if ( typeof( flowplayer ) !== 'undefined' ) {

  // Only autoplay if...
  if (
    // ...not in wp-admin, meaning in editor
    ! document.body.classList.contains( 'wp-admin' ) &&
    // ...not in Elementor editor
    ! document.body.classList.contains( 'elementor-editor-active' ) &&
    // Video Link feature must not be passed via URL
    ! location.href.match(/fvp_/)
  ) {
    freedomplayer( function(api, root) {
      root = jQuery(root);

      // Allows other plugins to wait with the autoplay until certain conditions are met, such as the age gate is passed
      if ( ! freedomplayer.did_scroll_autoplay_check && ! window.fv_player_autoplay_wait ) {
        freedomplayer.did_scroll_autoplay_check = true;

        // Trigger the scroll handler to load the first video
        debouncedScrollHandler();
      }

      var player_autoplay = typeof root.data( 'fvautoplay' ) != 'undefined',
        autoplay_type = fv_flowplayer_conf.autoplay_preload;

      if ( autoplay_type == 'viewport' || autoplay_type == 'sticky' || player_autoplay ) {
        api.on( 'pause', function( e, api ) {
          if ( api.manual_pause ) {
            fv_player_log( 'FV Player Scroll autoplay: User paused video, disabling scroll autoplay' );

            if ( is_scroll_container ) {
              jQuery( scroll_container ).off( 'scroll', debouncedScrollHandler );
            } else {
              jQuery( window ).off( 'scroll', debouncedScrollHandler );
            }
          }

        } ).on( 'resume', function( e, api ) {
          if ( api.manual_resume && 'sticky' !== autoplay_type ) {
            fv_player_log( 'FV Player Scroll autoplay: User resumed video, enabling scroll autoplay' );

            if ( is_scroll_container ) {
              jQuery( scroll_container ).on( 'scroll', debouncedScrollHandler );
            } else {
              jQuery( window ).on( 'scroll', debouncedScrollHandler );
            }
          }
        } );
      }
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
      past_winner = -1,
      have_autoplay = false,
      first_run = true;

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
          fv_player_log( 'FV Player Scroll autoplay: Not supported for YouTube on iOS' );
          return;
        }

        // play video when on viewport or sticky or player enabled autoplay
        if( autoplay_type == 'viewport' || autoplay_type == 'sticky' || player_autoplay ) {
          have_autoplay = true;

          var rect = v.getBoundingClientRect();
          if (
            // The player is not too far down, at least 1/4 of the player is visible
            height - rect.top > player.height() / 2 &&
            // ...and the player is not too far up either, so that less than bottom 1/4 can be seen
            rect.bottom > player.height() / 4 ||
            api.playing && api.is_sticky
          ) {
            current_winner = k;

            if ( past_winner === k ) {
              past_winner = -1;
            }
          }
        }
      });

      if ( ! have_autoplay ) {
        return;
      }

      // No scroll happened
      if ( current_winner === previous_winner && ! first_run ) {
        return;
      }

      first_run = false;

      fv_player_log( 'FV Player Scroll autoplay: STATUS current_winner: ' + current_winner + ' previous_winner: ' + previous_winner + ' past_winner: ' + past_winner );

      // Unload the video that went out of the viewport earlier
      if ( past_winner > -1 ) {
        let past_api = players.eq( past_winner ).data( 'freedomplayer' );
        if ( past_api.video && past_api.video.type == 'video/youtube' ) {
          fv_player_log( 'FV Player Scroll autoplay: PAST unload skipped for YouTube', current_winner + 1 );

        } else {
          fv_player_log( 'FV Player Scroll autoplay: PAST unload', past_winner );

          // Bring back the splash screen argument to make sure the unload actually removes the video
          past_api.conf.splash = true;
          past_api.unload();
        }
      }

      // Pause the video that just went out of viewport
      if ( previous_winner > -1 ) {
        let previous_api = players.eq( previous_winner ).data( 'freedomplayer' );
        if ( previous_api.playing ) {
          fv_player_log( 'FV Player Scroll autoplay: PREVIOUS pause', previous_winner );

          previous_api.pause();
        }

        let root = players.eq( previous_winner )[0];
        if (
          typeof root.fv_player_vast == 'object' &&
          root.fv_player_vast.adsManager_ &&
          typeof root.fv_player_vast.adsManager_ == 'object' &&
          typeof root.fv_player_vast.adsManager_.getRemainingTime == 'function' &&
          root.fv_player_vast.adsManager_.getRemainingTime() > 0
        ) {
          fv_player_log( 'FV Player Scroll autoplay: PREVIOUS pause VAST', previous_winner );

          root.fv_player_vast.adsManager_.pause();
        }
      }

      // Play the video in the viewport
      if ( current_winner > - 1 ) {
        let api = players.eq( current_winner ).data( 'freedomplayer' );

        delete( api.sticky_exclude );

        let root = players.eq( current_winner )[0];
        if ( 
          typeof root.fv_player_vast == 'object' &&
          root.fv_player_vast.adsManager_ &&
          typeof root.fv_player_vast.adsManager_ == 'object' &&
          typeof root.fv_player_vast.adsManager_.getRemainingTime == 'function' &&
          root.fv_player_vast.adsManager_.getRemainingTime() > 0
        ) {
          fv_player_log( 'FV Player Scroll autoplay: WINNER resume VAST', previous_winner );

          root.fv_player_vast.adsManager_.resume();

        } else if ( api.ready ) {
          fv_player_log( 'FV Player Scroll autoplay: WINNER resume', current_winner );

          api.resume();

        } else if ( api.loading ) {
          fv_player_log( 'FV Player Scroll autoplay: WINNER wait', current_winner );

          api.one( 'ready', function() {
            api.resume();
          } );

        } else {
          fv_player_log( 'FV Player Scroll autoplay: WINNER load', current_winner );

          api.load();

          // This ensures the YouTube video will attempt to unmute itself when the video is played
          api.autoplayed = true;
        }

        if (
          'sticky' === fv_flowplayer_conf.autoplay_preload ||
          'all' === freedomplayer.conf.sticky_video ||
          'desktop' === freedomplayer.conf.sticky_video && jQuery( window ).innerWidth() >= freedomplayer.conf.sticky_min_width ||
          players.eq( current_winner ).data( 'fvsticky' )
        ) {
          fv_player_log( 'FV Player Scroll autoplay: Found a winner for the sticky autoplay, stopping scroll autoplay' );

          if ( is_scroll_container ) {
            jQuery( scroll_container ).off( 'scroll', debouncedScrollHandler );
          } else {
            jQuery( window ).off( 'scroll', debouncedScrollHandler );
          }
        }
      }

      // Preload the next video
      if ( players.eq( current_winner + 1 ) ) {
        let preload_api = players.eq( current_winner + 1 ).data( 'freedomplayer' );
        if ( preload_api && ! preload_api.ready ) {

          // Check if fv_vast_conf.version is lower than 8.1
          var is_vast_version_below_81 = false;
          if (typeof fv_vast_conf !== 'undefined' && fv_vast_conf.version) {
            var version_parts = fv_vast_conf.version.split('.');
            var major = parseInt(version_parts[0], 10) || 0;
            var minor = parseInt(version_parts[1], 10) || 0;
            // If major < 8 or (major == 8 && minor < 1)
            is_vast_version_below_81 = (major < 8) || (major === 8 && minor < 1);
          }

          if ( is_vast_version_below_81 ) {
            fv_player_log( 'FV Player Scroll autoplay: PRELOAD skipped for VAST version below 8.1', current_winner + 1 );
          
          } else if ( preload_api.conf.clip && preload_api.conf.clip.sources[0].type == 'video/youtube' ) {
            fv_player_log( 'FV Player Scroll autoplay: PRELOAD skipped for YouTube', current_winner + 1 );

          } else {
            fv_player_log( 'FV Player Scroll autoplay: PRELOAD load', current_winner + 1 );

            // Preload the video, setting splash to false will ensure it won't play right away
            preload_api.conf.splash = false;
            // Bypass viewport check as we might be preloading video below the fold.
            preload_api.force_preload = true
            preload_api.sticky_exclude = true;
            preload_api.load();

            // Clean up the flag as otherwise Dash.js would not let user play the video.
            delete( preload_api.force_preload );
          }
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
