<?php

class FV_Player_Wizard_Step_2_List_Videos extends FV_Player_Wizard_Step_Base {

  private $search_string; 
  private $videos_data;

  var $buttons = array(
    'next' => array(
      'value' => 'Test Replace',
      'primary' => true
    )
  ); 

  public function __construct($search_string = false, $videos_data = false) {
    $this->search_string = $search_string;
    $this->videos_data = $videos_data;
  }

  function display() {
    global $fv_fp;
    ?>
<tr>
  <td colspan="2">
    <h2>Step 2: What To Replace</h2>
    <p>Enter new string you want to replace with:</p>
  </td>
</tr>
<?php

  $fv_fp->_get_input_text( array(
    'key' => array('video_src_replace','replace_string'),
    'name' => 'Replace string',
    'class' => 'regular-text code'
  ) );

  ?>
<tr>
  <td colspan="2">
    <h2>Videos found:</h2>
  </td>
</tr>
    <?php
    if( !empty($this->videos_data) ) {
      foreach($this->videos_data as $video) {
    ?>
<tr>
  <td colspan="2">
    <p>ID <?php echo $video->id ?> </p>
    <p>Src <?php echo $video->src ?> </p>
  </td>
</tr>
    <?php } ?>
<input type="hidden" name="search_string" value="<?php echo $this->search_string ?>" >
    <?php
    }
  }

  function process() {
    global $wpdb;

    $search_string = $_POST['search_string'];
    $replace_string = $_POST['video_src_replace']['replace_string'];
    $videos_data = $wpdb->get_results( $wpdb->prepare(
      "SELECT id, src FROM `{$wpdb->prefix}fv_player_videos` WHERE src LIKE %s", '%' . $wpdb->esc_like($search_string) . '%'
    ) );
    
    $test_replace = new FV_Player_Wizard_Step_3_Test_Replace($search_string, $replace_string ,$videos_data);

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