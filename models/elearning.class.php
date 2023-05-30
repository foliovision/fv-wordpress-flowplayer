<?php

class FV_Player_Elearning {
  private $is_enabled = false;

  function __construct() {
    add_action( 'plugins_loaded', array( $this, 'loader' ), 11 );
  }

  function loader() {
    add_filter( 'fv_player_item', array( $this, 'check_meta' ), 11, 3 );
    add_filter( 'fv_flowplayer_attributes', array( $this, 'edit_attributes' ), 11, 3 );

    add_action( 'admin_init', array( $this, 'admin__add_meta_boxes' ) );
    add_action( 'fv_flowplayer_shortcode_editor_tab_options', array( $this, 'shortcode_editor_options' ) );
    add_action( 'wp_footer', array( $this, 'script_enqueue_frontend' ) );
  }

  function check_meta( $aItem, $index, $aArgs ) {
    global $fv_fp;

    // shortcode args
    if( isset( $aArgs['lms_teaching '] ) ) {
      if( $aArgs['lms_teaching '] == 'yes' || $aArgs['lms_teaching '] == 'true' ) {
        $this->is_enabled = true;
      } else {
        $this->is_enabled = false;
      }
    } else {

      $meta_setting = 'default'; // setting for specific player
      if ($fv_fp->current_player() && count($fv_fp->current_player()->getMetaData())) {
        foreach ($fv_fp->current_player()->getMetaData() as $meta_object) {
          if( strcmp( $meta_object->getMetaKey(), 'lms_teaching' ) == 0 ) {
            $meta_setting = $meta_object->getMetaValue();
          }
        }
      }

      if( $meta_setting == 'yes' ) {
        $this->is_enabled = true;
      } else {
        $this->is_enabled = false;
      }
    }

    return $aItem;
  }

  function edit_attributes( $attributes, $media, $fv_fp ) {
    if( $this->is_enabled && is_user_logged_in() ) {
      $attributes['data-1st-play-no-seek'] = true;

      // if( strpos( $attributes['class'], 'no-controlbar' ) == false ) {
      //   $attributes['class'] .= ' no-controlbar';
      // }

    }

    return $attributes;
  }

  function admin__add_meta_boxes() {
    add_meta_box( 'FV_Player_Elearning', __('LMS | Teaching', 'fv-wordpress-flowplayer'), array( $this, 'fv_player_elearning_option' ), 'fv_flowplayer_settings', 'normal', 'low' );
  }

  function fv_player_elearning_option() {
    global $fv_fp;
    $controlbarOpt = $fv_fp->_get_option( 'lms_teaching' );
    ?>
    <p><?php _e('Disable player controlbar for users who didn`t see entire video:', 'fv-wordpress-flowplayer'); ?></p>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td class="first" ><label for="lms_teaching"><?php _e('Disable seeking forward for 1st time viewers', 'fv-wordpress-flowplayer' ) ?>:</label></td>
        <td >
          <p class="description">
            <input type="hidden" name="lms_teaching" value="false" >
            <input type="checkbox" name="lms_teaching" value="true" <?php if( $controlbarOpt ) echo ' checked="checked"'; ?> ></option>
          </p>
        </td>
      </tr>
      <tr>
        <td colspan="4">
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
        </td>
      </tr>
    </table>
    <?php
  }

  public function shortcode_editor_options() {
    global $fv_fp;

    if( !$fv_fp->_get_option( 'lms_teaching' ) ) { // check if disabled in settings
      return;
    }

    ?>
      <tr>
        <th scope="row" class="label"><label for="lms_teaching_player" class="alignright"><?php _e('LMS | Teaching: 1st Play Video Seek Disable', 'fv-wordpress-flowplayer'); ?></label></th>
        <td class="field">
        <select name="lms_teaching_player" id="lms_teaching_player">
          <option>Default</option>
          <option>Yes</option>
          <option>No</option>
        </select>
        </td>
      </tr>
    <?php
  }

  function script_enqueue_frontend() {
    wp_localize_script( 'flowplayer', 'fv_player_elearning', array(
      'msg_no_skipping' => __('Skipping is not allowed.', 'fv-wordpress-flowplayer'),
      'msg_watch_video' => __('Please watch the video carefully.', 'fv-wordpress-flowplayer'),
    ));
  }

}

new FV_Player_Elearning;
