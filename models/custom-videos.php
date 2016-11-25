<?php

class FV_Player_Custom_Videos {
  
  var $did_form = false;
  
  var $id;
  
  public function __construct( $args ) {
    global $post;
    
    $args = wp_parse_args( $args, array(
                                        'id' => isset($post) && isset($post->ID) ? $post->ID : false,
                                        'meta' => '_fv_player_user_video',
                                        'type' => isset($post->ID) ? 'post' : 'user'
                                        ) );
    
    $this->id = $args['id'];
    $this->meta = $args['meta'];
    $this->type = $args['type'];
        
  }

  public function __get( $name ) {
    return $data;
  }
  
  private function esc_shortcode( $arg ) {
    $arg = str_replace( array('[',']'), array('&#91;','&#93;'), $arg );
    return $arg;
  }
  
  public function get_form( $args = array() ) {
    
    global $FV_Player_Custom_Videos_form_instances;
    if( isset($FV_Player_Custom_Videos_form_instances[$this->meta]) ) {
      $number = rand();
      echo "<span id='fv-player-custom-videos-form-".$number."'></span>";
      echo "<script>jQuery('span#fv-player-custom-videos-form-".$number."').parents('.postbox').remove();</script>";
      return false;
    }
    $FV_Player_Custom_Videos_form_instances[$this->meta] = true;
    
    $this->did_form = true;
    
    $args = wp_parse_args( $args, array( 'kind' => 'div', 'edit' => true, 'limit' => 1000, 'no_form' => false ) );
    
    $html = '';
    
    if( $args['kind'] != 'li' ) {
      $html .= '<div class="fv-player-custom-video-list">';
    }
    
    if( is_admin() ) {
      global $fv_fp;
      if( $this->have_videos() ) {
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
      
        add_action('admin_footer','flowplayer_prepare_scripts');  
      }
      
      add_action('admin_footer', array( $fv_fp, 'css_enqueue' ) );    
    }
    
    if( !is_admin() && !$args['no_form'] ) $html .= "<form method='POST'>";
    
    $html .= $this->get_html( $args );
    
    if( !is_admin() ) {
      $html .= wp_nonce_field( 'fv-player-custom-videos-'.$this->meta.'-'.get_current_user_id(), 'fv-player-custom-videos-'.$this->meta.'-'.get_current_user_id(), true, false );
    }
    
    if( !is_admin() && !$args['no_form'] ) {      
      $html .= "<input type='hidden' name='action' value='fv-player-custom-videos-save' />";
      $html .= "<input type='submit' value='Save Videos' />"; //  todo: don't show when in post form      
      $html .= "</form>";
    }
    
    if( $args['kind'] != 'li' ) {
      $html .= '</div>';
    }
    
    if( $args['edit'] ) {
      if( is_admin() ) {
        add_action( 'admin_footer', array( $this, 'scripts' ) );
      } else {
        add_action( 'wp_footer', array( $this, 'scripts' ) );
      }
    }    
    
    return $html;
  }

