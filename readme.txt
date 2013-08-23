=== FV Wordpress Flowplayer ===
Contributors: FolioVision
Donate link: http://foliovision.com/donate/
Tags: video, flash, flowplayer, player, jwplayer, mobile, mobile video, html5
Requires at least: 3.5
Tested up to: 3.6
Stable tag: trunk

Embed videos (FLV, H.264, and MP4) into posts or pages.

== Description ==

Custom HTML 5 video on your own site with Flash fallback for legacy browsers is here.

FV Wordpress Flowplayer WordPress plugin is a free, easy-to-use, and complete solution for embedding FLV or MP4 videos into your posts or pages. With MP4 videos, FV Wordpress Flowplayer offers 98% coverage even on mobile devices.

* **New:** Automated checking of video mime type for logged in admins on MP4 videos
* FV Flowplayer 5 is the only completely responsive WordPress video player.
* Custom start and end screens are built right in. You can use your own custom design before and after the video.
* Enjoy unlimited instances in a single page.
* No expensive plugins: unlike other players who nickel and dime you for every feature, with FV Flowplayer all advanced features are available in the standard license (Google Analytics, Cuepoints, Native fullscreen, Keyboard shortcuts, Subtitles, Slow motion, Random seeking, Retina ready)
* Beautiful playlists which you can skin with CSS.
* Ultra-efficient player: just 43kB of JavaScript and 4kB of Flash code. You can extend Flowplayer using just HTML and CSS, leaving the JavaScript heavy lifting up to us.
* 98% Browser coverage. Built-in Flash fallback will get the job done on older browsers.
* Full support for Amazon S3 and other CDN's.
* Totally Brandable. Stop selling YouTube and start selling yourself. Even design your own player.

