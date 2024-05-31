<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_EndActionsTest extends FV_Player_UnitTestCase {

  private $popup_html = '<a href="https://foliovision.com/2018/07/panamax"><img src="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg" class="alignleft post-image entry-image lazyloaded " alt="PanamaX" itemprop="image" sizes="(max-width: 400px) 100vw, 400px" srcset="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg 400w, https://cdn.foliovision.com/images/2018/07/PanamaX-5.jpg 1128w" width="400" height="239"></a>';

  private $post_id_testEndActions;

  protected function setUp(): void {
    parent::setUp();

    // create a post with playlist shortcode
    $this->post_id_testEndActions= $this->factory->post->create( array(
      'post_title' => 'End Action Test',
      'post_content' => '[fvplayer src="https://cdn.site.com/video.mp4"]'
    ) );

    // if we don't load something with a [fvplayer] shortcode in it it won't know to load CSS in header!
    global $post;
    $post = get_post( $this->post_id_testEndActions );
    $post->ID = 1234;

    // we remove header stuff which we don't want to test
    remove_action('wp_head', 'wp_generator');
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    add_filter( 'wp_resource_hints', '__return_empty_array' );
    wp_deregister_script( 'wp-embed' );

    // the test data
    update_option('fv_player_popups', array( 1 => array (
        'name' => '',
        'html' => $this->popup_html,
        'css' => '',
        'disabled' => '0',
      ) ) );
  }

  public function testEndActionsEmailCollection() {

    // triggering the default email list creation
    global $FV_Player_Email_Subscription;
    $FV_Player_Email_Subscription->init_options();

    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" popup="email-1"]' );

    // Exactly one popup should be stored
    $this->assertTrue(
      1 === preg_match( '~data-popup="(.*?)"~', $output, $data_popup )
    );

    // Email form HTML must be found in the markup
    $data_popup = json_decode( html_entity_decode( $data_popup[1] ) );

    $this->assertTrue(
      stripos( $data_popup->html, '<form class="mailchimp-form ' ) !== false &&
      stripos( $data_popup->html, '<input type="email"' ) !== false &&
      stripos( $data_popup->html, 'name="email"' ) !== false &&
      stripos( $data_popup->html, '<input type="submit"' ) !== false
    );
  }

  public function testEndActionsLoop() {

    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" loop="true"]' );

    // Exactly one video
    $this->assertTrue( substr_count( $output, "data-item" ) == 1 );

    // Loop attribute
    $this->assertTrue( substr_count( $output, 'data-loop="1"' ) == 1 );

    // No popup
    $this->assertTrue( substr_count( $output, 'data-popup' ) == 0 );
  }

  public function testEndActionsPopupNumber() {

    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" popup="1" share="no" embed="false"]' );

    // Exactly one popup should be stored
    $this->assertTrue(
      1 === preg_match( '~data-popup="(.*?)"~', $output, $data_popup )
    );

    // Popup HTML must be found in the markup
    $data_popup = json_decode( html_entity_decode( $data_popup[1] ) );

    $fv_player_popups = get_option( 'fv_player_popups' );
    $html = $fv_player_popups[1]['html'];

    $this->assertTrue(
      stripos( $data_popup->html, $html ) !== false
    );
  }

  public function testEndActionsPopupHTML() {

    $html = '<h1>Title</h1><a href="https://foliovision.com/2018/07/panamax"><img src="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg" class="alignleft post-image entry-image lazyloaded " alt="PanamaX" itemprop="image" sizes="(max-width: 400px) 100vw, 400px" srcset="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg 400w, https://cdn.foliovision.com/images/2018/07/PanamaX-5.jpg 1128w" width="400" height="239"></a>';

    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" popup="'.addslashes( $html ).'"]' );

    // Exactly one popup should be stored
    $this->assertTrue(
      1 === preg_match( '~data-popup="(.*?)"~', $output, $data_popup )
    );

    // Popup HTML must be found in the markup
    $data_popup = json_decode( html_entity_decode( $data_popup[1] ) );

    $this->assertTrue(
      stripos( $data_popup->html, $html ) !== false
    );
  }

  public function testEndActionsRedirect() {

    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" redirect="https://foliovision.com"]' );

    // Exactly one video
    $this->assertTrue( substr_count( $output, "data-item" ) == 1 );

    // Redirection attribute
    $this->assertTrue( substr_count( $output, 'data-fv_redirect="https://foliovision.com"' ) == 1 );

    // No popup
    $this->assertTrue( substr_count( $output, 'data-popup' ) == 0 );
  }

  public function testEndActionsSplashEnd() {

    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" splash="https://cdn.site.com/video.jpg" share="no" embed="false" splashend="show"]' );

    // Exactly one video
    $this->assertTrue( substr_count( $output, "data-item" ) == 1 );

    // Splash to show at the end should be present
    $this->assertTrue( substr_count( $output, ' class="wpfp_custom_background" style="background: url(\'https://cdn.site.com/video.jpg\')' ) == 1 );
  }

  protected function tearDown(): void {
    delete_option('fv_player_popups');
  }

}
