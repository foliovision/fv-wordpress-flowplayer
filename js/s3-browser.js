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

    // remove infinite scroll checks from other tabs
    $media_frame_content.off('mousewheel');

    // replace content by the new S3 content
    $media_frame_content.html($overlay_div);

    if (typeof bucket === 'string' && bucket) {
      ajax_data['bucket'] = bucket;
    }
    if (typeof path === 'string' && path) {
      ajax_data['path'] = path;
    }

    jQuery.post(ajaxurl, ajax_data, function(ret) {
      var html = '<div class="attachments-browser"><div class="media-toolbar s3-media-toolbar">';

      if (ret.buckets) {
        html += '<div class="media-toolbar-secondary">';

        // prepare dropdown HTML
        var
          select_html = '<label for="browser-dropdown" class="screen-reader-text">S3 Bucket</label>'
            + '<select name="browser-dropdown" id="browser-dropdown" class="attachment-filters">',
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

      html += '\t\t<ul tabindex="-1" class="data attachments ui-sortable ui-sortable-disabled" id="__assets_browser"></ul>\n' +
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