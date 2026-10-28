<?php

require_once( dirname(__FILE__).'/../fv-player-ajax-unittest-case.php');

/**
 * The wizard AJAX steps (FV_Player_Wizard_Base_Class::ajax()) must require
 * the wizard capability (manage_options), a valid nonce is not enough.
 *
 * CVE-2026-108068: a Contributor could harvest the nonce from
 * wp-admin/admin.php?page=fv_player_migration and then list all the video
 * and video meta rows (step 1) and replace URLs site-wide (step 3).
 */
final class FV_Player_MigrationWizardAjaxTestCase extends FV_Player_Ajax_UnitTestCase {

  private $admin_id;
  private $contributor_id;

  private $video_id;

  private $http_requests = array();

  protected function setUp(): void {
    parent::setUp();

    global $FV_Player_Db;

    $this->admin_id       = self::factory()->user->create( array( 'role' => 'administrator' ) );
    $this->contributor_id = self::factory()->user->create( array( 'role' => 'contributor' ) );

    $author_id = self::factory()->user->create( array( 'role' => 'author' ) );
    wp_set_current_user( $author_id );

    $video = new FV_Player_Db_Video( null, array(
      'src'     => 'https://victim-cdn.example.com/private-draft.mp4',
      'caption' => 'Draft video',
      'splash'  => 'https://victim-cdn.example.com/private-draft.jpg',
    ), $FV_Player_Db );
    $this->video_id = $video->save( array(
      array( 'meta_key' => 'note', 'meta_value' => 'https://victim-cdn.example.com/meta-secret' ),
    ) );

    $player = new FV_Player_Db_Player( null, array( 'videos' => (string) $this->video_id ), $FV_Player_Db );
    $player->save();

    wp_set_current_user( 0 );

    // Record and block any outgoing HTTP request, the Bunny Stream wizard steps call the Bunny API
    $this->http_requests = array();
    add_filter( 'pre_http_request', array( $this, 'block_http' ), 10, 3 );
  }

  protected function tearDown(): void {
    remove_filter( 'pre_http_request', array( $this, 'block_http' ), 10 );
    parent::tearDown();
  }

  public function block_http( $pre, $args, $url ) {
    $this->http_requests[] = $url;
    return new WP_Error( 'http_blocked', 'HTTP blocked in tests' );
  }

  /**
   * Runs the wizard AJAX step as the given user, with a nonce valid for that user.
   */
  private function run_step( $user_id, $wizard_id, $post ) {
    wp_set_current_user( $user_id );

    $this->_last_response = '';

    $_POST = array_merge( array(
      'action' => $wizard_id . '_step',
      'nonce'  => wp_create_nonce( $wizard_id ),
    ), $post );

    // PHP 8.2+ dynamic property deprecation notices in the step classes would break the JSON
    $error_reporting = error_reporting( error_reporting() & ~E_DEPRECATED );

    try {
      $this->_handleAjax( $wizard_id . '_step' );
    } catch ( WPAjaxDieContinueException $e ) {
      unset( $e );
    } catch ( WPAjaxDieStopException $e ) {
      unset( $e );
    }

    error_reporting( $error_reporting );

    return $this->_last_response;
  }

  private function get_video_src() {
    global $wpdb;
    return $wpdb->get_var( $wpdb->prepare( "SELECT src FROM `{$wpdb->prefix}fv_player_videos` WHERE id = %d", $this->video_id ) );
  }

  private function get_video_meta_value() {
    global $wpdb;
    return $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM `{$wpdb->prefix}fv_player_videometa` WHERE id_video = %d", $this->video_id ) );
  }

  public function testHooks() {
    $this->assertTrue( has_action( 'wp_ajax_fv_player_migration_wizard_step' ) !== false );
    $this->assertSame( 'manage_options', FV_Player_Migration_Wizard()->get_capability() );
  }

  public function testContributorCannotListVideos() {
    $response = $this->run_step( $this->contributor_id, 'fv_player_migration_wizard', array(
      'step_name'        => 'FV_Player_Wizard_Step_1_Search_Videos',
      'video_src_search' => array( 'search_string' => 'victim-cdn.example.com' ),
    ) );

    $this->assertStringNotContainsString( 'private-draft', $response );
    $this->assertStringNotContainsString( 'meta-secret', $response );

    $json = json_decode( $response, true );
    $this->assertSame( 'You are not allowed to use this wizard.', $json['error'] );
    $this->assertArrayNotHasKey( 'next_step', $json );
  }

