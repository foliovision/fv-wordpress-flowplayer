<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/*
 *  Admin menus and such...
 */
function fv_player_admin_page() {
  include dirname( __FILE__ ) . '/../view/admin.php';
}




function fv_player_is_admin_screen() {
  if( (isset($_GET['page']) && sanitize_key( $_GET['page'] ) == 'fvplayer') || apply_filters('fv_player_is_admin_screen', false) ) {
    return true;
  }
  return false;
}




function fv_player_stats_page() {
  include dirname( __FILE__ ) . '/../view/stats.php';
}




add_action( 'fv_player_pro_video_ads_panel', 'fv_player_video_ad_stats_page' );

function fv_player_video_ad_stats_page() {
  include dirname( __FILE__ ) . '/../view/video-ad-stats.php';
}




add_filter('plugin_action_links', 'fv_wp_flowplayer_plugin_action_links', 10, 2);

function fv_wp_flowplayer_plugin_action_links($links, $file) {
  if( $file == 'fv-player/fv-player.php') {
    $settings_link = '<a href="https://foliovision.com/pro-support" target="_blank">Premium Support</a>';
    array_unshift($links, $settings_link);
    $settings_link = '<a href="admin.php?page=fvplayer">Settings</a>';
    array_unshift($links, $settings_link);
  }
  return $links;
}




add_action( 'after_plugin_row', 'fv_wp_flowplayer_after_plugin_row', 10, 3 );

function fv_wp_flowplayer_after_plugin_row( $arg) {
  if( apply_filters('fv_player_skip_ads',false) ) {
    return;
  }

  $args = func_get_args();

  if( $args[1]['Name'] == 'FV Player' ) {
    $options = get_option( 'fvwpflowplayer' );
    if( $options['key'] == 'false' || $options['key'] == '' ) :
    ?>
<tr class="plugin-update-tr fv-wordpress-flowplayer-tr">
  <td class="plugin-update colspanchange" colspan="3">
    <div class="update-message">
      <a href="https://foliovision.com/player/download">All Licenses 20% Off</a> - Easter sale!
    </div>
  </td>
</tr>
    <?php
    endif;
  }
}



/**
 * Settings metaboxes close
 */
add_filter( 'get_user_option_closedpostboxes_fv_flowplayer_settings', 'fv_wp_flowplayer_closed_meta_boxes' );

function fv_wp_flowplayer_closed_meta_boxes( $closed ) {
  if ( false === $closed ) {
    $closed = array( 'fv_flowplayer_amazon_options', 'fv_flowplayer_interface_options', 'fv_flowplayer_default_options', 'fv_flowplayer_ads', 'fv_flowplayer_integrations', 'fv_flowplayer_mobile', 'fv_flowplayer_seo', 'fv_flowplayer_privacy');
  }

  return $closed;
}

/**
 * Tools metaboxes close
 */
add_filter( 'get_user_option_closedpostboxes_fv_flowplayer_settings_tools', 'fv_flowplayer_settings_tools_closed_meta_boxes' );

function fv_flowplayer_settings_tools_closed_meta_boxes( $closed ) {
  if ( false === $closed ) {
    $closed = array( 'fv_flowplayer_conversion' );
  }

  return $closed;
}

/**
 * Skin metaboxes close
 */
add_filter( 'get_user_option_closedpostboxes_fv_flowplayer_settings_skin', 'fv_flowplayer_settings_skin_closed_meta_boxes' );

function fv_flowplayer_settings_skin_closed_meta_boxes( $closed ) {
  if ( false === $closed ) {
    global $fv_fp;
    $customCSS = $fv_fp->_get_option('customCSS');

    if( strlen( $customCSS ) === 0 ) {
      $closed = array( 'fv_flowplayer_skin_custom_css' );
    }
  }

  return $closed;
}

/*
 *  Saving settings
 */
add_action('admin_init', 'fv_player_settings_save', 9);
add_action('wp_ajax_fv_flowplayer_settings_save', 'fv_player_settings_save', 9);

function fv_player_settings_save() {

  if( isset($_POST['fv-wp-flowplayer-submit']) || isset($_POST['fv-wp-flowplayer-submit-ajax']) ) {

    if( isset($_POST['fv-wp-flowplayer-submit-ajax']) ) {
      if( wp_doing_ajax() ) {
        unset($_POST['action']);
      }

     if(! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv_flowplayer_settings_ajax_nonce'] ) ), 'fv_flowplayer_settings_ajax_nonce' ) ) {
        wp_die('Security check failed');
     }
    } else {
      check_admin_referer('fv_flowplayer_settings_nonce','fv_flowplayer_settings_nonce');
    }

    global $fv_fp;
    $fv_fp->_set_conf();
  }
}




