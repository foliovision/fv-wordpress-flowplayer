<?php
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
    
    $autoplay = 'false';
   	if( (isset($this->conf['autoplay']) && $this->conf['autoplay'] == 'true' && $args['autoplay'] != 'false' ) || (isset($args['autoplay']) && $args['autoplay'] == 'true') ) {
			$autoplay = 'true';
		}  
    
    //	decide which player to use
    foreach( array( $media, $src1, $src2 ) AS $media_item ) {
    	if( preg_match( '~\.(mp3|wav|ogg)~', $media_item ) ) {
				$player_type = 'audio';
				break;
			} 
			
	    global $post;
	    $fv_flowplayer_meta = get_post_meta( $post->ID, '_fv_flowplayer', true );
    	if( $fv_flowplayer_meta && isset($fv_flowplayer_meta[sanitize_title($media_item)]['time']) ) {
    		$this->expire_time = $fv_flowplayer_meta[sanitize_title($media_item)]['time'];
    	}
    }
    
    if( $player_type == 'video' ) {
    
			foreach( array( $media, $src1, $src2 ) AS $media_item ) {
				//if( ( strpos($media_item, 'amazonaws.com') !== false && stripos( $media_item, 'http://s3.amazonaws.com/' ) !== 0 && stripos( $media_item, 'https://s3.amazonaws.com/' ) !== 0  ) || stripos( $media_item, 'rtmp://' ) === 0 ) {  //  we are also checking amazonaws.com due to compatibility with older shortcodes
				if( stripos( $media_item, 'rtmp://' ) === 0 ) {
					$rtmp = $media_item;
				} 
							
				if( $this->conf['engine'] == 'false' && stripos( $media_item, '.m4v' ) !== false ) {
					$this->ret['script'] .= "
						if( jQuery.browser.mozilla && navigator.appVersion.indexOf(\"Win\")!=-1 ) {
							jQuery('#wpfp_".$this->hash."').attr('data-engine','flash');
						}
						";
				}
				
				if( $this->conf['engine'] == 'false' && preg_match( '~\.(mp4|m4v|mov)~', $media_item ) > 0 ) {
					$this->ret['script'] .= "
						var match = window.navigator.appVersion.match(/Chrome\/(\d+)\./);
						if( match != null ) {
							var chrome_ver = parseInt(match[1], 10);
							if(
								( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && chrome_ver < 28 && navigator.appVersion.indexOf(\"Win\")!=-1 ) || 
								( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && chrome_ver < 27 && navigator.appVersion.indexOf(\"Linux\")!=-1 )							
							) {
								jQuery('#wpfp_".$this->hash."').attr('data-engine','flash');
							}
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
				$this->ret['script'] .= "
					if( jQuery.browser.msie && parseInt(jQuery.browser.version, 10) >= 9 ) {
						jQuery('#wpfp_".$this->hash."').attr('data-engine','flash');
					}
					";
			}
			
			if( current_user_can('manage_options') && $this->ajax_count < 10 && $this->conf['disable_videochecker'] != 'true' ) {
				$this->ajax_count++;
				$test_media = array();
				$rtmp_test = ( isset($args['rtmp']) ) ? $args['rtmp'].$args['rtmp_path'] : $rtmp;
				foreach( array( $media, $src1, $src2, $rtmp_test ) AS $media_item ) {
					if( $media_item ) {
						$test_media[] = $this->get_amazon_secure( $media_item, $this );
						break;
					} 
				}   
				
				if( $this->conf['disable_videochecker'] == 'false' ) {
					$pre_notice = "jQuery('#wpfp_".$this->hash."').append('<div id=\"wpfp_notice_".$this->hash."\" class=\"fv-wp-flowplayer-notice-small\" title=\"This note is visible to logged-in admins only.\"><small>Admin note: Checking the video file...</small></div>');";
				}
				
				if( isset($test_media) && count($test_media) > 0 ) { 
					$this->ret['script'] .= "
						jQuery(document).ready( function() { 
							var ajaxurl = '".site_url()."/wp-admin/admin-ajax.php';
							$pre_notice
							jQuery.post( ajaxurl, { action: 'fv_wp_flowplayer_check_mimetype', media: '".json_encode($test_media)."', hash: '".$this->hash."' }, function( response ) {
								var obj;
								try {
									obj = jQuery.parseJSON( response );
									
									var extra_class = ( obj[1] > 0 ) ? ' fv-wp-flowplayer-error' : ' fv-wp-flowplayer-ok';
									if( obj[1] == 0 && obj[2] > 0 ) {
										extra_class = '';
									}
									jQuery('#wpfp_notice_".$this->hash."').remove();
									jQuery('#wpfp_".$this->hash."').append('<div id=\"wpfp_notice_".$this->hash."\" class=\"fv-wp-flowplayer-notice-small'+extra_class+'\" title=\"This note is visible to logged-in admins only.\">'+obj[0]+'</div>');						 			             
								} catch(e) {
									jQuery('#wpfp_notice_".$this->hash."').html('<p>Admin: Error parsing JSON</p>');
									return;
								}
	
							} );             
						} );
					";
				}
			}
			
			
			if ( !empty($redirect) ) {
				$this->ret['script'] .= "
				jQuery('#wpfp_".$this->hash."').bind('finish', function() { window.open('".$redirect."', '_blank'); } );";
			}

			
			$attributes = array();
			$attributes['class'] = 'flowplayer';
			
			if( isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true' ) {
				$this->autobuffer_count++;
			}
			if( 
				$this->autobuffer_count > apply_filters( 'fv_flowplayer_autobuffer_limit', 2 ) ||
				( $autoplay == 'false' && !( isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true' ) )				
			) {
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
			
			if( $args['embed'] == 'false' || $args['embed'] == 'true' ) {
				$attributes['data-embed'] = $args['embed'];
			}
			
			if( $this->conf['fixed_size'] == 'true' ) {
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
			
				$playlist_items_html = array();
				if( count($playlist_items) > 0 ) {
					
					$playlist_items_html[] = "\t\t<a ".( (isset($splash_img) && !empty($splash_img)) ? "style='background: url(\"".$splash_img."\") center center' " : "" )."href='".$this->get_video_src( $media, $mobileUserAgent, null, $rtmp, true )."'></a>\n";
					foreach( $playlist_items AS $playlist_item ) {
					
						$playlist_item = explode( ',', $playlist_item );
						if( count($playlist_item) == 2 ) {
							$media_item = str_replace( $playlist_replace_to, $playlist_replace_from, $playlist_item[0] );
							$meta_item = str_replace( $playlist_replace_to, $playlist_replace_from, $playlist_item[1] );		
							if( $meta_item == 'preroll' ) {
								$is_preroll = $media_item;
							} else {
								$playlist_items_html[] = "\t\t<a style='background: url(\"".$meta_item."\") center center' href='".trim($media_item)."'></a>\n";
							}
						} else {
							$playlist_item = str_replace( $playlist_replace_to, $playlist_replace_from, $playlist_item[0] );
							$playlist_items_html[] = "\t\t<a href='".trim($playlist_item)."'></a>\n";
						}
						
					}
					
					if( !$is_preroll || count($playlist_items) > 1 ) {	//	only show controls if the item is not preroll
						$playlist = "\t<a class='fp-prev'></a> <a class='fp-next'></a>\n"."\t<div class='fp-playlist'>\n".implode( '', $playlist_items_html )."\t</div>\n";
						$attributes['class'] .= ' has-playlist';
					} else {
						//	do stuff with $media, $src1, $src2
						$this->ret['script'] .= "
						jQuery('#wpfp_".$this->hash."').bind('finish', function (e,api, error) {
							jQuery('#wpfp_".$this->hash."').flowplayer().load( [ {mp4: '$media'} ] )
						} );
						";
						
						// put ad in as the video source
						$media = $media_item;
					}
				}
			}			
			
			
			$attributes_html = '';
			$attributes = apply_filters( 'fv_flowplayer_attributes', $attributes, $media );
			foreach( $attributes AS $attr_key => $attr_value ) {
				$attributes_html .= ' '.$attr_key.'="'.esc_attr( $attr_value ).'"';
			}
			
			$this->ret['html'] .= '<div id="wpfp_' . $this->hash . '"'.$attributes_html.'>'."\n";
			
			
			
			$this->ret['html'] .= "\t".'<video';      
			if (isset($splash_img) && !empty($splash_img)) {
				$this->ret['html'] .= ' poster="'.str_replace(' ','%20',$splash_img).'"';
			} 
			if( $autoplay == 'true' && $this->autoplay_count < apply_filters( 'fv_flowplayer_autoplay_limit', 1 ) ) {
				$this->ret['html'] .= ' autoplay';  
				$this->autoplay_count++;
			}
			if (isset($args['loop']) && $args['loop'] == 'true') {
				$this->ret['html'] .= ' loop';
				$this->ret['script'] .= "
					jQuery('#wpfp_".$this->hash."').bind('finish', function() {          
						jQuery('#wpfp_".$this->hash."').flowplayer().play();       
					});    
				";   				
			}     
			if( isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true' && $this->autobuffer_count < apply_filters( 'fv_flowplayer_autobuffer_limit', 2 )) {
				$this->ret['html'] .= ' preload="auto"';
				$this->ret['html'] .= ' id="wpfp_'.$this->hash.'_video"';
			}	else if ($autoplay == 'false') {
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
				if (isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true') {
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
		
				$this->ret['script'] .= "
					jQuery('#wpfp_$this->hash').bind('error', function (e,api, error) {
						if( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && error != null && ( error.code == 3 || error.code == 4 || error.code == 5 ) ) {							
							api.unload();
							var html = jQuery('<div />').append(jQuery('#wpfp_$this->hash .wpfp_custom_popup').clone()).html();
							html += jQuery('<div />').append(jQuery('#wpfp_$this->hash .wpfp_custom_ad').clone()).html();								
							
							jQuery('#wpfp_$this->hash').attr('id','bad_wpfp_$this->hash');					
							jQuery('#bad_wpfp_$this->hash').after( '<div id=\"wpfp_$this->hash\" $attributes_html data-engine=\"flash\">'+html+'</div>' );
							jQuery('#wpfp_$this->hash').flowplayer({ playlist: [ [ {mp4: \"$mp4_video\"} ] ] });
							".$tmp->ret['script']."
				";				
				if (isset($this->conf['auto_buffer']) && $this->conf['auto_buffer'] == 'true') {
					$this->ret['script'] .= "jQuery('#wpfp_$this->hash').bind('ready', function(e, api) { api.play(); } ); ";
				} else {
					$this->ret['script'] .= "jQuery('#wpfp_$this->hash').flowplayer().play(0); ";
				}
				$this->ret['script'] .= "jQuery('#bad_wpfp_$this->hash').remove();						
						}
					});					
				";				
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
				/*var_dump($rtmp_url);
				$rtmp_url = explode('/', $rtmp_url['path'], 3);        
				$rtmp_file = $rtmp_url[count($rtmp_url)-1];*/
				$extension = $this->get_file_extension($rtmp_url['path'], null);
				if( $extension ) {
					$extension .= ':';
				}
				$this->ret['html'] .= "\t"."\t".'<source src="'.$extension.trim($rtmp_url['path'], " \t\n\r\0\x0B/").'" type="video/flash" />'."\n";
			}  
			
			if (isset($subtitles) && !empty($subtitles)) {
				$this->ret['html'] .= "\t"."\t".'<track src="'.esc_attr($subtitles).'" />'."\n";
			}     
			
			$this->ret['html'] .= "\t".'</video>'."\n";

			
			$this->ret['html'] .= $playlist;
			
			
			if( isset($splashend_contents) ) {
				$this->ret['html'] .= $splashend_contents;
			}
			if( isset($popup_contents) ) {
				$this->ret['html'] .= $popup_contents;  
			}
			if( isset($ad_contents) ) {
				$this->ret['html'] .= $ad_contents;  
			}			
			$this->ret['html'] .= '</div>'."\n".$html_after.$scripts_after;      
    
    } else {	//	$player_type == 'video' ends
    	global $fv_wp_flowplayer_ver;
    	
    	/*global $wp_scripts;
    	var_dump($wp_scripts);*/
    	
    	$this->load_mediaelement = true;
    	
    	$preload = ($autoplay == 'true') ? '' : ' preload="none"'; 
    	    	
    	$this->ret['script'] .= "jQuery('#wpfp_{$this->hash} audio').mediaelementplayer();\n";
    
    	$this->ret['html'] .= '<div id="wpfp_' . $this->hash . '" class="fvplayer fv-mediaelement">'."\n";			
			$this->ret['html'] .= "\t".'<audio src="'.$media.'" type="audio/'.$this->get_file_extension($media).'" controls="controls" width="'.$width.'"'.$preload.'></audio>'."\n";  
    	$this->ret['html'] .= '</div>'."\n".$scripts_after; 
    }
    
    $this->ret['script'] = apply_filters( 'fv_flowplayer_scripts', $this->ret['script'], 'wpfp_' . $this->hash, $media );
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
    $extension = preg_replace( '~\?.+$~', '', $extension );
    
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
    } else if( $extension == 'mov' ) {
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
