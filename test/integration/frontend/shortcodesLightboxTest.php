<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_ShortcodeLightboxTestCase extends FV_Player_UnitTestCase {

  var $shortcode_body = 'src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" playlist="https://cdn.site.com/video2.mp4,https://cdn.site.com/video2.jpg;https://cdn.site.com/video3.mp4,https://cdn.site.com/video3.jpg" caption="Video 1;Video 2;Video 3" share="no" embed="false"';

  var $import_data = array();

  var $import_ids = array();

  protected function setUp(): void {
    FV_Player_lightbox()->clear_lightboxed_players();

    global $FV_Player_Db;
    $this->import_data[] = json_decode( file_get_contents(dirname(__FILE__).'/player-data-youtube.json'), true );

    $this->import_ids[] = $FV_Player_Db->import_player_data( false, false, $this->import_data[0] );

    parent::setUp();
  }

  public function testSimple() {

    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true" share="no" embed="false"]' );

    $this->assertTrue(
      preg_match( '~<div .*?class="freedomplayer lightbox-starter~', $output ) === 1,
      'FV Player "lightbox-starter" class not found'
    );

    $this->assertTrue(
      preg_match( '~<div id=".*?" class="fv_player_lightbox_hidden" style="display: none">~', $output ) === 1,
      'The hidden lightbox container not found'
    );

    $this->assertTrue(
      preg_match( '~<div .*?class="freedomplayer lightboxed~', $output ) === 1,
      'FV Player with "lightboxed" class not found'
    );

    // One video only
    preg_match_all( '~data-item="(.*?)"~', $output, $matches );

    $this->assertTrue( count( $matches[0] ) === 1 );

    $this->assertTrue( stripos( $matches[0][0], 'video1.mp4' ) !== false );

    $this->assertTrue(
      substr_count( $output, 'video1.jpg' ) === 4,
      'FV Player splash must be present 4 times in the markup for a single video. Lightbox starter splash, lightbox thumbnail in data-options, lightbox view and then data-item attribute'
    );
  }


  public function testCaption() {
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;Video 1" share="no" embed="false"]' );

    $this->assertTrue(
      stripos( $output, "title='Video 1'" ) !== false,
      "Lightbox title must match FV Player video title"
    );
  }


  public function testCaptionAndDimensions() {
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;320;240;Video 1" share="no" embed="false"]' );

    $this->assertTrue(
      stripos( $output, "max-width: 320px; max-height: 240px" ) !== false,
      "Lightbox dimension must be set"
    );
  }


  public function testText() {
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" caption="Video 1" lightbox="true;text" share="no" embed="false"]' );
    $sample = <<< HTML
<a data-fancybox='gallery' data-options='{"touch":false}' id="fv_flowplayer_115a93a5af442650797905ae63ef569b_lightbox_starter" title='Video 1' class="fv-player-lightbox-link" href="#" data-src="#wpfp_115a93a5af442650797905ae63ef569b_container">Video 1</a>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );

    remove_action( 'wp_footer', 'the_block_template_skip_link' );

    ob_start();
    do_action('wp_footer');
    $footer = $this->fix_newlines( ob_get_clean() );

    $this->assertTrue(
      preg_match( '~<div id=".*?" class="fv_player_lightbox_hidden" style="display: none">~', $footer ) === 1,
      'The hidden lightbox container should be in the footer'
    );

    $this->assertTrue(
      preg_match( '~<div.*?class="freedomplayer lightboxed~', $footer ) === 1,
      'FV Player with "lightboxed" class not found in footer'
    );

    // Playlist items need to match
    preg_match_all( '~data-item="(.*?)"~', $footer, $matches );

    $this->assertTrue( count( $matches[0] ) === 1 );

    $this->assertTrue( stripos( $matches[0][0], 'video1.mp4' ) !== false );

    $this->assertTrue( stripos( $footer, 'var fv_player_lightbox = {' ) !== false );
    $this->assertTrue( stripos( $footer, 'let fv_player_fancybox_loaded = false;' ) !== false );

    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }


  public function testPlaylist() {
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true"]' );

    $this->assertTrue(
      preg_match( '~<div id=".*?" class="fv_player_lightbox_hidden" style="display: none">~', $output ) === 1,
      'The hidden lightbox container not found'
    );

    $this->assertTrue(
      preg_match( '~<div.*?class="freedomplayer lightboxed~', $output ) === 1,
      'FV Player with "lightboxed" class not found'
    );

    $this->assertTrue(
      stripos( $output, 'fv-playlist-slider-wrapper' ) !== false && stripos( $output, 'has-playlist-slider' ) !== false,
      'FV Player playlist in lightbox must use the slider playlist style'
    );

    $this->assertTrue(
      substr_count( $output, 'fvp-playlist-thumb-img' ) === 6,
      'FV Player playlist in lightbox must show all the playlist thumbs both as the lightbox starter and then in the actual lightbox view'
    );

    // Playlist items need to match
    preg_match_all( "~data-item='(.*?)'~", $output, $matches );

    $this->assertTrue( count( $matches[0] ) === 3 );

    $this->assertTrue( stripos( $matches[0][0], 'video1.mp4' ) !== false );

    $this->assertTrue( stripos( $matches[0][1], 'video2.mp4' ) !== false );

    $this->assertTrue( stripos( $matches[0][2], 'video3.mp4' ) !== false );

    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?

    // setting liststyle shouldn't affect anything!
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true" liststyle="slider"]' );

    $this->assertTrue(
      stripos( $output, 'fv-playlist-slider-wrapper' ) !== false && stripos( $output, 'has-playlist-slider' ) !== false,
      'FV Player playlist in lightbox must use the slider playlist style even if some other playlist style is selected'
    );
  }


  public function testPlaylistText() {
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true;text"]' );
    $sample = <<< HTML
<ul class="fv-player-lightbox-text-playlist" rel="wpfp_338ee74dbab365544f456c8327b33616_container"><li><a data-fancybox='gallery' data-options='{"touch":false}' href="#wpfp_338ee74dbab365544f456c8327b33616_container" class="fv-player-lightbox-link" title="Video 1">Video 1</li><li><a href="#" class="fv-player-lightbox-link" title="Video 2">Video 2</li><li><a href="#" class="fv-player-lightbox-link" title="Video 3">Video 3</li></ul>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );

    remove_action( 'wp_footer', 'the_block_template_skip_link' );

    ob_start();
    do_action('wp_footer');
    $footer = ob_get_clean();

    $this->assertTrue(
      preg_match( '~<div id=".*?" class="fv_player_lightbox_hidden" style="display: none">~', $footer ) === 1,
      'The hidden lightbox container should be in the footer'
    );

    $this->assertTrue(
      preg_match( '~<div.*?class="freedomplayer lightboxed~', $footer ) === 1,
      'FV Player with "lightboxed" class not found in footer'
    );

    // Playlist items need to match
    preg_match_all( "~data-item='(.*?)'~", $footer, $matches );

    $this->assertTrue( count( $matches[0] ) === 3 );

    $this->assertTrue( stripos( $matches[0][0], 'video1.mp4' ) !== false );

    $this->assertTrue( stripos( $matches[0][1], 'video2.mp4' ) !== false );

    $this->assertTrue( stripos( $matches[0][2], 'video3.mp4' ) !== false );

    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }

  public function testDB() {

    $output = apply_filters( 'the_content', '[fvplayer id="' . intval( $this->import_ids[0] ) . '" lightbox="true"]' );

    // Check for lightbox thumbnail
    $this->assertTrue(
      preg_match( '~<div.*?class="freedomplayer lightbox-starter~', $output ) === 1,
      'FV Player "lightbox-starter" class not found'
    );

    // Check for the hidden player div
    $this->assertTrue(
      preg_match( '~<div id=".*?" class="fv_player_lightbox_hidden" style="display: none">~', $output ) === 1,
      'The hidden lightbox container not found'
    );

    $this->assertTrue(
      preg_match( '~<div.*?class="freedomplayer lightboxed~', $output ) === 1,
      'FV Player with "lightboxed" class not found'
    );

    // Match the player data in HTML
    preg_match_all( '~data-item="(.*?)"~', $output, $matches );

    // There should be one video
    $this->assertTrue( count( $matches[0] ) === 1 );

    $json = html_entity_decode( $matches[1][0] );
    $obj = json_decode( $json );

    // ensure the json_decode suceeded
    $this->assertTrue( ! empty( $obj->sources[0]->src ) );

    // Make sure proper video URL is in place
    $this->assertTrue( strcmp( $obj->sources[0]->src, $this->import_data[0]['videos'][0]['src'] ) === 0 );

    // Make sure proper splash image is in place
    $this->assertTrue(
      substr_count( $output, ' src="' . esc_attr( $this->import_data[0]['videos'][0]['splash'] ) . '"' ) === 2,
      'FV Player splash URL must be used in <img /> 2 times in the markup for a single video. Lightbox starter splash and lightbox view splash'
    );
  }

  public function testDBText() {

    $output = apply_filters( 'the_content', '[fvplayer id="' . intval( $this->import_ids[0] ) . '" lightbox="true;text"]' );

    // The output must be just the link
    preg_match_all( "~^<a data-fancybox='gallery'[^>]*?>(.*)?</a>$~", $output, $matches );

    $this->assertTrue( count( $matches[1] ) === 1 );

    $this->assertTrue( strcmp( $matches[1][0], $this->import_data[0]['videos'][0]['title'] ) === 0 );

    // Match the player data in footer HTML
    remove_action( 'wp_footer', 'the_block_template_skip_link' );
    ob_start();
    do_action('wp_footer');
    $footer = $this->fix_newlines( ob_get_clean() );

    preg_match_all( '~data-item="(.*?)"~', $footer, $matches );

    // There should be one video
    $this->assertTrue( count( $matches[0] ) === 1 );

    $json = html_entity_decode( $matches[1][0] );
    $obj = json_decode( $json );

    // ensure the json_decode suceeded
    $this->assertTrue( ! empty( $obj->sources[0]->src ) );

    // Make sure proper video URL is in place
    $this->assertTrue( strcmp( $obj->sources[0]->src, $this->import_data[0]['videos'][0]['src'] ) === 0 );

    // Make sure proper splash image is in place
    $this->assertTrue(
      substr_count( $footer, ' src="' . esc_attr( $this->import_data[0]['videos'][0]['splash'] ) . '"' ) === 1,
      'FV Player splash URL must be used in <img /> 1 times in the markup for a single video with text lightbox. Lightbox starter splash and lightbox view splash'
    );
  }

  public function testDBTextCustomCaption() {

    $custom_title = 'Custom Title';

    $output = apply_filters( 'the_content', '[fvplayer id="' . intval( $this->import_ids[0] ) . '" lightbox="true;text" caption="' . esc_attr( $custom_title ) . '"]' );

    // The output must be just anchor with title
    preg_match_all( "~^<a data-fancybox='gallery'[^>]*?>(.*)?</a>$~", $output, $matches );

    $this->assertTrue( count( $matches[1] ) === 1 );

    $this->assertTrue( strcmp( $matches[1][0], $custom_title ) === 0 );
  }

  // TODO: This does not pass, but it seems it should be possible to override the video title like this:
  /*public function testDBTextCustomTitle() {

    $custom_title = 'Custom Title';

    $output = apply_filters( 'the_content', '[fvplayer id="' . intval( $this->import_ids[0] ) . '" lightbox="true;text" title="' . esc_attr( $custom_title ) . '"]' );

    // The output must be just anchor with title
    preg_match_all( "~^<a data-fancybox='gallery'[^>]*?>(.*)?</a>$~", $output, $matches );

    $this->assertTrue( count( $matches[1] ) === 1 );

    $this->assertTrue( strcmp( $matches[1][0], $custom_title ) === 0 );
  }*/
}
