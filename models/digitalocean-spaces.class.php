<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( !class_exists('FV_Player_DigitalOcean_Spaces') ) :

class FV_Player_DigitalOcean_Spaces extends FV_Player_CDN {

  function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    // TODO: What if FV Player is not yet loaded?
    parent::__construct( array( 'key' => 'digitalocean_spaces', 'title' => 'DigitalOcean Spaces') );

    // we use priority of 9 to make sure it's loaded before FV Player Pro would load it
    add_action( 'plugins_loaded', array( $this, 'include_dos_media_browser' ), 9 );
    add_action( 'admin_init', array( $this, 'remove_fv_player_pro_dos' ), 21 );
    add_action( 'admin_init', array( $this, 'migrate_fv_player_pro_dos' ), 21 );
  }

  // includes the Digital Ocean Spaces handling class itself
  public function include_dos_media_browser() {
    if ( is_admin() && version_compare(phpversion(),'7.4') != -1 ) {
      include( dirname( __FILE__ ) . '/digitalocean-spaces-browser.class.php' );
    }
  }

  function get_endpoint() {
    global $fv_fp;
    $parsed = wp_parse_url( $fv_fp->_get_option( array($this->key,'endpoint' ) ) );

    if( count($parsed) == 1 && !empty($parsed['path']) ) { // for input like "region.digitaloceanspaces.com" it returns it as path, not realizing it's the hostname
      return $parsed['path'];

    } else if( !empty($parsed['host']) ) {
      return $parsed['host'];

    }
    return false;
  }

  function get_space() {
    global $fv_fp;
    $space = $fv_fp->_get_option( array($this->key,'space' ) );

    // If multiple DigitalOcean Spaces are configured, use the first one
    $spaces = explode( ',', $space );
    $space = $spaces[0];

    return $space;
  }

  function get_domains() {
    global $fv_fp;
    $spaces = $fv_fp->_get_option( array($this->key,'space' ) );
    $spaces = explode( ',', $spaces );

    $endpoint = $this->get_endpoint();

    $domains = array();

    foreach ( $spaces as $space ) {
      if( $space && $endpoint ) {
        /**
         * TODO: These two domains are really a problem. We do not store the endpoint for each Space, so here we just assume any.
         *       It's used in case of redundant Spaces which are not properly defined yet.
         */
        $domains[] = $space . '.';
        $domains[] = '/' . $space;

        $domains[] = $space . '.' . $endpoint;
        $domains[] = $endpoint . '/' . $space;
      }
    }

    if ( count( $domains ) ) {
      return $domains;
    } else {
      return false;
    }
  }

  function get_region() {
    $parts = explode( '.', $this->get_endpoint() );
    return $parts[0];
  }

  function get_secure_tokens() {
    global $fv_fp;
    return array( $fv_fp->_get_option( array($this->key,'secret' ) ) );
  }

  /*
   * Migrate DigitalOcean Spaces settings from FV Player Pro
   */
  function migrate_fv_player_pro_dos() {
    $option = get_option('fvwpflowplayer');

    $found_anything = false;

    global $fv_fp;
    foreach( array(
      'endpoint',
      'secret',
      'key',
      'space'
    ) AS $key ) {

      // bail if we have something already
      if( !empty($option['digitalocean_spaces']) && !empty($option['digitalocean_spaces'][$key]) ) continue;

      if( $value = $fv_fp->_get_option( array( 'pro', 'digitalocean_spaces_'.$key ) ) ) {
        if( empty($option['digitalocean_spaces']) ) $option['digitalocean_spaces'] = array();

        $option['digitalocean_spaces'][$key] = $value;

        $found_anything = true;
      }
    }

    if( $found_anything ) {
      update_option('fvwpflowplayer', $option);
    }

  }

  function options() {
    // TODO: Fix width
    // TODO: Add custom domain for CDN
    global $fv_fp;
    ?>
    <table class="form-table2" style="margin: 5px; ">
      <?php
      $fv_fp->_get_input_text( array(
        'key' => array($this->key,'space'),
        'name' => 'Space Name',
        'first_td_class' => 'first'
      ) );
      $fv_fp->_get_input_text( array(
        'key' => array($this->key,'endpoint'),
        'name' => 'Endpoint'
      ) );
      $fv_fp->_get_input_text( array(
        'key' => array($this->key,'key'),
        'name' => 'Key'
      ) );
      $fv_fp->_get_input_text( array(
        'key' => array($this->key,'secret'),
        'name' => 'Secret',
        'secret' => true
      ) );
      ?>
      <tr>
        <td colspan="4">
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#" style="margin-top: 2ex;"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
        </td>
      </tr>
    </table>
    <?php
  }

  function remove_fv_player_pro_dos() {
    // remove the legacy settings box in FV Player Pro
    remove_meta_box('fv_player_pro_digitalocean_spaces', 'fv_flowplayer_settings_hosting', 'normal');
  }

  function secure_link( $url, $secret, $ttl = false ) {

    if( stripos($url,'X-Amz-Expires') !== false ) return $url;

    global $fv_fp;
    $key = $fv_fp->_get_option( array($this->key,'key' ) );
    $secret = $fv_fp->_get_option( array($this->key,'secret' ) );
    $endpoint = $fv_fp->_get_option( array($this->key,'endpoint' ) );
    $endpoint = explode('.',$endpoint);
    $endpoint = $endpoint[0];

    $time = $ttl ? $ttl : apply_filters('fv_player_secure_link_timeout', 900);

    $url_components = wp_parse_url($url);

    $url_components['path'] = str_replace( array('%20','+'), ' ', $url_components['path']);

    $url_components['path'] = rawurlencode($url_components['path']);
    $url_components['path'] = str_replace('%2F', '/', $url_components['path']);
    $url_components['path'] = str_replace('%2B', '+', $url_components['path']);
    $url_components['path'] = str_replace('%2523', '%23', $url_components['path']);
    $url_components['path'] = str_replace('%252B', '%2B', $url_components['path']);
    $url_components['path'] = str_replace('%2527', '%27', $url_components['path']);

    // Round the current time to the nearest $time interval to ensure consistent signatures
    // Use ceil() to ensure URLs are valid for the full $time duration
    $currentTime = time();
    $roundedTime = ceil($currentTime / $time) * $time;

    $sXAMZDate = gmdate('Ymd\THis\Z', $roundedTime);
    $sDate = gmdate('Ymd', $roundedTime);
    $sCredentialScope = $sDate."/".$endpoint."/s3/aws4_request"; //  todo: variable
    $sSignedHeaders = "host";
    $sXAMZCredential = urlencode( $key.'/'.$sCredentialScope);

    // Support DigitalOcean Spaces CDN
    $url_components['host'] = str_replace( 'cdn.', '', $url_components['host'] );

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
    $sSignature = hash_hmac('sha256', $sDate, "AWS4".$secret, true );
    $sSignature = hash_hmac('sha256', $endpoint, $sSignature, true );  //  todo: variable
    $sSignature = hash_hmac('sha256', 's3', $sSignature, true );
    $sSignature = hash_hmac('sha256', 'aws4_request', $sSignature, true );
    $sSignature = hash_hmac('sha256', $sStringToSign, $sSignature );

    //  4. http://docs.aws.amazon.com/general/latest/gr/sigv4-add-signature-to-request.html
    $url .= "?X-Amz-Algorithm=AWS4-HMAC-SHA256";
    $url .= "&X-Amz-Credential=$sXAMZCredential";
    $url .= "&X-Amz-Date=$sXAMZDate";
    $url .= "&X-Amz-Expires=$time";
    $url .= "&X-Amz-SignedHeaders=$sSignedHeaders";
    $url .= "&X-Amz-Signature=".$sSignature;

    return $url;
  }

}

global $FV_Player_DigitalOcean_Spaces;
$FV_Player_DigitalOcean_Spaces = new FV_Player_DigitalOcean_Spaces;

endif;
