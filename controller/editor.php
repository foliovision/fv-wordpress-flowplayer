<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

add_action( 'admin_enqueue_scripts', 'fv_player_shortcode_editor_scripts' );

function fv_player_shortcode_editor_scripts( $page ) {
  if( $page !== 'post.php' && $page !== 'post-new.php' && $page !== 'site-editor.php' && ( empty($_GET['page']) || ($_GET['page'] != 'fvplayer' && $_GET['page'] != 'fv_player') ) ) {
    return;
  }

  fv_player_shortcode_editor_scripts_enqueue();
}




function fv_player_shortcode_editor_scripts_enqueue( $extra_args = array() ) {
  global $current_screen, $fv_wp_flowplayer_ver;

  $url = flowplayer::get_plugin_url();

  wp_enqueue_script('fv-player-editor-modal', $url.'/js/fv-player-editor-modal.js', array('jquery'), filemtime( dirname(__FILE__).'/../js/fv-player-editor-modal.js' ), true );

  wp_enqueue_script('fvwpflowplayer-shortcode-editor', $url.'/js/shortcode-editor.js',array('jquery','jquery-ui-sortable'), filemtime( dirname(__FILE__).'/../js/shortcode-editor.js' ), true );
  wp_enqueue_script('fv-player-editor-extras', $url.'/js/editor-extras.js',array('fvwpflowplayer-shortcode-editor'), filemtime( dirname(__FILE__).'/../js/editor-extras.js' ), true );
  wp_enqueue_script('fvwpflowplayer-editor-screenshots', $url.'/js/editor-screenshots.js', array( 'fvwpflowplayer-shortcode-editor','flowplayer' ), filemtime( dirname(__FILE__).'/../js/editor-screenshots.js' ), true );

  $fv_player_editor_conf = array(
    'admin_url' => admin_url('admin.php?page=fv_player'),
    'home_url' => home_url('/'),
    'db_import_nonce' => wp_create_nonce( "fv-player-db-import-".get_current_user_id() ),
    'db_load_nonce' => wp_create_nonce( "fv-player-db-load-".get_current_user_id() ),
    'edit_nonce' => wp_create_nonce( "fv-player-edit" ),
    'edit_posts_cell_nonce' => wp_create_nonce( "fv-player-edit_posts_cell_nonce-".get_current_user_id() ),
    'table_new_row_nonce' => wp_create_nonce( "fv-player-table_new_row_nonce-".get_current_user_id() ),
    'preview_nonce' => wp_create_nonce( "fv-player-preview-".get_current_user_id() ),
    'search_nonce' => wp_create_nonce( "fv-player-editor-search-nonce" ),
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
    'have_fv_player_vimeo_live' => class_exists('FV_Player_Vimeo_Live_Stream'),
    'is_fv_player_screen' => !empty($current_screen->id) && $current_screen->id == 'toplevel_page_fv_player',
    'is_edit_posts_screen' => !empty($current_screen->base) && $current_screen->base == 'edit' && !empty($current_screen->post_type)
  );

  // TODO: Ideally these inputs would not only be hidden, but they wouldn't save
  if( !empty($extra_args['hide']) ) {
    $extra_args['hide'] = explode( ',', $extra_args['hide'] );
  }

  foreach( array(
    'field'    => 'field_selector',
    'frontend' => 'frontend',
    'hide'     => 'hide',
    'library'  => 'library',  // TODO: Hide the Media Library buttons if the library is not found at all
    'tabs'     => 'tabs', // TODO: Ideally these inputs would not only be hidden, but they wouldn't save
  ) AS $key => $setting ) {
    if( !empty($extra_args[ $key ]) ) {
      $fv_player_editor_conf[ $setting ] = $extra_args[ $key ];
    }
  }

  wp_localize_script( 'fvwpflowplayer-shortcode-editor', 'fv_player_editor_conf', $fv_player_editor_conf );

  wp_localize_script( 'fvwpflowplayer-shortcode-editor', 'fv_player_editor_translations', array(
    'embed_notice' => __('Embed feature not supported in editor preview', 'fv-wordpress-flowplayer'),
    'link_notice' => __('Link feature not supported in editor preview', 'fv-wordpress-flowplayer'),
    'screenshot_cors_error' => __('Cannot obtain video screenshot, please make sure the video is served with <a href="https://foliovision.com/player/video-hosting/hls#hls-js">CORS headers</a>.', 'fv-wordpress-flowplayer'),
  ) );

  wp_localize_script( 'fvwpflowplayer-editor-screenshots', 'fv_player_editor_conf_screenshots', array(
    'disable_domains' => apply_filters( 'fv_player_editor_screenshot_disable_domains', array() )
  ) );

  // TODO: Eliminate, keep the close button
  wp_enqueue_style('fvwpflowplayer-domwindow-css', flowplayer::get_plugin_url().'/css/colorbox.css', '', $fv_wp_flowplayer_ver, 'screen');
  wp_enqueue_style('fvwpflowplayer-shortcode-editor', flowplayer::get_plugin_url().'/css/shortcode-editor.css', '', filemtime( dirname(__FILE__).'/../css/shortcode-editor.css' ), 'screen');
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
add_action( 'fv_player_db_save', 'fv_wp_flowplayer_post_add_featured_image' );

function fv_wp_flowplayer_post_add_featured_image( $player_id ) {
  global $FV_Player_Db;
  $objPlayer = new FV_Player_Db_Player( $player_id, array(), $FV_Player_Db );
  $posts = $objPlayer->getMetaValue('post_id'); // get posts where the player is embedded

  if(empty($posts)) return; // no posts

  foreach( $posts as $post_id ) {
    $post_id = intval($post_id);
    if( !has_post_thumbnail($post_id) ) {
      fv_wp_flowplayer_featured_image($post_id);
    }
  }
}

// Set featured image from splash arg or splash_attachment_id or splash meta attribute
function fv_wp_flowplayer_featured_image($post_id) {
  if( $parent_id = wp_is_post_revision($post_id) ) {
    $post_id = $parent_id;
  }

  // thumbnail already set
  $thumbnail_id = get_post_thumbnail_id($post_id);
  if( $thumbnail_id != 0 ) {
    return;
  }

  $post = get_post($post_id);

  // Delete old meta
  delete_post_meta($post_id, '_fv_player_featured_image_players');
  delete_post_meta($post_id, '_fv_player_featured_image_splash_urls');

  // We allow featured image to be set only once for each post
  if( get_post_meta($post_id, '_fv_player_featured_image_set', true) ) {
    return;
  }

  $thumbnail_id = false;
  $splash_attachment_id = false;
  $url = false;
  $title = '';

  // Search in post content
  $search_context = $post->post_content;

  // ...and also in post_meta
  if( $aMetas = get_post_custom($post_id) ) { // parse [fvplayer id="..."] shortcode in post meta
    foreach( $aMetas AS $aMeta ) {
      foreach( $aMeta AS $meta_value ) {
        if ( is_string( $meta_value ) && preg_match_all( '/\[fvplayer.*?\]/', $meta_value, $shortcodes ) ) {
          foreach( $shortcodes[0] AS $shortcode ) {
            $search_context .= "\n\n".$shortcode;
          }
        }
      }
    }
  }

  if( preg_match_all('/(?:splash=\\\?")([^"]*.(?:jpg|gif|png))/', $search_context, $splash_images) ) { // parse splash="..." in post content
    foreach($splash_images[1] as $src ) {
      if( !empty($src) ) {
        $url = $src;
        break;
      }
    }
  }

  if( !$url && preg_match_all('/\[fvplayer.*?id="(\d+)/', $search_context, $ids) ) { // parse [fvplayer id="..."] shortcode in post content
    global $FV_Player_Db;

    foreach( $ids[1] as $player_id ) {
      $atts = $FV_Player_Db->getPlayerAttsFromDb( array( 'id' => $player_id ) );

      if( !empty($atts['title']) ) {
        $title = $atts['title'];
      }

      if( !empty($atts['splash_attachment_id']) ) { // first check splash_attachment_id
        $splash_attachment_id = (int) $atts['splash_attachment_id'];

      } else if( !empty($atts['splash']) ) { // fallback to splash
        $url = $atts['splash'];
      }

      // If we found splash attachmend ID or URL remember that this player has set the Featured Image
      if($splash_attachment_id || $url) {
        break;
      }
    }
  }

  if( $splash_attachment_id ) {
    $thumbnail_id = $splash_attachment_id; // use saved splash
  } else if($url) {
    $args = array( // check if splash was already downloaded
      'post_type'  => 'attachment',
      'meta_query' => array(
        array(
          'key'   => '_fv_player_splash_image_url',
          'value' => $url,
        )
      )
    );

    $posts = get_posts( $args );

    if( !empty($posts[0]->ID) ) {
      $thumbnail_id = $posts[0]->ID;
    } else {
      $thumbnail_id = fv_wp_flowplayer_save_to_media_library($url, $post_id, $title); // download splash

      if($thumbnail_id) {
        update_post_meta( $thumbnail_id, '_fv_player_splash_image_url', $url );
      }
    }
  }

  if( $thumbnail_id ) { // set post thumbnail if we have thumbnail id
    update_post_meta($post_id, '_fv_player_featured_image_set', $thumbnail_id);
    set_post_thumbnail( $post_id, $thumbnail_id );
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

function fv_wp_flowplayer_save_to_media_library( $image_url, $post_id, $title = false ) {

  $image_url = apply_filters( 'fv_flowplayer_splash', $image_url );

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
    if( $title ) {
      $new_filename = sanitize_file_name($title);
    } else {
      $new_filename = fv_wp_flowplayer_construct_filename( $post_id ) . $image_extension;
    }

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

  global $wpdb; //access to the database
  $jsonReturn = '';

  function getTitleFromUrl($url) {
    $arr = explode('/', $url);
    $title = end($arr);

    if( strpos($title, ".m3u8") !== false ) {
      unset($arr[count($arr)-1]);
      $title = end($arr);
    }

    $vid_replacements = array(
      'watch?v=' => 'YouTube: '
    );
    $title = str_replace(array_keys($vid_replacements), array_values($vid_replacements), $title);

    if( is_numeric($title) && intval($title) == $title && stripos($url,'vimeo.com/') !== false ) {
      $title = "Vimeo: ".$title;
    }
    return urldecode($title);
  }

  if( check_ajax_referer( "fv-player-splashscreen-".get_current_user_id(), "security" , false ) == 1 ) {
    $title = $_POST['title'];
    $img = $_POST['img'];
    $limit = 128 - 5; // .jpeg

    $img = str_replace('data:image/jpeg;base64,', '', $img);
    $img = str_replace(' ', '+', $img);

    $title = getTitleFromUrl($title);
    $title = sanitize_title($title);

    if( function_exists('mb_strinwidth') ) {
      $title = mb_strimwidth($title, 0, $limit, '', 'UTF-8');
    } else if( strlen( $title ) > $limit ) {
      $title = substr($title, 0, $limit);
    }

    $decoded = base64_decode($img);

    $upload_dir = wp_upload_dir();
    $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

    $filename = $title .'.jpg';

    // $hashed_filename = md5( $filename . microtime() ) . '_' . $filename;

    $image_upload = file_put_contents( $upload_path . $filename, $decoded );

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
    $file['tmp_name'] = $upload_path . $filename;
    $file['name']     = $filename;
    $file['type']     = 'image/jpeg';
    $file['size']     = filesize( $upload_path . $filename );

    $file_return = wp_handle_sideload( $file, array( 'test_form' => false ) );

    if ( ! empty( $file_return['error'] ) ) {
      $jsonReturn = array(
        'src'     =>  '',
        'error'   =>  $file_return['error']
      );
    } else {
      $filename = $file_return['file'];

      $attachment = array(
        'post_mime_type' => $file_return['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content' => '',
        'post_status' => 'inherit',
        'guid' => $upload_dir['url'] . '/' . basename($filename)
      );

      $attach_id = wp_insert_attachment( $attachment, $filename, 0, true );

      if( is_wp_error( $attach_id ) ) {
        $jsonReturn = array(
          'src'     =>  '',
          'error'   =>  $attach_id->get_error_message()
        );
      } else {
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        $src = wp_get_attachment_image_url($attach_id, $size = 'full', false);

        $jsonReturn = array(
          'src'     =>  $src,
          'error'   =>  ''
        );
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




function fv_player_editor_subtitle_fields() {
  $subtitle_fields = apply_filters('fv_player_editor_subtitle_fields', array(
    'subtitles' => array(
      'items' => array(
        array(
          'label' => __('Subtitles', 'fv-wordpress-flowplayer'),
          'label_signular' => __('Subtitle', 'fv-wordpress-flowplayer'),
          'name' => 'subtitles',
          'browser' => true,
          'language' => true,
          'type' => 'text',
          'visible'     => true,
          'video_meta'  => true,
        )
      ),
      'sort' => false
    )
  ) );

  return $subtitle_fields;
}




function fv_player_editor_video_fields() {
  $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );

  $video_fields = apply_filters('fv_player_editor_video_fields', array(
    'video' => array(
      'items' => array(
        array(
          'label' => __('Video Link', 'fv-wordpress-flowplayer'),
          'name' => 'src',
          'browser' => true,
          'type' => 'text',
          'visible' => true
        ),
        array(
          'name' => 'video_info',
          'type' => 'notice_info',
          'content' => '<ul></ul>',
        ),
        array(
          'name' => 'auto_splash',
          'type' => 'hidden',
        ),
        array(
          'name' => 'auto_caption',
          'type' => 'hidden',
        ),
        array(
          'name' => 'encoding_job_id',
          'type' => 'hidden'
        ),
        array(
          'label' => __('Live Stream', 'fv-wordpress-flowplayer'),
          'name' => 'live',
          'children' => array(
            array(
              'label' => __('DVR Stream', 'fv-wordpress-flowplayer'),
              'name' => 'dvr',
              'visible' => true
            ),
          ),
        ),
        array(
          'label' => __('Audio Stream', 'fv-wordpress-flowplayer'),
          'name' => 'audio',
        ),
        array(
          'label' => __('Advanced Settings', 'fv-wordpress-flowplayer'),
          'name' => 'toggle_advanced_settings',
          'visible' => true,
          'children' => array(
            array(
              'label' => __('Mobile Video', 'fv-wordpress-flowplayer'),
              'name' => 'mobile',
              'browser' => true,
              'type' => 'text',
              'visible' => isset($fv_flowplayer_conf["interface"]["mobile"]) && $fv_flowplayer_conf["interface"]["mobile"] == 'true',
            ),
            array(
              'label' => __('Alternative Format 1', 'fv-wordpress-flowplayer'),
              'name' => 'src1',
              'browser' => true,
              'type' => 'text',
              'visible' => true
            ),
            array(
              'label' => __('Alternative Format 2', 'fv-wordpress-flowplayer'),
              'name' => 'src2',
              'browser' => true,
              'type' => 'text',
              'visible' => true
            ),
            array(
              'label' => __('RTMP', 'fv-wordpress-flowplayer'),
              'name' => 'rtmp_show',
              'no_data' => true,
              'visible' => false,
              'children' => array(
                array(
                  'label' => __('Path', 'fv-wordpress-flowplayer'),
                  'name' => 'rtmp_path',
                  'type' => 'text',
                  'visible' => true
                ),
                array(
                  'label' => __('Server', 'fv-wordpress-flowplayer'),
                  'name' => 'rtmp',
                  'type' => 'text',
                  'visible' => true
                ),
              )
            ),
          ),
        ),
        array(
          'label' => __('Splash Screen', 'fv-wordpress-flowplayer'),
          'name' => 'splash',
          'browser' => true,
          'type' => 'text',
          'visible' => true,
          'description' => __('Will appear in place of the video before it plays.', 'fv-wordpress-flowplayer'),
        ),
        array(
          'name' => 'splash_attachment_id',
          'type' => 'hidden',
        ),
        array(
          'label' => __('Title', 'fv-wordpress-flowplayer'),
          'name' => 'title',
          'type' => 'text',
          'visible' => isset($fv_flowplayer_conf["interface"]["playlist_titles"]) && $fv_flowplayer_conf["interface"]["playlist_titles"] == 'true',
          'description' => __('Will appear below the player and on playlist thumbnails. Also used for tracking.', 'fv-wordpress-flowplayer'),
        ),
        array(
          'label' => __('Splash Text', 'fv-wordpress-flowplayer'),
          'name' => 'splash_text',
          'type' => 'text',
          'visible' => isset($fv_flowplayer_conf["interface"]["splash_text"]) && $fv_flowplayer_conf["interface"]["splash_text"] == 'true',
          'description' => __('Will appear over the video before it plays.', 'fv-wordpress-flowplayer'),
        ),
        array(
          'label' => __('Synopsis', 'fv-wordpress-flowplayer'),
          'name' => 'synopsis',
          'type' => 'textarea',
          'visible' => isset($fv_flowplayer_conf["interface"]["synopsis"]) && $fv_flowplayer_conf["interface"]["synopsis"] == 'true',
          'description' => __('Shows for the Vertical Season playlist style.', 'fv-wordpress-flowplayer'),
          'video_meta'  => true,
        )
      ),
      'sort' => false
    )
  ) );

  return $video_fields;
}




add_filter( 'save_post', 'fv_wp_flowplayer_convert_to_db', 9, 3 );

/**
 * Convert shortcodes and links to DB on save
 *
 * @param int $post_id
 * @param WP_Post $post
 * @param bool $update
 *
 * @return void
 */
function fv_wp_flowplayer_convert_to_db($post_id, $post, $update) {
  global $wp, $wp_embed, $fv_fp, $FV_Player_Shortcode2Database_Conversion;

  $is_classic_editor_save = !empty($_POST['action']) && $_POST['action'] === 'editpost' && !empty($_POST['post_ID']) && $_POST['post_ID'] == $post_id;
  $is_gutenberg_post_save = !empty($wp->query_vars['rest_route']) && $wp->query_vars['rest_route'] == '/wp/v2/posts/'.$post_id;

  if( !$is_classic_editor_save && !$is_gutenberg_post_save ) {
    return;
  }

  // check if option is enabled
  if( $fv_fp->_get_option('disable_convert_db_save') ) return;

  // ignore revision
  if ( wp_is_post_revision( $post_id ) ) return;

  // ignore trash
  if ( $post->post_status === 'trash' ) return;

  $original_content = $post->post_content;
  $new_content = $post->post_content;

  // if( is_serialized($new_content) ) return; // TODO: is something serializing content?

  // convert links to embed
  $new_content = $wp_embed->autoembed( $new_content );

  // convert iframe/video tags to src shortcodes
  $new_content = fv_player_handle_video_tags($new_content);
  $new_content = fv_player_handle_youtube_links($new_content);
  $new_content = fv_player_handle_vimeo_links($new_content);

  $post->post_content = $new_content;

  $FV_Player_Shortcode2Database_Conversion->set_live = true;

  // convert src shortcodes to db
  $new_content_data = $FV_Player_Shortcode2Database_Conversion->convert_one($post);
  if( $new_content_data['content_updated'] ) {
    $new_content = $new_content_data['new_content'];
  }

  $new_content = preg_replace(
    '~<!-- wp:shortcode -->\n(\[fvplayer [\s\S]*?)\n<!-- /wp:shortcode -->~',
    '<!-- wp:fv-player-gutenberg/basic --><div class="wp-block-fv-player-gutenberg-basic">$1</div><!-- /wp:fv-player-gutenberg/basic -->',
    $new_content
  );

  if ( strcmp( $original_content, $new_content ) != 0 ) {
    add_filter(
      'rest_prepare_' . $post->post_type,
      function( $response, $post, $request ) {
        $response->data['fv_player_reload'] = true;
        return $response;
      },
      10,
      3
    );
  }

  // remove current action to prevent infinite loop when using wp_update_post
  remove_action( 'save_post', 'fv_wp_flowplayer_convert_to_db', 9 );
  wp_update_post( array( 'ID' => $post->ID, 'post_content' => $new_content ) );
}




/**
 * Elementor support
 */
add_action( 'elementor/editor/wp_head', 'fv_player_shortcode_editor_scripts_enqueue' );
add_action( 'elementor/editor/wp_head', 'fv_wp_flowplayer_edit_form_after_editor' );
add_action( 'elementor/editor/wp_head', 'flowplayer_prepare_scripts' );

// Bring back the FV Player into Elementor Elements search - it's their "Hide native WordPress widgets from search results" setting
add_filter( 'pre_option_elementor_experiment-e_hidden_wordpress_widgets', 'fv_player_editor_elementor_widget_search_enable' );

function fv_player_editor_elementor_widget_search_enable( $val ) {
  return 'inactive';
}
