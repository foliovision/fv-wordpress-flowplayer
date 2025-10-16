/*jslint browser: true, for: true */
/*global dashjs, flowplayer, MediaPlayer, window */

/*!

   DASH engine plugin for Freedom Video Player

   Copyright (c) 2016-2025, Foliovision

   Released under the MIT License:
   http://www.opensource.org/licenses/mit-license.php

   Includes dash.js
   Copyright (c) 2015, Dash Industry Forum. All rights reserved.
   https://github.com/Dash-Industry-Forum/dash.js/blob/master/LICENSE.md

   Requires Freedom Video Player version 8.x
   $GIT_DESC$

*/
(function () {
  "use strict";
  var extension = function (dashjs, flowplayer) {
      var engineName = "dash",
          mse = window.MediaSource || window.WebKitMediaSource,
          UA = navigator.userAgent,
          common = flowplayer.common,
          extend = flowplayer.extend,
          dashconf,
          trigger_finish,

          dashCanPlay = function (sourceType, dashType, dashCodecs) {
              return sourceType.toLowerCase() === "application/dash+xml" &&
                      mse.isTypeSupported(dashType + ';codecs="' + dashCodecs + '"') &&
                      // Android MSE advertises he-aac, but fails
                      (dashCodecs.indexOf("mp4a.40.5") < 0 || UA.indexOf("Android") < 0);
          },

          engineImpl = function dashjsEngine(player, root) {
              var bean = flowplayer.bean,
                  support = flowplayer.support,
                  brwsr = support.browser,
                  desktopSafari = brwsr.safari && support.dataload, // exclude IEMobile
                  mediaPlayer,
                  videoTag,
                  safariAutoplayFix = false,
                  handleError = function (errorCode, src, url) {
                      var errobj = {code: errorCode};

                      if (errorCode > 2) {
                          errobj.video = extend(player.video, {
                              src: src,
                              url: url || src
                          });
                      }
                      return errobj;
                  },

                  lastSelectedQuality = -1,
                  initQualitySelection = function (dashQualitiesConf, initialVideoQuality, data) {
                      // multiperiod not supported
                      var vsets = [],
                          qualities,
                          audioBandwidth = 0,
                          getLevel = function (q) {
                              return isNaN(Number(q))
                                  ? q.level
                                  : q;
                          },
                          setInitialVideoQuality = function (initialVideoQuality, vsets, qsel, minimal ) {
                              initialVideoQuality = Math.min(initialVideoQuality, vsets.length - 1);
                              if( initialVideoQuality == -1 ) {
                                mediaPlayer.updateSettings({
                                  'streaming': {
                                    'abr': {
                                      'autoSwitchBitrate': { video: true },
                                      'limitBitrateByPortal': true,
                                      'usePixelRatioInLimitBitrateByPortal': true
                                    }
                                  }
                                });
                              } else if ( minimal ) {
                                mediaPlayer.updateSettings({
                                  'streaming': {
                                    'abr': {
                                      'autoSwitchBitrate': { video: true },
                                      'minBitrate': { video: vsets[initialVideoQuality].bandwidth / 1000 }
                                    }
                                  }
                                });
                              } else {
                                mediaPlayer.updateSettings({
                                  'streaming': {
                                    'abr': {
                                      'autoSwitchBitrate': { video: false },
                                      'initialBitrate': { video: vsets[initialVideoQuality].bandwidth / 1000 }
                                    }
                                  }
                                });
                              }
                              
                              if (qsel) {
                                player.video.quality = initialVideoQuality;
                              }
                              
                          },
                          qselConf = dashQualitiesConf && support.inlineVideo &&
                                  data.Period_asArray.length === 1 &&
                                  (!desktopSafari || (desktopSafari && dashconf.qualitiesForSafari));

                      if (!qselConf) {
                          return;
                      }

                      data.Period_asArray[0].AdaptationSet_asArray.forEach(function (aset) {
                          var representations = aset.Representation_asArray,
                              mimeType = aset.mimeType || representations[0].mimeType;

                          if (mimeType.indexOf("video/") === 0) {
                              vsets = vsets.concat(representations.filter(function (repr) {
                                  var codecs = (repr.mimeType || mimeType) + ";codecs=" + repr.codecs;

                                  return mse.isTypeSupported(codecs);
                              }));
                          } else if (mimeType.indexOf("audio/") === 0 && !audioBandwidth) {
                              // too simple: audio tracks may have different bitrates
                              audioBandwidth = representations[0].bandwidth;
                          }
                      });
                      if (vsets.length < 2) {
                          return;
                      }

                      vsets.sort(function (a, b) {
                          return a.bandwidth - b.bandwidth;
                      });

                      if( localStorage.FVPlayerDashQuality ) {
                        Object.keys(vsets).forEach(function (key) { // loop through the qualities and pick what's best match
                          if( player.conf.dash && player.conf.dash.initialVideoQuality == 'restore' && localStorage.FVPlayerDashQuality <= vsets[key].height ) {
                            initialVideoQuality = player.conf.dash.initialVideoQuality = key;
                            console.log('restoring quality '+localStorage.FVPlayerDashQuality+' => '+key);
                          }
                        });
                        if( player.conf.dash && player.conf.dash.initialVideoQuality == 'restore' ) initialVideoQuality = player.conf.dash.initialVideoQuality = vsets.length - 1; // if nothing has been found, pick the biggest
                      }

                      if (!qselConf) {
                          setInitialVideoQuality(initialVideoQuality, vsets);
                          return;
                      }

                      switch (typeof dashQualitiesConf) {
                      case "object":
                          qualities = dashQualitiesConf.map(getLevel);
                          break;
                      case "string":
                          qualities = dashQualitiesConf.split(/\s*,\s*/).map(Number);
                          break;
                      default:
                          qualities = vsets.map(function (_repr, i) {
                              return i;
                          });
                          qualities.unshift(-1);
                      }
                      qualities = qualities.filter(function (q) {
                          return q < vsets.length && q > -2;
                      });

                      if (qualities.length < 2) {
                          return;
                      }

                      player.video.qualities = qualities.map(function (idx) {
                          var level = vsets[idx],
                              q = typeof dashQualitiesConf === "object"
                                  ? dashQualitiesConf.filter(function (q) {
                                      return getLevel(q) === idx;
                                  })[0]
                                  : idx,
                              label = q.label || (idx < 0
                                  ? "Auto"
                                  : Math.min(level.width, level.height) + "p " +
                                          "(" + Math.round((level.bandwidth + audioBandwidth) / 1000) + "k)");

                          return {value: idx, label: label};
                      });
                      
                      if( flowplayer.conf.hd_streaming && !flowplayer.support.fvmobile ) {
                        var hd_quality = -1;
                        Object.keys(vsets).forEach( function(k) {
                          if( hd_quality == -1 && ( vsets[k].height >= 720 || vsets[k].width >= 1280 ) ) {
                            hd_quality = k;
                          }
                        });
                        setInitialVideoQuality(hd_quality, vsets, true, true);
                      } else {
                        setInitialVideoQuality(initialVideoQuality, vsets, true);
                      }

                  },
                  keySystem,

                  bc,
                  has_bg,

                  engine = {
                      engineName: engineName,

                      pick: function (sources) {
                          var source = sources.filter(function (s) {
                              var dashType = s.dashType || dashconf.type,
                                  dashCodecs = s.dashCodecs || dashconf.codecs;

                              return dashCanPlay(s.type, dashType, dashCodecs);
                          })[0];

                          if (typeof source.src === 'string') {
                              source.src = common.createAbsoluteUrl(source.src);
                          }
                          return source;
                      },

                      load: function (video) {
                          var conf = player.conf,
                              dashUpdatedConf = extend(dashconf, conf.dash, video.dash),
                              dashQualitiesConf = video.dashQualities || conf.dashQualities,
                              EVENTS = {
                                  ended: "finish",
                                  loadeddata: "ready",
                                  pause: "pause",
                                  play: "resume",
                                  progress: "buffer",
                                  ratechange: "speed",
                                  seeked: "seek",
                                  timeupdate: "progress",
                                  volumechange: "volume",
                                  error: "error",
                                  waiting: "waiting"
                              },
                              DASHEVENTS = dashjs.MediaPlayer.events,
                              protection = video.dash && video.dash.protection,
                              autoplay = !!video.autoplay || !!conf.autoplay || !!conf.splash,
                              posterClass = "is-poster",
                              livestartpos = 0;

                          if (video.dashQualities === false) {
                              dashQualitiesConf = false;
                          } else if (dashQualitiesConf === undefined) {
                              dashQualitiesConf = true;
                          }

                          if (!mediaPlayer) {
                              videoTag = common.findDirect("video", root)[0]
                                      || common.find(".fp-player > video", root)[0];

                              if (videoTag) {
                                  // destroy video tag
                                  // otherwise <video autoplay> continues to play
                                  common.find("source", videoTag).forEach(function (source) {
                                      source.removeAttribute("src");
                                  });
                                  videoTag.removeAttribute("src");
                                  videoTag.load();
                                  common.removeNode(videoTag);
                              }

                              // dash.js enforces preload="auto" and
                              // autoplay depending on initialization
                              // so setting the attributes here will have no effect
                              videoTag = common.createElement("video", {
                                  "class": "fp-engine " + engineName + "-engine"
                              });

                              Object.keys(EVENTS).forEach(function (key) {
                                  var flow = EVENTS[key],
                                      type = key + "." + engineName,
                                      arg;

                                  bean.on(videoTag, type, function (e) {
                                      if (conf.debug && flow.indexOf("progress") < 0) {
                                          console.log(type, "->", flow, e.originalEvent);
                                      }

                                      var vct = videoTag.currentTime,
                                          ct = (mediaPlayer.time && mediaPlayer.time()) || vct,
                                          dur = mediaPlayer.duration(),
                                          buffered = videoTag.buffered,
                                          buffends = [],
                                          i,
                                          updatedVideo = player.video,
                                          src = updatedVideo.src,
                                          errorCode;

                                      switch (flow) {
                                      case "ready":
                                          arg = extend(updatedVideo, {
                                              duration: dur,
                                              seekable: dur,
                                              width: videoTag.videoWidth,
                                              height: videoTag.videoHeight,
                                              url: src
                                          });
                                          break;
                                      case "seek":
                                          arg = ct;
                                          break;
                                      case "progress":
                                          if (player.live && !player.dvr) {
                                              if (!livestartpos && vct) {
                                                  livestartpos = vct;
                                              }
                                              arg = vct - livestartpos;
                                          } else {
                                              arg = ct;
                                          }
                                          break;
                                      case "speed":
                                          // dash.js often triggers playback rate changes
                                          // when adapting bit rate
                                          // except when in debug mode, only
                                          // trigger explicit events via speed method
                                          if (!dashUpdatedConf.debug) {
                                              e.preventDefault();
                                              return;
                                          }
                                          arg = videoTag.playbackRate;
                                          break;
                                      case "volume":
                                          arg = videoTag.volume;
                                          break;
                                      case "buffer":
                                          for (i = 0; i < buffered.length; i += 1) {
                                              buffends.push(buffered.end(i));
                                          }
                                          arg = buffends.filter(function (b) {
                                              return b >= ct;
                                          }).sort()[0];
                                          updatedVideo.buffer = arg;
                                          if( typeof(arg) == "undefined" ) return;
                                          break;
                                      case "error":
                                          errorCode = videoTag.error && videoTag.error.code;
                                          arg = handleError(errorCode, src);
                                          break;                                        
                                      case "waiting":
                                          if( videoTag.currentTime > videoTag.duration - 0.5 ) { // if you get this event at the end of video, it means Dash JS has stalled, probably because of shorter last segment duration. The video should end.
                                            console.log('FV Player DASH: Triggering video end!');
                                            player.trigger('finish', [player]);
                                          }
                                          break;
                                      }

                                      player.trigger(flow, [player, arg]);
                                  });
                              });

                              player.on("error." + engineName, function () {
                                  if (mediaPlayer) {
                                      player.engine.unload();
                                  }
                              });

                              player.on("quality." + engineName, function (_e, _api, q) {
                                mediaPlayer.updateSettings({ 'streaming': {
                                  'abr': {
                                    'autoSwitchBitrate': { video: q < 0 },
                                    'limitBitrateByPortal': q == -1,
                                    'usePixelRatioInLimitBitrateByPortal': q == -1
                                  }
                                } });
                                if (q > -1) {
                                  mediaPlayer.setQualityFor("video", q);
                                }
                                lastSelectedQuality = q;
                              });

                              common.prepend(common.find(".fp-player", root)[0], videoTag);

                          } else {
                              mediaPlayer.reset();
                          }

                          mediaPlayer = dashjs.MediaPlayer().create();
                          player.engine[engineName] = mediaPlayer;

                          if (protection) {
                              mediaPlayer.setProtectionData(protection);
                              mediaPlayer.on(dashjs.Protection.events.KEY_SYSTEM_SELECTED, function (e) {
                                  keySystem = e.data.keySystem.systemString;
                              });
                          }
                          // caching can cause failures in playlists
                          // for the moment disable entirely
                          mediaPlayer.updateSettings({
                            'streaming': {
                              'fastSwitchEnabled': UA.indexOf("Trident/7") < 0,
                              'lastBitrateCachingInfo': {
                                'enabled': false
                              },
                              'scheduleWhilePaused': true, // for seeking in paused state
                              'useSuggestedPresentationDelay': dashUpdatedConf.useSuggestedPresentationDelay
                            }
                          });
                          
  
                          //mediaPlayer.getDebug().setLogToBrowserConsole(dashUpdatedConf.debug);
                          // live
                          if (typeof dashUpdatedConf.liveDelay === "number") {
                            mediaPlayer.updateSettings({
                              'streaming': {
                                'liveDelay': dashUpdatedConf.liveDelay
                              }
                            });
                          }
                          if (typeof dashUpdatedConf.liveDelayFragmentCount === "number") {
                            mediaPlayer.updateSettings({
                              'streaming': {
                                'liveDelayFragmentCount': dashUpdatedConf.liveDelayFragmentCount
                              }
                            });
                          }

                          if (dashUpdatedConf.xhrWithCredentials && dashUpdatedConf.xhrWithCredentials.length) {
                              dashUpdatedConf.xhrWithCredentials.forEach(function (requestType) {
                                  mediaPlayer.setXHRWithCredentialsForType(requestType, true);
                              });
                          }

                          Object.keys(DASHEVENTS).forEach(function (key) {
                              var etype = DASHEVENTS[key],
                                  fpEventType = engineName + etype.charAt(0).toUpperCase() + etype.slice(1),
                                  listeners = dashUpdatedConf.listeners,
                                  expose = listeners && listeners.indexOf(fpEventType) > -1;

                              mediaPlayer.on(etype, function (e) {
                                  var src = player.video.src,
                                      videoDashConf = player.video.dash,
                                      loadingClass = "is-loading",
                                      errors = player.conf.errors,
                                      protectionError = "None of the protection key systems supported. Try a different browser.",
                                      fperr,
                                      errobj;

                                  //if( !key.match(/^(FRAGMENT|LOG|METRIC)/) ) console.log(key);

                                  switch (key) {
                                  case "MANIFEST_LOADED":
                                      if (brwsr.chrome && videoDashConf && videoDashConf.protectionLevel) {
                                          mediaPlayer.getProtectionController().setRobustnessLevel(videoDashConf.protectionLevel);
                                      }
                                      initQualitySelection(dashQualitiesConf,
                                              dashUpdatedConf.initialVideoQuality, e.data);
                                      break;
                                  case "CAN_PLAY":
                                      if (desktopSafari && autoplay) {
                                          // hack to avoid "heaving" in Safari
                                          // at least in splash setups and playlist transitions
                                          common.addClass(root, loadingClass);
                                          bean.one(videoTag, "timeupdate." + engineName, function () {
                                              setTimeout(function () {
                                                  common.removeClass(root, loadingClass);
                                              });
                                          });
                                      }
                                      break;
                                  case "BUFFER_LEVEL_STATE_CHANGED":
                                      common.toggleClass(root, "is-seeking", e.state === "bufferStalled");
                                      break;
                                  case "PLAYBACK_NOT_ALLOWED":
                                      if (!conf.mutedAutoplay) throw new Error('Unable to autoplay');
                                      player.debug('Play errored, trying muted', e);
                                      
                                      // In macOS Safari 13/14 we have to wait a bit               
                                      if( desktopSafari ) {
                                          console.log('FV Player: Safari autoplay of Dash video blocked, retrying...');
                                          
                                          // So we use this special flag
                                          safariAutoplayFix = true;

                                      } else {
                                          player.mute(true, true);
                                          if( videoTag ) {
                                              videoTag.volume = 0;
                                          }
                                          player.play();
                                      }
                                      break;                                        
                                  case "ERROR":
                                      // TODO: handle different e.error.code values
                                      errobj = handleError(4, src);
                                      player.trigger('error', [player, errobj]);
                                      break;
                                  case "PLAYBACK_PAUSED":
                                      if( flowplayer.support.browser.chrome && !flowplayer.support.android ) {
                                          //console.log( 'Dash paused!', player.loading, player.ready, player.paused, player.playing );
                                          
                                          // If the element received pause before it stared playing,
                                          // We are dealing with autoplay blocked
                                          if( player.loading && player.paused && !player.ready && !player.playing ) {
                                              // In that case we wait for the ready event
                                              player.one('ready', function(e,api) {
                                                  // And then signal that the video was paused
                                                  api.trigger('pause');
                                                  
                                                  // TODO: But when you try to resume the video it still won't work, unless you do it 10 times.
                                              });
                                          }
                                      }
                                      break;
                                  // When the autoplay is not allowed on macOS Safari 13/14/15
                                  // This event takes 4 seconds to arrive, but calling what's below too soon won't play the video
                                  case "PLAYBACK_STALLED":
                                      if( safariAutoplayFix ) {
                                          safariAutoplayFix = false;
                                          
                                          console.log('FV Player: Safari autoplay of Dash video recovery');
                                          player.mute(true, true);
                                          videoTag.autoplay = true;
                                          player.paused = true;
                                          player.play();
  
                                      }
                                      break;
                                  }

                                  if (expose) {
                                      player.trigger(fpEventType, [player, e]);
                                  }
                              });
                          });

                          keySystem = null;

                          // update video object before ready
                          player.video = video;
                          if( parseFloat(dashjs.Version) >= 2.6 && video.manifest ) {
                            mediaPlayer.initialize(videoTag,false,autoplay);
                            
                            var DashParserFactory = dashjs.FactoryMaker.getClassFactory({ '__dashjs_factory_name': 'DashParser' });
                            var mpdParser = DashParserFactory({}).create( {
                              debug: new FV_Player_Dash_Js_DebugMock(),
                              errorHandler: {
                                manifestError: function(e,f,g,h) {
                                  console.log('FV Player DashParser error',e,h)
                                }
                              }
                            } );
                            var manifest = mpdParser.parse(video.manifest);
                            manifest.loadedTime = new Date;
                            mediaPlayer.attachSource(manifest);
                              
                          } else {
                            mediaPlayer.initialize(videoTag, video.src, autoplay);
                          }

                          if (!support.firstframe && support.dataload && !brwsr.mozilla &&
                                  autoplay && videoTag.paused) {
                              videoTag.load();
                          }
                      },

                      resume: function () {
                          mediaPlayer.play();
                      },

                      pause: function () {
                          mediaPlayer.pause();
                      },

                      seek: function (time) {
                          mediaPlayer.seek(time);
                      },

                      volume: function (level) {
                          if (videoTag) {
                              // when using FV Player Pro custom start time the level is often NaN somehow
                              // meaning the video doesn't unmute after starting
                              // so here's a hotfix
                              videoTag.volume = isNaN(level) ? 1 : level;
                          }
                      },

                      mute: function (flag) {
                          if (videoTag) {
                              // this is why we need this whole mute() function - to let it unmute the video tag properly when using with Safari 13/14
                              videoTag.muted = !!flag;
                              
                              // it's not fair, but somehow we have to work this hard to be able to unmute the video
                              // not sure why it's not needed in core FP engines
                              if( !!flag ) videoTag.volume = 0;
                              else if( localStorage.volume ) videoTag.volume = localStorage.volume;
                              player.trigger('volume', [player, videoTag.volume]);
                              player.trigger('mute', [player, flag]);
                              
                          }
                      },

                      speed: function (val) {
                          videoTag.playbackRate = val;
                          // see ratechange/speed event
                          player.trigger('speed', [player, val]);
                      },

                      unload: function () {
                          if (mediaPlayer) {
                              var listeners = "." + engineName;

                              mediaPlayer.reset();
                              mediaPlayer = 0;
                              player.off(listeners);
                              bean.off(root, listeners);
                              bean.off(videoTag, listeners);
                              common.removeNode(videoTag);
                              videoTag = 0;
                          }
                      }
                  };

              return engine;
          };

      if (mse && typeof mse.isTypeSupported === "function") {
          // only load engine if it can be used
          engineImpl.engineName = engineName; // must be exposed
          engineImpl.canPlay = function (type, conf) {
              /*
                WARNING: MediaSource.isTypeSupported very inconsistent!
                e.g. Safari ignores codecs entirely, even bogus, like codecs="XYZ"
                example avc3 main level 3.1 + aac_he: avc3.4d401f; mp4a.40.5
                example avc1 high level 4.1 + aac_lc: avc1.640029; mp4a.40.2
                default: avc1 baseline level 3.0 + aac_lc
              */
              // inject dash conf at earliest opportunity
              dashconf = extend({
                  type: "video/mp4",
                  codecs: "avc1.42c01e,mp4a.40.2",
                  initialVideoQuality: -1,
                  qualitiesForSafari: true
              }, conf[engineName], conf.clip[engineName]);

              return dashCanPlay(type, dashconf.type, dashconf.codecs);
          };

          // put on top of engine stack
          // so mpegedash is tested before html5
          flowplayer.engines.unshift(engineImpl);

      }

  };
  if (typeof module === 'object' && module.exports) {
      module.exports = extension.bind(undefined, require('dashjs'));
  } else if (window.dashjs && window.flowplayer) {
      extension(window.dashjs, window.flowplayer);
  }
}());

// Taken from https://github.com/Dash-Industry-Forum/dash.js/blob/bc3458fcb5765c4a08e7266e0898427dea5d2b91/test/unit/dash.DashParser.js
function FV_Player_Dash_Js_DebugMock () {
  let instance;
  let log = {};

  function getLogger(instance) {
      return {
          fatal: fatal.bind(instance),
          error: error.bind(instance),
          warn: warn.bind(instance),
          info: info.bind(instance),
          debug: debug.bind(instance)
      };
  }

  function fatal(param) {
      instance.log.fatal = param;
  }

  function error(param) {
      instance.log.error = param;
  }

  function warn(param) {
      instance.log.warn = param;
  }

  function info(param) {
      instance.log.info = param;
  }

  function debug(param) {
      instance.log.debug = param;
  }

  instance = {
      getLogger: getLogger,
      log: log
  };

  return instance;
}