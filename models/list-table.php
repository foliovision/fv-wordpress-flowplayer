<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FV_Player_List_Table extends WP_List_Table {

  public $args;

  public $base_url;
  
  public $counts;
  
  public $total_impressions = 0;
  
  public $total_clicks = 0;
  
  public $total_items = 0;
  
  private $dropdown_cache = false;

  public function __construct( $args ) {
    $this->args = $args;
    //var_dump($args);
    parent::__construct( array(
      'singular' => 'Log entry',
      'plural'   => 'Log entries',
      'ajax'     => false,
    ) );

    // initialize video and video meta objects, so if there are no video tables created in the DB,
    // we'll create them now (and no SQL errors will be displayed on the listing page)
    new FV_Player_Db_Video(-1);
    new FV_Player_Db_Video_Meta(-1);

    $this->get_result_counts();
    $this->process_bulk_action();
    $this->base_url = admin_url( 'admin.php?page=fv_player' );
  }
  
  public function advanced_filters() {
    if ( ! empty( $_REQUEST['orderby'] ) )
      echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
    if ( ! empty( $_REQUEST['order'] ) )
      echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';

    if (isset($_GET['id'])) {
      $input_id = $_GET['id'];
    } else {
      $input_id = null;
    }
    ?>
    <p class="search-box">
      <label class="screen-reader-text" for="<?php echo $input_id ?>">Search players:</label>
      <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
      <?php submit_button( "Search players", 'button', false, false, array('ID' => 'search-submit') ); ?><br/>
    </p>
    <?php
  }
  
  function get_columns() {
    $cols = array(
      //'cb'             => '<input type="checkbox" />',
      'id'               => __( 'Player', 'fv-wordpress-flowplayer' ),
      'player_name'      => __( 'Player Name', 'fv-wordpress-flowplayer' ),
      'date_created'     => __( 'Date', 'fv-wordpress-flowplayer' ),
      'author'         => __( 'Author', 'fv-wordpress-flowplayer' ),
      'thumbs'           => __( 'Videos', 'fv-wordpress-flowplayer' ),
      'subtitles_count'  => __( 'Subtitles', 'fv-wordpress-flowplayer' ),
      'chapters_count'   => __( 'Chapters', 'fv-wordpress-flowplayer' ),
      'transcript_count' => __( 'Transcript', 'fv-wordpress-flowplayer' ),
      'embeds'           => __( 'Embedded on', 'fv-wordpress-flowplayer' )
    );

    global $fv_fp;
    if( $fv_fp->_get_option('video_stats_enable') ) {
      $cols['stats_play'] = __( 'Plays', 'fv-wordpress-flowplayer' );
    }

    return $cols;
  }

  public function get_sortable_columns() {
    return array(
      'id'               => array( 'id', true ),
      'author'           => array( 'author', true ),
      'player_name'      => array( 'player_name', true ),
      'date_created'     => array( 'date_created', true ),
      'subtitles_count'  => array( 'subtitles_count', true ),
      'chapters_count'   => array( 'chapters_count', true ),
      'transcript_count' => array( 'transcript_count', true )
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
        $value = $player->date_created > 0 ? "<abbr title='$player->date_created'>".date('Y/m/d',strtotime($player->date_created))."</abbr>" : false;
        break;
      case 'player_name' :
        $value = "<a href='#' class='fv-player-edit' data-player_id='{$id}'>".flowplayer::filter_possible_html($player->player_name)."</a>\n";
        $value .= "<div class='row-actions'>";
        $value .= "<a href='#' class='fv-player-edit' data-player_id='{$id}'>Edit</a> | ";
        $value .= "<a href='#' class='fv-player-export' data-player_id='{$id}' data-nonce='".wp_create_nonce('fv-player-db-export-'.$id)."'>Export</a> | ";
        $value .= "<a href='#' class='fv-player-clone' data-player_id='{$id}' data-nonce='".wp_create_nonce('fv-player-db-export-'.$id)."'>Clone</a> | ";
        $value .= "<span class='trash'><a href='#' class='fv-player-remove' data-player_id='{$id}' data-nonce='".wp_create_nonce('fv-player-db-remove-'.$id)."'>Delete</a></span>";

        $value .= '<input type="text" class="fv-player-shortcode-input" readonly value="'.esc_attr('[fvplayer id="'. $id .'"]').'" style="display: none" /><a href="#" class="button fv-player-shortcode-copy">Copy Shortcode</a>';

        $value .= "</div>\n";
        break;
      case 'embeds':
        $player = new FV_Player_Db_Player($id);
        $value = '';
        if( $player->getIsValid() ) {
          if( $posts = $player->getMetaValue('post_id') ) {
            foreach( $posts AS $post_id ) {
              $post = get_post($post_id);
              if( !isset($post) ) continue;
              $title = !empty($post->post_title) ? $post->post_title : '#'.$post->ID;
              if( $post->post_status != 'publish' ) {
                $title .= ' ('.$post->post_status.')';
              }
              
              $value .= '<li><a href="'.get_permalink($post).'" target="_blank">'.$title.'</a></li>';
            }
          }
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
      default:
        $value = isset($player->$column_name) && $player->$column_name ? $player->$column_name : '';
        break;

    }
    
    return $value;
  }

  public function get_bulk_actions() { // todo: any bulk action?
    return array();
  }

  public function process_bulk_action() {  // todo: any bulk action?
    return;
  }
  
  public function get_result_counts() {
    global $FV_Player_Db;
    $this->total_items = $FV_Player_Db->getListPageCount();
  }

  public function get_data() {
    $current = !empty($_GET['paged']) ? intval($_GET['paged']) : 1;
    $order = !empty($_GET['order']) ? esc_sql($_GET['order']) : 'desc';
    $order_by = !empty($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'date_created';
    $single_id = !empty($_GET['id']) ? esc_sql($_GET['id']) : null;
    $search = !empty($_GET['s']) ? trim($_GET['s']) : null;

    if(!empty($this->args['player_id'])) $single_id = $this->args['player_id'];

    $per_page = $this->args['per_page'];
    $offset = ( $current - 1 ) * $per_page;

    global $FV_Player_Db;
    return $FV_Player_Db->getListPageData($order_by, $order, $offset, $per_page, $single_id, $search);
  }
  
  public function prepare_items() {

    wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

    $data     = $this->get_data();

    // re-count number of players to show when searching
    if (isset($_GET['s']) && $_GET['s']) {
      $this->get_result_counts();
    }

    $status   = isset( $_GET['status'] ) ? $_GET['status'] : 'all';

    $this->items = $data;

    $this->set_pagination_args( array(
        'total_items' => $this->total_items,
        'per_page'    => $this->args['per_page'],
        'total_pages' => ceil( $this->total_items / $this->args['per_page'] ),
      )
    );
  }

}
