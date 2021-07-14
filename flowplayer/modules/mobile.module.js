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


/*
 * Mobile touch screen double-tap on the left/right side of video to seek -10/+10 seconds
 */
flowplayer( function(api,root) {
	if( !flowplayer.support.touch ) return;

	jQuery.fn.fv_single_double_click = function(single_click_callback, double_click_callback, timeout) {
		return this.each(function(){
			var clicks = 0, self = this;
			jQuery(this).on( 'click', function(event){
				clicks++;
				if (clicks == 1) {
					setTimeout(function(){
						if(clicks == 1) {
							single_click_callback.call(self, event);
						} else {
							double_click_callback.call(self, event);
						}
						clicks = 0;
					}, timeout || 300);
				}
			});
		});
	}

	root = jQuery(root);

	var left = jQuery('<div class="fv-fp-tap-left"><span>-10s</span></div>'),
		right = jQuery('<div class="fv-fp-tap-right"><span>+10s</span></div>');

	left.fv_single_double_click(function () {
		api.toggle();
	}, function (e) {
		maybe_seek( e, left, api.ready ? api.video.time - 10 : false );
	});

	right.fv_single_double_click(function () {
		api.toggle();
	}, function (e) {
		maybe_seek( e, right, api.ready ? api.video.time + 10 : false );
	});

	root.find('.fp-ui').append(left).append(right);

	function maybe_seek( e, el, time ) {
		if( api.ready ) {
			api.seek( time );
			animation(el);

		} else {
			api.toggle();
		};

		e.preventDefault();
	}

	function animation( el ) {
		el.addClass('is-active');
		setTimeout( function() {
			el.removeClass('is-active');
		}, 500 );
	}

});