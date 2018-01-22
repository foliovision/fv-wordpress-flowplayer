<?php

/*
 *  Admin menus and such...
 */
add_action('admin_menu', 'fv_player_admin_menu');

function fv_player_admin_menu () {
	if( function_exists('add_submenu_page') ) {
		add_options_page( 'FV Player', 'FV Player', 'manage_options', 'fvplayer', 'fv_player_admin_page' );
  }
}




function fv_player_admin_page() {
	global $fv_fp;
  if( $fv_fp->is_beta() ) {
    include dirname( __FILE__ ) . '/../view/admin-beta.php';
  } else {
    include dirname( __FILE__ ) . '/../view/admin.php';
  }
}




function fv_player_is_admin_screen() {
	if( isset($_GET['page']) && $_GET['page'] == 'fvplayer' ) {
		 return true;
	}
	return false;
}




add_filter('plugin_action_links', 'fv_wp_flowplayer_plugin_action_links', 10, 2);

function fv_wp_flowplayer_plugin_action_links($links, $file) {
  if( $file == 'fv-wordpress-flowplayer/flowplayer.php') {
    $settings_link = '<a href="https://foliovision.com/pro-support" target="_blank">Premium Support</a>';
    array_unshift($links, $settings_link);
    $settings_link = '<a href="options-general.php?page=fvplayer">Settings</a>';
    array_unshift($links, $settings_link);      
  }
  return $links;
}




add_action( 'after_plugin_row', 'fv_wp_flowplayer_after_plugin_row', 10, 3 );

function fv_wp_flowplayer_after_plugin_row( $arg) {
  if( apply_filters('fv_player_skip_ads',false) ) {
    return;
  }
  
	$args = func_get_args();
	
	if( $args[1]['Name'] == 'FV Wordpress Flowplayer' ) {		
    $options = get_option( 'fvwpflowplayer' );
    if( $options['key'] == 'false' || $options['key'] == '' ) :
		?>
<tr class="plugin-update-tr fv-wordpress-flowplayer-tr">
	<td class="plugin-update colspanchange" colspan="3">
		<div class="update-message">
			<a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/download">All Licenses 20% Off</a> - Easter sale!
		</div>
	</td>
</tr>
		<?php
		endif;
	}
}




add_filter( 'get_user_option_closedpostboxes_fv_flowplayer_settings', 'fv_wp_flowplayer_closed_meta_boxes' );

function fv_wp_flowplayer_closed_meta_boxes( $closed ) {
    if ( false === $closed )
        $closed = array( 'fv_flowplayer_amazon_options', 'fv_flowplayer_interface_options', 'fv_flowplayer_default_options', 'fv_flowplayer_ads', 'fv_flowplayer_integrations', 'fv_player_pro' );

    return $closed;
}




/*
 *  Saving settings
 */
add_action('admin_init', 'fv_player_settings_save', 9);

function fv_player_settings_save() {
  //  Trick media uploader to show video only, while making sure we use our custom type; Also save options
	if( isset($_GET['type']) ) {
		if( $_GET['type'] == 'fvplayer_video' || $_GET['type'] == 'fvplayer_video_1' || $_GET['type'] == 'fvplayer_video_2' || $_GET['type'] == 'fvplayer_mobile' ) {
			$_GET['post_mime_type'] = 'video';
		}
		else if( $_GET['type'] == 'fvplayer_splash' || $_GET['type'] == 'fvplayer_logo' ) {
			$_GET['post_mime_type'] = 'image';
		}
  }
  
  if( isset($_POST['fv-wp-flowplayer-submit']) ) {
    check_admin_referer('fv_flowplayer_settings_nonce','fv_flowplayer_settings_nonce');
    
  	global $fv_fp;
  	if( method_exists($fv_fp,'_set_conf') ) {
			$fv_fp->_set_conf();    
		} else {
			echo 'Error saving FV Flowplayer options.';
		}
	}  
}




/*
 *  Pointer boxes
 */
add_action('admin_init', 'fv_player_admin_pointer_boxes');

