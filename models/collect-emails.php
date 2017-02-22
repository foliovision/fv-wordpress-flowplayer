<?php
require_once dirname(__FILE__) . '/../includes/mailchimp-api/src/MailChimp.php';

use \DrewM\MailChimp\MailChimp;

class FV_Player_Collect_Emails {

  public function __construct() {

    add_action('admin_init', array($this, 'admin__add_meta_boxes'));
    add_filter('fv_flowplayer_popup_html', array($this, 'popup_html'));
    add_filter('fv_player_settings_default', array($this, 'settings_default'));
    add_action('wp_ajax_nopriv_fv_wp_flowplayer_mailchimp_register', array($this, 'mailchimp_register'));
    add_action('wp_ajax_fv_wp_flowplayer_mailchimp_register', array($this, 'mailchimp_register'));
  }

  public function settings_default($defaults) {
    $defaults += array(
        'mailchimp_api' => '',
        'mailchimp_list' => '',
        'mailchimp_label' => 'Subscribe for updates',
    );

    return $defaults;
  }

  public function admin__add_meta_boxes() {
    add_meta_box('fv_flowplayer_email_collection', __('Email collection', 'fv-wordpress-flowplayer'), array($this, 'fv_player_admin_menu'), 'fv_flowplayer_settings_actions', 'normal');
  }

  public function fv_player_admin_menu() {
    global $fv_fp;
    ?>
    <table class="form-table2 fv-player-interface-form-group">
      <tr>
        <td><label for="mailchimp_api"><?php _e('Mailchimp API key', 'fv-wordpress-flowplayer'); ?>:</label></td>
        <td>
          <p class="description">
            <input type="text" name="mailchimp_api" id="mailchimp_api" value="<?php if ($fv_fp->conf['mailchimp_api'] !== 'false') echo esc_attr($fv_fp->conf['mailchimp_api']); ?>" />                  
          </p>
        </td>
      </tr>
      <?php if (!empty($fv_fp->conf['mailchimp_api'])) : ?>
        <tr>
          <td><label for="mailchimp_api"><?php _e('Form label', 'fv-wordpress-flowplayer'); ?>:</label></td>
          <td>
            <p class="description">
              <input type="text" name="mailchimp_label" id="mailchimp_label" value="<?php if( isset($fv_fp->conf['mailchimp_label']) ) echo esc_attr($fv_fp->conf['mailchimp_label']); ?>" />
            </p>
          </td>
        </tr>
        <tr>
          <td colspan="2">
          <?php        
            $aLists = $this->mailchimp_get_lists();
            if( $aLists['error'] ) {
              echo $aLists['error'];
            } else {            
              $aLists = $aLists['result']; ?>
              <style>
                .mailchimp-lists th { text-align: left; }
                #wpfp_options .mailchimp-lists label { text-align: left; }
                .mailchimp-lists tr td:first-child { width: 2%; }
                .mailchimp-lists tr td:nth-child(2) { width: 20%; }
                .mailchimp-lists tr td:nth-child(3) { width: 75%; }
              </style>
              <p><?php _e('Pick your list below:', 'fv-wordpress-flowplayer' ); ?></p>
              <table class="mailchimp-lists">
                <tr><th></th><th>List</th><th>Additional Required Fields</th></tr><?php
                foreach ($aLists as $key => $list) {
                  $names = array();
                  foreach ($list['fields'] as $field) {
                    if( $field['required'] )$names[] = $field['name'];
                  }
                  ?>
                  <tr>
                    <td><input id="list-<?php echo $key; ?>" type="radio" name="mailchimp_list" value="<?php echo $list['id'] ?>" <?php echo $fv_fp->conf['mailchimp_list'] === $list['id'] ? 'checked' : ''; ?>></td>
                    <td><label for="list-<?php echo $key; ?>"><?php echo $list['name'] ?></label></td>
                    <td><?php echo implode($names, ', '); ?></td>
                  </tr><?php
                }
              ?></table><?php
            }        
            ?>
          </td>
        </tr>
      <?php endif; ?>
      <tr>    		
        <td colspan="2">          
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
        </td>
      </tr>
    </table>
    <?php
  }

  /*
   * GENEREATE HTML
   */

