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
    $end, // allows you to show only a specific part of a video
    $mobile, // mobile (smaller-sized) version of this video
    $rtmp, // optional RTMP server URL
    $rtmp_path, // if RTMP is set, this will have the path on the server to the RTMP stream
    $splash, // URL to the splash screen picture
    $splash_text, // an optional splash screen text
    $src, // the main video source
    $src_1, // alternative source path #1 for the video
    $src_2, // alternative source path #2 for the video
    $start, // allows you to show only a specific part of a video
    $db_table_name,
    $additional_objects = array(),
    $meta_data = null; // object of this video's meta data

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
   * Checks for DB tables existence and creates it as necessary.
   *
   * @param $wpdb The global WordPress database object.
   */
  private function initDB($wpdb) {
    $this->db_table_name = $wpdb->prefix.'fv_player_videos';
    if ($wpdb->get_var("SHOW TABLES LIKE '".$this->db_table_name."'") !== $this->db_table_name) {
      $sql = "
CREATE TABLE `".$this->db_table_name."` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'optional video caption',
  `chapters` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL for a VTT file for displaying captions',
  `end` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'allows you to show only a specific part of a video',
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mobile (smaller-sized) version of this video',
  `rtmp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'optional RTMP server URL',
  `rtmp_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'if RTMP is set, this will have the path on the server to the RTMP stream',
  `splash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL to the splash screen picture',
  `splash_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'an optional splash screen text',
  `src` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'the main video source',
  `src_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'alternative source path #1 for the video',
  `src_2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'alternative source path #2 for the video',
  `start` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'allows you to show only a specific part of a video',
  PRIMARY KEY (`id`)
)" . $wpdb->get_charset_collate() . ";";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta($sql);
    }
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

    $this->initDB($wpdb);
    $multiID = is_array($id);

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
    } else if ($multiID || (is_numeric($id) && $id > 0)) {
      // no options, load data from DB
      if ($multiID) {
        // make sure we have numeric IDs
        foreach ($id as $id_key => $id_value) {
          $id[$id_key] = (int) $id_value;
        }

        // load multiple videos via their IDs but a single query and return their values
        $video_data = $wpdb->get_results('SELECT * FROM '.$this->db_table_name.' WHERE id IN('. implode(',', $id).')');
      } else {
        // load a single video
        $video_data = $wpdb->get_row('SELECT * FROM '.$this->db_table_name.' WHERE id = '. $id);
      }

      if ($video_data) {
        // single ID, just populate our own data
        if (!$multiID) {
          // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
          foreach ( $video_data as $key => $value ) {
            $this->$key = $value;
          }

          // load meta data
          $this->meta_data = new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => array($video_data->id)));
        } else {
          // multiple IDs, create new video objects for each of them except the first one,
          // for which we'll use this instance
          $first_done = false;
          foreach ($video_data as $db_record) {
            if (!$first_done) {
              // fill-in our internal variables
              foreach ( $db_record as $key => $value ) {
                $this->$key = $value;
              }

              // load meta data
              $this->meta_data = new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => array($db_record->id)));

              // add this to all the loaded video objects
              $this->additional_objects[] = $this;
              $first_done = true;
            } else {
              // create a new video object and populate it with DB values
              $record_id = $db_record->id;
              // if we don't unset this, we'll get warnings
              unset($db_record->id);
              $video_object = new FV_Player_Db_Shortcode_Player_Video(null, get_object_vars($db_record));
              $video_object->link2db($record_id, true);

              // cache is in the list of all loaded video objects
              $this->additional_objects[] = $video_object;
            }
          }
        }
      } else {
        $this->is_valid = false;
      }
    } else {
      throw new \Exception('No options nor a valid ID was provided for DB video instance.');
    }
  }

  /**
   * Makes this video linked to a record in database.
   * This is used when loading multiple videos in the constructor,
   * so we can return them as objects from the DB and any saving will
   * not insert their duplicates.
   *
   * @param $id        The DB ID to which we'll link this video.
   * @param $load_meta If true, the meta data will be loaded for the video from database.
   *                   Used when loading multiple videos at once with the array $id constructor parameter.
   *
   * @throws Exception When the underlying Meta object throws.
   */
  public function link2db($id, $load_meta = false) {
    $this->id = $id;

    if ($load_meta) {
      $this->meta_data = new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => array($id)));
    }
  }

  /**
   * Returns a list of videos that were potentially loaded
   * via multiple IDs in the constructor. If there are none,
   * null will be returned.
   *
   * @return array|null Returns list of loaded video objects or null if none were loaded.
   */
  public function getAllLoadedVideos() {
    if (count($this->additional_objects)) {
      return $this->additional_objects;
    } else {
      return null;
    }
  }

  /**
   * Returns all options data for this video.
   *
   * @return array Returns all options data for this video.
   */
  public function getAllDataValues() {
    $data = array();
    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'is_valid' && $property != 'db_table_name' && $property != 'additional_objects' && $property != 'meta_data') {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Returns meta data for this video.
   *
   * @return array Returns all meta data for this video.
   */
  public function getMetaData() {
    if ($this->meta_data->getAllLoadedMeta()) {
      return $this->meta_data->getAllLoadedMeta();
    } else {
      return array($this->meta_data);
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
   * @throws Exception When the underlying metadata object throws.
   */
  public function save($meta_data = array()) {
    global $wpdb;

    // prepare SQL
    $is_update   = ($this->id ? true : false);
    $sql         = ($is_update ? 'UPDATE' : 'INSERT INTO').' '.$this->db_table_name.' SET ';
    $data_keys   = array();
    $data_values = array();

    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'is_valid' && $property != 'db_table_name' && $property != 'additional_objects' && $property != 'meta_data') {
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
          $this->meta_data = $meta_object;
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
