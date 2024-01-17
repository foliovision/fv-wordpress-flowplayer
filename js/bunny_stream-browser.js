jQuery( function($) {
  var firstLoad = true;

  function fv_player_bunny_stream_browser_load_assets(dropdown_val, link) {

    var
      $this = jQuery(this),
      $media_frame_content = jQuery('.media-frame-content:visible'),
      $overlay_div = jQuery('#fv-player-shortcode-editor-preview-spinner').clone().css({
        'height' : '100%'
      }),
      page = 1,
      ajax_data = {
        action: "load_bunny_stream_jobs",
        nonce: fv_player_bunny_stream_settings.nonce,
        page: page
      },
      appending = false,
      allLoaded = false;

      if( link ) {
        ajax_data['path'] = link;
      }

    if( window.fv_flowplayer_browser_get_function ) {
      fv_flowplayer_browser_get_function[ 'fv_player_bunny_stream_browser_media_tab' ] = fv_player_bunny_stream_browser_load_assets;
    }

    $this.addClass('active').siblings().removeClass('active');

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
      $media_frame_content.html($overlay_div);
    
      if (searchVal) {
        ajax_data['search'] = searchVal;
      } else {
        delete(ajax_data['search']);
      }

      ajax_data['page'] = page;

      ajax_data['appending'] = (appending ? 1 : 0);
      ajax_data['firstLoad'] = (firstLoad ? 1 : 0);

      jQuery.post(ajaxurl, ajax_data, function(ret) {
        // don't overwrite the page if we've shown the browser for the first time already
        // ... instead, we'll be either clearing and rewriting the UL or appending data to it
        var renderOptions = {
          searchMsg: "Search in current collection..."
        };

        // add errors, if any
        if (ret.err) {
          renderOptions['errorMsg'] = ret.err;
        }

        $media_frame_content.html( renderBrowserPlaceholderHTML(renderOptions) );

       if (!appending && !allLoaded) {
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

        var args = {
          breadcrumbs: 1,
          action: 'add_bunny_stream_new_folder',
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
        }

        // show add new collection button only in Home/ folder
        if( typeof ajax_data['path'] == 'undefined' || ( ajax_data['path'] && ajax_data['path'] == 'Home/' ) ) {
          args.add_new_folder = 1;
          args.add_new_folder_text = 'Add new collection';
          args.nonce_add_new_folder = fv_player_bunny_stream_upload_settings.nonce_add_new_folder;
        }

        fv_flowplayer_browser_browse(ret.items, args);

        appending = false;
      } );
    }

    getBunnyStreamData();
    return false;
  }

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