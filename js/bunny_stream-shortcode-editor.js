(function ($) {
  var field = 'fv_wp_flowplayer_field_encoding_job_id',
    meta_key = 'encoding_job_id';
    
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


}(jQuery));