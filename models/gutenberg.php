<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function fv_player_gutenberg() {
  wp_register_script( 'fv-player-gutenberg', flowplayer::get_plugin_url().'/build/index.js', array( 'wp-blocks', 'wp-element','wp-editor', 'wp-components', 'wp-i18n', 'wp-dom-ready'), filemtime( __DIR__.'/../build/index.js' ) );

  wp_localize_script( 'fv-player-gutenberg', 'fv_player_gutenberg', array(
    'nonce' => wp_create_nonce( 'fv_player_gutenberg' ),
  ));

  if( function_exists('register_block_type') ) {
    register_block_type( 'fv-player-gutenberg/basic', array(
      'editor_script' => 'fv-player-gutenberg',
      'render_callback' => 'fv_player_block_render',
      'attributes' => array(
        'align' => array( // block alignment in popover
          'type' => 'string',
          'default' => '',
        ),
        'className' => array( // set in advanced block settings
          'type' => 'string',
          'default' => '',
        ),
        'src' => array(
          'type' => 'string',
          'default' => '',
        ),
        'splash' => array(
          'type' => 'string',
          'default' => '',
        ),
        'title' => array(
          'type' => 'string',
          'default' => '',
        ),
        'shortcodeContent' => array(
          'type' => 'string',
          'default' => '',
          'source' => 'text'
        ),
        'player_id' => array(
          'type' => 'string',
          'default' => '0',
        ),
        'splash_attachment_id' => array(
          'type' => 'string',
          'default' => '0',
        ),
      ),
    ));
  }
}

function fv_player_block_render($attributes, $content, $block) {
  ob_start();
  echo ! empty( $attributes['player_id'] ) ? '<div class="'.$attributes['className'].' align'. $attributes['align'] .'">' . do_shortcode( '[fvplayer id="' . $attributes['player_id'] . '"]' ) . '</div>' : 'No player created yet.';
  $output = ob_get_clean();
  return $output;
}

add_action( 'init', 'fv_player_gutenberg' );
