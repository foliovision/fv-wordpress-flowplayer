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

add_action('wp_footer','flowplayer_prepare_scripts',9);
add_action('wp_footer','flowplayer_display_scripts',100);
add_action('widget_text','do_shortcode');

add_filter( 'run_ngg_resource_manager', '__return_false' );


function fv_flowplayer_remove_bad_scripts() {  
  global $wp_scripts;
  if( isset($wp_scripts->registered['flowplayer']) && isset($wp_scripts->registered['flowplayer']->src) && stripos($wp_scripts->registered['flowplayer']->src, 'fv-wordpress-flowplayer') === false ) {
    wp_deregister_script( 'flowplayer' );
  }
}
add_action( 'wp_print_scripts', 'fv_flowplayer_remove_bad_scripts', 100 );

add_filter( 'run_ngg_resource_manager', '__return_false' ); //  Nextgen Gallery compatibility fix

function fv_flowplayer_ap_action_init(){
  // Localization
  load_plugin_textdomain('fv-wordpress-flowplayer', false, dirname(dirname(plugin_basename(__FILE__))) . "/languages");
}
add_action('init', 'fv_flowplayer_ap_action_init');

function fv_flowplayer_get_js_translations() {
  
  $sWhy = __(' <a target="_blank" href="https://foliovision.com/2017/05/issues-with-vimeo-on-android">Why?</a>','fv-wordpress-flowplayer');
  
  $aStrings = array(
  0 => '',
  1 => __('Video loading aborted', 'fv-wordpress-flowplayer'),
  2 => __('Network error', 'fv-wordpress-flowplayer'),
  3 => __('Video not properly encoded', 'fv-wordpress-flowplayer'),
  4 => __('Video file not found', 'fv-wordpress-flowplayer'),
  5 => __('Unsupported video', 'fv-wordpress-flowplayer'),
  6 => __('Skin not found', 'fv-wordpress-flowplayer'),
  7 => __('SWF file not found', 'fv-wordpress-flowplayer'),
  8 => __('Subtitles not found', 'fv-wordpress-flowplayer'),
  9 => __('Invalid RTMP URL', 'fv-wordpress-flowplayer'),
  10 => __('Unsupported video format. Try installing Adobe Flash.', 'fv-wordpress-flowplayer'),  
  11 => __('Click to watch the video', 'fv-wordpress-flowplayer'),
  12 => __('[This post contains video, click to play]', 'fv-wordpress-flowplayer'),
  'video_expired' => __('<h2>Video file expired.<br />Please reload the page and play it again.</h2>', 'fv-wordpress-flowplayer'),
  'unsupported_format' => __('<h2>Unsupported video format.<br />Please use a Flash compatible device.</h2>','fv-wordpress-flowplayer'),
  'mobile_browser_detected_1' => __('Mobile browser detected, serving low bandwidth video.','fv-wordpress-flowplayer'),
  'mobile_browser_detected_2' => __('Click here for full quality','fv-wordpress-flowplayer'),
  'live_stream_failed' => __('<h2>Live stream load failed.</h2><h3>Please try again later, perhaps the stream is currently offline.</h3>','fv-wordpress-flowplayer'),
  'live_stream_failed_2' => __('<h2>Live stream load failed.</h2><h3>Please try again later, perhaps the stream is currently offline.</h3>','fv-wordpress-flowplayer'),
  'what_is_wrong' => __('Please tell us what is wrong :','fv-wordpress-flowplayer'),
  'full_sentence' => __('Please give us more information (a full sentence) so we can help you better','fv-wordpress-flowplayer'),
  'error_JSON' =>__('Admin: Error parsing JSON','fv-wordpress-flowplayer'),
  'no_support_IE9' =>__('Admin: Video checker doesn\'t support IE 9.','fv-wordpress-flowplayer'),
  'check_failed' =>__('Admin: Check failed.','fv-wordpress-flowplayer'),
  'playlist_current' =>__('Now Playing','fv-wordpress-flowplayer'),
  'playlist_item_no' =>__('Item %d.','fv-wordpress-flowplayer'),
  'playlist_play_all' =>__('Play All','fv-wordpress-flowplayer'),
  'playlist_play_all_button' =>__('All','fv-wordpress-flowplayer'),
  'playlist_replay_all' =>__('Replay Playlist','fv-wordpress-flowplayer'),
  'playlist_replay_video' =>__('Repeat Track','fv-wordpress-flowplayer'),
  'playlist_shuffle' =>__('Shuffle Playlist','fv-wordpress-flowplayer'),  
  'video_issues' =>__('Video Issues','fv-wordpress-flowplayer'),
  'video_reload' =>__('Video loading has stalled, click to reload','fv-wordpress-flowplayer'),
  'link_copied' =>__('Video Link Copied to Clipboard','fv-wordpress-flowplayer'),
  'live_stream_starting'=>__('<h2>Live stream scheduled</h2><p>Starting in <span>%d</span>.</p>', 'fv-wordpress-flowplayer'),
  'live_stream_retry'=>__( '<h2>We are sorry, currently no live stream available.</h2><p>Retrying in <span>%d</span> ...</p>', 'fv-wordpress-flowplayer'),
  'live_stream_continue'=>__( '<h2>It appears the stream went down.</h2><p>Retrying in <span>%d</span> ...</p>', 'fv-wordpress-flowplayer'),
  'embed_copied' =>__('Embed Code Copied to Clipboard','fv-wordpress-flowplayer'),
  'subtitles_disabled' =>__('Subtitles disabled','fv-wordpress-flowplayer'),
  'subtitles_switched' =>__('Subtitles switched to ','fv-wordpress-flowplayer'),
  'warning_iphone_subs' => __('This video has subtitles, that are not supported on your device.','fv-wordpress-flowplayer'),
  'warning_unstable_android' => __('You are using an old Android device. If you experience issues with the video please use <a href="https://play.google.com/store/apps/details?id=org.mozilla.firefox">Firefox</a>.','fv-wordpress-flowplayer').$sWhy,
  'warning_samsungbrowser' => __('You are using the Samsung Browser which is an older and buggy version of Google Chrome. If you experience issues with the video please use <a href="https://www.mozilla.org/en-US/firefox/new/">Firefox</a> or other modern browser.','fv-wordpress-flowplayer').$sWhy,
  'warning_old_safari' => __('You are using an old Safari browser. If you experience issues with the video please use <a href="https://www.mozilla.org/en-US/firefox/new/">Firefox</a> or other modern browser.','fv-wordpress-flowplayer'),
  'warning_old_chrome' => __('You are using an old Chrome browser. Please make sure you use the latest version.','fv-wordpress-flowplayer'),
  'warning_old_firefox' => __('You are using an old Firefox browser. Please make sure you use the latest version.','fv-wordpress-flowplayer'),
  'warning_old_ie' => __('You are using a deprecated browser. If you experience issues with the video please use <a href="https://www.mozilla.org/en-US/firefox/new/">Firefox</a> or other modern browser.','fv-wordpress-flowplayer'),
  'quality' => __('Quality','fv-wordpress-flowplayer'),
  'closed_captions' => __('Closed Captions', 'fv-wordpress-flowplayer'),
  'no_subtitles' => __('No subtitles', 'fv-wordpress-flowplayer'),
  'speed' => __('Speed', 'fv-wordpress-flowplayer'),
  'duration_1_day' => __( "%s day" ),
  'duration_n_days' => _n( '%s day', '%s days', 5 ),
  'duration_1_hour' => __( "%s hour" ),
  'duration_n_hours' => _n( '%s hour', '%s hours', 5 ),
  'duration_1_minute' => __( "%s min" ),
  'duration_n_minutes' => _n( '%s min', '%s mins', 5 ),
  'duration_1_second' => __( "%s second" ),
  'duration_n_seconds' =>  _n( '%s second', '%s seconds', 5 ),
  'and' => sprintf( __( '%1$s and %2$s' ), '', '' ),
  'chrome_extension_disable_html5_autoplay' => __('It appears you are using the Disable HTML5 Autoplay Chrome extension, disable it to play videos', 'fv-wordpress-flowplayer'),
  'click_to_unmute' => __('Click to unmute', 'fv-wordpress-flowplayer'),
  'audio_button' => __('AUD', 'fv-wordpress-flowplayer'),
  'audio_menu' => __('Audio', 'fv-wordpress-flowplayer')
);

  return $aStrings;
} 

