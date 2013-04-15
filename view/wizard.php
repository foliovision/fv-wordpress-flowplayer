<?php                    
  if (isset($_COOKIE["selected_video"]))
    $uploaded_video = $_COOKIE["selected_video"];
  if (isset($_COOKIE["selected_video1"]))
    $uploaded_video1 = $_COOKIE["selected_video1"];
  if (isset($_COOKIE["selected_video2"]))
    $uploaded_video2 = $_COOKIE["selected_video2"];  
  if (isset($_COOKIE["selected_image"]))
    $uploaded_image = $_COOKIE["selected_image"];  
  
  $post_id = intval($_REQUEST['post_id']);
  //load configuration file:   
  $conf = get_option( 'fvwpflowplayer' );
  $allow_uploads = false;

	if (isset($conf["allowuploads"]))
	  $allow_uploads = $conf["allowuploads"];
	if (isset($conf["postthumbnail"]))
	  $post_thumbnail = $conf["postthumbnail"];
	  
	$video_types = array('flv','mov','avi','mpeg','mpg','asf','qt','wmv','mp4','m4v','mp3','webm','ogv');
  $splash_types = array('jpg','jpeg','gif','png', 'bmp','jpe');
  
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
    require_once(realpath(dirname(__FILE__).'/getid3/getid3.php'));
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

