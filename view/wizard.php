<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/*  FV Player - HTML5 video player with Flash fallback
    Copyright (C) 2016  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

  global $fv_wp_flowplayer_ver, $fv_fp;
  global $post;
  $post_id = isset($post->ID) ? $post->ID : 0;

  $script_fv_player_editor_defaults = array();

  $script_fv_player_editor_fields = array();
  $script_fv_player_editor_fields_with_language = array();

  $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );

  function fv_flowplayer_admin_select_popups() {
    $aPopupData = get_option('fv_player_popups');

    $aPopups = array(
      array( '' , 'Use site default' ),
      array( 'no' , 'None' ),
      array( 'random' , 'Random')
    );

    if( !empty($aPopupData) && is_array($aPopupData) ) {
      foreach( $aPopupData AS $key => $aPopupAd ) {
        $value = !empty($aPopupAd['name']) ? $aPopupAd['name'] : 'Popup - ' . $key;

        if( $aPopupAd['disabled'] == 1 ) $value .= ' (currently disabled)';

        $aPopups[] = array( $key , $value );
      }
    }

    return $aPopups;
  }

  function fv_player_email_lists() {
    $rawLists = get_option('fv_player_email_lists');
    $aLists = array();

    foreach($rawLists as $key => $val) {
      if(!is_numeric($key)) continue;

      $aLists[] = array( $key , (empty($val->name) ? "List " . $key : "$val->name" ) );
    }

    return $aLists;
  }

  function fv_player_shortcode_row( $args ) {
    $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );
    $args = wp_parse_args( $args, array(
                          'class' => false,
                          'dropdown' => array( 'Default', 'On', 'Off' ),
                          'id' => false,
                          'label' => '',
                          'name' => '',
                          'playlist_label' => false,
                         ) );
    extract($args);

    $class .= !isset($fv_flowplayer_conf["interface"][$name]) || $fv_flowplayer_conf["interface"][$name] !== 'true' ? ' fv_player_interface_hide' : '';
    ?>
      <tr<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; echo $class ? ' class="' . esc_attr( $class ) . '"' : ''; ?>>
        <th scope="row" class="label">
          <label for="fv_wp_flowplayer_field_<?php echo esc_attr( $name ); ?>" class="alignright" <?php echo $playlist_label ? ' data-playlist-label="' . esc_attr( $playlist_label ) . '"  data-single-label="' . esc_attr( $label ) . '"' : ''; ?>>
            <?php echo wp_strip_all_tags( $label ); ?>
          </label>
        </th>
        <td class="field">
          <select id="fv_wp_flowplayer_field_<?php echo esc_attr( $name ); ?>" name="fv_wp_flowplayer_field_<?php echo esc_attr( $name ); ?>">
            <?php foreach( $dropdown AS $option ) : ?>
              <?php if( is_array($option) ) : ?>
                <option value="<?php echo esc_attr( $option[0] ); ?>"><?php echo wp_strip_all_tags( $option[1] ); ?></option>
              <?php else : ?>
                <option><?php echo wp_strip_all_tags( $option ); ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
    <?php
  }

  // TODO: How to actually assign any action to this?
  function fv_player_editor_button( $args ) {
    extract($args);
    ?>
  <div<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="components-base-control__field">
    <a class="components-button is-secondary" id="fv_wp_flowplayer_field_<?php echo esc_attr( $name ); ?>"><?php echo wp_strip_all_tags( $label ); ?></a>
  </div>
    <?php
  }

  function fv_player_editor_checkbox( $args ) {
    extract($args);
    ?>
  <div<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="components-base-control__field">
    <span class="components-form-toggle<?php /*if( $default ) echo ' is-checked';*/ ?>">
      <input class="components-form-toggle__input<?php if( $no_data ) echo ' no-data'; ?>" type="checkbox" aria-describedby="inspector-toggle-control-0__help"  id="fv_wp_flowplayer_field_<?php echo esc_html( $name ); ?>" name="fv_wp_flowplayer_field_<?php echo esc_html( $name ); ?>" />
      <span class="components-form-toggle__track"></span>
      <span class="components-form-toggle__thumb"></span>
    </span>
    <label for="inspector-toggle-control-0" class="components-toggle-control__label"><?php echo wp_strip_all_tags( $label ); ?></label>
  </div>
    <?php
  }

  function fv_player_editor_numfield( $args ) {
    extract($args);

    $field_id = 'fv_wp_flowplayer_field_' . esc_attr( $name );
    ?>
  <div<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="components-base-control">
    <label class="components-base-control__label" for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
    <div class="components-base-control__field">
      <input class="components-text-control__input" type="number" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>" />
    </div>
  </div>
    <?php
  }

  function fv_player_editor_select( $args ) {
    extract($args);

    $field_id = 'fv_wp_flowplayer_field_' . esc_attr( $name );
    ?>
  <div<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="components-base-control__field">
    <div class="components-flex components-select-control">
      <div class="components-flex__item">
        <label for="<?php echo esc_attr( $field_id ); ?>" class="components-input-control__label"><?php echo esc_html( $label ); ?></label>
      </div>
      <div class="components-input-control__container">
        <select class="components-select-control__input" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>" <?php if( $multiple ) echo 'multiple'; ?>>
          <?php foreach( $options AS $option ) : ?>
            <?php if( is_array($option) ) : ?>
              <option value="<?php echo esc_attr( $option[0] ); ?>"><?php echo esc_html( $option[1] ); ?></option>
            <?php else : ?>
              <option><?php echo esc_html( $option ); ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>

      </div>
    </div>
  </div>
    <?php
  }

  function fv_player_editor_textarea( $args ) {
    extract($args);

    $field_id = 'fv_wp_flowplayer_field_' . esc_attr( $name );
    ?>
  <div<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="components-base-control">
    <label class="components-base-control__label" for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
    <div class="components-base-control__field">
      <textarea class="components-textarea-control__input" type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>" rows="4"></textarea>
    </div>
  </div>
    <?php
  }

  function fv_player_editor_textfield( $args ) {
    extract($args);

    $field_id = 'fv_wp_flowplayer_field_' . esc_attr( $name );
    ?>
  <div<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="components-base-control">
    <label class="components-base-control__label" for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
    <div class="components-base-control__field">
      <?php if( $language ): ?>
        <div class="field-with-language">
          <?php // We use a simple input field here, but it will be upgraded to a select box with all the languages in JavaScript ?>
          <input type="text" class="<?php echo esc_attr( $field_id ); ?>_lang" name="<?php echo esc_attr( $field_id ); ?>_lang" />
      <?php endif; ?>

      <?php if($browser):?>
        <div class="fv_player_editor_url_shortened" id="<?php echo "fv_player_editor_url_field_" . esc_attr( $name ) ; ?>">
          <span class="link-preview"></span>
          <span class="dashicons dashicons-edit"></span>
        </div>
      <?php endif; ?>

      <input class="<?php if($browser) echo "fv_player_interface_hide fv_player_editor_url_field "; ?>components-text-control__input" type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>" />

      <?php if( $language ) : ?>
        </div><!-- /.field-with-language-->
      <?php endif; ?>

      <?php if( $browser ) : ?>
        <a class="components-button is-secondary add_media" href="#" data-target="<?php echo esc_attr( $field_id ); ?>"><?php esc_html_e('Add from media library', 'fv_flowplayer'); ?></a>
      <?php endif; ?>

      <?php if( $language ) : ?>
        <a class="remove_language" href="#" data-field_name="<?php echo esc_attr( $name ); ?>" data-field_label="<?php echo $label_signular ? esc_attr( $label_signular ) : esc_attr( $label ); ?>"><span class="dashicons dashicons-trash"></span></a>
      <?php endif; ?>
    </div>

    <?php if( $language ) : ?>
      <a class="components-button is-secondary add_language" href="#" data-field_name="<?php echo esc_attr( $name ); ?>"><?php esc_html_e('Add Another Language', 'fv_flowplayer'); ?></a>
    <?php endif; ?>

  </div>
    <?php
  }

  function fv_player_editor_hidden( $args ) {
    extract($args);

    if( $id ) {
      $field_id = $id;
    } else {
      $field_id = 'fv_wp_flowplayer_field_' . esc_attr( $name );
    }

    ?>
     <input type="hidden" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_id ); ?>"/>
    <?php
  }

  function fv_player_editor_notice_info( $args ) {
    extract($args);

    $field_class = 'fv_wp_flowplayer_field_' . esc_attr( $name );

    ?>
  <div<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="components-base-control">
    <div class="components-base-control__field">
      <div class="<?php echo esc_attr( $field_class ); ?>">
        <?php echo $content; // TODO: use wp_kses() allowing <select> and <option> ?>
      </div>
    </div>
  </div>
    <?php
  }

  function fv_player_editor_input( $args, $is_child = false ) {
    global $script_fv_player_editor_fields, $script_fv_player_editor_fields_with_language;

    $args = wp_parse_args(
      $args,
      array(
        'browser'           => false,
        'children'          => false,
        'class'             => false,
        'content'           => false,
        'default'           => false,
        'dependencies'      => false,
        'dropdown'          => array( 'Default', 'On', 'Off' ),
        'id'                => false,
        'label'             => '',
        'label_signular'    => '',
        'multiple'          => false, // applies to type = select
        'name'              => '',
        'no_data'           => false, // do not save any data based on this input
        'options'           => array(),
        'playlist_label'    => false,
        'scope'             => false,
        'language'          => false,
        'type'              => false,
        'player_meta'       => false,
        'video_meta'        => false,
        'visible'           => false,
        'width'             => false,
      )
    );

    extract($args);

    if ( ! isset( $script_fv_player_editor_fields[ $name ] ) ) {
      $script_fv_player_editor_fields[ $name ] = array();
    }

    if ( $player_meta ) {
      $script_fv_player_editor_fields[ $name ]['store'] = 'player_meta';
    }

    if ( $video_meta ) {
      $script_fv_player_editor_fields[ $name ]['store'] = 'video_meta';
    }

    // This tells JavaScript to upgrade the text input to a select box with all the languages
    if ( $language ) {
      $script_fv_player_editor_fields_with_language[] = $name;
    }

    if( !empty($dependencies) ) {
      global $script_fv_player_editor_dependencies;

      foreach( $dependencies AS $parent => $value ) {
        if( empty($script_fv_player_editor_dependencies[$parent]) ) {
          $script_fv_player_editor_dependencies[$parent] = array();
        }
        if( empty($script_fv_player_editor_dependencies[$parent][$value]) ) {
          $script_fv_player_editor_dependencies[$parent][$value] = array();
        }
        $script_fv_player_editor_dependencies[$parent][$value][] = $name;
      }
    }



    if( !$visible ) {
      $class .= ' fv_player_interface_hide';
    }

    if ( $width && in_array( $width, array( 'half' ) ) ) {
      $class .= ' fv-player-editor-field-' . esc_attr( $width );
    }

    if( $scope == 'playlist' ) {
      $class .= ' hide-if-singular';
    }

    // TODO: Not needed?
    $playlist_label = $playlist_label ? ' data-playlist-label="' . esc_attr( $playlist_label ) . '"  data-single-label="' . esc_attr( $label ) . '"' : '';

    if( !$is_child ) {
      $class .= ' components-base-control';
    }

    if( $children ) {
      $class .= ' fv-player-editor-children-wrap';
    }

    ?>
    <div class="fv-player-editor-field-wrap-<?php echo esc_attr( $name ); ?><?php echo esc_attr( $class ); ?>">
      <?php

      if( !$type || $type == 'checkbox' ) {
        fv_player_editor_checkbox( $args );
      } else if( $type == 'text' ) {
        fv_player_editor_textfield( $args );
      } else if( $type == 'number' ) {
        fv_player_editor_numfield( $args );
      } else if( $type == 'select' ) {
        fv_player_editor_select( $args );
      } else if( $type == 'button' ) {
        fv_player_editor_button( $args );
      } else if( $type == 'textarea' ) {
        fv_player_editor_textarea( $args );
      } else if( $type == 'hidden' ) {
        fv_player_editor_hidden( $args );
      } else if( $type == 'notice_info' ) {
        fv_player_editor_notice_info( $args );
      }

      if( !empty($args['description']) ) : ?>
        <p class="components-form-token-field__help"><?php echo wp_kses_post( $args['description'] ); ?></p>
      <?php endif;

      if( $children ) : ?>
        <div class="fv-player-editor-field-children-<?php echo esc_attr( $name ); ?>" style="display: none">
          <?php
          foreach( $children AS $child_input ) {
            fv_player_editor_input( $child_input, true );
          }
          ?>
        </div>
      <?php endif; ?>
    </div>
    <?php
  }

  function fv_player_editor_input_group( $settings ) {
    global $script_fv_player_editor_defaults;

    // Check if the field is enabled in Post Interface Options
    $conf = get_option( 'fvwpflowplayer' );

    foreach( $settings AS $group => $group_options ) {
      $group_options = wp_parse_args( $group_options, array(
        'sort' => true,
      ) );

      // Determine if each field should be shown
      // and if there is anything to show at all in this group
      $have_visible_setting = false;
      foreach( $group_options['items'] AS $k => $v ) {
        if( isset($conf["interface"][$v['name']]) &&
        $conf["interface"][$v['name']] == 'true' ) {
          $group_options['items'][$k]['visible'] = true;
          $have_visible_setting = true;
        }

        if( !empty($v['visible']) ) {
          $have_visible_setting = true;
        }
      }

      $class = $have_visible_setting ? 'is-open' : 'fv_player_interface_hide';

      echo "<div class='fv_player_editor-panel__body fv-player-editor-options-" . esc_attr( $group ) . " " . esc_attr( $class ) . "'>\n";

      if( !empty($group_options['label']) ) {
        echo "<h2 class='fv_player_editor-panel__body-title'><button type='button' aria-expanded='true' class='components-button fv_player_editor-panel__body-toggle'>" . esc_html( $group_options['label'] ) . "</button></h2>\n";
      }

      if( $group_options['sort'] ) {
        usort( $group_options['items'], 'fv_player_editor_input_sort' );
      }

      echo "<div class='fv-fv_player_editor-panel__body-content'>\n";

      foreach( $group_options['items'] AS $input ) {
        if( isset($input['default']) ) {
          $script_fv_player_editor_defaults[$input['name']] = $input['default'];
        }

        fv_player_editor_input( $input );

      }

      echo "</div><!-- .fv-fv_player_editor-panel__body-content -->\n";
      echo "</div><!-- .fv_player_editor-panel__body -->\n";
    }
  }

  // Sort inputs alphabetically, but the one with sticky always wins
  function fv_player_editor_input_sort( $a, $b ) {
    if( !empty($a["sticky"]) ) {
      return -1;
    }
    return strnatcasecmp($a["label"], $b["label"]);
  }

	$fv_flowplayer_helper_tag = ( is_plugin_active('jetpack/jetpack.php') ) ? 'b' : 'span';
