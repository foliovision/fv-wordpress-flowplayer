/*
 * Advance to next playist item if a video is missing
 *
 * How to test this:
 * 
 * 1) Setup a playlist with two bad videos, then one working video, one bad video and a working video again
 * 2) Clicking player needs to try first and second video, before playing third
 * 3) Clicking first playlist item - needs to be same as above
 * 4) Clicking third video and using "next" needs to try fourth video and play 5 th
 * 5) Clicking fourth video needs to play fifth video
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var playlist = api.conf.playlist,
    videoIndex;
    
  api.bind("load", function (e, api, video) {
    videoIndex = video.index;
  });

  api.bind("error", function (e,api, error) {
    setTimeout(function(){
      if( playlist.length > 0 && api.error == true) {
        videoIndex = api.video.index;

        if ( api.conf.video_checker == '1' && playlist[videoIndex].video_checker && playlist[videoIndex].video_checker.length > 0 ) { // Run checker for admin
          console.log('FV Player: Video checker message present, stopping auto-advance to next playlist item');
          return false;
        }
        
        api.error = api.loading = false;
        root.removeClass('is-error');
        root.find('.fp-message.fp-shown').remove();

        videoIndex++;
        
        // loop playlist if out of items
        if(videoIndex > playlist.length -1){
          videoIndex = 0;
        }

        console.log('FV Player: Playlist item failure, auto-advancing to '+(videoIndex+1)+'. item');
        api.play(videoIndex);
      }
    }, 1000 );
  });
  
});
