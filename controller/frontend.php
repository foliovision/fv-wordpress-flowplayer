<?php

include_once(dirname( __FILE__ ) . '/../models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/../models/flowplayer-frontend.php');

$fv_fp = new flowplayer_frontend(); 

/**
 * WP Hooks 
 */
//add_action('the_content', 'flowplayer_content_remove_commas');
add_action('wp_head', 'flowplayer_head');
add_action('wp_footer','flowplayer_prepare_scripts',9);
add_action('wp_footer','flowplayer_display_scripts',100);
//	Addition for 0.9.15                 
add_action('widget_text','flowplayer_content');
add_action('wp_enqueue_scripts', 'flowplayer_jquery');
/**
 * END WP Hooks
 */
 

function flowplayer_content_remove_commas($content) {
  preg_match('/.*popup=\'(.*?)\'.*/', $content, $matches);
  $content_new = preg_replace('/\,/', '',$content);
  if (isset($matches[1]))
  $content_new = preg_replace('/popup=\'(.*?)\'/', 'popup=\''.$matches[1].'\'',$content_new);
  return $content_new;
}

/**
 * Replaces the flowplayer tags in post content by players and fills the $GLOBALS['fv_fp_scripts'] array.
 * @param string Content to be parsed
 * @return string Modified content string
 */
