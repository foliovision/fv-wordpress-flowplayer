<?php

class FV_Xml_Video_Sitemap {

    public function __construct() {
        // Add our custom rewrite rules
        add_filter('init', array($this, 'fv_check_xml_sitemap_rewrite_rules'));
        add_action('do_feed_video-sitemap', array($this, 'fv_generate_video_sitemap'), 10, 1);
    }
    
    public static function get_videos_details( $posts ) {
      global $fv_fp;

      $videos = array();

      foreach ($posts as $objPost) {
        if ( $objPost ) {
          $content = $objPost->post_content;
          preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $content, $matches );
          // todo: perhaps include videos in postmeta too
        }

        if ( isset( $matches[0] ) && count( $matches[0] ) ) {
          $video_alt_captions_counter = 1;

          foreach ( $matches[0] AS $shortcode ) {
            $increment_video_counter = false;
            $aArgs = shortcode_parse_atts( rtrim( $shortcode, ']' ) );

            // sitemap data generation - remove the first item (start of the tag)
            // and leave everything else that was defined
            $new_video_record = array(
                // landing page
                'loc' => get_permalink($objPost),
                'video' => array()
              );

            // this crazyness needs to be first converted into non-html characters (so &quot; becomes "), then
            // stripped of them all and returned back HTML-encoded for the XML formatting to be correct
            $trimmed_splash = htmlspecialchars(trim(html_entity_decode($aArgs['splash']), "\n\t\r\0\x0B".'"'));

            // update splash in accordance to what has been found
            $aArgs['splash'] = $trimmed_splash ? $trimmed_splash : plugins_url('css/img/play_white.png', __DIR__);

            // check for caption - if none present, build it up from page title and video position
            // note: html characters must be substituted or enclosed in CDATA, from which the first
            //       is easier to do correctly on a single line
            $sanitized_caption = htmlspecialchars(strip_tags($aArgs['caption']), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );

            // sanitized post title, used when no video caption is provided
            $sanitized_page_title = htmlspecialchars(strip_tags($objPost->post_title), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );

            // use excerpt as video description, or part of the content if excerpt is missing
            // max length = 2040 characters, so take first 1800 to be on the safe side with UTF-8 & UTF-16 chars
            $sanitized_description = htmlspecialchars(
              preg_replace(
                  ['~\[(?:flowplayer|fvplayer).*?\]~', '~(\r)?\n~'],
                  ' ',
                  strip_tags(!empty($objPost->excerpt) ? $objPost->post_excerpt : $objPost->post_content)
                ),
              ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE
            );

            // set thumbnail
            $new_video_record['video']['thumbnail_loc'] = $aArgs['splash'];

            // set video title
            if (!empty($sanitized_caption)) {
              $new_video_record['video']['title'] = $sanitized_caption;
            } else {
              if (!empty($sanitized_page_title)) {
                $new_video_record['video']['title'] = $sanitized_page_title;
              } else {
                $new_video_record['video']['title'] = 'Video ' . $video_alt_captions_counter;
                $increment_video_counter = true;
              }
            }

            // don't return empty descriptions (can happen if the video tag it the only thing on the page)
            if (empty(trim($sanitized_description))) {
              preg_match(
                  '/^.{1,1800}\b/s',
                  $sanitized_description,
                  $description_match
                );
              $new_video_record['video']['description'] = ( ! empty( $description_match[0] ) ? $description_match[0] : (!$increment_video_counter ? 'Video '.$video_alt_captions_counter.', ' : '').$new_video_record['video']['title']);
              $increment_video_counter = true;
            } else {
              $new_video_record['video']['description'] = $sanitized_description;
            }

            // update video counter used for naming videos without caption on pages without titles
            if ($increment_video_counter) {
              $video_alt_captions_counter++;
            }

            // files with extensions are considered direct video files,
            // everything else is considered a path to player location
            // note: we check for strlen($extension) < 10, since abc.com would otherwise register as extension
            if ((strpos($aArgs['src'], '.') !== false) && ($extension = substr(strrchr($aArgs['src'], "."), 1)) && strlen($extension) < 10) {
              // filename URL
              $new_video_record['video']['content_loc'] = $aArgs['src'];
            } else {
              // player URL
              $new_video_record['video']['player_loc'] = $aArgs['src'];
            }

            $videos[] = $new_video_record;
            
          }
        }

        if ( count( $videos ) > 0 ) {

        } else {
          $videos = false;
        }
      }

      return $videos;
    }    

    function fv_check_xml_sitemap_rewrite_rules() {
        global $wp_rewrite;

        $rules = get_option( 'rewrite_rules' );

        if (!isset($rules['video-sitemap\.xml$'])) {
            $wp_rewrite->flush_rules();
            add_rewrite_rule('video-sitemap\.xml$', 'index.php?feed=video-sitemap', 'top');
        }
    }

    function fv_generate_video_sitemap() {
        global $wpdb;

        // if output buffering is active, clear it
        if ( ob_get_level() ) ob_clean();

        if ( !headers_sent($filename, $linenum) ) {
            status_header('200'); // force header('HTTP/1.1 200 OK') for sites without posts
            header('Content-Type: text/xml; charset=' . get_bloginfo('charset'), true);
            header('X-Robots-Tag: noindex, follow', true);
        }

        // This is to prevent issues with New Relics stupid auto injection of code.
        if ( extension_loaded( 'newrelic' ) && function_exists( 'newrelic_disable_autorum' ) ) {
            newrelic_disable_autorum();
        }

        // get public post types
        $types = array_keys( get_post_types( array( 'public' => true ) ) );

        // remove revisions, forum topics / posts and attachments from the select
        unset($types['revision'], $types['attachment'], $types['topic'], $types['reply']);
        $videos = $wpdb->get_results( "SELECT ID, post_content, post_title, post_excerpt, post_content, post_date, post_name, post_status, post_parent, post_type, guid FROM $wpdb->posts WHERE post_type IN(\"".implode('", "', $types)."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %')" );

        $get_post_array = array();
        foreach ($videos as $vid) {
            $get_post_array[] = $vid;
        }

        $data = $this->get_video_details($get_post_array);

        if (count($data)) {
            // echo is for PHP short tags
            echo "<?";?>xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"><?php
                foreach ($data as $video) {
                    ?>

                    <url>
                    <loc><?php echo $video['loc']; ?></loc>
                    <video:video><?php
                        foreach ($video['video'] as $videoTag => $videoTagValue) {
                            ?>

                            <video:<?php echo $videoTag; ?>><?php echo $videoTagValue ?></video:<?php echo $videoTag; ?>><?php
                        }
                        ?>

                    </video:video>
                    </url><?php
                }
                ?>
            </urlset>
            <!-- XML Sitemap generated by FW Flow Player --><?php
        }
    }
}

$FV_Xml_Video_Sitemap = new FV_Xml_Video_Sitemap();