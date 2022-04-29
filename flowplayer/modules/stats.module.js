// Video stats
flowplayer( function(api,root) {
  root = jQuery(root);

  var last_tracked = -1; // store video index to check if video was tracked already
  
  if( !api.conf.fv_stats || !api.conf.fv_stats.enabled && ( !root.data('fv_stats') || root.data('fv_stats') == 'no' ) ) return;
  
  api.on('ready finish', function(e,api) { // first play and replay
    api.one('progress', function(e,api) {
      if( root.data('fv_stats_data') ) {
        try {
          var player_post_data = root.data('fv_stats_data');
        } catch(e) {
          return false;
        }

        // each video should be only tracked once!
        if( last_tracked == get_index() ) return;

        last_tracked = get_index();

        jQuery.post( api.conf.fv_stats.url, {
          'blog_id' : api.conf.fv_stats.blog_id,
          'video_id' : api.video.id ? api.video.id : 0,
          'player_id': player_post_data.player_id,
          'post_id' : player_post_data.post_id,
          'tag' : 'play'
        } );
      }
    });
  }).on('finish', function() {
    last_tracked = -1; // reset on finish to allow tracking video again
  });

  function get_index() {
    return api.video.index ? api.video.index : 0;
  }

});