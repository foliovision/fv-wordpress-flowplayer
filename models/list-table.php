<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FV_Player_List_Table extends WP_List_Table {

  public $args;

  public $base_url;

  public $counts;

  private $post_types;

  public $total_impressions = 0;

  public $total_clicks = 0;

  public $total_items = 0;

  private $dropdown_cache = false;

  public function __construct( $args = array() ) {
    $this->args = $args;

    parent::__construct( array(
      'singular' => 'Log entry',
      'plural'   => 'Log entries',
      'ajax'     => false,
      'screen'   => !empty($args['screen']) ? $args['screen'] : false
    ) );

    // initialize video and video meta objects, so if there are no video tables created in the DB,
    // we'll create them now (and no SQL errors will be displayed on the listing page)
    new FV_Player_Db_Video(-1);
    new FV_Player_Db_Video_Meta(-1);

    $this->process_bulk_action();
    $this->base_url = admin_url( 'admin.php?page=fv_player' );
  }

  public function advanced_filters() {
    if ( ! empty( $_REQUEST['orderby'] ) )
      echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( $_REQUEST['orderby'] ) ) . '" />';
    if ( ! empty( $_REQUEST['order'] ) )
      echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( $_REQUEST['order'] ) ) . '" />';
    ?>
    <p class="search-box">
      <label class="screen-reader-text" for="fv_player_search">Search players:</label>
      <input type="search" id="fv_player_search" name="s" value="<?php _admin_search_query(); ?>" />
      <?php submit_button( "Search players", 'button', false, false, array('ID' => 'search-submit') ); ?>
    </p>
    <?php
  }

  function get_columns() {
    return self::get_columns_worker();
  }

  public static function get_columns_worker() {
    $cols = array(
      //'cb'             => '<input type="checkbox" />',
      'id'               => __(  'Player', 'fv-player' ),
      'player_name'      => __(  'Player Name', 'fv-player' ),
      'date_created'     => __(  'Date', 'fv-player' ),
      'author'         => __(  'Author', 'fv-player' ),
      'thumbs'           => __(  'Videos', 'fv-player' ),
      'subtitles_count'  => __(  'Subtitles', 'fv-player' ),
      'chapters_count'   => __(  'Chapters', 'fv-player' ),
      'transcript_count' => __(  'Transcript', 'fv-player' ),
      'embeds'           => __(  'Embedded on', 'fv-player' )
    );

    global $fv_fp;
    if( $fv_fp->_get_option('video_stats_enable') ) {
      $cols['stats_play'] = __(  'Plays', 'fv-player' );
    }

    return $cols;
  }

  public function get_sortable_columns() {
    return array(
      'id'               => array( 'id', true ),
      'author'           => array( 'author', true ),
      'player_name'      => array( 'player_name', true ),
      'date_created'     => array( 'date_created', true ),
      // TODO: Disabled due to poor performance
      /*'subtitles_count'  => array( 'subtitles_count', true ),
      'chapters_count'   => array( 'chapters_count', true ),
      'transcript_count' => array( 'transcript_count', true )*/
    );
  }

  protected function get_primary_column_name() {
    return 'id';
  }

  function get_user_dropdown( $user_id, $name = false, $disabled = false ) {
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
  }

  public function column_cb( $player ) {
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />',
      'log_id',
      $player->id
    );
  }

  public function column_default( $player, $column_name ) {
    $id = $player->id;

    // TODO: This should be done in a sensible way
    // if any of the videos for this player contain a coconut_processing_ placeholder,
    // try to run Coconut's job check, so we can update that SRC if it was already
    // processed
    if ( function_exists( 'FV_Player_Coconut' ) ) {
      foreach ( $player->video_objects as $video_object ) {
        if ( strpos( $video_object->getSrc(), 'coconut_processing_' ) !== false ) {
          FV_Player_Coconut()->jobs_check();
          break;
        }
      }
    }

    switch ( $column_name ) {
      case 'id':
        $value = '<span class="fv_player_id_value" data-player_id="'. $id .'">' . $id . '</span>';
        break;
      case 'date_created' :
        $value = $player->date_created > 0 ? "<abbr title='$player->date_created'>".gmdate('Y/m/d',strtotime($player->date_created))."</abbr>" : false;
        break;
      case 'player_name' :
        $name = $player->player_name ? $player->player_name : join( ', ', $player->video_titles );
        $name = flowplayer::filter_possible_html($name);
        if ( 'published' !== $player->status ) {
          $name .= ' (' . ucfirst( $player->status ) . ')';
        }

        $value = "<a href='#' class='fv-player-edit' data-player_id='{$id}'>" . $name . "</a>\n";
        $value .= "<div class='row-actions'>";
        $value .= "<a href='#' class='fv-player-edit' data-player_id='{$id}'>Edit</a> | ";
        $value .= "<a href='#' class='fv-player-export' data-player_id='{$id}' data-nonce='".wp_create_nonce('fv-player-db-export-'.$id)."'>Export</a> | ";
        $value .= "<a href='#' class='fv-player-clone' data-player_id='{$id}' data-nonce='".wp_create_nonce('fv-player-db-export-'.$id)."'>Clone</a> | ";
        $value .= "<span class='trash'><a href='#' class='fv-player-remove' data-player_id='{$id}' data-nonce='".wp_create_nonce('fv-player-db-remove-'.$id)."'>Delete</a></span>";

        $value .= '<br /><input type="text" class="fv-player-shortcode-input" readonly value="'.esc_attr('[fvplayer id="'. $id .'"]').'" style="display: none" /><a href="#" class="button fv-player-shortcode-copy">Copy Shortcode</a>';

        $value .= apply_filters( 'fv_player_list_table_extra_row_actions', '', $id, $player );

        $value .= "</div>\n";
        break;
      case 'embeds':
        $value = '';

        foreach( $player->embeds AS $post_id => $post ) {

          $title = !empty($post->post_title) ? $post->post_title : '#'.$post->ID;
          if( $post->post_status != 'publish' ) {
            $title .= ' ('.$post->post_status.')';
          }

          $value .= '<li><a href="'.get_permalink($post).'" target="_blank">'.$title.'</a></li>';
        }

        if( $value ) $value = '<ul>'.$value.'</ul>';

      break;
      case 'author':
        $value = '<a href="#">'.get_the_author_meta( 'user_nicename' , $player->author ).'</a>';
        break;
      case 'stats_play':
        $value= '';
        if( $player->stats_play ) $value = '<a href="'. admin_url( 'admin.php?page=fv_player_stats&player_id=' . $id ) .'" target="_blank">'. $player->stats_play .'</a>';
        break;
      case 'thumbs':
        $value = join( ' ', $player->thumbs );
        break;
      default:
        $value = isset($player->$column_name) && $player->$column_name ? $player->$column_name : '';
        break;

    }

    // Use manage_toplevel_page_fv_player_columns to add new columns and what's below to add content
    do_action( "manage_toplevel_page_fv_player_custom_column", $column_name, $player );

    return $value;
  }

  public function get_bulk_actions() { // todo: any bulk action?
    return array();
  }

  public function get_views() {
    $current = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : 'all';


    if ( ! empty( $_GET['post_type'] ) ) {
      // Remove the taxonomy arg from the URL
      $post_type_taxonomies = fv_player_get_post_type_taxonomies( sanitize_key( $_GET['post_type'] ) );

      foreach ( $post_type_taxonomies AS $tax ) {
        if ( ! empty( $_GET[ $tax ] ) ) {
          $url = add_query_arg( array(
            $tax => false
          ) );
        }
      }
    }

    foreach( $this->post_types AS $k => $v ) {

      // Omit post types which have no player
      if ( 'all' != $k && ( !isset( $v->player_count ) || $v->player_count === 0 ) ) {
        continue;
      }

      $count = $v->player_count;

      $url_post_type = 'all' != $k ? $k : false;

      $url = add_query_arg( array(
        'post_type' => $url_post_type,
        'paged' => false
      ), isset($url) ? $url : false );

      $class = $current == $k ? ' class="current"' : '';

      $views['post_type-'.$k] = sprintf( '<a href="%s"%s>%s</a>', $url, $class, $v->label .  '&nbsp;<span class="count">('.number_format($count).')' );
    }

    return $views;
  }

  public function process_bulk_action() {  // todo: any bulk action?
    return;
  }

  public function get_result_counts() {
    global $FV_Player_Db;
    $this->total_items = $FV_Player_Db->getListPageCount();

    $all_post_type = new stdClass;
    $all_post_type->label = 'All';

    $total_count = array();

    // Get counts per post type and only do this once
    if ( empty( $this->post_types ) ) {
      $this->post_types = array( 'all' => $all_post_type );

      $this->post_types = array_merge( $this->post_types, get_post_types( array( 'public' => true ), 'objects' ) );

      // Get post IDs, post types and post statuses for each player
      // Each player might be in multiple posts
      global $wpdb;
      $player_to_post_id_to_post_type = $wpdb->get_results( "SELECT pl.id, pm.meta_value, p.ID, p.post_type, p.post_status FROM {$wpdb->prefix}fv_player_players AS pl LEFT JOIN {$wpdb->prefix}fv_player_playermeta AS pm ON pl.id = pm.id_player AND pm.meta_key = 'post_id' LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.meta_value" );

      $no_post_attached = 0;

      foreach ( $player_to_post_id_to_post_type AS $v ) {
        // Only count for the post type if it's not in Trash
        if ( 'trash' != $v->post_status ) {
          foreach ( $this->post_types AS $post_type => $post_type_details ) {
            if ( $v->post_type == $post_type ) {
              if ( !isset( $this->post_types[ $post_type ]->player_count ) ) {
                $this->post_types[ $post_type ]->player_count = 0;
              }
              $this->post_types[ $post_type ]->player_count++;
            }
          }
        }

        // Player may not be used in any post
        if ( ! $v->meta_value ) {
          $no_post_attached++;
        }

        $total_count[ $v->id ] = true;
      }

      if( $no_post_attached ) {
        $no_post_type = new stdClass;
        $no_post_type->label = 'None';
        $no_post_type->player_count = $no_post_attached;

        $this->post_types['none'] = $no_post_type;
      }

      $this->post_types[ 'all' ]->player_count = count( $total_count );
    }

    if ( ! empty( $_GET['post_type'] ) ) {
      $post_type = sanitize_key( $_GET['post_type'] );
      if ( ! empty( $this->post_types[ $post_type ] ) ) {
        $this->total_items = $this->post_types[ $post_type ]->player_count;
      }
    }
  }

  public function get_data() {
    $current = !empty($_GET['paged']) ? intval($_GET['paged']) : 1;
    $order = !empty($_GET['order']) ? sanitize_key($_GET['order']) : 'desc';
    $order_by = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date_created';
    $single_id = !empty($_GET['id']) ? absint($_GET['id']) : null;
    $search = !empty($_GET['s']) ? sanitize_text_field( $_GET['s'] ) : null;
    $post_type = !empty($_GET['post_type']) ? sanitize_key( $_GET['post_type'] ) : null;

    if(!empty($this->args['player_id'])) $single_id = $this->args['player_id'];

    $per_page = $this->args['per_page'];
    $offset = ( $current - 1 ) * $per_page;

    $args = array(
      'offset'    => $offset,
      'order'     => $order,
      'order_by'  => $order_by,
      'player_id' => $single_id,
      'per_page'  => $per_page,
      'post_type' => $post_type,
      'search'    => $search,
    );

    // Add any know taxonomy to the arguments
    if( $post_type ) {
      $post_type_taxonomies = fv_player_get_post_type_taxonomies( $post_type );

      foreach ( $post_type_taxonomies AS $tax ) {
        if ( !empty( $_GET[ $tax ] ) ) {
          $args[ 'tax_'.$tax ] = sanitize_key( $_GET[ $tax ] );
        }
      }
    }

    global $FV_Player_Db;
    return $FV_Player_Db->getListPageData( $args );
  }

  public function prepare_items() {

    wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

    $data     = $this->get_data();

    $this->get_result_counts();

    $this->items = $data;

    $this->set_pagination_args( array(
        'total_items' => $this->total_items,
        'per_page'    => $this->args['per_page'],
        'total_pages' => ceil( $this->total_items / $this->args['per_page'] ),
    ) );
  }

}
