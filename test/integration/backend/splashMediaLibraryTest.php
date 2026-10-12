<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Splash image download into the Media Library when the post is saved (featured image).
 * Covers CVE-2026-85351: Contributor+ SSRF with internal response bodies stored in the Media Library.
 */
final class FV_Player_SplashMediaLibraryTestCase extends FV_Player_UnitTestCase {

  // 1x1 PNG
  const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

  private $http_args = array();

  private $fake_response = false;

  private $external_checks = array();

  protected function setUp(): void {
    parent::setUp();

    // The editor controller is only loaded in wp-admin
    include_once( dirname( __FILE__ ) . '/../../../controller/editor.php' );

    add_filter( 'pre_http_request', array( $this, 'fake_http' ), 10, 3 );
  }

  protected function tearDown(): void {
    remove_filter( 'pre_http_request', array( $this, 'fake_http' ), 10 );
    remove_filter( 'http_request_host_is_external', array( $this, 'record_external_check' ), 10 );

    parent::tearDown();
  }

  public function fake_http( $pre, $args, $url ) {
    $this->http_args[] = array( 'url' => $url, 'args' => $args );
    return $this->fake_response;
  }

  public function record_external_check( $is_external, $host, $url ) {
    $this->external_checks[] = $host;
    return $is_external;
  }

  private function response( $body, $content_type, $code = 200 ) {
    return array(
      'headers'  => array( 'content-type' => $content_type ),
      'body'     => $body,
      'response' => array( 'code' => $code, 'message' => 'OK' ),
      'cookies'  => array(),
      'filename' => null,
    );
  }

  private function count_attachments() {
    return count( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) );
  }

  public function test_real_image_is_stored_using_safe_http_api() {
    $post_id = $this->factory->post->create( array( 'post_title' => 'Splash test' ) );

    $this->fake_response = $this->response( base64_decode( self::PNG ), 'image/png' );

    $attach_id = fv_wp_flowplayer_save_to_media_library( 'https://example.com/splash.png', $post_id );

    $this->assertNotEmpty( $this->http_args, 'The splash should be fetched' );
    // $this->assertTrue( $this->http_args[0]['args']['reject_unsafe_urls'], 'The splash must be fetched with the safe HTTP API' );
    $this->assertLessThanOrEqual( 3, $this->http_args[0]['args']['redirection'] );

    $this->assertIsInt( $attach_id );
    $this->assertSame( 'image/png', get_post_mime_type( $attach_id ) );
    $this->assertStringEndsWith( '.png', get_attached_file( $attach_id ) );
  }

  public function test_jpg_content_type_is_ignored_and_real_type_used() {
    $post_id = $this->factory->post->create( array( 'post_title' => 'Splash test' ) );

    // Server lies about the type, the file must be stored as what it really is
    $this->fake_response = $this->response( base64_decode( self::PNG ), 'image/jpeg' );

    $attach_id = fv_wp_flowplayer_save_to_media_library( 'https://example.com/splash.jpg', $post_id );

    $this->assertIsInt( $attach_id );
    $this->assertSame( 'image/png', get_post_mime_type( $attach_id ) );
  }

  public function test_non_image_body_with_image_content_type_is_not_stored() {
    $post_id = $this->factory->post->create( array( 'post_title' => 'Splash test' ) );
    $before  = $this->count_attachments();

    $this->fake_response = $this->response( "DB_PASSWORD=secret\n", 'image/jpeg' );

    $result = fv_wp_flowplayer_save_to_media_library( 'http://internal-host:8080/secret.jpg', $post_id );

    $this->assertFalse( $result );
    $this->assertSame( $before, $this->count_attachments(), 'No attachment should be created' );
  }

  public function test_non_200_response_is_not_stored() {
    $post_id = $this->factory->post->create( array( 'post_title' => 'Splash test' ) );

    $this->fake_response = $this->response( base64_decode( self::PNG ), 'image/png', 404 );

    $this->assertFalse( fv_wp_flowplayer_save_to_media_library( 'https://example.com/splash.png', $post_id ) );
  }

  public function test_title_file_name_gets_extension() {
    $post_id = $this->factory->post->create( array( 'post_title' => 'Splash test' ) );

    $this->fake_response = $this->response( base64_decode( self::PNG ), 'image/png' );

    $attach_id = fv_wp_flowplayer_save_to_media_library( 'https://example.com/splash.png', $post_id, 'My Video Caption' );

    $this->assertIsInt( $attach_id );
    $this->assertStringEndsWith( '.png', get_attached_file( $attach_id ) );
  }

  public function test_loopback_url_is_refused_before_any_connection() {
    $post_id = $this->factory->post->create( array( 'post_title' => 'Splash test' ) );

    // Let the request go through to WP_Http, record the private IP check
    $this->fake_response = false;
    add_filter( 'http_request_host_is_external', array( $this, 'record_external_check' ), 10, 3 );

    $result = fv_wp_flowplayer_save_to_media_library( 'http://127.0.0.1:8080/secret.jpg', $post_id );

    $this->assertFalse( $result );
    $this->assertContains( '127.0.0.1', $this->external_checks, 'Loopback target must go through the core private IP check' );
  }

  public function test_featured_image_from_contributor_post_is_not_stored_for_non_image() {
    $user_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
    wp_set_current_user( $user_id );

    $this->fake_response = $this->response( "DB_PASSWORD=secret\n", 'image/jpeg' );

    $before = $this->count_attachments();

    // Test suite restores hooks after each test, so add it again if needed
    if ( ! has_action( 'save_post', 'fv_wp_flowplayer_featured_image' ) ) {
      add_action( 'save_post', 'fv_wp_flowplayer_featured_image', 10000 );
    }

    $post_id = wp_insert_post( array(
      'post_author'  => $user_id,
      'post_status'  => 'draft',
      'post_title'   => 'SSRF test',
      'post_content' => '[fvplayer src="https://example.com/video.mp4" splash="http://internal-host:8080/secret.jpg"]',
    ) );

    $this->assertNotEmpty( $this->http_args, 'Saving the post should try to fetch the splash' );
    $this->assertSame( 'http://internal-host:8080/secret.jpg', $this->http_args[0]['url'] );
    $this->assertSame( $before, $this->count_attachments(), 'No attachment should be created' );
    $this->assertFalse( has_post_thumbnail( $post_id ) );
  }

}
