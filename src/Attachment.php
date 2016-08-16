<?php

namespace UploadsSync;

/**
 * Get infomation about an attachment in order to perform
 * RSYNC operations from the local server to Akamai.
 */
class Attachment
{
  /**
   * Attachment URL
   * Ex: http://local.hub.jhu.edu/assets/uploads/2016/08/filename.jpg
   * @var string
   */
  protected $attachmentUrl;

  /**
   * WordPress home URL
   * Ex: /var/www/html/hub/public/
   * @var string
   */
  public $homeurl;

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
    $this->attachmentUrl = wp_get_attachment_url($id); // http://hub.jhu.edu/assets/uploads/2016/08/filename.jpg
    $filepathInfo = pathinfo($this->attachmentUrl);
    $uploadDirectory = $filepathInfo["dirname"]; // http://hub.jhu.edu/assets/uploads/2016/08

    // get relative source directory
    $this->homeurl = home_url() . "/"; // http://hub.jhu.edu/
    $this->source = str_replace($this->homeurl, "", $uploadDirectory); // assets/uploads/2016/08

    // get directory to CD into prior to rsync
    $this->homepath = get_home_path(); // /var/www/html/hub/public/

    $this->getFilenames($meta);
  }

  /**
   * Get local filenames of attachment and crops
   * @param array $meta Attachment metadata
   * @return null
   */
  protected function getFilenames($meta)
  {
    $this->filenames[] = basename($this->attachmentUrl);

    if (!isset($meta["sizes"])) return; // non-image

    foreach ($meta["sizes"] as $crop) {
      $this->filenames[] = $crop["file"];
    }
  }
}
