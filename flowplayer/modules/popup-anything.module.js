document.addEventListener('custombox:overlay:close', function(e) { // popup-anything closed
  $players = jQuery(this).find('.flowplayer'); // find players in the popup

  if( $players.length == 0 ) return; // no players in popup

  $players.each(function(index,player) { 
    var api = jQuery(player).data("flowplayer");

    if( typeof(api) != "undefined") {
      if( api.playing ) { // pause if playing
        api.pause(); 
      } else if( api.loading ) { // uload if still loading
        api.one('ready', function() {
          api.unload();
        })
      }
    }
  });
  
});