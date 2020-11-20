
/**
 * FV Flowplayer additions!
 */
if( typeof(fv_flowplayer_conf) != "undefined" ) {
  try {
    if(typeof(window.localStorage) == 'object' && typeof(window.localStorage.volume) != 'undefined'){
      delete fv_flowplayer_conf.volume;
    }
  } catch(e) {}
  
  if(typeof fv_flowplayer_conf.chromecast === "undefined") {
    fv_flowplayer_conf.chromecast = false;
  }

  flowplayer.conf = fv_flowplayer_conf;
  flowplayer.conf.embed = false;
  flowplayer.conf.share = false;

  // we had a problem that some websites would change the key in HTML if stored as $62\d+
  try {
    flowplayer.conf.key = atob(flowplayer.conf.key);
  } catch(e) {}

  if( !flowplayer.support.android && flowplayer.conf.dacast_hlsjs ) {
    function FVAbrController(hls) {      
      this.hls = hls;
      this.nextAutoLevel = 3;
    }
    
    FVAbrController.prototype.nextAutoLevel = function(nextLevel) {
      this.nextAutoLevel = nextLevel;
    }
    
    FVAbrController.prototype.destroy = function() {}
    
    flowplayer.conf.hlsjs = {      
      startLevel: -1, // todo: doesn't seem to work, fix it to pick quality matching the player size
      abrController: FVAbrController
    }
  }
  
  // iOS 13 and desktop Safari above version 8 support MSE, so let's use HLS.js there
  if(
    flowplayer.support.iOS && parseInt(flowplayer.support.iOS.version) >= 13 ||
    !flowplayer.support.iOS && flowplayer.support.browser.safari && parseInt(flowplayer.support.browser.version) >= 8
  ) {
    flowplayer.conf.hlsjs.safari = true;
  }
  
  flowplayer.support.fvmobile = !!( !flowplayer.support.firstframe || flowplayer.support.iOS || flowplayer.support.android );
  
  var fls = flowplayer.support;
  if( flowplayer.conf.mobile_native_fullscreen && ( 'ontouchstart' in window ) && fls.fvmobile ) {
    flowplayer.conf.native_fullscreen = true;
  }
  
  if( 'ontouchstart' in window ) {    
    if( fls.android && fls.android.version < 4.4 && ! ( fls.browser.chrome && fls.browser.version > 54 ) ) {
      flowplayer.conf.native_fullscreen = true;
    }
    
    if( fls.iOS && ( fv_player_in_iframe() || fls.iOS.version < 7 ) ) {
      flowplayer.conf.native_fullscreen = true;
    }
  }
}
if( typeof(fv_flowplayer_translations) != "undefined" ) {
  flowplayer.defaults.errors = fv_flowplayer_translations;
}

//  did autoplay?
var fv_player_did_autoplay = false;

function fv_player_videos_parse(args, root) {
  try {
    var videos = JSON.parse(args);
  } catch(e) {
    return false;
  }
  
  var regex = new RegExp("[\\?&]fv_flowplayer_mobile=([^&#]*)");
	var results = regex.exec(location.search);	
	if(
		(
			(results != null && results[1] == 'yes') ||
			(jQuery(window).width() <= 480 || jQuery(window).height() <= 480) //  todo: improve for Android with 1.5 pixel ratio 
		)
		&&
		(results == null || results[1] != 'no')
	) {
    var fv_fp_mobile = false;
    jQuery(videos.sources).each( function(k,v) {
      if(v.mobile) {
        videos.sources[k] = videos.sources[0];
        videos.sources[0] = v;
        fv_fp_mobile = true;
      }
      if( fv_fp_mobile ) {
        jQuery(root).after('<p class="fv-flowplayer-mobile-switch">'+fv_flowplayer_translations.mobile_browser_detected_1+' <a href="'+document.URL+'?fv_flowplayer_mobile=no">'+fv_flowplayer_translations.mobile_browser_detected_2+'</a>.</p>');
      }
    });
  }
  return videos;
}

