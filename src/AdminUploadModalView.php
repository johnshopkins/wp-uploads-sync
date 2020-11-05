<?php

namespace UploadsSync;

class AdminUploadModalView extends Admin
{
  public function __construct($logger)
  {
    parent::__construct($logger);
    
    add_filter('attachment_fields_to_edit', [$this, 'addDataToMediaPage'], 10, 2);
  }

  public function addDataToMediaPage($form_fields, $post)
  {
    $form_fields['uploadsync'] = [
      'label' => 'File sync status',
      'input' => 'html',
      'html' => $this->getStatusIcon($post->ID)
    ];
    
    return $form_fields;
  }

  
}
