// Slider
freedomplayer( function(api,root) {
  var bean = freedomplayer.bean,
    common = freedomplayer.common,
    id = root.getAttribute("id"),
    playlist = common.find('[rel="'+id+'"]'),
    isDragging = false, // is the mouse dragging the slider?
    isHovered = false, // is the playlist slider hovered? Enabled keyboard controls
    startX, // dragging start
    prevScrollLeft,
    targetScrollLeft,
    cssColumnGap = 20, // required for proper left/right arrow functioning
    draggingThreshold = 5;

  if( !playlist[0] ) return;

  var slider = common.find('.fv-playlist-draggable', playlist),
    arrows = common.find('.fv-playlist-left-arrow, .fv-playlist-right-arrow', playlist);

  if( !slider[0] || !arrows[0] || !arrows[1] ) return;

  slider = slider[0];

  // Initial scroll position check
  toggleArrows();

  // All we need for mobile scrolling of the slider, really
  bean.on( slider, 'scroll', toggleArrows );

  // Prepare for dragging
  bean.on( slider, 'mousedown', function(e) {
    e.preventDefault();

    isDragging = true;
    slider.classList.add('active');
    prevScrollLeft = slider.scrollLeft;
    startX = e.pageX - slider.offsetLeft;
  } );
  
  // End of dragging
  bean.on( slider, 'mouseup', stoppedInteracting );
  
  // End of dragging and loosing focus
  slider.onmouseleave = function() {
    isHovered = false;

    stoppedInteracting();
  };
  
  // Actual dragging
  bean.on( slider, 'mousemove', function(e) {
    isHovered = true;

    if (!isDragging) {
      return;
    }
    e.preventDefault();

    var x = e.pageX - slider.offsetLeft,
      walk = x - startX;

    if( Math.abs(walk) > draggingThreshold ) {
      slider.classList.add('is-dragging');
    }

    slider.scrollLeft = prevScrollLeft - walk;
  } );

  function stoppedInteracting() {
    isDragging = false;
    slider.classList.remove('active');

    // Wait a bit as the click handler for playlist items needs to see we were dragging
    setTimeout( function() {
      slider.classList.remove('is-dragging');
    });

    toggleArrows();
  }
  
  function scrollSlider( right ) {
    var numVisibleItems = Math.floor( slider.clientWidth / slider.children[0].clientWidth ),
      itemWidth = slider.children[0].clientWidth + cssColumnGap;

    if( right ) {
      targetScrollLeft = slider.scrollLeft + numVisibleItems * itemWidth;
    } else {
      targetScrollLeft = slider.scrollLeft - numVisibleItems * itemWidth;
    }

    // Make sure we do not want to scroll out of bounds
    if( right && targetScrollLeft > slider.scrollWidth - slider.clientWidth ) {
      targetScrollLeft = slider.scrollWidth - slider.clientWidth;
    } else if( !right && targetScrollLeft < 0 ) {
      targetScrollLeft = 0;
    }

    window.requestAnimationFrame( smooth_scroll );

    function smooth_scroll() {
      var shift = right ? 30 : - 30;
      if( Math.abs(targetScrollLeft - slider.scrollLeft ) < 20 ) {
        shift = targetScrollLeft - slider.scrollLeft;
      }

      slider.scrollTo({ top: 0, left: slider.scrollLeft + shift });

      if( targetScrollLeft == slider.scrollLeft ) {
        toggleArrows();

      } else {
        window.requestAnimationFrame( smooth_scroll );
      }
    }
  }

  // which element is the current item?
  function toggleArrows() {
    slider.classList.remove('leftmost', 'rightmost');

    if (slider.scrollLeft === 0) {
      slider.classList.add('leftmost');
    } else if (slider.scrollLeft === slider.scrollWidth - slider.clientWidth) {
      slider.classList.add('rightmost');
    }
  }

  arrows[0].onclick = function() {
    scrollSlider(false);
  };

  arrows[1].onclick = function() {
    scrollSlider(true);
  };

  bean.on(document, "keydown", function(e) {
    if( !isHovered ) return;

    var key = e.keyCode;
    if (key === 39) { // left
      scrollSlider(true);
    }
    if (key === 37) { // right
      scrollSlider(false);
    }
  } );

});
