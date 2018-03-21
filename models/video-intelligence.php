<?php

class FV_Player_video_intelligence_Installer {

  var $notice = false;
  var $notice_status = false;

  function __construct() {
    add_action( 'admin_menu', array( $this, 'start' ), 8 ) ;
    add_action( 'admin_init', array( $this, 'settings_register' ) ) ;
    add_action( 'admin_notices', array( $this, 'show_notice' ) );
  }

  function settings() {
    $option = get_option('fv_player_video_intelligence');
    wp_nonce_field('fv_player_vi_install','_wpnonce_fv_player_vi_install');
    ?>
        <table class="form-table2" style="margin: 5px; ">
          <tbody>
            <tr>
              <td style="width: 25%">
                <img src="<?php echo flowplayer::get_plugin_url(); ?>/images/vi-logo.svg" alt="video intelligence logo" style="width: 95%" />
              </td>
              <td>
                <p><strong>vi stories</strong> creates in-stream ad opportunities on your site</p>
                <ul>
                  <li>Introduce contextual video content that your users are interested in</li>
                  <li>Create a great user experience and longer time-on-site</li>
                  <li>Monetize with in-stream video ads at a high CPM</li>
                  <li>Relevant, topical videos</li>
                </ul>                
              </td>
            </tr>
            <?php if( !class_exists('FV_Player_Video_Intelligence') ) : ?>
              <tr>
                <td></td>
                <td>
                  <?php if( $option && !empty($option['jwt']) && !empty($option['time']) && ( $option['time'] + 30 * 24 * 3600 ) > time() ) : ?>
                    <p>We found an existing video intelligence token. Click below to install FV Player video intelligence plugin.</p> <input type="submit" name="fv-player-vi-install" value="<?php _e('Install', 'fv-wordpress-flowplayer'); ?>" class="button">
                  <?php else : ?>
                    <?php $current_user = wp_get_current_user(); ?>
                    <a href="http://vi.ai/publisher-video-monetization/?aid=foliovision&email=<?php echo urlencode($current_user->user_email); ?>&url=<?php echo home_url(); ?>&invtype=3#publisher_signup" target="_blank" class="button">Register</a>                  
                    <p>Once you complete the signup above, please enter your login information below. FV Player doesn't store your login information, only the auth token (valid for 30 days) is stored.</p>
                  <?php endif; ?>
                </td>
              </tr>
              <?php if( !$option || empty($option['jwt']) ) : ?>
                <tr>
                  <td><label for="vi_login"><?php _e('Login', 'fv-wordpress-flowplayer'); ?>:</label></td>
                  <td>
                    <p class="description">
                      <input type="text" name="vi_login" id="vi_login" class="medium" />
                    </p>
                  </td>
                </tr>
                <tr>
                  <td><label for="sharing_text"><?php _e('Password', 'fv-wordpress-flowplayer'); ?>:</label></td>
                  <td>
                    <p class="description">
                      <input type="password" name="vi_pass" id="vi_pass" class="medium" />
                    </p>
                  </td>
                </tr>
                <tr>
                  <td>
                  </td>
                  <td>
                    <input type="submit" name="fv_player_vi_install" value="<?php _e('Sign in', 'fv-wordpress-flowplayer'); ?>" class="button-primary">
                  </td>
                </tr>
              <?php endif; ?>
            <?php endif; ?>
          </tbody>
        </table>


      <?php
  }

  function settings_register() {
    if( !class_exists('FV_Player_Video_Intelligence') ) {
      add_meta_box( 'fv_flowplayer_video_intelligence', __('video intelligence', 'fv-wordpress-flowplayer'), array( $this, 'settings' ), 'fv_flowplayer_settings_video_ads', 'normal' );
    } else {
      add_meta_box( 'fv_flowplayer_video_intelligence', __('video intelligence', 'fv-wordpress-flowplayer'), array( $this, 'settings' ), 'fv_flowplayer_settings_video_intelligence', 'normal', 'high' );
    }
  }

  function show_notice() {
    if( $this->notice_status ) {
      echo "<div class='".$this->notice_status."'><p>".$this->notice."</p></div>\n";
    }
  }

  function start() {
    $should_install = false;

    if( current_user_can('install_plugins') && !empty($_POST['vi_login']) && !empty($_POST['vi_pass']) && !empty($_POST['fv_player_vi_install']) ) {
      check_admin_referer( 'fv_player_vi_install', '_wpnonce_fv_player_vi_install' );
      
      remove_action('admin_init', 'fv_player_settings_save', 9);

      $request = wp_remote_get( 'https://dashboard-api.vidint.net/v1/api/widget/settings' );
      if( is_wp_error($request) ) {
        $this->notice_status = 'error';
        $this->notice = "Can't connect to dashboard-api.vidint.net (1)!";
        return;
      }

      $body = wp_remote_retrieve_body( $request );

      $data = json_decode( $body );

      if( !$data || empty($data->data) || empty($data->data->loginAPI) ) {
        $this->notice_status = 'error';
        $this->notice = "Can't parse settings URLs!";
        return;
      }


      $request = wp_remote_post( $data->data->loginAPI, array(
        'headers'   => array('Content-Type' => 'application/json;charset=UTF-8'),
        'body'      => json_encode(array( 'email' => $_POST['vi_login'], 'password' => $_POST['vi_pass'] )),
        'method'    => 'POST'
      ));

      if( is_wp_error($request) ) {
        $this->notice_status = 'error';
        $this->notice = "Can't connect to dashboard-api.vidint.net (2)!";
        return;
      }

      $body = wp_remote_retrieve_body( $request );

      $data = json_decode( $body );

      if( !$data || empty($data->status) || $data->status != 'ok' ) {
        $this->notice_status = 'error';
        $this->notice = 'Error logging in to video intelligence account. Please double check that you have filled in the video intelligence signup form and confirmed the account by clicking the link in confirmation email.';
        return;
      }

      update_option('fv_player_video_intelligence', array( 'jwt' => $data->data, 'time' => time() ) );

      $this->notice_status = 'updated';
      $this->notice = 'video intelligence login successful!';

      //  attempt plugin auto install!
      $should_install = true;
    }

    if( !empty($_POST['fv-player-vi-install']) ) {
      $should_install = true;
    }

    if( $should_install ) {
      $result = FV_Wordpress_Flowplayer_Plugin::install_plugin(
        "FV Player Video Intelligence",
        "fv-player-video-intelligence",
        "fv-player-video-intelligence.php",
        "https://foliovision.com/plugins/public/fv-player-video-intelligence-0.1.zip",
        '/wp-admin/options-general.php?page=fvplayer',
        'fv_wordpress_flowplayer_deferred_notices'
      );

      if( $result ) {
        echo "<script>location.href='".admin_url('options-general.php?page=fvplayer#postbox-container-tab_video_intelligence')."'; location.reload();</script>";
      }
    }
  }
}

new FV_Player_video_intelligence_Installer;
