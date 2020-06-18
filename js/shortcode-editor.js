// What's here needs to stay global

// used in FV Player Pro to add more matchers
var fv_player_editor_matcher = {
  default: {
    // matches URL of the video
    matcher: /\.(mp4|webm|m3u8)$/i,
    // AJAX will return these fields which can be auto-updated via JS
    update_fields: ['duration', 'last_video_meta_check'],
  }
};

// TODO: Remove, only used in FV Player Pro for no reason as shortcode is deprecated
var fv_wp_fp_shortcode;

// The actual editor
var fv_player_editor = (function($) {
  
var $doc = $(document),
    $win = $(window),
    el_editor,
    el_preview,
    el_preview_refresh;
  
  // data to save in Ajax
  var ajax_save_this_please = false;
  
  var current_player_db_id = -1;
  
  var current_video_to_edit = -1;
  
  // stores the button which has clicked to open the editor
  var editor_button_clicked = 0;
  
  // the post editor content being edited
  var editor_content;
  
  // used in WP Heartbeat
  var edit_lock_removal = {};
  
  // used to size the lightbox in editor_resize()
  var editor_resize_height_record = 0;
  
  // TinyMCE instance, if any
  var instance_tinymce;
  
  // Foliopress WYSIWYG instance, if any
  var instance_fp_wysiwyg;  
  
  // are you editing player which is not yet in DB?
  var is_draft = true;
  var is_draft_changed = false;
  
  var is_loading_preview = false;
  
  var is_loading_video_data = 0; // will be > 0 when any meta data are loading that needs saving along with the form (example: S3 video duration)
  // ... this prevents overlay closing until all meta required data are loaded and stored
  
  var is_playlist_active = false;
  var is_singular_active = false;
  
  var item_index = -1;
  
  // is it currently saving data?
  var is_saving = false;
  
  // used when editing shortcode in TinyMCE
  var helper_tag;
  
  // We disable preview in certain browsers
  var ua = window.navigator.userAgent;
  var preview_not_supported = ua.match(/edge/i) || ua.match(/safari/i) && !ua.match(/chrome/i) ;
  
  // Should preview only show single video? If so, which one in the current playlist?
  var preview_single = -1;
    
  // the part of shortcode outside of [fvplayer id="XYZ"]
  // also accessed from outside
  var shortcode_remains;  
  
  // Some shortcode args should be kept. For example if you edit
  // [fvplayer id="1 sort="newest"] that sort should not be removed
  var store_shortcode_args = false;
  
  // Flowplayer only lets us specify the RTMP server for the first video in plalist, so we store it here when the playlist item order is changing etc.
  var store_rtmp_server = '';
  
  // stores parts of editor HTML which are later re-used when adding new items
  var template_playlist_item;
  var template_video;
  var template_subtitles;
  var template_subtitles_tab;
  
  // used to remember which widget we are editing, if any
  var widget_id;

  var fv_player_preview_window;

  
  var fv_wp_flowplayer_save_ignore_errors = false;
  
  /*
   * A shorthand to save you from all the "fv_wp_flowplayer_field_"
   * when selecting fields
   * 
   * @param {string}         key    The field key. For example "src" gives 
   *                                you "fv_wp_flowplayer_field_src"
   * @param {object|string}  where  Lets you narrow down the element wher you
   *                                want to locate he field. You can use a jQuery
   *                                element or a string selector for jQuery
   * 
   * @return {object}               The field element
   */
  function get_field( key, where ) {
    var element = false,
      selector = '.fv_wp_flowplayer_field_'+key+', [name=fv_wp_flowplayer_field_'+key+']';
    
    if( where && typeof(where) == "object" ) {
      element = where.find(selector);
    } else if( where && typeof(where) == "string" ) {
      element = el_editor.find(where).find(selector);
    } else {
      element = el_editor.find(selector);
    }
    
    if( !element.length ) {
      console.log('FV Player Editor Error: field '+key+' not found');
    }
    
    return element;
  }
  
  /*
   * Gives you "src" out of "fv_wp_flowplayer_field_src"
   * 
   * @param {string}    name  The field name
   * 
   * @return {string}         The field.... real name?
   */
  function get_field_name( name ) {
    if (name.indexOf('fv_wp_flowplayer_field_') > -1) {
      return name.replace('fv_wp_flowplayer_field_', '');
    }
    return name;
  }
  
  /*
   * Gives you the desired tab with video information
   * 
   * @param {int|string}  index   Number, or first, or last
   * @param {string}      tab     Tab name to obtain, options:
   *                              * video-files
   *                              * subtitles
   * 
   * @return {object}             The tab element
   */
  function get_tab( index, tab ) {
    var selector = '.fv-player-tab-'+tab+' table';
    if( index == 'first' ) {
      selector += ':first';
    } else if( index == 'last' ) {
      selector += ':last';
    } else {
      selector += '[data-index='+index+']';
    }
    return el_editor.find(selector);
  }
  
   /*
   * Gives you all desired tabs of a certain kind
   * 
   * @return {object}            The tab elements
   */
  function get_tabs( tab ) {
    var selector = '.fv-player-tab-'+tab+' table';
    return el_editor.find(selector);
  }
  
  function b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode('0x' + p1);
    }));
  }

  $doc.ready( function(){
    el_editor = $('#fv-player-shortcode-editor');
    
    el_preview = $('#fv-player-shortcode-editor-preview');
    
    el_preview_refresh = $('#fv-player-shortcode-editor-preview-iframe-refresh');
    
    el_preview_target = $('#fv-player-shortcode-editor-preview-target');

    var
      previous = false,
      next = false,
      overlay_close_waiting_for_save = false,
      loading = true,
      int_keyup = false;

    $(window).on('beforeunload', function(e) {
      if (is_draft && is_draft_changed) {
        return e.originalEvent.returnValue = 'You have unsaved changes. Are you sure you want to close this dialog and loose them?';
      }
    });

    if( jQuery().fv_player_box ) {
      $doc.on( 'click', '.fv-wordpress-flowplayer-button, .fv-player-editor-button, .fv-player-edit', function(e) {
        editor_button_clicked = this;
        e.preventDefault();
        $.fv_player_box( {
          top: "100px",
          initialWidth: 1100,
          initialHeight: 50,
          width:"1100px",
          height:"100px",
          href: "#fv-player-shortcode-editor",
          inline: true,
          title: 'Add FV Player',
          onComplete : editor_open,
          onClosed : editor_close,
          onOpen: lightbox_open
        } );
        widget_id = $(this).data().number;
      });

      $doc.on( 'click', '.fv-player-export', function(e) {
        var $element = jQuery(this);

        e.preventDefault();
        $.fv_player_box( {
          top: "100px",
          initialWidth: 1100,
          initialHeight: 50,
          width:"1100px",
          height:"100px",
          href: "#fv-player-shortcode-editor",
          inline: true,
          title: 'Export FV Player',
          onComplete : function() {
            overlay_show('loading');

            $.post(ajaxurl, {
              action: 'fv_player_db_export',
              playerID : $element.data('player_id'),
              nonce : $element.data('nonce'),
              cookie: encodeURIComponent(document.cookie),
            }, function(json_export_data) {
              var overlay = overlay_show('export');
              
              overlay.find('textarea').val( $('<div/>').text(json_export_data).html() );

              jQuery('[name=fv_player_copy_to_clipboard]').select();
            }).error(function() {
              overlay_show('message', 'An unexpected error has occurred. Please try again.');
            });
            
          },
          onClosed : overlay_hide,
          onOpen: lightbox_open
        } );

        return false;
      });

      $doc.on( 'click', '.fv-player-import', function(e) {
        var $element = jQuery(this);

        e.preventDefault();
        $.fv_player_box( {
          top: "100px",
          initialWidth: 1100,
          initialHeight: 50,
          width:"1100px",
          height:"100px",
          href: "#fv-player-shortcode-editor",
          inline: true,
          title: 'Import FV Player(s)',
          onComplete : function() {
            overlay_show('import');
          },
          onClosed : overlay_hide,
          onOpen: lightbox_open
        } );

        return false;
      });

      $doc.on( 'click', '.fv-player-remove', function(e) {
        jQuery(this)
          .addClass('fv-player-remove-confirm')
          .removeClass('fv-player-remove')
          .html('Are you sure?')
          .one('mouseleave', function() {
            jQuery(this)
              .removeClass('fv-player-remove-confirm')
              .addClass('fv-player-remove')
              .html('Delete');
          });

        return false;
      });

      $doc.on( 'click', '.fv-player-remove-confirm', function(e) {
        var
          $element = $(this),
          $element_td = $element.parent(),
          $spinner = $('<div class="fv-player-shortcode-editor-small-spinner"></div>');

        $element_td.find('a, span').hide();
        $element.after($spinner);

        jQuery.post(ajaxurl, {
          action: "fv_player_db_remove",
          nonce: $element.data('nonce'),
          playerID: $element.data('player_id')
        }, function(rows_affected){
          if (!isNaN(parseFloat(rows_affected)) && isFinite(rows_affected)) {
            // remove the deleted player's row
            $element.closest('tr').hide('slow', function() {
              jQuery(this).remove();
            });
          } else {
            $spinner.remove();
            
            alert(rows_affected);

            $element_td.find('span, a:not(.fv-player-remove-confirm)').show();
          }
        }).error(function() {
          $spinner.remove();
          
          $element.html('Error');

          $element_td.find('span, a:not(.fv-player-remove-confirm)').show();
        });

        return false;
      });

      $doc.on( 'click', '.fv-player-clone', function(e) {
        var $element = jQuery(this),
          $spinner = $('<div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>');

        $element
          .hide()
          .after($spinner);

        $.post(ajaxurl, {
          action: "fv_player_db_clone",
          nonce: $element.data('nonce'),
          playerID: $element.data('player_id')
        }, function(playerID){
          if (playerID != '0' && !isNaN(parseFloat(playerID)) && isFinite(playerID)) {
          // add the inserted player's row
          $.get(
            fv_player_editor_conf.admin_url + '&id=' + playerID,
            function (response) {
              $('#the-list tr:first').before(jQuery(response).find('#the-list tr:first'));
              $spinner.remove();
              $element.show();
            }).error(function() {
              $spinner.remove();
              $element.show();
            });
          } else {
            $spinner.remove();
            $element.html('Error');
          }
        }).error(function() {
          $spinner
          $element.html('Error');
        });

        return false;
      });
      
    }
    /* 
    * NAV TABS 
    */
    $('.fv-player-tabs-header a').click( function(e) {
      e.preventDefault();
      $('.fv-player-tabs-header a').removeClass('nav-tab-active');
      $(this).addClass('nav-tab-active')
      $('.fv-player-tabs > .fv-player-tab').hide();
      $('.' + $(this).data('tab')).show();

      editor_resize();
    });
    
    /* 
    * Select playlist item 
    * keywords: select item
    */
    $doc.on('click','.fv-player-tab-playlist tr td', function(e) {
      var new_index = $(this).parents('tr').attr('data-index');
      
      preview_single = new_index;
      
      playlist_item_show(new_index);
    });

    $doc.on('input','.fv_wp_flowplayer_field_width', function(e) {
      $('.fv_wp_flowplayer_field_width').val(e.target.value);
    })
    $doc.on('input','.fv_wp_flowplayer_field_height', function(e) {
      $('.fv_wp_flowplayer_field_height').val(e.target.value);
    })
    
    /*
    * Playlist view thumbnail toggle
    */
    var list_style_toggles = $('#fv-player-list-thumb-toggle > a');
    list_style_toggles.click(function(){
      var button = $(this);
      if( button.hasClass('disabled') ) return;
      
      list_style_toggles.removeClass('active');
      $('.fv-player-tab-playlist').toggleClass( 'hide-thumbnails', button.attr('id') === 'fv-player-list-list-view' );
      button.addClass('active');
      
      return false;
    });

    /* 
    * Remove playlist item 
    * keywords: delete playlist items remove playlist items
    */
    $doc.on('click','.fv-player-tab-playlist tr .fvp_item_remove', function(e) {
        jQuery(this)
          .addClass('fvp_item_remove-confirm')
          .html('Are you sure?')
          .one('mouseleave', function() {
            jQuery(this)
              .removeClass('fvp_item_remove-confirm')
              .html('Delete');
          });

        return false;
    });

    $doc.on('click','.fv-player-tab-playlist tr .fvp_item_remove-confirm', function(e) {
      e.stopPropagation();
      var
        $parent = $(e.target).parents('[data-index]'),
        index = $parent.attr('data-index'),
        id = get_tab(index,'video-files').attr('data-id_video'),
        $deleted_videos_element = $('#fv-player-deleted_videos');

      if (id && $deleted_videos_element.val()) {
        $deleted_videos_element.val($deleted_videos_element.val() + ',' + id);
      } else {
        $deleted_videos_element.val(id);
      }

      $parent.remove();
      get_tab(index,'video-files').remove();
      get_tab(index,'subtitles').remove();
      get_tab(index,'cues').remove();
      
      // if no playlist item is left, add a new one
      // TODO: Some better way?
      if( !jQuery('.fv-player-tab-subtitles table[data-index]').length ){
        playlist_item_add();
        jQuery('.fv-player-tab-playlist tr td').click();
      }
      
      preview_show_button();

      $doc.trigger('fv_flowplayer_shortcode_item_delete');
    });
    
    /*
    *  Sort playlist  
    */
    $('.fv-player-tab-playlist table tbody').sortable({
      start: function( event, ui ) {
        store_rtmp_server = get_field( 'rtmp', get_tab('first','video-files') ).val();
      },
      update: function( event, ui ) {
        $doc.trigger('fv-player-editor-sortable-update');
        var new_sort = []; 
        $('.fv-player-tab-playlist table tbody tr').each(function(){
          var
            index = $(this).attr('data-index'),
            video_tab_item = get_tab(index,'video-files'),
            subtitle_tab_item = get_tab(index,'subtitles');

          new_sort.push({
            video_tab_item : video_tab_item.clone(),
            subtitle_tab_item : subtitle_tab_item.clone()
          });

          video_tab_item.remove();
          subtitle_tab_item.remove();
        });

        $.each(new_sort, function(k,v) {
          $('.fv-player-tab-video-files').append(v.video_tab_item);
          $('.fv-player-tab-subtitles').append(v.subtitle_tab_item);
        });
      
        get_field( 'rtmp', get_tab('first','video-files') ).val( store_rtmp_server );
        
        playlist_index();
        
        preview_show_button();

        $doc.trigger('fv_flowplayer_shortcode_item_sort');
        
      },
      axis: 'y',
      //handle: '.fvp_item_sort',
      containment: ".fv-player-tab-playlist"
    });
    
    /*
    * Uploader 
    */
    var fv_flowplayer_uploader;
    var fv_flowplayer_uploader_button;

    $doc.on( 'click', '#fv-player-shortcode-editor .button.add_media', function(e) {
        e.preventDefault();
        
        fv_flowplayer_uploader_button = jQuery(this);
        jQuery('.fv_flowplayer_target').removeClass('fv_flowplayer_target' );
        fv_flowplayer_uploader_button.siblings('input[type=text]').addClass('fv_flowplayer_target' );
                        
        //If the uploader object has already been created, reopen the dialog
        if (fv_flowplayer_uploader) {
            fv_flowplayer_uploader.open();
            return;
        }

        //Extend the wp.media object
        fv_flowplayer_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Add Video',
            button: {
                text: 'Choose'
            },
            multiple: false
        });
        
        fv_flowplayer_uploader.on('open', function() {
          $( document ).trigger( "mediaBrowserOpen" );
          jQuery('.media-router .media-menu-item').eq(0).click();
          jQuery('.media-frame-title h1').text(fv_flowplayer_uploader_button.text());
        });      

        //When a file is selected, grab the URL and set it as the text field's value
        fv_flowplayer_uploader.on('select', function() {
            attachment = fv_flowplayer_uploader.state().get('selection').first().toJSON();

            $('.fv_flowplayer_target').val(attachment.url);
            $('.fv_flowplayer_target').removeClass('fv_flowplayer_target' );
          
            if( attachment.type == 'video' ) {
              if( typeof(attachment.width) != "undefined" && attachment.width > 0 ) {
                $('.fv_wp_flowplayer_field_width').val(attachment.width);
              }
              if( typeof(attachment.height) != "undefined" && attachment.height > 0 ) {
                $('.fv_wp_flowplayer_field_height').val(attachment.height);
              }
              if( typeof(attachment.fileLength) != "undefined" ) {
                $('#fv_wp_flowplayer_file_info').show();
                $('#fv_wp_flowplayer_file_duration').html(attachment.fileLength);
              }
              if( typeof(attachment.filesizeHumanReadable) != "undefined" ) {
                $('#fv_wp_flowplayer_file_info').show();
                $('#fv_wp_flowplayer_file_size').html(attachment.filesizeHumanReadable);
              }
              
            } else if( attachment.type == 'image' && typeof(fv_flowplayer_set_post_thumbnail_id) != "undefined" ) {
              if( jQuery('#remove-post-thumbnail').length > 0 ){
                return;
              }
              jQuery.post(ajaxurl, {
                  action:"set-post-thumbnail",
                  post_id: fv_flowplayer_set_post_thumbnail_id,
                  thumbnail_id: attachment.id,
                  _ajax_nonce: fv_flowplayer_set_post_thumbnail_nonce,
                  cookie: encodeURIComponent(document.cookie)
                }, function(str){
                  var win = window.dialogArguments || opener || parent || top;
                  if ( str == '0' ) {
                    alert( setPostThumbnailL10n.error );
                  } else {
                    jQuery('#postimagediv .inside').html(str);
                    jQuery('#postimagediv .inside #plupload-upload-ui').hide();
                  }
                } );
              
            }
            
            preview_show_button();
        });

        //Open the uploader dialog
        fv_flowplayer_uploader.open();

    });
    
    template_playlist_item = jQuery('.fv-player-tab-playlist table tbody tr').parent().html();
    template_video = get_tab('first','video-files').parent().html();
    template_subtitles = jQuery('.fv-fp-subtitle').parent().html();
    template_subtitles_tab = jQuery('.fv-player-tab-subtitles').html();
    /*
    * Preview
    */
    $doc.on('input', '.fv-player-tabs [name][data-live-update!=false]' ,function(){
      if( !preview_not_supported && jQuery('.fv-player-tab-playlist tr').length < 10 ){
        el_preview_refresh.show();
      }
    });
    
    var fv_player_shortcode_click_element = null;
    $doc.mousedown(function(e) {
        fv_player_shortcode_click_element = jQuery(e.target);
    });

    $doc.mouseup(function(e) {
        fv_player_shortcode_click_element = null;
    });

    $doc.on('blur', '.fv-player-tabs [name][data-live-update!=false]' ,function(){
      if( fv_player_shortcode_click_element && fv_player_shortcode_click_element.hasClass('button-primary') ) {
        return;
      }
      
      preview_show_button();
    });

    $doc.on('keypress', '.fv-player-tabs [name][data-live-update!=false]' ,function(e){
      if(e.key === 'Enter') {
        preview_submit();
      }
    });
    
    el_preview_refresh.click(function(){
      el_preview_refresh.hide();
      
      preview_submit();
    });
    
    /*
    * End of playlist Actions   
    */
  
    jQuery('#fv_wp_flowplayer_field_end_actions').change(function(){
      var value = jQuery(this).val();
      jQuery('.fv_player_actions_end-toggle').hide().find('[name]').val('');
      switch(value){
        case 'redirect': 
          jQuery('#fv_wp_flowplayer_field_' + value).parents('tr').show(); 
          break; 
        case 'popup':
          jQuery('#fv_wp_flowplayer_field_' + value).parents('tr').show();
          jQuery('#fv_wp_flowplayer_field_' + value + '_id').parents('tr').show();
          break;
        case 'email_list':
          jQuery('#fv_wp_flowplayer_field_' + value).parents('tr').show();
          break;
        default:
          preview_show_button();
          break;
      }
    });
    
    /*
    * Preview iframe dialog resize
    */
    $doc.on('fvp-preview-complete',function(e){
      is_loading_preview = false;
      
      el_preview.attr('class','preview-show');
      editor_resize();
    });
    
    /*
    * Video share option
    */
  
    // TODO: Check
    jQuery('#fv_wp_flowplayer_field_share').change(function(){
      var value = jQuery(this).val();
      
      switch(value){
        case 'Custom': 
          jQuery("#fv_wp_flowplayer_field_share_custom").show();
          break;
        default:        
          jQuery("#fv_wp_flowplayer_field_share_custom").hide();
          break;
      }
    });

    $doc.on("change", "#fv-player-shortcode-editor-right input, #fv-player-shortcode-editor-right select", save );

    $doc.on("keyup", "#fv-player-shortcode-editor-right input[type=text], #fv-player-shortcode-editor-right textarea", function() {
      clearTimeout(int_keyup);
      int_keyup = setTimeout( function() {
        save();
      }, 500 );
    });

    $doc.on('fv_flowplayer_shortcode_new', function() {
      $('#fv-player-shortcode-editor .button-primary, .copy_player').show();
    });

    $doc.on('fv_flowplayer_video_meta_load', function() {
      $('#fv-player-shortcode-editor .button-primary, .copy_player').hide();

      // not a good solution!
      setTimeout( function() {
        loading = false;
        is_draft = false;
        is_draft_changed = false;
      },100);
    });

    $doc.on('fv_flowplayer_player_editor_reset', function() {
      loading = true;
      is_draft = true;
      is_draft_changed = false;
    });

    $doc.on('fv_flowplayer_shortcode_item_sort', save );
    $doc.on('fv_flowplayer_shortcode_item_delete', save );

    function save(e){

      if (is_draft) {
        is_draft_changed = true;
      }

      if( loading ) return;

      //console.log('Change?',e.type,e.currentTarget);

      var ajax_data = build_ajax_data(true);
      if( previous && JSON.stringify(ajax_data) == JSON.stringify(previous) ) {
        console.log('Not really!');
        return;
      }

      if( is_saving ) {
        console.log('Still saving!');
        next = ajax_data;
        return;
      }

      previous = ajax_data;

      ajax(ajax_data);

    }

    function ajax( data ) {
      ajax_save_this_please = data;
    }

    function error(msg) {
      is_saving = false;
      el_editor.find('.button-primary').removeAttr('disabled');

      overlay_show('message', 'An unexpected error has occurred. Please try again. '+msg, true );
    }

    setInterval( function() {
      if( !ajax_save_this_please || is_loading_video_data ) return;

      is_saving = true;
      el_editor.find('.button-primary').attr('disabled', 'disabled');

      $('.fv-player-save-waiting').addClass('is-active');

      $.post(ajaxurl+'?fv_player_db_save=1', {
        action: 'fv_player_db_save',
        data: JSON.stringify(ajax_save_this_please),
        nonce: fv_player_editor_conf.preview_nonce,
      }, function(player) {
        try {
          $(player.videos).each( function(k,v) {
            var item = $('.fv-player-playlist-item').eq(k);
            if( !item.data('id_video') ) {
              item.attr('data-id_video',v);
            }
          });

          if( next ) {console.log('There is more to do...');
            ajax(next);
            next = false;
          } else {
            is_saving = false;
            el_editor.find('.button-primary').removeAttr('disabled');
            $('.fv-player-save-waiting').removeClass('is-active');
            $('.fv-player-save-completed').show().delay( 2500 ).fadeOut(400);

            // close the overlay, if we're waiting for the save
            if (overlay_close_waiting_for_save) {
              // add this player's ID into players that no longer need an edit lock
              if (current_player_db_id > 0) {
                edit_lock_removal[current_player_db_id] = 1;
              }
              overlay_close_waiting_for_save = false;
              $.fn.fv_player_box.close();
            }
          }
        } catch(e) {
          error(e);
        }
      }, 'json' ).error(error);

      ajax_save_this_please = false;

    }, 1500 );

    editor_init();

    var $body = jQuery('body');
    $body.on('focus', '#fv_player_copy_to_clipboard', function() {
      this.select();
    });

    $body.on('change', '#fv_wp_flowplayer_field_src', function() {
      var
        $element = jQuery(this),
        $parent_table = $element.closest('table'),
        $playlist_row = jQuery('.fv-player-tab-playlist table tr[data-index="' + $parent_table.attr('data-index') + '"] td.fvp_item_caption'),
        value = $element.val(),
        update_fields = null,
        $caption_element = $parent_table.find('#fv_wp_flowplayer_field_caption'),
        $splash_element = $parent_table.find('#fv_wp_flowplayer_field_splash'),
        $auto_splash_element = $element.siblings('#fv_wp_flowplayer_field_auto_splash'),
        $auto_caption_element = $element.siblings('#fv_wp_flowplayer_field_auto_caption');

      // cancel any previous AJAX call
      if (typeof($element.data('fv_player_video_data_ajax')) != 'undefined') {
        $element.data('fv_player_video_data_ajax').abort();
        $element.removeData('fv_player_video_data_ajax');
      }

      // cancel any previous auto-refresh task
      /*if (typeof($element.data('fv_player_video_auto_refresh_task')) != 'undefined') {
        clearInterval($element.data('fv_player_video_auto_refresh_task'));
        $element.removeData('fv_player_video_auto_refresh_task');
      }*/

      // set jQuery data related to certain meta data that we may have for current video
      if (!$auto_splash_element.length && $splash_element.val() ) {
        // splash for this video was manually updated
        $splash_element.data('fv_player_user_updated', 1);
        console.log('splash for this video was manually updated');
      }

      if (!$auto_caption_element.length && $caption_element.val() ) {
        // caption for this video was manually updated
        $caption_element.data('fv_player_user_updated', 1);
        console.log('caption for this video was manually updated');
      }

      // try to check if we have a suitable matcher
      for (var vtype in fv_player_editor_matcher) {
        if (fv_player_editor_matcher[vtype].matcher.exec(value) !== null) {
          update_fields = (fv_player_editor_matcher[vtype].update_fields ? fv_player_editor_matcher[vtype].update_fields : []);
          break;
        }
      }

      // only make an AJAX call if we found a matcher
      if (update_fields !== null) {
        if (update_fields.length) {
          // add spinners (loading indicators) to all inputs where data are being loaded
          var selector = '#fv_wp_flowplayer_field_src';
          if( update_fields.indexOf('caption') > 0 ) selector += ', #fv_wp_flowplayer_field_splash';
          if( update_fields.indexOf('splash') > 0 ) selector += ', #fv_wp_flowplayer_field_caption';

          $parent_table
            .find(selector)
            .filter(function () {
              var
                $e = jQuery(this),
                updated_manually = $e.val() && typeof($e.data('fv_player_user_updated')) != 'undefined';

              console.log(this.id+' has been updated? '+updated_manually,$e.val());

              if (this.id == 'fv_wp_flowplayer_field_caption' && !updated_manually) {
                // add spinners (loading indicators) to the playlist table
                if ($playlist_row.length) {
                  $playlist_row.html('<div class="fv-player-shortcode-editor-small-spinner"></div>');
                }
              }

              return !updated_manually;
            })
            .parent()
            .append('<div class="fv-player-shortcode-editor-small-spinner"></div>');

          is_loading_video_data++;
          var ajax_call = function () {
            $element.data('fv_player_video_data_ajax', jQuery.post(ajaxurl, {
                action: 'fv_wp_flowplayer_retrieve_video_data',
                video_url: $element.val(),
                cookie: encodeURIComponent(document.cookie),
              }, function (json_data) {
                is_loading_video_data--;
                // check if we still have this element on page
                if ($element.closest("body").length > 0 && update_fields.length) {

                  // update all fields that should be updated
                  for (var i in update_fields) {
                    switch (update_fields[i]) {
                      case 'caption':
                        if (json_data.name) {
                          if (!$caption_element.val() || typeof($caption_element.data('fv_player_user_updated')) == 'undefined') {
                            $caption_element.val(json_data.name);

                            // update caption in playlist table
                            if ($playlist_row.length) {
                              $playlist_row.html('<div>' + json_data.name + '</div>');
                            }
                          }
                        }
                        break;

                      case 'splash':
                        if (json_data.thumbnail) {
                          if (!$splash_element.val() || typeof($splash_element.data('fv_player_user_updated')) == 'undefined') {
                            $splash_element.val(json_data.thumbnail);
                          }
                        }
                        break;

                      case 'auto_splash':
                        if (!$element.siblings('#fv_wp_flowplayer_field_auto_splash').length) {
                          $element.after('<input type="hidden" name="fv_wp_flowplayer_field_auto_splash" id="fv_wp_flowplayer_field_auto_splash" />');
                        }

                        $element.siblings('#fv_wp_flowplayer_field_auto_splash').val(1);

                        fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                          element: jQuery('#fv_wp_flowplayer_field_auto_splash'),
                          meta_section: 'video',
                          meta_key: 'auto_splash',
                          handle_delete: true
                        });
                        break;

                      case 'auto_caption':
                        if (!$element.siblings('#fv_wp_flowplayer_field_auto_caption').length) {
                          $element.after('<input type="hidden" name="fv_wp_flowplayer_field_auto_caption" id="fv_wp_flowplayer_field_auto_caption" />');
                        }

                        $element.siblings('#fv_wp_flowplayer_field_auto_caption').val(1);

                        fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                          element: jQuery('#fv_wp_flowplayer_field_auto_caption'),
                          meta_section: 'video',
                          meta_key: 'auto_caption',
                          handle_delete: true
                        });
                        break;

                      case 'duration':
                        if (json_data.duration) {
                          if (!$element.siblings('#fv_wp_flowplayer_field_duration').length) {
                            $element.after('<input type="hidden" name="fv_wp_flowplayer_field_duration" id="fv_wp_flowplayer_field_duration" />');
                          }

                          var $duration_element = $element.siblings('#fv_wp_flowplayer_field_duration');
                          $duration_element.val(json_data.duration);

                          fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                            element: $duration_element,
                            meta_section: 'video',
                            meta_key: 'duration',
                            handle_delete: true
                          });
                        } else {
                          var $duration_element = $element.siblings('#fv_wp_flowplayer_field_duration');

                          if ($duration_element.length) {
                            $duration_element.val('');

                            fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                              element: $duration_element,
                              meta_section: 'video',
                              meta_key: 'duration',
                              handle_delete: true
                            });
                          }
                        }
                        break;

                      case 'last_video_meta_check':
                        if (json_data.ts) {
                          if (!$element.siblings('#fv_wp_flowplayer_field_last_video_meta_check').length) {
                            $element.after('<input type="hidden" name="fv_wp_flowplayer_field_last_video_meta_check" id="fv_wp_flowplayer_field_last_video_meta_check" />');
                          }

                          $element.siblings('#fv_wp_flowplayer_field_last_video_meta_check').val(json_data.ts);

                          fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                            element: $element.siblings('#fv_wp_flowplayer_field_last_video_meta_check'),
                            meta_section: 'video',
                            meta_key: 'last_video_meta_check',
                            handle_delete: true
                          });
                        } else {
                          var $last_video_meta_check_element = $element.siblings('#fv_wp_flowplayer_field_last_video_meta_check');

                          if ($last_video_meta_check_element.length) {
                            $last_video_meta_check_element.val('');

                            fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                              element: $last_video_meta_check_element,
                              meta_section: 'video',
                              meta_key: 'last_video_meta_check',
                              handle_delete: true
                            });
                          }
                        }
                        break;
                    }
                  }
                }

                $element.removeData('fv_player_video_data_ajax');
                $element.removeData('fv_player_video_data_ajax_retry_count');

                // remove spinners
                $('.fv-player-shortcode-editor-small-spinner').remove();
              }).error(function () {
                is_loading_video_data--;
                // remove element AJAX data
                $element.removeData('fv_player_video_data_ajax');

                // check if we should still retry
                var retry_count = $element.data('fv_player_video_data_ajax_retry_count');
                if (typeof(retry_count) == 'undefined' || retry_count < 2) {
                  ajax_call();
                  $element.data('fv_player_video_data_ajax_retry_count', (typeof(retry_count) == 'undefined' ? 1 : retry_count + 1));
                } else {
                  // maximum retries reached
                  $element.removeData('fv_player_video_data_ajax_retry_count');

                  // check if we still have this element on page
                  if ($element.closest("body").length > 0) {
                    // get this element's table
                    var
                      $parent_table = $element.closest('table'),
                      $playlist_row = jQuery('.fv-player-tab-playlist table tr[data-index="' + $parent_table.attr('data-index') + '"] td.fvp_item_caption');

                    $playlist_row.html($caption_element.val());
                  }
                }

                // remove spinners
                $('.fv-player-shortcode-editor-small-spinner').remove();
              })
            );
          };

          ajax_call();
        }
      }

    });

    jQuery('.fv-player-editor-wrapper').each( function() { fv_show_video( jQuery(this) ) });  //  show last add more button only

    $doc.on( 'fv_flowplayer_shortcode_insert', '.fv-player-editor-field', function() {
      fv_load_video_preview( jQuery(this).parents('.fv-player-editor-wrapper'));
    } );

    /*
    Custom Videos feature
    TODO: Test
    */
    function fv_show_video( wrapper ) {
      if( wrapper.find('.fv-player-editor-field').val() ) {
        wrapper.find('.edit-video').show();
        wrapper.find('.add-video').hide();
      }
      else {
        wrapper.find('.edit-video').hide();
        wrapper.find('.add-video').show();
        wrapper.find('.fv-player-editor-preview').html('');
      }

      jQuery('[data-key='+wrapper.data('key')+'] .fv-player-editor-more').last().show();  //  show last add more button only
    }

    /*
    Custom Videos feature
    TODO: Test
    */
    function fv_remove_video( id ) {
      $( '#widget-widget_fvplayer-'+id+'-text' ).val("");
      fv_show_video(id);
      $('#fv_edit_video-'+id+' .video-preview').html('');
    }

    /*
    Custom Videos feature
    TODO: Test
    */
    function fv_load_video_preview( wrapper ) {
      var shortcode = $(wrapper).find('.fv-player-editor-field').val();
      var indicator = $("<div class='fv-player-editor-player-loading'><span class='waiting spinner is-active'></span></div>").appendTo('.fp-playlist-external');

      if( shortcode && shortcode.length === 0 ) {
        return false;
      }

      shortcode     = shortcode.replace( /(width=[\'"])\d*([\'"])/, "$1320$2" );  // 320
      shortcode     = shortcode.replace( /(height=[\'"])\d*([\'"])/, "$1240$2" ); // 240

      var url = fv_player_editor_conf.home_url + '?fv_player_embed='+fv_player_editor_conf.preview_nonce+'&fv_player_preview=' + b64EncodeUnicode(shortcode);
      $.get(url, function(response) {
        wrapper.find('.fv-player-editor-preview').html( jQuery('#wrapper',response ) );
        $doc.trigger('fvp-preview-complete', [ shortcode, wrapper.data('key'), wrapper ] );
        indicator.remove();
      } );

      fv_show_video(wrapper);
    }

    $doc.on('click','.fv-player-editor-remove', function(e) {console.log('.fv-player-editor-remove');
      var wrapper = $(this).parents('.fv-player-editor-wrapper');
      if( $('[data-key='+wrapper.data('key')+']').length == 1 ) { //  if there is only single video
        wrapper.find('.fv-player-editor-field').val('');
        fv_show_video(wrapper);
      } else {
        wrapper.remove();
        jQuery('.fv-player-editor-wrapper').each( function() { fv_show_video( jQuery(this) ) });  //  show last add more button only
      }
      return false;
    });

    $doc.on('click','.fv-player-editor-more', function(e) {
      var wrapper = $(this).parents('.fv-player-editor-wrapper');
      var new_wrapper = wrapper.clone();
      new_wrapper.find('.fv-player-editor-field').val('');
      fv_show_video(new_wrapper);
      new_wrapper.insertAfter( $('[data-key='+wrapper.data('key')+']:last') );  //  insert after last of the kind
      $(this).hide();

      return false;
    });

    $doc.on( 'click', '.fv-player-shortcode-copy', function(e) {
      var button = $(this);
      fv_player_clipboard( $(this).parents('tr').find('.fv-player-shortcode-input').val(), function() {
        button.html('Ok!');
        setTimeout( function() {
          button.html('Copy');
        }, 1000 );
      }, function() {
        button.html('Error');
      } );
      return false;
    });
    
    $doc.on('click', '#close_error_overlay_ignore_btn', function() {
      overlay_hide();
      fv_wp_flowplayer_save_ignore_errors = true;
      $('.fv_player_field_insert-button:visible, .fv_player_field_update-button:visible').click();
    });

    $doc.on('change', '#players_selector', function() {
      el_editor.find('.button-primary').text('Insert').removeAttr('disabled');
      editor_open(this.value);
    });

    $doc.on('click', '.fv_player_field_insert-button', function() {
      if (is_saving || ajax_save_this_please) {
        // for some reason, clicking on already-disabled primary button re-enables it,
        // so we'll just need to disable it again here
        $(this).attr('disabled', 'disabled');
      } else {
        editor_submit();
      }

    });

    $doc.on('click', '.playlist_add', function() {
      playlist_item_add();
    });

    $doc.on('click', '.playlist_edit', function() {
      return playlist_show();
    });
    
    // prevent closing of the overlay if we have unsaved data
    // unfortunately there is no event for this which we could use
    $.fn.fv_player_box.oldClose = $.fn.fv_player_box.close;
    $.fn.fv_player_box.close = function() {
      if (is_draft && is_draft_changed && !window.confirm('You have unsaved changes. Are you sure you want to close this dialog and loose them?')) {
        return false;
      }

      // prevent closing if we're still saving the data
      if (ajax_save_this_please || is_saving || is_loading_video_data) {
        // if we already have the overlay changed, bail out
        if (overlay_close_waiting_for_save) {
          return;
        }
    
        overlay_close_waiting_for_save = true;
        $('.fv-wp-flowplayer-notice-small, .fv-player-shortcode-editor-small-spinner').hide();
        overlay_show('loading');
    
        // call fv_wp_flowplayer_submit() which will create a repeating task
        // that will check for all meta data being loaded,
        // so we can auto-close this overlay once that's done
        editor_submit();

        return;
      }
      
      // close the dialog if confirmed
      $.fn.fv_player_box.oldClose();
      
      // reset variables
      is_draft = true;
      is_draft_changed = false;

      // manually invoke a heartbeat to remove an edit lock immediatelly
      if (current_player_db_id > -1) {
        // do this asynchronously to allow our cleanup procedures set lock removal data for the next hearbeat
        setTimeout(wp.heartbeat.connectNow, 500);
      }
    }
    
    /*
    Loads and displays a list of all players in a dropdown.
    */
    $doc.on('click', '.copy_player', function() {
      // show loader
      overlay_show('loading');

      $.post(ajaxurl, {
        // TODO: Nonce
        action: 'fv_player_db_retrieve_all_players_for_dropdown',
        cookie: encodeURIComponent(document.cookie),
      }, function (json_data) {
        var overlay = overlay_show('copy_player');
        
        // build the dropdown
        var dropdown = [];
        for (var i in json_data) {
          dropdown.push('<option value="' + json_data[i].id + '">' + (json_data[i].name ? json_data[i].name : 'Player #' + json_data[i].id) + '</option>');
        }

        // prepend the "Choose a player" option
        if (dropdown.length) {
          dropdown.unshift('<option hidden disabled selected value>Choose a Player...</option>');
        }

        overlay.find('select').html( dropdown.join('') );

      }).error(function () {
        overlay_show('message', 'An unexpected error has occurred. Please try again.');

      });

      return false;
    });

  });



  /*
   *  Initializes shortcode, removes playlist items, hides elements, figures out
   *  which actual field is edited - post editor, widget, etc.
   */
  function editor_init() {
    fv_wp_flowplayer_save_ignore_errors = false;
    
    // if error / message overlay is visible, hide it
    overlay_hide();

    // remove hidden meta data inputs
    jQuery('input[name="fv_wp_flowplayer_field_duration"], input[name="fv_wp_flowplayer_field_last_video_meta_check"], input[name="fv_wp_flowplayer_field_auto_splash"], input[name="fv_wp_flowplayer_field_auto_caption"]').remove();

    // stop and remove any pending AJAX requests to retrieve video meta data
    // as well as any auto-update timers
    jQuery('input[name="fv_wp_flowplayer_field_src"]').each(function() {
      var
        $this = jQuery(this),
        ajaxData = $this.data('fv_player_video_data_ajax'),
        //refreshTask = $this.data('fv_player_video_auto_refresh_task'),
        retryData = $this.data('fv_player_video_data_ajax_retry_count');

      if (typeof(ajaxData) != 'undefined') {
        ajaxData.abort();
        $this.removeData('fv_player_video_data_ajax');
      }

      if (typeof(retryData) != 'undefined') {
        $this.removeData('fv_player_video_data_ajax_retry_count');
      }

      /*if (typeof(refreshTask) != 'undefined') {
        clearInterval(refreshTask);
        $this.removeData('fv_player_video_auto_refresh_task');
      }*/
    });

    is_loading_preview = false;

    jQuery('#fv_wp_flowplayer_field_player_name').show();

    jQuery('#player_id_top_text').html('');

    // is there a Custom Video field or Gutenberg field next to the button?
    var field = $(editor_button_clicked).parents('.fv-player-editor-wrapper, .fv-player-gutenberg').find('.fv-player-editor-field');
    if( field.length ) {
      editor_content = jQuery(field).val();

    } else if( jQuery('#widget-widget_fvplayer-'+widget_id+'-text').length ){
      editor_content = jQuery('#widget-widget_fvplayer-'+widget_id+'-text').val();
    } else if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length){
      editor_content = jQuery('#content:not([aria-hidden=true])').val();
    } else if( typeof tinymce !== 'undefined' && typeof tinymce.majorVersion !== 'undefined' && typeof tinymce.activeEditor !== 'undefined' && tinymce.majorVersion >= 4 ){
      instance_tinymce = tinymce.activeEditor;
    } else if( typeof tinyMCE !== 'undefined' ) {
      instance_tinymce = tinyMCE.getInstanceById('content');
    } else if(typeof(FCKeditorAPI) !== 'undefined' ){
      instance_fp_wysiwyg = FCKeditorAPI.GetInstance('content');
    }
    
    jQuery('#fv_wp_flowplayer_file_info').hide();
    jQuery(".fv_wp_flowplayer_field_src2_wrapper").hide();
    jQuery("#fv_wp_flowplayer_field_src2_uploader").hide();
    jQuery(".fv_wp_flowplayer_field_src1_wrapper").hide();
    jQuery("#fv_wp_flowplayer_field_src1_uploader").hide();
    jQuery("#add_format_wrapper").show();
    jQuery(".add_rtmp_wrapper").show(); 
    jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").hide();
    el_preview.attr('class','preview-no');
    
    jQuery('.fv-player-tab-video-files table').each( function(i,e) {
      if( i == 0 ) return;
      jQuery(e).remove();
    } );
    
    jQuery('.fv-player-tab-playlist table tbody tr').each( function(i,e) {
      if( i == 0 ) return;
      jQuery(e).remove();
    } );
    
    jQuery('.fv-player-tab-subtitles').html(template_subtitles_tab);
    jQuery('.fv_wp_flowplayer_field_subtitles_lang').val(0);

    /**
     * TABS 
     */ 
    jQuery('#fv-player-shortcode-editor a[data-tab=fv-player-tab-playlist]').hide();
    jQuery('#fv-player-shortcode-editor a[data-tab=fv-player-tab-video-files]').trigger('click');
    jQuery('.nav-tab').show;

    current_player_db_id = -1;
    item_index = 0;
    
    is_playlist_active = false;
    is_singular_active = true;
    el_editor.attr('class','is-singular is-singular-active');
    
    //hide empy tabs hide tabs
    jQuery('.fv-player-tab-playlist').hide();
    jQuery('.fv-player-playlist-item-title').html('');
    jQuery('.fv-player-tab-video-files table').show();
    
    jQuery('.playlist_edit').html(jQuery('.playlist_edit').data('create')).removeClass('button-primary').addClass('button');

    tabs_refresh();
    
    el_preview_target.html('');
    
    if( typeof(fv_player_shortcode_editor_ajax) != "undefined" ) {
      fv_player_shortcode_editor_ajax.abort();
    }

    $doc.trigger('fv-player-editor-init');
  }
  
  /*
   *  Checks all the input fields and created the JavaScript object.
   *  Works when saving and also previewing.
   */
  function build_ajax_data( give_it_all ) {
    var
        $tabs                  = el_editor.find('.fv-player-tab'),
        regex                  = /((fv_wp_flowplayer_field_|fv_wp_flowplayer_hlskey|fv_player_field_ppv_)[^ ]*)/g,
        data                   = {'video_meta' : {}, 'player_meta' : {}},
        end_of_playlist_action = jQuery('#fv_wp_flowplayer_field_end_actions').val(),
        single_video_showing   = !give_it_all && is_singular_active;
  
    // special processing for end video actions
    if (end_of_playlist_action && end_of_playlist_action != 'Nothing') {
      switch (end_of_playlist_action) {
        case 'redirect':
          data['fv_wp_flowplayer_field_end_action_value'] = jQuery('#fv_wp_flowplayer_field_redirect').val();
          break;
        case 'popup':
          data['fv_wp_flowplayer_field_end_action_value'] = jQuery('#fv_wp_flowplayer_field_popup_id').val();
          break;
        case 'email_list':
          data['fv_wp_flowplayer_field_end_action_value'] = jQuery('#fv_wp_flowplayer_field_email_list').val();
          break;
      }
    }
  
    // add playlist name
    data['fv_wp_flowplayer_field_player_name'] = jQuery('#fv_wp_flowplayer_field_player_name').val();
  
    // trigger meta data save events, so we get meta data from different
    // plugins included as we post
    jQuery(document).trigger('fv_flowplayer_player_meta_save', [data, $tabs]);
  
    $tabs.each(function() {
      var
        $tab = jQuery(this),
        is_videos_tab = $tab.hasClass('fv-player-tab-video-files'),
        is_subtitles_tab = $tab.hasClass('fv-player-tab-subtitles'),
        $tables = ((is_videos_tab || is_subtitles_tab) ? $tab.find('table') : $tab.find('input, select, textarea')),
        save_index = -1;
  
      // prepare video and subtitles data, which are duplicated through their input names
      if (is_videos_tab) {
        data['videos'] = {};
      } else if (is_subtitles_tab) {
        data['video_meta']['subtitles'] = {};
        data['video_meta']['transcript'] = {};
        data['video_meta']['chapters'] = {};
      }
  
      // iterate over all tables in tabs
      $tables.each(function() {
        // only videos, subtitles tabs have tables, so we only need to search for their inputs when working with those
        var
          $inputs = ((is_videos_tab || is_subtitles_tab) ? jQuery(this).find('input, select, textarea') : jQuery(this)),
          table_index = jQuery(this).data('index');
        save_index++;
  
        $inputs.each(function() {
          var
            $this               = jQuery(this),
            $parent_tr          = $this.closest('tr'),
            optionsHaveNoValue = false, // will become true for dropdown options without values
            $valueLessOptions   = null,
            isDropdown          = this.nodeName == 'SELECT';
  
          // exceptions for selectively hidden fields, i.e. empty tabs with no content etc.
          if ($parent_tr.hasClass('fv_player_interface_hide') && $parent_tr.css('display') == 'none') {
            //return;
            // why? hidden tabs would have no content... have you tested this? maybe we should return the return? :-P
          }
  
          // check for a select without any option values, in which case we'll use their text
          if (isDropdown) {
            $valueLessOptions = $this.find('option:not([value])');
            if ($valueLessOptions.length == this.length) {
              optionsHaveNoValue = true;
            }
          }
  
          while ((m = regex.exec(this.name)) !== null) {
            // This is necessary to avoid infinite loops with zero-width matches
            if (m.index === regex.lastIndex) {
              regex.lastIndex++;
            }
            // let plugins update video meta, if applicable
            jQuery(document).trigger('fv_flowplayer_video_meta_save', [data, save_index, this]);
            // videos tab
            if (is_videos_tab) {
              if (!data['videos'][save_index]) {
                data['videos'][save_index] = {
                  id: jQuery('.fv-player-playlist-item[data-index=' + table_index + ']').data('id_video')
                };
              }
  
              // check for a meta field
              if (fv_wp_flowplayer_check_for_video_meta_field(m[1])) {
                // prepare HLS data, if not prepared yet
                if (!data['video_meta']['video']) {
                  data['video_meta']['video'] = {};
                }
  
                if (!data['video_meta']['video'][save_index]) {
                  data['video_meta']['video'][save_index] = {};
                }
  
                fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                  data: data,
                  meta_section: 'video',
                  meta_key: get_field_name(m[1]),
                  meta_index: save_index,
                  element: this
                });
              } else {
                // ordinary video field
                // check dropdown for its value based on values in it
                if (isDropdown) {
                  var opt_value = fv_wp_flowplayer_get_correct_dropdown_value(optionsHaveNoValue, $valueLessOptions, this);
                  // if there were any problems, just return an empty object
                  if (opt_value === false) {
                    return {};
                  } else {
                    data['videos'][save_index][m[1]] = opt_value;
                  }
                } else {
                  data['videos'][save_index][m[1]] = this.value;
                }
              }
            }
  
            // subtitles tab, subtitles inputs
            else if (is_subtitles_tab) {
              if($this.hasClass('fv_wp_flowplayer_field_subtitles')) {
                if (!data['video_meta']['subtitles'][save_index]) {
                  data['video_meta']['subtitles'][save_index] = [];
                }
  
                // jQuery-select the SELECT element when we get an INPUT, since we need to pair them
                if (this.nodeName == 'INPUT') {
                  data['video_meta']['subtitles'][save_index].push({
                    code : $this.siblings('select:first').val(),
                    file : this.value,
                    id: $this.parent().data('id_subtitles')
                  });
                }
              }
  
              // subtitles tab, chapters input
              else if ($this.attr('id') == 'fv_wp_flowplayer_field_chapters') {
                if (!data['video_meta']['chapters'][save_index]) {
                  data['video_meta']['chapters'][save_index] = {};
                }
  
                fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                  data: data,
                  meta_section: 'chapters',
                  meta_key: 'file',
                  meta_index: save_index,
                  element: $this
                });
              }
              
              // subtitles tab, transcript input
              else if (is_subtitles_tab && $this.hasClass('fv_wp_flowplayer_field_transcript')) {
                if (!data['video_meta']['transcript'][save_index]) {
                  data['video_meta']['transcript'][save_index] = {};
                }
  
                fv_flowplayer_insertUpdateOrDeleteVideoMeta({
                  data: data,
                  meta_section: 'transcript',
                  meta_key: 'file',
                  meta_index: save_index,
                  element: $this
                });
              }
            }
  
            // all other tabs
            else {
              if (this.nodeName == 'INPUT' && this.type.toLowerCase() == 'checkbox') {
                // some player attributes are meta data
                if (fv_wp_flowplayer_check_for_player_meta_field(m[1])) {
                  // meta data input
                  fv_flowplayer_insertUpdateOrDeletePlayerMeta({
                    data: data,
                    meta_section: 'player',
                    meta_key: get_field_name(m[1]),
                    element: this,
                    handle_delete: false
                  });
                } else {
                  // ordinary player attribute
                  data[m[1]] = (this.type.toLowerCase() == 'checkbox' ? this.checked ? 'true' : '' : this.value);
                }
              } else {
                // check dropdown for its value based on values in it
                if (isDropdown) {
                  var opt_value = fv_wp_flowplayer_get_correct_dropdown_value(optionsHaveNoValue, $valueLessOptions, this);
                  // if there were any problems, just return an empty object
                  if (opt_value === false) {
                    return {};
                  } else {
                    if (fv_wp_flowplayer_check_for_player_meta_field(m[1])) {
                      // meta data input
                      fv_flowplayer_insertUpdateOrDeletePlayerMeta({
                        data: data,
                        meta_section: 'player',
                        meta_key: get_field_name(m[1]),
                        element: this,
                        handle_delete: false
                      });
                    } else {
                      // ordinary player attribute
                      data[m[1]] = opt_value.toLowerCase();
                    }
                  }
                } else {
                  if (fv_wp_flowplayer_check_for_player_meta_field(m[1])) {
                    // meta data input
                    fv_flowplayer_insertUpdateOrDeletePlayerMeta({
                      data: data,
                      meta_section: 'player',
                      meta_key: get_field_name(m[1]),
                      element: this,
                      handle_delete: false
                    });
                  } else {
                    // ordinary player attribute
                    data[m[1]] = this.value;
                  }
                }
              }
            }
          }
        });
      });
    });
  
    // remove any empty videos, i.e. without a source
    // this is used when loading data from DB to avoid previewing an empty video that's in editor by default
    if (data['videos']) {
      var
        data_videos_new = {},
        x = 0;
  
      for (var i in data['videos']) {
        if (data['videos'][i]['src'] || data['videos'][i]['src1'] || !data['videos'][i]['src2']) {
          // if we should show preview of a single video only, add that video here,
          // otherwise add all videos here
          if (!single_video_showing || x == item_index) {
            data_videos_new[x++] =  data['videos'][i];
          } else {
            x++;
          }
        }
      }
  
      data['videos'] = data_videos_new;
    }
  
    // add player ID and deleted elements for a DB update
    var $updateElement = jQuery('#fv-player-id_player');
    if ($updateElement.length) {
      data['update'] = $updateElement.val();
      data['deleted_videos'] = jQuery('#fv-player-deleted_videos').val();
      data['deleted_video_meta'] = jQuery('#fv-player-deleted_video_meta').val();
      data['deleted_player_meta'] = jQuery('#fv-player-deleted_player_meta').val();
    }
  
    return data;
  }
  
  /*
   *  Closing the editor
   *  * updates the wp-admin -> FV Player screen
   *  * sets data for WordPress Heartbeat to unlock the player
   *  * calls editor_init() for editor clean-up
   */
  function editor_close() {
    // remove TinyMCE hidden tag which aids shortcode editing
    // to prevent opening the same player over and over
    editor_content = editor_content.replace(fv_wp_flowplayer_re_insert,'');
    set_post_editor_content(editor_content);

    // this variable needs to be reset here and not in editor_init
    current_video_to_edit = -1;
  
    if ( !is_fv_player_screen(editor_button_clicked) ) {
      // todo: what it the point of this call being made?
      // TODO: Perhaps to ensure the temporary strings in editor are removed?
      //set_post_editor_content( editor_content.replace( fv_wp_flowplayer_re_insert, '' ) );
      
      // trigger update for the FV Player Custom Videos/Meta Box
      var field = $(editor_button_clicked).parents('.fv-player-editor-wrapper').find('.fv-player-editor-field');
      field.trigger('fv_flowplayer_shortcode_insert');
      
    } else if( current_player_db_id > -1 ) {
      var playerRow = $('#the-list span[data-player_id="' + current_player_db_id + '"]')
      if( playerRow.length == 0 ) {
        var firstRow = $('#the-list tr:first'),
          newRow = firstRow.clone();
          
        newRow.find('td').html('');
        playerRow = newRow.find('td').eq(0);
        
        firstRow.before( newRow )
      }
      
      playerRow.append('&nbsp; <div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>');
      $.get(
        fv_player_editor_conf.admin_url + '&id=' + current_player_db_id,
        function (response) {
          playerRow.closest('tr').replaceWith( $(response).find('#the-list tr') );
        });
      
    }
  
    // we need to do this now to make sure Heartbeat gets the correct data
    if (current_player_db_id > -1 ){
      edit_lock_removal[current_player_db_id] = 1;
      current_player_db_id = -1;
    }
    
    editor_init();
    
  }
  
    
  /*
  * removes previous values from editor
  * fills new values from shortcode
  * 
  * @param {int} db_id Optional, force load of specified player ID
  */
  function editor_open(db_id) {
    editor_resize_height_record = 0;
    
    $('#fv_player_box').removeAttr('tabindex');
    
    editor_init();

    // remove any DB data IDs that may be left in the form
    el_editor.find('[data-id]').removeData('id').removeAttr('data-id');
    el_editor.find('[data-id_video]').removeData('id_video').removeAttr('data-id_video');
    el_editor.find('[data-id_subtitles]').removeData('id_subtitles').removeAttr('data-subtitles');

    // fire up editor reset event, so plugins can clear up their data IDs as well
    var $doc = jQuery(document);
    $doc.trigger('fv_flowplayer_player_editor_reset');

    // reset content of any input fields, except what has .extra-field    
    el_editor.find("input:not(.extra-field)").each( function() { jQuery(this).val( '' ); jQuery(this).attr( 'checked', false ) } );
    el_editor.find("textarea").each( function() { jQuery(this).val( '' ) } );
    el_editor.find('select').prop('selectedIndex',0);
    el_editor.find("[name=fv_wp_flowplayer_field_caption]").each( function() { jQuery(this).val( '' ) } );
    el_editor.find("[name=fv_wp_flowplayer_field_caption]").each( function() { jQuery(this).val( '' ) } );
    el_editor.find("[name=fv_wp_flowplayer_field_splash_text]").each( function() { jQuery(this).val( '' ) } );
    el_editor.find(".fv_player_field_insert-button").text( 'Insert' );

    var field = $(editor_button_clicked).parents('.fv-player-editor-wrapper, .fv-player-gutenberg').find('.fv-player-editor-field');

    if (!db_id) {
      // custom Field
      if (field.length || jQuery('#widget-widget_fvplayer-' + widget_id + '-text').length) {
        // this is a horrible hack as it adds the hidden marker to the otherwise clean text field value
        // just to make sure the shortcode varible below is parsed properly.
        // But it allows some extra text to be entered into the text widget, so for now - ok
        if (editor_content.match(/\[/)) {
          editor_content = '[' + helper_tag + editor_content.replace('[', '') + '';
        } else {
          editor_content = helper_tag + editor_content + '';
        }

        // Foliopress WYSIWYG
      } else if (typeof (FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length) {
        var bFound = false;
        var position = jQuery('#content:not([aria-hidden=true])').prop('selectionStart');
        for (var start = position; start--; start >= 0) {
          if (editor_content[start] == '[') {
            bFound = true;
            break;
          } else if (editor_content[start] == ']') {
            break
          }
        }
        var shortcode = [];

        if (bFound) {
          var temp = editor_content.slice(start);
          temp = temp.match(/^\[fvplayer[^\[\]]*]?/);
          if (temp) {
            shortcode = temp;
            editor_content = editor_content.slice(0, start) + '#fvp_placeholder#' + editor_content.slice(start).replace(/^\[[^\[\]]*]?/, '');
          } else {
            editor_content = editor_content.slice(0, position) + '#fvp_placeholder#' + editor_content.slice(position);
          }
        } else {
          editor_content = editor_content.slice(0, position) + '#fvp_placeholder#' + editor_content.slice(position);
        }

        // Edit button on wp-admin -> FV Player screen
      } else if (is_fv_player_screen_edit(editor_button_clicked)) {
        current_player_db_id = $(editor_button_clicked).data('player_id');

        // create an artificial shortcode from which we can extract the actual player ID later below
        editor_content = '[fvplayer id="' + current_player_db_id + '"]';
        shortcode = [editor_content];

        // Add new button on wp-admin -> FV Player screen
      } else if (is_fv_player_screen_add_new(editor_button_clicked)) {
        // create empty shortcode for Add New button on the list page
        editor_content = '';
        shortcode = '';

        // TinyMCE in Text Mode
      } else if (instance_tinymce == undefined || typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor.isHidden()) {
        editor_content = instance_fp_wysiwyg.GetHTML();
        if (editor_content.match(fv_wp_flowplayer_re_insert) == null) {
          instance_fp_wysiwyg.InsertHtml(helper_tag);
          editor_content = instance_fp_wysiwyg.GetHTML();
        }

      } else {
        // TinyMCE in Visual Mode
        editor_content = instance_tinymce.getContent();
        instance_tinymce.settings.validate = false;
        if (editor_content.match(fv_wp_flowplayer_re_insert) == null) {
          var tags = ['b', 'span', 'div'];
          for (var i in tags) {
            instance_tinymce.execCommand('mceInsertContent', false, '<' + tags[i] + ' data-mce-bogus="1" rel="FCKFVWPFlowplayerPlaceholder"></' + tags[i] + '>');
            editor_content = instance_tinymce.getContent();

            fv_wp_flowplayer_re_edit = new RegExp('\\[f[^\\]]*?<' + tags[i] + '[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?</' + tags[i] + '>.*?[^\]\\]', "mi");
            fv_wp_flowplayer_re_insert = new RegExp('<' + tags[i] + '[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?</' + tags[i] + '>', "gi");

            if (editor_content.match(fv_wp_flowplayer_re_insert)) {
              break;
            }

          }

        }
        instance_tinymce.settings.validate = true;
      }
    }

    var content = editor_content.replace(/\n/g, '\uffff');

    // if we've got a numeric DB ID passed to this function, use it directly
    // but don't replace editor_content, since we'll need that to be actually updated
    // rather then set to a player ID
    if (db_id) {
      content = db_id;
    }

    if(typeof(shortcode) == 'undefined'){
      if (!db_id) {
        var shortcode = content.match( fv_wp_flowplayer_re_edit );
      } else {
        var shortcode = ['fvplayer id="' + db_id + '"'];
      }
    }

    // remove visual editor placeholders etc.
    if (shortcode && shortcode[0]) {
      shortcode = shortcode[0]
        .replace(/^\[|]+$/gm, '')
        .replace(fv_wp_flowplayer_re_insert, '')
        .replace(/\\'/g, '&#039;');
    }

    if( shortcode != null && typeof(shortcode) != 'undefined' && typeof(shortcode[0]) != 'undefined') {
      // check for new, DB-based player shortcode
      var result = /fvplayer id="([\d,]+)"/g.exec(shortcode);
      if (result !== null) {
        var
          shortcode_parse_fix = shortcode
                                  .replace(/(popup|ad)='[^']*?'/g, '')
                                  .replace(/(popup|ad)="(.*?[^\\\\/])"/g, '');

        shortcode_remains = shortcode_parse_fix.replace( /^\S+\s*?/, '' );

        store_shortcode_args = {};
        for( var i in fv_player_editor_conf.shortcode_args_to_preserve ) {
          var value = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, fv_player_editor_conf.shortcode_args_to_preserve[i] );
          if (value && value[1]) {
            store_shortcode_args[fv_player_editor_conf.shortcode_args_to_preserve[i]] = value[1];
          }
        }
        
        // DB-based player, create a "wait" overlay
        overlay_show('loading');

        // store player ID into fv_player_conf, so we can keep sending it
        // in WP heartbeat
        current_player_db_id = result[1];

        if (edit_lock_removal[result[1]]) {
          delete edit_lock_removal[result[1]];
        }

        // check if we don't have multiple-playlists shortcode,
        // in which case we need to stop and show an error message
        if (shortcode.indexOf(',') > -1) {
          overlay_show('message', 'Shortcode editor is not available for multiple players shortcode tag.');
          return;
        }

        // now load playlist data
        // load video data via an AJAX call
        fv_player_shortcode_editor_ajax = jQuery.post(ajaxurl, {
          action : 'fv_player_db_load',
          nonce : fv_player_editor_conf.db_load_nonce, 
          playerID :  result[1]
        }, function(response) {
          var vids = response['videos'];

          if (response) {
            if( typeof(response) != "object" ) {
              overlay_show('message', 'Error: '+response);
              return;
            }
            
            var
              $id_player_element = jQuery('#fv-player-id_player'),
              $deleted_videos_element = jQuery('#fv-player-deleted_videos'),
              $deleted_video_meta_element = jQuery('#fv-player-deleted_video_meta'),
              $deleted_player_meta_element = jQuery('#fv-player-deleted_player_meta');

            // remove everything with index 0 and the initial video placeholder,
            // otherwise our indexing & previews wouldn't work correctly
            jQuery('[data-index="0"]').remove();
            jQuery('.fv-player-tab-playlist table tbody tr').remove();
            jQuery('.fv-player-tab-video-files table').remove();

            if (!$id_player_element.length) {
              // add player ID as a hidden field
              el_editor.append('<input type="hidden" name="id_player" id="fv-player-id_player" value="' + result[1] + '" />');

              // add removed video IDs as a hidden field
              el_editor.append('<input type="hidden" name="deleted_videos" id="fv-player-deleted_videos" />');

              // add removed video meta IDs as a hidden field
              el_editor.append('<input type="hidden" name="deleted_video_meta" id="fv-player-deleted_video_meta" />');

              // add removed player meta IDs as a hidden field
              el_editor.append('<input type="hidden" name="deleted_player_meta" id="fv-player-deleted_player_meta" />');
            } else {
              $id_player_element.val(result[1]);
              $deleted_videos_element.val('');
              $deleted_video_meta_element.val('');
              $deleted_player_meta_element.val('');
            }

            // fire the player load event to cater for any plugins listening
            var $doc = jQuery(document);
            $doc.trigger('fv_flowplayer_player_meta_load', [response]);

            // used several times below, so it's in a function
            function set_player_field(key, value, id, video_table_index) {
              var
                real_key = fv_wp_flowplayer_map_names_to_editor_fields(key),
                real_val = fv_wp_flowplayer_map_db_values_to_field_values(key, value),
                // try ID first
                $element = jQuery((typeof(video_table_index) != 'undefined' ? '.fv-player-tab table[data-id_video=' + video_table_index + '] ' : '') + '#' + real_key);

              // special processing for end video actions
              if (real_key == 'fv_wp_flowplayer_field_end_action_value') {
                var end_of_playlist_action = jQuery('#fv_wp_flowplayer_field_end_actions').val();

                // to actually show the value, we need to trigger a change event on the end_actions dropdown itself
                jQuery('#fv_wp_flowplayer_field_end_actions').trigger('change');

                switch (end_of_playlist_action) {
                  case 'redirect':
                    jQuery('#fv_wp_flowplayer_field_redirect').val(value);
                    break;
                  case 'popup':
                    jQuery('#fv_wp_flowplayer_field_popup_id').val(value);
                    break;

                  case 'email_list':
                    jQuery('#fv_wp_flowplayer_field_email_list').val(value);
                    break;
                }

                return;
              } else if (['fv_wp_flowplayer_field_email_list', 'fv_wp_flowplayer_field_popup_id', 'fv_wp_flowplayer_field_redirect'].indexOf(real_key) > -1) {
                // ignore the original fields, if we still use old DB values
                return;
              }

              if (!$element.length) {
                // no element with this ID found, we need to go for a name
                $element = jQuery((typeof(video_table_index) != 'undefined' ? '.fv-player-tab table[data-id_video=' + video_table_index + '] ' : '') + '[name="' + real_key + '"]');
              }

              // player and video IDs wouldn't have corresponding fields
              if ($element.length) {
                // dropdowns could have capitalized values
                if ($element.get(0).nodeName == 'SELECT') {
                  if ($element.find('option[value="' + real_val + '"]').length) {
                    $element.val(real_val);
                  } else {
                    // try capitalized
                    var caps = real_val.charAt(0).toUpperCase() + real_val.slice(1);
                    $element.find('option').each(function() {
                      if (this.text == caps) {
                        jQuery(this).attr('selected', 'selected');
                      }
                    });
                  }
                } else if ($element.get(0).nodeName == 'INPUT' && $element.get(0).type.toLowerCase() == 'checkbox') {
                  if (real_val === '1' || real_val === 'on' || real_val === 'true') {
                    $element.attr('checked', 'checked');
                  } else {
                    $element.removeAttr('checked');
                  }
                } else {
                  $element.val(real_val);
                }

                // if an ID exists, this is a meta field
                // and the data id needs to be added to it as well
                if (typeof(id) != 'undefined') {
                  $element.attr('data-id', id);
                }
              }
            }

            for (var key in response) {
              // put the field value where it belongs
              if (key !== 'videos') {
                // in case of meta data, proceed with each player meta one by one
                if (key == 'meta') {
                  for (var i in response[key]) {
                    set_player_field(response[key][i]['meta_key'], response[key][i]['meta_value'], response[key][i]['id']);
                  }
                } else {
                  set_player_field(key, response[key]);
                }
              }
            }

            // add videos from the DB
            for (var x in vids) {
              var
                subs = [],
                transcript = null,
                chapters = null,
                video_meta = [];

              // add all subtitles, chapters and transcripts
              if (vids[x].meta && vids[x].meta.length) {
                for (var m in vids[x].meta) {
                  // subtitles
                  if (vids[x].meta[m].meta_key.indexOf('subtitles') > -1) {
                    subs.push({
                      lang: vids[x].meta[m].meta_key.replace('subtitles_', ''),
                      file: vids[x].meta[m].meta_value,
                      id: vids[x].meta[m].id
                    });
                  }

                  // chapters
                  if (vids[x].meta[m].meta_key.indexOf('chapters') > -1) {
                    chapters = {
                      id: vids[x].meta[m].id,
                      value: vids[x].meta[m].meta_value
                    };
                  }

                  // transcript
                  if (vids[x].meta[m].meta_key === 'transcript') {
                    transcript = {
                      id: vids[x].meta[m].id,
                      value: vids[x].meta[m].meta_value
                    };
                  }

                  // general video meta
                  if (vids[x].meta[m].meta_key.indexOf('live') > -1 || ['dvr', 'duration', 'last_video_meta_check', 'auto_splash', 'auto_caption'].indexOf(vids[x].meta[m].meta_key) > -1) {
                    video_meta.push(vids[x].meta[m]);
                  }
                }
              }

              $video_data_tab = playlist_item_add(vids[x], false, subs);
              $subtitles_tab = $video_data_tab.parents('.fv-player-tabs:first').find('.fv-player-tab-subtitles table:eq(' + $video_data_tab.data('index') + ')');

              // add chapters and transcript
              if (chapters){
                $subtitles_tab.find('#fv_wp_flowplayer_field_chapters').val(chapters.value).attr('data-id', chapters.id);
              }

              if (transcript) {
                $subtitles_tab.find('.fv_wp_flowplayer_field_transcript').val(transcript.value).attr('data-id', transcript.id);
              }

              if (video_meta.length) {
                for (var i in video_meta) {
                  // video duration hidden input
                  if (['duration', 'last_video_meta_check', 'auto_splash', 'auto_caption'].indexOf(video_meta[i].meta_key) > -1) {
                    $video_data_tab.find('#fv_wp_flowplayer_field_src').after('<input type="hidden" name="fv_wp_flowplayer_field_' + video_meta[i].meta_key + '" id="fv_wp_flowplayer_field_' + video_meta[i].meta_key + '" value="' + video_meta[i].meta_value + '" data-id="' + video_meta[i].id + '" />');
                  } else {
                    // predefined meta input with field already existing in the dialog
                    set_player_field(video_meta[i].meta_key, video_meta[i].meta_value, video_meta[i].id, video_meta[i].id_video);
                  }
                }
              }

              // fire up meta load event for this video, so plugins can process it and react
              $doc.trigger('fv_flowplayer_video_meta_load', [x, vids[x].meta, $video_data_tab , $subtitles_tab]);
            }

            // show playlist instead of the "add new video" form
            // if we have more than 1 video
            if( current_video_to_edit > -1 ) {
              playlist_item_show(current_video_to_edit);
            } else if (vids.length > 1) {
              playlist_show();
            } else {
              playlist_item_show(0);
            }
          }

          overlay_hide();

          // show the Insert button, as this is only used when adding a new player into a post
          // and using the Pick existing player button, where we need to be able to actually
          // insert the player code into the editor
          // ... also, keep the Pick existing player button showing, if we decided to choose
          //     a different player
          if (db_id) {
            $('#fv-player-shortcode-editor .button-primary, .copy_player').show();
          }
        }).error(function(xhr) {
          if (xhr.status == 404) {
            overlay_show('message', 'The requested player could not be found. Please try again.');
          } else {
            overlay_show('message', 'An unexpected error has occurred. Please try again.');
          }

          // show the Insert button, as this is only used when adding a new player into a post
          // and using the Pick existing player button, where we need to be able to actually
          // insert the player code into the editor
          // ... also, keep the Pick existing player button showing, if we decided to choose
          //     a different player
          if (db_id) {
            $('#fv-player-shortcode-editor .button-primary, .copy_player').show();
          }
        });
        
      } else {
        $doc.trigger('fv-player-editor-non-db-shortcode');
        // ordinary text shortcode in the editor
        var shortcode_parse_fix = shortcode.replace(/(popup|ad)='[^']*?'/g, '');
        shortcode_parse_fix = shortcode_parse_fix.replace(/(popup|ad)="(.*?[^\\\\/])"/g, '');
        shortcode_remains = shortcode_parse_fix.replace( /^\S+\s*?/, '' );

        var srcurl = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'src' );
        var srcurl1 = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'src1' );
        var srcurl2 = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'src2' );

        var srcrtmp = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'rtmp' );
        var srcrtmp_path = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'rtmp_path' );

        var iwidth = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'width' );
        var iheight = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'height' );

        var sad_skip = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad_skip' );
        var salign = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'align' );
        var scontrolbar = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'controlbar' );
        var sautoplay = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'autoplay' );
        var sliststyle = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'liststyle' );
        var sembed = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'embed' );
        var sloop = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'loop' );
        var slive = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'live' );
        var sshare = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'share', false, fv_wp_flowplayer_share_parse_arg );
        var sspeed = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'speed' );
        var ssplash = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'splash' );
        var ssplashend = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'splashend' );
        var ssticky = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'sticky' );

        var splaylist_advance = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'playlist_advance' );

        var ssubtitles = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'subtitles' );
        var aSubtitlesLangs = shortcode.match(/subtitles_[a-z][a-z]+/g);
        for( var i in aSubtitlesLangs ){  //  move
          fv_wp_flowplayer_shortcode_parse_arg( shortcode, aSubtitlesLangs[i], false, fv_wp_flowplayer_subtitle_parse_arg );
        }
        if(!aSubtitlesLangs){ //  move
          subtitle_language_add(false, false );
        }

        var smobile = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'mobile' );
        var sredirect = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'redirect' );

        var sCaptions = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'caption' );
        var sSplashText = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'splash_text' );
        var sPlaylist = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'playlist' );

        var sad = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad', true );
        var iadwidth = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad_width' );
        var iadheight = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad_height' );


        if( srcrtmp != null && srcrtmp[1] != null ) {
          jQuery(".fv_wp_flowplayer_field_rtmp").val( srcrtmp[1] );
          jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").show();
          jQuery(".add_rtmp_wrapper").hide();
        }
        if( srcrtmp_path != null && srcrtmp_path[1] != null ) {
          jQuery(".fv_wp_flowplayer_field_rtmp_path").val( srcrtmp_path[1] );
          jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").show();
          jQuery(".add_rtmp_wrapper").hide();
        }
        var playlist_row = jQuery('.fv-player-tab-playlist tbody tr:first')

        if( srcurl != null && srcurl[1] != null )
          document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];
        if( srcurl1 != null && srcurl1[1] != null ) {
          document.getElementById("fv_wp_flowplayer_field_src1").value = srcurl1[1];
          jQuery(".fv_wp_flowplayer_field_src1_wrapper").css( 'display', 'table-row' );
          //document.getElementById("fv_wp_flowplayer_field_src1_uploader").style.display = 'table-row';
          if( srcurl2 != null && srcurl2[1] != null ) {
            document.getElementById("fv_wp_flowplayer_field_src2").value = srcurl2[1];
            jQuery(".fv_wp_flowplayer_field_src2_wrapper").css( 'display', 'table-row' );
            //document.getElementById("fv_wp_flowplayer_field_src2_uploader").style.display = 'table-row';
            document.getElementById("add_format_wrapper").style.display = 'none';
          }
        }

        if( srcurl != null && srcurl[1] != null ) {
          get_field('src').val(srcurl[1]);
          playlist_row.find('.fvp_item_video-filename').html( srcurl[1] );
        }

        get_field('width').val(iwidth[1] || '');
        get_field('height').val(iheight[1] || '');


        if( sautoplay != null && sautoplay[1] != null ) {
          if (sautoplay[1] == 'true')
            get_field("autoplay")[0].selectedIndex = 1;
          if (sautoplay[1] == 'false')
            get_field("autoplay")[0].selectedIndex = 2;
          if (sautoplay[1] == 'muted')
            get_field("autoplay")[0].selectedIndex = 3;
        }
        if( sliststyle != null && sliststyle[1] != null ) {
          var objPlaylistStyle = get_field("playlist")[0];
          if (sliststyle[1] == 'tabs') objPlaylistStyle.selectedIndex = 1;
          if (sliststyle[1] == 'prevnext') objPlaylistStyle.selectedIndex = 2;
          if (sliststyle[1] == 'vertical') objPlaylistStyle.selectedIndex = 3;
          if (sliststyle[1] == 'horizontal') objPlaylistStyle.selectedIndex = 4;
          if (sliststyle[1] == 'text') objPlaylistStyle.selectedIndex = 5;
          if (sliststyle[1] == 'slider') objPlaylistStyle.selectedIndex = 6;
        }
        if( sembed != null && sembed[1] != null ) {
          if (sembed[1] == 'true')
            get_field("embed")[0].selectedIndex = 1;
          if (sembed[1] == 'false')
            get_field("embed")[0].selectedIndex = 2;
        }
        if( smobile != null && smobile[1] != null )
          get_field("mobile").val(smobile[1]);

        if( ssplash != null && ssplash[1] != null ) {
          get_field("splash").val(ssplash[1]);
          playlist_row.find('.fvp_item_splash').html( '<img width="120" src="'+ssplash[1]+'" />' );
        }

        var aSubtitles = false;
        if( ssubtitles != null && ssubtitles[1] != null ) {
          aSubtitles = ssubtitles[1].split(';');
          get_field("subtitles").eq(0).val( aSubtitles[0] );
          aSubtitles.shift();  //  the first item is no longer needed for playlist parsing which will follow
        }

        if( ssticky != null && ssticky[1] != null ) {
          if (ssticky[1] == 'true')
            get_field("sticky")[0].selectedIndex = 1;
          if (ssticky[1] == 'false')
            get_field("sticky")[0].selectedIndex = 2;
        }

        if( sad != null && sad[1] != null ) {
          sad = sad[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
          sad = sad.replace(/&amp;/g,'&');
          get_field("ad").val(sad);
        }

        if( iadheight != null && iadheight[1] != null )
          get_field("ad_height").val(iadheight[1]);
        if( iadwidth != null && iadwidth[1] != null )
          get_field("ad_width").val(iadwidth[1]);
        if( sad_skip != null && sad_skip[1] != null && sad_skip[1] == 'yes' )
          get_field("ad_skip")[0].checked = 1;

        if( sspeed != null && sspeed[1] != null ) {
          if (sspeed[1] == 'buttons')
            get_field("speed")[0].selectedIndex = 1;
          if (sspeed[1] == 'no')
            get_field("speed")[0].selectedIndex = 2;
        }
        /*
        if( ssplashend != null && ssplashend[1] != null && ssplashend[1] == 'show' )
          document.getElementById("fv_wp_flowplayer_field_splashend").checked = 1;
        if( sloop != null && sloop[1] != null && sloop[1] == 'true' )
          document.getElementById("fv_wp_flowplayer_field_loop").checked = 1;
        if( sredirect != null && sredirect[1] != null )
          document.getElementById("fv_wp_flowplayer_field_redirect").value = sredirect[1];
        */

        if( sSplashText != null && sSplashText[1] != null ) {
          get_field("splash_text").val(sSplashText[1]);
        }


        /*
        * Video end dropdown
        */
       get_field("popup")[0].parentNode.style.display = 'none'
        var spopup = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'popup', true );

        if( sredirect != null && sredirect[1] != null ){
          get_field("end_actions")[0].selectedIndex = 1;
          get_field("redirect").val(sredirect[1]);
          jQuery('#fv_wp_flowplayer_field_redirect').parents('tr').show();
        }else if( sloop != null && sloop[1] != null && sloop[1] == 'true' ){
          get_field("end_actions")[0].selectedIndex = 2;
        }else if( spopup != null && spopup[1] != null ) {
          get_field("end_actions")[0].selectedIndex = 3;

          spopup = spopup[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
          spopup = spopup.replace(/&amp;/g,'&');

          get_field("popup_id").parents('tr').show();

          if (spopup === null || !isNaN(parseInt(spopup)) || spopup === 'no' || spopup === 'random' || spopup === 'email_list') {
            get_field("popup_id").val(spopup)
          } else if( spopup.match(/email-[0-9]*/)){
            get_field("popup_id").parent().parent().hide();
            get_field("email_list").parent().parent().show();
            get_field("end_actions").val('email_list');
            get_field("email_list").val(spopup.match(/email-([0-9]*)/)[1]);
          }else {
            get_field("popup").val(spopup).parent().show();
          }

        }else if( ssplashend != null && ssplashend[1] != null && ssplashend[1] == 'show' ){
          get_field('end_actions')[0].selectedIndex = 4
        }

        if( splaylist_advance != null && splaylist_advance[1] != null ) {
          var field = get_field("playlist_advance")[0];
          if (splaylist_advance[1] == 'true') field.selectedIndex = 1;
          if (splaylist_advance[1] == 'false') field.selectedIndex = 2;
        }


        if( salign != null && salign[1] != null ) {
          var field = get_field("align")[0];
          if (salign[1] == 'left') field.selectedIndex = 1;
          if (salign[1] == 'right') field.selectedIndex = 2;
        }

        if( scontrolbar != null && scontrolbar[1] != null ) {
          var field = get_field("controlbar")[0];
          if (scontrolbar[1] == 'yes' || scontrolbar[1] == 'show' ) field.selectedIndex = 1;
          if (scontrolbar[1] == 'no' || scontrolbar[1] == 'hide' ) field.selectedIndex = 2;
        }

        var aCaptions = false;
        if( sCaptions ) {
          aCaptions = fv_player_editor_shortcode_arg_split(sCaptions);

          var caption = aCaptions.shift();
          get_field("caption").val( caption );
          playlist_row.find('.fvp_item_caption div').html( caption );
        }

        var aSplashText = false;
        if( sSplashText ) {
          aSplashText = fv_player_editor_shortcode_arg_split(sSplashText);

          var splash_text = aSplashText.shift();
          get_field("splash_text").val( splash_text );
        }

        if( sPlaylist ) {
          // check for all-numeric playlist items separated by commas
          // which outlines video IDs from a database
          aPlaylist = sPlaylist[1].split(';');
          for (var i in aPlaylist) {
            playlist_item_add(aPlaylist[i], aCaptions[i], aSubtitles[i], aSplashText[i]);
          }
        }

        if( jQuery('.fv-fp-subtitles .fv-fp-subtitle:first input.fv_wp_flowplayer_field_subtitles').val() == '' ) {
          jQuery('.fv-fp-subtitles .fv-fp-subtitle:first').remove();
        }

        jQuery(document).trigger('fv_flowplayer_shortcode_parse', [ shortcode_parse_fix, shortcode_remains ] );

        jQuery('.fv_wp_flowplayer_playlist_head').hover(
          function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').show(); }, function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').hide(); } );

        //???
        jQuery('#cboxContent').css('background','white');
        
        if (slive != null && slive[1] != null && slive[1] == 'true') {
          jQuery("input[name=fv_wp_flowplayer_field_live]").each(function () {
            this.checked = 1;
          });
        }


        if(sPlaylist){
          playlist_show();
        } else {
          playlist_item_show(0);
        }
        
        tabs_refresh();
        
      }
    } else {
      jQuery(document).trigger('fv_flowplayer_shortcode_new');
      shortcode_remains = '';

      // rename insert to save for new playlists if we come from list view
      if ( is_fv_player_screen_add_new(editor_button_clicked) ) {
        jQuery('.fv_player_field_insert-button').text('Save');
      }
    }
  }
  
  /*
   *  Calculate FV Player editor popup (Colorbox) size
   */
  function editor_resize() {
    setTimeout(function(){
      var height = el_editor.height();
      
      // minimal height
      if( height < 50 ) height = 50;
      
      // maximum height
      if( height > $(window).height() - 160 ) height = $(window).height() - 160;
      
      // bit of space for padding
      height = height + 50; 
      
      if( editor_resize_height_record <= height ) {
        editor_resize_height_record = height;
        el_editor.fv_player_box.resize({width:1100, height:height})
      }
    },0);
  }
  
  /*
   *  Saving the data
   */
  function editor_submit() {
    // bail out if we're already saving or we're loading meta data still
    if ( ajax_save_this_please || is_saving || is_loading_video_data ) {
      // if we're saving a new player, let's disable the Save button and wait until meta data are loaded
      if ( current_player_db_id < 0 ) {
        if (is_loading_video_data) {
          el_editor.find('.button-primary').attr('disabled', 'disabled').text('Saving...');
          var checker = setInterval(function() {
            if (is_loading_video_data <= 0) {
              clearInterval(checker);
              editor_submit(); // call this function again, so we can really save this time
            }
          }, 500);

          return;
        }
      } else {
        // if we're updating a player, just return here if we're loading meta data,
        // as that would result in duplicate save - once with and once without meta data
        if (is_loading_video_data) {
          return;
        }
      }
    }
    
    var field_rtmp = get_field("rtmp"),
      field_rtmp_path = get_field("rtmp_path")

    if(
      field_rtmp.attr('placeholder') == '' &&
      get_field("rtmp_wrapper").is(":visible") &&
      (
        ( field_rtmp.val() != '' && field_rtmp_path.val() == '' ) ||
        ( field_rtmp.val() == '' && field_rtmp_path.val() != '' )
      )
    ) {
      alert('Please enter both server and path for your RTMP video.');
      return false;
    } else if(
            get_field("src").val() == ''
            && get_field("rtmp").val() == ''
            && get_field("rtmp_path").val() == '') {
      alert('Please enter the file name of your video file.');
      return false;
    }

    var ajax_data = build_ajax_data();

    overlay_show('loading');

    // unmark DB player ID as being currently edited
    if ( current_player_db_id > -1 ) {
      current_player_db_id = -1;
    }

    // save data
    jQuery.post(ajaxurl, {
      action: 'fv_player_db_save',
      data: JSON.stringify(ajax_data),
      nonce: fv_player_editor_conf.preview_nonce
    }, function(response) {
      // player saved, reset draft status
      is_draft = false;
      is_draft_changed = false;

      var player = JSON.parse(response);
      current_player_db_id = parseInt(player.id);
      if( current_player_db_id > 0 ) {
        // we have extra parameters to keep
        if (store_shortcode_args) {
          var
            params = jQuery.map(store_shortcode_args, function (value, index) {
              return index + '="' + value + '"';
            }),
            to_append = '';

          if (params.length) {
            to_append = ' ' + params.join(' ');
          }

          insert_shortcode('[fvplayer id="' + current_player_db_id + '"' + to_append + ']');
        } else {
          // simple DB shortcode, no extra presentation parameters
          insert_shortcode('[fvplayer id="' + current_player_db_id + '"]');
        }

        jQuery(".fv-wordpress-flowplayer-button").fv_player_box.close();
      } else {
        json_export_data = jQuery('<div/>').text(JSON.stringify(ajax_data)).html();
        
        var overlay = overlay_show('error_saving');    
        overlay.find('textarea').val( $('<div/>').text(json_export_data).html() );

        jQuery('#fv_player_copy_to_clipboard').select();
      }
    }).error(function() {
      overlay_show('message', 'An unexpected error has occurred. Please try again');
    });

    return;
 
  }
  
  /*
  * Sends new shortcode to editor
  */
  function insert_shortcode( shortcode ) {
    
    // do not insert new shortcode if using button on wp-admin -> FV Player
    if( is_fv_player_screen(editor_button_clicked) ) {
      return;
    }
    
    var field = $(editor_button_clicked).parents('.fv-player-editor-wrapper').find('.fv-player-editor-field');
    var gutenberg = $(editor_button_clicked).parents('.fv-player-gutenberg').find('.fv-player-editor-field');
    
    // is there a Gutenberg field together in wrapper with the button?
    if( gutenberg.length ) {
      var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, "value").set;
      nativeInputValueSetter.call(gutenberg[0], shortcode);
      var ev2 = new Event('change', { bubbles: true});
      gutenberg[0].dispatchEvent(ev2,shortcode);
      
    // is there a plain text field together in wrapper with the button?
    } else if (field.length) {
      field.val(shortcode);
      // Prevents double event triggering in FV Player Custom Video box
      //field.trigger('fv_flowplayer_shortcode_insert', [shortcode]);

    // or is it using Foliopress WYSIWYG?
    } else if (typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length) {
      editor_content = editor_content.replace(/#fvp_placeholder#/, shortcode);
      set_post_editor_content(editor_content);
      
    // or are we editing a shortcode in post content?
    } else if (editor_content.match(fv_wp_flowplayer_re_edit)) {
      editor_content = editor_content.replace(fv_wp_flowplayer_re_edit, shortcode)
      set_post_editor_content(editor_content);
    
    // is it a new player instance
    } else {
      
      // in existing post content?
      if (editor_content != '') {
        editor_content = editor_content.replace(fv_wp_flowplayer_re_insert, shortcode)
        set_post_editor_content(editor_content);
        
      // in blank post?
      } else {
        editor_content = shortcode;
        send_to_editor(shortcode);
      }
    }

  }
  
  /*
  Determines if the button clicked is on wp-admin -> FV Player
  */
  function is_fv_player_screen(button) {
    return is_fv_player_screen_add_new(button) || is_fv_player_screen_edit(button);
  }
  
  /*
  Determines if the button clicked is Add New on wp-admin -> FV Player
  */
  function is_fv_player_screen_add_new(button) {
    return typeof( $(button).data('add_new') ) != 'undefined';
  }

  /*
  Determines if the button clicked is Edit on wp-admin -> FV Player
  */
  function is_fv_player_screen_edit(button) {
    return typeof( $(button).data('player_id') ) != 'undefined';
  }
  
  /*
  Sets lightbox class once it opens
  */
  function lightbox_open() {
    $("#fv_player_box").addClass("fv-flowplayer-shortcode-editor");
  }
  
  /*
  * Adds playlist item
  * keywords: add playlist item
  */
  function playlist_item_add( input, sCaption, sSubtitles, sSplashText ) {
    jQuery('.fv-player-tab-playlist table tbody').append(template_playlist_item);
    var ids = jQuery('.fv-player-tab-playlist [data-index]').map(function() {
      return parseInt(jQuery(this).attr('data-index'), 10);
    }).get();
    var newIndex = Math.max(Math.max.apply(Math, ids) + 1,0);
    
    var current = jQuery('.fv-player-tab-playlist table tbody tr').last();
    current.attr('data-index', newIndex);
    current.find('.fvp_item_video-filename').html( 'Video ' + (newIndex + 1) );

    jQuery('.fv-player-tab-video-files').append(template_video);
    var new_item = get_tab('last','video-files');
    new_item.hide().attr('data-index', newIndex);
    jQuery('.fv-player-tab-subtitles').append(template_subtitles_tab);
    var new_item_subtitles = get_tab('last','subtitles');
    new_item_subtitles.hide().attr('data-index', newIndex);
    
    // processing database input
    if( typeof(input) == 'object' ) {
      var objVid = input;

      new_item.attr('data-id_video', objVid.id);
      get_field('src',new_item).val(objVid.src);
      if( objVid.src1 ) {
        get_field('src1',new_item).val(objVid.src1);
        get_field('src1_wrapper',new_item).css( 'display', 'table-row' );
      }
      if( objVid.src2 ) {
        get_field('src2',new_item).val(objVid.src2);
        get_field('src2_wrapper',new_item).css( 'display', 'table-row' );
        new_item.find('#fv_wp_flowplayer_add_format_wrapper').show();
      }
      get_field('mobile',new_item).val(objVid.mobile);

      if( objVid.rtmp || objVid.rtmp_path ) {
        get_field('rtmp',new_item).val(objVid.rtmp);
        get_field('rtmp_path',new_item).val(objVid.rtmp_path);
        get_field('rtmp_wrapper',new_item).show();
        new_item.find(".add_rtmp_wrapper").hide();
      }

      get_field('caption',new_item).val(objVid.caption);
      get_field('splash',new_item).val(objVid.splash);
      get_field('splash_text',new_item).val(objVid.splash_text);

      get_field('start',new_item).val(objVid.start);
      get_field('end',new_item).val(objVid.end);

      jQuery(objVid.meta).each( function(k,v) {
        if( v.meta_key == 'synopsis' ) get_field('synopsis',new_item).val(v.meta_value).attr('data-id',v.id);
        if( v.meta_key == 'audio' ) get_field('audio',new_item).prop('checked',v.meta_value).attr('data-id',v.id);
      });

      if (typeof sSubtitles === 'object' && sSubtitles.length && sSubtitles[0].lang) {
        for (var i in sSubtitles) {
          subtitle_language_add(sSubtitles[i].file, sSubtitles[i].lang, newIndex, sSubtitles[i].id);
        }
      }

    // processing shortcode input
    } else if( input ) {
      var aInput = input.split(',');
      var count = 0;
      for( var i in aInput ) {
        if( aInput[i].match(/^rtmp:/) ) {
          get_field('rtmp_path',new_item).val(aInput[i].replace(/^rtmp:/,''));
        } else if( aInput[i].match(/\.(jpg|png|gif|jpe|jpeg)(?:\?.*?)?$/) ) {
          get_field('splash',new_item).val(aInput[i]);
        } else {
          if( count == 0 ) {
            get_field('src',new_item).val(aInput[i]);
          } else {
            get_field('src'+count,new_item).val(aInput[i]);
          }
          count++;
        }
      }
      if( sCaption ) {
        get_field('caption',new_item).val(sCaption);
      }
      if( sSubtitles ) {
        get_field('subtitles',new_item_subtitles).val(sSubtitles);
      }
      if( sSplashText ) {
        get_field('splash_text',new_item).val(sSplashText);
      }
    }

    // fire up an update event if we're adding an empty template, which means this function is called
    // outside of the player meta loading and we should inform plugins that they need to add their own
    // video tab content
    if (!input) {
      $doc.trigger('fv-player-playlist-item-add');
    }

    editor_resize();
    return new_item;
  }
  
  /*
  Show a certain playlist item, it's Video and Subtitles tab
  */
  function playlist_item_show( new_index ) {
    item_index = new_index;
    
    is_playlist_active = false;
    is_singular_active = true;
    el_editor.attr('class','is-playlist is-singular-active');
    
    jQuery('.fv-player-tabs-header .nav-tab').attr('style',false);    
    
    $doc.trigger('fv_flowplayer_shortcode_item_switch', [ new_index ] );
   
    $('a[data-tab=fv-player-tab-video-files]').click();    
    
    get_tabs('video-files').hide();
    var video_tab = get_tab(new_index,'video-files').show();
    
    get_tabs('subtitles').hide();
    get_tab(new_index,'subtitles').show();
  
    if($('.fv-player-tab-playlist [data-index]').length > 1){
      $('.fv-player-playlist-item-title').html('Playlist item no. ' + ++new_index);
      $('.playlist_edit').html($('.playlist_edit').data('edit'));
      
    }else{
      $('.playlist_edit').html($('.playlist_edit').data('create'));
      
      el_editor.attr('class','is-singular is-singular-active');
    }
    
    // Show or hide RTMP fields if they are filled in
    var rtmp_not_provided = get_field('rtmp_path',video_tab).val().length === 0 && get_field('rtmp',video_tab).val().length === 0;
    get_field('rtmp_wrapper',video_tab).toggle( !rtmp_not_provided );
    $('.add_rtmp_wrapper',video_tab).toggle( rtmp_not_provided );
    
    // As Flowplayer only lets us set RTMP server for the first video in playlist, prefill it for this new item as well
    if(new_index > 1){
      get_field('rtmp',video_tab).val( get_field('rtmp',$('.fv-player-tab-video-files table').eq(0) ).val()).attr('readonly',true);
    }
    
    $('.fv_wp_flowplayer_field_subtitles_lang, .subtitle_language_add_link').attr('style',false);
  
    tabs_refresh();
  
    preview_submit();  
  }
  
  /*
   *  Recalculate the data-index values for playlist items
   */
  function playlist_index() {
    $doc.trigger('fv-player-editor-initial-indexing');

    $('.fv-player-tab-playlist table tbody tr').each(function(){
      $(this).attr('data-index', $(this).index() );
    });
    
    $('.fv-player-tab-video-files table').each(function(){
      $(this).attr('data-index', $(this).index() );
    });
    
    $('.fv-player-tab.fv-player-tab-subtitles table').each(function(){
      $(this).attr('data-index', $(this).index() );
    });
  }
  
  /*
  * Displays playlist editor
  * keywords: show playlist 
  */
  function playlist_show() {
    item_index = -1;
    
    is_playlist_active = true;
    is_singular_active = false;
    el_editor.attr('class','is-playlist-active');
    
    jQuery('.fv-player-tabs-header .nav-tab').attr('style',false);
    jQuery('a[data-tab=fv-player-tab-playlist]').click();
    
    preview_single = -1;
    
    playlist_index();
    
    //fills playlist edistor table from individual video tables
    var video_files = jQuery('.fv-player-tab-video-files table');
    video_files.each( function() {
      var current = jQuery(this);

      var currentUrl = get_field("src",current).val();
      if(!currentUrl.length){
        currentUrl = 'Video ' + (jQuery(this).index() + 1);
      }
      
      var playlist_row = jQuery('.fv-player-tab-playlist table tbody tr').eq( current.data('index') );
      
      var video_preview = get_field("splash",current).val();
      playlist_row.find('.fvp_item_video-thumbnail').html( video_preview.length ? '<img src="' + video_preview + '" />':'');
      
      var video_name = decodeURIComponent(currentUrl).split("/").pop();
      video_name = video_name.replace(/\+/g,' ');
      video_name = video_name.replace(/watch\?v=/,'YouTube: ');
      
      playlist_row.find('.fvp_item_video-filename').html( video_name );

      var playlist_row_div = playlist_row.find('.fvp_item_caption div');
      // do not put in caption if it's loading
      if (!playlist_row_div.hasClass('fv-player-shortcode-editor-small-spinner')) {
        playlist_row_div.html( get_field("caption",current).val() );
      }
    });

    playlist_index();

    if(!jQuery('.fvp_item_video-thumbnail>img').length){
      jQuery('#fv-player-list-list-view').click();
      jQuery('#fv-player-list-thumb-view').addClass('disabled');
      jQuery('#fv-player-list-thumb-view').attr('title',jQuery('#fv-player-list-thumb-view').data('title'));
    }else{
      jQuery('#fv-player-list-thumb-view').click();
      jQuery('#fv-player-list-thumb-view').removeClass('disabled');
      jQuery('#fv-player-list-thumb-view').removeAttr('title');
    }
    
    jQuery('.fv-player-tab-playlist').show();
    editor_resize();
    tabs_refresh();
    preview_submit();

    return false;
  }

  function preview_dimensions() {
    var width = parseInt( get_field('width').val() ) || 460;
    var height = parseInt( get_field('height').val() ) || 300;
    if (el_preview.length && el_preview.width() < width) {
      height = Math.round(height * (el_preview.width() / width));
      width = el_preview.width();
    }

    return {
      width: width,
      height: height
    };
  }
  
  /*
   *  Load the preview player
   */
  function preview_show(data) {
    el_preview_refresh.hide();
    
    $found_src = false;
    $.each( data.videos, function(k,v) {
      if( v.fv_wp_flowplayer_field_src ) {
        $found_src = true;
        return false;
      }
    });
    
    if( !$found_src ) {
      el_preview.attr('class', 'preview-no');
      editor_resize();
      return;
    }
    
    if(preview_not_supported){
      jQuery('#fv-player-shortcode-editor-preview-new-tab > a').html('Open preview in a new window');
      if( jQuery('#fv-player-shortcode-editor-preview div.incompatibility').length == 0 ) jQuery('#fv-player-shortcode-editor-preview-new-tab').after('<div class="notice notice-warning incompatibility"><p>For live preview of the video player please use the latest Firefox, Chromium or Opera.</p></div>');
      return;
    }
    
    //console.trace();
    console.log('preview_show',data);
    is_loading_preview = true;
  
    el_preview.attr('class','preview-loading');
    var url = fv_player_editor_conf.home_url + '?fv_player_embed='+fv_player_editor_conf.preview_nonce+'&fv_player_preview=POST';
   
    el_preview_target.html('');

    fv_player_shortcode_editor_ajax = jQuery.post(
      url,
      {
        'fv_player_preview_json' : JSON.stringify(data)
      }, function (response) {
        el_preview_target.html( $('#wrapper', response) );
        $doc.trigger('fvp-preview-complete');
      }
    );
    
  }
  
  /*
   *  Show button to refresh the preview player
   */
  function preview_show_button() {
    // if it's already loading preview, wait until it's finished and then do it again
    if( is_loading_preview ) {
      $doc.one('fvp-preview-complete', preview_show_button);
      return;
    };
    
    el_preview_refresh.show();
  }
  
  /*
   *  Ask for the preview
   */
  function preview_submit() {
    // if it's already loading preview, wait until it's finished and then do it again
    if( is_loading_preview ) {
      $doc.one('fvp-preview-complete', preview_submit);
      return;
    };
    
    var
      ajax_data = build_ajax_data(),
      previewDimensions = preview_dimensions(),
      previewWidth = previewDimensions.width,
      previewHeight = previewDimensions.height;
      
    ajax_data['fv_wp_flowplayer_field_width'] = previewWidth;
    ajax_data['fv_wp_flowplayer_field_height'] = previewHeight;
    
    preview_show(ajax_data);
  }
  
  function set_post_editor_content( html ) {
    if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length ){
      jQuery('#content:not([aria-hidden=true])').val(html); 
      
    }else if( instance_tinymce == undefined || typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor.isHidden() ) {
      instance_fp_wysiwyg.SetHTML( html );      
    }
    else {		
      instance_tinymce.setContent( html );
    }
  }
  
  /*
   *  Hide any overlays
   */
  function overlay_hide() {
    $('.fv-player-editor-overlay').hide();
    return false;
  }
  
  /*
   *  Show a certain kind of overlay
   */
  function overlay_show( type, message ) {
    overlay_hide();
    var overlayDiv = $('#fv-player-editor-'+type+'-overlay');
    console.log(overlayDiv);
    overlayDiv.show();
    
    if( typeof(message) != 'undefined' ) {
      overlayDiv.find('p').html( message );
    }

    editor_resize();
    return overlayDiv;
  }
  
  /*
  * Adds another language to subtitle menu
  */
  function subtitle_language_add( sInput, sLang, iTabIndex, sId ) {
    if(!iTabIndex){
      var current = jQuery('.fv-player-tab-subtitles table:visible');
      iTabIndex = current.length && current.data('index') ? current.data('index') : 0;
    }
    var oTab = jQuery('.fv-fp-subtitles').eq(iTabIndex);
    oTab.append( template_subtitles ); 
  
    var subElement = jQuery('.fv-fp-subtitle:last' , oTab);
  
    if (typeof(sId) !== 'undefined') {
      subElement.attr('data-id_subtitles', sId);
    }
    
    if( sInput ) {
      get_field('subtitles',subElement).val(sInput);
    }
    
    if ( sLang ) {
      if( sLang == 'iw' ) sLang = 'he';
      if( sLang == 'in' ) sLang = 'id';
      if( sLang == 'jw' ) sLang = 'jv';
      if( sLang == 'mo' ) sLang = 'ro';
      if( sLang == 'sh' ) sLang = 'sr';
      
      get_field('subtitles_lang',subElement).val(sLang).change();
    }

    editor_resize();
    return false;
  }
  
  function tabs_refresh(){
    var visibleTabs = 0;
    el_editor.find('a[data-tab]').removeClass('fv_player_interface_hide');
    el_editor.find('.fv-player-tabs > .fv-player-tab').each(function(){   
      var bHideTab = true
      $(this).find('tr:not(.fv_player_actions_end-toggle):not(.submit-button-wrapper)').each(function(){
        if( $(this).css('display') === 'table-row' ){
          bHideTab = false;
          return false;
        }
      });
      var tab;
      var data = jQuery(this).attr('class').match(/fv-player-tab-[^ ]*/);
      if(data[0]){
        tab = el_editor.find('a[data-tab=' + data[0] + ']');
      }
      
      if(bHideTab){
        tab.addClass('fv_player_interface_hide')
      } else {
        tab.removeClass('fv_player_interface_hide');
        if(tab.css('display')!=='none')
          visibleTabs++
        
      }
    });
    
    if(visibleTabs<=1){
      el_editor.find('.nav-tab').addClass('fv_player_interface_hide');
    }
    
    var end_actions_label = $('label[for=fv_wp_flowplayer_field_end_actions]');
    if( el_editor.hasClass('is-playlist-active')){		
      end_actions_label.html( end_actions_label.data('playlist-label') )		
    } else {		
      end_actions_label.html( end_actions_label.data('single-label') )		
    }
    
  }
  
  /*
  Click handlers
  */
 
  /*
  Click on Add another format
  */
  $doc.on('click', '#add_format_wrapper a', function() {
    if ( get_field("src").val() != '' ) {
      if ( get_field("src1_wrapper").is(":visible") ) {      
        if ( get_field("src1").val() != '' ) {
          get_field("src2_wrapper").show();
          get_field("src2_uploader").show();
          $("#add_format_wrapper").hide();
        }
        else {
          alert('Please enter the file name of your second video file.');
        }
      }
      else {
        get_field("src1_wrapper").show();
        get_field("src1_uploader").show();
      }
      editor_resize();
    }
    else {
      alert('Please enter the file name of your video file.');
    }
  });
 
  /*
  Click on Add RTMP
  */
  $doc.on('click', '.add_rtmp_wrapper a', function() {
    var item = $(this).parents('.fv-player-playlist-item');
    get_field("rtmp_wrapper", item).show();
    item.find(".add_rtmp_wrapper").hide();
    editor_resize();
    return false;
  });
  
  /*
  Click on Add Another Language (of Subtitles)
  */
  $doc.on('click', '.fv_flowplayer_language_add_link', function() {
    subtitle_language_add(false,true);
    return false;
  });
  
  /*
  Click on X to remove a language from Subtitles
  */
  $doc.on('click', '.fv-fp-subtitle-remove', function() {
    
    var $parent = jQuery(this).parents('.fv-fp-subtitle'),
        id = $parent.attr('data-id_subtitles')
  
      if (id) {
        fv_wp_delete_video_meta_record(id);
      }
  
    // if it's not the last subtitle, remove it completely
    if(jQuery(this).parents('.fv-fp-subtitles').find('.fv-fp-subtitle').length > 1){
      $parent.remove();
      
    // otherwise just empty the inputs to let user add new subtitles
    } else {
      $parent.find('[name]').val('');
      $parent.removeAttr('data-id_subtitles');
    }
    editor_resize();
  
    return false;
  });
  
  /*
  Click on Loading Overlay Close button
  */
  $doc.on('click', '#fv-player-editor-overlay-close', function() {
    $.fn.fv_player_box.close();
    // hide the overlay asynchronously to allow the actual modal close animation to finish,
    // so it doesn't blink from error message to an empty editor and only then starts to fade
    setTimeout(overlay_hide, 1000);
  });
  
  /*
  Click on Import player
  */
  $doc.on('click', '#fv-player-editor-import-overlay-import', function() {
    var button = this,
      data = jQuery('#fv_player_import_data').val();
  
    if (!data) {
      fv_player_editor.overlay_notice( button, 'No data to import!', 'warning', 5000 );  
      return false;
    }
    
    try {
      JSON.parse(data);
    } catch(e) {
      fv_player_editor.overlay_notice( button, 'Bad JSON format!', 'error', 5000 );
      return false;
    }
    
    fv_player_editor.overlay_notice_close_all();
    
    overlay_show('loading');
  
    jQuery.post(ajaxurl, {
      action: 'fv_player_db_import',
      nonce: fv_player_editor_conf.db_import_nonce,
      data: data,
      cookie: encodeURIComponent(document.cookie),
    }, function(response) {
      if (response != '0' && !isNaN(parseFloat(response)) && isFinite(response)) {
        var playerID = response;
        
        // add the inserted player's row
        jQuery.get(
          fv_player_editor_conf.admin_url + '&id=' + playerID,
          function (response) {
            jQuery('#the-list tr:first').before(jQuery(response).find('#the-list tr:first'));
            jQuery('.fv-wordpress-flowplayer-button').fv_player_box.close();
        }).error(function() {
          jQuery('.fv-wordpress-flowplayer-button').fv_player_box.close();
        });
        
      } else {
        fv_player_editor.overlay_notice( button, response, 'error' );

      }
    }).error(function() {
      fv_player_editor.overlay_notice( button, 'Unknown error!', 'error' );

    });
    
    return false;
  });
  
  /*
  * Removes playlist item
  * Also stores the RTMP server of the first item as that's the only place where
  * it's stored, so in case we are removing the first playlist item, we save it
  * that way and then set it again for the first item. Uff.
  * 
  * keywords: remove palylist item
  */
  $doc.on('click', '.fv_wp_flowplayer_playlist_remove', function() {
    // TODO: Some method to get first playlist item data
    store_rtmp_server = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val();
    $(this).parents('table').remove();
    jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val( store_rtmp_server );
    return false;
  });
  
  /*
  Extra fields to reveal when using a stream
  */
  $doc.on('keyup', '[name=fv_wp_flowplayer_field_src], [name=fv_wp_flowplayer_field_rtmp_path]', show_stream_fields );
  $doc.on('fv_flowplayer_shortcode_item_switch fv_flowplayer_shortcode_new', show_stream_fields );

  function show_stream_fields(e,index) {
    // on keyup
    var src = jQuery(this).val(),
      item = jQuery(this).parents('table');
    
    // on fv_flowplayer_shortcode_item_switch
    if( typeof(index) != "undefined" ) {
      item = jQuery('.fv-player-playlist-item[data-index='+index+']');
      src = item.find('[name=fv_wp_flowplayer_field_src]').val();
    }
    
    // on fv_flowplayer_shortcode_new
    if( item.length == 0 ) item = jQuery('.fv-player-playlist-item[data-index=0]');
    
    var show_stream_checkboxes = item.find('[name=fv_wp_flowplayer_field_rtmp_path]').val() || src.match(/m3u8/) || src.match(/rtmp:/) || src.match(/\.mpd/) || src.match(/vimeo\.com\//);
      
    item.find('[name=fv_wp_flowplayer_field_live]').closest('tr').toggle(!!show_stream_checkboxes);
    item.find('[name=fv_wp_flowplayer_field_audio]').closest('tr').toggle(!!show_stream_checkboxes);
    item.find('[name=fv_wp_flowplayer_field_dvr]').closest('tr').toggle(!!show_stream_checkboxes);
  }
  
  /*
  Mark each manually updated title or splash field as such
  */
  $doc.on('keydown', '#fv_wp_flowplayer_field_splash, #fv_wp_flowplayer_field_caption', function() {
    // remove spinner from playlist table row, if present
    var $element = jQuery(this);

    // if this element already has data set, don't do any of the selections below
    if (typeof($element.data('fv_player_user_updated')) != 'undefined') {
      return;
    }

    var
      $parent_row = $element.closest('tr'),
      $parent_table = $element.closest('table'),
      $playlist_row = jQuery('.fv-player-tab-playlist table tr[data-index="' + $parent_table.data('index') + '"] td.fvp_item_caption'),
      $playlist_row_spinner_div = $playlist_row.find('div.fv-player-shortcode-editor-small-spinner');

    if (this.id == 'fv_wp_flowplayer_field_caption' && $playlist_row_spinner_div.length > 0) {
      $playlist_row_spinner_div.removeClass('fv-player-shortcode-editor-small-spinner');
    }

    if( this.id == 'fv_wp_flowplayer_field_splash' ) {
      var $input = $parent_table.find('#fv_wp_flowplayer_field_auto_splash');
      var $meta_key = 'auto_splash';
    } else {
      var $input = $parent_table.find('#fv_wp_flowplayer_field_auto_caption');
      var $meta_key = 'auto_caption';
    }
    
    if( typeof($element.data('fv_player_user_updated')) == 'undefined' && $input.length > 0 ) {
      $input.val('');

      fv_flowplayer_insertUpdateOrDeleteVideoMeta({
        element: $input,
        meta_section: 'video',
        meta_key: $meta_key,
        handle_delete: true
      });
    }

    // remove spinner
    $parent_row.find('.fv-player-shortcode-editor-small-spinner').remove();

    console.log(this.id+' has been updated manually!');
    $element.data('fv_player_user_updated', 1);
  });
  
  // Public stuff
  return {
    get_current_player_db_id() {
      return current_player_db_id;
    },
    
    get_edit_lock_removal() {
      return edit_lock_removal;
    },
    
    get_shortcode_remains: function() {
      return shortcode_remains;
    },
    
    set_current_video_to_edit( index ) {
      current_video_to_edit = index;
    },
    
    set_edit_lock_removal( val ) {
      edit_lock_removal = val;
    },
    
    set_shortcode_remains: function(value) {
      shortcode_remains = value;
    },
    
    /*
     * Show a notice in the overlay above the editor
     *
     * @param {Object}  button      The button in the overlay that was clicked
     *                              Used to find the overlay and the notice in it
     * @param {string}  html        Content of the notice
     * @param {string}  type        success|error
     * @param {int}     close_after Optional number of miliseconds to close the
     *                              notice after
     * 
     */
    overlay_notice: function(button, html, type, close_after ) {
      var overlay = jQuery(button).closest('.fv-player-editor-overlay'),
        notice = overlay.find('.fv-player-editor-overlay-notice');
        
      notice
        .html(html)
        .removeClass('notice-error')
        .removeClass('notice-success')
        .addClass('notice-'+type)
        .css('visibility', 'visible');
        
      if( close_after ) {
        setTimeout(function() {
          notice.css('visibility', 'hidden');
        }, close_after);
      }
    },
    
    overlay_notice_close_all: function() {
      $('.fv-player-editor-overlay-notice').css('visibility', 'hidden');
    },

    editor_resize: editor_resize,
  };
  
})(jQuery);


