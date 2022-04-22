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

      // Enable "Mark Complete" button
      window.LearnDash_disable_assets(false);
    });

  }

});