function fv_player_handle_secrets($new, $old) {
  foreach( $new as $k => $v ) {
    if (is_array($v) && strpos($k, '_is_secret_') !== 0 && isset($old[$k]) ) {
      // recursive call for nested settings
      $v = fv_player_handle_secrets($v, $old[$k]);
      $new[$k] = $v;
    }

    if(strpos($k, '_is_secret_') === 0 ) {
      $key = str_replace('_is_secret_', '', $k);

      if( isset($old[$key]) ) {
        if(is_array($v)) { // array of values, 1 - keep original, 0 - use new
          foreach( $v as $a_k => $a_v ) {
            if( $a_v == '1' ) {
              $new[$key][$a_k] = $old[$key][$a_k];
            }
          }
        } else if($v == '1')  { // single value,  1 - keep original, 0 - use new
          $new[$key] = $old[$key];
        }
      }

      unset($new[$k]); // remove _is_secret_
    }
  }

  return $new;
}




/*
 *  Pointer boxes
 */
add_action('admin_init', 'fv_player_admin_pointer_boxes');

function fv_player_admin_pointer_boxes() {
  global $fv_fp;
  global $fv_wp_flowplayer_ver, $fv_wp_flowplayer_core_ver;

  if(
    $fv_fp->_get_option('video_position_save_enable') &&
    ! $fv_fp->_get_option('notice_user_video_positions_conversion') &&
    ( empty( $_GET['page'] ) || 'fv_player_conversion_positions_meta2table' != $_GET['page'] )
  ) {
    $fv_fp->pointer_boxes['fv_flowplayer_video_positions_conversion'] = array(
      'id' => '#wp-admin-bar-new-content',
      'pointerClass' => 'fv_flowplayer_video_positions_conversion',
      'heading' => __( 'FV Player Video Position Conversion', 'fv-player' ),
      'content' => __("<p>In this new version of FV Player the user video positions are stored in separate table. This avoids slowing down the user database (wp_usermeta).</p><p>Please run the tool to migrate the video positions to the new table.</p>"),
      'position' => array( 'edge' => 'top', 'align' => 'left' ),
      'button1' => __( 'Migrate', 'fv-player' ),
      'function1' => 'location.href = "' . admin_url('admin.php?page=fv_player_conversion_positions_meta2table') . '"',
    );
  }

  if(
    isset($fv_fp->conf['disable_videochecker']) && $fv_fp->conf['disable_videochecker'] == 'false' &&
    ( !isset($fv_fp->conf['video_checker_agreement']) || $fv_fp->conf['video_checker_agreement'] != 'true' )
  ) {
    $fv_fp->pointer_boxes['fv_flowplayer_video_checker_service'] = array(
      'id' => '#wp-admin-bar-new-content',
      'pointerClass' => 'fv_flowplayer_video_checker_service',
      'heading' => __( 'FV Player Video Checker', 'fv-player' ),
      'content' => __( "<p>FV Player includes a <a href='https://foliovision.com/player/basic-setup/how-to-use-video-checker' target='_blank'>free video checker</a> which will check your videos for any encoding errors and helps ensure smooth playback of all your videos. To work its magic, our video checker must contact our server.</p><p>Would you like to enable the video encoding checker?</p>", 'fv-player' ),
      'position' => array( 'edge' => 'top', 'align' => 'center' ),
      'button1' => __( 'Allow', 'fv-player' ),
      'button2' => __( 'Disable the video checker', 'fv-player' )
    );
  }

  if( $fv_fp->_get_option('video_sitemap') && $fv_fp->_get_option( 'ui_embed' ) && !$fv_fp->_get_option('notice_xml_sitemap_iframes') ) {
    $fv_fp->pointer_boxes['fv_flowplayer_notice_xml_sitemap_iframes'] = array(
      'id' => '#wp-admin-bar-new-content',
      'pointerClass' => 'fv_flowplayer_notice_xml_sitemap_iframes',
      'heading' => __( 'FV Player Video Sitemap coverage', 'fv-player' ),
      'content' => __( "<p>The XML Video Sitemap now includes a lot more videos as it uses the individual player iframe embed links. Until now it was only possible to put in videos using MP4 format without any kind of download protection.</p><p>Please <a href='https://foliovision.com/support/fv-wordpress-flowplayer/bug-reports#new-post' target='_blank'>let us know</a> in case you notice any issues. Your members only videos stay protected and won't open, but let us know if they appear in sitemap.</p>", 'fv-player' ).'<script>jQuery(".fv_flowplayer_notice_xml_sitemap_iframes .button2").click()</script>',
      'position' => array( 'edge' => 'top', 'align' => 'center' ),
      'button1' => __( 'Thanks for letting me know!', 'fv-player' ),
      'button2' => __( 'Go to setting', 'fv-player' ),
      'function2' => 'location.href = "'.admin_url('admin.php?page=fvplayer').'#fv_flowplayer_seo"',
    );
  }

  if( !$fv_fp->_get_option('nag_fv_player_8') ) {
    $fv_fp->pointer_boxes['fv_flowplayer_fv_player_8'] = array(
      'id' => '#wp-admin-bar-new-content',
      'pointerClass' => 'fv_flowplayer_fv_player_8',
      'heading' => __( 'FV Player 8', 'fv-player' ),
      'content' => '<p>Welcome to the brand new FV Player 8! Improvements include:</p>'.
        '<ul style="list-style: circle; padding-left: 3em;">
<li>New mobile controls</li>
<li>New editor interface</li>
<li>Proper Gutenberg block</li>
<li>Autoplay respects user scroll position</li>
<li>Two new playlist styles</li>
<li>Shortcodes and video links converted to database entries on post save</li>
<li>PageSpeed Score improvements</li>
<li>Increased compatibility and performance</li>
<li>User Video Positions stored in separate database table</li>
</ul>'.
        '<p>More information in our <a href="https://foliovision.com/player/developers/fv-player-8-changes" target="_blank">blog announcement</a>.</p>',
      'position' => array( 'edge' => 'top', 'align' => 'center' ),
      'button1' => __( 'Thanks for letting me know!', 'fv-player' ),
    );
  }
}




