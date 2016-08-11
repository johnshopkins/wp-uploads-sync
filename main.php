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
     * Cannot use `add_attachment` to detect when an attachment has been
     * added because it runs before the metadata is created. We need to
     * hook into this filter, which runs just before the metadata is
     * added (there is no action at this moment), but gives us acces to
     * the metadata
     * @var string
     */
    add_filter("wp_update_attachment_metadata", function ($meta, $id) {

        if (empty($meta)) {

          // regular file
          $url = get_attached_file($id);

          $this->logger->addInfo("wp_update_attachment_metadata", array(
            "id" => $id,
            "url" => $url
          ));

        } else {

          // image

          $originalFile = $meta["file"]; // 2016/08/hogsmeade.jpg

          // what about regular files???
          $files = array_values(array_map(function ($crop) {
            return $crop["file"]; // hogsmeade-360x240.jpg
          }, $meta["sizes"]));

          $this->logger->addInfo("wp_update_attachment_metadata", array(
            "id" => $id,
            "original_file" => $originalFile,
            "files" => $files
          ));
        }

      }

      return $meta;

    }, 10, 2);

    /**
     * This fires BEFORE WordPress has actually
     * deleted the file from the server, so rsync
     * has a chance of missing deleted images
     * when it runs. The next time it runs, it
     * shoud catch it.
     */
    add_action("delete_attachment", function ($id) {
      $this->logger->addInfo("delete_attachment");
      // $this->sync($id, "delete_attachment WP hook");
    });
  }

  /**
   * Syncs images to NetStorage, but doesn't wait
   * around for the rsync command to complete.
   * @param  string $trigger WordPress action
   * @return null
   */
  public function sync($id, $trigger = null)
  {
    $this->gearmanClient->doNormal("sync_uploads", json_encode(array(
      "trigger" => $trigger,
      "file" => get_attached_file($id)
    )));

    $this->gearmanClient->doBackground("invalidate_cache", json_encode(array(
      "id" => $id
    )));

    // add a hook here for jhu.edu to clear its cache
  }
}

new UploadsSyncMain($dependencies["logger_wp"]);
