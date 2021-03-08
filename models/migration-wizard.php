<?php
if( !class_exists('FV_Player_Migration') ) :

class FV_Player_Migration {
  public $admin_page;

  function __construct() {
    add_action('admin_menu', array( $this, 'admin_page' ), 12 );
  }

  function admin_page() {
    $this->admin_page = add_submenu_page(  'fv_player', 'FV Player Migration', 'FV Player Migration', 'edit_posts', 'fv_player_migration', array($this, 'tools_panel') );
  }

  function tools_panel() {
    FV_Player_Migration_Wizard()->view();
  }

}

$FV_Player_Migration = new FV_Player_Migration;

endif;