function fv_player_in_iframe() {
  try {
      return window.self !== window.top;
  } catch (e) {
      return true;
  }
}



jQuery(document).ready( function() {
  var loading_count = 0;
  var loading = setInterval( function() {
    loading_count++;
    if( loading_count < 1000 && (
      window.fv_video_intelligence_conf && !window.FV_Player_IMA ||
      window.fv_vast_conf && !window.FV_Player_IMA ||
      window.fv_player_pro && !window.FV_Flowplayer_Pro && document.getElementById('fv_player_pro') != fv_player_pro
    ) ) {      
      return;
    }
    clearInterval(loading);
    fv_player_preload();
  }, 10 );
});

function fv_escape_attr(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function fv_player_preload() {
 
  if( flowplayer.support.touch ) {
    jQuery('.fp-playlist-external.fv-playlist-design-2017').addClass('visible-captions');
  }

  flowplayer( function(api,root) {
    root = jQuery(root);
    var fp_player = root.find('.fp-player');
    var splash_click = false;

    if( root.hasClass('fixed-controls') ) {
      root.find('.fp-controls').click( function(e) {
        if( !api.loading && !api.ready ) {
          e.preventDefault();
          e.stopPropagation(); 
          api.load();
        }
      });
    }
    
    if( !flowplayer.support.volume && !flowplayer.support.autoplay ) { // iPhone iOS 11 doesn't support setting of volume, but the button it important to allow unmuting of autoplay videos
      root.find('.fp-volume').hide();
    }
    
    if( root.data('fullscreen') == false ) {
      root.find('.fp-fullscreen').remove();
    }

    if( root.data('volume') == 0 && root.hasClass('no-controlbar') ) {
      root.find('.fp-volume').remove();
    }
    
    // failsafe is Flowplayer is loaded outside of fv_player_load()
    var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
    if( ( !api.conf.playlist || api.conf.playlist.length == 0 ) && playlist.length && playlist.find('a[data-item]').length > 0 ) {  // api.conf.playlist.length necessary for iOS 9 in some setups
      var items = [];      
      playlist.find('a[data-item]').each( function() {
        if( parsed = fv_player_videos_parse(jQuery(this).attr('data-item'), root) ) {
          items.push(parsed);
        } else {
          jQuery(this).remove();
        }
      });
      api.conf.playlist = items;
      api.conf.clip = items[0];
    } else if( !api.conf.clip ){
      api.conf.clip = fv_player_videos_parse(jQuery(root).attr('data-item'), root);
    }
    
    //  playlist item click action
    jQuery('a',playlist).click( function(e) {
      e.preventDefault();

      splash_click = true;

      var
        $this = jQuery(this),
        playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']'),
        index = jQuery('a',playlist).index(this);
        $prev = $this.prev('a'),
        item = $this.data('item');

      if ($prev.length && $this.is(':visible') && !$prev.is(':visible')) {
        $prev.click();
        return false;
      }

      if( jQuery( '#' + $this.parent().attr('rel') ).hasClass('dynamic-playlist') ) return;
      
      var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
      
      fv_player_playlist_active(playlist,this);
      
      if( api ) {
        if( api.error ) {
          api.pause();
          api.error = api.loading = false;
          root.removeClass('is-error');
          root.find('.fp-message.fp-shown').remove();
        }
        
        if( !api.video || api.video.index == index ) return;
        api.play( index );
      }

      var new_splash = item.splash;
      if( !new_splash ) {
        new_splash = $this.find('img').attr('src');
      }
      
      player_splash(root, fp_player, item, new_splash);

      var rect = root[0].getBoundingClientRect();
      if((rect.bottom - 100) < 0){
        jQuery('html, body').animate({
          scrollTop: jQuery(root).offset().top - 100
        }, 300);
      }
    } );
    
    var playlist_external = jQuery('[rel='+root.attr('id')+']');
    var playlist_progress = false;

    var splash_img = root.find('.fp-splash');
    var splash_text = root.find('.fv-fp-splash-text');

    function player_splash(root, fp_player, item, new_splash) {
      var splash_img = root.find('img.fp-splash');
    
      // do we have splash to show?
      if( new_splash ) {
        // if the splash element missing? Create it!
        if( splash_img.length == 0 ) {
          splash_img = jQuery('<img class="fp-splash" />');
          fp_player.prepend(splash_img)
        }
    
        splash_img.attr('alt', item.fv_title ? fv_escape_attr(item.fv_title) : 'video' );
        splash_img.attr('src', new_splash );
    
      // remove the splash image if there is nothing present for the item
      } else if( splash_img.length ) {
        splash_img.remove(); 
      }
    }

    api.bind("load", function (e,api,video) {
      if ( !api.conf.playlist.length ) { // no need to run if not in playlist
        return;
      }

      if( video.type.match(/^audio/) && !splash_click ) {
        var anchor = playlist_external.find('a').eq(video.index);
        var item = anchor.data('item');
        var new_splash = item.splash;
        if( !new_splash ) { // parse the splash from HTML if not found in playlist item
          new_splash = anchor.find('img').attr('src');
        }
        player_splash(root, fp_player, item, new_splash);
      }
      splash_click = false;
    });

    api.bind('ready', function(e,api,video) {
      //console.log('playlist mark',video.index);
      setTimeout( function() {
        if( video.index > -1 ) {
          if( playlist_external.length > 0 ) {
            var playlist_item = jQuery('a',playlist_external).eq(video.index);
            fv_player_playlist_active(playlist_external,playlist_item);
            playlist_progress = playlist_item.find('.fvp-progress');
          }
        }
      }, 100 );

      splash_img = root.find('.fp-splash'); // must update, alt attr can change

      // Show splash img if audio
      if( !video.type.match(/^audio/) ) {
        splash_img.remove();
        splash_text.remove();
      }
    } );

    api.bind( 'unload', function() {
      jQuery('.fp-playlist-external .now-playing').remove();
      jQuery('.fp-playlist-external a').removeClass('is-active');

      fp_player.prepend(splash_text);
      fp_player.prepend(splash_img);

      playlist_progress = false;
    });
    
    api.bind( 'progress', function( e, api, time ) {
      if( playlist_progress.length ) {
        api.playlist_thumbnail_progress( playlist_progress, api.video, time );
      }
    });
    
    api.bind( 'error-subtitles', function() {console.log('error-subtitles');
      fv_player_notice(root,fv_flowplayer_translations[8],2000);
    });

    //is this needed?  
    var playlist = jQuery(root).parent().find('div.fp-playlist-vertical[rel='+jQuery(root).attr('id')+']');  
    if( playlist.length ){
      function check_size_and_all(args) {
        var property = playlist.hasClass('fp-playlist-only-captions') ? 'height' : 'max-height';
        if( playlist.parents('.fp-playlist-text-wrapper').hasClass('is-fv-narrow') ){
          property = 'max-height';
        }
        playlist.css(property,vertical_playlist_height());
        if( property == 'max-height' ) playlist.css('height','auto');
      }
      check_size_and_all();
      jQuery(window).on('resize tabsactivate', function() {
        setTimeout( check_size_and_all, 0 );
      } );
    }
    
    function vertical_playlist_height(args) {
      var height = root.height();
      if( height == 0 ) height = root.css('max-height');
      return height;
    }
  });
  
  //sets height for embedded players 
  if( window.self != window.top && !location.href.match(/fv_player_preview/) ){
    embed_size();
    jQuery(window.self).resize(embed_size);
  }
  
  function embed_size() {
    jQuery('.flowplayer.fp-is-embed').each( function() {
      var root = jQuery(this);
      if( !root.hasClass('has-chapters') && !root.hasClass('has-transcript') && jQuery('.fp-playlist-external[rel='+root.attr('id')+']').length == 0 ) {
        root.height(jQuery(window).height());
      }
    });
  }
  
  //  Playlist - old style
  if( typeof(fv_flowplayer_playlists) != "undefined" ) {
    for( var i in fv_flowplayer_playlists ) {
      if( !fv_flowplayer_playlists.hasOwnProperty(i) ) continue;
      jQuery('#'+i).flowplayer( { playlist: fv_flowplayer_playlists[i] });
    }
  }
  
  fv_player_load();
  fv_autoplay_exec();
  
  jQuery(document).ajaxComplete( function() {  
    fv_player_load();
  });
  
  jQuery(window).on('hashchange',fv_autoplay_exec);
}


