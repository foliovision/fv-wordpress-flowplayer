<?php
/*  FV Player - HTML5 video player
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

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

require_once dirname( __FILE__ ) . '/../models/fv-player.php';
if (!class_exists('flowplayer_frontend'))
  require_once dirname( __FILE__ ) . '/../models/fv-player-frontend.php';

add_shortcode('flowplayer','flowplayer_content_handle');

add_shortcode('fvplayer','flowplayer_content_handle');

add_shortcode('fv_time','fv_player_time');

function flowplayer_content_handle( $atts, $content = null, $tag = false ) {
	global $fv_fp;
  if( !$fv_fp ) return false;

  if( $fv_fp->_get_option('parse_commas') && strcmp($tag,'flowplayer') == 0 ) {

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

  $atts = wp_parse_args( $atts, array(
    'admin_warning' => '',
    'align' => '',
    'autoplay' => '',
    'caption' => '',
    'caption_html' => '',
    'controlbar' => '',
    'embed' => '',
    'end_popup_preview' => '',
    'engine' => '',
    'height' => '',
    'mobile' => '',
    'linking' => '',
    'liststyle' => '',
    'live' => '',
    'logo' => '',
    'loop' => '',
    'overlay' => '',
    'overlay_width' => '',
    'overlay_height' => '',
    'overlay_skip' => '',
    'play_button' => '',
    'playlist' => '',
    'playlist_advance' => '',
    'playlist_hide' => '',
    'popup' => '',
    'post' => '',
    'redirect' => '',
    'rtmp' => '',
    'rtmp_path' => '',
    'share' => '',
    'skin' => '',
    'speed' => '',
    'splash' => '',
    'splash_text' => '',
    'splashend' => '',
    'src' => '',
    'src1' => '',
    'src2' => '',
    'sticky' => '',
    'subtitles' => '',
    'title' => '',
    'width' => '',
  ) );

  if( $fv_fp->_get_option('parse_commas') && strcmp($tag,'flowplayer') == 0 ) {
		foreach( $atts AS $k => $v ) {
			if( in_array($k, array('admin_warning','caption','caption_html','playlist','popup','share') ) ) {
				$arguments[$k] = $v;
			} else {
				$arguments[$k] = preg_replace('/\,/', '', $v);
			}
		}

	} else {
		$arguments = $atts;
	}

  if( ( !isset($arguments['src']) || strlen(trim($arguments['src'])) == 0 ) && isset($arguments['mobile']) && strlen(trim($arguments['mobile'])) ) {
    $arguments['src'] = $arguments['mobile'];
    unset($arguments['mobile']);
  }

  $arguments = apply_filters( 'fv_flowplayer_shortcode', $arguments, $fv_fp, $atts );

  if( $arguments['post'] == 'this' ) {
    $arguments['post'] = get_the_ID();
  }

  // accepts:
  // 1) [fvplayer id="198"] - a DB-based shortcode in the form
  // 2) [fvplayer src="198" playlist="198;199;200"] - a front-end user's playlist shortcode that is programatically passed to this method
  if( (!empty($arguments['id']) && intval($arguments['id']) > 0) || (!empty($arguments['src']) && is_numeric($arguments['src']) && intval($arguments['src']) > 0) ) {
    $new_player = $fv_fp->build_min_player(false, $arguments);
    if (!empty($new_player['script'])) {
      $GLOBALS['fv_fp_scripts'] = $new_player['script'];
    }

    if ( $new_player ) {
      return $new_player['html'];
    } else {
      return '';
    }

  } else  if( intval($arguments['post']) > 0 ) {
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
        if( strlen($aArgs['title']) ) {
          $aArgs['title'] = apply_filters( 'fv_player_title', $aArgs['title'], false );
        }

        $new_player = $fv_fp->build_min_player( $aArgs['src'],$aArgs );
        if ( $new_player ) {
          $sHTML .= $new_player['html'];
        }
      }

      return $sHTML;
    }

  } else if( $arguments['src'] != '' || ( ( ( strlen($fv_fp->conf['rtmp']) && $fv_fp->conf['rtmp'] != 'false' ) || strlen($arguments['rtmp'])) && strlen($arguments['rtmp_path']) ) ) {
		// build new player
    $new_player = $fv_fp->build_min_player($arguments['src'],$arguments);
    if (!empty($new_player['script'])) {
      $GLOBALS['fv_fp_scripts'] = $new_player['script'];
    }

    if ( $new_player ) {
      return $new_player['html'];
    } else {
      return '';
    }

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
    $shortcode .= ' admin_warning="Admin note: Some of the OptimizePress styling parameters are not supported by FV Player. Please visit the <a href=\''.admin_url('admin.php?page=fvplayer').'\'>settings</a> and set your global appearance preferences there."';
  }

  $shortcode .= ']';

  return $shortcode;
}


function fv_player_time( $args = array() ) {
  global $post, $fv_fp;

  if( !empty($args['id']) ) {
    $player = new FV_Player_Db_Player($args['id']);
    if( $player->getIsValid() ) {
      foreach( $player->getVideos() AS $video ) {
        if( $duration = $video->getDuration() ) {
          return flowplayer::format_hms( $duration );
        }
      }
    }
  }

  if( $post->ID > 0 && isset($fv_fp->aCurArgs['src']) ) {
    return flowplayer::get_duration( $post->ID, $fv_fp->aCurArgs['src'] );
  } else {
    return flowplayer::get_duration_post();
  }
}


function fv_player_handle_vimeo_links( $html, $closing = '<!-- link converted by FV Player  -->' ) {
  $html = preg_replace( '~<iframe[^>]*?vimeo\.com/video/(\d+)[^>]*?(\?h=[a-z0-9]+)?[^>]*?></iframe>~', '[fvplayer src="http://vimeo.com/$1$2"]' . $closing, $html );
  return $html;
}

function fv_player_handle_youtube_links( $html, $closing = '<!-- link converted by FV Player  -->' ) {
  $html = preg_replace( '~<iframe[^>]*?youtube(?:-nocookie)?\.com/(?:embed|v)/(.*?)[\'"&#\?][^>]*?></iframe>~', '[fvplayer src="http://youtube.com/watch?v=$1"]' . $closing, $html );
  return $html;
}

function fv_player_handle_video_tags( $html, $closing = '<!-- link converted by FV Player  -->' ) {
  $html = preg_replace( '~<figure class="wp-block-video"><video[^>]*?src\s*=\s*"(.+?)"[^>]*?></video></figure>~', '<figure class="wp-block-video">[fvplayer src="$1"]' . $closing . '</figure>' , $html);
  return $html;
}

function fv_flowplayer_shortcode_video( $output ) {
  $aArgs = func_get_args();
  $atts = $aArgs[1];

  $bridge_atts = array();
  if( isset($atts['src']) ) {
    $bridge_atts['src'] = $atts['src'];
  }

  $count = 0;
  foreach( array('mp4','webm','ogv','mov','flv','wmv','m4v','mp3') AS $key => $value ) {
    if( !empty($atts[$value]) ) {
      $src = 'src'.(( $count > 0 ) ? $count : '');
      $bridge_atts[$src] = $atts[$value];
      $count++;
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
    return "<!--FV Player video shortcode integration - no attributes recognized-->";
  }
  return flowplayer_content_handle( $bridge_atts, false, 'video' );
}

function fv_flowplayer_shortcode_playlist( $output ) {
  $aArgs = func_get_args();
  $atts = $aArgs[1];

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
  $aPlaylistCaptions = array();
  $aPlaylistDurations = array();
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

    $aPlaylistCaptions[] = str_replace( array('"',';'), '', flowplayer::esc_caption($attachment->post_title) );

    $meta = wp_get_attachment_metadata( $attachment->ID );
    if( !empty($meta) ) {
      if( !empty($meta['length']) ) {
        $aPlaylistDurations[] = $meta['length'];
      }
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
  if( count($aPlaylistCaptions) > 1 || $atts['tracklist'] ) $bridge_atts['caption'] = implode(';',$aPlaylistCaptions);
  $bridge_atts['durations'] = implode(';',$aPlaylistDurations);

  if( $atts['tracklist'] ) {
    $bridge_atts['listshow'] = true;
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

  if( !empty($atts['type']) && $atts['type'] == 'audio' ) {
    $bridge_atts['liststyle'] = 'horizontal';
  }

  return flowplayer_content_handle( $bridge_atts, false, 'video' );
}

global $fv_fp;
if( ( empty($_POST['action']) || sanitize_key( $_POST['action'] ) != 'parse-media-shortcode' ) && ( empty($_GET['action']) || sanitize_key( $_GET['action'] ) != 'edit' ) && !empty($fv_fp->conf['integrations']['wp_core_video']) && $fv_fp->conf['integrations']['wp_core_video'] == 'true' ) {
  add_filter( 'wp_video_shortcode_override', 'fv_flowplayer_shortcode_video', 10, 4 );
  add_filter( 'wp_audio_shortcode_override', 'fv_flowplayer_shortcode_video', 10, 4 );
  add_filter( 'post_playlist', 'fv_flowplayer_shortcode_playlist', 10, 2 );
  add_filter( 'the_content', 'fv_player_handle_youtube_links' );
  add_filter( 'the_content', 'fv_player_handle_vimeo_links' );
  add_filter( 'embed_oembed_html', 'fv_player_handle_vimeo_links' );
  add_filter( 'embed_oembed_html', 'fv_player_handle_youtube_links' );
  add_filter( 'the_content', 'fv_player_handle_video_tags' );

  /**
   * Fix excerpts if video links are converted to FV Player.
   *
   * If FV Player is inserted using shortcode, then it's removed by core WordPress
   * strip_shortcodes() before the excerpt is created in wp_trim_excerpt().
   *
   * However if we use the "Handle WordPress audio/video" setting
   * integrations[]'wp_core_video'] the video is just a link and it does not get stripped.
   *
   * That way FV Player puts in the HTML code which is then removed, while keeping its text
   * content and this shows as excerpt:
   *
   * "Please enable JavaScriptplay-sharp-fill 01:53 LinkEmbedCopy and paste this HTML code into your webpage to embed."
   */
  add_filter( 'wp_trim_words', 'fv_player_fix_wp_trim_words', 100, 4 );

  function fv_player_fix_wp_trim_words( $text, $num_words, $more, $original_text ) {

    if ( stripos( $original_text, '<!-- link converted by FV Player  -->' ) !== false ) {
      remove_filter( 'wp_trim_words', 'fv_player_fix_wp_trim_words', 100, 4 );

      // Since the player HTML code ends with just </div> we look for the HTML comment to be able to detect where it ends.
      $original_text = preg_replace( '~<div id="wpfp[\s\S]*?<!-- link converted by FV Player  -->~', '', $original_text );

      $text = wp_trim_words( $original_text, $num_words, $more );

      add_filter( 'wp_trim_words', 'fv_player_fix_wp_trim_words', 100, 4 );
    }

    return $text;
  }
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


