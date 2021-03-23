<?php

class FV_Player_Wizard_Introduction extends FV_Player_Wizard_Step_Base_Class {

  // prevent the standard buttons from showing
  var $buttons = array(
    'next' => array(
      'value' => 'Start',
      'primary' => true
    )
  );

  function display() {
    ?>
<tr>
  <td colspan="2">
    <h2>Welcome</h2>
    <p>This wizard will help you change the domain where your videos are hosted if you are switching CDNs. First you enter the source URL to replace and then the destination URL.</p>
    <p>The process is divided into multiple steps with preview and a confirmation before any change is done.</p>
  </td>
</tr>
    <?php
  }

}

$this->register_step('FV_Player_Wizard_Introduction');