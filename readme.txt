=== FV Wordpress Flowplayer ===
Contributors: FolioVision
Donate link: http://foliovision.com/donate/
Tags: video, flash, flowplayer, player, jwplayer
Requires at least: 2.9
Tested up to: 3.4.1
Stable tag: 1.2.17

Embed videos (FLV, H.264, and MP4) into posts or pages. Warning: this version includes Flowplayer logos on full screen video and on canvas.

== Description ==

FV Wordpress Flowplayer plugin is a free, easy-to-use, and complete solution for embedding FLV or MP4 videos into your posts or pages. 

* Plugin contains unmodified opensource version of Flowplayer 3.2.3. This version of our FV player plugin does include Flowplayer branding. Last version without branding is 1.0.6.
* Supported video formats are FLV, H.264, and MP4. Multiple videos can be displayed in one post or page.
* Plugin tested compatible with all Wordpress versions from 2.5 through 3.0.1
* Default options for all the embedded videos can be set in comprehensive administration menu.
* In comparison with Wordpress Flowplayer plugin, there are several improvements:

	1. Doesn't use jQuery, so there will be no future conflicts with other plugins.
	2. Usage is simpler and forgiving, making the plugin easier to use.
	3. It will never display any annoying flowplayer logos or copyrights over your videos. (No longer true. Future Foliovision unbranded non-Flowplayer plugin in preparation.)
	4. Allows user to display clickable splash screen at the beginning of video (which not only looks good, but improves the performance significantly).
	5. Allows user to display popup box after the video ends, with any HTML content (clickable links, images, styling, etc.)
	6. Allows to upload videos and images through WP Media Library
	7. Does not use configuration file, but Wordpress Options

