<?php

abstract class FV_Player_UnitTestCase extends WP_UnitTestCase {
  
  protected
    $backupGlobals = false,
    $restore = array();
  
  public function setUp() {
    parent::setUp();
    
    global $fv_fp;
    $this->restore = $fv_fp->conf;
  }  
  
  public function fix_newlines( $html ) {
    $html = preg_replace( '/"wpfp_[0-9a-z]+"/', '"some-test-hash"', $html);
    $html = preg_replace( '~<input type="hidden" id="([^"]*?)nonce" name="([^"]*?)nonce" value="([^"]*?)" />~', '<input type="hidden" id="$1nonce" name="$2nonce" value="XYZ" />', $html);
    $html = preg_replace( '~<input type="hidden" id="nonce_([^"]*?)" name="nonce_([^"]*?)" value="([^"]*?)" />~', '<input type="hidden" id="nonce_$1" name="nonce_$2" value="XYZ" />', $html);    
    $html = preg_replace( "~nonce: '([^']*?)'~", "nonce: 'XYZ'", $html);
    
    // testProfileScreen
    $html = preg_replace( '~fv_ytplayer_[a-z0-9]+~', 'fv_ytplayer_XYZ', $html);
    $html = preg_replace( '~fv_vimeo_[a-z0-9]+~', 'fv_vimeo_XYZ', $html);
    $html = preg_replace( '~<input type="hidden" id="fv-player-custom-videos-_fv_player_user_video-0" name="fv-player-custom-videos-_fv_player_user_video-0" value="[^"]*?" />~', '<input type="hidden" id="fv-player-custom-videos-_fv_player_user_video-0" name="fv-player-custom-videos-_fv_player_user_video-0" value="XYZ" />', $html);
    
    $html = preg_replace( '~convert_jwplayer=[a-z0-9]+~', 'convert_jwplayer=XYZ', $html);
    $html = preg_replace( '~_wpnonce=[a-z0-9]+~', '_wpnonce=XYZ', $html);
    
    $html = explode("\n",$html);
    foreach( $html AS $k => $v ) {
      if( trim($v) == '' ) unset($html[$k]);
    }
    $html = implode( "\n", array_map('trim',$html) );
    
    $html = preg_replace( '~\t~', '', $html );
    
    //  playlist in lightbox test
    $html = preg_replace( "/(href|data-fv-lightbox|data-src)='#wpfp_[^']+'/", "$1='#some-test-hash'", $html);
    $html = preg_replace( '~fv_flowplayer_[a-z0-9]+_lightbox_starter~', 'fv_flowplayer_XYZ_lightbox_starter', $html);
    
    //  tabbed playlist test
    $html = preg_replace( '~tabs-\d+~', 'tabs-1', $html);
    
    // splash end
    $html = preg_replace( '~wpfp_[a-z0-9]+_custom_background~', 'wpfp_XYZ_custom_background', $html);
    
    $html = preg_replace( '~\?ver=[0-9\.]+~', '?ver=1.2.3', $html);
    
    $html = preg_replace( '~<video:publication_date>(.*?)</video:publication_date>~', '<video:publication_date>2019-04-23T09:44:33+00:00</video:publication_date>', $html);
    
    return $html;
  }

  public function remove_ids_and_urls($input) {
    $output = preg_replace(
      array(
        '/"id":\d{1,}/m',
        '/wp-content\/plugins\/[^"<]+(["<])/m',
        '/With the Verbose setting Vimeo API calls are logged into [^<]+</m',
        '/convert_(vimeo|youtube)=[^\']+\'/m',
        '/fv_player_embed=[^&]+&/m',
        '/fv-player-vimeo-[^<]+</m',
        '/Post excerpt \d{1,}( \d{1,})?/m',
        '/(\?|&)p=\d{1,}/m'
      ),
      array(
        '"id":-regex_replaced-',
        'wp-content\\/plugins\\/-regex-replaced-$1',
        'With the Verbose setting Vimeo API calls are logged into -regex-replaced-<',
        'convert_$1=-regex-replaced-\'',
        'fv_player_embed=-regex-replaced-&',
        'fv-player-vimeo--regex-replaced-<',
        'Post excerpt -regex-replaced-',
        '$1p=-regex-replaced-'
      ),
      $input
    );

    return $output;
  }

  // we need to set up PRO player with an appropriate key, or the PRO player won't work
  public static function wpSetUpBeforeClass() {
    global $fv_fp;

    // without this included, fv_wp_flowplayer_delete_extensions_transients() would not be found
    include_once "../../controller/backend.php";

    // include the flowplayer loader
    include "../../flowplayer.php";

    // include the PRO plugin class, so it can intercept data saving
    // and update the ads structure as needed for saving
    include_once "../../../fv-player-pro/beta/fv-player-pro.class.php";

    // save initial settings
    $fv_fp->_set_conf();
  }
  
  public function tearDown() {
    parent::tearDown();
    
    global $fv_fp;
    $fv_fp->conf = $this->restore;
    $fv_fp->_set_conf($fv_fp->conf);
  }  

}
