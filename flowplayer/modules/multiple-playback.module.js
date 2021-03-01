flowplayer( function(api,root) {
  root = jQuery(root);

  var instance_id = root.data('flowplayer-instance-id');

  flowplayer.audible_instance = -1;

  // we discard the splash configuration for each player instance
  // that way it won't get unloaded if another player starts playing
  // we need to do this once it actually starts loading, as otherwise Flowplayer would only start buffering the video
  api.one('load', function() {
    // we need to add a bit of wait as otherwise it would require another click to start playing
    setTimeout( function() {
      api.conf.splash = false;
    }, 0 );
  });

  // on iOS only one audible video can play at a time, so we must mute the other players
  api.on('ready', function() {
    var is_muted = root.data('volume') == 0;
    
    if( !is_muted ) {
      // mark the current player as the one who is making the noise
      flowplayer.audible_instance = instance_id;
    }
    
    // we go through all the players to paused or mute them all
    jQuery('.flowplayer[data-flowplayer-instance-id]').each( function() {
      var player = jQuery(this).data('flowplayer');
      
      // we must skip the current player, as the load even can occur multiple times
      // like for example when you switch to another video in playlist
      var current_instance_id = jQuery(this).data('flowplayer-instance-id');

      if( flowplayer.audible_instance == -1 || current_instance_id == flowplayer.audible_instance || current_instance_id == instance_id ) return;      

      if( player ) {
        if( player.ready ) {
          // if multiple video playback is enabled we go through all the players to mute them all
          if( api.conf.multiple_playback ) {
            // but only if the video is audible
            if( !is_muted ) {
              player.mute(true,true);
            }
            
          // otherwide pause the other player
          } else if( player.playing ) {
            player.pause();
          }
        } else {
          player.clearLiveStreamCountdown(); // if not playing stop countdown and unload if other video plays

          // TODO: Check for YouTube and Vimeo
          player.unload();
        }
      }
    });

  }).on('mute', function(e,api,muted) {
    // if the player was unmuted, mute the player which was audible previously
    if( !muted && flowplayer.audible_instance != instance_id ) {
      flowplayer(flowplayer.audible_instance).mute(true,true);

      // now our player is audible
      flowplayer.audible_instance = instance_id;
    }

  }).on('resume', function() {
    // if we resume a video, we need to pause all the other ones, unless multiple playback is enabled
    if( !api.conf.multiple_playback ) {
      jQuery('.flowplayer[data-flowplayer-instance-id]').each( function() {

        // of course skip the current player which is being resumed
        if( instance_id == jQuery(this).data('flowplayer-instance-id') ) return;

        var player = jQuery(this).data('flowplayer');

        if( player && player.playing ) {
          player.pause();
        }
      });
    }
  })
});