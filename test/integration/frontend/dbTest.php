<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_DBTest extends FV_Player_UnitTestCase {
  
  var $import_ids = array();
  private $post_id_testEndActions;
  private $post_id_testStartEnd;

  protected function setUp(): void {
    parent::setUp();

    require_once "../../../fv-wordpress-flowplayer/controller/editor.php";

    global $FV_Player_Db;
    $this->import_ids[] = $FV_Player_Db->import_player_data( false, false, json_decode( file_get_contents(dirname(__FILE__).'/player-data.json'), true) );
    $this->import_ids[] = $FV_Player_Db->import_player_data( false, false, json_decode( file_get_contents(dirname(__FILE__).'/player-data-start-end.json'), true) );

    // create a post with playlist shortcode
    $this->post_id_testEndActions= $this->factory->post->create( array(
      'post_title' => 'End Action Test',
      'post_content' => '[fvplayer src="https://cdn.site.com/video.mp4"]'
    ) );
    
    $this->post_id_testStartEnd = $this->factory->post->create( array(
      'post_title' => 'Custom Start End Test',
      'post_content' => '[fvplayer id="'.$this->import_ids[1].'"]'
    ) );

    // if we don't load something with a [fvplayer] shortcode in it it won't know to load CSS in header!
    global $post;
    $post = get_post( $this->post_id_testEndActions );
    $post->ID = 1234;
    
    // we remove header stuff which we don't want to test
    remove_action('wp_head', 'wp_generator');
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    add_filter( 'wp_resource_hints', '__return_empty_array' );
    wp_deregister_script( 'wp-embed' );
    
  }
  
  public function testDBExport() {
    global $FV_Player_Db;        
    $output = json_encode( $FV_Player_Db->export_player_data(false,false,1), JSON_UNESCAPED_SLASHES );

    // Compare two JSONs - we replace the last_check as it might differ and also decore and print_r them to make it readable
    $this->assertEquals(
      print_r(
        json_decode(
          preg_replace(
            '~"last_check":".*?"~',
            '"last_check":"some-date-was-here"',
            file_get_contents(dirname(__FILE__).'/player-data-export.json')
          )
        ),
        true
      ),
      print_r(
        json_decode(
          preg_replace(
            '~"last_check":".*?"~',
            '"last_check":"some-date-was-here"',
            $output
          )
        ),
        true
      )
    );  
  }  
  
  public function testDBShortcode() {
        
    $output = apply_filters( 'the_content', '[fvplayer id="1"]' );

    // Three videos
    $this->assertTrue(
      3 === preg_match_all( "~data-item='(.*?)'~", $output, $data_items )
    );

    // Verify video order
    $data_item = json_decode( html_entity_decode( $data_items[1][0] ) );
    $this->assertTrue( 'https://foliovision.com/videos/dominika-960-31.mp4' === $data_item->sources[0]->src );

    $data_item = json_decode( html_entity_decode( $data_items[1][1] ) );
    $this->assertTrue( 'https://foliovision.com/videos/Paypal-video-on-home-page.mp4' === $data_item->sources[0]->src );

    $data_item = json_decode( html_entity_decode( $data_items[1][2] ) );
    $this->assertTrue( 'https://foliovision.com/videos/Carly-Simon-Anticipation-1971.mp4' === $data_item->sources[0]->src );   
  }
  
  public function testDBShortcodeWithSort() {

    // Oldest first
    $output = apply_filters( 'the_content', '[fvplayer id="1" sort="oldest"]' );

    // Three videos
    $this->assertTrue(
      3 === preg_match_all( "~data-item='(.*?)'~", $output, $data_items )
    );

    // Verify video order
    $data_item = json_decode( html_entity_decode( $data_items[1][0] ) );
    $this->assertTrue( 'https://foliovision.com/videos/dominika-960-31.mp4' === $data_item->sources[0]->src );

    $data_item = json_decode( html_entity_decode( $data_items[1][1] ) );
    $this->assertTrue( 'https://foliovision.com/videos/Paypal-video-on-home-page.mp4' === $data_item->sources[0]->src );

    $data_item = json_decode( html_entity_decode( $data_items[1][2] ) );
    $this->assertTrue( 'https://foliovision.com/videos/Carly-Simon-Anticipation-1971.mp4' === $data_item->sources[0]->src );

    // Newest first
    $output = apply_filters( 'the_content', '[fvplayer id="1" sort="newest"]' );

    // Three videos
    $this->assertTrue(
      3 === preg_match_all( "~data-item='(.*?)'~", $output, $data_items )
    );

    // Verify video order
    $data_item = json_decode( html_entity_decode( $data_items[1][0] ) );
    $this->assertTrue( 'https://foliovision.com/videos/Carly-Simon-Anticipation-1971.mp4' === $data_item->sources[0]->src );

    $data_item = json_decode( html_entity_decode( $data_items[1][1] ) );
    $this->assertTrue( 'https://foliovision.com/videos/Paypal-video-on-home-page.mp4' === $data_item->sources[0]->src );

    $data_item = json_decode( html_entity_decode( $data_items[1][2] ) );
    $this->assertTrue( 'https://foliovision.com/videos/dominika-960-31.mp4' === $data_item->sources[0]->src );

    // Sort by title
    $output = apply_filters( 'the_content', '[fvplayer id="1" sort="title"]' );

    // Three videos
    $this->assertTrue(
      3 === preg_match_all( "~data-item='(.*?)'~", $output, $data_items )
    );

    // Verify video order
    $data_item = json_decode( html_entity_decode( $data_items[1][0] ) );
    $this->assertTrue( 'Carly Simon' === $data_item->fv_title );

    $data_item = json_decode( html_entity_decode( $data_items[1][1] ) );
    $this->assertTrue( 'Fire' === $data_item->fv_title );

    $data_item = json_decode( html_entity_decode( $data_items[1][2] ) );
    $this->assertTrue( 'PayPal Background Video' === $data_item->fv_title );
  }

  public function testHmsToSeconds() {
    $this->assertEquals(  flowplayer::hms_to_seconds('01:04:11'), 3851 );
  }

  public function testDBStartEnd() {
    global $post;
    $post = get_post( $this->post_id_testStartEnd );
    
    $output = apply_filters( 'the_content', '[fvplayer id="'.$this->import_ids[1].'"]' );

    // Three videos
    $this->assertTrue(
      3 === preg_match_all( "~data-item='(.*?)'~", $output, $data_items )
    );
      
    // First video should start at 00:10 and end at 00:40 and report as 00:30 long
    $data_item = json_decode( html_entity_decode( $data_items[1][0] ) );
    $this->assertTrue( 10 === $data_item->fv_start );
    $this->assertTrue( 40 === $data_item->fv_end );
    $this->assertTrue( stripos( $output, '<i class="dur">00:30</i>') !== false );

    // Second video should start at 00:05 and report stored duration
    $data_item = json_decode( html_entity_decode( $data_items[1][1] ) );
    $this->assertTrue( "5" === $data_item->fv_start );
    $this->assertTrue( stripos( $output, '<i class="dur">01:04:11</i>') !== false );
  }

  protected function tearDown(): void {
    delete_option('fv_player_popups');
  }

}
