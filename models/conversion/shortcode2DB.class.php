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

    // atts for video that are supported
    $this->supported_video_atts = array(
      'src',
      'src1',
      'src2',
      'splash',
    );

    // atts for player that are supported
    $this->supported_player_atts = array(
      'width',
      'height',
      'autoplay',
      'lightbox',
      'playlist',
      'caption',
      'subtitles',
      'ab',
      'chapters',
      'controlbar',
      'speed'
    );

    $this->supported_atts = array_merge( $this->supported_video_atts, $this->supported_player_atts );
  }

  /**
   * Count posts with old shortcode
   *
   * @return int $count
   */
  function get_count() {
    global $wpdb;
    return $wpdb->get_var( "SELECT FOUND_ROWS()" );
  }

  /**
   * Get posts with [fvplayer/flowplayer src...] shortcodes
   *
   * @return object|null $result
   */
  function get_posts_with_shortcode($offset, $limit) {
    global $wpdb;

    // Each row is the matching wp_posts row or wp_posts row with matching meta_value
    $results = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS  ID, post_author, post_date_gmt, post_status, post_title, post_type, post_content FROM {$wpdb->posts} AS p JOIN {$wpdb->postmeta} AS m ON p.ID = m.post_id WHERE post_status NOT IN ('inherit','trash') AND (post_content LIKE " . implode(' OR post_content LIKE ', $this->matchers) . ") AND post_type NOT IN ('topic','reply') OR (meta_value LIKE " . implode(' OR meta_value LIKE ',$this->matchers) . " ) AND meta_key NOT LIKE '%_fv_player_%_backup_%' ANd meta_key NOT LIKE '%_fv_player_%_failed' GROUP BY ID ORDER BY post_date_gmt DESC LIMIT {$offset},{$limit}");

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
    $content_updated = false; // track if content was updated
    $new_content = $post->post_content; // copy of content for update
    $output_data = array(); // output for html
    $errors = array(); // all errors for export

    if( !empty( $post->post_content) ) {

      // match shortcodes in post_content
      preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $post->post_content, $matched_shortcodes );

      if( !empty($matched_shortcodes) ) {
        foreach( $matched_shortcodes[0] as $shortcode ) {
          $result = $this->worker( $shortcode, $post, $new_content );
          if( $result ) {

            $new_content = $result['new_content'];
            if( $result['content_updated'] ) {
              $content_updated = $result['content_updated'];
            }
            $errors = array_merge( $errors, $result['errors'] );

            $output_data[] = $result['data'];
          }
        }
      }
    }

    $meta = get_post_custom( $post->ID );

    if( is_array($meta) ) {
      foreach( $meta AS $meta_key => $meta_values ) {
        // Skip meta keys created by the conversion
        if(
          preg_match( '~^_fv_player_.*?_backup_~', $meta_key ) ||
          preg_match( '~^_fv_player_.*?_failed$~', $meta_key )
        ) {
          continue;
        }

        foreach( $meta_values AS $meta_value ) {

          // Skip serialized meta values, it would be tricky to update
          if( is_serialized($meta_value) ) {
            continue;
          }

          $original_meta_value = $meta_value;

          $meta_updated = false;

          preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $meta_value, $matched_shortcodes );

          if( !empty($matched_shortcodes) ) {
            foreach( $matched_shortcodes[0] as $shortcode ) {
              $result = $this->worker( $shortcode, $post, $meta_value, $meta_key );
              if( $result ) {
                if( $result['content_updated'] ) {
                  $meta_updated = $result['content_updated'];
                  $meta_value = $result['new_content'];
                }
                $errors = array_merge( $errors, $result['errors'] );

                $output_data[] = $result['data'];
              }
            }
          }

          if( $this->is_live() && $meta_updated ) {
            update_post_meta( $post->ID, '_fv_player_'.$this->slug.'_backup_'.$meta_key, $original_meta_value );
            update_post_meta( $post->ID, $meta_key, $meta_value, $original_meta_value );
          }
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

  function worker( $shortcode, $post, $new_content, $meta_key = false ) {
    $content_updated = false;

    $atts = shortcode_parse_atts( trim(rtrim($shortcode,']')) );
    $import_video_atts = array();
    $import_player_atts = array();

    $import_atts = array();

    unset( $atts[0] ); // remove [fvplayer or [flowplayer

    // ignore db players
    if ( isset( $atts['id'] ) ) {
      return false;
    }

    // check for unsupported args
    $unsupported_atts_found = array();
    foreach( $atts as $k => $v ) {
      if( !in_array( $k, $this->supported_atts ) ) {
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
      foreach( $this->supported_video_atts as $video_att ) { 
        if( isset( $import_atts[$video_att] ) ) {
          $import_video_atts[$video_index][$video_att] = $import_atts[$video_att];
        }
      }

      // add player atts , parse lightbox, playlist items
      foreach( $this->supported_player_atts as $player_att ) {
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
          } else if(strcmp( $player_att, 'chapters' ) == 0) { // subtitles
            $chapters = explode(';', $import_atts[$player_att]);
            foreach( $chapters as $chapters_vtt ) {
              if( !empty( $chapters_vtt ) ) {
                if( !isset( $import_video_atts[$video_index]['meta'] ) ) {
                  $import_video_atts[$video_index]['meta'] = array();
                }
                $import_video_atts[$video_index]['meta'][] = array( 'meta_key' => 'chapters', 'meta_value' => esc_url_raw($chapters_vtt) );
              }

              $video_index++;
            }

          } else if(strcmp( $player_att, 'ab' ) == 0) { // subtitles
            if( $import_atts[$player_att] == 'true' ) {
              $import_player_atts[$player_att] = 'on';
            } else if( $import_atts[$player_att] == 'false' ) {
              $import_player_atts[$player_att] = 'off';
            }  else {
              $import_player_atts[$player_att] =  $import_atts[$player_att];
            }
          
          } else if(strcmp( $player_att, 'speed' ) == 0) { // subtitles
            if( $import_atts[$player_att] == 'buttons' ) {
              $import_player_atts[$player_att] = 'yes';
            } else {
              $import_player_atts[$player_att] =  $import_atts[$player_att];
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
          'meta_value' => get_class($this)
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

    $type = $post->post_type;
    if( $meta_key ) {
      $type .= '<br />meta_key: <code>'.$meta_key.'</code>';
    }

    return array(
      'errors' => $errors,
      'data' => array(
        'ID' => $post->ID,
        'title' => $post->post_title,
        'type' => $type,
        'shortcode' => $shortcode,
        'output' => $output_msg,
        'error' => $failed_msg
      ),
      'new_content' => $new_content,
      'content_updated' => $content_updated
    );
  }

}

new FV_Player_Shortcode2Database_Conversion;