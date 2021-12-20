<?php

class FV_Player_Bunny_Stream_Wizard_step_finish extends FV_Player_Wizard_Step_Base_Class {

  protected $buttons = array();

  function display() {
    ?>
<tr>
  <td colspan="2">
    <h2>Finished</h2>
    <p>Congratulations, you just finished your FV Player Bunny Stream setup!</p>
    <p><a href="<?php echo admin_url('admin.php?page=fv_player'); ?>" class="button button-primary">Upload your first video</a></p>
  </td>
</tr>
    <?php
  }

}

$this->register_step('FV_Player_Bunny_Stream_Wizard_step_finish');