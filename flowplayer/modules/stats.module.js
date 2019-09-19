// Video stats
flowplayer( function(api,root) {
  root = jQuery(root);
  
  if( !api.conf.fv_stats || !api.conf.fv_stats.enabled && ( !root.data('fv_stats') || root.data('fv_stats') == 'no' ) ) return;
  
  api.on('ready', function(e,api) {
    api.one('progress', function(e,api) {
      if( api.video.id ) {
        jQuery.post( api.conf.fv_stats.url, {
          'blog_id' : api.conf.fv_stats.blog_id,
          'tag' : 'play',
          'video_id' : api.video.id
        } );
      }
    });
  });

});