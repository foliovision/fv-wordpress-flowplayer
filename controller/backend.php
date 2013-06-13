<?php 

include_once(dirname( __FILE__ ) . '/../models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/../models/flowplayer-backend.php');

/**
 * Create the flowplayer_backend object
 */
$fp = new flowplayer_backend();

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
add_action('media_upload_fvplayer_splash', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_logo', 'fv_wp_flowplayer_media_upload');
add_action('media_upload_fvplayer_subtitles', 'fv_wp_flowplayer_media_upload');


add_action( 'admin_enqueue_scripts', 'fv_wp_flowplayer_admin_enqueue_scripts' );
add_action( 'edit_form_after_editor', 'fv_wp_flowplayer_edit_form_after_editor' );

add_action( 'after_plugin_row', 'fv_wp_flowplayer_after_plugin_row', 10, 3 );


//loading a video and splash image
if(
  isset($_REQUEST['_wp_http_referer']) && 
  (
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video') || 
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video_1') || 
    strpos($_REQUEST['_wp_http_referer'],'type=fvplayer_video_2') || 
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
	update_option( 'fv_wordpress_flowplayer_deferred_notices', 'FV Flowplayer upgraded - please click "Check template" and "Check videos" for automated check of your site at <a href="'.site_url().'/wp-admin/options-general.php?page=backend.php">the settings page</a> for automated checks!' );  
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
  
  if (isset($uploaded_video)) {
    $serv = $_SERVER['SERVER_NAME'];
    $pattern = '/'.$serv.'(.*)/';
    preg_match($pattern, $uploaded_video, $matches);
    require_once( plugin_dir_path(__FILE__).'../includes/getid3/getid3.php');
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
	global $fp;
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
	if( $notices = get_option('fv_wordpress_flowplayer_deferred_notices') ) {
  	echo '<div class="updated">
       			<p>'.$notices.'</p>
    			</div>';    	
  }

  $conversion = false; //(bool)get_option('fvwpflowplayer_conversion');
  if ($conversion) {
    echo '<div class="updated" id="fvwpflowplayer_conversion_notice"><p>'; 
    printf(__('FV Wordpress Flowplayer has found old shortcodes in the content of your posts. <a href="%1$s">Run the conversion script.</a>'), get_admin_url() . 'options-general.php?page=backend.php');
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
		if( $_GET['type'] == 'fvplayer_video' || $_GET['type'] == 'fvplayer_video_1' || $_GET['type'] == 'fvplayer_video_2' ) {
			$_GET['post_mime_type'] = 'video';
		}
		else if( $_GET['type'] == 'fvplayer_splash' || $_GET['type'] == 'fvplayer_logo' ) {
			$_GET['post_mime_type'] = 'image';
		}
  }
  
  if( isset($_POST['fv-wp-flowplayer-submit']) ) {
  	global $fp;
  	if( method_exists($fp,'_set_conf') ) {
			$fp->_set_conf();    
		} else {
			echo 'Error saving FV Flowplayer options.';
		}
	}

  global $fp;
  if( preg_match( '!^\$\d+!', $fp->conf['key'] ) ) {
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
          $fp->conf['key'] = $data->key;
          update_option( 'fvwpflowplayer', $fp->conf );
        }                            
      }            
    }      
  }
  
  global $fv_wp_flowplayer_core_ver;
  update_option( 'fvwpflowplayer_core_ver', $fv_wp_flowplayer_core_ver ); 
  
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
	
	return;
	
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
  if( isset( $_POST['media'] ) && stripos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {    
  	
    global $fp;        
  	$remotefilename = trim( $_POST['media'] );          
    $random = rand( 0, 10000 );
    
    $headers = wp_remote_head( trim( str_replace(' ', '%20', $remotefilename) ), array( 'redirection' => 3 ) );

    $video_errors = array();

    if( !isset($headers['headers']['accept-ranges']) || $headers['headers']['accept-ranges'] != 'bytes' ) {
      $video_errors[] = 'Server does not support HTTP range requests!';  
    }
    
    if( $headers['response']['code'] == '404' ) {
      $video_errors[] = 'File not found (HTTP 404)!';  
    } else if( $headers['response']['code'] == '403' ) {
    	$video_errors[] = 'Access to video forbidden (HTTP 403)!'; 
    } else if( $headers['response']['code'] != '200' ) {
      $video_errors[] = 'Can\'t check the video (HTTP '.$headers['response']['code'].')!'; 
    } else {    
			if(
				( stripos( $remotefilename, '.mp4' ) !== FALSE && $headers['headers']['content-type'] != 'video/mp4' ) ||
				( stripos( $remotefilename, '.m4v' ) !== FALSE && $headers['headers']['content-type'] != 'video/x-m4v' )
			) {
				if( $fp->conf['engine'] == 'default' ) {
					$meta_note_addition = ' Currently you are using the "Default (mixed)" <a href="'.site_url().'/wp-admin/options-general.php?page=backend.php">Preferred Flowplayer engine</a> setting, so IE will always use Flash and will play fine.';
				}     
				
				$fix = '<div class="fix-meta-'.$random.'" style="display: none; ">
					<p>If the video is hosted on Amazon S3:</p>
					<blockquote>Using your Amazon AWS Management Console, you can go though your videos and find file content type under the "Metadata" tab in an object\'s "Properties" pane and fix it to "video/mp4" for MP4 and "video/x-m4v" for M4V files.</blockquote>
					<p>If the video is hosted on your server, put this into your .htaccess:</p>
					<pre>AddType video/mp4             .mp4
AddType video/webm            .webm
AddType video/ogg             .ogv
AddType application/x-mpegurl .m3u8
AddType video/x-m4v           .m4v
# hls transport stream segments:
AddType video/mp2t            .ts</pre>
					<p>If you are using Microsoft IIS, you need to use the IIS manager. Check our <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq" target="_blank">FAQ</a> for more info.</p>
				</div>';     
				
				$video_errors[] = '<p><strong>Bad mime type</strong>: Video served with a bad mime type <tt>'.$headers['headers']['content-type'].'</tt>! Some web browsers may experience playback issues in HTML5 mode (Internet Explorer 9 - 10). '.$meta_note_addition.' (<a href="#" onclick="jQuery(\'.fix-meta-'.$random.'\').toggle(); return false">show fix</a>)</p>'.$fix ;        
			}
    }
    
         	  
		require_once( plugin_dir_path(__FILE__).'../includes/getid3/getid3.php');
		$getID3 = new getID3;     
    
    preg_match( '~^\S+://([^/]+)~', $remotefilename, $remote_domain );
    preg_match( '~^\S+://([^/]+)~', home_url(), $site_domain ); 
            
    if( strlen($remote_domain[1]) > 0 && strlen($site_domain[1]) > 0 && $remote_domain[1] != $site_domain[1] ) {
      $message = '<p>Analysis of <tt>'.$remotefilename.'</tt> (remote):</p>';
    		
  		//	taken from: http://www.getid3.org/phpBB3/viewtopic.php?f=3&t=1141
      $upload_dir = wp_upload_dir();
  		$localtempfilename = trailingslashit( $upload_dir['basedir'] ).'fv_flowlayer_tmp_'.basename($remotefilename);
  		$out = fopen( $localtempfilename,'wb' );
  		if( $out ) {
    		$ch = curl_init();
    		curl_setopt( $ch, CURLOPT_URL, $remotefilename );    		
    		curl_setopt( $ch, CURLOPT_RANGE, '0-2097152' );
    		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    		
    		$data = curl_exec($ch);
    		file_put_contents( $localtempfilename, $data);
    		if($ch == false) {
    			$message .= 'CURL Error: '.curl_error ( $ch);
    		}
    		curl_close($ch);
    
    		$ThisFileInfo = $getID3->analyze( $localtempfilename );
        
        if( !unlink($localtempfilename) ) {
          $video_errors[] = 'Can\'t remove temporary file for video analysis in <tt>'.$localtempfilename.'</tt>!';
        }         
      } else {
        $video_errors[] = 'Can\'t create temporary file for video analysis in <tt>'.$localtempfilename.'</tt>!';
      }                  
    } else {
      $message = '<p>Analysis of <tt>'.$remotefilename.'</tt> (local):</p>';
      $localtempfilename = preg_replace( '~^\S+://[^/]+~', trailingslashit($_SERVER['DOCUMENT_ROOT']), $remotefilename );

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
				$video_errors[] = 'Video meta data (moov-atom) not found at the start of the file! Please move the meta data to the start of video, otherwise it might have a slow start up time.';
			} else {
        if( $ThisFileInfo['quicktime']['moov']['offset'] > 1024 ) {
          $video_errors[]  = 'Meta Data (moov) not found at the start of the file (found at '. number_format( $ThisFileInfo['quicktime']['moov']['offset'] ).' byte)! Please move the meta data to the start of video, otherwise it might have a slow start up time.';
        } else {
				  $video_info['Meta Data (moov) position']  = $ThisFileInfo['quicktime']['moov']['offset'];		
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
        if( $fp->conf['engine'] == 'default' ) {
  				$m4v_note_addition = ' Currently you are using the "Default (mixed)" <a href="'.site_url().'/wp-admin/options-general.php?page=backend.php">Preferred Flowplayer engine</a> setting, so Firefox on Windows will always use Flash for M4V and will play fine.';
  			} 
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
		
		if( $video_errors ) {
			foreach( $video_errors AS $key => $item ) {			
				if( preg_match( '!Atom at offset \d+ claims to go beyond end-of-file!', $item ) ) {
					unset( $video_errors[$key] );	//	we are not interested in this particular warning, as we don't download the full file
				}
			}
		}
			
		if( $video_errors ) {							
			foreach( $video_errors AS $key => $item ) {					        
        if( $item && stripos( $item, '</p>' ) === false ) {
          $item = '<p><strong>Error</strong>: '.$item.'</p>';
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
				
//var_dump( $ThisFileInfo );		
		
    if( $ThisFileInfo ) {
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
    
		  $message .= '<div class="support-'.$random.'">';
		  $message .= '<textarea id="wpfp_support_'.$_POST['hash'].'" onclick="if( this.value == \'Enter your comment\' ) this.value = \'\'" style="width: 100%; height: 150px">Enter your comment</textarea>';
		  $message .= '<p><input type="button" onclick="fv_wp_flowplayer_support_mail(\''.trim($_POST['hash']).'\', this); return false" value="Send report to Foliovision" /><img id="wpfp_spin_'.$_POST['hash'].'" src="'.site_url().'/wp-includes/images/wpspin.gif" style="display: none; " /> <a class="techinfo" href="#" onclick="jQuery(\'.more-'.$random.'\').toggle(); return false">Technical info</a></p>';
		  $message .= '</div>';
		  $message .= '<div class="more-'.$random.' mail-content-details" style="display: none; ">'.$new_info.'</div>';
    }		
    
    if( count($video_errors ) == 0 && $fp->conf['videochecker'] == 'errors' ) {
      die();
    }
    
    $issues_text = ( count($video_errors) > 0 ) ? '<span style="color: red; ">Video Issues</span>' : '<span style="color: green; ">Video OK</span>';
    $message = "<small>Admin: <a href='#' onclick='fv_wp_flowplayer_show_notice($random, this); return false'>$issues_text</a></small><div id='fv_wp_fp_notice_$random' style='display: none;'>$message</div>\n";
      
    $json = @json_encode( array( $message, count( $video_errors ) ) );
    $last_error = ( function_exists('json_last_error') ) ? json_last_error() : true;
    
    if( $last_error ) {
    	if( function_exists('mb_check_encoding') && function_exists('utf8_encode') ) {
        	if(!mb_check_encoding($message, 'UTF-8')) {
          		$message = utf8_encode($message);
        	}
      	} else {
        	$message = htmlentities( $message, ENT_QUOTES, 'utf-8', FALSE);
        	$message = ( $message ) ? $message : 'Admin: Error parsing JSON';
      	}           
      
    	$json = json_encode( array( $message, count( $video_errors ) ) );
    	$last_error = ( function_exists('json_last_error') ) ? json_last_error() : false;
    	if( $last_error ) {
    		echo json_encode( array( 'Admin: JSON error: '.$last_error, count( $video_errors ) ) );    
    	} else {
				echo $json;
			}
    } else {
    	echo $json;
    }
		die();
  }
  
  die('-1');
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

  		$headers = get_headers( trim($videos[0]['src']) );
			if( $headers ) {

				$posts_links = '';
				foreach( $videos AS $video ) {
					$posts_links .= '<a href="'.home_url().'?p='.$video['post_id'].'">'.$video['post_id'].'</a> ';	
				}

				foreach( $headers AS $line ) {
					if( stripos( $line, 'Content-Type:' ) !== FALSE ) {
						preg_match( '~Content-Type: (\S+)$~', $line, $match );
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
						
							$errors[] = 'Server <code>'.$server.'</code> uses bad mime type <code>'.$match[1].'</code> for MP4 or M4V videos! (<a href="#" onclick="jQuery(\'#fv-flowplayer-warning-'.$count.'\').toggle(); return false">click to see a list of posts</a>) (<a href="#" onclick="jQuery(\'#fv-flowplayer-info-'.$count.'\').toggle(); return false">show fix</a>) <div id="fv-flowplayer-warning-'.$count.'" style="display: none; ">'.$posts_links.'</div> <div id="fv-flowplayer-info-'.$count.'" style="display: none; ">'.$fix.'</div>'; 
						} else {
							$ok[] = 'Server <code>'.$server.'</code> appears to serve correct mime type <code>'.$match[1].'</code> for MP4 and M4V videos.'; 						
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
  if( stripos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {    
  	$response = wp_remote_get( home_url() );
  	if( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$output = array( 'error' => $error_message );
		} else {		
			
			$active_plugins = get_option( 'active_plugins' );
			foreach( $active_plugins AS $plugin ) {
				if( stripos( $plugin, 'wp-minify' ) !== false ) {
					$errors[] = "You are using <strong>WP Minify</strong>, so the script checks would not be accurate. Please check your videos manually.";
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
    
    $headers = "Reply-To: \"$current_user->display_name\" <$current_user->user_email>\r\n";
  	
  	add_filter( 'wp_mail_content_type', create_function('', "return 'text/html';") );
  	
  	wp_mail( 'fvplayer@foliovision.com', 'FV Flowplayer Quick Support Submission', $content, $headers );
  	
  	die('1');
  }
}
 
?>