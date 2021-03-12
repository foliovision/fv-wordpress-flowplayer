<?php

class FV_Player_Wizard_Step_1_Search_Videos extends FV_Player_Wizard_Step_Base_Class {

  var $buttons = array(
    'next' => array(
      'value' => 'See what will be replaced',
      'primary' => true
    )
  );

  function display() {
    global $fv_fp;
    ?>
<tr>
  <td colspan="2">
    <h2>Step 1: What To Replace</h2>
    <p>Enter the part of the URL to replace (domain).</p>
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
    $search_string = $_POST['video_src_search']['search_string'];
    
    $list_videos = new FV_Player_Wizard_Step_2_List_Videos($search_string);

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