<?php
/* This file doesn't loading WordPress, it simple increment counters for posts in wp-content/cache/fv-tracker/{tag}-{site id}.data */

Class FvPlayerTrackerWorker {

  private $wp_content = false;
  private $cache_path = false;
  private $cache_filename = false;
  private $video_id = false;
  private $post_id = false;
  private $player_id = false;

  private $file = false;
  private $data = array();

  function __construct() {

    if( !isset( $_REQUEST['blog_id'] ) || !isset( $_REQUEST['tag'] ) || !isset( $_REQUEST['video_id'] ) ){
      die( "Error: missing arguments!" );
    }

    $blog_id = intval($_REQUEST['blog_id']);
    $tag = preg_replace( '~[^a-z]~', '', substr( $_REQUEST['tag'], 0, 16 ) );

    $this->wp_content = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
    $this->cache_path = $this->wp_content."/fv-player-tracking";
    $this->cache_filename = "{$tag}-{$blog_id}.data";
    $this->video_id = intval($_REQUEST['video_id']);
    $this->player_id = intval($_REQUEST['player_id']);
    $this->post_id = intval( $_REQUEST['post_id'] );

    $this->checkCacheFile();
  }

  /**
   * Check and initialize cache file
   * @return void
   */
  function checkCacheFile() {
    $full_path = $this->cache_path . "/" . $this->cache_filename;

    //cache file exists?
    if( file_exists( $full_path ) ) return;

    //cache directory exists
    if( !file_exists( $this->cache_path ) ){
      //create dir
      //todo: actually don't create it, if it doesn't exist it should mean the option is not enabled and this script shouldn't write anything!
      if( !mkdir( $this->cache_path, 0775, true ) ){
        die("Error: failed to create cache directory.");
      }
    }

    //init file
    touch( $full_path );
  }

  /**
   * Load cache file data, find specific video_id and increment coutner for it
   * @return boolean True when file lock was obtained, this doesn't ensure successful write. Otherwise false is returned
   */
  function incrementCacheCounter() {
    $max_attempts = 3;

    for( $i = 0; $i < $max_attempts; $i++ ){

      if( flock( $this->file, LOCK_EX | LOCK_NB ) ) {

        //increment counter
        $encoded_data = fgets( $this->file );
        $data = false;
        if( $encoded_data ) {
          $data = json_decode( $encoded_data, true );
  
          $json_error = json_last_error();
          if( $json_error !== JSON_ERROR_NONE ) {
            file_put_contents( $this->wp_content.'/fv-player-track-error.log', date('r')." JSON decode error:\n".var_export( array( 'err' => $json_error, 'data' => $encoded_data ), true )."\n", FILE_APPEND ); // todo: remove
            ftruncate( $this->file, 0 );
            return false;
          }
        }

        if( !$data ) { 
          $data = array();
        }

        $found = false;
        foreach( $data as $index => $item ) {
          if( $item['video_id'] == $this->video_id && $item['post_id'] == $this->post_id && $item['player_id'] == $this->player_id ) {
            $data[$index]['play'] += 1;
            $found = true;
            break;
          }
        }

        if( !$found ) {
          $data[] = array(
            'video_id' => $this->video_id,
            'post_id' => $this->post_id,
            'player_id' => $this->player_id,
            'play' => 1
          );
        }

        $encoded_data = json_encode($data);

        ftruncate( $this->file, 0 );
        rewind( $this->file );
        fputs( $this->file, $encoded_data );

        //UNLOCK
        flock( $this->file, LOCK_UN );
        return true;
      }
      else{
        //wait random interval from 50ms to 100ms
        usleep( rand(50,100) );
      }
    }

    return false;
  }

  /**
   * Main tracker functionality
   * @return void
   */
  function track() {
    $this->file = fopen( $this->cache_path."/".$this->cache_filename, 'r+');

    if( ! $this->incrementCacheCounter() ){
      file_put_contents( $this->wp_content.'/fv-player-track-error.log', date('r') . " flock or other error:\n".var_export($_REQUEST,true)."\n", FILE_APPEND ); // todo: remove
    }

    fclose( $this->file );
  }
}

$fv_player_tracker_worker = new FvPlayerTrackerWorker();
$fv_player_tracker_worker->track();

?>