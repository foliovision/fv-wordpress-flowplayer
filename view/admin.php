<?php
/*  FV Wordpress Flowplayer - HTML5 video player    
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

// Set the FV Player wp-admin menu item to be open
?>
<script>
var fv_player_menu_item = document.getElementById('toplevel_page_fv_player');
if( fv_player_menu_item && fv_player_menu_item.classList ) {
  fv_player_menu_item.classList.add('wp-menu-open');
  fv_player_menu_item.classList.add('wp-has-current-submenu');
  fv_player_menu_item.classList.remove('wp-not-current-submenu');
}
</script>
<?php

/**
 * Displays administrator backend.
 */


delete_option('fv_wordpress_flowplayer_deferred_notices');

function fv_flowplayer_admin_ads() {
	global $fv_fp;
  $lines = substr_count( $fv_fp->_get_option('ad'), "\n" ) + 2;
?>
					<table class="form-table2">	
						<tr>
							<td colspan="2">
								<label for="ad"><?php _e('Default Ad Code', 'fv-wordpress-flowplayer'); ?>:</label><br />
								<textarea id="ad" name="ad" class="large-text code" rows="<?php echo $lines; ?>"><?php echo esc_textarea($fv_fp->_get_option('ad')); ?></textarea>			
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
									jQuery('#ad_css_select').on('change', function() {
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
          <?php
          $can_use_aws_sdk = version_compare(phpversion(),'7.2.5') != -1;
          
          $fv_fp->_get_checkbox( array(
            'name' => __('Amazon S3 Browser', 'fv-wordpress-flowplayer').' (beta)',
            'key' => 's3_browser',
            'help' =>  !$can_use_aws_sdk ?
              __('This function requires PHP >= 7.2.5, please contact your web host support.' , 'fv-wordpress-flowplayer')
              : __('Show Amazon S3 Browser in the "Add Video" dialog.' , 'fv-wordpress-flowplayer'),
            'disabled' => !$can_use_aws_sdk
          ) ); ?>
          
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
                <option value=""><?php _e('Select the region', 'fv-wordpress-flowplayer'); ?></option><?php

                foreach (fv_player_get_aws_regions() as $aws_region_id => $aws_region_name) {
                  ?>
                  <option value="<?php echo $aws_region_id; ?>"<?php if( $sRegion == $aws_region_id ) echo " selected"; ?>><?php echo $aws_region_name; ?></option>
                  <?php
                }

                ?>
              </select>
            </td>
        </tr>
        <tr<?php echo $amazon_tr_class; ?>>
            <td><label for="amazon_key[]"><?php _e('Access Key ID', 'fv-wordpress-flowplayer'); ?>:</label></td>
            <td><input id="amazon_key[]" name="amazon_key[]" type="text" value="<?php echo esc_attr( $fv_fp->_get_option( array( 'amazon_key', $key ) ) ); ?>" /></td>
        </tr>
        <tr<?php echo $amazon_tr_class; ?>>
        <?php 
          $secret = !function_exists('FV_Player_Pro') ||
          ( function_exists('FV_Player_Pro') && version_compare( str_replace( '.beta','',FV_Player_Pro()->version ),'7.5.25.728', '>=') );
          
          $secret_key = "_is_secret_amazon_secret[]";
          $val = $fv_fp->_get_option( array( 'amazon_secret', $key ) ) ; 

          if( $secret ) {
            $censored_val = $fv_fp->_get_censored_val( $val );
            $val = '';
          }
  
        ?>
            <td><label for="amazon_secret[]"><?php _e('Secret Access Key', 'fv-wordpress-flowplayer'); ?>:</label></td>
            <td><input  <?php if($secret && !empty($censored_val)) echo 'style="display: none;"' ?> id="amazon_secret[]" name="amazon_secret[]" type="text" value="<?php echo esc_attr( $val ); ?>" />
          <?php if( $secret ): ?>
            <input name="<?php echo esc_attr($secret_key); ?>" value="<?php if(empty($censored_val)) {echo '0';} else {echo '1';} ?>" type="hidden" />
            <?php if(!empty($censored_val)): ?>
              <code class="secret-preview"><?php echo $censored_val; ?></code>
              <a href="#" data-is-empty="0" data-setting-change="<?php echo esc_attr($secret_key.'-index-'.$key); ?>" >Change</a>
            <?php endif; ?>
          <?php endif; ?>
          </td>
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
              <a class="button fv-help-link" href="https://foliovision.com/player/video-hosting/amazon-s3-guide" target="_blank">Help</a>
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
            <tr>
              <td class="first"><label for="autoplay"><?php _e('Autoplay', 'fv-wordpress-flowplayer'); ?>:</label></td>
              <td colspan="3">
                <p class="description">
                  <?php
                  // in the older FV Player versions this setting was just true/false and that creates a ton of issues
                  $value = $fv_fp->_get_option('autoplay');
                  ?>
                  <select id="autoplay" name="autoplay">
                    <option value="true"   <?php if( $value === 'true' || $value ) echo ' selected="selected"'; ?> >Yes</option>
                    <option value="false"  <?php if( $value === 'false' || !$value ) echo ' selected="selected"'; ?> >No</option>
                    <option value="muted"   <?php if ( $value === 'muted' )  echo ' selected="selected"'; ?> >Muted</option>
                  </select>
                  <?php _e('We make sure only one video per page autoplays. Mobile devices only support Muted autoplay.', 'fv-wordpress-flowplayer'); ?>
                </p>
              </td>
            </tr>
            
            <?php $fv_fp->_get_checkbox(__('Preloading', 'fv-wordpress-flowplayer'), 'preload', __('Works for the first video on the page. YouTube and Vimeo not supported.', 'fv-wordpress-flowplayer') ); ?>
            <?php $fv_fp->_get_checkbox(__('Controlbar Always Visible', 'fv-wordpress-flowplayer'), 'show_controlbar' ); ?>
            <tr>
              <td><label for="width"><?php _e('Default Video Size', 'fv-wordpress-flowplayer'); ?>:</label></td>
              <td>
                <p class="description">
                  <label for="width"><?php _e('Width', 'fv-wordpress-flowplayer'); ?>:</label>&nbsp;<input type="text" class="small" name="width" id="width" value="<?php echo $fv_fp->_get_option('width'); ?>" />
                  <label for="height"><?php _e('Height', 'fv-wordpress-flowplayer'); ?>:</label>&nbsp;<input type="text" class="small" name="height" id="height" value="<?php echo $fv_fp->_get_option('height'); ?>" />
                  <?php _e('Enter values in pixels or 100%.', 'fv-wordpress-flowplayer'); ?>
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
            <?php $fv_fp->_get_checkbox(__('Disable Video Links', 'fv-wordpress-flowplayer'), 'disable_video_hash_links', __('Removes the "Link" item to the top bar.', 'fv-wordpress-flowplayer'), __("Clicking the video Link gives your visitors a link to the exact place in the video they are watching. If the post access is restricted, it won't make the video open to public.", 'fv-wordpress-flowplayer') ); ?>
            <?php $fv_fp->_get_checkbox(__('Enable Chromecast', 'fv-wordpress-flowplayer'), 'chromecast', __('Adds support for Google Chromecast.', 'fv-wordpress-flowplayer') ); ?>

            <?php if( $fv_fp->_get_option('rtmp') ) : ?>
              <tr>
                <td><label for="rtmp"><?php _e('Flash Streaming Server (deprecated)', 'fv-wordpress-flowplayer'); ?>:</label></td>
                <td>
                  <p class="description">
                    <input type="text" name="rtmp" id="rtmp" value="<?php echo esc_attr( $fv_fp->_get_option('rtmp') ); ?>" placeholder="<?php _e('Enter your default RTMP streaming server (Amazon CloudFront domain).', 'fv-wordpress-flowplayer'); ?>" />
                  </p>
                </td>
              </tr>
            <?php endif; ?>

            <?php $fv_fp->_get_checkbox(__('Force HD Streaming', 'fv-wordpress-flowplayer'), 'hd_streaming', __('Use HD quality for HLS/MPEG-DASH even on slow connections.', 'fv-wordpress-flowplayer'), __( 'User can still switch to lower quality by hand. Doesn\'t work on iPhones.', 'fv-wordpress-flowplayer') ); ?>

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

                <input type="text"  name="logo" id="logo" value="<?php echo esc_attr( $fv_fp->_get_option('logo') ); ?>" class="large" placeholder="<?php  _e('Paste logo url or upload image to show custom logo on player.', 'fv-wordpress-flowplayer'); ?>"/>
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
            
            <tr>
              <td><label for="matomo_domain"><?php _e('Matomo/Piwik Tracking', 'fv-wordpress-flowplayer'); ?>:</label></td>
              <td>
                <p class="description">
                  <input type="text" name="matomo_domain" id="matomo_domain" value="<?php echo esc_attr( $fv_fp->_get_option('matomo_domain') ); ?>" placeholder="<?php _e('matomo.your-domain.com', 'fv-wordpress-flowplayer'); ?>" class="large" />
                  <input type="text" name="matomo_site_id" id="matomo_site_id" value="<?php echo esc_attr( $fv_fp->_get_option('matomo_site_id') ); ?>" placeholder="<?php _e('Site ID', 'fv-wordpress-flowplayer'); ?>" class="small" />
                </p>
              </td>
            </tr>

            <?php $fv_fp->_get_checkbox(__('Multiple video playback', 'fv-wordpress-flowplayer'), 'multiple_playback', __('Allows multiple players to play at once. Only one player remains audible.', 'fv-wordpress-flowplayer') ); ?>
            
            <?php $fv_fp->_get_checkbox(__('No Picture Button', 'fv-wordpress-flowplayer'), 'ui_no_picture_button', __('Adds a button to turn the video picture on and off.', 'fv-wordpress-flowplayer') ); ?>

            <tr>
              <td class="first"><label for="sticky_video">Play Icon:</label></td>
              <td>
                <p class="description">
                <input type="text"  name="play_icon" id="play_icon" value="<?php echo esc_attr( $fv_fp->_get_option('play_icon') ); ?>" class="large" placeholder="<?php  _e('The big play icon on top of the player. Recommended size is 168 pixels.', 'fv-wordpress-flowplayer'); ?>"/>
                <input id="upload_image_button" class="upload_image_button button no-margin small" type="button" value="<?php _e('Upload Icon', 'fv-wordpress-flowplayer'); ?>" />
                </p>
              </td>
            </tr>

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
                    <option value="slider"    <?php if( $value == 'slider' ) echo ' selected="selected"'; ?> ><?php _e('Slider', 'fv-wordpress-flowplayer'); ?></option>
                    <option value="season"    <?php if( $value == 'season' ) echo ' selected="selected"'; ?> ><?php _e('Vertical Season', 'fv-wordpress-flowplayer'); ?></option>
                    <option value="polaroid"  <?php if( $value == 'polaroid' ) echo ' selected="selected"'; ?> ><?php _e('Polaroid', 'fv-wordpress-flowplayer'); ?></option>
                    <option value="text"  <?php if( $value == 'text' ) echo ' selected="selected"'; ?> ><?php _e('Text', 'fv-wordpress-flowplayer'); ?></option>
                  </select>
                  <?php _e('Enter your default playlist style here', 'fv-wordpress-flowplayer'); ?>
                </p>
              </td>
            </tr>

            <?php $fv_fp->_get_checkbox(__('Popup Box', 'fv-wordpress-flowplayer'), 'popupbox', __('Shows a generic "Would you like to replay the video?" message at the end of each video.', 'fv-wordpress-flowplayer') ); ?>
            
            <?php $fv_fp->_get_checkbox(__('Repeat Button', 'fv-wordpress-flowplayer'), 'ui_repeat_button', __('Adds a button to set playlist/track repeat and shuffle.', 'fv-wordpress-flowplayer') ); ?>
            
            <?php $fv_fp->_get_checkbox(__('Rewind/Forward Button', 'fv-wordpress-flowplayer'), 'ui_rewind_button', __('Adds a button to go 10 seconds back/forth.', 'fv-wordpress-flowplayer') ); ?>

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
                <a class="button fv-help-link" href="https://foliovision.com/player/settings/sitewide-fv-player-defaults" target="_blank">Help</a>
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
      });

      //Open the uploader dialog
      fv_flowplayer_uploader.open();

  });

  // no sorting of settings boxes please
  var fv_player_no_sortable = setInterval( function() {
    try {
      jQuery('#normal-sortables').sortable( "disable" )

      clearInterval(fv_player_no_sortable);
    } catch(e) {}
  }, 10 );

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
          <?php _e('Here you can configure ads and banners that will be shown in the video.', 'fv-wordpress-flowplayer'); ?>
        </p>
      </td>
    </tr>
  </table>
