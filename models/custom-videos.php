<?php

global $FV_Player_Custom_Videos_count, $FV_Player_Custom_Videos_loaded;
$FV_Player_Custom_Videos_count = 0;
$FV_Player_Custom_Videos_loaded = false;

class FV_Player_Custom_Videos {
  
  var $did_form = false;
  
  var $id;
  
  var $instance_id;
  
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
  
  private function esc_shortcode( $arg ) {
    $arg = str_replace( array('[',']','"'), array('&#91;','&#93;','&quote;'), $arg );
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
    
    $args = wp_parse_args( $args, array( 'wrapper' => 'div', 'edit' => true, 'limit' => 1000, 'no_form' => false ) );
    
    $html = '';
    
    if( $args['wrapper'] != 'li' ) {
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
          add_action('admin_footer', array( $FV_Player_Pro, 'scripts' ) );  //  todo: not just for FV Player Pro
        }
      
        add_action('admin_footer','flowplayer_prepare_scripts');  
      }
      
      add_action('admin_footer', array( $this, 'shortcode_editor_load' ), 0 );    
    }
    
    if( !is_admin() && !$args['no_form'] ) $html .= "<form method='POST'>";
    
    $html .= $this->get_html( $args );
    
    //  todo: buttons to add more videos
    
    if( !is_admin() ) {
      $html .= wp_nonce_field( 'fv-player-custom-videos-'.$this->meta.'-'.get_current_user_id(), 'fv-player-custom-videos-'.$this->meta.'-'.get_current_user_id(), true, false );
    }
    
    if( !is_admin() && !$args['no_form'] ) {      
      $html .= "<input type='hidden' name='action' value='fv-player-custom-videos-save' />";
      $html .= "<input type='submit' value='Save Videos' />"; //  todo: don't show when in post form      
      $html .= "</form>";
    }
    
    if( $args['wrapper'] != 'li' ) {
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
  
  public function get_html_part( $video ) {
    
    global $FV_Player_Custom_Videos_count;
    $this->instance_id = ++$FV_Player_Custom_Videos_count;
    
    $html = "<div id='fv_edit_video-".$this->instance_id."'>
      <div class='fv_video_wrapper'>
        <div class='inside inside-child'>    
          <div class='video-preview'>".($video ? 'Loading...' : '')."</div>
          <input class='attachement-shortcode' id='widget-widget_fvplayer-".$this->instance_id."-text' name='fv_player_videos[".$this->meta."]' data-number='".$this->instance_id."' type='hidden' value='".esc_attr($video)."' />
          <div class='edit-video' ".(!$video ? 'style="display:none"' : '').">
            <button class='button fv-wordpress-flowplayer-button' data-number='".$this->instance_id."'>Edit Video</button>
            <button class='button remove-video' onclick='fv_remove_video( ".$this->instance_id." ); return false'>Remove Video</button>
          </div>

          <div class='add-video' ".($video ? 'style="display:none"' : '').">
            <button class='button fv-wordpress-flowplayer-button' data-number='". $this->instance_id."'>Add Video</button>
          </div>
        </div>
      </div>
    </div>";
    return $html;
  }

  public function get_html( $args = array() ) {
    
    $args = wp_parse_args( $args, array( 'wrapper' => 'div', 'edit' => false, 'limit' => 1000, 'shortcode' => false ) );
    
    $html = '';
    $count = 0;
    if( $this->have_videos() ) {
      foreach( $this->get_videos() AS $video ) {
        $count++;
        
        if( $args['wrapper'] ) $html .= '<'.$args['wrapper'].' class="fv-player-custom-video">';
        
        $html .= $this->get_html_part($video);
        
        if( $args['wrapper'] ) $html .= '</'.$args['wrapper'].'>'."\n";
        
      }
      
    } else if( $args['edit'] ) {
      $html .= '<'.$args['wrapper'].' class="fv-player-custom-video">';
        $html .= $this->get_html_part(false);
        
        /*$html .= "<input class='fv_player_custom_video fv_player_custom_video_url regular-text' placeholder='URL' type='text' name='fv_player_videos[".$this->meta."][]' /><br />\n";
        $html .= "<input class='fv_player_custom_video regular-text' placeholder='Title' type='text' name='fv_player_videos_titles[".$this->meta."][]' /><br />\n";
        if( 1 < $args['limit'] ) $html .= "<a class='fv-player-custom-video-add' href='#'>Add more</a>\n";*/
      
      $html .= '</'.$args['wrapper'].'>';      
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
    if( is_array($aMeta) && count($aMeta) > 0 ) {
      foreach( $aMeta AS $aVideo ) {
        if( is_array($aVideo) && isset($aVideo['url']) && isset($aVideo['title']) ) {
          $aVideos[] = '[fvplayer src="'.$this->esc_shortcode($aVideo['url']).'" caption="'.$this->esc_shortcode($aVideo['title']).'"]';
        } else if( is_string($aVideo) && stripos($aVideo,'[fvplayer ]') === 0 ) {
          $aVideos[] = $aVideo;
        }
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
        jQuery(".fv_player_field_insert-button").click( function() {
          if( <?php echo $this->instance_id; ?> == FVFP_sWidgetId ) fv_load_video_preview( FVFP_sWidgetId );
        } );
      
        jQuery(document).ready( function() {
          if( typeof(fv_wp_flowplayer_init) != "undefined" ) {
            fv_wp_flowplayer_init();
            for( var i=1; i < <?php echo $this->instance_id; ?>+1; i++ ) {  //  todo: stupid
              fv_load_video_preview(i);
            }
          }
        });
      
      </script>
    <?php
    
    global $FV_Player_Custom_Videos_loaded;
    if( $FV_Player_Custom_Videos_loaded == 0 ) :
      $FV_Player_Custom_Videos_loaded = 1;
      ?>
      <script>
      function fv_show_video( id, show ) {
        if( show ) {
          jQuery('#fv_edit_video-'+id+' .edit-video').show();
          jQuery('#fv_edit_video-'+id+' .add-video').hide();
        }
        else {
          jQuery('#fv_edit_video-'+id+' .edit-video').hide();
          jQuery('#fv_edit_video-'+id+' .add-video').show();
        }
      }

      function fv_remove_video( id ) {
        jQuery( '#widget-widget_fvplayer-'+id+'-text' ).val("");
        fv_show_video( id, false );
        jQuery('#fv_edit_video-'+id+' .video-preview').html('');
      }

      function fv_load_video_preview( id ) {
        var shortcode = jQuery('#widget-widget_fvplayer-'+id+'-text').val();
        if( shortcode.length === 0 ) {
          return false;
        }

        shortcode     = shortcode.replace( /(width=[\'"])\d*([\'"])/, "$1320$2" );  // 320
        shortcode     = shortcode.replace( /(height=[\'"])\d*([\'"])/, "$1240$2" ); // 240

        var preview_div = jQuery('#fv_edit_video-'+id+' .video-preview');
        var url = fv_Player_site_base + '?fv_player_embed=1&fv_player_preview=' + b64EncodeUnicode(shortcode);
        jQuery.get(url, function(response) {
          preview_div.html( jQuery('#wrapper',response ) );
          jQuery(document).trigger('fvp-preview-complete');
        } );

        fv_show_video( id, true );
      }

      ( function($) {
        $(window).resize( function() {
          $('.iframe_video_wrapper iframe').each( function() {
            if( $(this).data('ratio') ) $(this).height( $(this).width() * $(this).data('ratio') + 20 );
          });
        });
      })(jQuery);
      </script>
    <?php endif;
  }
  
  
  function shortcode_editor_load() {
    if( !function_exists('fv_flowplayer_admin_select_popups') ) {
      fv_wp_flowplayer_edit_form_after_editor();
      fv_player_shortcode_editor_scripts_enqueue();   
    }
  }
  
  
}




class FV_Player_Custom_Videos_Master {
  
  function __construct() {
    
    add_action( 'init', array( $this, 'save' ) ); //  saving of user profile, both front and back end    
    add_action( 'save_post', array( $this, 'save_post' ) );

    add_filter( 'show_password_fields', array( $this, 'user_profile' ), 10, 2 );
    add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 999, 2 );
    
    add_filter( 'the_content', array( $this, 'show' ) );
    add_filter( 'get_the_author_description', array( $this, 'show_bio' ), 10, 2 );
    
    //  EDD
    add_action('edd_profile_editor_after_email', array($this, 'EDD_profile_editor'));
    add_action('edd_pre_update_user_profile', array($this, 'save'));
    
    //  bbPress
    add_filter( 'bbp_template_after_user_profile', array( $this, 'bbpress_profile' ), 10, 2 );
    add_filter( 'bbp_user_edit_after_about', array( $this, 'bbpress_edit' ), 10, 2 );
  }
  
  function add_meta_boxes() {
    global $fv_fp;
    if( isset($fv_fp->conf['profile_videos_enable_bio']) && $fv_fp->conf['profile_videos_enable_bio'] == 'true' ) {
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
    
  }
  
  function bbpress_edit() {
    ?>
    </fieldset>
    
    <h2 class="entry-title"><?php _e( 'Videos', 'fv-wordpress-flowplayer' ); ?></h2>

    <fieldset class="bbp-form">
      
      <div>
        <?php
        $objVideos = new FV_Player_Custom_Videos(array( 'id' => bbp_get_displayed_user_field('id'), 'type' => 'user' ));
        echo $objVideos->get_form( array('no_form' => true) );
        ?>
      </div>
  
    <?php
  }
  
  function bbpress_profile() {
    global $fv_fp;
    
    if( !isset($fv_fp->conf['profile_videos_enable_bio']) || $fv_fp->conf['profile_videos_enable_bio'] !== 'true' ) 
      return;
    
    $objVideos = new FV_Player_Custom_Videos(array( 'id' => bbp_get_displayed_user_field('id'), 'type' => 'user' ));
    if( $objVideos->have_videos() ) : ?>
      <div id="bbp-user-profile" class="bbp-user-profile">
        <h2 class="entry-title"><?php _e( 'Videos', 'bbpress' ); ?></h2>
        <div class="bbp-user-section">
    
          <?php echo $objVideos->get_html(); ?>
    
        </div>
      </div><!-- #bbp-author-topics-started -->
    <?php endif;
  }
  
  function meta_box( $aPosts, $args ) {
    global $FV_Player_Custom_Videos_form_instances;
    unset($FV_Player_Custom_Videos_form_instances[$this->meta]);
          
    $objVideos = $args['args'];   
    echo $objVideos->get_form();
  }
  
  //  todo: fix for new code
  function save() {
    
    if( !isset($_POST['fv_player_videos']) || !isset($_POST['fv-player-custom-videos-entity-type']) || !isset($_POST['fv-player-custom-videos-entity-id']) ) {
      return;
    }
    
    
    
    //  todo: permission check!
    
    foreach( $_POST['fv_player_videos'] AS $meta => $value ) {
      if( $_POST['fv-player-custom-videos-entity-type'][$meta] == 'user' ) {
        delete_user_meta( $_POST['fv-player-custom-videos-entity-id'][$meta], $meta );

        if( strlen($value) == 0 ) continue;
              
        add_user_meta( $_POST['fv-player-custom-videos-entity-id'][$meta], $meta, $value );
      } 
      
    }
    
  }
  
  function save_post( $post_id ) {
    if( !isset($_POST['fv_player_videos']) || !isset($_POST['fv-player-custom-videos-entity-type']) || !isset($_POST['fv-player-custom-videos-entity-id']) ) {
      return;
    }
    
    //  todo: permission check!
    
    foreach( $_POST['fv_player_videos'] AS $meta => $value ) {
      if( $_POST['fv-player-custom-videos-entity-type'][$meta] == 'post' ) {
        delete_post_meta( $post_id, $meta );

        if( strlen($value) == 0 ) continue;
                
        add_post_meta( $post_id, $meta, $value );
      } 
      
    }
    
  }
  
  function show( $content ) {
    global $post, $fv_fp;
    if( isset($fv_fp->conf['profile_videos_enable_bio']) && $fv_fp->conf['profile_videos_enable_bio'] == 'true' && isset($post->ID) ) {
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
  
  function show_bio( $content, $user_id ) {
    global $fv_fp;
    if( !is_single() && isset($fv_fp->conf['profile_videos_enable_bio']) && $fv_fp->conf['profile_videos_enable_bio'] == 'true' ) {
      global $post;    
      $objVideos = new FV_Player_Custom_Videos( array('id' => $user_id, 'type' => 'user' ) );
      $html = $objVideos->get_html( array( 'wrapper' => false, 'shortcode' => array( 'width' => 272, 'height' => 153 ) ) );
      if( $html ) {
        $content .= $html."<div style='clear:both'></div>";
      }
    }
    return $content;
  }  
  
  function user_profile( $show_password_fields, $profileuser ) {
    global $fv_fp;
    if( isset($fv_fp->conf['profile_videos_enable_bio']) && $fv_fp->conf['profile_videos_enable_bio'] == 'true' ) {    
      if( $profileuser->ID > 0 ) {
        $objUploader = new FV_Player_Custom_Videos( array( 'id' => $profileuser->ID ) );
        ?>
        <tr class="user-videos">
          <th><?php _e( 'Videos', 'fv-wordpress-flowplayer' ); ?></th>
          <td>
            <?php
            
            echo $objUploader->get_form( array( 'wrapper' => 'div' ) );
            ?>
            <p class="description"><?php _e( 'You can put your Vimeo or YouTube links here.', 'fv-wordpress-flowplayer' ); ?> <abbr title="<?php _e( 'These show up as a part of the user bio. Licensed users get FV Player Pro which embeds these video types in FV Player interface without Vimeo or YouTube interface showing up.', 'fv-wordpress-flowplayer' ); ?>"><span class="dashicons dashicons-editor-help"></span></abbr></p>
          </td>
        </tr>
        <?php
      }
    }
    
    return $show_password_fields;
  }
  
  public function EDD_profile_editor(){ 
    global $fv_fp;
    
    if( !isset($fv_fp->conf['profile_videos_enable_bio']) || $fv_fp->conf['profile_videos_enable_bio'] !== 'true' ) 
      return;
    
    $user = new FV_Player_Custom_Videos(array( 'id' => get_current_user_id(), 'type' => 'user' ));
    ?>
        <p class="edd-profile-videos-label">
          <span for="edd_email"><?php _e( 'Profile Videos', 'fv-wordpress-flowplayer' ); ?></span>
            <?php echo $user->get_form(array('no_form' => true));?>
        </p>
    <?php
  }

}


$FV_Player_Custom_Videos_Master = new FV_Player_Custom_Videos_Master;
