( function($) {

  // We store these globally as we only run single beacon on page unload
  var watched = {},
    watched_has_data = false;

  // Video stats
  flowplayer( function(api,root) {
    root = $(root);

    var last_tracked = -1, // store video index to check if video was tracked already
      last_time = 0,
      is_first_progress;

    if( !api.conf.fv_stats || !api.conf.fv_stats.enabled && ( !root.data('fv_stats') || root.data('fv_stats') == 'no' ) ) return;

    try {
      var data = root.data('fv_stats_data');
      if ( !data ) {
        return;
      }
    } catch(e) {
      return false;
    }

    api.on('ready finish', function(e,api) { // first play and replay
      api.on('progress', function( e, api, time ) {
        
        // Do not track "play" if
        if(
          // it's too early in the video
          time < 1 ||
          // if it's the end of the video, as the progress even might run if it's the "waiting" event for MPEG-DASH
          api.video.duration && time > api.video.duration - 1 ||
          // if the video was already trackedd
          last_tracked == get_index()
        ) return;

        last_tracked = get_index();

        $.post( api.conf.fv_stats.url, {
          'blog_id' : api.conf.fv_stats.blog_id,
          'video_id' : api.video.id ? api.video.id : 0,
          'player_id': data.player_id,
          'post_id' : data.post_id,
          'user_id' : api.conf.fv_stats.user_id,
          'tag' : 'play',
          '_wpnonce' : api.conf.fv_stats.nonce,
        } );
      });

      last_time = 0;
      is_first_progress = true;

    }).on('finish', function() {
      // If the video is not set to loop...
      if ( ! api.conf.loop ) {
        last_tracked = -1; // reset to allow tracking video again
      }

    }).on( 'progress', function( e, api, time ) {

      if ( time == 0 ) {
        return;
      }

      if ( api.seeking ) {
        last_time = time;
        return;
      }

      // Ignore first progress as it often occurs when video position is only restoring etc.
      if ( is_first_progress ) {
        is_first_progress = false;
        return;
      }

      if ( last_time == 0 || time <= last_time ) {
        last_time = time;
        return;
      }

      // Building global data is complex
      if ( !watched[ data.player_id ] ) {
        watched[ data.player_id ] = {}
      }
      if ( !watched[ data.player_id ][ data.post_id ] ) {
        watched[ data.player_id ][ data.post_id ] = {}
      }
      if ( !watched[ data.player_id ][ data.post_id ][ api.video.id ] ) {
        watched[ data.player_id ][ data.post_id ][ api.video.id ] = 0;
      }

      watched[ data.player_id ][ data.post_id ][ api.video.id ] += time - last_time;

      watched_has_data = true;

      last_time = time;
    });

    api.on('cva', function(e,api) {
      $.post( api.conf.fv_stats.url, {
        'blog_id' : api.conf.fv_stats.blog_id,
        'video_id' : api.video.id ? api.video.id : 0,
        'player_id': data.player_id,
        'post_id' : data.post_id,
        'user_id' : api.conf.fv_stats.user_id,
        'tag' : 'click',
        '_wpnonce' : api.conf.fv_stats.nonce,
      } );
    });

    function get_index() {
      return api.video.index ? api.video.index : 0;
    }

  });

  function stats_send() {
    // Go through watched and round the time values
    for ( var player_id in watched ) {
      for ( var post_id in watched[ player_id ] ) {
        for ( var video_id in watched[ player_id ][ post_id ] ) {
          watched[ player_id ][ post_id ][ video_id ] = Math.round( watched[ player_id ][ post_id ][ video_id ] );
        }
      }
    }

    var conf = window.freedomplayer ? freedomplayer.conf : flowplayer.conf;

    if ( conf.debug ) {
      fv_player_stats_watched();
    }

    var fd = new FormData();
    fd.append( 'tag', 'seconds' );
    fd.append( 'blog_id', conf.fv_stats.blog_id );
    fd.append( 'user_id', conf.fv_stats.user_id );
    fd.append( '_wpnonce', conf.fv_stats.nonce );
    // TODO: Can we use pure JSON?
    fd.append( 'watched', encodeURIComponent(JSON.stringify(watched)) );

    navigator.sendBeacon(
      conf.fv_stats.url,
      fd
    );
  }

  /**
   * Send stats when closing the browser
   */
  $(window).on('beforeunload pagehide', function () {
    var sendBeaconSupported = ("sendBeacon" in navigator);

    // only fire a single AJAX call if we're closing / reloading the browser
    if (!flowplayer.conf.stats_sent && sendBeaconSupported) {
      flowplayer.conf.stats_sent = true;

      if ( ! watched_has_data ) {
        return;
      }

      stats_send();
    }
  });

  /**
   * Send stats periodically
   */
  setInterval( function() {

    if ( ! watched_has_data ) {
      return;
    }

    stats_send();

    // Stats sent, clear the data structure
    watched = {}
    watched_has_data = false;

  }, 5 * 60 * 1000 );

  // For debugging
  window.fv_player_stats_watched = function() {

    $.each( watched, function( k, v ) {
      console.log( 'player id: ' + k );
      $.each( v, function( i, j ) {
        console.log( 'post id: ' + i );
        $.each( j, function( k, l ) {
          console.log( 'video id: ' +  k + ' seconds: '+l );
        });
      });
    });
  }

})(jQuery);
