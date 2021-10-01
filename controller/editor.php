<?php

add_action( 'admin_enqueue_scripts', 'fv_player_shortcode_editor_scripts' );

function fv_player_shortcode_editor_scripts( $page ) {
  if( $page !== 'post.php' && $page !== 'post-new.php' && ( empty($_GET['page']) || ($_GET['page'] != 'fvplayer' && $_GET['page'] != 'fv_player') ) ) {
    return;
  }
  
  fv_player_shortcode_editor_scripts_enqueue();
}




function fv_player_shortcode_editor_scripts_enqueue() {  
  global $fv_wp_flowplayer_ver;
  wp_register_script('fvwpflowplayer-domwindow', flowplayer::get_plugin_url().'/js/jquery.colorbox-min.js',array('jquery'), $fv_wp_flowplayer_ver  );  
  wp_enqueue_script('fvwpflowplayer-domwindow');  
  
  wp_register_script('fvwpflowplayer-shortcode-editor', flowplayer::get_plugin_url().'/js/shortcode-editor.js',array('jquery','jquery-ui-sortable'), $fv_wp_flowplayer_ver.'-fix' );
  wp_register_script('fvwpflowplayer-editor-screenshots', flowplayer::get_plugin_url().'/js/editor-screenshots.js',array('jquery','fvwpflowplayer-shortcode-editor','flowplayer'), $fv_wp_flowplayer_ver );

  wp_localize_script( 'fvwpflowplayer-shortcode-editor', 'fv_player_editor_conf', array(
    'admin_url' => admin_url('admin.php?page=fv_player'),
    'home_url' => home_url('/'),
    'db_import_nonce' => wp_create_nonce( "fv-player-db-import-".get_current_user_id() ),
    'db_load_nonce' => wp_create_nonce( "fv-player-db-load-".get_current_user_id() ),
    'preview_nonce' => wp_create_nonce( "fv-player-preview-".get_current_user_id() ),
    'splashscreen_nonce' => wp_create_nonce( "fv-player-splashscreen-".get_current_user_id()),
    'shortcode_args_to_preserve' => array(
      'ab',
      'ad',
      'ad_height',
      'ad_width',
      'autoplay',
      'controlbar',
      'embed',
      'fullscreen',
      'height',
      'liststyle',
      'logo',
      'midroll',
      'playlist_advance',
      'playlist_hide',
      'playlist_start',
      'share',
      'sort',
      'vast',
      'volume',
      'width'
    ),
    'shortcode_args_not_db_compatible' => array(
      'fullscreen',
      'logo',
      'playlist_advance',
      'sort',
      'volume'
    ),
    'have_fv_player_vimeo_live' => class_exists('FV_Player_Vimeo_Live_Stream')
  ) );
  
  wp_localize_script( 'fvwpflowplayer-editor-screenshots', 'fv_player_editor_conf_screenshots', array(
    'disable_domains' => apply_filters( 'fv_player_editor_screenshot_disable_domains', array() )
  ) );

  wp_enqueue_script('fvwpflowplayer-shortcode-editor');
  wp_enqueue_script('fvwpflowplayer-editor-screenshots');
  
  wp_enqueue_style('fvwpflowplayer-domwindow-css', flowplayer::get_plugin_url().'/css/colorbox.css', '', $fv_wp_flowplayer_ver, 'screen');
  wp_enqueue_style('fvwpflowplayer-shortcode-editor', flowplayer::get_plugin_url().'/css/shortcode-editor.css', '', $fv_wp_flowplayer_ver, 'screen');
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

add_action( 'enqueue_block_editor_assets', 'fv_wp_flowplayer_gutenberg_editor_load' );

function fv_wp_flowplayer_gutenberg_editor_load() {
  add_action( 'admin_footer', 'fv_wp_flowplayer_edit_form_after_editor', 0 );
	
  // if we are loading for Gutenberg, then forget about the load method for old editor
  remove_action( 'edit_form_after_editor', 'fv_wp_flowplayer_edit_form_after_editor' );
}

add_action( 'edit_form_after_editor', 'fv_wp_flowplayer_edit_form_after_editor' );

function fv_wp_flowplayer_edit_form_after_editor( ) {
  require_once dirname( __FILE__ ) . '/../view/wizard.php';
  
  // todo: will some of this break page builders?
  global $fv_fp_scripts, $fv_fp;
  $fv_fp_scripts = array( 'fv_player_admin_load' => array( 'load' => true ) ); //  without this or option js-everywhere the JS won't load
  $fv_fp->load_hlsjs= true;
  $fv_fp->load_dash = true;
  $fv_fp->load_tabs = true;
  
  if( !fv_player_extension_version_is_min('7.4.46.727','pro') ) {
    global $FV_Player_Pro;
    if( isset($FV_Player_Pro) && $FV_Player_Pro ) {
      $FV_Player_Pro->bYoutube = true;
      add_action('admin_footer', array( $FV_Player_Pro, 'styles' ) );
      add_action('admin_footer', array( $FV_Player_Pro, 'scripts' ) );
    }
  }

  if( !fv_player_extension_version_is_min('7.4.46.727','vast') ) {
    global $FV_Player_VAST ;
    if( isset($FV_Player_VAST ) && $FV_Player_VAST ) {
      add_action('admin_footer', array( $FV_Player_VAST , 'func__wp_enqueue_scripts' ) );
    }
  }
  
  if( !fv_player_extension_version_is_min('7.4.46.727','alternative-sources') ) {
    global $FV_Player_Alternative_Sources ;
    if( isset($FV_Player_Alternative_Sources ) && $FV_Player_Alternative_Sources ) {
      add_action('admin_footer', array( $FV_Player_Alternative_Sources , 'enqueue_scripts' ) );
    }
  }
  
  // Tell all the (modern) extensions to load frontend+backend assets 
  do_action('fv_player_extensions_admin_load_assets');

  add_action('admin_footer','flowplayer_prepare_scripts');
}

//  allow .vtt subtitle files
add_filter( 'wp_check_filetype_and_ext', 'fv_flowplayer_filetypes', 10, 4 );

function fv_flowplayer_filetypes( $aFile ) {
  $aArgs = func_get_args();
  foreach( array( 'vtt', 'webm', 'ogg') AS $item ) {
    if( isset($aArgs[2]) && preg_match( '~\.'.$item.'~', $aArgs[2] ) ) {
      $aFile['type'] = $item;
      $aFile['ext'] = $item;
      $aFile['proper_file_name'] = $aArgs[2];    
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
  if( !$fv_fp->_get_option( array('integrations','featured_img') ) ){
    return;
  }
  
  $thumbnail_id = get_post_thumbnail_id($post_id);
  if( $thumbnail_id != 0 ) {
    return;
  }
  
  $post = get_post($post_id);
  
  $url = false;
  
  if( preg_match('/(?:splash=\\\?")([^"]*.(?:jpg|gif|png))/', $post->post_content, $splash) ) { // parse splash="..." in post content
     $url = $splash[1];
  }
  
  if( !$url && preg_match('/\[fvplayer.*?id="(\d+)/', $post->post_content, $id) ) { // parse [fvplayer id="..."] shortcode in post content
    global $FV_Player_Db;
    $atts = $FV_Player_Db->getPlayerAttsFromDb( array( 'id' => $id[1] ) );
    if( !empty($atts['splash']) ) {
      $url = $atts['splash'];
    }
  }
    
  if( !$url && $aMetas = get_post_custom($post_id) ) { // parse [fvplayer id="..."] shortcode in post meta
    foreach( $aMetas AS $key => $aMeta ) {
      foreach( $aMeta AS $shortcode ) {
        if( preg_match('/\[fvplayer.*?id="(\d+)/', $shortcode, $id) ) {
          global $FV_Player_Db;
          $atts = $FV_Player_Db->getPlayerAttsFromDb( array( 'id' => $id[1] ) );
          if( !empty($atts['splash']) ) {
            $url = $atts['splash'];
          }
        }
      }
    }
  }
  
  if( !$url ) return;
  
  $thumbnail_id = fv_wp_flowplayer_save_to_media_library($url, $post_id);
  if($thumbnail_id){
    set_post_thumbnail($post_id, $thumbnail_id);
  }
  
}

function fv_wp_flowplayer_construct_file_name( $post_id ) {
  $file_name = get_the_title( $post_id );
  $file_name = sanitize_title( $file_name, $post_id );
  $file_name = urldecode( $file_name );
  $file_name = preg_replace( '/[^a-zA-Z0-9\-]/', '', $file_name );
  $file_name = substr( $file_name, 0, 32 );
  $file_name = trim( $file_name, '-' );
  if ( $file_name == '' ) $file_name = (string) $post_id;
  return $file_name;
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
    $new_file_name = fv_wp_flowplayer_construct_file_name( $post_id ) . $image_extension;

    // Save the image bits using the new file_name
    $upload = wp_upload_bits( $new_file_name, null, $image_contents );

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
      
      // Define attachment metadata
      $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
      
      // Assign metadata to attachment
      wp_update_attachment_metadata( $attach_id,  $attach_data );

    }

  }

  return $attach_id;

}

add_action( 'wp_ajax_fv_player_splashcreen_action', 'fv_player_splashcreen_action' );

function fv_player_splashcreen_action() {
  $jsonReturn = '';

   function get_title_from_url($url) {
    $arr = explode('/', $url);
    $caption = end($arr);

    if( strpos($caption, ".m3u8") !== false ) {
      unset($arr[count($arr)-1]);
      $caption = end($arr);
    }

    $vid_replacements = array(
      'watch?v=' => 'YouTube: '
    );

    $caption = str_replace(array_keys($vid_replacements), array_values($vid_replacements), $caption);

    if( is_numeric($caption) && intval($caption) == $caption && stripos($url,'vimeo.com/') !== false ) {
      $caption = "Vimeo: ".$caption;
    } 
    return urldecode($caption);
  }

  if( check_ajax_referer( "fv-player-splashscreen-".get_current_user_id(), "security" , false ) == 1 ) {
    $title = $_POST['title'];

    $limit = 128 - 5; // .jpeg

    // $allowed_mimes = array(
    //   'jpg|jpeg|jpe'  => 'image/jpeg',
    //   'gif'           => 'image/gif',
    //   'png'           => 'image/png',
    // );

    $title = get_title_from_url($title);
    $title = sanitize_title($title);
    $title = mb_strimwidth($title, 0, $limit, '', 'UTF-8');

    $upload_dir = wp_upload_dir();
    $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

    $file_name = $title .'.jpg';

    $file_path = $upload_path . $file_name;

    // $hashed_file_name = md5( $file_name . microtime() ) . '_' . $file_name;

    if( isset( $_POST['img'] ) ) { // screenshot - decode and save
      $img = $_POST['img'];

      $img = str_replace('data:image/jpeg;base64,', '', $img);
      $img = str_replace(' ', '+', $img);

      $image_data = base64_decode($img);

      $image_upload = file_put_contents( $file_path, $image_data );

      if( !$image_upload ) {
        $jsonReturn = array(
          'src'     =>  '',
          'error'   =>  'Failed to save splash image'
        );
      }
    } else if( isset( $_POST['url'] ) ) { // splash - we must download it
      // if the function its not available, require it
      if ( ! function_exists( 'download_url' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
      }

      $file_path = download_url( $_POST['url'] );

      if ( is_wp_error( $file_path ) ) {
        @unlink( $file_path );
        $jsonReturn = array(
          'src'     =>  '',
          'error'   =>  'Cannot download splash - ' . $file_path->get_error_message()
        );
      }

    } else {
      $jsonReturn = array(
        'src'     =>  '',
        'error'   =>  'No image data'
      );
    }

    if( empty( $jsonReturn ) ) { // no error - continue
      // Handle upload file
      if( !function_exists( 'wp_handle_sideload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
      }

      // Debug error
      if( !function_exists( 'wp_get_current_user' ) ) {
        require_once( ABSPATH . 'wp-includes/pluggable.php' );
      }

      // New file
      $file             = array();
      $file['error']    = '';
      $file['tmp_name'] = $file_path;
      $file['name']     = $file_name;
      $file['type']     = mime_content_type( $file_path );
      $file['size']     = filesize( $file_path );

      $file_return = wp_handle_sideload( $file, array( 'test_form' => false ) );

      if ( ! empty( $file_return['error'] ) ) {
        $jsonReturn = array(
          'src'     =>  '',
          'error'   =>  $file_return['error']
        ); 
      } else {
        $file_name = $file_return['file'];

        $attachment = array(
          'post_mime_type' => $file_return['type'],
          'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_name)),
          'post_content' => '',
          'post_status' => 'inherit',
          'guid' => $upload_dir['url'] . '/' . basename($file_name)
        );

        $attach_id = wp_insert_attachment( $attachment, $file_name, 0, true );

        if( is_wp_error( $attach_id ) ) {
          $jsonReturn = array(
            'src'     =>  '',
            'error'   =>  $attach_id->get_error_message()
          );
        } else {
          require_once(ABSPATH . 'wp-admin/includes/image.php');
        
          $attach_data = wp_generate_attachment_metadata( $attach_id, $file_name );
          wp_update_attachment_metadata( $attach_id, $attach_data );

          $src = wp_get_attachment_image_url($attach_id, 'full', false);

          $jsonReturn = array(
            'src'     => $src,
            'attachment_id' => $attach_id,
            'error'   => ''
          );
        }
      }
    }

  } else {
    $jsonReturn = array(
      'src'     =>  '',
      'error'   =>  'Nonce error - please reload your page'
    );
  }

  header('Content-Type: application/json');
  echo json_encode($jsonReturn);

  wp_die(); 
}




/*
Elementor support
*/
add_action( 'elementor/editor/wp_head', 'fv_player_shortcode_editor_scripts_enqueue' );
add_action( 'elementor/editor/wp_head', 'fv_wp_flowplayer_edit_form_after_editor' );
add_action( 'elementor/editor/wp_head', 'flowplayer_prepare_scripts' );
