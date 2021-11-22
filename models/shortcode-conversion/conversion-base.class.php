<?php

abstract class FV_Player_Conversion_Base {

  protected $matchers = array();
  protected $title = 'Conversion';
  protected $slug = 'conversion-slug';
  protected $screen;

  abstract function convert_one($post);

  abstract function get_posts_with_shortcode( $offset, $limit );

  abstract function get_count();

  abstract function conversion_button();

  function __construct( $args ) {
    $this->title = $args['title'];
    $this->slug = $args['slug'];
    $this->matchers = $args['matchers'];
    $this->screen = 'fv_player_conversion_' . $this->slug;

    add_action('admin_menu', array( $this, 'admin_page' ) );
    add_action( 'wp_ajax_'. $this->screen, array( $this, 'ajax_convert') );
    add_action( 'fv_player_conversion_buttons', array( $this, 'conversion_button') );
  }

  function admin_page() {
    add_submenu_page( 'fv_player', 'FV Player Migration', 'FV Player Migration', 'install_plugins', $this->screen, array($this, 'conversion_screen') );
    remove_submenu_page( 'fv_player', $this->screen );
  }

  function ajax_convert() {
    if ( current_user_can( 'install_plugins' ) && check_ajax_referer( $this->screen ) ) {
      $html = []; // output html for conversion screen

      $offset = intval($_POST['offset']);
      $offset = 0 + intval($_POST['offset2']) + $offset;
      $limit = intval($_POST['limit']);

      $total = $this->get_count();

      $posts = $this->get_posts_with_shortcode( $offset, $limit );

      $start = microtime(true);

      foreach( $posts AS $post ) {
        $result = $this->convert_one($post);
        // mark post if conversion failed
        if( !$result['all_passed'] ) {
          // TODO: Use some new key which will only have the failed conversions in the post in it
          update_post_meta( $post->ID, '_fv_player_conversion_failed', implode(',', $result['table_rows']) );
        }

        // TODO: Convert array to table
        $html = array_merge( $html, $result['table_rows'] );

        $post_id = wp_update_post( array( 'ID' => $post->ID, 'post_content' => $result['new_content'] ) );
      }

      $percent_done = round ( (($offset + $limit) / $total) * 100 );
      $left = $total - ($offset + $limit);

      echo json_encode(
        array(
          'timing' => microtime(true) - $start,
          'table_rows' => implode( "\n", $html ),
          'percent_done' => $percent_done,
          'left' => $left
        )
      );
    }

    die();
  }

  function conversion_screen() {
    global $fv_wp_flowplayer_ver;
    wp_enqueue_script('fv-player-convertor', flowplayer::get_plugin_url().'/js/admin-shortcode-convertor.js', array('jquery'), $fv_wp_flowplayer_ver );

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
        <p>
          <input type="hidden" name="action" value="rebuild" />
          <input class="button-primary" type="submit" name="convert" value="Convert shortcodes" />
        </p>
        <div id="wrapper" style="display: none"><div id="progress"></div></div>
        <div id="loading" style="display: none"></div>
        <table class="wp-list-table widefat fixed striped table-view-list posts">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Post Type</th>
              <th>Shortcode</th>
              <th>Result</th>
            </tr>
          </thead>
          <tbody id="output">

          </tbody>
        </table>
      </div>

      <script type="text/javascript" charset="utf-8">
        jQuery(function() {
          jQuery('#wrapper').Progressor( {
            action:   '<?php echo $this->screen ?>',
            start:    jQuery('input[name=convert]'),
            cancel:   '<?php echo 'Cancel'; ?>',
            url:      '<?php echo admin_url('admin-ajax.php') ?>',
            nonce:    '<?php echo wp_create_nonce($this->screen)?>',
            finished: '<?php echo 'Finished'; ?>'
          });
        });
      </script>
    <?php
  }

}