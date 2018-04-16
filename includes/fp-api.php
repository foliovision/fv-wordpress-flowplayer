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
 * Foliopress base class
 */

/**
 * Class FV_Wordpress_Flowplayer_Plugin
 */
class FV_Wordpress_Flowplayer_Plugin
{
	/**
	 * Stores the path to readme.txt available on trac, needs to be set from plugin
	 * @var string
	 */  
  var $readme_URL;

	/**
	 * Stores the special message for updates
	 * @var string
	 */   
  var $update_prefix;
  
  function __construct(){
  	$this->class_name = sanitize_title( get_class($this) );
  	add_action( 'admin_enqueue_scripts', array( $this, 'pointers_enqueue' ) );
  	add_action( 'wp_ajax_fv_foliopress_ajax_pointers', array( $this, 'pointers_ajax' ), 999 );
  }
  
  function http_request($method, $url, $data = '', $auth = '', $check_status = true)
  {
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
  
  /**
   * Download url via GET
   *
   * @param string $url
   * @param string $auth
   * $param boolean $check_status
   * @return string
   */
  function http_get($url, $auth = '', $check_status = true)
  {
      return $this->http_request('GET', $url, null, $auth, $check_status);
  }
  
  

  function plugin_update_message()
  {
      if( $this->readme_URL ) {
        $data = $this->http_get( $this->readme_URL );
        
        if ($data) {
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
  /*function plugin_update_message()
  {
      if( $this->readme_URL ) {
        $data = $this->http_get( $this->readme_URL );
        
        if ($data) {
            $matches = null;  /// not sure if this works for more than one last changelog
            if (preg_match('~==\s*Changelog\s*==\s*=\s*[0-9.]+\s*=(.*)(=\s*[0-9.]+\s*=|$)~Uis', $data, $matches)) {
                $changelog = (array) preg_split('~[\r\n]+~', trim($matches[1]));

                if( $this->update_prefix ) {
                  echo '<div style="color: #b51212;">'.$this->update_prefix.'</div>';
                }
                echo '<div>Last version improvements:</div><div style="font-weight: normal;">';
                $ul = false;
                
                foreach ($changelog as $index => $line) {
                    if (preg_match('~^\s*\*\s*~', $line)) {
                        if (!$ul) {
                            echo '<ul style="list-style: disc; margin-left: 20px;">';
                            $ul = true;
                        }
                        $line = preg_replace('~^\s*\*\s*~', '', htmlspecialchars($line));
                        echo '<li style="width: 50%; margin: 0; float: left; ' . ($index % 2 == 0 ? 'clear: left;' : '') . '">' . $line . '</li>';
                    } else {
                        if ($ul) {
                            echo '</ul><div style="clear: left;"></div>';
                            $ul = false;
                        }
                        echo '<p style="margin: 5px 0;">' . htmlspecialchars($line) . '</p>';
                    }
                }
                
                if ($ul) {
                    echo '</ul><div style="clear: left;"></div>';
                }
                
                echo '</div>';
            }
        }
      }
  }*/
  
  
  
  function pointers_ajax() {
		if( $this->pointer_boxes ) { 	
  		foreach( $this->pointer_boxes AS $sKey => $aPopup ) {
  			if( $_POST['key'] == $sKey ) {
					check_ajax_referer($sKey);
  			}
  		}
  	}
  }
  
  
  
  function pointers_enqueue() {
  	global $wp_version;
		if( ! current_user_can( 'manage_options' ) || ( isset($this->pointer_boxes) && count( $this->pointer_boxes ) == 0 ) || version_compare( $wp_version, '3.4', '<' ) ) {
			return;
		}

		/*$options = get_option( 'wpseo' );
		if ( ! isset( $options['yoast_tracking'] ) || ( ! isset( $options['ignore_tour'] ) || ! $options['ignore_tour'] ) ) {*/
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
		/*}
		if ( ! isset( $options['tracking_popup'] ) && ! isset( $_GET['allow_tracking'] ) ) {*/
			
		/*}
		else if ( ! isset( $options['ignore_tour'] ) || ! $options['ignore_tour'] ) {
			add_action( 'admin_print_footer_scripts', array( $this, 'intro_tour' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
		}  */
		
  	add_action( 'admin_print_footer_scripts', array( $this, 'pointers_init_scripts' ) );		
  }  
  
  
  
  function pointers_init_scripts() {
  	if( !isset($this->pointer_boxes) || !$this->pointer_boxes ) {
  		return;
  	}
  	
  	foreach( $this->pointer_boxes AS $sKey => $aPopup ) {
			$sNonce = wp_create_nonce( $sKey );
	
			$content = '<h3>'.$aPopup['heading'].'</h3>';
			if( stripos( $aPopup['content'], '</p>' ) !== false ) {
				$content .= $aPopup['content'];
			} else {
				$content .= '<p>'.$aPopup['content'].'</p>';
			}
			
			$position = ( isset($aPopup['position']) ) ? $aPopup['position'] : array( 'edge' => 'top', 'align' => 'center' );
			
			$opt_arr = array(	'content'  => $content, 'position' => $position );
      
      if( isset($aPopup['pointerClass']) ) $opt_arr['pointerClass'] = $aPopup['pointerClass'];
      if( isset($aPopup['pointerWidth']) ) $opt_arr['pointerWidth'] = $aPopup['pointerWidth'];
				
			$function2 = $this->class_name.'_store_answer("'.$sKey.'", "false","' . $sNonce . '")';
			$function1 = $this->class_name.'_store_answer("'.$sKey.'", "true","' . $sNonce . '")';
			
			?>
<script type="text/javascript">
	//<![CDATA[
		function <?php echo $this->class_name; ?>_store_answer(key, input, nonce) {
			var post_data = {
				action        : 'fv_foliopress_ajax_pointers',
				key						:	key, 
				value					: input,
				_ajax_nonce   : nonce
			}
			jQuery.post(ajaxurl, post_data, function () {
				jQuery('.'+key).remove();	
			});
		}
	//]]>
</script>					
			<?php
	
			$this->pointers_print_scripts( $sKey, $aPopup['id'], $opt_arr, $aPopup['button2'], $aPopup['button1'], $function2, $function1 );
		}
  }
  
  
  
	function pointers_print_scripts( $id, $selector, $options, $button1, $button2 = false, $button2_function = '', $button1_function = '' ) {
		?>
		<script type="text/javascript">
			//<![CDATA[
			(function ($) {
				var <?php echo $id; ?>_pointer_options = <?php echo json_encode( $options ); ?>, <?php echo $id; ?>_setup;

				<?php echo $id; ?>_pointer_options = $.extend(<?php echo $id; ?>_pointer_options, {
					buttons: function (event, t) {
						button = jQuery('<a id="pointer-close" style="margin-left:5px" class="button-secondary">' + '<?php echo addslashes($button1); ?>' + '</a>');
						button.bind('click.pointer', function () {
							t.element.pointer('close');
						});
						return button;
					},
					close  : function () {
					}
				});

				<?php echo $id; ?>_setup = function () {
          var sSelector = '<?php echo $selector; ?>';
          if( $(sSelector).length == 0 ){
            sSelector = '#wpadminbar';
          }
          $(sSelector).append('<div class="<?php echo $id; ?>"></div>');
					$(sSelector+' .<?php echo $id; ?>').pointer(<?php echo $id; ?>_pointer_options).pointer('open');
					<?php if ( $button2 ) { ?>
					jQuery('.<?php echo $id; ?> #pointer-close').after('<a id="pointer-primary" class="button-primary">' + '<?php echo addslashes($button2); ?>' + '</a>');
					jQuery('.<?php echo $id; ?> #pointer-primary').click(function () { <?php echo $button1_function; ?> });
					jQuery('.<?php echo $id; ?> #pointer-close').click(function () { <?php echo $button2_function; ?>	});
					<?php } ?>
				};

				if(<?php echo $id; ?>_pointer_options.position && <?php echo $id; ?>_pointer_options.position.defer_loading)
					$(window).bind('load.wp-pointers', <?php echo $id; ?>_setup);
				else
					$(document).ready(<?php echo $id; ?>_setup);
			})(jQuery);
			//]]>
		</script>
	<?php
	}  
  
  
  
  function is_min_wp( $version ) {
    return version_compare( $GLOBALS['wp_version'], $version. 'alpha', '>=' );
  }
  
  
  

  //search for plugin path with {slug}.php
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
  
    

}

?>