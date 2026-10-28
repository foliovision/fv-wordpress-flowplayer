<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Bunny Stream browser upload uses the TUS endpoint with a signature created on the server,
 * so the browser never gets the library API key.
 *
 * testTusUploadAuthorization() uses made up values and sends no requests.
 *
 * testTusUploadToBunnyStream() talks to a real Bunny Stream library, set these in wp-tests-config.php:
 *   FV_PLAYER_TEST_BUNNY_STREAM_API_KEY    - the library API key
 *   FV_PLAYER_TEST_BUNNY_STREAM_LIBRARY_ID - the library ID
 * It is skipped if either is missing. The video it uploads is deleted from the library again.
 */
final class FV_Player_BunnyStreamUploadTest extends FV_Player_UnitTestCase {

  /**
   * GUIDs of videos created in the Bunny Stream library, deleted in tearDown()
   */
  private $created_video_ids = array();

  public static function wpSetUpBeforeClass() {
    // Loads FV Player, so that $fv_fp exists
    parent::wpSetUpBeforeClass();

    $plugin_dir = dirname( __FILE__ ) . '/../../..';

    include_once $plugin_dir . '/models/class.fv-player-bunny_stream-api.php';

    // The video encoder libraries are only loaded in wp-admin, see fv_player_load_video_encoder_libs()
    if ( ! class_exists( 'FV_Player_Bunny_Stream' ) ) {
      include_once $plugin_dir . '/models/video-encoder/video-encoder.php';
      require_once $plugin_dir . '/includes/class.fv-player-wizard-base.php';
      require_once $plugin_dir . '/includes/class.fv-player-wizard-step-base.php';
      require_once $plugin_dir . '/models/class.fv-player-bunny_stream.php';
    }
  }

  protected function tearDown(): void {
    // Remove the test videos from the Bunny Stream library, also if the test failed
    foreach ( $this->created_video_ids as $video_id ) {
      wp_remote_request(
        'https://video.bunnycdn.com/library/' . FV_PLAYER_TEST_BUNNY_STREAM_LIBRARY_ID . '/videos/' . $video_id,
        array(
          'method'  => 'DELETE',
          'headers' => array( 'AccessKey' => FV_PLAYER_TEST_BUNNY_STREAM_API_KEY ),
          'timeout' => 25,
        )
      );
    }
    $this->created_video_ids = array();

    parent::tearDown();
  }

  public function testTusUploadAuthorization() {
    global $fv_fp;

    $fv_fp->conf['bunny_stream'] = array(
      'lib_id'  => '123456',
      'api_key' => 'test-api-key',
    );

    $api = new FV_Player_Bunny_Stream_API();

    /**
     * Fixed values, so that the expected signature below can be precomputed.
     *
     * It's safe to use a fixed video ID: this test sends no requests, the GUID only goes into the hash.
     * It's the GUID of a video once uploaded by hand while testing, but even if a video with this GUID
     * existed in some library, the signature is made with the made up library ID and API key above,
     * so Bunny Stream would refuse it. The expiration is fixed (2027-01-15) so the signature can be precomputed, it does not matter that it will be in the past one day.
     */
    $video_id   = 'f0efd384-1f09-44bb-819e-7a9f1a357805';
    $expiration = 1800000000;

    $auth = $api->get_tus_upload_authorization( $video_id, $expiration );

    $this->assertSame( 'https://video.bunnycdn.com/tusupload', $auth['endpoint'] );
    $this->assertSame( '123456', $auth['library_id'] );
    $this->assertSame( $video_id, $auth['video_id'] );
    $this->assertSame( $expiration, $auth['expire'] );

    // Hex SHA-256 of library_id + api_key + expiration_time + video_id, see https://bunny.net/docs/stream/tus-resumable-uploads
    // Precomputed: printf '%s' '123456test-api-key1800000000f0efd384-1f09-44bb-819e-7a9f1a357805' | sha256sum
    $this->assertSame( 'ea35689f366365d4a0443631b9ad730461515a5d793661062821cf32fd82370c', $auth['signature'] );

    // The API key itself must not be in what goes to the browser
    $this->assertStringNotContainsString( 'test-api-key', wp_json_encode( $auth ) );
  }

