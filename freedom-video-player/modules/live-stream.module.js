/*
 *  Live stream errors
 */
flowplayer(function (api, root) {
  api.bind("load", function (e,api,data) {
    var player = jQuery(e.currentTarget);
    if( player.data('live') ){
      var live_check = setTimeout( function() {
        player.find('.fp-ui').append('<div class="fp-message">'+fv_flowplayer_translations.live_stream_failed+'</div>');
        player.addClass('is-error');
      }, 10000 );
      jQuery(e.currentTarget).data('live_check', live_check);
    }
  }).bind("ready", function (e,api,data) {
    clearInterval( jQuery(e.currentTarget).data('live_check') );
  }).bind("error", function (e,api,data) {
    var player = jQuery(e.currentTarget);
    if( player.data('live') ){
      player.find('.fp-message').html(fv_flowplayer_translations.live_stream_failed_2);
    }
  });
});