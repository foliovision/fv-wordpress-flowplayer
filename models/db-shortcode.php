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

  private
    $edit_lock_timeout_seconds = 300,
    $videos_cache = array(),
    $video_atts_cache = array(),
    $video_meta_cache = array(),
    $players_cache = array(),
    $player_atts_cache = array(),
    $player_meta_cache = array();

  public function __construct() {
    add_filter('fv_flowplayer_args_pre', array($this, 'getPlayerAttsFromDb'), 5, 1);
    add_filter('fv_player_item', array($this, 'setCurrentVideoAndPlayer' ), 1, 3 );
    add_action('wp_head', array($this, 'cache_players_and_videos' ));

    add_action( 'wp_ajax_return_shortcode_db_data', array($this, 'return_shortcode_db_data') );
    add_action( 'wp_ajax_fv_wp_flowplayer_export_player_data', array($this, 'export_player_data') );
    add_action( 'wp_ajax_fv_wp_flowplayer_import_player_data', array($this, 'import_player_data') );
    add_action( 'wp_ajax_fv_wp_flowplayer_retrieve_video_data', array($this, 'retrieve_video_data') );
  }

  public function getVideosCache() {
    return $this->videos_cache;
  }

  public function setVideosCache($cache) {
    return $this->videos_cache = $cache;
  }

  public function isVideoCached($id) {
    return isset($this->videos_cache[$id]);
  }

  public function getVideoMetaCache() {
    return $this->video_meta_cache;
  }

  public function setVideoMetaCache($cache) {
    return $this->video_meta_cache = $cache;
  }

  public function isVideoMetaCached($id_video, $id_meta = null) {
    return ($id_meta !== null ? isset($this->video_meta_cache[$id_video][$id_meta]) : isset($this->video_meta_cache[$id_video]));
  }

  public function getPlayersCache() {
    return $this->players_cache;
  }

  public function setPlayersCache($cache) {
    return $this->players_cache = $cache;
  }

  public function isPlayerCached($id) {
    return isset($this->players_cache[$id]);
  }

  public function getPlayerMetaCache() {
    return $this->player_meta_cache;
  }

  public function setPlayerMetaCache($cache) {
    return $this->player_meta_cache = $cache;
  }

  public function isPlayerMetaCached($id_player, $id_meta = null) {
    return ($id_meta !== null ? isset($this->player_meta_cache[$id_player][$id_meta]) : isset($this->player_meta_cache[$id_player]));
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

  public function cache_players_and_videos() {
    global $posts;
    if( !empty($posts) && is_array($posts) ) {
      $player_ids = array();
      foreach( $posts AS $post ) {
        if (isset($post->post_content)) {
          preg_match_all( '/\[fvplayer id="(\d+)"[^\]]*\]/m', $post->post_content, $matches, PREG_SET_ORDER, 0 );
          if ( $matches && count( $matches ) ) {
            foreach ( $matches as $match ) {
              $player_ids[] = $match[1];
            }
          }
        }
      }

      if (count($player_ids)) {
        // load all players at once
        new FV_Player_Db_Shortcode_Player( $player_ids, array(), $this );

        // load all player meta
        new FV_Player_Db_Shortcode_Player_Player_Meta( null, array( 'id_player' => $player_ids ), $this );

        // pre load all videos and their meta for these players
        $video_ids = array();

        foreach ( $this->players_cache as $player ) {
          $video_ids = array_merge( $video_ids, explode( ',', $player->getVideoIds() ) );
        }

        if ( count( $video_ids ) ) {
          new FV_Player_Db_Shortcode_Player_Video( $video_ids, array(), $this );
          new FV_Player_Db_Shortcode_Player_Video_Meta( null, array( 'id_video' => $video_ids ), $this );
        }
      }
    }
  }

  /**
   * Retrieves data for all players table shown in admin.
   *
   * @param $order_by  If set, data will be ordered by this column.
   * @param $order     If set, data will be ordered in this order.
   * @param $offset    If set, data will returned will be limited, starting at this offset.
   * @param $per_page  If set, data will returned will be limited, ending at this offset.
   * @param $single_id If set, data will be restricted to a single player ID.
   * @param $search    If set, results will be searched for using the GET search parameter.
   *
   * @return array     Returns an array of all list page results to be displayed.
   * @throws Exception When the underlying FV_Player_Db_Shortcode_Player_Video class generates an error.
   */
  public static function getListPageData($order_by, $order, $offset, $per_page, $single_id = null, $search = null) {
    global $FV_Db_Shortcode; // this is an instance of this same class, but since we're in static context, we need to access this globally like that... sorry :P

    // load single player, as requested by the user
    if ($single_id) {
      new FV_Player_Db_Shortcode_Player( $single_id, array(), $FV_Db_Shortcode );
    } else {
      // load all players, which will put them into the cache automatically
      new FV_Player_Db_Shortcode_Player( null, array(
        'db_options' => array(
          'select_fields' => 'id, player_name, videos',
          'order_by'      => $order_by,
          'order'         => $order,
          'offset'        => $offset,
          'per_page'      => $per_page
        )
      ), $FV_Db_Shortcode );
    }

    $players = $FV_Db_Shortcode->getPlayersCache();

    // get all video IDs used in all players
    if ($players && count($players)) {
      $videos = array();
      $result = array();

      foreach ($players as $player) {
        /* @var FV_Player_Db_Shortcode_Player $player */
        $videos = array_merge($videos, explode(',', $player->getVideoIds()));
      }

      // load all videos data at once
      if (count($videos)) {
        $vids_data = new FV_Player_Db_Shortcode_Player_Video( $videos, array(
          'db_options' => array(
            'select_fields' => 'caption, src, splash'
          )
        ), $FV_Db_Shortcode );

        // reset $videos variable and index all of our video data,
        // so they are easily accessible when building the resulting
        // display data
        if ($vids_data) {
          /* @var FV_Player_Db_Shortcode_Player_Video[] $videos */
          $videos = array();
          if (count($FV_Db_Shortcode->getVideosCache())) {
            foreach ( $FV_Db_Shortcode->getVideosCache() as $video_object ) {
              $videos[ $video_object->getId() ] = $video_object;
            }
          }
        }

        // build the result
        foreach ($players as $player) {
          // player data first
          $result_row = new stdClass();
          $result_row->id = $player->getId();
          $result_row->name = $player->getPlayerName();
          $result_row->thumbs = array();

          // no player name, we'll assemble it from video captions and/or sources
          if (!$result_row->name) {
            $result_row->name = array();
          }

          foreach (explode(',', $player->getVideoIds()) as $video_id) {
            // assemble video name, if there's no player name
            if (is_array($result_row->name) && isset($videos[ $video_id ])) {
              if ( $videos[ $video_id ]->getCaption() ) {
                // use caption
                $result_row->name[] = $videos[ $video_id ]->getCaption();
              } else {
                // use source
                $arr = explode('/', $videos[ $video_id ]->getSrc());
                $arr = end($arr);

                // update YouTube and other video names
                $vid_replacements = array(
                  'watch?v=' => 'YouTube: '
                );

                $arr = str_replace(array_keys($vid_replacements), array_values($vid_replacements), $arr);

                $result_row->name[] = $arr;
              }
            }

            // if we have a splash, add that in
            if (isset($videos[ $video_id ]) && $videos[ $video_id ]->getSplash()) {
              $result_row->thumbs[] = '<img src="'.$videos[ $video_id ]->getSplash().'" width="100" />';
            }
          }

          // join name items, if present
          if (is_array($result_row->name)) {
            $result_row->name = join(', ', $result_row->name);
          }

          // join thumbnails
          $result_row->thumbs = join(' ', $result_row->thumbs);

          $result[] = $result_row;
        }

        return $result;
      }
    }

    return array();
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
        if ( isset( $this->video_atts_cache[ $ids[0] ] ) ) {
          $first_video_data_cached = true;
          $atts                    = array_merge( $atts, $this->video_atts_cache[ $ids[0] ] );
        }

        // prepare cached data and IDs that still need loading from DB
        foreach ( $ids as $id ) {
          if ( isset( $this->video_atts_cache[ $id ] ) ) {
            $new_playlist_tag[] = $this->getPlaylistItemData( $this->video_atts_cache[ $id ] );
            $new_caption_tag[]  = $this->getCaptionData( $this->video_atts_cache[ $id ] );
            $new_startend_tag[] = $this->getStartEndData( $this->video_atts_cache[ $id ] );
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
        if (!$first_video_data_cached && $videos) {
          $vid = $videos[0]->getAllDataValues();
          $atts = array_merge($atts, $vid);
          $atts['video_objects'] = array($videos[0]);

          // don't cache if we're previewing
          if (!$preview_data) {
            $this->video_atts_cache[ $vid['id'] ] = $vid;
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
        if ($videos && count($videos)) {
          // if this remains false, the caption tag does not need to be present
          $has_captions = false;

          // if this remains false, the startend tag does not need to be present
          $has_timings = false;

          foreach ( $videos as $vid_object ) {
            $vid                              = $vid_object->getAllDataValues();
            $atts['video_objects'][]          = $vid_object;
            $this->video_atts_cache[ $vid['id'] ] = $vid;
            $new_playlist_tag[]               = $this->getPlaylistItemData( $vid );

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

    if (isset($atts['id'])) {
      // numeric ID means we're coming from a shortcode somewhere in a post
      if (is_numeric($atts['id'])) {
        if ( isset( $this->player_atts_cache[ $atts['id'] ] ) ) {
          return $this->player_atts_cache[ $atts['id'] ];
        }

        if ($this->isPlayerCached($atts['id'])) {
          $player = $this->getPlayersCache();
          $player = $player[$atts['id']];
        } else {
          $player = new FV_Player_Db_Shortcode_Player( $atts['id'], array(), $FV_Db_Shortcode );
        }

        if (!$player || !$player->getIsValid()) {
          return false;
        }

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

      $this->player_atts_cache[ $atts['id'] ] = $atts;
    } else {
      $fv_fp->currentPlayerObject = null;
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
    global $FV_Db_Shortcode;

    $player_options        = array();
    $video_ids             = array();
    $post_data             = (is_array($data) ? $data : (!empty($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : null));
    $ignored_player_fields = array(
      'fv_wp_flowplayer_field_subtitles_lang', // subtitles languages is a per-video value with global field name,
                                               // so the player should ignore it, as it will be added via video meta
      'fv_wp_flowplayer_field_popup', // never used, never shown in the UI, possibly a remnant of old code,
      'fv_wp_flowplayer_field_transcript', // transcript is a meta value, so it should not be stored globally per-player anymore
      'fv_wp_flowplayer_field_chapters', // chapters is a meta value, so it should not be stored globally per-player anymore
    );

    if ($post_data) {
      // parse and resolve deleted videos
      if (!empty($post_data['deleted_videos'])) {
        $deleted_videos = explode(',', $post_data['deleted_videos']);
        foreach ($deleted_videos as $d_id) {
          // we don't need to load this video data, just link it to a database
          // and then delete it
          // ... although we'll need at least 1 item in the data array to consider this
          //     video data valid for object creation
          $d_vid = new FV_Player_Db_Shortcode_Player_Video(null, array('caption' => '1'), $this);
          $d_vid->link2db($d_id);
          $d_vid->delete();
        }
      }

      // parse and resolve deleted meta data
      if (!empty($post_data['deleted_video_meta'])) {
        $deleted_meta = explode(',', $post_data['deleted_video_meta']);
        foreach ($deleted_meta as $d_id) {
          // we don't need to load this meta data, just link it to a database
          // and then delete it
          // ... although we'll need at least 1 item in the data array to consider this
          //     meta data valid for object creation
          $d_meta = new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('meta_key' => '1'), $this);
          $d_meta->link2db($d_id);
          $d_meta->delete();
        }
      }

      // parse and resolve deleted meta data
      if (!empty($post_data['deleted_player_meta'])) {
        $deleted_meta = explode(',', $post_data['deleted_player_meta']);
        foreach ($deleted_meta as $d_id) {
          // we don't need to load this meta data, just link it to a database
          // and then delete it
          // ... although we'll need at least 1 item in the data array to consider this
          //     meta data valid for object creation
          $d_meta = new FV_Player_Db_Shortcode_Player_Player_Meta(null, array('meta_key' => '1'), $this->DB_Shortcode_Instance);
          $d_meta->link2db($d_id);
          $d_meta->delete();
        }
      }

      foreach ($post_data as $field_name => $field_value) {
        // global player or local video setting field
        if (strpos($field_name, 'fv_wp_flowplayer_field_') !== false) {
          if (!in_array($field_name, $ignored_player_fields)) {
            $option_name = str_replace( 'fv_wp_flowplayer_field_', '', $field_name );
            // global player option
            $player_options[ $option_name ] = $field_value;
          }
        } else if ($field_name == 'videos' && is_array($field_value)) {
          // iterate over all videos for the player
          foreach ($field_value as $video_index => $video_data) {
            // width and height are global options but are sent out for shortcode compatibility
            unset($video_data['fv_wp_flowplayer_field_width'], $video_data['fv_wp_flowplayer_field_height']);

            // remove global player HLS key option, as it's handled as meta data item
            unset($video_data['fv_wp_flowplayer_hlskey'], $video_data['fv_wp_flowplayer_hlskey_cryptic']);

            // strip video data of the prefix
            $new_video_data = array();
            foreach ($video_data as $key => $value) {
              if ($key === 'id') {
                $id = $value;
              } else {
                $new_video_data[ str_replace( 'fv_wp_flowplayer_field_', '', $key ) ] = $value;
              }
            }
            $video_data = $new_video_data;
            unset($new_video_data);

            // add any video meta data that we can gather
            $video_meta = array();

            if (!empty($post_data['video_meta']['video'][$video_index])) {
              foreach ($post_data['video_meta']['video'][$video_index] as $video_meta_section => $video_meta_array) {
                $meta_data_to_add = array(
                  'meta_key' => $video_meta_section,
                  'meta_value' => $video_meta_array['value']
                );

                if (isset($video_meta_array['id'])) {
                  $meta_data_to_add['id'] = (int) $video_meta_array['id'];
                }

                $video_meta[] = $meta_data_to_add;
              }
            }

            // add chapters
            if (!empty($post_data['video_meta']['chapters'][$video_index]['file']['value'])) {
              $chapters = array(
                'meta_key' =>'chapters',
                'meta_value' => $post_data['video_meta']['chapters'][$video_index]['file']['value']
              );

              if (!empty($post_data['video_meta']['chapters'][$video_index]['file']['id'])) {
                $chapters['id'] = $post_data['video_meta']['chapters'][$video_index]['file']['id'];
              }

              $video_meta[] = $chapters;
            }

            // add transcript
            if (!empty($post_data['video_meta']['transcript'][$video_index]['file']['value'])) {
              $transcript = array(
                'meta_key' =>'transcript',
                'meta_value' => $post_data['video_meta']['transcript'][$video_index]['file']['value']
              );

              if (!empty($post_data['video_meta']['transcript'][$video_index]['file']['id'])) {
                $transcript['id'] = $post_data['video_meta']['transcript'][$video_index]['file']['id'];
              }

              $video_meta[] = $transcript;
            }

            // call a filter which is server by plugins to augment
            // the $video_meta data with all the plugin data for this
            // particular video
            if (!empty($post_data['video_meta'])) {
              $video_meta = apply_filters( 'fv_player_db_video_meta_save', $video_meta, $post_data['video_meta'], $video_index);
            }

            // save the video
            $video = new FV_Player_Db_Shortcode_Player_Video(null, $video_data, $this);

            // if we have video ID, link this video to DB
            if (isset($id)) {
              $video->link2db($id);
              unset($id);
            }

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
        }
      }

      // add all videos into this player
      if (!$data) {
        $player_options['videos'] = implode( ',', $video_ids );
      }

      // add any player meta data that we can gather
      $player_meta = array();

      if (!empty($post_data['player_meta']['player'])) {
        foreach ($post_data['player_meta']['player'] as $player_meta_section => $player_meta_array) {
          $meta_data_to_add = array(
            'meta_key' => $player_meta_section,
            'meta_value' => $player_meta_array['value']
          );

          if (isset($player_meta_array['id'])) {
            $meta_data_to_add['id'] = (int) $player_meta_array['id'];
          }

          $player_meta[] = $meta_data_to_add;
        }
      }

      // call a filter which is served by plugins to augment
      // the $player_meta data with all the plugin data for this
      // particular player
      if (!empty($post_data['player_meta'])) {
        $player_meta = apply_filters( 'fv_player_db_player_meta_save', $player_meta, $post_data['player_meta']);
      }

      // create and save the player
      $player = new FV_Player_Db_Shortcode_Player(null, $player_options, $FV_Db_Shortcode);

      // save only if we're not requesting new instances for preview purposes
      if (!$data) {
        // link to DB, if we're doing an update
        if (!empty($post_data['update'])) {
          $player->link2db($post_data['update']);
        }

        $id = $player->save($player_meta);

        if ($id) {
          // delete edit lock meta key, if found
          $meta = $player->getMetaData();

          if (count($meta)) {
            foreach ($meta as $meta_object) {
              if ( strstr($meta_object->getMetaKey(), 'edit_lock_') !== false ) {
                $meta_object->delete();
                break;
              }
            }
          }

          echo $id;
        } else {
          echo -1;
        }
      } else {
        $player->link2meta( $player_meta );
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
      if (!$this->getPlayerAttsFromDb(array( 'id' => $_POST['playerID'] ))) {
        header("HTTP/1.0 404 Not Found");
        die();
      }

      // check player's meta data for an edit lock
      $userID = get_current_user_id();
      if ($fv_fp->current_player() && count($fv_fp->current_player()->getMetaData())) {
        foreach ($fv_fp->current_player()->getMetaData() as $meta_object) {
          if ( strstr($meta_object->getMetaKey(), 'edit_lock_') !== false ) {
            if (str_replace('edit_lock_', '', $meta_object->getMetaKey()) != $userID) {
              // someone else is editing this video, first check the timestamp
              $last_tick = $meta_object->getMetaValue();
              if (time() - $last_tick > $this->edit_lock_timeout_seconds) {
                // timeout, remove lock, add lock for this user
                $meta_object->delete();

                $meta = new FV_Player_Db_Shortcode_Player_Player_Meta(null, array(
                  'id_player' => $fv_fp->current_player()->getId(),
                  'meta_key' => 'edit_lock_'.$userID,
                  'meta_value' => time()
                ), $this);

                $meta->save();
              } else {
                header( 'HTTP/1.0 403 Forbidden' );
                die();
              }
            } else {
              // same user, extend the lock
              $meta_object->setMetaValue(time());
              $meta_object->save();
            }
          }
        }
      } else {
        // add player edit lock if none was found
        if ($fv_fp->current_player()) {
          $meta = new FV_Player_Db_Shortcode_Player_Player_Meta( null, array(
            'id_player'  => $fv_fp->current_player()->getId(),
            'meta_key'   => 'edit_lock_' . $userID,
            'meta_value' => time()
          ), $this );

          $meta->save();
        }
      }

      // fill the $out variable with player data
      $out = array_merge($out, $fv_fp->current_player()->getAllDataValues());

      // load player meta data
      $meta = $fv_fp->current_player()->getMetaData();
      foreach ($meta as $meta_object) {
        if (!isset($out['meta'])) {
          $out['meta'] = array();
        }

        $out['meta'][] = $meta_object->getAllDataValues();
      }

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
          $vid['meta'][] = $meta_object->getAllDataValues();
        }

        $out['videos'][] = $vid;
      }

      header('Content-Type: application/json');
      echo json_encode($out, true);
    }

    wp_die();
  }

  /**
   * Receive Heartbeat data and checks for DB edit lock.
   * In case the lock is found and valid, it will be extended.
   *
   * @param array $response Heartbeat response data to pass back to front end.
   * @param array $data Data received from the front end (unslashed).
   *
   * @return array Returns the same response as received, as we don't need to update it or read it anywhere in JS.
   * @throws Exception When the underlying meta object throws an exception.
   */
  function check_db_edit_lock( $response, $data ) {
    global $FV_Db_Shortcode;

    $userID = get_current_user_id();

    // extend an existing lock
    if ( !empty( $data['fv_flowplayer_edit_lock_id'] ) ) {
      if ($FV_Db_Shortcode && $FV_Db_Shortcode->isPlayerCached($data['fv_flowplayer_edit_lock_id'])) {
        $player = $FV_Db_Shortcode->getPlayersCache();
        $player = $player[$data['fv_flowplayer_edit_lock_id']];
      } else {
        $player = new FV_Player_Db_Shortcode_Player($data['fv_flowplayer_edit_lock_id'], array(), $FV_Db_Shortcode);
      }

      if ($player->getIsValid()) {
        if (count($player->getMetaData())) {
          foreach ($player->getMetaData() as $meta_object) {
            if ( strstr($meta_object->getMetaKey(), 'edit_lock_') !== false ) {
              if (str_replace('edit_lock_', '', $meta_object->getMetaKey()) == $userID) {
                // same user, extend the lock
                $meta_object->setMetaValue(time());
                $meta_object->save();
              }
            }
          }
        }
      }
    }

    // remove locks that are no longer being edited
    if ( !empty( $data['fv_flowplayer_edit_lock_removal'] ) && count($data['fv_flowplayer_edit_lock_removal']) ) {
      // load meta for all players to remove locks for (and to auto-cache them as well)
      new FV_Player_Db_Shortcode_Player_Player_Meta(null, array('id_player' => array_keys($data['fv_flowplayer_edit_lock_removal'])), $this);
      $meta = $this->getPlayerMetaCache();

      if (count($meta)) {
        foreach ( $meta as $player ) {
          foreach ($player as $meta_object) {
            if ( strstr( $meta_object->getMetaKey(), 'edit_lock_' ) !== false ) {
              if ( str_replace( 'edit_lock_', '', $meta_object->getMetaKey() ) == $userID ) {
                // correct user, delete the lock
                $meta_object->delete();
              }
            }
          }
        }

        $response['fv_flowplayer_edit_locks_removed'] = 1;
      }
    }

    return $response;
  }

  /**
   * AJAX function to return JSON-formatted export data
   * for a specific player ID.
   *
   * Works for single player only right now!
   *
   * @throws Exception Thrown if one of the underlying DB classes throws an exception.
   */
  public function export_player_data() {
    if (isset($_POST['playerID']) && is_numeric($_POST['playerID']) && intval($_POST['playerID']) == $_POST['playerID']) {
      // first, load the player
      $player = new FV_Player_Db_Shortcode_Player($_POST['playerID'], array(), $this);
      if ($player && $player->getIsValid()) {
        $export_data = $player->export();

        // load player meta data
        $meta = $player->getMetaData();
        if ($meta && count($meta)) {
          $export_data['meta'] = array();

          foreach ($meta as $meta_data) {
            // don't include edit locks
            if ( strstr($meta_data->getMetaKey(), 'edit_lock_') === false ) {
              $export_data['meta'][] = $meta_data->export();
            }
          }
        }

        // load videos and meta for this player
        $videos = $player->getVideos();

        // this line will load and cache meta for all videos at once
        new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => explode(',', $player->getVideoIds())), $this);

        if ($videos && count($videos)) {
          $export_data['videos'] = array();

          foreach ($videos as $video) {
            $video_export_data = $video->export();

            // load all meta data for this video
            if ($this->isVideoMetaCached($video->getId())) {
              $video_export_data['meta'] = array();

              foreach ($this->video_meta_cache[$video->getId()] as $meta) {
                $video_export_data['meta'][] = $meta->export();
              }
            }

            $export_data['videos'][] = $video_export_data;
          }
        }
      } else {
        die('invalid player ID, export unsuccessful - please use the close button and try again');
      }

      echo json_encode($export_data, JSON_UNESCAPED_SLASHES);
      exit;
    } else {
      die('invalid player ID, export unsuccessful - please use the close button and try again');
    }
  }

  /**
   * AJAX function to import JSON-formatted export data.
   *
   * Works for single player only right now!
   *
   * @throws Exception Thrown if one of the underlying DB classes throws an exception.
   */
  public function import_player_data() {
    global $FV_Db_Shortcode;

    if (isset($_POST['data']) && $data = json_decode(stripslashes($_POST['data']), true)) {
      try {
        // first, create the player
        $player_keys = $data;
        unset($player_keys['meta'], $player_keys['videos']);

        $player = new FV_Player_Db_Shortcode_Player(null, $player_keys, $FV_Db_Shortcode);
        $player_video_ids = array();

        // create player videos, along with meta data
        // ... don't save the player yet, as we need all video IDs to be known
        //     before doing so
        if (isset($data['videos'])) {
          foreach ($data['videos'] as $video_data) {
            $video_object = new FV_Player_Db_Shortcode_Player_Video(null, $video_data, $FV_Db_Shortcode);
            $id_video = $video_object->save();

            // add all meta data for this video
            if (isset($video_data['meta'])) {
              foreach ($video_data['meta'] as $video_meta_data) {
                $video_meta_object = new FV_Player_Db_Shortcode_Player_Video_Meta(null, $video_meta_data, $FV_Db_Shortcode);
                $video_meta_object->link2db($id_video, true);
                $video_meta_object->save();
              }
            }

            $player_video_ids[] = $id_video;
          }
        }

        // set video IDs for the player
        $player->setVideos(implode(',', $player_video_ids));

        // save player
        $id_player = $player->save();

        // create player meta, if any
        if (isset($data['meta'])) {
          foreach ($data['meta'] as $meta_data) {
            $meta_object = new FV_Player_Db_Shortcode_Player_Player_Meta(null, $meta_data, $FV_Db_Shortcode);
            $meta_object->link2db($id_player, true);
            $meta_object->save();
          }
        }

      } catch (Exception $e) {
        if (WP_DEBUG) {
          var_dump($e);
        }

        die('0');
      }

      die((string) $id_player);
    } else {
      die('no valid import data found, import unsuccessful');
    }
  }

  /**
   * AJAX method to retrieve video caption, splash screen and duration.
   * Also returns current timestamp, so we can store the last check date in DB.
   */
  public function retrieve_video_data() {
    if (!isset($_POST['video_url'])) {
      exit;
    }

    $json_data = apply_filters('fv_player_meta_data', $_POST['video_url'], false);
    if ($json_data !== false) {
      header('Content-Type: application/json');
      $json_data['ts'] = time();
      die(json_encode($json_data));
    }

    // add last update timestamp & duration
    $json_data = array(
      'ts' => time()
    );

    // add duration
    global $FV_Player_Checker;
    $json_data['duration'] = $FV_Player_Checker->check_mimetype(array($_POST['video_url']), false, true);
    $json_data['duration'] = $json_data['duration']['duration'];

    header('Content-Type: application/json');
    die(json_encode($json_data));
  }

}
