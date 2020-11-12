/*
 *  MPEG-DASH and HLS.js ABR changes
 */
if( localStorage.FVPlayerHLSQuality && typeof(flowplayer.conf.hlsjs.autoLevelEnabled) == "undefined" ) {
  flowplayer.conf.hlsjs.startLevel = localStorage.FVPlayerHLSQuality;
}

flowplayer( function(api,root) {
  var hlsjs;
  flowplayer.engine('hlsjs-lite').plugin(function(params) {
    hlsjs = params.hls;
  });
  
  root = jQuery(root);
  var search = document.location.search;

  if( localStorage.FVPlayerDashQuality ) {
    if( !api.conf.dash ) api.conf.dash = {};
    api.conf.dash.initialVideoQuality = 'restore'; // special flag for Dash.js
  }
  
  if( localStorage.FVPlayerHLSQuality && typeof(flowplayer.conf.hlsjs.autoLevelEnabled) == "undefined" ) {
    flowplayer.conf.hlsjs.startLevel = localStorage.FVPlayerHLSQuality;
  } else if( flowplayer.conf.hd_streaming && !flowplayer.support.fvmobile ) {
    flowplayer.conf.hlsjs.startLevel = 3; // far from ideal, but in most cases it works; ideally HLS.js would handle this better
  }
  
  api.bind('quality', function(e,api,quality) {
    if(api.engine.engineName == 'dash' ) {      
      if( quality == -1 ) {
        localStorage.removeItem('FVPlayerDashQuality');
      } else if( bitrates[quality] ) {
        localStorage.FVPlayerDashQuality = bitrates[quality].height;
      }
    } else if(api.engine.engineName == 'hlsjs-lite' ) {      
      if( quality == -1 ) {
        localStorage.removeItem('FVPlayerHLSQuality');
      } else {
        localStorage.FVPlayerHLSQuality = quality;
      }
    }
  });  

  var bitrates = [];
  var last_quality = -1;
  api.bind('ready', function(e,api) {
    root.find('.fp-qsel-menu strong').text(fv_flowplayer_translations.quality); // translate Quality

    if(api.engine.engineName == 'dash' ) {      
      bitrates = api.engine.dash.getBitrateInfoListFor('video');
      if( localStorage.FVPlayerDashQuality && api.conf.dash.initialVideoQuality ) { // Dash.js gives us initialVideoQuality
        api.quality(api.conf.dash.initialVideoQuality);
      }
      quality_sort();
    } else if(api.engine.engineName == 'hlsjs-lite' ) {
      if( api.video.qualities && api.video.qualities.length > 2 ) {
        var qswitch = -1;
        if( localStorage.FVPlayerHLSQuality ) {
          // do we have such quality?
          jQuery(api.video.qualities).each( function(k,v) {
            if( v.value == localStorage.FVPlayerHLSQuality ) {
              // accept the remembered quality index
              qswitch = localStorage.FVPlayerHLSQuality;  
              return false;
            }
          });
        
        // is FV Player set to force HD?
        } else if( flowplayer.conf.hd_streaming && !flowplayer.support.fvmobile ) {
          jQuery(api.video.qualities).each( function(k,v) {
            var height = parseInt(v.label);
            if( height > 0 && hd_quality == -1 && height >= 720 && height <= 720 ) {
              qswitch = k;
            }
          });
          
        }
        
        if( qswitch > -1 ) {
          api.quality(qswitch);
          root.one('progress', function() {
            setTimeout( function() {
              api.quality(qswitch);
            });
          });
        }
        
        quality_sort();
      }
    } else if( api.video.sources_fvqs && api.video.sources_fvqs.length > 0 && api.video.src.match(/vimeo.*?\.mp4/) ) {
      setTimeout( quality_sort, 0 );      
    }    
    root.find('a[data-quality]').removeClass('is-current');
  });

  if( search.match(/dash_debug/) || search.match(/hls_debug/) ) var debug_log = jQuery('<div class="fv-debug" style="background: gray; color: white; top: 10%; position: absolute; z-index: 1000">').appendTo(root.find('.fp-player'));
  
  api.bind('ready progress', quality_process);
  
  api.bind('quality', function() {
    setTimeout( quality_process, 0 );
  });
  
  function quality_process() {
    if( api.engine.engineName == 'dash' ) {
      var stream_info = bitrates[api.engine.dash.getQualityFor('video')];
      if( stream_info.qualityIndex != last_quality ) {
        last_quality = stream_info.qualityIndex;
        quality_label( stream_info.qualityIndex, bitrates );
      }
      if( search.match(/dash_debug/) ) quality_debug(stream_info.width,stream_info.height,stream_info.bitrate);      
      
    } else if( api.engine.engineName == 'hlsjs-lite' ) {
      if( hlsjs.currentLevel != last_quality ) {
        last_quality = hlsjs.currentLevel;
        quality_label( hlsjs.currentLevel, hlsjs.levels );
      }
      
      if( search.match(/hls_debug/) ) {
        var level = hlsjs.levels[hlsjs.currentLevel];
        quality_debug(level.width,level.height,level.bitrate);
      }      
      
    }
  }
  
  function quality_label(index,qualities) {
    if( !qualities[index] ) return;
    
    var height = qualities[index].height,
      hd_limit = 541,
      lowest = 100000;
    jQuery(qualities).each( function(k,v) {
      if( v.height >= 720 && v.height < 1400 ) hd_limit = 720;
      if( v.height < lowest ) lowest = v.height;
    });
    
    root.find('a[data-quality]').removeClass('is-current');
    root.find('a[data-quality='+index+']').addClass('is-current');    
    var label = 'SD';
    if( height >= 360 && lowest < height ) label = 'SD';
    if( height >= hd_limit ) label = 'HD';
    if( height >= 1400 ) label = '4K';
    root.find('.fp-qsel').html(label);    
  }
  
  function quality_debug(w,h,br) {
    debug_log.html( "Using "+w+"x"+h+" at "+Math.round(br/1024)+" kbps" );
  }
  
  function quality_sort() {
    var menu = root.find('.fp-qsel-menu');
    menu.children().each(function(i,a){menu.prepend(a)});
    menu.children().each(function(i,a){
      if( /^NaNp/.test(jQuery(a).html()) ) { // could not parse quality so use bitrate, example : #EXT-X-STREAM-INF:BANDWIDTH=200000
        var bitrate = jQuery(a).html().match(/\((.*?)\)/);
        if( bitrate && typeof(bitrate[1] ) !== 'undefined' ) {
          jQuery(a).html(bitrate[1]);
        }
      } else { // quality parsed, remove bitrate
        jQuery(a).html(jQuery(a).html().replace(/\(.*?\)/,''));
      }
    });
    menu.prepend(menu.find('a[data-quality=-1]'));
    menu.prepend(menu.find('strong'));
  }

});