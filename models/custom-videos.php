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
    
    if( !is_admin() ) $html .= "<form method='POST'>";
    
    $html .= $this->get_html( $args );

    $html .= "<input type='hidden' name='fv-player-custom-videos-entity-id[".$this->meta."]' value='".esc_attr($this->id)."' />";
    $html .= "<input type='hidden' name='fv-player-custom-videos-entity-type[".$this->meta."]' value='".esc_attr($this->type)."' />";
    
    $html .= "<input class='fv_player_custom_video regular-text' placeholder='Add another video' type='text' name='fv_player_videos[".$this->meta."][]' />\n";
    
    if( !is_admin() ) {      
      $html .= "<input type='hidden' name='action' value='fv-player-custom-videos-save' />";
      $html .= "<input type='submit' value='Save Videos' />"; //  todo: don't show when in post form
      $html .= "</form>";
    }
    return $html;
  }

  public function get_html( $args = array() ) {
    
    $args = wp_parse_args( $args, array( 'kind' => 'li', 'edit' => false ) );
    
    $html = '';
    if( $this->have_videos() ) {
      foreach( $this->get_videos() AS $sURL ) {
        
        if( $args['kind'] == 'td' ) {
          $html .= '<tr><th></th>';
        }
        
        $html .= '<'.$args['kind'].'>';
        if( !is_admin() ) $html .= do_shortcode('[fvplayer src="'.$sURL.'"]');
        if( $args['edit'] ) {
          $html .= '<input class="fv_player_custom_video regular-text" type="text" name="fv_player_videos['.$this->meta.'][]" value="'.esc_attr($sURL).'" /> <a class="fv-player-custom-video-remove" href="#">Remove</a>';
        }
        $html .= '</'.$args['kind'].'>'."\n";
        
        if( $args['kind'] == 'td' ) {
          $html .= '</tr>';
        }
        
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
        foreach( $aValues AS $value ) {
          $value =  trim(strip_tags($value));
          if( strlen($value) == 0 ) continue;
          add_user_meta( $_POST['fv-player-custom-videos-entity-id'][$meta], $meta, $value );
        }
      }
    }
    
  }
  
  function user_profile( $show_password_fields, $profileuser ) {
    if( $profileuser->ID > 0 ) {
      ?>
      <tr class="user-profile-picture">
        <th><?php _e( 'Videos', 'fv-wordpress-flowplayer' ); ?></th>
        <td>
          <?php
          $objUploader = new FV_Player_Custom_Videos( array( 'id' => $profileuser->ID ) );
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

