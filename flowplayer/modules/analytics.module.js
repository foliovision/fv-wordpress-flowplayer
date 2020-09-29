/*
 *  Google Analytics improvements
 */
flowplayer( function(api,root) {
  var root = jQuery(root);
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
        fv_player_track(api.conf.analytics, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", error.message, name );
      }
    }, 100 );
  });
  
  var fv_ga_events = [ 'start', 'first quartile', 'second quartile', 'third quartile', 'complete' ];
        
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
            
      if( root.data('fv_track_'+fv_ga_events[i]) ) return;			
          
      for( var j in fv_ga_events ) {  //  make sure user triggered the previous quartiles before tracking
        if( !fv_ga_events.hasOwnProperty(j) ) continue;
        
        if(j == i) break;    
        if( !root.data('fv_track_'+fv_ga_events[j]) ) return;      
      }
      
      root.trigger('fv_track_'+fv_ga_events[i].replace(/ /,'_'), [api, name] );
      root.data('fv_track_'+fv_ga_events[i], true);
      
      fv_player_track( api.conf.analytics, "Video " + (root.hasClass('is-cva')?'Ad ':'') +  fv_ga_events[i] , api.engine.engineName + "/" + video.type, name );
    }
  
});

//Sends event statistics to Google analytics
function fv_player_track( ga_id, event, engineType, name){
 
  if( !ga_id || typeof( _gat) == 'undefined' ) return;    
  
  var tracker = _gat._getTracker(ga_id);
  if( typeof(tracker._setAllowLinker) == "undefined" ) {
    return;
  }
  
  if( typeof(engineType) == "undefined" ) engineType = 'Unknown engine';
  
  if( /fv_ga_debug/.test(window.location.href) ) console.log('FV GA: ' + event + ' - '   + engineType + " '" + name + "'")
  tracker._setAllowLinker(true);    
  tracker._trackEvent( event, engineType, name, 1 );
  //tracker._trackEvent( "Video "+fv_ga_events[i], api.engine + "/" + video.type, name, 1 );
  
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