/**
 * Replaces the flowplayer tags in post content by players and fills the $GLOBALS['fv_fp_scripts'] array.
 * @param string Content to be parsed
 * @return string Modified content string
 */
function flowplayer_content( $content ) {
  global $fv_fp;

  $content_matches = array();
  preg_match_all('/\[(flowplayer|fvplayer)\ [^\]]+\]/i', $content, $content_matches);
  
  // process all found tags
  foreach ($content_matches[0] as $tag) {
    $ntag = str_replace("\'",'&#039;',$tag);
    //search for URL
    preg_match("/src='([^']*?)'/i",$ntag,$tmp);
    if( $tmp[1] == NULL ) {
      preg_match_all("/src=([^,\s\]]*)/i",$ntag,$tmp);
      $media = $tmp[1][0];
    }
    else
      $media = $tmp[1]; 
    
    //strip the additional /videos/ from the beginning if present  
    preg_match('/(.*)\/videos\/(.*)/',$media,$matches);
    if ($matches[0] == NULL)
      $media = $media;
    else if ($matches[1] == NULL) {
      $media = $matches[2];
    }
    else {
      $media = $matches[2];
    }
    
    unset($arguments['src']);
    unset($arguments['src1']);
    unset($arguments['src2']);        
    unset($arguments['width']);
    unset($arguments['height']);
    unset($arguments['autoplay']);
    unset($arguments['splash']);
    unset($arguments['splashend']);
    unset($arguments['popup']);
    unset($arguments['controlbar']);
    unset($arguments['redirect']);
    unset($arguments['loop']);
    
    //width and heigth
    preg_match("/width=(\d*)/i",$ntag,$width);
    preg_match("/height=(\d*)/i",$ntag,$height);
    if( $width[1] != NULL)
      $arguments['width'] = $width[1];
    if( $height[1] != NULL)
      $arguments['height'] = $height[1];
      
    //search for redirect
    preg_match("/redirect='([^']*?)'/i",$ntag,$tmp);
    if ($tmp[1])
      $arguments['redirect'] = $tmp[1];
    
    //search for autoplay
    preg_match("/[\s]+autoplay([\s]|])+/i",$ntag,$tmp);
    if (isset($tmp[0])){
      $arguments['autoplay'] = true;
    }
    else {
      preg_match("/autoplay='([A-Za-z]*)'/i",$ntag,$tmp);
      if ( $tmp[1] == NULL )
        preg_match("/autoplay=([A-Za-z]*)/i",$ntag,$tmp);
      if (isset($tmp[1]))
        $arguments['autoplay'] = $tmp[1];
    }
    
    //search for popup in quotes
    preg_match("/popup='([^']*?)'/i",$ntag,$tmp);
    if ($tmp[1])
      $arguments['popup'] = $tmp[1];
    
    //search for loop
    preg_match("/[\s]+loop([\s]|])+/i",$ntag,$tmp);
    if (isset($tmp[0])){
      $arguments['loop'] = true;
    }
    else {
      preg_match("/loop='([A-Za-z]*)'/i",$ntag,$tmp);
      if ( $tmp[1] == NULL )
        preg_match("/loop=([A-Za-z]*)/i",$ntag,$tmp);
      if (isset($tmp[1]))
        $arguments['loop'] = $tmp[1];
    }
    
    //  search for splash image
    preg_match("/splash='([^']*?)'/i",$ntag,$tmp);   //quotes version
     if( $tmp[1] == NULL ) {
      preg_match_all("/splash=([^,\s\]]*)/i",$ntag,$tmp);  //non quotes version
      preg_match('/(.*)\/videos\/(.*)/i',$tmp[1][0],$matches);
       if ($matches[0] == NULL)
        $arguments['splash'] = $tmp[1][0];
       else if ($matches[1] == NULL) {
        $arguments['splash'] = $matches[2];//$tmp[1][0];
      }
       else {
        $arguments['splash'] = $matches[2];
      }
    }
    else {
      preg_match('/(.*)\/videos\/(.*)/',$tmp[1],$matches);
      if ($matches[0] == NULL)
        $arguments['splash'] = $tmp[1];
      elseif ($matches[1] == NULL)
        $arguments['splash'] = $matches[2];
      else
        $arguments['splash'] = $matches[2];//$tmp[1];
    }
    
    //  search for src1
    preg_match("/src1='([^']*?)'/i",$ntag,$tmp);   //quotes version
     if( $tmp[1] == NULL ) {
      preg_match_all("/src1=([^,\s\]]*)/i",$ntag,$tmp);  //non quotes version
      preg_match('/(.*)\/videos\/(.*)/i',$tmp[1][0],$matches);
       if ($matches[0] == NULL)
        $arguments['src1'] = $tmp[1][0];
       else if ($matches[1] == NULL) {
        $arguments['src1'] = $matches[2];//$tmp[1][0];
      }
       else {
        $arguments['src1'] = $matches[2];
      }
    }
    else {
      preg_match('/(.*)\/videos\/(.*)/',$tmp[1],$matches);
      if ($matches[0] == NULL)
        $arguments['src1'] = $tmp[1];
      elseif ($matches[1] == NULL)
        $arguments['src1'] = $matches[2];
      else
        $arguments['src1'] = $matches[2];//$tmp[1];
    }
    
    //  search for src1
    preg_match("/src2='([^']*?)'/i",$ntag,$tmp);   //quotes version
     if( $tmp[1] == NULL ) {
      preg_match_all("/src2=([^,\s\]]*)/i",$ntag,$tmp);  //non quotes version
      preg_match('/(.*)\/videos\/(.*)/i',$tmp[1][0],$matches);
       if ($matches[0] == NULL)
        $arguments['src2'] = $tmp[1][0];
       else if ($matches[1] == NULL) {
        $arguments['src2'] = $matches[2];//$tmp[1][0];
      }
       else {
        $arguments['src2'] = $matches[2];
      }
    }
    else {
      preg_match('/(.*)\/videos\/(.*)/',$tmp[1],$matches);
      if ($matches[0] == NULL)
        $arguments['src2'] = $tmp[1];
      elseif ($matches[1] == NULL)
        $arguments['src2'] = $matches[2];
      else
        $arguments['src2'] = $matches[2];//$tmp[1];
    }
    
    //search for splashend
    preg_match("/[\s]+splashend([\s]|])+/i",$ntag,$tmp);
    if (isset($tmp[0])){
      $arguments['splashend'] = true;
    }
    else {
      preg_match("/splashend='([A-Za-z]*)'/i",$ntag,$tmp);
      if ( $tmp[1] == NULL )
        preg_match("/splashend=([A-Za-z]*)/i",$ntag,$tmp);
      if (isset($tmp[1]))
        $arguments['splashend'] = $tmp[1];
    }
    
    //search for controlbar
    preg_match("/[\s]+controlbar([\s]|])+/i",$ntag,$tmp);
    if (isset($tmp[0])){
      $arguments['controlbar'] = true;
    }
    else {
      preg_match("/controlbar='([A-Za-z]*)'/i",$ntag,$tmp);
      if ( $tmp[1] == NULL )
        preg_match("/controlbar=([A-Za-z]*)/i",$ntag,$tmp);
      if (isset($tmp[1]))
        $arguments['controlbar'] = $tmp[1];
    }
    
    if (trim($media) != '') {
      // build new player
      $new_player = $fv_fp->build_min_player($media,$arguments);
      $content = str_replace($tag, $new_player['html'],$content);
      if (!empty($new_player['script'])) {
        $GLOBALS['fv_fp_scripts'] = $new_player['script'];
      }
    }
  }
  return $content;
}

