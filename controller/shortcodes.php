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

require_once dirname( __FILE__ ) . '/../models/flowplayer.php';
if (!class_exists('flowplayer_frontend')) 
  require_once dirname( __FILE__ ) . '/../models/flowplayer-frontend.php';

add_shortcode('flowplayer','flowplayer_content_handle');

add_shortcode('fvplayer','flowplayer_content_handle');

add_shortcode('fv_time','fv_player_time');

function flowplayer_content_handle( $atts, $content = null, $tag ) {
	global $fv_fp;
  if( !$fv_fp ) return false;	
  
  if( $fv_fp->conf['parse_commas'] == 'true' && strcmp($tag,'flowplayer') == 0 ) {
    
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
    'play_button' => '',
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
    'playlist' => '',
    'playlist_hide' => '',
    'admin_warning' => '',
    'live' => '',
    'caption' => '',
    'logo' => '',
    'share' => '',
    'post' => '',
    'speed' => '',
    'liststyle' => ''
  ), $atts ) );

  if( $fv_fp->conf['parse_commas'] == 'true' && strcmp($tag,'flowplayer') == 0 ) {  
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
    $arguments['play_button'] = preg_replace('/\,/', '', $play_button);
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
    $arguments['playlist_hide'] = $playlist_hide;
    $arguments['admin_warning'] = $admin_warning;
    $arguments['live'] = $live;
    $arguments['caption'] = $caption;
    $arguments['logo'] = $logo;
    $arguments['share'] = $share;
    $arguments['post'] = $post;
    $arguments['speed'] = $speed;
    $arguments['liststyle'] = $liststyle;
		$arguments['src'] = trim( preg_replace('/\,/', '', $src) );
    
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
      'play_button' => '',
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
			'playlist' => '',
      'playlist_hide' => '',
      'admin_warning' => '',
      'live' => '',
      'caption' => '',
      'logo' => '',
      'share' => '',
      'post' => '',
      'speed' => '',
      'liststyle' => ''
		), $atts );
	}
  
  if( ( !isset($arguments['src']) || strlen(trim($arguments['src'])) == 0 ) && isset($arguments['mobile']) && strlen(trim($arguments['mobile'])) ) {
    $arguments['src'] = $arguments['mobile'];
    unset($arguments['mobile']);
  }
  
  foreach( $atts AS $key => $val ) {
    if( stripos($key,'subtitles_') === 0 ) {
      $arguments[$key] = $val;
    }
  }
  
  $arguments = apply_filters( 'fv_flowplayer_shortcode', $arguments, $fv_fp, $atts );
	
  if( $post == 'this' ) {
    $post = get_the_ID();
  }
  
  if( intval($post) > 0 ) {
    $objVideoQuery = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent' => intval($post), 'post_mime_type' => 'video' ) );
    if( $objVideoQuery->have_posts() ) {
      $sHTML = '';
      while( $objVideoQuery->have_posts() ) {
        $objVideoQuery->the_post();
        $aArgs = $arguments;
        $aArgs['src'] = wp_get_attachment_url(get_the_ID());
        if( $aSplash = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'large' ) ) {
          $aArgs['splash'] = $aSplash[0];
        }
        if( strlen($aArgs['lightbox']) ) {
          $aArgs['lightbox'] .= ';'.html_entity_decode(get_the_title());
        }
        if( strlen($aArgs['caption']) ) {
          $aArgs['caption'] = apply_filters( 'fv_player_caption', $aArgs['caption'], false );
        }        

        $new_player = $fv_fp->build_min_player( $aArgs['src'],$aArgs );
        $sHTML .= $new_player['html'];
      }

      return $sHTML;
    }
        
  } else if( $arguments['src'] != '' || ( ( ( strlen($fv_fp->conf['rtmp']) && $fv_fp->conf['rtmp'] != 'false' ) || strlen($arguments['rtmp'])) && strlen($arguments['rtmp_path']) ) ) {
		// build new player
    $new_player = $fv_fp->build_min_player($arguments['src'],$arguments);		
    if (!empty($new_player['script'])) {
      $GLOBALS['fv_fp_scripts'] = $new_player['script'];
    }
    return $new_player['html'];
	}
  return false;
}




add_filter( 'the_content', 'fv_flowplayer_optimizepress', 1 );

function fv_flowplayer_optimizepress( $post_content ) {
  
  if( stripos( $post_content, '[video_player type="url"' ) === false ) {
    return $post_content;    
  }
  
  $post_content = preg_replace_callback( '~\[video_player.*?\].*?\[/video_player\]~', 'fv_flowplayer_optimizepress_bridge', $post_content );
  return $post_content;
}

