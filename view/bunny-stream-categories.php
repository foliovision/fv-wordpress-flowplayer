<?php // hide the "All Categories" and "Most Used" tabs as it's not important ?>
<style>
#fv_player_encoding_category-tabs {
  display: none;
}
</style>

<?php
// we miss some of the code WP admin stuff here
if( !function_exists('post_categories_meta_box') ) {
  require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
}
wp_enqueue_script('post');

ob_start();
$fake_post = new stdClass;
$fake_post->ID = -1;
post_categories_meta_box( $fake_post, array( 'args' => array( 'taxonomy' => 'fv_player_encoding_category') ) );
$html = ob_get_clean();

$html = preg_replace( '~(<ul id="fv_player_encoding_categorychecklist".*?>)\s+(</ul>)~', '$1<li id="fv-player-coconut-category-nag">Add a category to keep your video files organized</li>$2', $html );

echo $html;