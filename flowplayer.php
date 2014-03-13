<?PHP
/*
Plugin Name: FV Wordpress Flowplayer
Plugin URI: http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer
Description: Embed videos (MP4, WEBM, OGV, FLV) into posts or pages. Uses Flowplayer 5. 
Version: 2.2.2
Author: Foliovision
Author URI: http://foliovision.com/
License:     GPL-3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback  
	Copyright (C) 2013  Foliovision
		
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 

include( dirname( __FILE__ ) . '/includes/extra-functions.php' ); 

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


$fv_wp_flowplayer_ver = '2.2.2';
$fv_wp_flowplayer_core_ver = '5.4.6';
