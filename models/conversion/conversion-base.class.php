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
    $this->help = $args['help'];
    $this->slug = $args['slug'];
    $this->matchers = $args['matchers'];
    $this->screen = 'fv_player_conversion_' . $this->slug;

    add_action('admin_menu', array( $this, 'admin_page' ) );
    add_action( 'wp_ajax_'. $this->screen, array( $this, 'ajax_convert') );
    add_action( 'fv_player_conversion_buttons', array( $this, 'conversion_button') );

    if( isset($_GET['fv-conversion-export']) && !empty($_GET['page']) && $_GET['page'] === $this->screen ) {
      add_action('admin_init', array( $this, 'csv_export' ) );
    }
  }

  function admin_page() {
    add_submenu_page( 'fv_player', 'FV Player Migration', 'FV Player Migration', 'install_plugins', $this->screen, array($this, 'conversion_screen') );
    remove_submenu_page( 'fv_player', $this->screen );
  }

  function ajax_convert() {
    if ( current_user_can( 'install_plugins' ) && check_ajax_referer( $this->screen ) ) {
      
      if( function_exists( 'FV_Player_Pro' ) ) {
        // takes too long to save if not removed
        remove_filter( 'content_save_pre', array( FV_Player_Pro(), 'save_post' ), 10 );
      }

      $conversions_output = array();
      $convert_error = false;
      $html = array();

      $offset = intval($_POST['offset']);
      $offset = 0 + intval($_POST['offset2']) + $offset;
      $limit = intval($_POST['limit']);

      $posts = $this->get_posts_with_shortcode( $offset, $limit );

      $total = $this->get_count();

      foreach( $posts AS $post ) {
        $result = $this->convert_one($post);
        // mark post if conversion failed
        if( !empty( $result['errors'] ) ) {
          update_post_meta( $post->ID, '_fv_player_' . $this->slug . '_failed', $result['errors'] );
          $convert_error = true;
        } else {
          if( $result['content_updated'] ) {
            // no problem, unmark
            delete_post_meta( $post->ID, '_fv_player_' . $this->slug . '_failed' );
          }
        }

        $conversions_output = array_merge( $conversions_output, $result['output_data'] );

        if( $this->is_live() && $result['content_updated'] ) {
          wp_update_post( array( 'ID' => $post->ID, 'post_content' => $result['new_content'] ) );
        }
      }

      $percent_done = $total > 0 ? round ( (($offset + $limit) / $total) * 100 ) : 0;
      $left = $total - ($offset + $limit);

      // build html output
      foreach( $conversions_output as $output_data ) {
        $html[] = "<tr><td><a href='" . get_edit_post_link( $output_data['ID'] ) . "' target='_blank'> #". $output_data['ID'] . "</a></td><td><a href='" . get_permalink( $output_data['ID'] ) ."' target='_blank'>". $output_data['title'] . "</a></td><td>" . $output_data['type'] . "</td><td>". $output_data['shortcode'] . "</td><td>" . $output_data['output'] . "</td><td>" . $output_data['error'] . "</td></tr>";
      }
      
      if( empty($html) && $percent_done == 0 ) {
        $html[] = "<tr><td colspan='6'>No matching players found.</td></tr>";
      }

      // response
      echo json_encode(
        array(
          'table_rows' => implode( "\n", $html ),
          'percent_done' => $percent_done,
          'left' => $left,
          'convert_error' => $convert_error
        )
      );
    }

    die();
  }

  function csv_export() {
    if( !current_user_can('install_plugins') ) return;

    global $wpdb;

    $filename = $this->slug . '-export-' . date('Y-m-d') . '.csv';

    header('Content-type: text/csv');
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    $meta_key = '_fv_player_' . $this->slug . '_failed';

    $sql = $wpdb->prepare( "SELECT {$wpdb->postmeta}.meta_value FROM {$wpdb->postmeta} JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->postmeta}.meta_key = '%s' ORDER BY {$wpdb->posts}.post_date_gmt DESC ", $meta_key );

    $results = $wpdb->get_col( $sql );

    if( !empty( $results ) ) {
      $fp = fopen('php://output', 'wb');

      $header = array('ID','Title','Post-Link','Edit-Link','Shortcode','Message');

      fputcsv($fp, $header);

      foreach( $results as $result ) {
        $unserialized = unserialize( $result );
  
        foreach( $unserialized as $row ) {
          $row['post_link'] = htmlspecialchars_decode( $row['post_link'] );
          $row['post_edit'] = htmlspecialchars_decode( $row['post_edit'] );
          fputcsv($fp, $row);
        }
      }

      fclose($fp);
    }

    die();
  }

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
        <h1><?php echo $this->title; ?></h1>
        <?php echo wpautop($this->help); ?>
        <p>
          <input type="hidden" name="action" value="rebuild" />
          
          <?php // This checkbox shows the JS confirmation box when clicked to enable ?>
          <input type="checkbox" name="make-changes" id="make-changes" value="1" onclick="if( this.checked ) return confirm('<?php _e('Please make sure you backup your database before continuing. You can use post revisions to get back to previous version of your posts as well.', 'fv-wordpress-flowplayer') ?>') " /> <label for="make-changes">Make changes</label>

          <input class="button-primary" type="submit" name="convert" value="Start" />

          <img id="loading" width="16" height="16" src="<?php echo site_url('wp-includes/images/wpspin-2x.gif'); ?>" style="display: none" />
        </p>

        <p>
        <a id="export" href="<?php echo admin_url('admin.php?page=' . $this->screen .'&fv-conversion-export=1');?>" style="display: none" >Export errors to csv file</a>
        </p>

        <div id="wrapper" style="display: none"><div id="progress"></div></div>

        <table class="wp-list-table widefat fixed striped table-view-list posts">
          <thead>
            <tr>
              <th style="width: 5em">ID</th>
              <th>Title</th>
              <th style="width: 5em">Post Type</th>
              <th>Shortcode</th>
              <th>Result</th>
              <th>Error</th>
            </tr>
          </thead>
          <tbody id="output"></tbody>
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
  
  function is_live() {
    return !empty($_POST['make-changes']) && $_POST['make-changes'] == 'true';
  }

}