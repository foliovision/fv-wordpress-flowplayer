<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FV_Player_List_Table_View {
  function __construct() {
    add_action( 'admin_menu', array($this, 'admin_menu') );
  }
  
  function admin_menu(){    
    if( current_user_can('edit_posts') ) {
      add_menu_page( 'FV Player', 'FV Player', 'edit_posts', 'fv_player', '', 'dashicons-welcome-widgets-menus', 30 );
      add_submenu_page(  'fv_player', 'FV Player', 'FV Player', 'edit_posts', 'fv_player', array($this, 'tools_panel') );
    }
  }
  
  function tools_panel() {
		$table = new FV_Player_List_Table();
		$table->prepare_items();
  	?>
  	<div class="wrap">
      <div>
          <div id="icon-options-general" class="icon32"><br /></div>
          <h2>FV Player</h2>
      </div>
  		<form id="<?php echo $this->slug; ?>-filter" method="get" action="<?php echo admin_url( 'admin.php?page='.$this->slug ); ?>">
  			<input type="hidden" name="page" value="<?php echo $this->slug; ?>" />

  			<?php $table->views() ?>

  			<?php $table->advanced_filters(); ?>

  			<?php $table->display() ?>
  		</form>
      
  	</div>
  <?php 
    add_action( 'admin_footer', array($this, 'scripts') );
  }  
}

$FV_Player_List_Table_View = new FV_Player_List_Table_View;
  
class FV_Player_List_Table extends WP_List_Table {

	public $per_page = 3;

	public $base_url;
  
  public $counts;
	
	public $total_impressions = 0;
	
	public $total_clicks = 0;
  
  public $total_items = 0;
  
  private $dropdown_cache = false;

	public function __construct() {

		global $status, $page;

		parent::__construct( array(
			'singular' => 'Log entry',
			'plural'   => 'Log entries',
			'ajax'     => false,
		) );

		$this->get_result_counts();
		$this->process_bulk_action();
		$this->base_url = admin_url( 'admin.php?page=fv_player' );
	}
	
	public function advanced_filters() {
		$start_date = isset( $_GET['start-date'] )  ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )    ? sanitize_text_field( $_GET['end-date'] )   : null;
		?>
		<style>
		#fv-tr-employees-timelog-filter {
			background: #f5f5f5;
    	background-image: none;
			clear: both;
			background-image: -webkit-gradient(linear,left bottom,left top,from(#f5f5f5),to(#fafafa));
			background-image: -webkit-linear-gradient(bottom,#f5f5f5,#fafafa);
			background-image: -moz-linear-gradient(bottom,#f5f5f5,#fafafa);
			background-image: -o-linear-gradient(bottom,#f5f5f5,#fafafa);
			background-image: linear-gradient(to top,#f5f5f5,#fafafa);
			border-color: #dfdfdf;
			border-width: 1px;
			border-style: solid;
			border-radius: 3px;
			font-size: 13px;
			line-height: 2.1em;
			overflow: auto;
			padding: 12px;
			margin: 8px 0;
		}
		
		.fv-tweet-value-edit { display: none }
		.column-impressions, .column-clicks, .column-user_id, .column-ctr { width: 10% }
		.wp-list-table input { width: 100% }
		.column-edit { width: 3em }
    .widefat .column-text { color: #888 }
		</style>
		<div id="fv-tr-employees-timelog-filter">
			<span id="edd-payment-after-core-filters">
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'easy-digital-downloads' ); ?>"/>
			</span>
			<?php if( ! empty( $status ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>"/>
			<?php endif; ?>
			<?php if( ! empty( $start_date ) || ! empty( $end_date ) ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=fv_player' ); ?>" class="button-secondary"><?php _e( 'Clear Filter', 'easy-digital-downloads' ); ?></a>
			<?php endif; ?>
			<?php //$this->search_box( __( 'Search', 'easy-digital-downloads' ), 'edd-payments' ); ?>
		</div>

<?php
	}	
	
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
?>
		<p class="search-box">
			<?php do_action( 'edd_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?><br/>
		</p>
<?php
	}
  
	public function get_columns() {
		return array(
			//'cb'          => '<input type="checkbox" />',
			'id'          => __( 'ID', 'fv-wordpress-flowplayer' ),
      'player_name' => __( 'Name', 'fv-wordpress-flowplayer' ),
			'videos'      => __( 'Videos', 'fv-wordpress-flowplayer' ),
			'edit'			  => ''
		);
	}
  
	public function get_sortable_columns() {
		return array(
		  'id'          => array( 'ID', true )
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
	
	public function column_cb( $log ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'log_id',
			$log->id
		);
	}
  
	public function column_default( $log, $column_name ) {
		switch ( $column_name ) {
			case 'edit' :				
				$value = "<a href='#' class='fv-tweet-edit'>Edit</a>";
				break;
			default:
				$value = isset($log->$column_name) && $log->$column_name ? $log->$column_name : '';
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

		$aWhere = array();
		$where = count($aWhere) ? " WHERE ".implode( " AND ", $aWhere ) : "";
		
    global $wpdb;
    $this->total_items = $wpdb->get_var("SELECT count(id) FROM {$wpdb->prefix}fv_player_players $where" );		
	}

	public function get_players( $id = false ) {
		$aWhere = array();
		if( $id ) {
			$aWhere[] = 'id = '.intval($id);
		}		
    
    $where = count($aWhere) ? " WHERE ".implode( " AND ", $aWhere ) : "";
    
		$current = !empty($_GET['paged']) ? intval($_GET['paged']) : 1;
    $order = !empty($_GET['order']) ? esc_sql($_GET['order']) : 'asc';
    $order_by = !empty($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'id';

		$per_page = $this->per_page;
		$offset = ( $current - 1 ) * $per_page;
		
		global $wpdb, $FV_TR_Employees_Twitter_Log;
    $sql = "SELECT * FROM {$wpdb->prefix}fv_player_players $where ORDER BY $order_by $order LIMIT $offset, $per_page";		
		$results = $wpdb->get_results($sql);
		
    return $results;
	}
	
	public function prepare_items( $id = false ) {

		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();
		$data     = $this->get_players( $id );
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'all';

		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $this->total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
	
}
