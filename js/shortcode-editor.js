/* eslint-disable no-global-assign */
/*global fvwpflowplayer_helper_tag, fv_wp_flowplayer_re_edit, fv_wp_flowplayer_re_insert, fv_flowplayer_set_post_thumbnail_id, fv_flowplayer_set_post_thumbnail_nonce, */
/*global FCKeditorAPI, setPostThumbnailL10n, send_to_editor, tinymce*/

jQuery(function() {
  // The actual editor
  window.fv_player_editor = (function($) {
    var
      $doc = $(document),
      $el_editor = $('#fv-player-shortcode-editor'),
      $el_preview = $('#fv-player-shortcode-editor-preview'),
      el_spinner = $('#fv-player-shortcode-editor-preview-spinner'),
      el_preview_target = $('#fv-player-shortcode-editor-preview-target'),
      $el_notices = $('#fv-player-editor-notices'),

    // data to save in Ajax
    ajax_save_this_please = false,

    // last saved data to detect changes for auto-saving
    ajax_save_previous = false,

    current_player_db_id = 0,
    current_player_object = false,
    current_video_db_id = 0,
    current_video_to_edit = -1,

    current_editor_tab = false,

    // Used for Video Custom Fields
    current_post_id = 0,
    current_post_meta_key = 0,

    deleted_videos = [],
    deleted_video_meta = [],
    deleted_player_meta = [],

    prevent_reload_for_current_save = false,

    // stores the button which was clicked to open the editor
    editor_button_clicked = 0,

    // the post editor content being edited
    editor_content,

    // used in WP Heartbeat
    edit_lock_removal = {},

    // CodeMirror instance, if any
    instance_code_mirror,

    // keep track of the last cursor position in CodeMirror
    instance_code_mirror_cursor_last,

    // TinyMCE instance, if any
    instance_tinymce,

    // Foliopress WYSIWYG instance, if any
    instance_fp_wysiwyg,

    is_loading = false,

    // are we editing player which is not yet in DB?
    is_unsaved = true,

    is_playlist_hero_editing = false,

    // is the player already saved in the DB but actually
    // still in a "draft" status? i.e. not published yet
    has_draft_status = true,

    // will be > 0 when any meta data are loading that need saving along with the form (example: S3 video duration, PPV product creation)
    // ... this prevents overlay closing until all meta required data are loaded and stored
    is_loading_meta_data = 0,

    // whether we're editing a single video (true) or showing a playlist view (false)
    editing_video_details = false,

    // are we currently saving data?
    is_saving = false,

    fv_player_shortcode_editor_ajax,

    // used when editing shortcode in TinyMCE
    helper_tag = window.fvwpflowplayer_helper_tag,

    // the part of shortcode outside of [fvplayer id="XYZ"]
    // also accessed from outside
    shortcode_remains,

    // Some shortcode args should be kept. For example if you edit
    // [fvplayer id="1" sort="newest"] that sort should not be removed
    store_shortcode_args = {},

    // Some shortcode args do not have a DB counterpars, so they should always
    // be kept on the shortcode. For example if you edit
    // [fvplayer src="some_video_url" sort="newest"] that sort should not be removed,
    // as it's not been transferred into the DB
    always_keep_shortcode_args = {},

    // Flowplayer only lets us specify the RTMP server for the first video in plalist, so we store it here when the playlist item order is changing etc.
    store_rtmp_server = '',

    // stores parts of editor HTML which are later re-used when adding new items
    template_playlist_item,
    template_video,
    template_subtitles_tab,

    // used to remember which widget we are editing, if any
    widget_id,

    is_editing_playlist_item_title = false,

    // list of errors that currently prevent auto-saving in the form of: { error_identifier_with_(plugin_)prefix : "the actual error text to show" }
    // ... this will be shown in place of the "Saved!" message bottom overlay and it will always show only the first error in this object,
    //     as to not overload the user and UI with errors. Once that error is corrected, it gets removed from this object and next one (if any) is shown.
    errors = {},

    store_debug_log = [];

    debug_log('Loading...');

    /**
     * Adds a notice at the bottom of player. Used for successful save and save errors. Notices are removed on successful save.
     *
     * @param {string}  id
     * @param {string}  msg
     * @param {int}    [timeout] Duration for which it should appear or omit for persistent message
     */
    function add_notice( id, msg, timeout ) {
      var notice = $('<div class="fv-player-editor-notice fv-player-editor-notice_'+id+'">'+msg+'</div>' );
      $el_notices.append( notice );

      if( typeof(timeout) == 'number' ) {
        setTimeout( function() {
          notice.fadeOut();
        }, timeout );
      }
    }

    function add_shortcode_args( args ) {
      let
        params = jQuery.map( args, function (value, index) {
          return index + '="' + value + '"';
        });

      return params.length ? ' ' + params.join(' ') : '';
    }

    function copy_player_button_toggle( show ) {
      $('#fv-player-shortcode-editor .copy_player').toggle( show );
    }

    function check_for_player_meta_field(fieldName) {
      return get_field_conf( fieldName ).store == 'player_meta';
    }

    function check_for_video_meta_field(fieldName) {

      if ( get_field_conf( fieldName ).store == 'video_meta' ) {
        return true;
      }

      return [
        'fv_wp_flowplayer_field_duration',
        'fv_wp_flowplayer_field_dvr',
        'fv_wp_flowplayer_field_auto_splash',
        'fv_wp_flowplayer_field_auto_caption',
        'fv_wp_flowplayer_field_audio'
      ].indexOf(fieldName) > -1;
    }

    function get_current_player_db_id() {
      return current_player_db_id;
    }

    function get_current_player_object() {
      return current_player_object;
    }

    function get_current_video_meta( key ) {
      var video_object = get_current_video_object(),
        video_meta = false;

      if( video_object ) {
        $( video_object.meta ).each( function(k,v) {
          if( v.meta_key == key ) {
            video_meta = v;
            return false;
          }
        } );
      }
      return video_meta;
    }

    function get_current_video_meta_value( key ) {
      var video_object = get_current_video_object(),
        video_id = video_object && video_object.id ? video_object.id : -1,
        video_meta = get_current_video_meta( key );

      if( video_meta ) {
        debug_log( "Video meta '"+key+"' for #"+video_id, video_meta.meta_value );
        return video_meta.meta_value;
      }
      debug_log( "Video meta '"+key+"' not found for #"+video_id );
      return false;
    }

    function get_current_video_object() {
      if( current_video_to_edit == -1 ) return false;

      if( current_player_object.videos && current_player_object.videos[current_video_to_edit] ) {
        return current_player_object.videos[current_video_to_edit];
      }
      return false;
    }

    /*
     *  Used when entering link to the "hero" field.
     *  If it's used in the playlist mode, we need to insert the hidden new playlist row and use the added field.
     */
    function get_hero_src_field( input ) {
      let editor_src_field = get_field("src")

      if( $( '#playlist-hero:visible' ).length && input.data('playlist-hero') ) {

        // Remember the new item index to not add new video on each keyup event!
        if( !input.data( 'new-item-index' ) ) {
          input.data( 'new-item-index', get_playlist_items_count() );
        }

        editor_src_field = editor_src_field.eq( input.data( 'new-item-index' ) );

        // Add new playlist item if it's not there
        if( !editor_src_field.length ) {
          let new_item = playlist_item_add();

          // If we are going to type in the URL, do not show the row
          if( input.attr( 'type') == 'text' ) {
            $( '#fv-player-editor-playlist .fv-player-editor-playlist-item:last').hide();
          }

          editor_src_field = get_field( "src", new_item.video_tab );
        }
      }

      return editor_src_field;
    }

    /*
     * Returns the number of videos in a playlist for the current player.
     */
    function get_playlist_items_count() {
      // TODO: Could we just use current_player_object.videos.length ? Would that work with editing?
      return jQuery('#fv-player-editor-playlist .fv-player-editor-playlist-item').length;
    }

    function get_playlist_video_meta( meta_key, index ) {
      var video_object = current_player_object.videos && current_player_object.videos[index],
        video_meta = false;

      if( video_object ) {
        $( video_object.meta ).each( function(k,v) {
          if( v.meta_key == meta_key ) {
            video_meta = v;
            return false;
          }
        } );
      }
      return video_meta;
    }

    function get_playlist_video_object( index ) {
      if( current_player_object.videos && current_player_object.videos[index] ) {
        return current_player_object.videos[index]
      }
      return false;
    }

    function get_playlist_video_meta_value( meta_key, index ) {
      var video_meta = get_playlist_video_meta( meta_key, index );

      if( video_meta ) {
        return video_meta.meta_value;
      }

      return false;
    }

    /**
     * A shorthand to save you from all the "fv_wp_flowplayer_field_"
     * when selecting fields
     *
     * @param {string}             key   The field key. For example "src" gives
     *                                   you "fv_wp_flowplayer_field_src"
     * @param {bool|jQuery|string} where Lets you narrow down the element wher you
     *                                   want to locate he field. You can use a jQuery
     *                                   element or a string selector for jQuery.
     *                                   Or TRUE to get the currently open playlist item.
     *
     * @return {jQuery}                  The field element/elements (in case of multiple language subtitles)
     */
    function get_field( key, where ) {
      var element = false,
        selector = '.' + map_names_to_editor_fields(key) + ', [name=' + map_names_to_editor_fields(key) + ']';

      if( typeof(where) == 'boolean' && where ) {
        where = jQuery('.fv-player-tab [data-index='+current_video_to_edit+']');
        element = where.find(selector);

      } else if( where && typeof(where) == "object" ) {
        element = where.find(selector);
      } else if( where && typeof(where) == "string" ) {
        element = $el_editor.find(where).find(selector);
      } else {
        element = $el_editor.find(selector);
      }

      if( !element.length ) {
        console.log('FV Player Editor Warning: field '+key+' not found');
      }

      return element;
    }

    function get_field_conf( name ) {
      name = get_field_name( name );

      if ( window.fv_player_editor_fields && window.fv_player_editor_fields[ name ] ) {
        return window.fv_player_editor_fields[ name ];
      }

      return {};
    }

    /**
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

    /**
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
      var selector = '.fv-player-tab-'+tab+' [data-playlist-item]';
      if( index == 'first' ) {
        selector += ':first';
      } else if( index == 'last' ) {
        selector += ':last';
      } else {
        selector += '[data-index='+index+']';
      }
      return $el_editor.find(selector);
    }

    /**
    * Gives you all desired tabs of a certain kind
    *
    * @return {object}            The tab elements
    */
    function get_tabs( tab ) {
      var selector = '.fv-player-tab-'+tab+' [data-playlist-item]';
      return $el_editor.find(selector);
    }

    function has_errors() {
      for ( var i in errors ) {
        return errors[ i ];
      }

      return false;
    }

    function hide_inputs() {
      if( fv_player_editor_conf.hide ) {
        $.each( fv_player_editor_conf.hide, function( k, v ) {
          try {
            if( 'configure-video' === v ) {
              $( 'a.configure-video' ).hide();
              $( "<style>.fv-player-editor-playlist-item .fvp_item_video-thumbnail { cursor: default; }</style>" ).appendTo( "head" )

            } else {
              $( '.fv-player-editor-field-wrap-'+v ).hide();
              $( '[name=fv_wp_flowplayer_field_'+v+']' ).hide();
            }
          } catch(e) {
            debug_log( 'Hide Field: Failure', e );
          }
        } );
      }
    }

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
     function insertUpdateOrDeletePlayerMeta(options) {
      var
        $element = jQuery(options.element),
        value = map_input_value( $element );

      if ( ! options.meta_section ) {
        options.meta_section = 'player';
      }

      // don't do anything if we've not found the actual element
      if (!$element.length) {
        return;
      }

      // check whether to update or delete this meta
      if ($element.data('id')) {
        // only delete this meta if delete was not prevented via options
        // and if there was no value specified, otherwise update
        if ((!options.handle_delete || options.handle_delete !== false) && !value) {
          deleted_player_meta.push( $element.data('id') );

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
      } else if (value || options.handle_delete !== false ) {
        if (typeof(options.data) != 'undefined' && typeof(options.data['player_meta'][options.meta_section]) == 'undefined') {
          options.data['player_meta'][options.meta_section] = {};
        }

        // insert new data if no meta ID
        if (typeof(options.data) != 'undefined') {
          options.data['player_meta'][options.meta_section][options.meta_key] = {
            'value': value
          }
        }

        if ( value ) {
          // execute insert callback, if present
          if (options.insert_callback && typeof(options.insert_callback) == 'function') {
            options.insert_callback();
          }

        } else {
          if (options.delete_callback && typeof(options.delete_callback) == 'function') {
            options.delete_callback();
          }
        }

      }
    }

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
     function insertUpdateOrDeleteVideoMeta(options) {
      var
        $element = jQuery(options.element),
        value = map_input_value( $element );

      // don't do anything if we've not found the actual element
      if (!$element.length) {
        return;
      }

      // check whether to update or delete this meta
      if ($element.data('id')) {
        // only delete this meta if delete was not prevented via options
        // and if there was no value specified, otherwise update
        if ((!options.handle_delete || options.handle_delete !== false) && !$element.val()) {
          deleted_video_meta.push( $element.data('id') );

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
    }

    $doc.ready( function(){
      var
        next = false, // track if the player data has changed while saving
        overlay_close_waiting_for_save = false,
        ajax_saving = true,
        int_keyup = false;

      /*$(window).on('beforeunload', function(e) {
        if (is_draft && is_draft_changed) {
          return e.originalEvent.returnValue = 'You have unsaved changes. Are you sure you want to close this dialog and loose them?';
        }
      });*/

      if( jQuery().fv_player_box ) {
        debug_log('Attaching click actions...');

        $doc.on( 'click', '.fv-wordpress-flowplayer-button, .fv-player-editor-button, .fv-player-edit', function(e) {
          // make the TinyMCE editor below this button active,
          // as otherwise we would be inserting into the last TinyMCE instance
          // on the page if no TinyMCE instance was clicked into - which is not what we want to do
          if (typeof(tinyMCE) != 'undefined' && typeof(tinyMCE.activeEditor) != 'undefined' && this.className.indexOf('fv-wordpress-flowplayer-button') > -1) {
            var
              $btn = jQuery(this),
              $wraper_div = $btn.parents('.wp-editor-wrap:first');

            for (var i in tinyMCE.editors) {
              if (tinyMCE.editors[i].editorContainer && tinyMCE.editors[i].editorContainer.id && $wraper_div.find('#' + tinyMCE.editors[i].editorContainer.id).length) {
                tinyMCE.activeEditor = tinyMCE.editors[i];
                break;
              }
            }
          }

          editor_button_clicked = this;
          e.preventDefault();

          $.fv_player_box( {
            onComplete : editor_open,
            onClosed : editor_close,
            onOpen: lightbox_open
          } );

          widget_id = $(this).data().number;
        });

        /**
         * Elementor Widget support
         */
        if ( window.elementor && window.elementor.channels && window.elementor.channels.editor ) {

          // "Configure Player" button
          elementor.channels.editor.on( 'fv-player-elementor-editor-open', function(e) {
            editor_button_clicked = e.el;

            $.fv_player_box( {
              onComplete : editor_open,
              onClosed : editor_close,
              onOpen: lightbox_open
            } );
          });

          // "Select Media" button for "Source URL"
          elementor.channels.editor.on( 'fv-player-elementor-pick-source_url ', function(e) {
            fv_flowplayer_uploader_button = jQuery( e.el );

            $( '.elementor-control-source_url [data-setting="url"]' ).addClass( 'fv_flowplayer_target' );

            fv_flowplayer_uploader_open();
          } );

          // "Select Media" button for "Splash URL"
          elementor.channels.editor.on( 'fv-player-elementor-pick-splash_url ', function(e) {
            fv_flowplayer_uploader_button = jQuery( e.el );

            $( '.elementor-control-splash_url [data-setting="url"]' ).addClass( 'fv_flowplayer_target' );

            fv_flowplayer_uploader_open();
          } );
        }

        /**
         * Look for buttons in Site Editor iframe
         */
        function setupSiteEditorHandlers() {
          var site_editor_iframe = jQuery('.edit-site-visual-editor__editor-canvas').contents();
          if( site_editor_iframe.length ) {
            debug_log( 'Site editor found!' );

            // FV Player Editor
            site_editor_iframe.off( 'click', '.fv-wordpress-flowplayer-button, .fv-player-editor-button, .fv-player-edit' );

            site_editor_iframe.on( 'click', '.fv-wordpress-flowplayer-button, .fv-player-editor-button, .fv-player-edit', function(e) {

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

            // FV Player Block "Select Media" button
            site_editor_iframe.off( 'click', '.fv-player-gutenberg-media' );

            site_editor_iframe.on( 'click', '.fv-player-gutenberg-media', fv_flowplayer_uploader_init );
          }
        }

        // Initial setup with interval
        var site_editor_load = setInterval( function() {
          if( jQuery('.edit-site-visual-editor__editor-canvas').length ) {
            clearInterval(site_editor_load);
            setupSiteEditorHandlers();

            // Watch for iframe load events (fires when iframe reloads)
            jQuery('.edit-site-visual-editor__editor-canvas').on('load', function() {
              debug_log( 'Site editor iframe loaded, reattaching handlers' );
              setTimeout( setupSiteEditorHandlers, 100 );
            });
          }
        }, 1000 );

        $doc.on( 'click', '.fv-player-export', function(e) {
          let $element = jQuery(this),
            player_id = $element.data('player_id');

          e.preventDefault();
          $.fv_player_box( {
            onComplete : function() {
              overlay_show('loading');

              debug_log('Running fv_player_db_export Ajax for #' + player_id );

              $.post(ajaxurl, {
                action: 'fv_player_db_export',
                playerID : player_id,
                nonce : $element.data('nonce'),
                cookie: encodeURIComponent(document.cookie),
              }, function(json_export_data) {
                var overlay = overlay_show('export');
                overlay.find('textarea').val( $('<div/>').text(json_export_data).html() );
              }).fail( function( jqXHR, textStatus, errorThrown ) {
                overlay_show('message', 'An unexpected error has occurred while exporting player #' + player_id + ': <code>' + errorThrown + '</code><br /><br />Please try again.');
              });

            },
            onClosed : overlay_hide,
            onOpen: lightbox_open
          } );

          return false;
        });

        $doc.on( 'click', '.fv-player-import', function(e) {
          e.preventDefault();
          $.fv_player_box( {
            onComplete : function() {
              overlay_show('import');
            },
            onClosed : overlay_hide,
            onOpen: lightbox_open
          } );

          return false;
        });

        $doc.on( 'click', '.fv-player-remove', function() {
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

        $doc.on( 'click', '.fv-player-remove-confirm', function() {
          let
            $element = $(this),
            $row_actions = $element.closest( '.row-actions' ),
            $element_td = $element.parent(),
            $spinner = $('<div class="fv-player-shortcode-editor-small-spinner"></div>'),
            player_id = $element.data('player_id');

          $element_td.find('a, span').hide();
          $element.after($spinner);

          // Make sure the row actions do not show on hover only, but always appear to make sure the spinner remains visible
          $row_actions.css( 'left', 0 );

          debug_log('Running fv_player_db_remove Ajax for #' + player_id );

          jQuery.post(ajaxurl, {
            action: "fv_player_db_remove",
            nonce: $element.data('nonce'),
            playerID: player_id
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
          }).fail(function() {
            $spinner.remove();

            $element.html('Error');

            $element_td.find('span, a:not(.fv-player-remove-confirm)').show();
          });

          return false;
        });

        $doc.on( 'click', '.fv-player-clone', function() {
          var $element = jQuery(this),
            $spinner = $('<div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>');

          $element
            .hide()
            .after($spinner);

          debug_log('Running fv_player_db_clone Ajax.');

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
                }).fail(function() {
                $spinner.remove();
                $element.show();
              });
            } else {
              $spinner.remove();
              $element.show();
              alert(playerID); // show respone message
            }
          }).fail(function() {
            $spinner.remove();
            $element.show();
            alert('Error');
          });

          return false;
        });

      }

      // Gutenberg style checkboxes
      $el_editor.on( 'change', '.components-form-toggle input[type=checkbox]', function() {
        var wrap = $(this).closest('.components-form-toggle'),
          checked = $(this).prop('checked'),
          name = $(this).attr('name').replace( /fv_wp_flowplayer_field_/, '' );

          checkbox_toggle_worker(wrap, name, checked);
      });

      $el_editor.on('change', '.components-text-control__input, .components-select-control__input', function() {
        var input = jQuery(this),
          parent = input.closest('.fv-player-editor-children-wrap'),
          name = input.attr('name').replace( /fv_wp_flowplayer_field_/, '' ),
          wrap = input.parents( '.fv-player-editor-field-wrap-' + name );

        text_and_select_worker( input, parent, name, wrap );
      });

      /*
       * Intro view text input and button
       */

      // Copy hero input value to the first video src and trigger event for save & preview
      $('[name=hero-src]').on('change keyup', function() {

        $('#playlist-hero .fv-player-editor-notice.notice-use-ui').hide();

        let input = $( this ),
          value = input.val().trim(),
          is_valid_url = false;

        if( value ) {
          if( value.match( /https?:\/\// ) ) {

            for (var vtype in window.fv_player_editor_matcher) {
              if (window.fv_player_editor_matcher[vtype].matcher.exec(value) !== null) {
                is_valid_url = true;
                break;
              }
            }

          }

          if( $( '#playlist-hero:visible' ).length ) {
            $('#playlist-hero .fv-player-editor-notice.notice-url-format').toggle( !is_valid_url );
          } else {
            $('#fv-player-shortcode-editor-preview-no .fv-player-editor-notice.notice-url-format').toggle( !is_valid_url );
          }
        }

        if( !is_valid_url ) {
          return false;
        }

        get_hero_src_field( $(this) ).val( value ).trigger( 'keyup' );
      });

      $('.button-hero').on('click', function() {
        // click on the media library button next to that input
        get_hero_src_field( $(this) ).closest('.components-base-control__field').find('.add_media').trigger('click');
      });

      /*
      * NAV TABS
      */
      $('.fv-player-tabs-header a').on( 'click', function(e) {
        e.preventDefault();
        $('.fv-player-tabs-header a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active')
        $('.fv-player-tabs > .fv-player-tab').hide();
        $('.' + $(this).data('tab')).show();

        current_editor_tab = $(this).data('tab');
      });

      /*
      * Select playlist item
      * keywords: select item
      */
      $doc.on('click','.fv-player-editor-playlist-item .configure-video, .fv-player-editor-playlist-item .fvp_item_video-thumbnail', function() {
        if( fv_player_editor_conf.hide && fv_player_editor_conf.hide.indexOf( 'configure-video' ) > 0 ) {
          return false;
        }

        playlist_item_show( $(this).parents('.fv-player-editor-playlist-item').attr('data-index') );
        return false;
      });

      /*
      * Show edit input
      * keywords: edit playlist items edit playlist items
      */
      $doc.on('click','.fv-player-tab-playlist .fv-player-editor-playlist-item .fvp_item_video-filename', function(e) {
        e.stopPropagation();

        var
          $parent = $(e.target).closest('[data-index]'),
          filename = $parent.find('.fvp_item_video-filename'),
          wrap = $parent.find('.fvp_item_video-title-wrap'),
          input = $parent.find('.fvp_item_video-edit-input');

        wrap.hide();
        input.val(filename.text()).show().focus();

        is_editing_playlist_item_title = true;
      });

      // Clicking anywhere it the editor should close the playlist item title editing
      $el_editor.on( 'click', function(e) {
        if( is_editing_playlist_item_title ) {
          // Except the title input box
          if( jQuery(e.target).hasClass('fvp_item_video-edit-input') ) {
            return;
          }

          is_editing_playlist_item_title = false;

          title_editor_close();
        }
      });

      /*
      * Edit title
      * keywords: edit title playlist items edit title playlist items
      */
      $doc.on('keyup','.fv-player-tab-playlist .fv-player-editor-playlist-item .fvp_item_video-edit-input', function(e) {
        e.stopPropagation();

        if( e.key == "Enter" ) {
          title_editor_close();
          return;
        }

        var
          $parent = $(e.target).parents('[data-index]'),
          index = $parent.attr('data-index'),
          video_tab = get_tab(index, 'video-files'),
          new_title = jQuery(this).val();

        $parent.find('.fvp_item_video-filename').text( new_title );
        get_field('title', video_tab).val( new_title ).trigger('keyup');
      });

      /*
      * Remove playlist item
      * keywords: delete playlist items remove playlist items
      */
      $doc.on('click','.fv-player-tab-playlist .fv-player-editor-playlist-item .fvp_item_remove', function(e) {
        e.stopPropagation();

        if( !confirm('Would you like to remove this video?') ) {
          return false;
        }

        var
          playlist_row = $(e.target).parents('[data-index]'),
          index = playlist_row.attr('data-index'),
          id = get_tab(index,'video-files').attr('data-id_video');

        deleted_videos.push(id);

        playlist_row.remove();

        get_tab(index,'video-files').remove();
        get_tab(index,'subtitles').remove();
        get_tab(index,'cues').remove();

        // Keep data-index in order
        playlist_index();

        // if no playlist item is left, add a new one
        // TODO: Some better way? Like do it after the data is saved
        if( !jQuery('.fv-player-tab-subtitles [data-playlist-item][data-index]').length ){

          // Required, with this it actually saves the change!
          playlist_item_add();

          if( fv_player_editor_conf.frontend ) {
            reset_preview();
            playlist_show();

          } else {
            playlist_item_show(0);
          }
        }

        $doc.trigger('fv_player_editor_item_delete');

        return false;
      });

      /*
      *  Sort playlist
      */
      $('.fv-player-tab-playlist #fv-player-editor-playlist').sortable({
        start: function() {
          store_rtmp_server = get_field( 'rtmp', get_tab('first','video-files') ).val();
        },
        update: playlist_sortable_update,
        axis: 'y',
        handle: '.fv-player-editor-playlist-move-handle',
        containment: ".fv-player-tab-playlist"
      });

      $doc.on('click','.fv-player-editor-playlist-item .fv-player-editor-playlist-move-up, .fv-player-editor-playlist-item .fv-player-editor-playlist-move-down', function(e) {
        var button = $(e.target),
          item = button.closest('[data-index]');

        if( button.hasClass('fv-player-editor-playlist-move-up') ) {
          item.fadeOut( 250, function() {
            item.insertBefore( item.prev() );
            item.fadeIn( 250, playlist_sortable_update );
          });

        } else {
          item.fadeOut( 250, function() {
            item.insertAfter( item.next() );
            item.fadeIn( 250, playlist_sortable_update );
          });
        }

      });

      function playlist_sortable_update() {
        $doc.trigger('fv-player-editor-sortable-update');
        var new_sort = [];
        $('.fv-player-tab-playlist #fv-player-editor-playlist .fv-player-editor-playlist-item').each(function(){
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

        $doc.trigger('fv_player_editor_playlist_sort');
      }

      /*
      * Uploader
      */
      var fv_flowplayer_uploader;
      var fv_flowplayer_uploader_button;

      $doc.on( 'click', '#fv-player-shortcode-editor .components-button.add_media, .fv-player-gutenberg-media', fv_flowplayer_uploader_init );

      /**
       * In order for Media Library to work we need:
       * - set fv_flowplayer_uploader_button
       * - add .fv_flowplayer_target to the input field that will be updated
       */
      function fv_flowplayer_uploader_init(e) {
        e.preventDefault();

        fv_flowplayer_uploader_button = jQuery(this);

        var el_input = fv_flowplayer_uploader_button.closest('.components-base-control__field').find('[name=' + fv_flowplayer_uploader_button.data('target') + ']');

        // Fallback to previous input, used in FV Player Block for the "Settings" sidebar, also called "Inspector"
        if ( ! el_input.length ) {
          el_input = fv_flowplayer_uploader_button.prev('.components-panel__row').find('input');
        }

        /**
         * Mark the input as target.
         *
         * If using Gutenberg block .fv_flowplayer_target won't be added anywhere, but it does not matter.
         * We update the block properites directly.
         */
        if( el_input.length ) {
          // Remove target from the previous field
          jQuery('.fv_flowplayer_target').removeClass('fv_flowplayer_target' );

          el_input.addClass('fv_flowplayer_target');
        }

        fv_flowplayer_uploader_open();
      }

      function fv_flowplayer_uploader_open() {
        debug_log( 'Opening Media Library...' );

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
          jQuery('.media-router .media-menu-item').eq(0).trigger('click');
          jQuery('.media-frame-title h1').text(fv_flowplayer_uploader_button.text());

          // Hide Media Library tabs which are not allowed
          if( fv_player_editor_conf.library ) {
            let libraries = fv_player_editor_conf.library.split(/,/),
              library_found = false;

            $( '.media-router .media-menu-item' ).each( function( i, el ) {
              let found = false;
              $( libraries ).each( function( v, library ) {
                if( $(el).attr('id').match(library) ) {
                  found = library_found = true;
                  $(el).trigger( 'click' );
                }
              });

              if( !found ) {
                $(el).hide();
              }
            });

            // At least close the Media Library if the desired that was not found
            // TODO: It should not open at all if the required library is not found
            if ( ! library_found ) {
              debug_log('Libraries '+fv_player_editor_conf.library+' not found!');

              fv_flowplayer_uploader.close();
            }
          }
        });

        //When a file is selected, grab the URL and set it as the text field's value
        fv_flowplayer_uploader.on('select', function() {
          var attachment = fv_flowplayer_uploader.state().get('selection').first().toJSON();
          var target_element = $('.fv_flowplayer_target');

          // Update the HTML input field
          if ( target_element.length ) {
            target_element.val(attachment.url).trigger('change').trigger('keyup');
            target_element.removeClass('fv_flowplayer_target' );

          }

          // Did we open the media library from within the block?
          if ( fv_flowplayer_uploader_button.hasClass( 'fv-player-gutenberg-media' ) ) {
            // Look for block ID in the current document
            var clientId = jQuery('.is-selected[data-type="fv-player-gutenberg/basic"]').data('block');

            // Look for block ID in the Site Editor iframe
            var site_editor_iframe = jQuery('.edit-site-visual-editor__editor-canvas').contents();
            if( site_editor_iframe.length ) {
              clientId = site_editor_iframe.find('.is-selected[data-type="fv-player-gutenberg/basic"]').data('block');
            }

            if ( clientId ) {
              debug_log( 'Media library updating block attributes.' );

              wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(clientId, { src: attachment.url });
            }
          }

          if( attachment.type == 'video' ) {
            // TODO: Fill video title

          } else if( attachment.type == 'image' ) {
            if( attachment.id ) {
              // update splash attachent id
              get_field( 'splash_attachment_id', true ).val(attachment.id);
            }

            if( typeof(fv_flowplayer_set_post_thumbnail_id) != "undefined" ) {
              if( jQuery('#remove-post-thumbnail').length > 0 ) {
                return;
              }

              debug_log('Running set-post-thumbnail Ajax.');

              jQuery.post(ajaxurl, {
                action:"set-post-thumbnail",
                post_id: fv_flowplayer_set_post_thumbnail_id,
                thumbnail_id: attachment.id,
                _ajax_nonce: fv_flowplayer_set_post_thumbnail_nonce,
                cookie: encodeURIComponent(document.cookie)
              }, function(str){
                if ( str == '0' ) {
                  alert( setPostThumbnailL10n.error );
                } else {
                  jQuery('#postimagediv .inside').html(str);
                  jQuery('#postimagediv .inside #plupload-upload-ui').hide();
                }
              } );
            }
          }
        });

        //Open the uploader dialog
        fv_flowplayer_uploader.open();
      }

      template_playlist_item = jQuery('.fv-player-tab-playlist #fv-player-editor-playlist .fv-player-editor-playlist-item').parent().html();
      template_video = get_tab('first','video-files').parent().html();
      template_subtitles_tab = jQuery('.fv-player-tab-subtitles').html();

      /*
      * End of playlist Actions
      */

      jQuery('#fv_wp_flowplayer_field_end_actions').on( 'change', show_end_actions );


      /*
      * Preview iframe dialog resize
      */
      $doc.on('fvp-preview-complete',function() {
        $el_preview.attr('class','preview-show');

        // If editor was in the intro mode, we show the playlist and enable the full-editor
        if( $el_editor.hasClass('is-intro') ) {
          playlist_show();

          $el_editor.removeClass('is-intro');
          $('[name=hero-src]').val('');
        }

        // If editor was in the frontend mode and adding playlist item we hide the playlist hero item now
        // and instead show the playlist item row
        if( $( '#playlist-hero:visible' ).length ) {
          playlist_hero_hide();
        }
      });

      /*
      * Video share option
      */

      // TODO: Check
      jQuery('#fv_wp_flowplayer_field_share').on( 'change', function(){
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

      $doc.on("keyup", "#fv-player-shortcode-editor-right input[type=number], #fv-player-shortcode-editor-right input[type=text], #fv-player-shortcode-editor-right textarea", function(e) {
        clearTimeout(int_keyup);
        int_keyup = setTimeout( function() {
          save(e);
        }, fv_player_editor_conf.keyup_throttle );
      });

      $doc.on('fv_flowplayer_shortcode_new fv-player-editor-non-db-shortcode', function() {
        insert_button_toggle(true);
        copy_player_button_toggle(true);

        ajax_saving = false;
      });

      $doc.on('fv_player_editor_player_loaded', function() {
        ajax_saving = false;
        is_unsaved = false;
      });

      $doc.on('fv_flowplayer_player_editor_reset', function() {
        ajax_saving = true;
        is_unsaved = true;
        has_draft_status = true;
        //is_draft_changed = false;
      });

      $doc.on('fv_player_editor_playlist_sort', save );
      $doc.on('fv_player_editor_item_delete', save );
      $doc.on('fv_player_editor_language_delete', save );

      /*
       * @param {object} [e] The event handle is invoked by input change event
       */
      function save(e) {
        // "ajax_saving" is implicitly set to true to make sure we wait with any saving until
        // all existing player's data are loaded and filled into inputs
        if ( ajax_saving ) {
          return;
        }

        // Skips the interface toggles (Advanced Settings, RTMP)
        if( e && $(e.target).hasClass('no-data') ) {
          return;
        }

        if( e && $(e.target).data('no-reload') ) {
          prevent_reload_for_current_save = true;
        } else {
          prevent_reload_for_current_save = false;
        }

        var
          ajax_data = build_ajax_data(true),
          db_data_loading = true;

        for ( var item in ajax_data.videos ) {
          // we have video data already loaded from DB,
          // ... this is the only way to see if we actually have
          //     at least a single object key while preserving
          //     browsers compatibility for browsers without support
          //     for Object.keys()
          db_data_loading = false;
          break;
        }

        if ( db_data_loading || !ajax_data.videos ) {
          // editing a player and still waiting for the videos to load from DB
          return;
        }

        if( ajax_save_previous && JSON.stringify(ajax_data) == JSON.stringify(ajax_save_previous) ) {
          debug_log('No changes to save.');
          return;
        }

        if( is_saving ) {
          debug_log('Still saving!');
          next = true;
          return;
        }

        ajax_save_previous = ajax_data;

        ajax(ajax_data);

      }

      function ajax( data ) {
        ajax_save_this_please = data;
      }

      function error(msg) {
        is_saving = false;
        $el_editor.find('.button-primary').prop('disabled', false);

        overlay_show('message', 'An unexpected error has occurred. Please try again. '+msg, true );
      }

      setInterval( function() {
        if ( !ajax_save_this_please || is_loading_meta_data ) return;

        // show error overlay if we have errors
        var err = has_errors();
        if ( err ) {
          add_notice( 'error', err );
          return;
        }

        var what_is_saving = ajax_save_this_please;

        is_saving = true;
        insert_button_toggle_disabled(true);

        el_spinner.show();

        remove_notices();

        ajax_save_this_please = set_control_fields( ajax_save_this_please );

        debug_log('Running fv_player_db_save Ajax.', ajax_save_this_please.update );

        let time_start_save = performance.now();

        $.ajax({
          type:        'POST',
          url:         ajaxurl + '?action=fv_player_db_save',
          data:        JSON.stringify( {
            data:        ajax_save_this_please,
            nonce:       fv_player_editor_conf.edit_nonce,
          } ),
          contentType: 'application/json',
          dataType:    'json',
          success: function(response) {

            debug_log( 'Finished fv_player_db_save Ajax in ' + debug_time( time_start_save ) + '.' );

            if( response.error ) {
              if( response.fatal_error ) {

                debug_log( 'Fatal error in fv_player_db_save: ' + response.fatal_error );

                var json_export_data = jQuery('<div/>').text(JSON.stringify(what_is_saving)).html();

                var overlay = overlay_show('error_saving');
                overlay.find('textarea').val( $('<div/>').text(json_export_data).html() );
                overlay.find('[data-error]').html( response.error );

                jQuery('#fv_player_copy_to_clipboard').select();

              } else {
                add_notice( 'error', response.error );

                debug_log( 'Error in fv_player_db_save: ' + response.error );
              }

              el_spinner.hide();

              is_saving = false;

              return;
            }

            let time_start_display = performance.now();

            debug_log('player ID after save: '+response.id);

            current_player_object = response;
            current_player_db_id = response.id;

            try {
              $(response.videos).each( function(k,v) {
                var item = $('.fv-player-playlist-item').eq(k);
                if( !item.data('id_video') ) {
                  item.attr('data-id_video',v.id);
                }

                if( k == current_video_to_edit ) {
                  debug_log('current_video_db_id after save: '+v.id);
                  current_video_db_id = v.id;
                }

                /*
                * The video saving might fetch the video duration, splash screen and title...
                * So we fill that in
                */
                // TODO: Populate chapters, error, is_live, is_audio, encryption key
                var video_tab = get_tab( k, 'video-files' ),
                  subtitles_tab = get_tab( k, 'subtitles' ),
                  splash_field = get_field( 'splash', video_tab ),
                  splash_attachment_id_field = get_field( 'splash_attachment_id', video_tab ),
                  title_field = get_field( 'title', video_tab ),
                  auto_splash = get_playlist_video_meta_value( 'auto_splash', k ),
                  auto_caption = get_playlist_video_meta_value( 'auto_caption', k );

                if( get_field('auto_splash', video_tab ).val() == '0' ) {
                  auto_splash = false;
                }

                if( get_field('auto_caption', video_tab ).val() == '0' ) {
                  auto_caption = false;
                }

                if( v.splash && ( !splash_field.val() || auto_splash ) ) {
                  splash_field.val( v.splash );

                  if( v.splash_attachment_id && !splash_attachment_id_field.val() ) {
                    splash_attachment_id_field.val( v.splash_attachment_id )
                  }
                }

                // Populate video meta fields for which the video checking on video save has added value
                Object.keys( window.fv_player_editor_fields ).forEach( function( field_name ) {
                  if ( 'video_meta' == window.fv_player_editor_fields[ field_name].store ) {
                    let field_value = get_playlist_video_meta_value( field_name, k );
                    if( field_value ) {
                      if ( !get_field( field_name, video_tab ).val() ) {
                        get_field( field_name, video_tab ).val( field_value );
                      }

                      if ( !get_field( field_name, subtitles_tab ).val() ) {
                        get_field( field_name, subtitles_tab ).val( field_value );
                      }
                    }
                  }
                } );

                if( auto_splash ) {
                  get_field('auto_splash', video_tab ).val( auto_splash );
                }

                if( v.title && ( !title_field.val() || auto_caption ) ) {
                  title_field.val( v.title );
                }

                if( auto_caption ) {
                  get_field('auto_caption', video_tab ).val( auto_caption );
                }

                // The Ajax save can give us some video details and it detects stream type so we refresh that information here if we are editing that video
                if( current_video_to_edit == k ) {
                  show_video_details(k);
                  show_stream_fields_worker(k);
                  show_playlist_not_supported(k);
                }
              });

              // If we are in playlist view, we refresh the list too
              if( current_video_to_edit == -1 ) {
                playlist_refresh();
              }

              /**
               * Allow plugins to fill in the hidden fields with data coming back from save response.
               * These are the fields which hold ID of any additional items created, like PPV product IDs.
               * Their IDs need to be taken into consideration right away as otherwise the PPV products might multiple
               * if you make more changes to the price and save again before the initial save is done.
               */
              $doc.trigger( 'fv_flowplayer_player_meta_load_high_priority', [ response ] );

              debug_log( 'Finished populating editor fields for ' + response.videos.length + ' videos in ' + debug_time( time_start_display ) + '.' );

              // Did the data change while saving?
              if( next ) {
                debug_log('There is more to save...');

                if ( is_unsaved ) {
                  init_saved_player_fields();
                }

                ajax( build_ajax_data(true) );
                next = false;
              } else {
                is_saving = false;

                insert_button_toggle_disabled(false);

                el_spinner.hide();

                add_notice( 'success', 'Saved!', 2500 );

                // close the overlay, if we're waiting for the save
                if (overlay_close_waiting_for_save) {
                  // add this player's ID into players that no longer need an edit lock
                  if (current_player_db_id > 0) {
                    edit_lock_removal[current_player_db_id] = 1;
                  }
                  overlay_close_waiting_for_save = false;
                  $.fn.fv_player_box.close();

                } else if ( typeof( response.html ) != "undefined" && !prevent_reload_for_current_save ) {
                  if( response.html ) {
                    // auto-refresh preview
                    el_preview_target.html( response.html );

                    $doc.trigger('fvp-preview-complete');

                  } else {
                    reset_preview();
                  }
                }

                prevent_reload_for_current_save = false;

                // if we're creating a new player, hide the Save / Insert button and
                // add all the data and inputs to page that we need for an existing player
                if ( is_unsaved ) {
                  copy_player_button_toggle(false);
                  init_saved_player_fields();
                  is_unsaved = false;
                  //is_draft_changed = false;
                  ajax_saving = false;
                  ajax_save_this_please = false;
                }

                // Output the shortcode into the pre-configured output field
                if( fv_player_editor_conf.field_selector ){
                  insert_shortcode( '[fvplayer id="'+current_player_db_id+'"]');
                }

                // Allow plugins to fill in the field with data coming back from save response
                $doc.trigger( 'fv_flowplayer_player_meta_load', [ response ] );

                // Set the current data as previous to let auto-saving detect changes
                // For new player this will have video and player IDs
                ajax_save_previous = build_ajax_data(true);
              }
            } catch(e) {
              error(e);
            }

          },
          error: function( jqXHR, textStatus, errorThrown) {
            add_notice( 'error', '<p>Error saving changes: ' + errorThrown + ': ' + jqXHR.responseText + '</p>' );

            debug_log( 'HTTP Error in fv_player_db_save: ' + errorThrown + ': ' + jqXHR.responseText );

            el_spinner.hide();

            is_saving = false;
          }
        });

        ajax_save_this_please = false;

      }, 1500 );

      editor_init();

      var $body = jQuery('body');
      $body.on('focus', '#fv_player_copy_to_clipboard', function() {
        this.select();
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

        var url = fv_player_editor_conf.home_url + '?fv_player_preview_nonce='+fv_player_editor_conf.preview_nonce+'&fv_player_preview=' + fv_player_editor.b64EncodeUnicode(shortcode);
        $.get(url, function(response) {
          wrapper.find('.fv-player-editor-preview').html( jQuery('#wrapper',response ) );
          $doc.trigger('fvp-preview-complete', [ shortcode, wrapper.data('key'), wrapper ] );
          indicator.remove();
        } );

        fv_show_video(wrapper);
      }

      $doc.on('click','.fv-player-editor-remove', function() {console.log('.fv-player-editor-remove');
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

      $doc.on('click','.fv-player-editor-more', function() {
        var wrapper = $(this).parents('.fv-player-editor-wrapper');
        var new_wrapper = wrapper.clone();
        new_wrapper.find('.fv-player-editor-field').val('');
        fv_show_video(new_wrapper);
        new_wrapper.insertAfter( $('[data-key='+wrapper.data('key')+']:last') );  //  insert after last of the kind
        $(this).hide();

        return false;
      });

      $doc.on( 'click', '.fv-player-shortcode-copy', function() {
        var button = $(this);
        fv_player_clipboard( $(this).parents('tr').find('.fv-player-shortcode-input').val(), function() {
          button.html('Coppied to clipboard!');
          setTimeout( function() {
            button.html('Copy Shortcode');
          }, 1000 );
        }, function() {
          button.html('Error');
        } );
        return false;
      });

      $doc.on('click', '#fv-player-editor-copy_player-overlay .attachment', function() {
        let item = $(this),
          sidebar = $('#fv-player-editor-copy_player-overlay .media-sidebar'),
          attachment_details = sidebar.find( '.attachment-details' ),
          details = item.data('details');

        $( '#fv-player-editor-copy_player-overlay .attachment' ).removeClass( 'selected details' );

        item.addClass('selected details');

        attachment_details.find('.filename').text( '#' + details.id + '. '+ details.player_name );
        attachment_details.find('.uploaded').text( details.date_created );

        attachment_details.find('.videos-list').html( '<ul><li>' + details.video_titles.join('</li><li>' ) + '</li></ul>' );

        let ul = $('<ul></ul>');
        $.each( details.embeds, function( k, v ) {
          let type = v.post_type != 'post' ? ' (' + v.post_type + ')' : '',
            status = v.post_status != 'publish' ? ' (' + v.post_status + ')' : '',
            taxonomies_and_terms = [];

          if( v.taxonomies ) {
            $.each( v.taxonomies, function( taxonomy, taxonomy_details ) {

              let taxonomy_terms = [];
              $.each( taxonomy_details.terms, function( k, term ) {
                taxonomy_terms.push( term.name );
              } );
              taxonomies_and_terms.push( taxonomy_details.label + ': ' + taxonomy_terms.join( ', ' ) );
            } );
          }

          taxonomies_and_terms = '<ul><li>' + taxonomies_and_terms.join( '</li><li>' ) + '</li></ul>';

          ul.append( '<li><strong>' + v.post_title + '</strong>' + type + status + taxonomies_and_terms + '</li>')
        } );
        attachment_details.find('.posts-list').html('').append( ul );

        attachment_details.show();

        $( '#fv-player-editor-copy_player-overlay .button-primary' ).prop( 'disabled', false );

      });

      $doc.on('click', '#fv-player-editor-copy_player-overlay .button-primary', function() {
        $el_editor.find('.button-primary').text('Insert');

        let selected = $( '#fv-player-editor-copy_player-overlay .attachment.selected' );
        if( selected.length > 0 ) {
          editor_open( selected.data('details').id );
        }

      });

      $doc.on('click', '.fv_player_field_insert-button', function() {
        if (is_saving || ajax_save_this_please) {
          // for some reason, clicking on already-disabled primary button re-enables it,
          // so we'll just need to disable it again here
          insert_button_toggle_disabled(true);
        } else {
          // make sure we mark this player as published in the DB
          has_draft_status = false;
          editor_submit();
        }

      });

      $doc.on('click', '.playlist_add', function() {
        // If it's front-end mode do not add new item instantly, but show input and a button
        if( fv_player_editor_conf.frontend ) {
          console.log( 'is_playlist_hero_editing', is_playlist_hero_editing );
          if( is_playlist_hero_editing ) {
            $('#playlist-hero .fv-player-editor-notice.notice-use-ui').show();
          } else {
            playlist_hero_show();
          }

        } else {
          playlist_item_add();
        }

        return false;
      });

      $doc.on('click', '.playlist_edit', function() {
        if ( !jQuery(this).hasClass('disabled') ) {
          playlist_show();

          reload_preview( current_video_to_edit );
        }

        return false;
      });

      // prevent closing of the overlay if we have unsaved data
      // unfortunately there is no event for this which we could use
      // TODO: Now we can do this propery since we dropped Colorbox!
      $.fn.fv_player_box.oldClose = $.fn.fv_player_box.close;
      $.fn.fv_player_box.close = function() {
        // don't close editor if we have errors showing, otherwise we'd just overlay them by an infinite loader
        if ( has_errors() ) {
          return;
        }

        /*if (is_draft && is_draft_changed && !window.confirm('You have unsaved changes. Are you sure you want to close this dialog and loose them?')) {
          return false;
        }*/

        // TODO: Why !is_unsaved?
        if ( ( !is_unsaved || is_saving ) && has_draft_status && !is_fv_player_screen( editor_button_clicked ) ) {
          // TODO: change into notification bubble
          alert('Your new player was saved as a draft. To reopen it, open the editor again and use the "Pick existing player" button.');
        }

        // prevent closing if we're still saving the data
        if ( ajax_save_this_please || is_saving || is_loading_meta_data ) {
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
        // $.fn.fv_player_box.oldClose();

        // reset variables
        is_unsaved = true;
        has_draft_status = true;
        //is_draft_changed = false;

        // manually invoke a heartbeat to remove an edit lock immediatelly
        if (current_player_db_id > -1 && window.wp && wp.heartbeat && wp.heartbeat.connectNow ) {
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

        debug_log('Running fv_player_db_retrieve_all_players_for_dropdown Ajax.');

        $.post(ajaxurl, {
          action: 'fv_player_db_retrieve_all_players_for_dropdown',
          nonce: fv_player_editor_conf.search_nonce,
        }, show_players ).fail( function ( jqXHR, textStatus, errorThrown ) {
          overlay_show('message', 'An unexpected error has occurred while loading players: <code>' + errorThrown + '</code><br /><br />Please try again.');
        });

        return false;
      });

      let do_search = false,
        is_searching = false,
        search_val = false;

      $doc.on('keyup', '#fv-player-editor-copy_player-overlay [name=players_selector]', function() {
        search_val = $(this).val();
        do_search = true;
      });

      setInterval( function() {
        if( do_search ) {
          if( is_searching ) {
            // Try again later
            return;
          }

          $.post(ajaxurl, {
            action: 'fv_player_db_retrieve_all_players_for_dropdown',
            nonce: fv_player_editor_conf.search_nonce,
            search: search_val
          }, show_players ).fail( function ( jqXHR, textStatus, errorThrown ) {
            overlay_show('message', 'An unexpected error has occurred while searching in player data: <code>' + errorThrown + '</code><br /><br />Please try again.');
          });

          is_searching = true;
          do_search = false;
        }
      }, 1000 );

      function show_players(json_data) {
        if( !json_data.success ) {
          overlay_show('message', json_data.data );
          return;
        }

        is_searching = false;

        let overlay = overlay_show('copy_player'),
          list = overlay.find('.attachments'),
          button = overlay.find('.button-primary');

        button.prop( 'disabled', true );
        list.html('');

        for (var i in json_data.players) {
          let data = json_data.players[i],
            image = $( data.thumbs[0] ).find('img'),
            player_name = data.player_name || data.video_titles.join( ', ' );

          image.removeAttr('width');

          let item = $('<li class="attachment"><div class="attachment-preview js--select-attachment type-video subtype-mp4 landscape fullsize"><div class="thumbnail"><div class="filename hidden"><div></div></div></div></div><button type="button" class="check" tabindex="0"><span class="media-modal-icon"></span><span class="screen-reader-text">Deselect</span></button></li>');

          item.find('.thumbnail').append( image );

          item.find('.thumbnail .filename > div').html( player_name );
          if( image.length == 0 ) {
            item.find('.thumbnail .filename').removeClass('hidden');
          }
          item.attr( 'data-details', JSON.stringify( data ) );

          list.append( item );
        }

        show_players_resize();
      }

      $( window ).on( 'resize', show_players_resize );

      function show_players_resize() {
        let player_browser = $( '#fv-player-editor-copy_player-overlay .attachments-browser'),
          player_browser_list = player_browser.find( '.attachments' ),
          idealColumnWidth =  $( window ).width() < 640 ? 135 : 150;

        player_browser.attr( 'data-columns', Math.min( Math.round( player_browser_list.width() / idealColumnWidth ), 12 ) || 1 );
      }
    });



    /**
     *  Initializes shortcode, removes playlist items, hides elements, figures out
     *  which actual field is edited - post editor, widget, etc.
     */
    function editor_init() {
      // if error / message overlay is visible, hide it
      overlay_hide();

      remove_notices();

      jQuery('#fv_wp_flowplayer_field_player_name').show();

      jQuery('#player_id_top_text').html('');

      /**
       * Get the active post editor instance
       */

      // is there a Custom Video field or Gutenberg field next to the button?
      var field = $(editor_button_clicked).parents('.fv-player-editor-wrapper, .fv-player-gutenberg').find('.fv-player-editor-field'),
        elementor_field = $(editor_button_clicked).closest( '#elementor-controls' ).find( '[data-setting="shortcode"]' ),
        widget = jQuery('#widget-widget_fvplayer-'+widget_id+'-text');

      if( fv_player_editor_conf.field_selector ){
        var custom_field_selector = jQuery(fv_player_editor_conf.field_selector)

        // If the pre-configured field was not failed it's a big deal!
        if( !custom_field_selector.length ){
          alert( 'FV Player Editor: Field '+fv_player_editor_conf.field_selector+' not found!' );
        }
        editor_content = custom_field_selector.val();

        // No need for insert button
        insert_button_toggle(false);

      } else if( field.length ) {
        if (field[0].tagName != 'TEXTAREA' && !field.hasClass('attachement-shortcode')) {
          field = field.find('textarea').first();
        }

        editor_content = jQuery(field).val();

      } else if( elementor_field.length ) {
        editor_content = elementor_field.val();

      } else if( widget.length ){
        editor_content = widget.val();
      } else if( document.querySelector('.CodeMirror') && typeof(CodeMirror) !== 'undefined' ) {
        instance_code_mirror = document.querySelector('.CodeMirror').CodeMirror;
      } else if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length){
        editor_content = jQuery('#content:not([aria-hidden=true])').val();
      } else if( typeof tinymce !== 'undefined' && typeof tinymce.majorVersion !== 'undefined' && typeof tinymce.activeEditor !== 'undefined' && tinymce.majorVersion >= 4 ){
        instance_tinymce = tinymce.activeEditor;
      } else if( typeof tinyMCE !== 'undefined' ) {
        instance_tinymce = tinyMCE.getInstanceById('content');
      } else if(typeof(FCKeditorAPI) !== 'undefined' ){
        instance_fp_wysiwyg = FCKeditorAPI.GetInstance('content');
      }

      reset_preview();

      jQuery('.fv-player-tab-video-files [data-playlist-item]').each( function(i,e) {
        if( i == 0 ) return;
        jQuery(e).remove();
      } );

      jQuery('.fv-player-tab-playlist #fv-player-editor-playlist .fv-player-editor-playlist-item').each( function(i,e) {
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

      reset_editor_ids();

      editing_video_details = true;

      $el_editor
        .addClass( 'is-singular' )
        .addClass( 'is-singular-active' )
        .removeClass( 'is-playlist-active' )
        .removeClass( 'is-playlist' );

      //hide empy tabs hide tabs
      jQuery('.fv-player-tab-playlist').hide();
      jQuery('.fv-player-playlist-item-title').html('');
      jQuery('.fv-player-tab-video-files [data-playlist-item]').show();

      jQuery('.playlist_edit').html(jQuery('.playlist_edit').data('create')).removeClass('button-primary').addClass('button');

      tabs_refresh();

      playlist_buttons_disable(false);
      playlist_buttons_toggle(true);

      set_embeds('');

      el_preview_target.html('');

      if( typeof(fv_player_shortcode_editor_ajax) != "undefined" ) {
        fv_player_shortcode_editor_ajax.abort();
      }

      $doc.trigger('fv-player-editor-init');

      hide_inputs();

      $el_editor.show();
    }

    /**
     *  Checks all the input fields and created the JavaScript object.
     *  Works when saving and also previewing.
     */
    function build_ajax_data( give_it_all ) {
      var
        $tabs                  = $el_editor.find('.fv-player-tab'),
        regex                  = /((fv_wp_flowplayer_field_|fv_wp_flowplayer_hlskey|fv_player_field_ppv_)[^ ]*)/g,
        data                   = {'video_meta' : {}, 'player_meta' : {}},
        end_of_playlist_action = jQuery('#fv_wp_flowplayer_field_end_actions').val(),
        single_video_showing   = !give_it_all && editing_video_details;

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

      // add post ID manually here, as it's a special meta key
      insertUpdateOrDeletePlayerMeta({
        data: data,
        meta_section: 'player',
        meta_key: 'post_id',
        element: jQuery('#fv_wp_flowplayer_field_post_id')[0],
        handle_delete: false
      });

      // trigger meta data save events, so we get meta data from different
      // plugins included as we post
      jQuery(document).trigger('fv_flowplayer_player_meta_save', [data, $tabs]);

      $tabs.each(function() {
        var
          $tab = jQuery(this),
          is_videos_tab = $tab.hasClass('fv-player-tab-video-files'),
          is_subtitles_tab = $tab.hasClass('fv-player-tab-subtitles'),
          $input_groups = ((is_videos_tab || is_subtitles_tab) ? $tab.find('[data-playlist-item]') : $tab.find('input, select, textarea')),
          save_index = -1;

        // prepare video and subtitles data, which are duplicated through their input names
        if (is_videos_tab) {
          data['videos'] = {};
        } else if (is_subtitles_tab) {
          data['video_meta']['subtitles'] = {};
          data['video_meta']['transcript_src'] = {};
        }

        // iterate over all tables in tabs
        $input_groups.each(function() {
          // only videos, subtitles tabs have tables, so we only need to search for their inputs when working with those
          var
            $inputs = ((is_videos_tab || is_subtitles_tab) ? jQuery(this).find('input, select, textarea').not('.no-data') : jQuery(this).not('.no-data') ),
            table_index = jQuery(this).data('index');
          save_index++;

          $inputs.each(function() {
            var
              $this               = jQuery(this),
              m;

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
                if (check_for_video_meta_field(m[1])) {
                  // prepare HLS data, if not prepared yet
                  if (!data['video_meta']['video']) {
                    data['video_meta']['video'] = {};
                  }

                  if (!data['video_meta']['video'][save_index]) {
                    data['video_meta']['video'][save_index] = {};
                  }

                  insertUpdateOrDeleteVideoMeta({
                    data: data,
                    meta_section: 'video',
                    meta_key: get_field_name(m[1]),
                    meta_index: save_index,
                    element: this
                  });
                } else {
                  data['videos'][save_index][m[1]] = map_input_value($this);
                }
              }

              // subtitles tab, subtitles inputs
              else if (is_subtitles_tab) {

                // TODO: Adding field in PHP with languages => true should add to this array:
                let video_meta_languages = {
                    subtitles:      [],
                    transcript_src: [],
                  }

                $.each( video_meta_languages, function( k, v ) {
                  if( $this.attr('name') == 'fv_wp_flowplayer_field_' + k ) {
                    if (!data['video_meta'][ k ][save_index]) {
                      data['video_meta'][ k ][save_index] = [];
                    }

                    let parent = $this.closest( '.components-base-control__field' ),
                      lang = parent.find( '[name=fv_wp_flowplayer_field_' + k + '_lang]' );

                    // jQuery-select the SELECT element when we get an INPUT, since we need to pair them
                    if ( $this[0].nodeName == 'INPUT') {
                      data['video_meta'][ k ][save_index].push({
                        code : lang.val(),
                        file : $this.val(),
                        id: parent.data('id_videometa')
                      });
                    }
                  }

                });
              }

              // all other tabs
              else {
                let player_attribute_value = map_input_value($this);

                if (check_for_player_meta_field(m[1])) {
                  // meta data input
                  insertUpdateOrDeletePlayerMeta({
                    data: data,
                    meta_section: 'player',
                    meta_key: get_field_name(m[1]),
                    element: this,
                    handle_delete: false
                  });
                } else {
                  // Is there a default value for the setting?
                  let field = m[1].replace(/fv_wp_flowplayer_field_/, '');
                  if( typeof(window.fv_player_editor_defaults[field]) != "undefined" ) {
                    // Is it the same? Do not save.
                    if( window.fv_player_editor_defaults[field] == this.checked ) {
                      continue;

                    // Should it be off while default is on? Save literal false
                    } else if( window.fv_player_editor_defaults[field] && !this.checked ) {
                      player_attribute_value = 'false';
                    }
                  }

                    // ordinary player attribute
                  data[m[1]] = player_attribute_value;
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
            if (!single_video_showing || x == current_video_to_edit) {
              data_videos_new[x++] = data['videos'][i];
            } else {
              x++;
            }
          }
        }

        data['videos'] = data_videos_new;
      }

      // add player ID and deleted elements for a DB update
      data['update'] = current_player_db_id;
      data['deleted_videos'] = deleted_videos.join(',');
      data['deleted_video_meta'] = deleted_video_meta.join(',');
      data['deleted_player_meta'] = deleted_player_meta.join(',');

      return data;
    }

    function debug_log( message, details ) {
      store_debug_log.push( message );

      console.log( 'FV Player Editor: '+message);
      if( details ) {
        store_debug_log.push( details );

        console.log(details);
      }
    }

    function debug_time( time ) {
      return Math.round( 100 * ( performance.now() - time ) / 1000 ) / 100 + ' seconds';
    }

    /**
     * Closing the editor
     * * remove hidden tags from post editor
     * * updates the wp-admin -> FV Player screen
     * * sets data for WordPress Heartbeat to unlock the player
     * * calls editor_init() for editor clean-up
     */
    function editor_close() {
      // don't close editor if we have errors showing, otherwise we'd just overlay them by an infinite loader
      if ( has_errors() ) {
        return;
      }

      // remove TinyMCE hidden tags and other similar tags which aids shortcode editing
      // to prevent opening the same player over and over
      editor_content = editor_content.replace(fv_wp_flowplayer_re_insert,'');
      editor_content = editor_content.replace(fv_wp_flowplayer_re_edit,'');
      editor_content = editor_content.replace(/#fvp_placeholder#/, '');
      editor_content = editor_content.replace(/#fvp_codemirror_placeholder#/, '');
      set_post_editor_content(editor_content);

      // this variable needs to be reset here and not in editor_init
      set_current_video_to_edit( -1 );

      // check if code mirror is active and if so, focus on it & restore cursor position
      if( instance_code_mirror && instance_code_mirror_cursor_last ) {
        // run after everything else is done
        setTimeout(function() {
          instance_code_mirror.focus();
          instance_code_mirror.setCursor( instance_code_mirror_cursor_last, 0 );
          instance_code_mirror_cursor_last = false;
        },0);
      }

      if ( !is_fv_player_screen(editor_button_clicked) ) {
        // todo: what it the point of this call being made?
        // TODO: Perhaps to ensure the temporary strings in editor are removed?
        //set_post_editor_content( editor_content.replace( fv_wp_flowplayer_re_insert, '' ) );

        // trigger update for the FV Player Custom Videos/Meta Box and Gutenberg field for preview refresh purposes
        var
          $editor_button_clicked = $(editor_button_clicked),
          $fv_player_custom_meta_box = $editor_button_clicked.parents('.fv-player-editor-wrapper').find('.fv-player-editor-field'),
          $fv_player_gutenberg = $editor_button_clicked.parents('.fv-player-gutenberg');

        if ( $fv_player_custom_meta_box.length ) {
          $fv_player_custom_meta_box.trigger('fv_flowplayer_shortcode_insert');
        }

        // Update the Gutenberg fields
        if( get_current_player_object().videos && get_current_player_object().videos[0] && fv_player_editor.clientId ) {
          var src = get_current_player_object().videos[0].src,
            splash = get_current_player_object().videos[0].splash,
            title = get_current_player_object().videos[0].title,
            timeline_previews = get_playlist_video_meta_value( 'timeline_previews', 0 ),
            hlskey = get_playlist_video_meta_value( 'hls_hlskey', 0 );

          wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(fv_player_editor.clientId, { src: src, splash: splash, title: title, timeline_previews: timeline_previews, hls_hlskey: hlskey, player_id: current_player_db_id } );
        }

        // Refresh the Elementor Widget preview
        let elementor_field = $(editor_button_clicked).closest( '#elementor-controls' ).find( '[data-setting="shortcode"]' );
        if ( elementor_field.length ) {
          elementor_field.trigger( 'input' );
        }

      } else if( current_player_db_id > 0 ) {

        // Append or update player row in wp-admin -> FV Player
        if( fv_player_editor_conf.is_fv_player_screen ) {
          var playerRow = $('#the-list span[data-player_id="' + current_player_db_id + '"]')
          if( playerRow.length == 0 ) {
            var firstRow = $('#the-list tr:first'),
              newRow = firstRow.clone();

            newRow.find('td').html('');
            playerRow = newRow.find('td').eq(0);

            firstRow.before( newRow )
          }

          jQuery.post( ajaxurl, {
            action : 'fv_player_table_new_row',
            nonce : fv_player_editor_conf.table_new_row_nonce,
            playerID :  current_player_db_id
          }, function(response) {
            playerRow.closest('tr').replaceWith( $(response).find('#the-list tr') );
          });

          playerRow.append('&nbsp; <div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>');

        // Update the player on the wp-admin -> Posts, Pages or CPT screen
        } else if( fv_player_editor_conf.is_edit_posts_screen ) {

          // Existing player
          let target_el = jQuery('.fv-player-edit[data-player_id='+current_player_db_id+']'),
            args = {
              action : 'fv_player_edit_posts_cell',
              nonce : fv_player_editor_conf.edit_posts_cell_nonce,
              playerID :  current_player_db_id
            };

          // New player, load from the new post meta
          if ( target_el.length === 0 && current_post_id ) {
            target_el = jQuery('.fv-player-edit[data-post-id='+current_post_id+']');
            args.post_id = current_post_id;
            args.meta_key = current_post_meta_key;
          }

          target_el.find('.fv_player_splash_list_preview').append('<div class="fv-player-shortcode-editor-small-spinner">&nbsp;</div>');

          jQuery.post( ajaxurl, args, function(response) {
            if ( response ) {
              target_el.replaceWith( response );
            }
          });

        }

      }

      // we need to do this now to make sure Heartbeat gets the correct data
      if (current_player_db_id > 0 ){
        edit_lock_removal[current_player_db_id] = 1;
        current_player_db_id = 0;

        debug_log( 'editor_close current_player_db_id = 0' );
      }

      editor_init();

    }


    /**
    * removes previous values from editor
    * fills new values from shortcode
    *
    * @param {int} selected_player_id Optional, force load of specified player ID using "Pick existing player"
    */
    function editor_open( selected_player_id ) {
      if( !selected_player_id ) {
        editor_init();
      }

      instance_code_mirror_cursor_last = false;

      // remove any DB data IDs that may be left in the form
      $el_editor.find('[data-id]').removeData('id').removeAttr('data-id');
      $el_editor.find('[data-id_video]').removeData('id_video').removeAttr('data-id_video');
      $el_editor.find('[data-id_videometa]').removeData('id_videometa').removeAttr('data-subtitles');

      // fire up editor reset event, so plugins can clear up their data IDs as well
      $doc.trigger('fv_flowplayer_player_editor_reset');

      // reset content of any input fields, except what has .extra-field
      $el_editor.find("input:not(.extra-field), textarea").each( function() {
        $(this)
          .val( '' )
          .prop( 'checked', false )
          .trigger( 'change' );
      } );

      $el_editor.find('select:not([multiple])').prop('selectedIndex',0); // select first index, ignore multiselect - let it be handled separately

      $el_editor.find(".fv_player_field_insert-button").text( 'Insert' );

      if( window.fv_player_editor_defaults ) {
        jQuery.each( window.fv_player_editor_defaults, function(k,v) {
          var checkbox = $el_editor.find('[name=fv_wp_flowplayer_field_'+k+'][type=checkbox]');
          if( checkbox.length ) {
            checkbox.prop( 'checked', !!v ).trigger( 'change' );

            var wrap = checkbox.closest('.components-form-toggle');
            wrap.toggleClass( 'is-checked is-default', !!v );
          }
        } );
      }

      var
        field = $(editor_button_clicked).parents('.fv-player-editor-wrapper, .fv-player-gutenberg').find('.fv-player-editor-field'),
        clientId = $(editor_button_clicked).parents('.fv-player-editor-wrapper, .fv-player-gutenberg').find('.fv-player-gutenberg-client-id').val(),
        elementor_field = $(editor_button_clicked).closest( '#elementor-controls' ).find( '[data-setting="shortcode"]' ),
        is_elementor = elementor_field.length,
        is_gutenberg = $(editor_button_clicked).parents('.fv-player-gutenberg').length,
        shortcode = false,
        shortcode_parse_fix = false;

      if( clientId ) {
        fv_player_editor.clientId = clientId;
      } else {
        fv_player_editor.clientId = null;
      }

      // if we've got a numeric DB ID passed to this function, use it directly
      // but don't replace editor_content, since we'll need that to be actually updated
      // rather then set to a player ID
      if (selected_player_id) {
        debug_log('Loading for player id: '+selected_player_id );

        shortcode = 'fvplayer id="' + selected_player_id + '"';

        current_post_id = $(editor_button_clicked).data('post-id');
        current_post_meta_key = $(editor_button_clicked).data('meta_key');

        if( current_post_id || current_post_meta_key ) {
          debug_log( 'New player for post #' + current_post_id + ' for ' + current_post_meta_key + ' meta_key.' );
        }

      } else {

        /**
         * Load shortcode for the active post editor
         */

        if( fv_player_editor_conf.field_selector ){
          let custom_field_selector = jQuery(fv_player_editor_conf.field_selector)

          // If the pre-configured field was not failed it's a big deal!
          if( !custom_field_selector.length ){
            alert( 'FV Player Editor: Field '+fv_player_editor_conf.field_selector+' not found!' );
          }
          editor_content = custom_field_selector.val();
          shortcode = editor_content;

        // Edit button on wp-admin -> FV Player screen
        } else if (is_fv_player_screen_edit(editor_button_clicked)) {
          current_player_db_id = $(editor_button_clicked).data('player_id');

          current_post_id = $(editor_button_clicked).data('post-id');
          current_post_meta_key = $(editor_button_clicked).data('meta_key');

          if( current_post_id || current_post_meta_key ) {
            debug_log( 'New player for post #' + current_post_id + ' for ' + current_post_meta_key + ' meta_key.' );

            editor_content = '';

          } else {
            debug_log('Loading for FV Player screen, player id: '+current_player_db_id );

            // create an artificial shortcode from which we can extract the actual player ID later below
            editor_content = '[fvplayer id="' + current_player_db_id + '"]';
            shortcode = editor_content;
          }
        }

        // Add new button on wp-admin -> FV Player screen
        else if (is_fv_player_screen_add_new(editor_button_clicked)) {
          debug_log('Loading for FV Player screen, new player' );

          // create empty shortcode for Add New button on the list page
          editor_content = '';
          shortcode = '';

        }

        // custom Field or Widget
        else if (field.length || jQuery('#widget-widget_fvplayer-' + widget_id + '-text').length) {
          debug_log('Loading for custom field or a widget...');

          // this is a horrible hack as it adds the hidden marker to the otherwise clean text field value
          // just to make sure the shortcode varible below is parsed properly.
          // But it allows some extra text to be entered into the text widget, so for now - ok
          if (editor_content.match(/\[/)) {
            editor_content = '[<' + helper_tag +' rel="FCKFVWPFlowplayerPlaceholder">&shy;</' + helper_tag + '>' + editor_content.replace('[', '') + '';
          } else {
            editor_content = '<' + helper_tag + ' rel="FCKFVWPFlowplayerPlaceholder">&shy;</' + helper_tag + '>' + editor_content + '';
          }

        } else if ( is_elementor ) {
          debug_log( 'Loading for Elementor Widget...', editor_content );

        // CodeMirror
        } else if( instance_code_mirror ) {
          debug_log('Loading for CodeMirror...' );

          // get content
          editor_content = instance_code_mirror.getDoc().getValue();

          // get cursor position
          var position = instance_code_mirror.getDoc().getCursor(),
            line = position.line,
            ch = position.ch,
            line_value = instance_code_mirror.getDoc().getLine(line);

          // save cursor position
          instance_code_mirror_cursor_last = {line: line, ch: ch};

          // look for start of shortcode
          var shotcode_pattern = new RegExp(/\[fvplayer[^\[\]]*]?/g);

          if( shotcode_pattern.test(line_value) ) {
            for( var start = ch; start--; start >= 0 ) {
              if( line_value[start] == '[' ) {
                var sliced_content = line_value.slice(start);
                var matched = sliced_content.match(shotcode_pattern);

                if( matched ) {
                  shortcode = matched[0];
                }

                break;
              } else if( line_value[start] == ']' ) {
                break
              }
            }

          } else {
            // add placeholder for new editor
            instance_code_mirror.getDoc().replaceRange('#fvp_codemirror_placeholder#', {line: line, ch: ch}, {line: line, ch: ch});
            editor_content = instance_code_mirror.getDoc().getValue();
          }

        }
        // TinyMCE in Text Mode
        else if (typeof (FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length) {
          debug_log('Loading for TinyMCE in Text Mode...');

          var position = jQuery('#content:not([aria-hidden=true])').prop('selectionStart');

          // look for start of shortcode
          for (var start = position; start--; start >= 0) {
            if (editor_content[start] == '[') {
              var sliced_content = editor_content.slice(start);
              var matched = sliced_content.match(/^\[fvplayer[^\[\]]*]?/);
              // found the shortcode!
              if (matched) {
                shortcode = matched[0];
              }

              break;
            } else if (editor_content[start] == ']') {
              break
            }
          }
          // TODO: It would be better to use #fv_player_editor_{random number}# and remember it for the editing session
          editor_content = editor_content.slice(0, position) + '#fvp_placeholder#' + editor_content.slice(position);
        }

        // Foliopress WYSIWYG
        else if (
          instance_fp_wysiwyg && (
            instance_tinymce == undefined || (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor.isHidden())
          )
        ) {
          debug_log('Loading for Foliopress WYSIWYG...' );

          editor_content = instance_fp_wysiwyg.GetHTML();
          if (editor_content.match(fv_wp_flowplayer_re_insert) == null) {
            instance_fp_wysiwyg.InsertHtml('<' + fvwpflowplayer_helper_tag + ' rel="FCKFVWPFlowplayerPlaceholder">&shy;</' + fvwpflowplayer_helper_tag + '>');
            editor_content = instance_fp_wysiwyg.GetHTML();
          }

        } else if( instance_tinymce ) {
          debug_log('Loading for TinyMCE in Visual Mode...' );

          // TinyMCE in Visual Mode
          editor_content = instance_tinymce.getContent();
          instance_tinymce.settings.validate = false;
          if (editor_content.match(fv_wp_flowplayer_re_insert) == null) {
            var tags = ['b', 'span', 'div'];
            for ( let i in tags) {
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
        } else {
          editor_content = '';

        }

        if( !shortcode ){
          let content = editor_content.replace(/\n/g, '\uffff');

          if ( is_elementor || is_gutenberg ) {
            shortcode = content;

          } else {
            let match = content.match( fv_wp_flowplayer_re_edit );
            if( match ) {
              shortcode = match[0];
            }
          }
        }

      }

      // remove visual editor placeholders etc.
      if (shortcode) {
        shortcode = shortcode
          .replace(/^\[|]+$/gm, '')
          .replace(fv_wp_flowplayer_re_insert, '')
          .replace(/\\'/g, '&#039;');
      }

      if( shortcode ) {
        debug_log('Loading shortcode: '+shortcode );

        // check for new, DB-based player shortcode
        var result = /fvplayer.* id="([\d,]+)"/g.exec(shortcode);
        if (result !== null) {
          shortcode_parse_fix = shortcode
              .replace(/(popup|ad)='[^']*?'/g, '')
              .replace(/(popup|ad)="(.*?[^\\\\/])"/g, '');

          shortcode_remains = shortcode_parse_fix.replace( /^\S+\s*?/, '' );

          // DB-based player, create a "wait" overlay
          overlay_show('loading');

          // store player ID into fv_player_conf, so we can keep sending it
          // in WP heartbeat
          current_player_db_id = result[1];

          debug_log('Loading shortcode player id: '+current_player_db_id );

          if (edit_lock_removal[result[1]]) {
            delete edit_lock_removal[result[1]];
          }

          // check if we don't have multiple-playlists shortcode,
          // in which case we need to stop and show an error message
          if (shortcode.indexOf(',') > -1) {
            overlay_show('message', 'Shortcode editor is not available for multiple players shortcode tag.');
            return;
          }

          // stop Ajax saving that might occur from thinking it's a draft taking place
          is_unsaved = false;

          is_loading = true;

          // now load playlist data
          // load video data via an AJAX call
          debug_log('Running fv_player_db_load Ajax.');

          let time_start = performance.now();

          fv_player_shortcode_editor_ajax = jQuery.post( ajaxurl + '?fv_player_db_load=' + result[1], {
            action : 'fv_player_db_load',
            nonce : fv_player_editor_conf.db_load_nonce,
            playerID :  result[1],
            current_video_to_edit: get_playlist_items_count() > 1 ? current_video_to_edit : -1,
          }, function(response) {
            var vids = response['videos'];

            debug_log('Finished fv_player_db_load Ajax in ' + debug_time( time_start ) + '.',response);

            if (response) {

              if ( response.debug_duplicate_players && response.debug_duplicate_players.length ) {
                alert( "FV Player Editor warning:\n\n\
It seems the player #" + result[1] + " which you are about to edit is a duplicate of the player #" + response.debug_duplicate_players.join(', ') + ".\n\n\
Any changes made to the videos will update the other player as well. \
You can also carefully remove the duplicate player by checking which one is actually used in your post.\n\n\
Please also contact FV Player support with the following debug information:\n\n\
" + store_debug_log.join("\n")
                );
              }

              if( response.error ) {
                reset_editor_ids();

                overlay_show('message', response.error );

                // The editor failed to load, it's not locked
                edit_lock_removal[current_player_db_id] = 1;

                // Prevent autosave notice from appearing
                is_unsaved = true;

                is_loading = false;
                return;
              }

              let time_start = performance.now();

              init_saved_player_fields( result[1] );

              // remove everything with index 0 and the initial video placeholder,
              // otherwise our indexing & previews wouldn't work correctly
              jQuery('[data-index="0"]').remove();
              jQuery('.fv-player-tab-playlist #fv-player-editor-playlist .fv-player-editor-playlist-item').remove();
              jQuery('.fv-player-tab-video-files table').remove();

              set_embeds(response['embeds']);

              current_player_object = response;

              // fire the player load event to cater for any plugins listening
              $doc.trigger('fv_flowplayer_player_meta_load', [response]);

              for (var key in response) {
                // put the field value where it belongs
                if (key !== 'videos') {
                  // in case of meta data, proceed with each player meta one by one
                  if (key == 'meta') {
                    for ( let i in response[key]) {
                      set_editor_field(response[key][i]['meta_key'], response[key][i]['meta_value'], response[key][i]['id']);
                    }
                  } else {
                    set_editor_field(key, response[key]);
                  }
                }
              }

              let new_video_tabs = [],
                new_subtitles_tabs = [];

              // add videos from the DB
              for (var x in vids) {
                let
                  video_meta_languages = {
                    subtitles:      [],
                    transcript_src: [],
                  },
                  video_meta_to_set = [];

                // add all subtitles
                if (vids[x].meta && vids[x].meta.length) {
                  for (var m in vids[x].meta) {
                    let is_languages_meta = false;
                    $.each( video_meta_languages, function( k, v ) {
                      if (vids[x].meta[m].meta_key.indexOf( k ) > -1) {
                        is_languages_meta = true;

                        // Map subtitles_en to en and subtitles to just empty string
                        let lang = vids[x].meta[m].meta_key.replace( k + '_', '');
                        if ( 'subtitles' === lang ) {
                          lang = '';
                        }

                        video_meta_languages[k].push({
                          lang: lang,
                          file: vids[x].meta[m].meta_value,
                          id: vids[x].meta[m].id
                        });
                      }
                    } );

                    if ( ! is_languages_meta ) {
                      video_meta_to_set.push(vids[x].meta[m]);
                    }
                  }
                }

                let new_item = playlist_item_add( vids[x], true );
                new_video_tabs.push( new_item.video_tab );
                new_subtitles_tabs.push( new_item.subtitles_tab );

                $.each( video_meta_languages, function( k, v ) {
                  for (var i in v) {
                    language_add( k, v[i].file, v[i].lang, new_item.subtitles_tab, v[i].id);
                  }
                } );

                // This sets some additional video meta fields, like remove_black_bars
                if (video_meta_to_set.length) {
                  for ( let i in video_meta_to_set) {
                    set_editor_field( video_meta_to_set[i].meta_key, video_meta_to_set[i].meta_value, video_meta_to_set[i].id, new_item.video_tab );
                  }
                }

                // fire up meta load event for this video, so plugins can process it and react
                $doc.trigger('fv_flowplayer_video_meta_load', [ x, vids[x].meta, new_item.video_tab, new_item.subtitles_tab ] );
              }

              // Add all the new video and subtitle tabs to the DOM all at once
              jQuery('.fv-player-tab-video-files').append( new_video_tabs );
              jQuery('.fv-player-tab-subtitles').append( new_subtitles_tabs );

              debug_log( 'Finished populating editor fields for ' + vids.length + ' videos in ' + debug_time( time_start ) + '.' );

              // show playlist instead of the "add new video" form
              // if we have more than 1 video
              if( current_video_to_edit > -1 ) {
                playlist_item_show(current_video_to_edit);
              } else if ( vids.length > 1 || fv_player_editor_conf.frontend ) {
                playlist_show();
              } else {
                playlist_item_show(0);
              }

              // if this player is published, mark it as such
              has_draft_status = ( response.status == 'draft' );

              // Set the current data as previous to let auto-saving detect changes
              ajax_save_previous = build_ajax_data(true);

              is_loading = false;
            }

            overlay_hide();

            if ( response.html ) {
              // auto-refresh preview
              el_preview_target.html( response.html )

              $doc.trigger('fvp-preview-complete');
            }

            // show the Insert button, as this is only used when adding a new player into a post
            // and using the Pick existing player button, where we need to be able to actually
            // insert the player code into the editor
            // ... also, keep the Pick existing player button showing, if we decided to choose
            //     a different player
            if ( selected_player_id ) {
              insert_button_toggle(true);
              copy_player_button_toggle(true);

            } else if ( response.status == 'draft' ) {
              // show Save / Insert button, as we're still
              // in draft mode for this player
              insert_button_toggle(true);
              fix_save_btn_text();

            } else {
              insert_button_toggle(false);
              copy_player_button_toggle(false);
            }

            $doc.trigger('fv_player_editor_player_loaded');

            $doc.trigger('fv_player_editor_finished');

          }).fail( function( jqXHR, textStatus, errorThrown ) {
            if ( jqXHR.status == 404 ) {
              overlay_show('message', 'The requested player #' + result[1] + ' could not be found.');
            } else {
              overlay_show('message', 'An unexpected error has occurred while loading player #' + result[1] + ': <code>' + errorThrown + '</code><br /><br />Please try again.');
            }

            // show the Insert button, as this is only used when adding a new player into a post
            // and using the Pick existing player button, where we need to be able to actually
            // insert the player code into the editor
            // ... also, keep the Pick existing player button showing, if we decided to choose
            //     a different player
            if ( selected_player_id ) {
              insert_button_toggle(true);
              copy_player_button_toggle(true);
            }
          });
        } else {
          debug_log('Loading shortcode without player id...');

          // TODO: Check if all the values are set properly, for example controlbar checkbox is not set properly anymore 

          $doc.trigger('fv-player-editor-non-db-shortcode');
          // ordinary text shortcode in the editor
          shortcode_parse_fix = shortcode.replace(/(popup|ad)='[^']*?'/g, '');
          shortcode_parse_fix = shortcode_parse_fix.replace(/(popup|ad)="(.*?[^\\\\/])"/g, '');
          shortcode_remains = shortcode_parse_fix.replace( /^\S+\s*?/, '' );

          var srcurl = shortcode_parse_arg( shortcode_parse_fix, 'src' );
          var srcurl1 = shortcode_parse_arg( shortcode, 'src1' );
          var srcurl2 = shortcode_parse_arg( shortcode, 'src2' );

          var srcrtmp = shortcode_parse_arg( shortcode, 'rtmp' );
          var srcrtmp_path = shortcode_parse_arg( shortcode, 'rtmp_path' );

          var iwidth = shortcode_parse_arg( shortcode_parse_fix, 'width' );
          var iheight = shortcode_parse_arg( shortcode_parse_fix, 'height' );

          var sad_skip = shortcode_parse_arg( shortcode, 'ad_skip' );
          var scontrolbar = shortcode_parse_arg( shortcode, 'controlbar' );
          var sautoplay = shortcode_parse_arg( shortcode, 'autoplay' );
          var sliststyle = shortcode_parse_arg( shortcode, 'liststyle' );
          var sembed = shortcode_parse_arg( shortcode_parse_fix, 'embed' );
          var sloop = shortcode_parse_arg( shortcode, 'loop' );
          var slive = shortcode_parse_arg( shortcode, 'live' );
          var sshare = shortcode_parse_arg( shortcode, 'share', false, shortcode_share_parse_arg );
          var sspeed = shortcode_parse_arg( shortcode, 'speed' );
          var ssplash = shortcode_parse_arg( shortcode, 'splash' );
          var ssplashend = shortcode_parse_arg( shortcode, 'splashend' );
          var ssticky = shortcode_parse_arg( shortcode, 'sticky' );

          var splaylist_advance = shortcode_parse_arg( shortcode, 'playlist_advance' );

          var ssubtitles = shortcode_parse_arg( shortcode, 'subtitles' );
          var aSubtitlesLangs = shortcode.match(/subtitles_[a-z][a-z]+/g);
          for( let i in aSubtitlesLangs ){  //  move
            shortcode_parse_arg( shortcode, aSubtitlesLangs[i], false, shortcode_subtitle_parse_arg );
          }
          if(!aSubtitlesLangs){ //  move
            subtitle_language_add(false, false );
          }

          var smobile = shortcode_parse_arg( shortcode, 'mobile' );
          var sredirect = shortcode_parse_arg( shortcode, 'redirect' );

          var sCaptions = shortcode_parse_arg( shortcode, 'caption' );
          var sSplashText = shortcode_parse_arg( shortcode, 'splash_text' );
          var sPlaylist = shortcode_parse_arg( shortcode, 'playlist' );

          var sad = shortcode_parse_arg( shortcode, 'ad', true );
          var iadwidth = shortcode_parse_arg( shortcode, 'ad_width' );
          var iadheight = shortcode_parse_arg( shortcode, 'ad_height' );

          // TODO: Test if RTMP is shown
          if( srcrtmp != null && srcrtmp[1] != null ) {
            jQuery(".fv_wp_flowplayer_field_rtmp").val( srcrtmp[1] );
            jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").show();
          }
          if( srcrtmp_path != null && srcrtmp_path[1] != null ) {
            jQuery(".fv_wp_flowplayer_field_rtmp_path").val( srcrtmp_path[1] );
            jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").show();
          }
          var playlist_row = jQuery('.fv-player-tab-playlist tbody tr:first')

          if( srcurl != null && srcurl[1] != null ) {
            get_field( "src" ).val( srcurl[1] ).trigger('change');
          }

          if( srcurl1 != null && srcurl1[1] != null ) {
            get_field( "src1" ).val( srcurl1[1] ).trigger('change');

            if( srcurl2 != null && srcurl2[1] != null ) {
              get_field( "src2" ).val( srcurl2[1] ).trigger('change');
            }

            get_field( 'toggle_advanced_settings' ).prop( 'checked', true ).trigger('change');
          }

          if( srcurl != null && srcurl[1] != null ) {
            get_field( "src" ).val( srcurl[1] );
            playlist_row.find('.fvp_item_video-filename').text( srcurl[1] );
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
            if (sliststyle[1] == 'horizontal') objPlaylistStyle.selectedIndex = 1;
            if (sliststyle[1] == 'tabs') objPlaylistStyle.selectedIndex = 2;
            if (sliststyle[1] == 'prevnext') objPlaylistStyle.selectedIndex = 3;
            if (sliststyle[1] == 'vertical') objPlaylistStyle.selectedIndex = 4;
            if (sliststyle[1] == 'slider') objPlaylistStyle.selectedIndex = 5;
            if (sliststyle[1] == 'season') objPlaylistStyle.selectedIndex = 6;
            if (sliststyle[1] == 'polaroid') objPlaylistStyle.selectedIndex = 7;
            if (sliststyle[1] == 'text') objPlaylistStyle.selectedIndex = 8;
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
            get_field("splash").val( ssplash[1] ).trigger('change');

            var playlist_img = jQuery('<img />')
              .attr('width', 120 )
              .attr('src', ssplash[1] );

            playlist_row.find('.fvp_item_splash').html( playlist_img );
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
            get_field("overlay").val(sad);

            get_field( 'toggle_overlay' ).prop('checked', true).trigger('change');
          }

          if( iadheight != null && iadheight[1] != null )
            get_field("overlay_height").val(iadheight[1]);
          if( iadwidth != null && iadwidth[1] != null )
            get_field("overlay_width").val(iadwidth[1]);
          if( sad_skip != null && sad_skip[1] != null && sad_skip[1] == 'yes' )
            get_field("overlay_skip").prop('checked',true).trigger('change');

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

          //TODO: shortcode popup
          // get_field("popup")[0].parentNode.style.display = 'none'
          var spopup = shortcode_parse_arg( shortcode, 'popup', true );

          let end_actions_val = false;
          if( sredirect != null && sredirect[1] != null ){
            end_actions_val = 2;
            get_field("redirect").val( sredirect[1] );

          } else if( sloop != null && sloop[1] != null && sloop[1] == 'true' ){
            end_actions_val = 3;

          } else if( spopup != null && spopup[1] != null ) {
            end_actions_val = 4;

            spopup = spopup[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
            spopup = spopup.replace(/&amp;/g,'&');

            if( spopup.match(/email-[0-9]*/)){
              end_actions_val = 6;

              get_field("email_list").val(spopup.match(/email-([0-9]*)/)[1]);

            } else {
              get_field("popup_id").val(spopup);
            }

          } else if( ssplashend != null && ssplashend[1] != null && ssplashend[1] == 'show' ){
            end_actions_val = 5;
          }

          if( end_actions_val ) {
            get_field("end_actions").prop( 'selectedIndex', end_actions_val ).trigger('change');

            get_field( 'toggle_end_action' ).prop('checked', true).trigger('change');
          }

          if( splaylist_advance != null && splaylist_advance[1] != null ) {
            let field = get_field("playlist_advance")[0];
            if (splaylist_advance[1] == 'true') field.selectedIndex = 1;
            if (splaylist_advance[1] == 'false') field.selectedIndex = 2;
          }

          if( scontrolbar != null && scontrolbar[1] != null ) {
            let field = get_field("controlbar")[0];
            if (scontrolbar[1] == 'yes' || scontrolbar[1] == 'show' ) field.selectedIndex = 1;
            if (scontrolbar[1] == 'no' || scontrolbar[1] == 'hide' ) field.selectedIndex = 2;
          }

          var aCaptions = false;
          if( sCaptions ) {
            aCaptions = shortcode_arg_split(sCaptions);

            var caption = aCaptions.shift();
            get_field("title").val( caption );
            if( caption ) {
              playlist_row.find('.fvp_item_video-filename').text( caption );
            }
          }

          var aSplashText = false;
          if( sSplashText ) {
            aSplashText = shortcode_arg_split(sSplashText);

            var splash_text = aSplashText.shift();
            get_field("splash_text").val( splash_text );
          }

          if( sPlaylist ) {
            // check for all-numeric playlist items separated by commas
            // which outlines video IDs from a database
            var aPlaylist = sPlaylist[1].split(';');
            for ( let i in aPlaylist) {

              // TODO: Will this work?
              playlist_item_add( {
                src: aPlaylist[i],
                title: aCaptions[i],
                subtitles: aSubtitles[i],
                splash_text: aSplashText[i]
              } );
            }
          }

          if( jQuery('.fv-fp-subtitles .fv-fp-subtitle:first input.fv_wp_flowplayer_field_subtitles').val() == '' ) {
            jQuery('.fv-fp-subtitles .fv-fp-subtitle:first').remove();
          }

          try {
            jQuery(document).trigger('fv_flowplayer_shortcode_parse', [ shortcode_parse_fix, shortcode_remains ] );
          } catch(e) {
            debug_log('Error: fv_flowplayer_shortcode_parse', e);
          }

          if (slive != null && slive[1] != null && slive[1] == 'true') {
            jQuery("input[name=fv_wp_flowplayer_field_live]").each(function () {
              this.checked = 1;
              jQuery(this).closest('tr').show();
            });
          }

          if(sPlaylist){
            playlist_show();
          } else {
            playlist_item_show(0);
          }

          tabs_refresh();

        }

        store_shortcode_args = {};
        always_keep_shortcode_args = {};
        for( let i in fv_player_editor_conf.shortcode_args_to_preserve ) {
          let value = shortcode_parse_arg( shortcode_parse_fix, fv_player_editor_conf.shortcode_args_to_preserve[i] );
          if (value && value[1]) {
            store_shortcode_args[fv_player_editor_conf.shortcode_args_to_preserve[i]] = value[1];
          }
        }

        if( store_shortcode_args.length ) {
          debug_log('Preserving shortcode args', store_shortcode_args );
        }

        for( let i in fv_player_editor_conf.shortcode_args_not_db_compatible ) {
          let value = shortcode_parse_arg( shortcode_parse_fix, fv_player_editor_conf.shortcode_args_not_db_compatible[i] );
          if (value && value[1]) {
            always_keep_shortcode_args[fv_player_editor_conf.shortcode_args_not_db_compatible[i]] = value[1];
          }
        }

        if( always_keep_shortcode_args.length ) {
          debug_log('Always preserve shortcode args', always_keep_shortcode_args );
        }

      } else {
        debug_log('New player...' );

        playlist_item_show(0);

        jQuery(document).trigger('fv_flowplayer_shortcode_new');
        shortcode_remains = '';
        fix_save_btn_text();
      }

      $doc.trigger('fv_player_editor_finished');
    }

    /**
     * Makes sure that the Save / Insert button is showing the correct text
     * based on current context.
     */
    function fix_save_btn_text() {
      // rename insert to save for new playlists if we come from list view
      if ( is_fv_player_screen_add_new( editor_button_clicked ) || is_fv_player_screen_edit( editor_button_clicked ) ) {
        jQuery('.fv_player_field_insert-button').text('Save');
      } else {
        // we're in a post / widget, rename save button to Insert
        jQuery('.fv_player_field_insert-button').text('Insert');
      }
    }

    /*
     *  Saving the data
     */
    function editor_submit() {
      // bail out if we're already saving, we're loading meta data or we have errors
      if ( ajax_save_this_please || is_saving || is_loading_meta_data || has_errors() ) {
        // if we're saving a new player, let's disable the Save button and wait until meta data are loaded
        if ( current_player_db_id < 0 ) {
          if (is_loading_meta_data) {
            insert_button_toggle_disabled(true);

            $el_editor.find('.button-primary').text('Saving...');
            var checker = setInterval(function() {
              if (is_loading_meta_data <= 0) {
                clearInterval(checker);
                editor_submit(); // call this function again, so we can really save this time
              }
            }, 500);

            return;
          }
        } else {
          // if we're updating a player, just return here if we're loading meta data,
          // as that would result in duplicate save - once with and once without meta data
          if (is_loading_meta_data) {
            return;
          }
        }

        // we have errors, disable the Save button
        if ( has_errors() ) {
          insert_button_toggle_disabled(true);
          return;
        }
      }

      // TODO: Tell people they only have RTMP
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

      var ajax_data = build_ajax_data(true);

      overlay_show('loading');

      var player_was_non_db = (current_player_db_id > -1 ? false : true);

      // unmark DB player ID as being currently edited
      if ( current_player_db_id > 0 ) {
        current_player_db_id = 0;
      }

      // if player should be in published state, add it into the AJAX data
      if ( !has_draft_status ) {
        ajax_data['status'] = 'published';
      }

      debug_log('Running fv_player_db_save Ajax on submit.');

      ajax_data = set_control_fields( ajax_data );

      let time_start = performance.now();
      
      // save data
      // We use ?fv_player_db_save=1 as some people use that to exclude firewall rules
      $.ajax({
        type:        'POST',
        url:         ajaxurl + '?action=fv_player_db_save',
        data:        JSON.stringify( {
          data:        ajax_data,
          nonce:       fv_player_editor_conf.edit_nonce,
        } ),
        contentType: 'application/json',
        dataType:    'json',
        success: function(response) {

          debug_log( 'Finished fv_player_db_save Ajax in ' + debug_time( time_start ) + '.' );

          if( response.error ) {
            if( response.error && response.fatal_error ) {

              debug_log( 'Fatal error in fv_player_db_save: ' + response.fatal_error );

              let json_export_data = jQuery('<div/>').text(JSON.stringify(ajax_data)).html();

              let overlay = overlay_show('error_saving');
              overlay.find('textarea').val( $('<div/>').text(json_export_data).html() );
              overlay.find('[data-error]').html( response.error );

              jQuery('#fv_player_copy_to_clipboard').select();

            } else {
              add_notice( 'error', response.error );

              debug_log( 'Error in fv_player_db_save: ' + response.error );
            }

            el_spinner.hide();
            is_saving = false;

            return;
          }

          // player saved, reset draft status
          is_unsaved = false;
          //is_draft_changed = false;

          current_player_db_id = parseInt(response.id);
          if( current_player_db_id > 0 ) {
            let shortcode_insert = '[fvplayer id="' + current_player_db_id + '"';

            // we have extra presentation parameters to keep
            if (store_shortcode_args) {
              // if this was a non-DB player and is being converted into a DB-player,
              // remove all parameters to keep that will go into the DB
              var args_to_keep = {};

              for (var i in store_shortcode_args) {
                if (always_keep_shortcode_args[ i ]) {
                  args_to_keep[ i ] = store_shortcode_args[ i ];
                }
              }

              store_shortcode_args = args_to_keep;

              shortcode_insert += add_shortcode_args( store_shortcode_args );

            } else if (always_keep_shortcode_args && player_was_non_db) {
              // we have extra parameters to keep that are DB-incompatible
              shortcode_insert += add_shortcode_args( always_keep_shortcode_args );

            } else {
              // simple DB shortcode, no extra presentation parameters
              insert_shortcode('[fvplayer id="' + current_player_db_id + '"]');
            }

            shortcode_insert += ']';

            insert_shortcode( shortcode_insert );

            if( fv_player_editor.clientId ) {
              wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(fv_player_editor.clientId, { shortcodeContent: shortcode_insert, player_id: current_player_db_id });
            }

            jQuery(".fv-wordpress-flowplayer-button").fv_player_box.close();

          } else {
            let json_export_data = jQuery('<div/>').text(JSON.stringify(ajax_data)).html();

            let overlay = overlay_show('error_saving');
            overlay.find('textarea').val( $('<div/>').text(json_export_data).html() );

            jQuery('#fv_player_copy_to_clipboard').select();
          }
        },
        error: function( jqXHR, textStatus, errorThrown ) {
          overlay_show('message', 'An unexpected error has occurred while saving player: <code>' + errorThrown + '</code><br /><br />Please try again');

          debug_log( 'HTTP Error in fv_player_db_save: ' + errorThrown + ': ' + jqXHR.responseText );
        }
      });

      return;

    }

    function fv_wp_flowplayer_dialog_resize() {
      debug_log('Deprecated fv_wp_flowplayer_dialog_resize() call.');
    }

    function get_pretty_aspect_ratio( video ) {
      let w, h, dividend, divisor, remainder;

      // Sanitize input
      video.width = parseInt( video.width );
      video.height = parseInt( video.height );
      video.aspect_ratio = parseFloat( video.aspect_ratio );

      if( video.width && video.height ) {
        w = video.width;
        h = video.height;

      } else if( video.aspect_ratio ) {
        w = 1;
        h = video.aspect_ratio;

      } else {
        return false;
      }

      if( h == w ) {
        return '1:1';

      } else {
        if( h > w ) {
          dividend  = h;
          divisor   = w;
        }

        if( w > h ) {
          dividend   = w;
          divisor    = h;
        }

        let gcd = -1,
          loop_runs = 0;

        while( gcd == -1 ) {
          loop_runs++;

          // Avoid endless loop, what if...
          if( loop_runs > 100 ) {
            gcd = divisor;
            break;
          }

          remainder = dividend % divisor;
          if( remainder == 0 ){
            gcd = divisor;
          } else {
            dividend  = divisor;
            divisor   = remainder;
          }
        }

        return ( w / gcd ) + ':' + ( h / gcd );
      }
    }

    function insert_button_toggle( show ) {

      // Do not show Insert button if player is configured for a set field
      if( show && fv_player_editor_conf.field_selector ) {
        return;
      }

      $('.fv_player_field_insert-button').toggle( show );
    }

    function insert_button_toggle_disabled( disable ) {
      var button = $('.fv_player_field_insert-button');
      if( disable ) {
        button.prop('disabled', true);
      } else {
        button.prop('disabled', false);
      }
    }

    /**
     * Sends new shortcode to post editor
     */
    function insert_shortcode( shortcode ) {

      // do not insert new shortcode if using button on wp-admin -> FV Player
      if( is_fv_player_screen(editor_button_clicked) ) {
        return;
      }

      var field = $(editor_button_clicked).parents('.fv-player-editor-wrapper').find('.fv-player-editor-field'),
        elementor_field = $(editor_button_clicked).closest( '#elementor-controls' ).find( '[data-setting="shortcode"]' ),
        gutenberg = $(editor_button_clicked).parents('.fv-player-gutenberg').find('.fv-player-editor-field'),
        widget = jQuery('#widget-widget_fvplayer-'+widget_id+'-text'),
        custom_field_selector = jQuery(fv_player_editor_conf.field_selector);

      // Field set by the [fvplayer_editor field="{selector}"]
      if( custom_field_selector.length ){
        custom_field_selector.val( shortcode );

      // is there a Gutenberg field together in wrapper with the button?
      } else if( gutenberg.length ) {

      // is there a plain text field together in wrapper with the button?
      } else if (field.length) {
        field.val(shortcode);
        // Prevents double event triggering in FV Player Custom Video box
        //field.trigger('fv_flowplayer_shortcode_insert', [shortcode]);

      } else if ( elementor_field.length ) {
        elementor_field.val( shortcode ).trigger( 'input' );

        // FV Player in a Widget
      } else if( widget.length ){
        widget.val( shortcode );
        widget.trigger('keyup'); // trigger keyup to make sure Elementor updates the content
        widget.trigger('fv_flowplayer_shortcode_insert', [ shortcode ] );

        // CodeMirror tab
      } else if(instance_code_mirror) {
        editor_content = editor_content.replace(/#fvp_codemirror_placeholder#/, shortcode);
        set_post_editor_content(editor_content);
      } else if (typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length) {
        // editing
        if( editor_content.match(/\[.*?#fvp_placeholder#.*?\]/) ) {
          editor_content = editor_content.replace(/\[.*?#fvp_placeholder#.*?\]/, shortcode);

        //inserting
        } else {
          editor_content = editor_content.replace(/#fvp_placeholder#/, shortcode);
        }
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
    Determines if the button clicked is on wp-admin -> FV Player or wp-admin -> Posts (if using FV Player Video Custom Fields)
    */
    function is_fv_player_screen(button) {
      // TODO: Should use fv_player_editor_conf.is_fv_player_screen || fv_player_editor_conf.is_edit_posts_screen
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
      return (
        typeof( $(button).data('player_id') ) != 'undefined' ||
        typeof( $(button).data('post-id') ) != 'undefined'
      );
    }

    /*
    Determines if the button clicked is Insert/Edit on wp-admin -> Appearance -> Widgets
    */
    function is_fv_player_widgets(button) {
      return button.id.indexOf('widget-widget_fvplayer') > -1;
    }

    function is_live_stream( video ) {
      return !! ( video.src && video.src.match( /\.m3u8|live/ ) );
    }

    /*
    Sets lightbox class once it opens
    */
    function lightbox_open() {
      $("#fv_player_box").addClass("fv-flowplayer-shortcode-editor");
    }

    function map_checkbox_value( $element ) {
      var input_type = $element.get(0).type.toLowerCase(),
        is_checked = $element.get(0).checked;

      if ( 'checkbox' == input_type ) {
          return is_checked ? 'true' : '';
      }
    }

    function map_dropdown_value( dropdown_element ) {
      var $valueLessOptions = dropdown_element.find('option:not([value])'),
        value = dropdown_element[0].value.toLowerCase(),
        index = dropdown_element[0].selectedIndex;

      // multiselect element
      if(dropdown_element[0].multiple) {
        var selected = [],
          options = dropdown_element[0].options,
          opt;

        for (var i=0, iLen=options.length; i<iLen; i++) {
          opt = options[i];

          // take only selected with value
          if (opt.selected && opt.value) {
            selected.push(opt.value);
          }
        }

        return selected.length ? selected.join(',') : '';

      } else if ( $valueLessOptions.length ) { // at least one option is value-less
        // the first one is always default and should be sent as ''
        return index === 0 ? '' : value;

      } else {
        // normal dropdown - all options have a value, return this.value (option's own value)
        return value;
      }
    }

    function map_input_type( $el ) {
      var input_type = $el.get(0).type.toLowerCase();

      if ( 'SELECT' == $el.get(0).nodeName ) {
        input_type = 'select';
      }

      return input_type;
    }

    function map_input_value( $el ) {
      let input_type = map_input_type( $el ),
        value = $el.val();

      // check for a select without any option values, in which case we'll use their text
      if ( 'select' == input_type ) {
        value = map_dropdown_value( $el );

      } else if ( 'checkbox' == input_type ) {
        value = map_checkbox_value( $el );
      }

      return value;
    }

    function map_names_to_editor_fields(name) {
      var fieldMap = {
        'liststyle': 'playlist',
        'preroll': 'video_ads',
        'postroll': 'video_ads_post'
      };

      return 'fv_wp_flowplayer_field_' + (fieldMap[name] ? fieldMap[name] : name);
    }

    function playlist_add_toggle_function( hero_on ) {
      let button = $( '.playlist_add' );
      button.html( button.data( hero_on ? 'alt-html' : 'html' ) );
    }

    function playlist_buttons_disable( reason ) {
      if( reason ) {
        $('.playlist_add, .playlist_edit').addClass('disabled').attr('title', reason);
      } else {
        $('.playlist_add, .playlist_edit').removeClass('disabled');
      }
    }

    function playlist_buttons_toggle( show ) {
      $('.playlist_add, .playlist_edit').css( 'display', show ? 'inline-block' : 'none' );
    }

    function playlist_hero_hide() {
      playlist_add_toggle_function();

      $( '#fv-player-editor-playlist .fv-player-editor-playlist-item:last').show();
      $( '#playlist-hero' ).hide();

      // Reset playlist item count on the hero input box
      $( '#playlist-hero [name=hero-src]').data( 'new-item-index', false ).val('');

      // Reset playlist item count on the hero Media Library button
      $( '#playlist-hero button').data( 'new-item-index', false );

      is_playlist_hero_editing = false;
    }

    function playlist_hero_show() {
      is_playlist_hero_editing = true;

      playlist_add_toggle_function( true );

      $( '#playlist-hero' ).show();
    }

    // Clicking anywhere else should hide the prompt, the button to add playlist item is excluded too
    $( $el_editor ).on( 'click', function( e ) {
      if( is_playlist_hero_editing && $( e.target ).closest( '#playlist-hero').length == 0 && !$( e.target ).hasClass( 'playlist_add') ) {
        playlist_hero_hide();
      }
    });

    /**
     * Adds playlist item
     *
     * @param {object|string} input       Object from FV Player database or legacy shortcode argument
     *                                    text which was a comma separated list of URLs
     * @param {boolean}       skip_dom_add  If true, the item will not be added to the DOM and you will need to add it manually.
     *
     * @return {jQuery}                   New playlist item.
    */
    function playlist_item_add( input, skip_dom_add ) {
      var new_playlist_item = $(template_playlist_item);
      $('.fv-player-tab-playlist #fv-player-editor-playlist').append(new_playlist_item);

      var ids = jQuery('.fv-player-tab-playlist [data-index]').map(function() {
        return parseInt(jQuery(this).attr('data-index'), 10);
      }).get();
      var newIndex = Math.max(Math.max.apply(Math, ids) + 1,0);

      var current = jQuery('.fv-player-tab-playlist #fv-player-editor-playlist .fv-player-editor-playlist-item').last();
      current.attr('data-index', newIndex);
      current.find('.fvp_item_video-filename').text( 'Video ' + (newIndex + 1) );
      current.find('.fvp_item_video-duration').text( '' );

      let new_item = jQuery( template_video );
      new_item.hide().attr('data-index', newIndex);

      let new_item_subtitles = jQuery( template_subtitles_tab );
      new_item_subtitles.hide().attr('data-index', newIndex);

      // Since we are using the virtual DOM, we need to call the change event for the checkboxes manually
      if ( skip_dom_add ) {
        new_item.on( 'change', '.components-form-toggle input[type=checkbox]', function() {
          var wrap = $(this).closest('.components-form-toggle'),
            checked = $(this).prop('checked'),
            name = $(this).attr('name').replace( /fv_wp_flowplayer_field_/, '' );
  
            checkbox_toggle_worker(wrap, name, checked);
        });

        // Since we are using the virtual DOM, we need to call the change event for the inputs and selects manually
        new_item.on('change', '.components-text-control__input, .components-select-control__input', function() {
          var input = jQuery(this),
            parent = input.closest('.fv-player-editor-children-wrap'),
            name = input.attr('name').replace( /fv_wp_flowplayer_field_/, '' ),
            wrap = input.parents( '.fv-player-editor-field-wrap-' + name );
  
          text_and_select_worker( input, parent, name, wrap );
        });
      }

      // processing database input
      if( typeof(input) == 'object' ) {
        var objVid = input;

        new_item.attr('data-id_video', objVid.id);
        get_field('src',new_item).val(objVid.src);
        get_field('src1',new_item).val(objVid.src1);
        get_field('src2',new_item).val(objVid.src2);

        get_field('mobile',new_item).val(objVid.mobile).trigger( 'change' );

        get_field('rtmp',new_item).val(objVid.rtmp);
        get_field('rtmp_path',new_item).val(objVid.rtmp_path);

        get_field('title',new_item).val(objVid.title).trigger( 'change' );
        get_field('title_hide',new_item).prop( 'checked', objVid.title_hide ).trigger( 'change' );
        get_field('splash',new_item).val(objVid.splash);
        get_field('splash_text',new_item).val(objVid.splash_text).trigger( 'change' );
        get_field('splash_attachment_id',new_item).val(objVid.splash_attachment_id);

        get_field('start',new_item).val(objVid.start).trigger( 'change' );
        get_field('end',new_item).val(objVid.end).trigger( 'change' );

        get_field('toggle_advanced_settings',new_item).prop('checked', objVid.toggle_advanced_settings).trigger('change');

        jQuery(objVid.meta).each( function(k,v) {
          Object.keys( window.fv_player_editor_fields ).forEach( function( field_name ) {
            if ( 'video_meta' == window.fv_player_editor_fields[ field_name].store ) {
              if ( v.meta_key == field_name ) {
                get_field( field_name, new_item ).val( v.meta_value ).attr('data-id',v.id).trigger( 'change' );
              }
            }
          } );

          if( v.meta_key == 'audio' ) get_field('audio',new_item).prop('checked',v.meta_value).attr('data-id',v.id);
        });

      } else {
        new_playlist_item.find('.fvp_item_video-thumbnail').addClass( 'no-img' );
      }

      // fire up an update event if we're adding an empty template, which means this function is called
      // outside of the player meta loading and we should inform plugins that they need to add their own
      // video tab content
      if (!input) {
        $doc.trigger('fv-player-playlist-item-add');
      }

      hide_inputs();

      if ( ! skip_dom_add ) {
        jQuery('.fv-player-tab-video-files').append( new_item );
        jQuery('.fv-player-tab-subtitles').append( new_item_subtitles );
      }

      return { video_tab: new_item, subtitles_tab: new_item_subtitles };
    }

    /*
    Show a certain playlist item, it's Video and Subtitles tab
    */
    function playlist_item_show( new_index ) {
      let previous_index = current_video_to_edit;

      set_current_video_to_edit( new_index );

      editing_video_details = true;

      $el_editor
        .removeClass( 'is-playlist-active' )
        .addClass( 'is-singular-active' );

      jQuery('.fv-player-tabs-header .nav-tab').attr('style',false);

      $doc.trigger('fv_flowplayer_shortcode_item_switch', [ new_index ] );

      $('a[data-tab=fv-player-tab-video-files]').trigger('click');

      get_tabs('video-files').hide();
      var video_tab = get_tab(new_index,'video-files').show();

      if( video_tab.attr('data-id_video') ) {
        current_video_db_id = video_tab.attr('data-id_video');
        debug_log('current_video_db_id: '+current_video_db_id);
      }

      get_tabs('subtitles').hide();

      var subtitles_tab = get_tab(new_index,'subtitles').show();

      if($('.fv-player-tab-playlist [data-index]').length > 1){
        $('.fv-player-playlist-item-title').html('Playlist item no. ' + ++new_index);
        $('.playlist_edit').html($('.playlist_edit').data('edit'));

        $el_editor.addClass( 'is-playlist' );

      }else{
        $('.playlist_edit').html($('.playlist_edit').data('create'));

        $el_editor.addClass( 'is-singular' );
      }

      // As Flowplayer only lets us set RTMP server for the first video in playlist, prefill it for this new item as well
      if(new_index > 1){
        get_field('rtmp',video_tab).val( get_field('rtmp',$('.fv-player-tab-video-files table').eq(0) ).val()).attr('readonly',true);
      }

      $('.fv_wp_flowplayer_field_subtitles_lang, .subtitle_language_add_link').attr('style',false);

      tabs_refresh();

      if( previous_index != current_video_to_edit && get_playlist_items_count() > 1 ) {
        reload_preview( current_video_to_edit );
      }

      hide_inputs();

      $doc.trigger('fv-player-editor-video-opened', [ new_index ] );

      /**
       * Upgrade text inputs to select boxes for matching fields
       */
      window.fv_player_editor_fields_with_language.forEach( function( field_name ) {
        let fields_with_languages = $( '.fv_wp_flowplayer_field_' + field_name + '_lang', subtitles_tab );

        fields_with_languages.each( function() {
          let $element = $( this ),
            value = $element.val();

          $element.replaceWith( '<select class="fv_wp_flowplayer_field_' + field_name + '_lang" name="fv_wp_flowplayer_field_' + field_name + '_lang">' +
            '<option value="">' + fv_player_editor_conf.field_languages_default + '</option>' +
            Object.keys( fv_player_editor_conf.field_languages ).map( function( lang_code ) {
              let html = '<option value="' + lang_code.toLowerCase() + '"';
              if ( lang_code.toLowerCase() == value.toLowerCase() ) {
                html += ' selected';
              }
              html += '>' + fv_player_editor_conf.field_languages[ lang_code ] + ' (' + lang_code + ')</option>'
              return html;
            }).join( '' ) +
            '</select>'
          );
        } );
      } );
    }

    /**
     *  Recalculate the data-index values for playlist items
     */
    function playlist_index() {
      $doc.trigger('fv-player-editor-initial-indexing');

      $('.fv-player-tab-playlist #fv-player-editor-playlist .fv-player-editor-playlist-item').each( index );

      $('.fv-player-tab-video-files [data-playlist-item]').each( index );

      $('.fv-player-tab.fv-player-tab-subtitles [data-playlist-item]').each( index );

      function index() {
        /**
         * The element already had the data-index defined before, so it seems
         * we need to also set it with data(), since jQuery caches the first
         * .data() retrieval in the internal data object.
         *
         * We cannot just use .data() as we use CSS selectors for elements in JS.
         */
        $(this).attr('data-index', $(this).index() ).data( 'index', $(this).index() );
      }

    }

    // fills playlist editor table from individual video items
    function playlist_refresh() {

      // fills playlist editor table from individual video items
      let video_files = jQuery('.fv-player-tab-video-files [data-playlist-item]');
      video_files.each( function( k, v ) {
        let current = jQuery(v),
          video = get_playlist_video_object( k ),
          currentUrl = get_field("src",current).val();

        if (!currentUrl.length) {
          currentUrl = 'Video ' + (k + 1);
        }

        let playlist_row = jQuery('.fv-player-tab-playlist #fv-player-editor-playlist .fv-player-editor-playlist-item').eq( current.data('index') );

        let video_preview = video.splash_display ? video.splash_display : get_field("splash", current).val(),
          playlist_img = jQuery('<img />')
            .attr('width', 120 )
            .attr('src', video_preview );

        playlist_row.find('.fvp_item_video-thumbnail')
          .html( video_preview.length ? playlist_img : '' )
          .toggleClass( 'no-img', !video_preview.length );

        let video_name = decodeURIComponent(currentUrl).split("/").pop();
        video_name = video_name.replace(/\+/g,' ');
        video_name = video_name.replace(/watch\?v=/,'YouTube: ');

        let playlist_title = playlist_row.find('.fvp_item_video-filename');
        playlist_title.text( video_name );

        // do not put in title if it's loading
        if (!playlist_title.hasClass('fv-player-shortcode-editor-small-spinner')) {
          let title = get_field("title",current).val();
          if( title ) {
            playlist_title.text( title );
          }
        }

        playlist_row.find('.fvp_item_video-duration').text( seconds_to_hms( video.duration ) );
      });
    }

    /**
    * Displays playlist editor
    * keywords: show playlist
    */
    function playlist_show() {
      current_video_db_id = 0;

      editing_video_details = false;

      $el_editor
        .addClass( 'is-playlist-active' )
        .removeClass( 'is-playlist' )
        .removeClass( 'is-singular' )
        .removeClass( 'is-singular-active' );

      // show all the tabs previously hidden
      jQuery('.fv-player-tabs-header .nav-tab').attr('style',false);
      jQuery('a[data-tab=fv-player-tab-playlist]').trigger('click');

      set_current_video_to_edit( -1 );

      playlist_index();

      playlist_refresh();

      playlist_index();

      jQuery('.fv-player-tab-playlist').show();

      tabs_refresh();

      show_end_of_video_actions();

      $doc.trigger('fv-player-editor-video-opened', [ current_video_to_edit ] );

      return false;
    }

    function reload_preview( video_index ) {
      if(
        video_index > -1 && ( !current_player_object.videos || !current_player_object.videos[video_index] ) ||
        video_index== -1 && !current_player_object.videos
      ) {
        reset_preview();
        return;
      }

      el_spinner.show();

      debug_log('Running fv_player_db_load Ajax for preview.');

      let time_start = performance.now();

      // load player data and reload preview of the full player
      // when we go back from editing a single video in a playlist
      fv_player_shortcode_editor_ajax = jQuery.post( ajaxurl + '?fv_player_db_load=' + current_player_db_id, {
        action : 'fv_player_db_load',
        nonce : fv_player_editor_conf.db_load_nonce,
        playerID :  current_player_db_id,
        current_video_to_edit: get_playlist_items_count() > 1 ? video_index : -1,
      }, function(response) {

        debug_log('Finished fv_player_db_load Ajax in ' + debug_time( time_start ) + '.',response);

        if ( response.html ) {
          // auto-refresh preview
          el_preview_target.html( response.html )
          $doc.trigger('fvp-preview-complete');
        }
      }).fail(function( jqXHR, textStatus, errorThrown ) {
        if ( jqXHR.status == 404) {
          overlay_show('message', 'The requested player #' + current_player_db_id + ' could not be found.');
        } else {
          overlay_show('message', 'An unexpected error has occurred while loading player #' + current_player_db_id + ': <code>' + errorThrown + '</code><br /><br />Please try again.');
        }
      }).always(function() {
        el_spinner.hide();
      });
    }

    function remove_notices() {
      $el_notices.html('');
    }

    function reset_editor_ids() {
      debug_log( 'reset_editor_ids' );

      current_player_db_id = 0;
      current_player_object = false;
      current_video_db_id = 0;

      current_post_id = 0;
      current_post_meta_key = 0;
    }

    function reset_preview() {
      $el_preview.attr('class','preview-no');

      if( fv_player_editor_conf.frontend ) {
        $el_editor.addClass('is-intro');
      }
    }

    function seconds_to_hms( seconds ) {
      try {
        var duration_hms = new Date(seconds * 1000).toISOString().substr(11, 8);
        return duration_hms.replace( /^00:/, '' );
      } catch(e) {
        return '';
      }
    }

    function set_current_video_to_edit( index ) {
      current_video_to_edit = parseInt( index );
    }

    function set_control_fields( ajax_data ) {
      // add current video that we're editing into the save data
      ajax_data['current_video_to_edit'] = get_playlist_items_count() > 1 ? current_video_to_edit : -1;

      // set current editor tab for ajax_data
      ajax_data['current_editor_tab'] = current_editor_tab;

      // Used for Video Custom Fields
      if( current_post_id ) {
        ajax_data['current_post_id'] = current_post_id;
      }
      if( current_post_meta_key ) {
        ajax_data['current_post_meta_key'] = current_post_meta_key;
      }

      // Used in FV Player Pay Per View to store the post ID for the product
      ajax_data['editor_post_id'] = fv_flowplayer_set_post_thumbnail_id;

      return ajax_data;
    }

    // used several times below, so it's in a function
    function set_editor_field( key, real_val, id, video_tab ) {
      var real_key = map_names_to_editor_fields(key);

      if ( video_tab ) {
        $element = jQuery( '[name="' + real_key + '"]', video_tab );
      } else {
        $element = jQuery( '[name="' + real_key + '"]' );
      }

      // special processing for end video actions
      if (real_key == 'fv_wp_flowplayer_field_end_action_value') {
        show_end_actions( false, real_val );
        return;
      } else if (['fv_wp_flowplayer_field_email_list', 'fv_wp_flowplayer_field_popup_id', 'fv_wp_flowplayer_field_redirect'].indexOf(real_key) > -1) {
        // ignore the original fields, if we still use old DB values
        return;
      }

      // player and video IDs wouldn't have corresponding fields
      if ( $element && $element.length ) {
        // dropdowns could have capitalized values
        if ($element.get(0).nodeName == 'SELECT') {
          if ($element.find('option[value="' + real_val + '"]').length) {
            $element.val(real_val);
          } else {
            // try capitalized
            var caps = real_val.charAt(0).toUpperCase() + real_val.slice(1);
            $element.find('option').each(function() {
              if (this.text == caps) {
                $element.val(caps);
              }
            });
          }
        } else if ($element.get(0).nodeName == 'INPUT' && $element.get(0).type.toLowerCase() == 'checkbox') {
          let checked = real_val === '1' || real_val === 'on' || real_val === 'true' || real_val === 'yes';

          // Is the value empty and is there default?
          if( real_val == '' && typeof(window.fv_player_editor_defaults[key]) != "undefined" ) {
            return;
          }
          $element.prop( 'checked', checked ).trigger('change');

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

    function set_post_editor_content( html ) {
      if ( editor_button_clicked.className.indexOf('fv-player-editor-button') > -1 || is_fv_player_screen(editor_button_clicked) || is_fv_player_widgets(editor_button_clicked) || $(editor_button_clicked).parents('.fv-player-gutenberg').find('.fv-player-editor-field').length) {
        return;
      }

      if ( instance_code_mirror ) {
        // set code mirror content
        instance_code_mirror.getDoc().setValue( html );
      } else if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length ){
        jQuery('#content:not([aria-hidden=true])').val(html);

      } else if ( typeof(instance_fp_wysiwyg) != 'undefined' && ( instance_tinymce == undefined || typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor.isHidden() ) ) {
        instance_fp_wysiwyg.SetHTML( html );
      }
      else if ( instance_tinymce ) { // instance_tinymce will be null if we're updating custom meta box
        instance_tinymce.setContent( html );
      }
    }

    /**
     *  Hide any overlays
     */
    function overlay_hide() {
      $('.fv-player-editor-overlay').hide();
      return false;
    }

    /**
     *  Show a certain kind of overlay
     */
    function overlay_show( type, message ) {
      overlay_hide();
      var overlayDiv = $('#fv-player-editor-'+type+'-overlay');
      overlayDiv.show();

      if( typeof(message) != 'undefined' ) {
        overlayDiv.find('p').html( message );
      }
      return overlayDiv;
    }

    /**
     * Populate content of the Embeds tab and show it if there is any content to be set
     *
     * @param string  html  The OL > LI list of posts which contain the same player.
     */
    function set_embeds( html ) {
      // ugly way of making sure that tab stays hidden as otherwise playlist_show() would reveal it
      $('[data-tab=fv-player-tab-embeds]').toggleClass('always-hide',!html);
      get_tabs('embeds').find('td').html(html);
    }

    function shortcode_arg_split(sInput) {
      sInput[1] = sInput[1].replace(/\\;/gi, '<!--FV Flowplayer Caption Separator-->').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
      var aInput = sInput[1].split(';');
      for( var i in aInput ){
        aInput[i] = aInput[i].replace(/\\"/gi, '"');
        aInput[i] = aInput[i].replace(/\\<!--FV Flowplayer Caption Separator-->/gi, ';');
        aInput[i] = aInput[i].replace(/<!--FV Flowplayer Caption Separator-->/gi, ';');
      }
      return aInput;
    }

    /*
    Also used by FV Player Pro and FV Player Pay Per View
    */
    function shortcode_parse_arg( sShortcode, sArg, bHTML, sCallback ) {
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

      if( aOutput && bHTML ) {
        aOutput[1] = aOutput[1].replace(/\\"/g, '"').replace(/\\(\[|])/g, '$1');
      }

      if( aOutput && sCallback ) {
        sCallback(aOutput);
      } else {
       return aOutput;
      }
    }

    function shortcode_share_parse_arg( args ) {
      var field = fv_player_editor.get_field("share")[0];
      if (args[1] == 'yes' ) {
        field.selectedIndex = 1;
      } else if (args[1] == 'no' ) {
        field.selectedIndex = 2;
      } else {
        field.selectedIndex = 3;
        args = args[1].split(';');
        if( typeof(args[0]) == "string" ) fv_player_editor.get_field('share_url').val(args[0]);
        if( typeof(args[1]) == "string" ) fv_player_editor.get_field('share_title').val(args[1]);
        fv_player_editor.get_field("share_custom").show();
      }
    }

    function shortcode_subtitle_parse_arg( args ) {
      var input = ('shortcode_subtitle_parse_arg',args);
      var aLang = input[0].match(/subtitles_([a-z][a-z])/);
      subtitle_language_add( input[1], aLang[1] );
    }

    function subtitle_language_add( sInput, sLang ) {
      language_add( 'subtitles', sInput, sLang );
    }

    /*
     * Adds another language for a field
     *
     * @param {string}  field         Field name
     * @param {string}  sInput        Value to input
     * @param {string}  sLang         Language to use
     * @param {jQuery}  subtitles_tab Video Subtitles tab element
     * @param {int}     video_meta_id Field video meta ID
     */
    function language_add( field, sInput, sLang, subtitles_tab, video_meta_id ) {

      // Used when parsing a legacy shortcode like [fvplayer src="video-1.mp4" subtitles="video-1.vtt"]
      if( typeof( subtitles_tab ) == "undefined" ){
      // Reguired when parsing a legacy shortcode
        if( current_video_to_edit == -1 ) {
          current_video_to_edit = 0;
      }

        subtitles_tab = get_tab( current_video_to_edit, 'subtitles' );;
      }

      var subElement = false;

      // If we are loading data, do we have an empty subtitle field?
      if( sInput ) {
        // TODO: Function to get last of the language inputs which is not a child
        subElement = $('.fv-player-editor-field-wrap-' + field + ' > .components-base-control > .components-base-control__field:last', subtitles_tab);
        if( subElement.length ) {
          if( get_field( field,subElement).val() ) {
            subElement = false;
          }
        }
      }

      // If we do not have an empty subtitle field, add new
      if( !subElement ) {

        // Get the last inputs and clear the values
        // TODO: Function to get last of the language inputs which is not a child
        subElement = $( $('.fv-player-editor-field-wrap-' + field + ' > .components-base-control > .components-base-control__field:last', subtitles_tab ).prop('outerHTML') );
        subElement.find('[name]').val('');
        subElement.removeAttr('data-id_videometa');

        // Insert the new input after the last exiting input
        // TODO: Function to get last of the language inputs which is not a child
        subElement.insertAfter( $('.fv-player-editor-field-wrap-' + field + ' > .components-base-control > .components-base-control__field:last', subtitles_tab) );

        if( !sInput ) {
          // force user to pick the language by removing the blank value and selecting what's first
          subElement.find('select option[value=""]').remove();
          setTimeout( function() {
            subElement.find('select option').eq(0).prop('selected',true);
          }, 0 );
        }
      }

      if (typeof(video_meta_id) !== 'undefined') {
        subElement.attr('data-id_videometa', video_meta_id);
      }

      let new_field = get_field( field, subElement);
      if( sInput ) {
        new_field.val(sInput).trigger('change');
      }

      show_short_link( new_field );

      if ( sLang ) {
        if( sLang == 'iw' ) sLang = 'he';
        if( sLang == 'in' ) sLang = 'id';
        if( sLang == 'jw' ) sLang = 'jv';
        if( sLang == 'mo' ) sLang = 'ro';
        if( sLang == 'sh' ) sLang = 'sr';

        get_field( field + '_lang',subElement).val(sLang).trigger('change');
      }
    }

    function tabs_refresh() {
      var visibleTabs = 0;
      $el_editor.find('a[data-tab]').removeClass('fv_player_interface_hide');
      $el_editor.find('.fv-player-tabs > .fv-player-tab').each(function(){
        var bHideTab = true
        $(this).find('.fv_player_editor-panel__body, .fv-player-editor-playlist-item').each(function(){
          if( $(this).css('display') === 'block' || $(this).css('display') === 'flex' || $(this).css('display') === 'table' ){
            bHideTab = false;
            return false;
          }
        });
        var tab;
        var data = jQuery(this).attr('class').match(/fv-player-tab-[^ ]*/);
        if(data[0]){
          tab = $el_editor.find('a[data-tab=' + data[0] + ']');
        }

        if( fv_player_editor_conf.tabs && fv_player_editor_conf.tabs == 'none' ) {
          bHideTab = true;
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
        $el_editor.find('.nav-tab').addClass('fv_player_interface_hide');
        $el_editor.find('.nav-tab-wrapper').addClass('fv_player_interface_hide');
      }

      var end_actions_label = $('label[for=fv_wp_flowplayer_field_end_actions]');
      if( $el_editor.hasClass('is-playlist-active')){
        end_actions_label.html( end_actions_label.data('playlist-label') )
      } else {
        end_actions_label.html( end_actions_label.data('single-label') )
      }

    }

    /*
    Click handlers
    */

    /*
    Click on Add Another Language (of Subtitles)
    */
    $doc.on('click', '.add_language', function() {
      language_add( $( this ).data( 'field_name' ), false );
      return false;
    });

    /*
    Click on "Remove" to remove a language for a field
    */
    $doc.on('click', '.remove_language', function() {

      let button = $( this ),
        field_name = button.data( 'field_name' ),
        field_label = button.data( 'field_label' ),
        field = jQuery(this).closest( '.components-base-control__field' ),
        id = field.attr('data-id_videometa')

      if( !confirm('Would you like to remove this ' + field_label + '?') ) {
        return false;
      }

      if (id) {
        deleted_video_meta.push(id);
      }

      // TODO: How to get the field wrap and how to get its langauge versions?
      let language_fields = get_field( field_name, true )
        .closest('.components-base-control')
        .find( '.components-base-control__field' );

      // if it's not the last subtitle, remove it completely
      if( language_fields.length > 1){
        field.remove();

        // otherwise just empty the inputs to let user add new subtitles
      } else {
        field.find('[name]').val('');
        field.removeAttr('data-id_videometa');
      }

      $doc.trigger('fv_player_editor_language_delete');

      return false;
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

      debug_log('Running fv_player_db_import Ajax.');

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
            }).fail(function() {
            jQuery('.fv-wordpress-flowplayer-button').fv_player_box.close();
          });

        } else {
          // TODO: Fix, it won't show!
          fv_player_editor.overlay_notice( button, response, 'error' );

        }
      }).fail(function() {
        fv_player_editor.overlay_notice( button, 'Unknown error!', 'error' );

      });

      return false;
    });

    /*
    Editor short links
    */
    var shortened_field_edited = false, // store field that if being edited
      shortened_field_button = false; // store Media Library button for the field

    // click on shortened preveew, show original input and hide preview
    $doc.on('click', '.fv_player_editor_url_shortened', function() {
      var preview = jQuery(this),
        wrap = preview.closest('.components-base-control__field');

      shortened_field_edited = preview.next('.fv_player_editor_url_field')
      shortened_field_button = wrap.find('.add_media')

      preview.addClass('fv_player_interface_hide');
      preview.attr('title', '');
      shortened_field_button.show();
      shortened_field_edited.removeClass('fv_player_interface_hide');
      shortened_field_edited.focus();
    });

    // Clicking outside of the edited field should close the field editing
    $el_editor.on( 'click', function(e) {
      if( shortened_field_edited ) {
        if(
          !$(e.target).hasClass('fv_player_editor_url_shortened') &&
          !$(e.target).parent().hasClass('fv_player_editor_url_shortened') &&
          !shortened_field_edited.is(e.target) &&
          !shortened_field_button.is(e.target)
        ) {
          show_short_link( shortened_field_edited );
        }
      }
    });

    // focus lost from input
    $doc.on('change', '.fv_player_editor_url_field', function() {
      show_short_link(jQuery(this));
    });

    $doc.on('fv_player_editor_finished fv_flowplayer_shortcode_item_switch', function() {
      jQuery('.fv_player_editor_url_field').each(function(index, item) {
        show_short_link(jQuery(item))
      });
    });

    function show_short_link(field) {
      var value = field.val().trim(),
        preview = field.prev('.fv_player_editor_url_shortened'),
        button = field.closest( '.components-base-control__field').find('.add_media'); // Media Library Button

      shortened_field_edited = false;
      shortened_field_button = false;

      if( !value ) { // no value, hide preview
        field.removeClass('fv_player_interface_hide');
        preview.addClass('fv_player_interface_hide');
        button.show();

      } else {
        field.addClass('fv_player_interface_hide');
        preview.removeClass('fv_player_interface_hide');
        preview.attr('title', value );
        preview.find('.link-preview').html( shorten_original_link( value ) ); // shorten preview link
        button.hide();
      }
    }

    function shorten_original_link(original_link) {
      if(!original_link) return original_link;

      let parts = original_link.trim().split('/'),
        is_hls = parts[parts.length - 1].indexOf('.m3u8') != -1,
        new_parts = [];

      $(parts).each( function(k,v) {
        let c = '';
        // filename
        if( k == parts.length - 1 ) {
          // if it's HLS hilight the extension only
          if( is_hls ) {
            let filename_parts = v.split('.');
            if( filename_parts.length == 2 ) {
              new_parts.push( '<span class="path">'+filename_parts[0]+'.</span><span>'+filename_parts[1]+'</span>' );
              return true;
            }
          }

        // HLS filename - use the directory name
        } else if( is_hls && k == parts.length - 2 ) {

        // domain
        } else if ( k == 2 ) {
          // hilight bucket name for AWS
          let domain_parts = v.split('.');
          if( domain_parts.length > 1 && domain_parts[1].indexOf('s3') == 0 ) {
            let bucket_name = domain_parts[0];
            delete(domain_parts[0])
            new_parts.push( '<span>'+bucket_name+'</span><span class="path">'+domain_parts.join('.')+'</span>' );
            return true;
          }
        } else {
          c = ' class="path"';
        }

        new_parts.push( '<span'+c+'>'+v+'</span>' );
      });

      return new_parts.join('<span class="sep">/</span>');
    }

    /*
    End of video action
    */
    $doc.on('change', '#fv_wp_flowplayer_field_end_actions', function() {
      var selected = this.value;

      jQuery('.fv-player-editor-field-wrap-redirect').addClass('fv_player_interface_hide');
      jQuery('.fv-player-editor-field-wrap-popup_id').addClass('fv_player_interface_hide');
      jQuery('.fv-player-editor-field-wrap-email_list').addClass('fv_player_interface_hide');

      switch (selected) {
        case 'redirect':
          jQuery('.fv-player-editor-field-wrap-redirect').removeClass('fv_player_interface_hide');
          break;

        case 'popup':
          jQuery('.fv-player-editor-field-wrap-popup_id').removeClass('fv_player_interface_hide');
          break;

        case 'email_list':
          jQuery('.fv-player-editor-field-wrap-email_list').removeClass('fv_player_interface_hide');
          break;

        default:
          break;
      }

    });

    /*
    Extra fields to reveal when using a HLS or MPD stream
    */
    $doc.on('fv_flowplayer_shortcode_item_switch', function(e, index) {
      show_video_details(index);
      show_stream_fields_worker(index);
      show_rtmp_fields();
      show_end_of_video_actions();
      show_playlist_not_supported(index);
    });

    $doc.on('fv_flowplayer_shortcode_new', function() {
      show_video_details(0);
      show_stream_fields_worker(0);
      show_rtmp_fields();
      show_end_of_video_actions();
      show_playlist_not_supported();
    });

    function show_end_of_video_actions() {
      var player_object = get_current_player_object(),
        picked_action = player_object.end_actions,
        action_value = player_object.end_action_value;

      select_end_of_video_action(picked_action, action_value);
    }

    function select_end_of_video_action(action, value) {
      if( action == 'redirect' ) {
        jQuery('.fv-player-editor-field-wrap-redirect').removeClass('fv_player_interface_hide');
        get_field('redirect', false).val(value);
      } else if ( action == 'popup' ) {
        jQuery('.fv-player-editor-field-wrap-popup_id').removeClass('fv_player_interface_hide');
        get_field('popup', false).find('[value="'+value+'"]').prop('selected', true);
      } else if ( action == 'email_list' ) {
        jQuery('.fv-player-editor-field-wrap-email_list').removeClass('fv_player_interface_hide');
        get_field('email_list', false).find('[value="'+value+'"]').prop('selected', true);
      }
    }

    function show_playlist_not_supported( index = 0 ) {
      let video = get_playlist_video_object( index ),
        result = {
          'supported': true
        }

      if ( ! video.src ) {
        return;
      }

      if (
        video.src.indexOf('//vimeo.com') > -1 ||
        video.src.indexOf('//vimeopro.com') > -1
      ) {
        result.supported = false;
      }

      // fire up a JS event for the FV Player Pro to catch,
      // so it can check the URL and make sure we don't show
      // a warning message for PRO-supported video types
      $doc.trigger('fv-player-editor-src-change', [ video.src, result, this ]);

      if( result.supported ) {
        playlist_buttons_disable(false);

      } else {
        // Disable the playlist editing buttons if the video type is not supported in playlists
        playlist_buttons_disable('FV Player Pro required for playlists with this video type');

      }
    }

    function show_rtmp_fields() {
      var video_object = get_current_video_object(),
        is_enabled = video_object.rtmp || video_object.rtmp_path

      get_field( 'rtmp_show', true )
        .prop('checked', is_enabled)
        .trigger('change');

      if( is_enabled ) {
        get_field( 'toggle_advanced_settings', true )
          .prop('checked', true)
          .trigger('change');
      }
    }

    function show_stream_fields_worker( index = 0 ) {
      var encrypted = get_current_player_object() ? get_playlist_video_meta_value( 'encrypted', index ) : false;

      // hlskey
      var hlskey = get_field('hls_hlskey', true);
      if( encrypted || hlskey.val() ) {
        hlskey.closest('.fv_player_interface_hide').show();
      } else {
        hlskey.closest('.fv_player_interface_hide').hide();
      }

      // get live from video object
      var live_field = get_field('live', true),
        live_value   = get_current_video_object() ? get_current_video_object().live : false,
        error        = get_playlist_video_meta_value( 'error', index ),
        // Show the stream settings if it's not found and it's HLS stream
        // as it often returns 404 error if it's not live at the moment
        stream_fields_visible = !! ( error && is_live_stream( get_current_video_object() ) );

      live_field.prop('checked', !!live_value);
      live_field.closest('.fv_player_interface_hide').toggle( !!live_value || stream_fields_visible );

      checkbox_toggle_worker( jQuery(live_field).parent('.components-form-toggle'), 'live', !!live_value );

      jQuery.each( [ 'audio', 'dvr' ], function(k,v) {

        var field = get_field(v, true),
          meta = get_current_player_object() ? get_playlist_video_meta_value( v, index ) : false,
          force_visible = stream_fields_visible,
          is_hls = get_current_video_object().src && get_current_video_object().src.match( /\.m3u8/ );

        // Show Audio track checkbox if we have HLS with unknown dimensions
        if ( 'audio' === v && is_hls && get_current_video_object().width == 0 && get_current_video_object().height == 0 ) {
          force_visible = true;
        }

        field.prop('checked', !!meta);
        field.closest('.fv_player_interface_hide').toggle( !!meta || force_visible );

        checkbox_toggle_worker( jQuery(field).parent('.components-form-toggle'), v, !!meta );
      });

    }

    function show_video_details( index = 0 ) {
      var video_tab = get_tab(index,'video-files'),
        video = get_playlist_video_object( index ),
        error = get_playlist_video_meta_value( 'error', index )

      var video_info = get_field('video_info', video_tab).find('ul'),
        show = false;

      video_info.html('');

      if( video.duration > 0 ) {
        video_info.append( '<li title="Duration">' + seconds_to_hms(video.duration) + '</li>' );
        show = true;
      }
      if( video.width > 0 && video.height > 0 ) {
        video_info.append( '<li title="Video dimension in pixels">' + video.width  + 'x' + video.height + '</li>' );
        show = true;
      }
      if( video.aspect_ratio ) {
        video_info.append( '<li title="Aspect ratio">' + get_pretty_aspect_ratio(video) + '</li>' );
        show = true;
      }
      if( error ) {
        video_info.append( '<li class="error">' + error  + '</li>' );
        show = true;

        if ( is_live_stream( video ) ) {
          video_info.append( '<li>FV Player was not able to determine the stream type, please check what applies below.</li>' );
          video_info.append( '<li>This error is normal for some of the HLS live streams if they are not currently streaming.</li>' );
        }
      }

      if( show ) {
        video_info.closest('.fv_player_interface_hide').removeClass('fv_player_interface_hide');
      }

    }

    function checkbox_toggle_worker( wrap, name, checked ) {

      var compare = checked ? 1 : 0;

      wrap.toggleClass( 'is-checked', checked );

      // If the checkox is checked its parents must be visible
      if( checked ) {
        wrap
          .closest('.fv-player-editor-children-wrap')
          .removeClass('fv_player_interface_hide');
      }

      wrap.toggleClass( 'is-default', !!window.fv_player_editor_defaults[name] );

      if( window.fv_player_editor_dependencies[name] ) {
        jQuery.each( window.fv_player_editor_dependencies[name], function(value,inputs) {

          jQuery.each( inputs, function(k,input_name) {

            // We get the last matching field as if we are loading a playlist, we might have multiple fields
            // TODO: Do this more sensibly, like when switching playlist items
            let field_wrap = $('.fv-player-editor-field-wrap-' + input_name + ':last' );

            // TODO: What should be saved when it's enabled?
            field_wrap.toggleClass( 'is-visible-dependency', value == compare );
            field_wrap.toggleClass( 'is-hidden-dependency', value != compare );
          });
        });
      }

      wrap.closest( '.fv-player-editor-children-wrap' ).find( '.fv-player-editor-field-children-'+name ).toggle( checked );
    }

    function text_and_select_worker( input, parent, name, wrap ) {
      // Reveal the input if it has value even if it's not enabled in Post Interface options
      if ( input.val() ) {
        // Only if it's not field with shortening
        if ( input.prev( '.fv_player_editor_url_shortened' ).length === 0 ) {
          input.closest( '.fv_player_interface_hide' ).show();
        }
        wrap.show();
      }

      // Show children inputs if input has value
      parent.find('.fv-player-editor-field-children-' + name ).toggle( !! input.val() );
  }

    function show_end_actions( e, value ) {
      // redirect, popup and email_list
      var type = jQuery('#fv_wp_flowplayer_field_end_actions').val();

      jQuery('.fv_player_actions_end-toggle').hide().find('[name]').val('');

      // The field id is different for popup
      if( type == 'popup' ) {
        type = 'popup_id';
      }

      var field = jQuery('#fv_wp_flowplayer_field_' + type);
      field.parents('tr').show();
      if( value ) {
        field.val( value ).trigger( 'change' );
      }
    }

    function init_saved_player_fields() {
      deleted_videos = [];
      deleted_video_meta = [];
      deleted_player_meta = [];
    }

    function title_editor_close() {
      $('.fv-player-editor-playlist-item .fvp_item_video-title-wrap').show();
      $('.fv-player-editor-playlist-item .fvp_item_video-edit-input').hide();
    }

    /*
    Mark each manually updated title or splash field as such
    */
    $doc.on('input change', '#fv_wp_flowplayer_field_splash, #fv_wp_flowplayer_field_title', function() {

      if ( is_loading ) {
        return;
      }

      let $input;

      // if this element already has data set, don't do any of the selections below
      if (typeof( jQuery(this).data('fv_player_user_updated') ) != 'undefined') {
        return;
      }

      if( this.id == 'fv_wp_flowplayer_field_splash' ) {
        $input = get_field('auto_splash', true);
      } else {
        $input = get_field('auto_caption', true);
      }

      if( $input.length > 0 ) {
        $input.val(0);

        debug_log(this.id+' has been updated manually!');
      }

    });

    // Public stuff
    return {
      add_notice,

      playlist_index,

      reload_preview( index ) {
        return reload_preview(index);
      },

      fv_wp_flowplayer_dialog_resize,

      get_current_player_db_id,

      get_current_player_object,

      get_current_video_meta,

      get_current_video_meta_value,

      get_current_video_object,

      get_current_video_index() {
        return parseInt( current_video_to_edit );
      },

      get_current_video_db_id() {
        return current_video_db_id;
      },

      get_edit_lock_removal() {
        return edit_lock_removal;
      },

      get_field( key, where ) {
        return get_field( key, where );
      },

      get_playlist_video_meta,

      get_playlist_video_meta_value,

      get_playlist_video_object,

      get_shortcode_remains: function() {
        return shortcode_remains;
      },

      get_tab( index, tab ) {
        return get_tab( index, tab );
      },

      insertUpdateOrDeletePlayerMeta,

      insertUpdateOrDeleteVideoMeta,

      is_busy_saving() {
        return ajax_save_this_please || is_saving;
      },

      // Allows external entities to set which video will be edited once the editor opens
      set_current_video_to_edit,

      set_edit_lock_removal( val ) {
        edit_lock_removal = val;
      },

      set_shortcode_remains: function(value) {
        shortcode_remains = value;
      },

      /**
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

      playlist_item_add: playlist_item_add,

      playlist_show: playlist_show,

      shortcode_parse_arg,

      // We keep it for backwards compatibility
      editor_resize: function() {},

      get_playlist_items_count,

      insert_button_toggle,

      copy_player_button_toggle,

      // TODO: Deprecated
      meta_data_load_started() {
        is_loading_meta_data++;
      },

      meta_data_load_finished() {
        is_loading_meta_data--;
      },

      editor_open: editor_open,

      error_add( identifier, txt ) {
        errors[ identifier ] = txt;

        // no save button while we have errors
        insert_button_toggle_disabled( true );
      },

      error_remove( identifier ) {
        delete errors[ identifier ];

        if ( !has_errors() ) {
          // enable save button
          insert_button_toggle_disabled( false );
        }
      },

      errors_remove( prefix ) {
        var errors_found = [];

        for ( var key in errors ) {
          if ( key.startsWith( prefix ) ) {
            errors_found.push( key );
          }
        }

        if ( errors_found.length ) {
          for ( var val of errors_found ) {
            delete errors[ val ];
          }
        }

        if ( has_errors() ) {
          // enable save button
          insert_button_toggle_disabled( false );
        }
      },

      has_errors,

      b64EncodeUnicode(str) {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
          return String.fromCharCode('0x' + p1);
        }));
      },
    };

  })(jQuery);

  if ( window.wp && wp.data ) {
    wp.data.subscribe( function() {
      if ( wp.data.select('core/editor') && wp.data.select('core/editor').getCurrentPost().fv_player_reload ) {
        setTimeout( function() {
          location.href = location.href;
        }, 0 );
      }
    } );
  }
});
