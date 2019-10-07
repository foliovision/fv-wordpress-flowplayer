/*
 *  Remember subtitle selection in localstorage
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  api.on('ready', function(e,api,video) {
    if ( window.localStorage.fv_player_subtitle && root.find('strong.fp-cc').is(":visible") ) {
      if(api.video.subtitles.length){
        api.video.subtitles.forEach(function (item, index) {
          if(item.srclang === window.localStorage.fv_player_subtitle) {
            api.loadSubtitles(index);
          }
        });
      }
    }

    root.find('.fp-subtitle-menu').click( function(e) {
      if(typeof(e.target.getAttribute('data-subtitle-index')) == 'string' && e.target.getAttribute('data-subtitle-index').length) {
        try {
          window.localStorage.fv_player_subtitle = api.video.subtitles[e.target.getAttribute('data-subtitle-index')].srclang;
        } catch(e) {}
      }
    });
  });
});