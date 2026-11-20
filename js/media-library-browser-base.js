jQuery(function() {
  window.fv_player_media_browser = (function($) {
    var current_folder = '',
      current_pending_path = '',
      current_pending_bucket = '',
      current_term_id = false,
      $current_pending_tab = false,
      current_pending_refresh = false,
      is_uploading = false;

    return {
      get_current_folder() {
        return current_folder;
      },

      set_current_folder( folder ) {
        return current_folder = folder;
      },

      get_current_pending_path() {
        return current_pending_path;
      },

      set_current_pending_path( path ) {
        return current_pending_path = path;
      },

      get_current_pending_bucket() {
        return current_pending_bucket;
      },

      set_current_pending_bucket( bucket ) {
        return current_pending_bucket = bucket;
      },

      get_current_pending_tab() {
        return $current_pending_tab;
      },

      set_current_pending_tab( tab ) {
        return $current_pending_tab = tab;
      },

      get_current_pending_refresh() {
        return current_pending_refresh;
      },

      get_current_term_id() {
        return current_term_id;
      },

      set_current_term_id( term_id ) {
        return current_term_id = term_id;
      },

      set_current_pending_refresh( refresh ) {
        return current_pending_refresh = refresh;
      },

      get_upload_status() {
        return is_uploading;
      },

      set_upload_status( uploading ) {
        return is_uploading = uploading;
      },

      // TODO: fv_player_get_active_tab
      get_current_bucket() {
        return jQuery('#browser-dropdown').val()
      },

      get_active_tab() {
        return fv_flowplayer_media_browser_tabs.get_active_tab();
      },

      get_active_tab_id() {
        return fv_flowplayer_media_browser_tabs.get_active_tab_id();
      },

      get_active_tab_config() {
        return fv_flowplayer_media_browser_tabs.get_active_tab_config();
      },

      get_frame() {
        return fv_flowplayer_media_browser_tabs.get_frame();
      },

      get_frame_el() {
        return fv_flowplayer_media_browser_tabs.get_frame_el();
      },

      is_fv_tab_active() {
        return fv_flowplayer_media_browser_tabs.is_fv_tab_active();
      },

      set_choose_button_disabled( disabled ) {
        return fv_flowplayer_media_browser_tabs.set_choose_button_disabled( disabled );
      },

      open_standalone( options ) {
        return fv_flowplayer_media_browser_tabs.open_standalone( options );
      },

    }
  })(jQuery);
})

var
  fv_flowplayer_scannedFolders = [],
  fv_flowplayer_scannedFiles = [],
  fv_flowplayer_current_pending_refresh = false,
  fv_flowplayer_current_pending_path,
  // TODO: Use some public method instead
  // object where key->value pairs represent tabId->ajaxAssetsLoadingScript pairs
  // ... we use this to load assets (media files) from SDK of the correct browser integration
  //     depending on which tab is currently active
  fv_flowplayer_browser_assets_loaders = {},

  // TODO: Use some public method instead
  // the following will contain file patterns which will be checked when determining whether we can automatically find
  // a splash screen for a video file in any given browser
  //
  // ... for example, Coconut uses m3u8 files with the same file name as its encoded file names for thumbnails,
  //     so it will need to create an inclusion rule like so:
  //        fv_flowplayer_browser_splash_file_lookup_rules['fv_player_coconut_browser_media_tab']['include'] = ['\.(m3u8)$']
  fv_flowplayer_browser_splash_file_lookup_rules = {},
  fv_flowplayer_browser_holding_meta_key = false;

// this thumbnail sizing functionality originally comes from WP JS
function fv_flowplayer_media_browser_setColumns() {
  var
    $frame = fv_flowplayer_media_browser_tabs.get_frame_el(),
    $browser = $frame.find('#__assets_browser'),
    width = $browser.width(),
    idealColumnWidth = jQuery( window ).width() < 640 ? 135 : 150;

  if ( width ) {
    var columns = Math.min( Math.round( width / idealColumnWidth ), 12 ) || 1;
    $browser
      .closest( '.media-frame-content' )
      .attr( 'data-columns', columns );
  }
}

function fv_flowplayer_browser_add_load_more_button($fileListUl, loadMoreButtonAction) {
  $fileListUl.append('<li tabindex="0" class="attachment" id="overlay-loader-li"></li>');
  var $moreDiv = jQuery('<div class="attachment-preview"><div class="loadmore"></div></div>');
  var $a = jQuery('<button type="button" class="button media-button button-primary button-large">Load More</button>');
  $a.on('click', loadMoreButtonAction);
  $moreDiv.find('.loadmore').append($a);
  $fileListUl.find('#overlay-loader-li').append($moreDiv);
}

