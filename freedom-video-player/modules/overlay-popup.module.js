/*
 *  Overlays and Popups
 */
flowplayer(function (api,root) {
  root = jQuery(root);
  var player_id = root.attr('id'),
    current_overlay = false;

  if( root.data('end_popup_preview') ){
    jQuery(document).ready( function() {
      api.trigger('finish', [ api] );
    });
  }

  function overlay_height_check() {
    var count = 0;
    var overlay_height_check = setInterval( function() {
      var height = current_overlay && current_overlay.find('.adsbygoogle').height();
      count++;
      if( count > 20*10 || height > 0 ) clearInterval(overlay_height_check);
      if( height > root.height() ) {
        current_overlay.addClass('tall-overlay');
      }
    }, 50 );
  }

  function show_overlay() {
    var overlay_data = root.attr('data-overlay');
    if( typeof(overlay_data) !='undefined' && overlay_data.length ) {
      try {
        overlay_data = JSON.parse(overlay_data);
      } catch (e) {
        return false
      }

      if( !current_overlay && !root.hasClass('is-cva') && root.width() >= parseInt(overlay_data.width) ) {
        var html = overlay_data.html;
        html = html.replace( '%random%', Math.random() );
        current_overlay = jQuery('<div id="'+player_id+'_ad" class="wpfp_custom_ad">'+html+'</div>');
        root.find('.fp-player').append(current_overlay);

        // Checking also on touchend to make sure tapping does not show the mobile controls overlay.
        current_overlay.find( '.fv_fp_close' ).on( 'click touchend', function() {
          current_overlay.fadeOut();

          // Pause any video that might be in overlay.
          var overlay_video = current_overlay.find('video');
          if ( overlay_video.length ) overlay_video[0].pause();

          return false;
        } );

        overlay_height_check();
        // check if the overlay contains any video and pause the player if the video is found
        setTimeout( function() {
          if( root.find('.wpfp_custom_ad video').length ) {
            api.pause();
          }
        },500);
      }
    }
  }

  api.get_popup = function() {
    var popup_data = root.attr('data-popup');
    if( typeof(popup_data) !='undefined' && popup_data.length ) {
      try {
        popup_data = JSON.parse(popup_data);
      } catch (e) {
        return false;
      }

      return popup_data;
    }
  }

  function show_popup( event ) {
    var popup_data = api.get_popup();
    if( popup_data ) {
      if(
        (
          event == 'finish' ||
          // Detect proper pause and not pause before finishing the video
          popup_data.pause && api.ready && api.paused ||
          // FV Player Pay Per View
          popup_data.html.match(/fv-player-ppv-purchase-btn-wrapper/)
        ) &&
        root.find('.wpfp_custom_popup').length == 0
      ) {
        root.addClass('is-popup-showing');

        // Important for mobile at the end of the video, so that the constrol bar shows
        root.addClass('is-mouseover');

        root.find('.fp-player').append( '<div id="'+player_id+'_custom_popup" class="wpfp_custom_popup">'+popup_data.html+'</div>' );
      }
    }
  }

  api.bind("ready", function () {
    if (current_overlay.length == 1) {
      current_overlay.remove();
      current_overlay = false;
    }
    if( !root.data('overlay_show_after') ) {
      show_overlay();
    }

  }).bind('progress', function(e,api,current) {
    if (current > root.data('overlay_show_after') ){
      show_overlay();
    }
  }).bind("finish", function (e, api) {
    if( typeof(api.video.index) == "undefined" || api.video.index+1 == api.conf.playlist.length ) {
      show_popup(e.type);
    }
  }).bind("pause", function (e) {
    if ( typeof api.video.click !== "undefined" ) {
      return false;
    }

    // Give it a bit of time so that we can check if the video is paused and not just finished
    setTimeout( function() {
      show_popup(e.type);
    }, 5 );

  }).bind("resume unload seek", function () {
    if( root.hasClass('is-popup-showing') ) {
      root.find('.wpfp_custom_popup').remove();
      root.removeClass('is-popup-showing');
    }
  });
});

/*
 *  Popups form, disabling and enabling Flowplayer hotkeys when you enter/leave the field
 */
jQuery(document).on('focus','.fv_player_popup input[type=text], .fv_player_popup input[type=email], .fv_player_popup textarea', function() {
  var api = jQuery(this).parents('.flowplayer').data('flowplayer');
  if( api ) api.disable(true);
});
jQuery(document).on('blur','.fv_player_popup input[type=text], .fv_player_popup input[type=email], .fv_player_popup textarea', function() {
  var api = jQuery(this).parents('.flowplayer').data('flowplayer');
  if( api ) api.disable(false);
});