function fv_player_admin_pointer_boxes() {
  global $fv_fp;
  global $fv_wp_flowplayer_ver, $fv_wp_flowplayer_core_ver;

	if(
		isset($fv_fp->conf['disable_videochecker']) && $fv_fp->conf['disable_videochecker'] == 'false' &&
    ( !isset($fv_fp->conf['video_checker_agreement']) || $fv_fp->conf['video_checker_agreement'] != 'true' )
	) {
		$fv_fp->pointer_boxes['fv_flowplayer_video_checker_service'] = array(
      'id' => '#wp-admin-bar-new-content',
      'pointerClass' => 'fv_flowplayer_video_checker_service',
      'heading' => __('FV Player Video Checker', 'fv-wordpress-flowplayer'),
      'content' => __("<p>FV Player includes a free video checker which will check your videos for any encoding errors and helps ensure smooth playback of all your videos. To work its magic, our video checker must contact our server.</p><p>Would you like to enable the video encoding checker?</p>", 'fv-wordpress-flowplayer'),
      'position' => array( 'edge' => 'top', 'align' => 'center' ),
      'button1' => __('Allow', 'fv-wordpress-flowplayer'),
      'button2' => __('Disable the video checker', 'fv-wordpress-flowplayer')
    );
	}
  
  if( 
    (stripos( $_SERVER['REQUEST_URI'], '/plugins.php') !== false ||fv_player_is_admin_screen() ) 
    && $pnotices = get_option('fv_wordpress_flowplayer_persistent_notices') 
  ) {  
    $fv_fp->pointer_boxes['fv_flowplayer_license_expired'] = array(
      'id' => '#wp-admin-bar-new-content',
      'pointerClass' => 'fv_flowplayer_license_expired',
      'pointerWidth' => 340,
      'heading' => __('FV Flowplayer License Expired', 'fv-wordpress-flowplayer'),
      'content' => __( $pnotices ),
      'position' => array( 'edge' => 'top', 'align' => 'center' ),
      'button1' => __('Hide this notice', 'fv-wordpress-flowplayer'),
      'button2' => __('I\'ll check this later', 'fv-wordpress-flowplayer')
    );    
  }
  
  if( !$fv_fp->_get_option('disable_video_hash_links') && !$fv_fp->_get_option('notification_video_links') ) {    
		$fv_fp->pointer_boxes['fv_player_notification_video_links'] = array(
      'id' => '#wp-admin-bar-new-content',
      'pointerClass' => 'fv_player_notification_video_links',
      'heading' => __('FV Player Video Links', 'fv-wordpress-flowplayer'),
      'content' => $fv_fp->_get_option('disableembedding') ? __("<p>Now you can enable Video Links to allow people to share exact location in your videos. Clicking that link gives them a link to play that video at the exact time.</p>", 'fv-wordpress-flowplayer') : __("<p>Each video player now contains a link in the top bar. Clicking that link gives your visitors a link to play that video at the exact time where they are watching it.</p>", 'fv-wordpress-flowplayer'),
      'position' => array( 'edge' => 'top', 'align' => 'center' ),
      'button1' => __('Open Settings', 'fv-wordpress-flowplayer'),
      'button2' => __('Dismiss', 'fv-wordpress-flowplayer')
    );
    
    add_action( 'admin_print_footer_scripts', 'fv_player_pointer_scripts' );
	}  
}




add_action( 'wp_ajax_fv_foliopress_ajax_pointers', 'fv_wp_flowplayer_pointers_ajax' );

function fv_wp_flowplayer_pointers_ajax() {  
	if( isset($_POST['key']) && $_POST['key'] == 'fv_flowplayer_video_checker_service' && isset($_POST['value']) ) {
		check_ajax_referer('fv_flowplayer_video_checker_service');
		$conf = get_option( 'fvwpflowplayer' );
		if( $conf ) {
			if( $_POST['value'] == 'true' ) {
				$conf['disable_videochecker'] = 'false';
        $conf['video_checker_agreement'] = 'true';
			} else {
				$conf['disable_videochecker'] = 'true';
			}
			update_option( 'fvwpflowplayer', $conf );
		}
		die();
	}
  
  if( isset($_POST['key']) && $_POST['key'] == 'fv_player_notification_video_links' && isset($_POST['value']) ) {
		check_ajax_referer('fv_player_notification_video_links');
		$conf = get_option( 'fvwpflowplayer' );
		if( $conf ) {
			$conf['notification_video_links'] = 'true';
			update_option( 'fvwpflowplayer', $conf );
		}
		die();
	}  
	
  if( isset($_POST['key']) && $_POST['key'] == 'fv_flowplayer_license_expired' && isset($_POST['value']) && $_POST['value'] === 'true' ) {
    check_ajax_referer('fv_flowplayer_license_expired');
    delete_option("fv_wordpress_flowplayer_persistent_notices");
    die();
  }
	
}




function fv_player_pointer_scripts() {
  ?>
  <script>
    (function ($) {
      $(document).on('click', '.fv_player_notification_video_links .button-primary', function(e) {
        $(document).ajaxComplete( function() {
          window.location = '<?php echo site_url('wp-admin/options-general.php?page=fvplayer'); ?>#playlist_advance';
        });
      });
    })(jQuery);        
  </script>
  <?php
}




/*
 *  Making sure FV Player appears properly on settings screen
 */
add_action('admin_enqueue_scripts', 'fv_flowplayer_admin_scripts');

function fv_flowplayer_admin_scripts() {
  global $fv_wp_flowplayer_ver;
  if( fv_player_is_admin_screen() ) {
    wp_enqueue_media();
    
  	wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
    
    wp_register_script('fv-player-admin', flowplayer::get_plugin_url().'/js/admin.js',array('jquery'), $fv_wp_flowplayer_ver );
    wp_enqueue_script('fv-player-admin');    
  }
}




add_action('admin_head', 'flowplayer_admin_head');

function flowplayer_admin_head() {  
  if( !fv_player_is_admin_screen() ) return; 

  global $fv_wp_flowplayer_ver;
  ?>      
    <script type="text/javascript" src="<?php echo FV_FP_RELATIVE_PATH; ?>/js/jscolor/jscolor.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo flowplayer::get_plugin_url().'/css/license.css'; ?>?ver=<?php echo $fv_wp_flowplayer_ver; ?>" />
    
    <script>
    jQuery(window).on('unload', function(){
      window.fv_flowplayer_wp = window.wp;
    });
    </script>
  <?php
}




add_action('admin_footer', 'flowplayer_admin_footer');

function flowplayer_admin_footer() {
  if( !fv_player_is_admin_screen() ) return;
  
  flowplayer_prepare_scripts();
}




add_action('admin_print_footer_scripts', 'flowplayer_admin_footer_wp_js_restore', 999999 );

function flowplayer_admin_footer_wp_js_restore() {
  if( !fv_player_is_admin_screen() ) return; 
  
  ?>
  <script>
  jQuery(window).on('unload', function(){
    window.wp = window.fv_flowplayer_wp;
  });
  </script>
  <?php
}
