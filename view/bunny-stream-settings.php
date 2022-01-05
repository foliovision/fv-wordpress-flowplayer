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
