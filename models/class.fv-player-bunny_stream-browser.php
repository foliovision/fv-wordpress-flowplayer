<?php

if( !class_exists('FV_Player_Bunny_Stream_Browser') && class_exists('FV_Player_Media_Browser') ) :

require_once( dirname(__FILE__).'/class.fv-player-bunny_stream-api.php' );

class FV_Player_Bunny_Stream_Browser extends FV_Player_Media_Browser {

  function init() {
    global $fv_fp;

    if ( $fv_fp->_get_option( array('bunny_stream','api_key') ) && ( is_admin() || get_current_screen()->base == 'fv_player_bunny_stream' ) ) {
      wp_enqueue_script( 'fv-player-bunny_stream-browser', plugins_url( 'js/bunny_stream-browser.js', dirname(__FILE__) ), array( 'flowplayer-browser-base' ), FV_Player_Bunny_Stream()->get_version(), true );
      do_action( 'fv_player_media_browser_enqueue_base_uploader_css' );
      wp_enqueue_script( 'fv-player-bunny_stream-upload', plugins_url( 'js/bunny_stream-upload.js', dirname(__FILE__) ), array( 'flowplayer-browser-base' ), filemtime( dirname(__FILE__).'/js/bunny_stream-upload.js' ), true );
      wp_localize_script( 'fv-player-bunny_stream-upload', 'fv_player_bunny_stream_upload_settings', array(
        'upload_button_text' => __('Upload to Bunny.net Stream', 'fv-player-bunny_stream'),
        'lib_id' => $fv_fp->_get_option( array('bunny_stream','lib_id') ),
        'api_key' => $fv_fp->_get_option( array('bunny_stream','api_key') ),
        'job_submit_nonce' => wp_create_nonce('fv_player_bunny_stream'),
      ));
    }
  }

  function register() {
    add_action( $this->ajax_action_name, array($this, 'load_assets') );

    // register extra AJAX functions for file uploads to DOS
    add_action( 'wp_ajax_create_upload', array( $this, 'create_upload' ) );
    add_action( 'wp_ajax_upload_complete', array( $this, 'upload_complete' ) );
    add_action( 'wp_ajax_upload_abort', array( $this, 'upload_abort' ) );
  }

  // Legacy
  function init_for_gutenberg() {}

  function create_upload() {
    global $FV_Player_DigitalOcean_Spaces;

    // make sure we have correct CORS on the DOS bucket
    $this->s3("putBucketCors",
      array(
        "Bucket" => $FV_Player_DigitalOcean_Spaces->get_space(),
        "CORSConfiguration" => array(
          "CORSRules" => array(
            array(
              'AllowedHeaders' => array(
                'Access-Control-Allow-Methods',
                'Access-Control-Allow-Origin',
                'Origin',
                'Range',
              ),
              'AllowedMethods'=> array('GET','HEAD','PUT'),
              "AllowedOrigins"=> array("*"),
            ),
          ),
        ),
      )
    );

    $res = $this->s3( "createMultipartUpload", array(
      'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
      'Key' => $_POST['fileInfo']['name'],
      'ContentType' => $_REQUEST['fileInfo']['type'],
      'Metadata' => $_REQUEST['fileInfo']
    ));

    wp_send_json( array(
      'uploadId' => $res->get('UploadId'),
      'key' => $res->get('Key'),
    ));
    wp_die();
  }

  function upload_complete() {
    global $FV_Player_DigitalOcean_Spaces;

    $partsModel = $this->s3("listParts",[
      'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
      'Key' => $_REQUEST['sendBackData']['key'],
      'UploadId' => $_REQUEST['sendBackData']['uploadId'],
    ]);

    $ret = $this->s3( "completeMultipartUpload" , array(
      'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
      'Key' => $_REQUEST['sendBackData']['key'],
      'UploadId' => $_REQUEST['sendBackData']['uploadId'],
      'MultipartUpload' => array(
        "Parts" => $partsModel["Parts"],
      ),
      ))->toArray();

    wp_send_json( array(
      'success' => true,
      'url' => $ret['ObjectURL'],
      'key' => $ret['Key'],
      'nonce' => wp_create_nonce( 'fv_player_coconut' ),
    ));
    wp_die();
  }

  function upload_abort() {
    global $FV_Player_DigitalOcean_Spaces;

    // if initial pre-upload request fails, we'll have no sendBackData to abort
    if ( !empty( $_REQUEST['sendBackData'] ) ) {
      $this->s3("abortMultipartUpload",[
        'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
        'Key' => $_REQUEST['sendBackData']['key'],
        'UploadId' => $_REQUEST['sendBackData']['uploadId']
      ]);
    }

    wp_send_json( array(
      'success' => true
    ));
    wp_die();
  }

  function get_formatted_assets_data() {
    global $fv_fp, $wpdb;
    
    $local_jobs = $wpdb->get_results( "SELECT id, job_id FROM " . FV_Player_Bunny_Stream()->get_table_name() );
    $local_jobs = wp_list_pluck( $local_jobs, 'id', 'job_id');

    // load videos based from the library
    $api = new FV_Player_Bunny_Stream_API();

    $query_string = array( 'itemsPerPage' => 50, 'orderBy' => 'date' );
    $query_string['page'] = ( !empty($_POST['page']) && is_numeric($_POST['page']) && (int) $_POST['page'] == $_POST['page'] ? $_POST['page'] : 1 );
    if( !empty($_POST['search']) ) {
      $query_string['search'] = $_POST['search'];
    }

    $endpoint = add_query_arg(
      $query_string,
      'https://video.bunnycdn.com/library/'.$fv_fp->_get_option( array('bunny_stream','lib_id') ).'/videos'
    );

    $result = $api->api_call(  $endpoint );

    if ( is_wp_error( $result ) ) {
      $result = array( 'error' => $result->get_error_message() );
    } else if ( !is_object( $result ) ) {
      $result = array( 'error' => $result );
    }

    $result->time = time();
    $video_data_more_pages_exist = ( $result->totalItems > ( $result->currentPage * $result->itemsPerPage ) );

    // prepare base folder
    $body = array();
    $body['name'] = 'Home';
    $body['path'] = 'Home/';
    $body['type'] = 'folder';
    $body['items'] = array();

    // prepare result for browser
    // ... $result will be a return-value array instead of an object if there was an error
    if ( !is_array( $result ) ) {
      $date_format = get_option( 'date_format' );
      $cdn_hostname = 'https://' . $fv_fp->_get_option( array('bunny_stream','cdn_hostname') ) . '/';

      foreach ($result->items as $video) {
        $item = array(
          'link' => $cdn_hostname . $video->guid . '/playlist.m3u8',
          'name' => $video->title,
          'size' => $video->storageSize,
          'type' => 'file',
          'path' => 'Home/' . $video->title,
          'duration' => $video->length,
          'modified' => date( $date_format, strtotime( $video->dateUploaded ) ),
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
          $item['extra']['displayData'] = 'This file is currently being processed by the Bunny.net Stream service.';
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
            $item['extra']['displayData'] = 'Processing error on the Bunny.net Stream side.';
          } else {
            $item['extra']['displayData'] = 'Upload error or file upload cancelled.';
          }
        } else {
          // job complete
          $item['splash'] = $cdn_hostname . $video->guid . '/' . $video->thumbnailFileName;
          $item['extra']['title'] = $video->title;
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

new FV_Player_Bunny_Stream_Browser( 'wp_ajax_load_bunny_stream_jobs' );

endif;