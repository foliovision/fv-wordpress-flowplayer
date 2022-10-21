/*
 * 1) Preload only first video on the page
 * 2) Do not preload YouTube and videos which require Ajax to load - video/fv-mp4
 */
if (typeof (flowplayer) !== 'undefined'){
  freedomplayer.preload_count = 0;

  freedomplayer(function(api, root) {
    root = jQuery(root);

    // Prevent preloading for YouTube videos
    var sources = api.conf.clip.sources,
      start_index = jQuery(root).data('playlist_start'),
      index = start_index ? start_index-1 : 0;

    if( api.conf.playlist[index] && api.conf.playlist[index].sources ) {
      sources = api.conf.playlist[index].sources
    }

    for( var i in sources ) {
      if( sources[i].type == 'video/youtube' || sources[i].src.match(/\/\/vimeo.com/) ) {
        disable_preload();
        api.debug( 'Preload not allowed beause of the video type' );
        return;
      }
    }

    if( !api.conf.splash ) {
      freedomplayer.preload_count++;
    }

    // We have to limit it to 1 because of HLS.js, seems to only work for 1 instance
    if( freedomplayer.preload_count > 1 ) {
      disable_preload();
    }

    function disable_preload() {
      api.conf.splash = true;
      api.preload = false;
      root.removeClass('is-poster').addClass('is-splash');
    }
  });
}