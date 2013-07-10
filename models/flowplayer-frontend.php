<?php
/**
 * Extension of original flowplayer class intended for frontend.
 */
class flowplayer_frontend extends flowplayer
{

	var $ajax_count = 0;

	/**
	 * Builds the HTML and JS code of single flowplayer instance on a page/post.
	 * @param string $media URL or filename (in case it is in the /videos/ directory) of video file to be played.
	 * @param array $args Array of arguments (name => value).
	 * @return Returns array with 2 elements - 'html' => html code displayed anywhere on page/post, 'script' => javascript code displayed before </body> tag
	 */
	function build_min_player($media,$args = array()) {
				
    // unique coe for this player
		$hash = md5($media.$this->_salt());    
		$player_type = 'video';
    
		// returned array with new player's html and javascript content
		$ret = array('html' => '', 'script' => '');
      
    
    // set common variables
		$width = ( isset($this->conf['width']) && (!empty($this->conf['width'])) && intval($this->conf['width']) > 0 ) ? $this->conf['width'] : 320;
		$height = ( isset($this->conf['height']) && (!empty($this->conf['height'])) && intval($this->conf['height']) > 0 ) ? $this->conf['height'] : 240;
		if (isset($args['width'])&&!empty($args['width'])) $width = trim($args['width']);
		if (isset($args['height'])&&!empty($args['height'])) $height = trim($args['height']);		
        
    $src1 = ( isset($args['src1']) && !empty($args['src1']) ) ? trim($args['src1']) : false;
    $src2 = ( isset($args['src2']) && !empty($args['src2']) ) ? trim($args['src2']) : false;  
    $mobile = ( isset($args['mobile']) && !empty($args['mobile']) ) ? trim($args['mobile']) : false;  
    
    $autoplay = 'false';
   	if( (isset($this->conf['autoplay']) && $this->conf['autoplay'] == 'true' && $args['autoplay'] != 'false' ) || (isset($args['autoplay']) && $args['autoplay'] == 'true') ) {
			$autoplay = 'true';
		}  
    
    //	decide which player to use
    foreach( array( $media, $src1, $src2 ) AS $media_item ) {
    	if( preg_match( '!\.mp3$!', $media_item ) ) {
				$player_type = 'audio';
				break;
			} 
    }
    
    
    if( $player_type == 'video' ) {
    
			foreach( array( $media, $src1, $src2 ) AS $media_item ) {
				//if( ( strpos($media_item, 'amazonaws.com') !== false && stripos( $media_item, 'http://s3.amazonaws.com/' ) !== 0 && stripos( $media_item, 'https://s3.amazonaws.com/' ) !== 0  ) || stripos( $media_item, 'rtmp://' ) === 0 ) {  //  we are also checking amazonaws.com due to compatibility with older shortcodes
				if( stripos( $media_item, 'rtmp://' ) === 0 ) {
					$rtmp = $media_item;
				} 
							
				if( $this->conf['engine'] == 'default' && stripos( $media_item, '.m4v' ) !== false ) {
					$ret['script'] .= "
						if( jQuery.browser.mozilla && navigator.appVersion.indexOf(\"Win\")!=-1 ) {
							jQuery('#wpfp_".$hash."').attr('data-engine','flash');
						}
						";
				}
			}    
			
			if( isset($args['rtmp']) && !empty($args['rtmp']) && isset($args['rtmp_path']) && !empty($args['rtmp_path']) ) {
				$rtmp = trim( $args['rtmp_path'] );
			}
			if (!empty($media)) {
				$media = $this->get_video_url($media);
			}
			if (!empty($src1)) {
				$src1 = $this->get_video_url($src1);
			}
			if (!empty($src2)) {
				$src2 = $this->get_video_url($src2);
			} 
			if (!empty($mobile)) {
				$mobile = $this->get_video_url($mobile);
			}			
			
			$popup = '';
			$controlbar = 'hide';
			
			//check user agents
			$aUserAgents = array('iphone', 'ipod', 'iPad', 'aspen', 'incognito', 'webmate', 'android', 'android', 'dream', 'cupcake', 'froyo', 'blackberry9500', 'blackberry9520', 'blackberry9530', 'blackberry9550', 'blackberry9800', 'Palm', 'webos', 's8000', 'bada', 'Opera Mini', 'Opera Mobi', 'htc_touch_pro');
			$mobileUserAgent = false;
			foreach($aUserAgents as $userAgent){
				if(stripos($_SERVER['HTTP_USER_AGENT'],$userAgent))
					$mobileUserAgent = true;
			}
			
			$redirect = '';			

			if (isset($args['controlbar'])&&($args['controlbar']=='show')) $controlbar = 'show';
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
			
			if (isset($args['subtitles']) && !empty($args['subtitles'])) {
				$subtitles = $args['subtitles'];
				if( strpos($subtitles,'http://') === false && strpos($subtitles,'https://') === false ) {
					//$splash_img = VIDEO_PATH.trim($args['splash']);
					if($subtitles[0]=='/') $subtitles = substr($subtitles, 1);
						if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$subtitles))){  //if the site does not live in the document root
							$subtitles = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$subtitles;
						}
						else
						if(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$subtitles)){ // if the videos folder is in the root
							$subtitles = 'http://'.$_SERVER['SERVER_NAME'].VIDEO_DIR.$subtitles;//VIDEO_PATH.$media;
						}
						else {
							//if the videos are not in the videos directory but they are adressed relatively
							$subtitles = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$subtitles);
							$subtitles = 'http://'.$subtitles;
						}
				}
				else {
					$subtitles = trim($args['subtitles']);
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
					$popup = 'Would you like to replay the video?';
				}
				
				$popup = apply_filters( 'fv_flowplayer_popup_html', $popup);
				if( strlen(trim($popup)) > 0 ) {			
					$show_popup = true;
					$popup_contents = '<div id="wpfp_'.$hash.'_custom_popup" class="wpfp_custom_popup" style="display: none; position: absolute; top: 10%; z-index: 2; text-align: center; width: 100%; color: #fff;"><div style="background: ' . trim($this->conf['backgroundColor']) . '; padding: 1% 5%; width: 65%; margin: 0 auto;">'.$popup.'</div></div>';
					$ret['script'] .= "
						jQuery('#wpfp_".$hash."').bind('finish', function() {          
							jQuery('#wpfp_".$hash."_custom_popup').show();            
						});    
					";                   
				}
			}
	
			$show_ad = false;
			// if allowed by configuration file, set the popup box js code and content
			if(
				(
					( isset($this->conf['ad']) ) && strlen(trim($this->conf['ad'])) ||
					( isset($args['ad']) && !empty($args['ad']) )
				) 
				&&
				!strlen($args['ad_skip'])				
			) {
				if (isset($args['ad']) && !empty($args['ad'])) {
					$ad = html_entity_decode( str_replace('&#039;',"'", trim($args['ad']) ) );
					$ad_width = ( isset($args['ad_width']) ) ? $args['ad_width'].'px' : '60%';	
					$ad_height = ( isset($args['ad_height']) ) ? $args['ad_height'].'px' : '';					
				}
				else {
					$ad = trim($this->conf['ad']);			
					$ad_width = ( isset($this->conf['ad_width']) && $this->conf['ad_width'] ) ? $this->conf['ad_width'].'px' : '60%';	
					$ad_height = ( isset($this->conf['ad_height']) && $this->conf['ad_height'] ) ? $this->conf['ad_height'].'px' : '';
				}
				
				$ad = apply_filters( 'fv_flowplayer_ad_html', $ad);
				if( strlen(trim($ad)) > 0 ) {			
					$show_ad = true;
					$ad_contents = "\t<div id='wpfp_".$hash."_ad' class='wpfp_custom_ad'>\n\t\t<div class='wpfp_custom_ad_content' style='background: ".trim($this->conf['backgroundColor'])."; max-width: $ad_width; max-height: $ad_height; margin: 0 auto; position: relative'>\n\t\t<div class='fv_fp_close'><a href='#' onclick='jQuery(\"#wpfp_".$hash."_ad\").fadeOut(); return false'></a></div>\n\t\t\t".$ad."\n\t\t</div>\n\t</div>\n";                  
				}
			}			
			
			$show_splashend = false;
			if (isset($args['splashend']) && $args['splashend'] == 'show' && isset($args['splash']) && !empty($args['splash'])) {      
				$show_splashend = true;
				$splashend_contents = '<div id="wpfp_'.$hash.'_custom_background" class="wpfp_custom_background" style="display: none; position: absolute; background: url('.$splash_img.') no-repeat center center; background-size: contain; width: 100%; height: 100%; z-index: 1;"></div>';
			}	
			
			//  change engine for IE9 and 10
			
			if( $this->conf['engine'] == 'default' ) {
				$ret['script'] .= "
					if( jQuery.browser.msie && parseInt(jQuery.browser.version, 10) >= 9 ) {
						jQuery('#wpfp_".$hash."').attr('data-engine','flash');
					}
					";
			}
			
			if( current_user_can('manage_options') && $this->ajax_count < 10 && $this->conf['videochecker'] != 'off' ) {
				$this->ajax_count++;
				$test_media = array();
				$rtmp_test = ( isset($args['rtmp']) ) ? $args['rtmp'].$args['rtmp_path'] : $rtmp;
				foreach( array( $media, $src1, $src2, $rtmp_test ) AS $media_item ) {
					if( $media_item ) {
						$test_media[] = $media_item;
						break;
					} 
				}   
				
				if( $this->conf['videochecker'] == 'enabled' ) {
					$pre_notice = "jQuery('#wpfp_".$hash."').append('<div id=\"wpfp_notice_".$hash."\" class=\"fv-wp-flowplayer-notice-small\"><small>Admin note: Checking the video file...</small></div>');";
				}
				
				if( isset($test_media) && count($test_media) > 0 ) { 
					$ret['script'] .= "
						jQuery(document).ready( function() { 
							var ajaxurl = '".site_url()."/wp-admin/admin-ajax.php';
							$pre_notice
							jQuery.post( ajaxurl, { action: 'fv_wp_flowplayer_check_mimetype', media: '".json_encode($test_media)."', hash: '".$hash."' }, function( response ) {
								var obj;
								try {
									obj = jQuery.parseJSON( response );
									
									var extra_class = ( obj[1] > 0 ) ? ' fv-wp-flowplayer-error' : ' fv-wp-flowplayer-ok';
									if( obj[1] == 0 && obj[2] > 0 ) {
										extra_class = '';
									}
									jQuery('#wpfp_notice_".$hash."').remove();
									jQuery('#wpfp_".$hash."').append('<div id=\"wpfp_notice_".$hash."\" class=\"fv-wp-flowplayer-notice-small'+extra_class+'\">'+obj[0]+'</div>');						 			             
								} catch(e) {
									jQuery('#wpfp_notice_".$hash."').html('<p>Admin: Error parsing JSON</p>');
									return;
								}
	
							} );             
						} );
					";
				}
			}
			
			//  proper fallback on error?
			/*$ret['script'] .= "jQuery('#wpfp_".$hash."').bind('error', function(e, api, error) {
				if( error.code == 4 ) {
					jQuery('#wpfp_".$hash."').attr('data-engine','flash');
				}
			} );";*/    
			
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
			$ret['script'] .= "});";    
			
			$attributes = array();
			$attributes['class'] = 'flowplayer';
			if ($autoplay == 'false') {
				$attributes['class'] .= ' is-splash';
			}
			if ($controlbar == 'show') {
				$attributes['class'] .= ' fixed-controls';
			} 
			
			if( isset($args['align']) ) {
				if( $args['align'] == 'left' ) {
					$attributes['class'] .= ' alignleft';
				} else if( $args['align'] == 'right' ) {
					$attributes['class'] .= ' alignright';
				} else if( $args['align'] == 'center' ) {
					$attributes['class'] .= ' aligncenter';
				} 
			}
			
			if( $this->conf['engine'] == 'flash' || $args['engine'] == 'flash' ) {
				$attributes['data-engine'] = 'flash';
			}
			
			if( $args['embed'] == 'false' || $args['embed'] == 'true' ) {
				$attributes['data-embed'] = $args['embed'];
			}
			
			if( $this->conf['responsive'] == 'fixed' ) {
				$attributes['style'] = 'width: ' . $width . 'px; height: ' . $height . 'px';
			} else {
				$attributes['style'] = 'max-width: ' . $width . 'px; max-height: ' . $height . 'px';
			}
			
			$attributes['data-swf'] = RELATIVE_PATH.'/flowplayer/flowplayer.swf';
			
			if (isset($this->conf['googleanalytics']) && $this->conf['googleanalytics'] != 'false' && strlen($this->conf['googleanalytics']) > 0) {
				$attributes['data-analytics'] = $this->conf['googleanalytics'];
			}
			$commercial_key = false;
			if (isset($this->conf['key']) && $this->conf['key'] != 'false' && strlen($this->conf['key']) > 0) {
				$attributes['data-key'] = $this->conf['key'];
				$commercial_key = true;
			}
			if ($commercial_key && isset($this->conf['logo']) && $this->conf['logo'] != 'false' && strlen($this->conf['logo']) > 0) {
				$attributes['data-logo'] = $this->conf['logo'];
			}
			
			if( isset($args['rtmp']) && !empty($args['rtmp']) ) {
				$attributes['data-rtmp'] = trim( $args['rtmp'] );
			} else if( isset($rtmp) && !(isset($this->conf['rtmp']) && stripos($rtmp,$this->conf['rtmp']) !== false ) ) {
				$rtmp_info = parse_url($rtmp);
				if( isset($rtmp_info['host']) && strlen(trim($rtmp_info['host']) ) > 0 ) {
					$attributes['data-rtmp'] = 'rtmp://'.$rtmp_info['host'].'/cfx/st';
				}
			} else if( isset($this->conf['rtmp']) && $this->conf['rtmp'] != 'false' && strlen($this->conf['rtmp']) > 0 ) {				
				if( stripos( $this->conf['rtmp'], 'rtmp://' ) === 0 ) {
					$attributes['data-rtmp'] = $this->conf['rtmp'];
					$rtmp = str_replace( $this->conf['rtmp'], '', $rtmp );
				} else {
					$attributes['data-rtmp'] = 'rtmp://' . $this->conf['rtmp'] . '/cfx/st/';
				}
			}
			
			if (isset($this->conf['allowfullscreen']) && $this->conf['allowfullscreen'] == 'false') {
				$attributes['data-fullscreen'] = 'false';
			}       
			if ($width > $height) {
				$ratio = round($height / $width, 4);   
			}
			else if ($height > $width) {
				$ratio = round($width / $height, 4);
			}     
			$attributes['data-ratio'] = $ratio;
			if( $scaling == "fit" && $this->conf['responsive'] == 'fixed' ) {
				$attributes['data-flashfit'] .= 'true';
			}            
			
			$attributes_html = '';
			$attributes = apply_filters( 'fv_flowplayer_attributes', $attributes, $media );
			foreach( $attributes AS $attr_key => $attr_value ) {
				$attributes_html .= ' '.$attr_key.'="'.esc_attr( $attr_value ).'"';
			}
			
			$ret['html'] .= '<div id="wpfp_' . $hash . '"'.$attributes_html.'>'."\n";
			
			$ret['html'] .= "\t".'<video';      
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
				$ret['html'] .= ' preload="auto"';
			}
			else
			if ($autoplay == 'false') {
				$ret['html'] .= ' preload="none"';        
			}         
			$ret['html'] .= '>'."\n";
							
			if (!empty($media)) {              
				$ret['html'] .= "\t"."\t".$this->get_video_src($media, $mobileUserAgent)."\n";
			}
			if (!empty($src1)) {
				$ret['html'] .= "\t"."\t".$this->get_video_src($src1, $mobileUserAgent)."\n";
			}
			if (!empty($src2)) {
				$ret['html'] .= "\t"."\t".$this->get_video_src($src2, $mobileUserAgent)."\n";
			}
			if (!empty($mobile)) {
				$ret['script'] .= "\nfv_flowplayer_mobile_switch('wpfp_$hash')\n";
				$ret['html'] .= "\t"."\t".$this->get_video_src($mobile, $mobileUserAgent, 'wpfp_'.$hash.'_mobile')."<!--mobile-->\n";
			}			
	
			if( isset($rtmp) ) {
				$rtmp_url = parse_url($rtmp);
				/*var_dump($rtmp_url);
				$rtmp_url = explode('/', $rtmp_url['path'], 3);        
				$rtmp_file = $rtmp_url[count($rtmp_url)-1];*/
				$extension = $this->get_file_extension($rtmp_url['path'], null);
				if( $extension ) {
					$extension .= ':';
				}
				$ret['html'] .= "\t"."\t".'<source src="'.$extension.trim($rtmp_url['path'], " \t\n\r\0\x0B/").'" type="video/flash" />'."\n";
			}  
			
			if (isset($subtitles) && !empty($subtitles)) {
				$ret['html'] .= "\t"."\t".'<track src="'.esc_attr($subtitles).'" />'."\n";
			}     
			
			$ret['html'] .= "\t".'</video>'."\n";
			if( isset($splashend_contents) ) {
				$ret['html'] .= $splashend_contents;
			}
			if( isset($popup_contents) ) {
				$ret['html'] .= $popup_contents;  
			}
			if( isset($ad_contents) ) {
				$ret['html'] .= $ad_contents;  
			}			
			$ret['html'] .= '</div>'."\n";      
    
    } else {	//	$player_type == 'video' ends
    	global $fv_wp_flowplayer_ver;
    	
    	/*global $wp_scripts;
    	var_dump($wp_scripts);*/
    	
    	$this->load_mediaelement = true;
    	
    	$preload = ($autoplay == 'true') ? '' : ' preload="none"'; 
    	    	
    	$ret['script'] .= "jQuery('#wpfp_$hash audio').mediaelementplayer();\n";
    
    	$ret['html'] .= '<div id="wpfp_' . $hash . '" class="fvplayer fv-mediaelement">'."\n";			
			$ret['html'] .= "\t".'<audio src="'.$media.'" type="audio/'.$this->get_file_extension($media).'" controls="controls" width="'.$width.'"'.$preload.'></audio>'."\n";  
    	$ret['html'] .= '</div>'."\n"; 
    }
    
		return $ret;
	}
  
  function get_video_url($media) {
  	if( strpos($media,'rtmp://') !== false ) {
  		return null;
  	}
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
  
  function get_video_src($media, $mobileUserAgent, $id = '') {
  	if( $media ) { 
			$extension = $this->get_file_extension($media);
			//do not use https on mobile devices
			if (strpos($media, 'https') !== false && $mobileUserAgent) {
				$media = str_replace('https', 'http', $media);
			} 
			$id = ($id) ? 'id="'.$id.'" ' : '';
			return '<source '.$id.'src="'.trim($media).'" type="video/'.$extension.'" />';  
    }
    return null;
  }
  
  function get_file_extension($media, $default = 'flash' ) {
    $pathinfo = pathinfo( trim($media) );
    $extension = ( isset($pathinfo['extension']) ) ? $pathinfo['extension'] : false;       
    
		if( !$extension ) {
			return $default;
		}
 
    
    if ($extension == 'm4v') {
      $extension = 'mp4';
    } else if( $extension == 'mp3' ) {
    	$extension = 'mpeg';
    } else if( $extension == 'wav' ) {
    	$extension = 'wav';
    } else if( $extension == 'ogg' ) {
    	$extension = 'ogg';
    } else if( $extension == 'mov' ) {
      $extension = 'mp4';
    } else if( !in_array($extension, array('mp4', 'm4v', 'webm', 'ogv', 'mp3', 'ogg', 'wav')) ) {
      $extension = $default;  
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
