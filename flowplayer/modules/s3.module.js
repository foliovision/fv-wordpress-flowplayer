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