// copy of the original function with some mods
flowplayer(function(api, root) {
  if ( !api.conf.fv_chromecast ) return;
  
  // TODO: Only load when some video is started?
  if( !window['__onGCastApiAvailable'] ) {
    jQuery.getScript( { url: 'https://www.gstatic.com/cv/js/sender/v1/cast_sender.js', cache: true });
    window['__onGCastApiAvailable'] = function(loaded) {
      if (!loaded) return;
      initialize();
    };
  }

  var conf = api.conf.chromecast || {}
    , session
    , timer
    , trigger
    , bean = flowplayer.bean
    , common = flowplayer.common
    , waiting_for_seek = false;

  function initialize() {
    var applicationId, sessionRequest, apiConfig;
    applicationId = conf.applicationId || chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID;
    sessionRequest = new chrome.cast.SessionRequest(applicationId);
    apiConfig = new chrome.cast.ApiConfig(
      sessionRequest,
      sessionListener,
      receiverListener
    );
    chrome.cast.initialize(apiConfig, onInitSuccess, onError);
  }

  function sessionListener() {}

  function receiverListener(ev) {
    console.log('FV Player: Chromecast listener',ev);
    if (ev !== chrome.cast.ReceiverAvailability.AVAILABLE) return;
    
    flowplayer.conf.chromecast_available = true;
  }

  function onInitSuccess() {}

  function onError() {
    console.log('chromecast onError');
  }

  function createUIElements() {
    var btnContainer = common.find('.fp-header', root)[0];
    if (!btnContainer) return; // UI no more available
    common.find('.fp-chromecast', btnContainer).forEach(common.removeNode);
    common.find('.fp-chromecast-engine', root).forEach(common.removeNode);
    trigger = common.createElement('a', { 'class': 'fp-chromecast fp-icon', title: 'Play on Cast device'})
    btnContainer.appendChild(trigger);
    var chromeCastEngine = common.createElement('div', { 'class': 'fp-chromecast-engine' })
      , chromeCastStatus = common.createElement('p', { 'class': 'fp-chromecast-engine-status' })
      , chromeCastIcon = common.createElement('p', { 'class': 'fp-chromecast-engine-icon' });
    chromeCastEngine.appendChild(chromeCastIcon);
    chromeCastEngine.appendChild(chromeCastStatus);
    var engine = common.find('.fp-engine', root)[0];
    if (!engine) common.prepend(common.find('.fp-player', root)[0] || root, chromeCastEngine);
    else engine.parentNode.insertBefore(chromeCastEngine, engine);
  }

  function destroy() {
    clearInterval(timer);
    timer = null;
    api.release();
    common.toggleClass(root, 'is-chromecast', false);
    common.toggleClass(trigger, 'fp-active', false);
  }

  function get_media() {
    var media = false;
    
    // we need MP4 or MPEG-DASH
    var sources = api.video.sources_fvqs || api.video.sources;
    for( var i in sources ) {
      var type = sources[i].type;
      if( type == 'video/mp4' || type == 'video/fv-mp4' || type == 'application/dash+xml' ) {
        media = sources[i];
        break;
      }
    }

    // fallback to HLS
    if( !media ) {
      for( var i in sources ) {
        if( sources[i].type == 'application/x-mpegurl' ) {
          media = sources[i];
          break;
        }
      }
    }
    
    // if it's using encryption, we cannot use it
    if( api.video.fvhkey && !api.conf.hls_cast ) return false;

    if( media ) {
      // make sure you use the best quality available
      // this also prefers Hls over MP4 for Vimeo videos as Vimeo HLS wouldn't play on our Chromecast
      var top_quality = false,
        mp4_qualities = ['fullhd','hd','md','sd'];

      // check what's available and pick a MP4 video as Chromecast doesn't like Vimeo HLS. Tested on https://flowplayer.com/developers/tools/stream-tester
      for( var quality in mp4_qualities ) {
        var re = new RegExp('-'+mp4_qualities[quality]);
        for( var i in api.video.sources_fvqs ) {
          var source = api.video.sources_fvqs[i]
          if( source.src.match(re) && source.type == 'video/mp4' ) {
            top_quality = source;
            break;
          }
        }

        if( top_quality ) {
          media = top_quality;
          break;
        }
      }

    }

    return media;
  }
  
  function load_media() {
    var media = get_media();

    if( !media ) {
      return false;
    }
    
    // TODO: Test with Vimeo
    var cast_subtitles = [];
    if( api.video.subtitles ) {
      api.video.subtitles.forEach( function(v,k) {
        if( v.src.match(/\.srt/) ) {
          console.log('FV Player: Chromecast doesn\'t support SRT subtitles');
        }
        
        var subtitles = new chrome.cast.media.Track(k, chrome.cast.media.TrackType.TEXT);
        subtitles.trackContentId = v.src;
        subtitles.trackContentType = 'text/vtt';
        subtitles.subtype = chrome.cast.media.TextTrackType.SUBTITLES;
        subtitles.name = v.label;
        // we add the index as there might be multiple subtitles in the same lang
        subtitles.language = v.srclang+'-'+k,
        subtitles.customData = null;
        cast_subtitles.push( subtitles );
      });

    }

    // if we do not provide media.type below, then Stream Loader HLS streams won't be recognized as HLS
    var mediaInfo = new chrome.cast.media.MediaInfo(media.src, media.type);
    mediaInfo.tracks = cast_subtitles;
    
    var request = new chrome.cast.media.LoadRequest(mediaInfo);
    
    // do not play the video from start, but continue where you left off
    if( !api.live ) {
      request.currentTime = api.video.time;
    }

    // the old interval might be running and ruining the party
    clearInterval(timer);
    timer = false;
    
    session.loadMedia(request, onMediaDiscovered, function onMediaError(e) {
      console.log('onMediaError', e)
    });
  }

  function onMediaDiscovered(chromecast) {
    // use the selected audio tracks or subtitles
    switch_tracks( chromecast );
    
    chromecast.addUpdateListener(function(alive) {
      if (!session) return; // Already destoryed
      
      timer = timer || setInterval(function() {
        api.trigger('progress', [api, chromecast.getEstimatedTime()]);
        
        // hilight the active audio track
        // seems like subtitles are hilighted elsewhere
        chromecast.activeTrackIds.forEach( function(track_id) {
          jQuery.each( chromecast.media.tracks, function(k,v) {

            if( v.trackId == track_id && v.type == 'AUDIO' ) {
              // Match by name first
              var found = hilight_audio_track( "data-audio", v.language );
              
              // If no match found, match by language code
              // We do this as there might be two different audio tracks
              // which are in the same language but with different names
              if( !found ) {
                hilight_audio_track( "data-lang", v.language );
              }
              return false;
            }
          });
        });
        
      }, 500);
      
      if (alive) {
        common.toggleClass(root, 'is-chromecast', true);
        common.toggleClass(trigger, 'fp-active', true);
        api.hijack({
          pause: function() {
            console.log('hijacked pause!');
            chromecast.pause();
          },
          resume: function() {
          
            // support replay
            if( api.finished ) {
              clearInterval(timer);
              timer = null;
              api.release();
              
              load_media();
              return;
            }
            
            chromecast.play();
          },
          seek: function(time) {
            var req = new chrome.cast.media.SeekRequest();
            req.currentTime = time;
            chromecast.seek(req);
          }
        });
      }
      var playerState = chromecast.playerState;
      
      if (api.paused && playerState === chrome.cast.media.PlayerState.PLAYING) api.trigger('resume', [api]);
      if (api.playing && playerState === chrome.cast.media.PlayerState.PAUSED) api.trigger('pause', [api]);
      
      // when seeking we must wait for it to buffer to be able to really tell if
      // it did seek
      if (api.seeking && playerState === chrome.cast.media.PlayerState.BUFFERING) {
        waiting_for_seek = true;
      }
      // once we know we buffered, we can take the next playing event for granted
      if (api.seeking && playerState === chrome.cast.media.PlayerState.PLAYING && waiting_for_seek ) {
        waiting_for_seek = false;
        api.trigger('seek', [api]);
      }
      
      if( playerState == chrome.cast.media.PlayerState.IDLE && chromecast.idleReason == chrome.cast.media.IdleReason.FINISHED ) {
        api.trigger('finish', [api]);
      }
      
      common.toggleClass(root, 'is-loading', playerState === chrome.cast.media.PlayerState.BUFFERING);
    });
  }
  
  api.bind('ready', function(e,api,video) {
    
    // already using Chromecast
    if( session ) {
      if( get_media() ) {      
        // we wait a bit to be able to pause the video that just loaded
        api.one('progress', function(e,api) {
          // take the power back!
          api.release();
          api.pause();
          
          // make sure it won't be muted when you disabled Chromecast
          api.mute(false,true);
          
          load_media();
        });

        // make that wait silent
        api.mute(true,true);

        
      } else {
        session.stop();
        session = null;
        destroy();
        jQuery(trigger).hide();
      }
        
      return;
    }
    
    if( !flowplayer.conf.chromecast_available ) return;
    
    if( get_media() ) {
      createUIElements();
      
      jQuery(trigger).show();
    } else {
      FV_Flowplayer_Pro.log('FV Player: Can\'t find media source suitable for Chromecast!');
      jQuery(trigger).hide();
    }

  });

  bean.on(root, 'click', '.fp-chromecast', function(ev) {
    ev.preventDefault();
    if (session) {
      // without this the video cannot continue playing
      api.trigger('pause', [api]);
      
      // restore playback position
      if( session.media[0].media ) {
        var seek = session.media[0].getEstimatedTime();
        setTimeout( function() {
          api.seek( seek );
        }, 0 );
      }
      
      session.stop();
      session = null;
      destroy();
      return;
    }
    if (api.playing) api.pause();

    // bring up the Chromecast device selection
    chrome.cast.requestSession(function(s) {
      // user clicked the Chromecast device, so let's make it impossible to use the player before it really initializes
      jQuery(root).addClass('is-loading');
      
      session = s;
      var receiverName = session.receiver.friendlyName;
      common.html(common.find('.fp-chromecast-engine-status',root)[0], 'Playing on device ' + receiverName);
      
      load_media();

    }, function(err) {
      console.error('requestSession error', err);
    });
  });
  
  // changing audio tracks or subtitles
  bean.on(root, 'click', '.fv-fp-hls-menu [data-audio], .fp-subtitle-menu [data-subtitle-index]', function() {
    if( session && session.media[0].media ) {
      switch_tracks( session.media[0] );
      return false;
    }
  });
  
  jQuery(window).on('unload', function(){
    if( session ) {
      session.stop();
    }
  });
  
  
  function hilight_audio_track( attr, chromecast_language ) {
    var audio_tracks_menu = jQuery(root).find(".fv-fp-hls-menu a"),
      found = false;
    
    audio_tracks_menu.each(function (k,el) {
      if( jQuery(el).attr(attr) === chromecast_language ) {
        jQuery(el).addClass("fp-selected");
        found = true;
      } else {
        jQuery(el).removeClass("fp-selected");
      }
    });

    return found;
  }
  
  
  function switch_tracks( chromecast ) {
    console.log( chromecast.media.tracks );
    
    // use the selected audio track
    var audio = jQuery(root).find('.fv-fp-hls-menu [data-audio].fp-selected').data('audio'),
      audio_lang = jQuery(root).find('.fv-fp-hls-menu [data-audio].fp-selected').data('lang'),
      subtitle_index = jQuery(root).find('.fp-subtitle-menu [data-subtitle-index].fp-selected').data('subtitle-index'),
      subtitles = subtitle_index > -1 ? api.video.subtitles[subtitle_index].srclang : false;
    
    var audio_found = false,
      subtitles_found = false,
      tracks_selected = [];
    
    // Match audio track by name
    jQuery.each( chromecast.media.tracks, function(k,v) {
      if( v.language == audio && v.type == 'AUDIO' ) {
        audio_found = v;
      }
      
      // we also chech the index as there might be multiple subtitles in the same lang
      if( v.language == subtitles+'-'+subtitle_index && v.type == 'TEXT' ) {
        subtitles_found = v;
      }
    });
    
    // If no audio track match found, match by language code
    // We do this as there might be two different audio tracks
    // which are in the same language but with different names
    if( !audio_found ) {
      jQuery.each( chromecast.media.tracks, function(k,v) {
        if( v.language == audio_lang && v.type == 'AUDIO' ) {
          audio_found = v;
          return false;
        }
      });
    }
    
    var debug_log = '';
    if( audio_found ) {
      tracks_selected.push( audio_found.trackId );
      debug_log += audio_found.language+' audio';
    }
    if( subtitles_found ) {
      tracks_selected.push( subtitles_found.trackId );
      if( debug_log ) debug_log += ' ';
      debug_log += subtitles_found.language+' subtitles';
    }
    
    if( tracks_selected ) {
      var request = new chrome.cast.media.EditTracksInfoRequest( tracks_selected );
      chromecast.editTracksInfo(request, function(){
        console.log('FV Player: Chromecast '+debug_log+' loaded');
      }, function(){
        console.log('FV Player: Chromecast '+debug_log+' failed');
      });
    }
  }

});