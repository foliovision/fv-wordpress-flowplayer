<?php

class FV_Player_Wizard_Introduction extends FV_Player_Wizard_Step_Base {

  // prevent the standard buttons from showing
  var $buttons = array();

  function display() {
    ?>
<tr>
  <td colspan="2">
    <h2>Welcome</h2>
    <p>This wizard will help you replace part of or entire video src:</p>
    <?php if( function_exists('FV_Player_Pro') ) : ?>
      <ol>
        <li>You have FV Player Pro installed</li>
      </ol>
    <?php else : ?>
      <ol>
        <li>You have FV Wordpress Flowplayer installed</li>
      </ol>
    <?php endif; ?>
    <p>
    Proceed:
      <?php $this->get_buttons( array(
        'next' => array(
          'primary' => true,
          'value' => 'Next',
        )
      ) ); ?>

    </p>
  </td>
</tr>
    <?php
  }

}

$this->register_step('FV_Player_Wizard_Introduction');