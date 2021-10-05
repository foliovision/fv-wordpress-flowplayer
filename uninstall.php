<?php

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

$options = get_option( 'fvwpflowplayer', array() );

if( isset($options['remove_all_data']) && filter_var($options['remove_all_data'], FILTER_VALIDATE_BOOLEAN) ) {

  // delete options
  delete_option( 'fvwpflowplayer' );
  delete_option( 'fvwpflowplayer_core_ver' );
  delete_option( 'fv_flowplayer_extension_install' );
  delete_option( 'fv_wordpress_flowplayer_deferred_notices' );
  delete_option( 'fv_wordpress_flowplayer_persistent_notices' );
  delete_option( 'fv_player_email_lists' );
  delete_option( 'fv_player_mailchimp_time' );
  delete_option( 'fv_player_mailchimp_lists' );
  delete_option( 'fv_flowplayer_checker_queue' );
  delete_option( 'fv_player_popups' );
  delete_option( 'fv_preview_hls' );
  delete_option( 'fv_flowplayer_ppv' );
  delete_option( 'fv_flowplayer_vast' );

  // delete transients
  delete_transient( 'fv_flowplayer_license' );
  delete_transient( 'fv_player_s3_browser_cf' );

  // delete tables
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_players" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_videos" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_videometa" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_playermeta" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_stats" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_drm_logs" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_emails" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_encoding_jobs" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_user_playlist" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_user_playlist_video" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_fp_hls_access_tokens" );

  // clear hooks
  wp_clear_scheduled_hook( 'fv_flowplayer_checker_event' );
  wp_clear_scheduled_hook( 'fv_player_stats' );
  wp_clear_scheduled_hook( 'fv_player_pro_update_cloudflare_ips' );
  wp_clear_scheduled_hook( 'fv_player_pro_clear_cache' );
  wp_clear_scheduled_hook( 'fv_player_pro_update_vimeo_cache' );
  wp_clear_scheduled_hook( 'fv_player_pro_update_youtube_cache' );
  wp_clear_scheduled_hook( 'fv_player_pro_update_transcript_cache' );
  wp_clear_scheduled_hook( 'fv_player_pro_stream_loader_clear_log' );

  // remove any transients and options we've left behind
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_fv\_player%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_fv\_player%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_fv\_player%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_fv\_player%'" );

  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_fv-player%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_fv-player%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_fv-player%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_fv-player%'" );

  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'fv\_player\_%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'fv-player-%'" );

  // delete plugin created folders
  if ( WP_Filesystem() ) {
    global $wp_filesystem;

    if( $wp_filesystem->exists( $wp_filesystem->wp_content_dir().'fv-flowplayer-custom/' ) ) {
      $wp_filesystem->rmdir( $wp_filesystem->wp_content_dir().'fv-flowplayer-custom/', true );
    }

    if( $wp_filesystem->exists( $wp_filesystem->wp_content_dir().'fv-player-tracking/' ) ) {
      $wp_filesystem->rmdir( $wp_filesystem->wp_content_dir().'fv-player-tracking/', true );
    }
  }
}
