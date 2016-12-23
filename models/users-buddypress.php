<?php

class FV_Player_BuddyPress {  
  
  public function __construct() {
    add_action( 'plugins_loaded', array( $this, 'init') );
  }
  
  function init() {
    if( !function_exists('buddypress') ) return;
    
    global $fv_fp;
    if( isset($fv_fp->conf['profile_videos_enable_bio']) && $fv_fp->conf['profile_videos_enable_bio'] == 'true' ) {      
      add_action( 'bp_setup_nav', array( $this, 'add_video_tabs'), 100 );
      
    }
  }
  
  function add_video_tabs() {
    global $bp; 
    
    bp_core_new_nav_item( array(
      'name'                  => 'Videos',
      'slug'                  => 'videos',
      'parent_url'            => $bp->displayed_user->domain,
      'parent_slug'           => $bp->profile->slug,
      'screen_function'       => array( $this, 'videos_screen'),			
      'position'              => 200,
      'default_subnav_slug'   => 'videos'
    ) );
  }

  function videos_screen() {

    add_action( 'bp_template_title', array( $this, 'videos_screen_title') );
    add_action( 'bp_template_content', array( $this, 'profile') );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
  }
  
  function videos_screen_title() { 
    global $bp;
    echo $bp->displayed_user->fullname . '\'s Videos<br/>';
  }

  function profile( $content ) {
    global $bp;
    $user_id = $bp->displayed_user->id;

    $objVideos = new FV_Player_Custom_Videos( array( 'id' => $user_id, 'type' => 'user' ) );

    if( get_current_user_id() === $user_id){
      echo $objVideos->get_form();
    }else{
      echo $objVideos->get_html();
    }
  }
}

$FV_Player_BuddyPress = new FV_Player_BuddyPress();
