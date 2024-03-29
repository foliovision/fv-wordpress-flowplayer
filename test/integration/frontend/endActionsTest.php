<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_EndActionsTest extends FV_Player_UnitTestCase {
  
  private $post_id_testEndActions;

  protected function setUp(): void {
    parent::setUp();

    // create a post with playlist shortcode
    $this->post_id_testEndActions= $this->factory->post->create( array(
      'post_title' => 'End Action Test',
      'post_content' => '[fvplayer src="https://cdn.site.com/video.mp4"]'
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
    
    // the test data
    update_option('fv_player_popups', array( 1 => array (
        'name' => '',
        'html' => '<a href="https://foliovision.com/2018/07/panamax"><img src="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg" class="alignleft post-image entry-image lazyloaded " alt="PanamaX" itemprop="image" sizes="(max-width: 400px) 100vw, 400px" srcset="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg 400w, https://cdn.foliovision.com/images/2018/07/PanamaX-5.jpg 1128w" width="400" height="239"></a>',
        'css' => '',
        'disabled' => '0',
      ) ) );
  }
  
  public function testEndActionsEmailCollection() {
    
    // triggering the default email list creation
    global $FV_Player_Email_Subscription;
    $FV_Player_Email_Subscription->init_options();   
        
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" popup="email-1"]' );     
      
    $sample = <<< HTML
<div id="wpfp_0b50253ecb7c7e7fbbcb30228d6241f3" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625" data-popup="{&quot;html&quot;:&quot;&lt;div class=\&quot;fv_player_popup fv_player_popup-1 wpfp_custom_popup_content\&quot;&gt;&lt;h3&gt;Subscribe to list one&lt;\/h3&gt;&lt;p&gt;Two good reasons to subscribe right now&lt;\/p&gt;&lt;form class=\&quot;mailchimp-form  mailchimp-form-2\&quot;&gt;&lt;input type=\&quot;hidden\&quot; name=\&quot;list\&quot; value=\&quot;1\&quot; \/&gt;&lt;input type=\&quot;email\&quot; placeholder=\&quot;Email Address\&quot; name=\&quot;email\&quot;\/&gt;&lt;input type=\&quot;text\&quot; placeholder=\&quot;First Name\&quot; name=\&quot;first_name\&quot; required\/&gt;&lt;input type=\&quot;submit\&quot; value=\&quot;Subscribe\&quot;\/&gt;&lt;\/form&gt;&lt;\/div&gt;&quot;,&quot;pause&quot;:false}">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  } 
  
  public function testEndActionsLoop() {
        
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" loop="true"]' );    
    
    $sample = <<< HTML
<div id="wpfp_42c9605d867d28b89d26c2f02ef78efd" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625" data-loop="1">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }    

  public function testEndActionsPopupNumber() {
        
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" popup="1" share="no" embed="false"]' );    
    
    $sample = <<< HTML
<div id="wpfp_5ac4313ed2187b858a5b30dd2393f86f" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625" data-popup="{&quot;html&quot;:&quot;&lt;div class=\&quot;fv_player_popup fv_player_popup-1 wpfp_custom_popup_content\&quot;&gt;&lt;a href=\&quot;https:\/\/foliovision.com\/2018\/07\/panamax\&quot;&gt;&lt;img src=\&quot;https:\/\/cdn.foliovision.com\/images\/2018\/07\/PanamaX-5-400x239.jpg\&quot; class=\&quot;alignleft post-image entry-image lazyloaded \&quot; alt=\&quot;PanamaX\&quot; itemprop=\&quot;image\&quot; sizes=\&quot;(max-width: 400px) 100vw, 400px\&quot; srcset=\&quot;https:\/\/cdn.foliovision.com\/images\/2018\/07\/PanamaX-5-400x239.jpg 400w, https:\/\/cdn.foliovision.com\/images\/2018\/07\/PanamaX-5.jpg 1128w\&quot; width=\&quot;400\&quot; height=\&quot;239\&quot;&gt;&lt;\/a&gt;&lt;\/div&gt;&quot;,&quot;pause&quot;:false}">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  public function testEndActionsPopupHTML() {
        
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" popup="'.addslashes('<a href="https://foliovision.com/2018/07/panamax"><img src="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg" class="alignleft post-image entry-image lazyloaded " alt="PanamaX" itemprop="image" sizes="(max-width: 400px) 100vw, 400px" srcset="https://cdn.foliovision.com/images/2018/07/PanamaX-5-400x239.jpg 400w, https://cdn.foliovision.com/images/2018/07/PanamaX-5.jpg 1128w" width="400" height="239"></a>').'"]' );    
    
    $sample = <<< HTML
<div id="wpfp_c46c21c60fbcd30856f9719a9eed516d" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625" data-popup="{&quot;html&quot;:&quot;&lt;div class=\&quot;fv_player_popup fv_player_popup-1 wpfp_custom_popup_content\&quot;&gt;&lt;a href=\&quot;https:\/\/foliovision.com\/2018\/07\/panamax\&quot;&gt;&lt;img src=\&quot;https:\/\/cdn.foliovision.com\/images\/2018\/07\/PanamaX-5-400x239.jpg\&quot; class=\&quot;alignleft post-image entry-image lazyloaded \&quot; alt=\&quot;PanamaX\&quot; itemprop=\&quot;image\&quot; sizes=\&quot;(max-width: 400px) 100vw, 400px\&quot; srcset=\&quot;https:\/\/cdn.foliovision.com\/images\/2018\/07\/PanamaX-5-400x239.jpg 400w, https:\/\/cdn.foliovision.com\/images\/2018\/07\/PanamaX-5.jpg 1128w\&quot; width=\&quot;400\&quot; height=\&quot;239\&quot;&gt;&lt;\/a&gt;&lt;\/div&gt;&quot;,&quot;pause&quot;:false}">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  public function testEndActionsRedirect() {
        
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" share="no" embed="false" redirect="https://foliovision.com"]' );    
    
    $sample = <<< HTML
<div id="wpfp_ae330c88ef559c0d6e3178a897361931" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625" data-fv_redirect="https://foliovision.com">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  }
  
  public function testEndActionsSplashEnd() {
        
    $output = apply_filters( 'the_content', '[fvplayer src="https://cdn.site.com/video.mp4" splash="https://cdn.site.com/video.jpg" share="no" embed="false" splashend="show"]' );    
    
    $sample = <<< HTML
<div id="wpfp_33f840f66f40d65142bc334d771c562f" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}],&quot;splash&quot;:&quot;https:\/\/cdn.site.com\/video.jpg&quot;}" class="flowplayer no-brand is-splash is-paused skin-slim no-svg fp-slim fp-edgy" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">
	<div class="fp-ratio" style="padding-top: 56.25%"></div>
	<img class="fp-splash" alt="video" src="https://cdn.site.com/video.jpg" />
	<div class="fp-ui"><noscript>Please enable JavaScript</noscript><div class="fp-play fp-visible"><svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg></div><div class="fp-preload"><b></b><b></b><b></b><b></b></div></div>
<div id="wpfp_33f840f66f40d65142bc334d771c562f_custom_background" class="wpfp_custom_background" style="position: absolute; background: url('https://cdn.site.com/video.jpg') no-repeat center center; background-size: contain; width: 100%; height: 100%; z-index: 1;"></div>
</div>
HTML;
    
    $this->assertEquals( $this->fix_newlines($sample), $this->fix_newlines($output) );
  } 
  
  protected function tearDown(): void {
    delete_option('fv_player_popups');
  }

}
