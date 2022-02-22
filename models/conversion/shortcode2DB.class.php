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
      'help' => __("This converts the <code>[fvplayer src=...]</code> and <code>[flowplayer src=...]</code> shortcodes into database <code>[fvplayer id=...]</code> shortcodes.", 'fv-wordpress-flowplayer')
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

      // atts for video that are supported
      $supported_video_atts = array(
        'src',
        'src1',
        'src2',
        'splash',
      );

      // atts for player that are supported
      $supported_player_atts = array(
        'width',
        'height',
        'autoplay',
        'lightbox',
        'playlist',
        'caption',
        'subtitles'
      );

      $supported_atts = array_merge( $supported_video_atts, $supported_player_atts );

      if( !empty($matched_shortcodes) ) {
        foreach( $matched_shortcodes[0] as $shortcode ) {
          $atts = shortcode_parse_atts( trim(rtrim($shortcode,']')) );
          $import_video_atts = array();
          $import_player_atts = array();

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
            // single video set 0 index
            $video_index = 0;
            $import_video_atts[$video_index] = array();
            // add video atts
            foreach( $supported_video_atts as $video_att ) { 
              if( isset( $import_atts[$video_att] ) ) {
                $import_video_atts[$video_index][$video_att] = $import_atts[$video_att];
              }
            }

            // add player atts , parse lightbox, playlist items
            foreach( $supported_player_atts as $player_att ) {
              $video_index = 0; // reset video index to 0
              if( isset( $import_atts[$player_att] ) ) {
                if( strcmp( $player_att, 'lightbox' ) == 0 ) { // handle lightbox
                  $lightbox_atts = explode(';', $import_atts[$player_att] );

                  $lightbox_count = count( $lightbox_atts );

                  if( $lightbox_count == 2 ) { // lightbox_caption
                    $import_player_atts['lightbox_caption'] = $lightbox_atts[1];
                  } else if( $lightbox_count == 3 ) { // lightbox_width, lightbox_height
                    $import_player_atts['lightbox_width'] = $lightbox_atts[1];
                    $import_player_atts['lightbox_height'] = $lightbox_atts[2];
                  } else if( $lightbox_count == 4 ) { // lightbox_caption, lightbox_width, lightbox_height
                    $import_player_atts['lightbox_width'] = $lightbox_atts[1];
                    $import_player_atts['lightbox_height'] = $lightbox_atts[2];
                    $import_player_atts['lightbox_caption'] = $lightbox_atts[3];
                  }

                  $import_player_atts[$player_att] = $lightbox_atts[0]; // lightbox
                } else if( strcmp( $player_att, 'playlist' ) == 0 ) { // handle playlist
                  $playlist_items = explode(';', $import_atts[$player_att]);
                  foreach($playlist_items as $item) {
                    $video_index++;
                    $import_video_atts[$video_index] = array();

                    $item_data = explode(',', $item );

                    // parse src, scr1, scr2 and splash
                    if( count( $item_data ) > 1 ) {
                      foreach(  $item_data as $i => $data ) {
                        if( preg_match('~\.(png|gif|jpg|jpe|jpeg)($|\?)~', $data) || stripos($data, 'i.vimeocdn.com') !== false ) { // splash
                          $import_video_atts[$video_index]['splash'] = $data;
                        } else if( $i > 0 ) { // src1, src2
                          $import_video_atts[$video_index]['src'.$i] = $data;
                        }
                      }
                    }

                    $import_video_atts[$video_index]['src'] = $item_data[0]; // src
                  }
                } else if(strcmp( $player_att, 'subtitles' ) == 0) { // subtitles
                  $subtitles = explode(';', $import_atts[$player_att]);
                  foreach( $subtitles as $subtile ) {
                    if( !empty( $subtile ) ) {
                      if( !isset( $import_video_atts[$video_index]['meta'] ) ) {
                        $import_video_atts[$video_index]['meta'] = array();
                      }
                      $import_video_atts[$video_index]['meta'][] = array( 'meta_key' => 'subtitles', 'meta_value' => esc_url_raw($subtile) );
                    }

                    $video_index++;
                  }

                } else if(strcmp( $player_att, 'caption' ) == 0) { // caption
                  $replace_from = array('&amp;','\;', '\,'); // escaped characters
                  $replace_to = array('<!--amp-->','<!--semicolon-->','<!--comma-->'); // temp replacements
                  $unescaped = array( '&', ';', ',' );
                  $captions = str_replace( $replace_from, $replace_to, $import_atts[$player_att] );
                  $captions = explode(';', $captions);

                  foreach( $captions as $caption ) {
                    $caption = str_replace( $replace_to, $unescaped, $caption );
                    $import_video_atts[$video_index]['caption'] = $caption;

                    $video_index++;
                  }
                } else { // other atts
                  $import_player_atts[$player_att] =  $import_atts[$player_att];
                }
              }
            }

            // add metadata
            $import_player_atts['meta'] = array( 
              array(
                'meta_key' => 'post_id',
                'meta_value' => $post->ID
              ),
              array(
                'meta_key' => 'fv_player_conversion',
                'meta_value' => self::class
              )
            );

            // add author
            $import_player_atts['author'] = $post->post_author;

            // add status
            $import_player_atts['status'] = 'published';

            // add date_created
            $import_player_atts['date_created'] = $post->post_date_gmt;

            // add player_name
            // $import_player_atts['player_name'] = 'player_name';

            // add videos
            $import_player_atts['videos'] = $import_video_atts;

            // echo '<pre>';
            // var_export($import_player_atts);
            // echo '</pre>';
            // die();

            if( $this->is_live() ) {
              global $FV_Player_Db;
              $player_id =  $FV_Player_Db->import_player_data(false, false, $import_player_atts);

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
              
            } else {
              $output_msg = "Would create new FV Player";
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
            <input type="button" class="button" value="<?php _e('Convert FV Player shortcodes to DB', 'fv-player-pro'); ?>" style="margin-top: 2ex;" onclick="location.href='<?php echo admin_url('admin.php?page=' . $this->screen ) ?>'; "/>
          </p>
        </td>
      </tr>
    <?php
  }

}

new FV_Player_Shortcode2Database_Conversion;