<?php

abstract class FV_Player_Media_Browser {

  public $ajax_action_name = 'wp_ajax_load_assets';
  private $s3_assets_loaded = false;

  public function __construct($ajax_action_name) {

    // load base JS
    add_action( 'edit_form_after_editor', array($this, 'init_base'), 1 ); // for old WP editor
    add_action( 'enqueue_block_editor_assets', array($this, 'init_base') ); // for Gutenberg
    add_action( 'admin_print_scripts-toplevel_page_fv_player', array($this, 'init_base'), 0 ); // wp-admin -> FV Player
    add_action( 'admin_print_scripts-widgets.php', array($this, 'init_base'), 0 ); // wp-admin -> Widgets

    // TODO: Video encoder class should take care of this
    add_action( 'admin_print_scripts-fv-player_page_fv_player_coconut', array($this, 'init_base'), 0 ); // wp-admin -> FV Player -> Coconut Jobs
    add_action( 'admin_print_scripts-fv-player_page_fv_player_bunny_stream', array($this, 'init_base'), 0 ); // wp-admin -> FV Player -> Bunny Stream Jobs
    add_action( 'fv_player_media_browser_enqueue_base_uploader_css', array( $this, 'include_base_uploader_css' ) );

    // register extending class WP AJAX action
    $this->ajax_action_name = $ajax_action_name;
    $this->register();
  }

  abstract function init();

  // TOTO: should be abstract
  function decode_link_components( $link ) {}

  // TOTO: should be abstract
  function get_custom_domain_url( $link, $bucket, $custom_domain ) {}

  function init_base() {
    global $fv_wp_flowplayer_ver;
    wp_enqueue_media();
    wp_enqueue_script( 'flowplayer-browser-base', flowplayer::get_plugin_url().'/js/media-library-browser-base.js', array('jquery'), $fv_wp_flowplayer_ver, true );
    wp_enqueue_style('fvwpflowplayer-s3-browser', flowplayer::get_plugin_url().'/css/s3-browser.css','',$fv_wp_flowplayer_ver);
    $this->init();
  }

  function init_for_gutenberg_base() {
    add_action( 'admin_footer', array($this, 'init_base'), 1 );
  }

  function register() {
    add_action( $this->ajax_action_name, array($this, 'load_assets') );
  }

  function include_aws_sdk() {
    if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
      require_once( dirname( __FILE__ ) . "/../vendor/autoload.php" );
    }
  }

  function include_base_uploader_css() {
    wp_enqueue_style( 'fv-player-s3-uploader-css', flowplayer::get_plugin_url() . '/css/s3-uploader.css', array(), filemtime( dirname(__FILE__).'/../css/s3-uploader.css' ) );
  }

  function include_s3_upload_assets() {
    if ( $this->s3_assets_loaded ) {
      return;
    }

    global $fv_wp_flowplayer_ver;

    wp_enqueue_script( 'fv-player-s3-uploader', flowplayer::get_plugin_url().'/js/s3upload.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );
    wp_enqueue_script( 'fv-player-s3-uploader-base', flowplayer::get_plugin_url().'/js/s3-upload-base.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );
    $this->include_base_uploader_css();

    $this->s3_assets_loaded = true;
  }

  function get_formatted_assets_data() {
    return json_decode('{"items":{"name":"Home","type":"folder","path":"Home\/","items":[{"name":"01 The Beginning.mp3","size":2117536,"type":"file","path":"Home\/01 The Beginning.mp3","link":"http:\/\/sjdua7x04ygyx.cloudfront.net\/01%20The%20Beginning.mp3"},{"name":"Fender_Bass_Guitar_Patent.jpg","size":495756,"type":"file","path":"Home\/Fender_Bass_Guitar_Patent.jpg","link":"http:\/\/sjdua7x04ygyx.cloudfront.net\/Fender_Bass_Guitar_Patent.jpg"}]}}', true);
  }

  function get_output() {
    $output = array(
      'name' => 'Home',
      'type' => 'folder',
      'path' => !empty($_POST['path']) ? $_POST['path'] : 'Home/',
      'items' => array()
    );

    return $output;
  }

  function get_metadata( $s3Client, $bucket ) {
    $args = array(
      'Bucket' => $bucket,
      'Delimiter' => '/',
    );

    $request_path = !empty($_POST['path']) ? str_replace( 'Home/', '', stripslashes($_POST['path']) ) : false;

    if( $request_path ) {
      $args['Prefix'] = $request_path;
    }

    $paged = $s3Client->getPaginator('ListObjects',$args);

    $date_format = get_option( 'date_format' );

    return array( $request_path, $paged, $date_format );
  }

  function get_output_items( $output, $s3Client, $request_path, $paged, $date_format, $bucket, $sum_up = NULL, $custom_domain = NULL ) {
    foreach( $paged AS $res ) {

      $folders = !empty($res['CommonPrefixes']) ? $res['CommonPrefixes'] : array();
      $files = $res->get('Contents');
      if( !$files ) $files = array();
      
      $objects = array_merge( $folders, $files );

      foreach ( $objects as $object ) {
        if ( ! isset( $objectarray ) ) {
          $objectarray = array();
        }

        $item = array();

        $path = $object['Prefix'] ? $object['Prefix'] : $object['Key'];

        if( isset($sum_up) && !empty($object['Key']) && preg_match( '~\.ts$~', $object['Key'] ) ) {
          if( empty($sum_up['ts']) ) $sum_up['ts'] = 0;
          $sum_up['ts']++;
          continue;
        }

        $item['path'] = 'Home/' . $path;
        
        if( $request_path ) {
          if( $request_path == $path ) continue; // sometimes the current folder is present in the response, weird

          $item['name'] = str_replace( $request_path, '', $path );
        } else {
          $item['name'] = $path;
        }

        if( !empty($object['Size']) ) {
          $item['type'] = 'file';
          $item['size'] = $object['Size'];
          $item['modified'] = date($date_format, strtotime($object['LastModified']));

          if( isset($object['LastModified']) ) {
            $item['LastModified'] = $object['LastModified'];
          }

          $link = (string) $s3Client->getObjectUrl( $bucket, $path );

          $link = $this->decode_link_components( $link );

          // replace link with CloudFront URL, if we have one
          if( !empty($custom_domain) ) {
            $link = $this->get_custom_domain_url($link ,$bucket, $custom_domain);
          }

          $item['link'] = $link;

          if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $item['name'])) {
            $item['splash'] = apply_filters('fv_flowplayer_splash', $link );
          }
        } else {
          $item['type'] = 'folder';
          $item['items'] = array();
        }

        $output['items'][] = $item;

        if (strtolower(substr($item['name'], strrpos($item['name'], '.') + 1)) === 'ts') {
          continue;
        }

      }
    }

    return array( $output, $sum_up );
  }

  function load_assets() {
    $json_final = $this->get_formatted_assets_data();

    wp_send_json( $json_final );
    wp_die();
  }

}