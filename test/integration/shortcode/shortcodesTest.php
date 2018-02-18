<?php

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_Pro_PlaylistsNoAdsIntegrationTest extends WP_UnitTestCase {

  private $fvPlayerProInstance;
  private $postID = -1;

  protected $backupGlobals = false;
  
  public function fix_newlines( $html ) {
    $html = preg_replace( '/(id|rel)="wpfp_[^"]+"/', '$1="some-test-hash"', $html);
    $html = explode("\n",$html);
    $html = implode( "\n", array_map('trim',$html) );
    return $html;
  }

  // we need to set up PRO player with an appropriate key, or the PRO player won't work
  public static function wpSetUpBeforeClass() {
    global $fv_fp;

    // without this included, fv_wp_flowplayer_delete_extensions_transients() would not be found
    //include_once "../../../fv-wordpress-flowplayer/controller/backend.php";

    // include the flowplayer loader
    include_once "../../../fv-wordpress-flowplayer/flowplayer.php";

    // include the PRO plugin class, so it can intercept data saving
    // and update the ads structure as needed for saving
    //include_once "../../beta/fv-player-pro.class.php";

    // save initial settings
    //$fv_fp->_set_conf();
  }

  public function setUp() {
    parent::setUp();

    // create a post with playlist shortcode
    $this->post_id_SimpleShortcode = $this->factory->post->create( array(
      'post_title' => 'Simple Shortcode',
      'post_content' => '[fvplayer src="https://cdn.site.com/video.mp4"]'
    ) );

    /*global $fv_fp;

    include_once "../../../fv-wordpress-flowplayer/models/flowplayer.php";
    include_once "../../../fv-wordpress-flowplayer/models/flowplayer-frontend.php";
    $fv_fp = new flowplayer_frontend();

    include_once "../../beta/fv-player-pro.class.php";
    $this->fvPlayerProInstance = new FV_Player_Pro();*/
  }
  
  public function testSettingsScreen() {
    include( '../../../fv-wordpress-flowplayer/controller/backend.php' );
    include( '../../../fv-wordpress-flowplayer/controller/editor.php' );
    include( '../../../fv-wordpress-flowplayer/controller/settings.php' );
    
    
    ob_start();
    fv_player_admin_page();
    $output = ob_get_clean();
    
    $one = $this->fix_newlines(file_get_contents(dirname(__FILE__).'/testSettingsScreen.html'));
    $two = explode("\n",$this->fix_newlines($output));
    foreach( explode("\n",$one) as $k => $v ) {
      
      /*if( $v != $two[$k]) {
        for($i=0;$i<strlen($two[$k]);$i++) {
          var_dump( $two[$k][$i].' '.ord($two[$k][$i]) );
        }
      }*/
      
      $this->assertEquals( $v, $two[$k] );
    }
    
    $this->assertEquals( $this->fix_newlines(file_get_contents(dirname(__FILE__).'/testSettingsScreen.html')), $this->fix_newlines($output) );
  }

  public function testSimpleShortcode() {
    global $post;
    $post = get_post( $this->post_id_SimpleShortcode );
    
    remove_action('wp_head', 'wp_generator');
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    add_filter( 'wp_resource_hints', '__return_empty_array' );    

    wp_deregister_script( 'wp-embed' );
    
    ob_start();
    wp_head();
    echo apply_filters( 'the_content', $post->post_content );
    wp_footer();
    $output = ob_get_clean();
    
    $this->assertEquals( $this->fix_newlines(file_get_contents(dirname(__FILE__).'/testSimpleShortcode.html')), $this->fix_newlines($output) );
  }

}
