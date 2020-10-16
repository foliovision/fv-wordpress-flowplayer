/**
 * Improve subtitle activation
 * 
 * Normally Flowplayer doesn't show the subtitles before the subtitle line start time comes,
 * although it works on seek. But not on subtitle activation
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var currentPoint,
    check = false; // should we check for new subtitles?

  api.bind('cuepoint', function(e, api, cue) { // Get current cuepoint
    check = false;
    if (cue.subtitle) {
      currentPoint = cue.index;
    }
  });

  api.on('ready', function(e, api){
    root.find('.fp-subtitle-menu strong').text(fv_flowplayer_translations.closed_captions); // translate closed captions
    root.find('.fp-subtitle-menu a[data-subtitle-index="-1"]').text(fv_flowplayer_translations.no_subtitles) // translate no subtitles
  });

  // Start checking on subtitle menu click
  // It would be better to listen to subtitle activation event, but there is none
  root.on('click', '.fp-subtitle-menu a[data-subtitle-index]', function(e) {
    // Only if you picked some actual subtitle, no "No subtitles"
    if( jQuery(this).data('subtitle-index') > -1 ) {
      check = true;

      // Since subtitle load doesn't trigger any event, we just have to keep checking
      // if there are any new cuepoint added
      api.on('progress', time_check);
    }
  });

  // Trigger cuepoint if user enables subtitles during play 
  function time_check(e, api, time) {
    if(check) {
      (api.cuepoints || []).forEach(function(cue, index) { // Find subtitle which wasnt shown
        var entry = cue.subtitle;

        // skip the subtitle if it was just shown
        if (entry && currentPoint != index) {
          // if the playback position falls between subtitle line start and end time
          if (time >= cue.time && (!entry.endTime || time <= entry.endTime)) {
            // show the subtitle
            api.trigger('cuepoint', [api, cue]);
          }
        }
      });
    }
  }

});