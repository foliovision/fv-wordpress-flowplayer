<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Records if it was ever unserialized, a stand-in for a POP gadget class.
 */
class FV_Player_Object_Injection_Probe {
  public static $woken = 0;

  public $test;

  public function __wakeup() {
    self::$woken++;
  }

  public function __unserialize( array $data ) {
    self::$woken++;
  }
}

/**
 * The shortcode conversion CSV export and the email list export must never
 * unserialize objects from the database.
 *
 * The conversion export read the _fv_player_<slug>_failed post meta with raw
 * SQL and passed it to unserialize(). A C: or O: payload stored there was
 * turned into an object (PHP Object Injection).
 */
final class FV_Player_ConversionExportObjectInjectionTestCase extends FV_Player_UnitTestCase {

  private $conversion;

  private $post_id;

  protected function setUp(): void {
    parent::setUp();

    require_once( dirname(__FILE__).'/../../../models/conversion/conversion-base.class.php' );
    require_once( dirname(__FILE__).'/../../../models/conversion/shortcode2DB.class.php' );
    require_once( dirname(__FILE__).'/../../../models/email-subscription.php' );

    global $FV_Player_Shortcode2Database_Conversion;
    $this->conversion = $FV_Player_Shortcode2Database_Conversion;

    $this->post_id = self::factory()->post->create( array(
      'post_title'   => 'Conversion test post',
      'post_content' => '[fvplayer src="https://example.com/video.mp4" unsupported="1"]',
    ) );

    FV_Player_Object_Injection_Probe::$woken = 0;
  }

  /**
   * Store the meta value as it is, without maybe_serialize(), like a raw database write would.
   */
  private function insert_raw_meta( $post_id, $meta_value ) {
    global $wpdb;
    $wpdb->insert( $wpdb->postmeta, array(
      'post_id'    => $post_id,
      'meta_key'   => '_fv_player_shortcode2db_failed',
      'meta_value' => $meta_value,
    ) );
  }

  private function get_error_row( $args = array() ) {
    return array_merge( array(
      'ID'         => $this->post_id,
      'post_title' => 'Conversion test post',
      'post_link'  => 'https://example.com/?p=1&amp;preview=true',
      'post_edit'  => 'https://example.com/wp-admin/post.php?post=1&amp;action=edit',
      'shortcode'  => '[fvplayer src="https://example.com/video.mp4" unsupported="1"]',
      'message'    => 'Unsupported argument(s) unsupported',
    ), $args );
  }

  public function test_c_format_payload_is_not_unserialized() {
    $this->insert_raw_meta( $this->post_id, 'C:11:"ArrayObject":92:{x:i:0;a:1:{i:0;O:32:"FV_Player_Object_Injection_Probe":1:{s:4:"test";s:5:"PWNED";}};m:a:0:{}}' );

    // Make sure the payload is valid, it would create the object with plain unserialize()
    global $wpdb;
    $raw = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_fv_player_shortcode2db_failed'" );
    $this->assertInstanceOf( 'ArrayObject', unserialize( $raw ) );
    $this->assertSame( 1, FV_Player_Object_Injection_Probe::$woken );
    FV_Player_Object_Injection_Probe::$woken = 0;

    $rows = $this->conversion->get_csv_export_rows();

    $this->assertSame( 0, FV_Player_Object_Injection_Probe::$woken );
    $this->assertSame( array(), $rows );
  }

  public function test_o_format_payload_is_not_unserialized() {
    $this->insert_raw_meta( $this->post_id, 'a:1:{i:0;O:32:"FV_Player_Object_Injection_Probe":1:{s:4:"test";s:5:"PWNED";}}' );

    $rows = $this->conversion->get_csv_export_rows();

    $this->assertSame( 0, FV_Player_Object_Injection_Probe::$woken );
    $this->assertSame( array(), $rows );
  }

