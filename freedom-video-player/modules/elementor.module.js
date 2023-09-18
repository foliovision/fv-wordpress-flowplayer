if( location.href.match(/elementor-preview=/) ) {
  console.log('FV Player: Elementor editor is active');
  setInterval( fv_player_load, 1000 );
}