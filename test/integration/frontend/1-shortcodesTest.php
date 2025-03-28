<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_ShortcodeTestCase extends FV_Player_UnitTestCase {

  private $post_id_SimpleShortcode;

  protected function setUp(): void {
    parent::setUp();

    // create a post with playlist shortcode
    $this->post_id_SimpleShortcode = $this->factory->post->create( array(
      'post_title' => 'Simple Shortcode',
      'post_content' => '[fvplayer src="https://cdn.site.com/video.mp4"]'
    ) );
  }

  public function testSimpleShortcode() {
    global $post;
    $post = get_post( $this->post_id_SimpleShortcode );
    $post->ID = 1234;

    remove_action('wp_head', 'wp_generator');
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'wp_print_auto_sizes_contain_css_fix', 1 );
    add_filter( 'wp_resource_hints', '__return_empty_array' );

    // Avoid certain CSS files which WordPress started to include as the block themes become the new default
    add_action(
      'wp_enqueue_scripts',
      function() {

        // wp-includes/css/dist/block-library/style.css
        wp_dequeue_style( 'wp-block-library' );

        // inline CSS vars in global-styles-inline-css
        wp_dequeue_style( 'global-styles' );

        // wp-includes/css/classic-themes.css
        wp_dequeue_style( 'classic-theme-styles' );

        wp_dequeue_style( 'core-block-supports' );
      }
    );

    // Avoid more CSS files which WordPress started to include as the block themes become the new default
    add_action(
      'wp_footer',
      function() {
        // inline style with "Core styles: block-supports"
        wp_dequeue_style( 'core-block-supports' );
      }
    );

    wp_deregister_script( 'wp-embed' );

    // note that you can only use wp_head() or wp_footer() once!
    ob_start();
    wp_head();
    echo apply_filters( 'the_content', $post->post_content );
    wp_footer();
    $output = ob_get_clean();

    // file_put_contents( dirname(__FILE__).'/testSimpleShortcode.html.new', $output );

    $regex = '~var fv_flowplayer_translations = {.*?};~';

    $sample_without_translations = preg_replace( $regex, '', $this->fix_newlines( file_get_contents(dirname(__FILE__).'/testSimpleShortcode.html') ) );
    $output_without_translations = preg_replace( $regex, '', $this->fix_newlines( $output ) );

    $this->assertEquals( $sample_without_translations, $output_without_translations );
  }

}
