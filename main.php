<?php
/*
Plugin Name: UploadsSync
Description: On attachment upload, delete, edit, run a script to rsync uploads between servers.
Author: johnshopkins
Version: 0.0
*/

use Secrets\Secret;

class UploadsSyncMain
{
  public function __construct($logger, $cacheCleaner, $deps = array())
  {
    if (defined("ENV") && (ENV != "local" && ENV != "staging")) {

      $this->cacheCleaner = $cacheCleaner;

      $this->gearmanClient = isset($deps["gearmanClient"]) ? $deps["gearmanClient"] : new \GearmanClient();

      $servers = Secret::get("jhu", ENV, "servers");

      if (!$servers) {
        $wp_logger->addCritical("Servers unavailable for Gearman " . __FILE__ . " on line " . __LINE__);
      }

      $server = array_shift($servers);

      $this->gearmanClient->addServer($server->hostname);

      /**
       * Use this action to hook into when an image
       * is cropped uisng the crop-thumbnails plugin.
       * Also catches when an attachment is added.
       */
      add_action("wp_update_attachment_metadata", array($this, "newImage"), 10, 2);

      // add_action("add_attachment", function () {
      //   $this->sync("add_attachment WP hook");
      // });

      add_action("edit_attachment", function ($id) {
        $this->sync("edit_attachment WP hook");
      });

      /**
       * This fires BEFORE WordPress has actually
       * deleted the file from the server, so rsync
       * has a chance of missing deleted images
       * when it runs. The next time it runs, it
       * shoud catch it.
       */
      add_action("delete_attachment", function ($id) {
        $this->sync("delete_attachment WP hook");
      });

    }

  }

  public function newImage($data, $id)
  {
    // wait for uploads to sync
    $this->gearmanClient->doNormal("sync_uploads", json_encode(array(
      "trigger" => "wp_update_attachment_metadata"
    )));

    // clear cache of image that was just added
    $this->cacheCleaner->clear_cache($id);

    return $data;
  }

  public function sync($trigger = null)
  {
    return $this->gearmanClient->doBackground("sync_uploads", json_encode(array(
      "trigger" => $trigger
    )));
  }
}

new UploadsSyncMain($wp_logger, $jhu_cache_clearer);
