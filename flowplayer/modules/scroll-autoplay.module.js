if( typeof(flowplayer) !== 'undefined') {
  var fv_autoplay_type = fv_flowplayer_conf.autoplay_preload,
    fv_player_scroll_autoplay = false;

  jQuery(document).ready( function() {
    fv_player_scroll_autoplay = true;
  } );

  jQuery(window).on( 'scroll', function() {
    fv_player_scroll_autoplay = true;
  } );

  var fv_player_scroll_int = setInterval( function() {
    if( !fv_player_scroll_autoplay ) {
      return;
    }

    var i = 0,
      iMin = jQuery(window).scrollTop() + jQuery(window).height() / 4,
      iMax = jQuery(window).scrollTop() + 3 * jQuery(window).height() / 4;

    jQuery('.flowplayer:not(.is-disabled)').each( function(k,v) {
      var root = jQuery(this);

      if( typeof root.data('fvautoplay') != 'undefined' && root.data('fvautoplay') == -1 ) {
        return;
      }

      if( jQuery('body').hasClass('wp-admin') ) return;

      var api = root.data('flowplayer'),
        autoplay = root.attr('data-fvautoplay');

      if( fv_autoplay_type == 'viewport' || fv_autoplay_type == 'sticky' ) { // play video when on viewport or sticky
        var iPlayer = root.offset().top + root.height() / 2;

        // looks for a single player which is in the middle of screen
        // and it also has to be further down than the currently playing player
        // ...if the conservative mode is on
        if( i == 0 && iPlayer > iMin && iPlayer < iMax ) {
          // disabling for YouTube on iOS
          if( flowplayer.support.iOS && api.conf.clip.sources[0].type == 'video/youtube' ) {
            return;
          }

          if( !api ) {
            console.log('Scroll autoplay: Play ' + root.attr('id'));
            i++;
            fv_player_load( root );

          } else if( api.ready && api.paused ) {
            console.log('Scroll autoplay: Resume ' + root.attr('id'));
            i++;
            api.resume();

          } else if( !api.loading && !api.playing && !api.error ) {
            console.log('Scroll autoplay: Play again ' + root.attr('id'));
            i++;
            api.load();

            if( autoplay == 'muted' ) {
              console.log('Scroll autoplay: mute!');
              api.mute(true,true);
            }

          }
        } else {
          if( api && api.playing && fv_autoplay_type == 'viewport' ) {
            console.log('Scroll autoplay: Player not in viewport, pausing ' + root.attr('id'));
            api.pause();
          }
        }
      }
    });
    fv_player_scroll_autoplay = false;
  }, 200 );
}
