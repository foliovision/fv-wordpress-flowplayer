<?php

abstract class FV_Player_Wizard_Base_Class {

  private $args = array();

  private $id = false;

  private $page;

  // array of class names based on FV_Player_Wizard_Step_Base_Class
  private $steps = array();

  private $version = '7.4.32.727';

  private $title = false;

  /**
   * Create a new instance of the Wizard class
   * 
   * @param   array $args {
   *                  id          string  Used for WP Ajax
   *                  page        string  the ?page=NAME of the wp-admin page with the Wizard for CSS loadign
   *                  steps_path  string  Path to folder with all the step_*.php Wizard step files
   *                  title       string  Wizard title to display
   * }
   *
   */
  function __construct( $args ) {
    $this->args = $args;
    $this->args['steps_path'] = trailingslashit($this->args['steps_path']);

    $this->set_id($this->args['id']);

    $this->load_steps($this->args['steps_path']);

    $this->page = $this->args['page'];

    $this->set_title($this->args['title']);

    // TODO: Somehow make these work even if self is only loadedd
    // on the wizard screen
    add_action( 'wp_ajax_'.$this->get_id().'_step', array( $this, 'ajax' ) );

    add_action( 'admin_init', array( $this, 'styles' ) );
  }

  /*
   * Accepts a callback function which should use $fv_fp->_get_input_text or similar
   */
  function add_step($step) {
    $this->steps[] = $step;
  }

  function ajax() {
    if( !wp_verify_nonce( $_POST['nonce'], $this->get_id() ) ) {
      wp_send_json( array( 'error' => 'Invalid nonce' ) );
    }

    $class_name = (string)$this->get_step($_POST['step_name']);
    $step = new $class_name;

    $result = call_user_func( array($step,'process') );
    wp_send_json( $result );
  }

  function get_id() {
    return $this->id;
  }

  /**
   * @param   string  $i  Step name to get
   * 
   * @return  string      Step name, if it's indeed added to the list of steps
   */
  function get_step($i) {
    if( is_string($i) && in_array($i, $this->steps, true ) ) {
      return $i;

    }
  }

  function get_steps() {
    return $this->steps;
  }

  function get_title() {
    return $this->title;
  }

  function load_steps($path) {
    $files = glob($path.'/step_*.php');
    if( count($files) > 0 ) {
      foreach( $files AS $file ) {
        include_once($file);
      }
    }
  }

  function log( $args ) {
    $args = wp_parse_args( $args, array(
      'title' => false,
      'message' => false,
      'date' => date('r'),
      'status' => 'info',
      'time' => time()
    ));

    $logs = get_option( $this->log_name(), array() );
    $logs[] = $args;
    update_option( $this->log_name(), $logs, false );
  }

  function log_get() {
    return get_option( $this->log_name(), array() );
  }

  function log_name() {
    return $this->get_id().'-logs';
  }

  function log_show() {
    if( count($this->log_get()) ) : ?>
      <a href='#' class='button' data-fv-wizard-show-log='<?php echo esc_attr($this->get_id()); ?>'>Show wizard log (<?php echo count($this->log_get()); ?>)</a>
      <table data-fv-wizard-log-wrap='<?php echo esc_attr($this->get_id()); ?>' style='display: none'>
      <?php foreach( array_reverse($this->log_get()) AS $log ) :
        $color = '#8f8';
        if( $log['status'] == 'warning' ) {
          $color = '#ff8';
        } else if( $log['status'] == 'error' ) {
          $color = '#f88';
        }
        ?>
        <tr style='background-color: <?php echo htmlentities($color); ?>'>
          <td><?php echo htmlentities( $log['date'] ); ?></td>
          <td><?php echo htmlentities($log['title']); ?></td>
          <td><?php echo htmlentities(print_r($log['message'], true)); ?></td>
        </tr>
      <?php endforeach; ?>
      </table>

      <script>
      jQuery(document).on( 'click', '[data-fv-wizard-show-log]', function() {
        jQuery('[data-fv-wizard-log-wrap='+jQuery(this).data('fv-wizard-show-log')+']').toggle();
        return false;
      });
      </script>
    <?php endif;
  }

  function register_step($class) {
    if( in_array($class,$this->get_steps()) ) {
      echo "<div class='error'><p>Error: Step ".$class." already loaded!</p></div>";
      return;
    }
    $this->add_step($class);
  }

  function show() {
    ?>
    <style>
    .regular-text {
      width: 40em;
    }
    img.fv-player-wizard-logo {
      float: left;
      height: 32px;
      margin-top: -5px;
    }
    </style>
    <div class="fv-player-wizard" data-fv-player-wizard>
      <?php
      echo '<img class="fv-player-wizard-logo" src="'.plugins_url( 'images/logo.png', $this->args['steps_path'].'sample' ).'" /><h1 class="wp-heading-inline">'.$this->get_title().'</h1>';

      $shown_one_step = false; // only show a single step
      $i = 0; // keep track of the step being processed
      $first_step_number = -1; // remember the first visible step number, very important
      foreach( $this->get_steps() AS $class_name ) {
        $step = new $class_name;

        if( !call_user_func( array($step,'should_show') ) ) {
          $i++;
          continue;
        }

        $style = $shown_one_step ? " style='display: none;'" : "";
        $shown_one_step = true;
        
        $extra_fields = "";
        if( $fields = call_user_func( array($step,'extra_fields') ) ) {
          $extra_fields = " data-extra-fields='".json_encode($fields)."'";
        }

        echo "<table class='form-table fv-player-wizard-step' data-step='".intval($i)."' data-step_name='".esc_attr($class_name)."'".$style.$extra_fields.">\n";

        call_user_func( array($step,'display') );

        call_user_func( array($step,'buttons') );
        
        echo "</table>\n";

        call_user_func( array($step,'extra_scripts') );

        if( $first_step_number < 0 ) {
          $first_step_number = $i;
        }

        $i++;
      }
      ?>
    </div>
    <?php

    $this->log_show();

    wp_enqueue_script( 'fv_player_wizard_base', plugins_url('fv-player-wizard-base.js', __FILE__), array('jquery'), $this->version );
    wp_localize_script( 'fv_player_wizard_base', 'fv_player_wizard_base', array(
      'first_step_number' => $first_step_number,
      'id' => $this->id,
      'nonce' => wp_create_nonce( $this->get_id() )
    ));

  }

  function styles() {
    if( !empty($_GET['page']) && strcmp($this->page,$_GET['page']) == 0 ) {
      wp_enqueue_style( 'fv_player_wizard_base', plugins_url('fv-player-wizard-base.css', __FILE__), array(), $this->version );
    }
  }

  function set_id($id) {
    $this->id = $id;
  }

  function set_title($title) {
    $this->title = $title;
  }

}