function fv_wp_flowplayer_map_names_to_editor_fields(name) {
  var fieldMap = {
    'liststyle': 'playlist',
    'preroll': 'video_ads',
    'postroll': 'video_ads_post'
  };

  return 'fv_wp_flowplayer_field_' + (fieldMap[name] ? fieldMap[name] : name);
}

function fv_wp_flowplayer_map_db_values_to_field_values(name, value) {
  switch (name) {
    case 'playlist_advance':
      return ((value == 'true' || value == 'on') ? 'on' : (value == 'default' || value == '') ? 'default' : 'off');
      break;

    default: return value;
  }
}

function fv_wp_delete_player_meta_record(id) {
  var $element = jQuery('#fv-player-deleted_player_meta');

  if ($element.val()) {
    $element.val($element.val() + ',' + id);
  } else  {
    $element.val(id);
  }
}

function fv_wp_delete_video_meta_record(id) {
  var $element = jQuery('#fv-player-deleted_video_meta');

  if ($element.val()) {
    $element.val($element.val() + ',' + id);
  } else  {
    $element.val(id);
  }
}

function fv_wp_flowplayer_dialog_resize() {
  console.log('WARNING! USE OF DEPRECATED FUNCTION fv_wp_flowplayer_dialog_resize() FOUND!');
  console.log('Please update this to call the function as fv_player_editor.fv_wp_flowplayer_dialog_resize() instead!');

  fv_player_editor.editor_resize();
}

