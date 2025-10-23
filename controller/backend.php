<?php

/*  FV Player - HTML5 video player
    Copyright (C) 2013  Foliovision

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
  exit;
}

/*
 *  Video Checker support email
 */

add_action('wp_ajax_fv_wp_flowplayer_support_mail', 'fv_wp_flowplayer_support_mail');

function fv_wp_flowplayer_support_mail() {
  if( isset( $_POST['notice'] ) && ! empty( $_POST['nonce'] && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_player_frontend'  ) ) ) {

  	$current_user = wp_get_current_user();
    $content = "<h1>Admin: " . esc_html( sanitize_text_field( $_POST['status'] ) ) . "</h1>\n";
  	$content .= '<p>User: '.$current_user->display_name." (".$current_user->user_email.")</p>\n";
  	$content .= '<p>User Agent: ' . esc_html( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) ) . "</p>\n";
  	$content .= '<p>Referer: ' . esc_url( sanitize_url( $_SERVER['HTTP_REFERER'] ) ) . "</p>\n";
  	$content .= "<p>Comment:</p>\n" . wpautop( esc_html( sanitize_textarea_field( $_POST['comment'] ) ) );
  	$notice = str_replace( '<span class="value"', ': <span class="value"', wp_kses_post( $_POST['notice'] ) );
  	$notice .= str_replace( '<span class="value"', ': <span class="value"', wp_kses_post( $_POST['details'] ) );

  	$content .= "<p>Video analysis:</p>\n".$notice;

    global $fv_wp_flowplayer_support_mail_from, $fv_wp_flowplayer_support_mail_from_name;

    //$headers = "Reply-To: \"$current_user->display_name\" <$current_user->user_email>\r\n";
    $fv_wp_flowplayer_support_mail_from_name = $current_user->display_name;
    $fv_wp_flowplayer_support_mail_from = $current_user->user_email;

  	add_filter( 'wp_mail_content_type', 'fv_wp_flowplayer_support_mail_content_type' );

  	//add_action('phpmailer_init', 'fv_wp_flowplayer_support_mail_phpmailer_init' );
  	wp_mail( 'fvplayer@foliovision.com', 'FV Player Quick Support Submission', $content );

  	wp_send_json_success();

  } else {
    wp_send_json_error();
  }
}

function fv_wp_flowplayer_support_mail_content_type() {
  return 'text/html';
}

function fv_wp_flowplayer_support_mail_phpmailer_init( $phpmailer ) {
	global $fv_wp_flowplayer_support_mail_from, $fv_wp_flowplayer_support_mail_from_name;

	if( $fv_wp_flowplayer_support_mail_from_name ) {
		$phpmailer->FromName = trim( $fv_wp_flowplayer_support_mail_from_name );
	}
	if( $fv_wp_flowplayer_support_mail_from ) {
		if( strcmp( trim($phpmailer->From), trim($fv_wp_flowplayer_support_mail_from) ) != 0 && !trim($phpmailer->Sender) ) {
			$phpmailer->Sender = trim($phpmailer->From);
		}
		$phpmailer->From = trim( $fv_wp_flowplayer_support_mail_from );
	}

}




/*
 *  Activating Extensions
 */
add_action('wp_ajax_fv_wp_flowplayer_activate_extension', 'fv_wp_flowplayer_activate_extension');

function fv_wp_flowplayer_activate_extension() {
  check_ajax_referer( 'fv_wp_flowplayer_activate_extension', 'nonce' );
  if( !isset( $_POST['plugin'] ) ) {
    die();
  }

  $activate = activate_plugin( sanitize_text_field( $_POST['plugin'] ) );
  if ( is_wp_error( $activate ) ) {
    echo "<FVFLOWPLAYER>".wp_json_encode( array( 'message' => $activate->get_error_message(), 'error' => $activate->get_error_message() ) )."</FVFLOWPLAYER>";
    die();
  }

  echo "<FVFLOWPLAYER>".wp_json_encode( array( 'message' => 'Success!', 'plugin' => esc_html( sanitize_textarea_field( $_POST['plugin'] ) ) ) )."</FVFLOWPLAYER>";
  die();
}




/*
 *  Template Check
 */
add_action('wp_ajax_fv_wp_flowplayer_check_template', 'fv_wp_flowplayer_check_template');

