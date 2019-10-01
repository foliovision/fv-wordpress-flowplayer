flowplayer(function(api, root) {
  root = jQuery(root);
  if( flowplayer.conf.wpadmin || jQuery(root).hasClass('is-audio') ) return;
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']'),
    fsforce = root.data('fsforce') == true || playlist.hasClass('fp-playlist-season') || playlist.hasClass('fp-playlist-polaroid');
  
  // Force fullscreen on mobile setting
  if( flowplayer.conf.mobile_force_fullscreen && flowplayer.support.fvmobile || !flowplayer.support.fullscreen && fsforce ) {
    if( !flowplayer.support.fullscreen ) {
      api.bind('ready', function() {
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
  
  } else if( fsforce ) {
    var position, unload = root.find('.fp-unload'), is_closing = false;
    api.isFakeFullscreen = false;
    
    unload.show();
    
    root.on('click', function(e) {
      if( !api.ready && e.target != unload[0] ) api.fakeFullscreen(true);
    });
    
    unload.on('click', function(e) {
      if( api.ready ) {
        api.fullscreen(false);
      } else if( api.loading ) {
        is_closing = true;
        api.one('ready', function(e) {
          api.unload();
        })
      }
      api.fakeFullscreen(false);
      
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
  
  if( flowplayer.support.android && flowplayer.conf.mobile_landscape_fullscreen ) {
    api.on('fullscreen', function(a,api) {
      screen.orientation.lock("landscape-primary");
    })
  }
  
});