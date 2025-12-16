<?php
/*  FV Player - HTML5 video player
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

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

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

function fv_flowplayer_admin_overlay() {
	global $fv_fp;
  $lines = substr_count( $fv_fp->_get_option('overlay'), "\n" ) + 2;
?>
					<table class="form-table2">
						<tr>
							<td colspan="2">
								<label for="overlay"><?php esc_html_e( 'Default Overlay Code', 'fv-player' ); ?>:</label><br />
								<textarea id="overlay" name="overlay" class="large-text code" rows="<?php echo intval( $lines ); ?>"><?php echo esc_textarea($fv_fp->_get_option('overlay')); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="2"><label for="overlay_width"><?php esc_html_e( 'Default set size', 'fv-player' );?> [px]:</label>
								<label for="overlay_width">W:</label>&nbsp; <input type="text" name="overlay_width" id="overlay_width" value="<?php echo intval( $fv_fp->_get_option('overlay_width') ); ?>" class="small" />
								<label for="overlay_height">H:</label>&nbsp;<input type="text" name="overlay_height" id="overlay_height" value="<?php echo intval( $fv_fp->_get_option('overlay_height') ); ?>" class="small"  />
								<label for="overlayTextColor"><?php esc_html_e( 'Overlay text', 'fv-player' );?></label> <input class="color small" type="text" name="overlayTextColor" id="overlayTextColor" value="<?php echo esc_attr( $fv_fp->_get_option('overlayTextColor') ); ?>" />
								<label for="overlayLinksColor"><?php esc_html_e( 'Overlay links', 'fv-player' );?></label> <input class="color small" type="text" name="overlayLinksColor" id="overlayLinksColor" value="<?php echo esc_attr( $fv_fp->_get_option('overlayLinksColor') ); ?>" />
							</td>
						</tr>
            <tr>
              <td>
                <label for="overlay_show_after"><?php esc_html_e( 'Show After', 'fv-player' );?> [s]:</label>&nbsp; <input type="text" name="overlay_show_after" id="overlay_show_after" value="<?php echo intval( $fv_fp->_get_option('overlay_show_after') ); ?>" class="small" />
              </td>
            </tr>
						<tr>
							<td colspan="2">
								<label for="overlay_css_select"><?php esc_html_e( 'Overlay CSS', 'fv-player' ); ?>:</label>
								<a href="#" onclick="jQuery('.overlay_css_wrap').show(); jQuery(this).hide(); return false"><?php esc_html_e( 'Show styling options', 'fv-player' ); ?></a>
								<div class="overlay_css_wrap" style="display: none; ">
									<select id="overlay_css_select">
										<option value=""><?php esc_html_e( 'Select your preset', 'fv-player' ); ?></option>
										<option value="<?php echo esc_attr($fv_fp->overlay_css_default); ?>"<?php if( strcmp( preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->overlay_css_default), preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->_get_option('overlay_css') )) == 0 ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Default (white, centered above the control bar)', 'fv-player' ); ?></option>
										<option value="<?php echo esc_attr($fv_fp->overlay_css_bottom); ?>"<?php if( strcmp( preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->overlay_css_bottom), preg_replace('~[^a-z0-9\.{}:;]~','',$fv_fp->_get_option('overlay_css') ))  == 0 ) echo ' selected="selected"'; ?>><?php esc_html_e( 'White, centered at the bottom of the video', 'fv-player' ); ?></option>
									</select>
									<br />
									<textarea rows="5" name="overlay_css" id="overlay_css" class="large-text code"><?php echo esc_textarea($fv_fp->_get_option('overlay_css')); ?></textarea>
									<p class="description"><?php esc_html_e( '(Hint: put .wpfp_custom_ad_content before your own CSS selectors)', 'fv-player' ); ?></p>
									<script type="text/javascript">
									jQuery('#overlay_css_select').on('change', function() {
										if( jQuery('#overlay_css_select option:selected').val().length > 0 && jQuery('#overlay_css_select option:selected').val() != jQuery('#overlay_css').val() && confirm('Are you sure you want to apply the preset?') ) {
											jQuery('#overlay_css').val( jQuery('#overlay_css_select option:selected').val() );
										}
									} );
									</script>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="4">
								<a class="fv-wordpress-flowplayer-save button button-primary" href="#" data-reload="true"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
							<p><?php esc_html_e( 'Secured Amazon S3 URLs are recommended for member-only sections of the site. We check the video length and make sure the link expiration time is big enough for the video to buffer properly.', 'fv-player' ); ?></p>
              <p><?php esc_html_e( 'If you use a cache plugin (such as Hyper Cache, WP Super Cache or W3 Total Cache), we recommend that you set the "Default Expiration Time" to twice as much as your cache timeout and check "Force the default expiration time". That way the video length won\'t be accounted and the video source URLs in your cached pages won\'t expire. Read more in the', 'fv-player' ); ?> <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/secure-amazon-s3-guide#wp-cache" target="_blank"><?php esc_html_e( 'Using Amazon S3 secure content in FV Player guide', 'fv-player' ); ?></a>.</p>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="amazon_expire"><?php esc_html_e( 'Default Expiration Time [minutes]', 'fv-player' ); ?> (<abbr title="<?php esc_html_e( 'Each video duration is stored on post save and then used as the expire time. If the duration is not available, this value is used.', 'fv-player' ); ?>">?</abbr>):</label></td>
						<td>
              <input type="text" size="40" name="amazon_expire" id="amazon_expire" value="<?php echo intval( $fv_fp->_get_option('amazon_expire') ); ?>" />
            </td>
					</tr>

          <?php $fv_fp->_get_checkbox(__( 'Force the default expiration time', 'fv-player' ), 'amazon_expire_force'); ?>
          <?php
          $can_use_aws_sdk = version_compare(phpversion(),'7.4') != -1;

          $fv_fp->_get_checkbox( array(
            'name' => __( 'Amazon S3 Browser', 'fv-player' ).' (beta)',
            'key' => 's3_browser',
            'help' =>  !$can_use_aws_sdk ?
              __( 'This function requires PHP >= 7.4, please contact your web host support.' , 'fv-player' )
              : __( 'Show Amazon S3 Browser in the "Add Video" dialog.' , 'fv-player' ),
            'disabled' => !$can_use_aws_sdk
          ) ); ?>

          <?php do_action('fv_player_admin_amazon_options'); ?>
<?php
			$count = 0;
			foreach( $fv_fp->_get_option('amazon_bucket') AS $key => $item ) :
				$count++;
            $sRegion = $fv_fp->_get_option( array( 'amazon_region', $key ) );
?>
        <tr class="amazon-s3-<?php echo intval( $count ); ?>">
            <td><label for="amazon_bucket[]"><?php esc_html_e( 'Amazon Bucket', 'fv-player' ); ?> (<abbr title="<?php esc_html_e( 'We recommend that you simply put all of your protected video into a single bucket and enter its name here. All matching videos will use the protected URLs.', 'fv-player' ); ?>">?</abbr>):</label></td>
            <td><input id="amazon_bucket[]" name="amazon_bucket[]" type="text" value="<?php echo esc_attr($item); ?>" /></td>
        </tr>
        <tr class="amazon-s3-<?php echo intval( $count ); ?>">
            <td><label for="amazon_region[]"><?php esc_html_e( 'Region', 'fv-player' ); ?></td>
            <td>
              <select id="amazon_region[]" name="amazon_region[]">
                <option value=""><?php esc_html_e( 'Select the region', 'fv-player' ); ?></option><?php

                foreach (fv_player_get_aws_regions() as $aws_region_id => $aws_region_name) {
                  ?>
                  <option value="<?php echo esc_attr( $aws_region_id ); ?>"<?php if( $sRegion == $aws_region_id ) echo " selected"; ?>><?php echo esc_html( $aws_region_name .' ('. $aws_region_id.')' ); ?></option>
                  <?php
                }

                ?>
              </select>
            </td>
        </tr>
        <tr class="amazon-s3-<?php echo intval( $count ); ?>">
            <td><label for="amazon_key[]"><?php esc_html_e( 'Access Key ID', 'fv-player' ); ?>:</label></td>
            <td><input id="amazon_key[]" name="amazon_key[]" type="text" value="<?php echo esc_attr( $fv_fp->_get_option( array( 'amazon_key', $key ) ) ); ?>" /></td>
        </tr>
        <tr class="amazon-s3-<?php echo intval( $count ); ?>">
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
            <td><label for="amazon_secret[]"><?php esc_html_e( 'Secret Access Key', 'fv-player' ); ?>:</label></td>
            <td><input  <?php if($secret && !empty($censored_val)) echo 'style="display: none;"' ?> id="amazon_secret[]" name="amazon_secret[]" type="text" value="<?php echo esc_attr( $val ); ?>" />
          <?php if( $secret ): ?>
            <input name="<?php echo esc_attr($secret_key); ?>" value="<?php if(empty($censored_val)) {echo '0';} else {echo '1';} ?>" type="hidden" />
            <?php if(!empty($censored_val)): ?>
              <code class="secret-preview"><?php echo esc_attr( $censored_val ); ?></code>
              <a href="#" data-is-empty="0" data-setting-change="<?php echo esc_attr($secret_key.'-index-'.$key); ?>" >Change</a>
            <?php endif; ?>
          <?php endif; ?>
          </td>
        </tr>
        <tr class="amazon-s3-<?php echo intval( $count ); ?>">
            <td colspan="2">
                <div class="alignright fv_fp_amazon_remove">
                    <a href="#" onclick="fv_fp_amazon_s3_remove(this); return false"><?php esc_html_e( 'remove', 'fv-player' ); ?></a>
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
              <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
              <input type="button" id="amazon-s3-add" class="button" value="<?php esc_attr_e( 'Add more Amazon S3 secure buckets', 'fv-player' ); ?>" />
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
              <td><label for="width"><?php esc_html_e( 'Default Video Size', 'fv-player' ); ?>:</label></td>
              <td>
                <p class="description">
                  <label for="width"><?php esc_html_e( 'Width', 'fv-player' ); ?>:</label>&nbsp;<input type="text" class="small" name="width" id="width" value="<?php echo esc_attr( $fv_fp->_get_option('width') ); ?>" />
                  <label for="height"><?php esc_html_e( 'Height', 'fv-player' ); ?>:</label>&nbsp;<input type="text" class="small" name="height" id="height" value="<?php echo esc_attr( $fv_fp->_get_option('height') ); ?>" />
                  <?php esc_html_e( 'Enter values in pixels or 100%.', 'fv-player' ); ?>
                </p>
              </td>
            </tr>
            <tr>
              <td><label for="volume"><?php esc_html_e( 'Default Volume', 'fv-player' ); ?>:</label></td>
              <td>
                <p class="description">
                  <input id="volume" name="volume" type="range" min="0" max="1" step="0.1" value="<?php echo esc_attr( $fv_fp->_get_option('volume') ); ?>" class="medium" />
                </p>
              </td>
            </tr>

            <?php $fv_fp->_get_checkbox(__( 'Disable Admin Video Checker', 'fv-player' ), 'disable_videochecker', __( 'Checks your video encoding when you open a post with video as admin. Notifies you about possible playback issues.', 'fv-player' ) ); ?>
            <?php $fv_fp->_get_checkbox(__( 'Disable Playlist Autoadvance', 'fv-player' ), 'playlist_advance', __( 'Playlist won\'t play the next video automatically.', 'fv-player' ) ); ?>


            <?php if( $fv_fp->_get_option('rtmp') ) : ?>
              <tr>
                <td><label for="rtmp"><?php esc_html_e( 'Flash Streaming Server (deprecated)', 'fv-player' ); ?>:</label></td>
                <td>
                  <p class="description">
                    <input type="text" name="rtmp" id="rtmp" value="<?php echo esc_attr( $fv_fp->_get_option('rtmp') ); ?>" placeholder="<?php esc_attr_e( 'Enter your default RTMP streaming server (Amazon CloudFront domain).', 'fv-player' ); ?>" />
                  </p>
                </td>
              </tr>
            <?php endif; ?>

            <?php $fv_fp->_get_checkbox(__( 'Force HD Streaming', 'fv-player' ), 'hd_streaming', __( 'Use HD quality for HLS/MPEG-DASH even on slow connections.', 'fv-player' ), __(  'User can still switch to lower quality by hand. Doesn\'t work on iPhones.', 'fv-player' ) ); ?>

            <tr>
              <td><label for="googleanalytics"><?php esc_html_e( 'Google Analytics ID', 'fv-player' ); ?>:</label></td>
              <td>
                <p class="description">
                  <input type="text" name="googleanalytics" id="googleanalytics" value="<?php echo esc_attr( $fv_fp->_get_option('googleanalytics') ); ?>" placeholder="<?php esc_attr_e( 'Will be automatically loaded when playing a video.', 'fv-player' ); ?>" />
                </p>
              </td>
            </tr>

            <tr>
              <td><label for="matomo_domain"><?php esc_html_e( 'Matomo/Piwik Tracking', 'fv-player' ); ?>:</label></td>
              <td>
                <p class="description">
                  <input type="text" name="matomo_domain" id="matomo_domain" value="<?php echo esc_attr( $fv_fp->_get_option('matomo_domain') ); ?>" placeholder="<?php esc_attr_e( 'matomo.your-domain.com', 'fv-player' ); ?>" class="large" />
                  <input type="text" name="matomo_site_id" id="matomo_site_id" value="<?php echo esc_attr( $fv_fp->_get_option('matomo_site_id') ); ?>" placeholder="<?php esc_attr_e( 'Site ID', 'fv-player' ); ?>" class="small" />
                </p>
              </td>
            </tr>

            <?php $fv_fp->_get_checkbox(__( 'Multiple video playback', 'fv-player' ), 'multiple_playback', __( 'Allows multiple players to play at once. Only one player remains audible.', 'fv-player' ) ); ?>

            <tr>
							<td><label for="liststyle"><?php esc_html_e( 'Playlist style', 'fv-player' ); ?>:</label></td>
							<td colspan="3">
                <p class="description">
                  <?php
                  $value = $fv_fp->_get_option('liststyle');
                  ?>
                  <select id="liststyle" name="liststyle">
                    <?php
                    foreach(
                      array(
                        'horizontal'  => __(  'Horizontal', 'fv-player' ),
                        'tabs'        => __(  'Tabland', 'fv-player' ),
                        'prevnext'    => __(  'Big arrows (deprecated)', 'fv-player' ),
                        'vertical'    => __(  'Vertical', 'fv-player' ),
                        'slider'      => __(  'Scrollslider', 'fv-player' ),
                        'season'      => __(  'Episodes', 'fv-player' ),
                        'polaroid'    => __(  'Polaroid', 'fv-player' ),
                        'text'        => __(  'Text', 'fv-player' ),
                        'version-one' => __(  'Sliderland', 'fv-player' ),
                        'version-two' => __(  'Sliderbar', 'fv-player' ),
                      ) as $style => $name
                    ) {

                      // Do not offer "Big arrows" if it's not already saved.
                      if ( 'prevnext' === $style && strcmp( $value, $style ) !== 0 ) {
                        continue;
                      }
                      ?>
                      <option value="<?php echo esc_attr( $style ); ?>"<?php if( $value === $style ) echo ' selected="selected"'; ?>><?php echo esc_html( $name ); ?></option>
                      <?php
                    }
                    ?>
                  </select>
                  <?php esc_html_e( 'Enter your default playlist style here', 'fv-player' ); ?>
                </p>
              </td>
            </tr>

            <?php //$fv_fp->_get_checkbox(__( 'Popup Box', 'fv-player' ), 'popupbox', __( 'Shows a generic "Would you like to replay the video?" message at the end of each video.', 'fv-player' ) ); ?>

            <tr>
              <td><label for="sharing_text"><?php esc_html_e( 'Sharing Text', 'fv-player' ); ?>:</label></td>
              <td>
                <p class="description">
                  <input type="text" name="sharing_email_text" id="sharing_email_text" value="<?php echo esc_attr( $fv_fp->_get_option('sharing_email_text') ); ?>" placeholder="<?php esc_attr_e( 'Check out the amazing video here', 'fv-player' ); ?>" />
                </p>
              </td>
            </tr>
            <tr>
              <td><label for="splash"><?php esc_html_e( 'Splash Image', 'fv-player' ); ?>:</label></td>
              <td>
                <input type="text" name="splash" id="splash" value="<?php echo esc_attr( $fv_fp->_get_option('splash') ); ?>" class="large" placeholder="<?php esc_attr_e( 'Default which will be used for any player without its own splash image.', 'fv-player' ); ?>" />
                <input id="upload_image_button" class="upload_image_button button no-margin small" type="button" value="<?php esc_attr_e( 'Upload Image', 'fv-player' ); ?>" alt="Select default Splash Screen" /></td>
            </tr>

            <?php $fv_fp->_get_checkbox(__( 'Subtitles On By Default', 'fv-player' ), 'subtitleOn', __( 'Normally you have to hit a button in controlbar to turn on subtitles.', 'fv-player' ) ); ?>

            <?php do_action('fv_flowplayer_admin_default_options_after'); ?>
          </table>
          <table class="form-table2">
            <tr>
              <td colspan="4">
                <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
                <a class="button fv-help-link" href="https://foliovision.com/player/settings/sitewide-fv-player-defaults" target="_blank">Help</a>
                <a href="#skin-tab-controls" class="settings-section-link">Looking for controls settings?</a>
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

          $('.fv_flowplayer_target').val( attachment.url ).trigger('change');
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

function fv_flowplayer_admin_autoplay_and_preloading() {
  global $fv_fp;
  $value = $fv_fp->_get_option('autoplay_preload');
?>
  <style>
  #fv_flowplayer_autoplay_and_preloading .descriptions {
    float: right;
    position: relative;
    width: 50%;
  }
  #fv_flowplayer_autoplay_and_preloading [data-describe] {
    display: none;
    position: absolute;
    top: 0;
  }
  </style>
  <table class="form-table2">
  <tr>
    <td class="first"></td>
      <td>
        <?php
        $radio_butons = array();
        $radio_butons_descriptions = array();

        foreach( array(
          'false' => array(
            'label' => __( 'Off', 'fv-player' )
          ),
          'preload' => array(
            'label' => __( 'Video Preload', 'fv-player' ),
            'description' => __( 'First 3 videos on page will preload (or 1 if it\'s HLS). Then it will play instantly when clicked.', 'fv-player' )
          ),
          'viewport' => array(
            'label' => __( 'Autoplay Video in Viewport', 'fv-player' ),
            'description' => __( 'Video will autoplay when the player is visible on page load or when user scrolls down to it. It will pause when no longer in browser viewport. The next video will start preloading in the background.', 'fv-player' )
          ),
          'sticky' => array(
            'label' => __( 'Sticky Autoplay', 'fv-player' ),
            'description' => __( 'The video will autoplay and become sticky - following user\'s scroll position.', 'fv-player' )
          )
        ) AS $key => $field ) {
          $id = 'autoplay_preload_'.esc_attr($key);

          $radio_button = '<input id="'.$id.'" type="radio" name="autoplay_preload" value="'.esc_attr($key).'"';
          if( $value === $key || wp_json_encode($value) == $key ) { // use wp_json_encode as value can be boolean
            $radio_button .= ' checked="checked"';
          }
          $radio_button .= ' />';
          $radio_button .= '<label for="'.$id.'">'.$field['label'].'</label><br />';

          $radio_butons[] = $radio_button;

          if( !empty($field['description']) ) {
            $radio_butons_descriptions[$key] = $field['description'];
          }
        }

        echo '<div class="descriptions">';
        foreach( $radio_butons_descriptions AS $key => $description ) {
          echo '<p class="description" data-describe="' . esc_attr( $key ) . '">' . esc_html( $description ) . '</p>';
        }
        echo '</div>';

        echo implode( $radio_butons );
        ?>
      </td>
    </td>
  </tr>
  <?php do_action('fv_flowplayer_autoplay_and_preloading_inputs_after'); ?>
  <tr>
    <td colspan="4">
      <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
    </td>
  </tr>
  </table>
  <div class="clear"></div>

  <script>
  jQuery( function($) {
    show_description_autoplay();

    $('[name=autoplay_preload]' ).on( 'change', show_description_autoplay );

    function show_description_autoplay() {
      $( '#fv_flowplayer_autoplay_and_preloading [data-describe]' ).hide();
      $( '#fv_flowplayer_autoplay_and_preloading [data-describe='+$('[name=autoplay_preload]:checked').val()+']' ).show();
    }
  } );
  </script>
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
          <?php esc_html_e( 'FV Player is a free, easy-to-use, and complete solution for embedding', 'fv-player' ); ?>
          <strong>MP4</strong>, <strong>WEBM</strong>, <strong>OGV</strong>, <strong>MOV</strong>
          <?php esc_html_e( 'and', 'fv-player' ); ?>
          <strong>FLV</strong>
          <?php esc_html_e( 'videos into your posts or pages. With MP4 videos, FV Player offers 98&#37; coverage even on mobile devices.', 'fv-player' ); ?>
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
            <?php esc_html_e( 'You can customize the colors of the player to match your website.', 'fv-player' ); ?>
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
          <?php esc_html_e( 'Here you can enable and configure advanced hosting options.', 'fv-player' ); ?>
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
          <?php esc_html_e( 'Here you can configure ads and banners that will be shown in the video.', 'fv-player' ); ?>
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
          <?php esc_html_e( 'Maintenance tools and debug info.', 'fv-player' ); ?>
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
          <?php echo wp_kses( __( 'Purchase <a href="https://foliovision.com/player/download" target="_blank"><b>FV Player Licence</b></a>, and you will be able to configure multiple, clickable Video Ads, that can be played before or after Your videos.', 'fv-player' ), array( 'a' => array( 'href' => array() ) ) ); ?>
        </p>
        <p>
          <?php esc_html_e( 'You can configure video ads globally, or on a per video basis.', 'fv-player' ); ?>
        </p>
        <p>
        <?php echo wp_kses( __( 'If you are interested in VAST or VPAID ads, then check out <a href="https://foliovision.com/player/vast" target="_blank"><b>FV Player VAST</b></a>.', 'fv-player' ), array( 'a' => array( 'href' => array() ) ) ); ?>
        </p>
      </td>
    </tr>
  </table>
<?php
}

function fv_flowplayer_admin_integrations() {
	global $fv_fp;
?>
        <p><?php esc_html_e( 'Following options are suitable for web developers and programmers.', 'fv-player' ); ?></p>
        <table class="form-table2">
          <?php $fv_fp->_get_checkbox(__( 'Debug', 'fv-player' ), 'debug_log', __( 'Print debug messages to browser console.', 'fv-player' ) ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Disable database conversion', 'fv-player' ), 'disable_convert_db_save', __( 'Stop converting [fvplayer src="..."] shortcodes, [video] shortcodes, Vimeo and YouTube links to database-driven FV Player when post is saved.', 'fv-player' ) ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Disable saving skin CSS to a static file', 'fv-player' ), 'css_disable', __( 'Normally the player CSS configuration is stored in wp-content/fv-flowplayer-custom/style-{blog_id}.css.', 'fv-player' ), __('We do this to avoid a big style tag in your site &lt;head&gt;. Don\'t edit this file though, as it will be overwritten by plugin update or saving its options!', 'fv-player' )); ?>

          <tr>
            <td><label for="css_disable"><?php esc_html_e( 'Enable profile videos', 'fv-player' ).' (beta)'; ?>:</label></td>
            <td>
              <div class="description">
                <input type="hidden" name="profile_videos_enable_bio" value="false" />
                <input type="checkbox" name="profile_videos_enable_bio" id="profile_videos_enable_bio" value="true" <?php if( $fv_fp->_get_option('profile_videos_enable_bio') ) echo 'checked="checked"'; ?> />
                <?php esc_html_e( 'Check your site carefully after enabling. Videos attached to the user profile will be showing as a part of the user bio.', 'fv-player' ); ?> <a href="#" class="show-more">(&hellip;)</a>
                <div class="more">
                  <p><?php esc_html_e('This feature is designed for YouTube and Vimeo videos and works best for our licensed users who get these videos playing without YouTube or Vimeo branding.', 'fv-player'); ?></p>
                  <p><?php echo wp_kses( __('Some themes show author bio on the author post archive automatically (Genesis framework and others). Or you can also just put this code into your theme archive.php template, right before <code>while ( have_posts() )</code> is called:', 'fv-player'), array( 'code' => array() ) ); ?></p>
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
                  <p><?php esc_html_e('We will be adding integration for it for popular user profile plugins.', 'fv-player'); ?></p>

                </div>
              </div>
            </td>
          </tr>

          <?php $fv_fp->_get_checkbox(__( 'Handle WordPress audio/video', 'fv-player' ), array( 'integrations', 'wp_core_video' ), 'Make sure shortcodes <code><small>[video]</small></code>, <code><small>[audio]</small></code> and <code><small>[playlist]</small></code>, the Gutenberg video block and the YouTube links use FV Player.', '' ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Load JavaScript everywhere', 'fv-player' ), 'js-everywhere', __( 'If you use some special JavaScript integration you might prefer this option.', 'fv-player' ), __( 'Otherwise our JavaScript only loads if the shortcode is found in any of the posts being currently displayed. Required if you load content using Ajax, like in various LMS systems.', 'fv-player' ) ); ?>
          <?php $fv_fp->_get_checkbox(
            array(
              'name'     => __( 'Optimize JavaScript loading', 'fv-player' ),
              'key'      => 'js-optimize',
              'help'     =>
                flowplayer::is_wp_rocket_setting( 'delay_js' ) ?
                  sprintf( __( 'WP Rocket setting to <a href="%s" target="_blank">Delay JavaScript execution</a> is enabled, cannot use this setting.', 'fv-player' ), admin_url( 'options-general.php?page=wprocket#file_optimization' ) ) :
                  __( 'Helps with Google PageSpeed scores.', 'fv-player' ),
              'more'     => __( 'FV Player JavaScript will be only loaded once the user user start to use the page or on video tap.', 'fv-player' ),
              'disabled' => flowplayer::is_wp_rocket_setting( 'delay_js' ),
            )
          ); ?>
					<?php if( $fv_fp->_get_option('parse_commas') ) $fv_fp->_get_checkbox(__( 'Parse old shortcodes with commas', 'fv-player' ), 'parse_commas', __( 'Older versions of this plugin used commas to sepparate shortcode parameters.', 'fv-player' ), __( 'This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.', 'fv-player' ) ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Parse Vimeo and YouTube links', 'fv-player' ), 'parse_comments', __( 'Affects comments, bbPress and BuddyPress. These links will be displayed as videos.', 'fv-player' ), __( 'This option makes most sense together with FV Player Pro as it embeds these videos using FV Player. Enables use of shortcodes in comments and bbPress.', 'fv-player' ) ); ?>
          <?php if( $fv_fp->_get_option('postthumbnail') ) $fv_fp->_get_checkbox(__( 'Post Thumbnail', 'fv-player' ), 'postthumbnail', __( 'Setting a video splash screen from the media library will automatically make it the splash image if there is none.', 'fv-player' ) ); ?>
					<?php if( $fv_fp->_get_option('engine') ) $fv_fp->_get_checkbox(__( 'Prefer Flash player by default', 'fv-player' ), 'engine', __( 'Provides greater compatibility.', 'fv-player' ), __( 'We use Flash for MP4 files in IE9-10 and M4V files in Firefox regardless of this setting.', 'fv-player' ) ); ?>
          <?php if( $fv_fp->_get_option('rtmp-live-buffer') ) $fv_fp->_get_checkbox(__( 'RTMP bufferTime tweak (deprecated)', 'fv-player' ), 'rtmp-live-buffer', __( 'Use if your live streams are not smooth.', 'fv-player' ), __( 'Adobe <a href="http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/net/NetStream.html#bufferTime">recommends</a> to set bufferTime to 0 for live streams, but if your stream is not smooth, you can use this setting.', 'fv-player' ) ); ?>

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
              <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
          <?php $fv_fp->_get_checkbox(__( 'Use native fullscreen on mobile', 'fv-player' ), 'mobile_native_fullscreen', __( 'Stops popups, ads or subtitles from working, but provides faster interface. We set this for Android < 4.4 and iOS < 7 automatically.', 'fv-player' ) ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Force fullscreen on mobile', 'fv-player' ), 'mobile_force_fullscreen', __( 'Video playback will start in fullscreen. iPhone with iOS < 10 always forces fullscreen for video playback.', 'fv-player' )  ); ?>
          <?php
          $fv_fp->_get_checkbox(__( 'Alternative iPhone fullscreen mode', 'fv-player' ), 'mobile_alternative_fullscreen', __( "Use if you see site elements such as floating header bar ovelaying the player when in fullscreen.", 'fv-player' )  );
          ?>
          <tr>
            <td colspan="4">
              <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
          <?php $fv_fp->_get_checkbox(__( 'Disable local storage', 'fv-player' ), 'disable_localstorage', __( 'Remember video position will not work for non logged users. Video volume, mute status and subtitles selection will also not be stored.', 'fv-player' ) ); ?>
          <tr>
            <td colspan="4">
              <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
          <?php $fv_fp->_get_checkbox(__( 'Use Schema.org markup', 'fv-player' ), array( 'integrations', 'schema_org' ), __( ' Adds the video meta data information for search engines.', 'fv-player' ) ); ?>
          <?php do_action( 'fv_flowplayer_admin_seo_after'); ?>
          <tr>
            <td colspan="4">
              <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
              <a class="button fv-help-link" href="https://foliovision.com/player/settings/video-seo-schema-xml" target="_blank">Help</a>
              </td>
          </tr>
        </table>
<?php
}


function fv_flowplayer_admin_select_popups($aArgs){

  $aPopupData = apply_filters( 'fv_player_admin_popups_defaults', get_option( 'fv_player_popups', array() ) );


  $sId = (isset($aArgs['id'])?$aArgs['id']:'popups_default');
  $aArgs = wp_parse_args( $aArgs, array( 'id'=>$sId, 'cva_id'=>'', 'show_default' => false ) );
  ?>
  <select id="<?php echo esc_attr( $aArgs['id'] ); ?>" name="<?php echo esc_attr( $aArgs['id'] ); ?>">
    <?php if( $aArgs['show_default'] ) : ?>
      <option>Use site default</option>
    <?php endif; ?>
    <option <?php if( $aArgs['item_id'] == 'no' ) echo 'selected '; ?>value="no"><?php esc_html_e( 'None', 'fv-player' ); ?></option>
    <option <?php if( $aArgs['item_id'] == 'random' ) echo 'selected '; ?>value="random"><?php esc_html_e( 'Random', 'fv-player' ); ?></option>
    <?php
    if( isset($aPopupData) && is_array($aPopupData) && count($aPopupData) > 0 ) {
      foreach( $aPopupData AS $key => $aPopupAd ) {
        ?><option <?php if( $aArgs['item_id'] == $key ) echo 'selected'; ?> value="<?php echo esc_attr( $key ); ?>"><?php
        echo esc_html( $key );
        if( !empty($aPopupAd['title']) ) echo ' - ' . esc_html( $aPopupAd['title'] );
        if( !empty($aPopupAd['name']) ) echo ' - ' . esc_html( $aPopupAd['name'] );
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
        <td style="width:150px;vertical-align:top;line-height:2.4em;"><label for="popups_default"><?php esc_html_e( 'Default Popup', 'fv-player' ); ?>:</label></td>
        <td>
          <?php $cva_id = $fv_fp->_get_option('popups_default'); ?>
          <p class="description"><?php fv_flowplayer_admin_select_popups( array('item_id'=>$cva_id,'id'=>'popups_default') ); ?> <?php esc_html_e( 'You can set a default popup here and then skip it for individual videos.', 'fv-player' ); ?></p>
        </td>
      </tr>
      <tr>
        <td colspan="4">
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
        </td>
      </tr>
    </table>
    <?php
}


function fv_flowplayer_admin_popups(){
  global $fv_fp;
    ?>
    <p><?php esc_html_e( 'Add any popups here which you would like to use with multiple videos.', 'fv-player' ); ?></p>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td>
          <table id="fv-player-popups-settings">
            <thead>
            	<tr>
            		<td>ID</td>
            		<td></td>
          			<td><?php esc_html_e( 'Status', 'fv-player' ); ?></td>
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
              $value = ! empty( $aPopup['html'] ) ? $aPopup['html'] : '';
              $lines = ! empty( $aPopup['html'] ) ? substr_count( $aPopup['html'], "\n" ) + 2 : 2;
              ?>
              <tr class='data' id="fv-player-popup-item-<?php echo esc_html( $key ); ?>"<?php echo $key === '#fv_popup_dummy_key#' ? 'style="display:none"' : ''; ?>>
                <td class='id'><?php echo esc_html( $key ); ?></td>
                    <td>
                      <table class='fv-player-popup-formats'>
                        <tr>
                        	<td><label><?php esc_html_e( 'Name', 'fv-player' ); ?>:</label></td>
                        	<td><input type='text' maxlength="40" name='popups[<?php echo esc_attr( $key ); ?>][name]' value='<?php echo ( !empty($aPopup['name']) ? esc_attr($aPopup['name']) : '' ); ?>' placeholder='' /></td>
                      	</tr>
                        <tr>
                        	<td><label>HTML:</label></td>
                        	<td><textarea class="large-text code" type='text' name='popups[<?php echo esc_attr( $key ); ?>][html]' placeholder='' rows='<?php echo intval( $lines ); ?>'><?php echo esc_textarea( $value ); ?></textarea></td>
                      	</tr>
                        <tr>
                        	<td><label><?php echo wp_kses( __( 'Custom<br />CSS', 'fv-player' ), array( 'br' => array() ) ); ?>:</label></td>
                        	<td><textarea class="large-text code" type='text' name='popups[<?php echo esc_attr( $key ); ?>][css]'><?php echo ( !empty($aPopup['css']) ? esc_textarea($aPopup['css']) : '.wpfp_custom_popup .fv_player_popup-' . $key . ' { }' ); ?></textarea></td>
                      	</tr>
                      </table>
                    </td>
                    <td>
                      <input type='hidden' name='popups[<?php echo esc_attr( $key ); ?>][disabled]' value='0' />
                      <input id='PopupAdPause-<?php echo esc_html( $key ); ?>' type='checkbox' name='popups[<?php echo esc_attr( $key ); ?>][pause]' value='1' <?php echo (isset($aPopup['pause']) && $aPopup['pause'] ? 'checked="checked"' : ''); ?> />
                      <label for='PopupAdPause-<?php echo esc_html( $key ); ?>'><?php esc_html_e( 'Show on pause', 'fv-player' ); ?></label><br />
                      <input id='PopupAdDisabled-<?php echo esc_html( $key ); ?>' type='checkbox' name='popups[<?php echo esc_attr( $key ); ?>][disabled]' value='1' <?php echo (isset($aPopup['disabled']) && $aPopup['disabled'] ? 'checked="checked"' : ''); ?> />
                      <label for='PopupAdDisabled-<?php echo esc_html( $key ); ?>'><?php esc_html_e( 'Disable', 'fv-player' ); ?></label><br />
                      <a class='fv-player-popup-remove' href=''><?php esc_html_e( 'Remove', 'fv-player' ); ?></a></td>
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
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#" data-reload="true"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
          <input type="button" value="<?php esc_attr_e( 'Add more Popups', 'fv-player' ); ?>" class="button" id="fv-player-popups-add" />
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
        <p><?php esc_html_e( 'Which features should be available in shortcode editor?', 'fv-player' ); ?></p>
        <table class="form-table2">
          <?php $fv_fp->_get_checkbox(__( 'Autoplay', 'fv-player' ), array('interface', 'autoplay') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Controlbar', 'fv-player' ), array('interface', 'controlbar') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Embed', 'fv-player' ), array('interface', 'embed') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'LMS | Teaching', 'fv-player' ), array('interface', 'lms_teaching') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Mobile Video', 'fv-player' ), array('interface', 'mobile') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Playlist Auto Advance', 'fv-player' ), array('interface', 'playlist_advance') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Playlist Style', 'fv-player' ), array('interface', 'playlist') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Playlist Item Titles', 'fv-player' ), array('interface', 'playlist_titles') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Sharing Buttons', 'fv-player' ), array('interface', 'share') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Speed Buttons', 'fv-player' ), array('interface', 'speed') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Splash Text', 'fv-player' ), array('interface', 'splash_text') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Sticky', 'fv-player' ), array('interface', 'sticky') ); ?>
          <?php $fv_fp->_get_checkbox(__( 'Synopsis', 'fv-player' ), array('interface', 'synopsis') ); ?>

          <?php do_action('fv_flowplayer_admin_interface_options_after'); ?>

          <tr>
            <td colspan="4">
              <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
              <a class="button fv-help-link" href="https://foliovision.com/player/settings/post-interface-settings" target="_blank">Help</a>
            </td>
          </tr>
        </table>
<?php
}


function fv_flowplayer_admin_pro() {
  global $fv_fp;

  if( flowplayer::is_licensed() ) {
    $aCheck = get_transient( 'fv-player-pro_license' );
  }

  if( isset($aCheck->valid) && $aCheck->valid ) : ?>
    <p><?php esc_html_e( 'Valid license found, click the button at the top of the screen to install FV Player Pro!', 'fv-player' ); ?></p>
  <?php else : ?>
    <p><a href="https://foliovision.com/player/download"><?php esc_html_e( 'Purchase FV Player license', 'fv-player' ); ?></a> <?php esc_html_e( 'to enable Pro features!', 'fv-player' ); ?></p>
  <?php endif; ?>
  <table class="form-table2">
    <tr>
      <td class="first"><label><?php esc_html_e( 'Advanced Vimeo embeding', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php esc_html_e( 'Use Vimeo as your video host and use all of FV Player features.', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php esc_html_e( 'Advanced YouTube embeding', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php esc_html_e( 'Use YouTube as your video host and use all of FV Player features.', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php esc_html_e( 'Enable user defined AB loop', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php esc_html_e( 'Let your users repeat the parts of the video which they like!', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php esc_html_e( 'Enable video lightbox', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php esc_html_e( 'Enables Lightbox video gallery to show videos in a lightbox popup!', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php esc_html_e( 'Enable quality switching', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php esc_html_e( 'Upload your videos in multiple quality for best user experience with YouTube-like quality switching!', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php esc_html_e( 'Amazon CloudFront protected content', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php esc_html_e( 'Protect your Amazon CDN hosted videos', 'fv-player' ); ?>.
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php esc_html_e( 'Autoplay just once', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" disabled="true" />
          <?php esc_html_e( 'Makes sure each video autoplays only once for each visitor.', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
    <tr>
      <td><label><?php esc_html_e( 'Enable video ads', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" disabled="true" />
          <?php esc_html_e( 'Define your own videos ads to play in together with your videos - postroll or prerool', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
  </table>
  <p><strong><?php esc_html_e( 'Upcoming pro features', 'fv-player' ); ?></strong>:</p>
  <table class="form-table2">
    <tr>
      <td class="first"><label><?php esc_html_e( 'Enable PayWall', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="checkbox" checked="checked" disabled="true" />
          <?php esc_html_e( 'Monetize the video content on your membership site.', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
  </table>
  <?php
}

function fv_flowplayer_settings_box_conversion() {
  ?>
    <p><?php esc_html_e( 'This section allows you to convert videos posted using other plugins to FV Player shortcodes.', 'fv-player' ); ?></p>
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
          <td style="width:180px"><label for="pro[video_ads_default]"><?php esc_html_e( 'Default pre-roll ad:', 'fv-player' ); ?></label></td>
          <td>
            <p class="description">
              <select disabled="true" id="pro[video_ads_default]" >
                <option selected="" value="no">No ad</option>
                <option value="random">Random</option>
                <option value="1">1</option>
              </select>
              <?php esc_html_e( 'Set which ad should be played before videos.', 'fv-player' ); ?>
            </p>
          </td>
        </tr>
        <tr>
          <td style="width:180px"><label for="pro[video_ads_postroll_default]"><?php esc_html_e( 'Default post-roll ad:', 'fv-player' ); ?></label></td>
          <td>
            <p class="description">
              <select disabled="true" id="pro[video_ads_postroll_default]" >
                <option selected="" value="no">No ad</option>
                <option value="random">Random</option>
                <option value="1">1</option>
              </select>
              <?php esc_html_e( 'Set which ad should be played after videos.', 'fv-player' ); ?>
            </p>
          </td>
        </tr>
        <tr>
          <td style="width:180px"><label for="pro[video_ads_skip]"><?php esc_html_e( 'Default ad skip time', 'fv-player' ); ?>:</label></td>
          <td>
            <p class="description">
              <input disabled="true" class="small" id="pro[video_ads_skip]"  title="<?php esc_attr_e( 'Enter value in seconds', 'fv-player' ); ?>" type="text" value="5">
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
                      <tbody><tr><td><label><?php esc_html_e( 'Name', 'fv-player' ); ?>:</label></td><td colspan="2"><input disabled="true" type="text"  value="" placeholder="<?php esc_attr_e( 'Ad name', 'fv-player' ); ?>"></td></tr>
                        <tr><td><label><?php esc_html_e( 'Click URL', 'fv-player' ); ?>:</label></td><td colspan="2"><input disabled="true" type="text"  value="" placeholder="<?php esc_attr_e( 'Clicking the video ad will open the URL in new window', 'fv-player' ); ?>"></td></tr>
                        <tr><td><label><?php esc_html_e( 'Video', 'fv-player' ); ?>:</label></td><td colspan="2"><input disabled="true" type="text"  value="" placeholder="<?php esc_attr_e( 'Enter the video URL here', 'fv-player' ); ?>"></td></tr>
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
            <input disabled="true" type="button" value="<?php esc_attr_e( 'Add more video ads', 'fv-player' ); ?>" class="button" id="fv-player-pro_video-ads-add">
          </td>
        </tr>
      </tbody></table>


  <?php
}



function fv_flowplayer_admin_skin_get_table($options) {
    global $fv_fp;

    $selected_skin = $fv_fp->_get_option( 'skin' );
?>
    <table class="form-table2 flowplayer-settings fv-player-interface-form-group" id="skin-<?php echo esc_html( $options['skin_name'] ); ?>-settings"<?php if (($selected_skin && $selected_skin != $options['skin_radio_button_value']) || (!$selected_skin && $options['default'] !== true)) { echo ' style="display: none"'; } ?>>
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
          <td></td>
          <td>
            <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
    global $fv_fp_admin_preview_player;
    $fv_fp_admin_preview_player = flowplayer_content_handle( array(
      'src'       => 'https://player.vimeo.com/external/196881410.hd.mp4?s=24645ecff21ff60079fc5b7715a97c00f90c6a18&profile_id=174&oauth2_token_id=3501005',
      'splash'    => 'https://i.vimeocdn.com/video/609485450-6fc3febe7ce2c2fda919a99c27a9cb904c645dcb944bc53ac7f3a228685305d8-d?mw=1280&mh=720',
      'autoplay'  => 'false',
      'preroll'   => 'no',
      'postroll'  => 'no',
      'subtitles' => plugins_url('images/test-subtitles.vtt',dirname(__FILE__)),
      'caption'   => "Foliovision Video;Lapinthrope Extras - Roy Thompson Hall Dance;Romeo and Juliet Ballet Schloss Kittsee",
      'playlist'  => 'https://player.vimeo.com/external/224781088.sd.mp4?s=face4dbb990b462826c8e1e43a9c66c6a9bb5585&profile_id=165&oauth2_token_id=3501005,https://i.vimeocdn.com/video/643908843-984e68e66846a7a4b42bf5e854b65937217ed1b71759afa16afd4f81963900a6-d?mw=230&mh=130;https://player.vimeo.com/external/45864857.hd.mp4?s=94fddee594da3258c9e10355f5bad8173c4aee7b&profile_id=113&oauth2_token_id=3501005,https://i.vimeocdn.com/video/319116053-4745c7d678ba90ebabeadf58a65439b780c2ef26176176acc03eabbe87c8afda-d?mw=230&mh=130',
			'liststyle' => 'horizontal',
      'vast'      => 'skip',
      'checker'   => 'no'
      ) );
    $fv_fp_admin_preview_player = explode( '<div class="fp-playlist-external', $fv_fp_admin_preview_player );

    // Video checker uses <noscript> and style="display: none" so we need to keep that
    add_filter( 'wp_kses_allowed_html', 'fv_flowplayer_admin_skin_safe_tags', 10, 2 );
    add_filter( 'safe_style_css', 'fv_flowplayer_admin_skin_safe_styles' );
    echo wp_kses_post( $fv_fp_admin_preview_player[0] );
    remove_filter( 'wp_kses_allowed_html', 'fv_flowplayer_admin_skin_safe_tags', 10, 2 );
    remove_filter( 'safe_style_css', 'fv_flowplayer_admin_skin_safe_styles' );
    ?>
    <?php esc_html_e( 'Hint: play the video to see live preview of the Skin, Logo and Controls settings', 'fv-player' ) ?>
  </div>

  <div id="fv_flowplayer_admin_skin_tabs">
    <h2 class="fv-nav-tab-wrapper nav-tab-wrapper">
      <a href="#skin-tab-skin" class="nav-tab nav-tab-active" style="outline: 0px;">Skin</a>
      <a href="#skin-tab-logo" class="nav-tab" style="outline: 0px;">Logo</a>
      <a href="#skin-tab-controls" class="nav-tab" style="outline: 0px;">Controls</a>
    </h2>
  </div>

  <div id="skin-tab-skin" class="skin-tab-content">
    <table class="form-table2 flowplayer-settings fv-player-interface-form-group" id="skin-Skin-settings">
      <?php
          // skin change radios
          $fv_fp->_get_radio(array(
            'key' => 'skin',
            'name' => __( 'Skin', 'fv-player' ),
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
      'borderColor' => '.flowplayer{border-color:#%val% !important;}',
      'backgroundColor' => '.flowplayer .fv-ab-loop .noUi-handle  { color:#%val% !important; }
                  .fv_player_popup {  background: #%val% !important;}
                  .fvfp_admin_error_content {  background: #%val% !important; }
                  .flowplayer .fp-controls, .flowplayer .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { background-color: #%val% !important; }',
      'font-face' => '#content .flowplayer, .flowplayer { font-family: %val%; }',
      'progressColor' => '.flowplayer .fp-volumelevel { background-color: #%val% !important; }
            .flowplayer .fp-progress, .flowplayer .fv-ab-loop .noUi-connect, .fv-player-buttons a.current { background-color: #%val% !important; }
            .flowplayer .fp-menu a.fp-selected { background-color: #%val% !important }
            .flowplayer .fp-color { background-color: #%val% !important }',
      'durationColor' => '.flowplayer .fp-controls, .flowplayer .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { color:#%val% !important; }
                    .flowplayer .fp-controls > .fv-fp-prevbtn:before, .flowplayer .fp-controls > .fv-fp-nextbtn:before { border-color:#%val% !important; }
                    .flowplayer svg.fvp-icon { fill: #%val% !important; }
                    .flowplayer .fp-elapsed, .flowplayer .fp-duration { color: #%val% !important; }
                    .freedomplayer .fp-controls svg { fill: #%val% !important; stroke: #%val% !important }
                    .fv-player-video-checker { color: #%val% !important; }',
      'design-timeline' => '',
      'design-icons' => '',
    );

    // slim skin settings
    $aSettings = array(
        array(
          'type'    => 'input_text',
          'key'     => array('skin-slim', 'progressColor'),
          'name'    => __(  'Color', 'fv-player' ),
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
          'name'    => __(  'Color', 'fv-player' ),
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
          'name' => __(  'Border', 'fv-player' ),
          'data'    => array( 'fv-preview' => $aPreview['hasBorder'] )
        ),

        array(
          'type'    => 'input_text',
          'key'     => array('skin-custom', 'borderColor'),
          'name'    => __(  'Border color', 'fv-player' ),
          'class'   => 'color',
          'default' => '666666',
          'data'    => array( 'fv-preview' => $aPreview['borderColor'] )
        ),

        array(
          'type'    => 'input_text',
          'key'     => array('skin-custom', 'backgroundColor'),
          'name'    => __(  'Controlbar', 'fv-player' ),
          'class'   => 'color-opacity',
          'default' => '333333',
          'data'    => array( 'fv-preview' => $aPreview['backgroundColor'] )
        ),

        array(
          'type'    => 'select',
          'key'     => array('skin-custom', 'font-face'),
          'name'    => __(  'Font Face', 'fv-player' ),
          'options' => array(
            'inherit'                                     => __(  '(inherit from template)', 'fv-player' ),
            '&quot;Courier New&quot;, Courier, monospace' => 'Courier New',
            'Helvetica, sans-serif'                       => 'Helvetica',
            'Tahoma, Geneva, sans-serif'                  => 'Tahoma, Geneva'
          ),
          'default' => 'Tahoma, Geneva, sans-serif',
          'data'    => array( 'fv-preview' => $aPreview['font-face'] )
        ),

        array(
          'type'    => 'input_text',
          'key'     => array('skin-custom', 'progressColor'),
          'name'    => __(  'Progress', 'fv-player' ),
          'class'   => 'color',
          'default' => 'BB0000',
          'data'    => array( 'fv-preview' => $aPreview['progressColor'] )
        ),

        array(
          'type'    => 'input_text',
          'key'     => array('skin-custom', 'durationColor'),
          'name'    => __(  'Buttons', 'fv-player' ),
          'class'   => 'color',
          'default' => 'EEEEEE',
          'data'    => array( 'fv-preview' => $aPreview['durationColor'] )
        ),

        array(
          'type'           => 'select',
          'key'            => array('skin-custom', 'design-timeline'),
          'first_td_class' => 'second-column',
          'name'           => __(  'Timeline', 'fv-player' ),
          'default'        => ' ',
          'options'        => array(
            ' '          => __(  'Default', 'fv-player' ),
            'fp-slim'    => __(  'Slim', 'fv-player' ),
            'fp-full'    => __(  'Full', 'fv-player' ),
            'fp-fat'     => __(  'Fat', 'fv-player' ),
            'fp-minimal' => __(  'Minimal', 'fv-player' ),
          )
        ),

        array(
          'type'           => 'select',
          'key'            => array('skin-custom', 'design-icons'),
          'first_td_class' => 'second-column',
          'name'           => __(  'Icons', 'fv-player' ),
          'default'        => ' ',
          'options'        => array(
            ' '           => __(  'Default', 'fv-player' ),
            'fp-edgy'     => __(  'Edgy', 'fv-player' ),
            'fp-outlined' => __(  'Outlined', 'fv-player' ),
            'fp-playful'  => __(  'Playful', 'fv-player' )
          )
        ),

      )
    ) );
    ?>
  </div>

  <div id="skin-tab-logo" class="skin-tab-content">
    <table class="form-table2">
      <tr>
        <td class="aligntop-input"><label for="logo">Logo:</label></td>
        <td>

          <input type="text" name="logo" id="logo" value="<?php echo esc_attr( $fv_fp->_get_option('logo') ); ?>" class="large" placeholder="<?php esc_attr_e( 'Paste link or upload an image.', 'fv-player' ); ?>" data-fv-preview />
          <input id="upload_image_button" class="upload_image_button button no-margin small" type="button" value="<?php esc_attr_e( 'Upload Image', 'fv-player' ); ?>" alt="Select Logo" />
        </td>
      </tr>
      <tr>
        <td><label for="logoPosition">Position:</label></td>
        <td>
          <?php
          $value = $fv_fp->_get_option('logoPosition');
          ?>
          <select name="logoPosition" class="small" style="width: 9em !important" data-fv-preview>
            <option <?php if( $value == 'bottom-left' ) echo "selected"; ?> value="bottom-left"><?php esc_html_e( 'Bottom-left', 'fv-player' ); ?></option>
            <option <?php if( $value == 'bottom-right' ) echo "selected"; ?> value="bottom-right"><?php esc_html_e( 'Bottom-right', 'fv-player' ); ?></option>
            <option <?php if( $value == 'top-left' ) echo "selected"; ?> value="top-left"><?php esc_html_e( 'Top-left', 'fv-player' ); ?></option>
            <option <?php if( $value == 'top-right' ) echo "selected"; ?> value="top-right"><?php esc_html_e( 'Top-right', 'fv-player' ); ?></option>
          </select>
        </td>
      </tr>
      <?php $fv_fp->_get_checkbox(
        array(
          'name' => __( 'Align to video', 'fv-player' ),
          'key'  => 'logo_over_video',
          'help' => __( 'Logo stays on top of video if aspect ratio does not match.', 'fv-player' ),
          'first_td_class' => 'aligntop',
          'data' => array(
            'fv-preview' => ''
          )
        )
      ); ?>
      <tr>
        <td></td>
        <td>
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
        </td>
      </tr>
    </table>
  </div>

  <div id="skin-tab-controls" class="skin-tab-content">
    <table class="form-table2">
      <?php $fv_fp->_get_checkbox(__( 'Always Visible', 'fv-player' ), 'show_controlbar', __( 'Control bar will show below player and not just on hover.') ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Airplay', 'fv-player' ), 'ui_airplay', __( 'Adds support for Airplay.', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Embed', 'fv-player' ), 'ui_embed', __( 'Embed link in top bar (no preview).', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Chromecast', 'fv-player' ), 'chromecast', __( 'Adds support for Google Chromecast.', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Fullscreen', 'fv-player' ), 'allowfullscreen', __( 'Adds a fullscreen button.', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'No Picture', 'fv-player' ), 'ui_no_picture_button', __( 'Adds a button to turn the video picture on and off.', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Repeat', 'fv-player' ), 'ui_repeat_button', __( 'Adds a button to set playlist/track repeat and shuffle.', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Rewind/Forward', 'fv-player' ), 'ui_rewind_button', __( 'Adds a button to go 10 seconds back/forth.', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Sharing', 'fv-player' ), 'ui_sharing', __( 'Sharing buttons in top bar (no preview).', 'fv-player' ) ); ?>
      <?php $fv_fp->_get_checkbox(__( 'Speed', 'fv-player' ), 'ui_speed', __( 'Speed buttons control playback speed.', 'fv-player' ) ); ?>
      <tr>
        <td><label for="ui_speed_increment"><?php esc_html_e( 'Speed Step', 'fv-player' ); ?>:</label></td>
        <td colspan="3">
          <p class="description">
            <?php
            $value = $fv_fp->_get_option('ui_speed_increment');
            ?>
            <select id="ui_speed_increment" name="ui_speed_increment" style="width: 5em">
              <option value="0.1"   <?php if( $value == 0.1 ) echo ' selected="selected"'; ?> >0.1</option>
              <option value="0.25"  <?php if( $value == 0.25 ) echo ' selected="selected"'; ?> >0.25</option>
              <option value="0.5"   <?php if ( $value == 0.5 )  echo ' selected="selected"'; ?> >0.5</option>
            </select>
            <?php esc_html_e( 'Accuracy of the Speed button.', 'fv-player' ); ?>
          </p>
        </td>
      </tr>
      <?php $fv_fp->_get_checkbox(__( 'Video Links', 'fv-player' ), 'ui_video_links', __( '"Link" item in top bar (no preview).', 'fv-player' ), __( "Clicking the video Link gives your visitors a link to the exact place in the video they are watching. If the post access is restricted, it won't make the video open to public.", 'fv-player' ) ); ?>
      <tr>
        <td></td>
        <td>
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
        </td>
      </tr>
    </table>
  </div>

  <div style="clear: both"></div>
<?php
  do_action('fv_player_extensions_admin_load_assets');
}

function fv_flowplayer_admin_skin_safe_styles( $styles ) {
  $styles[] = 'display';
  return $styles;
}

function fv_flowplayer_admin_skin_safe_tags( $tags, $context ) {
  if ( 'post' === $context ) {
      $tags['noscript'] = array();
  }
  return $tags;
}

function fv_flowplayer_admin_skin_playlist() {
	global $fv_fp;
?>
  <div class="flowplayer-wrapper">
    <?php
    global $fv_fp_admin_preview_player;
    if ( isset( $fv_fp_admin_preview_player[1] ) ) {
			echo '<div class="fp-playlist-external'.str_replace( 'https://i.vimeocdn.com/video/609485450_1280.jpg', 'https://i.vimeocdn.com/video/608654918_295x166.jpg', $fv_fp_admin_preview_player[1] );
      esc_html_e( 'Hint: you can click the thumbnails to switch videos in the above player. This preview uses the horizontal playlist style.', 'fv-player' );
    }
    ?>
  </div>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
	<?php
	$fv_fp->_get_select(
						__( 'Playlist Design', 'fv-player' ),
						'playlist-design',
						false,
						false,
						array(
							  '2017' => __( '2017' , 'fv-player' ),
							  '2017 visible-captions' => __( '2017 with captions' , 'fv-player' ),
							  '2014' => __( '2014' , 'fv-player' )
							  )
					   ); ?>
    <tr>
      <td><label for="playlistBgColor"><?php esc_html_e( 'Background Color', 'fv-player' ); ?></label></td>
      <td><input class="color" id="playlistBgColor" name="playlistBgColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistBgColor') ); ?>"
                 data-fv-preview=".fp-playlist-external > a > span { background-color:#%val%; }"/></td>
    </tr>
    <tr>
      <td><label for="playlistSelectedColor"><?php esc_html_e( 'Active Item', 'fv-player' ); ?></label></td>
      <td><input class="color" id="playlistSelectedColor" name="playlistSelectedColor" type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistSelectedColor') ); ?>"
                 data-fv-preview=".fp-playlist-external.fv-playlist-design-2014 a.is-active, .fp-playlist-external.fv-playlist-design-2014 a.is-active h4, .fp-playlist-external.fv-playlist-design-2014 a.is-active h4 span, .fp-playlist-external.fp-playlist-only-captions a.is-active, .fp-playlist-external.fp-playlist-only-captions a.is-active h4 span { color:#%val% !important; }"/></td>
    </tr>
    <tr>
      <td><label for="playlistFontColor-proxy"><?php esc_html_e( 'Font Color', 'fv-player' ); ?></label></td>
        <?php $bShowPlaylistFontColor = ( $fv_fp->_get_option('playlistFontColor') && $fv_fp->_get_option('playlistFontColor') !== '#' ); ?>
      <td>
        <input class="color" id="playlistFontColor-proxy" name="playlistFontColor-proxy" data-previous="" <?php echo $bShowPlaylistFontColor ? '' : 'style="display:none;"'; ?> type="text" value="<?php echo esc_attr( $fv_fp->_get_option('playlistFontColor') ); ?>" data-fv-preview=".fp-playlist-external a h4 span { color:#%val% !important; }, .fp-playlist-external > a { color:#%val% !important; }, #dashboard-widgets .flowplayer-wrapper .fp-playlist-external h4{color: #%val% !important;}" />
        <input id="playlistFontColor" name="playlistFontColor" type="hidden" value="<?php echo esc_attr( $fv_fp->_get_option('playlistFontColor') ); ?>" />
        <a class="playlistFontColor-show" <?php echo $bShowPlaylistFontColor ? 'style="display:none;"' : ''; ?>><?php esc_html_e( 'Use custom color', 'fv-player' ); ?><?php esc_html_e( '', 'fv-player' ); ?></a>
        <a class="playlistFontColor-hide" <?php echo $bShowPlaylistFontColor ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Inherit from theme', 'fv-player' ); ?><?php esc_html_e( '', 'fv-player' ); ?></a>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
 <p><?php echo wp_kses(
  sprintf( __( 'Check our <a href="%s" target="_blank">CSS Tips and Fixes</a> guide for usefull appearance tweaks for FV Player.', 'fv-wordpres-flowplayer'), 'https://foliovision.com/player/advanced/css-tips-and-fixes' ),
  array( 'a' => array( 'href' => array(), 'target' => array() ) )
 ); ?></p>
 <table class="form-table2">
    <tr>
      <td colspan="2">
        <textarea id="customCSS" name="customCSS"><?php echo esc_textarea($customCSS); ?></textarea>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
  <div id="fp-preview-wrapper">
    <div class="flowplayer skin-<?php echo esc_html( $fv_fp->_get_option('skin') ); ?>" id="preview">
      <div class="fp-captions fp-shown">
        <p><?php esc_html_e( 'The quick brown fox jumps over the lazy dog.', 'fv-player' ); ?></p>
        <p><?php esc_html_e( 'Second line.', 'fv-player' ); ?></p>
      </div>
    </div>
  </div>
  <table class="form-table2 flowplayer-settings fv-player-interface-form-group">
    <tr>
      <td><label for="subtitle-font-face"><?php esc_html_e( 'Font Face', 'fv-player' ); ?></label></td>
      <td>
        <select id="subtitle-font-face" name="subtitleFontFace" data-fv-preview=".flowplayer .fp-captions { font-family: %val% !important; }">
          <option value="inherit"<?php if( $fv_fp->_get_option('subtitleFontFace') == 'inherit'  ) echo ' selected="selected"'; ?>><?php esc_html_e( '(inherit from player)', 'fv-player' ); ?></option>
          <option value="&quot;Courier New&quot;, Courier, monospace"<?php if( $fv_fp->_get_option('subtitleFontFace') == "\"Courier New\", Courier, monospace" ) echo ' selected="selected"'; ?>>Courier New</option>
          <option value="Helvetica, sans-serif"<?php if( $fv_fp->_get_option('subtitleFontFace') == "Helvetica, sans-serif" ) echo ' selected="selected"'; ?>>Helvetica</option>
          <option value="Tahoma, Geneva, sans-serif"<?php if( $fv_fp->_get_option('subtitleFontFace') == "Tahoma, Geneva, sans-serif" ) echo ' selected="selected"'; ?>>Tahoma, Geneva</option>
        </select>
      </td>
    </tr>
    <tr>
      <td><label for="subtitleSize"><?php esc_html_e( 'Font Size', 'fv-player' ); ?></label></td>
      <td><input id="subtitleSize" name="subtitleSize" title="<?php esc_attr_e( 'Enter value in pixels', 'fv-player' ); ?>" type="text" value="<?php echo ( $fv_fp->_get_option('subtitleSize') ); ?>"
                 data-fv-preview=".flowplayer .fp-player .fp-captions p { font-size: %val%px !important; }"/></td>
    </tr>
    <tr>
      <td><label for="subtitleBgColor"><?php esc_html_e( 'Background Color', 'fv-player' ); ?></label></td>
      <td><input class="color-opacity" id="subtitleBgColor" name="subtitleBgColor" type="text" value="<?php echo esc_attr($subtitleBgColor); ?>"
                 data-fv-preview=".flowplayer .fp-player .fp-captions p { background-color: %val% !important; }"/></td>
    </tr>
    <tr>
      <td></td>
      <td>
        <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
      </td>
    </tr>
  </table>
  <div style="clear: both"></div>
<?php
}
function fv_flowplayer_admin_skin_sticky() {
	global $fv_fp;
?>
  <p><?php esc_html_e('This feature lets your viewers continue watching the video as they scroll past it. For desktop computers we consider a display with minimal width of 1020 pixels.', 'fv-wordpres-flowplayer'); ?></p>
  <table class="thirds">
    <tr>
      <?php
      $fv_fp->_get_radio( array(
        'key' => 'sticky_video',
        'name' => __( '', 'fv-player' ),
        'style' => 'columns',
        'values' => array(
          'off'     => 'Off',
          'desktop' => 'Desktop',
          'all'     => 'Desktop and Mobile'
        ),
      ) );
      ?>
    </tr>
  </table>
  <table class="form-table2">
    <tr>
      <td class="first"><label for="sticky_place"><?php esc_html_e( 'Placement', 'fv-player' ); ?></label></td>
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
      <td><label for="sticky_width"><?php esc_html_e( 'Desktop Player Width [px]', 'fv-player' ); ?></label></td>
      <td>
        <input id="sticky_width" name="sticky_width" title="<?php esc_attr_e( 'Enter value in pixels', 'fv-player' ); ?>" type="text" value="<?php echo ( $fv_fp->_get_option('sticky_width') ); ?>"/>
        <?php esc_html_e(  'Used on desktop and (if enabled) also on mobile in landscape orientation and tablets.', 'fv-player' ); ?>
      </td>
    </tr>
    <?php
	  $fv_fp->_get_select(
			__(  'Mobile Player Width', 'fv-player' ),
			'sticky_width_mobile',
			__(  'Used on mobile (device width lower than 480 pixels).', 'fv-player' ),
			false,
			array(
				'100' => '100%',
				'75'  => '75%',
				'50'  => '50%'
			)
		); ?>
    <tr>
      <td></td>
      <td>
        <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
        <h3><a target="_blank" href="https://foliovision.com/player/why"><?php esc_html_e( 'Why FV Player?', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/integrations" title="FV Player Integrations">FV Player Integrations</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/player-pro-features" title="FV Player Features - Free vs Pro ">FV Player Features – Free vs Pro</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/comparison-table" title="WordPress Video Plugins Comparison">WordPress Video Plugins Comparison</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/fv-player-presto-comparison" title="FV Player vs. Presto Player – Feature Comparison List">FV Player vs. Presto Player – Feature Comparison List</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/why/sub-domains-and-multi-domains" title="Using FV Player with Sub-domains and Multi-Domains ">Sub-domains and Multi-Domains</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/why"><?php esc_html_e( 'Getting Started', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/playlists"><?php esc_html_e( 'Creating and Managing Playlists', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/playlists/creating-playlists" title="Creating Playlists with FV Player">Creating Playlists</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/faq"><?php esc_html_e( 'FAQ', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/faq/css-tips-and-fixes" title="CSS Tips and Fixes">CSS Tips and Fixes</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/faq/version-1" title="FV WordPress Flowplayer 1.x FAQ">FV WordPress Flowplayer 1.x FAQ</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/settings"><?php esc_html_e( 'Setting Screens', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/video-hosting"><?php esc_html_e( 'Video Hosting', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/features"><?php esc_html_e( 'Advanced features', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/features/accessibility"><?php esc_html_e( 'Accessibility Features', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/features/sharing"><?php esc_html_e( 'Sharing Options', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/video-security"><?php esc_html_e( 'Video Security', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods" title="Video Protection Methods">Video Protection Methods</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/protecting-video-from-downloading" title="How to Protect Your Videos from Being Downloaded">Protect Videos From Downloading</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/signed-urls-hls-protection" title="How to Protect your HLS Streams with URL Tokens">Protect HLS Streams with URL Tokens</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/cookie-protection" title="Protecting HLS Videos with Cookies ">Protecting HLS Videos with Cookies</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/video-protection-methods/secure-videos-security" title="How to secure your videos with Vimeo Security">Vimeo Security Add-on</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-security/cdn"><?php esc_html_e( 'CDN Options', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/cdn/using-bunnycdn-with-fvplayer-pro" title="Using BunnyCDN with FV Player">BunnyCDN</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/cdn/serving-private-cloudfront" title="Serving Private Videos via CloudFront in WordPress">CloudFront</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/cdn/using-keycdn-with-fvplayer" title="Using KeyCDN With FV Player">KeyCDN</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-security/drm-watermarking"><?php esc_html_e('DRM Watermarking'); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-security/drm-watermarking/protecting-videos-with-drm-text" title="Protecting Videos With DRM Text">Protecting Videos With DRM Text</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-security/encoding"><?php esc_html_e( 'Secure Video Encoding', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning"><?php esc_html_e( 'Video Membership, Pay Per View and eLearning', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv" title="FV Player Pay Per View">FV Player Pay Per View</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv/woocommerce" title="How to use FV Player Pay Per View for WooCommerce">FV Player Pay Per View for WooCommerce</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv/how-to" title="How to use FV Player Pay Per View With Easy Digital Downloads">FV Player Pay Per View for Easy Digital Downloads</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/ppv/sell-video-subscriptions-wordpress" title="How To Sell Video Subscriptions With Restrict Content">Video Subscriptions With Restrict Content</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/membership"><?php esc_html_e( 'Membership Sites', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/membership/rcp-integration" title="How to create membership site with RCP and FV Player">Membership Site with Restrict Content Pro</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/membership/membership-fv-player-compatible" title="Popular Membership Plugins Compatible with FV Player">Popular Membership Plugins Compatible with FV Player</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/elearning"><?php esc_html_e( 'WordPress eLearning', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/elearning/learndash-progression-player" title="Using LearnDash And Video Progression with FV Player">LearnDash And Video Progression</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/membership-ppv-elearning/elearning/tutor-lms-video-player" title="How To Use FV Player With Tutor LMS">Tutor LMS</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/fv-player-vast-vpaid"><?php esc_html_e( 'FV Player VAST/VPAID', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/tools"><?php esc_html_e( 'Tools', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/migration-wizard" title="How to Use FV Player Migration Wizard">FV Player Migration Wizard</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/how-shortcode-conversion-tool" title="How To Use The Shortcode Conversion tool">Shortcode Conversion tool</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/rollback-player-version" title="How To Rollback FV Player Version">Version Rollback For FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/tools/how-to-completely-remove-fv-player" title="How To Completely Remove FV Player">Complete Uninstall</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/live-streaming"><?php esc_html_e( 'Live Streaming', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/live-streaming/live-streaming-youtube" title="Live Streaming With YouTube">Live Streaming With YouTube</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/live-streaming/stream-with-viloud" title="Live Streaming With Viloud">Live Streaming With Viloud</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/live-streaming/live-streaming-vimeo" title="Live Streaming With Vimeo">Live Streaming With Vimeo</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/video-ads"><?php esc_html_e( 'Video Advertising', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/playback-stats" title="Playback Stats">Playback Stats</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/google-analytics-videos-4" title="Using Google Analytics 4 with FV Player">Google Analytics 4</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/google-analytics-fv-player" title="Using Google Universal Analytics with FV Player">Google Universal Analytics</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/analytics/matomo-analytics-fv-player" title="Using Matomo Analytics with FV Player">Matomo Analytics</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/analytics"><?php esc_html_e( 'Analytics', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/video-ads/google-ads"><?php esc_html_e( 'Google Ads', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/google-ads/google-advertising-options" title="Google Video Advertising Options">Google Video Advertising Options</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/video-ads/google-ads/incorporating-google-adsense" title="Incorporating Google Ads (AdSense)">Incorporating Google Ads (AdSense)</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/casting"><?php esc_html_e( 'Casting Options', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/casting/chromecast" title="Using FV Player with Chromecast">Chromecast</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/audio"><?php esc_html_e( 'Audio Player', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/audio/audio-tracks-player" title="How to Use Audio Tracks in FV Player">Audio Tracks in FV Player</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/audio/multiple-audio-tracks-player" title="How to Use Multiple Audio Tracks with FV Player">Multiple Audio Tracks</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/troubleshooting"><?php esc_html_e( 'Troubleshooting', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/developers"><?php esc_html_e( 'For Developers', 'fv-player' ); ?></a></h3>
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
        <h3><a target="_blank" href="https://foliovision.com/player/developers/changelog"><?php esc_html_e( 'Changelog', 'fv-player' ); ?></a></h3>
        <ul>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/changelog/fv-player-pro" title="FV Player Pro Changelog">FV Player Pro Changelog</a></li>
          <li class="page_item"><a target="_blank" href="https://foliovision.com/player/developers/changelog/player-vast-changelog" title="FV Player VAST Changelog">FV Player VAST Changelog</a></li>
        </ul>
      </div>
      <div class="usage-section">
        <h3><a target="_blank" href="https://foliovision.com/player/legal"><?php esc_html_e( 'Legal', 'fv-player' ); ?></a></h3>
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
      echo "<p><code>" . esc_html( $query[0] ) . "</code></p>";
    }
  }
}

function fv_flowplayer_admin_embedded_on() {
  global $wpdb;
  $players_with_no_posts = $wpdb->get_var( "SELECT count(p.id) FROM {$wpdb->prefix}fv_player_players AS p LEFT JOIN {$wpdb->prefix}fv_player_playermeta AS m ON p.id = m.id_player AND m.meta_key = 'post_id' OR m.id IS NULL WHERE m.id IS NULL" );

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
    <p>It appears there are <?php echo intval( $players_with_no_posts ); ?> players which do not belong to any post.</p>
    <a href="<?php echo esc_url( $url ); ?>" class="button">Fix</a>

  <?php else : ?>
    <p>All of your FV Players seem to have a post associated.</p>
  <?php endif;
}


function fv_flowplayer_admin_rollback() {
  global $fv_wp_flowplayer_ver;
  $base = 'admin.php?page=fvplayer&action=fv-player-rollback&version=';
  ?>
    <p>Are you having issues with version <?php echo esc_attr( $fv_wp_flowplayer_ver ); ?>?</p>
    <p>You can go back to the previous 7.5 version - without changes to Chromecast, WordPress audio/video handling, MPEG-DASH and YouTube:</p>
    </div>
<div class="usage-section">
<h3><a href="<?php echo wp_nonce_url( admin_url($base.'7.5.29.7210'), 'fv-player-rollback' ); ?>" class="button">Reinstall version 7.5.29.7210</a></h3>
  <?php
}

function fv_flowplayer_admin_uninstall() {
  global $fv_fp;

  ?>
    <p><?php echo wp_kses( __( 'Check this box if you would like FV Player to completely remove all of its data when the plugin is deleted. The <code>[fvplayer]</code> shortcodes will stop working.', 'fv-player' ), array( 'code' => array() ) ); ?></p>
    <table class="form-table2">
      <?php $fv_fp->_get_checkbox(__( 'Remove all data', 'fv-player' ), 'remove_all_data' , __( 'This action is irreversible, please backup your website if you are not absolutely sure.', 'fv-player' )); ?>

      <tr>
        <td></td>
        <td>
          <?php
          // Verify that uninstall.php is there only if needed
          $remove_all_data = $fv_fp->_get_option( 'remove_all_data' );

          if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
          }

          $plugin_folder = basename( dirname( dirname( __FILE__ ) ) );

          $wp_filesystem       = new WP_Filesystem_Direct( '' );
          $uninstall_file_hint = 'wp-content/plugings/' . $plugin_folder . '/uninstall.php';
          $uninstall_file_real = $wp_filesystem->wp_plugins_dir() . $plugin_folder . '/uninstall.php';

          if ( $remove_all_data && $wp_filesystem->exists( $uninstall_file_real ) ) : ?>
            <p style="font-weight: bold; color: #f00"><?php _e( 'Warning: If you deactivate and delete FV Player, all of its data will be removed!', 'fv-player'); ?>
          <?php elseif ( $remove_all_data && ! $wp_filesystem->exists( $uninstall_file_real ) ) : ?>
            <p>
              <?php printf( __( 'The <code>%s</code> file failed to create, full uninstall will not work.', 'fv-player' ), $uninstall_file_hint ); ?>
            </p>
          <?php elseif ( ! $remove_all_data && $wp_filesystem->exists( $uninstall_file_real ) ) : ?>
            <?php printf( __( 'The <code>%s</code> file is still present, please remove it by hand.', 'fv-player' ), $uninstall_file_hint ); ?>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td colspan="4">
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#" data-reload="true"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
  array('id' => 'fv_flowplayer_settings',           'hash' => 'tab_basic',    	'name' => __( 'Setup', 'fv-player' ) ),
  array('id' => 'fv_flowplayer_settings_skin',      'hash' => 'tab_skin',     	'name' => __( 'Skin', 'fv-player' ) ),
  array('id' => 'fv_flowplayer_settings_hosting',   'hash' => 'tab_hosting',  	'name' => __( 'Hosting', 'fv-player' ) ),
  array('id' => 'fv_flowplayer_settings_actions',   'hash' => 'tab_actions',  	'name' => __( 'Actions', 'fv-player' ) ),
  array('id' => 'fv_flowplayer_settings_video_ads',	'hash' => 'tab_video_ads', 	'name' => __( 'Video Ads', 'fv-player' ) ),
  array('id' => 'fv_flowplayer_settings_tools',     'hash' => 'tab_tools',     	'name' => __( 'Tools', 'fv-player' ) ),
  array('id' => 'fv_flowplayer_settings_help',      'hash' => 'tab_help',     	'name' => __( 'Help', 'fv-player' ) ),
);



$fv_player_aSettingsTabs = apply_filters('fv_player_admin_settings_tabs',$fv_player_aSettingsTabs);

/* Setup tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description', 'fv_flowplayer_settings', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_interface_options', __( 'Post Interface Options', 'fv-player' ), 'fv_flowplayer_admin_interface_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_default_options', __( 'Sitewide FV Player Defaults', 'fv-player' ), 'fv_flowplayer_admin_default_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_autoplay_and_preloading', __( 'Autoplay and preloading', 'fv-player' ), 'fv_flowplayer_admin_autoplay_and_preloading', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_integrations', __( 'Integrations/Compatibility', 'fv-player' ), 'fv_flowplayer_admin_integrations', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_mobile', __( 'Mobile Settings', 'fv-player' ), 'fv_flowplayer_admin_mobile', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_seo', __( 'Video SEO', 'fv-player' ), 'fv_flowplayer_admin_seo', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_privacy', __( 'Privacy Settings', 'fv-player' ), 'fv_flowplayer_admin_privacy', 'fv_flowplayer_settings', 'normal' );

if( !class_exists('FV_Player_Pro') ) {
  add_meta_box( 'fv_player_pro', __( 'Pro Features', 'fv-player' ), 'fv_flowplayer_admin_pro', 'fv_flowplayer_settings', 'normal', 'low' );
}

/* Skin Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_skin', 'fv_flowplayer_settings_skin', 'normal', 'high' );
add_meta_box( 'flowplayer-wrapper', __( 'Player Skin', 'fv-player' ), 'fv_flowplayer_admin_skin', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_playlist', __( 'Playlist', 'fv-player' ), 'fv_flowplayer_admin_skin_playlist', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_custom_css', __( 'Custom CSS', 'fv-player' ), 'fv_flowplayer_admin_custom_css', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_subtitles', __( 'Subtitles', 'fv-player' ), 'fv_flowplayer_admin_skin_subtitles', 'fv_flowplayer_settings_skin', 'normal' );
add_meta_box( 'fv_flowplayer_skin_sticky', __( 'Sticky Video', 'fv-player' ), 'fv_flowplayer_admin_skin_sticky', 'fv_flowplayer_settings_skin', 'normal' );

/* Hosting Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_hosting', 'fv_flowplayer_settings_hosting', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_amazon_options', __( 'Amazon S3 Protected Content', 'fv-player' ), 'fv_flowplayer_admin_amazon_options', 'fv_flowplayer_settings_hosting', 'normal' );

/* Actions Tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_actions', 'fv_flowplayer_settings_actions', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_end_of_video', __( 'End of Video', 'fv-player' ), 'fv_flowplayer_admin_end_of_video' , 'fv_flowplayer_settings_actions', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_popups', __( 'Custom Popups', 'fv-player' ), 'fv_flowplayer_admin_popups' , 'fv_flowplayer_settings_actions', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_ads', __( 'Overlay', 'fv-player' ), 'fv_flowplayer_admin_overlay', 'fv_flowplayer_settings_actions', 'normal' );

/* Video Ads Tab */
if( !class_exists('FV_Player_Pro') ) {
  add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_video_ads', 'fv_flowplayer_settings_video_ads', 'normal', 'high' );
  add_meta_box( 'fv_flowplayer_ads', __( 'Video Ads', 'fv-player' ), 'fv_flowplayer_admin_video_ads', 'fv_flowplayer_settings_video_ads', 'normal' );
}

