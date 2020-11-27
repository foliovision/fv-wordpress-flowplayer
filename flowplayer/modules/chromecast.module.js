// copy of the original function with some mods
flowplayer(function(api, root) {
  if ( !api.conf.fv_chromecast ) return;
  
  // TODO: Only load when some video is started?
  if( !window['__onGCastApiAvailable'] ) {
    jQuery.getScript('https://www.gstatic.com/cv/js/sender/v1/cast_sender.js');
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
    
    // we need MP4 of HLS
    var sources = api.video.sources_fvqs || api.video.sources;
    for( var i in sources ) {
      if( sources[i].type == 'application/x-mpegurl' || sources[i].type == 'video/mp4' || sources[i].type == 'video/fv-mp4' ) {
        media = sources[i].src;
        break;
      }
    }
    
    // if it's using encryption, we cannot use it
    if( api.video.fvhkey ) return false;

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
            top_quality = source.src;
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
    var src = get_media();

    if( !src ) {
      return false;
    }
    
    var mediaInfo = new chrome.cast.media.MediaInfo(src);
    var request = new chrome.cast.media.LoadRequest(mediaInfo);

    // the old interval might be running and ruining the party
    clearInterval(timer);
    timer = false;
    
    session.loadMedia(request, onMediaDiscovered, function onMediaError(e) {
      console.log('onMediaError', e)
    });
  }

  function onMediaDiscovered(media) {
    media.addUpdateListener(function(alive) {
      if (!session) return; // Already destoryed
      
      timer = timer || setInterval(function() {
        console.log('timer',api.video.src);
        api.trigger('progress', [api, media.getEstimatedTime()]);
      }, 500);
      
      if (alive) {
        common.toggleClass(root, 'is-chromecast', true);
        common.toggleClass(trigger, 'fp-active', true);
        api.hijack({
          pause: function() {
            console.log('hijacked pause!');
            media.pause();
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
            
            media.play();
          },
          seek: function(time) {
            var req = new chrome.cast.media.SeekRequest();
            req.currentTime = time;
            media.seek(req);
          }
        });
      }
      var playerState = media.playerState;
      
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
      
      if( playerState == chrome.cast.media.PlayerState.IDLE && media.idleReason == chrome.cast.media.IdleReason.FINISHED ) {
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
      FV_Flowplayer_Pro.log('FV Player Pro: Can\'t find media source suitable for Chromecast!');
      jQuery(trigger).hide();
    }

  });

  bean.on(root, 'click', '.fp-chromecast', function(ev) {
    ev.preventDefault();
    if (session) {
      api.trigger('pause', [api]);
      session.stop();
      session = null;
      destroy();
      return;
    }
    if (api.playing) api.pause();

    chrome.cast.requestSession(function(s) {
      session = s;
      var receiverName = session.receiver.friendlyName;
      common.html(common.find('.fp-chromecast-engine-status')[0], 'Playing on device ' + receiverName);
      
      load_media();

    }, function(err) {
      console.error('requestSession error', err);
    });
  });
  
  jQuery(window).on('unload', function(){
    if( session ) {
      session.stop();
    }
  });

});