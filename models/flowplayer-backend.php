<?php
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