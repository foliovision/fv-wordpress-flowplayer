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
    if ( window.localStorage.fv_player_speed && jQuery(root).find('strong.fp-speed').is(":visible") ) {
      api.speed(parseFloat(window.localStorage.fv_player_speed));
    }

    if( jQuery(root).data('volume') == 0 ) {
      api.mute(true,true);
    }
  });

});