function flowplayer_prepare_scripts() {
  global $fv_fp, $fv_wp_flowplayer_ver;
  
  //  don't load script in Optimize Press 2 preview
  if( flowplayer::is_special_editor() ) {
    return;  
  }

  if(
     isset($GLOBALS['fv_fp_scripts']) ||
     $fv_fp->should_force_load_js() ||
     isset($_GET['fv_wp_flowplayer_check_template']) 
  ){
    
    $aDependencies = array('jquery');
    if( $fv_fp->should_force_load_js() || $fv_fp->load_tabs ) {
      wp_enqueue_script('jquery-ui-tabs', false, array('jquery','jquery-ui-core'), $fv_wp_flowplayer_ver, true);
      $aDependencies[] = 'jquery-ui-tabs';
    }
    
    if( !$fv_fp->bCSSLoaded ) $fv_fp->css_enqueue(true);
    
    $sPluginUrl = preg_replace( '~^.*://~', '//', FV_FP_RELATIVE_PATH );
  
    $sCommercialKey = $fv_fp->_get_option('key') ? $fv_fp->_get_option('key') : '';
    $sLogo = $sCommercialKey && $fv_fp->_get_option('logo') ? $fv_fp->_get_option('logo') : '';
    
    $aConf = array( 'fv_fullscreen' => true, 'swf' => $sPluginUrl.'/flowplayer/flowplayer.swf?ver='.$fv_wp_flowplayer_ver, 'swfHls' => $sPluginUrl.'/flowplayer/flowplayerhls.swf?ver='.$fv_wp_flowplayer_ver );
    
    // Load base Flowplayer library
    $path = '/flowplayer/modules/flowplayer.min.js';
    if( file_exists(dirname(__FILE__).'/../flowplayer/modules/flowplayer.js') ) {
      $path = '/flowplayer/modules/flowplayer.js';
    }
    
    $version = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? filemtime( dirname(__FILE__).'/../'.$path ) : $fv_wp_flowplayer_ver;
    
    wp_enqueue_script( 'flowplayer', flowplayer::get_plugin_url().$path, $aDependencies, $version, true );
    $aDependencies[] = 'flowplayer';
    
    // Load modules
    if( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
      $path = '/flowplayer/modules/fv-player.js';
      wp_enqueue_script( 'fv-player', flowplayer::get_plugin_url().$path, $aDependencies, filemtime( dirname(__FILE__).'/../'.$path ), true );
      $aDependencies[] = 'fv-player';
      
      foreach( glob( dirname(dirname(__FILE__)).'/flowplayer/modules/*.module.js') as $filename ) {
        $path = '/flowplayer/modules/'.basename($filename);
        wp_enqueue_script( 'fv-player-'.basename($filename), flowplayer::get_plugin_url().$path, $aDependencies, filemtime( dirname(__FILE__).'/../'.$path ), true);
      }
      
    } else {
      wp_enqueue_script( 'fv-player', flowplayer::get_plugin_url().'/flowplayer/fv-player.min.js', $aDependencies, $fv_wp_flowplayer_ver, true );
      
    }

    if( current_user_can('manage_options') && !$fv_fp->_get_option('disable_videochecker') && ( $fv_fp->_get_option('video_checker_agreement') || $fv_fp->_get_option('key_automatic') ) ) {
      wp_enqueue_script( 'fv-player-video-checker', flowplayer::get_plugin_url().'/js/video-checker.js', array('flowplayer'), $fv_wp_flowplayer_ver, true );
      $aConf['video_checker'] = true;
    }

    if( $fv_fp->_get_option('rtmp-live-buffer') ) {
      $aConf['bufferTime'] = apply_filters( 'fv_player_rtmp_bufferTime', 3 );
    }
   
    if( $fv_fp->_get_option('ui_speed_increment') == 0.25){
      $aConf['speeds'] = array( 0.25,0.5,0.75,1,1.25,1.5,1.75,2 );
    }elseif( $fv_fp->_get_option('ui_speed_increment') == 0.1){
      $aConf['speeds'] = array( 0.25,0.3,0.4,0.5,0.6,0.7,0.8,0.9,1,1.1,1.2,1.3,1.4,1.5,1.6,1.7,1.8,1.9,2 );
    }elseif( $fv_fp->_get_option('ui_speed_increment') == 0.5){
      $aConf['speeds'] = array( 0.5,1,1.5,2 );
    }

    $aConf['video_hash_links'] = empty($fv_fp->aCurArgs['linking']) ? !$fv_fp->_get_option('disable_video_hash_links' ) : $fv_fp->aCurArgs['linking'] === 'true';
    
    if( $sCommercialKey ) $aConf['key'] = base64_encode($sCommercialKey);
    if( apply_filters( 'fv_flowplayer_safety_resize', true) && !$fv_fp->_get_option('fixed_size') ) {
      $aConf['safety_resize'] = true;
    }
    if( $fv_fp->_get_option('cbox_compatibility') ) {
      $aConf['cbox_compatibility'] = true;
    }    
    if( current_user_can('manage_options') && !$fv_fp->_get_option('disable_videochecker') ) {
      $aConf['video_checker_site'] = home_url();
    }
    if( $sLogo ) $aConf['logo'] = $sLogo;

    // Used to restore volume, removed in JS if volume stored in browser localStorage    
    $aConf['volume'] = floatval( $fv_fp->_get_option('volume') );
    if( $aConf['volume'] > 1 ) {
      $aConf['volume'] = 1;
    }
    
    $aConf['default_volume'] = $aConf['volume'];
    
    if( $val = $fv_fp->_get_option('mobile_native_fullscreen') ) $aConf['mobile_native_fullscreen'] = $val;
    if( $val = $fv_fp->_get_option('mobile_force_fullscreen') ) $aConf['mobile_force_fullscreen'] = $val;
    if( $val = $fv_fp->_get_option('mobile_alternative_fullscreen') ) $aConf['mobile_alternative_fullscreen'] = $val;
    if( $val = $fv_fp->_get_option('mobile_landscape_fullscreen') ) $aConf['mobile_landscape_fullscreen'] = $val;

    if ( $fv_fp->_get_option('video_position_save_enable') ) {
      $aConf['video_position_save_enable'] = $fv_fp->_get_option('video_position_save_enable');
    }

    if( is_user_logged_in() ) $aConf['is_logged_in'] = true;

    $aConf['sticky_video'] = $fv_fp->_get_option('sticky_video');
    $aConf['sticky_place'] = $fv_fp->_get_option('sticky_place');
    $aConf['sticky_width'] = $fv_fp->_get_option('sticky_width');
       
    global $post;
    if( $post && isset($post->ID) && $post->ID > 0 ) {
      if( get_post_meta($post->ID, 'fv_player_mobile_native_fullscreen', true) ) $aConf['mobile_native_fullscreen'] = true;
      if( get_post_meta($post->ID, 'fv_player_mobile_force_fullscreen', true) ) $aConf['mobile_force_fullscreen'] = true;
    }
    
    if( $fv_fp->should_force_load_js() || $fv_fp->load_hlsjs ) {
      wp_enqueue_script( 'flowplayer-hlsjs', flowplayer::get_plugin_url().'/flowplayer/hls.min.js', array('flowplayer'), '1.0.4', true );
    }
    $aConf['script_hls_js'] = flowplayer::get_plugin_url().'/flowplayer/hls.min.js?ver=1.0.4';
        
    if( $fv_fp->should_force_load_js() || $fv_fp->load_dash ) {
      wp_enqueue_script( 'flowplayer-dash', flowplayer::get_plugin_url().'/flowplayer/flowplayer.dashjs.min.js', array('flowplayer'), $fv_wp_flowplayer_ver, true );
    }
    $aConf['script_dash_js'] = flowplayer::get_plugin_url().'/flowplayer/flowplayer.dashjs.min.js?ver='.$fv_wp_flowplayer_ver;
    $aConf['script_dash_js_version'] = '2.7';
        
    if( $fv_fp->_get_option('googleanalytics') ) {
      $aConf['fvanalytics'] = $fv_fp->_get_option('googleanalytics');
    }
    
    if( $fv_fp->_get_option('matomo_domain') && $fv_fp->_get_option('matomo_site_id') ) {
      // take the domain name from Matomo Domain setting in case somebody entered full URL
      $matomo_domain = $fv_fp->_get_option('matomo_domain');
      $parsed = parse_url($matomo_domain);
      if( $parsed && !empty($parsed['host']) ) { 
        $matomo_domain = $parsed['host'];
        if( !empty($parsed['path']) ) { 
          $matomo_domain .= '/'.$parsed['path'];
        }
      }
      $aConf['matomo_domain'] = $matomo_domain;
      $aConf['matomo_site_id'] = $fv_fp->_get_option('matomo_site_id');
    }

    $aConf['chromecast'] = false; // tell core Flowplayer and FV Player Pro <= 7.4.43.727 to not load Chromecast
    if( $fv_fp->_get_option('chromecast') ) {
      $aConf['fv_chromecast'] = $fv_fp->_get_option('chromecast');
    }

    if( $fv_fp->_get_option('hd_streaming') ) {
      $aConf['hd_streaming'] = true;
    }

    if( $fv_fp->_get_option('multiple_playback') ) {
      $aConf['multiple_playback'] = true;
    }

    if( $fv_fp->_get_option('disable_localstorage') ) {
      $aConf['disable_localstorage'] = true;
    }

    $aConf['hlsjs'] = array(
      'startLevel' => -1,
      'fragLoadingMaxRetry' => 3,
      'levelLoadingMaxRetry' => 3,
      'capLevelToPlayerSize' => true
    );
    
    // The above HLS.js config doesn't work well on Chrome and Firefox, so we detect that in JS and use this config for it instead. Todo: make this a per-video thing
    if( class_exists('FV_Player_Pro_DaCast') ) {
      $aConf['dacast_hlsjs'] = array(        
        'autoLevelEnabled' => false // disable ABR. If you set startLevel or capLevelToPlayerSize it will be enabled again. So this way everybody on desktop gets top quality and they have to switch to lower each time.
      );
    }
    
    if( is_admin() ) $aConf['wpadmin'] = true;
    
    $aConf = apply_filters( 'fv_flowplayer_conf', $aConf );
    $aLocalize = array();

    wp_localize_script( 'flowplayer', 'fv_flowplayer_conf', $aConf );
    if( current_user_can('manage_options') ) {
      $aLocalize['admin_input'] = true;
      $aLocalize['admin_js_test'] = true;
    }

    if( current_user_can('edit_posts') ) {
      $aLocalize['user_edit'] = true;
    }
    
    $aLocalize['ajaxurl'] = site_url().'/wp-admin/admin-ajax.php';

    wp_localize_script( 'flowplayer', 'fv_player', $aLocalize );

    wp_localize_script( 'flowplayer', 'fv_flowplayer_translations', fv_flowplayer_get_js_translations());
    wp_localize_script( 'flowplayer', 'fv_flowplayer_playlists', array() );   //  has to be defined for FV Player Pro 0.6.20 and such
    
    if( isset($GLOBALS['fv_fp_scripts']) && count($GLOBALS['fv_fp_scripts']) > 0 ) {
      foreach( $GLOBALS['fv_fp_scripts'] AS $sKey => $aScripts ) {
        wp_localize_script( 'flowplayer', $sKey.'_array', $aScripts );
      }
    }
    
  }
  
  FV_Player_lightbox()->maybe_load();
}

