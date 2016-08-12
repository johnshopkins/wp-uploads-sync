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
     * Register custom actions
     */
    add_action("rsync_complete", function () {
      $this->logger->addInfo("rsync_complete");
    });

    /**
     * Catches when an attachment is created (`add_attachment`
     * runs before metadata is even created) or modified.
     * @var string
     */
    add_filter("wp_update_attachment_metadata", function ($meta, $id) {

      $file = new UploadsSync\Attachment($id, $meta);
      $this->sync($file->paths, $file->akamaiPath);

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

      $meta = get_post_meta($id, "_wp_attachment_metadata", true);
      $file = new UploadsSync\Attachment($id, $meta);
      $this->delete($file->directory, $file->akamaiPath, $file->filenames);

      // $this->logger->addInfo("delete_attachment", array("meta" => $meta));

    });
  }

  /**
   * Syncs images to NetStorage.
   * @param  array  $paths       Filepaths
   * @param  string $akamaiPath  Relative path in Akamai to rsync images to (ex: assets/uploads/2016/08)
   */
  public function sync($paths, $akamaiPath)
  {
    $this->logger->addInfo("sync", array(
      "paths" => $paths,
      "akamaiPath" => $akamaiPath
    ));

    // $this->gearmanClient->doNormal("sync_uploads", json_encode(array(
    //   "trigger" => $trigger,
    //   "file" => get_attached_file($id)
    // )));
    //
    // $this->gearmanClient->doBackground("invalidate_cache", json_encode(array(
    //   "id" => $id
    // )));
    //
    // do_action("rsync_complete", $id, $file);
  }

  public function delete($localPath, $akamaiPath, $filenames)
  {
    // only the original file was in $filenames -- missing crops

    $this->logger->addInfo("delete", array(
      "localPath" => $localPath,
      "akamaiPath" => $akamaiPath,
      "filenames" => $filenames
    ));
  }
}

new UploadsSyncMain($dependencies["logger_wp"]);
