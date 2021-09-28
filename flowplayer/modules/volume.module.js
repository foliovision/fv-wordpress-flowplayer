/*
 *  Volume control enhancements
 *  
 *  Dragging the volume down to zero mutes the video and shows the un-mute icon.
 *  What this code does is it remembers the last volume before you finished dragging
 *  the volume control and then clicking the un-mute icon restores back to that volume
 * 
 *  Also show overlay notice when the sound is muted at video start - mostly by autoplay
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var bean = flowplayer.bean;
  var restore = -1;
  
  // Restore volume on click
  root.on('click','.fp-volumebtn', function(e) {
    if(api.volumeLevel == 0 && restore != -1) {
      api.volume(restore);
      return false;
    }
  })

  // Click into volume bar and start dragging it down and drag it to very 0, it would remember the initial volume - the one which was there on mousedown
  // Other case is click into the volume bar and drag it from 1 to about 0.5. So 0.5 is remembered on mouseup, then drag it to 0. So clicking the mute icon would restore to 0.5
  // It's not so ideal with touchstart and touchend, it seems to remember some weird volume which user didn't intendt to set, but nothing too critical
  root.on('mousedown touchstart mouseup touchend','.fp-volumebar', function(e) { 
    if ( api.volumeLevel != 0 ) { 
      restore = api.volumeLevel;
    }
  })

  // When muting with the button forget about the volume to restore
  // As otherwise the root.on('click','.fp-volumebtn', ... ) handler above would restore the volume
  root.on('mousedown touchstart','.fp-volumebtn', function(e) {
    if( api.volumeLevel > 0 ) {
      restore = -1;
    }
  });

  // Mute
  api.on('volume', function(e,api) {
    if( root.hasClass('is-mouseover') && !api.muted ) {
      if( api.volumeLevel == 0 ) { // Mute when volume is set to 0 by mouse
        bean.off( flowplayer.support.touch ? root : document, 'mousemove.sld touchmove.sld'); // Stop flowplayer slide api, without this slide event gets incorrect value
        api.mute(true);
      }
    }
  });

  // If video starts muted, show a notice
  api.on('ready', function(e,api) {
    // We wait for the first progress event as api.muted wouldn't be there on iOS
    api.one('progress', function(e,api) {
      if( api.muted || api.volumeLevel == 0 ) {
        var mute_notice = jQuery('<div class="fp-message fp-message-muted fp-shown"><span class="fp-icon fp-volumebtn-notice"></span> '+fv_flowplayer_translations.click_to_unmute+'</div>');

        // We need touchstart for mobile, otherwise click would only show te UI
        mute_notice.on( 'click touchstart', function() {
          api.mute(false);
          api.volume(1);
        });

        root.find('.fp-ui').append( mute_notice );
        root.addClass('has-fp-message-muted');
      }
    } );
  });

  api.on('mute volume', function() {
    if( !api.muted || api.volumeLevel > 0 ) {
      root.removeClass('has-fp-message-muted');
      root.find('.fp-message-muted').remove();
    }
  });

})