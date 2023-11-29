<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Bunny_Stream_Wizard extends FV_Player_Wizard_Base_Class {

  static $instance = null;

  public static function _get_instance() {
    if( !self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    parent::__construct( array(
      'id' => 'fv_player_bunny_stream_wizard',
      'page' => 'fv_player_bunny_stream',
      'steps_path' => dirname(__FILE__).'/bunny-stream-wizard',
      'title' => 'FV Player Bunny Stream Wizard'
    ) );
  }

  public function view() {
    ?>
    <style>
    /* Make sure the SVG images have some spacing and background */
    .fv-player-wizard-step img[src$="svg"] {
      background-color: white;
      padding: 1em;
      max-width: calc( 100% - 2em );
    }
    </style>
    <?php
    $this->show();

  }

}

function FV_Player_Bunny_Stream_Wizard() {
  return FV_Player_Bunny_Stream_Wizard::_get_instance();
}

FV_Player_Bunny_Stream_Wizard();