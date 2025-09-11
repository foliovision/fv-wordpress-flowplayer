if( typeof(flowplayer) !== 'undefined') {
  var fv_autoplay_type = fv_flowplayer_conf.autoplay_preload,
    fv_player_scroll_autoplay = false,
    fv_player_scroll_autoplay_last_winner = -1;

  // Only autoplay if...
  if (
    // ...not in wp-admin, meaning in editor
    ! document.body.classList.contains( 'wp-admin' ) &&
    // ...not in Elementor editor
    ! document.body.classList.contains( 'elementor-editor-active' )
  ) {
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

      var players = jQuery('.flowplayer:not(.is-disabled)'),
        winner = -1;

      players.each( function(k,v) {
        var root = jQuery(this);

        // Autoplay disabled for he player
        if( typeof root.data('fvautoplay') != 'undefined' && root.data('fvautoplay') == -1 ) {
          return;
        }

        var api = root.data('flowplayer'),
          player = root.find('.fp-player'),
          player_autoplay = typeof root.data('fvautoplay') != 'undefined';

        // No ready yet
        if ( !player.length ) {
          return;
        }

        if ( api.non_viewport_pause ) {
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
            // The player is not too far down, at least 1/4 of the player is visible
            window_height - rect.top > player.height() / 4 &&
            // ...and the player is not too far up either, so that less than bottom 1/4 can be seen
            rect.bottom > player.height() / 4
          ) {
            // disabling for YouTube on iOS
            if( flowplayer.support.iOS && api.conf.clip.sources[0].type == 'video/youtube' ) {
              return;
            }

            winner = k;
          }
        }
      });

      // Pause the previously playing video
      if ( fv_player_scroll_autoplay_last_winner != winner ) {
        var root = players.eq( fv_player_scroll_autoplay_last_winner ),
          api = root.data('flowplayer');

        if( api && api.playing ) {
          console.log('Scroll autoplay: Player not in viewport, pausing ' + root.attr('id'));
          api.pause();
        }        
      }

      // Now play the winner
      if ( winner > -1 && fv_player_scroll_autoplay_last_winner != winner ) {
        var root = players.eq( winner ),
          api = root.data('flowplayer');

        if( !api ) {
          console.log('Scroll autoplay: Play ' + root.attr('id'));
          i++;
          fv_player_load( root );

          api.autoplayed = true;

        } else if( api.ready ) {
          console.log('Scroll autoplay: Resume ' + root.attr('id'));
          i++;
          api.resume();

        } else if( !api.loading && !api.playing && !api.error ) {
          console.log('Scroll autoplay: Load ' + root.attr('id'));
          i++;
          api.load();

          api.autoplayed = true;
        }

        fv_player_scroll_autoplay_last_winner = winner;
      }

      fv_player_scroll_autoplay = false;
    }, 200 );
  }
}
