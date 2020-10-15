/*
Video loop fix

Unfortunate bug in Flowplayer 7.2.5 and later when using MP4 of Hls.js (html5factory)

This is because Flowplayer tried to retain the paused state when seeking after finish:
https://github.com/flowplayer/flowplayer/blob/d5b70e7a40518582287d9b73aa76ea568c948816/lib/engine/html5-factory.js#L186

So then the video loop code won't work:
https://github.com/flowplayer/flowplayer/blob/922c21346f1375eac0782ede472dc65e61e95eac/lib/ext/playlist.js#L41
*/
flowplayer( function(api,root) {

  // So, once the video finishes
  api.bind('finish', function() {
    var finished_at = api.video.time;

    // ...and it's set to loop
    if( api.video.loop ) {
      
      // ...and it's paused
      api.one('pause', function() {
        /// ...and it's paused right there, even before seeking to the actual loop position        
        if( finished_at <= api.video.time ) {
          // ...we force resume the video
          api.resume();
        }
      });
    }
  });
});;