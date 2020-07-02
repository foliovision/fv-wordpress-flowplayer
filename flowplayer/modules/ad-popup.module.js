/*
 *  Ads
 */
flowplayer(function (api,root) {
  root = jQuery(root);
  var player_id = root.attr('id'),
    ad = false;
  
  if( root.data('end_popup_preview') ){
    jQuery(document).ready( function() {      
      api.trigger('finish', [ api] );
    });
  }
  
  function ad_height_check() {
    var count = 0;
    var ad_height_check = setInterval( function() {
      var height = ad && ad.find('.adsbygoogle').height();
      count++;
      if( count > 20*10 || height > 0 ) clearInterval(ad_height_check);
      if( height > root.height() ) {
        ad.addClass('tall-ad');
      }
    }, 50 );
  }
  
  function show_ad() {
    if( !ad && !root.hasClass('is-cva') && typeof(fv_flowplayer_ad) != "undefined" && typeof(fv_flowplayer_ad[player_id]) != "undefined" && root.width() >= parseInt(fv_flowplayer_ad[player_id].width) ) {
      var html = fv_flowplayer_ad[player_id].html;
      html = html.replace( '%random%', Math.random() );
      ad = jQuery('<div id="'+player_id+'_ad" class="wpfp_custom_ad">'+html+'</div>');
      root.find('.fp-player').append(ad);
      
      ad_height_check();
      // check if the ad contains any video and pause the player if the video is found
      setTimeout( function() {
        if( root.find('.wpfp_custom_ad video').length ) {
          api.pause();
        }
      },500);
    
    }
  }
  
  function show_popup( event ) {
    var popup = root.find('.wpfp_custom_popup');
    if( typeof(fv_flowplayer_popup) != "undefined" && typeof(fv_flowplayer_popup[player_id]) != "undefined" && ( event == 'finish' || fv_flowplayer_popup[player_id].pause || fv_flowplayer_popup[player_id].html.match(/fv-player-ppv-purchase-btn-wrapper/) ) ) {
      root.addClass('is-popup-showing');
      root.find('.fp-player').append( '<div id="'+player_id+'_custom_popup" class="wpfp_custom_popup">'+fv_flowplayer_popup[player_id].html+'</div>' );
    }
  }
  
  api.bind("ready", function (e, api) {
    if (ad.length == 1) {
      ad.remove();
      ad = false;
    }
    if( !root.data('ad_show_after') ) {
      show_ad();
    }
    
  }).bind('progress', function(e,api,current) {
    if (current > root.data('ad_show_after') ){
      show_ad();
    }
  }).bind("finish", function (e, api) {
    if( typeof(api.video.index) == "undefined" || api.video.index+1 == api.conf.playlist.length ) {
      show_popup(e.type);
    }
  }).bind("pause", function (e, api) {
    show_popup(e.type); // todo: only if showing on pause is enabled or FV Player PPV
  }).bind("resume unload seek", function (e, api) {
    if( root.hasClass('is-popup-showing') ) {
      root.find('.wpfp_custom_popup').remove();
      root.removeClass('is-popup-showing');
    }
  });
});

jQuery(document).on('click', '.fv_fp_close', function() {
  var ad = jQuery(this).parents('.wpfp_custom_ad_content'),
    video = ad.find('video');
    
  ad.fadeOut();
  if( video.length ) video[0].pause();
  
  return false;
} );

/*
 *  Popups form
 */
jQuery(document).on('focus','.fv_player_popup input[type=text], .fv_player_popup input[type=email], .fv_player_popup textarea', function() {
  var api = jQuery(this).parents('.flowplayer').data('flowplayer');
  if( api ) api.disable(true);
});
jQuery(document).on('blur','.fv_player_popup input[type=text], .fv_player_popup input[type=email], .fv_player_popup textarea', function() {
  var api = jQuery(this).parents('.flowplayer').data('flowplayer');
  if( api ) api.disable(false);
});