<?php

/**
 * Elementor support
 */

// Load assets for the editor
add_action( 'elementor/editor/wp_head', 'fv_player_shortcode_editor_scripts_enqueue' );
add_action( 'elementor/editor/wp_head', 'fv_wp_flowplayer_edit_form_after_editor' );
add_action( 'elementor/editor/wp_head', 'flowplayer_prepare_scripts' );

/**
 * Register the Elementor widget
 */
add_action( 'elementor/widgets/register', 'fv_player_elementor_register_widgets' );

function fv_player_elementor_register_widgets( $widgets_manager ) {

  include_once( __DIR__ . '/../models/elementor-widget.php' );

  $widgets_manager->register( new FV_Player_Elementor_Widget() );
}

// Register the FV Player Elementor widget scripts
add_action( 'wp_enqueue_scripts', 'fv_player_elementor_register_scripts' );

function fv_player_elementor_register_scripts() {

  // We do not use script ID like fv-player-... as otherwise it would be loaded for front-end automatically.
  wp_register_script(
    'elementor-fv-player-widget',
    plugins_url( 'js/fv-player-elementor-widget.js', dirname( __FILE__ ) ),
    array( 'jquery' ),
    filemtime( dirname( __FILE__ ) . '/../js/fv-player-elementor-widget.js' )
  );

  wp_localize_script(
    'elementor-fv-player-widget',
    'elementor_fv_player_widget',
    array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce'   => wp_create_nonce( 'fv_player_gutenberg' ),
    )
  );
}

// Remove the FV Player Elementor Widget settings which we store in FV Player DB
add_filter( 'elementor/document/save/data', 'fv_player_editor_elementor_widget_remove_settings' );

function fv_player_editor_elementor_widget_remove_settings( $data ) {

  if ( isset( $data['elements'] ) ) {
    foreach( $data['elements'] as $k => $e ) {

      // Handle nested elements
      if ( is_array( $e['elements'] ) ) {
        $data['elements'][ $k ] = fv_player_editor_elementor_widget_remove_settings( $e );
      }

      // Remove FV Player Elementor Widget settings (fields) which should not be stored
      if ( ! empty( $e['widgetType'] ) && $e['widgetType'] === 'fv_player' ) {
        unset( $data['elements'][ $k ]['settings']['source_url'] );
        unset( $data['elements'][ $k ]['settings']['splash_url'] );
        unset( $data['elements'][ $k ]['settings']['splash_attachment_id'] );
        unset( $data['elements'][ $k ]['settings']['title'] );

        unset( $data['elements'][ $k ]['settings']['_show_timeline_previews'] );
        unset( $data['elements'][ $k ]['settings']['timeline_previews'] );

        unset( $data['elements'][ $k ]['settings']['_show_hls_key'] );
        unset( $data['elements'][ $k ]['settings']['hls_hlskey'] );
      }
    }
  }

  return $data;
}

// Replace the old FV Player Widget with the new FV Player Elementor Widget in the Elementor editor.
add_filter( 'elementor/editor/localize_settings', 'fv_player_elementor_editor_localize_settings' );

function fv_player_elementor_editor_localize_settings( $settings ) {
  $settings['initial_document'] = fv_player_elementor_editor_localize_settings_replace( $settings['initial_document'] );
  return $settings;
}

function fv_player_elementor_editor_localize_settings_replace( $data ) {

  if ( isset( $data['elements'] ) ) {
    foreach( $data['elements'] as $k => $e ) {

      // Handle nested elements
      if ( is_array( $e['elements'] ) ) {
        $data['elements'][ $k ] = fv_player_elementor_editor_localize_settings_replace( $e );
      }

      // Remove FV Player Elementor Widget settings (fields) which should not be stored
      if ( ! empty( $e['widgetType'] ) && $e['widgetType'] === 'wp-widget-widget_fvplayer' ) {
        $data['elements'][ $k ]['widgetType'] = 'fv_player';
        $data['elements'][ $k ]['settings'] = array( 'shortcode' => $e['settings']['wp']['text'] );
      }
    }
  }

  return $data;
}

// If user has the FV Player Widget in their favorites, replace it with the new FV Player Elementor Widget.
add_filter( 'get_user_option_elementor_editor_user_favorites', 'fv_player_elementor_editor_user_favorites' );

function fv_player_elementor_editor_user_favorites( $favorites ) {

  if ( ! empty( $favorites['widgets'] ) && is_array( $favorites['widgets'] ) ) {
    foreach ( $favorites['widgets'] as $key => $widget ) {
      if ( 'wp-widget-widget_fvplayer' === $widget ) {
        $favorites['widgets'][ $key ] = 'fv_player';
      }
    }
  }

  return $favorites;
}