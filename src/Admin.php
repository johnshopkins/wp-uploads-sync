<?php

namespace UploadsSync;

class Admin
{
  public function __construct($logger)
  {
    $this->logger = $logger;
  }

  public function getStatusIcon($id)
  {
    $success = $this->getStatus($id);
    
    $iconClass = $success ? 'dashicons dashicons-yes-alt' : 'spinner is-active';
    $icon = '<span class="' . $iconClass . '"></span>';

    $link = '<a href="' . site_url() . '/wp-admin/post.php?post=' . $id. '&action=edit#sync-status">See details</a>';

    return $icon . ' ' . $link;
  }


  public function getStatus($id)
  {
    global $wpdb;
    
    $results = $wpdb->get_results($wpdb->prepare('SELECT fid FROM file_sync fs WHERE fs.fid = %d AND status = 0 AND archived = 0 GROUP BY fid', $id));

    return empty($results);
  }
}
