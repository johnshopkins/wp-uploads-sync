<?php

namespace UploadsSync;

class SyncStatusTable extends \WP_List_Table
{
  public function __construct($post)
  {
    // allow custom vars to be set on the object
    $this->compat_fields[] = 'post';
    $this->compat_fields[] = 'cacher';

    $this->post = $post;

    parent::__construct([]);
  }

  public function get_table_classes()
  {
    $classes = parent::get_table_classes();
    $classes[] = 'sync-status';
    return $classes;
  }

  public function get_columns()
  {
    return [
      'style' => 'Crop size',
      'context' => 'Context',
      'timestamp' => 'Job created',
      'handle' => 'Job handle',
      'status' => 'Status',
      'error' => 'Error',
      'rerun' => 'Rerun job'
    ];
  }

  function get_sortable_columns()
  {
    return [
      'style' => ['style', true],
      'context' => ['context', true],
      'timestamp' => ['timestamp', true]
    ];
  }

  function get_hidden_columns()
  {
    return [];
  }

  function prepare_items()
  {
    $this->_column_headers = [
      $this->get_columns(),
      $this->get_hidden_columns(),
      $this->get_sortable_columns()
    ];

    global $wpdb;

    $sql = "SELECT style, context, timestamp, handle, status, error, archived FROM file_sync WHERE fid = %d ORDER BY style, timestamp";
    $records = $wpdb->get_results($wpdb->prepare($sql, $this->post->ID), ARRAY_A);
    
    $this->items = array_map(function ($record) {

      $record['rerun'] = null;

      $status = (bool) $record['status'];
      $archived = (bool) $record['archived'];

      if ($status) {
        $record['status'] = '<span class="dashicons dashicons-yes-alt"></span>';
      } elseif (!empty($record['error'])) {
        $record['status'] = '<span class="dashicons dashicons-warning"></span>';
      } else {
        $record['status'] = '<span class="spinner is-active"></span>';
      }

      if ($record['error']) {
        $record['error'] = '<a href="https://sentry.io/organizations/jhu/issues/563542135/events/' . $record['error'] . '/?project=235628">View in Sentry</a>';
        if (!$record['archived']) {
          $record['rerun'] = '<button class="rerun" data-id="' . $this->post->ID . '" data-crop="' . $record['style'] . '">Rerun</button>';
        }
      }
      
      return $record;

    }, $records);
  }

  function column_default($item, $column_name)
  {
    return $item[$column_name];
  }

  public function single_row($item) {
    if ($item['archived']) {
      echo '<tr class="archived">';
    } else {
      echo '<tr>';
    }
		$this->single_row_columns($item);
		echo '</tr>';
	}
}