function fv_wp_flowplayer_check_template() {

  if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'fv_wp_flowplayer_check_template' ) ) {
    $output = array( 'errors' => array( 'Nonce verification failed' ), 'ok' => false );
    echo '<FVFLOWPLAYER>' . wp_json_encode( $output ) . '</FVFLOWPLAYER>';
    die();
  }

	$ok = array();
	$errors = array();

  /**
   * The HTTP request below will have no login cookies, so lets make sure the nonce is created for non-logged in user.
   */
  wp_set_current_user( 0 );
  unset( $_COOKIE[ LOGGED_IN_COOKIE ] );

  $response = wp_remote_get(
    add_query_arg( 'fv_wp_flowplayer_check_template', wp_create_nonce( 'fv_wp_flowplayer_check_template' ), home_url() )
  );

  $headers = ! is_wp_error( $response ) ?  wp_remote_retrieve_headers( $response ) : array();

  if( is_wp_error( $response ) ) {
    $error_message = $response->get_error_message();
    $output = array( 'errors' => $error_message);
  } else if ($response['response']['code'] == 401){
    $errors[] = 'You are using HTTP auth, we cannot check template.';
    $output = array( 'errors' => $errors);

  } else if (
    absint( $response['response']['code'] ) === 403 &&
    'cloudflare' === strtolower( $headers['server'] ) &&
    ! empty( $headers['cf-mitigated'] ) &&
    'challenge' === $headers['cf-mitigated']
  ) {
    $errors[] = 'Cloudflare is blocking our access to your website, we cannot check the template.';
    $output = array( 'ok' => $errors);

  } else {

    $active_plugins = get_option( 'active_plugins' );
    foreach( $active_plugins AS $plugin ) {
      if( stripos( $plugin, 'wp-minify' ) !== false ) {
        $errors[] = "You are using <strong>WP Minify</strong>, so the script checks would not be accurate. Please check your videos manually.";
        $output = array( 'errors' => $errors, 'ok' => $ok/*, 'html' => $response['body'] */);
        echo '<FVFLOWPLAYER>'.wp_json_encode($output).'</FVFLOWPLAYER>';
        die();
      }
    }

    $combine_js_warning = false;

    if( function_exists( 'w3_instance' ) && $minify = w3_instance('W3_Plugin_Minify') ) {
      if( $minify->_config->get_boolean('minify.js.enable') ) {
        $errors[] = "You are using <strong>W3 Total Cache</strong> with JS Minify enabled. The template check might not be accurate. Please check your videos manually.";

        $combine_js_warning = true;
      }
    }

    if( class_exists( 'SiteGround_Optimizer\Options\Options' ) && method_exists( 'SiteGround_Optimizer\Options\Options', 'is_enabled' ) ) {

      // Avoiding PHP 5.2 lint errors
      if( call_user_func_array( array( 'SiteGround_Optimizer\Options\Options', 'is_enabled' ), array( 'siteground_optimizer_combine_javascript' ) ) ) {
        $errors[] = "You are using <strong>SiteGround Optimizer</strong> with \"Combine JavaScript Files\" enabled. The template check might not be accurate. Please check your videos manually.";

        $combine_js_warning = true;
      }
    }

    $ok[] = __( 'We also recommend you to open any of your videos on your site and see if you get a red warning message about JavaScript not working.', 'fv-player' );

    $response['body'] = preg_replace( '$<!--[\s\S]+?-->$', '', $response['body'] );	//	handle HTML comments

    //	check Flowplayer scripts
    preg_match_all( '!<script[^>]*?src=[\'"]([^\'"]*?freedomplayer[0-9.-]*?(?:\.min)?\.js[^\'"]*?)[\'"][^>]*?>\s*?</script>!', $response['body'], $freedomplayer_scripts );
    if( count($freedomplayer_scripts[1]) > 0 ) {
      if( count($freedomplayer_scripts[1]) > 1 ) {
        $errors[] = "It appears there are <strong>multiple</strong> FreedomPlayer scripts on your site, your videos might not be playing, please check. There might be some other plugin adding the script.";
      }
      foreach( $freedomplayer_scripts[1] AS $flowplayer_script ) {
        $check = fv_wp_flowplayer_check_script_version( $flowplayer_script );
        if( $check == - 1 ) {
          $errors[] = "Flowplayer script <code>$flowplayer_script</code> is old version and won't play. You need to get rid of this script.";
        } else if( $check == 1 ) {
          $ok[] = __( 'FV Player script found: ', 'fv-player' ) . "<code>$flowplayer_script</code>!";
          $fv_flowplayer_pos = strpos( $response['body'], $flowplayer_script );
        } else if( $check == 0 ) {
          $errors[] = "<p>It appears there are <strong>stripping the query string versions</strong> as <code>$flowplayer_script</code> appears without the plugin version number.</p><p>Some site speed analysis tools recommend doing so, but it means you loose control over what version of plugin files is cached (in users' browsers and on CDN). That way users hang on to the old plugin files and might experience visual or functional issues with FV Player (and any other plugin).</p><p>You can read all the details in our article: <a href='https://foliovision.com/2017/06/wordpress-cdn-best-practices' target='_blank'>How to use WordPress with CDN<a>.</p>";
        }
      }
    } else if( !$combine_js_warning && count($freedomplayer_scripts[1]) < 1 ) {
      $errors[] = "It appears there are <strong>no</strong> FreedomPlayer scripts on your site, your videos might not be playing, please check. Check your template's header.php file if it contains wp_head() function call and footer.php should contain wp_footer()!";
    }


    //	check jQuery scripts
    preg_match_all( '!<script[^>]*?src=[\'"]([^\'"]*?/jquery[0-9.-]*?(?:\.min)?\.js[^\'"]*?)[\'"][^>]*?>\s*?</script>!', $response['body'], $jquery_scripts );
    if( count($jquery_scripts[1]) > 0 ) {
      foreach( $jquery_scripts[1] AS $jkey => $jquery_script ) {
        $ok[] = __( 'jQuery library found: ', 'fv-player' ) . "<code>$jquery_script</code>!";
        $jquery_pos = strpos( $response['body'], $jquery_script );
      }

      if( count($jquery_scripts[1]) > 1 ) {
        $errors[] = "It appears there are <strong>multiple</strong> jQuery libraries on your site, your videos might not be playing or may play with defects, please check.\n";
      }
    } else if( count($jquery_scripts[1]) < 1 ) {
      $errors[] = "It appears there are <strong>no</strong> jQuery library on your site, your videos might not be playing, please check.\n";
    }


    if( $fv_flowplayer_pos > 0 && $jquery_pos > 0 && $jquery_pos > $fv_flowplayer_pos && count($jquery_scripts[1]) < 1 ) {
      $errors[] = "It appears your Flowplayer JavaScript library is loading before jQuery. Your videos probably won't work. Please make sure your jQuery library is loading using the standard Wordpress function - wp_enqueue_scripts(), or move it above wp_head() in your header.php template.";
    }

    // check if Permissions-Policy header is set and has autoplay=() in it
    if( isset($headers['permissions-policy']) && strpos( $headers['permissions-policy'], 'autoplay=()' ) !== false ) {
      $errors[] = sprintf(
        __( 'You are using Permissions-Policy HTTP header to block video autoplay. This will force muted playback of YouTube videos too and viewers will have to un-mute the videos manually: <code>%s</code>', 'fv-player' ),
        esc_html( $headers['permissions-policy'] )
      );
    } else if ( isset($headers['permissions-policy'] ) ) {
      $ok[] = sprintf(
        __( 'You are using Permissions-Policy HTTP header to adjust the autoplay permissions: <code>%s</code>', 'fv-player' ),
        esc_html( $headers['permissions-policy'] )
      );
    } else {
      $ok[] = __( 'You are not using Permissions-Policy HTTP header to adjust the autoplay permissions.', 'fv-player' );
    }

    $output = array( 'errors' => $errors, 'ok' => $ok/*, 'html' => $response['body'] */);
  }
  echo '<FVFLOWPLAYER>'.wp_json_encode($output).'</FVFLOWPLAYER>';
  die();
}

