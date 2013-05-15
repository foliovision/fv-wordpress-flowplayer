<?php 

/**
 * Needed includes
 */
include_once(dirname( __FILE__ ) . '/../models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/../models/flowplayer-backend.php');

/**
 * Create the flowplayer_backend object
 */
$fp = new flowplayer_backend();

/**
 * WP Hooks
 */
add_action('wp_ajax_fv_wp_flowplayer_check_mimetype', 'fv_wp_flowplayer_check_mimetype'); 
add_action('wp_ajax_nopriv_fv_wp_flowplayer_check_mimetype', 'fv_wp_flowplayer_check_mimetype');
 
add_action('admin_head', 'flowplayer_head');
add_action('admin_menu', 'flowplayer_admin');
add_action('media_buttons', 'flowplayer_add_media_button', 30);


add_action('admin_init', 'fv_wp_flowplayer_admin_init');
add_action('media_upload_fvplayer_video', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_video_1', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_video_2', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_splash', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_logo', 'fv_wp_flowplayer_media_upload');


add_action( 'admin_enqueue_scripts', 'fv_wp_flowplayer_admin_enqueue_scripts' );
add_action( 'edit_form_after_editor', 'fv_wp_flowplayer_edit_form_after_editor' );

add_action( 'after_plugin_row', 'fv_wp_flowplayer_after_plugin_row', 10, 3 );


//loading a video and splash image
if(isset($_REQUEST['_wp_http_referer']) && (strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video') || strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video_1') || strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video_2') || strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_splash'))) {
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

if ( !empty($_GET['post_id']) && ($_GET['type'] == 'fvplayer_video' || $_GET['type'] == 'fvplayer_video_1' || $_GET['type'] == 'fvplayer_video_2' || $_GET['type'] == 'fvplayer_splash') ) {  
  add_action( 'post-html-upload-ui', 'fv_wp_flowplayer_image_media_upload_html_bypass', 100 );
}

add_action('the_content', 'flowplayer_content_remove_commas');
add_filter('admin_print_scripts', 'flowplayer_print_scripts');
add_action('admin_print_styles', 'flowplayer_print_styles');
//conversion script via AJAX
add_action('wp_ajax_flowplayer_conversion_script', 'flowplayer_conversion_script');
add_action('admin_notices', 'fv_wp_flowplayer_admin_notice');


function flowplayer_activate() {
  global $wpdb;
  
  $posts = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type != 'revision'");
  
  $old_shorttag = '[flowplayer';
  $found = false;
  
  foreach($posts as $fv_post) {
    if ( stripos( $fv_post->post_content, $old_shorttag ) !== false ) {
      $found = true;
      //exit;
    } 
  }
  
  if ($found) {
    update_option('fvwpflowplayer_conversion', 1);
  }
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
      else {
        $uploaded_video = $selected_attachment['url'];
      }
    }
    if (in_array($path_parts['extension'], $splash_types))
      $uploaded_image = $selected_attachment['url'];
  }                                                 
  
  if (isset($uploaded_video)) {
    $serv = $_SERVER['SERVER_NAME'];
    $pattern = '/'.$serv.'(.*)/';
    preg_match($pattern, $uploaded_video, $matches);
    require_once( plugin_dir_path(__FILE__).'../view/getid3/getid3.php');
    // Initialize getID3 engine                
    $getID3 = new getID3;     
    if (empty($matches)) {
      $ThisFileInfo = $getID3->analyze(realpath($_SERVER['DOCUMENT_ROOT'] . $uploaded_video));
    }
    else { 
      $ThisFileInfo = $getID3->analyze(realpath($_SERVER['DOCUMENT_ROOT'] . $matches[1]));
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
  window.parent.document.getElementById('fv_wp_flowplayer_field_width').value = "<?php echo esc_attr($file_width) ?>";
  window.parent.document.getElementById('fv_wp_flowplayer_field_height').value = "<?php echo esc_attr($file_height) ?>";
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
			8, 
			basename(__FILE__), 
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
	//initialize the class:
  $fp = new flowplayer();
	include dirname( __FILE__ ) . '/../view/admin.php';
}


/**
 * Checks for errors regarding access to configuration file. Displays errors if any occur.
 * @param object $fp Flowplayer class object.
 */
function flowplayer_check_errors($fp) {

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
  $conversion = false; //(bool)get_option('fvwpflowplayer_conversion');
  if ($conversion) {
    echo '<div class="updated" id="fvwpflowplayer_conversion_notice"><p>'; 
    printf(__('FV Wordpress Flowplayer has found old shortcodes in the content of your posts. <a href="%1$s">Run the conversion script.</a>'), get_admin_url() . 'options-general.php?page=backend.php');
    echo "</p></div>";
  }
  
  if( $_GET['page'] == 'backend.php' ) {
	  $options = get_option( 'fvwpflowplayer' );
    if( $options['key'] == 'false' ) {
  		echo '<div class="updated"><p>'; 
    	printf(__('Brand new version of Flowplayer for HTML5. <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy">Licenses half price</a> in May.' ) );
    	echo "</p></div>";
    }
  }
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
  if( $_GET['type'] == 'fvplayer_video' || $_GET['type'] == 'fvplayer_video_1' || $_GET['type'] == 'fvplayer_video_2' ) {
    $_GET['post_mime_type'] = 'video';
  }
  else if( $_GET['type'] == 'fvplayer_splash' || $_GET['type'] == 'fvplayer_logo' ) {
    $_GET['post_mime_type'] = 'image';
  }
  
  if( isset($_POST['fv-wp-flowplayer-submit']) ) {
  	global $fp;
		$fp->_set_conf();
    $fp = new flowplayer();
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
			Brand new version of Flowplayer for HTML5. <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy">Licenses half price</a> in May.
		</div>
	</td>
</tr>
		<?php
		endif;
	}
}
 
 
function fv_wp_flowplayer_check_mimetype() {
  if( isset( $_POST['media'] ) && stripos( $_POST['media'], '.mp4' ) !== FALSE && strpos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {    
    $headers = get_headers( trim($_POST['media']) );
    if( $headers ) {
      foreach( $headers AS $line ) {
        if( stripos( $line, 'Content-Type:' ) !== FALSE ) {
          if( !preg_match( '~video/mp4$~', $line ) ) {
            preg_match( '~Content-Type: (\S+)$~', $line, $match );
            echo json_encode( array( 'bad mime type', $match[1] ) );
            die();
          }
        }
      }
    }
    die('1');
  }
  
  die('-1');
} 
 
?>