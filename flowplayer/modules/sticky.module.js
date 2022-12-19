 // sticky video
 flowplayer(function(api, root) {
  var $root = jQuery(root),
    $playerDiv = $root.find('.fp-player'),
    sticky = $root.data("fvsticky"),
    globalSticky = false,
    videoRatio = $root.data("ratio");

  api.is_sticky = false;

  if (typeof(videoRatio) == "undefined") {
    videoRatio = 0.5625;
  }
  if (flowplayer.conf.sticky_video == 1 && typeof(sticky) == "undefined") {
    globalSticky = true;
  }
  if (globalSticky || sticky) {
    if (flowplayer.support.firstframe) {
      var stickyPlace = flowplayer.conf.sticky_place,
        stickyWidth = flowplayer.conf.sticky_width;

      if (stickyWidth == "") {
        stickyWidth = 380;
      }

      var stickyHeight = stickyWidth * videoRatio;
      fv_player_sticky_video();
    } else {
      return;
    }
  }

  function fv_player_is_in_viewport(el) {
    var rect = el.getBoundingClientRect();
    return (
      rect.top >= 0 - (jQuery(el).outerHeight() / 2) &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + (jQuery(el).outerHeight() / 2) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
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
        if( !is_big_enough() ) {
          if( api.is_sticky ) {
            fv_player_sticky_class_remove();
          }
          return;
        }

      })
      .on("scroll", function() {
        if( !is_big_enough() ) {
          if( api.is_sticky ) {
            fv_player_sticky_class_remove();
          }
          return;
        }

        // Not in viewport and the player loading, or it is the audible player
        if ( !fv_player_is_in_viewport($flowplayerDiv[0]) && ( api.playing || api.loading || flowplayer.audible_instance == $root.data('freedomplayer-instance-id') ) ) {
          if (jQuery("div.flowplayer.is-unSticky").length > 0) { // Sticky already added
            return false;
          } else {
            fv_player_sticky_class_add(); // Add sticky
          }
        } else {
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

      $playerDiv.css("width", stickyWidth);
      $playerDiv.css("height", stickyHeight);
      $playerDiv.css("max-height", stickyHeight);

      sanitize_parent_elements(true);

      api.is_sticky = true;
      api.trigger( 'sticky', [ api ] );
    }
    $playerDiv.parent(".flowplayer").addClass("is-stickable");
  }

  function fv_player_sticky_class_remove() {
    $playerDiv.removeClass("is-sticky");
    $playerDiv.removeClass("is-sticky-" + stickyPlace);
    $playerDiv.css("width", "");
    $playerDiv.css("height", "");
    $playerDiv.css("max-height", "");
    $playerDiv.parent(".flowplayer").removeClass("is-stickable");

    if( api.is_sticky ) {
      sanitize_parent_elements();

      api.is_sticky = false;
      api.trigger( 'sticky-exit', [ api ] );
    }
  }

  function is_big_enough() {
    return jQuery(window).innerWidth() >= fv_flowplayer_conf.sticky_min_width;
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
   */
  function sanitize_parent_elements( add ) {
    var parent = root;
    while (parent) {
      try {
        var styles = getComputedStyle(parent);
        if( styles.transform ) {
          parent.style.transform = add ? 'none' : '';
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
    $playerDiv.removeClass("is-sticky");
    $playerDiv.removeClass("is-sticky-right-bottom");
    $playerDiv.removeClass("is-sticky-left-bottom");
    $playerDiv.removeClass("is-sticky-right-top");
    $playerDiv.removeClass("is-sticky-left-top");
    $playerDiv.css("width", "");
    $playerDiv.css("height", "");
    $playerDiv.css("max-height", "");
    
    if( api.is_sticky ) {
      api.is_sticky = false;
      api.trigger( 'sticky-exit', [ api ] );
    }
  });
  $(document).on('click', "div.flowplayer.is-unSticky", function() {
    $("div.flowplayer").removeClass("is-unSticky");
  });
});