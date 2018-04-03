<?php

require_once( dirname(__FILE__).'/../fv-player-ajax-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_S3BrowserAjaxTestCase extends FV_Player_Ajax_UnitTestCase {

  // we need to set up PRO player with an appropriate key, or the PRO player won't work
  public static function wpSetUpBeforeClass() {
    global $fv_fp;

    // without this included, wp_ajax_load_s3_assets action would not be found
    include_once "../../../fv-wordpress-flowplayer/controller/editor.php";
    // include the flowplayer loader
    include_once "../../../fv-wordpress-flowplayer/flowplayer.php";
    $fv_fp = new flowplayer_frontend();
  }

  public function setUp() {
    parent::setUp();
    global $fv_fp;
    // without this included, wp_ajax_load_s3_assets action would not be found
    include_once "../../../fv-wordpress-flowplayer/controller/editor.php";
    include_once "../../../fv-wordpress-flowplayer/models/flowplayer.php";
    include_once "../../../fv-wordpress-flowplayer/models/flowplayer-frontend.php";
    $fv_fp = new flowplayer_frontend();

  }

  public function testNoSaveForNotLoggedInUsers() {
    global $fv_fp;

    $fv_fp->conf['amazon_bucket'] = array(FV_PLAYER_AMAZON_BUCKET);
    $fv_fp->conf['amazon_region'] = array(FV_PLAYER_AMAZON_REGION);
    $fv_fp->conf['amazon_key'] = array(FV_PLAYER_AMAZON_ACCESS_KEY);
    $fv_fp->conf['amazon_secret'] = array(FV_PLAYER_AMAZON_SECRET);

    // is anybody listening out there?
    $this->assertTrue( has_action('wp_ajax_load_s3_assets') );

    // Spoof the nonce in the POST superglobal
    //$_POST['_wpnonce'] = wp_create_nonce( 'anything-here-if-needed' );

    // set up POST data for video resume times
    // $_POST['action'] = 'fv_wp_flowplayer_video_position_save';

    // call the AJAX which
    try {
      $this->_handleAjax( 'fv_wp_flowplayer_ajax_load_s3_assets' );
    } catch ( WPAjaxDieContinueException $e ) {
      $response = json_decode( $this->_last_response );
      $this->assertInternalType( 'object', $response );
      $this->assertObjectHasAttribute( 'success', $response );
      $this->assertFalse( $response->success );
    }
  }

}
