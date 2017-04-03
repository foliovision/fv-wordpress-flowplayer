<?php
require_once dirname(__FILE__) . '/../includes/mailchimp-api/src/MailChimp.php';

use \DrewM\MailChimp\MailChimp;

class FV_Player_Collect_Emails {

  public function __construct() {

    add_action('admin_init', array($this, 'admin__add_meta_boxes'));
    add_filter('fv_flowplayer_popup_html', array($this, 'popup_html'));
    add_filter('fv_player_conf_defaults', array($this, 'conf_defaults'));
    add_filter('fv_flowplayer_settings_save',array($this, 'fv_flowplayer_settings_save'), 10, 2);
    add_action('wp_ajax_nopriv_fv_wp_flowplayer_email_signup', array($this, 'email_signup'));
    add_action('wp_ajax_fv_wp_flowplayer_email_signup', array($this, 'email_signup'));
    add_filter('fv_player_admin_popups_defaults',array($this,'fv_player_admin_popups_defaults'));


    if( !empty($_GET['fv-email-export']) && !empty($_GET['page']) && $_GET['page'] === 'fvplayer'){
      $this->csvExport($_GET['fv-email-export']);
    }
  }

  public function conf_defaults($conf) {
    $conf += array(
      'mailchimp_api' => '',
      'mailchimp_list' => '',
      'mailchimp_label' => 'Subscribe for updates',
    );
    return $conf;
  }

  public function admin__add_meta_boxes() {
    add_meta_box('fv_flowplayer_email_lists', __('Email Lists', 'fv-wordpress-flowplayer'), array($this, 'settings_box_lists'), 'fv_flowplayer_settings_actions', 'normal');
    add_meta_box('fv_flowplayer_email_integration', __('Email Integration', 'fv-wordpress-flowplayer'), array($this, 'settings_box_integration'), 'fv_flowplayer_settings_actions', 'normal');
  }

