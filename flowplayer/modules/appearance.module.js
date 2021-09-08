/*
 * Player size dependent classes
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var player = root.find('.fp-player'),
    had_no_volume = root.hasClass('no-volume'),
    had_fp_mute = root.hasClass('fp-mute'),
    had_fp_full = root.hasClass('fp-full'),
    buttons_count = 0;

  function check_size() {
    var width = player.width() || root.width(),
      video_index = api.video.index ? api.video.index : 0;

    if(width > 900) {
      jQuery('.fp-subtitle',root).addClass('is-wide');
    } else {
      jQuery('.fp-subtitle',root).removeClass('is-wide');
    }

    // core Flowplayer classes which are normally added in requestAnimationFrame, which increases CPU load too much
    root.toggleClass('is-tiny', width < 400);
    root.toggleClass('is-small', width < 600 && width >= 400 );

    // if the player is too narrow we put the timeline on top of the control bar
    var too_narrow = width < 480 + buttons_count*35;

    // move timeline when timeline chapters are enabled
    if( typeof api.fv_timeline_chapters_data != 'undefined' && typeof api.fv_timeline_chapters_data[video_index] != 'undefined' ) {
      too_narrow = true;
    }
    // we do so by adding .fp-full if it was not there, it needs to stay on for AB loop bar too!
    if( !had_fp_full ) {
      root.toggleClass('fp-full', root.hasClass('has-abloop') || too_narrow );
    }

    var size = '';
    if( width < 400 ) size = 'is-tiny';
    else if( width < 600 && width >= 400 ) size = 'is-small';
    root.trigger('fv-player-size', [ size ] );

    var el = player;
    if( root.parent().hasClass('fp-playlist-vertical-wrapper') || root.parent().hasClass('fp-playlist-text-wrapper') ) el = root.parent(); // in some cases we use the wrapper

    if(el.width() <= 560) {
      el.addClass('is-fv-narrow');
    } else {
      el.removeClass('is-fv-narrow');
    }
    
    if(width <= 320) { // remove volue bar on narrow players
      root.addClass('no-volume fp-mute');
    } else {
      if( !had_no_volume ) root.removeClass('no-volume');
      if( !had_fp_mute ) root.removeClass('fp-mute');
    }
  }

  check_size();

  jQuery(window).on('resize',check_size);

  api.on('ready fullscreen fullscreen-exit sticky sticky-exit', function(e) {
    setTimeout( function() {
      buttons_count = root.find('.fp-controls > strong:visible').length + root.find('.fp-controls > .fp-icon:visible').length;
      check_size();
    }, 0);
  });

  api.on('unload pause finish error',function(){
    if(typeof(checker) !== 'undefined')
      clearInterval(checker);
  })
})


jQuery(window).on('resize tabsactivate',function(){
  jQuery('.fp-playlist-external').each(function(){
    var playlist = jQuery(this);
    if( playlist.parent().width() >= 900 ) playlist.addClass('is-wide');
    else playlist.removeClass('is-wide');
  })
}).trigger('resize');

flowplayer(function(api, root) {
  /*
   *  Chrome 55>= video download button fix 
   */  
  api.bind('ready', function() {
    if( /Chrome/.test(navigator.userAgent) && parseFloat(/Chrome\/(\d\d)/.exec(navigator.userAgent)[1], 10) > 54 ) {
      if( api.video.subtitles ) {
        jQuery(root).addClass('chrome55fix-subtitles');
      } else {
        jQuery(root).addClass('chrome55fix');
      }
    }
  });
  
  /*
   *  Splash dimension bugfix
   */
  root = jQuery(root);
  var image_src = root.css('background-image')
  if( image_src ) {
    image_src = image_src.replace(/url\((['"])?(.*?)\1\)/gi, '$2').split(',');
    if( !image_src || !image_src[0].match(/^(https?:)?\/\//) ) return;      
    var image = new Image();
    image.src = image_src[0];
    
    var image_ratio = image.height/image.width;
    var player_ratio = root.height()/root.width();
    
    var ratio_diff = Math.abs(player_ratio - image_ratio);
    if( ratio_diff < 0.05 ) {
      root.css('background-size','cover');
    }
    
  }
});

/*
 *  Basic Iframe YouTube and Vimeo responsiveness
 */
(function($) {
  $(window).on('resize',function(){
    var iframe = $('iframe[id][src][height][width]'); 
    iframe.each(function(){
      if( $(this).attr('id').match(/(fv_vimeo_)|(fv_ytplayer_)/) && $(this).width() <= $(this).attr('width') )
        $(this).height( $(this).width() * $(this).attr('height') / $(this).attr('width') );
    })
    
    var wistia = jQuery('.wistia_embed'); 
    wistia.each(function(){      
      $(this).height( $(this).width() * $(this).data('ratio') );
    })
  }).trigger('resize');
})(jQuery);

/*
 *  Tabbed playlist
 */
jQuery(document).on("tabsactivate", '.fv_flowplayer_tabs_content', function(event, ui){
  var oldPlayer = jQuery('.flowplayer.is-playing').data('flowplayer');
  if( typeof(oldPlayer) != "undefined" ) {
    oldPlayer.pause();
  }
  
  var objPlayer = jQuery('.flowplayer',ui.newPanel);
  var api = objPlayer.data('flowplayer');
  api.load();  
});