  public function get_html( $args = array() ) {
    
    $args = wp_parse_args( $args, array( 'kind' => 'div', 'edit' => false, 'limit' => 1000 ) );
    
    $html = '';
    $count = 0;
    if( $this->have_videos() ) {
      foreach( $this->get_videos() AS $aVideo ) {
        $count++;
        $html .= '<'.$args['kind'].' class="fv-player-custom-video">';
        
        if( $args['edit'] ) {
          $html .= do_shortcode('[fvplayer src="'.$this->esc_shortcode($aVideo['url']).'" autoplay="false"]');
        } else {
          $html .= do_shortcode('[fvplayer src="'.$this->esc_shortcode($aVideo['url']).'" caption="'.$this->esc_shortcode($aVideo['title']).'"]');
        }
        
        if( $args['edit'] ) {
          $html .= '<input class="fv_player_custom_video fv_player_custom_video_url regular-text" type="text" name="fv_player_videos['.$this->meta.'][]" placeholder="Video URL" value="'.esc_attr($aVideo['url']).'" /><br />'."\n";
          $html .= ' <input class="fv_player_custom_video regular-text" type="text" name="fv_player_videos_titles['.$this->meta.'][]" value="'.esc_attr($aVideo['title']).'" placeholder="Video title" /><br />'."\n";
          if( count($this->get_videos()) == $count && $count < $args['limit'] ) $html .= '<a class="fv-player-custom-video-add" href="#">Add more</a> ';
          $html .= '<a class="fv-player-custom-video-remove" href="#">Remove</a> ';
                    
        }
        $html .= '</'.$args['kind'].'>'."\n";
        
      }
      
    } else if( $args['edit'] ) {
      $html .= '<'.$args['kind'].' class="fv-player-custom-video">';

        $html .= "<input class='fv_player_custom_video fv_player_custom_video_url regular-text' placeholder='URL' type='text' name='fv_player_videos[".$this->meta."][]' /><br />\n";
        $html .= "<input class='fv_player_custom_video regular-text' placeholder='Title' type='text' name='fv_player_videos_titles[".$this->meta."][]' /><br />\n";
        if( 1 < $args['limit'] ) $html .= "<a class='fv-player-custom-video-add' href='#'>Add more</a>\n";
      
      $html .= '</'.$args['kind'].'>';      
    }
    
    $html .= "<input type='hidden' name='fv-player-custom-videos-entity-id[".$this->meta."]' value='".esc_attr($this->id)."' />";
    $html .= "<input type='hidden' name='fv-player-custom-videos-entity-type[".$this->meta."]' value='".esc_attr($this->type)."' />";    

    return $html;
  }
  
  public function get_videos() {
    if( $this->type == 'user' ) {
      $aMeta = get_user_meta( $this->id, $this->meta );      
    } else if( $this->type == 'post' ) {
      $aMeta = get_post_meta( $this->id, $this->meta );
    }
    
    $aVideos = array();
    if( $aMeta ) {
      foreach( $aMeta AS $aVideo ) {
        if( isset($aVideo['url']) && isset($aVideo['title']) ) $aVideos[] = $aVideo;
      }
    }
    
    return $aVideos;
  }  
  
  public function have_videos() {
    return count($this->get_videos()) ? true : false;
  }
  
  public function scripts() {
    ?>
    <script>
      function fv_player_custom_video_add(row) {
        var row = jQuery(row);
        row.parents('.fv-player-custom-video').parent().append( row.parents('.fv-player-custom-video').clone() );
        row.parents('.fv-player-custom-video').parent().find('.fv-player-custom-video:last').find('input[type=text]').val('');
        row.parents('.fv-player-custom-video').parent().find('.fv-player-custom-video:last iframe').remove();
        row.parents('.fv-player-custom-video').parent().find('.fv-player-custom-video:last .flowplayer').remove();
        row.parents('.fv-player-custom-video').parent().find('.fv-player-custom-video:last .fv-player-custom-video-remove').remove();
        if( row.hasClass('fv-player-custom-video-add') ) row.remove();
      }
      
      jQuery(document).on('click','.fv-player-custom-video-remove', function(e) {
        e.preventDefault();
        if( jQuery(this).parents('.fv-player-custom-video-list').find('.fv-player-custom-video').length == 1 ) {
          fv_player_custom_video_add(this);
        }
        jQuery(this).parents('.fv-player-custom-video').remove();        
      });
      jQuery(document).on('click','.fv-player-custom-video-add', function(e) {
        e.preventDefault();

        fv_player_custom_video_add(this);
      });
      
      jQuery(document).on('change', '.fv_player_custom_video_url', function() {
        if( !jQuery(this).val().match(/^(https?:)?\/\//) ){
          jQuery(this).siblings('iframe').remove();
          return;
        }
        
        
        if( jQuery(this).siblings('iframe').length == 0 ) {
          jQuery(this).before('<iframe allowfullscreen class="fv_player_custom_video_preview" scrolling="no"></iframe>');
          jQuery(this).before('<p class="loading-preview"><?php _e('Loading preview...','fv-wordpress-flowplayer'); ?></p>');
        }
        
        jQuery(this).siblings('.flowplayer').remove();
        
        var url = '<?php echo home_url('/'); ?>?fv_player_embed=1&fv_player_preview=' + encodeURIComponent('[fvplayer src="'+jQuery(this).val()+'" embed="false"]');
        jQuery(this).siblings('iframe').attr('src',url).hide();
        jQuery(this).siblings('.loading-preview').show();
        
      });
      
      jQuery(document).on('fvp-preview-complete', function() {
        jQuery('.fv_player_custom_video_preview').show();
        jQuery('.loading-preview').hide();
      });
    </script>
    <?php
  }
  
  
}




class FV_Player_Custom_Videos_Master {
  
