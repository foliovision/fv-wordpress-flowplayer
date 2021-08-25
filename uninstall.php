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

  // delete transients
  delete_transient( 'fv_flowplayer_license' );
  delete_transient( 'fv_player_s3_browser_cf' );

  // delete tables
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_players" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_videos" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_videometa" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_playermeta" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_stats" );
  $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fv_player_emails" );

  // clear hooks
  wp_clear_scheduled_hook( 'fv_flowplayer_checker_event' );
  wp_clear_scheduled_hook( 'fv_player_stats' );

  // remove any transients and options we've left behind
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_fv\_%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_fv\_%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_fv\_%'" );
  $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_fv\_%'" );
  $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'fv_player_%'");
}
