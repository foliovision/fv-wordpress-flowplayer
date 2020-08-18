/*
Deprecated
*/
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
			jQuery('#wpfp_'+id).after('<p class="fv-flowplayer-mobile-switch">'+fv_flowplayer_translations.mobile_browser_detected_1+' <a href="'+document.URL+'?fv_flowplayer_mobile=no">'+fv_flowplayer_translations.mobile_browser_detected_2+'</a>.</p>');
		}
	}
}

if( typeof(fv_flowplayer_mobile_switch_array) != "undefined" ) {
  for( var i in fv_flowplayer_mobile_switch_array ) {
		if( !fv_flowplayer_mobile_switch_array.hasOwnProperty(i) ) continue;

    fv_flowplayer_mobile_switch( i );
  }
}