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
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_f52c072c04a7fee87b04a344ca2d7105_lightbox_starter" href="#wpfp_f52c072c04a7fee87b04a344ca2d7105_container" class="freedomplayer lightbox-starter flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>

<div id="wpfp_f52c072c04a7fee87b04a344ca2d7105_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_f52c072c04a7fee87b04a344ca2d7105" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="freedomplayer lightboxed flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px;max-height: 360px" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z" /><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)" /></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }


  public function testCaption() {
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;Video 1" share="no" embed="false"]' );

    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_be42e434aab187774826fdeb4b5bb1d1_lightbox_starter" title='Video 1' href="#wpfp_be42e434aab187774826fdeb4b5bb1d1_container" class="freedomplayer lightbox-starter flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>

<div id="wpfp_be42e434aab187774826fdeb4b5bb1d1_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_be42e434aab187774826fdeb4b5bb1d1" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="freedomplayer lightboxed flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px;max-height: 360px" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z" /><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)" /></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }


  public function testCaptionAndDimensions() {
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;320;240;Video 1" share="no" embed="false"]' );
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_479b52100d6888fc6ac4eee16841076f_lightbox_starter" title='Video 1' href="#wpfp_479b52100d6888fc6ac4eee16841076f_container" class="freedomplayer lightbox-starter flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 320px; max-height: 240px; " data-ratio="0.75">
	<div class="fp-ratio" style="padding-top: 75%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>

<div id="wpfp_479b52100d6888fc6ac4eee16841076f_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_479b52100d6888fc6ac4eee16841076f" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="freedomplayer lightboxed flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px;max-height: 360px" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z" /><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)" /></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

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

    remove_action( 'wp_footer', 'the_block_template_skip_link' );

    ob_start();
    do_action('wp_footer');
    $footer = $this->fix_newlines( ob_get_clean() );

    $sample = <<< HTML

<div id="wpfp_a1c867a00a9b9024ba559b457efca492_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_a1c867a00a9b9024ba559b457efca492" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;fv_title&quot;:&quot;Video 1&quot;,&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px;max-height: 360px" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z" /><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)" /></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div>
<!-- lightboxed players -->
HTML;

    $this->assertTrue( stripos( $footer,$this->fix_newlines($sample) ) !== false );  //  is the lightboxed players in the footer?

    //  are the required scripts in the footer?
    $this->assertTrue( stripos( $footer, 'fv-wordpress-flowplayer/css/skin.css' ) !== false );
    $this->assertTrue( stripos( $footer, 'fv-wordpress-flowplayer/css/fv-player-additions.css' ) !== false );
    $this->assertTrue( stripos( $footer, 'fv-wordpress-flowplayer/css/fancybox.css' ) !== false );
    $this->assertTrue( stripos( $footer, 'fv-wordpress-flowplayer/js/fancybox.js' ) !== false );

    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }


  public function testPlaylist() {
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true"]' );

    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_4c69d2ca536828df7eee9add037ca7ba_lightbox_starter" href="#wpfp_4c69d2ca536828df7eee9add037ca7ba_container" class="freedomplayer lightbox-starter flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="fv_flowplayer_4c69d2ca536828df7eee9add037ca7ba_lightbox_starter" id="wpfp_4c69d2ca536828df7eee9add037ca7ba_playlist" style="width: 600px; max-width: 600px !important">
		<a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' loading='lazy' /></div><h4><span>Video 1</span></h4></a>
		<a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' loading='lazy' /></div><h4><span>Video 2</span></h4></a>
		<a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' loading='lazy' /></div><h4><span>Video 3</span></h4></a>
	</div>
  <script>( function() { var el = document.getElementById( "wpfp_4c69d2ca536828df7eee9add037ca7ba_playlist" ); if ( el.parentNode.getBoundingClientRect().width >= 900 ) { el.classList.add( 'is-wide' ); } } )();</script>
</div>

<div id="wpfp_4c69d2ca536828df7eee9add037ca7ba_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_4c69d2ca536828df7eee9add037ca7ba" class="freedomplayer lightboxed flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z" /><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)" /></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_4c69d2ca536828df7eee9add037ca7ba" id="wpfp_4c69d2ca536828df7eee9add037ca7ba_playlist" style="width: 600px;max-width: 600px !important">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img src='https://cdn.site.com/video1.jpg' loading='lazy' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img src='https://cdn.site.com/video2.jpg' loading='lazy' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img src='https://cdn.site.com/video3.jpg' loading='lazy' /></div><h4><span>Video 3</span></h4></a>
	</div>
  <script>( function() { var el = document.getElementById( "wpfp_4c69d2ca536828df7eee9add037ca7ba_playlist" ); if ( el.parentNode.getBoundingClientRect().width >= 900 ) { el.classList.add( 'is-wide' ); } } )();</script>
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

    remove_action( 'wp_footer', 'the_block_template_skip_link' );

    ob_start();
    do_action('wp_footer');
    $footer = ob_get_clean();

    $sample = <<< HTML
<div id="wpfp_5b325c51ab8f30aff7811dfdc65c835b_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_5b325c51ab8f30aff7811dfdc65c835b" class="freedomplayer lightboxed flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z" /><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)" /></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_5b325c51ab8f30aff7811dfdc65c835b" id="wpfp_5b325c51ab8f30aff7811dfdc65c835b_playlist" style="width: 600px; max-width: 600px !important">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img src='https://cdn.site.com/video1.jpg' loading='lazy' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img src='https://cdn.site.com/video2.jpg' loading='lazy' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img src='https://cdn.site.com/video3.jpg' loading='lazy' /></div><h4><span>Video 3</span></h4></a>
	</div>
  <script>( function() { var el = document.getElementById( "wpfp_5b325c51ab8f30aff7811dfdc65c835b_playlist" ); if ( el.parentNode.getBoundingClientRect().width >= 900 ) { el.classList.add( 'is-wide' ); } } )();</script>
</div>
</div>
<!-- lightboxed players -->
HTML;

    $this->assertTrue( stripos( $this->fix_newlines($footer),$this->fix_newlines($sample) ) !== false );  //  are the lightboxed players in the footer?

    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }

}
