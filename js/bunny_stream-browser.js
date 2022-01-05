jQuery( function($) {
  var firstLoad = true;

  function fv_player_bunny_stream_browser_load_assets() {
    var
      $this = jQuery(this),
      $media_frame_content = jQuery('.media-frame-content:visible'),
      $overlay_div = jQuery('#fv-player-shortcode-editor-preview-spinner').clone().css({
        'height' : '100%'
      }),
      page = 1,
      ajax_data = {
        action: "load_bunny_stream_jobs",
        cookie: encodeURIComponent(document.cookie),
        page: page
      },
      appending = false,
      allLoaded = false;

    if( window.fv_flowplayer_browser_get_function ) {
      fv_flowplayer_browser_get_function[ 'fv_player_bunny_stream_browser_media_tab' ] = fv_player_bunny_stream_browser_load_assets;
    }

    $this.addClass('active').siblings().removeClass('active')

    function loadMoreFunction(force) {
      if ((!appending && !allLoaded) || force === true) {
        appending = true;
        // reset allLoaded if we're forcing a load after API error
        if (force === true) {
          allLoaded = false;
        }
        page++;
        getBunnyStreamData();
      }
      return false;
    }

    function getBunnyStreamData() {
      // check if we have search data to include
      var searchVal = $('#media-search-input').val();

      // show overlay if we're not appending, otherwise append the overlay and then remove it
      if (firstLoad) {
        $media_frame_content.html($overlay_div);
      } else {
        jQuery('#overlay-loader-li div').html($overlay_div);
      }

      if (searchVal) {
        ajax_data['search'] = searchVal;
      } else {
        delete(ajax_data['search']);
      }

      ajax_data['page'] = page;

       // check if we have any collection selected
       var collectionVal = jQuery('#browser-dropdown').val(),
        collectionName = jQuery('#browser-dropdown option:selected').text();

      if (collectionVal != -1) {
        ajax_data['collection_id'] = collectionVal;
        ajax_data['collection_name'] = collectionName;
      } else {
        delete(ajax_data['collection_id']);
        delete(ajax_data['collection_name']);
      }

      ajax_data['appending'] = (appending ? 1 : 0);
      ajax_data['firstLoad'] = (firstLoad ? 1 : 0);

      jQuery.post(ajaxurl, ajax_data, function(ret) {
        // don't overwrite the page if we've shown the browser for the first time already
        // ... instead, we'll be either clearing and rewriting the UL or appending data to it
        if (firstLoad) {
          var
          renderOptions = {
            'dropdownItems' : [],
            'dropdownItemSelected' : ret.active_collection_link,
            'dropdownDefaultOption' : {
              'value' : -1,
              'text' : 'Choose Collection...'
            }
          };

          // fill dropdown options
          for (var i in ret.collections) {
            renderOptions.dropdownItems.push({
              'value' : ret.collections[i].link,
              'text' : ret.collections[i].name
            });
          }

          // add errors, if any
          if (ret.err) {
            renderOptions['errorMsg'] = ret.err;
          }

          $media_frame_content.html( renderBrowserPlaceholderHTML(renderOptions) );

          // add change event listener to the playlists dropdown
          jQuery('#browser-dropdown').on('change', function() {
            allLoaded = false;
            appending = false;
            page = 1;
            // disable Choose button
            jQuery('.media-button-select').prop('disabled', 'disabled');
            // load collection contents
            fv_player_bunny_stream_browser_load_assets();
          });
        } else if (!appending && !allLoaded) {
          // clear the UL if we're not appending
          jQuery('#__assets_browser').find('li').remove();
        }

        // if our result didn't return any items, there's been an error
        // and we don't need to bother with any display logic below
        if (!ret.items.items) {
          return;
        }

        var
          $ul = jQuery('#__assets_browser'),
          havePendingItems = false;

        firstLoad = false;

        // if we didn't get any items back and we're auto-loading data for infinite scroll,
        // set allLoaded to true, so we don't try to load any more data
        if (!ret.items.items.length && appending) {
          jQuery('#overlay-loader-li').remove();

          // if we got an error, re-add the Load More button
          if (ret.err) {
            fv_flowplayer_browser_add_load_more_button($ul, function() { loadMoreFunction(true); });
          }

          appending = false;
          allLoaded = true;
          return;
        }

        // remove temporary loading LI if we're not displaying the full browser for the first time
        if (!firstLoad) {
          jQuery('#overlay-loader-li').remove();
        }

        fv_flowplayer_browser_browse( ret.items, {
          noFileName: true,
          append: appending,
          extraAttachmentClass: 'fullsize',
          ajaxSearchCallback: function() {
            allLoaded = false;
            appending = false;
            page = 1;
            getBunnyStreamData();
          },
          loadMoreButtonAction: (ret.is_last_page ? false : loadMoreFunction)
        } );

        // if we have any items returned in a processing state, auto-refresh the Coconut tab every 30 seconds
        if ( ret.items && ret.items.items ) {
          for ( var item of ret.items.items ) {
            if ( item.extra && item.extra.encoding_job_status && item.extra.encoding_job_status == 'processing' ) {
              havePendingItems = true;
            }
          }
        }

        if ( havePendingItems ) {
          setTimeout( function() {
            // only reload tab if the tab is actually still visible and active
            var $tab = $( '#fv_player_bunny_stream_browser_media_tab' );
            if ( $tab.is(':visible') && $tab.hasClass('active') )
              fv_player_bunny_stream_browser_load_assets();
          }, 30000 );
        }

        appending = false;
      } );
    }

    getBunnyStreamData();
    return false;
  };

  $( document ).on( "mediaBrowserOpen", function(event) {
    var tabId = 'fv_player_bunny_stream_browser_media_tab';
    
    // make sure we'll add .m3u8 files with the same name as our encoded file into the list of elements to lookup splash images in
    if( window.fv_flowplayer_browser_splash_file_lookup_rules ) {
      fv_flowplayer_browser_splash_file_lookup_rules[ tabId ] = { 'include' : ['\.(m3u8)$'] };
    }
    fv_flowplayer_media_browser_add_tab( tabId, 'Bunny Stream', fv_player_bunny_stream_browser_load_assets, null, function() {
      firstLoad = true;
    }).addClass('upload_supported');
  });
});