/**
 * Prints flowplayer javascript content to the bottom of the page.
 */
function flowplayer_display_scripts() {
  if( flowplayer::is_special_editor() ) {
    return;  
  }  

  if( is_user_logged_in() || isset($_GET['fv_wp_flowplayer_check_template']) ) {
    echo "\n<!--fv-flowplayer-footer-->\n\n";
  }
}

/**
 * This is the template tag. Use the standard Flowplayer shortcodes
 */
function flowplayer($shortcode) {
  echo apply_filters('the_content',$shortcode);
}


/*
Make sure our div won't be wrapped in any P tag and that html attributes don't break the shortcode
*/
function fv_flowplayer_the_content( $c ) {
  if( flowplayer::is_special_editor() ) {
    return $c;  
  }    
  
  $c = preg_replace( '!<p[^>]*?>(\[(?:fvplayer|flowplayer).*?[^\\\]\])</p>!', "\n".'$1'."\n", $c );
  $c = preg_replace_callback( '!\[(?:fvplayer|flowplayer).*?[^\\\]\]!', 'fv_flowplayer_shortfcode_fix_attrs', $c );
  return $c;
}
add_filter( 'the_content', 'fv_flowplayer_the_content', 0 );


function fv_flowplayer_shortfcode_fix_attrs( $aMatch ) {
  $aMatch[0] = preg_replace_callback( '!(?:ad|popup)="(.*?[^\\\])"!', 'fv_flowplayer_shortfcode_fix_attr', $aMatch[0] );
  return $aMatch[0];
}


