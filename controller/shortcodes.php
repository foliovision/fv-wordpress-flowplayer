<?php
require_once dirname( __FILE__ ) . '/../models/flowplayer.php';
if (!class_exists('flowplayer_frontend')) 
  require_once dirname( __FILE__ ) . '/../models/flowplayer-frontend.php';

add_shortcode('flowplayer','flowplayer_content_handle');

add_shortcode('fvplayer','flowplayer_content_handle');

function flowplayer_content_handle( $atts, $content = null, $tag ) {
	global $fv_fp;
	
  if( $fv_fp->conf['parse_commas'] == 'true' ) {
    
    if( !isset( $atts['src'] ) ) {     
      foreach( $atts AS $key => $att ) {
        if( stripos( $att, 'src=' ) !== FALSE ) {
          if( stripos( $att, ',' ) === FALSE ) {  //  if the broken attribute is not using ','
            $atts['src'] = preg_replace( '/^\s*?src=[\'"](.*)[\'"].*?$/', '$1', $att );
          } else {
            $atts['src'] = preg_replace( '/^\s*?src=[\'"](.*)[\'"],\s*?$/', '$1', $att );
          }
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
        if( ( stripos( $att, 'popup=' ) !== FALSE || $is_popup ) && stripos( $att, 'src=' ) === FALSE && stripos( $att, 'splash=' ) === FALSE && stripos( $att, 'ad=' ) === FALSE) {
          $popup[] = $att;
          $is_popup = true;
          unset( $atts[$key] ); // = ''; //  let's remove it, so it won't confuse the rest of workaround
        }
      }
      $popup = implode( ' ', $popup );
      $atts['popup'] = preg_replace( '/^\s*?popup=[\'"](.*)[\'"]\s*?$/mi', '$1', $popup );
    }
    
    //	same for ad code
    if( !isset( $atts['ad'] ) ) {
      $ad = array();
      $is_ad = false;
      foreach( $atts AS $key => $att ) {
        if( !is_numeric( $key ) ) continue;
        if( ( stripos( $att, 'ad=' ) !== FALSE || $is_ad ) && stripos( $att, 'src=' ) === FALSE && stripos( $att, 'splash=' ) === FALSE && stripos( $att, 'popup=' ) === FALSE) {
          $ad[] = $att;
          $is_ad = true;
          unset( $atts[$key] ); // = ''; //  let's remove it, so it won't confuse the rest of workaround
        }
      }
      $ad = implode( ' ', $ad );
      $atts['ad'] = preg_replace( '/^\s*?ad=[\'"](.*)[\'"]\s*?$/mi', '$1', $ad );
    }    
    
  }
  /// End of addition                                  
  
  extract( shortcode_atts( array(
    'src' => '',
    'src1' => '',
    'src2' => '',
    'mobile' => '',
    'width' => '',
    'height' => '',
    'autoplay' => '',
    'splash' => '',
    'splashend' => '',
    'popup' => '',
    'controlbar' => '',
    'redirect' => '',
    'loop' => '',
    'engine' => '',
    'embed' => '',
    'subtitles' => '',
    'ad' => '',
    'ad_width' => '',
    'ad_height' => '',
    'ad_skip' => '',
    'align' => '',
    'rtmp' => '',
    'rtmp_path' => '',
    'playlist' => ''    
  ), $atts ) );
  
  if( $fv_fp->conf['parse_commas'] == 'true' ) {  
		$arguments['width'] = preg_replace('/\,/', '', $width);
		$arguments['height'] = preg_replace('/\,/', '', $height);
		$arguments['autoplay'] = preg_replace('/\,/', '', $autoplay);
		$arguments['splash'] = preg_replace('/\,/', '', $splash);
		$arguments['src1'] = preg_replace('/\,/', '', $src1);
		$arguments['src2'] = preg_replace('/\,/', '', $src2);
		$arguments['mobile'] = preg_replace('/\,/', '', $mobile);  
		$arguments['splashend'] = preg_replace('/\,/', '', $splashend);
		$arguments['popup'] = $popup;
		$arguments['controlbar'] = preg_replace('/\,/', '', $controlbar);
		$arguments['redirect'] = preg_replace('/\,/', '', $redirect);
		$arguments['loop'] = preg_replace('/\,/', '', $loop);
		$arguments['engine'] = preg_replace('/\,/', '', $engine);
		$arguments['embed'] = preg_replace('/\,/', '', $embed);
		$arguments['subtitles'] = preg_replace('/\,/', '', $subtitles);
		$arguments['ad'] = preg_replace('/\,/', '', $ad);  
		$arguments['ad_width'] = preg_replace('/\,/', '', $ad_width);  
		$arguments['ad_height'] = preg_replace('/\,/', '', $ad_height);   
		$arguments['ad_skip'] = preg_replace('/\,/', '', $ad_skip); 
		$arguments['align'] = preg_replace('/\,/', '', $align);   
		$arguments['rtmp'] = preg_replace('/\,/', '', $rtmp);   
		$arguments['rtmp_path'] = preg_replace('/\,/', '', $rtmp_path);    
		$arguments['playlist'] = $playlist;          
		$src = trim( preg_replace('/\,/', '', $src) ); 
	} else {
		$arguments = shortcode_atts( array(
			'src' => '',
			'src1' => '',
			'src2' => '',
			'mobile' => '',
			'width' => '',
			'height' => '',
			'autoplay' => '',
			'splash' => '',
			'splashend' => '',
			'popup' => '',
			'controlbar' => '',
			'redirect' => '',
			'loop' => '',
			'engine' => '',
			'embed' => '',
			'subtitles' => '',
			'ad' => '',
			'ad_width' => '',
			'ad_height' => '',
			'ad_skip' => '',
			'align' => '',
			'rtmp' => '',
			'rtmp_path' => '',
			'playlist' => ''    
		), $atts );
	}
	
	
	if( $src != '' || ( strlen($arguments['rtmp']) && strlen($arguments['rtmp_path']) ) ) {
		// build new player
    $new_player = $fv_fp->build_min_player($src,$arguments);		
    if (!empty($new_player['script'])) {
      $GLOBALS['fv_fp_scripts'][] = $new_player['script'];
    }
	}
  return $new_player['html'];
}
?>