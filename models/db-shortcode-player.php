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
    $midroll, // manually sets the second in which the VAST ad will start
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
    $hlskey,
    $vast, // manual VAST ad tag
    $vast_engine, // type of the VAST engine set manually
    $videos,
    $numeric_properties = array('id', 'ad_height', 'ad_width', 'height', 'lightbox_height', 'lightbox_width', 'width'),
    $db_table_name;

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
  public function getMidroll() {
    return $this->midroll;
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
   * @return string
   */
  public function getVast() {
    return $this->vast;
  }

  /**
   * @return string
   */
  public function getVastEngine() {
    return $this->vast_engine;
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
  public function getHlskey() {
    return $this->hlskey;
  }

  /**
   * @return string
   */
  public function getVideos() {
    return $this->videos;
  } // comma-separated list of video IDs for this player

  /**
   * Checks for DB tables existence and creates it as necessary.
   *
   * @param $wpdb The global WordPress database object.
   */
  private function initDB($wpdb) {
    $this->db_table_name = $wpdb->prefix.'fv_player_players';
    if ($wpdb->get_var("SHOW TABLES LIKE '".$this->db_table_name."'") !== $this->db_table_name) {
      $sql = "
CREATE TABLE `".$this->db_table_name."` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `midroll` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'manually sets the second in which the VAST ad will start',
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
  `width` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'with of the player on page',
  `hlskey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vast` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'manual VAST ad tag',
  `vast_engine` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'type of the VAST engine set manually',
  `videos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'comma-separated list of video IDs for this player',
  PRIMARY KEY (`id`)
)" . $wpdb->get_charset_collate() . ";";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta($sql);
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
   * Returns all global options data for this player.
   *
   * @return array Returns all global options data for this player.
   */
  public function getAllDataValues() {
    $data = array();
    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'numeric_properties' && $property != 'is_valid' && $property != 'db_table_name') {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Stores new player instance or updates and existing one
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
      if ($property != 'id' && $property != 'numeric_properties' && $property != 'is_valid' && $property != 'db_table_name') {
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
      return $this->id;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }
}
