<?php

class FV_Player_Positions_Meta2Table_Conversion extends FV_Player_Conversion_Base {

  function __construct() {
    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    parent::__construct( array(
      'title' => 'FV Player PositionsMeta2Table Conversion',
      'slug' => 'positions_meta2table',
      'matchers' => array(

      ),
      'help' => __("This converts position values from usermeta to fv_player_user_video_positions table", 'fv-wordpress-flowplayer')
    ) );

    $this->screen_fields = array(
      'ID',
      'Name',
      'Result',
      'Error',
    );

  }

  /**
   * Override parent method
   *
   * @return int
   */
  function get_count() {
    global $wpdb;
    return (int) $wpdb->get_var( "SELECT COUNT(*) AS count FROM `$wpdb->usermeta` WHERE meta_key LIKE 'fv_wp_flowplayer_%'" );
  }

  function get_items( $offset, $limit ) {
    global $wpdb;

    $meta_data = $wpdb->get_results( "SELECT * FROM `$wpdb->usermeta` WHERE meta_key LIKE 'fv_wp_flowplayer_%' LIMIT {$offset},{$limit}" );

    return $meta_data;
  }

  function convert_one($meta) {
    $output_data = array(); // output for html
    $errors = array(); // all errors for export

    // get meta data
    $meta_key = $meta->meta_key;
    $meta_value = $meta->meta_value;
    $user_id = $meta->user_id;

    $type = '';

    if( strpos($meta_key, 'fv_wp_flowplayer_position') !== false ) { // last
      $type = 'last_position';
    } else if ( strpos($meta_key, 'fv_wp_flowplayer_saw') !== false ) { // finished
      $type = 'finished';
    } else if ( strpos($meta_key, 'fv_wp_flowplayer_top_position') !== false ) { // top
      $type = 'top_position';
    } else if ( strpos($meta_key, 'fv_wp_flowplayer_player_playlist') !== false  ) { // playlist
      $type = 'playlist';
    }

    if( $type == 'playlist' ) {
      preg_match('/fv_wp_flowplayer_player_playlist_(\d+)/', $meta_key, $matches);
      if( isset( $matches[1] ) ) {
        $playlist_id = $matches[1];

        if( $this->is_live() ) {

          // check if its db video or external
          $row_exitst = $this->position_row_exists( $user_id, $playlist_id, 'playlist' );

          $result = $this->insert_update_playlist_row( $user_id, $playlist_id, $meta_value, $row_exitst );

          if( $result ) {
            $output_data[] = array(
              'ID' => $user_id,
              'Name' => $meta_key,
              'output' => 'Playlist position updated',
              'error' => ''
            );
          } else { // failed to update
           $output_data[] = array(
              'ID' => $user_id,
              'Name' => $meta_key,
              'output' => 'Playlist position failed to update',
              'error' => 'Failed to update playlist position'
            );

            $errors[] = array(
              'ID' => $user_id,
              'Name' => $meta_key
            );
          }

        } else {
          $output_data[] = array(
            'ID' => $user_id,
            'Name' => $meta_key,
            'output' => 'Playlist position updated',
            'error' => ''
          );
        }
      } else {
        // failed to get playlist id
        $output_data[] = array(
          'ID' => $user_id,
          'Name' => $meta_key,
          'output' => 'Playlist position failed to update',
          'error' => 'Cannot get playlist id'
        );

        $errors[] = array(
          'ID' => $user_id,
          'Name' => $meta_key
        );
      }

    } else {
      preg_match('/fv_wp_flowplayer_\w+_(\w+)/', $meta_key, $matches);
      if( isset( $matches[1] ) ) {
        $video_id = $matches[1];

        // check if video exitst in db
        $video_exitst = $this->video_exists( $video_id );
        $row_exitst = $this->position_row_exists( $user_id, $video_id, 'position' );

        if( $this->is_live() ) {
          $result = $this->insert_update_video_row( $user_id, $video_id, $type, $meta_value, $row_exitst, $video_exitst );

          if( $result ) {
            $output_data[] = array(
              'ID' => $user_id,
              'Name' => $meta_key,
              'output' => 'Video position updated',
              'error' => ''
            );
          } else {
            $output_data[] = array(
              'ID' => $user_id,
              'Name' => $meta_key,
              'output' => 'Video position failed to update',
              'error' => 'Failed to update position'
            );

            $errors[] = array(
              'ID' => $user_id,
              'Name' => $meta_key
            );
          }

        } else {
          $output_data[] = array(
            'ID' => $user_id,
            'Name' => $meta_key,
            'output' => 'Video position updated',
            'error' => ''
          );
        }

      } else {
        $output_data[] = array(
          'ID' => $user_id,
          'Name' => $meta_key,
          'output' => 'Video position failed to update',
          'error' => 'Cannot get video id'
        );

        // failed to get video id
        $errors[] = array(
          'ID' => $user_id,
          'Name' => $meta_key
        );
      }

    }

    return array(
      'output_data' => $output_data,
      'errors' => $errors
    );

  }

