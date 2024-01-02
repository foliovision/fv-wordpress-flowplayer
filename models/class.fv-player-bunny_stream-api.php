<?php

class FV_Player_Bunny_Stream_API {

  private $access_key;

  public function __construct( $access_key = null ) {
    global $fv_fp;

    if ( $access_key ) {
      $this->access_key = $access_key;
    } else {
      // if we don't have an access key, try to get it from Bunny Stream config
      $this->access_key = $fv_fp->_get_option( array('bunny_stream','api_key') );
      if ( !$this->access_key ) {
        throw new Exception('Bunny.net API class did not receive an API key and could not detect a stored one in the configuration.');
      }
    }
  }

  public function get_all_collections( $search = false ) {
    global $fv_fp;

    $query_string = array( 'itemsPerPage' => 50, 'orderBy' => 'date' );
    $query_string['page'] = ( !empty($_POST['page']) && is_numeric($_POST['page']) && (int) $_POST['page'] == $_POST['page'] ? $_POST['page'] : 1 );

    if( $search ) $query_string['search'] = $search;

    $endpoint = add_query_arg(
      $query_string,
      'http://video.bunnycdn.com/library/'. $fv_fp->_get_option( array('bunny_stream','lib_id') ) .'/collections'
    );

    $result_collection = $this->api_call( $endpoint );

    return $result_collection;
  }

  function get_collection_guid_by_name($name) {
    $result_collection = $this->get_all_collections();

    if( !is_wp_error( $result_collection ) ) {
      foreach ( $result_collection->items as $collection ) {
        if( strcmp($name, $collection->name) === 0 ) {
          return $collection->guid;
        }
      }
    }

    return false;
  }

  public function api_call( $endpoint, $args = array(), $method = 'GET' ) {
    if( $method == 'POST' ) {
      $response = wp_remote_post( $endpoint, array(
        'headers' => array(
          "Accept: application/json",
          'Content-Type' => 'application/json',
          'AccessKey' => $this->access_key
        ),
        'body' => wp_json_encode( $args ),
        'timeout' => 25
      ) );

    } else {
      $response = wp_remote_get( $endpoint, array(
        'headers' => array(
          "Accept: application/json",
          'Content-Type' => 'application/json',
          'AccessKey' => $this->access_key
        ),
        'timeout' => 25
      ) );

    }

    // on error, return it directly
    if ( is_wp_error( $response ) ) {
      return $response;
    }

    if ( wp_remote_retrieve_body($response) ) {
      $body = wp_remote_retrieve_body($response);
      $obj = json_decode( $body );
      if ( $obj === null && json_last_error() !== JSON_ERROR_NONE ) {
        return new WP_Error( 1, 'Response from the API is not a valid JSON.', $body );
      }

      // non-200 codes are errors, too
      if ( substr( $response['response']['code'], 0, 2) != '20' ) {
        return new WP_Error( 2, $obj->Message );
      }

    } else {
      return new WP_Error( 3, 'Unable to retrieve response body from the API.' );
    }

    return $obj;
  }

}