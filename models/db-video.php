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
class FV_Player_Db_Video {

  private
    $id, // automatic ID for the video
    $is_valid = false, // used when loading the video from DB to determine whether we've found it
    $caption, // optional video caption
    $end, // allows you to show only a specific part of a video
    $mobile, // mobile (smaller-sized) version of this video
    $rtmp, // optional RTMP server URL
    $rtmp_path, // if RTMP is set, this will have the path on the server to the RTMP stream
    $splash, // URL to the splash screen picture
    $splash_text, // an optional splash screen text
    $splash_attachment_id, // splash attachment id
    $src, // the main video source
    $src1, // alternative source path #1 for the video
    $src2, // alternative source path #2 for the video
    $start, // allows you to show only a specific part of a video
    $meta_data = null; // object of this video's meta data

  private static
    $db_table_name,
    $DB_Instance = null;

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
  public function getCaptionFromSrc() {
    $src = $this->getSrc();
    $arr = explode('/', $src);
    $caption = end($arr);
    
    if( $caption == 'index.m3u8' ) {
      unset($arr[count($arr)-1]);
      $caption = end($arr);
    }

    $caption = apply_filters( 'fv_flowplayer_caption_src', $caption , $src, $this );

    return urldecode($caption);
  }
  
  /**
   * @return string
   */
  public function getDuration() {
    return intval($this->getMetaValue('duration',true));
  }

