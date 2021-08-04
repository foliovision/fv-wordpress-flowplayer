/*
 *  Sharing bar, redirect feature, loop, disabling rightclick and obscuring the video URL in errors
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  
  root.find('.fp-logo').removeAttr('href');
  
  if( root.hasClass('no-controlbar') ) {    
    var timelineApi = api.sliders.timeline;
    timelineApi.disable(true);
    api.bind('ready',function() {
      timelineApi.disable(true);
    });
  }
  
  jQuery('.fvfp_admin_error', root).remove();
  
  root.find('.fp-logo, .fp-header').on('click', function(e) {
    if (e.target !== this) return;
    root.find('.fp-ui').trigger('click');
  });
    
  jQuery('.fvp-share-bar .sharing-facebook',root).append('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="#fff"><title>Facebook</title><path d="M11.9 5.2l-2.6 0 0-1.6c0-0.7 0.3-0.7 0.7-0.7 0.3 0 1.6 0 1.6 0l0-2.9 -2.3 0c-2.6 0-3.3 2-3.3 3.3l0 2 -1.6 0 0 2.9 1.6 0c0 3.6 0 7.8 0 7.8l3.3 0c0 0 0-4.2 0-7.8l2.3 0 0.3-2.9Z"/></svg>');
  jQuery('.fvp-share-bar .sharing-twitter',root).append('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="#fff"><title>Twitter</title><path d="M16 3.1c-0.6 0.3-1.2 0.4-1.9 0.5 0.7-0.4 1.2-1 1.4-1.8 -0.6 0.4-1.3 0.6-2.1 0.8 -0.6-0.6-1.4-1-2.4-1 -2 0.1-3.2 1.6-3.2 4 -2.7-0.1-5.1-1.4-6.7-3.4 -0.9 1.4 0.2 3.8 1 4.4 -0.5 0-1-0.1-1.5-0.4l0 0.1c0 1.6 1.1 2.9 2.6 3.2 -0.7 0.2-1.3 0.1-1.5 0.1 0.4 1.3 1.6 2.2 3 2.3 -1.6 1.7-4.6 1.4-4.8 1.3 1.4 0.9 3.2 1.4 5 1.4 6 0 9.3-5 9.3-9.3 0-0.1 0-0.3 0-0.4 0.6-0.4 1.2-1 1.6-1.7Z"/></svg>');
  jQuery('.fvp-share-bar .sharing-email',root).append('<svg xmlns="http://www.w3.org/2000/svg" height="16" viewBox="0 0 16 16" width="16" fill="#fff"><title>Email</title><path d="M8 10c0 0 0 0-1 0L0 6v7c0 1 0 1 1 1h14c1 0 1 0 1-1V6L9 10C9 10 8 10 8 10zM15 2H1C0 2 0 2 0 3v1l8 4 8-4V3C16 2 16 2 15 2z"/></svg>');
    
  jQuery('.fp-header',root).prepend( jQuery('.fvp-share-bar',root) );
  
  if( api.conf.playlist.length ) {
    // Check if playlist is only single video with video ads
    var show = true;
    var playlist = api.conf.playlist;

    if( playlist.length == 2 ){
      // video ad, single video
      if( typeof(playlist[0].click) != 'undefined' && typeof(playlist[1].click) == 'undefined' ) {
        show = false;
      }
      // single video, video ad
      if( typeof(playlist[0].click) == 'undefined' && typeof(playlist[1].click) != 'undefined' ) {
        show = false;
      }
    } else if( playlist.length == 3 ) {
      // video ad, single video, video ad
      if( typeof(playlist[0].click) != 'undefined' && typeof(playlist[1].click) == 'undefined' && typeof(playlist[2].click) != 'undefined') {
        show = false;
      }
    }

    // Add prev and next buttons
    if (show) {
      var prev = jQuery('<a class="fp-icon fv-fp-prevbtn"></a>');
      var next = jQuery('<a class="fp-icon fv-fp-nextbtn"></a>');
      root.find('.fp-controls .fp-playbtn').before(prev).after(next);
      prev.on('click', function() {
        api.trigger('prev',[api]);
        api.prev();
      });
      next.on('click', function() {
        api.trigger('next',[api]);
        api.next();
      });
    }
  }
  
  api.bind("pause resume finish unload ready", function(e,api) {
    root.addClass('no-brand');
  });

  api.one('ready', function() {
    root.find('.fp-fullscreen').clone().appendTo( root.find('.fp-controls') );
  });

  api.bind("ready", function (e, api, video) {
    setTimeout( function () {
      jQuery('.fvp-share-bar',root).show();
      
      jQuery('.fv-player-buttons-wrap',root).appendTo(jQuery('.fv-player-buttons-wrap',root).parent().find('.fp-ui'));
    }, 100 );
  });

  api.bind('finish', function() {
    var url = root.data('fv_redirect');
    if( url && ( typeof(api.video.is_last) == "undefined" || api.video.is_last ) ) {
      location.href = url;
    }
  });
  
  if( flowplayer.support.iOS && flowplayer.support.iOS.version == 11 ) {
    api.bind('error',function(e,api,error){
      if( error.code == 4 ) root.find('.fp-engine').hide();
    });
  }
  
  jQuery(document).on('contextmenu', '.flowplayer', function(e) {
    e.preventDefault();
  });
  
  api.one("ready", function (e, api, video) {
    root.find('.fp-chromecast').insertAfter( root.find('.fp-header .fp-fullscreen') );
  });
  
  // replacing loading SVG with CSS animation
  root.find('.fp-waiting').html('<div class="fp-preload"><b></b><b></b><b></b><b></b></div>');    
  
  var id = root.attr('id'),
    alternative = !flowplayer.conf.native_fullscreen && flowplayer.conf.mobile_alternative_fullscreen,
    events_enter = 'fakefullscreen',
    events_exit = 'fakefullscreen-exit';  
  
  if( !flowplayer.support.fullscreen ) {
    events_enter += ' fullscreen';
    events_exit += ' fullscreen-exit';
  }

  api.bind( events_enter, function(e,api) {
    jQuery('#wpadminbar, .nc_wrapper').hide();
    if( alternative || e.type == 'fakefullscreen' ) {
      if( api.video.type == 'video/youtube' ) return;		
      root.before('<span data-fv-placeholder="'+id+'"></span>');
      root.appendTo('body');
    }
  });
  api.bind( events_exit, function(e,api,video) {
    jQuery('#wpadminbar, .nc_wrapper').show();
    if( alternative || e.type == 'fakefullscreen-exit' ) {
      jQuery('span[data-fv-placeholder='+id+']').replaceWith(root);
    }
  });
  
});