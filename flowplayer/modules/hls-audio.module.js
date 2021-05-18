/*
 *  Hls - audio menu
 */
flowplayer( function(api,root) {

  root = jQuery(root);
  var hlsjs,
    player,
    audioUXGroup,
    audioGroups,
    hls_audio_button,
    hls_audio_menu,
    mse = window.MediaSource || window.WebKitMediaSource;

  flowplayer.engine('hlsjs-lite').plugin(function(params) {
    hlsjs = params.hls;
  });
  
  // HLS.js - we can just check the tracks on ready event
  api.bind('ready', function(e,api) {  
    removeAudioMenu();
    
    if( hlsjs && api.video.type == 'application/x-mpegurl') {
      parseAudioTracksHlsJs(hlsjs);
      createAudioMenu();
    }
  });
  
  // HTML5 HLS support (Safari) - seems like we need to wait for the first progress event
  api.one('progress', function() {
    if( api.engine.engineName == 'html5' && api.video.type == 'application/x-mpegurl') {
      parseAudioTracksSafari()
      createAudioMenu();
    }
  });
  
  function getVideoTagAudioTracks() {
    var video = root.find('video');
    if( video.length && video[0].audioTracks ) {
      return video[0].audioTracks;
    }
    return [];
  }

  function hilightAudioTrack(audioTrack) {
    if( !audioTrack.name ) audioTrack.name = audioTrack.label;

    root.find(".fv-fp-hls-menu a").each(function (k,el) {
      jQuery(el).toggleClass("fp-selected", jQuery(el).attr("data-audio") === audioTrack.name);
    });
  }
  
  function createAudioMenu() {
    if (!audioUXGroup || audioUXGroup.length < 2) {
      return;
    }

    hls_audio_button = jQuery('<strong class="fv-fp-hls">AUD</strong>');
    hls_audio_menu = jQuery('<div class="fp-menu fv-fp-hls-menu"></div>').insertAfter( root.find('.fp-controls') );

    hls_audio_menu.append('<strong>Audio</strong>');

    // audio options
    audioUXGroup.forEach(function (audioTrack) {
      hls_audio_menu.append('<a data-audio="'+audioTrack.name+'" data-lang="'+audioTrack.lang+'">'+audioTrack.name+'</a>');
    });

    // button
    hls_audio_button.insertAfter( root.find('.fp-controls .fp-volume') ).on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      if( hls_audio_menu.hasClass('fp-active') ) {
        api.hideMenu(hls_audio_menu[0]);
      }
      else {
        root.click();
        api.showMenu(hls_audio_menu[0]);
      }
    });

    jQuery('a',hls_audio_menu).on('click', function(e) {
      var adata = e.target.getAttribute("data-audio");
      if( hlsjs ) {
        var gid = hlsjs.audioTracks[hlsjs.audioTrack].groupId;

        // confine choice to current group
        var atrack = hlsjs.audioTracks.filter(function (at) {
          return at.groupId === gid && (at.name === adata || at.lang === adata);
        })[0];
        hlsjs.audioTrack = atrack.id; // change track
        
        hilightAudioTrack(atrack);
        
      } else {
        var tracks = getVideoTagAudioTracks();
        for( var i in tracks ) {
          if( !tracks.hasOwnProperty(i) ) continue;
          
          if( tracks[i].label == adata ) {
            tracks[i].enabled = true;
            
            hilightAudioTrack(tracks[i]);
          }
        }
        
      }
      
    });

    if( hlsjs ) {
      hilightAudioTrack(hlsjs.audioTracks[hlsjs.audioTrack]);
      
    } else {
      var tracks = getVideoTagAudioTracks();
      for( var i in tracks ) {
        if( !tracks.hasOwnProperty(i) ) continue;
        
        if( tracks[i].enabled ) {
          hilightAudioTrack(tracks[i]);
        }
      }
      
    }
  }

  function removeAudioMenu(){
    jQuery(hls_audio_menu).remove();
    jQuery(hls_audio_button).remove();
  }

  function parseAudioTracksHlsJs(data){
    audioGroups = [];
    audioUXGroup = [];

    data.levels.forEach(function (level) {
      var agroup = level.attrs.AUDIO;

      if (agroup && audioGroups.indexOf(agroup) < 0/* && mse.isTypeSupported("video/mp4;codecs=" + level.videoCodec + "," + level.audioCodec)*/) { //TODO: This might be useful
        audioGroups.push(agroup);
      }

      if (audioGroups.length) {
        audioUXGroup = data.audioTracks.filter(function (audioTrack) {
          return audioTrack.groupId === audioGroups[0];
        });
      }
    });
  }
  
  function parseAudioTracksSafari(){
    audioGroups = [];
    audioUXGroup = [];

    var tracks = getVideoTagAudioTracks();
    for( var i in tracks ) {
      if( !tracks.hasOwnProperty(i) ) continue;
      
      audioUXGroup.push( {
        id: tracks[i].id,
        name: tracks[i].label,
      } );
    }
  }

});