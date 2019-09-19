/*
FV Flowplayer additions!
*/
if( typeof(fv_flowplayer_conf) != "undefined" ) {
  try {
    if(typeof(window.localStorage) == 'object' && typeof(window.localStorage.volume) != 'undefined'){
      delete fv_flowplayer_conf.volume;
    }
  } catch(e) {}
  
  flowplayer.conf = fv_flowplayer_conf;
  flowplayer.conf.embed = false;
  flowplayer.conf.share = false;
  
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
  
  flowplayer.support.fvmobile = !!( !flowplayer.support.firstframe || flowplayer.support.iOS || flowplayer.support.android );
  
  var fls = flowplayer.support;
  if( flowplayer.conf.mobile_native_fullscreen && ( 'ontouchstart' in window ) && fls.fvmobile ) {
    flowplayer.conf.native_fullscreen = true;
  }
  
  if( 'ontouchstart' in window ) {    
    if( fls.android && fls.android.version < 4.4 && ! ( fls.browser.chrome && fls.browser.version > 54 ) ) {
      flowplayer.conf.native_fullscreen = true;
    }
    
    function inIframe() {
      try {
          return window.self !== window.top;
      } catch (e) {
          return true;
      }
    }
    
    if( fls.iOS && ( inIframe() || fls.iOS.version < 7 ) ) {
      flowplayer.conf.native_fullscreen = true;
    }
  }
}




function fv_flowplayer_amazon_s3( hash, time ) {  //  v6
	jQuery('#wpfp_'+hash).bind('error', function (e,api, error) {
			var fv_fp_date = new Date();
			if( error.code == 4 && fv_fp_date.getTime() > (fv_fp_utime + parseInt(time)) ) {
				jQuery(e.target).find('.fp-message').delay(500).queue( function(n) {			
					jQuery(this).html(fv_flowplayer_translations.video_expired); n();
				} );
			}
	} );
}

function fv_flowplayer_browser_chrome_fail( hash, sAttributes, sVideo, bAutobuffer ) {
	jQuery('#wpfp_'+hash).bind('error', function (e,api, error) {
		if( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && error != null && ( error.code == 3 || error.code == 4 || error.code == 5 ) ) {							
			api.unload();
			
			jQuery('#wpfp_'+hash).attr('id','bad_wpfp_'+hash);					
			jQuery('#bad_wpfp_'+hash).after( '<div id="wpfp_'+hash+'" '+sAttributes+' data-engine="flash"></div>' );
			jQuery('#wpfp_'+hash).flowplayer({ playlist: [ [ {mp4: sVideo} ] ] });
      //  what about scripts?
			if( bAutobuffer ) {
				jQuery('#wpfp_'+hash).bind('ready', function(e, api) { api.play(); } );
			} else {
				jQuery('#wpfp_'+hash).flowplayer().play(0);
			}
			jQuery('#bad_wpfp_'+hash).remove();						
		}
	});				
}

function fv_flowplayer_browser_chrome_mp4( hash ) {
	var match = window.navigator.appVersion.match(/Chrome\/(\d+)\./);
	if( match != null ) {
		var chrome_ver = parseInt(match[1], 10);
		if(
			( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && chrome_ver < 28 && navigator.appVersion.indexOf("Win")!=-1 ) || 
			( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && chrome_ver < 27 && navigator.appVersion.indexOf("Linux")!=-1 && navigator.userAgent.toLowerCase().indexOf("android")==-1 )							
		) {
			jQuery('#wpfp_'+hash).attr('data-engine','flash');
		}
	}
}

function fv_flowplayer_browser_ff_m4v( hash ) {
	if( jQuery.browser && jQuery.browser.mozilla && navigator.appVersion.indexOf("Win")!=-1 ) {
		jQuery('#wpfp_'+hash).attr('data-engine','flash');
	}
}

function fv_flowplayer_browser_ie( hash ) {
	if( ( jQuery.browser && jQuery.browser.msie && parseInt(jQuery.browser.version, 10) >= 9) /*|| !!navigator.userAgent.match(/Trident.*rv[ :]*11\./)*/ ) {
		jQuery('#wpfp_'+hash).attr('data-engine','flash');
	}
}

if( (navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) || (navigator.userAgent.toLowerCase().indexOf("android") != -1) ) {  	
  flowplayer(function (api, root) { 
    api.bind("error", function (e,api, error) {
      if( error.code == 10 ) {
        jQuery(e.target).find('.fp-message').html(fv_flowplayer_translations.unsupported_format);
      }
    });
  });
}  	

