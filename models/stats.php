<?php
class FV_Player_Stats {

  var $used = false;
  var $cache_directory = false;

  public function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    global $fv_fp;
    $this->cache_directory = WP_CONTENT_DIR."/fv-player-tracking";

    add_filter( 'fv_flowplayer_admin_default_options_after', array( $this, 'options_html' ) );
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
    return array( 'play', 'seconds' );
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
      `date` DATE NULL DEFAULT NULL,\n";

    foreach( $this->get_stat_columns() AS $column ) {
      $sql .= "`".$column."` INT(11) NOT NULL,\n";
    }

    $sql .= "PRIMARY KEY (`id`),
      INDEX `date` (`date`),
      INDEX `id_video` (`id_video`),
      INDEX `id_player` (`id_player`),
      INDEX `id_post` (`id_post`),
      INDEX `user_id` (`user_id`)
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
                                'user_id' => get_current_user_id()
                               );
      if( $fv_fp->_get_option('video_stats_enable') ) $conf['fv_stats']['enabled'] = true;

    }
    return $conf;
  }

  function options_html() {
    global $fv_fp;
    $fv_fp->_get_checkbox(__('Video Stats', 'fv-wordpress-flowplayer'), 'video_stats_enable', __('Gives you a daily count of video plays.'), __('Uses a simple PHP script with a cron job to make sure these stats don\'t slow down your server too much.'));
  }

  function shortcode( $attributes, $media, $fv_fp ) {
    if( !empty($fv_fp->aCurArgs['stats']) ) {
      if( $fv_fp->aCurArgs['stats'] != 'no' ) {
        $this->used = true;
      }
      $attributes['data-fv_stats'] = $fv_fp->aCurArgs['stats'];
    }

    if( !empty($fv_fp->aCurArgs['stats']) || $fv_fp->_get_option('video_stats_enable') ) {
      global $post;

      $player_id = 0; // 0 if shortcode

      if( $fv_fp->current_player() ) {
        $player_id = $fv_fp->current_player()->getId();
      }

      if( !empty($post->ID ) ) {
        // TODO: Add signature to avoid faking the stats by users
        $attributes['data-fv_stats_data'] = json_encode( array(
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
        //file_put_contents( ABSPATH . 'failed_json_decode.log', date('r')."\n".var_export( array( 'err' => $json_error, 'data' => $encoded_data ), true )."\n", FILE_APPEND );
        return;
      }

      if( !is_array( $data ) || empty( $data ) )
        return;

      if( is_array($data) ) {
        foreach( $data  AS $index => $item ) {
          foreach( $item as $item_name => $item_value ) {
            if( is_int($item_value) && (intval($item_value) >= 0 || ( strcmp( $item_name, 'play' ) == 0 && intval($item_value) > 0 ) )) {
              continue;
            }

            continue 2;
          }

          $video_id = intval($item['video_id']);
          $player_id = intval($item['player_id']);
          $post_id = intval($item['post_id']);
          $user_id = intval($item['user_id']);
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

          $existing =  $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE date = %s AND id_video = %d AND id_post = %d AND id_player = %d AND user_id = %d", date_i18n( 'Y-m-d' ), $video_id, $post_id, $player_id, $user_id ) );

          if( $existing ) {
            $wpdb->update(
              $table_name,
              array(
                $type => $value + $existing->{$type}, // update plays in db
              ),
              array( 'id_video' => $video_id , 'date' => date_i18n( 'Y-m-d' ), 'id_player' => $player_id, 'id_post' => $post_id, 'user_id' => $user_id ), // update by video id, date, player id, post id and user ID
              array(
                '%d'
              ),
              array(
                '%d',
                '%s',
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
                'date' => date_i18n( 'Y-m-d' ),
                $type => $value
              ),
              array(
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

        $fp = fopen( $this->cache_directory."/".$filename, 'r+');
        $this->process_cached_data( $fp, $type );
        fclose( $fp );
      }
    }
  }

  public function top_ten_users_by_plays( $interval ) {
    global $wpdb;

    $excluded_posts = $this->get_posts_to_exclude();

    $results = $wpdb->get_col( "SELECT user_id FROM `{$wpdb->prefix}fv_player_stats` WHERE $interval $excluded_posts GROUP BY user_id ORDER BY sum(play) DESC LIMIT 10");

    return $results;
  }

  public function top_ten_users_by_watch_time( $interval ) {
    global $wpdb;

    $excluded_posts = $this->get_posts_to_exclude();

    $results = $wpdb->get_col( "SELECT user_id FROM `{$wpdb->prefix}fv_player_stats` WHERE $interval $excluded_posts GROUP BY user_id ORDER BY sum(seconds) DESC LIMIT 10");

    return $results;
  }

  public function top_ten_videos_by_plays( $interval, $user_check ) {
    global $wpdb;

    $excluded_posts = $this->get_posts_to_exclude();

    $results = $wpdb->get_col( "SELECT id_video FROM `{$wpdb->prefix}fv_player_stats` WHERE $interval $excluded_posts $user_check GROUP BY id_video ORDER BY sum(play) DESC LIMIT 10");

    return $results;
  }

  public function top_ten_videos_by_watch_time( $interval, $user_check ) {
    global $wpdb;

    $excluded_posts = $this->get_posts_to_exclude();

    $results = $wpdb->get_col( "SELECT id_video FROM `{$wpdb->prefix}fv_player_stats` WHERE $interval $excluded_posts $user_check GROUP BY id_video ORDER BY sum(seconds) DESC LIMIT 10");

    return $results;
  }

  public function get_posts_to_exclude() {
    $excluded_posts = '';

    // exclude posts with filter
    $exclude_posts_query_args = apply_filters( 'fv_player_stats_view_exclude_posts_query_args', false );
    if( $exclude_posts_query_args ) {
      $exclude_posts_query = new WP_Query( $exclude_posts_query_args );
      if( !empty($exclude_posts_query->posts) ) {
        $excluded_posts = implode( ', ', wp_list_pluck( $exclude_posts_query->posts, 'ID' ) );
        $excluded_posts = ' AND id_post NOT IN ( '.$excluded_posts.' )';
      }
    }

    return $excluded_posts;
  }

  public function get_top_user_stats( $metric, $range ) {
    global $wpdb;

    // dynamic interval based on range
    $interval = self::get_interval_from_range( $range );

    $datasets = false;
    $top_ids = array();
    $top_ids_arr = array();

    if( $metric == 'play' ) {
      $top_ids_results = $this->top_ten_users_by_plays( $interval );
    } else {
      $top_ids_results = $this->top_ten_users_by_watch_time( $interval );
    }

    if( !empty($top_ids_results) ) {
      $top_ids_arr = array_values( $top_ids_results );
      $top_ids = implode( ',', array_values( $top_ids_arr ) );
    } else {
      return false;
    }

    if( $metric == 'play' ) {
      $results = $wpdb->get_results( "SELECT date, user_id, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE $interval AND user_id IN( $top_ids ) GROUP BY user_id, date", ARRAY_A );
    } else {
      $results = $wpdb->get_results( "SELECT date, user_id, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE $interval AND user_id IN( $top_ids ) GROUP BY user_id, date", ARRAY_A );
    }

    if( isset($_GET['martinv']) ) {
      var_dump ( 'debug get_top_user_stats', $wpdb->last_query );
      die();
    }

    if( !empty($results) ) {
      $datasets = $this->process_graph_data( $results, $top_ids_arr, $range, 'user', $metric );
    }

    return $datasets;
  }

  public function get_top_video_watch_time_stats( $range, $user_id ) {
    global $wpdb;

    // dynamic interval based on range
    $interval = self::get_interval_from_range( $range );

    // dynamic filter based on user
    $user_check = $this->where_user( $user_id );

    $type = 'video';
    $datasets = false;
    $top_ids = array();
    $top_ids_arr = array();

    $top_ids_results = $this->top_ten_videos_by_watch_time( $interval, $user_check ); // get top video ids

    if( !empty($top_ids_results) ) {
      $top_ids_arr = array_values( $top_ids_results );
      $top_ids = implode( ',', array_values( $top_ids_arr ) );
    } else {
      return false;
    }

    $results = $wpdb->get_results( "SELECT date, id_player, id_video, caption, src, SUM(seconds) AS seconds FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE $interval AND id_video IN( $top_ids ) $user_check GROUP BY id_video, date", ARRAY_A );

    if( !empty($results) ) {
      $datasets = $this->process_graph_data( $results, $top_ids_arr, $range, $type, 'seconds' );
    }

    return $datasets;
  }

  public function get_top_video_post_stats( $type, $range, $user_id ) {
    global $wpdb;

    // dynamic interval based on range
    $interval = self::get_interval_from_range( $range );

    // dynamic filter based on user
    $user_check = $this->where_user( $user_id );

    $datasets = false;
    $top_ids = array();
    $top_ids_arr = array();
    $top_ids_results = $this->top_ten_videos_by_plays( $interval, $user_check ); // get top video ids

    if( !empty($top_ids_results) ) {
      $top_ids_arr = array_values( $top_ids_results );
      $top_ids = implode( ',', array_values( $top_ids_arr ) );
    } else {
      return false;
    }

    if( $type == 'video' ) { // video stats
      $results = $wpdb->get_results( "SELECT date, id_player, id_video, caption, src, SUM(play) AS play  FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE $interval AND id_video IN( $top_ids ) $user_check GROUP BY id_video, date", ARRAY_A );
    } else if( $type == 'post' ) { // post stats
      $results = $wpdb->get_results( "SELECT date, id_post, id_video, post_title, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}posts` AS p ON s.id_post = p.ID WHERE $interval AND id_video IN( $top_ids ) $user_check GROUP BY id_post, date;
      ", ARRAY_A );
    }

    if( !empty($results) ) {
      $datasets = $this->process_graph_data( $results, $top_ids_arr, $range, $type );
    }

    return $datasets;
  }

  public function get_player_stats( $player_id, $range) {
    global $wpdb;

    $interval = self::get_interval_from_range( $range );
    $datasets = false;

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT date, id_video, src, caption, player_name, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_players` AS p ON s.id_player = p.id JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE $interval AND s.id_player IN( '%d' ) GROUP BY date, id_video", $player_id ), ARRAY_A );

    if( !empty($results) ) {
      $ids_arr = array();
      foreach( $results as $row ) {
        $ids_arr[] = $row['id_video'];
      }

      $datasets = $this->process_graph_data( $results, $ids_arr, $range, 'player' );
    }

    return $datasets;
  }

  public function get_users_by_time_range( $range, $user_id = false ) {
    global $wpdb;

    $excluded_posts = $this->get_posts_to_exclude();
    $interval = self::get_interval_from_range( $range );

    if( $user_id ) {
      $user_id = intval( $user_id );
      $user_check ="WHERE u.ID = $user_id";
    } else {
      $user_check = '';
    }

    $result = $wpdb->get_results( "SELECT u.ID, display_name, user_email, SUM( play ) AS play FROM `{$wpdb->users}` AS u LEFT JOIN `{$wpdb->prefix}fv_player_stats` AS s ON u.ID = s.user_id AND $interval $excluded_posts $user_check GROUP BY u.ID ORDER BY display_name", ARRAY_A );

    if ( ! $result ) {
      $result = array();
    }

    return $result;
  }

  public function get_valid_interval( $user_id ) {
    // we need to check every interval for user to check if there is any data
    $intervals = array(
      'this_week',
      'last_week',
      'this_month',
      'last_month',
      'this_year',
      'last_year'
    );

    // TODO: optimize performance, no need to use SUM or ORDER BY, limit 1 would be enough
    foreach( $intervals as $k => $interval ) {
      $data = $this->get_top_video_watch_time_stats( $interval, $user_id );

      // if there is no data for this interval, remove it from the list
      if( empty($data) ) {
        unset($intervals[$k]);
      }

    }

    return $intervals;
  }

  private function where_user( $user_id ) {
    $where = '';

    if( is_numeric( $user_id ) ) {
      $where = "AND user_id = " . intval($user_id);
    }

    return $where;
  }

  public static function get_interval_from_range( $range ) {
    $date_range = '';

    if( strcmp( 'this_week', $range ) === 0 ) { // this week
      $date_range = 'date > now() - INTERVAL 7 day';
    } else if( strcmp( 'last_week', $range ) === 0 ) { // last week
      $previous_week = strtotime("-1 week +1 day");

      // convert to datetime
      $previous_week = date('Y-m-d', $previous_week);

      // respect the start of week day by wordpress
      $start_end_week = get_weekstartend($previous_week);

      $start_week = date('Y-m-d', $start_end_week['start']);
      $end_week = date('Y-m-d', $start_end_week['end']);

      $date_range = "date BETWEEN '$start_week' AND '$end_week'";

    } else if( strcmp( 'this_month', $range ) === 0 ) { // this month
      $this_month_start = date('Y-m-01');
      $this_month_end = date('Y-m-t');

      $date_range = "date BETWEEN '$this_month_start' AND '$this_month_end'";

    } else if( strcmp( 'last_month', $range ) === 0 ) { // last month
      $first_day_last_month = strtotime('first day of last month');
      $last_day_last_month = strtotime('last day of last month');

      $last_month_start = date('Y-m-01', $first_day_last_month );
      $last_month_end = date('Y-m-t', $last_day_last_month );

      $date_range = "date BETWEEN '$last_month_start' AND '$last_month_end'";

    } else if( strcmp( 'this_year', $range ) === 0 ) { // this year
      $this_year_start = date('Y-01-01');
      $this_year_end = date('Y-12-31');

      $date_range = "date BETWEEN '$this_year_start' AND '$this_year_end'";

    } else if( strcmp( 'last_year', $range ) === 0 ) { // last year
      $last_year_start = date('Y-01-01', strtotime('-1 year'));
      $last_year_end = date('Y-12-31', strtotime('-1 year'));

      $date_range = "date BETWEEN '$last_year_start' AND '$last_year_end'";
    }

    return $date_range;
  }

  private function get_dates_in_range( $range ) {
    $dates = array();

    if( strcmp( 'this_week', $range ) === 0 ) {
      $end_day = date('Y-m-d', strtotime('today'));
      $start_day = date('Y-m-d', strtotime('today - 7 days'));
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'last_week', $range ) === 0 ) {
      $previous_week = strtotime("-1 week +1 day");

      // convert to datetime
      $previous_week = date('Y-m-d', $previous_week);

      // respect the start of week day by wordpress
      $start_end_week = get_weekstartend($previous_week);

      $start_week = date('Y-m-d', $start_end_week['start']);
      $end_week = date('Y-m-d', $start_end_week['end']);

      $dates = $this->get_days_between_dates( $start_week, $end_week );
    } else if( strcmp( 'this_month', $range ) === 0 ) {
      $start_day = date('Y-m-01');
      $end_day = date('Y-m-d');
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'last_month', $range ) === 0 ) {
      $first_day_last_month = strtotime('first day of last month');
      $last_day_last_month = strtotime('last day of last month');

      $start_day = date('Y-m-01', $first_day_last_month );
      $end_day = date('Y-m-t', $last_day_last_month );

      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'this_year', $range ) === 0 ) {
      $start_day = date('Y-01-01');
      $end_day = date('Y-m-d');
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    } else if( strcmp( 'last_year', $range ) === 0 ) {
      $start_day = date('Y-01-01', strtotime('-1 year'));
      $end_day = date('Y-12-31', strtotime('-1 year'));
      $dates = $this->get_days_between_dates( $start_day, $end_day );
    }

    return $dates;
  }

  private function get_days_between_dates( $start_day, $end_day ) {
    $dates = array();

    $current = strtotime($start_day);
    $end = strtotime($end_day);

    while( $current <= $end ) {
      $dates[] = date('Y-m-d', $current);
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

  private function process_graph_data( $results, $top_ids_arr, $range, $type, $metric = 'play' ) {
    $datasets = array();

    $date_labels = $this->get_dates_in_range( $range );

    // order data for graph,
    foreach( $top_ids_arr as $id ) {
      foreach( $date_labels as $date ) {
        foreach( $results as $row) {
          if( ( isset($row['id_video']) && $row['id_video'] == $id ) || ( isset($row['user_id']) && $row['user_id'] == $id ) ) {
            if( !isset($datasets[$id]) ) {
              $datasets[$id] = array();
            }

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
                  $datasets[$id][$date]['seconds'] = $row['seconds'];
                }
              }

            } else { // date row dont exists, add 0 plays/seconds - dont overwrite if value already set
              if( $metric === 'play' && !isset( $datasets[$id][$date]['play']) ) $datasets[$id][$date]['play'] = 0;
              if( $metric === 'seconds' && !isset( $datasets[$id][$date]['seconds']) ) $datasets[$id][$date]['seconds'] = 0;
            }

            if( !isset($datasets[$id]['name']) ) {
              if( $type == 'video' || $type == 'player' ) {
                if( !empty( $row['caption'] ) ) {
                  $datasets[$id]['name'] = $row['caption'];
                } else {

                  // Using code from FV_Player_Db_Video::getTitleFromSrc
                  $name = wp_parse_url( $row['src'], PHP_URL_PATH );
                  $arr = explode('/', $name);
                  $name = trim( end($arr) );

                  if( in_array( $name, array( 'index.m3u8', 'stream.m3u8' ) ) ) {
                    unset($arr[count($arr)-1]);
                    $name = end($arr);

                    // Add parent folder too if there's any
                    if( !empty( $arr ) && count( $arr ) > 2 ) {
                      unset($arr[count($arr)-1]);
                      $name = end($arr) . '/' . $name;
                    }
                  }
                  $datasets[$id]['name'] = $name;
                }

              } else if( $type == 'post' ) {
                $datasets[$id]['name'] = !empty($row['post_title'] ) ? $row['post_title'] : 'id_post_' . $row['id_post'] ;
              } else if( $type == 'user' ) {
                $user_data = get_userdata( intval($row['user_id']) );

                if( $user_data === false ) {
                  $datasets[$id]['name'] = 'guest';
                } else {
                  $datasets[$id]['name'] = $user_data->display_name;
                }

              }
            }
          }
        }
      }
    }

    $datasets['date-labels'] = $date_labels; // date will be used as X axis label

    return $datasets;
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
      $val = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT sum( " . $field . " ) FROM {$wpdb->prefix}fv_player_stats WHERE user_id = %d AND date = %s",
          $user_id,
          date_i18n( 'Y-m-d' )
        )
      );

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
      $userquery->query_where .= " AND stats.date = '" . date_i18n( 'Y-m-d' ) . "' ";
      $userquery->query_orderby = " GROUP BY wp_users.ID ORDER BY " . $field . " ".($userquery->query_vars["order"] == "ASC" ? "ASC " : "DESC ");
    }
  }

  function user_stats_search() {
    if( isset($_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'fv-player-stats-users-search' ) && isset($_GET['q']) && isset($_GET['date_range']) ) {
      $search = sanitize_text_field( $_GET['q'] );
      $date_range = sanitize_text_field( $_GET['date_range'] );

      // search for users by login, nicename or email
      $users = get_users( array(
        'search' => '*' . $search . '*',
        'search_columns' => array( 'user_login', 'user_nicename', 'user_email' ),
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

      echo json_encode( array( 'results' => $results ) );
    }

    die();
  }

}

global $FV_Player_Stats;
$FV_Player_Stats = new FV_Player_Stats();

function fv_player_stats_top( $args = array() ) {
  $args = wp_parse_args( $args, array(
    'post_type' => false,
    'taxonomy' => false,
    'term' => false ) );

  extract($args);

  $join = $where = "";
  if( $taxonomy && $term ) {
    $join = "
    INNER JOIN {$wpdb->prefix}term_relationships AS tr ON (pm.meta_value = tr.object_id)
    INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
    INNER JOIN {$wpdb->prefix}terms AS t ON (t.term_id = tt.term_id)";

    $where = "
    AND tt.taxonomy = '".esc_sql($taxonomy)."'
    AND t.name = '".esc_sql($term)."' ";
  }

  global $wpdb;
  $sql = "
SELECT p.id, vm.id_video, vm.meta_value AS stats_play, pm.meta_value AS post_id
FROM {$wpdb->prefix}fv_player_videometa AS vm
  JOIN {$wpdb->prefix}fv_player_players AS p ON FIND_IN_SET(vm.id_video, p.videos) > 0
  JOIN {$wpdb->prefix}fv_player_playermeta AS pm ON p.id = pm.id_player
  $join
WHERE vm.meta_key = 'stats_play'
  AND pm.meta_key = 'post_id'
  $where
  ORDER BY CAST(vm.meta_value AS unsigned) DESC;";

  $raw = $wpdb->get_results($sql);

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
