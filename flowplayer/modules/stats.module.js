// Video stats
flowplayer( function(api,root) {
  root = jQuery(root);
  
  if( !api.conf.fv_stats || !api.conf.fv_stats.enabled && ( !root.data('fv_stats') || root.data('fv_stats') == 'no' ) ) return;
  
  api.on('ready', function(e,api) {
    api.one('progress', function(e,api) {
      if( root.data('fv_stats_data') ) {
        try {
          var player_post_data = root.data('fv_stats_data');
        } catch(e) {
          return false;
        }

        jQuery.post( api.conf.fv_stats.url, {
          'blog_id' : api.conf.fv_stats.blog_id,
          'video_id' : api.video.id ? api.video.id : 0,
          'player_id': player_post_data.player_id,
          'post_id' : player_post_data.post_id,
          'tag' : 'play'
        } );
      }
    });
  });

});