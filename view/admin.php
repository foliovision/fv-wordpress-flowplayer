<?php
/**
 * Displays administrator backend.
 */
?>

<?php 
	if(isset($_POST['submit'])) {
		/**
		 *  Write the configuration into file, if the form was submitted.
		 */
		$fp->_set_conf();
    $fp = new flowplayer();
    /**
		 *  Refresh the page.
		 */
		?>
		<?php
	}
?>

<div class="wrap">
  <form id="wpfp_options" method="post" action="">
              
    <div style="position: absolute; top: 10px; right: 10px;">
      <a href="https://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer" target="_blank" title="Documentation"><img alt="visit foliovision" src="http://foliovision.com/shared/fv-logo.png" /></a>
    </div>
    
    <div>
      <div id="icon-options-general" class="icon32"></div>
      <h2>FV Wordpress Flowplayer</h2>
    </div>
       			
    <?php //echo flowplayer_check_errors($fp); ?>
    <h3>Default Flowplayer Options:</h3>
    <table>
      <tr>
    		<td style="width: 330px;"><label for="autoplay">AutoPlay:</label></td>
    		<td style="text-align:right;">
    		  <select id="autoplay" name="autoplay"><?php echo flowplayer_bool_select($fp->conf['autoplay']); ?></select> 	
    		</td>
    		<td colspan="2" rowspan="10"  style="padding-left: 30px; vertical-align: top;">
          <div class="flowplayer is-splash" 
          data-swf="<?php echo RELATIVE_PATH ?>/flowplayer.html5/flowplayer.swf"'
          data-ratio="0.417" 
          style="width:<?php echo $fp->conf['width']; ?>px; max-height:<?php echo $fp->conf['height']; ?>px;"
          <?php if ($fp->conf['allowfullscreen'] == 'false') echo 'data-fullscreen="false"'; ?>
          <?php if (isset($fp->conf['key']) && $fp->conf['key'] != 'false' && strlen($fp->conf['key']) > 0) echo 'data-key="' . $fp->conf['key'] . '"'; ?>
          >
            <video poster="http://foliovision.com/videos/example.jpg">
              <source src="http://foliovision.com/videos/example.mp4" type="video/mp4" />
            </video>
          </div>          
        </td>
    	</tr>
    	<tr>
    		<td><label for="autobuffer">Auto Buffering:</label></td>
    		<td style="text-align:right">
          <select id="autobuffer" name="autobuffer"><?php echo flowplayer_bool_select($fp->conf['autobuffer']); ?></select>
        </td>
    	</tr>
      <tr>
					<td><label for="popupbox">Popup Box:</label></td>
					<td>
            <select id="popupbox" name="popupbox"><?php echo flowplayer_bool_select($fp->conf['popupbox']); ?></select>
          </td>
				</tr>
    	<tr>
    		<td><label for="allowfullscreen">Enable Full-screen Mode:</label></td>
    		<td style="text-align:right">
          <select id="allowfullscreen" name="allowfullscreen"><?php echo flowplayer_bool_select($fp->conf['allowfullscreen']); ?></select>
        </td>
    	</tr>
    	<tr>
    		<td><label for="scaling">Fit scaling (<abbr title="If set to true, the original aspect ratio of the video will be used to display the video in fullscreen mode as well as when embedded in the page.">?</abbr>):</label></td>
    		<td style="text-align:right">
          <select id="scaling" name="scaling"><?php echo flowplayer_bool_select($fp->conf['scaling']); ?></select>
        </td>
    	</tr>
    	<tr>
    		<td><label for="allowuploads">Allow User Uploads:</label></td>
    		<td style="text-align:right">
    		 	<select id="allowuploads" name="allowuploads"><?php echo flowplayer_bool_select($fp->conf['allowuploads']); ?></select>
    		</td>
    	</tr>
    	<tr>
    		<td><label for="postthumbnail">Enable Post Thumbnail:</label></td>
    		<td style="text-align:right">
    		 	<select id="postthumbnail" name="postthumbnail"><?php echo flowplayer_bool_select($fp->conf['postthumbnail']); ?></select>
    		</td>
    	</tr>    	
    	<tr>
    		<td><label for="commas">Convert old shortcodes with commas (<abbr title="Older versions of this plugin used commas to sepparate shortcode parameters. This option will make sure it works with current version. Turn this off if you have some problems with display or other plugins which use shortcodes.">?</abbr>):</label></td>
    		<td style="text-align:right">
    		 	<select id="commas" name="commas"><?php echo flowplayer_bool_select($fp->conf['commas']); ?></select>
    		</td>
    	</tr>
    	<tr>
    		<td colspan="2">Default video size [px]: 
          <span style="float:right">
    		 	  <label for="width">W:</label>&nbsp;<input type="text" size="4" name="width" id="width" value="<?php echo trim($fp->conf['width']); ?>" />  
    		 	  <label for="height">H:</label>&nbsp;<input type="text" size="4" name="height" id="height" value="<?php echo trim($fp->conf['height']); ?>" />
          </span>	
    		</td>
    	</tr>
    </table>
    <table style="width: 400px;">
      <tr>
    		<td><label for="googleanalytics">Google Analytics ID:</label></td>
        <td><input type="text" size="40" name="googleanalytics" id="googleanalytics" value="<?php echo trim($fp->conf['googleanalytics']); ?>" /></td>
    	</tr>
    	<tr>
    		<td><label for="key">Commercial License Key:</label></td>
        <td><input type="text" size="40" name="key" id="key" value="<?php echo trim($fp->conf['key']); ?>" /></td>
    	</tr>
      <tr>
    		<td><label for="logo">Logo:</label></td>
        <td><input type="text" size="40" name="logo" id="logo" value="<?php echo trim($fp->conf['logo']); ?>" /></td>
    	</tr>
      <tr>    		    		
        <td colspan="2" style="text-align: right">Or <a title="Add FV WP Flowplayer Logo" href="media-upload.php?type=image&TB_iframe=true&width=500&height=300 ?>" class="thickbox" >open media library</a> to upload logo.</td>
    	</tr>      
      <tr>
    		<td><label for="rtmp">Amazon CloudFront domain:</label></td>
        <td><input type="text" size="40" name="rtmp" id="rtmp" value="<?php echo trim($fp->conf['rtmp']); ?>" /></td>
    	</tr>	
    </table>
    <table style="width: 400px;">
    	<tr>
        <td colspan="4"><strong>Colors</strong></td>
      </tr>
    	<?php include dirname( __FILE__ ) . '/../view/colours.php'; ?>
    	<tr>    		
    		<td colspan="4">
    			<input type="submit" name="submit" class="button-primary" value="Apply Changes" style="margin-top: 2ex;"/>
    		</td>
    	</tr>
  	</table>
    <table style="width: 400px;">
    	<tr>    		
    		<td>
    			<input type="button" name="convert" class="button-primary" value="Run Conversion Script" style="margin-top: 2ex;" onclick="flowplayer_conversion_script()"/>
    		</td>
    	</tr>
  	</table>
    <script type="text/javascript" >
      function flowplayer_conversion_script() {
      
      	var data = {
      		action: 'flowplayer_conversion_script',
      		run: true
      	};
      
      	jQuery.post(ajaxurl, data, function(response) {
      		console.log(response);
      	});
      }
    </script>
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
      		To embed video "example.mp4", simply include the following code inside any post or page: 
      		<code>[fvplayer src=example.mp4]</code>
      		</p>
      		<p>
      		<code>src</code> is the only compulsory parameter, specifying the video file. Its value can be either a full URL of the file, 
      		or just a filename, if it is located in the /videos/ directory in the root of the web.
      		</p>
      		<p>When user uploads are allowed, uploading or selecting video from WP Media Library is available. To insert selected video, simply use the 'Insert into Post' button.</p>
      		<h4>Optional parameters:</h4>
      		<ul style="text-align: left;">
      			<li><code><strong>width</strong></code> and <code><strong>height</strong></code> specify the dimensions of played video in pixels. If they are not set, the default size is 320x240.<br />
      			<i>Example</i>: <code>[fvplayer src='example.mp4' width=640 height=480]</code></li>
      			<li><code><strong>splash</strong></code> parameter can be used to display a custom splash image before the video is started. Just like in case of <code>src</code> 
      			parameter, its value can be either complete URL, or filename of an image located in /videos/ folder.<br />
      			<i>Example</i>: <code>[fvplayer src='example.mp4' splash=image.jpg]</code></li>
      			<li><code><strong>autoplay</strong></code> parameter specify wheter the video should start to play automaticaly after the page is loaded. This parameter overrides the default autoplay setting above. Its value can be either true or false.<br />
      			<i>Example</i>: <code>[fvplayer src='example.mp4' autoplay=true]</code></li>
      			<li><code><strong>popup</strong></code> parameter can be used to display any HTML code after the video finishes (ideal for advertisment or links to similar videos). 
      			Content you want to display must be between simgle quotes (<code>''</code>).<br />
      			<i>Example</i>: <code>[fvplayer src='example.mp4' popup='&lt;p&gt;some HTML content&lt;/p&gt;']</code></li>      			
      			<li><code><strong>redirect</strong></code> parameter can be used to redirect to another page (in a new tab) after the video stops playing.<br />
      			<i>Example</i>: <code>[fvplayer src='example.mp4' redirect='http://www.site.com']</code></li>
      		</ul>
    		</td>
    		<td></td>
    	</tr>
    </table>
  </form>
</div>