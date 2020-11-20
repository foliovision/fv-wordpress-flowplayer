// HSL engine on iOS and on Safari doesn't report error for HTTP 403.
// So we use certain extra events from https://developer.apple.com/documentation/webkitjs/htmlmediaelement
// To make our best guesss
flowplayer( function(api,root) {  
  if( !flowplayer.support.browser.safari && !flowplayer.support.iOS ) return;
  
  root = jQuery(root);
  
  var video_tag = false,
    did_start_playing = false,
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

      api.one('progress', function() {
        did_start_playing = true;
      });
    }

  });

  // you might seek into unbuffered part of video too
  api.bind('beforeseek', wait_for_stalled );
  
  function debug(e) {
    console.log("FV PLayer: iOS video element: " + e.type);
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
          console.log("FV PLayer: iOS video element needs a push, triggering 'stalled'");
          video_tag.trigger( "stalled" );
        }
        return;
      }
      
      console.log("FV PLayer: iOS video element will trigger error after 'stalled' arrives");
      
      // then it also triggers this event if it really fails to load more
      video_tag.one( "stalled", function() {
        var time = api.video.time;

        // simple video files can be checked directly
        if( api.video.type.match(/video\//) ) {
          console.log("FV PLayer: Running check of video file...");

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
            console.log("FV PLayer: iOS video element continues playing, no need for error");
            return;
          }

          // the video is paused, so it should not progress and it's fine
          if( api.paused ) {
            console.log("FV PLayer: iOS video element paused, no need for error");
            return;
          }          
          
          // so we can tell Flowplayer there is an error
          api.trigger('error', [api, { code: 4, video: api.video }]);
        }, 5000 );
        
      } );
    }
  }
  
  

});