<?php

class FV_Player_Export_Import {

  public function __construct() {
    add_action( 'admin_init', array($this, 'init_options') );
    add_action( 'admin_init', array($this, 'admin__add_meta_boxes') );
    add_filter( 'fv_player_conf_defaults', array($this, 'conf_defaults') ); 
    if( !empty($_GET['fv-settings-export']) && !empty($_GET['page']) && $_GET['page'] === 'fvplayer'){
      add_action('admin_init', array( $this, 'json_export' ) );
    }    
  }

  public function admin__add_meta_boxes() {
    add_meta_box('fv_flowplayer_export_settings', __('Export settings', 'fv-wordpress-flowplayer'), array($this, 'export_box_admin'), 'fv_flowplayer_settings_exip', 'normal');
 
  }
  public function export_box_admin () {
    ?>
      <table class="form-table2" style="margin: 5px; ">
        <tr>
          <td style="width: 250px"><?php _e('Exportet settings', 'fv-wordpress-flowplayer'); ?>:</label></td>
          <td>
            <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-wordpress-flowplayer'); ?>" />
          </td>
        </tr>        
      </table>
    <?php
  }

  function json_export(){
    $list_id = $_GET['fv-settings-export'];
    $aLists = get_option( 'fvwpflowplayer');
    $list = $aLists[$list_id];
    $filename = 'export-fv-settings-' . date('Y-m-d') . '.json';
	/*
    header('Content-Type: application/json');
    header("Content-Disposition: attachment; filename=$filename");
    header("Cache-Control: no-cache");
    header("Expires: 0");
	 */
    // WHERE `id_list` = "' . esc_sql($list_id) . '
    global $wpdb;
    $results = $wpdb->get_results("SELECT `option_id`,`option_name`,`option_value` FROM `' . $wpdb->prefix . '_options` WHERE `option_name` LIKE '%flowplayer%'");

    if( $results ) {
      foreach ($results as $row){
         echo $row->option_name .' : '.$row->option_value . "\"\n";      
      }
    }
	echo var_dump($aLists);
    die;
  }

}

$FV_Player_Export_Import = new FV_Player_Export_Import();
