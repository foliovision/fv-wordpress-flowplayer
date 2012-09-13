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
		//set conf path
		///$this->conf_path = realpath(dirname(__FILE__)).'/../wpfp.conf';
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
    if( !isset( $conf['key'] ) ) $conf['key'] = 'false';
    if( !isset( $conf['autobuffer'] ) ) $conf['autobuffer'] = 'false';
    if( !isset( $conf['scaling'] ) ) $conf['scaling'] = 'false';
    if( !isset( $conf['popupbox'] ) ) $conf['popupbox'] = 'false';
    if( !isset( $conf['linkhighlight'] ) ) $conf['linkhighlight'] = 'false';
    if( !isset( $conf['allowfullscreen'] ) ) $conf['allowfullscreen'] = 'true';
    if( !isset( $conf['allowuploads'] ) ) $conf['allowuploads'] = 'true';
    if( !isset( $conf['postthumbnail'] ) ) $conf['postthumbnail'] = 'false';
    if( !isset( $conf['tgt'] ) ) $conf['tgt'] = 'backgroundcolor';
    if( !isset( $conf['backgroundColor'] ) ) $conf['backgroundColor'] = '#1b1b1d';
    if( !isset( $conf['canvas'] ) ) $conf['canvas'] = '#ffffff';
    if( !isset( $conf['sliderColor'] ) ) $conf['sliderColor'] = '#2e2e2e';
    if( !isset( $conf['buttonColor'] ) ) $conf['buttonColor'] = '#454545';
    if( !isset( $conf['buttonOverColor'] ) ) $conf['buttonOverColor'] = '#ffffff';
    if( !isset( $conf['durationColor'] ) ) $conf['durationColor'] = '#ffffff';
    if( !isset( $conf['timeColor'] ) ) $conf['timeColor'] = '#ededed';
    if( !isset( $conf['progressColor'] ) ) $conf['progressColor'] = '#707070';
    if( !isset( $conf['bufferColor'] ) ) $conf['bufferColor'] = '#4d4d4d';
    if( !isset( $conf['commas'] ) ) $conf['commas'] = 'true';
    if( !isset( $conf['optimizejs'] ) ) $conf['optimizejs'] = 'false';
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
	    $_POST[$key] = preg_replace('/[^A-Za-z0-9]/', '', $value);
	    if( (strpos( $key, 'Color' ) !== FALSE )||(strpos( $key, 'canvas' ) !== FALSE)) {
	      $_POST[$key] = '#'.strtolower($_POST[$key]);
	    }
	  }
	  $_POST['key'] = $save_key;
	  update_option( 'fvwpflowplayer', $_POST );
	  return;	
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
		  //define('RELATIVE_PATH', get_option('siteurl').'/wp-content/plugins/'.$strFPdirname);   // following bugfix by scott@scottelkin.com
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
      define('AUDIOPLAYER', RELATIVE_PATH.'/flowplayer/flowplayer.audio-3.2.2.swf');
   	$vid = 'http://'.$_SERVER['SERVER_NAME'];
   	if (dirname($_SERVER['PHP_SELF']) != '/') 
         $vid .= dirname($_SERVER['PHP_SELF']);
      define('VIDEO_DIR', '/videos/');
      define('VIDEO_PATH', $vid.VIDEO_DIR);	
   }
	// call the right function for displaying CSS and JS links
	if (class_exists('flowplayer_frontend')) {
		flowplayer_frontend::flowplayer_head();
	} else {
		flowplayer_backend::flowplayer_head();
	}
}
function fvp_ajax_action_checkvideo(){
   $pattern = '/' . $_SERVER['SERVER_NAME'] . '(.*)/';
   preg_match($pattern, $_POST['source'], $matches);
   if ($matches[1]) $strUpVideo = realpath($_SERVER['DOCUMENT_ROOT'] . $matches[1]);
   else $strUpVideo = $_POST['source'];
   require_once(dirname(__FILE__).'/../view/getid3/getid3.php');
   // Initialize getID3 engine
   $getID3 = new getID3;
   $ThisFileInfo = $getID3->analyze($strUpVideo);
   if (isset($ThisFileInfo['error'])) $file_error = "Could not read video details, please fill the width and height manually.";
   $file_time = $ThisFileInfo['playtime_string'];            // playtime in minutes:seconds, formatted string
   $file_width = $ThisFileInfo['video']['resolution_x'];          
   $file_height = $ThisFileInfo['video']['resolution_y'];
   $file_size = $ThisFileInfo['filesize'];           
   $file_size = round($file_size/(1024*1024),2);  
   $output = '<tr id="video_' . $_POST['id'] . '">
               <th></th>
               <td><span>Width <small>(px)</small></span><input type="text" id="width_' . $_POST['id'] . '" name="width_' . $_POST['id'] . '" style="width: 25%"  value="' . $file_width . '"/><br />
                <span>Height <small>(px)</small></span><input type="text" id="height_' . $_POST['id'] . '" name="height_' . $_POST['id'] . '" style="width: 25%" value="' . $file_height . '"/></td>
               <td>';
   if (isset($file_error))
     $output .= 'Video header could not be read, please fill the width and height manually.';
   else
      $output .= 'Video Duration: ' . $file_time . ' min<br />
                  File size: ' . $file_size . ' MB';
   $output .= '</td>
               <td><a href="javascript:void(0);" onclick="FVFPCheckVideo(\'' . $_POST['id'] . '\',true) ">Check</a></td>
               <td></td>
         </tr>';
   echo $output;
   die;
}
?>