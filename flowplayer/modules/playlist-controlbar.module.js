/*
 * Playlist in controlbar for the "Season" playlist style
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  
  if( api.conf.playlist.length == 0 ) return;
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
  //if( !playlist.hasClass('fp-playlist-season') ) return; // todo: what about mobile? Should we always allow this?
  
  var playlist_button = jQuery('<strong class="fv-fp-list">Item 1.</strong>'),
    playlist_menu = jQuery('<div class="fp-menu fv-fp-list-menu"></div>').insertAfter( root.find('.fp-controls') );
  
  jQuery(api.conf.playlist).each( function(k,v) {
    playlist_menu.append('<a data-index="'+k+'">'+(k+1)+'. '+parse_title(playlist.find('h4').eq(k))+'</a>');    
  });
  
  playlist_button.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if( playlist_menu.hasClass('fp-active') ) {
      api.hideMenu(playlist_menu[0]);
    }
    else {
      // workaround for flowplayer 7 not picking up our menu as one of its own,
      // thus not closing it
      root.click();
      api.showMenu(playlist_menu[0]);
    }
  });
  
  jQuery('a',playlist_menu).click( function() {
    api.play(jQuery(this).data('index'));
  });
  
  api.on('ready', function(e,api,video) {
    playlist_menu.find('a').removeClass('fp-selected');
    var thumb = playlist_menu.find('a[data-index='+video.index+']');
    thumb.addClass('fp-selected');
    var label = fv_flowplayer_translations.playlist_item_no
    label = label.replace( /%d/, video.index+1 );
    label = label.replace( /%s/, parse_title( thumb.find('h4') ) );
    playlist_button.html(label);
  });
  
  function parse_title(el) {
    var tmp = el.clone();
    tmp.find('i.dur').remove();
    return tmp.text();
  }
  
});