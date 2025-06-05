/*
 *  Show overlay notice when the sound is muted at video start - mostly by autoplay
 */
flowplayer(function(api, root) {
  root = jQuery(root);

  // If video starts muted, show a notice
  var deal_with_muted_start = false;

  // We only set this on the first ready event - meaning it only show for first item in playlist
  api.one('ready', function(e,api) {
    if( root.hasClass('is-audio') ) return;

    deal_with_muted_start = true;
  });

  // We wait for the first second of the video to not show this of video with the custom start time
  // as we mute the video in that case too to avoid sound glitch
  api.on('progress', function(e,api,time) {
    // And we remember that we already did the check, so the part below only runs once for each video    
    if( deal_with_muted_start && time > 1 ) {
      deal_with_muted_start = false;

      // Do not use for videos without audio track
      var video = jQuery('root').find('video');
      if( video.length && !hasAudio(video[0]) ) {
        return;
      }

      if( api.muted || api.volumeLevel == 0 ) {
        // Did user mute the video on purpose?
        if( localStorage.muted == 'true' || localStorage.volume == '0' ) {
          return;
        }

        var mute_notice = jQuery('<div class="fp-message fp-message-muted"><span class="fp-icon fp-volumebtn-notice"></span> '+fv_flowplayer_translations.click_to_unmute+'</div>');

        // We need touchstart for mobile, otherwise click would only show te UI
        freedomplayer.bean.on( mute_notice[0], 'click touchstart', function() {
          api.mute(false);
          api.volume(1);
        });

        root.find('.fp-ui').append( mute_notice );
        root.addClass('has-fp-message-muted');

        // Remove the notice after a while
        setTimeout( remove_volume_notice, 10000 );
      }
    }
  } );

  api.on('mute volume', function() {
    if( !api.muted || api.volumeLevel > 0 ) {
      remove_volume_notice();
    }
  });

  function remove_volume_notice() {
    root.removeClass('has-fp-message-muted');
    root.find('.fp-message-muted').remove();
  }

  function hasAudio(video) {
    return video.mozHasAudio ||
    Boolean(video.webkitAudioDecodedByteCount) ||
    Boolean(video.audioTracks && video.audioTracks.length);
  }

})