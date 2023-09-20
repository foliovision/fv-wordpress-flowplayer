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

  api.on("ready", function (e,api,video) {

    if( video.type == 'video/youtube' ) {
      root.addClass('is-youtube');

      if( typeof fv_flowplayer_conf.youtube_browser_chrome != 'undefined' ) {


        var yt_title = video.fv_title;

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
          var youtube_icon = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy" width="24" height="24" viewBox="0 0 24 24"\ stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">\
          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>\
          <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"></path>\
          <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"></path>\
          </svg>';

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
