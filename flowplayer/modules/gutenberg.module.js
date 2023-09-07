flowplayer( function(api,root) {
  root = jQuery(root);

  var extra_html = '<div class="fv-gt-test" style="width: 40%; height: calc( 100% - 3em ); z-index: 100000; position: absolute; top: 0; left: 0; cursor: context-menu" onclick="return false" title="Click to edit"></div><div style="width: 40%; height: calc( 100% - 3em ); z-index: 100000; position: absolute; top: 0; right: 0; cursor: context-menu" onclick="return false" title="Click to edit"></div><div style="width: 20%; height: 40%; z-index: 100000; position: absolute; top: 0; right: 40%; cursor: context-menu" onclick="return false" title="Click to edit"></div><div style="width: 20%; height: calc( 40% - 3em ); z-index: 100000; position: absolute; top: 60%; right: 40%; cursor: context-menu" onclick="return false" title="Click to edit"></div>';

  // check if we are in the editor
  if( typeof window.wp != 'undefined' && typeof window.wp.components != 'undefined' ) {
    jQuery(extra_html).insertAfter( root.find('.fp-ratio') );
  }

});