<?php
}

/*
 * Actions Tab Description
 */
function fv_flowplayer_admin_description_tools() {
?>
  <table class="form-table">
    <tr>
      <td colspan="4">
        <p>
          <?php _e('Maintenance tools and debug info.', 'fv-wordpress-flowplayer'); ?>
        </p>
        <p>
          Need help with replacing video paths after migrating video from one CDN to another? Try the <a href="<?php echo admin_url('admin.php?page=fv_player_migration'); ?>" class="button">Migration Wizard</a>
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

          <?php $fv_fp->_get_checkbox(__('Disable saving skin CSS to a static file', 'fv-wordpress-flowplayer'), 'css_disable', __('Normally the player CSS configuration is stored in wp-content/fv-flowplayer-custom/style-{blog_id}.css.', 'fv-wordpress-flowplayer'), __('We do this to avoid a big style tag in your site &lt;head&gt;. Don\'t edit this file though, as it will be overwritten by plugin update or saving its options!','fv-wordpress-flowplayer' )); ?>

          <tr>
            <td><label for="css_disable"><?php _e('Enable profile videos', 'fv-wordpress-flowplayer').' (beta)'; ?>:</label></td>
            <td>
              <div class="description">
                <input type="hidden" name="profile_videos_enable_bio" value="false" />
                <input type="checkbox" name="profile_videos_enable_bio" id="profile_videos_enable_bio" value="true" <?php if( $fv_fp->_get_option('profile_videos_enable_bio') ) echo 'checked="checked"'; ?> />
                <?php _e('Check your site carefully after enabling. Videos attached to the user profile will be showing as a part of the user bio.', 'fv-wordpress-flowplayer'); ?> <a href="#" class="show-more">(&hellip;)</a>
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

          <?php $fv_fp->_get_checkbox(__('Handle WordPress audio/video', 'fv-wordpress-flowplayer'), array( 'integrations', 'wp_core_video' ), 'Make sure shortcodes <code><small>[video]</small></code>, <code><small>[audio]</small></code> and <code><small>[playlist]</small></code>, the Gutenberg video block and the YouTube links use FV Player.', '' ); ?>
          <?php $fv_fp->_get_checkbox(__('Load FV Flowplayer JS everywhere', 'fv-wordpress-flowplayer'), 'js-everywhere', __('If you use some special JavaScript integration you might prefer this option.', 'fv-wordpress-flowplayer'), __('Otherwise our JavaScript only loads if the shortcode is found in any of the posts being currently displayed. Required if you load content using Ajax, like in various LMS systems.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Optimize FV Flowplayer JS loading', 'fv-wordpress-flowplayer'), 'js-optimize', __('Helps with Google PageSpeed scores.', 'fv-wordpress-flowplayer'), __('FV Player JavaScript will be only loaded once the user user start to use the page or on video tap.', 'fv-wordpress-flowplayer') ); ?>
					<?php if( $fv_fp->_get_option('parse_commas') ) $fv_fp->_get_checkbox(__('Parse old shortcodes with commas', 'fv-wordpress-flowplayer'), 'parse_commas', __('Older versions of this plugin used commas to sepparate shortcode parameters.', 'fv-wordpress-flowplayer'), __('This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Parse Vimeo and YouTube links', 'fv-wordpress-flowplayer'), 'parse_comments', __('Affects comments, bbPress and BuddyPress. These links will be displayed as videos.', 'fv-wordpress-flowplayer'), __('This option makes most sense together with FV Player Pro as it embeds these videos using FV Player. Enables use of shortcodes in comments and bbPress.', 'fv-wordpress-flowplayer') ); ?>
          <?php if( $fv_fp->_get_option('postthumbnail') ) $fv_fp->_get_checkbox(__('Post Thumbnail', 'fv-wordpress-flowplayer'), 'postthumbnail', __('Setting a video splash screen from the media library will automatically make it the splash image if there is none.', 'fv-wordpress-flowplayer') ); ?>
					<?php if( $fv_fp->_get_option('engine') ) $fv_fp->_get_checkbox(__('Prefer Flash player by default', 'fv-wordpress-flowplayer'), 'engine', __('Provides greater compatibility.', 'fv-wordpress-flowplayer'), __('We use Flash for MP4 files in IE9-10 and M4V files in Firefox regardless of this setting.', 'fv-wordpress-flowplayer') ); ?>
          <?php if( $fv_fp->_get_option('rtmp-live-buffer') ) $fv_fp->_get_checkbox(__('RTMP bufferTime tweak (deprecated)', 'fv-wordpress-flowplayer'), 'rtmp-live-buffer', __('Use if your live streams are not smooth.', 'fv-wordpress-flowplayer'), __('Adobe <a href="http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/net/NetStream.html#bufferTime">recommends</a> to set bufferTime to 0 for live streams, but if your stream is not smooth, you can use this setting.', 'fv-wordpress-flowplayer') ); ?>

          <!--<tr>
            <td style="width: 350px"><label for="optimizepress2">Handle OptimizePress 2 videos (<abbr title="Following attributes are not currently supported: margin, border">?</abbr>):</label></td>
            <td>
              <input type="hidden" name="integrations[optimizepress2]" value="false" />
              <input type="checkbox" name="integrations[optimizepress2]" id="optimizepress2" value="true" <?php if( $fv_fp->_get_option( array( 'integrations', 'optimizepress2' ) ) ) echo 'checked="checked"'; ?> />
            </td>
          </tr>-->       

          <?php do_action('fv_flowplayer_admin_integration_options_after'); ?>
          <tr>
            <td colspan="4">
              <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
              <a class="button fv-help-link" href="https://foliovision.com/player/settings/integrations-compatibility-options" target="_blank">Help</a>
            </td>
          </tr>
        </table>
<?php
}


function fv_flowplayer_admin_mobile() {
  global $fv_fp;
?>
        <table class="form-table2">
          <?php $fv_fp->_get_checkbox(__('Use native fullscreen on mobile', 'fv-wordpress-flowplayer'), 'mobile_native_fullscreen', __('Stops popups, ads or subtitles from working, but provides faster interface. We set this for Android < 4.4 and iOS < 7 automatically.', 'fv-wordpress-flowplayer') ); ?>
          <?php $fv_fp->_get_checkbox(__('Force fullscreen on mobile', 'fv-wordpress-flowplayer'), 'mobile_force_fullscreen', __('Video playback will start in fullscreen. iPhone with iOS < 10 always forces fullscreen for video playback.', 'fv-wordpress-flowplayer')  ); ?>
          <?php
          $fv_fp->_get_checkbox(__('Alternative iPhone fullscreen mode', 'fv-wordpress-flowplayer'), 'mobile_alternative_fullscreen', __("Use if you see site elements such as floating header bar ovelaying the player when in fullscreen.", 'fv-wordpress-flowplayer')  );
          ?>
          <tr>
            <td colspan="4">
              <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
              <a class="button fv-help-link" href="https://foliovision.com/player/settings/mobile-settings-behaviors" target="_blank">Help</a>
            </td>
          </tr>
        </table>
<?php
}


function fv_flowplayer_admin_privacy() {
  global $fv_fp;
?>
        <table class="form-table2">
          <?php $fv_fp->_get_checkbox(__('Disable local storage', 'fv-wordpress-flowplayer'), 'disable_localstorage', __('Remember video position will not work for non logged users. Video volume, mute status and subtitles selection will also not be stored.', 'fv-wordpress-flowplayer') ); ?>
          <tr>
            <td colspan="4">
              <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
              <a class="button fv-help-link" href="https://foliovision.com/2021/12/private-video-no-cookies" target="_blank">Help</a>
            </td>
          </tr>
        </table>
<?php
}


function fv_flowplayer_admin_seo() {
  global $fv_fp;
?>
        <table class="form-table2">
          <?php $fv_fp->_get_checkbox(__('Use Schema.org markup', 'fv-wordpress-flowplayer'), array( 'integrations', 'schema_org' ), __(' Adds the video meta data information for search engines.', 'fv-wordpress-flowplayer') ); ?>
          <?php do_action( 'fv_flowplayer_admin_seo_after'); ?>
          <tr>
            <td colspan="4">
              <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
              <a class="button fv-help-link" href="https://foliovision.com/player/settings/video-seo-schema-xml" target="_blank">Help</a>
              </td>
          </tr>
        </table>
<?php
}


