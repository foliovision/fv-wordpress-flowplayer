/**
 * Check if youtube video is still live
 */
flowplayer(function(api, root) {
  root = jQuery(root);

  api.on('ready', function(e,api) {
    if( !api.video.id || api.engine.engineName != 'fvyoutube' || !api.live ) return;

    var data = {
      action: 'fv_player_youtube_live_check',
      nonce: fv_flowplayer_conf.youtube_live_check_nonce,
      video_id: api.video.id
    };

    jQuery.post(fv_player.ajaxurl,data, function( response ) {
      if( !response.is_live ) { // not live anymore
        console.log('Video: ', api.video.id, ' is not live anymore.');
        root.removeClass('is-live');
        root.removeClass('is-live-position');
        root.find('.fp-duration').remove();
        api.live = false;
        api.video.live = false;
      }
    });
  });
});