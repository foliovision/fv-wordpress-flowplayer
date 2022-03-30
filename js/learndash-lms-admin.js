jQuery( function($) {
  var lms_hide_fields = jQuery(
    `#learndash-lesson-display-content-settings_lesson_video_url_field,
    #learndash-lesson-display-content-settings_lesson_video_focus_pause_field,
    #learndash-lesson-display-content-settings_lesson_video_auto_start_field,
    #learndash-lesson-display-content-settings_lesson_video_show_controls_field,
    #learndash-lesson-display-content-settings_lesson_video_track_time_field,
    #learndash-topic-display-content-settings_lesson_video_url_field,
    #learndash-topic-display-content-settings_lesson_video_focus_pause_field,
    #learndash-topic-display-content-settings_lesson_video_auto_start_field,
    #learndash-topic-display-content-settings_lesson_video_show_controls_field,
    #learndash-topic-display-content-settings_lesson_video_track_time_field`
  );
  
  var lms_show_fields = jQuery(
    `#learndash-lesson-display-content-settings_lesson_fv_player_field,
    #learndash-topic-display-content-settings_lesson_fv_player_field`
  );

  var fv_player_toggle = jQuery(
    `#learndash-lesson-display-content-settings_lesson_use_fvplayer_video,
    #learndash-topic-display-content-settings_lesson_use_fvplayer_video`
  );

  is_fv_player_enabled();

  fv_player_toggle.on('change', is_fv_player_enabled);
  
  var video_progression_toggle = jQuery(
    `#learndash-lesson-display-content-settings_lesson_video_enabled,
    #learndash-topic-display-content-settings_lesson_video_enabled`
  );
  video_progression_toggle.on('change', is_fv_player_enabled);
  
  function is_fv_player_enabled() {
    lms_hide_fields.toggle( !fv_player_toggle.prop('checked') );
    lms_show_fields.toggle( fv_player_toggle.prop('checked') );
  }
});
