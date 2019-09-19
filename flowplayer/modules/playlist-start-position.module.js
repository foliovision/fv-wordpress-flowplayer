flowplayer( function(api,root) {

  var
    $root = jQuery(root),
    start_index = $root.data('playlist_start');

  if( typeof(start_index) == 'undefined' ) return; 

  function start_position_changer() {  
    if ($root.data('position_changed') !== 1 && api.conf.playlist.length) {      
      start_index--; // the index should start from 0
      api.play(start_index);
      $root.data('position_changed', 1);
    }
  }

  api.bind('unload', function() {
    start_index = $root.data('playlist_start');
    $root.removeData('position_changed');
    api.one('ready', start_position_changer);
    api.video.index = 0;
  });

  api.one('ready', start_position_changer);

  jQuery(".fp-ui", root).on('click', function() {
    start_position_changer();
    $root.data('position_changed', 1);
  });

});