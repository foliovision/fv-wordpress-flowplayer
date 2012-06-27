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
	function build_min_player($media,$sources,$args = array()) {
			// returned array with new player's html and javascript content
		$ret = array('html' => '', 'script' => '');
		$extension = substr($media, -3);
		if( strpos($media,'http://') === false && strpos($media,'https://') === false ) {
			// strip the first / from $media
         if($media[0]=='/') $media = substr($media, 1);
         if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$media))){  //if the site does not live in the document root
            $media = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$media;
            }
         elseif(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$media)){ // if the videos folder is in the root
            $media = 'http://'.$_SERVER['SERVER_NAME'].VIDEO_DIR.$media;//VIDEO_PATH.$media;
         }
         else{ // if the videos are not in the videos directory but they are adressed relatively
          $media_path = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$media);
          $media = 'http://'.$media_path;
         }
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
      $aUserAgents = array('iphone', 'ipod', 'iPad', 'aspen', 'incognito', 'webmate', 'android', 'Android', 'dream', 'cupcake', 'froyo', 'blackberry9500', 'blackberry9520', 'blackberry9530', 'blackberry9550', 'blackberry9800', 'Palm', 'webos', 's8000', 'bada', 'Opera Mini', 'Opera Mobi', 'htc_touch_pro');
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
    if (isset($args['splashend'])&&($args['splashend']=='show')) $splashend = 'true';
    else $splashend = '';
		if (isset($this->conf['scaling'])&&($this->conf['scaling']=="true")) $scaling = "fit";
		else $scaling = "scale";
    if (isset($args['splash']) && !empty($args['splash'])) {
			$splash_img = $args['splash'];
			if( strpos($splash_img,'http://') === false && strpos($splash_img,'https://') === false ) {
			//	$splash_img = VIDEO_PATH.trim($args['splash']);
				if($splash_img[0]=='/') $splash_img = substr($splash_img, 1);
             if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$splash_img))){  //if the site does not live in the document root
                $splash_img = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$splash_img;
                }
             elseif(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$splash_img)){ // if the videos folder is in the root
                $splash_img = 'http://'.$_SERVER['SERVER_NAME'].VIDEO_DIR.$splash_img;//VIDEO_PATH.$media;
             }
             else{ // if the videos are not in the videos directory but they are adressed relatively
              $splash_img_path = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$splash_img);
              $splash_img = 'http://'.$splash_img_path;
             }
			} else {
				$splash_img = trim($args['splash']);
			}
			$splash = '<img src="'.$splash_img.'" alt="" class="splash" /><img width="83" height="83" border="0" src="'.RELATIVE_PATH.'/images/play.png" alt="" class="splash_play_button" style="top: '.round($height/2-45).'px; border:0;" />';
			// overriding the "autoplay" configuration - video should start immediately after click on the splash image
			$this->conf['autoplay'] = 'true';
			$autoplay = 'true';
		}
		else 
      if( $mobileUserAgent == true ) 
        $splash = '<img width="83" height="83" border="0" src="'.RELATIVE_PATH.'/images/play.png" alt="" class="splash_play_button" style="top: '.round($height/2-45).'px; border:0;"/>';
			// if allowed by configuration file, set the popup box js code and content
			if ((( isset($this->conf['popupbox'] )) && ( $this->conf['popupbox']=="true" )) || (isset($args['popup']) && !empty($args['popup'])) || (!empty($redirect))|| (!empty($splashend))) {
				if ( isset($args['popup'] ) && !empty($args['popup']) ) {
					$popup = trim($args['popup']);
					//$popup = html_entity_decode(str_replace("_"," ",substr($popup,1,strlen($popup)-2)));
					$popup = html_entity_decode( str_replace('&#039;',"'",$popup ) );
				} else {
               if (!$splashend)
   					$popup = 'Would you like to replay the video or share the link to it with your friends?';
   				else 
   				   $popup = '';
    			}
				$link_button = '';
				if ($this->conf['linkhighlight']=="true"){
          preg_match('/(\<a href=.*?\>)(.*?)\<\/a\>/',$popup,$matches);
			    if(!empty($matches[1])) $link_button = $matches[1] . '<span class="link_button">' . $matches[2] . '</span></a>';
			  }
				$popup_controls = '<div style="position:absolute;top:70%; width:100%;"><div class="popup_controls" style="border:none;text-align:center;"> <a title="Replay video" href="javascript:fp_replay(\''.$hash.'\');"><img src="'.RELATIVE_PATH.'/images/replay.png" alt="Replay video" /></a>&nbsp;&nbsp;&nbsp;<a title="Share video" href="javascript:fp_share(\''.$hash.'\');"><img src="'.RELATIVE_PATH.'/images/share.png" alt="Share video" /></a></div></div>';
				$popup_contents = "\n".'<div id="popup_contents_'.$hash.'" class="popup_contents" style="border:none;">'.$popup_controls.'<div id="wpfp_'.$hash.'_custom_popup" class="wpfp_custom_popup" style="border:none;margin:5%;text-align:center;"><p>'.$popup.'</p><br /><br />'.$link_button.'</div></div>';
				//if ( $splashend =='true' ) $popup_contents = "\n".'<div id="popup_contents_'.$hash.'" class="popup_contents" style="border:none;">'.$popup_controls.'<div id="wpfp_'.$hash.'_custom_popup" class="wpfp_custom_popup" style="border:none;margin:5%;text-align:center;"><p></p><br /><br /></div></div>';
        // replace href attribute by javascript function
				$popup_contents = str_replace("href=\"","onClick=\"javascript:window.location=this.href\" href=\"",$popup_contents);
				$popup_code = "
				window.flowplayer('wpfp_$hash').onFinish(function() {
      			if ('$redirect'){
                  window.open('$redirect','fv_redirect_to');             
               }else{
               	var fp = document.getElementById('wpfp_$hash');
     					var popup = document.createElement('div');
     					var popup_contents = document.getElementById('popup_contents_$hash');
     					popup.className = 'flowplayer_popup';
     					popup.id = 'wpfp_".$hash."_popup';
     					if('$splashend'=='true')
     					  popup.style.background = '#060606 url(\"$splash_img\") no-repeat';
     					popup.innerHTML = popup_contents.innerHTML;
     					fp.appendChild(popup);
               }
				});
				window.flowplayer('wpfp_$hash').onLoad(function() {
				   var fp = document.getElementById('wpfp_".$hash."');
					var emb = document.getElementById('wpfp_".$hash."').innerHTML;
               var e_start = emb.substr(0,emb.indexOf(\"width\",0)+7);
               var e_mid = emb.substr(emb.indexOf(\"width\",0)+11,10);
               var e_end = emb.substr(emb.indexOf(\"height\",0)+12,emb.length-emb.indexOf(\"height\",0)+12);
               e_start = e_start+fp.style.width + e_mid + fp.style.height+e_end;
               document.getElementById('embeded_$hash').value = e_start;
				});
				window.flowplayer('wpfp_$hash').onStart(function() {
					var popup = document.getElementById('wpfp_".$hash."_popup');
					var fp = document.getElementById('wpfp_$hash');
					var emb = document.getElementById('wpfp_".$hash."').innerHTML;
               var e_start = emb.substr(0,emb.indexOf(\"width\",0)+7);
               var e_mid = emb.substr(emb.indexOf(\"width\",0)+11,10);
               var e_end = emb.substr(emb.indexOf(\"height\",0)+12,emb.length-emb.indexOf(\"height\",0)+12);
               e_start = e_start+fp.style.width + e_mid + fp.style.height+e_end;
               document.getElementById('embeded_$hash').value = e_start;
					fp.removeChild(popup);
				});
				";
			}
			 // set the output JavaScript (which will be added to document head)
			if( $mobileUserAgent == false ) 
			  $ret['script'] = '
				if (document.getElementById(\'wpfp_'.$hash.'\') != null) {
				'.(($mobileUserAgent==true)?'jQuery(function() {':'').'
					flowplayer("wpfp_'.$hash.'", {src: "'.PLAYER.'", wmode: \'opaque\'}, {
	'.(isset($this->conf['key'])&&strlen($this->conf['key'])>0?'key:\''.trim($this->conf['key']).'\',':'').'
            plugins: {
            '.(((empty($args['controlbar']))||$args['controlbar']=='show')?'
							controls: {		
     				         hideDelay: 500,
								autoHide: \''.trim($controlbar).'\',
         					buttonOverColor: \''.trim($this->conf['buttonOverColor']).'\',
         					sliderColor: \''.trim($this->conf['sliderColor']).'\',
         					bufferColor: \''.trim($this->conf['bufferColor']).'\',
         					sliderGradient: \'none\',
         					progressGradient: \'medium\',
         					durationColor: \''.trim($this->conf['durationColor']).'\',
         					progressColor: \''.trim($this->conf['progressColor']).'\',
         					backgroundColor: \''.trim($this->conf['backgroundColor']).'\',
         					timeColor: \''.trim($this->conf['timeColor']).'\',
         					buttonColor: \''.trim($this->conf['buttonColor']).'\',
         					backgroundGradient: \'none\',
         					bufferGradient: \'none\',
	   						opacity:1.0,
     				         fullscreen: '.(isset($this->conf['allowfullscreen'])?trim($this->conf['allowfullscreen']):'true').'
	   					}':'controls:null'
                     ).',
                     audio: {
               			url: \''.AUDIOPLAYER.'\'
               		}
						},
						clip: {  
							url: \''.trim($media).'\', 
							autoPlay: '.trim($autoplay).',
							scaling: \''.$scaling.'\',
							autoBuffering: '.(isset($this->conf['autobuffer'])?trim($this->conf['autobuffer']):'false').'
						}, 
						canvas: {
							backgroundColor:\''.trim($this->conf['canvas']).'\'
						}
					});
					'.(($mobileUserAgent==true)?'flowplayer("wpfp_'.$hash.'").html5({html5_force:true, h264_baseurl:"http://diveintohtml5.org/i"});':'').'
					'.(($mobileUserAgent==true)?'});':'').'
				};
			';//.$popup_code;
         if($mobileUserAgent==false) $ret['script'] .= $popup_code;
			 // set the output HTML (which will be printed into document body)
		//	$ret['html'] .= '<a id="wpfp_'.$hash.'" style="width:'.$width.'px; height:'.$height.'px;" class="flowplayer_container">'.$splash.'</a>'.$popup_contents;
		 $ret['html'] .= '<a id="wpfp_'.$hash.'" style="width:'.$width.'px; height:'.$height.'px;" class="flowplayer_container player plain">'.$splash.'</a>';//.$popup_contents;
		   if($mobileUserAgent==false) $ret['html'] .= $popup_contents;
         if($mobileUserAgent==true) 
         $ret['html'] = '<video  poster="'.$splash_img.'" width="'.$width.'" height="'.$height.'"  controls >
         	<source src="'.trim($media).'"  type="video/mp4" />
         </video>';
		// return new player's html and script
		return $ret;
	}
	/**
	 * Displays the elements that need to be added to frontend.
	 */
	function flowplayer_head() {
		include dirname( __FILE__ ) . '/../view/frontend-head.php';
	}
}
?>