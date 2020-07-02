jQuery( function($) {

  function fv_flowplayer_s3_browser_load_assets(bucket, path) {
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

    // replace content by the new S3 content
    $media_frame_content.html($overlay_div);

    if (typeof bucket === 'string' && bucket) {
      ajax_data['bucket'] = bucket;
    }
    if (typeof path === 'string' && path) {
      ajax_data['path'] = path;
    }

    jQuery.post(ajaxurl, ajax_data, function(ret) {
      var
        renderOptions = {
          'dropdownItems' : [],
          'dropDownNoOptionEnabledWarningMsg' : '<strong>You have no S3 buckets configured <a href="options-general.php?page=fvplayer#postbox-container-tab_hosting">in settings</a> or none of them has complete settings (region, key ID and secret key).</strong>',
          'dropdownItemSelected' : ret.active_bucket_id
        };

      // fill dropdown options
      for (var i in ret.buckets) {
        renderOptions.dropdownItems.push({
          'value' : ret.buckets[i].id,
          'text' : ret.buckets[i].name
        });
      }

      // add errors, if any
      if (ret.err) {
        renderOptions['errorMsg'] = ret.err;
      }

      $media_frame_content.html( renderBrowserPlaceholderHTML(renderOptions) );

      // hide search, as it's not supported for AWS
      jQuery('#media-search-input').parent().hide();

      jQuery('#browser-dropdown').on('change', function() {
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

      fv_flowplayer_browser_browse( ret.items, { 'breadcrumbs' : true } );
    } );

    return false;
  };

  $( document ).on( "mediaBrowserOpen", function(event) {
    fv_flowplayer_media_browser_add_tab('fv_flowplayer_s3_browser_media_tab', 'Amazon S3', fv_flowplayer_s3_browser_load_assets);
  });
});