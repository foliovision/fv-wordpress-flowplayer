<?php

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

$options = get_option('fvwpflowplayer');

if( isset($options['remove_all_data']) && $options['remove_all_data'] == 'true' ) {

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

}
