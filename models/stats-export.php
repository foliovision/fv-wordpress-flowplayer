<?php


if( !class_exists('FV_Player_Stats_Export') ) :

class FV_Player_Stats_Export {

  public function __construct() {
    if( isset( $_GET['fv-stats-export-user']) ) {
      add_action('admin_init', array( $this, 'export_user_data' ) );
    }
  }

  public function export_user_data() {
    if( isset($_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'fv-stats-export-user-' . intval($_GET['fv-stats-export-user']) ) ) {

      if( !current_user_can('manage_options') ) return;

      global $wpdb;

      $user_id = intval($_GET['fv-stats-export-user']);

      $query = $wpdb->prepare( "SELECT user_email, date, pl.id AS player_id, src, post_title, play, seconds, duration
        FROM `{$wpdb->prefix}fv_player_stats` AS s
        JOIN `{$wpdb->users}` AS u ON s.user_id = u.ID
        JOIN `{$wpdb->posts}` AS p ON s.id_post = p.ID
        JOIN `{$wpdb->prefix}fv_player_videos` AS v ON s.id_video = v.id
        JOIN `{$wpdb->prefix}fv_player_players` AS pl ON FIND_IN_SET( v.id, pl.videos )
        WHERE u.ID = %d
        ORDER BY s.id DESC",
      $user_id);

      $results = $wpdb->get_results( $query , ARRAY_A);

      $this->serve_csv($results, $user_id);
    }

  }

  private function serve_csv( $data, $user_id ) {
    $user = get_user_by('id', $user_id);

    $user_email = $user->user_email;

    $header = array('Email', 'Date', 'Player-ID', 'Video-URL', 'Video-Title', 'Play', 'Seconds', 'Duration');
    $filename = 'fv-player-stats-export-' . $user_email .'-' . date('Y-m-d') . '.csv';

    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $header);

    foreach( $data as $row ) {
      fputcsv($fp, $row);
    }

    fclose($fp);
    die();
  }

}

global $FV_Player_Stats_Export;
$FV_Player_Stats_Export = new FV_Player_Stats_Export();

endif;
