<?php

/**
 * Elementor support
 */
add_action( 'elementor/editor/wp_head', 'fv_player_shortcode_editor_scripts_enqueue' );
add_action( 'elementor/editor/wp_head', 'fv_wp_flowplayer_edit_form_after_editor' );
add_action( 'elementor/editor/wp_head', 'flowplayer_prepare_scripts' );

// Bring back the FV Player into Elementor Elements search - it's their "Hide native WordPress widgets from search results" setting
add_filter( 'pre_option_elementor_experiment-e_hidden_wordpress_widgets', 'fv_player_editor_elementor_widget_search_enable' );

function fv_player_editor_elementor_widget_search_enable( $val ) {
  return 'inactive';
}