add_shortcode( 'fvplayer_watched', 'fvplayer_watched' );

function fvplayer_watched( $args = array() ) {
  $args = wp_parse_args( $args, array(
    'count' => 20,
    'include' => 'all',
    'post_type' => 'any',
    'user_id' => get_current_user_id()
  ) );

  $args['full_details'] = true;

  $videos = fv_player_get_user_watched_video_ids( $args );
  if( count($videos) == 0 ) {
    return "<p>No watched videos.</p>";
  }

  $video_ids = array_filter( array_keys($videos), 'is_numeric' );

  $video_ids = array_map( 'absint', $video_ids );
  $placeholder = implode( ', ', array_fill( 0, count( $video_ids ), '%d' ) );

  global $wpdb;
  $videos2durations = $wpdb->get_results(
    $wpdb->prepare(
      // $placeholders is a string of %d created above
      // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
      "SELECT v.id AS video_id, vm.meta_value AS duration FROM {$wpdb->prefix}fv_player_videos AS v JOIN {$wpdb->prefix}fv_player_videometa AS vm ON v.id = vm.id_video WHERE v.id IN ({$placeholder}) AND vm.meta_key = 'duration'",
      $video_ids
    )
  );

  $html = "<ul>\n";
  foreach( $videos AS $video_id => $data ) {

    global $FV_Player_Db;
    $objVideo = new FV_Player_Db_Video( $video_id, array(), $FV_Player_Db );
    if( $objVideo->getTitle() ) {
      $line = $objVideo->getTitle();
    } else {
      $line = $objVideo->getTitleFromSrc();
    }

    $post = get_post( $data['post_id'] );
    if( $post ) {
      $line .= " in <a href='".get_permalink($post)."'>".$post->post_title."</a>";
    }

    if( !empty($data['time']) ) {

      foreach( $videos2durations AS $details ) {
        if( $details->video_id == $video_id ) {
          // In some strange cases the duration might not be set right
		      if( $data['time'] <= intval($details->duration) ) {
            $line .= ' (<abbr title="'.esc_attr($data['message']).'">'.round( 100 * $data['time'] / intval($details->duration) ).'%</abbr>)';
		      }
        }
      }
    }

    $html .= "<li>".$line."</li>\n";
  }
  $html .= "</ul>\n";

  return $html;
}