?>
<script type="text/javascript">
function fillVideoInputs(){
   var vid_list = document.getElementById("files_video");
   var item = vid_list.options[vid_list.selectedIndex].title;
   document.getElementById("src").value = item;
   document.getElementById("hidden_video").value = item;
}
function fillSplashInputs(){
   var spl_list = document.getElementById("files_splash");
   var item = spl_list.options[spl_list.selectedIndex].title;
   document.getElementById("splash").value = item;
   document.getElementById("hidden_splash").value = item;
   document.cookie = "selected_image="+item+";";
}
</script>
<form>
	<table class="slidetoggle describe">
		<tbody>
			<tr>
				<th scope="row" class="label" style="width: 10%"><label for="src" class="alignright">Video</label></th>
				<td colspan="2" class="field"><input type="text" class="text" id="src" name="src" style="width: 100%" value="<?php echo $uploaded_video ?>"/></td>
			</tr>
			<?php 
      if ($allow_uploads=="true") {
      ?> 
			<tr>
  			<th></th>
  			<td colspan="2" style="width: 100%" >         
          Or <a href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=video&amp;TB_iframe=true&amp;width=640&amp;height=723fvplayer">open media library</a> to upload new video.
  			</td>
			</tr>
			<?php }; //allow uplads video ?>

			<?php if (!empty($uploaded_video)) { ?>
        <tr>
          <th></th>
          <th scope="row" class="label"><span class="alignleft">File info</span></th>
          <td>
            <?php if (!empty($file_width)) { ?>
            Video Duration: <?php echo $file_time ?><br />
            File size: <?php echo $file_size ?>MB
            <?php } else echo $file_error;  ?>
          </td>
        </tr>
      <?php }; //video has been selected ?> 
			<tr><th></th>
				<th scope="row" class="label" ><label for="width" class="alignleft">Width <small>(px)</small></label><br class='clear' /></th>
				<td class="field"><input type="text" id="width" name="width" style="width: 100%"  value="<?php echo $file_width ?>"/></td>
			</tr>
			<tr><th></th>
				<th scope="row" class="label" style="width: 10%"><label for="height" class="alignleft">Height <small>(px)</small></label></th>
				<td class="field"><input type="text" id="height" name="height" style="width: 100%" value="<?php echo $file_height ?>"/></td>
			</tr>
      
      <tr <?php if (!empty($uploaded_video) && !empty($uploaded_video1)) echo 'style="display: table-row;"'; else echo 'style="display: none;"'; ?> id="src_1_wrapper">
				<th scope="row" class="label" style="width: 10%"><label for="src" class="alignright">Video</label></th>
				<td colspan="2" class="field"><input type="text" class="text" id="src_1" name="src_1" style="width: 100%" value="<?php echo $uploaded_video1 ?>"/></td>
			</tr>
      <?php 
      if ($allow_uploads=="true") {
      ?> 
			<tr <?php if (!empty($uploaded_video) && !empty($uploaded_video1)) echo 'style="display: table-row;"'; else echo 'style="display: none;"'; ?> id="src_1_uploader">
  			<th></th>
  			<td colspan="2" style="width: 100%" >         
          Or <a href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=video&amp;TB_iframe=true&amp;width=640&amp;height=723fvplayer1">open media library</a> to upload new video.
  			</td>
			</tr>
			<?php }; //allow uplads video ?>
      
      <tr <?php if (!empty($uploaded_video1) && !empty($uploaded_video2)) echo 'style="display: table-row;"'; else echo 'style="display: none;"'; ?> id="src_2_wrapper">
				<th scope="row" class="label" style="width: 10%"><label for="src" class="alignright">Video</label></th>
				<td colspan="2" class="field"><input type="text" class="text" id="src_2" name="src_2" style="width: 100%" value="<?php echo $uploaded_video2 ?>"/></td>
			</tr>
      <?php 
      if ($allow_uploads=="true") {
      ?> 
			<tr <?php if (!empty($uploaded_video1) && !empty($uploaded_video2)) echo 'style="display: table-row;"'; else echo 'style="display: none;"'; ?> id="src_2_uploader">
  			<th></th>
  			<td colspan="2" style="width: 100%" >         
          Or <a href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=video&amp;TB_iframe=true&amp;width=640&amp;height=723fvplayer2">open media library</a> to upload new video.
  			</td>
			</tr>
			<?php }; //allow uplads video ?>
      
      <?php if (empty($uploaded_video2)) { ?>
      <tr id="add_format_wrapper">
  			<th scope="row" class="label" style="width: 10%"></th>
				<td colspan="2" class="field"><a href="#" onclick="add_format()" style="outline: 0"><span id="add-format" style="background: url(<?php echo plugins_url( 'images/admin-bar-sprite.png' , dirname(__FILE__) ) ?>) no-repeat -3px -205px; display: block; width: 11px; height: 11px; float: left; margin-top: 2px; padding-right: 4px;"></span>Add another format</a></td>
			</tr>      
      <?php }; ?>
			
      <tr>
				<th scope="row" class="label"><label for="splash" class="alignright">Splash Image</label></th>
				<td class="field" colspan="2"><input type="text" id="splash" name="splash" style="width: 100%"  value="<?php echo $uploaded_image ?>"/></td>
			</tr>
			<?php if ($allow_uploads=='true') { ?>
        <tr>
          <th></th>
          <td colspan="2" class="field" style="width: 100%" >
            Or <a href="media-upload.php?type=image&amp;post_id=<?php echo $post_id; ?>&amp;TB_iframe=true&amp;width=640&amp;height=723fvplayer">open media library</a> to upload new splash image.
          </td>
			</tr>
			<?php }; //allow uploads splash image ?>
			<?php if (!empty($uploaded_image))
        if (($post_thumbnail=='true') && current_theme_supports( 'post-thumbnails') && isset($selected_attachment['id'])) 
          update_post_meta( $post_id, '_thumbnail_id', $selected_attachment['id'] ); ?>			
    </tbody>
  </table>
  <table>
    <tbody>      
      <tr>
        <th scope="row" colspan="2" style="text-align: left; padding: 10px 0;">Additional features</th>
      </tr>
      <tr>
				<th valign="top" scope="row" class="label" style="width: 12%"><label for="popup" class="alignright">HTML Popup</label></th>
				<td><textarea type="text" id="popup" name="popup" style="width: 100%"></textarea></td>
			</tr>
      <tr>
				<th scope="row" class="label"><label for="redirect" class="alignright">Redirect to</label></th>
				<td class="field"><input type="text" id="redirect" name="redirect" style="width: 100%" /></td>
			</tr>
      <tr>
				<th scope="row" class="label"><label for="autoplay" class="alignright">Autoplay</label></th>
				<td class="field">
          <select id="autoplay" name="autoplay">
            <option>Default</option>
            <option>On</option>
            <option>Off</option>
          </select>
        </td>
			</tr>
      <tr>
				<th scope="row" class="label"><label for="loop" class="alignright">Loop</label></th>
				<td class="field"><input type="checkbox" id="loop" name="loop" /></td>
			</tr>   
      <tr>
        <th scope="row" class="label">
          <label for="splashend">Splash end</label>
        </th>
        <td>
          <input type="checkbox" id="splashend" name="splashend" /> (show splash image at the end)</th>
        </td> 
      </tr>         
			<tr>
				<th colspan="2" scope="row" class="label" style="padding-top: 20px;">					
          <input type="button" value="Insert" name="insert" id="insert-button" class="button-primary alignleft" onclick="clickOK();" />
				</th>
			</tr>
		</tbody>
	</table>
