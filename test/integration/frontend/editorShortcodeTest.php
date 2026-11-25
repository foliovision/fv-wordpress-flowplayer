<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');
require_once( dirname(__FILE__).'/../../../controller/editor.php');

/**
 * The [fvplayer_editor] shortcode can be used by any user who can write posts,
 * like a Contributor. KSES does not see the HTML in the shortcode attributes,
 * as \x3c is turned into < by stripcslashes() in shortcode_parse_atts().
 *
 * The field attribute goes into fv_player_editor_conf.field_selector which is
 * used as a jQuery selector, and jQuery( '<img ...>' ) creates the HTML.
 */
final class FV_Player_EditorShortcodeTest extends FV_Player_UnitTestCase {

  protected function setUp(): void {
    parent::setUp();

    // Start with fresh scripts to not see the fv_player_editor_conf of the other tests
    $GLOBALS['wp_scripts'] = null;
  }

  protected function tearDown(): void {
    $GLOBALS['wp_scripts'] = null;

    parent::tearDown();
  }

  /**
   * @dataProvider sanitize_args_payloads
   */
  public function testSanitizeShortcodeArgs( $input, $expected ) {
    $this->assertSame( $expected, fv_player_editor_sanitize_shortcode_args( $input ) );
  }

  public function sanitize_args_payloads() {
    return array(
      'not an array, shortcode without attributes' => array( '', array() ),

      'valid field selectors are kept' => array(
        array( 'field' => '#my-form > input[name="video"]' ),
        array( 'field' => '#my-form > input[name="video"]' ),
      ),

      'field with HTML is removed' => array(
        array( 'field' => '<img src=x onerror=alert(document.domain)>' ),
        array(),
      ),

      'field with HTML after whitespace is removed' => array(
        array( 'field' => "  \n<img src=x onerror=alert(1)>" ),
        array(),
      ),

      'field with HTML later in the string is removed' => array(
        array( 'field' => '#x, <img src=x onerror=alert(1)>' ),
        array(),
      ),

      'field must be a string' => array(
        array( 'field' => array( '#x' ) ),
        array(),
      ),

      'frontend becomes boolean' => array(
        array( 'frontend' => 'yes' ),
        array( 'frontend' => true ),
      ),

      'hide is a list of names' => array(
        array( 'hide' => 'configure-video, splash,<img src=x onerror=alert(1)>,,' ),
        array( 'hide' => array( 'configure-video', 'splash', 'imgsrcxonerroralert1' ) ),
      ),

      'library is a list of names' => array(
        array( 'library' => 'fv_player_bunny_stream,.*(a+)+$' ),
        array( 'library' => 'fv_player_bunny_stream,a' ),
      ),

      'tabs only accepts none' => array(
        array( 'tabs' => 'none' ),
        array( 'tabs' => 'none' ),
      ),

      'tabs with other value is removed' => array(
        array( 'tabs' => '<img src=x onerror=alert(1)>' ),
        array(),
      ),

      'unknown attributes are removed' => array(
        array( 'on_insert' => 'alert(1)', 'admin_url' => 'javascript:alert(1)' ),
        array(),
      ),
    );
  }

  /**
   * The Contributor payload must not get into the fv_player_editor_conf script data.
   */
  public function testEditorShortcodeFieldPayload() {
    $user_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
    wp_set_current_user( $user_id );

    do_shortcode( '[fvplayer_editor field="\x3cimg src=x onerror=alert(document.domain)\x3e" hide="splash,\x3cimg\x3e" tabs="\x3cb\x3e"]' );

    $data = wp_scripts()->get_data( 'fvwpflowplayer-shortcode-editor', 'data' );

    $this->assertIsString( $data );
    $this->assertStringContainsString( 'fv_player_editor_conf', $data );
    $this->assertStringNotContainsString( 'field_selector', $data );
    $this->assertStringNotContainsStringIgnoringCase( 'onerror', $data );
    $this->assertStringNotContainsString( '<', $data );
    $this->assertStringNotContainsString( '<img', $data );
    $this->assertStringContainsString( '"hide":["splash","img"]', $data );
    $this->assertStringNotContainsString( '"tabs"', $data );
  }

  /**
   * [fvplayer_editor] without any attributes gives an empty string to the shortcode function.
   */
  public function testEditorShortcodeWithoutAttributes() {
    $user_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
    wp_set_current_user( $user_id );

    $output = do_shortcode( '[fvplayer_editor]' );

    $this->assertStringContainsString( 'fv_player_editor.editor_open()', $output );

    $data = wp_scripts()->get_data( 'fvwpflowplayer-shortcode-editor', 'data' );
    $this->assertStringContainsString( '"frontend":"1"', $data );
  }

}
