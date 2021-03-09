<?php

class FV_Player_Wizard_Step_Finish extends FV_Player_Wizard_Step_Base {

  static $is_finish = true;

  var $buttons = array();

  function display() {
    ?>
<tr>
  <td colspan="2">
    <h2>Finished</h2>
    <p>All occurrences replaced!</p>
    <p><a href="<?php echo admin_url('admin.php?page=fv_player'); ?>" class="button button-primary">Check videos</a></p>
  </td>
</tr>
    <?php
  }

}

$this->register_step('FV_Player_Wizard_Step_Finish');