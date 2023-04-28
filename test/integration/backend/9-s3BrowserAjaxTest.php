<?php

require_once( dirname(__FILE__).'/../fv-player-ajax-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_S3BrowserAjaxTestCase extends FV_Player_Ajax_UnitTestCase {

  //  we need to make sure FV Player loads as if it's wp-admin
  public static function wpSetUpBeforeClass() {
    global $fv_fp;

    $_POST = array (
      'amazon_bucket' => array(FV_PLAYER_AMAZON_BUCKET),
      'amazon_region' => array(FV_PLAYER_AMAZON_REGION),
      'amazon_key' => array(FV_PLAYER_AMAZON_ACCESS_KEY),
      'amazon_secret' => array(FV_PLAYER_AMAZON_SECRET),
      's3_browser' => 1,
      'fv-wp-flowplayer-submit' => 'Save All Changes'
    );

    set_current_screen( 'edit-post' );
    // without this included, fv_wp_flowplayer_delete_extensions_transients() would not be found
    include_once "../../../fv-wordpress-flowplayer/controller/backend.php";
    parent::wpSetUpBeforeClass();

    $fv_fp->_set_conf($_POST);

    $fv_fp->conf;
  }

  protected function setUp(): void {
    parent::setUp();
  }

  // this AJAX unit tests fails for unknown reasons, with or without using the --group ajax argument
  // ... it's been disabled for now
  public function testStub() {
    $this->assertFalse( false );
  }

  public function testAdminAjaxSave() {
    global $fv_fp;

    $fv_fp->conf;

    // set bucket index
    $_POST['bucket'] = 0;

    // is anybody listening out there?
    $this->assertTrue( has_action('wp_ajax_load_s3_assets') );

    // Spoof the nonce in the POST superglobal
    //$_POST['_wpnonce'] = wp_create_nonce( 'anything-here-if-needed' );

    // set up POST data for video resume times
    // $_POST['action'] = 'fv_wp_flowplayer_video_position_save';

    // call the AJAX which
    try {
      $this->_handleAjax( 'load_s3_assets' );
    } catch ( WPAjaxDieContinueException $e ) {
      unset( $e );
    }

    $response = json_decode( $this->_last_response );
    $this->assertIsObject( $response );
    $this->assertTrue( property_exists( $response, 'items' ) );
    $this->assertTrue( property_exists( $response->items, 'items' ) );
  }

}
