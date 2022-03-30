/**
 * Add translated text
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  api.on('ready', function(e, api) {
    root.find('.fp-subtitle-menu strong').text(fv_flowplayer_translations.closed_captions); // translate closed captions
    root.find('.fp-subtitle-menu a[data-subtitle-index="-1"]').text(fv_flowplayer_translations.no_subtitles) // translate no subtitles
  });
});