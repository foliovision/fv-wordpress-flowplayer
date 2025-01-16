<?php

class FV_Player_S3_Upload {

  function sanitize_path($path) {
    $path = str_replace( 'Home/', '', stripslashes($path) );
    $path = preg_replace( '~/$~', '', $path ); // We need to remove trailing slash to keep the breadcrumbs working

    return $path;
  }

  function remove_special_chars($string) {
    // coconut doesnt like this characters, we need to remove them
    $string = str_replace( ' ', '-', $string );
    $string = str_replace( ',', '-', $string );
    $string = str_replace( '?', '', $string );
    $string = str_replace( '&', '', $string );
    $string = str_replace( '#', '', $string );
    $string = str_replace( '%', '', $string );
    $string = str_replace( '^', '', $string );
    $string = str_replace( '$', '', $string );
    $string = str_replace( '\'', '', $string );
    $string = str_replace( '"', '', $string );

    return $string;
  }

  /**
   * Easy wrapper around S3 API
   * @param  mixed $command the function to call
   * @param  mixed $args    variable args to pass
   * @return mixed
   */
  function s3( $command = null, $args = null) {
    global $FV_Player_DigitalOcean_Spaces_Browser;

    static $s3 = null;
    if ( $s3 === null ) {
      $FV_Player_DigitalOcean_Spaces_Browser->include_aws_sdk();
      $s3 = $FV_Player_DigitalOcean_Spaces_Browser->get_s3_client();
    }

    if ( $command === null ) return $s3;

    $args=func_get_args();
    array_shift($args);
    try {
      return call_user_func_array( [$s3, $command ], $args );
    } catch (AwsException $e) {
      echo esc_html( $e->getMessage() ), PHP_EOL;
    }

    return null;
  }

  function file_exists($contents, $filename) {
    if ( is_array( $contents ) ) {
      foreach( $contents as $object ) {
        if( isset($object['Key']) && $object['Key'] == $filename ) {
          return true;
        }
      }
    }

    return false;
  }

  function create_multiupload() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_flowplayer_create_multiupload' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    global $FV_Player_DigitalOcean_Spaces;

    $filename = $this->sanitize_path($_POST['fileInfo']['name']);
    $filename = $this->remove_special_chars($filename);

    $filename = remove_accents( $filename );
    $filename = str_replace('Ę', 'E', $filename);

    $target = dirname($filename);

    if( $target === '.' ) {
      $target = '';
    } else {
      $target = trailingslashit($target);
    }

    $filename_parts = explode('.', $filename);

    try {
      $s3Client = $this->s3();

      if ( ! $s3Client ) {
        $message = "AWS S3 SDK Failed to load.";

        if ( version_compare(phpversion(),'7.4') == -1 ) {
          $message .= " You need to use PHP version 7.4 or above.";
        }

        if ( function_exists( 'FV_Player_Coconut' ) ) {
          FV_Player_Coconut()->plugin_api->log( "create_multiupload: " . $message );
        }

        wp_send_json( array( 'error' => $message ) );
      }

      $bucket = $FV_Player_DigitalOcean_Spaces->get_space();

      // get objects from source space
      $objects = $s3Client->listObjects(array(
        'Bucket' => $bucket,
        'Prefix' => $target,
        'ResponseCacheControl'       => 'No-cache',
        'ResponseExpires'            => gmdate(DATE_RFC2822, time() + 3600),
      ));

      $contents = $objects->get('Contents');

      $rename_suffix_counter = 2;

      $filename_final = $filename;

      // verify if file already exists and append -{number} to prevent overwriting in source space
      while( $this->file_exists($contents, $filename_final) ) {
        $filename_parts = explode('.', $filename);
        $filename_parts[count($filename_parts) -2] .= '-' . $rename_suffix_counter; // add suffix to second last part of filename before extension
        $filename_final = implode('.', $filename_parts);
        $rename_suffix_counter++;
      }

      // TODO: Is this needed anywhere? If so do it properly!
      $_POST['fileInfo']['name'] = $filename_final;

    } catch( Aws\S3\Exception\S3Exception $e ) {
      $message = "Error checking files, please check your DigitalOcean Spaces keys in FV Player -> Coconut -> Settings.";

      if ( function_exists( 'FV_Player_Coconut' ) ) {
        FV_Player_Coconut()->plugin_api->log( "create_multiupload: " . $message . " Details: " . $e->getMessage() );
      }

      wp_send_json( array( 'error' => $message ) );
    }

    /**
     * Make sure we have correct CORS on the DOS bucket.
     * But if this fails then just go on as we want to allow key without full privileges
     * to succeed at the upload.
     */
    try {
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
    } catch( Aws\S3\Exception\S3Exception $e ) {
      if ( function_exists( 'FV_Player_Coconut' ) ) {
        FV_Player_Coconut()->plugin_api->log( "create_multiupload: Error setting CORS: " . $e->getMessage() );
      }
    }

