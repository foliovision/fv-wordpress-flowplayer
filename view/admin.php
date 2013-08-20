<?php
/**
 * Displays administrator backend.
 */
 
delete_option('fv_wordpress_flowplayer_deferred_notices');


function fv_flowplayer_admin_ads() {
	global $fv_fp;
?>
					<table class="form-table2" style="width: 100%">	
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
									<textarea rows="5" name="ad_css" id="ad_css" class="large-text code"><?php if( isset($fv_fp->conf['ad_css']) ) echo trim($fv_fp->conf['ad_css']); ?></textarea>
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
								<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" style="margin-top: 2ex;"/>
							</td>
						</tr>							
					</table>
<?php
}


function fv_flowplayer_admin_amazon_options() {
	global $fv_fp;
?>
				<style>
				#fv_flowplayer_amazon_options label { float: right }
				</style>
				<table class="form-table2" style="margin: 5px; ">
					<tr>
						<td colspan="2">
							<p>Secured Amazon S3 URLs are only recommended for member-only sections of the site. They don't work well with cache plugins, as they expire. Member-only sections in general require users to log in and thus use no WP cache. Read more in the <a href="#" target="_blank">Using Amazon S3 secure content in FV Flowplayer guide</a>.</p>
						</td>
					</tr>
					<tr>
						<td style="width: 200px"><label for="amazon_expire">Default Expire Time [minutes] (<abbr title="Each video duration is stored on post save and then used as the expire time.">?</abbr>):</label></td>
						<td><input type="text" size="40" name="amazon_expire" id="amazon_expire" value="<?php echo intval($fv_fp->conf['amazon_expire']); ?>" style="width: 100%" /></td>
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
?>					
					<tr<?php echo $amazon_tr_class; ?>>
						<td><label for="amazon_bucket[]">Amazon Bucket (<abbr title="We recommend that you simply put all of your protected video into a single bucket and enter its name here. All matching videos will use the protected URLs.">?</abbr>):</label></td>
						<td><input type="text" size="40" name="amazon_bucket[]" id="amazon_bucket[]" value="<?php echo trim($item); ?>" style="width: 100%" /></td>
					</tr>							
					<tr<?php echo $amazon_tr_class; ?>>
						<td><label for="amazon_key[]">Access Key ID:</label></td>
						<td><input type="text" size="40" name="amazon_key[]" id="amazon_key[]" value="<?php echo trim($fv_fp->conf['amazon_key'][$key]); ?>" style="width: 100%" /></td>
					</tr>	
					<tr<?php echo $amazon_tr_class; ?>>
						<td><label for="amazon_secret[]">Secret Access Key:</label></td>
						<td><input type="text" size="40" name="amazon_secret[]" id="amazon_secret[]" value="<?php echo trim($fv_fp->conf['amazon_secret'][$key]); ?>" style="width: 100%" /></td>
					</tr>
					<tr<?php echo $amazon_tr_class; ?>>
						<td colspan="2">
							<div class="alignright fv_fp_amazon_remove"><a href="#" onclick="fv_fp_amazon_s3_remove(this); return false">remove</a></div><div style="clear: both"></div>
							<hr style="border: 0; border-top: 1px solid #ccc;" />
						</td>
					</tr>						
<?php
			endforeach;
?>							
					<tr class="amazon-s3-last"><td colspan="2"></td></tr>	
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" style="margin-top: 2ex;"/>
							<input type="button" id="amazon-s3-add" class="button" value="Add more Amazon S3 secure buckets" />
						</td>
					</tr>   					                                 
				</table>
<?php 
}


