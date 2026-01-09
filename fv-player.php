<?php
/*
Plugin Name: FV Player 8
Plugin URI: http://foliovision.com/player
Description: Formerly FV WordPress Flowplayer. Supports MP4, HLS, MPEG-DASH, WebM and OGV. Advanced features such as overlay ads or popups.
Version: 8.1.3
Author URI: http://foliovision.com/
Requires PHP: 5.6
Text Domain: fv-player
Domain Path: /languages
License: GPL-3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

/* FV Player - HTML5 video player
	Copyright (C) 2020  Foliovision

	This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
  return;
}

global $fv_wp_flowplayer_ver;

$fv_wp_flowplayer_ver = '8.1.3';
$fv_wp_flowplayer_core_ver = '8.1.2';

function fv_player_8_activate() {
  foreach(
    array(
      'network_activated' => is_multisite() ? wp_get_active_network_plugins() : array(),
      'standard'          => wp_get_active_and_valid_plugins()
    ) as $activation_type => $plugins
  ) {
    $pattern = '/fv-wordpress-flowplayer.*\/(flowplayer|fv-player)\.php$/';

    foreach( $plugins as $key => $plugin ) {
      if( preg_match($pattern, $plugin) ) {
        if ( !function_exists('deactivate_plugins') ) {
          require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        deactivate_plugins( $plugin, true, 'network_activated' === $activation_type );
      }
    }
  }
}

register_activation_hook(__FILE__, 'fv_player_8_activate');

function fv_player_7_deactivate_notice() {
  ?>
  <div class="notice notice-error is-dismissible">
    <p><?php _e( 'FV Player 8 cannot run together with FV Player 7. Please deactivate FV Player 7 first.', 'fv-player' ); ?></p>
  </div>
  <?php
}

function fv_player__pro_7_deactivate_notice_with_version_check() {

  global $FV_Player_Pro;
  if( isset( $FV_Player_Pro ) && ! empty( $FV_Player_Pro->version ) ) {
    if( version_compare( $FV_Player_Pro->version, '7.9' ) != -1 ) {
      return;
    }
  }

  ?>
  <div class="notice notice-error">
    <p><?php _e( 'FV Player 8 cannot run together with FV Player Pro 7. Please remove FV Player Pro 7 and then install using wp-admin -> FV Player -> Settings -> Install Pro extension.', 'fv-player' ); ?></p>
  </div>
  <?php
}

global $fv_fp;

if(
  file_exists( ABSPATH . PLUGINDIR . '/fv-wordpress-flowplayer' ) ||
  file_exists( ABSPATH . PLUGINDIR . '/fv-player-pro' ) ||
  ! empty( $fv_fp )
) {
  $self = trim( str_replace( WP_PLUGIN_DIR, '', __FILE__ ), '/' );

  foreach(
    array(
      is_multisite() ? wp_get_active_network_plugins() : array(),
      wp_get_active_and_valid_plugins()
    ) as $plugins
  ) {
    foreach( $plugins as $key => $plugin ) {
      if (
        stripos( $plugin, 'fv-wordpress-flowplayer' ) !== false && (
          stripos( $plugin, '/flowplayer.php' ) !== false ||
          stripos( $plugin, '/fv-player.php' ) !== false
        ) &&
        // FV Player 8 might be running in the old folder name as well is it the actual plugin running here?
        'fv-wordpress-flowplayer/fv-player.php' !== $self
      ) {
        add_action( 'admin_notices', 'fv_player_7_deactivate_notice' );

        // Do not load as PHP would get fatal error
        return;
      }

      if ( stripos( $plugin, '/fv-player-pro/' ) !== false || stripos( $plugin, '/fv-player-pro-7/' ) !== false ) {
        add_action( 'admin_notices', 'fv_player__pro_7_deactivate_notice_with_version_check' );

        // Continue loading as the videos are still working, at least in most cases
      }
    }
  }
}

if( file_exists( dirname( __FILE__ ) . '/includes/module.php' ) ) {
  include_once( dirname( __FILE__ ) . '/includes/module.php' );
}

include_once( dirname( __FILE__ ) . '/models/checker.php' );

global $FV_Player_Checker;
$FV_Player_Checker = new FV_Player_Checker();

include_once(dirname( __FILE__ ) . '/models/fv-player.php');
include_once(dirname( __FILE__ ) . '/models/fv-player-frontend.php');

include_once(dirname( __FILE__ ) . '/models/lightbox.php');
include_once(dirname( __FILE__ ) . '/models/facebook-share.php');

include_once(dirname( __FILE__ ) . '/models/custom-videos.php');

include_once(dirname( __FILE__ ) . '/models/seo.php');

include_once(dirname( __FILE__ ) . '/models/subtitles.php');

include_once(dirname( __FILE__ ) . '/models/widget.php');

include_once(dirname( __FILE__ ) . '/models/email-subscription.php');

include_once(dirname( __FILE__ ) . '/models/player-position-save.php');

include_once(dirname( __FILE__ ) . '/models/db-player.php');
include_once(dirname( __FILE__ ) . '/models/db-video.php');
include_once(dirname( __FILE__ ) . '/models/db-video-meta.php');
include_once(dirname( __FILE__ ) . '/models/db-player-meta.php');
include_once(dirname( __FILE__ ) . '/models/db.php');

global $FV_Player_Db;
$FV_Player_Db = new FV_Player_Db();

include_once(dirname( __FILE__ ).'/models/cdn.class.php');
include_once(dirname( __FILE__ ).'/models/digitalocean-spaces.class.php');
include_once(dirname( __FILE__ ).'/models/linode-object-storage.class.php');

include_once(dirname( __FILE__ ).'/models/learndash.php');

include_once(dirname( __FILE__ ) . '/models/list-table.php');

include_once(dirname( __FILE__ ) . '/models/xml-video-sitemap.php');

global $fv_fp;
$fv_fp = new flowplayer_frontend();

/**
 * Load back-end code if it's wp-admin, cron or if it's Gutenberg post saving.
 *
 * For the URL match we must consider:
 *
 * * /wp-json/wp/v2/posts/{post ID}
 * * /index.php?rest_route=%2Fwp%2Fv2%2Fposts%2F{post ID}
 */
