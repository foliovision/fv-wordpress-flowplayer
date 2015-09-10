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
 * Displays administrator backend.
 */
 
delete_option('fv_wordpress_flowplayer_deferred_notices');


function fv_flowplayer_admin_ads() {
	global $fv_fp;
?>
					<table class="form-table2">	
						<tr>
							<td colspan="2">
								<label for="ad"><?php _e('Default Ad Code', 'fv_flowplayer'); ?>:</label><br />
								<textarea id="ad" name="ad" class="large-text code"><?php if( isset($fv_fp->conf['ad']) ) echo esc_textarea($fv_fp->conf['ad']); ?></textarea>			
							</td>
						</tr>
						<tr>
							<td colspan="2"><label for="width"><?php _e('Default set size', 'fv_flowplayer');?> [px]:</label> <label for="ad_width">W:</label>&nbsp;<input type="text" name="ad_width" id="ad_width" value="<?php echo intval($fv_fp->conf['ad_width']); ?>" class="small" /> <label for="ad_height">H:</label>&nbsp;<input type="text" name="ad_height" id="ad_height" value="<?php echo intval($fv_fp->conf['ad_height']); ?>" class="small"  /> <label for="adTextColor">Ad text</label> <input class="color small" type="text" name="adTextColor" id="adTextColor" value="<?php echo esc_attr($fv_fp->conf['adTextColor']); ?>" /> <label for="adLinksColor">Ad links</label> <input class="color small" type="text" name="adLinksColor" id="adLinksColor" value="<?php echo esc_attr($fv_fp->conf['adLinksColor']); ?>" /> </td>			
						</tr>           
						<tr>
							<td colspan="2">
								<label for="width">Ad CSS:</label>
								<a href="#" onclick="jQuery('.ad_css_wrap').show(); jQuery(this).hide(); return false">Show styling options</a>
								<div class="ad_css_wrap" style="display: none; ">
									<select id="ad_css_select">
										<option value="">Select your preset</option>
										<option value="<?php echo esc_attr($fv_fp->ad_css_default); ?>"<?php if( strcmp( preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->ad_css_default), preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->conf['ad_css'])) == 0 ) echo ' selected="selected"'; ?>>Default (white, centered above the control bar)</option>
										<option value="<?php echo esc_attr($fv_fp->ad_css_bottom); ?>"<?php if( strcmp( preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->ad_css_bottom), preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->conf['ad_css']))  == 0 ) echo ' selected="selected"'; ?>>White, centered at the bottom of the video</option>					  		
									</select>
									<br />
									<textarea rows="5" name="ad_css" id="ad_css" class="large-text code"><?php if( isset($fv_fp->conf['ad_css']) ) echo esc_textarea($fv_fp->conf['ad_css']); ?></textarea>
									<p class="description">(Hint: put .wpfp_custom_ad_content before your own CSS selectors)</p>
									<script type="text/javascript">
									jQuery('#ad_css_select').change( function() {
										if( jQuery('#ad_css_select option:selected').val().length > 0 && jQuery('#ad_css_select option:selected').val() != jQuery('#ad_css').val() && confirm('Are you sure you want to apply the preset?') ) {
											jQuery('#ad_css').val( jQuery('#ad_css_select option:selected').val() );	
										}									
									} );
									</script>
								</div>
							</td>
						</tr>			
						<tr>    		
							<td colspan="4">
								<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" />
							</td>
						</tr>							
					</table>
<?php
}


