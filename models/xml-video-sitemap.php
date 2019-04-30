<?php

class FV_Xml_Video_Sitemap {
    
    public function __construct() {
        // Add our custom rewrite rules
        add_filter('init', array($this, 'fv_check_xml_sitemap_rewrite_rules'), 999 );
        add_action('do_feed_video-sitemap', array($this, 'fv_generate_video_sitemap'), 10, 1);
        add_action('do_feed_video-sitemap-index', array($this, 'fv_generate_video_sitemap_index'), 10, 1);
        
        add_action( 'parse_request', array($this, 'fix_query_vars') );
        add_filter( 'query_vars', array($this, 'custom_query_vars') ); // we need to use custom query vars as otherwise Yoast SEO could stop the sitemap from working as it could be detected by $wp_query->is_date
        
        add_filter( 'redirect_canonical', array( $this, 'redirect_canonical' ) ); // stop trailing slashes on sitemap URLs
        add_filter( 'fv_flowplayer_settings_save', array($this, 'settings_save') ); // whitelist symbols
        add_action( 'fv_flowplayer_admin_seo_after', array( $this, 'options' ) );
    }
    
    function custom_query_vars( $vars ) {
      $vars[] = 'fvp_sitemap_year';
      $vars[] = 'fvp_sitemap_monthnum';
      return $vars;
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
    
    function get_meta_keys( $mode = false ) {
      global $fv_fp;
      if( !empty($fv_fp) && $fv_fp->_get_option('video_sitemap_meta') ) {
        $keys = explode(',', esc_sql($fv_fp->_get_option('video_sitemap_meta')) );        
        if( $mode == 'sql' ) return "'".implode("','",$keys)."'";
        return $keys;
      }
      return false;
    }
    
    function get_post_types() {
      $types = get_post_types( array( 'public' => true ) );
      unset($types['revision'], $types['attachment'], $types['topic'], $types['reply']);
      return array_keys($types);
    }
    
    function get_video_details( $posts ) {
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
      
      $cf = $fv_fp->_get_option( array('pro','cf_domain') );
      if( $cf ) {
        $cf = explode( ',', $cf );
        if( is_array($cf) && count($cf) > 0 ) {
          foreach( $cf AS $cf_domain ) {
            $dynamic_domains[] = $cf_domain;
          }
        }
      }

      foreach ($posts as $objPost) {
        $count = 0;
          
        $permalink = get_permalink($objPost);

        $xml_loc = array(
            // landing page
            'loc' => $permalink,
            'video' => array()
          );
        
        $used_videos = array();
        $used_titles = array();
        $used_descriptions = array();
        
        $content = $objPost->post_content;
        $content = preg_replace( '~<code.*?</code>~', '', $content );
        $content = preg_replace( '~<pre[\s\S]*?</pre>~', '', $content );
        
        $content = $this->strip_membership_content($content);
        
        preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $content, $matches );
        
        if( $this->get_meta_keys() ) {
          foreach( $this->get_meta_keys() AS $meta_key ) {
            $meta_values = implode( get_post_meta($objPost->ID, $meta_key) );
            
            $meta_values = $this->strip_membership_content($meta_values);
            
            preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $meta_values, $meta_matches );
            if( is_array($meta_matches) && count($meta_matches) > 0 ) {
              $matches[$meta_key] = $meta_matches[0];
            }
          }
        }
        
        $content_no_tags = preg_replace( '~</p>~', "</p>\n", $content );
        $content_no_tags = preg_replace( '~\n+~', "\n", $content );
        $content_no_tags = strip_tags( $content_no_tags );
        $content_no_tags = explode( "\n", $content_no_tags );
        
        // we apply the shortcodes to make sure any membership restrictions work, but we omit the FV Player shortcodes as we want to parse these elsewhere
        $content = str_replace( array('[fvplayer','[flowplayer'), '[noplayer', $content );
        $content = do_shortcode($content);
        $content = str_replace( '[noplayer', '[fvplayer', $content );
        
        if( $meta = get_post_meta($objPost->ID, '_aioseop_description', true ) ) {
          $description = $meta;
        } else if( $meta = get_post_meta($objPost->ID, '_yoast_wpseo_metadesc', true ) ) {
          $description = $meta;
        } else if( $meta = get_post_meta($objPost->ID, '_genesis_description', true ) ) {
          $description = $meta;
        } else {
          $description = !empty($objPost->post_excerpt) ? $objPost->post_excerpt : wp_trim_words( strip_shortcodes($objPost->post_content),10, '...');
        }
        
        $description = htmlspecialchars( $description,ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );

        foreach ( $matches AS $meta_key => $partial ) {          

          foreach ( $partial AS $key => $shortcode ) {
            $count++;
            
            $xml_video = array();
            
            $increment_video_counter = false;
            $aArgs = shortcode_parse_atts( rtrim( $shortcode, ']' ) );
            
            global $FV_Player_Db;
            if( !empty($FV_Player_Db) ) {
              $aArgs = $FV_Player_Db->getPlayerAttsFromDb( $aArgs );
            }
            
            if( empty($aArgs['embed']) ) $aArgs['embed'] = '';
            
            if( empty($aArgs['src']) || !empty($used_videos[$aArgs['src']]) ) continue;
            $used_videos[$aArgs['src']] = true;

            // this crazyness needs to be first converted into non-html characters (so &quot; becomes "), then
            // stripped of them all and returned back HTML-encoded for the XML formatting to be correct
            $splash = false;
            if( !empty($aArgs['poster']) ) $splash = $aArgs['poster'];
            if( !empty($aArgs['splash']) ) $splash = $aArgs['splash'];
            $splash = htmlspecialchars(trim(html_entity_decode($splash), "\n\t\r\0\x0B".'"'));
            
            // make sure the URLs are absolute
            if( $splash ) {
              if( stripos($splash,'http://') !== 0 && stripos($splash,'https://') !== 0 && stripos($splash,'//') !== 0 ) {
                $splash = home_url($splash);
              } else if( stripos($splash,'//') === 0 ) {
                $splash = 'http:'.$splash;
              }
            } else {
              $splash = get_the_post_thumbnail_url( $objPost->ID, 'thumbnail' );
              if( !$splash ) $splash = plugins_url('css/img/play_white.png', __DIR__);
            }            

            // check for caption - if none present, build it up from page title and video position
            // note: html characters must be substituted or enclosed in CDATA, from which the first
            //       is easier to do correctly on a single line
            $sanitized_caption = !empty($aArgs['caption']) ? htmlspecialchars(strip_tags($aArgs['caption']), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE ) : false;
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
            $xml_video['thumbnail_loc'] = apply_filters( 'fv_player_sitemap_thumbnail', $splash, $aArgs['src'], $objPost->ID );

            // set video title
            if (!empty($sanitized_caption)) {
              $title = $sanitized_caption;
            } else if (!empty($sanitized_page_title)) {
              $title = $sanitized_page_title;
            } else {
              $title = 'Video';
            }
            
            if( !empty($used_titles[$title]) ) {
              $used_titles[$title]++;
              $title .= ' '.$used_titles[$title];
            } else {
              $used_titles[$title] = 1;
            }
            
            $xml_video['title'] = $title;

            // don't return empty descriptions (can happen if the video tag it the only thing on the page)            
            if( strlen(trim($description)) == 0 ) {
              $description = $xml_video['title'];
            }
            
            $xml_video['description'] = $description;
            
            // if description is already in use try to find the previous sentence
            if( !empty($used_descriptions[$description]) ) {              
              $last = false;
              foreach( $content_no_tags AS $p ) {
                if( stripos($p,$shortcode) !== false ) break;
                $last = trim( strip_shortcodes($p) );
              }
              
              if( $last ) {
                $xml_video['description'] = wp_trim_words($last,20,'...');
              } else {
                $used_descriptions[$description]++;
                $xml_video['description'] .= ' '.$used_descriptions[$description];
              }
            } else {
              $used_descriptions[$description] = 1;
            }
            
            if( $aCategories = get_the_category($objPost->ID) ) {
              $xml_video['category'] = mb_substr( implode(', ',wp_list_pluck($aCategories,'name')), 0, 250 );
            }
            $xml_video['publication_date'] = get_the_date(DATE_W3C, $objPost->ID);
            
            if ((strpos($aArgs['src'], '.') !== false) && ($extension = substr(strrchr($aArgs['src'], "."), 1)) && strlen($extension) < 10) {
              // filename URL
              $xml_video['content_loc'] = $sanitized_src;
              
            } else if( $aArgs['embed'] == 'false' || $aArgs['embed'] == 'off' || ( $fv_fp->_get_option('disableembedding') && $aArgs['embed'] != 'true' ) ) {
              continue;
            
            } else {
              if( $player = $fv_fp->current_player() ) {
                $embed_id = 'fvp-'.$player->getId();
              } else {
                $embed_id = 'fvp';
                if( $count > 1 ) $embed_id .= $count;
              }
              $xml_video['player_loc'] = user_trailingslashit( trailingslashit($permalink).$embed_id );
            }

            $xml_loc['video'][] = $xml_video;
            
          }
        }

        if ( count($xml_loc['video']) > 0 ) {
          $videos[] = $xml_loc;
        }
      }
      
