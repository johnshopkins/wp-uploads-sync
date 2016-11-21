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

    // add_action("admin_init", function () {
    //   $id = 832;
    //   $meta = get_post_meta($ids, "_wp_attachment_metadata", true);
    //   $attachment = new \UploadsSync\Attachment(get_attached_file(832), $meta);
    //   print_r(array($attachment->homepath, $attachment->source, $attachment->filenames)); die();
    // });

  }

  /**
   * Sets up the Gearman client, adding only
   * the admin server.
   */
  protected function setupGearmanClient($servers)
  {
    $this->gearmanClient = new \GearmanClient();

    if (!$servers) {
      $wp_logger->addCritical("Servers unavailable for Gearman " . __FILE__ . " on line " . __LINE__);
    }

    // add admin server only
    $server = array_shift($servers);

    $this->gearmanClient->addServer($server->hostname);
  }

  protected function setupActions()
  {
    /**
     * Catches when an attachment is created (`add_attachment`
     * runs before metadata is even created) or modified.
     * @var string
     */
    add_filter("wp_update_attachment_metadata", function ($meta, $id) {

      $path = get_attached_file($id);
      $file = new UploadsSync\Attachment($path, $meta);
      $this->upload($id, $file->homepath, $file->source, $file->filenames, $file->getUrls());

      return $meta;

    }, 10, 2);

    add_filter("wp_delete_file", function ($path) {

      $file = new UploadsSync\Attachment($path);

      // delete the file ourselves (WP doesn't have a way to hook in AFTER the file is removed from the system)
      @unlink($path);

      // initialize rsync
      $this->delete($file->homepath, $file->source, $file->filenames);

      // return empty array so WP doesn't try to delete too
      return array();

    });
  }

  /**
   * Syncs images to NetStorage.
   * @param  integer $id        WordPress homepath (ex: /var/www/html/hub/public/)
   * @param  string  $homepath  WordPress homepath (ex: /var/www/html/hub/public/)
   * @param  string  $source    File location relative to homepath
   * @param  array   $filenames Names of files to upload
   * @param  array   $urls      URLs that need their cache busted
   */
  public function upload($id, $homepath, $source, $filenames, $urls)
  {
    $data = array(
      "homepath" => $homepath,
      "source" => $source,
      "filenames" => $filenames
    );

    $this->gearmanClient->doNormal("{$this->namespace}_upload", json_encode($data));

    $this->gearmanClient->doBackground("{$this->namespace}_invalidate_urls", json_encode(array(
      "urls" => $urls
    )));
  }

  /**
   * Delete a file in NetStorage
   * @param  string $homepath  WordPress homepath (ex: /var/www/html/hub/public/)
   * @param  string $source    File location relative to homepath
   * @param  array  $filenames Names of files to delete
   */
  public function delete($homepath, $source, $filenames)
  {
    $data = array(
      "homepath" => $homepath,
      "source" => $source,
      "filenames" => $filenames
    );

    $this->gearmanClient->doNormal("{$this->namespace}_delete", json_encode($data));
  }
}
