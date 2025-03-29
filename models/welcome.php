<?php

class FV_Player_Welcome {

  function __construct() {

    register_activation_hook(__FILE__, array($this, 'welcome_screen_activate'));
    add_action('admin_init', array($this, 'welcome_screen_do_activation_redirect'));
    add_action('admin_menu', array($this, 'welcome_screen_pages'));
    add_action('admin_head', array($this, 'welcome_screen_remove_menus'));
  }

  function welcome_screen_activate() {
    set_transient('fv_player_welcome_screen_activation_redirect', true, 30);
  }

  function welcome_screen_do_activation_redirect() {
    // Bail if no activation redirect
    if (!get_transient('fv_player_welcome_screen_activation_redirect')) {
      return;
    }

    // Delete the redirect transient
    delete_transient('fv_player_welcome_screen_activation_redirect');

    // Bail if activating from network, or bulk
    if (is_network_admin() || isset($_GET['activate-multi'])) {
      return;
    }

    // Redirect to bbPress about page
    wp_safe_redirect(add_query_arg(array('page' => 'fv-player-welcome'), admin_url('index.php')));
  }

  function welcome_screen_pages() {
    add_dashboard_page(
            'Welcome To Welcome Screen', 'Welcome To Welcome Screen', 'read', 'fv-player-welcome', array($this, 'welcome_screen_content')
    );
  }

  function welcome_screen_content() {
    ?>
    <div class="wrap">
      <h2>An Example Welcome Screen</h2>

      <p>
        You can put any content you like here from columns to sliders - it's up to you
      </p>
    </div>
    <?php
  }

  function welcome_screen_remove_menus() {
    remove_submenu_page('index.php', 'fv-player-welcome');
  }

}

$FV_Player_Welcome = new FV_Player_Welcome();
