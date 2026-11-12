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

      // Allows extensions to override the S3 client
      $s3 = apply_filters( 'fv_player_s3_upload_client', null );

      if ( ! $s3 ) {
        $s3 = $FV_Player_DigitalOcean_Spaces_Browser->get_s3_client();
      }
    }

    if ( $command === null ) return $s3;

    $args = func_get_args();
    array_shift( $args );
    try {
      return call_user_func_array( array( $s3, $command ), $args );
    } catch ( AwsException $e ) {
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

        if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
          FV_Player_Coconut()->plugin_api->log( "create_multiupload: " . $message );
        }

        wp_send_json( array( 'error' => $message ) );
      }

      // get objects from source space
      $objects = $s3Client->listObjects(array(
        'Bucket'               => $this->get_bucket(),
        'Prefix'               => $target,
        'ResponseCacheControl' => 'No-cache',
        'ResponseExpires'      => gmdate(DATE_RFC2822, time() + 3600),
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
      $message = "Error checking files, please check your " . $this->get_selected_storage_label() . " keys in FV Player -> Coconut -> Settings.";

      if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
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
          "Bucket" => $this->get_bucket(),
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
      if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
        FV_Player_Coconut()->plugin_api->log( "create_multiupload: Error setting CORS: " . $e->getMessage() );
      }
    }

    try {
      $res = $this->s3( "createMultipartUpload", array(
        'Bucket'      => $this->get_bucket(),
        'Key'         => $filename_final,
        'ContentType' => sanitize_text_field( $_REQUEST['fileInfo']['type'] ),
        'Metadata' => array(
          'name' => sanitize_text_field( $_REQUEST['fileInfo']['name'] ),
          'type' => sanitize_text_field( $_REQUEST['fileInfo']['type'] ),
          'size' => intval( $_REQUEST['fileInfo']['size'] ),
        )
      ));

      if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
        FV_Player_Coconut()->plugin_api->log( "create_multiupload: uploadId: " . $res->get('UploadId') . " for key: " . $res->get('Key') );
      }

      wp_send_json( array(
        'uploadId' => $res->get('UploadId'),
        'key' => $res->get('Key'),
      ));
    } catch( Aws\S3\Exception\S3Exception $e ) {
      $message = "Error creating upload, please check your " . $this->get_selected_storage_label() . " keys in FV Player -> Coconut -> Settings.";

      if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
        FV_Player_Coconut()->plugin_api->log( "create_multiupload: " . $message . " Details: " . $e->getMessage() );
      }

      wp_send_json( array( 'error' => $message ) );
    }

    wp_die();
  }

  /**
   * Get the bucket name to upload to.
   *
   * The filter fv_player_s3_upload_bucket can be used to override the bucket name.
   */
  function get_bucket() {
    global $FV_Player_DigitalOcean_Spaces;
    $bucket = $FV_Player_DigitalOcean_Spaces->get_space();

    return apply_filters( 'fv_player_s3_upload_bucket', $bucket );
  }

  /**
   * Get the label for the selected storage for error messages.
   *
   * The filter fv_player_s3_upload_storage_label can be used to override the label.
   */
  function get_selected_storage_label() {
    $label = 'DigitalOcean Spaces';

    return apply_filters( 'fv_player_s3_upload_storage_label', $label );
  }

  /**
   * Is FV Player -> Settings -> Debug Log (debug_file) enabled?
   */
  function is_debug_log_enabled() {
    global $fv_fp;
    return ! empty( $fv_fp ) && method_exists( $fv_fp, 'log' ) && $fv_fp->_get_option( 'debug_file' );
  }

  /**
   * Write into the FV Player Debug Log, if enabled.
   *
   * controller/s3-ajax.php loads WordPress with SHORTINIT, so the init action
   * which normally sets up the log file never runs. We set it up here.
   *
   * @param string $message
   */
  function debug_log( $message ) {
    if ( ! $this->is_debug_log_enabled() ) {
      return;
    }

    global $fv_fp;
    if ( empty( $fv_fp->log_file ) ) {
      $fv_fp->maybe_log_file_setup();
    }

    $fv_fp->log( $message );
  }

  /**
   * Log the validation error and send it to the browser.
   *
   * @param string $message Error message for the user.
   * @param mixed  $details Extra information only for the log.
   */
  private function validate_file_upload_error( $message, $details = false ) {
    $log = 'validate_file_upload: FAILED: ' . $message;
    if ( $details ) {
      $log .= ' Details: ' . ( is_string( $details ) ? $details : wp_json_encode( $details ) );
    }
    $this->debug_log( $log );

    wp_send_json( array( 'error' => $message ) );
    exit;
  }

  /**
   * Human readable meaning of the PHP file upload error codes.
   *
   * @param int $code
   */
  private function get_upload_error_message( $code ) {
    $messages = array(
      UPLOAD_ERR_OK         => 'No error',
      UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize in php.ini',
      UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE of the form',
      UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
      UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
      UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
      UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
      UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload',
    );

    return isset( $messages[ $code ] ) ? $messages[ $code ] : 'Unknown upload error';
  }

  function validate_file_upload() {

    if ( $this->is_debug_log_enabled() ) {
      $files_summary = array();
      if ( ! empty( $_FILES ) ) {
        foreach ( $_FILES as $key => $file ) {
          $files_summary[ $key ] = array(
            'name'         => isset( $file['name'] ) ? $file['name'] : null,
            'type'         => isset( $file['type'] ) ? $file['type'] : null,
            'size'         => isset( $file['size'] ) ? $file['size'] : null,
            'error'        => isset( $file['error'] ) ? $file['error'] . ' (' . $this->get_upload_error_message( $file['error'] ) . ')' : null,
            'tmp_exists'   => ! empty( $file['tmp_name'] ) && file_exists( $file['tmp_name'] ),
          );
        }
      }

      $this->debug_log( 'validate_file_upload: Request received: ' . wp_json_encode( array(
        'user_id'             => get_current_user_id(),
        'request_method'      => isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : null,
        'content_length'      => isset( $_SERVER['CONTENT_LENGTH'] ) ? $_SERVER['CONTENT_LENGTH'] : null,
        'content_type'        => isset( $_SERVER['CONTENT_TYPE'] ) ? $_SERVER['CONTENT_TYPE'] : null,
        'https'               => is_ssl(),
        'http_host'           => isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : null,
        'origin'              => isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : null,
        'referer'             => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null,
        'user_agent'          => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null,
        'php_version'         => PHP_VERSION,
        'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
        'post_max_size'       => ini_get( 'post_max_size' ),
        'memory_limit'        => ini_get( 'memory_limit' ),
        'max_execution_time'  => ini_get( 'max_execution_time' ),
        'upload_tmp_dir'      => ini_get( 'upload_tmp_dir' ) ? ini_get( 'upload_tmp_dir' ) : sys_get_temp_dir(),
        'post_keys'           => array_keys( $_POST ),
        'files'               => $files_summary,
        'file_info'           => isset( $_POST['file_info'] ) ? json_decode( stripslashes( $_POST['file_info'] ), true ) : null,
      ) ) );

      // Catch fatal errors, like getID3 running out of memory
      register_shutdown_function( function() {
        $error = error_get_last();
        if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ), true ) ) {
          $this->debug_log( 'validate_file_upload: PHP fatal error: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] );
        }
      } );
    }

    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_flowplayer_validate_file' ) ) {
      $this->validate_file_upload_error(
        'Access denied, please reload the page and try again.',
        isset( $_POST['nonce'] ) ? 'Nonce verification failed for user ID ' . get_current_user_id() : 'Nonce missing'
      );
    }

    // Check if file chunk was uploaded
    if (! isset( $_FILES['file_chunk']) || $_FILES['file_chunk']['error'] !== UPLOAD_ERR_OK ) {
      $error_msg = 'File upload failed or no file received.';
      $details   = 'No file_chunk in $_FILES';
      if ( isset( $_FILES['file_chunk'] ) ) {
        $error_msg .= ' Upload error code: ' . $_FILES['file_chunk']['error'];
        $details    = $this->get_upload_error_message( $_FILES['file_chunk']['error'] );
      }
      $this->validate_file_upload_error( $error_msg, $details );
    }

    // Get file info
    $file_info = json_decode( stripslashes( $_POST['file_info'] ), true );
    if ( ! $file_info ) {
      $this->validate_file_upload_error(
        'Invalid file information.',
        'JSON error: ' . json_last_error_msg() . ', raw: ' . ( isset( $_POST['file_info'] ) ? substr( $_POST['file_info'], 0, 500 ) : 'missing' )
      );
      exit;
    }

    // Basic file validation
    $uploaded_file = $_FILES['file_chunk'];
    $file_size = $uploaded_file['size'];
    $file_name = $uploaded_file['name'];

    // Check file size (5MB chunk should be reasonable)
    if ( $file_size > 5 * 1024 * 1024 ) {
      $this->validate_file_upload_error( 'File chunk too large: ' . $file_size . ' bytes (max: ' . ( 5 * 1024 * 1024 ) . ' bytes)' );
      exit;
    }

    // Check if file is empty
    if ( $file_size === 0 ) {
      $this->validate_file_upload_error( 'File appears to be empty.' );
      exit;
    }

    // Check for malicious file extensions
    $dangerous_extensions = array('php', 'php3', 'php4', 'php5', 'phtml', 'pl', 'py', 'cgi', 'asp', 'aspx', 'jsp', 'so', 'dll', 'exe', 'bat', 'cmd', 'sh', 'com');
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if ( in_array( $file_extension, $dangerous_extensions ) ) {
      $this->validate_file_upload_error( 'File type not allowed for security reasons: ' . $file_extension );
      exit;
    }

    // Read the start of the file for the executable checks below
    $file_content = '';
    $handle = @fopen( $uploaded_file['tmp_name'], 'rb' );
    if ( $handle ) {
      $file_content = fread( $handle, 16 );
      fclose( $handle );
    } else {
      $this->debug_log( 'validate_file_upload: Unable to open the uploaded file for reading: ' . $uploaded_file['tmp_name'] );
    }

    $this->debug_log( 'validate_file_upload: File "' . $file_name . '", chunk size ' . $file_size . ' bytes, first bytes (hex): ' . bin2hex( $file_content ) );

    // Check for ELF headers (Linux executables)
    if ( substr( $file_content, 0, 4 ) === "\x7fELF" ) {
      $this->validate_file_upload_error( 'File appears to be a Linux executable and is not allowed.' );
      exit;
    }

    // Check for PE headers (Windows executables)
    if ( substr( $file_content, 0, 2 ) === "MZ" ) {
      $this->validate_file_upload_error( 'File appears to be a Windows executable and is not allowed.' );
      exit;
    }

    // Use getID3 to analyze the actual file content
    if ( ! class_exists( 'getID3' ) ) {
      require( ABSPATH . WPINC . '/ID3/getid3.php' );
    }
    $getID3 = new getID3;

    /**
     * Analyze the uploaded file.
     *
     * Note: This is not 100% reliable as not all the uploaded files will have moov meta data
     * at the start of the file (in first 5MB). That's why we also run the check in browser,
     * see s3-upload-base.js file where is runs document.createElement('video').
     */
    $this->debug_log( 'validate_file_upload: Running getID3 analysis...' );

    $ThisFileInfo = $getID3->analyze($uploaded_file['tmp_name']);

    if ( $this->is_debug_log_enabled() ) {
      $this->debug_log( 'validate_file_upload: getID3 analysis: ' . wp_json_encode( array(
        'fileformat'       => isset( $ThisFileInfo['fileformat'] ) ? $ThisFileInfo['fileformat'] : null,
        'mime_type'        => isset( $ThisFileInfo['mime_type'] ) ? $ThisFileInfo['mime_type'] : null,
        'playtime_seconds' => isset( $ThisFileInfo['playtime_seconds'] ) ? $ThisFileInfo['playtime_seconds'] : null,
        'video_format'     => isset( $ThisFileInfo['video']['dataformat'] ) ? $ThisFileInfo['video']['dataformat'] : null,
        'resolution_x'     => isset( $ThisFileInfo['video']['resolution_x'] ) ? $ThisFileInfo['video']['resolution_x'] : null,
        'resolution_y'     => isset( $ThisFileInfo['video']['resolution_y'] ) ? $ThisFileInfo['video']['resolution_y'] : null,
        'rotate'           => isset( $ThisFileInfo['video']['rotate'] ) ? $ThisFileInfo['video']['rotate'] : null,
        'audio_format'     => isset( $ThisFileInfo['audio']['dataformat'] ) ? $ThisFileInfo['audio']['dataformat'] : null,
        'errors'           => isset( $ThisFileInfo['error'] ) ? $ThisFileInfo['error'] : array(),
        'warnings'         => isset( $ThisFileInfo['warning'] ) ? $ThisFileInfo['warning'] : array(),
      ) ) );
    }
    
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
      $this->validate_file_upload_error(
        'File type not supported.',
        'getID3 did not detect the MIME type. fileformat: ' . ( isset( $ThisFileInfo['fileformat'] ) ? $ThisFileInfo['fileformat'] : 'none' ) . ', browser MIME type: ' . ( isset( $uploaded_file['type'] ) ? $uploaded_file['type'] : 'none' )
      );
      exit;
    }

    $this->debug_log( 'validate_file_upload: Detected MIME type: ' . $detected_mime_type );

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

        // TODO: Limit vertical videos to 9:16 with minimum width of 720px
        // TODO: New error message: "Maximum vertical video aspect ratio is 9:16"
        // TODO: New error message: "Maximum widescreen video aspect ratio is Cinemascope 2.55:1"

        // For 1:3 aspect ratio
        $minimal_width_4_3 = $minimal_height;
        $minimal_height_4_3 = $minimal_height;

        // For 2.55:1 aspect ratio
        $minimal_width_21_9 = $minimal_width;
        $minimal_height_21_9 = $minimal_width * 1 / 2.55;

        $video_width    = absint( $ThisFileInfo['video']['resolution_x'] );
        $video_height   = absint( $ThisFileInfo['video']['resolution_y'] );
        
        $video_width_check = $video_width;
        $video_height_check = $video_height;
        $force_pass = false;

        // We need to flip the dimentions before checking aspect ratio for vertical videos
        // Alternatively we could parse degrees from $ThisFileInfo['video']['rotate'], but is that commonly used for vertical videos?
        if ( $video_width < $video_height ) {

          // If vertical video is 1080p or higher with a reasonable width, we accept it
          if ( $video_height >= 1080 && $video_width >= 540 ) {
            $force_pass = true;

          } else {
            $video_width_check  = intval( $ThisFileInfo['video']['resolution_y'] );
            $video_height_check = intval( $ThisFileInfo['video']['resolution_x'] );
          }
        }

        if (
          $force_pass ||
          $video_width_check >= $minimal_width && $video_height_check >= $minimal_height ||
          $video_width_check >= $minimal_width_4_3 && $video_height_check >= $minimal_height_4_3 ||
          $video_width_check >= $minimal_width_21_9 && $video_height_check >= $minimal_height_21_9
        ) {
          // Video dimensions are good for one of the aspect ratios

        } else {
          $this->validate_file_upload_error(
            "I'm sorry, your video is only " . absint( $ThisFileInfo['video']['resolution_x'] ) . "x" . absint( $ThisFileInfo['video']['resolution_y'] ) . ". Please re-render it as " . $minimal_video_resolution . " or higher and upload again.",
            'Minimal resolution: ' . $minimal_video_resolution . ' (' . $minimal_width . 'x' . $minimal_height . ')'
          );
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
      $this->validate_file_upload_error( 'File type not allowed: ' . $detected_mime_type );
      exit;
    }

    // Clean up the uploaded file
    unlink( $uploaded_file['tmp_name'] );

    $this->debug_log( 'validate_file_upload: PASSED: ' . $detected_mime_type . ', ' . $video_width . 'x' . $video_height . ', duration: ' . ( isset( $ThisFileInfo['playtime_seconds'] ) ? $ThisFileInfo['playtime_seconds'] : 'unknown' ) );

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
        'fileformat'   => isset( $ThisFileInfo['fileformat'] ) ? $ThisFileInfo['fileformat'] : 'unknown',
        'mime_type'    => $detected_mime_type,
        'filesize'     => $file_size,
        'resolution'   => $video_width . 'x' . $video_height,
        'height'       => $video_height,
        'width'        => $video_width,
        'duration'     => $ThisFileInfo['playtime_seconds'],
        'duration_hms' => flowplayer::format_hms( $ThisFileInfo['playtime_seconds'] ),
      )
    ));
  }

  function multiupload_send_part() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_flowplayer_multiupload_send_part' ) ) {
      wp_send_json( array( 'error' => 'Access denied, please reload the page and try again.' ) );
    }

    $args = array(
      'Bucket'        => $this->get_bucket(),
      'Key'           => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
      'UploadId'      => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] ),
      'PartNumber'    => intval( $_REQUEST['partNumber'] ),
      'ContentLength' => intval( $_REQUEST['contentLength'] )
    );

    if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
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

    // Try to complete the upload 4 times
    $attempt = 1;

    while( 1 ) {

      // Initial wait as these file parts may take a bit of time to really appear
      sleep(5);

      try {
        $args = array(
          'Bucket'   => $this->get_bucket(),
          'Key'      => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
          'UploadId' => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] ),
        );

        if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
          FV_Player_Coconut()->plugin_api->log( "multiupload_complete: S3 listParts: " . print_r( $args, true ) );
        }

        $partsModel = $this->s3("listParts", $args);

      } catch ( Exception $e ) {
        if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
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
          'Bucket'   => $this->get_bucket(),
          'Key'      => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
          'UploadId' => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] ),
          'MultipartUpload' => array(
            "Parts" => $parts,
          )
        );

        if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
          FV_Player_Coconut()->plugin_api->log( "multiupload_complete: S3 completeMultipartUpload: " . print_r( $args, true ) );
        }

        $ret = $this->s3( "completeMultipartUpload", $args )->toArray();

        // Do not try again if it succeeded!
        break;

      } catch ( Exception $e ) {
        $attempt++;

        if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
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

    // if initial pre-upload request fails, we'll have no sendBackData to abort
    if ( !empty( $_REQUEST['sendBackData'] ) ) {

      $args = array(
        'Bucket'   => $this->get_bucket(),
        'Key'      => sanitize_text_field( $_REQUEST['sendBackData']['key'] ),
        'UploadId' => sanitize_text_field( $_REQUEST['sendBackData']['uploadId'] )
      );

      if ( function_exists( 'FV_Player_Coconut' ) && ! empty( FV_Player_Coconut()->plugin_api ) ) {
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