  /**
   * Insert or update video row
   *
   * @param int $user_id
   * @param int $video_id
   * @param string $type
   * @param int $value
   * @param boolean $row_exitst
   * @param boolean $video_exitst
   *
   * @return boolean $res result of insert or update
   */
  function insert_update_video_row( $user_id, $video_id, $type, $value, $row_exitst, $video_exitst ) {
    global $wpdb;

    if( !$video_exitst ) { // non db video
      $legacy_id = $video_id;
      $video_id = 0;
    } else { // db video
      $legacy_id = '';
      $video_id = intval($video_id);
    }

    if( $row_exitst ) {
      $res = $wpdb->update(
        $wpdb->prefix . 'fv_player_user_video_positions',
        array(
          $type => $value
        ),
        array(
          'user_id' => $user_id,
          'video_id' => $video_id,
          'legacy_video_id' => $legacy_id
        )
      );

      $res = is_numeric($res);
    } else {
      $res = $wpdb->insert(
        $wpdb->prefix . 'fv_player_user_video_positions',
        array(
          $type => $value,
          'user_id' => $user_id,
          'video_id' => $video_id,
          'legacy_video_id' => $legacy_id
        )
      );

      $res = !empty($res);
    }

    return $res;
  }

  /**
   * Insert or update playlist row
   *
   * @param int $user_id
   * @param int $playlist_id
   * @param int $value
   * @param boolean $exitst
   *
   * @return boolean $res result of insert or update
   */
  function insert_update_playlist_row( $user_id, $playlist_id, $value, $exitst ) {
    global $wpdb;

    if( $exitst ) {
      $res = $wpdb->update(
        $wpdb->prefix . 'fv_player_user_playlist_positions',
        array(
          'item_index' => $value
        ),
        array(
          'user_id' => $user_id,
          'player_id' => $playlist_id
        )
      );

      $res = is_numeric($res);
    } else {
      $res = $wpdb->insert(
        $wpdb->prefix . 'fv_player_user_playlist_positions',
        array(
          'user_id' => $user_id,
          'player_id' => $playlist_id,
          'item_index' => $value
        )
      );

      $res = !empty($res);
    }

    return $res;
  }

  /**
   * Check if video exitst in db
   *
   * @param int $video_id
   *
   * @return object|null
   */
  function video_exists( $video_id ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}fv_player_videos` WHERE id = {$video_id}" );

    return $row;
  }

  /**
   * Check if position or playlist row exitst
   *
   * @param int $user_id
   * @param int|string $id
   * @param string $type
   * @param boolean $video_exitst
   *
   * @return object|null
   */
  function position_row_exists( $user_id, $id, $type, $video_exitst = false ) {
    global $wpdb;

    if( $type == 'position' ) {

      if( $video_exitst ) { // db video
        $row = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}fv_player_user_video_positions` WHERE user_id = {$user_id} AND video_id = {$id}" );
      } else { // legacy video
        $row = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}fv_player_user_video_positions` WHERE user_id = {$user_id} AND legacy_video_id = {$id}" );
      }

    }

    if( $type == 'playlist' ) {
      $row = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}fv_player_user_playlist_positions` WHERE user_id = {$user_id} AND player_id = {$id}" );
    }

    return $row;
  }

  function conversion_button() {
    ?>
      <tr>
        <td><label>Convert position meta values stored in usermeta to new table :</label></td>
        <td>
          <p class="description">
            <input type="button" class="button" value="<?php _e('Convert positions', 'fv-player-pro'); ?>" style="margin-top: 2ex;" onclick="location.href='<?php echo admin_url('admin.php?page=' . $this->screen ) ?>'; "/>
          </p>
        </td>
      </tr>
    <?php
  }

  function iterate_data( $data ) {
    $conversions_output = array();
    $convert_error = false;

    foreach( $data as $meta ) {
      $result = $this->convert_one( $meta );

      if( !empty($result['errors']) ) {
        $convert_error = true;
      }

      $conversions_output = array_merge( $conversions_output, $result['output_data'] );
    }

    return array(
      'convert_error' => $convert_error,
      'conversions_output' => $conversions_output
    );
  }

  function build_output_html( $data, $percent_done ) {
    $html = array();

    foreach( $data as $output_data ) {
      $html[] = "<tr><td>". $output_data['ID'] . "</td><td>". $output_data['Name'] ."</td><td>". $output_data['output'] ."</td><td>". $output_data['error'] ."</td></tr>";
    }

    if( empty($html) && $percent_done == 0 ) {
      $html[] = "<tr><td colspan='4'>No matching meta found.</td></tr>";
    }

    return $html;
  }

}

global $FV_Player_Positions_Meta2Table_Conversion;
$FV_Player_Positions_Meta2Table_Conversion = new FV_Player_Positions_Meta2Table_Conversion;
