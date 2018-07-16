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

// player instance with options that's stored in a DB
class FV_Player_Db_Shortcode_Player {

  private
    $id, // automatic ID for the player
    $is_valid = true, // used when loading the player from DB to determine whether we've found it
    $author, // user ID that created this player
    $changed_by, // user ID that last updated this player
    $date_created, // date this playlist was created on
    $date_modified, // date this playlist was modified on
    $ab, // whether to show AB loop
    $ad, // any HTML ad text
    $ad_height, // height of advertisement for this player
    $ad_width, // width of advertisement for this player
    $ad_skip, // whether or not to skip ads for this player
    $align, // alignment position
    $autoplay, // whether to autoplay videos on page load
    $controlbar, // whether to show the control bar for this player
    $copy_text, // whether to show DRM text on the player
    $embed, // whether to show embed links for this player
    $end_actions, // what do to when the playlist in this player ends
    $end_action_value, // the actual shortcode value for end_actions field
    $height, // height of this player on page
    $hflip, // whether to horizontally flip the player
    $lightbox, // whether to enable displaying this player in a lightbox
    $lightbox_caption, // title for the lightbox popup
    $lightbox_height, // height for the lightbox popup
    $lightbox_width, // width for the lightbox popup
    $logo, // adds a logo to the video or hides the globally preset one
    $loop, // (NON-ORM, class property only) loops player at the end if set
    $player_name, // custom name for the player
    $player_slug, // short slug to be used as a unique identifier for this player that can be used instead of an ID
    $playlist,
    $playlist_advance, // whether to auto-advance the playlist in this player (On / Off / Default)
    $popup, // (NON-ORM, class property only) ID of the popup to show at the end of playlist
    $qsel,
    $redirect, // (NON-ORM, class property only) where to redirect after the end of playlist
    $share, // whether to display sharing buttons (On / Off / Default)
    $share_title, // title for sharing buttons
    $share_url,
    $speed,
    $splashend, // (NON-ORM, class property only) populated by "true" if splash should be shown when the player stops
    $sticky, // whether or not to enable sticky functionality for this player
    $video_ads,
    $video_ads_post,
    $width, // with of the player on page
    $videos, // comma-delimited IDs of videos for this player
    $video_objects = null,
    $numeric_properties = array('id', 'ad_height', 'ad_width', 'height', 'lightbox_height', 'lightbox_width', 'width', 'author', 'changed_by'),
    $DB_Shortcode_Instance = null,
    $meta_data = null;

  /**
   * @param mixed $videos
   */
  public function setVideos( $videos ) {
    $this->videos = $videos;
  }

  /**
   * @return int
   */
  public function getAuthor() {
    return $this->author;
  }

  /**
   * @return int
   */
  public function getChangedBy() {
    return $this->changed_by;
  }

  /**
   * @return int
   */
  public function getDateCreated() {
    return $this->date_created;
  }

  /**
   * @return int
   */
  public function getDateModified() {
    return $this->date_modified;
  } // object of this player's meta data

  private static $db_table_name;

  /**
   * @return string
   */
  public function getPlayerName() {
    return $this->player_name;
  }

  /**
   * @return string
   */
  public function getPlayerSlug() {
    return $this->player_slug;
  }

  /**
   * @return string
   */
  public function getEndActionValue() {
    return $this->end_action_value;
  }

  /**
   * @return string
   */
  public function getAd() {
    return $this->ad;
  }

