<?php

class FV_Player_Custom_Videos {
  
  var $id;
  
  public function __construct( $args ) {
    global $post;
    
    $args = wp_parse_args( $args, array(
                                        'id' => isset($post) && isset($post->ID) ? $post->ID : false,
                                        'meta' => '_fv_player_user_video',
                                        'type' => 'user'
                                        ) );
    
    $this->id = $args['id'];
    $this->meta = $args['meta'];
    $this->type = $args['type'];
  }

  public function __get( $name ) {
    return $data;
  }
  
  public function get_form( $args = array() ) {
    
    $args = wp_parse_args( $args, array( 'kind' => 'div', 'edit' => true ) );
    
    $html = '';
    
    if( $args['kind'] != 'li' ) {
      $html .= '<div class="fv-player-custom-video-list">';
    }
    
    if( !is_admin() ) $html .= "<form method='POST'>";
    
    $html .= $this->get_html( $args );

    $html .= '<'.$args['kind'].' class="fv-player-custom-video">';
      
      $html .= "<input type='hidden' name='fv-player-custom-videos-entity-id[".$this->meta."]' value='".esc_attr($this->id)."' />";
      $html .= "<input type='hidden' name='fv-player-custom-videos-entity-type[".$this->meta."]' value='".esc_attr($this->type)."' />";
      
      $sPlaceholderVideo = $this->have_videos() ? 'Add another video' : 'Add your video';
      $sPlaceholderTitle = $this->have_videos() ? 'Another video title' : 'Add video title';
      
      $html .= "<input class='fv_player_custom_video regular-text' placeholder='".$sPlaceholderVideo."' type='text' name='fv_player_videos[".$this->meta."][]' />\n";
      $html .= "<input class='fv_player_custom_video regular-text' placeholder='".$sPlaceholderTitle."' type='text' name='fv_player_videos_titles[".$this->meta."][]' />\n";
      $html .= "<a class='fv-player-custom-video-add' href='#'>One more</a>\n";
    
    $html .= '</'.$args['kind'].'>';
    
    if( !is_admin() ) {      
      $html .= "<input type='hidden' name='action' value='fv-player-custom-videos-save' />";
      $html .= "<input type='submit' value='Save Videos' />"; //  todo: don't show when in post form
      $html .= "</form>";
    }
    
    if( $args['kind'] != 'li' ) {
      $html .= '</div>';
    }
    
    return $html;
  }

  public function get_html( $args = array() ) {
    
    $args = wp_parse_args( $args, array( 'kind' => 'li', 'edit' => false ) );
    
    $html = '';
    if( $this->have_videos() ) {
      foreach( $this->get_videos() AS $aVideo ) {  
        $html .= '<'.$args['kind'].' class="fv-player-custom-video">';
        
        if( $args['edit'] ) {
          $html .= do_shortcode('[fvplayer src="'.$aVideo['url'].'"]');
        } else {
          $html .= do_shortcode('[fvplayer src="'.$aVideo['url'].'" caption="'.$aVideo['title'].'"]');
        }
        
        if( $args['edit'] ) {
          $html .= '<input class="fv_player_custom_video regular-text" type="text" name="fv_player_videos['.$this->meta.'][]" value="'.esc_attr($aVideo['url']).'" />';
          $html .= ' <input class="fv_player_custom_video regular-text" type="text" name="fv_player_videos_titles['.$this->meta.'][]" value="'.esc_attr($aVideo['title']).'" placeholder="Video title" />';
          $html .= ' <a class="fv-player-custom-video-remove" href="#">Remove</a>';
        }
        $html .= '</'.$args['kind'].'>'."\n";
        
      }
      
      if( $args['edit'] ) {
        if( is_admin() ) {
          add_action( 'admin_footer', array( $this, 'scripts' ) );
        } else {
          add_action( 'wp_footer', array( $this, 'scripts' ) );
        }
      }
    }
    
    return $html;
  }
  
  public function get_videos() {
    if( $this->type == 'user' ) {
      return get_user_meta( $this->id, $this->meta );
    }
    return array();
  }  
  
