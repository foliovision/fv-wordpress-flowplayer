/**
 * Improve subtitle activation
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var currentPoint, check = false;

  api.bind('cuepoint', function(e, api, cue) { // Get current cuepoint
    check = false;
    if (cue.subtitle) {
      currentPoint = cue.index;
    }
  });

  root.on('click', '.fp-subtitle-menu a[data-subtitle-index]', function(e) { // Start checking on subtitle menu click
    if( jQuery(this).data('subtitle-index') > -1 ) {
      check = true;
      api.on('progress', time_check);
    }
  });

  // Trigger cuepoint if user enables subtitles during play 
  function time_check(e, api, time) {
    if(check) {
      (api.cuepoints || []).forEach(function(cue, index) { // Find subtitle which wasnt shown
        var entry = cue.subtitle;
        if (entry && currentPoint != index) {
          if (time >= cue.time && (!entry.endTime || time <= entry.endTime)) {
            api.trigger('cuepoint', [api, cue]);
          }
        }
      });
    }
  }

});