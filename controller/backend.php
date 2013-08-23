<?php 

include_once(dirname( __FILE__ ) . '/../models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/../models/flowplayer-backend.php');

/**
 * Create the flowplayer_backend object
 */
$fv_fp = new flowplayer_backend();

/**
 * WP Hooks
 */
add_action('wp_ajax_fv_wp_flowplayer_support_mail', 'fv_wp_flowplayer_support_mail');  
add_action('wp_ajax_fv_wp_flowplayer_check_mimetype', 'fv_wp_flowplayer_check_mimetype'); 
add_action('wp_ajax_fv_wp_flowplayer_check_template', 'fv_wp_flowplayer_check_template'); 
add_action('wp_ajax_fv_wp_flowplayer_check_files', 'fv_wp_flowplayer_check_files'); 
 
add_action('admin_head', 'flowplayer_head');
add_action('admin_menu', 'flowplayer_admin');
add_action('media_buttons', 'flowplayer_add_media_button', 30);


add_action('admin_init', 'fv_wp_flowplayer_admin_init');
add_action('media_upload_fvplayer_video', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_video_1', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_video_2', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_mobile', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_splash', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_logo', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_subtitles', 'fv_wp_flowplayer_media_upload');


add_action( 'admin_enqueue_scripts', 'fv_wp_flowplayer_admin_enqueue_scripts' );
add_action( 'edit_form_after_editor', 'fv_wp_flowplayer_edit_form_after_editor' );

add_action( 'after_plugin_row', 'fv_wp_flowplayer_after_plugin_row', 10, 3 );

add_action( 'save_post', 'fv_wp_flowplayer_save_post', 9999 );

add_filter( 'get_user_option_closedpostboxes_fv_flowplayer_settings', 'fv_wp_flowplayer_closed_meta_boxes' );


//loading a video and splash image
if(
  isset($_REQUEST['_wp_http_referer']) && 
  (
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video') || 
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video_1') || 
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video_2') || 
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_mobile') ||     
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_splash') ||
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_subtitles')
  )
) {
  add_filter('media_send_to_editor','fv_wp_flowplayer_media_send_to_editor', 10, 3);
  //disable inserting the image to the editor
  add_filter('image_send_to_editor', 'fv_wp_flowplayer_image_send_to_editor', 10);
}
else {
  //loading a logo
  if(isset($_POST['_wp_http_referer']) && (strpos($_POST['_wp_http_referer'],'type=fvplayer_logo'))) {
    add_filter('media_send_to_editor','fp_media_send_to_settings', 10, 3);
  }
}

if(
  !empty($_GET['post_id']) &&
  (
    $_GET['type'] == 'fvplayer_video' || 
    $_GET['type'] == 'fvplayer_video_1' || 
    $_GET['type'] == 'fvplayer_video_2' || 
    $_GET['type'] == 'fvplayer_mobile' ||     
    $_GET['type'] == 'fvplayer_splash' || 
    $_GET['type'] == 'fvplayer_subtitles'
  )
) {  
  add_action( 'post-html-upload-ui', 'fv_wp_flowplayer_image_media_upload_html_bypass', 100 );
}

add_action('the_content', 'flowplayer_content_remove_commas');
add_filter('admin_print_scripts', 'flowplayer_print_scripts');
add_action('admin_print_styles', 'flowplayer_print_styles');
//conversion script via AJAX
add_action('wp_ajax_flowplayer_conversion_script', 'flowplayer_conversion_script');
add_action('admin_notices', 'fv_wp_flowplayer_admin_notice');


function flowplayer_activate() {
	update_option( 'fv_wordpress_flowplayer_deferred_notices', 'FV Flowplayer upgraded - please click "Check template" and "Check videos" for automated check of your site at <a href="'.site_url().'/wp-admin/options-general.php?page=fvplayer">the settings page</a> for automated checks!' );  
}


function flowplayer_content_remove_commas($content) {
  preg_match('/.*popup=\'(.*?)\'.*/', $content, $matches);
  $content_new = preg_replace('/\,/', '',$content);
  if (isset($matches[1]))
    $content_new = preg_replace('/popup=\'(.*?)\'/', 'popup=\''.$matches[1].'\'',$content_new);
  return $content_new;
}


/**
 * END WP Hooks
 */
 
 
function fv_wp_flowplayer_media_send_to_editor($html, $attachment_id, $attachment) {    
      
  $video_types = array('flv','mov','avi','mpeg','mpg','asf','qt','wmv','mp4','m4v','mp3','webm','ogv');    
  $splash_types = array('jpg','jpeg','gif','png', 'bmp','jpe');
  $subtitles_types = array('txt','vtt');
  
  if (isset($attachment_id)) {
    $attachment_url = wp_get_attachment_url($attachment_id);
    $path_parts = pathinfo($attachment_url);
    if (strpos($_POST['_wp_http_referer'],'type=fvplayer_splash')) {
      setcookie("selected_image",$attachment_url);
      $selected_attachment = array('url'=>$attachment_url,'id'=>$attachment_id);
    }    
    else
    if (strpos($_POST['_wp_http_referer'],'type=fvplayer_video_1')) {
      setcookie("selected_video1",$attachment_url);
      $selected_attachment = array('id'=>'src1', 'url'=>$attachment_url);
    }
    else
    if (strpos($_POST['_wp_http_referer'],'type=fvplayer_video_2')) {
      setcookie("selected_video2",$attachment_url);
      $selected_attachment = array('id'=>'src2', 'url'=>$attachment_url);
    }
    else
    if (strpos($_POST['_wp_http_referer'],'type=fvplayer_mobile')) {
      setcookie("selected_mobile",$attachment_url);
      $selected_attachment = array('id'=>'mobile', 'url'=>$attachment_url);
    }    
    else
    if (strpos($_POST['_wp_http_referer'],'type=fvplayer_subtitles')) {
      setcookie("selected_subtitles",$attachment_url);
      $selected_attachment = array('id'=>'subtitles', 'url'=>$attachment_url);
    }
    else {
      setcookie("selected_video",$attachment_url);
      $selected_attachment = array('id'=>'src', 'url'=>$attachment_url);
    }        
  }
  
  if (isset($selected_attachment['url'])) {
    $path_parts = pathinfo($selected_attachment['url']);
    if (in_array($path_parts['extension'], $video_types)) {
      if ($selected_attachment['id'] == 'src1') {
        $uploaded_video1 = $selected_attachment['url'];
      }
      else
      if ($selected_attachment['id'] == 'src2') {
        $uploaded_video2 = $selected_attachment['url'];
      } 
      else
      if ($selected_attachment['id'] == 'mobile') {
        $uploaded_mobile = $selected_attachment['url'];
      }      
      else {
        $uploaded_video = $selected_attachment['url'];
      }
    }
    if( in_array($path_parts['extension'], $splash_types) ) {
      $uploaded_image = $selected_attachment['url'];
    }
    else if( in_array($path_parts['extension'], $subtitles_types) ) {
      $uploaded_subtitles = $selected_attachment['url'];
    }
  }        
  
  $document_root = ( isset($_SERVER['SUBDOMAIN_DOCUMENT_ROOT']) && strlen(trim($_SERVER['SUBDOMAIN_DOCUMENT_ROOT'])) > 0 ) ? $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'];
  
  if (isset($uploaded_video)) {
    $serv = $_SERVER['SERVER_NAME'];
    $pattern = '/'.$serv.'(.*)/';
    preg_match($pattern, $uploaded_video, $matches);
    require_once( plugin_dir_path(__FILE__).'../includes/getid3/getid3.php');
    // Initialize getID3 engine                
    $getID3 = new getID3;     
    if (empty($matches)) {
      $ThisFileInfo = $getID3->analyze(realpath($document_root . $uploaded_video));
    }
    else { 
      $ThisFileInfo = $getID3->analyze(realpath($document_root . $matches[1]));
    }
    if (isset($ThisFileInfo['error'])) $file_error = "Could not read video details, please fill the width and height manually.";
    //getid3_lib::CopyTagsToComments($ThisFileInfo);
    $file_time = $ThisFileInfo['playtime_string'];            // playtime in minutes:seconds, formatted string
    $file_width = $ThisFileInfo['video']['resolution_x'];          
    $file_height = $ThisFileInfo['video']['resolution_y'];
    $file_size = $ThisFileInfo['filesize'];           
    $file_size = round($file_size/(1024*1024),2);                
  }
  if (!empty($uploaded_video)) {
  ?>
<script type='text/javascript'>
window.parent.document.getElementById('fv_wp_flowplayer_field_src').value = "<?php echo esc_attr($uploaded_video) ?>";
<?php if (!empty($uploaded_video) && !isset($ThisFileInfo['error'])) { ?>
  window.parent.document.getElementById('fv_wp_flowplayer_field_width').value = "<?php echo esc_attr(ceil($file_width)) ?>";
  window.parent.document.getElementById('fv_wp_flowplayer_field_height').value = "<?php echo esc_attr(ceil($file_height)) ?>";
  window.parent.document.getElementById('fv_wp_flowplayer_file_info').style.display = "table-row";
  window.parent.document.getElementById('fv_wp_flowplayer_file_duration').innerHTML = "<?php echo esc_attr($file_time) ?>";
  window.parent.document.getElementById('fv_wp_flowplayer_file_size').innerHTML = "<?php echo esc_attr($file_size) ?>";
<?php } ?>
window.parent.tb_remove();
</script>  
  <?php
  }
  else
  if (!empty($uploaded_image)) {
  ?>
<script type='text/javascript'>
window.parent.document.getElementById('fv_wp_flowplayer_field_splash').value = "<?php echo esc_attr($uploaded_image) ?>";
</script>  
  <?php
    $conf = get_option( 'fvwpflowplayer' );
    $post_thumbnail = false;
  
  	if( isset($conf["postthumbnail"]) ) {
  	  $post_thumbnail = $conf["postthumbnail"]; 
  	}
    if ( $post_thumbnail == 'true' && current_theme_supports( 'post-thumbnails') && isset($selected_attachment['id']) ) { 
      $post_id = (int)$_GET['post_id'];
      update_post_meta( $post_id, '_thumbnail_id', $selected_attachment['id'] );  
    }        
  }
  else
  if (!empty($uploaded_video1)) {
  ?>
<script type='text/javascript'>
window.parent.document.getElementById('fv_wp_flowplayer_field_src_1').value = "<?php echo esc_attr($uploaded_video1) ?>";
</script>  
  <?php
  } 
  else
  if (!empty($uploaded_video2)) {
  ?>
<script type='text/javascript'>
window.parent.document.getElementById('fv_wp_flowplayer_field_src_2').value = "<?php echo esc_attr($uploaded_video2) ?>";
</script>  
  <?php
  }
  else
  if (!empty($uploaded_mobile)) {
  ?>
<script type='text/javascript'>
window.parent.document.getElementById('fv_wp_flowplayer_field_mobile').value = "<?php echo esc_attr($uploaded_mobile) ?>";
</script>  
  <?php
  }  
  else
  if( !empty($uploaded_subtitles) ) {
  ?>
<script type='text/javascript'>
window.parent.document.getElementById('fv_wp_flowplayer_field_subtitles').value = "<?php echo esc_attr($uploaded_subtitles) ?>";
</script>   
  <?php
  }   
}


