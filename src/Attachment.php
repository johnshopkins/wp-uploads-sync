<?php

namespace UploadsSync;

class Attachment
{
  /**
   * Path of attachment on local server
   * Ex: /var/www/html/hub/public/assets/uploads/2016/08/filename.jpg
   * @var string
   */
  protected $filepath;

  /**
   * Directory in which attachment is located
   * Ex: /var/www/html/hub/public/assets/uploads/2016/08
   * @var string
   */
  public $directory;

  /**
   * Where to rsync file on Akamai
   * Ex: assets/uploads/2016/08
   * @var string
   */
  public $akamaiPath;

  /**
   * Paths of attachment and attachment
   * crops on the local server
   * @var array
   */
  public $paths = array();

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
    $this->filepath = get_attached_file($id);

    $filepathInfo = pathinfo($this->filepath);
    $this->directory = $filepathInfo["dirname"];

    $homepath = get_home_path(); // /var/www/html/hub/public/
    $this->akamaiPath = str_replace($homepath, "", $this->directory);

    $this->getPaths($meta);
  }

  /**
   * Get local filepaths and filenames or
   * attachments and crops
   * @param array $meta Attachment metadata
   * @return null
   */
  protected function getPaths($meta)
  {
    $this->paths[] = $this->filepath;
    $this->filenames[] = basename($this->filepath);

    if (!isset($meta["sizes"])) return; // non-image

    foreach ($meta["sizes"] as $crop) {
      $this->paths[] = $this->directory . "/" . $crop["file"];
      $this->filenames[] = $crop["file"];
    }
  }
}
