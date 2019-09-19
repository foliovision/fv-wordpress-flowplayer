/*
 *  Playlist check
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  var playlist = api.conf.playlist;
  var videoIndex;

  api.bind("ready", function (e, api, video) {
    videoIndex = video.index;
  });

  api.bind("error", function (e,api, error) {
    setTimeout(function(){
      if( api.conf.playlist.length > 0 && api.error == true) {
        api.error = api.loading = false;
        root.removeClass('is-error');
        root.find('.fp-message.fp-shown').remove();
        if(videoIndex >= playlist.length -1){
          api.play(playlist.length -1);
        } else {
          api.play(videoIndex + 2)
          videoIndex += 1; // without this it will fail to recover if 2 items fail in a row
        }
      }
    },1000);
  });

});

/*
 * Playlist in controlbar for the "Season" playlist style
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  
  if( api.conf.playlist.length == 0 ) return;
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
  //if( !playlist.hasClass('fp-playlist-season') ) return; // todo: what about mobile? Should we always allow this?
  
  var playlist_button = jQuery('<strong class="fv-fp-list">Item 1.</strong>'),
    playlist_menu = jQuery('<div class="fp-menu fv-fp-list-menu"></div>').insertAfter( root.find('.fp-controls') );
  
  jQuery(api.conf.playlist).each( function(k,v) {
    playlist_menu.append('<a data-index="'+k+'">'+(k+1)+'. '+parse_title(playlist.find('h4').eq(k))+'</a>');    
  });
  
  playlist_button.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if( playlist_menu.hasClass('fp-active') ) {
      api.hideMenu(playlist_menu[0]);
    }
    else {
      // workaround for flowplayer 7 not picking up our menu as one of its own,
      // thus not closing it
      root.click();
      api.showMenu(playlist_menu[0]);
    }
  });
  
  jQuery('a',playlist_menu).click( function() {
    api.play(jQuery(this).data('index'));
  });
  
  api.on('ready', function(e,api,video) {
    playlist_menu.find('a').removeClass('fp-selected');
    var thumb = playlist_menu.find('a[data-index='+video.index+']');
    thumb.addClass('fp-selected');
    var label = fv_flowplayer_translations.playlist_item_no
    label = label.replace( /%d/, video.index+1 );
    label = label.replace( /%s/, parse_title( thumb.find('h4') ) );
    playlist_button.html(label);
  });
  
  function parse_title(el) {
    var tmp = el.clone();
    tmp.find('i.dur').remove();
    return tmp.text();
  }
  
});

flowplayer( function(api,root) {

  var
    $root = jQuery(root),
    start_index = $root.data('playlist_start');

  if( typeof(start_index) == 'undefined' ) return; 

  function start_position_changer() {  
    if ($root.data('position_changed') !== 1 && api.conf.playlist.length) {      
      start_index--; // the index should start from 0
      api.play(start_index);
      $root.data('position_changed', 1);
    }
  }

  api.bind('unload', function() {
    start_index = $root.data('playlist_start');
    $root.removeData('position_changed');
    api.one('ready', start_position_changer);
    api.video.index = 0;
  });

  api.one('ready', start_position_changer);

  jQuery(".fp-ui", root).on('click', function() {
    start_position_changer();
    $root.data('position_changed', 1);
  });

});

flowplayer(function(api, root) {
  root = jQuery(root);
  if( flowplayer.conf.wpadmin || jQuery(root).hasClass('is-audio') ) return;
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
  
  // Force fullscreen on mobile setting
  if( flowplayer.conf.mobile_force_fullscreen && flowplayer.support.fvmobile ) {
    if( !flowplayer.support.fullscreen ) {
      api.bind('ready', function() {
        api.fullscreen(true);
      });
    } else {
      root.on('click', function() {
        if( !api.ready || api.paused ) api.fullscreen(true);
      });
      
      jQuery('[rel='+root.attr('id')+'] a').on('click', function(e) {
        if( !api.isFullscreen ) {
          api.fullscreen();
          api.resume();
        }
      });
      
    }
    
    api.on('resume', function() {
      if( !api.isFullscreen ) api.fullscreen();
    });
    
    api.on('finish', function() {
      if( api.conf.playlist.length == 0 || api.conf.playlist.length -1 == api.video.index ) api.fullscreen(false);
    }).on('fullscreen', function(a,api) {
       root.addClass('forced-fullscreen');
    }).on('fullscreen-exit', function(a,api) {
       api.pause();
       root.removeClass('forced-fullscreen');
    });
  
  } else if( root.data('fsforce') == true || playlist.hasClass('fp-playlist-season') || playlist.hasClass('fp-playlist-polaroid') ) {
    var position, unload = root.find('.fp-unload');
    api.isFakeFullscreen = false;
    
    unload.show();
    
    root.on('click', function(e) {
      if( !api.ready && e.target != unload[0] ) api.fakeFullscreen(true);
    });
    
    unload.on('click', function(e) {
      if( !root.hasClass('is-splash') ) {
        api.fullscreen(false);
        api.fakeFullscreen(false);
      }
    });
    
    jQuery('[rel='+root.attr('id')+'] a').on('click', function(e) {
      if( !api.isFakeFullscreen ) {
        api.fakeFullscreen();
        api.resume();
      }
    });
    
    api.on('resume', function() {
      if( !api.isFakeFullscreen ) api.fakeFullscreen();
    }).on('finish', function() {
      if( api.conf.playlist.length == 0 || api.conf.playlist.length -1 == api.video.index ) api.fakeFullscreen(false);
    }).on('fullscreen', function(a,api) {
      root.removeClass('fake-fullscreen');
    }).on('fullscreen-exit', function(a,api) {
      if( api.isFakeFullscreen ) api.fakeFullscreen(true,true);
    }).on('unload', function(a,api) {
      // todo: ?? q key
    });
    
    api.fakeFullscreen = function( flag, force ) {
      if( !force && ( api.isFakeFullscreen == flag || api.disabled ) ) return;
      if( position === undefined ) position = root.css('position');      
      if( flag === undefined ) flag = !api.isFakeFullscreen;
      api.isFakeFullscreen = flag;
      api.trigger( flag ? 'fakefullscreen' : 'fakefullscreen-exit', [api] );
      root.toggleClass('is-fullscreen fake-fullscreen forced-fullscreen',flag)
      if( flag ) {        
        root.css('position','fixed');
      } else {
        root.css('position',position);
      }
    }
  }
  
  if( flowplayer.support.android && flowplayer.conf.mobile_landscape_fullscreen ) {
    api.on('fullscreen', function(a,api) {
      screen.orientation.lock("landscape-primary");
    })
  }
  
});

flowplayer( function(api,root) {
  root = jQuery(root);
  
  if( !root.data('button-no-picture') && !root.data('button-repeat') && !root.data('button-rewind') ) return;
  
  api.bind('ready', function(e,api) {
    if( !api.video.type.match(/^audio/) && root.data('button-no-picture') && root.find('.fv-fp-no-picture').length == 0 ) {
      var button_no_picture = jQuery('<span class="fv-fp-no-picture"><svg viewBox="0 0 90 80" width="20px" height="20px" class="fvp-icon fvp-nopicture"><use xlink:href="#fvp-nopicture"></use></svg></span>');
      
      button_no_picture.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        jQuery('.fp-engine',root).slideToggle(20);
        jQuery(this).toggleClass('is-active fp-color-fill');
      });    
    }
    
    if( root.data('button-repeat') ) {
      if( api.conf.playlist.length > 0 && root.find('.fv-fp-playlist').length == 0 ) {
        var playlist_button = jQuery('<strong class="fv-fp-playlist mode-normal"><svg viewBox="0 0 80.333 80" width="20px" height="20px" class="fvp-icon fvp-replay-list"><title>Replay Playlist</title><use xlink:href="#fvp-replay-list"></use></svg><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-shuffle"><use xlink:href="#fvp-shuffle"></use></svg><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-replay-track"><title>Replay Track</title><use xlink:href="#fvp-replay-track"></use></svg><span id="fvp-playlist-play" title="Play All">All</span></strong>'),
            playlist_menu = jQuery('<div class="fp-menu fv-fp-playlist-menu"><a data-action="repeat_playlist"><svg viewBox="0 0 80.333 80" width="20px" height="20px" class="fvp-icon fvp-replay-list"><title>Replay Playlist</title><use xlink:href="#fvp-replay-list"></use></svg> <span class="screen-reader-text">Repeat Playlist</span></a><a data-action="shuffle_playlist"><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-shuffle"><title>Shuffle Playlist</title><use xlink:href="#fvp-shuffle"></use></svg> <span class="screen-reader-text">Shuffle Playlist</span></a><a data-action="repeat_track"><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-replay-track"><title>Repeat Track</title><use xlink:href="#fvp-replay-track"></use></svg> <span class="screen-reader-text">Repeat Track</span></a><a class="fp-selected" data-action="normal"><span id="fvp-playlist-play" title="Play All">All</span></a></div>').insertAfter( root.find('.fp-controls') );
            
        api.conf.playlist_shuffle = api.conf.track_repeat = false;
          
        var random_seed = randomize();
        
        var should_advance = api.conf.advance;

        playlist_button.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
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
            root.click();
            api.showMenu(playlist_menu[0]);
          }
        });
        
        jQuery('a',playlist_menu).click( function() {
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
            playlist_button.addClass('mode-shuffle-playlist');
            api.conf.loop = true;
            api.conf.advance = true;
            api.conf.playlist_shuffle = true;          
          
          } else if( action == 'repeat_track' ) {
            playlist_button.addClass('mode-repeat-track');
            api.conf.track_repeat = api.video.loop = true;
            api.conf.loop = api.conf.playlist_shuffle = false;
            //api.conf.advance = !track_repeat && should_advance;
          
          } else if( action == 'normal' ) {
            playlist_button.addClass('mode-normal');
            api.conf.track_repeat = api.video.loop = false;
            api.conf.loop = api.conf.playlist_shuffle = false;
          
          }
          
        });
        
        if( api.conf.loop ) {
          jQuery('a[data-action=repeat_playlist]', playlist_menu ).click();
        }
        
        api.on('progress', function() {
          api.video.loop = api.conf.track_repeat;        
        });
        
        api.on("finish.pl", function(e,api) {console.log('playlist_repeat',api.conf.loop,'advance',api.conf.advance,'video.loop',api.video.loop);
          if( api.conf.playlist_shuffle ) {
            api.play( random_seed.pop() );
            if( random_seed.length == 0 ) random_seed = randomize();
          }
        });      
        
      } else if( root.find('.fv-fp-track-repeat').length == 0 && api.conf.playlist.length == 0 ) {
        var button_track_repeat = jQuery('<strong class="fv-fp-track-repeat"><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-replay-track"><use xlink:href="#fvp-replay-track"></use></svg></strong>');
        button_track_repeat.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          if( api.video.loop ) {
            api.video.loop = false;
            jQuery(this).removeClass('is-active fp-color-fill');
          } else {
            api.video.loop = true;
            jQuery(this).addClass('is-active fp-color-fill');
          }
        });
        
        if( api.conf.loop ) {
          button_track_repeat.click();
        }
        
      }
    }
    
    if( root.data('button-rewind') && root.find('.fv-fp-rewind').length == 0 ) {
      var button_rewind = jQuery('<span class="fv-fp-rewind"><svg viewBox="0 0 24 24" width="24px" height="24px" class="fvp-icon fvp-rewind"><use xlink:href="#fvp-rewind"></use></svg></span>');
      
      button_rewind.insertBefore( root.find('.fp-controls .fp-elapsed') ).click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        api.seek(api.video.time-10);
      });

      button_rewind.toggle(!api.video.live);
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
});