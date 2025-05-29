<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Position_Save {

  /**
   * Stores cache of user video positions
   *
   * @var array
   */
  private static $cache = array();

  public function __construct() {
    add_action( 'fv_player_update',  array( $this, 'plugin_update_database' ), 9 );
    add_action( 'wp_ajax_fv_wp_flowplayer_video_position_save', array($this, 'video_position_save' ) );
    add_filter( 'fv_player_item', array( $this, 'set_last_positions' ), 10, 3 );
    add_filter( 'fv_flowplayer_admin_default_options_after', array( $this, 'player_position_save_admin_default_options_html' ) );
    add_filter( 'fv_flowplayer_attributes', array( $this, 'shortcode' ), 10, 3 );
  }

  function plugin_update_database() {
    global $wpdb;

    // create table to store user video positions
    $sql_user_video_positions = "CREATE TABLE ".$wpdb->prefix."fv_player_user_video_positions (
      id int(11) NOT NULL auto_increment,
      user_id int(11) NOT NULL,
      video_id int(11) NOT NULL,
      last_position int(11) NOT NULL,
      top_position int(11) NOT NULL,
      finished tinyint(1) NOT NULL,
      legacy_video_id varchar(255) NOT NULL,
      ab_start int(11) NOT NULL,
      ab_end int(11) NOT NULL,
      last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY  (id),
      KEY user_id (user_id),
      KEY video_id (video_id),
      Key legacy_video_id (legacy_video_id)
    )" . $wpdb->get_charset_collate() . ";";

    // create table to store position in playlists
    $sql_playlist_positions = "CREATE TABLE ".$wpdb->prefix."fv_player_user_playlist_positions (
      id int(11) NOT NULL auto_increment,
      user_id int(11) NOT NULL,
      player_id int(11) NOT NULL,
      item_index int(11) NOT NULL,
      last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY  (id),
      KEY user_id (user_id),
      KEY player_id (player_id)
    )" . $wpdb->get_charset_collate() . ";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql_user_video_positions );
    dbDelta( $sql_playlist_positions );
  }

  /**
   * Set cache to static variable
   *
   * @param int $user_id
   * @param string $type video, playlist
   *
   * @return array
   */
  private function set_cache($user_id, $type) {
    global $wpdb;

    if( !isset(self::$cache[$user_id]) ) {
      self::$cache[$user_id] = array();
    }

    if( $type == 'video') {
      self::$cache[$user_id][$type] = $wpdb->get_results( $wpdb->prepare(
        "SELECT video_id, last_position, top_position, finished, legacy_video_id, ab_start, ab_end FROM `{$wpdb->prefix}fv_player_user_video_positions` WHERE user_id = %d",
        $user_id
      ) );
    }

    if( $type == 'playlist' ) {
      self::$cache[$user_id][$type] = $wpdb->get_results( $wpdb->prepare(
        "SELECT player_id, item_index FROM `{$wpdb->prefix}fv_player_user_playlist_positions` WHERE user_id = %d",
        $user_id
      ) );
    }

    return self::$cache[$user_id][$type];
  }

  /**
   * Get cache from static variable
   *
   * @param int $user_id
   * @param string $type video, playlist
   *
   * @return array
   */
  private function get_cache($user_id, $type) {
    if ( ! isset( self::$cache[$user_id][$type] ) ) {
      $this->set_cache($user_id, $type);
    }

    return self::$cache[$user_id][$type];
  }

  /**
   * Get video position
   *
   * @param int $user_id
   * @param int|string $video_id
   * @param string $type last_position, top_position, finished
   *
   * @return int
   */
  function get_video_position( $user_id, $video_id, $type ) {
    $cache = self::get_cache($user_id, 'video');
    $value = 0;

    if( is_numeric($video_id) ) { // id
      $video_id = intval($video_id);

      if(is_array($cache)) {
        foreach ($cache as $cache_item) {
          if ($cache_item->video_id == $video_id) {
            $value = $cache_item->$type;
            break;
          }
        }
      }
    } else { // legacy_video_id
      if( is_array($cache)) {
        foreach ($cache as $cache_item) {
          if ($cache_item->legacy_video_id == $video_id) {
            $value = $cache_item->$type;
            break;
          }
        }
      }
    }

    if( is_numeric($value) ) {
      $value = intval($value);
    } else {
      $value = 0;
    }

    return $value;
  }

  /**
   * Delete video position and set it to 0
   *
   * @param int $user_id
   * @param int|string $video_id
   * @param int $type
   *
   * @return void
   */
  function delete_video_postion( $user_id, $video_id, $type ) {
    global $wpdb;

    $legacy_video_id = '';
    if( !is_numeric($video_id) ) {
      $legacy_video_id = $video_id;
      $video_id = 0;
    } else {
      $video_id = intval($video_id);
    }

    $wpdb->update(
      $wpdb->prefix."fv_player_user_video_positions",
      array(
        $type => 0, // dont delete the record, just set the value to 0
      ),
      array(
        'user_id' => $user_id,
        'video_id' => $video_id,
        'legacy_video_id' => $legacy_video_id,
      ),
      array(
        '%d',
      ),
      array(
        '%d',
        '%d',
        '%s',
      )
    );

    // update cache
    self::set_cache($user_id, 'video');
  }

  /**
   * Get player position
   *
   * @param int $user_id
   * @param int $player_id
   *
   * @return int;
   */
  function get_player_position( $user_id, $player_id ) {
    global $wpdb;

    $index = 0;

    $cache = self::get_cache($user_id, 'playlist');

    if( is_array($cache) ) {
      foreach ($cache as $cache_item) {
        if ($cache_item->player_id == $player_id) {
          $index = $cache_item->item_index;
          break;
        }
      }
    }

    if( is_numeric($index) ) {
      $index = intval($index);
    } else {
      $index = 0;
    }

    return $index;
  }

  /**
   * Save video position
   *
   * @param int $user_id
   * @param int|string $video_id
   * @param string $type
   * @param int $value
   *
   * @return void
   */
  function set_video_position( $user_id, $video_id, $type, $value) {
    global $wpdb;

    $cache = self::get_cache($user_id, 'video');
    $exits = false;

    if( is_numeric($video_id) ) { // id
      $video_id = intval($video_id);

      if( is_array($cache) ) {
        foreach ($cache as $cache_item) {
          if ($cache_item->video_id == $video_id) {
            $exits = true;
            break;
          }
        }
      }
    } else { // legacy_video_id
      if( is_array($cache) ) {
        foreach ($cache as $cache_item) {
          if ($cache_item->legacy_video_id == $video_id) {
            $exits = true;
            break;
          }
        }
      }

    }

    // video id and legacy
    $legacy_video_id = '';
    if( !is_numeric($video_id) ) {
      $legacy_video_id = $video_id;
      $video_id = 0;
    }

    // check if the record already exists
    if( $exits ) { // update position
      $wpdb->update(
        $wpdb->prefix."fv_player_user_video_positions",
        array(
          $type => $value,
        ),
        array(
          'user_id' => $user_id,
          'video_id' => $video_id,
          'legacy_video_id' => $legacy_video_id,
        )
      );
    } else { // insert new position
      $wpdb->insert(
        $wpdb->prefix."fv_player_user_video_positions",
        array(
          'user_id' => $user_id,
          'video_id' => $video_id,
          'legacy_video_id' => $legacy_video_id,
          $type => $value,
        )
      );
    }

    // update cache
    self::set_cache($user_id, 'video');
  }

  /**
   * Save player position
   *
   * @param int $user_id
   * @param int $player_id
   * @param int $index
   *
   * @return void
   */
  function set_player_position( $user_id, $player_id, $index ) {
    global $wpdb;

    $cache = self::get_cache($user_id, 'playlist');
    $exits = false;

    if( is_array($cache) ) {
      // check if the record already exists
      foreach ($cache as $cache_item) {
        if ($cache_item->player_id == $player_id) {
          $exits = true;
          break;
        }
      }
    }

    if( $exits ) { // update index
      $wpdb->update(
        $wpdb->prefix."fv_player_user_playlist_positions",
        array(
          'item_index' => $index,
        ),
        array(
          'user_id' => $user_id,
          'player_id' => $player_id,
        )
      );
    } else { // insert new index
      $wpdb->insert(
        $wpdb->prefix."fv_player_user_playlist_positions",
        array(
          'user_id' => $user_id,
          'player_id' => $player_id,
          'item_index' => $index,
        )
      );
    }

    // update cache
    self::set_cache($user_id, 'playlist');
  }

  /**
   * Get extensionless file name from URL.
   * For HLS streams take the folder name instead of the file name,
   * as the file name is often index.m3u8 or stream.m3u8.
   *
   * @param string $url
   *
   * @return string Video name created from the URL.
   */
  public static function get_extensionless_file_name( $url ) {
    $arr        = explode( '/', $url );
    $video_name = end( $arr );

    // Do not accept HLS playlist file names as these are often index.m3u8 or stream.m3u8
    // Use folder name instead
    if ( strpos( $video_name, ".m3u8" ) !== false ) {
      unset( $arr[ count( $arr ) - 1 ] );
      $video_name = end( $arr );
    }

    $video_name = pathinfo( $video_name, PATHINFO_FILENAME );

    $video_name = apply_filters( 'fv_player_position_save_file_name', $video_name, $url );

    return $video_name;
  }

  public function set_last_positions( $aItem, $index, $aArgs ) {

    if ( did_action( 'wp_ajax_fv_player_db_load' ) ) {
      return $aItem;
    }

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

      // Try with the video ID first
      $try = array();
      if( $fv_fp->current_player() ) {
        $aVideos = $fv_fp->current_player()->getVideos();
        if( $aVideos && !empty($aVideos[$index]) ) {
          $try[] = $aVideos[$index]->getId();
        }
      }
      // ...then try with the video filename
      $try[] = $this->get_extensionless_file_name($aItem['sources'][0]['src']);

      foreach( $try AS $name ) {
        $metaPosition = $this->get_video_position( get_current_user_id(), $name, 'last_position' );

        if( $metaPosition ) {
          $aItem['sources'][0]['position'] = $metaPosition;
          break;
        }
      }

      foreach( $try AS $name ) {
        $metaPosition = $this->get_video_position( get_current_user_id(), $name, 'top_position' );

        if( $metaPosition ) {
          $aItem['sources'][0]['top_position'] = $metaPosition;
          break;
        }
      }

      foreach( $try AS $name ) {
        $metaPosition = $this->get_video_position( get_current_user_id(), $name, 'finished' );

        if( $metaPosition ) {
          $aItem['sources'][0]['saw'] = true;
          break;
        }
      }

      foreach( $try AS $name ) {
        $metaPositionStart = $this->get_video_position( get_current_user_id(), $name, 'ab_start' );
        $metaPositionEnd = $this->get_video_position( get_current_user_id(), $name, 'ab_end' );

        // at least one of the values must be set and the start must be smaller than the end
        if( ( $metaPositionStart || $metaPositionEnd ) && $metaPositionStart < $metaPositionEnd ) {
          $aItem['sources'][0]['ab_start'] = $metaPositionStart;
          $aItem['sources'][0]['ab_end'] = $metaPositionEnd;
          break;
        }
      }

    }
    return $aItem;
  }

  public function video_position_save() {

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_player_video_position_save' ) ) {
      wp_send_json_error();
      exit;
    }

    // TODO: XSS filter for POST values?
    // check if videoTimes is not a JSON-encoded value, which will happen
    // when the request came from a navigation.sendBeacon() call instead of the usual AJAX call
    if( isset( $_POST['videoTimes'] ) ) {
      // TODO: How to sanitize JSON?
      $decoded_times = json_decode(urldecode($_POST['videoTimes']), true);

      if ($decoded_times !== false) {
        $_POST['videoTimes'] = $decoded_times;
      }
    }

    if( isset( $_POST['playlistItems'] ) ) {
      // TODO: How to sanitize JSON?
      $decoded_playlists = json_decode(urldecode($_POST['playlistItems']), true);

      if ($decoded_playlists !== false) {
        $_POST['playlistItems'] = $decoded_playlists;
      }
    }

    $success = false;

    if ( is_user_logged_in() ) {
      $uid = get_current_user_id();
      if ( ! empty( $_POST['videoTimes'] ) ) {
        foreach ( $_POST['videoTimes'] as $record) {
          $name = $this->get_extensionless_file_name( sanitize_text_field( $record['name'] ) );
          if( intval($record['position']) == 0 ) {
            $this->delete_video_postion($uid, $name, 'last_position');
          } else {
            $position = intval($record['position']);
            $top_position = intval($record['top_position']);

            $previous_position = $this->get_video_position($uid, $name, 'last_position');
            $previous_top_position = $this->get_video_position($uid, $name, 'top_position');
            $saw = $this->get_video_position($uid,  $name, 'finished');
            $this->set_video_position($uid,  $name, 'last_position', $position);

            // Store the top position if user didn't see the full video
            // and if it's the same or bigger than what it was before
            // and if it's bigger than the last position
            $max = max( array( $previous_top_position, $previous_position, $position, $top_position ) );
            if( !$saw && $max >= $previous_top_position && $max > $position ) {
              $this->set_video_position($uid, $name, 'top_position', $max);

            // Otherwise get rid of it
            } else {
              $this->delete_video_postion($uid, $name, 'top_position');
            }
          }

          // ab loop times
          if ( ! empty( $record['ab_start'] ) && ! empty( $record['ab_end'] ) ) {
            $this->set_video_position($uid, $name, 'ab_start', intval($record['ab_start']));
            $this->set_video_position($uid, $name, 'ab_end', intval($record['ab_end']));
          } else {
            $this->delete_video_postion($uid, $name, 'ab_start');
            $this->delete_video_postion($uid, $name, 'ab_end');
          }

          // Did the user saw the full video?
          if( !empty($record['saw']) && sanitize_key( $record['saw'] ) == true ) {
            $this->set_video_position($uid, $name, 'finished', 1);
            $this->delete_video_postion($uid, $name, 'top_position');
          }
        }

        // What are the videos which user saw in full length?
        if( !empty($_POST['sawVideo']) && is_array($_POST['sawVideo']) ) {
          foreach ($_POST['sawVideo'] as $record) {

            // TODO: How does this target a specific video?
            $this->set_video_position($uid, $name, 'finished', 1);
            $this->delete_video_postion($uid, $name, 'top_position');
          }
        }

        $success = true;
      }

      if ( ! empty( $_POST['playlistItems'] ) ) {
        foreach( $_POST['playlistItems'] as $playeritem ) {
          $this->set_player_position( absint( $uid ), absint( $playeritem['player'] ), absint( $playeritem['item'] ) );
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
    $fv_fp->_get_checkbox(__( 'Remember video position', 'fv-player' ), 'video_position_save_enable', __('Stores the last video play position for users, so they can continue watching from where they left.'), __('It stores in <code>wp_usermeta</code> database table for logged in users and in a localStorage or cookie for guest users.'));
  }

  function shortcode( $attributes, $media, $fv_fp ) {

    if ( did_action( 'wp_ajax_fv_player_db_load' ) ) {
      return $attributes;
    }

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

        $user_id = get_current_user_id();
        if( $user_id ) {
          $metaItem = $this->get_player_position($user_id, $player_id);

          if ( $metaItem >= 0 ) {
            // playlist item restore
            $attributes['data-playlist_start'] = intval($metaItem) + 1; // playlist-start-position module starts from 0
          }
        }
      }
    }

    return $attributes;
  }
}

global $FV_Player_Position_Save;
$FV_Player_Position_Save = new FV_Player_Position_Save();
