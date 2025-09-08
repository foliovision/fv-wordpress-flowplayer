<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Custom_Videos {

  var $id;

  var $instance_id;

  private $meta;

  private $type;

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

    $args = wp_parse_args( $args, array( 'wrapper' => 'div', 'edit' => true, 'limit' => 1000, 'no_form' => false ) );

    $html = '';

    if( $args['wrapper'] != 'li' ) {
      $html .= '<div class="fv-player-custom-video-list">';
    }

    if( is_admin() ) {
      if( $this->have_videos() ) {
        global $FV_Player_Pro;
        if( isset($FV_Player_Pro) && $FV_Player_Pro ) {
          //  todo: there should be a better way than this
          if ( method_exists( $FV_Player_Pro, 'get__cached_splash' ) ) {
            add_filter( 'fv_flowplayer_splash', array( $FV_Player_Pro, 'get__cached_splash' ) );
            add_filter( 'fv_flowplayer_playlist_splash', array( $FV_Player_Pro, 'get__cached_splash' ), 10, 3 );
          }

          if ( method_exists( $FV_Player_Pro, 'youtube_splash' ) ) {
            add_filter( 'fv_flowplayer_splash', array( $FV_Player_Pro, 'youtube_splash' ) );
            add_filter( 'fv_flowplayer_playlist_splash', array( $FV_Player_Pro, 'youtube_splash' ), 10, 3 );
          }

          if ( method_exists( $FV_Player_Pro, 'styles' ) ) {
            add_action('admin_footer', array( $FV_Player_Pro, 'styles' ) );
          }

          if ( method_exists( $FV_Player_Pro, 'scripts' ) ) {
            add_action('admin_footer', array( $FV_Player_Pro, 'scripts' ) );  //  todo: not just for FV Player Pro
          }
        }

        add_action('admin_footer','flowplayer_prepare_scripts');
      }

      add_action('admin_footer', array( $this, 'shortcode_editor_load' ), 0 );
    }

    if( !is_admin() && !$args['no_form'] ) $html .= "<form method='POST'>";

    $html .= $this->get_html( $args );

    $html .= wp_nonce_field( 'fv-player-custom-videos-'.$this->meta.'-'.get_current_user_id(), 'fv-player-custom-videos-'.$this->meta.'-'.get_current_user_id(), true, false );

    if( !is_admin() && !$args['no_form'] ) {
      $html .= "<input type='hidden' name='action' value='fv-player-custom-videos-save' />";
      $html .= "<input type='submit' value='Save Videos' />";
      $html .= "</form>";
    }

    if( $args['wrapper'] != 'li' ) {
      $html .= '</div>';
    }

    return $html;
  }

  public function get_html_part( $video, $edit = false ) {
    global $post;

    $defaults = array( 'labels' => array( 'edit' => 'Edit Video', 'remove' => 'Remove Video' ), 'multiple' => true );
    $args = !empty($post) && !empty( FV_Player_Custom_Videos_Master()->aMetaBoxes[$post->post_type]) ? FV_Player_Custom_Videos_Master()->aMetaBoxes[$post->post_type][$this->meta] : $defaults;

    if( $video ) {
      $video = wp_kses( $video, 'post' );
    }

    //  exp: what matters here is .fv-player-editor-field and .fv-player-editor-button wrapped in  .fv-player-editor-wrapper and .fv-player-editor-preview
    if( $edit ) {
      $add_another = $args['multiple'] ? "<button class='button fv-player-editor-more' style='display:none'>Add Another Video</button>" : false;

      $preview = false;
      $before = 0;

      if( $video ) {
        global $fv_fp;

        $preview = do_shortcode( str_replace( '[fvplayer ', '[fvplayer autoplay="false" ', $video ) );

        if( $fv_fp->current_player() ) {
          $before = count($fv_fp->current_player()->getVideos());
        }

        // Previously we added autoplay="false" to the stored shortcodes by accident, so remove it here
        $video = str_replace( 'autoplay="false"', '', $video );
      }

      $html = "<div class='fv-player-editor-wrapper' data-key='fv-player-editor-field-".$this->meta."'>
          <div class='fv-player-editor-preview'>".$preview."</div>
          <input class='attachement-shortcode fv-player-editor-field' name='fv_player_videos[".$this->meta."][]' type='hidden' value='".esc_attr($video)."' />
          <input name='fv_player_videos_before[".$this->meta."][]' type='hidden' value='".$before."' />
          <div class='edit-video' ".(!$video ? 'style="display:none"' : '').">
            <button class='button fv-player-editor-button'>".$args['labels']['edit']."</button>
            <button class='button fv-player-editor-remove'>".$args['labels']['remove']."</button>
            $add_another
          </div>

          <div class='add-video' ".($video ? 'style="display:none"' : '').">
            <button class='button fv-player-editor-button'>Add Video</button>
          </div>
        </div>";
    } else {
      $html = do_shortcode($video);
    }
    return $html;
  }

  public function get_html( $args = array() ) {

    $args = wp_parse_args( $args, array( 'wrapper' => 'div', 'edit' => false, 'limit' => 1000, 'shortcode' => false ) );

    $html = '';
    $count = 0;
    if( $this->have_videos() ) {

      if( $args['wrapper'] ) $html .= '<'.$args['wrapper'].' class="fv-player-custom-video">';

      foreach( $this->get_videos() AS $video ) {
        $count++;
        $html .= $this->get_html_part($video, $args['edit']);
      }

      $html .= '<div style="clear: both"></div>'."\n";

      if( $args['wrapper'] ) $html .= '</'.$args['wrapper'].'>'."\n";

    } else if( $args['edit'] ) {
      $html .= '<'.$args['wrapper'].' class="fv-player-custom-video">';
        $html .= $this->get_html_part(false, true);
        $html .= '<div style="clear: both"></div>'."\n";
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
          $aVideos[] = '[fvplayer src="'.$this->esc_shortcode($aVideo['url']).'" title="'.$this->esc_shortcode($aVideo['title']).'"]';
        } else if( is_string($aVideo) && stripos($aVideo,'[fvplayer ') === 0 ) {
          $aVideos[] = $aVideo;
        }
      }
    }

    return $aVideos;
  }

  public function have_videos() {
    return count($this->get_videos()) ? true : false;
  }

  function shortcode_editor_load() {
    if( !function_exists('fv_flowplayer_admin_select_popups') ) {
      fv_wp_flowplayer_edit_form_after_editor();
      fv_player_shortcode_editor_scripts_enqueue();
    }
  }


}




