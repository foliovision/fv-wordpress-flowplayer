//  Magnific Popup suppport
jQuery(document).on('mfpClose', function() {
  if( typeof(jQuery('.flowplayer').data('flowplayer')) != "undefined" ) jQuery('.flowplayer').data('flowplayer').unload();
} );

/*
 *  Visual Composer tabs support
 */
jQuery(document).on('click','.vc_tta-tab a', function() {
  var api = jQuery('.flowplayer.is-playing').data('flowplayer');
  if( api ) api.pause();
});

/*
 *  Gravity Forms Partial Entries fix - the whole player is cloned if it's placed in the form, causing it to play again in the background
 */
flowplayer(function(api, root) {

  api.bind('ready',function() {
    setTimeout( function() {
      var video = jQuery('video',root);
      if( video.length > 0 ) {
        video.removeAttr('autoplay'); //  removing autoplay attribute fixes the issue
      }
    }, 100 ); //  by default the heartbeat JS event triggering this happens every 30 seconds, we just add a bit of delay to be sure
  });

});

/*
 *  Tabbed playlist
 */
jQuery(document).on("tabsactivate", '.fv_flowplayer_tabs_content', function(event, ui){
  var oldPlayer = jQuery('.flowplayer.is-playing').data('flowplayer');
  if( typeof(oldPlayer) != "undefined" ) {
    oldPlayer.pause();
  }
  
  var objPlayer = jQuery('.flowplayer',ui.newPanel);
  var api = objPlayer.data('flowplayer');
  api.load();  
}); 

/*
 *  BlackBerry 10 hotfix
 */
jQuery('.flowplayer').on('ready', function(e,api) { //  v6
  if( /BB10/.test(navigator.userAgent) ){
    api.fullscreen();
  }
});

//  v6
// if( /ipad/.test(navigator.userAgent.toLowerCase()) && /os 8/.test(navigator.userAgent.toLowerCase()) ){
//   flowplayer(function (api, root) {
//     api.bind("resume", function (e,api,data) {
//       setTimeout( function() {      
//         if( api.loading ) jQuery(e.currentTarget).children('video')[0].play();
//       }, 1000 );
//     });  
//   });
// }
