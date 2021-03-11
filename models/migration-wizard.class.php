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
  
  public static function list_videos( $videos, $from, $to, $color ) {
    ?>
    <p>Videos found: <?php echo count($videos); ?></p>
    <table class="wp-list-table widefat fixed striped logentries">
      <thead>
        <tr>
          <td>Video ID</td>
          <td>URL</td>
          <td>Alternative URL</td>
          <td>Alternative URL 2</td>
          <td>Splash</td>
        </tr>
      </thead>
      <?php foreach($videos as $video) : ?>
        <tr>
          <td><?php echo $video->id ?></td>
          <?php foreach( array( 'src', 'src1', 'src2', 'splash' ) AS $field ) : ?>
            <td><?php echo self::hilight( $video->$field, $from, $to, $color ); ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </table>
    <?php
  }
  
  public static function hilight( $string, $from, $to, $color ) {
    $phrase = $to ? $to : $from;
    
    $string = str_replace( $from, '<span style="background: '.$color.'">'.$phrase.'</span>', $string );
    return $string;
  }
  
  public static function search_video( $phrase ) {
    global $wpdb;
    
    $like = '%' . $wpdb->esc_like($phrase) . '%';
    
    return $wpdb->get_results( $wpdb->prepare(
      "SELECT id, src, src1, src2, splash FROM `{$wpdb->prefix}fv_player_videos` WHERE src LIKE %s OR src1 LIKE %s OR src2 LIKE %s OR splash LIKE %s",
      $like,
      $like,
      $like,
      $like,
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