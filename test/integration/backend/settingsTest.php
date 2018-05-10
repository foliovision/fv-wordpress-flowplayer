<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_SettingsTestCase extends FV_Player_UnitTestCase {
    
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
          if( $v[$i] != $two[$k][$i]) {
            var_dump( $v[$i].' vs '.$two[$k][$i].' '.ord($two[$k][$i]) );
          }
        }
      }*/
      
      //$this->assertEquals( $v, $two[$k] );
    }
    
    $this->assertEquals( $this->fix_newlines(file_get_contents(dirname(__FILE__).'/testSettingsScreen.html')), $this->fix_newlines($output) );
  }

}