// retrieves options and data for media browser and refreshes its content
function fv_flowplayer_browser_browse(data, options) {
  if ( ! fv_flowplayer_media_browser_tabs.is_fv_tab_active() ) {
    return;
  }

  var
    $frame = fv_flowplayer_media_browser_tabs.get_frame_el(),
    filemanager = $frame.find('.attachments-browser'),
    breadcrumbs = filemanager.find('.breadcrumbs'),
    fileList = filemanager.find('.data'),
    $searchInput = filemanager.find('#media-search-input'),
    showBreadcrumbs = (options && options.breadcrumbs ? options.breadcrumbs : false);

  var
    breadcrumbsUrls = [];

    fv_player_media_browser.set_current_folder('')

  // if we're appending data, don't do all this
  if (!options || !options.append) {
    jQuery(window).off('fv-player-browser-open-folder');
    jQuery(window).on('fv-player-browser-open-folder', function (e, path) {

      fv_player_media_browser.set_current_folder(data.path);

      if (showBreadcrumbs) {
        breadcrumbsUrls.push(data.path);
      }

      render(data.items, options);
    }).trigger('fv-player-browser-open-folder', ['']);
  } else if (options.append) {
    // we're appending, just render new items
    render(data.items, options);
  }


  // Listening for keyboard input on the search field.
  // We are using the "input" event which detects cut and paste
  // in addition to keyboard input.
  if (options && options.ajaxSearchCallback && !options.append) {
    var timedSearchTask = -1,
      searchDelay = (options.searchDelay >= 0 ? options.searchDelay : 1000),
      lastSearchVal = $searchInput.val();

    function runAjaxSearch(searchVal) {
      lastSearchVal = searchVal;
      options.ajaxSearchCallback(searchVal);

      var attempts = 0;
      var checkExist = setInterval(function() {
        attempts++;
        var $currentSearch = fv_flowplayer_media_browser_tabs.get_frame_el().find('#media-search-input');
        if ($currentSearch.length) {
          if ($currentSearch.val() !== searchVal) {
            $currentSearch.val(searchVal);
          }
          clearInterval(checkExist);
        } else if (attempts > 100) {
          clearInterval(checkExist);
        }
      }, 100); // check every 100ms
    }

    // remove any previous binds to the same input element
    // which could originate from other browsers (and would generate duplicate AJAX calls)
    $searchInput.off('input search keydown');
    $searchInput.on('input search', function () {
      var searchVal = jQuery(this).val();

      if (searchVal === lastSearchVal) {
        return;
      }

      // if we have old search timed task, cancel it and create a new one
      if (timedSearchTask > -1) {
        clearTimeout(timedSearchTask);
      }

      timedSearchTask = setTimeout(function() {
        timedSearchTask = -1;
        runAjaxSearch(searchVal);
      }, searchDelay);
    });

    $searchInput.on('keydown', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        var searchVal = jQuery(this).val();
        if (timedSearchTask > -1) {
          clearTimeout(timedSearchTask);
          timedSearchTask = -1;
        }
        runAjaxSearch(searchVal);
      }
    });
  }

  function addFolderAjax($element, folder_name, options) {
    var data = {
      action: options.action,
      nonce_add_new_folder: options.nonce_add_new_folder,
      folder_name: folder_name,
      current_path: fv_player_media_browser.get_current_folder()
    };

    jQuery.post(options.ajaxurl ? options.ajaxurl : fv_player.ajaxurl, data, function (response) {
      if(response.error) {
        alert(response.error);
      } else {
        // refresh browser
        fv_flowplayer_browser_assets_loaders[ fv_player_media_browser.get_active_tab().attr('id') ]( fv_player_media_browser.get_current_bucket() ,fv_player_media_browser.get_current_folder() );
      }
    });
  }

  // Render the HTML for the file manager
  function render(data, options) {
    var havePendingItems = false;

    fv_flowplayer_scannedFolders = [];
    fv_flowplayer_scannedFiles = [];

    if(Array.isArray(data)) {

      data.forEach(function (d) {

        if (d.type === 'folder') {
          fv_flowplayer_scannedFolders.push(d);
        }
        else {
          fv_flowplayer_scannedFiles.push(d);
        }

      });

    } else if(typeof data === 'object') {
      fv_flowplayer_scannedFolders = data.folders;
      fv_flowplayer_scannedFiles = data.files;
    }

    // Empty the old result and make the new one
    // ... don't do this if we're appending data
    if (!options || !options.append) {
      fileList.empty().hide();
    }

    if(options && options.add_new_folder) {
      fv_player_media_browser.set_current_term_id(options.current_term_id);

      var new_folder = jQuery(
      '<li class="attachment new-folder">'
      + '<div class="attachment-preview js--select-attachment type-video subtype-mp4 landscape">'
      + '<div class="thumbnail">'
      + '<span class="icon folder"></span>'
      + '<div class="filename">'
      + '<div>+ '+ (options.add_new_folder_text ? options.add_new_folder_text : 'Add new category')+ '</div>'
      + '</div>'
      + '</div>'
      + '</div>').on('click', function() {
        var folder_name = prompt("Please enter folder name");
        if(folder_name != null && folder_name.length > 1 ) {
          addFolderAjax(jQuery(this), folder_name, options);
        }
      })

      fileList.append(new_folder);
    }

    if(!fv_flowplayer_scannedFolders.length && !fv_flowplayer_scannedFiles.length) {
      filemanager.find('.nothingfound').show();
    }
    else {
      filemanager.find('.nothingfound').hide();
    }

    if(fv_flowplayer_scannedFolders.length) {
      // sort folders alphabetically
      fv_flowplayer_scannedFolders.sort(function(a, b) {
        if(a.name.toLowerCase() < b.name.toLowerCase()) return -1;
        if(a.name.toLowerCase() > b.name.toLowerCase()) return 1;
        return 0;
      });

      fv_flowplayer_scannedFolders.forEach(function(f) {
        var name = escapeHTML(f.name).replace(/\/$/,'');
        fileList.append( jQuery(
          '<li class="folders attachment save-ready">'
          + '<div class="attachment-preview js--select-attachment type-video subtype-mp4 landscape">'
          + '<div class="thumbnail">'
          + '<a href="' + f.path + '" title="' + name + '" class="folders">'
          + '<span class="icon folder"></span>'
          + '<div class="filename">'
          + '<div>' + name + '</div>'
          + '</div>'
          + '</a>'
          + '</div>'
          + '</div>'
          + '</li>')
        );
      });
    }

    if(fv_flowplayer_scannedFiles.length) {

      fv_flowplayer_scannedFiles.forEach(function(f) {
        var
          name = escapeHTML(f.name),
          file = jQuery('<li tabindex="0" role="checkbox" aria-label="' + name + '" aria-checked="false" class="folders attachment save-ready"></li>'),
          isPicture = name.match(/\.(jpg|jpeg|png|gif)$/),
          icon = '';

        if( f.splash ) {
          icon = '<img src="' + f.splash + '" draggable="false" class="icon thumb" title="' + name + '" loading="lazy" />';
        } else {
          var fileType = name.split('.');
          if( fileType.length > 1 ) {
            fileType = fileType[fileType.length-1];
            icon = '<span class="icon file f-'+fileType+'" >.'+fileType+'</span>';
          } else {
            icon = '<span class="icon file"></span>';
          }
        }

        var progress = '';
        // prepend processing progress DIVs, if needed
        if ( f.extra && ( f.extra.percentage || f.extra.encoding_job_status == 'error' ) ) {
          if ( f.extra.percentage ) {
            var percentage = f.extra.percentage;
            if( parseInt(percentage) < 5 ) percentage = 5+'%';
            progress += '<div class="thumbnail-status">' + ( f.extra.encoding_job_status == 'playable' ? 'Playable' : 'Processing' ) + '</div><div class="thumbnail-progress"><div class="thumbnail-progress-marker" style="width: '+percentage+'"></div></div>';
          } else {
            progress += '<div class="thumbnail-status-error">Error</div>';
          }
        }

        file.append('<div class="attachment-preview js--select-attachment type-video subtype-mp4 landscape' + (options && options.extraAttachmentClass ? ' ' + options.extraAttachmentClass : '') + '">'
          + '<div class="thumbnail"' + (isPicture || (options && options.noFileName) ? ' title="' + name + '"' : '') + '>'
          + icon
          + '<div class="filename' + (isPicture || (options && options.noFileName) ? ' hidden' : '') + '">'
          + '<div>' + name + '</div>'
          + '</div>'
          + progress
          + '</div>'
          + '</div>' +
          '<button type="button" class="check" tabindex="0">' +
          '<span class="media-modal-icon"></span>' +
          '<span class="screen-reader-text">Deselect</span>' +
          '</button>');

        var $filenameData = file.find('.filename > div');
        $filenameData
          .attr('data-modified', f.modified)
          .attr('data-size', f.size)
          .attr('data-link', f.link)
          .data('extra', typeof f.extra !== 'undefined' ? f.extra : {});

        if (f.duration) {
          $filenameData.attr('data-duration', f.duration);
        }

        if ( f.extra && f.extra.encoding_job_status && f.extra.encoding_job_status == 'processing' ) {
          havePendingItems = true;
        }

        file.appendTo(fileList);
      });

      if (options && options.loadMoreButtonAction) {
        fv_flowplayer_browser_add_load_more_button(fileList, options.loadMoreButtonAction);
      }

    }

    // Generate the breadcrumbs
    var url = '';
    if (filemanager.hasClass('searching')) {
      url = '<span>Search results: </span>';
      fileList.removeClass('animated');
    } else {
      fileList.addClass('animated');

      var right_arrow =  '<span class="arrow_sign">→</span> ';
      breadcrumbsUrls.forEach(function (u, i) {
        var name = u.replace(/\/$/,'').split('/');
        if( name.length > 1 ) {
          name.forEach(function (n, k) {
            var path = '';
            for( var j=0; j<k+1; j++ ) {
              path += name[j]+'/';
            }
            url += '<a href="'+path+'"><span class="folderName">'+n+'</span></a>';
            if( k < name.length-1 ) url += right_arrow;
          });
        }

      });
    }

    breadcrumbs.text('').append(url);
    fileList.show();
    fv_flowplayer_media_browser_setColumns();
    fileList.hide().fadeIn();

    // remove old timeout
    if( fv_player_media_browser.get_current_pending_refresh() ) {
      clearTimeout(fv_player_media_browser.get_current_pending_refresh());
      fv_player_media_browser.set_current_pending_refresh(false);
    }

    // check if we should refresh when we have pending items and not currently uploading
    if ( havePendingItems && !fv_player_media_browser.get_upload_status() ) {
      // update pending values
      fv_player_media_browser.set_current_pending_path( fv_player_media_browser.get_current_folder() );
      fv_player_media_browser.set_current_pending_tab( fv_player_media_browser.get_active_tab() );
      fv_player_media_browser.set_current_pending_bucket( fv_player_media_browser.get_current_bucket() );

      var refresh = setTimeout( function() {
        if( fv_player_media_browser.get_active_tab().attr('id') == fv_player_media_browser.get_current_pending_tab().attr('id') ) {
          fv_flowplayer_browser_assets_loaders[ fv_player_media_browser.get_active_tab().attr('id') ]( fv_player_media_browser.get_current_bucket() ,fv_player_media_browser.get_current_folder() );
        }
      }, 30000 );

      fv_player_media_browser.set_current_pending_refresh(refresh);
    }
  }

  // This function escapes special html characters in names
  function escapeHTML(text) {
    return text.replace(/\&/g,'&amp;').replace(/\</g,'&lt;').replace(/\>/g,'&gt;');
  }

}