/* Tools tab */
add_meta_box( 'fv_flowplayer_description', ' ', 'fv_flowplayer_admin_description_tools', 'fv_flowplayer_settings_tools', 'normal', 'high' );
add_meta_box( 'fv_flowplayer_conversion', __( 'Conversion', 'fv-player' ),  'fv_flowplayer_settings_box_conversion', 'fv_flowplayer_settings_tools', 'normal' );
add_meta_box( 'fv_flowplayer_database', __( 'Database', 'fv-player' ), 'fv_flowplayer_admin_database', 'fv_flowplayer_settings_tools', 'normal', 'low' );
add_meta_box( 'fv_flowplayer_embedded_on', __( 'Embeded Posts Information', 'fv-player' ), 'fv_flowplayer_admin_embedded_on', 'fv_flowplayer_settings_tools', 'normal', 'low' );
add_meta_box( 'fv_flowplayer_rollback', __( 'Rollback', 'fv-player' ), 'fv_flowplayer_admin_rollback', 'fv_flowplayer_settings_tools', 'normal', 'low' );
add_meta_box( 'fv_flowplayer_uninstall', __( 'Uninstall', 'fv-player' ), 'fv_flowplayer_admin_uninstall', 'fv_flowplayer_settings_tools', 'normal', 'low' );