function fv_player_load() {
  
  jQuery('.flowplayer' ).each( function(i,el) {
    var root = jQuery(el);
    var api = root.data('flowplayer');
    if( api ) return;
    
    if( root.attr('data-item') ) {
      root.flowplayer( { clip: fv_player_videos_parse(root.attr('data-item'), root) });
    } else if( playlist = jQuery( '[rel='+root.attr('id')+']' ) ) {
      if ( playlist.find('a[data-item]').length == 0 ) return;  //  respect old playlist script setup
      
      var items = [];
      playlist.find('a[data-item]').each( function() {
        if( parsed = fv_player_videos_parse(jQuery(this).attr('data-item'), root) ) {
          items.push(parsed);
        } else {
          jQuery(this).remove();
        }
      });

      root.flowplayer( { playlist: items } );
    }
  } );
  
  jQuery('.fv-playlist-slider-wrapper').each( function(i,el) {
    var items = jQuery(this).find('a');
    jQuery(this).find('.fp-playlist-external').css( 'width', items.outerWidth() * items.length );
  });
  
  if( typeof(jQuery().tabs) != "undefined" ) {
    jQuery('body').removeClass('fv_flowplayer_tabs_hide');
    jQuery('.fv_flowplayer_tabs_content').tabs();
  }

}


