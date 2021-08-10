<?php
class FV_Player_Stats {

  var $used = false;
  var $cache_directory = false;

  public function __construct() {
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
  
  }

  function stats_link() {
    add_submenu_page( 'fv_player', 'FV Player Stats', 'Stats', 'manage_options', 'fv_player_stats', 'fv_player_stats_page' );
  }

  function get_stat_columns() {
    return array( 'play' );
  }

  function get_table_name() {
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
      `date` DATE NULL DEFAULT NULL,\n";

    foreach( $this->get_stat_columns() AS $column ) {
      $sql .= "`".$column."` INT(11) NOT NULL,\n";
    }
      
    $sql .= "PRIMARY KEY (`id`),
      INDEX `date` (`date`),
      INDEX `id_video` (`id_video`),
      INDEX `id_player` (`id_player`),
      INDEX `id_post` (`id_post`)
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
                                'blog_id' => $blog_id
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
        $attributes['data-fv_stats_data'] = json_encode( array('player_id' => $player_id, 'post_id' => $post->ID) );
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
          $value = intval($item[$type]);

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

          $table_name = $this->get_table_name();

          $existing =  $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE date = %s AND id_video = %d AND id_post = %d AND id_player = %d", date_i18n( 'Y-m-d' ), $video_id, $post_id, $player_id ) );

          if( $existing ) {
            $wpdb->update(
              $table_name,
              array(
                $type => $value + $existing->{$type}, // update plays in db
              ),
              array( 'id_video' => $video_id , 'date' => date_i18n( 'Y-m-d' ), 'id_player' => $player_id, 'id_post' => $post_id ), // update by video id, date, player id and post id
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
                'id_video' => $video_id,
                'id_player' => $player_id,
                'id_post' => $post_id,
                'date' => date_i18n( 'Y-m-d' ),
                $type => $value
              ),
              array(
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

  public function top_ten_videos() {
    global $wpdb;

    $results = $wpdb->get_col( "SELECT id_video FROM `{$wpdb->prefix}fv_player_stats` WHERE date > now() - INTERVAL 7 day GROUP BY id_video ORDER BY sum(play) DESC LIMIT 10");

    return $results;
  }

  public function get_top_video_post_stats( $type ) {
    global $wpdb;

    $datasets = false;
    $top_ids = array();
    $top_ids_arr = array();
    $top_ids_results = $this->top_ten_videos(); // get top video ids
    
    if( !empty($top_ids_results) ) {
      $top_ids_arr = array_values( $top_ids_results );
      $top_ids = implode( ',', array_values( $top_ids_arr ) );
    } else {
      return false;
    }

    if( $type == 'video' ) { // video stats
      $results = $wpdb->get_results( "SELECT date, id_player, id_video, caption, src, SUM(play) AS play  FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date > now() - INTERVAL 7 day AND id_video IN( $top_ids ) GROUP BY id_video, date", ARRAY_A );
    } else if( $type == 'post' ) { // post stats
      $results = $wpdb->get_results( "SELECT date, id_post, id_video, post_title, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}posts` AS p ON s.id_post = p.ID WHERE date > now() - INTERVAL 7 day AND id_video IN( $top_ids ) GROUP BY id_post, date;
      ", ARRAY_A );
    }

    if( !empty($results) ) {
      $datasets = $this->process_graph_data( $results, $top_ids_arr, $type );
    }

    return $datasets;
  }

  public function get_player_stats( $player_id ) {
    global $wpdb;

    $datasets = false;

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT date, id_video, src, caption, player_name, SUM(play) AS play FROM `{$wpdb->prefix}fv_player_stats` AS s JOIN `{$wpdb->prefix}fv_player_players` AS p ON s.id_player = p.id JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id WHERE date > now() - INTERVAL 7 day AND s.id_player IN( '%d' ) GROUP BY date, id_video", $player_id ), ARRAY_A );

    if( !empty($results) ) {
      $ids_arr = array();
      foreach( $results as $row ) {
        $ids_arr[] = $row['id_video'];
      }

      $datasets = $this->process_graph_data( $results, $ids_arr, 'player' );
    }

    return $datasets;
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

  private function process_graph_data( $results, $top_ids_arr, $type ) {
    $datasets = array();
    $date_labels = $this->get_date_labels( $results );

    if( $type == 'video' ) {
      $id_item = 'id_player';
    } else if( $type == 'player' ) {
      $id_item = 'player_name';
    } else if( $type == 'post' ) {
      $id_item = 'id_post';
    }

    // order data for graph,
    foreach( $top_ids_arr as $id_video ) {
      foreach( $date_labels as $date ) {
        foreach( $results as $row) {
          if( $row['id_video'] == $id_video ) {
            if( !isset($datasets[$id_video]) ) {
              $datasets[$id_video] = array();
            }

            if( !isset($datasets[$id_video][$date]) ) {
              $datasets[$id_video][$date] = array(
                'id_player_post'=> $row[$id_item]
              );
            }
            
            if( strcmp( $date, $row['date'] ) == 0 ) { // date row exists
              $datasets[$id_video][$date]['play'] = $row['play'];
            } else if( !isset( $datasets[$id_video][$date]['play']) ) { // date row dont exists, add 0 plays - dont overwrite if value already set
              $datasets[$id_video][$date]['play'] = 0;
            }

            if( !isset($datasets[$id_video]['name']) ) {
              if( $type == 'video' || $type == 'player' ) {
                $datasets[$id_video]['name'] = !empty( $row['caption'] ) ? $row['caption'] : $row['src']; // if no caption then use src
              } else if( $type == 'post' ) {
                $datasets[$id_video]['name'] = !empty($row['post_title'] ) ? $row['post_title'] : 'id_post_' . $row['id_post'] ;
              }
            }
          }
        }
      }
    }

    $datasets['date-labels'] = $date_labels; // date will be used as X axis label

    return $datasets;
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
