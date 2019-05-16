<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_Video_SitemapTest extends FV_Player_UnitTestCase {
  
  var $import_ids = array();
  
  public function setUp() {
    parent::setUp();
        
    global $FV_Player_Db;
    $this->import_ids[] = $FV_Player_Db->import_player_data( false, false, json_decode( file_get_contents(dirname(__FILE__).'/player-data.json'), true) );
    $this->import_ids[] = $FV_Player_Db->import_player_data( false, false, json_decode( file_get_contents(dirname(__FILE__).'/player-data-youtube.json'), true) );
    
    // create a post with playlist shortcode
    $this->post_id_testEndActions= $this->factory->post->create( array(
      'post_title' => 'Video Sitemap Test',
      'post_content' => <<< HTML
Here is the intro paragraph
      
[fvplayer src="https://cdn.site.com/video.mp4"]

Some video with embed disabled
      
[fvplayer src="https://cdn.site.com/video.mp4" embed="false"]

Let's try a YouTube video:

[fvplayer src="https://www.youtube.com/watch?v=Rb0UmrCXxVA"]

Paragraph after first player

[fvplayer id="1"]

Paragraph after second player

[fvplayer src="https://cdn.site.com/video-2.mp4"]

Paragraph after third player

[fvplayer id="2"]
HTML
    ) );
    
  }
  
  public function testVideoSitemap() {
    
    ob_start();
    global $FV_Xml_Video_Sitemap;    
    $FV_Xml_Video_Sitemap->fv_generate_video_sitemap_do( date('Y'), date('m') );
    $output = ob_get_clean();
    
    $this->assertEquals( $this->fix_newlines( file_get_contents(dirname(__FILE__).'/video-sitemap.xml') ), $this->fix_newlines($output) );      
  }
  
  public function tearDown() {
    global $FV_Player_Db;
    
    // when you delete a player loaded from cache it won't remove the player and player meta, so we do a hard cache purge here! The player ID is not passed in contructor when loading from cache.
    $FV_Player_Db->setPlayersCache( array() );
    $FV_Player_Db->setPlayerMetaCache( array() );
    $FV_Player_Db->setVideosCache( array() );
    $FV_Player_Db->setVideoMetaCache( array() );
    
    foreach( $this->import_ids AS $id ) {
      $player = new FV_Player_Db_Player( $id, array() );
      $player->delete();
    }
  }

}
