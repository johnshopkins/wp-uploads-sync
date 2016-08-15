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
    //   $attachment = new \UploadsSync\Attachment(817);
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
      $this->sync($file->homepath, $file->source, $file->filenames);

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

      // $meta = get_post_meta($id, "_wp_attachment_metadata", true);
      // $file = new UploadsSync\Attachment($id, $meta);
      // $this->delete($file->directory, $file->akamaiPath, $file->filenames);

    });
  }

  /**
   * Syncs images to NetStorage.
   * @param  string $homepath  WordPress homepath (ex: /var/www/html/hub/public/)
   * @param  string $source    File location relative to homepath
   * @param  array  $filenames Names of files to rsync
   */
  public function sync($homepath, $source, $filenames)
  {
    $data = array(
      "homepath" => $homepath,
      "source" => $source,
      "filenames" => $filenames
    );

    // $this->logger->addInfo("sync", $data);

    $this->gearmanClient->doNormal("upload", json_encode($data));

    // $this->gearmanClient->doBackground("invalidate_cache", json_encode(array(
    //   "id" => $id
    // )));
    //
    // do_action("rsync_complete", $id, $file);
  }

  public function delete($localPath, $akamaiPath, $filenames)
  {
    // only the original file was in $filenames -- missing crops

    $this->logger->addInfo("delete");

    $this->gearmanClient->doBackground("delete", json_encode(array(
      "localPath" => $localPath,
      "akamaiPath" => $akamaiPath,
      "filenames" => $filenames
    )));
  }
}

new UploadsSyncMain($dependencies["logger_wp"]);
