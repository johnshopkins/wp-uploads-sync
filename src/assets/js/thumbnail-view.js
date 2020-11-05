jQuery(document).ready(function ($) {
  if (typeof wp.media.view.Attachment !== 'undefined') {
    wp.media.view.Attachment.prototype.template = wp.media.template('attachment-custom');
  }
});
