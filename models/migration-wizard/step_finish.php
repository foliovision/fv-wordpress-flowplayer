<?php

class FV_Player_Wizard_Step_Finish extends FV_Player_Wizard_Step_Base_Class {

  static $is_finish = true;

  var $buttons = array(); /* filled below as we need to call a function there */
  
  var $message = false;
  
  public function __construct($search_string = false, $replace_string = false ) {
    
    $this->buttons['done'] = array(
      'href' => admin_url('admin.php?page=fv_player'),
      'primary' => true,
      'value' => 'Go back to FV Player'
    );
    
    $this->search_string = $search_string ; 
    $this->replace_string = $replace_string;
  }

  function display() {
    ?>
<tr>
  <td colspan="2">
    <?php echo $this->message; ?>
  </td>
</tr>
    <?php
  }

}

$this->register_step('FV_Player_Wizard_Step_Finish');