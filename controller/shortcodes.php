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

add_filter( 'fv_flowplayer_attributes_retrieve', 'fv_flowplayer_getPlayerAttsFromDb', 10, 2);

add_action( 'wp_ajax_expand_player_shortcode', 'fv_flowplayer_expand_player_shortcode' );


/**
 * Returns playlist video item formatted for a shortcode,
 * so it's in the form of "video-src, video-src1, video-src2, rtmp:some-path, splash:some-url"
 * and can be added to the playlist section of that shortcode.
 *
 * @param $vid The video object from which to prepare the string data.
 *
 * @return string Returns the string data for a playlist item.
 */
function fv_flowplayer_getPlaylistItemData($vid) {
  $item = (!empty($vid['src']) ? $vid['src'] : '');

  if (!empty($vid['src1'])) {
    $item .= ',' . $vid['src1'];
  }

  if (!empty($vid['src2'])) {
    $item .= ',' . $vid['src2'];
  }

  if (!empty($vid['rtmp_path'])) {
    $item .= ',rtmp:' . $vid['rtmp_path'];
  }

  if (!empty($vid['splash'])) {
    $item .= ',' . $vid['splash'];
  }

  return $item;
}



/**
 * Returns caption formatted for a shortcode, so it can be used there.
 * Also, this function is used on 2 places, so that's why it's a function :P
 *
 * @param $vid The video object from which to prepare the string data.
 *
 * @return string Returns the string data for a captions item.
 */
function fv_flowplayer_getCaptionData($vid) {
  return (!empty($vid['caption']) ? $vid['caption'] : '');
}



/**
 * Returns startend tag formatted for a shortcode, so it can be used there.
 * Also, this function is used on 2 places, so that's why it's a function :P
 *
 * @param $vid The video object from which to prepare the string data.
 *
 * @return string Returns the string data for a startend item.
 */
function fv_flowplayer_getStartEndData($vid) {
  $str = (!empty($vid['start']) ? $vid['start'] : '');

  if ($str) {
    $str .= (!empty($vid['end']) ? '-' . $vid['end'] : '');
  }

  if (!$str) {
    $str = '-';
  }

  return $str;
}



/**
 * Generates a full code for a playlist from one that uses video IDs
 * stored in the database to one that conforms to the original long
 * playlist shortcode format (with multiple sources, rtmp, splashes etc.).
 */
function fv_flowplayer_generateFullPlaylistCode($atts) {
  static $cache = array();

  // check if we should change anything in the playlist code
  if (isset($atts['playlist']) && preg_match('/^[\d,]+$/m', $atts['playlist'])) {
    // serve what we can from the cache
    $ids = explode(',', $atts['playlist']);
    $newids = array();
    $new_playlist_tag = array();
    $new_caption_tag = array();
    $new_startend_tag = array();
    $first_video_data_cached = false;

    // check the first video, which is the main one for the playlist
    if (isset($cache[$ids[0]])) {
      $first_video_data_cached = true;
      $atts = array_merge($atts, $cache[$ids[0]]);
    }

    // prepare cached data and IDs that still need loading from DB
    foreach ($ids as $id) {
      if (isset($cache[$id])) {
        $new_playlist_tag[] = fv_flowplayer_getPlaylistItemData($cache[$id]);
        $new_caption_tag[] = fv_flowplayer_getCaptionData($cache[$id]);
        $new_startend_tag[] = fv_flowplayer_getStartEndData($cache[$id]);
      } else {
        $newids[] = (int) $id;
      }
    }

    if (count($newids)) {
      // load data from DB
      $videos = new FV_Player_Db_Shortcode_Player_Video($newids);
      $videos = $videos->getAllLoadedVideos();

      // cache first vid
      if (!$first_video_data_cached) {
        $vid = $videos[0]->getAllDataValues();
        $vid['subtitles'] = $videos[0]->getSubtitlesShortcodeData();
        $atts = array_merge($atts, $vid);
        $cache[$vid['id']] = $vid;

        $caption = fv_flowplayer_getCaptionData($vid);
        if ($caption) {
          $new_caption_tag[] = $caption;
        }

        $startend = fv_flowplayer_getStartEndData($vid);
        if ($startend != '-') {
          $new_startend_tag[] = $startend;
        }

        // remove the first video and keep adding the rest of the videos to the playlist tag
        array_shift( $videos );
      }

      // add rest of the videos into the playlist tag
      if (count($videos)) {
        // if this remains false, the caption tag does not need to be present
        $has_captions = false;

        // if this remains false, the startend tag does not need to be present
        $has_timings = false;

        foreach ( $videos as $vid_object ) {
          $vid = $vid_object->getAllDataValues();
          $vid['subtitles'] = $vid_object->getSubtitlesShortcodeData();
          $cache[ $vid['id'] ]  = $vid;
          $new_playlist_tag[] = fv_flowplayer_getPlaylistItemData( $vid );

          $caption = fv_flowplayer_getCaptionData($vid);
          if ($caption) {
            $has_captions = true;
          }
          $new_caption_tag[] = $caption;

          $startend = fv_flowplayer_getStartEndData($vid);
          if ($startend != '-') {
            $has_timings = true;
          }
          $new_startend_tag[] = $startend;
        }

        $atts['playlist'] = implode(';', $new_playlist_tag);

        if ($has_captions) {
          $atts['caption'] = implode( ';', $new_caption_tag );
        }

        if ($has_timings) {
          $atts['startend'] = implode( ';', $new_startend_tag );
        }
      } else {
        // only one video found, therefore this is not a playlist
        unset($atts['playlist']);

        $caption = fv_flowplayer_getCaptionData($vid);
        if ($caption) {
          $atts['caption'] = $caption;
        }

        $startend = fv_flowplayer_getStartEndData($vid);
        if ($startend != '-') {
          $atts['startend'] = $startend;
        }
      }
    } else {
      $atts['playlist'] = implode(';', $new_playlist_tag);

      if (count($new_caption_tag)) {
        $atts['caption'] = implode( ';', $new_caption_tag );
      }

      if (count($new_startend_tag)) {
        $atts['startend'] = implode( ';', $new_startend_tag );
      }
    }
  }

  return $atts;
}


