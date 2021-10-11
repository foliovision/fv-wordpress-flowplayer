/*
 *  Make sure Airplay is disabled if the video type is not supported
 */
flowplayer( function(api,root) {
  api.on( 'ready', function(e,api,video) {
    api.one( 'progress', function() {
      jQuery(root).find('.fp-airplay').toggle( api.engine.engineName == 'html5' );
    });
  })
});