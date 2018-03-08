<?php

class FV_Player_video_intelligence_Installer {
  
  var $notice = false;
  var $notice_status = false;
  
  function __construct() {
    add_action('admin_init', array( $this, 'start' ), 8 ) ;
    add_action('admin_init', array( $this, 'settings_register' ), 8 ) ;
    add_action( 'admin_notices', array( $this, 'show_notice' ) );
  }
  
  function settings() {
    ?>
      <p>Create in-stream inventory with vi stories</p>
      <p>vi stories creates in-stream ad opportunities on your site</p>
      <ul>
        <li>Introduce contextual video content that your users are interested in</li>
        <li>Create a great user experience and longer time-on-site</li>
        <li>Monetize with in-stream video ads at a high CPM</li>
        <li>Relevant, topical videos, sourced from:</li>
      </ul>
      <?php $current_user = wp_get_current_user(); ?>
      <a href="http://vi.ai/publisher-video-monetization/?aid=foliovision&email=<?php echo urlencode($current_user->user_email); ?>&url=<?php echo urlencode(home_url()); ?>" target="_blank" class="button">Signup</a>
      <p>Once you complete the signup above, please enter your login information below. FV Player doesn't store this login information, only the auth token valid for 30 days is stored.</p>
      <table class="form-table2" style="margin: 5px; ">  
        <tbody>
          <tr>
            <td><label for="vi_login"><?php _e('Login', 'fv-wordpress-flowplayer'); ?>:</label></td>
            <td>
              <p class="description">
                <input type="text" name="vi_login" id="vi_login" />
              </p>
            </td>
          </tr>
          <tr>
            <td><label for="sharing_text"><?php _e('Password', 'fv-wordpress-flowplayer'); ?>:</label></td>
            <td>
              <p class="description">
                <input type="text" name="vi_pass" id="vi_pass" />
              </p>
            </td>
          </tr>                        
          <tr>    		
            <td>
              <input type="submit" value="<?php _e('Sign in', 'fv-wordpress-flowplayer'); ?>" class="button-primary">
            </td>
          </tr>         
        </tbody>
      </table>


    <?php
  }
  
  function settings_register() {
    add_meta_box( 'fv_flowplayer_video_intelligence', __('video intelligence', 'fv-wordpress-flowplayer'), array( $this, 'settings' ), 'fv_flowplayer_settings_video_ads', 'normal' );
  }
  
  function show_notice() {
    if( $this->notice_status ) {
      echo "<div class='".$this->notice_status."'><p>".$this->notice."</p></div>\n";
    }
  }
  
  function start() {
    if( !empty($_POST['vi_login']) && !empty($_POST['vi_pass']) ) {
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
      
      update_option('fv-player-video-intelligence', array( 'jwt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJwdWJsaXNoZXJJZCI6IjUwMDYyMDM1NDM3MDI4NSIsImVtYWlsIjoibWFydGludkBmb2xpb3Zpc2lvbi5jb20iLCJmaXJzdExvZ2luIjpmYWxzZSwicm9sZSI6IlB1Ymxpc2hlciAoSlMgV1ApIiwibG9nZ2VkRnJvbSI6bnVsbCwiaWF0IjoxNTIwNTE0MzczLCJleHAiOjE1MjMxMDYzNzN9.jOJi_KRMMQqFIO-TqvlOR_zrAx2vnafMsO7WcdYcU4A', 'time' => time() ) );

      $this->notice_status = 'updated';
      $this->notice = 'video intelligence login successful!';
      
      //  attempt plugin auto install!
      
    }    
  }
}

new FV_Player_video_intelligence_Installer;