function fv_flowplayer_admin_select_popups($aArgs){

  $aPopupData = apply_filters('fv_player_admin_popups_defaults', get_option('fv_player_popups'));


  $sId = (isset($aArgs['id'])?$aArgs['id']:'popups_default');
  $aArgs = wp_parse_args( $aArgs, array( 'id'=>$sId, 'cva_id'=>'', 'show_default' => false ) );
  ?>
  <select id="<?php echo $aArgs['id']; ?>" name="<?php echo $aArgs['id']; ?>">
    <?php if( $aArgs['show_default'] ) : ?>
      <option>Use site default</option>
    <?php endif; ?>
    <option <?php if( $aArgs['item_id'] == 'no' ) echo 'selected '; ?>value="no"><?php _e('None', 'fv-wordpress-flowplayer'); ?></option>
    <option <?php if( $aArgs['item_id'] == 'random' ) echo 'selected '; ?>value="random"><?php _e('Random', 'fv-wordpress-flowplayer'); ?></option>
    <?php
    if( isset($aPopupData) && is_array($aPopupData) && count($aPopupData) > 0 ) {
      foreach( $aPopupData AS $key => $aPopupAd ) {
        ?><option <?php if( $aArgs['item_id'] == $key ) echo 'selected'; ?> value="<?php echo $key; ?>"><?php
        echo $key;
        if( !empty($aPopupAd['title']) ) echo ' - '.$aPopupAd['title'];
        if( !empty($aPopupAd['name']) ) echo ' - '.$aPopupAd['name'];
        if( !empty($aPopupAd['disabled']) && $aPopupAd['disabled'] == 1 ) echo ' (currently disabled)';
        ?></option><?php
      }
    } ?>
  </select>
  <?php
}


