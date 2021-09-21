/*
 *  Remember subtitle selection in localstorage
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  var ls = window.localStorage;

  // restore subtitle on ready event
  api.on('ready', function(e,api,video) {
    if( root.find('strong.fp-cc').is(":visible") ) {
      if( ls.fv_player_subtitle && api.video.subtitles.length ) {
        api.video.subtitles.forEach(function (item, index) {
          if( item.srclang === ls.fv_player_subtitle)  {
            api.loadSubtitles(index);
          } else if ( ls.fv_player_subtitle === 'none' ) {
            setTimeout(function() { // core flowplayer picks default subtitle, setTimeout is needed to prevent it
              api.disableSubtitles();
            },0);
          }
        });
      }
    }

    // subtitle menu click
    root.find('.fp-subtitle-menu').on('click', function(e) {
      var subtitle_index = e.target.getAttribute('data-subtitle-index');
      if( typeof(subtitle_index) == 'string' ) {
        try {
          ls.fv_player_subtitle = subtitle_index > -1 ? api.video.subtitles[subtitle_index].srclang : 'none'; // save lang shortcut to localstorage
        } catch(e) {}
      }
    });

  });
});