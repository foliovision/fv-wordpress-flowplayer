<?php

require_once( dirname(__FILE__).'/../fv-player-ajax-unittest-case.php');

/**
 * Video encoder (Bunny Stream) access:
 * - only users with manage_options can see and change the encoder settings, including the API key
 * - users without edit_others_posts can only list and delete their own encoding jobs
 */
final class FV_Player_EncoderJobsAccessTestCase extends FV_Player_Ajax_UnitTestCase {

  private $admin_id;
  private $editor_id;
  private $contributor_id;

  private $admin_job;
  private $contributor_job;

  private $http_requests = array();

  protected function setUp(): void {
    parent::setUp();

    global $fv_fp;

    set_current_screen( 'fv-player_page_fv_player_bunny_stream' );
    add_screen_option( 'per_page', array( 'default' => 25, 'option' => 'fv_player_bunny_stream_per_page' ) );

    // Normally loaded in wp-admin only, _set_conf() needs fv_player_handle_secrets()
    include_once dirname( __FILE__ ) . '/../../../controller/settings.php';

    // Normally loaded by fv_player_bunny_stream_include() on plugins_loaded, but only in wp-admin
    if ( ! function_exists( 'FV_Player_Bunny_Stream' ) ) {
      include_once dirname( __FILE__ ) . '/../../../models/video-encoder/video-encoder.php';
      include_once dirname( __FILE__ ) . '/../../../includes/class.fv-player-wizard-base.php';
      include_once dirname( __FILE__ ) . '/../../../includes/class.fv-player-wizard-step-base.php';
      include_once dirname( __FILE__ ) . '/../../../models/class.fv-player-bunny_stream.php';
    }

    FV_Player_Bunny_Stream()->plugin_update_database();
    FV_Player_Bunny_Stream()->include_listing_lib();

    // The hooks added when the class was first loaded are gone after the first test, as WP_UnitTestCase restores hooks after each test
    if ( ! has_action( 'wp_ajax_fv_player_bunny_stream_delete_job' ) ) {
      add_action( 'wp_ajax_fv_player_bunny_stream_delete_job', array( FV_Player_Bunny_Stream(), 'ajax_fv_player_delete_job' ) );
    }

    global $wpdb;
    $wpdb->query( "DELETE FROM `{$wpdb->prefix}fv_player_encoding_jobs`" );

    $fv_fp->conf['bunny_stream'] = array(
      'api_key'      => 'secret-library-api-key',
      'lib_id'       => '123456',
      'cdn_hostname' => 'vz-test.b-cdn.net',
    );
    $fv_fp->_set_conf( $fv_fp->conf );

    $this->admin_id       = self::factory()->user->create( array( 'role' => 'administrator' ) );
    $this->editor_id      = self::factory()->user->create( array( 'role' => 'editor' ) );
    $this->contributor_id = self::factory()->user->create( array( 'role' => 'contributor' ) );

    $this->admin_job       = $this->create_job( $this->admin_id, 'admin-private-source' );
    $this->contributor_job = $this->create_job( $this->contributor_id, 'contributor-own-source' );

    // Record and block any outgoing HTTP request
    $this->http_requests = array();
    add_filter( 'pre_http_request', array( $this, 'block_http' ), 10, 3 );
  }

  protected function tearDown(): void {
    remove_filter( 'pre_http_request', array( $this, 'block_http' ), 10 );
    $_GET = array();
    $_POST = array();
    parent::tearDown();
  }

  public function block_http( $pre, $args, $url ) {
    $this->http_requests[] = $url;
    return new WP_Error( 'http_blocked', 'HTTP blocked in tests' );
  }

  private function create_job( $author, $name ) {
    global $wpdb;

    $wpdb->insert( $wpdb->prefix . 'fv_player_encoding_jobs', array(
      'id_video'     => 0,
      'job_id'       => 'guid-' . $name,
      'date_created' => gmdate( 'Y-m-d H:i:s' ),
      'date_checked' => gmdate( 'Y-m-d H:i:s' ),
      'source'       => 'https://example.com/' . $name . '.mp4',
      'target'       => $name,
      'type'         => 'bunny_stream',
      'status'       => 'completed',
      'args'         => wp_json_encode( array( 'target' => $name ) ),
      'author'       => $author,
    ) );

    return $wpdb->insert_id;
  }