      return $videos;
    }
    
    function get_video_years() {
      global $wpdb;
      
      // grouped by year and month, each row is year, month, count
      $years_and_months = $wpdb->get_results( "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts WHERE post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %') GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date;" );
      
      if( $this->get_meta_keys() ) { // if we have some postmeta values to process
        $years_and_months_meta = $wpdb->get_results( "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts  FROM $wpdb->posts AS p JOIN $wpdb->postmeta AS m ON p.ID = m.post_id WHERE post_type IN('post') AND post_status  = 'publish' AND meta_key IN (".$this->get_meta_keys('sql').") GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date;" );
        
        if( $years_and_months_meta ) { // we have to marge these year, month, count rows into one array
          $years = array(); // multidimensional array year => month => count
          foreach( array_merge( $years_and_months, $years_and_months_meta ) AS $date ) {
            if( empty($years[$date->year]) ) $years[$date->year] = array();
            if( empty($years[$date->year][$date->month]) ) $years[$date->year][$date->month] = 0;
            $years[$date->year][$date->month] += $date->posts;
          }
          ksort($years);
          foreach( $years AS $k => $v ) {
            ksort($v);
            $years[$k] = $v;
          }
          
          $years_and_months = array(); // back to year, month, count rows
          foreach( $years AS $k => $year ) {
            foreach( $year AS $month => $count ) {
              $years_and_months[] = (object) array( 'year' => $k, 'month' => $month, 'posts' => $count );
            }
          }
        }
      }
      
      return $years_and_months;
    }

    function fv_check_xml_sitemap_rewrite_rules() {
      global $wp_rewrite, $fv_fp;

      $rules = get_option( 'rewrite_rules' );
      
      $aRules = array(
        'video-sitemap\.xml$' => 'index.php?feed=video-sitemap-index',
        'video-sitemap\.(\d\d\d\d)-(\d\d)\.xml$' =>  'index.php?feed=video-sitemap&fvp_sitemap_year=$matches[1]&fvp_sitemap_monthnum=$matches[2]',
        'video-sitemap\.(\d\d\d\d)\.xml$' => 'index.php?feed=video-sitemap&fvp_sitemap_year=$matches[1]',
      );
      $aKeys = array_keys($aRules);

      // flush rules if option is enabled and the second rewrite rule is not found or not matching
      if( $fv_fp->_get_option('video_sitemap') &&
        ( !isset($rules[$aKeys[1]]) ||
        $rules[$aKeys[1]] != $aRules[$aKeys[1]] )
      ) {
        $wp_rewrite->flush_rules();
        foreach( $aRules AS $rewrite => $vars ) {
          add_rewrite_rule( $rewrite, $vars, 'top');
        }
      
      } else if( !$fv_fp->_get_option('video_sitemap') && // remove the rules if found
        isset($rules[$aKeys[1]]) &&
        $rules[$aKeys[1]] == $aRules[$aKeys[1]] ) {
        $wp_rewrite->flush_rules();
      }
    }

    function fv_generate_video_sitemap() {
      global $fv_fp;

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
      
      $year = intval(get_query_var('fvp_sitemap_year'));
      $month = intval(get_query_var('fvp_sitemap_monthnum'));
      echo $this->fv_generate_video_sitemap_do( $year, $month );
    }
    
    function fv_generate_video_sitemap_do( $year, $month ) {
      global $wpdb, $fv_wp_flowplayer_ver;
      
      $date_query = false;
      if( $year && $month ) {
        $date_query = " year(post_date) = ".$year." AND month(post_date) = ".$month." AND ";
      } else if( get_query_var('year') ) {
        $date_query = " year(post_date) = ".$year." AND ";
      }
      
      $videos = $wpdb->get_results( "SELECT ID, post_content, post_title, post_excerpt, post_date, post_name, post_status, post_parent, post_type, guid FROM $wpdb->posts WHERE $date_query post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND (post_content LIKE '%[flowplayer %' OR post_content LIKE '%[fvplayer %')" );
      
      if( $this->get_meta_keys() ) {
        $videos_meta = $wpdb->get_results( "SELECT ID, post_content, post_title, post_excerpt, post_date, post_name, post_status, post_parent, post_type, guid FROM $wpdb->posts AS p JOIN $wpdb->postmeta AS m ON p.ID = m.post_id WHERE $date_query post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND meta_key IN (".$this->get_meta_keys('sql').")" );
        $videos = array_merge($videos,$videos_meta);
      }
      
      $get_post_array = array();
      foreach ($videos as $vid) {
        $get_post_array[] = $vid;
      }

      $data = $this->get_video_details($get_post_array);

      if (count($data)) {
        echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
        echo '<'.'?xml-stylesheet type="text/xsl" href="'.flowplayer::get_plugin_url().'/css/sitemap-video.xsl?ver='.$fv_wp_flowplayer_ver.'"?'.'>'."\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">'."\n";
        foreach ($data as $xml_loc) {
          echo "\t<url>\n";
          echo "\t\t<loc>".$xml_loc['loc']."</loc>\n";          
          foreach ($xml_loc['video'] as $xml_video ) {
            echo "\t\t<video:video>\n";
            foreach( $xml_video AS $videoTag => $videoTagValue) {
              echo "\t\t\t<video:$videoTag>$videoTagValue</video:$videoTag>\n";
            }
            echo "\t\t</video:video>\n";
          }          
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
    if( $this->get_meta_keys() ) {
      $last_modified_meta = $wpdb->get_var( "SELECT post_modified_gmt FROM $wpdb->posts AS p JOIN $wpdb->postmeta AS m ON p.ID = m.post_id WHERE post_type IN(\"".implode('", "', $this->get_post_types())."\") AND post_status  = 'publish' AND meta_key IN (".$this->get_meta_keys('sql').") AND year(post_date) = ".intval($objYear->year)." $date_query ORDER BY post_modified_gmt DESC LIMIT 1" );
      
      if( strtotime($last_modified_meta) > strtotime($last_modified) ) $last_modified = $last_modified_meta;
    }
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
    
    function options() {
      global $fv_fp;
      $fv_fp->_get_checkbox(__('Use XML Video Sitemap', 'fv-wordpress-flowplayer'), 'video_sitemap', sprintf( __('Creates <code>%s</code> which you can submit via Google Webmaster Tools.', 'fv-wordpress-flowplayer'), home_url('video-sitemap.xml') ), __('As feeds tend to be cached by web browser make sure you clear your browser cache if you are doing some testing.', 'fv-wordpress-flowplayer') );
      
      if( $fv_fp->_get_option('disableembedding') ) : ?>
        <tr>
          <td></td>
          <td><strong>Note:</strong> Since <a href="#fv_flowplayer_default_options">Disable Embed Button</a> setting is on the video sitemap can only present the bare MP4 or HLS videos. Disable that option to make it show your other video types too.</td>
        </tr>
      <?php endif;
      
      $fv_fp->_get_input_text( array( 'name' => __('Sitemap Post Meta', 'fv-wordpress-flowplayer'), 'key' => 'video_sitemap_meta', 'help' => __('You can enter post meta keys here, use <code>,</code> to separate multiple values.', 'fv-wordpress-flowplayer') ) );
    }
    
    function redirect_canonical( $redirect ) {
  		if( get_query_var('fvp_sitemap_year') || get_query_var('fvp_sitemap_monthnum') || get_query_var('feed') && get_query_var('feed') == 'video-sitemap-index' ) {
  			return false;
  		}
  		return $redirect;
  	}
    
    function settings_save( $settings ) {
      if( !empty($_POST['video_sitemap_meta']) ) {
        $settings['video_sitemap_meta'] = trim( preg_replace( '~[^A-Za-z0-9.:\-_\/,]~', '', $_POST['video_sitemap_meta']) );
      }
      return $settings;
    }
    
    function strip_membership_content( $content ) {
      $content = preg_replace( '~\[is_paid[\s\S]*?\[/is_paid\]~', '', $content );
      $content = preg_replace( '~\[restrict[\s\S]*?\[/restrict\]~', '', $content );
      $content = preg_replace( '~\[am4show[\s\S]*?\[/am4show\]~', '', $content );
      return $content;
    }
}

global $FV_Xml_Video_Sitemap;
$FV_Xml_Video_Sitemap = new FV_Xml_Video_Sitemap();