function fv_wp_flowplayer_image_send_to_editor() {
  return ''; 
}


function fv_wp_flowplayer_image_media_upload_html_bypass() {
  ?>
  <p class="upload-html-bypass hide-if-no-js">
  <?php _e('<strong>FV Wordpress Flowplayer Warning:</strong> Plese use <a href="#">the multi-file uploader</a>. Otherwise you will have to select the file in the Media Library.'); ?>
  </p>
  <?php
}


function fp_media_send_to_settings($html, $attachment_id, $attachment) {
  if(isset($_POST['_wp_http_referer']) && (strpos($_POST['_wp_http_referer'],'type=fvplayer_logo'))) {
    $logo_types = array('jpg','jpeg','gif','png', 'bmp','jpe');
    
    if (isset($attachment_id)) {
      $attachment_url = wp_get_attachment_url($attachment_id);
      $path_parts = pathinfo($attachment_url);
      if (in_array($path_parts['extension'], $logo_types)) {
        $selected_attachment = $attachment_url;
      }
    }
  ?>
  <script type="text/javascript">         
    window.parent.document.getElementById("logo").value = '<?php echo $selected_attachment ?>';
    window.parent.tb_remove();    
  </script>
  <?php
  }
}


/**
 * Administrator environment function.
 */
function flowplayer_admin () {
	// if we are in administrator environment
	if (function_exists('add_submenu_page')) {
		add_options_page(
		  'FV Wordpress Flowplayer', 
			'FV Wordpress Flowplayer', 
			'manage_options', 
			'fvplayer', 
			'flowplayer_page'
			);
	}
}


/**
 * Outputs HTML code for bool options based on arg passed.
 * @param string Currently selected value ('true' or 'false').
 * @return string HTML code
 */
function flowplayer_bool_select($current) {
	switch($current) {
    case "true":
      $html = '<option selected="selected" value="true">true</option><option value="false">false</option>';
      break;
    case "false":
      $html = '<option value="true" >true</option><option selected="selected" value="false">false</option>';
      break;
    default:
      $html = '<option value="true">true</option><option selected="selected" value="false">false</option>';
    break;
  }
  return $html;
}


/**
 * Displays administrator menu with configuration.
 */
function flowplayer_page() {
	global $fv_fp;
	include dirname( __FILE__ ) . '/../view/admin.php';
}


/**
 * Checks for errors regarding access to configuration file. Displays errors if any occur.
 * @param object $fv_fp Flowplayer class object.
 */
function flowplayer_check_errors($fv_fp) {

}


function flowplayer_add_media_button() {
  if( stripos( $_SERVER['REQUEST_URI'], 'post.php' ) === FALSE && stripos( $_SERVER['REQUEST_URI'], 'post-new.php' ) === FALSE ) {
    return;
  }

  global $post;
	$plugins = get_option('active_plugins');
	$found = false;
	foreach ( $plugins AS $plugin ) {
		if( stripos($plugin,'foliopress-wysiwyg') !== FALSE )
			$found = true;
	}
	$button_tip = 'Insert a Flash Video Player';
	$wizard_url = 'media-upload.php?post_id='.$post->ID.'&type=fv-wp-flowplayer';
	$button_src = RELATIVE_PATH.'/images/icon.png';    
	if(!$found) {
    $img = '<img src="' . $button_src . '" alt="' . $button_tip . '" />';
  }	
	echo '<a title="Add FV WP Flowplayer" href="#" class="fv-wordpress-flowplayer-button" >'.$img.'</a>';
}


function flowplayer_print_scripts() {
  wp_enqueue_script('media-upload');
  wp_enqueue_script('thickbox');
}


function flowplayer_print_styles() {
  wp_enqueue_style('thickbox');
}


function flowplayer_conversion_script() {
  global $wpdb;
  
  $posts = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type != 'revision'");
  
  $old_shorttag = '[flowplayer';
  $new_shorttag = '[fvplayer';
  $counter = 0;
  
  echo '<ol>';
  foreach($posts as $fv_post) {
    if ( stripos( $fv_post->post_content, $old_shorttag ) !== false ) {
      $update_post = array();
      $update_post['ID'] = $fv_post->ID;
      $update_post['post_content'] = str_replace( $old_shorttag, $new_shorttag, $fv_post->post_content ); 
      wp_update_post( $update_post );      
      echo '<li><a href="' . get_permalink($fv_post->ID) . '">' . get_the_title($fv_post->ID) . '</a> updated</li>';
      $counter++;
    } 
  }
  echo '</ol>';
  
  echo '<strong>Conversion was succesful. Total number of converted posts: ' . $counter . '</strong>';
  
  delete_option('fvwpflowplayer_conversion');
  
  die();
}


function fv_wp_flowplayer_admin_notice() {
	if( $notices = get_option('fv_wordpress_flowplayer_deferred_notices') ) {
  	echo '<div class="updated">
       			<p>'.$notices.'</p>
    			</div>';    	
  }

  $conversion = false; //(bool)get_option('fvwpflowplayer_conversion');
  if ($conversion) {
    echo '<div class="updated" id="fvwpflowplayer_conversion_notice"><p>'; 
    printf(__('FV Wordpress Flowplayer has found old shortcodes in the content of your posts. <a href="%1$s">Run the conversion script.</a>'), get_admin_url() . 'options-general.php?page=fvplayer');
    echo "</p></div>";
  }
  
  /*if( isset($_GET['page']) && $_GET['page'] == 'backend.php' ) {
	  $options = get_option( 'fvwpflowplayer' );
    if( $options['key'] == 'false' ) {
  		echo '<div class="updated"><p>'; 
    	printf(__('Brand new version of Flowplayer for HTML5. <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy">Licenses half price</a> in May.' ) );
    	echo "</p></div>";
    }
  }*/
}


function fv_wp_flowplayer_admin_enqueue_scripts( $page ) {
  if( $page !== 'post.php' && $page !== 'post-new.php' ) {
    return;
  }
  wp_register_script('fvwpflowplayer-domwindow', plugins_url().'/fv-wordpress-flowplayer/js/jquery.colorbox-min.js',array('jquery') );  
  wp_enqueue_script('fvwpflowplayer-domwindow');
  
  wp_register_style('fvwpflowplayer-domwindow-css', plugins_url().'/fv-wordpress-flowplayer/css/colorbox.css','','1.0','screen');
  wp_enqueue_style('fvwpflowplayer-domwindow-css');    
}


