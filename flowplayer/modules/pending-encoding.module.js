if( typeof(flowplayer) !== "undefined" ) {
  flowplayer( function(api,root) {
    root = jQuery(root);

    var notice = false,
      hlsjs;

    // We need some special handling for HLS JS
    // TODO: Figure out the core Flowplayer issue here
    flowplayer.engine('hlsjs-lite').plugin(function(params) {
      hlsjs = params.hls;
    });

    // Do we need to show the notice right away?
    var playlist = api.conf.playlist.length ? api.conf.playlist : [ api.conf.clip ];
    if( playlist[0] && playlist[0].pending_encoding) {
      show_notice(playlist[0]);
    } 

    api.on('load', function(e,api,video) {
      if( video.pending_encoding ) {
        show_notice(video);
        if( hlsjs ) {
          hlsjs.destroy();
        }

        // Block further loading
        return false;
      }

      remove_notice();
    });

    function show_notice(video) {
      remove_notice();

      var title = 'Video is being processed',
        message = 'Please return later to see the actual video in this player.';

      if( video.pending_encoding_error ) {
        title = 'Video unavailable';
        message = 'There was an error in the video encoding.';
      } else if( video.pending_encoding_progress ) {
        message += '<br /><br />('+video.pending_encoding_progress+' done)';
      }

      notice = jQuery('<div class="fv-player-encoder-video-processing-modal"><div><h2>'+title+'</h2><p>'+message+'</p></div></div');
      root.append(notice);
    }

    function remove_notice() {
      if( notice ) {
        notice.remove();
      }
    }
  })
}