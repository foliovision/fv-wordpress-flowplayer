<?php
/**
 * Displays administrator backend.
 */
 
delete_option('fv_wordpress_flowplayer_deferred_notices');
?>

<style>
#responsive, #engine { width: 180px; }
div.green { background-color: #e0ffe0; border-color: #88AA88; } 
</style>

<div class="wrap">
	<div style="position: absolute; top: 10px; right: 10px;">
		<a href="https://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer" target="_blank" title="Documentation"><img alt="visit foliovision" src="http://foliovision.com/shared/fv-logo.png" /></a>
	</div>
  <div>
    <div id="icon-options-general" class="icon32"></div>
    <h2>FV Wordpress Flowplayer</h2>
  </div>	  
  <p id="fv_flowplayer_admin_buttons">
  	<input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_template')" value="Check template" /> 
  	<input type="button" class="button" onclick="fv_flowplayer_ajax_check('fv_wp_flowplayer_check_files')" value="Check videos" /> 
  	<img class="fv_wp_flowplayer_check_template-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /> 
  	<img class="fv_wp_flowplayer_check_files-spin" style="display: none; " src="<?php echo site_url(); ?>/wp-includes/images/wpspin.gif" width="16" height="16" /> 
  </p>
  <div id="fv_flowplayer_admin_notices">
  </div>
  <?php if (isset($fv_fp->conf['key']) && $fv_fp->conf['key'] == 'false') : ?>
		<div id="fv_flowplayer_ad">
			<div class="text-part">
				<h2>FV Wordpress<strong>Flowplayer</strong></h2>
				<span class="red-text">with your own branding</span>
					<ul>
					<li>Put up your own logo</li>
					<li>Or remove the logo completely</li>
					<li>The best video plugin for Wordpress</li>
					</ul>
						<a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy" class="red-button"><strong>Summer Special!</strong><br />All Licenses 20% off</a></p>
				</div>
				<div class="graphic-part">
					<a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy">
					<img width="297" height="239" border="0" src="<?php echo plugins_url( 'images/fv-wp-flowplayer-led-monitor.png' , dirname(__FILE__) ) ?>"> </a>
				</div>
		</div>
  <?php endif; ?>	
  <style>
  #wpfp_options .postbox h3 { cursor: default; }
  </style>
  <form id="wpfp_options" method="post" action="">
  	<div id="poststuff" class="ui-sortable">            
			<div class="postbox">
				<?php //echo flowplayer_check_errors($fv_fp); ?>
				<h3>Default Flowplayer Options</h3>
				<table class="form-table2" style="margin: 5px; ">
					<tr>
						<td style="width: 250px;"><label for="autoplay">AutoPlay:</label></td>
						<td style="text-align:right;">
							<select id="autoplay" name="autoplay"><?php echo flowplayer_bool_select($fv_fp->conf['autoplay']); ?></select> 	
						</td>
						<td colspan="2" rowspan="10"  style="padding-left: 30px; vertical-align: top;">
							<div id="content">
								<div class="flowplayer is-splash"
								<?php if ($fv_fp->conf['engine'] == 'flash') echo 'data-engine="flash"'; ?>
								data-swf="<?php echo RELATIVE_PATH ?>/flowplayer/flowplayer.swf"
								data-ratio="0.417" 
								style="width:<?php echo $fv_fp->conf['width']; ?>px; max-height:<?php echo $fv_fp->conf['height']; ?>px;"
								<?php if ($fv_fp->conf['allowfullscreen'] == 'false') echo 'data-fullscreen="false"'; ?>
								<?php if (isset($fv_fp->conf['key']) && $fv_fp->conf['key'] != 'false' && strlen($fv_fp->conf['key']) > 0) {echo 'data-key="' . $fv_fp->conf['key'] . '"'; $commercial_key = true;} ?>
								<?php if ( isset($commercial_key) && isset($fv_fp->conf['logo']) && $fv_fp->conf['logo'] != 'false' && strlen($fv_fp->conf['logo']) > 0) echo ' data-logo="' . $fv_fp->conf['logo'] . '"'; ?>
								<?php if ($fv_fp->conf['scaling'] == "fit") echo 'data-flashfit="true"';; ?>
								>
									<video poster="http://foliovision.com/videos/example.jpg"<?php if (isset($fv_fp->conf['autoplay']) && $fv_fp->conf['autoplay'] == 'true') echo ' autoplay'; ?><?php if (isset($fv_fp->conf['autobuffer']) && $fv_fp->conf['autobuffer'] == 'true') echo ' preload'; ?>>
										<source src="http://foliovision.com/videos/example.mp4" type="video/mp4" />
									</video>
								</div>    
							</div>
						</td>
					</tr>
					<tr>
						<td><label for="autobuffer">Auto Buffering:</label></td>
						<td style="text-align:right">
							<select id="autobuffer" name="autobuffer"><?php echo flowplayer_bool_select($fv_fp->conf['autobuffer']); ?></select>
						</td>
					</tr>
					<tr>
							<td><label for="popupbox">Popup Box:</label></td>
							<td style="text-align:right">
								<select id="popupbox" name="popupbox"><?php echo flowplayer_bool_select($fv_fp->conf['popupbox']); ?></select>
							</td>
						</tr>
					<tr>
						<td><label for="allowfullscreen">Enable Full-screen Mode:</label></td>
						<td style="text-align:right">
							<select id="allowfullscreen" name="allowfullscreen"><?php echo flowplayer_bool_select($fv_fp->conf['allowfullscreen']); ?></select>
						</td>
					</tr>
					<tr>
						<td><label for="scaling">Fit scaling (<abbr title="If set to true, the original aspect ratio of the video will be used to display the video in fullscreen mode as well as when embedded in the page.">?</abbr>):</label></td>
						<td style="text-align:right">
							<select id="scaling" name="scaling"><?php echo flowplayer_bool_select($fv_fp->conf['scaling']); ?></select>
						</td>
					</tr>
					<tr>
						<td><label for="allowfullscreen">Disable embedding:</label></td>
						<td style="text-align:right">
							<select id="disableembedding" name="disableembedding"><?php echo flowplayer_bool_select($fv_fp->conf['disableembedding']); ?></select>
						</td>
					</tr>
					<tr>
						<td><label for="postthumbnail">Enable Post Thumbnail:</label></td>
						<td style="text-align:right">
							<select id="postthumbnail" name="postthumbnail"><?php echo flowplayer_bool_select($fv_fp->conf['postthumbnail']); ?></select>
						</td>
					</tr>    	
					<tr>
						<td><label for="commas">Convert old shortcodes with commas (<abbr title="Older versions of this plugin used commas to sepparate shortcode parameters. This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.">?</abbr>):</label></td>
						<td style="text-align:right">
							<select id="commas" name="commas"><?php echo flowplayer_bool_select($fv_fp->conf['commas']); ?></select>
						</td>
					</tr>
					<tr>
						<td><label for="engine">Preferred Flowplayer engine (<abbr title="Default setting - IE9 and IE10 get Flash (due to server compatibility issues), Firefox in Windows gets Flash for M4V files (due to issues with M4V in it on PC), everyone else gets HTML5 (with Flash fallback)">?</abbr>):</label></td>
						<td style="text-align:right">
							<select id="engine" name="engine">
							  <!--<option value="flash"<?php if( $fv_fp->conf['engine'] == 'flash' ) echo ' selected="selected"'; ?>>Flash (with HTML5 fallback)</option>-->
							  <option value="default"<?php if( $fv_fp->conf['engine'] == 'default' ) echo ' selected="selected"'; ?>>Default (mixed)</option>
							  <option value="html5"<?php if( $fv_fp->conf['engine'] == 'html5'  ) echo ' selected="selected"'; ?>>HTML5 (with Flash fallback)</option>
              </select> 							
						</td>
					</tr>
					<tr>
					  <td><label for="width">Default video size [px]:</label></td>
						<td style="text-align:right"> 					
							<label for="width">W:</label>&nbsp;<input type="text" size="4" name="width" id="width" value="<?php echo trim($fv_fp->conf['width']); ?>" />  
							<label for="height">H:</label>&nbsp;<input type="text" size="4" name="height" id="height" value="<?php echo trim($fv_fp->conf['height']); ?>" />							
						</td>
					</tr>
					<tr>
					  <td><label for="responsive">Video player size (<abbr title="Default setting - respects width and height setting of the video, but allows it to size down to be responsive">?</abbr>):</label></td>
						<td style="text-align:right"> 					
							<select id="responsive" name="responsive">
							  <option value="responsive"<?php if( $fv_fp->conf['engine'] == 'responsive' ) echo ' selected="selected"'; ?>>Default (responsive)</option>
							  <option value="fixed"<?php if( $fv_fp->conf['engine'] == 'fixed'  ) echo ' selected="selected"'; ?>>Fixed dimensions</option>
              </select> 					
						</td>
					</tr>
					<tr>
					  <td><label for="videochecker">Front-end video checker</label></td>
						<td style="text-align:right"> 					
							<select id="videochecker" name="videochecker">
							  <option value="enabled"<?php if( $fv_fp->conf['videochecker'] == 'enabled' ) echo ' selected="selected"'; ?>>Enabled</option>
							  <option value="errors"<?php if( $fv_fp->conf['videochecker'] == 'errors'  ) echo ' selected="selected"'; ?>>Errors only</option>
                <option value="off"<?php if( $fv_fp->conf['videochecker'] == 'off'  ) echo ' selected="selected"'; ?>>Turn off</option>
              </select> 					
						</td>
					</tr>          

					<tr>
						<td><label for="googleanalytics">Google Analytics ID:</label></td>
						<td><input type="text" size="40" name="googleanalytics" id="googleanalytics" value="<?php echo trim($fv_fp->conf['googleanalytics']); ?>" /></td>
					</tr>
					<tr>
						<td><label for="key">Commercial License Key:</label></td>
						<td><input type="text" size="40" name="key" id="key" value="<?php echo trim($fv_fp->conf['key']); ?>" /></td>
					</tr>
					<tr>
						<td><label for="logo">Logo:</label></td>
						<td><input type="text" size="40" name="logo" id="logo" value="<?php echo trim($fv_fp->conf['logo']); ?>" /></td>
					</tr>
					<tr>    		    		
						<td colspan="2" style="text-align: right">Or <a title="Add FV WP Flowplayer Logo" href="media-upload.php?type=fvplayer_logo&TB_iframe=true&width=500&height=300" class="thickbox" >open media library</a> to upload logo.</td>
					</tr>      
					<tr>
						<td><label for="rtmp">Flash streaming server<br />(Amazon CloudFront domain):</label></td>
						<td><input type="text" size="40" name="rtmp" id="rtmp" value="<?php echo trim($fv_fp->conf['rtmp']); ?>" /></td>
					</tr>	
					<tr>
						<td colspan="2"><p><strong>Ads</strong></p></td>
					</tr>					
					<tr>
						<td colspan="2">
							<label for="ad">Default Ad Code:</label><br />
							<textarea id="ad" name="ad" class="large-text code"><?php if( isset($fv_fp->conf['ad']) ) echo trim($fv_fp->conf['ad']); ?></textarea>			
						</td>
					</tr>
					<tr>
					  <td><label for="width">Default ad size [px]:</label></td>
						<td style="text-align:right"> 					
							<label for="ad_width">W:</label>&nbsp;<input type="text" size="4" name="ad_width" id="ad_width" value="<?php echo trim($fv_fp->conf['ad_width']); ?>" />  
							<label for="ad_height">H:</label>&nbsp;<input type="text" size="4" name="ad_height" id="ad_height" value="<?php echo trim($fv_fp->conf['ad_height']); ?>" />							
						</td>
					</tr>					
				</table>
				<table class="form-table2" style="margin: 5px; ">
					<tr>
						<td colspan="4"><p><strong>Colors</strong></p></td>
					</tr>
					<?php include dirname( __FILE__ ) . '/../view/colours.php'; ?>
					<tr>
						<td><label for="font-face">Player font face</label></td>
						<td style="text-align:right" colspan="3">
							<select id="font-face" name="font-face">
							  <option value="&quot;Courier New&quot;, Courier, monospace"<?php if( $fv_fp->conf['font-face'] == "\"Courier New\", Courier, monospace" ) echo ' selected="selected"'; ?>>Courier New</option>										  
							  <option value="Tahoma, Geneva, sans-serif"<?php if( $fv_fp->conf['font-face'] == "Tahoma, Geneva, sans-serif" ) echo ' selected="selected"'; ?>>Tahoma, Geneva</option>
							  <option value="inherit"<?php if( $fv_fp->conf['font-face'] == 'inherit'  ) echo ' selected="selected"'; ?>>(inherit from template)</option>
              </select> 							
						</td>
					</tr>					
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Apply Changes" style="margin-top: 2ex;"/>
						</td>
					</tr>
				</table>    
    	</div>
    </div>  
        
    
    <div id="poststuff" class="ui-sortable" style="float: left; width: 50%; ">            
			<div class="postbox">    
				<h3 id="interface">Interface options</h3>
				<table class="form-table2" style="margin: 5px; ">
          <tr>
            <td colspan="2"><p>Which features should be available in shortcode editor?</p></td>
          </tr>
					<tr>
						<td style="width: 520px;"><label for="allowuploads">Allow User Uploads:</label></td>
						<td style="text-align:right">
              <input type="hidden" name="allowuploads" value="false" />
              <input type="checkbox" name="allowuploads" id="allowuploads" value="true" <?php if( isset($fv_fp->conf['allowuploads']) && $fv_fp->conf['allowuploads'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   
					<tr>          
						<td><label for="interface[popup]">HTML popup:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[popup]" value="false" />
							<input type="checkbox" name="interface[popup]" id="interface[popup]" value="true" <?php if( isset($fv_fp->conf['interface']['popup']) && $fv_fp->conf['interface']['popup'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>    
					<tr>          
						<td style="width: 330px;"><label for="interface[redirect]">Redirect:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[redirect]" value="false" />
							<input type="checkbox" name="interface[redirect]" id="interface[redirect]" value="true" <?php if( isset($fv_fp->conf['interface']['redirect']) && $fv_fp->conf['interface']['redirect'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>                        
					<tr>          
						<td style="width: 330px;"><label for="interface[autoplay]">AutoPlay:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[autoplay]" value="false" />
							<input type="checkbox" name="interface[autoplay]" id="interface[autoplay]" value="true" <?php if( isset($fv_fp->conf['interface']['autoplay']) && $fv_fp->conf['interface']['autoplay'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td style="width: 330px;"><label for="interface[loop]">Loop:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[loop]" value="false" />
							<input type="checkbox" name="interface[loop]" id="interface[loop]" value="true" <?php if( isset($fv_fp->conf['interface']['loop']) && $fv_fp->conf['interface']['loop'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td style="width: 330px;"><label for="interface[splashend]">Splash end:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[splashend]" value="false" />
							<input type="checkbox" name="interface[splashend]" id="interface[splashend]" value="true" <?php if( isset($fv_fp->conf['interface']['splashend']) && $fv_fp->conf['interface']['splashend'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>     
					<tr>          
						<td style="width: 330px;"><label for="interface[embed]">Embed:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[embed]" value="false" />
							<input type="checkbox" name="interface[embed]" id="interface[embed]" value="true" <?php if( isset($fv_fp->conf['interface']['embed']) && $fv_fp->conf['interface']['embed'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>    
					<tr>          
						<td style="width: 330px;"><label for="interface[subtitles]">Subtitles:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[subtitles]" value="false" />
							<input type="checkbox" name="interface[subtitles]" id="interface[subtitles]" value="true" <?php if( isset($fv_fp->conf['interface']['subtitles']) && $fv_fp->conf['interface']['subtitles'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>  
					<tr>          
						<td style="width: 330px;"><label for="interface[ads]">Ads: <span style="color: #e00; font-weight: bold">NEW</span></label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[ads]" value="false" />
							<input type="checkbox" name="interface[ads]" id="interface[ads]" value="true" <?php if( isset($fv_fp->conf['interface']['ads']) && $fv_fp->conf['interface']['ads'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   	
					<tr>          
						<td style="width: 330px;"><label for="interface[mobile]">Mobile video: <span style="color: #e00; font-weight: bold">NEW</span></label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[mobile]" value="false" />
							<input type="checkbox" name="interface[mobile]" id="interface[mobile]" value="true" <?php if( isset($fv_fp->conf['interface']['mobile']) && $fv_fp->conf['interface']['mobile'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   		
					<tr>          
						<td style="width: 330px;"><label for="interface[align]">Align: <span style="color: #e00; font-weight: bold">NEW</span></label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[align]" value="false" />
							<input type="checkbox" name="interface[align]" id="interface[align]" value="true" <?php if( isset($fv_fp->conf['interface']['align']) && $fv_fp->conf['interface']['align'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr> 					
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Apply Changes" style="margin-top: 2ex;"/>
						</td>
					</tr>                                    
				</table>
			</div>
		</div>
        
    	
  	<div id="poststuff" class="ui-sortable" style="float: right; width: 49%; ">            
			<div class="postbox">    
				<h3>Description</h3>
				<table class="form-table">
					<tr>
						<td colspan="4" style="text-align: justify;">
							
							<ul>
								<li>FV Wordpress Flowplayer is a completely non-commercial solution for embedding video on Wordpress websites.</li>
								<li>Supported video formats are <strong>MP4</strong>, <strong>WEBM</strong>, <strong>OGV</strong> and <strong>FLV</strong>. Multiple videos can be displayed in one post or page.</li>
								<li>Default options for all the embedded videos can be set in the menu above.</li>
							</ul>
							<!--<p>
							The previous version of FV Wordpress Flowplayer uses flash based technology and <code>[flowplayer]</code> shortcode.
							This version uses HTML5 (with flash fallback) technology with both <code>[fvplayer]</code> and <code>[flowplayer]</code> shortcode.
							You can run the conversion script. To make sure you only use <code>[fvplayer]</code>.
							<strong>Before running the conversion script, do a backup of your database!</strong>
							</p>     
							<input style="float: left; margin-bottom: 10px;" type="button" name="convert" class="button-primary" value="Run Conversion Script" onclick="flowplayer_conversion_script()"/>
							<div id="fv-flowplayer-loader" style="background: url(<?php echo plugins_url( 'images/wpspin.gif' , dirname(__FILE__) ); ?>) no-repeat center left; width: 16px; height: 24px; float: left; margin-left: 5px; display: none;" /></div>
							<div style="clear: both;"></div>
							<div id="conversion-results"></div>
							<div style="clear: both;"></div>-->
						</td>
					</tr>
				</table>
			</div>
		</div>
    
    <div style="clear: both;"></div>
    
  	<div id="poststuff" class="ui-sortable">            
			<div class="postbox">	
				<h3>Usage:</h3>
				<table class="form-table">
					<tr>
						<td colspan="4" style="text-align: justify;">      									
							<p>
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
							</ul>
						</td>
						<td></td>
					</tr>
				</table>
    	</div>
    </div>
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
</script>
