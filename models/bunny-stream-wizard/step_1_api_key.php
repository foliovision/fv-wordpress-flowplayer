<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Bunny_Stream_Wizard_API_Key extends FV_Player_Wizard_Step_Base_Class {

  protected
    $key = 'bunnycdn_api',
    $name = 'Bunny.net API Key',

    $buttons = array(
    'next' => array(
      'value' => 'Check API key',
    )
  );

  public function __construct() {
    require_once( dirname(__FILE__).'/../class.fv-player-bunny_stream-api.php' );
  }

  function display() {
    ?>
      <tr>
          <td colspan="2">
              <h2>Bunny.net API key</h2>
              <p>We need your Bunny.net API key to connect to your Stream Library.<br />Please open <a href="https://panel.bunny.net/dashboard/account" target="_blank">panel.bunny.net/dashboard/account</a> to get your API key.</p>
              <p>(Note: we do not store this token, it's only used during this Wizard session).</p>
              <p><img src="<?php echo plugins_url( 'images/bunnycdn-api.png', __FILE__ ); ?>" srcset="<?php echo plugins_url( 'images/bunnycdn-api.png', __FILE__ ); ?> 1x, <?php echo plugins_url( 'images/bunnycdn-api-2x.png', __FILE__ ); ?> 2x" /></p>
          </td>
      </tr>
    <?php
    parent::display();
  }

  function process() {

    // We are not processing form data without nonce verification.
    // The nonce is verified in FV_Player_Wizard_Base_Class::ajax() which calls FV_Player_Wizard_Step_Base_Class::process()
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $api_key = trim( sanitize_text_field( $_POST[ $this->key ] ) );

    $api = new FV_Player_Bunny_Stream_API( $api_key );

    // test the API key by listing Stream Libraries
    $result = $api->api_call( 'https://api.bunny.net/videolibrary?page=1&perPage=1' );
    if ( is_wp_error( $result ) ) {
      return array('error' => $result->get_error_message() );
    }

    $bunny_stream_library_picker = new FV_Player_Bunny_Stream_Wizard_Stream_Libs();

    ob_start();
    $bunny_stream_library_picker->display();
    $bunny_stream_library_picker->buttons();

    return array(
      'next_step' => ob_get_clean(),
      'ok' => true
    );
  }

  function should_show() {
    return !FV_Player_Bunny_Stream()->is_configured();
  }

}

$this->register_step('FV_Player_Bunny_Stream_Wizard_API_Key');