<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_YouTube {

  static $instance = null;

  var $bYoutube = false;

  var $fTimeSpent_AutoSplash = 0;

  public static function _get_instance() {
    if( !self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  function __construct() {

    if( !is_admin() ) {
      // Load splash via API if not provided
      add_filter( 'fv_flowplayer_splash', array( $this, 'get__cached_splash' ), 10, 2 );
      add_filter( 'fv_flowplayer_playlist_splash', array( $this, 'get__cached_splash' ), 10, 2 );
    }

    // Fallback YouTube splash screen if no API key provided
    add_filter( 'fv_flowplayer_splash', array( $this, 'youtube_splash' ), 10, 2 );
    add_filter( 'fv_flowplayer_playlist_splash', array( $this, 'youtube_splash' ), 10, 2 );

    add_action( 'init', array( $this, 'disable_pro_plugin_hooks' ) );

    add_action( 'admin_init', array( $this, 'admin__add_meta_boxes' ) );

    //add_action( 'amp_post_template_footer', array( $this, 'amp_post_template_footer' ), 9 );

    add_filter( 'fv_flowplayer_attributes', array( $this, 'player_attributes' ), 10, 3 );

    add_filter( 'fv_player_item', array($this, 'player_item'), 10, 3 );

    add_filter( 'fv_flowplayer_checker_time', array( $this, 'youtube_duration' ), 10, 2 );

    add_filter( 'fv_flowplayer_args', array( $this, 'disable_titles_youtube') );

    add_filter( 'fv_flowplayer_get_mime_type', array( $this, 'set_file_type'), 10, 2 );

    add_action( 'fv_player_extensions_admin_load_assets', array( $this, 'admin_load_assets' ) );

    add_filter( 'fv_flowplayer_conf', array($this, 'fv_flowplayer_conf'), 10, 2);

    add_filter( 'fv_player_meta_data', array($this, 'fetch_yt_data'), 10, 3);

    //add_action( 'wp_footer', array( $this, 'scripts' ), 0 );

  }

  function admin__add_meta_boxes() {
    add_meta_box( 'fv_player_youtube', __('YouTube', 'fv-player-pro'), array( $this, 'fv_player_admin_youtube' ), 'fv_flowplayer_settings_hosting', 'normal' );
  }

  /*
   * Triggered when loading the FV Player editor, we will need the
   * editor scripts and the player scripts as well - for preview.
   */
  function admin_load_assets() {
    global $fv_wp_flowplayer_ver;
    wp_enqueue_script('fvplayer-shortcode-editor-youtube', plugins_url('js/shortcode-editor-youtube.js', dirname(__FILE__) ),array('jquery','fvwpflowplayer-shortcode-editor'), $fv_wp_flowplayer_ver );

    //$this->scripts();
  }

  function amp_post_template_footer() {
    $this->scripts();
  }

  function disable_pro_plugin_hooks() {
    if ( function_exists( 'FV_Player_Pro' ) ) {
      remove_filter( 'fv_flowplayer_splash', array( FV_Player_Pro(), 'youtube_splash' ), 10, 2 );
      remove_filter( 'fv_flowplayer_playlist_splash', array( FV_Player_Pro(), 'youtube_splash' ), 10, 2 );
    }
  }

  function disable_titles_youtube( $aArgs ) {
    global $fv_fp;

    //  we don't want to avoid caption if it's set in lightbox anchor
    if( isset($aArgs['lightbox']) && $aArgs['lightbox'] ) {
      $aLightbox = preg_split('~[;]~', $aArgs['lightbox']);

      $bUseAnchor = false;
      foreach ($aLightbox AS $k => $i) {
        if ($i == 'text') {
          unset($aLightbox[$k]);
          $bUseAnchor = true;
        }
      }

      if( $bUseAnchor ) {
        return $aArgs;
      }
    }

    if( isset($aArgs['src']) && $this->is_youtube($aArgs['src']) && $fv_fp->_get_option( array('pro','youtube_titles_disable') )) {
      $aArgs['caption'] = '';
    }
    return $aArgs;
  }

  function fetch_yt_data($video, $post_id = false, $videoObj = false ) {
    global $fv_fp;

    // must be url string
    if( !is_string($video) ) {
      return $video;
    }

    if ( $this->is_youtube($video) && $fv_fp->_get_option( array('pro','youtube_key') )) {

      $fv_flowplayer_meta = false;
      if( $post_id ) {
        $fv_flowplayer_meta = get_post_meta($post_id, flowplayer::get_video_key($video), true);
        if( !$fv_flowplayer_meta || !isset($fv_flowplayer_meta['date']) || ( $fv_flowplayer_meta['date'] + 24 * 3600 ) < time() || !$fv_flowplayer_meta['duration'] && ( $fv_flowplayer_meta['date'] + 60 ) < time() ) {
          $fv_flowplayer_meta = false;
        }
      }

      if( !$fv_flowplayer_meta ) {
        $tStart = microtime(true);
        $aId = $this->is_youtube($video);

        $api_url = add_query_arg( array(
          'part' => 'snippet,contentDetails,player',
          'id' => $aId[1],
          'key' => $fv_fp->_get_option( array('pro','youtube_key') ),
          'maxWidth' => 1920 // This is a trick to get player->embedWidth and embedHeight to be able to tell the aspect ratio of the video
        ), 'https://www.googleapis.com/youtube/v3/videos' );

        $response = wp_remote_get( $api_url, array( 'sslverify' => false ) );

        $obj = is_wp_error($response) ? false : @json_decode( wp_remote_retrieve_body($response) );

        if( isset($obj->error) && !empty($obj->error->message) ) {
          update_option('fv_player_pro_youtube_error', gmdate('r').": ".$obj->error->message, false );
        }

        if( $obj && !empty($obj->items[0]) ) {
          $obj_item = $obj->items[0];

          $fv_flowplayer_meta = array();
          if ( !empty($obj_item->contentDetails->duration) ) {
            $duration = $obj_item->contentDetails->duration;

            if (class_exists('DateInterval')) {
              $interval = new DateInterval($duration);
              $fv_flowplayer_meta['duration'] = date_create('@0')->add($interval)->getTimestamp();
            } else {
              $fv_flowplayer_meta['duration'] = flowplayer::hms_to_seconds($duration);
            }
          }

          $fv_flowplayer_meta['aspect_ratio'] = 0;
          if( !empty($obj_item->player->embedWidth) && !empty($obj_item->player->embedHeight) ) {
            $fv_flowplayer_meta['aspect_ratio'] = $obj_item->player->embedHeight/$obj_item->player->embedWidth;
          }

          $fv_flowplayer_meta['is_live'] = false;
          if( !empty($obj_item->snippet->liveBroadcastContent) ) {
            if( $obj_item->snippet->liveBroadcastContent == 'live' ) {
              $fv_flowplayer_meta['is_live'] = true;
            }
          }

          //  YouTube splash screens come in various sizes and often with black borders. So we use the maxres image to determine image aspect ratio and then look for matching image
          if( !empty($obj_item->snippet->thumbnails) ) {
            $thumbs = $obj_item->snippet->thumbnails;
            $ratio = isset($thumbs->maxres) && intval($thumbs->maxres->width) > 0 && intval($thumbs->maxres->height) > 0 ? $thumbs->maxres->height/$thumbs->maxres->width : false;
            foreach( (array)$thumbs AS $k => $v ) {
              if( !$ratio || $v->height/$v->width == $ratio ) {
                $fv_flowplayer_meta['splash'] = $v->url;
                if( $v->width > 600 ) break;
              }
            }
          }

          $fv_flowplayer_meta['caption'] = !empty($obj_item->snippet->title) ? $obj_item->snippet->title : false;

          $fv_flowplayer_meta['author_thumbnail'] = false;
          $fv_flowplayer_meta['author_name'] = false;
          $fv_flowplayer_meta['author_url'] = false;

          // get channel id
          $channel_id = !empty($obj_item->snippet->channelId) ? $obj_item->snippet->channelId : false;

          $youtube_channel_obj = false;

          if( $channel_id ) {
            $api_url = add_query_arg( array(
              'part' => 'snippet',
              'id' => $channel_id,
              'key' => $fv_fp->_get_option( array('pro','youtube_key') ),
            ), 'https://www.googleapis.com/youtube/v3/channels' );


            $response = wp_remote_get( $api_url, array( 'sslverify' => false ) );

            $youtube_channel_obj = is_wp_error($response) ? false : @json_decode( wp_remote_retrieve_body($response) );
          }

          if ( $youtube_channel_obj && ! empty( $youtube_channel_obj->items[0]->snippet ) ) {
            $snippet = $youtube_channel_obj->items[0]->snippet;

            // get 'default' thumbnail
            $author_thumbnail_url = ! empty( $snippet->thumbnails->default->url) ? $snippet->thumbnails->default->url : false;

            // get channel name
            $author_name = ! empty( $snippet->title) ? $snippet->title : false;
            $fv_flowplayer_meta['author_name'] = $author_name;

            // get channel url
            $author_url = ! empty( $snippet->customUrl) ? 'https://www.youtube.com/' . $snippet->customUrl : false;
            $fv_flowplayer_meta['author_url'] = $author_url;

            if( $author_thumbnail_url && $author_name ) { // download channel thumbnail to media library
              global $FV_Player_Splash_Download, $wpdb;

              // check if author name is already in media library and use its post id as attachment id
              $youtube_channel_attachment_cache = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_fv_player_youtube_channel_id' AND meta_value = %s", $channel_id ) );

              if( !empty($youtube_channel_attachment_cache) ) {
                $fv_flowplayer_meta['author_thumbnail'] = $youtube_channel_attachment_cache;
              } else {
                add_filter( 'upload_dir', array( $this, 'custom_upload_path' ) );
                $author_thumbnail_attachment_data = $FV_Player_Splash_Download->download_splash( $author_thumbnail_url, $author_name );
                remove_filter( 'upload_dir', array( $this, 'custom_upload_path' ) );

                if( !empty($author_thumbnail_attachment_data) ) {
                  $author_thumbnail_attachment_id = $author_thumbnail_attachment_data['attachment_id'];
                  $fv_flowplayer_meta['author_thumbnail'] = $author_thumbnail_attachment_id; // store attachment id in video meta

                  update_post_meta( $author_thumbnail_attachment_id, '_fv_player_youtube_channel_id', $channel_id ); // store splash url in attachment meta
                }
              }
            }
          }

        }

        $fv_flowplayer_meta['check_time'] = microtime(true) - $tStart;

        if ($post_id) {
          update_post_meta($post_id, flowplayer::get_video_key($video), $fv_flowplayer_meta);
        }
      }

      $videoData = false;
      if( !empty($fv_flowplayer_meta['splash']) && !empty($fv_flowplayer_meta['caption']) ) {
        $videoData = array(
            'name' => htmlspecialchars( str_replace( array(';','[',']'), array('\;','(',')'), $fv_flowplayer_meta['caption']) ),
            'thumbnail' => $fv_flowplayer_meta['splash'],
            'duration' => $fv_flowplayer_meta['duration'],
            'aspect_ratio' => $fv_flowplayer_meta['aspect_ratio'],
            'is_live'      => $fv_flowplayer_meta['is_live'],
            'author_thumbnail' => $fv_flowplayer_meta['author_thumbnail'],
            'author_name' => $fv_flowplayer_meta['author_name'],
            'author_url' => $fv_flowplayer_meta['author_url'],
            // Note: No way of getting the actual video size unless you own the video and can use part=fileDetails
        );

        $videoData = apply_filters( 'fv_player_meta_data_youtube', $videoData, $video, $obj, $videoObj, $fv_flowplayer_meta );
      }

      if( isset($obj->items[0]->snippet->liveBroadcastContent) && $obj->items[0]->snippet->liveBroadcastContent == 'live' ) {
        $videoData['is_live'] = true;
      }

      return $videoData;
    } else {
      return $video; // no vimeo or yt, pass to another filter
    }
  }

  function fv_flowplayer_conf( $conf ) {
    global $fv_fp;

    if( $this->bYoutube || $fv_fp->should_force_load_js() || did_action('fv_player_extensions_admin_load_assets') ) {
      $conf['youtube'] = true;

      if( $fv_fp->_get_option( array('pro','youtube_cookies') ) ) {
        $conf['youtube_cookies'] = true;
      }
    }
    return $conf;
  }

  function fv_player_admin_youtube() {
    global $fv_fp;

    $value = $fv_fp->_get_option('youtube_browser_chrome', 'standard');
    ?>
    <style>
      #fv_player_youtube .descriptions {
        float: right;
        position: relative;
        width: 50%;
      }

      #fv_player_youtube [data-describe] {
        display: none;
        position: absolute;
        top: 0;
      }
    </style>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td class="first"><label>YouTube UI:</label></td>
        <td>
          <?php
            $radio_butons = array();
            $radio_butons_descriptions = array();

            foreach( array(
              'standard' => array(
                'label' => __( 'Standard', 'fv-player' ),
                'description' => __( 'All of the YouTube embedded player interface will show, including related videos on pause.', 'fv-player' )
              ),
              'reduced' => array(
                'label' => __( 'Reduced', 'fv-player' ),
                'description' => __( 'Show only the video title and the YouTube logo.', 'fv-player' )
              ),
              'none' => array(
                'label' => __( 'None', 'fv-player' ),
                'description' => __( 'Remove everything.', 'fv-player' )
              )
            ) AS $key => $field ) {
              $id = 'youtube_browser_chrome_'.esc_attr($key);

              $radio_button = '<input id="'.$id.'" type="radio" name="youtube_browser_chrome" value="'.esc_attr($key).'"';
              if( $value === $key || wp_json_encode($value) == $key ) { // use wp_json_encode as value can be boolean
                $radio_button .= ' checked="checked"';
              }
              $radio_button .= '</input>';
              $radio_button .= '<label for="'.$id.'">'.$field['label'].'</label><br />';

              $radio_butons[] = $radio_button;

              if( !empty($field['description']) ) {
                $radio_butons_descriptions[$key] = $field['description'];
              }
            }

            echo '<div class="descriptions">';
            foreach( $radio_butons_descriptions AS $key => $description ) {
              echo '<p class="description" data-describe="' . esc_attr( $key ) . '">' . esc_html( $description ).'</p>';
            }
            echo '</div>';

            echo implode( $radio_butons );
          ?>
      </td>
    </tr>
      <?php if( $fv_fp->_get_option( array('pro','youtube_titles_disable') ) ) $fv_fp->_get_checkbox(__('Disable video captions', 'fv-player-pro'), array('pro', 'youtube_titles_disable'), __('Normally the video title is parsed into the shortcode when saving the post, with this setting it won\'t appear.', 'fv-player-pro') ); ?>
      <?php $fv_fp->_get_checkbox(__("Use YouTube Cookies", 'fv-player-pro'), array('pro', 'youtube_cookies'), __("Otherwise FV Player Pro uses the youtube-nocookie.com domain to avoid use of YouTube cookies.", 'fv-player-pro') ); ?>

      <?php
         $fv_fp->_get_input_text( array(
          'key' => array( 'pro', 'youtube_key' ),
          'name' => __('Google Developer Key', 'fv-player-pro'),
          'first_td_class' => 'first',
          'help' => __('Required for a reliable YouTube splash screen and YouTube video duration parsing. Start a new project at <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a> and make sure YouTube Data API is enabled for the project.', 'fv-player-pro'),
          'secret' => true
        ) );
      ?>

      <?php if( $sError = get_option('fv_player_pro_youtube_error') ) : ?>
        <tr>
          <td style="width: 250px"></td>
          <td>
            <p class="description">
              <?php esc_html_e('Last Error', 'fv-player-pro'); ?>: <?php echo esc_html( $sError ); ?>
            </p>
          </td>
        </tr>
      <?php endif; ?>
      <?php if( !function_exists('FV_PLayer_Pro')): ?>
        <tr>
          <td colspan="4">
            <a class="fv-wordpress-flowplayer-save button button-primary" href="#" style="margin-top: 2ex;"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
          </td>
        </tr>
      <?php endif; ?>
      <?php do_action('fv_player_youtube_inputs_after'); ?>
    </table>
    <div class="clear"></div>
    <script>
      jQuery( function($) {
        show_description_youtube_chrome();

        $('[name=youtube_browser_chrome]').on( 'change', show_description_youtube_chrome );

        function show_description_youtube_chrome() {
          $( '#fv_player_youtube [data-describe]' ).hide();
          $( '#fv_player_youtube [data-describe='+$('[name=youtube_browser_chrome]:checked').val()+']' ).show();
        }
      });
    </script>
    <?php
  }

  function get__cached_splash( $splash, $src = false ) {
    global $post;

    if( !$splash && is_string($src) ) {

      $sVideoMeta = isset($post) ? get_post_meta( $post->ID, flowplayer::get_video_key($src, true ), true ) : false;
      if( !empty($sVideoMeta['splash']) ) {
        return $sVideoMeta['splash'];
      }

      // If we have no image we accept it if it's recent
      if( !empty($sVideoMeta['date']) && $sVideoMeta['date'] + 3600 > time() ) {
        return false;
      }

      if( $this->fTimeSpent_AutoSplash < 1 ) {
        global $post;
        if( $video_id = $this->is_youtube($src) ) {
          $video_id = $video_id[1];
          $type = 'youtube';
        }

        if( $video_id ) {
          $tStart = microtime(true);
          $splash = get_option('fv_player_'.$type.'_splash_'.$video_id);
          if( !$splash ) {
            $post_id = !empty($post->ID) ? $post->ID : false;

            $videoData = $this->fetch_yt_data($src, $post_id);
            if( $videoData && isset($videoData['thumbnail']) ) {
              $this->fTimeSpent_AutoSplash += microtime(true) - $tStart;
              update_option( 'fv_player_'.$type.'_splash_'.$video_id, $videoData['thumbnail'], false );
              return $videoData['thumbnail'];
            }

          } else {
            return $splash;

          }

        }
      }
    }

    return $splash;
  }

  public function is_youtube( $sURL ) {

    if(
      preg_match( "~youtube.com/.*?(?:v|list)=([a-zA-Z0-9_-]+)(?:\?|$|&)~i", $sURL, $aDynamic ) ||
      preg_match( "~youtube(?:-nocookie)?.com/(?:embed|live|shorts)/([a-zA-Z0-9_-]+)(?:\?|$|&)~i", $sURL, $aDynamic ) ||
      preg_match( "~youtu.be/([a-zA-Z0-9_-]+)(?:\?|$|&)~i", $sURL, $aDynamic )
    ) {
      $this->bYoutube = true;
      return $aDynamic;
    }
    return false;
  }

  function player_attributes( $aAttributes, $media, $fv_fp ) {
    global $fv_fp;

    $aArgs = func_get_args();

    if( isset($aArgs[2]->aCurArgs['src']) && $this->is_youtube($aArgs[2]->aCurArgs['src']) ) {
      $aAttributes['data-engine'] = 'fvyoutube';
      $fv_fp->aCurArgs['engine'] = 'fvyoutube';
      if( stripos($aAttributes['class'],' is-youtube') === false ) {
        $aAttributes['class'] .= ' is-youtube';
      }
    }

    return $aAttributes;
  }

  function player_item($aItem, $index) {
    global $fv_fp;

    if( isset($aItem['sources'][0]['src']) && $this->is_youtube($aItem['sources'][0]['src']) ) {
      $video = $fv_fp->current_video();

      if( !$video ) {
        return $aItem;
      }

      $attachment_id = $video->getMetaValue('author_thumbnail', true);
      $author_name = $video->getMetaValue('author_name', true);
      $author_url = $video->getMetaValue('author_url', true);

      if( $attachment_id) {
        // get attachment url from attachment id
        $attachment_url = wp_get_attachment_url( $attachment_id );

        if( $attachment_url ) {
          $aItem['author_thumbnail'] = $attachment_url;
        }
      }

      if( $author_name ) {
        $aItem['author_name'] = $author_name;
      }

      if( $author_url ) {
        $aItem['author_url'] = $author_url;
      }

    }

    return $aItem;
  }

	function scripts() {
    global $post;

    //  todo: something better for video checker
    /*if( isset($GLOBALS['fv_fp_scripts']) && isset($GLOBALS['fv_fp_scripts']['fv_flowplayer_admin_test_media']) && count($GLOBALS['fv_fp_scripts']['fv_flowplayer_admin_test_media']) ) {
      foreach( $GLOBALS['fv_fp_scripts']['fv_flowplayer_admin_test_media'] AS $key => $item ) {
        if( !is_array($item[0]) && ( FV_Player_Pro_Vimeo()->is_vimeo(stripslashes($item[0])) || $this->is_youtube(stripslashes($item[0])) ) || apply_filters('fv_player_video_checker_exclude',false,$item[0]) ) {
          unset( $GLOBALS['fv_fp_scripts']['fv_flowplayer_admin_test_media'][$key] );
        }
      }
    }*/

    // Was there any player or do we expect any to load in Ajax?
    if(
      isset($GLOBALS['fv_fp_scripts']) ||
      $this->should_force_load_js()
    ) {

      // If we expect players to load in Ajax, YouTube API needs to
      // be there at all times
      if( $this->should_force_load_js() ) $this->bYoutube = 1;

      $aOptions = array(
        'youtube' => $this->bYoutube,
      );

      // TODO: Put bYouTube into fv_flowplayer_conf
    }
  }

  function set_file_type( $type ) {
    $args = func_get_args();
    if( isset($args[1]) ) {
      if( $this->is_youtube($args[1]) ) {
        $type = "video/youtube";
      }
    }
    return $type;
  }

  function youtube_duration( $iTime ) {
    return $iTime;

    $aArgs = func_get_args();

    if( $iTime == 0 && $aId = $this->is_youtube($aArgs[1]) ) {
      $response = wp_remote_get( 'http://gdata.youtube.com/feeds/api/videos/'.$aId[1].'?v=2&alt=jsonc', array( 'sslverify' => false ) );
      $obj = is_wp_error($response) ? false : @json_decode( wp_remote_retrieve_body($response) );

      if( $obj && isset($obj->data->duration) ) {
        $iTime = $obj->data->duration;
      }

    }

    return $iTime;
  }

  function youtube_splash( $splash, $src = false ) {
    if( !$splash && is_string($src) && $res = $this->is_youtube($src) ) {
      return "https://i.ytimg.com/vi/".$res[1]."/maxresdefault.jpg#auto";
    }
    return $splash;
  }

  public function custom_upload_path( $upload_dir ) {
    $upload_dir['path'] = $upload_dir['basedir'].'/fv-player-youtube-channels';
    $upload_dir['url'] = $upload_dir['baseurl'].'/fv-player-youtube-channels';
    return $upload_dir;
  }

}

function FV_Player_YouTube() {
  return FV_Player_YouTube::_get_instance();
}

FV_Player_YouTube();
