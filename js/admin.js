/*global cm_settings */

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

  function rgb2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
      ("0" + parseInt(rgb[1],10).toString(16)).slice(-2).toUpperCase() +
      ("0" + parseInt(rgb[2],10).toString(16)).slice(-2).toUpperCase() +
      ("0" + parseInt(rgb[3],10).toString(16)).slice(-2).toUpperCase() : '';
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
      style = '';
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

        } else if ( 'logoPosition' === $this.attr('name') ) {
          if ( fv_player_admin.css_logo_positions[ $this.val() ] ) {
            style += "\n" + '.flowplayer-wrapper .freedomplayer .fp-logo img { ' + fv_player_admin.css_logo_positions[ $this.val() ] + ' }';
          }
        }
      });

      return style;
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

        if ( 'logo' === $this.attr('name') ) {
          let player = $( '.flowplayer-wrapper .freedomplayer' ),
            logo_url = $this.val();

          /**
           * Only show preview if the logo is valid and is different from the default.
           * This prevents the logo showing before the video is started, but at the same
           * time show if if user picks are different image.
           */
          if ( logo_url.match( /^https?:\/\/.*?\.(jpg|jpe|jpeg|gif|png)$/i ) && ( logo_url !== freedomplayer.conf.logo || player.hasClass( 'is-ready' ) ) ) {
            player.find( '.fp-logo' ).remove();

            // Update the logo in the loaded player
            let api = player.data( 'freedomplayer' )
            if ( api ) {
              api.conf.logo = logo_url.trim();
            }

            let img = new Image();
            img.src = logo_url.trim();

            let logo = $( '<a class="fp-logo"></a>' );
            logo.append( img );

            $( '.flowplayer-wrapper .freedomplayer .fp-ui' ).eq(0).after( logo );

            if ( api ) {
              api.setLogoPosition();
            }
          }

        } else if($this.attr('type') == 'checkbox' ) {
          if ($this.prop('checked')) {
            newStyle = preview.replace(/%val%/g, '1');
          } else {
            newStyle = preview.replace(/%val%/g, '0');
          }
          style += sanitizeCSS(newStyle);

        } else {
          var value = $this.val().replace(/^#/,''),
            opacity = $this.minicolors('opacity'),
            color = hexToRgb(value);

          if( opacity && color ) {
            value = 'rgba('+color[0]+','+color[1]+','+color[2]+','+opacity+')';
          }
          newStyle = preview.replace(/%val%/g, value);
          style += sanitizeCSS(newStyle);

        }
      }, 0);

      // update progress bar + icons style
      style += skinPreviewDropdownChanged();

      show_player_controls_for_preview();

      $('#fv-style-preview').html(style);
    }

    // color inputs + checkbox changes
    $previewElements.on('input change', skinPreviewInputChanged);
    $previewElements.eq(0).trigger('input');

    $('[data-fv-preview]').on('select change', skinPreviewDropdownChanged);

    /**
     * Preview code for Controls tab of Skin
     */
    let player = $( '.freedomplayer' );

    $( '#show_controlbar' ).on( 'change', function() {
      player.toggleClass( 'fixed-controls', $( this ).prop( 'checked' ) );
    } );


    function show_player_controls_for_preview() {
      $( '.freedomplayer' ).addClass( 'is-mouseover' ).removeClass( 'is-mouseout is-poster is-splash' );
    }

    /**
     *
     * @param {string} selector
     * @param {Object[]} options
     * @param {string}   options.cb_enabled        api method to call when checkbox is checked
     * @param {string}   options.cb_disabled       api method to call when checkbox is unchecked
     * @param {string}   options.conf_key          api.conf key to set to true/false
     * @param {string}   options.data              Player element data-{data} attribute to set to true/false
     * @param {string}   options.selector_disabled Element to remove when checkbox is unchecked
     * @param {string}   options.show_hide         Element to show/hide
     */
    function controlsPreviewCheckbox( selector, options ) {
      $(selector).on('change', function() {
        let api = player.data( 'freedomplayer' ),
          is_checked = $(this).prop('checked');

        if ( options.conf_key ) {
          api.conf[ options.conf_key ] = is_checked;
        }

        if ( options.data ) {
          player.attr( 'data-' + options.data, is_checked );
        }

        if ( options.show_hide ) {
          player.find( options.show_hide ).toggle( is_checked );
        }

        if ( is_checked ) {
          if ( options.cb_enabled ) {
            api[ options.cb_enabled ]();
          }

        } else {
          if ( options.cb_disabled ) {
            api[ options.cb_disabled ]();
          }

          if ( options.selector_disabled ) {
            player.find( options.selector_disabled ).remove();
          }
        }

        show_player_controls_for_preview();
      });
    }

    // Airplay
    controlsPreviewCheckbox( '#ui_airplay', {
      conf_key:          'airplay',
      cb_enabled:        'createAirplayButton',
      selector_disabled: '.fp-airplay'
    } );

    // Chromecast
    controlsPreviewCheckbox( '#chromecast', {
      conf_key:          'chromecast',
      cb_enabled:        'createChromecastButton',
      selector_disabled: '.fp-chromecast'
    } );

    // Fullscreen
    // TODO: Not working when initially off and then enabled
    controlsPreviewCheckbox( '#allowfullscreen', {
      conf_key:   'fullscreen',
      data:       'fullscreen',
      show_hide:  '.fp-fullscreen'
    } );

    // No Picture
    controlsPreviewCheckbox( '#ui_no_picture_button', {
      cb_enabled:        'createNoPictureButton',
      data:              'button-no_picture',
      selector_disabled: '.fv-fp-no-picture'
    } );

    // Repeat
    controlsPreviewCheckbox( '#ui_repeat_button', {
      cb_enabled:        'createRepeatButton',
      data:              'button-repeat',
      selector_disabled: '.fv-fp-playlist, .fv-fp-playlist-menu'
    } );

    // Rewind/Forward
    controlsPreviewCheckbox( '#ui_rewind_button', {
      cb_enabled:        'createRewindForwardButtons',
      data:              'button-rewind',
      selector_disabled: '.fv-fp-rewind, .fv-fp-forward'
    } );

    // Speed
    controlsPreviewCheckbox( '#ui_speed', {
      cb_disabled: 'removeSpeedButton',
      cb_enabled:  'createSpeedButton',
      data:        'speedb',
    } );

    // Logo -> Align to video
    controlsPreviewCheckbox( '#logo_over_video', {
      conf_key:    'logo_over_video',
      cb_enabled:  'setLogoPosition',
      cb_disabled: 'setLogoPosition',
    } );
  });

  $(document).ready( function() {
    var settings = {
      animationSpeed: 0,
      changeDelay: 10,
      letterCase: 'uppercase'
    }
    $('input.color').minicolors(settings);
    settings.opacity = true;
    $('input.color-opacity').minicolors(settings);

    $('input.color, input.color-opacity').on('change', color_inputs);
    $('input.color, input.color-opacity').each(color_inputs);

    $('form#wpfp_options').on('submit', function(e) {
      $( document ).trigger( 'fv-wordpress-flowplayer-save' );
    });

    $( document ).on( 'fv-wordpress-flowplayer-save', function() {
      $('input.color-opacity').each( function() {
        var input = $(this),
          opacity = input.minicolors('opacity'),
          color = hexToRgb( input.val() );

        if( opacity && color ) {
          input.val( 'rgba('+color[0]+','+color[1]+','+color[2]+','+opacity+')' );
        }
      });
    });
  });

  $(document).ready( function() {
    $(document).on('click', 'a[data-setting-change]', function(e) {
      e.preventDefault();

      var name = $(this).data('setting-change'),
      index,
      hidden_input,
      password_input;

      if(name.match(/-index-(\d)+$/)) {
        var match = name.match(/-index-(\d)+$/);
        index = parseInt(match[1]);
        name = name.replace( match[0],'');
        hidden_input = $("[name='"+name+"']").eq(index);
        password_input = $("[name='"+name.replace('_is_secret_', '')+"']").eq(index);
      } else {
        hidden_input = $("[name='"+name+"']");
        password_input = $("[name='"+name.replace('_is_secret_', '')+"']");
      }

      var secret_preview = $(this).siblings('.secret-preview');

      if( hidden_input.val() == '1' ) {
        hidden_input.val('0');
        password_input.show();
        secret_preview.hide();
        $(this).text('Cancel');
      } else {
        hidden_input.val('1');
        password_input.hide();
        if( password_input.val() || !$(this).data('is-empty') ) {
          $(this).text('Change');
          secret_preview.show();
        }
      }

    });
  });

  function color_inputs() {
    var input = $(this);
    var color = input.val();
    var rgba = hexToRgb( color );
    if( color.match(/rgba\(.*?\)/) ) {
      rgba = hexToRgb( rgb2hex(color) );
      input.val( rgb2hex(color) );
    }

    var opacity = input.minicolors('opacity');

    if( rgba && ( opacity ) ) {
      input.css('box-shadow', 'inset 0 0 0 1000px rgba('+rgba[0]+','+rgba[1]+','+rgba[2]+','+opacity+')' );
    } else {
      input.css('background-color', input.val());
    }

    if( rgba && (
      (rgba[0] < 160 && rgba[1] < 160) || (rgba[1] < 160 && rgba[2] < 160) || (rgba[0] < 160 && rgba[2] < 160)
    ) ) {
      input.css('color', 'white');
    } else {
      input.css('color', '');
    }
  }

  /* CodeMirror */
  jQuery(function($) {
    if( $('#customCSS').length && wp.codeEditor ) {
      var editor_instance = wp.codeEditor.initialize($('#customCSS'), cm_settings);

      // Used in ajax saving
      window.fv_player_settings_custom_css_codeMirror = editor_instance;

    }
  });

}(jQuery));
