jQuery( function($) {

  function fv_flowplayer_linode_object_storage_browser_load_assets(bucket, path) {
    var
      $this = jQuery(this),
      $media_frame_content = jQuery('.media-frame-content:visible'),
      $overlay_div = jQuery('#fv-player-shortcode-editor-preview-spinner').clone().css({
        'height': '100%'
      }),
      ajax_data = {
        action: "load_linode_object_storage_assets",
        nonce: window.fv_player_linode_object_storage.nonce
      };

    $this.addClass('active').siblings().removeClass('active');

    $media_frame_content.html($overlay_div);

    if (typeof bucket === 'string' && bucket) {
      ajax_data['bucket'] = bucket;
    }
    if (typeof path === 'string' && path) {
      ajax_data['path'] = path;
    }

    jQuery.post(window.fv_flowplayer_browser.ajaxurl, ajax_data, function (ret) {
      var renderOptions = {};

      // add errors, if any
      if (ret.err) {
        renderOptions['errorMsg'] = ret.err;
      }

      $media_frame_content.html(renderBrowserPlaceholderHTML(renderOptions));

      jQuery('#media-search-input').parent().hide();

      fv_flowplayer_browser_browse(ret.items, { 'breadcrumbs' : 1 });
    });

    return false;
  }

  $(document).on("mediaBrowserOpen", function (event) {
    fv_flowplayer_media_browser_add_tab('fv_flowplayer_linode_object_storage_browser_media_tab', 'Linode Object Storage', fv_flowplayer_linode_object_storage_browser_load_assets);
  });
});
