<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Stats {

  var $used = false;
  var $cache_directory = false;

  public function __construct() {
    global $fv_fp;
    $this->cache_directory = WP_CONTENT_DIR."/fv-player-tracking";

    add_action( 'admin_init', array( $this, 'register_meta_boxes' ), 9 );

    add_filter( 'fv_flowplayer_conf', array( $this, 'option' ) );

    add_filter( 'fv_flowplayer_attributes', array( $this, 'shortcode' ), 10, 3 );

    if ( function_exists('wp_next_scheduled') ) {
      if( !wp_next_scheduled( 'fv_player_stats' ) && $fv_fp->_get_option('video_stats_enable')) {
        wp_schedule_event( time(), '5minutes', 'fv_player_stats' );
      } else if( wp_next_scheduled( 'fv_player_stats' ) && !$fv_fp->_get_option('video_stats_enable') ) {
        wp_clear_scheduled_hook( 'fv_player_stats' );
      }
    }

    add_action( 'fv_player_stats', array ( $this, 'parse_cached_files' ) );

    add_action( 'fv_player_update', array( $this, 'db_init' ) );

    // add_action( 'admin_init', array( $this, 'db_init' ) );

    add_action( 'admin_init', array( $this, 'folder_init' ) );

    add_action( 'admin_menu', array( $this, 'stats_link' ), 13 );

    add_filter( 'manage_users_columns', array( $this, 'users_column' ) );
    add_filter( 'manage_users_custom_column', array( $this, 'users_column_content' ), 10, 3 );
    add_filter( 'manage_users_sortable_columns', array( $this, 'users_sortable_columns' ) );

    if( is_admin() ) {
      add_action( 'pre_user_query', array( $this, 'users_sort' ) );
      add_action( 'wp_ajax_fv_player_stats_users_search', array( $this, 'user_stats_search' ) );
    }

  }

  function stats_link() {
    global $fv_fp;
    if ( $fv_fp->_get_option('video_stats_enable') ) {
      add_submenu_page( 'fv_player', 'FV Player Stats', 'Stats', 'manage_options', 'fv_player_stats', 'fv_player_stats_page' );
      add_submenu_page( 'fv_player', 'FV Player User Stats', 'User Stats', 'manage_options', 'fv_player_stats_users', 'fv_player_stats_page' );
    }
  }

  function get_stat_columns() {
    return array( 'play', 'seconds', 'click' );
  }

  public static function get_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'fv_player_stats';
  }

  function db_init( $force = false ) {
    global $fv_fp;

    if( !$force && !$fv_fp->_get_option('video_stats_enable') ) {
      return;
    }

    global $wpdb;
    $table_name = $this->get_table_name();

    $sql = "CREATE TABLE `$table_name` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `id_video` INT(11) NOT NULL,
      `id_player` INT(11) NOT NULL,
      `id_post` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      `guest_user_id` INT(11) NOT NULL,
      `date` DATE NULL DEFAULT NULL,\n";

    foreach( $this->get_stat_columns() AS $column ) {
      $sql .= "`".$column."` INT(11) NOT NULL,\n";
    }

    $sql .= "PRIMARY KEY (`id`),
      INDEX `date` (`date`),
      INDEX `id_video` (`id_video`),
      INDEX `id_player` (`id_player`),
      INDEX `id_post` (`id_post`),
      INDEX `user_id` (`user_id`),
      INDEX `guest_user_id` (`guest_user_id`)
    ) " . $wpdb->get_charset_collate() . ";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta($sql);
  }

  function folder_init( $force = false ) {
    if ( !WP_Filesystem() ) {
      return;
    }

    global $fv_fp;
    global $wp_filesystem;

    if( !$force && !$fv_fp->_get_option('video_stats_enable') ) {
      if( $wp_filesystem->exists( $this->cache_directory ) ) {
        $wp_filesystem->rmdir( $this->cache_directory, true );
      }

      return;
    }

    if( !$wp_filesystem->exists($this->cache_directory) ){
      $wp_filesystem->mkdir( $this->cache_directory );
    }
  }

  function option( $conf ) {
    global $fv_fp, $blog_id;
    if( $this->used || $fv_fp->_get_option('js-everywhere') || $fv_fp->_get_option('video_stats_enable') ) { // we want to enable the tracking if it's used, if FV Player JS is enabled globally or if the tracking is enabled globally
      $conf['fv_stats'] = array(
                                'url' => flowplayer::get_plugin_url().'/controller/track.php',
                                'blog_id' => $blog_id,
                                'user_id' => get_current_user_id(),
                                'nonce'   => wp_create_nonce( 'fv_player_track' ),
                               );
      if( $fv_fp->_get_option('video_stats_enable') ) $conf['fv_stats']['enabled'] = true;
    }

    return $conf;
  }

  function register_meta_boxes() {
    add_meta_box( 'fv_player_stats' , 'Video Stats', array( $this, 'options_html' ), 'fv_flowplayer_settings', 'normal', 'low' );
  }

  function options_html() {
    global $fv_fp;
    ?>
    <p><?php esc_html_e( 'Track user activity on your site. Users who can edit the post are excluded. You can see the stats in the FV Player menu.', 'fv-player' ); ?></p>
    <table class="form-table2">
      <?php
        $fv_fp->_get_checkbox(__( 'Enable', 'fv-player' ), 'video_stats_enable', __('Gives you a daily count of video plays.'), __('Uses a simple PHP script with a cron job to make sure these stats don\'t slow down your server too much.'));
        $fv_fp->_get_checkbox(__( 'Track Guest User IDs', 'fv-player' ), 'video_stats_enable_guest', __('Uses cookies to remember non-logged in users returning to website. Leave disabled to only get summary stats for all non-logged in users.'), '');
      ?>
      <tr>
        <td colspan="4">
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
          <a class="button fv-help-link" href="https://foliovision.com/player/analytics/user-stats" target="_blank">Help</a>
        </td>
      </tr>
    </table>
    <?php
  }

  function shortcode( $attributes, $media, $fv_fp ) {

    if( ! empty( $fv_fp->aCurArgs['stats'] ) || $fv_fp->_get_option('video_stats_enable') ) {
      global $post;

      // Do not track if user can edit the post
      if ( ! empty( $post->ID ) ) {

        // Only check once for performance reasons
        static $user_can_edit_posts;
        if ( ! isset( $user_can_edit_posts ) ) {
          $user_can_edit_posts = current_user_can( 'edit_others_posts' );
        }

        $current_user_is_post_author = ! empty( $post->post_author ) && absint( $post->post_author ) == get_current_user_id();

        // TODO: Also check the FV Player player author
        if ( $user_can_edit_posts || $current_user_is_post_author ) {
          $skip_reason = $user_can_edit_posts ? 'User can edit all posts' : 'User is post author';

          // Store reason for skipping to be able to show console warning if debug is enabled
          if ( $fv_fp->_get_option( 'debug_log' ) ) {
            $attributes['data-fv_stats_skip'] = $skip_reason;
          }

          // Query Monitor plugin integration
          do_action( 'qm/debug', 'Skip for player ' . $fv_fp->hash . ': ' . $skip_reason );

          return $attributes;
        }
      }

      if ( ! empty( $fv_fp->aCurArgs['stats'] ) && $fv_fp->aCurArgs['stats'] != 'no' ) {
        $this->used = true;
      }

      if( !empty($fv_fp->aCurArgs['stats']) ) {
        $attributes['data-fv_stats'] = $fv_fp->aCurArgs['stats'];
      }

      $player_id = 0; // 0 if shortcode

      if( $fv_fp->current_player() ) {
        $player_id = $fv_fp->current_player()->getId();
      }

      if( !empty($post->ID ) ) {
        // TODO: Add signature to avoid faking the stats by users
        $attributes['data-fv_stats_data'] = wp_json_encode( array(
          'player_id' => $player_id,
          'post_id' => $post->ID,
        ) );
      }
    }

    return $attributes;
  }

  /**
   * Process post counters from cache file and update post meta
   * @param  resource &$fp file handler
   * @param  string   $type Type of stats being parsed
   * @return void
   */
  function process_cached_data( &$fp, $type ) {
    global $wpdb;

    $table_name = $this->get_table_name();

    if( !in_array($type, $this->get_stat_columns() ) ) return;

    if( flock( $fp, LOCK_EX ) ) {
      $encoded_data = fgets( $fp );
      $data = json_decode( $encoded_data, true );

      ftruncate( $fp, 0 );
      //UNLOCK, process data later
      flock( $fp, LOCK_UN );

      $json_error = json_last_error();
      if( $json_error !== JSON_ERROR_NONE ) {
        //file_put_contents( ABSPATH . 'failed_json_decode.log', gmdate('r')."\n".var_export( array( 'err' => $json_error, 'data' => $encoded_data ), true )."\n", FILE_APPEND );
        return;
      }

      if( !is_array( $data ) || empty( $data ) )
        return;

      if( is_array($data) ) {
        foreach( $data  AS $index => $item ) {
          $video_id = intval($item['video_id']);
          $player_id = intval($item['player_id']);
          $post_id = intval($item['post_id']);
          $user_id = intval($item['user_id']);
          $guest_user_id = intval($item['guest_user_id']);
          $value = intval($item[$type]);

          if( $user_id ) {
            $meta_key = 'fv_player_stats_'.$type;
            $meta_value = $value + intval( get_user_meta( $user_id, $meta_key, true ) );
            if( $meta_value > 0 ) {
              update_user_meta( $user_id, $meta_key, $meta_value );
            }

          }

          if( $video_id ) {
            global $FV_Player_Db;
            $video = new FV_Player_Db_Video( $video_id, array(), $FV_Player_Db );

            if( $video ) {
              $meta_value = $value + intval($video->getMetaValue('stats_'.$type,true));
              if( $meta_value > 0 ) {
                $video->updateMetaValue( 'stats_'.$type, $meta_value );
              }
            }
          }

          $existing =  $wpdb->get_row( $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}fv_player_stats` WHERE date = %s AND id_video = %d AND id_post = %d AND id_player = %d AND user_id = %d AND guest_user_id = %d", date_i18n( 'Y-m-d', false, true ), $video_id, $post_id, $player_id, $user_id, $guest_user_id ) );

          if( $existing ) {
            $wpdb->update(
              $table_name,
              array(
                $type => $value + $existing->{$type}, // update plays in db
              ),
              array( 'id_video' => $video_id , 'date' => date_i18n( 'Y-m-d', false, true ), 'id_player' => $player_id, 'id_post' => $post_id, 'user_id' => $user_id, 'guest_user_id' => $guest_user_id ), // update by video id, date, player id, post id, user ID and guest user ID
              array(
                '%d'
              ),
              array(
                '%d',
                '%s',
                '%d',
                '%d',
                '%d'
              )
            );
          } else { // insert new row
            $wpdb->insert(
              $table_name,
              array(
                'id_video'  => $video_id,
                'id_player' => $player_id,
                'id_post'   => $post_id,
                'user_id'   => $user_id,
                'guest_user_id' => $guest_user_id,
                'date' => date_i18n( 'Y-m-d', false, true ),
                $type => $value
              ),
              array(
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%s',
                '%d'
              )
            );
          }
        }
      }
    }
    else {
      echo "Error: failed to obtain file lock.";
    }
  }

  /**
   * Loads directory with cache files, and process those, which belongs to current blog
   * @return void
   */
  function parse_cached_files() {
    // just in case...
    $this->db_init( true );
    $this->folder_init( true );

    $cache_files = scandir( $this->cache_directory );
    foreach( $cache_files as $filename ) {
      if( preg_match( '/^([^-]+)-([^\.]+)\.data$/', $filename, $matches ) ) {
        $type = $matches[1];
        if( !in_array($type, $this->get_stat_columns() ) ) continue;

        $blog_id = intval($matches[2]);

        if( get_current_blog_id() != $blog_id ) continue;

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
        $fp = fopen( $this->cache_directory."/".$filename, 'r+');
        $this->process_cached_data( $fp, $type );

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
        fclose( $fp );
      }
    }
  }

  public function top_ten_users_by_plays( $interval, $user_type = 'user' ) {
    global $wpdb;

    $excluded = $this->get_posts_to_exclude();

    $offset  = 0;
    $limit   = 50000;
    $grouped = array();

    // Determine limit by the amount of PHP memory available
    if ( intval( ini_get('memory_limit') ) > 32 ) {
      $limit = intval( ini_get('memory_limit') ) * 800;
    }

    do {
      if( $user_type == 'user' ) {
        $results = $wpdb->get_results(
          // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT user_id, play FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) LIMIT %d, %d",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $excluded['values'],
              array(
                $offset,
                $limit
              )
            )
          )
        );

      } else {
        $results = $wpdb->get_results(
          // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT guest_user_id AS user_id, play FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) AND guest_user_id > 0 LIMIT %d, %d",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $excluded['values'],
              array(
                $offset,
                $limit
              )
            )
          )
        );
      }

      // Group by user ID and sum up the plays, it's faster in PHP than MySQL.
      if ( ! empty( $results ) ) {
        foreach( $results as $row ) {
          $user_id             = $row->user_id;
          $grouped[ $user_id ] = isset( $grouped[ $user_id ] ) ? $grouped[ $user_id ] + $row->play : $row->play;
        }
      }

      $offset += $limit;

    } while( ! empty( $results ) && count( $results ) >= $limit );

    arsort( $grouped );

    $grouped = array_slice( $grouped, 0, 10, true );

    return array_keys( $grouped );
  }

  public function top_ten_users_by_watch_time( $interval, $user_type = 'user' ) {
    global $wpdb;

    $excluded = $this->get_posts_to_exclude();

    $offset  = 0;
    $limit   = 50000;
    $grouped = array();

    // Determine limit by the amount of PHP memory available
    if ( intval( ini_get('memory_limit') ) > 32 ) {
      $limit = intval( ini_get('memory_limit') ) * 800;
    }

    do {
      if( $user_type == 'user' ) {
        $results = $wpdb->get_results(
          // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT user_id, seconds FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) LIMIT %d, %d",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $excluded['values'],
              array(
                $offset,
                $limit
              )
            )
          )
        );

      } else {
        $results = $wpdb->get_results(
          // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT guest_user_id AS user_id, seconds FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) AND guest_user_id > 0 LIMIT %d, %d",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $excluded['values'],
              array(
                $offset,
                $limit
              )
            )
          )
        );
      }

      // Group by user ID and sum up the plays, it's faster in PHP than MySQL.
      if ( ! empty( $results ) ) {
        foreach( $results as $row ) {
          $user_id             = $row->user_id;
          $grouped[ $user_id ] = isset( $grouped[ $user_id ] ) ? $grouped[ $user_id ] + $row->seconds : $row->seconds;
        }
      }

      $offset += $limit;

    } while( ! empty( $results ) && count( $results ) >= $limit );

    arsort( $grouped );

    $grouped = array_slice( $grouped, 0, 10, true );

    return array_keys( $grouped );
  }

  public function top_ten_videos_or_posts_by_plays( $type, $interval, $user_id ) {
    global $wpdb;

    // Sanitize input for SQL
    if ( ! in_array( $type, array( 'post', 'video' ) ) ) {
      $type = 'video';
    }

    $excluded = $this->get_posts_to_exclude();

    if( is_numeric( $user_id ) ) {
      $results = $wpdb->get_col(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT id_" . esc_sql( $type ) . " FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) AND user_id = %d GROUP BY id_" . esc_sql( $type ) . " ORDER BY sum(play) DESC LIMIT 10",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values'],
            array(
              $user_id
            )
          )
        )
      );

    } else {
      $results = $wpdb->get_col(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT id_" . esc_sql( $type ) . " FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) GROUP BY id_" . esc_sql( $type ) . " ORDER BY sum(play) DESC LIMIT 10",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values']
          )
        )
      );
    }

    return $results;
  }

  public function top_ten_videos_by_watch_time( $type, $interval, $user_id ) {
    global $wpdb;

    // Sanitize input for SQL
    if ( ! in_array( $type, array( 'post', 'video' ) ) ) {
      $type = 'video';
    }

    $valid_interval = $this->check_watch_time_in_interval( $interval, $user_id );

    if( !$valid_interval ) {
      return false;
    }

    $excluded = $this->get_posts_to_exclude();

    if( is_numeric( $user_id ) ) {
      $results = $wpdb->get_col(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT id_" . esc_sql( $type ) . " FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) AND user_id = %d GROUP BY id_" . esc_sql( $type ) . " ORDER BY sum(seconds) DESC LIMIT 10",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values'],
            array(
              $user_id
            )
          )
        )
      );

    } else {
      $results = $wpdb->get_col(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT id_" . esc_sql( $type ) . " FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) GROUP BY id_" . esc_sql( $type ) . " ORDER BY sum(seconds) DESC LIMIT 10",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values']
          )
        )
      );
    }

    return $results;
  }

  public function get_video_ad_video_ids( $interval ) {
    global $wpdb;

    $excluded = $this->get_posts_to_exclude();

    $results = $wpdb->get_col(
      // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
      // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
      $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT s.id_video as id_video FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videometa` AS m ON m.id_video = s.id_video WHERE m.meta_key = 'is_video_ad' AND date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) GROUP BY id_video",
        array_merge(
          array(
            $interval[0],
            $interval[1]
          ),
          $excluded['values']
        )
      )
    );

    return $results;
  }

  public function check_watch_time_in_interval( $interval, $user_id ) {
    global $wpdb;

    $excluded = $this->get_posts_to_exclude();

    if( is_numeric( $user_id ) ) {
      $results = $wpdb->get_col(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT id_video FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) AND user_id = %d AND seconds > 0 LIMIT 1",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values'],
            array(
              $user_id
            )
          )
        )
      );

    } else {
      $results = $wpdb->get_col(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT id_video FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) AND seconds > 0 LIMIT 1",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values']
          )
        )
      );
    }

    return !empty($results);
  }

  /**
   * Get post IDs to exclude for stats
   *
   * @return array Array of post IDs with 0 value always included to make sure the query SQL is valid
   */
  public function get_posts_to_exclude() {

    // exclude posts with filter
    $exclude_posts_query_args = apply_filters( 'fv_player_stats_view_exclude_posts_query_args', false );
    if( $exclude_posts_query_args ) {
      $exclude_posts_query = new WP_Query( $exclude_posts_query_args );
      if( !empty($exclude_posts_query->posts) ) {
        // We count +1 for the 0 value
        $placeholders = implode( ', ', array_fill( 0, count( $exclude_posts_query->posts ) + 1, '%d' ) );
      }

      return array(
        'placeholder' => $placeholders,
        // We append the 0 value too
        'values'     => array_merge( array( 0 ), wp_list_pluck( $exclude_posts_query->posts, 'ID' ) ),
      );

    }

    // No posts to exclude? We still return the 0 post ID
    return array(
      'placeholder' => '%d',
      'values'     => array( 0 ),
    );
  }

  public function get_top_user_stats( $metric, $range ) {
    global $wpdb, $fv_fp;

    // dynamic interval based on range
    $interval = self::get_interval_from_range( $range );

    $guest_stats = $fv_fp->_get_option('video_stats_enable_guest');

    $datasets = false;
    $top_ids_user = array();
    $top_ids_arr_user = array();
    $top_ids_guest = array();
    $top_ids_arr_guest = array();
    $top_ids_results_user = array();
    $top_ids_results_guest = array();
    $results_user = array();
    $results_guest = array();
    $datasets_users = array();
    $datasets_guests = array();

    if( $metric == 'play' ) { // play stats
      $top_ids_results_user = $this->top_ten_users_by_plays( $interval, 'user' );
      if( $guest_stats ) $top_ids_results_guest = $this->top_ten_users_by_plays( $interval, 'guest' );
    } else { // watch time stats
      $top_ids_results_user = $this->top_ten_users_by_watch_time( $interval, 'user' );
      if( $guest_stats ) $top_ids_results_guest = $this->top_ten_users_by_watch_time( $interval, 'guest' );
    }

    // if both empty, return false
    if ( empty( $top_ids_results_user ) && empty( $top_ids_results_guest ) ) {
      return false;
    }

    // regular users
    if( !empty($top_ids_results_user) ) {
      $top_ids_arr_user = array_values( $top_ids_results_user );
      $top_ids_user = array_map( 'intval', array_values( $top_ids_arr_user ) );

      $placeholders = implode( ', ', array_fill( 0, count( $top_ids_user ), '%d' ) );

      if( $metric == 'play' ) {
        $results_user = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, user_id, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND user_id IN( $placeholders ) GROUP BY user_id, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids_user
            )
          ),
          ARRAY_A
        );

      } else {
        $results_user = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, user_id, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND user_id IN( $placeholders ) GROUP BY user_id, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids_user
            )
          ),
          ARRAY_A
        );
      }
    }

    // guest users
    if( $guest_stats && !empty($top_ids_results_guest) ) {
      // TODO: Fix if empty, the SQL below will fail
      $top_ids_arr_guest = array_values( $top_ids_results_guest );
      $top_ids_guest = array_map( 'intval', array_values( $top_ids_arr_guest ) );

      $placeholders = implode( ', ', array_fill( 0, count( $top_ids_guest ), '%d' ) );

      if( $metric == 'play' ) {
        $results_guest = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, guest_user_id, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND guest_user_id IN( $placeholders ) GROUP BY guest_user_id, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids_guest
            )
          ),
          ARRAY_A
        );

      } else {
        $results_guest = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, guest_user_id, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND guest_user_id IN( $placeholders ) GROUP BY guest_user_id, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids_guest
            )
          ),
          ARRAY_A
        );
      }
    }

    // process data for regular users
    if( !empty($results_user) ) {
      $datasets_users = $this->process_graph_data( $results_user, $top_ids_arr_user, $range, 'user', $metric );
    }

    // process data for guest users
    if( !empty($results_guest) ) {
      $datasets_guests = $this->process_graph_data( $results_guest, $top_ids_arr_guest, $range, 'guest', $metric );
    }

    // merge datasets
    $datasets = array_merge( $datasets_users, $datasets_guests );

    return $datasets;
  }

  public function get_top_video_watch_time_stats( $type, $range, $user_id ) {
    global $wpdb;

    // dynamic interval based on range
    $interval = self::get_interval_from_range( $range );

    $datasets = false;

    $top_ids_results = $this->top_ten_videos_by_watch_time( $type, $interval, $user_id ); // get top video ids

    if( !empty($top_ids_results) ) {
      $top_ids = array_map( 'intval', array_values( $top_ids_results ) );
      $top_ids[] = 0; // add 0 to make sure the SQL is valid
      $placeholders = implode( ', ', array_fill( 0, count( $top_ids ), '%d' ) );
    } else {
      return false;
    }

    if( is_numeric( $user_id ) ) {
      if( $type == 'video' ) { // video stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_player, id_video, title, src, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date BETWEEN %s AND %s AND id_video IN( $placeholders ) AND user_id = %d GROUP BY id_video, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids,
              array(
                $user_id
              )
            )
          ),
          ARRAY_A
        );
      } else if( $type == 'post' ) { // post stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_post, post_title, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->posts}` AS p ON s.id_post = p.ID WHERE date BETWEEN %s AND %s AND id_post IN( $placeholders ) AND user_id = %d GROUP BY id_post, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids,
              array(
                $user_id
              )
            )
          ),
          ARRAY_A
        );
      }

    } else {
      if( $type == 'video' ) { // video stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_player, id_video, title, src, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date BETWEEN %s AND %s AND id_video IN( $placeholders ) GROUP BY id_video, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids
            )
          ),
          ARRAY_A
        );

      } else if( $type == 'post' ) { // post stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_post, post_title, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->posts}` AS p ON s.id_post = p.ID WHERE date BETWEEN %s AND %s AND id_post IN( $placeholders ) GROUP BY id_post, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids
            )
          ),
          ARRAY_A
        );
      }
    }

    if( !empty($results) ) {
      $datasets = $this->process_graph_data( $results, $top_ids, $range, $type, 'seconds' );
    }

    return $datasets;
  }

  public function get_top_video_post_stats( $type, $range, $user_id ) {
    global $wpdb;

    // dynamic interval based on range
    $interval = self::get_interval_from_range( $range );

    $datasets = false;
    $top_ids_results = $this->top_ten_videos_or_posts_by_plays( $type, $interval, $user_id ); // get top video ids

    if( !empty($top_ids_results) ) {
      $top_ids = array_map( 'intval', array_values( $top_ids_results ) );
      $top_ids[] = 0; // add 0 to make sure the SQL is valid
      $placeholders = implode( ', ', array_fill( 0, count( $top_ids ), '%d' ) );
    } else {
      return false;
    }

    if( is_numeric( $user_id ) ) {
      if( $type == 'video' ) { // video stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_player, id_video, title, src, SUM(play) AS play  FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date BETWEEN %s AND %s AND id_video IN( $placeholders ) AND user_id = %d GROUP BY id_video, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids,
              array(
                $user_id
              )
            )
          ),
          ARRAY_A
        );
      } else if( $type == 'post' ) { // post stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_post, id_video, post_title, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}posts` AS p ON s.id_post = p.ID WHERE date BETWEEN %s AND %s AND id_post IN( $placeholders ) AND user_id = %d GROUP BY id_post, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids,
              array(
                $user_id
              )
            )
          ),
          ARRAY_A
        );
      }

    } else {
      if( $type == 'video' ) { // video stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_player, id_video, title, src, SUM(play) AS play  FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date BETWEEN %s AND %s AND id_video IN( $placeholders ) GROUP BY id_video, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids
            )
          ),
          ARRAY_A
        );
      } else if( $type == 'post' ) { // post stats
        $results = $wpdb->get_results(
          // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date, id_post, id_video, post_title, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}posts` AS p ON s.id_post = p.ID WHERE date BETWEEN %s AND %s AND id_post IN( $placeholders ) GROUP BY id_post, date",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $top_ids
            )
          ),
          ARRAY_A
        );
      }
    }

    if( !empty($results) ) {
      $datasets = $this->process_graph_data( $results, $top_ids, $range, $type );
    }

    return $datasets;
  }

  public function get_top_video_ad_data( $range, $metric ) {
    global $wpdb;

    // dynamic interval based on range
    $interval = self::get_interval_from_range( $range );

    $datasets = false;

    // we track ads based on video
    $type = 'video';

    $top_ids_results = $this->get_video_ad_video_ids( $interval );

    if( !empty($top_ids_results) ) {
      $top_ids = array_map( 'intval', array_values( $top_ids_results ) );
      $top_ids[] = 0; // add 0 to make sure the SQL is valid
      $placeholders = implode( ', ', array_fill( 0, count( $top_ids ), '%d' ) );
    } else {
      return false;
    }

    $results = $wpdb->get_results(
      // Explanation: $placeholders is created above and is a string for $wpdb->prepare(), it uses variable number of placements
      // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
      $wpdb->prepare(
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        "SELECT date, id_player, id_video, title, src, SUM($metric) AS {$metric} FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date BETWEEN %s AND %s AND id_video IN( $placeholders ) GROUP BY id_video, date",
        array_merge(
          array(
            $interval[0],
            $interval[1]
          ),
          $top_ids
        )
      ),
      ARRAY_A
    );

    if( !empty($results) ) {
      $datasets = $this->process_graph_data( $results, $top_ids, $range, $type, $metric );
    }

    return $datasets;
  }

  public function get_player_stats( $player_id, $range) {
    global $wpdb;

    $interval = self::get_interval_from_range( $range );
    $datasets = false;

    $results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT date, id_video, src, title, player_name, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_players` AS p ON s.id_player = p.id JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date BETWEEN %s AND %s AND s.id_player IN( %d ) GROUP BY date, id_video",
        $interval[0],
        $interval[1],
        $player_id
      ),
      ARRAY_A
    );

    if( !empty($results) ) {
      $ids_arr = array();
      foreach( $results as $row ) {
        $ids_arr[] = $row['id_video'];
      }

      // Make sure each video is only considered once, otherwise this ends up multiplying the stats is loading for one player only
      $ids_arr = array_unique( $ids_arr );

      $datasets = $this->process_graph_data( $results, $ids_arr, $range, 'video' );
    }

    return $datasets;
  }

  public function get_users_by_time_range( $range, $user_id = false ) {
    global $wpdb;

    $excluded = $this->get_posts_to_exclude();
    $interval = self::get_interval_from_range( $range );

    if( $user_id ) {
      $result = $wpdb->get_results(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT u.ID, display_name, user_email, SUM( play ) AS play FROM `{$wpdb->users}` AS u LEFT JOIN `{$wpdb->prefix}fv_player_stats` AS s ON u.ID = s.user_id AND date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) WHERE u.ID = %d GROUP BY u.ID ORDER BY display_name",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values'],
            array(
              $user_id
            )
          )
        ),
        ARRAY_A
      );

    } else {
      $result = $wpdb->get_results(
        // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
        $wpdb->prepare(
          // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          "SELECT u.ID, display_name, user_email, SUM( play ) AS play FROM `{$wpdb->users}` AS u LEFT JOIN `{$wpdb->prefix}fv_player_stats` AS s ON u.ID = s.user_id AND date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) GROUP BY u.ID ORDER BY display_name",
          array_merge(
            array(
              $interval[0],
              $interval[1]
            ),
            $excluded['values']
          )
        ),
        ARRAY_A
      );
    }

    if ( ! $result ) {
      $result = array();
    }

    return $result;
  }

  public function get_valid_dates( $user_id ) {
    global $wpdb;

    $excluded = $this->get_posts_to_exclude();

    $dates_all = array( 'this_week' => 'This Week', 'last_week' => 'Last Week', 'this_month' => 'This Month', 'last_month' => 'Last Month' );
    $years = $this->get_all_years();
    $dates_all = $dates_all + $years; // merge
    $dates_valid = array();

    $this_year = (int) gmdate( 'Y' );
    $last_year = $this_year - 1;

    foreach( $dates_all as $key => $value ) {

      $interval = self::get_interval_from_range( $key );

      if( $user_id ) {
        $result = $wpdb->get_results(
          // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) AND user_id = %d LIMIT 1",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $excluded['values'],
              array(
                $user_id
              )
            )
          ),
          ARRAY_A
        );

      } else {
        $result = $wpdb->get_results(
          // Explanation: $excluded['placeholder'] comes from get_posts_to_exclude() and is a string for $wpdb->prepare(), it uses variable number of placements
          // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
          $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT date FROM `{$wpdb->prefix}fv_player_stats` WHERE date BETWEEN %s AND %s AND id_post NOT IN ( {$excluded['placeholder']} ) LIMIT 1",
            array_merge(
              array(
                $interval[0],
                $interval[1]
              ),
              $excluded['values']
            )
          ),
          ARRAY_A
        );
      }

      if( $key == $this_year) {
        $key = 'this_year';
        $value = 'This Year';
      } else if( $key == $last_year ) {
        $key = 'last_year';
        $value = 'Last Year';
      }

      $dates_valid[$key] = array();

      if( !empty($result) ) {
        $dates_valid[$key]['disabled'] = false;
      } else {
        $dates_valid[$key]['disabled'] = true;
      }

      $dates_valid[$key]['value'] = $value;
    }

    return $dates_valid;
  }

  public function get_valid_interval( $user_id ) {
    // we need to check every interval for user to check if there is any data
    $intervals = array(
      'this_week',
      'last_week',
      'this_month',
      'last_month',
    );

    $years = $this->get_all_years();

    $intervals = $intervals + $years; // merge

    // TODO: optimize performance, no need to use SUM or ORDER BY, limit 1 would be enough
    foreach( $intervals as $k => $interval ) {
      $data = $this->get_top_video_watch_time_stats( 'video', $interval, $user_id );

      // if there is no data for this interval, remove it from the list
      if( empty($data) ) {
        unset($intervals[$k]);
      }

    }

    return $intervals;
  }

  public static function get_interval_from_range( $range ) {

    if( strcmp( 'this_week', $range ) === 0 ) { // this week
      $start = gmdate('Y-m-d', strtotime('-7 days') );
      $end = gmdate('Y-m-d', time() );

    } else if( strcmp( 'last_week', $range ) === 0 ) { // last week
      $previous_week = strtotime("-1 week +1 day");

      // convert to datetime
      $previous_week = gmdate('Y-m-d', $previous_week);

      // respect the start of week day by wordpress
      $start_end_week = get_weekstartend($previous_week);

      $start = gmdate('Y-m-d', $start_end_week['start']);
      $end = gmdate('Y-m-d', $start_end_week['end']);

    } else if( strcmp( 'this_month', $range ) === 0 ) { // this month
      $start = gmdate('Y-m-01');
      $end = gmdate('Y-m-t');

    } else if( strcmp( 'last_month', $range ) === 0 ) { // last month
      $first_day_last_month = strtotime('first day of last month');
      $last_day_last_month = strtotime('last day of last month');

      $start = gmdate('Y-m-01', $first_day_last_month );
      $end = gmdate('Y-m-t', $last_day_last_month );

    } else if( strcmp( 'this_year', $range ) === 0 ) { // this year
      $start = gmdate('Y-01-01');
      $end = gmdate('Y-12-31');

    } else if( strcmp( 'last_year', $range ) === 0 ) { // last year
      $start = gmdate('Y-01-01', strtotime('-1 year'));
      $end = gmdate('Y-12-31', strtotime('-1 year'));

    } else if( is_numeric($range)) { // specific year like 2021
      $start = intval( $range ) . '-01-01';
      $end = intval( $range ) . '-12-31';
    }

    return array( $start, $end);
  }

  /**
   * Get the desired date range
   *
   * @param string|int $range this_week, last_week, this_month, last_month, this_year, last_year or year number
   * @param mixed $base_date (optional) The base date to use for this_week
   * @return array            All the days in the date range in YYYY-MM-DD format.
   */
  private function get_dates_in_range( $range, $base_date = false ) {
    $dates = array();

    $time = time();
    if ( $base_date ) {
      $time = strtotime( $base_date );
    }

    if( strcmp( 'this_week', $range ) === 0 ) {
      $end_day = gmdate('Y-m-d', $time );
      $start_day = gmdate('Y-m-d', strtotime( '-7 days', $time ) );
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'last_week', $range ) === 0 ) {
      $previous_week = strtotime("-1 week +1 day");

      // convert to datetime
      $previous_week = gmdate('Y-m-d', $previous_week);

      // respect the start of week day by wordpress
      $start_end_week = get_weekstartend($previous_week);

      $start_week = gmdate('Y-m-d', $start_end_week['start']);
      $end_week = gmdate('Y-m-d', $start_end_week['end']);

      $dates = $this->get_days_between_dates( $start_week, $end_week );
    } else if( strcmp( 'this_month', $range ) === 0 ) {
      $start_day = gmdate('Y-m-01');
      $end_day = gmdate('Y-m-d');
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'last_month', $range ) === 0 ) {
      $first_day_last_month = strtotime('first day of last month');
      $last_day_last_month = strtotime('last day of last month');

      $start_day = gmdate('Y-m-01', $first_day_last_month );
      $end_day = gmdate('Y-m-t', $last_day_last_month );

      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'this_year', $range ) === 0 ) {
      $start_day = gmdate('Y-01-01');
      $end_day = gmdate('Y-m-d');
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'last_year', $range ) === 0 ) {
      $start_day = gmdate('Y-01-01', strtotime('-1 year'));
      $end_day = gmdate('Y-12-31', strtotime('-1 year'));
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( is_numeric($range) ) { // get dates for specific year like 2021
      $start_day = intval( $range ) . '-01-01';
      $end_day = intval( $range ) . '-12-31';
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    }

    return $dates;
  }

  function get_all_years() {
    global $wpdb;

    $years = array();

    $oldest_year = (int) $wpdb->get_var("SELECT YEAR(date) FROM {$wpdb->prefix}fv_player_stats ORDER BY id ASC LIMIT 1");

    // add every year from oldest to current, when oldest is 2021 and current is 2025, it will add 2021, 2022, 2023, 2024, 2025
    for( $i = $oldest_year; $i <= gmdate('Y'); $i++ ) {
      $j = strval($i);
      $years[$j] = $j;
    }

    // reorder years from newest to oldest
    $years = array_reverse( $years, true );

    return $years;
  }

  private function get_days_between_dates( $start_day, $end_day ) {
    $dates = array();

    $current = strtotime($start_day);
    $end = strtotime($end_day);

    while( $current <= $end ) {
      $dates[] = gmdate('Y-m-d', $current);
      $current = strtotime('+1 day', $current);
    }

    return $dates;
  }

  private function get_date_labels( $results ) {
    $date_labels = array();

    foreach( $results as $row) {
      if( !in_array( $row['date'], $date_labels ) ) {
        $date_labels[strtotime($row['date'])] = $row['date'];
      }
    }

    ksort($date_labels);

    return array_values($date_labels);
  }

  /**
   * Group the database result rows by the video or post ID for the desired date range.
   *
   * @param array $raw_db_results Each item is array like:
   *                                array(
   *                                  'date' => '2024-09-03',
   *                                  'id_player' => '14',
   *                                  'id_video' => '912',
   *                                  'title' => 'My Video',
   *                                  'play' => '1',
   *                                ),
   *                                array(
   *                                  'date' => '2024-09-05',
   *                                  'id_player' => '171',
   *                                  'id_video' => '912',
   *                                  'title' => 'My Video',
   *                                  'play' => '1',
   *                                ),
   *                                array(
   *                                  'date' => '2024-09-07',
   *                                  'id_player' => '14',
   *                                  'id_video' => '912',
   *                                  'title' => 'My Video',
   *                                  'play' => '1',
   *                                )
   *
   * @param mixed $top_ids_arr
   * @param string|int $range this_week, last_week, this_month, last_month, this_year, last_year or year number
   * @param string $type      video or post
   * @param string $metric    play or seconds or clicks
   * @param string $base_date (optional) The base date to use for $range
   *
   * @return array            Summary of the daily video plays per video or post (see $type) by id_video or is_post:
   *                            912 => array(
   *                              '2024-09-02' => array( 'play' => 0 ),
   *                              'name' => 'My Video',
   *                              '2024-09-03' => array( 'play' => '1' ),
   *                              '2024-09-04' => array( 'play' => 0 ),
   *                              '2024-09-05' => array( 'play' => 1 ),
   *                              '2024-09-06' => array( 'play' => 0 ),
   *                              '2024-09-07' => array( 'play' => 1 ),
   *                              '2024-09-08' => array( 'play' => 0 ),
   *                              '2024-09-09' => array( 'play' => 0 ),
   *                            ),
   */
  private function process_graph_data( $raw_db_results, $top_ids_arr, $range, $type, $metric = 'play', $base_date = false ) {
    $datasets = array();

    $date_labels = $this->get_dates_in_range( $range, $base_date );

    // order data for graph,
    foreach( $top_ids_arr as $id ) {
      foreach( $date_labels as $date ) {
        foreach( $raw_db_results as $row) {
          if( ( ( $type == 'video' || $type == 'player' ) && ( isset($row['id_' . $type ]) && $row['id_' . $type ] == $id ) ) || ( isset($row['user_id']) && $row['user_id'] == $id ) || ( isset($row['guest_user_id']) && $row['guest_user_id'] == $id ) || ( isset($row['id_post']) && $row['id_post'] == $id ) ) {
            if( !isset($datasets[$id]) ) {
              $datasets[$id] = array();
            }

            // aggregate data by date
            if( strcmp( $date, $row['date'] ) == 0 ) { // date row exists
              if( $metric === 'play' && isset($row['play']) ) {
                if( isset($datasets[$id][$date]['play']) ) {
                  $datasets[$id][$date]['play'] += $row['play'];
                } else {
                  $datasets[$id][$date]['play'] = $row['play'];
                }
              }

              if( $metric === 'seconds' && isset($row['seconds']) ) {
                if( isset($datasets[$id][$date]['seconds']) ) {
                  $datasets[$id][$date]['seconds'] += $row['seconds'];
                } else {
                  $datasets[$id][$date]['seconds'] = (int) $row['seconds'];
                }
              }

              if( $metric === 'click' && isset($row['click']) ) {
                if( isset($datasets[$id][$date]['click']) ) {
                  $datasets[$id][$date]['click'] += $row['click'];
                } else {
                  $datasets[$id][$date]['click'] = $row['click'];
                }
              }

            } else { // date row dont exists, add 0 plays/seconds - dont overwrite if value already set
              if( $metric === 'play' && !isset( $datasets[$id][$date]['play']) ) $datasets[$id][$date]['play'] = 0;
              if( $metric === 'seconds' && !isset( $datasets[$id][$date]['seconds']) ) $datasets[$id][$date]['seconds'] = 0;
              if( $metric === 'click' && !isset( $datasets[$id][$date]['click']) ) $datasets[$id][$date]['click'] = 0;
            }

            // add labels
            if( !isset($datasets[$id]['name']) ) {
              if( $type == 'video' || $type == 'player' ) {
                $datasets[$id]['name'] = $this->get_video_name( $row );
              } else if( $type == 'post' ) {
                $datasets[$id]['name'] = !empty($row['post_title'] ) ? $row['post_title'] : 'id_post_' . $row['id_post'] ;
              } else if( $type == 'user' ) {
                $user_data = get_userdata( intval($row['user_id']) );

                if( $user_data === false ) {
                  $datasets[$id]['name'] = 'Guest Users';
                } else {
                  $datasets[$id]['name'] = $user_data->display_name;
                }
              } else if( $type == 'guest') {
                $datasets[$id]['name'] = 'Guest ' . $row['guest_user_id'];
              }
            }
          }
        }
      }
    }

    $datasets['date-labels'] = $date_labels; // date will be used as X axis label

    return $datasets;
  }

  function get_video_name( $row ) {
    if( ! empty( $row['title'] ) ) {
      return $row['title'];
    }

    $src = $row['src'];

    // check if youtube
    if( FV_Player_YouTube()->is_youtube( $src ) ) {
      // get youtube id
      preg_match( '/[\\?\\&]v=([^\\?\\&]+)/', $src, $matches );
      if( isset($matches[1]) ) {
        $id = $matches[1];
        $name = 'Youtube: ' . $id;

        return $name;
      }
    }

    // check if vimeo
    if( function_exists('FV_Player_Pro_Vimeo') && FV_Player_Pro_Vimeo()->is_vimeo($src) ) {
      // get vimeo id
      preg_match( '/vimeo\.com\/([0-9]+)/', $src, $matches );
      if( isset($matches[1]) ) {
        $id = $matches[1];
        $name = 'Vimeo: ' . $id;

        return $name;
      }

    }

    // parse title
    $name = flowplayer::get_title_from_src($src);

    return $name;
  }

  function users_column( $columns ) {
    global $fv_fp;
    if ( $fv_fp->_get_option('video_stats_enable') ) {
      $columns['fv_player_stats_user_play_today'] = "Video Plays Today";
      $columns['fv_player_stats_user_seconds_today'] = "Video Minutes Today";
    }
    return $columns;
  }

  function users_column_content( $content, $column_name, $user_id ) {
    $field = false;

    if ( 'fv_player_stats_user_play_today' === $column_name ) {
      $field = 'play';
    } else if ( 'fv_player_stats_user_seconds_today' === $column_name ) {
      $field = 'seconds';
    }

    if( $field ) {

      // TODO: Preload to avoid too many SQL queries
      global $wpdb;

      if ( 'play' === $field ) {
        $val = $wpdb->get_var(
          $wpdb->prepare(
            "SELECT sum(play) FROM {$wpdb->prefix}fv_player_stats WHERE user_id = %d AND date = %s",
            $user_id,
            date_i18n( 'Y-m-d', false, true )
          )
        );
      } else if ( 'seconds' === $field ) {
        $val = $wpdb->get_var(
          $wpdb->prepare(
            "SELECT sum(seconds) FROM {$wpdb->prefix}fv_player_stats WHERE user_id = %d AND date = %s",
            $user_id,
            date_i18n( 'Y-m-d', false, true )
          )
        );
      }

      if ( $val ) {

        if( 'seconds' === $field ) {
          $val = ceil($val/60) . ' min';
        }

        $url = add_query_arg(
          array(
            'page'    => 'fv_player_stats_users',
            'user_id' => $user_id
          ),
          admin_url( 'admin.php' )
        );
        $content = '<a href="' . $url . '">' . $val . '</a>';
      }
    }

    return $content;
  }

  function users_sortable_columns( $columns ) {
    $columns['fv_player_stats_user_play_today'] = 'fv_player_stats_user_play_today';
    $columns['fv_player_stats_user_seconds_today'] = 'fv_player_stats_user_seconds_today';
    return $columns;
  }

  function users_sort($userquery) {
    global $wpdb;

    $field = false;

    if ( 'fv_player_stats_user_play_today' === $userquery->query_vars['orderby'] ) {
      $field = 'play';
    } else if ( 'fv_player_stats_user_seconds_today' === $userquery->query_vars['orderby'] ) {
      $field = 'seconds';
    }

    if ( $field ) {
      $userquery->query_fields .= ", sum(" . $field . ") AS " . $field . " ";
      $userquery->query_from .= " LEFT OUTER JOIN {$wpdb->prefix}fv_player_stats AS stats ON ($wpdb->users.ID = stats.user_id) ";
      $userquery->query_where .= " AND stats.date = '" . date_i18n( 'Y-m-d', false, true ) . "' ";
      $userquery->query_orderby = " GROUP BY wp_users.ID ORDER BY " . $field . " ".($userquery->query_vars["order"] == "ASC" ? "ASC " : "DESC ");
    }
  }

  function user_stats_search() {
    if( isset($_GET['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'fv-player-stats-users-search' ) && isset($_GET['q']) && isset($_GET['date_range']) ) {
      $search = sanitize_text_field( $_GET['q'] );
      $date_range = sanitize_text_field( $_GET['date_range'] );

      // search for users by login, nicename or email
      $users = get_users( array(
        'search' => '*' . $search . '*',
        'search_columns' => array( 'user_login', 'display_name' ,'user_nicename', 'user_email' ),
      ) );

      $results = array();
      foreach( $users AS $user ) {
        $data = $this->get_users_by_time_range( $date_range, $user->ID ); // check if user has any data in the selected date range

        if( $data ) {
          $plays = $data[0]['play'] ? $data[0]['play'] : 0;

          $item = array(
            'id' => $user->ID, // used as value for option
            'text' => $user->display_name . '-' . $user->user_email . ' ( ' . number_format_i18n( $plays, 0) . ' plays )' // used as label for option
          );

          if( !$plays ) {
            $item['disabled'] = true; // disable option if user has no data in the selected date range
          }

          $results[] = $item;
        }
      }

      echo wp_json_encode( array( 'results' => $results ) );
    }

    die();
  }

}

global $FV_Player_Stats;
$FV_Player_Stats = new FV_Player_Stats();

function fv_player_stats_top( $args = array() ) {
  $args = wp_parse_args( $args, array(
    'taxonomy' => false,
    'term' => false ) );

  extract($args);

  global $wpdb;

  if( $taxonomy && $term ) {
    $raw = $wpdb->get_results(
      $wpdb->prepare("
      SELECT p.id, vm.id_video, vm.meta_value AS stats_play, pm.meta_value AS post_id
      FROM {$wpdb->prefix}fv_player_videometa AS vm
        JOIN {$wpdb->prefix}fv_player_players AS p ON FIND_IN_SET(vm.id_video, p.videos) > 0
        JOIN {$wpdb->prefix}fv_player_playermeta AS pm ON p.id = pm.id_player
        INNER JOIN {$wpdb->prefix}term_relationships AS tr ON (pm.meta_value = tr.object_id)
        INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        INNER JOIN {$wpdb->prefix}terms AS t ON (t.term_id = tt.term_id)
      WHERE vm.meta_key = 'stats_play'
        AND pm.meta_key = 'post_id'
        AND tt.taxonomy = %s
        AND t.name = %s
        ORDER BY CAST(vm.meta_value AS unsigned) DESC",
        $taxonomy,
        $term
      )
    );

  } else {
    $raw = $wpdb->get_results( "
      SELECT p.id, vm.id_video, vm.meta_value AS stats_play, pm.meta_value AS post_id
      FROM {$wpdb->prefix}fv_player_videometa AS vm
        JOIN {$wpdb->prefix}fv_player_players AS p ON FIND_IN_SET(vm.id_video, p.videos) > 0
        JOIN {$wpdb->prefix}fv_player_playermeta AS pm ON p.id = pm.id_player
      WHERE vm.meta_key = 'stats_play'
        AND pm.meta_key = 'post_id'
        ORDER BY CAST(vm.meta_value AS unsigned) DESC"
    );
  }

  // sice there might be multiple players for a single post_id we count these together
  $top = array();
  foreach( $raw AS $record ) {
    if( empty($top[$record->post_id]) ) $top[$record->post_id] = 0;
    $top[$record->post_id] += $record->stats_play;
  }

  asort($top);
  $top = array_reverse($top,true);

  return $top;
}
