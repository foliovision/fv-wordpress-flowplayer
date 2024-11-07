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

  var show_iphone_notice_timeout = false;

  // Since iPhone doesn't provide real fullscreen we show a hint to swipe up to remove location bar
  if( flowplayer.support.iOS && !flowplayer.support.fullscreen && !flowplayer.conf.native_fullscreen ) {
    api.on('fullscreen', show_iphone_notice );
    window.addEventListener('resize', show_iphone_notice );
    window.addEventListener('resize', function() {
      // No location bar? We are all good!
      if( !check_for_location_bar() ) {
        clearTimeout(show_iphone_notice_timeout);
        show_iphone_notice_timeout = false;
        api.trigger('resize-good');
      }
    } );
  }

  function check_for_location_bar() {
    var is_landscape = window.innerWidth > window.innerHeight;

    /**
     * We compare the screen width to the viewport height, as we care about landscape only.
     * 26 px is the size taken by the shrinked location bar in Chrome when using maximum iOS text size.
     * So if the difference is bigger than that the location bar is showing.
     */
    if ( is_landscape && window.screen && window.screen.width && window.screen.width - window.innerHeight > 26 ) {
      return true;
    }

    return false;
  }

  function show_iphone_notice() {
    if( api.isFullscreen && window.innerWidth > window.innerHeight && check_for_location_bar() && !show_iphone_notice_timeout ) {
      // Show the notice until the location bar disappears
      fv_player_notice( root, fv_flowplayer_translations.iphone_swipe_up_location_bar, 'resize-good' );

      // Hide the notice after 5 seconds
      show_iphone_notice_timeout = setTimeout( function() {
        show_iphone_notice_timeout = false;

        api.trigger('resize-good');
      }, 5000 );
    }
  }

});
