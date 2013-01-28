<?php
require_once dirname( __FILE__ ) . '/../models/flowplayer.php';
if (!class_exists('flowplayer_frontend')) 
  require_once dirname( __FILE__ ) . '/../models/flowplayer-frontend.php';

add_shortcode('flowplayer','flowplayer_content_handle');

add_shortcode('fvplayer','flowplayer_content_handle');

function flowplayer_content_handle( $atts, $content = null, $tag ) {
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
    
  }
  /// End of addition                                  
  
  extract( shortcode_atts( array(
    'src' => '',
    'width' => '',
    'height' => '',
    'autoplay' => '',
    'splash' => '',
    'controlbar' => '',
    'redirect' => '',
    'loop' => ''
  ), $atts ) );
  
	$arguments['width'] = preg_replace('/\,/', '', $width);
	$arguments['height'] = preg_replace('/\,/', '', $height);
	$arguments['autoplay'] = preg_replace('/\,/', '', $autoplay);
	$arguments['splash'] = preg_replace('/\,/', '', $splash);
	$arguments['controlbar'] = preg_replace('/\,/', '', $controlbar);
	$arguments['redirect'] = preg_replace('/\,/', '', $redirect);
  $arguments['loop'] = preg_replace('/\,/', '', $loop);
  
  $arguments['html5'] = true;
  if ($tag == 'flowplayer') {
    $arguments['html5'] = false;  
  }
    
	$src = preg_replace('/\,/', '', $src);
	if (trim($src) != '') {
		// build new player
    $new_player = $fp->build_min_player($src,$arguments);		
    $GLOBALS['scripts'][] = $new_player['script'];
	}
  return $new_player['html'];
}
?>
