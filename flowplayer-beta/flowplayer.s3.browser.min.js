jQuery( function($) {
    function fv_flowplayer_media_browser_add_tab(tabId, tabText, tabOnClickCallback) {
      if (!jQuery('#' + tabId).length) {
        // add Vimeo browser tab
        var
          $router = jQuery('.media-router:visible'),
          $item = $router.find('.media-menu-item:last').clone();

        $item
          .attr('id', tabId)
          .text(tabText)
          .on('click', tabOnClickCallback);

        $router.append($item);
      }
    };

    function fv_flowplayer_s3_browser_load_assets(bucket) {
      var
        $this = jQuery(this),
        $media_frame_content = jQuery('.media-frame-content:visible'),
        $overlay_div = jQuery('#fv-player-shortcode-editor-preview-spinner').clone().css({
          'height' : '100%'
        }),
        ajax_data = {
          action: "load_s3_assets",
          cookie: encodeURIComponent(document.cookie)
        };

      $this.addClass('active').siblings().removeClass('active')
      $media_frame_content.html($overlay_div);

      if (typeof bucket === 'string' && bucket) {
        ajax_data['bucket'] = bucket;
      }

      jQuery.post(ajaxurl, ajax_data, function(ret) {
        var
          html = '<div class="files-div"><div class="filemanager">',
          last_selected_bucket = null;

        if (ret.buckets){
          html += '<div class="bucket-dropdown">';

          // prepare dropdown HTML
          var
            select_html = '<strong>S3 Bucket:</strong> &nbsp; <select name="bucket-dropdown" id="bucket-dropdown">',
            one_bucket_enabled = false;

          for (var i in ret.buckets) {
            select_html += '<option value="' + ret.buckets[i].id + '"' + (ret.active_bucket_id == ret.buckets[i].id ? ' selected="selected"' : '') + '>' + ret.buckets[i].name + '</option>'

            if (ret.buckets[i].id > -1) {
              one_bucket_enabled = true;
            }
          }

          select_html += '</select>';

          // check if we have at least a single enabled bucket
          // and if not, replace the whole select HTML with a warning message
          if (!one_bucket_enabled) {
            select_html = '<strong>You have no S3 buckets configured <a href="options-general.php?page=fvplayer#postbox-container-tab_hosting">in settings</a> or none of them has complete settings (region, key ID and secret key).</strong>';
          }

          html += select_html + '</div>' +
            '<hr /><br />';
        }

        if (ret.err) {
          html += '<div class="errors"><strong>' + ret.err + '</strong></div><hr /><br />';
        }

        html += '<div class="search">' +
          '<input type="search" placeholder="Find a file.." />' +
          '</div>' +
          '\t\t<div class="breadcrumbs"></div>\n' +
          '\n' +
          '\t\t<ul class="data"></ul>\n' +
          '\n' +
          '\t\t<div class="nothingfound">\n' +
          '\t\t\t<div class="nofiles"></div>\n' +
          '\t\t\t<span>No files here.</span>\n' +
          '\t\t</div>\n' +
          '\n' +
          '\t</div>\n' +
          '\t</div>';

        $media_frame_content.html(html);

        jQuery('#bucket-dropdown').on('change', function() {
          if (this.value >= 0) {
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

  $( document ).on( "mediaBrowserOpen", function(event) {
    fv_flowplayer_media_browser_add_tab('fv_flowplayer_s3_browser_media_tab', 'Amazon S3', fv_flowplayer_s3_browser_load_assets);
  });
});