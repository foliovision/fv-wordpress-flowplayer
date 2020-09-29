/*
 *  Take screenshot from preview
 */
( function($) {
  var index = 0;

  flowplayer( function(api,root) {
    root = jQuery(root);
    var button = jQuery('<input type="button" value="Screenshot" class="button" id="fv-splash-screen-button" />'),
    spinner =jQuery('<div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>'),
    message = jQuery('.fv-messages'),
    title ='';

    function takeScreenshot() {
      var video = root.find('video').get(0);
      var canvas = document.createElement("canvas");
      canvas.width = video.videoWidth * 1;
      canvas.height = video.videoHeight * 1;
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

      var img = document.createElement("img");
      img.src = canvas.toDataURL('image/jpeg', 0.95);

      return img.src ;
    }

    button.click(function(){
      try {
        button.prop("disabled",true);
        
        var screenshot = takeScreenshot();
        var item = jQuery('.fv-player-playlist-item[data-index="'+index+'"]');
        spinner.insertAfter( item.find('#fv_wp_flowplayer_field_splash') );
        
        // Check title
        if(item.find('#fv_wp_flowplayer_field_caption').val()){
            title = item.find('#fv_wp_flowplayer_field_caption').val()
        }else{
            title = item.find('#fv_wp_flowplayer_field_src').val()
        }
        var data = {
          'action': 'fv_player_splashcreen_action',
          'img': screenshot,
          'title': title,
          'security': fv_player_editor_conf.splashscreen_nonce
        };
      }
      catch(err) {
        spinner.remove();
        button.prop("disabled",false);
        message.html('<div class="error"><p>Cannot obtain video screenshot, please make sure the video is served with <a href="https://foliovision.com/player/video-hosting/hls#hls-js">CORS headers</a>.</p></div>');
        fv_wp_flowplayer_dialog_resize();
        return;
      }

      jQuery.post(fv_fp_ajaxurl, data, function(response) {
        if(response.src) {
          var splashInput = item.find('#fv_wp_flowplayer_field_splash');
          splashInput.val(response.src);
          splashInput.css('background-color','#6ef442');
        }
        if(response.error) {
          message.html('<div class="error"><p>'+response.error+'</p></div>');
          fv_wp_flowplayer_dialog_resize();
        }
        spinner.remove();
        button.prop("disabled",false);

        fv_wp_flowplayer_submit('refresh-button');
        setTimeout(function(){
          splashInput.css('background-color','#ffffff');
        }, 2000);
      });
    });

    // Compatibility test
    api.bind('ready', function(e,api) {
    if(jQuery('#fv_player_boxLoadedContent').length == 1) {
      button.appendTo('#fv-player-shortcode-editor-preview');
      try{
        takeScreenshot();
        }catch(err){
          button.prop("disabled",true);
        }
    }
    });

  });

  // Remove button, spinner, message
  jQuery(document).on('fv_flowplayer_player_editor_reset', function() {
    jQuery('#fv-splash-screen-button').remove();
    jQuery('.fv-player-shortcode-editor-small-spinner').remove();
    jQuery('.fv-messages').empty();
    index = 0;
  });

  jQuery(document).on('fvp-preview-complete', function() {
    jQuery('#fv-splash-screen-button').remove();
    jQuery('.fv-player-shortcode-editor-small-spinner').remove();
    jQuery('.fv-messages').empty();
  });

  // Video index
  jQuery(document).on('fv_flowplayer_shortcode_item_switch', function(e,i) {
    index = i;
  });

})(jQuery);