function fv_wp_flowplayer_get_correct_dropdown_value(optionsHaveNoValue, $valueLessOptions, dropdown_element) {
  // at least one option is value-less
  if ($valueLessOptions.length) {
    if (optionsHaveNoValue) {
      // all options are value-less - the first one is always default and should be sent as ''
      return (dropdown_element.selectedIndex === 0 ? '' : dropdown_element.value);
    } else {
      // some options are value-less
      if ($valueLessOptions.length > 1) {
        // multiple value-less options, while some other options do have a value - this should never be
        console.log('ERROR - Unhandled exception occurred while trying to get player values: more than 1 value-less options found');
        return false;
      } else {
        // single option is value-less (
        return (dropdown_element.selectedIndex === 0 ? '' : dropdown_element.value);
      }
    }
  } else {
    // normal dropdown - all options have a value, return this.value (option's own value)
    return dropdown_element.value;
  }
}

function fv_wp_flowplayer_check_for_player_meta_field(fieldName) {
  return [].indexOf(fieldName) > -1;
}

function fv_wp_flowplayer_check_for_video_meta_field(fieldName) {
  return [
    'fv_wp_flowplayer_field_duration',
    'fv_wp_flowplayer_field_last_video_meta_check',
    'fv_wp_flowplayer_field_live',
    'fv_wp_flowplayer_field_dvr',
    'fv_wp_flowplayer_field_auto_splash',
    'fv_wp_flowplayer_field_auto_caption',
    'fv_wp_flowplayer_field_synopsis',
    'fv_wp_flowplayer_field_audio'
  ].indexOf(fieldName) > -1;
}


