/*
 *  HLS.js fallback to Flowplayer Flash HLS
 */
flowplayer(function(api, root) {
  var store_engine_pos = -1;
  var store_engine = false;
    
  api.on("error", function (e, api, err) {
    if( err.code != 4 || api.engine.engineName != 'hlsjs' ) return;    
    
    console.log('FV Player: HLSJS failed to play the video, switching to Flash HLS');
    api.error = api.loading = false;
    
    jQuery(root).removeClass('is-error');
    jQuery(flowplayer.engines).each( function(k,v) {
      if( flowplayer.engines[k].engineName == 'hlsjs' ){
        store_engine_pos = k;
        store_engine = flowplayer.engines[k];        
        delete(flowplayer.engines[k]);        
      }
    });
    
    var index = typeof(api.video.index) != "undefined" ? api.video.index : 0;
    var video = index > 0 ? api.conf.playlist[index].sources : api.conf.clip.sources;
    video.index = index;
    
    api.load({ sources: video });
    
    //  without this any further HLS playback won't use HLS.js
    api.bind('unload error', function() {
      flowplayer.engines[store_engine_pos] = store_engine;
    });
  });
});