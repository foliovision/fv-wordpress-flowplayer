<?php

class FV_Player_Learndash_LMS {

  function __construct() {
    add_filter( 'plugins_loaded', array( $this, 'plugin_load' ) );
  }

  function plugin_load() {
    if( !defined('LEARNDASH_VERSION') ) {
      return;
    }

    // Register FV Player Custom Video field for LearnDash lesson settings
    add_filter( 'init', array( $this, 'register_fv_player_field' ), 0 );
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
      $objVideos = $FV_Player_Custom_Videos_form_instances['fv_player_custom_videos-field_lesson_fv_player'];
      if( method_exists($objVideos, 'get_form') ) {
        $field_args['html'] = $objVideos->get_form();
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
    if( class_exists('\FV_Player_MetaBox') ) {
      new \FV_Player_MetaBox( array(
          'name' => 'FV Player',
          'meta_key' => 'lesson_fv_player',
          'post_type' => 'sfwd-lessons',
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
    
    $lesson_use_fvplayer_video = false;
    if ( isset( $_POST['learndash-lesson-display-content-settings']['lesson_use_fvplayer_video'] ) ) {
      $lesson_use_fvplayer_video = true;
      
      $my_settings_value = esc_attr( $_POST['learndash-lesson-display-content-settings']['lesson_use_fvplayer_video'] );
      update_post_meta( $post_id, 'lesson_use_fvplayer_video', $my_settings_value );
      
    } else {
      delete_post_meta( $post_id, 'lesson_use_fvplayer_video' );
    }
    
    // Adjust the Video URL stored by LearnDash
    $_sfwd_lessons = get_post_meta( $post_id, '_sfwd-lessons', true );
    if( $_sfwd_lessons ) {        
      $backup = get_post_meta( $post_id, '_backup_sfwd_lessons_lesson_video_url', true );
      if( $lesson_use_fvplayer_video ) {
        if( !$backup ) {
          update_post_meta( $post_id, '_backup_sfwd_lessons_lesson_video_url', $_sfwd_lessons["sfwd-lessons_lesson_video_url"] );
        }
        
        $lesson_fv_player = '';
        
        $objVideos = new FV_Player_Custom_Videos( array('id' => $post_id, 'meta' => 'lesson_fv_player', 'type' => 'post' ) );
        if( $objVideos->have_videos() ) {
          foreach( $objVideos->get_videos() AS $video ) {
            $lesson_fv_player .= $video;
          }
        }
        
        // We need to put in that [embed][/embed] shortcode to ensure Learndash detects that as a shortcode to show
        $_sfwd_lessons["sfwd-lessons_lesson_video_url"] = '[embed][/embed]'.$lesson_fv_player;
      } else if( $backup ) {
        $_sfwd_lessons["sfwd-lessons_lesson_video_url"] = $backup;
        
        delete_post_meta( $post_id, '_backup_sfwd_lessons_lesson_video_url' );
      }

      update_post_meta( $post_id, '_sfwd-lessons', $_sfwd_lessons );
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
