<?php 

if( !class_exists('FV_Player_Splash_Download') ) :

class FV_Player_Splash_Download {
  function __construct() {
    add_filter('fv_player_meta_data', array( $this, 'splash_data' ), 20, 2);
  }

  function splash_data($video, $post_id = false) {
    if( is_array($video) && !empty($video['thumbnail']) ) {
      $splash_data = $this->download_splash( $video['thumbnail'], isset($video['name']) ? $video['name'] : false );
    
      if( !empty( $splash_data ) ) {
        $video['thumbnail'] = $splash_data['url'];
        $video['splash_attachment_id'] = $splash_data['attachment_id'];
      }
    }

    return $video;
  }

  private function download_splash( $splash_url, $title ) {
    $limit = 128 - 5; // .jpeg

    if( empty($title) ) {
      $arr = explode('/', $splash_url);
      $title = end($arr);

      if( preg_match( '/\.(png|jpg|jpeg|gif|webp)/', $title, $matches ) ) {
        $title = pathinfo($title, PATHINFO_FILENAME); // remove file extension
      }
    }

    $title = sanitize_title($title);

    if( function_exists('mb_strinwidth') ) {
      $title = mb_strimwidth($title, 0, $limit, '', 'UTF-8');
    } else if( strlen( $title ) > $limit ) {
      $title = substr($title, 0, $limit);
    }

    $upload_dir = wp_upload_dir();
    $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

    // if the function its not available, require it
    if ( ! function_exists( 'download_url' ) ) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    $file_name = $title . '.jpg';
    $file_path = download_url( $splash_url );

    if ( is_wp_error( $file_path ) ) {
      return false;
    }

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
      @unlink( $file['tmp_name']);
      return false;
    }

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
      return false;
    } else {

      require_once(ABSPATH . 'wp-admin/includes/image.php');

      update_post_meta( $attach_id, 'fv_player_original_splash_url', $splash_url ); // store original splash url in attachment meta

      $attach_data = wp_generate_attachment_metadata( $attach_id, $file_name );
      wp_update_attachment_metadata( $attach_id, $attach_data );

      $img_url = wp_get_attachment_image_url($attach_id, 'full', false);

      return array( 'url' => $img_url, 'attachment_id' => $attach_id ) ;
    }

  }

}

global $FV_Player_Splash_Download;
$FV_Player_Splash_Download = new FV_Player_Splash_Download;

endif;