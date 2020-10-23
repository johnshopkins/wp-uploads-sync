<?php

namespace UploadsSync;

class Admin
{
  public function __construct($logger)
  {
    $this->logger = $logger;
    
    add_filter('attachment_fields_to_edit', [$this, 'addDataToMediaPage'], 10, 2);

    add_action('admin_enqueue_scripts', function () {
      $dir = plugin_dir_url(dirname(__FILE__));
      wp_enqueue_script('wp-uploads-sync-rerun-js', $dir . 'dist/js/rerun.js', ['wp-api-fetch', 'wp-url'], false, true);
      wp_enqueue_style('wp-uploads-sync-admin-css', $dir . 'dist/css/admin.css');
    });
  }

  public function addDataToMediaPage($form_fields, $post)
  {
    $form_fields['uploadsync'] = array(
      'label' => '&nbsp;',
      'input' => 'html',
      'html' => $this->getTable($post)
    );
    
    return $form_fields;
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