/*
Trick media uploader to show video only, while making sure we use our custom type; Also save options
*/
function fv_wp_flowplayer_admin_init() {
	if( isset($_GET['type']) ) {
		if( $_GET['type'] == 'fvplayer_video' || $_GET['type'] == 'fvplayer_video_1' || $_GET['type'] == 'fvplayer_video_2' || $_GET['type'] == 'fvplayer_mobile' ) {
			$_GET['post_mime_type'] = 'video';
		}
		else if( $_GET['type'] == 'fvplayer_splash' || $_GET['type'] == 'fvplayer_logo' ) {
			$_GET['post_mime_type'] = 'image';
		}
  }
  
  if( isset($_POST['fv-wp-flowplayer-submit']) ) {
  	global $fv_fp;
  	if( method_exists($fv_fp,'_set_conf') ) {
			$fv_fp->_set_conf();    
		} else {
			echo 'Error saving FV Flowplayer options.';
		}
	}

  global $fv_fp;
  if( preg_match( '!^\$\d+!', $fv_fp->conf['key'] ) ) {
    global $fv_wp_flowplayer_ver, $fv_wp_flowplayer_core_ver;
    $version = get_option( 'fvwpflowplayer_core_ver' );
    if( version_compare( $fv_wp_flowplayer_core_ver, $version ) == 1 ) {
      
      $args = array(
      	'body' => array( 'domain' => home_url(), 'plugin' => 'fv-wordpress-flowplayer', 'version' => $fv_wp_flowplayer_ver, 'core_ver' => $fv_wp_flowplayer_core_ver ),
        'timeout' => 20,
      	'user-agent' => 'fv-wordpress-flowplayer-'.$fv_wp_flowplayer_ver.' ('.$fv_wp_flowplayer_core_ver.')'
      );
      $resp = wp_remote_post( 'http://foliovision.com/?fv_remote=true', $args );
      
      if( $resp['body'] && $data = json_decode( $resp['body'] ) ) {
        if( $data->domain && $data->key && stripos( home_url(), $data->domain ) !== false ) {
          $fv_fp->conf['key'] = $data->key;
          update_option( 'fvwpflowplayer', $fv_fp->conf );
        }                            
      }            
    }      
  }
  
  global $fv_wp_flowplayer_core_ver;
  update_option( 'fvwpflowplayer_core_ver', $fv_wp_flowplayer_core_ver ); 
  
  if( isset($_GET['page']) && $_GET['page'] == 'fvplayer' ) {
  	wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
	}
  
  
}   


function fv_wp_flowplayer_edit_form_after_editor( ) {
  include dirname( __FILE__ ) . '/../view/wizard.php';
}


/*
Custom media uploader type is really just the default one
*/
function fv_wp_flowplayer_media_upload() {
  wp_media_upload_handler();
}                           


function fv_wp_flowplayer_after_plugin_row( $arg) {
	$args = func_get_args();
	
	if( $args[1]['Name'] == 'FV Wordpress Flowplayer' ) {		
    $options = get_option( 'fvwpflowplayer' );
    if( $options['key'] == 'false' || $options['key'] == '' ) :
		?>
<tr class="plugin-update-tr fv-wordpress-flowplayer-tr">
	<td class="plugin-update colspanchange" colspan="3">
		<div class="update-message">
			<a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/download">All Licenses 20% Off</a> in Summer.
		</div>
	</td>
</tr>
		<?php
		endif;
	}
}


