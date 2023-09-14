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
      echo $e->getMessage(), PHP_EOL;
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
    if( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'fv_flowplayer_create_multiupload' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    global $FV_Player_DigitalOcean_Spaces;

    $filename = $this->sanitize_path($_POST['fileInfo']['name']);
    $filename = ($this->remove_special_chars($filename));

    $target = dirname($filename);

    if( $target === '.' ) {
      $target = '';
    } else {
      $target = trailingslashit($target);
    }

    $filename_parts = explode('.', $filename);

    try {
      global $fv_fp;

      $s3Client = $this->s3();

      $bucket = $fv_fp->_get_option(array('digitalocean_spaces','space'));

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

      $_POST['fileInfo']['name'] = $filename_final;

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
        'Key' => $filename_final,
        'ContentType' => $_REQUEST['fileInfo']['type'],
        'Metadata' => $_REQUEST['fileInfo']
      ));

      wp_send_json( array(
        'uploadId' => $res->get('UploadId'),
        'key' => $res->get('Key'),
      ));
    } catch( Aws\S3\Exception\S3Exception $e ) {
      wp_send_json( array( 'error' => 'Access denied, please check your DigitalOcean Spaces keys in FV Player -> Coconut -> Settings.' ) );
    }

    wp_die();
  }

  function multiupload_send_part() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'fv_flowplayer_multiupload_send_part' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    global $FV_Player_DigitalOcean_Spaces;

    $command = $this->s3( "getCommand", "UploadPart", array(
      'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
      'Key' => $_REQUEST['sendBackData']['key'],
      'UploadId' => $_REQUEST['sendBackData']['uploadId'],
      'PartNumber' => $_REQUEST['partNumber'],
      'ContentLength' => $_REQUEST['contentLength']
    ));

    // Give it at least 24 hours for large uploads
    $request = $this->s3( "createPresignedRequest" , $command, "+48 hours" );

    wp_send_json( array(
      'url' => (string) $request->getUri(),
    ));
    wp_die();
  }

  function multiupload_complete() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'fv_flowplayer_multiupload_complete' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    global $FV_Player_DigitalOcean_Spaces;

    try {
      $partsModel = $this->s3("listParts",[
        'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
        'Key' => $_REQUEST['sendBackData']['key'],
        'UploadId' => $_REQUEST['sendBackData']['uploadId'],
      ]);

    } catch ( Exception $e ) {
      wp_send_json( array(
        'error'   => true,
        'message' => $e->getMessage(),
      ) );
    }

    try {
      $ret = $this->s3( "completeMultipartUpload" , array(
        'Bucket' => $FV_Player_DigitalOcean_Spaces->get_space(),
        'Key' => $_REQUEST['sendBackData']['key'],
        'UploadId' => $_REQUEST['sendBackData']['uploadId'],
        'MultipartUpload' => array(
          "Parts" => $partsModel["Parts"],
        ),
        ))->toArray();

    } catch ( Exception $e ) {
      wp_send_json( array(
        'error'   => true,
        'message' => $e->getMessage(),
      ) );
    }

    wp_send_json( array(
      'success' => true,
      'url' => $ret['ObjectURL'],
      'key' => $ret['Key'],
      'nonce' => wp_create_nonce( 'fv_player_coconut' ),
    ));
    wp_die();
  }

  function multiupload_abort() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'fv_flowplayer_multiupload_abort' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

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

}

global $FV_Player_S3_Upload;
$FV_Player_S3_Upload = new FV_Player_S3_Upload();
