/*
 *  Take screenshot from preview
 */
( function($) {
  var index = 0;

  flowplayer( function(api,root) {
    root = jQuery(root);
    var button = jQuery('<input type="button" value="Screenshot" class="button" id="fv-splash-screen-button" />'),
      spinner =jQuery('<div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>');

    // where to seek when trying to setup the crossOrigin attribute for video
    var seek_recovery = false;

    function takeScreenshot() {
      var video = root.find('video').get(0);
      var canvas = document.createElement("canvas");
      canvas.width = video.videoWidth * 1;
      canvas.height = video.videoHeight * 1;
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

      var img = document.createElement("img");
      img.src = canvas.toDataURL('image/jpeg', 0.95);

      return img.src ;
    }

    button.on('click', function() {
      try {
        button.prop("disabled",true);

        var screenshot = takeScreenshot();
        var item = jQuery('.fv-player-playlist-item[data-index="'+index+'"]');
        spinner.insertAfter( item.find('#fv_wp_flowplayer_field_splash') );
      }
      catch(err) {
        var video_tag = root.find('video.fp-engine')[0];

        // try to set crossOrigin if it's a HTML5 video - no HLS or DASH
        if( video_tag && video_tag.crossOrigin != 'anonymous' && api.engine.engineName == 'html5' ) {
          debug_log('Screenshot: Reloading with CORS...');

          // without this Flowplayer will remove that crossOrigin="anonymous" automatically!
          api.conf.nativesubtitles = true;

          video_tag.crossOrigin = 'anonymous';
          reload_video();
          return;
        }

        show_error();

        debug_log('Screenshot error: '+ err);

        return;
      }

      fv_player_editor.upload_splash( { 'img': screenshot }, index );
    });

    // Compatibility test
    api.bind('ready', function(e,api) {
    if(jQuery('#fv_player_boxLoadedContent').length == 1) {
      var src = jQuery('[name="fv_wp_flowplayer_field_src"]:visible').val(), // check using visible src
        should_show = true;

      if ( typeof src != 'undefined' ) {
        fv_player_editor_conf_screenshots.disable_domains.forEach(function(item, index) {
          if( src.indexOf(item) !== -1 ) {
            should_show = false;
          }
        });

        if( should_show ) {
          button.appendTo('#fv-player-shortcode-editor-preview');
          try {
            takeScreenshot();
          } catch(err) {
            button.prop("disabled", true);
          }
        }
      }
    }
    });
    
    // Resume video after setting crossOrigin
    api.on('resume progress', function(e) {
      if( seek_recovery && api.video.seekable ) {
        api.seek(seek_recovery, function() {
          seek_recovery = false;
          
          // try to take the screenshot again
          button.click();
        });
      }
    });
    
    // Show error if video fails after setting crossOrigin
    api.on('error', function(e, api, err) {
      if( seek_recovery ) {
        // prevent FV Player Pro from trying to recover
        api.fv_retry_count = 100;
        
        debug_log('Screenshots: Video won\'t play with crossOrigin="anonymous"');
        
        show_error();
      }
    });
    
    function reload_video() {
      seek_recovery = api.video.time;

      var index = typeof(api.video.index) != "undefined" ? api.video.index : 0;
      
      api.error = api.loading = false;
      jQuery(root).find('.fp-message').remove();
      jQuery(root).removeClass("is-error").addClass("is-mouseover");
      
      if( api.conf.playlist.length ) {
        api.setPlaylist(api.conf.playlist).play(index);
      } else {
        api.load(api.conf.clip);
      }
      
    }
    
    function show_error() {
      spinner.remove();
      button.prop("disabled",false);
      message.html(fv_player_editor_translations.screenshot_cors_error);
      fv_wp_flowplayer_dialog_resize();
    }

  });

  // Remove button, spinner, message
  jQuery(document).on('fv_flowplayer_player_editor_reset', function() {
    jQuery('#fv-splash-screen-button').remove();
    jQuery('.fv-player-shortcode-editor-small-spinner').remove();
    jQuery('.fv-messages').empty();
    index = 0;
  });

  jQuery(document).on('fvp-preview-complete', function() {
    jQuery('#fv-splash-screen-button').remove();
    jQuery('.fv-player-shortcode-editor-small-spinner').remove();
    jQuery('.fv-messages').empty();
  });

  // Video index
  jQuery(document).on('fv_flowplayer_shortcode_item_switch', function(e,i) {
    index = i;
  });

})(jQuery);