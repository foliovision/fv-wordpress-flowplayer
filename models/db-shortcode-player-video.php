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

// video instance with options that's stored in a DB
class FV_Player_Db_Shortcode_Player_Video {

  private
    $id, // automatic ID for the video
    $is_valid = true, // used when loading the video from DB to determine whether we've found it
    $caption, // optional video caption
    $chapters, // URL for a VTT file for displaying captions
    $end,
    $mobile, // mobile (smaller-sized) version of this video
    $rtmp, // optional RTMP server URL
    $rtmp_path, // if RTMP is set, this will have the path on the server to the RTMP stream
    $splash, // URL to the splash screen picture
    $splash_text, // an optional splash screen text
    $src, // the main video source
    $src_1, // alternative source path #1 for the video
    $src_2, // alternative source path #2 for the video
    $start;

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getCaption() {
    return $this->caption;
  }

  /**
   * @return string
   */
  public function getChapters() {
    return $this->chapters;
  }

  /**
   * @return string
   */
  public function getEnd() {
    return $this->end;
  }

  /**
   * @return string
   */
  public function getMobile() {
    return $this->mobile;
  }

  /**
   * @return string
   */
  public function getRtmp() {
    return $this->rtmp;
  }

  /**
   * @return string
   */
  public function getRtmpPath() {
    return $this->rtmp_path;
  }

  /**
   * @return string
   */
  public function getSplash() {
    return $this->splash;
  }

  /**
   * @return string
   */
  public function getSplashText() {
    return $this->splash_text;
  }

  /**
   * @return string
   */
  public function getSrc() {
    return $this->src;
  }

  /**
   * @return string
   */
  public function getSrc1() {
    return $this->src_1;
  }

  /**
   * @return string
   */
  public function getSrc2() {
    return $this->src_2;
  }

  /**
   * @return string
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * FV_Player_Db_Shortcode_Player_Video constructor.
   *
   * @param int $id         ID of video to load data from the DB for.
   * @param array $options  Options for a newly created video that will be stored in a DB.
   *
   * @throws Exception When no valid ID nor options are provided.
   */
  function __construct($id, $options = array()) {
    global $wpdb;

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new video into the DB in such case
    if (is_array($options) && count($options)) {
      foreach ($options as $key => $value) {
        if (property_exists($this, $key)) {
          if ($key !== 'id') {
            $this->$key = $value;
          } else {
            // ID cannot be set, as it's automatically assigned to all new videos
            trigger_error('ID of a newly created DB video was provided but will be generated automatically.');
          }
        } else {
          // generate warning
          trigger_error('Unknown property for new DB video: ' . $key);
        }
      }
    } else if (is_int($id) && $id > 0) {
      // no options, load data from DB
      $video_data = $wpdb->get_row($wpdb->query('SELECT * FROM '.$wpdb->prefix.'fv_player_videos WHERE id = '. $id));
      if ($video_data) {
        // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
        foreach ($video_data as $key => $value) {
          $this->$key = $value;
        }
      } else {
        $this->is_valid = false;
      }
    } else {
      throw new \Exception('No options nor a valid ID was provided for DB video instance.');
    }
  }

  /**
   * Stores new video instance or updates and existing one
   * in the database.
   *
   * @param array $meta_data An optional array of key-value objects
   *                         with possible meta data for this video.
   *
   * @return bool|int Returns record ID if successful, false otherwise.
   */
  public function save($meta_data = array()) {
    global $wpdb;

    // prepare SQL
    $is_update   = ($this->id ? true : false);
    $sql         = ($is_update ? 'UPDATE' : 'INSERT INTO').' '.$wpdb->prefix.'fv_player_videos SET ';
    $data_keys   = array();
    $data_values = array();

    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'is_valid') {
        $data_keys[] = $property . ' = %s';
        $data_values[] = $value;
      }
    }

    $sql .= implode(',', $data_keys);

    if ($is_update) {
      $sql .= ' WHERE id = ' . $this->id;
    }

    $wpdb->query( $wpdb->prepare( $sql, $data_values ));

    if (!$is_update) {
      $this->id = $wpdb->insert_id;
    }

    if (!$wpdb->last_error) {
      // check for any meta data
      if (is_array($meta_data) && count($meta_data)) {
        // we have meta, let's insert that
        foreach ($meta_data as $meta_record) {
          // add our video ID
          $meta_record['id_video'] = $this->id;

          // create new record in DB
          $meta_object = new FV_Player_Db_Shortcode_Player_Video_Meta(null, $meta_record);
          $meta_object->save();
        }
      }

      return $this->id;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }
}
