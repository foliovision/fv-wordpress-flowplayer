<?php
/*  FV Wordpress Flowplayer - HTML5 video player    
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
    
    if( $id ) {
      $id = ' id="'.$id.'"';
    }
    
    $class .= !isset($fv_flowplayer_conf["interface"][$name]) || $fv_flowplayer_conf["interface"][$name] !== 'true' ? ' fv_player_interface_hide' : '';
    if( $class ) {
      $class = ' class="'.$class.'"';
    }
    
    $playlist_label = $playlist_label ? ' data-playlist-label="' . __( $playlist_label, 'fv_flowplayer') . '"  data-single-label="' . __( $label, 'fv_flowplayer') . '"' : '';
    
    ?>
      <tr<?php echo $id.$class; ?>>
        <th scope="row" class="label"><label for="fv_wp_flowplayer_field_<?php echo $name; ?>" class="alignright" <?php echo $playlist_label; ?>><?php _e( $label, 'fv_flowplayer'); ?></label></th>
        <td class="field">
          <select id="fv_wp_flowplayer_field_<?php echo $name; ?>" name="fv_wp_flowplayer_field_<?php echo $name; ?>">
            <?php foreach( $dropdown AS $option ) : ?>
              <?php if( is_array($option) ) : ?>
                <option value="<?php echo $option[0]; ?>"><?php _e( $option[1], 'fv_flowplayer' ); ?></option>
              <?php else : ?>
                <option><?php _e( $option, 'fv_flowplayer' ); ?></option>
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

    if( $id ) {
      $id = ' id="'.$id.'"';
    }
    ?>
  <div <?php echo $id; ?> class="components-base-control__field">
    <a class="components-button is-secondary" id="fv_wp_flowplayer_field_<?php echo $name; ?>"><?php _e( $label, 'fv_flowplayer'); ?></a>
  </div>
    <?php
  }

  function fv_player_editor_checkbox( $args ) {
    extract($args);

    if( $id ) {
      $id = ' id="'.$id.'"';
    }
    ?>
  <div <?php echo $id; ?> class="components-base-control__field">
    <span class="components-form-toggle<?php /*if( $default ) echo ' is-checked';*/ ?>">
      <input class="components-form-toggle__input<?php if( $no_data ) echo ' no-data'; ?>" type="checkbox" aria-describedby="inspector-toggle-control-0__help"  id="fv_wp_flowplayer_field_<?php echo $name; ?>" name="fv_wp_flowplayer_field_<?php echo $name; ?>" />
      <span class="components-form-toggle__track"></span>
      <span class="components-form-toggle__thumb"></span>
    </span>
    <label for="inspector-toggle-control-0" class="components-toggle-control__label"><?php _e( $label, 'fv_flowplayer'); ?></label>
  </div>
    <?php
  }

  function fv_player_editor_numfield( $args ) {
    extract($args);

    if( $id ) {
      $id = ' id="'.$id.'"';
    }

    $field_id = esc_attr('fv_wp_flowplayer_field_'.$name);
    ?>
  <div <?php echo $id; ?> class="components-base-control">
    <label class="components-base-control__label" for="<?php echo $field_id; ?>"><?php echo $label; ?></label>
    <div class="components-base-control__field">
      <input class="components-text-control__input" type="number" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" />
    </div>
  </div>
    <?php
  }

  function fv_player_editor_select( $args ) {
    extract($args);

    if( $id ) {
      $id = ' id="'.$id.'"';
    }

    $field_id = esc_attr('fv_wp_flowplayer_field_'.$name);
    ?>
  <div <?php echo $id; ?> class="components-base-control__field">
    <div class="components-flex components-select-control">
      <div class="components-flex__item">
        <label for="<?php echo $field_id; ?>" class="components-input-control__label"><?php echo $label; ?></label>
      </div>
      <div class="components-input-control__container">
        <select class="components-select-control__input" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>">
          <?php foreach( $options AS $option ) : ?>
            <?php if( is_array($option) ) : ?>
              <option value="<?php echo $option[0]; ?>"><?php echo $option[1]; ?></option>
            <?php else : ?>
              <option><?php echo $option; ?></option>
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

    if( $id ) {
      $id = ' id="'.$id.'"';
    }

    $field_id = esc_attr('fv_wp_flowplayer_field_'.$name);
    ?>
  <div <?php echo $id; ?> class="components-base-control">
    <label class="components-base-control__label" for="<?php echo $field_id; ?>"><?php echo $label; ?></label>
    <div class="components-base-control__field">
      <textarea class="components-textarea-control__input" type="text" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" rows="4"></textarea>
    </div>
  </div>
    <?php
  }

  function fv_player_editor_textfield( $args ) {
    extract($args);

    if( $id ) {
      $id = ' id="'.$id.'"';
    }

    $field_id = esc_attr('fv_wp_flowplayer_field_'.$name);
    ?>
  <div <?php echo $id; ?> class="components-base-control">
    <label class="components-base-control__label" for="<?php echo $field_id; ?>"><?php echo $label; ?></label>
    <div class="components-base-control__field">
      <?php if( $subtitle_language ): ?>
        <div class="field-with-language">
          <select class="fv_wp_flowplayer_field_subtitles_lang" name="fv_wp_flowplayer_field_subtitles_lang">
            <option value=""><?php _e('Pick language', 'fv_flowplayer'); ?></option>
            <?php
            $aLanguages = flowplayer::get_languages();
            $aCurrent = explode('-', get_bloginfo('language'));
            $sCurrent = ''; //aCurrent[0];
            foreach ($aLanguages AS $sCode => $sLabel) {
              ?><option value="<?php echo strtolower($sCode); ?>"<?php if (strtolower($sCode) == $sCurrent) echo ' selected'; ?>><?php echo $sCode; ?>&nbsp;&nbsp;(<?php echo $sLabel; ?>)</option>
              <?php
            }
            ?>
          </select>
      <?php endif; ?>
    
      <?php if($browser):?>
        <div class="fv_player_editor_url_shortened" id="<?php echo "fv_player_editor_url_field_" . $name ; ?>">
          <span class="link-preview"></span>
          <span class="dashicons dashicons-edit"></span>
        </div>
      <?php endif; ?>

      <input class="<?php if($browser) echo "fv_player_interface_hide fv_player_editor_url_field "; ?>components-text-control__input" type="text" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" />

      <?php if( $subtitle_language ) : ?>
        </div><!-- /.field-with-language-->
      <?php endif; ?>
      
      <?php if( $browser ) : ?>
        <a class="components-button is-secondary add_media" href="#" data-target="<?php echo $field_id; ?>"><?php _e('Add from media library', 'fv_flowplayer'); ?></a>
      <?php endif; ?>
    </div>
  </div>
    <?php
  }

  function fv_player_editor_hidden( $args ) {
    extract($args);

    if( $id ) {
      $field_id = $id;
    } else {
      $field_id = esc_attr('fv_wp_flowplayer_field_'.$name);
    }

    ?>
     <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>"/>
    <?php
  }

  function fv_player_editor_notice_info( $args ) {
    extract($args);

    if( $id ) {
      $id = ' id="'.$id.'"';
    }

    $field_class = esc_attr('fv_wp_flowplayer_field_'.$name);

    ?>
  <div <?php echo $id; ?> class="components-base-control">
    <div class="components-base-control__field">
      <ul class="<?php echo $field_class; ?>"></ul>
    </div>
  </div>
    <?php
  }

  function fv_player_editor_input( $args, $is_child = false ) {
    $args = wp_parse_args( $args, array(
                          'browser' => false,
                          'children' => false,
                          'class' => false,
                          'default' => false,
                          'dependencies' => false,
                          'dropdown' => array( 'Default', 'On', 'Off' ),
                          'id' => false,
                          'label' => '',
                          'name' => '',
                          'no_data' => false, // do not save any data based on this input
                          'options' => array(),
                          'playlist_label' => false,
                          'scope' => false,
                          'subtitle_language' => false,
                          'type' => false,
                          'visible' => false,
                         ) );

    extract($args);

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

    if( $scope == 'playlist' ) {
      $class .= ' hide-if-singular';
    }

    $playlist_label = $playlist_label ? ' data-playlist-label="' . __( $playlist_label, 'fv_flowplayer') . '"  data-single-label="' . __( $label, 'fv_flowplayer') . '"' : '';

    if( !$is_child ) {
      $class .= ' components-base-control';
    }

    if( $children ) {
      $class .= ' fv-player-editor-children-wrap';
    }

    ?>
    <div class="fv-player-editor-field-wrap-<?php echo $name; ?><?php echo $class; ?>">
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
        <p class="components-form-token-field__help"><?php echo $args['description']; ?></p>
      <?php endif;
    
      if( $children ) : ?>
        <div class="fv-player-editor-field-children-<?php echo $name; ?>" style="display: none">
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

      echo "<div class='components-panel__body fv-player-editor-options-".$group." ".$class."'>\n";
      
      if( !empty($group_options['label']) ) {
        echo "<h2 class='components-panel__body-title'><button type='button' aria-expanded='true' class='components-button components-panel__body-toggle'>".$group_options['label']."</button></h2>\n";
      }

      if( $group_options['sort'] ) {
        usort( $group_options['items'], 'fv_player_editor_input_sort' );
      }

      echo "<div class='fv-components-panel__body-content'>\n";
      
      foreach( $group_options['items'] AS $input ) {
        if( isset($input['default']) ) {
          $script_fv_player_editor_defaults[$input['name']] = $input['default'];
        }

        fv_player_editor_input( $input );

      }

      echo "</div><!-- .fv-components-panel__body-content -->\n";
      echo "</div><!-- .components-panel__body -->\n";
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
var fvwpflowplayer_helper_tag = '<?php echo $fv_flowplayer_helper_tag ?>';
var fv_wp_flowplayer_re_edit = /\[[^\]]*?<<?php echo $fv_flowplayer_helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $fv_flowplayer_helper_tag; ?>>.*?[^\\]\]/mi;
var fv_wp_flowplayer_re_insert = /<<?php echo $fv_flowplayer_helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $fv_flowplayer_helper_tag; ?>>/gi;
var fv_flowplayer_set_post_thumbnail_id = <?php echo $post_id; ?>;
var fv_flowplayer_set_post_thumbnail_nonce = '<?php echo wp_create_nonce( "set_post_thumbnail-$post_id" ); ?>';
var fv_flowplayer_preview_nonce = '<?php echo wp_create_nonce( "fv-player-preview-".get_current_user_id() ); ?>';
var fv_Player_site_base = '<?php echo home_url('/') ?>';
</script>

