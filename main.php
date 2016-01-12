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
  public function __construct($logger, $cacheCleaner)
  {
    // do not run this plugin on local or staging
		if (defined("ENV") && (ENV == "local" || ENV == "staging")) return;

    $this->cacheCleaner = $cacheCleaner;
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
     * Use this action to hook into when an image
     * is cropped uisng the crop-thumbnails plugin.
     * Also catches when an attachment is added.
     */
    add_action("wp_update_attachment_metadata", array($this, "newImage"), 10, 2);

    /**
     * This fires BEFORE WordPress has actually
     * deleted the file from the server, so rsync
     * has a chance of missing deleted images
     * when it runs. The next time it runs, it
     * shoud catch it.
     */
    add_action("delete_attachment", function ($id) {
      $this->sync($id, "delete_attachment WP hook");
    });

    add_action("edit_attachment", function ($id) {
      $this->sync($id, "edit_attachment WP hook");
    });
  }

  /**
   * Syncs images to NetStorage. Waits for the rsync
   * to finish and then clears the cache of the image,
   * now that its metadata is available in NetStorage.
   * @param array   $data Attachment meta data.
   * @param integer $id   Attachment ID
   */
  public function newImage($data, $id)
  {
    $this->gearmanClient->doNormal("sync_uploads", json_encode(array(
      "trigger" => "wp_update_attachment_metadata",
      "file" => get_attached_file($id)
    )));

    $this->gearmanClient->doBackground("invalidate_cache", json_encode(array(
      "id" => $id
    )));

    // clear endpoint
    $this->gearmanClient->doHighBackground("api_clear_cache", json_encode(array("id" => $id)));

    return $data;
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

    // clear endpoint
    $this->gearmanClient->doHighBackground("api_clear_cache", json_encode(array("id" => $id)));
  }
}

new UploadsSyncMain($wp_logger, $jhu_cache_clearer);
