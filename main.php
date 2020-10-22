<?php
/*
Plugin Name: UploadsSync
Description: On attachment upload, delete, edit, run a script to rsync uploads up to Akamai NetStorage.
Author: johnshopkins
Version: 0.0
*/

use Secrets\Secret;

class UploadsSync
{
  public function __construct($logger, $namespace, $servers)
  {
    // if not production AND DEBUG is false
    if ((defined("ENV") && ENV != "production") && (defined("DEBUG") && !DEBUG)) return;

    $this->logger = $logger;
    $this->namespace = $namespace;

    $this->setupGearmanClient($servers);
    $this->setupActions();
  }

  /**
   * Sets up the Gearman client, adding only
   * the admin server.
   */
  protected function setupGearmanClient($servers)
  {
    $this->gearmanClient = new \GearmanClient();

    if (!$servers) {
      $this->logger->addError("Servers unavailable for Gearman.");
    }

    // add admin server only
    $server = array_shift($servers);

    $this->gearmanClient->addServer($server->hostname);
  }

  /**
   * Based on the new metadata the crop thumbnails plugin is
   * about to save to the database, find out which images changed.
   * @param integer $id      Attachment ID
   * @param array   $newMeta New metadata from crop thumbnails plugin
   * @return array  Image sizes that changed
   */
  protected function findImagesThatChanged($id, $newMeta)
  {
    $changed = [];
    $current = wp_get_attachment_metadata($id);

    foreach ($newMeta['sizes'] as $size => $details) {

      if (!isset($details['cpt_last_cropping_data'])) {
        // this image has never been cropped by the plugin
        continue;
      }
        
      $currentCropData = $current['sizes'][$size]['cpt_last_cropping_data'] ?? null;
      $newCropData = $details['cpt_last_cropping_data'];

      if ($currentCropData === null || $currentCropData['x'] !== $newCropData['x'] || $currentCropData['y'] !== $newCropData['y'] || $currentCropData['x2'] !== $newCropData['x2'] || $currentCropData['y2'] !== $newCropData['y2']) {
        // new crop position is different than the current crop position
        $changed[] = $size;
      }

    }

    return $changed;
  }

  protected function setupActions()
  {
    /**
     * After an image is initially uploaded into the system and all crops created
     */
    add_filter("wp_generate_attachment_metadata", function ($meta, $id) {

      $path = get_attached_file($id);
      $file = new UploadsSync\Attachment($path, $meta);

      $this->upload($id, $file);

      return $meta;

    }, 10, 2);

    /**
     * After an image has been recropped with the crop-thumbnails plugin
     * https://github.com/vollyimnetz/crop-thumbnails#filter-crop_thumbnails_before_update_metadata
     */



    add_filter('crop_thumbnails_before_update_metadata', function ($meta, $id) {

      $changed = $this->findImagesThatChanged($id, $meta);

      if (!empty($changed)) {
        $path = get_attached_file($id);
        $file = new UploadsSync\Attachment($path, $meta);

        $this->upload($id, $file, 'recropped', $changed);
      }

      return $meta;

    }, 10, 3);

    add_filter("wp_delete_file", function ($path) {

      $file = new UploadsSync\Attachment($path);

      // delete the file ourselves (WP doesn't have a way to hook in AFTER the file is removed from the system)
      @unlink($path);

      // initialize rsync
      $this->delete($file);

      // return empty array so WP doesn't try to delete too
      return array();

    });
  }

  /**
   * Syncs images to NetStorage.
   */
  public function upload($id, $file, $type = 'initial upload', $changed = [])
  {
    global $wpdb;

    $files = $file->getFilenamesAndUrls($changed);

    $this->logger->addInfo('upload', $files);

    foreach ($files as $style => $details) {

      // create the gearman job

      $data = [
        'homepath' => $file->homepath,
        'source' => $file->source,
        'filenames' => [$details['filename']],
        'urls' => [$details['url']],
        'type' => $type
      ];

      $handle = $this->gearmanClient->doHighBackground("{$this->namespace}_upload", json_encode($data));

      
      // add the job to the database for status tracking

      $row = [
        'fid' => $id,
        'site' => get_current_blog_id(),
        'style' => $style,
        'type' => $type,
        'handle' => $handle
      ];

      $format = ['%d', '%d', '%s', '%s', '%s'];

      // insert into database
      $result = $wpdb->insert('file_sync', $row, $format);

      if ($result === false) {
        // fail silently
        $this->logger->addWarning('Failed to insert data into `file_sync` table', $row);
      }
    }
  }
  }

  /**
   * Delete a file in NetStorage
   */
  public function delete($file)
  {
    $data = [
      'homepath' => $file->homepath,
      'source' => $file->source,
      'filenames' => $file->getFilenames()
    ];

    $this->gearmanClient->doBackground("{$this->namespace}_delete", json_encode($data));
  }
}

$servers = Secret::get("jhu", ENV, "servers");
new UploadsSync($dependencies["logger_gearman"], "jhu", $servers);
