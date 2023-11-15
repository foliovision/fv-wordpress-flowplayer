/*global learndash_video_data, LearnDash_disable_assets, LearnDash_watchPlayers */

flowplayer( function(api,root) {
  root = jQuery(root);

  // Taking inspiration from learndash_video_script.js
  if(
    window.learndash_video_data &&
    learndash_video_data.videos_found_provider == 'local' &&
    root.closest('[data-video-progression=true]').length
  ) {
    LearnDash_disable_assets( true );
    LearnDash_watchPlayers();

    api.on("finish", function(e,api,time) {
      if( typeof(api.video.click) == "string" ) {
        return;
      }

      /**
       * Without this LearnDash LMS won't mark the lesson with Video Progression enabled as
       *  complete once you hit the button
       */
      var ld_lms_cookie_key = jQuery( '.ld-video' ).data( 'video-cookie-key' );
      if ( ld_lms_cookie_key ) {
        jQuery.cookie( ld_lms_cookie_key, JSON.stringify( { 'video_state': 'complete' } ) )
      }

      // Enable "Mark Complete" button
      window.LearnDash_disable_assets(false);
    });

  }

});