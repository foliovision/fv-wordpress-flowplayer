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
    $ab, // whether to show AB loop
    $ad, // any HTML ad text
    $ad_height, // height of advertisement for this player
    $ad_width, // width of advertisement for this player
    $ad_skip, // whether or not to skip ads for this player
    $align, // alignment position
    $autoplay, // whether to autoplay videos on page load
    $controlbar, // whether to show the control bar for this player
    $copy_text,
    $drm_text, // whether to show DRM text on the player
    $email_list, // ID of the e-mail list to collect e-mails to at the end of playlist
    $embed, // whether to show embed links for this player
    $end_actions, // what do to when the playlist in this player ends
    $end_action_value, // the actual shortcode value for end_actions field
    $engine, // enforces the Flash engine for the playback of the video
    $height, // height of this player on page
    $hflip, // whether to horizontally flip the player
    $lightbox, // whether to enable displaying this player in a lightbox
    $lightbox_caption, // title for the lightbox popup
    $lightbox_height, // height for the lightbox popup
    $lightbox_width, // width for the lightbox popup
    $live, // whether this video is a live stream
    $logo, // adds a logo to the video or hides the globally preset one
    $player_name, // custom name for the player
    $player_slug, // short slug to be used as a unique identifier for this player that can be used instead of an ID
    $playlist,
    $playlist_advance, // whether to auto-advance the playlist in this player (On / Off / Default)
    $playlist_hide, // whether to hide the playlist items below the video box
    $play_button, // whether to hide the play/pause button on the control bar
    $popup_id, // ID of the popup to show at the end of playlist
    $qsel,
    $redirect, // where to redirect after the end of playlist
    $share, // whether to display sharing buttons (On / Off / Default)
    $share_title, // title for sharing buttons
    $share_url,
    $speed,
    $sticky, // whether or not to enable sticky functionality for this player
    $video_ads,
    $video_ads_post,
    $width, // with of the player on page
    $videos,
    $video_objects = null,
    $numeric_properties = array('id', 'ad_height', 'ad_width', 'height', 'lightbox_height', 'lightbox_width', 'width'),
    $db_table_name,
    $meta_data = null; // object of this player's meta data

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
  public function getEngine() {
    return $this->engine;
  }

  /**
   * @return string
   */
  public function getLogo() {
    return $this->logo;
  }

  /**
   * @return string
   */
  public function getPlaylistHide() {
    return $this->playlist_hide;
  }

  /**
   * @return string
   */
  public function getPlayButton() {
    return $this->play_button;
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
  public function getDrmText() {
    return $this->drm_text;
  }

  /**
   * @return string
   */
  public function getEmailList() {
    return $this->email_list;
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
  public function getLive() {
    return $this->live;
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
  public function getPopupId() {
    return $this->popup_id;
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
   * Checks for DB tables existence and creates it as necessary.
   *
   * @param $wpdb The global WordPress database object.
   */
  private function initDB($wpdb) {
    global $fv_fp;

    $this->db_table_name = $wpdb->prefix.'fv_player_players';

    if (!$fv_fp->_get_option('player_model_db_checked')) {
      if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $this->db_table_name . "'" ) != $this->db_table_name ) {
        $sql = "
CREATE TABLE `" . $this->db_table_name . "` (
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
  `autoplay` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to autoplay videos on page load',
  `controlbar` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to show the control bar for this player',
  `copy_text` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `drm_text` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to show DRM text on the player',
  `email_list` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID of the e-mail list to collect e-mails to at the end of playlist',
  `embed` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to show embed links for this player',
  `end_actions` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'what do to when the playlist in this player ends',
  `end_action_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'the actual shortcode value for end_actions field',
  `engine` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'enforces the Flash engine for the playback of the video',
  `height` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'height of this player on page',
  `hflip` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to horizontally flip the player',
  `lightbox` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to enable displaying this player in a lightbox',
  `lightbox_caption` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'title for the lightbox popup',
  `lightbox_height` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'height for the lightbox popup',
  `lightbox_width` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'width for the lightbox popup',
  `live` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether this video is a live stream',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'adds a logo to the video or hides the globally preset one',
  `playlist` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT '[liststyle in shortcode] style of the playlist',
  `playlist_advance` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to auto-advance the playlist in this player (On / Off / Default)',
  `playlist_hide` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to hide the playlist items below the video box',
  `play_button` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'whether to hide the play/pause button on the control bar',
  `popup_id` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID of the popup to show at the end of playlist',
  `qsel` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'where to redirect after the end of playlist',
  `share` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether to display sharing buttons (On / Off / Default)',
  `share_title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'title for sharing buttons',
  `share_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `speed` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sticky` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'whether or not to enable sticky functionality for this player',
  `video_ads` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '[preroll in shortcode] ID of a saved video ad to be played as a pre-roll',
  `video_ads_post` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '[postroll in shortcode] ID of a saved video ad to be played as a pre-roll',
  `width` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'with of the player on page'
  PRIMARY KEY (`id`)
)" . $wpdb->get_charset_collate() . ";";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
      }

      $fv_fp->_set_option('player_model_db_checked', 1);
    }
  }

  /**
   * FV_Player_Db_Shortcode_Player constructor.
   *
   * @param int $id         ID of player to load data from the DB for.
   * @param array $options  Options for a newly created player that will be stored in a DB.
   *
   * @throws Exception When no valid ID nor options are provided.
   */
  function __construct($id, $options = array()) {
    global $wpdb;

    $this->initDB($wpdb);

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new player into the DB in such case
    if (is_array($options) && count($options)) {
      foreach ($options as $key => $value) {
        if (property_exists($this, $key)) {
          if ($key !== 'id') {
            $this->$key = $value;
          } else {
            // ID cannot be set, as it's automatically assigned to all new players
            trigger_error('ID of a newly created DB player was provided but will be generated automatically.');
          }
        } else {
          // generate warning
          trigger_error('Unknown property for new DB player: ' . $key);
        }
      }
    } else if (is_numeric($id) && $id > 0) {
      // no options, load data from DB
      $player_data = $wpdb->get_row('SELECT * FROM '.$this->db_table_name.' WHERE id = '. $id);
      if ($player_data) {
        // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
        foreach ($player_data as $key => $value) {
          $this->$key = $value;
        }
      } else {
        $this->is_valid = false;
      }
    } else {
      throw new \Exception('No options nor a valid ID was provided for DB player instance.');
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
      $this->meta_data = new FV_Player_Db_Shortcode_Player_Player_Meta(null, array('id_player' => array($id)));
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
        $meta_object = new FV_Player_Db_Shortcode_Player_Player_Meta(null, $meta_record);

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
      if ($property != 'numeric_properties' && $property != 'is_valid' && $property != 'db_table_name' && $property != 'meta_data') {
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
   * @return array Returns all meta data for this player.
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function getMetaData() {
    // meta data already loaded and present, return them
    if ($this->meta_data && $this->meta_data !== -1) {
      if ( $this->meta_data->getAllLoadedMeta() ) {
        return $this->meta_data->getAllLoadedMeta();
      } else {
        if ($this->meta_data && $this->meta_data->getIsValid()) {
          return array( $this->meta_data );
        } else {
          return array();
        }
      }
    } else if ($this->meta_data === null) {
      // meta data not loaded yet - load them now
      $this->meta_data = new FV_Player_Db_Shortcode_Player_Player_Meta(null, array('id_player' => array($this->id)));

      // set meta data to -1, so we know we didn't get any meta data for this player
      if (!$this->meta_data->getIsValid() && !$this->meta_data->getAllLoadedMeta()) {
        $this->meta_data = -1;
        return array();
      } else {
        if ($this->meta_data->getAllLoadedMeta()) {
          return $this->meta_data->getAllLoadedMeta();
        } else {
          if ($this->meta_data && $this->meta_data->getIsValid()) {
            return array( $this->meta_data );
          } else {
            return array();
          }
        }
      }
    } else {
      return array();
    }
  }

  /**
   * Returns all video objects for this player.
   *
   * @return array Returns all video objects for this player.
   * @throws Exception When an underlying video object throws an exception.
   */
  public function getVideos() {
    // video data already loaded and present, return them
    if ($this->video_objects && $this->video_objects !== -1) {
      return $this->video_objects;
    } else if ($this->video_objects === null) {
      // video objects not loaded yet - load them now
      $videos = new FV_Player_Db_Shortcode_Player_Video(explode(',', $this->videos));

      // set meta data to -1, so we know we didn't get any meta data for this video
      if (!$videos->getIsValid()) {
        $this->video_objects = -1;
        return array();
      } else {
        $this->video_objects = $videos->getAllLoadedVideos();

        // load meta data for all videos at once, then link them to those videos,
        // as we will always load meta data for those, so it's no use to lazy-load
        // those only when needed (which creates additional DB requests per each video)
        $ids = array();
        foreach ($this->video_objects as $video) {
          $ids[] = $video->getId();
        }

        $metas = new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => $ids));
        $meta_2_video = array();

        // prepare loaded meta to be assigned to their respective videos
        foreach ($metas->getAllLoadedMeta() as $meta_object) {
          if (empty($meta_2_video[$meta_object->getIdVideo()])) {
            $meta_2_video[$meta_object->getIdVideo()] = array();
          }

          $meta_2_video[$meta_object->getIdVideo()][] = $meta_object->getAllDataValues();
        }

        // assign all loaded meta data to their respective videos
        foreach ($this->video_objects as $video) {
          $video->link2meta($meta_2_video[$video->getId()]);
        }

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
    $sql         = ($is_update ? 'UPDATE' : 'INSERT INTO').' '.$this->db_table_name.' SET ';
    $data_keys   = array();
    $data_values = array();

    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'numeric_properties' && $property != 'is_valid' && $property != 'db_table_name' && $property != 'video_objects' && $property != 'meta_data') {
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
          $meta_object = new FV_Player_Db_Shortcode_Player_Player_Meta(null, $meta_record);

          // add meta data ID
          if ($is_update) {
            $meta_object->link2db($meta_record['id']);
          }

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
