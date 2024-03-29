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
    $this->assertEquals( file_get_contents(dirname(__FILE__).'/player-data.json'), $output );  
  }  
  
  public function testDBShortcode() {
        
    $output = apply_filters( 'the_content', '[fvplayer id="1"]' );     
    
    $sample = <<< HTML
<div id="wpfp_034c92b7716ddbcf3a90a3a26440386e" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-horizontal" style="max-width: 100%; " data-ratio="0.5625" data-player-id="1">
<div class="fp-ratio" style="padding-top: 56.25%"></div>
  <img class="fp-splash" alt="Fire" src="https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg" />
  <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_some-test-hash">
  <a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/dominika-960-31.mp4","type":"video\/mp4"}],"id":1234,"fv_title":"Fire","splash":"https:\/\/foliovision.com\/video\/burning-hula-hoop-girl-dominika.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg' /></div><h4><span>Fire</span></h4></a>
  <a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Paypal-video-on-home-page.mp4","type":"video\/mp4"}],"id":1234,"fv_title":"PayPal Background Video","splash":"https:\/\/foliovision.com\/videos\/paypal-splash.jpg","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/videos\/paypal-splash.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/videos/paypal-splash.jpg' /></div><h4><span>PayPal Background Video</span></h4></a>
  <a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Carly-Simon-Anticipation-1971.mp4","type":"video\/mp4"}],"id":1234,"fv_title":"Carly Simon","splash":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.png","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/images/2014/01/carly-simon-1971-anticipation.png' /></div><h4><span>Carly Simon</span></h4></a>
  </div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );    
  }
  
  public function testDBShortcodeWithSort() {
        
    $output = apply_filters( 'the_content', '[fvplayer id="1" sort="oldest"]' );
    
    $sample = <<< HTML
<div id="wpfp_3ba46ddc9ef7b689d5aebe50a6555ca5" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-horizontal" style="max-width: 100%; " data-ratio="0.5625" data-player-id="1">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Fire" src="https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_3ba46ddc9ef7b689d5aebe50a6555ca5">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/dominika-960-31.mp4","type":"video\/mp4"}],"id":1,"fv_title":"Fire","splash":"https:\/\/foliovision.com\/video\/burning-hula-hoop-girl-dominika.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg' /></div><h4><span>Fire</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Paypal-video-on-home-page.mp4","type":"video\/mp4"}],"id":2,"fv_title":"PayPal Background Video","splash":"https:\/\/foliovision.com\/videos\/paypal-splash.jpg","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/videos\/paypal-splash.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/videos/paypal-splash.jpg' /></div><h4><span>PayPal Background Video</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Carly-Simon-Anticipation-1971.mp4","type":"video\/mp4"}],"id":3,"fv_title":"Carly Simon","splash":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.png","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/images/2014/01/carly-simon-1971-anticipation.png' /></div><h4><span>Carly Simon</span></h4></a>
	</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    $output = apply_filters( 'the_content', '[fvplayer id="1" sort="newest"]' );
    
    $sample = <<< HTML
<div id="wpfp_06932b2e3a1414f9bf45398a401e055c" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-horizontal" style="max-width: 100%; " data-ratio="0.5625" data-player-id="3">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Carly Simon" src="https://foliovision.com/images/2014/01/carly-simon-1971-anticipation.png" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_06932b2e3a1414f9bf45398a401e055c">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Carly-Simon-Anticipation-1971.mp4","type":"video\/mp4"}],"id":9,"fv_title":"Carly Simon","splash":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.png","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/images/2014/01/carly-simon-1971-anticipation.png' /></div><h4><span>Carly Simon</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Paypal-video-on-home-page.mp4","type":"video\/mp4"}],"id":8,"fv_title":"PayPal Background Video","splash":"https:\/\/foliovision.com\/videos\/paypal-splash.jpg","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/videos\/paypal-splash.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/videos/paypal-splash.jpg' /></div><h4><span>PayPal Background Video</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/dominika-960-31.mp4","type":"video\/mp4"}],"id":7,"fv_title":"Fire","splash":"https:\/\/foliovision.com\/video\/burning-hula-hoop-girl-dominika.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg' /></div><h4><span>Fire</span></h4></a>
	</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    $output = apply_filters( 'the_content', '[fvplayer id="1" sort="title"]' );
    
    $sample = <<< HTML
<div id="wpfp_854a8e2bb53b053ae55cc34018d829b8" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-horizontal" style="max-width: 100%; " data-ratio="0.5625" data-player-id="3">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Carly Simon" src="https://foliovision.com/images/2014/01/carly-simon-1971-anticipation.png" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_854a8e2bb53b053ae55cc34018d829b8">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Carly-Simon-Anticipation-1971.mp4","type":"video\/mp4"}],"id":9,"fv_title":"Carly Simon","splash":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.png","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/images/2014/01/carly-simon-1971-anticipation.png' /></div><h4><span>Carly Simon</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/dominika-960-31.mp4","type":"video\/mp4"}],"id":7,"fv_title":"Fire","splash":"https:\/\/foliovision.com\/video\/burning-hula-hoop-girl-dominika.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg' /></div><h4><span>Fire</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Paypal-video-on-home-page.mp4","type":"video\/mp4"}],"id":8,"fv_title":"PayPal Background Video","splash":"https:\/\/foliovision.com\/videos\/paypal-splash.jpg","subtitles":[{"srclang":"en","label":"English","src":"https:\/\/foliovision.com\/videos\/paypal-splash.vtt"}]}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/videos/paypal-splash.jpg' /></div><h4><span>PayPal Background Video</span></h4></a>
	</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }

  public function testHmsToSeconds() {
    $this->assertEquals(  flowplayer::hms_to_seconds('01:04:11'), 3851 );
  }

  public function testDBStartEnd() {
    global $post;
    $post = get_post( $this->post_id_testStartEnd );
    
    $output = apply_filters( 'the_content', '[fvplayer id="'.$this->import_ids[1].'"]' );
    
    $sample = <<< HTML
<div id="wpfp_f82d3dac387dcb3fa70cbb1614e64a66" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-horizontal" style="max-width: 100%; " data-ratio="0.5625" data-player-id="10">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Fire" src="https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_f82d3dac387dcb3fa70cbb1614e64a66">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/dominika-960-31.mp4","type":"video\/mp4"}],"id":28,"fv_start":10,"fv_end":40,"fv_title":"Fire","splash":"https:\/\/foliovision.com\/video\/burning-hula-hoop-girl-dominika.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/video/burning-hula-hoop-girl-dominika.jpg' /></div><h4><span>Fire</span><i class="dur">00:30</i></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Paypal-video-on-home-page.mp4","type":"video\/mp4"}],"id":29,"fv_start":"5","fv_title":"PayPal Background Video","splash":"https:\/\/foliovision.com\/videos\/paypal-splash.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/videos/paypal-splash.jpg' /></div><h4><span>PayPal Background Video</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/foliovision.com\/videos\/Carly-Simon-Anticipation-1971.mp4","type":"video\/mp4"}],"id":30,"fv_title":"Carly Simon","splash":"https:\/\/foliovision.com\/images\/2014\/01\/carly-simon-1971-anticipation.png"}'><div class='fvp-playlist-thumb-img'><img  src='https://foliovision.com/images/2014/01/carly-simon-1971-anticipation.png' /></div><h4><span>Carly Simon</span><i class="dur">01:04:11</i></h4></a>
	</div>
HTML;

    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );

  }

  protected function tearDown(): void {
    delete_option('fv_player_popups');
  }

}
