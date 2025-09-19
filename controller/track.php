<?php
/* This file doesn't load WordPress, it simple increment counters for posts in wp-content/cache/fv-tracker/{tag}-{site id}.data */

if( !defined('SHORTINIT') ) {
  define('SHORTINIT',true);
}

// include wp-load.php
if( file_exists('../../../../wp-load.php') ) {
  require('../../../../wp-load.php');
}

//require_once( ABSPATH . WPINC . '/pluggable.php' );

/**
 * Including what's necessary for nonce verification
 */
require_once( ABSPATH . WPINC . '/capabilities.php' );
require_once( ABSPATH . WPINC . '/class-wp-roles.php' );
require_once( ABSPATH . WPINC . '/class-wp-role.php' );
require_once( ABSPATH . WPINC . '/class-wp-user.php' );
require_once( ABSPATH . WPINC . '/user.php' );

// Translation and localization.
require_once( ABSPATH . WPINC . '/pomo/mo.php' );
require_once( ABSPATH . WPINC . '/l10n.php' );

if ( file_exists( ABSPATH . WPINC . '/class-wp-textdomain-registry.php' ) ) {
  require_once( ABSPATH . WPINC . '/class-wp-textdomain-registry.php' );
}

require_once( ABSPATH . WPINC . '/class-wp-locale.php' );
require_once( ABSPATH . WPINC . '/class-wp-locale-switcher.php' );

if ( class_exists( 'WP_Textdomain_Registry' ) ) {
  global $wp_textdomain_registry;
  if ( ! $wp_textdomain_registry instanceof WP_Textdomain_Registry ) {
    $wp_textdomain_registry = new WP_Textdomain_Registry();
  }
}

require_once( ABSPATH . WPINC . '/pluggable.php' );
require_once( ABSPATH . WPINC . '/functions.php' );
require_once( ABSPATH . WPINC . '/formatting.php' );
require_once( ABSPATH . WPINC . '/link-template.php' );
require_once( ABSPATH . WPINC . '/shortcodes.php' );
require_once( ABSPATH . WPINC . '/general-template.php' );
require_once( ABSPATH . WPINC . '/class-wp-session-tokens.php' );
require_once( ABSPATH . WPINC . '/class-wp-user-meta-session-tokens.php' );
require_once( ABSPATH . WPINC . '/meta.php' );
require_once( ABSPATH . WPINC . '/kses.php' );
require_once( ABSPATH . WPINC . '/rest-api.php' );
require_once( ABSPATH . WPINC . '/blocks.php' );

// Without this plugins_url() won't work
wp_plugin_directory_constants();

// Without this the user login status won't work
wp_cookie_constants();

