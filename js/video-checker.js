( function($) {

  flowplayer( function(api,root) {
    root = jQuery(root);

    var media = [api.conf.clip.video_checker[0]];
    var fv_flowplayer_scroll_video_checker = false;
    var fv_flowplayer_scroll_video_checker_status = [];
    
    jQuery(document).ready( function() { fv_flowplayer_scroll_video_checker = true; } );
    jQuery(document).scroll( function() { fv_flowplayer_scroll_video_checker = true; } );

    if( !fv_flowplayer_scroll_video_checker ) return;

    var iMin = jQuery(window).scrollTop();
    var iMax = iMin + jQuery(window).height();

      var iPlayer = jQuery(root).offset().top;
      if( iPlayer > iMin && iPlayer < iMax ) {
        if( typeof(fv_flowplayer_scroll_video_checker_status[jQuery(root).attr('id')]) == "undefined" ) {
          fv_flowplayer_scroll_video_checker_status[jQuery(root).attr('id')] = true;
          var sID = jQuery(root).attr('id').replace(/wpfp_/,'');
          fv_flowplayer_admin_test_media( sID, media );
        }
      }
  });

  function fv_flowplayer_admin_test_media( hash, media ) {
    var hVideoChecker = jQuery('#wpfp_notice_'+hash);
    jQuery('#wpfp_notice_'+hash).parent().append(jQuery('#wpfp_notice_'+hash));
    jQuery('#wpfp_notice_'+hash).show();

    jQuery.post( 'https://video-checker.foliovision.com/', { action: 'vid_check', media: media, hash: hash, site: flowplayer.conf.video_checker_site }, function( response ) {
      var obj;
      try {
        response = response.replace( /[\s\S]*<FVFLOWPLAYER>/, '' );
        response = response.replace( /<\/FVFLOWPLAYER>[\s\S]*/, '' );
        obj = jQuery.parseJSON( response );

        var sCheckerInfo = '';
        var sCheckerDetails = '';
        var sResponseClass = 'vid-ok';
        var sResponseMsg = 'Video OK';

        for( var i in obj ) {
          if( !obj.hasOwnProperty(i) ) continue;
          if( i != "global" ) {
            sCheckerInfo += '<p>Analysis of <a href="'+i+'">'+i+'</a></p>';
          }
          sCheckerInfo += fv_flowplayer_admin_message_parse_group(obj[i].info);
                    
          var sWarnings = (typeof(obj[i].warnings) != "undefined" ) ? fv_flowplayer_admin_message_parse_group(obj[i].warnings) : false;
          if( typeof(obj[i].warnings) != "undefined" && sWarnings ) {
            if( sResponseClass != 'vid-issues' ) {
              sResponseMsg = 'Video Warnings';
              sResponseClass = 'vid-warning';
            }
            sCheckerInfo += sWarnings;
          }

          var sErrors = ( typeof(obj[i].errors) != "undefined" ) ? fv_flowplayer_admin_message_parse_group(obj[i].errors) : false;
          if( typeof(obj[i].errors) != "undefined" && sErrors ) {
            sResponseMsg = fv_flowplayer_translations.video_issues;
            sResponseClass = 'vid-issues';
            sCheckerInfo += sErrors;   
          }

          jQuery('#wpfp_notice_'+hash).find('.video-checker-result').addClass(sResponseClass).html(sResponseMsg);
          
          sCheckerDetails += fv_flowplayer_admin_message_parse_group(obj[i].details);

        }
        jQuery('#wpfp_notice_'+hash).find('.video-checker-result').wrap('<a class="fv_wp_flowplayer_dialog_link"></a>');
        jQuery('#wpfp_notice_'+hash).find('.fv_wp_flowplayer_dialog_link').click( function() { fv_wp_flowplayer_admin_show_notice( hash, this) } );
        jQuery('#wpfp_notice_'+hash).find('.mail-content-notice').html('<p>'+sCheckerInfo+'</p>');
        jQuery('#wpfp_notice_'+hash).find('.mail-content-details .fv-wp-flowplayer-notice-parsed').html(sCheckerDetails)

      } catch(e) {
        console.log(e);
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.error_JSON+'</p>');
        return;
      }

    } ).error(function() { 
      if( /MSIE 9/i.test(navigator.userAgent) ){
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.no_support_IE9+'</p>');
      } else {
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.check_failed+'</p>');
      }
    });
}

})(jQuery);