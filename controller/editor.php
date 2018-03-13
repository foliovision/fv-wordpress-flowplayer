<?php
add_action( 'admin_enqueue_scripts', 'fv_player_shortcode_editor_scripts' );

function fv_player_shortcode_editor_scripts( $page ) {
  if( $page !== 'post.php' && $page !== 'post-new.php' && ( empty($_GET['page']) || $_GET['page'] != 'fvplayer' ) ) {
    return;
  }
  
  global $fv_wp_flowplayer_ver;
  
  
  wp_register_script('fvwpflowplayer-domwindow', flowplayer::get_plugin_url().'/js/jquery.colorbox-min.js',array('jquery'), $fv_wp_flowplayer_ver  );  
  wp_enqueue_script('fvwpflowplayer-domwindow');  
  
  wp_register_script('fvwpflowplayer-shortcode-editor', flowplayer::get_plugin_url().'/js/shortcode-editor.js',array('jquery'), $fv_wp_flowplayer_ver );
  wp_register_script('fvwpflowplayer-shortcode-editor-old', flowplayer::get_plugin_url().'/js/shortcode-editor.old.js',array('jquery'), $fv_wp_flowplayer_ver );
  
  global $fv_fp;
  if( isset($fv_fp->conf["interface"]['shortcode_editor_old']) && $fv_fp->conf["interface"]['shortcode_editor_old'] == 'true' ) {
    wp_enqueue_script('fvwpflowplayer-shortcode-editor-old');
  } else {
    wp_enqueue_script('fvwpflowplayer-shortcode-editor');
  }
  
  wp_register_style('fvwpflowplayer-domwindow-css', flowplayer::get_plugin_url().'/css/colorbox.css','','1.0','screen');
  wp_enqueue_style('fvwpflowplayer-domwindow-css');
}




add_action('media_buttons', 'flowplayer_add_media_button', 10);

function flowplayer_add_media_button() {
  if( stripos( $_SERVER['REQUEST_URI'], 'post.php' ) !== FALSE ||
     stripos( $_SERVER['REQUEST_URI'], 'post-new.php' ) !== FALSE ||
     isset($_POST['action']) && $_POST['action'] == 'vc_edit_form'
     ) {
    global $post;
    $plugins = get_option('active_plugins');
    $found = false;
    foreach ( $plugins AS $plugin ) {
      if( stripos($plugin,'foliopress-wysiwyg') !== FALSE )
        $found = true;
    }
    $button_tip = 'Insert a video';
    $wizard_url = 'media-upload.php?post_id='.$post->ID.'&type=fv-wp-flowplayer';
    $icon = '<span> </span>';
  
    echo '<a title="' . __('Add FV Player', 'fv-wordpress-flowplayer') . '" title="' . $button_tip . '" href="#" class="button fv-wordpress-flowplayer-button" >'.$icon.' Player</a>';
  }
}




add_action('media_upload_fvplayer_video', '__return_false'); // keep for compatibility!




add_action( 'edit_form_after_editor', 'fv_wp_flowplayer_edit_form_after_editor' );

function fv_wp_flowplayer_edit_form_after_editor( ) {
  global $fv_fp;
  if( isset($fv_fp->conf["interface"]['shortcode_editor_old']) && $fv_fp->conf["interface"]['shortcode_editor_old'] == 'true' ) {
    include dirname( __FILE__ ) . '/../view/wizard.old.php';
  } else {
    include dirname( __FILE__ ) . '/../view/wizard.php';
    
    // todo: will some of this break page builders?
    global $fv_fp_scripts, $fv_fp;
    $fv_fp_scripts = array( 'fv_player_admin_load' => array( 'load' => true ) ); //  without this or option js-everywhere the JS won't load
    $fv_fp->load_hlsjs= true;
    $fv_fp->load_dash = true;
    $fv_fp->load_tabs = true;
    
    global $FV_Player_Pro;
    if( isset($FV_Player_Pro) && $FV_Player_Pro ) {
      $FV_Player_Pro->bYoutube = true;
      //  todo: there should be a better way than this
      add_action('admin_footer', array( $FV_Player_Pro, 'styles' ) );
      add_action('admin_footer', array( $FV_Player_Pro, 'scripts' ) );
    }

    global $FV_Player_VAST ;
    if( isset($FV_Player_VAST ) && $FV_Player_VAST ) {
      //  todo: there should be a better way than this
      add_action('admin_footer', array( $FV_Player_VAST , 'styles' ) );
      add_action('admin_footer', array( $FV_Player_VAST , 'func__wp_enqueue_scripts' ) );
    }

    global $FV_Player_Alternative_Sources ;
    if( isset($FV_Player_Alternative_Sources ) && $FV_Player_Alternative_Sources ) {
      //  todo: there should be a better way than this
      add_action('admin_footer', array( $FV_Player_Alternative_Sources , 'enqueue_scripts' ) );
    }

    add_action('admin_footer','flowplayer_prepare_scripts');    
  }
}

