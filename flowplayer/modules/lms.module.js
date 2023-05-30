if( typeof(flowplayer) != "undefined" ) {
  flowplayer(function (api, root) {
    root = jQuery(root);

    // If the player is in a LearnDash lesson which is already completed, do nothing
    // If there is video-progression = true, then the user has to watch the video to complete the lesson
    var learndash_video = root.closest('.ld-video');
    if( learndash_video.length && typeof( learndash_video.data('video-progression') ) == "boolean" && learndash_video.data('video-progression') == false ) {
      return;
    }

    if( root.data('lms_teaching') ) {

      var disable_seek,
        position,
        top_position,
        index,
        stored_max_position = []; // keep track of max played position by video

      api.on('ready', function (e,api,video) {
        disable_seek = typeof(api.video.saw) == 'undefined';

        index = api.video.index ? api.video.index : 0 // get current video index

        position = api.video.position ? api.video.position : 0;
        top_position = api.video.top_position ? api.video.top_position : 0;

        if( typeof stored_max_position[index]  == 'undefined' ) {
          if( top_position ) {
            stored_max_position[index] = top_position;
          } else if( position ) {
            stored_max_position[index] = position;
          } else if ( api.video.fv_start ) {
            stored_max_position[index] = api.video.fv_start;
          } else {
            stored_max_position[index] = 0;
          }
        }

        // console.log('disable_seek', disable_seek, 'position', position, 'stored_max_position[index]', stored_max_position[index]);
      });

      api.on('progress', function(e,api,current) {
        if( stored_max_position[index] < current ) {
          stored_max_position[index] = current;
        }
      });

      api.on('beforeseek', function(e,api,time) {
        if( disable_seek ) {
          // console.log('beforeseek', time, 'position', position, 'stored_max_position[index]', stored_max_position[index]);

          if( time <= position || time <= stored_max_position[index] ) {
            console.log( 'FV Player lms: allow seek to' , time );
          } else {
            // Remove previously shown warning
            api.trigger('fv-lms-teaching-be-gone');

            // stop seeking
            e.preventDefault();
            e.stopPropagation();

            // Show the notices
            var notice = fv_player_notice( root, '<p>'+fv_flowplayer_translations.msg_no_skipping+'<br />'+fv_flowplayer_translations.msg_watch_video+'</p>', 'fv-lms-teaching-be-gone' );
            notice.addClass('fv-player-lms-teaching')

            // Remove these notices in 2 seconds
            setTimeout( function() {
              api.trigger('fv-lms-teaching-be-gone');
            }, 2000 );

            // Seek to the maximum allowed time
            api.seek(stored_max_position[index]);
          }

        }
      });

    }

  });
}