/*
 * Media Library tabs of the FV Player media browsers (Coconut, Cloudflare R2, Vimeo...)
 *
 * The tabs are added into the router of the WordPress Media Library frame (wp.media.view.Router)
 * and each tab uses its own content mode of the frame. So WordPress takes care of switching
 * the active tab and of removing the content of the previous tab.
 *
 * The loader function of the media browser runs when its content mode gets activated.
 */
var fv_flowplayer_media_browser_tabs = ( function( $ ) {
  var
    // tabId => { text, loader, click_callback, priority, $el, standalone, render_sidebar, on_select, on_close, multiple }
    tabs = {},
    standalone_frames = {},
    tabs_count = 0,
    current_frame = false,
    RouterItem = false,
    suppress_last_tab_save = false,
    restore_timer = null;

  /**
   * Media browsers which only provide video files (Bunny Stream, Coconut, Vimeo...)
   * only show for the video source fields.
   */
  function is_tab_allowed( tabId ) {
    var input_name = $( '.fv_flowplayer_target' ).attr( 'name' );

    // input_name is not set when clicking on the "Select Media" button in the block, show all the tabs in that case
    if ( ! input_name || ! input_name.length ) {
      return true;
    }

    var video_only_tabs = [
      'fv_player_bunny_stream_browser_media_tab',
      'fv_flowplayer_cloudflare_stream_browser_media_tab',
      'fv_player_coconut_browser_media_tab',
      'fv_flowplayer_peertube_private_browser_media_tab',
      'fv_flowplayer_vimeo_browser_media_tab',
      'fv_player_cloudflare_stream_browser_media_tab'
    ];

    if ( video_only_tabs.indexOf( tabId ) == -1 ) {
      return true;
    }

    return [
      'fv_wp_flowplayer_field_src',
      'fv_wp_flowplayer_field_src1',
      'fv_wp_flowplayer_field_src2',
      'fv-player-gutenberg-media',
      'fv_wp_flowplayer_field_ppv_preview'
    ].indexOf( input_name ) > -1;
  }

  function is_tab_allowed_by_library_conf( tabId, itemView ) {
    if ( ! window.fv_player_editor_conf || ! fv_player_editor_conf.library ) {
      return true;
    }

    var elId = ( itemView && itemView.$el && itemView.$el.attr( 'id' ) ) ? itemView.$el.attr( 'id' ) : tabId,
      libraries = fv_player_editor_conf.library.split( /,/ );

    for ( var i = 0; i < libraries.length; i++ ) {
      if ( libraries[ i ] && elId.match( libraries[ i ] ) ) {
        return true;
      }
    }

    return false;
  }

  function is_frame( frame ) {
    return !! ( frame && frame.content && frame.$el && typeof frame.on == 'function' );
  }

  /**
   * @return {wp.media.view.MediaFrame|false} The Media Library frame which is open.
   */
  function get_frame() {
    if ( is_frame( current_frame ) && ( current_frame._fv_opening || current_frame.$el.is( ':visible' ) ) ) {
      return current_frame;
    }

    // Fallback for the code which does not pass the frame with the mediaBrowserOpen event, wp.media() stores the last frame there
    if ( window.wp && wp.media && is_frame( wp.media.frame ) && wp.media.frame.$el.is( ':visible' ) ) {
      return wp.media.frame;
    }

    return false;
  }

  function get_frame_el() {
    var frame = get_frame();
    if ( frame && frame.$el && frame.$el.length ) {
      return frame.$el;
    }
    return $( '.media-modal:visible' );
  }

  function get_router_view( frame ) {
    var view = is_frame( frame ) && frame.router && frame.router.get();

    if ( view && wp.media.view.Router && view instanceof wp.media.view.Router ) {
      return view;
    }

    return false;
  }

  function get_active_tab_id() {
    var frame = get_frame();
    if ( frame && frame.content ) {
      var mode = frame.content.mode();
      if ( mode ) {
        return mode;
      }
    }
    return get_active_tab().attr( 'id' );
  }

  function get_active_tab_config() {
    var activeTabId = get_active_tab_id();
    return ( activeTabId && tabs[ activeTabId ] ) ? tabs[ activeTabId ] : null;
  }

  function get_active_tab() {
    var frame = get_frame(),
      routerView = get_router_view( frame );

    if ( routerView && frame.content ) {
      var activeMode = frame.content.mode(),
        itemView = activeMode ? routerView.get( activeMode ) : null;

      if ( itemView && itemView.$el && itemView.$el.length ) {
        return itemView.$el;
      }
    }

    if ( frame ) {
      return frame.$el.find( '.media-router .media-menu-item.active' );
    }

    return $( '.media-menu-item.active:visible' );
  }

  function is_fv_tab_active() {
    var frame = get_frame();
    if ( frame && frame.content ) {
      var mode = frame.content.mode();
      return !! ( mode && tabs[ mode ] );
    }
    return get_active_tab().hasClass( 'fv-player-media-tab' );
  }

  function set_choose_button_disabled( disabled ) {
    var frame = get_frame();

    // Keep WordPress's Backbone Toolbar.Select button model disabled on FV tabs
    // (since native WP selection has 0 attachments) so WP doesn't prematurely
    // close/reset the modal before our .media-button-select handler reads #__assets_browser.
    if ( is_fv_tab_active() ) {
      var toolbar = frame && frame.toolbar && frame.toolbar.get(),
        selectBtn = toolbar && toolbar.get( 'select' );

      if ( selectBtn && selectBtn.model ) {
        selectBtn.model.set( 'disabled', true );
      }
    }

    ( frame ? frame.$el.find( '.media-button-select' ) : $( '.media-button-select' ) ).prop( 'disabled', !! disabled );
  }

  function clear_pending_refresh() {
    if ( window.fv_player_media_browser ) {
      var pending = fv_player_media_browser.get_current_pending_refresh();
      if ( pending ) {
        clearTimeout( pending );
        fv_player_media_browser.set_current_pending_refresh( false );
      }
    }
  }

  /**
   * Removes only the residual DOM elements created by FV Player media browsers
   * (renderBrowserPlaceholderHTML output, Backbone placeholder view, and cloned loading spinner)
   * without touching any WordPress core Media Library views.
   */
  function remove_fv_tab_content( frame ) {
    frame.$el
      .find( '.media-frame-content' )
      .children( '.fv-player-media-browser-content, .fv-player-media-browser-tab-content, #fv-player-shortcode-editor-preview-spinner' )
      .remove();
  }

  function get_router_item_class() {
    if ( ! RouterItem ) {
      RouterItem = wp.media.view.RouterItem.extend( {
        render: function() {
          wp.media.view.RouterItem.prototype.render.apply( this, arguments );

          // WordPress would use menu-item-{contentMode} as the ID, but FV Player and its extensions identify the tabs by the tab ID
          this.$el.attr( 'id', this.options.contentMode ).addClass( 'fv-player-media-tab' );

          return this;
        },

        click: function() {
          // Clicking the active tab reloads it, WordPress would not do anything as the content mode does not change
          if ( this.controller.content.mode() === this.options.contentMode ) {
            activate_tab( this.controller, this.options.contentMode );
          } else {
            wp.media.view.RouterItem.prototype.click.apply( this, arguments );
          }
        }
      } );
    }

    return RouterItem;
  }

  /**
   * Adds the tab into the router or removes it if it's not allowed for the current field.
   *
   * @return {wp.media.view.RouterItem|false} The router item or false if the tab is not allowed.
   */
  function add_tab_to_router( frame, routerView, tabId ) {
    var
      tab = tabs[ tabId ],
      item = routerView.get( tabId );

    if ( ! is_tab_allowed( tabId ) ) {
      if ( item ) {
        routerView.unset( tabId );
      }

      if ( frame.content && frame.content.mode() === tabId ) {
        suppress_last_tab_save = true;
        try {
          frame.content.mode( 'browse' );
        } finally {
          suppress_last_tab_save = false;
        }
      }

      return false;
    }

    if ( ! item ) {
      var Item = get_router_item_class(),
        // Keep the classes added by the media browser, like upload_supported, if the router was re-created
        classes = tab.$el ? ( tab.$el.attr( 'class' ) || '' ).replace( /\bactive\b/g, '' ) : '';

      item = new Item( {
        controller: frame,
        contentMode: tabId,
        text: tab.text,
        // WordPress uses 20 for Upload files and 40 for Media Library
        priority: tab.priority
      } );

      routerView.set( tabId, item );

      item.$el.addClass( classes );
    }

    tab.$el = item.$el;

    return item;
  }

  /**
   * Runs when the tab content mode gets activated or when the active tab is clicked again.
   */
  function activate_tab( frame, tabId ) {
    var tab = tabs[ tabId ],
      routerView = get_router_view( frame );

    if ( ! tab ) {
      return;
    }

    if ( routerView && routerView.get( tabId ) ) {
      routerView.select( tabId );
    }

    frame.$el.find( '.media-frame-content' ).attr( 'aria-labelledby', tabId );

    // Clear WP's native attachment selection so a previously selected Media Library item
    // cannot fire WP's native select handler when clicking Choose on an FV tab.
    var state = frame.state && frame.state();
    if ( state && state.get && state.get( 'selection' ) ) {
      state.get( 'selection' ).reset();
    }

    set_choose_button_disabled( true );

    if ( ! tab.standalone ) {
      fv_flowplayer_media_browser_disable_drag_drop( true );
      fv_flowplayer_media_browser_show_upload( tabId );
    }

    if ( typeof( tab.click_callback ) == 'function' ) {
      tab.click_callback();
    }

    if ( ! suppress_last_tab_save && ! tab.standalone ) {
      // store last clicked tab ID
      try {
        if ( typeof( window.localStorage ) == 'object' ) {
          localStorage.setItem( 'fv_player_last_tab_selected', tabId );
        }
      } catch ( e ) {}
    }

    frame.$el.find( '#media-search-input' ).val( '' );

    // hide the Drop files to upload modal initially
    frame.$el.find( '.uploader-window' ).css( {
      'display' : 'none',
      'opacity' : 0,
    } );

    return tab.loader.call( tab.$el ? tab.$el[0] : null );
  }

  function hook_frame( frame ) {
    if ( frame.fv_player_media_browser_tabs ) {
      return;
    }

    frame.fv_player_media_browser_tabs = true;

    // WordPress re-creates the router when the frame state changes, add the tabs back
    frame.on( 'router:render:browse', function( routerView ) {
      Object.keys( tabs ).forEach( function( tabId ) {
        if ( ! tabs[ tabId ].standalone ) {
          add_tab_to_router( frame, routerView, tabId );
        }
      } );

      if ( typeof( routerView.update ) == 'function' ) {
        routerView.update();
      }
    } );

    frame.on( 'close', function() {
      clear_pending_refresh();

      var activeMode = frame.content && frame.content.mode(),
        tab = activeMode && tabs[ activeMode ];
      if ( tab && typeof( tab.on_close ) == 'function' ) {
        tab.on_close();
      }

      // Remove rendered FV tab elements on close so hidden frames never leave duplicate global IDs
      // (#__assets_browser, #media-search-input, #browser-dropdown) that legacy FV Player Pro,
      // Coconut, S3, and Bunny Stream browsers query without frame scoping.
      if ( tab ) {
        remove_fv_tab_content( frame );
      }

      if ( current_frame === frame ) {
        current_frame = false;
      }
    } );

    frame.on( 'all', function( event_name, arg ) {
      var match = typeof( event_name ) == 'string' && event_name.match( /^content:(create|activate|deactivate|render)(?::(.+))?$/ );
      if ( ! match ) {
        return;
      }

      var
        action = match[1],
        mode = match[2];

      // Generic content:render event, runs after WordPress set the tab panel ARIA attributes
      if ( ! mode ) {
        var tabPanelEl = frame.$el.find( '.media-frame-content' ),
          currentMode = frame.content.mode();

        if ( 'render' == action && currentMode && tabPanelEl.attr( 'aria-labelledby' ) ) {
          tabPanelEl.attr( 'aria-labelledby', tabs[ currentMode ] ? currentMode : 'menu-item-' + currentMode );
        }
        return;
      }

      if ( tabs[ mode ] ) {
        if ( 'create' == action ) {
          // Placeholder view, so that WordPress removes the view of the previous tab
          // The media browser loader replaces it with its own HTML
          arg.view = new wp.media.View( { className: 'fv-player-media-browser-tab-content' } );

        } else if ( 'activate' == action ) {
          // Standalone frames activate inside their 'open' event after WordPress renders the content region
          if ( ! tabs[ mode ].standalone ) {
            activate_tab( frame, mode );
          }

        } else if ( 'deactivate' == action ) {
          clear_pending_refresh();
          // Legacy media browsers replace .media-frame-content via .html(), detaching the
          // Backbone placeholder view. Remove our residual elements so WordPress's next view is not hidden behind them.
          remove_fv_tab_content( frame );
        }

      // One of the WordPress tabs, like Upload files or Media Library
      } else if ( 'activate' == action ) {
        fv_flowplayer_media_browser_disable_drag_drop( false );
        fv_flowplayer_media_browser_show_upload( '' );
      }
    } );
  }

  function restore_last_tab() {
    var frame = get_frame(),
      last_tab = false;

    try {
      if ( typeof( window.localStorage ) == 'object' ) {
        last_tab = localStorage.getItem( 'fv_player_last_tab_selected' );
      }
    } catch ( e ) {}

    if ( ! frame || ! last_tab || ! tabs[ last_tab ] || tabs[ last_tab ].standalone ) {
      return;
    }

    var routerView = get_router_view( frame ),
      item = routerView && routerView.get( last_tab );

    if ( item && is_tab_allowed_by_library_conf( last_tab, item ) ) {
      if ( frame.content.mode() !== last_tab ) {
        frame.content.mode( last_tab );
      } else {
        activate_tab( frame, last_tab );
      }
    }
  }

  function schedule_restore_last_tab( frame ) {
    hook_frame( frame );

    // Prevent synchronous open-time clicks (e.g. shortcode-editor.js clicking tab 0 or library tabs)
    // from overwriting localStorage.fv_player_last_tab_selected before restore_last_tab runs.
    suppress_last_tab_save = true;

    if ( restore_timer ) {
      clearTimeout( restore_timer );
    }

    restore_timer = setTimeout( function() {
      restore_timer = null;
      suppress_last_tab_save = false;
      restore_last_tab();
    }, 0 );
  }

  // This is bound before the media browsers bind their mediaBrowserOpen listeners in which they add their tabs
  $( document ).on( 'mediaBrowserOpen', function( e, frame ) {
    current_frame = is_frame( frame ) ? frame : false;

    var open_frame = get_frame();
    if ( open_frame ) {
      schedule_restore_last_tab( open_frame );
    }
  } );

  return {
    /**
     * @return {jQuery|false} The tab element, empty jQuery object if the tab is not allowed for the current field
     *                        or false if there is no open Media Library frame with router.
     */
    add_tab: function( tabId, tabText, loader, added_callback, click_callback ) {
      var frame = get_frame(),
        routerView = get_router_view( frame );

      if ( ! routerView ) {
        return false;
      }

      schedule_restore_last_tab( frame );

      if ( ! tabs[ tabId ] ) {
        tabs[ tabId ] = {
          priority: 100 + 10 * tabs_count++
        };
      }

      tabs[ tabId ].text = tabText;
      tabs[ tabId ].loader = loader;
      tabs[ tabId ].click_callback = click_callback;

      // save assets loading function
      fv_flowplayer_browser_assets_loaders[ tabId ] = loader;

      var is_new = ! routerView.get( tabId ),
        item = add_tab_to_router( frame, routerView, tabId );

      if ( ! item ) {
        return $();
      }

      if ( is_new && typeof( added_callback ) == 'function' ) {
        added_callback( item.$el );
      }

      return item.$el;
    },

    /**
     * Opens a standalone WordPress Media Library Backbone frame managed by this wrapper,
     * without router tabs (Upload/Media Library/S3/Coconut...) and without overwriting
     * localStorage.fv_player_last_tab_selected.
     *
     * @return {wp.media.view.MediaFrame|false}
     */
    open_standalone: function( options ) {
      if ( ! options || ! options.tabId || typeof( options.loader ) !== 'function' || ! window.wp || ! wp.media || ! window.Backbone ) {
        return false;
      }

      var tabId = options.tabId,
        title = options.title || 'Choose',
        buttonText = options.buttonText || 'Choose';

      tabs[ tabId ] = {
        text: title,
        loader: options.loader,
        click_callback: options.click_callback || null,
        standalone: true,
        render_sidebar: options.renderSidebar || null,
        on_select: options.onSelect || null,
        on_close: options.onClose || null,
        multiple: !! options.multiple
      };

      fv_flowplayer_browser_assets_loaders[ tabId ] = options.loader;

      var frame = standalone_frames[ tabId ];
      if ( ! frame ) {
        var state = new wp.media.controller.State( {
          id: tabId,
          title: title,
          content: tabId,
          menu: false,
          router: false,
          toolbar: 'select',
          multiple: !! options.multiple,
          selection: new window.Backbone.Collection()
        } );

        frame = new wp.media.view.MediaFrame.Select( {
          title: title,
          button: {
            text: buttonText
          },
          state: tabId,
          states: [ state ],
          multiple: !! options.multiple,
          uploader: false
        } );

        hook_frame( frame );

        frame.on( 'open', function() {
          current_frame = frame;
          frame.$el.addClass( 'hide-menu hide-router' );
          if ( options.frameClass ) {
            frame.$el.addClass( options.frameClass );
          }

          if ( tabs[ tabId ] && tabs[ tabId ].text ) {
            frame.$el.find( '.media-frame-title h1' ).text( tabs[ tabId ].text );
          }

          activate_tab( frame, tabId );
        } );

        standalone_frames[ tabId ] = frame;
      }

      var existingState = frame.state( tabId );
      if ( existingState && title ) {
        existingState.set( 'title', title );
      }

      current_frame = frame;
      frame._fv_opening = true;
      try {
        frame.open();
      } finally {
        frame._fv_opening = false;
      }

      return frame;
    },

    get_frame: get_frame,
    get_frame_el: get_frame_el,
    get_active_tab: get_active_tab,
    get_active_tab_id: get_active_tab_id,
    get_active_tab_config: get_active_tab_config,
    is_fv_tab_active: is_fv_tab_active,
    set_choose_button_disabled: set_choose_button_disabled,

  };

} )( jQuery );