  private function job_exists( $id ) {
    global $wpdb;
    return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE id = %d", $id ) );
  }

  private function render_tools_panel( $user_id, $panel = false ) {
    wp_set_current_user( $user_id );

    $_GET = array( 'page' => 'fv_player_bunny_stream' );
    if ( $panel ) {
      $_GET['panel'] = $panel;
    }

    ob_start();
    try {
      FV_Player_Bunny_Stream()->tools_panel();
    } finally {
      $html = ob_get_clean();
    }
    return $html;
  }

  private function post_settings( $user_id ) {
    wp_set_current_user( $user_id );

    $_POST = array(
      'fv_player_bunny_stream_settings_nonce' => wp_create_nonce( 'fv_player_bunny_stream_settings_nonce' ),
      'bunny_stream' => array(
        'api_key'      => 'attacker-api-key',
        'lib_id'       => '999999',
        'cdn_hostname' => 'attacker.b-cdn.net',
      ),
    );

    $html = $this->render_tools_panel( $user_id, 'settings' );

    $_POST = array();

    return $html;
  }

  private function get_saved_api_key() {
    $options = get_option( 'fvwpflowplayer' );
    return $options['bunny_stream']['api_key'];
  }

  /**
   * Runs the delete AJAX handler as the given user, with a nonce valid for that user and job.
   */
  private function delete_job( $user_id, $job_id ) {
    wp_set_current_user( $user_id );

    $this->_last_response = '';

    $_POST = array(
      'action' => 'fv_player_bunny_stream_delete_job',
      'id_row' => $job_id,
      'nonce'  => wp_create_nonce( 'fv-player-encoder-delete-job-' . $job_id ),
    );

    try {
      $this->_handleAjax( 'fv_player_bunny_stream_delete_job' );
    } catch ( WPAjaxDieContinueException $e ) {
      unset( $e );
    } catch ( WPAjaxDieStopException $e ) {
      unset( $e );
    }

    return json_decode( $this->_last_response, true );
  }

  public function testHooks() {
    $this->assertTrue( has_action( 'wp_ajax_fv_player_bunny_stream_delete_job' ) !== false );
    $this->assertTrue( FV_Player_Bunny_Stream()->is_configured() );
  }

  public function testContributorCannotSeeSettings() {
    $html = $this->render_tools_panel( $this->contributor_id, 'settings' );

    $this->assertStringNotContainsString( 'secret-library-api-key', $html );
    $this->assertStringNotContainsString( 'fv_player_bunny_stream_settings_nonce', $html );
    $this->assertStringNotContainsString( 'panel=settings', $html );
    $this->assertStringContainsString( 'You are not allowed to change the Bunny Stream settings.', $html );
  }

  public function testContributorCannotSaveSettings() {
    $this->post_settings( $this->contributor_id );
    $this->assertSame( 'secret-library-api-key', $this->get_saved_api_key() );

    // Also when calling the settings screen directly
    wp_set_current_user( $this->contributor_id );
    $_POST = array(
      'fv_player_bunny_stream_settings_nonce' => wp_create_nonce( 'fv_player_bunny_stream_settings_nonce' ),
      'bunny_stream' => array( 'api_key' => 'attacker-api-key' ),
    );
    ob_start();
    FV_Player_Bunny_Stream()->tools_panel_settings();
    $html = ob_get_clean();

    $this->assertStringNotContainsString( 'secret-library-api-key', $html );
    $this->assertSame( 'secret-library-api-key', $this->get_saved_api_key() );
  }

