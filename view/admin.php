<?php
/**
 * Displays administrator backend.
 */
?>
<div class="wrap">
			<form id="wpfp_options" method="post" action="">
			<div id="icon-options-general" class="icon32"></div>
			<h2>FV Wordpress Flowplayer</h2>
			<?php //echo flowplayer_check_errors($fp); ?>
			<h3>Default Flowplayer Options:</h3>
			<table style="width: 800px;">
			   <tr><th style="width:35%"></th><th style="width:15%"></th><th colspan="2" style="width:50%"></th></tr>
				<tr>
					<td>AutoPlay: </td>
					<td style="text-align:right;">
					 	<select name="autoplay">
						<?php echo flowplayer_bool_select($fp->conf['autoplay']); ?>
					 	</select>
					 </td>
					 <td colspan="2" rowspan="10"  style="padding-left: 30px; vertical-align: top;">
					 <script type="text/javascript" src="<?php echo RELATIVE_PATH; ?>/js/jscolor/jscolor.js"></script>
                <script type="text/javascript" src="<?php echo RELATIVE_PATH; ?>/flowplayer/flowplayer.min.js"></script>
                <?php
                  echo "<!--[if lt IE 7.]>
      <script defer type=\"text/javascript\" src=\"" . RELATIVE_PATH . "/js/pngfix.js\"></script>
      <![endif]-->
      <script type=\"text/javascript\">	
      	/*<![CDATA[*/
      		function fp_replay(hash) {
      			var fp = document.getElementById('wpfp_'+hash);
      			var popup = document.getElementById('wpfp_'+hash+'_popup');
      			fp.removeChild(popup);
      			flowplayer('wpfp_'+hash).play();
      		}
      		function fp_share(hash) {
      			var cp = document.getElementById('wpfp_'+hash+'_custom_popup');
      			cp.innerHTML = '<div style=\"margin-top: 10px; text-align: center;\"><label for=\"permalink\" style=\"color: white;\">Permalink to this page:</label><input onclick=\"this.select();\" id=\"permalink\" name=\"permalink\" type=\"text\" value=\"http://".$_SERVER['SERVER_NAME'].urlencode($_SERVER['REQUEST_URI'])."\" /></div>';
      		}
      	/*]]>*/
      </script>";
                ?>

                <a id="player" class="flowplayer_div" style="display:block;width:<?php echo $fp->conf['width']; ?>px;height:<?php echo $fp->conf['height']; ?>px;"></a>
                </td>
				</tr>
				<tr>
					<td>Auto Buffering:</td>
					<td style="text-align:right"><select name="autobuffer">
					<?php echo flowplayer_bool_select($fp->conf['autobuffer']); ?>
					</select></td>
				</tr>
				<tr>
					<td>Popup Box:</td>
					<td style="text-align:right"><select name="popupbox">
					<?php echo flowplayer_bool_select($fp->conf['popupbox']); ?>
					</select></td>
				</tr>
				<tr>
					<td>Highlight Link in Popup:</td>
					<td style="text-align:right"><select name="linkhighlight">
					<?php echo flowplayer_bool_select($fp->conf['linkhighlight']); ?>
					</select></td>
				</tr>
				<tr>
					<td>Enable Full-screen Mode:</td>
					<td style="text-align:right"><select name="allowfullscreen">
					<?php echo flowplayer_bool_select($fp->conf['allowfullscreen']); ?>
					</select></td>
				</tr>
				<tr>
					<td>Fit scaling (<abbr title="If set to true, the original aspect ratio of the video will be used to display the video in fullscreen mode as well as when embedded in the page.">?</abbr>):</td>
					<td style="text-align:right"><select name="scaling">
					<?php echo flowplayer_bool_select($fp->conf['scaling']); ?>
					</select></td>
				</tr>
				<tr>
					<td>Allow User Uploads: </td>
					<td style="text-align:right">
					 	<select name="allowuploads">
						<?php echo flowplayer_bool_select($fp->conf['allowuploads']); ?>
					 	</select>
					 </td>
				</tr>
				<tr>
					<td>Enable Post Thumbnail: </td>
					<td style="text-align:right">
					 	<select name="postthumbnail">
						<?php echo flowplayer_bool_select($fp->conf['postthumbnail']); ?>
					 	</select>
					 </td>
				</tr>
				<tr>
					<td>Insert scripts only when needed (<abbr title="By default, all javascripts are being inserted into teh page all teh time. By enabling this option you will eliminate instertion of these script if video is not present on the page. If insert video outside the loop, you may need to keep this feature disabled.">?</abbr>): </td>
					<td style="text-align:right">
					 	<select name="optimizejs">
						<?php echo flowplayer_bool_select($fp->conf['optimizejs']); ?>
					 	</select>
					 </td>
				</tr>
				<tr>
					<td>Convert old shortcodes with commas (<abbr title="Older versions of this plugin used commas to sepparate shortcode parameters. This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.">?</abbr>): </td>
					<td style="text-align:right">
					 	<select name="commas">
						<?php echo flowplayer_bool_select($fp->conf['commas']); ?>
					 	</select>
					 </td>
				</tr>
				<tr>
					<td colspan="2">Default video size [px]: 
				<span style="float:right">
					 	W:&nbsp;<input type="text" size="4" name="width" id="width" value="<?php echo trim($fp->conf['width']); ?>" />  
					 	H:&nbsp;<input type="text" size="4" name="height" id="height" value="<?php echo trim($fp->conf['height']); ?>" /> </span>	
					 </td>
				</tr>
				<tr>
					<td colspan="2">Commercial License Key: <input type="text" size="40" name="key" id="key" value="<?php echo trim($fp->conf['key']); ?>" style="float:right" /></td>
					<td>
							
					</td>
				</tr>	
				</table>
				<table  style="width: 400px;">
				<tr><td><strong>Colors</strong></td><td></td></tr>
					<?php include dirname( __FILE__ ) . '/../view/colours.php'; ?>
				<tr>
					<td></td><td></td><td></td>
					<td>
						<input type="submit" name="submit" class="button-primary" value="Apply Changes" style="margin-top: 2ex;"/>
					</td>
				</tr>
				</table>
				<table style="width: 800px;">
				<tr>
					<td colspan="4" style="text-align: justify;">
					<h3>Description:</h3>
					<ul>
						<li>FV Wordpress Flowplayer is a completely non-commercial solution for embedding video on Wordpress websites.</li>
						<li>Supported video formats are <strong>FLV</strong>, <strong>H.264</strong>, and <strong>MP4</strong>. Multiple videos can be displayed in one post or page.</li>
						<li>Default options for all the embedded videos can be set in the menu above.</li>
					</ul>
					<h3>Usage:</h3>
					<p>
					To embed video "example.flv", simply include the following code inside any post or page: 
					<code>[flowplayer src=example.flv]</code>
					</p>
					<p>
					<code>src</code> is the only compulsory parameter, specifying the video file. Its value can be either a full URL of the file, 
					or just a filename, if it is located in the /videos/ directory in the root of the web.
					</p>
					<p>When user uploads are allowed, uploading or selecting video from WP Media Library is available. To insert selected video, simply use the 'Insert into Post' button.</p>
					<h4>Optional parameters:</h4>
					<ul style="text-align: left;">
						<li><code><strong>width</strong></code> and <code><strong>height</strong></code> specify the dimensions of played video in pixels. If they are not set, the default size is 320x240.<br />
						<i>Example</i>: <code>[flowplayer src='example.flv' width=640 height=480]</code></li>
						<li><code><strong>splash</strong></code> parameter can be used to display a custom splash image before the video is started. Just like in case of <code>src</code> 
						parameter, its value can be either complete URL, or filename of an image located in /videos/ folder.<br />
						<i>Example</i>: <code>[flowplayer src='example.flv' splash=image.jpg]</code></li>
						<li><code><strong>autoplay</strong></code> parameter specify wheter the video should start to play automaticaly after the page is loaded. This parameter overrides the default autoplay setting above. Its value can be either true or false.<br />
						<i>Example</i>: <code>[flowplayer src='example.flv' autoplay=true]</code></li>
						<li><code><strong>popup</strong></code> parameter can be used to display any HTML code after the video finishes (ideal for advertisment or links to similar videos). 
						Content you want to display must be between simgle quotes (<code>''</code>).<br />
						<i>Example</i>: <code>[flowplayer src='example.flv' popup='&lt;p&gt;some HTML content&lt;/p&gt;']</code></li>
						<li><code><strong>controlbar</strong></code> parameter can be used to show or hide the control bar. Value <code>show</code> will keep the controlbar visible for the whole duration of the video, and value <code>hide</code> will completely hide the control bar. If this parameter is not set, the default autohide is applied.<br />
						<i>Example</i>: <code>[flowplayer src='example.flv' controlbar='show']</code></li>
						<li><code><strong>redirect</strong></code> parameter can be used to redirect to another page (in a new tab) after the video stops playing.<br />
						<i>Example</i>: <code>[flowplayer src='example.flv' redirect='http://www.site.com']</code></li>
						<li><code><strong>splashend</strong></code> set to show if you want to show the splash image also at the end of the video. The image has to have exactly the same dimensions as the video, there is no stretching applied.<br />
						<i>Example</i>: <code>[flowplayer src='example.flv' splash=image.jpg splashend=show]</code></li>
					</ul>
					</td>
					<td></td>
				</tr>
			</table>
			</form>

