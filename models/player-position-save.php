<?php
class FV_Player_Position_Save {

  public function __construct() {
    add_action( 'fv_player_update',  array( $this, 'plugin_update_database' ), 9 );
    add_action( 'wp_ajax_fv_wp_flowplayer_video_position_save', array($this, 'video_position_save' ) );
    add_filter( 'fv_player_item', array( $this, 'set_last_position' ), 10, 3 );
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
      PRIMARY KEY  (id),
      KEY user_id (user_id),
      KEY video_id (video_id)
    )" . $wpdb->get_charset_collate() . ";";

    // create table to store position in playlists
    $sql_playlist_positions = "CREATE TABLE ".$wpdb->prefix."fv_player_user_playlist_positions (
      id int(11) NOT NULL auto_increment,
      user_id int(11) NOT NULL,
      player_id int(11) NOT NULL,
      item_index int(11) NOT NULL,
      PRIMARY KEY  (id),
      KEY user_id (user_id),
      KEY player_id (player_id)
    )" . $wpdb->get_charset_collate() . ";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql_user_video_positions );
    dbDelta( $sql_playlist_positions );
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
    global $wpdb;

    if( is_numeric( $video_id) ) { // id
      $video_id = intval($video_id);
      $value = $wpdb->get_var( $wpdb->prepare(
        "SELECT $type FROM ".$wpdb->prefix."fv_player_user_video_positions WHERE user_id = %d AND video_id = %d",
        $user_id,
        $video_id,
      ) );
    } else { // legacy
      $value = $wpdb->get_var( $wpdb->prepare(
        "SELECT $type FROM ".$wpdb->prefix."fv_player_user_video_positions WHERE user_id = %d AND legacy_video_id = %s and video_id = 0",
        $user_id,
        $video_id,
      ) );
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

    $index = $wpdb->get_var( $wpdb->prepare(
      "SELECT item_index FROM ".$wpdb->prefix."fv_player_user_playlist_positions WHERE user_id = %d AND player_id = %d",
      $user_id,
      $player_id
    ) );

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

    if( is_numeric($video_id) ) { // id
      $video_id = intval($video_id);
      $exits = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM ".$wpdb->prefix."fv_player_user_video_positions WHERE user_id = %d AND video_id = %d AND legacy_video_id = %s",
        $user_id,
        $video_id,
        ''
      ) );
    } else { // legacy
      $exits = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM ".$wpdb->prefix."fv_player_user_video_positions WHERE user_id = %d AND video_id = %d AND legacy_video_id = %s",
        $user_id,
        0,
        $video_id
      ) );
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

    // check if the record already exists
    $exits = $wpdb->get_var( $wpdb->prepare(
      "SELECT id FROM ".$wpdb->prefix."fv_player_user_playlist_positions WHERE user_id = %d AND player_id = %d",
      $user_id,
      $player_id
    ) );

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
    }
    return $aItem;
  }

  public function video_position_save() {
    // TODO: XSS filter for POST values?
    // check if videoTimes is not a JSON-encoded value, which will happen
    // when the request came from a navigation.sendBeacon() call instead of the usual AJAX call
    if( isset( $_POST['videoTimes'] ) ) {
      $decoded_times = json_decode(urldecode($_POST['videoTimes']), true);

      if ($decoded_times !== false) {
        $_POST['videoTimes'] = $decoded_times;
      }
    }

    if( isset( $_POST['playlistItems'] ) ) {
      $decoded_playlists = json_decode(urldecode($_POST['playlistItems']), true);

      if ($decoded_playlists !== false) {
        $_POST['playlistItems'] = $decoded_playlists;
      }
    }

    $success = false;

    if ( is_user_logged_in() ) {
      $uid = get_current_user_id();
      if (isset($_POST['videoTimes']) && ($times = $_POST['videoTimes']) && count($times)) {
        foreach ($times as $record) {
          $name = $this->get_extensionless_file_name($record['name']);
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

          // Did the user saw the full video?
          if( !empty($record['saw']) && $record['saw'] == true ) {
            $this->set_video_position($uid, $name, 'finished', 1);
            $this->delete_video_postion($uid, $name, 'top_position');
          }
        }

        // What are the videos which user saw in full length?
        if( !empty($_POST['sawVideo']) && is_array($_POST['sawVideo']) ) {
          foreach ($_POST['sawVideo'] as $record) {
            $this->set_video_position($uid, $name, 'finished', 1);
            $this->delete_video_postion($uid, $name, 'top_position');
          }
        }

        $success = true;
      }

      if (isset($_POST['playlistItems']) && ($playlistItems = $_POST['playlistItems']) && count($playlistItems)) {
        foreach ($playlistItems as $playeritem) {
          $this->set_player_position($uid, $playeritem['player'], $playeritem['item']);
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
$FV_Player_Position_Save = new FV_Player_Position_Save();
