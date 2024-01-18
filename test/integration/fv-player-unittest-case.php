<?php

abstract class FV_Player_UnitTestCase extends WP_UnitTestCase {
  
  protected $backupGlobals = false;
  protected $restore;

  protected function setUp(): void {
    parent::setUp();
    
    global $fv_fp;
    $this->restore = $fv_fp->conf;
  }  
  
  public function fix_newlines( $html ) {
    $html = preg_replace( '/[\'"]wpfp_[0-9a-z]+[\'"]/', '"wpfp_some-test-hash"', $html);
    $html = preg_replace( '/[\'"]wpfp_[0-9a-z]+_playlist[\'"]/', '"wpfp_some-test-hash_playlist"', $html);
    $html = preg_replace( '~<input type="hidden" id="([^"]*?)nonce" name="([^"]*?)nonce" value="([^"]*?)" />~', '<input type="hidden" id="$1nonce" name="$2nonce" value="XYZ" />', $html);
    $html = preg_replace( '~<input type="hidden" id="nonce_([^"]*?)" name="nonce_([^"]*?)" value="([^"]*?)" />~', '<input type="hidden" id="nonce_$1" name="nonce_$2" value="XYZ" />', $html);    
    $html = preg_replace( "~nonce: '([^']*?)'~", "nonce: 'XYZ'", $html);
    
    // testProfileScreen
    $html = preg_replace( '~fv_ytplayer_[a-z0-9]+~', 'fv_ytplayer_XYZ', $html);
    $html = preg_replace( '~fv_vimeo_[a-z0-9]+~', 'fv_vimeo_XYZ', $html);
    $html = preg_replace( '~<input type="hidden" id="fv-player-custom-videos-_fv_player_user_video-0" name="fv-player-custom-videos-_fv_player_user_video-0" value="[^"]*?" />~', '<input type="hidden" id="fv-player-custom-videos-_fv_player_user_video-0" name="fv-player-custom-videos-_fv_player_user_video-0" value="XYZ" />', $html);
    $html = preg_replace( "~fv-player-custom-videos-entity-id\[_fv_player_user_video\]' value='\d+'~", "fv-player-custom-videos-entity-id[_fv_player_user_video]' value='1234'", $html);
    
    $html = preg_replace( '~convert_jwplayer=[a-z0-9]+~', 'convert_jwplayer=XYZ', $html);
    $html = preg_replace( '~_wpnonce=[a-z0-9]+~', '_wpnonce=XYZ', $html);
    $html = preg_replace( '~nonce":"[a-z0-9]+"~', 'nonce":"XYZ"', $html);
    
    // XML Video Sitemap
    $html = preg_replace( '~http://example.org/\?p=\d{1,}~', 'http://example.org/?p=1234', $html);
    $html = preg_replace( '~Post excerpt \d{1,}~', 'Post excerpt -regex-replaced-', $html);
    
    // DB IDs in JSON
    $html = preg_replace( '~"id":\d+~', '"id":1234', $html);

    // data-player-id on main player DIV
    $html = preg_replace( '~data-player-id=".*?"~', 'data-player-id="some-id-here"', $html);
    
    $html = explode("\n",$html);
    foreach( $html AS $k => $v ) {
      if( trim($v) == '' ) unset($html[$k]);
    }
    $html = implode( "\n", array_map('trim',$html) );
    
    $html = preg_replace( '~\t~', '', $html );
    
    //  playlist in lightbox test
    $html = preg_replace( '/(href|data-fv-lightbox|data-src)="#wpfp_[^"]+"/', '$1="#wpfp_some-test-hash"', $html);
    $html = preg_replace( '/(id|rel)="wpfp_[^"]+"/', '$1="wpfp_some-test-hash"', $html);
    $html = preg_replace( '~fv_flowplayer_[a-z0-9]+_lightbox_starter~', 'fv_flowplayer_XYZ_lightbox_starter', $html);
    
    //  tabbed playlist test
    $html = preg_replace( '~tabs-\d+~', 'tabs-1', $html);

    //  player ID on wrapping DIV
    $html = preg_replace( '~data-player-id="\d+"~', 'data-player-id="{number}"', $html);

    // splash end
    $html = preg_replace( '~wpfp_[a-z0-9]+_custom_background~', 'wpfp_XYZ_custom_background', $html);
    
    $html = preg_replace( '~\?ver=[0-9-wpalphabetaRCmod\.]+~', '?ver=1.2.3', $html);
    
    $html = preg_replace( '~<video:publication_date>(.*?)</video:publication_date>~', '<video:publication_date>2019-04-23T09:44:33+00:00</video:publication_date>', $html);

    // plugin_url() giving bad paths as FV Player folder is above the WordPress folder (test/testSuite), more: https://core.trac.wordpress.org/ticket/34358
    // so you end up with paths like http://example.org/wp-content/plugins/Users/martinv/github/fv-wordpress-flowplayer/images/test-subtitles.vtt
    $html = preg_replace( '~\\\/Users\\\/.*?\\\/github\\\/~', '\/', $html);

    // Settings page preview link
    $html = preg_replace( '~http://example.org\?fv_player_embed=.*?&fv_player_preview=~', 'http://example.org?fv_player_embed=SOME_NONCE?&fv_player_preview=', $html);
    
    // System Info - gone for now
    $html = preg_replace( '~### Begin System Info ###[\s\S]*?### End System Info ###~', '$1: some value', $html);

    $html = preg_replace( '~Are you having issues with version [0-9.]+~', 'Are you having issues with version XYZ', $html);

    /**
     * Somehow WordPress puts 2 spaces into the <link> tags for CSS:
     * 
     * <link rel='stylesheet' id='fv_flowplayer-css'  href='http://example.org/wp-content/plugins/fv-wordpress-flowplayer/css/flowplayer.css?ver=1.2.3' type='text/css' media='all' />
     * 
     * So we avoid that here.
     */
    $html = preg_replace( "~(<link rel='stylesheet'.*)  ~", '$1 ', $html );

    // WordPress 6.4 seems to add decoding="async" to <img /> tags
    $html = preg_replace( '~<img decoding="async"~', '<img', $html );

    // WordPress 6.4 seems to use " instead of ' in script tags arguments, so we use regex to avoid that as it breaks our tests
    $html = preg_replace(
      '~<script type="text/javascript" (data-fv-player-loader-)?src="([^"]+)" id="([^"]+)"></script>~',
      "<script type='text/javascript' $1src='$2' id='$3'></script>",
      $html
    );

    $html = preg_replace( '~<script type="text/javascript" id="([^"]+)">~', "<script type='text/javascript' id='$1'>", $html );
    
    // We lazy load splash images except the first one, but it's hard to keep track of that in-between the tests
    $html = str_replace( '<img loading="lazy" ', '<img ', $html );

    return $html;
  }

  // we need to set up PRO player with an appropriate key, or the PRO player won't work
  public static function wpSetUpBeforeClass() {
    global $fv_fp;

    // without this included, fv_wp_flowplayer_delete_extensions_transients() would not be found
    //include_once "../../../fv-wordpress-flowplayer/controller/backend.php";

    // include the flowplayer loader
    include "../../../fv-wordpress-flowplayer/fv-player.php";

    // include the PRO plugin class, so it can intercept data saving
    // and update the ads structure as needed for saving
    //include_once "../../beta/fv-player-pro.class.php";

    // save initial settings
    //$fv_fp->_set_conf();
  }
  
  protected function tearDown(): void {
    parent::tearDown();
    
    global $fv_fp;
    $fv_fp->conf = $this->restore;
  }  

}
