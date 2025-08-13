<?php
/*  FV Folopress Base Class - set of useful functions for Wordpress plugins
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( file_exists( dirname(__FILE__) . '/../includes/fp-api.php') ) {
  require_once( dirname(__FILE__) . '/../includes/fp-api.php' );
}

if( file_exists( dirname(__FILE__) . '/../includes/fp-api-private.php') ) {
  require_once( dirname(__FILE__) . '/../includes/fp-api-private.php' );
}

class flowplayer extends FV_Wordpress_Flowplayer_Plugin_Private {
  private $count = 0;
  /**
   * Relative URL path
   */
  const FV_FP_RELATIVE_PATH = '';
  /**
   * Where the config file should be
   */
  private $conf_path = '';
  /**
   * Configuration variables array
   */
  public $conf = array();

  public $load_tabs = false;
  /**
   * Store scripts to load in footer
   */
  public $scripts = array();

  var $ajax_count = 0;

  var $ret = array('html' => false, 'script' => false);

  var $hash = false;

  var $bCSSInline = false;

  var $bCSSPlaylists = false;

  public $overlay_css_default = ".wpfp_custom_ad { position: absolute; bottom: 10%; z-index: 20; width: 100%; }\n.wpfp_custom_ad_content { background: white; margin: 0 auto; position: relative }";

  public $overlay_css_bottom = ".wpfp_custom_ad { position: absolute; bottom: 0; z-index: 20; width: 100%; }\n.wpfp_custom_ad_content { background: white; margin: 0 auto; position: relative }";

  public $load_dash = false;

  public $load_hlsjs = false;

  public $bCSSLoaded = false;

  public $aDefaultSkins = array(
      'skin-custom' => array(
          'hasBorder' => false,
          'borderColor' => false,
          'durationColor' => '#eeeeee',
          'progressColor' => '#bb0000',
          'backgroundColor' => '#333333',
          'font-face' =>'Tahoma, Geneva, sans-serif',
          'design-timeline' => 'fp-full',
          'design-icons' => ' '
      ),
      'skin-slim' => array(
          'hasBorder' => false,
          'borderColor' => false,
          'backgroundColor' => 'transparent',
          'font-face' => 'Tahoma, Geneva, sans-serif',
          'durationColor' => false,
          'design-timeline' => 'fp-slim',
          'design-icons' => 'fp-edgy'
        ),
      'skin-youtuby' => array(
          'hasBorder' => false,
          'borderColor' => false,
          'backgroundColor' => 'rgba(0, 0, 0, 0.5)',
          'font-face' =>'Tahoma, Geneva, sans-serif',
          'durationColor' => false,
          'design-timeline' => 'fp-full',
          'design-icons' => ' '
        )
    );

  public $css_logo_positions = array(
    'bottom-left'  => "margin: auto auto 2% 2%",
    'bottom-right' => "margin: auto 2% 2% auto",
    'top-left'     => "margin: 2% auto auto 2%",
    'top-right'    => "margin: 2% 2% auto auto",
  );

  private $help_html = array(
    'a'     => array( 'href' => array(), 'target' => array() ),
    'code'  => array(),
    'img'   => array( 'src' => array(), 'srcset' => array(), 'width' => array() ),
    'small' => array(),
  );


  public function __construct() {
    //load conf data into stack
    $this->_get_conf();

    if( is_admin() ) {
      //  update notices
      $this->readme_URL = 'https://plugins.trac.wordpress.org/browser/fv-player/trunk/readme.txt?format=txt';
      if( !has_action( 'in_plugin_update_message-fv-player/fv-player.php' ) ) {
        add_action( 'in_plugin_update_message-fv-player/fv-player.php', array( &$this, 'plugin_update_message' ) );
      }

       //  pointer boxes
      parent::__construct();
    }

    // define needed constants
    if (!defined('FV_FP_RELATIVE_PATH')) {
      define('FV_FP_RELATIVE_PATH', flowplayer::get_plugin_url() );
    }

    //add_filter( 'fv_flowplayer_caption', array( $this, 'get_duration_playlist' ), 10, 3 );
    add_filter( 'fv_flowplayer_inner_html', array( $this, 'get_duration_video' ), 10, 2 );

    add_filter( 'fv_flowplayer_video_src', array( $this, 'get_amazon_secure') );

    add_action( 'init', array( $this, 'enable_cdn_rewrite_maybe') );

    add_filter( 'fv_flowplayer_splash', array( $this, 'get_amazon_secure_long') );
    add_filter( 'fv_flowplayer_playlist_splash', array( $this, 'get_amazon_secure_long') );
    add_filter( 'fv_flowplayer_resource', array( $this, 'get_amazon_secure_long') );

    add_action( 'wp_enqueue_scripts', array( $this, 'css_enqueue' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'css_enqueue' ) );

    add_action( 'init', array( $this, 'fv_player_embed_rewrite_endpoint' ) );

    add_filter( 'rewrite_rules_array', array( $this, 'fv_player_embed_rewrite_rules_fix' ), PHP_INT_MAX );
    add_filter( 'query_vars', array( $this, 'rewrite_vars' ) );

    add_filter( 'fv_player_custom_css', array( $this, 'popup_css' ), 10 );
    add_filter( 'fv_player_custom_css', array( $this, 'custom_css' ), 11 );

    add_action( 'template_redirect', array( $this, 'template_preview' ), 0 );

    add_action( 'wp_head', array( $this, 'template_embed_buffer' ), PHP_INT_MAX);
    add_action( 'wp_footer', array( $this, 'template_embed' ), 0 );

    add_action( 'do_rocket_lazyload', array( $this, 'preview_no_lazy_load' ) );

    add_filter( 'fv_flowplayer_video_src', array( $this, 'add_fake_extension' ) );
    add_filter('fv_player_item', array($this, 'get_video_checker_media') );

    add_filter( 'searchwp_pre_set_post', array( $this, 'searchwp_pre_post_content' ) );
    add_filter( 'searchwp_set_post', array( $this, 'searchwp_post_content' ) );
  }


  public function _get_checkbox() {
    $args_num = func_num_args();

    // new method syntax with all options in the first parameter (which will be an array)
    if ($args_num == 1) {
      $options = func_get_arg(0);

      // options must be an array
      if (!is_array($options)) {
          throw new Exception('Options parameter passed to the _get_checkbox() method needs to be an array!');
      }

      $first_td_class = ( ! empty( $options['first_td_class'] ) ? $options['first_td_class'] : '' );
      $key            = (!empty($options['key']) ? $options['key'] : '');
      $name           = (!empty($options['name']) ? $options['name'] : '');
      $help           = (!empty($options['help']) ? $options['help'] : '');
      $more           = (!empty($options['more']) ? $options['more'] : '');
      $disabled       = !empty($options['disabled']);

      if (!$key || !$name) {
        throw new Exception('Both, "name" and "key" options need to be set for _get_checkbox()!');
      }
    } else if ($args_num >= 2) {
      // old method syntax with function parameters defined as ($name, $key, $help = false, $more = false)
      $first_td_class = 'first';
      $name = func_get_arg(0);
      $key = func_get_arg(1);
      $help = ($args_num >= 3 ? func_get_arg(2) : false);
      $more = ($args_num >= 4 ? func_get_arg(3) : false);

      $disabled = false;

    } else {
        throw new Exception('Invalid number of arguments passed to the _get_checkbox() method!');
    }

    $checked = $this->_get_option( $key );
    if ( $checked === 'false' ) {
      $checked = false;
    }

    if ( is_array( $key ) && count( $key ) > 1 ) {
      $key = $key[0] . '[' . $key[1] . ']';
    }
      ?>
      <tr>
          <td<?php echo $first_td_class ? ' class="' . esc_attr( $first_td_class ) . '"' : ''; ?>><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $name ); ?>:</label></td>
          <td>
              <p class="description">
                  <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="false"/>
                  <input type="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="true"<?php
                    if ( $checked ) { echo ' checked="checked"'; }
                    if ( $disabled ) { echo ' disabled'; }

                    if (isset($options) && isset($options['data']) && is_array($options['data'])) {
                        foreach ($options['data'] as $data_item => $data_value) {
                            echo ' data-' . esc_attr( $data_item ) . '="' . esc_attr( $data_value ) . '"';
                        }
                    }
                  ?> />
                  <?php if ( $help ) {
                      echo wp_kses( $help, $this->help_html );
                  } ?>
                  <?php if ( $more ) { ?>
                      <span class="more"><?php echo wp_kses( $more, $this->help_html ); ?></span> <a href="#" class="show-more">(&hellip;)</a>
                  <?php } ?>
              </p>
          </td>
      </tr>
      <?php

    // Disabled inputs are not sent in POST so if it's checked we retain the value using hidden input
    if ( $disabled && $checked ) {
      ?>
        <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="true"/>
      <?php
    }
  }


  public function _get_radio($options) {
    // options must be an array
    if (!is_array($options)) {
      throw new Exception('Options parameter passed to the _get_radio() method needs to be an array!');
    }

    $first_td_class = ( ! empty( $options['first_td_class'] ) ? $options['first_td_class'] : '' );
    $key            = (!empty($options['key']) ? $options['key'] : '');
    $name           = (!empty($options['name']) ? $options['name'] : '');
    $values         = (!empty($options['values']) ? $options['values'] : '');
    $value_keys     = (is_array($values) ? array_keys($values) : array());
    $help           = (!empty($options['help']) ? $options['help'] : '');
    $more           = (!empty($options['more']) ? $options['more'] : '');
    $default        = (!empty($options['default']) ? $options['default'] : reset($value_keys));

    if (!$key || !$values) {
      throw new Exception('The "name", "key" and "values" options need to be set for _get_radio()!');
    }

    $saved_value = $this->_get_option( $key );
    $selected = $default;

    // check if any of the given values match the saved one and store it for a pre-select
    foreach ($values as $index => $input_value) {
        if ($saved_value == $index) {
            $selected = $index;
            break;
        }
    }

    if ( is_array( $key ) && count( $key ) > 1 ) {
      $key = $key[0] . '[' . $key[1] . ']';
    }

    // determine style (display all checkboxes below each other or next to each other in multiple columns
    $style = (!empty($options['style']) ? $options['style'] : 'rows');

    // rows style
    if ($style == 'rows') {
      ?>
        <tr>
            <td<?php echo $first_td_class ? ' class="' . esc_attr( $first_td_class ) . '"' : ''; ?>><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $name ); ?>:</label></td>
            <td>
                <fieldset>
                    <p>
                      <?php
                      foreach ( $values as $index => $input_value ) {
                        ?>

                          &nbsp;<input type="radio" name="<?php echo esc_attr( $key ); ?>"
                                       id="<?php echo esc_attr( $key . '-' . $input_value ); ?>" value="<?php echo esc_html( $index ); ?>"<?php
                        if ( ( $selected == $index ) ) {
                          echo ' checked="checked"';
                        }

                        if ( isset( $options ) && isset( $options['data'] ) && is_array( $options['data'] ) ) {
                          foreach ( $options['data'] as $data_item => $data_value ) {
                            echo ' data-' . esc_attr( $data_item ) . '="' . esc_attr( $data_value ) . '"';
                          }
                        }
                        ?> /> <label for="<?php echo esc_attr( $key . '-' . $input_value ); ?>"><?php echo esc_html( $input_value ); ?></label><br/>

                        <?php
                      }
                      ?>

                    </p>
                </fieldset>
              <?php if ( $help ) {
                echo wp_kses( $help, $this->help_html );
              } ?>
              <?php if ( $more ) { ?>
                  <span class="more"><?php echo wp_kses( $more, $this->help_html ); ?></span> <a href="#" class="show-more">(&hellip;)</a>
              <?php } ?>
            </td>
        </tr>
      <?php
    } else {

      // columns style
?>
          <tr>
<?php
      foreach ( $values as $index => $input_value ) {
        ?>
              <td style="white-space: nowrap">
                  <fieldset>
                      <p>
                        &nbsp;<input type="radio" name="<?php echo esc_attr( $key ); ?>"
                                     id="<?php echo esc_attr( $key . '-' . $input_value ); ?>"
                                     value="<?php echo esc_attr( $index ); ?>"<?php
                      if ( ( $selected == $index ) ) {
                        echo ' checked="checked"';
                      }

                      if ( isset( $options ) && isset( $options['data'] ) && is_array( $options['data'] ) ) {
                        foreach ( $options['data'] as $data_item => $data_value ) {
                          echo ' data-' . esc_attr( $data_item ) . '="' . esc_attr( $data_value ) . '"';
                        }
                      }
                      ?> /> <label for="<?php echo esc_attr( $key . '-' . $input_value ); ?>"><?php echo esc_html( $input_value ); ?></label><br/>
                      </p>
                  </fieldset>
                <?php if ( $help ) {
                  echo wp_kses( $help, $this->help_html );
                } ?>
                <?php if ( $more ) { ?>
                    <span class="more"><?php echo wp_kses( $more, $this->help_html ); ?></span> <a href="#" class="show-more">(&hellip;)</a>
                <?php } ?>
              </td>
        <?php
      }
?>
          </tr>

<?php
    }
  }

  public function _get_censored_val($val) {
    $censored_val = '';

    for ($i = 0; $i < strlen($val); $i++) {
      // Reveal first and last 2 chars
      if( $i < 2 || $i >= strlen($val) - 2 ) {
        $censored_val .= $val[$i];
      } else {
        $censored_val .= '*';
      }
    }

    return $censored_val;
  }

  public function _get_input_text($options = array()) {
    // options must be an array
    if (!is_array($options)) {
      throw new Exception('Options parameter passed to the _get_input_text() method needs to be an array!');
    }

    $first_td_class = ! empty( $options['first_td_class'] ) ? $options['first_td_class'] : '';
    $class_name     = (!empty($options['class']) ? esc_attr($options['class']) : '');
    $key            = (!empty($options['key']) ? $options['key'] : '');
    $name           = (!empty($options['name']) ? $options['name'] : '');
    $title          = ! empty( $options['title'] ) ? $options['title'] : '';
    $default        = (!empty($options['default']) ? $options['default'] : '');
    $help           = (!empty($options['help']) ? $options['help'] : '');
    $autocomplete   = ! empty( $options['autocomplete'] ) ? ' autocomplete="' . esc_attr( $options['autocomplete'] ) . '"' : '';

    // Only use fields with secret values obfuscated if FV Player Pro is not there or is ready for it
    if(
      !function_exists('FV_Player_Pro') ||
      ( function_exists('FV_Player_Pro') && version_compare( str_replace( '.beta','',FV_Player_Pro()->version ),'7.5.25.728', '>=') )
    ) {
      $secret         = (!empty($options['secret']) ? $options['secret'] : false);

    } else {
      $secret = false;
    }

    if (!$key || !$name) {
      throw new Exception('Both, "name" and "key" options need to be set for _get_input_text()!');
    }

    $saved_value = esc_attr( $this->_get_option($key) );
    if ( is_array( $key ) && count( $key ) > 1 ) {
      if( $secret ) {
        $secret_key  = $key[0] . '[_is_secret_' . $key[1] . ']'; // add _is_secret_ prefix to key
      }
      $key = $key[0] . '[' . $key[1] . ']';
    }

    // use the default value if the setting is empty
    // however in case of numeric settings you might wish to enter 0 and we need to accept that
    // so we just check if the default if a number and if it is, we allow even 0 value
    $val = is_numeric($default) || !empty($saved_value) ? $saved_value : $default;

    // censor original value
    if( $secret ) {
      $censored_val = $this->_get_censored_val($val);
      $val = '';
      $class_name = 'code ' . $class_name;
    }

    ?>
      <tr>
        <td<?php echo $first_td_class ? ' class="' . esc_attr( $first_td_class ) . '"' : ''; ?>><label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses( $name, $this->help_html ); ?><?php if( $help ) echo ' <a href="#" class="show-info"><span class="dashicons dashicons-info"></span></a>'; ?>:</label></td>
        <td>
          <input class="<?php echo esc_attr( $class_name ); ?>" <?php if($secret && !empty($censored_val)) echo 'style="display: none;"'; ?> id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" <?php if ($title) { echo 'title="' . esc_attr( $title ) . '" '; } ?>type="text" value="<?php echo esc_attr($val); ?>"<?php
            if (isset($options['data']) && is_array($options['data'])) {
              foreach ($options['data'] as $data_item => $data_value) {
                echo ' data-' . esc_attr( $data_item ) . '="' . esc_attr( $data_value ) . '"';
              }
            }

          echo $autocomplete;
          ?> />
          <?php if ( $help ) { ?>
            <p class="description fv-player-admin-tooltip"><span class="info"><?php echo wp_kses( $help, $this->help_html ); ?></span></p>
          <?php } ?>

          <?php if ( $secret ): ?>
            <input name="<?php echo esc_attr($secret_key); ?>" value="<?php echo ! empty( $censored_val ) ? '1' : '0'; ?>" type="hidden" />
            <?php if(!empty($censored_val)): ?>
              <code class="secret-preview"><?php echo esc_html( $censored_val ); ?></code>
              <a href="#" data-is-empty="0" data-setting-change="<?php echo esc_attr($secret_key); ?>" >Change</a>
            <?php endif; ?>
          <?php endif; ?>
        </td>
      </tr>

    <?php
  }


  public function _get_input_hidden($options = array()) {
    // options must be an array
    if (!is_array($options)) {
      throw new Exception('Options parameter passed to the _get_input_hidden() method needs to be an array!');
    }

    $key     = (!empty($options['key']) ? $options['key'] : '');
    $default = (isset($options['default']) ? $options['default'] : '');

    if (!$key) {
      throw new Exception('The "key" option need to be set for _get_input_hidden()!');
    }

    $saved_value = esc_attr( $this->_get_option($key) );
    if ( is_array( $key ) && count( $key ) > 1 ) {
      $key = $key[0] . '[' . $key[1] . ']';
    }
    ?>
      <input id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="hidden"  value="<?php echo (!empty($saved_value) ? $saved_value : $default); ?>"<?php
            if (isset($options['data']) && is_array($options['data'])) {
              foreach ($options['data'] as $data_item => $data_value) {
                echo ' data-' . esc_attr( $data_item ) . '="' . esc_attr( $data_value ) . '"';
              }
            }
            ?> />

    <?php
  }


  public function _get_select() {
    $args_num = func_num_args();

    // new method syntax with all options in the first parameter (which will be an array)
    if ($args_num == 1) {
      $options = func_get_arg(0);

      // options must be an array
      if (!is_array($options)) {
        throw new Exception('Options parameter passed to the _get_select() method needs to be an array!');
      }

      $first_td_class = ! empty( $options['first_td_class'] ) ? $options['first_td_class'] : '';
      $key            = (!empty($options['key']) ? $options['key'] : '');
      $name           = (!empty($options['name']) ? $options['name'] : '');
      $aOptions       = (!empty($options['options']) ? $options['options'] : '');
      $class_name     = ! empty( $options['class'] ) ? $options['class'] : '';
      $help           = (!empty($options['help']) ? $options['help'] : '');
      $more           = (!empty($options['more']) ? $options['more'] : '');
      $default        = (isset($options['default']) ? $options['default'] : '');

      if (!$key || !$name || !$aOptions) {
        throw new Exception('The items "name", "key" and "options" need to be set in options for _get_select()!');
      }
    } else if ($args_num >= 5) {
      // old method syntax with function parameters defined as ($name, $key, $help = false, $more = false)
      $first_td_class = '';
      $name = func_get_arg(0);
      $key = func_get_arg(1);
      $aOptions = func_get_arg(4);
      $help = ($args_num >= 3 ? func_get_arg(2) : false);
      $more = ($args_num >= 4 ? func_get_arg(3) : false);
      $class_name = '';
      $default = '';
    } else {
      throw new Exception('Invalid number of arguments passed to the _get_checkbox() method!');
    }

    // check which option should be selected by default
    $option = $this->_get_option($key);
    foreach( $aOptions AS $k => $v ) {
        if ($k == $option) {
            $selected = $k;
        }
    }

    // if no option is selected, make a default one selected
    if (!isset($selected) && $default) {
        $selected = $default;
    }

    if ( is_array( $key ) && count( $key ) > 1 ) {
      $key = $key[0] . '[' . $key[1] . ']';
    }

    $key = esc_attr($key);
    ?>
      <tr>
        <td<?php echo $first_td_class ? ' class="' . esc_attr( $first_td_class ) . '"' : ''; ?>><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $name ); ?></label></td>
        <td>
          <select <?php echo $class_name ? 'class="' . esc_attr( $class_name ) . '"' : ''; ?>id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php
            if (!isset($options) || !isset($options['data']) || !isset($options['data']['fv-preview'])) { echo ' data-fv-preview=""'; }

            if (isset($options) && isset($options['data']) && is_array($options['data'])) {
              foreach ($options['data'] as $data_item => $data_value) {
                echo ' data-' . esc_attr( $data_item ) . '="' . esc_attr( $data_value ) . '"';
              }
            }
          ?>>
            <?php foreach( $aOptions AS $k => $v ) : ?>
              <option value="<?php echo esc_attr($k); ?>"<?php if( (isset($selected) && strcmp($selected ,$k) == 0 ) || (strcmp($option,$k) == 0) ) echo ' selected="selected"'; ?>><?php echo esc_html( $v ); ?></option>
            <?php endforeach; ?>
          </select>

          <?php if ( $help ) {
            echo wp_kses( $help, $this->help_html );
          } ?>
          <?php if ( $more ) { ?>
            <span class="more"><?php echo wp_kses( $more, $this->help_html ); ?></span> <a href="#" class="show-more">(&hellip;)</a>
          <?php } ?>
        </td>
      </tr>

    <?php
  }


  public function _get_conf() {
    $conf = get_option( 'fvwpflowplayer' );
    if( !is_array($conf) ) {
      $conf = array();
    }

    if( empty($conf) ) { // new install
      // hide some of the notices
      $conf['nag_fv_player_8'] = true;
      $conf['notice_user_video_positions_conversion'] = true;
      $conf['notice_xml_sitemap_iframes'] = true;
      $conf['video_position_save_enable'] = true;
      $conf['js-optimize'] = true;
    }

    if( !isset( $conf['googleanalytics'] ) ) $conf['googleanalytics'] = 'false';
    if( !isset( $conf['chromecast'] ) ) $conf['chromecast'] = 'false';
    if( !isset( $conf['key'] ) ) $conf['key'] = 'false';
    if( !isset( $conf['logo'] ) ) $conf['logo'] = 'false';
    if( !isset( $conf['logo_over_video'] ) ) $conf['logo_over_video'] = 'true';
    if( !isset( $conf['rtmp'] ) ) $conf['rtmp'] = 'false';

    if( !isset( $conf['popupbox'] ) ) $conf['popupbox'] = 'false';
    if( !isset( $conf['allowfullscreen'] ) ) $conf['allowfullscreen'] = 'true';
    if( !isset( $conf['postthumbnail'] ) ) $conf['postthumbnail'] = 'false';

    if( !isset( $conf['overlayTextColor'] ) ) $conf['overlayTextColor'] = '#888';
    if( !isset( $conf['overlayLinksColor'] ) ) $conf['overlayLinksColor'] = '#ff3333';
    if( !isset( $conf['subtitleBgColor'] ) ) $conf['subtitleBgColor'] = 'rgba(0,0,0,0.5)';
    if( !isset( $conf['subtitleSize'] ) ) $conf['subtitleSize'] = 16;

    //unset( $conf['playlistBgColor'], $conf['playlistFontColor'], $conf['playlistSelectedColor']);
    if( !isset( $conf['playlistBgColor'] ) ) $conf['playlistBgColor'] = '#808080';
    if( !isset( $conf['playlistFontColor'] ) ) $conf['playlistFontColor'] = '';
    if( !isset( $conf['playlistSelectedColor'] ) ) $conf['playlistSelectedColor'] = '#bb0000';
    if( !isset( $conf['logoPosition'] ) ) $conf['logoPosition'] = 'bottom-left';

    //

    if( !isset( $conf['parse_commas'] ) ) $conf['parse_commas'] = 'false';
    if( !isset( $conf['width'] ) ) $conf['width'] = '1280';
    if( !isset( $conf['height'] ) ) $conf['height'] = '720';
    if( !isset( $conf['engine'] ) ) $conf['engine'] = 'false';
    if( !isset( $conf['overlay'] ) ) $conf['overlay'] = '';
    if( !isset( $conf['overlay_width'] ) ) $conf['overlay_width'] = '';
    if( !isset( $conf['overlay_height'] ) ) $conf['overlay_height'] = '';
    if( !isset( $conf['overlay_css'] ) ) $conf['overlay_css'] = $this->overlay_css_default;
    if( !isset( $conf['overlay_show_after'] ) ) $conf['overlay_show_after'] = 0;
    if( !isset( $conf['disable_videochecker'] ) ) $conf['disable_videochecker'] = 'false';
    if(  isset( $conf['videochecker'] ) && $conf['videochecker'] == 'off' ) { $conf['disable_videochecker'] = 'true'; unset($conf['videochecker']); }
    if( !isset( $conf['interface'] ) ) $conf['interface'] = array( 'playlist' => false, 'redirect' => false, 'autoplay' => false, 'loop' => false, 'splashend' => false, 'embed' => false, 'subtitles' => false, 'mobile' => false, 'align' => false );
    if( !isset( $conf['interface']['popup'] ) ) $conf['interface']['popup'] = 'true';
    if( !isset( $conf['amazon_bucket'] ) || !is_array($conf['amazon_bucket']) ) $conf['amazon_bucket'] = array('');
    if( !isset( $conf['amazon_key'] ) || !is_array($conf['amazon_key']) ) $conf['amazon_key'] = array('');
    if( !isset( $conf['amazon_secret'] ) || !is_array($conf['amazon_secret']) ) $conf['amazon_secret'] = array('');
    if( !isset( $conf['amazon_region'] ) || !is_array($conf['amazon_region']) ) $conf['amazon_region'] = array('');
    if( !isset( $conf['amazon_expire'] ) ) $conf['amazon_expire'] = '5';
    if( !isset( $conf['amazon_expire_force'] ) ) $conf['amazon_expire_force'] = 'false';
    if( !isset( $conf['js-everywhere'] ) ) $conf['js-everywhere'] = 'false';
    if( !isset( $conf['volume'] ) ) $conf['volume'] = '0.7';
    if( !isset( $conf['playlist_advance'] ) ) $conf['playlist_advance'] = '';
    if( empty( $conf['sharing_email_text'] ) ) $conf['sharing_email_text'] = __( 'Check out the amazing video here', 'fv-player' );


    if( !isset( $conf['liststyle'] ) ) $conf['liststyle'] = 'horizontal';
    if( !isset( $conf['ui_airplay'] ) ) $conf['ui_airplay'] = true;
    if( !isset( $conf['ui_speed_increment'] ) ) $conf['ui_speed_increment'] = 0.25;
    if( !isset( $conf['popups_default'] ) ) $conf['popups_default'] = 'no';
    if( !isset( $conf['email_lists'] ) ) $conf['email_lists'] = array();

    if( !isset( $conf['sticky_video'] ) ) $conf['sticky_video'] = 'off';
    if( !isset( $conf['sticky_place'] ) ) $conf['sticky_place'] = 'right-bottom';
    if( !isset( $conf['sticky_width'] ) ) $conf['sticky_width'] = '380';
    if( !isset( $conf['sticky_width_mobile'] ) ) $conf['sticky_width_mobile'] = '100';

    if( !isset( $conf['playlist-design'] ) ) $conf['playlist-design'] = '2017';

    if (!isset($conf['skin-slim'])) $conf['skin-slim'] = array();
    if (!isset($conf['skin-youtuby'])) $conf['skin-youtuby'] = array();

    if ( ! isset( $conf['youtube_browser_chrome'] ) ) $conf['youtube_browser_chrome'] = 'standard';

    // Avoiding negavite checkboxes, like "Disable Embed Button"
    foreach( array(
      'disableembedding' => 'ui_embed',
      'disablesharing' => 'ui_sharing',
      'disable_video_hash_links' => 'ui_video_links'
    ) as $old_key => $new_key ) {
      if ( ! isset( $conf[ $new_key ] ) ) {
        if ( ! empty( $conf[ $old_key ] ) && 'false' === $conf[ $old_key ] ) {
          $conf[ $new_key ] = 'true';
        } else {
          $conf[ $new_key ] = 'false';
        }
      }
    }

    // apply existing colors from old config values to the new, skin-based config array
    if (!isset($conf['skin-custom'])) {
      $conf['skin-custom'] = $this->aDefaultSkins['skin-custom'];

      // iterate over old keys and bring them in to the new
      $old_skinless_settings_array = array(
        'hasBorder', 'borderColor', 'backgroundColor',
        'font-face', 'progressColor', 'durationColor',
        'design-timeline', 'design-icons'
      );

      foreach ($old_skinless_settings_array as $configKey) {
        if (isset($conf[$configKey])) {
          $conf['skin-custom'][ $configKey ] = $conf[$configKey];
        }
      }

      $conf['skin-slim']['progressColor'] = '#bb0000';
      $conf['skin-youtuby']['progressColor'] = '#bb0000';
    }

    // set to slim, if no skin set
    if (!isset($conf['skin'])) $conf['skin'] = 'slim';

    if ( ! empty( $conf['sticky_video'] ) && 'true' === $conf['sticky_video'] ) {
      $conf['sticky_video'] = 'desktop';
    }

    $conf = apply_filters('fv_player_conf_defaults', $conf);

    update_option( 'fvwpflowplayer', $conf );

    //  hard-coded defaults for the skin preset
    $conf['skin-slim'] = array_merge( $conf['skin-slim'], $this->aDefaultSkins['skin-slim'] );
    $conf['skin-youtuby'] = array_merge( $conf['skin-youtuby'], $this->aDefaultSkins['skin-youtuby'] );

    $conf = apply_filters('fv_player_conf_loaded', $conf);

    $this->conf = $conf;
    return true;
    /// End of addition
  }


  public function _get_option($key) {
    $conf = $this->conf;
    $value = false;
    if( is_array($key) && count($key) === 2) {
      if( isset($conf[$key[0]]) && isset($conf[$key[0]][$key[1]]) ) {
        $value = $conf[$key[0]][$key[1]];
      }
    } elseif( isset($conf[$key]) ) {
      $value = $conf[$key];
    }

    if( is_string($value) ) $value = trim($value);

    if($value === 'false')
        $value = false;
    else if($value === 'true')
        $value = true;

    return $value;
  }


  public function _set_conf( $aNewOptions = false ) {

    if ( ! $aNewOptions & ! empty( $_POST['fv_flowplayer_settings_ajax_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv_flowplayer_settings_ajax_nonce'] ) ), 'fv_flowplayer_settings_ajax_nonce' ) ) {

      $popups_fields = array();
      for( $i = 0; $i < 1000; $i++ ) {
        $popups_fields[] = array(
          'css',
          'html',
        );
      }

      $html_fields = apply_filters(
        'fv_player_settings_html',
        array(
          'overlay',
          'popups' => $popups_fields,
          'pro' => array(
            'download_template',
          )
        )
      );

      $multiline_fields = apply_filters(
        'fv_player_settings_multiline',
        array(
          'customCSS',
          'overlay_css',
          'pro' => array(
            'cf_pk',
          )
        )
      );

      $settings = apply_filters(
        'fv_player_settings',
        array(
          'allowfullscreen',
          'amazon_bucket',
          'amazon_expire',
          'amazon_expire_force',
          'amazon_key',
          'amazon_region',
          'amazon_secret',
          '_is_secret_amazon_secret', // make sure the flag for obfuscated fields is carried over
          'autoplay_preload',
          'bunny_stream',
          'chromecast',
          'closedpostboxesnonce',
          'cloudflare_stream', // FV Player Cloudflare Stream extension
          'coconut', // FV Player Coconut extension
          'coconut_video_variants', // FV Player Coconut extension
          'css_disable',
          'css_writeout',
          'customCSS',
          'digitalocean_spaces',
          'disable_convert_db_save',
          'disable_localstorage',
          'disable_videochecker',
          'email_lists',
          'engine',
          'fv-player-pro-release', // FV Player Pro extension
          'fv_player_admin_pro_quality_alive', // FV Player Pro extension
          'googleanalytics',
          'hd_streaming',
          'height',
          'indie_video_ads', // FV Player Indie Video Ads extension
          'integrations',
          'interface',
          'js-everywhere',
          'js-optimize',
          'jw_player', // FV Player JW Player extension
          'key',
          'lightbox_force',
          'lightbox_images',
          'lightbox_improve_galleries',
          'linode_object_storage',
          'liststyle',
          'logo',
          'logo_over_video',
          'logoPosition',
          'mailchimp_api',
          'mailchimp_label',
          'mailchimp_list',
          'matomo_domain',
          'matomo_site_id',
          'meta-box-order-nonce',
          'mobile_alternative_fullscreen',
          'mobile_force_fullscreen',
          'mobile_native_fullscreen',
          'multiple_playback',
          'overlay',
          'overlayLinksColor',
          'overlayTextColor',
          'overlay_css',
          'overlay_height',
          'overlay_show_after',
          'overlay_width',
          'parse_commas',
          'parse_comments',
          'playlist-design',
          'playlistBgColor',
          'playlistFontColor',
          'playlistFontColor-proxy',
          'playlistSelectedColor',
          'playlist_advance',
          'popupbox',
          'popups',
          'popups_default',
          'postthumbnail',
          'pro', // FV Player Pro extension
          'profile_videos_enable_bio',
          'remove_all_data',
          'rtmp',
          's3_browser',
          'sharing_email_text',
          'show_controlbar',
          'skin',
          'skin-custom',
          'skin-slim',
          'skin-youtuby',
          'splash',
          'sticky_place',
          'sticky_video',
          'sticky_width',
          'sticky_width_mobile',
          'subtitleBgColor',
          'subtitleFontFace',
          'subtitleOn',
          'subtitleSize',
          'ui_airplay',
          'ui_embed',
          'ui_no_picture_button',
          'ui_repeat_button',
          'ui_rewind_button',
          'ui_sharing',
          'ui_speed',
          'ui_speed_increment',
          'ui_video_links',
          'user_playlist', // FV Player Bookmarks extension
          'version',
          'video_position_save_enable',
          'video_sitemap',
          'video_sitemap_meta',
          'video_stats_enable',
          'video_stats_enable_guest',
          'viloud', // FV Player Viloud extension
          'volume',
          'width',
          'wistia_use_fv_player',
          'youtube_browser_chrome',
        )
      );

      $aNewOptions = array();
      foreach( $settings as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
          $aNewOptions[ $key ] = $this->_set_conf_sanitize( $_POST[ $key ], $key, $settings, $html_fields, $multiline_fields );
        }
      }
    }

    $aNewOptions = fv_player_handle_secrets( $aNewOptions, $this->conf);

    $is_ajax = isset($aNewOptions['fv-wp-flowplayer-submit-ajax']);

    if( $is_ajax ) {
      unset($aNewOptions['fv-wp-flowplayer-submit-ajax']);
      unset($aNewOptions['fv_flowplayer_settings_ajax_nonce']);
    } else {
      unset($aNewOptions['fv-wp-flowplayer-submit']);
    }

    $sKey = !$is_ajax && !empty($aNewOptions['key']) ? trim($aNewOptions['key']) : false;

    //  make sure the preset Skin properties are not over-written
    foreach( $this->aDefaultSkins AS $skin => $aSettings ) {
      if ( 'skin-custom' === $skin ) {
        continue;
      }

      foreach( $aSettings AS $k => $v ) {
        unset($aNewOptions[$skin][$k]);
      }
    }

    if(isset($aNewOptions['popups'])){
      unset($aNewOptions['popups']['#fv_popup_dummy_key#']);

      foreach( $aNewOptions['popups'] AS $key => $value ) {
        $aNewOptions['popups'][$key]['css'] = stripslashes($value['css']);
        $aNewOptions['popups'][$key]['html'] = stripslashes($value['html']);
      }

      update_option('fv_player_popups',$aNewOptions['popups']);
      unset($aNewOptions['popups']);
    }

    foreach( $aNewOptions AS $key => $value ) {
      if( is_array($value) ) {
        $aNewOptions[$key] = $value;

        // now that we have skin colors separated in an arrayed sub-values,
        // we also need to check their values for HEX colors
        foreach ($value as $sub_array_key => $sub_array_value) {
          if ( strpos( $sub_array_key, 'Color' ) !== FALSE && strpos($sub_array_value, 'rgba') === FALSE) {
            $aNewOptions[$key][$sub_array_key] = (strpos($sub_array_value, '#') === FALSE ? '#' : '').strtolower($sub_array_value);
          }
        }
      } else if( in_array( $key, array('width', 'height') ) ) {
        $aNewOptions[$key] = trim( preg_replace('/[^0-9%]/', '', $value) );
      } else if( !in_array( $key, array('amazon_region', 'amazon_bucket', 'amazon_key', 'amazon_secret', 'font-face', 'overlay', 'overlay_css', 'subtitleFontFace','sharing_email_text','mailchimp_label','email_lists','playlist-design','subtitleBgColor', 'customCSS') ) ) {
        $aNewOptions[$key] = trim( preg_replace('/[^A-Za-z0-9.:\-_\/,]/', '', $value) );
      } else {
        $aNewOptions[$key] = stripslashes(trim($value));
      }
      if ( strpos( $key, 'Color' ) !== FALSE && strpos($aNewOptions[$key], 'rgba') === FALSE) {
        $aNewOptions[$key] = (strpos($aNewOptions[$key], '#') === FALSE ? '#' : '').strtolower($aNewOptions[$key]);
      }
    }

    if( $sKey ) $aNewOptions['key'] = trim($sKey);

    $aOldOptions = is_array(get_option('fvwpflowplayer')) ? get_option('fvwpflowplayer') : array();

    // add pro options if they are not set
    if( !isset($aOldOptions['pro']) || !is_array($aOldOptions['pro']) ) {
      $aOldOptions['pro'] = array();
    }

    // merge pro options
    if( isset($aNewOptions['pro']) ) $aNewOptions['pro'] = array_merge($aOldOptions['pro'],$aNewOptions['pro']);

    // merge the rest of the options
    $aNewOptions = array_replace_recursive( $aOldOptions, $aNewOptions );

    // Ensure only one of "Load FV Flowplayer JS everywhere" and
    // "Optimize FV Flowplayer JS loading" can be enabled
    // The first one takes priority as it's safer
    if( !empty($aNewOptions['js-everywhere']) && $aNewOptions['js-everywhere'] == 'true' && !empty($aNewOptions['js-optimize']) ) {
      unset($aNewOptions['js-optimize']);
    }

    $aNewOptions = apply_filters( 'fv_flowplayer_settings_save', $aNewOptions, $aOldOptions );

    update_option( 'fvwpflowplayer', $aNewOptions );

    $this->_get_conf();

    $this->css_writeout();

    // We might be saving the settings in front-end too (plugin update hook)
    // so in that case we need to not do this
    if( function_exists('fv_wp_flowplayer_delete_extensions_transients') ) {
      fv_wp_flowplayer_delete_extensions_transients();
    }

    if( empty($aOldOptions['key']) || $aOldOptions['key'] != $sKey ) {
      global $FV_Player_Pro_loader;
      if( isset($FV_Player_Pro_loader) ) {
        $FV_Player_Pro_loader->license_key = $sKey;
      }
    }

    // Was "Remove all data" activated or deactivated?
    if ( ! empty( $_POST['fv_flowplayer_settings_ajax_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv_flowplayer_settings_ajax_nonce'] ) ), 'fv_flowplayer_settings_ajax_nonce' ) ) {

      $put_in_uninstall_php = false;
      $remove_uninstall_php = false;

      // Enabled
      if (
        ( empty( $aOldOptions['remove_all_data'] ) || 'false' === $aOldOptions['remove_all_data'] ) &&
        ! empty( $aNewOptions['remove_all_data'] ) && 'true' === $aNewOptions['remove_all_data']
      ) {
        $put_in_uninstall_php = true;

      // Disabled
      } else if (
        ! empty( $aOldOptions['remove_all_data'] ) && 'true' === $aOldOptions['remove_all_data'] &&
        ( empty( $aNewOptions['remove_all_data'] ) || 'false' === $aNewOptions['remove_all_data'] )
      ) {
        $remove_uninstall_php = true;
      }

      if ( $put_in_uninstall_php || $remove_uninstall_php ) {
        fv_player_setup_uninstall_script( $put_in_uninstall_php, $remove_uninstall_php );
      }
    }

    return true;
  }

  /**
   * Sanitize the value of the option.
   *
   * TODO: Also check if the option is allowed to be saved by looking it up in $settings.
   * All the plugins would have to register their settings arrays with fv_player_settings filter.
   *
   * @param string $value The value of the option.
   * @param string $key The key of the option.
   * @param array $settings The settings array.
   * @param array $html_fields The fields that should be sanitized as HTML.
   * @param array $multiline_fields The fields that should be sanitized as multiline text.
   *
   * @return string|array The sanitized value.
   */
  function _set_conf_sanitize( $value, $key, $settings, $html_fields, $multiline_fields ) {

    if ( is_array( $value ) ) {
      foreach( $value as $nested_key => $nested_value ) {
        $value[ $nested_key ] = $this->_set_conf_sanitize(
          $nested_value,
          $nested_key,
          ! empty( $settings[ $key ] ) ? $settings[ $key ] : array(),
          ! empty( $html_fields[ $key ] ) ? $html_fields[ $key ] : array(),
          ! empty( $multiline_fields[ $key ] ) ? $multiline_fields[ $key ] : array()
        );
      }
      return $value;
    }

    if ( in_array( $key, $html_fields ) ) {
      add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit' ), 999, 2 );
      add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit_settings' ), 999, 2 );
      add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit_scripts' ), 999, 2 );

      $value = wp_kses( wp_unslash( $value ), 'post' );

      remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit' ), 999, 2 );
      remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit_settings' ), 999, 2 );
      remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_permit_scripts' ), 999, 2 );

    } else if ( in_array( $key, $multiline_fields ) ) {
      $value = sanitize_textarea_field( $value );

    } else {
      $value = sanitize_text_field( $value );
    }

    return $value;
  }

  public function _set_option($key, $value) {
    $aOldOptions = get_option( 'fvwpflowplayer', array() );

    if( ! is_array($key) ) {
      $aNewOptions = array_merge($aOldOptions,array($key => $value));
    } else {
      $aNewOptions = $aOldOptions;

      if( !isset($aNewOptions[$key[0]]) || !is_array($aNewOptions[$key[0]]) ) {
        $aNewOptions[$key[0]] = array();
      }

      $aNewOptions[$key[0]][$key[1]] = $value;
    }

    update_option( 'fvwpflowplayer', $aNewOptions );

    $this->_get_conf();
  }

  /**
   * Salt function - returns pseudorandom string hash.
   * @return Pseudorandom string hash.
   */
  public function _salt() {
    $salt = substr(md5(uniqid( wp_rand(), true) ), 0, 10);
    return $salt;
  }

  public function get_video_checker_media($mediaData , $src1 = false, $src2 = false, $rtmp = false) {
    global $FV_Player_Pro;
    $media = $mediaData['sources'];

    static $enabled;
    if( !isset($enabled) ) {
      $enabled = current_user_can('manage_options') && !$this->_get_option('disable_videochecker') && ( $this->_get_option('video_checker_agreement') || $this->_get_option('key_automatic') );
    }

    if( $enabled && $this->ajax_count < 100 ) {
      $this->ajax_count++;

      if( stripos($rtmp,'rtmp://') === false && $rtmp ) {
        list( $rtmp_server, $rtmp ) = $this->get_rtmp_server($rtmp);
        $rtmp = trailingslashit($rtmp_server).$rtmp;
      }

      $aTest_media = array();
      foreach( $media as $h => $v ) {
        if( $v ) {
          // allow checker skip using filter
          if( apply_filters( 'fv_player_video_checker_skip', false, $v['src'] ) ) {
            continue;
          }

          $temp_media = $this->get_video_src( $v['src'], array( 'dynamic' => true ) );

          if( isset($FV_Player_Pro) && $FV_Player_Pro ) {
            if (
              method_exists( $FV_Player_Pro, 'is_vimeo') && $FV_Player_Pro->is_vimeo($temp_media) ||
              method_exists( $FV_Player_Pro, 'is_vimeo_event' ) && $FV_Player_Pro->is_vimeo_event($temp_media)
            ) {
              continue;
            }
          }

          if( strcmp( $v['type'], 'video/youtube') === 0 ) {
            continue;
          }

          $aTest_media[] = $temp_media;
        }
      }

      if( !empty($this->aCurArgs['mobile']) ) {
        $aTest_media[] = $this->get_video_src($this->aCurArgs['mobile'], array( 'dynamic' => true ) );
      }

      if( isset($aTest_media) && count($aTest_media) > 0 ) {
        $mediaData['video_checker'] = $aTest_media;
        return $mediaData;
      }
    }

    return $mediaData;
  }


  public function add_fake_extension( $media ) {
    if( stripos( $media, '(format=m3u8' ) !== false ) { //  http://*.streaming.mediaservices.windows.net/*.ism/manifest(format=m3u8-aapl)
      $media .= '#.m3u8'; //  if this is not added then the Flowpalyer Flash HLS won't play the HLS stream!
    }
    return $media;
  }


  private function build_playlist_html( $aArgs, $sSplashImage, $sItemCaption, $aPlayer, $index ) {
    $aPlayer = apply_filters( 'fv_player_item', $aPlayer, $index, $aArgs );

    $aItem = isset($aPlayer['sources']) && isset($aPlayer['sources'][0]) ? $aPlayer['sources'][0] :  false;
    $sListStyle = !empty($aArgs['liststyle']) ? $aArgs['liststyle'] : false;

    $sItemCaption = flowplayer::filter_possible_html($sItemCaption);

    if ($this->current_video()) {
      if ( $this->current_video()->getTitleHide() && $this->current_video()->getToggleAdvancedSettings() ) {
        $sItemCaption = false;
      }
    }

    if( !$sItemCaption && $sListStyle == 'text' ) $sItemCaption = 'Video '.($index+1);

    $sItemCaptionOriginal = $sItemCaption;

    if( !empty($aArgs['members_only']) ) {
      $sHTML = "\t\t<a href='".esc_attr($aArgs['members_only'])."' data-fancybox>";
    } else {
      $arg = !empty($this->aCurArgs['lazy']) ? 'data-item-lazy' : 'data-item';
      $sHTML = "\t\t<a href='#' ".$arg."='".$this->json_encode($aPlayer)."'>";
    }

    $tDuration = false;
    if( isset($aPlayer['fv_start']) && isset($aPlayer['fv_end']) ) { // change duration if using custom startend
      $tDuration = $aPlayer['fv_end'] - $aPlayer['fv_start'];
    } else {
      if ($this->current_video()) {
        $tDuration = $this->current_video()->getDuration();
      }

      if( !empty($aArgs['durations']) ) {
        $aDurations = explode( ';', $aArgs['durations'] );
        if( !empty($aDurations[$index]) ) {
          $tDuration = $aDurations[$index];
        }
      }

      global $post;
      if( !$tDuration && $post && isset($post->ID) && !empty($aItem['src']) ) {
        $tDuration = flowplayer::get_duration( $post->ID, $aItem['src'], true );
      }

      if( isset($aPlayer['fv_start']) && !empty($tDuration) ) { // custom start only
        $tDuration = flowplayer::hms_to_seconds( $tDuration ) -  $aPlayer['fv_start'];
      }

      if( isset($aPlayer['fv_end']) ) { // custom end only
        $tDuration = $aPlayer['fv_end'];
      }

    }

    if( $sListStyle != 'text' ) {
      $current_video_splash_id = false;

      $current_video = $this->current_video();
      if ( $current_video ) {
        $current_video_splash_id = $current_video->getSplashAttachmentId();
      }

      if ( $current_video_splash_id || $sSplashImage ) {
        $sHTML .= "<div class='fvp-playlist-thumb-img'>";

        $image_html = false;

        // Load from WordPress Media Library
        if ( $current_video_splash_id ) {
          $image_html = wp_get_attachment_image( $current_video_splash_id, 'medium', false, array( 'fv_sizes' => '25vw, 50vw, 100vw', 'loading' => 'lazy' ) );
        }

        // Fall back to URL is the image is not in the Media Library
        if( ! $image_html && $sSplashImage ) {
          if( !(  defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) && function_exists( 'get_rocket_option' ) && get_rocket_option( 'lazyload' ) && apply_filters( 'do_rocket_lazyload', true ) ) {
            $image_html = "<img src='data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=' data-lazy-src='" . esc_attr( $sSplashImage ) . "' />";

          } else {
            $image_html = "<img ". ( get_query_var('fv_player_embed') || get_query_var('fv_player_cms_id') ? "data-no-lazy='1'" : "" ) . " src='" . esc_attr( $sSplashImage ) . "' loading='lazy' />";
          }

        }

        $sHTML .= $image_html;

      } else {
        $sHTML .= "<div class='fvp-playlist-thumb-img no-image'>";
      }

      if( !empty($tDuration) && intval($tDuration) && ( !empty($this->aCurArgs['saveposition']) || $this->_get_option('video_position_save_enable') ) && is_user_logged_in() ) {
        $tDuration = flowplayer::hms_to_seconds( $tDuration );
        $tPosition = !empty($aItem['position']) ? $aItem['position'] : 0;
        if( $tPosition > 0 && !empty($aPlayer['fv_start']) ) {
          $tPosition -= $aPlayer['fv_start'];
          if( $tPosition < 0 ) {
            $tPosition = 0;
          }
        }

        if( !empty($aItem['saw']) ) {
          $tPosition = $tDuration;
        }

        $sHTML .= '<span class="fvp-progress-wrap"><span class="fvp-progress" style="width: '.( 100 * $tPosition / $tDuration ).'%" data-duration="'.esc_attr($tDuration).'"></
        span></span>';
      } else if( !empty($aItem['saw']) ) {
        $sHTML .= '<span class="fvp-progress-wrap"><span class="fvp-progress" style="width: 100%"></span></span>';
      }
      $sHTML .= "</div>"; // close .fvp-playlist-thumb-img
    }

    if( $sListStyle == 'season' ) {
      $sHTML .= "<div class='fvp-playlist-item-info'>";
      if( $sItemCaption ) {
        $sHTML .= "<h4>".$sItemCaption."</h4>";
      }
      if ($this->current_video()) {
        $sSynopsis = $this->current_video()->getMetaValue('synopsis',true);
        if( $sSynopsis ) {
          $sHTML .= wpautop($sSynopsis);
        }
      }

      if( !empty($aArgs['synopsis']) ) {
        // preserver semicolons
        $synopsis_items = str_replace( '\;', '{fv-player-semicolon}', $aArgs['synopsis'] );

        $synopsis_items = explode( ';', $synopsis_items );
        if( !empty($synopsis_items[$index]) ) {
          // put back semicolons
          $sHTML .= wpautop( str_replace( '{fv-player-semicolon}', ';', $synopsis_items[$index] ) );
        }
      }

      if( $tDuration ) {
        $sHTML .= '<i class="dur">('.ceil( flowplayer::hms_to_seconds($tDuration)/60).'m)</i>';
      }

      $sHTML .= "</div>";

    } else {
      if( $sItemCaption ) $sItemCaption = "<span>".$sItemCaption."</span>";

      if( $tDuration ) {
        $sDuration = '<i class="dur">'.flowplayer::format_hms($tDuration).'</i>';
        if( !empty($this->aCurArgs['listdesign']) && $this->aCurArgs['listdesign'] == '2014' || empty($this->aCurArgs['listdesign']) && $this->_get_option('playlist-design') == 2014 ) {
          $sHTML .= $sDuration;
        } else {
          $sItemCaption .= $sDuration;
        }
      }

      if( $sItemCaption || $this->is_audio_playlist() ) {
        $sHTML .= "<h4>".$sItemCaption."</h4>";
      }

    }

    $sHTML .= "</a>\n";

    $sHTML = apply_filters( 'fv_player_item_html', $sHTML, $aArgs, $sSplashImage, $sItemCaptionOriginal, $aPlayer, $index, $tDuration );

    return $sHTML;
  }

  //  todo: this could be parsing rtmp://host/path/mp4:rtmp_path links as well
  function build_playlist( $aArgs, $media, $src1, $src2, $rtmp, $splash_img, $suppress_filters = false ) {

      $sShortcode = isset($aArgs['playlist']) ? $aArgs['playlist'] : false;
      $sCaption = isset($aArgs['caption']) ? $aArgs['caption'] : false;
      if( !$sCaption && isset($aArgs['title']) ) {
        $sCaption = $aArgs['title'];
      }

      $replace_from = array('&amp;','\;', '\,');
      $replace_to = array('<!--amp-->','<!--semicolon-->','<!--comma-->');
      $sShortcode = str_replace( $replace_from, $replace_to, $sShortcode );
      $sItems = explode( ';', $sShortcode );

      if( $sCaption ) {
        $replace_from = array('&amp;quot;','&amp;','\;','&quot;');
        $replace_to = array('"','<!--amp-->','<!--semicolon-->','"');
        $sCaption = str_replace( $replace_from, $replace_to, $sCaption );
        $aCaption = explode( ';', $sCaption );
      }
      if( isset($aCaption) && count($aCaption) > 0 ) {
        foreach( $aCaption AS $key => $item ) {
          $aCaption[$key] = str_replace('<!--amp-->','&',$item);
          $aCaption[$key] = str_replace('<!--semicolon-->',';',$item);
        }
      }

      $aDurations = !empty($aArgs['durations']) ? explode( ';', $aArgs['durations'] ) : array();

      $aItem = array();

      if( $rtmp && stripos($rtmp,'rtmp://') === false ) {
        $rtmp = 'rtmp:'.$rtmp;
      }

      if( isset($aArgs['toggle_advanced_settings']) && !$aArgs['toggle_advanced_settings'] ) { // disable alternative sources if advanced settings are hidden
        $src1 = $src2 = $rtmp = false;
      }

      foreach( apply_filters( 'fv_player_media', array($media, $src1, $src2, $rtmp), $this ) AS $key => $media_item ) {
        if( !$media_item ) continue;

        if( stripos($media_item,'rtmp:') === 0 && stripos($media_item,'rtmp://') === false ) {
          $media_item_tmp = preg_replace( '~^rtmp:~', '', $media_item );
        } else {
          $media_item_tmp = $media_item;
        }

        $media_url = $this->get_video_src( $media_item_tmp, array( 'suppress_filters' => $suppress_filters ) );

        //  add domain for relative video URLs if it's not RTMP
        if( stripos($media_item,'rtmp://') === false && $key != 3 ) {
          $media_url = $this->get_video_url($media_url);
        }

        if( stripos( $media_item, 'rtmp:' ) === 0 ) {
          if( !preg_match( '~^[a-z0-9]+:~', $media_url ) ) { //  no RTMP extension provided
            $ext = $this->get_mime_type($media_url,false,true) ? $this->get_mime_type($media_url,false,true).':' : false;
            $aItem[] = array( 'src' => $ext.str_replace( '+', ' ', $media_url ), 'type' => 'video/flash' );
          } else {
            $aItem[] = array( 'src' => str_replace( '+', ' ', $media_url ), 'type' => 'video/flash' );
          }
        } else {
          $aItem[] = array( 'src' => $media_url, 'type' => $this->get_mime_type($media_url) );
        }
      }

      $aItem = $this->process_preferred_video_type( $aItem );

      $sItemCaption = ( isset($aCaption) ) ? array_shift($aCaption) : false;

      list( $rtmp_server, $rtmp ) = $this->get_rtmp_server($rtmp);

      if( !empty($aArgs['mobile']) ) {
        $mobile = $this->get_video_src( $this->get_video_url($aArgs['mobile']) );
        $aItem[] = array( 'src' => $mobile, 'type' => $this->get_mime_type($mobile), 'mobile' => true );
      }

      if( isset($aArgs['toggle_advanced_settings']) && !$aArgs['toggle_advanced_settings'] ) {
        $mobile = false;
      }

      $aPlayer = array( 'sources' => $aItem );
      if( $rtmp_server ) $aPlayer['rtmp'] = array( 'url' => $rtmp_server );

      $aPlayer = apply_filters( 'fv_player_item_pre', $aPlayer, 0, $aArgs );

      if ($this->current_video()) {
        if( !$splash_img ) $splash_img = $this->current_video()->getSplash();
        if( !$sItemCaption ) $sItemCaption = $this->current_video()->getTitle();

        $remove_black_bars = $this->current_video()->getMetaValue( 'remove_black_bars', true );
        if ( $remove_black_bars && $aArgs['toggle_advanced_settings'] ) {
          $aPlayer['remove_black_bars'] = true;
        }
      }

      $splash_img = apply_filters( 'fv_flowplayer_playlist_splash', $splash_img, !empty($aPlayer['sources'][0]['src']) ? $aPlayer['sources'][0]['src'] : false );
      $sItemCaption = apply_filters( 'fv_flowplayer_caption', $sItemCaption, $aItem, $aArgs );

      if( $sItemCaption ) {
        $aPlayer['fv_title'] = $sItemCaption;
        if( $this->_get_option('matomo_domain') && $this->_get_option('matomo_site_id') ) {
          $aPlayer['matomoTitle'] = $sItemCaption;
        }
      }

      if( $splash_img ) {
        $aPlayer['splash'] = $splash_img;
      }

      if( empty($aPlayer['duration']) && !empty($aDurations[0]) ) {
        $aPlayer['duration'] = $aDurations[0];
      }

      $aPlaylistItems[] = $aPlayer;
      $aSplashScreens[] = $splash_img;
      $aCaptions[] = $sItemCaption;


      $sHTML = array();

      if( isset($aArgs['liststyle']) && !empty($aArgs['liststyle'])   ){

        $sHTML[] = $this->build_playlist_html( $aArgs, $splash_img, $sItemCaption, $aPlayer, 0 );
      }else{
        $sHTML[] = "<a href='#' class='is-active'><span ".( (isset($splash_img) && !empty($splash_img)) ? "style='background-image: url(\"".$splash_img."\")' " : "" )."></span>$sItemCaption</a>\n";
      }

      if( count($sItems) > 0 ) {
        foreach( $sItems AS $iKey => $sItem ) {

          if( !$sItem ) continue;

          $index = $iKey + 1;

          $aPlaylist_item = explode( ',', $sItem );

          foreach( $aPlaylist_item AS $key => $item ) {
            if( $key > 0 && ( stripos($item,'http:') !== 0 && stripos($item,'https:') !== 0 && stripos($item,'rtmp:') !== 0 && stripos($item,'/') !== 0 ) ) {
              $aPlaylist_item[$key-1] .= ','.$item;
              $aPlaylist_item[$key] = $aPlaylist_item[$key-1];
              unset($aPlaylist_item[$key-1]);
            }
            $aPlaylist_item[$key] = str_replace( $replace_to, $replace_from, $aPlaylist_item[$key] );
          }

          $aItem = array();
          $sSplashImage = false;

          foreach( apply_filters( 'fv_player_media', $aPlaylist_item, $this ) AS $aPlaylist_item_i ) {

            // check known image extensions
            // also accept i.vimeocdn.com which doesn't use image extensions
            if( preg_match('~\.(png|gif|jpg|jpe|jpeg)($|\?)~',$aPlaylist_item_i) || stripos($aPlaylist_item_i, 'i.vimeocdn.com') !== false ) {
              $sSplashImage = $aPlaylist_item_i;
              continue;
            }

            $media_url = $this->get_video_src( preg_replace( '~^rtmp:~', '', $aPlaylist_item_i ), array( 'suppress_filters' => $suppress_filters ) );

            if( stripos( $aPlaylist_item_i, 'rtmp:' ) === 0 ) {
              if( !preg_match( '~^[a-z0-9]+:~', $media_url ) ) { //  no RTMP extension provided
                $ext = $this->get_mime_type($media_url,false,true) ? $this->get_mime_type($media_url,false,true).':' : false;
                $aItem[] = array( 'src' => $ext.str_replace( '+', ' ', $media_url ), 'type' => 'video/flash' );
              } else {
                $aItem[] = array( 'src' => str_replace( '+', ' ', $media_url ), 'type' => 'video/flash' );
              }
            } else {
              $aItem[] = array( 'src' => $media_url, 'type' => $this->get_mime_type($media_url) );
            }

          }

          $aPlayer = array( 'sources' => $aItem );
          if( $rtmp_server ) $aPlayer['rtmp'] = array( 'url' => $rtmp_server );

          $sItemCaption = ( isset($aCaption[$iKey]) ) ? $aCaption[$iKey] : false;

          $aPlayer = apply_filters( 'fv_player_item_pre', $aPlayer, $index, $aArgs );

          $aPlayer['sources'] = $this->process_preferred_video_type( $aPlayer['sources'] );

          if ($this->current_video()) {
            if( !$sSplashImage ) $sSplashImage = $this->current_video()->getSplash();
            if( !$sItemCaption ) $sItemCaption = $this->current_video()->getTitle();

            $remove_black_bars = $this->current_video()->getMetaValue( 'remove_black_bars', true );
            if ( $remove_black_bars && $aArgs['toggle_advanced_settings'] ) {
              $aPlayer['remove_black_bars'] = true;
            }
          }

          if( !$sSplashImage && $this->_get_option('splash') ) {
            $sSplashImage = $this->_get_option('splash');
          }

          $sSplashImage = apply_filters( 'fv_flowplayer_playlist_splash', $sSplashImage, !empty($aPlayer['sources'][0]['src']) ? $aPlayer['sources'][0]['src'] : false );
          $sItemCaption = apply_filters( 'fv_flowplayer_caption', $sItemCaption, $aItem, $aArgs );

          if( $sItemCaption ) {
            $aPlayer['fv_title'] = $sItemCaption;
            if( $this->_get_option('matomo_domain') && $this->_get_option('matomo_site_id') ) {
              $aPlayer['matomoTitle'] = $sItemCaption;
            }
          }

          if( $sSplashImage ) {
            $aPlayer['splash'] = $sSplashImage;
          }

          if( empty($aPlayer['duration']) && !empty($aDurations[$index]) ) {
            $aPlayer['duration'] = $aDurations[$index];
          }

          $aPlaylistItems[] = $aPlayer;

          $sHTML[] = $this->build_playlist_html( $aArgs, $sSplashImage, $sItemCaption, $aPlayer, $index );
          if( $sSplashImage ) {
            $aSplashScreens[] = $sSplashImage;
          }
          $aCaptions[] = $sItemCaption;
        }
      }

      if(isset($this->aCurArgs['liststyle']) && $this->aCurArgs['liststyle'] != 'tabs'){
        $aPlaylistItems = apply_filters('fv_flowplayer_playlist_items',$aPlaylistItems,$this);
      }

      $sHTML = apply_filters( 'fv_flowplayer_playlist_item_html', $sHTML );

      $attributes = array();
      $attributes_html = '';
      $attributes['class'] = 'fp-playlist-external '.$this->get_playlist_class($aCaptions);
      $attributes['rel'] = 'wpfp_'.$this->hash;
      $attributes['id'] = 'wpfp_'.$this->hash.'_playlist';

      // we put in enough to be sure it will fit in, later JS calculates a better value
      if( isset($this->aCurArgs['liststyle']) && $this->aCurArgs['liststyle'] == 'slider' ) {
        $slider_width = count( $aPlaylistItems ) * 200;
        $attributes['style'] = "width: " . absint( $slider_width ) . "px; max-width: " . absint( $slider_width ) . "px !important";
      }

      $attributes = apply_filters( 'fv_player_playlist_attributes', $attributes, $media, $this );
      foreach( $attributes AS $attr_key => $attr_value ) {
        $attributes_html .= ' '.$attr_key.'="'.esc_attr( $attr_value ).'"';
      }

      $items = implode( '', $sHTML );

      if( isset($aArgs['liststyle']) ){
        $limit = 150;
        if( isset($aArgs['liststyle']) && in_array( $this->aCurArgs['liststyle'], array( 'version-one', 'version-two' ) ) ) {
          $limit = 200;
        }

        if( in_array( $this->aCurArgs['liststyle'], array( 'version-one', 'version-two' ) ) ) {
          $items = "<div class='fv-playlist-draggable'>".$items."</div>";
          $items .= "<div class='fv-playlist-slider-controls'>
          <button class='fv-playlist-left-arrow'></button>
          <button class='fv-playlist-right-arrow'></button>
          </div>\n";
        }
      }

      $sHTML = "\t<div$attributes_html>\n" . $items . "\t</div>\n";

      /**
       * Pure-JavaScript version of freedomplayer_playlist_size_check() to avoid layout shift as the JavaScript loads.
       *
       * We don't need it if playlists is in lightbox = using slider playlist style.
       */
      if ( ! empty( $this->aCurArgs['liststyle'] ) && ! in_array( $this->aCurArgs['liststyle'], array( 'slider' ) ) ) {
        $script_fit_thumbs = '';
        if( isset($aArgs['liststyle']) && in_array( $this->aCurArgs['liststyle'], array( 'polaroid', 'version-one', 'version-two' ) ) ) {
          $script_fit_thumbs = "
          var w = getComputedStyle( el ).width;
          while ( w === 'auto' && el.parentNode ) {
            el = el.parentNode;
            w = getComputedStyle(el).width;
          }
          var f = Math.floor( parseInt( 'auto' === w ? 0 : w  ) / " . absint( $limit ) . " );
          if( f > 8 ) f = 8;
          else if( f < 2 ) f = 2;
          el.style.setProperty('--fp-playlist-items-per-row', String(f));
          ";
        }

        $script = "( function() {
          var el = document.getElementById( '" . $attributes['id'] ."' );
          if ( el.parentNode.getBoundingClientRect().width >= 900 ) {
            el.classList.add( 'is-wide' );
          } " . $script_fit_thumbs . "
        } )();";

        // remove whitespace
        $script = preg_replace( '~\s+~m', ' ', $script );

        $sHTML .= "\t<script>" . $script . "</script>\n";
      }

      if( isset($aArgs['liststyle']) && $this->aCurArgs['liststyle'] == 'slider' ) {
        $sHTML = "<div class='fv-playlist-slider-wrapper'>".$sHTML."</div>\n";
      }

      return array( $sHTML, $aPlaylistItems, $aSplashScreens, $aCaptions );
  }

  public function check_license( $force ) {
    parent::setLicenseTransient( $force );
  }

  function css_generate( $skip_style_tag = true ) {
    $this->_get_conf(); //  todo: without this the colors for skin-slim might end up empty, why?

    $sSubtitleBgColor = $this->_get_option('subtitleBgColor');
    if( $sSubtitleBgColor[0] == '#' && $this->_get_option('subtitleBgAlpha') ) {
      $sSubtitleBgColor = 'rgba('.hexdec(substr($sSubtitleBgColor,1,2)).','.hexdec(substr($sSubtitleBgColor,3,2)).','.hexdec(substr($sSubtitleBgColor,5,2)).','.$this->_get_option('subtitleBgAlpha').')';
    }

    if( !$skip_style_tag ) : ?>
      <style type="text/css">
    <?php endif;

    $css = '';

    //  generate CSS for all the available skin settings
    foreach( array('skin-slim','skin-youtuby','skin-custom') AS $skin ) {
      $sel = '.flowplayer.'.$skin;

      $sBackground = $this->_get_option( array($skin, 'backgroundColor') );
      $sDuration = $this->_get_option( array($skin, 'durationColor') );
      $sProgress = $this->_get_option(array($skin, 'progressColor'));
      $sTimeline = $this->_get_option( array($skin, 'timelineColor') );
      $sAccent = $this->_get_option( array($skin, 'accent') );

      if( $this->_get_option(array($skin, 'hasBorder')) ) {
        $css .= $sel." { border: 1px solid ".$this->_get_option(array($skin, 'borderColor'))."; }\n";
      }

      $css .= $sel." .fp-color, ".$sel." .fp-selected, .fp-playlist-external.".$skin." .fvp-progress, .fp-color { background-color: ".$this->_get_option(array($skin, 'progressColor'))." !important; }\n";
      $css .= $sel." .fp-color-fill .svg-color, ".$sel." .fp-color-fill svg.fvp-icon, ".$sel." .fp-color-fill { fill: ".$this->_get_option(array($skin, 'progressColor'))." !important; color: ".$this->_get_option(array($skin, 'progressColor'))." !important; }\n";
      $css .= $sel." .fp-controls, .fv-player-buttons a:active, .fv-player-buttons a { background-color: ".$sBackground." !important; }\n";
      if( $sDuration ) {
        $css .= $sel." a.fp-play, ".$sel." a.fp-volumebtn, ".$sel." .fp-controls, ".$sel." .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { color: ".$sDuration." }\n";
        $css .= $sel." .fp-controls .fv-fp-prevbtn:before, ".$sel." .fp-controls .fv-fp-nextbtn:before { border-color: ".$sDuration." !important; }\n";
        $css .= $sel." .fvfp_admin_error, ".$sel." .fvfp_admin_error a, #content ".$sel." .fvfp_admin_error a { color: ".$sDuration."; }\n";
        $css .= $sel." svg.fvp-icon { fill: ".$sDuration." !important; }\n";

        $css .= $sel." .fp-elapsed, ".$sel." .fp-duration { color: ".$sDuration." !important; }\n";
        $css .= $sel." .fv-player-video-checker { color: ".$sDuration." !important; }\n";
        $css .= $sel." .fp-controls svg { fill: ".$sDuration."; stroke: ".$sDuration." }\n";
      }

      if( $sTimeline ) {
        $css .= $sel." .fp-timeline { background-color: ".$sTimeline." !important; }\n";
        $css .= $sel. " .fp-bar span.chapter_unbuffered{ background-color: ".$sTimeline." !important; }\n";
      }

      if( $sBackground != 'transparent' ) {
        $css .= $sel." .fv-ab-loop { background-color: ".$sBackground." !important; }\n";
        $css .= $sel." .fv_player_popup, .fvfp_admin_error_content {  background: ".$sBackground."; }\n";
      }

      if( $sAccent ) {
        $css .= $sel." .fv-ab-loop .noUi-connect { background-color: ".$sAccent." !important; }\n";
      }

      $css .= $sel. " .fp-bar span.chapter_passed{ background-color: ".$sProgress." !important; }\n";
      $css .= ".fv-player-buttons a.current { background-color: ".$sProgress." !important; }\n";
      $css .= "#content ".$sel.", ".$sel." { font-family: ".$this->_get_option(array($skin, 'font-face'))."; }\n";
      $css .= $sel." .fp-dropdown li.active { background-color: ".$sProgress." !important }\n";
    }

    echo esc_html( $css );

    //  rest is not depending of the skin settings or can use the default skin
    $skin = 'skin-'.$this->_get_option('skin');

    ?>

    .wpfp_custom_background { display: none; position: absolute; background-position: center center; background-repeat: no-repeat; background-size: contain; width: 100%; height: 100%; z-index: 1 }
    .wpfp_custom_popup { position: absolute; top: 10%; z-index: 20; text-align: center; width: 100%; color: #fff; }
    .wpfp_custom_popup h1, .wpfp_custom_popup h2, .wpfp_custom_popup h3, .wpfp_custom_popup h4 { color: #fff; }
    .is-finished .wpfp_custom_background { display: block; }

    <?php echo esc_html( $this->_get_option('overlay_css') ); ?>
    .wpfp_custom_ad { color: <?php echo esc_html( $this->_get_option('overlayTextColor') ); ?>; z-index: 20 !important; }
    .wpfp_custom_ad a { color: <?php echo esc_html( $this->_get_option('overlayLinksColor') ); ?> }

    .fp-playlist-external > a > span { background-color:<?php echo esc_html( $this->_get_option('playlistBgColor') ); ?>; }
    <?php if ( $this->_get_option('playlistFontColor') && $this->_get_option('playlistFontColor') !=='#') : ?>
      .fp-playlist-external a h4,
      .fp-playlist-external a:hover h4,
      .fp-playlist-external a.is-active:hover h4,
      .visible-captions.fp-playlist-external a h4 span,
      .fv-playlist-design-2014.fp-playlist-external a h4,
      .fv-playlist-design-2014.fp-playlist-external a:hover h4 { color:<?php echo esc_html( $this->_get_option('playlistFontColor') ); ?>; }
    <?php endif; ?>
    .fp-playlist-external > a.is-active > span { border-color:<?php echo esc_html( $this->_get_option('playlistSelectedColor') ); ?>; }
    .fp-playlist-external.fv-playlist-design-2014 a.is-active,
    .fp-playlist-external.fv-playlist-design-2014 a.is-active h4,
    .fp-playlist-external.fv-playlist-design-2014 a.is-active:hover h4,
    .fp-playlist-external.fp-playlist-only-captions a.is-active,
    .fp-playlist-external.fp-playlist-only-captions a.is-active h4,
    .fp-playlist-external.fp-playlist-only-captions a.is-active:hover h4 { color:<?php echo esc_html( $this->_get_option('playlistSelectedColor') ); ?>; }
    <?php if ( $this->_get_option('playlistBgColor') !=='#') : ?>.fp-playlist-vertical { background-color:<?php echo esc_html( $this->_get_option('playlistBgColor') ); ?>; }<?php endif; ?>

    <?php if( $this->_get_option('subtitleSize') ) : ?>.flowplayer .fp-player .fp-captions p { font-size: <?php echo intval($this->_get_option('subtitleSize')); ?>px; }<?php endif; ?>
    <?php if( $this->_get_option('subtitleFontFace') ) : ?>.flowplayer .fp-player .fp-captions p { font-family: <?php echo esc_html( $this->_get_option('subtitleFontFace') ); ?>; }<?php endif; ?>
    <?php if( $this->_get_option('logoPosition') ) :
      $value = $this->_get_option('logoPosition');
      $sCSS = ! empty( $this->css_logo_positions[ $value ] ) ? $this->css_logo_positions[ $value ] : '';
      ?>.flowplayer > .fp-player > .fp-logo > img { <?php echo esc_html( $sCSS ); ?> }<?php endif; ?>

    .flowplayer .fp-player .fp-captions p { background-color: <?php echo esc_html( $sSubtitleBgColor ); ?> }

    .flowplayer .fp-player.is-sticky { max-width: <?php echo intval( $this->_get_option('sticky_width') ); ?>px }
    @media screen and ( max-width: 480px ) {
      .flowplayer .fp-player.is-sticky { max-width: <?php echo intval( $this->_get_option('sticky_width_mobile') ); ?>% }
    }

    <?php echo apply_filters('fv_player_custom_css',''); ?>
    <?php if( !$skip_style_tag ) : ?>
      </style>
    <?php endif;
  }


  function css_enqueue( $force = false ) {

    if(
      is_admin() && // do not load in wp-admin
      !did_action('admin_footer') && // if the footer was not yet shown
      !did_action('elementor/editor/wp_head') && // and if Elementor head was not yet loaded
      ( !isset($_GET['page']) || sanitize_key( $_GET['page'] ) != 'fvplayer' ) && // and unless it's the FV Player player
      !empty($_GET['legacy-widget-preview[idBase]']) // and unless it's the legacy widget preview of Gutenberg-powered WordPress 5.8 widgets
    ) {
      return;
    }

    /*
     *  Let's check if FV Player is going to be used before loading CSS!
     */
    global $posts, $post;
    if( !$posts || empty($posts) ) $posts = array( $post );

    if( !$force && !$this->should_force_load_js() && isset($posts) && count($posts) > 0 ) {
      $bFound = false;


      foreach( $posts AS $objPost ) {
        if( !empty($objPost->post_content) && (
            stripos($objPost->post_content,'[fvplayer') !== false ||
            stripos($objPost->post_content,'[flowplayer') !== false ||
            stripos($objPost->post_content,'[video') !== false
          )
        ) {
          $bFound = true;
          break;
        }

        if ( is_singular() ) {
          $post_meta = get_post_custom( $objPost->ID );
          if ( is_array( $post_meta ) ) {
            foreach ( $post_meta as $meta_values ) {
              foreach ( $meta_values as $meta_value ) {
                if ( stripos( $meta_value,'[fvplayer') !== false ) {
                  $bFound = true;
                  break 2;
                }
              }
            }
          }
        }
      }

      if( !$bFound ) {
        return;
      }
    }

    $this->bCSSLoaded = true;

    global $fv_wp_flowplayer_ver;
    $this->bCSSInline = true;
    $sURL = FV_FP_RELATIVE_PATH.'/css/fv-player.min.css';
    $sVer = $fv_wp_flowplayer_ver;
    if( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
      $sVer = filemtime( dirname(__FILE__).'/../css/skin.css' );
      $sVerAdditions = filemtime( dirname(__FILE__).'/../css/fv-player-additions.css' );

      $sURL = FV_FP_RELATIVE_PATH.'/css/skin.css';
      $sURLAdditions = FV_FP_RELATIVE_PATH.'/css/fv-player-additions.css';
    }

    if( !( $this->_get_option('css_disable') || defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) && $this->_get_option($this->css_option()) ) {
      if( @file_exists($this->css_path()) ) {
        $sURL = $this->css_path('url');
        $sVer = $this->_get_option($this->css_option());
        $this->bCSSInline = false;
        $sURLAdditions = null;
      }
    }

    if( is_admin() && did_action('admin_footer') ) {
      echo "<link rel='stylesheet' id='fv_freedomplayer-css'  href='".esc_attr($sURL)."?ver=".$sVer."' type='text/css' media='all' />\n";
      if(isset($sURLAdditions)) {
        echo "<link rel='stylesheet' id='fv_freedomplayer-css-additions'  href='".esc_attr($sURLAdditions)."?ver=".$sVerAdditions."' type='text/css' media='all' />\n";
      }

      echo "<link rel='stylesheet' id='fv_freedomplayer_playlists-css'  href='".esc_attr( FV_FP_RELATIVE_PATH.'/css/playlists.css' )."?ver=".filemtime( dirname(__FILE__).'/../css/playlists.css' )."' type='text/css' media='all' />\n";

      echo "<link rel='stylesheet' id='fv_freedomplayer_admin'  href='".FV_FP_RELATIVE_PATH."/css/admin.css?ver=" . filemtime( dirname(__FILE__).'/../css/admin.css' ) . "' type='text/css' media='all' />\n";

      if( $this->bCSSInline ) {
        $this->css_generate(false);
      }

    } else {
      $aDeps = array();
      if( class_exists('OptimizePress_Default_Assets') ) $aDeps = array('optimizepress-default'); //  make sure the CSS loads after optimizePressPlugin

      wp_enqueue_style( 'fv_flowplayer', $sURL, $aDeps, $sVer );
      if(isset($sURLAdditions)) {
        wp_enqueue_style( 'fv_freedomplayer_additions', $sURLAdditions, array('fv_flowplayer'), $sVerAdditions );
      }

      if ( $this->bCSSPlaylists || $force || did_action( 'fv_player_force_load_assets' ) ) {
        wp_enqueue_style( 'fv_freedomplayer_playlists', FV_FP_RELATIVE_PATH.'/css/playlists.css', array('fv_flowplayer'), filemtime( dirname(__FILE__).'/../css/playlists.css' ) );
      }

      if(is_user_logged_in()){
        wp_enqueue_style( 'fv_freedomplayer_admin', FV_FP_RELATIVE_PATH.'/css/admin.css', array(), filemtime( dirname(__FILE__).'/../css/admin.css' ) );
      }

      if( $this->bCSSInline ) {
        add_action( did_action('wp_footer') ? 'wp_footer' : 'wp_head', array( $this, 'css_generate' ), 999 );
        add_action( 'admin_head', array( $this, 'css_generate' ) );
      }

    }

  }


  function css_option() {
    global $fv_wp_flowplayer_ver;
    return 'css_writeout-'.sanitize_title(home_url()) . '-' . $fv_wp_flowplayer_ver;
  }


  function css_path( $type = false ) {
    if( is_multisite() ) {
      global $blog_id;
      $site_id = $blog_id;
    } else {
      $site_id = 1;
    }

    $name = 'fv-player-custom/style-'.$site_id.'.css';
    if( 'name' == $type ) {
      return $name;
    } else if( 'url' == $type ) {
      return trailingslashit( str_replace( array('/plugins','\\plugins'), '', plugins_url() )).$name;
    } else {
      return trailingslashit(WP_CONTENT_DIR).$name;
    }
  }


  function css_writeout() {
    if( $this->_get_option('css_disable') ) {
      return false;
    }

    $aOptions = get_option( 'fvwpflowplayer' );
    $aOptions[$this->css_option()] = false;
    update_option( 'fvwpflowplayer', $aOptions );

    /*$url = wp_nonce_url('admin.php?page=fvplayer','otto-theme-options');
    if( false === ($creds = request_filesystem_credentials($url, $method, false, false, $_POST) ) ) { //  todo: no annoying notices here
      return false; // stop the normal page form from displaying
    }   */

    if ( ! WP_Filesystem(true) ) {
      return false;
    }

    global $wp_filesystem;
    $filename = $wp_filesystem->wp_content_dir().$this->css_path('name');

    // by this point, the $wp_filesystem global should be working, so let's use it to create a file

    $bDirExists = false;
    if( !$wp_filesystem->exists($wp_filesystem->wp_content_dir().'fv-player-custom/') ) {
      if( $wp_filesystem->mkdir($wp_filesystem->wp_content_dir().'fv-player-custom/') ) {
        $bDirExists = true;
      }
    } else {
      $bDirExists = true;
    }

    if( !$bDirExists ) {
      return false;
    }

    ob_start();
    $this->css_generate(true);

    $sCSS = "\n\n/*CSS writeout performed on FV Player Settings save  on ".gmdate('r')."*/\n".ob_get_clean();
    if( !$sCSSCurrent = $wp_filesystem->get_contents( dirname(__FILE__).'/../css/fv-player.min.css' ) ) {
      return false;
    }

    $sCSSCurrent = preg_replace_callback( '~url\(.*?\)~', array( $this, 'css_relative_paths_fix' ), $sCSSCurrent );

    /**
     * Only keep relative paths by replacing the https://domain.com with an empty string
     */
    $home_url_parsed = wp_parse_url( home_url() );

    if ( ! empty( $home_url_parsed['path'] ) ) {
      unset( $home_url_parsed['path'] );
    }

    // reverse wp_parse_url for $home_url_parsed
    $home_url_without_path = $home_url_parsed['scheme'] . '://' . $home_url_parsed['host'] . ( isset( $home_url_parsed['port'] ) ? ':' . $home_url_parsed['port'] : '' );

    $sCSSCurrent = str_replace( $home_url_without_path, '', $sCSSCurrent );

    /**
     * Replace any left-over URLs with protocol agnostic URLs
     */
    $sCSSCurrent = str_replace( array('http://', 'https://'), array('//','//'), $sCSSCurrent );

    if( !$wp_filesystem->put_contents( $filename, "/*\n * FV Player custom styles\n *\n * Warning: This file should not to be edited. Please put your custom CSS into your theme stylesheet or any custom CSS field of your template.\n */\n\n".$sCSSCurrent.$sCSS, FS_CHMOD_FILE) ) {
      return false;
    } else {
      $aOptions[$this->css_option()] = gmdate('U');
      update_option( 'fvwpflowplayer', $aOptions );
      $this->_get_conf();
    }
  }

  /*
   * @param array $match
   *        string $match[0] - CSS declaration with url() in "" or '' or without quotes,
   *                           using path like "icons/flowplayer.eot?#iefix"
   *                           or data URL like url("data:image/svg+xml;base64,PHN2...");
   *
   * @return string CSS declaration with fixed relative path.
   */
  function css_relative_paths_fix( $match ) {
    $path = self::get_plugin_url().'/css/';
    if(
      stripos($match[0],'data:') === false || // skip data URLs
      stripos($match[0],'//') === false  // skip absolute paths
    ) {
      if( stripos($match[0],'url("') === 0 ) {
        $match[0] = str_replace( 'url("', 'url("'.$path, $match[0] );
      } else if( stripos($match[0],"url('") === 0 ) {
        $match[0] = str_replace( "url('", "url('".$path, $match[0] );
      } else if( stripos($match[0],'url(') === 0 ) {
        $match[0] = str_replace( 'url(', 'url('.$path, $match[0] );
      }
    }
    return $match[0];
  }

  public function enable_cdn_rewrite( $item ) {
    if( is_admin() ) {
      return $item;
    }

    foreach( $item['sources'] AS $k => $source ) {

      // Not if the video URL has no path
      if ( empty( $source['src'] ) || ! wp_parse_url( $source['src'], PHP_URL_PATH ) ) {
        continue;
      }

      if( function_exists('get_rocket_cdn_url') ) {
        $item['sources'][$k]['src'] = get_rocket_cdn_url($source['src']);
      }

      if( method_exists('CDN_Enabler_Engine', 'rewriter') ) {
        $item['sources'][$k]['src'] = CDN_Enabler_Engine::rewriter($source['src']);
      }

      if( class_exists('BunnyCDN') && class_exists('BunnyCDNFilter') && method_exists( 'BunnyCDN', 'getOptions' ) ) {

        require_once dirname(__FILE__) . '/../includes/class.bunnycdn.rewrite.php';

        $options = BunnyCDN::getOptions();

        $fv_bunnycdn = new FV_Player_BunnyCDN_Rewrite($options["site_url"], (is_ssl() ? 'https://' : 'http://') . $options["cdn_domain_name"], $options["directories"], $options["excluded"], $options["disable_admin"]);
        $item['sources'][$k]['src'] = $fv_bunnycdn->rewrite_url($source['src']);
      }

    }

    return $item;
  }

  function enable_cdn_rewrite_maybe() {
    // Support WordPress CDN plugins - can slow down the PHP if you have hundreds of videos on a single page
    // We tried to check if the video is using the site domain before checking with the WordPress CDN plugins
    // But there is just no way around this - even that would be slow
    // So if you greatly care about peformance use:
    //
    // add_filter( 'fv_player_performance_disable_wp_cdn', '__return_true' );
    if( !apply_filters( 'fv_player_performance_disable_wp_cdn', false ) ) {
      add_filter( 'fv_player_item', array( $this, 'enable_cdn_rewrite'), 11 );
    }
  }

  public static function esc_caption( $caption ) {
    return str_replace( array(';','[',']'), array('\;','(',')'), $caption );
  }

  /*
   * Use the heavy-duty WordPress HTML filtering if the value looks like it might be HTML
   *
   * @param string $content
   *
   * @return string Filtered string
   */
  public static function filter_possible_html( $content ) {
    if( stripos($content, '<') !== false || stripos($content, '>') !== false ) {
      $content = wp_kses( $content, 'post' );
    }
    return $content;
  }


  function get_amazon_secure( $media ) {

    if( stripos($media,'X-Amz-Expires') !== false || stripos($media,'AWSAccessKeyId') !== false ) return $media;

    global $fv_fp;

    $amazon_key = -1;
    if( count($fv_fp->_get_option('amazon_key')) && count($fv_fp->_get_option('amazon_secret')) && count($fv_fp->_get_option('amazon_bucket')) ) {
      foreach( $fv_fp->_get_option('amazon_bucket') AS $key => $item ) {
        // The bucket name must be in the first folder
        // or the subdomain
        if(
          stripos($media,'.amazonaws.com/'.$item.'/') != false  ||
          stripos($media,'.amazonaws.com.cn/'.$item.'/') != false  ||
          stripos($media,'//'.$item.'.') != false
        ) {
          $amazon_key = $key;
          break;
        }
      }
    }

    if( $amazon_key != -1 &&
       $fv_fp->_get_option( array('amazon_key', $amazon_key) ) &&$fv_fp->_get_option( array('amazon_secret', $amazon_key) ) && $fv_fp->_get_option( array('amazon_bucket', $amazon_key) ) && stripos( $media, $fv_fp->_get_option( array('amazon_bucket', $amazon_key) ) ) !== false && apply_filters( 'fv_flowplayer_amazon_secure_exclude', $media ) ) {

      $resource = trim( $media );

      if( !isset($fv_fp->expire_time) ) {
        $time = 60 * intval($fv_fp->_get_option('amazon_expire'));
      } else {
        $time = intval(ceil($fv_fp->expire_time));
      }

      if( $fv_fp->_get_option('amazon_expire_force') ) {
        $time = 60 * intval($fv_fp->_get_option('amazon_expire'));
      }

      if( $time < 900 ) {
        $time = 900;
      }

      $time = apply_filters( 'fv_flowplayer_amazon_expires', $time, $media );

      // Allow to set the expire time to 0 to disable it = use 1 week
      if ( ! $time ) {
        $time = WEEK_IN_SECONDS;
      }

      $url_components = wp_parse_url($resource);

      // decode the path, as it might come partially URL encoded already
      $url_components['path'] = urldecode( $url_components['path'] );

      // URL encode the decoded path
      $url_components['path'] = rawurlencode( $url_components['path'] );

      // Restore the directory separators
      $url_components['path'] = str_replace('%2F', '/', $url_components['path']);

      $iAWSVersion = $fv_fp->_get_option( array( 'amazon_region', $amazon_key ) ) ? 4 : 2;

      if( $iAWSVersion == 4 ) {
        $sXAMZDate = gmdate('Ymd\THis\Z');
        $sDate = gmdate('Ymd');
        $sCredentialScope = $sDate."/".$fv_fp->_get_option( array('amazon_region', $amazon_key ) )."/s3/aws4_request"; //  todo: variable
        $sSignedHeaders = "host";
        $sXAMZCredential = urlencode( $fv_fp->_get_option( array('amazon_key', $amazon_key ) ).'/'.$sCredentialScope);

        //  1. http://docs.aws.amazon.com/general/latest/gr/sigv4-create-canonical-request.html
        $sCanonicalRequest = "GET\n";
        $sCanonicalRequest .= $url_components['path']."\n";
        $sCanonicalRequest .= "X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=$sXAMZCredential&X-Amz-Date=$sXAMZDate&X-Amz-Expires=$time&X-Amz-SignedHeaders=$sSignedHeaders\n";
        $sCanonicalRequest .= "host:".$url_components['host']."\n";
        $sCanonicalRequest .= "\n$sSignedHeaders\n";
        $sCanonicalRequest .= "UNSIGNED-PAYLOAD";

        //  2. http://docs.aws.amazon.com/general/latest/gr/sigv4-create-string-to-sign.html
        $sStringToSign = "AWS4-HMAC-SHA256\n";
        $sStringToSign .= "$sXAMZDate\n";
        $sStringToSign .= "$sCredentialScope\n";
        $sStringToSign .= hash('sha256',$sCanonicalRequest);

        //  3. http://docs.aws.amazon.com/general/latest/gr/sigv4-calculate-signature.html
        $sSignature = hash_hmac('sha256', $sDate, "AWS4".$fv_fp->_get_option( array('amazon_secret', $amazon_key) ), true );
        $sSignature = hash_hmac('sha256', $fv_fp->_get_option( array('amazon_region', $amazon_key) ), $sSignature, true );  //  todo: variable
        $sSignature = hash_hmac('sha256', 's3', $sSignature, true );
        $sSignature = hash_hmac('sha256', 'aws4_request', $sSignature, true );
        $sSignature = hash_hmac('sha256', $sStringToSign, $sSignature );

        //  4. http://docs.aws.amazon.com/general/latest/gr/sigv4-add-signature-to-request.html
        $resource .= "?X-Amz-Algorithm=AWS4-HMAC-SHA256";
        $resource .= "&X-Amz-Credential=$sXAMZCredential";
        $resource .= "&X-Amz-Date=$sXAMZDate";
        $resource .= "&X-Amz-Expires=$time";
        $resource .= "&X-Amz-SignedHeaders=$sSignedHeaders";
        $resource .= "&X-Amz-Signature=".$sSignature;

      } else {
        $expires = time() + $time;

        if( strpos( $url_components['path'], $fv_fp->_get_option( array('amazon_bucket', $amazon_key) ) ) === false ) {
          $url_components['path'] = '/'.$fv_fp->_get_option( array('amazon_bucket', $amazon_key) ).$url_components['path'];
        }

        do {
          $expires++;
          $stringToSign = "GET\n\n\n$expires\n{$url_components['path']}";

          $signature = utf8_encode($stringToSign);

          $signature = hash_hmac('sha1', $signature, $fv_fp->_get_option( array('amazon_secret', $amazon_key ) ), true);
          $signature = base64_encode($signature);

          $signature = urlencode($signature);
        } while( stripos($signature,'%2B') !== false );

        $resource .= '?AWSAccessKeyId='.$fv_fp->_get_option( array('amazon_key', $amazon_key) ).'&Expires='.$expires.'&Signature='.$signature;

      }

      $media = $resource;

    }

    return $media;
  }

  function get_amazon_secure_long( $media ) {
    add_filter( 'fv_flowplayer_amazon_expires', '__return_false' );

    $signed_media = $this->get_amazon_secure( $media, WEEK_IN_SECONDS );

    remove_filter( 'fv_flowplayer_amazon_expires', '__return_false' );

    return $signed_media;
  }

  public static function hms_to_seconds( $tDuration ) {
    if ( preg_match('/([\d]{1,2}\:)?[\d]{1,2}\:[\d]{2}/', $tDuration) ) {
      $tDuration = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $tDuration);
      sscanf($tDuration, "%d:%d:%d", $hours, $minutes, $seconds);
      $tDuration = $hours * 3600 + $minutes * 60 + $seconds;
    }
    return $tDuration;
  }

  public static function format_hms( $seconds ) {
    if( !is_numeric($seconds) ) return $seconds;

    $seconds = round( $seconds );

    if( $seconds < 3600 ) {
      return gmdate( "i:s", $seconds );
    } else {
      return gmdate( "H:i:s", $seconds );
    }
  }


  public static function get_duration( $post_id, $video_src, $seconds = false ) {
    if( $sVideoMeta = get_post_meta( $post_id, flowplayer::get_video_key($video_src), true ) ) {  //  todo: should probably work regardles of quality version
      if( isset($sVideoMeta['duration']) && $sVideoMeta['duration'] > 0 ) {
        if( $seconds ) {
          return $sVideoMeta['duration'];
        }

        return flowplayer::format_hms($sVideoMeta['duration']);
      }
    }
    return false;
  }


  /*
   * Get duration of the longets video in the post
   */
  public static function get_duration_post( $post_id = false ) {
    global $post, $fv_fp;
    $post_id = ( $post_id ) ? $post_id : $post->ID;

    if( $fv_fp->_get_option('player_model_db_checked') && $fv_fp->_get_option('player_meta_model_db_checked') && $fv_fp->_get_option('video_model_db_checked') && $fv_fp->_get_option('video_meta_model_db_checked') ) {
      global $wpdb;
      $tDuration = intval( $wpdb->get_var( "SELECT v.duration FROM {$wpdb->prefix}fv_player_playermeta AS pm JOIN {$wpdb->prefix}fv_player_players AS p ON p.id = pm.id_player JOIN {$wpdb->prefix}fv_player_videos AS v ON FIND_IN_SET(v.id, p.videos) > 0 WHERE pm.meta_key = 'post_id' AND pm.meta_value = ".intval($post_id)." ORDER BY v.duration DESC LIMIT 1" ) );

      if( $tDuration > 3600 ) {
        return gmdate( "H:i:s", $tDuration );
      } else if( $tDuration > 0 ) {
        return gmdate( "i:s", $tDuration );
      }
    }

    $content = false;
    $objPost = get_post($post_id);
    if( $aVideos = FV_Player_Checker::get_videos($objPost->ID) ) {
      foreach( $aVideos AS $video ) {
        $tDuration = flowplayer::get_duration($post_id, $video, true );
        if( !$content || $tDuration > $content ) {
          $content = $tDuration;
        }
      }
    }

    if( $content ) {
      $content = flowplayer::format_hms($content);
    }

    return $content;
  }


  public static function get_duration_playlist( $caption ) {
    if( !$caption ) return $caption;

    global $post;
    $aArgs = func_get_args();

    if( $post && isset($aArgs[1][0]) && is_array($aArgs[1][0]) ) {
      $sItemKeys = array_keys($aArgs[1][0]);
      if( $sDuration = flowplayer::get_duration( $post->ID, $aArgs[1][0][$sItemKeys[0]] ) ) {
        $caption .= '<i class="dur">'.$sDuration.'</i>';
      }
    }

    return $caption;
  }


  public static function get_duration_video( $content ) {
    global $post;
    if( !$post ) return $content;

    $aArgs = func_get_args();
    if( $sDuration = flowplayer::get_duration( $post->ID, $aArgs[1]->aCurArgs['src']) ) {
      $content .= '<div class="fvfp_duration">'.$sDuration.'</div>';
    }

    return $content;
  }


  public static function get_encoded_url( $sURL ) {

    $parsed_url = wp_parse_url( $sURL );
    $url_parts_encoded = wp_parse_url( $sURL );

    if( !empty($parsed_url['path']) ) {
      $parsed_url['path'] = join('/', array_map( 'rawurlencode', array_map('urldecode', explode('/', $url_parts_encoded['path']) ) ) );
      $parsed_url['path'] = str_replace( '%2B', '+', $parsed_url['path'] );
    }

    if( !empty($parsed_url['query']) ) {
      $parsed_url['query'] = str_replace( '&amp;', '&', $url_parts_encoded['query'] );
    }

    // https://www.php.net/manual/en/function.parse-url.php#106731
    $scheme   = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
    $port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
    $user     = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
    $pass     = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass']  : '';
    $pass     = ( $user || $pass ) ? "$pass@" : '';
    $path     = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
    $query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
    $fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';

    return "$scheme$user$pass$host$port$path$query$fragment";
  }


  public static function get_languages() {
    // List taken from https://www.localeplanet.com/icu/
    $aLangs = array(
      'SDH' => 'SDH',
      'AA' => 'Afaraf',
      'AB' => 'аҧсшәа',
      'AF' => 'Afrikaans',
      'AGQ' => 'Aghem',
      'AK' => 'Akan',
      'AM' => 'አማርኛ',
      'AR' => 'العربية',
      'AS' => 'অসমীয়া',
      'ASA' => 'Kipare',
      'AST' => 'asturianu',
      'AY' => 'aymar aru',
      'AZ' => 'azərbaycan',
      'BA' => 'башҡорт теле',
      'BAS' => 'Ɓàsàa',
      'BE' => 'беларуская',
      'BEM' => 'Ichibemba',
      'BEZ' => 'Hibena',
      'BH' => 'भोजपुरी',
      'BI' => 'Bislama',
      'BG' => 'български',
      'BM' => 'bamanakan',
      'BN' => 'বাংলা',
      'BO' => 'བོད་སྐད་',
      'BR' => 'brezhoneg',
      'BRX' => 'बड़ो',
      'BS' => 'bosanski',
      'CA' => 'català',
      'CE' => 'нохчийн',
      'CGG' => 'Rukiga',
      'CHR' => 'ᏣᎳᎩ',
      'CO' => 'Corsu',
      'CKB' => 'کوردیی ناوەندی',
      'CS' => 'Čeština',
      'CY' => 'Cymraeg',
      'DA' => 'dansk',
      'DAV' => 'Kitaita',
      'DE' => 'Deutsch',
      'DJE' => 'Zarmaciine',
      'DSB' => 'dolnoserbšćina',
      'DUA' => 'duálá',
      'DYO' => 'joola',
      'DZ' => 'རྫོང་ཁ',
      'EBU' => 'Kĩembu',
      'EE' => 'Eʋegbe',
      'EL' => 'Ελληνικά',
      'EN' => 'English',
      'EO' => 'esperanto',
      'ES' => 'español',
      'ET' => 'eesti',
      'EU' => 'euskara',
      'EWO' => 'ewondo',
      'FA' => 'فارسی',
      'FF' => 'Pulaar',
      'FI' => 'suomi',
      'FJ' => 'vosa Vakaviti',
      'FIL' => 'Filipino',
      'FO' => 'føroyskt',
      'FR' => 'français',
      'FUR' => 'furlan',
      'FY' => 'Frysk',
      'GA' => 'Gaeilge',
      'GD' => 'Gàidhlig',
      'GL' => 'galego',
      'GN' => 'Avañe\'ẽ',
      'GSW' => 'Schwiizertüütsch',
      'GU' => 'ગુજરાતી',
      'GUZ' => 'Ekegusii',
      'GV' => 'Gaelg',
      'HA' => 'Hausa',
      'HAW' => 'ʻŌlelo Hawaiʻi',
      'HE' => 'עברית',
      'HI' => 'हिन्दी',
      'HR' => 'hrvatski',
      'HSB' => 'hornjoserbšćina',
      'HU' => 'magyar',
      'HY' => 'հայերեն',
      'IA' => 'Interlingua',
      'IE' => 'Interlingue',
      'ID' => 'Indonesia',
      'IG' => 'Igbo',
      'IK' => 'Iñupiatun',
      'II' => 'ꆈꌠꉙ',
      'IS' => 'íslenska',
      'IT' => 'italiano',
      'JA' => '日本語',
      'JI' => 'אידיש',
      'JV' => 'basa Jawa',
      'JGO' => 'Ndaꞌa',
      'JMC' => 'Kimachame',
      'KA' => 'ქართული',
      'KAB' => 'Taqbaylit',
      'KAM' => 'Kikamba',
      'KDE' => 'Chimakonde',
      'KEA' => 'kabuverdianu',
      'KHQ' => 'Koyra ciini',
      'KI' => 'Gikuyu',
      'KK' => 'қазақ тілі',
      'KKJ' => 'kakɔ',
      'KL' => 'kalaallisut',
      'KLN' => 'Kalenjin',
      'KM' => 'ខ្មែរ',
      'KN' => 'ಕನ್ನಡ',
      'KO' => '한국어',
      'KOK' => 'कोंकणी',
      'KS' => 'کٲشُر',
      'KSB' => 'Kishambaa',
      'KSF' => 'rikpa',
      'KSH' => 'Kölsch',
      'KW' => 'kernewek',
      'KU' => 'كوردی‎',
      'KY' => 'кыргызча',
      'LA' => 'Latine',
      'LAG' => 'Kɨlaangi',
      'LB' => 'Lëtzebuergesch',
      'LG' => 'Luganda',
      'LKT' => 'Lakȟólʼiyapi',
      'LN' => 'lingála',
      'LO' => 'ລາວ',
      'LRC' => 'لۊری شومالی',
      'LT' => 'lietuvių',
      'LU' => 'Tshiluba',
      'LUO' => 'Dholuo',
      'LUY' => 'Luluhia',
      'LV' => 'latviešu',
      'MAS' => 'Maa',
      'MER' => 'Kĩmĩrũ',
      'MFE' => 'kreol morisien',
      'MG' => 'Malagasy',
      'MGH' => 'Makua',
      'MGO' => 'metaʼ',
      'MI' => 'te reo Māori',
      'MK' => 'македонски',
      'ML' => 'മലയാളം',
      'MN' => 'монгол',
      'MR' => 'मराठी',
      'MS' => 'Melayu',
      'MT' => 'Malti',
      'MUA' => 'MUNDAŊ',
      'MY' => 'မြန်မာ',
      'MZN' => 'مازرونی',
      'NA' => 'Ekakairũ Naoero',
      'NO' => 'Norsk',
      'NAQ' => 'Khoekhoegowab',
      'NB' => 'norsk bokmål',
      'ND' => 'isiNdebele',
      'NDS' => 'nds',
      'NE' => 'नेपाली',
      'NL' => 'Nederlands',
      'NMG' => 'nmg',
      'NN' => 'nynorsk',
      'NNH' => 'Shwóŋò ngiembɔɔn',
      'NUS' => 'Thok Nath',
      'NYN' => 'Runyankore',
      'OC' => 'occitan, lenga d\'òc',
      'OM' => 'Oromoo',
      'OR' => 'ଓଡ଼ିଆ',
      'OS' => 'ирон',
      'PA' => 'ਪੰਜਾਬੀ',
      'PL' => 'polski',
      'PS' => 'پښتو',
      'PT' => 'português',
      'QU' => 'Runasimi',
      'RM' => 'rumantsch',
      'RN' => 'Ikirundi',
      'RO' => 'română',
      'ROF' => 'Kihorombo',
      'RU' => 'русский',
      'RW' => 'Kinyarwanda',
      'RWK' => 'Kiruwa',
      'SA' => 'संस्कृतम्',
      'SD' => 'सिन्धी, سنڌي، سندھی‎',
      'ST' => 'Sesotho',
      'SAH' => 'саха тыла',
      'SAQ' => 'Kisampur',
      'SBP' => 'Ishisangu',
      'SM' => 'gagana fa\'a Samoa',
      'SE' => 'davvisámegiella',
      'SEH' => 'sena',
      'SES' => 'Koyraboro senni',
      'SG' => 'Sängö',
      'SHI' => 'ⵜⴰⵛⵍⵃⵉⵜ',
      'SI' => 'සිංහල',
      'SK' => 'slovenčina',
      'SL' => 'slovenščina',
      'SMN' => 'anarâškielâ',
      'SN' => 'chiShona',
      'SS' => 'SiSwati',
      'SO' => 'Soomaali',
      'SQ' => 'shqip',
      'SR' => 'српски',
      'SV' => 'svenska',
      'SU' => 'Basa Sunda',
      'SW' => 'Kiswahili',
      'TA' => 'தமிழ்',
      'TE' => 'తెలుగు',
      'TEO' => 'Kiteso',
      'TL' => 'Wikang Tagalog',
      'TS' => 'Xitsonga',
      'TK' => 'Türkmen, Түркмен',
      'TG' => 'тоҷикӣ',
      'TH' => 'ไทย',
      'TI' => 'ትግርኛ',
      'TN' => 'Setswana',
      'TO' => 'lea fakatonga',
      'TR' => 'Türkçe',
      'TT' => 'татар',
      'TW' => 'Twi',
      'TWQ' => 'Tasawaq senni',
      'TZM' => 'Tamaziɣt n laṭlaṣ',
      'UG' => 'ئۇيغۇرچە',
      'UK' => 'українська',
      'UR' => 'اردو',
      'UZ' => 'o‘zbek',
      'VAI' => 'ꕙꔤ',
      'VI' => 'Tiếng Việt',
      'VO' => 'Volapük',
      'VUN' => 'Kyivunjo',
      'WAE' => 'Walser',
      'WO' => 'Wolof',
      'XOG' => 'Olusoga',
      'XH' => 'isiXhosa',
      'YAV' => 'nuasue',
      'YI' => 'ייִדיש',
      'YO' => 'Èdè Yorùbá',
      'YUE' => '粵語',
      'ZGH' => 'ⵜⴰⵎⴰⵣⵉⵖⵜ',
      'ZH' => '中文',
      'ZH_HANS' => '中文（简体）',
      'ZU' => 'isiZulu'
    );

    // Uppercase first letter
    foreach( $aLangs as $code => $native ) {
      if( function_exists('mb_convert_case') ) {
        $aLangs[$code] = mb_convert_case($native, MB_CASE_TITLE, "UTF-8");
      } else {
        $aLangs[$code] = $native;
      }
    }

    ksort($aLangs);

    return $aLangs;
  }


  function get_mime_type($media, $default = 'mp4', $no_video = false) {
    $media = trim($media);
    $aURL = explode( '?', $media ); //  throwing away query argument here
    $pathinfo = pathinfo( $aURL[0] );
    if( empty($pathinfo['extension']) ) $pathinfo = pathinfo( $media ); // but if no extension remains, keep the query arguments, todo: unit test for https://drive.google.com/uc?export=download&id=0B32098YdDwTAcmJxVl9Kc1piT2s#.mp4

    $extension = ( isset($pathinfo['extension']) ) ? $pathinfo['extension'] : false;
    $extension = preg_replace( '~[?#].+$~', '', $extension );
    $extension = strtolower($extension);

    if( !$extension ) {
      $output = $default;
    } else {
      if ($extension == 'm3u8' || $extension == 'm3u') {
        $output = 'x-mpegurl';
      } else if ($extension == 'mpd') {
        $output = 'dash+xml';
      } else if ($extension == 'm4v') {
        $output = 'mp4';
      } else if( $extension == 'mp3' ) {
        $output = 'mpeg';
      } else if( $extension == 'wav' ) {
        $output = 'wav';
      } else if( $extension == 'ogg' ) {
        $output = 'ogg';
      } else if( $extension == 'ogv' ) {
        $output = 'ogg';
      } else if( $extension == 'mov' ) {
        $output = 'mp4';
      } else if( $extension == '3gp' ) {
        $output = 'mp4';
      } else if( $extension == 'mkv' ) {
        $output = 'mp4';
      } else if( $extension == 'mp3' ) {
        $output = 'mpeg';
      } else if( !in_array($extension, array('mp4', 'm4v', 'webm', 'ogv', 'ogg', 'wav', '3gp')) ) {
        $output = $default;
      } else {
        $output = $extension;
      }
    }

    if( $output == 'flash' ) {
      if( stripos( $media, '(format=m3u8' ) !== false ) { //  http://*.streaming.mediaservices.windows.net/*.ism/manifest(format=m3u8-aapl)
        $output = 'x-mpegurl';
        $extension = 'm3u8';
      }
      if( stripos( $media, '(format=mpd' ) !== false ) {  //  http://*.streaming.mediaservices.windows.net/*.ism/manifest(format=mpd-time-csf)
        $output = 'dash+xml';
        $extension = 'mpd';
      }
    }

    global $fv_fp;
    if( $extension == 'mpd' ) {
      $fv_fp->load_dash = true;
    } else if( $extension == 'm3u8' ) {
      $fv_fp->load_hlsjs = true;
    }

    if( !$no_video ) {
      switch($extension)  {
        case 'dash+xml' :
        case 'mpd' :
          $output = 'application/'.$output;
          break;
        case 'x-mpegurl' :
          $output = 'application/'.$output;
          break;
        case 'm3u8' :
          $output = 'application/'.$output;
          break;
        case 'flac' :
        case 'mp3' :
        case 'ogg' :
        case 'wav' :
          $output = 'audio/'.$output;
          break;
        default:
          $output = 'video/'.$output;
          break;
      }
    }

    $output = apply_filters( 'fv_flowplayer_get_mime_type', $output, $media );

    // The custom HTML5 engine to support Ajax loading of video source URL is now part of core, no need to init it in any special way
    if ( 'video/fv-mp4' === $output ) {
      $output = 'video/mp4';
    }

    return $output;
  }


  public static function get_plugin_url() {
    if( stripos( __FILE__, '/themes/' ) !== false || stripos( __FILE__, '\\themes\\' ) !== false ) {
      return get_template_directory_uri().'/fv-player';
    } else {
      $plugin_folder = basename(dirname(dirname(__FILE__))); // make fv-wordpress-flowplayer out of {anything}/fv-wordpress-flowplayer/models/flowplayer.php
      return plugins_url($plugin_folder);
    }
  }


  public function get_playlist_class($aCaptions) {
    $sClass = 'fv-playlist-design-';
    if( !empty($this->aCurArgs['listdesign']) ) {
      $sClass .= $this->aCurArgs['listdesign'];
    } else {
      $sClass .= $this->_get_option('playlist-design');
    }

    // Playlist design doesn't have any use for these two playlist styles:
    if( !empty($this->aCurArgs['liststyle']) && in_array($this->aCurArgs['liststyle'], array( 'season', 'polaroid', 'version-one', 'version-two' ) ) ) {
      $sClass = '';
    }

    if( isset($this->aCurArgs['liststyle']) ) {
      $list_style = $this->aCurArgs['liststyle'];
      if( $list_style == 'slider' ) {
        $sClass .= ' fp-playlist-horizontal';
      } else if( $list_style == 'text' ) {
        $sClass = 'fp-playlist-vertical';
      } else {
        $sClass .= ' fp-playlist-'.$list_style;
      }

      if( $list_style == 'text' ) {
        $sClass .= ' fp-playlist-only-captions';
      } else if( sizeof($aCaptions) > 0 && strlen(implode("",$aCaptions)) > 0 ) {
        $sClass .= ' fp-playlist-has-captions';
      }
    }

    if ( get_query_var('fv_player_embed') || get_query_var('fv_player_cms_id') ) {
      $sClass .= ' fp-is-embed';
    }

    if( !empty($this->aCurArgs['skin']) ) {
      $sClass .= ' skin-'.$this->aCurArgs['skin'];
    } else {
      $sClass .= ' skin-'.$this->_get_option('skin');
    }

    return $sClass;
  }


  public static function get_core_version() {
    global $fv_wp_flowplayer_core_ver;
    return $fv_wp_flowplayer_core_ver;
  }


  function get_server_url() {
    $url = is_ssl() ? 'https://' : 'http://';

    $url .= sanitize_text_field( $_SERVER['SERVER_NAME'] );

    $url = sanitize_url( $url );

    if ( ! empty( $_SERVER['SERVER_PORT'] ) && intval( $_SERVER['SERVER_PORT'] ) != 80 && intval( $_SERVER['SERVER_PORT'] ) != 443 ) {
      $url .= ':' . intval( $_SERVER['SERVER_PORT'] );
    }

    $url .= '/';

    return $url;
  }


  public static function get_video_key( $sURL ) {
    $sURL = str_replace( '?v=', '-v=', $sURL );
    $sURL = preg_replace( '~\?.*$~', '', $sURL );
    $sURL = str_replace( array('/','://'), array('-','-'), $sURL );
    return '_fv_flowplayer_'.sanitize_title($sURL);
  }


  public static function get_title_from_src( $src ) {
    $name = wp_parse_url( $src, PHP_URL_PATH );
    $arr = explode('/', $name);
    $name = trim( end($arr) );

    if( in_array( $name, array( 'index.m3u8', 'stream.m3u8', 'master.m3u8', 'playlist.m3u8' ) ) ) {
      unset($arr[count($arr)-1]);
      $name = end($arr);

      // Add parent folder too if there's any
      if( !empty( $arr ) && count( $arr ) > 2 ) {
        unset($arr[count($arr)-1]);
        $name = end($arr) . '/' . $name;
      }
    }

    return $name;
  }


  function get_video_src($media, $aArgs = array() ) {
    $aArgs = wp_parse_args( $aArgs, array(
          'dynamic' => false, // apply URL signature for CDNs which normally use Ajax
          'suppress_filters' => false,
        )
      );

    if( $media ) {
      if( !$aArgs['suppress_filters'] ) {
        $media = apply_filters( 'fv_flowplayer_video_src', $media, $aArgs );
      }
      return wp_strip_all_tags(trim($media));
    }
    return null;
  }


  function get_video_url($media) {
    if( !is_string($media) ) return $media;

    if( strpos($media,'rtmp://') !== false ) {
      return null;
    }
    if( strpos($media,'http://') !== 0 && strpos($media,'https://') !== 0 && strpos($media,'//') !== 0 ) {
      if ( $media[0] === '/' ) {
        $media = substr($media, 1);
      }

      $media = $this->get_server_url() . $media;
    }

    $media = apply_filters( 'fv_flowplayer_media', $media, $this );
    return $media;
  }


  public static function is_licensed() {
    global $fv_fp;
    return preg_match( '!^\$\d+!', $fv_fp->_get_option('key') );
  }


  public static function is_special_editor() {
    return flowplayer::is_optimizepress() || flowplayer::is_themify();
  }


  public static function is_optimizepress() {
    if( ( isset($_GET['page']) && sanitize_key( $_GET['page'] ) == 'optimizepress-page-builder' ) ||
        ( isset($_POST['action']) && sanitize_key( $_POST['action'] ) == 'optimizepress-live-editor-parse' )
      ) {
      return true;
    }
    return false;
  }


  public static function is_themify() {
    if( isset($_POST['action']) && sanitize_key( $_POST['action'] ) == 'tfb_load_module_partial' ) {
      return true;
    }
    return false;
  }


  public static function is_wp_rocket_setting( $setting ) {
    return function_exists( 'get_rocket_option') && get_rocket_option( $setting );
  }


  public function is_secure_amazon_s3( $url ) {
    return preg_match( '/^.+?s3.*?\.amazonaws\.com\/.+Signature=.+?$/', $url ) || preg_match( '/^.+?\.cloudfront\.net\/.+Signature=.+?$/', $url );
  }


  public static function json_encode( $input ) {
    if( version_compare(phpversion(), '5.3.0', '>') ) {
      return wp_json_encode( $input, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
    } else {
      return str_replace( "'", '\u0027', wp_json_encode( $input ) );
    }
  }


  function popup_css( $css ){
    $aPopupData = get_option('fv_player_popups');
    $sNewCss = '';
    if( is_array($aPopupData) ) {
      foreach($aPopupData as $key => $val){
        if( empty($val['css']) ){
          continue;
        }
        $sNewCss .= '.flowplayer '.stripslashes($val['css'])."\n";
      }
    }
    if( strlen($sNewCss) ){
      $css .= "\n/*custom popup css*/\n".$sNewCss."\n/*end custom popup css*/\n";
    }
    return $css;
  }

  function custom_css( $css ) {
    global $fv_fp;
    $aCustomCSS = $fv_fp->_get_option('customCSS');

    if( strlen($aCustomCSS) ) {
      $css .= "\n/*custom css*/\n".wp_strip_all_tags( stripslashes($aCustomCSS) )."\n/*end custom css*/\n";
    }
    return $css;
  }

  /*
  * This function changes the /fvp/{number} rewrite endpoint created with fv_player_embed_rewrite_endpoint()
  * to accept /fvp{number} or  fvp-{number} and also adds rule fo /fvp only (no number)
  *
  * Example:
  * [(.?.+?)/fvp(/(.*))?/?$] => index.php?pagename=$matches[1]&fv_player_embed=$matches[3]
  *
  * is changed to
  *
  * [(.?.+?)/fvp((-?.*))?/?$] => index.php?pagename=$matches[1]&fv_player_embed=$matches[3]
  *
  * and new rule for /fvp without number is added, example:
  * [(.?.+?)/fvp/?$] => index.php?pagename=$matches[1]&fv_player_embed=1
  */
  function fv_player_embed_rewrite_rules_fix( $aRules ) {
    $aRulesNew = array();
    foreach( $aRules AS $k => $v ) {
      if( stripos($k,'/fvp(/') !== false ) {
        // 1st rule
        $new_k = str_replace( 'fvp(/(.*))?', 'fvp', $k ); // fvp only
        $new_v = preg_replace('/fv_player_embed=\$matches\[\d]/', 'fv_player_embed=1', $v); // fv_player_embed=1

        $aRulesNew[$new_k] = $new_v;

        // 2nd rule
        $new_k = str_replace( '/fvp(/(.*))', '/fvp((-?\d+))', $k ); // fvp{number} or fvp-{number}

        $aRulesNew[$new_k]= $v;
      } else {
        $aRulesNew[$k] = $v;
      }
    }

    return $aRulesNew;
  }

  function fv_player_embed_rewrite_endpoint() {
    add_rewrite_endpoint( 'fvp', EP_PERMALINK | EP_PAGES, 'fv_player_embed' );
  }

  function rewrite_vars( $public_query_vars ) {
    $public_query_vars[] = 'fv_player_embed';
    return $public_query_vars;
  }

  function template_embed_buffer(){
    if( get_query_var('fv_player_embed') ) {
      ob_start();

      global $fvseo;
      if( isset( $_REQUEST['fv_player_preview_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['fv_player_preview_nonce'] ) ), 'fv_player_preview' ) && isset($_REQUEST['fv_player_preview']) ) {
        global $fvseo;
        if( isset($fvseo) ) remove_action('wp_footer', array($fvseo, 'script_footer_content'), 999999 );

        global $objTracker;
        if( isset($objTracker) ) remove_action( 'wp_footer', array( $objTracker, 'OutputFooter' ) );
      }
    }
  }

  function template_embed() {
    // Generate embed html
    if( $embed_id = get_query_var('fv_player_embed') ) {
      $content = ob_get_contents();
      ob_clean();

      if( function_exists('rocket_insert_load_css') ) rocket_insert_load_css();

      remove_action( 'wp_footer', array( $this, 'template_embed' ),0 );
      //remove_action('wp_head', '_admin_bar_bump_cb');
      show_admin_bar(false);
      ?>
  <style>
    body { margin: 0; padding: 0; overflow:hidden; background:white;}
    body:before { height: 0px!important;}
    html {margin-top: 0px !important; overflow:hidden; }
  </style>
</head>
<body class="fv-player-preview">
  <?php
    if( stripos($content,'<!--fv player end-->') !== false ) {

      $bFound = false;
      $rewrite = get_option('rewrite_rules');
      if( empty($rewrite) ) {
        $sLink = 'fv_player_embed='.$embed_id;
      } else {
        $sPostfix = $embed_id == 1 ? 'fvp' : 'fvp'.$embed_id;
        $sLink = user_trailingslashit( trailingslashit( get_permalink() ).$sPostfix );
      }

      $aPlayers = explode( '<!--fv player end-->', $content );
      if( $aPlayers ) {
        foreach( $aPlayers AS $k => $v ) {
          if( stripos($v,$sLink.'"') !== false ) {
            echo substr($v, stripos($v,'<div id="wpfp_') );
            $bFound = true;
            break;
          }
        }
      }

      if( !$bFound && is_numeric($embed_id) && !empty($aPlayers[$embed_id-1]) ) {
        echo substr($aPlayers[$embed_id-1], stripos($aPlayers[$embed_id-1],'<div id="wpfp_') );
        $bFound = true;
      }

      if( !$bFound ) {
        echo "<p>Player not found, see the full article: <a href='".get_permalink()."' target='_blank'>".get_the_title()."</a>.</p>";
      }

    }

    wp_footer();

    ?>
    </body>
    </html>
    <?php
    exit();
    }
  }

  function preview_no_lazy_load( $value ) {
    if( isset( $_REQUEST['fv_player_preview_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['fv_player_preview_nonce'] ) ), 'fv_player_preview' ) && isset($_REQUEST['fv_player_preview']) ) {
      return false;
    }
    return $value;
  }

  function process_preferred_video_type( $sources ) {

    // Match video URL extensions and put these first
    if ( ! empty( $this->aCurArgs['prefer'] ) ) {
      $extension = '.' . sanitize_key( $this->aCurArgs['prefer'] );

      $new_sources = array();

      foreach( $sources as $source_k => $source ) {
        if ( stripos( $source['src'], $extension ) !== false ) {
          $new_sources[] = $source;
          unset( $sources[ $source_k ] );
        }
      }

      $sources = array_merge( $new_sources, $sources );
    }

    return $sources;
  }

  function searchwp_pre_post_content( $post ) {
    $post->post_content = preg_replace( '~(\[fvplayer(.*?)\])~', '<!--FV Player Search WP Start-->[fvplayer embed="false" $1]<!--FV Player Search WP End-->', $post->post_content );
    return $post;
  }

  function searchwp_post_content( $post ) {
    $post->post_content = preg_replace_callback( '~<!--FV Player Search WP Start-->[\s\S]*?<!--FV Player Search WP End-->~', array( $this, 'searchwp_post_content_callback' ), $post->post_content );
    return $post;
  }

  function searchwp_post_content_callback( $match ) {
    $html = $match[0];

    $html = preg_replace( '~<noscript.*?</noscript>~', '', $html );
    $html = preg_replace( '~<script.*?</script>~', '', $html );
    $html = preg_replace( '~<svg.*?</svg>~', '', $html );

    $allowed_html = array(
      // Allowing the 'data-item' attribute for 'a' tags
      'a' => array(
          'data-item' => true,
      ),
      // Allow 'data-item' for 'div' tags
      'div' => array(
          'data-item' => true,
      ),
      // ... Add other tags as needed
    );

    $html = wp_kses( $html, $allowed_html );

    return $html;
  }

  // Also used by FV Player extensions
  function should_force_load_js() {
    return $this->_get_option('js-everywhere') || isset($_GET['brizy-edit-iframe']) || isset($_GET['elementor-preview']) || did_action('fv_player_force_load_assets');
  }

  function template_preview() {
    if( !empty($_GET['fv_player_preview']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['fv_player_preview_nonce'] ) ), 'fv_player_preview' ) ) {
    // Generate preview html
    show_admin_bar(false);
    ?>
    <html>
      <head>
        <?php wp_head(); ?>
        <style>
          body { margin: 0; padding: 0; overflow:hidden; background:white;}
          body:before { height: 0px!important;}
          html {margin-top: 0px !important; overflow:hidden; }
        </style>
      </head>
      <body class="fv-player-preview">
    <?php

      if( isset( $_REQUEST['fv_player_preview_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['fv_player_preview_nonce'] ) ), 'fv_player_preview' ) && isset($_REQUEST['fv_player_preview']) ) :
        $shortcode = base64_decode( sanitize_text_field( $_REQUEST['fv_player_preview'] ) );
        $shortcode = apply_filters( 'fv_player_preview_data', $shortcode );

      $matches = null;
      $width ='';
      $height ='';

      // width from regular shortcdode data
      if ( preg_match('/width="([0-9.,]*)"/', $shortcode, $matches)) {
        $width = 'width:'.$matches[1].'px;';
      }

      // width from DB shortcdode data
      if ( !empty($shortcode['fv_wp_flowplayer_field_width'])) {
        $width = 'width:'.$shortcode['fv_wp_flowplayer_field_width'].'px;';
      }

      // height from regular shortcdode data
      if( preg_match('/height="([0-9.,]*)"/', $shortcode, $matches)) {
        $height = 'min-height:'.$matches[1].'px;';
      }

      // height from DB shortcdode data
      if ( !empty($shortcode['fv_wp_flowplayer_field_height'])) {
        $height = 'min-height:'.$shortcode['fv_wp_flowplayer_field_height'].'px;';
      }

      ?>
      <div style="background:white;">
        <div id="wrapper" style="background:white; overflow:hidden; <?php echo esc_html( $width . $height ); ?>;">
      <?php
      // regular shortcode data with source
      global $fv_fp;
      if ( preg_match('/id="\d+"|src="[^"][^"]*"/i',$shortcode)) {
        $aAtts = shortcode_parse_atts($shortcode);
        if ( $aAtts && !empty($aAtts['liststyle'] ) && $aAtts['liststyle'] == 'vertical' || $fv_fp->_get_option('liststyle') == 'vertical' ) {
          esc_html_e('The preview is too narrow, vertical playlist will shift below the player as it would on mobile.', 'fv-player');
        }
        echo do_shortcode($shortcode);
      } else { ?>
        <h1 style="margin: auto;text-align: center; padding: 60px; color: darkgray;">No video.</h1>
        <?php
      }
      ?>
      </div>
    </div>

    <?php

    endif;

    ?>
      <?php
      wp_footer();
      if( isset($_GET['fv_player_preview']) && !empty($_GET['fv_player_preview']) ) :
      ?>
      <script>
        jQuery(document).ready( function(){
          var parent = window.parent.jQuery(window.parent.document);
          if( typeof(flowplayer) != "undefined" ) {
            parent.trigger('fvp-preview-complete', [jQuery(document).width(),jQuery(document).height()]);

          } else {
            parent.trigger('fvp-preview-error');
          }

        });

        if (window.top===window.self) {
          jQuery('#wrapper').css('margin','25px 50px 0 50px');
        }
      </script>
      <?php endif; ?>
      </body>
    </html>
    <?php

    exit;
  }
  }

  function wp_kses_permit_scripts( $tags, $context = false ) {
    if( $context != 'post' ) return $tags;

    if ( empty($tags['ins']) ) {
      $tags['ins'] = array();
    }

    $tags['ins']['class'] = true;
    $tags['ins']['data-zoneid'] = true;

    if ( empty($tags['script']) ) {
      $tags['script'] = array();
    }

    $tags['script']['src'] = true;
    $tags['script']['type'] = true;
    $tags['script']['async'] = true;

    return $tags;
  }

  function wp_kses_permit_settings( $tags, $context = false ) {
    if( $context != 'post' ) return $tags;

    if ( empty($tags['form']) ) {
      $tags['form'] = array();
    }

    $tags['form']['action'] = true;
    $tags['form']['class'] = true;
    $tags['form']['id'] = true;
    $tags['form']['method'] = true;

    if ( empty($tags['iframe']) ) {
      $tags['iframe'] = array();
    }

    $tags['iframe']['class'] = true;
    $tags['iframe']['frameborder'] = true;
    $tags['iframe']['data-*'] = true;
    $tags['iframe']['height'] = true;
    $tags['iframe']['id'] = true;
    $tags['iframe']['src'] = true;
    $tags['iframe']['style'] = true;
    $tags['iframe']['width'] = true;

    if ( empty($tags['input']) ) {
      $tags['input'] = array();
    }

    $tags['input']['class'] = true;
    $tags['input']['id'] = true;
    $tags['input']['name'] = true;
    $tags['input']['onclick'] = true;
    $tags['input']['placeholder'] = true;
    $tags['input']['type'] = true;
    $tags['input']['value'] = true;

    return $tags;
  }
}

function fv_wp_flowplayer_save_post( $post_id ) {
  if( $parent_id = wp_is_post_revision($post_id) ) {
    $post_id = $parent_id;
  }

  global $post;
  $post_id = ( isset($post->ID) ) ? $post->ID : $post_id;

  global $fv_fp, $post, $FV_Player_Checker;
  if( !$FV_Player_Checker->is_cron && $FV_Player_Checker->queue_check($post_id) ) {
    //return;
  }

  $saved_post = get_post($post_id);
  if ( ! $saved_post ) {
    return;
  }

  $videos = FV_Player_Checker::get_videos($saved_post->ID);

  $iDone = 0;
  if( is_array($videos) && count($videos) > 0 ) {
    $tStart = microtime(true);
    foreach( $videos AS $video ) {
      if( microtime(true) - $tStart > apply_filters( 'fv_flowplayer_checker_save_post_time', 5 ) ) {
        FV_Player_Checker::queue_add($post_id);
        break;
      }

      if( isset($post->ID) && !get_post_meta( $post->ID, flowplayer::get_video_key($video), true ) ) {

        if ( $FV_Player_Checker->check_mimetype( array( $video ), array( 'meta_action' => 'check_time' ) ) ) {
          $iDone++;

        } else {
          FV_Player_Checker::queue_add($post_id);
        }

      } else {
        $iDone++;
      }

    }
  }

  if( !$videos || $iDone == count($videos) ) {
    FV_Player_Checker::queue_remove($post_id);
  }
}
