<?php
require_once dirname(__FILE__) . '/../includes/mailchimp-api/src/MailChimp.php';

use \DrewM\MailChimp\MailChimp;

class FV_Player_Collect_Emails {

  public function __construct() {

    add_action('admin_init', array($this, 'admin__add_meta_boxes'));
    add_filter('fv_flowplayer_popup_html', array($this, 'popup_html'));
    add_filter('fv_player_conf_defaults', array($this, 'conf_defaults'));
    add_filter('fv_flowplayer_settings_save',array($this, 'fv_flowplayer_settings_save'), 10, 2);
    add_action('wp_ajax_nopriv_fv_wp_flowplayer_mailchimp_register', array($this, 'mailchimp_register'));
    add_action('wp_ajax_fv_wp_flowplayer_mailchimp_register', array($this, 'mailchimp_register'));
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
    add_meta_box('fv_flowplayer_email_collection', __('Email collection', 'fv-wordpress-flowplayer'), array($this, 'fv_player_admin_menu'), 'fv_flowplayer_settings_actions', 'normal');
  }

  public function fv_flowplayer_settings_save($param1,$param2){

    if(isset($_POST['email_lists'])){
      $aOptions = $_POST['email_lists'];
      unset($aOptions['#fv_popup_dummy_key#']);

      foreach( $aOptions AS $key => $value ) {
        $aOptions[$key]['first_name'] = stripslashes($value['first_name']);
        $aOptions[$key]['last_name'] = stripslashes($value['last_name']);
        $aOptions[$key]['mailchimp'] = stripslashes($value['mailchimp']);
        $aOptions[$key]['name'] = stripslashes($value['name']);

      }
      update_option('fv_player_email_lists',$aOptions);
    }

   return $param1;
  }

  private function fv_flowplayer_admin_select_email_lists($aArgs){

    $aPopupData = get_option('fv_player_email_lists');

    $sId = (isset($aArgs['id'])?$aArgs['id']:'email_lists_default');
    $aArgs = wp_parse_args( $aArgs, array( 'id'=>$sId, 'cva_id'=>'', 'show_default' => false ) );
    ?>
    <select id="<?php echo $aArgs['id']; ?>" name="<?php echo $aArgs['id']; ?>">
      <?php if( $aArgs['show_default'] ) : ?>
        <option>Use site default</option>
      <?php endif; ?>
      <option <?php if( $aArgs['item_id'] == 'no' ) echo 'selected '; ?>value="no"><?php _e('None', 'fv-wordpress-flowplayer'); ?></option>
      <option <?php if( $aArgs['item_id'] == 'random' ) echo 'selected '; ?>value="random"><?php _e('Random', 'fv-wordpress-flowplayer'); ?></option>
      <?php
      if( isset($aPopupData) && is_array($aPopupData) && count($aPopupData) > 0 ) {
        foreach( $aPopupData AS $key => $aPopupAd ) {
          ?><option <?php if( $aArgs['item_id'] == $key ) echo 'selected'; ?> value="<?php echo $key; ?>"><?php
          echo $key;
          if( !empty($aPopupAd['name']) ) echo ' - '.$aPopupAd['name'];
          if( $aPopupAd['disabled'] == 1 ) echo ' (currently disabled)';
          ?></option><?php
        }
      } ?>
    </select>
    <?php
  }

  public function fv_player_admin_menu (){
    global $fv_fp;
    ?>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td style="width:150px;vertical-align:top;line-height:2.4em;"><label for="email_lists_default"><?php _e('Default list', 'fv-wordpress-flowplayer'); ?>:</label></td>
        <td>
          <?php $cva_id = $fv_fp->_get_option('email_lists_default'); ?>
          <?php $this->fv_flowplayer_admin_select_email_lists( array('item_id'=>$cva_id,'id'=>'email_lists_default') ); ?>
          <p class="description"><?php _e('You can set a default list here and then skip it for individual videos.', 'fv-wordpress-flowplayer'); ?></p>
        </td>
      </tr>
    </table>
    <table class="form-table3" style="margin: 5px; ">
      <tr>
        <td><label for="mailchimp_api"><?php _e('Mailchimp API key', 'fv-wordpress-flowplayer'); ?>:</label>
          <p class="description">
            <input type="text" name="mailchimp_api" id="mailchimp_api" value="<?php if ($fv_fp->conf['mailchimp_api'] !== 'false') echo esc_attr($fv_fp->conf['mailchimp_api']); ?>" />
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <table id="fv-player-email_lists-settings">
            <thead>
            <tr>
              <td>ID</td>
              <td></td>
              <td><?php _e('Status', 'fv-wordpress-flowplayer'); ?></td>
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
                var_dump($aMailchimpLists);
                if(!$list)
                  continue;
                $use = true;
                foreach($list['fields'] as $field){
                  if( $field['tag']==='FNAME' && (isset($aList['first_name']) && !$aList['first_name'] || empty($aList['first_name']) ) ||
                    $field['tag']==='LNAME' && (isset($aList['last_name']) && !$aList['last_name'] || empty($aList['last_name']))){
                    $use = false;
                    break;
                  }

                }
                if($use){

                  $mailchimpOptions .= '<option value="' . $list['id'] . '" ' . ($list['id'] === $aList['mailchimp']?"checked":"" ) . '>' . $list['name'] . '</option>';
                }
              }