function fv_flowplayer_optimizepress_bridge( $input ) {
  $video = $input[0];
  
  $atts = shortcode_parse_atts($video);

  $default = array(
			'type' => 'embed',  //  na
			'hide_controls' => 'N', //  todo
			'auto_play' => 'N', //  ok
			'auto_buffer' => 'N', //  todo
			'width' => 511, //  ok
			'height' => 288,  //  ok
			'margin_top' => 0,  //  todo
			'margin_bottom' => 20,  //  todo
			'border_size' => 0, //  todo
			'border_color' => '#fff', //  todo
			'placeholder' => '',  //  ok
			'align' => 'center',  //  ok
			'youtube_url' => '',  //  na
			'youtube_auto_play' => 'N', //  na
			'youtube_hide_controls' => 'N', //  na
			'youtube_remove_logo' => 'N', //  na
			'youtube_show_title_bar' => 'N',  //  na
			'youtube_force_hd' => '', //  na
			'url1' => '', //  ok
			'url2' => '', //  ok
	);
	$vars = shortcode_atts($default, $atts);
  
  $shortcode = '[fvplayer';
  
  $content = preg_replace( '~\[video_player.*?\](.*?)\[/video_player\]~', '$1', $video );
  $content = base64_decode($content);
  if(preg_match('|(https?://[^<"]+)|im',$content,$matches)){
    $shortcode .= ' src="'.$matches[1].'"';
  }
  $url1 = base64_decode($atts['url1']);
  if(preg_match('|(https?://[^<"]+)|im',$url1,$matches)){
    $shortcode .= ' src1="'.$matches[1].'"';
  }
  $url2 = base64_decode($atts['url2']);
  if(preg_match('|(https?://[^<"]+)|im',$url2,$matches)){
    $shortcode .= ' src2="'.$matches[1].'"';
  }
  
  if( $vars['placeholder'] ) {
    $shortcode .= ' splash="'.$vars['placeholder'].'"';
  }
  
  if( $vars['auto_play'] == 'Y' ) {
    $shortcode .= ' autoplay="true"';
  }  
  
  $shortcode .= ' width="'.$vars['width'].'"';
  $shortcode .= ' height="'.$vars['height'].'"';
  $shortcode .= ' align="'.$vars['align'].'"';

  if( current_user_can('manage_options') &&
    (
      ( isset($vars['margin-top']) && $vars['margin-top'] > 0 ) ||
      ( isset($vars['margin-bottom']) && $vars['margin-bottom'] > 0 && $vars['margin-bottom'] != 20 ) ||
      ( isset($vars['hide_controls']) && $vars['hide_controls'] == 'Y' ) ||
      ( isset($vars['auto_buffer']) && $vars['auto_buffer'] == 'Y' ) ||
      ( isset($vars['border_size']) && $vars['border_size'] > 0 ) ||
      isset($vars['border_color'])
    )
  ) {
    $shortcode .= ' admin_warning="Admin note: Some of the OptimizePress styling parameters are not supported by FV Flowplayer. Please visit the <a href=\''.admin_url('options-general.php?page=fvplayer').'\'>settings</a> and set your global appearance preferences there."';
  }
  
  $shortcode .= ']';

  return $shortcode;
}


function fv_player_time() {
  global $post, $fv_fp;
  
  if( $post->ID > 0 && isset($fv_fp->aCurArgs['src']) ) {
    return flowplayer::get_duration( $post->ID, $fv_fp->aCurArgs['src'] );
  } else {
    return flowplayer::get_duration_post();
  }
}