    try {
      $res = $this->s3( "createMultipartUpload", array(
        'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
        'Key' => $filename_final,
        'ContentType' => sanitize_text_field( $_REQUEST['fileInfo']['type'] ),
        'Metadata' => array(
          'name' => sanitize_text_field( $_REQUEST['fileInfo']['name'] ),
          'type' => sanitize_text_field( $_REQUEST['fileInfo']['type'] ),
          'size' => intval( $_REQUEST['fileInfo']['size'] ),
        )
      ));

      if ( function_exists( 'FV_Player_Coconut' ) ) {
        FV_Player_Coconut()->plugin_api->log( "create_multiupload: uploadId: " . $res->get('UploadId') . " for key: " . $res->get('Key') );
      }

      wp_send_json( array(
        'uploadId' => $res->get('UploadId'),
        'key' => $res->get('Key'),
      ));
    } catch( Aws\S3\Exception\S3Exception $e ) {
      $message = "Error creating upload, please check your DigitalOcean Spaces keys in FV Player -> Coconut -> Settings.";

      if ( function_exists( 'FV_Player_Coconut' ) ) {
        FV_Player_Coconut()->plugin_api->log( "create_multiupload: " . $message . " Details: " . $e->getMessage() );
      }

      wp_send_json( array( 'error' => $message ) );
    }

    wp_die();
  }

  function multiupload_send_part() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_flowplayer_multiupload_send_part' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    global $FV_Player_DigitalOcean_Spaces;

    $args = array(
      'Bucket'        => $FV_Player_DigitalOcean_Spaces->get_space(),
      'Key'           => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
      'UploadId'      => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] ),
      'PartNumber'    => intval( $_REQUEST['partNumber'] ),
      'ContentLength' => intval( $_REQUEST['contentLength'] )
    );

    if ( function_exists( 'FV_Player_Coconut' ) ) {
      FV_Player_Coconut()->plugin_api->log( "multiupload_send_part: S3 UploadPart: " . print_r( $args, true ) );
    }

    $command = $this->s3( "getCommand", "UploadPart", $args );

    // Give it at least 24 hours for large uploads
    $request = $this->s3( "createPresignedRequest" , $command, "+48 hours" );

    wp_send_json( array(
      'url' => (string) $request->getUri(),
    ));
    wp_die();
  }

  function multiupload_complete() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_flowplayer_multiupload_complete' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    global $FV_Player_DigitalOcean_Spaces;

    // Try to complete the upload 4 times
    $attempt = 1;

    while( 1 ) {

      // Initial wait as these file parts may take a bit of time to really appear
      sleep(5);

      try {
        $args = array(
          'Bucket'   => $FV_Player_DigitalOcean_Spaces->get_space(),
          'Key'      => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
          'UploadId' => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] ),
        );

        if ( function_exists( 'FV_Player_Coconut' ) ) {
          FV_Player_Coconut()->plugin_api->log( "multiupload_complete: S3 listParts: " . print_r( $args, true ) );
        }

        $partsModel = $this->s3("listParts", $args);

      } catch ( Exception $e ) {
        if ( function_exists( 'FV_Player_Coconut' ) ) {
          FV_Player_Coconut()->plugin_api->log( "multiupload_complete: S3 listParts exception: " . $e->getMessage() );
        }

        wp_send_json( array(
          'error'   => true,
          'message' => $e->getMessage(),
        ) );
      }

      $parts = array();

      if (isset($partsModel["Parts"]) ) {
        $parts = $partsModel["Parts"];
      } else if (isset($partsModel["data"]["Parts"]) ) {
        $parts = $partsModel["data"]["Parts"];
      }

      try {
        $args = array(
          'Bucket'   => $FV_Player_DigitalOcean_Spaces->get_space(),
          'Key'      => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
          'UploadId' => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] ),
          'MultipartUpload' => array(
            "Parts" => $parts,
          )
        );

        if ( function_exists( 'FV_Player_Coconut' ) ) {
          FV_Player_Coconut()->plugin_api->log( "multiupload_complete: S3 completeMultipartUpload: " . print_r( $args, true ) );
        }

        $ret = $this->s3( "completeMultipartUpload", $args )->toArray();

        // Do not try again if it succeeded!
        break;

      } catch ( Exception $e ) {
        $attempt++;

        if ( function_exists( 'FV_Player_Coconut' ) ) {
          FV_Player_Coconut()->plugin_api->log( "multiupload_complete: S3 completeMultipartUpload exception: " . $e->getMessage() );
        }

        if ( $attempt > 4 ) {
          wp_send_json( array(
            'error'   => true,
            'message' => $e->getMessage(),
          ) );
        }

        sleep(5);
      }
    }

    wp_send_json( array(
      'success' => true,
      'url' => $ret['ObjectURL'],
      'key' => $ret['Key'],
      'nonce' => wp_create_nonce( 'fv_player_coconut' ),
      'attempt' => $attempt
    ));
    wp_die();
  }

  function multiupload_abort() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_flowplayer_multiupload_abort' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    global $FV_Player_DigitalOcean_Spaces;

    // if initial pre-upload request fails, we'll have no sendBackData to abort
    if ( !empty( $_REQUEST['sendBackData'] ) ) {

      $args = array(
        'Bucket'   => $FV_Player_DigitalOcean_Spaces->get_space(),
        'Key'      => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
        'UploadId' => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] )
      );

      if ( function_exists( 'FV_Player_Coconut' ) ) {
        FV_Player_Coconut()->plugin_api->log( "multiupload_abort: S3 abortMultipartUpload: " . print_r( $args, true ) );
      }

      $this->s3("abortMultipartUpload", $args );
    }

    wp_send_json( array(
      'success' => true
    ));
    wp_die();
  }

}

global $FV_Player_S3_Upload;
$FV_Player_S3_Upload = new FV_Player_S3_Upload();
