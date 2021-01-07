/*
 * Improve the fullscreen calling to make sure the video covers the full visible viewport of Google Pixel 4 or iPhone Pro which have a special viewport shape
 */
flowplayer(function(player, root) {
  // if the fullscreen is not supported do not alter the Flowplayer behavior in any way
  if (!flowplayer.support.fullscreen && player.conf.native_fullscreen && typeof flowplayer.common.createElement('video').webkitEnterFullScreen === 'function') {
    return;
  }

  //  copy of original Flowplayer variable declarations
  var FS_ENTER = "fullscreen",
    FS_EXIT = "fullscreen-exit",
    FS_SUPPORT = flowplayer.support.fullscreen,
    win = window,
    scrollX,
    scrollY;

  //  copy of original Flowplayer function with some subtle changes
  player.fullscreen = function(flag) {
    var wrapper = jQuery(root).find('.fp-player')[0];
    
    if (player.disabled) return;

    if (flag === undefined) flag = !player.isFullscreen;

    if (flag) {
      scrollY = win.scrollY;
      scrollX = win.scrollX;
    }

    if (FS_SUPPORT) {

       if (flag) {
          ['requestFullScreen', 'webkitRequestFullScreen', 'mozRequestFullScreen', 'msRequestFullscreen'].forEach(function(fName) {
             if (typeof wrapper[fName] === 'function') {
                wrapper[fName]({
                  navigationUI: "hide"  // hides the white bar on Google Pixel 4 etc.
                });
                if (fName === 'webkitRequestFullScreen' && !document.webkitFullscreenElement)  {
                   wrapper[fName]();
                }
             }
          });

       } else {
          ['exitFullscreen', 'webkitCancelFullScreen', 'mozCancelFullScreen', 'msExitFullscreen'].forEach(function(fName) {
            if (typeof document[fName] === 'function') {
              document[fName]();                            
            }
          });
       }

    } else {
       player.trigger(flag ? FS_ENTER : FS_EXIT, [player]);
    }
    
    return player;
  };

  player.on('fullscreen-exit', function() {
    win.scrollTo(scrollX, scrollY);    

    /*
    * Core Flowplayer already does try to restore the scroll, but since it has no idea where
    * the scroll position was here's where we fix it. This is required when using
    * CSS scroll-behavior: smooth; in Chrome
    */
    jQuery(window).one('scroll', function() {
      window.scrollTo(scrollX, scrollY);
    });
  });
});