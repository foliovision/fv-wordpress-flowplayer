<?php

class FV_Player_Wizard_Step_1_Search_Videos extends FV_Player_Wizard_Step_Base {

  var $buttons = array(
    'next' => array(
      'value' => 'Search',
    )
  ); 

  function display() {
  global $fv_fp;
    ?>
<tr>
  <td colspan="2">
    <h2>Step 1: What To Replace</h2>
    <p>Enter string you want to replace:</p>
  </td>
</tr>
    <?php

  $fv_fp->_get_input_text( array(
    'key' => array('video_src_search','search_string'),
    'name' => 'Search string',
    'class' => 'regular-text code'
  ) );
  }

  function process() {
    global $wpdb;
    $search_string = $_POST['video_src_search']['search_string'];
    
    $videos_data = $wpdb->get_results( $wpdb->prepare(
      "SELECT id, src FROM `{$wpdb->prefix}fv_player_videos` WHERE src LIKE %s", '%' . $wpdb->esc_like($search_string) . '%'
    ) );

    $list_videos = new FV_Player_Wizard_Step_2_List_Videos($search_string, $videos_data);

    ob_start();
    $list_videos->display();
    $list_videos->buttons();
    return array(
      'next_step' => ob_get_clean(),
      'ok' => true
    );
  
  }

}

$this->register_step('FV_Player_Wizard_Step_1_Search_Videos');