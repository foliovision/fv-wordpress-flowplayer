<?php
use PHPUnit\Framework\TestCase;

final class FV_Player_Controller extends TestCase {

  private $fvPlayerProInstance;

  // playlist items
  private $playlist_items = array(
    0 =>
      array (
        'sources' =>
          array (
            0 =>
              array (
                'src' => 'https://youtu.be/7uY0Ab5HlZ0',
                'type' => 'video/youtube'
              )
          )
      ),
    1 =>
      array (
        'sources' =>
          array (
            0 =>
              array (
                'src' => 'https://www.youtube.com/watch?v=1XiHhpGUmQg',
                'type' => 'video/youtube'
              )
          )
      ),
    2 =>
      array (
        'sources' =>
          array (
            0 =>
              array (
                'src' => 'https://www.youtube.com/watch?v=Q1eR8pUM5iY',
                'type' => 'video/youtube'
              )
          )
      )
  );

  // ads
  private $adsMock = array(
    array(
      'videos' => array(
        'mp4' => 'https://www.youtube.com/watch?v=tPEE9ZwTmy0'
      ),
      'disabled' => '0',
      'name' => 'cat preroll',
      'click' => 'http://www.pobox.sk'
    ),
    array(
      'videos' => array(
        'mp4' => 'https://www.youtube.com/watch?v=bCGmUCDj4Nc'
      ),
      'disabled' => '1',
      'name' => 'kids postroll',
      'click' => 'http://www.foliovision.com'
    ),
    array(
      'videos' => array(
        'mp4' => 'https://www.youtube.com/watch?v=OsAVRDo9znQ'
      ),
      'disabled' => '0',
      'name' => 'funny whatroll',
      'click' => 'http://www.google.com'
    )
  );

  protected function setUp(): void {
    // set an empty global return value to be used
    // in all the mocked global WordPress functions
    // like add_action() and the such
    global $testReturnValue;
    $testReturnValue = '';

    define( 'ABSPATH', dirname( __FILE__ ) );

    include_once "../../models/fv-player.php";
    include_once "../../models/lightbox.php";
    global $fv_fp;
    $fv_fp = new flowplayer();

    include_once "../../controller/frontend.php";
  }

