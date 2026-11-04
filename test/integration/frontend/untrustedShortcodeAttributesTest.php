<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Shortcodes in comments and in the post and profile videos come from
 * untrusted users, so only the src, splash, caption and title attributes may
 * be used there. Post and profile videos may also use id.
 *
 * Comments: the check is done in flowplayer_content_handle() on the
 * attributes parsed by WordPress.
 *
 * Post and profile videos: the shortcode text is rewritten with
 * FV_Player_Sanitize_User_Input()->sanitize_untrusted_shortcodes() when saving and loading, as
 * these can be shown with just do_shortcode().
 */
final class FV_Player_UntrustedShortcodeAttributesTest extends FV_Player_UnitTestCase {

  private $allowed = array( 'src', 'splash', 'caption', 'title' );

  private $video_payload = '[fvplayer id="123" src="https://cdn.site.com/video.mp4" caption="My caption" autoplay="true" ad="\x3cimg src=/x onerror=alert(1)//\x3e" popup="\x3cimg src=/x onerror=alert(1)//\x3e" error="\x3cimg src=/x onerror=alert(1)//\x3e" admin_warning="\x3cimg src=/x onerror=alert(1)//\x3e"]';

  /**
   * Shortcode arguments of each player, as seen by the fv_flowplayer_shortcode filter
   */
  private $captured = array();

  protected function setUp(): void {
    parent::setUp();

    global $fv_fp;
    $fv_fp->conf['parse_comments'] = 'true';

    $this->captured = array();
    add_filter( 'fv_flowplayer_shortcode', array( $this, 'capture_arguments' ) );
  }

  protected function tearDown(): void {
    remove_filter( 'fv_flowplayer_shortcode', array( $this, 'capture_arguments' ) );
    remove_filter( 'comment_text', 'do_shortcode' );

    parent::tearDown();
  }

  public function capture_arguments( $arguments ) {
    $this->captured[] = $arguments;

    // The player IDs used in this test do not exist, so do not try to load them
    unset( $arguments['id'] );

    return $arguments;
  }

  /**
   * Only the non-empty arguments, as wp_parse_args() fills in the defaults.
   */
  private function get_captured_arguments() {
    $this->assertCount( 1, $this->captured, 'Expected exactly one player.' );
    return array_filter( $this->captured[0], 'strlen' );
  }

  private function assertOnlyAllowedArguments( $allowed ) {
    $arguments = $this->get_captured_arguments();
    $this->assertSame( array(), array_diff( array_keys( $arguments ), $allowed ), 'Unexpected arguments: ' . var_export( $arguments, true ) );
    return $arguments;
  }

  private function sorted( $array ) {
    ksort( $array );
    return $array;
  }

  /**
   * @dataProvider comment_payloads
   */
  public function testCommentShortcodeAttributesAreRestricted( $comment, $parse_commas = false ) {
    global $fv_fp;
    if ( $parse_commas ) {
      $fv_fp->conf['parse_commas'] = 'true';
    }

    $output = apply_filters( 'comment_text', $comment );

    $this->assertOnlyAllowedArguments( $this->allowed );

    $this->assertStringNotContainsStringIgnoringCase( 'onerror', $output );
    $this->assertStringNotContainsString( 'fp-message', $output );

    $this->assertFalse( FV_Player_Sanitize_User_Input()->is_untrusted_content() );
  }

