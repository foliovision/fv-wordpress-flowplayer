<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
    Copyright (C) 2015  Foliovision

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

/**
 * Extension of original flowplayer class intended for frontend.
 */
class flowplayer_frontend extends flowplayer
{

  var $ajax_count = 0;
  
  var $autobuffer_count = 0;  
  
  var $expire_time = 0;
  
  var $aPlayers = array();
  
  var $aCurArgs = array();
  
  var $sHTMLAfter = false;
  
  var $count_tabs = 0;

  var $currentPlayerObject = null;

  var $currentVideoObject = null;


  /**
   * Retrieves instance of current player
   * with data loaded from database.
   *
   * @return FV_Player_Db_Player | null
   */
  function current_player() {
    return $this->currentPlayerObject;
  }

  /**
   * Retrieves instance of current video
   * with data loaded from database.
   *
   * @return FV_Player_Db_Video | null
   */
  function current_video() {
    return $this->currentVideoObject;
  }

  /**
   * Retrieves list of player instances containing videos
   * given by the $ids_string variable.
   *
   * @param $ids_string string ID or IDs (comma-separated) of videos to search players for.
   *
   * @return array Returns array of player objects found.
   */
  function get_players_by_video_ids( $ids_string ) {
    global $wpdb;
    $ret = array();
    $ids_string = esc_sql( $ids_string );

    $results = $wpdb->get_results( '
          SELECT
            id
          FROM
            ' . FV_Player_Db_Player::get_db_table_name() . '
          WHERE
            (videos = "' . $ids_string . '" OR videos LIKE "%,' . $ids_string . '" OR videos LIKE "' . $ids_string . ',%")'
    );

    foreach ( $results as $row ) {
      $ret[] = new FV_Player_Db_Player( $row->id );
    }

    return $ret;
  }

