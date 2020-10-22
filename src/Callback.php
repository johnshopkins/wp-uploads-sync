<?php

namespace UploadsSync;

/**
 * Get infomation about an attachment in order to perform
 * RSYNC operations from the local server to Akamai.
 */
class Callback
{
  public function __construct($logger, $namespace, $servers)
  {
    // // if not production AND DEBUG is false
    // if ((defined("ENV") && ENV != "production") && (defined("DEBUG") && !DEBUG)) return;

    $this->logger = $logger;
    $this->namespace = $namespace;

    $this->setupGearmanClient($servers);
  }

  /**
   * Sets up the Gearman client, adding only
   * the admin server.
   */
  protected function setupGearmanClient($servers)
  {
    $this->gearmanClient = new \GearmanClient();

    if (!$servers) {
      $this->logger->addError("Servers unavailable for Gearman.");
    }

    // add admin server only
    $server = array_shift($servers);

    $this->gearmanClient->addServer($server->hostname);
  }

  public function onUpload($filename, $url, $context)
  {
    $this->logger->addInfo('callback', [$filename, $url, $context]);

    if ($context === 'recropped') {
      $this->gearmanClient->doBackground("{$this->namespace}_invalidate_urls", json_encode([
        'urls' => [$url]
      ]));
    }
  }
}
