/**
 * Subtitle module
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var currentPoint , subtitlesShown = false;

  api.bind("cuepoint", function(e, api, cue) {
    if (cue.subtitle) {
      currentPoint = cue.index;
    }
  });

  // Subtitles
  api.on('progress', function(e,api,time) {
    (api.cuepoints || []).forEach(function(cue, index) {
      var entry = cue.subtitle;
      if (entry && currentPoint != index) {
        if (time >= cue.time && (!entry.endTime || time <= entry.endTime)) api.trigger("cuepoint", [api, cue]);
      }
      // else if (cue.subtitleEnd && time >= cue.time && index == currentPoint + 1) {
      //   api.trigger("cuepoint", [api, cue]);
      // }
    });

  });

});