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
								<label for="ad"><?php _e('Default Ad Code', 'fv-wordpress-flowplayer'); ?>:</label><br />
								<textarea id="ad" name="ad" class="large-text code"><?php echo esc_textarea($fv_fp->_get_option('ad')); ?></textarea>			
							</td>
						</tr>
						<tr>
							<td colspan="2"><label for="ad_width"><?php _e('Default set size', 'fv-wordpress-flowplayer');?> [px]:</label> 
								<label for="ad_width">W:</label>&nbsp; <input type="text" name="ad_width" id="ad_width" value="<?php echo intval( $fv_fp->_get_option('ad_width') ); ?>" class="small" /> 
								<label for="ad_height">H:</label>&nbsp;<input type="text" name="ad_height" id="ad_height" value="<?php echo intval( $fv_fp->_get_option('ad_height') ); ?>" class="small"  />
								<label for="adTextColor"><?php _e('Ad text', 'fv-wordpress-flowplayer');?></label> <input class="color small" type="text" name="adTextColor" id="adTextColor" value="<?php echo esc_attr( $fv_fp->_get_option('adTextColor') ); ?>" /> 
								<label for="adLinksColor"><?php _e('Ad links', 'fv-wordpress-flowplayer');?></label> <input class="color small" type="text" name="adLinksColor" id="adLinksColor" value="<?php echo esc_attr( $fv_fp->_get_option('adLinksColor') ); ?>" /> 
							</td>			
						</tr> 
            <tr>
              <td>
                <label for="ad_show_after"><?php _e('Show After', 'fv-wordpress-flowplayer');?> [s]:</label>&nbsp; <input type="text" name="ad_show_after" id="ad_show_after" value="<?php echo intval( $fv_fp->_get_option('ad_show_after') ); ?>" class="small" /> 
              </td>
            </tr> 
						<tr>
							<td colspan="2">
								<label for="ad_css_select"><?php _e('Ad CSS', 'fv-wordpress-flowplayer'); ?>:</label>
								<a href="#" onclick="jQuery('.ad_css_wrap').show(); jQuery(this).hide(); return false"><?php _e('Show styling options', 'fv-wordpress-flowplayer'); ?></a>
								<div class="ad_css_wrap" style="display: none; ">
									<select id="ad_css_select">
										<option value=""><?php _e('Select your preset', 'fv-wordpress-flowplayer'); ?></option>
										<option value="<?php echo esc_attr($fv_fp->ad_css_default); ?>"<?php if( strcmp( preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->ad_css_default), preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->_get_option('ad_css') )) == 0 ) echo ' selected="selected"'; ?>><?php _e('Default (white, centered above the control bar)', 'fv-wordpress-flowplayer'); ?></option>
										<option value="<?php echo esc_attr($fv_fp->ad_css_bottom); ?>"<?php if( strcmp( preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->ad_css_bottom), preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->_get_option('ad_css') ))  == 0 ) echo ' selected="selected"'; ?>><?php _e('White, centered at the bottom of the video', 'fv-wordpress-flowplayer'); ?></option>					  		
									</select>
									<br />
									<textarea rows="5" name="ad_css" id="ad_css" class="large-text code"><?php echo esc_textarea($fv_fp->_get_option('ad_css')); ?></textarea>
									<p class="description"><?php _e('(Hint: put .wpfp_custom_ad_content before your own CSS selectors)', 'fv-wordpress-flowplayer'); ?></p>
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
								<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
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
							<p><?php _e('Secured Amazon S3 URLs are recommended for member-only sections of the site. We check the video length and make sure the link expiration time is big enough for the video to buffer properly.', 'fv-wordpress-flowplayer'); ?></p>
              <p><?php _e('If you use a cache plugin (such as Hyper Cache, WP Super Cache or W3 Total Cache), we recommend that you set the "Default Expiration Time" to twice as much as your cache timeout and check "Force the default expiration time". That way the video length won\'t be accounted and the video source URLs in your cached pages won\'t expire. Read more in the', 'fv-wordpress-flowplayer'); ?> <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/secure-amazon-s3-guide#wp-cache" target="_blank"><?php _e('Using Amazon S3 secure content in FV Flowplayer guide', 'fv-wordpress-flowplayer'); ?></a>.</p>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="amazon_expire"><?php _e('Default Expiration Time [minutes]', 'fv-wordpress-flowplayer'); ?> (<abbr title="<?php _e('Each video duration is stored on post save and then used as the expire time. If the duration is not available, this value is used.', 'fv-wordpress-flowplayer'); ?>">?</abbr>):</label></td>
						<td>
              <input type="text" size="40" name="amazon_expire" id="amazon_expire" value="<?php echo intval( $fv_fp->_get_option('amazon_expire') ); ?>" />            
            </td>
					</tr>
          
          <?php $fv_fp->_get_checkbox(__('Force the default expiration time', 'fv-wordpress-flowplayer'), 'amazon_expire_force'); ?>
          
          <?php do_action('fv_player_admin_amazon_options'); ?>
<?php
			$count = 0;
			foreach( $fv_fp->_get_option('amazon_bucket') AS $key => $item ) :
				$count++;
				$amazon_tr_class = ($count==1) ? ' class="amazon-s3-first"' : ' class="amazon-s3-'.$count.'"';
            $sRegion = $fv_fp->_get_option( array( 'amazon_region', $key ) );
?>					
        <tr<?php echo $amazon_tr_class; ?>>
            <td><label for="amazon_bucket[]"><?php _e('Amazon Bucket', 'fv-wordpress-flowplayer'); ?> (<abbr title="<?php _e('We recommend that you simply put all of your protected video into a single bucket and enter its name here. All matching videos will use the protected URLs.', 'fv-wordpress-flowplayer'); ?>">?</abbr>):</label></td>
            <td><input id="amazon_bucket[]" name="amazon_bucket[]" type="text" value="<?php echo esc_attr($item); ?>" /></td>
        </tr>
        <tr<?php echo $amazon_tr_class; ?>>
            <td><label for="amazon_region[]"><?php _e('Region', 'fv-wordpress-flowplayer'); ?></td>
            <td>
              <select id="amazon_region[]" name="amazon_region[]">
                <option value=""><?php _e('Select the region', 'fv-wordpress-flowplayer'); ?></option>
                <option value="eu-central-1"<?php if( $sRegion == 'eu-central-1' ) echo " selected"; ?>><?php _e('Frankfurt', 'fv-wordpress-flowplayer'); ?></option>
                <option value="eu-west-1"<?php if( $sRegion == 'eu-west-1' ) echo " selected"; ?>><?php _e('Ireland', 'fv-wordpress-flowplayer'); ?></option>
                <option value="us-west-1"<?php if( $sRegion == 'us-west-1' ) echo " selected"; ?>><?php _e('Northern California', 'fv-wordpress-flowplayer'); ?></option>
                <option value="us-west-2"<?php if( $sRegion == 'us-west-2' ) echo " selected"; ?>><?php _e('Oregon', 'fv-wordpress-flowplayer'); ?></option>
                <option value="sa-east-1"<?php if( $sRegion == 'sa-east-1' ) echo " selected"; ?>><?php _e('Sao Paulo', 'fv-wordpress-flowplayer'); ?></option>
                <option value="ap-northeast-2"<?php if( $sRegion == 'ap-northeast-2' ) echo " selected"; ?>><?php _e('Seoul', 'fv-wordpress-flowplayer'); ?></option>
                <option value="ap-southeast-1"<?php if( $sRegion == 'ap-southeast-1' ) echo " selected"; ?>><?php _e('Singapore', 'fv-wordpress-flowplayer'); ?></option>
                <option value="ap-southeast-2"<?php if( $sRegion == 'ap-southeast-2' ) echo " selected"; ?>><?php _e('Sydney', 'fv-wordpress-flowplayer'); ?></option>
                <option value="ap-northeast-1"<?php if( $sRegion == 'ap-northeast-1' ) echo " selected"; ?>><?php _e('Tokyo', 'fv-wordpress-flowplayer'); ?></option>
                <option value="us-east-1"<?php if( $sRegion == 'us-east-1' ) echo " selected"; ?>><?php _e('US Standard', 'fv-wordpress-flowplayer'); ?></option>
              </select>
            </td>
        </tr>
        <tr<?php echo $amazon_tr_class; ?>>
            <td><label for="amazon_key[]"><?php _e('Access Key ID', 'fv-wordpress-flowplayer'); ?>:</label></td>
            <td><input id="amazon_key[]" name="amazon_key[]" type="text" value="<?php echo esc_attr( $fv_fp->_get_option( array( 'amazon_key', $key ) ) ); ?>" /></td>
        </tr>
        <tr<?php echo $amazon_tr_class; ?>>
            <td><label for="amazon_secret[]"><?php _e('Secret Access Key', 'fv-wordpress-flowplayer'); ?>:</label></td>
            <td><input id="amazon_secret[]" name="amazon_secret[]" type="text" value="<?php echo esc_attr( $fv_fp->_get_option( array( 'amazon_secret', $key ) ) ); ?>" /></td>
        </tr>
        <tr<?php echo $amazon_tr_class; ?>>
            <td colspan="2">
                <div class="alignright fv_fp_amazon_remove">
                    <a href="#" onclick="fv_fp_amazon_s3_remove(this); return false"><?php _e('remove', 'fv-wordpress-flowplayer'); ?></a>
                </div>
                <div class="clear"></div>
                <hr style="border: 0; border-top: 1px solid #ccc;" />
            </td>
        </tr>
