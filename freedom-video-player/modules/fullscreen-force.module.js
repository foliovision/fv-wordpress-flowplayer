flowplayer(function(api, root) {
  root = jQuery(root);

  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']'),
    playlist_with_fullscreen =  playlist.hasClass('fp-playlist-season') || playlist.hasClass('fp-playlist-polaroid'),
    fsforce = root.data('fsforce') == true; // used for players which load using Ajax after click and then they need fullscreen

  if( flowplayer.conf.wpadmin && ! playlist_with_fullscreen || jQuery(root).hasClass('is-audio') ) return;

  // Fullscreen or forcing of fullscreen might be disabled for this player
  if( root.data('fullscreen') == false || root.data('fsforce') == false ) {
    return;
  }

  /**
   * Force fullscreen on mobile setting
   * or if forcing fullscreen
   * or if it's playlist type that requires fullscreen
   */
  if( flowplayer.conf.mobile_force_fullscreen && flowplayer.support.fvmobile || fsforce || playlist_with_fullscreen ) {
    // iPhone
    if( !flowplayer.support.fullscreen ) {
      api.bind('ready', function() {
        if( api.video.vr ) return;

        api.one( 'progress', function() {
          api.fullscreen(true);
        });
      });

    // Android
    } else {
      root.on('click', function() {
        if( !api.ready || api.paused ) api.fullscreen(true);
      });
    }

    jQuery('[rel='+root.attr('id')+'] a').on('click', function(e) {
      if( !api.isFullscreen ) {
        api.fullscreen();
        api.resume();
      }
    });

    api.on('resume', function() {
      if( api.video.vr ) return;

      if( !api.isFullscreen ) {
        if( !flowplayer.support.fullscreen ) {
          api.one( 'progress', function() {
            api.fullscreen(true);
          });

        } else {
          api.fullscreen();
        }
      }
    });

    api.on('finish', function() {
      if( api.conf.playlist.length == 0 || api.conf.playlist.length -1 == api.video.index ) api.fullscreen(false);
    }).on('fullscreen', function(a,api) {
       root.addClass('forced-fullscreen');
    }).on('fullscreen-exit', function(a,api) {
       api.pause();
       root.removeClass('forced-fullscreen');
    });
  }

  if( flowplayer.support.android && flowplayer.conf.mobile_landscape_fullscreen && window.screen && window.screen.orientation ) {
    api.on('fullscreen', function(a,api) {
      if( is_portrait_video(api) ) {
        screen.orientation.lock("portrait-primary");
      } else {
        screen.orientation.lock("landscape-primary");
      }
    });
  }

  /**
   * Does the video has portrait orientation? = Is the height is larger than width?
   * @param {object} api - Flowplayer object
   * @return {bool} True if portrait, false if landscape
   */
  function is_portrait_video( api ) {
    // If the video dimensions are not known assume it's wide and landscape mode should be used
    // TODO: Instead fix HLS.js engine to report video width and height properly
    return (typeof api.video.width != 'undefined' && typeof api.video.height != 'undefined') && (api.video.width != 0 && api.video.height != 0 && api.video.width < api.video.height);
  }

  var notice_timeouts = {
    'iphone_swipe_up_location_bar' : false,
    'iphone_swipe_up_browser'      : false,
  }

  function debounce(func, wait) {
    var timeout;
    return function() {
      clearTimeout(timeout);
      timeout = setTimeout(func, wait);
    };
  }

  var debounced_maybe_show_iphone_notice = debounce( maybe_show_iphone_notice, 10 );

  // Since iPhone doesn't provide real fullscreen we show a hint to swipe up to remove location bar
  if( flowplayer.support.iOS && !flowplayer.support.fullscreen && !flowplayer.conf.native_fullscreen ) {
    api.on('fullscreen', debounced_maybe_show_iphone_notice );
    window.addEventListener('resize', debounced_maybe_show_iphone_notice );
  }

  /**
   * We compare the screen width to the viewport height, as we care about landscape only.
   * 26 px is the size taken by the shrinked location bar when using maximum iOS text size.
   * If we find difference bigger than that the location bar is showing.
   */
  function check_for_location_bar( threshold = 26 ) {
    var is_landscape = window.innerWidth > window.innerHeight;

    return is_landscape && window.screen && window.screen.width && window.screen.width - window.innerHeight > threshold;
  }

  function maybe_show_iphone_notice() {
    if( api.isFullscreen ) {
      if ( check_for_location_bar() ) {
        show_iphone_notice_worker( 'iphone_swipe_up_location_bar' );

      } else if ( check_for_location_bar( 8 ) ) {
        show_iphone_notice_worker( 'iphone_swipe_up_browser' );

      } else {
        remove_iphone_notices();
      }
    }
  }

  function show_iphone_notice_worker( notice_id ) {

    // Remove all other related notices added with fv_player_notice()
    remove_iphone_notices( notice_id );

    if ( ! notice_timeouts[ notice_id ] ) {
      // Wait before the old notice disappears - fv_player_notice() has 100 ms fade out when removing
      setTimeout( function() {
        fv_player_notice(root, fv_flowplayer_translations[ notice_id ], 'notice-' + notice_id );
      }, 100 );

      // Remove the notice after 5 seconds
      notice_timeouts[ notice_id ] = setTimeout( function() {
        notice_timeouts[ notice_id ] = false;
        api.trigger( 'notice-' + notice_id );
      }, 5000 );
    }
  };

  function remove_iphone_notices( keep ) {
    for ( var notice_id in notice_timeouts ) {
      if ( keep && notice_id == keep ) {
        continue;
      }

      if ( notice_timeouts[ notice_id ] ) {
        clearTimeout( notice_timeouts[ notice_id ] );
        notice_timeouts[ notice_id ] = false;
        api.trigger( 'notice-' + notice_id );
      }
    }
  }

});
