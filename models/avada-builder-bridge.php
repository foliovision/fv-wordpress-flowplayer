<?php

add_action( 'admin_footer', 'fv_player_avada_builder_bridge' );

function fv_player_avada_builder_bridge() {
  if( did_action('fusion_builder_admin_scripts_hook') ) {
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