 // sticky video
 flowplayer(function(api, root) {
  var $root = jQuery(root);
  var $playerDiv = $root.find('.fp-player');
  var sticky = $root.data("fvsticky");
  var globalSticky = false;
  var videoRatio = $root.data("ratio"),
    is_sticky = false;
  
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
    var change = false;
    var $window = jQuery(window),
      $flowplayerDiv = $root,
      top = $flowplayerDiv.offset().top,
      offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2));
    
    api.on('ready', function() {
      change = true;
    });
    api.on('progress', function() {
         change = true;
     });
    api.on('unload', function() {
      change = false;
      fv_player_sticky_class_remove();
      $root.removeClass("is-unSticky");
    });

     $window
      .on("resize", function() {
        if( !is_big_enough() ) {
          if( is_sticky ) {
            fv_player_sticky_class_remove();
          }
          return;
        }

        top = $flowplayerDiv.offset().top;
        offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2));
      })
      .on("scroll", function() {
        if( !is_big_enough() ) {
          if( is_sticky ) {
            fv_player_sticky_class_remove();
          }
          return;
        }

        top = $flowplayerDiv.offset().top;
        offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2)); 
        if ($window.scrollTop() > offset && change) {
          if (jQuery("div.flowplayer.is-unSticky").length > 0) {
            console.log('unSticky', jQuery("div.flowplayer.is-unSticky").length);
            return false;
          } else {
            fv_player_sticky_class_add();
          }
        } else {
          fv_player_sticky_class_remove();
          change = false;
        }
      });
  }

  function fv_player_sticky_class_add() {
    if ($playerDiv.hasClass("is-sticky-" + stickyPlace)) {
      return;
    } else {
      $playerDiv.addClass("is-sticky-" + stickyPlace);
      if ($root.find("a.fp-sticky").length == 0){
        $root.find('div.fp-header').prepend('<a class="fp-sticky fp-icon"></a>');
      }
      $playerDiv.css("width", stickyWidth);
      $playerDiv.css("height", stickyHeight);
      $playerDiv.css("max-height", stickyHeight);
      
      is_sticky = true;
      api.trigger( 'sticky', [ api ] );
    }
    $playerDiv.parent(".flowplayer").addClass("is-stickable");
  }

  function fv_player_sticky_class_remove() {
    $playerDiv.removeClass("is-sticky-" + stickyPlace);
    $playerDiv.css("width", "");
    $playerDiv.css("height", "");
    $playerDiv.css("max-height", "");
    $playerDiv.parent(".flowplayer").removeClass("is-stickable");
    
    if( is_sticky ) {
      is_sticky = false;
      api.trigger( 'sticky-exit', [ api ] );
    }
  }

  function is_big_enough() {
    return jQuery(window).innerWidth() >= 1020;
  }
});

jQuery(function($) {
  $(document).on('click', "a.fp-sticky", function() {
    $("div.flowplayer.is-stickable").addClass("is-unSticky");
    var $playerDiv = $("div.flowplayer.is-stickable").find('.fp-player');
    $playerDiv.removeClass("is-sticky-right-bottom");
    $playerDiv.removeClass("is-sticky-left-bottom");
    $playerDiv.removeClass("is-sticky-right-top");
    $playerDiv.removeClass("is-sticky-left-top");
    $playerDiv.css("width", "");
    $playerDiv.css("height", "");
    $playerDiv.css("max-height", "");
    
    if( is_sticky ) {
      is_sticky = false;
      api.trigger( 'sticky-exit', [ api ] );
    }
  });
  $(document).on('click', "div.flowplayer.is-unSticky", function() {
    $("div.flowplayer").removeClass("is-unSticky");
  });
});