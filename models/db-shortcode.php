<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
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

// class handling database shortcode generation and saving
class FV_Player_Db_Shortcode {

  public function __construct() {
    add_filter('fv_flowplayer_args_pre', array($this, 'getPlayerAttsFromDb'), 10, 1);
    add_filter('fv_player_item', array($this, 'setCurrentVideoAndPlayer' ), 1, 3 );

    add_action( 'wp_ajax_return_shortcode_db_data', array($this, 'return_shortcode_db_data') );
  }

  public function setCurrentVideoAndPlayer($aItem, $index, $aPlayer) {
    global $fv_fp;

    if (!empty($aPlayer['video_objects'][$index])) {
      $fv_fp->currentVideoObject = $aPlayer['video_objects'][$index];
    } else {
      $fv_fp->currentVideoObject = null;
      $fv_fp->currentPlayerObject = null;
    }

    return $aItem;
  }

  /**
   * Returns playlist video item formatted for a shortcode,
   * so it's in the form of "video-src, video-src1, video-src2, rtmp:some-path, splash:some-url"
   * and can be added to the playlist section of that shortcode.
   *
   * @param $vid The video object from which to prepare the string data.
   *
   * @return string Returns the string data for a playlist item.
   */
  private function getPlaylistItemData($vid) {
    $item = (!empty($vid['src']) ? $vid['src'] : '');

    if (!empty($vid['src1'])) {
      $item .= ',' . $vid['src1'];
    }

    if (!empty($vid['src2'])) {
      $item .= ',' . $vid['src2'];
    }

    if (!empty($vid['rtmp_path'])) {
      $item .= ',rtmp:' . $vid['rtmp_path'];
    }

    if (!empty($vid['splash'])) {
      $item .= ',' . $vid['splash'];
    }

    return $item;
  }



  /**
   * Returns caption formatted for a shortcode, so it can be used there.
   * Also, this function is used on 2 places, so that's why it's a function :P
   *
   * @param $vid The video object from which to prepare the string data.
   *
   * @return string Returns the string data for a captions item.
   */
  private function getCaptionData($vid) {
    return (!empty($vid['caption']) ? $vid['caption'] : '');
  }



  /**
   * Returns startend tag formatted for a shortcode, so it can be used there.
   * Also, this function is used on 2 places, so that's why it's a function :P
   *
   * @param $vid The video object from which to prepare the string data.
   *
   * @return string Returns the string data for a startend item.
   */
  private function getStartEndData($vid) {
    $str = (!empty($vid['start']) ? $vid['start'] : '');

    if ($str) {
      $str .= (!empty($vid['end']) ? '-' . $vid['end'] : '');
    }

    if (!$str) {
      $str = '-';
    }

    return $str;
  }


