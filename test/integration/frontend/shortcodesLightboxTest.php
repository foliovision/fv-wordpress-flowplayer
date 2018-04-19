<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_ShortcodeLightboxTestCase extends FV_Player_UnitTestCase {
  
  var $shortcode_body = 'src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" playlist="https://cdn.site.com/video2.mp4,https://cdn.site.com/video2.jpg;https://cdn.site.com/video3.mp4,https://cdn.site.com/video3.jpg" caption="Video 1;Video 2; Video 3" share="no" embed="false"';
  
  public function testPlaylistLightboxShortcode() {
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true"]' );
    
    $sample = <<< HTML
<div id='fv_flowplayer_5d2ac904592b20b5bf87a2a85df7ace7_lightbox_starter'  href='#wpfp_5d2ac904592b20b5bf87a2a85df7ace7' class='flowplayer lightbox-starter is-splash' style="max-width: 640px; max-height: 360px; background-image: url('https://cdn.site.com/video1.jpg')" data-ratio="0.5625"><div class='fp-ui'></div><div class="fp-ratio" style="padding-top: 56.25%"></div></div>
<div class='fv_player_lightbox_hidden' style='display: none'>
<div id="wpfp_5d2ac904592b20b5bf87a2a85df7ace7" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer lightboxed no-brand is-splash fvp-play-button has-caption" data-embed="false" style="max-width: 640px; max-height: 360px; background-image: url(https://cdn.site.com/video1.jpg);" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>

</div>
<p class='fp-caption'>Video 1</p></div><div class='fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions'><a id='fv_flowplayer_lightbox_placeholder' href='#' onclick='document.getElementById("fv_flowplayer_5d2ac904592b20b5bf87a2a85df7ace7_lightbox_starter").click(); return false'><div style="background-image: url('https://cdn.site.com/video1.jpg')"></div><h4><span>Video 1</span></h4></a><a id='fv_flowplayer_lightbox_starter' href='#' data-fv-lightbox='#wpfp_e802b17ebbace952275cd50709bf549b'><div style="background-image: url('https://cdn.site.com/video2.jpg')"></div><h4><span>Video 2</span></h4></a><a id='fv_flowplayer_lightbox_starter' href='#' data-fv-lightbox='#wpfp_2ffbd4e84c1ecf2e00db5edf98996de3'><div style="background-image: url('https://cdn.site.com/video3.jpg')"></div><h4><span> Video 3</span></h4></a></div><div class='fv_player_lightbox_hidden' style='display: none'>
<div id="wpfp_e802b17ebbace952275cd50709bf549b" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video2.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer lightboxed no-brand is-splash fvp-play-button has-caption" data-embed="false" style="max-width: 640px; max-height: 360px; background-image: url(https://cdn.site.com/video2.jpg);" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>

</div>
<p class='fp-caption'>Video 2</p></div><div class='fv_player_lightbox_hidden' style='display: none'>
<div id="wpfp_2ffbd4e84c1ecf2e00db5edf98996de3" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video3.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer lightboxed no-brand is-splash fvp-play-button has-caption" data-embed="false" style="max-width: 640px; max-height: 360px; background-image: url(https://cdn.site.com/video3.jpg);" data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>

</div>
<p class='fp-caption'> Video 3</p></div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true" liststyle="slider"]' );
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );  
    
    
    $output = apply_filters( 'the_content', '[fvplayer '.$this->shortcode_body.' lightbox="true;text"]' );
    $sample = <<< HTML
<ul><li><a id='fv_flowplayer_b721d6e309a0b856f27cc5ffe3f64c19_lightbox_starter' href="#" data-fv-lightbox='#wpfp_b721d6e309a0b856f27cc5ffe3f64c19'>Video 1</a></li><li><a id='fv_flowplayer_lightbox_starter' href='#' data-fv-lightbox='#wpfp_f7e1bf7ee8d12a2bf3bc4f148cdd718c'>Video 2</a></li><li><a id='fv_flowplayer_lightbox_starter' href='#' data-fv-lightbox='#wpfp_d0ecb746d43cfeca15296bd46c0dee3c'> Video 3</a></li></div></ul>
HTML;
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    ob_start();
    do_action('wp_footer');
    $footer = ob_get_clean();
    
    global $FV_Player_lightbox;
    ob_start();
    $FV_Player_lightbox->disp__lightboxed_players();    
    $find = ob_get_clean();
    
    $this->assertTrue( stripos($footer,$find) !== false );  //  are the lightboxed players in the footer?
    $this->assertTrue( $FV_Player_lightbox->bLoad );  //  is the flag to load lightbox JS set?
  }
  
  public function tearDown() {
    global $FV_Player_lightbox;
    $FV_Player_lightbox = false;
    $FV_Player_lightbox = new FV_Player_lightbox(); // reset the lightbox loading flag and footer lightboxed players HTML
    
    parent::tearDown();
  }

}
