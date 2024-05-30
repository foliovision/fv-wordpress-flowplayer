<?php

require_once( dirname(__FILE__).'/../fv-player-ajax-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_videoPositionSavingTestCase extends FV_Player_Ajax_UnitTestCase {

  var $postID = -1;
  var $userID = -1;

  protected $backupGlobals = false;

  protected function setUp(): void {
    parent::setUp();

    // create a post with playlist shortcode
    $this->postID = $this->factory->post->create( array(
      'post_title' => 'Playlist with Ads',
      'post_content' => '[fvplayer src="https://cdn.site.com/1.mp4" playlist="https://cdn.site.com/2.mp4;https://cdn.site.com/3.mp4" saveposition="yes"]'
    ) );

    global $fv_fp;

    include_once "../../../fv-wordpress-flowplayer/models/flowplayer.php";
    include_once "../../../fv-wordpress-flowplayer/models/flowplayer-frontend.php";
    $fv_fp = new flowplayer_frontend();

    // add new user and create last saved position metadata for this new user
    $this->userID = $this->factory->user->create(array(
      'role' => 'admin'
    ));

    /*add_user_meta($this->userID, 'fv_wp_flowplayer_position_watch?v=1XiHhpGUmQg', '12');
    var_export(get_user_meta($this->userID, 'fv_wp_flowplayer_position_watch?v=1XiHhpGUmQg', true ));*/

  }

  // For not logged in users the video position saving Ajax should not do anything
  public function testNoSaveForNotLoggedInUsers() {
    // is anybody listening out there?
    $this->assertTrue( has_action('wp_ajax_fv_wp_flowplayer_video_position_save') );

    // Spoof the nonce in the POST superglobal
    //$_POST['_wpnonce'] = wp_create_nonce( 'anything-here-if-needed' );

    // set up POST data for video resume times
    $_POST['action'] = 'fv_wp_flowplayer_video_position_save';
    $_POST['videoTimes'] = urlencode( json_encode( array(
      array(
        'name' => 'https://cdn.site.com/2.mp4',
        'position' => 12
      )
    ) ) );

    // call the AJAX which
    try {
      $this->_handleAjax( 'fv_wp_flowplayer_video_position_save' );
    } catch ( WPAjaxDieContinueException $e ) {
      $response = json_decode( $this->_last_response );
      $this->assertIsObject($response );
      $this->assertObjectHasProperty( 'success', $response );
      $this->assertFalse( $response->success );
    }

    // check for clear playlist HTML without last player position data items
    $post = get_post( $this->postID );
    $output = apply_filters( 'the_content', $post->post_content );

    $this->assertTrue( substr_count( $output, "data-item" ) == 3 );
    $this->assertTrue( substr_count( $output, '"position"' ) == 0 );
    $this->assertTrue( substr_count( $output, '"saw":true' ) == 0 );
  }

  // For logged in users the video position saving Ajax should affect the FV Player output - adding position to data-item
  public function testSaveAndPlaylistHTMLForLoggedInUsers() {
    global $FV_Player_Position_Save;

    // is anybody listening out there?
    $this->assertTrue( has_action('wp_ajax_fv_wp_flowplayer_video_position_save') );

    // Make sure the user video position tables are present
    do_action( 'fv_player_update' );

    // set this user as the active one
    wp_set_current_user($this->userID);

    // set up POST data for video resume times
    $_POST['action'] = 'fv_wp_flowplayer_video_position_save';
    $_POST['videoTimes'] = urlencode( json_encode( array(
      array(
        'name' => 'https://cdn.site.com/2.mp4',
        'position' => 12,
        'top_position' => 32
      )
    ) ) );

    // Spoof the nonce in the POST superglobal
    $_POST['nonce'] = wp_create_nonce( 'fv_player_video_position_save' );

    // call the AJAX which
    try {
      $this->_handleAjax( 'fv_wp_flowplayer_video_position_save' );
    } catch ( WPAjaxDieContinueException $e ) {
      $response = json_decode( $this->_last_response );
      $this->assertIsObject( $response );
      $this->assertObjectHasProperty( 'success', $response );
      $this->assertTrue( $response->success );
    }

    // check if metadata was saved correctly
    $this->assertEquals( 12, $FV_Player_Position_Save->get_video_position( $this->userID, 2, 'last_position' ) );
    $this->assertEquals( 32, $FV_Player_Position_Save->get_video_position( $this->userID, 2, 'top_position' ) );

    // check that the playlist HTML is being generated correctly, with the last player position taken into consideration
    $post = get_post( $this->postID );
    $output = apply_filters( 'the_content', $post->post_content );

    $this->assertTrue( substr_count( $output, "data-item" ) == 3 );
    $this->assertTrue( substr_count( $output, '"position":12,"top_position":32' ) == 1 );
    $this->assertTrue( substr_count( $output, '"saw":true' ) == 0 );

    // another request, this time with lower top position being sent
    $this->_last_response = '';

    $_POST['action'] = 'fv_wp_flowplayer_video_position_save';
    $_POST['videoTimes'] = urlencode( json_encode( array(
      array(
        'name' => 'https://cdn.site.com/2.mp4',
        'position' => 10,
        'top_position' => 24
      )
    ) ) );

    try {
      $this->_handleAjax( 'fv_wp_flowplayer_video_position_save' );
    } catch ( WPAjaxDieContinueException $e ) {
      $response = json_decode( $this->_last_response );
      $this->assertIsObject( $response );
      $this->assertObjectHasProperty( 'success', $response );
      $this->assertTrue( $response->success );
    }

    // check if metadata was saved correctly
    $this->assertEquals( 10, $FV_Player_Position_Save->get_video_position( $this->userID, 2, 'last_position' ) );
    // however the previous top position should still be stored as it was bigger
    $this->assertEquals( 32, $FV_Player_Position_Save->get_video_position( $this->userID, 2, 'top_position' ) );

    // finally a request indicating the user saw the whole video
    $this->_last_response = '';

    $_POST['action'] = 'fv_wp_flowplayer_video_position_save';
    $_POST['videoTimes'] = urlencode( json_encode( array(
      array(
        'name' => 'https://cdn.site.com/2.mp4',
        'position' => 12,
        'top_position' => 120,
        'saw' => true
      )
    ) ) );

    try {
      $this->_handleAjax( 'fv_wp_flowplayer_video_position_save' );
    } catch ( WPAjaxDieContinueException $e ) {
      $response = json_decode( $this->_last_response );
      $this->assertIsObject( $response );
      $this->assertObjectHasProperty( 'success', $response );
      $this->assertTrue( $response->success );
    }

    // check if metadata was saved correctly
    $this->assertEquals( 12, $FV_Player_Position_Save->get_video_position( $this->userID, 2, 'last_position' ) );
    // however the top position should no longer be stored
    $this->assertEquals( 0, $FV_Player_Position_Save->get_video_position( $this->userID, 2, 'top_position' ) );
    // and it shoudl be remembered user saw the video
    $this->assertEquals( 1, $FV_Player_Position_Save->get_video_position( $this->userID, 2, 'finished' )  );

    // check that the playlist HTML is being generated correctly, with the last player position taken into consideration and saw flag present
    $post = get_post( $this->postID );
    $output = apply_filters( 'the_content', $post->post_content );

    $this->assertTrue( substr_count( $output, "data-item" ) == 3 );
    $this->assertTrue( substr_count( $output, '"position":12,"saw":true' ) == 1 );
  }

}
