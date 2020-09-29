// HSL engine on iOS and on Safari doesn't report error for HTTP 403.
// So we use certain extra events from https://developer.apple.com/documentation/webkitjs/htmlmediaelement
// To make our best guesss
flowplayer( function(api,root) {  
  if( !flowplayer.support.browser.safari && !flowplayer.support.iOS ) return;
  
  root = jQuery(root);
  
  var video_tag = false,
    did_start_playing = false;
  
  // first we need to obtain the video element
  api.on('ready', function() {
    did_start_playing = false;
    
    if( api.engine.engineName == 'html5' ) {
      // find the video
      video_tag = root.find('video');
      
      if( !video_tag.data('fv-ios-recovery') ) {
        // triggered if the iOS video player runs out of buffer
        video_tag.on( "waiting", wait_for_stalled );
        
        // we use this to ensure the video tag has the event bound only once
        video_tag.data('fv-ios-recovery',true);
        //video_tag.on( "stalled suspend abort emptied error waiting", debug );
      }

      api.one('progress', function() {
        did_start_playing = true;
      });
    }

  });

  // you might seek into unbuffered part of video too
  api.bind('beforeseek', function() {
    // we don't want the initial seek when resuming position to trigger error
    // so we track if the playback did actually start in this variable
    if( did_start_playing ) {
      wait_for_stalled();
    }
  });
  
  function debug(e) {
    console.log("FV PLayer: iOS video element: " + e.type);
  }
  
  function wait_for_stalled() {
    if( video_tag && api.engine.engineName == 'html5' ) {
      
      console.log("FV PLayer: iOS video element will trigger error after 'stalled' arrives");
      
      // then it also triggers this event if it really fails to load more
      video_tag.one( "stalled", function() {
        var time = api.video.time;
        
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