// adds new tab on top of the Media Library popup
function fv_flowplayer_media_browser_add_tab( tabId, tabText, tabOnClickCallback, tabAddedCallback, tabClickEventCallback ) {
  var $tab = fv_flowplayer_media_browser_tabs.add_tab( tabId, tabText, tabOnClickCallback, tabAddedCallback, tabClickEventCallback );

  if ( $tab ) {
    return $tab;
  }

  /*
   * No open Media Library frame with router found. The frame should be passed with the mediaBrowserOpen event
   * like this: $( document ).trigger( "mediaBrowserOpen", [ frame ] ), otherwise the frame created by wp.media() is used.
   */
  console.warn( 'FV Player: Media browser: Unable to add tab ' + tabId + ', no open WordPress Media Library frame with router found.' );

  return jQuery();
}

/*
 * Disable/enable core WordPress drag&drop uploader
 */
function fv_flowplayer_media_browser_disable_drag_drop( disable ) {
  var
    overlay = jQuery('.media-frame-uploader'),
    overlay_content = jQuery('.media-modal .uploader-window'),
    overlay_title = overlay_content.find('.uploader-editor-title'),
    drop_targets = jQuery('[id^=__wp-uploader-id-]'),
    $active_tab = fv_player_media_browser.get_active_tab(),
    upload_supported = $active_tab.hasClass( 'upload_supported' );

  var original_title = overlay_title.data('original-overlay-title');
  if( !original_title ) {
    overlay_title.data('original-overlay-title', overlay_title.text() );
  }

  if( disable ) {
    drop_targets.off('drop', fv_flowplayer_media_browser_disable_drag_drop_worker );
    drop_targets.on('drop', fv_flowplayer_media_browser_disable_drag_drop_worker );

    if ( !upload_supported ) {
      overlay.css('opacity', 0 );
    } else {
      overlay.css('opacity', 1 );
    }

    overlay_title.html( original_title + ' to ' + $active_tab.text() );

  } else {
    drop_targets.off('drop', fv_flowplayer_media_browser_disable_drag_drop_worker );

    if ( !upload_supported ) {
      overlay.css('opacity', '' );

      // We need to hide this now as WordPress did make it visible at some point
      overlay_content.css({
        'display' : 'none',
        'opacity' : 0,
      });
    } else {
      overlay.css('opacity', 1 );
      overlay_content.css({
        'display' : 'block',
        'opacity' : 1,
      });
    }

    overlay_title.html( original_title );
  }
}