  /**
   * Does what the browser upload does, but from PHP:
   *
   * 1. Create the video in Bunny Stream and get the upload signature - the same server code as the fv_player_bunny_stream_submit AJAX call
   * 2. TUS: Check that a bad signature is refused
   * 3. TUS: Create the upload with the signature, Bunny Stream returns its URL in the Location header
   * 4. TUS: Upload the file with PATCH
   * 5. TUS: Check that Bunny Stream has the whole file with HEAD
   *
   * Then tearDown() deletes the video.
   */
  public function testTusUploadToBunnyStream() {
    global $fv_fp;

    if ( ! defined( 'FV_PLAYER_TEST_BUNNY_STREAM_API_KEY' ) || ! FV_PLAYER_TEST_BUNNY_STREAM_API_KEY ) {
      $this->markTestSkipped( 'FV_PLAYER_TEST_BUNNY_STREAM_API_KEY is not set in wp-tests-config.php' );
    }

    if ( ! defined( 'FV_PLAYER_TEST_BUNNY_STREAM_LIBRARY_ID' ) || ! FV_PLAYER_TEST_BUNNY_STREAM_LIBRARY_ID ) {
      $this->markTestSkipped( 'FV_PLAYER_TEST_BUNNY_STREAM_LIBRARY_ID is not set in wp-tests-config.php' );
    }

    $file = DIR_TESTDATA . '/uploads/small-video.mp4';
    $this->assertFileExists( $file );
    $size = filesize( $file );

    $fv_fp->conf['bunny_stream'] = array(
      'lib_id'  => (string) FV_PLAYER_TEST_BUNNY_STREAM_LIBRARY_ID,
      'api_key' => FV_PLAYER_TEST_BUNNY_STREAM_API_KEY,
    );

    $bunny = FV_Player_Bunny_Stream();
    $bunny->plugin_update_database();

    // 1. Create the video
    $job_id = $bunny->job_create( array(
      'source' => 'fv-player-phpunit-tus.mp4',
      'target' => 'fv-player-phpunit-tus.mp4',
    ) );
    $this->assertGreaterThan( 0, $job_id );

    $ret = $bunny->job_submit( $job_id );

    $this->assertSame( 'processing', $ret['status'], 'Bunny Stream Create Video failed: ' . wp_json_encode( $ret['result'] ) );
    $this->created_video_ids[] = $ret['result']->guid;

    $this->assertArrayHasKey( 'tus', $ret );
    $tus = $ret['tus'];

    $this->assertSame( 'https://video.bunnycdn.com/tusupload', $tus['endpoint'] );
    $this->assertSame( (string) FV_PLAYER_TEST_BUNNY_STREAM_LIBRARY_ID, $tus['library_id'] );
    $this->assertSame( $ret['result']->guid, $tus['video_id'] );
    $this->assertGreaterThan( time(), $tus['expire'] );

    // Nothing that goes to the browser contains the API key
    $this->assertStringNotContainsString( FV_PLAYER_TEST_BUNNY_STREAM_API_KEY, wp_json_encode( $ret ) );

    $headers = array(
      'AuthorizationSignature' => $tus['signature'],
      'AuthorizationExpire'    => (string) $tus['expire'],
      'VideoId'                => $tus['video_id'],
      'LibraryId'              => $tus['library_id'],
      'Tus-Resumable'          => '1.0.0',
    );

    $create_headers = array_merge( $headers, array(
      'Upload-Length'   => (string) $size,
      'Upload-Metadata' => 'filetype ' . base64_encode( 'video/mp4' ) . ',title ' . base64_encode( 'fv-player-phpunit-tus.mp4' ),
    ) );

    // 2. A bad signature is refused
    $response = wp_remote_post( $tus['endpoint'], array(
      'headers' => array_merge( $create_headers, array( 'AuthorizationSignature' => str_repeat( '0', 64 ) ) ),
      'timeout' => 25,
    ) );
    $this->assertNotWPError( $response );
    $this->assertSame( 401, wp_remote_retrieve_response_code( $response ), 'Bunny Stream accepted a bad TUS signature' );

    // 3. Create the upload
    $response = wp_remote_post( $tus['endpoint'], array(
      'headers' => $create_headers,
      'timeout' => 25,
    ) );
    $this->assertNotWPError( $response );
    $this->assertSame( 201, wp_remote_retrieve_response_code( $response ), 'TUS upload creation failed: ' . wp_remote_retrieve_body( $response ) );

    $location = wp_remote_retrieve_header( $response, 'location' );
    $this->assertNotEmpty( $location, 'TUS upload creation did not return the Location header' );

    // The Location is relative, like /tusupload/{id}, the JavaScript resolves it in the same way
    $upload_url = WP_Http::make_absolute_url( $location, $tus['endpoint'] );

    // 4. Upload the file
    $response = wp_remote_request( $upload_url, array(
      'method'  => 'PATCH',
      'headers' => array_merge( $headers, array(
        'Upload-Offset' => '0',
        'Content-Type'  => 'application/offset+octet-stream',
      ) ),
      'body'    => file_get_contents( $file ),
      'timeout' => 60,
    ) );
    $this->assertNotWPError( $response );
    $this->assertSame( 204, wp_remote_retrieve_response_code( $response ), 'TUS upload failed: ' . wp_remote_retrieve_body( $response ) );
    $this->assertSame( (string) $size, wp_remote_retrieve_header( $response, 'upload-offset' ) );

    // 5. Bunny Stream has the whole file
    $response = wp_remote_head( $upload_url, array(
      'headers' => $headers,
      'timeout' => 25,
    ) );
    $this->assertNotWPError( $response );
    $this->assertSame( 200, wp_remote_retrieve_response_code( $response ) );
    $this->assertSame( (string) $size, wp_remote_retrieve_header( $response, 'upload-offset' ) );
  }

}
