<?php

class FV_Player_Position_Save {

  public function __construct() {
    add_action( 'wp_ajax_fv_wp_flowplayer_video_position_save', array($this, 'video_position_save') );
  }

  public function video_position_save() {
    // TODO: XSS filter for POST values?
    if (isset($_POST['videoTimes']) && ($times = $_POST['videoTimes']) && count($times)) {
        $uid = get_current_user_id();
        foreach ($times as $record) {
            update_user_meta($uid, 'fv_wp_flowplayer_position_'.$record['name'], $record['position']);
        }
    }

    exit;
  }

}

$FV_Player_Position_Save = new FV_Player_Position_Save();
