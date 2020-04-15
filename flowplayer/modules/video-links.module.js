/*
* Video Links for sharing
* keywords: hashmark hashtag anchor links
* */
if (typeof (flowplayer) !== "undefined" && typeof(fv_flowplayer_conf) != "undefined"  && fv_flowplayer_conf.video_hash_links ) {
  flowplayer(function (api, root) {
    if( jQuery(root).find('.sharing-link').length > 0 ) {
      api.on('progress',function(e,api){
        if( !api.video.sources || !api.video.sources[0] ) {
          return;
        }

        var hash = fv_player_get_video_link_hash(api);
        var sTime = ',' + fv_player_time_hms(api.video.time);
        //console.log(sTime);
        jQuery('.fvp-sharing>li>a',root).each(function(){
          jQuery(this).attr('href',jQuery(this).attr('href').replace(/%23.*/,'') + '%23' + hash /*+ sTime*/);
        });

        jQuery('.sharing-link',root).attr('href',jQuery('.sharing-link',root).attr('href').replace(/#.*/,'') + '#' + hash + sTime);
      });
      
      jQuery('.sharing-link',root).click( function(e) {

        fv_player_clipboard( jQuery(this).attr('href'), function() {
          e.preventDefault();
          fv_player_notice(root,fv_flowplayer_translations.link_copied,2000);
        });
      })
    }
  })

  jQuery(document).on('click','a[href*="fvp_"]', function() {
    var link = jQuery(this)
    setTimeout( function() {
      if( link.parents('.fvp-share-bar').length == 0 ) fv_autoplay_exec();
    } );
  });

}
