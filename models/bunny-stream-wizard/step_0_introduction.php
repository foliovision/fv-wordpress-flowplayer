<?php

class FV_Player_Bunny_Stream_Introduction extends FV_Player_Wizard_Step_Base_Class {

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
    <p><a href="https://bunny.net/stream/?ref=rsfonj1su1" target="_blank">Bunny Stream</a> offers great encoding and hosting for your videos.</p>
    <p>With free video encoding, storage from $0.01/GB/month and CDN from $0.005/GB/month, their prices don’t even have a competitor that would fall in the same price bracket.</p>
    <p><img src="<?php echo plugins_url( 'images/pricing.png', __FILE__ ); ?>" srcset="<?php echo plugins_url( 'images/pricing.png', __FILE__ ); ?> 1x, <?php echo plugins_url( 'images/pricing-2x.png', __FILE__ ); ?> 2x" /></p>
    <p>Detailed comparison can be found in our <a href="https://foliovision.com/2021/09/video-encoding-prices-cost" target="_blank">The True Cost Of A Video Encoding Workflow</a> article.</p>
    <p>If you do not have a Bunny.net account, please <a href="https://bunny.net/stream/?ref=rsfonj1su1" target="_blank">sign up for it here</a>.</p>
  </td>
</tr>
    <?php
  }

}

$this->register_step('FV_Player_Bunny_Stream_Introduction');