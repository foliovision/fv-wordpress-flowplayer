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

  public function api_call( $endpoint, $args = array(), $method = 'GET' ) {
    if( $method == 'POST' ) {
      $response = wp_remote_post( $endpoint, array(
        'headers' => array(
          "Accept: application/json",
          'Content-Type' => 'application/json',
          'AccessKey' => $this->access_key
        ),
        'body' => wp_json_encode( $args ),
        'timeout' => 10
      ) );

    } else {
      $response = wp_remote_get( $endpoint, array(
        'headers' => array(
          "Accept: application/json",
          'Content-Type' => 'application/json',
          'AccessKey' => $this->access_key
        ),
        'timeout' => 10
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