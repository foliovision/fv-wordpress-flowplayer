<?php

/**
 * Foliopress base class
 */
 
 /*
 Usage:
  
  * Autoupdates, put this into the main plugin Class which extends this class
  
  In the plugin object:
  var $strPluginSlug = 'fv-sharing';
  var $strPrivateAPI = 'http://foliovision.com/plugins/';
  
  And if this file is not in the same directory as the main plugn file:
  $this->strPluginPath = basename(dirname(__FILE__)).'/'.basename(__FILE__);
  
  In the plugin constructor:
  parent::auto_updates();  
  
  * Update notices
  
  In the plugin constructor:
    $this->readme_URL = 'http://plugins.trac.wordpress.org/browser/{plugin-slug}/trunk/readme.txt?format=txt';    
    add_action( 'in_plugin_update_message-{plugin-dir}/{plugin-file}.php', array( &$this, 'plugin_update_message' ) );
 */

/**
 * Class FVFB_Foliopress_Plugin_Private
 */
class FV_Wordpress_Flowplayer_Plugin_Private
{
  
  var $_wp_using_ext_object_cache_prev;
  
  function __construct(){
        $this->class_name = sanitize_title( get_class($this) );

        // get plugin slug based on directory
        if( empty( $this->strPluginSlug ) ) {
          $this->strPluginSlug = basename( dirname( __FILE__ ) );
        }

        if( empty( $this->strPluginName ) ) {
          $this->strPluginName  = $this->strPluginSlug;
        }
        
        if( empty( $this->strPluginPath ) ) {
          $this->strPluginPath = basename(dirname(__FILE__)).'/plugin.php';
          if( !file_exists( WP_PLUGIN_DIR.'/'.$this->strPluginPath ) ) {
            $this->strPluginPath = basename(dirname(__FILE__)).'/'.$this->strPluginSlug.'.php';
          }
        }

        add_action( 'admin_enqueue_scripts', array( $this, 'pointers_enqueue' ) );

        // store cookie for each dimissed notice first
        add_action( 'wp_ajax_fv_foliopress_ajax_pointers', array( $this, 'pointers_ajax_cookie' ), 0 );
        // TODO: What about the actual processing of the Ajax? Does it have to be in the plugin for real?
        add_action( 'wp_ajax_fv_foliopress_ajax_pointers', array( $this, 'pointers_ajax' ), 999 );
        add_action( 'wp_ajax_check_domain_license', array( $this, 'check_domain_license' ) );

        add_filter( 'plugins_api_result', array( $this, 'changelog_filter' ), 5, 3 );
        
        add_filter( 'pre_set_transient_'.$this->strPluginSlug . '_license', array( $this, 'object_cache_disable' ) );
        add_filter( 'pre_transient_'.$this->strPluginSlug . '_license', array( $this, 'object_cache_disable' ) );
        add_action( 'delete_transient_'.$this->strPluginSlug . '_license', array( $this, 'object_cache_disable' ) );
        add_action( 'set_transient_'.$this->strPluginSlug . '_license', array( $this, 'object_cache_disable' ) );
        add_filter( 'transient_'.$this->strPluginSlug . '_license', array( $this, 'object_cache_enable' ) );
        add_action( 'deleted_transient_'.$this->strPluginSlug . '_license', array( $this, 'object_cache_disable' ) );
        
        //add_action('admin_init', array($this, 'welcome_screen_do_activation_redirect'));
        //add_action('admin_menu', array($this, 'welcome_screen_pages'));
        //add_action('admin_head', array($this, 'welcome_screen_remove_menus'));
  }
  
