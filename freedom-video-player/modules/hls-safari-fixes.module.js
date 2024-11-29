// HSL engine on iOS and on Safari doesn't report error for HTTP 403.
// So we use certain extra events from https://developer.apple.com/documentation/webkitjs/htmlmediaelement
// To make our best guesss
flowplayer( function(api,root) {
  if( !flowplayer.support.browser.safari && !flowplayer.support.iOS ) return;

  root = jQuery(root);

  var video_tag = false,
    did_start_playing = false,
    live_stream_check = false,
    are_waiting_already = 0; // make sure you wait for the event only on one event at a time

  // first we need to obtain the video element
  api.on('ready', function( e, api, video ) {
    are_waiting_already = 0;

    did_start_playing = false;

    // only work if the video is using token in URL
    if( api.engine.engineName == 'html5' && video.src.match(/\?/) ) {
      // find the video
      video_tag = root.find('video');

      if( !video_tag.data('fv-ios-recovery') ) {
        //video_tag.on( "stalled suspend abort emptied error waiting", debug );

        // triggered if the iOS video player runs out of buffer
        video_tag.on( "waiting", wait_for_stalled );

        // we use this to ensure the video tag has the event bound only once
        video_tag.data('fv-ios-recovery',true);
      }

      // If it's live stream there is no "waiting" event, so it never triggers
      if( api.live && video.src.match(/m3u8|stream_loader/) ) {
        console.log("FV Player: iOS video element is a live stream...");

        clearInterval(live_stream_check);

        live_stream_check = setTimeout( function() {
          // Check if there is a valid HLS stream
          jQuery.get( video.src, function( response ) {
            if( !response.match(/#EXT/) ) {
              console.log("FV Player: iOS video element live stream does not look like a HLS file, triggering error...");

              // Trigger error, but use code 1 so that the reload routine will not be used
              api.trigger('error', [api, { code: 1, video: api.video }]);
            }
          });
        }, 5000 );
      }

      api.one('progress', function() {
        did_start_playing = true;
        clearInterval(live_stream_check);
      });
    }

  });

  // you might seek into unbuffered part of video too
  api.bind('beforeseek', wait_for_stalled );

  function debug(e) {
    console.log("FV Player: iOS video element: " + e.type);
  }

  function wait_for_stalled() {
    // do not run if there was not progress event
    // we don't want the initial seek when resuming position to trigger error
    // so we track if the playback did actually start
    if( !did_start_playing ) {
      return;
    }

    if( video_tag && api.engine.engineName == 'html5' ) {
      are_waiting_already++;
      if( are_waiting_already > 1 ) {
        if( are_waiting_already > 3 ) {
          console.log("FV Player: iOS video element needs a push, triggering 'stalled'");
          video_tag.trigger( "stalled" );
        }
        return;
      }

      console.log("FV Player: iOS video element will trigger error after 'stalled' arrives");

      // then it also triggers this event if it really fails to load more
      video_tag.one( "stalled", function() {
        var time = api.video.time;

        // simple video files can be checked directly
        if( api.video.type.match(/video\//) ) {
          console.log("FV Player: Running check of video file...");

          // create a new video tag and let iOS fetch the meta data
          var test_video = document.createElement('video');
          test_video.src = api.video.src;
          test_video.onloadedmetadata = function() {
            are_waiting_already = 0;
            console.log("FV Player: Video link works");
          }

          test_video.onerror = function() {
            console.log("FV Player: Video link issue!");
            if( are_waiting_already > 0 ) {
              api.trigger('error', [api, { code: 4, video: api.video }]);
            }
          }
          return;
        }

        // for HLS streams -
        // give it a bit more time to really play
        setTimeout( function() {
          console.log(api.video.time,time);

          // did the video advance?
          if( api.video.time != time ) {
            are_waiting_already = 0;

            console.log("FV Player: iOS video element continues playing, no need for error");
            return;
          }

          // the video is paused, so it should not progress and it's fine
          if( api.paused ) {
            are_waiting_already = 0;

            console.log("FV Player: iOS video element paused, no need for error");
            return;
          }

          // so we can tell Flowplayer there is an error
          api.trigger('error', [api, { code: 4, video: api.video }]);
        }, 5000 );

      } );
    }
  }

  /**
   * Record video duration once it starts.
   *
   * Then if the video is paused and it's paused at the end of video playtime. And the current position where it paused is lower than the previously detected duration...
   * ...then it's time to reload the video as iPhone simply failed to load the video segments and said the video is shorter.
   */
  var recorded_duration = 0;

  api.on( 'ready', function( e, api ) {
    api.one( 'progress', function( e, api ) {
      recorded_duration = api.video.duration;
      console.log( 'recorded_duration', recorded_duration );
    })
  });

  api.on( 'pause', function( e, api ) {
    var video_tag = root.find( 'video');
    if ( video_tag.length ) {
      if ( parseInt( api.video.time ) === parseInt( video_tag[0].duration ) ) {
        if ( recorded_duration > api.video.time ) {
          console.log( 'suddenly the video is much shorter, why?', recorded_duration, video_tag[0].duration );

          api.video.duration = recorded_duration;

          api.trigger('error', [api, { code: 4, video: api.video }]);
        }
      }
    }
  });


});
