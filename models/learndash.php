<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Learndash_LMS {

  function __construct() {
    add_filter( 'plugins_loaded', array( $this, 'plugin_load' ) );
  }

  function plugin_load() {
    if( !defined('LEARNDASH_VERSION') ) {
      return;
    }

    // Register FV Player Custom Video field for LearnDash lesson settings
    add_filter( 'init', array( $this, 'register_fv_player_field' ) );
    // Make sure it does not appear as a standard meta box
    add_action( 'add_meta_boxes', array( $this, 'remove_fv_player_meta_box' ), PHP_INT_MAX );
    
    // Field for Learndash
    // FV Player display
    add_filter( 'learndash_settings_field', array( $this, 'display_field' ), 10, 2 );
    // Register "Use FV Player" and "FV Player"
    add_filter( 'learndash_settings_fields', array( $this, 'editing_field' ), 10, 2 );
    add_filter( 'ld_video_provider', array( $this, 'set_provider' ), 10, 2 );

    // TODO: Only load where needed
    add_action( 'admin_init', array( $this, 'admin_load_assets' ), 10, 2 );
    
    // We need to save our custom fields
    // Here we also adjust the Video URL field of Learndash
    add_action( 'save_post', array( $this, 'save_field' ), PHP_INT_MAX );
  }

  function admin_load_assets() {
    global $fv_wp_flowplayer_ver;
    wp_enqueue_script('fvplayer-learndash-lms-admin', plugins_url('js/learndash-lms-admin.js', dirname(__FILE__) ), array('jquery'), $fv_wp_flowplayer_ver, true );
  }

  function display_field( $field_args ) {
    if( $field_args['name'] == 'lesson_fv_player' ) {
      global $FV_Player_Custom_Videos_form_instances;
      if( !empty($FV_Player_Custom_Videos_form_instances['fv_player_custom_videos-field_lesson_fv_player']) && method_exists($FV_Player_Custom_Videos_form_instances['fv_player_custom_videos-field_lesson_fv_player'], 'get_form') ) {
        $objVideos = $FV_Player_Custom_Videos_form_instances['fv_player_custom_videos-field_lesson_fv_player'];
        $field_args['html'] = $objVideos->get_form();
      } else {
        $field_args['html'] = 'Failed to load FV Player Editor.';
      }
    }
    return $field_args;
  }

  function editing_field( $setting_option_fields, $settings_metabox_key ) {
    if( in_array($settings_metabox_key, array('learndash-lesson-display-content-settings', 'learndash-topic-display-content-settings')) ) {

      $new = array();
      foreach( $setting_option_fields AS $k => $v ) {
        $new[$k] = $v;
        
        // Add new settings after "Video URL"
        if( $k == 'lesson_video_url' ) {
                      
          // We have to load the field value ourselves: https://developers.learndash.com/hook/learndash_settings_fields/
          $post_id = get_the_ID();
          $settings_value = get_post_meta( $post_id, 'lesson_use_fvplayer_video', true );
          
          $new['lesson_use_fvplayer_video'] = array(
            'name'           => 'lesson_use_fvplayer_video',
            'label'          => esc_html__('Use FV Player', 'learndash'),
            'type'           => 'checkbox-switch',
            'value'          => $settings_value,
            'help_text'      => esc_html__('Use the FV Player video in your post content for video progression.', 'learndash'),
            'default'        => '',
            'options'        => array(
              'on' => esc_html__('Use FV Player for video progression.', 'learndash'),
              ''   => '',
            ),
            'parent_setting' => 'lesson_video_enabled',
          );
          
          $new['lesson_fv_player'] = array(
            'name'           => 'lesson_fv_player',
            'label'          => esc_html__( 'FV Player', 'learndash' ),
            'type'           => 'custom',
            'class'          => 'full-text',
            'default'        => '',
            'placeholder'    => esc_html__( 'FV Player', 'learndash' ),
            'attrs'          => array(
              'rows' => '1',
              'cols' => '57',
            ),
            'parent_setting' => 'lesson_video_enabled',
            'rest'           => array(
              'show_in_rest' => LearnDash_REST_API::enabled(),
              'rest_args'    => array(
                'schema' => array(
                  'field_key'   => 'fv_player',
                  // translators: placeholder: Lesson.
                  'description' => sprintf( esc_html_x( '%s FV Player', 'placeholder: Lesson', 'learndash' ), learndash_get_custom_label( 'lesson' ) ),
                  'type'        => 'text',
                  'default'     => '',
                ),
              ),
            ),
          );
        }
      }

      $setting_option_fields = $new;
    }
    return $setting_option_fields;
  }

  function register_fv_player_field() {
    if( class_exists('FV_Player_MetaBox') ) {
      new FV_Player_MetaBox( array(
        'name' => 'FV Player',
        'meta_key' => 'lesson_fv_player',
        'post_type' => 'sfwd-lessons',
        'display' => false,
        'multiple' => false
        )
      );
      new FV_Player_MetaBox( array(
        'name' => 'FV Player',
        'meta_key' => 'lesson_fv_player',
        'post_type' => 'sfwd-topic',
        'display' => false,
        'multiple' => false
        )
      );
    }
  }

  function remove_fv_player_meta_box() {
    remove_meta_box('fv_player_custom_videos-field_lesson_fv_player', null, 'normal' );
  }
  
  // https://developers.learndash.com/hook/learndash_settings_fields/
  function save_field( $post_id ) {
    
    if (
      ! isset( $_POST['learndash-lesson-access-settings']['nonce'] ) ||
      ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['learndash-lesson-access-settings']['nonce'] ) ), 'learndash-lesson-access-settings' )
    ) {
      return;
    }

    // Is it saving LearnDash lesson or a topic?
    $post_key = false;
    foreach( array(
      'learndash-lesson-display-content-settings',
      'learndash-topic-display-content-settings'
    ) AS $key ) {
      if( !empty($_POST[$key]) ) {
        $post_key = $key;
      }
    }

    if( !$post_key ) {
      return false;
    }

    $lesson_use_fvplayer_video = false;

    // We need to save our custom field for Use FV Player
    if ( isset( $_POST[$post_key]['lesson_use_fvplayer_video'] ) ) {
      $lesson_use_fvplayer_video = true;
      
      $my_settings_value = sanitize_key( $_POST[$post_key]['lesson_use_fvplayer_video'] );
      update_post_meta( $post_id, 'lesson_use_fvplayer_video', $my_settings_value );

    } else {
      delete_post_meta( $post_id, 'lesson_use_fvplayer_video' );
    }

    if( !empty($_POST[$post_key]['lesson_video_enabled']) && sanitize_key( $_POST[$post_key]['lesson_video_enabled'] ) == 'on' ) {
    
      // Adjusting the Video URL field based on "Use FV Player"
      foreach( array(
        '_sfwd-lessons' => 'sfwd-lessons',
        '_sfwd-topic' => 'sfwd-topic'
      ) AS $meta_key => $prefix ) {
        $video_url_key = $prefix."_lesson_video_url";
        $backup_key = '_backup_'.$prefix.'_lesson_video_url';

        // Adjust the Video URL stored by LearnDash
        $meta = get_post_meta( $post_id, $meta_key, true );
        if( $meta ) {

          // If we detect [fvplayer] shortcode was used as Video URL we enable FV Player
          if( stripos($meta[$video_url_key],'[fvplayer ') !== false ) {
            
            // If the FV Player is not already in
            // ...or if Use FV Player is not on
            $objVideos = new FV_Player_Custom_Videos( array('id' => $post_id, 'meta' => 'lesson_fv_player', 'type' => 'post' ) );
            if( !$objVideos->have_videos() || !$lesson_use_fvplayer_video ) {
              $lesson_use_fvplayer_video = true;
              update_post_meta( $post_id, 'lesson_use_fvplayer_video', 'on' );
              update_post_meta( $post_id, 'lesson_fv_player', $meta[$video_url_key] );
            }
          }

          $backup = get_post_meta( $post_id, $backup_key, true );
          if( $lesson_use_fvplayer_video ) {
            if( !$backup ) {
              update_post_meta( $post_id, $backup_key, $meta[$video_url_key] );
            }

            $lesson_fv_player = '';

            $objVideos = new FV_Player_Custom_Videos( array('id' => $post_id, 'meta' => 'lesson_fv_player', 'type' => 'post' ) );
            if( $objVideos->have_videos() ) {
              foreach( $objVideos->get_videos() AS $video ) {
                $lesson_fv_player .= $video;
              }
            }

            // We need to put in that [embed][/embed] shortcode to ensure Learndash detects that as a shortcode to show
            $meta[$video_url_key] = '[embed][/embed]'.$lesson_fv_player;
          } else if( $backup ) {
            $meta[$video_url_key] = $backup;
            
            delete_post_meta( $post_id, $backup_key );
          }

          // If Video URL is empty, LearnDash would just turn this off
          $meta[$prefix."_lesson_video_enabled"] = 'on';

          update_post_meta( $post_id, $meta_key, $meta );
        }
      }
    }
  }

  /*
  If we see [fvplayer ...] shortcode in Learndash Video URL we need to persuade it it's the local provider, otherwise it would not parse the shortcode
  */
  function set_provider($video_data, $step_settings) {
    if (strpos($step_settings['lesson_video_url'], '[fvplayer ') !== false) {
      return 'local';
    }

    return $video_data;
  }

}

new FV_Player_Learndash_LMS;