  /**
   * Generates a full code for a playlist from one that uses video IDs
   * stored in the database to one that conforms to the original long
   * playlist shortcode format (with multiple sources, rtmp, splashes etc.).
   *
   * @param array $atts Player attributes to build the actual playlist from.
   * @param array $preview_data Alternative data to use instead of the $atts array
   *                            when we want to show previews etc.
   *
   * @return array Returns augmented array of attributes that get picked up
   *               on the front-end side.
   * @throws Exception When any of the underlying classes throw an exception.
   */
  private function generateFullPlaylistCode($atts, $preview_data = null) {
    global $fv_fp;
    static $cache = array();

    // check if we should change anything in the playlist code
    if ($preview_data || (isset($atts['playlist']) && preg_match('/^[\d,]+$/m', $atts['playlist']))) {
      $new_playlist_tag = array();
      $new_caption_tag = array();
      $new_startend_tag = array();
      $first_video_data_cached = false;

      // serve what we can from the cache
      if (!$preview_data) {
        $ids    = explode( ',', $atts['playlist'] );
        $newids = array();

        // check the first video, which is the main one for the playlist
        if ( isset( $cache[ $ids[0] ] ) ) {
          $first_video_data_cached = true;
          $atts                    = array_merge( $atts, $cache[ $ids[0] ] );
        }

        // prepare cached data and IDs that still need loading from DB
        foreach ( $ids as $id ) {
          if ( isset( $cache[ $id ] ) ) {
            $new_playlist_tag[] = $this->getPlaylistItemData( $cache[ $id ] );
            $new_caption_tag[]  = $this->getCaptionData( $cache[ $id ] );
            $new_startend_tag[] = $this->getStartEndData( $cache[ $id ] );
          } else {
            $newids[] = (int) $id;
          }
        }
      }

      if ($preview_data || count($newids)) {
        if ($preview_data) {
          $videos = $preview_data['videos'];
        } else {
          $videos = $fv_fp->current_player()->getVideos();
        }

        // cache first vid
        if (!$first_video_data_cached) {
          $vid = $videos[0]->getAllDataValues();
          $atts = array_merge($atts, $vid);
          $atts['video_objects'] = array($videos[0]);

          // don't cache if we're previewing
          if (!$preview_data) {
            $cache[ $vid['id'] ] = $vid;
          }

          $caption = $this->getCaptionData($vid);
          if ($caption) {
            $new_caption_tag[] = $caption;
          }

          $startend = $this->getStartEndData($vid);
          if ($startend != '-') {
            $new_startend_tag[] = $startend;
          }

          // remove the first video and keep adding the rest of the videos to the playlist tag
          array_shift( $videos );
        }

        // add rest of the videos into the playlist tag
        if (count($videos)) {
          // if this remains false, the caption tag does not need to be present
          $has_captions = false;

          // if this remains false, the startend tag does not need to be present
          $has_timings = false;

          foreach ( $videos as $vid_object ) {
            $vid = $vid_object->getAllDataValues();
            $atts['video_objects'][] = $vid_object;
            $cache[ $vid['id'] ]  = $vid;
            $new_playlist_tag[] = $this->getPlaylistItemData( $vid );

            $caption = $this->getCaptionData($vid);
            if ($caption) {
              $has_captions = true;
            }
            $new_caption_tag[] = $caption;

            $startend = $this->getStartEndData($vid);
            if ($startend != '-') {
              $has_timings = true;
            }
            $new_startend_tag[] = $startend;
          }

          $atts['playlist'] = implode(';', $new_playlist_tag);

          if ($has_captions) {
            $atts['caption'] = implode( ';', $new_caption_tag );
          }

          if ($has_timings) {
            $atts['startend'] = implode( ';', $new_startend_tag );
          }
        } else {
          // only one video found, therefore this is not a playlist
          unset($atts['playlist']);

          $caption = $this->getCaptionData($vid);
          if ($caption) {
            $atts['caption'] = $caption;
          }

          $startend = $this->getStartEndData($vid);
          if ($startend != '-') {
            $atts['startend'] = $startend;
          }
        }
      } else {
        $atts['playlist'] = implode(';', $new_playlist_tag);

        if (count($new_caption_tag)) {
          $atts['caption'] = implode( ';', $new_caption_tag );
        }

        if (count($new_startend_tag)) {
          $atts['startend'] = implode( ';', $new_startend_tag );
        }
      }
    }

    return $atts;
  }


  /**
   * Maps attributes from database into their respective shortcode names.
   *
   * @param $att_name Attribute name from the database to map into shortcode format.
   *
   * @return mixed Returns the correct attribute name for shortcode use.
   */
  private function mapDbAttributes2Shortcode($att_name) {
    $atts_map = array(
      'playlist'       => 'liststyle',
      'video_ads'      => 'preroll',
      'video_ads_post' => 'postroll'
    );

    return (isset($atts_map[$att_name]) ? $atts_map[$att_name] : $att_name);
  }


  /**
   * Maps attributes values from database into their respective shortcode values.
   *
   * @param $att_name  Attribute name from the database.
   * @param $att_value Attribute value from the database.
   *
   * @return mixed Returns the correct attribute value for shortcode use.
   */
  private function mapDbAttributeValue2Shortcode($att_name, $att_value) {
    switch ($att_name) {
      case 'playlist_advance':
        return ($att_value == 'off' ? 'false' : 'true');
    }

    return $att_value;
  }


  /**
   * Retrieves player attributes from the database
   * as opposed to getting them from the old full-text
   * shortcode format.
   *
   * @param $id ID of the player to get attributes for.
   *
   * @return array|mixed Returns an array with all player attributes in it.
   *                     If the player ID is not found, an empty array is returned.
   * @throws Exception When the underlying video object throws.
   */
  public function getPlayerAttsFromDb($atts) {
    global $fv_fp, $FV_Db_Shortcode;
    static $cache = array();

    if (isset($atts['id'])) {
      // numeric ID means we're coming from a shortcode somewhere in a post
      if (is_numeric($atts['id'])) {
        if ( isset( $cache[ $atts['id'] ] ) ) {
          return $cache[ $atts['id'] ];
        }

        $player                     = new FV_Player_Db_Shortcode_Player( $atts['id'] );
        $player->getVideos();
        $fv_fp->currentPlayerObject = $player;

        $data = $player->getAllDataValues();

        // did we find the player?
        if ( $data ) {
          foreach ( $data AS $k => $v ) {
            $k = $this->mapDbAttributes2Shortcode( $k );
            $v = $this->mapDbAttributeValue2Shortcode( $k, $v );
            if ( $v ) {
              // we omit empty values and they will get set to defaults if necessary
              $atts[ $k ] = $v;
            }
          }

          // add playlist / single video data
          $atts = array_merge( $atts, $this->generateFullPlaylistCode(
          // we need to prepare the same attributes array here
          // as is ingested by generateFullPlaylistCode()
          // when parsing the new playlist code on the front-end
            array(
              'playlist' => $data['videos']
            )
          ) );

        }
      } else {
        // when ID is not numeric, it's most probably a preview that we need to build
        $atts = array_merge( $atts, $FV_Db_Shortcode->generateFullPlaylistCode(array(), $this->db_store_player_data($_POST)));
      }

      $cache[ $atts['id'] ] = $atts;

    }

    return $atts;
  }