global $fv_fp;
if( ( empty($_POST['action']) || $_POST['action'] != 'parse-media-shortcode' ) && ( empty($_GET['action']) || $_GET['action'] != 'edit' ) && !empty($fv_fp->conf['integrations']['wp_core_video']) && $fv_fp->conf['integrations']['wp_core_video'] == 'true' ) {
    
  function fv_flowplayer_shortcode_video( $output ) {
    $aArgs = func_get_args();
    $atts = $aArgs[1];
    
    $bridge_atts = array();
    if( isset($atts['src']) ) {
      $bridge_atts['src'] = $atts['src'];
    }
    foreach( array('mp4','webm','ogv','mov','flv','wmv','m4v') AS $key => $value ) {
      $src = 'src'.(( $key > 0 ) ? $key : '');
      if( isset($atts[$value]) ) {
        $bridge_atts[$src] = $atts[$value];
      }
    }
    
    if( isset($atts['poster']) ) {
      $bridge_atts['splash'] = $atts['poster'];
    }
    
    if( isset($atts['loop']) && $atts['loop'] == 'on' ) {
      $bridge_atts['loop'] = 'true';
    } else if( isset($atts['loop']) && $atts['loop'] == 'off' ) {
      $bridge_atts['loop'] = 'false';
    }
    
    if( isset($atts['autoplay']) && $atts['autoplay'] == 'on' ) {
      $bridge_atts['autoplay'] = 'true';
    } else if( isset($atts['loop']) && $atts['loop'] == 'off' ) {
      $bridge_atts['autoplay'] = 'false';
    }
    
    if( isset($atts['width']) ) {
      $bridge_atts['width'] = $atts['width'];
    }
    if( isset($atts['height']) ) {
      $bridge_atts['height'] = $atts['height'];
    }
    
    if( count($bridge_atts) == 0 ) {
      return "<!--FV Flowplayer video shortcode integration - no attributes recognized-->";
    }
    return flowplayer_content_handle( $bridge_atts, false, 'video' );
  }
  
  add_filter( 'wp_video_shortcode_override', 'fv_flowplayer_shortcode_video', 10, 4 );
  
  
  
  
  function fv_flowplayer_shortcode_playlist( $output ) {
    $aArgs = func_get_args();
    $atts = $aArgs[1];

    if( !empty($atts['type']) && $atts['type'] != 'video' ) {
      return false;
    }
        
    //  copy from wp-includes/media.php wp_playlist_shortcode()
    global $post;
    if ( ! empty( $attr['ids'] ) ) {
      // 'ids' is explicitly ordered, unless you specify otherwise.
      if ( empty( $attr['orderby'] ) ) {
        $attr['orderby'] = 'post__in';
      }
      $attr['include'] = $attr['ids'];
    }  
    
    $atts = shortcode_atts( array(
      'type'		=> 'audio',
      'order'		=> 'ASC',
      'orderby'	=> 'menu_order ID',
      'id'		=> $post ? $post->ID : 0,
      'include'	=> '',
      'exclude'   => '',
      'style'		=> 'light',
      'tracklist' => true,
      'tracknumbers' => true,
      'images'	=> true,
      'artists'	=> true
    ), $atts, 'playlist' );    
      
    $args = array(
      'post_status' => 'inherit',
      'post_type' => 'attachment',
      'post_mime_type' => $atts['type'],
      'order' => $atts['order'],
      'orderby' => $atts['orderby']
    );
    
    if( !empty($atts['include']) ) {
      $args['include'] = $atts['include'];
      $_attachments = get_posts( $args );
      if( !count($_attachments) ) {
        return false;
      }
  
      $attachments = array();
      foreach( $_attachments as $key => $val ) {
        $attachments[$val->ID] = $_attachments[$key];
      }
    } else {
      return false;
    }
    
    
    $bridge_atts = array();
    $aPlaylistItems = array();
    $aPlaylistImages = array();
    $i = 0;
    foreach ( $attachments as $attachment ) {
      $i++;
      
      $url = wp_get_attachment_url( $attachment->ID );
      if( $i == 1 ) {
        $bridge_atts['src'] = $url;
      } else {
        $aPlaylistItems[] = $url;
      }
  
      $thumb_id = get_post_thumbnail_id( $attachment->ID );
      $src = false;
      if( !empty( $thumb_id ) ) {        
        list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
      }
      if( $i == 1 ) {
        $bridge_atts['splash'] = $src;
      } else {
        $aPlaylistImages[] = $src;
      }      

    }
    
    
    $bridge_atts['playlist'] = '';
    foreach( $aPlaylistItems AS $key => $src ) {
      $bridge_atts['playlist'] .= $src;
      if( $aPlaylistImages[$key] ) {
        $bridge_atts['playlist'] .= ','.$aPlaylistImages[$key];
      }
      $bridge_atts['playlist'] .= ';';
    }
    $bridge_atts['playlist'] = trim($bridge_atts['playlist'],';');
     
    if( isset($atts['width']) ) {
      $bridge_atts['width'] = $atts['width'];
    }
    if( isset($atts['height']) ) {
      $bridge_atts['height'] = $atts['height'];
    }
    
    if( count($bridge_atts) == 0 ) {
      return "<!--FV Flowplayer video shortcode integration - no attributes recognized-->";
    }

    return flowplayer_content_handle( $bridge_atts, false, 'video' );
  }
  
  add_filter( 'post_playlist', 'fv_flowplayer_shortcode_playlist', 10, 2 );
}


add_filter( 'fv_flowplayer_shortcode', 'fv_flowplayer_shortcode_fix_fancy_quotes' );

function fv_flowplayer_shortcode_fix_fancy_quotes( $aArgs ) {
  
  foreach( $aArgs AS $k => $v ) {   
    $v = preg_replace( "~^(\xe2\x80\x9c|\xe2\x80\x9d|\xe2\x80\xb3)~","", $v);
    $v = preg_replace( "~(\xe2\x80\x9c|\xe2\x80\x9d|\xe2\x80\xb3)$~","", $v);
            
    $aArgs[$k] = $v;
  }
  
  return $aArgs;
}