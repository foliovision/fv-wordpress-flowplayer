<?php
/**
 * Extension of original flowplayer class intended for frontend.
 */
class flowplayer_frontend extends flowplayer
{
	/**
	 * Builds the HTML and JS code of single flowplayer instance on a page/post.
	 * @param string $media URL or filename (in case it is in the /videos/ directory) of video file to be played.
	 * @param array $args Array of arguments (name => value).
	 * @return Returns array with 2 elements - 'html' => html code displayed anywhere on page/post, 'script' => javascript code displayed before </body> tag
	 */
	function build_min_player($media,$args = array()) {
		// returned array with new player's html and javascript content
		$ret = array('html' => '', 'script' => '');
    
    if (isset($args['src1'])&&!empty($args['src1'])) $src1 = trim($args['src1']);
    if (isset($args['src2'])&&!empty($args['src2'])) $src2 = trim($args['src2']);
    
    $media = $this->get_video_url($media);
    if (!empty($src1)) {
      $src1 = $this->get_video_url($src1);
    }
    if (!empty($src2)) {
      $src2 = $this->get_video_url($src2);
    }       
		
    // unique coe for this player
		$hash = md5($media.$this->_salt());
		// setting argument values
		$width =  ( isset($this->conf['width']) && (!empty($this->conf['width'])) ) ? $this->conf['width'] : 320;
		$height = ( isset($this->conf['height']) && (!empty($this->conf['height'])) ) ? $this->conf['height'] : 240;
		$popup = '';
		$autoplay = 'false';
		$controlbar = 'always';
		
    //check user agents
    $aUserAgents = array('iphone', 'ipod', 'iPad', 'aspen', 'incognito', 'webmate', 'android', 'android', 'dream', 'cupcake', 'froyo', 'blackberry9500', 'blackberry9520', 'blackberry9530', 'blackberry9550', 'blackberry9800', 'Palm', 'webos', 's8000', 'bada', 'Opera Mini', 'Opera Mobi', 'htc_touch_pro');
    $mobileUserAgent = false;
    foreach($aUserAgents as $userAgent){
      if(stripos($_SERVER['HTTP_USER_AGENT'],$userAgent))
        $mobileUserAgent = true;
    }
    
    $redirect = '';
		if (isset($this->conf['autoplay'])&&!empty($this->conf['autoplay'])) $autoplay = trim($this->conf['autoplay']);
		if (isset($args['autoplay'])&&!empty($args['autoplay'])) $autoplay = trim($args['autoplay']);
		if (isset($args['width'])&&!empty($args['width'])) $width = trim($args['width']);
		if (isset($args['height'])&&!empty($args['height'])) $height = trim($args['height']);
		if (isset($args['controlbar'])&&($args['controlbar']=='show')) $controlbar = 'never';
    if (isset($args['redirect'])&&!empty($args['redirect'])) $redirect = trim($args['redirect']);
    $scaling = "scale";
		if (isset($this->conf['scaling'])&&($this->conf['scaling']=="true"))
      $scaling = "fit";
		else
      $scaling = "scale";
      
    if (isset($args['splash']) && !empty($args['splash'])) {
  		$splash_img = $args['splash'];
  		if( strpos($splash_img,'http://') === false && strpos($splash_img,'https://') === false ) {
  		  //$splash_img = VIDEO_PATH.trim($args['splash']);
  			if($splash_img[0]=='/') $splash_img = substr($splash_img, 1);
          if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$splash_img))){  //if the site does not live in the document root
            $splash_img = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$splash_img;
          }
          else
          if(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$splash_img)){ // if the videos folder is in the root
            $splash_img = 'http://'.$_SERVER['SERVER_NAME'].VIDEO_DIR.$splash_img;//VIDEO_PATH.$media;
          }
          else {
            //if the videos are not in the videos directory but they are adressed relatively
            $splash_img_path = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$splash_img);
            $splash_img = 'http://'.$splash_img_path;
          }
  		}
      else {
  		  $splash_img = trim($args['splash']);
  		}  		  		
		}
    
    $show_popup = false;
    // if allowed by configuration file, set the popup box js code and content
		if ( ( ( isset($this->conf['popupbox']) ) && ( $this->conf['popupbox'] == "true" ) ) || ( isset($args['popup']) && !empty($args['popup']) ) ) {
			if (isset($args['popup']) && !empty($args['popup'])) {
				$popup = trim($args['popup']);
				$popup = html_entity_decode( str_replace('&#039;',"'",$popup ) );
			}
      else {
				$popup = 'Would you like to replay the video or share the video with your friends?';
			}
      $show_popup = true;
			$popup_contents = '<div id="wpfp_'.$hash.'_custom_popup" class="wpfp_custom_popup" style="display: none; position: absolute; top: 10%; z-index: 2; text-align: center; width: 90%; padding: 0 5%; color: #fff;">'.$popup.'</div>';
      $ret['script'] .= "
        jQuery('#wpfp_".$hash."').bind('finish', function() {          
          jQuery('#wpfp_".$hash."_custom_popup').show();            
        })    
      ";                   
		}
    
    $show_splashend = false;
    if (isset($args['splashend']) && $args['splashend'] == 'show' && isset($args['splash']) && !empty($args['splash'])) {      
      $show_splashend = true;
      $splashend_contents = '<div id="wpfp_'.$hash.'_custom_background" class="wpfp_custom_background" style="display: none; position: absolute; background: url('.$splash_img.') no-repeat center center; background-size: contain; width: 100%; height: 100%; z-index: 1;"></div>';
    }	
    
    $ret['script'] .= "
      jQuery('#wpfp_".$hash."').bind('finish', function() {";
    //if redirection is set
    if ( !empty($redirect) ) {
      $ret['script'] .= "window.open('".$redirect."', '_blank');";
    }
    //if there is a popup content set background color
    if ( $show_popup ) {
      if ( $show_splashend ) {
        $ret['script'] .= "
          jQuery('#wpfp_".$hash." .fp-ui').css('background', '');";
      }
      else {
        $ret['script'] .= "
          jQuery('#wpfp_".$hash." .fp-ui').css('background-color', '#000');";
      }
    }
    if ( $show_splashend ) {
      $ret['script'] .= "
        jQuery('#wpfp_".$hash."_custom_background').show();";
    }
    //remove the background color and popup    
    $ret['script'] .= "
      jQuery('#wpfp_".$hash."').bind('resume seek', function() {
        jQuery('#wpfp_".$hash." .fp-ui').css('background-color', 'transparent');
        ".($show_popup ? "jQuery('#wpfp_".$hash."_custom_popup').hide();" : "")."
        ".($show_splashend ? "jQuery('#wpfp_".$hash."_custom_background').hide();" : "")."
      })";
    $ret['script'] .= "})";
      
    if ((isset($this->conf['autoplay']) && $this->conf['autoplay'] == 'true') || (isset($args['autoplay']) && $args['autoplay'] == 'true')) {
      $autoplay = 'true';
    }     
    $ret['html'] = '<div id="wpfp_' . $hash . '" class="flowplayer';
    if ($autoplay == 'false') {
      $ret['html'] .= ' is-splash';
    }
    $ret['html'] .= '"';
    $ret['html'] .= ' style="width: ' . $width . 'px; height: ' . $height . 'px"';
    $ret['html'] .= ' data-swf="'.RELATIVE_PATH.'/flowplayer/flowplayer.swf"';
    if (isset($this->conf['googleanalytics']) && $this->conf['googleanalytics'] != 'false' && strlen($this->conf['googleanalytics']) > 0) {
      $ret['html'] .= ' data-analytics="' . $this->conf['googleanalytics'] . '"';
    }
    $commercial_key = false;
    if (isset($this->conf['key']) && $this->conf['key'] != 'false' && strlen($this->conf['key']) > 0) {
      $ret['html'] .= ' data-key="' . $this->conf['key'] . '"';
      $commercial_key = true;
    }
    if ($commercial_key && isset($this->conf['logo']) && $this->conf['logo'] != 'false' && strlen($this->conf['logo']) > 0) {
      $ret['html'] .= ' data-logo="' . $this->conf['logo'] . '"';
    }
    $rtmp = false;
    if (isset($this->conf['rtmp']) && $this->conf['rtmp'] != 'false' && strlen($this->conf['rtmp']) > 0) {
      $ret['html'] .= ' data-rtmp="rtmp://' . $this->conf['rtmp'] . '/cfx/st/"';
      $rtmp = true;
    }
    if (isset($this->conf['allowfullscreen']) && $this->conf['allowfullscreen'] == 'false') {
      $ret['html'] .= ' data-fullscreen="false"';
    } 
    if ($width > $height) {
      $ratio = round($height / $width, 4);   
    }
    else
    if ($height > $width) {
      $ratio = round($width / $height, 4);
    }     
    $ret['html'] .= ' data-ratio="' . $ratio . '"';
    if ($scaling == "fit") {
      $ret['html'] .= ' data-flashfit="true"';
    }            
    //$ret['html'] .= ' data-debug="true"';
    $ret['html'] .= '>';
    $ret['html'] .= '<video';      
    if (isset($splash_img) && !empty($splash_img)) {
      $ret['html'] .= ' poster="'.$splash_img.'"';
    } 
    if ($autoplay == 'true') {
      $ret['html'] .= ' autoplay';  
    }
    if (isset($args['loop']) && $args['loop'] == 'true') {
      $ret['html'] .= ' loop';
    }     
    if (isset($this->conf['autobuffer']) && $this->conf['autobuffer'] == 'true') {
      $ret['html'] .= ' preload';
    }
    else
    if ($autoplay == 'false') {
      $ret['html'] .= ' preload="none"';        
    }         
    $ret['html'] .= '>';
         
    //if the cloudfront is set in the plugin settings screen but it isn't acually a streaming 
    if (strpos($media, 'amazonaws.com') === false) {
      $rtmp = false;
    }      
    $ret['html'] .= $this->get_video_src($media, $mobileUserAgent);
    if (!empty($src1)) {
      $ret['html'] .= $this->get_video_src($src1, $mobileUserAgent);
    }
    if (!empty($src2)) {
      $ret['html'] .= $this->get_video_src($src2, $mobileUserAgent);
    }
    //don't use RTMP for mobile devices
    if ($rtmp && !$mobileUserAgent) {
      $video_url = parse_url($media);
      $video_url = explode('/', $video_url['path'], 3);        
      $media_file = $video_url[count($video_url)-1];
      $extension = $this->get_file_extension($media);
      $ret['html'] .= '<source src="'.$extension.':'.trim($media_file).'" type="video/flash" />';
    }      
    $ret['html'] .= '</video>';
    $ret['html'] .= $splashend_contents;
    $ret['html'] .= $popup_contents;            
    $ret['html'] .= '</div>';      
    
		return $ret;
	}
  
  function get_video_url($media) {
    if( strpos($media,'http://') === false && strpos($media,'https://') === false ) {
			// strip the first / from $media
      if($media[0]=='/') $media = substr($media, 1);
      if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$media))){  //if the site does not live in the document root
        $media = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$media;
      }
      else if(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$media)){ // if the videos folder is in the root
        $media = 'http://'.$_SERVER['SERVER_NAME'].VIDEO_DIR.$media;//VIDEO_PATH.$media;
      }
      else{ // if the videos are not in the videos directory but they are adressed relatively
        $media_path = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$media);
        $media = 'http://'.$media_path;
      }
		}
    return $media;
  }
  
  function get_video_src($media, $mobileUserAgent) {
    $extension = $this->get_file_extension($media);
    //do not use https on mobile devices
    if (strpos($media, 'https') !== false && $mobileUserAgent) {
      $media = str_replace('https', 'http', $media);
    } 
    return '<source src="'.trim($media).'" type="video/'.$extension.'" />';  
  }
  
  function get_file_extension($media) {
    $pathinfo = pathinfo($media);
    $extension = $pathinfo['extension'];                  
    if (!in_array($extension, array('mp4', 'm4v', 'webm', 'ogv'))) {
      $extension = 'flash';  
    }
    else
    if ($extension == 'm4v') {
      $extension = 'mp4';
    }
    return $extension;  
  }
  
	/**
	 * Displays the elements that need to be added to frontend.
	 */
	function flowplayer_head() {
    include dirname( __FILE__ ) . '/../view/frontend-head.php';
	}
      
}
?>