To remove our branding and add your own branding and get access to additional pro support, [you can buy your own license here](://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/buy).

Licenses are on a May Day half price launch sale for May 2013. Don't miss out!

**Additional Technical information**

* Plugin based on opensource version of Flowplayer 5. 
* Supported video formats are FLV, H.264, and MP4 ([read about HTML5 video formats](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding)). Multiple videos can be displayed in one post or page.
* Default options for all the embedded videos can be set in comprehensive administration menu.
* In comparison with Wordpress Flowplayer plugin, there are several improvements:

	1. Usage is simpler and forgiving, making the plugin easier to use.
	2. Allows user to display clickable splash screen at the beginning of video (which not only looks good, but improves the performance significantly).
	3. Allows user to display popup box after the video ends, with any HTML content (clickable links, images, styling, etc.)
	4. Allows to upload videos and images through WP Media Library
	5. Does not use configuration file, but Wordpress Options

**[Download now!](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer)**

[Support](http://foliovision.com/support/fv-wordpress-flowplayer/) |
[Change Log](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/changelog) |
[Installation](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/installation)|
[Usage](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/user-guide) | 
[FAQ](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq)


== Installation ==

There aren't any special requirements for FV Wordpress Flowplayer to work, and you don't need to install any additional plugins.

   1. Download and unpack zip archive containing the plugin.
   2. Upload the fv-wordpress-flowplayer directory into wp-content/plugins/ directory of your wordpress installation.
   3. Go into Wordpress plugins setup in Wordpress administration interface and activate FV Wordpress Flowplayer plugin.
   4. Optionally, if you want to embed videos denoted just by their filename, you can create the /videos/ directory located directly in the root of your domain and place your videos there. Otherwise, you would have to type in a complete URL of video files.
   5. Go to plugin Settings screen and click both "Check template" and "Check videos" buttons to check your template and videos mime type.

   
== Frequently Asked Questions ==

= I'm having issues with splash end or loop functions =

Currently these don't work when the Flash fallback player is used. So they only work if your browsers supports the video format natively (read more about video formats in next question). One of the next version should have this fixed.

= My video doesn't play in some browsers =

This should be related to your video format or mime type issues.

Each browser supports different video format, MP4 is the recommended format: http://flowplayer.org/docs/#video-formats

Each browser supports different video format, MP4 is the recommended format. In general, it's recommended to use constant frame rate. Detailed instructions about [video encoding for HTML 5](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/encoding).

It seems HTML5 is more picky about what video it can play than Flash.

Please note that MP4 is just a container, it might contain various streams for audio and video. You should check what audio and video stream are you using. Read next question to find out how.

= How to check my video properties using the built-in checker and how to report video not playing =

* Login to your site as administrator (please use the browser in which the video is not playing for you)
* Double check Settings -> FV Wordpress Flowplayer -> "Front-end video checker" is set to "Enabled"
* Come to any post which was video
* A message in top left corner of the video should appear saying: "Checking the video file...". The check takes usually 1-2 seconds.
* Once it's finished, it changes color based on what problem is detected and you can click it to get video details.
* The dialog also includes our tips on how to resolve the issues found. There is also a button labeled "Send to Foliovision" which sends your video with details straight to our private support tickets.

Note: The checker works much better for local files (on the same server as site). For the remote files, we only grab first 2MB of the file, store it temporarily in your uploads folder, analyze it and then delete. Since we don't get the full file, some values might not be correct, but basic things like codecs should be not affected.

= My video doesn't play in Internet Explorer 9 and 10 =

Most of the issues is caused by bad mime type on the server which serves your video files. Our plugin contains an automated checked for this - just click the "Check Videos" button on the plugin Settings screen.

Here's how to fix the mime type:

**If your videos are hosted on a standard server:**

You need to put the following into your .htaccess:

`AddType video/mp4             .mp4
AddType video/webm            .webm
AddType video/ogg             .ogv
AddType application/x-mpegurl .m3u8
AddType video/x-m4v           .m4v
# hls transport stream segments:
AddType video/mp2t            .ts`

This can be also done in the Apache configuration. If you are on Microsoft IIS, you need to use the IIS manager. 

**If you host videos on Amazon AWS:**

They might be served with bad mime type too - "application/octet-stream". This largely depends on the tool which you use to upload your videos. Using your Amazon AWS Management Console, you can go though your videos and find file content type under the "Metadata" tab in an object's "Properties" pane and fix it to "video/mp4" (without the quotes, of course different video formats need different mime type, this one is for MP4). There are also tools for this, like S3 Browser Freeware, good place for start is here: https://forums.aws.amazon.com/thread.jspa?messageID=224446

Good example can be seen in our support forum: http://foliovision.com/support/fv-wordpress-flowplayer/how-to/how-to-set-correct-mime-type-on-videos-hosted-by-amazon

Also for Internet Explorer, it's not recommended to use MPEG-4 Visual or MPEG-4 Part 2 video stream codecs.

= How do I fix the bad metadata (moov) position? =

If you are using Mac, open the video in Quick Time Pro and in the Movie Properties -> Video Track -> Other Settings turn on the "Cache (hint)" - [screenshot](http://drop.foliovision.com/webwork/it/quick-time-pro-cache-hint.png).

If you are using Windows, try this tool: http://www.datagoround.com/lab/

There are also server-side tools for fixing of this written in Python and there one for PHP, but it fails on videos bigger than the PHP memory limit.

= I'm using OptimizePress template. =

First click the "Check template" button on the pluging settings screen. It will likely report an issue like:

`It appears there are multiple Flowplayer scripts on your site, your videos might not be playing, please check. There might be some other plugin adding the script.
Flowplayer script http://site.com/wp-content/themes/OptimizePress/js/flowplayer-3.2.4.min.js is old version and won't play. You need to get rid of this script.`

The problem with this template is that it includes that old Flowplayer library without using the proper Wordpress function to add a new script (wp_enqueue_script). You need to go through the template and make sure the script is not loading.

There is also a workaround - on each page what is using one of the OptimizePress custom templates, check Launch Page & Sales Letter Options --> Video Options --> "Activate Video" and enter "<!-- FV Flowplayer -->" into Launch Page & Sales Letter Options --> Video Options --> "External Player Code" field. That way the template thinks the video is external and will not try to put in the Flowplayer library and the video will play.

= Does this plugin support Shoutcast? =

Unfortunatelly HTML5 does not support live broadcasting. Please read about it here under "Flash. The good parts": http://flowplayer.org/docs/#flash

= I get an error message like this when activating the plugin: Parse error: parse error, unexpected T_STRING, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or '}' in /wp-content/plugins/fv-wordpress-flowplayer/models/flowplayer.php on line 4 =

You need to use at least PHP 5, your site is probably still running on old PHP 4. 

= I installed the plugin, inserted the video, but it's not working, only a gray box appears. =

Go to plugin Settings screen and hit "Check template" button. It will check if both jQuery library and Flowplayer JavaScript is loading properly.

= You player works just fine, but there are some weird display issues. =

Please check if these issues also appear when using the default Wordpress template. There seems to be some sort of conflict between the Flowplayer CSS and your theme CSS.

= How to make this plugin WPMU compatible? =

Just copy the plugin into wp-content/plugins and then activate it on each blog where you want to use it.

= Is there a way to force pre-buffering to load a chunk of the video before the splash screen appears? =

This option is not available. With autobuffer, it means every visitor on every visit to your page will be downloading the video. This means that you use a lot more bandwidth than on demand. I know that I actually watch the video on only about 1/3 of the pages with video that I visit. That saves you money (no bandwidth overages) and means that people who do want to watch the video and other visitors to your site get faster performance.
If you want to autobuffer, you can turn that on in the options (we turn it off by default and recommend that it stays off).

= My videos are hosted with Amazon S3 service. How can I fill the details into shortcode? =

Just enter the URL of your video hosted on Amazon S3 as the video source.

= I would like to localize the play again button. =

Currently there is no support for other languages.

= Where can I change the default directory for videos? =

You can change this manually in the the models/flowplayer.php in the flowplayer_head function. It you use videos in widgets you might need to edit the function flowplayer_content in controller/frontend.php as well. Please be carefull when editing source codes.

= How do I insert flowplayer object outside the post, for example to a sidebar? =

You need to use following code to include the shortcode into a sidebar:

echo apply_filters('the_content', '[flowplayer src=yourvideo.mp4 width=240 height=320]');

Fill the Flowplayer shortcode part according to your needs. The apply filter needs to be called because the flowplayer shortcodes are not parsen outside posts automatically. Also, please do not forget to add the echo at the beginning.

= How do I get rid of the extra blank line below the player? =

To get rid of the spacing, just add this into your template CSS, assuming that your theme uses the standard #content ID on the main content wrapper DIV:

`#content .flowplayer { margin: 0 auto; }`

Also make sure the [fvplayer] shortcode is located on it's own line in the editor and there is not text or any code on the same line. 

= How can I style the popup or ad? =

Check out .wpfp_custom_popup and .wpfp_custom_ad in /fv-wordpress-flowplayer/css/flowplayer.css. You might want to move your changes to your template CSS - make sure you use ID of container element, so your declarations will work even when the flowplayer.css is loaded later in the head section of your webpage.

= Is there a way to remove the share (embed) button? =

Yes, there's a global option in settings to disable sharing/embed. We plan to add an individual flag on a per video basis to allow sharing when sharing is turned off globally and vice versa.

= My videos are taking long time to load. =

1. Check your hosting for download speed.
2. Try to use different settings when encoding the videos, try to turn on the cache when encoding with [Quick Time](http://drop.foliovision.com/webwork/it/quick-time-pro-cache-hint.png)

= How do I insert videos in playlist? =

Playlist feature is not supported right now.

= How can I change the play icon? =

You need to copy the CSS from the Flowplayer CSS (default theme) and put it into your theme CSS. Also add some element ID in front of it to make sure it overridsed the default Flowplayer CSS:

`#content .is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE}.png) center no-repeat;background-size:12%;}
#content .is-rtl.is-splash.flowplayer .fp-ui, #content .is-rtl.is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE-rtl}.png) center no-repeat;background-size:12%}
@media (-webkit-min-device-pixel-ratio: 2){
  #content .is-splash.flowplayer .fp-ui, #content .is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE@2x}.png) center no-repeat;background-size:12%}
  #content .is-rtl.is-splash.flowplayer .fp-ui, #content .is-rtl.is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE-rtl@2x}.png)}
}`

The image needs to be 100x106px normal version nad 200x212px hi res version. You only have to include the RTL version if your site runs in such language.

= Volume control in player looks weird =

Make sure you are not using obsolete tags like &lt;center&gt; to wrap the video. Such tag is not supported in HTML5, you have to use CSS to center elements.

= How do I get rid of the 'Hit ? for help' tooltip on the player box? =

You can put this into your template's functions.php file, if you know a bit of PHP. It will disable the tooltip.

`add_filter( 'fv_flowplayer_attributes', 'tweak_fv_flowplayer_attributes', 10, 2 );
function tweak_fv_flowplayer_attributes( $attrs ) {
	$attrs['data-tooltip'] = 'false';
	return $attrs;
}`

= How can I customized the player control bar? I want to add a play/pause button. =

Just put this code into the template's functions.php file. If you know a bit of PHP, it should not be a problem for you:

`add_filter( 'fv_flowplayer_attributes', 'tweak_controlbar_fv_flowplayer_attributes', 10, 2 );
function tweak_controlbar_fv_flowplayer_attributes( $attrs ) {
	$attrs['class'] .= ' play-button';
	return $attrs;
}`

It simply adds a class "play-button" to the player DIV element and then it knows to use the play button. The other options are:

`no-mute
no-time
no-volume`

= What if the FV Flowplayer 5 doesn't work for me? =

No worries.

1. You can always downgrade to version the Flash version ([here's how](http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/downgrading)). You do lose a lot of mobile and iOS capability but you didn't have it in the first place.
1. Contact us via [support](http://foliovision.com/support). We are actively investigating and fixing people's sites now during the initial release period. We will help you to get FV Flowplayer 5 working in your environment.

FV Flowplayer 5 Pro comes with a money back guarantee so you can even try the commercial no-branding version risk free. Or make it work first with the free versions.

Thank you for being part of the HMTL 5 mobile video revolution!

=======

== Screenshots ==

1. Post containing modified flowplayer playing a video.
2. Adding three players with different arguments into a post.
3. Add new video dialog window in editing mode.
4. Configuration menu for administrators.
5. Video checker. This shows up for admins only. Click on Admin: Video Ok or Admin: Video Issues in top left corner of the video when you are logged in as admin to get it.

== Changelog ==

= What's coming =
* Amazon S3 secure URLs support
* playlist support (allowing pre-roll videos)
* cue points support
* VAST/VPAID support
* improved checking of videos with improved integration in wp-admin (check all of your videos in one place)
* tools for fixing of slow loading videos (bad meta data location)
* other bugfixes

= 2.1.34 - 2013/08/23 =
* Feature - template checker now also checks for working wp_footer hook in template
* Bugfix - Amazon S3 settings interface
* Bubfix - video player sizing in weird templates

= 2.1.33 - 2013/08/22 =
* Bugfix - fix for parsing of splash images (space characters)

= 2.1.32 - 2013/08/21 =
* Feature - better use of Wordpress filters - for programmers. Read the guide here: http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/api-programming
* Feature - support for Amazon S3 secured URLs!
* Fix - controlbar hides after 2 seconds of no mouse movement, it was 5 seconds before
* Fix - new structure of the Settings screen 
* Fix - options and settings screen layout revised - engine preference, fixed player size and video checker preference changed to simple checkboxes
* Bugfix - autobuffering now works only for first 2 videos also in HTML5
* Bugfix - autoplay now works only for the first video on the page (use fv_flowplayer_autoplay_limit filter)
* Bugfix - comma parsing turned off by default - it was causing issue with googlevideo.com URLs
* Bugfix - global variable name $scripts changed to $fv_fp_scripts
* Bugfix - video checker on Wordpress Multisite media files

= 2.1.31 - 2013/08/09 =
* Fix - fixed dimension ads are now responsive - only part which first into the video player is shown
* Bugix - better Flash fallback for Google Chrome and Chromium - was not working without autobuffering ong

= 2.1.30 - 2013/08/08 =
* Fix - autobuffering now works only for first 2 videos on page ('fv_flowplayer_autobuffer_limit' filter) - to save your bandwidth.
* Fix - better Flash fallback for Google Chrome and Chromium - MP4 files just won't play for some people, so we detect this problem and reload the player in Flash mode - better than preferring Flash for all Chrome browsers
* Fix - player position is now calculated using JS if the player is too small - fixes issues with some of the themes, or when placing player into table with too many columns and no column width specified
* Fix - various finish events now don't use JS but CSS - popup, splashend.
* Bugfix - loop function in Flash player fixed
* Bugfix - player dimensions dropdown on settings screen
* Bugfix - splashend function in Flash player fixed* Bugfix - splashend function in Flash player fixed
* Bugfix - fix for rare occurrences of decimal numbers when fetching the video size in insert video dialog

= 2.1.29 - 2013/08/02 = 
* Bugfix - two boxes below each video removed - result of alpha version of playlist feature in our plugin. Sorry about the inconvenience.
* Bugfix - Chrome check breaking the plugin JS

= 2.1.28 - 2013/08/01 =
* Bugfix - we set Flash as preference in Chrome < 28 on Windows and Chrome < 27 on Linux. This tweak combined with disabled auto buffering on Chrome/Chromium should minimize issues with these browsers.
* Bugfix - loading indicator was in way of the play button - making it impossible to click in the middle of it. This was originally tweaked to avoid issues with some templates on iDevices (we registered 1 user having issues with this)

= 2.1.27 - 2013/07/31 =
*	Feature - styling presets for ad - let's you edit the ad CSS on the plugin settings screen
* Fix - auto buffering is disabled for MP4 in Google Chrome and Chromium, as therse browsers sometimes don't play MP4 and this seems to help
* Bugfix - auto buffering was not working properly and now it's fixed. It will be disabled after you upgrade this plugin. Please test it carefully before enabling it back on, mainly check your hosting bandwidth.

= 2.1.26 - 2013/07/26 =
* Fix - improved vidoe checker appearance
* Fix - player buttons fixed for white background
* Fix - play icon changed to striked-over play icon on video error
* Fix - video checker now detects bad mime type for WebM
* Bugfix - video checker fixed for big files

= 2.1.25 - 2013/07/18 =
* Bugfix - PHP warnings

= 2.1.24 - 2013/07/17 =
* Fix - added warning for Youtube videos (we don't have support for their embeding yet)
* Fix - ad and popup background color moved from inline style attribute to header, so you can use your template CSS to alter it now
* Fix - video checker warning about bad MOV mime type fixed. It only caused the playback issues with video/quicktime on Windows Firefox
* Bugfix - a glitch in iPad and iPhone rendering was causing our player to hide the entire post content when using certain templates (ThemesIndep, CSS .fp-waiting can't use display none there)
* Bugfix - Amazon S3 signed URL parsing for Flash player - thanks goes out to Jeremy Madison for his contribution!
* Bugfix - video checker now works with Amazon S3 signed URLs
* Bugfix - parsing of video type from Amazon S3 signed URLs

= 2.1.23 - 2013/07/11 =
* Fix - added warning for AVI videos - not supported by neither HTML5 nor Flash
* Fix - m3u8 parsing
* Fix - video checker now shows a tooltip that it's visible to admins only
* Bugfix - fix for editing of alternative video sources in "Add FV WP Flowplayer" dialog

= 2.1.22 - 2013/07/10 =
* Feature - video checker now also suggests when the video should be re-encoded or an alternative format provided (simple checks)
* Fix - you can now enter your RTMP server and RTMP video path independently for each video. Just click "Add RTMP" in the "Add FV WP Flowplayer" dialog.
* Fix - iPad, iPhone and Android users are no longer advised to download Flash if their device doesn't support the video. The notice now says: "Unsupported video format. Please use a Flash compatible device."
* Fix - Update to Flowplayer 5.4.3
* Bugfix - video checker "Send report to Foliovision" now doesn't interfere with Flowplayer shortcuts

= 2.1.20 - 2013/07/03 =
* Feature - added setting for player border (on by default for upgrades from 1.x version)
* Feature - added shortcode attribute for player alignment (enable in Interface Options)

= 2.1.19 - 2013/07/02 =
* Feature - added setting for ad text and link color
* Bugfix - video checker fix for Windows servers

= 2.1.18 - 2013/06/29 =
* Bugfix - fix for bad MOV parsing for HTML5 playing

= 2.1.17 - 2013/06/28 =
* Feature - Ad support! You can enter the global ad for your videos in plugin settings. Enable Interface options -> "Show Ads" to be able to specify ad in the video shortcode.
* Feature - Mobile video support! You can specify the low-bandwidth version of the video. We are working on recommended encoding settings and better mobile detection.
* Bugfix - fix for JetPack plugin conflict (After The Deadline)

= 2.1.16 - 2013/06/25 =
* Fix - video checker now requires a comment for the video issue submission
* Bugfix - video checker styling in older templates (no #content element)
* Bugfix - video checker URL parsing
* Bugfix - main plugin variable renamed, avoiding weird conflicts with some plugins

= 2.1.15 - 2013/06/24 =
* Bugfix - "Check template" bugfixes and improvements for WP Minify
* Bugfix - Fix for fix of Flowplayer preventing window.onload from firing on iPad
* Bugfix - Fix for RTMP streams with no extension
* Bugfix - Fix for video checker redirection and issues on some servers (which don't use DOCUMENT_ROOT)
* Bugfix - Settings screen moved to options-general.php?page=fvplayer

= 2.1.14 - 2013/06/12 =
* Feature - Added support for audio! Just put your MP3, OGG, or WAV into your shortcode.
* Feature - Added a function to report video not playing to Foliovision. Thank you for letting us know what videos don't play for you in our player.
* Styling - added some spacing below the video player
* Fix - Admin front-end video checker now takes minimum of space
* Bugfix - PHP warnings
* Bugfix - for parsing of video with no extension
* Bugfix - Flowplayer was preventing window.onload from firing on iPad

= 2.1.13 - 2013/06/05 =
* Feature - Added support for subtitles - first enable "Show Subtitles" in Settings -> FV Wordpress Flowplayer -> Interface options
* Feature - Added options for what features show up in shortcode editor - check Settings -> FV Wordpress Flowplayer -> Interface options
* Feature - Added option to allow/disallow embeding per video
* Fix - Admin front-end video checker is now less obnoxious - shows smaller messages and can be disabled in options
* Bugfix - for shortcode parsing

= 2.1.12 - 2013/05/31 =
* Feature - Front-end video checker now detects video codecs and other details (read "How to check my video properties using the built-in checker" in FAQ before we update our documentation )
* Fix - Firefox on Windows prefers Flash for M4V files (due to issues on some PCs)
* Styling - Fullscreen background color set to black
* Styling - Fix for bad fullscreen dimensions in some browsers (Chrome)
* Bugfix - Template checker bugfix for false positives (jQuery plugins detected as duplicite jQuery libraries)

= 2.1.11 - 2013/05/28 =
* Fix - more improvements and bugfixes for RTMP handling
* Fix - for template and videos checker

= 2.1.10 - 2013/05/28 =
* Fix - Update to Flowplayer 5.4.2
* Bugfix - more improvements and bugfixes for RTMP handling
* Bugfix - for popup and redirection in Flash version of the player
* Bugfix - for admin front-end check of the videos 

= 2.1.9 - 2013/05/27 =
* improvements and bugfixes for RTMP handling
* improved styling of insert video dialog box
* bugfix for autoplay is off for video when autoplay is on globally

= 2.1.8 - 2013/05/23 =
* quick bugfix for Flowplayer script loading

= 2.1.7 - 2013/05/22 =
* support for responsive layout enabled by default
* automated check of template added to settings screen - checks if your template loads Flowplayer and jQuery libraries properly
* automated check of video files added to setting screen - checks if your servers are using right mime type for videos

= 2.1.6 - 2013/05/21 =
* quick fix for player skin - time values not appearing properly for some font faces

= 2.1.5 - 2013/05/17 =
* player font face setting
* improved appearance of the embed dialog
* improved shortcode editor (does handle iframe in popup correctly)
* various CSS fixes

= 2.1.4 - 2013/05/16 =
* quick fix for shortcode parsing when there is a newline after src parameter

= 2.1.3 - 2013/05/15 =
* Flowplayer now by default uses Flash (for better compatibility)
* shortcode editor fixes
* when using HTML5, admins get warnings about videos with bad mime type as they browse the site.
* logged in admins see warnings above MP4 videos with bad mime type

= 2.1.2 - 2013/05/10 =
* fix for player alignment (center by default)
* fix for volume bar alignment (was not working properly when using obsolete &lt;center&gt; tags)

= 2.1.1 - 2013/05/08 =
* fix for browser caching
* upgrade to latest core Flowplayer (5.4.1)
* additional fixes for smooth install (more compatible default settings)
* lightening of branding
* apologies to anyone who faced difficulties with the initial 2.1 version: FV Flowplayer 5 should work for you now

= 2.1 - 2013/05/02 =
* small interface changes

= 2.0 - 2013/05/02 =
* upgrade to Flowplayer 5
* fixes in the shortcode editor

= 1.2.17 =
* bugfix for wp-content paths
* fix for some warnings
* bugfix for popups and splash image at the end

= 1.2.16 =
* Flowplayer shortcodes and placeholders removed from feed

= 1.2.14 =
* Fixed Sharing permalink
 
= 1.2.14 =
* Option in settings to prevent doubling the link in the popup box, default option is set to false (do not double)

= 1.2.13 =
* Loading javascripts only when video is present on the page - optional, see settings page

= 1.2.12 =
* XSS fix

= 1.2.11 =
* FV Flowplayer removed from RSS feeds

= 1.2.10 =
* fix for HTTPS, thanks to Scott Elkin

= 1.2.9 =
* Bug with flush rules fixed

= 1.2.8 =
* added options for default video size
* problem with splasscreen at the end fixed
* audio plugin installed foraudio tracks

= 1.2.7 =
* Problem with widgets fixed

= 1.2.6 =
* Support functions for future extensions added

= 1.2.5 =
* Support functions for future extensions added

= 1.2.4 =
* Wizard fixes
* Added option for showing splash image at the end

= 1.2.3 =
* HTML 5 suport for mobile browsers (Thanks for donation from [enterpriseIT](http://enterpriseit.com/))
* incorrect paths fixed

= 1.2.2 =
* Option for keeping the aspect ratio of videos
* Class 'flowplayer_frontend' not found bug fixed

= 1.2.1 =
* License key entering fixed
* Color entering fixed

= 1.2.0 =
* Compatibility with the commercial version - possibility to insert licence key and get completely unbranded version
* Fixed the conflict with media library

= 1.1.0 =
* Flowplayer logos reintroduced at request of Wordpress.org

= 1.0.6 =
* widgets problems with splash image and controlbar fixed
* cyan background color fixed

= 1.0.5 =
* compatibility fixes
* HTTPS support added

= 1.0.4 =
* compatibility fixes
* configuration file replaced by WP options

= 1.0.3 =
* white spaces causing errors on some servers fixed

= 1.0.2 =
* redirect feature added (Thanks for donation from Klaus Eickelpasch)
* more bug fix for wp shortcodes api to be compatible with commas in shortcodes
* fixed the absolute paths

= 1.0.1 =
* bug fix for wp shortcodes api to be compatible with commas in shortcodes

= 1.0 =
* autoplay option for single videos
* show/hide control bar
* show/hide fullscreen option
* connected with wp media library, video and image upload is supported now (Thanks for donation from Kermit Woodhall)

= 0.9.18 =
* added button & dialog window for easy video adding and editing

= 0.9.16 =
* minor bug fixes

= 0.9.15 =
* support for widget use and template use

= 0.9.14 =
* Added a possibility to forbid the popup boxes.
* Some output validation.
* Minor visual improvements.

= 0.9.13 =
* Added "Replay" and "Share" buttons to the popup box after video finishes.
* Some performance tweaks concerning popup box.

= 0.9.12 =
* First stable version ready to be published.
* Removed farbtastic colour picker using jQuery from settings menu. Substituted by jscolor.

== Other Notes ==

This new version uses Flowplayer 5 running on HTML5, so we recommend you read first two questions of FAQ first.

Once the plugin is uploaded and activated, there will be a submenu of settings menu called FV Wordpress Flowplayer. In that submenu, you can modify following settings:

* AutoPlay - decides whether the video starts playing automatically, when the page/post is displayed.
* AutoBuffering - decides whether te video starts buffering automatically, when the page/post is displayed. If AutoPlay is set to true, you can ignore this setting.
* Popup Box - decides whether a popup box with "replay" and "share" buttons will be displayed when video ends.
* Enable Full-screen Mode - select false if you do not wish the fullscreen option to be displayed.
* Allow User Uploads - select true if you like to upload new videos via Media Library.
* Enable Post Thumbnail - select true if you wish the screen shot appear as post thumbnail. Works only when uploading new splash image via Media Library.
* Convert old shortcodes with commas - older versions of this plugin used commas to sepparate shortcode parameters. This option will make sure it works with current version.
* Commercial Licence Key - enter your licence key here to get the completely unbranded version of the player
* Colors of all the parts of flowplayer instances on page/post (controlbar, canvas, sliders, buttons, mouseover buttons, time and total time, progress and buffer sliders).

On the right side of this screen, you can see the current visual configuration of flowplayer. If you click Apply Changes button, this player's looks refreshes.

== Upgrade Notice ==

= 2.1.32 =
* New settings screen - engine preference, fixed player size and video checker preference changed to simple checkboxes.

= 2.1.27 =
* This new version includes a fix for autobuffering, which was not working properly. It will be disabled after you upgrade this plugin. Please test it carefully before enabling it back on, mainly check your hosting bandwidth.

= 2.1.16 =
* Feature - Added support for audio! Just put your MP3, OGG, or WAV into your shortcode.
* Feature - Added a function to report video not playing to Foliovision. Thank you for letting us know what videos don't play for you in our player.
* Fixes for RTMP parsing - please check your RTMP videos after upgrade.
* Upgrade to latest Flowplayer version - 5.4.3

= 2.1.16 =
* Feature - Added support for audio! Just put your MP3, OGG, or WAV into your shortcode.
* Feature - Added a function to report video not playing to Foliovision. Thank you for letting us know what videos don't play for you in our player.
* Styling - added some spacing below the video player
* Various bug fixes, check changelog

= 2.1.13 =
* Admin front-end video checker is not much smaller and can be disabled in options
* Support for subtitles added

= 2.1.11 =
* Upgrade to latest Flowplayer version - 5.4.2
* Fixes for RTMP parsing - please check your RTMP videos after upgrade.

= 2.1.10 =
* Upgrade to latest Flowplayer version - 5.4.2
* Fixes for RTMP parsing - please check your RTMP videos after upgrade.

= 2.1.9 =
* Fixes for RTMP parsing - please check your RTMP videos after upgrade.

= 2.1.5 =
* Default player font face set to Tahoma, Geneva, sans-serif. Change 'Player font face' setting to 'inherit from template' if you have your own CSS.

= 2.1.4 =
* Flowplayer now defaults to using Flash for Internet Explorer 9 and 10 (due to server compatibility issues when bad mime type is set).

= 2.1.3 =
* Flowplayer now defaults to using Flash for Internet Explorer 9 and 10 (due to server compatibility issues when bad mime type is set).

= 2.0 =
* Brand new version of Flowplayer! HTML5 compatible video player. Please check your videos thoroughly.