//	enter script URL, return false if it's not version 5
function fv_wp_flowplayer_check_script_version( $url ) {
	$url_mod = preg_replace( '!\?.+!', '', $url );
	if( preg_match( '!flowplayer-([\d\.]+)!', $url_mod, $version ) && $version[1] ) {
		if( strpos( $version[1], '5' ) !== 0 ) {
			return -1;
		}
	}

	global $fv_wp_flowplayer_ver;
	if( strpos( $url, '/freedom-video-player/fv-player.min.js?ver='.$fv_wp_flowplayer_ver ) !== false ) {
		return 1;
  }

  // when using Google PageSpeed module
  if( strpos( $url, '/freedom-video-player/fv-player.min.js,qver='.$fv_wp_flowplayer_ver ) !== false ) {
    return 1;
  }

  // when using SCRIPT_DEBUG
  if( strpos( $url, '/freedom-video-player/freedomplayer.min.js?ver=' ) !== false ) {
		return 1;
  }

  // when using SCRIPT_DEBUG with Google PageSpeed module
  if( strpos( $url, '/freedom-video-player/freedomplayer.min.js,qver=' ) !== false ) {
    return 1;
  }

	return 0;
}

function fv_wp_flowplayer_check_jquery_version( $url, &$array, $key ) {
	$url_mod = preg_replace( '!\?.+!', '', $url );
	if( preg_match( '!(\d+.[\d\.]+)!', $url_mod, $version ) && $version[1] ) {
		if( version_compare($version[1], '1.7.1') == -1 ) {
			return -1;
		} else {
			return 1;
		}
	}

	//	if jQuery is in the Wordpress install, we know that the ?ver= says what version it is
	if( strpos( $url, site_url().'/wp-includes/js/jquery/jquery.js' ) !== false ) {
		if( preg_match( '!(\d+.[\d\.]+)!', $url, $version ) && $version[1] ) {
			if( version_compare($version[1], '1.7.1') == -1 ) {
				return -1;
			} else {
				return 1;
			}
		}
	}

	return 0;
}




/*
 *  Check video files
 */
add_action('wp_ajax_fv_wp_flowplayer_check_files', 'fv_wp_flowplayer_check_files');

function fv_wp_flowplayer_check_files() {

  if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'fv_wp_flowplayer_check_files' ) ) {
    $output = array( 'errors' => array( 'Nonce verification failed' ), 'ok' => false );
    echo '<FVFLOWPLAYER>' . wp_json_encode( $output ) . '</FVFLOWPLAYER>';
    die();
  }

  global $wpdb;

  $bNotDone = false;
  $tStart = microtime(true);
  $tMax = ( @ini_get('max_execution_time') ) ? @ini_get('max_execution_time') - 5 : 25;

  $videos1 = $wpdb->get_results( "SELECT ID, post_content FROM $wpdb->posts WHERE post_type != 'revision' AND post_status != 'trash' AND post_content LIKE '%[flowplayer %'" );
  $videos2 = $wpdb->get_results( "SELECT ID, post_content FROM $wpdb->posts WHERE post_type != 'revision' AND post_status != 'trash' AND post_content LIKE '%[fvplayer %'" );

  $videos = array_merge( $videos1, $videos2 );

  $source_servers = array();

  $shortcodes_count = 0;
  $src_count = 0;
  if( count($videos) ) {
    foreach( $videos AS $post ) {

      $shortcodes_count += preg_match_all( '!\[(?:flowplayer|fvplayer)[^\]]+\]!', $post->post_content, $post_videos );
      if( count($post_videos[0]) ) {
        foreach( $post_videos[0] AS $post_video ) {
          $post_video = preg_replace( '!popup=\'.*\'!', '', $post_video );
          $src_count += preg_match_all( '!(?:src|src1|src2|src3|mp4|webm|ogv)=[\'"](.*?(?:mp4|m4v))[\'"]!', $post_video, $sources1 );
          $src_count += preg_match_all( '!(?:src|src1|src2|src3|mp4|webm|ogv)=([^\'"].*?(?:mp4|m4v|flv))[\s\]]!', $post_video, $sources2 );
          $sources = array_merge( $sources1[1], $sources2[1] );
          if( count($sources) ) {
            foreach($sources AS $src ) {
              if( strpos( $src, '//' ) === 0 ) {
                $src = 'http:'.$src;
              } else if( strpos( $src, '/' ) === 0 ) {
                $src = home_url().$src;
              }

              $server = preg_replace( '!(.*?//.*?)/.+!', '$1', $src );

              $source_servers[$server][] = array( 'src' => $src, 'post_id' => $post->ID );
            }
          }
        }
      }

    }
  }

  $ok = array();
  $errors = array();

  $count = 0;
  foreach( $source_servers AS $server => $videos ) {

    $tCurrent = microtime(true);
    if( $tCurrent - $tStart > $tMax ) {
      $bNotDone = true;
      break;
    }

    if( stripos( $videos[0]['src'], '.mp4' ) === FALSE /*&& stripos( $videos[0]['src'], '.m4v' ) === FALSE*/ ) {
      continue;
    }

    global $FV_Player_Checker;

    if( stripos( trim($videos[0]['src']), 'rtmp://' ) === false ) {
      list( $header, $message_out ) = $FV_Player_Checker->http_request( trim($videos[0]['src']), array( 'quick_check' => 10, 'size' => 65536 ) );
      if( $header ) {
        $headers = WP_Http::processHeaders( $header );
        list( $new_errors, $mime_type, $fatal ) = $FV_Player_Checker->check_headers( $headers, trim($videos[0]['src']), wp_rand(0,999), array( 'talk_bad_mime' => 'Server <code>'.$server.'</code> uses incorrect mime type for MP4 ', 'wrap' => false ) );
        if( $fatal ) {
          continue;
        }
        if( $new_errors ) {
          $sPostsLinks = false;
          foreach( $videos AS $video ) {
            $sPostsLinks .= '<a href="'.home_url().'?p='.$video['post_id'].'">'.$video['post_id'].'</a> ';
          }
          $errors[] = implode( " ",$new_errors ).'(<a href="#" onclick="jQuery(\'#fv-flowplayer-warning-'.$count.'\').toggle(); return false">click to see a list of posts</a>) <div id="fv-flowplayer-warning-'.$count.'" style="display: none; ">'.$sPostsLinks.'</div>';
          $count++;
          continue;
        } else {
          $ok[] = 'Server <code>'.$server.'</code> appears to serve correct mime type <code>'.$mime_type.'</code> for MP4 videos.';
        }
      }
    }
  }

  if( $bNotDone ) {
    $ok[] = '<strong>Not all the servers were checked as you use a lot of them, increase your PHP execution time or check your other videos by hand.</strong>';
  }

  $output = array( 'errors' => $errors, 'ok' => $ok/*, 'html' => $response['body'] */);
  echo '<FVFLOWPLAYER>'.wp_json_encode($output).'</FVFLOWPLAYER>';
  die();
}




