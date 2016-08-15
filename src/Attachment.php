<?php

namespace UploadsSync;

/**
 * Get infomation about an attachment in order to perform
 * RSYNC operations from the local server to Akamai.
 */
class Attachment
{
  /**
   * Path of attachment on local server
   * Ex: /var/www/html/hub/public/assets/uploads/2016/08/filename.jpg
   * @var string
   */
  protected $filepath;

  /**
   * WordPress home directory, where we will cd into prior to rsync
   * Ex: /var/www/html/hub/public/
   * @var string
   */
  public $homepath;

  /**
   * Rsync source relative to homepath
   * Ex: assets/uploads/2016/08
   * @var string
   */
  public $source;

  /**
   * Filenames of attachment and attachment crops
   * @var array
   */
  public $filenames = array();

  /**
   * __construct
   * @param integer $id   Attachment ID
   * @param array   $meta [description]
   */
  public function __construct($id, $meta = array())
  {
    // get directory this file was uploaded into
    $this->filepath = get_attached_file($id); // /var/www/html/hub/public/assets/uploads/2016/08/oriole-bird-1.jpg
    $filepathInfo = pathinfo($this->filepath);
    $uploadDirectory = $filepathInfo["dirname"]; // /var/www/html/hub/public/assets/uploads/2016/08

    // get relative source directory
    $this->homepath = get_home_path(); // /var/www/html/hub/public/
    $this->source = str_replace($this->homepath, "", $uploadDirectory); // assets/uploads/2016/08

    // get general uploads directory
    $uploadsdir = wp_upload_dir();
    $basedir = $uploadsdir["basedir"]; // /var/www/html/hub/public/assets/uploads

    $this->getFilenames($meta);
  }

  /**
   * Get local filenames of attachment and crops
   * @param array $meta Attachment metadata
   * @return null
   */
  protected function getFilenames($meta)
  {
    $this->filenames[] = basename($this->filepath);

    if (!isset($meta["sizes"])) return; // non-image

    foreach ($meta["sizes"] as $crop) {
      $this->filenames[] = $crop["file"];
    }
  }
}
