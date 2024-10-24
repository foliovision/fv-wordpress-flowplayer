flowplayer( function(api,root) {
  root = jQuery(root);
  var original_prev, original_next, random_seed = false;

  let button_no_picture, button_no_picture_ready = false;

  let playlist_button, playlist_menu, playlist_button_ready = false;

  if( !root.data('button-no_picture') && !root.data('button-repeat') && !root.data('button-rewind') && ! api.conf.skin_preview ) return;

  let have_playlist = (
    ! api.have_visible_playlist && api.conf.playlist.length > 0 ||
    api.have_visible_playlist()
  );

  api.bind('ready', function(e,api) {

    // Backup original api.next() and api.prev()
    if( typeof original_next == 'undefined' && typeof original_prev == 'undefined' ) {
      original_next = api.next;
      original_prev = api.prev;
    }

    if( api.video && api.video.type && !api.video.type.match(/^audio/) && root.data('button-no_picture') && ! button_no_picture_ready ) {
      button_no_picture_ready = true;

      api.createNoPictureButton();
    }

    if( root.data('button-repeat') ) {
      if( have_playlist && ! playlist_button_ready ) {
        playlist_button_ready = true;

        api.createRepeatButton();

        api.conf.playlist_shuffle = api.conf.track_repeat = false;

        random_seed = randomize();

        if( api.conf.loop ) {
          jQuery('a[data-action=repeat_playlist]', playlist_menu ).trigger('click');
        }

      } else if( root.find('.fv-fp-track-repeat').length == 0 && ! have_playlist ) {
        var button_track_repeat = jQuery('<strong class="fv-fp-track-repeat"><svg viewBox="0 0 80.333 71" width="18px" height="18px" class="fvp-icon fvp-replay-track"><use xlink:href="#fvp-replay-track"></use></svg></strong>');
        button_track_repeat.insertAfter( root.find('.fp-controls .fp-volume') ).on('click', function(e) {
          e.preventDefault();
          e.stopPropagation();

          if( api.video.loop ) {
            api.video.loop = false;
          } else {
            api.video.loop = true;
          }

          jQuery(this).toggleClass('is-active fp-color-fill',api.video.loop);
        });

        if( api.conf.loop ) {
          button_track_repeat.addClass('is-active fp-color-fill');
        }

        api.on("finish", function( e, api ) {
          if( api.video.loop ) {
            console.log('playlist-repeat.module', api.video.loop );
            api.resume();
          }
        });

      }
    }

    if( root.data('button-rewind') && ! freedomplayer.support.touch ) {
      api.createRewindForwardButtons();
    }

  }).bind('progress', function() {
    if( root.data('button-repeat') ) {
      api.video.loop = api.conf.track_repeat;
    }

  }).bind("finish.pl", function(e,api) {
    if( root.data('button-repeat') && have_playlist ) {
      console.log('playlist_repeat',api.conf.loop,'advance',api.conf.advance,'video.loop',api.video.loop);
      if( api.conf.playlist_shuffle ) {
        api.play( random_seed.pop() );
        if( random_seed.length == 0 ) random_seed = randomize();
      }
    }

  }).bind('unload', function() {
    root.find('.fv-fp-no-picture').remove();
    root.find('.fv-fp-playlist').remove();
    root.find('.fv-fp-track-repeat').remove();

  });

  function array_shuffle(a) {
    var j, x, i;
    for (i = a.length; i; i--) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
    }
    return a;
  }

  function randomize(random_seed) {
    random_seed = [];
    jQuery(api.conf.playlist).each( function(k,v) {
      random_seed.push(k);
    });

    random_seed = array_shuffle(random_seed);
    console.log('FV Player Randomizer random seed:',random_seed);
    return random_seed;
  }

  api.createNoPictureButton = function() {
    if ( root.find('.fv-fp-no-picture').length > 0 ) return;

    button_no_picture = jQuery('<span class="fv-fp-no-picture"><svg viewBox="0 0 90 80" width="18px" height="18px" class="fvp-icon fvp-nopicture"><use xlink:href="#fvp-nopicture"></use></svg></span>');

    button_no_picture.insertAfter( root.find('.fp-controls .fp-volume') ).on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      jQuery('.fp-engine',root).slideToggle(20);
      jQuery(this).toggleClass('is-active fp-color-fill');
      root.toggleClass('is-no-picture');
    });
  }

  api.createRepeatButton = function() {
    if ( root.find('.fv-fp-playlist').length > 0 ) return;

    var t = fv_flowplayer_translations;

    playlist_button = jQuery(
    '<strong class="fv-fp-playlist mode-normal">\
      <svg viewBox="0 0 80.333 80" width="18px" height="18px" class="fvp-icon fvp-replay-list"><title>'+t.playlist_replay_all+'</title><use xlink:href="#fvp-replay-list"></use></svg>\
      <svg viewBox="0 0 80.333 71" width="18px" height="18px" class="fvp-icon fvp-shuffle"><title>'+t.playlist_shuffle+'</title><use xlink:href="#fvp-shuffle"></use></svg>\
      <svg viewBox="0 0 80.333 71" width="18px" height="18px" class="fvp-icon fvp-replay-track"><title>'+t.playlist_replay_video+'</title><use xlink:href="#fvp-replay-track"></use></svg>\
      <span id="fvp-playlist-play" title="'+t.playlist_play_all+'">'+t.playlist_play_all_button+'</span>\
      </strong>');

    playlist_button.insertAfter( root.find('.fp-controls .fp-volume') ).on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      // reposition the repeat menu to be aligned with the repeat button
      if (playlist_menu.css('right') !== 'auto') {
        playlist_menu.css({
          "right": "auto",
          "left": playlist_button.position().left + 'px'
        });
      }

      if( playlist_menu.hasClass('fp-active') ) {
        api.hideMenu(playlist_menu[0]);
      }
      else {
        // workaround for flowplayer 7 not picking up our menu as one of its own,
        // thus not closing it
        root.trigger('click');
        api.showMenu(playlist_menu[0]);
      }
    });

    playlist_menu = jQuery(
      '<div class="fp-menu fv-fp-playlist-menu">\
        <a data-action="repeat_playlist"><svg viewBox="0 0 80.333 80" width="18px" height="18px" class="fvp-icon fvp-replay-list"><title>'+t.playlist_replay_all+'</title><use xlink:href="#fvp-replay-list"></use></svg> <span class="screen-reader-text">'+t.playlist_replay_all+'</span></a>\
        <a data-action="shuffle_playlist"><svg viewBox="0 0 80.333 71" width="18px" height="18px" class="fvp-icon fvp-shuffle"><title>'+t.playlist_shuffle+'</title><use xlink:href="#fvp-shuffle"></use></svg> <span class="screen-reader-text">'+t.playlist_shuffle+'</span></a>\
        <a data-action="repeat_track"><svg viewBox="0 0 80.333 71" width="18px" height="18px" class="fvp-icon fvp-replay-track"><title>'+t.playlist_replay_video+'</title><use xlink:href="#fvp-replay-track"></use></svg> <span class="screen-reader-text">'+t.playlist_replay_video+'</span></a>\
        <a class="fp-selected" data-action="normal"><span id="fvp-playlist-play" title="'+t.playlist_play_all+'">'+t.playlist_play_all_button+'</span></a>\
        </div>').insertAfter( root.find('.fp-controls') );

        playlist_button

    jQuery('a',playlist_menu).on('click', function() {
      jQuery(this).siblings('a').removeClass('fp-selected');
      jQuery(this).addClass('fp-selected');
      playlist_button.removeClass('mode-normal mode-repeat-track mode-repeat-playlist mode-shuffle-playlist');

      var action = jQuery(this).data('action');
      if( action == 'repeat_playlist' ) {
        playlist_button.addClass('mode-repeat-playlist');
        api.conf.loop = true;
        api.conf.advance = true;
        api.video.loop = api.conf.track_repeat = false;
        api.conf.playlist_shuffle = false;

      } else if( action == 'shuffle_playlist' ) {
        if ( ! random_seed ) {
          random_seed = randomize();
        }

        playlist_button.addClass('mode-shuffle-playlist');
        api.conf.loop = true;
        api.conf.advance = true;
        api.conf.playlist_shuffle = true;

      } else if( action == 'repeat_track' ) {
        playlist_button.addClass('mode-repeat-track');
        api.conf.track_repeat = api.video.loop = true;
        api.conf.loop = api.conf.playlist_shuffle = false;

      } else if( action == 'normal' ) {
        playlist_button.addClass('mode-normal');
        api.conf.track_repeat = api.video.loop = false;
        api.conf.loop = api.conf.playlist_shuffle = false;

      }

      if(api.conf.playlist_shuffle) {
        api.next = function() {
          api.play( random_seed.pop() );
          if( random_seed.length == 0 ) random_seed = randomize();
        };

        api.prev = function() {
          api.play( random_seed.shift() );
          if( random_seed.length == 0 ) random_seed = randomize();
        };

      } else {
        api.next = original_next;
        api.prev = original_prev;
      }

    });
  }

  api.createRewindForwardButtons = function() {
    if( root.find('.fv-fp-rewind').length == 0 ) {
      var button_rewind = jQuery('<span class="fv-fp-rewind"><svg viewBox="0 0 24 24" width="21px" height="21px" class="fvp-icon fvp-rewind"><use xlink:href="#fvp-rewind"></use></svg></span>');

      button_rewind.insertBefore( root.find('.fp-controls .fp-playbtn') ).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        api.seek(api.video.time-10);
      });

      button_rewind.toggle( !api.video.live || api.video.dvr );
    }

    if( root.find('.fv-fp-forward').length == 0 ) {
      var button_forward = jQuery('<span class="fv-fp-forward"><svg viewBox="0 0 24 24" width="21px" height="21px" class="fvp-icon fvp-forward"><use xlink:href="#fvp-forward"></use></svg></span>');

      button_forward.insertAfter( root.find('.fp-controls .fp-playbtn') ).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        api.seek(api.video.time+10);
      });

      button_forward.toggle( !api.video.live || api.video.dvr );
    }
  }

  if ( api.conf.skin_preview ) {
    if ( root.data('button-no_picture') ) {
      // Wait a bit so that it can be added after the volume button
      setTimeout( function() {
        api.createNoPictureButton();
      }, 0 );
    }

    if ( root.data('button-repeat') ) {
      // Wait a bit so that it can be added after the volume button
      setTimeout( function() {
        api.createRepeatButton();
      }, 0 );
    }

    if ( root.data('button-rewind') ) {
      // Wait a bit so that they are added after the playlist prev/next buttons
      setTimeout( function() {
        api.createRewindForwardButtons();
      }, 0 );
    }
  }

});