</form>

<script type="text/javascript">
  var re = /\[[^\]]*?<span style="display: none;">FCKFVWPFlowplayerPlaceholder<\/span>[^\]]*?\]/mi;
	var re2 = /<span style="display: none;">FCKFVWPFlowplayerPlaceholder<\/span>/gi;
	
  var hTinyMCE;
  var oEditor;
  if (window.parent.tinyMCE) {
    hTinyMCE = window.parent.tinyMCE.getInstanceById('content');
  }
  else {
    oEditor = window.parent.FCKeditorAPI.GetInstance('content');    
  }
  
	if( hTinyMCE == undefined || window.parent.tinyMCE.activeEditor.isHidden() ) {
		//Foliopres WYSIWYG      
    var content_original = oEditor.GetHTML();
    if (content_original.match( re2 ) == null) {
      oEditor.InsertHtml('<span style="display: none;">FCKFVWPFlowplayerPlaceholder</span>');
      content_original = oEditor.GetHTML();
    }           
	}
	else {
		//Wordpress WYSIWYG
    var content_original = hTinyMCE.getContent();
    if (content_original.match( re2 ) == null) {      
      hTinyMCE.selection.setContent('<span style="display: none;">FCKFVWPFlowplayerPlaceholder</span>');
      content_original = hTinyMCE.getContent();
    }		
	}
  
  var content = content_original.replace(/\n/g, '\uffff');    
  
  var shortcode = content.match( re );    
  
  if( shortcode != null ) {
    shortcode = shortcode.join('');
    shortcode = shortcode.replace('[', '');
    shortcode = shortcode.replace(']', '');
  	shortcode = shortcode.replace( re2, '' );
  	
  	shortcode = shortcode.replace( /\\'/g,'&#039;' );
  	
  	var srcurl = shortcode.match( /src='([^']*)'/ );
  	if( srcurl == null )
  		srcurl = shortcode.match( /src=([^,\]\s]*)/ );			
  	var iheight = shortcode.match( /height=(\d*)/ );			
  	var iwidth = shortcode.match( /width=(\d*)/ );
  	var sautoplay = shortcode.match( /autoplay=([^\s]+)/ );
  	var ssplash = shortcode.match( /splash='([^']*)'/ );
    var sredirect = shortcode.match( /redirect='([^']*)'/ );
  	if( ssplash == null )
  		ssplash = shortcode.match( /splash=([^,\]\s]*)/ );			
  	var spopup = shortcode.match( /popup='([^']*)'/ );
    var sloop = shortcode.match( /loop=([^\s]+)/ );
    var ssplashend = shortcode.match( /splashend=([^\s]+)/ );
    
  	if( srcurl != null && srcurl[1] != null )
  		document.getElementById("src").value = srcurl[1];
  	if( iheight != null && iheight[1] != null )
  		document.getElementById("height").value = iheight[1];
  	if( iwidth != null && iwidth[1] != null )
  		document.getElementById("width").value = iwidth[1];
  	if( sautoplay != null && sautoplay[1] != null ) {
  		if (sautoplay[1] == 'true') 
        document.getElementById("autoplay").selectedIndex = 1;
      if (sautoplay[1] == 'false') 
        document.getElementById("autoplay").selectedIndex = 2;
    }
  	if( ssplash != null && ssplash[1] != null )
  		document.getElementById("splash").value = ssplash[1];
  	if( spopup != null && spopup[1] != null ) {
  		spopup = spopup[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
  		spopup = spopup.replace(/&amp;/g,'&');
  		document.getElementById("popup").value = spopup;
  	}
    if( sredirect != null && sredirect[1] != null )
  		document.getElementById("redirect").value = sredirect[1];
    if( sloop != null && sloop[1] != null && sloop[1] == 'true' )
  		document.getElementById("loop").checked = 1;
    if( ssplashend != null && ssplashend[1] != null && ssplashend[1] == 'show' )
  		document.getElementById("splashend").checked = 1;  
  	
  	document.getElementById("insert-button").value = "Update";	  	
	}

