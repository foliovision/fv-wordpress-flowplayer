<?php

class FV_Player_System_Info {

  public function __construct() {
    add_action( 'admin_init', array($this, 'admin__add_meta_boxes') );
    
    if( !empty($_GET['fv-email-export']) && !empty($_GET['page']) && $_GET['page'] === 'fvplayer'){
      add_action('admin_init', array( $this, 'csv_export' ) );
    }

  }

  public function admin__add_meta_boxes() {    
    add_meta_box('fv_flowplayer_system_information', __('System Info', 'fv-wordpress-flowplayer'), array($this, 'settings_box'), 'fv_flowplayer_settings_help', 'normal');
  }
  
  public function settings_box () {
    global $wpdb, $fv_wp_flowplayer_ver, $fv_wp_flowplayer_core_ver, $FV_Player_Pro, $FV_Player_VAST, $FV_Player_PayPerView, $FV_Player_Video_Intelligence;
    
    if ( get_bloginfo( 'version' ) < '3.4' ) {
      $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
      $theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
    } else {
      $theme_data = wp_get_theme();
      $theme      = $theme_data->Name . ' ' . $theme_data->Version;
    }

    // Try to identifty the hosting provider
    $host = false;
    if( defined( 'WPE_APIKEY' ) ) {
      $host = 'WP Engine';
    } elseif( defined( 'PAGELYBIN' ) ) {
      $host = 'Pagely';
    }
    ?>
<textarea readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'edd' ); ?>">
### Begin System Info ###

## Please include this information when posting support requests ##

Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

FV Player version:        <?php echo $fv_wp_flowplayer_ver . "\n"; ?>
FV Player core version:   <?php echo $fv_wp_flowplayer_core_ver . "\n"; ?>
FV Player license:        <?php $license = get_transient('fv_flowplayer_license'); if( $license && isset($license->valid) && $license->valid ) echo "Valid\n"; ?>

<?php if( isset($FV_Player_Pro) ) : ?>
FV Player Pro version:    <?php if( isset($FV_Player_Pro->version) ) echo $FV_Player_Pro->version."\n"; ?>
FV Player Pro license:    <?php $license = get_transient('fv-player-pro_license'); if( $license && isset($license->valid) && $license->valid ) echo "Valid\n"; ?>
<?php endif; ?>
<?php if( isset($FV_Player_VAST) ) : ?>
FV Player VAST version:   <?php if( isset($FV_Player_VAST->version) ) echo $FV_Player_VAST->version."\n"; ?>
FV Player VAST license:   <?php $license = get_transient('fv-player-vast_license'); if( $license && isset($license->valid) && $license->valid ) echo "Valid\n"; ?>
<?php endif; ?>
<?php if( isset($FV_Player_PayPerView) ) : ?>
FV Player PPV version:    <?php if( isset($FV_Player_PayPerView->version) ) echo $FV_Player_PayPerView->version."\n"; ?>
FV Player PPV license:    <?php $license = get_transient('fv-player-pay-per-view_license'); if( $license && isset($license->valid) && $license->valid ) echo "Valid\n"; ?>
<?php endif; ?>
<?php if( isset($FV_Player_Video_Intelligence) ) : ?>
FV Player vi version:     <?php if( isset($FV_Player_Video_Intelligence->version) ) echo $FV_Player_Video_Intelligence->version."\n"; echo "\n"; ?>
<?php endif; ?>

WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $theme . "\n"; ?>
<?php if( $host ) : ?>
Host:                     <?php echo $host . "\n"; ?>
<?php endif; ?>

Browser:                  <?php echo $_SERVER['HTTP_USER_AGENT']; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); echo mysqli_get_server_info($connection) . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

WordPress Memory Limit:   <?php echo ( edd_let_to_num( WP_MEMORY_LIMIT )/( 1024 ) )."MB"; ?><?php echo "\n"; ?>
PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; ?>
PHP Arg Separator:        <?php echo ini_get( 'arg_separator.output' ) . "\n"; ?>
PHP Allow URL File Open:  <?php echo ini_get( 'allow_url_fopen' ) ? "Yes" : "No\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.'; ?><?php echo "\n"; ?>
SOAP Client:              <?php echo ( class_exists( 'SoapClient' ) ) ? 'Your server has the SOAP Client enabled.' : 'Your server does not have the SOAP Client enabled.'; ?><?php echo "\n"; ?>
SUHOSIN:                  <?php echo ( extension_loaded( 'suhosin' ) ) ? 'Your server has SUHOSIN installed.' : 'Your server does not have SUHOSIN installed.'; ?><?php echo "\n"; ?>

ACTIVE PLUGINS:

<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
// If the plugin isn't active, don't show it.
if ( ! in_array( $plugin_path, $active_plugins ) )
continue;

echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}

if ( is_multisite() ) :
?>

NETWORK ACTIVE PLUGINS:

<?php
$plugins = wp_get_active_network_plugins();
$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

foreach ( $plugins as $plugin_path ) {
$plugin_base = plugin_basename( $plugin_path );

// If the plugin isn't active, don't show it.
if ( ! array_key_exists( $plugin_base, $active_plugins ) )
continue;

$plugin = get_plugin_data( $plugin_path );

echo $plugin['Name'] . ' :' . $plugin['Version'] ."\n";
}

endif;

?>

SETTINGS

<?php
$conf = get_option('fvwpflowplayer');
foreach( $conf AS $k => $v ) {
  if( stripos($k,'nonce') !== false ) unset($conf[$k]);
}

if( isset($conf['amazon_key']) && count($conf['amazon_key']) > 0 ) $conf['amazon_key'] = '(redacted)';
if( isset($conf['amazon_secret']) && count($conf['amazon_secret']) > 0 ) $conf['amazon_secret'] = '(redacted)';

if( isset($conf['pro']) ) {
  if( !empty($conf['pro']['cf_key_id']) ) $conf['pro']['cf_key_id'] = '(redacted)';
  if( !empty($conf['pro']['cf_pk']) ) $conf['pro']['cf_pk'] = '(redacted)';
  
  if( !empty($conf['pro']['elastic_key']) ) $conf['pro']['elastic_key'] = '(redacted)';
  if( !empty($conf['pro']['elastic_secret']) ) $conf['pro']['elastic_secret'] = '(redacted)';
  
  foreach( $conf['pro'] AS $k => $v ) {
    if( stripos($k,'secure_token') !== false ) $conf['pro'][$k] = '(redacted)';
  }
  
}

if( isset($conf['addon-video-intelligence']) && !empty($conf['addon-video-intelligence']['jwt']) ) {
  $conf['addon-video-intelligence']['jwt'] = '(redacted)';
}


print_r( $conf );
?>

DATABASE

<?php
global $wpdb;
foreach( array( 'fv_player_players', 'fv_player_playermeta', 'fv_player_videos', 'fv_player_videometa' ) AS $table) {
  $res = $wpdb->get_row( "SHOW CREATE TABLE {$wpdb->prefix}{$table}", ARRAY_A );
  if( isset($res['Create Table']) ) {
    echo $res['Create Table']."\n\n";
  } else {
    echo $table." not found!\n";
  }
}
?>

### End System Info ###
</textarea>
    <?php
    die();
  }

}

global $FV_Player_System_Info;
$FV_Player_System_Info = new FV_Player_System_Info();
