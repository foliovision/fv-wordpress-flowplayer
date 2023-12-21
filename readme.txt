=== FV Player ===
Contributors: FolioVision
Donate link: https://foliovision.com/donate
Tags: video player, flowplayer, mobile video, html5 video, Vimeo, html5 player, youtube player, youtube playlist, video playlist, Cloudfront, HLS
Requires at least: 3.5
Tested up to: 6.3
Stable tag: 8.0.beta.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WordPress's most reliable, easy to use and feature-rich video player. Supports responsive design, HTML5, playlists, ads, stats, Vimeo and YouTube.

== Description ==

FV Player is a free, easy-to-use, and complete solution for embedding MP4 or HLS videos into your posts or pages. With MP4 videos, FV Player offers 98% coverage even on mobile devices.

Features:

* Remembering video position for both guest users and members
* Chromecast and Airplay support
* Video playlists
* Automated checking of video encoding for logged in admins
* Totally Brandable. Stop selling YouTube and start selling yourself. Even design your own player
* Full support for Amazon S3
* YouTube support
* Integration with the Bunny Stream video encoding service
* Integration with the Coconut.co video encoding service - using a free addon plugin: https://foliovision.com/downloads/fv-player-coconut
* API for custom video fields
* iframe embed codes
* Ultra-efficient player: just 41.8kB of Javascript. Rest is loaded later when user is going to play the video

Video presentation options:

* Scroll autoplay with the sticky video option
* Sticky video playback
* Video lightbox  (works for images and galleries too, using fancyBox 3)
* Video links to point to exact time in the video

Video tracking options:

* Built-in video play stats
* Google Analytics 4
* Matomo

Pro features using the commercial FV Player Pro:

* easy AB looped playback for your visitors (great for teaching sites)
* Encrypted video support
* DRM watermark
* Video Chapters
* Full-featured Vimeo embedding
* Support for URL tokens of different CDNs: Bunny CDN, CloudFront, StackPath and Universal CDN
* Support for other video services: Vimeo, OK.ru, Odysee, PeerTube (incuding support for your own instance)
* Autoplay video only once for each visitor
* Interactive video transcript
* Timeline previews

Other extensions:

* Alternative Sources plugin for backup CDN configuration
* Cloudflare Stream plugin
* JW Player platform plugin
* Pay Per View plugin for Easy Digital Downloads and WooCommerce
* VAST plugin
* Viloud Live Streaming platform support
* Video Bookmarks and User Playlists plugin

Back to school special 25% off pro licenses until end of September.

**Additional Documentation at Foliovision.com**

