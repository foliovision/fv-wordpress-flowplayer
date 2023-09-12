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

// including what's necessary for user login status, base FV Player and FV Player Pro to load:
require_once( ABSPATH . WPINC . '/capabilities.php' );
require_once( ABSPATH . WPINC . '/class-wp-roles.php' );
require_once( ABSPATH . WPINC . '/class-wp-role.php' );
require_once( ABSPATH . WPINC . '/class-wp-user.php' );
require_once( ABSPATH . WPINC . '/user.php' );
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

// and of course the WP_HTTP
require_once( ABSPATH . WPINC . '/http.php' );
if( file_exists( ABSPATH . WPINC . '/class-wp-http.php' ) ) {
  require_once( ABSPATH . WPINC . '/class-wp-http.php' );
} else {
  require_once( ABSPATH . WPINC . '/class-http.php' );
}
require_once( ABSPATH . WPINC . '/class-wp-http-streams.php' );
require_once( ABSPATH . WPINC . '/class-wp-http-curl.php' );
require_once( ABSPATH . WPINC . '/class-wp-http-proxy.php' );
require_once( ABSPATH . WPINC . '/class-wp-http-cookie.php' );
require_once( ABSPATH . WPINC . '/class-wp-http-encoding.php' );
require_once( ABSPATH . WPINC . '/class-wp-http-response.php' );
require_once( ABSPATH . WPINC . '/class-wp-http-requests-response.php' );
require_once( ABSPATH . WPINC . '/class-wp-http-requests-hooks.php' );

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

// Load FV Player
foreach ( wp_get_active_and_valid_plugins() as $plugin ) {
  if(
    stripos($plugin,'/fv-wordpress-flowplayer') !== false && stripos($plugin,'/flowplayer.php') !== false
  ) {
    wp_register_plugin_realpath( $plugin );
    include_once( $plugin );
  }
}
unset( $plugin );

if(strcmp($action, 'load_dos_assets') == 0) {
  require_once(dirname( __FILE__ ) . '/../models/media-browser.php');
  require_once(dirname( __FILE__ ). '/../models/cdn.class.php');
  require_once(dirname( __FILE__ ). '/../models/digitalocean-spaces.class.php');
  require_once(dirname( __FILE__ ). '/../models/digitalocean-spaces-browser.class.php');

  global $FV_Player_DigitalOcean_Spaces_Browser;
  $json_final = $FV_Player_DigitalOcean_Spaces_Browser->get_formatted_assets_data();

  wp_send_json($json_final);
}

die();