              ?>
              <tr class='data' id="fv-player-list-item-<?php echo $key; ?>"<?php echo $key === '#fv_list_dummy_key#' ? 'style="display:none"' : ''; ?>>
                <td class='id'><?php echo $key ; ?></td>
                <td>
                  <label for='list-name-<?php echo $key; ?>'><?php _e('First Name', 'fv-wordpress-flowplayer'); ?></label><input type='text' name='email_lists[<?php echo $key; ?>][name]' value='<?php echo isset($aList['name']) ? $aList['name'] : ''; ?>' />
                </td>
                <td>
                  <input type='hidden' name='email_lists[<?php echo $key; ?>][first_name]' value='0' />
                  <input id='list-first-name-<?php echo $key; ?>' type='checkbox' name='email_lists[<?php echo $key; ?>][first_name]' value='1' <?php echo (isset($aList['first_name']) && $aList['first_name'] ? 'checked="checked"' : ''); ?> />
                  <label for='list-first-name-<?php echo $key; ?>'><?php _e('First Name', 'fv-wordpress-flowplayer'); ?></label><br />
                </td>
                <td>
                  <input type='hidden' name='email_lists[<?php echo $key; ?>][last_name]' value='0' />
                  <input id='list-last-name-<?php echo $key; ?>' type='checkbox' name='email_lists[<?php echo $key; ?>][last_name]' value='1' <?php echo (isset($aList['last_name']) && $aList['last_name'] ? 'checked="checked"' : ''); ?> />
                  <label for='list-last-name-<?php echo $key; ?>'><?php _e('Last Name', 'fv-wordpress-flowplayer'); ?></label><br />
                </td>
                <td>
                  <label for='list-mailchimp-<?php echo $key; ?>'><?php _e('Mailchimp List', 'fv-wordpress-flowplayer'); ?></label>
                  <select name="email_lists[<?php echo $key; ?>][mailchimp]" >
                    <option value="" >No list</option>
                    <?php echo $mailchimpOptions ;?>
                  </select>
                </td>
                <td>
                  <input type='hidden' name='email_lists[<?php echo $key; ?>][disabled]' value='0' />
                  <input id='ListAdDisabled-<?php echo $key; ?>' type='checkbox' name='email_lists[<?php echo $key; ?>][disabled]' value='1' <?php echo (isset($aList['disabled']) && $aList['disabled'] ? 'checked="checked"' : ''); ?> />
                  <label for='PopupAdDisabled-<?php echo $key; ?>'><?php _e('Disable', 'fv-wordpress-flowplayer'); ?></label><br />
                  <a class='fv-player-list-remove' href=''><?php _e('Remove', 'fv-wordpress-flowplayer'); ?></a></td>
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
          <input type="button" value="<?php _e('Add more lists', 'fv-wordpress-flowplayer'); ?>" class="button" id="fv-player-email_lists-add" />
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
  function toReadable($val, $key){


  }


  public function popup_html($popup) {
    global $fv_fp;
    if (strpos($popup,'email-') !== 0 ) {
      return $popup;
    }

    $id = array_reverse(explode('-',$popup));
    $id = $id[0];
    $aLists = get_option('fv_player_email_lists',array());
    //var_dump($aLists,$id);
    $list = isset($aLists[$id]) ? $aLists[$id] : array();
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
    $popup = '<form class="mailchimp-form  mailchimp-form-' . $count . '">'
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


  public function mailchimp_register() {
    global $fv_fp;
    $MailChimp = new MailChimp($fv_fp->_get_option('mailchimp_api'));
    $list_id = $fv_fp->_get_option('mailchimp_list');
    $merge_fields = array();
    foreach ($_POST as $key => $val) {
      if ($key === 'action' || $key === 'MERGE0')
        continue;
      $merge_fields[$key] = addslashes($val);
    }
    $result_data = $MailChimp->post("lists/$list_id/members", array(
        'email_address' => $_POST['MERGE0'],
        'status' => 'subscribed',
        'merge_fields' => (object)$merge_fields));


    $result = array(
        'status' => 'OK',
        'text' => __('Thank You for subscribing.', 'fv-wordpress-flowplayer'));

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


    $error = false;

    if ($result_data['status'] === 400) {
      if ($result_data['title'] === 'Member Exists') {
        $result = array(
            'status' => 'OK',
            'text' => __('Email Address already subscribed.', 'fv-wordpress-flowplayer'),
        );
        die(json_encode($result));
      } elseif ($result_data['title'] === 'Invalid Resource') {
        $result = array(
            'status' => 'ERROR',
            'text' => __('Email Address not valid', 'fv-wordpress-flowplayer'),
        );
        $error = serialize($result_data);
      } else {
        $result = array(
            'status' => 'ERROR',
            'text' => 'Unknown Error.',
            'details' => $result_data['detail'],
        );
        $error = serialize($result_data);
      }
    }elseif($result_data['status'] !== 'subscribed'){
      $error = serialize($result_data);
    }

    $wpdb->insert($table_name, array(
      'email' => $_POST['MERGE0'],
      'data' => serialize($merge_fields),
      'first_name' => isset($_POST['FNAME']) ? $_POST['FNAME'] : '',
      'last_name' => isset($_POST['LNAME']) ? $_POST['LNAME'] : '',
      'error' => $error
    ));

    die(json_encode($result));
  }

}

$FV_Player_Collect_Emails = new FV_Player_Collect_Emails();
