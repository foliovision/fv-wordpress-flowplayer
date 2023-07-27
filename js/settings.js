jQuery(function() {
  jQuery('.fv-wordpress-flowplayer-save').on('click', function() {

    var $this = jQuery(this),
      $postbox = $this.closest('.postbox'),
      serialized = jQuery($postbox).find(':input').serializeArray();

      // add 'fv-wp-flowplayer-submit-ajax' to serialized data
      serialized.push({name: 'fv-wp-flowplayer-submit-ajax', value: 'fv-wp-flowplayer-submit-ajax'});

      // add nonce 'fv_flowplayer_settings_ajax_nonce' to serialized data
      serialized.push({name: 'fv_flowplayer_settings_ajax_nonce', value: jQuery('#fv_flowplayer_settings_ajax_nonce').val()});

      jQuery.ajax({
        url: window.location.href,
        type: 'POST',
        data: serialized,
        success: function(data) {
          // get new postbox
          var $new = jQuery(data).find('#' + $postbox.attr('id'));

          // replace old postbox with new one
          $postbox.replaceWith($new);

          return false;
        },
        error: function(data) {
          alert('Error: Cannot save settings.');
        }
      });
  });
});
