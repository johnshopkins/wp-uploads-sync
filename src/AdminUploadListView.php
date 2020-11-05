<?php

namespace UploadsSync;

class AdminUploadListView extends Admin
{
  public function __construct($logger)
  {
    parent::__construct($logger);

    // add new column
    add_filter('manage_media_columns', function ($cols) {
      $cols['sync-status'] = 'Sync Status';
      return $cols;
    });

    // register the new column as sortable
    add_filter('manage_upload_sortable_columns', function ($cols) {
      $cols['sync-status'] = 'name';
      return $cols;
    });

    // add data to column
    add_action('manage_media_custom_column', function ($column, $id) {
      echo $this->getStatusIcon($id);
    }, 10, 2);
  }
}
