jQuery(function() {
  var is_saving = false,
    spinner = jQuery('<div id="fv-editor-screenshot-spinner" class="fv-player-shortcode-editor-small-spinner" style="float: right;">&nbsp;</div>');

  function show_popup(message, bgColor) {
    var popup = jQuery('<div>').text(message).css('background-color', bgColor);
    jQuery('#fv-player-popup-container').empty().append(popup).fadeIn();

    setTimeout(function() {
      jQuery('#fv-player-popup-container').fadeOut();
    }, 3000);
  }

  jQuery(document).on( 'click', '.fv-wordpress-flowplayer-save', function(e) {
    e.preventDefault();

    if (is_saving) {
      return false;
    }

    is_saving = true;

    var $this = jQuery(this),
      $postbox = $this.closest('.postbox'),
      serialized = jQuery($postbox).find(':input').serializeArray(),
      reload = $this.data('reload');

    $this.closest('td').append(spinner);

    // add 'fv-wp-flowplayer-submit-ajax' to serialized data
    serialized.push({name: 'fv-wp-flowplayer-submit-ajax', value: 'fv-wp-flowplayer-submit-ajax'});

    // add nonce 'fv_flowplayer_settings_ajax_nonce' to serialized data
    serialized.push({name: 'fv_flowplayer_settings_ajax_nonce', value: jQuery('#fv_flowplayer_settings_ajax_nonce').val()});

    // use custom action if not reloading
    if( !reload ) {
      serialized.push({name: 'action', value: 'fv_flowplayer_settings_save'});
    }

    var ajax_obj = {
      url: reload ? window.location.href : ajaxurl,
      type: 'POST',
      data: serialized,
      success: function(data) {
        // get new postbox
        var $new = jQuery(data).find('#' + $postbox.attr('id'));

        // replace old postbox with new one
        if(reload) $postbox.replaceWith($new);
        show_popup('Settings saved', 'green');
      },
      error: function(data) {
        show_popup('Error saving settings', 'red');
      },
      complete: function() {
        is_saving = false;
        spinner.remove();
        return false;
      }
    }

    jQuery.ajax(ajax_obj);

  });
});
