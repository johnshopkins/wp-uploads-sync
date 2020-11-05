<?php

namespace UploadsSync;

class AdminUploadThumbnailView extends Admin
{
  public function __construct($logger)
  {
    parent::__construct($logger);

    // // adapted from:
    // // https://wordpress.org/support/topic/override-the-template-for-the-attachment-thumbnails-in-the-media-grid/
    // add_action('admin_footer-upload.php', function () {
    //   echo $this->getTemplate();

		// 	$dir = plugin_dir_url(dirname(__FILE__));
    // 	echo '"<script type="text/javascript" src="'. $dir . 'dist/js/thumbnail-view.js"></script>"';
		// });

    add_filter('wp_prepare_attachment_for_js', function ($response, $attachment, $meta) {

			// how to force update of tmpl-attachment-custom when this updates?

      $response['uploading'] = !$this->getStatus($attachment->ID);
      return $response;
    }, 10, 3);
  }

	/*
  protected function getTemplate()
  {
    ?>
    <script type="text/html" id="tmpl-attachment-custom">
		<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">
			<div class="thumbnail">
				<# if ( data.uploading ) { #>
          <div class="media-progress-bar"><div style="width: {{ data.percent }}%"></div></div>
				<# } else if ( 'image' === data.type && data.sizes ) { #>
					<div class="centered">
						<img src="{{ data.size.url }}" draggable="false" alt="" />
					</div>
				<# } else { #>
					<div class="centered">
						<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
							<img src="{{ data.image.src }}" class="thumbnail" draggable="false" alt="" />
						<# } else if ( data.sizes && data.sizes.medium ) { #>
							<img src="{{ data.sizes.medium.url }}" class="thumbnail" draggable="false" alt="" />
						<# } else { #>
							<img src="{{ data.icon }}" class="icon" draggable="false" alt="" />
						<# } #>
					</div>
					<div class="filename">
						<div>{{ data.filename }}</div>
					</div>
				<# } #>
			</div>
			<# if ( data.buttons.close ) { #>
				<button type="button" class="button-link attachment-close media-modal-icon"><span class="screen-reader-text"><?php _e( 'Remove' ); ?></span></button>
			<# } #>
		</div>
		<# if ( data.buttons.check ) { #>
			<button type="button" class="check" tabindex="-1"><span class="media-modal-icon"></span><span class="screen-reader-text"><?php _e( 'Deselect' ); ?></span></button>
		<# } #>
		<#
		var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly';
		if ( data.describe ) {
			if ( 'image' === data.type ) { #>
				<input type="text" value="{{ data.caption }}" class="describe" data-setting="caption"
					aria-label="<?php esc_attr_e( 'Caption' ); ?>"
					placeholder="<?php esc_attr_e( 'Caption&hellip;' ); ?>" {{ maybeReadOnly }} />
			<# } else { #>
				<input type="text" value="{{ data.title }}" class="describe" data-setting="title"
					<# if ( 'video' === data.type ) { #>
						aria-label="<?php esc_attr_e( 'Video title' ); ?>"
						placeholder="<?php esc_attr_e( 'Video title&hellip;' ); ?>"
					<# } else if ( 'audio' === data.type ) { #>
						aria-label="<?php esc_attr_e( 'Audio title' ); ?>"
						placeholder="<?php esc_attr_e( 'Audio title&hellip;' ); ?>"
					<# } else { #>
						aria-label="<?php esc_attr_e( 'Media title' ); ?>"
						placeholder="<?php esc_attr_e( 'Media title&hellip;' ); ?>"
					<# } #> {{ maybeReadOnly }} />
			<# }
		} #>
  </script>
  <?php
	}
	*/

  protected function getOverrideJS()
  {
    ?>
    <script>
      jQuery(document).ready(function($) {
        if (typeof wp.media.view.Attachment !== 'undefined') {
          wp.media.view.Attachment.prototype.template = wp.media.template('attachment-custom');
        }
      });
    </script>
    <?php
  }
}
