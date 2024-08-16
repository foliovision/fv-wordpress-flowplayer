<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_DBTest extends FV_Player_UnitTestCase {

  var $import_data = array();

  var $import_ids = array();

  private $post_id_testEndActions;
  private $post_id_testStartEnd;

  protected function setUp(): void {
    parent::setUp();

    // Need to use the back-end controller to import the player data
    require_once "../../../fv-wordpress-flowplayer/controller/editor.php";

    global $FV_Player_Db;
    $this->import_data[] = json_decode( file_get_contents(dirname(__FILE__).'/player-data.json'), true );
    $this->import_data[] = json_decode( file_get_contents(dirname(__FILE__).'/player-data-start-end.json'), true );

    $this->import_ids[] = $FV_Player_Db->import_player_data( false, false, $this->import_data[0] );
    $this->import_ids[] = $FV_Player_Db->import_player_data( false, false, $this->import_data[1] );

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

  /*public function testDBExport() {
    global $FV_Player_Db;
    $output = json_encode( $FV_Player_Db->export_player_data(false,false, $this->import_ids[0] ), JSON_UNESCAPED_SLASHES );
    $this->assertEquals( trim( file_get_contents(dirname(__FILE__).'/player-data.json') ), $output );
  }*/

  public function testDBShortcode() {
    $output = apply_filters( 'the_content', '[fvplayer id="' . $this->import_ids[0] . '"]' );

    // Player ID needs to match
    $this->assertTrue( stripos( $output, 'data-player-id="' . $this->import_ids[0] . '"' ) !== false );

    // Each playlist video source needs to match the JSON
    preg_match_all( "~data-item='(.*?)'~", $output, $matches );

    $videos_json = $this->import_data[0]['videos'];

    foreach( $matches[1] as $k => $playlist_item_raw ) {
      $playlist_item = json_decode( $playlist_item_raw );

      // Video link
      $this->assertTrue( strcmp( $playlist_item->sources[0]->src, $videos_json[ $k ]['src'] ) === 0 );

      // Video title
      $this->assertTrue( strcmp( $playlist_item->fv_title, $videos_json[ $k ]['title'] ) === 0 );

      // Video splash
      $this->assertTrue( strcmp( $playlist_item->splash, $videos_json[ $k ]['splash'] ) === 0 );

      // Subtitles need to be present if they were in the import JSON
      if ( ! empty( $videos_json[ $k ]['meta'] ) ) {
        foreach( $videos_json[ $k ]['meta'] as $meta ) {
          if ( stripos( $meta['meta_key'], 'subtitles' ) === 0 ) {
            $this->assertTrue( ! empty( $playlist_item->subtitles ) );
          }
        }
      }
    }
  }

  public function testDBShortcodeWithPrefer() {
    $output = apply_filters( 'the_content', '[fvplayer id="' . $this->import_ids[0] . '" prefer="webm"]' );

    // Player ID needs to match
    $this->assertTrue( stripos( $output, 'data-player-id="' . $this->import_ids[0] . '"' ) !== false );

    // Each playlist video source needs to match the JSON
    preg_match_all( "~data-item='(.*?)'~", $output, $matches );

    $videos_json = $this->import_data[0]['videos'];

    foreach( $matches[1] as $k => $playlist_item_raw ) {
      $playlist_item = json_decode( $playlist_item_raw );

      // If second video sources is available and it's webm, then it should be the first one now
      if ( ! empty( $videos_json[ $k ]['src1'] ) && stripos( $videos_json[ $k ]['src1'], '.webm' ) != false ) {
        $this->assertTrue( strcmp( $playlist_item->sources[0]->src, $videos_json[ $k ]['src1'] ) === 0 );

        // ...and first source should be second
        $this->assertTrue( strcmp( $playlist_item->sources[1]->src, $videos_json[ $k ]['src'] ) === 0 );

      } else {
        $this->assertTrue( strcmp( $playlist_item->sources[0]->src, $videos_json[ $k ]['src'] ) === 0 );
      }

      // Video title
      $this->assertTrue( strcmp( $playlist_item->fv_title, $videos_json[ $k ]['title'] ) === 0 );

      // Video splash
      $this->assertTrue( strcmp( $playlist_item->splash, $videos_json[ $k ]['splash'] ) === 0 );

      // Subtitles need to be present if they were in the import JSON
      if ( ! empty( $videos_json[ $k ]['meta'] ) ) {
        foreach( $videos_json[ $k ]['meta'] as $meta ) {
          if ( stripos( $meta['meta_key'], 'subtitles' ) === 0 ) {
            $this->assertTrue( ! empty( $playlist_item->subtitles ) );
          }
        }
      }
    }
  }

  public function testDBShortcodeWithSort() {

    $output = apply_filters( 'the_content', '[fvplayer id="' . $this->import_ids[0] . '" sort="oldest"]' );

    // Player ID needs to match
    $this->assertTrue( stripos( $output, 'data-player-id="' . $this->import_ids[0] . '"' ) !== false );

    // Each playlist video source needs to match the JSON
    preg_match_all( "~data-item='(.*?)'~", $output, $matches );

    $videos_json = $this->import_data[0]['videos'];

    foreach( $matches[1] as $k => $playlist_item_raw ) {
      $playlist_item = json_decode( $playlist_item_raw );

      // Video link
      $this->assertTrue( strcmp( $playlist_item->sources[0]->src, $videos_json[ $k ]['src'] ) === 0 );

      // Video title
      $this->assertTrue( strcmp( $playlist_item->fv_title, $videos_json[ $k ]['title'] ) === 0 );

      // Video splash
      $this->assertTrue( strcmp( $playlist_item->splash, $videos_json[ $k ]['splash'] ) === 0 );

      // Subtitles need to be present if they were in the import JSON
      if ( ! empty( $this->import_data[0]['videos'][ $k ]['meta'] ) ) {
        foreach( $this->import_data[0]['videos'][ $k ]['meta'] as $meta ) {
          if ( stripos( $meta['meta_key'], 'subtitles' ) === 0 ) {
            $this->assertTrue( ! empty( $playlist_item->subtitles ) );
          }
        }
      }
    }

    $output = apply_filters( 'the_content', '[fvplayer id="' . $this->import_ids[0] . '" sort="newest"]' );

    // Player ID needs to match
    $this->assertTrue( stripos( $output, 'data-player-id="' . $this->import_ids[0] . '"' ) !== false );

    // Each playlist video source needs to match the JSON
    preg_match_all( "~data-item='(.*?)'~", $output, $matches );

    // Reverse the videos JSON so we can compare it to the newest sort
    $videos_json = array_reverse( $this->import_data[0]['videos'] );

    foreach( $matches[1] as $k => $playlist_item_raw ) {
      $playlist_item = json_decode( $playlist_item_raw );

      // Video link
      $this->assertTrue( strcmp( $playlist_item->sources[0]->src, $videos_json[ $k ]['src'] ) === 0 );

      // Video title
      $this->assertTrue( strcmp( $playlist_item->fv_title, $videos_json[ $k ]['title'] ) === 0 );

      // Video splash
      $this->assertTrue( strcmp( $playlist_item->splash, $videos_json[ $k ]['splash'] ) === 0 );

      // Subtitles need to be present if they were in the import JSON
      if ( ! empty( $videos_json[ $k ]['meta'] ) ) {
        foreach( $videos_json[ $k ]['meta'] as $meta ) {
          if ( stripos( $meta['meta_key'], 'subtitles' ) === 0 ) {
            $this->assertTrue( ! empty( $playlist_item->subtitles ) );
          }
        }
      }
    }

    $output = apply_filters( 'the_content', '[fvplayer id="' . $this->import_ids[0] . '" sort="title"]' );

    // Each playlist video source needs to match the JSON
    preg_match_all( "~data-item='(.*?)'~", $output, $matches );

    $videos_json = array_reverse( $this->import_data[0]['videos'] );

    // Sort by title
    usort(
      $videos_json,
      function( $a, $b ) {
        return strcmp( $a['title'], $b['title'] );
      }
    );

    foreach( $matches[1] as $k => $playlist_item_raw ) {
      $playlist_item = json_decode( $playlist_item_raw );

      // Video link
      $this->assertTrue( strcmp( $playlist_item->sources[0]->src, $videos_json[ $k ]['src'] ) === 0 );

      // Video title
      $this->assertTrue( strcmp( $playlist_item->fv_title, $videos_json[ $k ]['title'] ) === 0 );

      // Video splash
      $this->assertTrue( strcmp( $playlist_item->splash, $videos_json[ $k ]['splash'] ) === 0 );

      // Subtitles need to be present if they were in the import JSON
      if ( ! empty( $videos_json[ $k ]['meta'] ) ) {
        foreach( $videos_json[ $k ]['meta'] as $meta ) {
          if ( stripos( $meta['meta_key'], 'subtitles' ) === 0 ) {
            $this->assertTrue( ! empty( $playlist_item->subtitles ) );
          }
        }
      }
    }
  }

  public function testHmsToSeconds() {
    $this->assertEquals(  flowplayer::hms_to_seconds('01:04:11'), 3851 );
  }

  public function testDBStartEnd() {
    global $post;
    $post = get_post( $this->post_id_testStartEnd );

    $output = apply_filters( 'the_content', '[fvplayer id="'.$this->import_ids[1].'"]' );

    // Player ID needs to match
    $this->assertTrue( stripos( $output, 'data-player-id="' . $this->import_ids[1] . '"' ) !== false );

    // Each playlist video source needs to match the JSON
    preg_match_all( "~data-item='(.*?)'~", $output, $matches );

    // Match playlist item HTML
    preg_match_all( '~<a.*?</a>~', $output, $item_anchors );

    $videos_json = $this->import_data[1]['videos'];

    foreach( $matches[1] as $k => $playlist_item_raw ) {
      $playlist_item = json_decode( $playlist_item_raw );

      // Video link
      $this->assertTrue( strcmp( $playlist_item->sources[0]->src, $videos_json[ $k ]['src'] ) === 0 );

      // Video title
      $this->assertTrue( strcmp( $playlist_item->fv_title, $videos_json[ $k ]['title'] ) === 0 );

      // Video splash
      $this->assertTrue( strcmp( $playlist_item->splash, $videos_json[ $k ]['splash'] ) === 0 );

      // Subtitles need to be present if they were in the import JSON
      if ( ! empty( $videos_json[ $k ]['meta'] ) ) {
        foreach( $videos_json[ $k ]['meta'] as $meta ) {
          if ( stripos( $meta['meta_key'], 'subtitles' ) === 0 ) {
            $this->assertTrue( ! empty( $playlist_item->subtitles ) );
          }
        }
      }

      // If video has start and end time, then the duration must be in HTML
      if ( ! empty( $videos_json[ $k ]['start'] ) && ! empty( $videos_json[ $k ]['end'] ) ) {
        $duration = flowplayer::hms_to_seconds( $videos_json[ $k ]['end'] ) - flowplayer::hms_to_seconds( $videos_json[ $k ]['start'] );
        // The duration of the video must be in HTML
        $this->assertTrue( stripos( $item_anchors[0][ $k ], '<i class="dur">' . flowplayer::format_hms( $duration )  . '</i>' ) !== false );
      }

      // TODO: Also test if the video has duration meta and duration and start and just end time set
    }
  }

  protected function tearDown(): void {
    delete_option('fv_player_popups');
  }

}
