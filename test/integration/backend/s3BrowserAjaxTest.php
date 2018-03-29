<?php

require_once( dirname(__FILE__).'/../fv-player-ajax-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_S3BrowserAjaxTestCase extends FV_Player_Ajax_UnitTestCase {

  public function testNoSaveForNotLoggedInUsers() {
    /*global $fv_fp;

    $fv_fp->conf['amazon_bucket'] = FV_PLAYER_AMAZON_BUCKETS;
    $fv_fp->conf['amazon_region'] = FV_PLAYER_AMAZON_REGIONS;
    $fv_fp->conf['amazon_key'] = FV_PLAYER_AMAZON_ACCESS_KEYS;
    $fv_fp->conf['amazon_secret'] = FV_PLAYER_AMAZON_SECRETS;

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
    }*/
  }

}
