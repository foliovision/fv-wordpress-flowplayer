/*
 *  Custom keyboard controls, todo: fp7 check!
 */
flowplayer.bean.off(document,'keydown.fp');

flowplayer(function(api, root) {
  var bean = flowplayer.bean;  

  // no keyboard configured
  if (!api.conf.keyboard) return;
  
  //  todo: is help really gone?
  /*var help = jQuery(root).find('.fp-help').html();
  var playlist_help = api.conf.playlist.length > 0 ? '<p><em>shift</em> + <em>n</em><em>p</em>next / prev video</p>' : '';
  help = help.replace(/<p><em>1.*?60% <\/p>/,playlist_help);
  jQuery(root).find('.fp-help').html(help);*/
  
  // hover
  bean.on(root, "mouseenter mouseleave", function(e) {
    fv_player_focused = !api.disabled && e.type == 'mouseover' ? api : 0;
    if (fv_player_focused) fv_player_focusedRoot = root;
  });
  
  api.bind('ready', function(e,api,video) {
    if( video.subtitles && video.subtitles.length > 0 ) {
      var help = jQuery(root).find('.fp-help').html();
      help += '<div class="fp-help-section fp-help-subtitles"><p><em>c</em>cycle through subtitles</p></div>';
      jQuery(root).find('.fp-help').html(help);
    } else {
      jQuery(root).find('.fp-help-subtitles').remove();
    }
  });
});

flowplayer.bean.on(document, "keydown.fp", function(e) {
  if( typeof(fv_player_focused) == "undefined" ) return

  var api = fv_player_focused,
    focusedRoot = api ? fv_player_focusedRoot : false,
    common = flowplayer.common;
  
  var el = api && !api.disabled ? api : 0,
    metaKeyPressed = e.ctrlKey || e.metaKey || e.altKey,
    key = e.which,
    conf = el && el.conf;

  // no keybinds when controlbar is disabled or video ad
  if( common.hasClass(focusedRoot, "no-controlbar") || common.hasClass(focusedRoot, "is-cva") ) return;
  
  if (!el || !conf.keyboard || el.disabled) return;
  
  // help dialog (shift key not truly required)
  if ([63, 187, 191].indexOf(key) != -1) {
    common.toggleClass(focusedRoot, "is-help");
    return false;
  }
  
  // close help / unload
  if (key == 27 && common.hasClass(focusedRoot, "is-help")) {
    common.toggleClass(focusedRoot, "is-help");
    return false;
  }
  
  if (!metaKeyPressed && el.ready) {
  
  e.preventDefault();
  
  // slow motion / fast forward
  if (e.shiftKey) {
    if (key == 39) el.speed(true);
    else if (key == 37) el.speed(false);
    else if (key == 78) el.next();  //  N
    else if (key == 80) el.prev();  //  P    
    return;
  }
  
  // 1, 2, 3, 4 ..
  if (key < 58 && key > 47) return el.seekTo(key - 48);
  
  switch (key) {
    case 38: case 75: el.volume(el.volumeLevel + 0.15); break;
    case 40: case 74: el.volume(el.volumeLevel - 0.15); break;
    case 39: case 76: el.seeking = true; el.seek(api.video.time+5); break;
    case 37: case 72: el.seeking = true; el.seek(api.video.time-5); break;
    case 190: el.seekTo(); break;
    case 32: el.toggle(); break;
    case 70: if(conf.fullscreen) el.fullscreen(); break;
    case 77: el.mute(); break;
    case 81: el.unload(); break;
    case 67:  //  circle through subtitles
      if( !api.video.subtitles || api.video.subtitles.length == 0 ) break;
      
      var current_subtitles = jQuery(focusedRoot).find('.fp-dropdown li.active[data-subtitle-index]').data('subtitle-index');
      if( typeof(current_subtitles) == "undefined" ) current_subtitles = -1;
      
      current_subtitles++;
      if( current_subtitles > (api.video.subtitles.length - 1) ) {
        current_subtitles = -1;
      }
      
      api.trigger('fv-subtitles-switched');
      
      if( current_subtitles > -1 ) {
        el.loadSubtitles(current_subtitles);
        fv_player_notice(focusedRoot,fv_flowplayer_translations.subtitles_switched+' '+api.video.subtitles[current_subtitles].label,'fv-subtitles-switched');          
      } else {
        el.disableSubtitles();
        fv_player_notice(focusedRoot,fv_flowplayer_translations.subtitles_disabled,'fv-subtitles-switched');          
      }
      
      break;
  }
  
  }

});