function fv_flowplayer_shortfcode_fix_attr( $aMatch ) {
  $aMatch[0] = str_replace( $aMatch[1], '<!--fv_flowplayer_base64_encoded-->'.base64_encode($aMatch[1]), $aMatch[0] );
  return $aMatch[0];
}


/*
Handle attachment pages which contain videos
*/
function fv_flowplayer_attachment_page_video( $c ) {
  global $post;
  if( stripos($post->post_mime_type, 'video/') !== 0 && stripos($post->post_mime_type, 'audio/') !== 0 ) {
    return $c;
  }
  
  if( !$src = wp_get_attachment_url($post->ID) ) {
    return $c;
  }

  $meta = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
  $size = (isset($meta['width']) && isset($meta['height']) && intval($meta['width'])>0 && intval($meta['height'])>0 ) ? ' width="'.intval($meta['width']).'" height="'.intval($meta['height']).'"' : false;
  
  $shortcode = '[fvplayer src="'.$src.'"'.$size.']';
  
  $c = preg_replace( '~<p class=.attachment.[\s\S]*?</p>~', $shortcode, $c );
  $c = preg_replace( '~<div[^>]*?class="[^"]*?wp-video[^"]*?"[^>]*?>[\s\S]*?<video.*?</video></div>~', $shortcode, $c );

  return $c;
}
add_filter( 'prepend_attachment', 'fv_flowplayer_attachment_page_video' );