<?php
			endforeach;
?>							
					<tr class="amazon-s3-last"><td colspan="2"></td></tr>	
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
							<input type="button" id="amazon-s3-add" class="button" value="<?php _e('Add more Amazon S3 secure buckets', 'fv-wordpress-flowplayer'); ?>" />
						</td>
					</tr>   					                                 
				</table>
<?php 
}


function fv_flowplayer_admin_default_options() {
	global $fv_fp;
?>
          <style>
            p.description { font-style: normal; }
          </style>
					<table class="form-table2">
            
            <?php $fv_fp->_get_checkbox(__('Autoplay', 'fv-wordpress-flowplayer'), 'autoplay', __('We make sure only one video per page autoplays. Note that mobile devices don\'t support autoplay.' , 'fv-wordpress-flowplayer') ); ?>
            <?php $fv_fp->_get_checkbox(__('Auto Buffering', 'fv-wordpress-flowplayer'), 'auto_buffering', __('Works for first 2 videos on the page only, to preserve your bandwidth.', 'fv-wordpress-flowplayer') ); ?>

						<tr>
							<td><label for="width"><?php _e('Default Video Size', 'fv-wordpress-flowplayer'); ?>:</label></td>
							<td>
                <p class="description">
                  <label for="width"><?php _e('Width', 'fv-wordpress-flowplayer'); ?>:</label>&nbsp;<input type="text" class="small" name="width" id="width" value="<?php echo intval( $fv_fp->_get_option('width') ); ?>" />
                  <label for="height"><?php _e('Height', 'fv-wordpress-flowplayer'); ?>:</label>&nbsp;<input type="text" class="small" name="height" id="height" value="<?php echo intval( $fv_fp->_get_option('height') ); ?>" />
                  <?php _e('Enter values in pixels.', 'fv-wordpress-flowplayer'); ?>
                </p>
							</td>
						</tr>
						<tr>
							<td><label for="volume"><?php _e('Default Volume', 'fv-wordpress-flowplayer'); ?>:</label></td>
							<td>
                <p class="description">
                  <input id="volume" name="volume" type="range" min="0" max="1" step="0.1" value="<?php echo esc_attr( $fv_fp->_get_option('volume') ); ?>" class="medium" />
                </p>
							</td>
            </tr>

            <?php $fv_fp->_get_checkbox(__('Disable Admin Video Checker', 'fv-wordpress-flowplayer'), 'disable_videochecker', __('Checks your video encoding when you open a post with video as admin. Notifies you about possible playback issues.', 'fv-wordpress-flowplayer') ); ?>
            <?php $fv_fp->_get_checkbox(__('Disable Embed Button', 'fv-wordpress-flowplayer'), 'disableembedding', __('Removes embed button from top bar.', 'fv-wordpress-flowplayer') ); ?>
            <?php $fv_fp->_get_checkbox(__('Disable Playlist Autoadvance', 'fv-wordpress-flowplayer'), 'playlist_advance', __('Playlist won\'t play the next video automatically.', 'fv-wordpress-flowplayer') ); ?>
            <?php $fv_fp->_get_checkbox(__('Disable Sharing', 'fv-wordpress-flowplayer'), 'disablesharing', __('Removes sharing buttons from top bar.', 'fv-wordpress-flowplayer') ); ?>

						<tr>
							<td><label for="rtmp"><?php _e('Flash Streaming Server', 'fv-wordpress-flowplayer'); ?>:</label></td>
							<td>
                <p class="description">
                  <input type="text" name="rtmp" id="rtmp" value="<?php echo esc_attr( $fv_fp->_get_option('rtmp') ); ?>" placeholder="<?php _e('Enter your default RTMP streaming server (Amazon CloudFront domain).', 'fv-wordpress-flowplayer'); ?>" />
                </p>
							</td>
						</tr>

            <?php $fv_fp->_get_checkbox(__('Fullscreen Button', 'fv-wordpress-flowplayer'), 'allowfullscreen', __('Adds fullscreen button to player top bar.', 'fv-wordpress-flowplayer') ); ?>

						<tr>
							<td><label for="googleanalytics"><?php _e('Google Analytics ID', 'fv-wordpress-flowplayer'); ?>:</label></td>
							<td>
                <p class="description">
                  <input type="text" name="googleanalytics" id="googleanalytics" value="<?php echo esc_attr( $fv_fp->_get_option('googleanalytics') ); ?>" placeholder="<?php _e('Will be automatically loaded when playing a video.', 'fv-wordpress-flowplayer'); ?>" />
                </p>
							</td>
						</tr>
						<tr>
							<td><label for="logo">Logo:</label></td>
							<td>
                <input type="text"  name="logo" id="logo" value="<?php echo esc_attr( $fv_fp->_get_option('logo') ); ?>" class="large" placeholder="<?php
            $aCheck = false;
            if( flowplayer::is_licensed() ) {
              $aCheck = get_transient( 'fv_flowplayer_license' );
            }
            if( $aCheck && isset($aCheck->valid) && $aCheck->valid ) {
              _e('You have a valid FV Flowplayer license, you can put up your logo here', 'fv-wordpress-flowplayer');
            } else {
              _e('You need to have a FV Flowplayer license to use it', 'fv-wordpress-flowplayer');
            }
            ?>" />

                <input id="upload_image_button" class="upload_image_button button no-margin small" type="button" value="<?php _e('Upload Image', 'fv-wordpress-flowplayer'); ?>" alt="Select Logo" />

                <?php
                $value = $fv_fp->_get_option('logoPosition');
                ?>
                <select name="logoPosition" class="small">
                  <option value="bottom-left"><?php _e('Position', 'fv-wordpress-flowplayer'); ?></option>
                  <option <?php if( $value == 'bottom-left' ) echo "selected"; ?> value="bottom-left"><?php _e('Bottom-left', 'fv-wordpress-flowplayer'); ?></option>
                  <option <?php if( $value == 'bottom-right' ) echo "selected"; ?> value="bottom-right"><?php _e('Bottom-right', 'fv-wordpress-flowplayer'); ?></option>
                  <option <?php if( $value == 'top-left' ) echo "selected"; ?> value="top-left"><?php _e('Top-left', 'fv-wordpress-flowplayer'); ?></option>
                  <option <?php if( $value == 'top-right' ) echo "selected"; ?> value="top-right"><?php _e('Top-right', 'fv-wordpress-flowplayer'); ?></option>
                </select>
              </td>
						</tr>

            <?php $fv_fp->_get_checkbox(__('Play Button', 'fv-wordpress-flowplayer'), 'ui_play_button', __('Adds play button to player controlbar.', 'fv-wordpress-flowplayer') ); ?>

            <tr>
							<td><label for="liststyle"><?php _e('Playlist style', 'fv-wordpress-flowplayer'); ?>:</label></td>
							<td colspan="3">
                <p class="description">
                  <?php
                  $value = $fv_fp->_get_option('liststyle');
                  ?>
                  <select id="liststyle" name="liststyle">
                    <option value="horizontal"<?php if( $value == 'horizontal' ) echo ' selected="selected"'; ?> ><?php _e('Horizontal', 'fv-wordpress-flowplayer'); ?></option>
                    <option value="tabs"      <?php if( $value == 'tabs' ) echo ' selected="selected"'; ?> ><?php _e('Tabs', 'fv-wordpress-flowplayer'); ?></option>
                    <option value="prevnext"  <?php if( $value == 'prevnext' ) echo ' selected="selected"'; ?> ><?php _e('Prev/Next', 'fv-wordpress-flowplayer'); ?></option>
                    <option value="vertical"  <?php if( $value == 'vertical' ) echo ' selected="selected"'; ?> ><?php _e('Vertical', 'fv-wordpress-flowplayer'); ?></option>
                  </select>
                  <?php _e('Enter your default playlist style here', 'fv-wordpress-flowplayer'); ?>
                </p>
              </td>
						</tr>

            <?php $fv_fp->_get_checkbox(__('Popup Box', 'fv-wordpress-flowplayer'), 'popupbox', __('Shows a generic "Would you like to replay the video?" message at the end of each video.', 'fv-wordpress-flowplayer') ); ?>

            <tr>
              <td><label for="sharing_text"><?php _e('Sharing Text', 'fv-wordpress-flowplayer'); ?>:</label></td>
              <td>
                <p class="description">
                  <input type="text" name="sharing_email_text" id="sharing_email_text" value="<?php echo $fv_fp->_get_option('sharing_email_text'); ?>" placeholder="<?php _e('Check out the amazing video here', 'fv-wordpress-flowplayer'); ?>" />
                </p>
              </td>
            </tr>

            <?php $fv_fp->_get_checkbox(__('Speed Buttons', 'fv-wordpress-flowplayer'), 'ui_speed', __('Speed buttons control playback speed and only work in HTML5 compatible browsers.', 'fv-wordpress-flowplayer') ); ?>

            <tr>
							<td><label for="ui_speed_increment"><?php _e('Speed Step', 'fv-wordpress-flowplayer'); ?>:</label></td>
							<td colspan="3">
                <p class="description">
                  <?php
                  $value = $fv_fp->_get_option('ui_speed_increment');
                  ?>
                  <select id="ui_speed_increment" name="ui_speed_increment">
                    <option value="0.1"   <?php if( $value == 0.1 ) echo ' selected="selected"'; ?> >0.1</option>
                    <option value="0.25"  <?php if( $value == 0.25 ) echo ' selected="selected"'; ?> >0.25</option>
                    <option value="0.5"   <?php if ( $value == 0.5 )  echo ' selected="selected"'; ?> >0.5</option>
                  </select>
                  <?php _e('Speed buttons will increase or decrease the speed in steps of selected value', 'fv-wordpress-flowplayer'); ?>
                </p>
              </td>
						</tr>
            <tr>
							<td><label for="splash"><?php _e('Splash Image', 'fv-wordpress-flowplayer'); ?>:</label></td>
              <td>
                <input type="text" name="splash" id="splash" value="<?php echo esc_attr( $fv_fp->_get_option('splash') ); ?>" class="large" placeholder="<?php _e('Default which will be used for any player without its own splash image.', 'fv-wordpress-flowplayer'); ?>" />
                <input id="upload_image_button" class="upload_image_button button no-margin small" type="button" value="<?php _e('Upload Image', 'fv-wordpress-flowplayer'); ?>" alt="Select default Splash Screen" /></td>
						</tr>

            <?php $fv_fp->_get_checkbox(__('Subtitles On By Default', 'fv-wordpress-flowplayer'), 'subtitleOn', __('Normally you have to hit a button in controlbar to turn on subtitles.', 'fv-wordpress-flowplayer') ); ?>

            <?php do_action('fv_flowplayer_admin_default_options_after'); ?>
          </table>
          <small class="alignright">
          	<?php _e('Missing settings? Check <a class="fv-settings-anchor" href="#fv_flowplayer_integrations">Integrations/Compatbility</a> box below.', 'fv-wordpress-flowplayer'); ?>
          </small>
          <table class="form-table2">
						<tr>
							<td colspan="4">
								<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
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

/*
 * Setup Tab Description
 */
function fv_flowplayer_admin_description() {
?>
  <table class="form-table">
    <tr>
      <td colspan="4">
        <p>
          <?php _e('FV Player is a free, easy-to-use, and complete solution for embedding', 'fv-wordpress-flowplayer'); ?>
          <strong>MP4</strong>, <strong>WEBM</strong>, <strong>OGV</strong>, <strong>MOV</strong>
          <?php _e('and', 'fv-wordpress-flowplayer'); ?>
          <strong>FLV</strong>
          <?php _e('videos into your posts or pages. With MP4 videos, FV Player offers 98&#37; coverage even on mobile devices.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
  </table>
<?php
}

/*
 * Skin Tab Description
 */
function fv_flowplayer_admin_description_skin() {
?>
  <table class="form-table">
      <tr>
        <td colspan="4">
          <p>
            <?php _e('You can customize the colors of the player to match your website.', 'fv-wordpress-flowplayer'); ?>
          </p>
        </td>
      </tr>
    </table>
<?php
}

/*
 * Hosting Tab Description
 */
function fv_flowplayer_admin_description_hosting() {
?>
  <table class="form-table">
    <tr>
      <td colspan="4">
        <p>
          <?php _e('Here you can enable and configure advanced hosting options.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
  </table>
<?php
}

/*
 * Actions Tab Description
 */
function fv_flowplayer_admin_description_actions() {
?>
  <table class="form-table">
    <tr>
      <td colspan="4">
        <p>
          <?php _e('Here you can configure ads and banners that will be showed in the video.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
  </table>
<?php
}

/*
 * Video Ads Tab Description
 */
function fv_flowplayer_admin_description_video_ads() {
?>
  <table class="form-table">
    <tr>
      <td colspan="4">
        <p>
          <?php _e('Purchase <a href="https://foliovision.com/player/download" target="_blank"><b>FV Player Licence</b></a>, and you will be able to configure multiple, clickable Video Ads, that can be played before or after Your videos.', 'fv-wordpress-flowplayer'); ?>
        </p>
        <p>
          <?php _e('You can configure video ads globally, or on a per video basis.', 'fv-wordpress-flowplayer'); ?>
        </p>
        <p>
          <?php _e('If you are interested in VAST or VPAID ads, then check out <a href="https://foliovision.com/player/vast" target="_blank"><b>FV Player VAST</b></a>.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
  </table>
<?php
}

function fv_flowplayer_admin_integrations() {
	global $fv_fp;
?>
        <p><?php _e('Following options are suitable for web developers and programmers.', 'fv-wordpress-flowplayer'); ?></p>
				<table class="form-table2">

          <?php $fv_fp->_get_checkbox(__('Always use fixed size player', 'fv-wordpress-flowplayer'), 'fixed_size', __('Enable to force video size at cost of loosing the video responsiveness.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Colorbox Compatibility', 'fv-wordpress-flowplayer'), 'cbox_compatibility', __('Enable if your theme is using colorbox lightbox to show content and clones the HTML content into it.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Disable saving of color settings into a static file', 'fv-wordpress-flowplayer'), 'css_disable', __('Normally the player CSS configuration is stored in wp-content/fv-player-custom/style-{blog_id}.css.', 'fv-wordpress-flowplayer'), __('We do this to avoid outputting CSS code into your site <head>. Don\'t edit this file though, as it will be overwritten by plugin update or saving its options!','fv-wordpress-flowplayer' )); ?>
          <?php $fv_fp->_get_checkbox(__('Enable HLS.js', 'fv-wordpress-flowplayer'), 'hlsjs', __('Beta version, allows HLS playback in all modern browsers.', 'fv-wordpress-flowplayer'), __('HLS normally plays only on iOS, Mac Safari and new Android versions. FV Player increases the compatibility by using Flash engine for HLS. With this option you can go even further and modern browsers supporting MediaSource will play HLS even without Flash. Make sure you setup the required CORS headers.','fv-wordpress-flowplayer' )); ?>

          <tr>
						<td><label for="css_disable"><?php _e('Enable profile videos (beta)', 'fv-wordpress-flowplayer'); ?>:</label></td>
						<td>
              <div class="description">
                <p>
                  <input type="hidden" name="profile_videos_enable_bio" value="false" />
                  <input type="checkbox" name="profile_videos_enable_bio" id="profile_videos_enable_bio" value="true" <?php if( $fv_fp->_get_option('profile_videos_enable_bio') ) echo 'checked="checked"'; ?> />
                  <?php _e('Check your site carefully after enabling. Videos attached to the user profile will be showing as a part of the user bio.', 'fv-wordpress-flowplayer'); ?> <a href="#" class="show-more">(&hellip;)</a>
                </p>
                <div class="more">
                  <p><?php _e('This feature is designed for YouTube and Vimeo videos and works best for our licensed users who get these videos playing without YouTube or Vimeo branding.','fv-wordpress-flowplayer'); ?></p>
                  <p><?php _e('Some themes show author bio on the author post archive automatically (Genesis framework and others). Or you can also just put this code into your theme archive.php template, right before <code>while ( have_posts() )</code> is called:','fv-wordpress-flowplayer'); ?></p>
                  <blockquote>
<pre>
&lt;?php if ( is_author() &amp;&amp; get_the_author_meta( 'description' ) ) : ?&gt;
  &lt;div class=&quot;author-info&quot;&gt;
    &lt;div class=&quot;author-avatar&quot;&gt;
      &lt;?php echo get_avatar( get_the_author_meta( 'user_email' ) ); ?&gt;
    &lt;/div&gt;

    &lt;div class=&quot;author-description&quot;&gt;
      &lt;?php the_author_meta( 'description' ); ?&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;?php endif; ?&gt;
</pre>
                  </blockquote>
                  <p><?php _e('We will be adding integration for it for popular user profile plugins.','fv-wordpress-flowplayer'); ?></p>

                </div>
              </div>
						</td>
					</tr>

          <?php $fv_fp->_get_checkbox(__('Fit scaling', 'fv-wordpress-flowplayer'), 'scaling', __('Original aspect ratio of the video will be used to display the video - for troubleshooting of fullscreen issues.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Handle WordPress <code><small>[video]</small></code> shortcodes', 'fv-wordpress-flowplayer'), array( 'integrations', 'wp_core_video' ), '', '' ); ?>
          <?php $fv_fp->_get_checkbox(__('Load FV Flowplayer JS everywhere', 'fv-wordpress-flowplayer'), 'js-everywhere', __('If you use some special JavaScript integration you might prefer this option.', 'fv-wordpress-flowplayer'), __('Otherwise our JavaScript only loads if the shortcode is found in any of the posts being currently displayed.', 'fv-wordpress-flowplayer') ); ?>
					<?php $fv_fp->_get_checkbox(__('Parse old shortcodes with commas', 'fv-wordpress-flowplayer'), 'parse_commas', __('Older versions of this plugin used commas to sepparate shortcode parameters.', 'fv-wordpress-flowplayer'), __('This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Parse Vimeo and YouTube links', 'fv-wordpress-flowplayer'), 'parse_comments', __('Affects comments, bbPress and BuddyPress. These links will be displayed as videos.', 'fv-wordpress-flowplayer'), __('This option makes most sense together with FV Player Pro as it embeds these videos using FV Player. Enables use of shortcodes in comments and bbPress.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Post Thumbnail', 'fv-wordpress-flowplayer'), 'postthumbnail', __('Setting a video splash screen from the media library will automatically make it the splash image if there is none.', 'fv-wordpress-flowplayer') ); ?>
					<?php $fv_fp->_get_checkbox(__('Prefer Flash player by default', 'fv-wordpress-flowplayer'), 'engine', __('Provides greater compatibility.', 'fv-wordpress-flowplayer'), __('We use Flash for MP4 files in IE9-10 and M4V files in Firefox regardless of this setting.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('RTMP bufferTime tweak', 'fv-wordpress-flowplayer'), 'rtmp-live-buffer', __('Use if your live streams are not smooth.', 'fv-wordpress-flowplayer'), __('Adobe <a href="http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/net/NetStream.html#bufferTime">recommends</a> to set bufferTime to 0 for live streams, but if your stream is not smooth, you can use this setting.', 'fv-wordpress-flowplayer') ); ?>

          <tr>
						<td class="first"><label for="db_duration"><?php _e('Scan video length', 'fv-wordpress-flowplayer'); ?>:</label></td>
						<td>
              <p class="description">
                <input type="hidden" name="db_duration" value="false" />
                <input type="checkbox" name="db_duration" id="db_duration" value="true" <?php if( $fv_fp->_get_option('db_duration') ) echo 'checked="checked"'; ?> />
                <?php _e('Beta version, turn off if you experience issues when saving posts!','fv-wordpress-flowplayer'); ?>
                <span class="more"><?php _e('Turn on to enable video duration scanning.', 'fv-wordpress-flowplayer'); ?>
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
                  printf(__('Currently %d videos in database and %s posts in queue.', 'fv-wordpress-flowplayer'), $iCount, $htmlQueue);
                } else if( $iCount ) {
                  printf(__("Currently %d videos in database.", 'fv-wordpress-flowplayer'), $iCount);
                } else if( $iQueue ) {
                  printf(__("Currently %s posts in queue.", 'fv-wordpress-flowplayer'), $htmlQueue);
                }
                ?>
                </span> <a href="#" class="show-more">(&hellip;)</a>
              </p>
						</td>
					</tr>
					<!--<tr>
						<td style="width: 350px"><label for="optimizepress2">Handle OptimizePress 2 videos (<abbr title="Following attributes are not currently supported: margin, border">?</abbr>):</label></td>
						<td>
              <input type="hidden" name="integrations[optimizepress2]" value="false" />
              <input type="checkbox" name="integrations[optimizepress2]" id="optimizepress2" value="true" <?php if( $fv_fp->_get_option( array( 'integrations', 'optimizepress2' ) ) ) echo 'checked="checked"'; ?> />
						</td>
					</tr>-->

          <?php $fv_fp->_get_checkbox(__('Use iframe embedding', 'fv-wordpress-flowplayer'), array( 'integrations', 'embed_iframe' ), __('Beta version! New kind of embedding which supports all the features in embedded player.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Add featured image automatically', 'fv-wordpress-flowplayer'), array( 'integrations', 'featured_img' ), __('If the featured image is not set, splash image of the first player will be used.', 'fv-wordpress-flowplayer') ); ?>

          <?php do_action('fv_flowplayer_admin_integration_options_after'); ?>
					<tr>
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
						</td>
					</tr>
				</table>
<?php
}

function fv_flowplayer_admin_select_popups($aArgs){
  global $fv_fp;

  $aPopupData = get_option('fv_player_popups');


  $sId = (isset($aArgs['id'])?$aArgs['id']:'popups_default');
  $aArgs = wp_parse_args( $aArgs, array( 'id'=>$sId, 'cva_id'=>'', 'show_default' => false ) );
  ?>
  <select id="<?php echo $aArgs['id']; ?>" name="<?php echo $aArgs['id']; ?>">
    <?php if( $aArgs['show_default'] ) : ?>
      <option>Use site default</option>
    <?php endif; ?>
    <option <?php if( $aArgs['item_id'] == 'no' ) echo 'selected '; ?>value="no">None</option>
    <option <?php if( $aArgs['item_id'] == 'random' ) echo 'selected '; ?>value="random">Random</option>
    <?php
    if( isset($aPopupData) && is_array($aPopupData) && count($aPopupData) > 0 ) {
      foreach( $aPopupData AS $key => $aPopupAd ) {
        ?><option <?php if( $aArgs['item_id'] == $key ) echo 'selected'; ?> value="<?php echo $key; ?>"><?php
        echo $key;
        if( !empty($aPopupAd['name']) ) echo ' - '.$aPopupAd['name'];
        if( $aPopupAd['disabled'] == 1 ) echo ' (currently disabled)';
        ?></option><?php
      }
    } ?>
  </select>
  <?php
}


function fv_flowplayer_admin_popups(){
  global $fv_fp;
    ?>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td style="width:150px;vertical-align:top;line-height:2.4em;"><label for="popups_default"><?php _e('Default Popup', 'fv-wordpress-flowplayer'); ?>:</label></td>
        <td>
          <?php $cva_id = $fv_fp->_get_option('popups_default'); ?>
          <?php fv_flowplayer_admin_select_popups( array('item_id'=>$cva_id,'id'=>'popups_default') ); ?>
          <p class="description"><?php _e('You can set a default popup here and then skip it for individual videos.', 'fv-wordpress-flowplayer'); ?></p>
        </td>
      </tr>
      </table>
      <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td>
          <table id="fv-player-popups-settings">
            <thead>
            	<tr>
            		<td>ID</td>
            		<td></td>
          			<td><?php _e('Status', 'fv-wordpress-flowplayer'); ?></td>
        			</tr>
      			</thead>
            <tbody>
            <?php
            $aPopupData = get_option('fv_player_popups');
            if( empty($aPopupData) ) {
              $aPopupData = array( 1 => array() );
            } else {
              $aPopupData =  array( '#fv_popup_dummy_key#' => array() ) + $aPopupData ;
            }

            foreach ($aPopupData AS $key => $aPopup) {
              ?>
              <tr class='data' id="fv-player-popup-item-<?php echo $key; ?>"<?php echo $key === '#fv_popup_dummy_key#' ? 'style="display:none"' : ''; ?>>
                <td class='id'><?php echo $key ; ?></td>
                    <td>
                      <table class='fv-player-popup-formats'>
                        <tr>
                        	<td><label><?php _e('Name', 'fv-wordpress-flowplayer'); ?>:</label></td>
                        	<td><input type='text' maxlength="40" name='popups[<?php echo $key; ?>][name]' value='<?php echo ( !empty($aPopup['name']) ? esc_attr($aPopup['name']) : '' ); ?>' placeholder='' /></td>
                      	</tr>
                        <tr>
                        	<td><label>HTML:</label></td>
                        	<td><textarea class="large-text code" type='text' name='popups[<?php echo $key; ?>][html]' placeholder=''><?php echo ( !empty($aPopup['html']) ? esc_textarea($aPopup['html']) : '' ); ?></textarea></td>
                      	</tr>
                        <tr>
                        	<td><label><?php _e('Custom<br />CSS', 'fv-wordpress-flowplayer'); ?>:</label></td>
                        	<td><textarea class="large-text code" type='text' name='popups[<?php echo $key; ?>][css]' placeholder='.fv_player_popup-<?php echo $key; ?> { }'><?php echo ( !empty($aPopup['css']) ? esc_textarea($aPopup['css']) : '' ); ?></textarea></td>
                      	</tr>
                      </table>
                    </td>
                    <td>
                      <input type='hidden' name='popups[<?php echo $key; ?>][disabled]' value='0' />
                      <input id='PopupAdDisabled-<?php echo $key; ?>' type='checkbox' name='popups[<?php echo $key; ?>][disabled]' value='1' <?php echo (isset($aPopup['disabled']) && $aPopup['disabled'] ? 'checked="checked"' : ''); ?> />
                      <label for='PopupAdDisabled-<?php echo $key; ?>'><?php _e('Disable', 'fv-wordpress-flowplayer'); ?></label><br />
                      <a class='fv-player-popup-remove' href=''><?php _e('Remove', 'fv-wordpress-flowplayer'); ?></a></td>
                  </tr>
              <?php
            }
            ?>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
          <input type="button" value="<?php _e('Add more Popups', 'fv-wordpress-flowplayer'); ?>" class="button" id="fv-player-popups-add" />
        </td>
      </tr>
    </table>

    <script>

    jQuery('#fv-player-popups-add').click( function() {
      var fv_player_popup_index  = (parseInt( jQuery('#fv-player-popups-settings tr.data:last .id').html()  ) || 0 ) + 1;
      jQuery('#fv-player-popups-settings').append(jQuery('#fv-player-popups-settings tr.data:first').prop('outerHTML').replace(/#fv_popup_dummy_key#/gi,fv_player_popup_index + ""));
      jQuery('#fv-player-popup-item-'+fv_player_popup_index).show();
      return false;
    } );

    jQuery(document).on('click','.fv-player-popup-remove', false, function() {
      if( confirm('Are you sure you want to remove the popup ad?') ){
        jQuery(this).parents('.data').remove();
        if(jQuery('#fv-player-popups-settings .data').length === 1) {
          jQuery('#fv-player-popups-add').trigger('click');
        }
      }
      return false;
    } );
    </script>
    <?php
}


function fv_flowplayer_admin_interface_options() {
	global $fv_fp;
?>
				<p><?php _e('Which features should be available in shortcode editor?', 'fv-wordpress-flowplayer'); ?></p>
				<table class="form-table2">
          <?php $fv_fp->_get_checkbox(__('Ads', 'fv-wordpress-flowplayer'), array('interface', 'ads') ); ?>
          <?php $fv_fp->_get_checkbox(__('Align', 'fv-wordpress-flowplayer'), array('interface', 'align') ); ?>
          <?php $fv_fp->_get_checkbox(__('Allow Video Uploads', 'fv-wordpress-flowplayer'),'allowuploads', __('Enables the WP Media Library integraton if you want to host videos on your own server', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Autoplay', 'fv-wordpress-flowplayer'), array('interface', 'autoplay') ); ?>
          <?php $fv_fp->_get_checkbox(__('Controlbar', 'fv-wordpress-flowplayer'), array('interface', 'controlbar') ); ?>
          <?php $fv_fp->_get_checkbox(__('Embed', 'fv-wordpress-flowplayer'), array('interface', 'embed') ); ?>
          <?php $fv_fp->_get_checkbox(__('Live Stream', 'fv-wordpress-flowplayer'), array('interface', 'live') ); ?>
          <?php $fv_fp->_get_checkbox(__('Mobile Video', 'fv-wordpress-flowplayer'), array('interface', 'mobile') ); ?>
          <?php $fv_fp->_get_checkbox(__('Playlist Auto Advance', 'fv-wordpress-flowplayer'), array('interface', 'playlist_advance') ); ?>
          <?php $fv_fp->_get_checkbox(__('Playlist Style', 'fv-wordpress-flowplayer'), array('interface', 'playlist') ); ?>
          <?php $fv_fp->_get_checkbox(__('Playlist Captions', 'fv-wordpress-flowplayer'), array('interface', 'playlist_captions') ); ?>
          <?php $fv_fp->_get_checkbox(__('Speed Buttons', 'fv-wordpress-flowplayer'), array('interface', 'speed') ); ?>
          <?php $fv_fp->_get_checkbox(__('Subtitles', 'fv-wordpress-flowplayer'), array('interface', 'subtitles') ); ?>
          <?php $fv_fp->_get_checkbox(__('Video Actions', 'fv-wordpress-flowplayer'), array('interface', 'end_actions'), __('Enables end of playlist actions like Loop, Redirect, Show popup and Show splash screen', 'fv-wordpress-flowplayer') ); ?>

          <?php do_action('fv_flowplayer_admin_interface_options_after'); ?>

          <?php $fv_fp->_get_checkbox(__('Enable old interface', 'fv-wordpress-flowplayer'), array('interface', 'shortcode_editor_old'), __('Not recommended', 'fv-wordpress-flowplayer') ); ?>
          
					<tr>
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
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
    <p><?php _e('Valid license found, click the button at the top of the screen to install FV Player Pro!', 'fv-wordpress-flowplayer'); ?></p>
  <?php else : ?>
    <p><a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/download"><?php _e('Purchase FV Flowplayer license', 'fv-wordpress-flowplayer'); ?></a> <?php _e('to enable Pro features!', 'fv-wordpress-flowplayer'); ?></p>
  <?php endif; ?>
  <table class="form-table2">
    <tr>
      <td class="first"><label><?php _e('Advanced Vimeo embeding', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php _e('Use Vimeo as your video host and use all of FV Flowplayer features.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php _e('Advanced YouTube embeding', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php _e('Use YouTube as your video host and use all of FV Flowplayer features.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php _e('Enable user defined AB loop', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php _e('Let your users repeat the parts of the video which they like!', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php _e('Enable video lightbox', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php _e('Enables Lightbox video gallery to show videos in a lightbox popup!', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php _e('Enable quality switching', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php _e('Upload your videos in multiple quality for best user experience with YouTube-like quality switching!', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php _e('Amazon CloudFront protected content', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php _e('Protect your Amazon CDN hosted videos', 'fv-wordpress-flowplayer'); ?>.
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php _e('Autoplay just once', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" disabled="true" />
          <?php _e('Makes sure each video autoplays only once for each visitor.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php _e('Enable video ads', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" disabled="true" />
          <?php _e('Define your own videos ads to play in together with your videos - postroll or prerool', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>     
  </table>
  <p><strong><?php _e('Upcoming pro features', 'fv-wordpress-flowplayer'); ?></strong>:</p>
  <table class="form-table2">
    <tr>
      <td class="first"><label><?php _e('Enable PayWall', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php _e('Monetize the video content on your membership site.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>  
  </table>  
  <?php
}

/*
 * Pro Video Ads Dummy box
 */
function fv_flowplayer_admin_video_ads(){
  ?>
  <style>
      #fv-player-pro_video-ads-settings tr.data:nth-child(even) { background-color: #eee; }
      .fv-player-pro_video-ad-remove { visibility: hidden; }
      table.fv-player-pro_video-ad-formats td:first-child { width: 132px }
    </style>
    <table class="form-table2" style="margin: 5px; ">
      <tbody><tr>
          <td style="width:180px"><label for="pro[video_ads_default]"><?php _e('Default pre-roll ad:', 'fv-wordpress-flowplayer'); ?></label></td>
          <td>
            <p class="description">
              <select disabled="true" id="pro[video_ads_default]" >
                <option selected="" value="no">No ad</option>
                <option value="random">Random</option>
                <option value="1">1</option>      
              </select>
              <?php _e('Set which ad should be played before videos.', 'fv-wordpress-flowplayer'); ?>
            </p>
          </td>
        </tr>
        <tr>
          <td style="width:180px"><label for="pro[video_ads_postroll_default]"><?php _e('Default post-roll ad:', 'fv-wordpress-flowplayer'); ?></label></td>
          <td>
            <p class="description">
              <select disabled="true" id="pro[video_ads_postroll_default]" >
                <option selected="" value="no">No ad</option>
                <option value="random">Random</option>
                <option value="1">1</option>      
              </select>
              <?php _e('Set which ad should be played after videos.', 'fv-wordpress-flowplayer'); ?>
            </p>
          </td>
        </tr>
        <tr>
          <td style="width:180px"><label for="pro[video_ads_skip]"><?php _e('Default ad skip time', 'fv-wordpress-flowplayer'); ?>:</label></td>
          <td>
            <p class="description">
              <input disabled="true" class="small" id="pro[video_ads_skip]"  title="<?php _e('Enter value in seconds', 'fv-wordpress-flowplayer'); ?>" type="text" value="5">
              Enter the number of seconds after which an ad can be skipped.
            </p>
          </td>
        </tr>
      </tbody></table>
    <table class="form-table2" style="margin: 5px; ">  
      <tbody><tr>    		
          <td>
            <table id="fv-player-pro_video-ads-settings">
              <thead><tr><td>ID</td><td></td><td>Status</td></tr></thead>
              <tbody>
                <tr class="data">
                  <td class="id">1</td>
                  <td>
                    <table class="fv-player-pro_video-ad-formats">
                      <tbody><tr><td><label><?php _e('Name', 'fv-wordpress-flowplayer'); ?>:</label></td><td colspan="2"><input disabled="true" type="text"  value="" placeholder="<?php _e('Ad name', 'fv-wordpress-flowplayer'); ?>"></td></tr>
                        <tr><td><label><?php _e('Click URL', 'fv-wordpress-flowplayer'); ?>:</label></td><td colspan="2"><input disabled="true" type="text"  value="" placeholder="<?php _e('Clicking the video ad will open the URL in new window', 'fv-wordpress-flowplayer'); ?>"></td></tr>
                        <tr><td><label><?php _e('Video', 'fv-wordpress-flowplayer'); ?>:</label></td><td colspan="2"><input disabled="true" type="text"  value="" placeholder="<?php _e('Enter the video URL here', 'fv-wordpress-flowplayer'); ?>"></td></tr>
                      </tbody></table>
                  </td>
                  <td>
                    <input disabled="true" id="VideoAdDisabled-0" type="checkbox"  value="1"> <label for="VideoAdDisabled-0">Disable</label><br>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>         
        <tr>    		
          <td>
            <input disabled="true" type="button" value="<?php _e('Add more video ads', 'fv-wordpress-flowplayer'); ?>" class="button" id="fv-player-pro_video-ads-add">
          </td>
        </tr>         
      </tbody></table>


  <?php
} 



function fv_flowplayer_admin_skin() {
	global $fv_fp;
?>
<style id="fv-style-preview"></style>
  <div class="flowplayer-wrapper">
    <?php echo do_shortcode('[fvplayer src="https://player.vimeo.com/external/196881410.hd.mp4?s=24645ecff21ff60079fc5b7715a97c00f90c6a18&profile_id=174&oauth2_token_id=3501005" splash="https://i.vimeocdn.com/video/609485450_1280.jpg" autoplay="false" preroll="no" postroll="no" subtitles="'.plugins_url('images/test-subtitles.vtt',dirname(__FILE__)).'" caption="'.__('Hint: play the video to see live preview of the color settings', 'fv-wordpress-flowplayer').'"]'); ?>    
  </div>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
    <tr>
      <td><label for="hasBorder"><?php _e('Border', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input type="hidden" name="hasBorder" value="false" />
          <input type="checkbox" name="hasBorder" id="hasBorder" value="true" <?php if( $fv_fp->_get_option('hasBorder') == 'true' ) echo 'checked="checked"'; ?> 
                 data-fv-preview=".flowplayer{border:%val%px solid!important;}"/>
      </td>
      
    </tr>       
    <tr>
      <td><label for="borderColor"><?php _e('Border color', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="borderColor" name="borderColor" type="text"  value="<?php echo esc_attr( $fv_fp->_get_option('borderColor') ); ?>" 
                 data-fv-preview=".flowplayer{border-color:#%val%!important;}"/></td>
    </tr>
    <tr>
      <td><label for="marginBottom"><?php _e('Bottom Margin', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input id="marginBottom" name="marginBottom" title="<?php _e('Enter value in pixels', 'fv-wordpress-flowplayer'); ?>" type="text" value="<?php echo esc_attr($fv_fp->_get_option('marginBottom')); ?>" 
                 data-fv-preview=".flowplayer { margin: 0 auto %val%px auto!important; display: block!important; }
    .flowplayer.fixed-controls { margin: 0 auto calc(%val%px + 30px) auto!important; display: block!important; }
    .flowplayer.has-abloop { margin-bottom: %val%px!important; }
    .flowplayer.fixed-controls.has-abloop { margin-bottom: calc(%val%px + 30px)!important; }"/></td>      
    </tr>
    <tr>
      <td><label for="bufferColor"><?php _e('Buffer', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="bufferColor" name="bufferColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('bufferColor') ); ?>" 
                 data-fv-preview=".flowplayer .fp-volumeslider, .flowplayer .noUi-background { background-color: #%val% !important; }
                 .flowplayer .fp-buffer, .flowplayer .fv-ab-loop .noUi-handle { background-color: #%val% !important; }" /></td>
    </tr>
    <tr>
      <td><label for="canvas"><?php _e('Canvas', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="canvas" name="canvas" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('canvas') ); ?>" 
                 data-fv-preview=".flowplayer { background-color: #%val%!important; }"/></td>
    </tr>
    <tr>
      <td><label for="backgroundColor"><?php _e('Controlbar', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="backgroundColor" name="backgroundColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('backgroundColor') ); ?>" 
                 data-fv-preview=".flowplayer .fv-ab-loop .noUi-handle  { color:#%val%!important; }
                 .fv_player_popup {  background: #%val%!important;}
                 .fvfp_admin_error_content {  background: #%val%!important; }
                 .flowplayer .fp-controls, .flowplayer .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { background-color: #%val% !important; }"/></td>
    </tr>
    <tr>
      <td><label for="font-face"><?php _e('Font Face', 'fv-wordpress-flowplayer'); ?></label></td>
      <td>
        <?php
        $value = $fv_fp->_get_option('font-face');
        ?>
        <select id="font-face" name="font-face" data-fv-preview="#content .flowplayer, .flowplayer { font-family: %val%; }">
          <option value="inherit"<?php if( $value == 'inherit'  ) echo ' selected="selected"'; ?>><?php _e('(inherit from template)', 'fv-wordpress-flowplayer'); ?></option>
          <option value="&quot;Courier New&quot;, Courier, monospace"<?php if( $value == "\"Courier New\", Courier, monospace" ) echo ' selected="selected"'; ?>>Courier New</option>										  
          <option value="Tahoma, Geneva, sans-serif"<?php if( $value == "Tahoma, Geneva, sans-serif" ) echo ' selected="selected"'; ?>>Tahoma, Geneva</option>          
        </select>
      </td>      
    </tr>
    <tr>
      <td class="second-column"><label for="player-position"><?php _e('Player position', 'fv-wordpress-flowplayer'); ?></label></td>
      <td>
        <select id="player-position" name="player-position" data-fv-preview=".flowplayer { margin-left: 0; }">
          <option value=""<?php if( $fv_fp->_get_option('player-position') == "" ) echo ' selected="selected"'; ?>><?php _e('Centered', 'fv-wordpress-flowplayer'); ?></option>
          <option value="left"<?php if( $fv_fp->_get_option('player-position') == 'left'  ) echo ' selected="selected"'; ?>><?php _e('Left (no text-wrap)', 'fv-wordpress-flowplayer'); ?></option>
        </select>
      </td>      
    </tr>
    <tr>
      <td><label for="progressColor"><?php _e('Progress', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="progressColor" name="progressColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('progressColor') ); ?>" 
           data-fv-preview=".flowplayer .fp-volumelevel { background-color: #%val%!important; }  
          .flowplayer .fp-progress, .flowplayer .fv-ab-loop .noUi-connect, .fv-player-buttons a.current { background-color: #%val% !important; }
          .flowplayer .fp-dropdown li.active { background-color: #%val% !important }"/></td>
    </tr>
    <tr>
      <td><label for="timeColor"><?php _e('Time', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="timeColor" name="timeColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('timeColor') ); ?>" 
                 data-fv-preview=".flowplayer .fp-elapsed { color: #%val% !important; } 
                 .fv-wp-flowplayer-notice-small { color: #%val% !important; }"/></td>
    </tr>
    <tr>
      <td><label for="durationColor"><?php _e('Total time', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="durationColor" name="durationColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('durationColor') ); ?>" 
                 data-fv-preview=".flowplayer .fp-controls, .flowplayer .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { color:#%val% !important; }"/></td>
    </tr>
    <tr>    		
      <td colspan="2">        
        <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
      </td>
    </tr>
  </table>
  <div style="clear: both"></div>
<?php
}


function fv_flowplayer_admin_skin_playlist() {
	global $fv_fp;
?>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
    <tr>
      <td><label for="playlistBgColor"><?php _e('Background Color', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="playlistBgColor" name="playlistBgColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistBgColor') ); ?>" 
                 data-fv-preview=".fp-playlist-external > a > span { background-color:#%val%; }"/></td>
    </tr>
    <tr>
      <td><label for="playlistSelectedColor"><?php _e('Active Item', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="playlistSelectedColor" name="playlistSelectedColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistSelectedColor') ); ?>" 
                 data-fv-preview=".fp-playlist-external > a.is-active > span { border-color:#%val%; }
    .fp-playlist-external a.is-active { color:#%val%; }"/></td>
    </tr>
    <tr>              
      <td><label for="playlistFontColor-proxy"><?php _e('Font Color', 'fv-wordpress-flowplayer'); ?></label></td>
        <?php $bShowPlaylistFontColor = ( $fv_fp->_get_option('playlistFontColor') && $fv_fp->_get_option('playlistFontColor') !== '#' ); ?>
      <td>
        <input class="color" id="playlistFontColor-proxy" data-previous="" <?php echo $bShowPlaylistFontColor?'':'style="display:none;"'; ?> type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistFontColor') ); ?>" />
        <input id="playlistFontColor" name="playlistFontColor" type="hidden" value="<?php echo esc_attr( $fv_fp->_get_option('playlistFontColor') ); ?>" 
               data-fv-preview=".fp-playlist-external > a { color:#%val%; }"/> 
        <a class="playlistFontColor-show" <?php echo $bShowPlaylistFontColor ? 'style="display:none;"' : ''; ?>><?php _e('Use custom color', 'fv-wordpress-flowplayer'); ?></a>
        <a class="playlistFontColor-hide" <?php echo $bShowPlaylistFontColor ? '' : 'style="display:none;"'; ?>><?php _e('Inherit from theme', 'fv-wordpress-flowplayer'); ?></a>
      </td>      
    </tr>    
    <tr>    		
      <td colspan="2">
        <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
      </td>
    </tr>
  </table>
  <div style="clear: both"></div>
<?php
}


function fv_flowplayer_admin_skin_subtitles() {
	global $fv_fp;
?>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
    <tr>  
      <td><label for="subtitle-font-face"><?php _e('Font Face', 'fv-wordpress-flowplayer'); ?></label></td>
      <td>
        <select id="subtitle-font-face" name="subtitleFontFace" data-fv-preview=".flowplayer .fp-subtitle .fp-subtitle-line { font-family: %val%!important; }">
          <option value="inherit"<?php if( $fv_fp->_get_option('subtitleFontFace') == 'inherit'  ) echo ' selected="selected"'; ?>><?php _e('(inherit)', 'fv-wordpress-flowplayer'); ?></option>          
          <option value="&quot;Courier New&quot;, Courier, monospace"<?php if( $fv_fp->_get_option('subtitleFontFace') == "\"Courier New\", Courier, monospace" ) echo ' selected="selected"'; ?>>Courier New</option>										  
          <option value="Tahoma, Geneva, sans-serif"<?php if( $fv_fp->_get_option('subtitleFontFace') == "Tahoma, Geneva, sans-serif" ) echo ' selected="selected"'; ?>>Tahoma, Geneva</option>          
        </select>
      </td>   
    </tr>    
    <tr>
      <td><label for="subtitleSize"><?php _e('Font Size', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input id="subtitleSize" name="subtitleSize" title="<?php _e('Enter value in pixels', 'fv-wordpress-flowplayer'); ?>" type="text" value="<?php echo ( $fv_fp->_get_option('subtitleSize') ); ?>"
                 data-fv-preview=".flowplayer .fp-subtitle .fp-subtitle-line { font-size: %val%px!important; }"/></td>
    </tr>
    <tr>
      <td><label for="subtitleBgColor"><?php _e('Background Color', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="subtitleBgColor" name="subtitleBgColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('subtitleBgColor' ) ); ?>"
                 data-fv-preview=".flowplayer .fp-subtitle .fp-subtitle-line { background-color: rgba(%val%)!important; }"/></td>
    </tr>    
    <tr>
      <td><label for="subtitleBgAlpha"><?php _e('Background Opacity', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input id="subtitleBgAlpha" name="subtitleBgAlpha" type="range" min="0" max="1" step="0.01"  value="<?php echo esc_attr( $fv_fp->_get_option('subtitleBgAlpha') ); ?>"
                 data-fv-preview=""/></td>
    </tr>
    <tr>    		
      <td colspan="2">
        <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
      </td>
    </tr>
  </table>
  <div id="fp-preview-wrapper">
    <div class="flowplayer"id="preview"> 
      <div class="fp-subtitle fp-active">
        <span class="fp-subtitle-line">
          <?php _e('The quick brown fox jumps over the lazy dog.', 'fv-wordpress-flowplayer'); ?>
        </span>
      </div>
    </div>
  </div>
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
								<p><?php _e('Illustrated user guides', 'fv-wordpress-flowplayer'); ?>:</p>
								<div class="clear"></div>
								<ul>
									<li><a target="_blank" href="https://foliovision.com/player/basic-setup/start-up-guide#insert-videos"><?php _e('Inserting videos', 'fv-wordpress-flowplayer'); ?></a>
									<li><a target="_blank" href="https://foliovision.com/player/basic-setup/start-up-guide"><?php _e('License key and custom logo', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/basic-setup/creating-playlists"><?php _e('How to create a playlist', 'fv-wordpress-flowplayer'); ?></a></li>
									<li><a target="_blank" href="https://foliovision.com/player/ads"><?php _e('Using ads', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/ads/incorporating-google-adsense"><?php _e('Using Google Ads', 'fv-wordpress-flowplayer'); ?></a></li>
									<li><a target="_blank" href="https://foliovision.com/player/video-hosting/securing-your-video/rtmp-streams"><?php _e('RTMP streams', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/advanced/subtitles"><?php _e('Subtitles', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/video-hosting/secure-amazon-s3-guide"><?php _e('Amazon S3 secure content guide', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/video-hosting/securing-your-video/hls-stream"><?php _e('How to setup a HLS stream', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/advanced/google-analytics-flowplayer"><?php _e('Google Analytics support', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/basic-setup/using-lightbox"><?php _e('Video lightbox', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/video-hosting/youtube-with-fv-player"><?php _e('YouTube integration', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/basic-setup/setting-quality-switching"><?php _e('Quality Switching', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/video-hosting/how-to-use-vimeo"><?php _e('Vimeo integration', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/ads/using-preroll-postroll-ads"><?php _e('Custom video ads', 'fv-wordpress-flowplayer'); ?></a></li>
                  <li><a target="_blank" href="https://foliovision.com/player/video-hosting/serving-private-cloudfront"><?php _e('CloudFront CDN - protected video downloads', 'fv-wordpress-flowplayer'); ?></a></li>
								</ul>
							</div>
							<div class="column">
								<div class="icon32" id="icon-tools"><br></div>							
								<p><?php _e('Troubleshooting', 'fv-wordpress-flowplayer'); ?>:</p>
								<div class="clear"></div>
								<ul>
									<li><a target="_blank" href="https://foliovision.com/player/basic-setup/installation"><?php _e('Automated checks', 'fv-wordpress-flowplayer'); ?></a></li>
									<li><a target="_blank" href="https://foliovision.com/player/basic-setup/encoding"><?php _e('Video encoding tips', 'fv-wordpress-flowplayer'); ?></a></li>
									<li><a target="_blank" href="https://foliovision.com/player/basic-setup/encoding#flash-only"><?php _e('Video formats to avoid', 'fv-wordpress-flowplayer'); ?></a></li>		
									<li><a target="_blank" href="https://foliovision.com/player/video-hosting/secure-amazon-s3-guide/fix-amazon-mime-type"><?php _e('Fixing mime type on Amazon S3', 'fv-wordpress-flowplayer'); ?></a></li>		
									<li><a target="_blank" href="https://foliovision.com/player/basic-setup/faq"><?php _e('Plugin FAQ', 'fv-wordpress-flowplayer'); ?></a></li>									
									<li><a target="_blank" href="http://foliovision.com/support/fv-wordpress-flowplayer/"><?php _e('Support forums', 'fv-wordpress-flowplayer'); ?></a></li>										
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

/* TABS */
$fv_player_aSettingsTabs = array(
  array('id' => 'fv_flowplayer_settings',           'hash' => 'tab_basic',    	'name' => __('Setup', 'fv-wordpress-flowplayer') ),
  array('id' => 'fv_flowplayer_settings_skin',      'hash' => 'tab_skin',     	'name' => __('Skin', 'fv-wordpress-flowplayer') ),
  array('id' => 'fv_flowplayer_settings_hosting',   'hash' => 'tab_hosting',  	'name' => __('Hosting', 'fv-wordpress-flowplayer') ),
  array('id' => 'fv_flowplayer_settings_actions',   'hash' => 'tab_actions',  	'name' => __('Actions', 'fv-wordpress-flowplayer') ),
  array('id' => 'fv_flowplayer_settings_video_ads',	'hash' => 'tab_video_ads', 	'name' => __('Video Ads', 'fv-wordpress-flowplayer') ),
  array('id' => 'fv_flowplayer_settings_help',      'hash' => 'tab_help',     	'name' => __('Help', 'fv-wordpress-flowplayer') ),
);

//unset video ads tab for Legacy PRO player
if(version_compare( str_replace( '.beta','',get_option( 'fv_player_pro_ver' ) ),'0.7.23') == -1){
  unset($fv_player_aSettingsTabs[4]);
  $fv_player_aSettingsTabs = array_merge($fv_player_aSettingsTabs,array());
}


$fv_player_aSettingsTabs = apply_filters('fv_player_admin_settings_tabs',$fv_player_aSettingsTabs);

/* Setup tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description', 'fv_flowplayer_settings', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_interface_options', __('Post Interface Options', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_interface_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_default_options', __('Sitewide Flowplayer Defaults', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_default_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_integrations', __('Integrations/Compatibility', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_integrations', 'fv_flowplayer_settings', 'normal' );
if( !class_exists('FV_Player_Pro') ) {
  add_meta_box( 'fv_player_pro', __('Pro Features', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_pro', 'fv_flowplayer_settings', 'normal', 'low' );
}

/* Skin Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_skin', 'fv_flowplayer_settings_skin', 'normal', 'high' );
add_meta_box( 'flowplayer-wrapper', __('Player Skin', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_skin', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_playlist', __('Playlist', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_skin_playlist', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_subtitles', __('Subtitles', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_skin_subtitles', 'fv_flowplayer_settings_skin', 'normal' );

/* Hosting Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_hosting', 'fv_flowplayer_settings_hosting', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_amazon_options', __('Amazon S3 Protected Content', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_amazon_options', 'fv_flowplayer_settings_hosting', 'normal' );

/* Actions Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_actions', 'fv_flowplayer_settings_actions', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_popups', __('Popups'), 'fv_flowplayer_admin_popups' , 'fv_flowplayer_settings_actions', 'normal' );
add_meta_box( 'fv_flowplayer_ads', __('Ads', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_ads', 'fv_flowplayer_settings_actions', 'normal' );

/* Video Ads Tab */
if( !class_exists('FV_Player_Pro') ) {
  add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_video_ads', 'fv_flowplayer_settings_video_ads', 'normal', 'high' );
  add_meta_box( 'fv_flowplayer_ads', __('Video Ads', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_video_ads', 'fv_flowplayer_settings_video_ads', 'normal' );
}

/* Help tab */
add_meta_box( 'fv_flowplayer_usage', __('Usage', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_usage', 'fv_flowplayer_settings_help', 'normal', 'high' );

?>

<div class="wrap">
	<div style="position: absolute; margin-top: 10px; right: 10px;">
		<a href="https://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer" target="_blank" title="<?php _e('Documentation', 'fv-wordpress-flowplayer'); ?>"><img alt="visit foliovision" src="//foliovision.com/shared/fv-logo.png" /></a>
	</div>
  <div>
    <div id="icon-options-general" class="icon32"></div>
    <h2>FV Player</h2>
  </div>
  
  <?php
  do_action('fv_player_settings_pre');
  
  if( isset($_GET['fv_flowplayer_checker'] ) ) {
    do_action('fv_flowplayer_checker_event');
  }
  
  $aCheck = false;
  if( flowplayer::is_licensed() ) {
    $aCheck = get_transient( 'fv_flowplayer_license' );
    $aInstalled = get_option('fv_flowplayer_extension_install');
  }
  
  ?>
  
  <form id="wpfp_options" method="post" action="">
    
    <p id="fv_flowplayer_admin_buttons">
      <?php if( $aCheck && isset($aCheck->valid) && $aCheck->valid ) : ?>
        <?php
        $fv_player_pro_path = fv_flowplayer_get_extension_path('fv-player-pro');      
        if( is_plugin_inactive($fv_player_pro_path) && !is_wp_error(validate_plugin($fv_player_pro_path)) ) : ?>
          <input type="button" class='button fv-license-yellow fv_wp_flowplayer_activate_extension' data-plugin="<?php echo $fv_player_pro_path; ?>" value="<?php _e('Enable the Pro extension', 'fv-wordpress-flowplayer'); ?>" /> <img style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
        <?php elseif( is_plugin_active($fv_player_pro_path) && !is_wp_error(validate_plugin($fv_player_pro_path)) ) : ?>
          <input type="button" class="button fv-license-active" onclick="window.location.href += '&fv_player_pro_installed=yes#fv_player_pro'" value="<?php _e('Pro pack installed', 'fv-wordpress-flowplayer'); ?>" />
        <?php else : ?>
          <input type="submit" class="button fv-license-yellow" value="<?php _e('Install Pro extension', 'fv-wordpress-flowplayer'); ?>" /><?php wp_nonce_field('fv_player_pro_install', 'nonce_fv_player_pro_install') ?>
        <?php endif; ?>
      <?php elseif( !preg_match( '!^\$\d+!', $fv_fp->_get_option('key') ) ) : ?>
        <input type="button" class="button fv-license-inactive" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_license'); return false" value="<?php _e('Apply Pro upgrade', 'fv-wordpress-flowplayer'); ?>" />
      <?php endif; ?>
      <input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_template'); return false" value="<?php _e('Check template', 'fv-wordpress-flowplayer'); ?>" /> 
      <input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_files')" value="<?php _e('Check videos', 'fv-wordpress-flowplayer'); ?>" />
      
      <input type="text" name="key" id="key" placeholder="<?php _e('Commercial License Key', 'fv-wordpress-flowplayer'); ?>" value="<?php echo esc_attr( $fv_fp->_get_option('key') ); ?>" /> <a title="<?php _e('Click here for license info', 'fv-wordpress-flowplayer'); ?>" target="_blank" href="https://foliovision.com/player/download"><span class="dashicons dashicons-editor-help"></span></a>
      
      <img class="fv_wp_flowplayer_check_license-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /> 
      <img class="fv_wp_flowplayer_check_template-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /> 
      <img class="fv_wp_flowplayer_check_files-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
      <?php do_action('fv_flowplayer_admin_buttons_after'); ?>
    </p>
    <div id="fv_flowplayer_admin_notices">
    </div> 
    <div id="fv_flowplayer_admin_tabs">
      <h2 class="fv-nav-tab-wrapper nav-tab-wrapper">
        <?php foreach($fv_player_aSettingsTabs as $key => $val):?>
        <a href="#postbox-container-<?php echo $val['hash'];?>" class="nav-tab<?php if( $key == 0 ) : ?> nav-tab-active<?php endif; ?>" style="outline: 0px;"><?php _e($val['name'],'fv-wordpress-flowplayer');?></a>
        <?php endforeach;?>
        <div id="fv_player_js_warning" style=" margin: 8px 40px; display: inline-block; color: darkgrey;" >There Is a Problem with JavaScript.</div>
      </h2>
    </div>
    
    <?php if( preg_match( '!^\$\d+!', $fv_fp->_get_option('key') ) || apply_filters('fv_player_skip_ads',false) ) : ?>    
    <?php else : ?>
      <div id="fv_flowplayer_ad">
        <div class="text-part">
          <h2>FV Wordpress<strong>Flowplayer</strong></h2>
          <span class="red-text"><?php _e('with your own branding', 'fv-wordpress-flowplayer'); ?></span>
            <ul>
            <li><?php _e('Put up your own logo', 'fv-wordpress-flowplayer'); ?></li>
            <li><?php _e('Or remove the logo completely', 'fv-wordpress-flowplayer'); ?></li>
            <li><?php _e('The best video plugin for Wordpress', 'fv-wordpress-flowplayer'); ?></li>
            </ul>
              <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/download" class="red-button"><strong><?php _e('Easter sale!', 'fv-wordpress-flowplayer'); ?></strong><br /><?php _e('All Licenses 20% Off', 'fv-wordpress-flowplayer'); ?></a></p>
          </div>
          <div class="graphic-part">
            <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy">
            <img width="297" height="239" border="0" src="<?php echo flowplayer::get_plugin_url().'/images/fv-wp-flowplayer-led-monitor.png' ?>"> </a>
          </div>
      </div>
    <?php endif; ?>	
  
		<div id="dashboard-widgets" class="metabox-holder fv-metabox-holder columns-1">
      <?php foreach($fv_player_aSettingsTabs as $key => $val):?>
      <div id='postbox-container-<?php echo $val['hash']; ?>' class='postbox-container'<?php if( $key > 0 ) : ?> style=""<?php endif; ?>>    
				<?php
				do_meta_boxes($val['id'], 'normal', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order-nonce', 'meta-box-order-nonce', false );
				?>
			</div>
      <?php endforeach;?>
      <div style="clear: both"></div>
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
      try {
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
        
      } catch(err) {
        jQuery('#fv_flowplayer_admin_notices').append( jQuery('#wpbody', response ) );
        
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
      jQuery(button).siblings('img').eq(0).show();
      
      var button = this;
      jQuery.post( ajaxurl, { action: 'fv_wp_flowplayer_activate_extension', nonce: '<?php echo wp_create_nonce( 'fv_wp_flowplayer_activate_extension' ); ?>', plugin: jQuery(this).attr("data-plugin") }, function( response ) {
        jQuery(button).siblings('img').eq(0).hide();
        
        var obj;
        try {
          response = response.replace( /[\s\S]*<FVFLOWPLAYER>/, '' );
          response = response.replace( /<\/FVFLOWPLAYER>[\s\S]*/, '' );
          obj = jQuery.parseJSON( response );

          jQuery(button).removeClass('fv_wp_flowplayer_activate_extension');
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
        jQuery(button).siblings('img').eq(0).hide();
        jQuery(button).after('<p>Error!</p>');
      });  
    } );
    
    jQuery('.fv-flowplayer-admin-addon-installed').click( function() {
      jQuery('html, body').animate({
          scrollTop: jQuery("#"+jQuery(this).attr("data-plugin") ).offset().top
      }, 1000);
    } );
    
    jQuery('.show-more').click( function(e) {
      e.preventDefault();
      
      var more = jQuery('.more', jQuery(this).parent() ).length ? jQuery('.more', jQuery(this).parent() ) : jQuery(this).parent().siblings('.more');
      
      more.toggle();
      
      if( jQuery(':visible', more ).length > 0 ) {
        jQuery(this).html('(hide)');
      } else {
        jQuery(this).html('(&hellip;)');
      }      
    } );  
    
    /*
     * Coor Picker Default  
     */	
    jQuery('.playlistFontColor-show').click(function(e){
      e.preventDefault();
      jQuery(e.target).hide();
      jQuery('.playlistFontColor-hide').show();

      jQuery('#playlistFontColor-proxy').show().val(jQuery('#playlistFontColor-proxy').data('previous'));
      jQuery('#playlistFontColor').val(jQuery('#playlistFontColor-proxy').data('previous'));
    });

    jQuery('.playlistFontColor-hide').click(function(e){
      e.preventDefault();
      jQuery(e.target).hide();
      jQuery('.playlistFontColor-show').show();

      jQuery('#playlistFontColor-proxy').data('previous',jQuery('#playlistFontColor-proxy').hide().val()).val('');
      jQuery('#playlistFontColor').val('');
    }); 

    jQuery('#playlistFontColor-proxy').on('change',function(e){
      jQuery('#playlistFontColor').val(jQuery(e.target).val());
    });
  });
	//]]>
</script>

<script>
/* TABS */  
jQuery(document).ready(function(){
  jQuery('#fv_player_js_warning').hide();
  
  var anchor = window.location.hash.substring(1);
  if( !anchor || !anchor.match(/tab_/) ) {
    anchor = 'postbox-container-tab_basic';
  }
  
  jQuery('#fv_flowplayer_admin_tabs .nav-tab').removeClass('nav-tab-active');
  jQuery('[href=#'+anchor+']').addClass('nav-tab-active');
  jQuery('#dashboard-widgets .postbox-container').hide();
  jQuery('#' + anchor).show();
});
jQuery('#fv_flowplayer_admin_tabs a').on('click',function(e){
  e.preventDefault();
  window.location.hash = e.target.hash;
  var anchor = jQuery(this).attr('href').substring(1);
  jQuery('#fv_flowplayer_admin_tabs .nav-tab').removeClass('nav-tab-active');
  jQuery('[href=#'+anchor+']').addClass('nav-tab-active');
  jQuery('#dashboard-widgets .postbox-container').hide();
  jQuery('#' + anchor).show();
});  


jQuery('a.fv-settings-anchor').on('click',function(e){
  var id = jQuery(this).attr('href');
  if( id.match(/^#./) ){
    var el = jQuery(id);
    if(el.length){
      var tab = el.parents('.postbox-container').attr('id');
      jQuery('#fv_flowplayer_admin_tabs').find('a[href=#'+tab+']').click()
    }
  }
});




</script>
