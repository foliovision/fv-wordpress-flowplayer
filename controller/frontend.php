<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 

include_once(dirname( __FILE__ ) . '/../models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/../models/flowplayer-frontend.php');

$fv_fp = new flowplayer_frontend(); 

//add_action('the_content', 'flowplayer_content_remove_commas');
add_action('wp_head', 'flowplayer_head');
add_action('wp_footer','flowplayer_prepare_scripts',9);
add_action('wp_footer','flowplayer_display_scripts',100);          
add_action('widget_text','flowplayer_content');
add_action('wp_enqueue_scripts', 'flowplayer_jquery');

 

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
        $GLOBALS['fv_fp_scripts'] = $new_player['script'];
      }
		}
	}
	return $content;
}

/**
 * Figure out if we need to include MediaElement.js
 */
function flowplayer_prepare_scripts() {
	global $fv_fp, $fv_wp_flowplayer_ver;

  if(
     isset($GLOBALS['fv_fp_scripts']) ||
     (isset($fv_fp->conf['js-everywhere']) && strcmp($fv_fp->conf['js-everywhere'],'true') == 0 ) ||
     isset($_GET['fv_wp_flowplayer_check_template'])
  ) {    
    wp_enqueue_script( 'flowplayer', flowplayer::get_plugin_url().'/flowplayer/fv-flowplayer.min.js', array('jquery'), $fv_wp_flowplayer_ver, true );

    $sPluginUrl = preg_replace( '~^.*://~', '//', FV_FP_RELATIVE_PATH );
  
    $sCommercialKey = (isset($fv_fp->conf['key']) && $fv_fp->conf['key'] != 'false' && strlen($fv_fp->conf['key']) > 0) ? $fv_fp->conf['key'] : '';
    $sLogo = ($sCommercialKey && isset($fv_fp->conf['logo']) && $fv_fp->conf['logo'] != 'false' && strlen($fv_fp->conf['logo']) > 0) ? $fv_fp->conf['logo'] : '';
    
    if( $fv_fp->load_mediaelement && !wp_script_is('wp-mediaelement') ) {
      wp_enqueue_script( 'flowplayer-mediaelement', flowplayer::get_plugin_url().'/mediaelement/mediaelement-and-player.min.js', array('jquery'), $fv_wp_flowplayer_ver, true );
    }
    wp_localize_script( 'flowplayer', 'fv_flowplayer_playlists', $fv_fp->aPlaylists );
    wp_localize_script( 'flowplayer', 'fv_fp_ajaxurl', site_url().'/wp-admin/admin-ajax.php' );
    
    $aConf = array('embed' => array( 'library' => $sPluginUrl.'/flowplayer/fv-flowplayer.min.js', 'script' => $sPluginUrl.'/flowplayer/embed.min.js', 'skin' => $sPluginUrl.'/css/flowplayer.css', 'swf' => $sPluginUrl.'/flowplayer/flowplayer.swf?ver='.$fv_wp_flowplayer_ver ) );
    if( $sCommercialKey ) $aConf['key'] = $sCommercialKey;
    if( !isset($fv_fp->conf['fixed_size']) || strcmp($fv_fp->conf['fixed_size'],'true') != 0 ) {
      $aConf['safety_resize'] = true;
    }
    if( $sLogo ) $aConf['logo'] = $sLogo;
    wp_localize_script( 'flowplayer', 'fv_flowplayer_conf', $aConf );
    if( current_user_can('manage_options') ) {
      wp_localize_script( 'flowplayer', 'fv_flowplayer_admin_input', array(true) );
      wp_localize_script( 'flowplayer', 'fv_flowplayer_admin_js_test', array(true) );
    }

    if( count($GLOBALS['fv_fp_scripts']) > 0 ) {
      foreach( $GLOBALS['fv_fp_scripts'] AS $sKey => $aScripts ) {
        wp_localize_script( 'flowplayer', $sKey.'_array', $aScripts );
      }
    }
  }
}

/**
 * Prints flowplayer javascript content to the bottom of the page.
 */
function flowplayer_display_scripts() {
	global $fv_fp;

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


/*
Handle attachment pages which contain videos
*/
function fv_flowplayer_attachment_page_video( $c ) {
	global $post;
  if( stripos($post->post_mime_type, 'video/') !== 0 && stripos($post->post_mime_type, 'audio/') !== 0 ) {
    return $c;
  }
  
  if( !$src = wp_get_attachment_url($post->ID) ) {
    return $c;
  }
  
  $meta = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
  $size = (isset($meta['width']) && isset($meta['height']) && intval($meta['width'])>0 && intval($meta['height'])>0 ) ? ' width="'.intval($meta['width']).'" height="'.intval($meta['height']).'"' : false;
  
  $shortcode = '[fvplayer src="'.$src.'"'.$size.']';
  
  $c = preg_replace( '~<p class=.attachment.[\s\S]*?</p>~', $shortcode, $c );
	return $c;
}
add_filter( 'prepend_attachment', 'fv_flowplayer_attachment_page_video' );
