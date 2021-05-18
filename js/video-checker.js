( function($) {

  flowplayer( function( api, root ) {
    root = jQuery(root);

    var fv_flowplayer_scroll_video_checker = false;
    var checked_media = [];
    
    jQuery(document).ready(function() { fv_flowplayer_scroll_video_checker = true; } );
    jQuery(document).on('scroll', function() { fv_flowplayer_scroll_video_checker = true; } );

    var index = api.video.index ? api.video.index : 0;

    // Initial video check on pageload
    setInterval( function() { 
      var iMin = jQuery(window).scrollTop();
      var iMax = iMin + jQuery(window).height();
      var iPlayer = jQuery(root).offset().top;

      if( !fv_flowplayer_scroll_video_checker ) return;
      // console.log('iPlayer',iPlayer,'iMin',iMin,'iMax',iMax);
      if( iPlayer > iMin && iPlayer < iMax ) {
        if(typeof checked_media[index] == "undefined" ) {
          check_media( api, root);
          checked_media[index] = 1;
        }
      }

      fv_flowplayer_scroll_video_checker = false;
    }, 500 );

    // TODO: implement robust solution for another items
    // Another checks for playlist
    // api.bind('load', function(e,api,video){ 
    //   index = api.video.index ? api.video.index : 0;
    //   if( api.conf.playlist.length > 0 ) {
    //     if(typeof checked_media[index] == "undefined") {
    //       check_media( api, root );
    //     }
    //   }
    // });

    // api.bind('error', function(e,api,video){
    //   check_media( api, root );
    // });

  });

  function check_media( api, root) {
    var media;
    var sID = jQuery(root).attr('id').replace(/wpfp_/,'');

    if(api.conf.playlist.length > 0){
      if(typeof api.video.index == "undefined") {
        media = get_media(api.conf.playlist[0].video_checker); // Playlist first
      } else {
        media = get_media(api.conf.playlist[api.video.index].video_checker); // Playlist another
      }
    } else {
      media = get_media(api.conf.clip.video_checker); // Single video
    }
    if(media.length) {
      video_checker(sID,media);
    } else {
      root.find('.fv-player-video-checker').hide();
    }
  }

  function get_media( video_checker ) {
    var temp_media = [];

    if(typeof video_checker == 'undefined') {
      return temp_media;
    }

    video_checker.forEach(function(item, index){
      temp_media.push(item);
    });

    return temp_media;
  }

  function video_checker( sID, media ) {
    jQuery('#wpfp_notice_'+sID).find('.video-checker-result').attr('class','video-checker-result').html('Checking the video file...');
    admin_test_media( sID, media );

    if( typeof(fv_player.admin_input) != "undefined" && fv_player.admin_input ) {
      jQuery(document).on('keyup', function(e) {
        if (e.keyCode == 27) { fv_wp_flowplayer_admin_show_notice(); 	}   // esc
      });

      jQuery(document).on('click', function(event) {
        if( jQuery(event.target).parents('.is-open').length == 0 &&
          jQuery(event.target).parents('.fv-player-video-checker').length == 0 ) {
          if( jQuery('.is-open:visible').length ) {
            fv_wp_flowplayer_admin_show_notice();
          };
        }
      });
    }
  }

  function admin_test_media( hash, media ) {
    var hVideoChecker = jQuery('#wpfp_notice_'+hash);
    jQuery('#wpfp_notice_'+hash).parent().append(jQuery('#wpfp_notice_'+hash));
    jQuery('#wpfp_notice_'+hash).show();

    jQuery.post( 'https://video-checker.foliovision.com/', { action: 'vid_check', media: media, hash: hash, site: flowplayer.conf.video_checker_site }, function( response ) {
      var obj;

      try {
        response = response.replace( /[\s\S]*<FVFLOWPLAYER>/, '' );
        response = response.replace( /<\/FVFLOWPLAYER>[\s\S]*/, '' );
        obj = JSON.parse( response );

        var sCheckerInfo = '';
        var sCheckerDetails = '';
        var sResponseClass = 'vid-ok';
        var sResponseMsg = 'Video OK';

        for( var i in obj ) {
          if( !obj.hasOwnProperty(i) ) continue;
          if( i != "global" ) {
            sCheckerInfo += '<p>Analysis of <a href="'+i+'">'+i+'</a></p>';
          }
          sCheckerInfo += admin_message_parse_group(obj[i].info);

          var sWarnings = (typeof(obj[i].warnings) != "undefined" ) ? admin_message_parse_group(obj[i].warnings) : false;
          if( typeof(obj[i].warnings) != "undefined" && sWarnings ) {
            if( sResponseClass != 'vid-issues' ) {
              sResponseMsg = 'Video Warnings';
              sResponseClass = 'vid-warning';
            }
            sCheckerInfo += sWarnings;
          }

          var sErrors = ( typeof(obj[i].errors) != "undefined" ) ? admin_message_parse_group(obj[i].errors) : false;
          if( typeof(obj[i].errors) != "undefined" && sErrors ) {
            sResponseMsg = fv_flowplayer_translations.video_issues;
            sResponseClass = 'vid-issues';
            sCheckerInfo += sErrors;   
          }

          jQuery('#wpfp_notice_'+hash).find('.video-checker-result').addClass(sResponseClass).html(sResponseMsg);
          
          sCheckerDetails += admin_message_parse_group(obj[i].details);

        }
        if(jQuery('#wpfp_notice_'+hash + ' .fv_wp_flowplayer_dialog_link').length == 0) {
          jQuery('#wpfp_notice_'+hash).find('.video-checker-result').wrap('<a class="fv_wp_flowplayer_dialog_link" href="#"></a>');
        }

        jQuery('#wpfp_notice_'+hash).find('.mail-content-notice').html('<p>'+sCheckerInfo+'</p>');
        jQuery('#wpfp_notice_'+hash).find('.mail-content-details .fv-wp-flowplayer-notice-parsed').html(sCheckerDetails)

      } catch(e) {
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.error_JSON+'</p>');
        return;
      }

    } ).fail(function() { 
      if( /MSIE 9/i.test(navigator.userAgent) ){
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.no_support_IE9+'</p>');
      } else {
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.check_failed+'</p>');
      }
    });
  }

  function admin_message_parse_group(aInfo) {
    var sOutput = '';
    if( typeof(aInfo) != "undefined" && Object.keys(aInfo).length > 0 ) {
      for( var j in aInfo ) {
        if( j == parseInt(j) ){
          sOutput += aInfo[j]+'<br />';
        } else if( typeof(aInfo[j]) == "function" ) {
          continue;
        } else {
          sOutput += j+': <tt>'+aInfo[j]+'</tt><br />';
        }
      }
    }
    if( sOutput.length > 0 ){
      sOutput = '<p>'+sOutput+'</p>';
    }
    return sOutput;
  }

  jQuery(document).on('click','.fv_wp_flowplayer_dialog_link, .fv-player-video-checker-head span', function() {
    var hash = jQuery(this).closest('.fv-player-video-checker').attr('id').replace(/wpfp_notice_/,'');
    fv_wp_flowplayer_admin_show_notice( hash, this);
    return false;
  });

})(jQuery);

