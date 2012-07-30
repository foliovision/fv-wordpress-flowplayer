<?php
if (isset($_COOKIE["selected_video"]))
   $uploaded['normal'] = $_COOKIE["selected_video"];
if (isset($_COOKIE["selected_video_low"]))
   $uploaded['low'] = $_COOKIE["selected_video_low"];
if (isset($_COOKIE["selected_video_mobile"]))
   $uploaded['mobile'] = $_COOKIE["selected_video_mobile"];
if (isset($_COOKIE["selected_video_webm"]))
   $uploaded['webm'] = $_COOKIE["selected_video_webm"];
if (isset($_COOKIE["selected_video_3gp"]))
   $uploaded['3gp'] = $_COOKIE["selected_video_3gp"];
if (isset($_COOKIE["selected_image"]))
   $uploaded['splash'] =  $_COOKIE["selected_image"];
 
  $post_id = intval($_REQUEST['post_id']);
  //load configuration file:   
  $conf = get_option( 'fvwpflowplayer' );
  $allow_uploads = false;

	if (isset($conf["allowuploads"]))
	  $allow_uploads = $conf["allowuploads"];
	if (isset($conf["postthumbnail"]))
	  $post_thumbnail = $conf["postthumbnail"];
	  
	$uploadtype = '';  
	if (isset($selected_attachment['url'])) {
	  switch ($selected_attachment['type']){
      case 'splash': $uploaded['splash'] = $selected_attachment['url']; //$uploaded_image = $selected_attachment['url'];
                     $uploadtype = 'splash';
         break;
      case 'normal': $uploaded['normal'] = $selected_attachment['url']; //$uploaded_video = $selected_attachment['url'];
                     $uploadtype = 'normal';
         break;
      case 'low': $uploaded['low'] = $selected_attachment['url']; //$uploaded_video = $selected_attachment['url'];
                     $uploadtype = 'low';
         break;
      case 'mobile': $uploaded['mobile'] = $selected_attachment['url']; //$uploaded_video_mobile = $selected_attachment['url'];
                     $uploadtype = 'mobile';
         break;
      case 'webm': $uploaded['webm'] = $selected_attachment['url']; //$uploaded_video_mobile = $selected_attachment['url'];
                     $uploadtype = 'webm';
         break;
      case '3gp': $uploaded['3gp'] = $selected_attachment['url']; //$uploaded_video_mobile = $selected_attachment['url'];
                     $uploadtype = '3gp';
         break;
     }
   }
   
/*	$video_types = array('flv','mov','avi','mpeg','mpg','asf','qt','wmv','mp4','mp3');
  $splash_types = array('jpg','jpeg','gif','png', 'bmp','jpe');
  if (isset($selected_attachment['url'])) {
    $path_parts = pathinfo($selected_attachment['url']);
    if (in_array($path_parts['extension'], $video_types))
      $uploaded_video = $selected_attachment['url'];
    if (in_array($path_parts['extension'], $splash_types))
      $uploaded_image = $selected_attachment['url'];
  }*/
 if ( ( $uploadtype == 'normal' ) || ( $uploadtype == 'mobile' ) || ( $uploadtype == 'webm' ) || ( $uploadtype == '3gp' )){
    $serv = $_SERVER['SERVER_NAME'];
    $pattern = '/'.$serv.'(.*)/';
    preg_match($pattern, $uploaded[$uploadtype], $matches);
    if($matches[1]) $strUpVideo = $matches[1];
    else $strUpVideo = $uploaded[$uploadtype];
    require_once(realpath(dirname(__FILE__).'/getid3/getid3.php'));
    
    // Initialize getID3 engine
    $getID3 = new getID3;
      
    $ThisFileInfo = $getID3->analyze(realpath($_SERVER['DOCUMENT_ROOT'] .$strUpVideo));
    if (isset($ThisFileInfo['error'])) $file_error[$uploadtype] = "Could not read video details, please fill the width and height manually.";
    //getid3_lib::CopyTagsToComments($ThisFileInfo);
    $file_time[$uploadtype] = $ThisFileInfo['playtime_string'];            // playtime in minutes:seconds, formatted string
    $file_width[$uploadtype] = $ThisFileInfo['video']['resolution_x'];          
    $file_height[$uploadtype] = $ThisFileInfo['video']['resolution_y'];
    $file_size[$uploadtype] = $ThisFileInfo['filesize'];           
    $file_size[$uploadtype] = round($file_size[$uploadtype]/(1024*1024),2);                
   }  	    
   

