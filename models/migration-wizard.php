<?php
if( !class_exists('FV_Player_Migration') ) :

class FV_Player_Migration {

  function __construct() {
    add_action('admin_menu', array( $this, 'admin_page' ) );
  }

  function admin_page() {
    add_submenu_page(  'fv_player', 'FV Player Migration', 'FV Player Migration', 'edit_posts', 'fv_player_migration', array($this, 'tools_panel') );
    remove_submenu_page( 'fv_player', 'fv_player_migration' );
  }

  function tools_panel() {
    FV_Player_Migration_Wizard()->view();
  }

}

$FV_Player_Migration = new FV_Player_Migration;

endif;