function fv_wp_flowplayer_admin_show_notice( id ) {
  jQuery('.fv-player-video-checker').each( function() {
    var is_open = jQuery(this).hasClass('is-open'),
      root = jQuery(this).parents('.flowplayer'),
      api = root.data('flowplayer');
      
    if( jQuery(this).attr('id') == 'wpfp_notice_'+id ) {
      if( is_open ) {
        is_open = false;
      } else {
        is_open = true;
      }
    }
    
    if( id == null ) {
      is_open = false;
    }
    
    jQuery(this).toggleClass("is-open", is_open );
    jQuery(this).find(".fv-player-video-checker-details").toggle( is_open );
    
    root.toggleClass( 'has-video-checker', is_open );
    
    api.disable( is_open );
  });
}

function fv_wp_flowplayer_admin_support_mail( hash, button ) {
  jQuery('.fv_flowplayer_submit_error').remove();

  var comment_text = jQuery('#wpfp_support_'+hash).val();
  var comment_words = comment_text.split(/\s/);
  if( comment_words.length == 0 || comment_text.match(/Enter your comment/) ) {
    jQuery('#wpfp_support_'+hash).before('<p class="fv_flowplayer_submit_error" style="display:none; "><strong>'+fv_flowplayer_translations.what_is_wrong+'</strong></p>');
    jQuery('.fv_flowplayer_submit_error').fadeIn();
    return false;
  }

  if( comment_words.length < 7 ) {
    jQuery('#wpfp_support_'+hash).before('<p class="fv_flowplayer_submit_error" style="display:none; "><strong>'+fv_flowplayer_translations.full_sentence+'</strong>:</p>');
    jQuery('.fv_flowplayer_submit_error').fadeIn();					
    return false;
  }

  jQuery('#wpfp_spin_'+hash).show();
  jQuery(button).attr("disabled", "disabled");

  jQuery.post(
    fv_player.ajaxurl,
    {
      action: 'fv_wp_flowplayer_support_mail',
      comment: comment_text,
      notice: jQuery('#wpfp_notice_'+hash+' .mail-content-notice').html(),
      details: jQuery('#wpfp_notice_'+hash+' .mail-content-details').html()
    },
    function( response ) {
      jQuery('#wpfp_spin_'+hash).hide();
      jQuery(button).removeAttr("disabled");
      jQuery(button).after(' Message sent');
    }	
  );
}