  public function fv_flowplayer_settings_save($param1,$param2){

    if(isset($_POST['email_lists'])){
      $aOptions = $_POST['email_lists'];
      unset($aOptions['#fv_popup_dummy_key#']);

      foreach( $aOptions AS $key => $value ) {
        $aOptions[$key]['first_name'] = stripslashes($value['first_name']);
        $aOptions[$key]['last_name'] = stripslashes($value['last_name']);
        $aOptions[$key]['integration'] = stripslashes($value['integration']);
        $aOptions[$key]['title'] = stripslashes($value['title']);
        $aOptions[$key]['description'] = stripslashes($value['description']);

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
    <p><?php _e('Enter your service API key and then assign it to a list which you create above.', 'fv-wordpress-flowplayer'); ?></p>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td style="width: 250px"><label for="mailchimp_api"><?php _e('Mailchimp API key', 'fv-wordpress-flowplayer'); ?>:</label></td>
        <td>
          <p class="description">
            <input type="text" name="mailchimp_api" id="mailchimp_api" value="<?php echo esc_attr($fv_fp->_get_option('mailchimp_api')); ?>" />
          </p>
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
        </td>
      </tr>
    </table>


    <?php
  }

  public function settings_box_lists () {
    ?>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td>
          <table id="fv-player-email_lists-settings">
            <thead>
            <tr>
              <td>ID</td>
              <td><?php _e('Title', 'fv-wordpress-flowplayer'); ?></td>
              <td><?php _e('Description', 'fv-wordpress-flowplayer'); ?></td>
              <td><?php _e('First Name', 'fv-wordpress-flowplayer'); ?></td>
              <td><?php _e('Last Name', 'fv-wordpress-flowplayer'); ?></td>
              <td><?php _e('Integration', 'fv-wordpress-flowplayer'); ?></td>
              <td><?php _e('Export', 'fv-wordpress-flowplayer'); ?></td>
              <td><?php _e('Disable', 'fv-wordpress-flowplayer'); ?></td>
            </tr>
            </thead>
            <tbody>
            <?php
            $aListData = get_option('fv_player_email_lists');
            if( empty($aListData) ) {
              $aListData = array( 1 => array() );
            }
            if(!isset($aListData['#fv_list_dummy_key#'])){
              $aListData =  array( '#fv_list_dummy_key#' => array() ) + $aListData ;
            }
            $aMailchimpLists = $this->get_mailchimp_lists();
            
            foreach ($aListData AS $key => $aList) {
              $mailchimpOptions = '';

              foreach($aMailchimpLists['result'] as $mailchimpId => $list){
                if(!$list)
                  continue;
                $use = true;
                foreach($list['fields'] as $field){

                  if( $field['required'] && ($field['tag'] === "FNAME" && !$aList['first_name'] || $field['tag'] === "LNAME" && !$aList['last_name'] ) ){
                    $use = false;
                    break;
                  }

                }
                if($use){
                  $mailchimpOptions .= '<option value="' . $list['id'] . '" ' . ( isset($aList['integration']) && $list['id'] === $aList['integration']?"selected":"" ) . '>' . $list['name'] . '</option>';
                }
              }
              
              if( $aMailchimpLists && $mailchimpOptions ) {
                $mailchimp_no_option = 'None';
              } else if( $aMailchimpLists && !$mailchimpOptions ) {
                $mailchimp_no_option = 'No matching list found';
              } else {
                $mailchimp_no_option = 'No integrations configured';
              }

              ?>
              <tr class='data' id="fv-player-list-item-<?php echo $key; ?>"<?php echo $key === '#fv_list_dummy_key#' ? 'style="display:none"' : ''; ?>>
                <td class='id'><?php echo $key ; ?></td>
                <td>
                  <input type='text' name='email_lists[<?php echo $key; ?>][title]' value='<?php echo isset($aList['title']) ? esc_attr($aList['title']) : ''; ?>' />
                </td>
                <td>
                  <input type='text' name='email_lists[<?php echo $key; ?>][description]' value='<?php echo isset($aList['description']) ? esc_attr($aList['description']) : ''; ?>' />
                </td>                
                <td>
                  <input type='hidden' name='email_lists[<?php echo $key; ?>][first_name]' value='0' />
                  <input id='list-first-name-<?php echo $key; ?>' type='checkbox' name='email_lists[<?php echo $key; ?>][first_name]' value='1' <?php echo (isset($aList['first_name']) && $aList['first_name'] ? 'checked="checked"' : ''); ?> />
                </td>
                <td>
                  <input type='hidden' name='email_lists[<?php echo $key; ?>][last_name]' value='0' />
                  <input id='list-last-name-<?php echo $key; ?>' type='checkbox' name='email_lists[<?php echo $key; ?>][last_name]' value='1' <?php echo (isset($aList['last_name']) && $aList['last_name'] ? 'checked="checked"' : ''); ?> />
                </td>
                <td>                  
                  <select name="email_lists[<?php echo $key; ?>][integration]" >
                    <option value=""><?php echo $mailchimp_no_option; ?></option>
                    <?php echo $mailchimpOptions ;?>
                  </select>
                </td>
                <td>
                  <a class='fv-player-list-export' href='<?php echo trailingslashit(admin_url());?>options-general.php?page=fvplayer&fv-email-export=<?php echo $key; ?>' target="_blank" ><?php _e('CSV', 'fv-wordpress-flowplayer'); ?></a>
                </td>
                <td>
                  <input type='hidden' name='email_lists[<?php echo $key; ?>][disabled]' value='0' />
                  <input id='ListAdDisabled-<?php echo $key; ?>' type='checkbox' name='email_lists[<?php echo $key; ?>][disabled]' value='1' <?php echo (isset($aList['disabled']) && $aList['disabled'] ? 'checked="checked"' : ''); ?> />
                  <a class='fv-player-list-remove' href=''><?php _e('Remove', 'fv-wordpress-flowplayer'); ?></a>
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
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
          <input type="button" value="<?php _e('Add More Lists', 'fv-wordpress-flowplayer'); ?>" class="button" id="fv-player-email_lists-add" />
        </td>
      </tr>
    </table>

    <script>
      jQuery('#fv-player-email_lists-add').click( function() {
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
    if($list['disabled'] === '1'){
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
      . '<input type="email" placeholder="' . __('Email Address', 'fv-wordpress-flowplayer') . '" name="email"/>'
      . $popupItems . '<input type="submit" value="' . __('Subscribe', 'fv-wordpress-flowplayer') . '"/></form>';
    return $popup;
  }

  /*
   * API CALL
   */
  private function get_mailchimp_lists() {
    global $fv_fp;
    $aLists = array();

    if (empty($fv_fp->conf['mailchimp_api'])) {
      update_option('fv_mailchimp_lists', $aLists);
      return array('error' => 'No API key found.  ', 'result' => $aLists);
    }

    $aLists = get_option('fv_mailchimp_lists', array());
    if( get_option('fv_mailchimp_time', 0 ) + 3600 > time() && !isset($_GET['fv_refresh_mailchimp']) ) return array('error' => false, 'result' => $aLists);


    $MailChimp = new MailChimp($fv_fp->conf['mailchimp_api']);
    $MailChimp->verify_ssl = false;
    $result = $MailChimp->get('lists');
    $error = $MailChimp->getLastError();
    if ($error || !$result) {
      update_option('fv_mailchimp_time', time() - 50 * 60);
      update_option('fv_mailchimp_lists', $aLists);
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
          $mergeFields = $MailChimp->get("lists/{$list['id']}/merge-fields");
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

    update_option('fv_mailchimp_time', time() );
    update_option('fv_mailchimp_lists', $aLists);
    return array('error' => false, 'result' => $aLists);
  }

  private function  mailchimp_signup($list_id, $data){
    global $fv_fp;
    $MailChimp = new MailChimp($fv_fp->_get_option('mailchimp_api'));
    $merge_fields = array();

    if(isset($data['first_name'])){
      $merge_fields['FNAME'] = $data['first_name'];
    }

    if(isset($data['last_name'])){
      $merge_fields['LNAME'] = $data['last_name'];
    }

    $result_data = $MailChimp->post("lists/$list_id/members", array(
      'email_address' => $data['email'],
      'status' => 'subscribed',
      'merge_fields' => (object)$merge_fields));

    $result = array(
      'status' => 'OK',
      'text' => __('Thank You for subscribing.', 'fv-wordpress-flowplayer'),
      'error_log' => false,
    );


    if ($result_data['status'] === 400) {
      if ($result_data['title'] === 'Member Exists') {
        $result = array(
          'status' => 'ERROR',
          'text' => __('Email Address already subscribed.', 'fv-wordpress-flowplayer'),
          'error_log' => $result_data,
        );
      } elseif ($result_data['title'] === 'Invalid Resource') {
        $result = array(
          'status' => 'ERROR',
          'text' => __('Email Address not valid', 'fv-wordpress-flowplayer'),
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
    $data = $_POST;
    $list_id = isset($data['list']) ? $data['list'] : 0;
    unset($data['list']);
    $aLists = get_option('fv_player_email_lists');

    $list = isset($aLists[$list_id]) ? $aLists[$list_id] : array();

    global $wpdb;
    $table_name = $wpdb->prefix . 'fv_player_emails';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
      $sql = "CREATE TABLE `$table_name` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `email` TEXT NULL,
        `first_name` TEXT NULL,
        `last_name` TEXT NULL,
        `id_list` INT(11) NOT NULL,
        `date` DATETIME NULL DEFAULT NULL,
        `data` TEXT NULL,
        `error` TEXT NULL,
        PRIMARY KEY (`id`)
      )" . $wpdb->get_charset_collate() . ";";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta($sql);
    }

    $result = array(
      'status' => 'OK',
      'text' => __('Thank You for subscribing.', 'fv-wordpress-flowplayer'));


    if(!empty($list['integration'])){
      $result = $this->mailchimp_signup($list['integration'],$data);
    }
    if(empty($data['email']) || filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)===false){
      $result['status'] = 'ERROR';
      $result['text'] = __('Malformed Email Address.', 'fv-wordpress-flowplayer');
    };

    $count = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'fv_player_emails WHERE email="' . addslashes($data['email']) . '" AND id_list = "'. addslashes($list_id) .'"' );


    if(intval($count) === 0){
      $wpdb->insert($table_name, array(
        'email' => $data['email'],
        'data' => serialize($data),
        'id_list'=>$list_id,
        'date'=>date("Y-m-d H:i:s"),
        'first_name' => isset($data['first_name']) ? $data['first_name'] : '',
        'last_name' => isset($data['last_name']) ? $data['last_name'] : '',
        'error' => $result['status'] === 'ERROR' ? serialize( $result['error_log'] ) : '',
      ));
    }elseif($result['status'] === 'OK'){
      $result = array(
        'status' => 'ERROR',
        'text' => __('Email Address already subscribed.', 'fv-wordpress-flowplayer'),
      );
    }else{
      $wpdb->insert($table_name, array(
        'email' => $data['email'],
        'data' => serialize($data),
        'id_list'=>$list_id,
        'date'=>date("Y-m-d H:i:s"),
        'first_name' => isset($data['first_name']) ? $data['first_name'] : '',
        'last_name' => isset($data['last_name']) ? $data['last_name'] : '',
        'error' => $result['status'] === 'ERROR' ? serialize( $result['error_log'] ) : '',
      ));
    }

    unset($result['error_log']);
    die(json_encode($result));
  }

  private function csvExport($list_id){
    $aLists = get_option('fv_player_email_lists');
    $list = $aLists[$list_id];
    $filename = 'export-lists-' . (empty($list->title) ? $list_id : $list->title) . '-' . date('Y-m-d') . '.csv';

    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    global $wpdb;
    $results = $wpdb->get_results('SELECT `email`,`first_name`,`last_name`,`date` FROM `' . $wpdb->prefix . 'fv_player_emails` WHERE `id_list` = "' . addslashes($list_id) . '"');


    foreach ($results as $row){
      echo '"'.implode('","',(array)$row) . "\"\n";
    }
    die;
  }

}

$FV_Player_Collect_Emails = new FV_Player_Collect_Emails();
