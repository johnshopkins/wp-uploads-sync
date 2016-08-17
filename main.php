<?php
/*
Plugin Name: UploadsSync
Description: On attachment upload, delete, edit, run a script to rsync uploads up to Akamai NetStorage.
Author: johnshopkins
Version: 0.0
*/

use Secrets\Secret;

class UploadsSyncMain
{
  public function __construct($logger)
  {
    // // do not run this plugin on local or staging
		// if (defined("ENV") && (ENV == "local" || ENV == "staging")) return;

    $this->logger = $logger;
    $this->setupGearmanClient();
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
  protected function setupGearmanClient()
  {
    $this->gearmanClient = new \GearmanClient();

    $servers = Secret::get("jhu", ENV, "servers");

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

      // delete the file ourselves (WP doesn't have a way )
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

    $this->gearmanClient->doNormal("upload", json_encode($data));

    $this->gearmanClient->doBackground("purge_cache", json_encode(array(
      "urls" => $urls
    )));

    do_action("netstorage_upload_complete", $id, $file);
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

    $this->gearmanClient->doNormal("delete", json_encode($data));
  }
}

new UploadsSyncMain($dependencies["logger_wp"]);
