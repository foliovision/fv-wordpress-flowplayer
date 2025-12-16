<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Shortcode2Database_Conversion extends FV_Player_Conversion_Base {

  var $supported_atts;

  var $supported_player_atts;

  var $supported_video_atts;

  function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    parent::__construct( array(
      'title' => 'FV Player Shortcode2Database Conversion',
      'slug' => 'shortcode2db',
    ) );

    $this->conversion_limit = 1;

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
      'transcript',
      'original_formatting',
      'ab',
      'chapters',
      'controlbar',
      'speed'
    );

    $this->supported_atts = array_merge( $this->supported_video_atts, $this->supported_player_atts );

    $this->screen_fields = array(
      'ID',
      'Title',
      'Post Type',
      'Shortcode',
      'Result',
      'Error'
    );

    if( isset($_GET['fv-conversion-export']) && !empty($_GET['page']) && sanitize_key( $_GET['page'] ) === $this->screen ) {
      add_action('admin_init', array( $this, 'csv_export' ) );
    }
  }

  public function get_text( $type ) {
    if ( 'help' === $type ) {
      return __( "This converts the <code>[fvplayer src=...]</code> and <code>[flowplayer src=...]</code> shortcodes into database <code>[fvplayer id=...]</code> shortcodes.", 'fv-player' );

    } else if ( 'start_warning' === $type ) {
      return __( 'Please make sure you backup your database before continuing. You can use post revisions to get back to previous version of your posts as well.', 'fv-player' );
    }

    return parent::get_text( $type );
  }

  /**
   * Get posts with [fvplayer/flowplayer src...] shortcodes
   *
   * @return object|null $result
   */
  function get_items($offset, $limit) {
    global $wpdb;

    // Each row is the matching wp_posts row or wp_posts row with matching meta_value
    $results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT SQL_CALC_FOUND_ROWS  ID, post_author, post_date_gmt, post_status, post_title, post_type, post_content FROM {$wpdb->posts} AS p JOIN {$wpdb->postmeta} AS m ON p.ID = m.post_id WHERE post_status NOT IN ('inherit','trash') AND (post_content LIKE %s OR post_content LIKE %s) AND post_type NOT IN ('topic','reply') OR (meta_value LIKE %s OR meta_value LIKE %s) AND meta_key NOT LIKE %s AND meta_key NOT LIKE %s GROUP BY ID ORDER BY post_date_gmt DESC LIMIT %d, %d",
        '%' . $wpdb->esc_like( "[fvplayer src=" ) . '%',
        '%' . $wpdb->esc_like( "[flowplayer src=" ) . '%',
        '%' . $wpdb->esc_like( "[fvplayer src=" ) . '%',
        '%' . $wpdb->esc_like( "[flowplayer src=" ) . '%',
        '%' . $wpdb->esc_like( "_fv_player_%_backup_" ) . '%',
        '%' . $wpdb->esc_like( "_fv_player_%_failed_" ) . '%',
        $offset,
        $limit
      )
    );

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

      // match shortcodes in post_content, ignore shotcodes in double brackets like [[fvplayer src="https://your-website.com/videos/video.mp4"]]
      preg_match_all( '~(?<!\[)\[(?:flowplayer|fvplayer) .*?\](?!\[)~', $post->post_content, $matched_shortcodes );

      if( !empty($matched_shortcodes) && !empty($matched_shortcodes[0]) ) {
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

    // match shortcodes also in post meta
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

          preg_match_all( '~(?<!\[)\[(?:flowplayer|fvplayer) .*?\](?!\[)~', $meta_value, $matched_shortcodes );

          if( !empty($matched_shortcodes) && !empty($matched_shortcodes[0]) ) {
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
            <input type="button" class="button" value="<?php esc_attr_e('Convert FV Player shortcodes to DB', 'fv-player-pro'); ?>" style="margin-top: 2ex;" onclick="location.href='<?php echo admin_url('admin.php?page=' . $this->screen ) ?>'; "/>
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
    $errors = array();

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

          } else if(strcmp( $player_att, 'transcript' ) == 0) { // transcript text
            $transcript_text = $import_atts[$player_att];
            if( !empty($transcript_text) ) {
              $import_video_atts[$video_index]['meta'][] = array( 'meta_key' => 'transcript_text', 'meta_value' => $transcript_text );
            }

          } else if(strcmp( $player_att, 'original_formatting' ) == 0) { // transcript original formatting
            $transcript_original_formatting = $import_atts[$player_att];

            if( $transcript_original_formatting === 'true' ) {
              $import_video_atts[$video_index]['meta'][] = array( 'meta_key' => 'transcript_original_formatting', 'meta_value' => $transcript_original_formatting );
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
      $import_player_atts['date_created'] = strtotime($post->post_date_gmt) > 0 ? $post->post_date_gmt : current_time( 'mysql' );

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

  function build_output_html($data, $percent_done) {
    $html = array();

    foreach( $data as $output_data ) {
      $html[] = "<tr><td><a href='" . get_edit_post_link( $output_data['ID'] ) . "' target='_blank'> #". $output_data['ID'] . "</a></td><td><a href='" . get_permalink( $output_data['ID'] ) ."' target='_blank'>". $output_data['title'] . "</a></td><td>" . $output_data['type'] . "</td><td>". $output_data['shortcode'] . "</td><td>" . $output_data['output'] . "</td><td>" . $output_data['error'] . "</td></tr>";
    }

    if( empty($html) && $percent_done == 0 ) {
      $html[] = "<tr><td colspan='6'>No matching players found.</td></tr>";
    }

    return $html;
  }

  function iterate_data($data) {
    $conversions_output = array();
    $convert_error = false;

    foreach( $data AS $post ) {
      $result = $this->convert_one($post);
      // mark post if conversion failed
      if( !empty( $result['errors'] ) ) {
        update_post_meta( $post->ID, '_fv_player_' . $this->slug . '_failed', $result['errors'] );
        $convert_error = true;
      } else {
        if( $result['content_updated'] ) {
          // no problem, unmark
          delete_post_meta( $post->ID, '_fv_player_' . $this->slug . '_failed' );
        }
      }

      $conversions_output = array_merge( $conversions_output, $result['output_data'] );

      if( $this->is_live() && $result['content_updated'] ) {
        wp_update_post( array( 'ID' => $post->ID, 'post_content' => $result['new_content'] ) );
      }
    }

    return array(
      'convert_error' => $convert_error,
      'conversions_output' => $conversions_output
    );
  }

  function csv_export() {
    if( !current_user_can('install_plugins') ) return;

    global $wpdb;

    $filename = $this->slug . '-export-' . gmdate('Y-m-d') . '.csv';

    header('Content-type: text/csv');
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    $meta_key = '_fv_player_' . $this->slug . '_failed';

    $results = $wpdb->get_col( $wpdb->prepare( "SELECT {$wpdb->postmeta}.meta_value FROM {$wpdb->postmeta} JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->postmeta}.meta_key = %s ORDER BY {$wpdb->posts}.post_date_gmt DESC ", $meta_key ) );

    if( !empty( $results ) ) {
      $fp = fopen('php://output', 'wb');

      $header = array('ID','Title','Post-Link','Edit-Link','Shortcode','Message');

      fputcsv($fp, $header);

      foreach( $results as $result ) {
        $unserialized = unserialize( $result );

        foreach( $unserialized as $row ) {
          $row['post_link'] = htmlspecialchars_decode( $row['post_link'] );
          $row['post_edit'] = htmlspecialchars_decode( $row['post_edit'] );
          fputcsv($fp, $row);
        }
      }

      // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
      fclose($fp);
    }

    die();
  }

}

global $FV_Player_Shortcode2Database_Conversion;
$FV_Player_Shortcode2Database_Conversion = new FV_Player_Shortcode2Database_Conversion;
