// HSL engine on iOS and on Safari doesn't report error for HTTP 403. If there is no progress event for 5 second and it's not loading or anything, we can assume that the HLS segment has failed to load
flowplayer( function(api,root) {  
  if( !flowplayer.support.browser.safari && !flowplayer.support.iOS ) return;
  
  root = jQuery(root);
  
  var no_progress = false,
    time_start = 0,
    time_delay = 0;
  
  api.on('load', function(e,api,video) {
    clearInterval(no_progress);
    time_start = new Date().getTime();
  });
  
  api.on('ready', function() {
    clearInterval(no_progress);
    root.find('video').on( "stalled", function(e) {} ); // could be helpful, but just using this event alone is not enough: https://github.com/flowplayer/flowplayer/issues/1403
    
    if( api.engine.engineName == 'html5' ) {
      
      time_delay = new Date().getTime() - time_start;
      
      console.log('Video took '+time_delay+' ms to start');
      
      if( time_delay < 500 ) time_delay = 500;      
      time_delay = 10 * time_delay;
      if( time_delay > 15000 ) time_delay = 15000;
      
      no_progress = setTimeout( hls_check, time_delay );
      
      api.on('progress', function(e,api,time) {
        clearInterval(no_progress);
        no_progress = setTimeout( hls_check, time_delay );
      });
    }
  });
  
  function hls_check() {    
    if( api.ready && api.playing && !api.loading && !api.finished ) {
      clearInterval(no_progress);
      console.log('Video stale for '+time_delay+' ms, triggering error!');      
      fv_player_notice(root,fv_flowplayer_translations.video_reload+' <a class="fv-player-reload" href="#">&#x21bb;</a>','progress error unload');
      jQuery('.fv-player-reload').click( function() {
        api.trigger('error', [api, { code: 4, video: api.video }]);
        return false;
      });
    }
  }
});