<script defer="defer" language="Javascript" type="text/javascript">
		//load player
		$f("player", "<?php echo PLAYER; ?>", {
				<?php echo (isset($fp->conf['key'])&&strlen($fp->conf['key'])>0?'key:\''.trim($fp->conf['key']).'\',':''); ?>
				plugins: {
				    <?php echo (((empty($fp->conf['showcontrols']))||($fp->conf['showcontrols']=='true'))? 
                  'controls: { buttonOverColor: \''.trim($fp->conf['buttonOverColor']).'\', sliderColor: \''. trim($fp->conf['sliderColor']).'\', bufferColor: \''. trim($fp->conf['bufferColor']).'\', sliderGradient: \'none\', progressGradient: \'medium\', durationColor: \''. trim($fp->conf['durationColor']).'\', progressColor: \''. trim($fp->conf['progressColor']).'\', backgroundColor: \''. trim($fp->conf['backgroundColor']).'\', timeColor: \''. trim($fp->conf['timeColor']).'\', buttonColor: \''. trim($fp->conf['buttonColor']).'\', backgroundGradient: \'none\', bufferGradient: \'none\', opacity:0.9, fullscreen: '.trim($fp->conf['allowfullscreen']).',autoHide: \'always\',hideDelay: 500} ':'controls:null'); ?> 
				},
				clip: {
					url:'http://foliovision.com/videos/example.flv',
					autoPlay: '<?php if (isset($fp->conf["autoplay"])) { echo trim($fp->conf["autoplay"]); } else { echo(false); } ?>',
					scaling: '<?php if (isset($fp->conf["scaling"])) { echo trim($fp->conf["scaling"]); } else { echo(false); } ?>',
	       	autoBuffering: '<?php if (isset($fp->conf["autobuffer"])) { echo trim($fp->conf["autobuffer"]); } else { echo "false"; } ?>'
				},
            <?php 	
            if($fp->conf['logoenable'] == 'true'){
            	echo 'logo: {url: \'http://'.$fp->conf['logo'].'\', fullscreenOnly: '.trim($fp->conf['fullscreenonly']).', displayTime: 0, linkUrl: \'http://'.$fp->conf['logolink'].'\'},';
            }
            ?>
				canvas: {
					backgroundColor:'<?php echo trim($fp->conf["canvas"]); ?>'
				},
				onLoad: function() {
					jQuery(":input[name=tgt]").removeAttr("disabled");		
				},
				onUnload: function() {
					jQuery(":input[name=tgt]").attr("disabled", true);		
				}
			});
</script>
</div>
<?php 
	if(isset($_POST['submit'])) {
//					url:'<?php echo RELATIVE_PATH; ? >/flowplayer/example.flv',
		/**
		 *  Write the configuration into file, if the form was submitted.
		 */
		$fp->_set_conf();
    /**
		 *  Refresh the page.
		 */
		?>
		<script type="text/JavaScript">
		<!--
			window.location = window.location;
		//   -->
		</script>
		<?php
	}
?>
<?php
if (get_option('wp_mobile_video_active') == 'enabled')
  if (function_exists('wpvideo_check_domain')){
    wpvideo_check_domain();
  }
?>