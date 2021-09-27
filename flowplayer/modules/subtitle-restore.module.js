/*
 *  Remember subtitle selection in localstorage
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  var ls = window.localStorage;

  // restore subtitle on ready event
  api.on('ready', function(e,api,video) {
    if( root.find('strong.fp-cc').is(":visible") ) {
      if( ls.fv_player_subtitle && api.video.subtitles.length ) { // check if we have subtitles to restore
        if ( ls.fv_player_subtitle === 'none' ) { // none is saved, disable subtitles
          api.disableSubtitles();
        } else {
          api.video.subtitles.forEach(function (item, index) {
            if( item.srclang === ls.fv_player_subtitle) {
              api.loadSubtitles(index); // restore saved subtitle
            }
          });
        }
      } else { // no subtitles saved, pick default
        var defaultSubtitle = video.subtitles.filter(function(one) {
          return one['fv_default'];
        })[0];

        if (defaultSubtitle) {
          api.loadSubtitles(video.subtitles.indexOf(defaultSubtitle));
        } 
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