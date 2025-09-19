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

  function validate_file_upload() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_flowplayer_validate_file' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    // Check if file chunk was uploaded
    if (! isset( $_FILES['file_chunk']) || $_FILES['file_chunk']['error'] !== UPLOAD_ERR_OK ) {
      $error_msg = 'File upload failed or no file received.';
      if ( isset( $_FILES['file_chunk'] ) ) {
        $error_msg .= ' Upload error code: ' . $_FILES['file_chunk']['error'];
      }
      wp_send_json(array('error' => $error_msg));
    }

    // Get file info
    $file_info = json_decode( stripslashes( $_POST['file_info'] ), true );
    if ( ! $file_info ) {
      wp_send_json(array('error' => 'Invalid file information.'));
    }

    // Basic file validation
    $uploaded_file = $_FILES['file_chunk'];
    $file_size = $uploaded_file['size'];
    $file_name = $uploaded_file['name'];

    // Check file size (5MB chunk should be reasonable)
    if ( $file_size > 5 * 1024 * 1024 ) {
      wp_send_json(array('error' => 'File chunk too large: ' . $file_size . ' bytes (max: ' . ( 5 * 1024 * 1024 ) . ' bytes)'));
    }

    // Check if file is empty
    if ( $file_size === 0 ) {
      wp_send_json( array( 'error' => 'File appears to be empty.' ) );
    }

    // Check for malicious file extensions
    $dangerous_extensions = array('php', 'php3', 'php4', 'php5', 'phtml', 'pl', 'py', 'cgi', 'asp', 'aspx', 'jsp', 'so', 'dll', 'exe', 'bat', 'cmd', 'sh', 'com');
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if ( in_array( $file_extension, $dangerous_extensions ) ) {
      wp_send_json( array( 'error' => 'File type not allowed for security reasons: ' . $file_extension ) );
    }

    // Check for ELF headers (Linux executables)
    if ( substr( $file_content, 0, 4 ) === "\x7fELF" ) {
      wp_send_json(array('error' => 'File appears to be a Linux executable and is not allowed.'));
    }

    // Check for PE headers (Windows executables)
    if ( substr( $file_content, 0, 2 ) === "MZ" ) {
      wp_send_json(array('error' => 'File appears to be a Windows executable and is not allowed.'));
    }

    // Use getID3 to analyze the actual file content
    if ( ! class_exists( 'getID3' ) ) {
      require( ABSPATH . WPINC . '/ID3/getid3.php' );
    }
    $getID3 = new getID3;
    
    // Analyze the uploaded file
    $ThisFileInfo = $getID3->analyze($uploaded_file['tmp_name']);
    
    error_log('validate_file_upload: getID3 analysis: ' . print_r($ThisFileInfo, true));
    
    // Check if getID3 detected a valid file type
    $detected_mime_type = '';
    if ( isset( $ThisFileInfo['mime_type'] ) ) {
      $detected_mime_type = $ThisFileInfo['mime_type'];

    } elseif ( isset( $ThisFileInfo['fileformat'] ) ) {
      // Map file formats to MIME types
      $format_mime_map = array(
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'avi' => 'video/avi',
        'mov' => 'video/mov',
        'wmv' => 'video/wmv',
        'flv' => 'video/flv',
        'mkv' => 'video/mkv',
        'mp3' => 'audio/mp3',
        'wav' => 'audio/wav',
        'm4a' => 'audio/m4a',
      );

      $detected_mime_type = false;
      if ( isset( $format_mime_map[ $ThisFileInfo['fileformat'] ] ) ) {
        $detected_mime_type = $format_mime_map[ $ThisFileInfo['fileformat'] ];
      }
    }

    // If getID3 couldn't detect the type, fall back to browser MIME type
    if ( empty( $detected_mime_type ) ) {
      wp_send_json( array( 'error' => 'File type not supported.' ) );
      exit;
    }

    /**
     * Ensure video resolution is at least the minimal resolution
     */
    $video_width = 0;
    $video_height = 0;

    global $fv_fp;
    $minimal_video_resolution = $fv_fp->_get_option( array( 'coconut', 'minimal_source_video_resolution' ) );

    if ( $minimal_video_resolution && ! empty( $ThisFileInfo['video']['resolution_x'] ) && ! empty( $ThisFileInfo['video']['resolution_y'] ) ) {

      // Convert resolution names like 720p to actual dimensions
      $resolution_map = array(
        '480p'  => array( 720,  480  ),
        '720p'  => array( 1280, 720  ),
        '1080p' => array( 1920, 1080 ),
        '1440p' => array( 2560, 1440 ),
        '4K'    => array( 3840, 2160 )
      );

      if ( array_key_exists( $minimal_video_resolution, $resolution_map ) ) {
        $minimal_width  = $resolution_map[ $minimal_video_resolution ][0];
        $minimal_height = $resolution_map[ $minimal_video_resolution ][1];
      } else {
        $minimal_width  = 0;
        $minimal_height = 0;
      }

      if ( $minimal_width && $minimal_height ) {

        // For 4:3 aspect ratio
        $minimal_width_4_3 = $minimal_height * 4 / 3;
        $minimal_height_4_3 = $minimal_height;

        // For 21:9 aspect ratio
        $minimal_width_21_9 = $minimal_width;
        $minimal_height_21_9 = $minimal_width * 9 / 21;

        $video_width    = absint( $ThisFileInfo['video']['resolution_x'] );
        $video_height   = absint( $ThisFileInfo['video']['resolution_y'] );

        // Check for vertical video
        // Alternatively we could parse degrees from $ThisFileInfo['video']['rotate'], but is that commonly used for vertical videos?
        if ( $video_width < $video_height ) {
          $video_width  = absint( $ThisFileInfo['video']['resolution_y'] );
          $video_height = absint( $ThisFileInfo['video']['resolution_x'] );
        }

        if (
          $video_width >= $minimal_width && $video_height >= $minimal_height ||
          $video_width >= $minimal_width_4_3 && $video_height >= $minimal_height_4_3 ||
          $video_width >= $minimal_width_21_9 && $video_height >= $minimal_height_21_9
        ) {
          // Video dimensions are good for one of the aspect ratios

        } else {
          wp_send_json( array( 'error' => 'The video resolution is too low. Please create a ' . $minimal_video_resolution . ' video.' ) );
          exit;
        }

      }
    }

    // Define allowed MIME types
    $allowed_types = array(
      'video/mp4',
      'video/webm',
      'video/ogg',
      'video/avi',
      'video/mov',
      'video/wmv',
      'video/flv',
      'video/mkv',
      'video/quicktime',
      'video/x-matroska',
      'audio/mp3',
      'audio/wav',
      'audio/ogg',
      'audio/m4a',
    );

    if ( ! in_array( $detected_mime_type, $allowed_types ) ) {
      wp_send_json( array( 'error' => 'File type not allowed: ' . $detected_mime_type ) );
    }

    // Clean up the uploaded file
    unlink( $uploaded_file['tmp_name'] );

    // Generate a new nonce for the create_multiupload action
    $create_multiupload_nonce = wp_create_nonce( 'fv_flowplayer_create_multiupload' );

    wp_send_json(array(
      'success' => true,
      'message' => 'File validation passed.',
      'create_multiupload_nonce'    => $create_multiupload_nonce,
      'multiupload_send_part_nonce' => wp_create_nonce( 'fv_flowplayer_multiupload_send_part' ),
      'multiupload_abort_nonce'     => wp_create_nonce( 'fv_flowplayer_multiupload_abort' ),
      'multiupload_complete_nonce'  => wp_create_nonce( 'fv_flowplayer_multiupload_complete' ),
      'validated_file_info'         => $file_info,
      'detected_mime_type'          => $detected_mime_type,
      'file_analysis'               => array(
        'fileformat'  => isset( $ThisFileInfo['fileformat'] ) ? $ThisFileInfo['fileformat'] : 'unknown',
        'mime_type'   => $detected_mime_type,
        'filesize'    => $file_size,
        'resolution'  => $video_width . 'x' . $video_height,
      )
    ));
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
