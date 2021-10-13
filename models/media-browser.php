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
    add_action( 'admin_print_scripts-fv-player_page_fv_player_coconut', array($this, 'init_base'), 0 ); // wp-admin -> FV Player -> Encode video

    // register extending class WP AJAX action
    $this->ajax_action_name = $ajax_action_name;
    $this->register();
  }

  abstract function init();

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

  function include_s3_upload_assets() {
    if ( $this->s3_assets_loaded ) {
      return;
    }

    global $fv_wp_flowplayer_ver;

    wp_enqueue_script( 'fv-player-s3-uploader', flowplayer::get_plugin_url().'/js/s3upload.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );
    wp_enqueue_style( 'fv-player-s3-uploader-css', flowplayer::get_plugin_url() . '/css/s3-uploader.css', array(), filemtime( dirname(__FILE__).'/../css/s3-uploader.css' ) );
    wp_enqueue_script( 'fv-player-s3-uploader-base', flowplayer::get_plugin_url().'/js/s3-upload-base.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );

    $this->s3_assets_loaded = true;
  }

  function get_formatted_assets_data() {
    return json_decode('{"items":{"name":"Home","type":"folder","path":"Home\/","items":[{"name":"01 The Beginning.mp3","size":2117536,"type":"file","path":"Home\/01 The Beginning.mp3","link":"http:\/\/sjdua7x04ygyx.cloudfront.net\/01%20The%20Beginning.mp3"},{"name":"Fender_Bass_Guitar_Patent.jpg","size":495756,"type":"file","path":"Home\/Fender_Bass_Guitar_Patent.jpg","link":"http:\/\/sjdua7x04ygyx.cloudfront.net\/Fender_Bass_Guitar_Patent.jpg"}]}}', true);
  }

  function load_assets() {
    $json_final = $this->get_formatted_assets_data();

    wp_send_json( $json_final );
    wp_die();
  }

}