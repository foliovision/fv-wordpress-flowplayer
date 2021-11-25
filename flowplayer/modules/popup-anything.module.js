/*
Compatibility with https://wordpress.org/plugins/popup-anything-on-click/
*/
document.addEventListener('custombox:overlay:close', function(e) { // popup-anything closed, using Custombox library
  console.log( 'FV Player: Custombox/Popup anything ligtbox closed');

  var $players = jQuery(this).find('.flowplayer'); // find players in the popup

  if( $players.length == 0 ) return; // no players in popup

  console.log( 'FV Player: Custombox/Popup anything ligtbox contains a player');

  $players.each(function(index,player) { 
    var api = jQuery(player).data("flowplayer");

    if( typeof(api) != "undefined") {
      if( api.playing ) { // pause if playing
        console.log( 'FV Player: Custombox/Popup anything ligtbox video pause');
        api.pause(); 
      } else if( api.loading ) { // uload if still loading
        api.one('ready', function() {
          console.log( 'FV Player: Custombox/Popup anything ligtbox video unload');
          api.unload();
        })
      }
    }
  });
  
});