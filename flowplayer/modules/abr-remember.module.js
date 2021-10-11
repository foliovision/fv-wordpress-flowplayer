/*
 *  MPEG-DASH and HLS.js ABR changes
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  var hlsjs;

  // this is the proper place to pick the initial HLS video quality 
  flowplayer.engine('hlsjs-lite').plugin(function(params) {
    hlsjs = params.hls;

    // if hls decryption key is invalid show error
    hlsjs.on(Hls.Events.ERROR, function (event, data) {
      if( data.type == 'mediaError' && data.details == 'fragParsingError' && data.fatal == true ) {
        hlsjs.destroy();
        api.trigger('error', [api, { code: 3 }]);
        setTimeout(function() {
          root.removeClass('is-seeking');
          root.addClass('is-paused');
        },0)
      }
    });

    // Safari needs a little push with some of the encrypted streams to start playing
    if( flowplayer.support.browser.safari ) {
      hlsjs.on(Hls.Events.KEY_LOADED, function (event) {
        if( event == 'hlsKeyLoaded' ) {
          setTimeout( function() {
            if( api.loading ) {
              console.log('FV Player: Safari stuck loading HLS, resuming playback...');
              api.resume();
            }
          }, 0 );
        }
      });
    }

    // do we force HD playback?
    var pick_quality = flowplayer.conf.hd_streaming && !flowplayer.support.fvmobile ? 720 : false;
    // or did we disable it for this player?
    if( jQuery(params.root).data('hd_streaming') == false ) pick_quality = false;
    // or did the user pick some quality by hand?
    if( localStorage.FVPlayerHLSQuality ) pick_quality = localStorage.FVPlayerHLSQuality;

    if( pick_quality ) {
      hlsjs.on(Hls.Events.MANIFEST_PARSED, function(_, data) {
        // look for the exact matching quality
        var found = false;
        jQuery.each( data.levels, function(k,v) {
          if( v.height == pick_quality ) found = k;
        });

        // if it's not the manual selection look for the highest quality
        if( !localStorage.FVPlayerHLSQuality && !found ) {
          jQuery.each( data.levels, function(k,v) {
            if( v.height > found ) found = k;
          });
        }

        if( found ) {
          console.log('FV Player: Picked '+data.levels[found].height+'p quality');
          hlsjs.startLevel = found;
          hlsjs.currentLevel = found;
        }

      });
    }
  });

  root = jQuery(root);
  var search = document.location.search;

  if( localStorage.FVPlayerDashQuality ) {
    if( !api.conf.dash ) api.conf.dash = {};
    api.conf.dash.initialVideoQuality = 'restore'; // special flag for Dash.js
  }

  // store the hand-picked HLS quality height such as 720p in localStorage
  // perhaps to ensure it's saved right away without it having to load
  root.on('click', '.fp-qsel-menu a', function() {
    if(api.engine.engineName == 'hlsjs-lite' ) {
      var quality = jQuery(this).data('quality');
      if( quality == -1 ) {
        localStorage.removeItem('FVPlayerHLSQuality');
      } else {
        var level = hlsjs.levels[quality];
        localStorage.FVPlayerHLSQuality = level.height;
      }
    }
  });

  
  if( localStorage.FVPlayerHLSQuality ) {
    api.conf.hlsjs.startLevel = parseInt(localStorage.FVPlayerHLSQuality);
    api.conf.hlsjs.testBandwidth = false;
    api.conf.hlsjs.autoLevelEnabled = false;
  } else if( flowplayer.conf.hd_streaming && !flowplayer.support.fvmobile ) {
    api.conf.hlsjs.startLevel = 3; // far from ideal, but in most cases it works; ideally HLS.js would handle this better
    api.conf.hlsjs.testBandwidth = false;
    api.conf.hlsjs.autoLevelEnabled = false;
  }
  
  api.bind('quality', function(e,api,quality) {
    if(api.engine.engineName == 'dash' ) {      
      if( quality == -1 ) {
        localStorage.removeItem('FVPlayerDashQuality');
      } else if( bitrates[quality] ) {
        localStorage.FVPlayerDashQuality = bitrates[quality].height;
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

      // with HLS.js the stream might not be playing even after receiving the ready event
      // like when the decryption key is loading, so we need to indicate it's loading
      // TODO: What about fixing that ready event instead? Core Flowplayer 7.2.8?
      root.addClass('is-loading');
      api.loading = true;

      // once we get a progress event we know it's really playing
      api.one('progress', function() {
        if( api.loading ) {
          root.removeClass('is-loading');
          api.loading = false;
        }
      });

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
            if( height > 0 && qswitch == -1 && height >= 720 && height <= 720 ) {
              qswitch = v.value;
            }
          });
          
        }
        
        qswitch = parseInt(qswitch);

        if( qswitch > -1 ) {
          root.one('progress', function() {
            setTimeout( function() {
              api.quality(qswitch);
            });
          });
        }
        
        quality_sort();
      }

    // FV Player Pro Quality Switching of MP4 files
    } else if( api.video.sources_fvqs && api.video.sources_fvqs.length > 0 && api.video.src.match(/vimeo.*?\.mp4/) ) {
      setTimeout( quality_sort, 0 );      

    }    
    root.find('a[data-quality]').removeClass('is-current');
  });

  // show debug information in overlay
  if( search.match(/dash_debug/) || search.match(/hls_debug/) ) var debug_log = jQuery('<div class="fv-debug" style="background: gray; color: white; top: 10%; position: absolute; z-index: 1000">').appendTo(root.find('.fp-player'));
  
  api.bind('ready progress', quality_process);
  
  // the the quality label in control bar and debug info
  api.bind('quality', function() {
    setTimeout( quality_process, 0 );
  });
  
  // the the quality label in control bar and debug info
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
        if( level ) {
          quality_debug(level.width,level.height,level.bitrate);
        }
      }      
      
    }
  }
  
  // the the quality label in control bar
  function quality_label(index,qualities) {
    if( !qualities[index] ) return;
    
    // hd_limit allows us to treat 576p as HD if there is nothing higher
    var height = qualities[index].height,
      hd_limit = 541,
      lowest = 100000;
    jQuery(qualities).each( function(k,v) {
      // if we have 720p, then that will be the HD
      if( v.height >= 720 && v.height < 1400 ) hd_limit = 720;
      if( v.height < lowest ) lowest = v.height;

      // make sure the manually selected quality is shown in menu
      if( localStorage.FVPlayerHLSQuality == v.height ) {
        root.find('a[data-quality]').removeClass('fp-selected fp-color');
        root.find('a[data-quality='+k+']').addClass('fp-selected fp-color');
      }
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
  
  // sort qualities in menu, omit the bitrate information
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