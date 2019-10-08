/*
 *  Audio support
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var bean = flowplayer.bean;
  var restore = 0.5 , first_mouseover = true, mute_check = true;
  
  root.on('click','.fp-volume', function() { // Restore volume
    if(api.volumeLevel == 0) {
      api.volume(restore);
    }
  })

  api.on('volume', function(e,api){
    // console.log('Volume', api.volumeLevel,'Restore',restore);
    if( root.hasClass('is-mouseover') && !root.hasClass('is-muted') ) {
      if( api.volumeLevel == 0 && mute_check ) { // Mute when volume is set to 0 by mouse
        api.mute();
      } else if (api.volumeLevel > 0) {
        mute_check = true;
      }

      if ( api.volumeLevel != 0  && first_mouseover ) { // Remember first volume change
        restore = api.volumeLevel;
        first_mouseover = false;
      }
    }
  });
  
  if( root.hasClass('is-audio') ) {
    bean.off(root[0], "mouseenter");
    bean.off(root[0], "mouseleave");
    root.removeClass('is-mouseout');
    root.addClass('fixed-controls').addClass('is-mouseover');
    
    api.on('error', function (e,api, error) {    
      jQuery('.fp-message',root).html( jQuery('.fp-message',root).html().replace(/video/,'audio') );
    });
    
    root.click( function(e) {
      if( !api.ready) {
        e.preventDefault();
        e.stopPropagation();
        api.load();
      }
    })
  }
  
})