jQuery(document).ready( function() {
  if( (navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) ) {
    jQuery(window).trigger('load');
  }
  jQuery('.flowplayer').mouseleave( function() {
    jQuery(this).find('.fvp-share-bar').removeClass('visible');
    jQuery(this).find('.embed-code').hide();
  } ); 
} );

jQuery(document).on('click', '.flowplayer .embed-code-toggle', function() {
  var button = jQuery(this);
  var player = button.parents('.flowplayer');
  var api = player.data('flowplayer');
  if( typeof(api.embedCode) == 'function' && player.find('.embed-code textarea').val() == '' ) {
    player.find('.embed-code textarea').val(api.embedCode());  
  }
  
  fv_player_clipboard( player.find('.embed-code textarea').val(), function() {
      fv_player_notice(player,fv_flowplayer_translations.embed_copied,2000);          
    }, function() {
      button.parents('.fvp-share-bar').find('.embed-code').toggle();
      button.parents('.fvp-share-bar').toggleClass('visible');
    });
  
  return false;
} );


function fv_flowplayer_mobile_switch(id) {
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
		jQuery('#wpfp_'+id+' video source').each( function() {
			if( jQuery(this).attr('id') != 'wpfp_'+id+'_mobile' ) {
				fv_fp_mobile = true
				jQuery(this).remove();
			}
		} );
		if( fv_fp_mobile ) {
			jQuery('#wpfp_'+id).after('<p class="fv-flowplayer-mobile-switch">'+fv_flowplayer_translations.mobile_browser_detected_1+' <a href="'+document.URL+'?fv_flowplayer_mobile=no">'+fv_flowplayer_translations.mobile_browser_detected_2+'</a> '+fv_flowplayer_translations.mobile_browser_detected_3+'</p>');
		}
	}
}


var fv_flowplayer_safety_resize_arr = Array();

function fv_flowplayer_safety_resize() {
	var fv_flowplayer_safety_resize_init = false;

	jQuery('.flowplayer').each( function() {
    if( !jQuery(this).is(":visible") || jQuery(this).hasClass('lightboxed') || jQuery(this).hasClass('lightbox-starter') || jQuery(this).hasClass('is-audio') ) return;
    
		if( jQuery(this).width() < 30 || jQuery(this).height() < 20 ) {
			fv_flowplayer_safety_resize_init = true
			var el = jQuery(this);
			while( jQuery(el).width() < 30 || jQuery(el).width() == jQuery(this).width() ) {
        if( jQuery(el).parent().length == 0 ) break; 
				el = jQuery(el).parent();
			}
			
			jQuery(this).width( jQuery(el).width() );
			jQuery(this).height( parseInt(jQuery(this).width() * jQuery(this).attr('data-ratio')) );					
			fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')] = el;                  
		}
	} );
	
	if( fv_flowplayer_safety_resize_init ) {
		jQuery(window).resize(function() {
			jQuery('.flowplayer').each( function() {
        if( jQuery(this).hasClass('lightboxed') || jQuery(this).hasClass('lightbox-starter') ) return;
        
				if( fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')] ) {
					jQuery(this).width( fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')].width() );
					jQuery(this).height( parseInt(jQuery(this).width() * jQuery(this).attr('data-ratio')) );	
				}
			} );  
		} );    
	}
}

if( typeof(flowplayer.conf.safety_resize) != "undefined" && flowplayer.conf.safety_resize ) {
  jQuery(document).ready(function() { setTimeout( function() { fv_flowplayer_safety_resize(); }, 10 ); } );	
}




//  did autoplay?
var fv_player_did_autoplay = false;




function fv_player_videos_parse(args, root) {
  var videos = JSON.parse(args);
  
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
        jQuery(root).after('<p class="fv-flowplayer-mobile-switch">'+fv_flowplayer_translations.mobile_browser_detected_1+' <a href="'+document.URL+'?fv_flowplayer_mobile=no">'+fv_flowplayer_translations.mobile_browser_detected_2+'</a> '+fv_flowplayer_translations.mobile_browser_detected_3+'</p>');
      }
    });
  }
  return videos;
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

