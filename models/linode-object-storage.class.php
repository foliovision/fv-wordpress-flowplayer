<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( !class_exists('FV_Player_Linode_Object_Storage') ) :

class FV_Player_Linode_Object_Storage extends FV_Player_CDN {

  function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    // TODO: What if FV Player is not yet loaded?
    add_action( 'plugins_loaded', array( $this, 'include_linode_media_browser' ), 9 );
    parent::__construct( array( 'key' => 'linode_object_storage', 'title' => 'Linode Object Storage') );
  }

  // includes the Digital Ocean Spaces handling class itself
  public function include_linode_media_browser() {
    if ( is_admin() && version_compare(phpversion(),'5.5.0') != -1 ) {
      include( dirname( __FILE__ ) . '/linode-object-storage-browser.class.php' );
    }
  }

  function get_endpoint() {
    global $fv_fp;
    $parsed = wp_parse_url( $fv_fp->_get_option( array($this->key,'endpoint' ) ) );

    if( count($parsed) == 1 && !empty($parsed['path']) ) {
      return $parsed['path'];

    } else if( !empty($parsed['host']) ) {
      return $parsed['host'];

    }
    return false;
  }

  function get_domains() {
    global $fv_fp;
    $space = $fv_fp->_get_option( array($this->key,'space' ) );
    $endpoint = $this->get_endpoint();
    if( $space && $endpoint ) {
      return array( $space.'.'.$endpoint, $endpoint.'/'.$space );
    }
    return false;
  }

  function get_region() {
    $parts = explode( '.', $this->get_endpoint() );
    return $parts[0];
  }

  function get_secure_tokens() {
    global $fv_fp;
    return $fv_fp->_get_option( array($this->key,'key' ) ) && $fv_fp->_get_option( array($this->key,'secret' ) );
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
        'name' => 'Storage Name',
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

  function secure_link( $url, $secret, $ttl = false ) {
    global $fv_fp;
    $key = $fv_fp->_get_option( array($this->key,'key' ) );
    $secret = $fv_fp->_get_option( array($this->key,'secret' ) );
    $endpoint = $fv_fp->_get_option( array($this->key,'endpoint' ) );
    $endpoint = explode('.',$endpoint);
    $endpoint = $endpoint[0];

    /*$path = preg_replace( '~.*?//.*?/~', '/', $url );
    $expires = time() + ( $ttl ? $ttl : apply_filters('fv_player_secure_link_timeout', 900) );
    $md5 = base64_encode(md5($path . $secret . $expires, true));
    $md5 = strtr($md5, '+/', '-_');
    $md5 = str_replace('=', '', $md5);
    $url = str_replace( $path, $path."?token=".$md5."&expire=".$expires, $url );*/

    $time = $ttl ? $ttl : apply_filters('fv_player_secure_link_timeout', 900);

    $url_components = wp_parse_url($url);

    $sXAMZDate = gmdate('Ymd\THis\Z');
    $sDate = gmdate('Ymd');
    $sCredentialScope = $sDate."/".$endpoint."/s3/aws4_request"; //  todo: variable
    $sSignedHeaders = "host";
    $sXAMZCredential = urlencode( $key.'/'.$sCredentialScope);

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

global $FV_Player_Linode_Object_Storage;
$FV_Player_Linode_Object_Storage = new FV_Player_Linode_Object_Storage;

endif;
