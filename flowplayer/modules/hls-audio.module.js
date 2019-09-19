/*
 *  Hls - audio menu
 */
flowplayer( function(api,root) {

  root = jQuery(root);
  var hlsjs, player, videoTag ,audioUXGroup, audioGroups;
  var hls_audio_button, hls_audio_menu;
  var  mse = window.MediaSource || window.WebKitMediaSource;

  flowplayer.engine('hlsjs-lite').plugin(function(params) {
    hlsjs = params.hls;
  });

  selectAudioTrack = function (audioTrack) {
    var elements = root.find(".fv-fp-hls-menu a");
    elements.each(function (k,el) {
        var adata = jQuery(el).attr("data-audio"),
            isSelected = adata === audioTrack.name;
        jQuery(el).toggleClass("fp-selected", isSelected);
    });
  }

  function removeAudioMenu(){
    jQuery(hls_audio_menu).remove();
    jQuery(hls_audio_button).remove();
  }

  function  initAudio(data){
    audioGroups = [];
    audioUXGroup = [];

    data.levels.forEach(function (level) {
      var agroup = level.attrs.AUDIO;

      if (agroup && audioGroups.indexOf(agroup) < 0/* && mse.isTypeSupported("video/mp4;codecs=" + level.videoCodec + "," + level.audioCodec)*/) { //TODO: test & fix
        audioGroups.push(agroup);
      }

      if (audioGroups.length) {
        audioUXGroup = data.audioTracks.filter(function (audioTrack) {
          return audioTrack.groupId === audioGroups[0];
        });
      }
    });
  }

  api.bind('ready', function(e,api) {  
    removeAudioMenu();
    
    if( !hlsjs || api.video.type != 'application/x-mpegurl'){
      return;
    }

    initAudio(hlsjs);

    if (!audioUXGroup || audioUXGroup.length < 2) {
      return;
    }

    // gui
    hls_audio_button = jQuery('<strong class="fv-fp-hls">AUD</strong>');
    hls_audio_menu = jQuery('<div class="fp-menu fv-fp-hls-menu"></div>').insertAfter( root.find('.fp-controls') );

    hls_audio_menu.append('<strong>AUDIO</strong>');

    // audio options
    audioUXGroup.forEach(function (audioTrack) {
      hls_audio_menu.append('<a data-audio="'+audioTrack.name+'">'+audioTrack.name+'</a>');
    });

    // button
    hls_audio_button.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
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

    jQuery('a',hls_audio_menu).click( function(e) {
      var adata = e.target.getAttribute("data-audio");
      var gid = hlsjs.audioTracks[hlsjs.audioTrack].groupId;

      // confine choice to current group
      var atrack = hlsjs.audioTracks.filter(function (at) {
        return at.groupId === gid && (at.name === adata || at.lang === adata);
      })[0];
      hlsjs.audioTrack = atrack.id; // change track
      selectAudioTrack(atrack); // gui
    });

    var currentAudioTrack = hlsjs.audioTracks[hlsjs.audioTrack];
    selectAudioTrack(currentAudioTrack);
  });

});