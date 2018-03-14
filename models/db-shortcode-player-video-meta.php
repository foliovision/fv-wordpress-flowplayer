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

// video meta data instance with options that's stored in a DB
class FV_Player_Db_Shortcode_Player_Video_Meta {

  private
    $id, // automatic ID for the meta data
    $is_valid = true, // used when loading meta data from DB to determine whether we've found it
    $id_video, // DB ID of the video to which this meta data belongs
    $meta_key, // arbitrary meta key
    $meta_value, // arbitrary meta value
    $db_table_name;

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return int
   */
  public function getIdVideo() {
    return $this->id_video;
  }

  /**
   * @return string
   */
  public function getMetaKey() {
    return $this->meta_key;
  }

  /**
   * @return mixed
   */
  public function getMetaValue() {
    return $this->meta_value;
  }

  /**
   * FV_Player_Db_Shortcode_Player_Video_Meta constructor.
   *
   * @param int $id         ID of video meta data to load data from the DB for.
   * @param array $options  Options for a newly created video meta data that will be stored in a DB.
   *
   * @throws Exception When no valid ID nor options are provided.
   */
  function __construct($id, $options = array()) {
    global $wpdb;

    $this->db_table_name = $wpdb->prefix.'fv_player_videometa';
    if ($wpdb->get_var("SHOW TABLES LIKE '".$this->db_table_name."'") !== $this->db_table_name) {
      $sql = "
CREATE TABLE `".$this->db_table_name."` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_video` int(10) UNSIGNED NOT NULL,
  `meta_key` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_video` (`id_video`),
  KEY `meta_key` (`meta_key`)
)" . $wpdb->get_charset_collate() . ";";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta($sql);
    }

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new video meta data into the DB in such case
    if (is_array($options) && count($options)) {
      foreach ($options as $key => $value) {
        if (property_exists($this, $key)) {
          if ($key !== 'id') {
            $this->$key = $value;
          } else {
            // ID cannot be set, as it's automatically assigned to all new video meta data item
            trigger_error('ID of a newly created DB video meta data item was provided but will be generated automatically.');
          }
        } else {
          // generate warning
          trigger_error('Unknown property for new DB video meta data item: ' . $key);
        }
      }
    } else if (is_int($id) && $id > 0) {
      // no options, load data from DB
      $meta_data = $wpdb->get_row($wpdb->query('SELECT * FROM '.$this->db_table_name.' WHERE id = '. $id));
      if ($meta_data) {
        // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
        foreach ($meta_data as $key => $value) {
          $this->$key = $value;
        }
      } else {
        $this->is_valid = false;
      }
    } else {
      throw new \Exception('No options nor a valid ID was provided for DB video meta data item.');
    }
  }

  /**
   * Stores new video meta data item or updates and existing one
   * in the database.
   *
   * @return bool|int Returns record ID if successful, false otherwise.
   */
  public function save() {
    global $wpdb;

    // prepare SQL
    $is_update   = ($this->id ? true : false);
    $sql         = ($is_update ? 'UPDATE' : 'INSERT INTO').' '.$this->db_table_name.' SET ';
    $data_keys   = array();
    $data_values = array();

    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'is_valid' && $property != 'db_table_name') {
        $is_video_id = ($property == 'id_video');
        $data_keys[] = $property . ' = '.($is_video_id ? (int) $value : '%s');

        if (!$is_video_id) {
          $data_values[] = $value;
        }
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
      return $this->id;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }
}
