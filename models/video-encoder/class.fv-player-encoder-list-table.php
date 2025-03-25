<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FV_Player_Encoder_List_Table extends WP_List_Table {

  private
    $args,
    $total_items = 0,
    //$dropdown_cache = false,
    $encoder_id = '',
    $table_name = '';

  public $base_url;

  public function __construct( $args = array() ) {
    $this->args = wp_parse_args( $args, array( 'per_page' => 25 ) );

    if ( empty($args['encoder_id']) ) {
      throw new Exception('Constructor to FV_Player_List_Table is missing "encoder_id" key in its $args array.');
    }

    if ( empty($args['table_name']) ) {
      throw new Exception('Constructor to FV_Player_List_Table is missing "table_name" key in its $args array.');
    }

    parent::__construct( array(
      'singular' => 'Job',
      'plural'   => 'Jobs',
      'ajax'     => false,
    ) );

    $this->encoder_id = $args['encoder_id'];
    $this->table_name = $args['table_name'];
    $this->get_result_counts();
    $this->process_bulk_action();
    $this->base_url = admin_url( 'admin.php?page=' . $this->encoder_id );
  }

  public function advanced_filters() {
    ?>
    <style>
    #fv-player-filter table {
      table-layout: auto;
    }
    .hover-wrap { position: relative }
    .hover-details {
      position: absolute;
      display: none;
      background: white;
      border: 1px solid lightgray;
      padding: 1em;
      right: 0;
      top: 2em;
      width: 50em;
      z-index: 9;
    }
    pre {
      white-space: pre-wrap;       /* css-3 */
      white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
      white-space: -pre-wrap;      /* Opera 4-6 */
      white-space: -o-pre-wrap;    /* Opera 7 */
      word-wrap: break-word;       /* Internet Explorer 5.5+ */
    }

    .tooltiptext {
      visibility: hidden;
      width: 120px;
      background-color: black;
      color: #fff;
      text-align: center;
      border-radius: 6px;
      padding: 5px 0;

      /* Position the tooltip */
      position: absolute;
      z-index: 1;
    }

    .cannot-trash:hover .tooltiptext {
      visibility: visible;
    }

    .cannot-trash a {
      color: gray;
      cursor: not-allowed;
    }
		</style>
    <?php
    if ( ! empty( $_REQUEST['orderby'] ) )
      echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_key( $_REQUEST['orderby'] ) ) . '" />';
    if ( ! empty( $_REQUEST['order'] ) )
      echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_key( $_REQUEST['order'] ) ) . '" />';

    ?>
    <p class="search-box">
      <label class="screen-reader-text" for="fv_player_encoding_jobs_search">Search jobs:</label>
      <input type="search" id="fv_player_encoding_jobs_search" name="s" value="<?php _admin_search_query(); ?>" />
      <?php submit_button( "Search jobs", 'button', false, false, array('ID' => 'search-submit') ); ?><br/>
    </p>
    <?php
  }

  public function get_sortable_columns() {
    return array(
      'id'               => array( 'id', true ),
      'date_created'     => array( 'date_created', true ),
      // 'player_id'        => array( 'player_id', true ),
      'source'           => array( 'source', true ),
      'target'           => array( 'target', true ),
      'status'           => array( 'status', true ),
      'author'           => array( 'author', true )
    );
  }

  protected function get_primary_column_name() {
    return 'id';
  }

  /*function get_user_dropdown( $user_id, $name = false, $disabled = false ) {
    if( !$this->dropdown_cache ) {
      $this->dropdown_cache  = wp_dropdown_users( array(
        'name' => 'user_id',
        'role__not_in' => array('subscriber'),
        'show_option_none' => 'All users',
        'echo' => false
      ) );
    }
    $html = $this->dropdown_cache;

    $html = str_replace("value='".$user_id."'>","value='".$user_id."' selected>",$html);
    if( $name ) $html = str_replace("name='user_id' ","name='".$name."' ' ",$html);
    if( $disabled ) $html = str_replace("<select ","<select disabled='disabled' ",$html);

    return $html;
  }*/

  public function column_cb( $player ) {
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />',
      'log_id',
      $player->id
    );
  }

  public function column_default( $job, $column_name ) {

    $value = '';

    switch ( $column_name ) {
      case 'id' :
        $value = '<a href="#"">'.$job->id.'</a>';
        if( isset($job->delete_confirmation_message) && !isset( $job->player_id ) ) { // show delete only id not in player
          $value .='<div class="row-actions"><span class="trash"><a href="#" class="job-delete delete-hover" data-message="'. $job->delete_confirmation_message .'" data-nonce="'.wp_create_nonce( 'fv-player-encoder-delete-job-'.$job->id ).'" data-id="'.$job->id.'">Delete</a></span></div>';
        } else {
          $value .= '<div class="row-actions"><span class="cannot-trash"><a class="delete-hover">Delete</a><span class="tooltiptext">Cannot delete the job because video is embeded in player</span></div>';
        }
        break;
      case 'date_created' :
        $value = $job->date_created > 0 ? "<abbr title='$job->date_created'>".gmdate('Y/m/d',strtotime($job->date_created))."</abbr>" : false;
        break;
      case 'source':

        // The video source URL might have the URL signature on it, so we need to strip it off to make it easy to read
        $source_no_query_string = preg_replace( '~\?.+$~', '', $job->$column_name );

        // The video source URL might require the URL signature to allow opening, so we add that here
        $source_signed = apply_filters( 'fv_flowplayer_video_src', $source_no_query_string, array( 'dynamic' => true ) );

        // No signature added? Then use the original URL for link
        if ( strcmp( $source_signed, $source_no_query_string ) === 0 ) {
          $source_signed = $job->$column_name ;
        }

        $value = "<a href='" . esc_attr( $source_signed ) . "' target='_blank'>" . $source_no_query_string . "</a>";
        break;
      case 'status':
        $error = !empty($job->error) ? "<p><b>".$job->error."</b></p>" : "";
        $value = "<div class='hover-wrap'><a href='#'>".$job->status. ( $job->status == 'processing' ? " " . $job->progress : "" ) ."</a>";
        if( $job->status == 'processing' ) {
          $value .= ' <img data-fv-player-wizard-indicator width="16" height="16" src="'.site_url('wp-includes/images/wpspin-2x.gif').'" />';
        }
        $value .= "<div class='hover-details'>".$error."<h4>Response:</h4><pre>".self::json_prettyPrint($job->result)."</pre><h4>Output:</h4><pre>".self::json_prettyPrint($job->output)."</pre></div></div>";
        break;
      case 'target':
        $value = "<div class='hover-wrap'><a href='#'>".$job->target."</a><div class='hover-details'><pre>" . $this->json_prettyPrint( $job->args ) . "</pre></div></div>";
        break;
      case 'author':
        $value = '<a href="#">'.get_the_author_meta( 'user_nicename' , $job->author ).'</a>';
        break;
      case 'fv_player_encoding_category_id':
        $term = get_term( $job->fv_player_encoding_category_id, 'fv_player_encoding_category' );
        if ( $term && ! is_wp_error( $term ) ) {
          $value = $term->name;
          $parent = $term->parent;
          while( $parent ) {
            $term = get_term( $parent, 'fv_player_encoding_category' );
            $value = $term->name . ' > ' . $value;
            $parent = $term->parent;
          }
        }
        break;
      default:
        $value = isset($job->$column_name) && $job->$column_name ? $job->$column_name : '';
        break;
    }

    return $value;
  }

  private function json_prettyPrint( $json ) {
      $result = '';
      $level = 0;
      $in_quotes = false;
      $in_escape = false;
      $ends_line_level = NULL;
      $json_length = strlen( $json );

      for( $i = 0; $i < $json_length; $i++ ) {
          $char = $json[$i];
          $new_line_level = NULL;
          $post = "";
          if( $ends_line_level !== NULL ) {
              $new_line_level = $ends_line_level;
              $ends_line_level = NULL;
          }
          if ( $in_escape ) {
              $in_escape = false;
          } else if( $char === '"' ) {
              $in_quotes = !$in_quotes;
          } else if( ! $in_quotes ) {
              switch( $char ) {
                  case '}': case ']':
                      $level--;
                      $ends_line_level = NULL;
                      $new_line_level = $level;
                      break;

                  case '{': case '[':
                      $level++;
                  case ',':
                      $ends_line_level = $level;
                      break;

                  case ':':
                      $post = " ";
                      break;

                  case " ": case "\t": case "\n": case "\r":
                      $char = "";
                      $ends_line_level = $new_line_level;
                      $new_line_level = NULL;
                      break;
              }
          } else if ( $char === '\\' ) {
              $in_escape = true;
          }
          if( $new_line_level !== NULL ) {
              $result .= "\n".str_repeat( "  ", $new_line_level );
          }
          $result .= $char.$post;
      }

      return $result;
  }

  public function get_bulk_actions() { // todo: any bulk action?
    return array();
  }

  public function process_bulk_action() {  // todo: any bulk action?
    return;
  }

  private function get_result_counts() {
    global $wpdb;

    if( !empty($_GET['s']) ) {
      $this->total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE type = %s AND source LIKE %s", $this->encoder_id, '%' . $wpdb->esc_like( sanitize_text_field( $_GET['s'] ) ) . '%' ) );

    } else {
      $this->total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE type = %s", $this->encoder_id ) );
    }
  }

  public function get_columns() {
    return array(
      'id'               => __( 'ID' ),
      'date_created'     => __( 'Created' ),
      'source'           => __( 'Source' ),
      'target'           => __( 'Target' ),
      'status'           => __( 'Status' ),
      'author'           => __( 'Author' ),
      'player_id'        => __( 'Player ID' ),
      'fv_player_encoding_category_id' => __( 'Video Category' )
    );
  }

  private function get_data($id = false, $args = false ) {
    if( !$args ) {
      $args = array();
      if ( ! empty( $_GET['exclude'] ) ) $args['exclude'] = sanitize_text_field( $_GET['exclude'] );
      if ( ! empty( $_GET['order'] ) ) $args['order'] = sanitize_key( $_GET['order'] );
      if ( ! empty( $_GET['orderby'] ) ) $args['orderby'] = sanitize_key( $_GET['orderby'] );
      if ( ! empty( $_GET['paged'] ) ) $args['paged'] = absint( $_GET['paged'] );
      if ( ! empty( $_GET['status'] ) ) $args['status'] = sanitize_key( $_GET['status'] );
      if ( ! empty( $_GET['s'] ) ) $args['s'] = sanitize_text_field( $_GET['s'] );
    }

    $args = wp_parse_args( $args, array(
      'exclude' => false,
      'order' => 'desc',
      'orderby' => 'date_created',
      'paged' => 1,
      'status' => false,
      's' => false
    ));

    $aWhere = array();
    $aWhere[] = "type = '{$this->encoder_id}'";

    if( is_array($id) ) {
      $id = array_map('intval', $id);
      $aWhere[] = 'id IN ('.implode(',',$id).')';
    } else if( $id ) {
      $aWhere[] = 'id = '.intval($id);
    }

    if( $args['exclude'] ) {
      $args['exclude'] = array_map( 'intval', $args['exclude'] );
      $aWhere[] = 'id NOT IN ('.implode(',',$args['exclude']).')';
    }

    if( $args['status'] == 'pending' ) $aWhere[] = "status = 'pending'";
    if( $args['status'] == 'complete' ) $aWhere[] = "status = 'complete'";
    if( $args['status'] == 'error' ) $aWhere[] = "status = 'error'";

    global $wpdb;

    if( $args['s'] ) {
      $aWhere[] = $wpdb->prepare( "source LIKE %s", '%' . $wpdb->esc_like( $args['s'] ) . '%' );
    }

    $where = count($aWhere) ? " WHERE ".implode( " AND ", $aWhere ) : "";

    $order = in_array( $args['order'], array( 'asc', 'desc' ) ) ? $args['order'] : 'desc';
    $order_by = in_array( $args['orderby'], array( 'id', 'date_descted', 'source', 'target', 'status', 'author' ) ) ? $args['orderby'] : 'date_created';

    $per_page = intval($this->args['per_page']);
    $offset = ( $args['paged'] - 1 ) * $per_page;

    $results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM `{$wpdb->prefix}fv_player_encoding_jobs` $where ORDER BY $order_by $order LIMIT %d, %d",
        $offset,
        $per_page
      )
    );

    // get embeded players using id from job
    $playlist_embed = $wpdb->get_results( "SELECT j.id, m.id_video, p.id AS player_id FROM `{$wpdb->prefix}fv_player_encoding_jobs` AS j
      JOIN `{$wpdb->prefix}fv_player_videometa` AS m ON j.id = m.meta_value
      JOIN `{$wpdb->prefix}fv_player_players` AS p ON find_in_set(p.videos,m.id_video) > 0
      WHERE m.meta_key = 'encoding_job_id'" );

    // add player id(s) to results
    foreach( $results AS $key => $row ) {
      foreach ( $playlist_embed as $key2 => $row2 ) {
        if( $row->id == $row2->id ) {
          if( !isset($results[$key]->player_id) ) {
            $results[$key]->player_id = "". $row2->player_id;
          } else {
            $results[$key]->player_id .= ",". $row2->player_id;
          }
        }
      }

      $args = json_decode($row->args ,true);

      $message = array();

      // Get target, host & bucket for message when deleting job
      if( !empty($args['storage']['endpoint']) ) {
        $message[] = 'Host: ' .$args['storage']['endpoint'];
      }

      if( !empty($args['storage']['bucket']) ) {
        $message[] = 'Space/Bucket: ' .$args['storage']['bucket'];
      }

      if( isset($args['outputs']['httpstream']) ) {
        $message[] = 'Folder: ' .$args['outputs']['httpstream']['hls']['path'];
      } else if ( isset($args['outputs']['httpstream#above4k']) ) {
        $message[] = 'Folder: ' .$args['outputs']['httpstream#above4k']['hls']['path'];
      }

      if( $this->encoder_id == 'bunny_stream' && isset($args['target']) ) {
        $message[] = 'Folder: ' . $args['target'];
      }

      if( count( $message ) ) {
        $results[$key]->delete_confirmation_message = "\n\n".implode( "\n\n", $message );
      }

    }

    return $results;
  }

  public function prepare_items( $id = false, $args = false ) {
    $columns  = $this->get_columns();
    $hidden   = array(); // No hidden columns
    $sortable = $this->get_sortable_columns();
    $this->items     = $this->get_data( $id, $args );

    $this->_column_headers = array( $columns, $hidden, $sortable );

    $this->set_pagination_args( array(
        'total_items' => $this->total_items,
        'per_page'    => $this->args['per_page'],
        'total_pages' => ceil( $this->total_item / $this->args['per_page'] ),
      )
    );
  }

}
