 // sticky video
 flowplayer(function(api, root) {
  var $root = jQuery(root);
  var $playerDiv = $root.find('.fp-player');
  var sticky = $root.data("fvsticky");
  var globalSticky = false;
  var videoRatio = $root.data("ratio");
  
  api.is_sticky = false;
  
  if (typeof(videoRatio) == "undefined") {
    videoRatio = 0.5625;
  }
  if (flowplayer.conf.sticky_video == 1 && typeof(sticky) == "undefined") {
    globalSticky = true;
  }
  if (globalSticky || sticky) {
    if (flowplayer.support.firstframe) {
      var stickyPlace = flowplayer.conf.sticky_place;
      var stickyWidth = flowplayer.conf.sticky_width;
      if (stickyWidth == "") {
        stickyWidth = 380;
      }
      var stickyHeight = stickyWidth * videoRatio;
      fv_player_sticky_video();
    } else {
      return;
    }
  }

  function fv_player_sticky_video() {
    var $window = jQuery(window),
      $flowplayerDiv = $root,
      top = $flowplayerDiv.offset().top,
      offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2));

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

        top = $flowplayerDiv.offset().top;
        offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2));
      })
      .on("scroll", function() {
        if( !is_big_enough() ) {
          if( api.is_sticky ) {
            fv_player_sticky_class_remove();
          }
          return;
        }

        top = $flowplayerDiv.offset().top;
        offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2));

        // Is the player loading, or is it the audible player?
        if ($window.scrollTop() > offset && ( api.loading || flowplayer.audible_instance == $root.data('flowplayer-instance-id') ) ) {
          if (jQuery("div.flowplayer.is-unSticky").length > 0) {
            return false;
          } else {
            fv_player_sticky_class_add();
          }
        } else {
          fv_player_sticky_class_remove();
        }
      });
  }

  function fv_player_sticky_class_add() {
    if ($playerDiv.hasClass("is-sticky-" + stickyPlace)) {
      return;
    } else {
      $playerDiv.addClass("is-sticky");
      $playerDiv.addClass("is-sticky-" + stickyPlace);
      if ($root.find("a.fp-sticky").length == 0){
        $root.find('div.fp-header').prepend('<a class="fp-sticky fp-icon"></a>');
      }
      $playerDiv.css("width", stickyWidth);
      $playerDiv.css("height", stickyHeight);
      $playerDiv.css("max-height", stickyHeight);
      
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