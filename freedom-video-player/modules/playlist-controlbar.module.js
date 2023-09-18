// Playlist menu in controlbar
flowplayer( function(api,root) {
  root = jQuery(root);
  
  if (
    ! api.have_visible_playlist && api.conf.playlist.length == 0 ||
    ! api.have_visible_playlist()
  ) return;
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
  //if( !playlist.hasClass('fp-playlist-season') ) return; // todo: what about mobile? Should we always allow this?
  
  var playlist_button = jQuery('<strong class="fv-fp-list">Item 1.</strong>'),
      playlist_button_name = jQuery('<strong class="fv-fp-list-name">Item 1.</strong>'),
    playlist_menu = jQuery('<div class="fp-menu fv-fp-list-menu"></div>').insertAfter( root.find('.fp-controls') );
  
  var i =0 , item_index = [], track_item = [];
  jQuery(api.conf.playlist).each( function(k,v) {
    if(typeof(v.click) == 'undefined' ) {
      var title = parse_title(playlist.find('h4').eq(i));
      playlist_menu.append('<a data-index="'+k+'">'+(i+1)+'. '+title+'</a>');
      track_item[k] = title;
      item_index.push(k);
      i++;
    }
  });
  
  function playlist_button_click(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if( playlist_menu.hasClass('fp-active') ) {
      api.hideMenu(playlist_menu[0]);
    }
    else {
      // workaround for flowplayer 7 not picking up our menu as one of its own,
      // thus not closing it
      root.trigger('click');
      api.showMenu(playlist_menu[0]);
    }
  }

  playlist_button.insertAfter( root.find('.fp-controls .fp-volume') ).on('click', playlist_button_click);
  playlist_button_name.insertAfter(playlist_button).on('click', playlist_button_click);

  jQuery('a',playlist_menu).on('click', function() {
    var index = jQuery(this).data('index'),
      prev = index - 1;
    if( typeof(api.conf.playlist[prev]) != 'undefined' && typeof(api.conf.playlist[prev].click) != 'undefined' ) { // check if FV Player Pro Video Ad is in front of video - act as if clicked on Ad
      api.play( prev );
    } else {
      api.play( index );
    }
  });
  
  api.on('ready', function(e,api,video) {
    playlist_menu.find('a').removeClass('fp-selected');
    var thumb = playlist_menu.find('a[data-index='+video.index+']');
    thumb.addClass('fp-selected');
    var label = fv_flowplayer_translations.playlist_item_no
    label = label.replace( /%d/, item_index.indexOf(video.index) + 1 );
    label = label.replace( /%s/, parse_title( thumb.find('h4') ) );
    playlist_button.html(label);
    playlist_button_name.html( (item_index.indexOf(video.index) + 1) + '. ' + track_item[video.index] );
  });
  
  function parse_title(el) {
    var tmp = el.clone();
    tmp.find('i.dur').remove();
    return tmp.text();
  }
  
});