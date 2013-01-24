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
add_action('wp_footer','flowplayer_display_scripts');
//	Addition for 0.9.15                 
add_action('widget_text','flowplayer_content');
add_action('wp_enqueue_scripts', 'flowplayer_jquery');
/**
 * END WP Hooks
 */
 
$GLOBALS['scripts'] = array();

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
	$content_matches = array();
	preg_match_all('/\[flowplayer\ [^\]]+\]/i', $content, $content_matches);
	// process all found tags
	foreach ($content_matches[0] as $tag) {
		$ntag = str_replace("\'",'&#039;',$tag);
		//	search for URL
		preg_match("/src='([^']*?)'/i",$ntag,$tmp);
		if( $tmp[1] == NULL ) {
			preg_match_all("/src=([^,\s\]]*)/i",$ntag,$tmp);
			$media = $tmp[1][0];
		}
		else
      $media = $tmp[1];
		//strip the additional /videos/ from the beginning if present	
		preg_match('/(.*)\/videos\/(.*)/',$media,$matches);
/*		if ($matches[0] == NULL)
      $media = $matches[2];
		else
		  $media = $matches[0];*/
   		if ($matches[0] == NULL)
   		   $media = $media;
   		elseif ($matches[1] == NULL){
          $media = $matches[2];//$tmp[1][0];
      }
   		else{
			   $media = $matches[2];
		  }
//    var_dump($media);
		//	width and heigth
		preg_match("/width=(\d*)/i",$ntag,$width);
		preg_match("/height=(\d*)/i",$ntag,$height);
		if( $width[1] != NULL)
			$arguments['width'] = $width[1];
		if( $height[1] != NULL)
			$arguments['height'] = $height[1];
		preg_match("/controlbar=\'?([a-zA-Z]*)\'?/i",$ntag,$controlbar);
		if( $controlbar[1] != NULL)
			$arguments['controlbar'] = $controlbar[1];
		preg_match("/redirect=\'?([^\s\]]*)\'?/i",$ntag,$redirect);
		if( $redirect[1] != NULL)
			$arguments['redirect'] = $redirect[1];
    //	search for autoplay
		preg_match("/[\s]+autoplay([\s]|])+/i",$ntag,$tmp);
		if (isset($tmp[0])){
       $arguments['autoplay'] = true;
      }
		else
		{
         preg_match("/autoplay='([A-Za-z]*)'/i",$ntag,$tmp);
		   if ( $tmp[1] == NULL )
		       preg_match("/autoplay=([A-Za-z]*)/i",$ntag,$tmp);
		   if (isset($tmp[1])) $arguments['autoplay'] = $tmp[1];
		}
		//	search for popup in quotes
		preg_match("/popup='([^']*?)'/i",$ntag,$tmp);
		$arguments['popup'] = $tmp[1];
		//	search for splash image
		preg_match("/splash='([^']*?)'/i",$ntag,$tmp);   //quotes version
   	if( $tmp[1] == NULL ) {
			preg_match_all("/splash=([^,\s\]]*)/i",$ntag,$tmp);  //non quotes version
			preg_match('/(.*)\/videos\/(.*)/i',$tmp[1][0],$matches);
   		if ($matches[0] == NULL)
   		   $arguments['splash'] = $tmp[1][0];
   		elseif ($matches[1] == NULL){
          $arguments['splash'] = $matches[2];//$tmp[1][0];
      }
   		else{
			   $arguments['splash'] = $matches[2];
		  }
    }
		else{
		    preg_match('/(.*)\/videos\/(.*)/',$tmp[1],$matches);
		    if ($matches[0] == NULL)
          $arguments['splash'] = $tmp[1];
        elseif ($matches[1] == NULL)
          $arguments['splash'] = $matches[2];
   		  else
			   $arguments['splash'] = $matches[2];//$tmp[1];
		}
		if (trim($media) != '') {
			// build new player
			$fp = new flowplayer_frontend();
      $new_player = $fp->build_min_player($media,$arguments);
			$content = str_replace($tag, $new_player['html'],$content);
			$GLOBALS['scripts'][] = $new_player['script'];
		}
	}
	return $content;
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