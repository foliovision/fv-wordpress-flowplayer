<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class FV_Player_List_Table_View {
  
  var $list_page = false;

  function __construct() {
    add_action( 'init', array( $this, 'load_options' ) );
  }

  function admin_menu(){
    if( current_user_can('edit_posts')  ) {
      add_menu_page( 'FV Player', 'FV Player', 'edit_posts', 'fv_player', '', flowplayer::get_plugin_url().'/images/icon@x2.png', 30 );
      $this->list_page = add_submenu_page(  'fv_player', 'FV Player', 'Videos/Playlists', 'edit_posts', 'fv_player', array($this, 'tools_panel') );
      
      add_action( 'load-'.$this->list_page, array( $this, 'screen_options' ) );
      add_filter( 'manage_toplevel_page_fv_player_columns', array( $this, 'screen_columns' ) );
      add_filter( 'hidden_columns', array( $this, 'screen_columns_hidden' ), 10, 3 );
    }
  }

  function settings_link() {
    add_submenu_page(  'fv_player', 'Settings Link', 'Settings', 'manage_options', 'fvplayer', 'fv_player_admin_page' );
  }

  function settings_link_fix_start() {
    ob_start();
  }

  function settings_link_fix_end() {
    $html = ob_get_clean();
    $html = str_replace( 'admin.php?page=fvplayer', 'options-general.php?page=fvplayer', $html );
    echo $html;
  }
  
  function load_options() {
    add_action( 'admin_menu', array($this, 'admin_menu') );

    // this adds a admin.php?page=fvplayer link to the menu
    add_action( 'admin_menu', array($this, 'settings_link'), 12 );
    // so we use output buffer to trap the menu output
    add_action( 'admin_head', array($this, 'settings_link_fix_start'), PHP_INT_MAX );
    // and then fix the link
    add_action( 'adminmenu', array($this, 'settings_link_fix_end') );

    add_action( 'admin_head', array($this, 'styling') );
    add_filter( 'set-screen-option', array($this, 'set_screen_option'), 10, 3);
    add_filter( 'set_screen_option_fv_player_per_page', array($this, 'set_screen_option'), 10, 3);
  }
  
  function set_screen_option($status, $option, $value) {
    if( 'fv_player_per_page' == $option ) return $value;
    return $status;
  }
  
  function screen_columns() {
    return FV_Player_List_Table::get_columns_worker();
  }
  
  function screen_columns_hidden( $hidden, $screen, $use_defaults ) {
    if( $use_defaults && $screen->id == $this->list_page) {
      $hidden = array( 'subtitles_count', 'chapters_count', 'transcript_count', 'author' );
    }
    return $hidden;
  }
  
  function screen_options() {
    $screen = get_current_screen();
    if(!is_object($screen) || $screen->id != $this->list_page)
      return;
   
    $args = array(
      'label' => __('Players per page', 'fv-wordpress-flowplayer'),
      'default' => 25,
      'option' => 'fv_player_per_page'
    );
    add_screen_option( 'per_page', $args );
  }
  
  function styling() {
    if( isset($_GET['page']) && $_GET['page'] == 'fv_player' ) {
      global $fv_wp_flowplayer_ver;
      wp_enqueue_style('fv-player-list-view', flowplayer::get_plugin_url().'/css/list-view.css',array(), $fv_wp_flowplayer_ver );
      
      wp_enqueue_media();
    }
    ?>
    <style>#adminmenu #toplevel_page_fv_player .wp-menu-image img {width:28px;height:25px;padding-top:4px !important}</style>
    <?php
  }
  
  function tools_panel() {
    
    $user = get_current_user_id();
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($user, $screen_option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
      $per_page = $screen->get_option( 'per_page', 'default' );
    }
    $table = new FV_Player_List_Table( array(
      'per_page' => $per_page
    ) );
    
    $table->prepare_items();
    ?>
    <div class="wrap">
      <h1 class="wp-heading-inline">FV Player</h1>
      <a href="#" class="page-title-action fv-player-edit" data-add_new="1">Add New</a>
      <a href="#" class="page-title-action fv-player-import">Import</a>

      <div id="fv_player_players_table">
          <form id="fv-player-filter" method="get" action="<?php echo admin_url( 'admin.php?page=fv_player' ); ?>">
              <input type="hidden" name="page" value="fv_player" />

              <?php
              // Show the current taxonomy filtering, if any
              if ( ! empty( $_GET['post_type'] ) ) {
                $post_type_taxonomies = get_taxonomies( array(
                  'object_type' => array( $_GET['post_type'] ),
                  'public'      => true,
                  'show_ui'     => true,
                ), 'objects' );

                foreach ( $post_type_taxonomies AS $tax ) {
                  if ( ! empty( $_GET[ $tax->name ] ) ) :
                    $term = term_exists( $_GET[ $tax->name ], $tax->name );
                    if ( $term ) :
                      $term = get_term( $term['term_id'], $tax->name );
                      ?>
                        <p class="subsubsub" style="position: absolute; margin-top: 43px;"><?php echo $tax->labels->singular_name; ?>: <strong><?php echo $term->name; ?></strong></p>
                      <?php
                    endif;
                  endif;
                }
              }
              ?>

              <p class="subsubsub" style="line-height: 26px; margin-right: .5em">Embed post type:</p>

              <?php $table->views() ?>

              <?php $table->advanced_filters(); ?>

              <?php $table->display() ?>
          </form>
      </div>
    
    </div>
  <?php 
    fv_player_shortcode_editor_scripts_enqueue();
    fv_wp_flowplayer_edit_form_after_editor();

    wp_enqueue_script( 'fv-player-list-view', flowplayer::get_plugin_url().'/js/list-table.js', array('jquery'), filemtime( dirname(__FILE__).'/../js/list-table.js' ), true );
  }  
}

$FV_Player_List_Table_View = new FV_Player_List_Table_View;