function fv_flowplayer_media_browser_disable_drag_drop_worker( e ) {
  // forward this event via a custom trigger which gets intercepted by our browsers that support file uploads
  // ... if we just returned false here without the custom trigger, we're basically prevent any drop event anywhere on the Media Browser dialog
  jQuery( document ).trigger('media_browser_drop_event', [ fv_player_media_browser.get_active_tab().attr( 'id' ), e.originalEvent.dataTransfer.files ] );
  return false;
}

function fv_flowplayer_media_browser_show_upload( id ) {
  jQuery('.media-toolbar-secondary > .fv-player-upload_buttons').hide();
  if ( id ) {
    jQuery('.media-toolbar-secondary > .fv-player-upload_buttons[data-tab-id="' + id + '"]').show();
  }
}

function renderBrowserPlaceholderHTML(options) {
  options = options || {};
  var html = '<div class="attachments-browser fv-player-media-browser-content' + (options.noBreadcrumbs ? ' no-breadcrumbs' : '') + '"><div class="media-toolbar s3-media-toolbar">';

  if (options.dropdownItems) {
    html += '<div class="media-toolbar-secondary">';

    // prepare dropdown HTML
    var
      select_html = '<label for="browser-dropdown" class="screen-reader-text">S3 Bucket</label>'
        + '<select name="browser-dropdown" id="browser-dropdown" class="attachment-filters">',
      one_option_enabled = (options.dropdownTopDefault ? true : false);

    // if we have a default option, add it here
    if (options.dropdownDefaultOption) {
      select_html += '<option value="' + options.dropdownDefaultOption.value + '"' + (!options.dropdownItemSelected || (options.dropdownItemSelected && options.dropdownItemSelected == options.dropdownDefaultOption.value) ? ' selected="selected"' : '') + '>' + options.dropdownDefaultOption.text + '</option>'
    }

    for (var i in options.dropdownItems) {
      select_html += '<option value="' + options.dropdownItems[i].value + '"' + (options.dropdownItemSelected && options.dropdownItemSelected == options.dropdownItems[i].value ? ' selected="selected"' : '') + '>' + options.dropdownItems[i].text + '</option>'

      if (options.dropdownItems[i].value > -1) {
        one_option_enabled = true;
      }
    }

    select_html += '</select><span class="spinner"></span>';

    // check if we have at least a single option enabled
    // and if not and we need one, replace the whole select HTML with a warning message
    if (!one_option_enabled && options.dropDownNoOptionEnabledWarningMsg) {
      select_html = options.dropDownNoOptionEnabledWarningMsg;
    }

    html += select_html + '</div>';
  }

  html += '<div class="media-toolbar-primary search-form">' +
    '<label for="media-search-input" class="screen-reader-text">Search Media</label>' +
    '<input type="search" placeholder="'+ (options.searchMsg ? options.searchMsg : 'Search media items...') +'" id="media-search-input" class="search">' +
    '</div>' +
    '</div>' +
    (options.noBreadcrumbs ? '' : '\t\t<div class="breadcrumbs"></div>\n') +
    '\n';

  if (options.errorMsg) {
    html += '<div class="errors"><strong>' + options.errorMsg + '</strong></div><hr /><br />';
  }

  html += '\t\t<ul tabindex="-1" class="data attachments ui-sortable ui-sortable-disabled" id="__assets_browser"></ul>\n' +
    '<div class="media-sidebar"></div>' +
    '\t\t<div class="nothingfound">\n' +
    '\t\t\t<div class="nofiles"></div>\n' +
    '\t\t\t<p>' + (options.nothingFoundMsg ? options.nothingFoundMsg : 'No files here.') + '</p>\n' +
    '\t\t</div>\n' +
    '\n' +
    '\t</div>';

  return html;
}