function fv_player_caption( $caption ) {
  global $post, $authordata;
  $sAuthorInfo = ( $authordata ) ? sprintf( '<a href="%1$s" title="%2$s" rel="author">%3$s</a>', esc_url( get_author_posts_url( $authordata->ID, $authordata->user_nicename ) ), esc_attr( sprintf( __( 'Posts by %s' ), get_the_author() ) ), get_the_author() ) : false;
  $caption = str_replace(
                         array(
                               '%post_title%',
                               '%post_date%',
                               '%post_author%',
                               '%post_author_name%'
                               ),
                         array(
                               get_the_title(),
                               get_the_date(),
                               $sAuthorInfo,
                               get_the_author()
                              ),
                        $caption );
  return $caption;
}
add_filter( 'fv_player_caption', 'fv_player_caption' );


add_filter( 'comment_text', 'fv_player_comment_text', 0 );
add_filter( 'bp_get_activity_content_body', 'fv_player_comment_text', 6 );
add_filter( 'bbp_get_topic_content', 'fv_player_comment_text', 0 );
add_filter( 'bbp_get_reply_content', 'fv_player_comment_text', 0 );

function fv_player_comment_text( $comment_text ) {
  if( is_admin() ) return $comment_text;
  
  global $fv_fp;
  if( isset($fv_fp->conf['parse_comments']) && $fv_fp->conf['parse_comments'] == 'true' ) {
    add_filter('comment_text', 'do_shortcode');
    add_filter('bbp_get_topic_content', 'do_shortcode', 11);
    add_filter('bbp_get_reply_content', 'do_shortcode', 11);

    if( stripos($comment_text,'youtube.com') !== false || stripos($comment_text,'youtu.be') !== false ) {
      $pattern = '#(?:<iframe[^>]*?src=[\'"])?((?:https?://|//)?' # Optional URL scheme. Either http, or https, or protocol-relative.
               . '(?:www\.|m\.)?'      #  Optional www or m subdomain.
               . '(?:'                 #  Group host alternatives:
               .   'youtu\.be/'        #    Either youtu.be,
               .   '|youtube\.com/'    #    or youtube.com
               .     '(?:'             #    Group path alternatives:
               .       'embed/'        #      Either /embed/,
               .       '|v/'           #      or /v/,
               .       '|watch\?v='    #      or /watch?v=,
               .       '|watch\?.+&v=' #      or /watch?other_param&v=
               .     ')'               #    End path alternatives.
               . ')'                   #  End host alternatives.
               . '([\w-]{11})'         # 11 characters (Length of Youtube video ids).
               . '(?![\w-]))(?:.*?</iframe>)?#';         # Rejects if overlong id.
      $comment_text = preg_replace( $pattern, '[fvplayer src="$1"]', $comment_text );
    }

    if( stripos($comment_text,'vimeo.com') !== false ) {
      $pattern = '#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[/a-z]*/)*([0-9]{6,11})[?]?.*#';
      $comment_text = preg_replace( $pattern, '[fvplayer src="https://vimeo.com/$1"]', $comment_text );
    }
  }
  
  return $comment_text;
}