function fv_wp_flowplayer_check_headers( $headers, $remotefilename, $random ) {
	global $fv_fp;

	$video_errors = array();

	if( $headers && $headers['response']['code'] == '404' ) {
		$video_errors[] = 'File not found (HTTP 404)!';  
	} else if( $headers && $headers['response']['code'] == '403' ) {
		$video_errors[] = 'Access to video forbidden (HTTP 403)!'; 
	} else if( $headers && $headers['response']['code'] != '200' && $headers['response']['code'] != '206' ) {
		$video_errors[] = 'Can\'t check the video (HTTP '.$headers['response']['code'].')!'; 
	} else {  
	
		if( !isset($headers['headers']['accept-ranges']) || $headers['headers']['accept-ranges'] != 'bytes' ) {
			$video_errors[] = 'Server does not support HTTP range requests!';  
		}
	
		if(
			( stripos( $remotefilename, '.mp4' ) !== FALSE && $headers['headers']['content-type'] != 'video/mp4' ) ||
			( stripos( $remotefilename, '.m4v' ) !== FALSE && $headers['headers']['content-type'] != 'video/x-m4v' ) ||
			( stripos( $remotefilename, '.webm' ) !== FALSE && $headers['headers']['content-type'] != 'video/webm' ) ||			
			( stripos( $remotefilename, '.mov' ) !== FALSE && $headers['headers']['content-type'] != 'video/mp4' )
		) {
			if( stripos( $remotefilename, '.mov' ) !== FALSE ) {
				$meta_note_addition = ' Firefox on Windows does not like MOV files with video/quicktime mime type.';
			} else if( stripos( $remotefilename, '.webm' ) !== FALSE ) {
				$meta_note_addition = ' Older Firefox versions don\'t like WEBM files with mime type other than video/webm.';
			} else {
				$meta_note_addition = ' Some web browsers may experience playback issues in HTML5 mode (Internet Explorer 9 - 10).';
				/*if( $fv_fp->conf['engine'] == 'default' ) {
					$meta_note_addition .= ' Currently you are using the "Default (mixed)" <a href="'.site_url().'/wp-admin/options-general.php?page=fvplayer">Preferred Flowplayer engine</a> setting, so IE will always use Flash and will play fine.';
				}*/
			} 
			
			$fix = '<div class="fix-meta-'.$random.'" style="display: none; ">
				<p>If the video is hosted on Amazon S3:</p>
				<blockquote>Using your Amazon AWS Management Console, you can go though your videos and find file content type under the "Metadata" tab in an object\'s "Properties" pane and fix it to "video/mp4" for MP4, "video/x-m4v" for M4V files, "video/mp4" for MOV files and "video/webm" for WEBM files.</blockquote>
				<p>If the video is hosted on your server, put this into your .htaccess:</p>
				<pre>AddType video/mp4             .mp4
	AddType video/webm            .webm
	AddType video/ogg             .ogv
	AddType application/x-mpegurl .m3u8
	AddType video/x-m4v           .m4v
	AddType video/mp4             .mov
	# hls transport stream segments:
	AddType video/mp2t            .ts</pre>
				<p>If you are using Microsoft IIS, you need to use the IIS manager. Check our <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq" target="_blank">FAQ</a> for more info.</p>
			</div>';     
			
			$video_errors[] = '<p><strong>Bad mime type</strong>: Video served with a bad mime type <tt>'.$headers['headers']['content-type'].'</tt>!'.$meta_note_addition.' (<a href="#" onclick="jQuery(\'.fix-meta-'.$random.'\').toggle(); return false">show fix</a>)</p>'.$fix ;        
		}
	}
	return $video_errors;
}
 
 
//	don't include body in our wp_remote_head requests. We have to use GET instead of HEAD because of Amazon
function fv_wp_flowplayer_http_api_curl( $handle ) {
	curl_setopt( $handle, CURLOPT_NOBODY, true );
}
 
 
function fv_wp_flowplayer_check_mimetype( $URLs = false, $meta = false ) {
	add_action( 'http_api_curl', 'fv_wp_flowplayer_http_api_curl' );

	global $fv_wp_flowplayer_ver, $fv_fp;
	
	if( !empty($meta) ) {
		extract( $meta, EXTR_SKIP );
	}
	
  if( defined('DOING_AJAX') && DOING_AJAX && isset( $_POST['media'] ) && stripos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {    
  	$URLs = json_decode( stripslashes( trim($_POST['media']) ));
  }
  
  if( isset($URLs) ) {
  
  	$all_sources = $URLs;
  	
  	$video_warnings = array();
  	$video_errors = array();
  	$video_info = array();
  	$message = false;
  	$new_info = false;

  	foreach( $all_sources AS $source ) {
  		if( preg_match( '!^rtmp://!', $source, $match ) ) {
  			$found_rtmp = true;
  		} else {
  			if( preg_match('!^http://(www\.)?youtube!',$source) ) {
  				$video_errors[]	= 'Youtube video embeding not supported yet. Please download the video file and put it in as a source directly.';
  			} else if( !isset($media) && !preg_match( '!\.(m3u8|m3u|avi)$!', $source) ) {
  				$media = $source;
  			}
  			
				if( preg_match( '!\.(mp4|m4v)$!', $source, $match ) ) {
					$found_mp4 = true;
				} else if( preg_match( '!\.mov$!', $source, $match ) ) {
					$video_warnings[]	= 'We recommend that you re-encode your MOV video into MP4 format. MOV is not be 100% compatible with HTML5 and might not play in Google Chrome. <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding#flash-only" target="_blank">Read our article about video encoding</a>';
				} else if( preg_match( '!\.flv$!', $source, $match ) ) {
					$found_flv = true;
				} else if( preg_match( '!\.(m3u8|m3u)$!', $source, $match ) ) {
					$found_m3u8 = true;
				} else if( preg_match( '!\.(avi)$!', $source, $match ) ) {
					$found_avi = true;
				}
  		}
  	}
  	
  	if( isset($found_flv) && !isset($found_mp4) ) {
  		$video_warnings[]	= 'We recommend that you re-encode your FLV video into MP4 format or also provide the video in MP4 format. FLV is not compatible with HTML5 and won\'t play on devices without Flash (iPhone, iPad...). <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding#flash-only">Read our article about video encoding</a>';
  	}
  	
  	if( isset($found_rtmp) && !isset($found_mp4) ) {
  		$video_warnings[]	= 'We recommend that you also provide your RTMP video in MP4 format. RTMP is not compatible with HTML5 and won\'t play on devices without Flash (iPhone, iPad...). <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding#flash-only">Read our article about video encoding</a>';
  	}
  	
  	if( isset($found_m3u8) && count($all_sources) == 1 ) {
  		$video_warnings[]	= 'We recommend that you also provide your M3U8 video in MP4 or WEBM format. HTTP Live Streaming (m3u8) is only supported by Apple iOS devices (iPhone, iPad...). <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding#flash-only">Read our article about video encoding</a>';
  	}
  	
  	if( isset($found_avi) ) {
  		$video_errors[]	= 'AVI format is not supported by neither HTML5 nor Flash. Please re-encode the video to MP4. <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding#flash-only">Read our article about video encoding</a>';
  	}
  	
  	//$random = rand( 0, 10000 );
  	$random = (isset($_POST['hash'])) ? trim($_POST['hash']) : false;
  	
  	
		if( isset($media) ) {	
		       
			$remotefilename = $media;
			$url_parts = parse_url( urldecode($remotefilename) );
			$url_parts_encoded = parse_url( $remotefilename );			
			if( !empty($url_parts['path']) ) {
					$url_parts['path'] = join('/', array_map('rawurlencode', explode('/', $url_parts['path'])));
			}
			if( !empty($url_parts['query']) ) {
					$url_parts['query'] = str_replace( '&amp;', '&', $url_parts_encoded['query'] );				
			}
			
			$remotefilename_encoded = http_build_url($remotefilename, $url_parts);  	
		
			if( $fv_fp->is_secure_amazon_s3($remotefilename_encoded) ) {	//	skip headers check for Amazon S3, as it's slow
				$headers = false;
			} else {
				$headers = wp_remote_head( trim( str_replace(' ', '%20', $remotefilename_encoded ) ), array( 'method' => 'GET', 'redirection' => 3 ) );
			}
			
			if( is_wp_error($headers) ) {
				$video_errors[] = 'Error checking '.$media.'!<br />'.print_r($headers,true);  
			} else {
				if( $headers ) {
					$video_errors += fv_wp_flowplayer_check_headers( $headers, $remotefilename, $random );
				}
				
				if( function_exists('is_utf8') && is_utf8($remotefilename) ) {
					 $video_errors[] = '<p><strong>UTF-8 error</strong>: Your file name is using non-latin characters, the file might not play in browsers using Flash for the video!</p>';
				}
				
								
				require_once( plugin_dir_path(__FILE__).'../includes/getid3/getid3.php');
				$getID3 = new getID3;     
				
				preg_match( '~^\S+://([^/]+)~', $remotefilename, $remote_domain );
				preg_match( '~^\S+://([^/]+)~', home_url(), $site_domain ); 
				
				if( !function_exists('curl_init') ) {
					$video_errors[] = 'cURL for PHP not found, please contact your server administrator.';
				} else if( strlen($remote_domain[1]) > 0 && strlen($site_domain[1]) > 0 && $remote_domain[1] != $site_domain[1] ) {
					$message = '<p>Analysis of <a class="bluelink" target="_blank" href="'.esc_attr($remotefilename_encoded).'">'.$remotefilename_encoded.'</a></p>';
					$video_info['File'] = 'Remote';

					//	taken from: http://www.getid3.org/phpBB3/viewtopic.php?f=3&t=1141
					$upload_dir = wp_upload_dir();      
					$localtempfilename = trailingslashit( $upload_dir['basedir'] ).'fv_flowlayer_tmp_'.md5(rand(1,999)).'_'.basename( substr($remotefilename_encoded,0,32) );
					
					$out = fopen( $localtempfilename,'wb' );
					if( $out ) {
						$ch = curl_init();
						curl_setopt( $ch, CURLOPT_URL, $remotefilename_encoded );    		
						curl_setopt( $ch, CURLOPT_RANGE, '0-2097152' );
						curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
						curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
						curl_setopt( $ch, CURLOPT_HEADER, true );
						curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
						curl_setopt( $ch, CURLOPT_USERAGENT, 'FV Flowplayer video checker/'.$fv_wp_flowplayer_ver);
						
						$data = curl_exec($ch);
						
						$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
						$header = substr($data, 0, $header_size);
						$body = substr($data, $header_size);

						file_put_contents( $localtempfilename, $body);
						if($ch == false) {
							$message .= 'CURL Error: '.curl_error ( $ch);
						}
						curl_close($ch);
						fclose($out);

						if( !$headers ) {
							$headers = WP_Http::processHeaders( $header );			
							
							$video_errors += fv_wp_flowplayer_check_headers( $headers, $remotefilename, $random );
							if( $headers['response']['code'] == '403' ) {
								$error = new SimpleXMLElement($body);
								
								if( stripos( $error->Message, 'Request has expired' ) !== false ) {
									$video_errors[] = '<p><strong>Amazon S3</strong>: Your secure link is expired, there might be problem with your Amazon S3 plugin. Please test if the above URL opens in your browser.</p>';		
								} else {
									$video_errors[] = '<p><strong>Amazon S3</strong>: '.$error->Message.'</p>';				
								}
								
							}
						}
										
						$ThisFileInfo = $getID3->analyze( $localtempfilename );
						
						if( !@unlink($localtempfilename) ) {
							$video_errors[] = 'Can\'t remove temporary file for video analysis in <tt>'.$localtempfilename.'</tt>!';
						}         
					} else {
						$video_errors[] = 'Can\'t create temporary file for video analysis in <tt>'.$localtempfilename.'</tt>!';
					}                  
				} else {
					$a_link = str_replace( '&amp;', '&', $remotefilename );
					$message = '<p>Analysis of <a class="bluelink" target="_blank" href="'.esc_attr($a_link).'">'.$a_link.'</a></p>';
					$video_info['File'] = 'Local';
					
					$document_root = ( isset($_SERVER['SUBDOMAIN_DOCUMENT_ROOT']) && strlen(trim($_SERVER['SUBDOMAIN_DOCUMENT_ROOT'])) > 0 ) ? $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'];
					
					global $blog_id;
					if( isset($blog_id) && $blog_id > 1 ) {
						$upload_dir = wp_upload_dir();
						if( stripos($remotefilename, $upload_dir['baseurl']) !== false ) { 
							$localtempfilename = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $remotefilename );						
						} else {
							$localtempfilename = preg_replace( '~^\S+://[^/]+~', trailingslashit($document_root), preg_replace( '~(\.[a-z]{1,4})/files/~', '$1/wp-content/blogs.dir/'.$blog_id.'/files/', $remotefilename ) );							
						}
					} else {
						$localtempfilename = preg_replace( '~^\S+://[^/]+~', trailingslashit($document_root), $remotefilename );
					}
		
					$ThisFileInfo = $getID3->analyze( $localtempfilename );
				}
																						
				if( isset($ThisFileInfo['error']) ) {
					fv_wp_flowplayer_array_search_by_item( 'not correctly handled', $ThisFileInfo['error'], $check, true );
					if( $check ) {
						$video_info['Warning'] = 'Video checker doesn\'t support this format.'; 
					} else { 
						$video_errors[] = implode( '<br />', $ThisFileInfo['error'] );
					}
				}
				
				if( isset($ThisFileInfo['fileformat']) ) {
					$video_info['Format']  = $ThisFileInfo['fileformat'];
				}		
				
				if( isset($ThisFileInfo['quicktime']) ) {			
					if( !isset($ThisFileInfo['quicktime']['moov']) ) {
						$video_errors[] = 'Video meta data (moov-atom) not found at the start of the file! Please move the meta data to the start of video, otherwise it might have a slow start up time. Plese check the "How do I fix the bad metadata (moov) position?" question in <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq" target="_blank">FAQ</a>.';
					} else {
						if( $ThisFileInfo['quicktime']['moov']['offset'] > 1024 ) {
							$video_errors[]  = 'Meta Data (moov) not found at the start of the file (found at '. number_format( $ThisFileInfo['quicktime']['moov']['offset'] ).' byte)! Please move the meta data to the start of video, otherwise it might have a slow start up time. Plese check the "How do I fix the bad metadata (moov) position?" question in <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq" target="_blank">FAQ</a>.';
						} else {
							$video_info['Moov position']  = $ThisFileInfo['quicktime']['moov']['offset'];		
						}
						
						/*if( isset($ThisFileInfo['quicktime']['moov']['subatoms']) ) {
							foreach( $ThisFileInfo['quicktime']['moov']['subatoms'] AS $subatom ) {
								if( $subatom['hierarchy'] == 'trak' ) {
								
								}
							}
						}*/
						
						fv_wp_flowplayer_array_search_by_item( 'stts', $ThisFileInfo, $stts );
						if( isset($stts[0]) && $stts[0]['number_entries'] > 1 ) {
							$video_info['Seek points'] = $stts[0]['number_entries'].' (stts)';
						} else {
							if( isset($stts[0]['time_to_sample_table'][0]['sample_count']) ) {
								$video_info['Seek points'] = $stts[0]['time_to_sample_table'][0]['sample_count'].' (stts sample count)';
							} else { 
								$video_errors[] = 'One one seeking point found, it might be slow to seek in the video.';
							}
						}               
						 
					}
				}
				
				if( isset($ThisFileInfo['audio']['streams']) ) {			
					$count_streams = count( $ThisFileInfo['audio']['streams'] ); 
					if( $count_streams == 1 ) {
						$video_info['Audio'] = $count_streams.' stream, ';
					} else {
						$video_info['Audio'] = $count_streams.' streams, ';
					}
					foreach( $ThisFileInfo['audio']['streams'] AS $stream ) {
						foreach( array( 'dataformat', 'codec', 'sample_rate', 'channels', 'bits_per_sample', 'channelmode' ) AS $item ) {
							if( isset( $stream[$item] ) ) {
								$add = $stream[$item];
								switch( $item ) {
									case 'codec' : $add = '('.$add.')'; break;
									case 'sample_rate' : $add .= 'Hz, '; break;
									case 'bits_per_sample' : $add .= 'bit, '; break;
									case 'channels' : $add .= ' channels, '; break;					
								}
								$video_info['Audio'] .= $add.' ';
							}
						}
						$video_info['Audio'] .= '|';
					}
					$video_info['Audio'] = trim( $video_info['Audio'], '|' );
				}
				
				$video_info['Video'] = array();
				if( isset($ThisFileInfo['video']['streams']) ) {			
					$count_streams = count( $ThisFileInfo['video']['streams'] ); 
					if( $count_streams == 1 ) {
						$video_info['Video'] = $count_streams.' stream, ';
					} else {
						$video_info['Video'] = $count_streams.' streams, ';
					}
					foreach( $ThisFileInfo['video']['streams'] AS $stream ) {
						foreach( array( 'dataformat', 'resolution_x', 'resolution_y', 'frame_rate' ) AS $item ) {
							if( isset( $stream[$item] ) ) {
								$add = $stream[$item];
								switch( $item ) {
									case 'resolution_x' : $add .= ' x'; break;
									case 'resolution_y' : $add .= ', '; break;
									case 'frame_rate' : $add .= ' fps '; break;		
								}
								$video_info['Video'] .= $add.' ';
							}
						}
						$video_info['Video'] .= '|';
					}
					$video_info['Video'] = trim( $video_info['Video'], '|' );
					
					if( isset($ThisFileInfo['video']['bitrate']) ) {
						$video_info['Video'] .= number_format( ceil($ThisFileInfo['video']['bitrate']/1024) ).'Kbps ';
					}
				}  
				
				if( isset($ThisFileInfo['video']['fourcc']) ) {			
					if( !isset($video_info['Video']) ) $video_info['Video'] = array();
					$video_info['Video'][] .= $ThisFileInfo['video']['fourcc'].' codec';
				}
		
				if( isset($ThisFileInfo['quicktime']['ftyp']['signature']) ) {	
					$video_info['Video'][] .= $ThisFileInfo['quicktime']['ftyp']['signature'].' file type ';
					if( strcasecmp( trim($ThisFileInfo['quicktime']['ftyp']['signature']), 'M4V' ) === 0 && preg_match( '~.m4v$~', $remotefilename ) ) {      
						$m4v_note_addition = false;
						/*if( $fv_fp->conf['engine'] == 'default' ) {
							$m4v_note_addition = ' Currently you are using the "Default (mixed)" <a href="'.site_url().'/wp-admin/options-general.php?page=fvplayer">Preferred Flowplayer engine</a> setting, so Firefox on Windows will always use Flash for M4V and will play fine.';
						} */
						$video_errors[] = 'We recommend that you change file extension of M4V videos to MP4, to avoid issues with Firefox on PC. '.$m4v_note_addition;
					}
				}
				
				if( isset($video_info['Video']) && is_array($video_info['Video']) ) {
					$video_info['Video'] = implode( ', ', $video_info['Video'] );
				
					$video_format_info = array( 'avc1' => 'H.264 Encoder', 'mp42' => 'MS-MPEG4 v2 Decoder' );
					foreach( $video_format_info AS $key => $item ) {
						$video_info['Video'] = str_replace( $key, $key.' ('.$item.')', $video_info['Video'] );
					}
				}      
				
			}	//	end is_wp_error check			
			
		}	//	end isset($media) 
		
		
		if( $video_errors ) {
			foreach( $video_errors AS $key => $item ) {			
				if( preg_match( '!Atom at offset \d+ claims to go beyond end-of-file!', $item ) ) {
					unset( $video_errors[$key] );	//	we are not interested in this particular warning, as we don't download the full file
				}
			}
		}
		
			
		if( $video_errors ) {			
			$message_items = array();
			foreach( $video_errors AS $key => $item ) {					        
				if( $item && stripos( $item, '</p>' ) === false ) {
					$item = '<p><strong>Error</strong>: '.$item.'</p>';
				}
				$message_items[] = $item;
			}
			$message .= implode("\n", $message_items);
		}
		
		if( $video_warnings ) {				
			$message_items = array();
			foreach( $video_warnings AS $key => $item ) {					        
				if( $item && stripos( $item, '</p>' ) === false ) {
					$item = '<p><strong>Warning</strong>: '.$item.'</p>';
				}
				$message_items[] = $item;
			}
			$message .= implode("\n", $message_items);
		}		
		
		
		if( isset($video_info) ) {
			$message_items = array();
			foreach( $video_info AS $key => $item ) {
				$message_item = '';	
				if( !is_int($key) ) {
					$message_item .= $key.': ';
				}
				$message_item .= '<tt>'.$item.'</tt>';
				$message_items[] = $message_item;
			}
			$message .= '<p>'.implode("<br />\n", $message_items).'</p>';
		}		
				
		$message = '<div class="mail-content-notice">'.$message.'</div>';		
		
		if( isset($ThisFileInfo) && $ThisFileInfo ) {
			$more_info = var_export($ThisFileInfo,true);
		
			$note = '(Note: read about remote file analysis in <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq">FAQ</a>)';
			$more_info = str_replace( array('Unknown QuickTime atom type', 'Atom at offset'), array($note.' Unknown QuickTime atom type', $note.' Atom at offset'), $more_info );
		
			$lines = explode( "\n", $more_info );
			
			$depth = 0;
			$new_info = '<p>'.$note.'</p><div class="fv-wp-flowplayer-notice-parsed level-0">';
			foreach( $lines AS $line ) {
				$class = ( $depth > 0 ) ? ' indent' : '';
			
				if( strcmp( trim($line), 'array (' ) == 0 ) {
					if( $depth == 0 ) {			
						$new_info .= '<div class="fv-wp-flowplayer-notice-parsed level-0 row close"></div>';
					}    		
					$depth++;
					$new_info .= '<div class="fv-wp-flowplayer-notice-parsed level-'.$depth.''.$class.'">';
					continue;
				}
				if( strcmp( trim($line), '),' ) == 0 ) {
					$depth--;
					$new_info .= '</div><!-- .level-'.$depth.' -->';
					$new_info .= '<div class="fv-wp-flowplayer-notice-parsed level-'.$depth.' row close"></div>';
					continue;
				}
				
				
				if( $depth > 7 ) {
					$style = ' fv-wp-fp-hidden';
				} else {
					$style = '';
				}
				
				$line_i = explode( " => ", trim($line), 2 );
				if( !$line_i ) {
					continue;
				}
								
				$line_html = '<div class="row'.$class.$style.'"><span>'.ucfirst( str_replace( "' =>", '', trim($line_i[0],"' ")) ).'</span><span class="value">'.( (isset($line_i[1])) ? trim(rtrim($line_i[1],", "),"' ") : '' ).'</span><div style="clear:both;"></div></div>';
				
				$new_info .= $line_html."\n";
			}
			$new_info .= '</div>';
		
		}
		
		$message .= '<div class="support-'.$random.'">';
		$message .= '<textarea id="wpfp_support_'.$random.'" class="wpfp_message_field" onclick="if( this.value == \'Enter your comment\' ) this.value = \'\'" style="width: 98%; height: 150px">Enter your comment</textarea>';
		$message .= '<p><a class="techinfo" href="#" onclick="jQuery(\'.more-'.$random.'\').toggle(); return false">Technical info</a> <img id="wpfp_spin_'.$random.'" src="'.site_url().'/wp-includes/images/wpspin.gif" style="display: none; " /> <input type="button" onclick="fv_wp_flowplayer_support_mail(\''.$random.'\', this); return false" value="Send report to Foliovision" /></p>';
		$message .= '</div>';
		$message .= '<div class="more-'.$random.' mail-content-details" style="display: none; "><p>Plugin version: '.$fv_wp_flowplayer_ver.'</p>'.$new_info.'</div>';
				
          
    if( count($video_errors) > 0 ) {
    	$issues_text = '<span class="vid-issues">Video Issues</span>';
    } else if( count($video_warnings) ) {
			$issues_text = '<span class="vid-warning">Video Warnings</span>';    
    } else {
    	$issues_text = '<span class="vid-ok">Video OK</span>';
    }
    $message = "<div onclick='fv_wp_flowplayer_show_notice(\"$random\", this.parent); return false' class='fv_wp_flowplayer_notice_head'>Report Issue</div><small>Admin: <a class='fv_wp_flowplayer_dialog_link' href='#' onclick='fv_wp_flowplayer_show_notice(\"$random\", this); return false'>$issues_text</a></small><div id='fv_wp_fp_notice_$random' class='fv_wp_fp_notice_content' style='display: none;'>$message</div>\n";
      
    $json = @json_encode( array( $message, count( $video_errors ), count( $video_warnings ) ) );
    $last_error = ( function_exists('json_last_error') ) ? json_last_error() : true;
    
    if( isset($meta_action) && $meta_action == 'check_time' ) {
    
    	if( isset($ThisFileInfo['playtime_seconds']) ) {
    		$time = $ThisFileInfo['playtime_seconds'];    	
    	}
    	global $post;
    	$fv_flowplayer_meta = get_post_meta( $post->ID, '_fv_flowplayer', true );
    	$fv_flowplayer_meta = ($fv_flowplayer_meta) ? $fv_flowplayer_meta : array();
    	$fv_flowplayer_meta[sanitize_title($meta_original)] = array('time' => $time);
    	update_post_meta( $post->ID, '_fv_flowplayer', $fv_flowplayer_meta );
    	
    } else {    
			if( $last_error ) {
				if( function_exists('mb_check_encoding') && function_exists('utf8_encode') ) {
						if(!mb_check_encoding($message, 'UTF-8')) {
								$message = utf8_encode($message);
						}
					} else {
						$message = htmlentities( $message, ENT_QUOTES, 'utf-8', FALSE);
						$message = ( $message ) ? $message : 'Admin: Error parsing JSON';
					}           
				
				$json = json_encode( array( $message, count( $video_errors ), count( $video_warnings ) ) );
				$last_error = ( function_exists('json_last_error') ) ? json_last_error() : false;
				if( $last_error ) {
					echo json_encode( array( 'Admin: JSON error: '.$last_error, count( $video_errors ), count( $video_warnings ) ) );    
				} else {
					echo $json;
				}
			} else {
				echo $json;
			}
			die();
		}
  } else {  
  	die('-1');
  }
}