function clickOK() {
			
	var shortcode = '';
  var shorttag = 'fvplayer';
	
	if(document.getElementById("src").value == '') {
		alert('Please enter the file name of your video file.');
		return false;
	}
	else
		shortcode = '[' + shorttag + ' src=\'' + document.getElementById("src").value + '\'';
    
  if ( document.getElementById("src_1").value != '' ) {
    shortcode += ' src1=\'' + document.getElementById("src_1").value + '\''; 
  }
  if ( document.getElementById("src_2").value != '' ) {
    shortcode += ' src2=\'' + document.getElementById("src_2").value + '\''; 
  }
		
	if( document.getElementById("width").value != '' && document.getElementById("width").value % 1 != 0 ) {
		alert('Please enter a valid width.');
		return false;
	}
	if( document.getElementById("width").value != '' )
		shortcode += ' width=' + document.getElementById("width").value;
		
	if( document.getElementById("height").value != '' && document.getElementById("height").value % 1 != 0 ) {
		alert('Please enter a valid height.');
		return false;
	}
	if( document.getElementById("height").value != '' )
		shortcode += ' height=' + document.getElementById("height").value;
	
  if( document.getElementById("autoplay").selectedIndex == 1 )
	  shortcode += ' autoplay=true';
	if( document.getElementById("autoplay").selectedIndex == 2 )
	  shortcode += ' autoplay=false';
    
  if( document.getElementById("loop").checked )
		shortcode += ' loop=true';    
		
	if( document.getElementById("splash").value != '' )
		shortcode += ' splash=\'' + document.getElementById("splash").value + '\'';
    
  if( document.getElementById("splashend").checked )
		shortcode += ' splashend=show';
    
  if( document.getElementById("redirect").value != '' )
		shortcode += ' redirect=\'' + document.getElementById("redirect").value + '\'';        
    
  if( document.getElementById("popup").value != '' ) {
		var popup = document.getElementById("popup").value;
		popup = popup.replace(/&/g,'&amp;');
		popup = popup.replace(/'/g,'\\\'');
		popup = popup.replace(/"/g,'&quot;');
		popup = popup.replace(/</g,'&lt;');
		popup = popup.replace(/>/g,'&gt;');
		shortcode += ' popup=\'' + popup +'\''
	}        
	
	shortcode += ']';
	document.cookie = "selected_video='';expires=Thu, 01-Jan-1970 00:00:01 GMT;";
  document.cookie = "selected_video1='';expires=Thu, 01-Jan-1970 00:00:01 GMT;";
  document.cookie = "selected_video2='';expires=Thu, 01-Jan-1970 00:00:01 GMT;";
	document.cookie = "selected_image='';expires=Thu, 01-Jan-1970 00:00:01 GMT;";
	if( content_original.match( re ) ) {
    if( hTinyMCE == undefined || window.parent.tinyMCE.activeEditor.isHidden() ) {
      //Foliopres WYSIWYG
      oEditor.SetHTML( content_original.replace( re, shortcode ) );      
    }
    else {		
			//Wordpress WYSIWYG
      hTinyMCE.setContent( content_original.replace( re, shortcode ) );
    }
  }
  else {
    if( hTinyMCE == undefined || window.parent.tinyMCE.activeEditor.isHidden() ) {
      //Foliopres WYSIWYG
      oEditor.SetHTML( content_original.replace( re2, shortcode ) );      
    }
    else {		
			//Wordpress WYSIWYG
      hTinyMCE.setContent( content_original.replace( re2, shortcode ) );
    }
    //window.parent.send_to_editor( shortcode );
  }
  
  window.parent.tb_remove();
}

function add_format() {
  if ( document.getElementById("src").value != '' ) {
    if ( document.getElementById("src_1_wrapper").style.display == 'table-row' ) {      
      document.getElementById("src_2_wrapper").style.display = 'table-row';
      if ( document.getElementById("src_2_uploader") != null ) {
        document.getElementById("src_2_uploader").style.display = 'table-row';
      }
      document.getElementById("add_format_wrapper").style.display = 'none';
    }
    else {
      document.getElementById("src_1_wrapper").style.display = 'table-row';
      if ( document.getElementById("src_1_uploader") ) {
        document.getElementById("src_1_uploader").style.display = 'table-row';
      }
    }
  }
  else {
    alert('Please enter the file name of your video file.');
  }
}

</script>