function fv_flowplayer_admin_amazon_options() {
	global $fv_fp;
  
  //$config = w3_instance('W3_Config');
  //var_dump($config->get_boolean('pgcache.reject.logged') );
  
  /*if( function_exists('w3_instance') && class_exists('W3_Config') ) {
    $config = w3_instance('W3_Config');
  }
  
  $message = '';
  if( is_plugin_active('w3-total-cache/w3-total-cache.php')  && ( $config instanceof W3_Config ) && !$config->get_boolean('pgcache.reject.logged') ) {
    $message = 'W3 Total Cache appears to be enabled, please turn on <code> Performance -> Page Cache -> "Don\'t cache pages for logged in users"</code>. ';
  } else if( is_plugin_active('w3-total-cache/w3-total-cache.php') ) {
    $message = 'W3 Total Cache appears to be enabled, please make sure that <code> Performance -> Page Cache -> "Don\'t cache pages for logged in users" </code> is on.';
  } else if( is_plugin_active('wp-super-cache/wp-cache.php') ) {
    $message = 'WP Super Cache';
  }
  
  $message .= ' Otherwise Amazon S3 protected content might be not loading for your members.';
  
  var_dump($message);*/
?>
				<table class="form-table2">
					<tr>
						<td colspan="2">
							<p>Secured Amazon S3 URLs are recommended for member-only sections of the site. We check the video length and make sure the link expiration time is big enough for the video to buffer properly</p>
              <p>If you use a cache plugin (such as Hyper Cache, WP Super Cache or W3 Total Cache), we recommend that you set the "Default Expire Time" to twice as much as your cache timeout and check "Force the default expiration time". That way the video length won't be accounted and the video source URLs in your cached pages won't expire. Read more in the <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/secure-amazon-s3-guide#wp-cache" target="_blank">Using Amazon S3 secure content in FV Flowplayer guide</a>.</p>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="amazon_expire">Default Expire Time [minutes] (<abbr title="Each video duration is stored on post save and then used as the expire time. If the duration is not available, this value is used.">?</abbr>):</label></td>
						<td>
              <input type="text" size="40" name="amazon_expire" id="amazon_expire" value="<?php echo intval($fv_fp->conf['amazon_expire']); ?>" />            
            </td>
					</tr>
					<tr>
						<td class="first"><label for="amazon_expire_force">Force the default expiration time:</label></td>
						<td>             
              <?php fv_flowplayer_admin_checkbox('amazon_expire_force'); ?>              
            </td>
					</tr>		          
<?php
			if( !isset($fv_fp->conf['amazon_bucket']) ) {
				$fv_fp->conf['amazon_bucket'] = array('');
				$fv_fp->conf['amazon_key'] = array('');
				$fv_fp->conf['amazon_secret'] = array('');				
			}
			$count = 0;
			foreach( $fv_fp->conf['amazon_bucket'] AS $key => $item ) :
				$count++;
				$amazon_tr_class = ($count==1) ? ' class="amazon-s3-first"' : ' class="amazon-s3-'.$count.'"';
        $sRegion = ( isset($fv_fp->conf['amazon_region'][$key]) ) ? $fv_fp->conf['amazon_region'][$key] : false;
?>					
					<tr<?php echo $amazon_tr_class; ?>>
						<td><label for="amazon_bucket[]">Amazon Bucket (<abbr title="We recommend that you simply put all of your protected video into a single bucket and enter its name here. All matching videos will use the protected URLs.">?</abbr>):</label></td>
						<td><input id="amazon_bucket[]" name="amazon_bucket[]" type="text" value="<?php echo esc_attr($item); ?>" /></td>
					</tr>
					<tr<?php echo $amazon_tr_class; ?>>
						<td><label for="amazon_region[]">Region</td>
						<td>
              <select id="amazon_region[]" name="amazon_region[]">
                <option value="">Select the region</option>
                <option value="eu-central-1"<?php if( $sRegion == 'eu-central-1' ) echo " selected"; ?>>Frankfurt</option>
                <option value="eu-west-1"<?php if( $sRegion == 'eu-west-1' ) echo " selected"; ?>>Ireland</option>                              
                <option value="us-west-1"<?php if( $sRegion == 'us-west-1' ) echo " selected"; ?>>Northern California</option>
                <option value="us-west-2"<?php if( $sRegion == 'us-west-2' ) echo " selected"; ?>>Oregon</option>
                <option value="sa-east-1"<?php if( $sRegion == 'sa-east-1' ) echo " selected"; ?>>Sao Paulo</option>          
                <option value="ap-southeast-1"<?php if( $sRegion == 'ap-southeast-1' ) echo " selected"; ?>>Singapore</option>
                <option value="ap-southeast-2"<?php if( $sRegion == 'ap-southeast-2' ) echo " selected"; ?>>Sydney</option>
                <option value="ap-northeast-1"<?php if( $sRegion == 'ap-northeast-1' ) echo " selected"; ?>>Tokyo</option>
                <option value="us-east-1"<?php if( $sRegion == 'us-east-1' ) echo " selected"; ?>>US Standard</option>      
              </select>
            </td>
					</tr>			          
					<tr<?php echo $amazon_tr_class; ?>>
						<td><label for="amazon_key[]">Access Key ID:</label></td>
						<td><input id="amazon_key[]" name="amazon_key[]" type="text" value="<?php echo esc_attr($fv_fp->conf['amazon_key'][$key]); ?>" /></td>
					</tr>	
					<tr<?php echo $amazon_tr_class; ?>>
						<td><label for="amazon_secret[]">Secret Access Key:</label></td>
						<td><input id="amazon_secret[]" name="amazon_secret[]" type="text" value="<?php echo esc_attr($fv_fp->conf['amazon_secret'][$key]); ?>" /></td>
					</tr>
					<tr<?php echo $amazon_tr_class; ?>>
						<td colspan="2">
							<div class="alignright fv_fp_amazon_remove"><a href="#" onclick="fv_fp_amazon_s3_remove(this); return false">remove</a></div><div class="clear"></div>
							<hr style="border: 0; border-top: 1px solid #ccc;" />
						</td>
					</tr>						
<?php
			endforeach;
?>							
					<tr class="amazon-s3-last"><td colspan="2"></td></tr>	
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" />
							<input type="button" id="amazon-s3-add" class="button" value="Add more Amazon S3 secure buckets" />
						</td>
					</tr>   					                                 
				</table>
<?php 
}


