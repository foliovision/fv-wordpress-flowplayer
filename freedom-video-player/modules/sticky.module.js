 // sticky video
flowplayer(function(api, root) {
  var $root = jQuery(root),
    $playerDiv = $root.find('.fp-player'),
    sticky = $root.data("fvsticky"),
    globalSticky = false,
    videoRatio = $root.find(".fp-ratio");

  api.is_sticky = false;

  if ( flowplayer.conf.sticky_video && flowplayer.conf.sticky_video != 'off' && typeof(sticky) == "undefined") {
    globalSticky = true;
  }
  if (globalSticky || sticky) {
    if (flowplayer.support.firstframe) {
      var stickyPlace = flowplayer.conf.sticky_place;

      fv_player_sticky_video();
    } else {
      return;
    }
  }

  function fv_player_is_in_viewport(el) {
    var rect = el.getBoundingClientRect(),
      height = window.innerHeight;

    var top = height - rect.top > el.clientHeight / 2,
      bottom = rect.bottom > el.clientHeight / 4,
      left = rect.left >= 0,
      right = rect.right / 2 <= (window.innerWidth || document.documentElement.clientWidth);

    return (
      top &&
      bottom &&
      left &&
      right
    );
  }

  function fv_player_sticky_video() {
    var $window = jQuery(window),
      $flowplayerDiv = $root;

    api.on('unload', function() {
      fv_player_sticky_class_remove();
      $root.removeClass("is-unSticky");
    });

     $window
      .on("resize", function() {
        if( !is_big_enough() && flowplayer.conf.sticky_video != 'all' ) {
          if( api.is_sticky ) {
            fv_player_sticky_class_remove();
          }
          return;
        }

      })
      .on("scroll", function() {
        if( !is_big_enough() && flowplayer.conf.sticky_video != 'all' ) {
          if( api.is_sticky ) {
            fv_player_sticky_class_remove();
          }
          return;
        }

        // Not in viewport and the player loading, or it is the audible player
        if (
          ! fv_player_is_in_viewport( $flowplayerDiv[0] ) && (
            api.playing && flowplayer.audible_instance == $root.data('freedomplayer-instance-id') ||
            api.loading && ! api.sticky_exclude ||
            typeof root.fv_player_vast == 'object' &&
            root.fv_player_vast.adsManager_ &&
            typeof root.fv_player_vast.adsManager_.getRemainingTime == 'function' &&
            root.fv_player_vast.adsManager_.getRemainingTime() > 0
          )
        ) {
          if (jQuery("div.flowplayer.is-unSticky").length > 0) { // Sticky already added
            return false;
          } else {

            if ( ! api.is_sticky ) {
              fv_player_log( 'FV Player Sticky: Enable for: ' + $root.data( 'freedomplayer-instance-id' ) );
            } 

            fv_player_sticky_class_add(); // Add sticky
          }
        } else {
          if ( api.is_sticky ) {
            fv_player_log( 'FV Player Sticky: Disable for: ' + $root.data( 'freedomplayer-instance-id' ) );
          }

          fv_player_sticky_class_remove(); // Remove sticky
        }
      });
  }

  function fv_player_sticky_class_add() {
    if ($playerDiv.hasClass("is-sticky-" + stickyPlace)) {
      return;
    } else {
      $playerDiv.addClass("is-sticky");
      $playerDiv.addClass("is-sticky-" + stickyPlace);
      
      if ($root.find("a.fp-sticky").length == 0) {
        $root.find('div.fp-header').prepend('<a class="fp-sticky fp-icon"></a>');
      }

      $playerDiv.prepend( videoRatio.clone() );

      sanitize_parent_elements(true);

      api.is_sticky = true;
      api.trigger( 'sticky', [ api ] );
    }
    $playerDiv.parent(".flowplayer").addClass("is-stickable");
  }

  function fv_player_sticky_class_remove() {
    $playerDiv.removeClass("is-sticky");
    $playerDiv.removeClass("is-sticky-" + stickyPlace);
    $playerDiv.css("max-width", "");
    $playerDiv.find( '.fp-ratio' ).remove();
    $playerDiv.parent(".flowplayer").removeClass("is-stickable");

    if( api.is_sticky ) {
      sanitize_parent_elements();

      api.is_sticky = false;
      api.trigger( 'sticky-exit', [ api ] );
    }
  }

  function is_big_enough() {
    return api.autoplayed || jQuery(window).innerWidth() >= fv_flowplayer_conf.sticky_min_width;
  }
  
  api.sticky = function( flag, remember ) {
    if( typeof(flag) == "undefined" ) {
      flag = !api.is_sticky;
    }

    if( remember ) {
      $root.toggleClass("is-unSticky", !flag );
    }

    if( flag ) {
      fv_player_sticky_class_add();
    } else {
      fv_player_sticky_class_remove();
    }
  }

  /* 
   * Video will not be sticky if one of the parent elements it using CSS transform,
   * so we get rid of it here. We put it back too!
   *
   * We also reset the z-index as with that the fixed position elements would appear on top of the video.
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

jQuery(function($) {
  $(document).on('click', "a.fp-sticky", function() {
    var root = $("div.flowplayer.is-stickable"),
      api = root.data('flowplayer');

    root.addClass("is-unSticky");

    var $playerDiv = root.find('.fp-player');
    $playerDiv
      .removeClass([
        'is-sticky',
        'is-sticky-right-bottom',
        'is-sticky-left-bottom', 
        'is-sticky-right-top',
        'is-sticky-left-top'
      ])
      .css({
        width: '',
        height: '',
        maxHeight: ''
      });
    
    if( api.is_sticky ) {
      api.is_sticky = false;
      api.trigger( 'sticky-exit', [ api ] );
    }

    // Closing sticky player which was autoplaying should pause the video
    if( api.autoplayed ) {
      api.pause();
    }
  });
  $(document).on('click', "div.flowplayer.is-unSticky", function() {
    $("div.flowplayer").removeClass("is-unSticky");
  });
});