function flowplayer_content( $content ) {
	global $fv_fp;

	$content_matches = array();
	preg_match_all('/\[(flowplayer|fvplayer)\ [^\]]+\]/i', $content, $content_matches);
  
	// process all found tags
	foreach ($content_matches[0] as $tag) {
		$ntag = str_replace("\'",'&#039;',$tag);
		//search for URL
		preg_match("/src='([^']*?)'/i",$ntag,$tmp);
		if( $tmp[1] == NULL ) {
			preg_match_all("/src=([^,\s\]]*)/i",$ntag,$tmp);
			$media = $tmp[1][0];
		}
		else
      $media = $tmp[1]; 
		
    //strip the additional /videos/ from the beginning if present	
		preg_match('/(.*)\/videos\/(.*)/',$media,$matches);
 		if ($matches[0] == NULL)
      $media = $media;
 		else if ($matches[1] == NULL) {
      $media = $matches[2];
    }
 		else {
		   $media = $matches[2];
	  }
    
    unset($arguments['src']);
    unset($arguments['src1']);
    unset($arguments['src2']);        
    unset($arguments['width']);
    unset($arguments['height']);
    unset($arguments['autoplay']);
    unset($arguments['splash']);
    unset($arguments['splashend']);
    unset($arguments['popup']);
    unset($arguments['controlbar']);
    unset($arguments['redirect']);
    unset($arguments['loop']);
		
    //width and heigth
		preg_match("/width=(\d*)/i",$ntag,$width);
		preg_match("/height=(\d*)/i",$ntag,$height);
		if( $width[1] != NULL)
			$arguments['width'] = $width[1];
		if( $height[1] != NULL)
			$arguments['height'] = $height[1];
      
    //search for redirect
    preg_match("/redirect='([^']*?)'/i",$ntag,$tmp);
		if ($tmp[1])
      $arguments['redirect'] = $tmp[1];
    
    //search for autoplay
		preg_match("/[\s]+autoplay([\s]|])+/i",$ntag,$tmp);
		if (isset($tmp[0])){
      $arguments['autoplay'] = true;
    }
		else {
      preg_match("/autoplay='([A-Za-z]*)'/i",$ntag,$tmp);
		  if ( $tmp[1] == NULL )
		    preg_match("/autoplay=([A-Za-z]*)/i",$ntag,$tmp);
		  if (isset($tmp[1]))
        $arguments['autoplay'] = $tmp[1];
		}
    
    //search for popup in quotes
		preg_match("/popup='([^']*?)'/i",$ntag,$tmp);
		if ($tmp[1])
      $arguments['popup'] = $tmp[1];
    
    //search for loop
		preg_match("/[\s]+loop([\s]|])+/i",$ntag,$tmp);
		if (isset($tmp[0])){
      $arguments['loop'] = true;
    }
		else {
      preg_match("/loop='([A-Za-z]*)'/i",$ntag,$tmp);
		  if ( $tmp[1] == NULL )
		    preg_match("/loop=([A-Za-z]*)/i",$ntag,$tmp);
		  if (isset($tmp[1]))
        $arguments['loop'] = $tmp[1];
		}
    
		//	search for splash image
		preg_match("/splash='([^']*?)'/i",$ntag,$tmp);   //quotes version
   	if( $tmp[1] == NULL ) {
			preg_match_all("/splash=([^,\s\]]*)/i",$ntag,$tmp);  //non quotes version
			preg_match('/(.*)\/videos\/(.*)/i',$tmp[1][0],$matches);
   		if ($matches[0] == NULL)
        $arguments['splash'] = $tmp[1][0];
   		else if ($matches[1] == NULL) {
        $arguments['splash'] = $matches[2];//$tmp[1][0];
      }
   		else {
        $arguments['splash'] = $matches[2];
		  }
    }
		else {
      preg_match('/(.*)\/videos\/(.*)/',$tmp[1],$matches);
      if ($matches[0] == NULL)
        $arguments['splash'] = $tmp[1];
      elseif ($matches[1] == NULL)
        $arguments['splash'] = $matches[2];
  	  else
        $arguments['splash'] = $matches[2];//$tmp[1];
		}
    
    //	search for src1
		preg_match("/src1='([^']*?)'/i",$ntag,$tmp);   //quotes version
   	if( $tmp[1] == NULL ) {
			preg_match_all("/src1=([^,\s\]]*)/i",$ntag,$tmp);  //non quotes version
			preg_match('/(.*)\/videos\/(.*)/i',$tmp[1][0],$matches);
   		if ($matches[0] == NULL)
        $arguments['src1'] = $tmp[1][0];
   		else if ($matches[1] == NULL) {
        $arguments['src1'] = $matches[2];//$tmp[1][0];
      }
   		else {
        $arguments['src1'] = $matches[2];
		  }
    }
		else {
      preg_match('/(.*)\/videos\/(.*)/',$tmp[1],$matches);
      if ($matches[0] == NULL)
        $arguments['src1'] = $tmp[1];
      elseif ($matches[1] == NULL)
        $arguments['src1'] = $matches[2];
  	  else
        $arguments['src1'] = $matches[2];//$tmp[1];
		}
    
    //	search for src1
		preg_match("/src2='([^']*?)'/i",$ntag,$tmp);   //quotes version
   	if( $tmp[1] == NULL ) {
			preg_match_all("/src2=([^,\s\]]*)/i",$ntag,$tmp);  //non quotes version
			preg_match('/(.*)\/videos\/(.*)/i',$tmp[1][0],$matches);
   		if ($matches[0] == NULL)
        $arguments['src2'] = $tmp[1][0];
   		else if ($matches[1] == NULL) {
        $arguments['src2'] = $matches[2];//$tmp[1][0];
      }
   		else {
        $arguments['src2'] = $matches[2];
		  }
    }
		else {
      preg_match('/(.*)\/videos\/(.*)/',$tmp[1],$matches);
      if ($matches[0] == NULL)
        $arguments['src2'] = $tmp[1];
      elseif ($matches[1] == NULL)
        $arguments['src2'] = $matches[2];
  	  else
        $arguments['src2'] = $matches[2];//$tmp[1];
		}
    
    //search for splashend
		preg_match("/[\s]+splashend([\s]|])+/i",$ntag,$tmp);
		if (isset($tmp[0])){
      $arguments['splashend'] = true;
    }
		else {
      preg_match("/splashend='([A-Za-z]*)'/i",$ntag,$tmp);
		  if ( $tmp[1] == NULL )
		    preg_match("/splashend=([A-Za-z]*)/i",$ntag,$tmp);
		  if (isset($tmp[1]))
        $arguments['splashend'] = $tmp[1];
		}
    
    //search for controlbar
		preg_match("/[\s]+controlbar([\s]|])+/i",$ntag,$tmp);
		if (isset($tmp[0])){
      $arguments['controlbar'] = true;
    }
		else {
      preg_match("/controlbar='([A-Za-z]*)'/i",$ntag,$tmp);
		  if ( $tmp[1] == NULL )
		    preg_match("/controlbar=([A-Za-z]*)/i",$ntag,$tmp);
		  if (isset($tmp[1]))
        $arguments['controlbar'] = $tmp[1];
		}
    
		if (trim($media) != '') {
			// build new player
      $new_player = $fv_fp->build_min_player($media,$arguments);
			$content = str_replace($tag, $new_player['html'],$content);
			if (!empty($new_player['script'])) {
        $GLOBALS['fv_fp_scripts'][] = $new_player['script'];
      }
		}
	}
	return $content;
}

