<?php

if( !class_exists('FV_Player_Related_Videos') ) :

class FV_Player_Related_Videos {

  public $max_related_videos = 4;
  public $max_related_posts = 10;

  function __construct() {
    add_filter('fv_flowplayer_attributes', array($this, 'related_videos_popup'), 10, 3);
  }

  public function related_videos_popup($attributes, $media, $player) {
    if( !function_exists('yarpp_get_related') ) {
      return $attributes;
    }

    remove_shortcode('fvplayer');

    // get related posts
    $related_posts = yarpp_get_related(
      array(
        'limit' => $this->max_related_posts, // maximum number of related entries to return
        'order' => 'score DESC', // column on "wp_posts" to order by, then a space, and whether to order in ascending ("ASC") or descending ("DESC") order
        'promote_yarpp' => false, // boolean indicating whether to add 'Powered by YARPP' below related posts
        'post_type' => array('post', 'page'), //  post types to include in results
      ));

    add_shortcode('fvplayer','flowplayer_content_handle');

    if( !is_array($related_posts ) ) {
      return $attributes;
    }

    global $wpdb;

    // get all player meta
    $player_meta_all = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}fv_player_playermeta` WHERE meta_key = 'post_id'");

    // html for related videos
    $related_videos_html = '';

    // keep track of related videos count
    $current_related_videos = 0;

    // loop through related posts
    foreach($related_posts as $related_post) {
      $post_id = $related_post->ID;

      // loop through player meta
      foreach($player_meta_all as $player_meta) {

        // if player meta is for this post
        if( $player_meta->meta_value == $post_id ) {
          $id_player = $player_meta->id_player;

          // get player data
          $player_data = $wpdb->get_row("SELECT p.id, videos, v.id, src, splash FROM `{$wpdb->prefix}fv_player_players` AS p JOIN wp_fv_player_videos AS v ON find_in_set( p.videos, v.id ) WHERE p.id = $id_player");

          if( !$player_data ) {
            continue;
          }

          $splash_img = '';

          // check if there is splash
          if( $player_data->splash ) {
            $splash_img = $player_data->splash;
          } else {
            $splash_img = get_the_post_thumbnail($post_id, 'thumbnail');
          }

          $related_videos_html .= '<div class="fv_player_related_video"><a href="'.get_permalink($post_id).'"><img style="width: 100px;" src="'.$splash_img.'" alt="'.get_the_title($post_id).'" /></a><a href="'.get_permalink($post_id).'">'.get_the_title($post_id).'</a></div>';

          $current_related_videos++;

          if( $current_related_videos >= $this->max_related_videos ) {
            break 2;
          }

        }
      }

    }

    if( $related_videos_html ) {
      $popup = '<div class="fv_player_popup wpfp_custom_popup_content"><p>'.__('Related videos','fv-player-ppv') .'</p>' . $related_videos_html . '</div>';

      $attributes['data-related-videos'] = $player->json_encode( array( 'html' => $popup ) );
    }

    return $attributes;
  }

}


global $FV_Player_Related_Videos;
$FV_Player_Related_Videos = new FV_Player_Related_Videos;

endif;