function fv_player_preload() {
 
  if( flowplayer.support.touch ) {
    jQuery('.fp-playlist-external.fv-playlist-design-2017').addClass('visible-captions');
  }

  flowplayer( function(api,root) {
    root = jQuery(root);
    
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
    
    // failsafe is Flowplayer is loaded outside of fv_player_load()
    var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
    if( ( !api.conf.playlist || api.conf.playlist.length == 0 ) && playlist.length && playlist.find('a[data-item]').length > 0 ) {  // api.conf.playlist.length necessary for iOS 9 in some setups
      var items = [];      
      playlist.find('a[data-item]').each( function() {
        items.push( fv_player_videos_parse(jQuery(this).attr('data-item'), root) );
      });
      api.conf.playlist = items;
      api.conf.clip = items[0];
    } else if( !api.conf.clip ){
      api.conf.clip = fv_player_videos_parse(jQuery(root).attr('data-item'), root);
    }
    
    //  playlist item click action
    jQuery('a',playlist).click( function(e) {
      e.preventDefault();

      var
        $this = jQuery(this),
        playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']'),
        index = jQuery('a',playlist).index(this);
        $prev = $this.prev('a');

      if ($prev.length && $this.is(':visible') && !$prev.is(':visible')) {
        $prev.click();
        return false;
      }

      if( jQuery( '#' + $this.parent().attr('rel') ).hasClass('dynamic-playlist') ) return;
      
      var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
      
      fv_player_playlist_active(playlist,this);
      
      if( api ){
        if( api.error ) {
          api.pause();
          api.error = api.loading = false;
          root.removeClass('is-error');
          root.find('.fp-message.fp-shown').remove();
        }
        
        if( !api.video || api.video.index == index ) return;
        api.play( index );
      }
      
      var new_splash = $this.find('img').attr('src');
      if( new_splash ) {
        root.find('img.fp-splash').attr('src', new_splash );
      }

      var rect = root[0].getBoundingClientRect();
      if((rect.bottom - 100) < 0){
        jQuery('html, body').animate({
          scrollTop: jQuery(root).offset().top - 100
        }, 300);
      }
    } );
    
    var playlist_external = jQuery('[rel='+root.attr('id')+']');
    var playlist_progress = false;
    
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
      }, 250 );
      
      root.find('.fp-splash').hide();
      root.find('.fv-fp-splash-text').hide();
    } );
    
    api.bind( 'unload', function() {
      jQuery('.fp-playlist-external .now-playing').remove();
      jQuery('.fp-playlist-external a').removeClass('is-active');
      
      root.find('.fp-splash').show();
      root.find('.fv-fp-splash-text').show();
      playlist_progress = false;
    });
    
    api.bind( 'progress', function() {
      if( playlist_progress && api.video.duration ) {
        var progress = 100*api.video.time/api.video.duration;
        playlist_progress.css('width',progress+'%');
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
        items.push( fv_player_videos_parse(jQuery(this).attr('data-item'), root) );
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


if( typeof(fv_flowplayer_browser_ff_m4v_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ff_m4v_array ) {
    fv_flowplayer_browser_ff_m4v( i );
  }
}
if( typeof(fv_flowplayer_browser_chrome_fail_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_chrome_fail_array ) {
    fv_flowplayer_browser_chrome_fail( i, fv_flowplayer_browser_chrome_fail_array[i]['attrs'], fv_flowplayer_browser_chrome_fail_array[i]['mp4'], fv_flowplayer_browser_chrome_fail_array[i]['auto_buffer'] );
  }
}

if( typeof(fv_flowplayer_browser_ie_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ie_array ) {
    fv_flowplayer_browser_ie( i );
  }
}
if( typeof(fv_flowplayer_mobile_switch_array) != "undefined" ) {
  for( var i in fv_flowplayer_mobile_switch_array ) {
    fv_flowplayer_mobile_switch( i );
  }
}




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
  
  if( root.data('fv_loop') ) {
    api.conf.loop = true;
  }
  
  jQuery('.fvfp_admin_error', root).remove();
  
  root.find('.fp-logo, .fp-header').click( function(e) {
    if (e.target !== this) return;
    root.find('.fp-ui').click();
  });
    
  jQuery('.fvp-share-bar .sharing-facebook',root).append('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="#fff"><title>Facebook</title><path d="M11.9 5.2l-2.6 0 0-1.6c0-0.7 0.3-0.7 0.7-0.7 0.3 0 1.6 0 1.6 0l0-2.9 -2.3 0c-2.6 0-3.3 2-3.3 3.3l0 2 -1.6 0 0 2.9 1.6 0c0 3.6 0 7.8 0 7.8l3.3 0c0 0 0-4.2 0-7.8l2.3 0 0.3-2.9Z"/></svg>');
  jQuery('.fvp-share-bar .sharing-twitter',root).append('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="#fff"><title>Twitter</title><path d="M16 3.1c-0.6 0.3-1.2 0.4-1.9 0.5 0.7-0.4 1.2-1 1.4-1.8 -0.6 0.4-1.3 0.6-2.1 0.8 -0.6-0.6-1.4-1-2.4-1 -2 0.1-3.2 1.6-3.2 4 -2.7-0.1-5.1-1.4-6.7-3.4 -0.9 1.4 0.2 3.8 1 4.4 -0.5 0-1-0.1-1.5-0.4l0 0.1c0 1.6 1.1 2.9 2.6 3.2 -0.7 0.2-1.3 0.1-1.5 0.1 0.4 1.3 1.6 2.2 3 2.3 -1.6 1.7-4.6 1.4-4.8 1.3 1.4 0.9 3.2 1.4 5 1.4 6 0 9.3-5 9.3-9.3 0-0.1 0-0.3 0-0.4 0.6-0.4 1.2-1 1.6-1.7Z"/></svg>');
  jQuery('.fvp-share-bar .sharing-email',root).append('<svg xmlns="http://www.w3.org/2000/svg" height="16" viewBox="0 0 16 16" width="16" fill="#fff"><title>Email</title><path d="M8 10c0 0 0 0-1 0L0 6v7c0 1 0 1 1 1h14c1 0 1 0 1-1V6L9 10C9 10 8 10 8 10zM15 2H1C0 2 0 2 0 3v1l8 4 8-4V3C16 2 16 2 15 2z"/></svg>');
    
  jQuery('.fp-header',root).prepend( jQuery('.fvp-share-bar',root) );
  
  if( api.conf.playlist.length ) {
    var prev = jQuery('<a class="fp-icon fv-fp-prevbtn"></a>');
    var next = jQuery('<a class="fp-icon fv-fp-nextbtn"></a>');
    root.find('.fp-controls .fp-playbtn').before(prev).after(next);
    prev.click( function() {
      api.prev();
    });
    next.click( function() {
      api.next();
    });
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
  
  if( !flowplayer.support.fullscreen ) {
    var id = root.attr('id'),
      alternative = !flowplayer.conf.native_fullscreen && flowplayer.conf.mobile_alternative_fullscreen;
    
  	api.bind('fullscreen', function(e,api) {
      jQuery('#wpadminbar, .nc_wrapper').hide();
	  if( alternative ) {
        if( api.video.type == 'video/youtube' ) return;		
        root.before('<span data-fv-placeholder="'+id+'"></span>');
  	    root.appendTo('body');
      }
  	});
    api.bind('fullscreen-exit', function(e,api,video) {
      jQuery('#wpadminbar, .nc_wrapper').show();
      if( alternative ) jQuery('span[data-fv-placeholder='+id+']').replaceWith(root);		
  	});
  }
    
});

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
        var id = (typeof(playlist[item].id) !== 'undefined') ? fv_parse_sharelink(playlist[item].id.toString()) : false;
        if( hash === id && autoplay ){
          console.log('fv_autoplay_exec for '+id,item);
          fv_autoplay_init(root, parseInt(item),time);
          autoplay = false;
          return false;
        }
      }

      for( var item in playlist ) {
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
        }
      }
    });
  }
}