?>

<script>
var fvwpflowplayer_helper_tag = '<?php echo esc_html( $fv_flowplayer_helper_tag ) ?>';
var fv_wp_flowplayer_re_edit = /\[[^\]]*?<<?php echo esc_html( $fv_flowplayer_helper_tag ); ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo esc_html( $fv_flowplayer_helper_tag ); ?>>.*?[^\\]\]/mi;
var fv_wp_flowplayer_re_insert = /<<?php echo esc_html( $fv_flowplayer_helper_tag ); ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo esc_html( $fv_flowplayer_helper_tag ); ?>>/gi;
var fv_flowplayer_set_post_thumbnail_id = <?php echo intval( $post_id ); ?>;
var fv_flowplayer_set_post_thumbnail_nonce = '<?php echo wp_create_nonce( "set_post_thumbnail-" . intval( $post_id ) ); ?>';
var fv_flowplayer_preview_nonce = '<?php echo wp_create_nonce( "fv-player-preview" ); ?>';
var fv_Player_site_base = '<?php echo home_url('/') ?>';
</script>

<div id="fv-player-editor-backdrop" style="display: none">
</div>
<div id="fv-player-editor-modal" style="display: none">

  <div id="fv-player-shortcode-editor"<?php if( did_action('elementor/editor/wp_head') ) echo ' class="wp-core-ui"'; // when using Elementor we need to add this class to ensure proper button styling ?> style="display: none">

    <input type="hidden" id="fv_wp_flowplayer_field_post_id" name="fv_wp_flowplayer_field_post_id" value="<?php echo get_the_ID(); ?>" />

    <div id="fv-player-editor-loading-overlay" class="fv-player-editor-overlay">
    </div>

    <div id="fv-player-editor-message-overlay" class="fv-player-editor-overlay">
      <p></p>
    </div>

    <div id="fv-player-editor-copy_player-overlay" class="fv-player-editor-overlay media-frame hide-menu">
      <div class="wp-core-ui media-frame-content attachments-browser">
        <div class="media-toolbar">
          <div class="media-toolbar-primary search-form">
            <input type="search" placeholder="Search videos or playlists" class="search" name="players_selector" >
          </div>
        </div>

        <ul class="attachments"></ul>

        <div class="media-sidebar">
          <div class="attachment-details" style="display: none">
            <h2>Player Details</h2>
            <div class="attachment-info">
              <div class="details">
                <div class="filename"></div>
                <div class="uploaded"></div>
              </div>
            </div>

            <h2>Videos</h2>
            <div class="videos-list"></div>

            <h2>Embedded on</h2>
            <div class="posts-list"></div>

          </div>
        </div>
      </div>
      <div class="media-frame-toolbar">
        <div class="media-toolbar">
          <div class="media-toolbar-secondary"></div>
          <div class="media-toolbar-primary search-form">
            <button type="button" class="button media-button button-primary button-large media-button-select" disabled>Choose</button>
          </div>
        </div>
      </div>
    </div>

    <div id="fv-player-editor-import-overlay" class="fv-player-editor-overlay">
      <textarea name="fv_player_import_data" id="fv_player_import_data" rows="13" placeholder="Paste your FV Player Export JSON data here"></textarea>
      <br />
      <br />
      <a id="fv-player-editor-import-overlay-import" href="#" class="button button-primary">Import player</a>
      <div class="fv-player-editor-overlay-notice"></div>
    </div>

    <div id="fv-player-editor-export-overlay" class="fv-player-editor-overlay">
      <textarea name="fv_player_copy_to_clipboard" rows="13"></textarea>
      <br />
      <br />
      <a data-fv-player-editor-export-overlay-copy href="#" class="button button-primary">Copy To Clipboard</a>
      <div class="fv-player-editor-overlay-notice"></div>
    </div>

    <div id="fv-player-editor-error_saving-overlay" class="fv-player-editor-overlay">
      <p data-error></p>
      <p>An unexpected error has occurred. Please copy the player raw data below and <a href="https://foliovision.com/support/fv-wordpress-flowplayer/bug-reports#new-post" target="_blank">submit a support ticket to Foliovision</a></p>
      <textarea name="fv_player_copy_to_clipboard" rows="15"></textarea>
      <br />
      <br />
      <a data-fv-player-editor-export-overlay-copy href="#" class="button button-primary">Copy To Clipboard</a>
      <div class="fv-player-editor-overlay-notice"></div>
    </div>

    <div id="fv-player-editor-modal-top">
      <h1>FV Player</h1>
	    <button type="button" id="fv-player-editor-modal-close">close</button>
    </div>

    <div id="fv-player-shortcode-editor-left">
      <div id="fv-player-shortcode-editor-preview">
        <div id="fv-player-shortcode-editor-preview-spinner" class="fv-player-shortcode-editor-helper"></div>
        <div id="fv-player-shortcode-editor-preview-no" class="fv-player-shortcode-editor-helper">
          <p><?php esc_html_e( 'Add your video', 'fv-player' ); ?></p>
          <div class="components-base-control__field">
            <input class="components-text-control__input" type="text" placeholder="Paste a link to a new video." name="hero-src" /> or
            <button type="button" class="browser button button-hero" style="position: relative; z-index: 1;">Choose from <?php echo get_bloginfo(); ?>'s library</button>
          </div>
          <div class="fv-player-editor-notice notice-url-format" style="display: none"><?php esc_html_e( 'This does not look like a video link.', 'fv-player' ); ?></div>
        </div>
        <div id="fv-player-shortcode-editor-preview-new-tab" class="fv-player-shortcode-editor-helper">
          <a class="button" href="" target="_blank"><?php esc_html_e( 'Playlist too long, click here for preview', 'fv-player' ); ?></a>
        </div>
        <div id="fv-player-shortcode-editor-preview-target"></div>
      </div>
    </div>
    <div id="fv-player-shortcode-editor-right">
      <input type="text" class="hide-if-playlist hide-if-singular" name="fv_wp_flowplayer_field_player_name" id="fv_wp_flowplayer_field_player_name" placeholder="Playlist name" /> <span id="player_id_top_text"></span>
      <div class="fv-player-tabs-header">
        <h2 class="fv-player-playlist-item-title nav-tab nav-tab-active"></h2>
        <h2 class="nav-tab-wrapper hide-if-no-js">
          <a href="#" class="nav-tab hide-if-singular hide-if-playlist" style="outline: 0;" data-tab="fv-player-tab-playlist"><?php esc_html_e( 'Playlist', 'fv-player' ); ?></a>
          <a href="#" class="nav-tab nav-tab-active hide-if-playlist-active" style="outline: 0;" data-tab="fv-player-tab-video-files"><?php esc_html_e( 'Video', 'fv-player' ); ?></a>
          <a href="#" class="nav-tab hide-if-playlist-active" style="outline: 0;" data-tab="fv-player-tab-subtitles"><?php esc_html_e( 'Subtitles', 'fv-player' ); ?></a>
          <a href="#" class="nav-tab hide-if-playlist" style="outline: 0;" data-tab="fv-player-tab-options"><?php esc_html_e( 'Options', 'fv-player' ); ?></a>
          <a href="#" class="nav-tab hide-if-playlist" style="outline: 0;" data-tab="fv-player-tab-actions"><?php esc_html_e( 'Actions', 'fv-player' ); ?></a>
          <a href="#" class="nav-tab" style="outline: 0;" data-tab="fv-player-tab-embeds"><?php esc_html_e( 'Embeds', 'fv-player' ); ?></a>
          <?php do_action('fv_player_shortcode_editor_tab'); ?>
        </h2>
      </div>
      <div class="fv-player-tabs">

        <div class="fv-player-tab fv-player-tab-playlist">
          <div id="fv-player-editor-playlist">
              <div class="fv-player-editor-playlist-item">
                <div class="fv-player-editor-playlist-move">
                  <span class="fv-player-editor-playlist-move-up dashicons dashicons-arrow-up-alt2"></span>
                  <span class="fv-player-editor-playlist-move-handle"></span>
                  <span class="fv-player-editor-playlist-move-down dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <a class="fvp_item_video-thumbnail"></a>
                <div class="fvp_item_video-title-wrap">
                  <span class="fvp_item_video-filename"></span>
                  <a class="configure-video" href="#">Configure video</a>
                </div>
                <input class="fvp_item_video-edit-input" type="text" style="display: none;">
                <span class="fvp_item_video-duration"></span>
                <a class="fvp_item_remove" href="#"><span class="dashicons dashicons-trash"></span></a>
              </div>
          </div>
          <div id="playlist-hero" style="display: none">
            <div class="components-base-control__field">
              <input class="components-text-control__input" type="text" placeholder="Paste a link to a new video." name="hero-src" data-playlist-hero="true" />
              <button type="button" class="browser button button-hero" style="position: relative; z-index: 1;" data-playlist-hero="true">Choose from <?php echo get_bloginfo(); ?>'s library</button>
              <div class="fv-player-editor-notice notice-url-format" style="display: none"><?php esc_html_e( 'This does not look like a video link.', 'fv-player' ); ?></div>
              <div class="fv-player-editor-notice notice-use-ui" style="display: none"><?php esc_html_e( 'Please post a link to the new video or choose one.', 'fv-player' ); ?></div>
            </div>
          </div>
          <a class="playlist_add" data-html="+" data-alt-html="&#10140;">+</a>

        </div>

        <div class="fv-player-tab fv-player-tab-video-files">
          <div class="fv-player-playlist-item" data-playlist-item data-index="0">
            <?php
            fv_player_editor_input_group( fv_player_editor_video_fields() );

            // Legacy
            echo "<div class='fv_player_editor-panel__body fv-player-editor-legacy'>\n";

              // TODO: Will these still actually work?
              do_action('fv_flowplayer_shortcode_editor_before');
              do_action('fv_flowplayer_shortcode_editor_item_after');

            echo "</div>\n";
            ?>
          </div>
        </div>

        <div class="fv-player-tab fv-player-tab-subtitles" style="display: none">
          <div class="fv-player-playlist-item" data-playlist-item data-index="0">

            <?php
            fv_player_editor_input_group( fv_player_editor_subtitle_fields() );

            // Legacy
            echo "<div class='fv_player_editor-panel__body fv-player-editor-legacy'>\n";

              // TODO: Will these still actually work?
              do_action('fv_flowplayer_shortcode_editor_subtitles_tab_prepend');
              do_action('fv_flowplayer_shortcode_editor_subtitles_tab_append');

            echo "</div>\n";
            ?>

          </div>
        </div>

        <div class="fv-player-tab fv-player-tab-options" style="display: none">
          <?php
          $player_options = apply_filters('fv_player_editor_player_options', array(
            'general' => array(
              'label' => __( 'Appearance', 'fv-player' ),
              'items' => array(
                array(
                  'label' => __( 'Autoplay', 'fv-player' ),
                  'name' => 'autoplay',
                  'description' => __( 'Video will autoplay when it\'s in browser viewport.', 'fv-player' ),
                  'default'     => in_array( $fv_fp->_get_option('autoplay_preload'), array( 'sticky', 'viewport' ) ),
                  'children' => array(
                    array(
                      'label' => __( 'Muted Autoplay', 'fv-player' ),
                      'name' => 'autoplay_muted'
                    ), // TODO: Save properly
                  )
                ),
                /*array(
                  'label' => __( 'Player Alignment', 'fv-player' ),
                  'name' => 'align',
                  'description' => __( 'Allows the article text to wrap around the player.', 'fv-player' ),
                  'children' => array(
                    array(
                      'label' => __( 'Position', 'fv-player' ),
                      'name' => 'align',
                      'options' => array(
                        'Left',
                        'Right',
                        'Centered'
                      ),
                      'type' => 'select'
                    ),
                    array(
                      'label' => __( 'Width', 'fv-player' ),
                      'name' => 'lightbox_width',
                      'type' => 'number'
                    )
                  )
                ),*/
                array(
                  'label' => __( 'Playlist Auto Advance', 'fv-player' ),
                  'name' => 'playlist_advance',
                  'default' => !$fv_fp->_get_option('playlist_advance'),
                  'scope' => 'playlist'
                ),
                array(
                  'label'        => __( 'Playlist Style', 'fv-player' ),
                  'name'         => 'playlist',
                  'dependencies' => array( 'lightbox' => false ),
                  'options'      => array(
                                      array( '',            'Default' ),
                                      array( 'horizontal',  'Horizontal' ),
                                      array( 'tabs',        'Tabland' ),
                                      array( 'prevnext',    'Big arrows (deprecated)' ),
                                      array( 'vertical',    'Vertical' ),
                                      array( 'slider',      'Scrollslider' ),
                                      array( 'season',      'Episodes' ),
                                      array( 'polaroid',    'Polaroid' ),
                                      array( 'text',        'Text' ),
                                      array( 'version-one', 'Sliderland' ),
                                      array( 'version-two', 'Sliderbar' ),
                  ),
                  'scope'        => 'playlist',
                  'type'         => 'select',
                ),
                array(
                  'label'        => __( 'Sticky video', 'fv-player' ),
                  'name'         => 'sticky',
                  'description'  => __( 'Watch the playing video when scrolling down the page.', 'fv-player' ),
                  'default'      => in_array( $fv_fp->_get_option('sticky_video'), array( 'desktop', 'all' ) ),
                  'dependencies' => array( 'lightbox' => false )
                )
              )
            ),
            'controls' => array(
              'label' => __( 'Controls', 'fv-player' ),
              'items' => array(
                array(
                  'label' => __( 'Controlbar', 'fv-player' ),
                  'name' => 'controlbar',
                  'description' => __( 'Without the controlbar seeking in video is impossible.', 'fv-player' ),
                  'default' => true,
                  'sticky' => true
                ),
                array(
                  'label'        => __( 'LMS | Teaching', 'fv-player' ),
                  'name'         => 'lms_teaching',
                  'description'  => __( 'Seeking forward not allowed if user did not see the full video.', 'fv-player' ),
                  'dependencies' => array( 'controlbar' => true )
                ),
                array(
                  'label' => __( 'Speed Buttons', 'fv-player' ),
                  'name' => 'speed',
                  'description' => __( 'Allows user to speed up or slow down the video.', 'fv-player' ),
                  'default' => $fv_fp->_get_option('ui_speed'),
                  'dependencies' => array( 'controlbar' => true )
                )
              )
            ),
            'header' => array(
              'label' => __( 'Sharing', 'fv-player' ),
              'items' => array(
                array(
                  'label' => __( 'Embedding', 'fv-player' ),
                  'name' => 'embed',
                  'description' => __( 'Allows users to embed your player on their websites.', 'fv-player' ),
                  'default' => $fv_fp->_get_option( 'ui_embed' )
                ),
                array(
                  'label' => __( 'Sharing Buttons', 'fv-player' ),
                  'name' => 'share',
                  'description' => __( 'Provides a quick way of sharing your article on Facebook, Twitter or via Email.', 'fv-player' ),
                  'default' => $fv_fp->_get_option( 'ui_sharing' )
                ), // TODO: Custom URL setting
              )
            )
          ));

          fv_player_editor_input_group( $player_options );
          ?>

          <table width="100%">
            <tr id="fv_wp_flowplayer_field_share_custom" style="display: none">
              <th scope="row" class="label"><label for="fv_wp_flowplayer_field_lightbox" class="alignright">Sharing Properties</label></th>
              <td class="field">
                <input type="text" id="fv_wp_flowplayer_field_share_url" name="fv_wp_flowplayer_field_share_url" style="width: 49%" placeholder="URL" />
                <input type="text" id="fv_wp_flowplayer_field_share_title" name="fv_wp_flowplayer_field_share_title" style="width: 49%" placeholder="Title" />
              </td>
            </tr>


            <?php do_action('fv_flowplayer_shortcode_editor_tab_options'); ?>
          </table>
        </div>

        <div class="fv-player-tab fv-player-tab-actions" style="display: none">
          <?php
          $overlay_show_after = $fv_fp->_get_option('overlay_show_after');

          $actions = array(
            'actions' => array(
              'items' => array(
                array(
                  'label' => __( 'End of Video Action', 'fv-player' ),
                  'name' => 'toggle_end_action',
                  'description' => __( 'What should happen at the end of the video.', 'fv-player' ),
                  'children' => array(
                    array(
                      'label' => __( 'Pick the action', 'fv-player' ),
                      'name' => 'end_actions',
                      'options' => array(
                        array('', 'Default'),
                        array('no', 'Nothing'),
                        array('redirect', 'Redirect'),
                        array('loop', 'Loop'),
                        array('popup', 'Show popup'),
                        array('splashend', 'Show splash screen'),
                        array('email_list', 'Collect Emails')
                      ),
                      'type' => 'select',
                      'visible' => true
                    ),
                    array(
                      'label' => __( 'Redirect', 'fv-player' ),
                      'name' => 'redirect',
                      'type' => 'text'
                    ),
                    array(
                      'label' => __( 'Popup', 'fv-player' ),
                      'name' => 'popup_id',
                      'type' => 'select',
                      'options' => fv_flowplayer_admin_select_popups()
                    ),
                    array(
                      'label' => __( 'Email list', 'fv-player' ),
                      'name' => 'email_list',
                      'type' => 'select',
                      'options' => fv_player_email_lists()
                    ),
                  ),
                  'visible' => true
                ),
                array(
                  'label' => __( 'Show Overlay', 'fv-player' ),
                  'name' => 'toggle_overlay',
                  'description' => __( 'Enter text or HTML to show on top of video while it\'s playing.', 'fv-player' ),
                  'children' => array(
                    array(
                      'label' => __( 'Overlay Code', 'fv-player' ),
                      'name' => 'overlay',
                      'description' => $overlay_show_after ? sprintf( __( 'Shows after %d seconds.', 'fv-player' ), $overlay_show_after ) : false,
                      'type' => 'textarea',
                      'visible' => true
                    ),
                    array(
                      'label' => __( 'Width', 'fv-player' ),
                      'name' => 'overlay_width',
                      'type' => 'number',
                      'visible' => true
                    ),
                    array(
                      'label' => __( 'Height', 'fv-player' ),
                      'name' => 'overlay_height',
                      'type' => 'number',
                      'visible' => true
                    )
                  ),
                  'visible' => true,
                  'dependencies' => array( 'overlay_skip' => false )
                ),
              ),
              'sort' => false
            )
          );

          if( $fv_fp->_get_option('overlay') ) {
            $actions['actions']['items'][] = array(
              'label' => __( 'Do not show global overlay', 'fv-player' ),
              'name' => 'overlay_skip',
              'description' => sprintf( __( 'Use to disable overlay set in <a href="%s" target="_blank">Actions -> Overlays</a>', 'fv-player' ), admin_url('admin.php?page=fvplayer#postbox-container-tab_actions') ),
              'visible' => true,
              'dependencies' => array( 'toggle_overlay' => false )
            );
          }

          $actions = apply_filters('fv_player_editor_actions', $actions );

          fv_player_editor_input_group( $actions );

          // Legacy
          echo "<div class='fv_player_editor-panel__body fv-player-editor-legacy'>\n";

            // TODO: Will these still actually work?
            do_action('fv_flowplayer_shortcode_editor_after');
            do_action('fv_flowplayer_shortcode_editor_tab_actions');

          echo "</div>\n";
          ?>

        </div>

        <div class="fv-player-tab fv-player-tab-embeds" style="display: none">
          <p>This page shows you where else this player is used.</p>
          <table width="100%">
            <tr>
              <th scope="row" class="label"><label for="fv_wp_flowplayer_field_embedded_on" class="alignright"><?php esc_html_e('Embedded on', 'fv_flowplayer'); ?></label></th>
              <td></td>
            </tr>
          </table>
        </div>

        <?php do_action('fv_player_shortcode_editor_tab_content'); ?>

        <div id="fv-player-editor-modal-bottom">
          <a class="button-primary fv_player_field_insert-button"><?php esc_html_e('Insert', 'fv_flowplayer'); ?></a>
          <a class="playlist_edit button hide-if-playlist-active" href="#" data-create="<?php esc_attr_e('Add another video into playlist', 'fv_flowplayer'); ?>" data-edit="<?php esc_attr_e('Back to playlist', 'fv_flowplayer'); ?>"><?php esc_html_e('Add another video into playlist', 'fv_flowplayer'); ?></a>

          <?php
          if( function_exists('get_current_screen') && current_user_can('edit_posts') ) :
            $screen = get_current_screen();
            if ( $screen->parent_base != 'fv_player' ) : ?>
              <a class="copy_player button" href="#"><?php esc_html_e( 'Pick existing player', 'fv_flowplayer' ); ?></a>
            <?php endif;
          endif; ?>

        </div>

      </div>
      <!--<div id="fv-player-tabs-debug"></div>-->
    </div>

    <div style="clear: both"></div>

    <div id="fv-player-editor-notices"></div>
  </div>
</div>

<?php
global $script_fv_player_editor_defaults, $script_fv_player_editor_dependencies, $script_fv_player_editor_fields, $script_fv_player_editor_fields_with_language;

echo "<script>var fv_player_editor_defaults = ".wp_json_encode($script_fv_player_editor_defaults)."</script>\n";
echo "<script>var fv_player_editor_dependencies = ".wp_json_encode($script_fv_player_editor_dependencies)."</script>\n";
echo "<script>var fv_player_editor_fields = ".wp_json_encode($script_fv_player_editor_fields)."</script>\n";

// This tells JavaScript to upgrade the text input to a select box with all the languages
echo "<script>var fv_player_editor_fields_with_language = ".wp_json_encode($script_fv_player_editor_fields_with_language)."</script>\n";
?>