  function auto_updates(){
    if( is_admin() ){

      // define $this->strPrivateAPI in main plugin class if the plugin is public
      if( !isset($this->strPrivateAPI) || empty($this->strPrivateAPI) ) {
        $this->strPrivateAPI = $this->getUpgradeUrl();
      }

      if( $this->strPrivateAPI !== FALSE ){
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'CheckPluginUpdate' ) );
        add_filter( 'plugins_api', array( $this, 'PluginAPICall' ), 10, 3 );
        add_action( 'update_option__transient_update_plugins',  array( $this, 'CheckPluginUpdateOld' ) );
        add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 );
      }
    }
  }
  
  function object_cache_disable($value=null){
    global $_wp_using_ext_object_cache;
    $this->_wp_using_ext_object_cache_prev = $_wp_using_ext_object_cache;
    $_wp_using_ext_object_cache = false;
    return $value;
  }
  
  function object_cache_enable($value=null){
    global $_wp_using_ext_object_cache;
    $_wp_using_ext_object_cache = $this->_wp_using_ext_object_cache_prev;
    return $value;
  }  
    
  function http_request_args( $params ) {
    $aArgs = func_get_args();
    $url = $aArgs[1];
  
    if( stripos($url,'foliovision.com') === false ) {
      return $params;
    }
  
    add_filter( 'https_ssl_verify', '__return_false' );
    return $params;
  }
  
  function http_request($method, $url, $data = '', $auth = '', $check_status = true) {
      $status = 0;
      $method = strtoupper($method);
      
      if (function_exists('curl_init')) {
          $ch = curl_init();
          
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
          @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
          curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
          curl_setopt($ch, CURLOPT_TIMEOUT, 10);
          
          switch ($method) {
              case 'POST':
                  curl_setopt($ch, CURLOPT_POST, true);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                  break;
              
              case 'PURGE':
                  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PURGE');
                  break;
          }
          
          if ($auth) {
              curl_setopt($ch, CURLOPT_USERPWD, $auth);
          }
          
          $contents = curl_exec($ch);
          
          $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          
          curl_close($ch);
      } else {
          $parse_url = @parse_url($url);
          
          if ($parse_url && isset($parse_url['host'])) {
              $host = $parse_url['host'];
              $port = (isset($parse_url['port']) ? (int) $parse_url['port'] : 80);
              $path = (!empty($parse_url['path']) ? $parse_url['path'] : '/');
              $query = (isset($parse_url['query']) ? $parse_url['query'] : '');
              $request_uri = $path . ($query != '' ? '?' . $query : '');
              
              $request_headers_array = array(
                  sprintf('%s %s HTTP/1.1', $method, $request_uri), 
                  sprintf('Host: %s', $host), 
                  sprintf('User-Agent: %s', W3TC_POWERED_BY), 
                  'Connection: close'
              );
              
              if (!empty($data)) {
                  $request_headers_array[] = sprintf('Content-Length: %d', strlen($data));
              }
              
              if (!empty($auth)) {
                  $request_headers_array[] = sprintf('Authorization: Basic %s', base64_encode($auth));
              }
              
              $request_headers = implode("\r\n", $request_headers_array);
              $request = $request_headers . "\r\n\r\n" . $data;
              $errno = null;
              $errstr = null;
              
              $fp = @fsockopen($host, $port, $errno, $errstr, 10);
              
              if (!$fp) {
                  return false;
              }
              
              $response = '';
              @fputs($fp, $request);
              
              while (!@feof($fp)) {
                  $response .= @fgets($fp, 4096);
              }
              
              @fclose($fp);
              
              list($response_headers, $contents) = explode("\r\n\r\n", $response, 2);
              
              $matches = null;
              
              if (preg_match('~^HTTP/1.[01] (\d+)~', $response_headers, $matches)) {
                  $status = (int) $matches[1];
              }
          }
      }
      
      if (!$check_status || $status == 200) {
          return $contents;
      }
      
      return false;
  }
  
  
  function is_min_wp( $version ) {
    return version_compare( $GLOBALS['wp_version'], $version. 'alpha', '>=' );
  }
  
  
  public static function get_plugin_path( $slug ){
    $aPluginSlugs = get_transient('plugin_slugs');
    $aPluginSlugs = is_array($aPluginSlugs) ? $aPluginSlugs : array( $slug.'/'.$slug.'.php');
    $aActivePlugins = get_option('active_plugins');
    $aInactivePlugins = array_diff($aPluginSlugs,$aActivePlugins);
    
    if( !$aPluginSlugs )
      return false;
      
    foreach( $aActivePlugins as $item ){
      if( stripos($item,$slug.'.php') !== false && !is_wp_error(validate_plugin($item)) )
        return $item;
    }
    
    $sPluginFolder = plugin_dir_path( dirname( dirname(__FILE__) ) );
    foreach( $aInactivePlugins as $item ){
      if( stripos($item,$slug.'.php') !== false && file_exists($sPluginFolder.$item) )
        return $item;
    }  
    
    return false;
  }  


  private function check_license_remote( $args = array() ) {

    if( !isset($this->strPluginSlug) || empty($this->strPluginSlug)
       || !isset($this->version) || empty($this->version)
       || !isset($this->license_key) || $this->license_key === FALSE  ) {
      return false;
    }

    $defaults = array(
      'action'    => 'check',
      'core_ver'  => false,
      'key'       => !empty( $this->license_key) ? $this->license_key : false,
      'plugin'    => $this->strPluginSlug,
      'type'      => home_url(),
      'version'   => $this->version,
    );
    $body_args = wp_parse_args( $args, $defaults );

    $post = array(
      'body' => $body_args,
      'timeout' => 20,
      'user-agent' => $this->strPluginSlug.'-'.$this->version
    );
    $resp = wp_remote_post( 'https://foliovision.com/?fv_remote=true', $post );
    if( !is_wp_error($resp) && isset($resp['body']) && $resp['body'] && $data = json_decode( preg_replace( '~[\s\s]*?<FVFLOWPLAYER>(.*?)</FVFLOWPLAYER>[\s\s]*?~', '$1', $resp['body'] ) ) ) {
      return $data;
    
    } else if( is_wp_error($resp) ) {
      $post['sslverify'] = false;
      $resp = wp_remote_post( 'https://foliovision.com/?fv_remote=true', $post );
    
      if( !is_wp_error($resp) && isset($resp['body']) && $resp['body'] && $data = json_decode( preg_replace( '~[\s\S]*?<FVFLOWPLAYER>(.*?)</FVFLOWPLAYER>[\s\S]*?~', '$1', $resp['body'] ) ) ) {    
        return $data;
      }
      
    }
    
    return false;
  }
  
  // set force = true to delete transient and recheck license
  function setLicenseTransient( $force = false ){
    $strTransient = $this->strPluginSlug . '_license';
    
    if( $force )
      delete_transient( $strTransient );
    
    //is transiet set?
    if ( false !== ( $aCheck = get_transient( $strTransient ) ) )
      return;
    
    $aCheck = $this->check_license_remote( );
    if( $aCheck ) {
      set_transient( $strTransient, $aCheck, 60*60*24 );
    } else {
      set_transient( $strTransient, json_decode(json_encode( array('error' => 'Error checking license') ), FALSE), 60*10 );
    }
  }
 

  function checkLicenseTransient(){
    $aCheck = get_transient( $this->strPluginSlug . '_license' );
    return isset($aCheck->valid) && $aCheck->valid;
  }

  function getUpgradeUrl(){
    $aCheck = get_transient( $this->strPluginSlug . '_license' );
    if( isset($aCheck->upgrade) && !empty($aCheck->upgrade) ) {
      return $aCheck->upgrade;
    } else {
      return false;
    }
  }
  
  
