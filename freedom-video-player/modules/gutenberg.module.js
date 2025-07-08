/**
 * Adds invisible div elements to the video player in the Gutenberg editor.
 * Clicking these elements will open the video player editing.
 * Only clicking the middle of the video player will play the video.
 * We also do not cover the control bar.
 */
flowplayer( function(api,root) {
  root = jQuery(root);

  // z-index has to be lower than the .fv-player-video-checker z-index
  var click_to_edit = '<div title="Click to edit" style="width: 40%; height: calc( 100% - 3em ); z-index: 19; position: absolute; top: 0; left: 0; cursor: context-menu" onclick="return false" title="Click to edit"></div><div style="width: 40%; height: calc( 100% - 3em ); z-index: 19; position: absolute; top: 0; right: 0; cursor: context-menu" onclick="return false" title="Click to edit"></div><div style="width: 20%; height: 40%; z-index: 19; position: absolute; top: 0; right: 40%; cursor: context-menu" onclick="return false" title="Click to edit"></div><div style="width: 20%; height: calc( 40% - 3em ); z-index: 19; position: absolute; top: 60%; right: 40%; cursor: context-menu" onclick="return false"></div>';

  // check if we are in the editor
  if( document.body.classList.contains('block-editor-page') && ! root.closest( '#fv-player-shortcode-editor-preview-target' ).length || jQuery( 'body.block-editor-iframe__body' ).length ) {
    jQuery( click_to_edit ).insertAfter( root.find('.fp-ratio') );
  }

});
