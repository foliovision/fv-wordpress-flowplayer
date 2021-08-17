<?php

class FV_Player_lightbox {

  static $instance = null;

  private $lightboxHtml;
  
  public $bLoad = false;

  public static function _get_instance() {
    if( !self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }
  
  public function __construct() {
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
    
    wp_enqueue_style( 'fv_player_lightbox', FV_FP_RELATIVE_PATH . '/css/fancybox.css', array(), $fv_wp_flowplayer_ver );
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
    if( !$args['link'] && $fv_fp->_get_option('lightbox_images') && $fv_fp->_get_option('lightbox_improve_galleries') ) {
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

  function disp__lightboxed_players() {
    if (strlen($this->lightboxHtml)) {
      echo $this->lightboxHtml . "<!-- lightboxed players -->\n\n";
    }
  }

  /*
   * Controls the stylesheet and script loading
   */
  function enqueue() {
    $this->bLoad = true;
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
    // disable lightbox HTML for previews
    if (flowplayer::is_preview()) {
      return $html;
    }

    $aArgs = func_get_args();

    if (isset($aArgs[1]) ) {
      $args = $aArgs[1]->aCurArgs;
      if( isset($args['lightbox']) && !get_query_var('fv_player_embed') ) {

        $this->enqueue();
        
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
         * true;Lightbox title;text - not sure, TODO
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
          
        } else if( empty($args['playlist']) && !empty($args['caption']) ) {
          $sTitle = $args['caption'];
        }
        
        if( !empty($sTitle) ) {
          $sTitle = " title='".esc_attr($sTitle)."'";
        }
        
        // The original player HTML markup becomes the hidden lightbox content
        // We add the lightboxed class
        $lightbox = str_replace(array('class="flowplayer ', "class='flowplayer "), array('class="flowplayer lightboxed ', "class='flowplayer lightboxed "), $html);
        // ...and wrap it in hidden DIV
        $lightbox = "\n".'<div id="'.$container.'" class="fv_player_lightbox_hidden" style="display: none">'."\n".$lightbox."</div>\n";
        
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
            $html = '<a'.$this->fancybox_opts().' id="'.$button.'"'.$sTitle.' class="fv-player-lightbox-link" href="#" data-src="#'.$container.'">'.$args['caption'].'</a>';
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
          $html = str_replace( '<div id="wpfp_'.$hash.'" ', '<div'.$this->fancybox_opts($sSplash).' id="'.$button.'"'.$sTitle.' href="#'.$container.'" ', $html );

          // add all the new classes
          $html = str_replace( 'class="flowplayer ', 'class="flowplayer ' . implode(' ', $add_classes ). ' ' , $html );

          // use new size
          $html = str_replace( array( "max-width: ".$iPlayerWidth."px", "max-height: ".$iPlayerHeight."px"), array('max-width: '.$iWidth.'px', 'max-height: '.$iHeight.'px'), $html );

          // new ratio for responsiveness
          if( $iWidth > 0 ) {
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
    
    $this->css_enqueue(true);

    wp_enqueue_script( 'fv_player_lightbox', flowplayer::get_plugin_url().'/js/fancybox.js', 'jquery', $fv_wp_flowplayer_ver, true );
    wp_localize_script( 'fv_player_lightbox', 'fv_player_lightbox', $aConf );
  }

  function html_to_lightbox_videos($content) {

    //  todo: disabling the option should turn this off
    if (stripos($content, 'colorbox') !== false) {
      $content = preg_replace_callback('~<a[^>]*?class=[\'"][^\'"]*?colorbox[^\'"]*?[\'"][^>]*?>([\s\S]*?)</a>~', array($this, 'html_to_lightbox_videos_callback'), $content);
      return $content;
    }

    if( stripos($content, 'lightbox') !== false ) {
      $content = preg_replace_callback('~<a[^>]*?class=[\'"][^\'"]*?lightbox[^\'"]*?[\'"][^>]*?>([\s\S]*?)</a>~', array($this, 'html_to_lightbox_videos_callback'), $content);
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

    $content = preg_replace_callback('~(<a[^>]*?>\s*?)(<img.*?>)~', array($this, 'html_lightbox_images_callback'), $content, -1, $count );

    if( $count ) {
      $this->enqueue();
    }

    return $content;
  }

  function html_lightbox_images_callback($matches) {
    if( stripos($matches[1],'data-fancybox') ) return $matches[0];
    
    if (!preg_match('/href=[\'"].*?(jpeg|jpg|jpe|gif|png)(?:\?.*?|\s*?)[\'"]/i', $matches[1]))
      return $matches[0];

    $matches[1] = str_replace( '<a ', '<a data-fancybox="gallery" ', $matches[1] );

    return $matches[1] . $matches[2];
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
    if (isset($aArgs['lightbox'])) { // we force the slider playlist style as that' the only one where the lightbox works properly with the sizing right now
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
      $fv_fp->should_force_load_js() || // "Load FV Flowplayer JS everywhere" is enabled
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

      <tr<?php if (!$bLightbox) echo ' style="display: none"'; ?>>
        <th scope="row" class="label"><label for="fv_wp_flowplayer_field_lightbox" class="alignright">Lightbox popup</label></th>
        <td class="field">
          <input type="checkbox" id="fv_wp_flowplayer_field_lightbox" name="fv_wp_flowplayer_field_lightbox" />        
          <input type="text" id="fv_wp_flowplayer_field_lightbox_width" name="fv_wp_flowplayer_field_lightbox_width" style="width: 12%" placeholder="Width" />
          <input type="text" id="fv_wp_flowplayer_field_lightbox_height" name="fv_wp_flowplayer_field_lightbox_height" style="width: 12%" placeholder="Height" />
          <input type="text" id="fv_wp_flowplayer_field_lightbox_caption" name="fv_wp_flowplayer_field_lightbox_caption" style="width: 62%" placeholder="Title" />
        </td>
      </tr>
      <script>

        jQuery(document).on('fv_flowplayer_shortcode_parse', function (e, shortcode) {

          document.getElementById("fv_wp_flowplayer_field_lightbox").checked = 0;
          document.getElementById("fv_wp_flowplayer_field_lightbox_width").value = '';
          document.getElementById("fv_wp_flowplayer_field_lightbox_height").value = '';
          document.getElementById("fv_wp_flowplayer_field_lightbox_caption").value = '';

          var sLightbox = shortcode.match(/lightbox="(.*?)"/);
          if (sLightbox && typeof (sLightbox) != "undefined" && typeof (sLightbox[1]) != "undefined") {
            sLightbox = sLightbox[1];

            if (sLightbox) {
              var aLightbox = sLightbox.split(/[;]/, 4);
              if (aLightbox.length > 2) {
                for (var i in aLightbox) {
                  if (i == 0 && aLightbox[i] == 'true') {
                    document.getElementById("fv_wp_flowplayer_field_lightbox").checked = 1;
                  } else if (i == 1) {
                    document.getElementById("fv_wp_flowplayer_field_lightbox_width").value = parseInt(aLightbox[i]);
                  } else if (i == 2) {
                    document.getElementById("fv_wp_flowplayer_field_lightbox_height").value = parseInt(aLightbox[i]);
                  } else if (i == 3) {
                    document.getElementById("fv_wp_flowplayer_field_lightbox_caption").value = aLightbox[i].trim();
                  }
                }
              } else {
                if (typeof (aLightbox[0]) != "undefined" && aLightbox[0] == 'true') {
                  document.getElementById("fv_wp_flowplayer_field_lightbox").checked = 1;
                }
                if (typeof (aLightbox[1]) != "undefined") {
                  document.getElementById("fv_wp_flowplayer_field_lightbox_caption").value = aLightbox[1].trim();
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
    $fv_fp->_get_checkbox(__('Remove fancyBox', 'fv-wordpress-flowplayer'), 'lightbox_force', __('Use if FV Player lightbox is not working and you see a "fancyBox already initialized" message on JavaScript console.', 'fv-wordpress-flowplayer'));
  }

  function lightbox_admin_interface_html() {
    global $fv_fp;
    $fv_fp->_get_checkbox(__('Enable video lightbox', 'fv-wordpress-flowplayer'), array('interface', 'lightbox'), __('You can also put in <code>&lt;a href="http://path.to.your/video.mp4" class="colorbox"&gt;Your link title&lt;/a&gt;</code> for a quick lightboxed video.', 'fv-wordpress-flowplayer'));
  }

  function lightbox_admin_default_options_html() {
    global $fv_fp;
    ?>
    <tr>
      <td style="width: 250px"><label for="lightbox_images"><?php _e('Use video lightbox for images as well', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="hidden" value="false" name="lightbox_images" />
          <input type="checkbox" value="true" name="lightbox_images" id="lightbox_images" <?php if ($fv_fp->_get_option('lightbox_images')) echo 'checked="checked"'; ?> />
          <?php _e('Will group images as well as videos into the same lightbox gallery. Turn <strong>off</strong> your lightbox plugin when using this.', 'fv-wordpress-flowplayer'); ?> <span class="more"><?php _e('Also works with WordPress <code>[gallery]</code> galleries - these are automatically switched to link to image URLs rather than the attachment pages.'); ?></span> <a href="#" class="show-more">(&hellip;)</a>
        </p>
      </td>
    </tr>
    <tr id="lightbox-wp-galleries">
      <td style="width: 250px"><label for="lightbox_improve_galleries"><?php _e('Use video lightbox for WP Galleries', 'fv-wordpress-flowplayer'); ?>:</label></td>
      <td>
        <p class="description">
          <input type="hidden" value="false" name="lightbox_improve_galleries" />
          <input type="checkbox" value="true" name="lightbox_improve_galleries" id="lightbox_improve_galleries" <?php if ($fv_fp->_get_option('lightbox_improve_galleries')) echo 'checked="checked"'; ?> />
          <?php _e('Your gallery items will link to image files directly to allow this.', 'fv-wordpress-flowplayer'); ?>
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
    return " data-fancybox='gallery' data-options='".json_encode($options)."'";
  }

  /*
   * Was it enqueued  with self::enqueue() ?
   */
  function should_load() {
    return $this->bLoad;
  }

}

global $FV_Player_lightbox;
$FV_Player_lightbox = FV_Player_lightbox::_get_instance();

function FV_Player_lightbox() {
  return FV_Player_lightbox::_get_instance();
}