<?php

require_once( dirname(__FILE__, 2).'/includes/class.fv-player-wizard-base.php' );
require_once( dirname(__FILE__, 2).'/includes/class.fv-player-wizard-step-base.php' );

class FV_Player_Migration_Wizard extends FV_Player_Wizard_Base {

  static $instance = null;

  private $dos_check_result = false;

  public static function _get_instance() {
    if( !self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function __construct() {
    parent::__construct( array(
      'id' => 'fv_player_migration_wizard',
      'page' => 'fv_player_migration',
      'steps_path' => dirname(__FILE__).'/migration-wizard',
      'title' => 'FV Player Migration Wizard'
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

function FV_Player_Migration_Wizard() {
  return FV_Player_Migration_Wizard::_get_instance();
}

FV_Player_Migration_Wizard();