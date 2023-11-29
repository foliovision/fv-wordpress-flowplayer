<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_LMS_Teaching {
  private $is_enabled = false;

  function __construct() {
    add_action( 'plugins_loaded', array( $this, 'loader' ), 11 );
  }

  function loader() {
    add_filter( 'fv_player_item', array( $this, 'check_meta' ), 11, 3 );
    add_filter( 'fv_flowplayer_attributes', array( $this, 'edit_attributes' ), 11, 3 );
  }

  function check_meta( $aItem, $index, $aArgs ) {
    global $fv_fp;

    // shortcode args
    if( isset( $aArgs['lms_teaching '] ) ) {
      if( $aArgs['lms_teaching '] == 'yes' || $aArgs['lms_teaching '] == 'true' ) {
        $this->is_enabled = true;
      } else {
        $this->is_enabled = false;
      }
    } else {

      $meta_setting = 'default'; // setting for specific player
      if ($fv_fp->current_player() && count($fv_fp->current_player()->getMetaData())) {
        foreach ($fv_fp->current_player()->getMetaData() as $meta_object) {
          if( strcmp( $meta_object->getMetaKey(), 'lms_teaching' ) == 0 ) {
            $meta_setting = $meta_object->getMetaValue();
          }
        }
      }

      if( $meta_setting == 'true' ) {
        $this->is_enabled = true;
      } else {
        $this->is_enabled = false;
      }
    }

    return $aItem;
  }

  function edit_attributes( $attributes, $media, $fv_fp ) {
    if( $this->is_enabled && is_user_logged_in() ) {
      $attributes['data-lms_teaching'] = true;

      // if( strpos( $attributes['class'], 'no-controlbar' ) == false ) {
      //   $attributes['class'] .= ' no-controlbar';
      // }

    }

    return $attributes;
  }

}

new FV_Player_LMS_Teaching;
