<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
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

/**
 * Extension of original flowplayer class intended for frontend.
 */
class flowplayer_frontend extends flowplayer
{

	var $ajax_count = 0;
	
	var $autobuffer_count = 0;	
	
	var $autoplay_count = 0;
	
	var $expire_time = 0;

	/**
	 * Builds the HTML and JS code of single flowplayer instance on a page/post.
	 * @param string $media URL or filename (in case it is in the /videos/ directory) of video file to be played.
	 * @param array $args Array of arguments (name => value).
	 * @return Returns array with 2 elements - 'html' => html code displayed anywhere on page/post, 'script' => javascript code displayed before </body> tag
	 */
	function build_min_player($media,$args = array()) {
		global $post;
				
		// unique coe for this player
		$this->hash = md5($media.$this->_salt());    
		$player_type = 'video';
		$rtmp = false;
		$youtube = false;
		$vimeo = false;		
    
		// returned array with new player's html and javascript content
		$this->ret = array('html' => '', 'script' => ' ');	//	note: we need the white space here, it fails to add into the string on some hosts without it (???)
		$html_after = '';
		$scripts_after = '';
      
    
		// set common variables
		$width = ( isset($this->conf['width']) && (!empty($this->conf['width'])) && intval($this->conf['width']) > 0 ) ? $this->conf['width'] : 320;
		$height = ( isset($this->conf['height']) && (!empty($this->conf['height'])) && intval($this->conf['height']) > 0 ) ? $this->conf['height'] : 240;
		if (isset($args['width'])&&!empty($args['width'])) $width = trim($args['width']);
		if (isset($args['height'])&&!empty($args['height'])) $height = trim($args['height']);		
		        
		$src1 = ( isset($args['src1']) && !empty($args['src1']) ) ? trim($args['src1']) : false;
		$src2 = ( isset($args['src2']) && !empty($args['src2']) ) ? trim($args['src2']) : false;  
		$mobile = ( isset($args['mobile']) && !empty($args['mobile']) ) ? trim($args['mobile']) : false;  
    
		$autoplay = false;
		if( isset($this->conf['autoplay']) && $this->conf['autoplay'] == 'true' && $args['autoplay'] != 'false'  ) {
			$this->autobuffer_count++;
			if( $this->autobuffer_count < apply_filters( 'fv_flowplayer_autobuffer_limit', 2 ) ) {
				$autoplay = true;
			}
		}  
		if( isset($args['autoplay']) && $args['autoplay'] == 'true') {
			$this->autobuffer_count++;
			$autoplay = true;
		}
    
		//	decide which player to use
		foreach( array( $media, $src1, $src2 ) AS $media_item ) {
			if( preg_match( '~\.(mp3|wav|ogg)([?#].*?)?$~', $media_item ) ) {
					$player_type = 'audio';
					break;
				} 
				
			global $post;
			$fv_flowplayer_meta = get_post_meta( $post->ID, '_fv_flowplayer', true );
			if( $fv_flowplayer_meta && isset($fv_flowplayer_meta[sanitize_title($media_item)]['time']) ) {
				$this->expire_time = $fv_flowplayer_meta[sanitize_title($media_item)]['time'];
			}
		}
    
		if( preg_match( "~(youtu\.be/|youtube\.com/(watch\?(.*&)?v=|(embed|v)/))([^\?&\"'>]+)~i", $media, $aYoutube ) ) {
			if( isset($aYoutube[5]) ) {
				$youtube = $aYoutube[5];
				$player_type = 'youtube';
			}
		} else if( preg_match( "~^[a-zA-Z0-9-_]{11}$~", $media, $aYoutube ) ) {
			if( isset($aYoutube[0]) ) {
				$youtube = $aYoutube[0];
				$player_type = 'youtube';
			}
		}

		if( preg_match( "~vimeo.com/(?:video/|moogaloop\.swf\?clip_id=)?(\d+)~i", $media, $aVimeo ) ) {
			if( isset($aVimeo[1]) ) {
				$vimeo = $aVimeo[1];
				$player_type = 'vimeo';
			}
		} else if( preg_match( "~^[0-9]{8}$~", $media, $aVimeo ) ) {
			if( isset($aVimeo[0]) ) {
				$vimeo = $aVimeo[0];
				$player_type = 'vimeo';
			}
		}    
    
		if( $player_type == 'video' ) {
		
				foreach( array( $media, $src1, $src2 ) AS $media_item ) {
					//if( ( strpos($media_item, 'amazonaws.com') !== false && stripos( $media_item, 'http://s3.amazonaws.com/' ) !== 0 && stripos( $media_item, 'https://s3.amazonaws.com/' ) !== 0  ) || stripos( $media_item, 'rtmp://' ) === 0 ) {  //  we are also checking amazonaws.com due to compatibility with older shortcodes
					if( stripos( $media_item, 'rtmp://' ) === 0 ) {
						$rtmp = $media_item;
					} 
								
					if( $this->conf['engine'] == 'false' && stripos( $media_item, '.m4v' ) !== false ) {
						$this->ret['script'] .= "fv_flowplayer_browser_ff_m4v('".$this->hash."')\n";
					}
					
					if( $this->conf['engine'] == 'false' && preg_match( '~\.(mp4|m4v|mov)~', $media_item ) > 0 ) {
						$this->ret['script'] .= "fv_flowplayer_browser_chrome_mp4('".$this->hash."');\n";
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
						$popup_contents = '<div id="wpfp_'.$this->hash.'_custom_popup" class="wpfp_custom_popup"><div class="wpfp_custom_popup_content">'.$popup.'</div></div>';                
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
						$ad_contents = "\t<div id='wpfp_".$this->hash."_ad' class='wpfp_custom_ad'>\n\t\t<div class='wpfp_custom_ad_content' style='max-width: $ad_width; max-height: $ad_height; '>\n\t\t<div class='fv_fp_close'><a href='#' onclick='jQuery(\"#wpfp_".$this->hash."_ad\").fadeOut(); return false'></a></div>\n\t\t\t".$ad."\n\t\t</div>\n\t</div>\n";                  
					}
				}			
				
				$show_splashend = false;
				if (isset($args['splashend']) && $args['splashend'] == 'show' && isset($args['splash']) && !empty($args['splash'])) {      
					$show_splashend = true;
					$splashend_contents = '<div id="wpfp_'.$this->hash.'_custom_background" class="wpfp_custom_background" style="position: absolute; background: url(\''.$splash_img.'\') no-repeat center center; background-size: contain; width: 100%; height: 100%; z-index: 1;"></div>';
				}	
				
				//  change engine for IE9 and 10
				
				if( $this->conf['engine'] == 'false' ) {
					$this->ret['script'] .= "fv_flowplayer_browser_ie( '".$this->hash."' );\n";
				}
				
				if( current_user_can('manage_options') && $this->ajax_count < 10 && $this->conf['disable_videochecker'] != 'true' ) {
					$this->ajax_count++;
					$test_media = array();
					$rtmp_test = ( isset($args['rtmp']) ) ? $args['rtmp'].$args['rtmp_path'] : $rtmp;
					foreach( array( $media, $src1, $src2, $rtmp_test ) AS $media_item ) {
						if( $media_item ) {
							$test_media[] = $this->get_amazon_secure( $media_item, $this );
							//break;
						} 
					}   
					
					if( isset($test_media) && count($test_media) > 0 ) { 
						$this->ret['script'] .= "fv_flowplayer_admin_test_media( '".$this->hash."', '".json_encode($test_media)."' );\n";
					}
				}
				
				
				if ( !empty($redirect) ) {
					$this->ret['script'] .= "fv_flowplayer_redirect( '".$this->hash."', '".$redirect."')\n";
				}
	
				
				$attributes = array();
				$attributes['class'] = 'flowplayer';
				
				if( $autoplay == false ) {
					$attributes['class'] .= ' is-splash';
				}
				
				if( $controlbar == 'show' ) {
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
				
				if( $this->conf['engine'] == 'true' || $args['engine'] == 'flash' ) {
					$attributes['data-engine'] = 'flash';
				}
				
				if( $args['embed'] == 'false' || ( $this->conf['disableembedding'] == 'true' && $args['embed'] != 'true' ) ) {
					$attributes['data-embed'] = 'false';
				}
				
				if( $this->conf['fixed_size'] == 'true' ) {
					$attributes['style'] = 'width: ' . $width . 'px; height: ' . $height . 'px; ';
				} else {
					$attributes['style'] = 'max-width: ' . $width . 'px; max-height: ' . $height . 'px; ';
				}
				
				$attributes['data-swf'] = FV_FP_RELATIVE_PATH.'/flowplayer/flowplayer.swf';
				
				if (isset($this->conf['googleanalytics']) && $this->conf['googleanalytics'] != 'false' && strlen($this->conf['googleanalytics']) > 0) {
					$attributes['data-analytics'] = $this->conf['googleanalytics'];
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
	
				$ratio = round($height / $width, 4);   
	
				$attributes['data-ratio'] = $ratio;
				if( $scaling == "fit" && $this->conf['fixed_size'] == 'fixed' ) {
					$attributes['data-flashfit'] = 'true';
				}            
				
				$playlist = '';
				$is_preroll = false;
				if( isset($args['playlist']) && strlen(trim($args['playlist'])) > 0 ) {
					$playlist_replace_from = array('&amp;','\;', '\,');				
					$playlist_replace_to = array('<!--amp-->','<!--semicolon-->','<!--comma-->');				
					$args['playlist'] = str_replace( $playlist_replace_from, $playlist_replace_to, $args['playlist'] );			
					$playlist_items = explode( ';', $args['playlist'] );
				
					$aPlaylistItems = array();
					if( count($playlist_items) > 0 ) {					
						$aPlaylistItem = array();
						$playlist_items_external_html = array();
						foreach( array( $media, $src1, $src2, $rtmp ) AS $media_item ) {
							if( !$media_item ) continue;
							$aPlaylistItem[] = array( $this->get_file_extension($media_item) => $this->get_video_src( $media_item, false, false, false, true ) );
											
						}							
						$aPlaylistItems[] = $aPlaylistItem;
						$playlist_items_external_html[] = "\t\t<a class='is-active' ".( (isset($splash_img) && !empty($splash_img)) ? "style='background-image: url(\"".$splash_img."\")' " : "" )."onclick='return false'></a>\n";
						
						foreach( $playlist_items AS $iKey => $sPlaylist_item ) {
							$aPlaylist_item = explode( ',', $sPlaylist_item );
							$aPlaylistItem = array();
							$sSplashImage = false;
							foreach( $aPlaylist_item AS $aPlaylist_item_i ) {
								if( preg_match('~\.(png|gif|jpg|jpe|jpeg)($|\?)~',$aPlaylist_item_i) ) {
									$sSplashImage = $aPlaylist_item_i;
									continue;
								}
								$aPlaylist_item_i = preg_replace( '~^rtmp:~', '', $aPlaylist_item_i );						
								$aPlaylistItem[] = array( $this->get_file_extension($aPlaylist_item_i) => $aPlaylist_item_i ); 
							}
							$aPlaylistItems[] = $aPlaylistItem;
							if( $sSplashImage ) {
								$playlist_items_external_html[] = "\t\t<a style='background-image: url(\"".$sSplashImage."\")' onclick='return false'></a>\n";
							} else {
								$playlist_items_external_html[] = "\t\t<a onclick='return false'></a>\n";
							}
						}
	
						$jsonPlaylistItems = str_replace( array('\\/', ','), array('/', ",\n\t\t"), json_encode($aPlaylistItems) );
						$jsonPlaylistItems = preg_replace( '~"(.*)":"~', '$1:"', $jsonPlaylistItems );
		
						$html_after .= "\t<div class='fp-playlist-external' rel='wpfp_{$this->hash}'>\n".implode( '', $playlist_items_external_html )."\t</div>\n";
						$scripts_after .= "<script>jQuery('#wpfp_{$this->hash}').flowplayer( {\n\tplaylist: \n\t\t{$jsonPlaylistItems}";
						/*if( isset($attributes['data-rtmp']) ) {
							$scripts_after .= ",\n\trtmp: '{$attributes['data-rtmp']}'";
						}*/
						//$scripts_after .= ",\n\tautoplay: 'autoplay'";
						$scripts_after .= "} );</script>\n";
						if( $autoplay ) {
						
						}
						$attributes['style'] .= "background-image: url({$splash_img});";
					    if( $autoplay ) {
						    $this->ret['script'] .= "fv_flowplayer_autoplay( '".$this->hash."' );\n";			
						}
					}
				}			
				
				
				$attributes_html = '';
				$attributes = apply_filters( 'fv_flowplayer_attributes', $attributes, $media );
				foreach( $attributes AS $attr_key => $attr_value ) {
					$attributes_html .= ' '.$attr_key.'="'.esc_attr( $attr_value ).'"';
				}
				
				$this->ret['html'] .= '<div id="wpfp_' . $this->hash . '"'.$attributes_html.'>'."\n";
				
				if (isset($args['loop']) && $args['loop'] == 'true') {
					$this->ret['script'] .= "fv_flowplayer_loop( '".$this->hash."' );\n";			
				}
				
				if( count($aPlaylistItems) == 0 ) {	// todo: this stops subtitles, mobile video, preload etc.
					$this->ret['html'] .= "\t".'<video';      
					if (isset($splash_img) && !empty($splash_img)) {
						$this->ret['html'] .= ' poster="'.str_replace(' ','%20',$splash_img).'"';
					} 
					if( $autoplay == true ) {
						$this->ret['html'] .= ' autoplay';  
					}
					if (isset($args['loop']) && $args['loop'] == 'true') {
						$this->ret['html'] .= ' loop';
								
					}     
					if( isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true' && $this->autobuffer_count < apply_filters( 'fv_flowplayer_autobuffer_limit', 2 )) {
						$this->ret['html'] .= ' preload="auto"';
						$this->ret['html'] .= ' id="wpfp_'.$this->hash.'_video"';
					}	else if ($autoplay == false) {
						$this->ret['html'] .= ' preload="none"';        
					}        
						
					$count = $mp4_position = $webm_position = 0;
					$mp4_video = false;
					foreach( array( $media, $src1, $src2 ) AS $media_item ) {
						$count++;
						if( preg_match( '~\.(mp4|mov|m4v)~', $media_item ) ) {
							$mp4_position = $count;
							$mp4_video = $media_item;
						} else if( preg_match( '~\.webm~', $media_item ) ) {
							$webm_position = $count;
						} 					
					}			
					
					if( $mp4_position > $webm_position ) {
						if (isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true' && $this->autobuffer_count < apply_filters( 'fv_flowplayer_autobuffer_limit', 2 )) {
							$scripts_after .= '<script type="text/javascript">
								if( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) )  {
									document.getElementById("wpfp_'.$this->hash.'_video").setAttribute("preload", "none");
								}
								</script>					
							';
						}						
							
						//	tricky way of moving over the error handler
						$tmp = $this;
						$mp4_video = $this->get_amazon_secure( $mp4_video, $tmp );	
				
						$this->ret['script'] .= "fv_flowplayer_browser_chrome_fail( '".$this->hash."', '".addslashes( str_replace("\n"," ",$tmp->ret['script']) )."', '".$attributes_html."', '".$mp4_video."', '".( (isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true') ? "true" : "false" )."');\n";
					}
					 
					$this->ret['html'] .= '>'."\n";
									
					if (!empty($media)) {              
						$this->ret['html'] .= "\t"."\t".$this->get_video_src($media, $mobileUserAgent, null, $rtmp)."\n";
					}
					if (!empty($src1)) {
						$this->ret['html'] .= "\t"."\t".$this->get_video_src($src1, $mobileUserAgent, null, $rtmp)."\n";
					}
					if (!empty($src2)) {
						$this->ret['html'] .= "\t"."\t".$this->get_video_src($src2, $mobileUserAgent, null, $rtmp)."\n";
					}
					if (!empty($mobile)) {
						$this->ret['script'] .= "\nfv_flowplayer_mobile_switch('wpfp_{$this->hash}')\n";
						$this->ret['html'] .= "\t"."\t".$this->get_video_src($mobile, $mobileUserAgent, 'wpfp_'.$this->hash.'_mobile', $rtmp)."<!--mobile-->\n";
					}			
			
					if( isset($rtmp) && !empty($rtmp) ) {
						$rtmp_url = parse_url($rtmp);
						$rtmp_file = $rtmp_url['path'] . ( ( strlen($rtmp_url['query']) ) ? '?'. str_replace( '&amp;', '&', $rtmp_url['query'] ) : '' );
		
						$extension = $this->get_file_extension($rtmp_url['path'], null);
						if( $extension ) {
							$extension .= ':';
						}
						$this->ret['html'] .= "\t"."\t".'<source src="'.$extension.trim($rtmp_file, " \t\n\r\0\x0B/").'" type="video/flash" />'."\n";
					}  
					
					if (isset($subtitles) && !empty($subtitles)) {
						$this->ret['html'] .= "\t"."\t".'<track src="'.esc_attr($subtitles).'" />'."\n";
					}     
					
					$this->ret['html'] .= "\t".'</video>'."\n";
				}
	
				
				//$this->ret['html'] .= $playlist;	//	todo: is this needed for next and prev arrows?
				
				
				if( isset($splashend_contents) ) {
					$this->ret['html'] .= $splashend_contents;
				}
				if( isset($popup_contents) ) {
					$this->ret['html'] .= $popup_contents;  
				}
				if( isset($ad_contents) ) {
					$this->ret['html'] .= $ad_contents;  
				}			
				$this->ret['html'] .= '</div>'."\n";
	
				$this->ret['html'] .= $html_after.$scripts_after;      
		
		} else if( $player_type == 'youtube' ) {
				
			$sAutoplay = ($autoplay) ? 'autoplay=1&amp;' : '';
			$this->ret['html'] .= "<iframe id='fv_ytplayer_{$this->hash}' type='text/html' width='{$width}' height='{$height}'
	  src='http://www.youtube.com/embed/$youtube?{$sAutoplay}origin=".urlencode(get_permalink())."' frameborder='0'></iframe>\n";
			
		} else if( $player_type == 'vimeo' ) {
		
			$sAutoplay = ($autoplay) ? " autoplay='1'" : "";
			$this->ret['html'] .= "<iframe id='fv_vimeo_{$this->hash}' src='//player.vimeo.com/video/{$vimeo}' width='{$width}' height='{$height}' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen{$sAutoplay}></iframe>\n";
			
		} else {	//	$player_type == 'video' ends
			global $fv_wp_flowplayer_ver;
			
			/*global $wp_scripts;
			var_dump($wp_scripts);*/
			
			$this->load_mediaelement = true;
			
			$preload = ($autoplay == true) ? '' : ' preload="none"'; 
					
			$this->ret['script'] .= "jQuery('#wpfp_{$this->hash} audio').mediaelementplayer();\n";
			$this->ret['html'] .= '<div id="wpfp_' . $this->hash . '" class="fvplayer fv-mediaelement">'."\n";			
				$this->ret['html'] .= "\t".'<audio src="'.$media.'" type="audio/'.$this->get_file_extension($media).'" controls="controls" width="'.$width.'"'.$preload.'></audio>'."\n";  
			$this->ret['html'] .= '</div>'."\n";
			$this->ret['html'] .= $scripts_after; 
		}
		
		$this->ret['script'] = apply_filters( 'fv_flowplayer_scripts', $this->ret['script']."\n", 'wpfp_' . $this->hash, $media );
		return $this->ret;
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
  
  function get_video_src($media, $mobileUserAgent, $id = '', $rtmp = false, $url_only = false ) {
  	if( $media ) { 
			$extension = $this->get_file_extension($media);
			//do not use https on mobile devices
			if (strpos($media, 'https') !== false && $mobileUserAgent) {
				$media = str_replace('https', 'http', $media);
			}
			$id = ($id) ? 'id="'.$id.'" ' : '';
	
			$media = $this->get_amazon_secure( $media, $this );	
			
			//	fix for signed Amazon URLs, we actually need it for Flash only, so it gets into an extra source tag
			$source_flash_encoded = false;	
			if( $this->is_secure_amazon_s3($media) /*&& stripos($media,'.webm') === false && stripos($media,'.ogv') === false */) {
					$media_fixed = str_replace('%2B', '%25252B',$media);   
					//	only if there was a change and we don't have an RTMP for Flash
					if( $media_fixed != $media && empty($rtmp) ) {
						$source_flash_encoded = "\n\t\t".'<source '.$id.'src="'.trim($media_fixed).'" type="video/flash" />';				
					}
			}
			
			$url_parts = parse_url( ($source_flash_encoded) ? $source_flash_encoded : $media );					
			if( stripos( $url_parts['path'], '+' ) !== false ) {

				if( !empty($url_parts['path']) ) {
						$url_parts['path'] = join('/', array_map('rawurlencode', explode('/', $url_parts['path'])));
				}
				if( !empty($url_parts['query']) ) {
						//$url_parts['query'] = str_replace( '&amp;', '&', $url_parts['query'] );				
				}
				
				$media_fixed = http_build_url( ($source_flash_encoded) ? $source_flash_encoded : $media, $url_parts);
				$source_flash_encoded = "\n\t\t".'<source '.$id.'src="'.trim($media_fixed).'" type="video/flash" />';
			}
			
			if( $url_only ) {
				return trim($media);
			} else {
				return '<source '.$id.'src="'.trim($media).'" type="video/'.$extension.'" />'.$source_flash_encoded;  
			}
    }
    return null;
  }
  
  
  function get_file_extension($media, $default = 'flash' ) {
    $pathinfo = pathinfo( trim($media) );

    $extension = ( isset($pathinfo['extension']) ) ? $pathinfo['extension'] : false;       
    $extension = preg_replace( '~[?#].+$~', '', $extension );
    
		if( !$extension ) {
			return $default;
		}
 
    
    if ($extension == 'm3u8' || $extension == 'm3u') {
      $extension = 'x-mpegurl';
    } else if ($extension == 'm4v') {
      $extension = 'mp4';
    } else if( $extension == 'mp3' ) {
    	$extension = 'mpeg';
    } else if( $extension == 'wav' ) {
    	$extension = 'wav';
    } else if( $extension == 'ogg' ) {
    	$extension = 'ogg';
    } else if( $extension == 'ogv' ) {
    	$extension = 'ogg';
    } else if( $extension == 'mov' ) {
      $extension = 'mp4';
    } else if( $extension == '3gp' ) {
      $extension = 'mp4';      
    } else if( !in_array($extension, array('mp4', 'm4v', 'webm', 'ogv', 'mp3', 'ogg', 'wav', '3gp')) ) {
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
