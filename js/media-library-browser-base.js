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
        return jQuery('.media-menu-item.active:visible');
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
    width = jQuery('#__assets_browser').width(),
    idealColumnWidth = jQuery( window ).width() < 640 ? 135 : 150;

  if ( width ) {
    var columns = Math.min( Math.round( width / idealColumnWidth ), 12 ) || 1;
    jQuery('#__assets_browser')
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
  jQuery('#overlay-loader-li').append($moreDiv);
}

// retrieves options and data for media browser and refreshes its content
function fv_flowplayer_browser_browse(data, options) {

  var
    filemanager = jQuery('.attachments-browser'),
    breadcrumbs = jQuery('.breadcrumbs'),
    fileList = filemanager.find('.data'),
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
    var timedSearchTask = -1;
    // remove any previous binds to the same input element
    // which could originate from other browsers (and would generate duplicate AJAX calls)
    jQuery('#media-search-input').off('input');
    jQuery('#media-search-input').on('input', function (e) {
      var searchVal = jQuery(this).val();

      // if we have old search timed task, cancel it and create a new one
      if (timedSearchTask > -1) {
        clearTimeout(timedSearchTask);
      }

      timedSearchTask = setTimeout(function() {
        options.ajaxSearchCallback();
        timedSearchTask = -1;

        var checkExist = setInterval(function() {
          if (jQuery('#media-search-input').length) {
            jQuery('#media-search-input').val(searchVal);
            clearInterval(checkExist);
          }
       }, 100); // check every 100ms
      }, 1000);
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
          + '<div data-modified="' + f.modified + '" data-size="' + f.size + '" data-link="' + f.link + '"' + (f.duration ? ' data-duration="' + f.duration + '"' : '') + ' data-extra=\''+JSON.stringify(f.extra)+'\'>' + name + '</div>'
          + '</div>'
          + progress
          + '</div>'
          + '</div>' +
          '<button type="button" class="check" tabindex="0">' +
          '<span class="media-modal-icon"></span>' +
          '<span class="screen-reader-text">Deselect</span>' +
          '</button>');

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

// adds new tab on top of the Media Library popup
function fv_flowplayer_media_browser_add_tab(tabId, tabText, tabOnClickCallback, tabAddedCallback, tabClickEventCallback) {
  var $tab = jQuery('#' + tabId),
    input_name = jQuery('.fv_flowplayer_target').attr('name');

  // Bail if it's a tab which provides video files only (Bunny Stream, Cloudflare Stream etc.) and it's not the video src field
  if (
    // input_name must be set, it's not set when clicking on the "Select Media" button in the block and we don't want to remove the tab in that case
    input_name && input_name.length &&
    [
      'fv_player_bunny_stream_browser_media_tab',
      'fv_flowplayer_cloudflare_stream_browser_media_tab',
      'fv_player_coconut_browser_media_tab',
      'fv_flowplayer_peertube_private_browser_media_tab',
      'fv_flowplayer_vimeo_browser_media_tab',
      'fv_player_cloudflare_stream_browser_media_tab'
    ].indexOf( tabId ) > -1
     &&
    [
      'fv_wp_flowplayer_field_src',
      'fv_wp_flowplayer_field_src1',
      'fv_wp_flowplayer_field_src2',
      'fv-player-gutenberg-media',
      'fv_wp_flowplayer_field_ppv_preview'
    ].indexOf( input_name ) == -1
  ) {

    if ( $tab.length ) {
      $tab.remove();
    }

    return $tab;
  }

  if (!$tab.length) {
    var
      $router = jQuery('.media-router:visible'),
      $nativeTabs = $router.find('.media-menu-item:not(.artificial)'),
      $item = jQuery($nativeTabs[$nativeTabs.length - 1]).clone(),
      switchClicking = false;

    // remove active class
    $item.removeClass('active');

    // save assets loading function
    fv_flowplayer_browser_assets_loaders[tabId] = tabOnClickCallback;

    // this is a super-ugly hack to circumvent heavily complicated Backbone WP programming...
    // on our browser tab click, WP still thinks the last tab is actually still the active tab
    // and won't allow us to click on that same tab again to show it, so we'll have to programmatically
    // click another Backbone-created tab to update its internal pointers
    // TODO: study up on Backbone WP functionality and fix this hack by at least showing the correct Backbonw view!
    $nativeTabs.each(function() {
      var
        $e = jQuery(this),
        $prev = $e.prev(),
        $next = $e.next();

      // clickbaited is class name determining whether we've already
      // applied this hackish logic to tab clicks
      if (!$e.hasClass('clickbaited')) {
        $e.addClass('clickbaited');
        $e.on('click', function() {
          fv_flowplayer_media_browser_disable_drag_drop(false);

          fv_flowplayer_media_browser_show_upload( jQuery(this).attr('id') );

          if (!switchClicking) {
            switchClicking = true;
            // find a tab that is native and is not our clicked tab and click on it
            if ($prev.length && !$prev.hasClass('artificial')) {
              $prev.trigger('click');
            } else {
              $next.trigger('click');
            }

            // then click back on our tab to activate it
            $e.trigger('click');
            switchClicking = false;
          }
        });
      }
    });

    $item
      .attr('id', tabId)
      .text(tabText)
      .addClass('artificial')
      .on('click', function() {
        // disable Choose button
        jQuery('.media-button-select').prop('disabled', 'disabled');
        $router.find('.media-menu-item.active').removeClass('active');
        jQuery(this).addClass('active');

        fv_flowplayer_media_browser_disable_drag_drop(true);
        fv_flowplayer_media_browser_show_upload( jQuery(this).attr('id') );

        // execute tab click function
        if (typeof(tabClickEventCallback) == 'function' && !switchClicking) {
          tabClickEventCallback();
        }

        // store last clicked tab ID
        try {
          if (typeof(window.localStorage) == 'object') {
            localStorage.setItem('fv_player_last_tab_selected', tabId);
          }
        } catch(e) {}

        jQuery('#media-search-input').val('');

        // hide the Drop files to upload modal initially
        jQuery('.media-modal .uploader-window').css({
          'display' : 'none',
          'opacity' : 0,
        });

        return tabOnClickCallback();
      });

    $router.append($item);

    // if we have a callback function to call once the tab has been added,
    // do it here
    if (typeof(tabAddedCallback) == 'function') {
      tabAddedCallback($item);
    }

    $tab = $item;
  }

  // if this tab was the last active, make it active again
  try {
    if ( typeof window.localStorage == "object" && window.localStorage.fv_player_last_tab_selected && window.localStorage.fv_player_last_tab_selected == tabId ) {
      // do this async, so the browser has time to paint the UI
      // and change class of this tab to active on click
      setTimeout(function() {
        jQuery('#' + tabId).trigger( 'click' );
      }, 500);
    }
  } catch(e) {}

  return $tab;
}

/*
 * Disable/enable core WordPress drag&drop uploader
 */
function fv_flowplayer_media_browser_disable_drag_drop( disable ) {
  var
    overlay = jQuery('.media-frame-uploader'),
    overlay_content = jQuery('.media-modal .uploader-window'),
    overlay_title = overlay_content.find('.uploader-editor-title'),
    drop_targets = jQuery('[id^=__wp-uploader-id-'),
    upload_supported = fv_player_media_browser.get_active_tab().hasClass( 'upload_supported' );

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

    overlay_title.html( original_title + ' to ' + jQuery('.media-router:visible button.active').text() );

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
  jQuery('.media-toolbar-secondary > .fv-player-upload_buttons[data-tab-id='+id+']').show();
}

function renderBrowserPlaceholderHTML(options) {
  var html = '<div class="attachments-browser"><div class="media-toolbar s3-media-toolbar">';

  if (options && options.dropdownItems) {
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
    '\t\t<div class="breadcrumbs"></div>\n' +
    '\n';

  if (options.errorMsg) {
    html += '<div class="errors"><strong>' + options.errorMsg + '</strong></div><hr /><br />';
  }

  html += '\t\t<ul tabindex="-1" class="data attachments ui-sortable ui-sortable-disabled" id="__assets_browser"></ul>\n' +
    '<div class="media-sidebar"></div>' +
    '\t\t<div class="nothingfound">\n' +
    '\t\t\t<div class="nofiles"></div>\n' +
    '\t\t\t<p>No files here.</p>\n' +
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
      activeTabId = jQuery( '.media-router:visible .media-menu-item.active' ).attr('id');

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
      var editor_splash = splash ? splash : '',
        editor_timeline_previews = extra && extra.timeline_previews ? extra.timeline_previews : '';
        editor_hlskey = extra && extra.hlskey ? extra.hlskey : '';

      wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(clientId, { src: href, splash: editor_splash, timeline_previews: editor_timeline_previews, hls_hlskey: editor_hlskey });
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
      activeTabId = jQuery('.media-modal:visible .media-router .media-menu-item.active').attr('id'), // TODO: API to get back to actual tab
      assetsLoadingFunction = (activeTabId && fv_flowplayer_browser_assets_loaders[activeTabId] ? fv_flowplayer_browser_assets_loaders[activeTabId] : function() {});

    $('#media-search-input').val(''); // remove search when clicked on folder

    // deselect all items if not holding the meta key
    if ( !fv_flowplayer_browser_holding_meta_key && jQuery('#__assets_browser li.selected').length > 1 ) {
      jQuery('#__assets_browser li.selected').removeClass('selected');
    }

    // wp adds on click .details, we need to remove it if element doesnt have class .selected
    jQuery('.wp-core-ui .attachment.details:not(.selected)').removeClass('details');

    // coming directly from a link
    if (this.tagName == 'A') {
      // disable Choose button
      jQuery('.media-button-select').prop('disabled', 'disabled');
      // load folder contents
      assetsLoadingFunction(jQuery('#browser-dropdown').val(), jQuery(this).attr('href'));
    } else {
      // coming from a LI where the link is located
      var
        $e = jQuery(this),
        href = $e.find('a:first').attr('href');

      if (typeof (href) != 'undefined') {
        // disable Choose button
        jQuery('.media-button-select').prop('disabled', 'disabled');
        // load folder conents
        assetsLoadingFunction(jQuery('#browser-dropdown').val(), href);
      } else {
        // we clicked on a file, not a folder... add a confirmation tick icon to it
        var wasSelected = $e.hasClass('selected');

        if ($lastElementSelected !== null && !fv_flowplayer_browser_holding_meta_key) {
          $lastElementSelected
            .attr('aria-checked', 'false')
            .removeClass('selected details');
        }

        // if we click on same selected LI while holding meta key, deselect it
        if (wasSelected && fv_flowplayer_browser_holding_meta_key) {
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
            fSize = parseInt($filenameDiv.data('size')),
            fSizeTextual = fSize != $filenameDiv.data('size'),
            fDuration = parseInt($filenameDiv.data('duration')),
            sizeSuffix = 'bytes',
            fExtraDisplayData = $filenameDiv.data('extra');
            fExtraDisplayData = fExtraDisplayData.displayData;

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
            isPicture = $filenameDiv.data('link').match(/\.(jpg|jpeg|png|gif)$/),
            splashValue = getFileSplashImage( locateSplashFileObjectForMediaFileHref( $filenameDiv.data('link') ) ),
            splash = (isPicture ? $e.find('.icon').get(0).outerHTML : '<img src="' + splashValue + '" draggable="false" class="icon thumb" loading="lazy" />');

          // if we didn't find a splash image for a media file,
          // use its icon
          if (!splashValue) {
            splash = $e.find('.icon').get(0).outerHTML;
          }

          // show info about the file in right sidebar
          jQuery('.media-sidebar').html('<div tabindex="0" class="attachment-details save-ready" style="overflow: visible">\n' +
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
            ( ( splashValue && $filenameDiv.data('link').match(/\.(jpg|jpeg|png|gif)$/) ) ? '<div><i>Found matching splash screen image</i></div>' : '') +
            '\t\t</div>\n' +
            '\n' +
            '\t\t\n' +
            '\t\t\n' +
            '\t\t\t<label class="setting" data-setting="url">\n' +
            '\t\t\t<span class="name">Copy Link</span>\n' +
            '\t\t\t<input type="text" value="' + $filenameDiv.data('link') + '" readonly="">\n' +
            '\t\t</label>\n' +
            '\t\t' + ( ($filenameDiv.data('extra') != 'undefined' && $filenameDiv.data('extra').trailer_src != undefined ) ? '<button type="button" class="button media-button trailer-button-select">Select Trailer</button>' : '' ) +
            '\t</div>');

          // if this item is unselectable (i.e. a Coconut job that errored-out or encoding job that's still being processed externally), disable the Choose button
          if ( $filenameDiv.data('extra') && $filenameDiv.data('extra').disabled ) {
            jQuery('.media-button-select').prop('disabled', 'disabled');
          } else {
            // enable Choose button
            jQuery('.media-button-select').removeAttr('disabled');
          }
        } else {
          // disable Choose button if nothing is selected
          if(jQuery('.wp-core-ui .attachment.selected').length === 0) jQuery('.media-button-select').prop('disabled', 'disabled');
        }

        $lastElementSelected = $e;
      }
    }
    return false;
  });

  $( document ).on( "click", ".check .media-modal-icon", function(event) {
    var $element = jQuery(this).closest('li');

    // deselect media element
    $element
      .attr('aria-checked', 'false')
      .removeClass('selected details');

    $lastElementSelected = null;

    // disable Choose button
    jQuery('.media-button-select').prop('disabled', 'disabled');

    return false;
  });

  $( document ).on( "click", ".media-button-select", function() {
    let selected_media_items = jQuery('#__assets_browser li.selected');

    selected_media_items.each(function (index, $e) {
      $e = jQuery($e);

      var filenameDiv = $e.find('.filename div');

      if (filenameDiv.length && filenameDiv.data('link')) {
        fileUrlIntoShortcodeEditor(filenameDiv.data('link'), filenameDiv.data('extra'), false, index);
      }
    });

    // close media browser
    jQuery('.media-modal-close:visible').trigger( 'click' );

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
      $e = jQuery('#__assets_browser li.selected'),
      filenameDiv = $e.find('.filename div');

    if (filenameDiv.length && filenameDiv.data('extra').trailer_src[0] != undefined ) {
      fileUrlIntoShortcodeEditor(filenameDiv.data('extra').trailer_src[0], filenameDiv.data('extra'), true);
    }

    // close media browser
    jQuery('.media-modal-close:visible').trigger( 'click' );

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
