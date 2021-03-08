<?php

abstract class FV_Player_Wizard_Step_Base {

  var $buttons = array(
    'next' => array(
      'primary' => false,
      'value' => 'Next',
    )
  );

  function buttons() {
    ?>
      <tr>
        <td></td>
        <td>
          <?php $this->get_buttons( $this->buttons ); ?>
        </td>
      </tr>
    <?php
  }

  function display() {
    global $fv_fp;
    $fv_fp->_get_input_text( array(
      'key' => static::$key,
      'name' => static::$name,
      'class' => 'regular-text code'
    ) );
  }

  function get_buttons( $buttons ) {
    if( $buttons ) :
      foreach( $buttons AS $name => $button ) : ?>
        <input type="button" class="button<?php if( !empty($button['primary']) ) echo ' button-primary'; ?>" data-fv-player-wizard-<?php echo $name; ?> value="<?php echo $button['value']; ?>" />
      <?php endforeach; ?>
      <img data-fv-player-wizard-indicator width="16" height="16" src="<?php echo site_url('wp-includes/images/wpspin-2x.gif'); ?>" style="display: none" />
    <?php endif;
  }

  function is_finish() {
    return !empty(static::$is_finish) && static::$is_finish;
  }

  // use to fill in field names that should be submitted with this step if they belonged to some other step
  function extra_fields() {
    return array();
  }

  // the place to output any extra HTML which the step might need
  // Note: it can't rely on Ajax next_step data
  function extra_scripts() {}

  function process() {
    wp_send_json( array(
      // Use when everything is fine and Wizard can go to next step
      'ok' => true,
      
      // Use to provide any error message
      'error' => false,
      
      // If provided, it will be used to populare the next step content
      //
      // Typically it should be something like this
      //
      // $dos_spaces_picker = new FV_Player_Coconut_Wizard_step_5_dos_spaces_picker($dos_info);
      //
      // ob_start();
      // $dos_spaces_picker->display();
      // $dos_spaces_picker->buttons();
      // $html = ob_get_clean()
      //
      // TODO: Do this properly in the class
      'next_step' => false,
    ) );
  }

  // should this step show at all? Your class can override this method to provide custom logic
  function should_show() {
    return true;
  }
}