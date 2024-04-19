/*
 * 1) Preload only first video on the page
 * 2) Do not preload YouTube and videos which require Ajax to load - video/fv-mp4
 */
if (typeof (flowplayer) !== 'undefined'){
  freedomplayer.preload_count = 0;
  freedomplayer.preload_limit = 3;

  freedomplayer(function(api, root) {
    root = jQuery(root);

    // Prevent preloading for YouTube videos
    var sources = false,
      start_index = jQuery(root).data('playlist_start'),
      index = start_index ? start_index-1 : 0;

    if ( api.conf.clip ) {
      sources = api.conf.clip.sources;
    }

    if( api.conf.playlist[index] && api.conf.playlist[index].sources ) {
      sources = api.conf.playlist[index].sources
    }

    for( var i in sources ) {
      if( sources[i].type == 'video/youtube' || sources[i].src.match(/\/\/vimeo.com/) ) {
        disable_preload();
        api.debug( 'Preload not allowed beause of the video type' );
        return;
      }

      // If there's HLS video, only preload 1 video
      // This is to prevent multiple HLS videos from preloading as HLS.js does not like that
      // So if there's MP4 and HLS they both preload, but no further videos will preload
      if ( sources[i].type == 'application/x-mpegurl' ) {
        freedomplayer.preload_limit = 1;
      }
    }

    if( !api.conf.splash ) {
      freedomplayer.preload_count++;
    }

    if( freedomplayer.preload_count > freedomplayer.preload_limit ) {
      disable_preload();
    }

    function disable_preload() {
      api.conf.splash = true;
      api.preload = false;
      root.removeClass('is-poster').addClass('is-splash');
    }
  });
}