  function __construct() {
    
    add_action( 'init', array( $this, 'save' ) ); //  saving of user profile, both front and back end    
    add_action( 'save_post', array( $this, 'save_post' ) );

    add_filter( 'show_password_fields', array( $this, 'user_profile' ), 10, 2 );
    add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 999, 2 );
    
    add_filter( 'the_content', array( $this, 'show' ) );
  }
  
  function add_meta_boxes() {
    global $post;
    $aMeta = get_post_custom($post->ID);
    if( $aMeta ) {
      foreach( $aMeta AS $key => $aMetas ) {
        $objVideos = new FV_Player_Custom_Videos( array('id' => $post->ID, 'meta' => $key, 'type' => 'post' ) );
        if( $objVideos->have_videos() ) {
          add_meta_box( 'fv_player_custom_videos-field_'.$key,
                      ucfirst(str_replace( array('_','-'),' ',$key)),
                      array( $this, 'meta_box' ),
                      null,
                      'normal',
                      'high',
                      $objVideos );
        }
                    
      }
    }
    
  }
  
  function meta_box( $aPosts, $args ) {
    global $FV_Player_Custom_Videos_form_instances;
    unset($FV_Player_Custom_Videos_form_instances[$this->meta]);
          
    $objVideos = $args['args'];   
    echo $objVideos->get_form();
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
  
  function save_post( $post_id ) {
    if( !isset($_POST['fv_player_videos']) || !isset($_POST['fv-player-custom-videos-entity-type']) || !isset($_POST['fv-player-custom-videos-entity-id']) ) {
      return;
    }
    
    //  todo: permission check!
    
    foreach( $_POST['fv_player_videos'] AS $meta => $aValues ) {
      if( $_POST['fv-player-custom-videos-entity-type'][$meta] == 'post' ) {
        delete_post_meta( $post_id, $meta );
        foreach( $aValues AS $key => $value ) {
          if( strlen($value) == 0 ) continue;
          $aVideo = array(
                          'url' => trim(strip_tags($value)),
                          'title' => trim(htmlspecialchars($_POST['fv_player_videos_titles'][$meta][$key]))
                          );          
          add_post_meta( $post_id, $meta, $aVideo );
        }
        
      } 
      
    }
    
  }
  
  function show( $content ) {
    global $post;    
    if( isset($post->ID) ) {
      $aMeta = get_post_custom($post->ID);
      if( $aMeta ) {
        foreach( $aMeta AS $key => $aMetas ) {
          $objVideos = new FV_Player_Custom_Videos( array('id' => $post->ID, 'meta' => $key, 'type' => 'post' ) );
          if( $objVideos->have_videos() ) {
            $content .= $objVideos->get_html();
          }
        }
      }
    }
    
    return $content;
  }
  
  function user_profile( $show_password_fields, $profileuser ) {        
    if( $profileuser->ID > 0 ) {
      $objUploader = new FV_Player_Custom_Videos( array( 'id' => $profileuser->ID ) );
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

