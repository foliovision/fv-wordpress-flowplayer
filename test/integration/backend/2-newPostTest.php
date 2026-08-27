<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

final class FV_Player_NewPostTest extends FV_Player_UnitTestCase {
  
  public function setUp() {
    parent::setUp();

    global $fv_fp;

    include_once "../../../fv-wordpress-flowplayer/models/flowplayer.php";
    include_once "../../../fv-wordpress-flowplayer/models/flowplayer-frontend.php";
    $fv_fp = new flowplayer_frontend();

  }

  public function testOpeningNewPost() {
    do_action( "load-post-new.php" );
    
    $this->assertEquals( 1, 1 );
  }

}
