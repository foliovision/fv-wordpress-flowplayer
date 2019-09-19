/*
 *  Video Position Store functionality
 */
flowplayer( function(api,root) {
  var
    $root = jQuery(root),
    enabled = flowplayer.conf.video_position_save_enable || $root.data('save-position'),
    progressEventsCount = 0,
    // number of events to pass before we auto-send current video positions
    sendPositionsEvery = 60,
    // the actual AJAX object we use to send progress data, so we can cancel it in case it's still running    
    ajaxCall = null,
    // maximum cookie size with saved video positions we should store
    maxCookieSize = 2500,    
    localStorageEnabled = null,    
    cookieKeyName = 'video_positions',

    // retrieves the original source of a video
    getOriginalSource = function(video) {
      // logged-in users will have position stored within that video
      return (
        (typeof(video.sources_original) != "undefined" && typeof(video.sources_original[0]) != "undefined") ?
          video.sources_original[0] :
          video.sources[0]
      );
    },

    // calculates a cookie byte size
    getTextByteSize = function(txt) {
      return encodeURIComponent(txt).length;
    },

    getCookieKey = function(key) {
      return (localStorageEnabled ? localStorage.getItem(key) : Cookies.get(key));
    },

    setCookieKey = function(key, value) {
      return (localStorageEnabled ? localStorage.setItem(key, value) : Cookies.set(key, value));
    },

    // stores currently played/paused/stopped video position
    storeVideoPosition = function (e, api) {
      if (api.live) {
        return;
      }
      
      if (api.video.sources) {
        if (typeof(flowplayer['playPositions']) == 'undefined') {
          flowplayer['playPositions'] = [];
        }

        var
          originalVideoApiPath = getOriginalSource(api.video),
          position = Math.round(api.video.time);

        flowplayer['playPositions'][originalVideoApiPath.src] = position;

        // store the new position in the instance itself as well
        if (originalVideoApiPath.position) {
          originalVideoApiPath.position = position;
        }

        // make a call home every +-30 seconds to make sure a browser crash doesn't affect the position save too much
        // if (progressEventsCount++ >= sendPositionsEvery) {
        // ... refactor: only store position when we're leaving the page now, not on player progress
        if (progressEventsCount++ >= sendPositionsEvery && flowplayer.conf.closingPage) {
          if (ajaxCall) {
            ajaxCall.abort();
          }
          
          ajaxCall = sendVideoPositions(true, function () {
            ajaxCall = null;
          });
          
          progressEventsCount = 0;
        }
      }
    },

    // used when video unloads and another video starts playing
    forceSavePosition = function (e, api) {
      var inPlaylist = false;

      for (var i in api.conf.playlist) {
        inPlaylist = true;
        break;
      }

      if (inPlaylist && !flowplayer.conf.closingPage) {
        progressEventsCount = sendPositionsEvery + 1;
        storeVideoPosition(e, api);
        sendVideoPositions();
      }
    },

    // called when the video finishes playing - removes that video position from cache, as it's no longer needed
    removeVideoPosition = function (e, api) {
      if (api.video.sources) {
        if (typeof(flowplayer['playPositions']) == 'undefined') {
          flowplayer['playPositions'] = [];
        }

        var originalVideoApiPath = getOriginalSource(api.video);
        flowplayer['playPositions'][originalVideoApiPath.src] = 0;
      }
    },

    // used to seek into the desired last stored position when he video has started
    seekIntoPosition = function (e, api) {      
      if( api.video && api.video.live ) return;
      
      var
        originalVideoApiPath = getOriginalSource(api.video),
        position = originalVideoApiPath.position;

      api.bind('progress', storeVideoPosition);
      
      if (position) {
        seek(position);
      } else {
        // try to lookup position of a guest visitor
        if (flowplayer.conf.is_logged_in != '1') {
          var data = getCookieKey(cookieKeyName);
          if (data && typeof(data) !== 'undefined') {
            try {
              data = JSON.parse(data);
              if (data[originalVideoApiPath.src]) {
                seek(data[originalVideoApiPath.src]);
              }
            } catch (e) {
              // something went wrong...
              // TODO: shall we try to reset guest data here?
              return;
            }
          }
        }
      }
    },

    sendVideoPositions = function(async, callback) {
      if (async !== true) {
        async = false;
      }

      if (!callback || typeof(callback) == 'undefined') {
        callback = function() {};
      }

      postData = [];

      for (var video_name in flowplayer['playPositions']) {
        postData.push({
          name: video_name,
          position: flowplayer['playPositions'][video_name]
        });
      }

      if (!postData.length) {
        return;
      }
      
      if ( flowplayer.conf.is_logged_in == '1') {
        // logged-in user, store position in their metadata on server
        return jQuery.ajax({
          type: 'POST',
          async: async,
          url: fv_fp_ajaxurl,
          complete: callback,
          data: {
            action: 'fv_wp_flowplayer_video_position_save',
            videoTimes: postData
          }
        });
      } else {
        // guest visitor, store position in a cookie / localStorage
        try {
          var data = getCookieKey(cookieKeyName);
          if (data && typeof(data) !== 'undefined') {
            data = JSON.parse(data);
          } else {
            data = {};
          }

          // add / edit our video positions
          for (var i in postData) {
            data[postData[i].name] = postData[i].position;
          }
          
          var
            serialized = JSON.stringify(data),
            dataSize = getTextByteSize(serialized);

          // check if we're not going over maximum cache size
          if (dataSize > maxCookieSize) {
            // we're over max cache size, let's delete some older videos
            while (dataSize > maxCookieSize) {
              // remove the first entry only
              for (var i in data) {
                delete data[i];

                // re-serialize with the value removed
                serialized = JSON.stringify(data);
                // calculate new data size, so we can exit the while loop
                dataSize = getTextByteSize(serialized);

                break;
              }
            }
          }
          
          setCookieKey(cookieKeyName, serialized);
        } catch (e) {
          // JSON JS support missing
          return;
        }
      }
      
      return false;
    },
    
  seek = function(position) {
    var seek_count = 0;
    var do_seek = setInterval( function() {
      if( ++seek_count > 20 ) clearInterval(do_seek);
      if( api.loading ) return;            
      api.seek(parseInt(position)); // int for Dash.js!
      clearInterval(do_seek);
    }, 10 );
  };
    
  if( !enabled ) return;

  // stop events  
  api.bind('finish', removeVideoPosition);

  // seek into the last saved position, it also hooks the progress event
  if( flowplayer.support.fvmobile ) {
    api.one( 'progress', seekIntoPosition);
  } else {
    api.bind( 'ready', seekIntoPosition);
  }

  // TODO: find out what event can be used to force saving of playlist video positions on video change
  //api.bind('finish', forceSavePosition);
  api.one('progress', function() {
    jQuery(window).on('beforeunload', function () {
      // only fire a single AJAX call if we're closing / reloading the browser
      if (!flowplayer.conf.closingPage) {
        flowplayer.conf.closingPage = true;
        sendVideoPositions();
      }
    });
  });

  // check whether local storage is enabled
  if (localStorageEnabled !== null) {
    return localStorageEnabled;
  }

  localStorageEnabled = true;
  try {
    localStorage.setItem('t', 't');
    if (localStorage.getItem('t') !== 't') {
      localStorageEnabled = false;
    }
    localStorage.removeItem('t');
  } catch (e) {
    localStorageEnabled = false;
  }
  
});