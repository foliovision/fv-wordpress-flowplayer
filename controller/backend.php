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
add_action('admin_head', 'flowplayer_head');
add_action('admin_menu', 'flowplayer_admin');
add_action('media_buttons', 'flowplayer_add_media_button', 30);
add_action('media_upload_fv-wp-flowplayer', 'flowplayer_wizard');
//loading a video and splash image
if(isset($_POST['_wp_http_referer']) && (strpos($_POST['_wp_http_referer'],'fvplayer'))) {
  add_filter('media_send_to_editor','fp_media_send_to_editor', 10, 3);
}
else
//loading a logo
if(isset($_POST['_wp_http_referer']) && (strpos($_POST['_wp_http_referer'],'image'))) {
  add_filter('media_send_to_editor','fp_media_send_to_settings', 10, 3);
}
add_action('the_content', 'flowplayer_content_remove_commas');
add_filter('admin_print_scripts', 'flowplayer_print_scripts');
add_action('admin_print_styles', 'flowplayer_print_styles');
//conversion script via AJAX
add_action('wp_ajax_flowplayer_conversion_script', 'flowplayer_conversion_script');

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
function fp_media_send_to_editor($html, $attachment_id, $attachment) {
  if(isset($_POST['_wp_http_referer']) && (strpos($_POST['_wp_http_referer'],'fvplayer'))) {
    preg_match('/height=([0-9]+([a-z]+))/',$_POST['_wp_http_referer'],$matchesh);
      
    $video_types = array('flv','mov','avi','mpeg','mpg','asf','qt','wmv','mp4','m4v','mp3','webm','ogv');    
    $splash_types = array('jpg','jpeg','gif','png', 'bmp','jpe');
    
    if (isset($attachment_id)) {
      $attachment_url = wp_get_attachment_url($attachment_id);
      $path_parts = pathinfo($attachment_url);
      if (in_array($path_parts['extension'], $splash_types)) {
        setcookie("selected_image",$attachment_url);
        $selected_attachment = array('url'=>$attachment_url,'id'=>$attachment_id);
      }
      else {
        if (strpos($_POST['_wp_http_referer'],'fvplayer1')) {
          setcookie("selected_video1",$attachment_url);
          $selected_attachment = array('id'=>'src1', 'url'=>$attachment_url);
        }
        else
        if (strpos($_POST['_wp_http_referer'],'fvplayer2')) {
          setcookie("selected_video2",$attachment_url);
          $selected_attachment = array('id'=>'src2', 'url'=>$attachment_url);
        }
        else {
          setcookie("selected_video",$attachment_url);
          $selected_attachment = array('id'=>'src', 'url'=>$attachment_url);
        }        
      }
    }
    wp_enqueue_style('media');
    wp_iframe('flowplayer_wizard_function',$selected_attachment);
    die;
  }
}
function fp_media_send_to_settings($html, $attachment_id, $attachment) {
  if(isset($_POST['_wp_http_referer']) && (strpos($_POST['_wp_http_referer'],'image'))) {
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
function flowplayer_wizard() {
  //do the magic here
  setcookie("selected_video",'',time()-3600);
  setcookie("selected_video1",'',time()-3600);
  setcookie("selected_video2",'',time()-3600);
  setcookie("selected_image",'',time()-3600);
	wp_enqueue_style('media');
	wp_iframe('flowplayer_wizard_function','');
}
function flowplayer_wizard_function($selected_attachment) {
  include dirname( __FILE__ ) . '/../view/wizard.php';
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
	$html = '';
	// config file checks, exists, readable, writeable
	$conf_file = realpath(dirname(__FILE__)).'/wpfp.conf';  //Zdenka: I think here should be /../
	if(!file_exists($conf_file)){
		$html .= '<h3 style="font-weight: bold; color: #ff0000">'.$conf_file.' Does not exist please create it</h3>';
	} elseif(!is_readable($conf_file)){
		$html .= '<h3 style="font-weight: bold; color: #ff0000">'.$conf_file.' is not readable please check file permissions</h3>';
	} elseif(!is_writable($conf_file)){
		$html .= '<h3 style="font-weight: bold; color: #ff0000">'.$conf_file.' is not writable please check file permissions</h3>';
	}
//	return $html;  //Zdenka : Why is this not here?
}
function flowplayer_add_media_button() {
  global $post;
	$plugins = get_option('active_plugins');
	$found = false;
	foreach ( $plugins AS $plugin ) {
		if( stripos($plugin,'foliopress-wysiwyg') !== FALSE )
			$found = true;
	}
	if(!$found) 
  {
		$button_tip = 'Insert a Flash Video Player';
		$wizard_url = 'media-upload.php?post_id='.$post->ID.'&type=fv-wp-flowplayer';
		$button_src = RELATIVE_PATH.'/images/icon.png';
		echo '<a title="Add FV WP Flowplayer" href="'.$wizard_url.'&TB_iframe=true&width=500&height=300" class="thickbox" ><img src="' . $button_src . '" alt="' . $button_tip . '" /></a>';
	}
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
  
  echo '<strong>Conversion was succesful. Total number of converted posts: ' . $counter . '</strong>';
  
  die();
} 
?>