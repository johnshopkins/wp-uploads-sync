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
  public function __construct($logger, $deps = array())
  {
    if (defined("ENV") && (ENV != "local" && ENV != "staging")) {

      $this->gearmanClient = isset($deps["gearmanClient"]) ? $deps["gearmanClient"] : new \GearmanClient();

      $servers = Secret::get("jhu", ENV, "servers");
      if (!$servers) {
        $wp_logger->addCritical("Servers unavailable for Gearman " . __FILE__ . " on line " . __LINE__);
        die();
      }
      $server = array_shift($servers);

      $this->gearmanClient->addServer($server->hostname);

      /**
       * Use this action to hook into when an image
       * is cropped uisng the crop-thumbnails plugin.
       * Also catches when an attachment is added.
       */
      add_action("wp_update_attachment_metadata", function ($data) {
        $this->sync("wp_update_attachment_metadata");
        return $data;
      });

      // add_action("add_attachment", function () {
      //   $this->sync("add_attachment WP hook");
      // });

      add_action("edit_attachment", function () {
        $this->sync("edit_attachment WP hook");
      });

      /**
       * This fires BEFORE WordPress has actually
       * deleted the file from the server, so rsync
       * has a chance of missing deleted images
       * when it runs. The next time it runs, it
       * shoud catch it.
       */
      add_action("delete_attachment", function () {
        $this->sync("delete_attachment WP hook");
      });
    
    }

  }

  public function sync($trigger = null)
  {
    return $this->gearmanClient->doBackground("sync_uploads", json_encode(array(
      "trigger" => $trigger
    )));
  }
}

new UploadsSyncMain($wp_logger);