<div id="fv-player-editor-backdrop" style="display: none">
</div>
<div id="fv-player-editor-modal" style="display: none">

  <div id="fv-player-shortcode-editor"<?php if( did_action('elementor/editor/wp_head') ) echo ' class="wp-core-ui"'; // when using Elementor we need to add this class to ensure proper button styling ?>>

    <input type="hidden" id="fv_wp_flowplayer_field_post_id" name="fv_wp_flowplayer_field_post_id" value="<?php echo get_the_ID(); ?>" />

    <div id="fv-player-editor-loading-overlay" class="fv-player-editor-overlay">
    </div>
    
    <div id="fv-player-editor-message-overlay" class="fv-player-editor-overlay">
      <p></p>
      <a data-fv-player-editor-overlay-close href="#" class="button button-primary">Close</a>
    </div>
    
    <div id="fv-player-editor-copy_player-overlay" class="fv-player-editor-overlay">
      <select name="players_selector" id="players_selector">
        <option hidden disabled selected value>Choose a Player...</option>
      </select>
      
      <a data-fv-player-editor-overlay-close href="#" class="button">Close</a>
    </div>
    
    <div id="fv-player-editor-import-overlay" class="fv-player-editor-overlay">
      <textarea name="fv_player_import_data" id="fv_player_import_data" rows="13" placeholder="Paste your FV Player Export JSON data here"></textarea>
      <br />
      <br />
      <a id="fv-player-editor-import-overlay-import" href="#" class="button button-primary">Import player</a>
      <a data-fv-player-editor-overlay-close href="#" class="button">Close</a>
      <div class="fv-player-editor-overlay-notice"></div>
    </div>
    
    <div id="fv-player-editor-export-overlay" class="fv-player-editor-overlay">
      <textarea name="fv_player_copy_to_clipboard" rows="13"></textarea>
      <br />
      <br />
      <a data-fv-player-editor-export-overlay-copy href="#" class="button button-primary">Copy To Clipboard</a>
      <a data-fv-player-editor-overlay-close href="#" class="button">Close</a>
      <div class="fv-player-editor-overlay-notice"></div>
    </div>
    
    <div id="fv-player-editor-error_saving-overlay" class="fv-player-editor-overlay">
      <p data-error></p>
      <p>An unexpected error has occurred. Please copy the player raw data below and <a href="https://foliovision.com/support/fv-wordpress-flowplayer/bug-reports#new-post" target="_blank">submit a support ticket to Foliovision</a></p>
      <textarea name="fv_player_copy_to_clipboard" rows="15"></textarea>
      <br />
      <br />
      <a data-fv-player-editor-export-overlay-copy href="#" class="button button-primary">Copy To Clipboard</a>
      <a data-fv-player-editor-overlay-close href="#" class="button button-primary">Close</a>
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
          <h1><?php _e('Add your video', 'fv-wordpress-flowplayer'); ?></h1>
		  <p><?php _e('Add your video from the media gallery or use the video tab to enter your URL.', 'fv-wordpress-flowplayer'); ?></p>
		  <button type="button" class="browser button button-hero"style="position: relative; z-index: 1;">Select File</button>
        </div>
        <div id="fv-player-shortcode-editor-preview-new-tab" class="fv-player-shortcode-editor-helper">
          <a class="button" href="" target="_blank"><?php _e('Playlist too long, click here for preview', 'fv-wordpress-flowplayer'); ?></a>
        </div>
        <div id="fv-player-shortcode-editor-preview-target"></div>
      </div>
    </div>
    <div id="fv-player-shortcode-editor-right">
      <input type="text" class="hide-if-playlist hide-if-singular" name="fv_wp_flowplayer_field_player_name" id="fv_wp_flowplayer_field_player_name" placeholder="Playlist name" /> <span id="player_id_top_text"></span>
      <div class="fv-player-tabs-header">
        <h2 class="fv-player-playlist-item-title nav-tab nav-tab-active"></h2>
        <h2 class="nav-tab-wrapper hide-if-no-js">
          <a href="#" class="nav-tab hide-if-singular hide-if-playlist" style="outline: 0;" data-tab="fv-player-tab-playlist"><?php _e('Playlist', 'fv-wordpress-flowplayer'); ?></a>
          <a href="#" class="nav-tab nav-tab-active hide-if-playlist-active" style="outline: 0;" data-tab="fv-player-tab-video-files"><?php _e('Video', 'fv-wordpress-flowplayer'); ?></a>
          <a href="#" class="nav-tab hide-if-playlist-active" style="outline: 0;" data-tab="fv-player-tab-subtitles"><?php _e('Subtitles', 'fv-wordpress-flowplayer'); ?></a>
          <a href="#" class="nav-tab hide-if-playlist" style="outline: 0;" data-tab="fv-player-tab-options"><?php _e('Options', 'fv-wordpress-flowplayer'); ?></a>
          <a href="#" class="nav-tab hide-if-playlist" style="outline: 0;" data-tab="fv-player-tab-actions"><?php _e('Actions', 'fv-wordpress-flowplayer'); ?></a>
          <a href="#" class="nav-tab" style="outline: 0;" data-tab="fv-player-tab-embeds"><?php _e('Embeds', 'fv-wordpress-flowplayer'); ?></a>
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
          <a class="playlist_add">+</a>

        </div>

        <div class="fv-player-tab fv-player-tab-video-files">
          <div class="fv-player-playlist-item" data-playlist-item data-index="0">
            <?php
            $video_fields = apply_filters('fv_player_editor_video_fields', array(
              'video' => array(
                'items' => array(
                  array(
                    'label' => __('Video Link', 'fv-wordpress-flowplayer'),
                    'name' => 'src',
                    'browser' => true,
                    'type' => 'text',
                    'visible' => true
                  ),
                  array(
                    'name' => 'video_info',
                    'type' => 'notice_info'
                  ),
                  array(
                    'name' => 'auto_splash',
                    'type' => 'hidden',
                  ),
                  array(
                    'name' => 'auto_caption',
                    'type' => 'hidden',
                  ),
                  array(
                    'name' => 'encoding_job_id',
                    'type' => 'hidden'
                  ),
                  // TODO: Actually make these live
                  array(
                    'label' => __('Live Stream', 'fv-wordpress-flowplayer'),
                    'name' => 'live',
                  ),
                  array(
                    'label' => __('DVR Stream', 'fv-wordpress-flowplayer'),
                    'name' => 'dvr',
                  ),
                  array(
                    'label' => __('Audio Stream', 'fv-wordpress-flowplayer'),
                    'name' => 'audio',
                  ),
                  array(
                    'label' => __('Advanced Settings', 'fv-wordpress-flowplayer'),
                    'name' => 'advanced-settings',
                    'no_data' => true,
                    'visible' => true,
                    'children' => array(
                      array(
                        'label' => __('Mobile Video', 'fv-wordpress-flowplayer'),
                        'name' => 'mobile',
                        'browser' => true,
                        'type' => 'text',
                        'visible' => isset($fv_flowplayer_conf["interface"]["mobile"]) && $fv_flowplayer_conf["interface"]["mobile"] == 'true',
                      ),                      
                      array(
                        'label' => __('Alternative Format 1', 'fv-wordpress-flowplayer'),
                        'name' => 'src1',
                        'browser' => true,
                        'type' => 'text',
                        'visible' => true
                      ),
                      array(
                        'label' => __('Alternative Format 2', 'fv-wordpress-flowplayer'),
                        'name' => 'src2',
                        'browser' => true,
                        'type' => 'text',
                        'visible' => true
                      ),
                      array(
                        'label' => __('RTMP', 'fv-wordpress-flowplayer'),
                        'name' => 'rtmp_show',
                        'no_data' => true,
                        'visible' => false,
                        'children' => array(
                          array(
                            'label' => __('Path', 'fv-wordpress-flowplayer'),
                            'name' => 'rtmp_path',
                            'type' => 'text',
                            'visible' => true
                          ),
                          array(
                            'label' => __('Server', 'fv-wordpress-flowplayer'),
                            'name' => 'rtmp',
                            'type' => 'text',
                            'visible' => true
                          ),
                        )
                      ),
                    ),
                  ),
                  array(
                    'label' => __('Splash Screen', 'fv-wordpress-flowplayer'),
                    'name' => 'splash',
                    'browser' => true,
                    'type' => 'text',
                    'visible' => true,
                    'description' => __('Will appear in place of the video before it plays.', 'fv-wordpress-flowplayer'),
                  ),
                  array(
                    'name' => 'splash_attachment_id',
                    'type' => 'hidden',
                  ),
                  array(
                    'label' => __('Title', 'fv-wordpress-flowplayer'),
                    'name' => 'caption',
                    'type' => 'text',
                    'visible' => isset($fv_flowplayer_conf["interface"]["playlist_captions"]) && $fv_flowplayer_conf["interface"]["playlist_captions"] == 'true',
                    'description' => __('Will appear below the player and on playlist thumbnails. Also used for tracking.', 'fv-wordpress-flowplayer'),
                  ),
                  array(
                    'label' => __('Splash Text', 'fv-wordpress-flowplayer'),
                    'name' => 'splash_text',
                    'type' => 'text',
                    'visible' => isset($fv_flowplayer_conf["interface"]["splash_text"]) && $fv_flowplayer_conf["interface"]["splash_text"] == 'true',
                    'description' => __('Will appear over the video before it plays.', 'fv-wordpress-flowplayer'),
                  ),
                  array(
                    'label' => __('Synopsis', 'fv-wordpress-flowplayer'),
                    'name' => 'synopsis',
                    'type' => 'textarea',
                    'visible' => isset($fv_flowplayer_conf["interface"]["synopsis"]) && $fv_flowplayer_conf["interface"]["synopsis"] == 'true',
                    'description' => __('Shows for the Vertical Season playlist style.', 'fv-wordpress-flowplayer'),
                  )
                ),
                'sort' => false
              )
            ) );

            fv_player_editor_input_group( $video_fields );

            // Legacy
            // TODO: Will these still actually work?
            do_action('fv_flowplayer_shortcode_editor_before');

            do_action('fv_flowplayer_shortcode_editor_item_after');
            ?>
          </div>
        </div>

        <div class="fv-player-tab fv-player-tab-subtitles" style="display: none">
          <div class="fv-player-playlist-item" data-playlist-item data-index="0">

            <?php
            $subtitle_fields = apply_filters('fv_player_editor_subtitle_fields', array(
              'subtitles' => array(
                'items' => array(
                  array(
                    'label' => __('Subtitles', 'fv-wordpress-flowplayer'),
                    'name' => 'subtitles',
                    'browser' => true,
                    'subtitle_language' => true,
                    'type' => 'text',
                    'visible' => true,
                  ),
                  array(
                    'label' => __('Add Another Language', 'fv-wordpress-flowplayer'),
                    'name' => 'subtitles_add', // TODO: Do not save
                    'type' => 'button',
                    'visible' => true
                  )
                ),
                'sort' => false
              )
            ) );

            fv_player_editor_input_group( $subtitle_fields );

            // Legacy
            // TODO: Will these still actually work?
            do_action('fv_flowplayer_shortcode_editor_subtitles_tab_prepend');

            do_action('fv_flowplayer_shortcode_editor_subtitles_tab_append');
            ?>

          </div>
        </div>

        <div class="fv-player-tab fv-player-tab-options" style="display: none">
          <?php
          $player_options = apply_filters('fv_player_editor_player_options', array(
            'general' => array(
              'label' => __('Appearance', 'fv-wordpress-flowplayer'),
              'items' => array(
                array(
                  'label' => __('Autoplay', 'fv-wordpress-flowplayer'),
                  'name' => 'autoplay',
                  'description' => __('Video will autoplay when the page loads.', 'fv-wordpress-flowplayer'),
                  'default' => $fv_fp->_get_option('autoplay'),
                  'children' => array(
                    array(
                      'label' => __('Muted Autoplay', 'fv-wordpress-flowplayer'),
                      'name' => 'autoplay_muted'
                    ), // TODO: Save properly  
                  )
                ),
                /*array(
                  'label' => __('Player Alignment', 'fv-wordpress-flowplayer'),
                  'name' => 'align',
                  'description' => __('Allows the article text to wrap around the player.', 'fv-wordpress-flowplayer'),
                  'children' => array(
                    array(
                      'label' => __('Position', 'fv-wordpress-flowplayer'),
                      'name' => 'align',
                      'options' => array(
                        'Left',
                        'Right',
                        'Centered'
                      ),
                      'type' => 'select'
                    ),
                    array(
                      'label' => __('Width', 'fv-wordpress-flowplayer'),
                      'name' => 'lightbox_width',
                      'type' => 'number'
                    )
                  )
                ),*/
                array(
                  'label' => __('Playlist auto advance', 'fv-wordpress-flowplayer'),
                  'name' => 'playlist_advance',
                  'default' => !$fv_fp->_get_option('playlist_advance'),
                  'scope' => 'playlist'
                ),
                array(
                  'label' => __('Sticky video', 'fv-wordpress-flowplayer'),
                  'name' => 'sticky',
                  'description' => __('Watch the playing video when scrolling down the page.', 'fv-wordpress-flowplayer'),
                  'default' => $fv_fp->_get_option('sticky'),
                  'dependencies' => array( 'lightbox' => false )
                )
              )
            ),
            'controls' => array(
              'label' => __('Controls', 'fv-wordpress-flowplayer'),
              'items' => array(
                array(
                  'label' => __('Controlbar', 'fv-wordpress-flowplayer'),
                  'name' => 'controlbar',
                  'description' => __('Without the controlbar seeking in video is impossible.', 'fv-wordpress-flowplayer'),
                  'default' => true,
                  'sticky' => true
                ),
                array(
                  'label' => __('Speed Buttons', 'fv-wordpress-flowplayer'),
                  'name' => 'speed',
                  'description' => __('Allows user to speed up or slow down the video.', 'fv-wordpress-flowplayer'),
                  'default' => $fv_fp->_get_option('ui_speed'),
                  'dependencies' => array( 'controlbar' => true )
                )
              )
            ),
            'header' => array(
              'label' => __('Sharing', 'fv-wordpress-flowplayer'),
              'items' => array(
                array(
                  'label' => __('Embedding', 'fv-wordpress-flowplayer'),
                  'name' => 'embed',
                  'description' => __('Allows users to embed your player on their websites.', 'fv-wordpress-flowplayer'),
                  'default' => !$fv_fp->_get_option('disableembedding')
                ),
                array(
                  'label' => __('Sharing Buttons', 'fv-wordpress-flowplayer'),
                  'name' => 'share',
                  'description' => __('Provides a quick way of sharing your article on Facebook, Twitter or via Email.', 'fv-wordpress-flowplayer'),
                  'default' => !$fv_fp->_get_option('disablesharing')
                ), // TODO: Custom URL setting
              )
            )
          ));

          fv_player_editor_input_group( $player_options );
          ?>

          <table width="100%">            
            <?php fv_player_shortcode_row( array( 'label' => 'Playlist Style', 'name' => 'playlist', 'dropdown' => array(
                  array('','Default'),
                  array('horizontal','Horizontal'),
                  array('tabs','Tabs'),
                  array('prevnext','Prev/Next'),
                  array('vertical','Vertical'),
                  array('slider','Slider'),
                  array('season','Season'),
                  array('polaroid','Polaroid'),
                  array('text','Text')
                ), 'class' => 'hide-if-singular' ) );
                ?>
            
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
          $actions = array(
            'actions' => array(
              'items' => array(
                array(
                  'label' => __('End of Video Action', 'fv-wordpress-flowplayer'),
                  'name' => 'end_actions_show',
                  'description' => __('What should happen at the end of the video.', 'fv-wordpress-flowplayer'),
                  'children' => array(
                    array(
                      'label' => __('Pick the action', 'fv-wordpress-flowplayer'),
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
                      'label' => __('Redirect', 'fv-wordpress-flowplayer'),
                      'name' => 'redirect',
                      'type' => 'text'
                    ),
                    array(
                      'label' => __('Popup', 'fv-wordpress-flowplayer'),
                      'name' => 'popup_id',
                      'type' => 'select',
                      'options' => fv_flowplayer_admin_select_popups()
                    ),
                    array(
                      'label' => __('Email list', 'fv-wordpress-flowplayer'),
                      'name' => 'email_list',
                      'type' => 'select',
                      'options' => fv_player_email_lists()
                    ),
                  ),
                  'no_data' => true,
                  'visible' => true
                ),
                array(
                  'label' => __('Custom Ad Code', 'fv-wordpress-flowplayer'),
                  'name' => 'ad_custom', // TODO: Do not save
                  'no_data' => true,
                  'description' => __('Shows while the video is playing.', 'fv-wordpress-flowplayer'),
                  'children' => array(
                    array(
                      'label' => __('Ad Code', 'fv-wordpress-flowplayer'),
                      'name' => 'ad',
                      'type' => 'textarea',
                      'visible' => true
                    ),
                    array(
                      'label' => __('Width', 'fv-wordpress-flowplayer'),
                      'name' => 'ad_width',
                      'type' => 'number',
                      'visible' => true
                    ),
                    array(
                      'label' => __('Height', 'fv-wordpress-flowplayer'),
                      'name' => 'ad_height',
                      'type' => 'number',
                      'visible' => true
                    )
                  ),
                  'visible' => true,
                  'dependencies' => array( 'ad_skip' => false )
                ),
              ),
              'sort' => false
            )
          );

          if( $fv_fp->_get_option('ad') ) {
            $actions['actions']['items'][] = array(
              'label' => __('Skip Global Ad', 'fv-wordpress-flowplayer'),
              'name' => 'ad_skip',
              'description' => sprintf( __('Use to disable ad set in <a href="%s" target="_blank">Actions -> Ads</a>', 'fv-wordpress-flowplayer'), admin_url('options-general.php?page=fvplayer#postbox-container-tab_actions') ),
              'visible' => true,
              'dependencies' => array( 'ad_custom' => false )
            );
          }
        
          $actions = apply_filters('fv_player_editor_actions', $actions );

          fv_player_editor_input_group( $actions );
          ?>

          <!--
          <tr class="fv_player_actions_end-toggle">
          <th scope="row" class="label"><label for="fv_wp_flowplayer_field_redirect" class="alignright"><?php _e('Redirect to', 'fv_flowplayer'); ?></label></th>
          <td class="field"><input type="text" id="fv_wp_flowplayer_field_redirect" name="fv_wp_flowplayer_field_redirect" style="width: 93%" /></td>
        </tr>

        <tr class="fv_player_actions_end-toggle">
          <th scope="row" class="label"><label for="fv_wp_flowplayer_field_popup_id" class="alignright"><?php _e('End popup', 'fv_flowplayer'); ?></label></th>
          <td>
            <?php fv_flowplayer_admin_select_popups(array('id' => 'fv_wp_flowplayer_field_popup_id', 'show_default' => true)) ?>
            <div style="display: none">
              <p><span class="dashicons dashicons-warning"></span> <?php _e('You are using the legacy popup functionality. Move the popup code', 'fv-wordpress-flowplayer'); ?> <a href="<?php echo site_url(); ?>/wp-admin/options-general.php?page=fvplayer#tab_popups" target="_blank"><?php _e('here', 'fv-wordpress-flowplayer'); ?></a><?php _e(', then use the drop down menu above.', 'fv-wordpress-flowplayer'); ?></p>
              <textarea id="fv_wp_flowplayer_field_popup" name="fv_wp_flowplayer_field_popup" style="width: 93%"></textarea>
            </div>                      
          </td>
        </tr>

        <?php

        $rawLists = get_option('fv_player_email_lists');
        $aLists = array();
        foreach($rawLists as $key => $val){
          if(!is_numeric($key))
            continue;
          $aLists[] = array($key,(empty($val->name) ? "List " . $key : "$val->name" ));
        }
        if(count($aLists)){
          fv_player_shortcode_row( array(
              'label' => 'E-mail list',
              'name' => 'email_list',
              'class' => 'fv_player_actions_end-toggle',
              'dropdown' =>$aLists,
          ) );
        }
        ?>
        <tr <?php if( !isset($fv_flowplayer_conf["interface"]["ads"]) || $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
          <th scope="row" class="label"><label for="fv_wp_flowplayer_field_ad" class="alignright"><?php _e('Ad code', 'fv_flowplayer'); ?></label></th>
          <td>
            <textarea id="fv_wp_flowplayer_field_ad" name="fv_wp_flowplayer_field_ad" style="width: 93%"></textarea>
          </td>
        </tr> 
        <tr <?php if( !isset($fv_flowplayer_conf["interface"]["ads"]) || $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
          <th scope="row" class="label"><label for="fv_wp_flowplayer_field_liststyle" class="alignright"><?php _e('Ad Size', 'fv_flowplayer'); ?></label></th>
          <td class="field<?php if( !isset($fv_flowplayer_conf["interface"]["ads"]) || $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' fv_player_interface_hide'; ?>">
            <input type="text" id="fv_wp_flowplayer_field_ad_width" name="fv_wp_flowplayer_field_ad_width" style="width: 19%; margin-right: 25px;"  value="" placeholder="<?php _e('Width', 'fv_flowplayer'); ?>"/>
            <input type="text" id="fv_wp_flowplayer_field_ad_height" name="fv_wp_flowplayer_field_ad_height" style="width: 19%; margin-right: 25px;" value="" placeholder="<?php _e('Height', 'fv_flowplayer'); ?>"/>
            <input type="checkbox" id="fv_wp_flowplayer_field_ad_skip" name="fv_wp_flowplayer_field_ad_skip" /> <?php _e('Skip global ad in this video', 'fv_flowplayer'); ?>  					
          </td>
        </tr>
        -->
        
          <?php
          // Legacy
          // TODO: Will these still actually work?
          do_action('fv_flowplayer_shortcode_editor_after');

          do_action('fv_flowplayer_shortcode_editor_tab_actions'); ?>

        </div>

        <div class="fv-player-tab fv-player-tab-embeds" style="display: none">
          <p>This page shows you where else this player is used.</p>
          <table width="100%">
            <tr>
              <th scope="row" class="label"><label for="fv_wp_flowplayer_field_embedded_on" class="alignright"><?php _e('Embedded on', 'fv_flowplayer'); ?></label></th>
              <td></td>
            </tr>
          </table>
        </div>  
        
        <?php do_action('fv_player_shortcode_editor_tab_content'); ?>

        <div id="fv-player-editor-modal-bottom">
          <a class="button-primary fv_player_field_insert-button"><?php _e('Insert', 'fv_flowplayer'); ?></a>
          <a class="playlist_edit button hide-if-playlist-active" href="#" data-create="<?php _e('Add another video into playlist', 'fv_flowplayer'); ?>" data-edit="<?php _e('Back to playlist', 'fv_flowplayer'); ?>"><?php _e('Add another video into playlist', 'fv_flowplayer'); ?></a>

          <?php
          if( function_exists('get_current_screen') && current_user_can('edit_posts') ) :
            $screen = get_current_screen();
            if ( $screen->parent_base != 'fv_player' ) : ?>
              <a class="copy_player button" href="#"><?php _e( 'Pick existing player', 'fv_flowplayer' ); ?></a>
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
global $script_fv_player_editor_defaults;
global $script_fv_player_editor_dependencies;

echo "<script>var fv_player_editor_defaults = ".json_encode($script_fv_player_editor_defaults)."</script>\n";
echo "<script>var fv_player_editor_dependencies = ".json_encode($script_fv_player_editor_dependencies)."</script>\n";
?>