function fv_flowplayer_admin_end_of_video(){
  global $fv_fp;
    ?>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td style="width:150px;vertical-align:top;line-height:2.4em;"><label for="popups_default"><?php _e('Default Popup', 'fv-wordpress-flowplayer'); ?>:</label></td>
        <td>
          <?php $cva_id = $fv_fp->_get_option('popups_default'); ?>          
          <p class="description"><?php fv_flowplayer_admin_select_popups( array('item_id'=>$cva_id,'id'=>'popups_default') ); ?> <?php _e('You can set a default popup here and then skip it for individual videos.', 'fv-wordpress-flowplayer'); ?></p>
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


function fv_flowplayer_admin_popups(){
  global $fv_fp;
    ?>
    <p><?php _e('Add any popups here which you would like to use with multiple videos.', 'fv-wordpress-flowplayer'); ?></p>
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
                      <input id='PopupAdPause-<?php echo $key; ?>' type='checkbox' name='popups[<?php echo $key; ?>][pause]' value='1' <?php echo (isset($aPopup['pause']) && $aPopup['pause'] ? 'checked="checked"' : ''); ?> />
                      <label for='PopupAdPause-<?php echo $key; ?>'><?php _e('Show on pause', 'fv-wordpress-flowplayer'); ?></label><br />
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

    jQuery('#fv-player-popups-add').on('click', function() {
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
          <?php $fv_fp->_get_checkbox(__('Autoplay', 'fv-wordpress-flowplayer'), array('interface', 'autoplay') ); ?>
          <?php $fv_fp->_get_checkbox(__('Controlbar', 'fv-wordpress-flowplayer'), array('interface', 'controlbar') ); ?>
          <?php $fv_fp->_get_checkbox(__('Embed', 'fv-wordpress-flowplayer'), array('interface', 'embed') ); ?>
          <?php $fv_fp->_get_checkbox(__('Mobile Video', 'fv-wordpress-flowplayer'), array('interface', 'mobile') ); ?>
          <?php $fv_fp->_get_checkbox(__('Playlist Auto Advance', 'fv-wordpress-flowplayer'), array('interface', 'playlist_advance') ); ?>
          <?php $fv_fp->_get_checkbox(__('Playlist Style', 'fv-wordpress-flowplayer'), array('interface', 'playlist') ); ?>
          <?php $fv_fp->_get_checkbox(__('Playlist Item Titles', 'fv-wordpress-flowplayer'), array('interface', 'playlist_captions') ); ?>
          <?php $fv_fp->_get_checkbox(__('Sharing Buttons', 'fv-wordpress-flowplayer'), array('interface', 'share') ); ?>
          <?php $fv_fp->_get_checkbox(__('Speed Buttons', 'fv-wordpress-flowplayer'), array('interface', 'speed') ); ?>
          <?php $fv_fp->_get_checkbox(__('Splash Text', 'fv-wordpress-flowplayer'), array('interface', 'splash_text') ); ?>
          <?php $fv_fp->_get_checkbox(__('Sticky', 'fv-wordpress-flowplayer'), array('interface', 'sticky') ); ?>
          <?php $fv_fp->_get_checkbox(__('Synopsis', 'fv-wordpress-flowplayer'), array('interface', 'synopsis') ); ?>
          <?php $fv_fp->_get_checkbox(__('Video Actions', 'fv-wordpress-flowplayer'), array('interface', 'end_actions'), __('Enables end of playlist actions like Loop, Redirect, Show popup and Show splash screen', 'fv-wordpress-flowplayer') ); ?>

          <?php do_action('fv_flowplayer_admin_interface_options_after'); ?>
          
          <tr>
            <td colspan="4">
              <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
              <a class="button fv-help-link" href="https://foliovision.com/player/settings/post-interface-settings" target="_blank">Help</a>
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

function fv_flowplayer_settings_box_conversion() {
  ?>
    <p><?php _e('This section allows you to convert videos posted using other plugins to FV Player shortcodes.', 'fv-wordpress-flowplayer'); ?></p>
    <table class="form-table2" style="margin: 5px; ">
      <?php do_action('fv_player_conversion_buttons'); ?>
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



function fv_flowplayer_admin_skin_get_table($options) {
    global $fv_fp;

    $selected_skin = $fv_fp->_get_option( 'skin' );
?>
    <table class="form-table2 flowplayer-settings fv-player-interface-form-group" id="skin-<?php echo $options['skin_name']; ?>-settings"<?php if (($selected_skin && $selected_skin != $options['skin_radio_button_value']) || (!$selected_skin && $options['default'] !== true)) { echo ' style="display: none"'; } ?>>
      <?php
      $options = apply_filters( 'fv_player_skin_settings', $options );
      
      foreach ($options['items'] as $item) {
        $setup = wp_parse_args( $item, array( 'name' => false, 'data' => false, 'optoins' => false, 'attributes' => false, 'class' => false, 'default' => false ) );

        switch ($item['type']) {
          case 'checkbox':
            $fv_fp->_get_checkbox($setup);
            break;
          case 'input_text':
            $fv_fp->_get_input_text($setup);
            break;
          case 'input_hidden':
            $fv_fp->_get_input_hidden($setup);
            break;
          case 'select':
            $fv_fp->_get_select($setup);
            break;

        }
      }
      ?>
        <tr>
            <td colspan="2">
                <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
            </td>
        </tr>
    </table>
<?php
}



function fv_flowplayer_admin_skin() {
	global $fv_fp;
?>
<style id="fv-style-preview"></style>
  <div class="flowplayer-wrapper">
    <?php
    $fv_fp->admin_preview_player = flowplayer_content_handle( array(
      'src' => 'https://player.vimeo.com/external/196881410.hd.mp4?s=24645ecff21ff60079fc5b7715a97c00f90c6a18&profile_id=174&oauth2_token_id=3501005',
      'splash' => 'https://i.vimeocdn.com/video/609485450-6fc3febe7ce2c2fda919a99c27a9cb904c645dcb944bc53ac7f3a228685305d8-d?mw=1280&mh=720',
      'autoplay' => 'false',
      'preroll' => 'no',
      'postroll' => 'no',
      'subtitles' => plugins_url('images/test-subtitles.vtt',dirname(__FILE__)),
      'caption' => "Foliovision Video;Lapinthrope Extras - Roy Thompson Hall Dance;Romeo and Juliet Ballet Schloss Kittsee",
      'playlist' => 'https://player.vimeo.com/external/224781088.sd.mp4?s=face4dbb990b462826c8e1e43a9c66c6a9bb5585&profile_id=165&oauth2_token_id=3501005,https://i.vimeocdn.com/video/643908843-984e68e66846a7a4b42bf5e854b65937217ed1b71759afa16afd4f81963900a6-d?mw=230&mh=130;https://player.vimeo.com/external/45864857.hd.mp4?s=94fddee594da3258c9e10355f5bad8173c4aee7b&profile_id=113&oauth2_token_id=3501005,https://i.vimeocdn.com/video/319116053-4745c7d678ba90ebabeadf58a65439b780c2ef26176176acc03eabbe87c8afda-d?mw=230&mh=130',
			'liststyle' => 'horizontal',
      'vast' => 'skip'
      ) );
    $fv_fp->admin_preview_player = explode( '<div class="fp-playlist-external', $fv_fp->admin_preview_player );
    echo $fv_fp->admin_preview_player[0];
    ?>
    <?php _e('Hint: play the video to see live preview of the color settings', 'fv-wordpress-flowplayer') ?>
  </div>

  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
    <?php
        // skin change radios
        $fv_fp->_get_radio(array(
          'key' => 'skin',
          'name' => __('Skin', 'fv-wordpress-flowplayer'),
          'style' => 'columns',
          'values' => array(
            'slim' => 'Slim',
            'youtuby' => 'YouTuby',
            'custom' => 'Custom'
          ),
          'default' => 'custom',
          'data' => array(
            'fv-skin' => ''
          )
        ));
    ?>
  </table>

  <?php
  
  $aPreview = array(
    'hasBorder' => '.flowplayer{border:%val%px solid !important;}',
    'bottom-fs' => '',
    'borderColor' => '.flowplayer{border-color:#%val% !important;}',
    'marginBottom' => '.flowplayer { margin: 0 auto %val%px auto !important; display: block !important; }
                .flowplayer.fixed-controls { margin: 0 auto calc(%val%px + 30px) auto !important; display: block !important; }
                .flowplayer.has-abloop { margin-bottom: %val%px !important; }
                .flowplayer.fixed-controls.has-abloop { margin-bottom: calc(%val%px + 30px) !important; }',
    'bufferColor' => '.flowplayer .fp-volumeslider, .flowplayer .noUi-background { background-color: #%val% !important; }
                 .flowplayer .fp-buffer, .flowplayer .fv-ab-loop .noUi-handle { background-color: #%val% !important; }',
    'canvas' => '.flowplayer { background-color: #%val% !important; }',
    'backgroundColor' => '.flowplayer .fv-ab-loop .noUi-handle  { color:#%val% !important; }
                 .fv_player_popup {  background: #%val% !important;}
                 .fvfp_admin_error_content {  background: #%val% !important; }
                 .flowplayer .fp-controls, .flowplayer .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { background-color: #%val% !important; }',
    'font-face' => '#content .flowplayer, .flowplayer { font-family: %val%; }',
    'player-position' => '.flowplayer { margin-left: 0 !important; }',
    'progressColor' => '.flowplayer .fp-volumelevel { background-color: #%val% !important; }
          .flowplayer .fp-progress, .flowplayer .fv-ab-loop .noUi-connect, .fv-player-buttons a.current { background-color: #%val% !important; }
          .flowplayer .fp-dropdown li.active { background-color: #%val% !important }
					.flowplayer .fp-color { background-color: #%val% !important }',
    'timeColor' => '.flowplayer .fp-elapsed, .flowplayer .fp-duration { color: #%val% !important; } 
                  .fv-player-video-checker { color: #%val% !important; }',
    'durationColor' => '.flowplayer .fp-controls, .flowplayer .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { color:#%val% !important; }
                  .flowplayer .fp-controls > .fv-fp-prevbtn:before, .flowplayer .fp-controls > .fv-fp-nextbtn:before { border-color:#%val% !important; }
                  .flowplayer svg.fvp-icon { fill: #%val% !important; }',
    'design-timeline' => '',
    'design-icons' => '',
  );
  
  // slim skin settings
  $aSettings = array(
      array(
        'type'    => 'input_text',
        'key'     => array('skin-slim', 'progressColor'),
        'name'    => __( 'Color', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => 'BB0000',
        'data'    => array( 'fv-preview' => $aPreview['progressColor'] )
      )
    );
  
  foreach( $fv_fp->aDefaultSkins['skin-slim'] AS $k => $v ) {
    $aSettings[] =  array(
        'type'    => 'input_hidden',
        'key'     => array('skin-slim', $k),
        'default' => $v,
        'data'    => array( 'fv-preview' => $aPreview[$k] )
      );
  }
  
  fv_flowplayer_admin_skin_get_table( array(
    'skin_name'               => 'Slim',
    'skin_radio_button_value' => 'slim',
    'default'                 => true,
    'items'                   => $aSettings
  ) );

  // YouTuby skin settings  
  $aSettings = array(
      array(
        'type'    => 'input_text',
        'key'     => array('skin-youtuby', 'progressColor'),
        'name'    => __( 'Color', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => 'BB0000',
        'data'    => array( 'fv-preview' => $aPreview['progressColor'] )
      )
    );
  
  foreach( $fv_fp->aDefaultSkins['skin-youtuby'] AS $k => $v ) {
    $aSettings[] =  array(
        'type'    => 'input_hidden',
        'key'     => array('skin-youtuby', $k),
        'default' => $v,
        'data'    => array( 'fv-preview' => $aPreview[$k] ),
        'attributes' => array( 'readonly' => 'true' )
      );
  }  
  
  fv_flowplayer_admin_skin_get_table( array(
    'skin_name'               => 'YouTuby',
    'skin_radio_button_value' => 'youtuby',
    'default'                 => false,
    'items'                   => $aSettings
  ) );



  // custom skin settings
  fv_flowplayer_admin_skin_get_table( array(
    'skin_name'               => 'Custom',
    'skin_radio_button_value' => 'custom',
    'default' => false,
    'items'                   => array(

      array(
        'type' => 'checkbox',
        'key'  => array('skin-custom', 'hasBorder'),
        'name' => __( 'Border', 'fv-wordpress-flowplayer' ),
        'data'    => array( 'fv-preview' => $aPreview['hasBorder'] )
      ),
      
      array(
        'type' => 'checkbox',
        'key'  => array('skin-custom', 'bottom-fs'),
        'name' => __( 'Controlbar Fullscreen', 'fv-wordpress-flowplayer' ),
        'data'    => array( 'fv-preview' => '' )
      ),      

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'borderColor'),
        'name'    => __( 'Border color', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => '666666',
        'data'    => array( 'fv-preview' => $aPreview['borderColor'] )
      ),

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'marginBottom'),
        'name'    => __( 'Bottom Margin', 'fv-wordpress-flowplayer' ),
        'default' => 2.8,
        'title'   => __( 'Enter value in em', 'fv-wordpress-flowplayer' ),
        'data'    => array( 'fv-preview' => $aPreview['marginBottom'] )
      ),

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'bufferColor'),
        'name'    => __( 'Buffer', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => 'EEEEEE',
        'data'    => array( 'fv-preview' => $aPreview['bufferColor'] )
      ),

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'canvas'),
        'name'    => __( 'Canvas', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => '000000',
        'data'    => array( 'fv-preview' => $aPreview['canvas'] )
      ),

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'backgroundColor'),
        'name'    => __( 'Controlbar', 'fv-wordpress-flowplayer' ),
        'class'   => 'color-opacity',
        'default' => '333333',
        'data'    => array( 'fv-preview' => $aPreview['backgroundColor'] )
      ),

      array(
        'type'    => 'select',
        'key'     => array('skin-custom', 'font-face'),
        'name'    => __( 'Font Face', 'fv-wordpress-flowplayer' ),
        'options' => array(
          'inherit'                                     => __( '(inherit from template)', 'fv-wordpress-flowplayer' ),
          '&quot;Courier New&quot;, Courier, monospace' => 'Courier New',
          'Helvetica, sans-serif'                       => 'Helvetica',
          'Tahoma, Geneva, sans-serif'                  => 'Tahoma, Geneva'
        ),
        'default' => 'Tahoma, Geneva, sans-serif',
        'data'    => array( 'fv-preview' => $aPreview['font-face'] )
      ),

      array(
        'type'           => 'select',
        'key'            => array('skin-custom', 'player-position'),
        'first_td_class' => 'second-column',
        'name'           => __( 'Player position', 'fv-wordpress-flowplayer' ),
        'default'        => '',
        'options'        => array(
          ''     => __( 'Centered', 'fv-wordpress-flowplayer' ),
          'left' => 'Left (no text-wrap)'
        ),
        'data'    => array( 'fv-preview' => $aPreview['player-position'] )
      ),

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'progressColor'),
        'name'    => __( 'Progress', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => 'BB0000',
        'data'    => array( 'fv-preview' => $aPreview['progressColor'] )
      ),

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'timeColor'),
        'name'    => __( 'Time', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => 'EEEEEE',
        'data'    => array( 'fv-preview' => $aPreview['timeColor'] )
      ),

      array(
        'type'    => 'input_text',
        'key'     => array('skin-custom', 'durationColor'),
        'name'    => __( 'Buttons', 'fv-wordpress-flowplayer' ),
        'class'   => 'color',
        'default' => 'EEEEEE',
        'data'    => array( 'fv-preview' => $aPreview['durationColor'] )
      ),

      array(
        'type'           => 'select',
        'key'            => array('skin-custom', 'design-timeline'),
        'first_td_class' => 'second-column',
        'name'           => __( 'Timeline', 'fv-wordpress-flowplayer' ),
        'default'        => ' ',
        'options'        => array(
          ' '          => __( 'Default', 'fv-wordpress-flowplayer' ),
          'fp-slim'    => __( 'Slim', 'fv-wordpress-flowplayer' ),
          'fp-full'    => __( 'Full', 'fv-wordpress-flowplayer' ),
          'fp-fat'     => __( 'Fat', 'fv-wordpress-flowplayer' ),
          'fp-minimal' => __( 'Minimal', 'fv-wordpress-flowplayer' ),
        )
      ),

      array(
        'type'           => 'select',
        'key'            => array('skin-custom', 'design-icons'),
        'first_td_class' => 'second-column',
        'name'           => __( 'Icons', 'fv-wordpress-flowplayer' ),
        'default'        => ' ',
        'options'        => array(
          ' '           => __( 'Default', 'fv-wordpress-flowplayer' ),
          'fp-edgy'     => __( 'Edgy', 'fv-wordpress-flowplayer' ),
          'fp-outlined' => __( 'Outlined', 'fv-wordpress-flowplayer' ),
          'fp-playful'  => __( 'Playful', 'fv-wordpress-flowplayer' )
        )
      ),

    )
  ) );
  ?>
  <div style="clear: both"></div>
<?php
  do_action('fv_player_extensions_admin_load_assets');
}


function fv_flowplayer_admin_skin_playlist() {
	global $fv_fp;
?>
  <div class="flowplayer-wrapper">
    <?php   
    if( isset($fv_fp->admin_preview_player[1]) ) {            
			echo '<div class="fp-playlist-external'.str_replace( 'https://i.vimeocdn.com/video/609485450_1280.jpg', 'https://i.vimeocdn.com/video/608654918_295x166.jpg', $fv_fp->admin_preview_player[1] );
      _e('Hint: you can click the thumbnails to switch videos in the above player. This preview uses the horizontal playlist style.', 'fv-wordpress-flowplayer');
    }
    ?>    
  </div>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
	<?php
	$fv_fp->_get_select(
						__('Playlist Design', 'fv-wordpress-flowplayer'),
						'playlist-design',
						false,
						false,
						array(
							  '2017' => __('2017' , 'fv-wordpress-flowplayer'),
							  '2017 visible-captions' => __('2017 with captions' , 'fv-wordpress-flowplayer'),
							  '2014' => __('2014' , 'fv-wordpress-flowplayer')
							  )
					   ); ?>
    <tr>
      <td><label for="playlistBgColor"><?php _e('Background Color', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="playlistBgColor" name="playlistBgColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistBgColor') ); ?>" 
                 data-fv-preview=".fp-playlist-external > a > span { background-color:#%val%; }"/></td>
    </tr>
    <tr>
      <td><label for="playlistSelectedColor"><?php _e('Active Item', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color" id="playlistSelectedColor" name="playlistSelectedColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistSelectedColor') ); ?>" 
                 data-fv-preview=".fp-playlist-external.fv-playlist-design-2014 a.is-active, .fp-playlist-external.fv-playlist-design-2014 a.is-active h4, .fp-playlist-external.fv-playlist-design-2014 a.is-active h4 span, .fp-playlist-external.fp-playlist-only-captions a.is-active, .fp-playlist-external.fp-playlist-only-captions a.is-active h4 span { color:#%val% !important; }"/></td>
    </tr>
    <tr>              
      <td><label for="playlistFontColor-proxy"><?php _e('Font Color', 'fv-wordpress-flowplayer'); ?></label></td>
        <?php $bShowPlaylistFontColor = ( $fv_fp->_get_option('playlistFontColor') && $fv_fp->_get_option('playlistFontColor') !== '#' ); ?>
      <td>
        <input class="color" id="playlistFontColor-proxy" name="playlistFontColor-proxy" data-previous="" <?php echo $bShowPlaylistFontColor?'':'style="display:none;"'; ?> type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistFontColor') ); ?>" data-fv-preview=".fp-playlist-external a h4 span { color:#%val% !important; }, .fp-playlist-external > a { color:#%val% !important; }, #dashboard-widgets .flowplayer-wrapper .fp-playlist-external h4{color: #%val% !important;}" />
        <input id="playlistFontColor" name="playlistFontColor" type="hidden" value="<?php echo esc_attr( $fv_fp->_get_option('playlistFontColor') ); ?>" />
        <a class="playlistFontColor-show" <?php echo $bShowPlaylistFontColor ? 'style="display:none;"' : ''; ?>><?php _e('Use custom color', 'fv-wordpress-flowplayer'); ?><?php _e('', 'fv-wordpress-flowplayer'); ?></a>
        <a class="playlistFontColor-hide" <?php echo $bShowPlaylistFontColor ? '' : 'style="display:none;"'; ?>><?php _e('Inherit from theme', 'fv-wordpress-flowplayer'); ?><?php _e('', 'fv-wordpress-flowplayer'); ?></a>
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

function fv_flowplayer_admin_custom_css() {
  global $fv_fp;
  $customCSS = $fv_fp->_get_option('customCSS');
?>
<style>
  .CodeMirror {
  border: 1px solid #ddd;
  }
</style>
 <p><?php echo sprintf( __( 'Check our <a href="%s" target="_blank">CSS Tips and Fixes</a> guide for usefull appearance tweaks for FV Player.', 'fv-wordpres-flowplayer'), 'https://foliovision.com/player/advanced/css-tips-and-fixes' ); ?></p>
 <table class="form-table2">
    <tr>
      <td colspan="2">
        <textarea id="customCSS" name="customCSS"><?php echo esc_textarea($customCSS); ?></textarea>
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
  $subtitleBgColor = $fv_fp->_get_option('subtitleBgColor');
  if( $subtitleBgColor[0] == '#' && $opacity = $fv_fp->_get_option('subtitleBgAlpha') ) {
    $rgb = array_map('hexdec', array($subtitleBgColor[1].$subtitleBgColor[2], $subtitleBgColor[3].$subtitleBgColor[4], $subtitleBgColor[5].$subtitleBgColor[6]) );
    $subtitleBgColor = 'rgba('.implode(",",$rgb).','.$opacity.')';
  }
?>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
    <tr>  
      <td><label for="subtitle-font-face"><?php _e('Font Face', 'fv-wordpress-flowplayer'); ?></label></td>
      <td>
        <select id="subtitle-font-face" name="subtitleFontFace" data-fv-preview=".flowplayer .fp-captions { font-family: %val% !important; }">
          <option value="inherit"<?php if( $fv_fp->_get_option('subtitleFontFace') == 'inherit'  ) echo ' selected="selected"'; ?>><?php _e('(inherit from player)', 'fv-wordpress-flowplayer'); ?></option>          
          <option value="&quot;Courier New&quot;, Courier, monospace"<?php if( $fv_fp->_get_option('subtitleFontFace') == "\"Courier New\", Courier, monospace" ) echo ' selected="selected"'; ?>>Courier New</option>
          <option value="Helvetica, sans-serif"<?php if( $fv_fp->_get_option('subtitleFontFace') == "Helvetica, sans-serif" ) echo ' selected="selected"'; ?>>Helvetica</option>				  
          <option value="Tahoma, Geneva, sans-serif"<?php if( $fv_fp->_get_option('subtitleFontFace') == "Tahoma, Geneva, sans-serif" ) echo ' selected="selected"'; ?>>Tahoma, Geneva</option>
        </select>
      </td>   
    </tr>    
    <tr>
      <td><label for="subtitleSize"><?php _e('Font Size', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input id="subtitleSize" name="subtitleSize" title="<?php _e('Enter value in pixels', 'fv-wordpress-flowplayer'); ?>" type="text" value="<?php echo ( $fv_fp->_get_option('subtitleSize') ); ?>"
                 data-fv-preview=".flowplayer .fp-player .fp-captions p { font-size: %val%px !important; }"/></td>
    </tr>
    <tr>
      <td><label for="subtitleBgColor"><?php _e('Background Color', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input class="color-opacity" id="subtitleBgColor" name="subtitleBgColor" type="text" value="<?php echo esc_attr($subtitleBgColor); ?>"
                 data-fv-preview=".flowplayer .fp-player .fp-captions p { background-color: %val% !important; }"/></td>
    </tr>    
    <tr>    		
      <td colspan="2">
        <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
      </td>
    </tr>
  </table>
  <div id="fp-preview-wrapper">
    <div class="flowplayer skin-<?php echo $fv_fp->_get_option('skin'); ?>" id="preview"> 
      <div class="fp-captions fp-shown">
        <p><?php _e('The quick brown fox jumps over the lazy dog.', 'fv-wordpress-flowplayer'); ?></p>
        <p><?php _e('Second line.', 'fv-wordpress-flowplayer'); ?></p>
      </div>
    </div>
  </div>
  <div style="clear: both"></div>
<?php
}
function fv_flowplayer_admin_skin_sticky() {
	global $fv_fp;
?>
  <p><?php _e('This feature lets your viewers continue watching the video as they scroll past it. It applies to desktop computer displays - minimal width of 1020 pixels.', 'fv-wordpres-flowplayer'); ?></p>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
    <?php $fv_fp->_get_checkbox(__('Enable', 'fv-wordpress-flowplayer'), 'sticky_video'); ?>
    <tr>  
      <td><label for="sticky_place"><?php _e('Placement', 'fv-wordpress-flowplayer'); ?></label></td>
      <td>
        <select id="sticky_place" name="sticky_place">
          <option value="right-bottom"<?php if( $fv_fp->_get_option('sticky_place') == "right-bottom" ) echo ' selected="selected"'; ?>>Right, Bottom</option>
          <option value="left-bottom"<?php if( $fv_fp->_get_option('sticky_place') == "left-bottom" ) echo ' selected="selected"'; ?>>Left, Bottom</option>
          <option value="left-top"<?php if( $fv_fp->_get_option('sticky_place') == "left-top" ) echo ' selected="selected"'; ?>>Left, Top</option>
          <option value="right-top"<?php if( $fv_fp->_get_option('sticky_place') == "right-top" ) echo ' selected="selected"'; ?>>Right, Top</option>
        </select>
      </td>
    </tr>
    <tr>
      <td><label for="sticky_width"><?php _e('Player width [px]', 'fv-wordpress-flowplayer'); ?></label></td>
      <td><input id="sticky_width" name="sticky_width" title="<?php _e('Enter value in pixels', 'fv-wordpress-flowplayer'); ?>" type="text" value="<?php echo ( $fv_fp->_get_option('sticky_width') ); ?>"/></td>
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

function fv_flowplayer_admin_usage() {
  ?>
<table class="form-table">
  <tr>
    <td colspan="4">
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/why"><?php _e('Why FV Player?', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/integrations" title="FV Player Integrations">FV Player Integrations</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/player-pro-features" title="FV Player Features - Free vs Pro ">FV Player Features – Free vs Pro</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/comparison-table" title="WordPress Video Plugins Comparison">WordPress Video Plugins Comparison</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/fv-player-presto-comparison" title="FV Player vs. Presto Player – Feature Comparison List">FV Player vs. Presto Player – Feature Comparison List</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/sub-domains-and-multi-domains" title="Using FV Player with Sub-domains and Multi-Domains ">Sub-domains and Multi-Domains</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/why"><?php _e('Getting Started', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item page_item_has_children"><a target="_blank" href="https://foliovision.com/player/getting-started/installation" title="FV Player Installation">Installation</a>
            <ul>
              <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/installation/pro-extension" title="Installation - Pro extension">Installation – Pro extension</a></li>
            </ul>
          </li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/start-up-guide" title="Start up guide for explanation and basic settings">Start-up Guide</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/customizing-fv-player-skin" title="Customizing FV Player skin">Customizing FV Player Skin</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/media-video-library-browser" title="How to use the built-in Library Browser">Built-in Library Browser</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/fv-player-elementor-support" title="Using FV Player With Elementor Page Builder">Using FV Player With Elementor Page Builder</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/lms-player" title="Make Any LMS Work With FV Player">Make Any LMS Work With FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/page-builder-player" title="Make Any Page Builder Work With FV Player">Make Any Page Builder Work With FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/getting-started/how-player-widget" title="How to use FV Player Widget">FV Player Widget</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/playlists"><?php _e('Creating and Managing Playlists', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/playlists/creating-playlists" title="Creating Playlists with FV Player">Creating Playlists</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/faq"><?php _e('FAQ', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/faq/css-tips-and-fixes" title="CSS Tips and Fixes">CSS Tips and Fixes</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/faq/version-1" title="FV WordPress Flowplayer 1.x FAQ">FV WordPress Flowplayer 1.x FAQ</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/settings"><?php _e('Setting Screens', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/settings/post-interface-settings" title="FV Player Post Interface Options">Post Interface Options</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/settings/sitewide-fv-player-defaults" title="Sitewide FV Player Defaults">Sitewide FV Player Defaults</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/settings/integrations-compatibility-options" title="Integrations/Compatibility Options">Integrations/Compatibility Options</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/settings/mobile-settings-behaviors" title="Mobile Settings, Behaviors, and Limitations">Mobile Settings, Behaviors, and Limitations</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/settings/end-of-video-actions" title="End of Video Actions">End of Video Actions</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/settings/video-seo-schema-xml" title="Using Video SEO With FV Player Videos">Using Video SEO</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/settings/optimize-javascript-loading" title="Optimizing FV Player Loading">Optimizing FV Player Loading</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-hosting"><?php _e('Video Hosting', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/youtube-with-fv-player" title="Using YouTube with FV Player">YouTube</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/how-to-use-vimeo" title="How to Use Vimeo with WordPress">Vimeo</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/bunny-stream-player-integration" title="Using Bunny Stream With FV Player">Bunny Stream</a></li>
          <li class="page_item page_item_has_children"><a target="_blank" href="https://foliovision.com/player/video-hosting/amazon-s3-guide" title="Serving Private Videos with Amazon S3 and WordPress">Amazon Web Services S3</a>
            <ul>
              <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/amazon-s3-guide/fix-amazon-mime-type" title="How to set correct MIME type on videos in Amazon S3">Amazon S3 MIME Settings</a></li>
            </ul>
          </li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/how-to-enable-cors-headers" title="How to enable CORS headers for video hosting">How to enable CORS</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/fallback-video-hosting" title="How To Set Up Fallback Video Hosting">Fallback Video Hosting</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/rtmp-streams" title="How to use RTMP streams with Flash - Deprecated">RTMP Streams with Flash - Deprecated</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-hosting/video-hosting-google" title="Video Hosting on Google Drive - Deprecated">Google Drive - Deprecated</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/features"><?php _e('Advanced features', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback" title="Playback Features">Playback Features</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/ab-loop-function" title="AB Loop Function">AB Loop Function</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/autoplay" title="Autoplay">Autoplay</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/using-lightbox" title="Using the Video Lightbox Effect">Video Lightbox Effect</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/speed-buttons" title="How to Use the Speed Buttons">Speed Buttons</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/custom-start-end-time" title="How to Use Custom Start/End Time">Custom Start/End Time</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/quality-switching" title="Setting Up Video Quality Switching">Video Quality Switching</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/are-still-watching-prompt" title="Are You Still Watching Prompt">Are You Still Watching Prompt</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/playback/video-position-saving" title="How To Use Video Position Saving">Video Position Saving</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/features/accessibility"><?php _e('Accessibility Features', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/subtitles" title="How to Use Subtitles">Subtitles</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/interactive-video-transcript" title="Interactive Video Transcript">Interactive Video Transcript</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/timeline-previews" title="Timeline Previews">Timeline Previews</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/vtt-chapters" title="Using VTT Chapters with FV Player">VTT Chapters</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/sticky-video" title="Sticky Video">Sticky Video</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/adding-title-and-splash-text" title="Adding Item Titles and Splash Text">Item Titles and Splash Text</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/alignment-settings" title="Alignment Settings">Alignment Settings</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/accessibility/profile-videos" title="FV Player Profile Videos">FV Player Profile Videos</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/features/sharing"><?php _e('Sharing Options', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/sharing/managing-sharing-buttons" title="Managing Social Sharing Buttons">Social Media Buttons</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/sharing/video-downloading-with-simple-history" title="Video Downloading With Simple History Support">Video Downloading With Simple History Support</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/sharing/creating-video-links" title="Creating Video Links in FV Player">Creating Video Links</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/sharing/email-subscription-form-popups" title="Email Subscription Form Pop-ups">Email Subscription Form Pop-ups</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/sharing/customing-email-sharing" title="Customizing the Email Sharing Text">Email Sharing</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/features/sharing/using-iframe-embedding" title="Using the Iframe Embedding">Iframe Embedding</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-security"><?php _e('Video Security', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods" title="Video Protection Methods">Video Protection Methods</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/protecting-video-from-downloading" title="How to Protect Your Videos from Being Downloaded">Protect Videos From Downloading</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/signed-urls-hls-protection" title="How to Protect your HLS Streams with URL Tokens">Protect HLS Streams with URL Tokens</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/cookie-protection" title="Protecting HLS Videos with Cookies ">Protecting HLS Videos with Cookies</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/secure-videos-security" title="How to secure your videos with Vimeo Security">Vimeo Security Add-on</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-security/cdn"><?php _e('CDN Options', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/cdn/using-bunnycdn-with-fvplayer-pro" title="Using BunnyCDN with FV Player">BunnyCDN</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/cdn/serving-private-cloudfront" title="Serving Private Videos via CloudFront in WordPress">CloudFront</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/cdn/using-keycdn-with-fvplayer" title="Using KeyCDN With FV Player">KeyCDN</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-security/drm-watermarking"><?php _e('DRM Watermarking'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/drm-watermarking/protecting-videos-with-drm-text" title="Protecting Videos With DRM Text">Protecting Videos With DRM Text</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-security/encoding"><?php _e('Secure Video Encoding', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/encoding/encrypted-hls-coconut" title="How to Set up Encrypted HLS with Coconut">Encrypted HLS with Coconut Setup</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/encoding/encrypt-encode-videos-wordpress" title="How to encrypt videos directly in WordPress with Coconut">Encrypted HLS with Coconut End User Guide</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/encoding/mediaconvert-encrypted-hls-guide" title="AWS MediaConvert Encrypted HLS Guide">AWS MediaConvert Encrypted HLS Guide</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/encoding/mediaconvert-end-user-guide" title="AWS MediaConvert End User guide">AWS MediaConvert End User guide</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/encoding/hls-stream" title="How to setup encrypted HLS stream with Amazon Elastic Transcoder - Deprecated">Amazon Elastic Transcoder Encrypted HLS Setup- Deprecated</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/encoding/aws-hls-end-user-guide" title="AWS Elastic Transcoder End User Guide - Deprecated">AWS Elastic Transcoder End User Guide – Deprecated</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning"><?php _e('Video Membership, Pay Per View and eLearning', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv" title="FV Player Pay Per View">FV Player Pay Per View</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv/woocommerce" title="How to use FV Player Pay Per View for WooCommerce">FV Player Pay Per View for WooCommerce</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv/how-to" title="How to use FV Player Pay Per View With Easy Digital Downloads">FV Player Pay Per View for Easy Digital Downloads</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv/sell-video-subscriptions-wordpress" title="How To Sell Video Subscriptions With Restrict Content">Video Subscriptions With Restrict Content</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/membership"><?php _e('Membership Sites', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/membership/rcp-integration" title="How to create membership site with RCP and FV Player">Membership Site with Restrict Content Pro</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/membership/membership-fv-player-compatible" title="Popular Membership Plugins Compatible with FV Player">Popular Membership Plugins Compatible with FV Player</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/elearning"><?php _e('WordPress eLearning', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/elearning/learndash-progression-player" title="Using LearnDash And Video Progression with FV Player">LearnDash And Video Progression</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/elearning/tutor-lms-video-player" title="How To Use FV Player With Tutor LMS">Tutor LMS</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid"><?php _e('FV Player VAST/VPAID', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/how-to-use-vast" title="How to use VAST / VPAID with video player FV Player VAST">How to Use FV Player VAST</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/using-fv-vast-outside-wordpress" title="Using FV Player VAST Outside of WordPress">FV Player VAST Outside of WordPress</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/using-exoclick-ads" title="Using ExoClick Ads With FV Player">ExoClick Ads</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/tracking-vast-with-analytics" title="Tracking VAST and VPAID Ads With Google Analytics">Tracking VAST and VPAID Ads With Google Analytics</a></li>
          <li class="page_item page_item_has_children"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/vast-vpaid-tools" title="VAST/VPAID Tools">VAST/VPAID Tools</a>
            <ul>
              <li class="page_item"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/vast-vpaid-tools/url-tags" title="FV Player VAST - How to use url tags with VAST and VPAID">Using URL Tags</a></li>
              <li class="page_item"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/vast-vpaid-tools/errors-causes-resolutions" title="VAST Errors - Causes &amp; Resolutions">VAST Errors – Causes &amp; Resolutions</a></li>
              <li class="page_item"><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid/vast-vpaid-tools/tester" title="VAST Tester">VAST Tester</a></li>
            </ul>
          </li>
        </ul>
        <div class="clear"></div>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/tools"><?php _e('Tools', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/migration-wizard" title="How to Use FV Player Migration Wizard">FV Player Migration Wizard</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/how-shortcode-conversion-tool" title="How To Use The Shortcode Conversion tool">Shortcode Conversion tool</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/rollback-player-version" title="How To Rollback FV Player Version">Version Rollback For FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/how-to-completely-remove-fv-player" title="How To Completely Remove FV Player">Complete Uninstall</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/live-streaming"><?php _e('Live Streaming', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/live-streaming/live-streaming-youtube" title="Live Streaming With YouTube">Live Streaming With YouTube</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/live-streaming/stream-with-viloud" title="Live Streaming With Viloud">Live Streaming With Viloud</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/live-streaming/live-streaming-vimeo" title="Live Streaming With Vimeo">Live Streaming With Vimeo</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-ads"><?php _e('Video Advertising', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/playback-stats" title="Playback Stats">Playback Stats</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/google-analytics-videos-4" title="Using Google Analytics 4 with FV Player">Google Analytics 4</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/google-analytics-fv-player" title="Using Google Universal Analytics with FV Player">Google Universal Analytics</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/matomo-analytics-fv-player" title="Using Matomo Analytics with FV Player">Matomo Analytics</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/analytics"><?php _e('Analytics', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/advertising-with-fv-flowplayer" title="Advertising Options with FV Player">Advertising Options with FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/built-advertising-tools" title="Built-in Advertising Tools">Built-in Advertising Tools</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/built-advertising-tools/using-preroll-postroll-ads" title="Custom Video Ads in FV Player (pre-roll and post-roll)">Custom Video Ads in FV Player (pre-roll and post-roll)</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/built-advertising-tools/adding-popup-ads" title="Overlay Ads in FV Player">Overlay Ads in FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/built-advertising-tools/setting-up-html-popups" title="Video Actions: HTML Pop-ups">Video Actions: HTML Pop-ups</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/built-advertising-tools/setting-up-html-popup" title="Setting Up the HTML Pop-up Feature">Setting Up the HTML Pop-up Feature</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-ads/google-ads"><?php _e('Google Ads', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/google-ads/google-advertising-options" title="Google Video Advertising Options">Google Video Advertising Options</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/google-ads/incorporating-google-adsense" title="Incorporating Google Ads (AdSense)">Incorporating Google Ads (AdSense)</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/casting"><?php _e('Casting Options', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/casting/chromecast" title="Using FV Player with Chromecast">Chromecast</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/audio"><?php _e('Audio Player', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/audio/audio-tracks-player" title="How to Use Audio Tracks in FV Player">Audio Tracks in FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/audio/multiple-audio-tracks-player" title="How to Use Multiple Audio Tracks with FV Player">Multiple Audio Tracks</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/troubleshooting"><?php _e('Troubleshooting', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/troubleshooting/how-to-use-video-checker" title="How to Use the Built-in Video Checker">How to Use the Built-in Video Checker</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/troubleshooting/switching-fv-player-pro-to-beta" title="Switching FV Player Pro to Beta">Switching FV Player Pro to Beta</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/troubleshooting/compatibility" title="Flowplayer Compatibility - Incompatible Plugins">Incompatible Plugins and Scripts</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/troubleshooting/troubleshooting-javascript-errors" title="Troubleshooting Javascript Errors">Troubleshooting Javascript Errors</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/troubleshooting/encoding" title="Video Encoding for HTML 5">Video Encoding for HTML 5</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/troubleshooting/hls" title="Using HLS With FV Player">Using HLS With FV Player</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/developers"><?php _e('For Developers', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/player-database" title="How to use FV Player Database">FV Player Database</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/shortcode-parameters" title="List of Shortcode Parameters">List of Shortcode Parameters</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/video-custom-fields" title="Setting up Video Custom Fields">Video Custom Fields</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/staging-sites-developers" title="Staging Sites For Developers">Staging Sites For Developers</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/using-fv-player-with-amp" title="Using FV Player With AMP">AMP</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/player-minify-plugins" title="Using FV Player with Minify Plugins">Minify Plugins</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/api-programming" title="Programmer's Guide">Programmer’s Guide</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/video-position-storing" title="Video Position Storing">Video Position Storing</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/developers/changelog"><?php _e('Changelog', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/changelog/fv-player-pro" title="FV Player Pro Changelog">FV Player Pro Changelog</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/changelog/player-vast-changelog" title="FV Player VAST Changelog">FV Player VAST Changelog</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/legal"><?php _e('Legal', 'fv-wordpress-flowplayer'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/legal/commercial-license" title="FV Player Pro Changelog">Commercial License</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/legal/downloading-legal-invoice" title="FV Player VAST Changelog">How to Download Your Legal Invoice</a></li>
        </ul>
      </div>
    </td>
    <td></td>
  </tr>
</table>
  <?php
  }


function fv_flowplayer_admin_database() {
  ?>
    <p>Here's the result of the last database upgrade check:</p>
  <?php
  global $FV_Player_Db;
  if( is_array($FV_Player_Db->getDatabaseUpgradeStatus()) ) {
    foreach( $FV_Player_Db->getDatabaseUpgradeStatus() AS $query ) {
      echo "<p><code>".$query[0]."</code></p>";
    }
  }
}

function fv_flowplayer_admin_embedded_on() {
  global $wpdb;
  $players_with_no_posts = $wpdb->get_var( "SELECT p.id, m.meta_value FROM wp_hp_fv_player_players AS p LEFT JOIN wp_hp_fv_player_playermeta AS m ON p.id = m.id_player AND m.meta_key = 'post_id' OR m.id IS NULL WHERE m.id IS NULL" );
  
  $url = wp_nonce_url(
    add_query_arg(
      array(
        'page'   => 'fvplayer',
        'action' => 'fv-player-embedded-on-fix',
      ),
      admin_url( 'options-general.php' )
    ),
    'fv-player-embedded-on-fix'
  );

  if( $players_with_no_posts > 0 ) :
    ?>
    <p>It appears there are <?php echo $players_with_no_posts; ?> players which do not belong to any post.</p>
    <a href="<?php echo $url ?>" class="button">Fix</a>

  <?php else : ?>
    <p>All of your FV Players seem to have a post associated.</p>
  <?php endif;
}


function fv_flowplayer_admin_rollback() {
  global $fv_wp_flowplayer_ver;
  $base = 'options-general.php?page=fvplayer&action=fv-player-rollback&version=';
  ?>
    <p>Are you having issues with version <?php echo $fv_wp_flowplayer_ver; ?>?</p>
    <p>You can go back to the previous 7.5 version - without changes to Chromecast, WordPress audio/video handling, MPEG-DASH and YouTube:</p>
    </div>
<div class="usage-section">
<h3><a href="<?php echo wp_nonce_url( admin_url($base.'7.5.29.7210'), 'fv-player-rollback' ); ?>" class="button">Reinstall version 7.5.29.7210</a></h3>
  <?php
}

function fv_flowplayer_admin_uninstall() {
  global $fv_fp;

  ?>
    <p><?php _e('Check this box if you would like FV Player to completely remove all of its data when the plugin is deleted. The <code>[fvplayer]</code> shortcodes will stop working.', 'fv-wordpress-flowplayer') ?></p>
    <table class="form-table2">
      <?php   $fv_fp->_get_checkbox(__('Remove all data', 'fv-wordpress-flowplayer'), 'remove_all_data' , __('This action is irreversible, please backup your website if you are not absolutely sure.', 'fv-wordpress-flowplayer')); ?>
      <tr>
        <td colspan="4">
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
        </td>
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
  array('id' => 'fv_flowplayer_settings_tools',     'hash' => 'tab_tools',     	'name' => __('Tools', 'fv-wordpress-flowplayer') ),
  array('id' => 'fv_flowplayer_settings_help',      'hash' => 'tab_help',     	'name' => __('Help', 'fv-wordpress-flowplayer') ),
);



$fv_player_aSettingsTabs = apply_filters('fv_player_admin_settings_tabs',$fv_player_aSettingsTabs);

/* Setup tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description', 'fv_flowplayer_settings', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_interface_options', __('Post Interface Options', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_interface_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_default_options', __('Sitewide FV Player Defaults', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_default_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_integrations', __('Integrations/Compatibility', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_integrations', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_mobile', __('Mobile Settings', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_mobile', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_seo', __('Video SEO', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_seo', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_privacy', __('Privacy Settings', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_privacy', 'fv_flowplayer_settings', 'normal' );

if( !class_exists('FV_Player_Pro') ) {
  add_meta_box( 'fv_player_pro', __('Pro Features', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_pro', 'fv_flowplayer_settings', 'normal', 'low' );
}

/* Skin Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_skin', 'fv_flowplayer_settings_skin', 'normal', 'high' );
add_meta_box( 'flowplayer-wrapper', __('Player Skin', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_skin', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_playlist', __('Playlist', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_skin_playlist', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_custom_css', __('Custom CSS', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_custom_css', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_subtitles', __('Subtitles', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_skin_subtitles', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_sticky', __('Sticky Video', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_skin_sticky', 'fv_flowplayer_settings_skin', 'normal' );

/* Hosting Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_hosting', 'fv_flowplayer_settings_hosting', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_amazon_options', __('Amazon S3 Protected Content', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_amazon_options', 'fv_flowplayer_settings_hosting', 'normal' );

/* Actions Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_actions', 'fv_flowplayer_settings_actions', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_end_of_video', __('End of Video', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_end_of_video' , 'fv_flowplayer_settings_actions', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_popups', __('Custom Popups', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_popups' , 'fv_flowplayer_settings_actions', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_ads', __('Ads', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_ads', 'fv_flowplayer_settings_actions', 'normal' );

/* Video Ads Tab */
if( !class_exists('FV_Player_Pro') ) {
  add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_video_ads', 'fv_flowplayer_settings_video_ads', 'normal', 'high' );
  add_meta_box( 'fv_flowplayer_ads', __('Video Ads', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_video_ads', 'fv_flowplayer_settings_video_ads', 'normal' );
}

/* Tools tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_tools', 'fv_flowplayer_settings_tools', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_conversion', __('Conversion', 'fv-wordpress-flowplayer'),  'fv_flowplayer_settings_box_conversion', 'fv_flowplayer_settings_tools', 'normal' );
add_meta_box( 'fv_flowplayer_database', __('Database', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_database', 'fv_flowplayer_settings_tools', 'normal', 'low' );
add_meta_box( 'fv_flowplayer_embedded_on', __('Embeded Posts Information', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_embedded_on', 'fv_flowplayer_settings_tools', 'normal', 'low' );
add_meta_box( 'fv_flowplayer_rollback', __('Rollback', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_rollback', 'fv_flowplayer_settings_tools', 'normal', 'low' );
add_meta_box( 'fv_flowplayer_uninstall', __('Uninstall', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_uninstall', 'fv_flowplayer_settings_tools', 'normal', 'low' );

/* Help tab */
add_meta_box( 'fv_flowplayer_usage', __('Usage', 'fv-wordpress-flowplayer'), 'fv_flowplayer_admin_usage', 'fv_flowplayer_settings_help', 'normal', 'high' );

?>

<div class="wrap">
	<div style="position: absolute; margin-top: 10px; right: 10px;">
		<a href="https://foliovision.com/player" target="_blank" title="<?php _e('Documentation', 'fv-wordpress-flowplayer'); ?>"><img alt="visit foliovision" src="<?php echo flowplayer::get_plugin_url().'/images/fv-logo.png' ?>" /></a>
	</div>
  <div>
    <div id="icon-options-general" class="icon32"></div>
    <h2>FV Player</h2>
  </div>
  
  <?php
  global $fv_fp;
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
        $fv_player_pro_path = FV_Wordpress_Flowplayer_Plugin_Private::get_plugin_path('fv-player-pro');
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
      <input type="text" name="key" id="key" placeholder="<?php _e('Commercial License Key', 'fv-wordpress-flowplayer'); ?>" value="<?php echo esc_attr( $fv_fp->_get_option('key') ); ?>" /> 
      <?php if( isset($aCheck->expired) && $aCheck->expired ) : ?>
        <a href="#" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_license'); return false"><span class="dashicons dashicons-update-alt"></span><?php _e('Check license', 'fv-wordpress-flowplayer'); ?></a>
      <?php endif; ?>
      <?php if( !$fv_fp->_get_option('key') ) : ?>
        <a title="<?php _e('Click here for license info', 'fv-wordpress-flowplayer'); ?>" target="_blank" href="https://foliovision.com/player/download"><span class="dashicons dashicons-editor-help"></span></a>
      <?php endif; ?>
      <img class="fv_wp_flowplayer_check_license-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /> 
      <img class="fv_wp_flowplayer_check_template-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /> 
      <img class="fv_wp_flowplayer_check_files-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
      <?php do_action('fv_flowplayer_admin_buttons_after'); ?>
    </p>
    <div id="fv_flowplayer_admin_notices">
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
    
    <div id="fv_flowplayer_admin_tabs">
      <h2 class="fv-nav-tab-wrapper nav-tab-wrapper">
        <?php foreach($fv_player_aSettingsTabs as $key => $val):?>
        <a href="#postbox-container-<?php echo $val['hash'];?>" class="nav-tab<?php if( $key == 0 ) : ?> nav-tab-active<?php endif; ?>" style="outline: 0px;"><?php _e($val['name'],'fv-wordpress-flowplayer');?></a>
        <?php endforeach;?>
        <div id="fv_player_js_warning" style=" margin: 8px 40px; display: inline-block; color: darkgrey;" >There Is a Problem with JavaScript.</div>
        <style>
          #fv_player_js_warning {
            animation: cssAnimation 0s 5s forwards;
            visibility: hidden;
          }
          @keyframes cssAnimation {
            to { visibility: visible; }
          }
        </style>
      </h2>
    </div>    
  
    <div id="dashboard-widgets" class="metabox-holder fv-metabox-holder columns-1">
      <?php foreach($fv_player_aSettingsTabs as $key => $val):?>
      <div id='postbox-container-<?php echo $val['hash']; ?>' class='postbox-container'<?php if( $key > 0 ) : ?> style=""<?php endif; ?>>
        <?php do_meta_boxes($val['id'], 'normal', false ); ?>
      </div>
      <?php endforeach;?>
      <div style="clear: both"></div>
    </div>
    <?php
    wp_nonce_field( 'fv_flowplayer_settings_nonce', 'fv_flowplayer_settings_nonce' );
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
    wp_nonce_field( 'meta-box-order-nonce', 'meta-box-order-nonce', false );
    ?>
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

        // Removed the FV Player Pro notice about expired license
        if(type == 'fv_wp_flowplayer_check_license') {
          jQuery('.fv-player-pro-admin_notice_license_error').remove();
        }
      } catch(err) {
        jQuery('#fv_flowplayer_admin_notices').append( jQuery('#wpbody', response ) );
        
      }
      
      jQuery('.'+type+'-spin').hide();
    } );
  }

  var fv_flowplayer_amazon_s3_count = 0;
  jQuery('#amazon-s3-add').on('click', function() {
    var new_inputs = jQuery('tr.amazon-s3-first').clone();
    new_inputs.find("[name='amazon_key[]']").val('');
    new_inputs.find("[name='amazon_secret[]']").val('').show();
    new_inputs.find("[name='_is_secret_amazon_secret[]']").val(0); // set val to 0 - save new
    new_inputs.attr('class', new_inputs.attr('class') + '-' + fv_flowplayer_amazon_s3_count );
    new_inputs.find(':selected').prop('selected',false); // unselect
    new_inputs.find('.secret-preview').remove(); // remove secret preview
    new_inputs.find("[data-setting-change]").remove(); // remove change link
    new_inputs.insertBefore('.amazon-s3-last');
    fv_flowplayer_amazon_s3_count++;
    return false;
  });
  
  function fv_fp_amazon_s3_remove(a) {
    jQuery( '.'+jQuery(a).parents('tr').attr('class') ).remove();
  }
</script>


<script type="text/javascript">
	console.log( 'FV Player Settings screen loading...');
	jQuery(window).one( 'load', function() {
    console.log( 'FV Player Settings screen initializing settings boxes...');

		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

		// postboxes setup
    setTimeout( function() {
      // check if not already initialized
      if( jQuery('.fv-metabox-holder #normal-sortables.ui-sortable').length == 0 ) {
  		  postboxes.add_postbox_toggles('fv_flowplayer_settings');

        // Prevent other plugins from interferring
        postboxes.add_postbox_toggles = function() {
          console.log('FV Player Settings screen prevented duplciate add_postbox_toggles call!');
        }
      }
    }, 100 );

    jQuery('.fv_wp_flowplayer_activate_extension').on('click', function() {  //  todo: block multiple clicks
      var button = jQuery(this);
      button.siblings('img').eq(0).show();

      jQuery.post( ajaxurl, { action: 'fv_wp_flowplayer_activate_extension', nonce: '<?php echo wp_create_nonce( 'fv_wp_flowplayer_activate_extension' ); ?>', plugin: jQuery(this).attr("data-plugin") }, function( response ) {
        button.siblings('img').eq(0).hide();

        var obj;
        try {
          response = response.replace( /[\s\S]*<FVFLOWPLAYER>/, '' );
          response = response.replace( /<\/FVFLOWPLAYER>[\s\S]*/, '' );
          obj = jQuery.parseJSON( response );

          button.removeClass('fv_wp_flowplayer_activate_extension');
          button.attr('value',obj.message);
          
          if( typeof(obj.error) == "undefined" ) {
            //window.location.hash = '#'+jQuery(button).attr("data-plugin");
            //window.location.reload(true);
            window.location.href = window.location.href;
          }
        } catch(e) {  //  todo: what if there is "<p>Plugin install failed.</p>"
          button.after('<p>Error parsing JSON</p>');
          return;
        }
    
      } ).error(function() {
        button.siblings('img').eq(0).hide();
        button.after('<p>Error!</p>');
      });  
    } );
    
    jQuery('.fv-flowplayer-admin-addon-installed').on('click', function() {
      jQuery('html, body').animate({
          scrollTop: jQuery("#"+jQuery(this).attr("data-plugin") ).offset().top
      }, 1000);
    } );
    
    jQuery('.show-more').on('click', function(e) {
      e.preventDefault();
      
      var more = jQuery('.more', jQuery(this).parents('tr') ).length ? jQuery('.more', jQuery(this).parents('tr') ) : jQuery(this).parent().siblings('.more');
      
      more.toggle();
      
    } );
    
    jQuery('.show-info').on('click', function(e) {
      e.preventDefault();
      jQuery('.fv-player-admin-tooltip', jQuery(this).parents('tr') ).toggle();
    } );  
    
    /*
     * Color Picker Default  
     */	
    jQuery('.playlistFontColor-show').on('click', function(e){
      e.preventDefault();
      jQuery(e.target).hide();
      jQuery('.playlistFontColor-hide').show();

      jQuery('#playlistFontColor-proxy').show().val(jQuery('#playlistFontColor-proxy').data('previous')).trigger('change');
      jQuery('#playlistFontColor').val(jQuery('#playlistFontColor-proxy').data('previous'));
    });

    jQuery('.playlistFontColor-hide').on('click', function(e){
      e.preventDefault();
      jQuery(e.target).hide();
      jQuery('.playlistFontColor-show').show();

      jQuery('#playlistFontColor-proxy').data('previous',jQuery('#playlistFontColor-proxy').hide().val()).val('').trigger('change');
      jQuery('#playlistFontColor').val('');
    }); 

    jQuery('#playlistFontColor-proxy').on('change',function(e){
      jQuery('#playlistFontColor').val(jQuery(e.target).val());
    });

    /*
    Ensure only one of "Load FV Flowplayer JS everywhere" and "Optimize FV Flowplayer JS loading" can be enabled
    */
    var cb_js_everywhere = jQuery('#js-everywhere'),
      cb_js_optimize = jQuery('#js-optimize');

    function check_js_everywhere( was_clicked ) {
      if( was_clicked && cb_js_optimize.prop('checked') ) {
        alert('Cannot be used together with "Optimize FV Flowplayer JS loading".');
        return false;
      }

      cb_js_optimize.prop('readonly', cb_js_everywhere.prop('checked') );
    }
    cb_js_everywhere.on( 'click', check_js_everywhere );
    check_js_everywhere();

    function check_js_optimize( was_clicked ) {
      if( was_clicked && cb_js_everywhere.prop('checked') ) {
        alert('Cannot be used together with "Load FV Flowplayer JS everywhere".');
        return false;
      }

      cb_js_everywhere.prop('readonly', cb_js_optimize.prop('checked') );
    }
    cb_js_optimize.on( 'click', check_js_optimize );
    check_js_optimize();

    console.log( 'FV Player Settings screen initializing finished.');
  });
</script>

<script>
/* TABS */
jQuery(window).one( 'load', function() {
  jQuery('#fv_player_js_warning').hide();

  var anchor = window.location.hash.substring(1);
  if( !anchor || !anchor.match(/tab_/) ) {
    anchor = 'postbox-container-tab_basic';
  }

  jQuery('#fv_flowplayer_admin_tabs .nav-tab').removeClass('nav-tab-active');
  jQuery('[href=\\#'+anchor+']').addClass('nav-tab-active');
  jQuery('#dashboard-widgets .postbox-container').hide();
  jQuery('#' + anchor).show();
});

jQuery('#fv_flowplayer_admin_tabs a').on('click',function(e){
  e.preventDefault();
  window.location.hash = e.target.hash;
  var anchor = jQuery(this).attr('href').substring(1);
  jQuery('#fv_flowplayer_admin_tabs .nav-tab').removeClass('nav-tab-active');
  jQuery('[href=\\#'+anchor+']').addClass('nav-tab-active');
  jQuery('#dashboard-widgets .postbox-container').hide();
  jQuery('#' + anchor).show();
});

jQuery('#normal-sortables .button-primary').on('click',function(e){
  if ('fv-wp-flowplayer-submit' == this.name) {
    // store windows scroll position, so we can return to the same spot after reload
    if (localStorage) {
      localStorage["fv_posStorage"] = jQuery(window).scrollTop();
    }
  }

  return true;
});

jQuery(window).on('load', function() {
  setTimeout(function() {
    if (localStorage) {
      var posReader = localStorage["fv_posStorage"];
      if (posReader) {
        jQuery(window).scrollTop(posReader);
        localStorage.removeItem("fv_posStorage");
      }
    }
  }, 100);
});

jQuery('a.fv-settings-anchor').on('click',function(e){
  var id = jQuery(this).attr('href');
  if( id.match(/^#./) ){
    var el = jQuery(id);
    if(el.length){
      var tab = el.parents('.postbox-container').attr('id');
      jQuery('#fv_flowplayer_admin_tabs').find('a[href=\\#'+tab+']').click()
    }
  }
});





</script>
