<?php

namespace UploadsSync;

/**
 * Get infomation about an attachment in order to perform
 * RSYNC operations from the local server to Akamai.
 */
class Attachment
{
  /**
   * Attachment path
   * Ex: /var/www/html/hub/current/public/assets/uploads/2016/08/filename.jpg
   * OR  /var/www/html/hub/releases/20160816140117/public/assets/uploads/2016/08/filename.jpg
   * @var string
   */
  public $path;

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
   * @param string  $path  Attachment path
   * @param array   $meta  Metadata
   */
  public function __construct($path, $meta = array())
  {
    // get directory this file was uploaded into
    $this->path = $this->normalizePath($path);
    $filepathInfo = pathinfo($this->path);
    $uploadDirectory = $filepathInfo["dirname"]; // /var/www/html/hub/public/assets/uploads/2016/08

    // get directory to CD into prior to rsync
    $this->homepath = get_home_path(); // /var/www/html/hub/public/

    // get relative source directory
    $this->source = str_replace($this->homepath, "", $uploadDirectory); // assets/uploads/2016/08

    $this->getFilenames($meta);
  }

  /**
   * Convert `releases/YYYYMMDDHHMMSS` to `current`
   * for staging and production environments
   * @param  string $path Attachment path
   * @return strig Normalized path
   */
  protected function normalizePath($path)
  {
    return preg_replace("/releases\/\d{14}/", "current", $path);
  }

  /**
   * Get local filenames of attachment and crops
   * @param array $meta Attachment metadata
   * @return null
   */
  protected function getFilenames($meta)
  {
    $this->filenames[] = basename($this->path);

    if (!isset($meta["sizes"])) return; // non-image

    foreach ($meta["sizes"] as $crop) {
      $this->filenames[] = $crop["file"];
    }
  }

  public function getUrls()
  {
    $homeurl = home_url();
    return array_map(function ($filename) use ($homeurl) {
      return "{$homeurl}/{$this->source}/{$filename}";
    }, $this->filenames);
  }
}