add_action( 'fv_player_extensions_admin_load_assets', 'fv_player_footer_svg_playlist' );

function fv_player_footer_svg_playlist() {
  if( file_exists(dirname( __FILE__ ) . '/../css/fvp-icon-sprite.svg') ) {
    include_once(dirname( __FILE__ ) . '/../css/fvp-icon-sprite.svg');
  }
}

add_action( 'fv_player_extensions_admin_load_assets', 'fv_player_footer_svg_rewind' );

function fv_player_footer_svg_rewind() {
  ?>
<svg style="position: absolute; width: 0; height: 0; overflow: hidden;" class="fvp-icon" xmlns="http://www.w3.org/2000/svg">
  <g id="fvp-rewind">
    <path d="M22.7 10.9c0 1.7-0.4 3.3-1.1 4.8 -0.7 1.5-1.8 2.8-3.2 3.8 -0.4 0.3-1.3-0.9-0.9-1.2 1.2-0.9 2.1-2 2.7-3.3 0.7-1.3 1-2.7 1-4.1 0-2.6-0.9-4.7-2.7-6.5 -1.8-1.8-4-2.7-6.5-2.7 -2.5 0-4.7 0.9-6.5 2.7 -1.8 1.8-2.7 4-2.7 6.5 0 2.4 0.8 4.5 2.5 6.3 1.7 1.8 3.7 2.7 6.1 2.9l-1.2-2c-0.2-0.3 0.9-1 1.1-0.7l2.3 3.7c0.2 0.3 0 0.6-0.2 0.7L9.5 23.8c-0.3 0.2-0.9-0.9-0.5-1.2l2.1-1.1c-2.7-0.2-5-1.4-6.9-3.4 -1.9-2-2.8-4.5-2.8-7.2 0-3 1.1-5.5 3.1-7.6C6.5 1.2 9 0.2 12 0.2c3 0 5.5 1.1 7.6 3.1C21.7 5.4 22.7 7.9 22.7 10.9z"  fill="#fff"/><path d="M8.1 15.1c-0.1 0-0.1 0-0.1-0.1V8C8 7.7 7.8 7.9 7.7 7.9L6.8 8.3C6.8 8.4 6.7 8.3 6.7 8.2L6.3 7.3C6.2 7.2 6.3 7.1 6.4 7.1l2.7-1.2c0.1 0 0.4 0 0.4 0.3v8.8c0 0.1 0 0.1-0.1 0.1H8.1z" fill="#fff"/><path d="M17.7 10.6c0 2.9-1.3 4.7-3.5 4.7 -2.2 0-3.5-1.8-3.5-4.7s1.3-4.7 3.5-4.7C16.4 5.9 17.7 7.7 17.7 10.6zM12.3 10.6c0 2.1 0.7 3.4 2 3.4 1.3 0 2-1.2 2-3.4 0-2.1-0.7-3.4-2-3.4C13 7.2 12.3 8.5 12.3 10.6z" fill="#fff"/>
  </g>
</svg>
<svg style="position: absolute; width: 0; height: 0; overflow: hidden;" class="fvp-icon" xmlns="http://www.w3.org/2000/svg">
  <g id="fvp-forward">
    <path d="M22.7 10.9c0 1.7-0.4 3.3-1.1 4.8 -0.7 1.5-1.8 2.8-3.2 3.8 -0.4 0.3-1.3-0.9-0.9-1.2 1.2-0.9 2.1-2 2.7-3.3 0.7-1.3 1-2.7 1-4.1 0-2.6-0.9-4.7-2.7-6.5 -1.8-1.8-4-2.7-6.5-2.7 -2.5 0-4.7 0.9-6.5 2.7 -1.8 1.8-2.7 4-2.7 6.5 0 2.4 0.8 4.5 2.5 6.3 1.7 1.8 3.7 2.7 6.1 2.9l-1.2-2c-0.2-0.3 0.9-1 1.1-0.7l2.3 3.7c0.2 0.3 0 0.6-0.2 0.7L9.5 23.8c-0.3 0.2-0.9-0.9-0.5-1.2l2.1-1.1c-2.7-0.2-5-1.4-6.9-3.4 -1.9-2-2.8-4.5-2.8-7.2 0-3 1.1-5.5 3.1-7.6C6.5 1.2 9 0.2 12 0.2c3 0 5.5 1.1 7.6 3.1C21.7 5.4 22.7 7.9 22.7 10.9z"  fill="#fff" transform="scale(-1,1) translate(-24,0)" /><path d="M8.1 15.1c-0.1 0-0.1 0-0.1-0.1V8C8 7.7 7.8 7.9 7.7 7.9L6.8 8.3C6.8 8.4 6.7 8.3 6.7 8.2L6.3 7.3C6.2 7.2 6.3 7.1 6.4 7.1l2.7-1.2c0.1 0 0.4 0 0.4 0.3v8.8c0 0.1 0 0.1-0.1 0.1H8.1z" fill="#fff" /><path d="M17.7 10.6c0 2.9-1.3 4.7-3.5 4.7 -2.2 0-3.5-1.8-3.5-4.7s1.3-4.7 3.5-4.7C16.4 5.9 17.7 7.7 17.7 10.6zM12.3 10.6c0 2.1 0.7 3.4 2 3.4 1.3 0 2-1.2 2-3.4 0-2.1-0.7-3.4-2-3.4C13 7.2 12.3 8.5 12.3 10.6z" fill="#fff" />
  </g>
</svg>
  <?php
}