add_action( 'wp_ajax_fv_foliopress_ajax_pointers', 'fv_wp_flowplayer_pointers_ajax' );

function fv_wp_flowplayer_pointers_ajax() {

  if( isset($_POST['key']) && sanitize_key( $_POST['key'] ) == 'fv_flowplayer_video_checker_service' && isset($_POST['value']) ) {
    check_ajax_referer('fv_flowplayer_video_checker_service');
    $conf = get_option( 'fvwpflowplayer' );
    if( $conf ) {
      if( sanitize_key( $_POST['value'] ) == 'true' ) {
        $conf['disable_videochecker'] = 'false';
        $conf['video_checker_agreement'] = 'true';
      } else {
        $conf['disable_videochecker'] = 'true';
      }
      update_option( 'fvwpflowplayer', $conf );
    }
    die();
  }

  if( isset($_POST['key']) && sanitize_key( $_POST['key'] ) == 'fv_flowplayer_video_positions_conversion' && isset($_POST['value']) ) {
    check_ajax_referer('fv_flowplayer_video_positions_conversion');
    $conf = get_option( 'fvwpflowplayer' );
    if( $conf ) {
      $conf['notice_user_video_positions_conversion'] = sanitize_text_field( $_POST['value'] );
      update_option( 'fvwpflowplayer', $conf );
    }
    die();
  }

  $notices = array(
    'fv_flowplayer_notice_xml_sitemap_iframes' => 'notice_xml_sitemap_iframes',
    'fv_flowplayer_fv_player_8'                => 'nag_fv_player_8',
  );

  if( isset($_POST['key']) && isset($_POST['value']) && in_array($_POST['key'], array_keys($notices) ) ) {
    check_ajax_referer( sanitize_key( $_POST['key'] ) );
    $conf = get_option( 'fvwpflowplayer' );
    if( $conf ) {
      $conf[ $notices[ sanitize_key( $_POST['key'] ) ] ] = 'true';
      update_option( 'fvwpflowplayer', $conf );
    }
    die();
  }

}


/*
 *  Making sure FV Player appears properly on settings screen
 */
add_action('admin_enqueue_scripts', 'fv_flowplayer_admin_scripts');

