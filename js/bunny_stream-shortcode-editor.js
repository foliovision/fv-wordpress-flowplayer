(function ($) {
  var field = 'fv_wp_flowplayer_field_coconut_encoding_job_id',
    meta_key = 'coconut_encoding_job_id';
    
  // TODO: The above is all you should ever need!
  
  ('use strict');
  $(document).on('fv_flowplayer_video_meta_save', function(event, data, element_data_index, element) {
    //console.log(element.id,field);
    if (element.id != field) {
      return;
    }

    if (!data['video_meta']['video']) {
      data['video_meta']['video'] = {};
    }

    if (!data['video_meta']['video'][element_data_index]) {
      data['video_meta']['video'][element_data_index] = {};
    }

    // handle hslkey metadata for each video
    fv_flowplayer_insertUpdateOrDeleteVideoMeta({
      data: data,
      meta_section: 'video',
      meta_key: meta_key,
      meta_index: element_data_index,
      element: element
    });
  });

  $(document).on('fv_flowplayer_video_meta_load', function(event, element_meta_index, metadata, $video_data_tab) {
    var meta_found = false;

    if (metadata) {
      for (var i in metadata) {
        if (metadata[i].meta_key == meta_key) {
          if (metadata[i].meta_value) {
            meta_found = true;
          }

          $video_data_tab.find('#'+field)
            .val(metadata[i].meta_value)
            .attr('data-id', metadata[i].id);
        }
      }
    }
  });

  $(document).on('fv_flowplayer_shortcode_parse', function() {
    $('#'+field).val('');
  } );

  function check_for_coconut_src_value ( event, src_input_value, result, src_input_element ) {
    var $input_field_notice = jQuery( src_input_element ).siblings('.fv-player-src-playlist-support-notice');

    // if we're coming from editor opening event, fire up this function again with the correct source input element
    if ( event.type == 'fv_player_editor_finished' && !event.customUpdated ) {
      event.customUpdated = true;
      var $inputElement = $( '#fv_wp_flowplayer_field_src:visible' );
      check_for_coconut_src_value ( event, $inputElement.val(), result, $inputElement );
    }

    // if the source has a coconut_processing_ prefix, we want to show info about what it means
    if ( src_input_value && src_input_value.substring(0, 19) == 'coconut_processing_' ) {
      $input_field_notice
        .data('oldTextCoconut', $input_field_notice.text())
        .data('wasVisibleCoconut', ( $input_field_notice.is(':visible') ? 1 : 0 ) )
        .text('This video is pending the FV Player Coconut encoding.')

      // as other handlers might hide our message, we need to show it after a small timeout
      setTimeout(function() {
        $input_field_notice.show();
      }, 10);
    } else if ( $input_field_notice.data('oldTextCoconut') ) {
      $input_field_notice
        .text( $input_field_notice.data('oldTextCoconut') )
        .removeData('oldTextCoconut');

      if ( !$input_field_notice.data('wasVisibleCoconut') ) {
        $input_field_notice.hide();
      } else {
        // as other handlers might hide our message, we need to show it after a small timeout
        setTimeout(function() {
          $input_field_notice.show();
        }, 10);
      }

      $input_field_notice.removeData('wasVisibleCoconut');
    }
  };

  $(document).on('fv-player-editor-src-change fv_player_editor_finished', check_for_coconut_src_value );

}(jQuery));