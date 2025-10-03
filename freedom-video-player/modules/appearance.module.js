/*
 * Player size dependent classes
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var player = root.find('.fp-player'),
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

    // check if there are too many items in .fp-controls and they don't fit
    var controls = root.find('.fp-controls'),
      controls_width = controls.parent().width(),
      // here are the items that we can hide
      controls_to_sacrifice = controls.find('.fp-duration, .fp-playbtn'),
      controls_items_width = 0;

    controls_to_sacrifice.removeClass( 'wont-fit' );
    root.find('.fp-controls').children(':visible:not(.fp-timeline)').each( function() {
      controls_items_width += jQuery(this).outerWidth(true);
    } );

    if ( controls_items_width > controls_width ) {
      controls_to_sacrifice.addClass( 'wont-fit' );
    }
  }

  // This improves the performance about 2x, but what if we find a way of running them all at once?
  if (typeof window.requestAnimationFrame === 'function') {
    requestAnimationFrame( check_size );

  } else {
    check_size();
  }
  

  function debounce(func, wait) {
    var timeout;
    return function() {
      clearTimeout(timeout);
      timeout = setTimeout(func, wait);
    };
  }

  var debouncedCheckSize = debounce( check_size, 250 );

  window.addEventListener('resize', debouncedCheckSize);

  if ('fonts' in document) {
    api.one('load', function() {
      document.fonts.load('1em flowplayer');
    });
  }

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


/**
 * What's below must also be updated in flowplayer.php which creates the pure-JavaScript version of it for pageload.
 */
jQuery(window).on( 'resize tabsactivate', freedomplayer_playlist_size_check );

function freedomplayer_playlist_size_check(){
  jQuery('.fp-playlist-external').each(function(){
    var playlist = jQuery(this),
      parent_width = playlist.parent().width(),
      // the playlist wrapper might be getting some max-width CSS applied, so we check that
      playlist_max_width = playlist.css('max-width').match(/%/) ? playlist.width() : parseInt(playlist.css('max-width')),
      // we use the above max-width if it's not more than parent width or the parent width
      width = playlist_max_width > 0 && playlist_max_width < parent_width ? playlist_max_width : parent_width;

    if( playlist.parent().width() >= 900 ) playlist.addClass('is-wide');
    else playlist.removeClass('is-wide');

    if (playlist.hasClass('fp-playlist-polaroid') || playlist.hasClass('fp-playlist-version-one') || playlist.hasClass('fp-playlist-version-two')) {
      var limit = playlist.hasClass('fp-playlist-version-one') || playlist.hasClass('fp-playlist-version-two') ? 200 : 150,
        fit_thumbs = Math.floor(width / limit);

      if (fit_thumbs > 8) fit_thumbs = 8;
      else if (fit_thumbs < 2) fit_thumbs = 2;
      playlist.css('--fp-playlist-items-per-row', String(fit_thumbs));
    }
  })
}

jQuery( document ).ready( freedomplayer_playlist_size_check );

flowplayer(function(api, root) {
  root = jQuery(root);

  api.setLogoPosition = function() {
    let is_old_safari =
      freedomplayer.support.browser.safari && parseFloat( freedomplayer.support.browser.version ) < 14.1 ||
      freedomplayer.support.iOS && parseFloat( freedomplayer.support.iOS.version ) < 15;

    if ( api.conf.logo_over_video && api.video && api.video.width && api.video.height && ! is_old_safari ) {
      root.find('.fp-logo').css('--fp-aspect-ratio', ( api.video.width / api.video.height).toFixed(2));
    } else {
      root.find('.fp-logo').css('width', '100%').css('height', '100%');
    }
  }

  api.bind('ready', function( e, api, video ) {
    api.setLogoPosition();

    // Remove Top and Bottom Black Bars
    if ( video.remove_black_bars ) {
      root.addClass('remove-black-bars');
    } else {
      root.removeClass('remove-black-bars');
    }

    /*
     *  Chrome 55>= video download button fix
     */
    if( /Chrome/.test(navigator.userAgent) && parseFloat(/Chrome\/(\d\d)/.exec(navigator.userAgent)[1], 10) > 54 ) {
      if( api.video.subtitles ) {
        jQuery(root).addClass('chrome55fix-subtitles');
      } else {
        jQuery(root).addClass('chrome55fix');
      }
    }
  });

  /*
   * YouTube player class
   */
  var is_youtube = false;
  jQuery(api.conf.playlist).each( function(k,v) {
    if( v.sources[0].type.match(/youtube/) ) is_youtube = true;
  });

  if( is_youtube ) {
    root.addClass('is-youtube');
  }

  api.bind("ready", function (e,api,video) {
    if( video.type == 'video/youtube' ) {
      root.addClass('is-youtube');
    } else {
      root.removeClass('is-youtube');
    }
  });
});

/*
 *  Basic Iframe YouTube and Vimeo responsiveness
 */
(function($) {
  $(window).on('resize',function(){
    var iframe = $('iframe[id][src][height][width]');
    iframe.each(function(){
      if( $(this).attr('id').match(/fv_vimeo_/) && $(this).width() <= $(this).attr('width') )
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
  var oldPlayer = jQuery( ui.oldPanel ).find('.flowplayer').data('flowplayer');

  // pause old player to make sure it does not keep playing while the new video is loading
  if( typeof(oldPlayer) != "undefined" ) {
    oldPlayer.pause();
  }

  // load new player
  var objPlayer = jQuery('.flowplayer',ui.newPanel);
  var api = objPlayer.data('flowplayer');
  api.load();
});