function fv_player_playlist_active(playlist,item) {
  if(playlist) {
    jQuery('a',playlist).removeClass('is-active');
    jQuery('.now-playing').remove();
  }
  
  $playlist = jQuery(playlist);
  $item = jQuery(item);

  var scroll_parent = false;
  
  $item.addClass('is-active');
  var is_design_2014 = $playlist.hasClass('fv-playlist-design-2014');
  if( ( is_design_2014 && $item.find('h4').length == 0 || !is_design_2014 ) && $item.find('.now-playing').length == 0 ) $item.prepend('<strong class="now-playing"><span>'+fv_flowplayer_translations.playlist_current+'</span></strong>');
  
  // adjust playlist to the encompassing DIV, if the actual playlist element itself is wrapped inside
  // another element to enable CSS scrolling
  if (!$playlist.parent().find('.flowplayer').length) {
    scroll_parent = true;
  }
  
  // scroll to the currently playing video if playlist type is vertical or horizontal
  if ( (
        $playlist.hasClass('fp-playlist-vertical') ||
        $playlist.hasClass('fp-playlist-horizontal') && $playlist.hasClass('is-audio') // this combination is also a vertical playlist basically
        ) && !fullyVisibleY($item.get(0)) ) {
    var $el = (scroll_parent ? $playlist.parent() : $playlist);
    $el.animate({
      scrollTop: $el.scrollTop() + ($item.position().top - $el.position().top)
    }, 750);
  
    //$playlist.scrollTop($playlist.scrollTop() + ($item.position().top - $playlist.position().top));
  } else if ($playlist.hasClass('fp-playlist-horizontal') && !fullyVisibleX($item.get(0))) {
    var $el = (scroll_parent ? $playlist.parent() : $playlist);
    $el.animate({
      scrollLeft: $el.scrollLeft() + ($item.position().left - $el.position().left)
    }, 750);
  }
  
  function fullyVisibleY(el) {
    var rect = el.getBoundingClientRect(), top = rect.top, height = rect.height,
      bottom = (top + height), el = el.parentNode;
    do {
      rect = el.getBoundingClientRect();
      if (bottom <= rect.bottom === false) return false;
      if (top <= rect.top) return false;
      el = el.parentNode;
    } while (el != document.body);
    // Check its within the document viewport
    return bottom <= document.documentElement.clientHeight;
  }
  
  function fullyVisibleX(el) {
    var rect = el.getBoundingClientRect(), left = rect.left, width = rect.width,
      right = (left + width), el = el.parentNode;
    do {
      rect = el.getBoundingClientRect();
      if (right <= rect.right === false) return false;
      if (left <= rect.left) return false;
      el = el.parentNode;
    } while (el != document.body);
    // Check its within the document viewport
    return right <= document.documentElement.clientWidth;
  }  
}


