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
      api.one('progress', function(e,api) {
        // each video should be only tracked once!
        if( last_tracked == get_index() ) return;

        last_tracked = get_index();

        $.post( api.conf.fv_stats.url, {
          'blog_id' : api.conf.fv_stats.blog_id,
          'video_id' : api.video.id ? api.video.id : 0,
          'player_id': data.player_id,
          'post_id' : data.post_id,
          'user_id' : api.conf.fv_stats.user_id,
          'tag' : 'play'
        } );
      });

      last_time = 0;
      is_first_progress = true;

    }).on('finish', function() {
      last_tracked = -1; // reset on finish to allow tracking video again

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

    function get_index() {
      return api.video.index ? api.video.index : 0;
    }

  });

  $(window).on('beforeunload pagehide', function () {
    // only fire a single AJAX call if we're closing / reloading the browser
    if (!flowplayer.conf.stats_sent) {
      flowplayer.conf.stats_sent = true;

      if ( !watched_has_data ) {
        return;
      }

      fv_player_stats_watched();

      var conf = window.freedomplayer ? freedomplayer.conf : flowplayer.conf;

      var fd = new FormData();
      fd.append( 'tag', 'seconds' );
      fd.append( 'blog_id', conf.fv_stats.blog_id );
      fd.append( 'user_id', conf.fv_stats.user_id );
      // TODO: Can we use pure JSON?
      fd.append( 'watched', encodeURIComponent(JSON.stringify(watched)) );

      navigator.sendBeacon(
        conf.fv_stats.url,
        fd
      );
    }
  });

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