  /**
   * @return string
   */
  public function getLogo() {
    return $this->logo;
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getAb() {
    return $this->ab;
  }

  /**
   * @return int
   */
  public function getAdHeight() {
    return $this->ad_height;
  }

  /**
   * @return int
   */
  public function getAdWidth() {
    return $this->ad_width;
  }

  /**
   * @return string
   */
  public function getAdSkip() {
    return $this->ad_skip;
  }

  /**
   * @return string
   */
  public function getAlign() {
    return $this->align;
  }

  /**
   * @return string
   */
  public function getAutoplay() {
    return $this->autoplay;
  }

  /**
   * @return string
   */
  public function getControlbar() {
    return $this->controlbar;
  }

  /**
   * @return string
   */
  public function getCopyText() {
    return $this->copy_text;
  }

  /**
   * @return string
   */
  public function getEmbed() {
    return $this->embed;
  }

  /**
   * @return string
   */
  public function getEndActions() {
    return $this->end_actions;
  }

  /**
   * @return int
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * @return string
   */
  public function getHflip() {
    return $this->hflip;
  }

  /**
   * @return string
   */
  public function getLightbox() {
    return $this->lightbox;
  }

  /**
   * @return string
   */
  public function getLightboxCaption() {
    return $this->lightbox_caption;
  }

  /**
   * @return int
   */
  public function getLightboxHeight() {
    return $this->lightbox_height;
  }

  /**
   * @return int
   */
  public function getLightboxWidth() {
    return $this->lightbox_width;
  }

  /**
   * @return string
   */
  public function getPlaylist() {
    return $this->playlist;
  }

  /**
   * @return string
   */
  public function getPlaylistAdvance() {
    return $this->playlist_advance;
  }

  /**
   * @return string
   */
  public function getPopup() {
    return $this->popup;
  }

  /**
   * @return string
   */
  public function getQsel() {
    return $this->qsel;
  }

  /**
   * @return string
   */
  public function getRedirect() {
    return $this->redirect;
  }

  /**
   * @return string
   */
  public function getShare() {
    return $this->share;
  }

  /**
   * @return string
   */
  public function getShareTitle() {
    return $this->share_title;
  }

  /**
   * @return string
   */
  public function getShareUrl() {
    return $this->share_url;
  }

  /**
   * @return string
   */
  public function getSpeed() {
    return $this->speed;
  }

  /**
   * @return string
   */
  public function getSticky() {
    return $this->sticky;
  }

  /**
   * @return string
   */
  public function getVideoAds() {
    return $this->video_ads;
  }

  /**
   * @return string
   */
  public function getVideoAdsPost() {
    return $this->video_ads_post;
  }

  /**
   * @return int
   */
  public function getWidth() {
    return $this->width;
  }

  /**
   * @return string
   */
  public function getVideoIds() {
    return $this->videos;
  } // comma-separated list of video IDs for this player

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

    self::$db_table_name = $wpdb->prefix.'fv_player_players';
    return self::$db_table_name;
  }

