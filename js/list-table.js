jQuery( function($) {
  $(document).on('click','.fv_player_splash_list_preview', function() {
    fv_player_editor.set_current_video_to_edit( $(this).parents('.thumbs').find('.fv_player_splash_list_preview').index(this) );
    $(this).parents('tr').find('.fv-player-edit').eq(0).trigger('click');
  });

  $(document).on('click','.column-shortcode input', function() {
    $(this).select();
  });
});