  public function popup_html($popup) {
    global $fv_fp;
    if ($popup !== 'mailchimp' || empty($fv_fp->conf['mailchimp_api'])) {
      return $popup;
    }
    $id = $fv_fp->conf['mailchimp_list'];
    $aLists = get_option('fv_mailchimp_lists', array());
    if (isset($aLists[$id])) {
      $popup = "";
      if( isset($fv_fp->conf['mailchimp_label']) && strlen(trim($fv_fp->conf['mailchimp_label'])) ) $popup = wpautop($fv_fp->conf['mailchimp_label']);
      $popup .= '<form class="mailchimp-form">'
              . '<input type="email" placeholder="Email Adress" name="MERGE0"/>';
      foreach ($aLists[$id]['fields'] as $field) {
        if ($field['required']) {
          $popup .= '<input type="text" placeholder="' . $field['name'] . '" name="' . $field['tag'] . '" required/>';
        }
      }
      $popup .= '<input type="submit" value="' . __('Subscribe', 'fv-wordpress-flowplayer') . '"/></form>';
    }
    return $popup;
  }

  /*
   * API CALL
   */

  private function mailchimp_get_lists() {
    global $fv_fp;    
    $aLists = array();

    if (empty($fv_fp->conf['mailchimp_api'])) {
      update_option('fv_mailchimp_lists', $aLists);
      return array('error' => 'No API key found.  ', 'result' => $aLists);
    }

    $MailChimp = new MailChimp($fv_fp->conf['mailchimp_api']);
    $MailChimp->verify_ssl = false;
    $result = $MailChimp->get('lists');
    $error = $MailChimp->getLastError();
    if ($error || !$result) {      
      update_option('fv_mailchimp_lists', $aLists);
      return array('error' => $error, 'result' => $aLists);
    }
    foreach ($result['lists']as $list) {
      $item = array(
          'id' => $list['id'],
          'name' => $list['name'],
          'fields' => array()
      );


      foreach ($list['_links']as $link) {
        if ($link['rel'] === 'merge-fields') {
          $mergeFields = $MailChimp->get("lists/{$list['id']}/merge-fields");
          foreach ($mergeFields['merge_fields']as $field) {
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
    update_option('fv_mailchimp_lists', $aLists);
    return array('error' => false, 'result' => $aLists);
  }

  public function mailchimp_register() {
    global $fv_fp;
    $MailChimp = new MailChimp($fv_fp->conf['mailchimp_api']);

    $list_id = $fv_fp->conf['mailchimp_list'];
    $merge_fields = array();
    foreach ($_POST as $key => $val) {
      if ($key === 'action' || $key === 'MERGE0')
        continue;
      $merge_fields[$key] = addslashes($val);
    }

    $result_data = $MailChimp->post("lists/$list_id/members", array(
        'email_address' => $_POST['MERGE0'],
        'status' => 'subscribed',
        'merge_fields' => $merge_fields));


    if ($result_data['status'] === 'subscribed') {
      $result = array(
          'status' => 'OK',
          'text' => __('Thank You for subscribing.', 'fv-wordpress-flowplayer'));

      global $wpdb;
      $table_name = $wpdb->prefix . 'fv_player_emails';
      if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `$table_name` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `email` TEXT NULL,
          `data` TEXT NULL,
          PRIMARY KEY (`id`)
        )" . $wpdb->get_charset_collate() . ";";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
      }

      $wpdb->insert($table_name, array(
          'email' => $_POST['MERGE0'],
          'data' => serialize($merge_fields),
      ));
    } elseif ($result_data['status'] === 400) {
      if ($result_data['title'] === 'Member Exists') {
        $result = array(
            'status' => 'OK',
            'text' => __('Email Address already subscribed.', 'fv-wordpress-flowplayer'),
        );
      } elseif ($result_data['title'] === 'Invalid Resource') {
        $result = array(
            'status' => 'ERROR',
            'text' => $result_data['detail'],
        );
      } else {
        $result = array(
            'status' => 'ERROR',
            'text' => 'Unknown Error.',
            'details' => $result_data['detail'],
        );
      }
    }
    die(json_encode($result));
  }

}

$FV_Player_Collect_Emails = new FV_Player_Collect_Emails();
