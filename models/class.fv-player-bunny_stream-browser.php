<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( !class_exists('FV_Player_Bunny_Stream_Browser') && class_exists('FV_Player_Media_Browser') ) :

require_once( dirname(__FILE__).'/class.fv-player-bunny_stream-api.php' );

class FV_Player_Bunny_Stream_Browser extends FV_Player_Media_Browser {

  function init() {
    global $fv_fp;

    if ( $fv_fp->_get_option( array('bunny_stream','api_key') ) && ( is_admin() || get_current_screen()->base == 'fv_player_bunny_stream' ) ) {
      wp_enqueue_script( 'fv-player-bunny_stream-browser', plugins_url( 'js/bunny_stream-browser.js', dirname(__FILE__) ), array( 'flowplayer-browser-base' ), filemtime( dirname(__FILE__).'/../js/bunny_stream-browser.js' ), true );
      wp_localize_script( 'fv-player-bunny_stream-browser', 'fv_player_bunny_stream_settings', array(
        'nonce' => wp_create_nonce( $this->ajax_action_name )
      ));

      do_action( 'fv_player_media_browser_enqueue_base_uploader_css' );

      wp_enqueue_script( 'fv-player-bunny_stream-upload', plugins_url( 'js/bunny_stream-upload.js', dirname(__FILE__) ), array( 'flowplayer-browser-base' ), filemtime( dirname(__FILE__).'/../js/bunny_stream-upload.js' ), true );
      wp_localize_script( 'fv-player-bunny_stream-upload', 'fv_player_bunny_stream_upload_settings', array(
        'upload_button_text' => __('Upload to Bunny Stream', 'fv-player-bunny_stream'),
        'lib_id' => $fv_fp->_get_option( array('bunny_stream','lib_id') ),
        'api_key' => $fv_fp->_get_option( array('bunny_stream','api_key') ),
        'job_submit_nonce' => wp_create_nonce('fv_player_bunny_stream'),
        'nonce_add_new_folder' => wp_create_nonce('fv_player_bunny_stream_add_new_folder')
      ));
    }
  }

  function register() {
    add_action( $this->ajax_action_name, array($this, 'load_assets') );
    add_action( $this->ajax_action_name_add_new_folder, array($this, 'add_new_folder_ajax' ) );
  }

  // Legacy
  function init_for_gutenberg() {}

