function fv_flowplayer_browser_ff_m4v( hash ) {
	if( flowplayer.support.browser && flowplayer.support.browser.mozilla && navigator.appVersion.indexOf("Win")!=-1 ) {
		jQuery('#wpfp_'+hash).attr('data-engine','flash');
	}
}

if( typeof(fv_flowplayer_browser_ff_m4v_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ff_m4v_array ) {
		if( !fv_flowplayer_browser_ff_m4v_array.hasOwnProperty(i) ) continue;

    fv_flowplayer_browser_ff_m4v( i );
  }
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

if( typeof(fv_flowplayer_browser_chrome_fail_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_chrome_fail_array ) {
		if( !fv_flowplayer_browser_chrome_fail_array.hasOwnProperty(i) ) continue;

    fv_flowplayer_browser_chrome_fail( i, fv_flowplayer_browser_chrome_fail_array[i]['attrs'], fv_flowplayer_browser_chrome_fail_array[i]['mp4'], fv_flowplayer_browser_chrome_fail_array[i]['auto_buffer'] );
  }
}

function fv_flowplayer_browser_ie( hash ) {
	if( ( flowplayer.support.browser && flowplayer.support.browser.msie && parseInt(flowplayer.support.browser.version, 10) >= 9) || !!navigator.userAgent.match(/Trident.*rv[ :]*11\./) ) {
		jQuery('#wpfp_'+hash).attr('data-engine','flash');
	}
}

if( typeof(fv_flowplayer_browser_ie_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ie_array ) {
		if( !fv_flowplayer_browser_ie_array.hasOwnProperty(i) ) continue;

    fv_flowplayer_browser_ie( i );
  }
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
  jQuery('.flowplayer').on("mouseleave", function() {
    jQuery(this).find('.fvp-share-bar').removeClass('visible');
    jQuery(this).find('.embed-code').hide();
  } ); 
} );