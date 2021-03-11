<?php

class FV_Player_Wizard_Step_3_Test_Replace extends FV_Player_Wizard_Step_Base {

  private $search_string; 
  private $replace_string;

  var $buttons = array(
    'prev' => array(
      'value' => 'Adjust your replacement phrase'
    ),
    'next' => array(
      'value' => 'Replace all',
      'primary' => true
    )
  );

  public function __construct($search_string = false, $replace_string = false) {
    $this->search_string = $search_string ; 
    $this->replace_string = $replace_string;
  }

  function display() {
    global $fv_fp;
    ?>
<tr>
  <td colspan="2">
    <h2>Step 3: Replace Preview</h2>
      <p>Test replacing <b><?php echo $this->search_string ?></b> with <b><?php echo $this->replace_string ?></b></p>
  </td>
</tr>

<tr>
  <td colspan="2">
    <h2>Videos list:</h2>
  </td>
</tr>
    <?php
        
    if( $this->search_string ) {
      global $wpdb;
      $videos_data = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, src FROM `{$wpdb->prefix}fv_player_videos` WHERE src LIKE %s", '%' . $wpdb->esc_like($this->search_string) . '%'
      ) );
      
      if( !empty($videos_data) ) {
        foreach($videos_data as $video) {
      ?>
  <tr>
    <td colspan="2">
      <p>ID <?php echo $video->id ?> </p>
      <p>Src <?php echo  str_replace( $this->search_string, $this->replace_string, $video->src ) ?> </p>
    </td>
  </tr>
      <?php } ?>
      
  <tr>
    <td colspan="2">
      <input type="checkbox" required name="confirmation" /> I have checked the above and I want the links to be replaced.
      <input type="hidden" name="search_string" value="<?php echo $this->search_string ?>" >
      <input type="hidden" name="replace_string" value="<?php echo $this->replace_string ?>" >
    </td>
  </tr>
      <?php
      }
    }

  }

  function process() {
    global $wpdb;

    $search_string = $_POST['search_string'];
    $replace_string= $_POST['replace_string'];
    
        
    $step_finish = new FV_Player_Wizard_Step_Finish();

    if( !empty($_POST['confirmation']) ) {
      
      $affected_fields = array();
      foreach( array( 'src', 'src1', 'src2', 'splash' ) AS $field ) {
        $affected_fields[$field] = $wpdb->query( $wpdb->prepare(
          "UPDATE `{$wpdb->prefix}fv_player_videos` SET {$field} = REPLACE( {$field}, '%s', '%s' ) WHERE {$field} LIKE %s",
          $search_string,
          $replace_string,
          '%' . $wpdb->esc_like($search_string) . '%'
        ) );
      }

      $message = "<h2>Done!</h2>\n";
      $message .= "<p>Number of replacements:</p>\n";
      $message .= "<ul>\n";
      foreach( $affected_fields AS $field => $count ) {
        $message .= "<li>".$field.": ".$count."</li>\n";
      }
      $message .= "</ul>\n";
      
    } else {
      $message = "<p>No replacements done as the confirmation checkbox was not selected.</p>";
      $step_finish->buttons = array(
        'prev' => array(
          'value' => 'Back'
        )
      );
      
    }

    $step_finish->message = $message;
    
    ob_start();
    $step_finish->display();
    $step_finish->buttons();
    
    return array(
      'next_step' => ob_get_clean(),
      'ok' => true
    );
  
  }

}

$this->register_step('FV_Player_Wizard_Step_3_Test_Replace');