  public function test_flowplayer_prepare_scripts_js_everywhere() {
    global $fv_fp;
    $fv_fp->conf['js-everywhere'] = true;

    ob_start();
    flowplayer_prepare_scripts();
    $output = ob_get_clean();

    $expected = "Registering jquery-ui-tabs for ?ver=1.2.3.4 footer? 1
Registering fv_flowplayer for fv-player/css/fv-player.min.css?ver=1.2.3.4
Registering fv_freedomplayer_playlists for fv-player/css/playlists.css?ver=1.2.3.4
Registering flowplayer for fv-player/freedom-video-player/freedomplayer.min.js?ver=1.2.3.4 footer? 1
Registering fv-player for fv-player/freedom-video-player/fv-player.min.js?ver=1.2.3.4 footer? 1
Registering flowplayer-hlsjs for fv-player/freedom-video-player/hls.min.js?ver=1.2.3.4 footer? 1
Registering dashjs for fv-player/freedom-video-player/dash.mediaplayer.min.js?ver=1.2.3.4 footer? 1
Registering fv-player-dash for fv-player/freedom-video-player/fv-player-dashjs.min.js?ver=1.2.3.4 footer? 1
Registering fv-player-youtube for fv-player/freedom-video-player/fv-player-youtube.min.js?ver=1.2.3.4 footer? 1
Localizing flowplayer with fv_flowplayer_conf = Array
(
    [speeds] => Array
        (
            [0] => 0.25
            [1] => 0.5
            [2] => 0.75
            [3] => 1
            [4] => 1.25
            [5] => 1.5
            [6] => 1.75
            [7] => 2
        )

    [video_hash_links] =>
    [safety_resize] => 1
    [logo_over_video] => 1
    [volume] => 0.7
    [default_volume] => 0.7
    [mobile_landscape_fullscreen] => 1
    [video_position_save_enable] => 1
    [sticky_video] => off
    [sticky_place] => right-bottom
    [sticky_min_width] => 1020
    [script_hls_js] => fv-player/freedom-video-player/hls.min.js?ver=1.2.3.4
    [script_dash_js] => fv-player/freedom-video-player/dash.mediaplayer.min.js?ver=1.2.3.4
    [script_dash_js_engine] => fv-player/freedom-video-player/fv-player-dashjs.min.js?ver=1.2.3.4
    [airplay] => 1
    [chromecast] =>
    [youtube_browser_chrome] => standard
    [hlsjs] => Array
        (
            [startLevel] => -1
            [fragLoadingMaxRetry] => 3
            [levelLoadingMaxRetry] => 3
            [capLevelToPlayerSize] => 1
            [use_for_safari] =>
        )

)

Localizing flowplayer with fv_player = Array
(
    [ajaxurl] => https://site.com/wp//wp-admin/admin-ajax.php
    [nonce] => nonce
    [email_signup_nonce] => nonce
    [video_position_save_nonce] => nonce
)

Localizing flowplayer with fv_flowplayer_translations = Array
(
    [0] =>
    [1] => Video loading aborted
    [2] => Network error
    [3] => Video not properly encoded
    [4] => Video file not found
    [5] => Unsupported video
    [6] => Skin not found
    [7] => SWF file not found
    [8] => Subtitles not found
    [10] => Unsupported video format.
    [11] => Click to watch the video
    [12] => [This post contains video, click to play]
    [video_expired] => <h2>Video file expired.<br />Please reload the page and play it again.</h2>
    [unsupported_format] => <h2>Unsupported video format.<br />Please use a Flash compatible device.</h2>
    [mobile_browser_detected_1] => Mobile browser detected, serving low bandwidth video.
    [mobile_browser_detected_2] => Click here for full quality
    [live_stream_failed] => <h2>Live stream load failed.</h2><h3>Please try again later, perhaps the stream is currently offline.</h3>
    [live_stream_failed_2] => <h2>Live stream load failed.</h2><h3>Please try again later, perhaps the stream is currently offline.</h3>
    [what_is_wrong] => Please tell us what is wrong :
    [full_sentence] => Please give us more information (a full sentence) so we can help you better
    [error_JSON] => Admin: Error parsing JSON
    [no_support_IE9] => Admin: Video checker doesn't support IE 9.
    [check_failed] => Admin: Check failed.
    [playlist_current] => Now Playing
    [playlist_item_no] => Item %d.
    [playlist_play_all] => Play All
    [playlist_play_all_button] => All
    [playlist_replay_all] => Replay Playlist
    [playlist_replay_video] => Repeat Track
    [playlist_shuffle] => Shuffle Playlist
    [video_issues] => Video Issues
    [video_reload] => Video loading has stalled, click to reload
    [link_copied] => Video Link Copied to Clipboard
    [live_stream_starting] => <h2>Live stream scheduled</h2><p>Starting in <span>%d</span>.</p>
    [live_stream_retry] => <h2>We are sorry, currently no live stream available.</h2><p>Retrying in <span>%d</span> ...</p>
    [live_stream_continue] => <h2>It appears the stream went down.</h2><p>Retrying in <span>%d</span> ...</p>
    [embed_copied] => Embed Code Copied to Clipboard
    [error_copy_clipboard] => Error copying text into clipboard!
    [subtitles_disabled] => Subtitles disabled
    [subtitles_switched] => Subtitles switched to
    [warning_iphone_subs] => This video has subtitles, that are not supported on your device.
    [warning_unstable_android] => You are using an old Android device. If you experience issues with the video please use <a href=\"https://play.google.com/store/apps/details?id=org.mozilla.firefox\">Firefox</a>.
    [warning_samsungbrowser] => You are using the Samsung Browser which is an older and buggy version of Google Chrome. If you experience issues with the video please use <a href=\"https://www.mozilla.org/en-US/firefox/new/\">Firefox</a> or other modern browser.
    [warning_old_safari] => You are using an old Safari browser. If you experience issues with the video please use <a href=\"https://www.mozilla.org/en-US/firefox/new/\">Firefox</a> or other modern browser.
    [warning_old_chrome] => You are using an old Chrome browser. Please make sure you use the latest version.
    [warning_old_firefox] => You are using an old Firefox browser. Please make sure you use the latest version.
    [warning_old_ie] => You are using a deprecated browser. If you experience issues with the video please use <a href=\"https://www.mozilla.org/en-US/firefox/new/\">Firefox</a> or other modern browser.
    [quality] => Quality
    [closed_captions] => Closed Captions
    [no_subtitles] => No subtitles
    [speed] => Speed
    [duration_1_day] => %s day
    [duration_n_days] => %s day
    [duration_1_hour] => %s hour
    [duration_n_hours] => %s hour
    [duration_1_minute] => %s min
    [duration_n_minutes] => %s min
    [duration_1_second] => %s second
    [duration_n_seconds] => %s second
    [and] =>  and
    [chrome_extension_disable_html5_autoplay] => It appears you are using the Disable HTML5 Autoplay Chrome extension, disable it to play videos
    [click_to_unmute] => Click to unmute
    [audio_button] => AUD
    [audio_menu] => Audio
    [iphone_swipe_up_location_bar] => To enjoy fullscreen swipe up to hide location bar.
    [invalid_youtube] => Invalid Youtube video ID.
    [redirection] => Admin note:

This player is set to redirect to a URL at the end of the video:

%url%

Would you like to be redirected?

This note only shows to logged in Administrators and Editors for security reasons, other users are redirected without any popup or confirmation.
    [video_loaded] => Video loaded, click to play.
    [msg_no_skipping] => Skipping is not allowed.
    [msg_watch_video] => Please watch the video carefully.
)

Localizing flowplayer with fv_flowplayer_playlists = Array
(
)

Registering fv_player_lightbox for ?ver=
Registering fv_player_lightbox for ?ver= footer?
Adding inline script for fv_player_lightbox: ( function() { let fv_player_fancybox_loaded = false; const triggers = document.querySelectorAll( '[data-fancybox], .fp-playlist-external[rel$=_lightbox_starter] a' ); for (let i = 0; i < triggers.length; i++) { triggers[i].addEventListener( 'click', function( e ) { if ( fv_player_fancybox_loaded ) return; fv_player_fancybox_loaded = true; let i = this, l = document.createElement('link'), s = document.createElement('script'); e.preventDefault(); e.stopPropagation(); l.rel = 'stylesheet'; l.type = 'text/css'; l.href = fv_player_lightbox.css_url; document.head.appendChild(l); s.onload = function () { let evt = new MouseEvent('click',{bubbles: true,cancelable:true,view:window}); i.dispatchEvent(evt); }; s.src = fv_player_lightbox.js_url; document.head.appendChild(s); }); } })();
Localizing fv_player_lightbox with fv_player_lightbox = Array
(
    [lightbox_images] =>
    [js_url] => fv-player/js/fancybox.js
    [css_url] => fv-player/css/fancybox.css
)

";

    $output = preg_replace( '~\?ver=[0-9.mod-]+~', '?ver=1.2.3.4', $output );

    /*$aOut = explode( "\n", preg_replace( '~\r\n~', "\n", $output) );
    $aExpected = explode( "\n", preg_replace( '~\r\n~', "\n", $expected ) );

    foreach( $aOut AS $k => $v ) {
      $this->assertEquals( $v, $aExpected[$k] );
    }*/

    // Replace windows newlines with unix newlines, including any whitespace before newline
    $this->assertEquals( preg_replace( '~\s*?\r?\n~', "\n", $expected ), preg_replace( '~\s*?\r?\n~', "\n", $output) );
  }

}
