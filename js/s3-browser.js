var
  fv_flowplayer_scannedFolders = [],
  fv_flowplayer_scannedFiles = [],
  fv_flowplayer_media_browser_columns = 7,
  fv_flowplayer_idealColumnWidth = jQuery( window ).width() < 640 ? 135 : 150;

// this function is from WP JS
function fv_flowplayer_media_browser_setColumns() {
  var  width = jQuery('#__s3-view').width(); // from WP

  if ( width ) {
    var columns = Math.min( Math.round( width / fv_flowplayer_idealColumnWidth ), 12 ) || 1;
    jQuery('#__s3-view').closest( '.media-frame-content' ).attr( 'data-columns', columns );
    fv_flowplayer_media_browser_columns = columns;
  }
}

jQuery( function($) {
    var
      $lastElementSelected = null;

    // calculate number of columns on each window resize
    jQuery(window).on('resize', function() {
      setTimeout(fv_flowplayer_media_browser_setColumns, 500);
    });

    function fv_flowplayer_media_browser_add_tab(tabId, tabText, tabOnClickCallback) {
      if (!jQuery('#' + tabId).length) {
        // add Vimeo browser tab
        var
          $router = jQuery('.media-router:visible'),
          $lastItem = $router.find('.media-menu-item:not(.artificial):last'),
          $firstItem = $router.find('.media-menu-item:first'),
          $item = $lastItem.clone(),
          switchClicking = false;

        // this is a super-ugly hack to circumvent heavily complicated Backbone WP hackery,
        // since on our browser tab click, WP still thinks Media Library is actually the active tab
        // and won't allow us to click on that tab to actually show Media Library
        // TODO: study up on Backbone WP functionality and fix this hack by at least showing the correct Backbonw view!
        if (!$lastItem.hasClass('clickbaited')) {
          $lastItem.addClass('clickbaited');
          $lastItem.on('click', function() {
            if (!switchClicking) {
              switchClicking = true;
              $firstItem.click();
              $lastItem.click();
              switchClicking = false;
            }
          });
        }

        $item
          .attr('id', tabId)
          .text(tabText)
          .addClass('artificial')
          .on('click', tabOnClickCallback);

        $router.append($item);
      }
    };

    function fv_flowplayer_s3_browser_load_assets(bucket,path) {
      var
        $this = jQuery(this),
        $media_frame_content = jQuery('.media-frame-content:visible'),
        $overlay_div = jQuery('#fv-player-shortcode-editor-preview-spinner').clone().css({
          'height' : '100%'
        }),
        ajax_data = {
          action: "load_s3_assets",
        };

      $this.addClass('active').siblings().removeClass('active');
      $media_frame_content.attr('data-columns', 7);
      // remove infinite scroll checks from other tabs
      $media_frame_content.off('mousewheel');
      $media_frame_content.html($overlay_div);

      if (typeof bucket === 'string' && bucket) {
        ajax_data['bucket'] = bucket;
      }
      if (typeof path === 'string' && path) {
        ajax_data['path'] = path;
      }

      jQuery.post(ajaxurl, ajax_data, function(ret) {
        var
          html = '<div class="attachments-browser"><div class="media-toolbar s3-media-toolbar">',
          last_selected_bucket = null;

        if (ret.buckets) {
          html += '<div class="media-toolbar-secondary">';

          // prepare dropdown HTML
          var
            select_html = '<label for="bucket-dropdown" class="screen-reader-text">S3 Bucket</label>'
              + '<select name="bucket-dropdown" id="bucket-dropdown" class="attachment-filters">',
            one_bucket_enabled = false;

          for (var i in ret.buckets) {
            select_html += '<option value="' + ret.buckets[i].id + '"' + (ret.active_bucket_id == ret.buckets[i].id ? ' selected="selected"' : '') + '>' + ret.buckets[i].name + '</option>'

            if (ret.buckets[i].id > -1) {
              one_bucket_enabled = true;
            }
          }

          select_html += '</select><span class="spinner"></span>';

          // check if we have at least a single enabled bucket
          // and if not, replace the whole select HTML with a warning message
          if (!one_bucket_enabled) {
            select_html = '<strong>You have no S3 buckets configured <a href="options-general.php?page=fvplayer#postbox-container-tab_hosting">in settings</a> or none of them has complete settings (region, key ID and secret key).</strong>';
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

        if (ret.err) {
          html += '<div class="errors"><strong>' + ret.err + '</strong></div><hr /><br />';
        }

        html += '\t\t<ul tabindex="-1" class="data attachments ui-sortable ui-sortable-disabled" id="__s3-view"></ul>\n' +
          '<div class="media-sidebar"></div>' +
          '\t\t<div class="nothingfound">\n' +
          '\t\t\t<div class="nofiles"></div>\n' +
          '\t\t\t<span>No files here.</span>\n' +
          '\t\t</div>\n' +
          '\n' +
          '\t</div>';

        $media_frame_content.html(html);

        // hide search, as it's not supported for AWS
        jQuery('#media-search-input').parent().hide();

        jQuery('#bucket-dropdown').on('change', function() {
          if (this.value >= 0) {
            // disable Choose button
            jQuery('.media-button-select').prop('disabled', 'disabled');
            // load bucket contents
            fv_flowplayer_s3_browser_load_assets(this.value);
          } else {
            var $err_div = jQuery('.filemanager .errors');

            if (!$err_div.length) {
              $err_div = jQuery('<div class="errors"></div>');
              $err_div.insertBefore(jQuery('.filemanager .search'));
              $err_div.after('<hr /><br />');
            }

            $err_div.html('<strong>Bucket is missing settings. Please make sure you assigned region, key ID and secret key to this bucket.</strong>');
            return false;
          }
        });

        fv_flowplayer_s3_browse( ret.items );
      } );

      return false;
    };

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

  function fileUrlIntoShortcodeEditor(href) {
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

    $popup_close_btn.click();

    return false;
  }

  $( document ).on( "mediaBrowserOpen", function(event) {
    fv_flowplayer_media_browser_add_tab('fv_flowplayer_s3_browser_media_tab', 'Amazon S3', fv_flowplayer_s3_browser_load_assets);
  });

  $( document ).on( "click", ".folders, .breadcrumbs a", function(event) {
    // coming directly from a link
    if (this.tagName == 'A') {
      // disable Choose button
      jQuery('.media-button-select').prop('disabled', 'disabled');
      // load folder contents
      fv_flowplayer_s3_browser_load_assets( jQuery('#bucket-dropdown').val(), jQuery(this).attr('href') );
    } else {
      // coming from a LI where the link is located
      var
        $e = jQuery(this),
        href = $e.find('a:first').attr('href');
      if (typeof(href) != 'undefined') {
        // disable Choose button
        jQuery('.media-button-select').prop('disabled', 'disabled');
        // load folder conents
        fv_flowplayer_s3_browser_load_assets(jQuery('#bucket-dropdown').val(), href);
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
            fDuration = parseInt($filenameDiv.data('duration')),
            sizeSuffix = 'bytes';

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

          if (fDuration) {
            var sec_num = parseInt(fDuration, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num % 3600) / 60);
            var seconds = Math.floor(sec_num % 60);

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}

            fDuration = hours + ':' + minutes + ':' + seconds;
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
            '\t\t\t\t<div class="uploaded">' + $filenameDiv.data('modified') + '</div>\n' +
            '\n' +
            '\t\t\t\t<div class="file-size">' + (fSize > -1 ? fSize + ' ' + sizeSuffix : fDuration) +'</div>\n' +
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
      $e = jQuery('#__s3-view li.selected'),
      filenameDiv = $e.find('.filename div');

    if (filenameDiv.length && filenameDiv.data('link')) {
      fileUrlIntoShortcodeEditor(filenameDiv.data('link'));
    }

    return false;
  });
});

fv_flowplayer_s3_browse = function(data, options) {

  var filemanager = jQuery('.attachments-browser'),
    breadcrumbs = jQuery('.breadcrumbs'),
    fileList = filemanager.find('.data');

  var response = [data],
    currentPath = '',
    breadcrumbsUrls = [];

  // if we're appending data, don't do all this
  if (!options || !options.append) {
    jQuery(window).off('fv-player-browser-open-folder');
    jQuery(window).on('fv-player-browser-open-folder', function (e, path) {
      currentPath = data.path;
      breadcrumbsUrls.push(data.path);
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


  // Splits a file path and turns it into clickable breadcrumbs
  function generateBreadcrumbs(nextDir){
    var path = nextDir.split('/').slice(0);
    for(var i=1;i<path.length;i++){
      path[i] = path[i-1]+ '/' +path[i];
    }
    return path;
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
          fileSize = typeof(f.size) == "number" ? bytesToSize(f.size) : f.size, // just show the size for placeholders
          name = escapeHTML(f.name),          
          link = f.link ? 'href="'+ f.link+'"' : '',          
          file = jQuery('<li tabindex="0" role="checkbox" aria-label="' + name + '" aria-checked="false" class="folders attachment save-ready"></li>'),
          isPicture = name.match(/\.(jpg|jpeg|png|gif)$/);

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
          + '<div data-modified="' + f.modified + '" data-size="' + f.size + '" data-link="' + f.link + '"' + (f.duration ? ' data-duration="' + f.duration + '"' : '') + '>' + name + '</div>'
          + '</div>'
          + '</div>'
          + '</div>' +
          '<button type="button" class="check" tabindex="0">' +
          '<span class="media-modal-icon"></span>' +
          '<span class="screen-reader-text">Deselect</span>' +
          '</button>');

        file.appendTo(fileList);
      });

    }


    // Generate the breadcrumbs
    var url = '';
    if(filemanager.hasClass('searching')){
      url = '<span>Search results: </span>';
      fileList.removeClass('animated');
    }
    else {
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

    fileList.fadeIn({
      complete: function() {
        setTimeout(fv_flowplayer_media_browser_setColumns, 500);
      }
    });
  }


  // This function escapes special html characters in names

  function escapeHTML(text) {
    return text.replace(/\&/g,'&amp;').replace(/\</g,'&lt;').replace(/\>/g,'&gt;');
  }


  // Convert file sizes from bytes to human readable units

  function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return '0 Bytes';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
  }

//	});
};