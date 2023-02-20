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

  public function setUp() {
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
      $this->assertInternalType( 'object', $response );
      $this->assertObjectHasAttribute( 'success', $response );
      $this->assertFalse( $response->success );
    }

    // check for clear playlist HTML without last player position data items
    $post = get_post( $this->postID );
    $output = apply_filters( 'the_content', $post->post_content );

    $sample = <<< HTML
<div id="wpfp_31180ef298e0fc79eff36d1114e09913" class="freedomplayer flowplayer no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-horizontal" data-fv-embed="?fv_player_embed=1" style="max-width: 100%; " data-ratio="0.5625" data-save-position="yes">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
<div class='fvp-share-bar'><ul class="fvp-sharing">
    <li><a class="sharing-facebook" href="https://www.facebook.com/sharer/sharer.php?u=" target="_blank"></a></li>
    <li><a class="sharing-twitter" href="https://twitter.com/intent/tweet?text=Test+Blog+&url=" target="_blank"></a></li>
    <li><a class="sharing-email" href="mailto:?body=Check%20out%20the%20amazing%20video%20here%3A%20" target="_blank"></a></li></ul><div><label><a class="embed-code-toggle" href="#"><strong>Embed</strong></a></label></div><div class="embed-code"><label>Copy and paste this HTML code into your webpage to embed.</label><textarea></textarea></div></div>
</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal skin-slim" rel="wpfp_31180ef298e0fc79eff36d1114e09913" id="wpfp_31180ef298e0fc79eff36d1114e09913_playlist">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/1.mp4","type":"video\/mp4"}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/2.mp4","type":"video\/mp4"}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/3.mp4","type":"video\/mp4"}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
	</div>
HTML;

    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }

  // For logged in users the video position saving Ajax should affect the FV Player output - adding position to data-item
  public function testSaveAndPlaylistHTMLForLoggedInUsers() {
    // is anybody listening out there?
    $this->assertTrue( has_action('wp_ajax_fv_wp_flowplayer_video_position_save') );
    
    // Spoof the nonce in the POST superglobal
    //$_POST['_wpnonce'] = wp_create_nonce( 'anything-here-if-needed' );

    // set this user as the active one
    global $current_user;
    $restore_user = $current_user;
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

    // call the AJAX which
    try {
      $this->_handleAjax( 'fv_wp_flowplayer_video_position_save' );
    } catch ( WPAjaxDieContinueException $e ) {
      $response = json_decode( $this->_last_response );
      $this->assertInternalType( 'object', $response );
      $this->assertObjectHasAttribute( 'success', $response );
      $this->assertTrue( $response->success );
    }

    // check if metadata was saved correctly
    $this->assertEquals(12, get_user_meta($this->userID, 'fv_wp_flowplayer_position_2', true ));
    $this->assertEquals(32, get_user_meta($this->userID, 'fv_wp_flowplayer_top_position_2', true ));

    // check that the playlist HTML is being generated correctly, with the last player position taken into consideration
    $post = get_post( $this->postID );
    $output = apply_filters( 'the_content', $post->post_content );

    $sample = <<< HTML
<div id="wpfp_245a181e8fd0e4cbe48d6e34cd579eda" class="freedomplayer flowplayer no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-horizontal" data-fv-embed="?fv_player_embed=1" style="max-width: 100%; " data-ratio="0.5625" data-save-position="yes">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
<div class='fvp-share-bar'><ul class="fvp-sharing">
    <li><a class="sharing-facebook" href="https://www.facebook.com/sharer/sharer.php?u=" target="_blank"></a></li>
    <li><a class="sharing-twitter" href="https://twitter.com/intent/tweet?text=Test+Blog+&url=" target="_blank"></a></li>
    <li><a class="sharing-email" href="mailto:?body=Check%20out%20the%20amazing%20video%20here%3A%20" target="_blank"></a></li></ul><div><label><a class="embed-code-toggle" href="#"><strong>Embed</strong></a></label></div><div class="embed-code"><label>Copy and paste this HTML code into your webpage to embed.</label><textarea></textarea></div></div>
</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal skin-slim" rel="wpfp_245a181e8fd0e4cbe48d6e34cd579eda" id="wpfp_245a181e8fd0e4cbe48d6e34cd579eda_playlist">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/1.mp4","type":"video\/mp4"}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/2.mp4","type":"video\/mp4","position":12,"top_position":32}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/3.mp4","type":"video\/mp4"}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
	</div>
HTML;

    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );

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
      $this->assertInternalType( 'object', $response );
      $this->assertObjectHasAttribute( 'success', $response );
      $this->assertTrue( $response->success );
    }

    // check if metadata was saved correctly
    $this->assertEquals(10, get_user_meta($this->userID, 'fv_wp_flowplayer_position_2', true ));
    // however the previous top position should still be stored as it was bigger
    $this->assertEquals(32, get_user_meta($this->userID, 'fv_wp_flowplayer_top_position_2', true ));

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
      $this->assertInternalType( 'object', $response );
      $this->assertObjectHasAttribute( 'success', $response );
      $this->assertTrue( $response->success );
    }

    // check if metadata was saved correctly
    $this->assertEquals(12, get_user_meta($this->userID, 'fv_wp_flowplayer_position_2', true ));
    // however the top position should no longer be stored
    $this->assertFalse(false, get_user_meta($this->userID, 'fv_wp_flowplayer_top_position_2', true ));
    // and it shoudl be remembered user saw the video
    $this->assertEquals( 1, get_user_meta($this->userID, 'fv_wp_flowplayer_saw_2', true ) );

    // check that the playlist HTML is being generated correctly, with the last player position taken into consideration and saw flag present
    $post = get_post( $this->postID );
    $output = apply_filters( 'the_content', $post->post_content );

    $sample = <<< HTML
<div id="wpfp_55754a50dd0a87c73e1de6be4f08b9a1" class="flowplayer no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-horizontal" data-fv-embed="?fv_player_embed=2" style="max-width: 100%; " data-ratio="0.5625" data-save-position="yes">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
<div class='fvp-share-bar'><ul class="fvp-sharing">
    <li><a class="sharing-facebook" href="https://www.facebook.com/sharer/sharer.php?u=" target="_blank"></a></li>
    <li><a class="sharing-twitter" href="https://twitter.com/intent/tweet?text=Test+Blog+&url=" target="_blank"></a></li>
    <li><a class="sharing-email" href="mailto:?body=Check%20out%20the%20amazing%20video%20here%3A%20" target="_blank"></a></li></ul><div><label><a class="embed-code-toggle" href="#"><strong>Embed</strong></a></label></div><div class="embed-code"><label>Copy and paste this HTML code into your webpage to embed.</label><textarea></textarea></div></div>
</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal skin-slim" rel="wpfp_55754a50dd0a87c73e1de6be4f08b9a1">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/1.mp4","type":"video\/mp4"}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/2.mp4","type":"video\/mp4","position":12,"saw":true}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div><span class="fvp-progress-wrap"><span class="fvp-progress" style="width: 100%"></span></span></div></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/3.mp4","type":"video\/mp4"}]}'><div class='fvp-playlist-thumb-img'><div class='fvp-playlist-thumb-img no-image'></div></div></a>
	</div>
HTML;
    
    $current_user;
  }

}