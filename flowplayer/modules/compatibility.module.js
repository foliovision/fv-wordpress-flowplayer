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

flowplayer(function(api, root) {

  root = jQuery(root);

  api.bind('ready',function() {
    /*
    * Gravity Forms Partial Entries fix - the whole player is cloned
    * if it's placed in the form causing it to play again in the background.
    * Since the video is already playing by now we don't care about removing the attribute.
    */
    setTimeout( function() {
      var video = jQuery('video',root);
      if( video.length > 0 ) {
        video.prop( "autoplay", false ); //  removing autoplay attribute fixes the issue
      }
    }, 100 ); //  by default the heartbeat JS event triggering this happens every 30 seconds, we just add a bit of delay to be sure

    /*
    * Avoiding Twenty Twenty video resize function
    * https://core.trac.wordpress.org/ticket/49030
    */
    root.find('video.fp-engine').addClass('intrinsic-ignore');
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
if( flowplayer.support.browser && flowplayer.support.browser.msie && parseInt(flowplayer.support.browser.version, 10) < 9 ) {
  jQuery('.flowplayer').each( function() {
    jQuery(this).css('width', jQuery(this).css('max-width'));
    jQuery(this).css('height', jQuery(this).css('max-height'));
  } );
}

if( location.href.match(/elementor-preview=/) ) {
  console.log('FV Player: Elementor editor is active');
  setInterval( fv_player_load, 1000 );
  
} else if( location.href.match(/brizy-edit-iframe/) ) {
  console.log('FV Player: Brizy editor is active');
  setInterval( fv_player_load, 1000 );
}


/*
 *  Disable HTML5 Autoplay
 */
if( window.DELEGATE_NAMES ) {
  flowplayer( function(api,root) {
    fv_player_notice(root,fv_flowplayer_translations.chrome_extension_disable_html5_autoplay);
  });
}