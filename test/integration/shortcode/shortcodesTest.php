<?php

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_Pro_PlaylistsNoAdsIntegrationTest extends WP_UnitTestCase {

  private $fvPlayerProInstance;
  private $postID = -1;

  protected $backupGlobals = false;
  
  public function fix_newlines( $html ) {
    return preg_replace( '~\r\n~', "\n", $html);
  }

  // we need to set up PRO player with an appropriate key, or the PRO player won't work
  public static function wpSetUpBeforeClass() {
    global $fv_fp;

    // without this included, fv_wp_flowplayer_delete_extensions_transients() would not be found
    //include_once "../../../fv-wordpress-flowplayer/controller/backend.php";

    // include the flowplayer loader
    include_once "../../../fv-wordpress-flowplayer/flowplayer.php";

    // include the PRO plugin class, so it can intercept data saving
    // and update the ads structure as needed for saving
    //include_once "../../beta/fv-player-pro.class.php";

    // save initial settings
    //$fv_fp->_set_conf();
  }

  public function setUp() {
    parent::setUp();

    // create a post with playlist shortcode
    $this->post_id_SimpleShortcode = $this->factory->post->create( array(
      'post_title' => 'Simple Shortcode',
      'post_content' => '[fvplayer src="https://cdn.site.com/video.mp4"]'
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
    $post = get_post( $this->post_id_SimpleShortcode );
    
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    add_filter( 'wp_resource_hints', '__return_empty_array' );    

    wp_deregister_script( 'wp-embed' );
    
    ob_start();
    wp_head();
    ob_get_clean();
    
    ob_start();
    //
    echo apply_filters( 'the_content', $post->post_content );
    wp_footer();
    $output = ob_get_clean();
    
    $expect = <<<EOT
<div id="some-test-hash" data-item="{&quot;sources&quot;:[{&quot;src&quot;:&quot;https:\/\/cdn.site.com\/video.mp4&quot;,&quot;type&quot;:&quot;video\/mp4&quot;}]}" class="flowplayer no-brand is-splash fvp-play-button" style="max-width: 640px; max-height: 360px; " data-ratio="0.5625">

EOT;
  $expect .= "\t".'<div class="fp-ratio" style="padding-top: 56.25%"></div>'."\n";
  $expect .= <<<EOT
<div class='fvp-share-bar'><ul class="fvp-sharing">
    <li><a class="sharing-facebook" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Facebook</a></li>
    <li><a class="sharing-twitter" href="https://twitter.com/home?status=Test+Blog+http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Twitter</a></li>
    <li><a class="sharing-google" href="https://plus.google.com/share?url=http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Google+</a></li>
    <li><a class="sharing-email" href="mailto:?body=Check%20out%20the%20amazing%20video%20here%3A%20http%3A%2F%2Fexample.org%2F%3Fp%3D3" target="_blank">Email</a></li></ul><div><a class="sharing-link" href="http://example.org/?p=3" target="_blank">Link</a></div><div><label><a class="embed-code-toggle" href="#"><strong>Embed</strong></a></label></div><div class="embed-code"><label>Copy and paste this HTML code into your webpage to embed.</label><textarea></textarea></div></div>
</div>

<script type='text/javascript' src='http://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4'></script>
<script type='text/javascript' src='http://example.org/wp-includes/js/jquery/jquery-migrate.js?ver=1.4.1'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var fv_flowplayer_conf = {"fullscreen":"1","swf":"\/\/example.org\/wp-content\/plugins\/C:\/Work\/github\/fv-wordpress-flowplayer\/flowplayer\/flowplayer.swf?ver=","swfHls":"\/\/example.org\/wp-content\/plugins\/C:\/Work\/github\/fv-wordpress-flowplayer\/flowplayer\/flowplayerhls.swf?ver=","embed":{"library":"\/\/example.org\/wp-content\/plugins\/C:\/Work\/github\/fv-wordpress-flowplayer\/flowplayer\/fv-flowplayer.min.js","script":"\/\/example.org\/wp-content\/plugins\/C:\/Work\/github\/fv-wordpress-flowplayer\/flowplayer\/embed.min.js","skin":"\/\/example.org\/wp-content\/plugins\/C:\/Work\/github\/fv-wordpress-flowplayer\/css\/flowplayer.css","swf":"\/\/example.org\/wp-content\/plugins\/C:\/Work\/github\/fv-wordpress-flowplayer\/flowplayer\/flowplayer.swf?ver=","swfHls":"\/\/example.org\/wp-content\/plugins\/C:\/Work\/github\/fv-wordpress-flowplayer\/flowplayer\/flowplayerhls.swf?ver="},"speeds":[0.25,0.5,0.75,1,1.25,1.5,1.75,2],"video_hash_links":"1","safety_resize":"1","volume":"0.7","mobile_native_fullscreen":"","mobile_force_fullscreen":"","sticky_video":"","sticky_place":"right-bottom","sticky_width":"380"};
var fv_flowplayer_translations = {"0":"","1":"Video loading aborted","2":"Network error","3":"Video not properly encoded","4":"Video file not found","5":"Unsupported video","6":"Skin not found","7":"SWF file not found","8":"Subtitles not found","9":"Invalid RTMP URL","10":"Unsupported video format. Try installing Adobe Flash.","11":"Click to watch the video","12":"[This post contains video, click to play]","video_expired":"<h2>Video file expired.<br \/>Please reload the page and play it again.<\/h2>","unsupported_format":"<h2>Unsupported video format.<br \/>Please use a Flash compatible device.<\/h2>","mobile_browser_detected_1":"Mobile browser detected, serving low bandwidth video.","mobile_browser_detected_2":"Click here","mobile_browser_detected_3":"for full quality.","live_stream_failed":"<h2>Live stream load failed.<\/h2><h3>Please try again later, perhaps the stream is currently offline.<\/h3>","live_stream_failed_2":"<h2>Live stream load failed.<\/h2><h3>Please try again later, perhaps the stream is currently offline.<\/h3>","what_is_wrong":"Please tell us what is wrong :","full_sentence":"Please give us more information (a full sentence) so we can help you better","error_JSON":"Admin: Error parsing JSON","no_support_IE9":"Admin: Video checker doesn't support IE 9.","check_failed":"Admin: Check failed.","playlist_current":"Now Playing","video_issues":"Video Issues","link_copied":"Video Link Copied to Clipboard","embed_copied":"Embed Code Copied to Clipboard","subtitles_disabled":"Subtitles disabled","subtitles_switched":"Subtitles switched to ","warning_iphone_subs":"This video has subtitles, that are not supported on your device.","warning_unstable_android":"You are using an old Android device. If you experience issues with the video please use <a href=\"https:\/\/play.google.com\/store\/apps\/details?id=org.mozilla.firefox\">Firefox<\/a>. <a target=\"_blank\" href=\"https:\/\/foliovision.com\/2017\/05\/issues-with-vimeo-on-android\">Why?<\/a>","warning_old_safari":"You are using an old Safari browser. If you experience issues with the video please use <a href=\"https:\/\/www.mozilla.org\/en-US\/firefox\/new\/\">Firefox<\/a> or other modern browser. <a target=\"_blank\" href=\"https:\/\/foliovision.com\/2017\/05\/issues-with-vimeo-on-android\">Why?<\/a>"};
var fv_fp_ajaxurl = "http:\/\/example.org\/wp-admin\/admin-ajax.php";
var fv_flowplayer_playlists = [];
/* ]]> */
</script>
<script type='text/javascript' src='http://example.org/wp-content/plugins/C:/Work/github/fv-wordpress-flowplayer/flowplayer/fv-flowplayer.min.js'></script>

EOT;

    // replace the player's ID by a preset one, so we can actually test the output
    $regex = '/(id|rel)="wpfp_[^"]+"/';
    $substitution = '$1="some-test-hash"';
    $output = preg_replace($regex, $substitution, $output);

    // test the HTML
    $this->assertEquals( $this->fix_newlines($expect), $this->fix_newlines($output) );
  }

}