/**
 * Maps attributes from database into their respective shortcode names.
 *
 * @param $att_name Attribute name from the database to map into shortcode format.
 *
 * @return mixed Returns the correct attribute name for shortcode use.
 */
function fv_flowplayer_mapDbAttributes2Shortcode($att_name) {
  $atts_map = array(
    'playlist'       => 'liststyle',
    'video_ads'      => 'preroll',
    'video_ads_post' => 'postroll'
  );

  return (isset($atts_map[$att_name]) ? $atts_map[$att_name] : $att_name);
}


/**
 * Maps attributes values from database into their respective shortcode values.
 *
 * @param $att_name  Attribute name from the database.
 * @param $att_value Attribute value from the database.
 *
 * @return mixed Returns the correct attribute value for shortcode use.
 */
function fv_flowplayer_mapDbAttributeValue2Shortcode($att_name, $att_value) {
  switch ($att_name) {
    case 'playlist_advance':
      return ($att_value == 'off' ? 'false' : 'true');
  }

  return $att_value;
}


/**
 * Retrieves player attributes from the database
 * as opposed to getting them from the old full-text
 * shortcode format.
 *
 * @param $id ID of the player to get attributes for.
 *
 * @return array|mixed Returns an array with all player attributes in it.
 *                     If the player ID is not found, an empty array is returned.
 * @throws Exception When the underlying video object throws.
 */
function fv_flowplayer_getPlayerAttsFromDb($atts) {
  static $cache = array();

  if (isset($atts['id'])) {
    if ( isset( $cache[ $atts['id'] ] ) ) {
      return $cache[ $atts['id'] ];
    }

    $player = new FV_Player_Db_Shortcode_Player($atts['id']);
    $data = $player->getAllDataValues();
    
    // did we find the player?
    if ( $data ) {
      foreach( $data AS $k => $v ) {
        $k = fv_flowplayer_mapDbAttributes2Shortcode($k);
        $v = fv_flowplayer_mapDbAttributeValue2Shortcode($k, $v);
        if( $v ) {
          // we omit empty values and they will get set to defaults if necessary
          $atts[$k] = $v;
        }
      }

      // add playlist / single video data
      $atts = array_merge( $atts, fv_flowplayer_generateFullPlaylistCode(
      // we need to prepare the same attributes array here
      // as is ingested by generateFullPlaylistCode()
      // when parsing the new playlist code on the front-end
        array(
          'playlist' => $data['videos']
        )
      ) );
      
    }
    
    $cache[ $atts['id'] ] = $atts;    

  }
  
  return $atts;
}



/**
 * AJAX method to generate expanded textual shortcode from database information
 * to build the shortcode editor UI on the front-end.
 */
function fv_flowplayer_expand_player_shortcode() {
  if (isset($_POST['playerID']) && is_numeric($_POST['playerID']) && intval($_POST['playerID']) == $_POST['playerID']) {
      $atts = $atts = apply_filters('fv_flowplayer_attributes_retrieve', array( 'id' => $_POST['playerID']));

      if (count($atts)) {
        $out = '[fvplayer';

        foreach ( $atts as $att_name => $att_value ) {
          $out .= ' ' . $att_name . '="' . $att_value . '"';
        }

        $out .= ']';

        echo $out;
      }
  }

  wp_die();
}



function flowplayer_content_handle( $atts, $content = null, $tag = false ) {
	global $fv_fp;
  if( !$fv_fp ) return false;

  // check for new playlist tag format with video data saved in DB
  $atts = apply_filters('fv_flowplayer_attributes_retrieve', $atts);

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
    'ad' => '',
    'ad_width' => '',
    'ad_height' => '',
    'ad_skip' => '',    
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
  
  if( intval($arguments['post']) > 0 ) {
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