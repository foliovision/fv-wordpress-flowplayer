<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function fv_player_gutenberg() {
  wp_register_script( 'fv-player-gutenberg', flowplayer::get_plugin_url().'/blocks/build/index.js', array( 'wp-blocks', 'wp-element','wp-editor', 'wp-components', 'wp-i18n', 'wp-dom-ready'), filemtime( __DIR__.'/../blocks/build/index.js' ) );

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
        'forceUpdate' => array(
          'type' => 'string',
          'default' => '0',
        )
      ),
    ));
  }
}

function fv_player_block_render($attributes, $content, $block) {
  ob_start();

  if( !empty( $attributes['player_id'] ) ) {

    if( $attributes['align'] == 'wide' || $attributes['align'] == 'full' ) {
      $shortcode_dimensions = 'width="100%" height="100%"';
    } else  if( $attributes['align'] == 'left' || $attributes['align'] == 'right' ) {
      $shortcode_dimensions = 'align="left|right"';
    }

    echo '<div class="'.$attributes['className'].' align'. $attributes['align'] .'">' . do_shortcode( '[fvplayer id="' . $attributes['player_id'] . '" '.$shortcode_dimensions.']' ) . '</div>';
  } else {
    echo 'No player created yet.';
  }

  $output = ob_get_clean();
  return $output;
}

add_action( 'init', 'fv_player_gutenberg' );

function fv_player_add_missing_attributes_callback($matches) {
  $player_id = preg_match('/id="(\d+)"/', $matches[0], $player_id_matches) ? $player_id_matches[1] : 0;

  // bail out if no player id
  if ( !$player_id ) {
    return $matches[0];
  }

  $player = new FV_Player_Db_Player( $player_id );

  $attributes = array(
    'align' => '',
    'className' => '',
    'src' => '',
    'splash' => '',
    'title' => '',
    'shortcodeContent' => '',
    'player_id' => '',
    'splash_attachment_id' => '',
    'forceUpdate' => 0
  );

  $attributes['player_id'] = $player_id;
  $attributes['shortcodeContent'] = '[fvplayer id="' . $player_id . '"]';

  // get data from first video
  foreach( $player->getVideos() AS $video ) {
    $attributes['src'] = $video->getSrc() ? $video->getSrc() : '';
    $attributes['splash'] = $video->getSplash() ? $video->getSplash() : '';
    $attributes['title'] = $video->getTitle() ? $video->getTitle() : '';
    $attributes['splash_attachment_id'] = $video->getSplashAttachmentId() ? $video->getSplashAttachmentId() : '0';
    break;
  }

  $content = $attributes['shortcodeContent'];

  return '<!-- wp:fv-player-gutenberg/basic ' . json_encode($attributes) . ' -->'. PHP_EOL . $content . PHP_EOL . '<!-- /wp:fv-player-gutenberg/basic -->';
}

function fv_player_update_block_attributes($content) {
  $content = preg_replace_callback('/<!-- wp:fv-player-gutenberg\/basic -->(.*?)<!-- \/wp:fv-player-gutenberg\/basic -->/s', 'fv_player_add_missing_attributes_callback', $content);

  return $content;
}

function fv_player_handle_post_content($content) {
  // get post object
  global $post;

  if ( ! $post || !$content ) {
    return $content;
  }

  $updated_content = fv_player_update_block_attributes($content);

  return $updated_content;
}

// frontend, before block generates HTML
add_filter( 'the_content', 'fv_player_handle_post_content', 8 );

function fv_player_handle_rest_content($response) {
  $updated_content = fv_player_update_block_attributes($response->data['content']['raw']);

  // check if changed
  if ( $updated_content !== $response->data['content']['raw'] ) {
    $response->data['content']['raw'] = $updated_content;
  }

  return $response;
}

$post_types = get_post_types( array( 'show_in_rest' => true ), 'names' );

foreach ( $post_types as $post_type ) {
  add_filter( 'rest_prepare_' . $post_type, 'fv_player_handle_rest_content' );
}