jQuery( function() {
  jQuery('.flowplayer').each( function() {
    flowplayer.bean.off(jQuery(this)[0],'contextmenu');
  });
} );

var fv_fp_date = new Date();
var fv_fp_utime = fv_fp_date.getTime();




/* *
 * Anchor Sharing + Playlist Autoplay
 */

//Makes sharable slug
function fv_parse_sharelink(src){
  src = src.replace('https?://[^./].','')
  var prefix = 'fvp_';
  if(src.match(/(youtube.com)/)){
    return prefix + src.match(/(?:v=)([A-Za-z0-9_-]*)/)[1]; 
  }else if(src.match(/(vimeo.com)|(youtu.be)/)){
    return prefix + src.match(/(?:\/)([^/]*$)/)[1];
  }else{
    var match = src.match(/(?:\/)([^/]*$)/);
    if(match){
      return prefix + match[1].match(/^[^.]*/)[0];
    }
  }
  return prefix + src;
}

function fv_player_get_video_link_hash(api) {
  var hash = fv_parse_sharelink( typeof(api.video.sources_original) != "undefined" && typeof(api.video.sources_original[0]) != "undefined" ? api.video.sources_original[0].src : api.video.sources[0].src);

  if( typeof(api.video.id) != "undefined" ) {
    hash = fv_parse_sharelink(api.video.id.toString());
  }

  return hash;
}

function fv_player_time_hms(seconds) {

  if(isNaN(seconds)){
    return NaN;
  }

  var date = new Date(null);
  date.setSeconds(seconds); // specify value for SECONDS here
  var timeSrting = date.toISOString().substr(11, 8);
  timeSrting = timeSrting.replace(/([0-9]{2}):([0-9]{2}):([0-9]{2}\.?[0-9]*)/,'$1h$2m$3s').replace(/^00h(00m)?/,'').replace(/^0/,'');
  return timeSrting;
}

function fv_player_time_seconds(time, duration) {

  if(!time)
    return -1;

  var seconds = 0;
  var aTime = time.replace(/[hm]/g,':').replace(/s/,'').split(':').reverse();

  if( typeof(aTime[0]) != "undefined" ) seconds += parseFloat(aTime[0]);
  if( typeof(aTime[1]) != "undefined" ) seconds += parseInt(60*aTime[1]);
  if( typeof(aTime[2]) != "undefined" ) seconds += parseInt(60*60*aTime[2]);

  return duration ? Math.min(seconds, duration) : seconds;
}

