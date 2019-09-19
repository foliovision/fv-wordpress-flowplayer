/*
 *  Iframe embedding embed code
 */
flowplayer(function(player, root) {
  root = jQuery(root);
  if( typeof(root.data('fv-embed')) == 'undefined' || !root.data('fv-embed') || root.data('fv-embed') == 'false' ) return;

  player.embedCode = function() {    
    var video = player.video;
    var width = root.width();
    var height = root.height();
    height += 2;
    
    // adjust height to show at least some of chapters and transcripts
    if (root.hasClass('has-chapters') || root.hasClass('has-transcript') ) {
      height += 300;
    }
    
    if( jQuery('.fp-playlist-external[rel='+root.attr('id')+']').length > 0 ) {
      height += 150 + 20; // estimate of playlist height + scrollbar height
    }

    return '<iframe src="' + root.data('fv-embed') + '" allowfullscreen  width="' + width + '" height="' + height + '" frameborder="0" style="max-width:100%"></iframe>';
  };
  
});
