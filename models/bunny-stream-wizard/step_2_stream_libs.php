<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Bunny_Stream_Wizard_Stream_Libs extends FV_Player_Wizard_Step_Base_Class {

  protected
    $buttons_across_2_columns = true,

    $buttons = array(
    'next' => array(
      'value' => 'Save and Continue',
    )
  );

  public function __construct() {
    require_once( dirname(__FILE__).'/../class.fv-player-bunny_stream-api.php' );
  }

  function display() {
    // We are not processing form data without nonce verification.
    // The nonce is verified in FV_Player_Wizard_Base_Class::ajax() which calls FV_Player_Bunny_Stream_Wizard_API_Key::process() where this method is called.
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $bunnycdn_api = ! empty( $_POST['bunnycdn_api'] ) ? sanitize_text_field( $_POST['bunnycdn_api'] ) : false;

    ?>
      <tr>
          <td colspan="2">
              <h2>Bunny Stream Library</h2>
              <p>Pick an existing library to upload videos to or create a new one:</p>
          </td>
      </tr>
      <tr>
      <td colspan="2">
        <input type="hidden" name="bunnycdn_api" value="<?php echo esc_attr( $bunnycdn_api ); ?>" />
        <select name="fv_bunny_stream_wizard_lib" id="fv_bunny_stream_wizard_lib">
          <option value="-1" disabled selected hidden>Pick a Library...</option>
          <option value="create-new-bunny-stream-library">- Create a new Stream Library -</option>
        <?php
        // retrieve a list of existing Stream Libraries
        if ( !empty( $bunnycdn_api ) ) {
          $api = new FV_Player_Bunny_Stream_API( $bunnycdn_api );
          $libs = $api->api_call( 'https://api.bunny.net/videolibrary?page=1&perPage=1000' );
          if ( $libs->Items && count( $libs->Items ) ) {
            foreach ( $libs->Items as $lib ) {
              ?>
                <option value="<?php echo esc_attr( $lib->Id ); ?>"><?php echo esc_html( $lib->Name ); ?></option>
              <?php
            }
          }
        }
        ?>
        </select>
      <script>
        (function ($) {
          $( '#fv_bunny_stream_wizard_lib' ).on( 'change', function() {
            var self = this;

            // show the library rows, hide the select dropdown
            if ( this.value == 'create-new-bunny-stream-library' ) {
              $( '.bunny_stream_wizard_hidden' ).css( 'display', 'block' );
              $( self ).hide();

              // add a "Cancel" button next to a save button, so we can go back to picking an existing library
              var $cancelBtn = $('#bunny_stream_wizard_cancel');
              if ( !$cancelBtn.length ) {
                $( 'input[data-fv-player-wizard-next]' ).after( $( '<a id="bunny_stream_wizard_cancel" href="#" style="margin-left: 10px">Cancel</a>' ).on( 'click', function() {
                  $( '.bunny_stream_wizard_hidden' ).css( 'display', 'none' );
                  $( self ).show()[0].selectedIndex = 0;
                  $( this ).hide();

                  return false;
                }) );
              } else {
                $cancelBtn.show();
              }
            }
          } );
        }(jQuery));
      </script>
    </td>
    </tr>

    <tr class="bunny_stream_wizard_hidden hidden">
      <td><label for="bunny_stream_wizard[name]">New Stream Library Name:</label></td>
      <td>
        <input class="regular-text code" id="bunny_stream_wizard[name]" name="bunny_stream_wizard[name]" data-optional="1" type="text" value="" />
      </td>
    </tr>
    <?php
  }

  function process() {
    global $fv_fp;

    // We are not processing form data without nonce verification.
    // The nonce is verified in FV_Player_Wizard_Base_Class::ajax() which calls FV_Player_Wizard_Step_Base_Class::process()
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $bunnycdn_api = ! empty( $_POST['bunnycdn_api'] ) ? sanitize_text_field( $_POST['bunnycdn_api'] ) : false;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $lib = ! empty( $_POST['fv_bunny_stream_wizard_lib'] ) ? sanitize_text_field( $_POST['fv_bunny_stream_wizard_lib'] ) : false;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $name = ! empty( $_POST['bunny_stream_wizard']['name'] ) ? sanitize_text_field( $_POST['bunny_stream_wizard']['name'] ) : false;

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    if ( $lib && $lib == '-1' ) {
      return array('error' => 'No action selected. Please pick a library or create a new one.' );
    }

    // data missing
    if (
      ! $lib ||
      $lib == 'create-new-bunny-stream-library' && ! $name
    ) {
      return array('error' => 'Some required wizard data are missing. Please make sure you provided all information for this page.' );
    }

    // create new API instance
    $api = new FV_Player_Bunny_Stream_API( $bunnycdn_api );

    if ( $lib == 'create-new-bunny-stream-library' ) {
      // create the stream library
      $lib = $api->api_call( 'https://api.bunny.net/videolibrary', array( 'Id' => 1, 'Name' => trim( $name ) ), 'POST' );

      if ( is_wp_error($lib) ) {
        return array('error' => $lib->get_error_message() );
      }

      // load the Pull Zone and get its CDN URL, so we can display thumbnails in the Bunny Stream browser
      $pull_zone = $api->api_call( 'https://api.bunny.net/pullzone/' . $lib->PullZoneId );

      if ( is_wp_error($pull_zone) ) {
        return array('error' => $pull_zone->get_error_message() );
      }
    } else {
      // get existing library data
      $lib = $api->api_call( 'https://api.bunny.net/videolibrary/' . $lib );

      if ( is_wp_error($lib) ) {
        return array('error' => $lib->get_error_message() );
      }

      // load the Pull Zone and get its CDN URL, so we can display thumbnails in the Bunny Stream browser
      $pull_zone = $api->api_call( 'https://api.bunny.net/pullzone/' . $lib->PullZoneId );

      if ( is_wp_error($pull_zone) ) {
        return array('error' => $pull_zone->get_error_message() );
      }
    }

    // store the library info in config
    $fv_fp->conf['bunny_stream']['api_key'] = $lib->ApiKey;
    $fv_fp->conf['bunny_stream']['lib_id'] = $lib->Id;
    $fv_fp->conf['bunny_stream']['cdn_hostname'] = $pull_zone->Hostnames[0]->Value;
    $fv_fp->_set_conf( $fv_fp->conf );

    return array( 'ok' => true );
  }

}

$this->register_step('FV_Player_Bunny_Stream_Wizard_Stream_Libs');