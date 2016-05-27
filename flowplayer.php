<?PHP
/*
Plugin Name: FV Player
Plugin URI: http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer
Description: Formerly FV WordPress Flowplayer. Embed videos (MP4, WEBM, OGV, FLV) into posts or pages. Uses Flowplayer 6.
Version: 6.0.4.21
Author URI: http://foliovision.com/
License:     GPL-3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

/*  FV Player - HTML5 video player with Flash fallback  
	Copyright (C) 2015  Foliovision
		
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

$fv_wp_flowplayer_ver = '6.0.4.21';
$fv_wp_flowplayer_core_ver = '6.0.4';

include( dirname( __FILE__ ) . '/includes/extra-functions.php' );
if( file_exists( dirname( __FILE__ ) . '/includes/module.php' ) ) {
  include( dirname( __FILE__ ) . '/includes/module.php' );
}

include( dirname( __FILE__ ) . '/models/checker.php' );
$FV_Player_Checker = new FV_Player_Checker();

include_once(dirname( __FILE__ ) . '/models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/models/flowplayer-frontend.php');
include_once(dirname( __FILE__ ) . '/models/widget.php');
$fv_fp = new flowplayer_frontend();

if( is_admin() ) {
	include( dirname( __FILE__ ) . '/controller/backend.php' );
  
  register_deactivation_hook( __FILE__, 'flowplayer_deactivate' );

} 
	
include( dirname( __FILE__ ) . '/controller/frontend.php' );
require_once( dirname( __FILE__ ) . '/controller/shortcodes.php');

