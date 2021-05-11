document.addEventListener('custombox:overlay:close', function(e) {
  $players = jQuery(this).find('.flowplayer');

  $players.each(function(index,player){
    var api = jQuery(player).data("flowplayer");
    if( typeof(api) != "undefined") {
      if( api.playing ) api.pause();
    }
  });
  
});