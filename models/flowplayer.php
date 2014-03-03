<?php
/*  FV Folopress Base Class - set of useful functions for Wordpress plugins    
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 

require_once( dirname(__FILE__) . '/../includes/fp-api.php' );

class flowplayer extends FV_Wordpress_Flowplayer_Plugin {
	private $count = 0;
	/**
	 * Relative URL path
	 */
	const FV_FP_RELATIVE_PATH = '';
	/**
	 * Where videos should be stored
	 */
	const VIDEO_PATH = '';
	/**
	 * Where the config file should be
	 */
	private $conf_path = '';
	/**
	 * Configuration variables array
	 */
	public $conf = array();
	/**
	 * We set this to true in shortcode parsing and then determine if we need to enqueue the JS, or if it's already included
	 */
	public $load_mediaelement = false;	
	/**
	 * Store scripts to load in footer
	 */
	public $scripts = array();		
	
	var $ret = array('html' => false, 'script' => false);
	
	var $hash = false;	
	
	public $ad_css_default = ".wpfp_custom_ad { position: absolute; bottom: 10%; z-index: 2; width: 100%; }\n.wpfp_custom_ad_content { background: white; margin: 0 auto; position: relative }";
	
	public $ad_css_bottom = ".wpfp_custom_ad { position: absolute; bottom: 0; z-index: 2; width: 100%; }\n.wpfp_custom_ad_content { background: white; margin: 0 auto; position: relative }";	
	
	/**
	 * Class constructor
	 */	
	public function __construct() {
		//load conf data into stack
		$this->_get_conf();
		
		if( is_admin() ) {
			//	update notices
		  $this->readme_URL = 'http://plugins.trac.wordpress.org/browser/fv-wordpress-flowplayer/trunk/readme.txt?format=txt';    
		  if( !has_action( 'in_plugin_update_message-fv-wordpress-flowplayer/flowplayer.php' ) ) {
	   		add_action( 'in_plugin_update_message-fv-wordpress-flowplayer/flowplayer.php', array( &$this, 'plugin_update_message' ) );
	   	}
	   	
	   	//	pointer boxes
	   	parent::__construct();
		}
		

		// define needed constants
		if (!defined('FV_FP_RELATIVE_PATH')) {
			define('FV_FP_RELATIVE_PATH', flowplayer::get_plugin_url() );
      
			$vid = 'http://'.$_SERVER['SERVER_NAME'];
			if (dirname($_SERVER['PHP_SELF']) != '/') 
				$vid .= dirname($_SERVER['PHP_SELF']);
			define('VIDEO_DIR', '/videos/');
			define('VIDEO_PATH', $vid.VIDEO_DIR);	
		}		
	}
	/**
	 * Gets configuration from cfg file.
	 * 
	 * @return bool Returns false on failiure, true on success.
	 */
	private function _get_conf() {
	  ///  Addition  2010/07/12  mv
    $conf = get_option( 'fvwpflowplayer' );  
        
    if( !isset( $conf['autoplay'] ) ) $conf['autoplay'] = 'false';
    if( !isset( $conf['googleanalytics'] ) ) $conf['googleanalytics'] = 'false';
    if( !isset( $conf['key'] ) ) $conf['key'] = 'false';
    if( !isset( $conf['logo'] ) ) $conf['logo'] = 'false';
    if( !isset( $conf['rtmp'] ) ) $conf['rtmp'] = 'false';
    if( !isset( $conf['auto_buffer'] ) ) $conf['auto_buffer'] = 'false';
    if( !isset( $conf['scaling'] ) ) $conf['scaling'] = 'true';
    if( !isset( $conf['disableembedding'] ) ) $conf['disableembedding'] = 'false';
    if( !isset( $conf['popupbox'] ) ) $conf['popupbox'] = 'false';    
    if( !isset( $conf['allowfullscreen'] ) ) $conf['allowfullscreen'] = 'true';
    if( !isset( $conf['allowuploads'] ) ) $conf['allowuploads'] = 'true';
    if( !isset( $conf['postthumbnail'] ) ) $conf['postthumbnail'] = 'false';
    if( !isset( $conf['tgt'] ) ) $conf['tgt'] = 'backgroundcolor';
    if( !isset( $conf['backgroundColor'] ) ) $conf['backgroundColor'] = '#333333';
    if( !isset( $conf['canvas'] ) ) $conf['canvas'] = '#000000';
    if( !isset( $conf['sliderColor'] ) ) $conf['sliderColor'] = '#ffffff';
    if( !isset( $conf['buttonColor'] ) ) $conf['buttonColor'] = '#ffffff';
    if( !isset( $conf['buttonOverColor'] ) ) $conf['buttonOverColor'] = '#ffffff';
    if( !isset( $conf['durationColor'] ) ) $conf['durationColor'] = '#eeeeee';
    if( !isset( $conf['timeColor'] ) ) $conf['timeColor'] = '#eeeeee';
    if( !isset( $conf['progressColor'] ) ) $conf['progressColor'] = '#00a7c8';
    if( !isset( $conf['bufferColor'] ) ) $conf['bufferColor'] = '#eeeeee';
    if( !isset( $conf['timelineColor'] ) ) $conf['timelineColor'] = '#666666';
    if( !isset( $conf['borderColor'] ) ) $conf['borderColor'] = '#666666';
    if( !isset( $conf['hasBorder'] ) ) $conf['hasBorder'] = 'false';    
    if( !isset( $conf['adTextColor'] ) ) $conf['adTextColor'] = '#888';
    if( !isset( $conf['adLinksColor'] ) ) $conf['adLinksColor'] = '#ff3333';    
    if( !isset( $conf['parse_commas'] ) ) $conf['parse_commas'] = 'false';
    if( !isset( $conf['width'] ) ) $conf['width'] = '600';
    if( !isset( $conf['height'] ) ) $conf['height'] = '400';
    if( !isset( $conf['engine'] ) ) $conf['engine'] = 'false';
    if( !isset( $conf['font-face'] ) ) $conf['font-face'] = 'Tahoma, Geneva, sans-serif';
		if( !isset( $conf['ad'] ) ) $conf['ad'] = '';     
		if( !isset( $conf['ad_width'] ) ) $conf['ad_width'] = '';     
		if( !isset( $conf['ad_height'] ) ) $conf['ad_height'] = '';     
		if( !isset( $conf['ad_css'] ) ) $conf['ad_css'] = $this->ad_css_default;     		
		if( !isset( $conf['disable_videochecker'] ) ) $conf['disable_videochecker'] = 'false';            
    if( isset( $conf['videochecker'] ) && $conf['videochecker'] == 'off' ) { $conf['disable_videochecker'] = 'true'; unset($conf['videochecker']); }         
    if( !isset( $conf['interface'] ) ) $conf['interface'] = array( 'playlist' => false, 'redirect' => false, 'autoplay' => false, 'loop' => false, 'splashend' => false, 'embed' => false, 'subtitles' => false, 'ads' => false, 'mobile' => false, 'align' => false );        
    if( !isset( $conf['interface']['popup'] ) ) $conf['interface']['popup'] = 'true';    
		if( !isset( $conf['amazon_bucket'] ) || !is_array($conf['amazon_bucket']) ) $conf['amazon_bucket'] = array('');       
		if( !isset( $conf['amazon_key'] ) || !is_array($conf['amazon_key']) ) $conf['amazon_key'] = array('');   
		if( !isset( $conf['amazon_secret'] ) || !is_array($conf['amazon_secret']) ) $conf['amazon_secret'] = array('');  		
		if( !isset( $conf['amazon_expire'] ) ) $conf['amazon_expire'] = '5';   
		if( !isset( $conf['fixed_size'] ) ) $conf['fixed_size'] = 'false';   		
		if( isset( $conf['responsive'] ) && $conf['responsive'] == 'fixed' ) { $conf['fixed_size'] = true; unset($conf['responsive']); }
    if( !isset( $conf['js-everywhere'] ) ) $conf['js-everywhere'] = 'false';
    if( !isset( $conf['marginBottom'] ) ) $conf['marginBottom'] = '28';   		

    update_option( 'fvwpflowplayer', $conf );
    $this->conf = $conf;
    return true;	 
    /// End of addition
	}
	/**
	 * Writes configuration into file.
	 */
	public function _set_conf() {
	  $aNewOptions = $_POST;
	  $sKey = $aNewOptions['key'];

	  foreach( $aNewOptions AS $key => $value ) {
	  	if( is_array($value) ) {
      	$aNewOptions[$key] = $value;
      } else if( !in_array( $key, array('amazon_bucket', 'amazon_key', 'amazon_secret', 'font-face', 'ad', 'ad_css') ) ) {
      	$aNewOptions[$key] = trim( preg_replace('/[^A-Za-z0-9.:\-_\/]/', '', $value) );
      } else {
      	$aNewOptions[$key] = stripslashes($value);
      }
	    if( (strpos( $key, 'Color' ) !== FALSE )||(strpos( $key, 'canvas' ) !== FALSE)) {
	      $aNewOptions[$key] = '#'.strtolower($aNewOptions[$key]);
	    }
	  }
	  $aNewOptions['key'] = trim($sKey);   
	  update_option( 'fvwpflowplayer', $aNewOptions );
    $this->conf = $aNewOptions;    
    
    $this->css_writeout();
	     
	  return true;	
	}
	/**
	 * Salt function - returns pseudorandom string hash.
	 * @return Pseudorandom string hash.
	 */
	public function _salt() {
    $salt = substr(md5(uniqid(rand(), true)), 0, 10);    
    return $salt;
	}
  
  
  function css_generate( $style_tag = true ) {
    global $fv_fp;
    
    $iMarginBottom = (isset($fv_fp->conf['marginBottom']) && intval($fv_fp->conf['marginBottom']) > -1 ) ? intval($fv_fp->conf['marginBottom']) : '28';
    
    if( $style_tag ) : ?>
      <style type="text/css">
    <?php endif;
    
    if ( isset($fv_fp->conf['key']) && $fv_fp->conf['key'] != 'false' && strlen($fv_fp->conf['key']) > 0 && isset($fv_fp->conf['logo']) && $fv_fp->conf['logo'] != 'false' && strlen($fv_fp->conf['logo']) > 0 ) : ?>		
      .flowplayer .fp-logo { display: block; opacity: 1; }                                              
    <?php endif;
  
    if( isset($fv_fp->conf['hasBorder']) && $fv_fp->conf['hasBorder'] == "true" ) : ?>
      .flowplayer { border: 1px solid <?php echo trim($fv_fp->conf['borderColor']); ?> !important; }
    <?php endif; ?>
  
    .flowplayer, flowplayer * { margin: 0 auto <?php echo $iMarginBottom; ?>px auto; display: block; }
    .flowplayer .fp-controls { background-color: <?php echo trim($fv_fp->conf['backgroundColor']); ?> !important; }
    .flowplayer { background-color: <?php echo trim($fv_fp->conf['canvas']); ?> !important; }
    .flowplayer .fp-duration { color: <?php echo trim($fv_fp->conf['durationColor']); ?> !important; }
    .flowplayer .fp-elapsed { color: <?php echo trim($fv_fp->conf['timeColor']); ?> !important; }
    .flowplayer .fp-volumelevel { background-color: <?php echo trim($fv_fp->conf['progressColor']); ?> !important; }  
    .flowplayer .fp-volumeslider { background-color: <?php echo trim($fv_fp->conf['bufferColor']); ?> !important; }
    .flowplayer .fp-timeline { background-color: <?php echo trim($fv_fp->conf['timelineColor']); ?> !important; }
    .flowplayer .fp-progress { background-color: <?php echo trim($fv_fp->conf['progressColor']); ?> !important; }
    .flowplayer .fp-buffer { background-color: <?php echo trim($fv_fp->conf['bufferColor']); ?> !important; }
    #content .flowplayer, .flowplayer { font-family: <?php echo trim($fv_fp->conf['font-face']); ?>; }
    #content .flowplayer .fp-embed-code textarea, .flowplayer .fp-embed-code textarea { line-height: 1.4; white-space: pre-wrap; color: <?php echo trim($this->conf['durationColor']); ?> !important; height: 160px; font-size: 10px; }
    
    .fvplayer .mejs-container .mejs-controls { background: <?php echo trim($fv_fp->conf['backgroundColor']); ?>!important; } 
    .fvplayer .mejs-controls .mejs-time-rail .mejs-time-current { background: <?php echo trim($fv_fp->conf['progressColor']); ?>!important; } 
    .fvplayer .mejs-controls .mejs-time-rail .mejs-time-loaded { background: <?php echo trim($fv_fp->conf['bufferColor']); ?>!important; } 
    .fvplayer .mejs-horizontal-volume-current { background: <?php echo trim($fv_fp->conf['progressColor']); ?>!important; } 
    .fvplayer .me-cannotplay span { padding: 5px; }
    #content .fvplayer .mejs-container .mejs-controls div { font-family: <?php echo trim($fv_fp->conf['font-face']); ?>; }
  
    .wpfp_custom_background { display: none; }	
    .wpfp_custom_popup { display: none; position: absolute; top: 10%; z-index: 2; text-align: center; width: 100%; color: #fff; }
    .is-finished .wpfp_custom_popup, .is-finished .wpfp_custom_background { display: block; }	
    .wpfp_custom_popup_content {  background: <?php echo trim($fv_fp->conf['backgroundColor']) ?>; padding: 1% 5%; width: 65%; margin: 0 auto; }
  
    <?php echo trim($this->conf['ad_css']); ?>
    .wpfp_custom_ad { color: <?php echo trim($fv_fp->conf['adTextColor']); ?>; }
    .wpfp_custom_ad a { color: <?php echo trim($fv_fp->conf['adLinksColor']); ?> }
  
    <?php if( $style_tag ) : ?>
      </style>  
    <?php endif;
  }
  
  
  function css_get() {
    global $fv_wp_flowplayer_ver;
    $bInline = true;
    $sURL = FV_FP_RELATIVE_PATH.'/css/flowplayer.css?ver='.$fv_wp_flowplayer_ver;    
    
    if( is_multisite() ) {
      global $blog_id;
      $site_id = $blog_id;
    } else {
      $site_id = 1;
    }

    if( isset($this->conf[$this->css_option()]) && $this->conf[$this->css_option()] ) {
      $filename = trailingslashit(WP_CONTENT_DIR).'fv-flowplayer-custom/style-'.$site_id.'.css';
      if( @file_exists($filename) ) {
        $sURL = trailingslashit( str_replace( array('/plugins','\\plugins'), '', plugins_url() )).'fv-flowplayer-custom/style-'.$site_id.'.css?ver='.$this->conf[$this->css_option()];
        $bInline = false;
      }
    }
    
    echo '<link rel="stylesheet" href="'.$sURL.'" type="text/css" media="screen" />'."\n";
    if( $bInline ) {
      $this->css_generate();
    }
    return ;
  }
  
  
  function css_option() {
    return 'css_writeout-'.sanitize_title(WP_CONTENT_URL);
  }
  
  
  function css_writeout() {
    $aOptions = get_option( 'fvwpflowplayer' );
    $aOptions[$this->css_option()] = false;
    update_option( 'fvwpflowplayer', $aOptions );
    
    /*$url = wp_nonce_url('options-general.php?page=fvplayer','otto-theme-options');
    if( false === ($creds = request_filesystem_credentials($url, $method, false, false, $_POST) ) ) { //  todo: no annoying notices here      
      return false; // stop the normal page form from displaying
    }   */ 
    
    if ( ! WP_Filesystem(true) ) {
      return false;
    }

    global $wp_filesystem;
    if( is_multisite() ) {
      global $blog_id;
      $site_id = $blog_id;
    } else {
      $site_id = 1;
    }
    $filename = $wp_filesystem->wp_content_dir().'fv-flowplayer-custom/style-'.$site_id.'.css';
     
    // by this point, the $wp_filesystem global should be working, so let's use it to create a file
    
    $bDirExists = false;
    if( !$wp_filesystem->exists($wp_filesystem->wp_content_dir().'fv-flowplayer-custom/') ) {
      if( $wp_filesystem->mkdir($wp_filesystem->wp_content_dir().'fv-flowplayer-custom/') ) {
        $bDirExists = true;
      }
    } else {
      $bDirExists = true;
    }
    
    if( !$bDirExists ) {
      return false;
    }
    
    ob_start();
    $this->css_generate(false);
    $sCSS = "\n/*CSS writeout performed on FV Flowplayer Settings save  on ".date('r')."*/\n".ob_get_clean();    
    if( !$sCSSCurrent = $wp_filesystem->get_contents( self::get_plugin_url().'/css/flowplayer.css' ) ) {
      return false;
    }
    $sCSSCurrent = preg_replace( '~url\((")?~', 'url($1'.self::get_plugin_url().'/css/', $sCSSCurrent ); //  fix relative paths!

    if( !$wp_filesystem->put_contents( $filename, $sCSSCurrent.$sCSS, FS_CHMOD_FILE) ) {
      return false;
    } else {
      $aOptions[$this->css_option()] = date('U');
      update_option( 'fvwpflowplayer', $aOptions );
      $this->conf = $aOptions;
    }
  }
  
  
  function get_amazon_secure( $media, &$fv_fp ) {

		$amazon_key = -1;
  	if( !empty($fv_fp->conf['amazon_key']) && !empty($fv_fp->conf['amazon_secret']) && !empty($fv_fp->conf['amazon_bucket']) ) {
  		foreach( $fv_fp->conf['amazon_bucket'] AS $key => $item ) {
  			if( stripos($media,$item.'/') != false  || stripos($media,$item.'.') != false ) {
  				$amazon_key = $key;
  				break;
  			}
  		}
  	}
  	
  	if( $amazon_key != -1 && !empty($fv_fp->conf['amazon_key'][$amazon_key]) && !empty($fv_fp->conf['amazon_secret'][$amazon_key]) && !empty($fv_fp->conf['amazon_bucket'][$amazon_key]) && stripos( $media, trim($fv_fp->conf['amazon_bucket'][$amazon_key]) ) !== false && apply_filters( 'fv_flowplayer_amazon_secure_exclude', $media ) ) {
  	
			$resource = trim( $media );
			
			if( !isset($fv_fp->expire_time) ) {
				$time = 60 * intval($fv_fp->conf['amazon_expire'][$amazon_key]);
			} else {
				$time = intval(ceil($fv_fp->expire_time));
			}			
			if( $time < 900 ) {
				$time = 900;
			}
			$time = apply_filters( 'fv_flowplayer_amazon_expires', $time, $media );
			$expires = time() + $time;
		 
			$url_components = parse_url($resource);
			$url_components['path'] = rawurlencode($url_components['path']); 
			$url_components['path'] = str_replace('%2F', '/', $url_components['path']);
			$url_components['path'] = str_replace('%2B', '+', $url_components['path']);			
			if( strpos( $url_components['path'], $fv_fp->conf['amazon_bucket'][$amazon_key] ) === false ) {
				$url_components['path'] = '/'.$fv_fp->conf['amazon_bucket'][$amazon_key].$url_components['path'];
			}
		
			$stringToSign = "GET\n\n\n$expires\n{$url_components['path']}";	
		
			$signature = utf8_encode($stringToSign);

			$signature = hash_hmac('sha1', $signature, $fv_fp->conf['amazon_secret'][$amazon_key], true);
			$signature = base64_encode($signature);
			
			$signature = urlencode($signature);
		
			$url = $resource;
			$url .= '?AWSAccessKeyId='.$fv_fp->conf['amazon_key'][$amazon_key]
						 .'&amp;Expires='.$expires
						 .'&amp;Signature='.$signature;
						 
			$media = $url;
						
			$this->ret['script']['fv_flowplayer_amazon_s3'][$this->hash] = $time;
  	}
  	
  	return $media;
  }
  
  
  function get_cloudfront_secure( $resource, $timeout = 900 ) {
		//This comes from key pair you generated for cloudfront
		$keyPairId = "YOUR_CLOUDFRONT_KEY_PAIR_ID";
		
		$expires = time() + $timeout; //Time out in seconds
		$json = '{"Statement":[{"Resource":"'.$resource.'","Condition":{"DateLessThan":{"AWS:EpochTime":'.$expires.'}}}]}';		
		
		//Read Cloudfront Private Key Pair
		$fp=fopen("private_key.pem","r"); 
		$priv_key=fread($fp,8192); 
		fclose($fp); 
		
		//Create the private key
		$key = openssl_get_privatekey($priv_key);
		if(!$key)
		{
			echo "<p>Failed to load private key!</p>";
			die();
			return;
		}
		
		//Sign the policy with the private key
		if(!openssl_sign($json, $signed_policy, $key, OPENSSL_ALGO_SHA1))
		{
			echo '<p>Failed to sign policy: '.openssl_error_string().'</p>';
			die();
			return;
		}
		
		//Create url safe signed policy
		$base64_signed_policy = base64_encode($signed_policy);
		$signature = str_replace(array('+','=','/'), array('-','_','~'), $base64_signed_policy);
		
		//Construct the URL
		$url = $resource.'?Expires='.$expires.'&Signature='.$signature.'&Key-Pair-Id='.$keyPairId;
		
		return $url;  
  }
  
  
  public static function get_encoded_url( $sURL ) {
    if( !preg_match('~%[0-9A-F]{2}~',$sURL) ) {
      $url_parts = parse_url( $sURL );
      $url_parts_encoded = parse_url( $sURL );			
      if( !empty($url_parts['path']) ) {
          $url_parts['path'] = join('/', array_map('rawurlencode', explode('/', $url_parts_encoded['path'])));
      }
      if( !empty($url_parts['query']) ) {
          $url_parts['query'] = str_replace( '&amp;', '&', $url_parts_encoded['query'] );				
      }
      
      $url_parts['path'] = str_replace( '%2B', '+', $url_parts['path'] );
      return http_build_url($sURL, $url_parts);
    } else {
      return $sURL;
    }    
  }
  
  
  public static function get_plugin_url() {
    if( stripos( __FILE__, '/themes/' ) !== false || stripos( __FILE__, '\\themes\\' ) !== false ) {
      return get_template_directory_uri().'/fv-wordpress-flowplayer';
    } else {
      return plugins_url( '', str_replace( array('/models','\\models'), '', __FILE__ ) );
    }
  }
  
	
	public function is_secure_amazon_s3( $url ) {
		return preg_match( '/^.+?s3.*?\.amazonaws\.com\/.+Signature=.+?$/', $url ) || preg_match( '/^.+?\.cloudfront\.net\/.+Signature=.+?$/', $url );
	}
	

	public function is_secure_cloudfront( $url ) {
		return preg_match( '/^.+?\.cloudfront\.net\/.+Signature=.+?$/', $url );
	}	
}
/**
 * Defines some needed constants and loads the right flowplayer_head() function.
 */
function flowplayer_head() {
	global $fv_fp;	
  $fv_fp->flowplayer_head();
}


function flowplayer_jquery() {
  global $fv_wp_flowplayer_ver, $fv_fp;
  
}

?>