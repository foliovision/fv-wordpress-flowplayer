<?php
class FV_Player_Bitchute {

  public function __construct() {
    $this->cache = false;
    add_filter( 'fv_flowplayer_video_src', array( $this, 'get_bitchute_src') );
    add_filter( 'fv_flowplayer_splash', array( $this, 'get_bitchute_splash'), 10, 2 );
    add_filter( 'fv_flowplayer_playlist_splash', array( $this, 'get_bitchute_splash'), 10, 2 );
    add_filter( 'fv_flowplayer_get_mime_type', array( $this, 'set_file_type'), 10, 2 );
  }

  public function get_bitchute_splash( $splash, $src = false ) {
    if( !$splash && is_string($src) ) {
      $idVideo = $this->get_butchute_video_id($src);
      
      if($idVideo) {
        $output = $this->get_bitchute_cache( $src, $idVideo );
        if( is_array( $output ) && !empty( $output['splash'] ) ) {
          return $output['splash'];
        }
      }
    }

    return $splash;
  }

  public function get_bitchute_src( $media ) {
    $idVideo = $this->get_butchute_video_id($media);
      
    if($idVideo) {
      $output = $this->get_bitchute_cache( $media, $idVideo );
      if( is_array( $output ) && !empty( $output['src'] ) ) {
        return $output['src'];
      } 
    }

    return $media;
  }

  function get_bitchute_cache( $media, $idVideo ) {
    $option = 'fv_bitchute_cache_' . $idVideo;
    $cache = get_option( $option, array() );

    if( !empty( $cache['time'] ) && !empty($cache['src'])  &&  !empty($cache['splash'])  && $cache['time'] + 86400 > time() ) {
      return $cache;
    } else if ( !empty( $cache['time'] ) && $cache['time'] + 360 > time() ) {
      return $cache;
    } else {
      $output_media = $this->update_bitchute_cache( $media, $option );
      return $output_media;
    }
  } 
  

  function update_bitchute_cache( $media, $option ) {
    $response = wp_remote_get( $media );
    $value = array(
      'src' => false,
      'splash' => false,
      'time' => time() 
    );

    if ( is_array( $response ) && !is_wp_error( $response ) ) {
      $headers = $response['headers'];
      $body = $response['body'];

      if( preg_match('/<video .*?>([\s\S]*?)<\/video>/', $body, $video) ) {
        preg_match('/src\s*=\s*"(.*?)"/', $video[1], $src); // video src
        preg_match('/poster\s*=\s*"(.*?)"/', $video[0], $splash); // video splash
        $output_media = $src[1];

        $value = array(
          'src' => $src[1],
          'splash' => $splash[1],
          'time' => time() 
        );
      }
    }

    update_option( $option , $value, false);

    return $value;
  }

  function get_butchute_video_id( $link ) {
    if( preg_match('/https:\/\/www\.bitchute\.com\/video\/(.*?)\//', $link, $idVideo) ) { 
      return $idVideo[1];
    } else {
      return false;
    }
  }

  function set_file_type( $type ) {
    $args = func_get_args();
    if( strpos( $args[1], 'bitchute.com' ) !== false ) {
      $type = "video/mp4";
    }
    return $type;
  }

}
$FV_Player_Bitchute = new FV_Player_Bitchute();