<?php
/*  FV Player - HTML5 video player
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
class FV_Player_Db_Player {

  private
    $id, // automatic ID for the player
    $is_valid = true, // used when loading the player from DB to determine whether we've found it
    $author, // user ID that created this player
    $changed_by, // user ID that last updated this player
    $date_created, // date this playlist was created on
    $date_modified, // date this playlist was modified on
    $ab, // whether to show AB loop
    $align, // alignment position, DEPRECATED
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
    $overlay, // any HTML overlay text
    $overlay_height, // height of overlay for this player
    $overlay_width, // width of overlay for this player
    $overlay_skip, // whether or not to use global overlay for this player
    $player_name, // custom name for the player
    $player_slug, // short slug to be used as a unique identifier for this player that can be used instead of an ID
    $playlist,
    $playlist_advance, // whether to auto-advance the playlist in this player (On / Off / Default)
    $qsel,
    $share, // whether to display sharing buttons (On / Off / Default)
    $share_title, // title for sharing buttons
    $share_url,
    $speed,
    $sticky, // whether or not to enable sticky functionality for this player
    $video_ads,
    $video_ads_post,
    $width, // with of the player on page
    $status, // draft of published
    $videos, // comma-delimited IDs of videos for this player
    $toggle_end_action,
    $toggle_overlay,
    $video_objects = null,
    $numeric_properties = array('id', 'author', 'changed_by'),
    $meta_data = null,
    $subtitles_count,
    $chapters_count,
    $transcript_count,
    $cues_count,
    $ignored_input_fields = array();

  private static
    $db_table_name,
    $DB_Instance = null;

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
    return intval($this->author);
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
    _deprecated_function( __METHOD__, '8.0', __CLASS__ . '::getOverlay' );

    return $this->getOverlay();
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
    _deprecated_function( __METHOD__, '8.0', __CLASS__ . '::getOverlayHeight' );

    return $this->getOverlayHeight();
  }

  /**
   * @return int
   */
  public function getAdWidth() {
    _deprecated_function( __METHOD__, '8.0', __CLASS__ . '::getOverlayWidth' );

    return $this->getOverlayWidth();
  }

  /**
   * @return string
   */
  public function getAdSkip() {
    _deprecated_function( __METHOD__, '8.0', __CLASS__ . '::getOverlaySkip' );

    return $this->getOverlaySkip();
  }

  /**
   * @return string
   */
  public function getAlign() { // DEPRECATED
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

  public function getCount($video_meta) {
    if( $video_meta == 'subtitles' && isset($this->subtitles_count) ) return $this->subtitles_count;
    if( $video_meta == 'cues' && isset($this->cues_count) ) return $this->cues_count;
    if( $video_meta == 'chapters' && isset($this->chapters_count) ) return $this->chapters_count;
    if( $video_meta == 'transcript' && isset($this->transcript_count) ) return $this->transcript_count;
    return 0;
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
  public function getOverlay() {
    return $this->overlay;
  }

  /**
   * @return int
   */
  public function getOverlayHeight() {
    return $this->overlay_height;
  }

  /**
   * @return int
   */
  public function getOverlayWidth() {
    return $this->overlay_width;
  }

  /**
   * @return string
   */
  public function getOverlaySkip() {
    return $this->overlay_skip;
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
  public function getPlaylistSplash() {
    return $this->playlist_splash;
  }


  /**
   * @return int
   */
  public function getPlaylistAttachmentId() {
    return $this->playlist_splash_attachment_id;
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
  public function getStatus() {
    return $this->status;
  }

  /**
   * @param $status
   */
  public function setStatus( $status ) {
    $this->status = $status;
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
    return boolval($this->is_valid);
  }

  /**
   * @return bool
   */
  public function getToggleEndAction() {
    return boolval($this->toggle_end_action);
  }

  /**
   * @return bool
   */
  public function getToggleAdCustom() {
    _deprecated_function( __METHOD__, '8.0', __CLASS__ . '::getToggleOverlay' );

    return $this->getToggleOverlay();
  }

  /**
   * @return bool
   */
  public function getToggleOverlay() {
    return boolval($this->toggle_overlay);
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

    if( defined('PHPUnitTestMode') || !$fv_fp->_get_option('player_model_db_checked') || $fv_fp->_get_option('player_model_db_checked') != $fv_wp_flowplayer_ver || $force ) {
      $sql = "
CREATE TABLE " . self::$db_table_name . " (
  id bigint(20) unsigned NOT NULL auto_increment,
  player_name varchar(255) NOT NULL,
  player_slug varchar(255) NOT NULL,
  videos text NOT NULL,
  ab varchar(7) NOT NULL,
  align varchar(7) NOT NULL,
  author bigint(20) unsigned NOT NULL default '0',
  autoplay varchar(7) NOT NULL,
  controlbar varchar(7) NOT NULL,
  copy_text varchar(120) NOT NULL,
  changed_by bigint(20) unsigned NOT NULL default '0',
  date_created datetime NOT NULL default CURRENT_TIMESTAMP,
  date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
  embed varchar(12) NOT NULL,
  end_actions varchar(10) NOT NULL,
  end_action_value varchar(255) NOT NULL,
  height varchar(7) NOT NULL,
  hflip varchar(7) NOT NULL,
  lightbox varchar(7) NOT NULL,
  lightbox_caption varchar(120) NOT NULL,
  lightbox_height varchar(7) NOT NULL,
  lightbox_width varchar(7) NOT NULL,
  overlay text NOT NULL,
  overlay_height varchar(7) NOT NULL,
  overlay_width varchar(7) NOT NULL,
  overlay_skip varchar(7) NOT NULL,
  playlist varchar(12) NOT NULL,
  playlist_advance varchar(7) NOT NULL,
  qsel varchar(25) NOT NULL,
  share varchar(7) NOT NULL,
  share_title varchar(120) NOT NULL,
  share_url varchar(255) NOT NULL,
  speed varchar(255) NOT NULL,
  sticky varchar(7) NOT NULL,
  video_ads varchar(10) NOT NULL,
  video_ads_post varchar(10) NOT NULL,
  width varchar(7) NOT NULL,
  status varchar(9) NOT NULL default 'published',
  toggle_end_action varchar(7) NOT NULL,
  toggle_overlay varchar(7) NOT NULL,
  PRIMARY KEY  (id)
)" . $wpdb->get_charset_collate() . ";";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      $res = dbDelta( $sql );

      if( $fv_fp->_get_option('player_model_db_checked') != $fv_wp_flowplayer_ver ) {
        self::updateTableConversion();
      }

      $fv_fp->_set_option('player_model_db_checked', $fv_wp_flowplayer_ver);
    }

    return $res;
  }

  /**
   * Run conversion of old data to new data structure.
   *
   * @return void
   */
  private static function updateTableConversion() {
    global $fv_fp, $wpdb;

    $table = self::$db_table_name;

    if( $fv_fp->_get_option('player_model_db_updated') != '7.9.3' ) {
      // enable toggle end action
      $wpdb->query("UPDATE `{$wpdb->prefix}fv_player_players` SET toggle_end_action = 'true' WHERE end_actions != '' AND end_action_value != ''");

      // enable toggle ad custom
      if ( $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = 'ad'", $table ) ) ) {
        $wpdb->query("UPDATE `{$wpdb->prefix}fv_player_players` SET toggle_overlay = 'true' WHERE ad != ''");
      }

      // ad => overlay
      if ( !FV_Player_Db::has_table_column( self::$db_table_name , 'overlay' ) ) {
        if ( $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s", $table, 'ad' ) ) ) {
          $wpdb->query( "UPDATE `{$wpdb->prefix}fv_player_players` SET `overlay` = `ad` WHERE `overlay` = '' AND `ad` != ''" );
        }
      }

      // ad_height => overlay_height
      if ( !FV_Player_Db::has_table_column( self::$db_table_name , 'overlay_height' ) ) {
        if ( $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s", $table, 'ad_height' ) ) ) {
          $wpdb->query( "UPDATE `{$wpdb->prefix}fv_player_players` SET `overlay_height` = `ad_height` WHERE `overlay_height` = '' AND `ad_height` != ''" );
        }
      }

      // ad_skip => overlay_skip
      if ( !FV_Player_Db::has_table_column( self::$db_table_name , 'overlay_skip' ) ) {
        if ( $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s", $table, 'ad_skip' ) ) ) {
          $wpdb->query( "UPDATE `{$wpdb->prefix}fv_player_players` SET `overlay_skip` = `ad_skip` WHERE `overlay_skip` = '' AND `ad_skip` != ''" );
        }
      }

      // ad_width => overlay_width
      if ( !FV_Player_Db::has_table_column( self::$db_table_name , 'overlay_width' ) ) {
        if ( $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s", $table, 'ad_width' ) ) ) {
          $wpdb->query( "UPDATE `{$wpdb->prefix}fv_player_players` SET `overlay_width` = `ad_width` WHERE `overlay_width` = '' AND `ad_width` != ''" );
        }
      }

      // toggle_ad_custom => toggle_overlay
      if ( !FV_Player_Db::has_table_column( self::$db_table_name , 'toggle_overlay' ) ) {
        if ( $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = %s AND column_name = %s", $table, 'toggle_ad_custom' ) ) ) {
          $wpdb->query( "UPDATE `{$wpdb->prefix}fv_player_players` SET `toggle_overlay` = `toggle_ad_custom` WHERE `toggle_overlay` = '' AND `toggle_ad_custom` != ''" );
        }
      }

      $fv_fp->_set_option('player_model_db_updated', '7.9.3');
    }
  }

  /**
   * Fills non-orm variables that are not directly linked
   * from received POST data to the DB.
   */
  private function fill_properties( $options, $DB_Cache = false ) {
    // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
    foreach ($options as $key => $value) {
      if (!in_array($key, $this->ignored_input_fields)) {
        if (property_exists($this, $key)) {

          $value = FV_Player_Db::strip_tags( $value, $key );

          $this->$key = FV_Player_Db::sanitize( $value );

        } else if ( in_array($key, array('subtitles_count', 'chapters_count', 'transcript_count', 'cues_count'))) {
          $this->$key = FV_Player_Db::sanitize( $value );

        }
      }
    }
  }

  /**
   * FV_Player_Db_Player constructor.
   *
   * @param int $id                              ID of player to load data from the DB for.
   * @param array $options                       Options for a newly created player that will be stored in a DB.
   * @param FV_Player_Db                         $DB_Cache Instance of the DB shortcode global object that handles caching
   *                                             of videos, players and their meta data.
   */
  function __construct($id, $options = array(), $DB_Cache = null) {

    global $wpdb;

    // add any extra fields from extending plugins that should be ORM-ignored
    $this->ignored_input_fields = apply_filters('fv_flowplayer_add_ignored_input_names', $this->ignored_input_fields);

    if ($DB_Cache) {
      self::$DB_Instance = $DB_Cache;
    } else {
      global $FV_Player_Db;
      self::$DB_Instance = $DB_Cache = $FV_Player_Db;
    }

    self::initDB();

    // For a while https://foliovision.com/player/advanced/player-database used to say the $player_id should be passed as array
    if( is_array($id) ) {
      $id = array_pop($id);
    }

    // don't load anything, if we've only created this instance
    // to initialize the database (this comes from list-table.php and unit tests)
    if ($id === -1) {
      return;
    }

    if ( is_numeric($id) && $id >= -1 ) {
      /* @var $cache FV_Player_Db_Player[] */
      $cache = ($DB_Cache ? $DB_Cache->getPlayersCache() : array());
      $all_cached = false;

      if ($DB_Cache && !$DB_Cache->isPlayerCached($id)) {
        // load a single player
        $player_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fv_player_players WHERE id = %d", $id ), ARRAY_A );

      } else if ($DB_Cache && $DB_Cache->isPlayerCached($id)) {
        $all_cached = true;
      } else {
        $player_data = -1;
        $this->is_valid = false;
      }

      if (isset($player_data) && $player_data !== -1 && is_array($player_data) && count($player_data)) {

        $this->fill_properties($player_data,$DB_Cache);

        // cache this player in DB object
        if ($DB_Cache) {
          $cache[$this->id] = $this;
        }

      } else if ($all_cached) {
        // fill the data for this class with data of the cached class
        $cached_player = $cache[$id];

        foreach ($cached_player->getAllDataValues() as $key => $value) {
          if( is_array($value) ) continue; // problem with video_objects when exporting

          $this->$key = FV_Player_Db::sanitize( $value );
        }

        // add meta data
        $this->meta_data = $cached_player->getMetaData();

        // make this class a valid player
        $this->is_valid = true;
      } else if ($player_data !== -1) {
        // no players found in DB
        $this->is_valid = false;
      }

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new player into the DB in such case
    } else if (is_array($options)) {

      if( !empty($options['id']) ) {
        // ID cannot be set, as it's automatically assigned to all new players
        trigger_error('ID of a newly created DB player was provided but will be generated automatically.');
        unset($options['id']);
      }

      $this->fill_properties($options);

      // add dates for newly created players
      if( empty($this->date_created) ) $this->date_created = date_format( date_create(), "Y-m-d H:i:s" );
      if( empty($this->date_modified) ) $this->date_modified = date_format( date_create(), "Y-m-d H:i:s" );

      // add author, if we're creating new player and not loading a player from DB for caching purposes
      if ( empty($options['author']) ) {
        $this->author = get_current_user_id();
      }

      if ( empty($options['changed_by']) ) {
        if ( !empty($options['author']) ) {
          $this->changed_by = $options['author'];
        } else {
          $this->changed_by = get_current_user_id();
        }
      }
    } else {
      if ( defined('WP_DEBUG') && WP_DEBUG ) {
        trigger_error( 'No options nor a valid ID was provided for DB player instance.' );
      }

      return;
    }

    // update cache, if changed
    if (isset($cache) && (!isset($all_cached) || !$all_cached)) {
      self::$DB_Instance->setPlayersCache($cache);
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
      $this->meta_data = new FV_Player_Db_Player_Meta(null, array('id_player' => array($id)), self::$DB_Instance);
    }
  }

  /**
   * This method will manually link meta data to the player.
   * Used when not using save() method to link meta data to player while saving it
   * into the database (i.e. while previewing etc.)
   *
   * @param FV_Player_Db_Player_Meta $meta_data The meta data object to link to this player.
   *
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function link2meta($meta_data) {
    if (is_array($meta_data) && count($meta_data)) {
      // we have meta, let's insert that
      $first_done = false;
      foreach ($meta_data as $meta_record) {
        // create new record in DB
        $meta_object = new FV_Player_Db_Player_Meta(null, $meta_record, self::$DB_Instance);

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
    }
  }

  /**
   * Returns player name, if its empty then its assembled from video captions and /or sources
   *
   * @return string player name from getter or assembled name
   */
  public function getPlayerNameWithFallback() {
    $player_name = $this->getPlayerName();

    // player name is present - return it
    if( empty( $player_name ) ) {
      $player_name = join(', ', $this->getPlayerVideoNames() );
    }

    // add "Draft" at the end of player, if in draft status
    if ( $this->getStatus() == 'draft' ) {
      $player_name .= ' (' . esc_attr__( 'Draft', 'fv-player' ) . ')';
    }

    return $player_name;
  }

  public function getPlayerVideoNames() {
    $video_names = array();

    foreach( $this->getVideos() as $video ) {
      $title = $video->getTitle();
      if( !$title ) {
        $title = $video->getTitleFromSrc();
      }

      $video_names[] = $title;
    }

    return $video_names;
  }

  /**
   * Returns all global options data for this player.
   *
   * @return array Returns all global options data for this player.
   */
  public function getAllDataValues() {
    $data = array();
    foreach (get_object_vars($this) as $property => $value) {
      if (!in_array($property, array('numeric_properties', 'is_valid', 'DB_Instance', 'db_table_name', 'meta_data', 'ignored_input_fields', 'subtitles_count', 'chapters_count', 'transcript_count', 'cues_count' ))) {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Returns meta data for this player.
   *
   * @return FV_Player_Db_Player_Meta[] Returns all meta data for this player.
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function getMetaData() {
    // meta data already loaded and present, return them
    if ($this->meta_data && $this->meta_data !== -1) {
      if (is_array($this->meta_data)) {
        return $this->meta_data;
      } else if ( self::$DB_Instance && self::$DB_Instance->isPlayerMetaCached($this->id) ) {
        $cache = self::$DB_Instance->getPlayerMetaCache();
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
      $this->meta_data = new FV_Player_Db_Player_Meta(null, array('id_player' => array($this->id)), self::$DB_Instance);

      // set meta data to -1, so we know we didn't get any meta data for this player
      if (!$this->meta_data->getIsValid()) {
        $this->meta_data = -1;
        return array();
      } else {
        if ($this->meta_data && $this->meta_data->getIsValid()) {
          // we want to return all meta data for this player
          if ( self::$DB_Instance && self::$DB_Instance->isPlayerMetaCached($this->id) ) {
            $cache = self::$DB_Instance->getPlayerMetaCache();
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
   * Returns actual meta data for a key for this player.
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
   * Returns all video objects for this player.
   *
   * @return FV_Player_Db_Video[] Returns all video objects for this player.
   * @throws Exception When an underlying video object throws an exception.
   */
  public function getVideos() {
    // video data already loaded and present, return them
    if ($this->video_objects && $this->video_objects !== -1) {
      return $this->video_objects;
    } else if ($this->video_objects === null) {
      // video objects not loaded yet - load them now
      $videos_in_order = explode(',', trim($this->videos, ','));
      $videos = new FV_Player_Db_Video($videos_in_order, array(), self::$DB_Instance);

      // set meta data to -1, so we know we didn't get any meta data for this video
      if (!$videos->getIsValid()) {
        $this->video_objects = -1;
        return array();
      } else {
        $this->video_objects = self::$DB_Instance->getVideosCache();

        // load meta data for all videos at once, then link them to those videos,
        // as we will always load meta data for those, so it's no use to lazy-load
        // those only when needed (which creates additional DB requests per each video)
        $ids = array();
        foreach ($this->video_objects as $video) {
          $ids[] = $video->getId();
        }

        new FV_Player_Db_Video_Meta(null, array('id_video' => $ids), self::$DB_Instance);

        // assign all meta data to their respective videos
        foreach ( $this->video_objects as $video ) {
          if ( self::$DB_Instance->isVideoMetaCached($video->getId()) ) {
            // prepare meta data
            $meta_2_video = array();
            $cache = self::$DB_Instance->getVideoMetaCache();
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
   * @param bool  $is_import Used when importing to respect the desired player status flag
   *
   * @return bool|int Returns record ID if successful, false otherwise.
   * @throws Exception When the underlying metadata object throws.
   */
  public function save($meta_data = array(), $is_import = false ) {
    global $wpdb;

    // prepare SQL
    $is_update   = ($this->id ? true : false);
    $data_values = array();

    // fill gmdate(s)
    $this->date_modified = date_format( date_create(), "Y-m-d H:i:s" );

    if (!$is_update && empty($this->date_created) ) {
      $this->date_created = $this->date_modified;
    }

    // fill author(s)
    $this->changed_by = get_current_user_id();

    if (!$is_update) {
      $this->author = $this->changed_by;
    }

    foreach (get_object_vars($this) as $property => $value) {
      if (!in_array($property, array('id', 'numeric_properties', 'is_valid', 'DB_Instance', 'db_table_name', 'video_objects', 'meta_data', 'ignored_input_fields', 'subtitles_count', 'chapters_count', 'transcript_count', 'cues_count' ))) {
        // don't update author or date created if we're updating
        if ($is_update && ($property == 'date_created' || $property == 'author')) {
          continue;
        }

        // make sure status is set to "draft" for a new player
        if ( $property == 'status' ) {
          if ( !$is_update && !$is_import ) {
            $value = 'draft';
          } else if ( !$value ) {
            // for existing player, only update if we need to change player status
            // from "draft" to "published", otherwise leave the status alone
            continue;
          }
        }

        $value = FV_Player_Db::strip_tags( $value, $property );

        if ( in_array( $property, array( 'author', 'changed_by' ) ) ) {
          $format[ $property ] = '%d';

          if ( ! $value ) {
            $value = 0;
          }

        } else {
          $format[ $property ] = '%s';

          if ( ! $value ) {
            $value = '';
          }
        }

        $data_values[ $property ] = $value;
      }
    }

    if ($is_update) {
      $wpdb->update( self::$db_table_name, $data_values, array( 'id' => $this->id ), $format );

    } else {
      $wpdb->insert( self::$db_table_name, $data_values, $format );
    }

    if (!$is_update) {
      $this->id = $wpdb->insert_id;
    }

    if (!$wpdb->last_error) {
      // check for any meta data
      if (is_array($meta_data) && count($meta_data)) {
        // we check which meta values are no longer set and remove these
        $existing_meta = $is_update ? $this->getMetaData() : array();
        $existing_meta_ids = array();
        foreach( $existing_meta AS $existing ) {
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

          // add our player ID
          $meta_record['id_player'] = $this->id;

          // create new record in DB
          $meta_object = new FV_Player_Db_Player_Meta(null, $meta_record, self::$DB_Instance);

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

      // cache this instance
      $cache = self::$DB_Instance->getPlayersCache();
      $cache[$this->id] = $this;
      self::$DB_Instance->setPlayersCache($cache);

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
      if (!in_array($property, array('id', 'id_player', 'numeric_properties', 'is_valid', 'DB_Instance', 'db_table_name', 'videos', 'video_objects', 'meta_data', 'author', 'changed_by', 'date_created', 'date_modified', 'ignored_input_fields', 'subtitles_count', 'chapters_count', 'transcript_count', 'cues_count' ))) {
        $export_data[$property] = $value;
      }
    }

    return $export_data;
  }

  /**
   * Removes the player and all of its videos, meta and video meta data from database.
   *
   * @return false|int Returns number of rows updated or false on failure.
   * @throws Exception When any of the underlying classes throws an exception.
   */
  public function delete() {
    global $wpdb;

    // load player meta data
    $meta = $this->getMetaData();
    if ($meta && count($meta)) {
      $export_data['meta'] = array();

      foreach ($meta as $meta_data) {
        // don't include edit locks
        $meta_data->delete();
      }
    }

    // load videos and meta for this player
    $videos = $this->getVideos();

    // this line will load and cache meta for all videos at once
    new FV_Player_Db_Video_Meta(null, array('id_video' => explode(',', $this->getVideoIds())), self::$DB_Instance);

    if ($videos && count($videos)) {
      foreach ($videos as $video) {
        // only delete videos which are used for this particular player and no other player
        if( $wpdb->get_var( $wpdb->prepare( "select count(*) from `{$wpdb->prefix}fv_player_players` where FIND_IN_SET( %d, videos )", $video->getId() ) ) > 1 ) {
          continue;
        }

        $video->delete();

        // load all meta data for this video
        if (self::$DB_Instance->isVideoMetaCached($video->getId())) {
          $cache = self::$DB_Instance->getVideoMetaCache();
          foreach ($cache[$video->getId()] as $meta) {
            $meta->delete();
          }
        }
      }
    }

    return $wpdb->delete(self::$db_table_name, array('id' => $this->id));
  }

}
