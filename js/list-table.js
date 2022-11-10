jQuery( function($) {
  $(document).on('click','.fv_player_splash_list_preview', function() {
    fv_player_editor.set_current_video_to_edit( $(this).parents('.thumbs').find('.fv_player_splash_list_preview').index(this) );
    $(this).parents('tr').find('.fv-player-edit').eq(0).trigger('click');
  });

  $(document).on('click','.column-shortcode input', function() {
    $(this).select();
  });

  $(document).on('click', '[data-fv-player-editor-export-overlay-copy]', function() {
    var button = this;
    fv_player_clipboard( $(button).closest('.fv-player-editor-overlay').find('[name=fv_player_copy_to_clipboard]').val(), function() {
      fv_player_editor.overlay_notice( button, 'Text Copied To Clipboard!', 'success', 3000 );
    }, function() {
      fv_player_editor.overlay_notice( button, '<strong>Error copying text into clipboard!</strong><br />Please copy the content of the above text area manually by using CTRL+C (or CMD+C on MAC).', 'error' );
    });
    
    return false;
  });
});