jQuery( function($) {
  var $lastElementSelected = null;

  // calculate number of columns on each window resize
  jQuery(window).on('resize', function() {
    setTimeout(fv_flowplayer_media_browser_setColumns, 500);
  });

  function fileGetBase( link ) {
    link = link.replace(/\.[a-z0-9]+$/,'');
    return link;
  }

  /**
   * Iterates over all of the loaded files for the current browser
   * and tries to find a file object that has the same base name as our given
   * video HREF parameter and can therefore be used as its splash.
   *
   * @param href The video file we're trying to find a splash image for.
   * @returns {boolean|Object} Returns either false, if splash image for the given HREF is not found,
   *                           otherwise returns the splash image object itself.
   */
  function locateSplashFileObjectForMediaFileHref(href) {
    var find = [ fileGetBase(href) ];

    if( window.fv_player_editor_pro && fv_player_editor_pro.video_qualities ) {
      Object.keys(fv_player_editor_pro.video_qualities).forEach( function(prefix) {
        var re = new RegExp(prefix+'$');
        if( find[0].match(re) ) {
          find.push( find[0].replace(re,'') );
        }
      });
    }

    var
      splash = false,
      activeTabId = fv_player_media_browser.get_active_tab().attr('id');

    for( var i in find ) {
      for( var j in fv_flowplayer_scannedFiles ) {
        var
          f = fv_flowplayer_scannedFiles[j],
          // check for image splash files with the same base name that are not poining to the same actual file
          // as the one we're checking them against (classic splash files)
          splashCheck = ( f && f.link && f.link.match(/\.(jpg|jpeg|png|gif)$/) && fileGetBase(f.link) == find[i] && f.link != href );

        // check for any additional inclusions and exclusions in splash checks
        if ( fv_flowplayer_browser_splash_file_lookup_rules[ activeTabId ] ) {
          // see if we have additional files to include as splash images
          if ( fv_flowplayer_browser_splash_file_lookup_rules[ activeTabId ]['include'] ) {
            // check for each of these file names against current file name
            for ( var value of fv_flowplayer_browser_splash_file_lookup_rules[ activeTabId ]['include'] ) {
              var regexCheck = new RegExp( value );
              if ( f && f.link && f.link.match( regexCheck ) && fileGetBase(f.link) == find[i] ) {
                // file pattern found, simply set splashCheck to true and bail out to continue with exclusion checks
                splashCheck = true;
                break;
              }
            }
          }

          // see if we have additional files to exclude as splash images
          if ( fv_flowplayer_browser_splash_file_lookup_rules[ activeTabId ]['exclude'] ) {
            // check for each of these file names against current file name
            for ( var value of fv_flowplayer_browser_splash_file_lookup_rules[ activeTabId ]['exclude'] ) {
              var regexCheck = new RegExp( value );
              if ( f && f.link && !f.link.match( regexCheck ) && fileGetBase(f.link) == find[i] ) {
                // file pattern found, simply set splashCheck to false and bail out, as we must exclude this file as splash
                splashCheck = false;
                break;
              }
            }
          }
        }

        if ( splashCheck ) {
          splash = f;
        }
      }
    }

    return splash;
  }

  /**
   * Returns the actual splash image file to be used as a thumbnail or splash screen,
   * with or without a valid signature (with = for preview purposes, without = for inserting it
   * into the splash input field in the editor).
   *
   * @param file The actual file object, or false if a relevant splash file object was not previously found
   *             by using the locateSplashFileObjectForMediaFileHref() function.
   * @param strip_signature Whether to leave the image signature in or strip it out.
   * @param splash_name The actual file object key to look up as a splash image. Defaults to "splash".
   * @returns {boolean|Object} Returns the actual splash file image or false if none was found.
   */
  function getFileSplashImage( file, strip_signature, splash_name ) {
    if ( !file ) {
      return false;
    }

    var splash = false;

    // default name for splash in the file object is "splash"
    // but we might want to set it to a different one, if we for example
    // have a Coconut large splash image file, which is stored under "splash_large"
    if ( typeof( splash_name ) == 'undefined' ) {
      splash_name = 'splash';
    }

    // Perhaps it's a older encoding job without splash_large, so check carfully
    splash = file[splash_name] ? file[splash_name] : file['splash']

    // we remove the signature when we're updating the Editor field, otherwise we leave it in,
    // so we can actually preview the splash
    if ( splash && typeof( strip_signature ) != 'undefined' && strip_signature ) {
      if ( splash.indexOf('?') > -1 ) {
        splash = splash.substring(0, splash.indexOf('?'));

      // Deal with splash images with Bunny Stream token_path
      } else if ( splash.match( /\/bcdn_token=[a-z0-9-_]+&expires=\d+&token_path=[a-z0-9-%]+\//i ) ) {
        splash = splash.replace( /\/bcdn_token=[a-z0-9-_]+&expires=\d+&token_path=[a-z0-9-%]+\//i, '/' );
      }
    }

    return splash;
  }

  function fileUrlIntoShortcodeEditor(href, extra, is_trailer, index) {
    var $url_input;

    if( typeof index == 'number' && index > 0 ) {
      var new_item = fv_player_editor.playlist_item_add();
      fv_player_editor.playlist_index();

      fv_player_editor.set_current_video_to_edit(
        new_item.attr('data-index')
      );

      $url_input = fv_player_editor.get_field( 'src', true );
    } else {
      $url_input = jQuery('.fv_flowplayer_target');
    }

    var splash = locateSplashFileObjectForMediaFileHref(href);

    if ( splash ) {
      splash = getFileSplashImage( splash, true, 'splash_large' );
    }

    $url_input
      .val(href)
      .removeClass('fv_flowplayer_target' )
      .trigger('keyup')   // this changes the HLS key field visibility in FV Player Pro
      .trigger('change'); // this check the video duration etc.

    // Update the block attributes, if we are not updating a playlist video!
    var clientId = jQuery('.is-selected[data-type="fv-player-gutenberg/basic"]').data('block');

    if( clientId && fv_player_editor.get_current_video_index() < 1 ) {
      var data = {};
      data.src = href;
      if ( splash ) {
        data.splash = splash;
      }
      if ( extra && extra.timeline_previews ) {
        data.timeline_previews = extra.timeline_previews;
      }
      if (extra && extra.hlskey) {
        data.hls_hlskey = extra.hlskey;
      }

      wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( clientId, data );
    }

    var are_we_picking_the_video = $url_input.attr('id') && $url_input.attr('id').match(/^fv_wp_flowplayer_field_src/);

    if( splash && are_we_picking_the_video ) {
      var splash_input = fv_player_editor.get_field('splash', true);
      if( splash_input.val() == '' ) {
        splash_input.val(splash);
      }
    }

    if( !is_trailer ) {
      if( extra && window.fv_player_editor ) {
        fv_player_editor.get_field( 'hls_hlskey', true ).val( extra.hlskey ).trigger( 'change' );

        fv_player_editor.get_field( 'timeline_previews', true ).val( extra.timeline_previews ).trigger( 'change' );

        if( extra.sd_download ) fv_player_editor.get_field( 'download_sd', true ).val( extra.sd_download ).trigger( 'change' );

        if( extra.hd_download ) fv_player_editor.get_field( 'download_hd', true ).val( extra.hd_download ).trigger( 'change' );

        fv_player_editor.get_field( 'encoding_job_id', true).val( extra.encoding_job_id ).trigger( 'change' );

        if( extra.sd_download || extra.hd_download ) {
          fv_player_editor.get_field('download_enabled').prop('checked', true).trigger('change');
        }

        if( extra.title ) {
          let title_input = fv_player_editor.get_field( 'caption', true );
          if( title_input.val() == '' ) {
            title_input.val( extra.title ).trigger( 'change' );
          }
        }
      }

    }

    if ( window.fv_player_editor ) {
      var audio_checkbox = fv_player_editor.get_field('audio', true);
      if( extra && extra.mime ) {
        if( extra.mime.indexOf('audio') !== -1 ) {
          audio_checkbox.prop( "checked", true );
        } else {
          audio_checkbox.prop( "checked", false );
        }
      }
    }

    return false;
  }

  function confirm_uploader_close() {
    if( fv_player_media_browser.get_upload_status() ) {
      if( confirm('You are currently uploading video, closing uploader will stop it. Do You want to close ?') ) {
        jQuery('.media-modal-backdrop').click();
      }
    }
  }

  $( document ).on( "mousedown touchstart", ".media-modal-backdrop", confirm_uploader_close );
  $( document ).on( "mousedown touchstart", '.media-modal-close', confirm_uploader_close );

  $( document ).on( "click", "#overlay-loader-li", function() {
    // click the Load More button when the actual DIV is clicked, for accessibility
    jQuery(this).find('button').click();
  });

  $( document ).on( "click", ".folders:not(#overlay-loader-li), .breadcrumbs a", function(event) {
    var
      $frame = fv_player_media_browser.get_frame_el(),
      activeTabId = fv_player_media_browser.get_active_tab_id(),
      activeTab = fv_player_media_browser.get_active_tab_config(),
      allowMultiple = ! activeTab || activeTab.multiple !== false,
      assetsLoadingFunction = (activeTabId && fv_flowplayer_browser_assets_loaders[activeTabId] ? fv_flowplayer_browser_assets_loaders[activeTabId] : function() {});

    // wp adds on click .details, we need to remove it if element doesnt have class .selected
    $frame.find('.attachment.details:not(.selected)').removeClass('details');

    // coming directly from a link
    if (this.tagName == 'A') {
      $frame.find('#media-search-input').val(''); // remove search when clicked on folder
      // disable Choose button
      fv_player_media_browser.set_choose_button_disabled(true);
      // load folder contents
      assetsLoadingFunction($frame.find('#browser-dropdown').val(), jQuery(this).attr('href'));
    } else {
      // coming from a LI where the link is located
      var
        $e = jQuery(this),
        href = $e.find('a:first').attr('href');

      if (typeof (href) != 'undefined') {
        $frame.find('#media-search-input').val(''); // remove search when clicked on folder
        // disable Choose button
        fv_player_media_browser.set_choose_button_disabled(true);
        // load folder conents
        assetsLoadingFunction($frame.find('#browser-dropdown').val(), href);
      } else {
        // we clicked on a file, not a folder... add a confirmation tick icon to it
        var wasSelected = $e.hasClass('selected'),
          isMultiSelect = allowMultiple && fv_flowplayer_browser_holding_meta_key;

        if (!isMultiSelect) {
          $frame.find('#__assets_browser li.selected')
            .attr('aria-checked', 'false')
            .removeClass('selected details');
        }

        // if we click on same selected LI while holding meta key, deselect it
        if (wasSelected && isMultiSelect) {
          $e
            .attr('aria-checked', 'false')
            .removeClass('selected details');
        }

        // if we clicked on the same selected LI, don't re-select it, as we just deselected it
        if (!wasSelected) {
          $e
            .attr('aria-checked', 'true')
            .addClass('selected details');

          var
            $filenameDiv = $e.find('.filename div'),
            extra = $filenameDiv.data('extra') || {},
            $sidebar = $frame.find('.media-sidebar');

          if ( activeTab && typeof activeTab.render_sidebar === 'function' ) {
            var sidebarContent = activeTab.render_sidebar( $e, $filenameDiv, extra );
            if ( sidebarContent !== false ) {
              $sidebar.empty().append( sidebarContent );
            }
          } else {
            var
              fSize = parseInt($filenameDiv.data('size')),
              fSizeTextual = fSize != $filenameDiv.data('size'),
              fDuration = parseInt($filenameDiv.data('duration')),
              sizeSuffix = 'bytes',
              fExtraDisplayData = extra.displayData;

            if (!fSizeTextual) {
              // if filesize is too small, show it in KBytes
              if (fSize > -1) {
                if (fSize > 10000) {
                  if (fSize <= 999999) {
                    fSize /= 100000;
                    sizeSuffix = 'KB';
                  } else if (fSize <= 999999999) {
                    fSize /= 1000000;
                    sizeSuffix = 'MB';
                  } else {
                    fSize /= 1000000000;
                    sizeSuffix = 'GB';
                  }
                }

                // "round" to 2 decimals
                if (parseFloat(fSize) != parseInt(fSize)) {
                  fSize += '';
                  fSize = fSize.substring(0, fSize.indexOf('.') + 3);
                }
              }
            } else {
              // if there's a non-numeric filesize, just display that
              fSize = $filenameDiv.data('size');
            }

            if (fDuration && fDuration > 0) {
              var sec_num = parseInt(fDuration, 10); // don't forget the second param
              var hours = Math.floor(sec_num / 3600);
              var minutes = Math.floor((sec_num % 3600) / 60);
              var seconds = Math.floor(sec_num % 60);

              if (hours < 10) {
                hours = "0" + hours;
              }
              if (minutes < 10) {
                minutes = "0" + minutes;
              }
              if (seconds < 10) {
                seconds = "0" + seconds;
              }

              fDuration = hours + ':' + minutes + ':' + seconds;
            } else {
              fDuration = 'Processing Media...';
            }

            // load splash image
            var
              linkStr = String($filenameDiv.data('link') || ''),
              isPicture = linkStr.match(/\.(jpg|jpeg|png|gif)$/),
              splashValue = getFileSplashImage( locateSplashFileObjectForMediaFileHref( linkStr ) ),
              splash = (isPicture ? $e.find('.icon').get(0).outerHTML : '<img src="' + splashValue + '" draggable="false" class="icon thumb" loading="lazy" />');

            // if we didn't find a splash image for a media file,
            // use its icon
            if (!splashValue) {
              splash = $e.find('.icon').get(0).outerHTML;
            }

            // show info about the file in right sidebar
            $sidebar.html('<div tabindex="0" class="attachment-details save-ready" style="overflow: visible">\n' +
              '\t\t<h2>Media Details</h2>\n' +
              '\t\t<div class="attachment-info">\n' +
              '\t\t\t<div class="thumbnail thumbnail-image">\n' +
              '\t\t\t\t\n' +
              '\t\t\t\t\t' + splash + '\n' +
              '\t\t\t\t\n' +
              '\t\t\t</div>\n' +
              '\t\t\t<div class="details">\n' +
              '\t\t\t\t<div class="filename">' + $filenameDiv.text() + '</div>\n' +
              '\t\t\t\t<div class="uploaded">' + ($filenameDiv.data('modified') != 'undefined' ? $filenameDiv.data('modified') : fSize) + '</div>\n' +
              '\n' +
              '\t\t\t\t<div class="file-size">' + (!fSizeTextual ? (fSize > -1 ? fSize + ' ' + sizeSuffix : fDuration) : '') + '</div>\n' +
              (fExtraDisplayData ? '\t\t\t\t<div class="uploaded"><br /><strong><em>' + fExtraDisplayData + '</em></strong></div>\n' : '') +
              '\t\t\t</div>\n' +
              ( ( splashValue && isPicture ) ? '<div><i>Found matching splash screen image</i></div>' : '') +
              '\t\t</div>\n' +
              '\n' +
              '\t\t\n' +
              '\t\t\n' +
              '\t\t\t<label class="setting" data-setting="url">\n' +
              '\t\t\t<span class="name">Copy Link</span>\n' +
              '\t\t\t<input type="text" value="' + linkStr + '" readonly="">\n' +
              '\t\t</label>\n' +
              '\t\t' + ( (extra && extra.trailer_src != undefined ) ? '<button type="button" class="button media-button trailer-button-select">Select Trailer</button>' : '' ) +
              '\t</div>');
          }

          // if this item is unselectable (i.e. a Coconut job that errored-out or encoding job that's still being processed externally), disable the Choose button
          if ( extra && extra.disabled ) {
            fv_player_media_browser.set_choose_button_disabled(true);
          } else {
            // enable Choose button
            fv_player_media_browser.set_choose_button_disabled(false);
          }
        } else {
          // disable Choose button and clear sidebar if nothing is selected
          if ($frame.find('#__assets_browser li.selected').length === 0) {
            $frame.find('.media-sidebar').empty();
            fv_player_media_browser.set_choose_button_disabled(true);
          }
        }

        $lastElementSelected = $e;
      }
    }
    return false;
  });

  $( document ).on( "click", ".folders .check", function(event) {
    if ( !fv_player_media_browser.is_fv_tab_active() ) {
      return;
    }

    var $frame = fv_player_media_browser.get_frame_el(),
      $element = jQuery(this).closest('li');

    // deselect media element
    $element
      .attr('aria-checked', 'false')
      .removeClass('selected details');

    $lastElementSelected = null;

    // disable Choose button and clear sidebar if nothing remains selected
    if ($frame.find('#__assets_browser li.selected').length === 0) {
      $frame.find('.media-sidebar').empty();
      fv_player_media_browser.set_choose_button_disabled(true);
    }

    return false;
  });

  $( document ).on( "click", ".media-button-select", function() {
    if ( !fv_player_media_browser.is_fv_tab_active() ) {
      return;
    }

    var frame = fv_player_media_browser.get_frame(),
      $frame = fv_player_media_browser.get_frame_el(),
      activeTab = fv_player_media_browser.get_active_tab_config(),
      selected_media_items = $frame.find('#__assets_browser li.selected');

    function close_active_frame() {
      if ( frame && typeof frame.close === 'function' ) {
        frame.close();
      } else {
        jQuery('.media-modal-close:visible').trigger( 'click' );
      }
    }

    if ( activeTab && typeof activeTab.on_select === 'function' ) {
      activeTab.on_select( selected_media_items, frame );
      close_active_frame();
      return false;
    }

    selected_media_items.each(function (index, $e) {
      $e = jQuery($e);

      var filenameDiv = $e.find('.filename div');

      if (filenameDiv.length && filenameDiv.data('link')) {
        fileUrlIntoShortcodeEditor(filenameDiv.data('link'), filenameDiv.data('extra'), false, index);
      }
    });

    // close media browser
    close_active_frame();

    // show playlist if multiple items were inserted
    if ( selected_media_items.length > 1 ) {
      setTimeout( function() {
        fv_player_editor.playlist_show();
      }, 0 );
    }

    return false;
  });

  $( document ).on( "click", ".trailer-button-select", function(event) {
    var
      frame = fv_player_media_browser.get_frame(),
      $frame = fv_player_media_browser.get_frame_el(),
      $e = $frame.find('#__assets_browser li.selected'),
      filenameDiv = $e.find('.filename div');

    if (filenameDiv.length && filenameDiv.data('extra') && filenameDiv.data('extra').trailer_src != undefined && filenameDiv.data('extra').trailer_src[0] != undefined ) {
      fileUrlIntoShortcodeEditor(filenameDiv.data('extra').trailer_src[0], filenameDiv.data('extra'), true);
    }

    // close media browser
    if ( frame && typeof frame.close === 'function' ) {
      frame.close();
    } else {
      jQuery('.media-modal-close:visible').trigger( 'click' );
    }

    return false;
  });

  // listen to a drop event on one of our browser tabs and hide the drop overlay
  // since WP does not do this for us apart from in its own upload tab
  $( document ).on( "media_browser_drop_event", function() {
    if ( fv_player_media_browser.get_active_tab().hasClass('upload_supported') ) {
      $( '.media-modal .uploader-window' ).css({
        'display' : 'none',
        'opacity' : 0,
      });
    }
  });

  // set fv_flowplayer_browser_holding_meta_key to true when meta or ctrl key is pressed
  $( document ).on( "keydown", function(event) {
    if ( event.metaKey || event.ctrlKey  ) {
      fv_flowplayer_browser_holding_meta_key = true;
    }
  });

  // set fv_flowplayer_browser_holding_meta_key to false when meta or ctrl key is released
  $( document ).on( "keyup", function(event) {
    if ( !event.metaKey && !event.ctrlKey ) {
      fv_flowplayer_browser_holding_meta_key = false;
    }
  });

});

/*
 * Compatibility with older FV Player Pro versions which do not send the nonce for their media browsers
 *
 * fv_flowplayer_browser.nonces is printed by FV_Player_Media_Browser::init_base(),
 * keyed by the AJAX action, like { load_vimeo_assets: '...', load_s3_assets: '...' }
 */
( function( $ ) {

  function get_browser_action( data ) {
    if ( typeof data !== 'string' || ! window.fv_flowplayer_browser || ! window.fv_flowplayer_browser.nonces ) {
      return false;
    }

    var action = new URLSearchParams( data ).get( 'action' );
    if ( action && Object.prototype.hasOwnProperty.call( window.fv_flowplayer_browser.nonces, action ) ) {
      return action;
    }

    return false;
  }

  /*
   * Only add the nonce to POST requests going to the WordPress AJAX handler
   * or to controller/s3-ajax.php, so that it never goes to another host or into the URL.
   */
  function is_allowed_request( options ) {
    // jQuery only uppercases the type after running the prefilters, jQuery.post() gives 'post'
    if ( String( options.type ).toUpperCase() !== 'POST' ) {
      return false;
    }

    var request_url;
    try {
      request_url = new URL( options.url, document.baseURI );
    } catch ( e ) {
      return false;
    }

    var allowed_urls = [ window.ajaxurl, window.fv_flowplayer_browser.ajaxurl ];

    for ( var i = 0; i < allowed_urls.length; i++ ) {
      if ( typeof allowed_urls[ i ] !== 'string' || ! allowed_urls[ i ] ) {
        continue;
      }

      var allowed_url = new URL( allowed_urls[ i ], document.baseURI );
      if ( request_url.origin === allowed_url.origin && request_url.pathname === allowed_url.pathname ) {
        return true;
      }
    }

    return false;
  }

  /*
   * Older FV Player Pro versions do not send the nonce for their media browsers
   * (Vimeo, DigitalOcean Spaces, PeerTube Private), add it.
   * jQuery has already serialized the data into a string at this point.
   */
  $.ajaxPrefilter( function( options ) {
    var action = get_browser_action( options.data );
    if ( ! action || ! is_allowed_request( options ) ) {
      return;
    }

    var params = new URLSearchParams( options.data );
    if ( ! params.get( 'nonce' ) ) {
      console.log( 'FV Player: Media browser: Adding nonce for action: ' + action );

      params.set( 'nonce', window.fv_flowplayer_browser.nonces[ action ] );
      options.data = params.toString();
    }
  } );

} )( jQuery );