  public function comment_payloads() {
    $nbsp = "\u{00A0}";
    $zwsp = "\u{200B}";
    $img  = '\x3cimg src=/x onerror=alert(1)//\x3e';

    return array(
      'error'                          => array( '[flowplayer src="https://cdn.site.com/video.mp4" error="\x3cimg src=/fvpoc404 onerror=window.__fvpoc=Date.now();//\x3e"]' ),
      'admin_warning'                  => array( '[flowplayer src="https://cdn.site.com/video.mp4" admin_warning="\x3cimg src=/fvpoc404b onerror=window.__fvpocaw=Date.now();//\x3e"]' ),
      'ad'                             => array( '[fvplayer src="https://cdn.site.com/video.mp4" ad="' . $img . '"]' ),
      'popup'                          => array( '[fvplayer src="https://cdn.site.com/video.mp4" popup="' . $img . '"]' ),
      'end_popup_preview'              => array( '[fvplayer src="https://cdn.site.com/video.mp4" end_actions="popup" end_action_value="' . $img . '" end_popup_preview="true"]' ),
      'id is not allowed in comments'  => array( '[fvplayer id="1" src="https://cdn.site.com/video.mp4"]' ),
      'error, uppercase'               => array( '[flowplayer src="https://cdn.site.com/video.mp4" ERROR="' . $img . '"]' ),
      'error, single quotes'           => array( '[flowplayer src="https://cdn.site.com/video.mp4" error=\'' . $img . '\']' ),
      'error, unquoted'                => array( '[flowplayer src="https://cdn.site.com/video.mp4" error=\x3cimg&#32;src=/x&#32;onerror=alert(1)//\x3e]' ),
      'error, non-breaking space'      => array( '[flowplayer src="https://cdn.site.com/video.mp4"' . $nbsp . 'error="' . $img . '"]' ),
      'error, zero width space'        => array( '[flowplayer src="https://cdn.site.com/video.mp4"' . $zwsp . 'error="' . $img . '"]' ),
      'error, non-breaking space at =' => array( '[flowplayer src="https://cdn.site.com/video.mp4" error' . $nbsp . '="' . $img . '"]' ),
      'ad, non-breaking space at ='    => array( '[fvplayer src="https://cdn.site.com/video.mp4" ad' . $nbsp . '="' . $img . '"]' ),
      'error, first attribute'         => array( '[fvplayer error="' . $img . '" src="https://cdn.site.com/video.mp4"]' ),
      'popup, positional parse_commas' => array( '[flowplayer src="https://cdn.site.com/video.mp4" "zpopup=" \x3cimg&#32;s&#114;c=/x&#32;onerror=alert(1)\x3e]', true ),
    );
  }

  public function testCommentShortcodeKeepsAllowedAttributes() {
    apply_filters( 'comment_text', '[fvplayer src="https://cdn.site.com/video.mp4" splash="https://cdn.site.com/splash.jpg" caption="My caption" title="My title" autoplay="true"]' );

    $this->assertSame(
      array(
        'caption' => 'My caption',
        'splash'  => 'https://cdn.site.com/splash.jpg',
        'src'     => 'https://cdn.site.com/video.mp4',
        'title'   => 'My title',
      ),
      $this->sorted( $this->get_captured_arguments() )
    );
  }

  public function testCommentVideoLinkStillWorks() {
    $output = apply_filters( 'comment_text', 'Watch https://www.youtube.com/watch?v=1XiHhpGUmQg please' );

    $arguments = $this->get_captured_arguments();
    $this->assertSame( 'https://www.youtube.com/watch?v=1XiHhpGUmQg', $arguments['src'] );
    $this->assertStringContainsString( 'flowplayer', $output );
  }

  public function testPostContentIsNotRestricted() {
    // Comment first, to make sure the untrusted mode ends with it
    apply_filters( 'comment_text', '[fvplayer src="https://cdn.site.com/video.mp4" autoplay="true"]' );
    $this->captured = array();

    do_shortcode( '[fvplayer src="https://cdn.site.com/video.mp4" autoplay="true" admin_warning="Note"]' );

    $arguments = $this->get_captured_arguments();
    $this->assertSame( 'true', $arguments['autoplay'] );
    $this->assertSame( 'Note', $arguments['admin_warning'] );
  }

  public function testProfileVideoAttributesAreRestricted() {
    $user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
    add_user_meta( $user_id, '_fv_player_user_video', $this->video_payload );

    $videos = new FV_Player_Custom_Videos( array( 'id' => $user_id, 'type' => 'user' ) );
    $videos->get_html();

    $arguments = $this->assertOnlyAllowedArguments( array_merge( $this->allowed, array( 'id' ) ) );
    $this->assertSame( '123', $arguments['id'] );
    $this->assertSame( 'My caption', $arguments['caption'] );

    $this->assertFalse( FV_Player_Sanitize_User_Input()->is_untrusted_content() );
  }

  public function testPostVideoAttributesAreRestricted() {
    $post_id = $this->factory->post->create();
    add_post_meta( $post_id, 'fv_player_test_videos', $this->video_payload );

    $videos = new FV_Player_Custom_Videos( array( 'id' => $post_id, 'meta' => 'fv_player_test_videos', 'type' => 'post' ) );
    $videos->get_html();

    $arguments = $this->assertOnlyAllowedArguments( array_merge( $this->allowed, array( 'id' ) ) );
    $this->assertSame( '123', $arguments['id'] );

    $this->assertFalse( FV_Player_Sanitize_User_Input()->is_untrusted_content() );
  }

