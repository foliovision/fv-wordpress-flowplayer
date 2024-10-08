/*
 * Improve the fullscreen calling to make sure the video covers the full visible viewport of Google Pixel 4 or iPhone Pro which have a special viewport shape
 */
flowplayer(function(player, root) {

  //  copy of original Flowplayer variable declarations
  var FS_ENTER = "fullscreen",
    FS_EXIT = "fullscreen-exit",
    FS_SUPPORT = flowplayer.support.fullscreen,
    win = window,
    scrollX,
    scrollY,
    bean = flowplayer.bean;

  //  copy of original Flowplayer function with some subtle changes
  player.fullscreen = function(flag) {
    if ( player.disabled || jQuery(root).data('fullscreen') == false ) return;

    if (flag === undefined) flag = !player.isFullscreen;

    if (flag) {
      scrollY = win.scrollY;
      scrollX = win.scrollX;
      console.log( 'scrollY', scrollY );
    }

    var video = common.find('video.fp-engine', root)[0];
    if( flowplayer.conf.native_fullscreen && video && flowplayer.support.iOS /* TODO: Also allow Android, it's missing controls on the video unfortunately */ ) {
      // Taking from core Flowplayer /lib/ext/mobile.js
      player.trigger( FS_ENTER, [player]);

      bean.on(document, 'webkitfullscreenchange.nativefullscreen', function() {
        if (document.webkitFullscreenElement !== video) return;

        bean.off(document, '.nativefullscreen');
        bean.on(document, 'webkitfullscreenchange.nativefullscreen', function() {
          if (document.webkitFullscreenElement) return;

          bean.off(document, '.nativefullscreen');
          player.trigger( FS_EXIT, [player]);
        });
      });

      try {
        video.webkitEnterFullScreen();
      } catch( e ) {
        // If the fullscreen was blocked, pause the video and show the play button
        // Then tapping the video will try fullscreen again
        player.pause();

        common.find('.fp-play', root)[0].style.opacity = 1;

        jQuery( root ).on( 'touchstart', function(e) {
          common.find('.fp-play', root)[0].style.opacity = '';

          player.resume();
          video.webkitEnterFullScreen();
          return false;
        });
      }

      bean.one(video, 'webkitendfullscreen', function() {
        bean.off(document, 'fullscreenchange.nativefullscreen');
        player.trigger( FS_EXIT, [player]);
        common.prop(video, 'controls', true);
        common.prop(video, 'controls', false);
      });

      return;
    }

    var wrapper = jQuery(root).find('.fp-player')[0];

    /**
     * If we are entering fullscreen on Safari or iPad and a fullscreen element is already
     * present, we just do the CSS fullscreen.
     * We allow this so that you can put <body> to fullscreen before the FV Player starts
     * loading. You need to do that if FV Player HTML load with Ajax. Ajax request cannot
     * initiate fullscreen in Safari.
     */
    if ( flowplayer.support.browser.safari && flowplayer.support.fullscreen && flag && document.fullscreenElement ) {
      FS_SUPPORT = false;

      // Exit the fullscreen should discard the CSS fullscreen of player too
      document.addEventListener("fullscreenchange", function(e) {
        flowplayer( '.is-fullscreen' ).trigger( FS_EXIT );
      });
    }

    if (FS_SUPPORT ) {

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


  //  copy of original Flowplayer variable declarations and FS events
  var lastClick, common = flowplayer.common;

  player.on("mousedown.fs", function() {
    if (+new Date() - lastClick < 150 && player.ready) player.fullscreen();
    lastClick = +new Date();
  });

  player.on(FS_ENTER, function() {
      common.addClass(root, 'is-fullscreen');
      common.toggleClass(root, 'fp-minimal-fullscreen', common.hasClass(root, 'fp-minimal'));
      common.removeClass(root, 'fp-minimal');

      common.addClass( document.body, 'has-fv-player-fullscreen' );

      /**
       * If fullscreen is not supported we have add all the extra CSS which helps us to make
       * the player fullscreen.
       *
       * Also do this if you support fullscreen but some other element is in fullscreen.
       */
      if (!FS_SUPPORT || document.fullscreenElement) {
        common.css(root, 'position', 'fixed');

        sanitize_parent_elements(true);
      }

      player.isFullscreen = true;

   }).on(FS_EXIT, function() {
      var oldOpacity;
      common.toggleClass(root, 'fp-minimal', common.hasClass(root, 'fp-minimal-fullscreen'));
      common.removeClass(root, 'fp-minimal-fullscreen');

      /**
       * If we support fullscreen but some other element is in fullscreen, we have to make
       * sure to remove all the extra CSS which helped us to make the player fullscreen
       */
      var fs_support = FS_SUPPORT && jQuery(root).find('.fp-player')[0] == document.fullscreenElement;

      if ( !fs_support && player.engine === "html5") {
        oldOpacity = root.css('opacity') || '';
        common.css(root, 'opacity', 0);
      }
      if (!fs_support ) {
        common.css(root, 'position', '');

        sanitize_parent_elements(false);
      }

      common.removeClass(root, 'is-fullscreen');

      common.removeClass( document.body, 'has-fv-player-fullscreen' );

      if (!fs_support && player.engine === "html5") setTimeout(function() { root.css('opacity', oldOpacity); });
      player.isFullscreen = false;

      if( player.engine.engineName != 'fvyoutube' ){ // youtube scroll ignore
        win.scrollTo(scrollX, scrollY);
      }
   }).on('unload', function() {
     if (player.isFullscreen) player.fullscreen();
   });

   player.on('shutdown', function() {
     FULL_PLAYER = null;
     common.removeNode(wrapper);
   });

   // Avoid dragging by the edge of the window which acts as the back button on iPhones without home button
   if( flowplayer.support.iOS ) {
      root.querySelector('.fp-player').addEventListener('touchstart', function(e) {
         if( player.isFullscreen && e.pageX ) {
            if (e.pageX > 16 && e.pageX < window.innerWidth - 16) return;

            e.preventDefault();
         }
      });
   }

   /*
    * iPhone fullscreen is CSS-based and it can't work if the parent elements use CSS transform
    * So we get rid of these rules even entering fullscreen and put them back when leaving
    * We also reset the z-index as with that the fixed position elements would appear on top of the video
    */
   function sanitize_parent_elements( add ) {
     var parent = root;
     while (parent) {
       try {
         var styles = getComputedStyle(parent);
         if( styles.transform ) {
           parent.style.transform = add ? 'none' : '';
         }
         if( styles.zIndex ) {
          parent.style.zIndex = add ? 'auto' : '';
        }
       } catch(e) {}
       parent = parent.parentNode;
     }
   }
});
