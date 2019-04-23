<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_Video_SitemapTest extends FV_Player_UnitTestCase {
  
  public function setUp() {
    parent::setUp();
        
    global $FV_Player_Db;
    $FV_Player_Db->import_player_data( false, false, json_decode( file_get_contents(dirname(__FILE__).'/player-data.json'), true) );

    // create a post with playlist shortcode
    $this->post_id_testEndActions= $this->factory->post->create( array(
      'post_title' => 'Video Sitemap Test',
      'post_content' => <<< HTML
Here is the intro paragraph
      
[fvplayer src="https://cdn.site.com/video.mp4"]

Paragraph after first player

[fvplayer id="1"]

Paragraph after second player

[fvplayer src="https://cdn.site.com/video-2.mp4"]

Paragraph after third player
HTML
    ) );
    
  }
  
  public function testVideoSitemap() {
    
    ob_start();
    global $FV_Xml_Video_Sitemap;    
    $FV_Xml_Video_Sitemap->fv_generate_video_sitemap_do( date('Y'), date('m') );die();
    $output = ob_get_clean();
    
    $this->assertEquals( $this->fix_newlines( file_get_contents(dirname(__FILE__).'/video-sitemap.xml') ), $this->fix_newlines($output) );      
  }
  
  public function tearDown() {
    //delete_option('fv_player_popups');
  }

}
