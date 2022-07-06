flowplayer(function(api, root) {
  root = jQuery(root);
  if( flowplayer.conf.wpadmin || jQuery(root).hasClass('is-audio') ) return;
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']'),
    playlist_with_fullscreen =  playlist.hasClass('fp-playlist-season') || playlist.hasClass('fp-playlist-polaroid'),
    fsforce = root.data('fsforce') == true; // used for players which load using Ajax after click and then they need fullscreen
  
  if( root.data('fullscreen') == false ) {
    return;
  }
    
  // Force fullscreen on mobile setting
  if( flowplayer.conf.mobile_force_fullscreen && flowplayer.support.fvmobile || !flowplayer.support.fullscreen && fsforce || playlist_with_fullscreen ) {
    if( !flowplayer.support.fullscreen ) {
      api.bind('ready', function() {
        if( api.video.vr ) return;

        api.fullscreen(true);
      });
    }
    
    root.on('click', function() {
      if( !api.ready || api.paused ) api.fullscreen(true);
    });
    
    jQuery('[rel='+root.attr('id')+'] a').on('click', function(e) {
      if( !api.isFullscreen ) {
        api.fullscreen();
        api.resume();
      }
    });
    
    api.on('resume', function() {
      if( api.video.vr ) return;
      
      if( !api.isFullscreen ) api.fullscreen();
    });
    
    api.on('finish', function() {
      if( api.conf.playlist.length == 0 || api.conf.playlist.length -1 == api.video.index ) api.fullscreen(false);
    }).on('fullscreen', function(a,api) {
       root.addClass('forced-fullscreen');
    }).on('fullscreen-exit', function(a,api) {
       api.pause();
       root.removeClass('forced-fullscreen');
    });
  
  // only important if the player is loading with Ajax
  // on click and then you need to go to fullscreen
  // so at least you get the CSS fullscreen
  } else if( fsforce ) {
    var position, unload = root.find('.fp-unload'), is_closing = false;
    api.isFakeFullscreen = false;
    
    root.addClass('is-closeable');
    
    root.on('click', function(e) {
      if( !api.ready && e.target != unload[0] ) api.fakeFullscreen(true);
    });
    
    unload.on('click', function(e) {
      if( api.ready && api.isFullscreen ) {
        api.fullscreen(false);
      } else if( api.loading ) {
        is_closing = true;
        
        // triggering unload on ready didn't work with HLS.js
        api.one('resume', function(e) {
          // it's already closed!
          is_closing = false;
          api.pause();
        });
      }
      api.fakeFullscreen(false);

      // do not run Flowplayer unload() as that would reset the video time
      return false;
    });
    
    jQuery('[rel='+root.attr('id')+'] a').on('click', function(e) {
      if( !api.isFakeFullscreen ) {
        api.fakeFullscreen();
        api.resume();
      }
    });
    
    api.on('resume', function() {
      if( !is_closing && !api.isFakeFullscreen ) api.fakeFullscreen();
    }).on('finish', function() {
      if( api.conf.playlist.length == 0 || api.conf.playlist.length -1 == api.video.index ) api.fakeFullscreen(false);
    }).on('fullscreen', function(a,api) {
      root.removeClass('fake-fullscreen');
    }).on('fullscreen-exit', function(a,api) {
      if( api.isFakeFullscreen ) api.fakeFullscreen(true,true);
    }).on('unload', function(a,api) {
      // todo: ?? q key
    });
    
    api.fakeFullscreen = function( flag, force ) {
      if( !force && ( api.isFakeFullscreen == flag || api.disabled ) ) return;
      if( position === undefined ) position = root.css('position');      
      if( flag === undefined ) flag = !api.isFakeFullscreen;
      api.isFakeFullscreen = flag;
      api.trigger( flag ? 'fakefullscreen' : 'fakefullscreen-exit', [api] );
      root.toggleClass('is-fullscreen fake-fullscreen forced-fullscreen',flag)
      if( flag ) {        
        root.css('position','fixed');
      } else {
        root.css('position',position);
      }
    }
  }
  
  if( flowplayer.support.android && window.screen && window.screen.orientation ) {
    api.on('fullscreen', function(a,api) {
      if( is_portrait_video(api) ) { 
        screen.orientation.lock("portrait-primary");
      } else {
        screen.orientation.lock("landscape-primary");
      }
    });
  }

  var show_iphone_notice_timeout = false;

  // Since iPhone doesn't provide real fullscreen we show a hint to swipe up to remove location bar
  if( flowplayer.support.iOS && !flowplayer.support.fullscreen ) {
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

  function check_for_location_bar() {
    var is_portrait = window.innerWidth < window.innerHeight,
      magic_number = is_portrait ?
        // using 0.6 works on 375x628 window size, 0.575 on bigger iPhones, like 414x719px ones
        ( window.innerWidth <= 375 ? 0.6 : 0.575 ) :
        ( window.innerWidth <= 667 ? 2 : 2.4 ),
      has = window.innerWidth / window.innerHeight > magic_number;

    return has;
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