/* Help tab */
add_meta_box( 'fv_flowplayer_usage', __( 'Usage', 'fv-player' ), 'fv_flowplayer_admin_usage', 'fv_flowplayer_settings_help', 'normal', 'high' );

?>

<div class="wrap">
	<div style="position: absolute; margin-top: 10px; right: 10px;">
		<a href="https://foliovision.com/player" target="_blank" title="<?php esc_attr_e( 'Documentation', 'fv-player' ); ?>"><img alt="visit foliovision" src="<?php echo flowplayer::get_plugin_url().'/images/fv-logo.png' ?>" /></a>
	</div>
  <div>
    <div id="icon-options-general" class="icon32"></div>
    <h2>FV Player</h2>
  </div>

  <?php
  global $fv_fp;
  do_action('fv_player_settings_pre');
  ?>

  <form id="wpfp_options" method="post" action="">

    <p id="fv_flowplayer_admin_buttons">
      <?php if( preg_match( '!^\$\d+!', $fv_fp->_get_option('key') ) ) : ?>
        <?php
        $fv_player_pro_path = FV_Wordpress_Flowplayer_Plugin_Private::get_plugin_path('fv-player-pro');
        if( is_plugin_inactive($fv_player_pro_path) && !is_wp_error(validate_plugin($fv_player_pro_path)) ) : ?>
          <input type="button" class='button fv-license-yellow fv_wp_flowplayer_activate_extension' data-plugin="<?php echo esc_attr( $fv_player_pro_path ); ?>" value="<?php esc_attr_e( 'Enable the Pro extension', 'fv-player' ); ?>" /> <img style="display: none; " src="<?php echo esc_attr( site_url() ); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
        <?php elseif( is_plugin_active($fv_player_pro_path) && !is_wp_error(validate_plugin($fv_player_pro_path)) ) : ?>
          <input type="button" class="button fv-license-active" onclick="window.location.href += '&fv_player_pro_installed=yes#fv_player_pro'" value="<?php esc_attr_e( 'Pro pack installed', 'fv-player' ); ?>" />
        <?php else : ?>
          <input type="submit" class="button fv-license-yellow" value="<?php esc_attr_e( 'Install Pro extension', 'fv-player' ); ?>" /><?php wp_nonce_field('fv_player_pro_install', 'nonce_fv_player_pro_install') ?>
        <?php endif; ?>
      <?php else : ?>
        <input type="button" class="button fv-license-inactive" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_license', '<?php echo wp_create_nonce( 'fv_wp_flowplayer_check_license' ); ?>'); return false" value="<?php esc_attr_e( 'Apply Pro upgrade', 'fv-player' ); ?>" />
      <?php endif; ?>

      <input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_template', '<?php echo wp_create_nonce( 'fv_wp_flowplayer_check_template' ); ?>'); return false" value="<?php esc_attr_e( 'Check template', 'fv-player' ); ?>" />
      <!--<input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_files', '<?php echo wp_create_nonce( 'fv_wp_flowplayer_check_files' ); ?>')" value="<?php esc_attr_e( 'Check videos', 'fv-player' ); ?>" />-->

      <?php if( !$fv_fp->_get_option('key') ) : ?>
        <a title="<?php esc_attr_e( 'Click here for license info', 'fv-player' ); ?>" target="_blank" href="https://foliovision.com/player/download"><span class="dashicons dashicons-editor-help"></span></a>
      <?php endif; ?>
      <img class="fv_wp_flowplayer_check_license-spin" style="display: none; " src="<?php echo esc_attr( site_url() ); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
      <img class="fv_wp_flowplayer_check_template-spin" style="display: none; " src="<?php echo esc_attr( site_url() ); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
      <img class="fv_wp_flowplayer_check_files-spin" style="display: none; " src="<?php echo esc_attr( site_url() ); ?>/wp-includes/images/wpspin.gif" width="16" height="16" />
      <?php do_action('fv_flowplayer_admin_buttons_after'); ?>
    </p>
    <div id="fv_flowplayer_admin_notices">
    </div>

    <?php if( preg_match( '!^\$\d+!', $fv_fp->_get_option('key') ) || apply_filters('fv_player_skip_ads',false) ) : ?>
    <?php else : ?>
      <div id="fv_flowplayer_ad">
        <div class="text-part">
          <h2>FV <strong>Player</strong> Pro</h2>
          <span class="red-text"><?php esc_html_e( 'Host your videos anywhere', 'fv-player' ); ?></span>
            <ul>
            <li><?php esc_html_e( 'Pick your favorite CDN', 'fv-player' ); ?></li>
            <li><?php esc_html_e( 'Encrypt your videos to avoid downloading', 'fv-player' ); ?></li>
            <li><?php esc_html_e( 'Interactive transcript, AB loop&hellip;', 'fv-player' ); ?></li>
            </ul>
              <a href="https://foliovision.com/player/download" class="red-button"><strong><?php esc_html_e( 'Christmas sale!', 'fv-player' ); ?></strong><br /><?php esc_html_e( 'All Licenses 20% Off', 'fv-player' ); ?></a></p>
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
        <a href="#postbox-container-<?php echo esc_attr( $val['hash'] ); ?>" class="nav-tab<?php if( $key == 0 ) : ?> nav-tab-active<?php endif; ?>" style="outline: 0px;"><?php echo wp_strip_all_tags( $val['name'] );?></a>
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
      <div id='postbox-container-<?php echo esc_attr( $val['hash'] ); ?>' class='postbox-container'<?php if( $key > 0 ) : ?> style=""<?php endif; ?>>
        <?php do_meta_boxes($val['id'], 'normal', false ); ?>
      </div>
      <?php endforeach;?>
      <div style="clear: both"></div>
    </div>
    <?php
    wp_nonce_field( 'fv_flowplayer_settings_nonce', 'fv_flowplayer_settings_nonce' );
    wp_nonce_field( 'fv_flowplayer_settings_ajax_nonce', 'fv_flowplayer_settings_ajax_nonce', false );
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
    wp_nonce_field( 'meta-box-order-nonce', 'meta-box-order-nonce', false );
    ?>

  <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes">

  </form>

  <div id="fv-player-settings-save-notice"></div>

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

  function fv_flowplayer_ajax_check( type, nonce ) {
    jQuery('.'+type+'-spin').show();
    var ajaxurl = '<?php echo esc_attr( site_url() ); ?>/wp-admin/admin-ajax.php';
    jQuery.post( ajaxurl, { action: type, nonce: nonce }, function( response ) {
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
    var new_inputs = jQuery('tr.amazon-s3-1').clone();
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
    Ensure only one of "Load JavaScript everywhere" and "Optimize JavaScript loading" can be enabled
    */
    var cb_js_everywhere = jQuery('#js-everywhere'),
      cb_js_optimize = jQuery('#js-optimize');

    function check_js_everywhere( was_clicked ) {
      if( was_clicked && cb_js_optimize.prop('checked') ) {
        alert('Cannot be used together with "Optimize JavaScript loading".');
        return false;
      }

      cb_js_optimize.prop('readonly', cb_js_everywhere.prop('checked') );
    }
    cb_js_everywhere.on( 'click', check_js_everywhere );
    check_js_everywhere();

    function check_js_optimize( was_clicked ) {
      if( was_clicked && cb_js_everywhere.prop('checked') ) {
        alert('Cannot be used together with "Load JavaScript everywhere".');
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
jQuery( function($) {
  function set_visible_tab() {
    $('#fv_player_js_warning').hide();

    var anchor = window.location.hash.substring(1),
      skin_anchor = window.location.hash.substring(1);

    if( !anchor || !anchor.match(/tab_/) ) {
      if ( skin_anchor.match( /skin-tab-/ ) ) {
        anchor = 'postbox-container-tab_skin';
      } else {
        anchor = 'postbox-container-tab_basic';
      }
    }

    if ( ! skin_anchor || ! skin_anchor.match( /skin-tab-/ ) ) {
      skin_anchor = 'skin-tab-skin';
    }

    $('#fv_flowplayer_admin_tabs .nav-tab, #fv_flowplayer_admin_skin_tabs .nav-tab').removeClass('nav-tab-active');
    $('[href=\\#'+anchor+']').addClass('nav-tab-active');
    $('#dashboard-widgets .postbox-container').hide();
    $('#' + anchor).show();

    $( '[href=\\#' + skin_anchor + ']' ).addClass('nav-tab-active');
    $( '.skin-tab-content' ).hide();
    $( '#' + skin_anchor ).show();

    // scroll to the top of skin_anchor
    var offset = $( '#' + skin_anchor ).offset().top - 120;
    window.scrollTo(0, offset);
  }

  set_visible_tab();

  $( '.settings-section-link' ).on('click', function(e) {
    location.hash = $(this).attr('href');
    set_visible_tab();
    return false;
  });
});

jQuery('#fv_flowplayer_admin_tabs a').on('click',function(e){
  if ( history.pushState ) {
    history.pushState( null, null, e.target.hash );
  }

  var anchor = jQuery(this).attr('href').substring(1);
  jQuery('#fv_flowplayer_admin_tabs .nav-tab').removeClass('nav-tab-active');
  jQuery('[href=\\#'+anchor+']').addClass('nav-tab-active');
  jQuery('#dashboard-widgets .postbox-container').hide();
  jQuery('#' + anchor).show();

  return false;
});

jQuery('#fv_flowplayer_admin_skin_tabs a').on('click',function(e){
  if ( history.pushState ) {
    history.pushState( null, null, e.target.hash );
  }

  var anchor = jQuery(this).attr('href').substring(1);
  jQuery('#fv_flowplayer_admin_skin_tabs .nav-tab').removeClass('nav-tab-active');
  jQuery('[href=\\#'+anchor+']').addClass('nav-tab-active');
  jQuery( '.skin-tab-content' ).hide();
  jQuery('#' + anchor).show();

  return false;
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