  /**
   * Stored player data in a database from the POST data sent via AJAX
   * from the shortcode editor.
   *
   * @param array $data Alternative data to work with rather than getting these from $_POST.
   *                    Used when previews are being made.
   *
   * @return void|array Returns nothing when we're saving a new player into the DB,
   *                    otherwise returns a new unsaved player and video instances to be used as needed.
   * @throws Exception When any of the underlying objects throw.
   */
  public function db_store_player_data($data = null) {
    $player_options   = array();
    $video_ids        = array();
    $post_data        = (is_array($data) ? $data : (!empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : null));

    if ($post_data) {
      foreach ($post_data as $field_name => $field_value) {
        // global player or local video setting field
        if (strpos($field_name, 'fv_wp_flowplayer_field_') !== false) {
          $option_name = str_replace('fv_wp_flowplayer_field_', '', $field_name);
          // global player option
          $player_options[$option_name] = $field_value;
        } else if ($field_name == 'videos' && is_array($field_value)) {
          // iterate over all videos for the player
          foreach ($field_value as $video_index => $video_data) {
            // width and height are global options but are sent out for shortcode compatibility
            unset($video_data['fv_wp_flowplayer_field_width'], $video_data['fv_wp_flowplayer_field_height']);

            // strip video data of the prefix
            $new_video_data = array();
            foreach ($video_data as $key => $value) {
              $new_video_data[str_replace('fv_wp_flowplayer_field_', '', $key)] = $value;
            }
            $video_data = $new_video_data;
            unset($new_video_data);

            // add any video meta data that we can gather
            $video_meta = array();

            /***
             * SUBTITLES META DATA
             */
            if (isset($post_data['subtitles']) && isset($post_data['subtitles'][$video_index])) {
              // prepare all options for this video
              foreach ( $post_data['subtitles'][$video_index] as $subtitle_values ) {
                if ($subtitle_values['file']) {
                  $video_meta[] = array(
                    'meta_key' => 'subtitles' . ($subtitle_values['code'] ? '_'.$subtitle_values['code'] : ''),
                    'meta_value' => $subtitle_values['file']
                  );
                }
              }
            }

            // save the video
            $video = new FV_Player_Db_Shortcode_Player_Video(null, $video_data);

            // save only if we're not requesting new instances for preview purposes
            if (!$data) {
              $id_video = $video->save( $video_meta );
            } else {
              $video->link2meta( $video_meta );
            }

            // return videos as well as the full player
            if (!$data) {
              $video_ids[] = $id_video;
            } else {
              $video_ids[] = $video;
            }
          }
        } else {
          // TODO:
          // here should be all other fields from plugins etc. (i.e. fv_player_field_ppv_price for PPV...)
        }
      }

      // add all videos into this player
      if (!$data) {
        $player_options['videos'] = implode( ',', $video_ids );
      }

      // create and save the player
      $player = new FV_Player_Db_Shortcode_Player(null, $player_options);

      // save only if we're not requesting new instances for preview purposes
      if (!$data) {
        $id = $player->save();

        if ($id) {
          echo $id;
        } else {
          echo -1;
        }
      } else {
        return array(
          'player' => $player,
          'videos' => $video_ids
        );
      }
    }

    if (!$data) {
      die();
    }
  }



  /**
   * AJAX method to return database data for the player ID given
   */
  public function return_shortcode_db_data() {
    global $fv_fp;

    if (isset($_POST['playerID']) && is_numeric($_POST['playerID']) && intval($_POST['playerID']) == $_POST['playerID']) {
      $out = array();

      // load player and its videos from DB
      $this->getPlayerAttsFromDb(array( 'id' => $_POST['playerID'] ));

      // fill the $out variable with player data
      $out = array_merge($out, $fv_fp->current_player()->getAllDataValues());
      unset($out['video_objects'], $out['videos']);

      // fill the $out variable with video data
      $out['videos'] = array();
      foreach ($fv_fp->current_player()->getVideos() as $video) {
        // load video values
        $vid = $video->getAllDataValues();
        $vid['meta'] = array();

        // load all meta data
        $meta = $video->getMetaData();
        foreach ($meta as $meta_object) {
          if ($meta_object->getIsValid()) {
            $vid['meta'][] = $meta_object->getAllDataValues();
          }
        }

        $out['videos'][] = $vid;
      }

      header('Content-Type: application/json');
      echo json_encode($out, true);
    }

    wp_die();
  }

}
