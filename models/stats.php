<?php
class FV_Player_Stats {
  
  var $used = false;
  var $cache_directory = WP_CONTENT_DIR."/fv-player-tracking";

  public function __construct() {
    add_filter( 'fv_flowplayer_admin_default_options_after', array( $this, 'options_html' ) );
    add_filter( 'fv_flowplayer_conf', array( $this, 'option' ) );
    add_filter( 'fv_flowplayer_attributes', array( $this, 'shortcode' ), 10, 3 );

    if ( function_exists('wp_next_scheduled') && !wp_next_scheduled( 'fv_player_stats' ) ) {
      wp_schedule_event( time(), '5minutes', 'fv_player_stats' );
    }

    add_action( 'fv_player_stats', array ( $this, 'parse_cached_files' ) );

    add_action( 'fv_player_update', array( $this, 'db_init' ) );

    add_action( 'admin_init', array( $this, 'folder_init' ) );
  }

  function get_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'fv_player_stats';
  }

  function db_init( $force ) {
    global $fv_fp;

    if( !$force && !$fv_fp->_get_option('video_stats_enable') ) {
      return;
    }

    global $wpdb;
    $table_name = $this->get_table_name();

    $sql = "CREATE TABLE `$table_name` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `id_video` INT(11) NOT NULL,
      `date` DATE NULL DEFAULT NULL,
      `play` INT(11) NOT NULL,
      PRIMARY KEY (`id`),
      INDEX `date` (`date`),
      INDEX `id_video` (`id_video`)
    )" . $wpdb->get_charset_collate() . ";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    dbDelta($sql);
  }

  function folder_init( $force = false ) {
    global $fv_fp;

    if( !$force && !$fv_fp->_get_option('video_stats_enable') ) {
      if( file_exists( $this->cache_directory ) ) rmdir( $this->cache_directory );
      return;
    }

    if( !file_exists( $this->cache_directory ) ) mkdir( $this->cache_directory );
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
    $fv_fp->_get_checkbox(__('Enable Video Stats', 'fv-wordpress-flowplayer'), 'video_stats_enable', __('Gives you a simple count of video playbacks.'), __('Uses a simple PHP script with a cron job to make sure these stats don\'t slow down your server too much.'));
  }

  function shortcode( $attributes, $media, $fv_fp ) {
    if( !empty($fv_fp->aCurArgs['stats']) ) {
      if( $fv_fp->aCurArgs['stats'] != 'no' ) {
        $this->used = true;
      }
      $attributes['data-fv_stats'] = $fv_fp->aCurArgs['stats'];
    }
    return $attributes;
  }
  

  /**
   * Process post counters from cache file and update post meta
   * @param  resource &$fp file handler
   * @param  string   $tag post_meta name
   * @return void
   */
  function process_cached_data( &$fp, $tag ) {
    global $wpdb;

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
        foreach( $data  AS $video_id => $plays ) {
          if( !is_int($plays) || intval($plays) < 1 ) {
            continue;
          }

          global $FV_Player_Db;
          $video = new FV_Player_Db_Video( $video_id, array(), $FV_Player_Db );
          if( $video ) {
            $plays_meta = $plays + intval($video->getMetaValue('stats_'.$tag,true));
            if( $plays_meta > 0 ) {
              $video->updateMetaValue( 'stats_'.$tag, $plays_meta );
            }

            $table_name = $this->get_table_name();

            $plays_db =  $wpdb->get_var( $wpdb->prepare("SELECT `plays` FROM  $table_name WHERE date = %s AND id_video = %d ", date('Y-m-d'), $video_id ) );

            if( $tag != "play" ) {
              continue;
            }

            if( $plays_db ) {
              $wpdb->update(
                $table_name,
                array(
                  'play' => $plays + $plays_db, // update plays in db
                ),
                array( 'id_video' => $video_id , 'date' => date('Y-m-d') ), // update by video id and date
                array(
                  '%d'
                ),
                array(
                  '%d',
                  '%s'
                )
              );
            } else { // insert new row
              $wpdb->insert(
                $table_name,
                array(
                  'id_video' => $video_id,
                  'date' => date('Y-m-d'),
                  'play' => $plays
                ),
                array(
                  '%d',
                  '%s',
                  '%d'
                )
              );
            }
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
    foreach( $cache_files as $filename ){
      if( preg_match( '/^([^-]+)-([^\.]+)\.data$/', $filename, $matches ) ) {
        $tag = $matches[1];
        if( !in_array($tag, array('play') ) ) continue;
        
        $blog_id = intval($matches[2]);

        if( get_current_blog_id() != $blog_id ) continue;

        $fp = fopen( $this->cache_directory."/".$filename, 'r+');
        $this->process_cached_data( $fp, $tag );
        fclose( $fp );
      }
    }
  }  

}
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
    INNER JOIN wp_term_relationships AS tr ON (pm.meta_value = tr.object_id)
    INNER JOIN wp_term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
    INNER JOIN wp_terms AS t ON (t.term_id = tt.term_id)";
    
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