/*
 *  Apply Pro Upgrade button
 */
add_action('wp_ajax_fv_wp_flowplayer_check_license', 'fv_wp_flowplayer_check_license');

function fv_wp_flowplayer_check_license() {
  if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'fv_wp_flowplayer_check_license' ) ) {
    $output = array( 'errors' => array( 'Nonce verification failed' ), 'ok' => false );

  } else if( fv_wp_flowplayer_admin_key_update() ) {
    $output = array( 'errors' => false, 'ok' => array(__( 'License key acquired successfully. <a href="">Reload</a>', 'fv-player' )) );
    fv_wp_flowplayer_install_extension();
  } else {
    $message = get_option('fv_wordpress_flowplayer_deferred_notices');
    if( !$message ) $message = get_option('fv_wordpress_flowplayer_persistent_notices');
    $output = array( 'errors' => array($message), 'ok' => false );
  }
  echo '<FVFLOWPLAYER>'.wp_json_encode($output).'</FVFLOWPLAYER>';
  die();
}



/*
 *  Run this when new version is installed
 */
add_action('admin_init', 'fv_player_admin_update');

function fv_player_admin_update() {
  global $fv_fp, $fv_wp_flowplayer_ver;

  $aOptions = get_option( 'fvwpflowplayer' );
  if( !isset($aOptions['version']) || version_compare( $fv_wp_flowplayer_ver, $aOptions['version'] ) ) {
    do_action( 'fv_player_update' ); // trigger update actions

    //update_option( 'fv_wordpress_flowplayer_deferred_notices', 'FV Flowplayer upgraded - please click "Check template" and "Check videos" for automated check of your site at <a href="'.site_url().'/wp-admin/admin.php?page=fvplayer">the settings page</a> for automated checks!' );

    if( !empty($aOptions['version']) && $aOptions['version'] == '6.0.5.20' && $aOptions['playlist_advance'] == 'true' ) { //  version 6.0.5 used reverse logic for this option!
      $aOptions['playlist_advance'] = false;
      $fv_fp->_get_conf();
    }

    if ( ! isset( $aOptions['autoplay_preload'] ) ) {
      if( $fv_fp->_get_option( 'autoplay' ) || $fv_fp->_get_option( array( 'pro' ,'autoplay_scroll' )) ) {
        $aOptions['autoplay_preload'] = 'viewport';
      } else {
        $aOptions['autoplay_preload'] = false;
      }
    }

    if( empty($aOptions["interface"]['playlist_titles']) && !empty($aOptions["interface"]["playlist_captions"]) ) {
      $aOptions["interface"]['playlist_titles'] = $aOptions["interface"]["playlist_captions"];
    }

    foreach( array(
      'ad'            => 'overlay',
      'ad_css'        => 'overlay_css',
      'ad_height'     => 'overlay_height',
      'ad_show_after' => 'overlay_show_after',
      'ad_width'      => 'overlay_width',
      'adTextColor'   => 'overlayTextColor',
      'adLinksColor'  => 'overlayLinksColor'
    ) as $from => $to ) {
      if( empty($aOptions[ $to ]) && !empty($aOptions[ $from ]) ) {
        $aOptions[ $to ] = $aOptions[ $from ];
      }
    }

    $aOptions['version'] = $fv_wp_flowplayer_ver;
    update_option( 'fvwpflowplayer', $aOptions );

    fv_wp_flowplayer_pro_settings_update_for_lightbox();
    $fv_fp->css_writeout();

    fv_wp_flowplayer_delete_extensions_transients();
    delete_option('fv_flowplayer_extension_install');
  }

  if( isset($_POST['fv-player-pro-release']) && isset($_POST['fv_player_pro_switch']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv_player_pro_switch'] ) ), 'fv_player_pro_switch') ) {
    $fv_fp->css_writeout();
  }
}

function fv_wp_flowplayer_pro_settings_update_for_lightbox(){
  global $fv_fp;
  if(isset($fv_fp->conf['pro']) && isset($fv_fp->conf['pro']['interface']['lightbox']) && $fv_fp->conf['pro']['interface']['lightbox'] == true ){
    $fv_fp->conf['interface']['lightbox'] = true;
    $fv_fp->conf['pro']['interface']['lightbox'] = false;
    $options = get_option('fvwpflowplayer');
    unset($options['pro']['interface']['lightbox']);
    $options['interface']['lightbox'] = true;
    update_option('fvwpflowplayer', $options);
  }
  if(isset($fv_fp->conf['pro']) && isset($fv_fp->conf['pro']['lightbox_images']) && $fv_fp->conf['pro']['lightbox_images'] == true ){
    $fv_fp->conf['lightbox_images'] = true;
    $fv_fp->conf['pro']['lightbox_images'] = false;
    $options = get_option('fvwpflowplayer');
    unset($options['pro']['lightbox_images']);
    $options['lightbox_images'] = true;
    update_option('fvwpflowplayer', $options);
  }

}

function fv_wp_flowplayer_delete_extensions_transients( $delete_delay = false ){
  $aTransientsLike = array('fv-player_license','fv-player-pro_license','fv-player-vast_license','fv-player-pro_fp-private-updates','fv-player-vast_fp-private-updates');

  global $wpdb;
  $aWhere = array();
  foreach( $aTransientsLike AS $sKey ) {
    $aWhere[] = $wpdb->prepare( 'option_name LIKE %s', '%' . $wpdb->esc_like( $sKey) . '%' );
  }
  $sWhere = implode(" OR ", $aWhere);

  $aOptions = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%_transient_fv%' AND ( {$sWhere} )" );

  foreach( $aOptions AS $sKey ) {
    if( !$delete_delay ){
      delete_transient( str_replace('_transient_','',$sKey) );
    } else {
      fv_wp_flowplayer_change_transient_expiration( str_replace('_transient_','',$sKey), $delete_delay );
    }
  }

  $aUpdates = get_site_transient('update_plugins');
  set_site_transient('update_plugins', $aUpdates );

}


add_action('admin_init', 'fv_player_lchecks');

function fv_player_lchecks() {
  $aCheck = get_transient( 'fv-player_license' );

  if( isset($_REQUEST['nonce_fv_player_pro_install']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce_fv_player_pro_install'] ) ), 'fv_player_pro_install') ) {

    if ( ! $aCheck || empty( $aCheck->valid ) ) {
      global $fv_fp, $fv_wp_flowplayer_ver;
      $fv_fp->strPluginSlug = 'fv-player';
      $fv_fp->version = $fv_wp_flowplayer_ver;
      $fv_fp->license_key = 'activation';

      $fv_fp->check_license( true );

      $aCheck = get_transient( 'fv-player_license' );
    }

    if( isset($aCheck->valid) && $aCheck->valid){
      fv_wp_flowplayer_install_extension('fv_player_pro');

    } else if ( ! empty( $aCheck->message ) ) {
      add_action( 'admin_notices', 'fv_player_install_pro_license_notice' );
    }
  }

  if( isset($aCheck->valid) && $aCheck->valid){
    delete_option('fv_wordpress_flowplayer_persistent_notices');
  }
}

function fv_wp_flowplayer_admin_key_update() {
	global $fv_fp;

	$data = fv_wp_flowplayer_license_check( array('action' => 'key_update') );
	if( isset($data->domain) ) {  //  todo: test
		if( $data->domain && $data->key && stripos( home_url(), $data->domain ) !== false ) {
			$fv_fp->conf['key'] = $data->key;
			update_option( 'fvwpflowplayer', $fv_fp->conf );
			update_option( 'fvwpflowplayer_core_ver', flowplayer::get_core_version() );

      fv_wp_flowplayer_change_transient_expiration("fv-player_license",5);
      fv_wp_flowplayer_delete_extensions_transients(5);
			return true;
		}
	} else if( isset($data->expired) && $data->expired && isset($data->message) ){
    update_option( 'fv_wordpress_flowplayer_persistent_notices', $data->message );
    update_option( 'fvwpflowplayer_core_ver', flowplayer::get_core_version() );
    return false;
	} else {
    update_option( 'fv_wordpress_flowplayer_deferred_notices', 'FV Player License upgrade failed - please check if you are running the plugin on your licensed domain or download FV Player Pro on <a href="https://foliovision.com/my-licenses" target="_blank">Your Foliovison.com Licenses page</a>' );
    update_option( 'fvwpflowplayer_core_ver', flowplayer::get_core_version() );
		return false;
	}
}

function fv_wp_flowplayer_license_check( $aArgs ) {
	global $fv_wp_flowplayer_ver;

	$args = array(
		'body' => array( 'plugin' => 'fv-wordpress-flowplayer', 'version' => $fv_wp_flowplayer_ver, 'core_ver' => flowplayer::get_core_version(), 'type' => home_url(), 'action' => $aArgs['action'], 'admin-url' => admin_url() ),
		'timeout' => 10,
		'user-agent' => 'fv-wordpress-flowplayer-'.$fv_wp_flowplayer_ver.' ('.flowplayer::get_core_version().')'
	);
	$resp = wp_remote_post( 'https://license.foliovision.com/?fv_remote=true', $args );

  if( !is_wp_error($resp) && isset($resp['body']) && $resp['body'] && $data = json_decode( preg_replace( '~[\s\S]*?<FVFLOWPLAYER>(.*?)</FVFLOWPLAYER>[\s\S]*?~', '$1', $resp['body'] ) ) ) {
    return $data;

  } else if( is_wp_error($resp) ) {
    $args['sslverify'] = false;
    $resp = wp_remote_post( 'https://license.foliovision.com/?fv_remote=true', $args );

    if( !is_wp_error($resp) && isset($resp['body']) && $resp['body'] && $data = json_decode( preg_replace( '~[\s\S]*?<FVFLOWPLAYER>(.*?)</FVFLOWPLAYER>[\s\S]*?~', '$1', $resp['body'] ) ) ) {
      return $data;
    }

  }

  return false;
}

function fv_wp_flowplayer_change_transient_expiration( $transient_name, $time ){
  $transient_val = get_transient($transient_name);
  if( $transient_val ){
    set_transient($transient_name,$transient_val,$time);
    return true;
  }
  return false;
}

add_action('admin_notices', 'fv_wordpress_flowplayer_expired_license_update_notice');

function fv_wordpress_flowplayer_expired_license_update_notice() {
  if( get_current_screen()->base === 'update-core' && get_option('fv_wordpress_flowplayer_expired_license_update_notice') ) {
    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'To update FV Player please either renew your license or disable FV Player Pro.', 'fv-player' ) . '</p></div>';
  }
}

add_action( 'after_plugin_row_fv-player/fv-player.php', 'fv_wordpress_flowplayer_expired_license_update_plugin_row', 0, 3 );

function fv_wordpress_flowplayer_expired_license_update_plugin_row($plugin_file, $plugin_data, $status) {
  if( get_option('fv_wordpress_flowplayer_expired_license_update_notice') ) {
    echo '<tr class="plugin-update-tr active" style="position: relative; top: -1px"><td colspan="4" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>' . esc_html__('To update FV Player please either renew your license or disable FV Player Pro.', 'fv-player') . '</p></div></td></tr>';
  }
}


add_action('wp_ajax_flowplayer_conversion_script', 'flowplayer_conversion_script');

function flowplayer_conversion_script() {
  global $wpdb;

  $posts = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type != 'revision'");

  $old_shorttag = '[flowplayer';
  $new_shorttag = '[fvplayer';
  $counter = 0;

  echo '<ol>';
  foreach($posts as $fv_post) {
    if ( stripos( $fv_post->post_content, $old_shorttag ) !== false ) {
      $update_post = array();
      $update_post['ID'] = $fv_post->ID;
      $update_post['post_content'] = str_replace( $old_shorttag, $new_shorttag, $fv_post->post_content );
      wp_update_post( $update_post );
      echo '<li><a href="' . get_permalink($fv_post->ID) . '">' . get_the_title($fv_post->ID) . '</a> updated</li>';
      $counter++;
    }
  }
  echo '</ol>';

  echo '<strong>Conversion was succesful. Total number of converted posts: ' . intval( $counter ) . '</strong>';

  delete_option('fvwpflowplayer_conversion');

  die();
}




add_action('admin_notices', 'fv_wp_flowplayer_admin_notice');

function fv_wp_flowplayer_admin_notice() {
	if( $notices = get_option('fv_wordpress_flowplayer_deferred_notices') ) {
  	echo '<div class="updated inline">
       			<p>'.$notices.'</p>
    			</div>';
    delete_option('fv_wordpress_flowplayer_deferred_notices');
  }

  $conversion = false; //(bool)get_option('fvwpflowplayer_conversion');
  if ($conversion ) {
    echo '<div class="updated" id="fvwpflowplayer_conversion_notice"><p>';
    printf(
      wp_kses( __( 'FV Player has found old shortcodes in the content of your posts. <a href="%1$s">Run the conversion script.</a>', 'fv-player' ), array( 'a' => array( 'href' => array() ) ) ),
      get_admin_url() . 'admin.php?page=fvplayer');
    echo "</p></div>";
  }
}







/*
 *  Check the extension info from plugin license transient and activate the plugin
 */
function fv_wp_flowplayer_install_extension( $plugin_package = 'fv_player_pro' ) {

  $aInstalled = get_option( 'fv_flowplayer_extension_install', array() );
  $aInstalled = array_merge( $aInstalled, array( $plugin_package => false ) );
  update_option('fv_flowplayer_extension_install', $aInstalled );

  $aPluginInfo = get_transient( 'fv-player_license' );

  if ( ! $aPluginInfo || empty( $aPluginInfo->valid ) ) {
    global $fv_fp, $fv_wp_flowplayer_ver;
    $fv_fp->strPluginSlug = 'fv-player';
    $fv_fp->version = $fv_wp_flowplayer_ver;
    $fv_fp->license_key = 'activation';

    $fv_fp->check_license( true );

    $aPluginInfo = get_transient( 'fv-player_license' );
  }

  $plugin_basename = $aPluginInfo->{$plugin_package}->slug;
  $download_url = $aPluginInfo->{$plugin_package}->url;

  $result = FV_Wordpress_Flowplayer_Plugin_Private::install_plugin(
    "FV Player Pro",
    $plugin_package,
    $plugin_basename,
    $download_url,
    admin_url('admin.php?page=fvplayer&reload='.wp_rand()),
    'fv_wordpress_flowplayer_deferred_notices',
    'fv_player_pro_install'
  );

  $aInstalled = ( get_option('fv_flowplayer_extension_install' ) ) ? get_option('fv_flowplayer_extension_install' ) : array();
  $aInstalled = array_merge( $aInstalled, array( $plugin_package => $result ) );
  update_option('fv_flowplayer_extension_install', $aInstalled );
}




function flowplayer_deactivate() {
  if ( WP_Filesystem() ) {
    global $wp_filesystem;

    if( $wp_filesystem->exists( $wp_filesystem->wp_content_dir().'fv-player-tracking/' ) ) {
      $wp_filesystem->rmdir( $wp_filesystem->wp_content_dir().'fv-player-tracking/', true );
    }

    if( $wp_filesystem->exists( $wp_filesystem->wp_content_dir().'fv-flowplayer-custom/' ) ) {
      $wp_filesystem->rmdir( $wp_filesystem->wp_content_dir().'fv-flowplayer-custom/', true );
    }
  }

  delete_option( 'fv_flowplayer_extension_install' );

  wp_clear_scheduled_hook( 'fv_flowplayer_checker_event' );
  wp_clear_scheduled_hook( 'fv_player_stats' );
}




/*
 *  DB based player data saving
 */
global $FV_Player_Db;

// these have to be here, as using them in constructor doesn't work
add_filter('heartbeat_received', array($FV_Player_Db, 'check_db_edit_lock'), 10, 2);


add_action( 'admin_notices', 'fv_player_embedded_on_fix' );

function fv_player_embedded_on_fix() {
  if( current_user_can('install_plugins') && isset($_GET['action']) && sanitize_text_field( $_GET['action'] ) == 'fv-player-embedded-on-fix' && !empty($_REQUEST['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'fv-player-embedded-on-fix' ) ) {

    global $wpdb;
    $players_with_no_posts = $wpdb->get_col( "SELECT p.id FROM {$wpdb->prefix}fv_player_players AS p LEFT JOIN {$wpdb->prefix}fv_player_playermeta AS m ON p.id = m.id_player AND m.meta_key = 'post_id' OR m.id IS NULL WHERE m.id IS NULL" );

    echo "<h2>FV Player Embedded On Post Scan</h2>\n";

    echo '<p>' . sprintf( 'It appears there are %d players which do not belong to any post.', count( $players_with_no_posts ) ) . "</p>\n";

    if ( $players_with_no_posts ) {
      foreach ( $players_with_no_posts as $player_id ) {
        echo '<p>';
        echo 'Player #' . intval( $player_id ) . '... ';

        $wild = '%';
        $find = 'fvplayer id="'.$player_id.'"';
        $like = $wild . $wpdb->esc_like( $find ) . $wild;

        $posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status != 'trash' AND post_type != 'revision' AND post_content LIKE %s", $like ) );

        $post_meta = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta AS m JOIN $wpdb->posts AS p ON m.post_id = p.ID WHERE post_status != 'trash' AND post_type != 'revision' AND meta_value LIKE %s", $like ) );

        $posts = array_merge( $posts, $post_meta );

        $posts = array_unique( $posts );

        if ( count($posts) > 0 ) {

          echo "Found in posts: " . implode( ', ', $posts ) . "\n";

          foreach ( $posts AS $post_id ) {
            $meta = new FV_Player_Db_Player_Meta(null, array(
              'id_player' => $player_id,
              'meta_key' => 'post_id',
              'meta_value' => $post_id
            ) );

            $meta->save();
          }

        } else {
          echo "Not found in any post.\n";

        }

        echo "</p>\n";
      }
    }

    die( 'Done!' );

  }
}


add_action( 'admin_notices', 'fv_player_rollback' );

function fv_player_rollback() {
  if( current_user_can('install_plugins') && isset($_GET['action']) && sanitize_text_field( $_GET['action'] ) == 'fv-player-rollback' && !empty($_REQUEST['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'fv-player-rollback' ) ) {

    ob_start(); // first check if we can perform the update automatically!
    $creds = request_filesystem_credentials( admin_url(), '', false, false, array() );
    if( !WP_Filesystem($creds) ) { // if not, then don't try to do it at all
      ob_get_clean();
      echo "<div class='error'><p>Unfortunately rollback is not supported as your site can't install plugins without FTP. Please login to your Foliovision.com account and download the previous plugin version there using the \"Show Previous Version\" button.</p></div>";
      return;
    }

    echo ob_get_clean();

    global $fv_fp, $fv_wp_flowplayer_ver;
    $fv_fp->pointer_boxes = array(); // no pointer boxes here!

    $plugin_slug = false;
    $active_plugins = get_option( 'active_plugins' );
    foreach( $active_plugins AS $plugin ) {
      if( stripos($plugin,'fv-player') === 0 && stripos($plugin,'/fv-player.php') !== false ) {
        $plugin_slug = $plugin;
      }
    }

    $plugin_transient 	= get_site_transient( 'update_plugins' );
    $plugin_folder    	= plugin_basename( dirname( $plugin_slug ) );
    $plugin_file      	= basename( $plugin_slug );
    $version            = isset($_GET['version']) ? sanitize_text_field( $_GET['version'] ) : '6.6.6';
    $url 				        = 'https://downloads.wordpress.org/plugin/fv-player.'.$version.'.zip';
    $temp_array 		= array(
      'slug'        => $plugin_folder,
      'new_version' => $version,
      'url'         => 'https://foliovision.com',
      'package'     => $url,
    );

    $temp_object = (object) $temp_array;
    $plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $temp_object;
    set_site_transient( 'update_plugins', $plugin_transient );

    add_filter( 'upgrader_pre_download', 'fv_player_rollback_message' );

    require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
    $title = 'FV Player Rollback';
    $nonce = 'upgrade-plugin_' . $plugin_slug;
    $url = 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin_slug );
    $upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
    $upgrader = new Plugin_Upgrader( $upgrader_skin );
    $upgrader->upgrade( $plugin_slug );

    include( ABSPATH . 'wp-admin/admin-footer.php' );

    delete_option('fv-player-pro-release');


    $active_plugins = get_option( 'active_plugins' );
    foreach( $active_plugins AS $plugin ) {
      if( stripos( $plugin, 'fv-player-pro' ) === 0 ) {
        delete_plugins( array($plugin) ); // deleting the FV Player Pro plugin here means that he FV Player activation process in the iframe will already re-install it in the iframe, so in the iframe you will get the FV Player settings screen!
      }
    }

    wp_die( '', 'FV Player Rollback', array( 'response' => 200 ) );

  }
}

function fv_player_rollback_message( $val ) {
  echo "<div class='updated'>";
  echo "<p>Please wait until the plugin download and reactivation is completed.</p>";
  if( flowplayer::is_licensed() ) {
    echo "<p>We also rollback the FV Player Pro plugin in the process.</p>";
    if( class_exists('FV_Player_Pro') ) echo "<style>#wpbody-content iframe[title=\"Update progress\"] { display: none; }</style>";
  }
  echo "</div>";
  return $val;
}

add_action( 'admin_notices', 'fv_player_pro_version_check' );

function fv_player_pro_version_check() {
  $version = '7.5.0.727';

  global $FV_Player_Pro;

  if( !empty($FV_Player_Pro) && !fv_player_extension_version_is_min($version,'pro') ) :
  ?>
  <div class="error">
      <p><?php printf( esc_html__(  'FV Player: Please upgrade to FV Player Pro version %s or above!', 'fv-player' ), $version ); ?></p>
  </div>
  <?php
  endif;
}

add_action( 'admin_notices', 'fv_player_pay_per_view_version_check' );

function fv_player_pay_per_view_version_check() {
  $version = '7.5.3.727';

  global $FV_Player_PayPerView;

  if( !empty($FV_Player_PayPerView) && !fv_player_extension_version_is_min($version,'ppv') ) :
  ?>
  <div class="error">
      <p><?php printf( esc_html__(  'FV Player: Please upgrade to FV Player Pay Per View version %s or above!', 'fv-player' ), $version ); ?></p>
  </div>
  <?php
  endif;
}

add_action( 'admin_notices', 'fv_player_pay_per_view_woocommerce_version_check' );

function fv_player_pay_per_view_woocommerce_version_check() {
  $version = '7.5.3.727';

  global $FV_Player_PayPerView_WooCommerce;

  if( !empty($FV_Player_PayPerView_WooCommerce) && !fv_player_extension_version_is_min($version,'ppv-woocommerce') ) :
  ?>
  <div class="error">
      <p><?php printf( esc_html__(  'FV Player: Please upgrade to FV Player Pay Per View for WooCommerce version %s or above!', 'fv-player' ), $version ); ?></p>
  </div>
  <?php
  endif;
}

// lazy-load of video encoder libraries
add_action( 'fv_player_load_video_encoder_libs', 'fv_player_load_video_encoder_libs' );
function fv_player_load_video_encoder_libs() {
  include_once( dirname( __FILE__ ).'/../models/video-encoder/video-encoder.php');
  require_once( dirname(__FILE__).'/../includes/class.fv-player-wizard-base.php' );
  require_once( dirname(__FILE__).'/../includes/class.fv-player-wizard-step-base.php' );
}

/**
 * Attachment edit - show attached posts
 */
add_action( 'attachment_submitbox_misc_actions', 'fv_player_submitbox_misc_actions' );

function fv_player_submitbox_misc_actions( $attachment ) {
  global $pagenow, $typenow;

  // We only want to run the code on a specific page
  if( $pagenow != 'post.php' || $typenow != 'attachment' ) {
    return;
  }

  global $fv_fp;

  $video_id = get_post_meta( $attachment->ID, 'fv_player_video_id', true );

  if( !empty($video_id) ) {
    $players = $fv_fp->get_players_by_video_ids( $video_id );

    // Iterate players and create html with name and link to player
    foreach( $players as $player ) {
      ?>
        <div class="misc-pub-section misc-pub-attachment">
          FV Player splash screen: <strong><a href="<?php echo esc_url( admin_url( 'admin.php?page=fv_player&id='. $player->getId() ) ); ?>"><?php echo esc_html( $player->getPlayerNameWithFallback() ); ?></a></strong>
        </div>
      <?php
    }
  }
}

/**
 * Append or update player row in wp-admin -> FV Player
 */
add_action('wp_ajax_fv_player_table_new_row', 'fv_player_table_new_row');

function fv_player_table_new_row() {
  if( isset($_POST['playerID']) && isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), "fv-player-table_new_row_nonce" ) ) {
    $table = new FV_Player_List_Table( array(
        'player_id' => intval($_POST['playerID']),
        'per_page' => 25,
        'screen' => 'toplevel_page_fv_player'
      )
    );

    $table->prepare_items();
    $table->views();
    $table->advanced_filters();
    $table->display();
    exit;
  }
}

/**
 * Update the player on the wp-admin -> Posts, Pages or CPT screen
 *
 * Gets the first video thumbnail only
 */
add_action('wp_ajax_fv_player_edit_posts_cell', 'fv_player_edit_posts_cell');

function fv_player_edit_posts_cell() {
  if( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), "fv-player-edit_posts_cell_nonce" ) ) {

    $player_id = false;

    // New player, load from post meta field to ensure it saved
    if ( ! empty( $_POST['post_id'] ) && ! empty( $_POST['meta_key'] ) ) {
      $shortcode = get_post_meta( absint( $_POST['post_id'] ), sanitize_key( $_POST['meta_key'] ), true );
      $shortcode_atts = shortcode_parse_atts( trim( $shortcode, ']' ) );

      if( ! empty($shortcode_atts['id']) ) {
        $player_id = $shortcode_atts['id'];
      }

    // Existing player, load by id
    } else if ( ! empty( $_POST['playerID'] ) ) {
      $player_id = absint( $_POST['playerID'] );
    }

    if ( $player_id ) {
      global $FV_Player_Db;
      $aPostListPlayers = $FV_Player_Db->getListPageData( array(
        'player_id' => $player_id
      ) );

      if( !empty($aPostListPlayers[0]->thumbs[0]) ) {
        echo '<a href="#" class="fv-player-edit" data-player_id="' . intval($player_id) . '">' . $aPostListPlayers[0]->thumbs[0] . '</a>';

      } else {
        echo "Error: Player not found!";
      }
    }

    exit;
  }
}

