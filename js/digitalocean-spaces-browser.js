jQuery( function($) {

  function fv_flowplayer_dos_browser_load_assets(bucket, path) {
    var
      $this = jQuery(this),
      $media_frame_content = jQuery('.media-frame-content:visible'),
      $overlay_div = jQuery('#fv-player-shortcode-editor-preview-spinner').clone().css({
        'height': '100%'
      }),
      ajax_data = {
        action: "load_dos_assets",
      };

    $this.addClass('active').siblings().removeClass('active');

    // replace content by the new DOS content
    $media_frame_content.html($overlay_div);

    if (typeof bucket === 'string' && bucket) {
      ajax_data['bucket'] = bucket;
    }
    if (typeof path === 'string' && path) {
      ajax_data['path'] = path;
    }

    jQuery.post(ajaxurl, ajax_data, function (ret) {
      var renderOptions = {};

      // add errors, if any
      if (ret.err) {
        renderOptions['errorMsg'] = ret.err;
      }

      $media_frame_content.html(renderBrowserPlaceholderHTML(renderOptions));

      // hide search, as it's not supported for DOS
      jQuery('#media-search-input').parent().hide();

      fv_flowplayer_browser_browse(ret.items, { 'breadcrumbs' : 1 });
    });

    return false;
  };

  $(document).on("mediaBrowserOpen", function (event) {
    fv_flowplayer_media_browser_add_tab('fv_flowplayer_dos_browser_media_tab', 'DigitalOcean Spaces', fv_flowplayer_dos_browser_load_assets);
  });
});