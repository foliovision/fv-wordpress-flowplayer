(function ($) {
  ('use strict');
  
  /*
   * Skin live preview
   */
  function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? [
      parseInt(result[1], 16),
      parseInt(result[2], 16),
      parseInt(result[3], 16)
    ] : null;
  }
  
  function sanitizeCSS(val) {
    if (val.indexOf('#rgba') > -1) {
      val = val.replace(/#rgba/g, 'rgba');
    } else if (val.indexOf('#transparent') > -1) {
      val = val.replace(/#transparent/g, 'transparent');
    }
    
    if( val.match(/# !/) ) {
      val = false;
    }
    
    return val;
  }

  if (!String.prototype.endsWith)
    String.prototype.endsWith = function(searchStr, Position) {
      // This works much better than >= because
      // it compensates for NaN:
      if (!(Position < this.length))
        Position = this.length;
      else
        Position |= 0; // round position
      return this.substr(Position - searchStr.length,
        searchStr.length) === searchStr;
    };

  $(document).ready(function () {
    $('[data-fv-skin]').on('input click', function () {
      $('[data-fv-skin]').each( function() {
        $('.flowplayer').removeClass( 'skin-'+$(this).val() );
      });
      $('.flowplayer').addClass( 'skin-'+$(this).val() );
      
      // hide currently visible settings tables
      $('#skin-Custom-settings, #skin-Slim-settings, #skin-YouTuby-settings').hide();

      // show the relevant settings table
      $('#' + this.id + '-settings').show();
      
      

      // update CSS
      skinPreviewInputChanged();
    });

    // cache this, it's quite expensive to select via data attribute
    var $previewElements = $('[data-fv-preview]');

    // dropdown value changes (slim type, icon types)
    function skinPreviewDropdownChanged() {
      $previewElements.each(function() {
        var
          $this = $(this),
          $parent = $this.closest('table');

        // don't change to values of another skin but to our currently visible skin type
        if ($parent.css('display') == 'none') {
          return;
        }

        // playlist design style change
        if ($this.attr('name').endsWith('playlist-design')) {
          var
            $external_playlist = $('.fp-playlist-external'),
            match = $external_playlist.attr('class').match(/fv-playlist-design-\S+/);

          if (match) {
            $external_playlist.removeClass(match[0]);
          }

          $external_playlist
            .removeClass('visible-captions')
            .addClass('fv-playlist-design-' + $this.val());

        } else if ($this.attr('name').endsWith('design-timeline]')) {
          // timeline design style change
          $('.flowplayer')
            .removeClass('fp-slim fp-full fp-fat fp-minimal')
            .addClass($this.val());
        } else if ($this.attr('name').endsWith('design-icons]')) {
          $('.flowplayer')
            .removeClass('fp-edgy fp-outlined fp-playful')
            .addClass($this.val());
        }
      });
    }

    // input (textbox, checkbox) value changes
    function skinPreviewInputChanged() {
      var style = '';

      $previewElements.each(function () {
        
        var
          newStyle = '',
          $this = $(this),
          $parent = $this.closest('table');
          
        var preview = $this.data('fv-preview').replace(/\.flowplayer/g,'.flowplayer.skin-'+jQuery('[data-fv-skin]:checked').val() );

        if ($parent.css('display') == 'none') {
          return;
        }

        if ($this.attr('name').endsWith('player-position]')) {
          if ($this.val() === 'left')
            style += preview;

        } else if ($this.attr('name').endsWith('subtitleBgColor')) {
          var replacement = hexToRgb($this.val());
          replacement.push($('#subtitleBgAlpha').val());
          newStyle = preview.replace(/%val%/g, replacement.join(', '));
          style += sanitizeCSS(newStyle);
          
        } else if($this.attr('type') == 'checkbox' ) {          
          if ($this.prop('checked')) {
            newStyle = preview.replace(/%val%/g, '1');
          } else {
            newStyle = preview.replace(/%val%/g, '0');
          }
          style += sanitizeCSS(newStyle);
          
        } else {
          var value = $this.val().replace(/^#/,'');
          newStyle = preview.replace(/%val%/g, value);
          style += sanitizeCSS(newStyle);
          
        }
      }, 0);
      
      $('#fv-style-preview').html(style);

      // update progress bar + icons style
      skinPreviewDropdownChanged();
    }

    // color inputs + checkbox changes
    $previewElements.on('input change', skinPreviewInputChanged).trigger('input');

    $('[data-fv-preview]').on('select change', skinPreviewDropdownChanged);
  });

  
}(jQuery));