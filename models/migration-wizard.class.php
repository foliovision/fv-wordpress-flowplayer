<?php

// TODO: These should only be loaded on the wizard screen
require_once( dirname(__FILE__).'/../includes/class.fv-player-wizard-base.php' );
require_once( dirname(__FILE__).'/../includes/class.fv-player-wizard-step-base.php' );

class FV_Player_Migration_Wizard extends FV_Player_Wizard_Base_Class {

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
          <td>Player ID</td>
          <td>Video ID</td>
          <td>URL</td>
          <td>Alternative URL</td>
          <td>Alternative URL 2</td>
          <td>Splash</td>
          <td>Mobile</td>
          <td>RTMP</td>
          <td>RTMP Path</td>
        </tr>
      </thead>
      <?php foreach($videos as $video) : ?>
        <tr>
          <td><?php if( isset($video->player_id) ) echo $video->player_id ?></td>
          <td><?php echo $video->id ?></td>
          <?php foreach( array('src', 'src1', 'src2', 'splash', 'mobile', 'rtmp', 'rtmp_path' ) AS $field ) : ?>
            <td><?php echo self::hilight( $video->$field, $from, $to, $color ); ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </table>
    <?php
  }
  
  public static function list_meta_data( $videos, $from, $to, $color ) {
    ?>
    <p>Video meta found: </p>
    <table class="wp-list-table widefat fixed striped logentries">
      <thead>
        <tr>
          <td>Player ID</td>
          <td>Video ID</td>
          <td>Meta Key</td>
          <td>Meta Value</td>
        </tr>
      </thead>
      <?php foreach($videos as $video) : ?>
        <tr>
          <td><?php if( isset( $video->player_id) ) echo $video->player_id ?></td>
          <td><?php echo $video->id ?></td>
          <?php foreach( array( 'meta_key', 'meta_value' ) AS $field ) : ?>
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
    
    $videos_data = $wpdb->get_results( $wpdb->prepare(
      "SELECT id, src, src1, src2, splash, mobile, rtmp, rtmp_path  FROM `{$wpdb->prefix}fv_player_videos` WHERE src LIKE %s OR src1 LIKE %s OR src2 LIKE %s OR splash LIKE %s OR mobile LIKE %s OR rtmp LIKE %s OR rtmp_path LIKE %s",
      $like,
      $like,
      $like,
      $like,
      $like,
      $like,
      $like
    ) );

    $videos_data = self::add_player_id( $videos_data );

    return $videos_data;
  }

  public static function search_meta( $phrase ) {
    global $wpdb;
    
    $like = '%' . $wpdb->esc_like($phrase) . '%';
    
    $videos_data = $wpdb->get_results( $wpdb->prepare(
      "SELECT id_video as id , meta_key, meta_value FROM `{$wpdb->prefix}fv_player_videometa` WHERE meta_value LIKE %s AND meta_value NOT REGEXP '^(a|s|O):[0-9]:'",
      $like
    ) );

    $videos_data = self::add_player_id( $videos_data );

    return $videos_data;
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

  private static function add_player_id( $videos_data ) {
    global $wpdb;

    $players = $wpdb->get_results( "SELECT id , videos FROM `{$wpdb->prefix}fv_player_players`" );

    foreach ($videos_data as $kv => $video) {
      foreach($players as $kp => $player) {
        $videos = explode( ',', $player->videos );
        if( in_array( $video->id, $videos ) ) {
          $videos_data[$kv]->player_id = $player->id;
        }
      }
    }
  
    return $videos_data;
  }
}

function FV_Player_Migration_Wizard() {
  return FV_Player_Migration_Wizard::_get_instance();
}

FV_Player_Migration_Wizard();