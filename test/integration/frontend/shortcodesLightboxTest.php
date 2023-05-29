<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_ShortcodeLightboxTestCase extends FV_Player_UnitTestCase {
  
  var $shortcode_body = 'src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" playlist="https://cdn.site.com/video2.mp4,https://cdn.site.com/video2.jpg;https://cdn.site.com/video3.mp4,https://cdn.site.com/video3.jpg" caption="Video 1;Video 2;Video 3" share="no" embed="false"';

  public function testSimple() {
    
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true" share="no" embed="false"]' );
    
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_f52c072c04a7fee87b04a344ca2d7105_lightbox_starter" href="#wpfp_f52c072c04a7fee87b04a344ca2d7105_container" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>

<div id="wpfp_f52c072c04a7fee87b04a344ca2d7105_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_f52c072c04a7fee87b04a344ca2d7105" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  
  public function testCaption() {
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;Video 1" share="no" embed="false"]' );
    
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_be42e434aab187774826fdeb4b5bb1d1_lightbox_starter" title='Video 1' href="#wpfp_be42e434aab187774826fdeb4b5bb1d1_container" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>

<div id="wpfp_be42e434aab187774826fdeb4b5bb1d1_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_be42e434aab187774826fdeb4b5bb1d1" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  
  public function testCaptionAndDimensions() {    
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;320;240;Video 1" share="no" embed="false"]' );
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_479b52100d6888fc6ac4eee16841076f_lightbox_starter" title='Video 1' href="#wpfp_479b52100d6888fc6ac4eee16841076f_container" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 320px; max-height: 240px; " data-ratio="0.75">
	<div class="fp-ratio" style="padding-top: 75%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>

<div id="wpfp_479b52100d6888fc6ac4eee16841076f_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_479b52100d6888fc6ac4eee16841076f" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );      
  }
  
  
  public function testText() {    
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" caption="Video 1" lightbox="true;text" share="no" embed="false"]' );
    $sample = <<< HTML
<a data-fancybox='gallery' data-options='{"touch":false}' id="fv_flowplayer_115a93a5af442650797905ae63ef569b_lightbox_starter" title='Video 1' class="fv-player-lightbox-link" href="#" data-src="#wpfp_115a93a5af442650797905ae63ef569b_container">Video 1</a>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );


    // Avoid more CSS files which WordPress started to include as the block themes become the new default
    add_action(
      'wp_footer',
      function() {
        // inline style with "Core styles: block-supports"
        wp_dequeue_style( 'core-block-supports' );
      }
    );
    
    ob_start();
    do_action('wp_footer');
    $footer = ob_get_clean();
    
    $sample = <<< HTML

<div id="wpfp_a1c867a00a9b9024ba559b457efca492_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_a1c867a00a9b9024ba559b457efca492" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;fv_title&quot;:&quot;Video 1&quot;,&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>
<!-- lightboxed players -->

<link rel='stylesheet' id='fv_player_lightbox-css' href='http://example.org/wp-content/plugins/fv-wordpress-flowplayer/css/fancybox.css?ver=7.5.30.7212' type='text/css' media='all' />
<script type='text/javascript' id='fv_player_lightbox-js-extra'>
/* <![CDATA[ */
var fv_player_lightbox = {"lightbox_images":""};
/* ]]> */
</script>
<script type='text/javascript' src='http://example.org/wp-content/plugins/fv-wordpress-flowplayer/js/fancybox.js?ver=7.5.30.7212' id='fv_player_lightbox-js'></script>
      <style type="text/css">
    .flowplayer.skin-slim { background-color: #000000 !important; }
.flowplayer.skin-slim .fp-color, .flowplayer.skin-slim .fp-selected, .fp-playlist-external.skin-slim .fvp-progress { background-color: #bb0000 !important; }
.flowplayer.skin-slim .fp-color-fill .svg-color, .flowplayer.skin-slim .fp-color-fill svg.fvp-icon, .flowplayer.skin-slim .fp-color-fill { fill: #bb0000 !important; color: #bb0000 !important; }
.flowplayer.skin-slim .fp-controls, .fv-player-buttons a:active, .fv-player-buttons a { background-color: transparent !important; }
.flowplayer.skin-slim .fp-elapsed, .flowplayer.skin-slim .fp-duration { color: #ffffff !important; }
.flowplayer.skin-slim .fv-player-video-checker { color: #ffffff !important; }
.flowplayer.skin-slim .fp-bar span.chapter_passed{ background-color: #bb0000 !important; }
.fv-player-buttons a.current { background-color: #bb0000 !important; }
#content .flowplayer.skin-slim, .flowplayer.skin-slim { font-family: Tahoma, Geneva, sans-serif; }
.flowplayer.skin-slim .fp-dropdown li.active { background-color: #bb0000 !important }
.flowplayer.skin-youtuby { background-color: #000000 !important; }
.flowplayer.skin-youtuby .fp-color, .flowplayer.skin-youtuby .fp-selected, .fp-playlist-external.skin-youtuby .fvp-progress { background-color: #bb0000 !important; }
.flowplayer.skin-youtuby .fp-color-fill .svg-color, .flowplayer.skin-youtuby .fp-color-fill svg.fvp-icon, .flowplayer.skin-youtuby .fp-color-fill { fill: #bb0000 !important; color: #bb0000 !important; }
.flowplayer.skin-youtuby .fp-controls, .fv-player-buttons a:active, .fv-player-buttons a { background-color: rgba(0, 0, 0, 0.5) !important; }
.flowplayer.skin-youtuby .fp-elapsed, .flowplayer.skin-youtuby .fp-duration { color: #ffffff !important; }
.flowplayer.skin-youtuby .fv-player-video-checker { color: #ffffff !important; }
.flowplayer.skin-youtuby .fv-ab-loop { background-color: rgba(0, 0, 0, 0.5) !important; }
.flowplayer.skin-youtuby .fv_player_popup, .fvfp_admin_error_content {  background: rgba(0, 0, 0, 0.5); }
.flowplayer.skin-youtuby .fp-bar span.chapter_passed{ background-color: #bb0000 !important; }
.fv-player-buttons a.current { background-color: #bb0000 !important; }
#content .flowplayer.skin-youtuby, .flowplayer.skin-youtuby { font-family: Tahoma, Geneva, sans-serif; }
.flowplayer.skin-youtuby .fp-dropdown li.active { background-color: #bb0000 !important }
.flowplayer.skin-custom { background-color: #000000 !important; }
.flowplayer.skin-custom .fp-color, .flowplayer.skin-custom .fp-selected, .fp-playlist-external.skin-custom .fvp-progress { background-color: #bb0000 !important; }
.flowplayer.skin-custom .fp-color-fill .svg-color, .flowplayer.skin-custom .fp-color-fill svg.fvp-icon, .flowplayer.skin-custom .fp-color-fill { fill: #bb0000 !important; color: #bb0000 !important; }
.flowplayer.skin-custom .fp-controls, .fv-player-buttons a:active, .fv-player-buttons a { background-color: #333333 !important; }
.flowplayer.skin-custom a.fp-play, .flowplayer.skin-custom a.fp-volumebtn, .flowplayer.skin-custom .fp-controls, .flowplayer.skin-custom .fv-ab-loop, .fv-player-buttons a:active, .fv-player-buttons a { color: #eeeeee !important; }
.flowplayer.skin-custom .fp-controls > .fv-fp-prevbtn:before, .flowplayer.skin-custom .fp-controls > .fv-fp-nextbtn:before { border-color: #eeeeee !important; }
.flowplayer.skin-custom .fvfp_admin_error, .flowplayer.skin-custom .fvfp_admin_error a, #content .flowplayer.skin-custom .fvfp_admin_error a { color: #eeeeee; }
.flowplayer.skin-custom svg.fvp-icon { fill: #eeeeee !important; }
.flowplayer.skin-custom .fp-volumeslider, .flowplayer.skin-custom .fp-buffer { background-color: #eeeeee !important; }
.flowplayer.skin-custom .fp-bar span.chapter_buffered{ background-color: #eeeeee !important; }
.flowplayer.skin-custom .fp-elapsed, .flowplayer.skin-custom .fp-duration { color: #eeeeee !important; }
.flowplayer.skin-custom .fv-player-video-checker { color: #eeeeee !important; }
.flowplayer.skin-custom .fv-ab-loop { background-color: #333333 !important; }
.flowplayer.skin-custom .fv_player_popup, .fvfp_admin_error_content {  background: #333333; }
.flowplayer.skin-custom .fp-bar span.chapter_passed{ background-color: #bb0000 !important; }
.fv-player-buttons a.current { background-color: #bb0000 !important; }
#content .flowplayer.skin-custom, .flowplayer.skin-custom { font-family: Tahoma, Geneva, sans-serif; }
.flowplayer.skin-custom .fp-dropdown li.active { background-color: #bb0000 !important }
      
    .wpfp_custom_background { display: none; }  
    .wpfp_custom_popup { position: absolute; top: 10%; z-index: 20; text-align: center; width: 100%; color: #fff; }
    .wpfp_custom_popup h1, .wpfp_custom_popup h2, .wpfp_custom_popup h3, .wpfp_custom_popup h4 { color: #fff; }
    .is-finished .wpfp_custom_background { display: block; }  
    
    .wpfp_custom_ad { position: absolute; bottom: 10%; z-index: 20; width: 100%; }
.wpfp_custom_ad_content { background: white; margin: 0 auto; position: relative }    .wpfp_custom_ad { color: #888; z-index: 20 !important; }
    .wpfp_custom_ad a { color: #ff3333 }
    
    .fp-playlist-external > a > span { background-color:#808080; }
        .fp-playlist-external > a.is-active > span { border-color:#bb0000; }
    .fp-playlist-external.fv-playlist-design-2014 a.is-active,.fp-playlist-external.fv-playlist-design-2014 a.is-active h4,.fp-playlist-external.fp-playlist-only-captions a.is-active,.fp-playlist-external.fv-playlist-design-2014 a.is-active h4, .fp-playlist-external.fp-playlist-only-captions a.is-active h4 { color:#bb0000; }
    .fp-playlist-vertical { background-color:#808080; }
    .flowplayer .fp-player .fp-captions p { font-size: 16px; }        .flowplayer .fp-logo { bottom: 30px; left: 15px }      
    .flowplayer .fp-player .fp-captions p { background-color: rgba(0,0,0,0.5) }
  
                  </style>
    
HTML;

    // Compare line by line or even char by char
    /*$one = explode( "\n", $this->fix_newlines($sample) );
    $two = explode( "\n", $this->fix_newlines($footer) );
    foreach( $one as $k => $v ) {

      if( $v != $two[$k]) {
        for( $i=0; $i<strlen($two[$k]); $i++)  {
          //var_dump( $two[$k][$i].' '.ord($two[$k][$i]) );
        }
      }

      $this->assertEquals( $v, $two[$k] );
    }*/
    
    $this->assertTrue( stripos( $this->fix_newlines($footer),$this->fix_newlines($sample) ) !== false );  //  is the lightboxed players in the footer?
    
    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }

  
  public function testPlaylist() {
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true"]' );
    
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_4c69d2ca536828df7eee9add037ca7ba_lightbox_starter" href="#wpfp_4c69d2ca536828df7eee9add037ca7ba_container" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="fv_flowplayer_4c69d2ca536828df7eee9add037ca7ba_lightbox_starter" style="width: 750px">
		<a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' /></div><h4><span>Video 1</span></h4></a>
		<a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' /></div><h4><span>Video 2</span></h4></a>
		<a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' /></div><h4><span>Video 3</span></h4></a>
	</div>
</div>

<div id="wpfp_4c69d2ca536828df7eee9add037ca7ba_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_4c69d2ca536828df7eee9add037ca7ba" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_4c69d2ca536828df7eee9add037ca7ba" style="width: 750px">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' /></div><h4><span>Video 3</span></h4></a>
	</div>
</div>
</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    // setting liststyle shouldn't affect anything!
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true" liststyle="slider"]' );
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );  
  }
    
    
  public function testPlaylistText() {
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true;text"]' );
    $sample = <<< HTML
<ul class="fv-player-lightbox-text-playlist" rel="wpfp_338ee74dbab365544f456c8327b33616_container"><li><a data-fancybox='gallery' data-options='{"touch":false}' href="#wpfp_338ee74dbab365544f456c8327b33616_container" class="fv-player-lightbox-link" title="Video 1">Video 1</li><li><a href="#" class="fv-player-lightbox-link" title="Video 2">Video 2</li><li><a href="#" class="fv-player-lightbox-link" title="Video 3">Video 3</li></ul>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    ob_start();
    do_action('wp_footer');
    $footer = ob_get_clean();
    
    $sample = <<< HTML
<div id="wpfp_b5d8cfd91b4c4c757624b1e8c0d9449e_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_b5d8cfd91b4c4c757624b1e8c0d9449e" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;fv_title&quot;:&quot;Video 1&quot;,&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>

<div id="wpfp_5b325c51ab8f30aff7811dfdc65c835b_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_5b325c51ab8f30aff7811dfdc65c835b" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_5b325c51ab8f30aff7811dfdc65c835b" style="width: 750px">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' /></div><h4><span>Video 3</span></h4></a>
	</div>
</div>
</div>
<!-- lightboxed players -->
HTML;
    
    $this->assertTrue( stripos( $this->fix_newlines($footer),$this->fix_newlines($sample) ) !== false );  //  are the lightboxed players in the footer?
    
    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }

}
