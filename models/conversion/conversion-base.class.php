<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

abstract class FV_Player_Conversion_Base {

  /**
   * If set to true, it will convert data to db
   *
   * @var boolean
   */
  public $set_live = false;

  protected $title = 'Conversion';
  protected $slug = 'conversion-slug';
  protected $screen;
  protected $screen_fields = array();
  protected $conversion_done_details =false;
  protected $conversion_limit = 1;
  protected $make_chages_button = true;

  abstract function convert_one($post);

  abstract function get_items( $offset, $limit );

  abstract function conversion_button();

  abstract function iterate_data( $data );

  abstract function build_output_html( $data , $percent_done);

  function __construct( $args ) {
    $this->title  = $args['title'];
    $this->slug   = $args['slug'];
    $this->screen = 'fv_player_conversion_' . $this->slug;

    add_action( 'admin_menu', array( $this, 'admin_page' ) );
    add_action( 'wp_ajax_'. $this->screen, array( $this, 'ajax_convert') );
    add_action( 'fv_player_conversion_buttons', array( $this, 'conversion_button') );

  }

  function admin_page() {
    add_submenu_page( 'fv_player', 'FV Player Migration', 'FV Player Migration', 'install_plugins', $this->screen, array($this, 'conversion_screen') );
    remove_submenu_page( 'fv_player', $this->screen );
  }

  /**
   * Count old data
   *
   * @return int $count
   */
  function get_count() {
    global $wpdb;
    return $wpdb->get_var( "SELECT FOUND_ROWS()" );
  }

  /**
   * Convert data to new format using ajax
   *
   * @return void
   */
  function ajax_convert() {
    if ( current_user_can( 'install_plugins' ) && check_ajax_referer( $this->screen ) ) {
      if( function_exists( 'FV_Player_Pro' ) ) {
        // takes too long to save if not removed
        remove_filter( 'content_save_pre', array( FV_Player_Pro(), 'save_post' ), 10 );
      }

      $convert_error = false;

      $offset = intval($_POST['offset']);
      $offset = 0 + intval($_POST['offset2']) + $offset;
      $limit = intval($_POST['limit']);

      $items = $this->get_items( $offset, $limit );

      $total = $this->get_count();

      // iterate data
      $result = $this->iterate_data( $items );

      $convert_error = $result['convert_error'];
      $conversions_output = $result['conversions_output'];

      $percent_done = $total > 0 ? round ( (($offset + $limit) / $total) * 100 ) : 0;
      $left = $total - ($offset + $limit);

      // build html output
      $html = $this->build_output_html( $conversions_output, $percent_done );

      // check if we are done
      if( $percent_done >= 100 ) {
        // store conversion status
        $options = get_option( 'fvwpflowplayer' );

        if( !isset( $options['conversion'] ) ) {
          $options['conversion'] = array();
        }

        // store current time as finished time
        $options['conversion'][$this->slug] = array( 'date' => gmdate('Y-m-d H:i:s'), 'did_cleanup' => false );
        update_option( 'fvwpflowplayer', $options );
      }

      // response
      echo wp_json_encode(
        array(
          'table_rows' => implode( "\n", $html ),
          'percent_done' => $percent_done,
          'left' => $left,
          'total' => $total,
          'convert_error' => $convert_error,
          'memory_used_peak_mb' => round( memory_get_peak_usage() / 1024 / 1024, 2 ),
          'memory_used_mb' => round( memory_get_usage() / 1024 / 1024, 2 ),
        )
      );
    }

    die();
  }

  /**
   * Create admin page for conversion screen
   *
   * @return void
   */
  function conversion_screen() {
    global $fv_wp_flowplayer_ver;
    wp_enqueue_script('fv-player-convertor', flowplayer::get_plugin_url().'/js/admin-convertor.js', array('jquery'), filemtime( dirname(__FILE__).'/../../js/admin-convertor.js' ) );

    ?>
      <style>
        #wrapper {
          border: 1px solid gray;
          position: relative;
          height: 1em;
          margin-bottom: 1em;
        }
        #progress {
          border-right: 1px solid gray;
          background-color: #800;
          position: absolute;
          left: 0;
          top: 0;
          bottom: 0;
        }
      </style>
      <div class="wrap">
        <h1><?php echo esc_html( $this->title ); ?></h1>

        <?php echo wpautop( $this->get_text( 'help' ) ); ?>
        <p>
          <input type="hidden" name="action" value="rebuild" />

          <?php if( $this->make_chages_button ): // This checkbox shows the JS confirmation box when clicked to enable ?>
            <input type="checkbox" name="make-changes" id="make-changes" value="1" onclick="if( this.checked ) return confirm('<?php echo esc_attr( $this->get_text( 'start_warning' ) ); ?>') " /> <label for="make-changes">Make changes</label>
          <?php endif; ?>

          <input class="button-primary" type="submit" name="convert" value="Start" />

          <img id="loading" width="16" height="16" src="<?php echo esc_attr( site_url('wp-includes/images/wpspin-2x.gif') ); ?>" style="display: none" />
        </p>

        <p>
        <a id="export" href="<?php echo admin_url('admin.php?page=' . $this->screen .'&fv-conversion-export=1');?>" style="display: none" >Export errors to csv file</a>
        </p>

        <div id="wrapper" style="display: none"><div id="progress"></div></div>

        <div class="conversion-done" style="display: none;" style="max-width: 30em; background: white; padding: .5em 1em; margin: 5em auto; box-shadow: 0px 0px 10px 5px rgba(0,0,0,.5)">
          <h1>Conversion done!</h1>

          <?php if( $this->get_text( 'conversion_done_details' ) ): ?>
            <p>
              <?php echo esc_html( $this->get_text( 'conversion_done_details' ) ); ?>
            </p>
          <?php endif; ?>
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list posts">
          <thead>
            <tr>
              <?php foreach( $this->screen_fields as $field ) : ?>
                <th><?php echo esc_html( $field ); ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody id="output"></tbody>
        </table>
      </div>

      <script type="text/javascript" charset="utf-8">
        jQuery(function() {
          jQuery('#wrapper').Progressor( {
            action:   '<?php echo esc_attr( $this->screen ); ?>',
            start:    jQuery('input[name=convert]'),
            cancel:   '<?php echo 'Cancel'; ?>',
            url:      '<?php echo admin_url('admin-ajax.php') ?>',
            nonce:    '<?php echo wp_create_nonce($this->screen)?>',
            finished: '<?php echo 'Finished'; ?>',
            limit:    '<?php echo intval( $this->conversion_limit ); ?>'
          });
        });
      </script>
    <?php
  }

  public function get_text( $type ) {
    if ( 'conversion_done_details' === $type ) {

    } else if ( 'help' === $type ) {

    } else if ( 'start_warning' === $type ) {
      return 'This will convert your data to new format. Please make sure you have a backup of your database before continuing.';
    }

    return false;
  }

  /**
   * Get live status
   *
   * @return boolean
   */
  function is_live() {
    return (!empty($_POST['make-changes']) && sanitize_key( $_POST['make-changes'] ) == 'true') || $this->set_live ;
  }

}