function fv_flowplayer_admin_default_options() {
	global $fv_fp;
?>
					<table class="form-table2" style="margin: 5px; ">
						<tr>
							<td style="width: 250px;"><label for="autoplay">AutoPlay:</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('autoplay'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="auto_buffer">Auto Buffering (<abbr title="Works for first 2 videos on the page only, to preserve your bandwidth.">?</abbr>):</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('auto_buffer'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="popupbox">Popup Box:</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('popupbox'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="allowfullscreen">Enable Full-screen Mode:</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('allowfullscreen'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="scaling">Fit scaling (<abbr title="If set to true, the original aspect ratio of the video will be used to display the video in fullscreen mode as well as when embedded in the page.">?</abbr>):</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('scaling'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="disableembedding">Disable embedding:</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('disableembedding'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="postthumbnail">Enable Post Thumbnail:</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('postthumbnail'); ?>
							</td>
						</tr>    	
						<tr>
							<td><label for="parse_commas">Convert old shortcodes with commas (<abbr title="Older versions of this plugin used commas to sepparate shortcode parameters. This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.">?</abbr>):</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('parse_commas'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="engine">Prefer Flash player by default (<abbr title="Default setting is off - IE9 and IE10 get Flash (due to server compatibility issues), Firefox in Windows gets Flash for M4V files (due to issues with M4V in it on PC), everyone else gets HTML5 (with Flash fallback)">?</abbr>):</label></td>
							<td style="text-align:left">
								<?php fv_flowplayer_admin_checkbox('engine'); ?>
							</td>
						</tr>
						<tr>
							<td><label for="fixed_size">Always use fixed size player (<abbr title="Default setting - respects width and height setting of the video, but allows it to size down to be responsive">?</abbr>):</label></td>
							<td style="text-align:left"> 					
								<?php fv_flowplayer_admin_checkbox('fixed_size'); ?>					
							</td>
						</tr>
						<tr>
							<td><label for="disable_videochecker">Disable admin video checker</label></td>
							<td style="text-align:left"> 					
								<?php fv_flowplayer_admin_checkbox('disable_videochecker'); ?>			
							</td>
						</tr>    
						<tr>
							<td><label for="width">Default video size [px]:</label></td>
							<td style="text-align:left"> 					
								<label for="width">W:</label>&nbsp;<input type="text" size="4" name="width" id="width" value="<?php echo trim($fv_fp->conf['width']); ?>" />  
								<label for="height">H:</label>&nbsp;<input type="text" size="4" name="height" id="height" value="<?php echo trim($fv_fp->conf['height']); ?>" />							
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
							<td colspan="4">
								<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" style="margin-top: 2ex;"/>
							</td>
						</tr>						
					</table>   
					<div style="clear: both"></div>
<?php
}


function fv_flowplayer_admin_description() {
?>
				<table class="form-table">
					<tr>
						<td colspan="4" style="text-align: justify;">
							<p>FV WordPress Flowplayer WordPress plugin is a free, easy-to-use, and complete solution for embedding <strong>MP4</strong>, <strong>WEBM</strong>, <strong>OGV</strong>, <strong>MOV</strong> and <strong>FLV</strong>. videos into your posts or pages. With MP4 videos, FV WordPress Flowplayer offers 98% coverage even on mobile devices.</p>
						</td>
					</tr>
				</table>
<?php
}


function fv_flowplayer_admin_interface_options() {
	global $fv_fp;
?>
				<p>Which features should be available in shortcode editor?</p>
				<table class="form-table2" style="margin: 5px; ">
					<tr>
						<td style="width: 250px"><label for="allowuploads">Allow User Uploads:</label></td>
						<td style="text-align:right">
              <input type="hidden" name="allowuploads" value="false" />
              <input type="checkbox" name="allowuploads" id="allowuploads" value="true" <?php if( isset($fv_fp->conf['allowuploads']) && $fv_fp->conf['allowuploads'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   
					<tr>          
						<td><label for="interface[popup]">HTML popup:</label></td>
						<td style="text-align:right">
              <input type="hidden" name="interface[popup]" value="false" />
							<input type="checkbox" name="interface[popup]" id="interface[popup]" value="true" <?php if( isset($fv_fp->conf['interface']['popup']) && $fv_fp->conf['interface']['popup'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>    
					<tr>          
						<td><label for="interface[redirect]">Redirect:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[redirect]" value="false" />
							<input type="checkbox" name="interface[redirect]" id="interface[redirect]" value="true" <?php if( isset($fv_fp->conf['interface']['redirect']) && $fv_fp->conf['interface']['redirect'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>                        
					<tr>          
						<td><label for="interface[autoplay]">AutoPlay:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[autoplay]" value="false" />
							<input type="checkbox" name="interface[autoplay]" id="interface[autoplay]" value="true" <?php if( isset($fv_fp->conf['interface']['autoplay']) && $fv_fp->conf['interface']['autoplay'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[loop]">Loop:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[loop]" value="false" />
							<input type="checkbox" name="interface[loop]" id="interface[loop]" value="true" <?php if( isset($fv_fp->conf['interface']['loop']) && $fv_fp->conf['interface']['loop'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
					<tr>          
						<td><label for="interface[splashend]">Splash end:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[splashend]" value="false" />
							<input type="checkbox" name="interface[splashend]" id="interface[splashend]" value="true" <?php if( isset($fv_fp->conf['interface']['splashend']) && $fv_fp->conf['interface']['splashend'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>     
					<tr>          
						<td><label for="interface[embed]">Embed:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[embed]" value="false" />
							<input type="checkbox" name="interface[embed]" id="interface[embed]" value="true" <?php if( isset($fv_fp->conf['interface']['embed']) && $fv_fp->conf['interface']['embed'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>    
					<tr>          
						<td><label for="interface[subtitles]">Subtitles:</label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[subtitles]" value="false" />
							<input type="checkbox" name="interface[subtitles]" id="interface[subtitles]" value="true" <?php if( isset($fv_fp->conf['interface']['subtitles']) && $fv_fp->conf['interface']['subtitles'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>  
					<tr>          
						<td><label for="interface[ads]">Ads: <span style="color: #e00; font-weight: bold">NEW</span></label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[ads]" value="false" />
							<input type="checkbox" name="interface[ads]" id="interface[ads]" value="true" <?php if( isset($fv_fp->conf['interface']['ads']) && $fv_fp->conf['interface']['ads'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   	
					<tr>          
						<td><label for="interface[mobile]">Mobile video: <span style="color: #e00; font-weight: bold">NEW</span></label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[mobile]" value="false" />
							<input type="checkbox" name="interface[mobile]" id="interface[mobile]" value="true" <?php if( isset($fv_fp->conf['interface']['mobile']) && $fv_fp->conf['interface']['mobile'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr>   		
					<tr>          
						<td><label for="interface[align]">Align: <span style="color: #e00; font-weight: bold">NEW</span></label></td>
						<td style="text-align:right;">
              <input type="hidden" name="interface[align]" value="false" />
							<input type="checkbox" name="interface[align]" id="interface[align]" value="true" <?php if( isset($fv_fp->conf['interface']['align']) && $fv_fp->conf['interface']['align'] == 'true' ) echo 'checked="checked"'; ?> />
						</td>
					</tr> 					
					<tr>    		
						<td colspan="4">
							<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" style="margin-top: 2ex;"/>
						</td>
					</tr>                                    
				</table>
<?php
}


function fv_flowplayer_admin_skin() {
	global $fv_fp;
?>
					<table class="form-table2" style="width: 100%">	
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
								<input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes" style="margin-top: 2ex;"/>
							</td>
						</tr>					
					</table>						
<?php
}


function fv_flowplayer_admin_usage() {
?>
				<table class="form-table">
					<tr>
						<td colspan="4" style="text-align: justify;">  
							<div style="float: left; width: 49%">
								<div class="icon32" id="icon-users"><br></div>							
								<p>Illustrated user guides:</p>
								<div style="clear: both"></div>
								<ul>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/user-guide">Inserting videos</a>
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/user-guide#license">License key and custom logo</a>
									</li>					
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/adding-ads">Using ads</a></li>				
									<li><a target="_blank" href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/rtmp-streams">RTMP streams</a></li>				
								</ul>
							</div>
							<div style="float: left; width: 49%">
								<div class="icon32" id="icon-tools"><br></div>							
								<p>Troubleshooting:</p>
								<div style="clear: both"></div>
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
							<div style="clear: both"></div>
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
	<input type="hidden" name="<?php echo $name; ?>" value="false" />
  <input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="true" <?php if( isset($fv_fp->conf[$name]) && $fv_fp->conf[$name] == 'true' ) echo 'checked="checked"'; ?> />
<?php
}


add_meta_box( 'fv_flowplayer_description', 'Description', 'fv_flowplayer_admin_description', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_skin', 'Player Skin', 'fv_flowplayer_admin_skin', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_interface_options', 'Post Interface Options', 'fv_flowplayer_admin_interface_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_default_options', 'Sitewide Flowplayer Defaults', 'fv_flowplayer_admin_default_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_amazon_options', 'Amazon S3 Protected Content', 'fv_flowplayer_admin_amazon_options', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_ads', 'Ads', 'fv_flowplayer_admin_ads', 'fv_flowplayer_settings', 'normal' );
add_meta_box( 'fv_flowplayer_usage', 'Usage', 'fv_flowplayer_admin_usage', 'fv_flowplayer_settings', 'normal' );

?>

<style>
div.green { background-color: #e0ffe0; border-color: #88AA88; } 
.amazon-s3-first .fv_fp_amazon_remove { display: none; }
.form-table2 td p { line-height: 20px; }
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
						<a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/download" class="red-button"><strong>Summer Special!</strong><br />All Licenses 20% Off</a></p>
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
		<div id="dashboard-widgets" class="metabox-holder columns-1">
			<div id='postbox-container-1' class='postbox-container'>    
				<?php
				do_meta_boxes('fv_flowplayer_settings', 'normal', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order-nonce', 'meta-box-order-nonce', false );
				?>
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
	});
	//]]>
</script>
