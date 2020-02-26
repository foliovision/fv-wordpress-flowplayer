var
  fv_flowplayer_scannedFolders = [],
  fv_flowplayer_scannedFiles = [],
  // object where key->value pairs represent tabId->ajaxAssetsLoadingScript pairs
  // ... we use this to load assets (media files) from SDK of the correct browser integration
  //     depending on which tab is currently active
  fv_flowplayer_browser_assets_loaders = {};

// this thumbnail sizing functionality originally comes from WP JS
function fv_flowplayer_media_browser_setColumns() {
  const
    width = jQuery('#__assets_browser').width(),
    idealColumnWidth = jQuery( window ).width() < 640 ? 135 : 150;

  if ( width ) {
    const columns = Math.min( Math.round( width / idealColumnWidth ), 12 ) || 1;
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

  const
    filemanager = jQuery('.attachments-browser'),
    breadcrumbs = jQuery('.breadcrumbs'),
    fileList = filemanager.find('.data'),
    showBreadcrumbs = (options && options.breadcrumbs ? options.breadcrumbs : false);

  var
    currentPath = '',
    breadcrumbsUrls = [];

  // if we're appending data, don't do all this
  if (!options || !options.append) {
    jQuery(window).off('fv-player-browser-open-folder');
    jQuery(window).on('fv-player-browser-open-folder', function (e, path) {
      currentPath = data.path;

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
      // if we have old search timed task, cancel it and create a new one
      if (timedSearchTask > -1) {
        clearTimeout(timedSearchTask);
      }

      timedSearchTask = setTimeout(function() {
        options.ajaxSearchCallback();
        timedSearchTask = -1;
      }, 1000);
    });
  }

  // Render the HTML for the file manager
  function render(data, options) {

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

    if(!fv_flowplayer_scannedFolders.length && !fv_flowplayer_scannedFiles.length) {
      filemanager.find('.nothingfound').show();
    }
    else {
      filemanager.find('.nothingfound').hide();
    }

    if(fv_flowplayer_scannedFolders.length) {

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
          icon = '<img src="' + f.splash + '" draggable="false" class="icon thumb" title="' + name + '" />';
        } else {
          var fileType = name.split('.');
          if( fileType.length > 1 ) {
            fileType = fileType[fileType.length-1];
            icon = '<span class="icon file f-'+fileType+'" >.'+fileType+'</span>';
          } else {
            icon = '<span class="icon file"></span>';
          }
        }

        file.append('<div class="attachment-preview js--select-attachment type-video subtype-mp4 landscape' + (options && options.extraAttachmentClass ? ' ' + options.extraAttachmentClass : '') + '">'
          + '<div class="thumbnail"' + (isPicture || (options && options.noFileName) ? ' title="' + name + '"' : '') + '>'
          + icon
          + '<div class="filename' + (isPicture || (options && options.noFileName) ? ' hidden' : '') + '">'
          + '<div data-modified="' + f.modified + '" data-size="' + f.size + '" data-link="' + f.link + '"' + (f.duration ? ' data-duration="' + f.duration + '"' : '') + ' data-extra=\''+JSON.stringify(f.extra)+'\'>' + name + '</div>'
          + '</div>'
          + '</div>'
          + '</div>' +
          '<button type="button" class="check" tabindex="0">' +
          '<span class="media-modal-icon"></span>' +
          '<span class="screen-reader-text">Deselect</span>' +
          '</button>');

        file.appendTo(fileList);
      });

      if (options && options.loadMoreButtonAction) {
        fv_flowplayer_browser_add_load_more_button(fileList, options.loadMoreButtonAction);
      }

    }

    // Generate the breadcrumbs
    var url = '';
    if (filemanager.hasClass('searching')){
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
  }

  // This function escapes special html characters in names
  function escapeHTML(text) {
    return text.replace(/\&/g,'&amp;').replace(/\</g,'&lt;').replace(/\>/g,'&gt;');
  }

};

