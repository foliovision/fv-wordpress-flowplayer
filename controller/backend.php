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
    $headers = get_headers( trim($_POST['media']) );
    if( $headers ) {
      foreach( $headers AS $key => $line ) {
        if( $key == 0 && preg_match( '!HTTP.*?404.*?!', $line ) ) {
        	$message ="<p>Admin note: video not found, please check your video source. You can do so by clicking <a href='".trim($_POST['media'])."' target='_blank'>this link</a> and see if you get to the video file.</p>";
            
          echo json_encode( array( 'not found', $match[1], $message ) );
          die();
        
        } else if( stripos( $line, 'Content-Type:' ) !== FALSE ) {
          if(
          	( !preg_match( '~video/mp4$~', $line ) && stripos( $_POST['media'], '.mp4' ) !== FALSE ) ||
          	( !preg_match( '~video/x-m4v$~', $line ) && stripos( $_POST['media'], '.m4v' ) !== FALSE )
          ) {
            preg_match( '~Content-Type: (\S+)$~', $line, $match );
     
						global $fp;
						if( $fp->conf['engine'] == 'default' ) {
							$admin_note_addition = 'Currently you are using the "Default (mixed)" <a href="'.site_url().'/wp-admin/options-general.php?page=backend.php">Preferred Flowplayer engine</a> setting, so IE will always use Flash and will play fine.';
						}
						preg_match( '!([a-zA-Z0-9-_]{2,4})$!', trim($_POST['media']), $video_format );
						$message ="<p>Admin note: This <abbr='".trim($_POST['media'])."'>".$video_format[1]."</abbr> video has bad mime type of ".$match[1].", so it won't play in HTML5 in IE9 and IE10. Refer to <a href=\"http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq\">Internet Explorer 9 question in our FAQ</a> for fix. ".$admin_note_addition."</p>";
            
            echo json_encode( array( 'bad mime type', $match[1], $message ) );
            die();
          }
        }
      }
    }
    die('1');
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


function fv_wp_flowplayer_check_jquery_version( $url ) {
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
				if( count($jquery_scripts[1]) > 1 ) {
					$errors[] = "It appears there are <strong>multiple</strong> jQuery libraries on your site, your videos might not be playing, please check.\n";
				}
				foreach( $jquery_scripts[1] AS $jquery_script ) {
					$check = fv_wp_flowplayer_check_jquery_version( $jquery_script );
					if( $check == - 1 ) {
						$errors[] = "jQuery library <code>$jquery_script</code> is old version and might not be compatible with Flowplayer.";
					} else if( $check == 1 ) {
						$ok[] = "jQuery library 1.7.1+ found: <code>$jquery_script</code>!";
						$jquery_pos = strpos( $response['body'], $jquery_script );
					} else {
						$errors[] = "jQuery library <code>$jquery_script</code> found, but unable to check version, please make sure it's at least 1.7.1.";
					}
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
 
?>