  /**
   * Checks for DB tables existence and creates it as necessary.
   *
   * @param $wpdb The global WordPress database object.
   */
  private function initDB($wpdb) {
    global $fv_fp;

    self::init_db_name();

    if (is_admin() || !$fv_fp->_get_option('player_model_db_checked')) {
      if ( $wpdb->get_var( "SHOW TABLES LIKE '" . self::$db_table_name . "'" ) != self::$db_table_name ) {
        $sql = "
CREATE TABLE `" . self::$db_table_name . "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `player_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'custom name for the player',
  `player_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '	short slug to be used as a unique identifier for this player that can be used instead of an ID',
  `videos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'comma-separated list of video IDs for this player',
  `ab` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to show AB loop',
  `ad` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'any HTML ad text',
  `ad_height` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'height of advertisement for this player',
  `ad_width` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'width of advertisement for this player',
  `ad_skip` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether or not to skip ads for this player',
  `align` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'alignment position',
  `author` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'user ID that created this player',
  `autoplay` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to autoplay videos on page load',
  `controlbar` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to show the control bar for this player',
  `copy_text` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'user ID that last updated this player',
  `date_created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date this playlist was created on',
  `date_modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date this playlist was modified on',
  `embed` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to show embed links for this player',
  `end_actions` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'what do to when the playlist in this player ends',
  `end_action_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'the actual shortcode value for end_actions field',
  `height` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'height of this player on page',
  `hflip` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to horizontally flip the player',
  `lightbox` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to enable displaying this player in a lightbox',
  `lightbox_caption` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'title for the lightbox popup',
  `lightbox_height` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'height for the lightbox popup',
  `lightbox_width` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'width for the lightbox popup',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'adds a logo to the video or hides the globally preset one',
  `playlist` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT '[liststyle in shortcode] style of the playlist',
  `playlist_advance` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to auto-advance the playlist in this player (On / Off / Default)',
  `qsel` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `share` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to display sharing buttons (On / Off / Default)',
  `share_title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'title for sharing buttons',
  `share_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `speed` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sticky` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether or not to enable sticky functionality for this player',
  `video_ads` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '[preroll in shortcode] ID of a saved video ad to be played as a pre-roll',
  `video_ads_post` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '[postroll in shortcode] ID of a saved video ad to be played as a pre-roll',
  `width` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'with of the player on page',
  PRIMARY KEY (`id`)
)" . $wpdb->get_charset_collate() . ";";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        $fv_fp->_set_option('player_model_db_checked', 1);
      }
    }
  }

  /**
   * Fills non-orm variables that are not directly linked
   * from received POST data to the DB.
   */
  private function fill_non_orm_properties() {
    $this->propagate_end_action_value();
  }

  /**
   * Propagates end action value into one of the non-orm
   * related class keys for this player. This is because
   * we've refactored the end_actions functionality and now
   * we don't have the original DB counterparts for the input
   * fields in the wizard.
   */
  private function propagate_end_action_value() {
    switch ($this->end_actions) {
      case 'redirect': $this->redirect = $this->end_action_value;
        break;

      case 'popup': $this->popup = $this->end_action_value;
        break;

      case 'email_list': $this->popup = 'email-'.$this->end_action_value;
        break;

      case 'loop': $this->loop = true;
        break;

      case 'splashend': $this->splashend = true;
        break;
    }
  }

  /**
   * FV_Player_Db_Shortcode_Player constructor.
   *
   * @param int $id                              ID of player to load data from the DB for.
   * @param array $options                       Options for a newly created player that will be stored in a DB.
   * @param FV_Player_Db_Shortcode $DB_Shortcode Instance of the DB shortcode global object that handles caching
   *                                             of videos, players and their meta data.
   *
   * @throws Exception When no valid ID nor options are provided.
   */
  function __construct($id, $options = array(), $DB_Shortcode = null) {
    global $wpdb;

    if ($DB_Shortcode) {
      $this->DB_Shortcode_Instance = $DB_Shortcode;
    }

    $this->initDB($wpdb);
    $multiID = is_array($id) || $id === null;

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new player into the DB in such case
    if (is_array($options) && count($options) && !isset($options['db_options'])) {
      foreach ($options as $key => $value) {
        if (property_exists($this, $key)) {
          if ($key !== 'id') {
            $this->$key = stripslashes($value);
          } else {
            // ID cannot be set, as it's automatically assigned to all new players
            trigger_error('ID of a newly created DB player was provided but will be generated automatically.');
          }
        } else {
          // ignore old database structure records
          if (!in_array($key, array('drm_text', 'email_list', 'live', 'popup_id'))) {
            // generate warning
            trigger_error('Unknown property for new DB player: ' . $key);
          }
        }
      }

      // add dates
      $this->date_created = $this->date_modified = strftime( '%Y-%m-%d %H:%M:%S', time() );

      // add author
      $this->author = $this->changed_by = get_current_user_id();
    } else if ($multiID || (is_numeric($id) && $id >= -1)) {
      /* @var $cache FV_Player_Db_Shortcode_Player[] */
      $cache = ($DB_Shortcode ? $DB_Shortcode->getPlayersCache() : array());
      $all_cached = false;

      // no options, load data from DB
      if ($multiID) {
        $query_ids = array();

        if ($id !== null) {
          // make sure we have numeric IDs and that they're not cached yet
          $query_ids = array();
          foreach ( $id as $id_key => $id_value ) {
            // check if this player is not cached yet
            if ($DB_Shortcode && !$DB_Shortcode->isPlayerCached($id_value)) {
              $query_ids[ $id_key ] = (int) $id_value;
            }

            $id[ $id_key ] = (int) $id_value;
          }
        }

        if ($id === null || count($query_ids)) {
          // if we have multiple video IDs to load players for, let's prepare a like statement here
          $where_like_part = '';

          if ($options && !empty($options['db_options']) && !empty($options['db_options']['search_by_video_ids'])){
            $where_like_part = array();

            foreach ($options['db_options']['search_by_video_ids'] as $player_video_id) {
              $where_like_part[] = "(videos = \"$player_video_id\" OR videos LIKE \"%,$player_video_id\" OR videos LIKE \"$player_video_id,%\")";
            }

            $where_like_part = implode(' OR ', $where_like_part);
          }

          // load multiple players via their IDs but a single query and return their values
          $player_data = $wpdb->get_results('
          SELECT
            '.($options && !empty($options['db_options']) && !empty($options['db_options']['select_fields']) ? 'id,'.$options['db_options']['select_fields'] : '*').'
          FROM
            '.self::$db_table_name.($id !== null ? '
          WHERE
            id IN('. implode(',', $query_ids).')' : ($options && !empty($options['db_options']) && !empty($options['db_options']['search_by_video_ids']) ? ' WHERE '.$where_like_part : '')).
            ($options && !empty($options['db_options']) && !empty($options['db_options']['order_by']) ? ' ORDER BY '.$options['db_options']['order_by'].(!empty($options['db_options']['order']) ? ' '.$options['db_options']['order'] : '') : '').
            ($options && !empty($options['db_options']) && isset($options['db_options']['offset']) && isset($options['db_options']['per_page']) ? ' LIMIT '.$options['db_options']['offset'].', '.$options['db_options']['per_page'] : '')
          );
        } else if ($id !== null && !count($query_ids)) {
          $all_cached = true;
        } else {
          $player_data = -1;
          $this->is_valid = false;
        }
      } else {
        if ($DB_Shortcode && !$DB_Shortcode->isPlayerCached($id)) {
          // load a single video
          $player_data = $wpdb->get_row('
          SELECT
            '.($options && !empty($options['db_options']) && !empty($options['db_options']['select_fields']) ? 'id,'.$options['db_options']['select_fields'] : '*').'
          FROM
            '.self::$db_table_name.'
          WHERE
            id = '.$id.
            ($options && !empty($options['db_options']) && !empty($options['db_options']['order_by']) ? ' ORDER BY '.$options['db_options']['order_by'].(!empty($options['db_options']['order']) ? ' '.$options['db_options']['order'] : '') : '').
            ($options && !empty($options['db_options']) && !empty($options['db_options']['offset']) && !empty($options['db_options']['per_page']) ? ' LIMIT '.$options['db_options']['offset'].', '.$options['db_options']['per_page'] : '')
          );
        } else if ($DB_Shortcode && $DB_Shortcode->isPlayerCached($id)) {
          $all_cached = true;
        } else {
          $player_data = -1;
          $this->is_valid = false;
        }
      }

      if (isset($player_data) && $player_data !== -1 && count($player_data)) {
        // single ID, just populate our own data
        if (!$multiID) {
          // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
          foreach ( $player_data as $key => $value ) {
            $this->$key = stripslashes($value);
          }

          // make sure we fill the appropriate non-orm object properties
          $this->fill_non_orm_properties();

          // cache this player in DB Shortcode object
          if ($DB_Shortcode) {
            $cache[$this->id] = $this;
          }
        } else {
          // multiple IDs, create new player objects for each of them
          // and cache those (except for the first one, which will be
          // this actual instance)
          $first_done = false;
          foreach ($player_data as $db_record) {
            if (!$first_done) {
              // fill-in our internal variables
              foreach ( $db_record as $key => $value ) {
                $this->$key = stripslashes($value);
              }

              // make sure we fill the appropriate non-orm object variables
              $this->fill_non_orm_properties();
              $first_done = true;

              // cache this player in DB Shortcode object
              if ($DB_Shortcode) {
                $cache[$db_record->id] = $this;
              }
            } else {
              // create a new video object and populate it with DB values
              $record_id = $db_record->id;
              // if we don't unset this, we'll get warnings
              unset($db_record->id);

              if ($this->DB_Shortcode_Instance && !$this->DB_Shortcode_Instance->isPlayerCached($record_id)) {
                $player_object = new FV_Player_Db_Shortcode_Player( null, get_object_vars( $db_record ), $this->DB_Shortcode_Instance );
                $player_object->link2db( $record_id );

                // cache this player in DB Shortcode object
                if ($DB_Shortcode) {
                  $cache[$record_id] = $player_object;
                }
              }
            }
          }
        }
      } else if ($all_cached) {
        // fill the data for this class with data of the cached class
        if ($multiID) {
          $cached_player = $cache[reset($id)];
        } else {
          $cached_player = $cache[$id];
        }

        foreach ($cached_player->getAllDataValues() as $key => $value) {
          $this->$key = stripslashes($value);
        }

        // add meta data
        $this->meta_data = $cached_player->getMetaData();

        // make this class a valid player
        $this->is_valid = true;
      } else if ($player_data !== -1) {
        // no players found in DB
        $this->is_valid = false;
      }
    } else {
      throw new \Exception('No options nor a valid ID was provided for DB player instance.');
    }

    // update cache, if changed
    if (isset($cache) && (!isset($all_cached) || !$all_cached)) {
      $this->DB_Shortcode_Instance->setPlayersCache($cache);
    }
  }

  /**
   * Retrieves total number of players in the database.
   *
   * @return int Returns the total number of players in database.
   */
  public static function getTotalPlayersCount() {
    global $player_ids_when_searching, $wpdb;

    self::init_db_name();

    // make total the number of players cached, if we've used search
    if (isset($_GET['s']) && $_GET['s']) {
      if ($player_ids_when_searching) {
        $total = count( $player_ids_when_searching );
      } else {
        $total = 0;
      }
    } else {
      $total = $wpdb->get_row('SELECT Count(*) AS Total FROM '.self::$db_table_name);
      if ($total) {
        $total = $total->Total;
      }
    }

    if ($total) {
      return $total;
    } else {
      return 0;
    }
  }

  /**
   * Makes this player linked to a record in database.
   * This is used when we want to update a player in the DB
   * instead of inserting a new record for it.
   *
   * @param int $id         The DB ID to which we'll link this player.
   * @param bool $load_meta If true, the meta data will be loaded for the video from database.
   *                        Used when loading multiple videos at once with the array $id constructor parameter.
   *
   * @throws Exception When the underlying Meta object throws.
   */
  public function link2db($id, $load_meta = false) {
    $this->id = (int) $id;

    if ($load_meta) {
      $this->meta_data = new FV_Player_Db_Shortcode_Player_Player_Meta(null, array('id_player' => array($id)), $this->DB_Shortcode_Instance);
    }
  }

  /**
   * This method will manually link meta data to the player.
   * Used when not using save() method to link meta data to player while saving it
   * into the database (i.e. while previewing etc.)
   *
   * @param FV_Player_Db_Shortcode_Player_Player_Meta $meta_data The meta data object to link to this player.
   *
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function link2meta($meta_data) {
    if (is_array($meta_data) && count($meta_data)) {
      // we have meta, let's insert that
      foreach ($meta_data as $meta_record) {
        // create new record in DB
        $meta_object = new FV_Player_Db_Shortcode_Player_Player_Meta(null, $meta_record, $this->DB_Shortcode_Instance);

        // link to DB, if the meta record has an ID
        if (!empty($meta_record['id'])) {
          $meta_object->link2db($meta_record['id']);
        }

        $this->meta_data = $meta_object;
      }
    }
  }

  /**
   * Returns all global options data for this player.
   *
   * @return array Returns all global options data for this player.
   */
  public function getAllDataValues() {
    $data = array();
    foreach (get_object_vars($this) as $property => $value) {
      if (!in_array($property, array('numeric_properties', 'is_valid', 'DB_Shortcode_Instance', 'db_table_name', 'meta_data'))) {
        // change ID to ID_PLAYER, as ID is used as a shortcode property
        if ($property == 'id') {
          $property = 'id_player';
        }
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Returns meta data for this player.
   *
   * @return FV_Player_Db_Shortcode_Player_Player_Meta[] Returns all meta data for this player.
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function getMetaData() {
    // meta data already loaded and present, return them
    if ($this->meta_data && $this->meta_data !== -1) {
      if ( $this->DB_Shortcode_Instance && $this->DB_Shortcode_Instance->isPlayerMetaCached($this->id) ) {
        $cache = $this->DB_Shortcode_Instance->getPlayerMetaCache();
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
      $this->meta_data = new FV_Player_Db_Shortcode_Player_Player_Meta(null, array('id_player' => array($this->id)), $this->DB_Shortcode_Instance);

      // set meta data to -1, so we know we didn't get any meta data for this player
      if (!$this->meta_data->getIsValid()) {
        $this->meta_data = -1;
        return array();
      } else {
        if ($this->meta_data && $this->meta_data->getIsValid()) {
          // we want to return all meta data for this player
          if ( $this->DB_Shortcode_Instance && $this->DB_Shortcode_Instance->isPlayerMetaCached($this->id) ) {
            $cache = $this->DB_Shortcode_Instance->getPlayerMetaCache();
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
   * Returns all video objects for this player.
   *
   * @return FV_Player_Db_Shortcode_Player_Video[] Returns all video objects for this player.
   * @throws Exception When an underlying video object throws an exception.
   */
  public function getVideos() {
    // video data already loaded and present, return them
    if ($this->video_objects && $this->video_objects !== -1) {
      return $this->video_objects;
    } else if ($this->video_objects === null) {
      // video objects not loaded yet - load them now
      $videos_in_order = explode(',', $this->videos);
      $videos = new FV_Player_Db_Shortcode_Player_Video($videos_in_order, array(), $this->DB_Shortcode_Instance);

      // set meta data to -1, so we know we didn't get any meta data for this video
      if (!$videos->getIsValid()) {
        $this->video_objects = -1;
        return array();
      } else {
        $this->video_objects = $this->DB_Shortcode_Instance->getVideosCache();

        // load meta data for all videos at once, then link them to those videos,
        // as we will always load meta data for those, so it's no use to lazy-load
        // those only when needed (which creates additional DB requests per each video)
        $ids = array();
        foreach ($this->video_objects as $video) {
          $ids[] = $video->getId();
        }

        new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => $ids), $this->DB_Shortcode_Instance);

        // assign all meta data to their respective videos
        foreach ( $this->video_objects as $video ) {
          if ( $this->DB_Shortcode_Instance->isVideoMetaCached($video->getId()) ) {
            // prepare meta data
            $meta_2_video = array();
            $cache = $this->DB_Shortcode_Instance->getVideoMetaCache();
            foreach ($cache[$video->getId()] as $meta_object) {
              $meta_2_video[] = $meta_object->getAllDataValues();
            }

            $video->link2meta( $meta_2_video );
          } else {
            $video->link2meta(-1);
          }
        }

        // fill video objects with videos sorted according to playlist order
        $ordered_video_objects = array();
        foreach ($videos_in_order as $video_id) {
          // find the correct video
          foreach ( $this->video_objects as $video ) {
            if ($video->getId() == $video_id) {
              $ordered_video_objects[] = $video;
              break;
            }
          }
        }
        $this->video_objects = $ordered_video_objects;

        return $this->video_objects;
      }
    } else {
      return array();
    }
  }

  /**
   * Stores new player instance or updates and existing one
   * in the database.
   *
   * @param array $meta_data An optional array of key-value objects
   *                         with possible meta data for this player.
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

    // fill date(s)
    $this->date_modified = strftime( '%Y-%m-%d %H:%M:%S', time() );

    if (!$is_update) {
      $this->date_created = $this->date_modified;
    }

    // fill author(s)
    $this->changed_by = get_current_user_id();

    if (!$is_update) {
      $this->author = $this->changed_by;
    }

    foreach (get_object_vars($this) as $property => $value) {
      if (!in_array($property, array('id', 'numeric_properties', 'is_valid', 'DB_Shortcode_Instance', 'db_table_name', 'video_objects', 'meta_data', 'popup', 'splashend', 'redirect', 'loop'))) {
        // don't update author or date created if we're updating
        if ($is_update && ($property == 'date_created' || $property == 'author')) {
          continue;
        }

        $numeric_value = in_array( $property, $this->numeric_properties );
        $data_keys[]   = $property . ' = ' . ($numeric_value  ? (int) $value : '%s' );

        if (!$numeric_value) {
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
      // check for any meta data
      if (is_array($meta_data) && count($meta_data)) {
        // we have meta, let's insert that
        foreach ($meta_data as $meta_record) {
          // add our player ID
          $meta_record['id_player'] = $this->id;

          // create new record in DB
          $meta_object = new FV_Player_Db_Shortcode_Player_Player_Meta(null, $meta_record, $this->DB_Shortcode_Instance);

          // add meta data ID
          if ($is_update) {
            $meta_object->link2db($meta_record['id']);
          }

          $meta_object->save();
          $this->meta_data = $meta_object;
        }
      }

      // cache this instance
      $cache = $this->DB_Shortcode_Instance->getPlayersCache();
      $cache[$this->id] = $this;
      $this->DB_Shortcode_Instance->setPlayersCache($cache);

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
      if (!in_array($property, array('id', 'numeric_properties', 'is_valid', 'DB_Shortcode_Instance', 'db_table_name', 'videos', 'video_objects', 'meta_data', 'popup', 'splashend', 'redirect', 'loop', 'author', 'changed_by', 'date_created', 'date_modified'))) {
        $export_data[$property] = $value;
      }
    }

    return $export_data;
  }

}