/// ================================================================================================
/// Custom plugin repository
/// ================================================================================================

/*
Uses:
$this->strPluginSlug - this has to be in plugin object
$this->strPrivateAPI - also

*/

   private function PrepareRequest( $action, $args ){
      global $wp_version;

      return array(
         'body' => array(
            'action' => $action, 
            'request' => serialize($args),
            'api-key' => md5(get_bloginfo('url'))
         ),
         'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
      );
   }

   public function CheckPluginUpdate( $checked_data ){
      $plugin_path = $this->strPluginPath;
      $request_args = array( 'slug' => $this->strPluginSlug );
      if( !empty( $checked_data->checked ) && empty($this->version) ){
        $request_args['version'] = isset($checked_data->checked[$plugin_path]) ? $checked_data->checked[$plugin_path] : '0.1';        
      }
      else{
        if( !function_exists('get_plugins') ) return $checked_data;
        
        $cache_plugins = get_plugins();        
        
        if( empty($cache_plugins[$plugin_path]['Version']) ){
          return $checked_data;
        }
        $request_args['version'] = $this->version ? $this->version : $cache_plugins[$plugin_path]['Version'];
      }

      $request = $this->PrepareRequest( 'basic_check', $request_args );
      
      $sTransient = $this->strPluginSlug.'_fp-private-updates-api-'.sanitize_title($request_args['version']);
      $response = get_transient( $sTransient );
      
      if( !$response ){
        if( stripos($this->strPrivateAPI,'plugins.trac.wordpress.org') === false ) {
          $raw_response = wp_remote_post( $this->strPrivateAPI, $request );
          if( is_wp_error($raw_response) ) {
            $request['sslverify'] = false;
            $raw_response = wp_remote_post( $this->strPrivateAPI, $request );
          }          
        } else {
          $raw_response = wp_remote_get( $this->strPrivateAPI );        
        }        
        
        if( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) ) {
          $response = @unserialize( preg_replace( '~^/\*[\s\S]*?\*/\s+~', '', $raw_response['body'] ) );
          if( !$response ) $response = $raw_response['body'];
        }
        
        set_transient( $sTransient, $response, 3600 );
      }
      
      if( isset($response->version) && version_compare( $response->version, $request_args['version'] ) == 1 ){
         if( is_object( $response ) && !empty( $response ) ) // Feed the update data into WP updater
            $checked_data->response[ $plugin_path ] = $response;
      }
      
      return $checked_data;
   }

   public function CheckPluginUpdateOld( $aData = null ){
      $aData = get_transient( "update_plugins" );
      $aData = $this->CheckPluginUpdate( $aData );
      set_transient( "update_plugins", $aData );
      
      if( function_exists( "set_site_transient" ) ) set_site_transient( "update_plugins", $aData );
   }   

   public function PluginAPICall( $def, $action, $args ){
      if( !isset($args->slug) || $args->slug != $this->strPluginSlug ) return $def;

      // Get the current version
      $plugin_info = get_site_transient( 'update_plugins' );
      $current_version = ( isset($plugin_info->response[$this->strPluginPath]) ) ? $plugin_info->response[$this->strPluginPath] : false;
      $args->version = $current_version;

      $request_string = $this->PrepareRequest( $action, $args );

      $request = wp_remote_post( $this->strPrivateAPI, $request_string );

      if( is_wp_error( $request ) ) {
         $res = new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
      }else{
         $res = unserialize( preg_replace( '~^/\*[\s\S]*?\*/\s+~', '', $request['body'] ) );
         if( $res === false ) $res = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred' ), $request['body'] );
      }

      return $res;
   }
   
   
  public function plugin_update_message() {
    if( $this->readme_URL ) {
      $data = $this->get_readme_url_remote( $this->readme_URL );
      if( $data ) {
        $matches = null;  /// not sure if this works for more than one last changelog
        //if (preg_match('~==\s*Changelog\s*==\s*=\s*[0-9.]+\s*=(.*)(=\s*[0-9.]+\s*=|$)~Uis', $data, $matches)) {
        if (preg_match('~==\s*Upgrade Notice\s*==\s*=\s*[0-9.]+\s*=(.*)(=\s*[0-9.]+\s*=|$)~Uis', $data, $matches)) {
          $changelog = (array) preg_split('~[\r\n]+~', trim($matches[1]));

          $ul = false;
          foreach ($changelog as $index => $line) {
            if (preg_match('~^\s*\*\s*~', $line) && 1<0 ) {
              if (!$ul) {
                //echo '<ul style="list-style: disc; margin-left: 20px;">';
                $ul = true;
              }
              $line = preg_replace('~^\s*\*\s*~', '', htmlspecialchars($line));
              echo '<li style="width: 50%; margin: 0; float: left; ' . ($index % 2 == 0 ? 'clear: left;' : '') . '">' . $line . '</li>';
            } else {
              if ($ul) {
                //echo '</ul><div style="clear: left;"></div>';
                $ul = false;
              }
              $line = preg_replace('~^\s*\*\s*~', '', htmlspecialchars($line));
              echo '<br /><br />' . htmlspecialchars($line)."\n";
            }
          }

          if ($ul) {
            //echo '</ul><div style="clear: left;"></div>';
          }
        }
      }
    }
  }
      
   
  function pointers_ajax() {
    if( $this->pointer_boxes ) {  
      foreach( $this->pointer_boxes AS $sKey => $aPopup ) {
        if( $_POST['key'] == $sKey ) {
          check_ajax_referer($sKey);
        }
      }
    }
  }


  function pointers_ajax_cookie() {
    $cookie = $this->pointers_get_cookie();

    $cookie[$_POST['key']] = !empty($_POST['value']) ? $_POST['value'] : true;

    $secure = ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
    setcookie( $this->class_name.'_store_answer', json_encode($cookie), time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, $secure );
  }


  function pointers_enqueue() {
    global $wp_version;
    if( ! current_user_can( 'manage_options' ) || ( isset($this->pointer_boxes) && count( $this->pointer_boxes ) == 0 ) || version_compare( $wp_version, '3.4', '<' ) ) {
      return;
    }

    wp_enqueue_style( 'wp-pointer' );
    wp_enqueue_script( 'jquery-ui' );
    wp_enqueue_script( 'wp-pointer' );
    wp_enqueue_script( 'utils' );

    add_action( 'admin_print_footer_scripts', array( $this, 'pointers_init_scripts' ) );    
  }


  /**
   * Get a cookie storing which pointers were already dimissed
   * The cookie uses JSON so we decode it too
   *
   * @return array
   */
  function pointers_get_cookie() {
    $cookie_name = $this->class_name.'_store_answer';

    $cookie = false;
    if( !empty($_COOKIE[$cookie_name]) ) {
      $cookie = $_COOKIE[$cookie_name];
    }

    $cookie = (array) json_decode( stripslashes($cookie) );

    $json_error = json_last_error();
    if( $json_error !== JSON_ERROR_NONE ) {
      $cookie = array();
    }

    return $cookie;
  }


  private function get_readme_url_remote( $url = false ) { // todo: caching
    $output = false;
    
    if( $url ) {
      $response = wp_remote_get( $url );
      if( !is_wp_error($response) ) {
        $output = $response['body'];
      }
    } else {
      if( !isset($this->strPluginSlug) || empty($this->strPluginSlug) || !isset($this->version) || empty($this->version) )
        return false;
          
      $args = array(
        'body' => array( 'plugin' => $this->strPluginSlug, 'version' => $this->version, 'type' => home_url() ),
        'timeout' => 20,
        'user-agent' => $this->strPluginSlug.'-'.$this->version
      );
      $resp = wp_remote_post( 'https://foliovision.com/?fv_remote=true&readme=1', $args );
      
      if( !is_wp_error($resp) && isset($resp['body']) && $resp['body'] ) {
        $output = $resp['body'];
      
      } else if( is_wp_error($resp) ) {
        $args['sslverify'] = false;
        $resp = wp_remote_post( 'https://foliovision.com/?fv_remote=true', $args );
      
        if( !is_wp_error($resp) && isset($resp['body']) && $resp['body'] ) {    
          $output = $resp['body'];
        }
        
      }
    }
    
    return $output;
  }
  
  
  function changelog_filter( $res, $action, $args ){
    
    if( !isset( $args->slug ) || $args->slug != $this->strPluginSlug  )
      return $res;
    
    if(isset($args->fv_readme_file)){
      $data = file_get_contents($args->fv_readme_file);
    } else if( $this->readme_URL ) {
      $data = $this->get_readme_url_remote( $this->readme_URL );
    } else {
      $data = $this->get_readme_url_remote();
    }
    if( !$data )
      return $res;

    $plugin_data = get_plugin_data($this->strPluginPath);
    
    $pluginReq = preg_match( '~Requires at least:\s*([0-9.]*)~', $data, $reqMatch ) ? $reqMatch[1] : false;
    $pluginUpto = preg_match( '~Tested up to:\s*([0-9.]*)~', $data, $uptoMatch ) ? $uptoMatch[1] : false;
    
    $changelogOut = '';
    if( preg_match('~==\s*Changelog\s*==(.*)~si', $data, $match) ){
      $changelogPart = preg_replace('~==.*~','',$match[1]);
      $version = preg_match('~=\s*([0-9.]+).*=~', $changelogPart, $verMatch ) ? $verMatch[1] : false;
      
        $changelog = (array) preg_split('~[\r\n]+~', trim($changelogPart));
        $ul = false;
        $changelogFinish = false;
        $changelogCounter = 0;
        foreach ($changelog as $index => $line) {
            if (preg_match('~^\s*\*\s*~', $line)) {
                if (!$ul) {
                    $changelogOut .= '<ul style="list-style: disc; margin-left: 20px;">';
                    $ul = true;
                }
                $line = preg_replace('~^\s*\*\s*~', '', htmlspecialchars($line));
                $changelogOut .= '<li style="width: 50%; margin: 0; float: left; ' . ($index % 2 == 0 ? 'clear: left;' : '') . '">' . $line . '</li>';
            } else {
                if ($ul) {
                    $changelogOut .= '</ul><div style="clear: left;"></div>';
                    $ul = false;
                }
                
                $strong = $strongEnd = '';
                if( preg_match('~^=(.*)=$~', $line ) ){
                  $strong = '<strong>';
                  $strongEnd = '</strong>';
                  $line = preg_replace('~^=(.*)=$~', '$1', $line );
                  if(isset($args->fv_prev_ver)){
                    if(($args->fv_prev_ver == false || $args->fv_prev_ver === $this->version )  ){
                      if(++$changelogCounter > 3){
                        $changelogFinish = true;
                      }
                    }elseif(strpos($line,str_replace('.beta','',$args->fv_prev_ver . ' ')) !== false){
                      $changelogFinish = true;
                    }
                  }
                }
                if ($changelogFinish) {
                  break;
                }
                $changelogOut .= '<p style="margin: 5px 0;">' .$strong. htmlspecialchars($line) .$strongEnd. '</p>';
               
            }
            
        }
        if ($ul) {
            $changelogOut .= '</ul><div style="clear: left;"></div>';
        }
        $changelogOut .= '</div>';
    }
    
    $res = (object) array(
       'name' => $plugin_data['Name'],
       'slug' => false,
       'version' => $version,
       'author' => $plugin_data['Author'],
       'requires' => $pluginReq,
       'tested' => $pluginUpto,
       'homepage' => $plugin_data['PluginURI'],
       'sections' => 
      array (
        'support' => 'Use support forum at <a href="https://foliovision.com/support/">foliovison.com/support</a>',
        'changelog' => $changelogOut,
      ),
       'donate_link' => NULL
    );
      
    return $res;
    
  }
  
  
  //notification boxes
   function pointers_init_scripts() {
    if( !isset($this->pointer_boxes) || !$this->pointer_boxes ) {
      return;
    }
    
    ?>
<script type="text/javascript">
//<![CDATA[
  function <?php echo $this->class_name; ?>_store_answer(key, input, nonce) {
    jQuery.post(ajaxurl, { action : 'fv_foliopress_ajax_pointers', key : key, value : input, _ajax_nonce : nonce }, function () {
      jQuery('#wp-pointer-0').remove(); // there must only be a single pointer at once. Or perhaps it removes them all, but the ones which were not dismissed by Ajax by storing the option will turn up again?
    });
  }

  /*! js-cookie v3.0.1 | MIT */
  !function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t():"function"==typeof define&&define.amd?define(t):(e=e||self,function(){var n=e.Cookies,o=e.Cookies=t();o.noConflict=function(){return e.Cookies=n,o}}())}(this,(function(){"use strict";function e(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)e[o]=n[o]}return e}return function t(n,o){function r(t,r,i){if("undefined"!=typeof document){"number"==typeof(i=e({},o,i)).expires&&(i.expires=new Date(Date.now()+864e5*i.expires)),i.expires&&(i.expires=i.expires.toUTCString()),t=encodeURIComponent(t).replace(/%(2[346B]|5E|60|7C)/g,decodeURIComponent).replace(/[()]/g,escape);var c="";for(var u in i)i[u]&&(c+="; "+u,!0!==i[u]&&(c+="="+i[u].split(";")[0]));return document.cookie=t+"="+n.write(r,t)+c}}return Object.create({set:r,get:function(e){if("undefined"!=typeof document&&(!arguments.length||e)){for(var t=document.cookie?document.cookie.split("; "):[],o={},r=0;r<t.length;r++){var i=t[r].split("="),c=i.slice(1).join("=");try{var u=decodeURIComponent(i[0]);if(o[u]=n.read(c,u),e===u)break}catch(e){}}return e?o[e]:o}},remove:function(t,n){r(t,"",e({},n,{expires:-1}))},withAttributes:function(n){return t(this.converter,e({},this.attributes,n))},withConverter:function(n){return t(e({},this.converter,n),this.attributes)}},{attributes:{value:Object.freeze(o)},converter:{value:Object.freeze(n)}})}({read:function(e){return'"'===e[0]&&(e=e.slice(1,-1)),e.replace(/(%[\dA-F]{2})+/gi,decodeURIComponent)},write:function(e){return encodeURIComponent(e).replace(/%(2[346BF]|3[AC-F]|40|5[BDE]|60|7[BCD])/g,decodeURIComponent)}},{path:"/"})}));

