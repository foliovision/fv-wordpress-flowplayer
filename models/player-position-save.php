<?php
class FV_Player_Position_Save {

  public function __construct() {
    add_action( 'wp_ajax_fv_wp_flowplayer_video_position_save', array($this, 'video_position_save') );
    add_filter('fv_player_item', array($this, 'set_last_position'), 10, 3 );
    add_filter('fv_flowplayer_admin_default_options_after', array( $this, 'player_position_save_admin_default_options_html' ) );
    
    add_filter( 'fv_flowplayer_attributes', array( $this, 'shortcode' ), 10, 3 );
  }

  public static function get_extensionless_file_name($path) {
    $arr = explode('/', $path);
    $video_name = end($arr);

    // Do not accept HLS playlist file names as these are often index.m3u8 or stream.m3u8
    // Use folder name instead
    if( strpos($video_name, ".m3u8") !== false ) {
      unset($arr[count($arr)-1]);
      $video_name = end($arr);
    }

    return pathinfo($video_name, PATHINFO_FILENAME);
  }

  public function set_last_position( $aItem, $index, $aArgs ) {
    global $fv_fp;
    // we only use the first source to check for stored position,
    // since other sources would be alternatives (in quality, etc.)
    if (
      ( !empty($fv_fp->aCurArgs['saveposition']) || $fv_fp->_get_option('video_position_save_enable') ) &&
      is_user_logged_in() &&
      is_array($aItem) &&
      isset($aItem['sources']) &&
      isset($aItem['sources'][0])
    ) {
      
      $try = array();
      if( $fv_fp->current_player() ) {
        $aVideos = $fv_fp->current_player()->getVideos();
        if( $aVideos && !empty($aVideos[$index]) ) {
          $try[] = $aVideos[$index]->getId();
        }
      }
      $try[] = $this->get_extensionless_file_name($aItem['sources'][0]['src']);

      foreach( $try AS $name ) {
        if( $metaPosition = get_user_meta( get_current_user_id(), 'fv_wp_flowplayer_position_' . $name, true ) ) {
          $aItem['sources'][0]['position'] = intval($metaPosition);
          break;
        }
      }
      
      foreach( $try AS $name ) {
        if( $metaPosition = get_user_meta( get_current_user_id(), 'fv_wp_flowplayer_saw_' . $name, true ) ) {
          $aItem['sources'][0]['saw'] = true;
          break;
        }
      }
    }
    return $aItem;
  }

  public function video_position_save() {
    // TODO: XSS filter for POST values?
    // check if videoTimes is not a JSON-encoded value, which will happen
    // when the request came from a navigation.sendBeacon() call instead of the usual AJAX call
    $decoded_times = json_decode(urldecode($_POST['videoTimes']), true);
    $decoded_playlists = json_decode(urldecode($_POST['playlistItems']), true);

    if ($decoded_times !== false) {
      $_POST['videoTimes'] = $decoded_times;
    }

    if ($decoded_playlists !== false) {
      $_POST['playlistItems'] = $decoded_playlists;
    }

    $success = false;

    if ( is_user_logged_in() ) {
      $uid = get_current_user_id();
      if (isset($_POST['videoTimes']) && ($times = $_POST['videoTimes']) && count($times)) {
        
        foreach ($times as $record) {
          $name = $this->get_extensionless_file_name($record['name']);
          if( intval($record['position']) == 0 ) {
            delete_user_meta($uid, 'fv_wp_flowplayer_position_'.$name );
          } else {
            update_user_meta($uid, 'fv_wp_flowplayer_position_'.$name, $record['position']);
          }
          
          if( !empty($record['saw']) && $record['saw'] == true ) {
            update_user_meta($uid, 'fv_wp_flowplayer_saw_'.$name, true);
          }
        }
        
        if( !empty($_POST['sawVideo']) && is_array($_POST['sawVideo']) ) {
          foreach ($_POST['sawVideo'] as $record) {
            update_user_meta($uid, 'fv_wp_flowplayer_saw_'.$this->get_extensionless_file_name($record['name']), true);
          }
        }
        
        $success = true;
      }

      if (isset($_POST['playlistItems']) && ($playlistItems = $_POST['playlistItems']) && count($playlistItems)) {
        foreach ($playlistItems as $playeritem) {
          update_user_meta($uid, 'fv_wp_flowplayer_player_playlist_'.$playeritem['player'], $playeritem['item']);
        }

        $success = true;
      }

      if( $success ) {
        wp_send_json_success();
      }
    } else {
      wp_send_json_error();
    }
  }

  function player_position_save_admin_default_options_html() {
    global $fv_fp;
    $fv_fp->_get_checkbox(__('Remember video position', 'fv-wordpress-flowplayer'), 'video_position_save_enable', __('Stores the last video play position for users, so they can continue watching from where they left.'), __('It stores in <code>wp_usermeta</code> database table for logged in users and in a localStorage or cookie for guest users.'));
  }

  function shortcode( $attributes, $media, $fv_fp ) {
    if( !empty($fv_fp->aCurArgs['saveposition']) ) {
      $attributes['data-save-position'] = $fv_fp->aCurArgs['saveposition'];
    }

    if ( $fv_fp->_get_option('video_position_save_enable') || !empty($fv_fp->aCurArgs['saveposition']) ) {
      $player_id = false;

      if( $fv_fp->current_player() ) { // db player
        $player_id = $fv_fp->current_player()->getId();
      }

      if( $player_id ) { // add id to data item if db player
        $attributes['data-player-id'] = $player_id;

        $metaItem = get_user_meta( get_current_user_id(), 'fv_wp_flowplayer_player_playlist_' . $player_id, true );

        if ( $metaItem >= 0 ) {
          // playlist item restore
          $attributes['data-playlist_start'] = intval($metaItem) + 1; // playlist-start-position module starts from 0
        }
      }
    }

    return $attributes;
  }
}
$FV_Player_Position_Save = new FV_Player_Position_Save();