<?php
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

/** 
 * Extension of original flowplayer class intended for administrator backend.
 */
class flowplayer_backend extends flowplayer
{
	/**
	 * Displays elements that need to be added into head in administrator backend.
	 */
	function flowplayer_head() {
	  /**
	 	 * Standard JS and CSS same as for frontend
	 	 */
	 	include dirname( __FILE__ ) . '/../view/frontend-head.php';
	 	/**
	 	 * Admin specific CSS and JS
	 	 */
	 	if( isset($_GET['page']) && $_GET['page'] == 'fvplayer' ) {
	 		include dirname( __FILE__ ) . '/../view/backend-head.php'; 
	 	}
	}
}
?>