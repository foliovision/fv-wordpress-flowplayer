<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_ShortcodePlaylistTestCase extends FV_Player_UnitTestCase {
  
  private $playlist_default;
  private $playlist_vertical;
  private $playlist_tabs;
  private $playlist_prevnext;
  private $playlist_slider;

  protected function setUp(): void {
    parent::setUp();
    
    $shortcode_body = 'src="https://cdn.site.com/video1.mp4" splash="https://cdn.site.com/video1.jpg" playlist="https://cdn.site.com/video2.mp4,https://cdn.site.com/video2.jpg;https://cdn.site.com/video3.mp4,https://cdn.site.com/video3.jpg" caption="Video 1;Video 2;Video 3" share="no" embed="false"';

    // create a post with playlist shortcode
    $this->playlist_default = $this->factory->post->create( array(      
      'post_content' => '[fvplayer '.$shortcode_body.']'
    ) );
    
    $this->playlist_vertical = $this->factory->post->create( array(      
      'post_content' => '[fvplayer '.$shortcode_body.' liststyle="vertical"]'
    ) );
    
    $this->playlist_tabs = $this->factory->post->create( array(      
      'post_content' => '[fvplayer '.$shortcode_body.' liststyle="tabs"]'
    ) );
    
    $this->playlist_prevnext = $this->factory->post->create( array(      
      'post_content' => '[fvplayer '.$shortcode_body.' liststyle="prevnext"]'
    ) );
    
    $this->playlist_slider = $this->factory->post->create( array(      
      'post_content' => '[fvplayer '.$shortcode_body.' liststyle="slider"]'
    ) );

  }

  public function testPlaylistStyleShortcode() {
    global $post;
    
    $post = get_post( $this->playlist_default );
    $output = apply_filters( 'the_content', $post->post_content );
    
    $sample = <<< HTML
<div id="wpfp_f42eec11ed5dc49d5c4e03e1cd99b39b" class="freedomplayer flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-horizontal" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_f42eec11ed5dc49d5c4e03e1cd99b39b" id="wpfp_some-test-hash">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' loading='lazy' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' loading='lazy' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' loading='lazy' /></div><h4><span>Video 3</span></h4></a>
	</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    $post = get_post( $this->playlist_vertical );
    $output = apply_filters( 'the_content', $post->post_content );
    
    $sample = <<< HTML
<div class="fp-playlist-vertical-wrapper"><div id="wpfp_2f7513fd179c9537da5b3a02cb603184" class="freedomplayer flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-vertical" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-vertical fp-playlist-has-captions skin-slim" rel="wpfp_2f7513fd179c9537da5b3a02cb603184" id="wpfp_2f7513fd179c9537da5b3a02cb603184_playlist">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' loading='lazy' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' loading='lazy' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' loading='lazy' /></div><h4><span>Video 3</span></h4></a>
	</div>
</div><script>
( function() {
  var player = document.getElementById( 'wpfp_9c6d90c69519637b841bab4b66ce21c9'),
    el = player.parentNode,
    playlist = document.getElementById( 'wpfp_9c6d90c69519637b841bab4b66ce21c9_playlist'),
    property = playlist.classList.contains( 'fp-playlist-only-captions' ) ? 'height' : 'max-height',
    height = player.offsetHeight || parseInt(player.style['max-height']);

  if ( el.offsetHeight && el.offsetWidth <= 560 ) {
    el.classList.add('is-fv-narrow');
  }
  playlist.style[property] = height + 'px';
  if (property === 'max-height') {
    playlist.style['height'] = 'auto';
  }
} )();
</script>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    $post = get_post( $this->playlist_prevnext );
    $output = apply_filters( 'the_content', $post->post_content );

    $sample = <<< HTML
<div id="wpfp_557f8ef3e1e5fb5738ea8672ee6ad6b1" class="freedomplayer flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-prevnext" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

<a class="fp-prev" title="prev"></a><a class="fp-next" title="next"></a></div>
	<div style="display: none" class="fp-playlist-external fv-playlist-design-2017 fp-playlist-prevnext fp-playlist-has-captions skin-slim" rel="wpfp_557f8ef3e1e5fb5738ea8672ee6ad6b1" id="wpfp_557f8ef3e1e5fb5738ea8672ee6ad6b1_playlist">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' loading='lazy' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' loading='lazy' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' loading='lazy' /></div><h4><span>Video 3</span></h4></a>
	</div>
HTML;

    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
    
    $post = get_post( $this->playlist_slider );
    $output = apply_filters( 'the_content', $post->post_content );

    $sample = <<< HTML
<div id="wpfp_71aa011cbf9647b3f239334bd655a7a0" class="freedomplayer flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy has-playlist has-playlist-slider" style="max-width: 100%; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="Video 1;Video 2;Video 3" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
<div class='fv-playlist-slider-wrapper'>	<div class="fp-playlist-external fv-playlist-design-2017 fp-playlist-horizontal fp-playlist-has-captions skin-slim" rel="wpfp_71aa011cbf9647b3f239334bd655a7a0" id="wpfp_71aa011cbf9647b3f239334bd655a7a0_playlist" style="width: 750px">
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video1.mp4","type":"video\/mp4"}],"fv_title":"Video 1","splash":"https:\/\/cdn.site.com\/video1.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video1.jpg' /></div><h4><span>Video 1</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video2.mp4","type":"video\/mp4"}],"fv_title":"Video 2","splash":"https:\/\/cdn.site.com\/video2.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video2.jpg' /></div><h4><span>Video 2</span></h4></a>
		<a href='#' data-item='{"sources":[{"src":"https:\/\/cdn.site.com\/video3.mp4","type":"video\/mp4"}],"fv_title":"Video 3","splash":"https:\/\/cdn.site.com\/video3.jpg"}'><div class='fvp-playlist-thumb-img'><img  src='https://cdn.site.com/video3.jpg' /></div><h4><span>Video 3</span></h4></a>
	</div>
</div>
HTML;

    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  public function testPlaylistTabsShortcode() {
    global $post;
    
    $post = get_post( $this->playlist_tabs );
    $output = apply_filters( 'the_content', $post->post_content );
    
    $sample = <<< HTML
<script>document.body.className += " fv_flowplayer_tabs_hide";</script><div class="fv_flowplayer_tabs tabs woocommerce-tabs" style="max-width: 640px"><div id="tabs-28-1" class="fv_flowplayer_tabs_content"><ul><li><a href="#tabs-28-1-0">Video 1</a></li><li><a href="#tabs-28-1-1">Video 2</a></li><li><a href="#tabs-28-1-2">Video 3</a></li></ul><div class="fv_flowplayer_tabs_cl"></div><div id="tabs-28-1-0" class="fv_flowplayer_tabs_first"><div id="wpfp_b6048f10f0114cc6ff02fbeba28e0891" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video1.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video1.jpg&quot;}" class="freedomplayer flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video1.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div><div id="tabs-28-1-1"><div id="wpfp_2a4dd4c1fa76be7e9d7a9abc8a67cc27" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video2.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video2.jpg&quot;}" class="freedomplayer flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video2.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div><div id="tabs-28-1-2"><div id="wpfp_1153bef460a0919c9506f4c79681259f" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video3.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video3.jpg&quot;}" class="freedomplayer flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video3.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>

</div>
</div><div class="fv_flowplayer_tabs_cl"></div><div class="fv_flowplayer_tabs_cr"></div></div></div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
    
        
    global $fv_fp;
    $this->assertTrue( $fv_fp->load_tabs );    
  }
  
  protected function tearDown(): void {
    global $fv_fp, $FV_Player_lightbox;
    $fv_fp->load_tabs = false;
    $FV_Player_lightbox = new FV_Player_lightbox(); // reset the lightbox loading flag and footer lightboxed players HTML
    
    parent::tearDown();
  }

}