/**
 * Figure out if we need to include MediaElement.js
 */
function flowplayer_prepare_scripts() {
	global $fv_fp;
	global $fv_wp_flowplayer_ver;

	global $wp_scripts;
	if( $fv_fp->load_mediaelement && !wp_script_is('wp-mediaelement') ) {
		wp_enqueue_script( 'flowplayer-mediaelement', plugins_url( '/fv-wordpress-flowplayer/mediaelement/mediaelement-and-player.min.js' ), array('jquery'), $fv_wp_flowplayer_ver, true );
	}
}

/**
 * Prints flowplayer javascript content to the bottom of the page.
 */
function flowplayer_display_scripts() {
	global $fv_fp;
	if (!empty($GLOBALS['fv_fp_scripts'])) {
		$mobile_switch = false;	
		foreach ($GLOBALS['fv_fp_scripts'] as $scr) {
			if( stripos($scr, 'fv_flowplayer_mobile_switch') !== false ) {
				$mobile_switch = true;
			}
		}  
		
		echo "\n<script type=\"text/javascript\">\n";
		
		if( $mobile_switch ) {
			?>
				function fv_flowplayer_mobile_switch(id) {
					var regex = new RegExp("[\\?&]fv_flowplayer_mobile=([^&#]*)");
					var results = regex.exec(location.search);	
					if(
						(
							(results != null && results[1] == 'yes') ||
							(jQuery(window).width()<=320 || jQuery(window).height()<=480)
						)
						&&
						(results == null || results[1] != 'no')
					) {
						var fv_fp_mobile = false;
						jQuery('#'+id+' video source').each( function() {
							if( jQuery(this).attr('id') != id+'_mobile' ) {
								fv_fp_mobile = true
								jQuery(this).remove();
							}
						} );
						if( fv_fp_mobile ) {
							jQuery('#'+id).after('<p>Mobile browser detected, serving low bandwidth video. <a href="'+document.URL+'?fv_flowplayer_mobile=no">Click here</a> for full quality.</p>');
						}
					}
				}
				//alert("Width: "+jQuery(window).width()+"\nHeight: "+jQuery(window).height() );
			<?php
		}
		
		foreach ($GLOBALS['fv_fp_scripts'] as $scr) {
			echo $scr;
			if( stripos($scr, 'fv_flowplayer_mobile_switch') !== false ) {
				$mobile_switch = true;
			}
		}  
		
		if( current_user_can('manage_options') ) {
			?>
			function fv_wp_flowplayer_support_mail( hash, button ) {			
				jQuery('.fv_flowplayer_submit_error').remove();
			
				var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
				
				var comment_text = jQuery('#wpfp_support_'+hash).val();
				var comment_words = comment_text.split(/\s/);
				if( comment_words.length == 0 || comment_text.match(/Enter your comment/) ) {
					jQuery('#wpfp_support_'+hash).before('<p class="fv_flowplayer_submit_error" style="display:none; "><strong>Please tell us what is wrong</strong>:</p>');
					jQuery('.fv_flowplayer_submit_error').fadeIn();
					return false;
				}

				if( comment_words.length < 7 ) {
					jQuery('#wpfp_support_'+hash).before('<p class="fv_flowplayer_submit_error" style="display:none; "><strong>Please give us more information (a full sentence) so we can help you better</strong>:</p>');
					jQuery('.fv_flowplayer_submit_error').fadeIn();					
					return false;
				}
				
				jQuery('#wpfp_spin_'+hash).show();
				jQuery(button).attr("disabled", "disabled");
							
				jQuery.post(
					ajaxurl,
					{
						action: 'fv_wp_flowplayer_support_mail',
						comment: comment_text,
						notice: jQuery('#wpfp_notice_'+hash+' .mail-content-notice').html(),
						details: jQuery('#wpfp_notice_'+hash+' .mail-content-details').html()						
					},
					function( response ) {
						jQuery('#wpfp_spin_'+hash).hide();					
						jQuery(button).removeAttr("disabled");
						jQuery(button).after(' Message sent');
						setTimeout( function() { fv_wp_flowplayer_show_notice(hash) }, 1500 );
					}	
				);
			}
			
			
			function fv_wp_flowplayer_show_notice( id, link ) {
				if( id == null && link == null ) {
					var api = flowplayer(), currentPos;
					if( typeof api != "undefined" ) {
						api.disable(false);
					}
					jQuery('.fv-wp-flowplayer-notice .fv_wp_fp_notice_content').toggle();
					jQuery('.fv-wp-flowplayer-notice').toggleClass("fv-wp-flowplayer-notice");					
				} else {			
					jQuery('#fv_wp_fp_notice_'+id).toggle();
	
					var api = flowplayer(), currentPos;
					if( jQuery('#fv_wp_fp_notice_'+id).parent().hasClass("fv-wp-flowplayer-notice") ) {
						api.disable(false);
					} else {
						api.disable(true);
					}
					
					jQuery('#fv_wp_fp_notice_'+id).parent().toggleClass("fv-wp-flowplayer-notice");
				}
			}					
			
			jQuery(document).keyup(function(e) {
				if (e.keyCode == 27) {
					fv_wp_flowplayer_show_notice();
				}   // esc
			});
			
			jQuery(document).click( function(event) {
				if(					
					jQuery(event.target).parents('.fv-wp-flowplayer-notice').length == 0 &&
					jQuery(event.target).parents('.fv-wp-flowplayer-notice-small').length == 0				
				) {
					fv_wp_flowplayer_show_notice();	
				}
			}	);
			<?php
		}
    
    ?>
    
  	jQuery(document).ready( function() {
  		if( (navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) ) {
  			jQuery(window).trigger('load');
  		}
  	} );	
  	
		if( (navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) || (navigator.userAgent.toLowerCase().indexOf("android") != -1) ) {  	
			flowplayer(function (api, root) { 
				api.bind("error", function (e,api, error) {
					if( error.code == 10 ) {
						jQuery(e.target).find('.fp-message').html('<h2>Unsupported video format.<br />Please use a Flash compatible device.</h2>');
					}
				});
			});
		}  	

		<?php if( $fv_fp->conf['fixed_size'] == 'false' ) : ?>
		
		var fv_flowplayer_safety_resize_arr = Array();
		
		function fv_flowplayer_safety_resize() {
			var fv_flowplayer_safety_resize_init = false;
		
			jQuery('.flowplayer').each( function() {
				if( jQuery(this).width() < 30 || jQuery(this).height() < 20 ) {
					fv_flowplayer_safety_resize_init = true
					var el = jQuery(this);
					while( jQuery(el).width() < 30 || jQuery(el).width() == jQuery(this).width() ) {        
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
						if( fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')] ) {
							jQuery(this).width( fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')].width() );
							jQuery(this).height( parseInt(jQuery(this).width() * jQuery(this).attr('data-ratio')) );	
						}
					} );  
				} );    
			}
		}
		
		jQuery(document).ready(function() { fv_flowplayer_safety_resize(); } );		
		
		<?php endif; ?>
		
		var fv_fp_date = new Date();
		var fv_fp_utime = fv_fp_date.getTime();		
    <?php    		
    echo apply_filters( 'fv_flowplayer_scripts_global', '' );
		echo "\n</script>\n";
	}
	
	if( is_user_logged_in() || isset($_GET['fv_wp_flowplayer_check_template']) ) {
		echo "\n<!--fv-flowplayer-footer-->\n\n";
	}
}

/**
 * This is the template tag. Use the standard Flowplayer shortcodes
 */
function flowplayer($shortcode) {
	echo apply_filters('the_content',$shortcode);
}


/*
Make sure our div won't be wrapped in any P tag.
*/
function fv_flowplayer_the_content( $c ) {
	$c = preg_replace( '!<p[^>]*?>(\[(?:fvplayer|flowplayer).*?\])</p>!', "\n".'$1'."\n", $c );
	return $c;
}
add_filter( 'the_content', 'fv_flowplayer_the_content', 0 );

?>