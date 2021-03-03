/*
 *  Google Analytics improvements + heartbeat
 *  Also provides custom fv_track_* events
 */
flowplayer( function(api,root) {
  var root = jQuery(root);
  var bean = flowplayer.bean;
  var time = 0, last = 0, timer, event_name;

  if( typeof(ga) == 'undefined' && api.conf.fvanalytics ) {
    jQuery.getScript( { url: "https://www.google-analytics.com/analytics.js", cache: true }, function() {
      ga('create', api.conf.fvanalytics, 'auto');
    });
  }

  api.bind('progress', function(e,api,current) {
    fv_track(e,api,current);
  }).bind('finish ready ', function(e,api) {				
    //if( typeof(aFVPlayersSwitching[root.attr('id')]) != "undefined" ) { //  todo: problem that it won't work on video replay or playlist
      //return;
    //}
    for( var j in fv_ga_events ) {
      if( !fv_ga_events.hasOwnProperty(j) ) continue;
      root.removeData('fv_track_'+fv_ga_events[j]);
    }
  }).bind('error', function(e,api,error) {
    setTimeout( function() {
      if( !api.error ) return;

      var video = typeof(api.video) != "undefined" && typeof(api.video.src) != "undefined" ? api.video : false;
      if( !video && typeof(api.conf.clip) != "undefined" && typeof(api.conf.clip.sources) != "undefined" && typeof(api.conf.clip.sources[0]) != "undefined" && typeof(api.conf.clip.sources[0].src) != "undefined" ) video = api.conf.clip.sources[0];

      var name = fv_player_track_name(root,video);
      if( name && !name.match(/\/\/vimeo.com\/\d/) ) {
        fv_player_track(api.conf.fvanalytics, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", error.message, name );
      }
    }, 100 );
  });

  api.bind("load unload", fv_track_seconds_played).bind("progress", function() {

    if (!api.seeking) {
       time += last ? (+new Date() - last) : 0;
       last = +new Date();
    }

    if (!timer) {
      timer = setTimeout(function() {
        timer = null;
        fv_player_track( api.conf.fvanalytics, "Flowplayer heartbeat", api.engine.engineName + "/" + api.video.type, "Heartbeat", 0 );
      }, 10*60*1000); // heartbeat every 10 minutes
    }

  }).bind("pause", function() {
      last = 0;
  });

  api.bind('shutdown', function() {
    bean.off(window, 'unload', fv_track_seconds_played);
  });

  bean.on(window, 'unload', fv_track_seconds_played);

  var fv_ga_events = [ 'start', 'first quartile', 'second quartile', 'third quartile', 'complete' ];

  function fv_track_seconds_played(e, api_not_needed, video) {
    video = video || api.video;

    if(e.type === 'load') {
      event_name = fv_player_track_name(root,video);
    }

    if (time) {
      fv_player_track( api.conf.fvanalytics, "Video / Seconds played", api.engine.engineName + "/" + api.video.type, event_name, Math.round(time / 1000) );

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

    if( dur ) {
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

    fv_player_track( api.conf.fvanalytics, "Video " + (root.hasClass('is-cva')?'Ad ':'') +  fv_ga_events[i] , api.engine.engineName + "/" + video.type, name );
  }
  
  api.get_time_played = function() {
    return time/1000;
  }
});

//Sends event statistics to Google analytics
function fv_player_track( ga_id, event, engineType, name, value){

  if( !ga_id || typeof(ga) == 'undefined' ) return;

  ga('create', ga_id, 'auto', name , { allowLinker: true});
  ga('require', 'linker');

  if( typeof(engineType) == "undefined" ) engineType = 'Unknown engine';

  if( /fv_ga_debug/.test(window.location.href) ) console.log('FV GA: ' + event + ' - ' + engineType + " '" + name + "'",value);
 // ga('linker:autoLink', ['destination.com']);

  if (typeof value === 'undefined') {
    ga('send', 'event', event, engineType, name);
  } else {
    ga('send', 'event', event, engineType, name, value);
  }

}

function fv_player_track_name(root,video) {
  var name = root.attr("title");
  if( !name && typeof(video.fv_title) != "undefined" ) name = video.fv_title;
  if( !name && typeof(video.src) != "undefined" ) {
    name = video.src.split("/").slice(-1)[0].replace(/\.(\w{3,4})(\?.*)?$/i, '');
    if( video.type.match(/mpegurl/) ) name = video.src.split("/").slice(-2)[0].replace(/\.(\w{3,4})(\?.*)?$/i, '') + '/' + name;
  }
  return name;
}