  public function testContributorCannotReplace() {
    $response = $this->run_step( $this->contributor_id, 'fv_player_migration_wizard', array(
      'step_name'      => 'FV_Player_Wizard_Step_3_Test_Replace',
      'search_string'  => 'victim-cdn.example.com',
      'replace_string' => 'attacker.example.net',
      'confirmation'   => 'on',
    ) );

    $json = json_decode( $response, true );
    $this->assertSame( 'You are not allowed to use this wizard.', $json['error'] );

    $this->assertSame( 'https://victim-cdn.example.com/private-draft.mp4', $this->get_video_src() );
    $this->assertSame( 'https://victim-cdn.example.com/meta-secret', $this->get_video_meta_value() );
  }

  public function testEditorCannotReplace() {
    $editor_id = self::factory()->user->create( array( 'role' => 'editor' ) );

    $response = $this->run_step( $editor_id, 'fv_player_migration_wizard', array(
      'step_name'      => 'FV_Player_Wizard_Step_3_Test_Replace',
      'search_string'  => 'victim-cdn.example.com',
      'replace_string' => 'attacker.example.net',
      'confirmation'   => 'on',
    ) );

    $json = json_decode( $response, true );
    $this->assertSame( 'You are not allowed to use this wizard.', $json['error'] );
    $this->assertSame( 'https://victim-cdn.example.com/private-draft.mp4', $this->get_video_src() );
  }

  public function testAdminWithoutNonceRefused() {
    $response = $this->run_step( $this->admin_id, 'fv_player_migration_wizard', array(
      'nonce'          => '',
      'step_name'      => 'FV_Player_Wizard_Step_3_Test_Replace',
      'search_string'  => 'victim-cdn.example.com',
      'replace_string' => 'new-cdn.example.org',
      'confirmation'   => 'on',
    ) );

    $json = json_decode( $response, true );
    $this->assertSame( 'Invalid nonce', $json['error'] );
    $this->assertSame( 'https://victim-cdn.example.com/private-draft.mp4', $this->get_video_src() );
  }

  public function testAdminCanListAndReplace() {
    $response = $this->run_step( $this->admin_id, 'fv_player_migration_wizard', array(
      'step_name'        => 'FV_Player_Wizard_Step_1_Search_Videos',
      'video_src_search' => array( 'search_string' => 'victim-cdn.example.com' ),
    ) );

    $json = json_decode( $response, true );
    $this->assertTrue( $json['ok'] );
    $this->assertStringContainsString( 'private-draft.mp4', $json['next_step'] );

    $response = $this->run_step( $this->admin_id, 'fv_player_migration_wizard', array(
      'step_name'      => 'FV_Player_Wizard_Step_3_Test_Replace',
      'search_string'  => 'victim-cdn.example.com',
      'replace_string' => 'new-cdn.example.org',
      'confirmation'   => 'on',
    ) );

    $json = json_decode( $response, true );
    $this->assertTrue( $json['ok'] );

    $this->assertSame( 'https://new-cdn.example.org/private-draft.mp4', $this->get_video_src() );
    $this->assertSame( 'https://new-cdn.example.org/meta-secret', $this->get_video_meta_value() );
  }

  public function testContributorGetsNoWizardMarkupOrNonce() {
    wp_set_current_user( $this->contributor_id );

    ob_start();
    FV_Player_Migration_Wizard()->view();
    $html = ob_get_clean();

    $this->assertStringContainsString( 'You are not allowed to use the FV Player Migration Wizard', $html );
    $this->assertStringNotContainsString( 'data-fv-player-wizard', $html );

    $data = wp_scripts()->get_data( 'fv_player_wizard_base', 'data' );
    $this->assertEmpty( $data );
  }

  public function testAdminGetsWizard() {
    wp_set_current_user( $this->admin_id );

    ob_start();
    FV_Player_Migration_Wizard()->view();
    $html = ob_get_clean();

    $this->assertStringContainsString( 'data-fv-player-wizard', $html );
    $this->assertStringContainsString( 'FV_Player_Wizard_Step_1_Search_Videos', $html );
  }

  public function testContributorCannotRunBunnyStreamWizard() {
    if ( ! function_exists( 'FV_Player_Bunny_Stream_Wizard' ) ) {
      // Normally loaded by fv_player_bunny_stream_include() on plugins_loaded, but only in wp-admin
      include_once dirname( __FILE__ ) . '/../../../models/video-encoder/video-encoder.php';
      require_once dirname( __FILE__ ) . '/../../../models/class.fv-player-bunny_stream.php';
    }

    $this->assertTrue( has_action( 'wp_ajax_fv_player_bunny_stream_wizard_step' ) !== false );

    $response = $this->run_step( $this->contributor_id, 'fv_player_bunny_stream_wizard', array(
      'step_name'                  => 'FV_Player_Bunny_Stream_Wizard_Stream_Libs',
      'bunnycdn_api'               => 'attacker-account-key',
      'fv_bunny_stream_wizard_lib' => '12345',
    ) );

    $json = json_decode( $response, true );
    $this->assertSame( 'You are not allowed to use this wizard.', $json['error'] );
    $this->assertSame( array(), $this->http_requests );
  }

}
