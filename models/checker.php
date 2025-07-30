<?php
/*  FV Player - HTML5 video player
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class FV_Player_Checker {


  var $is_cron = false;


  function __construct() {
    add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
    add_action( 'fv_flowplayer_checker_event', array( $this, 'checker_cron' ) );
    add_action( 'init', array( $this, 'cron_init' ) );
  }



  public static function check_headers( $headers, $remotefilename, $random, $args = false ) {
    $args = wp_parse_args( $args, array( 'talk_bad_mime' => 'Video served with a bad mime type' , 'wrap'=>'p' ) );

    $sOutput = '';

    $video_errors = array();

    $bFatal = false;
    if( $headers && $headers['response']['code'] == '404' ) {
      $video_errors[] = 'File not found (HTTP 404)!';
      $bFatal = true;
    } else if( $headers && $headers['response']['code'] == '403' ) {
      $video_errors[] = 'Access to video forbidden (HTTP 403)!';
      $bFatal = true;
    } else if( $headers && $headers['response']['code'] != '200' && $headers['response']['code'] != '206' ) {
      $video_errors[] = 'Can\'t check the video (HTTP '.$headers['response']['code'].')!';
      $bFatal = true;
    } else {

      if(
        ( !isset($headers['headers']['accept-ranges']) || $headers['headers']['accept-ranges'] != 'bytes' ) &&
        !isset($headers['headers']['content-range'])
      ) {
        $video_errors[] = 'Server does not support HTTP range requests! Please check <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq#getting-error-about-range-requests">our FAQ</a>.';
      }

      if(
        ( stripos( $remotefilename, '.mp4' ) !== FALSE && $headers['headers']['content-type'] != 'video/mp4' ) ||
        ( stripos( $remotefilename, '.m4v' ) !== FALSE && $headers['headers']['content-type'] != 'video/x-m4v' ) ||
        ( stripos( $remotefilename, '.webm' ) !== FALSE && $headers['headers']['content-type'] != 'video/webm' ) ||
        ( stripos( $remotefilename, '.mov' ) !== FALSE && $headers['headers']['content-type'] != 'video/mp4' )
      ) {
        if( stripos( $remotefilename, '.mov' ) !== FALSE ) {
          $meta_note_addition = ' Firefox on Windows does not like MOV files with video/quicktime mime type.';
        } else if( stripos( $remotefilename, '.webm' ) !== FALSE ) {
          $meta_note_addition = ' Older Firefox versions don\'t like WEBM files with mime type other than video/webm.';
        } else {
          $meta_note_addition = ' Some web browsers may experience playback issues in HTML5 mode (Internet Explorer 9 - 10).';
          /*if( $fv_fp->conf['engine'] == 'default' ) {
            $meta_note_addition .= ' Currently you are using the "Default (mixed)" <a href="'.site_url().'/wp-admin/admin.php?page=fvplayer">Preferred Flowplayer engine</a> setting, so IE will always use Flash and will play fine.';
          }*/
        }

        $fix = '<div class="fix-meta-'.$random.'" style="display: none; ">
          <p>If the video is hosted on Amazon S3:</p>
          <blockquote>Using your Amazon AWS Management Console, you can go though your videos and find file content type under the "Metadata" tab in an object\'s "Properties" pane and fix it to "video/mp4" for MP4, "video/x-m4v" for M4V files, "video/mp4" for MOV files and "video/webm" for WEBM files.</blockquote>
          <p>If the video is hosted on your server, put this into your .htaccess:</p>
          <pre>AddType video/mp4             .mp4
    AddType video/webm            .webm
    AddType video/ogg             .ogv
    AddType application/x-mpegurl .m3u8
    AddType video/x-m4v           .m4v
    AddType video/mp4             .mov
    # hls transport stream segments:
    AddType video/mp2t            .ts</pre>
          <p>If you are using Microsoft IIS, you need to use the IIS manager. Check our <a href="http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer/faq#video-doesnt-play-internet-explorer" target="_blank">FAQ</a> for more info.</p>
        </div>';

        $sOutput = ( $args['wrap'] ) ? '<'.$args['wrap'].'>' : '';
        $sOutput .= '<strong>Bad mime type</strong>: '.$args['talk_bad_mime'].' <tt>'.$headers['headers']['content-type'].'</tt>!'.$meta_note_addition.' (<a href="#" onclick="jQuery(\'.fix-meta-'.$random.'\').toggle(); return false">show fix</a>)';
        $sOutput .= ( $args['wrap'] ) ? '</'.$args['wrap'].'>' : '';
        $sOutput .= $fix;
        $video_errors[] = $sOutput;
      }
    }

    return array( $video_errors, (isset($headers['headers']['content-type'])) ? $headers['headers']['content-type'] : '', $bFatal );
  }




  public function check_mimetype( $URLs = false, $meta = array(), $force_is_cron = false ) {

    // If we create new player in FV Player Coconut in coconut-ajax.php, there will be no wp_remote_get function or WP_Http class, so we cannot check the video.
    if ( ! function_exists( 'wp_remote_get' ) || ! class_exists( 'WP_Http' ) ) {
      return false;
    }

    $error = false;
    $tStart = microtime(true);

    global $fv_wp_flowplayer_ver, $fv_fp;

    if( !empty($meta) ) {
      extract( $meta, EXTR_SKIP );
    }

    if( defined('DOING_AJAX') && DOING_AJAX && isset( $_POST['media'] ) && stripos( sanitize_url( $_SERVER['HTTP_REFERER'] ), home_url() ) === 0 ) {
      $URLs = json_decode( stripslashes( trim( wp_strip_all_tags( $_POST['media'] ) ) ) );
    }

    if( isset($URLs) ) {
      $all_sources = $URLs;

      foreach( $all_sources AS $source ) {
        if( preg_match( '~^https?://~', $source, $match ) ) {
          $media = $source;
          break;
        }
      }

      $random = (isset($_POST['hash'])) ? trim( wp_strip_all_tags( sanitize_text_field( $_POST['hash'] ) ) ) : false;
      if( isset($media) ) {
        $remotefilename = $media;
        $remotefilename_encoded = flowplayer::get_encoded_url($remotefilename);

        $bValidFile = true;

        if ( ! class_exists( 'getID3' ) ) {
          require( ABSPATH . WPINC . '/ID3/getid3.php' );
        }
        $getID3 = new getID3;

        $upload_dir = wp_upload_dir();
        $localtempfilename = trailingslashit( $upload_dir['basedir'] ).'fv_flowlayer_tmp_'.md5(wp_rand(1,999)).'_'.basename( substr($remotefilename_encoded,0,32) );

        global $wp_filesystem;

        if ( is_null( $wp_filesystem ) ) {
          require_once ABSPATH . '/wp-admin/includes/file.php';
          WP_Filesystem();
        }

        $real_file_size = 0;
        $analysis_size  = 8 * 1024 * 1024;

        $remotefilename_encoded = $fv_fp->get_video_src( $remotefilename_encoded, array( 'dynamic' => true ) );

        $res = wp_remote_get( $remotefilename_encoded, array(
          'headers'    => array(
            'range'    => 'bytes=0-' . $analysis_size,
            'referer'  => home_url()
          ),
          'timeout'    => !$this->is_cron && !$force_is_cron ? apply_filters( 'fv_flowplayer_checker_timeout_quick', 2 ) : 20,
          'user-agent' => 'FV Player video checker/' . $fv_wp_flowplayer_ver
        ) );

        if ( ! is_wp_error( $res ) ) {

          // Get the real file size out of Content-length: 0-8388608/{full file size here}
          if ( $content_range = wp_remote_retrieve_header( $res, 'content-range' ) ) {
            $content_range_parts = explode( '/', $content_range );
            if ( 2 === count( $content_range_parts ) ) {
              $real_file_size = intval( $content_range_parts[1] );
            }
          }

          $wp_filesystem->put_contents( $localtempfilename, wp_remote_retrieve_body( $res ) );

          if( !empty($res['response']['code']) ) {
            $code = intval($res['response']['code']);
            if( $code == 404 ) {
              $error = 'Video not found';

            } else if( $code == 403 ) {
              $error = 'Access denied';

            } else if( $code > 399 ) {
              $error = 'HTTP '.$code;
              if( !empty($res['response']['message']) ) {
                $error .= ': '.$res['response']['message'];
              }
            }
          }

          list( $aVideoErrors, $sContentType, $bFatal ) = $this->check_headers( $res, $remotefilename, $random );
          if( $bFatal ) {
            $bValidFile = false;
          }

          if( $bValidFile ) {
            $ThisFileInfo = $getID3->analyze( $localtempfilename );
          }

          $wp_filesystem->delete( $localtempfilename );

        } else {
          $bValidFile = false;
        }

        /*
        Only check file length
        */

        if( (isset($meta_action) && $meta_action == 'check_time') || $force_is_cron ) {
          $time = false;
          $width = false;
          $height = false;

          if( isset($ThisFileInfo) && isset($ThisFileInfo['playtime_seconds']) ) {
            $time = $ThisFileInfo['playtime_seconds'];

            /**
             * MP3 duration check is not reliable if we only check a part of file.
             * So here we multiply the duration by the ratio of the real file size to the analysis size,
             * if it's bigger than the analysis size.
             */
            if ( stripos( $remotefilename_encoded, '.mp3' ) !== false ) {
              if ( $real_file_size > $analysis_size ) {
                $time = $time * $real_file_size / $analysis_size;
              }
            }
          }
          if( !empty($ThisFileInfo['video']['resolution_x']) ) {
            $width = intval($ThisFileInfo['video']['resolution_x']);
          }
          if( !empty($ThisFileInfo['video']['resolution_y']) ) {
            $height = intval($ThisFileInfo['video']['resolution_y']);
          }

          $is_live = false;
          $is_encrypted = false;

          if(preg_match('/.m3u8(\?.*)?$/i', $remotefilename_encoded)){
            $remotefilename_encoded = apply_filters( 'fv_flowplayer_video_src', $remotefilename_encoded , array('dynamic'=>true) );
            $request = wp_remote_get(
              $remotefilename_encoded,
              array(
                'headers' => array(
                  'referer' => home_url(),
                ),
                'timeout' => 15,
                'user-agent' => 'FV Player video checker/' . $fv_wp_flowplayer_ver
              )
            );

            $response_code = wp_remote_retrieve_response_code( $request );
            if( $response_code == 404 ) {
              return array( 'error' => 'Video not found' );

            } else if( $response_code == 403 ) {
              return array( 'error' => 'Access denied' );

            } else if( is_wp_error($request) ) {
              return array( 'error' => $request->get_error_message() );
            }

            $response = wp_remote_retrieve_body( $request );

            $playlist = false;
            $duration = 0;
            $segments = false;

            if(preg_match_all('/^#EXTINF:([0-9]+\.?[0-9]*)/im', $response,$segments)){
              $is_live = stripos( $response, '#EXT-X-ENDLIST' ) === false;

              foreach($segments[1] as $segment_item){
                $duration += $segment_item;
              }
            } else {
              $lines = explode( "\n", $response );

              $streams = array();
              $had_ext_x_stream_inf = false;
              $resoluton_x_max = 0;
              $resoluton_y_max = 0;

              foreach( $lines AS $line ) {
                // last line was starting with #EXT-X-STREAM-INF:
                if( stripos($line,'#') !== 0 && $had_ext_x_stream_inf ) {
                  $streams[] = trim($line);
                  $had_ext_x_stream_inf = false;
                }

                if( stripos($line,'#EXT-X-STREAM-INF:') === 0 ) {
                  $had_ext_x_stream_inf = true;

                  if( stripos($line,'RESOLUTION=') !== false ) {
                    if( preg_match( '~RESOLUTION=(\d+)x(\d+)~', $line, $resoluton ) ) {
                      if( $resoluton[1] > $resoluton_x_max ) {
                        $resoluton_x_max = $resoluton[1];
                      }
                      if( $resoluton[2] > $resoluton_y_max ) {
                        $resoluton_y_max = $resoluton[2];
                      }
                    }
                  }
                }

              }

              $width = $resoluton_x_max;
              $height = $resoluton_y_max;

              foreach($streams as $item){
                $item_url = $item;

                // If the stream URL is relative, we need to take the master playlist URL, remove the filename part and put in the stream link
                if( stripos( $item_url, 'http://' ) !== 0 && stripos( $item_url, 'https://' ) !== 0 ) {
                  $item_url = preg_replace('/[^\/]*\.m3u8(\?.*)?/i', $item, $remotefilename_encoded);
                }
                if( $secured_url = $fv_fp->get_video_src( $item_url, array( 'dynamic' => true ) ) ) {
                  $item_url = $secured_url;
                }

                $request = wp_remote_get(
                  $item_url,
                  array(
                    'headers' => array(
                      'referer' => home_url()
                    ),
                    'user-agent' => 'FV Player video checker/' . $fv_wp_flowplayer_ver
                  )
                );

                if( is_wp_error($request) ) {
                  return array( 'error' => $request->get_error_message() );
                }

                $playlist_item = wp_remote_retrieve_body( $request );

                if(preg_match_all('/^#EXTINF:([0-9]+\.?[0-9]*)/im', $playlist_item,$segments)){
                  $is_live = stripos( $playlist_item, '#EXT-X-ENDLIST' ) === false;
                  $is_encrypted = stripos( $playlist_item, '#EXT-X-KEY' ) !== false;

                  foreach($segments[1] as $segment_item){
                    $duration += $segment_item;
                  }
                }
                if($duration > 0)
                  break;
              }
            }

            $time = $duration;
          }

          $time = apply_filters( 'fv_flowplayer_checker_time', $time, $remotefilename_encoded );
          $key = flowplayer::get_video_key($remotefilename_encoded);

          global $post;
          $fv_flowplayer_meta = array();
          if( !empty($post) ) {
            $fv_flowplayer_meta = get_post_meta( $post->ID, $key, true );
            if( !$fv_flowplayer_meta ) $fv_flowplayer_meta = array();
          }

          $fv_flowplayer_meta['error'] = $error;
          $fv_flowplayer_meta['duration'] = $time;
          $fv_flowplayer_meta['width'] = $width;
          $fv_flowplayer_meta['height'] = $height;
          $fv_flowplayer_meta['is_live'] = $is_live;
          $fv_flowplayer_meta['is_encrypted'] = $is_encrypted;
          $fv_flowplayer_meta['etag'] = ! is_wp_error( $res ) && isset($res['headers']['etag']) ? $res['headers']['etag'] : false;  //  todo: check!
          $fv_flowplayer_meta['date'] = time();
          $fv_flowplayer_meta['check_time'] = microtime(true) - $tStart;

          if( $time > 0 || $error || $this->is_cron ) {
            if( !empty($post) ) {
              update_post_meta( $post->ID, $key, $fv_flowplayer_meta );
            }
            return $fv_flowplayer_meta;
          }

        }

      }	//	end isset($media)
    }
  }




  function checker_cron() {
    global $fv_fp;
    if( $fv_fp->_get_option('video_model_db_checked') && $fv_fp->_get_option('video_meta_model_db_checked') ) {

      // get all video IDs for which the duration is zero and are not live streams and were not checked in last day
      global $wpdb;
      $aVideos = $wpdb->get_col( "SELECT id FROM `{$wpdb->prefix}fv_player_videos` WHERE duration = 0 AND live = 0 AND DATE_SUB( UTC_TIMESTAMP(), INTERVAL 1 DAY ) > last_check ORDER BY id DESC" );

      if( $aVideos ) {
        global $FV_Player_Db;
        foreach( $aVideos AS $video_id ) {
          $objVideo = new FV_Player_Db_Video( $video_id, array(), $FV_Player_Db );

          $last_check = $objVideo->getLastCheck();

          $check_ttl = 86400;

          $is_live = $objVideo->getLive();
          if( $is_live && FV_Player_YouTube()->is_youtube( $objVideo->getSrc() ) ) {
            $check_ttl = 300;
          }

          if( $last_check && intval($last_check) + $check_ttl > time() ) {
            continue;
          }

          $error_count = $objVideo->getMetaValue('error_count',true);

          if( $error_count && intval($error_count) > 5 ) {
            continue;
          }

          // Make sure we do no loose video meta!
          $objVideo->save( array(), true );

        }
      }
    }

    // legacy
    if( !$aQueue = self::queue_get() ) return;
    $tStart = microtime(true);
    $this->is_cron = true;
    foreach( $aQueue AS $key => $item ) {
      if( microtime(true) - $tStart > apply_filters( 'fv_flowplayer_checker_cron_time', 20 ) ) {
        break;
      }
      global $post;
      $tmp = $post;
      $post = get_post($key);

      do_action( 'fv_flowplayer_checker_cron_post', $key );

      fv_wp_flowplayer_save_post($key);
      $post = $tmp;
    }

  }




  function cron_init() {
      if ( !wp_next_scheduled( 'fv_flowplayer_checker_event' ) ) {
        wp_schedule_event( time(), '5minutes', 'fv_flowplayer_checker_event' );
    }
  }




  function cron_schedules( $schedules ) {
    $schedules['5minutes'] = array(
      'interval' => 300,
      'display' => __('Every 5 minutes')
    );
    return $schedules;
  }




  public static function get_videos( $post_id ) {
    global $fv_fp;

    $objPost = get_post($post_id);
    if( $objPost ) {
      $content = $objPost->post_content;
      preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $content, $matches );

      $aMeta = get_post_custom($post_id);
      if( $aMeta && is_array($aMeta) && count($aMeta) > 0) {
        $meta_values = '';
        foreach( $aMeta AS $values ) {
          $meta_values .= implode("", $values);
        }
        if( preg_match_all( '~\[(?:flowplayer|fvplayer).*?\]~', $meta_values, $meta_matches ) ) {
          $matches[0] = array_merge($matches[0], $meta_matches[0]);
        }
      }

    }

    $videos = array();
    if( isset($matches[0]) && count($matches[0]) ) {
      $aPlaylistItems = array();
      foreach( $matches[0] AS $shortcode ) {
        $aArgs = shortcode_parse_atts( rtrim($shortcode,']') );
        list( $playlist_items_external_html, $aPlaylistItems ) = $fv_fp->build_playlist( $aArgs, isset($aArgs['src']) ? $aArgs['src'] : false, false, false, false, false, true );

        if( count($aPlaylistItems) > 0 ) {
          foreach( $aPlaylistItems AS $aItem ) {
            if( isset($aItem['sources']) && isset($aItem['sources'][0]) && isset($aItem['sources'][0]['src']) ) {
              $videos[] = $aItem['sources'][0]['src'];
            }
          }
        }
      }
    }

    if( count($videos) > 0 ) {
      $videos = array_unique($videos);
    } else {
      $videos = false;
    }
    return $videos;
  }




  public static function queue_add( $post_id ) {
    $aQueue = get_option( 'fv_flowplayer_checker_queue' ) ? get_option( 'fv_flowplayer_checker_queue' ) : array();
    $aQueue[$post_id] = true;
    update_option( 'fv_flowplayer_checker_queue', $aQueue );
  }




  public static function queue_check( $post_id = false ) {
    global $post;
    $post_id = ( isset($post->ID) ) ? $post->ID : $post_id;
    $aQueue = get_option( 'fv_flowplayer_checker_queue' ) ? get_option( 'fv_flowplayer_checker_queue' ) : array();
    if( in_array($post_id,array_keys($aQueue)) ) {
      return true;
    }
    return false;
  }




  public static function queue_get() {
    return get_option( 'fv_flowplayer_checker_queue', array() );
  }




  public static function queue_remove( $post_id ) {
    $aQueue = get_option( 'fv_flowplayer_checker_queue' ) ? get_option( 'fv_flowplayer_checker_queue' ) : array();
    if( isset($aQueue[$post_id]) ) {
      unset($aQueue[$post_id]);
    }
    update_option( 'fv_flowplayer_checker_queue', $aQueue );
  }




}
