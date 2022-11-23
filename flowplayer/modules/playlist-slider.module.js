// Slider
freedomplayer( function(api,root) {
  var bean = freedomplayer.bean,
    common = freedomplayer.common,
    id = root.getAttribute("id"),
    playlist = common.find('[rel="'+id+'"]'),
    isDragging = false, // is the mouse dragging the slider?
    isHovered = false, // is the playlist slider hovered? Enabled keyboard controls
    startX,
    scrollLeft;

  if( !playlist[0] ) return;

  var slider = common.find('.fv-playlist-draggable', playlist),
    arrows = common.find('.fv-playlist-left-arrow, .fv-playlist-right-arrow', playlist);

  if( !slider[0] || !arrows[0] || !arrows[1] ) return;

  slider = slider[0];

  // Initial scroll position check
  checkScrollPosition();

  slider.onmousedown = function(e) {
    isDragging = true;
    slider.classList.add('active');
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
    checkScrollPosition();
  };
  
  slider.onmouseup = function() {
    isDragging = false;
    slider.classList.remove('active');
  };
  
  slider.onmouseleave = function() {
    isHovered = false;
    isDragging = false;
    slider.classList.remove('active');
  };
  
  slider.onmousemove = function(e) {
    isHovered = true;

    if (!isDragging) {
      return;
    }
    e.preventDefault();
    var x = e.pageX - slider.offsetLeft,
      walk = x - startX;

    slider.scrollLeft = scrollLeft - walk;
    checkScrollPosition();
  };
  
  function scrollSliderBy(deltaX) {
    window.requestAnimationFrame(() => {
      slider.scrollTo({ top: 0, left: slider.scrollLeft + deltaX, behavior: 'smooth' });

      // We wait for the scrollTo to finish, this is not ideal as the timeout might depend on browser
      setTimeout( function() {
        checkScrollPosition();
      }, 250 );
    });
  }
  
  function checkScrollPosition() {;
    slider.classList.remove('leftmost', 'rightmost');
    if (slider.scrollLeft === 0) {
      slider.classList.add('leftmost');
    } else if (slider.scrollLeft === slider.scrollWidth - slider.clientWidth) {
      slider.classList.add('rightmost');
    }
  }
  
  arrows[0].onclick = function() {
    scrollSliderBy(-600);
  };
  
  arrows[1].onclick = function() {
    scrollSliderBy(600);
  };

  bean.on(document, "keydown", function(e) {
    if( !isHovered ) return;

    var key = e.keyCode;
    if (key === 39) {
      scrollSliderBy(600);
    }
    if (key === 37) {
      scrollSliderBy(-600);
    }
  } );
  
});
