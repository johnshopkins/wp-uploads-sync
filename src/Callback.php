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

  public function onUploadFail($handle, $error)
  {
    global $wpdb;

    $result = $wpdb->update(
      'file_sync',
      ['error' => $error],   // update date
      ['handle' => $handle], // where
      ['%s'],                // data format
      ['%s']                 // where format
    );

    if ($result === false) {
      $this->logger->addWarning("Failed to report error for handle: {$handle} in `file_sync` table.", ['error' => $error]);
    }
  }

  public function onUploadSuccess($handle, $filename, $url, $context)
  {
    global $wpdb;

    if ($context !== 'initial upload') {
      $this->gearmanClient->doBackground("{$this->namespace}_invalidate_urls", json_encode([
        'urls' => [$url]
      ]));
    }

    // change status to 1

    $result = $wpdb->update(
      'file_sync',
      ['status' => 1],       // update date
      ['handle' => $handle], // where
      ['%d'],                // data format
      ['%s']                 // where format
    );

    if ($result === false) {
      $this->logger->addWarning("Failed to change status to `1` for handle: {$handle} in `file_sync` table.");
    }
  }
}
