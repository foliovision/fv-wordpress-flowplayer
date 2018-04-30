<?php

class FV_Xml_Video_Sitemap {

    public function __construct() {
        // Add our custom rewrite rules
        add_filter('init', array($this, 'fv_check_xml_sitemap_rewrite_rules'));
        add_action('do_feed_video-sitemap', array($this, 'fv_generate_video_sitemap'), 10, 1);
        add_action('do_feed_video-sitemap-index', array($this, 'fv_generate_video_sitemap_index'), 10, 1);
    }
    
    function get_post_types() {
      $types = array_keys( get_post_types( array( 'public' => true ) ) );
      unset($types['revision'], $types['attachment'], $types['topic'], $types['reply']);
      return $types;
    }
    
    public static function get_video_details( $posts ) {
      global $fv_fp;

      $videos = array();

      foreach ($posts as $objPost) {
        if ( $objPost ) {
          $content = $objPost->post_content;
          preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $content, $matches );
          // todo: perhaps include videos in postmeta too
        }
        
        $sanitized_description = !empty($objPost->post_excerpt) ? $objPost->post_excerpt : wp_trim_words( strip_shortcodes($objPost->post_content),10, '...');
        $sanitized_description = htmlspecialchars( $sanitized_description,ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );

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
              $new_video_record['video']['description'] = $new_video_record['video']['title'];
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
    
    function get_video_years() {
      global $wpdb;
      
      return $wpdb->get_results( "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts WHERE post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %') GROUP BY YEAR(post_date) ORDER BY post_date;" );
    }

    function fv_check_xml_sitemap_rewrite_rules() {
      global $wp_rewrite;

      $rules = get_option( 'rewrite_rules' );

      if (!isset($rules['video-sitemap\.xml$'])) {
        $wp_rewrite->flush_rules();
        add_rewrite_rule('video-sitemap\.xml$', 'index.php?feed=video-sitemap-index', 'top');
        add_rewrite_rule('video-sitemap\.(\d+)\.xml$', 'index.php?feed=video-sitemap&year=$matches[1]', 'top');
      }
    }

    function fv_generate_video_sitemap() {
      global $wpdb, $fv_wp_flowplayer_ver;

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

      $videos = $wpdb->get_results( "SELECT ID, post_content, post_title, post_excerpt, post_date, post_name, post_status, post_parent, post_type, guid FROM $wpdb->posts WHERE year(post_date) = ".get_query_var('year')." AND post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %')" );

      $get_post_array = array();
      foreach ($videos as $vid) {
        $get_post_array[] = $vid;
      }

      $data = $this->get_video_details($get_post_array);

      if (count($data)) {
        echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
        echo '<'.'?xml-stylesheet type="text/xsl" href="'.flowplayer::get_plugin_url().'/css/sitemap-video.xsl?ver='.$fv_wp_flowplayer_ver.'"?'.'>'."\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">'."\n";
        foreach ($data as $video) {
          echo "\t<url>\n";
          echo "\t\t<loc>".$video['loc']."</loc>\n";
          echo "\t\t<video:video>\n";
          foreach ($video['video'] as $videoTag => $videoTagValue) {
            echo "\t\t\t<video:$videoTag>$videoTagValue</video:$videoTag>\n";
          }
          echo "\t\t</video:video>\n";
          echo "\t</url>\n";
        }
        echo "</urlset>\n";
        echo "<!-- XML Sitemap generated by FV Player -->\n";
      }
    }
    
    function fv_generate_video_sitemap_index() {
      global $wpdb, $fv_wp_flowplayer_ver;

      // if output buffering is active, clear it
      if ( ob_get_level() ) ob_clean();

      if ( !headers_sent($filename, $linenum) ) {
        status_header('200'); // force header('HTTP/1.1 200 OK') for sites without posts
        header('Content-Type: text/xml; charset=' . get_bloginfo('charset'), true);
        header('X-Robots-Tag: noindex, follow', true);
      }

      // This is to prevent issues with New Relic's stupid auto injection of code.
      if ( extension_loaded( 'newrelic' ) && function_exists( 'newrelic_disable_autorum' ) ) {
        newrelic_disable_autorum();
      }
      
      $years = $this->get_video_years();
      
      if( $years ) :
        echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
        echo '<'.'?xml-stylesheet type="text/xsl" href="'.flowplayer::get_plugin_url().'/css/sitemap-index.xsl?ver='.$fv_wp_flowplayer_ver.'"?'.'>'."\n";
        ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">
  <?php foreach( $years AS $objYear ) :
    
    $last_modified = $wpdb->get_var( "SELECT post_modified_gmt FROM $wpdb->posts WHERE post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %') AND year(post_date) = ".$objYear->year." ORDER BY post_modified_gmt DESC LIMIT 1" );
    ?>
    <sitemap>
  		<loc><?php echo home_url('/video-sitemap.'.$objYear->year.'.xml'); ?></loc>
  		<lastmod><?php echo mysql2date('Y-m-d\TH:i:s+00:00', $last_modified, false); ?></lastmod>
  	</sitemap>    
  <?php endforeach; ?>
</sitemapindex><!-- XML Sitemap generated by FV Player -->
      <?php
      endif;
    }    
}

$FV_Xml_Video_Sitemap = new FV_Xml_Video_Sitemap();