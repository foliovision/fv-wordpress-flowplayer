//  Magnific Popup suppport
jQuery(document).on('mfpClose', function() {
  if( typeof(jQuery('.flowplayer').data('flowplayer')) != "undefined" ) jQuery('.flowplayer').data('flowplayer').unload();
} );

/*
 *  Visual Composer tabs support
 */
jQuery(document).on('click','.vc_tta-tab a', function() {
  var api = jQuery('.flowplayer.is-playing').data('flowplayer');
  if( api ) api.pause();
});

/*
 *  Gravity Forms Partial Entries fix - the whole player is cloned if it's placed in the form, causing it to play again in the background
 */
flowplayer(function(api, root) {

  api.bind('ready',function() {
    setTimeout( function() {
      var video = jQuery('video',root);
      if( video.length > 0 ) {
        video.removeAttr('autoplay'); //  removing autoplay attribute fixes the issue
      }
    }, 100 ); //  by default the heartbeat JS event triggering this happens every 30 seconds, we just add a bit of delay to be sure
  });

});

/*
 *  BlackBerry 10 hotfix
 */
jQuery('.flowplayer').on('ready', function(e,api) { //  v6
  if( /BB10/.test(navigator.userAgent) ){
    api.fullscreen();
  }
});

//  v6
// if( /ipad/.test(navigator.userAgent.toLowerCase()) && /os 8/.test(navigator.userAgent.toLowerCase()) ){
//   flowplayer(function (api, root) {
//     api.bind("resume", function (e,api,data) {
//       setTimeout( function() {      
//         if( api.loading ) jQuery(e.currentTarget).children('video')[0].play();
//       }, 1000 );
//     });  
//   });
// }

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

/*
 *  IE11 - hiding animations
 */
var isIE11 = !!navigator.userAgent.match(/Trident.*rv[ :]*11\./);
if( isIE11 ) {
  jQuery(document).ready( function() {
    jQuery('.fp-waiting').hide();
  } );
  
  flowplayer( function(api,root) {
    api.bind("load", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').show();
    } ).bind("beforeseek", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').show();
    } ).bind("progress", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').hide();
    } ).bind("seek", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').hide();
    } ).bind("fullscreen", function (e) {
      jQuery('#wpadminbar').hide();
    } ).bind("fullscreen-exit", function (e) {
      jQuery('#wpadminbar').show();
    } );       
  } );
}

/*
 *  IE < 9 - disabling responsiveness
 */
if( jQuery.browser && jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9 ) {
  jQuery('.flowplayer').each( function() {
    jQuery(this).css('width', jQuery(this).css('max-width'));
    jQuery(this).css('height', jQuery(this).css('max-height'));
  } );
}

/*
MQQBrowser is Tencent's Cross Platform WebView Framework for WeChat
It seems it doesn't support HTML5 fullscreen when on Android
*/
if( navigator.userAgent.match(/MQQBrowser/) ) {
  flowplayer(function(player, root) {

    /*
    this is a copy of the original Flowplayer function with some changes
    - it doesn't rely on flowplayer.support.fullscreen as Flowplayer only
    checks that as the code gets loaded, not giving us a chance to change
    it later
    */
    var win = window,
      scrollY,
      scrollX;

    player.isFullscreen = false;

    player.fullscreen = function(flag) {

      if (player.disabled) return;

      if (flag === undefined) flag = !player.isFullscreen;

      if (flag) {
        scrollY = win.scrollY;
        scrollX = win.scrollX;
      }

      player.trigger(flag ? "fullscreen" : "fullscreen-exit", [player]);

      return player;
    };

    // here we do everything that Flowplayer would do for a device without fullscreen support
    player.on("fullscreen", function() {
      flowplayer.common.css(root, 'position', 'fixed');

    }).on("fullscreen-exit", function() {
        var oldOpacity;
        if (player.engine === "html5") {
          oldOpacity = root.css('opacity') || '';
          flowplayer.common.css(root, 'opacity', 0);
        }
        flowplayer.common.css(root, 'position', '');

        if ( player.engine === "html5") setTimeout(function() { root.css('opacity', oldOpacity); });

        win.scrollTo(scrollX, scrollY);
    })
  });
}

/*
WPMobile app uses a weak user agent string - "WPMobile.App - Android" or "WPMobile.App - iOS"
So here we detect the capabilities "properly"
*/
if( navigator.userAgent.match(/WPMobile.App/) ) {
  flowplayer.support = {
    "browser": false,
    "iOS": false,
    "android": false,
    "subtitles": true,
    "fullscreen": false, // let's be careful
    "inlineBlock": true,
    "touch": true,
    "dataload": false,
    "flex": true,
    "svg": true,
    "zeropreload": true,
    "volume": true,
    "cachedVideoTag": false,
    "firstframe": true,
    "inlineVideo": true,
    "hlsDuration": true,
    "seekable": true,
    "preloadMetadata": false,
    "autoplay": true,
    "video": true,
    "animation": true,
    "fvmobile": true
  }

  if( navigator.userAgent.match(/iOS/) ) {
    flowplayer.support.browser = {
      "safari": true,
      "version": "12.0"
    }
    flowplayer.support.iOS = {
      "iPhone": true,
      "iPad": false,
      "version": 12,
      "chrome": false
    }
    flowplayer.support.volume = false;

  } else if( navigator.userAgent.match(/Android/) ) {
    flowplayer.support.browser = {
      "chrome": true,
      "version": "80.0.3987.100"
    }
    flowplayer.support.android = {
      "firefox": false,
      "opera": false,
      "samsung": false,
      "version": 8
    }
    flowplayer.support.cachedVideoTag = true;
    flowplayer.support.dataload = true;
    flowplayer.support.hlsDuration = false;
    flowplayer.support.preloadMetadata = true;
    flowplayer.support.zeropreload = false;
  }

}