  public function test_valid_errors_are_exported() {
    $other_post_id = self::factory()->post->create( array( 'post_title' => 'Payload post' ) );
    $this->insert_raw_meta( $other_post_id, 'C:11:"ArrayObject":92:{x:i:0;a:1:{i:0;O:32:"FV_Player_Object_Injection_Probe":1:{s:4:"test";s:5:"PWNED";}};m:a:0:{}}' );

    // Stored the same way as FV_Player_Conversion_Base::ajax_convert() does
    update_post_meta( $this->post_id, '_fv_player_shortcode2db_failed', array(
      $this->get_error_row(),
      $this->get_error_row( array( 'message' => 'Error saving FV Player instance' ) ),
    ) );

    $rows = $this->conversion->get_csv_export_rows();

    $this->assertSame( 0, FV_Player_Object_Injection_Probe::$woken );
    $this->assertSame( array(
      array(
        (string) $this->post_id,
        'Conversion test post',
        'https://example.com/?p=1&preview=true',
        'https://example.com/wp-admin/post.php?post=1&action=edit',
        '[fvplayer src="https://example.com/video.mp4" unsupported="1"]',
        'Unsupported argument(s) unsupported',
      ),
      array(
        (string) $this->post_id,
        'Conversion test post',
        'https://example.com/?p=1&preview=true',
        'https://example.com/wp-admin/post.php?post=1&action=edit',
        '[fvplayer src="https://example.com/video.mp4" unsupported="1"]',
        'Error saving FV Player instance',
      ),
    ), $rows );
  }

  public function test_only_expected_columns_are_exported() {
    $row = $this->get_error_row( array( 'message' => array( 'nested' ), 'extra' => 'should not be exported' ) );
    unset( $row['shortcode'] );

    update_post_meta( $this->post_id, '_fv_player_shortcode2db_failed', array( $row, 'not a row' ) );

    $rows = $this->conversion->get_csv_export_rows();

    $this->assertCount( 1, $rows );
    $this->assertCount( 6, $rows[0] );
    $this->assertSame( '', $rows[0][4], 'Missing shortcode should be empty' );
    $this->assertSame( '', $rows[0][5], 'Non-scalar message should be empty' );
    $this->assertNotContains( 'should not be exported', $rows[0] );
  }

  public function test_formula_injection_is_escaped() {
    update_post_meta( $this->post_id, '_fv_player_shortcode2db_failed', array(
      $this->get_error_row( array(
        'post_title' => '=HYPERLINK("https://attacker.example.com","Click")',
        'shortcode'  => ' @SUM(1+1)',
        'message'    => '-2+3',
      ) ),
    ) );

    $rows = $this->conversion->get_csv_export_rows();

    $this->assertSame( '\'=HYPERLINK("https://attacker.example.com","Click")', $rows[0][1] );
    $this->assertSame( '\' @SUM(1+1)', $rows[0][4] );
    $this->assertSame( '\'-2+3', $rows[0][5] );
    $this->assertSame( (string) $this->post_id, $rows[0][0] );
  }

  public function test_email_error_title() {
    global $FV_Player_Email_Subscription;

    $this->assertSame( 'Member Exists', $FV_Player_Email_Subscription->get_error_title( serialize( array( 'title' => 'Member Exists', 'status' => 400 ) ) ) );

    $this->assertSame( '', $FV_Player_Email_Subscription->get_error_title( 'O:32:"FV_Player_Object_Injection_Probe":1:{s:4:"test";s:5:"PWNED";}' ) );
    $this->assertSame( '', $FV_Player_Email_Subscription->get_error_title( 'a:1:{s:5:"title";O:32:"FV_Player_Object_Injection_Probe":1:{s:4:"test";s:5:"PWNED";}}' ) );
    $this->assertSame( '', $FV_Player_Email_Subscription->get_error_title( 'C:11:"ArrayObject":92:{x:i:0;a:1:{i:0;O:32:"FV_Player_Object_Injection_Probe":1:{s:4:"test";s:5:"PWNED";}};m:a:0:{}}' ) );
    $this->assertSame( '', $FV_Player_Email_Subscription->get_error_title( 'not serialized' ) );

    $this->assertSame( 0, FV_Player_Object_Injection_Probe::$woken );
  }

}