//	enter script URL, return false if it's not version 5
function fv_wp_flowplayer_check_script_version( $url ) {
	$url_mod = preg_replace( '!\?.+!', '', $url );
	if( preg_match( '!flowplayer-([\d\.]+)!', $url_mod, $version ) && $version[1] ) {
		if( strpos( $version[1], '5' ) !== 0 ) {
			return -1;			
		}
	}
	
	global $fv_wp_flowplayer_ver;
	if( strpos( $url, '/fv-wordpress-flowplayer/flowplayer/flowplayer.min.js?ver='.$fv_wp_flowplayer_ver ) !== false ) {
		return 1;
	}
	return 0;
}


function fv_wp_flowplayer_check_jquery_version( $url, &$array, $key ) {
	if( preg_match( '!/jquery(\.[a-zA-Z]{2,}|-[a-zA-Z]{3,})[^/]*?\.js!', $url ) ) {	//	jquery.ui.core.min.js, jquery-outline-1.1.js
		unset( $array[$key] );
		return 2;
	}
	
	$url_mod = preg_replace( '!\?.+!', '', $url );
	if( preg_match( '!(\d+.[\d\.]+)!', $url_mod, $version ) && $version[1] ) {
		if( version_compare($version[1], '1.7.1') == -1 ) {
			return -1;
		} else {
			return 1;
		}
	}
	
	//	if jQuery is in the Wordpress install, we know that the ?ver= says what version it is
	if( strpos( $url, site_url().'/wp-includes/js/jquery/jquery.js' ) !== false ) {
		if( preg_match( '!(\d+.[\d\.]+)!', $url, $version ) && $version[1] ) {
			if( version_compare($version[1], '1.7.1') == -1 ) {
				return -1;
			} else {
				return 1;
			}
		}
	}

	return 0;
}


