<?php
global $fv_fp;
?>

<style>
.form-table tr > td:first-child {
  width: 200px;
}
.regular-text {
  width: 100%;
  max-width: 50em;
}
.tabs-panel {
  max-width: 48em;
}

</style>

<form method="POST">
  <table class='form-table'>
    <?php
    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'lib_id' ),
      'name' => __('Stream Library ID', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'api_key' ),
      'name' => __('Stream Library API Key', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'cdn_hostname' ),
      'name' => __('Stream Library CDN Hostname', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    $fv_fp->_get_checkbox(__('Enable Token Authentication', 'fv-wordpress-flowplayer'), array('bunny_stream', 'video_token'), __('Improve Video Security.', 'fv-wordpress-flowplayer'));

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'security_token' ),
      'name' => __('Security Token', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    ob_start();
    ?>
    <p><img width="400" src="<?php echo plugins_url( 'models/bunny-stream-wizard/images/bunnycdn-api.png', dirname(__FILE__) ); ?>" srcset="<?php echo plugins_url( 'models/bunny-stream-wizard/images/bunnycdn-api.png', dirname(__FILE__) ); ?> 1x, <?php echo plugins_url( 'models/bunny-stream-wizard/images/bunnycdn-api-2x.png', dirname(__FILE__) ); ?> 2x" /></p>
    <?php
    $help = ob_get_clean();

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'api_access_key' ),
      'name' => __('API Access Key', 'fv-player-bunny_stream'),
      'class' => 'regular-text code',
      'help' => $help
    ) );

    ?>
    <tr>
      <td></td>
      <td>
        <?php wp_nonce_field( 'fv_player_bunny_stream_settings_nonce', 'fv_player_bunny_stream_settings_nonce' ); ?>
        <input type="submit" class="button button-primary" value="Save" />
      </td>
    </tr>
  </table>
</form>

<script>
  jQuery(function() {
    var api_access_key_input = jQuery('input[name="bunny_stream[api_access_key]"]'),
      api_access_key_row = api_access_key_input.closest('tr'),
      security_token_input = jQuery('input[name="bunny_stream[security_token]"]'),
      security_token_row = security_token_input.closest('tr'),
      pro_compatible = <?php echo json_encode(FV_Player_Bunny_Stream()->fv_player_pro_compatible()); ?>;

    api_access_key_row.hide(); // do not show

    if(security_token_input.val()) { // check if already set
      security_token_row.show();
    } else {
      security_token_row.hide();
    }

    jQuery('input[name="bunny_stream[video_token]"]').on('click', function(e) {
      if(!pro_compatible) {
        alert('To Use This Feature You Need To Install Latest FV Player Pro.');
        jQuery(this).prop('checked', false);
        return false;
      }

      var checked = jQuery(this).prop('checked');

      if(checked === true) {
        api_access_key_row.show();
      } else {
        // wipe data
        api_access_key_input.val('');
        security_token_input.val('');
        api_access_key_row.hide();
        security_token_row.hide();
      }
    });
  });
</script>