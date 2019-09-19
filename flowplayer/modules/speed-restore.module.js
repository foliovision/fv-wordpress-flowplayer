/*
 *  Video speed localstorage
 */
flowplayer( function(api,root) {
  api.on('speed', function(ev, _a, rate) {
    try {
      window.localStorage.fv_player_speed = rate;
    } catch(e) {}
  });

  api.on('ready', function() {
    if ( jQuery(root).find('strong.fp-speed').is(":visible") && window.localStorage.fv_player_speed ) {
      api.speed(parseFloat(window.localStorage.fv_player_speed));
    } 
  });

});