function fv_wp_flowplayer_check_files() {
  if( stripos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {    
  	global $wpdb;
  	define('VIDEO_DIR', '/videos/');
  	
  	$videos1 = $wpdb->get_results( "SELECT ID, post_content FROM $wpdb->posts WHERE post_type != 'revision' AND post_content LIKE '%[flowplayer %'" );
  	$videos2 = $wpdb->get_results( "SELECT ID, post_content FROM $wpdb->posts WHERE post_type != 'revision' AND post_content LIKE '%[fvplayer %'" );  
  	
  	$videos = array_merge( $videos1, $videos2 );
  	  	
  	$source_servers = array();
  	
  	$shortcodes_count = 0;
  	$src_count = 0;
  	if( count($videos) ) {
			foreach( $videos AS $post ) {
			
				$shortcodes_count += preg_match_all( '!\[(?:flowplayer|fvplayer)[^\]]+\]!', $post->post_content, $post_videos );
				if( count($post_videos[0]) ) {
					foreach( $post_videos[0] AS $post_video ) {
						$post_video = preg_replace( '!popup=\'.*\'!', '', $post_video );
						$src_count += preg_match_all( '!(?:src|src1|src2|src3|mp4|webm|ogv)=[\'"](.*?(?:mp4|m4v))[\'"]!', $post_video, $sources1 );
						$src_count += preg_match_all( '!(?:src|src1|src2|src3|mp4|webm|ogv)=([^\'"].*?(?:mp4|m4v|flv))[\s\]]!', $post_video, $sources2 );
						$sources = array_merge( $sources1[1], $sources2[1] );
						if( count($sources) ) {
							foreach($sources AS $src ) {
								if( strpos( $src, '//' ) === 0 ) {
									$src = 'http:'.$src;
								} else if( strpos( $src, '/' ) === 0 ) {
									$src = home_url().$src;
								} else if( !preg_match( '!^\S+://!', $src ) )  {
									$src = home_url().VIDEO_DIR.$src;
								} 
								
								$server = preg_replace( '!(.*?//.*?)/.+!', '$1', $src );
																
								$source_servers[$server][] = array( 'src' => $src, 'post_id' => $post->ID );
							}
						}
					}
				}
				
			}
  	}
  	
  	$ok = array();
  	$errors = array();
  	
  	$count = 0;
  	foreach( $source_servers AS $server => $videos ) {
  	
  		//echo $server."\n";  		

			if( stripos( trim($videos[0]['src']), 'rtmp://' ) === false ) {
  			$headers = get_headers( trim($videos[0]['src']) );
  		}
			if( isset($headers) && $headers ) {

				$posts_links = '';
				foreach( $videos AS $video ) {
					$posts_links .= '<a href="'.home_url().'?p='.$video['post_id'].'">'.$video['post_id'].'</a> ';	
				}

				foreach( $headers AS $line ) {
					if( stripos( $line, 'Content-Type:' ) !== FALSE ) {
						preg_match( '~Content-Type: (\S+)$~', $line, $match );
						$mime_matched = ( isset($match[1]) ) ? $match[1] : '';
						
						if(
							( !preg_match( '~video/mp4$~', $line ) && stripos( $videos[0]['src'], '.mp4' ) !== FALSE ) ||
							( !preg_match( '~video/x-m4v$~', $line ) && stripos( $videos[0]['src'], '.m4v' ) !== FALSE )
						) {
							if( strpos( $server, 'amazonaws' ) !== false ) {
								$fix = '<p>It\'s important to set this correctly, otherwise the videos will not play in HTML5 mode in Internet Explorer 9 and 10.</p><blockquote><code>Using your Amazon AWS Management Console, you can go though your videos and find file content type under the "Metadata" tab in an object\'s "Properties" pane and fix it to "video/mp4" for MP4 and "video/x-m4v" for M4V files.</code></blockquote>';
							} else {
								$fix = '<p>It\'s important to set this correctly, otherwise the videos will not play in HTML5 mode in Internet Explorer 9 and 10.</p><p>Make sure you put this into your .htaccess file, or ask your server admin to upgrade the web server mime type configuration:</p> <blockquote><pre><code>AddType video/mp4             .mp4
AddType video/webm            .webm
AddType video/ogg             .ogv
AddType application/x-mpegurl .m3u8
AddType video/x-m4v           .m4v
# hls transport stream segments:
AddType video/mp2t            .ts</code></pre></blockquote>';
							}
				
							$errors[] = 'Server <code>'.$server.'</code> uses bad mime type <code>'.$mime_matched.'</code> for MP4 or M4V videos! (<a href="#" onclick="jQuery(\'#fv-flowplayer-warning-'.$count.'\').toggle(); return false">click to see a list of posts</a>) (<a href="#" onclick="jQuery(\'#fv-flowplayer-info-'.$count.'\').toggle(); return false">show fix</a>) <div id="fv-flowplayer-warning-'.$count.'" style="display: none; ">'.$posts_links.'</div> <div id="fv-flowplayer-info-'.$count.'" style="display: none; ">'.$fix.'</div>'; 
						} else if(
							( !preg_match( '~video/webm$~', $line ) && stripos( $videos[0]['src'], '.webm' ) !== FALSE )
						) {
							if( strpos( $server, 'amazonaws' ) !== false ) {
								$fix = '<p>It\'s important to set this correctly, otherwise the videos will not play in older Firefox.</p><blockquote><code>Using your Amazon AWS Management Console, you can go though your videos and find file content type under the "Metadata" tab in an object\'s "Properties" pane and fix it to "video/webm" for WEBM videos.</code></blockquote>';
							} else {
								$fix = '<p>It\'s important to set this correctly, otherwise the videos will not play in older Firefox.</p><p>Make sure you put this into your .htaccess file, or ask your server admin to upgrade the web server mime type configuration:</p> <blockquote><pre><code>AddType video/mp4             .mp4
AddType video/webm            .webm
AddType video/ogg             .ogv
AddType application/x-mpegurl .m3u8
AddType video/x-m4v           .m4v
# hls transport stream segments:
AddType video/mp2t            .ts</code></pre></blockquote>';
							}
				
							$errors[] = 'Server <code>'.$server.'</code> uses bad mime type <code>'.$mime_matched.'</code> for MP4 or M4V videos! (<a href="#" onclick="jQuery(\'#fv-flowplayer-warning-'.$count.'\').toggle(); return false">click to see a list of posts</a>) (<a href="#" onclick="jQuery(\'#fv-flowplayer-info-'.$count.'\').toggle(); return false">show fix</a>) <div id="fv-flowplayer-warning-'.$count.'" style="display: none; ">'.$posts_links.'</div> <div id="fv-flowplayer-info-'.$count.'" style="display: none; ">'.$fix.'</div>'; 
						} else if( stripos( $videos[0]['src'], '.mp4' ) !== FALSE  || stripos( $videos[0]['src'], '.m4v' ) !== FALSE ) {
							$ok[] = 'Server <code>'.$server.'</code> appears to serve correct mime type <code>'.$mime_matched.'</code> for MP4 and M4V videos.'; 						
						}
					}
				}
				
				$count++;
			}
			
			if( $server == 'http://lifeinamovie.com' ) {
  			//break;
  		}
			
  	}
  	
  	$output = array( 'errors' => $errors, 'ok' => $ok/*, 'html' => $response['body'] */);
		echo json_encode($output);
		die();
  }
  die('-1');
}


function fv_wp_flowplayer_check_template() {
	$ok = array();
	$errors = array();
	
  if( stripos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {    
  	$response = wp_remote_get( home_url().'?fv_wp_flowplayer_check_template=yes' );
  	if( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$output = array( 'error' => $error_message );
		} else {		
			
			$active_plugins = get_option( 'active_plugins' );
			foreach( $active_plugins AS $plugin ) {
				if( stripos( $plugin, 'wp-minify' ) !== false ) {
					$errors[] = "You are using <strong>WP Minify</strong>, so the script checks would not be accurate. Please check your videos manually.";
					$wp_minify_options = get_option('wp_minify');
					if( isset($wp_minify_options['js_in_footer']) && $wp_minify_options['js_in_footer'] ) {
						$errors[] = "Please make sure that you turn off Settings -> WP Minify -> 'Place Minified JavaScript in footer'.";
					}
					$output = array( 'errors' => $errors, 'ok' => $ok/*, 'html' => $response['body'] */);
					echo json_encode($output);
					die();
				}
			}
			
			if( function_exists( 'w3_instance' ) && $minify = w3_instance('W3_Plugin_Minify') ) {			
				if( $minify->_config->get_boolean('minify.js.enable') ) {
					$errors[] = "You are using <strong>W3 Total Cache</strong> with JS Minify enabled. The template check might not be accurate. Please check your videos manually.";
				}
			}

			if( stripos($response['body'],'<!--fv-flowplayer-footer-->') === false ) {
				$errors[] = "It appears that your template is not using the wp_footer() hook. Advanced FV Flowplayer functions may not work properly.";
			} else {
				$ok[] = "wp_footer found in your template!";
			}
			
			$response['body'] = preg_replace( '$<!--[\s\S]+?-->$', '', $response['body'] );	//	handle HTML comments
			
			//	check Flowplayer scripts
			preg_match_all( '!<script[^>]*?src=[\'"]([^\'"]*?flowplayer[^\'"]*?\.js[^\'"]*?)[\'"][^>]*?>\s*?</script>!', $response['body'], $flowplayer_scripts );
			if( count($flowplayer_scripts[1]) > 0 ) {
				if( count($flowplayer_scripts[1]) > 1 ) {
					$errors[] = "It appears there are <strong>multiple</strong> Flowplayer scripts on your site, your videos might not be playing, please check. There might be some other plugin adding the script.";
				}
				foreach( $flowplayer_scripts[1] AS $flowplayer_script ) {
					$check = fv_wp_flowplayer_check_script_version( $flowplayer_script );
					if( $check == - 1 ) {
						$errors[] = "Flowplayer script <code>$flowplayer_script</code> is old version and won't play. You need to get rid of this script.";
					} else if( $check == 1 ) {
						$ok[] = "FV Flowplayer script found: <code>$flowplayer_script</code>!";
						$fv_flowplayer_pos = strpos( $response['body'], $flowplayer_script );
					}
				}
			} else if( count($flowplayer_scripts[1]) < 1 ) {
				$errors[] = "It appears there are <strong>no</strong> Flowplayer scripts on your site, your videos might not be playing, please check.";			
			}
			

			//	check jQuery scripts						
			preg_match_all( '!<script[^>]*?src=[\'"]([^\'"]*?jquery[^\'"]*?\.js[^\'"]*?)[\'"][^>]*?>\s*?</script>!', $response['body'], $jquery_scripts );
			if( count($jquery_scripts[1]) > 0 ) {				
				foreach( $jquery_scripts[1] AS $jkey => $jquery_script ) {
					$check = fv_wp_flowplayer_check_jquery_version( $jquery_script, $jquery_scripts[1], $jkey );
					if( $check == - 1 ) {
						$errors[] = "jQuery library <code>$jquery_script</code> is old version and might not be compatible with Flowplayer.";
					} else if( $check == 1 ) {
						$ok[] = "jQuery library 1.7.1+ found: <code>$jquery_script</code>!";
						$jquery_pos = strpos( $response['body'], $jquery_script );
					} else if( $check == 2 ) {
						//	nothing
					}	else {
						$errors[] = "jQuery library <code>$jquery_script</code> found, but unable to check version, please make sure it's at least 1.7.1.";
					}
				}
				if( count($jquery_scripts[1]) > 1 ) {
					$errors[] = "It appears there are <strong>multiple</strong> jQuery libraries on your site, your videos might not be playing, please check.\n";
				}
			} else if( count($jquery_scripts[1]) < 1 ) {
				$errors[] = "It appears there are <strong>no</strong> jQuery library on your site, your videos might not be playing, please check.\n";			
			}
			
						
			if( $fv_flowplayer_pos > 0 && $jquery_pos > 0 && $jquery_pos > $fv_flowplayer_pos ) {
				$errors[] = "It appears your Flowplayer JavaScript library is loading before jQuery. Your videos probably won't work. Please make sure your jQuery library is loading using the standard Wordpress function - wp_enqueue_scripts(), or move it above wp_head() in your header.php template.";
			}
		
			
			$output = array( 'errors' => $errors, 'ok' => $ok/*, 'html' => $response['body'] */);
		}
		echo json_encode($output);
		die();
  }
  
  die('-1');
} 
 
 
function fv_wp_flowplayer_array_search_by_item( $find, $in_array, &$found, $like = false ) {
    global $fv_wp_flowplayer_array_search_by_item_depth;
    
    $fv_wp_flowplayer_array_search_by_item_depth++;
    if( $fv_wp_flowplayer_array_search_by_item_depth > 100 ) {
      return false;
    }

    if( is_array( $in_array ) )
    {
        foreach( $in_array as $key=> $val )
        {
            if( is_array( $val ) ) {
              fv_wp_flowplayer_array_search_by_item( $find, $val, $found );
            } else {
                if( !$like && strcasecmp($find, $val) === 0 ) {
                  $found[] = $in_array;
                } else if( $like && stripos($val, $find) !== false ) {
                  $found[] = $in_array;
                }
            }
        }
        return false;
    }
    return false;
}  


function fv_wp_flowplayer_support_mail() {
  if( isset( $_POST['notice'] ) && stripos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {

  	global $current_user;
    get_currentuserinfo();

  	$content = '<p>User: '.$current_user->display_name." (".$current_user->user_email.")</p>\n";  	
  	$content .= '<p>User Agent: '.$_SERVER['HTTP_USER_AGENT']."</p>\n";  	
  	$content .= '<p>Referer: '.$_SERVER['HTTP_REFERER']."</p>\n";
  	$content .= "<p>Comment:</p>\n".wpautop( stripslashes($_POST['comment']) );  	  	
  	$notice = str_replace( '<span class="value"', ': <span class="value"', stripslashes($_POST['notice']) );
  	$notice .= str_replace( '<span class="value"', ': <span class="value"', stripslashes($_POST['details']) );  	
  	
  	$content .= "<p>Video analysis:</p>\n".$notice;  	  	
    
    global $fv_wp_flowplayer_support_mail_from, $fv_wp_flowplayer_support_mail_from_name; 
    
    //$headers = "Reply-To: \"$current_user->display_name\" <$current_user->user_email>\r\n";
    $fv_wp_flowplayer_support_mail_from_name = $current_user->display_name;
    $fv_wp_flowplayer_support_mail_from = $current_user->user_email;
  	
  	add_filter( 'wp_mail_content_type', create_function('', "return 'text/html';") );
  	
  	add_action('phpmailer_init', 'fv_wp_flowplayer_support_mail_phpmailer_init' );
  	wp_mail( 'fvplayer@foliovision.com', 'FV Flowplayer Quick Support Submission', $content, $headers );
  	
  	die('1');
  }
}


function fv_wp_flowplayer_support_mail_phpmailer_init( $phpmailer ) {
	global $fv_wp_flowplayer_support_mail_from, $fv_wp_flowplayer_support_mail_from_name; 
	
	if( $fv_wp_flowplayer_support_mail_from_name ) {
		$phpmailer->FromName = trim( $fv_filled_in_phpmailer_init_from_name );
	}
	if( $fv_wp_flowplayer_support_mail_from ) {
		if( strcmp( trim($phpmailer->From), trim($fv_wp_flowplayer_support_mail_from) ) != 0 && !trim($phpmailer->Sender) ) {
			$phpmailer->Sender = trim($phpmailer->From);	
		}
		$phpmailer->From = trim( $fv_wp_flowplayer_support_mail_from );
	}	

}


function fv_wp_flowplayer_save_post( $id ) {
	if( wp_is_post_revision($id) ) {
  	return true;
  }
  
  global $fv_fp;
  if( !isset($fv_fp->conf['amazon_bucket']) ) {
  	return;
  }
  
  $videos = array();
  $saved_post = get_post($id);
  preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $saved_post->post_content, $matches );
  if( isset($matches[0]) && count($matches[0]) ) {
  	foreach( $matches[0] AS $shortcode ) {
  		$process = false;
  		foreach( $fv_fp->conf['amazon_bucket'] AS $bucket ) {
  			if( preg_match( '~[\'"](\S+?'.$bucket.'\S+?)[\'"]~', $shortcode, $process) ) {
  				$videos[] = $process[1];
  				break;
  			}
  		}
  	}
  }
  
  if( count($videos) > 0 ) {
  	$videos = array_unique($videos);
  	foreach( $videos AS $video ) {
    	global $post;
    	if( $fv_flowplayer_meta = get_post_meta( $post->ID, '_fv_flowplayer', true ) )  {
    		if( isset($fv_flowplayer_meta[sanitize_title($video)]) ) {
    			continue;
    		}
    	}
    	
  		$video_secured = $fv_fp->get_amazon_secure($video, $fv_fp);
  		fv_wp_flowplayer_check_mimetype( array($video_secured), array( 'meta_action' => 'check_time', 'meta_original' => $video ) );
  	}
  }
}


function fv_wp_flowplayer_closed_meta_boxes( $closed ) {
    if ( false === $closed )
        $closed = array( 'fv_flowplayer_amazon_options', 'fv_flowplayer_interface_options', 'fv_flowplayer_default_options', 'fv_flowplayer_ads', 'fv_flowplayer_skin' );

    return $closed;
}
 

if( !function_exists('http_build_url') ) :
    define('HTTP_URL_REPLACE', 1);          // Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);        // Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);       // Join query strings
    define('HTTP_URL_STRIP_USER', 8);       // Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);      // Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);      // Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);      // Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);     // Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);    // Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512); // Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);     // Strip anything but scheme and host
    
    // Build an URL
    // The parts of the second URL will be merged into the first according to the flags argument. 
    // 
    // @param  mixed      (Part(s) of) an URL in form of a string or associative array like parse_url() returns
    // @param  mixed      Same as the first argument
    // @param  int        A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
    // @param  array      If set, it will be filled with the parts of the composed url like parse_url() would return 
    function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
    {
      $keys = array('user','pass','port','path','query','fragment');
      
      // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
      if ($flags & HTTP_URL_STRIP_ALL)
      {
        $flags |= HTTP_URL_STRIP_USER;
        $flags |= HTTP_URL_STRIP_PASS;
        $flags |= HTTP_URL_STRIP_PORT;
        $flags |= HTTP_URL_STRIP_PATH;
        $flags |= HTTP_URL_STRIP_QUERY;
        $flags |= HTTP_URL_STRIP_FRAGMENT;
      }
      // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
      else if ($flags & HTTP_URL_STRIP_AUTH)
      {
        $flags |= HTTP_URL_STRIP_USER;
        $flags |= HTTP_URL_STRIP_PASS;
      }
      
      // Parse the original URL
      $parse_url = parse_url($url);
      
      // Scheme and Host are always replaced
      if (isset($parts['scheme']))
        $parse_url['scheme'] = $parts['scheme'];
      if (isset($parts['host']))
        $parse_url['host'] = $parts['host'];
      
      // (If applicable) Replace the original URL with it's new parts
      if ($flags & HTTP_URL_REPLACE)
      {
        foreach ($keys as $key)
        {
          if (isset($parts[$key]))
            $parse_url[$key] = $parts[$key];
        }
      }
      else
      {
        // Join the original URL path with the new path
        if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
        {
          if (isset($parse_url['path']))
            $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
          else
            $parse_url['path'] = $parts['path'];
        }
        
        // Join the original query string with the new query string
        if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
        {
          if (isset($parse_url['query']))
            $parse_url['query'] .= '&' . $parts['query'];
          else
            $parse_url['query'] = $parts['query'];
        }
      }
        
      // Strips all the applicable sections of the URL
      // Note: Scheme and Host are never stripped
      foreach ($keys as $key)
      {
        if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
          unset($parse_url[$key]);
      }
      
      
      $new_url = $parse_url;
      
      return 
         ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
        .((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
        .((isset($parse_url['host'])) ? $parse_url['host'] : '')
        .((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
        .((isset($parse_url['path'])) ? $parse_url['path'] : '')
        .((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
        .((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
      ;
    }

endif; 


if( !function_exists('is_utf8') && function_exists('mb_strlen') ) :

	function is_utf8($str) {
		return ( (mb_strlen($str) != strlen($str) ) ? true : false );
	}

endif; 

 
?>