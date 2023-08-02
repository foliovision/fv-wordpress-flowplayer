<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function fv_player_gutenberg() {
  wp_register_script( 'fv-player-gutenberg', flowplayer::get_plugin_url().'/build/index.js', array( 'wp-blocks', 'wp-element','wp-editor', 'wp-components', 'wp-i18n'), filemtime( __DIR__.'/../build/index.js' ) );

  wp_localize_script( 'fv-player-gutenberg', 'fv_player_gutenberg', array(
    'nonce' => wp_create_nonce( 'fv_player_gutenberg' ),
  ));

  if( function_exists('register_block_type') ) {
    register_block_type( 'fv-player-gutenberg/basic', array(
      'editor_script' => 'fv-player-gutenberg',
    ));
  }
}

add_action( 'init', 'fv_player_gutenberg' );