  /**
   * @return string
   */
  public function getEnd() {
    return flowplayer::hms_to_seconds($this->end);
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
   * @return int
   */
  public function getSplashAttachmentId() {
    return $this->splash_attachment_id;
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
    return $this->src1;
  }

  /**
   * @return string
   */
  public function getSrc2() {
    return $this->src2;
  }

  /**
   * @return string
   */
  public function getStart() {
    return flowplayer::hms_to_seconds($this->start);
  }

  /**
   * @return bool
   */
  public function getIsValid() {
    return $this->is_valid;
  }

  /**
   * Initializes database name, including WP prefix
   * once WPDB class is initialized.
   *
   * @return string Returns the actual table name for this ORM class.
   */
  public static function init_db_name() {
    global $wpdb;

    self::$db_table_name = $wpdb->prefix.'fv_player_videos';
    return self::$db_table_name;
  }

  /**
   * Returns name of the video DB table.
   *
   * @return mixed The name of the video DB table.
   */
  public static function get_db_table_name() {
    if ( !self::$db_table_name ) {
      self::init_db_name();
    }

    return self::$db_table_name;
  }

  /**
   * Checks for DB tables existence and creates it as necessary.
   *
   * @param $force Forces to run dbDelta.
   */
  public static function initDB($force = false) {
    global $wpdb, $fv_fp, $fv_wp_flowplayer_ver;

    self::init_db_name();

    $res = false;

    if( defined('PHPUnitTestMode') || !$fv_fp->_get_option('video_model_db_checked') || $fv_fp->_get_option('video_model_db_checked') != $fv_wp_flowplayer_ver || $force ) {
      $sql = "
CREATE TABLE " . self::$db_table_name . " (
  id bigint(20) unsigned NOT NULL auto_increment,
  src varchar(1024) NOT NULL,
  src1 varchar(1024) NOT NULL,
  src2 varchar(1024) NOT NULL,
  splash_attachment_id bigint(20) unsigned,
  splash varchar(512) NOT NULL,
  splash_text varchar(512) NOT NULL,
  caption varchar(1024) NOT NULL,
  end varchar(16) NOT NULL,
  mobile varchar(512) NOT NULL,
  rtmp varchar(128) NOT NULL,
  rtmp_path varchar(128) NOT NULL,
  start varchar(16) NOT NULL,
  PRIMARY KEY  (id),
  KEY src (src(191)),
  KEY caption (caption(191))
)" . $wpdb->get_charset_collate() . ";";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      $res = dbDelta( $sql );
      
      $fv_fp->_set_option('video_model_db_checked', $fv_wp_flowplayer_ver);
    }

    return $res;
  }

  /**
   * FV_Player_Db_Video constructor.
   *
   * @param int $id         ID of video to load data from the DB for.
   * @param array $options  Options for a newly created video that will be stored in a DB.
   * @param FV_Player_Db    $DB_Cache Instance of the DB shortcode global object that handles caching
   *                        of videos, players and their meta data.
   *
   * @throws Exception When no valid ID nor options are provided.
   */
  function __construct($id, $options = array(), $DB_Cache = null) {
    global $wpdb;

    if ($DB_Cache) {
      self::$DB_Instance = $DB_Cache;
    } else {
      global $FV_Player_Db;
      self::$DB_Instance = $DB_Cache = $FV_Player_Db;
    }

    self::initDB();

    // TODO: This should not be here at all
    $multiID = is_array($id);

    // don't load anything, if we've only created this instance
    // to initialize the database (this comes from list-table.php and unit tests)
    if ($id === -1) {
      return;
    }

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new video into the DB in such case
    if (is_array($options) && count($options) ) {
      foreach ($options as $key => $value) {
        if( $key == 'meta' ) continue; // meta is handled elsewhere, but it's part of the object when importing from JSON
        
        if (property_exists($this, $key)) {
          if ($key !== 'id') {
            if( !is_string($value) ) $value = '';

            $this->$key = strip_tags( FV_Player_Db::sanitize( $value ) );
          } else {
            // ID cannot be set, as it's automatically assigned to all new videos
            trigger_error('ID of a newly created DB video was provided but will be generated automatically.');
          }
        }
      }

      $this->is_valid = true;
    } else if ($multiID || (is_numeric($id) && $id > 0)) {
      /* @var $cache FV_Player_Db_Video[] */
      $cache = ($DB_Cache ? $DB_Cache->getVideosCache() : array());
      $all_cached = false;

      // no options, load data from DB
      if ($multiID) {
        // make sure we have numeric IDs and that they're not cached yet
        $query_ids = array();
        foreach ($id as $id_key => $id_value) {
          if (!isset($cache[$id_value])) {
            $query_ids[ $id_key ] = (int) $id_value;
          }

          $id[ $id_key ] = (int) $id_value;
        }

        // load multiple videos via their IDs but a single query and return their values
        if (count($query_ids)) {
          $select = '*';

          $where = ' WHERE id IN('. implode(',', $query_ids).') ';
          
          $order = '';
          
          $limit = '';
          
          $video_data = $wpdb->get_results('SELECT '.$select.' FROM '.self::$db_table_name.$where.$order.$limit);
          
          if( !$video_data && count($id) != count($query_ids) ) { // if no video data has returned, but we have the rest of videos cached already
            $all_cached = true;
          }
        } else {
          $all_cached = true;
        }
      } else {
        if (!isset($cache[$id])) {
          // load a single video
          $video_data = $wpdb->get_row( '
          SELECT
            *
          FROM
            ' . self::$db_table_name . '
          WHERE
            id = ' . intval($id)
          );
        } else {
          $all_cached = true;
        }
      }

      if (isset($video_data) && $video_data && (is_object($video_data) || count($video_data))) {
        // single ID, just populate our own data
        if (!$multiID) {
          // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
          foreach ( $video_data as $key => $value ) {
            $this->$key = FV_Player_Db::sanitize( $value );
          }

          // cache this video in DB object
          if ($DB_Cache) {
            $cache[$this->id] = $this;
          }
        } else {
          // multiple IDs, create new video objects for each of them except the first one,
          // for which we'll use this instance
          $first_done = false;
          foreach ($video_data as $db_record) {
            if (!$first_done) {
              // fill-in our internal variables
              foreach ( $db_record as $key => $value ) {
                $this->$key = FV_Player_Db::sanitize( $value );
              }

              $first_done = true;

              // cache this video in DB object
              if ($DB_Cache) {
                $cache[$this->id] = $this;
              }
            } else {
              // create a new video object and populate it with DB values
              $record_id = $db_record->id;
              // if we don't unset this, we'll get warnings
              unset($db_record->id);

              if ($DB_Cache && !$DB_Cache->isVideoCached($record_id)) {
                $video_object = new FV_Player_Db_Video( null, get_object_vars( $db_record ), self::$DB_Instance );
                $video_object->link2db( $record_id );

                // cache this video in DB object
                if ( $DB_Cache ) {
                  $cache[ $record_id ] = $video_object;
                }
              }
            }
          }
        }
        $this->is_valid = true;
      } else if ($all_cached) {
        // fill the data for this class with data of the cached class
        if ($multiID) {
          $cached_video = $cache[reset($id)];
        } else {
          $cached_video = $cache[$id];
        }

        if ( $cached_video ) {
          foreach ($cached_video->getAllDataValues() as $key => $value) {
            $this->$key = FV_Player_Db::sanitize( $value );
          }

          // add meta data
          $this->meta_data = $cached_video->getMetaData();

          // make this class a valid video
          $this->is_valid = true;
        }
      }
    } else {
      throw new Exception('No options nor a valid ID was provided for DB video instance.');
    }

    // update cache, if changed
    if (isset($cache) && (!isset($all_cached) || !$all_cached)) {
      self::$DB_Instance->setVideosCache($cache);
    }
  }

  /**
   * Makes this video linked to a record in database.
   * This is used when loading multiple videos in the constructor,
   * so we can return them as objects from the DB and any saving will
   * not insert their duplicates.
   *
   * @param int  $id        The DB ID to which we'll link this video.
   * @param bool $load_meta If true, the meta data will be loaded for the video from database.
   *                        Used when loading multiple videos at once with the array $id constructor parameter.
   *
   * @throws Exception When the underlying Meta object throws.
   */
  public function link2db($id, $load_meta = false) {
    $this->id = (int) $id;

    if ($load_meta) {
      $this->meta_data = new FV_Player_Db_Video_Meta(null, array('id_video' => array($id)), self::$DB_Instance);
    }
  }

  /**
   * This method will manually link meta data to the video.
   * Used when not using save() method to link meta data to video while saving it
   * into the database (i.e. while previewing etc.)
   *
   * @param FV_Player_Db_Video_Meta $meta_data The meta data object to link to this video.
   *
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function link2meta($meta_data) {
    if (is_array($meta_data) && count($meta_data)) {
      // we have meta, let's insert that
      $first_done = false;
      foreach ($meta_data as $meta_record) {
        // create new record in DB
        $meta_object = new FV_Player_Db_Video_Meta(null, $meta_record, self::$DB_Instance);

        // link to DB, if the meta record has an ID
        if (!empty($meta_record['id'])) {
          $meta_object->link2db($meta_record['id']);
        }

        if (!$first_done) {
          $this->meta_data = array($meta_object);
          $first_done = true;
        } else {
          $this->meta_data[] = $meta_object;
        }
      }
    } else if ($meta_data === -1) {
      $this->meta_data = -1;
    }
  }

  /**
   * Searches for a player video via custom query.
   * Used in plugins such as HLS which will
   * provide video src data but not ID to search for.
   *
   * @param bool $like   The LIKE part for the database query. If false, exact match is used.
   * @param null $fields Fields to return for this search. If null, all fields are returned.
   *
   * @return bool Returns true if any data were loaded, false otherwise.
   */
  public function searchBySrc($like = false, $fields = null) {
    _deprecated_function( __FUNCTION__, '7.5.22', 'FV_Player_Db::query_videos' );

    global $wpdb;

    $row = $wpdb->get_row("SELECT ". ($fields ? esc_sql($fields) : '*') ." FROM `" . self::$db_table_name . "` WHERE `src` ". ($like ? 'LIKE "%'.esc_sql($this->src).'%"' : '="'.esc_sql($this->src).'"') ." ORDER BY id DESC");

    if (!$row) {
      return false;
    } else {
      // load up all values for this video
      foreach ($row as $key => $value) {
        if (property_exists($this, $key)) {
          $this->$key = FV_Player_Db::sanitize( $value );
        }
      }

      return true;
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
      if ($property != 'is_valid' && $property != 'db_table_name' && $property != 'DB_Instance' && $property != 'meta_data') {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Returns meta data for this video.
   *
   * @return FV_Player_Db_Video_Meta[] Returns all meta data for this video.
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function getMetaData() {
    // meta data already loaded and present, return them
    if ($this->meta_data && $this->meta_data !== -1) {
      // meta data will be an array if we filled all of them at once
      // from database at the time when player is initially created
      if (is_array($this->meta_data)) {
        return $this->meta_data;
      } else if ( self::$DB_Instance->isVideoMetaCached($this->id) ) {
        $cache = self::$DB_Instance->getVideoMetaCache();
        return $cache[$this->id];
      } else {
        if ($this->meta_data && $this->meta_data->getIsValid()) {
          return array( $this->meta_data );
        } else {
          return array();
        }
      }
    } else if ($this->meta_data === null) {
      // meta data not loaded yet - load them now
      $this->meta_data = new FV_Player_Db_Video_Meta(null, array('id_video' => array($this->id)), self::$DB_Instance);

      // set meta data to -1, so we know we didn't get any meta data for this video
      if (!$this->meta_data->getIsValid()) {
        $this->meta_data = -1;
        return array();
      } else {
        if ($this->meta_data && $this->meta_data->getIsValid()) {
          // meta data will be an array if we filled all of them at once
          // from database at the time when player is initially created
          if (is_array($this->meta_data)) {
            return $this->meta_data;
          } else if ( self::$DB_Instance->isVideoMetaCached($this->id) ) {
            $cache = self::$DB_Instance->getVideoMetaCache();
            return $cache[$this->id];
          } else {
            if ($this->meta_data && $this->meta_data->getIsValid()) {
              return array( $this->meta_data );
            } else {
              return array();
            }
          }
        } else {
          return array();
        }
      }
    } else {
      return array();
    }
  }
  
  /**
   * Returns actual meta data for a key for this video.
   */
  public function getMetaValue( $key, $single = false ) {
    $output = array();
    $data = $this->getMetaData();
    if (count($data)) {
      foreach ($data as $meta_object) {
        if ($meta_object->getMetaKey() == $key) {
          if( $single ) return $meta_object->getMetaValue();
          $output[] = $meta_object->getMetaValue();
        }
      }
    }
    if( $single ) return false;
    return $output;
  }
  
  /**
   * Updates or instert a video meta row
   *
   * @param string $key       The meta key
   * @param string $value     The meta value     
   * @param int $id           ID of the existing video meta row.
   *                          If it's left empty only one $key is allowed for the $this->getId() video ID.
   *
   * @throws Exception When the underlying Meta object throws.
   *
   * @return bool|int Returns record ID if successful, false otherwise.
   * 
   */
  public function updateMetaValue( $key, $value, $id = false ) {
    $to_update = false;
    $data = $this->getMetaData();

    if (count($data)) {      
      foreach ($data as $meta_object) {
        // find the matching video meta row and if id is provided as well, match on that too
        if( ( !$id || $id == $meta_object->getId() ) && $meta_object->getMetaKey() == $key) {
          $to_update = $meta_object->getId();
          
          // if there is no change, then do not run any update and instead return the row ID
          if(
            is_string($value) && strcmp($meta_object->getMetaValue(), $value) == 0 ||
            !is_string($value) && $meta_object->getMetaValue() == $value
          ) {
            return $to_update;
          }
        }
      }
    }

    // if matching row has been found or if it was not found and no row id is provided (insert)
    if( $to_update || !$to_update && !$id ) {
      $meta = new FV_Player_Db_Video_Meta( null, array( 'id_video' => $this->getId(), 'meta_key' => $key, 'meta_value' => $value ), self::$DB_Instance );
      if( $to_update ) $meta->link2db($to_update);
      return $meta->save();        
    }
    
    return false;
  }

  /**
   * Lets you alter any of the video properties and then call save()
   *
   * @param string $key       The meta key
   * @param string $value     The meta value     
   */
  public function set( $key, $value ) {
    $this->$key = FV_Player_Db::sanitize( $value );
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
    $sql         = ($is_update ? 'UPDATE' : 'INSERT INTO').' '.self::$db_table_name.' SET ';
    $data_keys   = array();
    $data_values = array();

    $splash_attachment_id = $this->getSplashAttachmentId();

    if( $is_update ) {
      // check if splash url changed
      if( !empty( $splash_attachment_id ) ) {
        $saved_splash = wp_get_attachment_image_url($splash_attachment_id, 'full', false);
        if( !empty( $saved_splash ) ) {
          $saved_parse = wp_parse_url( $saved_splash );
          $current_parse = $this->getSplash() ? wp_parse_url( $this->getSplash() ) : false;

          // if splash removed or changed, delete splash attachment
          if( !$current_parse || (strcmp( $saved_parse['path'], $current_parse['path'] ) !== 0) ) {
            delete_post_meta( $splash_attachment_id, 'fv_player_video_id', $this->getId() );
            $this->splash_attachment_id = '';
          }
        }
      }
    }

    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'is_valid' && $property != 'db_table_name' && $property != 'DB_Instance' && $property != 'meta_data') {
        $data_keys[] = $property . ' = %s';
        $data_values[] = strip_tags($value);
      }
    }

    $sql .= implode(',', $data_keys);

    if ( $is_update ) {
      $sql .= ' WHERE id = ' . $this->id;
    }

    $wpdb->query( $wpdb->prepare( $sql, $data_values ));

    if (!$is_update) {
      $this->id = $wpdb->insert_id;
    }

    if (!$wpdb->last_error) {
      // check for any meta data
      if (is_array($meta_data) && count($meta_data)) {
        // we check which meta values are no longer set and remove these
        $existing_meta = $is_update ? $this->getMetaData() : array();
        $existing_meta_ids = array();
        foreach( $existing_meta as $existing ) {
          $found = false;
          foreach ($meta_data as $meta_record) {
            if( !empty($meta_record['meta_value']) && $meta_record['meta_key'] == $existing->getMetaKey() ) {
              $found = true;
              break;
            }
          }
          if( !$found ) {
            $existing->delete();
          } else {
            $existing_meta_ids[$existing->getId()] = true;
          }
        }

        // Clear previous checker error
        $this->deleteMetaValue( 'error' );
        $this->deleteMetaValue( 'error_count' );
        
        // we have meta, let's insert that
        foreach ($meta_data as $meta_record) {
          // it's possible that we switched the checkbox off and then on, by that time its id won't exist anymore! Todo: remove data-id instead?
          if( !empty($meta_record['id']) && empty($existing_meta_ids[$meta_record['id']]) ) {
            unset($meta_record['id']);
          }
          
          // if the meta value has no ID associated, we replace the first one which exists, effectively preventing multiple values under the same meta key, which is something to improve, perhaps
          if( empty($meta_record['id']) ) {
            foreach( $existing_meta AS $existing ) {
              if( $meta_record['meta_key'] == $existing->getMetaKey() ) {
                $meta_record['id'] = $existing->getId();
                break;
              }
            }
          }
          
          // add our video ID
          $meta_record['id_video'] = $this->id;

          // create new record in DB
          $meta_object = new FV_Player_Db_Video_Meta(null, $meta_record, self::$DB_Instance);

          // add meta data ID
          if( !empty($meta_record['id']) ) {
            $meta_object->link2db($meta_record['id']);
          } else if( empty($meta_record['meta_value']) ) {
            continue;
          }

          $meta_object->save();
          $this->meta_data = $meta_object;
        }
      }

      // add this meta into cache
      $cache = self::$DB_Instance->getVideosCache();
      $cache[$this->id] = $this;
      self::$DB_Instance->setVideosCache($cache);

      $saved_attachments = $wpdb->get_col( 
        $wpdb->prepare( "SELECT post_id FROM `{$wpdb->postmeta}` WHERE meta_key = 'fv_player_video_id' AND meta_value = %d", $this->getId() )
      );

      // check for unused attachments
      if( !empty( $saved_attachments ) ) {
        foreach( $saved_attachments as $post_id ) {
          // remove if not used
          if( $splash_attachment_id != $post_id ) {
            delete_post_meta( $post_id, 'fv_player_video_id' );
          }
        }
      }

      // store video id for splash attachment
      if( $splash_attachment_id ) {
        update_post_meta( $splash_attachment_id, 'fv_player_video_id', $this->getId() );
      }

      return $this->id;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }

  /**
   * Prepares this class' properties for export
   * and returns them in an associative array.
   *
   * @return array Returns an associative array of this class' properties and their values.
   */
  public function export() {
    $export_data = array();
    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'is_valid' && $property != 'db_table_name' && $property != 'DB_Instance' && $property != 'meta_data') {
        $export_data[$property] = $value;
      }
    }

    return $export_data;
  }

  /**
   * Removes video instance from the database.
   *
   * @return bool Returns true if the delete was successful, false otherwise.
   */
  public function delete() {
    // not a DB video? no delete
    if (!$this->is_valid) {
      return false;
    }

    global $wpdb;

    $wpdb->delete(self::$db_table_name, array('id' => $this->id));

    if (!$wpdb->last_error) {
      // remove this meta from cache
      $cache = self::$DB_Instance->getVideosCache();
      if (isset($cache[$this->id])) {
        unset($cache[$this->id]);
        self::$DB_Instance->setVideosCache($cache);
      }

      return true;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }

/**
   * Updates or instert a video meta row
   *
   * @param string $key   The meta key
   * @param string $value Option meta value to remove
   *
   * @throws Exception    When the underlying Meta object throws.
   *
   * @return int          Returns number of removed meta rows
   * 
   */
  public function deleteMetaValue( $key, $value = false ) {
    $deleted = 0;
    $data = $this->getMetaData();

    if( count($data) ) {      
      foreach( $data as $meta_object ) {
        if(
          $meta_object->getMetaKey() == $key &&
          ( !$value || $meta_object->getMetaValue() == $value )
        ) {
          if( $meta_object->delete() ) {
            $deleted++;
          }
        }
      }
    }

    return $deleted;
  }
}
