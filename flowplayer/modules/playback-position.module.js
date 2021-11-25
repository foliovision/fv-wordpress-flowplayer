/*
 *  Video Position Store functionality
 */

// MSIE8- shim
if (!Date.now) {
  Date.now = function() { return new Date().getTime(); }
}

(function($) {
  var
    // number of events to pass before we auto-send current video positions
    sendPositionsEvery = 60,
    // the actual AJAX object we use to send progress data, so we can cancel it in case it's still running
    ajaxCall = null,
    // maximum cookie size with saved video positions we should store
    maxCookieSize = 2500,
    localStorageEnabled = null,
    cookiePositionsKeyName = 'video_positions',
    cookiePlaylistsKeyName = 'player_playlist_item',
    tempPositionCookieKeyName = 'video_positions_tmp',
    tempPlaylistsCookieKeyName = 'player_playlist_item_tmp',
    tempSawCookieKeyName = 'video_saw_tmp',
    playPositions = [],
    playlistIndexes = [],
    sawVideo = [],

    getSerialized = function(data) {
      var
        serialized = JSON.stringify(data),
        dataSize = getTextByteSize(serialized);

      // check if we're not going over maximum cache size
      if (dataSize > maxCookieSize) {
        // we're over max cache size, let's delete some older videos
        while (dataSize > maxCookieSize) {
          // remove the first entry only
          for (var i in data) {
            if( !data.hasOwnProperty(i) ) continue;

            delete data[i];

            // re-serialize with the value removed
            serialized = JSON.stringify(data);
            // calculate new data size, so we can exit the while loop
            dataSize = getTextByteSize(serialized);

            break;
          }
        }
      }

      return serialized;
    },

    // retrieves the original source of a video
    getVideoId = function(video) {
      if( video.id ) {
        return video.id;
      }
      
      // logged-in users will have position stored within that video
      var out = (
        (typeof(video.sources_original) != "undefined" && typeof(video.sources_original[0]) != "undefined") ?
          video.sources_original[0].src :
          video.sources[0].src
      );

      // remove all AWS signatures from the path, if an original video URL is not found / present
      if (typeof(video.sources_original) == "undefined" || typeof(video.sources_original[0]) == "undefined") {
        out = removeAWSSignatures(out);
      }

      return out;
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

    removeCookieKey = function(key) {
      if (localStorageEnabled) {
        localStorage.removeItem(key);
      } {
        Cookies.remove(key);
      };
    },

    // called when the video finishes playing - removes that video position from cache, as it's no longer needed
    removeVideoPosition = function (e, api) {
      if (api.video.sources) {
        var video_id = getVideoId(api.video);
        playPositions[video_id] = 0;
        sawVideo[video_id] = 1;
      }
    },

    removeAWSSignatures = function(videoURL) {
      return videoURL
        .replace(/(X-Amz-Algorithm=[^&]+&?)/gm, '')
        .replace(/(X-Amz-Credential=[^&]+&?)/gm, '')
        .replace(/(X-Amz-Date=[^&]+&?)/gm, '')
        .replace(/(X-Amz-Expires=[^&]+&?)/gm, '')
        .replace(/(X-Amz-SignedHeaders=[^&]+&?)/gm, '')
        .replace(/(X-Amz-Signature=[^&]+&?)/gm, '');
    },

    sendVideoPositions = function(async, callback) {
      var beaconSupported = ("sendBeacon" in navigator);
      if (async !== true) {
        async = false;
      }

      if (!callback || typeof(callback) == 'undefined') {
        callback = function() {};
      }

      postDataPositions = [];
      postDataPlaylists = [];

      for (var video_name in playPositions) {
        if( !playPositions.hasOwnProperty(video_name) ) continue;

        // remove all AWS signatures from this video
        postDataPositions.push({
          name: video_name,
          position: playPositions[video_name],
          saw: typeof(sawVideo[video_name]) != "undefined" ? sawVideo[video_name] : false
        });
      }

      for (var player_id in playlistIndexes) {
        if( !playlistIndexes.hasOwnProperty(player_id) ) continue;

        postDataPlaylists.push({
          player: player_id,
          item: playlistIndexes[player_id]
        });
      }

      if (!postDataPlaylists.length) {
        removeCookieKey(tempPlaylistsCookieKeyName);
      }

      if (!postDataPositions.length) {
        // no video positions? remove the temporary position cookie/localStorage data as well
        removeCookieKey(tempPositionCookieKeyName);
        removeCookieKey(tempSawCookieKeyName);
        return;
      }

      if ( flowplayer.conf.is_logged_in == '1') {
        if (beaconSupported) {
          // because the beacon call can arrive at the server after the page loads again
          // in case of a page reload, we'll store our last positions into a temporary cookie/localStorage
          // which will get removed on next page load
          try {
            var temp_position_data = {},
              temp_saw_data = {},
              temp_playlist_data = {};

            // add our video positions
            for (var i in postDataPositions) {
              if( !postDataPositions.hasOwnProperty(i) ) continue;

              temp_position_data[postDataPositions[i].name] = postDataPositions[i].position;
              temp_saw_data[postDataPositions[i].name] = postDataPositions[i].saw;
            }

            // playlist and item
            for (var i in postDataPlaylists) {
              if( !postDataPlaylists.hasOwnProperty(i) ) continue;
              temp_playlist_data[postDataPlaylists[i].player] = postDataPlaylists[i].item;
            }

            setCookieKey(tempPositionCookieKeyName, getSerialized(temp_position_data));
            setCookieKey(tempSawCookieKeyName, getSerialized(temp_saw_data));
            setCookieKey(tempPlaylistsCookieKeyName, getSerialized(temp_playlist_data));
          } catch (e) {
            // JSON JS support missing
            return;
          }

          var fd = new FormData();
          fd.append('action', 'fv_wp_flowplayer_video_position_save');
          fd.append('videoTimes', encodeURIComponent(JSON.stringify(postDataPositions)));
          fd.append('playlistItems', encodeURIComponent(JSON.stringify(postDataPlaylists)));
          navigator.sendBeacon(fv_player.ajaxurl, fd);

          // return false, so no ajax.abort() will be tried if multiple players try to call this same script part
          return false;
        } else {
          // logged-in user, store position in their metadata on server
          return jQuery.ajax({
            type: 'POST',
            async: async,
            url: fv_player.ajaxurl,
            complete: callback,
            data: {
              action: 'fv_wp_flowplayer_video_position_save',
              videoTimes: postDataPositions,
              playlistItems: postDataPlaylists
            }
          });
        }
      } else {
        // guest visitor, store position and item in a cookie / localStorage
        try {
          var data_positions = getCookieKey(cookiePositionsKeyName);
          var data_playlist_item = getCookieKey(cookiePlaylistsKeyName);

          if (data_positions && typeof(data_positions) !== 'undefined') {
            data_positions = JSON.parse(data_positions);
          } else {
            data_positions = {};
          }

          if (data_playlist_item && typeof(data_playlist_item) !== 'undefined') {
            data_playlist_item = JSON.parse(data_playlist_item);
          } else {
            data_playlist_item = {};
          }

          // add / edit our video positions
          for (var i in postDataPositions) {
            if( !postDataPositions.hasOwnProperty(i) ) continue;

            data_positions[postDataPositions[i].name] = postDataPositions[i].position;
          }

          for (var i in postDataPlaylists) {
            if( !postDataPlaylists.hasOwnProperty(i) ) continue;

            data_playlist_item[postDataPlaylists[i].player] = postDataPlaylists[i].item;
          }

          var
            serialized_positions = JSON.stringify(data_positions),
            serialized_playlist_items = JSON.stringify(data_playlist_item),
            dataSize_postions = getTextByteSize(serialized_positions),
            dataSize_playlist_items = getTextByteSize(serialized_playlist_items);

          // check if we're not going over maximum cache size
          if (dataSize_postions > maxCookieSize) {
            // we're over max cache size, let's delete some older videos
            while (dataSize_postions > maxCookieSize) {
              // remove the first entry only
              for (var i in data_positions) {
                if( !data_positions.hasOwnProperty(i) ) continue;

                delete data_positions[i];

                // re-serialize with the value removed
                serialized_positions = JSON.stringify(data_positions);
                // calculate new data_positions size, so we can exit the while loop
                dataSize_postions = getTextByteSize(serialized_positions);

                break;
              }
            }
          }

          // do the same for playlist item
           if (dataSize_playlist_items > maxCookieSize) {
            while (dataSize_playlist_items > maxCookieSize) {
              for (var i in data_positions) {
                if( !data_playlist_item.hasOwnProperty(i) ) continue;
                delete data_playlist_item[i];

                serialized_playlist_items = JSON.stringify(data_playlist_item);
                dataSize_playlist_items = getTextByteSize(serialized_positions);

                break;
              }
            }
          }

          setCookieKey(cookiePositionsKeyName, serialized_positions);
          setCookieKey(cookiePlaylistsKeyName, serialized_playlist_items);
        } catch (e) {
          // JSON JS support missing
          return;
        }
      }

      return false;
    };

  flowplayer( function(api,root) {
    // localstorage disabled by admin
    if( typeof(api.conf.disable_localstorage) != 'undefined' ) {
      return;
    }

    var
      $root = jQuery(root),
      enabled = flowplayer.conf.video_position_save_enable && $root.data('save-position') != 'false' || $root.data('save-position'),
      progressEventsCount = 0,
      player_id = $root.data('player-id') ? $root.data('player-id') : false,
      item_changed = false,

      // used to seek into the desired last stored position when he video has started
      seekIntoPosition = function (e, api) {
        if( api.video && api.video.live ) return;

        var
          video_id = getVideoId(api.video),
          position = api.video.position;

        // try to lookup position of a guest visitor
        if (flowplayer.conf.is_logged_in != '1') {
          var data = getCookieKey(cookiePositionsKeyName);
          if (data && typeof(data) !== 'undefined') {
            try {
              data = JSON.parse(data);
              if (data[video_id]) {
                position = data[video_id];
              }
            } catch (e) {
              // something went wrong...
              // TODO: shall we try to reset guest data here?
              return;
            }
          }
        }

        // Use the FV Player Pro method for custom end time if available
        // - is the position is too late and should it be ignored?
        if( !!api.get_custom_end && api.get_custom_end() < position ) {
          position = false;
        }

        // Use the FV Player Pro method for custom start time if available
        // - is the position too early and should it be ignored or adjusted?
        if( !!api.get_custom_start && api.get_custom_start() > 0 ) {
          if( position < api.get_custom_start() ) {
            position = false;
          }
        }

        api.bind('progress', storeVideoPosition);

        // no temporary positions found, let's work with DB / cookies
        if (position) {
          seek(position);
        }
      },

      // stores currently played/paused/stopped video position
      storeVideoPosition = function (e, api) {
        if (api.live) {
          return;
        }

        if (api.video.sources) {
          var
            video_id = getVideoId(api.video),
            position = Math.round(api.video.time);

          playPositions[video_id] = position;

          if( api.conf.playlist.length > 0 ) {
            if( player_id ) playlistIndexes[ player_id ] = api.video.index; // player_id => playlist_item
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
          if( !api.conf.playlist.hasOwnProperty(i) ) continue;

          inPlaylist = true;
          break;
        }

        if (inPlaylist && !flowplayer.conf.closingPage) {
          progressEventsCount = sendPositionsEvery + 1;
          storeVideoPosition(e, api);
          sendVideoPositions();
        }
      },

      seek = function(position) {
        // use the FV Player Pro method if available which considers the custom start/end time
        if( !!api.custom_seek ) {
          api.custom_seek(position);
          return;
        }

        // TODO: Is this still needed?
        var seek_count = 0;
        var do_seek = setInterval( function() {
          if( ++seek_count > 20 ) clearInterval(do_seek);
          if( api.loading ) return;
          api.seek(parseInt(position)); // int for Dash.js!
          clearInterval(do_seek);
        }, 10 );
      },

      processTempData = function(temp_data_name, video_id) {
        var data = getCookieKey(temp_data_name),
          output = false;
        if (data && typeof(data) !== 'undefined') {
          try {
            data = JSON.parse(data);

            if ( typeof(data[video_id]) != "undefined" ) {
              output = data[video_id];

              // remove the temporary cookie/localStorage data
              delete data[video_id];

              // check if we have any data left
              var stillHasData = false;
              for (var i in data) {
                if( !data.hasOwnProperty(i) ) continue;

                stillHasData = true;
                break;
              }

              if (stillHasData) {
                setCookieKey(temp_data_name, JSON.stringify(data));
              } else {
                removeCookieKey(temp_data_name);
              }
            }

            // we seeked into the correct position now, let's bail out,
            // so the DB value doesn't override this
            return output;
          } catch (e) {
            // something went wrong, so the next block will continue
          }
        }
      },

      restorePlaylistItem = function(e, api) {
        if ( typeof api == 'undefined' || api.conf.playlist.length == 0 ) return;

        var item_index = -1;

        if(player_id) {
          if ( flowplayer.conf.is_logged_in != '1') {
            var data = getCookieKey(cookiePlaylistsKeyName);
            if (data && typeof(data) !== 'undefined') {
              try {
                data = JSON.parse(data);
                if (data[player_id]) {
                  item_index = data[player_id];
                }
              } catch (e) {
                return;
              }
            }
          } else if( flowplayer.conf.is_logged_in == '1') {
            item_index = api.conf.playlist.length > 0 ? processTempData( tempPlaylistsCookieKeyName, player_id ) : false;
          }
        }

        if ( item_index >= 0  && !item_changed ) {
          api.play(item_index);
          item_changed = true;
        }

      };

    if( !enabled ) return;

    // console.log('Temp cookie', getCookieKey(tempPlaylistsCookieKeyName));

    if( getCookieKey(tempPlaylistsCookieKeyName) ) {
      $root.removeData('playlist_start'); // prevent conflict with playlist start module
    }

    // stop events
    api.bind('finish', removeVideoPosition);

    // seek into the last saved position, it also hooks the progress event
    // this used to run on ready event for !flowplayer.support.fvmobile,
    // but then we run into some reliability issue with HLS.js, so it's safer
    // to use progress
    api.one( 'progress', seekIntoPosition);

    api.bind('unload', function() {
      item_changed = false;
      api.one('ready', restorePlaylistItem);
    });
  
    api.one('ready', restorePlaylistItem);
  
    jQuery(".fp-ui", root).on('click', function() {
      restorePlaylistItem();
    });

    /**
     * Show the progress on the playlist item thumbnail
     *
     * @param element el The playlist item thumnbail progress indicator
     * @param object video The Flowplayer video object to act upon
     * @param int position Video position to show
     */
    api.playlist_thumbnail_progress = function(el,video,position) {
      // Use the FV Player Pro method for custom start time if available
      if( !!api.get_custom_start && api.get_custom_start(video) > 0 ) {
        position -= api.get_custom_start(video);
        if( position < 0 ) position = 0;
      }
      
      var duration = video.duration;

      // Use the FV Player Pro method for custom duration
      if( !!api.get_custom_duration && api.get_custom_duration() > 0 ) {
        duration = api.get_custom_duration();
      }

      // if the video is not yet loaded, take the data from HTML
      if( !duration ) {
        duration = el.data('duration');
      }

      if( !duration ) return; // TODO: Remove the marker?

      var progress = 100 * position/duration;
      el.css('width',progress+'%');
    }

    // Check all the playlist items to see if any of them has the temporary "position" or "saw" cookie set
    // We do this as the position saving Ajax can no longer be synchronous and block the page reload
    // So sometimes it takes longer to progress than the page load
    if (flowplayer.conf.is_logged_in == '1') {
      var playlist = api.conf.playlist.length > 0 ? api.conf.playlist : [ api.conf.clip ],
        playlist_external = jQuery('[rel='+jQuery(root).attr('id')+']');
      
      for( var i in playlist ) {
        if (!playlist.hasOwnProperty(i)) continue;

        var video_id = getVideoId(playlist[i]),
          position = processTempData(tempPositionCookieKeyName,video_id),
          saw = processTempData(tempSawCookieKeyName,video_id);

        if( position ) {
          if( api.conf.playlist.length ) {
            // set the position
            api.conf.playlist[i].sources[0].position = position;

            // Show the position on the playlist thumbnail
            var playlist_progress = jQuery('a',playlist_external).eq(i).find('.fvp-progress');
            if( playlist_progress.length ) {
              api.playlist_thumbnail_progress(playlist_progress,api.conf.playlist[i],position);
            }

          } else {
            api.conf.clip.sources[0].position = position;
          }
        }

        if( saw ) {
          if( api.conf.playlist.length ) {
            api.conf.playlist[i].sources[0].saw = true;
          } else {
            api.conf.clip.sources[0].saw = true;
          }
        }
      }
      
    }

    // store saw after finish
    api.bind('finish', function (e, api) {
      if( api.conf.playlist.length ) {
        api.conf.playlist[api.video.index].sources[0].saw = true;
      } else {
        api.conf.clip.sources[0].saw = true;
      }
    });

    // TODO: find out what event can be used to force saving of playlist video positions on video change
    //api.bind('finish', forceSavePosition);
  });

  // pagehide is required for iOS
  jQuery(window).on('beforeunload pagehide', function () {
    // only fire a single AJAX call if we're closing / reloading the browser
    if (!flowplayer.conf.closingPage) {
      flowplayer.conf.closingPage = true;
      sendVideoPositions();
    }
  });

  // localstorage disabled by admin
  if( typeof(fv_flowplayer_conf.disable_localstorage) != 'undefined' ) {
    localStorageEnabled = false;
  }

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

})(jQuery);
