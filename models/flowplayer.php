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
  public $load_tabs = false;    
  /**
   * Store scripts to load in footer
   */
  public $scripts = array();    
  
  var $ret = array('html' => false, 'script' => false);
  
  var $hash = false;

  var $bCSSInline = false;
  
  public $ad_css_default = ".wpfp_custom_ad { position: absolute; bottom: 10%; z-index: 20; width: 100%; }\n.wpfp_custom_ad_content { background: white; margin: 0 auto; position: relative }";
  
  public $ad_css_bottom = ".wpfp_custom_ad { position: absolute; bottom: 0; z-index: 20; width: 100%; }\n.wpfp_custom_ad_content { background: white; margin: 0 auto; position: relative }";
  
  public $load_dash = false;
  
  public $load_hlsjs = false;
  

  public function __construct() {
    //load conf data into stack
    $this->_get_conf();
    
    if( is_admin() ) {
      //  update notices
      $this->readme_URL = 'http://plugins.trac.wordpress.org/browser/fv-wordpress-flowplayer/trunk/readme.txt?format=txt';    
      if( !has_action( 'in_plugin_update_message-fv-wordpress-flowplayer/flowplayer.php' ) ) {
        add_action( 'in_plugin_update_message-fv-wordpress-flowplayer/flowplayer.php', array( &$this, 'plugin_update_message' ) );
      }
       
       //  pointer boxes
      parent::__construct();
    }
    

    // define needed constants
    if (!defined('FV_FP_RELATIVE_PATH')) {
      define('FV_FP_RELATIVE_PATH', flowplayer::get_plugin_url() );
      
      $aURL = parse_url( home_url() );
      $vid = isset($_SERVER['SERVER_NAME']) ? 'http://'.$_SERVER['SERVER_NAME'] : $aURL['scheme'].'://'.$aURL['host'];
      if (dirname($_SERVER['PHP_SELF']) != '/') 
        $vid .= dirname($_SERVER['PHP_SELF']);
      define('VIDEO_DIR', '/videos/');
      define('VIDEO_PATH', $vid.VIDEO_DIR);  
    }
    
    
    add_filter( 'fv_flowplayer_caption', array( $this, 'get_duration_playlist' ), 10, 3 );
    add_filter( 'fv_flowplayer_inner_html', array( $this, 'get_duration_video' ), 10, 2 );
    
    add_filter( 'fv_flowplayer_video_src', array( $this, 'get_amazon_secure'), 10, 2 );
    
    add_filter('fv_flowplayer_css_writeout', array( $this, 'css_writeout_option' ) );
    
    add_action( 'wp_enqueue_scripts', array( $this, 'css_enqueue' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'css_enqueue' ) );
    
    add_filter( 'rewrite_rules_array', array( $this, 'rewrite_embed' ), 999999 );    
    add_filter( 'query_vars', array( $this, 'rewrite_vars' ) );
    add_filter( 'init', array( $this, 'rewrite_check' ) );
    
    add_filter( 'fv_player_custom_css', array( $this, 'popup_css' ) );

    add_action( 'wp_head', array( $this, 'template_embed_buffer' ), 999999);
    add_action( 'wp_footer', array( $this, 'template_embed' ), 0 );
    
  }
  
  
  private function _get_conf() {
    ///  Addition  2010/07/12  mv
    $conf = get_option( 'fvwpflowplayer' );  
        
    if( !isset( $conf['autoplay'] ) ) $conf['autoplay'] = 'false';
    if( !isset( $conf['googleanalytics'] ) ) $conf['googleanalytics'] = 'false';
    if( !isset( $conf['key'] ) ) $conf['key'] = 'false';
    if( !isset( $conf['logo'] ) ) $conf['logo'] = 'false';
    if( !isset( $conf['rtmp'] ) ) $conf['rtmp'] = 'false';
    if( !isset( $conf['auto_buffering'] ) ) $conf['auto_buffering'] = 'false';
    if( !isset( $conf['scaling'] ) ) $conf['scaling'] = 'true';
    if( !isset( $conf['disableembedding'] ) ) $conf['disableembedding'] = 'false';
    if( !isset( $conf['popupbox'] ) ) $conf['popupbox'] = 'false';    
    if( !isset( $conf['allowfullscreen'] ) ) $conf['allowfullscreen'] = 'true';
    if( !isset( $conf['allowuploads'] ) ) $conf['allowuploads'] = 'true';
    if( !isset( $conf['postthumbnail'] ) ) $conf['postthumbnail'] = 'false';
    
    //default colors
    if( !isset( $conf['tgt'] ) ) $conf['tgt'] = 'backgroundcolor';
    if( !isset( $conf['backgroundColor'] ) ) $conf['backgroundColor'] = '#333333';
    if( !isset( $conf['canvas'] ) ) $conf['canvas'] = '#000000';
    if( !isset( $conf['sliderColor'] ) ) $conf['sliderColor'] = '#ffffff';
    /*if( !isset( $conf['buttonColor'] ) ) $conf['buttonColor'] = '#ffffff';
    if( !isset( $conf['buttonOverColor'] ) ) $conf['buttonOverColor'] = '#ffffff';*/
    if( !isset( $conf['durationColor'] ) ) $conf['durationColor'] = '#eeeeee';
    if( !isset( $conf['timeColor'] ) ) $conf['timeColor'] = '#eeeeee';
    if( !isset( $conf['progressColor'] ) ) $conf['progressColor'] = '#00a7c8';
    if( !isset( $conf['bufferColor'] ) ) $conf['bufferColor'] = '#eeeeee';
    if( !isset( $conf['timelineColor'] ) ) $conf['timelineColor'] = '#666666';
    if( !isset( $conf['borderColor'] ) ) $conf['borderColor'] = '#666666';
    if( !isset( $conf['hasBorder'] ) ) $conf['hasBorder'] = 'false';    
    if( !isset( $conf['adTextColor'] ) ) $conf['adTextColor'] = '#888';
    if( !isset( $conf['adLinksColor'] ) ) $conf['adLinksColor'] = '#ff3333';
    
    
    //unset( $conf['playlistBgColor'], $conf['playlistFontColor'], $conf['playlistSelectedColor']);
    if( !isset( $conf['playlistBgColor'] ) ) $conf['playlistBgColor'] = '#808080';
    if( !isset( $conf['playlistFontColor'] ) ) $conf['playlistFontColor'] = '';
    if( !isset( $conf['playlistSelectedColor'] ) ) $conf['playlistSelectedColor'] = '#00a7c8';

    //
    
    if( !isset( $conf['parse_commas'] ) ) $conf['parse_commas'] = 'false';
    if( !isset( $conf['width'] ) ) $conf['width'] = '720';
    if( !isset( $conf['height'] ) ) $conf['height'] = '480';
    if( !isset( $conf['engine'] ) ) $conf['engine'] = 'false';
    if( !isset( $conf['font-face'] ) ) $conf['font-face'] = 'Tahoma, Geneva, sans-serif';
    if( !isset( $conf['ad'] ) ) $conf['ad'] = '';     
    if( !isset( $conf['ad_width'] ) ) $conf['ad_width'] = '';     
    if( !isset( $conf['ad_height'] ) ) $conf['ad_height'] = '';     
    if( !isset( $conf['ad_css'] ) ) $conf['ad_css'] = $this->ad_css_default;
    if( !isset( $conf['ad_show_after'] ) ) $conf['ad_show_after'] = 0;         
    if( !isset( $conf['disable_videochecker'] ) ) $conf['disable_videochecker'] = 'false';            
    if( isset( $conf['videochecker'] ) && $conf['videochecker'] == 'off' ) { $conf['disable_videochecker'] = 'true'; unset($conf['videochecker']); }         
    if( !isset( $conf['interface'] ) ) $conf['interface'] = array( 'playlist' => false, 'redirect' => false, 'autoplay' => false, 'loop' => false, 'splashend' => false, 'embed' => false, 'subtitles' => false, 'ads' => false, 'mobile' => false, 'align' => false );        
    if( !isset( $conf['interface']['popup'] ) ) $conf['interface']['popup'] = 'true';    
    if( !isset( $conf['amazon_bucket'] ) || !is_array($conf['amazon_bucket']) ) $conf['amazon_bucket'] = array('');       
    if( !isset( $conf['amazon_key'] ) || !is_array($conf['amazon_key']) ) $conf['amazon_key'] = array('');   
    if( !isset( $conf['amazon_secret'] ) || !is_array($conf['amazon_secret']) ) $conf['amazon_secret'] = array('');
    if( !isset( $conf['amazon_region'] ) || !is_array($conf['amazon_region']) ) $conf['amazon_region'] = array('');      
    if( !isset( $conf['amazon_expire'] ) ) $conf['amazon_expire'] = '5';
    if( !isset( $conf['amazon_expire_force'] ) ) $conf['amazon_expire_force'] = 'false';   
    if( !isset( $conf['fixed_size'] ) ) $conf['fixed_size'] = 'false';       
    if( isset( $conf['responsive'] ) && $conf['responsive'] == 'fixed' ) { $conf['fixed_size'] = true; unset($conf['responsive']); }
    if( !isset( $conf['js-everywhere'] ) ) $conf['js-everywhere'] = 'false';
    if( !isset( $conf['marginBottom'] ) ) $conf['marginBottom'] = '28';
    if( !isset( $conf['ui_play_button'] ) ) $conf['ui_play_button'] = 'true';
    if( !isset( $conf['volume'] ) ) $conf['volume'] = 1;
    if( !isset( $conf['player-position'] ) ) $conf['player-position'] = '';

    update_option( 'fvwpflowplayer', $conf );
    $this->conf = $conf;
    return true;   
    /// End of addition
  }

  
  public function _set_conf() {
    $aNewOptions = $_POST;
    $sKey = $aNewOptions['key'];

    if(isset($aNewOptions['popups'])){
      unset($aNewOptions['popups']['#fv_popup_dummy_key#']);
      
      foreach( $aNewOptions['popups'] AS $key => $value ) {
        $aNewOptions['popups'][$key]['css'] = stripslashes($value['css']);
        $aNewOptions['popups'][$key]['html'] = stripslashes($value['html']);
      }
      
      update_option('fv_player_popups',$aNewOptions['popups']);
      unset($aNewOptions['popups']);
    }
    
    foreach( $aNewOptions AS $key => $value ) {
      if( is_array($value) ) {
        $aNewOptions[$key] = $value;
      } else if( !in_array( $key, array('amazon_region', 'amazon_bucket', 'amazon_key', 'amazon_secret', 'font-face', 'ad', 'ad_css') ) ) {
        $aNewOptions[$key] = trim( preg_replace('/[^A-Za-z0-9.:\-_\/]/', '', $value) );
      } else {
        $aNewOptions[$key] = stripslashes(trim($value));
      }
      if( (strpos( $key, 'Color' ) !== FALSE )||(strpos( $key, 'canvas' ) !== FALSE)) {
        $aNewOptions[$key] = '#'.strtolower($aNewOptions[$key]);
      }
    }
    $aNewOptions['key'] = trim($sKey);
    $aOldOptions = is_array(get_option('fvwpflowplayer')) ? get_option('fvwpflowplayer') : array();
    
    if( isset($aNewOptions['db_duration']) && $aNewOptions['db_duration'] == "true" && ( !isset($aOldOptions['db_duration']) || $aOldOptions['db_duration'] == "false" ) ) {
      global $FV_Player_Checker;
      $FV_Player_Checker->queue_add_all();
    }
    
    if( !isset($aNewOptions['pro']) || !is_array($aNewOptions['pro']) ) {
      $aNewOptions['pro'] = array();
    }
    
    if( !isset($aOldOptions['pro']) || !is_array($aOldOptions['pro']) ) {
      $aOldOptions['pro'] = array();
    }    
 
    
    $aNewOptions['pro'] = array_merge($aOldOptions['pro'],$aNewOptions['pro']);
    $aNewOptions = array_merge($aOldOptions,$aNewOptions);
    $aNewOptions = apply_filters( 'fv_flowplayer_settings_save', $aNewOptions, $aOldOptions );
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
  
  
  private function build_playlist_html( $aArgs, $sSplashImage, $sItemCaption ){

    if(isset($aArgs['liststyle']) && $aArgs['liststyle'] == 'vertical'){

       if( $sSplashImage ) {
        $sHTML = "\t\t<a href='#' onclick='return false'><span style='background-image: url(\"".$sSplashImage."\")'></span>$sItemCaption</a>\n";
      } else {
        $sHTML = "\t\t<a href='#' onclick='return false'><span></span>$sItemCaption</a>\n";
      }  
      
    }else{
      if( $sSplashImage ) {
        $sHTML = "\t\t<a href='#' onclick='return false'><span style='background-image: url(\"".$sSplashImage."\")'></span>$sItemCaption</a>\n";
      } else {
        $sHTML = "\t\t<a href='#' onclick='return false'><span></span>$sItemCaption</a>\n";
      }  
    }
      
    return $sHTML;
  }
  
  //  todo: this could be parsing rtmp://host/path/mp4:rtmp_path links as well
  function build_playlist( $aArgs, $media, $src1, $src2, $rtmp, $splash_img, $suppress_filters = false ) {

      $sShortcode = isset($aArgs['playlist']) ? $aArgs['playlist'] : false;
      $sCaption = isset($aArgs['caption']) ? $aArgs['caption'] : false;
  
      $replace_from = array('&amp;','\;', '\,');        
      $replace_to = array('<!--amp-->','<!--semicolon-->','<!--comma-->');        
      $sShortcode = str_replace( $replace_from, $replace_to, $sShortcode );      
      $sItems = explode( ';', $sShortcode );

      if( $sCaption ) {
        $replace_from = array('&amp;quot;','&amp;','\;','&quot;');        
        $replace_to = array('"','<!--amp-->','<!--semicolon-->','"');        
        $sCaption = str_replace( $replace_from, $replace_to, $sCaption );
        $aCaption = explode( ';', $sCaption );        
      }
      if( isset($aCaption) && count($aCaption) > 0 ) {
        foreach( $aCaption AS $key => $item ) {
          $aCaption[$key] = str_replace('<!--amp-->','&',$item);
        }
      } 
                 
      $aItem = array();      
      $flash_media = array();
      
      if( $rtmp ) {
        $rtmp = 'rtmp:'.$rtmp;  
      }
      
      foreach( apply_filters( 'fv_player_media', array($media, $src1, $src2, $rtmp), $this ) AS $key => $media_item ) {
        if( !$media_item ) continue;
        $media_url = $this->get_video_src( preg_replace( '~^rtmp:~', '', $media_item ), array( 'url_only' => true, 'suppress_filters' => $suppress_filters ) );
        if( is_array($media_url) ) {
          $actual_media_url = $media_url['media'];
          if( $this->get_mime_type($actual_media_url) == 'video/mp4' ) {
            $flash_media[] = $media_url['flash'];
          }
        } else {
          $actual_media_url = $media_url;
        }
        if( stripos( $media_item, 'rtmp:' ) === 0 ) {
          if( !preg_match( '~^[a-z0-9]+:~', $actual_media_url ) ) {
            $aItem[] = array( 'src' => $this->get_mime_type($actual_media_url,'mp4',true).':'.str_replace( '+', ' ', $actual_media_url ), 'type' => 'video/flash' );
          } else {
            $aItem[] = array( 'src' => str_replace( '+', ' ', $actual_media_url ), 'type' => 'video/flash' );
          }
        } else {
          $aItem[] = array( 'src' => $actual_media_url, 'type' => $this->get_mime_type($actual_media_url) );
        }        
      }
      
      if( count($flash_media) ) {
        $bHaveFlash = false;
        foreach( $aItem AS $key => $aItemFile ) { //  how to avoid duplicates?
          if( in_array( 'flash', array_keys($aItemFile) ) ) {
            $bHaveFlash = true;
          }
        }
        
        if( !$bHaveFlash ) {
          foreach( $flash_media AS $flash_media_items ) {
            $aItem[] = array( 'flash' => $flash_media_items );
          }
        }      
      }
      
      $sItemCaption = ( isset($aCaption) ) ? array_shift($aCaption) : false;
      $sItemCaption = apply_filters( 'fv_flowplayer_caption', $sItemCaption, $aItem, $aArgs );
      
      $splash_img = apply_filters( 'fv_flowplayer_playlist_splash', $splash_img, $this );
      
      list( $rtmp_server, $rtmp ) = $this->get_rtmp_server($rtmp);
      
      $aPlayer = array( 'sources' => $aItem );      
      if( $rtmp_server ) $aPlayer['rtmp'] = array( 'url' => $rtmp_server );
      
      $aPlaylistItems[] = $aPlayer;
      $aSplashScreens[] = $splash_img;
      $aCaptions[] = $sItemCaption;

      
      $sHTML = array();
      
      if( $sShortcode && count($sItems) > 0) {
        //var_dump($sItemCaption);
        
        if( isset($aArgs['liststyle']) && !empty($aArgs['liststyle'])   ){
          $sHTML[] = $this->build_playlist_html( $aArgs, $splash_img, $sItemCaption );
        }else{
          $sHTML[] = "<a href='#' class='is-active' onclick='return false'><span ".( (isset($splash_img) && !empty($splash_img)) ? "style='background-image: url(\"".$splash_img."\")' " : "" )."></span>$sItemCaption</a>\n";
        }       
            
        foreach( $sItems AS $iKey => $sItem ) {
          $aPlaylist_item = explode( ',', $sItem );
        
          foreach( $aPlaylist_item AS $key => $item ) {
            if( $key > 0 && ( stripos($item,'http:') !== 0 && stripos($item,'https:') !== 0 && stripos($item,'rtmp:') !== 0 && stripos($item,'/') !== 0 ) ) {
              $aPlaylist_item[$key-1] .= ','.$item;              
              $aPlaylist_item[$key] = $aPlaylist_item[$key-1];
              unset($aPlaylist_item[$key-1]);
            }
            $aPlaylist_item[$key] = str_replace( $replace_to, $replace_from, $aPlaylist_item[$key] );                          
          }
  
          $aItem = array();
          $sSplashImage = false;
          $flash_media = array();
          
          $sSplashImage = apply_filters( 'fv_flowplayer_playlist_splash', $sSplashImage, $this, $aPlaylist_item );

          foreach( apply_filters( 'fv_player_media', $aPlaylist_item, $this ) AS $aPlaylist_item_i ) {
            if( preg_match('~\.(png|gif|jpg|jpe|jpeg)($|\?)~',$aPlaylist_item_i) ) {
              $sSplashImage = $aPlaylist_item_i;
              continue;
            }
            
            $media_url = $this->get_video_src( preg_replace( '~^rtmp:~', '', $aPlaylist_item_i ), array( 'url_only' => true, 'suppress_filters' => $suppress_filters ) );
            if( is_array($media_url) ) {
              $actual_media_url = $media_url['media'];
              if( $this->get_mime_type($actual_media_url) == 'video/mp4' ) {
                $flash_media[] = $media_url['flash'];
              }
            } else {
              $actual_media_url = $media_url;
            }
            if( stripos( $aPlaylist_item_i, 'rtmp:' ) === 0 ) {
              if( !preg_match( '~^[a-z0-9]+:~', $actual_media_url ) ) {
                $aItem[] = array( 'src' => $this->get_mime_type($actual_media_url,'mp4',true).':'.str_replace( '+', ' ', $actual_media_url ), 'type' => 'video/flash' );
              } else {
                $aItem[] = array( 'src' => str_replace( '+', ' ', $actual_media_url ), 'type' => 'video/flash' );
              }              
            } else {
              $aItem[] = array( 'src' => $actual_media_url, 'type' => $this->get_mime_type($aPlaylist_item_i) ); 
            }                
            
          }
          
          if( count($flash_media) ) {
            $bHaveFlash = false;
            foreach( $aItem AS $key => $aItemFile ) {
              if( in_array( 'flash', array_keys($aItemFile) ) ) {
                $bHaveFlash = true;
              }
            }
            
            if( !$bHaveFlash ) {
              foreach( $flash_media AS $flash_media_items ) {
                $aItem[] = array( 'flash' => $flash_media_items );
              }
            }      
          }

          $aPlayer = array( 'sources' => $aItem );      
          if( $rtmp_server ) $aPlayer['rtmp'] = array( 'url' => $rtmp_server );
      
          $aPlaylistItems[] = $aPlayer;
          $sItemCaption = ( isset($aCaption[$iKey]) ) ? __($aCaption[$iKey]) : false;
          $sItemCaption = apply_filters( 'fv_flowplayer_caption', $sItemCaption, $aItem, $aArgs );
          
          
          $sHTML[] = $this->build_playlist_html( $aArgs, $sSplashImage, $sItemCaption );
          if( $sSplashImage ) {
            $aSplashScreens[] = $sSplashImage;  
          } 
          $aCaptions[] = $sItemCaption;
        }
        
      }
      
      if( isset($aArgs['liststyle']) && $aArgs['liststyle'] == 'prevnext' ){
        $sHTML = array();
      }
      
      $sPlaylistClass = '' ;
      
      if( isset($aArgs['liststyle']) && $aArgs['liststyle'] == 'vertical' ){
        $sPlaylistClass .= ' fp-playlist-vertical';
      }
      //var_dump($aCaptions);
      if( isset($aArgs['liststyle']) && sizeof($aCaptions) > 0 ){
        $sPlaylistClass .= ' fp-playlist-has-captions';
      }
      
      if(isset($aArgs['liststyle']) && $aArgs['liststyle'] != 'tabs'){
        $aPlaylistItems = apply_filters('fv_flowplayer_playlist_items',$aPlaylistItems,$this);
      }
    
      
      
      $sHTML = apply_filters( 'fv_flowplayer_playlist_item_html', $sHTML );

      $sHTML = "\t<div class='fp-playlist-external $sPlaylistClass' rel='wpfp_{$this->hash}'>\n".implode( '', $sHTML )."\t</div>\n";

      $jsonPlaylistItems = str_replace( array('\\/', ','), array('/', ",\n\t\t"), json_encode($aPlaylistItems) );
      //$jsonPlaylistItems = preg_replace( '~"(.*)":"~', '$1:"', $jsonPlaylistItems );
     
      return array( $sHTML, $aPlaylistItems, $aSplashScreens, $aCaptions );      
  }  
  
  function css_generate( $skip_style_tag = true ) {
    global $fv_fp;
    
    $iMarginBottom = (isset($fv_fp->conf['marginBottom']) && intval($fv_fp->conf['marginBottom']) > -1 ) ? intval($fv_fp->conf['marginBottom']) : '28';
    
    if( !$skip_style_tag ) : ?>
      <style type="text/css">
    <?php endif;
    
    if ( isset($fv_fp->conf['key']) && $fv_fp->conf['key'] != 'false' && strlen($fv_fp->conf['key']) > 0 && isset($fv_fp->conf['logo']) && $fv_fp->conf['logo'] != 'false' && strlen($fv_fp->conf['logo']) > 0 ) : ?>    
      .flowplayer .fp-logo { display: block; opacity: 1; }                                              
    <?php endif;
  
    if( isset($fv_fp->conf['hasBorder']) && $fv_fp->conf['hasBorder'] == "true" ) : ?>
      .flowplayer { border: 1px solid <?php echo trim($fv_fp->conf['borderColor']); ?> !important; }
    <?php endif; ?>
  
    .flowplayer { margin: 0 auto <?php echo $iMarginBottom; ?>px auto; display: block; }
    .flowplayer.fixed-controls { margin: 0 auto <?php echo $iMarginBottom+30; ?>px auto; display: block; }
    .flowplayer.has-abloop { margin-bottom: <?php echo $iMarginBottom+24; ?>px; }
    .flowplayer.fixed-controls.has-abloop { margin-bottom: <?php echo $iMarginBottom+30+24; ?>px; }
    .flowplayer.has-caption, flowplayer.has-caption * { margin: 0 auto; }
    .flowplayer .fp-controls, .flowplayer .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { color: <?php echo trim($fv_fp->conf['durationColor']); ?> !important; background-color: <?php echo trim($fv_fp->conf['backgroundColor']); ?> !important; }
    .flowplayer { background-color: <?php echo trim($fv_fp->conf['canvas']); ?> !important; }
    .flowplayer .fp-duration, .flowplayer a.fp-play, .flowplayer a.fp-mute { color: <?php echo trim($fv_fp->conf['durationColor']); ?> !important; }
    .flowplayer .fp-elapsed { color: <?php echo trim($fv_fp->conf['timeColor']); ?> !important; }
    .flowplayer .fp-volumelevel { background-color: <?php echo trim($fv_fp->conf['progressColor']); ?> !important; }  
    .flowplayer .fp-volumeslider, .flowplayer .noUi-background { background-color: <?php echo trim($fv_fp->conf['bufferColor']); ?> !important; }
    .flowplayer .fp-timeline { background-color: <?php echo trim($fv_fp->conf['timelineColor']); ?> !important; }
    .flowplayer .fv-ab-loop .noUi-handle  { color: <?php echo trim($fv_fp->conf['backgroundColor']); ?> !important; }
    .flowplayer .fp-progress, .flowplayer .fv-ab-loop .noUi-connect, .fv-player-buttons a.current { background-color: <?php echo trim($fv_fp->conf['progressColor']); ?> !important; }
    .flowplayer .fp-buffer, .flowplayer .fv-ab-loop .noUi-handle { background-color: <?php echo trim($fv_fp->conf['bufferColor']); ?> !important; }
    #content .flowplayer, .flowplayer { font-family: <?php echo trim($fv_fp->conf['font-face']); ?>; }
    .flowplayer .fp-dropdown li.active { background-color: <?php echo trim($fv_fp->conf['progressColor']); ?> !important }
    
    .fvplayer .mejs-container .mejs-controls { background: <?php echo trim($fv_fp->conf['backgroundColor']); ?>!important; } 
    .fvplayer .mejs-controls .mejs-time-rail .mejs-time-current { background: <?php echo trim($fv_fp->conf['progressColor']); ?>!important; } 
    .fvplayer .mejs-controls .mejs-time-rail .mejs-time-loaded { background: <?php echo trim($fv_fp->conf['bufferColor']); ?>!important; } 
    .fvplayer .mejs-horizontal-volume-current { background: <?php echo trim($fv_fp->conf['progressColor']); ?>!important; } 
    .fvplayer .me-cannotplay span { padding: 5px; }
    #content .fvplayer .mejs-container .mejs-controls div { font-family: <?php echo trim($fv_fp->conf['font-face']); ?>; }
  
    .wpfp_custom_background { display: none; }  
    .wpfp_custom_popup { position: absolute; top: 10%; z-index: 20; text-align: center; width: 100%; color: #fff; }
    .is-finished .wpfp_custom_background { display: block; }  
    .fv_player_popup {  background: <?php echo trim($fv_fp->conf['backgroundColor']) ?>; padding: 1% 5%; width: 65%; margin: 0 auto; }
  
    <?php echo trim($this->conf['ad_css']); ?>
    .wpfp_custom_ad { color: <?php echo trim($fv_fp->conf['adTextColor']); ?>; z-index: 20 !important; }
    .wpfp_custom_ad a { color: <?php echo trim($fv_fp->conf['adLinksColor']); ?> }
    
    .fv-wp-flowplayer-notice-small { color: <?php echo trim($fv_fp->conf['timeColor']); ?> !important; }
    
    .fvfp_admin_error { color: <?php echo trim($fv_fp->conf['durationColor']); ?>; }
    .fvfp_admin_error a { color: <?php echo trim($fv_fp->conf['durationColor']); ?>; }
    #content .fvfp_admin_error a { color: <?php echo trim($fv_fp->conf['durationColor']); ?>; }
    .fvfp_admin_error_content {  background: <?php echo trim($fv_fp->conf['backgroundColor']); ?>; opacity:0.75;filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=75); }
    
    .fp-playlist-external > a > span { background-color:<?php echo $fv_fp->conf['playlistBgColor'];?>; }
    <?php if (!empty($fv_fp->conf['playlistFontColor'])&& $fv_fp->conf['playlistFontColor'] !=='#'):?>.fp-playlist-external > a { color:<?php echo $fv_fp->conf['playlistFontColor'];?>; }<?php endif; ?>
    .fp-playlist-external > a.is-active > span { border-color:<?php echo $fv_fp->conf['playlistSelectedColor'];?>; }
    .fp-playlist-external a.is-active { color:<?php echo $fv_fp->conf['playlistSelectedColor'];?>; }
    <?php if (!empty($fv_fp->conf['splash'])):?>.fp-playlist-external a span { background-image:url(<?php echo $fv_fp->conf['splash']; ?>); }<?php endif; ?>    
    <?php if( isset($fv_fp->conf['subtitleSize']) ) : ?>.flowplayer .fp-subtitle p { font-size: <?php echo intval($fv_fp->conf['subtitleSize']); ?>px; }<?php endif; ?>
    <?php if( isset($fv_fp->conf['logoPosition']) ) :
      if( $fv_fp->conf['logoPosition'] == 'bottom-left' ) {
        $sCSS = "bottom: 30px; left: 15px";
      } else if( $fv_fp->conf['logoPosition'] == 'bottom-right' ) {
        $sCSS = "bottom: 30px; right: 15px; left: auto";
      } else if( $fv_fp->conf['logoPosition'] == 'top-left' ) {
        $sCSS = "top: 30px; left: 15px; bottom: auto";
      } else if( $fv_fp->conf['logoPosition'] == 'top-right' ) {
        $sCSS = "top: 30px; right: 15px; bottom: auto; left: auto";
      }
      ?>.flowplayer .fp-logo { <?php echo $sCSS; ?> }<?php endif; ?>
  
    <?php if( isset($fv_fp->conf['player-position']) && 'left' == $fv_fp->conf['player-position'] ) : ?>.flowplayer { margin-left: 0; }<?php endif; ?>
    <?php echo apply_filters('fv_player_custom_css',''); ?>
    <?php if( !$skip_style_tag ) : ?>
      </style>  
    <?php endif;
  }
  
  
  function css_enqueue() {
    
    if( is_admin() && !did_action('admin_footer') && ( !isset($_GET['page']) || $_GET['page'] != 'fvplayer' ) ) {
      return;
    }
    
    global $fv_wp_flowplayer_ver;
    $this->bCSSInline = true;
    $sURL = FV_FP_RELATIVE_PATH.'/css/flowplayer.css';
    $sVer = $fv_wp_flowplayer_ver;
    
    if( is_multisite() ) {
      global $blog_id;
      $site_id = $blog_id;
    } else {
      $site_id = 1;
    }

    if( apply_filters('fv_flowplayer_css_writeout', true ) && isset($this->conf[$this->css_option()]) && $this->conf[$this->css_option()] ) {
      $filename = trailingslashit(WP_CONTENT_DIR).'fv-flowplayer-custom/style-'.$site_id.'.css';
      if( @file_exists($filename) ) {
        $sURL = trailingslashit( str_replace( array('/plugins','\\plugins'), '', plugins_url() )).'fv-flowplayer-custom/style-'.$site_id.'.css';
        $sVer = $this->conf[$this->css_option()];
        $this->bCSSInline = false;
      }
    }
    
    if( is_admin() &&  did_action('admin_footer') ) {
      echo "<link rel='stylesheet' id='fv_flowplayer-css'  href='".esc_attr($sURL)."?ver=".$sVer."' type='text/css' media='all' />\n";
      echo "<link rel='stylesheet' id='fv_flowplayer_admin'  href='".FV_FP_RELATIVE_PATH."/css/admin.css?ver=".$fv_wp_flowplayer_ver."' type='text/css' media='all' />\n";            
      
    } else {
      wp_enqueue_style( 'fv_flowplayer', $sURL, array(), $sVer );
      wp_enqueue_style( 'fv_flowplayer_admin', FV_FP_RELATIVE_PATH.'/css/admin.css', array(), $fv_wp_flowplayer_ver );
      
      if( $this->bCSSInline ) {
        add_action( 'wp_head', array( $this, 'css_generate' ) );
        add_action( 'admin_head', array( $this, 'css_generate' ) );
      }
      
    }
    
  }
  
  
  function css_option() {
    return 'css_writeout-'.sanitize_title(WP_CONTENT_URL);
  }
  
  
  function css_writeout() {
    if( !apply_filters('fv_flowplayer_css_writeout', true ) ) {
      return false;
    }
    
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
    $this->css_generate(true);
    $sCSS = "\n/*CSS writeout performed on FV Flowplayer Settings save  on ".date('r')."*/\n".ob_get_clean();    
    if( !$sCSSCurrent = $wp_filesystem->get_contents( dirname(__FILE__).'/../css/flowplayer.css' ) ) {
      return false;
    }
    $sCSSCurrent = apply_filters('fv_player_custom_css',$sCSSCurrent);
    $sCSSCurrent = preg_replace( '~url\(([\'"])?~', 'url($1'.self::get_plugin_url().'/css/', $sCSSCurrent ); //  fix relative paths!
    $sCSSCurrent = str_replace( array('http://', 'https://'), array('//','//'), $sCSSCurrent );

    if( !$wp_filesystem->put_contents( $filename, "/*\nFV Flowplayer custom styles\n\nWarning: This file is not mean to be edited. Please put your custom CSS into your theme stylesheet or any custom CSS field of your template.\n*/\n\n".$sCSSCurrent.$sCSS, FS_CHMOD_FILE) ) {
      return false;
    } else {
      $aOptions[$this->css_option()] = date('U');
      update_option( 'fvwpflowplayer', $aOptions );
      $this->conf = $aOptions;
    }
  }
  
  
  function get_amazon_secure( $media ) {
    
    if( stripos($media,'X-Amz-Expires') !== false || stripos($media,'AWSAccessKeyId') !== false ) return $media;
    
    $aArgs = func_get_args();
    $aArgs = $aArgs[1];
    global $fv_fp;
  
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
        $time = 60 * intval($fv_fp->conf['amazon_expire']);
      } else {
        $time = intval(ceil($fv_fp->expire_time));
      }
      
      if( isset($fv_fp->conf['amazon_expire']) && $fv_fp->conf['amazon_expire_force'] == 'true' ) {
        $time = 60 * intval($fv_fp->conf['amazon_expire']);
      }
      
      if( $time < 900 ) {
        $time = 900;
      }
      
      $time = apply_filters( 'fv_flowplayer_amazon_expires', $time, $media );
      
      $url_components = parse_url($resource);
      
      $iAWSVersion = ( isset($fv_fp->conf['amazon_region'][$amazon_key]) && $fv_fp->conf['amazon_region'][$amazon_key] ) ? 4 : 2;
      
      if( $iAWSVersion == 4 ) {
        $url_components['path'] = str_replace('+', ' ', $url_components['path']);
      }
      
      $url_components['path'] = rawurlencode($url_components['path']);
      $url_components['path'] = str_replace('%2F', '/', $url_components['path']);
      $url_components['path'] = str_replace('%2B', '+', $url_components['path']);
      $url_components['path'] = str_replace('%2523', '%23', $url_components['path']);
      $url_components['path'] = str_replace('%252B', '%2B', $url_components['path']);  
      $url_components['path'] = str_replace('%2527', '%27', $url_components['path']);  
          
      $sGlue = ( $aArgs['url_only'] ) ? '&' : '&amp;';
      
      if( $iAWSVersion == 4 ) {
        $sXAMZDate = gmdate('Ymd\THis\Z');
        $sDate = gmdate('Ymd');
        $sCredentialScope = $sDate."/".$fv_fp->conf['amazon_region'][$amazon_key]."/s3/aws4_request"; //  todo: variable
        $sSignedHeaders = "host";
        $sXAMZCredential = urlencode($fv_fp->conf['amazon_key'][$amazon_key].'/'.$sCredentialScope);
        
        //  1. http://docs.aws.amazon.com/general/latest/gr/sigv4-create-canonical-request.html      
        $sCanonicalRequest = "GET\n";
        $sCanonicalRequest .= $url_components['path']."\n";
        $sCanonicalRequest .= "X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=$sXAMZCredential&X-Amz-Date=$sXAMZDate&X-Amz-Expires=$time&X-Amz-SignedHeaders=$sSignedHeaders\n";
        $sCanonicalRequest .= "host:".$url_components['host']."\n";        
        $sCanonicalRequest .= "\n$sSignedHeaders\n";
        $sCanonicalRequest .= "UNSIGNED-PAYLOAD";
        
        //  2. http://docs.aws.amazon.com/general/latest/gr/sigv4-create-string-to-sign.html
        $sStringToSign = "AWS4-HMAC-SHA256\n";
        $sStringToSign .= "$sXAMZDate\n";
        $sStringToSign .= "$sCredentialScope\n";
        $sStringToSign .= hash('sha256',$sCanonicalRequest);
        
        //  3. http://docs.aws.amazon.com/general/latest/gr/sigv4-calculate-signature.html
        $sSignature = hash_hmac('sha256', $sDate, "AWS4".$fv_fp->conf['amazon_secret'][$amazon_key], true );
        $sSignature = hash_hmac('sha256', $fv_fp->conf['amazon_region'][$amazon_key], $sSignature, true );  //  todo: variable
        $sSignature = hash_hmac('sha256', 's3', $sSignature, true );
        $sSignature = hash_hmac('sha256', 'aws4_request', $sSignature, true );
        $sSignature = hash_hmac('sha256', $sStringToSign, $sSignature );
                
        //  4. http://docs.aws.amazon.com/general/latest/gr/sigv4-add-signature-to-request.html        
        $resource .= "?X-Amz-Algorithm=AWS4-HMAC-SHA256";        
        $resource .= $sGlue."X-Amz-Credential=$sXAMZCredential";
        $resource .= $sGlue."X-Amz-Date=$sXAMZDate";
        $resource .= $sGlue."X-Amz-Expires=$time";
        $resource .= $sGlue."X-Amz-SignedHeaders=$sSignedHeaders";
        $resource .= $sGlue."X-Amz-Signature=".$sSignature;              
        
        $this->ret['script']['fv_flowplayer_amazon_s3'][$this->hash] = $time;
  
      } else {
        $expires = time() + $time;
        
        if( strpos( $url_components['path'], $fv_fp->conf['amazon_bucket'][$amazon_key] ) === false ) {
          $url_components['path'] = '/'.$fv_fp->conf['amazon_bucket'][$amazon_key].$url_components['path'];
        }        
            
        do {
          $expires++;
          $stringToSign = "GET\n\n\n$expires\n{$url_components['path']}";  
        
          $signature = utf8_encode($stringToSign);
    
          $signature = hash_hmac('sha1', $signature, $fv_fp->conf['amazon_secret'][$amazon_key], true);
          $signature = base64_encode($signature);
          
          $signature = urlencode($signature);        
        } while( stripos($signature,'%2B') !== false );      
      
        $resource .= '?AWSAccessKeyId='.$fv_fp->conf['amazon_key'][$amazon_key].$sGlue.'Expires='.$expires.$sGlue.'Signature='.$signature;
        
      }
      
      $media = $resource;
    
    }
    
    return $media;
  }
  
  
  public static function get_duration( $post_id, $video_src ) {
    $sDuration = false;
    if( $sVideoMeta = get_post_meta( $post_id, flowplayer::get_video_key($video_src), true ) ) {  //  todo: should probably work regardles of quality version
      if( isset($sVideoMeta['duration']) && $sVideoMeta['duration'] > 0 ) {
        $tDuration = $sVideoMeta['duration'];
        if( $tDuration < 3600 ) {
          $sDuration = gmdate( "i:s", $tDuration );
        } else {
          $sDuration = gmdate( "H:i:s", $tDuration );
        }
      }      
    }
    return $sDuration;
  }
  
  
  public static function get_duration_post( $post_id = false ) {
    global $post, $fv_fp;
    $post_id = ( $post_id ) ? $post_id : $post->ID;

    $content = false;
    $objPost = get_post($post_id);
    if( $aVideos = FV_Player_Checker::get_videos($objPost->post_content) ) {
      if( $sDuration = flowplayer::get_duration($post_id, $aVideos[0]) ) {
        $content = $sDuration;
      }
    }
    
    return $content;
  }   
  
  
  public static function get_duration_playlist( $caption ) {
    global $fv_fp;
    if( !isset($fv_fp->conf['db_duration']) || $fv_fp->conf['db_duration'] != 'true' || !$caption ) return $caption;
    
    global $post;
    $aArgs = func_get_args();
    
    if( isset($aArgs[1][0]) && is_array($aArgs[1][0]) ) {        
      $sItemKeys = array_keys($aArgs[1][0]);
      if( $sDuration = flowplayer::get_duration( $post->ID, $aArgs[1][0][$sItemKeys[0]] ) ) {
        $caption .= '<i class="dur">'.$sDuration.'</i>';
      } 
    }
    
    return $caption;
  }
  
  
  public static function get_duration_video( $content ) {
    global $fv_fp, $post;    
    if( !$post || !isset($fv_fp->conf['db_duration']) || $fv_fp->conf['db_duration'] != 'true' ) return $content;

    $aArgs = func_get_args();
    if( $sDuration = flowplayer::get_duration( $post->ID, $aArgs[1]->aCurArgs['src']) ) {
      $content .= '<div class="fvfp_duration">'.$sDuration.'</div>';
    }
    
    return $content;
  }    
  
  
  public static function get_encoded_url( $sURL ) {
    //if( !preg_match('~%[0-9A-F]{2}~',$sURL) ) {
      $url_parts = parse_url( $sURL );
      $url_parts_encoded = parse_url( $sURL );      
      if( !empty($url_parts['path']) ) {
          $url_parts['path'] = join('/', array_map('rawurlencode', explode('/', $url_parts_encoded['path'])));
      }
      if( !empty($url_parts['query']) ) {
          $url_parts['query'] = str_replace( '&amp;', '&', $url_parts_encoded['query'] );        
      }
      
      $url_parts['path'] = str_replace( '%2B', '+', $url_parts['path'] );
      return fv_http_build_url($sURL, $url_parts);
    /*} else {
      return $sURL;
    }*/    
  }
  
  
  public static function get_languages() {
    $aLangs = array(
      'SDH' => 'SDH',
      'AB' => 'Abkhazian',
      'AA' => 'Afar',
      'AF' => 'Afrikaans',
      'SQ' => 'Albanian',
      'AM' => 'Amharic',
      'AR' => 'Arabic',
      'HY' => 'Armenian',
      'AS' => 'Assamese',
      'AY' => 'Aymara',
      'AZ' => 'Azerbaijani',
      'BA' => 'Bashkir',
      'EU' => 'Basque',
      'BN' => 'Bengali, Bangla',
      'DZ' => 'Bhutani',
      'BH' => 'Bihari',
      'BI' => 'Bislama',
      'BR' => 'Breton',
      'BG' => 'Bulgarian',
      'MY' => 'Burmese',
      'BE' => 'Byelorussian',
      'KM' => 'Cambodian',
      'CA' => 'Catalan',
      'ZH' => 'Chinese',
      'CO' => 'Corsican',
      'HR' => 'Croatian',
      'CS' => 'Czech',
      'DA' => 'Danish',
      'NL' => 'Dutch',
      'EN' => 'English',
      'EO' => 'Esperanto',
      'ET' => 'Estonian',
      'FO' => 'Faeroese',
      'FJ' => 'Fiji',
      'FI' => 'Finnish',
      'FR' => 'French',
      'FY' => 'Frisian',
      'GD' => 'Gaelic (Scots Gaelic)',
      'GL' => 'Galician',
      'KA' => 'Georgian',
      'DE' => 'German',
      'EL' => 'Greek',
      'KL' => 'Greenlandic',
      'GN' => 'Guarani',
      'GU' => 'Gujarati',
      'HA' => 'Hausa',
      'IW' => 'Hebrew',
      'HI' => 'Hindi',
      'HU' => 'Hungarian',
      'IS' => 'Icelandic',
      'IN' => 'Indonesian',
      'IA' => 'Interlingua',
      'IE' => 'Interlingue',
      'IK' => 'Inupiak',
      'GA' => 'Irish',
      'IT' => 'Italian',
      'JA' => 'Japanese',
      'JW' => 'Javanese',
      'KN' => 'Kannada',
      'KS' => 'Kashmiri',
      'KK' => 'Kazakh',
      'RW' => 'Kinyarwanda',
      'KY' => 'Kirghiz',
      'RN' => 'Kirundi',
      'KO' => 'Korean',
      'KU' => 'Kurdish',
      'LO' => 'Laothian',
      'LA' => 'Latin',
      'LV' => 'Latvian, Lettish',
      'LN' => 'Lingala',
      'LT' => 'Lithuanian',
      'MK' => 'Macedonian',
      'MG' => 'Malagasy',
      'MS' => 'Malay',
      'ML' => 'Malayalam',
      'MT' => 'Maltese',
      'MI' => 'Maori',
      'MR' => 'Marathi',
      'MO' => 'Moldavian',
      'MN' => 'Mongolian',
      'NA' => 'Nauru',
      'NE' => 'Nepali',
      'NO' => 'Norwegian',
      'OC' => 'Occitan',
      'OR' => 'Oriya',
      'OM' => 'Oromo, Afan',
      'PS' => 'Pashto, Pushto',
      'FA' => 'Persian',
      'PL' => 'Polish',
      'PT' => 'Portuguese',
      'PA' => 'Punjabi',
      'QU' => 'Quechua',
      'RM' => 'Rhaeto-Romance',
      'RO' => 'Romanian',
      'RU' => 'Russian',
      'SM' => 'Samoan',
      'SG' => 'Sangro',
      'SA' => 'Sanskrit',
      'SR' => 'Serbian',
      'SH' => 'Serbo-Croatian',
      'ST' => 'Sesotho',
      'TN' => 'Setswana',
      'SN' => 'Shona',
      'SD' => 'Sindhi',
      'SI' => 'Singhalese',
      'SS' => 'Siswati',
      'SK' => 'Slovak',
      'SL' => 'Slovenian',
      'SO' => 'Somali',
      'ES' => 'Spanish',
      'SU' => 'Sudanese',
      'SW' => 'Swahili',
      'SV' => 'Swedish',
      'TL' => 'Tagalog',
      'TG' => 'Tajik',
      'TA' => 'Tamil',
      'TT' => 'Tatar',
      'TE' => 'Tegulu',
      'TH' => 'Thai',
      'BO' => 'Tibetan',
      'TI' => 'Tigrinya',
      'TO' => 'Tonga',
      'TS' => 'Tsonga',
      'TR' => 'Turkish',
      'TK' => 'Turkmen',
      'TW' => 'Twi',
      'UK' => 'Ukrainian',
      'UR' => 'Urdu',
      'UZ' => 'Uzbek',
      'VI' => 'Vietnamese',
      'VO' => 'Volapuk',
      'CY' => 'Welsh',
      'WO' => 'Wolof',
      'XH' => 'Xhosa',
      'JI' => 'Yiddish',
      'YO' => 'Yoruba',
      'ZU' => 'Zulu'
    );
    
    ksort($aLangs);
    
    return $aLangs;
  }
  
  
  function get_mime_type($media, $default = 'flash', $no_video = false) {
    $media = trim($media);
    $aURL = explode( '?', $media ); //  throwing away query argument here
    $pathinfo = pathinfo( $aURL[0] );    

    $extension = ( isset($pathinfo['extension']) ) ? $pathinfo['extension'] : false;       
    $extension = preg_replace( '~[?#].+$~', '', $extension );
    $extension = strtolower($extension);

    if( !$extension ) {
      $output = $default;
    } else {
      if ($extension == 'm3u8' || $extension == 'm3u') {
        global $fv_fp;
        $fv_fp->load_hlsjs = true;        
        $output = 'x-mpegurl';
      } else if ($extension == 'mpd') {
        global $fv_fp;
        $fv_fp->load_dash = true;
        $output = 'dash+xml';
      } else if ($extension == 'm4v') {
        $output = 'mp4';
      } else if( $extension == 'mp3' ) {
        $output = 'mpeg';
      } else if( $extension == 'wav' ) {
        $output = 'wav';
      } else if( $extension == 'ogg' ) {
        $output = 'ogg';
      } else if( $extension == 'ogv' ) {
        $output = 'ogg';
      } else if( $extension == 'mov' ) {
        $output = 'mp4';
      } else if( $extension == '3gp' ) {
        $output = 'mp4';      
      } else if( $extension == 'mkv' ) {
        $output = 'mp4';      
      } else if( !in_array($extension, array('mp4', 'm4v', 'webm', 'ogv', 'mp3', 'ogg', 'wav', '3gp')) ) {
        $output = $default;  
      } else {
        $output = $extension;
      }
    }
    
    if( !$no_video ) {
      switch($extension)  {
        case 'dash+xml' :
        case 'mpd' :
          $output = 'application/'.$output;
          break;
        case 'x-mpegurl' :
          $output = 'application/'.$output;
          break;
        case 'm3u8' :
          $output = 'application/'.$output;
          break;        
        default:
          $output = 'video/'.$output;
          break;
      }
    }

    return apply_filters( 'fv_flowplayer_get_mime_type', $output, $media );  
  }
  
  
  public static function get_plugin_url() {
    if( stripos( __FILE__, '/themes/' ) !== false || stripos( __FILE__, '\\themes\\' ) !== false ) {
      return get_template_directory_uri().'/fv-wordpress-flowplayer';
    } else {
      return plugins_url( '', str_replace( array('/models','\\models'), '', __FILE__ ) );
    }
  }
  
  
  public static function get_video_key( $sURL ) {
    $sURL = str_replace( '?v=', '-v=', $sURL );
    $sURL = preg_replace( '~\?.*$~', '', $sURL );
    $sURL = str_replace( array('/','://'), array('-','-'), $sURL );
    return '_fv_flowplayer_'.sanitize_title($sURL);
  }
  
  
  
  
  function get_video_src($media, $aArgs ) {
    $aArgs = wp_parse_args( $aArgs, array(
          'dynamic' => false,
          'flash' => true,
          'id' => false,
          'mobileUserAgent' => false,
          'rtmp' => false,        
          'suppress_filters' => false,
          'url_only' => false
        )
      );
    
    if( $media ) { 
      $mime_type = $this->get_mime_type($media);
      $sID = ($aArgs['id']) ? 'id="'.$aArgs['id'].'" ' : '';
  
      if( !$aArgs['suppress_filters'] ) {
        $media = apply_filters( 'fv_flowplayer_video_src', $media, $aArgs );          
      }
      
      //  fix for signed Amazon URLs, we actually need it for Flash only, so it gets into an extra source tag
      $source_flash_encoded = false;  
      if( $this->is_secure_amazon_s3($media) /*&& stripos($media,'.webm') === false && stripos($media,'.ogv') === false */) {
          $media_fixed = str_replace('%2B', '%25252B',$media);   
          $media_fixed = str_replace('%23', '%252523',$media_fixed );
          $media_fixed = str_replace('+', '%2B',$media_fixed ); 
          //  only if there was a change and we don't have an RTMP for Flash
          if( $media_fixed != $media && empty($aArgs['rtmp']) ) {
            $source_flash_encoded = $media_fixed;
          }
      }
      
      if( $aArgs['url_only'] ) {
        if( $aArgs['flash'] && $source_flash_encoded ) {
          return array( 'media' => $media, 'flash' => $source_flash_encoded );
        } else {
          return trim($media);
        }
      } else {

        $sReturn = '<source '.$sID.'src="'.trim($media).'" type="'.$mime_type.'" />'."\n";
        
        if( $source_flash_encoded && strcmp($mime_type,'video/mp4') == 0 ) {
          $sReturn .= '<source '.$sID.'src="'.trim($source_flash_encoded).'" type="video/flash" />'."\n";
        }
        return $sReturn;
      }
    }
    return null;
  }
  
  
  function get_video_url($media) {
    if( strpos($media,'rtmp://') !== false ) {
      return null;
    }
    if( strpos($media,'http://') !== 0 && strpos($media,'https://') !== 0 && strpos($media,'//') !== 0 ) {
      $http = is_ssl() ? 'https://' : 'http://';
      // strip the first / from $media
      if($media[0]=='/') $media = substr($media, 1);
      if((dirname($_SERVER['PHP_SELF'])!='/')&&(file_exists($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$media))){  //if the site does not live in the document root
        $media = $http.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).VIDEO_DIR.$media;
      }
      else if(file_exists($_SERVER['DOCUMENT_ROOT'].VIDEO_DIR.$media)){ // if the videos folder is in the root
        $media = $http.$_SERVER['SERVER_NAME'].VIDEO_DIR.$media;//VIDEO_PATH.$media;
      }
      else{ // if the videos are not in the videos directory but they are adressed relatively
        $media_path = str_replace('//','/',$_SERVER['SERVER_NAME'].'/'.$media);
        $media = $http.$media_path;
      }
    }
    
    $media = apply_filters( 'fv_flowplayer_media', $media, $this );
    
    return $media;
  }  
  
  
  public static function is_licensed() {
    global $fv_fp;
    return preg_match( '!^\$\d+!', $fv_fp->conf['key'] );
  }
  
  
  public static function is_special_editor() {
    return flowplayer::is_optimizepress() || flowplayer::is_themify();
  }
  
  
  public static function is_optimizepress() {
    if( ( isset($_GET['page']) && $_GET['page'] == 'optimizepress-page-builder' ) ||
        ( isset($_POST['action']) && $_POST['action'] == 'optimizepress-live-editor-parse' )
      ) {
      return true;
    }
    return false;
  }
  
  
  public static function is_themify() {
    if( isset($_POST['action']) && $_POST['action'] == 'tfb_load_module_partial' ) {
      return true;
    }
    return false;
  }    
  
  
  public function is_secure_amazon_s3( $url ) {
    return preg_match( '/^.+?s3.*?\.amazonaws\.com\/.+Signature=.+?$/', $url ) || preg_match( '/^.+?\.cloudfront\.net\/.+Signature=.+?$/', $url );
  }
  
  
  function css_writeout_option() {
    if( isset($this->conf['css_disable']) && $this->conf['css_disable'] == 'true' ) {
      return false;
    }
    return true;
  }
  

  function popup_css( $css ){
    $aPopupData = get_option('fv_player_popups');
    $sNewCss = '';
    if( is_array($aPopupData) ) {
      foreach($aPopupData as $key => $val){
        if( empty($val['css']) ){
          continue;
        }
        $sNewCss .= stripslashes($val['css'])."\n";
      }
    }
    if( strlen($sNewCss) ){
      $css .= "\n/*custom popup css*/\n".$sNewCss."/*end custom popup css*/\n";
    }
    return $css;
  }  
    
  
  function rewrite_check( $aRules ) {
    $aRewriteRules = get_option('rewrite_rules');
    if( empty($aRewriteRules) ) {
      return;
    }
    
    $bFound = false;
    foreach( $aRewriteRules AS $k => $v ) {
      if( stripos($v,'&fv_player_embed=') !== false ) {
        $bFound = true;
        break;
      }
    }
    
    if( !$bFound ) {
      flush_rewrite_rules( true );
    }
  }
  
  
  function rewrite_embed( $aRules ) {
    $aRulesNew = array();
    foreach( $aRules AS $k => $v ) {
      $aRulesNew[$k] = $v;
      if( stripos($k,'/trackback/') !== false ) {
        $new_k = str_replace( '/trackback/', '/fvp/', $k );
        $new_v = str_replace( '&tb=1', '&fv_player_embed=1', $v );
        $aRulesNew[$new_k] = $new_v;
        $new_k = str_replace( '/trackback/', '/fvp(\d+)?/', $k );
        $new_v = str_replace( '&tb=1', '&fv_player_embed=$matches['.(substr_count($v,'$matches[')+1).']', $v );
        $aRulesNew[$new_k] = $new_v;        
      }
    }
    return $aRulesNew;
  }
  
  
  function rewrite_vars( $public_query_vars ) {
    $public_query_vars[] = 'fv_player_embed';
    return $public_query_vars;
  }
  
  function template_embed_buffer(){
    if( get_query_var('fv_player_embed') ) {
      ob_start();
      
    }
  }
  
  function template_embed() {
  
    if( get_query_var('fv_player_embed') ) {
      $content = ob_get_contents();
      ob_clean();

      remove_action( 'wp_footer', array( $this, 'template_embed' ),0 );
      //remove_action('wp_head', '_admin_bar_bump_cb');
      show_admin_bar(false);
      ?>
  <style>
    body { margin: 0; padding: 0; overflow:hidden; background:white;}
    body:before { height: 0px!important;}
    html {margin-top: 0px !important;}
  </style>
</head>
<body>
  <?php if( isset($_GET['fv_player_preview']) && !empty($_GET['fv_player_preview']) ) :
    
    if( !is_user_logged_in() ){
      ?><script>window.parent.jQuery(window.parent.document).trigger('fvp-preview-complete');</script><?php
      wp_die('Please log in.');
    }
    $shortcode = base64_decode($_GET['fv_player_preview']);
    $matches = null;
    $width ='';
    $height ='';
    if(preg_match('/width="([0-9.,]*)"/', $shortcode, $matches)){
      $width = 'width:'.$matches[1].'px;';
    }
    if(preg_match('/height="([0-9.,]*)"/', $shortcode, $matches)){
      $height = 'min-height:'.$matches[1].'px;';
    }
    
    ?>    
    <style>
      html {overflow-y: auto;}
    </style>    
    <div style="background:white;">
      <div id="wrapper" style="background:white; overflow:hidden; <?php echo $width . $height; ?>;">
        <?php
        if(preg_match('/src="[^"][^"]*"/i',$shortcode)) {
          echo do_shortcode($shortcode);          
        } else { ?>
          <h1 style="margin: auto;text-align: center; padding: 60px; color: darkgray;">No video.</h1>
          <?php
        }
        ?>
      </div>
    </div>
    
  <?php else : ?>
    <?php while ( have_posts() ) : the_post(); //is this needed? ?>
      <?php
  
      $bFound = false;
      $rewrite = get_option('rewrite_rules');
      if( empty($rewrite) ) {
        $sLink = 'fv_player_embed='.get_query_var('fv_player_embed');
      } else {
        $sPostfix = get_query_var('fv_player_embed') > 1 ? 'fvp'.get_query_var('fv_player_embed') : 'fvp';
        $sLink = user_trailingslashit( trailingslashit( get_permalink() ).$sPostfix );
      }
      //$content = apply_filters( 'the_content', get_the_content() );
      
      
              
      $aPlayers = explode( '<!--fv player end-->', $content );
      if( $aPlayers ) {
        foreach( $aPlayers AS $k => $v ) {
          if( stripos($v,$sLink.'"') !== false ) {
            echo substr($v, stripos($v,'<div id="wpfp_') );
            $bFound = true;
            break;
          }
        }
      }
      
      if( !$bFound ) {
        echo "<p>Player not found, see the full article: <a href='".get_permalink()."' target='_blank'>".get_the_title()."</a>.</p>";
      }    
      
      ?>
    <?php endwhile; 
  endif;
  ?>
</body>

<?php wp_footer(); ?>

<?php if( isset($_GET['fv_player_preview']) && !empty($_GET['fv_player_preview']) ) : ?>
  
  <script>
  jQuery(document).ready( function(){
    var parent = window.parent.jQuery(window.parent.document);
    if( typeof(flowplayer) != "undefined" ) {      
      parent.trigger('fvp-preview-complete');      
    } else {
      parent.trigger('fvp-preview-error');
    }
  
  });
  
  if (window.top===window.self) {
    jQuery('#wrapper').css('margin','25px 50px 0 50px');
  } 
  </script>
<?php endif; ?>

</html>       
      <?php
      exit();  
    }
  }
  
  
}