**[Download now!](http://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer)**

[Support](http://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer) |
[Change Log](http://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer/changelog) |
[Installation](http://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer/installation)|
[Usage](http://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer/user-guide) | 
[FAQ](http://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer/faq)


== Installation ==

There aren't any special requirements for FV Wordpress Flowplayer to work, and you don't need to install any additional plugins.

   1. Download and unpack zip archive containing the plugin.
   2. Upload the fv-wordpress-flowplayer directory into wp-content/plugins/ directory of your wordpress installation.
   3. Go into Wordpress plugins setup in Wordpress administration interface and activate FV Wordpress Flowplayer plugin.
   4. Optionally, if you want to embed videos denoted just by their filename, you can create the /videos/ directory located directly in the root of your domain and place your videos there. Otherwise, you would have to type in a complete URL of video files.

   
== Frequently Asked Questions ==

= I get an error message like this when activating the plugin: Parse error: parse error, unexpected T_STRING, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or '}' in /wp-content/plugins/fv-wordpress-flowplayer/models/flowplayer.php on line 4 =

You need to use at least PHP 5, your site is probably still running on old PHP 5. 

= I installed the plugin, inserted the video, but it's not working, only a gray box appears. =

FV Flowplayer calls some javascript from the footer. That means your footer.php file must contain the &lt;?php wp_footer(); ?&gt; Wordpress hook. Almost all themes do this out of the box, but if you've customised your theme there's a chance that you might have deleted this call.

= I tried to change some setting in the admin section, but without effect.  =

If you used v1.0.4 or less, please make sure, that configuration file wpfp.conf is writable (666 permissions).

= You player works just fine, but there are some weird display issues. =

Please check if these issues also appear when using the default Wordpress template. There seems to be some sort of conflict between the Flowplayer CSS and your theme CSS.

= How to make this plugin WPMU compatible? =

Just copy the plugin into wp-content/plugins and then activate it on each blog where you want to use it.

= Is there a way to force pre-buffering to load a chunk of the video before the splash screen appears? =

This option is not available. With autobuffer, it means every visitor on every visit to your page will be downloading the video. This means that you use a lot more bandwidth than on demand. I know that I actually watch the video on only about 1/3 of the pages with video that I visit. That saves you money (no bandwidth overages) and means that people who do want to watch the video and other visitors to your site get faster performance.
If you want to autobuffer, you can turn that on in the options (we turn it off by default and recommend that it stays off).

= My videos are hosted with Amazon S3 service. How can I fill the details into shortcode? =

Currently there is no support for Amazon S3 service, this feature might be added in the future. 

= The spinning circle is off centre when the video is loading. =

This happens when you set width and height of the video other than are native dimensions. We recommend to use native dimensions of the video when placing on a webpage. 

= The splash image and controlbar are not working properly in widgets. =

Please upgrade to version at least 1.0.6.

= I would like to localize the play again button. =

Currently there is no support for other languages. Some localizations for Flowplayer exists, but there is no official support from flowplayer.org.

= Where can I change the default directory for videos? =

You can change this manually in the the models/flowplayer.php in the flowplayer_head function. It you use videos in widgets you might need to edit the function flowplayer_content in controller/frontend.php as well. Please be carefull when editing source codes.

= How do I insert flowplayer object outside the post, for example to a sidebar? =

You need to use following code to include the shortcode into a sidebar:

echo apply_filters('the_content', '[flowplayer src=yourvideo.mp4 width=240 height=320]');

Fill the Flowplayer shortcode part according to your needs. The apply filter needs to be called because the flowplayer shortcodes are not parsen outside posts automatically. Also, please do not forget to add the echo at the beginning.

= How can I remove the black border around the video? =

The black border is defined in the style sheet, located in the /css/flowplayer.css at line 6

= I do own a license key and I would like to use the latest version Flowplayer provided to me. =

Replace the /flowplayer/commercial/flowplayer.swf with the newest Flowplayer (strip out additional version numbers from the file name). Go to admin section and enter your licence key. Don't forget to click 'Apply changes', and you're ready to use your commercial version. 

= How do I change the size of the play button? =

The size is defined to be 83x83px, defined in /modules/flowplayer-frontend.php line 118.

= How do I change the position of the play button? =

The position of the play button is defined at two places, first is the css file (.splash_play_button) and in the /modules/flowplayer-frontend.php (line 118, starting with $splash = ...). To modify the vertical position modify top=round($height/2-45) to your custom value. Try for example values like 0 or $height to move the play button up or down.

= When viewing the video in fullscreen mode, it is stretched and looks deformed. =

If you have version 1.2.2 or higher, than go to settings and set Fit scaling to true.

For versions below 1.2.2, this happens because the scaling is set by default to fill. If you wish the video show up with correct aspect ratio, you need to add following piece of code into flowplayer-frontend.php, around the line 155 into the clip section:

scaling: \'fit\',

Also don't forget comma at the end of the line where appropriate. Be aware that this scaling will affect also non-fullscreen mode, which might result into borders around your video if the dimensions are not properly set.

= How can I style the popup? =

Check out .flowplayer_popup and .wpfp_custom_popup in /fv-wordpress-flowplayer/css/flowplayer.css. You might want to move your changes to your template CSS - make sure you use ID of container element, so your declarations will work even when the flowplayer.css is loaded later in the head section of your webpage.

= How can I style the popup? =

Check out .flowplayer_popup and .wpfp_custom_popup in /fv-wordpress-flowplayer/css/flowplayer.css. You might want to move your changes to your template CSS - make sure you use ID of container element, so your declarations will work even when the flowplayer.css is loaded later in the head section of your webpage.

= My videos are taking long time to load. =

1. Check your hosting for download speed.
2. Try to use different settings when encoding the videos, try to turn on the cache when encoding with [Quick Time](http://drop.foliovision.com/webwork/it/quick-time-pro-cache-hint.png)

= Is it possible to loop the video? =

No at the moment we do not support looping.

= How do I insert videos in playlist? =

Playlist feature is not supported right now.

=======

== Screenshots ==

1. Post containing modified flowplayer playing a video.
2. Adding three players with different arguments into a post.
3. Add new video dialog window in editing mode.
4. Configuration menu for administrators.

== Changelog ==

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

== Upgrade Notice ==

= 1.1 =
Warning! This version includes Flowplayer logos on canvas and full screen. Do not upgrade if you would prefer an unbranded video player.
