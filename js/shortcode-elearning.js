(function ($) {
  ('use strict');

  jQuery(document).on('fv_flowplayer_player_meta_save', function(event, data) {

    fv_flowplayer_insertUpdateOrDeletePlayerMeta({
      data: data,
      meta_section: 'player',
      meta_key: 'lms_teaching_player',
      element: '#lms_teaching_player',
      handle_delete: false
    });

  });

  jQuery(document).on('fv_flowplayer_player_meta_load', function(event, data) {

    if (data.meta) {
      for (var i in data.meta) {
        if (data.meta[i].meta_key == 'lms_teaching_player') {
          if(data.meta[i].meta_value == 'yes') {
            document.getElementById("lms_teaching_player").selectedIndex = 1;
          }

          if(data.meta[i].meta_value == 'no') {
            document.getElementById("lms_teaching_player").selectedIndex = 2;
          }

          jQuery('#lms_teaching_player').attr('data-id', data.meta[i].id);

        }
      }
    }
  });

}(jQuery));
