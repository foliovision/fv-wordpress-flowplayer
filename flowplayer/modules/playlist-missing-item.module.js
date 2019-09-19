/*
 *  Playlist check
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var playlist = api.conf.playlist;
  var videoIndex;

  api.bind("ready", function (e, api, video) {
    videoIndex = video.index;
  });

  api.bind("error", function (e,api, error) {
    setTimeout(function(){
      if( api.conf.playlist.length > 0 && api.error == true) {
        api.error = api.loading = false;
        root.removeClass('is-error');
        root.find('.fp-message.fp-shown').remove();
        if(videoIndex >= playlist.length -1){
          api.play(playlist.length -1);
        } else {
          api.play(videoIndex + 2)
          videoIndex += 1; // without this it will fail to recover if 2 items fail in a row
        }
      }
    },1000);
  });

});