  /**
   * Builds the HTML and JS code of single flowplayer instance on a page/post.
   *
   * @param string $media URL or filename (in case it is in the /videos/ directory) of video file to be played.
   * @param array $args Array of arguments (name => value).
   *
   * @return array array with 2 elements - 'html' => html code displayed anywhere on page/post, 'script' => javascript code displayed before </body> tag
   * @throws Exception If any of the underlying classes throws an exception.
   */
  function build_min_player($media,$args = array()) {

    $this->hash = md5($media.$this->_salt()); //  unique player id
    // todo: harmonize this, the media arg was a bad idea in the first place
    if( !empty($media) ) {
      $this->aCurArgs['src'] = $media;
    }
    $this->aCurArgs = apply_filters( 'fv_flowplayer_args_pre', $args );

    // restore list styling from the shortcode, if provided,
    // as it needs to override the DB value
    if (!empty($args['liststyle'])) {
      $this->aCurArgs['liststyle'] = $args['liststyle'];
    }

    // load attributes from player into $this->aCurArgs if we're receiving
    // preview POST data, as they are not all present here yet
    if( $player = $this->current_player() ) {

      if (isset($_GET['fv_player_preview']) && $_GET['fv_player_preview'] == 'POST' && isset($_POST['fv_player_preview_json'])) {
        foreach ($player->getAllDataValues() as $key => $value) {
          if (empty($this->aCurArgs[$key]) && !empty($value)) {
            $this->aCurArgs[$key] = $value;
          }
        }
      }

      if( $videos = $player->getVideos() ) {
        if( !empty($videos[0]) && (
            $videos[0]->getMetaValue('audio',true) ||
            preg_match( '~\.(mp3|wav|ogg)([?#].*?)?$~', $videos[0]->getSrc() )
          )
        ) {
          // force horizontal playlist style for audio as that the only one styled properly
          $this->aCurArgs['liststyle'] = 'horizontal';
        }
      }
    } else if(preg_match( '~\.(mp3|wav|ogg)([?#].*?)?$~', $media ) ) {
      $this->aCurArgs['liststyle'] = 'horizontal';
    }
    
    $media = $this->aCurArgs['src'];

    if( !$media && empty($this->aCurArgs['rtmp_path']) ) {
      return;
    }

    $this->sHTMLAfter = false;
    $player_type = 'video';
    $rtmp = false;
    $youtube = false;
    $vimeo = false;
    $wistia = false;
    $scripts_after = '';
    
    $attributes = array();
    
    // returned array with new player's html and javascript content
    if( !isset($GLOBALS['fv_fp_scripts']) ) {
      $GLOBALS['fv_fp_scripts'] = array();
    }
    $this->ret = array('html' => '', 'script' => $GLOBALS['fv_fp_scripts'] );  //  note: we need the white space here, it fails to add into the string on some hosts without it (???)
    
      
    
    /*
     *  Set common variables
     */
    $width = $this->_get_option('width');
    $height = $this->_get_option('height');
    if (isset($this->aCurArgs['width'])&&!empty($this->aCurArgs['width'])) $width = trim($this->aCurArgs['width']);
    if (isset($this->aCurArgs['height'])&&!empty($this->aCurArgs['height'])) $height = trim($this->aCurArgs['height']);    
            
    $src1 = ( isset($this->aCurArgs['src1']) && !empty($this->aCurArgs['src1']) ) ? trim($this->aCurArgs['src1']) : false;
    $src2 = ( isset($this->aCurArgs['src2']) && !empty($this->aCurArgs['src2']) ) ? trim($this->aCurArgs['src2']) : false;

    $splash_img = $this->get_splash();
    
    foreach( array( $media, $src1, $src2 ) AS $media_item ) {
      if( stripos( $media_item, 'rtmp://' ) === 0 ) {
        $rtmp = $media_item;
      }
    }

    if( ( !empty($this->aCurArgs['rtmp']) || $this->_get_option('rtmp') ) && !empty($this->aCurArgs['rtmp_path']) ) {
      $rtmp = trim( $this->aCurArgs['rtmp_path'] );
    }
  
    list( $media, $src1, $src2 ) = apply_filters( 'fv_flowplayer_media_pre', array( $media, $src1, $src2 ), $this );
    
    
    /*
     *  Which player should be used
     */
    foreach( array( $media, $src1, $src2 ) AS $media_item ) {        
      global $post;
      if( $post ) {
        $fv_flowplayer_meta = get_post_meta( $post->ID, '_fv_flowplayer', true );
        if( $fv_flowplayer_meta && isset($fv_flowplayer_meta[sanitize_title($media_item)]['time']) ) {
          $this->expire_time = $fv_flowplayer_meta[sanitize_title($media_item)]['time'];
        }
      }
    }
    
    if( preg_match( "~(youtu\.be/|(?:youtube\.com|youtube-nocookie\.com)/(watch\?(.*&)?v=|(embed|v)/))([^\?&\"'>]+)~i", $media, $aYoutube ) ) {
      if( isset($aYoutube[5]) ) {
        $youtube = $aYoutube[5];
        $player_type = 'youtube';
      }
    } else if( preg_match( "~^[a-zA-Z0-9-_]{11}$~", $media, $aYoutube ) ) {
      if( isset($aYoutube[0]) ) {
        $youtube = $aYoutube[0];
        $player_type = 'youtube';
      }
    }

    if( preg_match( "~vimeo.com/(?:video/|moogaloop\.swf\?clip_id=)?(\d+)~i", $media, $aVimeo ) ) {
      if( isset($aVimeo[1]) ) {
        $vimeo = $aVimeo[1];
        $player_type = 'vimeo';
      }
    } else if( preg_match( "~^[0-9]{8}$~", $media, $aVimeo ) ) {
      if( isset($aVimeo[0]) ) {
        $vimeo = $aVimeo[0];
        $player_type = 'vimeo';
      }
    }
    
    //  https://account.wistia.com/medias/9km3qucr7g?embedType=async&videoFoam=true&videoWidth=1920
    if( preg_match( "~https?://\S*?\.wistia\.com/medias/([a-z0-9]+)~i", $media, $aWistia ) ) {
      $wistia = $aWistia[1];
      $player_type = 'wistia';
      
    //  http://fast.wistia.net/embed/iframe/avk9twrrbn
    } else if( preg_match( "~https?://\S*?\.wistia\.(?:com|net)/embed/(?:iframe|medias)/([a-z0-9]+)~i", $media, $aWistia ) ) {
      $wistia = $aWistia[1];
      $player_type = 'wistia';      
    }
    
    if( !isset($this->aCurArgs['liststyle']) || empty($this->aCurArgs['liststyle']) ){
      $this->aCurArgs['liststyle'] = $this->_get_option('liststyle');
    }
    
    if( get_query_var('fv_player_embed') && empty($_REQUEST['fv_player_preview']) && $this->aCurArgs['liststyle'] != 'tabs' && !in_array($this->aCurArgs['liststyle'], array( 'season', 'polaroid' ) ) ) { // force vertical playlist when using embed and not using tabs, nor season style and it's not a preview for editing
      $this->aCurArgs['liststyle'] = 'slider';
    }
    
    // if single video, force horizontal style (fix for FV Player Pro Video Ads)
    // TODO: Perhaps FV Player Pro Video Ads should deal with this instead
    if(
      // when using the FV Player DB it's possible to have a single video only
      // but then it might fill the playlist shortcode argument
      // this happens for FV Player Pro Vimeo Channel functionality
      $player && count($player->getVideos()) == 1 && empty($this->aCurArgs['playlist']) ||
      empty($this->aCurArgs['playlist']) 
    ) {
      $this->aCurArgs['liststyle'] = 'horizontal';
    }

    $aPlaylistItems = array();  //  todo: remove
    $aSplashScreens = array();
    $aCaptions = array();
    
    list( $playlist_items_external_html, $aPlaylistItems, $aSplashScreens, $aCaptions ) = $this->build_playlist( $this->aCurArgs, $media, $src1, $src2, $rtmp, $splash_img );    
    
    if( count($aPlaylistItems) == 1 && empty($this->aCurArgs['listshow']) ) {
      $playlist_items_external_html = false;
      $attributes[ !empty($this->aCurArgs['lazy']) ? 'data-item-lazy' : 'data-item' ] = $this->json_encode( apply_filters( 'fv_player_item', $aPlaylistItems[0], 0, $this->aCurArgs ) );
    }
    
    $this->aCurArgs = apply_filters( 'fv_flowplayer_args', $this->aCurArgs, $this->hash, $media, $aPlaylistItems );
    
    
    $player_type = apply_filters( 'fv_flowplayer_player_type', $player_type, $this->hash, $media, $aPlaylistItems, $this->aCurArgs );
    
    
    /*
     *  Allow plugins to create custom playlist styles
     */
    $res = apply_filters( 'fv_flowplayer_playlist_style', false, $this->aCurArgs, $aPlaylistItems, $aSplashScreens, $aCaptions );
    if( $res ) {
      return $res;
    }  
    

    /*
     * Playlist Start Position Splash Screen
     */
    global $fv_fp;

    if (isset($this->aCurArgs['playlist_start']) && $fv_fp && method_exists($fv_fp, 'current_player') && $fv_fp->current_player() && $fv_fp->current_player()->getVideos()) {
      foreach ($fv_fp->current_player()->getVideos() as $video_index => $video) {
        if ($video_index + 1 == $this->aCurArgs['playlist_start']) {
          $splash_img = $video->getSplash();
          break;
        }
      }
    }

    /*
     *  Video player tabs
     */
 
    if( $player_type == 'video'  && $this->aCurArgs['liststyle'] == 'tabs' && count($aPlaylistItems) > 1 ) {
      return $this->get_tabs($aPlaylistItems,$aSplashScreens,$aCaptions,$width);            
    }
    
    
    /*
     *  Autoplay, in the older FV Player versions this setting was just true/false and that creates a ton of issues
     */
    $autoplay = -1;
    if( $this->_get_option('autoplay') == 'true' && $this->aCurArgs['autoplay'] != 'false'  ) {
      $autoplay = 0;
    } else if ( $this->_get_option('autoplay') == 'muted' && $this->aCurArgs['autoplay'] != 'false' ) {
      $autoplay = 'muted';
    }
    
    if( isset($this->aCurArgs['autoplay']) && ($this->aCurArgs['autoplay'] == 'true' || $this->aCurArgs['autoplay'] == 'on')) {
      $autoplay = 0;
    }
    if( isset($this->aCurArgs['autoplay']) && ($this->aCurArgs['autoplay'] == 'muted')) {
      $autoplay = 'muted';
    }

    /*
     *  Sticky
     */
    $sticky = false;  //  todo: should be changed into a property
    if( $this->_get_option('sticky') && $this->aCurArgs['sticky'] != 'false'  ) {
      $sticky = true;
    }  
    if( isset($this->aCurArgs['sticky']) && ($this->aCurArgs['sticky'] == 'true' || $this->aCurArgs['sticky'] == 'on')) {
      $sticky = true;
    }    
    
    /*
     *  Video player
     */
    if( $player_type == 'video' ) {
        
        add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit' ), 999, 2 );
      
        if (!empty($media)) {
          $media = $this->get_video_url($media);
        }
        if (!empty($src1)) {
          $src1 = $this->get_video_url($src1);
        }
        if (!empty($src2)) {
          $src2 = $this->get_video_url($src2);
        }
        $mobile = ( isset($this->aCurArgs['mobile']) && !empty($this->aCurArgs['mobile']) ) ? trim($this->aCurArgs['mobile']) : false;  
        if (!empty($mobile)) {
          $mobile = $this->get_video_url($mobile);
        }      
      
        if( is_feed() ) {
          $this->ret['html'] = '<p class="fv-flowplayer-feed"><a href="'.get_permalink().'" title="'.__('Click to watch the video').'">'.apply_filters( 'fv_flowplayer_rss_intro_splash', __('[This post contains video, click to play]') );
          if( $splash_img ) {
            $this->ret['html'] .= '<br /><img src="'.$splash_img.'" width="400" />';
          }
          $this->ret['html'] .= '</a></p>';
          
          $this->ret['html'] = apply_filters( 'fv_flowplayer_rss', $this->ret['html'], $this );
          
          return $this->ret;
        }
        
        $bHTTPs = false;
        foreach( apply_filters( 'fv_player_media', array( $mobile, $media, $src1, $src2), $this ) AS $media_item ) {
          if( stripos($media_item,'https://') === 0 ) {
            $bHTTPs = true;
          }
        }
        
        if( !$bHTTPs && function_exists('is_amp_endpoint') && is_amp_endpoint() || count($aPlaylistItems) > 1 && function_exists('is_amp_endpoint') && is_amp_endpoint() ) {          
          $this->ret['html'] = '<p class="fv-flowplayer-feed"><a href="'.get_permalink().'" title="'.__('Click to watch the video').'">'.apply_filters( 'fv_flowplayer_rss_intro_splash', __('[This post contains advanced video player, click to open the original website]') );
          if( $splash_img ) {
            $this->ret['html'] .= '<br /><img src="'.$splash_img.'" width="400" />';
          }
          $this->ret['html'] .= '</a></p>';
          
          $this->ret['html'] = apply_filters( 'fv_flowplayer_amp_link', $this->ret['html'], $this );
          
          return $this->ret;
        
        } else if( function_exists('is_amp_endpoint') && is_amp_endpoint() ) {          
          $this->ret['html'] .= "\t".'<video controls';      
          if (isset($splash_img) && !empty($splash_img)) {
            $this->ret['html'] .= ' poster="'.flowplayer::get_encoded_url($splash_img).'"';
          } 
          
          if( $autoplay > -1 ) {
            $this->ret['html'] .= ' autoplay';  
          }
          
          if( stripos($width,'%') == false && intval($width) > 0 ) {
            $this->ret['html'] .= ' width="'.$width.'"'; 
          }
          if( stripos($height,'%') == false && intval($height) > 0 ) {
            $this->ret['html'] .= ' height="'.$height.'"';
          }
          
          $this->ret['html'] .= ">\n";
          
          if (!empty($mobile)) {
            $src = $this->get_video_src($mobile);
            $this->ret['html'] .= '<source src="'.esc_attr($src).'" type="'.$this->get_mime_type($src).'" />';
          } else {
             foreach( apply_filters( 'fv_player_media', array($media, $src1, $src2), $this ) AS $media_item ) {
               $src = $this->get_video_src($media_item);
               $this->ret['html'] .= '<source src="'.esc_attr($src).'" type="'.$this->get_mime_type($src).'" />';
            }
          }
          
          $this->ret['html'] .= "\t".'</video>';
          
          $this->ret['html'] = apply_filters( 'fv_flowplayer_amp', $this->ret['html'], $this );
          
          return $this->ret;
        }    
    
        foreach( array( $media, $src1, $src2 ) AS $media_item ) {
          //if( ( strpos($media_item, 'amazonaws.com') !== false && stripos( $media_item, 'http://s3.amazonaws.com/' ) !== 0 && stripos( $media_item, 'https://s3.amazonaws.com/' ) !== 0  ) || stripos( $media_item, 'rtmp://' ) === 0 ) {  //  we are also checking amazonaws.com due to compatibility with older shortcodes
          
          if( !$this->_get_option('engine') && stripos( $media_item, '.m4v' ) !== false ) {
            $this->ret['script']['fv_flowplayer_browser_ff_m4v'][$this->hash] = true;
          }
          
        }
        
        $popup = '';
        
        $aSubtitles = $this->get_subtitles();
      
        if(
          // new, DB playlist code
          (!empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'splashend')
        ||
          // compatibility fallback for classic (non-DB) shortcode
          (isset($this->aCurArgs['splashend']) && $this->aCurArgs['splashend'] == 'show' && isset($this->aCurArgs['splash']) && !empty($this->aCurArgs['splash']))
        ) {
          $splashend_contents = '<div id="wpfp_'.$this->hash.'_custom_background" class="wpfp_custom_background" style="position: absolute; background: url(\''.$splash_img.'\') no-repeat center center; background-size: contain; width: 100%; height: 100%; z-index: 1;"></div>';
        }
  
        // should the player appear as audio player?
        $bIsAudio = false;
        if( preg_match( '~\.(mp3|wav|ogg)([?#].*?)?$~', $media ) ) {
          $bIsAudio = true;
        } else  if( $video = $this->current_video() ) {          
          $bIsAudio = $video->getMetaValue('audio',true);
        }
        
        // if there is splash and it's different from the site-wide default splash
        if( !empty($splash_img) && strcmp( $splash_img, $this->_get_option('splash') ) != 0 ) {
          $bIsAudio = false;
        }
        
        $attributes['class'] = 'flowplayer no-brand is-splash';
        
        if( !empty($this->aCurArgs['skin']) ) {
          $skin = 'skin-'.$this->aCurArgs['skin'];
        } else {
          $skin = 'skin-'.$this->_get_option('skin');
        }
        $attributes['class'] .= ' no-svg is-paused '.$skin;
        $timeline_class = $this->_get_option(array($skin, 'design-timeline'));
        if( $bIsAudio && $timeline_class == 'fp-minimal' ) {
          $timeline_class = 'fp-slim';
        }
        $attributes['class'] .= ' '.$timeline_class.' '.$this->_get_option(array($skin, 'design-icons'));
        
        if( $this->_get_option(array($skin, 'bottom-fs')) ) {
          $attributes['class'] .= ' bottom-fs';
        }
        
        if( !empty($this->aCurArgs['playlist']) ) {
          $attributes['class'] .= ' has-playlist has-playlist-'.$this->aCurArgs['liststyle'];
        }
      
        
        if( $autoplay != -1 ) {
          $attributes['data-fvautoplay'] = $autoplay;
        } 

        if( $sticky ) {
          $attributes['data-fvsticky'] = 'true';
        }
        
        if( !empty($this->aCurArgs['splash_text']) ) {
          $attributes['class'] .= ' has-splash-text';
        }

        if( isset($this->aCurArgs['playlist_hide']) && strcmp($this->aCurArgs['playlist_hide'],'true') == 0 ) {
          $attributes['class'] .= ' playlist-hidden';
        }
                
        if( $bIsAudio ) {
          $attributes['class'] .= ' is-audio fixed-controls is-mouseover';
        }
        
        //  Fixed control bar
        $bFixedControlbar = $this->_get_option('show_controlbar');
        if( isset($this->aCurArgs['controlbar']) ) {
          if( strcmp($this->aCurArgs['controlbar'],'yes') == 0 || strcmp($this->aCurArgs['controlbar'],'show') == 0 ) {
            $bFixedControlbar = true;
          } else if( strcmp($this->aCurArgs['controlbar'],'no') == 0 ) {
            $attributes['class'] .= ' no-controlbar';
            $bFixedControlbar = false;
          }
        }
        if( $bFixedControlbar ) {
          $attributes['class'] .= ' fixed-controls';
        }
        
        $attributes = $this->get_button_data( $attributes, 'no_picture', $this->aCurArgs );
        
        $attributes = $this->get_button_data( $attributes, 'repeat', $this->aCurArgs );
        
        $attributes = $this->get_button_data( $attributes, 'rewind', $this->aCurArgs );
        
        if( !empty($this->aCurArgs['fsforce']) ) {
          $attributes['data-fsforce'] = $this->aCurArgs['fsforce'];
        }
        
        //  Align
        $attributes['class'] .= $this->get_align();
        
        if( $this->_get_option('engine') || $this->aCurArgs['engine'] == 'flash' ) {
          $attributes['data-engine'] = 'flash';
        }

        if( $this->aCurArgs['embed'] == 'false' || $this->aCurArgs['embed'] == 'off' || ( $this->_get_option('disableembedding') && $this->aCurArgs['embed'] != 'true' ) ) {

        } else {
          $attributes['data-fv-embed'] = $this->get_embed_url();
        }

        if( isset($this->aCurArgs['logo']) && $this->aCurArgs['logo'] ) {
          $attributes['data-logo'] = ( strcmp($this->aCurArgs['logo'],'none') == 0 ) ? '' : $this->aCurArgs['logo'];
        }
        
        $attributes['style'] = '';
        if( !empty($this->aCurArgs['playlist']) && in_array( $this->aCurArgs['liststyle'], array('horizontal','slider','vertical','prevnext') ) ) {
          $attributes['style'] .= 'max-width: 100%; ';
        } else if( !$bIsAudio ) {
          if( intval($width) == 0 ) $width = '100%';
          if( intval($height) == 0 ) $height = '100%';
          $cssWidth = stripos($width,'%') !== false ? $width : $width . 'px';
          $cssHeight = stripos($height,'%') !== false ? $height : $height. 'px';          
          if( $this->_get_option('fixed_size') ) {
            $attributes['style'] .= 'width: ' . $cssWidth . '; height: ' . $cssHeight . '; ';
          } else {
            $attributes['style'] .= 'max-width: ' . $cssWidth . '; max-height: ' . $cssHeight . '; ';
          }
        }
        
        list( $rtmp_server, $rtmp ) = $this->get_rtmp_server($rtmp);        
        if( /*count($aPlaylistItems) == 0 &&*/ $rtmp_server) {
          $attributes['data-rtmp'] = $rtmp_server;
        }

        if( !$this->_get_option('allowfullscreen') || isset($this->aCurArgs['fullscreen']) && $this->aCurArgs['fullscreen'] == 'false' ) {
          $attributes['data-fullscreen'] = 'false';
        }
        
        if( !$bIsAudio && stripos($width,'%') == false && intval($width) > 0 && stripos($height,'%') == false && intval($height) > 0 ) {
          $ratio = round($height / $width, 4);   
          $this->fRatio = $ratio;
  
          $attributes['data-ratio'] = str_replace(',','.',$ratio);
        }
        
        if( isset($this->aCurArgs['live']) && $this->aCurArgs['live'] == 'true' ) {
          $attributes['data-live'] = 'true';
        }
        
        if( isset($this->aCurArgs['dvr']) && $this->aCurArgs['dvr'] == 'true' ) {
          $attributes['data-dvr'] = 'true';
        }

        if( isset($this->aCurArgs['hd_streaming']) ) {
          $attributes['data-hd_streaming'] = $this->aCurArgs['hd_streaming'];
        }

        if( isset($this->aCurArgs['volume']) ) {
          $attributes['data-volume'] = floatval($this->aCurArgs['volume']);
          $attributes['class'] .= ' no-volume';
        }

        $playlist = '';
        $is_preroll = false;
        if( isset($playlist_items_external_html) ) {
          
          if( $bIsAudio ) {
            $playlist_items_external_html = str_replace( 'class="fp-playlist-external', 'class="fp-playlist-external is-audio', $playlist_items_external_html );
          }
          
          if( $this->aCurArgs['liststyle'] == 'prevnext' || ( isset($this->aCurArgs['playlist_hide']) && $this->aCurArgs['playlist_hide']== 'true' ) ) {
            $playlist_items_external_html = str_replace( 'class="fp-playlist-external', 'style="display: none" class="fp-playlist-external', $playlist_items_external_html );
          }
          
          if( count($aPlaylistItems) == 1 && $this->get_title() && empty($this->aCurArgs['listshow']) && empty($this->aCurArgs['lightbox']) ) {
            $attributes['class'] .= ' has-caption';
            $this->sHTMLAfter .= apply_filters( 'fv_player_caption', "<p class='fp-caption'>".$this->get_title()."</p>", $this );
          }
          $this->sHTMLAfter .= $playlist_items_external_html;
          
        } else if( $this->get_title() && empty($this->aCurArgs['lightbox']) ) {
          $attributes['class'] .= ' has-caption';
          $this->sHTMLAfter = apply_filters( 'fv_player_caption', "<p class='fp-caption'>".$this->get_title()."</p>", $this );
          
        }
        
        if( !empty($this->aCurArgs['chapters']) ) {
          $attributes['class'] .= ' has-chapters';
        }
         if( !empty($this->aCurArgs['transcript']) ) {
          $attributes['class'] .= ' has-transcript';
        }

        if( get_query_var('fv_player_embed') ) {  //  this is needed for iframe embedding only
          $attributes['class'] .= ' fp-is-embed';
        }
        if( !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'redirect' ) {
          $attributes['data-fv_redirect'] = trim($this->aCurArgs['end_action_value']);
        } else if( !empty($this->aCurArgs['redirect']) ) {
          // compatibility fallback for classic (non-DB) shortcode
          $attributes['data-fv_redirect'] = trim($this->aCurArgs['redirect']);
        }

        if( isset($this->aCurArgs['admin_warning']) ) {
          $this->sHTMLAfter .= wpautop($this->aCurArgs['admin_warning']);
        }

        if( isset($this->aCurArgs['playlist_start']) ) {
          $attributes['data-playlist_start'] = $this->aCurArgs['playlist_start'];
        }

        if( $this->_get_option('ad_show_after') ) {
          $attributes['data-ad_show_after'] = $this->_get_option('ad_show_after');
        }
        if( count($aPlaylistItems) ) {
          if( isset($this->aCurArgs['playlist_advance']) && ($this->aCurArgs['playlist_advance'] === 'false' || $this->aCurArgs['playlist_advance'] === 'off') ){
            $attributes['data-advance'] = 'false';
          } elseif (empty($this->aCurArgs['playlist_advance']) ) {
            if( $this->_get_option('playlist_advance') ) {
              $attributes['data-advance'] = 'false';
            }
          }
        }

        if(
          !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'loop' ||
          // compatibility fallback for classic (non-DB) shortcode
          isset($this->aCurArgs['loop']) && $this->aCurArgs['loop'] == 'true'
        ) {
          $attributes['data-loop'] = true;
          unset($attributes['data-advance']); // loop won't work if auto advance is disabled
        }
                

        if( $popup_contents = $this->get_popup_code() ) {
          $attributes['data-popup'] = $this->json_encode( $popup_contents );
        }

        if( $ad_contents = $this->get_ad_code() ) {
          $attributes['data-ad'] = $this->json_encode( $ad_contents );
        }
        
        add_filter( 'fv_flowplayer_attributes', array( $this, 'get_speed_attribute' ) );
        
        $attributes_html = '';
        $attributes = apply_filters( 'fv_flowplayer_attributes', $attributes, $media, $this );
        foreach( $attributes AS $attr_key => $attr_value ) {
          $attributes_html .= ' '.$attr_key.'="'.esc_attr( $attr_value ).'"';
        }
        
        $this->ret['html'] .= '<div id="wpfp_' . $this->hash . '"'.$attributes_html.'>'."\n";

        if( isset($this->fRatio) ) {
          $this->ret['html'] .= "\t".'<div class="fp-ratio" style="padding-top: '.str_replace(',','.',$this->fRatio * 100).'%"></div>'."\n";
        }
        
        if( !$bIsAudio && !empty($splash_img) ) {
          $alt = $this->get_title() ? $this->get_title() : 'video';
          
           // load the image from WP Media Library if you got a number
          if( is_numeric($splash_img) ) {
            $image = wp_get_attachment_image($splash_img, 'full', false, array('class' => 'fp-splash', 'fv_sizes' => '25vw, 50vw, 100vw') );
          } else {
            $image = '<img class="fp-splash" alt="'.esc_attr($alt).'" src="'.esc_attr($splash_img).'" />';
          }
          
          $this->ret['html'] .= "\t".$image."\n"; 
        }
        
        if( !$bIsAudio ) {
          $this->ret['html'] .= "\t".'<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>'."\n";
        }
        
        $this->ret['html'] .= $this->get_buttons();
        
        if( isset($splashend_contents) ) {
          $this->ret['html'] .= $splashend_contents;
        }
        
        if( flowplayer::is_special_editor() ) {
          $this->ret['html'] .= '<div class="fp-ui"></div>';       
        } else if( current_user_can('manage_options') && empty($this->aCurArgs['lazy']) && empty($this->aCurArgs['lightbox']) ) {
          $this->ret['html'] .= '<div id="wpfp_'.$this->hash.'_admin_error" class="fvfp_admin_error"><div class="fvfp_admin_error_content"><h4>Admin JavaScript warning:</h4><p>I\'m sorry, your JavaScript appears to be broken. Please use "Check template" in plugin settings, read our <a href="https://foliovision.com/player/installation#fixing-broken-javascript" target="_blank">troubleshooting guide</a>, <a href="https://foliovision.com/troubleshooting-javascript-errors" target="_blank">troubleshooting guide for programmers</a> or <a href="https://foliovision.com/pro-support" target="_blank">order our pro support</a> and we will get it fixed for you.</p></div></div>';       
        }
        
        $this->ret['html'] .= apply_filters( 'fv_flowplayer_inner_html', null, $this );
        
        if( !$bIsAudio ) {
          $this->ret['html'] .= $this->get_sharing_html()."\n";
        }
        
        if( !empty($this->aCurArgs['splash_text']) ) {
          $aSplashText = explode( ';', $this->aCurArgs['splash_text'] );         
          $this->ret['html'] .= "<div class='fv-fp-splash-text'><span class='custom-play-button'>".$aSplashText[0]."</span></div>\n"; //  needed for soap customizations of play button!
        }

        if( empty($this->aCurArgs['checker']) && !$this->_get_option('disable_videochecker') && current_user_can('manage_options') ) {
          $this->ret['html'] .= $this->get_video_checker_html()."\n";
        }
        
        if ($this->aCurArgs['liststyle'] == 'prevnext' && count($aPlaylistItems) > 1 ) {
          $this->ret['html'].='<a class="fp-prev" title="prev"></a><a class="fp-next" title="next"></a>'; 
        }          
        
        $this->ret['html'] .= '</div>'."\n";
        
        $this->ret['html'] .= $this->sHTMLAfter.$scripts_after;
        
        //  change engine for IE9 and 10
        if( $this->aCurArgs['engine'] == 'false' ) {
          $this->ret['script']['fv_flowplayer_browser_ie'][$this->hash] = true;
        }        
        
    } //  end Video player
    
    
    /*
     *  Youtube player
     */
    else if( $player_type == 'youtube' ) {
        
      $sAutoplay = ($autoplay > -1) ? 'autoplay=1&amp;' : '';
      $this->ret['html'] .= "<iframe id='fv_ytplayer_{$this->hash}' type='text/html' width='{$width}' height='{$height}'
    src='//www.youtube.com/embed/$youtube?{$sAutoplay}origin=".urlencode(get_permalink())."' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>\n";
      
    }
    
    
    /*
     *  Vimeo player
     */
    else if( $player_type == 'vimeo' ) {
    
      $sAutoplay = ($autoplay > -1) ? " autoplay='1'" : "";
      $this->ret['html'] .= "<iframe id='fv_vimeo_{$this->hash}' src='//player.vimeo.com/video/{$vimeo}' width='{$width}' height='{$height}' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen{$sAutoplay}></iframe>\n";
      
    }
    
    
    /*
     *  Wistia player
     */
    else if( $player_type == 'wistia' ) {
      $ratio = $width > 0 ? ' data-ratio="'.($height/$width).'"' : '';
      $this->ret['html'] .= "</script><script src='//fast.wistia.com/assets/external/E-v1.js' async></script><div class='wistia_embed wistia_async_{$wistia}' style='height:{$height}px;max-width:{$width}px'".$ratio.">&nbsp;</div>\n";
      
    }
    
    
    if( isset($this->aCurArgs['liststyle']) && in_array($this->aCurArgs['liststyle'], array('vertical','text') ) && count($aPlaylistItems) > 1 ){
      $this->ret['html'] = '<div class="fp-playlist-'.$this->aCurArgs['liststyle'].'-wrapper">'.$this->ret['html'].'</div>';
    }
    $this->ret['html'] = apply_filters( 'fv_flowplayer_html', $this->ret['html'], $this );

    if( get_query_var('fv_player_embed') ) {  //  this is needed for iframe embedding only
      $this->ret['html'] .= "<!--fv player end-->";
    }    
    
    $this->ret['script'] = apply_filters( 'fv_flowplayer_scripts_array', $this->ret['script'], 'wpfp_' . $this->hash, $media );
      
      return $this->ret;
  }
  
  
  function get_ad_code() {
    $ad_contents = false;
    
    if(
      ( trim($this->_get_option('ad')) || ( isset($this->aCurArgs['ad']) && !empty($this->aCurArgs['ad']) ) ) 
      && !strlen($this->aCurArgs['ad_skip'])        
    ) {
      if (isset($this->aCurArgs['ad']) && !empty($this->aCurArgs['ad'])) {
        $ad = trim($this->aCurArgs['ad']);
        if( stripos($ad,'<!--fv_flowplayer_base64_encoded-->') !== false ) {
          $ad = str_replace('<!--fv_flowplayer_base64_encoded-->','',$ad);
          $ad = html_entity_decode( str_replace( array('\"','\[','\]'), array('"','[',']'), base64_decode($ad) ) );
        } else {
          $ad = html_entity_decode( str_replace('&#039;',"'",$ad ) );
        }

        $ad_width = ( isset($this->aCurArgs['ad_width']) && intval($this->aCurArgs['ad_width']) > 0 ) ? intval($this->aCurArgs['ad_width']).'px' : '100%';  
        $ad_height = ( isset($this->aCurArgs['ad_height']) && intval($this->aCurArgs['ad_height']) > 0 ) ? intval($this->aCurArgs['ad_height']).'px' : '';          
      }
      else {
        $ad = trim( $this->_get_option('ad') );      
        $ad_width = ( $this->_get_option('ad_width') ) ? $this->_get_option('ad_width').'px' : '100%';  
        $ad_height = ( $this->_get_option('ad_height') ) ? $this->_get_option('ad_height').'px' : '';
      }
      
      $ad = apply_filters( 'fv_flowplayer_ad_html', $ad);
      if( strlen(trim($ad)) > 0 ) {      
        $ad_contents = array(
                             'html' => "<div class='wpfp_custom_ad_content' style='width: $ad_width; height: $ad_height'>\n\t\t<div class='fv_fp_close'><a href='#'></a></div>\n\t\t\t".$ad."\n\t\t</div>",
                             'width' => $ad_width,
                             'height' => $ad_height
                            );                 
      }
    }
    
    return $ad_contents;
  }
  
  
  function get_align() {
    $sClass = false;
    if( isset($this->aCurArgs['align']) && ( empty($this->aCurArgs['liststyle']) || $this->aCurArgs['liststyle'] != 'vertical' ) ) {
      if( $this->aCurArgs['align'] == 'left' ) {
        $sClass .= ' alignleft';
      } else if( $this->aCurArgs['align'] == 'right' ) {
        $sClass .= ' alignright';
      } else if( $this->aCurArgs['align'] == 'center' ) {
        $sClass .= ' aligncenter';
      } 
    }
    return $sClass;
  }
  
    
  function get_buttons() {
    $sHTML = false;
    foreach( array('left','center','right','controlbar') AS $key ) {
      $aButtons = apply_filters( 'fv_flowplayer_buttons_'.$key, array() );
      if( !$aButtons || !count($aButtons) ) continue;

      $sButtons = implode( '', $aButtons );
      $sHTML .= "<div class='fv-player-buttons fv-player-buttons-$key'>$sButtons</div>";
    }
    if( $sHTML ) {
      $sHTML = "<div class='fv-player-buttons-wrap'>$sHTML</div>";
    }
    
    return $sHTML;
  }
  
  
  function get_button_data( $attributes, $type, $args ) {
    $show = $this->_get_option('ui_'.$type.'_button');
    if( isset($args[$type.'_button']) ) {
      if( strcmp($args[$type.'_button'],'yes') == 0 ) {
        $show = true;
      } else if( strcmp($args[$type.'_button'],'no') == 0 ) {
        $show = false;
      }
    }
    if( $show ) {
      $attributes['data-button-'.$type] = true;
      
      if( $type == 'rewind' ) {
        add_action( 'wp_footer', 'fv_player_footer_svg_rewind', 101 );
      } else if( $type == 'repeat' || $type == 'no_picture' ) {
        add_action( 'wp_footer', 'fv_player_footer_svg_playlist', 101 );
      }
      
    }    
    
    return $attributes;
  }
  
  
  function get_embed_url() {
    if( empty($this->aPlayers[get_the_ID()]) ) {
      $num = $this->aPlayers[get_the_ID()] = 1;
    } else {
      $num = ++$this->aPlayers[get_the_ID()];
    }
    
    $append = 'fvp';
    if( $num > 1 ) {
      $append .= $num;
    }
    
    if( $player = $this->current_player() ) {
      $append = 'fvp-'.$player->getId();
      $num = $append;
    }
    
    $rewrite = get_option('rewrite_rules');
    if( empty($rewrite) ) {
      return add_query_arg( 'fv_player_embed', $num, get_permalink() );
    } else {
      return user_trailingslashit( trailingslashit( get_permalink() ).$append );
    }
  }
  
  
  function get_popup_code() {
    if( !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'no') {
      return false;
    }

    // static and e-mail popups share the same parameter in old non-DB shortcode
    $is_static_popup = (!empty($this->aCurArgs['popup']) || !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'popup');
    $is_email_popup = (!empty($this->aCurArgs['popup']) || !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'email_list');

    if( !empty($this->aCurArgs['end_actions']) && ($is_static_popup || $is_email_popup) ) {
      if ($is_static_popup) {
        $popup = (!empty($this->aCurArgs['popup']) ? trim($this->aCurArgs['popup']) : trim( $this->aCurArgs['end_action_value'] ));
      } else if ($is_email_popup) {
        $popup = 'email-'.trim( $this->aCurArgs['end_action_value'] );
      }
    } else if (!empty($this->aCurArgs['popup'])) {
      $popup = trim($this->aCurArgs['popup']);
    } else {
      $popup = $this->_get_option('popups_default');
    }
    if (stripos($popup, '<!--fv_flowplayer_base64_encoded-->') !== false) {
      $popup = str_replace('<!--fv_flowplayer_base64_encoded-->', '', $popup);
      $popup = html_entity_decode(str_replace(array('\"', '\[', '\]'), array('"', '[', ']'), base64_decode($popup)));
    } else {
      $popup = html_entity_decode(str_replace('&#039;', "'", $popup));
    }

    if ($popup === 'no') {
      return false;
    }

    $iPopupIndex = 1;
    if ($popup === 'random' || is_numeric($popup)) { // we don't get there if it's email-1 or direct HTML
      $aPopupData = get_option('fv_player_popups');
      if ($popup === 'random') {
        $iPopupIndex = rand(1, count($aPopupData));
      } elseif (is_numeric($popup)) {
        $iPopupIndex = intval($popup);
      }

      if (isset($aPopupData[$iPopupIndex])) {
        $popup = $aPopupData[$iPopupIndex]['html'];
      } else {
        return false;
      }
    }

    $sClass = ' fv_player_popup-' . $iPopupIndex;

    $popup = apply_filters('fv_flowplayer_popup_html', $popup);

    if (strlen(trim($popup)) > 0) {
      $popup_contents = array(
          'html' => '<div class="fv_player_popup' . $sClass . ' wpfp_custom_popup_content">' . $popup . '</div>',
          'pause' => isset($aPopupData) && isset($aPopupData[$iPopupIndex]['pause']) ? $aPopupData[$iPopupIndex]['pause'] : false
      );
      return $popup_contents;
    }

    return false;
  }

