/*
 *  Take screenshot from preview
 */
( function() {
  var index = 0;

  flowplayer( function(api,root) {
    root = jQuery(root);
    var button = jQuery('<input type="button" value="Make new splash screen" class="button" id="fv-splash-screen-button" />'),
      spinner =jQuery('<div id="fv-editor-screenshot-spinner" class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>'),
      title ='',
      actual_index;
    // where to seek when trying to setup the crossOrigin attribute for video
    var seek_recovery = false;

    function takeScreenshot() {
      var video = root.find('video').get(0),
        canvas = document.createElement("canvas");

      canvas.width = video.videoWidth * 1;
      canvas.height = video.videoHeight * 1;
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

      var img = document.createElement("img");
      img.src = canvas.toDataURL('image/jpeg', 0.95);

      return img.src ;
    }

    button.on('click', function() {
      console.log('FV Player Editor Screenshots: Taking screenshot for ' + actual_index);

      try {
        button.prop("disabled", true);

        var screenshot = takeScreenshot(),
          item = jQuery('.fv-player-playlist-item[data-index="'+index+'"]');

        spinner.insertAfter( item.find('#fv_wp_flowplayer_field_splash') ); // TODO: fix position

        // Check title
        if(item.find('#fv_wp_flowplayer_field_caption').val()){
          title = item.find('#fv_wp_flowplayer_field_caption').val();
        } else {
          title = item.find('#fv_wp_flowplayer_field_src').val();
        }
        var data = {
          'action': 'fv_player_splashcreen_action',
          'img': screenshot,
          'title': title,
          'security': fv_player_editor_conf.splashscreen_nonce
        };
      }
      catch(err) {
        var video_tag = root.find('video.fp-engine')[0];

        // try to set crossOrigin if it's a HTML5 video - no HLS or DASH
        if( video_tag && video_tag.crossOrigin != 'anonymous' && api.engine.engineName == 'html5' ) {
          console.log('FV Player Editor Screenshots: Reloading with CORS');

          // without this Flowplayer will remove that crossOrigin="anonymous" automatically!
          api.conf.nativesubtitles = true;

          video_tag.crossOrigin = 'anonymous';
          reload_video();
          return;
        }

        show_error();

        console.log('FV Player Editor Screenshots: '+err);

        return;
      }

      jQuery.post(fv_player.ajaxurl, data, function(response) {
        if(response.src) {

          console.log('FV Player Editor Screenshots: Got screenshot URL: '+response.src , 'video index: '+actual_index);

          var splashInput = fv_player_editor.get_field('splash').eq( actual_index ),
            wrap = splashInput.closest('.components-base-control__field');

          splashInput.val(response.src);
          wrap.css('background-color','#6ef442');

          // trigger autosave
          splashInput.trigger('keyup').trigger('change');
        }
        if(response.error) {
          fv_player_editor.add_notice('error', response.error, 2500);
          fv_player_editor.fv_wp_flowplayer_dialog_resize()
        }
        spinner.remove();
        button.prop("disabled", false);

        // trigger preview
        fv_wp_flowplayer_submit('refresh-button');
        setTimeout(function(){
          wrap.css('background-color','');
        }, 2000);
      });
    });

    // Compatibility test
    api.on('ready', function(e,api) {

      var ads_before = 0;

      actual_index = parseInt(fv_player_editor.get_current_video_index());

      if( api.conf.playlist.length > 0 ) {
        for ( var i in api.conf.playlist ) {
          // we need to check for ads before actual video
          if( typeof api.conf.playlist[i].click != 'undefined' ) {
            ads_before++;
          }

          // stop if we're at the current video
          if(i == api.video.index) {
            break;
          }
        }

        actual_index = api.video.index - ads_before;
      }

      console.log('FV Player Editor Screenshots: Video index: '+ actual_index);

      var src = fv_player_editor.get_field('src').eq( actual_index ).val(), // get current video src
        should_show = !! root.find('video').length;

      if ( typeof src != 'undefined' ) {
        fv_player_editor_conf_screenshots.disable_domains.forEach(function(item) {
          if( src.indexOf(item) !== -1 ) {
            console.log('FV Player Editor Screenshots: Not available for the video source domain.');
            should_show = false;
          }
        });

        // do not show for video ads
        if( typeof api.video.click != 'undefined' ) {
          should_show = false;
        }

        if( should_show ) {
          if ( root.find( '#fv-player-shortcode-editor-preview' ).length === 0 ) {
            button.appendTo('#fv-player-shortcode-editor-preview');
          } else {
            button.show();
          }

          try {
            takeScreenshot();
          } catch(err) {
            console.log('FV Player Editor Screenshots: Failed test with error: ' + err );
          }
        } else {
          console.log('FV Player Editor Screenshots: Unsupported video type' );

          button.hide();
        }
      }
    });

    // Resume video after setting crossOrigin
    api.on('resume progress', function() {
      if( seek_recovery && api.video.seekable ) {
        var time = seek_recovery;
          seek_recovery = false;

        api.seek( time, function() {
          api.pause();

          // ... and try to take the screenshot again
          button.click();
        });
      }
    });

    // Show error if video fails after setting crossOrigin
    api.on('error', function(e, api) {
      if( seek_recovery ) {
        // prevent FV Player Pro from trying to recover
        api.fv_retry_count = 100;

        console.log('FV Player Editor Screenshots: Video won\'t play with crossOrigin="anonymous"');

        show_error();

        fv_player_editor.reload_preview(fv_player_editor.get_current_video_index());
      }
    });

    function reload_video() {
      seek_recovery = api.video.time;

      var index = typeof(api.video.index) != "undefined" ? api.video.index : 0;

      api.error = api.loading = false;
      jQuery(root).removeClass("is-error").addClass("is-mouseover");

      if( api.conf.playlist.length ) {
        api.setPlaylist(api.conf.playlist).play(index);
      } else {
        api.load(api.conf.clip);
      }

    }

    function show_error() {
      spinner.remove();
      button.prop("disabled", false);
      fv_player_editor.add_notice('error', fv_player_editor_translations.screenshot_cors_error, 2500);
      fv_wp_flowplayer_dialog_resize();
    }

  });

  // Remove button, spinner
  jQuery(document).on('fv_flowplayer_player_editor_reset', function() {
    jQuery('#fv-splash-screen-button').remove();
    jQuery('#fv-editor-screenshot-spinner').remove();
    index = 0;
  });

  jQuery(document).on('fvp-preview-complete', function() {
    jQuery('#fv-splash-screen-button').remove();
    jQuery('#fv-editor-screenshot-spinner').remove();
  });

  // Video index
  jQuery(document).on('fv_flowplayer_shortcode_item_switch', function(e,i) {
    index = i;
  });

})(jQuery);
