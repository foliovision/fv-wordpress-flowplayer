<?php
class flowplayer {
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
	 * Class constructor
	 */
	public function __construct() {
		//load conf data into stack
		$this->_get_conf();
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
    if( !isset( $conf['autobuffer'] ) ) $conf['autobuffer'] = 'false';
    if( !isset( $conf['scaling'] ) ) $conf['scaling'] = 'true';
    if( !isset( $conf['popupbox'] ) ) $conf['popupbox'] = 'false';    
    if( !isset( $conf['allowfullscreen'] ) ) $conf['allowfullscreen'] = 'true';
    if( !isset( $conf['allowuploads'] ) ) $conf['allowuploads'] = 'true';
    if( !isset( $conf['postthumbnail'] ) ) $conf['postthumbnail'] = 'false';
    if( !isset( $conf['tgt'] ) ) $conf['tgt'] = 'backgroundcolor';
    if( !isset( $conf['backgroundColor'] ) ) $conf['backgroundColor'] = '#333333';
    if( !isset( $conf['canvas'] ) ) $conf['canvas'] = '#ffffff';
    if( !isset( $conf['sliderColor'] ) ) $conf['sliderColor'] = '#ffffff';
    if( !isset( $conf['buttonColor'] ) ) $conf['buttonColor'] = '#ffffff';
    if( !isset( $conf['buttonOverColor'] ) ) $conf['buttonOverColor'] = '#ffffff';
    if( !isset( $conf['durationColor'] ) ) $conf['durationColor'] = '#eeeeee';
    if( !isset( $conf['timeColor'] ) ) $conf['timeColor'] = '#eeeeee';
    if( !isset( $conf['progressColor'] ) ) $conf['progressColor'] = '#00a7c8';
    if( !isset( $conf['bufferColor'] ) ) $conf['bufferColor'] = '#eeeeee';
    if( !isset( $conf['timelineColor'] ) ) $conf['timelineColor'] = '#666666';
    if( !isset( $conf['commas'] ) ) $conf['commas'] = 'true';
    if( !isset( $conf['width'] ) ) $conf['width'] = '320';
    if( !isset( $conf['height'] ) ) $conf['height'] = '240';

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
      $_POST[$key] = preg_replace('/[^A-Za-z0-9.:\-_\/]/', '', $value);
	    if( (strpos( $key, 'Color' ) !== FALSE )||(strpos( $key, 'canvas' ) !== FALSE)) {
	      $_POST[$key] = '#'.strtolower($_POST[$key]);
	    }
	  }
	  $_POST['key'] = $save_key;
	  update_option( 'fvwpflowplayer', $_POST );
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
}
/**
 * Defines some needed constants and loads the right flowplayer_head() function.
 */
function flowplayer_head() {
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
    $conf = get_option( 'fvwpflowplayer' );
    if( !isset( $conf['key'] )||(!$conf['key'])||($conf['key']=='false') )
      define('PLAYER', RELATIVE_PATH.'/flowplayer/flowplayer.swf');
    else
      define('PLAYER', RELATIVE_PATH.'/flowplayer/commercial/flowplayer.commercial-3.2.15.swf');
    define('AUDIOPLAYER', RELATIVE_PATH.'/flowplayer/flowplayer.audio-3.2.10.swf');
   	$vid = 'http://'.$_SERVER['SERVER_NAME'];
   	if (dirname($_SERVER['PHP_SELF']) != '/') 
      $vid .= dirname($_SERVER['PHP_SELF']);
    define('VIDEO_DIR', '/videos/');
    define('VIDEO_PATH', $vid.VIDEO_DIR);	
  }
	// call the right function for displaying CSS and JS links
	if (is_admin()) {		
    $fp = new flowplayer_backend();      
    $fp->flowplayer_head();    
	} else {
		$fp = new flowplayer_frontend();  
    $fp->flowplayer_head();
	}
}

function flowplayer_jquery() {
  wp_enqueue_script("jquery");
}
?>