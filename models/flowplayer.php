<?php

require_once( dirname(__FILE__) . '/../includes/fp-api.php' );

class flowplayer extends FV_Wordpress_Flowplayer_Plugin {
	private $count = 0;
	/**
	 * Relative URL path
	 */
	const RELATIVE_PATH = '';
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
		  $this->readme_URL = 'http://plugins.trac.wordpress.org/browser/fv-wordpress-flowplayer/trunk/readme.txt?format=txt';    
		  if( !has_action( 'in_plugin_update_message-fv-wordpress-flowplayer/flowplayer.php' ) ) {
	   		add_action( 'in_plugin_update_message-fv-wordpress-flowplayer/flowplayer.php', array( &$this, 'plugin_update_message' ) );
	   	}
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
    if( !isset( $conf['interface']['popup'] ) ) $conf['interface']['popup'] = 'true';    
		if( !isset( $conf['amazon_bucket'] ) || !is_array($conf['amazon_bucket']) ) $conf['amazon_bucket'] = array('');       
		if( !isset( $conf['amazon_key'] ) || !is_array($conf['amazon_key']) ) $conf['amazon_key'] = array('');   
		if( !isset( $conf['amazon_secret'] ) || !is_array($conf['amazon_secret']) ) $conf['amazon_secret'] = array('');  		
		if( !isset( $conf['amazon_expire'] ) ) $conf['amazon_expire'] = '5';   
		if( !isset( $conf['fixed_size'] ) ) $conf['fixed_size'] = false;   		
		if( isset( $conf['responsive'] ) && $conf['responsive'] == 'fixed' ) { $conf['fixed_size'] = true; unset($conf['responsive']); } 

    update_option( 'fvwpflowplayer', $conf );
    $this->conf = $conf;
    return true;	 
    /// End of addition
	}
	/**
	 * Writes configuration into file.
	 */
	public function _set_conf() {
	  $save_key = $_POST['key'];
	  foreach( $_POST AS $key => $value ) {
	  	if( !in_array( $key, array('amazon_bucket', 'amazon_key', 'amazon_secret', 'font-face', 'ad', 'ad_css') ) ) {
      	$_POST[$key] = preg_replace('/[^A-Za-z0-9.:\-_\/]/', '', $value);
      } else if( is_array($value) ) {
      	$_POST[$key] = $value;
      } else {
      	$_POST[$key] = stripslashes($value);
      }
	    if( (strpos( $key, 'Color' ) !== FALSE )||(strpos( $key, 'canvas' ) !== FALSE)) {
	      $_POST[$key] = '#'.strtolower($_POST[$key]);
	    }
	  }
	  $_POST['key'] = $save_key;    
	  update_option( 'fvwpflowplayer', $_POST );
	  
	  $conf = get_option( 'fvwpflowplayer' );  
	  $this->conf = $conf;
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
  
  
  function get_amazon_secure( $media, &$fv_fp ) {

  	if( !empty($fv_fp->conf['amazon_key']) && !empty($fv_fp->conf['amazon_secret']) && !empty($fv_fp->conf['amazon_bucket']) ) {
  		foreach( $fv_fp->conf['amazon_bucket'] AS $key => $item ) {
  			if( stripos($media,$item.'/') != false  || stripos($media,$item.'.') != false ) {
  				break;
  			}
  		}
  	}
    	
  	if( !empty($fv_fp->conf['amazon_key'][$key]) && !empty($fv_fp->conf['amazon_secret'][$key]) && !empty($fv_fp->conf['amazon_bucket'][$key]) && stripos( $media, trim($fv_fp->conf['amazon_bucket'][$key]) ) !== false && apply_filters( 'fv_flowplayer_amazon_secure_exclude', $media ) ) {
  	
			$resource = trim( $media );
			
			if( !isset($fv_fp->expire_time) ) {
				$time = apply_filters( 'fv_flowplayer_amazon_expires', 60 * intval($fv_fp->conf['amazon_expire'][$key]), $media );
			} else {
				$time = apply_filters( 'fv_flowplayer_amazon_expires', intval(ceil($fv_fp->expire_time)), $media );
			}			
			if( $time < 900 ) {
				$time = 900;
			}
			$expires = time() + $time;
		 
			$url_components = parse_url($resource);
			$url_components['path'] = rawurlencode($url_components['path']); 
			$url_components['path'] = str_replace('%2F', '/', $url_components['path']);
			
			$stringToSign = "GET\n\n\n$expires\n{$url_components['path']}";
		
			$signature = utf8_encode($stringToSign);
			$signature = hash_hmac('sha1', $signature, $fv_fp->conf['amazon_secret'][$key], true);
			$signature = base64_encode($signature);
			
			$signature = urlencode($signature);
		
			$url = $resource;
			$url .= '?AWSAccessKeyId='.$fv_fp->conf['amazon_key'][$key]
						 .'&Expires='.$expires
						 .'&Signature='.$signature;
						 
			$media = $url;
						
			$fv_fp->ret['script'] .= "
			jQuery('#wpfp_".$fv_fp->hash."').bind('error', function (e,api, error) {
					fv_fp_date = new Date();			
					if( error.code == 4 && fv_fp_date.getTime() > (fv_fp_utime + $time) ) {
						jQuery(e.target).find('.fp-message').delay(500).queue( function(n) {
							jQuery(this).html('<h2>Video file expired.<br />Please reload the page and play it again.</h2>'); n();
						} );
					}
			} );
			";
  	}
  	
  	return $media;
  }
  
	
	public function is_secure_amazon_s3( $url ) {
		return preg_match( '/^.+?s3.*?\.amazonaws\.com\/.+Signature=.+?$/', $url ) || preg_match( '/^.+?\.cloudfront\.net\/.+Signature=.+?$/', $url );
	}
}
/**
 * Defines some needed constants and loads the right flowplayer_head() function.
 */
function flowplayer_head() {
	global $fv_fp;

	// define needed constants
  preg_match('/.*wp-content\/plugins\/(.*?)\/models.*/',dirname(__FILE__),$matches);
  if (isset($matches[1]))
    $strFPdirname = $matches[1];
  else
    $strFPdirname = 'fv-wordpress-flowplayer';
	if (!defined('RELATIVE_PATH')) {
    if( function_exists('plugins_url') ) {
			define('RELATIVE_PATH', plugins_url().'/'.$strFPdirname);
		} else {
			$siteurl = get_option('siteurl');
			if((!empty($_SERVER['HTTPS'])) && ('off'!==$_SERVER['HTTPS']))   // this line changes by carlo@artilibere.com
        $siteurl = preg_replace('/^http:(.*)$/', "https:$1", $siteurl);
			define('RELATIVE_PATH', $siteurl.'/wp-content/plugins/'.$strFPdirname);
    }			
   	$vid = 'http://'.$_SERVER['SERVER_NAME'];
   	if (dirname($_SERVER['PHP_SELF']) != '/') 
      $vid .= dirname($_SERVER['PHP_SELF']);
    define('VIDEO_DIR', '/videos/');
    define('VIDEO_PATH', $vid.VIDEO_DIR);	
  }
	
  $fv_fp->flowplayer_head();
}


function flowplayer_jquery() {
  global $fv_wp_flowplayer_ver;
  wp_enqueue_script( 'flowplayer', plugins_url( '/fv-wordpress-flowplayer/flowplayer/flowplayer.min.js' ), array('jquery'), $fv_wp_flowplayer_ver );
}

?>