[Support](https://foliovision.com/support/fv-wordpress-flowplayer/) |
[Change Log](https://foliovision.com/player/changelog) |
[Installation](https://foliovision.com/player/installation)|
[User Guide](https://foliovision.com/player/user-guide) | 
[Detailed FAQ](https://foliovision.com/player/faq)

== Installation ==

There aren't any special requirements for FV Player to work, and you don't need to install any additional plugins.

Visit [our site](https://foliovision.com/player/getting-started/installation) for a fully featured guide with **screenshots** and more!

== Frequently Asked Questions ==

= 1. My video doesn't play in some browsers. =

This should be related to your video format or mime type issues.

Each browser supports different video format, MP4 is the recommended format. In general, it's recommended to use constant frame rate. Detailed instructions about [video encoding for HTML 5](https://foliovision.com/player/encoding).

HTML5 is pickier about what video it can play than Flash.

Please note that MP4 is just a container, it might contain various streams for audio and video. You should check what audio and video stream are you using. Read next question to find out how.

= 2. How to check my video properties using the built-in checker and how to report video not playing =

The video checker works automatically when you're logged in as admin. You'll see a text in upper left corner of any video on your site. All the necessary info can be found in [this guide](https://foliovision.com/player/basic-setup/how-to-use-video-checker).

= 3. Player buttons are gone - there are only square symbols. =

1) This can happen if your site is at www.domain.com, but the CSS is loaded from your CDN at cdn.domain.com. Based on CSS3 and HTML5 specs not all the resources can be shared between domains.

So you need to set the following on your CDN for web fonts (woff, eot, ttf, svg):

Access-Control-Allow-Origin: *

Or you can allow your domain only (but in that case it might be good to also allow it with https):

Access-Control-Allow-Origin: http://www.domain.com

Or you can exclude wp-content/plugins/fv-wordpress-flowplayer/css/flowplayer.css from CDN.

2) Second cause might be that your webfonts are served with bad mimetype

`AddType application/x-font-woff woff
AddType application/x-font-ttf ttf
AddType application/vnd.ms-fontobject eot
AddType image/svg+xml svg`

= 4. I want to align my player (left/right/middle). =

By default the player is positioned in the middle. To change alignment of the player to either left or right:
Go to FV Player settings > scroll down to post interface options > tick "Align". Now you can insert your video. In the interface you can now choose you alignment from the drop down menu: default (middle), left, or right.
You can check [demo in here](https://foliovision.com/player/demos/align-settings).

= 5. How do I fix the bad metadata (moov) position? =

This means that the video information (such as what codecs are used) is not stored at the beginning of the file. In our experience, video with bad meta data position might be slow to load in Flash engine (check some browser which doesn't play MP4 format in Flash - like Opera) and Firefox. Although Safary and iOS (iPAd, iPhone) may play it just fine. 

In general we recommend you to re-encode your video as [per our instructions](https://foliovision.com/player/encoding#encoding-samples), but here are some quick tools:

If you are using Mac, try Lillipot (just remember to rename the file back to .mp4 extension): http://www.qtbridge.com/lillipot/lillipot.html

If you have Quick Time Pro, just open the video and in the Movie Properties -> Video Track -> Other Settings turn on the "Cache (hint)" - [screenshot](http://drop.foliovision.com/webwork/it/quick-time-pro-cache-hint.png).

If you are using Windows, try MP4 FastStart: http://www.datagoround.com/lab/

There are also server-side tools for fixing of this written in Python and there one for PHP, but it fails on videos bigger than the PHP memory limit.

= 6. I'm getting error about 'HTTP range requests'. =

Please check with your technical support if your web server supports HTTP range requests. Most of the modern web servers support this feature (Apache, Nginx, Lighttpd, Litespeed...). It's important for fast seeking in HTML5 video playback.

Other possible cause is that you are using some membership plugin to protect downloading of your videos (Premise and others). While this might seem like a good solution, we don't recommend it as it increases the load of your server and it won't allow seeking in the videos. You can use <a href="https://foliovision.com/player/secure-amazon-s3-guide">Amazon S3 with privacy settings</a>, just hit the link to read our illustrated guide.

= 7. Are there any known compatibility issues?. =

We heard about problems when using some fancy pro templates like OptimizePress (read below for fixing instructions) or Gantry framework. These templates often break the WordPress conventions (probably as they often try to add too many non-template functions, like video support built-in into the template). We can debug the issues for you, just head over to our website and order the pro support.

Full list of conflicting plugins is available here: https://foliovision.com/player/compatibility

= 8. I'm using OptimizePress version 1 template. =

First click the "Check template" button on the pluging settings screen. It will likely report an issue like:

`It appears there are multiple Flowplayer scripts on your site, your videos might not be playing, please check. There might be some other plugin adding the script.
Flowplayer script http://site.com/wp-content/themes/OptimizePress/js/flowplayer-3.2.4.min.js is old version and won't play. You need to get rid of this script.`

The problem with this template is that it includes that old Flowplayer library without using the proper WordPress function to add a new script (wp_enqueue_script). You need to go through the template and make sure the script is not loading. Typically it will be in any of the header.php files - including header-myheader.php, header-singleheader.php or similar files.

There is also a workaround - on each page what is using one of the OptimizePress custom templates, check Launch Page & Sales Letter Options --> Video Options --> "Activate Video" and enter "&lt;!-- FV Flowplayer --&gt;" into Launch Page & Sales Letter Options --> Video Options --> "External Player Code" field. That way the template thinks the video is external and will not try to put in the Flowplayer library and the video will play.

= 9. I'm using OptimizePress version 2 template. =

FV Player will handle all the videos inserted by the Live Edit. 

= 10. I installed the plugin, inserted the video, but it's not working - there is no control bar or only a gray box appears. =

Go to plugin Settings screen and hit "Check template" button. It will check if both jQuery library and FV Player JavaScript is loading properly.

Also, check "I'm using OptimizePress template" question above.

= 11. Your player works just fine, but there are some weird display issues. =

Please check if these issues also appear when using the default WordPress template. There seems to be some sort of conflict between the FV Player CSS and your theme CSS.

= 12. Fullscreen is not working properly for me. =

Are you using some old lightbox plugin like http://www.4mj.it/slimbox-wordpress-plugin/ ? Or are you putting the video into Iframe? Also, the video should not be placed in an HTML element with lowered z-index.

= 13. How to make this plugin WPMU compatible?. =

Just copy the plugin into wp-content/plugins and then activate it on each blog where you want to use it.

= 14. Is there a way to force pre-buffering to load a chunk of the video before the splash screen appears?. =

This option is not available. With autobuffer, it means every visitor on every visit to your page will be downloading the video. This means that you use a lot more bandwidth than on demand. I know that I actually watch the video on only about 1/3 of the pages with video that I visit. That saves you money (no bandwidth overages) and means that people who do want to watch the video and other visitors to your site get faster performance.
If you want to autobuffer, you can turn that on in the options (we turn it off by default and recommend that it stays off).

= 15. My videos are hosted with Amazon S3 service. How can I fill the details into shortcode?. =

Just enter the URL of your video hosted on Amazon S3 as the video source.

= 16. Is there a way to remove the share (embed) button? =
Yes, there's a global option in settings to disable sharing/embed. We plan to add an individual flag on a per video basis to allow sharing when sharing is turned off globally and vice versa.

= 17. My videos are taking long time to load. =

1. Check your hosting for download speed.
2. Try to use different settings when encoding the videos, try to turn on the cache when encoding with [Quick Time](http://drop.foliovision.com/webwork/it/quick-time-pro-cache-hint.png)

= 18. How can I customize the player control bar? I want to add a play/pause button. =

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

= 19. Minify plugins are interfering with FV Player =

Read our guide [Using FV Player with Minify Plugins](https://foliovision.com/player/advanced/player-minify-plugins). There you'll find how to set up plugins such as Autoptimize or WP Rocket so they work properly with the FV Player.

= 20. What if FV Player doesn't work for me? =

No worries.

1. You can always downgrade to version the Flash version ([delete the plugin then grab older version here and install from the ZIP file](https://wordpress.org/plugins/fv-wordpress-flowplayer/developers/)). If you downgrade to version 1.x you do lose a lot of mobile and iOS capability but you didn't have it in the first place.
1. Contact us via [support](https://foliovision.com/support). We are actively investigating and fixing people's sites now during the initial release period. We will help you to get FV Player 7 working in your environment.

FV Player Pro comes with a money back guarantee so you can even try the commercial no-branding version risk free. Or make it work first with the free versions.

Thank you for being part of the HMTL 5 mobile video revolution!

= 21. I can't see overlay ads on my videos =

The problem is probably in AdBlock. If it's active, the overlay ads will be blocked. Once AdBlock is deactivated for the particular domain where the video is played, the overlay ads will be displayed (page refresh needed).

= 22. My YouTube video doesn't show properly in fullscreen =

There is an possible issue with some themes: YouTube video opens in fullscreen, but after minimizing and opening fullscreen again, the video is shrinked in the left part of the screen (as in [this example](http://screenshots.foliovision.com/431J0P0z0v3s)). You need to copy this CSS into your theme style sheet:

`iframe.fvyoutube-engine {`
    `width: 100% !important;`
`}`

You can optionally edit your theme's JS to prevent the shrinking.

=======

== Screenshots ==

1. FV Player different skin options
2. FV Player shortcode in post content
3. It's easy to use our shortcode editor to add videos
4. Plugin settings screen
5. Video checker helps you find issues with your video encoding

== Changelog ==

= 8.0 - 2023-09-19 =

* Initial release of FV Player 8
* Subtitles - fix for RTL languages when the line contains latin word - thanks to Olivier Legendre

== External Services ==

FV Player uses the following external services all of which are optional:

* FV Player Pro license checks via foliovision.com - only used if you click the button to install FV Player Pro extension
* AWS - if you setup Amazon S3 with FV Player
* DigitalOcean Spaces - if you setup DigitalOcean Spaces with FV Player
* Google API via googleapis.com and gdata.youtube.com - if you setup YouTube API key to be able to obtain video splash screens, video titles and duration information when inserting videos from YouTube
* Bunny Stream API via video.bunnycdn.com - only if you setup Bunny Stream
* FV Video Checker on video-checker.foliovision.com - when you install the plugin you are prompted if you allow access to your video files to check for video encoding issues. This can also be disabled using the "Disable Admin Video Checker" setting later.
* Wistia embed codes if you use post Wistia videos