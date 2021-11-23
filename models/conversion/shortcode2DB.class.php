<?php

class FV_Player_Shortcode2Database_Conversion extends FV_Player_Conversion_Base {

  function __construct() {
    parent::__construct( array(
      'title' => 'FV Player Shortcode2Database Conversion',
      'slug' => 'shortcode2db',
      'matchers' => array(
        "'%[fvplayer src=%'",
        "'%[flowplayer src=%'",
      )
    ) );
  }

  /**
   * Count posts with old shortcode
   *
   * @return int $count
   */
  function get_count() {
    global $wpdb;

    $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status != 'inherit' AND (post_content LIKE " . implode(' OR post_content LIKE ',$this->matchers) . ")" );

    return intval($count);
  }

  /**
   * Get posts with [fvplayer/flowplayer src...] shortcodes
   *
   * @return object|null $result
   */
  function get_posts_with_shortcode($offset, $limit) {
    global $wpdb;

    $results = $wpdb->get_results( "SELECT ID, post_date_gmt ,post_title, post_type, post_content FROM {$wpdb->posts} WHERE post_status != 'inherit' AND (post_content LIKE " . implode(' OR post_content LIKE ', $this->matchers) . ") ORDER BY ID DESC LIMIT {$offset},{$limit}");

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

    $new_content = $post->post_content;
    $output_data = array();
    $errors = array();

    if( !empty( $post->post_content) ) {

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
      );

      if( !empty( $matched_shortcodes) ) {
        foreach( $matched_shortcodes[0] as $shortcode ) {
          $atts = shortcode_parse_atts( rtrim($shortcode,']') );

          unset( $atts[0] ); // remove [fvplayer or [flowplayer

          // ignore db players
          if ( isset( $atts['id'] )) {
            continue;
          }

          // check for unsupported args
          $unsupported_atts_found = array();
          foreach( $atts as $k => $v ) {
            if( !in_array( $k, $supported_atts ) ) {
              $unsupported_atts_found[] = $k;
            }
          }

          $output = "Conversion failed.";

          // check if unsupported args found
          if( !empty( $unsupported_atts_found) ) {
            $output = "Unsupported argument(s) " . implode(',', $unsupported_atts_found);
            
            $errors[] = array( 
              'message' => $output,
              'post_edit' => get_edit_post_link( $post->ID ),
              'post_link' => get_permalink( $post->ID )
            );
          } else {
            // only splash, caption and src, src1, src2
            $import = array(
              // 'player_name' => $post->post_title,
              'date_created' => $post->post_date_gmt,
              'width' => isset($atts['width']) ? $atts['width'] : '',
              'height' => isset($atts['height']) ? $atts['height'] : '',
              'autoplay' => isset($atts['autoplay']) ? $atts['autoplay'] : '',
              'videos' => array(
                array(
                  'src' => isset($atts['src']) ? $atts['src'] : '',
                  'src1' => isset($atts['src1']) ? $atts['src1'] : '',
                  'src2' => isset($atts['src2']) ? $atts['src2'] : '',
                  'splash' => isset($atts['splash']) ? $atts['splash'] : '',
                  'caption' => isset($atts['caption']) ? $atts['caption'] : ''
                ),
                'meta' => array(
                  array(
                    'meta_key' => 'post_id',
                    'meta_value' => $post->ID
                  ),
                )
              ),
            );

            global $FV_Player_Db;
            $player_id =  $FV_Player_Db->import_player_data(false, false, $import);

            if( $player_id > 0 ) {
              // echo "Inserted player #".$player_id."\n";
              $new_content = str_replace( $shortcode , '[fvplayer id="'.$player_id.'"]', $new_content );
              $output = "New FV Player #" . $player_id ;
            } else {
              $output = "Error saving FV Player instance";
              
              $errors[] = array(
                'message' => $output,
                'post_edit' => get_edit_post_link( $post->ID ),
                'post_link' => get_permalink( $post->ID )
              );
            }
          }

          $output_data[] = array(
            'timing' => number_format(microtime(true) - $start),
            'ID' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'shortcode' => $shortcode,
            'output' => $output,
          );
        }
      }
    }

    return  array(
      'new_content' => $new_content,
      'output_data' => $output_data,
      'errors' => $errors
    );
  }

  function conversion_button() {
    ?>
      <tr>
        <td>
          <td class="first"><label>Convert fvplayer to DB:</label></td>
        </td>
        <td>
          <p class="description">
            <input type="button" class="button" value="<?php _e('Convert FV Player shortcodes to DB', 'fv-player-pro'); ?>" style="margin-top: 2ex;" onclick="if( confirm('<?php _e('This converts the [fvplayer src=...] and [flowplayer src=...] shortcodes into database [fvplayer id=...] shortcodes.\n\n Please make sure you backup your database before continuing. You can use revisions to get back to previos versions of your posts as well.', 'fv-player-pro'); ?>') )location.href='<?php echo admin_url('admin.php?page=' . $this->screen ) ?>'; "/>
          </p>
        </td>
      </tr>
    <?php
  }

}

new FV_Player_Shortcode2Database_Conversion;