class FV_Player_Custom_Videos_Master {

  static $instance = null;

  var $aMetaBoxes = array();

  var $aPostListPlayers = array();

  function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    add_action( 'init', array( $this, 'save' ) ); //  saving of user profile, both front and back end
    add_action( 'save_post', array( $this, 'save_post' ) );

    add_filter( 'show_password_fields', array( $this, 'user_profile' ), 10, 2 );
    add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

    add_filter( 'the_content', array( $this, 'show' ) );  //  adding post videos after content automatically
    add_filter( 'get_the_author_description', array( $this, 'show_bio' ), 10, 2 );

    //  EDD
    add_action('edd_profile_editor_after_email', array($this, 'EDD_profile_editor'));
    add_action('edd_pre_update_user_profile', array($this, 'save'));

    //  bbPress
    add_action( 'bbp_template_after_user_profile', array( $this, 'bbpress_profile' ), 10 );
    add_filter( 'bbp_user_edit_after_about', array( $this, 'bbpress_edit' ), 10, 2 );

    // Post list custom columns
    add_action( 'admin_head-edit.php', array( $this, 'post_list_column_styles' ) );
    add_action( 'admin_init', array( $this, 'init_post_list_columns' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'init_post_list_columns_script' ) );
    add_filter( 'the_posts', array( $this, 'preload_post_list_players' ) );