  function get_rtmp_server($rtmp) {
    $rtmp_server = false;
    if( !empty($this->aCurArgs['rtmp']) ) {
      $rtmp_server = trim( $this->aCurArgs['rtmp'] );
    } else if( isset($rtmp) && stripos( $rtmp, 'rtmp://' ) === 0 && stripos($this->_get_option('rtmp'), $rtmp ) === false  ) {
      if( preg_match( '~/([a-zA-Z0-9]+)?:~', $rtmp ) ) {
        $aTMP = preg_split( '~/([a-zA-Z0-9]+)?:~', $rtmp, -1, PREG_SPLIT_DELIM_CAPTURE );
        $rtmp_server = $aTMP[0];
      } else {
        $rtmp_info = parse_url($rtmp);
        if( isset($rtmp_info['host']) && strlen(trim($rtmp_info['host']) ) > 0 ) {
          $rtmp_server = 'rtmp://'.$rtmp_info['host'].'/cfx/st';
        }
      }
    } else if( $this->_get_option('rtmp') ) {
      $rtmp_server = $this->_get_option('rtmp');
      if( stripos( $rtmp_server, 'rtmp://' ) === 0 ) {        
        $rtmp = str_replace( $rtmp_server, '', $rtmp );
      } else {
        $rtmp_server = 'rtmp://' . $rtmp_server . '/cfx/st/';
      }
    }
    return array( $rtmp_server, $rtmp );
  }
  
  
  function get_speed_attribute( $attributes ) {
    $bShow = false;
    if( $this->_get_option('ui_speed') || isset($this->aCurArgs['speed']) && ( $this->aCurArgs['speed'] == 'buttons' || $this->aCurArgs['speed'] == 'yes' ) ) {
      $bShow = true;
    }
    
    if( isset($this->aCurArgs['speed']) && $this->aCurArgs['speed'] == 'no' ) {
      $bShow = false;
    }
    
    if( $bShow ) {
      $attributes['data-speedb'] = true;
    }
    
    return $attributes;
  }
  
  
  function get_splash() {
    $splash_img = false;
    if (isset($this->aCurArgs['splash']) && !empty($this->aCurArgs['splash'])) {
      $splash_img = $this->aCurArgs['splash'];
      
      if( !is_numeric($splash_img) && strpos($splash_img,'http://') !== 0 && strpos($splash_img,'https://') !== 0 && strpos($splash_img,'//') !== 0 ) {
        $http = is_ssl() ? 'https://' : 'http://';
        
        //$splash_img = VIDEO_PATH.trim($this->aCurArgs['splash']);
        if($splash_img[0]=='/') $splash_img = substr($splash_img, 1);
          if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$splash_img))){  //if the site does not live in the document root
            $splash_img = $http.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$splash_img;
          }
          else
          if(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$splash_img)){ // if the videos folder is in the root
            $splash_img = $http.$_SERVER['SERVER_NAME'].VIDEO_DIR.$splash_img;//VIDEO_PATH.$media;
          }
          else {
            //if the videos are not in the videos directory but they are adressed relatively
            $splash_img_path = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$splash_img);
            $splash_img = $http.$splash_img_path;
          }
      }
      else {
        $splash_img = trim($this->aCurArgs['splash']);
      }            
    } else if( $this->_get_option('splash') ) {
      $splash_img = $this->_get_option('splash');
    }    

    $splash_img = apply_filters( 'fv_flowplayer_splash', $splash_img, !empty($this->aCurArgs['src']) ? $this->aCurArgs['src'] : false );

    return $splash_img;
  }

  function get_subtitles_url($protocol, $subtitles) {
    if( strpos($subtitles,'http://') === false && strpos($subtitles,'https://') === false ) {
      //$splash_img = VIDEO_PATH.trim($args['splash']);
      if($subtitles[0]=='/') $subtitles = substr($subtitles, 1);
      if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$subtitles))){  //if the site does not live in the document root
        $subtitles = $protocol.'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$subtitles;
      }
      else
        if(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$subtitles)){ // if the videos folder is in the root
          $subtitles = $protocol.'://'.$_SERVER['SERVER_NAME'].VIDEO_DIR.$subtitles;//VIDEO_PATH.$media;
        }
        else {
          //if the videos are not in the videos directory but they are adressed relatively
          $subtitles = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$subtitles);
          $subtitles = $protocol.'://'.$subtitles;
        }
    }
    else {
      $subtitles = trim($subtitles);
    }

    $subtitles = apply_filters( 'fv_flowplayer_resource', $subtitles );

    return $subtitles;
  }

  function get_subtitles($index = 0) {
    global $fv_fp;

    $aSubtitles = array();
    $args = $this->aCurArgs;
    $protocol = is_ssl() ? 'https' : 'http';

    // each video can have subtitles in any language with new DB-based shortcodes
    if ($this->current_video()) {
      if ($this->current_video()->getMetaData()) {
        foreach ($this->current_video()->getMetaData() as $meta_object) {
          if (strpos($meta_object->getMetaKey(), 'subtitles') !== false) {
            // subtitles meta data found, create URL from it
            // note: we ignore $index here, as it's used to determine an index
            //       for a single subtitle file from all subtitles set for the whole
            //       playlist, which was the old way of doing stuff
            $aSubtitles[str_replace( 'subtitles_', '', $meta_object->getMetaKey() )] = $this->get_subtitles_url($protocol, $meta_object->getMetaValue());
          }
        }
      }
    } else {
      if( $args && count($args) > 0 ) {
        foreach( $args AS $key => $subtitles ) {
          if( stripos($key,'subtitles') !== 0 || empty($subtitles) ) {
            continue;
          }

          $subtitles = explode( ";",$subtitles);
          if( empty($subtitles[$index]) ) continue;

          $aSubtitles[str_replace( 'subtitles_', '', $key )] = $this->get_subtitles_url($protocol, $subtitles[$index]);
        }
      }
    }

    return $aSubtitles;
  }
  
  function get_tabs($aPlaylistItems,$aSplashScreens,$aCaptions,$width) {
    global $post;
    
    if( intval($width) == 0 ) $width = '100%';
    $cssWidth = stripos($width,'%') !== false ? $width : $width . 'px';
    
    $this->count_tabs++;
    $output = new stdClass;
    $output->ret = array();
    $output->ret['html'] = '<script>document.body.className += " fv_flowplayer_tabs_hide";</script><div class="fv_flowplayer_tabs tabs woocommerce-tabs" style="max-width: '.$cssWidth.'"><div id="tabs-'.$post->ID.'-'.$this->count_tabs.'" class="fv_flowplayer_tabs_content">';
    $output->ret['script'] = array();
    
    $output->ret['html'] .= '<ul>';
    foreach( $aPlaylistItems AS $key => $aSrc ) {
      $sCaption = !empty($aCaptions[$key]) ? $aCaptions[$key] : $key;
      $output->ret['html'] .= '<li><a href="#tabs-'.$post->ID.'-'.$this->count_tabs.'-'.$key.'">'.$sCaption.'</a></li>';
    }
    $output->ret['html'] .= '</ul><div class="fv_flowplayer_tabs_cl"></div>';

    $aStartend = !empty($this->aCurArgs['startend']) ? explode(";",$this->aCurArgs['startend']) : array();  //  todo: somehow move to Pro?
    
    foreach( $aPlaylistItems AS $key => $aSrc ) {
      if( !empty($aStartend[$key]) ) $this->aCurArgs['startend'] = $aStartend[$key];
      
      unset($this->aCurArgs['id']);
      unset($this->aCurArgs['playlist']);
      $this->aCurArgs['src'] = $aSrc['sources'][0]['src'];  //  todo: remaining sources!
      
      $this->aCurArgs['splash'] = isset($aSplashScreens[$key])?$aSplashScreens[$key]:'';
      unset($this->aCurArgs['caption']);
      unset($this->aCurArgs['title']);
      $this->aCurArgs['liststyle']='none';
      
      $aPlayer = $this->build_min_player( $this->aCurArgs['src'],$this->aCurArgs );
      $sClass = $key == 0 ? ' class="fv_flowplayer_tabs_first"' : '';
      $output->ret['html'] .= '<div id="tabs-'.$post->ID.'-'.$this->count_tabs.'-'.$key.'"'.$sClass.'>'.$aPlayer['html'].'</div>';
      foreach( $aPlayer['script'] AS $key => $value ) {
        $output->ret['script'][$key] = array_merge( isset($output->ret['script'][$key]) ? $output->ret['script'][$key] : array(), $aPlayer['script'][$key] );
      }
    }
    $output->ret['html'] .= '<div class="fv_flowplayer_tabs_cl"></div><div class="fv_flowplayer_tabs_cr"></div></div></div>';
          
    $this->load_tabs = true;
          
    return $output->ret;    
  }
  
  
  function get_sharing_html() {
    global $post;
    
    $sSharingText = $this->_get_option('sharing_email_text' );
    $bVideoLink = empty($this->aCurArgs['linking']) ? !$this->_get_option('disable_video_hash_links' ) : $this->aCurArgs['linking'] === 'true';
    
    if( isset($this->aCurArgs['share']) && $this->aCurArgs['share'] ) { 
      $aSharing = explode( ';', $this->aCurArgs['share'] );
      if( count($aSharing) == 2 ) {
        $sPermalink = urlencode($aSharing[1]);
        $sMail = rawurlencode( apply_filters( 'fv_player_sharing_mail_content',$sSharingText.': '.$aSharing[1] ) );
        $sTitle = urlencode( $aSharing[0].' ');
        $bVideoLink = false;
      } else if( count($aSharing) == 1 && $this->aCurArgs['share'] != 'yes' && $this->aCurArgs['share'] != 'no' ) {
        $sPermalink = urlencode($aSharing[0]);
        $sMail = rawurlencode( apply_filters( 'fv_player_sharing_mail_content', $sSharingText.': '.$aSharing[0] ) );
        $sTitle = urlencode( get_bloginfo().' ');
        $bVideoLink = false;
      }
    }
    
    $sLink = get_permalink();
    if( !isset($sPermalink) || empty($sPermalink) ) {       
      $sPermalink = urlencode(get_permalink());
      $sMail = rawurlencode( apply_filters( 'fv_player_sharing_mail_content', $sSharingText.': '.get_permalink() ) );
      $sTitle = urlencode( html_entity_decode( is_singular() ? get_the_title().' ' : get_bloginfo() ).' ');
    }

          
    $sHTMLSharing = '<ul class="fvp-sharing">
    <li><a class="sharing-facebook" href="https://www.facebook.com/sharer/sharer.php?u=' . $sPermalink . '" target="_blank"></a></li>
    <li><a class="sharing-twitter" href="https://twitter.com/intent/tweet?text=' . $sTitle .'&url='. $sPermalink . '" target="_blank"></a></li>
    <li><a class="sharing-email" href="mailto:?body=' . $sMail . '" target="_blank"></a></li></ul>';
    
    if( isset($post) && isset($post->ID) ) {
      $sHTMLVideoLink = $bVideoLink ? '<div><a class="sharing-link" href="' . $sLink . '" target="_blank">Link</a></div>' : '';
    } else {
      $sHTMLVideoLink = false;
    }
    
    if( $this->aCurArgs['embed'] == 'false' || $this->aCurArgs['embed'] == 'off' ) {
      $sHTMLVideoLink = false;
    }

    $sHTMLEmbed = '<div><label><a class="embed-code-toggle" href="#"><strong>Embed</strong></a></label></div><div class="embed-code"><label>Copy and paste this HTML code into your webpage to embed.</label><textarea></textarea></div>';


    if( $this->aCurArgs['embed'] == 'false' || $this->aCurArgs['embed'] == 'off' || ( $this->_get_option('disableembedding') && $this->aCurArgs['embed'] != 'true' ) ) {
      $sHTMLEmbed = '';
    }
    
    if( isset($this->aCurArgs['share']) && $this->aCurArgs['share'] == 'no' ) {
      $sHTMLSharing = '';
    } else if( isset($this->aCurArgs['share']) && $this->aCurArgs['share'] && $this->aCurArgs['share'] != 'no' ) {
      
    } else if( $this->_get_option('disablesharing') ) {
      $sHTMLSharing = '';
    }

    $sHTML = false;
    if( $sHTMLSharing || $sHTMLEmbed || $sHTMLVideoLink) {
      $sHTML = "<div class='fvp-share-bar'>$sHTMLSharing$sHTMLVideoLink$sHTMLEmbed</div>";
    }
    
    $sHTML = apply_filters( 'fv_player_sharing_html', $sHTML );

    return $sHTML;
  }
  
  
  function get_title() {
    if( !empty($this->aCurArgs['caption']) ) {
      return trim($this->aCurArgs['caption']);
    }
    
    if( !empty($this->aCurArgs['title']) ) {
      return trim($this->aCurArgs['title']);
    }
    
    return false;
  }
  
  
  function get_video_checker_html() {
    global $fv_wp_flowplayer_ver;
    $sSpinURL = site_url('wp-includes/images/wpspin.gif');

    $sHTML = <<< HTML
<div title="Only you and other admins can see this warning." class="fv-player-video-checker fv-wp-flowplayer-ok" id="wpfp_notice_{$this->hash}" style="display: none">
  <div class="fv-player-video-checker-head">Video Checker <span></span></div>
  <small>Admin: <span class="video-checker-result">Checking the video file...</span></small>
  <div style="display: none;" class="fv-player-video-checker-details" id="fv_wp_fp_notice_{$this->hash}">
    <div class="mail-content-notice">
    </div>
    <div class="support-{$this->hash}">
      <textarea style="width: 98%; height: 150px" onclick="if( this.value == 'Enter your comment' ) this.value = ''" class="wpfp_message_field" id="wpfp_support_{$this->hash}">Enter your comment</textarea>
      <p><a class="techinfo" href="#" onclick="jQuery('.more-{$this->hash}').toggle(); return false">Technical info</a> <img style="display: none; " src="{$sSpinURL}" id="wpfp_spin_{$this->hash}" /> <input type="button" value="Send report to Foliovision" onclick="fv_wp_flowplayer_admin_support_mail('{$this->hash}', this); return false" /></p></div>
    <div class="more-{$this->hash} mail-content-details" style="display: none; ">
      <p>Plugin version: {$fv_wp_flowplayer_ver}</p>
      <div class="fv-wp-flowplayer-notice-parsed level-0"></div></div>
  </div>
</div>
HTML;

    return $sHTML;
  }
  
  
  // some themes use wp_filter_post_kses() on output, so we must ensure FV Player markup passes
  function wp_kses_permit( $tags, $context = false ) {
    if( $context != 'post' ) return $tags;
    
    if( !empty($tags['a']) && is_array($tags['a']) ) {
      $tags['a']['data-item'] = true;
      $tags['a']['itemprop'] = true;
      $tags['a']['itemscope'] = true;
      $tags['a']['itemtype'] = true;
      $tags['a']['onclick'] = true;
    }
    
    if( !empty($tags['div']) && is_array($tags['div']) ) {
      $tags['div']['data-ad_show_after'] = true;
      $tags['div']['data-advance'] = true;
      $tags['div']['data-analytics'] = true;
      $tags['div']['data-item'] = true;
      $tags['div']['data-button-no-picture'] = true;
      $tags['div']['data-button-repeat'] = true;
      $tags['div']['data-engine'] = true;
      $tags['div']['data-embed'] = true;
      $tags['div']['data-fv-embed'] = true;
      $tags['div']['data-loop'] = true;
      $tags['div']['data-fv_redirect'] = true;
      $tags['div']['data-fvautoplay'] = true;
      $tags['div']['data-fvsticky'] = true;
      $tags['div']['data-fullscreen'] = true;
      $tags['div']['data-live'] = true;
      $tags['div']['data-logo'] = true;
      $tags['div']['data-ratio'] = true;      
      $tags['div']['data-rtmp'] = true;
      $tags['div']['itemprop'] = true;
      $tags['div']['itemscope'] = true;
      $tags['div']['itemtype'] = true;
      $tags['div']['onclick'] = true;
      $tags['div']['rel'] = true;
    }
    
    if( empty($tags['meta']) ) {
      $tags['meta'] = array();
      $tags['meta']['name'] = true;
      $tags['meta']['content'] = true;
      $tags['meta']['itemprop'] = true;
    }
    
    return $tags;  
  }
  
  
}

