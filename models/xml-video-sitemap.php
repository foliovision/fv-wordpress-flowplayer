<?php

class FV_Xml_Video_Sitemap {

    public function __construct() {
        // Add our custom rewrite rules
        add_filter('init', array($this, 'fv_check_xml_sitemap_rewrite_rules'), 999 );
        add_action('do_feed_video-sitemap', array($this, 'fv_generate_video_sitemap'), 10, 1);
        add_action('do_feed_video-sitemap-index', array($this, 'fv_generate_video_sitemap_index'), 10, 1);
        
        add_action( 'parse_request', array($this, 'fix_query_vars') );
    }
    
    function fix_query_vars( $query ) { // stop Yoast SEO from interferring
      global $fv_fp;
      if( !$fv_fp->_get_option('video_sitemap') ) return $query;
      
      if( !empty($query->query_vars['sitemap']) && $query->query_vars['sitemap'] == 'video' ) {
        unset($query->query_vars['sitemap']);
        $query->query_vars['feed'] = 'video-sitemap-index';
      }
      return $query;
    }
    
    function get_post_types() {
      $types = get_post_types( array( 'public' => true ) );
      unset($types['revision'], $types['attachment'], $types['topic'], $types['reply']);
      return array_keys($types);
    }
    
    public static function get_video_details( $posts ) {
      global $fv_fp;

      $videos = array();
      
      $dynamic_domains = apply_filters('fv_player_pro_video_ajaxify_domains', array());
      $amazon = $fv_fp->_get_option('amazon_bucket');
      if( $amazon && is_array($amazon) && count($amazon) > 0 ) {
        foreach( $amazon AS $bucket ) {
          $dynamic_domains[] = 'amazonaws.com/'.$bucket.'/';
          $dynamic_domains[] = '//'.$bucket.'.s3';
        }      
      }

      foreach ($posts as $objPost) {
        if ( $objPost ) {
          $content = $objPost->post_content;
          $content = preg_replace( '~<code>.*?</code>~', '', $content );
          preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $content, $matches );
          // todo: perhaps include videos in postmeta too
        }
        
        if( $meta = get_post_meta($objPost->ID, '_aioseop_description', true ) ) {
          $sanitized_description = $meta;
        } else if( $meta = get_post_meta($objPost->ID, '_yoast_wpseo_metadesc', true ) ) {
          $sanitized_description = $meta;
        } else if( $meta = get_post_meta($objPost->ID, '_genesis_description', true ) ) {
          $sanitized_description = $meta;
        } else {
          $sanitized_description = !empty($objPost->post_excerpt) ? $objPost->post_excerpt : wp_trim_words( strip_shortcodes($objPost->post_content),10, '...');          
        }
        
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
            $splash = !empty($aArgs['poster']) ? $aArgs['poster'] : $aArgs['splash'];
            $splash = htmlspecialchars(trim(html_entity_decode($splash), "\n\t\r\0\x0B".'"'));
            
            // make sure the URLs are absolute
            if( $splash ) {
              if( stripos($splash,'http://') !== 0 && stripos($splash,'https://') !== 0 && stripos($splash,'//') !== 0 ) {
                $splash = home_url($splash);
              } else if( stripos($trimmed_splash,'//') === 0 ) {
                $splash = 'http:'.$splash;
              }
            } else {
              $splash = plugins_url('css/img/play_white.png', __DIR__);
            }            

            // check for caption - if none present, build it up from page title and video position
            // note: html characters must be substituted or enclosed in CDATA, from which the first
            //       is easier to do correctly on a single line
            $sanitized_caption = htmlspecialchars(strip_tags($aArgs['caption']), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );
            $sanitized_src = htmlspecialchars(strip_tags(trim($aArgs['src'])), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );
            
            // make sure the URLs are absolute
            if( $sanitized_src ) {
              if( stripos($sanitized_src,'http://') !== 0 && stripos($sanitized_src,'https://') !== 0 && stripos($sanitized_src,'//') !== 0 ) {
                $sanitized_src = home_url($sanitized_src);
              } else if( stripos($sanitized_src,'//') === 0 ) {
                $sanitized_src = 'https:'.$sanitized_src;
              }
            }            

            // sanitized post title, used when no video caption is provided
            $sanitized_page_title = htmlspecialchars(strip_tags($objPost->post_title), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );

            // set thumbnail
            $new_video_record['video']['thumbnail_loc'] = apply_filters( 'fv_player_sitemap_thumbnail', $splash, $aArgs['src'], $objPost->ID );

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
            if( strlen(trim($sanitized_description)) == 0 ) {
              $new_video_record['video']['description'] = $new_video_record['video']['title'];
              $increment_video_counter = true;
            } else {
              $new_video_record['video']['description'] = $sanitized_description;
            }
            
            if( $aCategories = get_the_category($objPost->ID) ) {
              $new_video_record['video']['category'] = mb_substr( implode(', ',wp_list_pluck($aCategories,'name')), 0, 250 );
            }
            $new_video_record['video']['publication_date'] = get_the_date(DATE_W3C, $objPost->ID);

            // update video counter used for naming videos without caption on pages without titles
            if ($increment_video_counter) {
              $video_alt_captions_counter++;
            }
            
            if( count($dynamic_domains) ) {
              $is_dynamic = false;
              foreach( $dynamic_domains AS $domain ) {
                if( stripos($sanitized_src,$domain) !== false ) {
                  $is_dynamic = true;
                }
              }
              if( $is_dynamic ) continue;
            }

            // files with extensions are considered direct video files,
            // everything else is considered a path to player location
            // note: we check for strlen($extension) < 10, since abc.com would otherwise register as extension
            if ((strpos($aArgs['src'], '.') !== false) && ($extension = substr(strrchr($aArgs['src'], "."), 1)) && strlen($extension) < 10) {
              // filename URL
              $new_video_record['video']['content_loc'] = $sanitized_src;
            } else {
              continue;
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
      
      // grouped by year and month
      $years_and_months = $wpdb->get_results( "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts WHERE post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %') GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date;" );
      return $years_and_months;
    }

    function fv_check_xml_sitemap_rewrite_rules() {
      global $wp_rewrite, $fv_fp;

      $rules = get_option( 'rewrite_rules' );

      if( $fv_fp->_get_option('video_sitemap') && !isset($rules['video-sitemap\.xml$']) ) {
        $wp_rewrite->flush_rules();
        add_rewrite_rule('video-sitemap\.xml$', 'index.php?feed=video-sitemap-index', 'top');        
        add_rewrite_rule('video-sitemap\.(\d\d\d\d)-(\d\d)\.xml$', 'index.php?feed=video-sitemap&year=$matches[1]&monthnum=$matches[2]', 'top');  
        add_rewrite_rule('video-sitemap\.(\d\d\d\d)\.xml$', 'index.php?feed=video-sitemap&year=$matches[1]', 'top');
      }
    }

    function fv_generate_video_sitemap() {
      global $wpdb, $fv_wp_flowplayer_ver, $fv_fp;
      
      if( !$fv_fp->_get_option('video_sitemap') ) return;

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
      
      $date_query = false;
      if( get_query_var('year') && get_query_var('monthnum') ) {
        $date_query = " year(post_date) = ".get_query_var('year')." AND month(post_date) = ".get_query_var('monthnum')." AND ";
      } else if( get_query_var('year') ) {
        $date_query = " year(post_date) = ".get_query_var('year')." AND ";
      }
      
      $videos = $wpdb->get_results( "SELECT ID, post_content, post_title, post_excerpt, post_date, post_name, post_status, post_parent, post_type, guid FROM $wpdb->posts WHERE $date_query post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %')" );

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
      global $wpdb, $fv_wp_flowplayer_ver, $fv_fp;
      
      if( !$fv_fp->_get_option('video_sitemap') ) return;

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
    $filename = $objYear->year;
    if( !empty($objYear->month) ) {
      $filename .= '-';
      if( strlen($objYear->month) == 1 ) $filename .= '0';
      $filename .= $objYear->month;
    }
    
    $date_query = !empty($objYear->month) ? " AND month(post_date) = ".intval($objYear->month) : false;    
    
    $last_modified = $wpdb->get_var( "SELECT post_modified_gmt FROM $wpdb->posts WHERE post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %') AND year(post_date) = ".intval($objYear->year)." $date_query ORDER BY post_modified_gmt DESC LIMIT 1" );
    ?>
    <sitemap>
  		<loc><?php echo home_url('/video-sitemap.'.$filename.'.xml'); ?></loc>
  		<lastmod><?php echo mysql2date('Y-m-d\TH:i:s+00:00', $last_modified, false); ?></lastmod>
  	</sitemap>    
  <?php endforeach; ?>
</sitemapindex><!-- XML Sitemap generated by FV Player -->
      <?php
      endif;
    }    
}

$FV_Xml_Video_Sitemap = new FV_Xml_Video_Sitemap();