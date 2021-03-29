<?php

class FV_Player_Wizard_Step_2_List_Videos extends FV_Player_Wizard_Step_Base_Class {

  private $search_string; 

  var $buttons = array(
    'prev' => array(
      'value' => 'Adjust your search phrase'
    )
  ); 

  public function __construct($search_string = false) {
    $this->search_string = $search_string;
  }

  function display() {
    global $fv_fp;
    $videos_data = false;
    $meta_data =false;
    if( $this->search_string ) {
      $videos_data = FV_Player_Migration_Wizard::search_video($this->search_string);
      $meta_data = FV_Player_Migration_Wizard::search_meta($this->search_string);
    }
    ?>
<tr>
  <td colspan="2">
    <h2>Step 2: List of affected videos</h2>

    <?php if( !empty($videos_data) ) :
      FV_Player_Migration_Wizard::list_videos($videos_data, $this->search_string, false, '#f88' );
      ?>
      
    <?php else : ?>
      <p>No matching videos found.</p>
    <?php endif; ?>
    
    <?php if(!empty($meta_data)) : 
      FV_Player_Migration_Wizard::list_meta_data($meta_data, $this->search_string, false, '#f88' );
    ?>

    <?php else : ?>
      <p>No matching video meta found.</p>
    <?php endif; ?>

    <?php if( !empty($videos_data) || !empty($meta_data) ) : 
    $this->buttons['next'] = array(
        'value' => 'Test Replace',
        'primary' => true
      );
    ?>
     
      <tr>
        <td colspan="2">
          <input type="hidden" name="search_string" value="<?php echo esc_attr($this->search_string) ?>" >
          <p>Enter the string which should replace <code><?php echo $this->search_string; ?></code>:</p>
        </td>
      </tr>
      <?php
      $fv_fp->_get_input_text( array(
        'key' => array('video_src_replace','replace_string'),
        'name' => 'Replace string',
        'class' => 'regular-text code'
      ) );
    
    endif;
  }

  function process() {
    $search_string = $_POST['search_string'];
    $replace_string = $_POST['video_src_replace']['replace_string'];
    
    $test_replace = new FV_Player_Wizard_Step_3_Test_Replace($search_string, $replace_string);

    ob_start();
    $test_replace->display();
    $test_replace->buttons();
    return array(
      'next_step' => ob_get_clean(),
      'ok' => true
    );
  
  }

}

$this->register_step('FV_Player_Wizard_Step_2_List_Videos');