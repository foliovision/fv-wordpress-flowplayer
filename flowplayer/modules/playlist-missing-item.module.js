/*
 * Advance to next playist item if a video is missing
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var playlist = api.conf.playlist,
    videoIndex,
    videos_attempted = []; // we do not want it to try same video again
    
  api.bind("load", function (e, api, video) {
    videoIndex = video.index;
    
    attempted_load(video.index);
  });

  api.bind("error", function (e,api, error) {
    setTimeout(function(){
      // make sure there is still error before going to business
      if( playlist.length > 0 && api.error == true ) {

        // if video checker is on and there is some media to check for the video we do not advance to next video to let admin see the error
        if ( api.conf.video_checker == '1' && playlist[videoIndex].video_checker && playlist[videoIndex].video_checker.length > 0 ) { // Run checker for admin
          return false;
        }
   
        var try_video = videoIndex + 1;
        // if it was the last video, try second to last video again
        if( videoIndex >= playlist.length -1 ) {
          try_video = playlist.length -2;
        }
        
        api.error = api.loading = false;
        root.removeClass('is-error');
        root.find('.fp-message.fp-shown').remove();
        
        // we record the attempted load here as if the video type is not supported, the load event won't run for it
        while( attempted_load(try_video) ) {
          try_video++;
        }
        
        api.play(try_video);
      }
    }, 1000 );
  });
  
  // was the video already attempted? If no, add it to list
  function attempted_load(index) {
    if( !videos_attempted.includes(index) ) {
      videos_attempted.push(index);
      return false;
    }
    return true;
  }
  
});