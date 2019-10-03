/* *
 * WARNINGS
 */
if( typeof(flowplayer) != "undefined" ) {
  flowplayer(function (api,root) {
    root = jQuery(root);
    
    //  Subtitles which iPhone can't show
    if( navigator.userAgent.match(/iPhone.* OS [0-6]_/i)){
      api.one('progress', function(e) {
        if( typeof(api.video.subtitles) !== 'undefined' && api.video.subtitles.length ){
          fv_player_warning(root,fv_flowplayer_translations.warning_iphone_subs);
        }        
      });         
    }
    
    //  unstable Android
    if( flowplayer.support.android && flowplayer.support.android.version < 5 && ( flowplayer.support.android.samsung || flowplayer.support.browser.safari ) ){
      fv_player_warning(root,fv_flowplayer_translations.warning_unstable_android,'firefox');
    }
    
    //  Vimeo misbehaving on Android 4.4
    if( /Android 4/.test(navigator.userAgent) && !/Firefox/.test(navigator.userAgent) ) {
      api.on('ready', function(e,api,video) { //  works for my Samsung Android 4.4.4, both built-in browser and Chrome
        setTimeout( function() {          
          if( video.src && video.src.match(/fpdl.vimeocdn.com/) && ( video.time == 0 || video.time == 1 ) ) {          
            fv_player_warning(root,fv_flowplayer_translations.warning_unstable_android,'firefox');
            
            api.on('progress', function(e,api) {
              root.prev().find('.fv-player-warning-firefox').remove();
            });          
          }
        }, 1500 );
      });
      
      api.on('error', function(e,api,error) { //  works for Huawei Android 4.3
        if( error.MEDIA_ERR_NETWORK == 2 && error.video.src.match(/fpdl.vimeocdn.com/) ) {          
          fv_player_warning(root,fv_flowplayer_translations.warning_unstable_android,'firefox');
        }        
      });
    }
    
    //  Vimeo misbehaving on old Safari
    if( /Safari/.test(navigator.userAgent) && /Version\/5/.test(navigator.userAgent) ) {
      api.on('error', function(e,api,error) {
        if( error.video.src.match(/fpdl.vimeocdn.com/) ) {          
          fv_player_warning(root,fv_flowplayer_translations.warning_old_safari);
        }        
      });
    }
    
    var sup = flowplayer.support;
    if( sup.android && (      
      sup.android.samsung && parseInt(sup.browser.version) < 66 || // Samsung Browser is just old version of Google Chrome!
      sup.browser.safari // and in some cases it's detected as Safari
      )
    ) {
      api.on('error', function(e,api,error) {     
        fv_player_warning(root,fv_flowplayer_translations.warning_samsungbrowser,'warning_samsungbrowser');      
      });
    }        
    
    
  });
  
  
  function fv_player_warning(root,warning,classname) {
    var wrapper = jQuery(root).prev('.fv-player-warning-wrapper');
    if( wrapper.length == 0 ) {
      jQuery(root).before('<div class="fv-player-warning-wrapper">');
      wrapper = jQuery(root).prev('.fv-player-warning-wrapper');
    }
    
    if( wrapper.find('.fv-player-warning-'+classname).length == 0 ) {
      var latest = jQuery("<p style='display: none' "+(classname?" class='fv-player-warning-"+classname+"'" : "")+">"+warning+"</p>");
      wrapper.append(latest);
      latest.slideDown();
    }
  }
}