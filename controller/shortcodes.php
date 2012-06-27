<?php
require_once dirname( __FILE__ ) . '/../models/flowplayer.php';
if (!class_exists('flowplayer_frontend')) 
  require_once dirname( __FILE__ ) . '/../models/flowplayer-frontend.php';

add_shortcode('flowplayer','flowplayer_content_handle');

function flowplayer_content_handle( $atts ) {
  /// Addition  2010/07/12  mv 
  $fp = new flowplayer_frontend();
  if( $fp->conf['commas'] == 'true' ) {
    
    if( !isset( $atts['src'] ) ) {
      foreach( $atts AS $key => $att ) {
        if( stripos( $att, 'src=' ) !== FALSE ) {
          $atts['src'] = preg_replace( '/^\s*?src=[\'"](.*)[\'"],\s*?$/', '$1', $att );
          $i = $key+1;
          unset( $atts[$key] ); // = ''; //  let's remove it, so it won't confuse the rest of workaaround
        }
      }
    }
  
    if( !isset( $atts['splash'] ) ) {
      foreach( $atts AS $key => $att ) {
        if( stripos( $att, 'splash=' ) !== FALSE ) {
          $atts['splash'] = preg_replace( '/^\s*?splash=[\'"](.*)[\'"],\s*?$/', '$1', $att );
          unset( $atts[$key] ); // = ''; //  let's remove it, so it won't confuse the rest of workaround
        }
      }
    }
  
    //  the popup should really be a content of the shortcode, not an attribute
    //  this part will fix the popup if there is any single quote in it.
    if( !isset( $atts['popup'] ) ) {
      $popup = array();
      $is_popup = false;
      foreach( $atts AS $key => $att ) {
        if( !is_numeric( $key ) ) continue;
        if( ( stripos( $att, 'popup=' ) !== FALSE || $is_popup ) && stripos( $att, 'src=' ) === FALSE && stripos( $att, 'splash=' ) === FALSE) {
          $popup[] = $att;
          $is_popup = true;
          unset( $atts[$key] ); // = ''; //  let's remove it, so it won't confuse the rest of workaround
        }
      }
      $popup = implode( ' ', $popup );
      $atts['popup'] = preg_replace( '/^\s*?popup=[\'"](.*)[\'"]\s*?$/mi', '$1', $popup );
    }
    
  }
  /// End of addition
  
  extract( shortcode_atts( array(
      'src' => '',
      'src_mobile' => '',
      'src_webm' => '',
      'src_3gp' => '',
      'src_low' => '',
      'src_mobile' => '',
      'width' => '',
      'height' => '',
      'width_mobile' => '',
      'height_mobile' => '',
      'autoplay' => '',
      'splash' => '',
      'popup' => '',
      'controlbar' => '',
      'redirect' => '',
      'splashend' => ''
      ), $atts ) );
  
   $sources['normal'] = $src;
   $sources['low'] = $src_low;
   $sources['mobile'] = $src_mobile;
   $sources['webm'] = $src_webm;
   $sources['3gp'] = $src_3gp;
  
	$arguments['width'] = preg_replace('/\,/', '', $width);
	$arguments['height'] = preg_replace('/\,/', '', $height);
	$arguments['width_mobile'] = preg_replace('/\,/', '', $width_mobile);
	$arguments['height_mobile'] = preg_replace('/\,/', '', $height_mobile);
	$arguments['autoplay'] = preg_replace('/\,/', '', $autoplay);
//	$arguments['embed'] = $embed;
	$arguments['splash'] = preg_replace('/\,/', '', $splash);
	$arguments['splashend'] = preg_replace('/\,/', '', $splashend);
	$arguments['popup'] = $popup;
	$arguments['controlbar'] = preg_replace('/\,/', '', $controlbar);
	$arguments['redirect'] = preg_replace('/\,/', '', $redirect);

	$src = preg_replace('/\,/', '', $src);
	if (trim($src) != '') {
		// build new player
	  //$fp = new flowplayer_frontend();
    $new_player = $fp->build_min_player($src,$sources,$arguments);
		$content = str_replace($src, $new_player['html'],$atts);
		$GLOBALS['scripts'][] = $new_player['script'];
	}
    return $new_player['html'];
//	return $content;
}
?>