//Autoplays the video, queues the right video on mobile
function fv_autoplay_init(root, index ,time){
  if( fv_autoplay_exec_in_progress ) return;

  fv_autoplay_exec_in_progress = true;  

  var api = root.data('flowplayer');
  if(!api) return;

  var fTime = fv_player_time_seconds(time);

  if(root.parent().hasClass('ui-tabs-panel')){
    var tabId = root.parent().attr('id');
    jQuery('[aria-controls=' + tabId + '] a').click();
  }

  if( !root.find('.fp-player').attr('class').match(/\bis-sticky/) ){    
    var offset = jQuery(root).offset().top - (jQuery(window).height() - jQuery(root).height()) / 2;    
    window.scrollTo(0,offset);
    api.one('ready',function(){
      window.scrollTo(0,offset);
    });
  }
  if(root.hasClass('lightboxed')){
    setTimeout(function(){
      jQuery('[href=\\#' + root.attr('id')+ ']').click();
    },0);
  }

  // todo: refactor!
  if(index){
    if( fv_player_video_link_autoplay_can(api,parseInt(index)) ) {
      if( api.ready ) {
        if( fTime > -1 ) api.seek(fTime);
        fv_autoplay_exec_in_progress = false;
        
      } else {
        api.play(parseInt(index));
        api.one('ready', function() {
          fv_autoplay_exec_in_progress = false;
          if( fTime > -1 ) api.seek(fTime)
        } );
      }
    } else if( flowplayer.support.inlineVideo ) {
      api.one( api.playing ? 'progress' : 'ready', function (e,api) {
        api.play(parseInt(index));
        api.one('ready', function() {
          fv_autoplay_exec_in_progress = false;
          if( fTime > -1 ) api.seek(fTime)
        } );
      });
      
      root.find('.fp-splash').attr('src', jQuery('[rel='+root.attr('id')+'] div').eq(index).find('img').attr('src')); // select splachscreen from playlist items by id

      if( !fv_player_in_iframe() ) {
        fv_player_notice( root, fv_flowplayer_translations[11], 'progress' );
      }
    }
  }else{
    if( api.ready ) {
      if( fTime > -1 ) api.seek(fTime);
      fv_autoplay_exec_in_progress = false;
      
    } else {
      if( fv_player_video_link_autoplay_can(api) ) {
        api.load();
      } else if ( !fv_player_in_iframe() ) {
        fv_player_notice( root, fv_flowplayer_translations[11], 'progress' );
      }
      api.one('ready', function() {
        fv_autoplay_exec_in_progress = false;
        if( fTime > -1 ) {
          var do_seek = setInterval( function() {
            if( api.loading ) return;
            api.seek(fTime)
            clearInterval(do_seek);
          }, 10 );
        }
      } );
    }
  }
  
}




var fv_autoplay_exec_in_progress = false;

function fv_autoplay_exec(){
  var autoplay = true;
  //anchor sharing
  if( typeof (flowplayer) !== "undefined" && typeof(fv_flowplayer_conf) != "undefined"  && fv_flowplayer_conf.video_hash_links && window.location.hash.substring(1).length ) {
    var aHash = window.location.hash.match(/\?t=/) ? window.location.hash.substring(1).split('?t=') : window.location.hash.substring(1).split(',');
    var hash = aHash[0];
    var time = aHash[1] === undefined ? false : aHash[1];
    jQuery('.flowplayer').each(function(){
      var root = jQuery(this);
      if(root.hasClass('lightbox-starter')){
        root = jQuery(root.attr('href'));
      }
      var api = root.data('flowplayer');
      if(!api) return;
      
      var playlist = typeof(api.conf.playlist) !== 'undefined' && api.conf.playlist.length > 1 ? api.conf.playlist : [ api.conf.clip ];          

      // first play if id is set
      for( var item in playlist ) {
        if( !playlist.hasOwnProperty(item) ) continue;

        var id = (typeof(playlist[item].id) !== 'undefined') ? fv_parse_sharelink(playlist[item].id.toString()) : false;
        if( hash === id && autoplay ){
          console.log('fv_autoplay_exec for '+id,item);
          fv_autoplay_init(root, parseInt(item),time);
          autoplay = false;
          return false;
        }
      }

      for( var item in playlist ) {
        if( !playlist.hasOwnProperty(item) ) continue;

        var src = fv_parse_sharelink(playlist[item].sources[0].src);
        if( hash === src  && autoplay ){
          console.log('fv_autoplay_exec for '+src,item);
          fv_autoplay_init(root, parseInt(item),time);
          autoplay = false;
          return false;
        }
      }
    });
  }

  // If no video is matched by URL hash string, process autoplay
  if( autoplay && flowplayer.support.firstframe ) {
    jQuery('.flowplayer[data-fvautoplay]').each( function() {
      var root = jQuery(this);
      var api = root.data('flowplayer');
      if( !fv_player_did_autoplay && root.data('fvautoplay') ) {
        if( !( ( flowplayer.support.android || flowplayer.support.iOS ) && api && api.conf.clip.sources[0].type == 'video/youtube' ) ) { // don't let these mobile devices autoplay YouTube
          fv_player_did_autoplay = true;
          api.load();

          // prevent play arrow and control bar from appearing for a fraction of second for an autoplayed video
          var play_icon = root.find('.fp-play').addClass('invisible'),
            control_bar = root.find('.fp-controls').addClass('invisible');
            
          api.one('progress', function() {
            play_icon.removeClass('invisible');
            control_bar.removeClass('invisible');
          });

          if(root.data('fvautoplay') == 'muted') {
            api.mute(true,true);
          }
        }
      }
    });
  }
}