function fv_autoplay_can( api, item ) {
  var video = item ? api.conf.playlist[item] : api.conf.clip;
  
  if( video.sources[0].type == 'video/youtube' && ( flowplayer.support.iOS || flowplayer.support.android ) ) return false;
  
  return flowplayer.support.firstframe;
}

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
    return false;

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
      jQuery('[href=#' + root.attr('id')+ ']').click();
    },0);
  }

  if(index){
    if( fv_autoplay_can(api,parseInt(index)) ) {
      api.play(parseInt(index));
      api.one('ready', function() {
        fv_autoplay_exec_in_progress = false;
        if( fTime ) api.seek(fTime)
      } );    
    } else if( flowplayer.support.inlineVideo ) {
      api.one( api.playing ? 'progress' : 'ready', function (e,api) {
        api.play(parseInt(item));
        api.one('ready', function() {
          fv_autoplay_exec_in_progress = false;
          if( fTime ) api.seek(fTime)
        } );              
      });
      
      fv_player_playlist_active( false, jQuery('[rel='+root.attr('id')+'] a').eq(index) );
      
      root.css('background-image', jQuery('[rel='+root.attr('id')+'] a').eq(index).find('span').css('background-image') );
      
      fv_player_notice( root, fv_flowplayer_translations[11], 'progress' );
    }
  }else{
    if( fv_autoplay_can(api) ) {
      api.load();
    } else {
      fv_player_notice( root, fv_flowplayer_translations[11], 'progress' );
    }
    api.one('ready', function() {
      fv_autoplay_exec_in_progress = false;
      if( fTime ) {
        var do_seek = setInterval( function() {
          if( api.loading ) return;
          api.seek(fTime)
          clearInterval(do_seek);
        }, 10 );
      }
    } );    
  }
  
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