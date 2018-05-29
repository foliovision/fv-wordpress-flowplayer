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

// player meta data instance with options that's stored in a DB
class FV_Player_Db_Shortcode_Player_Player_Meta {

  private
    $id, // automatic ID for the meta data
    $is_valid = true, // used when loading meta data from DB to determine whether we've found it
    $id_player, // DB ID of the video to which this meta data belongs
    $meta_key, // arbitrary meta key
    $meta_value, // arbitrary meta value
    $db_table_name,
    $additional_objects = array();

  /**
   * @param mixed $meta_value
   */
  public function setMetaValue( $meta_value ) {
    $this->meta_value = $meta_value;
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return int
   */
  public function getIdPlayer() {
    return $this->id_player;
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
   * @return bool
   */
  public function getIsValid() {
    return $this->is_valid;
  }

  /**
   * Checks for DB tables existence and creates it as necessary.
   *
   * @param $wpdb The global WordPress database object.
   */
  private function initDB($wpdb) {
    global $fv_fp;

    $this->db_table_name = $wpdb->prefix.'fv_player_playermeta';

    if (!$fv_fp->_get_option('player_meta_model_db_checked')) {
      if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $this->db_table_name . "'" ) != $this->db_table_name ) {
        $sql = "
CREATE TABLE `" . $this->db_table_name . "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_player` int(10) UNSIGNED NOT NULL,
  `meta_key` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `meta_key` (`meta_key`)
)" . $wpdb->get_charset_collate() . ";";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        $fv_fp->_set_option('player_meta_model_db_checked', 1);
      }
    }
  }

  /**
   * Makes this meta data object linked to a record in database.
   * Used for player preview purposes.
   *
   * @param $id The DB ID to which we'll link this meta data record.
   */
  public function link2db($id) {
    $this->id = (int) $id;
  }

  /**
   * Returns a list of meta records that were potentially loaded
   * via multiple IDs in the constructor. If there are none,
   * null will be returned.
   *
   * @return array|null Returns list of loaded meta objects or null if none were loaded.
   */
  public function getAllLoadedMeta() {
    if (count($this->additional_objects)) {
      return $this->additional_objects;
    } else {
      return null;
    }
  }

  /**
   * FV_Player_Db_Shortcode_Player_Player_Meta constructor.
   *
   * @param int $id         ID of player meta data to load data from the DB for.
   * @param array $options  Options for a newly created player meta data that will be stored in a DB.
   *
   * @throws Exception When no valid ID nor options are provided.
   */
  function __construct($id, $options = array()) {
    global $wpdb;

    $this->initDB($wpdb);
    $multiID = is_array($id);

    // check whether we're not trying to load data for a single player
    // rather than meta data by its own ID
    $load_for_player = false;
    if (is_array($options) && count($options) && isset($options['id_player']) && is_array($options['id_player'])) {
      $load_for_player = true;
      $multiID = true;
      $id = $options['id_player'];
      // reset this, so we don't try to create a new record below
      $options = array();
    }

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new player meta data into the DB in such case
    if (is_array($options) && count($options)) {
      foreach ($options as $key => $value) {
        if (property_exists($this, $key)) {
          if ($key !== 'id') {
            $this->$key = $value;
          }
        } else {
          // generate warning
          trigger_error('Unknown property for new DB player meta data item: ' . $key);
        }
      }
    } else if ($multiID || (is_numeric($id) && $id > 0)) {
      // no options, load data from DB
      if ($multiID) {
        // make sure we have numeric IDs
        foreach ($id as $id_key => $id_value) {
          $id[$id_key] = (int) $id_value;
        }

        // load multiple player metas via their IDs but a single query and return their values
        $meta_data = $wpdb->get_results('SELECT * FROM '.$this->db_table_name.' WHERE ' . ($load_for_player ? 'id_player' : 'id') . ' IN('. implode(',', $id).')');
      } else {
        // load a single player meta data record
        $meta_data = $wpdb->get_row($wpdb->query('SELECT * FROM '.$this->db_table_name.' WHERE id = '. $id));
      }

      if ($meta_data) {
        // single ID, just populate our own data
        if (!$multiID) {
          // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
          foreach ( $meta_data as $key => $value ) {
            $this->$key = $value;
          }
        } else {
          // multiple IDs, create new player meta objects for each of them except the first one,
          // for which we'll use this instance
          $first_done = false;
          foreach ($meta_data as $db_record) {
            if (!$first_done) {
              // fill-in our internal variables
              foreach ( $db_record as $key => $value ) {
                $this->$key = $value;
              }
              $this->additional_objects[] = $this;
              $first_done = true;
            } else {
              // create a new player meta object and populate it with DB values
              $record_id = $db_record->id;
              // if we don't unset this, we'll get warnings
              unset($db_record->id);
              $video_meta_object = new FV_Player_Db_Shortcode_Player_Player_Meta(null, get_object_vars($db_record));
              $video_meta_object->link2db($record_id);

              // cache is in the list of all loaded player meta objects
              $this->additional_objects[] = $video_meta_object;
            }
          }
        }
      } else {
        $this->is_valid = false;
      }
    } else {
      throw new \Exception('No options nor a valid ID was provided for DB player meta data item.');
    }
  }

  /**
   * Returns all options data for this player meta object.
   *
   * @return array Returns all options data for this player meta object.
   */
  public function getAllDataValues() {
    $data = array();
    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'is_valid' && $property != 'db_table_name' && $property != 'additional_objects') {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Stores new player meta data item or updates and existing one
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
      if ($property != 'id' && $property != 'is_valid' && $property != 'db_table_name' && $property != 'additional_objects') {
        $is_player_id = ($property == 'id_player');
        $data_keys[] = $property . ' = '.($is_player_id ? (int) $value : '%s');

        if (!$is_player_id) {
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

  /**
   * Removes meta data instance from the database.
   *
   * @return bool Returns true if the delete was successful, false otherwise.
   */
  public function delete() {
    // not a DB meta? no delete
    if (!$this->is_valid) {
      return false;
    }

    global $wpdb;

    $wpdb->delete($this->db_table_name, array('id' => $this->id));

    if (!$wpdb->last_error) {
      return true;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }
}
