jQuery(function() {
  var is_saving = false;

  function show_popup(message, bgColor) {
    var popup = jQuery('<div>').text(message).css('background-color', bgColor);
    jQuery('#fv-player-popup-container').empty().append(popup).fadeIn();

    setTimeout(function() {
      jQuery('#fv-player-popup-container').fadeOut();
    }, 3000);
  }

  jQuery('.fv-wordpress-flowplayer-save').on('click', function(e) {
    e.preventDefault();

    if (is_saving) {
      return false;
    }

    is_saving = true;

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
          show_popup('Settings saved', 'green');
        },
        error: function(data) {
          show_popup('Error saving settings', 'red');
        },
        complete: function() {
          is_saving = false;
          return false;
        }
      });
  });
});
