<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_lightbox {

  static $instance = null;

  private $lightboxHtml = '';

  public $bLoad = false;

  public static function _get_instance() {
    if( !self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    add_action('init', array($this, 'remove_pro_hooks'), 10);

    add_filter('fv_flowplayer_shortcode', array($this, 'shortcode'), 15, 3);

    add_filter('fv_flowplayer_player_type', array($this, 'lightbox_enable'));

    add_filter('fv_flowplayer_args', array($this, 'disable_autoplay')); // disable autoplay for lightboxed videos
    add_filter('fv_flowplayer_args', array($this, 'lightbox_button_align')); // save align for lightbox button

    add_filter('fv_flowplayer_args_pre', array($this, 'lightbox_playlist_style')); // force slider style for lightboxed playlist

    add_filter('fv_flowplayer_args', array($this, 'parse_html_caption'), 0);

    add_filter('the_content', array($this, 'html_to_lightbox_videos'));
    add_filter('the_content', array($this, 'html_lightbox_images'), 999);  //  moved after the shortcodes are parsed to work for galleries

    add_action('fv_flowplayer_shortcode_editor_tab_options', array($this, 'shortcode_editor'), 8);

    add_filter( 'fv_player_editor_player_options', array( $this, 'editor_setting' ) );

    add_action('fv_flowplayer_admin_default_options_after', array( $this, 'lightbox_admin_default_options_html' ) );
    add_filter('fv_flowplayer_admin_interface_options_after', array( $this, 'lightbox_admin_interface_html' ) );
    add_filter('fv_flowplayer_admin_integration_options_after', array( $this, 'lightbox_admin_integrations_html' ) );

    add_action( 'wp_footer', array( $this, 'disp__lightboxed_players' ), 0 );

    add_filter('fv_player_conf_defaults', array( $this, 'conf_defaults' ) );

    add_action('wp_head', array( $this, 'remove_other_fancybox' ), 8 );
    add_action('wp_footer', array( $this, 'remove_other_fancybox' ), 19 );

    add_filter( 'shortcode_atts_gallery', array( $this, 'improve_galleries' ) );

    add_action( 'wp_enqueue_scripts', array( $this, 'css_enqueue' ), 999 );
  }

  /*
   * Load CSS if it's actually needed or if the global settings are set.
   *
   * @param bool $force Used to tell the function that the CSS is indeed required
   */
  function css_enqueue( $force ) {
    global $fv_fp, $fv_wp_flowplayer_ver;
    if(
      !$force &&
      !$fv_fp->_get_option('lightbox_force') // "Remove fancyBox" compatibility option is disabled
    ) return;

    if ( $fv_fp->_get_option('js-optimize') && ! did_action('fv_player_force_load_lightbox') ) {
      // TODO: Should we still enqueue CSS somehow?

    } else {
      wp_enqueue_style( 'fv_player_lightbox', FV_FP_RELATIVE_PATH . '/css/fancybox.css', array(), $fv_wp_flowplayer_ver );
    }
  }

  function conf_defaults($conf){
    //TODO probbably not needed in the future
    if(isset($conf['lightbox_images']) && $conf['lightbox_images'] && !isset($conf['lightbox_improve_galleries']) )$conf['lightbox_improve_galleries'] = false;

    $conf += array(
      'lightbox_images' => false,
      'lightbox_improve_galleries' => false
    );

    return $conf;
  }

  function remove_pro_hooks() {
    global $FV_Player_Pro;

    if (isset($FV_Player_Pro)) {
      //remove_filter('fv_flowplayer_shortcode', array($FV_Player_Pro, 'shortcode'));
      remove_filter('fv_flowplayer_html', array($FV_Player_Pro, 'lightbox_html'), 11 );
      remove_filter('fv_flowplayer_playlist_style', array($FV_Player_Pro, 'lightbox_playlist'), 10);
      remove_filter('fv_flowplayer_args', array($FV_Player_Pro, 'disable_autoplay')); // disable autoplay for lightboxed videos, todo: it should work instead!
      remove_filter('the_content', array($FV_Player_Pro, 'lightbox_add'));
      remove_filter('the_content', array($FV_Player_Pro, 'lightbox_add_post'), 999 );  //  moved after the shortcodes are parsed to work for galleries

    }
  }

  function improve_galleries( $args ) {
    global $fv_fp;
    if ( empty( $args['link'] ) && $fv_fp->_get_option('lightbox_images') && $fv_fp->_get_option('lightbox_improve_galleries') ) {
      $args['link'] = 'file';
    }
    return $args;
  }

  function lightbox_enable($sType) {

    if ($sType === 'video') {
      add_filter('fv_flowplayer_html', array($this, 'lightbox_html'), 11, 2);
    } else {
      remove_filter('fv_flowplayer_html', array($this, 'lightbox_html'), 11);
    }

    return $sType;
  }

  /*
   * Runs when "Remove fancyBox" compatibility option is enabled. Removes any other fancyBox script.
   */
  function remove_other_fancybox() {
    global $fv_fp;
    if( $fv_fp->_get_option('lightbox_force') ) {
      global $wp_scripts;
      if( isset($wp_scripts) && isset($wp_scripts->queue) && is_array($wp_scripts->queue) ) {
        foreach( $wp_scripts->queue as $handle ) {
          if( stripos($handle,'fancybox') !== false ) {
            wp_dequeue_script($handle);
          }
        }
      }
    }
  }

  function shortcode($attrs) {
    $aArgs = func_get_args();

    if (isset($aArgs[2]) && isset($aArgs[2]['lightbox'])) {
      $attrs['lightbox'] = $aArgs[2]['lightbox'];
    }

    return $attrs;
  }

  // Used in integration tests
  public function clear_lightboxed_players() {
    $this->lightboxHtml = '';
  }

  function disp__lightboxed_players() {
    if ( strlen( $this->lightboxHtml ) ) {
      echo wp_kses_post( $this->lightboxHtml ) . "<!-- lightboxed players -->\n\n";
    }
  }

  function editor_setting( $options ) {
    global $fv_fp;
    $options['general']['items'][] = array(
      'label' => __( 'Lightbox', 'fv-player' ),
      'name' => 'lightbox',
      'description' => __( 'Video will play in a popup box.', 'fv-player' ),
      'dependencies' => array( 'autoplay' => false, 'sticky' => false ),
      'visible' => $fv_fp->_get_option( array('interface','lightbox') ),
      'children' => array(
        array(
          'label' => __( 'Lightbox Title', 'fv-player' ),
          'name' => 'lightbox_caption',
          'description' => __( 'Shows when the lightbox is open.', 'fv-player' ),
          'type' => 'text',
          'visible' => true
        ),
      )
    );
    return $options;
  }

  /*
   * Controls the stylesheet and script loading
   */
  public function enqueue() {
    do_action('fv_player_force_load_lightbox');
  }

  function is_text_lightbox($aArgs) {
    $aLightbox = preg_split('~[;]~', $aArgs['lightbox']);

    foreach ($aLightbox AS $k => $i) {
      if ($i == 'text') {
        return true;
      }
    }
    return false;
  }

  function lightbox_html($html) {

    $aArgs = func_get_args();

    if (isset($aArgs[1]) ) {
      $args = $aArgs[1]->aCurArgs;
      if ( isset($args['lightbox']) && $args['lightbox'] != false && ! get_query_var( 'fv_player_embed' ) && ! get_query_var( 'fv_player_cms_id' ) ) {

        $this->bLoad = true;

        global $fv_fp;

        $iConfWidth = intval($fv_fp->_get_option('width'));
        $iConfHeight = intval($fv_fp->_get_option('height'));

        $iPlayerWidth = ( isset($args['width']) && intval($args['width']) > 0 ) ? intval($args['width']) : $iConfWidth;
        $iPlayerHeight = ( isset($args['height']) && intval($args['height']) > 0 ) ? intval($args['height']) : $iConfHeight;

        /*
         * Going back to the oldschool days...
         * The possibilities here are:
         *
         * true
         * true;text
         * true;Lightbox title
         * true;640;360;Lightbox title
         */
        $aLightbox = preg_split('~[;]~', $args['lightbox']);

        // Properties set up by FV Player DB
        if( !empty($args['lightbox_width']) ) {
          $aLightbox[1] = $args['lightbox_width'];
        }
        if( !empty($args['lightbox_height']) ) {
          $aLightbox[2] = $args['lightbox_height'];
        }
        if( !empty($args['lightbox_caption']) ) {
          $aLightbox[3] = $args['lightbox_caption'];
        }

        $hash = $aArgs[1]->hash;
        $container = "wpfp_".$hash."_container";
        $button = "fv_flowplayer_".$hash."_lightbox_starter";

        $sTitle = '';

        // Using "text" as in "true;640;360;text" makes it a text lightbox and it should not be used for the lightbox title
        if( !empty($aLightbox[3]) && $aLightbox[3] != 'text' ) {
          $sTitle = $aLightbox[3];

        // If we only have "true;Lightbox title" then we know it's the lightbox title
        } else if( !empty($aLightbox[1]) && !isset($aLightbox[2]) && !isset($aLightbox[3]) && $aLightbox[1] != 'text'  ) {
          $sTitle = $aLightbox[1];

        // Allow caption="..." to override the title, some users use that still
        }  else if( empty( $args['playlist'] ) && ! empty( $args['caption'] ) ) {
          $sTitle = wp_kses_post( $args['caption'] );

        } else if( empty( $args['playlist'] ) && ! empty( $args['title'] ) ) {
          $sTitle = wp_kses_post( $args['title'] );

        }

        $title_attribute = ! empty( $sTitle ) ? " title='" . esc_attr( $sTitle ) . "'" : '';

        // Allow scripts for the lightboxed HTML for playlist sizing
        add_filter( 'wp_kses_allowed_html', array( $fv_fp, 'wp_kses_permit_scripts' ), 999, 2 );

        // The original player HTML markup becomes the hidden lightbox content
        // We add the lightboxed class
        $lightbox = str_replace(array('class="freedomplayer ', "class='freedomplayer "), array('class="freedomplayer lightboxed ', "class='freedomplayer lightboxed "), $html);
        // ...and wrap it in hidden DIV
        $lightbox = "\n".'<div id="'.$container.'" class="fv_player_lightbox_hidden" style="display: none">'."\n" . wp_kses_post( $lightbox ) . "</div>\n";

        remove_filter( 'wp_kses_allowed_html', array( $fv_fp, 'wp_kses_permit_scripts' ), 999, 2 );

        if ( $this->is_text_lightbox($args) ) {
          if( !empty($args['playlist']) ) {
            list( $playlist_items_external_html, $aPlaylistItems, $aSplashScreens, $aCaptions ) = $fv_fp->build_playlist( $args, $args['src'], $args['src1'], $args['src2'], false, false );
            if( is_array($aCaptions) ) {
              $html = '<ul class="fv-player-lightbox-text-playlist" rel="'.$container.'">';
              foreach( $aCaptions AS $key => $caption ) {
                $html .= '<li><a';
                // we only add the fancyBox attributes for the first link as having multiple links for one fancyBox view causes issues, we handle the clicks in JS
                if( $key == 0 ) {
                  $html .= $this->fancybox_opts().' href="#'.$container.'"';
                } else {
                  $html .= ' href="#"';
                }
                $html .= ' class="fv-player-lightbox-link" title="'.esc_attr($caption).'">'.$caption.'</li>';
              }
              $html .= '</ul>';
            }
          } else {
            $html = '<a'.$this->fancybox_opts().' id="'.$button.'"' . $title_attribute . ' class="fv-player-lightbox-link" href="#" data-src="#'.$container.'">' . $sTitle . '</a>';
          }

          // in this case we put the lightboxed player into footer as putting it behind the anchor might break the parent block element
          $this->lightboxHtml .= $lightbox;

        } else {
          $iWidth = ( isset($aLightbox[1]) && intval($aLightbox[1]) > 0 ) ? intval($aLightbox[1]) : ( ($iPlayerWidth > $iConfWidth) ? $iPlayerWidth : $iConfWidth );
          $iHeight = ( isset($aLightbox[2]) && intval($aLightbox[2]) > 0 ) ? intval($aLightbox[2]) : ( ($iPlayerHeight > $iConfHeight) ? $iPlayerHeight : $iConfHeight );

          // new classes to be added
          $add_classes = array( 'lightbox-starter' );

          // use the align for the lightbox button
          if( isset($args['lightbox_align']) ) {
            $add_classes[] = 'align' . $args['lightbox_align'];
          }

          $sSplash = apply_filters('fv_flowplayer_playlist_splash', $args['splash'], $args['src']);

          // re-use the existing player HTML and add data-fancybox, data-options, new id and href
          $html = str_replace( '<div id="wpfp_'.$hash.'" ', '<div'.$this->fancybox_opts($sSplash).' id="'.$button.'"' . $title_attribute . ' href="#'.$container.'" ', $html );

          // add all the new classes
          $html = str_replace( 'class="freedomplayer ', 'class="freedomplayer ' . implode(' ', $add_classes ). ' ' , $html );

          // use new size
          $html = str_replace( array( "max-width: ".$iPlayerWidth."px", "max-height: ".$iPlayerHeight."px"), array('max-width: '.$iWidth.'px', 'max-height: '.$iHeight.'px'), $html );

          // Only use new ratio for responsiveness if the lightbox size is specified
          $have_custom_size = ! empty( $aLightbox[1] ) || ! empty( $aLightbox[2] );

          if ( $have_custom_size ) {
            $ratio = $iHeight / $iWidth;
            if( $ratio > 0 ) {
              $ratio = round($ratio, 4);
              $html = preg_replace( '~ data-ratio=".*?"~', ' data-ratio="'.$ratio.'"', $html );

              $ratio = str_replace(',','.', $ratio * 100 );
              $html = preg_replace( '~<div class="fp-ratio".*?</div>~', '<div class="fp-ratio" style="padding-top: '.$ratio.'%"></div>', $html );
            }
          }

          // this is how we link playlist items to the player
          $html = str_replace( ' rel="wpfp_'.$hash.'"', ' rel="'.$button.'"', $html );

          // we removed FV Player data to make sure it won't initialize here
          $html = preg_replace( "~ data-item='.*?'~", '', $html );
          $html = preg_replace( '~ data-item=".*?"~', '', $html );

          // put the hidden player after our lightbox button
          $html .= $lightbox;
        }
      }
    }
    return $html;
  }

  /*
   * Load the scripts and stylesheets
   */
  function load_scripts() {
    global $fv_fp, $fv_wp_flowplayer_ver;

    $aConf = array();
    $aConf['lightbox_images'] = $fv_fp->_get_option('lightbox_images'); // should FV Player fancybox be used to show images?
    $aConf['js_url'] = flowplayer::get_plugin_url().'/js/fancybox.js';
    $aConf['css_url'] = FV_FP_RELATIVE_PATH . '/css/fancybox.css';

    $this->css_enqueue(true);

    if ( $fv_fp->_get_option('js-optimize') && ! did_action('fv_player_force_load_lightbox')  ) {

      $script = "
( function() {
  let fv_player_fancybox_loaded = false;
  const triggers = document.querySelectorAll( '[data-fancybox], .fp-playlist-external[rel$=_lightbox_starter] a' );
  for (let i = 0; i < triggers.length; i++) {
    triggers[i].addEventListener( 'click', function( e ) {
      if ( fv_player_fancybox_loaded ) return;
      fv_player_fancybox_loaded = true;

      let i = this,
        l = document.createElement('link'),
        s = document.createElement('script');

      e.preventDefault();
      e.stopPropagation();

      l.rel = 'stylesheet';
      l.type = 'text/css';
      l.href = fv_player_lightbox.css_url;
      document.head.appendChild(l);

      s.onload = function () {
        let evt = new MouseEvent('click',{bubbles: true,cancelable:true,view:window});
        i.dispatchEvent(evt);
      };
      s.src = fv_player_lightbox.js_url;
      document.head.appendChild(s);
    });
  }
})();
";

      if( !defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG ) {
        // remove /* comments */
        $script = preg_replace( '~/\*[\s\S]*?\*/~m', '', $script );
        // remove whitespace
        $script = preg_replace( '~\s+~m', ' ', $script );
      }

      // Load inline JS only, but will this work with WordPress 5.7?
      wp_register_script( 'fv_player_lightbox', '', array( 'jquery') );
      wp_enqueue_script( 'fv_player_lightbox' );
      wp_add_inline_script( 'fv_player_lightbox', trim( $script ) );

    } else {

      wp_enqueue_script( 'fv_player_lightbox', flowplayer::get_plugin_url().'/js/fancybox.js', 'jquery', $fv_wp_flowplayer_ver, true );
    }

    wp_localize_script( 'fv_player_lightbox', 'fv_player_lightbox', $aConf );
  }

  function html_to_lightbox_videos($content) {

    //  todo: disabling the option should turn this off
    if (stripos($content, 'colorbox') !== false) {
      $content = preg_replace_callback('~<a[^>]*?class=[\'"][^\'"]*?\bcolorbox\b[^\'"]*?[\'"][^>]*?>([\s\S]*?)</a>~', array($this, 'html_to_lightbox_videos_callback'), $content);
      return $content;
    }

    if( stripos($content, 'lightbox') !== false ) {
      $content = preg_replace_callback('~<a[^>]*?class=[\'"][^\'"]*?\blightbox\b[^\'"]*?[\'"][^>]*?>([\s\S]*?)</a>~', array($this, 'html_to_lightbox_videos_callback'), $content);
      return $content;
    }

    return $content;
  }

  function html_to_lightbox_videos_callback($matches) {
    $html = $matches[0];
    $caption = trim($matches[1]);
    if( stripos($html,'.mp4') !== false &&
       stripos($html,'.webm') !== false &&
       stripos($html,'.m4v') !== false &&
       stripos($html,'.mov') !== false &&
       stripos($html,'.ogv') !== false &&
       stripos($html,'.ogg') !== false &&
       stripos($html,'.m3u8') !== false &&
       stripos($html,'youtube.com/') !== false &&
       stripos($html,'youtu.be/') !== false &&
       stripos($html,'vimeo.com/') !== false
       ) {
      return $html;
    }

    if( preg_match( '~href=[\'"](.*?(?:mp4|webm|m4v|mov|ogv|ogg|m3u8|youtube\.com|youtu\.be|vimeo.com).*?)[\'"]~', $html, $href ) ) {
      if( stripos($caption,'<img') === 0 ) {
        return '[fvplayer src="'.esc_attr($href[1]).'" lightbox="true;text" caption_html="'.base64_encode($caption).'"]';
      } else {
        return '[fvplayer src="'.esc_attr($href[1]).'" lightbox="true;text" caption="'.esc_attr($caption).'"]';
      }
    }

    return $html;
  }

  function html_lightbox_images($content) {
    global $fv_fp;
    //TODO IMAGES

    if( $fv_fp->_get_option('lightbox_images') === false ) {
      return $content;
    }

    // Look for any image links
    $content = preg_replace_callback('~(<a[^>]*?>\s*?)~', array($this, 'html_lightbox_images_callback'), $content, -1, $count );

    if( $count ) {
      $this->bLoad = true;
    }

    return $content;
  }

  function html_lightbox_images_callback($matches) {
    if( stripos($matches[1],'data-fancybox') ) return $matches[0];

    if (!preg_match('/href=[\'"][^\'"]*?(jpeg|jpg|jpe|gif|png)(?:\?.*?|\s*?)[\'"]/i', $matches[1]))
      return $matches[0];

    $matches[1] = str_replace( '<a ', '<a data-fancybox="gallery" ', $matches[1] );

    return $matches[1];
  }

  function disable_autoplay($aArgs) {
    if (isset($aArgs['lightbox'])) {
      $aArgs['autoplay'] = 'false';
    }
    return $aArgs;
  }

  function lightbox_button_align($aArgs) {
    if (isset($aArgs['lightbox']) && !empty($aArgs['align']) ) {
      $aArgs['lightbox_align'] = $aArgs['align']; // save align to new key
      unset($aArgs['align']); // do not allow the align for lightbox as it doesn't make sense
    }
    return $aArgs;
  }

  function lightbox_playlist_style($aArgs) {
    if ( isset( $aArgs['lightbox'] ) && $aArgs['lightbox'] ) { // we force the slider playlist style as that' the only one where the lightbox works properly with the sizing right now
      $aArgs['liststyle'] = 'slider';
    }
    return $aArgs;
  }

  /*
   * Check if scripts and styles is the lightbox is actually used or it scripts are set to load everywhere. Called in FV Player's flowplayer_prepare_scripts()
   */
  function maybe_load() {
    global $fv_fp;
    if(
      $this->should_load() ||
      $fv_fp->should_force_load_js() || // "Load FV Player JS everywhere" is enabled
      $fv_fp->_get_option('lightbox_force') // "Remove fancyBox" compatibility option is enabled
    ) {
      $this->load_scripts();
    }
  }

  function parse_args( $aArgs ) {
    foreach ($aArgs AS $k => $i) {
      if ($i == 'text') {
        unset($aArgs[$k]);
        $bUseAnchor = true;
      }
    }
    return $aArgs;
  }

  function parse_html_caption( $aArgs ) {
    if( isset($aArgs['caption_html']) && $aArgs['caption_html'] ) {
      $aArgs['caption'] = base64_decode($aArgs['caption_html']);
      unset($aArgs['caption_html']);
    }
    return $aArgs;
  }

  function shortcode_editor() {
    global $fv_fp;

    $bLightbox = $fv_fp->_get_option(array('interface','lightbox'));

    if ($bLightbox) {
      ?>
      <script>

        jQuery(document).on('fv_flowplayer_shortcode_parse', function (e, shortcode) {
          var sLightbox = shortcode.match(/lightbox="(.*?)"/);
          if (sLightbox && typeof (sLightbox) != "undefined" && typeof (sLightbox[1]) != "undefined") {
            sLightbox = sLightbox[1];

            if( sLightbox ) {
              var aLightbox = sLightbox.split(/[;]/, 4);
              if (aLightbox.length > 2) {
                for (var i in aLightbox) {
                  if (i == 0 && aLightbox[i] == 'true') {
                    fv_player_editor.get_field('lightbox').prop('checked', true).trigger('change');
                  } else if (i == 1) {
                    // ignore Lightbox Width
                  } else if (i == 2) {
                    // ignore Lightbox Height
                  } else if (i == 3) {
                    fv_player_editor.get_field('lightbox_caption').val( aLightbox[i].trim() ).trigger('change');
                  }
                }
              } else {
                if (typeof (aLightbox[0]) != "undefined" && aLightbox[0] == 'true') {
                  fv_player_editor.get_field('lightbox').prop('checked', true).trigger('change');
                }
                if (typeof (aLightbox[1]) != "undefined") {
                  fv_player_editor.get_field('lightbox_caption').val( aLightbox[1].trim() ).trigger('change');
                }
              }
            }
          }
        });
      </script>
      <?php
    }
  }

  function lightbox_admin_integrations_html() {
    global $fv_fp;
    $fv_fp->_get_checkbox(__( 'Remove fancyBox', 'fv-player' ), 'lightbox_force', __( 'Use if FV Player lightbox is not working and you see a "fancyBox already initialized" message on JavaScript console.', 'fv-player' ));
  }

  function lightbox_admin_interface_html() {
    global $fv_fp;
    $fv_fp->_get_checkbox(__( 'Enable video lightbox', 'fv-player' ), array('interface', 'lightbox'), __( 'You can also put in <code>&lt;a href="http://path.to.your/video.mp4" class="colorbox"&gt;Your link title&lt;/a&gt;</code> for a quick lightboxed video.', 'fv-player' ));
  }

  function lightbox_admin_default_options_html() {
    global $fv_fp;
    ?>
    <tr>
      <td style="width: 250px"><label for="lightbox_images"><?php esc_html_e( 'Use video lightbox for images as well', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="hidden" value="false" name="lightbox_images" />
          <input type="checkbox" value="true" name="lightbox_images" id="lightbox_images" <?php if ($fv_fp->_get_option('lightbox_images')) echo 'checked="checked"'; ?> />
          <?php echo wp_kses( __( 'Will group images as well as videos into the same lightbox gallery. Turn <strong>off</strong> your lightbox plugin when using this.', 'fv-player' ), array( 'strong' => array() ) ); ?> <span class="more"><?php echo wp_kses( __('Also works with WordPress <code>[gallery]</code> galleries - these are automatically switched to link to image URLs rather than the attachment pages.'), array( 'code' => array() ) ); ?></span> <a href="#" class="show-more">(&hellip;)</a>
        </p>
      </td>
    </tr>
    <tr id="lightbox-wp-galleries">
      <td style="width: 250px"><label for="lightbox_improve_galleries"><?php esc_html_e( 'Use video lightbox for WP Galleries', 'fv-player' ); ?>:</label></td>
      <td>
        <p class="description">
          <input type="hidden" value="false" name="lightbox_improve_galleries" />
          <input type="checkbox" value="true" name="lightbox_improve_galleries" id="lightbox_improve_galleries" <?php if ($fv_fp->_get_option('lightbox_improve_galleries')) echo 'checked="checked"'; ?> />
          <?php esc_html_e( 'Your gallery items will link to image files directly to allow this.', 'fv-player' ); ?>
        </p>
      </td>
    </tr>
    <script>
      jQuery(document).ready(function(){
        var lightbox_images = jQuery('#lightbox_images');
        if(lightbox_images.prop('checked')){
            jQuery('#lightbox-wp-galleries').show();
          }else{
            jQuery('#lightbox-wp-galleries').hide();
          }
        lightbox_images.on('click',function(){
          if(jQuery(this).prop('checked')){
            jQuery('#lightbox-wp-galleries').show();
          }else{
            jQuery('#lightbox-wp-galleries').hide();
          }
        })
      })
    </script>
    <?php
  }

  function fancybox_opts( $splash = false ) {
    $options = array('touch' => false);
    if( !empty($splash) ) $options['thumb'] = $splash;
    return " data-fancybox='gallery' data-options='".wp_json_encode($options)."'";
  }

  /*
   * Was it enqueued  with self::enqueue() ?
   */
  function should_load() {
    return $this->bLoad || did_action('fv_player_force_load_lightbox');
  }

}

global $FV_Player_lightbox;
$FV_Player_lightbox = FV_Player_lightbox::_get_instance();

function FV_Player_lightbox() {
  return FV_Player_lightbox::_get_instance();
}
