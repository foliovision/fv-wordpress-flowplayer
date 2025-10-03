if ( typeof( flowplayer ) !== 'undefined' ) {

  // Only autoplay if...
  if (
    // ...not in wp-admin, meaning in editor
    ! document.body.classList.contains( 'wp-admin' ) &&
    // ...not in Elementor editor
    ! document.body.classList.contains( 'elementor-editor-active' )
  ) {
    freedomplayer( function(api, root) {
      
      // Allows other plugins to wait with the autoplay until certain conditions are met, such as the age gate is passed
      if ( ! freedomplayer.did_scroll_autoplay_check && ! window.fv_player_autoplay_wait ) {
        freedomplayer.did_scroll_autoplay_check = true;

        // Trigger the scroll handler to load the first video
        debouncedScrollHandler();
      }

      api.on( 'pause', function( e, api ) {
        if ( api.manual_pause ) {
          // TODO: Do we want this at all? If so, we must fix the status for the preloaded video.
          // console.log( 'Scroll autoplay: User paused video, disabling scroll autoplay' );

          // jQuery( scroll_container ).off( 'scroll', debouncedScrollHandler );
        }
      });
    } );

    /**
     * Look for FV Player scroll container if you want to for example use autoplay in a snap scroll container.
     * A snap scroll container would not trigger window scroll event, we need to listen on it directly.
     */
    var scroll_container = jQuery( '.fv-player-scroll-container' ),
      is_scroll_container = scroll_container.length > 0;

    /**
     * Initialize the variables
     */
    var autoplay_type = fv_flowplayer_conf.autoplay_preload

    var current_winner = -1,
      previous_winner = -1;
      past_winner = -1;

    /**
     * Debounce the scroll handler
     */
    function debounce( func, wait ) {
      let timeout;
      return function executedFunction( ...args ) {
        const later = function() {
          clearTimeout( timeout );
          func( ...args );
        }
        clearTimeout( timeout );
        timeout = setTimeout( later, wait );
      };
    }

    var debouncedScrollHandler = debounce( handleScroll, 100 );

    if ( is_scroll_container ) {
      players = scroll_container.find( '.freedomplayer:not(.is-disabled)' );
      scroll_container.on( 'scroll', debouncedScrollHandler );
    } else {
      players = jQuery( '.freedomplayer:not(.is-disabled)' );
      jQuery( window ).on( 'scroll', debouncedScrollHandler );
    }

    // Scroll handler function
    function handleScroll() {

      /**
       * This make sure the preload_api calls below work on iOS. The reason is that Freedom Video Player only allows
       * the video preload if it's in viewport and it checks again on the scroll event.
       *
       * TODO: Add a way of preloading the video even if it's not in viewport.
       */
      if ( is_scroll_container ) {
        freedomplayer.bean.fire( document, 'scroll' );
      }

      let height = is_scroll_container ? jQuery( scroll_container ).height() : jQuery( window ).height();

      players.each( function( k, v ) {
        var root = jQuery( v );

        // Autoplay disabled for the player
        if( typeof root.data('fvautoplay') != 'undefined' && root.data('fvautoplay') == -1 ) {
          return;
        }

        // FV Player in wp-admin = in editor - should not autoplay
        if( jQuery('body').hasClass('wp-admin') ) return;

        var api = root.data( 'freedomplayer' ),
          player = root.find( '.fp-player' ),
          player_autoplay = typeof root.data( 'fvautoplay' ) != 'undefined';

        // No ready yet
        if ( !player.length ) {
          return;
        }

        // disabling for YouTube on iOS
        if( freedomplayer.support.iOS && api.conf.clip.sources[0].type == 'video/youtube' ) {
          return;
        }

        // play video when on viewport or sticky or player enabled autoplay
        if( autoplay_type == 'viewport' || autoplay_type == 'sticky' || player_autoplay ) {
          var rect = v.getBoundingClientRect();
          if (
            // The player is not too far down, at least 1/4 of the player is visible
            height - rect.top > player.height() / 2 &&
            // ...and the player is not too far up either, so that less than bottom 1/4 can be seen
            rect.bottom > player.height() / 4
          ) {
            current_winner = k;

            if ( past_winner === k ) {
              past_winner = -1;
            }
          }
        }
      });

      // No scroll happened
      if ( current_winner === previous_winner ) {
        return;
      }

      console.log( 'STATUS current_winner: ' + current_winner + ' previous_winner: ' + previous_winner + ' past_winner: ' + past_winner );

      // Unload the video that went out of the viewport earlier
      if ( past_winner > -1 ) {
        let past_api = players.eq( past_winner ).data( 'freedomplayer' );
        console.log( 'PAST unload', past_winner );

        // Bring back the splash screen argument to make sure the unload actually removes the video
        past_api.conf.splash = true;
        past_api.unload();
      }

      // Pause the video that just went out of viewport
      if ( previous_winner > -1 ) {
        let previous_api = players.eq( previous_winner ).data( 'freedomplayer' );
        if ( previous_api.playing ) {
          console.log( 'PREVIOUS pause', previous_winner );

          previous_api.pause();
        }
      }

      // Play the video in the viewport
      if ( current_winner > - 1 ) {
        let api = players.eq( current_winner ).data( 'freedomplayer' );
        if ( api.ready ) {
          console.log( 'WINNER resume', current_winner );

          api.resume();

        } else if ( api.loading ) {
          console.log( 'WINNER wait', current_winner );

          api.one( 'ready', function() {
            api.resume();
          } );

        } else {
          console.log( 'WINNER load', current_winner );

          api.load();
        }

        let preload_api = players.eq( current_winner + 1 ).data( 'freedomplayer' );
        if ( preload_api && ! preload_api.ready ) {
          console.log( 'PRELOAD load', current_winner + 1 );

          // Preload the video, setting splash to false will ensure it won't play right away
          preload_api.conf.splash = false;
          preload_api.load();
        }
      }

      // Keep track of the previous and past winners
      if ( past_winner !== previous_winner ) {
        past_winner = previous_winner;
      }

      if ( current_winner !== previous_winner ) {
        previous_winner = current_winner;
      }
    }
  }
}
