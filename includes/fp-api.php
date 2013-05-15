<?php

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

                echo '<div style="color: #b51212;"">';
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
                        echo '<p style="margin: 5px 0;">' . htmlspecialchars($line) . '</p>';
                    }
                }
                
                if ($ul) {
                    //echo '</ul><div style="clear: left;"></div>';
                }
                
                echo '</div>';
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
  
  function is_min_wp( $version ) {
    return version_compare( $GLOBALS['wp_version'], $version. 'alpha', '>=' );
  }

}

?>