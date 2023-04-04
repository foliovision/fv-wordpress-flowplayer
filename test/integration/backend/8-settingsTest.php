<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_SettingsTestCase extends FV_Player_UnitTestCase {
  
  //  we need to convince it is showing the FV Player settings screen!
  public static function wpSetUpBeforeClass() {
    set_current_screen( 'settings_page_fvplayer' );
    
    parent::wpSetUpBeforeClass();
    
    remove_action( 'admin_init', 'wp_admin_headers' );
    
    remove_action( 'admin_init', '_maybe_update_core' );
    remove_action( 'admin_init', '_maybe_update_plugins' );
    remove_action( 'admin_init', '_maybe_update_themes' );

    do_action( 'admin_init' );
  }  
  
  /*
  public function testSettingsScreen() {

    ob_start();
    fv_player_admin_page();
    $output = ob_get_clean();

    $expect = file_get_contents(dirname(__FILE__).'/testSettingsScreen.html');

    file_put_contents( dirname(__FILE__) .'/testSettingsScreen.output.html', $output );

    $expect = preg_replace('~"srclang".*?\.vtt"~', '"srclang":"/usr/bin/php","src":"http:\/\/example.org\/wp-content\/plugins\/fv-wordpress-flowplayer\/images\/test-subtitles.vtt"', $expect);
    $output = preg_replace('~"srclang".*?\.vtt"~', '"srclang":"/usr/bin/php","src":"http:\/\/example.org\/wp-content\/plugins\/fv-wordpress-flowplayer\/images\/test-subtitles.vtt"', $output);


    $this->assertEquals( $this->fix_newlines($expect), $this->fix_newlines($output) );
  }
  */
}
