<?php
/*  FV Player - HTML5 video player
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

  var $splash_count = 0;

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

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}fv_player_players` WHERE (videos = %s OR videos LIKE %s OR videos %s", $ids_string, '%' . $wpdb->esc_like( $ids_string ), '%' . $wpdb->esc_like( $ids_string ) ) );

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

    // Nicer player element ID for players using FV Player database
    if ( ! empty( $args['id'] ) && is_numeric( $args['id'] ) ) {
      $id = $args['id'];
      $this->hash = $id;

      static $used;
      if ( !isset( $used ) ) {
        $used = array();
      }
      if ( !isset( $used[ $id ] ) ) {
        $used[ $id ] = 0;
      }

      // Append -2 or -3 if the same player is used multiple times on the page
      $used[ $id ]++;
      if( $used[ $id ] > 1 ) {
        $this->hash .= '-'.$used[ $id ];
      }
    }

    // todo: harmonize this, the media arg was a bad idea in the first place
    if( !empty($media) ) {
      $this->aCurArgs['src'] = $media;
    }
    $this->aCurArgs = apply_filters( 'fv_flowplayer_args_pre', $args );

    if ( -1 !== $this->get_current_video_to_edit() ) {
      $this->hash .= '-edit-' . $this->get_current_video_to_edit();
    }

    /**
     * Restore certain shortcode arguments if provided as they should override the DB value,
     * or any value provided via fv_flowplayer_args_pre filter.
     */
    foreach(
      array(
        'height',
        'lightbox',
        'liststyle',
        'width',
      ) as $key
    ) {
      if (
        ! empty( $args[ $key ] ) ||
        // Some of the shortcode arguments should be applied even if they are empty
        isset( $args[ $key ] ) && in_array( $key, array( 'lightbox' ) )
      ) {
        $this->aCurArgs[ $key ] = $args[ $key ];
      }
    }

    // load attributes from player into $this->aCurArgs if we're receiving
    // preview POST data, as they are not all present here yet
    if( $player = $this->current_player() ) {

      if( !$player->getToggleEndAction() ) {
        $this->aCurArgs['end_actions'] = false;
        $this->aCurArgs['end_action_value'] = false;
      }

      if( !$player->getToggleOverlay() ) {
        $this->aCurArgs['overlay'] = false;
      }
    }

    $media = $this->aCurArgs['src'];

    if( !$media && empty($this->aCurArgs['rtmp_path']) ) {
      return;
    }

    $this->sHTMLAfter = false;
    $player_type = 'video';
    $rtmp = false;
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

    // Only use player width if alignment is specified
    if ( ! empty( $this->aCurArgs['align'] ) ) {
      if ( ! empty( $this->aCurArgs['width'] ) ) {
        $width = trim( $this->aCurArgs['width'] );
      }

      if ( ! empty( $this->aCurArgs['height'] ) ) {
        $height = trim( $this->aCurArgs['height'] );
      }

    // Otherwise let it just affect the player aspect ratio
    } else if(
      stripos( $width, '%' ) === false && intval( $width ) > 0 &&
      stripos( $height, '%' ) === false && intval( $height ) > 0
    ) {
      $height = $width * $this->get_ratio();
    }

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

    // force horizontal playlist style for audio as that the only one styled properly if there are no splash screens
    if ( ! $splash_img && $this->is_audio_playlist() ) {
      $this->aCurArgs['liststyle'] = 'horizontal';
    }

    if( !isset($this->aCurArgs['liststyle']) || empty($this->aCurArgs['liststyle']) ){
      $this->aCurArgs['liststyle'] = $this->_get_option('liststyle');
    }

    if ( ( get_query_var('fv_player_embed') || get_query_var('fv_player_cms_id') ) && $this->aCurArgs['liststyle'] != 'tabs' && !in_array($this->aCurArgs['liststyle'], array( 'season', 'polaroid' ) ) ) { // force vertical playlist when using embed and not using tabs, nor season style and it's not a preview for editing
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

    list( $playlist_items_external_html, $aPlaylistItems, $aSplashScreens, $aCaptions ) = $this->build_playlist( $this->aCurArgs, $media, $src1, $src2, $rtmp, $splash_img );


    // Load playlists.css later, if it's used.
    if ( count( $aPlaylistItems ) > 1 ) {

      // Did we load FV Player CSS already? Then lets load the playlist CSS as soon as possible
      if ( $this->bCSSLoaded ) {
        wp_enqueue_style( 'fv_freedomplayer_playlists', FV_FP_RELATIVE_PATH.'/css/playlists.css', array('fv_flowplayer'), filemtime( dirname(__FILE__).'/../css/playlists.css' ) );

      // Tell the CSS loader to also include playlists.css
      } else {
        $this->bCSSPlaylists = true;
      }
    }

    if( count($aPlaylistItems) == 1 && empty($this->aCurArgs['listshow']) ) {
      $playlist_items_external_html = false;

      // Do not put in video data if there is an error to avoid playback
      if( !empty($this->aCurArgs['error']) ) {
        // Put in some minimal information to avoid JS errors
        $attributes['data-item'] = $this->json_encode( array( 'sources' => array( array( 'src' => '', 'type' => '' ) ) ) );
      } else {
        $attributes[ !empty($this->aCurArgs['lazy']) ? 'data-item-lazy' : 'data-item' ] = $this->json_encode( apply_filters( 'fv_player_item', $aPlaylistItems[0], 0, $this->aCurArgs ) );
      }
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

    if (isset($this->aCurArgs['playlist_start']) ) {
      if( $fv_fp && method_exists($fv_fp, 'current_player') && $fv_fp->current_player() && $fv_fp->current_player()->getVideos() ) { // DB player
        foreach ($fv_fp->current_player()->getVideos() as $video_index => $video) {
          if ($video_index + 1 == $this->aCurArgs['playlist_start']) {
            $splash_img = $video->getSplash();
            break;
          }
        }
      } else if( isset($this->aCurArgs['playlist']) ) { // Shortcode player
        $playlist_items = explode(';', $this->aCurArgs['playlist']);

        foreach( $playlist_items as $index => $item ) {
          if( $index + 2 == $this->aCurArgs['playlist_start'] ) {
            $item_data = explode(',', $item); // parse splash

            foreach( $item_data as $data) {
              if( preg_match('~\.(png|gif|jpg|jpe|jpeg)($|\?)~', $data) || stripos($data, 'i.vimeocdn.com') !== false) {
                $splash_img = $data;
                break 2;
              }
            }
          }
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
    if( in_array( $fv_fp->_get_option('autoplay_preload'), array('viewport', 'sticky')) && $this->aCurArgs['autoplay'] != 'false'  ) {
      $autoplay = 0;
    }

    if( isset($this->aCurArgs['autoplay']) && ($this->aCurArgs['autoplay'] == 'true' || $this->aCurArgs['autoplay'] == 'on')) {
      $autoplay = 0;
    }

    /*
     *  Sticky
     */
    $sticky = null;  //  todo: should be changed into a property

    if( $this->_get_option('autoplay_preload') == 'sticky' && $this->aCurArgs['sticky'] != 'false'  ) {
      $sticky = true;
    }

    if( isset($this->aCurArgs['sticky']) && ($this->aCurArgs['sticky'] == 'true' || $this->aCurArgs['sticky'] == 'on')) {
      $sticky = true;
    }

    if ( 'off' !== $this->_get_option('sticky_video') && 'false' === $this->aCurArgs['sticky'] ) {
      $sticky = false;
    }

    /*
     *  Video player
     */
    if( $player_type == 'video' ) {

        add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit' ), 999, 2 );
        add_filter( 'safe_style_css', array( $this, 'safe_style_css' ), 999, 2 );

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
            $this->ret['html'] .= '<br /><img src="'.esc_attr($splash_img).'" width="400" />';
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
            $this->ret['html'] .= '<br /><img src="'.esc_attr($splash_img).'" width="400" />';
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

        $popup = '';

        if(
          // new, DB playlist code
          (!empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'splashend')
        ||
          // compatibility fallback for classic (non-DB) shortcode
          (isset($this->aCurArgs['splashend']) && $this->aCurArgs['splashend'] == 'show' && isset($this->aCurArgs['splash']) && !empty($this->aCurArgs['splash']))
        ) {
          $splashend_contents = '<div id="wpfp_'.$this->hash.'_custom_background" class="wpfp_custom_background" style="background-image: url(\''.$splash_img.'\')"></div>';
        }

        // should the player appear as audio player?
        $bIsAudio = false;
        if( preg_match( '~\.(mp3|wav|ogg)([?#].*?)?$~', $media ) ) {
          $bIsAudio = true;

        } else if( ! empty( $this->aCurArgs['type'] ) && 'audio' === $this->aCurArgs['type'] ) {
          $bIsAudio = true;

        } else if( $video = $this->current_video() ) {
          $bIsAudio = $video->getMetaValue('audio',true);
        }

        // if there is splash and it's different from the site-wide default splash
        if( !empty($splash_img) && strcmp( $splash_img, $this->_get_option('splash') ) != 0 ) {
          $bIsAudio = false;
        }

        if( $this->_get_option('autoplay_preload') == 'preload' ) {
          $splash_preload_class = 'is-poster';
        } else {
          $splash_preload_class = 'is-splash';
        }

        $attributes['class'] = 'freedomplayer flowplayer no-brand ' . $splash_preload_class;

        if( !empty($this->aCurArgs['skin']) ) {
          $skin = 'skin-'.$this->aCurArgs['skin'];
        } else {
          $skin = 'skin-'.$this->_get_option('skin');
        }

        $attributes['class'] .= ' is-paused '.$skin;

        if ( ! flowplayer::is_wp_rocket_setting( 'delay_js' ) ) {
          $attributes['class'] .= ' no-svg';
        }

        $timeline_class = $this->_get_option(array($skin, 'design-timeline'));
        if( $bIsAudio && ( $timeline_class == 'fp-minimal' || $timeline_class == 'fp-full' ) ) {
          $timeline_class = 'fp-slim';
        }
        $attributes['class'] .= ' '.$timeline_class.' '.$this->_get_option(array($skin, 'design-icons'));

        if( !empty($this->aCurArgs['playlist']) ) {
          $attributes['class'] .= ' has-playlist has-playlist-'.$this->aCurArgs['liststyle'];
        }

        // Only add the HTML code if autoplay is not disabled or if it's set to be disabled for the player
        if( $autoplay != -1 || $autoplay == -1 && !empty($this->aCurArgs['autoplay']) && empty($this->aCurArgs['lightbox']) ) {
          $attributes['data-fvautoplay'] = $autoplay;
        }

        if ( true === $sticky ) {
          $attributes['data-fvsticky'] = 'true';

        } else if ( false === $sticky ) {
          $attributes['data-fvsticky'] = 'false';
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
          } else if( strcmp($this->aCurArgs['controlbar'],'no') == 0 || strcmp($this->aCurArgs['controlbar'],'false') == 0 ) {
            $attributes['class'] .= ' no-controlbar';
            $bFixedControlbar = false;
          }
        }
        if ( $bFixedControlbar && ! get_query_var('fv_player_embed') && ! get_query_var('fv_player_cms_id') ) {
          $attributes['class'] .= ' fixed-controls';
        }

        $attributes = $this->get_button_data( $attributes, 'no_picture', $this->aCurArgs );

        $attributes = $this->get_button_data( $attributes, 'repeat', $this->aCurArgs );

        $attributes = $this->get_button_data( $attributes, 'rewind', $this->aCurArgs );

        if( !empty($this->aCurArgs['fsforce']) ) {
          $attributes['data-fsforce'] = $this->aCurArgs['fsforce'];
        }

        if( $this->_get_option('engine') || !empty($this->aCurArgs['engine']) && $this->aCurArgs['engine'] == 'flash' ) {
          $attributes['data-engine'] = 'flash';
        }

        if(
          !empty($this->aCurArgs['embed']) && ( $this->aCurArgs['embed'] == 'false' || $this->aCurArgs['embed'] == 'off' ) ||
          ! $this->_get_option('ui_embed') && ( empty($this->aCurArgs['embed']) || $this->aCurArgs['embed'] != 'true' )
        ) {

        } else {
          $attributes['data-fv-embed'] = $this->get_embed_url();
        }

        if( isset($this->aCurArgs['logo']) && $this->aCurArgs['logo'] ) {
          $attributes['data-logo'] = ( strcmp($this->aCurArgs['logo'],'none') == 0 ) ? '' : $this->aCurArgs['logo'];
        }

        $attributes['style'] = '';

        // If FV Player CSS was not yet enqueue (in header) make sure to use minimal styling to avoid CLS
        if( !wp_style_is('fv_flowplayer') && !defined('PHPUnitTestMode') ) {
          $attributes['style'] = 'position:relative; ';
        }

        if( !empty($this->aCurArgs['playlist']) && in_array( $this->aCurArgs['liststyle'], array('horizontal','slider','vertical','prevnext','version-one','version-two') ) ) {
          $attributes['style'] .= 'max-width: 100%; ';
        } else if( !$bIsAudio ) {
          if( intval($width) == 0 ) $width = '100%';
          if( intval($height) == 0 ) $height = '100%';
          $cssWidth = stripos($width,'%') !== false ? $width : $width . 'px';
          $cssHeight = stripos($height,'%') !== false ? $height : $height. 'px';

            $attributes['style'] .= 'max-width: ' . $cssWidth . '; max-height: ' . $cssHeight . '; ';
          }

        list( $rtmp_server, $rtmp ) = $this->get_rtmp_server($rtmp);
        if( /*count($aPlaylistItems) == 0 &&*/ $rtmp_server) {
          $attributes['data-rtmp'] = $rtmp_server;
        }

        if( !$this->_get_option('allowfullscreen') || isset($this->aCurArgs['fullscreen']) && $this->aCurArgs['fullscreen'] == 'false' ) {
          if ( ! in_array( $this->aCurArgs['liststyle'], array( 'season', 'polaroid' ) ) ) {
            $attributes['data-fullscreen'] = 'false';
          }
        }

        // Calculate player aspect ratio
        if( !$bIsAudio ) {
          $attributes['data-ratio'] = str_replace(',','.', $this->get_ratio() );
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

        $show_title_div = false;

        $playlist = '';
        $is_preroll = false;

        // Is playlist
        if( isset($playlist_items_external_html) ) {

          if( $bIsAudio ) {
            $playlist_items_external_html = str_replace( 'class="fp-playlist-external', 'class="fp-playlist-external is-audio', $playlist_items_external_html );
          }

          if( $this->aCurArgs['liststyle'] == 'prevnext' || ( isset($this->aCurArgs['playlist_hide']) && $this->aCurArgs['playlist_hide']== 'true' ) ) {
            $playlist_items_external_html = str_replace( 'class="fp-playlist-external', 'style="display: none" class="fp-playlist-external', $playlist_items_external_html );
          }

          // Ignore video ads
          $visible_items = 0;
          foreach ( $aPlaylistItems AS $aPlaylistItem ) {
            if ( ! isset( $aPlaylistItem['click'] ) ) {
              $visible_items++;
            }
          }

          // Is a playlist with one item only (why?)
          if( 1 === $visible_items && empty($this->aCurArgs['listshow']) && empty($this->aCurArgs['lightbox']) ) {
            $show_title_div = true;
          }

          $this->sHTMLAfter .= $playlist_items_external_html;

        // Not a playlist and not using lightbox
        } else if( empty($this->aCurArgs['lightbox']) ) {
          $show_title_div = true;
        }

        if ( $show_title_div ) {
          $title = apply_filters( 'fv_player_title', $this->get_title(), $this );
          if ( $title ) {
            $attributes['class'] .= ' has-title-below';

            $title = "<p class='fp-title'>" . $title . "</p>";
          }

          $this->sHTMLAfter .= $title;
        }

        if( !empty($this->aCurArgs['chapters']) ) {
          $attributes['class'] .= ' has-chapters';
        }
         if( !empty($this->aCurArgs['transcript']) ) {
          $attributes['class'] .= ' has-transcript';
        }

        if ( get_query_var('fv_player_embed') || get_query_var('fv_player_cms_id') ) {  //  this is needed for iframe embedding only
          $attributes['class'] .= ' fp-is-embed';
        }

        $buttons_html = $this->get_buttons();
        if ( ! empty( $buttons_html ) ) {
          $attributes['class'] .= ' have-buttons';
        }

        if( !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'redirect' && ! empty( $this->aCurArgs['end_action_value'] ) ) {
          $attributes['data-fv_redirect'] = sanitize_url( trim($this->aCurArgs['end_action_value']) );
        } else if( !empty($this->aCurArgs['redirect']) ) {
          // compatibility fallback for classic (non-DB) shortcode
          $attributes['data-fv_redirect'] = sanitize_url( trim($this->aCurArgs['redirect']) );
        }

        if( isset($this->aCurArgs['admin_warning']) ) {
          $this->sHTMLAfter .= wpautop($this->aCurArgs['admin_warning']);
        }

        if( isset($this->aCurArgs['playlist_start']) ) {
          $attributes['data-playlist_start'] = $this->aCurArgs['playlist_start'];
        }

        if( $this->_get_option('overlay_show_after') ) {
          $attributes['data-overlay_show_after'] = $this->_get_option('overlay_show_after');
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

        if( $overlay_contents = $this->get_ad_code() ) {
          $attributes['data-overlay'] = $this->json_encode( $overlay_contents );
        }

        // Tell the preload script to not try to load the player
        if( !empty($this->aCurArgs['error']) ) {
          $attributes['data-error'] = $this->aCurArgs['error'];
        }

        add_filter( 'fv_flowplayer_attributes', array( $this, 'get_speed_attribute' ) );
        add_filter( 'fv_flowplayer_attributes', array( $this, 'get_youtube_attribute' ), 14, 3 );

        $attributes_html = '';
        $attributes = apply_filters( 'fv_flowplayer_attributes', $attributes, $media, $this );
        foreach( $attributes AS $attr_key => $attr_value ) {
          $attributes_html .= ' '.$attr_key.'="'.esc_attr( $attr_value ).'"';
        }

        $this->ret['html'] .= '<div id="wpfp_' . $this->hash . '"'.$attributes_html.'>'."\n";

        if( !$bIsAudio ) {
          $this->ret['html'] .= "\t".'<div class="fp-ratio" style="padding-top: '.str_replace(',','.',$this->get_ratio() * 100).'%"></div>'."\n";
        }

        if( !$bIsAudio && ( ! empty( $splash_img ) || ! empty( $this->aCurArgs['splash_attachment_id'] ) ) ) {
          $alt = $this->get_title() ? $this->get_title() : 'video';

          $splash_img_id = false;

          // Get the first video splash image attachment ID
          // Since current_video is the last video in playlist, we need to find the first video
          // TODO: What if we apply_filters( 'fv_player_item_pre', $aPlayer, 0, $aArgs ); here?
          $current_player = $this->current_player();
          if ( $current_player ) {
            $current_player_videos = $current_player->getVideos();
            if ( $current_player_videos ) {
              $count = 0;
              foreach ( $current_player_videos as $current_player_video ) {
                $current_player_video_splash_id = $current_player_video->getSplashAttachmentId();

                if ( -1 === $this->get_current_video_to_edit() || $this->get_current_video_to_edit() === $count ) {

                  if ( $current_player_video_splash_id ) {
                    $splash_img_id = $current_player_video_splash_id;
                  }

                  break;
                }

                $count++;
              }
            }
          }

          if ( ! $splash_img_id && ! empty( $this->aCurArgs['splash_attachment_id'] ) && intval( $this->aCurArgs['splash_attachment_id'] ) > 0 ) {
            $splash_img_id = intval( $this->aCurArgs['splash_attachment_id'] );
          }

          // Use the playlist splash if it's playlist
          if ( count($aPlaylistItems) > 1 ) {
            if ( ! empty( $this->aCurArgs['playlist_splash_attachment_id'] ) ) {
              $splash_img = $this->aCurArgs['playlist_splash_attachment_id'];
            } else if ( ! empty( $this->aCurArgs['playlist_splash'] ) ) {
              $splash_img = $this->aCurArgs['playlist_splash'];
            }
          }

          $image = false;

           // load the image from WP Media Library if you got a number
          if( $splash_img_id ) {
            $image = wp_get_attachment_image( $splash_img_id, 'full', false, array('class' => 'fp-splash', 'fv_sizes' => '25vw, 50vw, 100vw') );
          }

          if ( ! $image ) {
            $image = '<img class="fp-splash" alt="' . esc_attr( $alt ) . '" src="' . esc_attr( $splash_img ) . '" />';
          }

          /**
           * Lazy load images excerpt the first image as it might be above the fold.
           * This way we avoid the "Largest Contentful Paint image was lazily loaded" warning
           * in Google PageSpeed Insights.
           */
          if ( ++$this->splash_count > 1 ) {
            $image = str_replace( '<img ', '<img loading="lazy" ', $image );

          // If FV Player CSS was not yet enqueue (in header) make sure to use minimal styling to avoid CLS for first image
          } else {
            if( !wp_style_is('fv_flowplayer') && !defined('PHPUnitTestMode') ) {
              $image = str_replace( '<img ', '<img style="position:absolute;top:0;left:0;width:100%" ', $image );
            }
          }

          $this->ret['html'] .= "\t".$image."\n";
        }

        $preload = '<div class="fp-preload"><b></b><b></b><b></b><b></b></div>';

        if ( flowplayer::is_wp_rocket_setting( 'delay_js' ) ) {
          $preload = '';
        }

        if( !empty($fv_fp->aCurArgs['error']) ) {
          $this->ret['html'] .= "\t".'<div class="fp-ui"><div class="fp-message fp-shown">'.$fv_fp->aCurArgs['error'].'</div>'.$this->get_play_button().$preload.'</div>'."\n";

        } else {
          $fp_ui_style = !wp_style_is('fv_flowplayer') && !defined('PHPUnitTestMode') ? ' style="position:absolute"' : '';

          $this->ret['html'] .= "\t".'<div class="fp-ui"' . $fp_ui_style . '>';

          if( $bIsAudio ) {
            $this->ret['html'] .= '<div class="fp-controls"><a class="fp-icon fp-playbtn"></a><div class="fp-timeline fp-bar"></div></div>';

          } else {
            $this->ret['html'] .= '<noscript>Please enable JavaScript</noscript>' . $this->get_play_button() . $preload;
          }

          $this->ret['html'] .= '</div>'."\n";
        }

        $this->ret['html'] .= $buttons_html;

        if( isset($splashend_contents) ) {
          $this->ret['html'] .= $splashend_contents;
        }

        if( flowplayer::is_special_editor() ) {
          $this->ret['html'] .= '<div class="fp-ui"></div>';
        } else if( current_user_can('manage_options') && empty($this->aCurArgs['lazy']) && empty($this->aCurArgs['lightbox']) && empty($this->aCurArgs['error']) && empty($this->aCurArgs['checker']) ) {
          $this->ret['html'] .= '<div id="wpfp_'.$this->hash.'_admin_error" class="fvfp_admin_error"><div class="fvfp_admin_error_content"><h4>Admin JavaScript warning:</h4><p>I\'m sorry, your JavaScript appears to be broken. Please use "Check template" in plugin settings, read our <a href="https://foliovision.com/player/installation#fixing-broken-javascript" target="_blank">troubleshooting guide</a>, <a href="https://foliovision.com/troubleshooting-javascript-errors" target="_blank">troubleshooting guide for programmers</a> or <a href="https://foliovision.com/pro-support" target="_blank">order our pro support</a> and we will get it fixed for you.</p></div></div>';
        }

        $this->ret['html'] .= apply_filters( 'fv_flowplayer_inner_html', null, $this );

        if( !$bIsAudio ) {
          $this->ret['html'] .= $this->get_sharing_html()."\n";
        }

        if( !empty($this->aCurArgs['splash_text']) ) {
          $aSplashText = explode( ';', $this->aCurArgs['splash_text'] );
          $this->ret['html'] .= "<div class='fv-fp-splash-text'><span class='custom-play-button'>".flowplayer::filter_possible_html($aSplashText[0])."</span></div>\n"; //  needed for soap customizations of play button!
        }

        if( empty($this->aCurArgs['checker']) && !$this->_get_option('disable_videochecker') && current_user_can('manage_options') ) {
          $this->ret['html'] .= $this->get_video_checker_html()."\n";
        }

        if ($this->aCurArgs['liststyle'] == 'prevnext' && count($aPlaylistItems) > 1 ) {
          $this->ret['html'].='<a class="fp-prev" title="prev"></a><a class="fp-next" title="next"></a>';
        }

        $this->ret['html'] .= '</div>'."\n";

        $this->ret['html'] .= $this->sHTMLAfter;

        $align = $this->get_align();

        if ( $show_title_div && $align ) {
          $align_wrapper = '<div class="fv-player-align' . $align . '" ';

          if ( intval( $width ) == 0 ) {
            $width = '100%';
          }

          $cssWidth = stripos( $width, '%' ) !== false ? $width : $width . 'px';

          $align_wrapper .= 'style="max-width: ' . $cssWidth . '"';
          $align_wrapper .= '>' . $this->ret['html'] . '</div>';

          $this->ret['html'] = $align_wrapper;
        }

        $this->ret['html'] .= $scripts_after;

        //  change engine for IE9 and 10
        if( !empty($this->aCurArgs['engine']) && $this->aCurArgs['engine'] == 'false' ) {
          $this->ret['script']['fv_flowplayer_browser_ie'][$this->hash] = true;
        }

    } //  end Video player


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

      // These script need to run right away to ensure nothing moves during the page loading
      $script = "
<script>
( function() {
  var player = document.getElementById( 'wpfp_%hash%'),
    el = player.parentNode,
    playlist = document.getElementById( 'wpfp_%hash%_playlist'),
    property = playlist.classList.contains( 'fp-playlist-only-captions' ) ? 'height' : 'max-height',
    height = player.offsetHeight || parseInt(player.style['max-height']);

  if ( el.offsetHeight && el.offsetWidth <= 560 ) {
    el.classList.add('is-fv-narrow');
  }

  playlist.style[property] = height + 'px';

  if (property === 'max-height') {
    playlist.style['height'] = 'auto';
  }
} )();
</script>";

      $this->ret['html'] .= str_replace( '%hash%', $this->hash , $script );
    }

    $this->ret['html'] = apply_filters( 'fv_flowplayer_html', $this->ret['html'], $this );

    if( get_query_var('fv_player_embed') ) {  //  this is needed for iframe embedding only
      $this->ret['html'] .= "<!--fv player end-->";
    }

    $this->ret['script'] = apply_filters( 'fv_flowplayer_scripts_array', $this->ret['script'], 'wpfp_' . $this->hash, $media );

    return $this->ret;
  }


  function get_ad_code() {
    $overlay_contents = false;

    // Map old shortcode arguments to new
    foreach(
      array(
        'ad'        => 'overlay',
        'ad_width'  => 'overlay_width',
        'ad_height' => 'overlay_height',
        'ad_skip'   => 'overlay_skip',
      ) as $old => $new
    ) {
      if (isset( $this->aCurArgs[ $old ] ) && !empty( $this->aCurArgs[ $old ] ) ) {
        $this->aCurArgs[ $new ] = $this->aCurArgs[ $old ];
      }
    }

    if(
      ( trim($this->_get_option('overlay')) || ( isset($this->aCurArgs['overlay']) && !empty($this->aCurArgs['overlay']) ) )
      && !strlen($this->aCurArgs['overlay_skip'])
    ) {
      if (isset($this->aCurArgs['overlay']) && !empty($this->aCurArgs['overlay'])) {
        $overlay = trim($this->aCurArgs['overlay']);
        if( stripos($overlay,'<!--fv_flowplayer_base64_encoded-->') !== false ) {
          $overlay = str_replace('<!--fv_flowplayer_base64_encoded-->','',$overlay);
          $overlay = html_entity_decode( str_replace( array('\"','\[','\]'), array('"','[',']'), base64_decode($overlay) ) );
        } else {
          $overlay = html_entity_decode( str_replace('&#039;',"'",$overlay ) );
        }

        $overlay_width = ( isset($this->aCurArgs['overlay_width']) && intval($this->aCurArgs['overlay_width']) > 0 ) ? intval($this->aCurArgs['overlay_width']).'px' : '100%';
        $overlay_height = ( isset($this->aCurArgs['overlay_height']) && intval($this->aCurArgs['overlay_height']) > 0 ) ? intval($this->aCurArgs['overlay_height']).'px' : '';
      }
      else {
        $overlay = trim( $this->_get_option('overlay') );
        $overlay_width = ( $this->_get_option('overlay_width') ) ? $this->_get_option('overlay_width').'px' : '100%';
        $overlay_height = ( $this->_get_option('overlay_height') ) ? $this->_get_option('overlay_height').'px' : '';
      }

      $overlay = apply_filters( 'fv_flowplayer_ad_html', $overlay);
      $overlay = apply_filters( 'fv_flowplayer_overlay_html', $overlay);
      if( strlen(trim($overlay)) > 0 ) {
        $overlay_contents = array(
                             'html' => "<div class='wpfp_custom_ad_content' style='width: $overlay_width; height: $overlay_height'>\n\t\t<div class='fv_fp_close'><a href='#'></a></div>\n\t\t\t".$overlay."\n\t\t</div>",
                             'width' => $overlay_width,
                             'height' => $overlay_height
                            );
      }
    }

    return $overlay_contents;
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


  /**
   * Get the index of the video in the FV Player Editor preview
   *
   * @return int Zero-based index of the video in playlist or -1 if it's the playlist view
   */
  function get_current_video_to_edit() {
    if ( isset( $this->aCurArgs['current_video_to_edit'] ) && $this->aCurArgs['current_video_to_edit'] > -1 ) {
      return $this->aCurArgs['current_video_to_edit'];
    }

    return -1;
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

  function get_play_button() {
    $icons = $this->_get_option( array('skin-custom', 'design-icons') );
    if( $this->_get_option( 'skin' ) == 'slim' ) {
      $icons = 'fp-edgy';
    } else if( $this->_get_option( 'skin' ) == 'youtuby' ) {
      $icons = 'fp-playful';
    }

    $rounded_outline = '<svg class="fp-play-rounded-outline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 99.844 99.8434"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-rounded-outline</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><path class="controlbutton" d="M41.0359,71.19a5.0492,5.0492,0,0,1-2.5575-.6673c-1.8031-1.041-2.7958-3.1248-2.7958-5.8664V35.1887c0-2.7429.9933-4.8272,2.797-5.8676,1.8025-1.0422,4.1034-.86,6.48.5143L70.4782,44.5672c2.3751,1.3711,3.6826,3.2725,3.6832,5.3545s-1.3076,3.9845-3.6832,5.3562L44.9592,70.0114A7.9384,7.9384,0,0,1,41.0359,71.19Zm.0065-40.123a2.6794,2.6794,0,0,0-1.3582.3413c-1.0263.5926-1.5912,1.9349-1.5912,3.78V64.6563c0,1.8449.5649,3.1866,1.5906,3.7791,1.0281.5932,2.4733.4108,4.07-.512L69.273,53.1906c1.5983-.9227,2.478-2.0838,2.478-3.2689s-.88-2.3445-2.478-3.2666L43.754,31.9227A5.5685,5.5685,0,0,0,41.0423,31.0671Z" filter="url(#f1)"/></svg>';

    $rounded_fill = '<svg class="fp-play-rounded-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.a{fill:#000;opacity:0.65;}.b{fill:#fff;opacity:1.0;}</style></defs><title>play-rounded-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><path class="b" d="M35.942,35.2323c0-4.7289,3.3506-6.6637,7.446-4.2971L68.83,45.6235c4.0956,2.364,4.0956,6.2319,0,8.5977L43.388,68.91c-4.0954,2.364-7.446.43-7.446-4.2979Z" filter="url(#f1)"/></svg>';

    $sharp_fill = '<svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg>';

    $sharp_outline = '<svg class="fp-play-sharp-outline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 99.844 99.8434"><defs><style>.controlbuttonbg{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-outline</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><path class="controlbutton" d="M36.9443,72.2473V27.2916L75.8776,49.77Zm2.2-41.1455V68.4371L71.4776,49.77Z" filter="url(#f1)"/></svg>';

    if( $icons == 'fp-edgy' ) {
      $html = $sharp_fill;

    } else if( $icons == 'fp-outlined' ) {
      $html = $rounded_outline;

    } else {
      // TODO: Fix transparency before JS loads
      $html = $rounded_fill;

    }

    return '<div class="fp-play fp-visible">'.$html.'</div>';

  }


  function get_popup_code() {
    if(
      !empty($this->aCurArgs['end_actions']) &&
      in_array( $this->aCurArgs['end_actions'], array( 'no', 'redirect', 'loop', 'splashend' ) )
    ) {
      return false;
    }

    // static and e-mail popups share the same parameter in old non-DB shortcode
    $is_static_popup = (!empty($this->aCurArgs['popup']) || !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'popup');
    $is_email_popup = (!empty($this->aCurArgs['popup']) || !empty($this->aCurArgs['end_actions']) && $this->aCurArgs['end_actions'] == 'email_list');

    if( !empty($this->aCurArgs['end_actions']) && ($is_static_popup || $is_email_popup) ) {
      if ($is_static_popup) {
        $popup = false;

        if ( ! empty( $this->aCurArgs['end_action_value'] ) ) {
          $popup = trim( $this->aCurArgs['end_action_value'] );
        }

        if ( ! empty( $this->aCurArgs['popup'] ) ) {
          $popup = trim( $this->aCurArgs['popup'] );
        }

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
        $iPopupIndex = wp_rand(1, count($aPopupData));
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

  /**
   * Get player aspect ratio based on the first video.
   *
   * Logic:
   * * use the aspect_ratio loaded from database
   * * or calculate from the video dimensions if provided
   * * or calculate form the global settings of default player dimensions if in pixels
   * * or use 9/16 as a default
   *
   * @return float Aspect ratio of the first video.
   */
  function get_ratio() {
    if ( ! empty( $this->aCurArgs['aspect_ratio'] ) && floatval( $this->aCurArgs['aspect_ratio'] ) > 0 ) {
      return floatval( $this->aCurArgs['aspect_ratio'] );
    }

    $width = $this->_get_option('width');
    $height = $this->_get_option('height');

    // Use video dimensions if provided
    $ratio_width = ! empty( $this->aCurArgs['width'] ) ? intval( $this->aCurArgs['width'] ) : $width;
    $ratio_height = ! empty( $this->aCurArgs['height'] ) ? intval( $this->aCurArgs['height'] ) : $height;

    // Do not calculate ratio if % values are provided
    if(
      stripos( $ratio_width, '%' ) === false && intval( $ratio_width ) > 0 &&
      stripos( $ratio_height, '%' ) === false && intval( $ratio_height ) > 0
    ) {
      $ratio = round( intval( $ratio_height ) / intval( $ratio_width ), 4);
    } else {
      $ratio = 9/16;
    }

    return $ratio;
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
        $rtmp_info = wp_parse_url($rtmp);
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
    if( $this->_get_option('ui_speed') || isset($this->aCurArgs['speed']) && (
      $this->aCurArgs['speed'] == 'buttons' || $this->aCurArgs['speed'] == 'yes' || $this->aCurArgs['speed'] == 'true'
    ) ) {
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


  function get_youtube_attribute( $attributes ) {

    $youtube_chrome = $this->_get_option('youtube_browser_chrome');

    if( !empty($attributes['data-engine']) && strcmp($attributes['data-engine'], 'fvyoutube') == 0 ) {

      if( strcmp($youtube_chrome, 'standard') == 0 ) {
        $attributes['class'] .= ' is-youtube-standard';
      } else if( strcmp($youtube_chrome, 'reduced') == 0 ) {
        $attributes['class'] .= ' is-youtube-reduced';
      } else if( strcmp($youtube_chrome, 'none') == 0 ) {
        $attributes['class'] .= ' is-youtube-nl';
      }

    }

    return $attributes;
  }


  function get_splash() {
    $splash_img = false;
    if (isset($this->aCurArgs['splash']) && !empty($this->aCurArgs['splash'])) {
      $splash_img = $this->aCurArgs['splash'];

      /**
       * If the URL is not a number and it does not start with protocol it's a relative URL.
       * So we change if from relative to absolute URL.
       */
      if( !is_numeric($splash_img) && strpos($splash_img,'http://') !== 0 && strpos($splash_img,'https://') !== 0 && strpos($splash_img,'//') !== 0 ) {
        if ( $splash_img[0] === '/' ) {
          $splash_img = substr($splash_img, 1);
        }

        $splash_img = $this->get_server_url() . $splash_img;
      }

    } else if( $this->_get_option('splash') ) {
      $splash_img = $this->_get_option('splash');
    }

    $splash_img = apply_filters( 'fv_flowplayer_splash', $splash_img, !empty($this->aCurArgs['src']) ? $this->aCurArgs['src'] : false );

    return $splash_img;
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

      // Do not let it load the same player again
      unset($this->aCurArgs['id']);
      unset($this->aCurArgs['playlist']);

      foreach( array( 'src', 'src1', 'src2' ) as $k ) {
        if ( ! empty( $aSrc['sources'][0][ $k ] ) ) {
          $this->aCurArgs[ $k ] = $aSrc['sources'][0][ $k ];
        }
      }

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

      /**
       * Make sure each item is aware of its DB entry.
       *
       * Somehow adjusting $this->currentPlayerObject->video_objects is not required.
       */
      if ( ! empty( $this->aCurArgs['video_objects'] ) ) {
        array_shift( $this->aCurArgs['video_objects'] );
      }
    }
    $output->ret['html'] .= '<div class="fv_flowplayer_tabs_cl"></div><div class="fv_flowplayer_tabs_cr"></div></div></div>';

    $this->load_tabs = true;

    return $output->ret;
  }


  function get_sharing_html() {
    global $post;

    $sSharingText = $this->_get_option('sharing_email_text' );
    $bVideoLink = empty($this->aCurArgs['linking']) ? $this->_get_option( 'ui_video_links' ) : $this->aCurArgs['linking'] === 'true';

    if( isset($this->aCurArgs['share']) && $this->aCurArgs['share'] ) {
      $aSharing = explode( ';', $this->aCurArgs['share'] );
      if( count($aSharing) == 2 ) {
        $sPermalink = $aSharing[1];
        $sMail =  apply_filters( 'fv_player_sharing_mail_content',$sSharingText.': '.$aSharing[1] );
        $sTitle = $aSharing[0].' ';
        $bVideoLink = false;
      } else if( count($aSharing) == 1 && $this->aCurArgs['share'] != 'yes' && $this->aCurArgs['share'] != 'no' ) {
        $sPermalink = $aSharing[0];
        $sMail = apply_filters( 'fv_player_sharing_mail_content', $sSharingText.': '.$aSharing[0] );
        $sTitle = get_bloginfo().' ';
        $bVideoLink = false;
      }
    }

    if( !isset($sPermalink) || empty($sPermalink) ) {
      $sPermalink = get_permalink();

      // If the player is on a category, tag or a tax page, but it's not in the loop yet, use the category/tag/tax link
      if( !in_the_loop() && ( is_category() || is_tag() || is_tax() ) ) {
        $term = get_queried_object();
        if( $term && is_a( $term, 'WP_Term') ) {
          $sPermalink = get_term_link($term);
        }
      }

      $sMail = apply_filters( 'fv_player_sharing_mail_content', $sSharingText.': '.$sPermalink );
      $sTitle = html_entity_decode( is_singular() ? get_the_title().' ' : get_bloginfo() ).' ';
    }


    $sHTMLSharing = '<ul class="fvp-sharing">
    <li><a class="sharing-facebook" href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($sPermalink) . '" target="_blank"></a></li>
    <li><a class="sharing-twitter" href="https://twitter.com/intent/tweet?text=' . urlencode( $sTitle ) .'&url='. urlencode($sPermalink) . '" target="_blank"></a></li>
    <li><a class="sharing-email" href="mailto:?body=' . rawurlencode( $sMail ) . '" target="_blank"></a></li></ul>';

    if( isset($post) && isset($post->ID) ) {
      $sHTMLVideoLink = $bVideoLink ? '<div><label><a class="sharing-link" href="' . esc_attr( $sPermalink ) . '" target="_blank">Link</a></label></div>' : '';
    } else {
      $sHTMLVideoLink = false;
    }

    if( !empty($this->aCurArgs['embed']) && ( $this->aCurArgs['embed'] == 'false' || $this->aCurArgs['embed'] == 'off' ) ) {
      $sHTMLVideoLink = false;
    }

    $sHTMLEmbed = '<div><label><a class="embed-code-toggle" href="#"><strong>Embed</strong></a></label></div><div class="embed-code"><label>Copy and paste this HTML code into your webpage to embed.</label><textarea></textarea></div>';


    if(
      !empty($this->aCurArgs['embed']) && ( $this->aCurArgs['embed'] == 'false' || $this->aCurArgs['embed'] == 'off' ) ||
      ! $this->_get_option('ui_embed') && ( empty($this->aCurArgs['embed']) || $this->aCurArgs['embed'] != 'true' )
    ) {
      $sHTMLEmbed = '';
    }

    if( isset($this->aCurArgs['share']) && ($this->aCurArgs['share'] == 'no' || $this->aCurArgs['share'] == 'false') ) {
      $sHTMLSharing = '';
    } else if( isset($this->aCurArgs['share']) && $this->aCurArgs['share'] && $this->aCurArgs['share'] != 'no' && $this->aCurArgs['share'] != 'false' ) {

    } else if( ! $this->_get_option('ui_sharing') ) {
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
    $title = false;

    if( !empty($this->aCurArgs['caption']) ) {
      $title = $this->aCurArgs['caption'];
    }

    if( !empty($this->aCurArgs['title']) ) {
      $title = $this->aCurArgs['title'];
    }

    if ( !empty( $this->aCurArgs['title_hide'] ) && !empty( $this->aCurArgs['toggle_advanced_settings'] ) ) {
      $title = false;
    }

    $title = flowplayer::filter_possible_html($title);
    $title = trim($title);

    return $title;
  }


  function get_video_checker_html() {
    global $fv_wp_flowplayer_ver;

    $template = '
<div title="Only you and other admins can see this warning." class="fv-player-video-checker fv-wp-flowplayer-ok" id="wpfp_notice_%hash%" style="display: none">
  <div class="fv-player-video-checker-head">Video Checker <span></span></div>
  <small>Admin: <span class="video-checker-result">Checking the video file...</span></small>
  <div style="display: none;" class="fv-player-video-checker-details" id="fv_wp_fp_notice_%hash%">
    <div class="mail-content-notice">
    </div>
    <div class="support-%hash%">
      <textarea style="width: 98%; height: 150px" onclick="if( this.value == \'Enter your comment\' ) this.value = \'\'" class="wpfp_message_field" id="wpfp_support_%hash%">Enter your comment</textarea>
      <p><a class="techinfo" href="#" onclick="jQuery(\'.more-%hash%\').toggle(); return false">Technical info</a> <img style="display: none; " src="%spinner%" id="wpfp_spin_%hash%" /> <input type="button" value="Send report to Foliovision" onclick="fv_wp_flowplayer_admin_support_mail(\'%hash%\', this); return false" /></p></div>
    <div class="more-%hash% mail-content-details" style="display: none; ">
      <p>Plugin version: %ver%</p>
      <div class="fv-wp-flowplayer-notice-parsed level-0"></div></div>
  </div>
</div>
';

    return str_replace(
      array(
        '%hash%',
        '%spinner%',
        '%ver%'
      ),
      array(
        $this->hash,
        site_url( 'wp-includes/images/wpspin.gif' ),
        $fv_wp_flowplayer_ver
      ),
      $template
    );
  }


  /**
   * Is it a audio track-only playlist with no splash screens?
   */
  function is_audio_playlist() {

    // Are all the database player items audio tracks?
    if( $player = $this->current_player() ) {

      $items = $player->getVideos();
      $count_audio_items = 0;

      if( $items ) {
        foreach( $items AS $item ) {
          if( $item->getSplash() ) {
            continue;
          }

          if(
            $item->getMetaValue('audio',true) ||
            preg_match( '~\.(mp3|wav|ogg)([?#].*?)?$~', $item->getSrc() )
          ) {
            $count_audio_items++;
          }
        }
      }

      if ( count( $items ) === $count_audio_items ) {
        return true;
      }

    // Does the legacy shortcode start with an audio track with no splash?
    } else if( ! empty( $this->aCurArgs['src'] ) && preg_match( '~\.(mp3|wav|ogg)([?#].*?)?$~', $this->aCurArgs['src'] ) && empty( $this->aCurArgs['splash'] ) ) {
      return true;
    }

    return false;
  }

  public function safe_style_css( $styles ) {
    $styles[] = 'display';
    return $styles;
  }

  // some themes use wp_filter_post_kses() on output, so we must ensure FV Player markup passes
  function wp_kses_permit( $tags, $context = false ) {
    if( $context != 'post' ) return $tags;

    if( !empty($tags['a']) && is_array($tags['a']) ) {
      $tags['a']['data-item'] = true;
      $tags['a']['download'] = true;
      $tags['a']['itemprop'] = true;
      $tags['a']['itemscope'] = true;
      $tags['a']['itemtype'] = true;
      $tags['a']['onclick'] = true;
    }

    if ( empty($tags['defs']) ) {
      $tags['defs'] = array();
    }

    if( !empty($tags['div']) && is_array($tags['div']) ) {
      $tags['div']['data-overlay_show_after'] = true;
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

    $tags['noscript'] = true;

    if ( empty($tags['path']) ) {
      $tags['path'] = array();
    }

    $tags['path']['class'] = true;
    $tags['path']['d'] = true;

    if ( empty($tags['polygon']) ) {
      $tags['polygon'] = array();
    }

    $tags['polygon']['class'] = true;
    $tags['polygon']['points'] = true;
    $tags['polygon']['filter'] = true;

    if ( empty($tags['style']) ) {
      $tags['style'] = array();
    }

    if ( empty($tags['svg']) ) {
      $tags['svg'] = array();
    }

    $tags['svg']['class'] = true;
    $tags['svg']['viewbox'] = true;
    $tags['svg']['xmlns'] = true;

    return $tags;
  }


}