  public function have_videos() {
    return count($this->get_videos()) ? true : false;
  }
  
  public function scripts() {
    ?>
    <script>
      jQuery(document).on('click','.fv-player-custom-video-remove', function(e) {
        e.preventDefault();
        jQuery(this).siblings('.flowplayer').remove();
        jQuery(this).siblings('input.fv_player_custom_video').remove();
        jQuery(this).remove();
      });
      jQuery(document).on('click','.fv-player-custom-video-add', function(e) {
        e.preventDefault();

        jQuery(this).parents('.fv-player-custom-video').parent().append( jQuery(this).parents('.fv-player-custom-video').clone() );
        jQuery(this).parents('.fv-player-custom-video').parent().find('.fv-player-custom-video:last').find('input[type=text]').val('');
        jQuery(this).remove();
      });      
    </script>
    <?php
  }
  
  
}




class FV_Player_Custom_Videos_Master {
  
  function __construct() {
    add_action( 'init', array( $this, 'save' ) );

    add_filter( 'show_password_fields', array( $this, 'user_profile' ), 10, 2 );
  }
  
  function save() {
    if( !isset($_POST['fv_player_videos']) || !isset($_POST['fv-player-custom-videos-entity-type']) || !isset($_POST['fv-player-custom-videos-entity-id']) ) {
      return;
    }
    
    //  todo: permission check!
    
    foreach( $_POST['fv_player_videos'] AS $meta => $aValues ) {
      if( $_POST['fv-player-custom-videos-entity-type'][$meta] == 'user' ) {
        delete_user_meta( $_POST['fv-player-custom-videos-entity-id'][$meta], $meta );
        foreach( $aValues AS $key => $value ) {
          if( strlen($value) == 0 ) continue;
          $aVideo = array(
                          'url' => trim(strip_tags($value)),
                          'title' => trim(htmlspecialchars($_POST['fv_player_videos_titles'][$meta][$key]))
                          );          
          add_user_meta( $_POST['fv-player-custom-videos-entity-id'][$meta], $meta, $aVideo );
        }
      }
    }
    
  }
  
  function user_profile( $show_password_fields, $profileuser ) {        
    if( $profileuser->ID > 0 ) {
      $objUploader = new FV_Player_Custom_Videos( array( 'id' => $profileuser->ID ) );
      
      if( $objUploader->have_videos() ) {
        global $FV_Player_Pro;
        if( isset($FV_Player_Pro) && $FV_Player_Pro ) {
          //  todo: there should be a better way than this
          add_filter( 'fv_flowplayer_splash', array( $FV_Player_Pro, 'get__cached_splash' ) );
          add_filter( 'fv_flowplayer_playlist_splash', array( $FV_Player_Pro, 'get__cached_splash' ), 10, 3 );      
          add_filter( 'fv_flowplayer_splash', array( $FV_Player_Pro, 'youtube_splash' ) );
          add_filter( 'fv_flowplayer_playlist_splash', array( $FV_Player_Pro, 'youtube_splash' ), 10, 3 );
      
          add_action('admin_footer', array( $FV_Player_Pro, 'styles' ) );
          add_action('admin_footer', array( $FV_Player_Pro, 'scripts' ) );
        }
        
        global $fv_fp;
        add_action('admin_footer', array( $fv_fp, 'css_enqueue' ) );
        add_action('admin_footer','flowplayer_prepare_scripts');        
        
      }
      
      ?>
      <tr class="user-videos">
        <th><?php _e( 'Videos', 'fv-wordpress-flowplayer' ); ?></th>
        <td>
          <?php
          
          echo $objUploader->get_form( array( 'kind' => 'div' ) );
          ?>
          <p class="description"><?php _e( 'You can put your Vimeo or YouTube links here.', 'fv-wordpress-flowplayer' ); ?></p>
        </td>
      </tr>
      <?php
    }
    
    return $show_password_fields;
  }

}


$FV_Player_Custom_Videos_Master = new FV_Player_Custom_Videos_Master;