function fv_player_video_link_autoplay_can( api, item ) {  
  var video = item ? api.conf.playlist[item] : api.conf.clip;
  
  if( video.sources[0].type == 'video/youtube' && ( flowplayer.support.iOS || flowplayer.support.android ) || fv_player_in_iframe() ) return false;
  
  return flowplayer.support.firstframe;
}




/*
 *  Player notices
 */
function fv_player_notice(root, message, timeout) {
  var notices = jQuery('.fvfp-notices',root);
  if( !notices.length ) {
    notices = jQuery('<div class="fvfp-notices">');    
    jQuery('.fp-player',root).append(notices);
  }
  
  var notice = jQuery('<div class="fvfp-notice-content">'+message+'</div></div>');  
  notices.append(notice);
  if ( typeof(timeout) == 'string' ) {
    var player = jQuery(root).data('flowplayer');
    player.on(timeout, function() {
      notice.fadeOut(100,function() { jQuery(this).remove(); });
    } );
  }
  if ( timeout > 0 ) {
    setTimeout( function() {
      notice.fadeOut(2000,function() { jQuery(this).remove(); });
    }, timeout );
  }
  return notice;
}




var fv_player_clipboard = function(text, successCallback, errorCallback) {
  try {
    fv_player_doCopy(text);
    successCallback();
  } catch (e) {
    if( typeof(errorCallback) != "undefined" ) errorCallback(e);
  }
};

function fv_player_doCopy(text) {
  var textarea = document.createElement('textarea');
  textarea.value = text;
  textarea.style.opacity = 0;
  textarea.style.position = 'absolute';
  textarea.setAttribute('readonly', true);
  document.body.appendChild(textarea);

  // Check if there is any content selected previously.
  var selected = document.getSelection().rangeCount > 0 ?
    document.getSelection().getRangeAt(0) : false;

  // iOS Safari blocks programmtic execCommand copying normally, without this hack.
  // https://stackoverflow.com/questions/34045777/copy-to-clipboard-using-javascript-in-ios
  if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
    var editable = textarea.contentEditable;
    textarea.contentEditable = true;
    var range = document.createRange();
    range.selectNodeContents(textarea);
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    textarea.setSelectionRange(0, 999999);
    textarea.contentEditable = editable;
  } else {
    textarea.select();
  }

  try {
    var result = document.execCommand('copy');

    // Restore previous selection.
    if (selected) {
      document.getSelection().removeAllRanges();
      document.getSelection().addRange(selected);
    }

    document.body.removeChild(textarea);

    return result;
  } catch (err) {
    throw new Error('Unsuccessfull');
  }
}

if( location.href.match(/elementor-preview=/) ) {
  console.log('FV Player: Elementor editor is active');
  setInterval( fv_player_load, 1000 );
}