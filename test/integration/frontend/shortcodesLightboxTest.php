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
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_5d2ac904592b20b5bf87a2a85df7ace7_lightbox_starter" href="#wpfp_5d2ac904592b20b5bf87a2a85df7ace7_container" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
  <div class="fp-ratio" style="padding-top: 56.25%"></div>
  <img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
  <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
<div id="wpfp_5d2ac904592b20b5bf87a2a85df7ace7_container" class="fv_player_lightbox_hidden" style="display: none">
  <div id="wpfp_5d2ac904592b20b5bf87a2a85df7ace7" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
   <div class="fp-ratio" style="padding-top: 56.25%"></div>
   <img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
   <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
   </div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  
  public function testCaption() {
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;Video 1" share="no" embed="false"]' );
    
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_5d2ac904592b20b5bf87a2a85df7ace7_lightbox_starter" title='Video 1' href="#wpfp_5d2ac904592b20b5bf87a2a85df7ace7" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
  <div class="fp-ratio" style="padding-top: 56.25%"></div>
  <img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
  <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
<div id="wpfp_5d2ac904592b20b5bf87a2a85df7ace7_container" class="fv_player_lightbox_hidden" style="display: none">
  <div id="wpfp_5d2ac904592b20b5bf87a2a85df7ace7" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
   <div class="fp-ratio" style="padding-top: 56.25%"></div>
   <img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
   <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
   </div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  
  public function testCaptionAndDimensions() {    
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" lightbox="true;320;240;Video 1" share="no" embed="false"]' );
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_f1f51bb87ed9702bd91ac63990cee57b_lightbox_starter" title='Video 1' href="#wpfp_f1f51bb87ed9702bd91ac63990cee57b" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 320px; max-height: 240px; " data-ratio="0.5625">
  <div class="fp-ratio" style="padding-top: 56.25%"></div>
  <img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />  
  <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
<div id="wpfp_f1f51bb87ed9702bd91ac63990cee57b_container" class="fv_player_lightbox_hidden" style="display: none">
  <div id="wpfp_f1f51bb87ed9702bd91ac63990cee57b" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
    <div class="fp-ratio" style="padding-top: 56.25%"></div>
    <img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
   <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
   </div>
</div>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );      
  }
  
  
  public function testText() {    
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" caption="Video 1" lightbox="true;text" share="no" embed="false"]' );
    $sample = <<< HTML
<a data-fancybox='gallery' data-options='{"touch":false}' id="fv_flowplayer_2f9724515033ace3d660707b426f527c_lightbox_starter" title='Video 1' class="fv-player-lightbox-link" href="#" data-src="#wpfp_2f9724515033ace3d660707b426f527c_container">Video 1</a>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    ob_start();
    do_action('wp_footer');
    $footer = ob_get_clean();
    
    $sample = <<< HTML
<div id="wpfp_11361d379334c11f2eaa75f6aacd8386_container" class="fv_player_lightbox_hidden" style="display: none">
  <div id="wpfp_11361d379334c11f2eaa75f6aacd8386" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;fv_title&quot;:&quot;Video 1&quot;,&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
    <div class="fp-ratio" style="padding-top: 56.25%"></div>
    <img class="fp-splash" alt="Video 1" src="https://cdn.site.com/video1.jpg" />
    <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
  </div>
</div>
<!-- lightboxed players -->
HTML;
    
    $this->assertTrue( stripos( $this->fix_newlines($footer),$this->fix_newlines($sample) ) !== false );  //  is the lightboxed players in the footer?
    
    global $FV_Player_lightbox;
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }

  
  public function testPlaylist() {
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true"]' );
    
    $sample = <<< HTML
<div data-fancybox='gallery' data-options='{"touch":false,"thumb":"https:\/\/cdn.site.com\/video1.jpg"}' id="fv_flowplayer_5d2ac904592b20b5bf87a2a85df7ace7_lightbox_starter" href="#wpfp_5d2ac904592b20b5bf87a2a85df7ace7" class="flowplayer lightbox-starter no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
  <div class="fp-ratio" style="padding-top: 56.25%"></div>
  <img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
  <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
<div class='fv-playlist-slider-wrapper'><div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="fv_flowplayer_XYZ_lightbox_starter" style="width: 750px">
  <a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' /></div><h4><span>Video 1</span></h4></a>
  <a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' /></div><h4><span>Video 2</span></h4></a>
  <a href='#'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' /></div><h4><span>Video 3</span></h4></a>
  </div>
</div>
<div id="wpfp_5d2ac904592b20b5bf87a2a85df7ace7_container" class="fv_player_lightbox_hidden" style="display: none">
  <div id="wpfp_5d2ac904592b20b5bf87a2a85df7ace7" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
    <div class="fp-ratio" style="padding-top: 56.25%"></div>
    <img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
    <div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
  </div>
  <div class='fv-playlist-slider-wrapper'><div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_5d2ac904592b20b5bf87a2a85df7ace7" style="width: 750px">
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
<ul class="fv-player-lightbox-text-playlist" rel="wpfp_03551462692f72d815eca8e8aff1fcb5_container"><li><a data-fancybox='gallery' data-options='{"touch":false}' href="#wpfp_some-test-hash" class="fv-player-lightbox-link" title="Video 1">Video 1</li><li><a href="#" class="fv-player-lightbox-link" title="Video 2">Video 2</li><li><a href="#" class="fv-player-lightbox-link" title="Video 3">Video 3</li></ul>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    ob_start();
    do_action('wp_footer');
    $footer = ob_get_clean();
    
    $sample = <<< HTML
<div id="wpfp_f0f6ba67a6f7c994dd76b73bf4d7aa41_container" class="fv_player_lightbox_hidden" style="display: none">
<div id="wpfp_f0f6ba67a6f7c994dd76b73bf4d7aa41" class="flowplayer lightboxed no-brand is-splash no-svg is-paused skin-slim fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_f0f6ba67a6f7c994dd76b73bf4d7aa41" style="width: 750px">
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
