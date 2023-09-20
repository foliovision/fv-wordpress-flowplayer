/**
 * Youtube module
 */

flowplayer(function (api, root) {
  root = jQuery(root);

  var is_youtube = false;
  jQuery(api.conf.playlist).each( function(k,v) {
    if( v.sources[0].type.match(/youtube/) ) is_youtube = true;
  });

  if( is_youtube ) {

    root.addClass('is-youtube');
    if( typeof fv_flowplayer_conf.youtube_browser_chrome != 'undefined' && fv_flowplayer_conf.youtube_browser_chrome == 'none' ) {
      root.addClass('is-youtube-nl');
    }
  }

  // add or remove youtube class based on video type & settings
  api.on("ready", function (e,api,video) {

    if( video.type == 'video/youtube' ) {
      root.addClass('is-youtube');

      if( typeof fv_flowplayer_conf.youtube_browser_chrome != 'undefined' ) {

        // no logo
        if( fv_flowplayer_conf.youtube_browser_chrome == 'none' ) {
          root.addClass('is-youtube-nl');
        }

        // standart
        if( fv_flowplayer_conf.youtube_browser_chrome == 'standart' ) {
          root.addClass('is-youtube-standart');
        }

        // reduced
        if( fv_flowplayer_conf.youtube_browser_chrome == 'reduced' ) {
          root.addClass('is-youtube-reduced');
        }

      }
    } else {
      root.removeClass('is-youtube');
      root.removeClass('is-youtube-nl');
      root.removeClass('is-youtube-standart');
      root.removeClass('is-youtube-reduced');
    }
  });
});
