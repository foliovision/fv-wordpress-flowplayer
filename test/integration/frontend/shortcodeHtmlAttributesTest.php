<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * The error="" and admin_warning="" shortcode attributes are output as HTML.
 *
 * WordPress decodes shortcode attribute values with stripcslashes(), so
 * "\x3c" and "\x3e" become "<" and ">" only after kses has approved the
 * comment or post. The player must sanitize these values on output.
 *
 * For shortcodes in comments see untrustedShortcodeAttributesTest.php
 */
final class FV_Player_ShortcodeHtmlAttributesTest extends FV_Player_UnitTestCase {

  private $error_payload = '[flowplayer src="https://cdn.site.com/video.mp4" error="\x3cimg src=/fvpoc404 onerror=window.__fvpoc=Date.now();//\x3e"]';

  private $admin_warning_payload = '[flowplayer src="https://cdn.site.com/video.mp4" admin_warning="\x3cimg src=/fvpoc404b onerror=window.__fvpocaw=Date.now();//\x3e"]';

  private function get_error_message_html( $output ) {
    $this->assertSame( 1, preg_match( '~<div class="fp-message fp-shown">(.*?)</div>~s', $output, $match ), 'Error message not found in: ' . $output );
    return $match[1];
  }

  public function testErrorAttributeIsSanitized() {
    $output = do_shortcode( $this->error_payload );

    $message = $this->get_error_message_html( $output );
    $this->assertStringNotContainsStringIgnoringCase( 'onerror', $message );

    // The only other place where the error is used is the data-error attribute which is escaped
    $this->assertStringNotContainsString( '<img src=/fvpoc404', $output );
    $this->assertStringContainsString( 'data-error="&lt;img src=/fvpoc404 onerror=', $output );
  }

  public function testErrorAttributeKeepsSafeHtml() {
    $output = do_shortcode( '[fvplayer src="https://cdn.site.com/video.mp4" error="Please \x3ca href=\x27https://example.com/login\x27\x3elog in\x3c/a\x3e to watch."]' );

    $message = $this->get_error_message_html( $output );
    $this->assertStringContainsString( 'https://example.com/login', $message );
    $this->assertStringContainsString( '>log in</a> to watch.', $message );
  }

  public function testAdminWarningAttributeIsSanitized() {
    $output = do_shortcode( $this->admin_warning_payload );

    $this->assertStringNotContainsStringIgnoringCase( 'onerror', $output );
  }

  public function testAdminWarningAttributeKeepsSafeHtml() {
    // Like the OptimizePress shortcode conversion in controller/shortcodes.php
    $output = do_shortcode( '[fvplayer src="https://cdn.site.com/video.mp4" admin_warning="Admin note: Please visit the <a href=\'https://example.com/settings\'>settings</a>."]' );

    $this->assertStringContainsString( 'https://example.com/settings', $output );
    $this->assertStringContainsString( '>settings</a>.', $output );
  }

}