//]]>
</script>
    <?php
    $cookie = $this->pointers_get_cookie();
    
    foreach( $this->pointer_boxes AS $key => $args ) {
      // Some users are experiencing issues when dismissing the notices
      // So we use cookies as a backup to not show the same notice twice
      if( !empty($cookie[$key]) ) {
        continue;
      }

      $nonce = wp_create_nonce( $key );
      
      $args = wp_parse_args( $args, array(
        'button1' => false, // req
        'button2' => false,
        'function1' => $this->class_name.'_store_answer("'.$key.'", "true","' . $nonce . '")',
        'function2' => $this->class_name.'_store_answer("'.$key.'", "false","' . $nonce . '")',
        'heading' => false, // req
        'id' => false,  // req
        'content' => false, // req
        'position' => array( 'edge' => 'top', 'align' => 'center' ),
      ) );
      
      extract($args);
      
      $html = '<h3>'.$heading.'</h3>';
      if( stripos( $content, '</p>' ) !== false ) {
        $html .= $content;
      } else {
        $html .= '<p>'.$content.'</p>';
      }

      ?>
      <script type="text/javascript">
        //<![CDATA[
        (function ($) {
          store_cookie_js = function(value , key) {
            var cookie_name = '<?php echo $this->class_name.'_store_answer'; ?>';
            var pointer_cookies = JSON.parse( Cookies.get(cookie_name) );
            pointer_cookies[key] = value;
            Cookies.set(cookie_name, JSON.stringify(pointer_cookies) , { secure: location.protocol == 'https:', expires: 365 } )
            jQuery('#wp-pointer-0').remove();
          }

          var pointer_options = <?php echo json_encode( array( 'pointerClass' => $key, 'content'  => $html, 'position' => $position ) ); ?>,
            key = '<?php echo $key; ?>',

            setup = function () {
              $('<?php echo $id; ?>').pointer(pointer_options).pointer('open');
              var buttons = $('.<?php echo $key; ?> .wp-pointer-buttons').html('');
              buttons.append( $('<a style="margin-left:5px" class="button-primary">' + '<?php echo addslashes($button1); ?>' + '</a>').on('click.pointer', function () { <?php echo $function1; ?>; store_cookie_js('true' , key); }));
              <?php if ( $button2 ) { ?>
                buttons.append( $('<a class="button-secondary">' + '<?php echo addslashes($button2); ?>' + '</a>').on('click.pointer', function () { <?php echo $function2; ?>; store_cookie_js('false', key); }));
              <?php } ?>
            };

          if(pointer_options.position && pointer_options.position.defer_loading)
            $(window).bind('load.wp-pointers', setup);
          else
            $(document).ready(setup);
        })(jQuery);
        //]]>
      </script>
      <?php
    }
  }


  function check_domain_license() {
    if( $_POST['slug'] != $this->strPluginSlug ) {
      return;
    }

    if( stripos( $_SERVER['HTTP_REFERER'], home_url() ) === 0 ) {
      $license_key = $this->domain_key_update();
      if( $license_key ) {
        $message  = !empty( $this->domain_license_success ) ? $this->domain_license_success : 'License key acquired successfully. <a href="">Reload</a>';
        $output   = array( 'errors' => false, 'ok' => array( $message ), 'license_key' => $license_key );
        //fv_wp_flowplayer_install_extension();
      } else {
        $message  = !empty( $this->domain_license_error ) ? $this->domain_license_error : 'There is no license key purchased for this domain. Please visit <a target="_blank" href="https://foliovision.com">Foliovision</a>.';
        $output   = array( 'errors' => array($message), 'ok' => false );
      }
      echo '<FVFLOWPLAYER>'.json_encode($output).'</FVFLOWPLAYER>';
      die();
    }
    die('-1');
  }

  function change_transient_expiration( $transient_name, $time ){
    $transient_val = get_transient($transient_name);
    if( $transient_val ){
      set_transient($transient_name,$transient_val,$time);
      return true;
    }
    return false;
  }


  function domain_key_update() {

    $data = $this->check_license_remote( array('action' => 'key_update') );

    if( isset($data->domain) ) {  //  todo: test
      if( $data->domain && $data->key && stripos( home_url(), $data->domain ) !== false ) {
        $this->license_key = $data->key;
        do_action( $this->strPluginSlug.'_admin_key_update', $this->license_key );
        
        $this->change_transient_expiration( $this->strPluginSlug."_license", 1 );
        // change the expiration to license renew by: $this->setLicenseTransient( true );

        //fv_wp_flowplayer_delete_extensions_transients(5);
        return $data->key;
      }
    } else if( isset($data->expired) && $data->expired && isset($data->message) ){

      update_option( 'fv_'.$this->strPluginSlug.'_deferred_notices', $data->message );
      return false;
    } else {
      $message = 'FV Flowplayer License upgrade failed - please check if you are running the plugin on your licensed domain.';
      update_option( 'fv_'.$this->strPluginSlug.'_deferred_notices', $message );
      return false;
    }
  }

  function pro_install_talk( $content, $url ) {
    $content = preg_replace( '~<h3.*?</h3>~', '<h3>'.$this->strPluginName.' auto-installation</h3><p>As a license holder, we would like to automatically install our Pro extension for you.</p>', $content );
    $content = preg_replace( '~(<input[^>]*?type="submit"[^>]*?>)~', '$1 <a href="'.$url.'">Skip the Pro addon install</a>', $content );
    return $content;
  }

  //search for plugin path with {slug}.php
  function get_extension_path( $slug ){
    $aPluginSlugs = get_transient('plugin_slugs');
    $aPluginSlugs = is_array($aPluginSlugs) ? $aPluginSlugs : array( 'fv-player-pro/fv-player-pro.php');
    $aActivePlugins = get_option('active_plugins');
    $aInactivePlugins = array_diff($aPluginSlugs,$aActivePlugins);
    
    if( !$aPluginSlugs )
      return false;
    foreach( $aActivePlugins as $item ){
      if( stripos($item,$slug.'.php') !== false )
        return $item;
    }
    
    foreach( $aInactivePlugins as $item ){
      if( stripos($item,$slug.'.php') !== false )
        return $item;
    }
    
    return false;
  }
  
  
  public static function install_form_text( $html, $name ) {
    $tag = stripos($html,'</h3>') !== false ? 'h3' : 'h2';
    $html = preg_replace( '~<'.$tag.'.*?</'.$tag.'>~', '<'.$tag.'>'.$name.' auto-installation</'.$tag.'>', $html );
    $html = preg_replace( '~(<input[^>]*?type="submit"[^>]*?>)~', '$1 <a href="'.admin_url('options-general.php?page=fvplayer').'">Skip the '.$name.' install</a>', $html );    
    return $html;
  }  
  
  
  public static function install_plugin( $name, $plugin_package, $plugin_basename, $download_url, $settings_url, $option, $nonce ) {  //  'FV Player Pro', 'fv-player-pro', '/wp-admin/options-general.php?page=fvplayer', download URL (perhaps from the license), settings URL (use admin_url(...), should also contain some GET which will make it install the extension if present) and option where result message should be stored and a nonce which should be passed
    global $hook_suffix;
    
    $plugin_path = self::get_plugin_path( str_replace( '_', '-', $plugin_package ) );
    if( !defined('PHPUnitTestMode') && $plugin_path ) {
      $result = activate_plugin( $plugin_path, $settings_url );
      if ( is_wp_error( $result ) ) {
        update_option( $option, $name.' extension activation error: '.$result->get_error_message() );
        return false;
      } else {
        update_option( $option, $name.' extension activated' );
        return true; //  already installed
      }
    }

    $plugin_basename = $plugin_path ? $plugin_path : $plugin_basename;

    $url = wp_nonce_url( $settings_url, $nonce, 'nonce_'.$nonce );

    set_current_screen();

    ob_start();
    if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, false ) ) ) {
      $form = ob_get_clean();
      include( ABSPATH . 'wp-admin/admin-header.php' );
      echo self::install_form_text($form, $name);
      include( ABSPATH . 'wp-admin/admin-footer.php' );
      die;
    }

    if ( ! WP_Filesystem( $creds ) ) {
      ob_start();
      request_filesystem_credentials( $url, $method, true, false, false );
      $form = ob_get_clean();
      include( ABSPATH . 'wp-admin/admin-header.php' );
      echo self::install_form_text($form, $name);
      include( ABSPATH . 'wp-admin/admin-footer.php' );
      die;
    }

    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    
    $result = true;
       
    if( !$plugin_path || is_wp_error(validate_plugin($plugin_basename)) ) {
      $sTaskDone = $name.__(' extension installed successfully!', 'fv-wordpress-flowplayer');
      
      echo '<div style="display: none;">';
      $objInstaller = new Plugin_Upgrader();
      $objInstaller->install( $download_url );
      echo '</div>';
      wp_cache_flush();
      
      if ( is_wp_error( $objInstaller->skin->result ) ) {
        update_option( $option, $name.__(' extension install failed - ', 'fv-wordpress-flowplayer') . $objInstaller->skin->result->get_error_message() );
        $result = false;
      } else {    
        if ( $objInstaller->plugin_info() ) {
          $plugin_basename = $objInstaller->plugin_info();
        }
        
        $activate = activate_plugin( $plugin_basename );
        if ( is_wp_error( $activate ) ) {
          update_option( $option, $name.__(' extension install failed - ', 'fv-wordpress-flowplayer') . $activate->get_error_message());
          $result = false;
        }
      }
      
    } else if( $plugin_path ) {
      $sTaskDone = $name.__(' extension upgraded successfully!', 'fv-wordpress-flowplayer');

      echo '<div style="display: none;">';
      $objInstaller = new Plugin_Upgrader();
      $objInstaller->upgrade( $plugin_path );    
      echo '</div></div>';  //  explanation: extra closing tag just to be safe (in case of "The plugin is at the latest version.")
      wp_cache_flush();
      
      if ( is_wp_error( $objInstaller->skin->result ) ) {
        update_option( $option, $name.' extension upgrade failed - '.$objInstaller->skin->result->get_error_message() );
        $result = false;
      } else {    
        if ( $objInstaller->plugin_info() ) {
          $plugin_basename = $objInstaller->plugin_info();
        }
        
        $activate = activate_plugin( $plugin_basename );
        if ( is_wp_error( $activate ) ) {
          update_option( $option, $name.' Pro extension upgrade failed - '.$activate->get_error_message() );
          $result = false;
        }
      }    
      
    }

    if( $result ) {
      update_option( $option, $sTaskDone );
      echo "<script>location.href='".$settings_url."';</script>";
    }

    return $result;
  }
    

  function install_pro_version( $plugin_package = false, $target_url = false ) {

    $aPluginInfo        = get_transient( $this->strPluginSlug.'_license' );
    if( $plugin_package && isset( $aPluginInfo->{$plugin_package} ) ) {
      $plugin_basename  = $aPluginInfo->{$plugin_package}->slug;
      $download_url     = $aPluginInfo->{$plugin_package}->url;
    }
    else {
      $plugin_basename  = file_exists( WP_PLUGIN_DIR.'/'.$this->strPluginSlug.'/plugin.php' ) ? $this->strPluginSlug.'/plugin.php' : $this->strPluginSlug.'/'.$this->strPluginSlug.'.php';
      $download_url     = $aPluginInfo->url;
      $plugin_package    = $this->strPluginSlug;
    }

    $aInstalled = get_option( $this->strPluginSlug.'_extension_install', array() );
    $aInstalled = array_merge( $aInstalled, array( $plugin_package => false ) );
    update_option( $this->strPluginSlug.'_extension_install', $aInstalled );

    $sPluginBasenameReal  = $this->get_extension_path( str_replace( '_', '-', $plugin_package ) );
    $plugin_basename      = $sPluginBasenameReal ? $sPluginBasenameReal : $plugin_basename;

    $url = ( $target_url ) ? $target_url : site_url().'/wp-admin/plugins.php';
    $url = wp_nonce_url( $url );

    set_current_screen();
    
    ob_start();
    if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, false ) ) ) {
      $form = ob_get_clean();
      include( ABSPATH . 'wp-admin/admin-header.php' );
      echo $this->pro_install_talk( $form, $target_url );
      include( ABSPATH . 'wp-admin/admin-footer.php' );
      die;
    }

    if ( ! WP_Filesystem( $creds ) ) {
      ob_start();
      request_filesystem_credentials( $url, $method, true, false, false );
      $form = ob_get_clean();
      include( ABSPATH . 'wp-admin/admin-header.php' );
      echo $this->pro_install_talk( $form, $target_url );
      include( ABSPATH . 'wp-admin/admin-footer.php' );
      die;
    }

    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

    if( !$sPluginBasenameReal || is_wp_error(validate_plugin($plugin_basename)) ) {
      $sTaskDone = $this->strPluginName.' has been installed!';
      echo '<div style="display: none;">';
      $objInstaller = new Plugin_Upgrader();
      $objInstaller->install( $download_url );
      echo '</div>';
      wp_cache_flush();
      
      if ( is_wp_error( $objInstaller->skin->result ) ) {
        
        update_option( $this->strPluginSlug.'_deferred_notices', $this->strPluginName.' install failed - '. $objInstaller->skin->result->get_error_message() );
        $bResult = false;
      }
      else {
        if ( $objInstaller->plugin_info() ) {
          $plugin_basename = $objInstaller->plugin_info();
        }
        
        $activate = activate_plugin( $plugin_basename );
        if ( is_wp_error( $activate ) ) {
          update_option( $this->strPluginSlug.'_deferred_notices', $this->strPluginName.'  install failed - '. $activate->get_error_message() );
          $bResult = false;
        }
      }
    }
    else if( $sPluginBasenameReal ) {
      $sTaskDone = $this->strPluginName.' upgraded successfully!';
      echo '<div style="display: none;">';
      $objInstaller = new Plugin_Upgrader();
      $objInstaller->upgrade( $sPluginBasenameReal );    
      echo '</div></div>';  //  explanation: extra closing tag just to be safe (in case of "The plugin is at the latest version.")
      wp_cache_flush();
      
      if ( is_wp_error( $objInstaller->skin->result ) ) {
        update_option( $this->strPluginSlug.'_deferred_notices', $this->strPluginName.' extension upgrade failed - '.$objInstaller->skin->result->get_error_message() );
        $bResult = false;
      }
      else {
        if ( $objInstaller->plugin_info() ) {
          $plugin_basename = $objInstaller->plugin_info();
        }
        
        $activate = activate_plugin( $plugin_basename );
        if ( is_wp_error( $activate ) ) {
          update_option( $this->strPluginSlug.'_deferred_notices', $this->strPluginName.' extension upgrade failed - '.$activate->get_error_message() );
          $bResult = false;
        }
      }
    }

    if( empty( $bResult ) ) {
      update_option( $this->strPluginSlug.'_deferred_notices', $sTaskDone );
      $bResult = true;
    }

    $aInstalled = array_merge( $aInstalled, array( $plugin_package => $bResult ) );
    update_option( $this->strPluginSlug.'_extension_install', $aInstalled );

    return $bResult;
  }


   /*
   * WELCOME SCREEN
   */

  public function welcome_screen_do_activation_redirect() {    
    if ( str_replace( array('.beta','.release'), '', $this->version ) === str_replace( array('.beta','.release'), '', get_option($this->strPluginSlug . '-prev-ver') ) || isset($_GET['page']) && $_GET['page'] === $this->strPluginSlug . '-welcome') {
      return;
    }
    
    if (is_network_admin() || isset($_GET['activate-multi']) || isset($_GET['action']) && $_GET['action'] == 'activate-plugin' ) {
      return;
    }
    wp_safe_redirect(add_query_arg(array('page' => $this->strPluginSlug . '-welcome'), admin_url('index.php')));
  }

  public function welcome_screen_pages() {
    add_dashboard_page(
            'Welcome To Welcome Screen', 'Welcome To Welcome Screen', 'read', $this->strPluginSlug . '-welcome', array($this, 'welcome_screen_content')
    );
  }

  public function welcome_screen_content() {

    $prev_ver = str_replace( array('.beta','.release'), '', get_option($this->strPluginSlug . '-prev-ver') );
    update_option($this->strPluginSlug . '-prev-ver', $this->version);
    $args = (object) array(
                'slug' => $this->strPluginSlug,
                'fv_readme_file' => dirname(__FILE__) . '/readme.txt',
                'fv_prev_ver' => $prev_ver
    );
    $changelog = $this->changelog_filter(false, false, $args);
    if ($changelog) {
      $changelog = $changelog->sections['changelog'];
    }
    $version = $this->version;

    if (file_exists(dirname(__FILE__) . '/welcome.php')) {
      include(dirname(__FILE__) . '/welcome.php');
    } else {
      //TODO:DEFAULT BEHAVIOR
    }
  }

  public function welcome_screen_remove_menus() {
    remove_submenu_page('index.php', $this->strPluginSlug . '-welcome');
  }

}
