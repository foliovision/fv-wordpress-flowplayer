<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Email_Subscription {

  public function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    add_action( 'admin_init', array($this, 'init_options') );

    add_action( 'admin_init', array($this, 'admin__add_meta_boxes') );
    add_filter( 'fv_flowplayer_popup_html', array($this, 'popup_html') );
    add_filter( 'fv_player_conf_defaults', array($this, 'conf_defaults') );
    add_filter( 'fv_flowplayer_settings_save', array($this, 'fv_flowplayer_settings_save'), 10, 2 );
    add_action( 'wp_ajax_nopriv_fv_wp_flowplayer_email_signup', array($this, 'email_signup') );
    add_action( 'wp_ajax_fv_wp_flowplayer_email_signup', array($this, 'email_signup') );
    add_filter( 'fv_player_admin_popups_defaults', array($this,'fv_player_admin_popups_defaults') );

    add_action( 'wp_ajax_fv_player_email_subscription_save', array($this, 'save_settings') );

    add_action('admin_init', array( $this, 'csv_export' ) );

    add_action( 'admin_notices', array($this,'admin_export_screen') );

    add_filter( 'fv_flowplayer_attributes', array( $this, 'popup_preview' ), 10, 3 );

  }

  /*
  * SETTINGS
  */

  public function conf_defaults($conf) {
    $conf += array(
      'mailchimp_api' => '',
      'mailchimp_list' => '',
      'mailchimp_label' => 'Subscribe for updates',
    );
    return $conf;
  }

  public function init_options() {
    if( !get_option('fv_player_email_lists') ) {
      update_option('fv_player_email_lists', array( 1 => array('first_name' => true,
                                                   'last_name' => false,
                                                   'integration' => false,
                                                   'title' => 'Subscribe to list one',
                                                   'description' => 'Two good reasons to subscribe right now',
                                                   'disabled' => false
                                                   ) ) );
    }
  }

  public function admin__add_meta_boxes() {
    add_meta_box('fv_flowplayer_email_lists', __( 'Email Popups', 'fv-player' ), array($this, 'settings_box_lists'), 'fv_flowplayer_settings_actions', 'normal');
    add_meta_box('fv_flowplayer_email_integration', __( 'Email Integration', 'fv-player' ), array($this, 'settings_box_integration'), 'fv_flowplayer_settings_actions', 'normal');
  }

  public function fv_flowplayer_settings_save($param1,$param2){

    if ( isset( $_POST['email_lists'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv_player_email_lists_nonce'] ) ), 'fv_player_email_lists' ) ) {
      $aOptions = array();
      unset($aOptions['#fv_popup_dummy_key#']);

      foreach( $_POST['email_lists'] AS $key => $value ) {
        $key = intval($key);
        $aOptions[$key]['first_name'] = sanitize_text_field( $value['first_name'] );
        $aOptions[$key]['last_name'] = sanitize_text_field( $value['last_name'] );
        $aOptions[$key]['integration'] = isset($value['integration']) ? sanitize_text_field( $value['integration'] ) : false;
        $aOptions[$key]['title'] = sanitize_text_field( $value['title'] );
        $aOptions[$key]['description'] = sanitize_text_field( $value['description'] );

      }
      update_option('fv_player_email_lists',$aOptions);
    }

    return $param1;
  }

  public function fv_player_admin_popups_defaults($aData){
    $aPopupData = get_option('fv_player_email_lists');
    unset($aPopupData['#fv_list_dummy_key#']);

    if( is_array($aPopupData) ) {
      foreach( $aPopupData AS $key => $aPopupAd ) {
        $aData['email-' . $key] = $aPopupAd;
      }
    }

    return $aData;
  }

  public function settings_box_integration () {
    global $fv_fp;
    ?>
    <p><?php esc_html_e( 'Enter your service API key and then assign it to a list which you create above.', 'fv-player' ); ?></p>
    <?php if( version_compare(phpversion(),'5.3.0') >= 0 ) : ?>
      <table class="form-table2" style="margin: 5px; ">
        <tr>
          <td style="width: 250px"><label for="mailchimp_api"><?php esc_html_e( 'Mailchimp API key', 'fv-player' ); ?>:</label></td>
          <td>
            <p class="description">
              <input type="text" name="mailchimp_api" id="mailchimp_api" value="<?php echo esc_attr($fv_fp->_get_option('mailchimp_api')); ?>" />
            </p>
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
          </td>
        </tr>
      </table>
    <?php else : ?>
      <p><?php esc_html_e( 'Please upgrade to PHP 5.3 or above to use the Mailchimp integration.', 'fv-player' ); ?></p>
    <?php endif;
  }

  public function settings_box_lists () {
    global $fv_fp;

    $aListData = get_option('fv_player_email_lists');
    if( empty($aListData) ) {
      $aListData = array( 1 => array() );
    }
    if(!isset($aListData['#fv_list_dummy_key#'])){
      $aListData =  array( '#fv_list_dummy_key#' => array() ) + $aListData ;
    }
    $aMailchimpLists = $this->get_mailchimp_lists();
    ?>
    <p><?php esc_html_e( 'Lists defined here can be used for subscription box for each video or for Default Popup above.', 'fv-player' ); ?></p>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td>
          <table id="fv-player-email_lists-settings">
            <thead>
            <tr>
              <td>ID</td>
              <td style="width: 40%"><?php esc_html_e( 'Properties', 'fv-player' ); ?></td>
              <?php if( !empty($aMailchimpLists['result']) ) : ?>
                <td><?php esc_html_e( 'Target List', 'fv-player' ); ?></td>
              <?php endif; ?>
              <td><?php esc_html_e( 'Export', 'fv-player' ); ?></td>
              <td><?php esc_html_e( 'Options', 'fv-player' ); ?></td>
              <td><?php esc_html_e( 'Status', 'fv-player' ); ?></td>
              <td></td>
            </tr>
            </thead>
            <tbody>
            <?php

            foreach ($aListData AS $key => $aList) {
              $mailchimpOptions = '';

              foreach($aMailchimpLists['result'] as $mailchimpId => $list){
                if(!$list)
                  continue;
                $use = true;
                foreach($list['fields'] as $field){

                  if( $field['required'] && ($field['tag'] === "FNAME" && ( empty($aList['first_name']) || !$aList['first_name'] ) || $field['tag'] === "LNAME" && ( empty($aList['last_name']) || !$aList['last_name'] ) ) ){
                    $use = false;
                    break;
                  }

                }
                if($use){
                  $mailchimpOptions .= '<option value="mailchimp-' . $list['id'] . '" ' . ( isset($aList['integration']) && 'mailchimp-' . $list['id'] === $aList['integration']?"selected":"" ) . '>' . $list['name'] . '</option>';
                }
              }

              if( $aMailchimpLists && $mailchimpOptions ) {
                $mailchimp_no_option = 'None';
              } else if( $aMailchimpLists && !$mailchimpOptions ) {
                $mailchimp_no_option = 'No matching list found';
              }

              ?>
              <tr class='data' id="fv-player-list-item-<?php echo esc_attr( $key ); ?>"<?php echo $key === '#fv_list_dummy_key#' ? ' style="display:none"' : ''; ?>>
                <td class='id'><?php echo esc_html( $key ); ?></td>
                <td>
                  <table>
                    <tr>
                      <td style="width:16%"><label>Header</label></td>
                      <td><input type='text' name='email_lists[<?php echo esc_attr( $key ); ?>][title]' value='<?php echo isset($aList['title']) ? esc_attr($aList['title']) : ''; ?>' /></td>
                    </tr>
                    <tr>
                      <td><label>Message</label></td>
                      <td><input type='text' name='email_lists[<?php echo esc_attr( $key ); ?>][description]' value='<?php echo isset($aList['description']) ? esc_attr($aList['description']) : ''; ?>' /></td>
                    </tr>
                  </table>
                </td>
                <?php if( !empty($aMailchimpLists['result']) ) : ?>
                  <td>
                    <select name="email_lists[<?php echo esc_attr( $key ); ?>][integration]" title="E-mail list">
                      <option value=""><?php echo esc_html( $mailchimp_no_option ); ?></option>
                      <?php echo wp_kses( $mailchimpOptions, array( 'option' => array( 'value' => array() ) ) );?>
                    </select>
                    <br />&nbsp;
                  </td>
                <?php endif; ?>
                <td>
                  <a class='fv-player-list-export' href='<?php echo wp_nonce_url( admin_url('admin.php?page=fvplayer&fv-email-export='.$key ), 'fv-email-export', 'nonce' ); ?>' target="_blank" ><?php esc_html_e( 'Download CSV', 'fv-player' ); ?></a>
                  <br />
                  <a class='fv-player-list-export' href='<?php echo wp_nonce_url( admin_url('admin.php?page=fvplayer&fv-email-export-screen='.$key), 'fv-email-show', 'nonce' ); ?>' target="_blank" ><?php esc_html_e( 'View list', 'fv-player' ); ?></a>
                </td>
                <td>
                    <input type='hidden' name='email_lists[<?php echo esc_attr( $key ); ?>][first_name]' value='0' />
                    <input id='list-first-name-<?php echo esc_attr( $key ); ?>' title="first name" type='checkbox' name='email_lists[<?php echo esc_attr( $key ); ?>][first_name]' value='1' <?php echo (isset($aList['first_name']) && $aList['first_name'] ? 'checked="checked"' : ''); ?> />
                    <label for='list-first-name-<?php echo esc_attr( $key ); ?>'>First Name</label>
                    <br />
                    <input type='hidden' name='email_lists[<?php echo esc_attr( $key ); ?>][last_name]' value='0' />
                    <input id='list-last-name-<?php echo esc_attr( $key ); ?>' title="last name" type='checkbox' name='email_lists[<?php echo esc_attr( $key ); ?>][last_name]' value='1' <?php echo (isset($aList['last_name']) && $aList['last_name'] ? 'checked="checked"' : ''); ?> />
                    <label for='list-last-name-<?php echo esc_attr( $key ); ?>'>Last Name</label>
                </td>
                <td>
                  <input type='hidden' name='email_lists[<?php echo esc_attr( $key ); ?>][disabled]' value='0' />
                  <input id='ListAdDisabled-<?php echo esc_attr( $key ); ?>' type='checkbox' title="disable" name='email_lists[<?php echo esc_attr( $key ); ?>][disabled]' value='1' <?php echo (isset($aList['disabled']) && $aList['disabled'] ? 'checked="checked"' : ''); ?> />
                  <label for='ListAdDisabled-<?php echo esc_attr( $key ); ?>'>Disable</label>
                  <br />
                  <a class='fv-player-list-remove' href=''><?php esc_html_e( 'Remove', 'fv-player' ); ?></a>
                </td>
                <td>
                  <input type="button" style="visibility: hidden" class="fv_player_email_list_save button" value="Save & Preview" />
                </td>
              </tr>
              <?php
            }
            ?>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <?php wp_nonce_field( 'fv_player_email_lists', 'fv_player_email_lists_nonce' ); ?>
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="Save All Changes">
          <input type="button" value="<?php esc_html_e( 'Add More Lists', 'fv-player' ); ?>" class="button" id="fv-player-email_lists-add" />
        </td>
      </tr>
    </table>

    <script>
      jQuery('#fv-player-email_lists-add').on('click', function() {
        var fv_player_list_index  = (parseInt( jQuery('#fv-player-email_lists-settings tr.data:last .id').html()  ) || 0 ) + 1;
        jQuery('#fv-player-email_lists-settings').append(jQuery('#fv-player-email_lists-settings tr.data:first').prop('outerHTML').replace(/#fv_list_dummy_key#/gi,fv_player_list_index + ""));
        jQuery('#fv-player-list-item-' + fv_player_list_index).show();
        return false;
      } );

      jQuery(document).on('click','.fv-player-list-remove', false, function() {
        if( confirm('Are you sure you want to remove the list?') ){
          jQuery(this).parents('.data').remove();
          if(jQuery('#fv-player-email_lists-settings .data').length === 1) {
            jQuery('#fv-player-email_lists-add').trigger('click');
          }
        }
        return false;
      } );

      jQuery(document).on('keydown change', '#fv-player-email_lists-settings', function(e) {
        var row = jQuery(e.target).parents('[id^="fv-player-list-item-"]');
        row.find('.fv_player_email_list_save').css('visibility','visible');
      });
      jQuery(document).on('click', '#fv-player-email_lists-settings input[type=checkbox]', function(e) {
        var row = jQuery(e.target).parents('[id^="fv-player-list-item-"]');
        row.find('.fv_player_email_list_save').css('visibility','visible');
      });

      jQuery(document).on('click', '.fv_player_email_list_save', function() {
        var button = jQuery(this);
        var row = button.parents('[id^="fv-player-list-item-"]');
        var aInputs = row.find('input, select');
        var key = row.attr('id').replace(/fv-player-list-item-/,'');

        fv_player_open_preview_window(null,720,480);

        button.prop('disabled',true);
        jQuery.ajax( {
          type: "POST",
          url: ajaxurl,
          data: aInputs.serialize()+'&key='+key+'&action=fv_player_email_subscription_save&_wpnonce=<?php echo wp_create_nonce('fv_player_email_subscription_save'); ?>',
          success: function(response) {
            button.css('visibility','hidden');
            button.prop('disabled', false);

            row.replaceWith( jQuery('#'+row.attr('id'),response) );

            var shortcode = '<?php echo '[fvplayer src="https://player.vimeo.com/external/196881410.hd.mp4?s=24645ecff21ff60079fc5b7715a97c00f90c6a18&profile_id=174&oauth2_token_id=3501005" splash="https://i.vimeocdn.com/video/609485450_1280.jpg" preroll="no" postroll="no" subtitles="'.flowplayer::get_plugin_url().'/images/test-subtitles.vtt" end_popup_preview="true" popup="email-#key#" caption="'.__("This is how the popup will appear at the end of a video", 'fv-player').'"]'; ?>';
            shortcode = shortcode.replace(/#key#/,key);

            var url = '<?php echo home_url(); ?>?fv_player_preview_nonce=<?php echo wp_create_nonce( "fv_player_preview" ); ?>&fv_player_preview=' + fv_player_editor.b64EncodeUnicode(shortcode);
            fv_player_open_preview_window(url);
          },
          error: function() {
            button.val('Error saving!');
          }
        } );
      });

      function fv_player_open_preview_window(url, width, height){
        height = Math.min(window.screen.availHeight * 0.80, height + 25);
        width = Math.min(window.screen.availWidth * 0.66, width + 100);

        if( typeof fv_player_preview_window == 'undefined' || fv_player_preview_window == null || fv_player_preview_window.self == null || fv_player_preview_window.closed ){
          fv_player_preview_window = window.open(url,'window','toolbar=no, menubar=no, resizable=yes width=' + width + ' height=' + height);
        }else{
          fv_player_preview_window.location.assign(url);
          fv_player_preview_window.focus();
        }

      }
    </script>
    <?php
  }

  /*
   * GENEREATE HTML
   */
  public function popup_html($popup) {
    if ($popup === 'email-no'){
      return '';
    }

    if(strpos($popup,'email-') !== 0)
    {
      return $popup ;
    }

    $id = array_reverse(explode('-',$popup));
    $id = $id[0];
    $aLists = get_option('fv_player_email_lists',array());
    $list = isset($aLists[$id]) ? $aLists[$id] : array('disabled' => '1');

    if( empty($list['title']) || isset($list['disabled']) && $list['disabled'] === '1'){
      return '';
    }
    $popupItems = '';
    $count = 1;
    foreach($list as $key => $field){
      if(($key === 'first_name' || $key === 'last_name') && $field == "1"){
        $count++;
        $aName = explode('_',$key);
        foreach($aName as $nameKey => $val){
          $aName[$nameKey] = ucfirst($aName[$nameKey]);
        }

        $sName = implode(' ',$aName);
        $popupItems .= '<input type="text" placeholder="' . $sName . '" name="' . $key . '" required/>';
      }

    }
    $popup = '';
    if( !empty($list['title']) ) $popup .= '<h3>'.$list['title'].'</h3>';
    if( !empty($list['description']) ) $popup .= '<p>'.$list['description'].'</p>';
    $popup .= '<form class="mailchimp-form  mailchimp-form-' . $count . '">'
      . '<input type="hidden" name="list" value="' . $id . '" />'
      . '<input type="email" placeholder="' . esc_attr__( 'Email Address', 'fv-player' ) . '" name="email"/>'
      . $popupItems . '<input type="submit" value="' . esc_attr__( 'Subscribe', 'fv-player' ) . '"/></form>';
    return $popup;
  }

  /*
   * API CALL
   */
  private function get_mailchimp_lists() {
    if(version_compare(phpversion(),'5.3.0','<')){
      return array('error' => 'PHP 5.3 or above required.', 'result' => false);
    }

    global $fv_fp;
    $aLists = array();

    if (!$fv_fp->_get_option('mailchimp_api')) {
      update_option('fv_player_mailchimp_lists', $aLists);
      return array('error' => 'No API key found.  ', 'result' => $aLists);
    }

    $aLists = get_option('fv_player_mailchimp_lists', array());
    $sTimeout = !$aLists || count($aLists) == 0 ? 60 : 3600;

    if( get_option('fv_player_mailchimp_time', 0 ) + $sTimeout > time() ) return array('error' => false, 'result' => $aLists);

    if( !class_exists('\DrewM\MailChimp\MailChimp') ) {
      require_once dirname(__FILE__) . '/../includes/mailchimp-api/src/MailChimp.php';
    }
    require_once dirname(__FILE__) . '/email-subscription-mailchimp.php';

    $result = fv_player_mailchimp_result();
    $error = fv_player_mailchimp_last_error();
    if ($error || !$result) {
      update_option('fv_player_mailchimp_time', time() - 50 * 60);
      update_option('fv_player_mailchimp_lists', $aLists);
      return array('error' => $error, 'result' => $aLists);
    }
    $aLists  = array();
    foreach ($result['lists'] as $list) {
      $item = array(
        'id' => $list['id'],
        'name' => $list['name'],
        'fields' => array()
      );


      foreach ($list['_links'] as $link) {
        if ($link['rel'] === 'merge-fields') {
          $mergeFields = fv_player_mailchimp_get($list['id']);
          foreach ($mergeFields['merge_fields'] as $field) {
            $item['fields'][] = array(
              'tag' => $field['tag'],
              'name' => $field['name'],
              'required' => $field['required'],
            );
          }
          break;
        }
      }
      $aLists[$list['id']] = $item;
    }

    update_option('fv_player_mailchimp_time', time() );
    update_option('fv_player_mailchimp_lists', $aLists);
    return array('error' => false, 'result' => $aLists);
  }

  private function  mailchimp_signup($list_id){

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_player_email_signup' ) ) {
      wp_json_encode(
        array(
          'status' => 'ERROR',
          'text' => __( 'Nonce verification error.', 'fv-player' ),
        )
      );
      exit;
    }

    if( !class_exists('\DrewM\MailChimp\MailChimp') ) {
      require_once dirname(__FILE__) . '/../includes/mailchimp-api/src/MailChimp.php';
    }
    require_once dirname(__FILE__) . '/email-subscription-mailchimp.php';

    $merge_fields = array();

    if(isset( $_POST['first_name'] )){
      $merge_fields['FNAME'] = sanitize_text_field( $_POST['first_name'] );
    }

    if(isset( $_POST['last_name'] )){
      $merge_fields['LNAME'] = sanitize_text_field( $_POST['last_name'] );
    }

    $result_data = fv_player_mailchimp_post($list_id, sanitize_text_field( $_POST['email'] ), $merge_fields);

    $result = array(
      'status' => 'OK',
      'text' => __( 'Thank You for subscribing.', 'fv-player' ),
      'error_log' => false,
    );


    if ($result_data['status'] === 400) {
      if ($result_data['title'] === 'Member Exists') {
        $result = array(
          'status' => 'ERROR',
          'text' => __( 'Email Address already subscribed.', 'fv-player' ),
          'error_log' => $result_data,
        );
      } elseif ($result_data['title'] === 'Invalid Resource') {
        $result = array(
          'status' => 'ERROR',
          'text' => __( 'Email Address not valid', 'fv-player' ),
          'error_log' => $result_data
        );
      } else {
        $result = array(
          'status' => 'ERROR',
          'text' => 'Unknown Error 1. ',
          'error_log'=> $result_data,
        );
      }
    }elseif($result_data['status'] !== 'subscribed'){
      $result = array(
        'status' => 'ERROR',
        'text' => 'Unknown Error 2.',
        'error_log'=> $result_data,
      );
    }
    return $result;
  }

  public function email_signup() {

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_player_email_signup' ) ) {
      wp_json_encode(
        array(
          'status' => 'ERROR',
          'text' => __( 'Nonce verification error.', 'fv-player' ),
        )
      );
      exit;
    }

    $list_id = isset( $_POST['list'] ) ? absint( $_POST['list'] ) : 0;

    $aLists = get_option('fv_player_email_lists');

    $list = isset($aLists[$list_id]) ? $aLists[$list_id] : array();

    global $wpdb;
    $table_name = $wpdb->prefix . 'fv_player_emails';
    if( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) != $table_name ) {
      $sql = "CREATE TABLE `$table_name` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `email` TEXT NULL,
        `first_name` TEXT NULL,
        `last_name` TEXT NULL,
        `id_list` INT(11) NOT NULL,
        `date` DATETIME NULL DEFAULT NULL,
        `data` TEXT NULL,
        `integration` TEXT NULL,
        `integration_nice` TEXT NULL,
        `status` TEXT NULL,
        `error` TEXT NULL,
        PRIMARY KEY (`id`)
      )" . $wpdb->get_charset_collate() . ";";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta($sql);
    }

    $result = array(
      'status' => 'OK',
      'text' => __( 'Thank You for subscribing.', 'fv-player' ));

    $integration_nice = '';

    if(!empty($list['integration'])){
      $aLists = get_option('fv_player_mailchimp_lists', array());
      $integration_nice = $aLists[str_replace('mailchimp-','',$list['integration'])]['name'];
      $result = $this->mailchimp_signup(str_replace('mailchimp-','',$list['integration']));
    }
    if(empty( $_POST['email'] ) || filter_var(trim( sanitize_text_field( $_POST['email'] ) ), FILTER_VALIDATE_EMAIL)===false){
      $result['status'] = 'ERROR';
      $result['text'] = __( 'Malformed Email Address.', 'fv-player' );
      die(wp_json_encode($result));
    };

    $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."fv_player_emails WHERE email = %s AND id_list = %s", sanitize_email( $_POST['email'] ), intval($list_id) ) );

    if(intval($count) === 0){
      $wpdb->insert($table_name, array(
        'email' => sanitize_email( $_POST['email'] ),
        'data' => '',
        'id_list'=> intval($list_id),
        'date' => gmdate("Y-m-d H:i:s"),
        'first_name' => isset( $_POST['first_name'] ) ? wp_strip_all_tags( sanitize_text_field( $_POST['first_name'] ) ) : '',
        'last_name' => isset( $_POST['last_name'] ) ? wp_strip_all_tags( sanitize_text_field( $_POST['last_name'] ) ) : '',
        'integration' => $list['integration'],
        'integration_nice' => $integration_nice,
        'status' => $result['status'],
        'error' => $result['status'] === 'ERROR' ? serialize( $result['error_log'] ) : '',
      ));

    }elseif($result['status'] === 'OK'){
      $result = array(
        'status' => 'ERROR',
        'text' => __( 'Email Address already subscribed.', 'fv-player' ),
      );

    }else{
      $wpdb->insert($table_name, array(
        'email' => sanitize_email( $_POST['email'] ),
        'data' => '',
        'id_list' => intval($list_id),
        'date' => gmdate("Y-m-d H:i:s"),
        'first_name' => isset( $_POST['first_name'] ) ? wp_strip_all_tags( sanitize_text_field( $_POST['first_name'] ) ) : '',
        'last_name' => isset( $_POST['last_name'] ) ? wp_strip_all_tags( sanitize_text_field( $_POST['last_name'] ) ) : '',
        'integration' => $list['integration'],
        'integration_nice' => $integration_nice,
        'status' => $result['status'],
        'error' => $result['status'] === 'ERROR' ? serialize( $result['error_log'] ) : '',
      ));
    }

    unset($result['error_log']);
    die(wp_json_encode($result));
  }

  function csv_export() {
    if( isset($_GET['fv-email-export']) && !empty($_GET['page']) && sanitize_key( $_GET['page'] ) === 'fvplayer' && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'fv-email-export' ) ) {
      $list_id = intval($_GET['fv-email-export']);
      $aLists = get_option('fv_player_email_lists');
      $list = $aLists[$list_id];
      $filename = 'export-lists-' . (empty($list->title) ? $list_id : $list->title) . '-' . gmdate('Y-m-d') . '.csv';

      header("Content-type: text/csv");
      header("Content-Disposition: attachment; filename=$filename");
      header("Pragma: no-cache");
      header("Expires: 0");

      global $wpdb;
      $results = $wpdb->get_results('SELECT `email`, `first_name`, `last_name`, `date`, `integration`, `integration_nice`, `status`, `error` FROM `' . $wpdb->prefix . 'fv_player_emails` WHERE `id_list` = "' . intval($list_id) . '"');

      echo 'email,first_name,last_name,date,integration,status,error'."\n";
      if( $results ) {
        foreach ($results as $row){
          if(!empty($row->integration)){
            $row->integration .= ': '.$row->integration_nice;
          }
          unset($row->integration_nice);

          if(!empty($row->error)){
            $tmp = unserialize($row->error);
            $row->error =  $tmp['title'];
          }


          echo '"' . implode('","',str_replace('"','',(array)$row)) . "\"\n";
        }
      }
      die;
    }
  }


  public function admin_export_screen(){
    if( isset($_GET['fv-email-export-screen']) && !empty($_GET['page']) && sanitize_key( $_GET['page'] ) === 'fvplayer' && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'fv-email-show' ) ) {

      $list_id = intval($_GET['fv-email-export-screen']);

      global $wpdb;
      $results = $wpdb->get_results( $wpdb->prepare( "SELECT `email`, `first_name`, `last_name`, `date`, `integration`, `integration_nice`, `status`, `error` FROM {$wpdb->prefix}fv_player_emails WHERE id_list = %d LIMIT 10", $list_id ) );

      ?>
      <style>
        #adminmenumain { display: none }
        #wpcontent { margin-left: 0 }
      </style>

      <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
          <th scope="col" class="manage-column">E-mail</th>
          <th scope="col" class="manage-column">First Name</th>
          <th scope="col" class="manage-column">Last Name</th>
          <th scope="col" class="manage-column">Date</th>
          <th scope="col" class="manage-column">Integration</th>
          <th scope="col" class="manage-column">Status</th>
          <th scope="col" class="manage-column">Error</th>
        </tr>
        </thead>
        <tbody>
      <?php
      foreach ($results as $row){
        echo '<tr>';
        foreach ($row as $key => $item) {
          if($key === 'integration' && !empty($item)){
            $item .= ': ' . $row->integration_nice;
          }elseif($key === 'integration_nice'){
            continue;
          }elseif($key === 'error'){
            $item = '';
            if( !empty($item) ) {
              $tmp = unserialize($item);
              $item = $tmp['title'];
            }
          }
          echo '<td>' . wp_strip_all_tags($item) . '</td>';
        }
        echo '</tr>';
      }
      ?>
        </tbody>
      </table>
      <p>
        <a class='fv-player-list-export button' href='<?php echo admin_url('admin.php?page=fvplayer&fv-email-export='.intval($list_id));?>' target="_blank" ><?php esc_attr_e( 'Download CSV', 'fv-player' ); ?></a>
      </p>

    <?php

      die();
    }
  }


  public function save_settings() {
    check_ajax_referer('fv_player_email_subscription_save');

    $aLists = get_option('fv_player_email_lists',array());
    $key = intval($_POST['key']);

    if( !isset($_POST['email_lists'][$key]) ) {
      header('HTTP/1.0 403 Forbidden');
      die();
    }

    foreach ( $_POST['email_lists'] as $index => $values) {
      foreach ($values as $key => $value) {
        $aLists[$index][$key] = sanitize_text_field( $value );
      }
    }
    update_option('fv_player_email_lists',$aLists);

    fv_player_admin_page();
  }


  public function popup_preview( $aAttributes ) {
    global $fv_fp;
    $aArgs = func_get_args();
    if( isset($aArgs[2]->aCurArgs['end_popup_preview']) && $aArgs[2]->aCurArgs['end_popup_preview'] ) {
      $aAttributes['data-end_popup_preview'] = true;
    }
    return $aAttributes;
  }

}

global $FV_Player_Email_Subscription;
$FV_Player_Email_Subscription = new FV_Player_Email_Subscription();
