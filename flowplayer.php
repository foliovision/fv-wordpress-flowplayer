<?PHP
/*
Plugin Name: FV Wordpress Flowplayer
Plugin URI: http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer
Description: Embed videos (MP4, WEBM, OGV, FLV) into posts or pages. Uses Flowplayer 5. 
Version: 2.1.34
Author: Foliovision
Author URI: http://foliovision.com/
*/

if( is_admin() ) {
	/**
	 * If administrator is logged, loads the controller for backend.
	 */

	include( dirname( __FILE__ ) . '/controller/backend.php' );
  
  register_activation_hook( __FILE__, 'flowplayer_activate' );

} else {
	/**
	 * If administrator is not logged, loads the controller for frontend.
	 */
	include( dirname( __FILE__ ) . '/controller/frontend.php' );
  require_once( dirname( __FILE__ ) . '/controller/shortcodes.php');
}

$fv_wp_flowplayer_ver = '2.1.34';
$fv_wp_flowplayer_core_ver = '5.4.3';
?>