Class FvPlayerTrackerWorker {

  private $wp_content = false;
  private $cache_path = false;
  private $cache_filename = false;
  private $video_id = false;
  private $post_id = false;
  private $player_id = false;
  private $user_id = 0;
  private $guest_user_id = 0;
  private $watched = false;
  private $tag = false;

  private $file = false;

  function __construct() {

    if(
      !isset( $_REQUEST['blog_id'] ) ||
      !isset( $_REQUEST['tag'] ) ||
      !isset( $_REQUEST['video_id'] ) && !isset( $_REQUEST['watched'] )
    ){
      die( "Error: missing arguments!" );
    }

    // $action has one been added in WordPress 6.1 unfortunately
    add_filter(
      'nonce_life',
      function( $seconds, $action = false ) {
        if ( 'fv_player_track' === $action ) {
          $seconds = 7 * DAY_IN_SECONDS;
        }
        return $seconds;
      },
      PHP_INT_MAX,
      2
    );

    // Do not check HTTP auth as we did not load WP_Application_Passwords class
    remove_filter( 'determine_current_user', 'wp_validate_application_password', 20 );

    if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'fv_player_track' ) ) {
      die( "Error: invalid nonce!" );
    }

    if( sanitize_key( $_REQUEST['tag'] ) == 'click' ) {
      $a = 1;
    }

    $blog_id = intval($_REQUEST['blog_id']);
    $tag = preg_replace( '~[^a-z]~', '', substr( sanitize_key( $_REQUEST['tag'] ), 0, 16 ) );
    $this->tag = $tag;

    $this->wp_content = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
    $this->cache_path = $this->wp_content."/fv-player-tracking";
    $this->cache_filename = "{$tag}-{$blog_id}.data";

    $this->video_id = !empty($_REQUEST['video_id']) ? intval($_REQUEST['video_id']) : false;
    $this->player_id = !empty($_REQUEST['player_id']) ? intval($_REQUEST['player_id']) : false;
    $this->post_id = !empty($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : false;
    $this->user_id = intval($_REQUEST['user_id']);
    $this->watched = !empty($_REQUEST['watched']) ? sanitize_text_field( urldecode( $_REQUEST['watched'] ) ) : false;

    // TODO: Verify some kind of signature here

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
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
            file_put_contents( $this->wp_content.'/fv-player-track-error.log', gmdate('r')." JSON decode error:\n".var_export( array( 'err' => $json_error, 'data' => $encoded_data ), true )."\n", FILE_APPEND ); // todo: remove
            ftruncate( $this->file, 0 );
            return false;
          }
        }

        if( !$data ) {
          $data = array();
        }

        if ( 'seconds' === $this->tag ) {
          $this->watched = json_decode( $this->watched, true );

          $json_error = json_last_error();
          if( $json_error !== JSON_ERROR_NONE ) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
            file_put_contents( $this->wp_content.'/fv-player-track-error.log', gmdate('r')." JSON decode error for watched:\n".var_export( array( 'err' => $json_error, 'data' => $this->watched ), true )."\n", FILE_APPEND ); // todo: remove
            return false;
          }

          foreach ( $this->watched as $player_id => $players ) {

            foreach( $players as $post_id => $videos ) {

              foreach( $videos as $video_id => $seconds ) {

                // Add to the existing JSON data
                $found = false;
                foreach( $data as $index => $item ) {
                  if( $item['video_id'] == $video_id && $item['post_id'] == $post_id && $item['player_id'] == $player_id && $item['user_id'] == $this->user_id  && $item['guest_user_id'] == $this->guest_user_id ) {
                    $data[$index]['seconds'] = round( $data[$index]['seconds'] + $seconds );
                    $found = true;
                  }
                }

                // New JSON data
                if ( ! $found ) {
                  $data[] = array(
                    'video_id'  => $video_id,
                    'post_id'   => $post_id,
                    'player_id' => $player_id,
                    'user_id'   => $this->user_id,
                    'guest_user_id' => $this->guest_user_id,
                    'seconds'   => round($seconds)
                  );
                }
              }
            }
          }

        } else if ( 'play' === $this->tag ) {
          $found = false;
          foreach( $data as $index => $item ) {
            if( $item['video_id'] == $this->video_id && $item['post_id'] == $this->post_id && $item['player_id'] == $this->player_id && $item['user_id'] == $this->user_id && $item['guest_user_id'] == $this->guest_user_id ) {
              $data[$index]['play'] += 1;
              $found = true;
              break;
            }
          }

          if( !$found ) {
            $data[] = array(
              'video_id'  => $this->video_id,
              'post_id'   => $this->post_id,
              'player_id' => $this->player_id,
              'user_id'   => $this->user_id,
              'guest_user_id' => $this->guest_user_id,
              'play'      => 1
            );
          }
        } else if ( 'click' === $this->tag ) {
          $found = false;
          foreach( $data as $index => $item ) {
            if( $item['video_id'] == $this->video_id && $item['post_id'] == $this->post_id && $item['player_id'] == $this->player_id && $item['user_id'] == $this->user_id && $item['guest_user_id'] == $this->guest_user_id ) {
              $data[$index]['click'] += 1;
              $found = true;
              break;
            }
          }

          if( !$found ) {
            $data[] = array(
              'video_id'  => $this->video_id,
              'post_id'   => $this->post_id,
              'player_id' => $this->player_id,
              'user_id'   => $this->user_id,
              'guest_user_id' => $this->guest_user_id,
              'click'      => 1
            );
          }
        }

        $encoded_data = wp_json_encode($data);

        ftruncate( $this->file, 0 );
        rewind( $this->file );
        fputs( $this->file, $encoded_data );

        //UNLOCK
        flock( $this->file, LOCK_UN );
        return true;
      }
      else{
        //wait random interval from 50ms to 100ms
        usleep( wp_rand(50,100) );
      }
    }

    return false;
  }

  /**
   * Main tracker functionality
   * @return void
   */
  function track() {

    if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'fv_player_track' ) ) {
      die( "Error: invalid nonce!" );
    }

    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
    $this->file = fopen( $this->cache_path."/".$this->cache_filename, 'r+');

    $options = get_option('fvwpflowplayer');
    $guest_user_id = 0;

    if( absint( $_REQUEST['user_id'] ) == 0 && ! empty( $options['video_stats_enable_guest'] ) && 'true' === $options['video_stats_enable_guest']) { // guest user

      if( isset( $_COOKIE['fv_player_stats_guest_user_id'] ) ) { // check if cookie is set
        $guest_user_id = intval( $_COOKIE['fv_player_stats_guest_user_id'] );
      } else { // create new guest user id
        $last_guest_id = get_option( 'fv_player_stats_last_guest_user_id', 0 );
        $last_guest_id = $last_guest_id + 1;

        $guest_user_id = $last_guest_id;

        update_option( 'fv_player_stats_last_guest_user_id', $last_guest_id );

        // save cookie fo 1 year
        setcookie( 'fv_player_stats_guest_user_id', $last_guest_id, time() + 60 * 60 * 24 * 365, '/' );
      }
    }

    $this->guest_user_id = $guest_user_id;

    if( ! $this->incrementCacheCounter() ) {
      // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
      file_put_contents( $this->wp_content.'/fv-player-track-error.log', gmdate('r') . " flock or other error:\n".var_export( $this,true )."\n", FILE_APPEND ); // todo: remove
    }

    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
    fclose( $this->file );

    // Add .htaccess to deny all direct access
    $htaccess_path = $this->cache_path . '/.htaccess';
    if ( ! file_exists( $htaccess_path ) ) {
      file_put_contents( $htaccess_path, "# Deny access to tracking files\nOrder allow,deny\nDeny from all\n" );
    }

  }
}

$fv_player_tracker_worker = new FvPlayerTrackerWorker();
$fv_player_tracker_worker->track();
