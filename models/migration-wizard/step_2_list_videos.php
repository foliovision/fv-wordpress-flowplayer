<?php

class FV_Player_Wizard_Step_2_List_Videos extends FV_Player_Wizard_Step_Base {

  private $search_string; 

  var $buttons = array(
    'prev' => array(
      'value' => 'Adjust your search phrase'
    ),
    'next' => array(
      'value' => 'Test Replace',
      'primary' => true
    )
  ); 

  public function __construct($search_string = 'amazon') {
    $this->search_string = $search_string;
  }

  function display() {
    global $fv_fp;
    ?>
<tr>
  <td colspan="2">
    <h2>Step 2: List of affected videos</h2>

    <?php
    if( $this->search_string ) {
      global $wpdb;
      $videos_data = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, src, src1, src2, splash FROM `{$wpdb->prefix}fv_player_videos` WHERE src LIKE %s OR src1 LIKE %s OR src2 LIKE %s OR splash LIKE %s",
        '%' . $wpdb->esc_like($this->search_string) . '%',
        '%' . $wpdb->esc_like($this->search_string) . '%',
        '%' . $wpdb->esc_like($this->search_string) . '%',
        '%' . $wpdb->esc_like($this->search_string) . '%'
      ) );
      
      if( !empty($videos_data) ) : ?>
        <table class="wp-list-table widefat fixed striped logentries">
          <thead>
            <tr>
              <td>Video ID</td>
              <td>URL</td>
              <td>Alternative URL</td>
              <td>Alternative URL 2</td>
              <td>Splash</td>
            </tr>
          </thead>
          <?php foreach($videos_data as $video) : ?>
            <tr>
              <td><?php echo $video->id ?></td>
              <td><?php echo $this->hilight($video->src); ?></td>
              <td><?php echo $this->hilight($video->src1); ?></td>
              <td><?php echo $this->hilight($video->src2); ?></td>
              <td><?php echo $this->hilight($video->splash); ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
  <input type="hidden" name="search_string" value="<?php echo esc_attr($this->search_string) ?>" >
      <?php endif;
      
    }
    ?>
<tr>
  <td colspan="2">
    <p>Enter the string which should replace <code><?php echo $this->search_string; ?></code>:</p>
  </td>
</tr>
    <?php
    $fv_fp->_get_input_text( array(
      'key' => array('video_src_replace','replace_string'),
      'name' => 'Replace string',
      'class' => 'regular-text code'
    ) );
  }
  
  function hilight( $string ) {
    $string = str_replace( $this->search_string, '<span style="background: #f88">'.$this->search_string.'</span>', $string );
    return $string;
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