  public function testProfileVideoEditorPreviewAttributesAreRestricted() {
    $user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

    $videos = new FV_Player_Custom_Videos( array( 'id' => $user_id, 'type' => 'user' ) );
    // Stored videos do not have autoplay, the preview adds autoplay="false"
    $videos->get_html_part( str_replace( ' autoplay="true"', '', $this->video_payload ), true );

    $arguments = $this->assertOnlyAllowedArguments( array_merge( $this->allowed, array( 'id', 'autoplay' ) ) );
    $this->assertSame( 'false', $arguments['autoplay'] );

    $this->assertFalse( FV_Player_Sanitize_User_Input()->is_untrusted_content() );
  }

  public function testNestedUntrustedContent() {
    $this->assertFalse( FV_Player_Sanitize_User_Input()->is_untrusted_content() );

    // Like comment_text used in a shortcode in post content which is in a comment
    FV_Player_Sanitize_User_Input()->untrusted_content_start( 'text' );
    FV_Player_Sanitize_User_Input()->untrusted_content_start( 'text' );
    FV_Player_Sanitize_User_Input()->untrusted_content_end( 'text' );
    $this->assertTrue( FV_Player_Sanitize_User_Input()->is_untrusted_content() );

    FV_Player_Sanitize_User_Input()->untrusted_content_end( 'text' );
    $this->assertFalse( FV_Player_Sanitize_User_Input()->is_untrusted_content() );

    // Ending more than started is harmless
    FV_Player_Sanitize_User_Input()->untrusted_content_end( '' );
    FV_Player_Sanitize_User_Input()->untrusted_content_start( '' );
    $this->assertTrue( FV_Player_Sanitize_User_Input()->is_untrusted_content() );
    FV_Player_Sanitize_User_Input()->untrusted_content_end( '' );
  }

  /**
   * Developers show the post videos with just do_shortcode(), see
   * https://foliovision.com/player/developers/video-custom-fields
   */
  public function testPostVideosDeveloperCode() {
    $post_id = $this->factory->post->create();
    add_post_meta( $post_id, 'film_videos', $this->video_payload );

    $objVideos = new FV_Player_Custom_Videos( array( 'id' => $post_id, 'meta' => 'film_videos', 'type' => 'post' ) );
    if ( $objVideos->have_videos() ) {
      foreach ( $objVideos->get_videos() as $video ) {
        $output = do_shortcode( $video );
      }
    }

    $arguments = $this->assertOnlyAllowedArguments( array_merge( $this->allowed, array( 'id' ) ) );
    $this->assertSame( '123', $arguments['id'] );
    $this->assertSame( 'My caption', $arguments['caption'] );
    $this->assertStringNotContainsStringIgnoringCase( 'onerror', $output );
  }

  /**
   * @dataProvider sanitize_payloads
   */
  public function testSanitizeUntrustedShortcodes( $input, $expected ) {
    $this->assertSame( $expected, FV_Player_Sanitize_User_Input()->sanitize_untrusted_shortcodes( $input, array( 'id' ) ) );
  }

  public function sanitize_payloads() {
    $nbsp = "\u{00A0}";
    $img  = '\x3cimg src=/x onerror=alert(1)//\x3e';

    return array(
      'clean shortcode is kept'        => array( '[fvplayer id="123"]', '[fvplayer id="123"]' ),
      'only allowed attributes'        => array( '[fvplayer id="1" src="https://cdn.site.com/video.mp4" autoplay="true" ad="' . $img . '" popup="' . $img . '" error="' . $img . '"]', '[fvplayer id="1" src="https://cdn.site.com/video.mp4"]' ),
      'non-breaking space'             => array( '[fvplayer id="1"' . $nbsp . 'ad="' . $img . '"]', '[fvplayer id="1"]' ),
      'non-breaking space at ='        => array( '[fvplayer id="1" ad' . $nbsp . '="' . $img . '"]', '[fvplayer id="1"]' ),
      'uppercase, single quotes'       => array( '[fvplayer id="1" AD=\'' . $img . '\']', '[fvplayer id="1"]' ),
      'positional'                     => array( '[flowplayer src="https://cdn.site.com/video.mp4" "zpopup=" \x3cimg&#32;s&#114;c=/x&#32;onerror=alert(1)\x3e]', '[flowplayer src="https://cdn.site.com/video.mp4"]' ),
      'content is removed'             => array( '[fvplayer id="1"]text[/fvplayer]', '[fvplayer id="1"]' ),
      'escaped shortcode is kept'      => array( '[[fvplayer ad="x"]]', '[[fvplayer ad="x"]]' ),
      'escaped and same shortcode'     => array( '[[fvplayer ad="x"]] [fvplayer ad="x"]', '[[fvplayer ad="x"]] [fvplayer]' ),
      'multiple shortcodes'            => array( '[fvplayer id="1" ad="x"][fvplayer id="2" popup="y"]', '[fvplayer id="1"][fvplayer id="2"]' ),
      'other text and shortcodes kept' => array( 'Hello [gallery ids="1,2"] [fvplayer id="1" ad="x"] bye', 'Hello [gallery ids="1,2"] [fvplayer id="1"] bye' ),
      'HTML is encoded'                => array( '[fvplayer caption="' . $img . '"]', '[fvplayer caption="\x3cimg src=/x onerror=alert(1)//\x3e"]' ),
      'no shortcode'                   => array( 'Just text', 'Just text' ),
    );
  }

