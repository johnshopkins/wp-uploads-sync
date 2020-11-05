<?php

namespace UploadsSync;

class AdminUploadFileView extends Admin
{
  public function __construct($logger)
  {
    parent::__construct($logger);
    
    add_action('admin_enqueue_scripts', function () {
      $dir = plugin_dir_url(dirname(__FILE__));
      wp_enqueue_script('wp-uploads-sync-rerun-js', $dir . 'dist/js/rerun.js', ['wp-api-fetch', 'wp-url'], false, true);
      wp_enqueue_style('wp-uploads-sync-admin-css', $dir . 'dist/css/sync-status.css');
    });

    add_action('edit_form_after_editor', function ($post) {
      echo '<p id="sync-status"><strong>File sync status</strong></p>';
      echo $this->getTable($post);
    });
  }

  protected function getTable($post)
  {
    $table = new SyncStatusTable($post);
    $table->prepare_items();

    // capture function output
    ob_start();
    $table->display();
    $data = ob_get_contents();
    ob_end_clean();

    return $data;
  }
}