?>
<!--script type="text/javascript">
function fillVideoInputs(){
   var vid_list = document.getElementById("files_video");
   var item = vid_list.options[vid_list.selectedIndex].title;
//   alert(item); 
   document.getElementById("src").value = item;
   document.getElementById("hidden_video").value = item;
}
function fillSplashInputs(){
   var spl_list = document.getElementById("files_splash");
   var item = spl_list.options[spl_list.selectedIndex].title;
//   alert(item); 
   document.getElementById("splash").value = item;
   document.getElementById("hidden_splash").value = item;
   document.cookie = "selected_image="+item+";";
}
</script-->
<form>
	<table class="slidetoggle describe">
		<tbody>
			<tr>
				<th valign="top" scope="row" class="label"><span class="alignright">Video</span></th>
				<td colspan="2" class="field" style="width: 75%"><input type="text" class="text" id="src" name="src" style="width: 100%" value="<?php echo $uploaded['normal'] ?>"/></td>
			</tr>
			<?php 
         if ($allow_uploads=="true") { 
			echo '<tr>
			<th></th>
			<td colspan="2" style="width: 100%" >         
         Or <a href="media-upload.php?post_id='.$post_id.'&amp;type=video&amp;TB_iframe=true&amp;width=640&amp;height=723fvplayernormal">open media library</a> to upload new video.
			</td>
			</tr>';
			 }; //allow uplads video ?>

			<?php if (!empty($uploaded['normal'])){?>
			   <tr><th></th>
         <th valign="top" scope="row" class="label"><span class="alignleft">File info</span></th><td>
           <?php if (!empty($file_width['normal'])){?>
            Video Duration: <?php echo $file_time['normal'] ?><br />
            File size: <?php echo $file_size['normal'] ?>MB
            <?php } else echo $file_error['normal'];  ?>
            </td>
         </tr>
      <?php }; //video has been selected ?>
			<tr><!--th></th-->
				<th valign="top" scope="row" class="label" ><span class="alignright">Width <small>(px)</small></span><br class='clear' /></th>
				<td  colspan="2" class="field"><input type="text" id="width" name="width" style="width: 100%"  value="<?php echo $file_width['normal'] ?>"/></td>
			</tr>
			<tr><!--th></th-->
				<th valign="top" scope="row" class="label" style="width: 10%"><span class="alignright">Height <small>(px)</small></span></th>
				<td  colspan="2" class="field"><input type="text" id="height" name="height" style="width: 100%" value="<?php echo $file_height['normal'] ?>"/></td>
			</tr>
			<tr>
				<th valign="top" scope="row" class="label"><span class="alignright">Splash Image</span></th>
				<td class="field" colspan="2"><input type="text" id="splash" name="splash" style="width: 100%"  value="<?php echo $uploaded['splash'] ?>"/></td>
			</tr>
			<?php if ($allow_uploads=='true') {
			echo '<tr>
  			<th></th>
  			<td colspan="2" class="field" style="width: 100%" >
        Or <a href="media-upload.php?type=image&amp;post_id='.$post_id .'&amp;TB_iframe=true&amp;width=640&amp;height=723fvplayersplash">open media library</a> to upload new splash image.
        </td>
			</tr>';
			 }; //allow uplads splash image ?>
			<?php if (!empty($uploaded['splash']))
          if (($post_thumbnail=='true') && current_theme_supports( 'post-thumbnails') && isset($selected_attachment['id'])) 
             update_post_meta( $post_id, '_thumbnail_id', $selected_attachment['id'] );?>
			<tr><th colspan=3 style="text-align:left; padding-left:50px">&nbsp;</th></tr>
			<tr><th colspan=3 style="text-align:left; padding-left:30px">Additional features:</th></tr>
			<tr>
				<td valign="top" scope="row" class="label"><span class="alignright">HTML Popup</span></td>
				<td colspan="2"><textarea type="text" id="popup" name="popup" style="width: 100%"></textarea></td>
			</tr>
			<tr>
				<td valign="top" scope="row" class="label"><span class="alignright">Redirect to</span></td>
				<td class="field" colspan="2"><input type="text" id="redirect" name="redirect" style="width: 100%"  value=""/></td>
			</tr>

			<tr>
				<td valign="top" scope="row" class="label"><span class="alignright">Autoplay</span></td>
				<td colspan="2" class="field">
               <select id="autoplay" name="autoplay">
                  <option>Default&nbsp;</option><option>On</option><option>Off</option>
               </select><!--input type="checkbox" id="autoplay" name="autoplay" /--> 
        </td>
			</tr>
			<tr>
				<td valign="top" scope="row" class="label"><span class="alignright">Controlbar</span></td>
				<td colspan="2" class="field"><!--input type="checkbox" id="controlbar" name="controlbar" /-->
               <select id="controlbar" name="controlbar">
                  <option>Default</option><option>Always show</option><option>Always hide</option>
               </select>
            </td>
			</tr>
			<tr>
				<!--th valign="top" scope="row" class="label"><span class="alignright"></span></th-->
				<td colspan="3" class="field" style="padding-left:40px"> 
        Show splash image at the end&nbsp;<input type="checkbox" id="splashend" name="splashend" />
        <span style="font-size:70%; width:300px;">(The splash image has to have the same dimensions as the video)</span>
        </td>
			</tr>
			

			<tr>
				<th valign="top" scope="row" class="label"  style="test-align:right">
					<input type="button" value="Insert" name="insert" id="insert-button" class="button-primary" onclick="clickOK();" style="float:right"/>
				</th>
			</tr>
		</tbody>
	</table>
