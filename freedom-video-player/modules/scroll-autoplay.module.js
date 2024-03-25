if( typeof(flowplayer) !== 'undefined') {
  var fv_autoplay_type = fv_flowplayer_conf.autoplay_preload,
    fv_player_scroll_autoplay = false;

  freedomplayer(function(api, root) {
    fv_player_scroll_autoplay = true;

    api.on('pause', function(e,api) {
      if(api.manual_pause) {
        console.log('Scroll autoplay: Manual pause for ' + jQuery(root).attr('id'));
        api.non_viewport_pause = true;
      }
    });
  })

  jQuery(window).on( 'scroll', function() {
    fv_player_scroll_autoplay = true;
  } );

  var fv_player_scroll_int = setInterval( function() {
    if( !fv_player_scroll_autoplay ) {
      return;
    }

    var i = 0,
      window_height = (window.innerHeight || document.documentElement.clientHeight );

    jQuery('.flowplayer:not(.is-disabled)').each( function(k,v) {
      var root = jQuery(this);

      // Autoplay disabled for he player
      if( typeof root.data('fvautoplay') != 'undefined' && root.data('fvautoplay') == -1 ) {
        return;
      }

      // FV Player in wp-admin = in editor - should not autoplay
      if( jQuery('body').hasClass('wp-admin') ) return;

      var api = root.data('flowplayer'),
        player = root.find('.fp-player'),
        player_autoplay = typeof root.data('fvautoplay') != 'undefined';

      // No ready yet
      if ( !player.length ) {
        return;
      }

      if( fv_autoplay_type == 'viewport' || fv_autoplay_type == 'sticky' || player_autoplay ) { // play video when on viewport or sticky or player enabled autoplay
        var rect = player[0].getBoundingClientRect(); // watch .fp-player because root can ve outside viewport when stickied

        // prevent play arrow and control bar from appearing for a fraction of second for an autoplayed video
        // var play_icon = root.find('.fp-play').addClass('invisible'),
        // control_bar = root.find('.fp-controls').addClass('invisible');

        // api.one('progress', function() {
        //   play_icon.removeClass('invisible');
        //   control_bar.removeClass('invisible');
        // });

        if(
          i == 0 &&
          player.height() < window_height && (
            rect.top > 0 && ( rect.top + player.height() / 4 ) < window_height ||
            rect.bottom > player.height() / 4 && rect.bottom <= window_height
          ) ||
          // If player is taller than the viewport at least 1/2 has to be visible
          player.height() > window_height && (
            rect.top <= window_height / 2 && rect.bottom > window_height / 2
          )
        ) {
          // disabling for YouTube on iOS
          if( flowplayer.support.iOS && api.conf.clip.sources[0].type == 'video/youtube' ) {
            return;
          }

          if( jQuery('.freedomplayer.is-playing').length > 0 || jQuery('.freedomplayer.is-loading').length > 0 ) return; // prevent multiple autoplays & pick first video when there are multiple videos in viewport

          if( !api ) {
            console.log('Scroll autoplay: Play ' + root.attr('id'));
            i++;
            fv_player_load( root );

            api.autoplayed = true;

          } else if( api.ready && api.viewport_pause && !api.non_viewport_pause ) {
            api.viewport_pause = false;
            console.log('Scroll autoplay: Resume ' + root.attr('id'));
            i++;
            api.resume();

          } else if( !api.loading && !api.playing && !api.error && !api.non_viewport_pause ) {
            api.viewport_pause = false;
            console.log('Scroll autoplay: Load ' + root.attr('id'));
            i++;
            api.load();

            api.autoplayed = true;
          }
        } else {
          if( api && api.playing ) {
            console.log('Scroll autoplay: Player not in viewport, pausing ' + root.attr('id'));
            api.viewport_pause = true;
            api.pause();
          }
        }
      }
    });
    fv_player_scroll_autoplay = false;
  }, 200 );
}
