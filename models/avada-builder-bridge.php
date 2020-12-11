<?php

add_action( 'wp_footer', 'fv_player_avada_builder_bridge', PHP_INT_MAX ); // front-end editing
add_action( 'admin_footer', 'fv_player_avada_builder_bridge' ); // back-end editing

function fv_player_avada_builder_bridge() {
  if( did_action('fusion_builder_admin_scripts_hook') || !empty($_GET['fb-edit']) ) { // either the admin scripts are loaded or it's the front-end editing
    ?>
<script>
jQuery( function($) {
  $(document).on( 'fusionButtons', function( e, current_id ) {
    jQuery('[data-option-id='+current_id+']').find('#wp-element_content-media-buttons').append('<a title="Add FV Player" href="#" class="button fv-wordpress-flowplayer-button"><span> </span> Player</a>');
  });
});
</script>  
    <?php
  }
}

// front-end editing
if( !empty($_GET['fb-edit']) ) {
  if( !function_exists('fv_wp_flowplayer_edit_form_after_editor') ) include( dirname( __FILE__ ) . '/../controller/editor.php' );
	
  add_action( 'wp_footer', 'fv_player_shortcode_editor_scripts_enqueue' );
  add_action( 'wp_footer', 'fv_wp_flowplayer_edit_form_after_editor' );
  add_action( 'wp_footer', 'flowplayer_prepare_scripts' );

  // TODO: FV Player Coconut doesn't work here
  // TODO: Shortcode is not put into the editor
}