add_filter( 'script_loader_tag', 'fv_player_js_loader_mark_scripts', PHP_INT_MAX, 2 );

/*
 * Alters all the script tags related to FV Player, with excetption of the base FV Player library.
 * The reason is that it's a dependency of most of the modules so then each module would have to be 
 * adjusted to be able to load without it.
 * 
 * Fancybox lightbox library with additional code is also excluded.
 * 
 * @param string $tag The original script tag.
 * @param string $handle The WordPress script handle
 * 
 * @global object $fv_fp The FV Player plugin instance
 * 
 * @return string The adjusted script tag
 */
function fv_player_js_loader_mark_scripts( $tag, $handle ) {
  global $fv_fp;
  if( is_admin() || isset($_GET['fv_player_loader_skip']) || $fv_fp->_get_option('js-everywhere') || !$fv_fp->_get_option('js-optimize') ) {
    return $tag;
  }

  if(
    // script ID must start with one of following
    (
      stripos($handle,'flowplayer-') === 0 || // process Flowplayer HLS.js and Dash.js, but not the base FV Player library, that one must be present instantly
      stripos($handle,'fv-player') === 0 ||
      stripos($handle,'fv_player') === 0
    
    // script handle must not be one of
    ) && !in_array( $handle, array(
      'fv_player_lightbox' // without this it would be impossible to open the lightbox without hovering the page before it, so it's really a problem on mobile
    ), true )
  ) {
    $tag = str_replace( ' src=', ' data-fv-player-loader-src=', $tag );
    add_action( 'wp_print_footer_scripts', 'fv_player_js_loader_load', PHP_INT_MAX );
  }
  return $tag;
}

/*
 * Outpput FV Player JS Loader into footer, hooked in in fv_player_js_loader_mark_scripts()
 */
function fv_player_js_loader_load() {
  require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
  require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
  $filesystem = new WP_Filesystem_Direct( new StdClass() );
  
  $js = $filesystem->get_contents( dirname(__FILE__).'/../flowplayer/fv-player-loader.babel.js' );
  
  // remove /* comments */
  $js = preg_replace( '~/\*[\s\S]*?\*/~m', '', $js );
  // remove whitespace
  $js = preg_replace( '~\s+~m', ' ', $js );
  
  echo '<script data-length="'.strlen($js).'">'.$js.'</script>';
}

/*
 * @param string $min The minimal version to check - like 7.4.44.727
 * 
 * @return bool True if the version is at least $min
 */
function fv_player_extension_version_is_min( $min, $extension = 'pro' ) {
  $version = false;
  if( $extension == 'pro' ) {
    global $FV_Player_Pro;
    if( isset($FV_Player_Pro) && !empty($FV_Player_Pro->version) ) {
      $version = $FV_Player_Pro->version;
    }
    
  } else if( $extension == 'vast' ) {
    global $FV_Player_VAST;
    if( isset($FV_Player_VAST) && !empty($FV_Player_VAST->version) ) {
      $version = $FV_Player_VAST->version;
    }
    
  } else if( $extension == 'alternative-sources' ) {
    global $FV_Player_Alternative_Sources;
    if( isset($FV_Player_Alternative_Sources) && !empty($FV_Player_Alternative_Sources->version) ) {
      $version = $FV_Player_Alternative_Sources->version;
    }
    
  } else if( $extension == 'ppv' ) {
    global $FV_Player_PayPerView;
    if( isset($FV_Player_PayPerView) && !empty($FV_Player_PayPerView->version) ) {
      $version = $FV_Player_PayPerView->version;
    }

  } else if( $extension == 'ppv-woocommerce' ) {
    global $FV_Player_PayPerView_WooCommerce;
    if( isset($FV_Player_PayPerView_WooCommerce) && !empty($FV_Player_PayPerView_WooCommerce->version) ) {
      $version = $FV_Player_PayPerView_WooCommerce->version;
    }
    
  }
  
  $version = str_replace('.beta','',$version);
  
  return version_compare($version,$min ) != -1;
}


/*
 * WP Rocket Used CSS exclusion
 * Since many FV Player features are only visible once the video starts this optimization doesn't work
*/
add_filter( 'pre_get_rocket_option_remove_unused_css_safelist', 'fv_player_wp_rocket_used_css' );

function fv_player_wp_rocket_used_css( $safelist ) {
  // Without this our additions would show on WP Rocket settings page
  global $pagenow;
  if ( 'options-general.php' === $pagenow && 'wprocket' === $_GET['page'] ) {
    return $safelist;
  }

  $safelist[] = '/wp-content/fv-flowplayer-custom/style-*';
  $safelist[] = '/wp-content/plugins/fv-wordpress-flowplayer*';
  $safelist[] = '/wp-content/plugins/fv-player-*';
  return $safelist;
}