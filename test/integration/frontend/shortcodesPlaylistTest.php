<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_ShortcodePlaylistTestCase extends FV_Player_UnitTestCase {
  
  public function setUp() {
    parent::setUp();

    // create a post with playlist shortcode
    $this->playlist_default = $this->factory->post->create( array(      
      'post_content' => '[fvplayer src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" playlist="https://cdn.site.com/video2.mp4,https://cdn.site.com/video2.jpg;https://cdn.site.com/video3.mp4,https://cdn.site.com/video3.jpg" caption="Video 1;Video 2; Video 3"]'
    ) );

    /*global $fv_fp;

    include_once "../../../fv-wordpress-flowplayer/models/flowplayer.php";
    include_once "../../../fv-wordpress-flowplayer/models/flowplayer-frontend.php";
    $fv_fp = new flowplayer_frontend();

    include_once "../../beta/fv-player-pro.class.php";
    $this->fvPlayerProInstance = new FV_Player_Pro();*/
  }

  public function testSimpleShortcode() {
    global $post;
    $post = get_post( $this->playlist_default );
        
    //$output = new DOMDocument;
    //$output->loadXML( apply_filters( 'the_content', $post->post_content ) );
    
    $sample_html = <<< HTML
    <div id="wpfp_10ecd1d835d0db002906d6666d27a916" class="flowplayer no-brand is-splash fvp-play-button" style="background-image: url(https://cdn.site.com/video1.jpg);" data-ratio="0.5625">
    	<div class="fp-ratio" style="padding-top: 56.25%"></div>
    <div class='fvp-share-bar'><ul class="fvp-sharing">
        <li><a class="sharing-facebook" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Facebook</a></li>
        <li><a class="sharing-twitter" href="https://twitter.com/home?status=Test+Blog+http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Twitter</a></li>
        <li><a class="sharing-google" href="https://plus.google.com/share?url=http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Google+</a></li>
        <li><a class="sharing-email" href="mailto:?body=Check%20out%20the%20amazing%20video%20here%3A%20http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Email</a></li></ul><div><a class="sharing-link" href="http://example.org/?p=3" target="_blank">Link</a></div><div><label><a class="embed-code-toggle" href="#"><strong>Embed</strong></a></label></div><div class="embed-code"><label>Copy and paste this HTML code into your webpage to embed.</label><textarea></textarea></div></div>
    </div>
    	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions" rel="wpfp_10ecd1d835d0db002906d6666d27a916">
    		<a href='#' onclick='return false' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}]}'><div style='background-image: url("https://cdn.site.com/video1.jpg")'></div><h4><span>Video 1</span></h4></a>
    		<a href='#' onclick='return false' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}]}'><div style='background-image: url("https://cdn.site.com/video2.jpg")'></div><h4><span>Video 2</span></h4></a>
    		<a href='#' onclick='return false' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}]}'><div style='background-image: url("https://cdn.site.com/video3.jpg")'></div><h4><span> Video 3</span></h4></a>
    	</div>
HTML;

    $sample = new DOMDocument;
    $sample->loadXML($sample_html);
    
    die('ok?');
    
    //$this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    $this->assertEqualXMLStructure( $sample->firstChild, $output->firstChild );
  }

}