</form>
<script type="text/javascript">
   var shortcode;
   if(window.parent.tinyMCE.activeEditor){
      var re = /\[flowplayer[^\[]*?<span>FCKFVWPFlowplayerPlaceholder<\/span>[^\[]*?\]/mi;
	    var re2 = /<span>FCKFVWPFlowplayerPlaceholder<\/span>/gi;
      var hTinyMCE = window.parent.tinyMCE.activeEditor;

      if(hTinyMCE){
        hTinyMCE.selection.setContent('<span>FCKFVWPFlowplayerPlaceholder</span>');
        content_original = hTinyMCE.getContent();
        content = content_original.replace(/\n/g,'\uffff');
    		shortcode = content.match( re );
    		hTinyMCE.setContent( hTinyMCE.getContent().replace( re2,'' ) );
		  }
	 }

	if(window.parent.FCKeditorAPI){
   	var oEditor = window.parent.FCKeditorAPI.GetInstance('content') ;
   	if (typeof oEditor != 'undefined')
   	{
   		oEditor.InsertHtml('<span>FCKFVWPFlowplayerPlaceholder</span>');
   	}
   	var re = /\[flowplayer[^\[]*?<span>FCKFVWPFlowplayerPlaceholder<\/span>[^\[]*?\]/mi;
   	var re2 = /<span>FCKFVWPFlowplayerPlaceholder<\/span>/gi;
   	if( (oEditor == undefined) || window.parent.tinyMCE.activeEditor.isHidden() ) {
   	}
   	else {
   		content_original = oEditor.GetHTML();
   		content = content_original.replace(/\n/g,'\uffff');
   		shortcode = content.match( re );
   		var orig = oEditor.GetHTML().replace( re2,'' );
   		oEditor.SetData( orig );
      }
   }	
   if( shortcode != null ) {
		shortcode = shortcode.join('');
		shortcode = shortcode.replace( re2,'' );
		shortcode = shortcode.replace( /\\'/g,'&#039;' );
		
		srcurl = shortcode.match( /src='([^']*)'/ );
		if( srcurl == null ) srcurl = shortcode.match( /src=([^,\]\s]*)/ );
		
		ssplash = shortcode.match( /splash='([^']*)'/ );
		if( ssplash == null ) ssplash = shortcode.match( /splash=\"([^\"]*)\"/ );
		if( ssplash == null ) ssplash = shortcode.match( /splash=([^,\]\s]*)/ );

		iheight = shortcode.match( /height=(\d*)/ );			
		iwidth = shortcode.match( /width=(\d*)/ );
		if( iheight == null ) iheight = shortcode.match( /height='(\d*)'/ );
		if( iwidth == null ) iwidth = shortcode.match( /width='(\d*)'/ );
		
		spopup = shortcode.match( /popup='([^']*)'/ );
		if( spopup == null ) spopup = shortcode.match( /popup=\"([^\"]*)\"/ );
      sredirect = shortcode.match( /redirect='([^']*)'/ );
      if(sredirect == null ) sredirect = shortcode.match( /redirect=\"([^\"]*)\"/ );
      			
		sautoplay = shortcode.match( /autoplay='([^\s\]]+)'/ );
		if(sautoplay == null )sautoplay = shortcode.match( /autoplay=([^\s]+)/ );
		
      controlbar = shortcode.match( /controlbar='([^\s\]]+)'/ );
	   if( controlbar == null ) controlbar = shortcode.match( /controlbar=([^\s]+)/ );

		ssplashend = shortcode.match( /splashend='([^\s\]]+)'/ );
		if(ssplashend[1] == null || !ssplashend[1] )ssplashend = shortcode.match( /splashend=([^\s]+)/ );
		//alert( srcurl[1] + '\n' + iheight[1] + '\n' + iwidth[1] + '\n' + splash[1] + '\n' + popup[1] );
		if( srcurl != null && srcurl[1] != null )
			document.getElementById("src").value = srcurl[1];
		if( iheight != null && iheight[1] != null )
			document.getElementById("height").value = iheight[1];
		if( iwidth != null && iwidth[1] != null )
			document.getElementById("width").value = iwidth[1];
		if( ssplash != null && ssplash[1] != null )
			document.getElementById("splash").value = ssplash[1];
		if( spopup != null && spopup[1] != null ) {
			spopup = spopup[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
			spopup = spopup.replace(/&amp;/g,'&');
			document.getElementById("popup").value = spopup;
		}
		if( sredirect != null && sredirect[1] != null )
			document.getElementById("redirect").value = sredirect[1];
	
		if( (ssplashend != null) && (ssplashend[1] != null) ){
      	document.getElementById("splashend").checked = true;//sautoplay[1];
      }
		if( controlbar != null && controlbar[1] != null ){
		   if (controlbar[1] == 'show')
  				document.getElementById("controlbar").selectedIndex = 1;//sautoplay[1];
		   else if (controlbar[1] == 'hide')
  				document.getElementById("controlbar").selectedIndex = 2;//sautoplay[1];
 				else document.getElementById("controlbar").selectedIndex = 0;
		}

		/*if( sautoplay != null && sautoplay[1] != null )
			document.getElementById("autoplay").checked = true;//sautoplay[1];
*/
		if( (sautoplay != null) && (sautoplay[1] != null) ){
		   if (sautoplay[1] == 'true')
  				document.getElementById("autoplay").selectedIndex = 1;//sautoplay[1];
		   else if (sautoplay[1] == 'false')
  				document.getElementById("autoplay").selectedIndex = 2;//sautoplay[1];
 				else document.getElementById("autoplay").selectedIndex = 0;
		}

	 
		document.getElementById("insert-button").value = "Update";
	}
//	document.getElementById("src").focus();


function clickOK() {
			
	var shortcode = '';
	
	if(document.getElementById("src").value == '') {
		alert('Please enter the file name of your video file.');
		return false;
	}
	else
		shortcode = '[flowplayer src=\'' + document.getElementById("src").value + '\'';
		
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
	
   if( document.getElementById("autoplay").checked )
		shortcode += ' autoplay=true';

   if( document.getElementById("splashend").checked )
		shortcode += ' splashend=show';
		
	if( document.getElementById("splash").value != '' )
		shortcode += ' splash=\'' + document.getElementById("splash").value + '\'';

	if( document.getElementById("redirect").value != '' )
		shortcode += ' redirect=\'' + document.getElementById("redirect").value + '\'';
	
	if( document.getElementById("popup").value != '' ) {
			var popup = document.getElementById("popup").value;
			popup = popup.replace(/&/g,'&amp;');
			popup = popup.replace(/'/g,'\\\'');
			popup = popup.replace(/"/g,'&quot;');
			popup = popup.replace(/</g,'&lt;');
			popup = popup.replace(/>/g,'&gt;');
			shortcode += ' popup=\'' + popup +'\'';
	}
	
	if( document.getElementById("controlbar").selectedIndex == 1 )
	  shortcode += ' controlbar=show';
	if( document.getElementById("controlbar").selectedIndex == 2 )
	  shortcode += ' controlbar=hide';

	if( document.getElementById("autoplay").selectedIndex == 1 )
	  shortcode += ' autoplay=true';
	if( document.getElementById("autoplay").selectedIndex == 2 )
	  shortcode += ' autoplay=false';
      	
	shortcode += ']';
	document.cookie = "selected_video='';expires=Thu, 01-Jan-1970 00:00:01 GMT;";
	document.cookie = "selected_image='';expires=Thu, 01-Jan-1970 00:00:01 GMT;";
	if( hTinyMCE == undefined || window.parent.tinyMCE.activeEditor.isHidden() ) {
		window.parent.send_to_editor( shortcode );
	}
	else {
		if( content_original.match( re ) )
			hTinyMCE.setContent( content_original.replace( re,shortcode ) );
		else
			hTinyMCE.setContent( content_original.replace( re2,shortcode ) );
	
		//return true;
		window.parent.tb_remove();
	}
}

</script>