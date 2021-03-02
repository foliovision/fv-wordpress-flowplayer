<?php
class FV_Player_Stats {
  
  var $used = false;

  public function __construct() {
    add_filter('fv_flowplayer_admin_default_options_after', array( $this, 'options_html' ) );
    add_filter('fv_flowplayer_conf', array( $this, 'option' ) );
    add_filter( 'fv_flowplayer_attributes', array( $this, 'shortcode' ), 10, 3 );
    
    if ( function_exists('wp_next_scheduled') && !wp_next_scheduled( 'fv_player_stats' ) ) {
      wp_schedule_event( time(), '5minutes', 'fv_player_stats' );
    }
    
    add_action('fv_player_stats', array($this,'parseCachedFiles'));
    
    // todo: create/delete the WP_CONTENT_DIR."/fv-player-tracking" based on the settin
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
  function processCachedData( &$fp, $tag ) {
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
            $plays = $plays + intval($video->getMetaValue('stats_'.$tag,true));
            if( $plays > 0 ) {
              $video->updateMetaValue( 'stats_'.$tag, $plays );
            }
          }
        }
      }
      
    }
    else{
      echo "Error: failed to obtain file lock.";
    }
  }

  /**
   * Loads directory with cache files, and process those, which belongs to current blog
   * @return void
   */
  function parseCachedFiles() {
    $cache_directory = WP_CONTENT_DIR."/fv-player-tracking";
    if( !file_exists( $cache_directory ) )
      return;

    $cache_files = scandir( $cache_directory );
    foreach( $cache_files as $filename ){
      if( preg_match( '/^([^-]+)-([^\.]+)\.data$/', $filename, $matches ) ) {
        $tag = $matches[1];
        if( !in_array($tag, array('play') ) ) continue;
        
        $blog_id = intval($matches[2]);

        if( get_current_blog_id() != $blog_id )
          continue;

        $fp = fopen( $cache_directory."/".$filename, 'r+');
        $this->processCachedData( $fp, $tag );
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
