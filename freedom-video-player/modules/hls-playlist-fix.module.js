/*
 *  HLS.js - prevent from loading when engine changed in playlist
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var hlsjs;

  flowplayer.engine('hlsjs-lite').plugin(function(params) {
    hlsjs = params.hls;
  });
  
  api.on('ready', function(e,api) {
    // engine changed, destroy hlsjs
    if( hlsjs && api.conf.playlist.length && api.engine.engineName != 'hlsjs-lite' ) {
      hlsjs.destroy();
    }
  });

});