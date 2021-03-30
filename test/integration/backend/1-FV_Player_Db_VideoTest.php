<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_Db_VideoTestCase extends FV_Player_UnitTestCase {

  var $videos = array();

  public function setUp() {
    parent::setUp();
        
    global $FV_Player_Db;
    $player_id = $FV_Player_Db->import_player_data( false, false, json_decode( file_get_contents(dirname(__FILE__).'/player-data.json'), true) );
    
    $player = new FV_Player_Db_Player( $player_id );
    $this->videos = $player->getVideos();
  }
  
  public function test_updateMetaValue_with_video_with_no_meta() {
    $this->assertTrue( !empty($this->videos[0]) );
  
    // Are we really testing on a video with no meta?
    $this->assertTrue( count($this->videos[0]->getMetaData()) == 0 );
    
    // Test with integer value
    $this->worker( $this->videos[0], 'bogus_meta_integer', 1, 2 );
    
    // Test with string value
    $string = file_get_contents(dirname(__FILE__).'/player-data.json');
    $this->worker( $this->videos[0], 'bogus_meta_string', $string, $string.$string );

  }
  
  public function test_updateMetaValue_with_video_with_existing_meta() {
    $this->assertTrue( !empty($this->videos[1]) );
  
    // Are we really testing on a video with existing meta?
    $this->assertTrue( count($this->videos[1]->getMetaData()) > 0 );
    
    // Test with integer value
    $this->worker( $this->videos[1], 'bogus_meta_integer', 1, 2 );
    
    // Test with string value
    $string = file_get_contents(dirname(__FILE__).'/player-data.json');
    $this->worker( $this->videos[1], 'bogus_meta_string', $string, $string.$string );

  }
  
  function worker( $video, $meta_key, $value, $new_value ) {
    // the video meta here should not exist
    $this->assertFalse( $video->getMetaValue( $meta_key, true ) );

    // we should get a new meta row id here
    $meta_id = $video->updateMetaValue( $meta_key, $value );
    $this->assertTrue( $meta_id > 0 );
    
    // running the same updateMetaValue() should give the same row = id
    $check_meta_id = $video->updateMetaValue( $meta_key, $value );
    $this->assertEquals( $meta_id, $check_meta_id );
    
    // if we run another update it should be the same row id
    $check_meta_id = $video->updateMetaValue( $meta_key, $new_value );
    $this->assertEquals( $meta_id, $check_meta_id );
  }

}
