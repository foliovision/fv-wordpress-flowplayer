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
      'amazon_bucket' => FV_PLAYER_AMAZON_BUCKET,
      'amazon_region' => FV_PLAYER_AMAZON_REGION,
      'amazon_key' => FV_PLAYER_AMAZON_ACCESS_KEY,
      'amazon_secret' => FV_PLAYER_AMAZON_SECRET,
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

  public function testMediaBrowserS3() {
    global $fv_fp;

    $fv_fp->conf;

    // set bucket index
    $_POST['bucket'] = 0;

    // is anybody listening out there?
    $this->assertTrue( has_action('wp_ajax_load_s3_assets') );

    // Spoof the nonce in the POST superglobal
    $_POST['nonce'] = wp_create_nonce( 'wp_ajax_load_s3_assets' );

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

    // there should be no error
    $this->assertTrue( !property_exists( $response, 'err' ) );

    // if( isset( $response->err ) ) {
    //   // check if there is no 403 Forbidden error in string
    //   $this->assertTrue( strpos( $response->err, '403 Forbidden' ) === false );
    // }

    $this->assertTrue( property_exists( $response, 'items' ) );
    $this->assertTrue( property_exists( $response->items, 'items' ) );
  }

}
