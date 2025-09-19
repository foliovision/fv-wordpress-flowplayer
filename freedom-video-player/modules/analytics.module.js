/*global gtag, ga, _paq, _gat*/

/*
 *  Google Analytics improvements + heartbeat
 *  Also provides custom fv_track_* events
 */
flowplayer( function(api,root) {
  var root = jQuery(root),
      bean = flowplayer.bean,
      time = 0, last = 0, timer, event_name,
      tracked_subtitle_langs = [],
      last_video_index = 0;

  // Load analytics.js if ga.js is not already loaded
  if( typeof(ga) == 'undefined' && api.conf.fvanalytics && typeof(_gat) == 'undefined' && typeof(gtag) == 'undefined' ) {
    if( is_ga_4(api) ) {
      jQuery.getScript( { url: "https://www.googletagmanager.com/gtag/js?id=" + api.conf.fvanalytics , cache: true }, function() {
        window.dataLayer = window.dataLayer || [];

        window.gtag = function() {
          window.dataLayer.push(arguments);
        };

        window.gtag('js', new Date());
        window.gtag('config', api.conf.fvanalytics);
      });
    } else {
      jQuery.getScript( { url: "https://www.google-analytics.com/analytics.js", cache: true }, function() {
        ga('create', api.conf.fvanalytics, 'auto');
      });
    }
  }

  // Load Matomo if not already loaded when needed
  if( !window._paq && api.conf.matomo_domain && api.conf.matomo_site_id ) {
    var u="//"+api.conf.matomo_domain+"/";
    var _paq = window._paq = window._paq || [];
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', api.conf.matomo_site_id]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  }

  api.bind('progress', function(e,api,current) {
    // Only track the video if it played for at least 1 second
    if ( current > 1 ) {
      fv_track(e,api,current);
    }

  }).bind('finish ready ', function(e,api) {
    // Do not track video again if it's the finish event and the video is set to loop
    if ( 'finish' === e.type && api.conf.loop ) {
      return;
    }

    for( var j in fv_ga_events ) {
      if( !fv_ga_events.hasOwnProperty(j) ) continue;
      root.removeData('fv_track_'+fv_ga_events[j]);
    }

    // Reset tracked subtitle languages when changing playlist items
    var video_index = api.video.index || 0;
    if ( last_video_index !== video_index ) {
      tracked_subtitle_langs = [];
      last_video_index = video_index;
    }

  // TODO errors in GA4
  }).bind('error', function(e,api,error) {
    setTimeout( function() {
      if( !api.error ) return;

      var video = typeof(api.video) != "undefined" && typeof(api.video.src) != "undefined" ? api.video : false;
      if( !video && typeof(api.conf.clip) != "undefined" && typeof(api.conf.clip.sources) != "undefined" && typeof(api.conf.clip.sources[0]) != "undefined" && typeof(api.conf.clip.sources[0].src) != "undefined" ) video = api.conf.clip.sources[0];

      var name = fv_player_track_name(root,video);
      if( name && !name.match(/\/\/vimeo.com\/\d/) ) {
        if( is_ga_4( api ) ) {

        } else {
          fv_player_track( api, false, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", error.message, name );
        }
      }
    }, 100 );
  });

  api.bind("load unload", fv_track_seconds_played).bind("progress", function(e, api) {

    if (!api.seeking) {
       time += last ? (+new Date() - last) : 0;
       last = +new Date();
    }

    // Send the seconds played event periodically too. This replace the old "FV Player heartbeat" event
    if (!timer) {
      timer = setTimeout(function() {
        timer = null;
        fv_track_seconds_played( { type: 'heartbeat' } );
      }, 10*60*1000); // heartbeat every 10 minutes
    }

  }).bind("pause", function() {
      last = 0;
  });

  api.bind('shutdown', function() {
    bean.off(window, 'visibilitychange pagehide', fv_track_seconds_played);
  });

  bean.on(window, 'visibilitychange pagehide', fv_track_seconds_played);

  var fv_ga_events = is_ga_4( api ) ?
    [ 'Play', '25 Percent Played', '50  Percent Played', '75 Percent Played', '100 Percent Played' ] :
    [ 'start', 'first quartile', 'second quartile', 'third quartile', 'complete' ];

  function fv_track_seconds_played(e, api_not_needed, video) {

    // Do not track when coming back to the browser tab, we can track that when really leaving the page
    // Except if it's the video load event, we need to allow that as we set the video name and track the seconds played if switching playlist items
    // Or if it's the heartbeat event, we need to allow that as we track the seconds played for the heartbeat too
    if ( document.visibilityState === 'visible' && e.type !== 'load' && e.type !== 'heartbeat' ) {
      return;
    }

    video = video || api.video;

    if(e.type === 'load') {
      event_name = fv_player_track_name(root,video);
    }

    if (time) {
      fv_player_track( api, false, "Video / Seconds played", api.engine.engineName + "/" + api.video.type, event_name, Math.round(time / 1000) );

      time = 0;
      if (timer) {
        clearTimeout(timer);
        timer = null;
      }
    }
  }

  function fv_track(e,api,data) {
    var video = api.video,
      dur = video.duration,
      i = 0;

    var name = fv_player_track_name(root,video);

    // Only track video quartiles if it's more than 4 seconds long
    if( dur > 4 ) {
      if( data > 19 * dur/20 ) i = 4;
      else if( data > 3 * dur/4 ) i = 3;
      else if( data > dur/2 ) i = 2;
      else if( data > dur/4 ) i = 1;
    }

    // For live streams we can only track the start
    if( api.live ) i = 0;

    if( root.data('fv_track_'+fv_ga_events[i]) ) return;

    for( var j in fv_ga_events ) {  //  make sure user triggered the previous quartiles before tracking
      if( !fv_ga_events.hasOwnProperty(j) ) continue;

      if(j == i) break;
      if( !root.data('fv_track_'+fv_ga_events[j]) ) return;
    }

    root.trigger('fv_track_'+fv_ga_events[i].replace(/ /,'_'), [api, name] );
    root.data('fv_track_'+fv_ga_events[i], true);

    fv_player_track( api, false, "Video " + (root.hasClass('is-cva')?'Ad ':'') +  fv_ga_events[i] , api.engine.engineName + "/" + video.type, name );
  }

  api.get_time_played = function() {
    return time/1000;
  }

  // Track subtitles
  var original_loadSubtitles = api.loadSubtitles;

  api.loadSubtitles = function(index) {
    original_loadSubtitles( index );

    if ( api.video.subtitles[ index ] ) {
      var name = fv_player_track_name( root, api.video ),
        lang = api.video.subtitles[ index ].srclang;

      // Track the subtitle language only once per video
      if ( tracked_subtitle_langs.indexOf( lang ) === -1 ) {
        fv_player_track( api, false, "Video Subtitles", lang, name );
        tracked_subtitle_langs.push( lang );
      }
    }
  }
});

/**
 * Checks if fvanalytics is using ga4
 *
 * @param {object} api
 * @returns {boolean}
 */
function is_ga_4 ( api ) {
  if( typeof api.conf.fvanalytics != 'undefined' && api.conf.fvanalytics && api.conf.fvanalytics.startsWith('G-') ) return true;
  return false;
}

/**
 * Sends event statistics to Google analytics and Matomo
 *
 * @param {string} ga_id Optional Google Analytics ID to use.
 * @param {string} event
 * @param {string} engineType
 * @param {string} name
 * @param {number} value
 *
 */
function fv_player_track( api, ga_id, event, engineType, name, value) {
  // Handle bad function call from old FV Player Pro
  if( typeof(api) != "object" ) {
    value = name;
    name = engineType;
    engineType = event;
    event = ga_id;
    ga_id = api;
    api = false;
  }

  if( !ga_id ) ga_id = flowplayer.conf.fvanalytics;

  if( typeof(engineType) == "undefined" ) engineType = 'Unknown engine';

  if( /fv_player_track_debug/.test(window.location.href) ) console.log('FV Player Track: ' + event + ' - ' + engineType + " '" + name + "'",value);

  // gtag.js
  if( typeof(gtag) != "undefined" ) {

    // Track the video properties the GA4 way
    if( is_ga_4( api ) && 'Video Subtitles' !== event ) {
      gtag("event", event, {
        'video_title': name,
        'video_current_time': api.video.time,
        'video_provider': engineType,
        'video_duration': api.video.duration,
        'value': value ? value  : 1
      });
    } else {
      gtag('event', event, {
        'event_category': engineType,
        'event_label': name,
        'value': value ? value  : 1
      });

    }

  // analytics.js
  } else if( ga_id && typeof(ga) != 'undefined' ) {
    ga('create', ga_id, 'auto', name , { allowLinker: true});
    ga('require', 'linker');

    if( value ) {
      ga('send', 'event', event, engineType, name, value);
    } else {
      ga('send', 'event', event, engineType, name);
    }

  // ga.js
  } else if( ga_id && typeof(_gat) != 'undefined' ) {
    var tracker = _gat._getTracker(ga_id);
    if( typeof(tracker._setAllowLinker) == "undefined" ) {
      return;
    }

    tracker._setAllowLinker(true);

    if( value ) {
      tracker._trackEvent( event, engineType, name, value );
    } else {
      tracker._trackEvent( event, engineType, name );
    }
  }

  // Matomo
  if( flowplayer.conf.matomo_domain && flowplayer.conf.matomo_site_id && typeof(_paq) != 'undefined' ) {
    if( value ) {
      _paq.push(['trackEvent', event, engineType, name, value]);
    } else {
      _paq.push(['trackEvent', event, engineType, name]);
    }
  }

}

function fv_player_track_name(root,video) {
  var name = root.attr("title");
  if( !name && typeof(video.fv_title) != "undefined" ) name = video.fv_title;
  if( !name && typeof(video.title) != "undefined" ) name = video.title;
  if( !name && typeof(video.src) != "undefined" ) {
    name = video.src.split("/").slice(-1)[0].replace(/\.(\w{3,4})(\?.*)?$/i, '');
    if( video.type.match(/mpegurl/) ) name = video.src.split("/").slice(-2)[0].replace(/\.(\w{3,4})(\?.*)?$/i, '') + '/' + name;
  }
  return name;
}