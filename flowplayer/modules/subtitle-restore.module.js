/*
 *  Remember subtitle selection in localstorage
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  // restore subtitle on ready event
  api.on('ready', function(e,api,video) {
    if( root.find('strong.fp-cc').is(":visible") ) {
      if( window.localStorage.fv_player_subtitle && api.video.subtitles.length ) {
        if ( window.localStorage.fv_player_subtitle === 'none' ) {
          setTimeout(function() { // core flowplayer picks default subtitle, setTimeout is needed to prevent it
            api.disableSubtitles();
          },0);
        } else {
          api.video.subtitles.forEach(function (item, index) {
            if( item.srclang === window.localStorage.fv_player_subtitle)  {
              api.loadSubtitles(index); // restore saved subtitle
            }
          });
        }
      }
    }

    // subtitle menu click
    root.find('.fp-subtitle-menu').on('click', function(e) {
      if( typeof(e.target.getAttribute('data-subtitle-index')) == 'string' ) {
        try {
          if( e.target.getAttribute('data-subtitle-index') > -1  ) {
            window.localStorage.fv_player_subtitle = api.video.subtitles[e.target.getAttribute('data-subtitle-index')].srclang; // save lang shortcut to localstorage
          } else if( e.target.getAttribute('data-subtitle-index') == -1 ) {
            window.localStorage.fv_player_subtitle = 'none'; // store none value to disable subtiles on ready event
          }
        } catch(e) {}
      }
    });

  });
});