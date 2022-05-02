<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
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
  
  $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );
  $allow_uploads = false;

	if( isset($fv_flowplayer_conf["allowuploads"]) && $fv_flowplayer_conf["allowuploads"] == 'true' ) {
	  $allow_uploads = $fv_flowplayer_conf["allowuploads"];
	  $upload_field_class = ' with-button';
	} else {
	  $upload_field_class = '';
	}
  
  function fv_flowplayer_admin_select_popups($aArgs) {

    $aPopupData = get_option('fv_player_popups');

  
    $sId = (isset($aArgs['id'])?$aArgs['id']:'popups_default');
    $aArgs = wp_parse_args( $aArgs, array( 'id'=>$sId, 'item_id'=>'', 'show_default' => false ) );
    ?>
    <select id="<?php echo $aArgs['id']; ?>" name="<?php echo $aArgs['id']; ?>">
      <?php if( $aArgs['show_default'] ) : ?>
        <option>Use site default</option>
      <?php endif; ?>
      <option <?php if( $aArgs['item_id'] == 'no' ) echo 'selected '; ?>value="no">None</option>
      <option <?php if( $aArgs['item_id'] == 'random' ) echo 'selected '; ?>value="random">Random</option>
      <?php
      if( isset($aPopupData) && is_array($aPopupData) && count($aPopupData) > 0 ) {
        foreach( $aPopupData AS $key => $aPopupAd ) {
          ?><option <?php if( $aArgs['item_id'] == $key ) echo 'selected'; ?> value="<?php echo $key; ?>"><?php
          echo $key;
          if( !empty($aPopupAd['name']) ) echo ' - '.$aPopupAd['name'];
          if( $aPopupAd['disabled'] == 1 ) echo ' (currently disabled)';
          ?></option><?php
        }
      } ?>      
    </select>
    <?php
  }
  
  function fv_player_shortcode_row( $args ) {
    $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );
    $args = wp_parse_args( $args, array(
                          'class' => false,
                          'dropdown' => array( 'Default', 'On', 'Off' ),
                          'id' => false,
                          'label' => '',
                          'live' => true,
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
    
    $live = !$live ? ' data-live-update="false"' : '';
    
    $playlist_label = $playlist_label ? ' data-playlist-label="' . __( $playlist_label, 'fv_flowplayer') . '"  data-single-label="' . __( $label, 'fv_flowplayer') . '"' : '';
    
    ?>
      <tr<?php echo $id.$class; ?>>
        <th scope="row" class="label"><label for="fv_wp_flowplayer_field_<?php echo $name; ?>" class="alignright" <?php echo $playlist_label; ?>><?php _e( $label, 'fv_flowplayer'); ?></label></th>
        <td class="field">
          <select id="fv_wp_flowplayer_field_<?php echo $name; ?>" name="fv_wp_flowplayer_field_<?php echo $name; ?>"<?php echo $live; ?>>
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
  
  function fv_player_editor_checkbox( $args ) {
    extract($args);

    if( $id ) {
      $id = ' id="'.$id.'"';
    }
    ?>
  <div <?php echo $id; ?> class="components-base-control__field">
    <span class="components-form-toggle<?php /*if( $default ) echo ' is-checked';*/ ?>">
      <input class="components-form-toggle__input" type="checkbox" aria-describedby="inspector-toggle-control-0__help"  id="fv_wp_flowplayer_field_<?php echo $name; ?>" name="fv_wp_flowplayer_field_<?php echo $name; ?>"<?php echo $live; ?><?php /*if( $default ) echo ' checked="checked"';*/ ?> />
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
      <input class="components-text-control__input" type="text" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" />
      <?php if( $browser ) : ?>
        <a class="components-button is-tertiary" href="#"><?php _e('Add', 'fv_flowplayer'); ?> <?php echo $label; ?> <?php _e('from media library', 'fv_flowplayer'); ?></a>
      <?php endif; ?>
    </div>
  </div>
    <?php
  }
  
  function fv_player_editor_input( $args, $is_child = false ) {
    $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );
    $args = wp_parse_args( $args, array(
                          'browser' => false,
                          'children' => false,
                          'class' => false,
                          'default' => false,
                          'dropdown' => array( 'Default', 'On', 'Off' ),
                          'id' => false,
                          'label' => '',
                          'live' => true,
                          'name' => '',
                          'options' => array(),
                          'playlist_label' => false,
                          'scope' => false,
                          'type' => false,
                          'visible' => false
                         ) );

    extract($args);

    // Check if the field is enabled in Post Interface Options
    if(
      !$visible &&
      (
        !isset($fv_flowplayer_conf["interface"][$name]) ||
        $fv_flowplayer_conf["interface"] [$name] !== 'true'
      )
    ) {
      $class .= ' fv_player_interface_hide';
    }

    if( $scope == 'playlist' ) {
      $class .= ' hide-if-singular';
    }

    $live = !$live ? ' data-live-update="false"' : '';

    $playlist_label = $playlist_label ? ' data-playlist-label="' . __( $playlist_label, 'fv_flowplayer') . '"  data-single-label="' . __( $label, 'fv_flowplayer') . '"' : '';

    // Lookout for gutenberg modular styles, where this is only a direct copy of the checkbox field
    
    if( !$is_child ) : ?>
<div id="fv-player-editor-field-wrap-<?php echo $name; ?>" class="components-base-control <?php echo $class; ?>">
    <?php endif;

    if( !$type || $type == 'checkbox' ) {
      fv_player_editor_checkbox( $args );     
    } else if( $type == 'text' ) {
      fv_player_editor_textfield( $args );     
    } else if( $type == 'number' ) {
      fv_player_editor_numfield( $args );     
    } else if( $type == 'select' ) {
      fv_player_editor_select( $args );     
    }

    if( !empty($args['description']) ) : ?>
      <p class="components-form-token-field__help"><?php echo $args['description']; ?></p>
    <?php endif;
  
    if( $children ) : ?>
      <div id="fv-player-editor-field-children-<?php echo $name; ?>" style="display: none">
        <?php
        foreach( $children AS $child_input ) {
          fv_player_editor_input( $child_input, true );
        }
        ?>
      </div>
    <?php endif;
    
    if( !$is_child ) : ?>
</div>
    <?php endif;
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
<?php global $fv_fp; if( $fv_fp->_get_option('postthumbnail') || $fv_fp->_get_option( array('integrations','featured_img') ) ) : ?>
var fv_flowplayer_set_post_thumbnail_id = <?php echo $post_id; ?>;
var fv_flowplayer_set_post_thumbnail_nonce = '<?php echo wp_create_nonce( "set_post_thumbnail-$post_id" ); ?>';
<?php endif; ?>
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
          <!--<h1><?php _e('Add your video', 'fv-wordpress-flowplayer'); ?></h1>-->
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
        
        <div class="fv-player-tab fv-player-tab-playlist" style="">
          <div id="fv-player-list-thumb-toggle">
            <a href="#" id="fv-player-list-list-view" ><span class="dashicons dashicons-list-view"><span class="screen-reader-text">List view</span></span></a>
            <a href="#" id="fv-player-list-thumb-view" class="active" data-title="<?php _e('Add splash images to enable thumbnail view', 'fv_flowplayer');?>"><span class="dashicons dashicons-exerpt-view"><span class="screen-reader-text">Thumbnail view</span></span></a>
          </div>
          <table class="wp-list-table widefat fixed striped media" width="100%">
            <thead>
              <tr>
                <th><a>Video</a></th>
                <th><a<?php if( !isset($fv_flowplayer_conf["interface"]["playlist_captions"]) || $fv_flowplayer_conf["interface"]["playlist_captions"] != 'true' ) echo ' class="fv_player_interface_hide"'; ?>>Title</a></th>
                <!--<th>Dimension</th>
                <th>Time</th>-->
              </tr>  
            </thead>
            
            
            <tbody>
              <tr>
                <!--<td class="fvp_item_sort">&nbsp;&nbsp;&nbsp;</td>-->
                <!--<td class="fvp_item_video"><strong class="has-media-icon">(new video)</strong></td>-->
                <td class="title column-title" data-colname="File">		
                  <div class="fvp_item_video-side-by-side">
                    <a class="fvp_item_video-thumbnail"></a>
                  </div>
                  <div class="fvp_item_video-side-by-side">
                    <a class="fvp_item_video-filename"></a><br>
                    <a class="fvp_item_remove" role="button">Delete</a>
                  </div>
                </td>
                
                <td class="fvp_item_caption"><div<?php if( !isset($fv_flowplayer_conf["interface"]["playlist_captions"]) || $fv_flowplayer_conf["interface"]["playlist_captions"] != 'true' ) echo ' class="fv_player_interface_hide"'; ?>>-</div></td>
                <!--<td class="fvp_item_dimension">-</td>-->
                <!--<td class="fvp_item_time">-</td>-->
                <!--<td class="fvp_item_remove"><div></div></td>-->
              </tr> 
            </tbody>        
          </table>

        </div>
        
        <div class="fv-player-tab fv-player-tab-video-files">

          <?php
          $video_fields = apply_filters('fv_player_editor_video_fields', array(
            'video' => array(
              array(
                'label' => __('Video', 'fv-wordpress-flowplayer'),
                'name' => 'src',
                'browser' => true,
                'type' => 'text',
                'sticky' => true,
                'visible' => true
              ),
              // TODO: #fv_wp_flowplayer_file_info, Add more formats, add mobile video, add RTMP even
              array(
                'label' => __('Splash', 'fv-wordpress-flowplayer'),
                'name' => 'src',
                'browser' => true,
                'type' => 'text',
                'visible' => true
              ),
              array(
                'label' => __('Splash Text', 'fv-wordpress-flowplayer'),
                'name' => 'splash_text',
                'type' => 'text'
              ),
              array(
                'label' => __('Title', 'fv-wordpress-flowplayer'),
                'name' => 'caption',
                'type' => 'text',
                'visible' => isset($fv_flowplayer_conf["interface"]["playlist_captions"]) && $fv_flowplayer_conf["interface"]["playlist_captions"] == 'true'
              )
            )
          ) );

          // TODO: Refactor, use same code as for $player_options
          foreach( $video_fields AS $group => $inputs ) {
            echo "<div class='components-panel__body fv-player-editor-options-".$group."'>\n";
            
            usort( $inputs, 'fv_player_editor_input_sort' );
            
            foreach( $inputs AS $input ) {
              fv_player_editor_input( $input );
            }
            echo "</div>\n";
          }
          ?>

          <table class="slidetoggle describe fv-player-playlist-item" width="100%" data-index="0">
            <tbody>
              <?php do_action('fv_flowplayer_shortcode_editor_before'); ?>

              <tr id="fv_wp_flowplayer_add_format_wrapper">
                <th scope="row" class="label"></th>
                <td class="field" style="width: 50%"><div id="add_format_wrapper"><a href="#" class="partial-underline" style="outline: 0"><span id="add-format">+</span>&nbsp;<?php _e('Add another format', 'fv_flowplayer'); ?></a> <?php _e('(i.e. WebM, OGV)', 'fv_flowplayer'); ?></div></td>
                <td class="field"><div class="add_rtmp_wrapper"><a href="#" class="partial-underline" style="outline: 0"><span id="add-rtmp">+</span>&nbsp;<?php _e('Add RTMP', 'fv_flowplayer'); ?></a></div></td>  				
              </tr>      
              
    <tr class="<?php if (isset($fv_flowplayer_conf["interface"]["synopsis"]) && $fv_flowplayer_conf["interface"]["synopsis"] == 'true') echo 'playlist_synopsis'; else echo 'fv_player_interface_hide'; ?>" >
                <th scope="row" class="label" valign="top"><label for="fv_wp_flowplayer_field_synopsis" class="alignright"><?php _e('Synopsis', 'fv_flowplayer'); ?></label></th>
                <td class="field" colspan="2"><textarea id="fv_wp_flowplayer_field_synopsis" name="fv_wp_flowplayer_field_synopsis" class="<?php echo $upload_field_class; ?>" rows="3"></textarea></td>
              </tr>

              <tr class="fv_player_interface_hide">
                  <th scope="row" class="label"><label for="fv_wp_flowplayer_field_live" class="alignright"><?php _e('Live stream', 'fv_flowplayer'); ?></label></th>
                  <td class="field"><input type="checkbox" id="fv_wp_flowplayer_field_live" name="fv_wp_flowplayer_field_live" /></td>
              </tr>
              
              <tr class="fv_player_interface_hide">
                  <th scope="row" class="label"><label for="fv_wp_flowplayer_field_dvr" class="alignright"><?php _e('DVR stream', 'fv_flowplayer'); ?></label></th>
                  <td class="field"><input type="checkbox" id="fv_wp_flowplayer_field_dvr" name="fv_wp_flowplayer_field_dvr" /></td>
              </tr>
              
              <tr class="fv_player_interface_hide">
                  <th scope="row" class="label"><label for="fv_wp_flowplayer_field_audio" class="alignright"><?php _e('Audio stream', 'fv_flowplayer'); ?></label></th>
                  <td class="field"><input type="checkbox" id="fv_wp_flowplayer_field_audio" name="fv_wp_flowplayer_field_audio" /></td>
              </tr>                    

              <?php do_action('fv_flowplayer_shortcode_editor_item_after'); ?>     

              <?php if (!$allow_uploads && current_user_can('manage_options')) : ?> 
                <tr>
                  <td colspan="2">
                    <div class="fv-wp-flowplayer-notice"><?php _e('Admin note: Video uploads are currently disabled, set Allow User Uploads to true in', 'fv_flowplayer'); ?> <a href="<?php echo site_url(); ?>/wp-admin/options-general.php?page=fvplayer"><?php _e('Settings', 'fv_flowplayer'); ?></a></div>
                  </td>
                </tr>            
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="fv-player-tab fv-player-tab-subtitles" style="display: none">
          <table width="100%" data-index="0">

          <?php do_action('fv_flowplayer_shortcode_editor_subtitles_tab_prepend'); ?>

            <tr>
              <th scope="row" class="label"><label for="fv_wp_flowplayer_field_subtitles" class="alignright"><?php _e('Subtitles', 'fv_flowplayer'); ?></label></th>
              <td class="field fv-fp-subtitles" colspan="2">
                <div class="fv-fp-subtitle">
                  <select class="fv_wp_flowplayer_field_subtitles_lang" name="fv_wp_flowplayer_field_subtitles_lang">
                    <option value="">&nbsp;</option>
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
                  <input type="text" class="text<?php echo $upload_field_class; ?> fv_wp_flowplayer_field_subtitles" name="fv_wp_flowplayer_field_subtitles" value=""/>
                  <?php if ($allow_uploads == 'true') { ?>
                    <a class="button add_media" href="#"><span class="wp-media-buttons-icon"></span> <?php _e('Add Subtitles', 'fv_flowplayer'); ?></a>
                    <a class="fv-fp-subtitle-remove" href="#" style="display: none">X</a>
                  <?php }; ?>
                  <div style="clear:both"></div>
                </div>
              </td>
            </tr>

            <?php do_action('fv_flowplayer_shortcode_editor_subtitles_tab_append'); ?>

            <tr class="submit-button-wrapper">
              <td colspan="2">
              </td>              
              <td>
                <a class="fv_flowplayer_language_add_link partial-underline"  style="outline: 0" href="#"><span class="add-subtitle-lang">+</span>&nbsp;<?php _e('Add Another Language', 'fv_flowplayer'); ?></a>
              </td>
            </tr>
          </table>
        </div>

        <div class="fv-player-tab fv-player-tab-options" style="display: none">
          <table width="100%">
            
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
                array(
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
                ),                
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


            $script_fv_player_editor_defaults = array();

            foreach( $player_options AS $group => $group_options ) {
              echo "<div class='components-panel__body fv-player-editor-options-".$group." is-opened'>\n";
              echo "<h2 class='components-panel__body-title'><button type='button' aria-expanded='true' class='components-button components-panel__body-toggle'><span aria-hidden='true'><svg width='24' height='24' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg' class='components-panel__arrow' role='img' aria-hidden='true' focusable='false'><path d='M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z'></path></svg></span>".$group_options['label']."</button></h2>\n";
              
              usort( $group_options['items'], 'fv_player_editor_input_sort' );
              
              foreach( $group_options['items'] AS $input ) {
                if( isset($input['default']) ) {
                  $script_fv_player_editor_defaults[$input['name']] = $input['default'];
                }
                
                if( isset($input['dependencies']) ) {
                  foreach( $input['dependencies'] AS $parent => $value ) {
                    if( empty($script_fv_player_editor_dependencies[$parent]) ) {
                      $script_fv_player_editor_dependencies[$parent] = array();
                    }
                    if( empty($script_fv_player_editor_dependencies[$parent][$value]) ) {
                      $script_fv_player_editor_dependencies[$parent][$value] = array();
                    }
                    $script_fv_player_editor_dependencies[$parent][$value][] = $input['name'];
                  }
                }

                fv_player_editor_input( $input );

              }
              echo "</div>\n";
            }

            echo "<script>var fv_player_editor_defaults = ".json_encode($script_fv_player_editor_defaults)."</script>";
            echo "<script>var fv_player_editor_dependencies = ".json_encode($script_fv_player_editor_dependencies)."</script>";
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
          <table width="100%">
            <?php fv_player_shortcode_row( array('label' => 'End of video',
                                                  'playlist_label' => 'End of playlist',
                                                  'name' => 'end_actions',
                                                  'dropdown' => array(
                                                      array('', 'Default'),
                                                      array('no', 'Nothing'),
                                                      array('redirect', 'Redirect'),
                                                      array('loop', 'Loop'),
                                                      array('popup', 'Show popup'),
                                                      array('splashend', 'Show splash screen'),
                                                      array('email_list', 'Collect Emails')),
                                                  'live' => false ) ); ?>

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
                  'live' => false ) );
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
            
            <?php do_action('fv_flowplayer_shortcode_editor_after'); ?>
            
            <?php do_action('fv_flowplayer_shortcode_editor_tab_actions'); ?>
            
          </table>
        </div>

        <div class="fv-player-tab fv-player-tab-embeds" style="display: none">
          <p>This page shows you where else this video is used.</p>
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
          <a class="playlist_add button hide-if-singular-active"><?php _e(' + Add playlist item', 'fv_flowplayer');?></a>
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

    <div class="fv-player-save-notice fv-player-save-completed" style="display: none"><p>Saved!</p></div>
    <div class="fv-player-save-notice fv-player-save-error" style="display: none"><p>Error saving changes.</p></div>
    <div class="fv-messages"></div>   
  </div>
</div>
