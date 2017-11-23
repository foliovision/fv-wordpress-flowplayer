<?php

class FV_Player_Position_Save {

  public function __construct() {
    add_action( 'wp_ajax_fv_wp_flowplayer_video_position_save', array($this, 'video_position_save') );
    add_filter('fv_player_item', array($this, 'set_last_position'), 10, 1 );
  }

  private function get_extensionless_file_name($path) {
      return pathinfo($path, PATHINFO_FILENAME);
  }

  public function set_last_position($aItemArray) {
    // we only use the first source to check for stored position,
    // since other sources would be alternatives (in quality, etc.)
    if (
        is_array($aItemArray) &&
        isset($aItemArray['sources']) &&
        isset($aItemArray['sources'][0]) &&
        ($metaPosition = get_user_meta( get_current_user_id(), 'fv_wp_flowplayer_position_' . $this->get_extensionless_file_name($aItemArray['sources'][0]['src']), true ))
    ) {
        $aItemArray['sources'][0]['position'] = $metaPosition;
    }

    return $aItemArray;
  }

  public function video_position_save() {
    // TODO: XSS filter for POST values?
    if (isset($_POST['videoTimes']) && ($times = $_POST['videoTimes']) && count($times)) {
        $uid = get_current_user_id();
        foreach ($times as $record) {
            update_user_meta($uid, 'fv_wp_flowplayer_position_'.$this->get_extensionless_file_name($record['name']), $record['position']);
        }
    }

    exit;
  }

}

$FV_Player_Position_Save = new FV_Player_Position_Save();