function fv_flowplayer_admin_default_options() {
	global $fv_fp;
?>
					<table class="form-table2">
						<tr>
							<td class="first"><label for="autoplay">AutoPlay (<abbr title="We make sure only one video per page autoplays">?</abbr>):</label></td>
							<td colspan="2">
								<?php fv_flowplayer_admin_checkbox('autoplay'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="auto_buffering">Auto Buffering (<abbr title="Works for first 2 videos on the page only, to preserve your bandwidth.">?</abbr>):</label></td>
							<td colspan="2">
								<?php fv_flowplayer_admin_checkbox('auto_buffering'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="popupbox">Popup Box:</label></td>
							<td colspan="2">
								<?php fv_flowplayer_admin_checkbox('popupbox'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="scaling">Fit scaling (<abbr title="If set to true, the original aspect ratio of the video will be used to display the video in fullscreen mode as well as when embedded in the page.">?</abbr>):</label></td>
							<td colspan="2">
								<?php fv_flowplayer_admin_checkbox('scaling'); ?>
							</td>
						</tr>           
						<tr>
							<td><label for="postthumbnail">Enable Post Thumbnail (<abbr title="When you set a splash screen from the media library, it will automatically become the splash image if there is none.">?</abbr>):</label></td>
							<td colspan="2">
								<?php fv_flowplayer_admin_checkbox('postthumbnail'); ?>
							</td>
						</tr>    	
						<tr>
							<td><label for="parse_commas">Convert old shortcodes with commas (<abbr title="Older versions of this plugin used commas to sepparate shortcode parameters. This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.">?</abbr>):</label></td>
							<td colspan="2">
								<?php fv_flowplayer_admin_checkbox('parse_commas'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="engine">Prefer Flash player by default (<abbr title="Default setting is off - IE9 and IE10 get Flash (due to server compatibility issues), Firefox in Windows gets Flash for M4V files (due to issues with M4V in it on PC), everyone else gets HTML5 (with Flash fallback)">?</abbr>):</label></td>
							<td colspan="2">
								<?php fv_flowplayer_admin_checkbox('engine'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="fixed_size">Always use fixed size player (<abbr title="Default setting - respects width and height setting of the video, but allows it to size down to be responsive">?</abbr>):</label></td>
							<td colspan="2"> 					
								<?php fv_flowplayer_admin_checkbox('fixed_size'); ?>					
							</td>
						</tr>
						<tr>
							<td><label for="disable_videochecker">Disable admin video checker</label></td>
							<td colspan="2"> 					
								<?php fv_flowplayer_admin_checkbox('disable_videochecker'); ?>			
							</td>
						</tr>    
						<tr>
							<td><label for="width">Default video size [px]:</label></td>
							<td colspan="2"> 					
								<label for="width">W:</label>&nbsp;<input type="text" class="small" name="width" id="width" value="<?php echo intval($fv_fp->conf['width']); ?>" />  
								<label for="height">H:</label>&nbsp;<input type="text" class="small" name="height" id="height" value="<?php echo intval($fv_fp->conf['height']); ?>" />							
							</td>
						</tr>						
						<tr>
							<td><label for="googleanalytics">Google Analytics ID:</label></td>
							<td colspan="3"><input type="text" name="googleanalytics" id="googleanalytics" value="<?php echo esc_attr($fv_fp->conf['googleanalytics']); ?>" /></td>
						</tr>
						<tr>
							<td><label for="logo">Logo:</label></td>
							<td><input type="text"  name="logo" id="logo" value="<?php echo esc_attr($fv_fp->conf['logo']); ?>" /></td>
              <td style="width: 5%"><input id="upload_image_button" class="upload_image_button button no-margin" type="button" value="Upload Image" alt="Select Logo" /></td>
              <td style="width: 5%">
                <select name="logoPosition">
                  <option value="bottom-left">Position</option>
                  <option <?php if( !isset($fv_fp->conf['logoPosition']) || $fv_fp->conf['logoPosition'] == 'bottom-left' ) echo "selected"; ?> value="bottom-left">Bottom-left</option>
                  <option <?php if( isset($fv_fp->conf['logoPosition']) && $fv_fp->conf['logoPosition'] == 'bottom-right' ) echo "selected"; ?> value="bottom-right">Bottom-right</option>
                  <option <?php if( isset($fv_fp->conf['logoPosition']) && $fv_fp->conf['logoPosition'] == 'top-left' ) echo "selected"; ?> value="top-left">Top-left</option>
                  <option <?php if( isset($fv_fp->conf['logoPosition']) && $fv_fp->conf['logoPosition'] == 'top-right' ) echo "selected"; ?> value="top-right">Top-right</option>
                </select>
              </td>
						</tr>
						<tr>
							<td><label for="logo">Splash Image (<abbr title="Default which will be used for any player without its own splash image">?</abbr>):</label></td>
							<td colspan="2"><input type="text"  name="splash" id="splash" value="<?php if( isset($fv_fp->conf['splash']) ) echo esc_attr($fv_fp->conf['splash']); ?>" /></td>
              <td style="width: 5%"><input id="upload_image_button" class="upload_image_button button no-margin" type="button" value="Upload Image" alt="Select default Splash Screen" /></td>
						</tr>
						<tr>
							<td><label for="rtmp">Flash streaming server<br />(Amazon CloudFront domain) (<abbr title="Enter your default RTMP streaming server here">?</abbr>):</label></td>
							<td colspan="3"><input type="text" name="rtmp" id="rtmp" value="<?php echo esc_attr($fv_fp->conf['rtmp']); ?>" /></td>
						</tr>				
						<tr>    		
							<td colspan="4">
								<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" />
							</td>
						</tr>						
					</table>
<script>
jQuery(document).ready(function($) {    
  var fv_flowplayer_uploader;
  var fv_flowplayer_uploader_button;

  $(document).on( 'click', '.upload_image_button', function(e) {
      e.preventDefault();
      
      fv_flowplayer_uploader_button = jQuery(this);
      jQuery('.fv_flowplayer_target').removeClass('fv_flowplayer_target' );
      fv_flowplayer_uploader_button.parents('tr').find('input[type=text]').addClass('fv_flowplayer_target' );
                       
      //If the uploader object has already been created, reopen the dialog
      if (fv_flowplayer_uploader) {
          fv_flowplayer_uploader.open();
          return;
      }

      //Extend the wp.media object
      fv_flowplayer_uploader = wp.media.frames.file_frame = wp.media({
          title: 'Pick the image',
          button: {
              text: 'Choose'
          },
          multiple: false
      });
      
      fv_flowplayer_uploader.on('open', function() {
        jQuery('.media-frame-title h1').text(fv_flowplayer_uploader_button.attr('alt'));
      });      

      //When a file is selected, grab the URL and set it as the text field's value
      fv_flowplayer_uploader.on('select', function() {
          attachment = fv_flowplayer_uploader.state().get('selection').first().toJSON();

          $('.fv_flowplayer_target').val(attachment.url);
          $('.fv_flowplayer_target').removeClass('fv_flowplayer_target' );
        
          /*if( attachment.type == 'video' ) {
            if( typeof(attachment.width) != "undefined" && attachment.width > 0 ) {
              $('#fv_wp_flowplayer_field_width').val(attachment.width);
            }
            if( typeof(attachment.height) != "undefined" && attachment.height > 0 ) {
              $('#fv_wp_flowplayer_field_height').val(attachment.height);
            }
            if( typeof(attachment.fileLength) != "undefined" ) {
              $('#fv_wp_flowplayer_file_info').show();
              $('#fv_wp_flowplayer_file_duration').html(attachment.fileLength);
            }
            if( typeof(attachment.filesizeHumanReadable) != "undefined" ) {
              $('#fv_wp_flowplayer_file_info').show();
              $('#fv_wp_flowplayer_file_size').html(attachment.filesizeHumanReadable);
            }
            
          } else if( attachment.type == 'image' && typeof(fv_flowplayer_set_post_thumbnail_id) != "undefined" ) {
            if( jQuery('#remove-post-thumbnail').length > 0 ){
              return;
            }
            jQuery.post(ajaxurl, {
                action:"set-post-thumbnail",
                post_id: fv_flowplayer_set_post_thumbnail_id,
                thumbnail_id: attachment.id,
                 _ajax_nonce: fv_flowplayer_set_post_thumbnail_nonce,
                cookie: encodeURIComponent(document.cookie)
              }, function(str){
                var win = window.dialogArguments || opener || parent || top;
                if ( str == '0' ) {
                  alert( setPostThumbnailL10n.error );
                } else {
                  jQuery('#postimagediv .inside').html(str);
                  jQuery('#postimagediv .inside #plupload-upload-ui').hide();
                }
              } );
            
          }*/
          
      });

      //Open the uploader dialog
      fv_flowplayer_uploader.open();

  });    
 
});  
</script>          
					<div class="clear"></div>
<?php
}


function fv_flowplayer_admin_description() {
?>
				<table class="form-table">
					<tr>
						<td colspan="4">
							<p>FV WordPress Flowplayer WordPress plugin is a free, easy-to-use, and complete solution for embedding <strong>MP4</strong>, <strong>WEBM</strong>, <strong>OGV</strong>, <strong>MOV</strong> and <strong>FLV</strong>. videos into your posts or pages. With MP4 videos, FV WordPress Flowplayer offers 98% coverage even on mobile devices.</p>
						</td>
					</tr>
				</table>
<?php
}


function fv_flowplayer_admin_integrations() {
	global $fv_fp;
?>
        <p>Following options are suitable for web developers and programmers.</p>
				<table class="form-table2">
          <tr>
						<td class="first"><label for="cbox_compatibility">Colorbox Compatibility (<abbr title="Use if your theme is using colorbox lightbox to show content and clones the HTML content into it.">?</abbr>):</label></td>
						<td>
              <input type="hidden" name="cbox_compatibility" value="false" />
              <input type="checkbox" name="cbox_compatibility" id="cbox_compatibility" value="true" <?php if( isset($fv_fp->conf['cbox_compatibility']) && $fv_fp->conf['cbox_compatibility'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>          
          <tr>
						<td class="first"><label for="js-everywhere">Load FV Flowplayer JS everywhere (<abbr title="If you use some special JavaScript integration, you might prefer this option, otherwise it loads only if the shortcode is found.">?</abbr>):</label></td>
						<td>
              <input type="hidden" name="js-everywhere" value="false" />
              <input type="checkbox" name="js-everywhere" id="js-everywhere" value="true" <?php if( isset($fv_fp->conf['js-everywhere']) && $fv_fp->conf['js-everywhere'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
          <tr>
						<td class="first"><label for="db_duration">Scan video length (<abbr title="Beta version! Turn on to enable video duration scanning. Turn off if you experience issues when saving posts.">?</abbr>):</label></td>
						<td>
              <input type="hidden" name="db_duration" value="false" />
              <input type="checkbox" name="db_duration" id="db_duration" value="true" <?php if( isset($fv_fp->conf['db_duration']) && $fv_fp->conf['db_duration'] == 'true' ) echo 'checked="checked"'; ?> />
              <?php
              global $wpdb;
              $iCount = $wpdb->get_var( "SELECT count(meta_id) FROM $wpdb->postmeta WHERE meta_key LIKE '_fv_flowplayer_%'" );
              $iQueue = count(FV_Player_Checker::queue_get());
              if( $iQueue && $aQueue = FV_Player_Checker::queue_get() ) {
                $htmlQueue = "<a href='#' onclick='jQuery(this).siblings(\"span\").toggle(); return false'>$iQueue</a> <span style='display: none'>(";
                foreach( $aQueue as $k => $i ) {
                  $htmlQueue .= "<a href='".get_edit_post_link($k)."'>$k</a> ";
                }
                $htmlQueue .= ") <a href='".site_url()."/wp-admin/options-general.php?page=fvplayer&fv_flowplayer_checker'>Scan now!</a></span>";
              }
              if( $iCount && $iQueue ) {
                echo "Currently $iCount videos in database and $htmlQueue posts in queue.";
              } else if( $iCount ) {
                echo "Currently $iCount videos in database.";
              } else if( $iQueue ) {
                echo "Currently $htmlQueue posts in queue.";
              }
              ?>
						</td>
					</tr>
          <tr>
						<td class="first"><label for="css_disable">Disable saving of color settings into a static file (<abbr title="Normally the player CSS configuration is stored in wp-content/fv-player-custom/style-{blog_id}.css, you can disable this here.">?</abbr>):</label></td>
						<td>
              <input type="hidden" name="css_disable" value="false" />
              <input type="checkbox" name="css_disable" id="css_disable" value="true" <?php if( isset($fv_fp->conf['css_disable']) && $fv_fp->conf['css_disable'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>           
					<!--<tr>
						<td style="width: 350px"><label for="optimizepress2">Handle OptimizePress 2 videos (<abbr title="Following attributes are not currently supported: margin, border">?</abbr>):</label></td>
						<td>
              <input type="hidden" name="integrations[optimizepress2]" value="false" />
              <input type="checkbox" name="integrations[optimizepress2]" id="optimizepress2" value="true" <?php if( isset($fv_fp->conf['integrations']['optimizepress2']) && $fv_fp->conf['integrations']['optimizepress2'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>-->
					<tr>
						<td><label for="wp_core_video">Handle Wordpress <code><small>[video]</small></code> shortcodes:</label></td>
						<td>
              <input type="hidden" name="integrations[wp_core_video]" value="false" />
              <input type="checkbox" name="integrations[wp_core_video]" id="wp_core_video" value="true" <?php if( isset($fv_fp->conf['integrations']['wp_core_video']) && $fv_fp->conf['integrations']['wp_core_video'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>           
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" />
						</td>
					</tr>                               
				</table>
<?php
}


function fv_flowplayer_admin_interface_options() {
	global $fv_fp;
?>
				<p>Which features should be available in shortcode editor?</p>
				<table class="form-table2">
					<tr>
						<td class="first"><label for="allowuploads">Allow User Uploads:</label></td>
						<td>
              <input type="hidden" name="allowuploads" value="false" />
              <input type="checkbox" name="allowuploads" id="allowuploads" value="true" <?php if( isset($fv_fp->conf['allowuploads']) && $fv_fp->conf['allowuploads'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   
					<tr>          
						<td><label for="interface[playlist]">Playlist:</label></td>
						<td>
              <input type="hidden" name="interface[playlist]" value="false" />
							<input type="checkbox" name="interface[playlist]" id="interface[playlist]" value="true" <?php if( isset($fv_fp->conf['interface']['playlist']) && $fv_fp->conf['interface']['playlist'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[playlist]">Playlist captions:</label></td>
						<td>
              <input type="hidden" name="interface[playlist_captions]" value="false" />
							<input type="checkbox" name="interface[playlist_captions]" id="interface[playlist_captions]" value="true" <?php if( isset($fv_fp->conf['interface']['playlist_captions']) && $fv_fp->conf['interface']['playlist_captions'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>    		          
					<tr>          
						<td><label for="interface[popup]">HTML popup:</label></td>
						<td>
              <input type="hidden" name="interface[popup]" value="false" />
							<input type="checkbox" name="interface[popup]" id="interface[popup]" value="true" <?php if( isset($fv_fp->conf['interface']['popup']) && $fv_fp->conf['interface']['popup'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>    
					<tr>          
						<td><label for="interface[redirect]">Redirect:</label></td>
						<td>
              <input type="hidden" name="interface[redirect]" value="false" />
							<input type="checkbox" name="interface[redirect]" id="interface[redirect]" value="true" <?php if( isset($fv_fp->conf['interface']['redirect']) && $fv_fp->conf['interface']['redirect'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>                        
					<tr>          
						<td class="first"><label for="interface[autoplay]">AutoPlay:</label></td>
						<td>
              <input type="hidden" name="interface[autoplay]" value="false" />
							<input type="checkbox" name="interface[autoplay]" id="interface[autoplay]" value="true" <?php if( isset($fv_fp->conf['interface']['autoplay']) && $fv_fp->conf['interface']['autoplay'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[loop]">Loop:</label></td>
						<td>
              <input type="hidden" name="interface[loop]" value="false" />
							<input type="checkbox" name="interface[loop]" id="interface[loop]" value="true" <?php if( isset($fv_fp->conf['interface']['loop']) && $fv_fp->conf['interface']['loop'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[splashend]">Splash end:</label></td>
						<td>
              <input type="hidden" name="interface[splashend]" value="false" />
							<input type="checkbox" name="interface[splashend]" id="interface[splashend]" value="true" <?php if( isset($fv_fp->conf['interface']['splashend']) && $fv_fp->conf['interface']['splashend'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>     
					<tr>          
						<td><label for="interface[embed]">Embed:</label></td>
						<td>
              <input type="hidden" name="interface[embed]" value="false" />
							<input type="checkbox" name="interface[embed]" id="interface[embed]" value="true" <?php if( isset($fv_fp->conf['interface']['embed']) && $fv_fp->conf['interface']['embed'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>    
					<tr>          
						<td><label for="interface[subtitles]">Subtitles:</label></td>
						<td>
              <input type="hidden" name="interface[subtitles]" value="false" />
							<input type="checkbox" name="interface[subtitles]" id="interface[subtitles]" value="true" <?php if( isset($fv_fp->conf['interface']['subtitles']) && $fv_fp->conf['interface']['subtitles'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>  
					<tr>          
						<td><label for="interface[ads]">Ads:</label></td>
						<td>
              <input type="hidden" name="interface[ads]" value="false" />
							<input type="checkbox" name="interface[ads]" id="interface[ads]" value="true" <?php if( isset($fv_fp->conf['interface']['ads']) && $fv_fp->conf['interface']['ads'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   	
					<tr>          
						<td><label for="interface[mobile]">Mobile video:</label></td>
						<td>
              <input type="hidden" name="interface[mobile]" value="false" />
							<input type="checkbox" name="interface[mobile]" id="interface[mobile]" value="true" <?php if( isset($fv_fp->conf['interface']['mobile']) && $fv_fp->conf['interface']['mobile'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   		
					<tr>          
						<td><label for="interface[align]">Align:</label></td>
						<td>
              <input type="hidden" name="interface[align]" value="false" />
							<input type="checkbox" name="interface[align]" id="interface[align]" value="true" <?php if( isset($fv_fp->conf['interface']['align']) && $fv_fp->conf['interface']['align'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[controlbar]">Controlbar: </label></td>
						<td>
              <input type="hidden" name="interface[controlbar]" value="false" />
							<input type="checkbox" name="interface[controlbar]" id="interface[controlbar]" value="true" <?php if( isset($fv_fp->conf['interface']['controlbar']) && $fv_fp->conf['interface']['controlbar'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[live]">Live stream: </label></td>
						<td>
              <input type="hidden" name="interface[live]" value="false" />
							<input type="checkbox" name="interface[live]" id="interface[live]" value="true" <?php if( isset($fv_fp->conf['interface']['live']) && $fv_fp->conf['interface']['live'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[live]">Speed Buttons: </label></td>
						<td>
              <input type="hidden" name="interface[speed]" value="false" />
							<input type="checkbox" name="interface[speed]" id="interface[speed]" value="true" <?php if( isset($fv_fp->conf['interface']['speed']) && $fv_fp->conf['interface']['speed'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>           
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" />
						</td>
					</tr>                                    
				</table>
<?php
}


function fv_flowplayer_admin_pro() {
  global $fv_fp;
  
  if( flowplayer::is_licensed() ) {
    $aCheck = get_transient( 'fv_flowplayer_license' );
  }
  
  if( isset($aCheck->valid) && $aCheck->valid ) : ?>  
    <p>Valid license found, click the button at the top of the screen to install FV Player Pro!</p>
  <?php else : ?>
    <p><a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/download">Purchase FV Flowplayer license</a> to enable Pro features!</p>
  <?php endif; ?>
  <table class="form-table2">
    <tr>
      <td><label>Advanced Vimeo embeding:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          Use Vimeo as your video host and use all of FV Flowplayer features.
        </p>
      </td>
    </tr>
    <tr>
      <td><label>Advanced Youtube embeding:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          Use Youtube as your video host and use all of FV Flowplayer features.
        </p>
      </td>
    </tr>
    <tr>
      <td><label>Enable user defined AB loop:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          Let your users repeat the parts of the video which they like!
        </p>
      </td>
    </tr>    
    <tr>
      <td><label>Enable video lightbox:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          Enables Lightbox video gallery to show videos in a lightbox popup!
        </p>
      </td>
    </tr>
    <tr>
      <td><label>Enable quality switching:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          Upload your videos in multiple quality for best user experience with Youtube-like quality switching!
        </p>
      </td>
    </tr>
    <tr>
      <td><label>Amazon CloudFront protected content:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          Protect your Amazon CDN hosted videos.
        </p>
      </td>
    </tr>        
    <tr>
      <td><label>Use video lightbox for images as well:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" disabled="true" />
          Will group images as well as videos into the same lightbox gallery.
        </p>
      </td>
    </tr>
    <tr>
      <td><label>Autoplay just once:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" disabled="true" />
          Makes sure each video autoplays only once for each visitor.
        </p>
      </td>
    </tr>    
  </table>
  <p>Upcoming pro features:</p>
  <table class="form-table2">
    <tr>
      <td><label>Enable PayWall:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          Monetize the video content on your membership site.
        </p>
      </td>
    </tr>  
  </table>  
  <?php
}


function fv_flowplayer_admin_skin() {
	global $fv_fp;
?>
  <div class="flowplayer-wrapper">
    <?php echo do_shortcode('[fvplayer src="http://foliovision.com/videos/example.mp4" splash="http://foliovision.com/videos/example.jpg" autoplay="false"]'); ?>
  </div>

  <table class="form-table2 flowplayer-settings">	
    <tr>
      <td><label for="bufferColor">Buffer</label></td>
      <td><input class="color small" id="bufferColor" name="bufferColor" type="text" value="<?php echo esc_attr($fv_fp->conf['bufferColor']); ?>" /></td>
      <td><label for="player-position">Player position</label> (<abbr title='You can still use align="right" where needed'>?</abbr>)</td>
      <td>
        <select id="player-position" name="player-position">
          <option value=""<?php if( $fv_fp->conf['player-position'] == "" ) echo ' selected="selected"'; ?>>Centered</option>										  
          <option value="left"<?php if( $fv_fp->conf['player-position'] == 'left'  ) echo ' selected="selected"'; ?>>Left (no text-wrap)</option>
        </select> 							
      </td>    
    </tr>
    <tr>
      <td><label for="canvas">Canvas</label></td>
      <td><input class="color small" id="canvas" name="canvas" type="text" value="<?php echo esc_attr($fv_fp->conf['canvas']); ?>" /></td>
      <td><label for="marginBottom">Bottom Margin</label></td>
      <td><input class="small" id="marginBottom" name="marginBottom" title="Enter value in pixels" type="text" value="<?php echo esc_attr($fv_fp->conf['marginBottom']); ?>" /></td>           
    </tr>            
    <tr>
      <td><label for="backgroundColor">Controlbar</label></td>
      <td><input class="color small" id="backgroundColor" name="backgroundColor" type="text" value="<?php echo esc_attr($fv_fp->conf['backgroundColor']); ?>" /></td>
      <td class="second-column"><label for="disableembedding">Disable Embed Button</label></td>
      <td><?php fv_flowplayer_admin_checkbox('disableembedding'); ?></td>
    </tr>
    <tr>
      <td><label for="progressColor">Progress</label></td>
      <td><input class="color small" id="progressColor" name="progressColor" type="text" value="<?php echo esc_attr($fv_fp->conf['progressColor']); ?>" /></td>
      <td><label for="disablesharing">Disable Sharing</label></td>
      <td><?php fv_flowplayer_admin_checkbox('disablesharing'); ?></td>   
    </tr>
    <tr>
      <td><label for="sliderColor">Sliders</label></td>
      <td><input class="color small" id="sliderColor" name="sliderColor" type="text" value="<?php echo esc_attr($fv_fp->conf['sliderColor']); ?>" /></td>
      <td><label for="allowfullscreen">Enable Fullscreen</label></td>
      <td><?php fv_flowplayer_admin_checkbox('allowfullscreen'); ?></td>              
    </tr>            
    <tr>
      <td><label for="timeColor">Time</label></td>
      <td><input class="color small" id="timeColor" name="timeColor" type="text" value="<?php echo esc_attr($fv_fp->conf['timeColor']); ?>" /></td>
      <!--<td><label for="ui_fixed_controlbar">Fixed Controlbar</label></td>
      <td><?php fv_flowplayer_admin_checkbox('ui_fixed_controlbar'); ?></td>-->
      <td><label for="font-face">Font Face</label></td>
      <td>
        <select id="font-face" name="font-face">
          <option value="&quot;Courier New&quot;, Courier, monospace"<?php if( $fv_fp->conf['font-face'] == "\"Courier New\", Courier, monospace" ) echo ' selected="selected"'; ?>>Courier New</option>										  
          <option value="Tahoma, Geneva, sans-serif"<?php if( $fv_fp->conf['font-face'] == "Tahoma, Geneva, sans-serif" ) echo ' selected="selected"'; ?>>Tahoma, Geneva</option>
          <option value="inherit"<?php if( $fv_fp->conf['font-face'] == 'inherit'  ) echo ' selected="selected"'; ?>>(inherit from template)</option>
        </select> 							
      </td>       
    </tr>            
    <tr>
      <td><label for="timeline">Timeline</label></td>
      <td><input class="color small" id="timelineColor" name="timelineColor" type="text" value="<?php echo esc_attr($fv_fp->conf['timelineColor']); ?>" /></td>
      <td><label for="subtitleSize">Subitle Font Size</label></td>
      <td><input class="small" id="subtitleSize" name="subtitleSize" title="Enter value in pixels" type="text" value="<?php echo ( isset($fv_fp->conf['subtitleSize']) ) ? intval($fv_fp->conf['subtitleSize']) : '16'; ?>" /></td>
    </tr>		
    <tr>              
      <td><label for="durationColor">Total time</label></td>
      <td><input class="color small" id="durationColor" name="durationColor" type="text" value="<?php echo esc_attr($fv_fp->conf['durationColor']); ?>" /></td>
      <td><label for="ui_play_button">Play Button</label></td>
      <td colspan="2"><?php fv_flowplayer_admin_checkbox('ui_play_button'); ?></td>        
      <!--<td><label for="db_duration">Show Playlist Duration (<abbr title="Beta version! Turn on to enable video duration scanning. Turn off if you experience issues when saving posts.">?!</abbr>)</label></td>
      <td><?php fv_flowplayer_admin_checkbox('db_duration'); ?></td>-->
    </tr>
    <!--<tr>
      <td><label for="buttonColor">Buttons</label></td>
      <td><input class="color small" type="text" name="buttonColor" id="buttonColor" value="<?php //echo $fv_fp->conf['buttonColor']; ?>" /></td>
      <td><label for="buttonOverColor">Mouseover</label></td>
      <td><input class="color small" type="text" name="buttonOverColor" id="buttonOverColor" value="<?php //echo $fv_fp->conf['buttonOverColor']; ?>" /></td>
    <tr>-->
    <tr>
      <td><label for="durationColor">Border color</label></td>
      <td><input class="color small" id="borderColor" name="borderColor" type="text" value="<?php echo esc_attr($fv_fp->conf['borderColor']); ?>" /></td>
      <td><label for="volume">Default Volume</label></td>
      <td><input id="volume" name="volume" type="range" min="0" max="1" step="0.1" value="<?php echo esc_attr($fv_fp->conf['volume']); ?>" /></td>     
    </tr>
    <tr>
      <td><label for="hasBorder">Border</label></td>
      <td><?php fv_flowplayer_admin_checkbox('hasBorder'); ?></td>
      <td><label for="ui_play_button">Speed Buttons</label></td>
      <td colspan="2"><?php fv_flowplayer_admin_checkbox('ui_speed'); ?></td>      
    </tr>
    <tr>    		
      <td colspan="4">
        <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" />
      </td>
    </tr>					
  </table>
  <div style="clear: both"></div>
<?php
}


function fv_flowplayer_admin_usage() {
?>
				<table class="form-table">
					<tr>
						<td colspan="4">  
							<div class="column">
								<div class="icon32" id="icon-users"><br></div>							
								<p>Illustrated user guides:</p>
								<div class="clear"></div>
								<ul>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/user-guide">Inserting videos</a>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/user-guide#license">License key and custom logo</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/creating-playlists">How to create a playlist</a></li>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/adding-ads">Using ads</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/google-advertising-options">Using Google Ads</a></li>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/rtmp-streams">RTMP streams</a></li>
                  <li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/subtitles">Subtitles</a></li>
                  <li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/secure-amazon-s3-guide">Amazon S3 secure content guide</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/hls-stream">How to setup a HLS stream</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/google-analytics-flowplayer">Google Analytics support</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/how-to-use-lightbox">Video lightbox</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/youtube-with-flowplayer">YouTube integration</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/switch-video-quality">Quality Switching</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/how-to-use-vimeo-pro">Vimeo integration</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/video-roll-post">Custom video ads</a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/serving-private-cloudfront">CloudFront CDN - protected video downloads</a></li>
								</ul>
							</div>
							<div class="column">
								<div class="icon32" id="icon-tools"><br></div>							
								<p>Troubleshooting:</p>
								<div class="clear"></div>
								<ul>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/installation">Automated checks</a></li>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding">Video encoding tips</a></li>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding#flash-only">Video formats to avoid</a></li>		
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/fix-amazon-mime-type">Fixing mime type on Amazon S3</a></li>		
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq">Plugin FAQ</a></li>									
									<li><a target="_blank" href="http://foliovision.com/support/fv-wordpress-flowplayer/">Support forums</a></li>	
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/installation/downgrading">Downgrading the plugin</a></li>									
								</ul>
							</div>
							<div class="clear"></div>
							<!--<p>
							To embed video "example.mp4", simply include the following code inside any post or page: 
							<code>[fvplayer src=example.mp4]</code>
							</p>
							<p>
							<code>src</code> is the only compulsory parameter, specifying the video file. Its value can be either a full URL of the file, 
							or just a filename (if it is located in the /videos/ directory in the root of the web).
							</p>
							<p>When user uploads are allowed, uploading or selecting video from WP Media Library is available. To insert selected video, simply use the 'Insert into Post' button.</p>
							<h4>Optional parameters:</h4>
							<ul style="text-align: left;">
								<li><code><strong>width</strong></code> and <code><strong>height</strong></code> specify the dimensions of played video in pixels. If they are not set, the default size is 320x240.<br />
								<i>Example</i>: <code>[fvplayer src='example.mp4' width=640 height=480]</code></li>
								<li><code><strong>splash</strong></code> parameter can be used to display a custom splash image before the video starts. Just like in case of <code>src</code> 
								parameter, its value can be either complete URL, or filename of an image located in /videos/ folder.<br />
								<i>Example</i>: <code>[fvplayer src='example.mp4' splash=image.jpg]</code></li>
								<li><code><strong>splashend</strong></code> parameter can be used to display a custom splash image after the video ends.<br />
								<i>Example</i>: <code>[fvplayer src='example.mp4' splashend=show]</code></li>
								<li><code><strong>autoplay</strong></code> parameter specify wheter the video should start to play automaticaly after the page is loaded. This parameter overrides the default autoplay setting above. Its value can be either true or false.<br />
								<i>Example</i>: <code>[fvplayer src='example.mp4' autoplay=true]</code></li>
								<li><code><strong>loop</strong></code> parameter specify wheter the video starts again from the beginning when the video ends. Its value can be either true or false.<br />
								<i>Example</i>: <code>[fvplayer src='example.mp4' loop=true]</code></li>
								<li><code><strong>popup</strong></code> parameter can be used to display any HTML code after the video finishes (ideal for advertisment or links to similar videos). 
								Content you want to display must be between simple quotes (<code>''</code>).<br />
								<i>Example</i>: <code>[fvplayer src='example.mp4' popup='&lt;p&gt;some HTML content&lt;/p&gt;']</code></li>      			
								<li><code><strong>redirect</strong></code> parameter can be used to redirect to another page (in a new tab) after the video ends.<br />
								<i>Example</i>: <code>[fvplayer src='example.mp4' redirect='http://www.site.com']</code></li>
							</ul>-->
						</td>
						<td></td>
					</tr>
				</table>
<?php			
}


function fv_flowplayer_admin_checkbox( $name ) {
	global $fv_fp;
?>
	<input type="hidden" name="<?php echo esc_attr($name); ?>" value="false" />
  <input type="checkbox" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="true" <?php if( isset($fv_fp->conf[$name]) && $fv_fp->conf[$name] == 'true' ) echo 'checked="checked"'; ?> />
<?php
}


add_meta_box( 'fv_flowplayer_description', 'Description', 'fv_flowplayer_admin_description', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_skin', 'Player Skin', 'fv_flowplayer_admin_skin', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_interface_options', 'Post Interface Options', 'fv_flowplayer_admin_interface_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_default_options', 'Sitewide Flowplayer Defaults', 'fv_flowplayer_admin_default_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_amazon_options', 'Amazon S3 Protected Content', 'fv_flowplayer_admin_amazon_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_ads', 'Ads', 'fv_flowplayer_admin_ads', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_integrations', 'Integrations', 'fv_flowplayer_admin_integrations', 'fv_flowplayer_settings', 'normal' );
if( !class_exists('FV_Player_Pro') ) {
  add_meta_box( 'fv_player_pro', 'Pro Features', 'fv_flowplayer_admin_pro', 'fv_flowplayer_settings', 'normal', 'low' );
}
add_meta_box( 'fv_flowplayer_usage', 'Usage', 'fv_flowplayer_admin_usage', 'fv_flowplayer_settings', 'normal', 'low' );

?>

<div class="wrap">
	<div style="position: absolute; margin-top: 10px; right: 10px;">
		<a href="https://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer" target="_blank" title="Documentation"><img alt="visit foliovision" src="http://foliovision.com/shared/fv-logo.png" /></a>
	</div>
  <div>
    <div id="icon-options-general" class="icon32"></div>
    <h2>FV Wordpress Flowplayer</h2>
  </div>
  
  <form id="wpfp_options" method="post" action="">  
  
    <p id="fv_flowplayer_admin_buttons">
      <input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_template'); return false" value="Check template" /> 
      <input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_files')" value="Check videos" />
      
      <input type="text" name="key" id="key" placeholder="Commercial License Key" value="<?php if( $fv_fp->conf['key'] !== "false" ) echo esc_attr($fv_fp->conf['key']); ?>" /> <a title="Click here for license info" target="_blank" href="https://foliovision.com/player/download"><span class="dashicons dashicons-editor-help"></span></a>
      
      <img class="fv_wp_flowplayer_check_template-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /> 
      <img class="fv_wp_flowplayer_check_files-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
      <?php do_action('fv_flowplayer_admin_buttons_after'); ?>
    </p>
    <div id="fv_flowplayer_admin_notices">
    </div>
  <?php
  
  do_action('fv_player_settings_pre');
  
  if( isset($_GET['fv_flowplayer_checker'] ) ) {
    do_action('fv_flowplayer_checker_event');
  }
  
  if( flowplayer::is_licensed() ) {
    $aCheck = get_transient( 'fv_flowplayer_license' );
    $aInstalled = get_option('fv_flowplayer_extension_install');
  }
  
  if( isset($aCheck->valid) && $aCheck->valid ){
    
    $fv_player_pro_path = fv_flowplayer_get_extension_path('fv-player-pro');
    
    if( is_plugin_inactive($fv_player_pro_path) && !is_wp_error(validate_plugin($fv_player_pro_path)) ) : ?>
      <div id="fv_flowplayer_addon_pro">
        <p>Thank you for purchasing FV Player license! <input type="button" class='button fv_wp_flowplayer_activate_extension' data-plugin="<?php echo $fv_player_pro_path; ?>" value="Enable the Pro extension" /> <img style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /></p>
      </div>
    <?php elseif( is_plugin_active($fv_player_pro_path) && !is_wp_error(validate_plugin($fv_player_pro_path)) ) : ?>
      <div id="fv_flowplayer_addon_pro">
        <p>Thank you for purchasing FV Player license! <input type="button" class="button" onclick="window.location.href += '&fv_player_pro_installed=yes#fv_player_pro'" value="Pro pack installed" /></p>
      </div>
    <?php else : ?>
      <div id="fv_flowplayer_addon_pro">
        <p>Thank you for purchasing FV Player license! <form method="post"><input type="submit" class="button" value="Install Pro extension" /><?php wp_nonce_field('fv_player_pro_install', 'nonce_fv_player_pro_install') ?></form></p>
      </div>
    <?php
    endif;
  }

  
  if( preg_match( '!^\$\d+!', $fv_fp->conf['key'] ) || apply_filters('fv_player_skip_ads',false) ) : ?>    
  <?php else : ?>
		<div id="fv_flowplayer_ad">
			<div class="text-part">
				<h2>FV Wordpress<strong>Flowplayer</strong></h2>
				<span class="red-text">with your own branding</span>
					<ul>
					<li>Put up your own logo</li>
					<li>Or remove the logo completely</li>
					<li>The best video plugin for Wordpress</li>
					</ul>
						<a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/download" class="red-button"><strong>Back to School sale!</strong><br />All Licenses 20% Off</a></p>
				</div>
				<div class="graphic-part">
					<a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy">
					<img width="297" height="239" border="0" src="<?php echo flowplayer::get_plugin_url().'/images/fv-wp-flowplayer-led-monitor.png' ?>"> </a>
				</div>
		</div>
  <?php endif; ?>	
  
  
		<div id="dashboard-widgets" class="metabox-holder columns-1">
			<div id='postbox-container-1' class='postbox-container'>    
				<?php
				do_meta_boxes('fv_flowplayer_settings', 'normal', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order-nonce', 'meta-box-order-nonce', false );
				?>
			</div>
		</div>
    <?php wp_nonce_field( 'fv_flowplayer_settings_nonce', 'fv_flowplayer_settings_nonce' ); ?>
  </form>
  
</div>
<script type="text/javascript" >
  function flowplayer_conversion_script() {
    jQuery('#fv-flowplayer-loader').show();
  
  	var data = {
  		action: 'flowplayer_conversion_script',
  		run: true
  	};
  
  	jQuery.post(ajaxurl, data, function(response) {
      jQuery('#fv-flowplayer-loader').hide();
      jQuery('#conversion-results').html(response);
      jQuery('#fvwpflowplayer_conversion_notice').hide();	
  	});
  }
  
	function fv_flowplayer_ajax_check( type ) {
		jQuery('.'+type+'-spin').show();
		var ajaxurl = '<?php echo site_url() ?>/wp-admin/admin-ajax.php';
		jQuery.post( ajaxurl, { action: type }, function( response ) {
      response = response.replace( /[\s\S]*<FVFLOWPLAYER>/, '' );
      response = response.replace( /<\/FVFLOWPLAYER>[\s\S]*/, '' );
			var obj = (jQuery.parseJSON( response ) );
			var css_class = '';
			jQuery('#fv_flowplayer_admin_notices').html('');
			if( obj.errors && obj.errors.length > 0 ) {
				jQuery('#fv_flowplayer_admin_notices').append( '<div class="error"><p>'+obj.errors.join('</p><p>')+'</p></div>' );
			} else {
				css_class = ' green';
			}

			if( obj.ok && obj.ok.length > 0 ) {
				jQuery('#fv_flowplayer_admin_notices').append( '<div class="updated'+css_class+'"><p>'+obj.ok.join('</p><p>')+'</p></div>' );
			}
			jQuery('.'+type+'-spin').hide();
		} );              
  }
  
  var fv_flowplayer_amazon_s3_count = 0;
  jQuery('#amazon-s3-add').click( function() {
  	var new_inputs = jQuery('tr.amazon-s3-first').clone(); 	
  	new_inputs.find('input').attr('value','');  	
		new_inputs.attr('class', new_inputs.attr('class') + '-' + fv_flowplayer_amazon_s3_count );
  	new_inputs.insertBefore('.amazon-s3-last');
  	fv_flowplayer_amazon_s3_count++;
  	return false;
  } );
  
  function fv_fp_amazon_s3_remove(a) {
  	jQuery( '.'+jQuery(a).parents('tr').attr('class') ).remove();
  }
</script>


<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('fv_flowplayer_settings');
    
    jQuery('.fv_wp_flowplayer_activate_extension').click( function() {  //  todo: block multiple clicks
      var button = jQuery(this);
      jQuery(button).siblings('img').show();
      
      var button = this;
      jQuery.post( ajaxurl, { action: 'fv_wp_flowplayer_activate_extension', nonce: '<?php echo wp_create_nonce( 'fv_wp_flowplayer_activate_extension' ); ?>', plugin: jQuery(this).attr("data-plugin") }, function( response ) {
        jQuery(button).siblings('img').hide();
        
        var obj;
        try {
          response = response.replace( /[\s\S]*<FVFLOWPLAYER>/, '' );
          response = response.replace( /<\/FVFLOWPLAYER>[\s\S]*/, '' );
          obj = jQuery.parseJSON( response );

          jQuery(button).attr('class','button');
          jQuery(button).attr('value',obj.message);
          
          if( typeof(obj.error) == "undefined" ) {
            //window.location.hash = '#'+jQuery(button).attr("data-plugin");
            //window.location.reload(true);
            window.location.href = window.location.href;
          }
        } catch(e) {  //  todo: what if there is "<p>Plugin install failed.</p>"
          jQuery(button).after('<p>Error parsing JSON</p>');
          return;
        }
    
      } ).error(function() {
        jQuery(button).siblings('img').hide();
        jQuery(button).after('<p>Error!</p>');
      });  
    } );
    
    jQuery('.fv-flowplayer-admin-addon-installed').click( function() {
      jQuery('html, body').animate({
          scrollTop: jQuery("#"+jQuery(this).attr("data-plugin") ).offset().top
      }, 1000);
    } );    
	});
	//]]>
</script>

