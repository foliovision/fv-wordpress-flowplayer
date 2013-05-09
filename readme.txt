=== FV Wordpress Flowplayer ===
Contributors: FolioVision
Donate link: http://foliovision.com/donate/
Tags: video, flash, flowplayer, player, jwplayer, mobile, mobile video
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 2.1.1

Embed videos (FLV, H.264, and MP4) into posts or pages.

== Description ==

Custom HTML 5 video on your own site with Flash fallback for legacy browsers is here.

FV Wordpress Flowplayer WordPress plugin is a free, easy-to-use, and complete solution for embedding FLV or MP4 videos into your posts or pages. With MP4 videos, FV Wordpress Flowplayer offers 98% coverage even on mobile devices.

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
* Supported video formats are FLV, H.264, and MP4 ([read Flowplayer article](http://flowplayer.org/docs/#video-formats)). Multiple videos can be displayed in one post or page.
* Default options for all the embedded videos can be set in comprehensive administration menu.
* In comparison with Wordpress Flowplayer plugin, there are several improvements:

	1. Doesn't use jQuery, so there will be no future conflicts with other plugins.
	2. Usage is simpler and forgiving, making the plugin easier to use.
	3. Allows user to display clickable splash screen at the beginning of video (which not only looks good, but improves the performance significantly).
	4. Allows user to display popup box after the video ends, with any HTML content (clickable links, images, styling, etc.)
	5. Allows to upload videos and images through WP Media Library
	6. Does not use configuration file, but Wordpress Options

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

   
== Frequently Asked Questions ==

= My video doesn't play in some browsers. =

This should be related to your video format or mime type issues.

Each browser supports different video format, MP4 is the recommended format: http://flowplayer.org/docs/#video-formats

Please note that MP4 is just a container, it might contain various streams for audio and video. You should check if the video stream in your MP4 is H.264 aka MPEG-4 AVC or MPEG-4 Part 10 and if audio is using AAC: http://flowplayer.org/docs/encoding.html#codecs

Using MPEG-4 Visual or MPEG-4 Part 2 is not recommended as it might cause issues in Internet Explorer HTML5 mode.

In general, it's recommended to use constant frame rate: http://flowplayer.org/docs/encoding.html#general-advice

You should also check if your server is serving your video file with the proper mime type. Just copy full video URL and check it with this tool: http://web-sniffer.net/

You need to look at "Content-Type:" in the "HTTP Response Header" section. It should not be "video/mpeg" if your video is MP4.

It seems HTML5 is more picky about what video it can play.

= Does this plugin support Shoutcast? =

Unfortunatelly HTML5 does not support live broadcasting. Please read about it here under "Flash. The good parts": http://flowplayer.org/docs/#flash

= I get an error message like this when activating the plugin: Parse error: parse error, unexpected T_STRING, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or '}' in /wp-content/plugins/fv-wordpress-flowplayer/models/flowplayer.php on line 4 =

You need to use at least PHP 5, your site is probably still running on old PHP 4. 

= I installed the plugin, inserted the video, but it's not working, only a gray box appears. =

FV Flowplayer calls some javascript from the footer. That means your footer.php file must contain the &lt;?php wp_footer(); ?&gt; Wordpress hook. Almost all themes do this out of the box, but if you've customised your theme there's a chance that you might have deleted this call.

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

Currently there is no support for other languages. Some localizations for Flowplayer exists, but there is no official support from flowplayer.org.

= Where can I change the default directory for videos? =

You can change this manually in the the models/flowplayer.php in the flowplayer_head function. It you use videos in widgets you might need to edit the function flowplayer_content in controller/frontend.php as well. Please be carefull when editing source codes.

= How do I insert flowplayer object outside the post, for example to a sidebar? =

You need to use following code to include the shortcode into a sidebar:

echo apply_filters('the_content', '[flowplayer src=yourvideo.mp4 width=240 height=320]');

Fill the Flowplayer shortcode part according to your needs. The apply filter needs to be called because the flowplayer shortcodes are not parsen outside posts automatically. Also, please do not forget to add the echo at the beginning.

= How can I style the popup? =

Check out .wpfp_custom_popup in /fv-wordpress-flowplayer/css/flowplayer.css. You might want to move your changes to your template CSS - make sure you use ID of container element, so your declarations will work even when the flowplayer.css is loaded later in the head section of your webpage.

= My videos are taking long time to load. =

1. Check your hosting for download speed.
2. Try to use different settings when encoding the videos, try to turn on the cache when encoding with [Quick Time](http://drop.foliovision.com/webwork/it/quick-time-pro-cache-hint.png)

= Is it possible to loop the video? =

No at the moment we do not support looping.

= How do I insert videos in playlist? =

Playlist feature is not supported right now.

= How can I change the play icon? =

You need to copy the CSS from the Flowplayer CSS (default theme) and put it into your theme CSS. Also add some element ID in front of it to make sure it overridsed the default Flowplayer CSS:

  #content .is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE}.png) center no-repeat;background-size:12%;}
  #content .is-rtl.is-splash.flowplayer .fp-ui, #content .is-rtl.is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE-rtl}.png) center no-repeat;background-size:12%}
  @media (-webkit-min-device-pixel-ratio: 2){
    #content .is-splash.flowplayer .fp-ui, #content .is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE@2x}.png) center no-repeat;background-size:12%}
    #content .is-rtl.is-splash.flowplayer .fp-ui, #content .is-rtl.is-paused.flowplayer .fp-ui{background:url({PATH TO YOUR IMAGE-rtl@2x}.png)}
  }

The image needs to be 100x106px normal version nad 200x212px hi res version. You only have to include the RTL version if your site runs in such language.

= Volume control in player looks weird =

Make sure you are not using obsolete tags like &lt;center&gt; to wrap the video. Such tag is not supported in HTML5, you have to use CSS to center elements.

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

== Changelog ==

= 2.1.1 =
* fix for browser caching
* upgrade to latest core Flowplayer (5.4.1)
* additional fixes for smooth install (more compatible default settings)
* lightening of branding
* apologies to anyone who faced difficulties with the initial 2.1 version: FV Flowplayer 5 should work for you now

= 2.1 =
* small interface changes

= 2.0 =
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

== Configuration ==

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

