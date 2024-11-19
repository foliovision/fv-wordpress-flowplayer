flowplayer( function(api,root) {

  var
    $root = jQuery(root),
    start_index = $root.data('playlist_start');

  if( typeof(start_index) == 'undefined' ) return;

  function start_position_changer() {
    if ($root.data('position_changed') !== 1 && api.conf.playlist.length) {
      start_index--; // the index should start from 0
      // Do not go to the playlist item if it's ad
      // TODO: Have it pick the previous video
      if( typeof(api.conf.playlist[start_index].click) == "undefined" ) {

        // In case of HLS.js this flag is present and the api.play() which uses api.load() does not execute
        if( api.engine && api.engine.engineName == 'hlsjs-lite' ) {
          api.loading = false;
        }

        api.play(start_index);
      }
      $root.data('position_changed', 1);
    }
  }

  api.bind('unload', function() {
    start_index = $root.data('playlist_start');
    $root.removeData('position_changed');
    api.one( api.conf.poster ? 'resume' : 'ready', start_position_changer);
  });

  api.one( api.conf.poster ? 'resume' : 'ready', start_position_changer);

  jQuery(".fp-ui", root).on('click', function() {
    start_position_changer();
    $root.data('position_changed', 1);
  });

});
