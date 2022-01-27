<?php

class FV_Player_Shortcode2Database_Conversion extends FV_Player_Conversion_Base {

  function __construct() {
    parent::__construct( array(
      'title' => 'FV Player Shortcode2Database Conversion',
      'slug' => 'shortcode2db',
      'matchers' => array(
        "'%[fvplayer src=%'",
        "'%[flowplayer src=%'",
      ),
      'help' => __("This converts the <code>[fvplayer src=...]</code> and <code>[flowplayer src=...]</code> shortcodes into database <code>[fvplayer id=...]</code> shortcodes.\n\nPlease make sure you backup your database before continuing. You can use revisions to get back to previos versions of your posts as well.", 'fv-wordpress-flowplayer')
    ) );
  }

  /**
   * Count posts with old shortcode
   *
   * @return int $count
   */
  function get_count() {
    global $wpdb;

    $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status NOT IN ('inherit','trash') AND (post_content LIKE " . implode(' OR post_content LIKE ',$this->matchers) . ") AND post_type NOT IN ('topic','reply')" );

    return intval($count);
  }

  /**
   * Get posts with [fvplayer/flowplayer src...] shortcodes
   *
   * @return object|null $result
   */
  function get_posts_with_shortcode($offset, $limit) {
    global $wpdb;

    $results = $wpdb->get_results( "SELECT ID, post_author, post_date_gmt, post_status, post_title, post_type, post_content FROM {$wpdb->posts} WHERE post_status NOT IN ('inherit','trash') AND (post_content LIKE " . implode(' OR post_content LIKE ', $this->matchers) . ") AND post_type NOT IN ('topic','reply') ORDER BY post_date_gmt DESC LIMIT {$offset},{$limit}");

    return $results;
  }

  /**
   * Converts all shortcodes to DB
   *
   * @param WP_Post $post
   *
   * @return arrray
   */
  function convert_one( $post ) {
    $start = microtime(true);

    $content_updated = false; // track if content was updated
    $new_content = $post->post_content; // copy of content for update
    $output_data = array(); // output for html
    $errors = array(); // all errors for export

    if( !empty( $post->post_content) ) {

      // match shortcodes in post_content
      preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $post->post_content, $matched_shortcodes );

      $supported_atts = array(
        'src',
        'src1',
        'src2',
        'splash',
        'caption',
        'width',
        'height',
        'autoplay',
        'splashend',
        'lightbox '
      );

      if( !empty( $matched_shortcodes) ) {
        foreach( $matched_shortcodes[0] as $shortcode ) {
          $atts = shortcode_parse_atts( trim(rtrim($shortcode,']')) );
          $import_atts = array();

          unset( $atts[0] ); // remove [fvplayer or [flowplayer

          // ignore db players
          if ( isset( $atts['id'] ) ) {
            continue;
          }

          // check for unsupported args
          $unsupported_atts_found = array();
          foreach( $atts as $k => $v ) {
            if( !in_array( $k, $supported_atts ) ) {
              $unsupported_atts_found[] = $k;
            } else {
              $import_atts[$k] = $v;
            }
          }

          $output_msg = "Conversion failed.";
          $failed_msg = "";

          // check if unsupported args found
          if( !empty($unsupported_atts_found) ) {
            $failed_msg = "Unsupported argument(s) " . implode(',', $unsupported_atts_found);
            
            $errors[] = array(
              'ID' => $post->ID,
              'post_title' => $post->post_title,
              'post_link' => get_permalink( $post->ID ),
              'post_edit' => get_edit_post_link( $post->ID ),
              'shortcode' => $shortcode,
              'message' => $failed_msg
            );
          } else {
            // $import_atts['meta'] = array(
            //   array(
            //     'meta_key' => '',
            //     'meta_value' => ''
            //   )
            // );

            // only splash, caption and src, src1, src2
            $import = array(
              // 'player_name' => $post->post_title,
              'meta' => array(
                array(
                  'meta_key' => 'post_id',
                  'meta_value' => $post->ID
                ),
                array(
                  'meta_key' => 'fv_player_conversion',
                  'meta_value' => self::class
                )
              ),
              'author' => $post->post_author,
              'status' => 'published',
              'date_created' => $post->post_date_gmt,
              'width' => isset($atts['width']) ? $atts['width'] : '',
              'height' => isset($atts['height']) ? $atts['height'] : '',
              'autoplay' => isset($atts['autoplay']) ? $atts['autoplay'] : '',
              'videos' => array(
                $import_atts
              )
            );

            global $FV_Player_Db;
            $player_id =  $FV_Player_Db->import_player_data(false, false, $import);

            if( $player_id > 0 ) {
              // echo "Inserted player #".$player_id."\n";
              $new_content = str_replace( $shortcode , '[fvplayer id="'.$player_id.'"]', $new_content );
              $content_updated = true;
              $output_msg = "New FV Player #" . $player_id ;
            } else {
              $failed_msg = "Error saving FV Player instance";

              $errors[] = array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_link' => get_permalink( $post->ID ),
                'post_edit' => get_edit_post_link( $post->ID ),
                'shortcode' => $shortcode,
                'message' => $failed_msg
              );
            }
          }

          $output_data[] = array(
            'timing' => number_format(microtime(true) - $start),
            'ID' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'shortcode' => $shortcode,
            'output' => $output_msg,
            'error' => $failed_msg
          );
        }
      }
    }

    return array(
      'new_content' => $new_content,
      'content_updated' => $content_updated,
      'output_data' => $output_data,
      'errors' => $errors
    );
  }

  function conversion_button() {
    ?>
      <tr>
        <td><label>Convert <code>[fvplayer src="..."]</code> shortocdes to database-driven <code>[fvplayer id="..."]</code> :</label></td>
        <td>
          <p class="description">
            <input type="button" class="button" value="<?php _e('Convert FV Player shortcodes to DB', 'fv-player-pro'); ?>" style="margin-top: 2ex;" onclick="if( confirm('<?php _e('Please make sure you backup your database before continuing. You can use revisions to get back to previos versions of your posts as well.', 'fv-wordpress-flowplayer') ?>') )location.href='<?php echo admin_url('admin.php?page=' . $this->screen ) ?>'; "/>
          </p>
        </td>
      </tr>
    <?php
  }

}

new FV_Player_Shortcode2Database_Conversion;