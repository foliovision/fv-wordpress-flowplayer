/*global YT, fv_player_log, fv_player_track */

/*eslint no-inner-declarations: 0*/
/*eslint no-cond-assign: 0*/

/*
 * Moved in from FV Player Pro
 * For full comit history check foliovision/fv-player-pro/blob/517cb6ef122e507f6ba7744e591b3825a643abe4/beta/js/youtube.module.js
 */

if( fv_flowplayer_conf.youtube ) {
  // If jQuery is already present use it to load the API as it won't show in browser as if the page is loading
  // This is important if YouTube has issues in your location, it might just time out while loading
  if( window.jQuery ) {
    jQuery.getScript("https://www.youtube.com/iframe_api");

  // ...loading it this way show the browser loading indicator for the tab
  } else {
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    document.body.appendChild(tag);
  }
}

  
  
  
if( typeof(flowplayer) != "undefined" ) {
  
  function fv_player_pro_youtube_get_video_id( src ) {
    var aMatch;
    if( aMatch = src.match(/(?:\?|&)v=([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];  
    }
    if( aMatch = src.match(/youtu.be\/([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];  
    }
    if( aMatch = src.match(/embed\/([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];  
    }
    if( aMatch = src.match(/shorts\/([a-zA-Z0-9_-]+)/) ){
      return aMatch[1];  
    }  
    return false;
  }
  
  function fv_player_pro_youtube_addRemovableEventListener( player, eventName, cb ) {
    var callbackName = 'youtubeCallbackFunction' + Math.random().toString(36).substr(2, 7);
    window[callbackName] = cb;
    player.addEventListener(eventName, callbackName);
  
    return function () {
      window[callbackName] = function () {}; // make the callback inactive
      if( typeof(player.removeEventListener) != "undefined" ) {
        player.removeEventListener(eventName, callbackName);
      }
    };
  }
  
  function fv_player_pro_youtube_onReady(e) {
    //console.log('fv_player_pro_youtube_onReady');
    var root = jQuery(e.target.getIframe()).closest('.flowplayer');
    root.removeClass('is-loading');                
    
    var api = root.data('flowplayer');
    api.loading = false;
    api.trigger('yt-ready');
    
    //  signal to the other players that 1MB YouTube API base.js has loaded
    jQuery(document).trigger('fv-player-yt-api-loaded');
  }
  
  
  function fv_player_pro_youtube_onStateChange(e) {
    //console.log('fv_player_pro_youtube_onStateChange',e.data);
    
    var root = jQuery(e.target.getIframe()).parents('.flowplayer');
    switch (e.data) {
      case -1:
        jQuery('.fp-splash',root).css('pointer-events','');
        root.addClass('is-loading');
        break;
      case YT.PlayerState.PLAYING:
        var api = root.data('flowplayer');
        api.load();
        break;
      case YT.PlayerState.BUFFERING:
        root.addClass('is-loading');
        // todo: put in placeholder splash screen as this event occurs if you use Video Link targetting a playlist item, but most of the time it triggers in onStateChange() already
        break;
    }
  }
  
  
  function fv_player_pro_youtube_onError(e) {
    var root = jQuery(e.target.getIframe()).parents('.flowplayer');
    var player = root.data('flowplayer');
    
    //  this is a copy of onError as we need to execute it for mobile preloaded player somehow...
    fv_player_log('FV Player Youtube onError for preloaded player',e);
    
    var src = player.video.index > 0 ? player.conf.playlist[player.video.index].sources[0].src : player.conf.clip.sources[0].src;
    
    fv_player_track( player, false, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", "YouTube video removed", src );

    
    setTimeout( function() {
      root.removeClass('is-splash'); //  we only do this for the preloaded player

      player.loading = false; //  we need to reset this to make sure user can pick another video in playlist
      root.removeClass('is-loading'); //  same as above
        
      if( player.conf.clip.sources.length > 1 ) {
      
        player.youtube.destroy();
        player.youtube = false;
        jQuery('.fvyoutube-engine',root).remove();
        jQuery('.fv-pf-yt-temp2',root).remove();
        jQuery(root).removeClass('is-ytios11');
        
        jQuery('.fp-ui',root).css('background-image','');
        jQuery('.fp-ui',root).append('<div class="wpfp_custom_popup fp-notice-load" style="height: 100%"><div class="wpfp_custom_popup_content">' + fv_flowplayer_translations.video_loaded + '</div></div>'); //  we show this so that we can capture the user click
        
        jQuery('.fp-notice-load').one( 'click', function()  {
          jQuery('.fp-notice-load',root).remove();
          
          //var api = jQuery(root).data('flowplayer');
          player.trigger('error', [ player, { code: 4, video: player.video } ] );
        } );
        
      }

    });

  }  
  
  
  function fv_player_pro_youtube_is_mobile() {
    // If it's the Facebook in-app browser or Messenger it seems to not permit autoplay for YouTube iframes
    if( navigator.userAgent.match(/FBAN|FBAV|FB_IAB|FB4A|FBMD|FBBV|FBDV|FBSN|FBSV|FBSS|FBID|FBLC|FBOP|FBRV|FBSF|FBAN|FB4A|FBMD|FBAV|FBBV|FBDV|FBSN|FBSV|FBSS|FBID|FBLC|FBOP|FBRV|FBSF|FB_IAB/i) ) {
      // Also add the special class for pointer-events: none on .fp-ui before playing
      jQuery('body').addClass( 'is-fv-player-fb-app' );
      return true;
    }

    // If it's Android, then it gets a special permission to play YouTube with sound! So we do not consider that a mobile
    // Include Safari (which means iPad too)
    return !flowplayer.support.android && (
      !flowplayer.support.firstframe || flowplayer.support.iOS || flowplayer.support.browser.safari
    );
  }

  function fv_player_pro_youtube_is_old_android() {
    return flowplayer.support.android && flowplayer.support.android.version < 4.4;
  }

  function fv_player_pro_youtube_player_vars( video_id, root, events ) {
    var vars = {
      videoId: video_id,
      width: root.width,
      height: root.height,
      playerVars: {
        // seems we need this for mobile load, otherwise onReady calls playVideo()
        // but now we had to exclude Safari (which means iPad too) from it
        autoplay: 0,
        controls: !jQuery(root).hasClass('no-controlbar') && fv_player_pro_youtube_is_old_android() ? 1 : 0, //  todo: no interface if it's a video ad!                       
        disablekb: 1,
        enablejsapi: 1,
        fs: 0,
        html5: 1,
        iv_load_policy: 3,
        loop: 0, //T.loop,                        
        modestbranding: 1,
        origin: ( document.location.protocol == "https:" ) ? "https://" : "http://" + flowplayer.conf.hostname,                        
        playsinline: 1,
        rel: 0,
        showinfo: 0,
        showsearch: 0,
        start: 0,
        t0: 1,
        widget_referrer: window ? window.location.href : null // help with YouTube tracking
      }
    }

    if( !fv_flowplayer_conf.youtube_cookies ) {
      vars.host = 'https://www.youtube-nocookie.com';
    }

    if( events ) {
      vars.events = events;
    }
    return vars;
  }
  
  
  function fv_player_pro_youtube_preload(that, api) {
    var root = jQuery(that);
    if( !api ) api = root.data('flowplayer');
    
    if( api && api.conf.item && api.conf.item.sources[0].type == 'video/youtube' || api && api.conf.clip && api.conf.clip.sources[0].type == 'video/youtube' ) { // exp: not sury why api.conf.clip sometimes fails?!
      if( api.loading == true || api.youtube || api.video.index ) return; // don' preload if it's already loading, if YouTube API already exists or if it's about to advanced to some other playlist item in case that this function was triggered by ajaxComplete as Vimeo loading Ajax has succeeded
      
      //if( root.find('.fake-video') ) return; // don't preload if FV Player VAST has decided to put in bogus video tag for the video ad
      
      api.loading = true;      
      root.addClass('is-loading');

      var common = flowplayer.common,
        video_id = api.conf.item ? fv_player_pro_youtube_get_video_id(api.conf.item.sources[0].src) : fv_player_pro_youtube_get_video_id(api.conf.clip.sources[0].src); // exp: not sury why api.conf.clip sometimes fails?!

      common.removeNode(common.findDirect("video", root)[0] || common.find(".fp-player > video", root)[0]);
      var wrapperTag = common.createElement("div");    
      wrapperTag.className = 'fp-engine fvyoutube-engine';
      common.prepend(common.find(".fp-player", root)[0], wrapperTag);    

        //console.log('new YT preload');  //  probably shouldn't happen when used in lightbox
        
        // this is the event which lets the player load YouTube
        jQuery(document).one('fv-player-yt-api-loaded', function() {
        
          // only one player can enter the loading phase
          if( ( typeof(YT) == "undefined" || typeof(YT.Player) == "undefined" ) && window.fv_player_pro_yt_loading ) {
            return;
          }
          
          window.fv_player_pro_yt_loading = true;
          
          var intLoad = setInterval( function() {
            // somehow the loading indicator disappears, so we put it back
            api.loading = true;
            root.addClass('is-loading');
            
            if( typeof(YT) == "undefined" || typeof(YT.Player) == "undefined" ) {
              return;
            }
            
            clearInterval(intLoad);
            
            api.youtube = new YT.Player(
              wrapperTag,
              fv_player_pro_youtube_player_vars(video_id, root)
            );
            
            jQuery('.fp-engine.fvyoutube-engine',root)[0].allowFullscreen = false;

            

            // splash needs to cover the iframe
            var splash = jQuery('.fp-splash',root);
            jQuery('.fp-ui',root).before( splash );            
            splash.css('pointer-events','none');

            jQuery('.fp-ui',root).before('<div class="fv-pf-yt-temp2"></div>');
            if( flowplayer.support.iOS && flowplayer.support.iOS.version > 11 ) {
              jQuery(root).addClass('is-ytios11');
              jQuery(root).find('.fv-pf-yt-temp2').on('click', function(){
                api.toggle();
              });
            }
            
            api.fv_yt_onReady = fv_player_pro_youtube_addRemovableEventListener(api.youtube,'onReady',fv_player_pro_youtube_onReady);
            api.fv_yt_onStateChange = fv_player_pro_youtube_addRemovableEventListener(api.youtube,'onStateChange',fv_player_pro_youtube_onStateChange);
            api.fv_yt_onError = fv_player_pro_youtube_addRemovableEventListener(api.youtube,'onError',fv_player_pro_youtube_onError);
                      
          }, 50 );
        });
        
        if( !window.fv_player_pro_yt_load ) {
          window.fv_player_pro_yt_load = true;
          jQuery(document).trigger('fv-player-yt-api-loaded');
        }
      
    }    
  }
  
  
  (function () {
    
    var engineImpl = function(player, root) {        
        
        function getVideoDeatils( youtube ) {
          var quality = youtube.getPlaybackQuality();

          var output = {
            seekable: true,
            src: youtube.getVideoUrl()
          };
          output.duration = youtube.getDuration();
          if( quality && typeof(aResolutions[quality]) != "undefined" ) {
            output.width = aResolutions[quality].width;
            output.height = aResolutions[quality].height;
            output.quality = quality;
            output.qualityLabel = aQuality.qualityLabels[quality];
            output.bitrate = aResolutions[quality].bitrate;
          }
          
          if( typeof(youtube.getVideoData) == 'function' ){
            var details = youtube.getVideoData();
            if( details.title ) {                      
              output.fv_title = 'YouTube: '+details.title+' ('+details.video_id+')';
            }
          }

          return output;                  
        }
        
        
        function onError(e) {
          fv_player_log('FV Player Youtube onError',e);
          
          var src = player.video.index > 0 ? player.conf.playlist[player.video.index].sources[0].src : player.conf.clip.sources[0].src;
          
          fv_player_track( player, false, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", "YouTube video removed", src );

          // Unfortunately the player had to enter the ready state to get this far
          // So we act as if it's the splash state - means no controls
          root.addClass('is-splash');

          player.trigger('error', [ player, { code: 4, video: player.video } ] );

          /**
           * Go to next video if it's a playlist and if there are not other sources.
           * In case of other sources FV Player Alternative Sources will already play the other
           * source based on that error trigger above.
           */
          if( player.conf.playlist.length > 1 && player.conf.clip.sources.length == 0 ) {

            setTimeout( function() {
              player.loading = false; //  we need to reset this to make sure user can pick another video in playlist
              root.removeClass('is-loading'); //  same as above
              
              player.paused = false;  //  we need to make sure it's not paused which happens in case of autoadvance
              root.removeClass('is-paused');  //  same as above
              
              player.ready = true;  //  we need to set this otherwise further clicks will make the video load again            
              player.bind('load', function() {
                player.ready = false; //  we need to set this otherwise playlist advance won't trigger all the events properly
              });
              
              // Go to next video, unless it's the last video
              setTimeout( function() {
                if( player.video.index + 1 < player.conf.playlist.length ) {
                player.next();
                }
              }, 5000 );
              
            });
          }
          
        }
        
        
        function onApiChange() {
          player.one('ready progress', function() { //  exp: primary issue here is that the event fires multiple times for each video. And also Flowplayer won't remove the subtitles button/menu when you switch videos            
            
            if( youtube.getOptions().indexOf('captions') > -1 ) {
            
              if( player.video.subtitles ) {
                youtube.unloadModule("captions");
                return;
              }
            
              var objCurrent = youtube.getOption('captions','track');
              var aSubtitles = youtube.getOption('captions','tracklist');              
              if( aSubtitles == 0 ){
                youtube.loadModule("captions");
                return;
              }
            
              youtube.setOption('captions','fontSize', 1 );
              
              //  core FP createUIElements()
              var common = flowplayer.common;
              wrap = common.find('.fp-captions', root)[0];
              var wrap = common.find('.fp-subtitle', root)[0];
              wrap = wrap || common.appendTo(common.createElement('div', {'class': 'fp-captions'}), common.find('.fp-player', root)[0]);
              Array.prototype.forEach.call(wrap.children, common.removeNode);
              
              //  core FP createSubtitleControl()
              var subtitleControl = root.find('.fp-cc')[0] || common.createElement('strong', { className: 'fp-cc' }, 'CC');
              var subtitleMenu = root.find('.fp-subtitle-menu')[0] || common.createElement('div', {className: 'fp-menu fp-subtitle-menu'}, '<strong>Closed Captions</strong>');
              
              common.find('a', subtitleMenu).forEach(common.removeNode);
              subtitleMenu.appendChild(common.createElement('a', {'data-yt-subtitle-index': -1}, 'No subtitles'));  //  exp: not using data-subtitle-index, but data-yt-subtitle-index to avoid code in core FP lib/ext/subtitle.js
              
              ( aSubtitles || []).forEach(function(st, i) { //  customized to read from above parsed YouTube subtitles
                var item = common.createElement('a', {'data-yt-subtitle-index': i}, st.displayName);
                if( objCurrent && objCurrent.languageCode && objCurrent.languageCode == st.languageCode) {
                  jQuery(item).addClass('fp-selected');
                }
                subtitleMenu.appendChild(item);
              });
              common.find('.fp-ui', root)[0].appendChild(subtitleMenu);
              common.find('.fp-controls', root)[0].appendChild(subtitleControl);
              
              root.find('.fp-cc').removeClass('fp-hidden');
              
              jQuery(document).on('click', '.fp-subtitle-menu a', function(e) {
                e.preventDefault();
                
                jQuery('a[data-yt-subtitle-index]').removeClass('fp-selected');
                jQuery(this).addClass('fp-selected');
                
                if( aSubtitles[jQuery(this).data('yt-subtitle-index')] ) {
                  // Was the NL option in use?
                  if( root.data('fv-player-youtube-nl') == undefined ) {
                    root.data('fv-player-youtube-nl', root.hasClass('is-youtube-nl') );
                  }

                  // Do not use the NL mode as it would prevent the subtitles from showing
                  root.removeClass('is-youtube-nl');

                  youtube.setOption('captions','track',{"languageCode": aSubtitles[jQuery(this).data('yt-subtitle-index')].languageCode});
                } else {
                  if( root.data('fv-player-youtube-nl') ) {
                    // Back to NL if it was enabled before
                    root.addClass('is-youtube-nl');
                  }

                  youtube.unloadModule("captions");
                }
                
              });
                          
            }
          });
        }
        
        
        function onReady() {
          // YouTube doesn't tell us if it's a live stream
          // but it seems when you check the duration in this moment
          // it gives 0 on live streams
          var duration = youtube.getDuration();
          if( duration == 0 ) {
            player.live = true;
            jQuery(root).addClass('is-live');
            
            // TODO: Problem is that when you use this in playlist
            // the next video will also behave like a live stream
            // but it appears to be a problem with Flowplayer in general
          }

          var a = jQuery.extend( loadVideo, getVideoDeatils(youtube) );

          if( !player.ready ) {

            // we init YouTube muted to allow muted autoplay
            // we need to do this before we trigger ready event as there we might need to mute the video for custom start time
            player.mute(true,true); // mute, but don't remember it!
            youtube.playVideo();
            // look for youtube_unmute_attempted to see what happens next

            // TODO: Shouldn't this trigger on YT.PlayerState.PLAYING - if so, do we need this onReady at all?
            //  workaround for iPad "QuotaExceededError: DOM Exception 22: An attempt was made to add something to storage that exceeded the quota." http://stackoverflow.com/questions/14555347/html5-localstorage-error-with-safari-quota-exceeded-err-dom-exception-22-an
            try {
              player.one( 'ready', function() {
                player.trigger( "resume", [player] ); //  not sure why but Flowplayer HTML5 engine triggers resume event once the video starts to play
              });              
              player.trigger('ready', [player, a] );
            } catch(e) {} //  bug: the seeking doesn't work!
          }
                        
          player.ready = true;          
          
          // TODO: Is this actually helping anything? It did not make the iframe click possible for Facebook in-app browser
          if( isMobile ) {                
            jQuery('.fp-ui',root).hide();
          }
          
          if( flowplayer.support.iOS.version < 11 || flowplayer.support.android.version < 5 ) { // tested on Android 6
            root.find('.fp-speed').hide();

            player.YTErrorTimeout = setTimeout( function() {
              if( !player.error && youtube.getPlayerState() == -1 ) {  //  exp: the onError event sometimes won't fire :( (Safari 11 most of the time)
                player.trigger('error', [ player, { code: 4, video: player.video } ] );
              }
            }, 1000 );
          }
        }
        
        
        function onStateChange(e) {//console.log('onStateChange '+e.data+' '+ ( e.target ? jQuery('.flowplayer').index(jQuery(e.target.getIframe()).parents('.flowplayer')) : false ) );
          if( root.find('.fv-fp-no-picture.is-active').length == 0 ) jQuery('.fvyoutube-engine',root).show();

          switch (e.data) {          
            case -1:  //  exp: means "unstarted", runs for playlist item change
              jQuery('.fp-splash',root).css('pointer-events',''); //  exp: for random playlist autoplay
              //player.ready = false;  //  todo: causes ready event on playlist advance - should it be there?
              
              // we need to set the status properly, what if the VAST ad loads before YouTube engine does, it must be able to resume the video
              player.playing = false;
              player.paused = true;

              // The video might not be playable, it might be set to start in XY hours
              // Unfortunately this information is not part of any of the get* calls on youtube
              // So we just check again if the video is still in the -1 status
              // If it is, then we show the UI to make sure the "Live in XY hours" message is visible
              setTimeout( function() {
                var fresh_status = youtube.getPlayerState();
                if( fresh_status == -1 ) {
                  fv_player_log('This video did not start yet!');

                  root.removeClass('is-youtube-nl');
                }
              }, 1000 );
              break;

            case YT.PlayerState.BUFFERING:    //  3, seems to me we don't need this at all
              if( typeof(youtube.getCurrentTime) == "function") {
                player.trigger('seek', [player, youtube.getCurrentTime()] );
              }
              break;
            
            case YT.PlayerState.CUED:         //  5
              root.removeClass('is-loading');
              root.addClass('is-paused');
              player.loading = false;  //  exp: without this the core Flowplayer will think the player is still loading and wont' allow iphone users to click the playlist thumbs more than twice
              
              if( !flowplayer.support.firstframe  ) { // todo: this whole part doesn't make sense anymore, as .fv-pf-yt-temp is no more, but it should be
                var playlist_item = jQuery('[rel='+root.attr('id')+'] span').eq(player.video.index);                  
                jQuery('.fv-pf-yt-temp',root).css('background-image', playlist_item.css('background-image') );
                if( !flowplayer.support.dataload ) jQuery('.fp-ui',root).hide(); //  exp: hide the UI so that the iframe can be clicked into on iPad
                jQuery('.fv-pf-yt-temp',root).show();
                jQuery('.fv-pf-yt-temp-play',root).show();
              }
              
              break;
            
            case YT.PlayerState.ENDED:  //  0
              player.playing = false;
              
              // TODO: Sometimes the end time is missing 1 second to match the duration
              // However the same issue appears on https://www.youtube.com/watch?v=QRS8MkLhQmM
              // where the video loads as having duration of 1:37 which then changes to 1:36 in a second
              clearInterval(intUIUpdate);
              intUIUpdate = false;

              player.trigger( "pause", [player] );  //  not sure why but Flowplayer HTML5 engine triggers pause event before the video finishes
              player.trigger( "finish", [player] );
              
              jQuery('.fvyoutube-engine',root).hide();
              
              jQuery('.fv-pf-yt-temp2',root).show();
              jQuery('.fp-ui',root).show();
              break;                
            
            case YT.PlayerState.PAUSED:   //  2

              // Was it paused because of unmuting?
              if( player.youtube_unmute_attempted === 1 ) {
                player.youtube_unmute_attempted = 2;
                fv_player_log('FV FP YouTube: Volume restore failed.');

                player.mute(true,true); // mute, but don't remember it!
                youtube.playVideo();

                jQuery('body').one('click', function() {
                  if( player && player.ready ) {
                    fv_player_log('FV FP YouTube: Volume restore on click.');

                    player.volume(player.volumeLevel); // unmute
                  }
                });
                return;
              }

              if( player.seeking ) {
                youtube.playVideo();
                return;
              }
              
              clearInterval(intUIUpdate);
              intUIUpdate = false;
              player.trigger( "pause", [player] );                                    
              break;
            
            case YT.PlayerState.PLAYING:    //  1
              triggerVideoInfoUpdate();
              onReady();
              triggerUIUpdate();
              if( isMobile ) {
                var ui = jQuery('.fp-ui',root);          
                ui.show();
                jQuery('.fp-splash',root).css('pointer-events',''); //  iPad iOS 7 couldn't pause video after it started
                if( !jQuery(root).hasClass('no-controlbar') && fv_player_pro_youtube_is_old_android() || flowplayer.support.iOS && flowplayer.support.iOS.version < 10 ) {
                  ui.hide();
                }
              }
              if( player.seeking ) {
                player.seeking = false;
                
                //  todo: stop progress event perhaps
                if( typeof(youtube.getCurrentTime) == "function") {                      
                  player.trigger('seek', [player, youtube.getCurrentTime()] );
                }
              }
              
              if( player.paused ) {
                player.trigger( "resume", [player] );
              }

              // Without this delay we cannot be sure the youtube.isMuted() reports properly in playlists
              player.one('progress', function() {
                if( !player.youtube_unmute_attempted && youtube.isMuted() ) {
                  fv_player_log('FV FP YouTube: Trying to restore volume to '+player.volumeLevel);

                  player.volume(player.volumeLevel); // unmute

                  // used to try to unmute the video once paused due to "unmuting failed and the element was paused instead because the user didn't interact with the document before."
                  player.youtube_unmute_attempted = 1;
                  // But it has to pause quickly, what if user paused the video?
                  setTimeout( function() {
                    player.youtube_unmute_attempted = false;
                  }, 500 );
                }
              } );

              // Hide UI again if it was shown previously
              // To show the "Live in XY hours" message
              if( window.fv_player_pro && fv_player_pro.youtube_nl ) {
                root.addClass('is-youtube-nl');
              }

              break;
            
          }
                         
        }        
      
        
        function triggerUIUpdate() {
          var P_previous = false;
          if( intUIUpdate ) return;
          intUIUpdate = setInterval(function () {
            if( typeof(youtube) == "undefined" || typeof(youtube.getCurrentTime) == "undefined" ){
              return;
            }
            
            var P = youtube.getCurrentTime(); 
            
            if( isMobile ) {  //  YouTube sometimes doesn't fire the event to signal that the seeking was finished on iPad      
              if( typeof(player.seeking) != "undefined" && player.seeking && P_previous && P_previous < P ) {
                //player.seeking = false;
                player.trigger('seek', [player] );
              }
              P_previous = P;
            }
            
            var time = player.video.time = (P > 0) ? P : 0;
            
            // for some YouTube Live streams we might get the current time of even
            // 500 days! If we pass that to progress event below, it would result
            // in checking the cuepoints for too long and stalling the browser:
            // https://github.com/flowplayer/flowplayer/blob/d5b70e7a40518582287d9b73aa76ea568c948816/lib/ext/cuepoint.js#L24-L31
            // So we start from 0 here! 
            //
            // TODO: What about FV Player Pro custom start time?
            if( player.live ) {
              if( live_stream_start_time == 0 ) {
                live_stream_start_time = time;
              }
              time = time - live_stream_start_time;
            }

            player.trigger("progress", [player, time] );
            var buffer = youtube.getVideoLoadedFraction() * player.video.duration + 0.5;
            if( buffer < player.video.duration && !player.video.buffered) {
                player.video.buffer = buffer;
                player.trigger("buffer", [player, player.video.buffer ] );
            } else if (!player.video.buffered) {
                player.video.buffered = true;
                player.trigger("buffer", [player, player.video.buffer ] ).trigger("buffered", [player]);
            }
            
          }, 250);

        }
        
        
        function triggerVideoInfoUpdate() {
          //if( engine.playing ) return;
          //engine.playing = true;

          jQuery.extend(player.video, getVideoDeatils(youtube) );              
        }

      
        var aResolutions = {
              'small': { width: 320, height: 240, bitrate: 64 },
              'medium': { width: 640, height: 360, bitrate: 512 },
              'large': { width: 854, height: 480, bitrate: 640 },
              'hd720': { width: 1280, height: 720, bitrate: 2000 },
              'hd1080': { width: 1920, height: 1080, bitrate: 4000 }
            },    
            aQuality = {
               bitrates: false,
               defaultQuality: "default",
               activeClass: "active",
               qualityLabels: {
                   medium: 'medium',
                   large: 'large',
                   'hd720': 'hd'
               }
            },
            common = flowplayer.common,
            intUIUpdate = false,
            isMobile = fv_player_pro_youtube_is_mobile(),
            loadVideo,
            root = jQuery(root),
            youtube,
            live_stream_start_time = 0;

        var engine = {
            engineName: engineImpl.engineName,

            load: function (video) {
                loadVideo = video;
                live_stream_start_time = 0;
              
                var video_id = fv_player_pro_youtube_get_video_id(video.src);
                if( !video_id ){
                  root.find('.fp-ui').append('<div class="fp-message"><h2>' + fv_flowplayer_translations.invalid_youtube + '</h2></div>');
                  root.addClass('is-error').removeClass('is-loading');
                  //  todo: trigger error event in a normal way?
                  return;
                }

                if( youtube ) {//console.log('YT already loaded');
                  if( !flowplayer.support.dataload && !flowplayer.support.inlineVideo  ) {  //  exp: for old iOS
                    youtube.cueVideoById( video_id, 0, 'default' );
                  } else {//console.log('y 2');
                    youtube.loadVideoById( video_id, 0, 'default' );
                  }                      
                  
                } else if( player.youtube && player.youtube.getIframe() ) { // youtube and its iframe exists - was not destroyed
                  //console.log('YT preloaded',player.youtube.getIframe());
                  youtube = player.youtube;
                  
                  //  this removes the start-up event listeners   
                  player.fv_yt_onReady();
                  player.fv_yt_onStateChange();
                  player.fv_yt_onError();
                  
                  youtube.addEventListener('onReady',onReady);
                  youtube.addEventListener('onStateChange',onStateChange);
                  youtube.addEventListener('onError',onError);
                  youtube.addEventListener('onApiChange',onApiChange);
                  if( !flowplayer.support.dataload && !flowplayer.support.inlineVideo  ) { //  exp: for old iOS
                    youtube.cueVideoById( video_id, 0, 'default' );
                    
                    //  exp: we just changed the video to something else, so we need to let it process it
                    setTimeout( function() {
                      onReady();        
                    },100); // todo: find some better way!                        
                  } else {
                    youtube.loadVideoById( video_id, 0, 'default' );
                  }
                  
                } else {//console.log('YT not yet loaded');
                  common.removeNode(common.findDirect("video", root)[0] || common.find(".fp-player > video", root)[0]);
                  var wrapperTag = common.createElement("div");    
                  wrapperTag.className = 'fp-engine fvyoutube-engine';
                  common.prepend(common.find(".fp-player", root)[0], wrapperTag);
                     
                  var intLoad = setInterval( function() {
                    if( typeof(YT) == "undefined" || typeof(YT.Player) == "undefined" ) {
                      //console.log('YT not awaken yet!');
                      return;
                    }
                    
                    clearInterval(intLoad);

                    /*var had_youtube_before = 
                      jQuery('presto-player[src*=\\.youtube\\.com], presto-player[src*=\\.youtu\\.be], presto-player[src*=\\.youtube-nocookie\\.com]').length ||
                      jQuery('iframe[src*=\\.youtube\\.com], iframe[src*=\\.youtu\\.be], iframe[src*=\\.youtube-nocookie\\.com]').length;*/
                    
                    youtube = new YT.Player(
                      wrapperTag,
                      fv_player_pro_youtube_player_vars(video_id, root, {
                        onReady: onReady,
                        onStateChange: onStateChange,
                        onError: onError,
                        onApiChange: onApiChange,
                      })
                    );
                                        
                    /*if( had_youtube_before ) {
                      //youtube.loadVideoById( video_id, 0, 'default' );

                      setTimeout( function() {
                        onReady();        
                      },1000);
                    }

                    console.log(youtube);*/
             
                    var iframe = jQuery('.fp-engine.fvyoutube-engine',root);
                    iframe[0].allowFullscreen = false;
                    /* in Chrome it's possible to double click the video entery YouTube fullscreen that way. Cancelling the event won't help, so here is a pseudo-fix */
                    iframe.on("webkitfullscreenchange", function() {
                      if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                      }
                      return false;
                    });
                  }, 5 );
                }
                
                //  exp: only needed if we decide not to use standard player for iPad etc.
                //  copy of original Flowplayer variable declarations
                var FS_ENTER = "fullscreen",
                  FS_EXIT = "fullscreen-exit",
                  FS_SUPPORT = flowplayer.support.fullscreen,
                  win = window,
                  scrollX,
                  scrollY;
                  
                //  copy of original Flowplayer function                
                player.fullscreen = function(flag) {
                  var wrapper = jQuery(root).find('.fp-player')[0];
                  
                  if (player.disabled) return;
            
                  if (flag === undefined) flag = !player.isFullscreen;
            
                  if (flag) {
                    scrollY = win.scrollY;
                    scrollX = win.scrollX;
                  }
            
                  if (FS_SUPPORT) {
            
                     if (flag) {
                        ['requestFullScreen', 'webkitRequestFullScreen', 'mozRequestFullScreen', 'msRequestFullscreen'].forEach(function(fName) {
                           if (typeof wrapper[fName] === 'function') {
                              wrapper[fName](Element.ALLOW_KEYBOARD_INPUT);
                              if (fName === 'webkitRequestFullScreen' && !document.webkitFullscreenElement)  { // Element.ALLOW_KEYBOARD_INPUT not allowed
                                 wrapper[fName]();
                              }
                              return false;
                           }
                        });
            
                     } else {
                        ['exitFullscreen', 'webkitCancelFullScreen', 'mozCancelFullScreen', 'msExitFullscreen'].forEach(function(fName) {
                          if (typeof document[fName] === 'function') {
                            document[fName]();                            
                          }
                        });
                     }
            
                  } else {
                     player.trigger(flag ? FS_ENTER : FS_EXIT, [player]);
                  }
                  
                  return player;
                };
                
                player.on('fullscreen-exit', function() {
                  win.scrollTo(scrollX, scrollY);
                });
            },
            
            mute: function(flag) {
              if( typeof(youtube) == "undefined" ) return;
              player.muted = !!flag;
              if( flag ) youtube.mute(); else youtube.unMute();
              player.trigger('mute', [player, flag]);
            },
                    
            pause: function () {
              clearInterval(player.YTErrorTimeout);
              youtube.pauseVideo();
            },                
            
            pick: function (sources) {
              var i, source;
              for (i = 0; i < sources.length; i = i + 1) {
                source = sources[i];
                if( source.src.match(/(youtube\.com|youtube-nocookie\.com|youtu\.be)/) ) {
                  if(source.src.match(/\/shorts\//)) {
                    source.src = source.src.replace('/shorts/', '/watch?v=') // replace shorts with /watch?v=
                  }

                  return source;
                }
              }
            },

            resume: function () {
              if( player.finished ) {
                //videoTag.currentTime = 0;
              }
              if( typeof(youtube.playVideo) != "undefined" ) {
                youtube.playVideo();
              }   
            },

            seek: function (time) {
              youtube.seekTo(time, true);                
              player.seeking = true;                
              loadVideo.currentTime = time;
              triggerUIUpdate();          
            },

            speed: function (val) {
              youtube.setPlaybackRate( parseFloat(val) );
              player.trigger('speed', [player, val]);
            },
            
            stop: function() {
              youtube.stopVideo();
            },

            unload: function () { //  todo: using youtube.stopVideo breaks things, no good experience with youtube.destroy either
              //engine.playing = false;           
              
              clearInterval(intUIUpdate);                            
              
              if( !fv_player_pro_youtube_is_mobile() ) {
                youtube.destroy();                             
              } else {//console.log('YT mobile unload');
                youtube.stopVideo(); //  exp. engine.youtube is somehow undefined here?
              }
              player.youtube_unmute_attempted = false;
              
              //player.trigger("unload", [engine.player]);
              
              player.one( 'load', function(e,api) {
                if( !fv_player_pro_youtube_is_mobile() || api.engine.engineName == 'fvyoutube' ) return;
                
                clearInterval(intUIUpdate);
                youtube.destroy();
                player.youtube = false;
                
                jQuery('.fvyoutube-engine',root).remove();
                jQuery('.fv-pf-yt-temp2',root).remove();
                jQuery(root).removeClass('is-ytios11');
                
                //  exp: if the next video is not YouTube, iPad will have issues loading it as there was no video element on the page previously
                //e.preventDefault();
                /*jQuery('.fp-ui',root).css('background-image','');
                jQuery(root).removeClass('is-loading');
                jQuery(root).removeClass('is-mouseover');
                jQuery(root).addClass('is-mouseout');
                jQuery('.fp-ui',root).append('<div class="wpfp_custom_popup fp-notice-load" style="height: 100%"><div class="wpfp_custom_popup_content">' + fv_flowplayer_translations.video_loaded + '</div></div>'); //  we show this so that we can capture the user click              
                
                api.loading = false;
                
                var i = api.video.index;
                jQuery('.fp-notice-load').one( 'click', function(e) {                                            
                  jQuery('.fp-notice-load',root).remove();
                  
                  var api = jQuery(root).data('flowplayer');
                  api.loading = false;
                  api.error = false;
                  api.play(i);
                } );*/

              });
              
              if( !flowplayer.support.firstframe ) {  // prevent playback of the next video on iOS 9 and so on
                player.one( 'ready', function(e,api) {
                  api.stop();
                });
              }
            },
            
            volume: function (level) {
              if( typeof(youtube.setVolume) == "function" ) {
                if( level > 0 ) player.mute(false);
                player.volumeLevel = level;
                youtube.setVolume( level * 100 );
                player.trigger("volume", [player, level]);
              }
            },
            
        };

        // When the lightbox is closing or switching frames we need to get rid of YouTube as fancyBox moves the player HTML when closing,
        // which means that the iframe content loads again and YouTube video starts playing.
        jQuery(document).on('afterClose.fb beforeLoad.fb', function() {
          if( youtube && (player.lightbox_visible && !player.lightbox_visible()) && (player.is_in_lightbox && player.is_in_lightbox()) ) {
            // Using player.unload() won't work as the player is not in the splash state
            player.trigger("unload", [player]);

            youtube.destroy();
            youtube = false;
          }
        });
        
        return engine;
    };

    engineImpl.engineName = 'fvyoutube';
    engineImpl.canPlay = function (type) {
      return /video\/youtube/i.test(type);
    };
    flowplayer.engines.push(engineImpl);
    
    flowplayer( function(api,root) {
      if( jQuery(root).hasClass('lightboxed') ) return;

      if( fv_player_pro_youtube_is_mobile() ) {
        // Give Flowplayer a bit of time to finish initializing, like the unload event for splash state players has to finish
        setTimeout( function() {
          fv_player_pro_youtube_preload(root,api);
        });
      }
    });
    
    jQuery(document).ready( function() {
      if( fv_player_pro_youtube_is_mobile() ) {  //  in Flowplayer 7 Andoird and iOS thinks it can autoplay
        jQuery(document).on( 'afterShow.fb', function() {
          jQuery('.fancybox-slide--current .flowplayer').each( function() {
            fv_player_pro_youtube_preload(this);  //  todo: fix if you are opening the lightbox the second time
          })
        });
      }    
    });

  }());

}




/*
 * YouTube has a limited set of speed settings available and we need to handle special case when a playlist of YouTube, MP4 is started by clicking the 2nd item (MP4)
 */
if (typeof (flowplayer) !== 'undefined'){
  flowplayer(function(api, root) {
    api.on('ready beforeseek', function() {
      if( api.engine.engineName == 'fvyoutube' ) {
        if( typeof(api.youtube) !== 'undefined' && typeof(api.youtube.getAvailablePlaybackRates) == "function" ) {
          api.conf.backupSpeeds = api.conf.speeds;
          api.conf.speeds = api.youtube.getAvailablePlaybackRates();
        }
      } else {
        if( api.youtube ) { // what happens if you play a vdeo which is not YouTube and the YouTube API is still up, needed for mobile          
          api.youtube.destroy();
          api.youtube = false;          
          jQuery('.fp-ui',root).css('background-image','');
          jQuery('.fvyoutube-engine',root).remove();
          jQuery('.fv-pf-yt-temp2',root).remove();
          jQuery(root).removeClass('is-ytios11');
        }
      
        if(typeof(api.conf.backupSpeeds) !== 'undefined'){
          api.conf.speeds = api.conf.backupSpeeds;
        }
      }
    })
  
    // buddyboss-theme - prevent adding div to player root
    if( typeof(jQuery.fn.fitVids) != 'undefined' ) {
      jQuery(root).addClass('fitvidsignore');
    }
  
  })
}