function fv_wp_flowplayer_save_post( $post_id ) {
  if( $parent_id = wp_is_post_revision($post_id) ) {
    $post_id = $parent_id;
  }
  
  global $post;
  $post_id = ( isset($post->ID) ) ? $post->ID : $post_id;
  
  global $fv_fp, $post, $FV_Player_Checker;
  if( !$FV_Player_Checker->is_cron && $FV_Player_Checker->queue_check($post_id) ) {
    //return;
  }
  
  $saved_post = get_post($post_id);
  $videos = FV_Player_Checker::get_videos($saved_post->post_content);

  $iDone = 0;
  if( is_array($videos) && count($videos) > 0 ) {
    $tStart = microtime(true);
    foreach( $videos AS $video ) {
      if( microtime(true) - $tStart > apply_filters( 'fv_flowplayer_checker_save_post_time', 5 ) ) {
        FV_Player_Checker::queue_add($post_id);
        break;
      }
      
      if( isset($post->ID) && !get_post_meta( $post->ID, flowplayer::get_video_key($video), true ) ) {
        $video_secured = $fv_fp->get_video_src( $video, array( 'dynamic' => true, 'url_only' => true, 'flash' => false ) );
        if( !is_array($video_secured) ) {
          $video_secured = array( 'media' => $video_secured );
        }
        if( isset($video_secured['media']) && $FV_Player_Checker->check_mimetype( array($video_secured['media']), array( 'meta_action' => 'check_time', 'meta_original' => $video ) ) ) {
          $iDone++;
          if( isset($_GET['fv_flowplayer_checker'] ) ) {
            echo "<p>Post $post_id video '$video' ok!</p>\n";
          }
        } else {
          if( isset($_GET['fv_flowplayer_checker'] ) ) {
            echo "<p>Post $post_id video '$video' not done, adding into queue!</p>\n";
          }
          FV_Player_Checker::queue_add($post_id);
        }
      } else {
        $iDone++;
      }
      
    }
  }

  if( !$videos || $iDone == count($videos) ) {
    FV_Player_Checker::queue_remove($post_id);
    if( isset($_GET['fv_flowplayer_checker'] ) ) {
      echo "<p>Post $post_id done, removing from queue!</p>\n";
    }
  }
}

