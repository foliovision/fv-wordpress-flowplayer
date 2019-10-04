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

    var data = root.data('fv-embed');
    var embed = data.match(/(?:fvp-[0-9]*)|(?:(?:fvp[0-9]*))/); // fvp-{id} or fvp{id} (shortcode)

    embed ='/' + embed + '#' + fv_player_get_video_link_hash(player);
    data = data.replace(/(?:\/fvp-[0-9]*\/)|(?:(?:\/fvp[0-9]*\/))/, embed);
    return '<iframe src="' + data + '" allowfullscreen  width="' + width + '" height="' + height + '" frameborder="0" style="max-width:100%"></iframe>';
  };
  
});

jQuery(document).on('click', '.flowplayer .embed-code-toggle', function() {
  var button = jQuery(this);
  var player = button.parents('.flowplayer');
  var api = player.data('flowplayer');
  if( typeof(api.embedCode) == 'function' && player.find('.embed-code textarea').val() == '' ) {
    player.find('.embed-code textarea').val(api.embedCode());  
  }
  
  fv_player_clipboard( player.find('.embed-code textarea').val(), function() {
      fv_player_notice(player,fv_flowplayer_translations.embed_copied,2000);          
    }, function() {
      button.parents('.fvp-share-bar').find('.embed-code').toggle();
      button.parents('.fvp-share-bar').toggleClass('visible');
    });
  
  return false;
} );