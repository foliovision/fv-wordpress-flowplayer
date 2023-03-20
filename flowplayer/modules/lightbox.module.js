// This is the part which makes sure lightboxed player starting buttons look like players once Freedom Player JS loads
jQuery(fv_player_lightbox_bind);
jQuery(document).ajaxComplete(fv_player_lightbox_bind);

function fv_player_lightbox_bind(){
    
  jQuery(".freedomplayer.lightbox-starter").each( function() {
    var player = jQuery(this);
    if( parseInt(player.css('width')) < 10 || parseInt(player.css('height')) < 10 ) {
      //if (!parseInt(origRatio, 10))
      var ratio = player.find('.fp-ratio');
      if( ratio.length < 1){
        player.append('<div class="fp-ratio"></div>');
        ratio = player.find('.fp-ratio');
      }
      ratio.css("paddingTop", player.data('ratio') * 100 + "%");
    }

    player.find('.fp-preload').remove();

  } );
  
}

// Lightboxed player behavior improvements
jQuery( function() {
  if( typeof(freedomplayer) != "undefined" ) {
    freedomplayer( function(api,root) {
      root = jQuery(root);

      var lightbox_wrap = root.closest('.fv_player_lightbox_hidden');

      /**
       * Check if player is lightboxed
       * 
       * @returns int
       */
      api.is_in_lightbox = function() {
        return lightbox_wrap.length;
      };

      /**
       * Check if lightbox is visible
       * 
       * @returns int
       */
      api.lightbox_visible = function() {
        return root.closest('.fancybox-slide--current').length;
      };

      if( api.is_in_lightbox() ) {
        lightbox_wrap.on('click', function(e) {
          if( e.target == e.currentTarget) {
            jQuery.fancybox.close();
          }
        });
        
        // overriding default Flowplayer fullscreen function
        if( freedomplayer.support.fullscreen ) { // todo: should also work for YouTube on desktop
          api.fullscreen = function() {
            jQuery.fancybox.getInstance().FullScreen.toggle();
          };
        } else {
          var fancybox_ui = '.fancybox-caption, .fancybox-toolbar, .fancybox-infobar, .fancybox-navigation';
          var fancybox_thumbs = false;
          api.on('fullscreen', function() {
            jQuery(fancybox_ui).hide();
            fancybox_thumbs = jQuery('.fancybox-container').hasClass('fancybox-show-thumbs')
            jQuery('.fancybox-container').removeClass('fancybox-show-thumbs');
          }).on('fullscreen-exit', function() {
            jQuery(fancybox_ui).show();
            if( fancybox_thumbs ) jQuery('.fancybox-container').addClass('fancybox-show-thumbs');
          });
        }

      }
    });
  }
});