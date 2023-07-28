jQuery(function() {
  var is_saving = false,
    spinner = jQuery('<div id="fv-editor-screenshot-spinner" class="fv-player-shortcode-editor-small-spinner" style="float: right;">&nbsp;</div>'),
    postbox_data_changed = [];

  function closeWarning(e) {
    (e || window.event).returnValue = true; //Gecko + IE
    return true; //Gecko + Webkit, Safari, Chrome etc.
  }

  function show_popup(message, bgColor) {
    var popup = jQuery('<div>').text(message).css('background-color', bgColor);
    jQuery('#fv-player-popup-container').empty().append(popup).fadeIn();

    setTimeout(function() {
      jQuery('#fv-player-popup-container').fadeOut();
    }, 3000);
  }

  // use setTimeout to avoid conflicts with other plugins
  setTimeout(function() {
    // detect any change in postbox
    jQuery(document).on( 'input', '.postbox :input', function(e) {
      if( postbox_data_changed.length == 0 ) {
        window.addEventListener('beforeunload', closeWarning);
      }

      var postbox_id = jQuery(this).closest('.postbox').attr('id');

      if( postbox_data_changed.indexOf( postbox_id ) == -1 ) {
        postbox_data_changed.push( postbox_id );
      }
    });
  },0);

  jQuery(document).on( 'click', '.fv-wordpress-flowplayer-save', function(e) {
    e.preventDefault();

    if (is_saving) {
      return false;
    }

    is_saving = true;

    var $this = jQuery(this),
      $postbox = $this.closest('.postbox'),
      serialized = jQuery($postbox).find(':input').serializeArray(),
      reload = $this.data('reload'),
      postbox_id = $postbox.attr('id'),
      postbox_name = $postbox.find('h2').first().text();

    $this.closest('td').append(spinner);

    // add 'fv-wp-flowplayer-submit-ajax' to serialized data
    serialized.push({name: 'fv-wp-flowplayer-submit-ajax', value: 'fv-wp-flowplayer-submit-ajax'});

    // add nonce 'fv_flowplayer_settings_ajax_nonce' to serialized data
    serialized.push({name: 'fv_flowplayer_settings_ajax_nonce', value: jQuery('#fv_flowplayer_settings_ajax_nonce').val()});

    // add postbox id to serialized data
    serialized.push({name: 'postbox_id', value: postbox_id});

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
        var $new = jQuery(data).find('#' + postbox_id);

        // replace old postbox with new one
        if(reload) $postbox.replaceWith($new);
        show_popup('Settings saved for: ' + postbox_name.toLowerCase(), 'green');
      },
      error: function(data) {
        show_popup('Error saving settings for: ' + postbox_name.toLowerCase() , 'red');
        console.error(data);
      },
      complete: function() {
        is_saving = false;
        spinner.remove();

        // remove saved postbox from array
        postbox_data_changed.splice( postbox_data_changed.indexOf( postbox_id ), 1 );

        // ceck if there are any unsaved postboxes
        if( postbox_data_changed.length == 0 ) {
          window.removeEventListener('beforeunload', closeWarning);
        }
        return false;
      }
    }

    jQuery.ajax(ajax_obj);

  });
});