if (
  wp_doing_cron() ||
  is_admin() ||
  ! empty( $_SERVER['REQUEST_METHOD'] ) && "POST" === sanitize_text_field( $_SERVER['REQUEST_METHOD'] ) && preg_match( '~/wp/v2/posts/\d+~', urldecode( sanitize_text_field( $_SERVER['REQUEST_URI'] ) ) )
) {
  include_once( dirname( __FILE__ ) . '/controller/backend.php' );
  include_once( dirname( __FILE__ ) . '/controller/editor.php' );
  include_once( dirname( __FILE__ ) . '/controller/settings.php' );
  if( version_compare(phpversion(),'5.5.0') != -1 ) {
    include_once(dirname( __FILE__ ) . '/models/media-browser.php');
  }

  if( version_compare(phpversion(),'7.4') != -1 ) {
    include_once(dirname( __FILE__ ) . '/models/media-browser-s3.php');
  }

  include_once(dirname( __FILE__ ) . '/models/system-info.php');

  include_once(dirname( __FILE__ ). '/models/conversion/conversion-base.class.php');
  include_once(dirname( __FILE__ ). '/models/conversion/shortcode2DB.class.php');
  include_once(dirname( __FILE__ ). '/models/conversion/positionsMeta2Table.php');
  include_once(dirname( __FILE__ ) . '/models/conversion.php');

  include_once( dirname( __FILE__ ) . '/view/fv-player.php' );

  register_deactivation_hook( __FILE__, 'flowplayer_deactivate' );
}

include_once( dirname( __FILE__ ) . '/controller/elementor.php' );
include_once( dirname( __FILE__ ) . '/controller/frontend.php' );
include_once( dirname( __FILE__ ) . '/controller/shortcodes.php');

include_once( dirname( __FILE__ ) . '/models/avada-builder-bridge.php' );
include_once( dirname( __FILE__ ) . '/models/gutenberg.php' );

include_once(dirname( __FILE__ ). '/models/migration-wizard.class.php');
include_once(dirname( __FILE__ ). '/models/migration-wizard.php');

include_once( dirname( __FILE__ ) .'/models/splash-download.php');

include_once(dirname( __FILE__ ) . '/models/stats.php');
include_once(dirname( __FILE__ ) . '/models/stats-export.php');

include_once(dirname( __FILE__ ) . '/models/youtube.php');

include_once(dirname( __FILE__ ) . '/models/lms-teaching.class.php');

include_once(dirname( __FILE__ ) . '/models/tutor-lms.class.php');

add_action('plugins_loaded', 'fv_player_bunny_stream_include' );

if( !function_exists( 'fv_player_bunny_stream_include' ) && version_compare(PHP_VERSION, '5.2.17') >= 0 ) {
  function fv_player_bunny_stream_include() {
    do_action( 'fv_player_load_video_encoder_libs' );
    if ( class_exists( 'FV_Player_Video_Encoder' ) ) {
      require_once( dirname( __FILE__ ).'/models/class.fv-player-bunny_stream.php' );
    }
  }
}

add_filter( 'tables_to_repair', 'fv_player_tables_to_repair' );

// Check needed because of integration tests
if ( ! function_exists( 'fv_player_tables_to_repair' ) ) {

  function fv_player_tables_to_repair( $tables ) {
    global $wpdb;

    $tables[] = FV_Player_Db_Player::get_db_table_name();
    $tables[] = FV_Player_Db_Player_Meta::get_db_table_name();
    $tables[] = FV_Player_Db_Video::get_db_table_name();
    $tables[] = FV_Player_Db_Video_Meta::get_db_table_name();
    $tables[] = FV_Player_Stats::get_table_name();
    $tables[] = $wpdb->prefix . 'fv_player_emails';
    $tables[] = $wpdb->prefix . 'fv_player_encoding_jobs';
    $tables[] = $wpdb->prefix . 'fv_fp_hls_access_tokens';

    return $tables;
  }

}