    // Admin Columns Pro support
    /*
     * That plugin ignores the standard 'manage_'.$post_type.'_columns' hooks
     * but fortunately we can still add it this way. Then the
     * 'manage_'.$post_type.'_custom_column' hook works for the column content
     */
    add_filter( 'ac/headings', array( $this, 'post_list_column_for_admin_columns_pro' ), 10, 2 );
  }

  public static function _get_instance() {
    if( !self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  function add_meta_boxes() {
    global $post;
    if ( ! empty( $post->post_type ) && ! empty( $this->aMetaBoxes[ $post->post_type ] ) ) {
      foreach( $this->aMetaBoxes[$post->post_type] AS $meta_key => $args ) {
        global $FV_Player_Custom_Videos_form_instances;
        $id = 'fv_player_custom_videos-field_'.$meta_key;
        $FV_Player_Custom_Videos_form_instances[$id] = new FV_Player_Custom_Videos( array('id' => $post->ID, 'meta' => $args['meta_key'], 'type' => 'post' ) );
        add_meta_box( $id,
                    $args['name'],
                    array( $this, 'meta_box' ),
                    null,
                    'normal',
                    'high'
                    );
      }
    }

    //  todo: following code should not add the meta boxes added by the above again!

    global $fv_fp;
    if( isset($fv_fp->conf['profile_videos_enable_bio']) && $fv_fp->conf['profile_videos_enable_bio'] == 'true' ) {
      $aMeta = get_post_custom($post->ID);
      if( $aMeta ) {
        foreach( $aMeta AS $key => $aMetas ) {
          $objVideos = new FV_Player_Custom_Videos( array('id' => $post->ID, 'meta' => $key, 'type' => 'post' ) );
          if( $objVideos->have_videos() ) {
            global $FV_Player_Custom_Videos_form_instances;
            $id = 'fv_player_custom_videos-field_'.$key;
            $FV_Player_Custom_Videos_form_instances[$id] = $objVideos;
            add_meta_box( $id,
                        ucfirst(str_replace( array('_','-'),' ',$key)),
                        array( $this, 'meta_box' ),
                        null,
                        'normal',
                        'high' );
          }

        }
      }
    }

  }

  function bbpress_edit() {
    ?>
    </fieldset>

    <h2 class="entry-title"><?php esc_attr_e(  'Videos', 'fv-player' ); ?></h2>

    <fieldset class="bbp-form">

      <div>
        <?php
        $objVideos = new FV_Player_Custom_Videos(array( 'id' => bbp_get_displayed_user_field('ID'), 'type' => 'user' ));

        global $fv_fp;
        add_filter( 'wp_kses_allowed_html', array( $fv_fp, 'wp_kses_permit' ), 10, 2 );

        add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
        add_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );

        echo wp_kses_post( $objVideos->get_form( array('no_form' => true) ) );

        remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
        remove_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );
        ?>
      </div>

    <?php

    if( !function_exists('is_plugin_active') ) include( ABSPATH . 'wp-admin/includes/plugin.php' );
    if( !function_exists('fv_player_extension_version_is_min') ) include( dirname( __FILE__ ) . '/../controller/backend.php' );
    if( !function_exists('fv_wp_flowplayer_edit_form_after_editor') ) include( dirname( __FILE__ ) . '/../controller/editor.php' );

    fv_wp_flowplayer_edit_form_after_editor();
    fv_player_shortcode_editor_scripts_enqueue();
  }

  function bbpress_profile() {
    global $fv_fp;

    if( !isset($fv_fp->conf['profile_videos_enable_bio']) || $fv_fp->conf['profile_videos_enable_bio'] !== 'true' )
      return;

    $objVideos = new FV_Player_Custom_Videos(array( 'id' => bbp_get_displayed_user_field('ID'), 'type' => 'user' ));
    if( $objVideos->have_videos() ) : ?>
      <div id="bbp-user-profile" class="bbp-user-profile">
        <h2 class="entry-title"><?php esc_attr_e( 'Videos', 'bbpress' ); ?></h2>
        <div class="bbp-user-section">

          <?php
          global $fv_fp;
          add_filter( 'wp_kses_allowed_html', array( $fv_fp, 'wp_kses_permit' ), 10, 2 );

          add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
          add_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );

          echo wp_kses_post( $objVideos->get_html() );

          remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
          remove_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );
          ?>

        </div>
      </div><!-- #bbp-author-topics-started -->
    <?php endif;
  }

  function has_post_type( $post_type ) {
    return !empty( $this->aMetaBoxes[ $post_type ] );
  }

  // Add post list column for post types which do have a FV Player Video Custom Field
  function init_post_list_columns() {
    if( !empty($this->aMetaBoxes) ) {
      foreach( $this->aMetaBoxes AS $post_type => $boxes ) {
        if( $post_type == 'post' ) {
          $post_type = 'posts';
        } else if( $post_type == 'page' ) {
          $post_type = 'pages';
        } else {
          $post_type = $post_type.'_posts';
        }

        add_filter( 'manage_'.$post_type.'_columns', array( $this, 'post_list_column' ) );
        add_filter( 'manage_'.$post_type.'_custom_column', array( $this, 'post_list_column_content' ), 10, 2 );
      }
    }
  }

  function init_post_list_columns_script() {
    global $current_screen;
    if( !empty($current_screen->post_type) && $this->has_post_type($current_screen->post_type) ) {
      fv_player_shortcode_editor_scripts_enqueue();

      // We use 0 priority to ensure both FV Player playback and editor scripts load
      add_action( 'admin_footer', 'fv_wp_flowplayer_edit_form_after_editor', 0 );

      do_action( 'fvplayer_editor_load' );

      wp_enqueue_media();
    }
  }

  function meta_box( $aPosts, $args ) {
    global $FV_Player_Custom_Videos_form_instances;
    $objVideos = $FV_Player_Custom_Videos_form_instances[$args['id']];

    global $fv_fp;
    add_filter( 'wp_kses_allowed_html', array( $fv_fp, 'wp_kses_permit' ), 10, 2 );

    add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
    add_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );

    echo wp_kses_post( $objVideos->get_form() );

    remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
    remove_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );
  }

  function post_list_column( $cols ) {
    global $current_screen;
    if( !empty($current_screen->post_type) && $this->has_post_type($current_screen->post_type) ) {
      foreach( $this->aMetaBoxes[$current_screen->post_type] AS $box ) {
        $cols = $this->post_list_column_add( $cols, $box );
      }
    }
    return $cols;
  }

  function post_list_column_add( $cols, $box ) {
    // Column form player video count
    $cols[ 'fv-player-video-custom-field-playlist-items-count-'.$box['meta_key'] ] = $box['name'].' playlist videos count';

    // Actual player splash image here
    $cols[ 'fv-player-video-custom-field-player-'.$box['meta_key'] ] = $box['name'];
    return $cols;
  }

  function post_list_column_content( $column_name, $post_id ) {
    global $current_screen;
    if( stripos( $column_name, 'fv-player-video-custom-field-' ) === 0 && !empty($current_screen->post_type) && !empty($this->aMetaBoxes[$current_screen->post_type]) ) {

      // TODO: Load the wp-admin -> FV Player styles more sensibly
      global $fv_wp_flowplayer_ver;
      wp_enqueue_style('fv-player-list-view', flowplayer::get_plugin_url().'/css/list-view.css',array(), $fv_wp_flowplayer_ver );

      foreach( $this->aMetaBoxes[$current_screen->post_type] AS $box ) {
        $column_name_sanitized = str_replace( array(
          'fv-player-video-custom-field-playlist-items-count-',
          'fv-player-video-custom-field-player-'
        ), '', $column_name );

        if( $column_name_sanitized == $box['meta_key'] ) {
          $shortcode = get_post_meta( $post_id, $box['meta_key'], true );
          $shortcode_atts = shortcode_parse_atts( trim( $shortcode, ']' ) );

          if( !empty($shortcode_atts['id']) ) {
            $button_text = 'FV Player #'.$shortcode_atts['id'];
            $video_count = 0;

            // Did we preload the right player?
            $found = false;
            foreach( $this->aPostListPlayers AS $objPostPlayer ) {
              if( $objPostPlayer->id == $shortcode_atts['id'] ) {
                $button_text = $objPostPlayer->thumbs[0];

                $video_count = count( $objPostPlayer->thumbs );
                if( $video_count > 1 ) {
                  $video_count .= 'v';
                } else {
                  $video_count = '';
                }

                $found = true;
                break;
              }
            }

            // Fallback if it was not preloaded
            if( !$found ) {
              global $FV_Player_Db;
              $objPlayers = $FV_Player_Db->getListPageData( array(
                'player_id' => $shortcode_atts['id']
              ) );

              // The above function always gives back the FV Player PHP Players cache, so we need to loop through results
              foreach( $objPlayers AS $objPlayer ) {
                if( $objPlayer->id == $shortcode_atts['id'] ) {
                  // Only show the first thumbnail
                  $button_text = $objPlayer->thumbs[0];

                  // The above FV_Player_Db::getListPageData() call it supposed to only occur once, it seems $objPlayer->video_objects contains all the videos in cache and that's bad
                  // So we check the HTML :(
                  $video_count = count( $objPlayer->thumbs );
                  if( $video_count > 1 ) {
                    $video_count .= 'v';
                  } else {
                    $video_count = '';
                  }

                  $found = true;
                  break;
                }
              }
            }

            if( stripos( $column_name, 'fv-player-video-custom-field-player' ) === 0 ) {
              echo '<input type="hidden" value="'.esc_attr($shortcode).'" />';

              echo '<a href="#" class="fv-player-edit" data-player_id="' . intval( $shortcode_atts['id'] ) . '">' . wp_kses( $button_text, array(
                'div'  => array(
                  'class'   => array(),
                ),
                'img'  => array(
                  'alt'     => array(),
                  'loading' => array(),
                  'src'     => array(),
                  'title'   => array(),
                  'width'   => array(),
                ),
                'span' => array()
              ) ) . '</a>';

            } else if( stripos( $column_name, 'fv-player-video-custom-field-playlist-items-count-' ) === 0 ) {
              echo esc_html( $video_count );
            }

          } else if( stripos( $column_name, 'fv-player-video-custom-field-player' ) === 0 ) {
            echo '<a href="#" class="fv-player-edit" data-post-id="' . intval( $post_id ) . '" data-meta_key="' . esc_attr( $box['meta_key'] ) . '">Add new player</a>';

          }
        }
      }
    }
  }

  function post_list_column_for_admin_columns_pro( $headings, $list_screen ) {
    if( method_exists( $list_screen, 'get_post_type' ) ) {
      if( $post_type = $list_screen->get_post_type() ) {
        if( $this->has_post_type($post_type) ) {
          foreach( $this->aMetaBoxes[$post_type] AS $box ) {
            $headings = $this->post_list_column_add( $headings, $box );
          }
        }
      }
    }
    return $headings;
  }

  function post_list_column_styles() {
    global $current_screen;
    if( !empty($current_screen->post_type) && $this->has_post_type($current_screen->post_type) ) {
      // Set fixed column width to fit the number of videos and hide its label
      // ..and fixed width for the player splash
      ?>
      <style>
      #fv-player-video-custom-field-playlist-items-count-fv_player, tfoot .column-fv-player-video-custom-field-playlist-items-count-fv_player { text-indent: -9999px; width: 2em }
      td.column-fv-player-video-custom-field-playlist-items-count-fv_player { padding-left: 0; padding-right: 0 }
      #fv-player-video-custom-field-player-fv_player { width: 148px; }
      </style>
      <?php
    }
  }

  /*
   * The preload might not succeed, for example Admin Columns Pro seems to run WP_Query without the_posts filter
   */
  function preload_post_list_players( $posts ) {

    // Only do this once as you never know what plugin might call the_posts filter multiple times
    static $finished;

    if ( ! empty( $finished ) ) {
      return $posts;
    }

    // Are we looking at the wp-admin list of posts?
    global $current_screen;
    if( !is_admin() || empty($current_screen->post_type) || 'edit' !== $current_screen->base || !$this->has_post_type($current_screen->post_type) ) {
      return $posts;
    }

    $finished = true;

    $players = array();
    foreach( $posts AS $post ) {
      if( !empty($this->aMetaBoxes[$post->post_type]) ) {
        foreach( $this->aMetaBoxes[$post->post_type] AS $box ) {

          // Get shortcode and player ID
          $shortcode = get_post_meta( $post->ID, $box['meta_key'], true );
          $shortcode_atts = shortcode_parse_atts( trim( $shortcode, ']' ) );
          if( !empty($shortcode_atts['id']) ) {
            $players[] = $shortcode_atts['id'];
          }
        }
      }
    }

    // Somehow calling it with empty array would pre-load all the players an videos
    if( count($players) ) {
      global $FV_Player_Db;
      $this->aPostListPlayers = $FV_Player_Db->getListPageData( array(
        'player_id' => $players
      ) );
    }

    return $posts;
  }

  function register_metabox( $args ) {
    if( !isset($this->aMetaBoxes[$args['post_type']]) ) $this->aMetaBoxes[$args['post_type']] = array();

    $this->aMetaBoxes[$args['post_type']][$args['meta_key']] = $args;
  }


  function save() {
    if( !isset($_POST['fv_player_videos']) || !isset($_POST['fv-player-custom-videos-entity-type']) || !isset($_POST['fv-player-custom-videos-entity-id']) ) {
      return;
    }

    foreach( $_POST['fv_player_videos'] AS $meta => $videos ) {
      $meta = sanitize_text_field( $meta );

      if( !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv-player-custom-videos-'.$meta.'-'.get_current_user_id()] ) ),'fv-player-custom-videos-'.$meta.'-'.get_current_user_id() ) ) {
        continue;
      }

      if( sanitize_key( $_POST['fv-player-custom-videos-entity-type'][$meta] ) == 'user' ) {
        delete_user_meta( absint( $_POST['fv-player-custom-videos-entity-id'][$meta] ), $meta );

        foreach( $videos AS $video ) {
          if( strlen($video) == 0 ) continue;

          // strip html tags to prevent XSS
          $video = sanitize_text_field( $video );

          add_user_meta( absint( $_POST['fv-player-custom-videos-entity-id'][$meta] ), $meta, $video );
        }
      }
    }
  }

  function save_post( $post_id ) {
    if( !isset($_POST['fv_player_videos']) || !isset($_POST['fv-player-custom-videos-entity-type']) || !isset($_POST['fv-player-custom-videos-entity-id']) ) {
      return;
    }

    foreach( $_POST['fv_player_videos'] AS $meta => $value ) {
      $meta = sanitize_text_field( $meta );

      if( !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv-player-custom-videos-'.$meta.'-'.get_current_user_id()] ) ),'fv-player-custom-videos-'.$meta.'-'.get_current_user_id() ) ) {
        continue;
      }

      if( sanitize_key( $_POST['fv-player-custom-videos-entity-type'][$meta] ) == 'post' && absint( $_POST['fv-player-custom-videos-entity-id'][$meta] ) == $post_id ) {
        delete_post_meta( $post_id, $meta );

        if( is_array($value) && count($value) > 0 ) {
          foreach( $value AS $k => $v ) {
            if( strlen($v) == 0 ) continue;

            // strip html tags to prevent XSS
            $v = sanitize_text_field( $v );

            add_post_meta( $post_id, $meta, $v );
          }
        }
      }

    }

  }

  function show( $content ) {
    global $post, $fv_fp;
    if( isset($fv_fp->conf['profile_videos_enable_bio']) && $fv_fp->conf['profile_videos_enable_bio'] == 'true' && isset($post->ID) ) {
      $aMeta = get_post_custom($post->ID);
      if( $aMeta ) {
        foreach( $aMeta AS $key => $aMetas ) {
          if( !empty($this->aMetaBoxes[$post->post_type][$key]) && $this->aMetaBoxes[$post->post_type][$key]['display'] ) {
            $objVideos = new FV_Player_Custom_Videos( array('id' => $post->ID, 'meta' => $key, 'type' => 'post' ) );
            if( $objVideos->have_videos() ) {
              $content .= $objVideos->get_html();
            }
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
          <th><?php esc_attr_e(  'Videos', 'fv-player' ); ?></th>
          <td>
            <?php
            global $fv_fp;
            add_filter( 'wp_kses_allowed_html', array( $fv_fp, 'wp_kses_permit' ), 10, 2 );

            add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
            add_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );

            echo wp_kses_post( $objUploader->get_form( array( 'wrapper' => 'div' ) ) );

            remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
            remove_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );
            ?>

            <p class="description"><?php esc_attr_e(  'You can put your Vimeo or YouTube links here.', 'fv-player' ); ?> <abbr title="<?php esc_attr_e(  'These show up as a part of the user bio. Licensed users get FV Player Pro which embeds these video types in FV Player interface without Vimeo or YouTube interface showing up.', 'fv-player' ); ?>"><span class="dashicons dashicons-editor-help"></span></abbr></p>
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
          <span for="edd_email"><?php esc_attr_e(  'Profile Videos', 'fv-player' ); ?></span>
            <?php
            global $fv_fp;
            add_filter( 'wp_kses_allowed_html', array( $fv_fp, 'wp_kses_permit' ), 10, 2 );

            add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
            add_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );

            echo wp_kses_post( $user->get_form(array('no_form' => true)) );

            remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses' ) );
            remove_filter( 'safe_style_css', array( $this, 'safe_style_css' ) );
            ?>
        </p>
    <?php

    if( !function_exists('is_plugin_active') ) include( ABSPATH . 'wp-admin/includes/plugin.php' );
    if( !function_exists('fv_player_extension_version_is_min') ) include( dirname( __FILE__ ) . '/../controller/backend.php' );
    if( !function_exists('fv_wp_flowplayer_edit_form_after_editor') ) include( dirname( __FILE__ ) . '/../controller/editor.php' );

    fv_wp_flowplayer_edit_form_after_editor();
    fv_player_shortcode_editor_scripts_enqueue();
  }

  // Used to permit FV Player's Custom Video Field markup
  public function safe_style_css( $styles ) {
    $styles[] = 'display';
    return $styles;
  }

  // Used to permit FV Player's Custom Video Field markup
  public function wp_kses( $tags ) {

    if ( empty($tags['defs']) ) {
      $tags['defs'] = array();
    }

    if ( empty($tags['iframe']) ) {
      $tags['iframe'] = array();
    }

    $tags['iframe']['id'] = true;
    $tags['iframe']['src'] = true;
    $tags['iframe']['width'] = true;
    $tags['iframe']['height'] = true;
    $tags['iframe']['frameborder'] = true;
    $tags['iframe']['webkitallowfullscreen'] = true;
    $tags['iframe']['mozallowfullscreen'] = true;
    $tags['iframe']['allowfullscreen'] = true;

    if ( empty($tags['input']) ) {
      $tags['input'] = array();
    }

    $tags['input']['class'] = true;
    $tags['input']['id'] = true;
    $tags['input']['name'] = true;
    $tags['input']['onclick'] = true;
    $tags['input']['type'] = true;
    $tags['input']['value'] = true;

    $tags['noscript'] = array();

    if ( empty($tags['path']) ) {
      $tags['path'] = array();
    }

    $tags['path']['class'] = true;
    $tags['path']['d'] = true;

    if ( empty($tags['polygon']) ) {
      $tags['polygon'] = array();
    }

    $tags['polygon']['class'] = true;
    $tags['polygon']['points'] = true;
    $tags['polygon']['filter'] = true;

    if ( empty($tags['style']) ) {
      $tags['style'] = array();
    }

    if ( empty($tags['svg']) ) {
      $tags['svg'] = array();
    }

    $tags['svg']['class'] = true;
    $tags['svg']['viewbox'] = true;
    $tags['svg']['xmlns'] = true;

    if ( empty($tags['textarea']) ) {
      $tags['textarea'] = array();
    }

    $tags['textarea']['onclick'] = true;

    return $tags;
  }

}


function FV_Player_Custom_Videos_Master() {
  return FV_Player_Custom_Videos_Master::_get_instance();
}

FV_Player_Custom_Videos_Master();


class FV_Player_MetaBox {

  function __construct( $args, $meta_key = false, $post_type = false, $display = false ) {
    if( is_string($args) ) {
      $args = array(
                    'name' => $args,
                    'meta_key' => $meta_key,
                    'post_type' => $post_type,
                    'display' => $display
                   );
    }

    $args = wp_parse_args( $args, array(
      'display' => false,
      'multiple' => true,
      'labels' => array(
        'edit' => 'Edit Video',
        'remove' => 'Remove Video'
      ) ) );

    FV_Player_Custom_Videos_Master()->register_metabox($args);
  }

}
