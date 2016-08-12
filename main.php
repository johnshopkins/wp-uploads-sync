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

      // urls of files to rsync
      $paths = [];

      if (empty($meta)) {

        // non-image file
        $paths[] = get_attached_file($id);

      } else {

        // image

        $uploadDir = $this->getUploadsPath($meta["file"]);

        // original file
        $paths[] = $uploadsDir . "/" . basename($meta["file"]);

        // crops
        $paths = array_values(array_map(function ($crop) use ($uploadDir) {
          return $uploadDir . "/" . $crop["file"]; // hogsmeade-360x240.jpg
        }, $meta["sizes"]));

      }

      $this->logger->addInfo("wp_update_attachment_metadata", array(
        "paths" => $paths
      ));

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
      // $meta = get_post_meta($id);
      // $this->logger->addInfo("delete_attachment", array("meta" => $meta));

      // non-image file meta: {"urls":["/var/www/html/hub/public/assets/uploads/2016/08/SrDeveloper.docx"]}
      // image file meta: {"meta":{"_wp_attached_file":["2016/08/paper.peach_.gif"],"_wp_attachment_metadata":["a:5:{s:5:\"width\";i:300;s:6:\"height\";i:300;s:4:\"file\";s:24:\"2016/08/paper.peach_.gif\";s:5:\"sizes\";a:1:{s:9:\"thumbnail\";a:4:{s:4:\"file\";s:24:\"paper.peach_-300x240.gif\";s:5:\"width\";i:300;s:6:\"height\";i:240;s:9:\"mime-type\";s:9:\"image/gif\";}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}"]}}

      // $this->sync($id, "delete_attachment WP hook");
    });
  }

  /**
   * Get the absolute path of the directory
   * into which the given file was uploaded.
   * @param  string $filepath Relative file path (ex: 2016/08/hogsmeade.jpg)
   * @return string Absolute path of upload directory (ex: /var/www/html/hub/public/assets/uploads/2016/08)
   */
  public function getUploadsPath($filepath)
  {
    $uploadInfo = wp_upload_dir();
    $uploadsPath = $uploadInfo["basedir"]; // /var/www/html/hub/public/assets/uploads

    $pathinfo = pathinfo($filepath);
    $dirname = $pathinfo["dirname"]; // 2016/08

    return $uploadsPath . "/" . $dirname;
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

    do_action("rsync_complete", $id, $file);
  }
}

new UploadsSyncMain($dependencies["logger_wp"]);
