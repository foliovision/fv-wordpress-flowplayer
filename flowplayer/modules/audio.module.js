/*
 *  Audio support
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var bean = flowplayer.bean;

  root.on('click','.fp-volume', function() {
    if(api.volumeLevel == 0) {
      api.volume(0.5);
    }
  })

  api.on('volume', function(e,api){
    if( api.volumeLevel == 0 && root.hasClass('is-mouseover') && !root.hasClass('is-muted') ) {
      api.mute();
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