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

    var i = 0;

    jQuery('.flowplayer:not(.is-disabled)').each( function(k,v) {
      var root = jQuery(this);

      if( typeof root.data('fvautoplay') != 'undefined' && root.data('fvautoplay') == -1 ) {
        return;
      }

      if( jQuery('body').hasClass('wp-admin') ) return;

      var api = root.data('flowplayer'),
        autoplay = root.attr('data-fvautoplay');

      if( fv_autoplay_type == 'viewport' || fv_autoplay_type == 'sticky' ) { // play video when on viewport or sticky
        var rect = root[0].getBoundingClientRect();

        // prevent play arrow and control bar from appearing for a fraction of second for an autoplayed video
        // var play_icon = root.find('.fp-play').addClass('invisible'),
        // control_bar = root.find('.fp-controls').addClass('invisible');

        // api.one('progress', function() {
        //   play_icon.removeClass('invisible');
        //   control_bar.removeClass('invisible');
        // });

        // looks for a single player which is in the middle of screen
        // and it also has to be further down than the currently playing player
        // ...if the conservative mode is on
        if( i == 0 && rect.top > 0 && ( rect.top + 32 ) < (window.innerHeight || document.documentElement.clientHeight) ||
        rect.bottom > 16 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) ) {
          // disabling for YouTube on iOS
          if( flowplayer.support.iOS && api.conf.clip.sources[0].type == 'video/youtube' ) {
            return;
          }

          if( jQuery('.freedomplayer.is-playing .is-sticky').length > 0 ) return; // bail out if we have sticky

          if( !api ) {
            console.log('Scroll autoplay: Play ' + root.attr('id'));
            i++;
            fv_player_load( root );

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

            if( autoplay == 'muted' ) {
              console.log('Scroll autoplay: mute!');
              api.mute(true,true);
            }

          }
        } else {
          if( api && api.playing && fv_autoplay_type == 'viewport' ) {
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