//  allow .vtt subtitle files
add_filter( 'wp_check_filetype_and_ext', 'fv_flowplayer_filetypes', 10, 4 );

function fv_flowplayer_filetypes( $aFile ) {
  $aArgs = func_get_args();
  foreach( array( 'vtt', 'webm', 'ogg') AS $item ) {
    if( isset($aArgs[2]) && preg_match( '~\.'.$item.'~', $aArgs[2] ) ) {
      $aFile['type'] = $item;
      $aFile['ext'] = $item;
      $aFile['proper_filename'] = $aArgs[2];    
    }
  }
  return $aFile;
}




add_filter('admin_print_scripts', 'flowplayer_print_scripts');

function flowplayer_print_scripts() {
  wp_enqueue_script('media-upload');
  wp_enqueue_script('thickbox');
}




add_action('admin_print_styles', 'flowplayer_print_styles');

function flowplayer_print_styles() {
  wp_enqueue_style('thickbox');
}




add_action( 'save_post', 'fv_wp_flowplayer_save_post' );




add_action( 'save_post', 'fv_wp_flowplayer_featured_image' , 10000 );

function fv_wp_flowplayer_featured_image($post_id) {
  if( $parent_id = wp_is_post_revision($post_id) ) {
    $post_id = $parent_id;
  }
  
  global $fv_fp;
  if( !isset($fv_fp->conf['integrations']['featured_img']) || $fv_fp->conf['integrations']['featured_img'] != 'true' ){
    return;
  }
  
  $thumbnail_id = get_post_thumbnail_id($post_id);
  if( $thumbnail_id != 0 ) {
    return;
  }
  
  $post = get_post($post_id);
  if( !$post || empty($post->post_content) ){
    return;
  }
  
  $sThumbUrl = array();
  if (!preg_match('/(?:splash=\\\?")([^"]*.(?:jpg|gif|png))/', $post->post_content, $sThumbUrl) || empty($sThumbUrl[1])) {
    return;
  }
  
  $thumbnail_id = fv_wp_flowplayer_save_to_media_library($sThumbUrl[1], $post_id);
  if($thumbnail_id){
    set_post_thumbnail($post_id, $thumbnail_id);
  }
  
}

function fv_wp_flowplayer_construct_filename( $post_id ) {
  $filename = get_the_title( $post_id );
  $filename = sanitize_title( $filename, $post_id );
  $filename = urldecode( $filename );
  $filename = preg_replace( '/[^a-zA-Z0-9\-]/', '', $filename );
  $filename = substr( $filename, 0, 32 );
  $filename = trim( $filename, '-' );
  if ( $filename == '' ) $filename = (string) $post_id;
  return $filename;
}