/**
 * Get taxonomies registered for the post type. The core WordPress function
 * to do this does not return taxonomy if it's enabled for multiple
 * post types. So we do it properly here.
 *
 * @param  string $post_type Post type to check
 * @return array             Taxonomy slugs
 */
function fv_player_get_post_type_taxonomies( $post_type ) {
  $taxonomies = get_taxonomies( array(
    'public'      => true,
    'show_ui'     => true,
  ), 'objects' );

  $post_type_taxonomies = array();
  foreach( $taxonomies AS $taxonomy) {
    if( in_array( $post_type, $taxonomy->object_type ) ) {
      $post_type_taxonomies[] = $taxonomy->name;
    }
  }

  return $post_type_taxonomies;
}

function fv_player_setup_uninstall_script( $put_in_uninstall_php, $remove_uninstall_php ) {

  if ( 'direct' !== get_filesystem_method( array(), dirname( dirname( __FILE__ ) ) ) ) {
    return false;
  }

  if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
  }

  $plugin_folder = basename( dirname( dirname( __FILE__ ) ) );

  $wp_filesystem = new WP_Filesystem_Direct( '' );
  $original_file = $wp_filesystem->wp_plugins_dir() . $plugin_folder . '/uninstall-script.php';
  $new_file      = $wp_filesystem->wp_plugins_dir() . $plugin_folder . '/uninstall.php';

  if ( $put_in_uninstall_php && ! $wp_filesystem->exists( $new_file ) ) {
    return $wp_filesystem->copy( $original_file, $new_file );

  } else if ( $remove_uninstall_php && $wp_filesystem->exists( $new_file ) ) {
    return $wp_filesystem->delete( $new_file );
  }

  return false;
}

function fv_player_install_pro_license_notice() {
  $aCheck = get_transient( 'fv-player_license' );
  if ( ! empty( $aCheck->message ) ) {
    echo "<div class='error'>" . wp_kses( $aCheck->message, 'post' ) . "</div>";
  }
}

/**
 * Paid FV Player extensions need to know what's the FV Player version.
 */
add_filter( 'http_request_args', 'fv_player_license_checks_base_plugin_version', 10, 2 );

function fv_player_license_checks_base_plugin_version( $parsed_args, $url ) {

  global $fv_wp_flowplayer_ver;
  if ( ( stripos( $url, '//foliovision.com/' ) !== false || stripos( $url, '.foliovision.com/' ) !== false ) && stripos( $url, 'fv_remote' ) !== false ) {
    if ( is_array( $parsed_args['body'] ) ) {
      $parsed_args['body']['fv_player_core_version'] = $fv_wp_flowplayer_ver;

    // No POST? It seems this adds to the URL query string which we need to actual plugin update downloads.
    } else if ( empty( $parsed_args['body'] ) ) {
      $parsed_args['body'] = array( 'fv_player_core_version' => $fv_wp_flowplayer_ver );
    }
  }

  return $parsed_args;
}
