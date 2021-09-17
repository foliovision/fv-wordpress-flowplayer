/*
 *  Remember subtitle selection in localstorage
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  // restore subtitle on ready event
  api.on('ready', function(e,api,video) {

    if( root.find('strong.fp-cc').is(":visible") ) {
      if ( window.localStorage.fv_player_subtitle  ) {
        if(api.video.subtitles.length){
          api.video.subtitles.forEach(function (item, index) {
            if(item.srclang === window.localStorage.fv_player_subtitle) {
              api.loadSubtitles(index);
            }
          });
        }
      } else if( typeof window.localStorage.fv_player_subtitle == 'undefined' ) {
        setTimeout(function() { // prevent default subtitle pick
          api.disableSubtitles();
        },0);
      }
    }

    root.find('.fp-subtitle-menu').on('click', function(e) {
      if( typeof(e.target.getAttribute('data-subtitle-index')) == 'string' ) {
        try {
          if( e.target.getAttribute('data-subtitle-index') > -1  ) {
            window.localStorage.fv_player_subtitle = api.video.subtitles[e.target.getAttribute('data-subtitle-index')].srclang; // save lang shortcut to localstorage
          } else if( e.target.getAttribute('data-subtitle-index') == -1 ) {
            delete window.localStorage.fv_player_subtitle; // no subtitles selected, delete whats saved in localstorage
          }
        } catch(e) {}
      }
    });

  });
});