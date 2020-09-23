/*
 *  Volume control enhancements
 *  
 *  Dragging the volume down to zero mutes the video and shows the un-mute icon.
 *  What this code does is it remembers the last volume before you finished dragging
 *  the volume control and then clicking the un-mute icon restores back to that volume
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

})