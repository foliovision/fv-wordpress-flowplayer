<?php

define( 'DOING_AJAX', true );

// With this the wp-load.php takes 20 ms, without it about 900 ms. Of course it largely depends on what/how many plugins you use.
if( !defined('SHORTINIT') ) {
  define('SHORTINIT',true);
}

if( file_exists('../../../../wp-load.php') ) {
  require('../../../../wp-load.php');
} else {
  http_response_code( 500 );
  die( "Error: wp-load.php not found!" );
}


/**
 * Including what's necessary for user login status, base FV Player and FV Player Pro to load:
 */
require_once( ABSPATH . WPINC . '/capabilities.php' );
require_once( ABSPATH . WPINC . '/class-wp-roles.php' );
require_once( ABSPATH . WPINC . '/class-wp-role.php' );
require_once( ABSPATH . WPINC . '/class-wp-user.php' );
require_once( ABSPATH . WPINC . '/user.php' );

// Translation and localization.
require_once( ABSPATH . WPINC . '/pomo/mo.php' );
require_once( ABSPATH . WPINC . '/l10n.php' );

if ( file_exists( ABSPATH . WPINC . '/class-wp-textdomain-registry.php' ) ) {
  require_once( ABSPATH . WPINC . '/class-wp-textdomain-registry.php' );
}

require_once( ABSPATH . WPINC . '/class-wp-locale.php' );
require_once( ABSPATH . WPINC . '/class-wp-locale-switcher.php' );

if ( class_exists( 'WP_Textdomain_Registry' ) ) {
  global $wp_textdomain_registry;
  if ( ! $wp_textdomain_registry instanceof WP_Textdomain_Registry ) {
    $wp_textdomain_registry = new WP_Textdomain_Registry();
  }
}

require_once( ABSPATH . WPINC . '/pluggable.php' );
require_once( ABSPATH . WPINC . '/functions.php' );
require_once( ABSPATH . WPINC . '/formatting.php' );
require_once( ABSPATH . WPINC . '/link-template.php' );
require_once( ABSPATH . WPINC . '/shortcodes.php' );
require_once( ABSPATH . WPINC . '/general-template.php' );
require_once( ABSPATH . WPINC . '/class-wp-session-tokens.php' );
require_once( ABSPATH . WPINC . '/class-wp-user-meta-session-tokens.php' );
require_once( ABSPATH . WPINC . '/meta.php' );
require_once( ABSPATH . WPINC . '/kses.php' );
require_once( ABSPATH . WPINC . '/rest-api.php' );
require_once( ABSPATH . WPINC . '/blocks.php' );

// wp_parse_url()
require_once( ABSPATH . WPINC . '/http.php' );

if(!empty($_POST['action'])) {
  $action = sanitize_text_field($_POST['action']);
} else {
  http_response_code( 400 );
  die( "Error: action not set!" );
}

// Without this plugins_url() won't work
wp_plugin_directory_constants();
$GLOBALS['wp_plugin_paths'] = array();

// Without this the user login status won't work
wp_cookie_constants();

// This function is hard to make work and FV Player might need it in constructor
if( !function_exists('__') ) {
  function __() {
    return false;
  }
}

$plugins = wp_get_active_and_valid_plugins();
if ( function_exists( 'wp_get_active_network_plugins' ) ) {
  $plugins = array_merge( $plugins, wp_get_active_network_plugins() );
}

$plugins = array_unique( $plugins );

// Load FV Player and all related plugins
foreach ( $plugins as $plugin ) {
  if ( stripos($plugin,'/fv-player') !== false ) {
    wp_register_plugin_realpath( $plugin );
    include_once( $plugin );
  }
}
unset( $plugin );

global $fv_fp;
if ( empty( $fv_fp ) ) {
  wp_send_json( array( 'err' => 'Error: Unable to load FV Player.' ) );
  die();
}


if(strcmp($action, 'load_dos_assets') == 0) { // DigitalOcean Spaces
  require_once(dirname( __FILE__ ) . '/../models/media-browser.php');
  require_once(dirname( __FILE__ ). '/../models/cdn.class.php');
  require_once(dirname( __FILE__ ). '/../models/digitalocean-spaces.class.php');
  require_once(dirname( __FILE__ ). '/../models/digitalocean-spaces-browser.class.php');

  global $FV_Player_DigitalOcean_Spaces_Browser;
  $json_final = $FV_Player_DigitalOcean_Spaces_Browser->get_formatted_assets_data();

  wp_send_json($json_final);
} else if(strcmp($action,'load_s3_assets') == 0) { // Amazon S3
  require_once(dirname( __FILE__ ) . '/settings.php');
  require_once(dirname( __FILE__ ) . '/../models/media-browser.php');
  require_once(dirname( __FILE__ ) . '/../models/media-browser-s3.php');

  global $FV_Player_Media_Browser_S3;
  $json_final = $FV_Player_Media_Browser_S3->get_formatted_assets_data();

  wp_send_json($json_final);
} else if (strcmp($action,'load_linode_object_storage_assets') == 0) { // Linode Object Storage
  require_once(dirname( __FILE__ ) . '/../models/media-browser.php');
  require_once(dirname( __FILE__ ). '/../models/cdn.class.php');
  require_once(dirname( __FILE__ ). '/../models/linode-object-storage.class.php');
  require_once(dirname( __FILE__ ). '/../models/linode-object-storage-browser.class.php');

  global $FV_Player_Linode_Object_Storage_Browser;
  $json_final = $FV_Player_Linode_Object_Storage_Browser->get_formatted_assets_data();

  wp_send_json($json_final);
} else if( strpos($action, 'multiupload') !== false || strcmp($action, 'validate_file_upload') == 0 ) { // S3 Multiupload
  require_once(dirname( __FILE__ ) . '/../models/media-browser.php');
  require_once(dirname( __FILE__ ). '/../models/cdn.class.php');
  require_once(dirname( __FILE__ ). '/../models/digitalocean-spaces.class.php');
  require_once(dirname( __FILE__ ). '/../models/digitalocean-spaces-browser.class.php');
  require_once(dirname( __FILE__ ) . '/s3-upload.php');

  do_action('fv_player_shortinit_loaded');

  global $FV_Player_S3_Upload;

  if(strcmp($action,'validate_file_upload') == 0 ) {
    $FV_Player_S3_Upload->validate_file_upload();

  } else if(strcmp($action,'create_multiupload') == 0 ) {
    $FV_Player_S3_Upload->create_multiupload();
  } else if(strcmp($action, 'multiupload_send_part') == 0) {
    $FV_Player_S3_Upload->multiupload_send_part();
  } else if(strcmp($action, 'multiupload_abort') == 0) {
    $FV_Player_S3_Upload->multiupload_abort();
  } else if(strcmp($action, 'multiupload_complete') == 0) {
    $FV_Player_S3_Upload->multiupload_complete();
  }
}

die();
