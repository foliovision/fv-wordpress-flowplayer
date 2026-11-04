jQuery( document ).ready( function() {
  freedomplayer_ios_playlist_scrollbar( jQuery( '.fp-playlist-vertical, .fp-playlist-version-two, .fv-playlist-slider-wrapper' ) );
} );

function freedomplayer_ios_playlist_scrollbar( wrappers ) {
  if ( ! freedomplayer.support.iOS ) {
    return;
  }

  wrappers.each( function() {
    var playlist = this,
      draggable = playlist.querySelector('.fv-playlist-draggable'),
      track = playlist.querySelector('.fp-ios-playlist-scrollbar'),
      thumb = playlist.querySelector('.fp-ios-playlist-scrollbar-thumb');

    playlist.classList.add('has-custom-scrollbar');

    if ( ! track ) {
      thumb = document.createElement('div');
      thumb.className = 'fp-ios-playlist-scrollbar-thumb';

      track = document.createElement('div');
      track.className = 'fp-ios-playlist-scrollbar';
      track.setAttribute( 'aria-hidden', 'true' );
      track.appendChild( thumb );

      if (
        playlist.classList.contains( 'fv-playlist-slider-wrapper' ) ||
        playlist.classList.contains( 'fp-playlist-version-two' )
      ) {
        // The wide child is the sticky containing block, so the bar can stay in view across the scroll.
        var wide = playlist.querySelector( '.fp-playlist-horizontal' );
        ( wide || playlist ).appendChild( track );
      } else {
        playlist.insertBefore( track, playlist.firstChild );
      }

      var scroller = draggable || playlist;
      scroller.addEventListener( 'scroll', function() {
        freedomplayer_ios_playlist_scrollbar_position( scroller, track, thumb );
      }, { passive: true } );

      window.addEventListener( 'orientationchange', function() {
        freedomplayer_ios_playlist_scrollbar_update( playlist, draggable, track, thumb );
      } );
    }

    freedomplayer_ios_playlist_scrollbar_update( playlist, draggable, track, thumb );
  } );
}

function freedomplayer_ios_playlist_scrollbar_update( pl, draggable, track, thumb ) {

  if ( draggable ) {
    pl = draggable;
  }

  /* We tollerate 12px as the scrollbar has martin-right: -12px for vertical playlists on narrow displays */
  var is_horizontal = pl.scrollWidth - pl.clientWidth > 12,
    total,
    viewport;

  if ( pl.scrollHeight === pl.clientHeight && pl.scrollWidth === pl.clientWidth ) {
    track.classList.add('is-hidden');
    return;
  }

  track.classList.remove('is-hidden');
  track.classList.toggle('is-horizontal', is_horizontal);

  if ( is_horizontal ) {
    viewport = pl.clientWidth;
    total = pl.scrollWidth;
  } else {
    viewport = pl.clientHeight;
    total = pl.scrollHeight;
  }

  if ( ! viewport || ! total ) {
    return;
  }

  var size = Math.max( viewport * viewport / total, 24 );
  if ( size > viewport ) {
    size = viewport;
  }

  track.style.setProperty( '--fvp-playlist-viewport', viewport + 'px' );
  thumb.style.setProperty( '--fvp-playlist-scrollbar-thumb-size', size + 'px' );
  track.setAttribute( 'data-viewport', viewport );
  track.setAttribute( 'data-total', total );
  track.setAttribute( 'data-thumb-size', size );

  freedomplayer_ios_playlist_scrollbar_position( pl, track, thumb );
}

function freedomplayer_ios_playlist_scrollbar_position( pl, track, thumb ) {
  var is_horizontal = track.classList.contains( 'is-horizontal' ),
    viewport = parseFloat( track.getAttribute( 'data-viewport' ) ) || 0,
    total = parseFloat( track.getAttribute( 'data-total' ) ) || 0,
    size = parseFloat( track.getAttribute( 'data-thumb-size' ) ) || 0,
    overflow = total - viewport,
    scroll = is_horizontal ? pl.scrollLeft : pl.scrollTop,
    travel = Math.max( viewport - size, 0 ),
    position = overflow > 0 ? ( scroll / overflow ) * travel : 0;

  thumb.style.transform = is_horizontal ? 'translateX(' + position + 'px)' : 'translateY(' + position + 'px)';
}