  public function testEditorCannotSeeSettings() {
    $html = $this->render_tools_panel( $this->editor_id, 'settings' );
    $this->assertStringNotContainsString( 'secret-library-api-key', $html );

    $this->post_settings( $this->editor_id );
    $this->assertSame( 'secret-library-api-key', $this->get_saved_api_key() );
  }

  public function testAdminCanSeeAndSaveSettings() {
    $html = $this->render_tools_panel( $this->admin_id, 'settings' );
    $this->assertStringContainsString( 'secret-library-api-key', $html );
    $this->assertStringContainsString( 'panel=settings', $html );

    $html = $this->post_settings( $this->admin_id );
    $this->assertStringContainsString( 'Settings saved', $html );
    $this->assertSame( 'attacker-api-key', $this->get_saved_api_key() );
  }

  public function testContributorNotConfiguredGetsNoSettings() {
    global $fv_fp;
    $fv_fp->conf['bunny_stream'] = array( 'api_key' => '', 'lib_id' => '', 'cdn_hostname' => '' );
    $this->assertFalse( FV_Player_Bunny_Stream()->is_configured() );

    $html = $this->render_tools_panel( $this->contributor_id );

    $this->assertStringContainsString( 'is not set up yet', $html );
    $this->assertStringNotContainsString( 'fv_player_bunny_stream_settings_nonce', $html );
  }

  public function testContributorListsOnlyOwnJobs() {
    $html = $this->render_tools_panel( $this->contributor_id );

    $this->assertStringContainsString( 'contributor-own-source', $html );
    $this->assertStringNotContainsString( 'admin-private-source', $html );
    $this->assertStringNotContainsString( 'data-id="' . $this->admin_job . '"', $html );
    $this->assertStringContainsString( '1 item', $html );
  }

  public function testEditorListsAllJobs() {
    $html = $this->render_tools_panel( $this->editor_id );

    $this->assertStringContainsString( 'contributor-own-source', $html );
    $this->assertStringContainsString( 'admin-private-source', $html );
    $this->assertStringContainsString( 'data-id="' . $this->admin_job . '"', $html );
    $this->assertStringContainsString( '2 items', $html );
  }

  public function testContributorHeartbeatRowsOnlyOwnJobs() {
    wp_set_current_user( $this->contributor_id );

    $rows = FV_Player_Bunny_Stream()->get_updated_rows( array( $this->admin_job, $this->contributor_job ) );

    $this->assertArrayNotHasKey( $this->admin_job, $rows );
    $this->assertStringContainsString( 'contributor-own-source', $rows[ $this->contributor_job ] );
  }

  public function testContributorCannotDeleteOtherUsersJob() {
    $response = $this->delete_job( $this->contributor_id, $this->admin_job );

    $this->assertSame( 'You are not allowed to delete this job.', $response['error'] );
    $this->assertTrue( $this->job_exists( $this->admin_job ) );
  }

  public function testContributorCanDeleteOwnJob() {
    $response = $this->delete_job( $this->contributor_id, $this->contributor_job );

    $this->assertSame( 'Job deleted successfully', $response['success'] );
    $this->assertFalse( $this->job_exists( $this->contributor_job ) );
  }

  public function testEditorCanDeleteOtherUsersJob() {
    $response = $this->delete_job( $this->editor_id, $this->admin_job );

    $this->assertSame( 'Job deleted successfully', $response['success'] );
    $this->assertFalse( $this->job_exists( $this->admin_job ) );
  }

  public function testDeleteWithoutNonceRefused() {
    wp_set_current_user( $this->admin_id );

    $_POST = array(
      'action' => 'fv_player_bunny_stream_delete_job',
      'id_row' => $this->admin_job,
    );

    try {
      $this->_handleAjax( 'fv_player_bunny_stream_delete_job' );
    } catch ( WPAjaxDieContinueException $e ) {
      unset( $e );
    } catch ( WPAjaxDieStopException $e ) {
      unset( $e );
    }

    $response = json_decode( $this->_last_response, true );
    $this->assertSame( 'Bad nonce', $response['error'] );
    $this->assertTrue( $this->job_exists( $this->admin_job ) );
  }

}