jQuery(document).on('click', '#fv-player-editor-export-overlay-copy', function() {
  var button = this;
  fv_player_clipboard(jQuery('[name=fv_player_copy_to_clipboard]').val(), function() {
    fv_player_editor.overlay_notice( button, 'Text Copied To Clipboard!', 'success', 3000 );
  }, function() {
    fv_player_editor.overlay_notice( button, '<strong>Error copying text into clipboard!</strong><br />Please copy the content of the above text area manually by using CTRL+C (or CMD+C on MAC).', 'error' );
  });
  
  return false;
});


// TODO: This is used in editor-screenshots.js and FV Player Pay Per View!
function fv_wp_flowplayer_submit( preview ) {

}


function fv_player_open_preview_window(url, width, height){
  height = Math.min(window.screen.availHeight * 0.80, height + 25);
  width = Math.min(window.screen.availWidth * 0.66, width + 100);
  
  if(fv_player_preview_window == null || fv_player_preview_window.self == null || fv_player_preview_window.closed ){
    fv_player_preview_window = window.open(url,'window','toolbar=no, menubar=no, resizable=yes width=' + width + ' height=' + height);
  }else{
    fv_player_preview_window.location.assign(url);
    fv_player_preview_window.focus();
  }
  
}




/*
Also used by FV Player Pro and FV Player Pay Per View
*/
function fv_wp_flowplayer_shortcode_parse_arg( sShortcode, sArg, bHTML, sCallback ) {

  var rDoubleQ = new RegExp(sArg+"=\"","g");
  var rSingleQ = new RegExp(sArg+"='","g");
  var rNoQ = new RegExp(sArg+"=[^\"']","g");
  
  var rMatch = false;
  if( sShortcode.match(rDoubleQ) ) {
    //rMatch = new RegExp(sArg+'="(.*?[^\\\\/])"',"g");
    rMatch = new RegExp('[ "\']' + sArg + '="(.*?[^\\\\])"', "g");
  } else if (sShortcode.match(rSingleQ)) {
    rMatch = new RegExp('[ "\']' + sArg + "='([^']*?)'", "g");
  } else if (sShortcode.match(rNoQ)) {
    rMatch = new RegExp('[ "\']' + sArg + "=([^\\]\\s,]+)", "g");
  }

  if( !rMatch ){
    return false;
  }
  
  var aOutput = rMatch.exec(sShortcode);
  fv_player_editor.set_shortcode_remains( fv_player_editor.get_shortcode_remains().replace( rMatch, '' ) );
 
  if( bHTML ) {
    aOutput[1] = aOutput[1].replace(/\\"/g, '"').replace(/\\(\[|])/g, '$1');
  }
  
  if( aOutput && sCallback ) {
    sCallback(aOutput);
  } else {
   return aOutput;
  }
}


function fv_wp_flowplayer_subtitle_parse_arg( args ) {
  var input = ('fv_wp_flowplayer_subtitle_parse_arg',args);
  var aLang = input[0].match(/subtitles_([a-z][a-z])/);
  subtitle_language_add( input[1], aLang[1] );
}


function fv_wp_flowplayer_share_parse_arg( args ) {
  var field = get_field("share")[0];
  if (args[1] == 'yes' ) {
    field.selectedIndex = 1;
  } else if (args[1] == 'no' ) {
    field.selectedIndex = 2;
  } else {
    field.selectedIndex = 3;
    args = args[1].split(';');
    if( typeof(args[0]) == "string" ) get_field('share_url').val(args[0]);
    if( typeof(args[1]) == "string" ) get_field('share_title').val(args[1]);
    get_field("share_custom").show();
  }
}


function fv_player_editor_shortcode_arg_split(sInput) {
  sInput[1] = sInput[1].replace(/\\;/gi, '<!--FV Flowplayer Caption Separator-->').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
  aInput = sInput[1].split(';');
  for( var i in aInput ){
    aInput[i] = aInput[i].replace(/\\"/gi, '"');
    aInput[i] = aInput[i].replace(/\\<!--FV Flowplayer Caption Separator-->/gi, ';');
    aInput[i] = aInput[i].replace(/<!--FV Flowplayer Caption Separator-->/gi, ';');
  }
  return aInput;
};

jQuery(document).on('fv_flowplayer_shortcode_insert', function(e) {
  jQuery(e.target).siblings('.button.fv-wordpress-flowplayer-button').val('Edit');
});

/**
 * Automatically handles new, updated or removed player meta data
 * via JS.
 *
 * @param options Object with the following elements:
 *                element -> the actual element on page (input, select...) to check and get meta value from
 *                data -> existing player data, including player meta data
 *                meta_section -> section for the meta data, for example "player" for common metas, "ppv" for pay per view plugin etc.
 *                meta_key -> rhe actual key to check for and potentially add/update/remove
 *                handle_delete -> if true, value-less ('') elements will be considered indication that the meta key should be deleted
 *                delete_callback -> if set, this function is called when a meta key is deleted
 *                edit_callback -> if set, this function is called when a meta key is updated
 *                insert_callback -> if set, this function is called when a meta key is added
 */
function fv_flowplayer_insertUpdateOrDeletePlayerMeta(options) {
  var
    $element = jQuery(options.element),
    $deleted_meta_element = jQuery('#fv-player-deleted_player_meta'),
    optionsHaveNoValue = false, // will become true for dropdown options without values
    $valueLessOptions = null,
    isDropdown = $element.get(0).nodeName == 'SELECT',
    value = ($element.get(0).type.toLowerCase() == 'checkbox' ? $element.get(0).checked ? 'true' : '' : $element.val());

  // don't do anything if we've not found the actual element
  if (!$element.length) {
    return;
  }

  // check for a select without any option values, in which case we'll use their text
  if (isDropdown) {
    $valueLessOptions = $element.find('option:not([value])');
    if ($valueLessOptions.length == $element.get(0).length) {
      optionsHaveNoValue = true;
    }

    var opt_value = fv_wp_flowplayer_get_correct_dropdown_value(optionsHaveNoValue, $valueLessOptions, $element.get(0));
    // if there were any problems, just set value to ''
    if (opt_value === false) {
      value = '';
    } else {
      value = opt_value.toLowerCase();
    }
  }

  // check whether to update or delete this meta
  if ($element.data('id')) {
    // only delete this meta if delete was not prevented via options
    // and if there was no value specified, otherwise update
    if ((!options.handle_delete || options.handle_delete !== false) && !value) {
      if ($deleted_meta_element.val()) {
        $deleted_meta_element.val($deleted_meta_element.val() + ',' + $element.data('id'));
      } else {
        $deleted_meta_element.val($element.data('id'));
      }

      $element
        .removeData('id')
        .removeAttr('data-id');

      // execute delete callback, if present
      if (options.delete_callback && typeof(options.delete_callback) == 'function') {
        options.delete_callback();
      }
    } else {
      if (typeof(options.data) != 'undefined' && typeof(options.data['player_meta'][options.meta_section]) == 'undefined') {
        options.data['player_meta'][options.meta_section] = {};
      }

      // update if we have an ID
      if (typeof(options.data) != 'undefined') {
        options.data['player_meta'][options.meta_section][options.meta_key] = {
          'id': $element.data('id'),
          'value': value
        }
      }

      // execute update callback, if present
      if (options.edit_callback && typeof(options.edit_callback) == 'function') {
        options.edit_callback();
      }
    }
  } else if (value) {
    if (typeof(options.data) != 'undefined' && typeof(options.data['player_meta'][options.meta_section]) == 'undefined') {
      options.data['player_meta'][options.meta_section] = {};
    }

    // insert new data if no meta ID
    if (typeof(options.data) != 'undefined') {
      options.data['player_meta'][options.meta_section][options.meta_key] = {
        'value': value
      }
    }

    // execute insert callback, if present
    if (options.insert_callback && typeof(options.insert_callback) == 'function') {
      options.insert_callback();
    }
  }
};

/**
 * Automatically handles new, updated or removed video meta data
 * via JS.
 *
 * @param options Object with the following elements:
 *                element -> the actual element on page (input, select...) to check and get meta value from
 *                data -> existing player data, including video meta data
 *                meta_section -> section for the meta data, for example "player" for common metas, "ppv" for pay per view plugin etc.
 *                meta_key -> rhe actual key to check for and potentially add/update/remove
 *                handle_delete -> if true, value-less ('') elements will be considered indication that the meta key should be deleted
 *                delete_callback -> if set, this function is called when a meta key is deleted
 *                edit_callback -> if set, this function is called when a meta key is updated
 *                insert_callback -> if set, this function is called when a meta key is added
 */
function fv_flowplayer_insertUpdateOrDeleteVideoMeta(options) {
  var
    $element = jQuery(options.element),
    $deleted_meta_element = jQuery('#fv-player-deleted_video_meta'),
    optionsHaveNoValue = false, // will become true for dropdown options without values
    $valueLessOptions = null,
    isDropdown = $element.get(0).nodeName == 'SELECT',
    value = ($element.get(0).type.toLowerCase() == 'checkbox' ? $element.get(0).checked ? 'true' : '' : $element.val());
  // don't do anything if we've not found the actual element
  if (!$element.length) {
    return;
  }

  // check for a select without any option values, in which case we'll use their text
  if (isDropdown) {
    $valueLessOptions = $element.find('option:not([value])');
    if ($valueLessOptions.length == $element.get(0).length) {
      optionsHaveNoValue = true;
    }

    var opt_value = fv_wp_flowplayer_get_correct_dropdown_value(optionsHaveNoValue, $valueLessOptions, $element.get(0));
    // if there were any problems, just set value to ''
    if (opt_value === false) {
      value = '';
    } else {
      value = opt_value.toLowerCase();
    }
  }

  // check whether to update or delete this meta
  if ($element.data('id')) {
    // only delete this meta if delete was not prevented via options
    // and if there was no value specified, otherwise update
    if ((!options.handle_delete || options.handle_delete !== false) && !$element.val()) {
      if ($deleted_meta_element.val()) {
        $deleted_meta_element.val($deleted_meta_element.val() + ',' + $element.data('id'));
      } else {
        $deleted_meta_element.val($element.data('id'));
      }

      $element
        .removeData('id')
        .removeAttr('data-id');

      // execute delete callback, if present
      if (options.delete_callback && typeof(options.delete_callback) == 'function') {
        options.delete_callback();
      }
    } else {
      if (typeof(options.data) != 'undefined' && typeof(options.data['video_meta'][options.meta_section]) == 'undefined') {
        options.data['video_meta'][options.meta_section] = {};
      }

      if (typeof(options.data) != 'undefined') {
        // update if we have an ID
        options.data['video_meta'][options.meta_section][options.meta_index][options.meta_key] = {
          'id': $element.data('id'),
          'value': value
        }

        // execute update callback, if present
        if (options.edit_callback && typeof(options.edit_callback) == 'function') {
          options.edit_callback();
        }
      }
    }
  } else if (value) {
    if (typeof(options.data) != 'undefined' && typeof(options.data['video_meta'][options.meta_section]) == 'undefined') {
      options.data['video_meta'][options.meta_section] = {};
    }

    // insert new data if no meta ID
    if (typeof(options.data) != 'undefined') {
      options.data['video_meta'][options.meta_section][options.meta_index][options.meta_key] = {
        'value': value
      }

      // execute insert callback, if present
      if (options.insert_callback && typeof(options.insert_callback) == 'function') {
        options.insert_callback();
      }
    }
  }
};




/*
For wp-admin -> FV Player screen, should not be here
*/

if( typeof(fv_player_editor_conf) != "undefined" ) {
  // extending DB player edit lock's timer
  jQuery( document ).on( 'heartbeat-send', function ( event, data ) {
    if( fv_player_editor.get_current_player_db_id() > -1 ) {
      data.fv_flowplayer_edit_lock_id = fv_player_editor.get_current_player_db_id();
    }

    var
      removals = fv_player_editor.get_edit_lock_removal(),
      removalsEmpty = true;

    for (var i in removals) {
      removalsEmpty = false;
      break;
    }

    if( !removalsEmpty ) {
      data.fv_flowplayer_edit_lock_removal = fv_player_editor.get_edit_lock_removal();
    } else {
      delete(data.fv_flowplayer_edit_lock_removal);
    }
  });
  
  // remove edit locks in the config if it was removed on the server
  jQuery( document ).on( 'heartbeat-tick', function ( event, data ) {
    if ( data.fv_flowplayer_edit_locks_removed ) {
      var
        edit_lock_removal = fv_player_editor.get_edit_lock_removal(),
        new_edit_lock_removal = {};

      // remove only edit locks that were removed server-side
      for (var i in edit_lock_removal) {
        if (!data.fv_flowplayer_edit_locks_removed[i]) {
          new_edit_lock_removal[i] = edit_lock_removal[i];
        }
      }

      fv_player_editor.set_edit_lock_removal(new_edit_lock_removal);
    }
  });
}

jQuery(document).on('click','.fv_player_splash_list_preview', function() {
  fv_player_editor.set_current_video_to_edit( jQuery(this).parents('.thumbs').find('.fv_player_splash_list_preview').index(this) );
  jQuery(this).parents('tr').find('.fv-player-edit').click();
});
jQuery(document).on('click','.column-shortcode input', function() {
  jQuery(this).select();
});