// adds new tab on top of the Media Library popup
function fv_flowplayer_media_browser_add_tab(tabId, tabText, tabOnClickCallback, tabAddedCallback, tabClickEventCallback) {
  if (!jQuery('#' + tabId).length) {
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
          if (!switchClicking) {
            switchClicking = true;
            // find a tab that is native and is not our clicked tab and click on it
            if ($prev.length && !$prev.hasClass('artificial')) {
              $prev.click();
            } else {
              $next.click();
            }

            // then click back on our tab to activate it
            $e.click();
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

        return tabOnClickCallback();
      });

    $router.append($item);

    // if we have a callback function to call once the tab has been added,
    // do it here
    if (typeof(tabAddedCallback) == 'function') {
      tabAddedCallback($item);
    }

  }
  
  // if this tab was the last active, make it active again
  try {
    if ( typeof window.localStorage == "object" && window.localStorage.fv_player_last_tab_selected && window.localStorage.fv_player_last_tab_selected == tabId ) {
      // do this async, so the browser has time to paint the UI
      // and change class of this tab to active on click
      setTimeout(function() {
        jQuery('#' + tabId).click();
      }, 500);
    }
  } catch(e) {}
};

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
    '<input type="search" placeholder="Search media items..." id="media-search-input" class="search">' +
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
    '\t\t\t<span>No files here.</span>\n' +
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

  function getSplashImageForMediaFileHref(href) {
    var find = [ fileGetBase(href) ];
    if( window.fv_player_shortcode_editor_qualities ) {
      Object.keys(fv_player_shortcode_editor_qualities).forEach( function(prefix) {
        var re = new RegExp(prefix+'$');
        if( find[0].match(re) ) {
          find.push( find[0].replace(re,'') );
        }
      });
    }

    var splash = false;
    for( var i in find ) {
      for( var j in fv_flowplayer_scannedFiles ) {
        var f = fv_flowplayer_scannedFiles[j];
        if( f.link.match(/\.(jpg|jpeg|png|gif)$/) && fileGetBase(f.link) == find[i] && f.link != href ) {
          splash = (f.splash ? f.splash : f.link);
        }
      }
    }

    return splash;
  }

  function fileUrlIntoShortcodeEditor(href, extra) {
    var
      $url_input       = jQuery('.fv_flowplayer_target'),
      $popup_close_btn = jQuery('.media-modal-close:visible'),
      splash = getSplashImageForMediaFileHref(href);

    $url_input
      .val(href)
      .removeClass('fv_flowplayer_target' )
      .trigger('keyup')   // this changes the HLS key field visibility in FV Player Pro
      .trigger('change'); // this check the video duration etc.

    if( splash && $url_input.attr('id').match(/^fv_wp_flowplayer_field_src/) ) {
      var splash_input = $url_input.parents('table').find('#fv_wp_flowplayer_field_splash');
      if( splash_input.val() == '' ) {
        splash_input.val(splash);
      }
    }
    
    if( extra && extra.hlskey ) {
      $url_input.closest('table').find('#fv_wp_flowplayer_hlskey').val(extra.hlskey);
    } else {
      $url_input.closest('table').find('#fv_wp_flowplayer_hlskey').val('');
    }
    
    // TODO: Proper API!
    if( extra && extra.encoding_job_id ) {
      $url_input.closest('table').find('#fv_wp_flowplayer_field_encoding_job_id').val(extra.encoding_job_id);
    } else {
      $url_input.closest('table').find('#fv_wp_flowplayer_field_encoding_job_id').val('');
    }
    
    

    $popup_close_btn.click();

    // refresh preview
    jQuery('#fv-player-shortcode-editor-preview-iframe-refresh').click();

    return false;
  }

  $( document ).on( "click", "#overlay-loader-li", function() {
    // click the Load More button when the actual DIV is clicked, for accessibility
    jQuery(this).find('button').click();
  });

  $( document ).on( "click", ".folders:not(#overlay-loader-li), .breadcrumbs a", function(event) {
    var
      activeTabId = jQuery('.media-router .media-menu-item.active').attr('id'),
      assetsLoadingFunction = (activeTabId && fv_flowplayer_browser_assets_loaders[activeTabId] ? fv_flowplayer_browser_assets_loaders[activeTabId] : function() {});

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

        if ($lastElementSelected !== null) {
          $lastElementSelected
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
            sizeSuffix = 'bytes';

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
            splashValue = getSplashImageForMediaFileHref($filenameDiv.data('link')),
            splash = (isPicture ? $e.find('.icon').get(0).outerHTML : '<img src="' + splashValue + '" draggable="false" class="icon thumb" />');

          // if we didn't find a splash image for a media file,
          // use its icon
          if (!splashValue) {
            splash = $e.find('.icon').get(0).outerHTML;
          }

          // show info about the file in right sidebar
          jQuery('.media-sidebar').html('<div tabindex="0" class="attachment-details save-ready">\n' +
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
            '\t\t\t</div>\n' +
            (splashValue ? '<div><i>Found matching splash screen image</i></div>' : '') +
            '\t\t</div>\n' +
            '\n' +
            '\t\t\n' +
            '\t\t\n' +
            '\t\t\t<label class="setting" data-setting="url">\n' +
            '\t\t\t<span class="name">Copy Link</span>\n' +
            '\t\t\t<input type="text" value="' + $filenameDiv.data('link') + '" readonly="">\n' +
            '\t\t</label>\n' +
            '\t</div>');

          // enable Choose button
          jQuery('.media-button-select').removeAttr('disabled');
        } else {
          // disable Choose button
          jQuery('.media-button-select').prop('disabled', 'disabled');
        }

        $lastElementSelected = $e;
      }
    }
    return false;
  });

  $( document ).on( "click", ".check .media-modal-icon", function(event) {
    // deselect media element
    $lastElementSelected
      .attr('aria-checked', 'false')
      .removeClass('selected details');

    $lastElementSelected = null;

    // disable Choose button
    jQuery('.media-button-select').prop('disabled', 'disabled');

    return false;
  });

  $( document ).on( "click", ".media-button-select", function(event) {
    var
      $e = jQuery('#__assets_browser li.selected'),
      filenameDiv = $e.find('.filename div');

    if (filenameDiv.length && filenameDiv.data('link')) {
      fileUrlIntoShortcodeEditor(filenameDiv.data('link'), filenameDiv.data('extra'));
    }

    return false;
  });
});