<?php

/**
 * Needed includes
 */
include_once(dirname( __FILE__ ) . '/../models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/../models/flowplayer-frontend.php');

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
 * Replaces the flowplayer tags in post content by players and fills the $GLOBALS['scripts'] array.
 * @param string Content to be parsed
 * @return string Modified content string
 */
function flowplayer_content( $content ) {
	global $fp;

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
      $new_player = $fp->build_min_player($media,$arguments);
			$content = str_replace($tag, $new_player['html'],$content);
			if (!empty($new_player['script'])) {
        $GLOBALS['scripts'][] = $new_player['script'];
      }
		}
	}
	return $content;
}

/**
 * Figure out if we need to include MediaElement.js
 */
function flowplayer_prepare_scripts() {
	global $fp;
	global $fv_wp_flowplayer_ver;

	global $wp_scripts;
	if( $fp->load_mediaelement && !wp_script_is('wp-mediaelement') ) {
		wp_enqueue_script( 'flowplayer-mediaelement', plugins_url( '/fv-wordpress-flowplayer/mediaelement/mediaelement-and-player.min.js' ), array('jquery'), $fv_wp_flowplayer_ver, true );
	}
}

/**
 * Prints flowplayer javascript content to the bottom of the page.
 */
function flowplayer_display_scripts() {
	if (!empty($GLOBALS['scripts'])) {         
		echo "\n<script type=\"text/javascript\">\n\n\n";
		foreach ($GLOBALS['scripts'] as $scr) {
			echo $scr;
		}   
		
		if( current_user_can('manage_options') ) {
			?>
			function fv_wp_flowplayer_support_mail( hash, button ) {			
				var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
				jQuery('#wpfp_spin_'+hash).show();
				jQuery(button).attr("disabled", "disabled");
				jQuery.post(
					ajaxurl,
					{
						action: 'fv_wp_flowplayer_support_mail',
						comment: jQuery('#wpfp_support_'+hash).val(),
						notice: jQuery('#wpfp_notice_'+hash+' .mail-content-notice').html(),
						details: jQuery('#wpfp_notice_'+hash+' .mail-content-details').html()						
					},
					function( response ) {
						jQuery('#wpfp_spin_'+hash).hide();					
						jQuery(button).removeAttr("disabled");
						jQuery(button).after(' Message sent');
					}	
				); 
			}
			
			
			function fv_wp_flowplayer_show_notice( id, link ) {

				jQuery('#fv_wp_fp_notice_'+id).toggle();
				jQuery(link).parent().parent().toggleClass("fv-wp-flowplayer-notice");
				
				/*var pos = jQuery(link).position();
				console.log(pos);
				
				jQuery(link).parent().parent().css('position','absolute');
				jQuery(link).parent().parent().css('top',pos.top);
				jQuery(link).parent().parent().css('left',pos.left);			*/	
			}
			<?php
		}
		
		echo "\n\n\n</script>\n";
	}
}

/**
 * This is the template tag. Use the standard Flowplayer shortcodes
 */
function flowplayer($shortcode) {
	echo apply_filters('the_content',$shortcode);
}

?>