function fv_flowplayer_admin_scripts() {
  global $fv_wp_flowplayer_ver, $fv_fp;

  if( fv_player_is_admin_screen() ) {
    wp_enqueue_media();

    wp_enqueue_script('wp-theme-plugin-editor');
    wp_enqueue_style('wp-codemirror');

    wp_enqueue_script('common');
    wp_enqueue_script('wp-lists');
    wp_enqueue_script('postbox');

    wp_enqueue_script('jquery-minicolors', flowplayer::get_plugin_url().'/js/jquery-minicolors/jquery.minicolors.min.js',array('jquery'), $fv_wp_flowplayer_ver );
    wp_enqueue_script('fv-player-admin', flowplayer::get_plugin_url().'/js/admin.js',array('jquery','jquery-minicolors'), filemtime( (__DIR__).'/../js/admin.js' ), true );
    wp_localize_script( 'fv-player-admin', 'fv_player_admin', array(
      'css_logo_positions' => $fv_fp->css_logo_positions,
    ) );

    wp_enqueue_script('fv-player-settings', flowplayer::get_plugin_url().'/js/settings.js',array('jquery'), filemtime( (__DIR__).'/../js/settings.js' ), true );

    if( function_exists('wp_enqueue_code_editor') ) {
      wp_localize_script('fv-player-admin', 'cm_settings', wp_enqueue_code_editor(array('type' => 'text/css')) );
    }
  }
}




add_action( 'admin_enqueue_scripts', 'fv_player_settings_styles' );

function fv_player_settings_styles() {
  if( !fv_player_is_admin_screen() ) return;

  global $fv_wp_flowplayer_ver;
  wp_enqueue_style('fv-player-admin', flowplayer::get_plugin_url().'/css/license.css',array(), $fv_wp_flowplayer_ver );
  wp_enqueue_style('jquery-minicolors', flowplayer::get_plugin_url().'/js/jquery-minicolors/jquery.minicolors.css',array(), $fv_wp_flowplayer_ver );
}

add_action('admin_footer', 'flowplayer_admin_footer');

function flowplayer_admin_footer() {
  if( !fv_player_is_admin_screen() ) return;

  flowplayer_prepare_scripts();
  flowplayer_display_scripts();
}

function fv_player_get_aws_regions() {

  return array(
    'af-south-1'     => __('Africa (Cape Town)', 'fv-player' ),
    'ap-east-1'      => __('Asia Pacific (Hong Kong)', 'fv-player' ),
    'ap-south-2'     => __('Asia Pacific (Hyderabad)', 'fv-player' ),
    'ap-southeast-3' => __('Asia Pacific (Jakarta)', 'fv-player' ),
    'ap-southeast-5' => __('Asia Pacific (Malaysia)', 'fv-player' ),
    'ap-southeast-4' => __('Asia Pacific (Melbourne)', 'fv-player' ),
    'ap-south-1'     => __('Asia Pacific (Mumbai)', 'fv-player' ),
    'ap-northeast-3' => __('Asia Pacific (Osaka)', 'fv-player' ),
    'ap-northeast-2' => __('Asia Pacific (Seoul)', 'fv-player' ),
    'ap-southeast-1' => __('Asia Pacific (Singapore)', 'fv-player' ),
    'ap-southeast-2' => __('Asia Pacific (Sydney)', 'fv-player' ),
    'ap-northeast-1' => __('Asia Pacific (Tokyo)', 'fv-player' ),

    'ca-central-1'   => __('Canada (Central)', 'fv-player' ),
    'ca-west-1'      => __('Canada West (Calgary)', 'fv-player' ),

    'cn-north-1'     => __('China (Beijing)', 'fv-player' ),
    'cn-northwest-1' => __('China (Ningxia)', 'fv-player' ),

    'eu-central-1'   => __('Europe (Frankfurt)', 'fv-player' ),
    'eu-west-1'      => __('Europe (Ireland)', 'fv-player' ),
    'eu-west-2'      => __('Europe (London)', 'fv-player' ),
    'eu-south-1'     => __('Europe (Milan)', 'fv-player' ),
    'eu-west-3'      => __('Europe (Paris)', 'fv-player' ),
    'eu-south-2'     => __('Europe (Spain)', 'fv-player' ),
    'eu-north-1'     => __('Europe (Stockholm)', 'fv-player' ),
    'eu-central-2'   => __('Europe (Zurich)', 'fv-player' ),

    'il-central-1'   => __('Israel (Tel Aviv)', 'fv-player' ),

    'me-south-1'     => __('Middle East (Bahrain)', 'fv-player' ),
    'me-central-1'   => __('Middle East (UAE)', 'fv-player' ),

    'sa-east-1'      => __('South America (S&atilde;o Paulo)', 'fv-player' ),

    'us-west-1'      => __('US West (N. California)', 'fv-player' ),
    'us-east-1'      => __('US East (N. Virginia)', 'fv-player' ),
    'us-east-2'      => __('US East (Ohio)', 'fv-player' ),
    'us-west-2'      => __('US West (Oregon)', 'fv-player' )

  );
}