function fv_wp_flowplayer_save_to_media_library( $image_url, $post_id ) {
  
  $error = '';
  $response = wp_remote_get( $image_url );
  if( is_wp_error( $response ) ) {
    $error = new WP_Error( 'thumbnail_retrieval', sprintf( __( 'Error retrieving a thumbnail from the URL <a href="%1$s">%1$s</a> using <code>wp_remote_get()</code><br />If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'video-thumbnails' ), $image_url ) . '<br>' . __( 'Error Details:', 'video-thumbnails' ) . ' ' . $response->get_error_message() );
  } else {
    $image_contents = $response['body'];
    $image_type = wp_remote_retrieve_header( $response, 'content-type' );
  }
  
  if ( $error != '' || $image_contents == '' ) {
    return false;
  } else {

    // Translate MIME type into an extension
    if ( $image_type == 'image/jpeg' ) {
      $image_extension = '.jpg';
    } elseif ( $image_type == 'image/png' ) {
      $image_extension = '.png';
    } elseif ( $image_type == 'image/gif' ) {
      $image_extension = '.gif';
    } else {
      return new WP_Error( 'thumbnail_upload', __( 'Unsupported MIME type:', 'video-thumbnails' ) . ' ' . $image_type );
    }

    // Construct a file name with extension
    $new_filename = fv_wp_flowplayer_construct_filename( $post_id ) . $image_extension;

    // Save the image bits using the new filename    
    $upload = wp_upload_bits( $new_filename, null, $image_contents );    

    // Stop for any errors while saving the data or else continue adding the image to the media library
    if ( $upload['error'] ) {
      $error = new WP_Error( 'thumbnail_upload', __( 'Error uploading image data:', 'video-thumbnails' ) . ' ' . $upload['error'] );
      return $error;
    } else {

      $wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );

      $upload = apply_filters( 'wp_handle_upload', array(
        'file' => $upload['file'],
        'url'  => $upload['url'],
        'type' => $wp_filetype['type']
      ), 'sideload' );

      // Contstruct the attachment array
      $attachment = array(
        'post_mime_type'	=> $upload['type'],
        'post_title'		=> get_the_title( $post_id ),
        'post_content'		=> '',
        'post_status'		=> 'inherit'
      );
      // Insert the attachment
      $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

    }

  }

  return $attach_id;

}


add_action( 'wp_ajax_load_s3_assets', 'fv_wp_flowplayer_ajax_load_s3_assets' );
add_action( 'wp_enqueue_scripts', 'fv_wp_flowplayer_s3_browse_register_scripts' );



function fv_wp_flowplayer_s3_browse_register_scripts() {
  wp_register_script( 'browse-s3-js', plugins_url( '/js/s3-browser.js' , __FILE__ ), array(), '', true );
  wp_enqueue_script( 'browse-s3-js' );
}



function fv_wp_flowplayer_include_aws_sdk() {
  if (!class_exists('Aws\S3\S3Client')) {
    require_once( dirname( __FILE__ ) . "/../includes/aws/aws-autoloader.php" );
  }
}



function fv_wp_flowplayer_ajax_load_s3_assets() {
  fv_wp_flowplayer_include_aws_sdk();
  global $fv_fp, $s3Client;

  $regions = $fv_fp->_get_option('amazon_region');
  $secrets = $fv_fp->_get_option('amazon_secret');
  $keys    = $fv_fp->_get_option('amazon_key');
  $buckets = $fv_fp->_get_option('amazon_bucket');
  $region_names = fv_player_get_aws_regions();
  $domains = array();

  if (isset($_POST['bucket']) && isset($buckets[$_POST['bucket']])) {
    $array_id = $_POST['bucket'];
  } else {
    $array_id = 0;
  }

  // remove all buckets with missing name
  $keep_checking = true;
  while ($keep_checking) {
    // break here if there are no buckets left
    if (!count($buckets)) {
      break;
    } else {
      // remove any bucket without a name
      $all_ok = true;
      foreach ($buckets as $bucket_id => $bucket_name) {
        if (!$bucket_name) {
          unset($buckets[$bucket_id], $regions[$bucket_id], $secrets[$bucket_id], $keys[$bucket_id]);

          // adjust the selected bucket to the first array ID if we just
          // removed the one we chose to display
          if ($array_id == $bucket_id) {
            reset($buckets);
            $array_id = key($buckets);
          }

          $all_ok = false;
          break;
        }
      }

      // all buckets have regions, we can stop the check now
      if ($all_ok) {
        $keep_checking = false;
      }
    }
  }

  // if the selected bucket is a region-less one, change the $array_id variable
  // to one that has a region
  $regioned_bucket_found = (count($buckets) ? true : false);
  if (!$regions[$array_id]) {
    $regioned_bucket_found = false;
    foreach ($buckets as $bucket_id => $unused) {
      if ($regions[$bucket_id]) {
        $array_id = $bucket_id;
        $regioned_bucket_found = true;
        break;
      }
    }
  }

  if ($regioned_bucket_found) {
    $region = $regions[ $array_id ];
    $secret = $secrets[ $array_id ];
    $key    = $keys[ $array_id ];
    $bucket = $buckets[ $array_id ];

    // load CloudFront setttings
    $cfDomains = $fv_fp->_get_option( array('pro','cf_domains_list') );
    $cfBuckets = $fv_fp->_get_option( array('pro','cf_buckets_list') );
    $cf_domain_to_use = null;

    // check if we have current bucket assigned to an URL
    if (is_array($cfBuckets) && count($cfBuckets)) {
      foreach ($cfBuckets as $cf_bucket_index => $cf_bucket_id) {
        if ($cf_bucket_id == $array_id) {
          $cf_domain_to_use = strtolower($cfDomains[$cf_bucket_index]);

          // add HTTP, if not set
          if (substr($cf_domain_to_use, 0, 4) !== 'http') {
            $cf_domain_to_use = 'http://' . $cf_domain_to_use;
          }

          $domains[ $array_id ] = $cf_domain_to_use;

          break;
        }
      }
    }

    $credentials = new Aws\Credentials\Credentials( $key, $secret );

    // instantiate the S3 client with AWS credentials
    $s3Client = Aws\S3\S3Client::factory( array(
      'credentials' => $credentials,
      'region'      => $region,
      'version'     => 'latest'
    ) );

    try {
      $objects = $s3Client->getIterator( 'ListObjects', array( 'Bucket' => $bucket ) );

      $path_array = array();
      $size_array = array();
      $link_array = array();

      foreach ( $objects as $object ) {
        if ( ! isset( $objectarray ) ) {
          $objectarray = array();
        }
        //print_r($object);
        $name = $object['Key'];
        $size = $object['Size'];

        if ( $object['Size'] != '0' ) {

          $link = (string) $s3Client->getObjectUrl( $bucket, $name );

          // replace link with CloudFront URL, if we have one
          if ($cf_domain_to_use !== null) {
            // replace S3 URLs with buckets in the S3 subdomain
            $link = preg_replace('/https?:\/\/' . $bucket . '\.s3[^.]*\.amazonaws\.com\/(.*)/i', rtrim($cf_domain_to_use, '/').'/$1', $link);

            // replace S3 URLs with bucket name as a subfolder
            $link = preg_replace('/https?:\/\/[^\/]+\/' . $bucket . '\/(.*)/i', rtrim($cf_domain_to_use, '/').'/$1', $link);
          }

          $path = 'Home/' . $name;

          $path_array[] = $path;
          $size_array[] = $size;
          $link_array[] = $link;

        }

      }

      function &placeInArray( array &$dest, array $path_array, $size, $pathorig, $link ) {
        // If we're at the leaf of the tree, just push it to the array and return
        //echo $pathorig;
        //echo $size."<br>";

        global $folders_added;
        if ( count( $path_array ) === 1 ) {
          if ( $path_array[0] !== '' ) {
            $file_array         = array();
            $file_array['name'] = $path_array[0];
            $file_array['size'] = $size;
            $file_array['type'] = 'file';
            $file_array['path'] = $pathorig;
            $file_array['link'] = $link;
            array_push( $dest['items'], $file_array );
          }

          return $dest;
        }

        // If not (if multiple elements exist in path_array) then shift off the next path-part...
        // (this removes $path_array's first element off, too)
        $path = array_shift( $path_array );

        if ( $path ) {

          $newpath_temp = explode( $path, $pathorig );
          $newpath      = $newpath_temp[0] . $path . '/';
          // ...make a new sub-array for it...


          //if (!isset($dest['items'][$path])) {
          if ( ! in_array( $newpath, $folders_added, true ) ) {
            $dest['items'][] = array(

              'name'  => $path,
              'type'  => 'folder',
              'path'  => $newpath,
              'items' => array()

            );
            $folders_added[] = $newpath;
            //print_r($folders_added);
          }
          $count = count( $dest['items'] );
          $count --;
          //echo $count.'<br>';
          //print_r($dest['items'][$path]);

          // ...and continue the process on the sub-array
          return placeInArray( $dest['items'][ $count ], $path_array, $size, $pathorig, $link );
        }

        // This is just here to blow past multiple slashes (an empty path-part), like
        // /path///to///thing
        return placeInArray( $dest, $path_array, $size, $pathorig, $link );
      }

      $output        = array();
      $folders_added = array();
      $i             = 0;
      foreach ( $path_array as $path ) {
        $size = $size_array[ $i ];
        $link = $link_array[ $i ];
        placeInArray( $output, explode( '/', $path ), $size, $path, $link );
        $i ++;
      }
    } catch ( Aws\S3\Exception\S3Exception $e ) {
      //echo $e->getMessage() . "\n";
      $err = $e->getMessage();
      $output['items'] = array(
        'items' => array(),
        'name' => '/',
        'path' => '/',
        'type' => 'folder'
      );
    }
  }

  // prepare list of buckets for the selection dropdown
  $buckets_output = array();
  $negative_ids = -1;
  foreach ( $buckets as $bucket_index => $bucket_name ) {
    $has_all_data = ($regions[ $bucket_index ] && $keys[ $bucket_index ] && $secrets[ $bucket_index ]);
    $buckets_output[] = array(
      'id'   => ($has_all_data ? $bucket_index : $negative_ids--),
      'name' => $bucket_name . ' (' . ($regions[ $bucket_index ] ? $regions[ $bucket_index ] : translate('no region', 'fv-wordpress-flowplayer')) . ')' . (!empty($domains[$bucket_index]) ? ' - '. $domains[$bucket_index] : '')
    );
  }

  $json_final = array(
    'buckets'          => $buckets_output,
    'region_names'     => $region_names,
    'active_bucket_id' => $array_id,
    'items'            => (
      $regioned_bucket_found ?
        $output['items'][0] :
        array(
          'items' => array(),
          'name' => '/',
          'path' => '/',
          'type' => 'folder'
        )
    )
  );

  if (isset($err) && $err) {
    $json_final['err'] = $err;
  }

  wp_send_json( $json_final );
  wp_die();
}