  /**
   * Values which need encoding must give back the same attributes.
   */
  public function testSanitizeUntrustedShortcodesRoundTrip() {
    $atts = array(
      'src'     => 'https://cdn.site.com/video.mp4?a=1&b=2',
      'caption' => 'He said "hi" [really] C:\new\x22 <b>bold</b> it\'s',
      'title'   => 'back\\slash\\\\',
    );

    $shortcode = '[fvplayer';
    foreach ( $atts as $name => $value ) {
      $shortcode .= ' ' . $name . '="' . FV_Player_Sanitize_User_Input()->shortcode_attribute_encode( $value ) . '"';
    }
    $shortcode .= ']';

    $sanitized = FV_Player_Sanitize_User_Input()->sanitize_untrusted_shortcodes( $shortcode );
    $this->assertSame( $shortcode, $sanitized );

    $this->assertSame( 1, preg_match( '/' . get_shortcode_regex( array( 'fvplayer' ) ) . '/', $sanitized, $m ) );
    $this->assertSame( $atts, shortcode_parse_atts( $m[3] ) );

    // And again, nothing changes
    $this->assertSame( $sanitized, FV_Player_Sanitize_User_Input()->sanitize_untrusted_shortcodes( $sanitized ) );
  }

  public function testProfileVideoSave() {
    $user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
    wp_set_current_user( $user_id );

    $meta = '_fv_player_user_video';
    $_POST = wp_slash( array(
      'fv_player_videos'                    => array( $meta => array( $user_id => array( $this->video_payload, '[fvplayer id="7" caption="Quote \x22 and back\\\\slash"]' ) ) ),
      'fv-player-custom-videos-entity-type' => array( $meta => array( $user_id => 'user' ) ),
      'fv-player-custom-videos-entity-id'   => array( $meta => array( $user_id => $user_id ) ),
      'fv-player-custom-videos-' . $meta . '-' . $user_id => wp_create_nonce( 'fv-player-custom-videos-' . $meta . '-' . $user_id ),
    ) );

    FV_Player_Custom_Videos_Master()->save();
    $_POST = array();

    $this->assertSame(
      array(
        '[fvplayer id="123" src="https://cdn.site.com/video.mp4" caption="My caption"]',
        '[fvplayer id="7" caption="Quote \x22 and back\\\\slash"]',
      ),
      get_user_meta( $user_id, $meta )
    );
  }

  public function testPostVideoSave() {
    $user_id = $this->factory->user->create( array( 'role' => 'author' ) );
    wp_set_current_user( $user_id );
    $post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

    $meta = 'film_videos';
    $_POST = wp_slash( array(
      'fv_player_videos'                    => array( $meta => array( $post_id => array( $this->video_payload ) ) ),
      'fv-player-custom-videos-entity-type' => array( $meta => array( $post_id => 'post' ) ),
      'fv-player-custom-videos-entity-id'   => array( $meta => array( $post_id => $post_id ) ),
      'fv-player-custom-videos-' . $meta . '-' . $user_id => wp_create_nonce( 'fv-player-custom-videos-' . $meta . '-' . $user_id ),
    ) );

    FV_Player_Custom_Videos_Master()->save_post( $post_id );
    $_POST = array();

    $this->assertSame(
      array( '[fvplayer id="123" src="https://cdn.site.com/video.mp4" caption="My caption"]' ),
      get_post_meta( $post_id, $meta )
    );
  }

}
