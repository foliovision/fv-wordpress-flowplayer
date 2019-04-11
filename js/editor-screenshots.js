/*
 *  Take screenshot from preview
 */
( function($) {
var index = 0;

flowplayer( function(api,root) {
  root = jQuery(root);
  var button = jQuery('<input type="button" value="Screenshot" class="button" id="fv-splash-screen-button" />'),
  spinner =jQuery('<div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>'),
  message = jQuery('.fv-messages');

  function takeScreenshot(){
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
    if(!button.hasClass('nocors')){
      try {
      spinner.insertAfter(button)
      button.prop("disabled",true);
      
      var screenshot = takeScreenshot();
      
      var data = {
          'action': 'fv_player_splashcreen_action',
          'img': screenshot,
          'security': fv_player_editor_conf.splashscreen_nonce
      };
      }
      catch(err) {
      spinner.remove();
      button.prop("disabled",false);
      message.html('<div class="error"><p>Cannot obtain video screenshot, please make sure the video is served with <a href="#">CORS headers</a>.</p></div>');
      return;
      }

      jQuery.post(fv_fp_ajaxurl, data, function(response) {
        if(response.src){
          var splashInput =  jQuery('.fv-player-playlist-item[data-index="'+index+'"] #fv_wp_flowplayer_field_splash');
          splashInput.val(response.src);
        }
        if(response.error){
          message.html('<div class="error"><p>'+response.error+'</p></div>');
          console.log(response.error);
        }
        spinner.remove();
        button.prop("disabled",false);
      });
    }else{
      message.html('<div class="error"><p>Cannot obtain video screenshot, please make sure the video is served with <a href="#">CORS headers</a>.</p></div>');
    }
  });

  api.bind('ready', function(e,api) {
  if(jQuery('.fv-playlist-slider-wrapper').length == 0){
    button.appendTo('.fv-player-shortcode-editor-left');
  }
    api.one('progress', function(e,api){
      try{
      takeScreenshot();
      }catch(err){
      button.addClass('nocors');
      // button.prop("disabled",true);
      }
    });
  });

  });

  // Remove button
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