  function add_new_folder_ajax() {
    if( defined('DOING_AJAX') && ( !isset( $_POST['nonce_add_new_folder'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce_add_new_folder'] ) ), 'fv_player_bunny_stream_add_new_folder' ) ) ) {
      wp_send_json( array('error' => 'Bad nonce') );
    }

    $output = array();

    $name = wp_strip_all_tags( sanitize_text_field( $_POST['folder_name'] ) ); // new collection to create
    $name = stripslashes($name);

    $api = new FV_Player_Bunny_Stream_API();

    $guid = $api->get_collection_guid_by_name($name); // check if collection already exists

    if( empty($guid) ) {
      global $fv_fp;

      $endpoint = 'http://video.bunnycdn.com/library/'. $fv_fp->_get_option( array('bunny_stream','lib_id') ) .'/collections';

      $response = $api->api_call( $endpoint, array('name' => $name), 'POST' );

      if( is_wp_error($response) ) {
        $output['error'] = $response->get_error_message();
      } else {
        $output['guid'] = $response->guid;
      }

    } else {
      $output['error'] = 'Error - collection ' . $name . ' does already exists';
    }

    wp_send_json( $output );
  }

  function get_formatted_assets_data() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->ajax_action_name ) ) {
      return array(
        'items' => array(),
        'name' => '/',
        'path' => '/',
        'type' => 'folder',
        'err' => 'Invalid nonce'
      );
    }

    global $fv_fp, $wpdb;

    $local_jobs = $wpdb->get_results( "SELECT id, job_id FROM `{$wpdb->prefix}fv_player_encoding_jobs`" );
    $local_jobs = wp_list_pluck( $local_jobs, 'id', 'job_id');

    $query_string = array( 'itemsPerPage' => 1000, 'orderBy' => 'date' );
    $query_string['page'] = ( !empty($_POST['page']) && is_numeric($_POST['page']) && intval( $_POST['page'] ) == absint( $_POST['page'] ) ? absint( $_POST['page'] ) : 1 );
    if( !empty($_POST['search']) ) {
      $query_string['search'] = sanitize_text_field( $_POST['search'] );
    }

    // prepare base folder
    $body = array();
    $body['name'] = 'Home';
    $body['path'] = 'Home/';
    $body['type'] = 'folder';
    $body['items'] = array();

    if( isset($_POST['path']) ) {
      $_POST['path'] = wp_strip_all_tags( sanitize_text_field( $_POST['path'] ) );
      $path = str_replace('Home/', '', sanitize_text_field( $_POST['path'] ) ); // remove Home/
      $path = rtrim($path, '/'); // remove ending /
    } else {
      $path = false;
    }

    $api = new FV_Player_Bunny_Stream_API();

    // query default videos or concrete collection library
    if( $path ) {
      $query_string['collection'] = $api->get_collection_guid_by_name($path);
      $body['path'] = sanitize_text_field( $_POST['path'] );
    } else { // no colledction_id load collections
      $result_collection = $api->get_all_collections( $query_string['search'] ? $query_string['search'] : false );

      if( !is_wp_error( $result_collection ) ) {
        foreach( $result_collection->items as $collection ) { // add collections as folders
          $body['items'][] = array(
            'name' => $collection->name,
            'path' => 'Home/' . $collection->name,
            'type' => 'folder'
          );
        }
      }
    }

    $endpoint = add_query_arg(
      $query_string,
      'https://video.bunnycdn.com/library/'.$fv_fp->_get_option( array('bunny_stream','lib_id') ).'/videos'
    );

    $result = $api->api_call( $endpoint );

    if ( is_wp_error( $result ) ) {
      $result = array( 'error' => $result->get_error_message() );
    } else if ( !is_object( $result ) ) {
      $result = array( 'error' => $result );
    }

    $video_data_more_pages_exist = ( $result->totalItems > ( $result->currentPage * $result->itemsPerPage ) );

    // prepare result for browser
    // ... $result will be a return-value array instead of an object if there was an error
    if ( !is_array( $result ) ) {
      $result->time = time();

      $date_format = get_option( 'date_format' );
      $cdn_hostname = 'https://' . $fv_fp->_get_option( array('bunny_stream','cdn_hostname') ) . '/';

      foreach ($result->items as $video) {
        if( !$path && !empty($video->collectionId) ) continue; // do not list videos with collection when no collection selected

        $item = array(
          'link' => $cdn_hostname . $video->guid . '/playlist.m3u8',
          'name' => $video->title,
          'size' => $video->storageSize,
          'type' => 'file',
          'path' => 'Home/' . $video->title,
          'duration' => $video->length,
          'modified' => gmdate( $date_format, strtotime( $video->dateUploaded ) ),
          'width' => $video->width,
          'height' => $video->height,
          'extra' => array(),
        );

        if( !empty($local_jobs[$video->guid]) ) {
          $item['extra']['encoding_job_id'] = $local_jobs[$video->guid];
        }

        // job in processing
        if ( $video->status < 4 ) {
          $item['extra']['encoding_job_status'] = 'processing';
          $item['extra']['displayData'] = 'This file is currently being processed by the Bunny Stream service.';
          $item['extra']['percentage'] = $video->encodeProgress . '%';
          // don't allow selecting this file until it's at least playable
          if ( !$video->availableResolutions ) {
            $item['extra']['disabled'] = 1;
          } else {
            // video is playable but still encoding, mark it as such
            $item['extra']['encoding_job_status'] = 'playable';

            // These properties are already there
            $item['splash'] = $cdn_hostname . $video->guid . '/' . $video->thumbnailFileName;
            $item['extra']['title'] = $video->title;
          }
        } else if ( $video->status > 4 ) {
          // job errored out
          $item['extra']['disabled'] = 1;
          $item['extra']['encoding_job_status'] = 'error';
          if ( $video->status == 5 ) {
            $item['extra']['displayData'] = 'Processing error on the Bunny Stream side.';
          } else {
            $item['extra']['displayData'] = 'Upload error or file upload cancelled.';
          }
        } else {
          // job complete
          $item['splash'] = $cdn_hostname . $video->guid . '/' . $video->thumbnailFileName;
          $item['extra']['title'] = $video->title;
        }

        if( !empty($item['splash']) ) {
          $item['splash'] = apply_filters('fv_flowplayer_splash', $item['splash'] );
        }

        $body['items'][] = $item;
      }
    }

    $json_final = array(
      'items' => $body,
      'is_last_page' => !$video_data_more_pages_exist,
    );

    // ... $result will be a return-value array instead of an object if there was an error
    if ( is_array($result) ) {
      $json_final['err'] = $result['error'];
    }

    return $json_final;
  }

  function load_assets() {
    $json_final = $this->get_formatted_assets_data();

    wp_send_json( $json_final );
    wp_die();
  }

}

new FV_Player_Bunny_Stream_Browser( array( 'ajax_action_name' => 'wp_ajax_load_bunny_stream_jobs', 'ajax_action_name_add_new_folder' => 'wp_ajax_add_bunny_stream_new_folder') );

endif;