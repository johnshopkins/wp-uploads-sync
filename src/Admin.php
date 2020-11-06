<?php

namespace UploadsSync;

class Admin
{
  /**
   * All jobs associated with file successfully uploaded
   */
  const SUCCESS = 0;

  /**
   * There are still pending jobs
   */
  const PENDING = 1;

  /**
   * One or more jobs failed
   */
  const FAIL = 2;

  /**
   * Classes of icons that coorespond to response code
   * @param array $logger
   */
  protected $iconClasses = [
    'dashicons dashicons-yes-alt',
    'spinner is-active',
    'dashicons dashicons-warning'
  ];

  public function __construct($logger)
  {
    $this->logger = $logger;
  }

  public function getStatusIcon($id)
  {
    $status = $this->getStatus($id);
    
    $icon = '<span class="' . $this->iconClasses[$status] . '"></span>';
    $link = '<a href="' . site_url() . '/wp-admin/post.php?post=' . $id. '&action=edit#sync-status">See details</a>';

    return $icon . ' ' . $link;
  }

  /**
   * Find out the status of all upload jobs associated with a particular file
   * @param  integer $id Attachment ID
   * @return integer     Response code
   */
  public function getStatus($id)
  {
    global $wpdb;

    $status = $this::SUCCESS;
    
    $results = $wpdb->get_results($wpdb->prepare('SELECT fid, error FROM file_sync fs WHERE fs.fid = %d AND status = 0 AND archived = 0 GROUP BY fid', $id));

    if (!empty($results)) {

      // default to pending jobs
      $status = $this::PENDING;
      
      // unless an error is found
      foreach ($results as $result) {
        if (!empty($result->error)) {
          $status = $this::FAIL;
          break;
        }
      }
    }
    
    return $status;
  }
}
