<?php

abstract class FV_Player_Video_Encoder {
  private
    $encoder_id = '',          // used for unique action names and asset names (CSS, JS)
                               // examples: coconut, bunny_stream, dos ...
    $encoder_wp_url_slug = '', // used in all links that will point to the list of this encoder jobs
                               // examples: fv_player_coconut, fv_player_bunny_stream ...
    $encoder_name = '',        // used to display name of the service where appropriate (mostly information DIVs in a HTML output)
                               // examples: Coconut, Bunny Stream ...
    $instance = null,          // self-explanatory
    $admin_page = false,       // will be set to a real admin submenu page object once created
    $browser_inc_file = '',    // the full inclusion path for this Encoder's browser PHP backend file, so we can include_once() it
    $use_wp_list_table;        // allow descendants to decide if use wp list table

  // variables to override or access from outside of the base class
  protected
    $version = 'latest',
    $license_key = false;

  public
    $table_name = 'fv_player_encoding_jobs'; // table in which encoding jobs are stored

  public function _get_instance() {
    return $this->instance;
  }

  public function get_version() {
    return $this->version;
  }

  public function get_table_name() {
    return $this->table_name;
  }

  protected function __construct( $encoder_id, $encoder_name, $encoder_wp_url_slug, $browser_inc_file = '', $use_wp_list_table = true ) {
    global $wpdb;

    if ( !$encoder_id ) {
      throw new Exception('Extending encoder class did not provide an encoder ID!');
    }

    if ( !$encoder_name ) {
      throw new Exception('Extending encoder class did not provide an encoder name!');
    }

    if ( !$encoder_wp_url_slug ) {
      throw new Exception('Extending encoder class did not provide an encoder URL slug!');
    }

    $this->encoder_id = $encoder_id;
    $this->encoder_name = $encoder_name;
    $this->encoder_wp_url_slug = $encoder_wp_url_slug;

    // table names always start on WP prefix, so add that here for our table name here
    $this->table_name = $wpdb->prefix . $this->table_name;
    $this->browser_inc_file = $browser_inc_file;
    $this->use_wp_list_table = $use_wp_list_table;

    add_action('init', array( $this, 'email_notification' ), 7 );

    if( is_admin() ) {
      add_action( 'admin_menu', array($this, 'admin_menu'), 11 );

      add_filter( 'fv_player_conf_defaults', array( $this, 'default_settings' ), 10, 2 );

      $version = get_option( 'fv_player_' . $this->encoder_id . '_ver' );
      if( $this->version != $version ) {
        update_option( 'fv_player_' . $this->encoder_id . '_ver', $this->version );

        // This is where FV Player will set any default settings.
        // We have to do this again as we only init this plugin on plugins_loaded after $fv_fp has been created with the default settings initialized.
        global $fv_fp;
        if( !empty($fv_fp) && method_exists( $fv_fp, '_get_conf' ) ) {
          $fv_fp->_get_conf();
        }

        $this->plugin_update_database();
      }

      add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

      add_action( 'wp_ajax_fv_player_' . $this->encoder_id .'_submit', array( $this, 'ajax_fv_player_job_submit') );

      add_action( 'wp_ajax_fv_player_' . $this->encoder_id .'_delete_job', array( $this, 'ajax_fv_player_delete_job') );

      //add_action( 'plugins_loaded', array( $this, 'init_browser') );
      // this file is actually only included after the 'plugins_loaded' action was fired, so let's run this method manually
      $this->init_browser();

      // we use a custom taxonomy to categorize the jobs
      add_action( 'admin_init', array( $this, 'create_encoding_categories' ) );

      // when a new encoding category gets added, we don't want it to show on top of the list
      add_filter( 'wp_terms_checklist_args', array( $this, 'category_picker_args' ) );

      // Periodically update jobs status when wp hearbeat is fired
      add_filter( 'heartbeat_received', array( $this, 'heartbeat_check' ), 10, 3 );

      // Editor enhancements to store job ID with video
      add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_editor_scripts'));
      add_action( 'fv_flowplayer_shortcode_editor_item_after', array( $this, 'shortcode_editor_item' ) );

      add_filter('plugin_action_links', array( $this, 'admin_plugin_action_links' ), 10, 2);

      $options = get_option( 'fvwpflowplayer' );
      if( !empty($options[ $this->encoder_id ]) && !empty($options[ $this->encoder_id ]['license_key']) ) {
        $this->license_key = $options[ $this->encoder_id ]['license_key'];
      }

      add_action( 'admin_notices', array( $this, 'admin_notices' ) );

      add_action( 'fv_player_video_encoder_include_listing_lib', array( $this, 'include_listing_lib' ), 10, 0 );
    }

    add_action( 'fv_player_item', array( $this, 'check_playlist_video_is_processing' ) );
  }

  /**
   * Includes a generic jobs listing library, so it can be used outside of this class,
   * i.e. by extending Encoder classes, when needed.
   */
  public function include_listing_lib() {
    require_once dirname( __FILE__ ) . '/class.fv-player-encoder-list-table.php';
  }

  /**
   * Checks whether the video is being processed by the extending Encoder
   * and if so, includes JS & CSS for that Encoder (+ global overlay CSS) on page, so the extending class
   * can display overlays with error / progress messages.
   *
   * @param $item array The actual video item to check.
   *
   * @return array Returns an augmented video item data, if its source was found to be a video in encoding process.
   */
  public function check_playlist_video_is_processing( $item ) {
    if ( is_array($item['sources']) ) {
      foreach( $item['sources'] as $source ) {
        if ( strpos($source['src'], $this->encoder_id . '_processing_' ) !== false ) {
          $item['pending_encoding'] = true;

          $job_id = explode( $this->encoder_id. '_processing_', $source['src'] );
          if( !empty($job_id[1]) ) {
            $job_id = $job_id[1];

            $check = $this->update_temporary_job_src( false, $job_id );
            if( !empty($check['progress']) && ( $check['status'] != 'error' )  ) {
              $item['pending_encoding_progress'] = $check['progress'];
            } else {
              $item['pending_encoding_error'] = true;
            }
          }
        }
      }
    }

    return $item;
  }

  /**
   * Enqueues a JS file for shortcode editor when needed on the backend pages.
   *
   * @param $page The identifier of a page we're currently viewing.
   */
  public function admin_enqueue_editor_scripts($page) {
    if( $page == 'post.php' || $page == 'post-new.php' || $page == 'toplevel_page_fv_player' ) {

      $file = $this->locate_script('shortcode-editor.js');
      if( $file ) {
        $handle = 'fvplayer-shortcode-editor-' . $this->encoder_id;
        wp_enqueue_script( $handle, plugins_url( $file, $this->getFILE() ), array('jquery'), filemtime( dirname( $this->getFILE() ) . $file), true );
      }

    }
  }

  /**
   * Periodically updates jobs status when wp hearbeat is fired.
   *
   * @param $response  array The heartbeat response body which we're augmenting with our job data.
   * @param $data      array containing IDs of jobs pending encoding for the current extending Encoder class.
   * @param $screen_id string ID of the page we're currently viewing.
   *
   * @return mixed
   */
  public function heartbeat_check( $response, $data, $screen_id ) {
    if( strcmp( 'fv-player_page_' . $this->encoder_wp_url_slug, $screen_id ) == 0 ) {
      if( isset($data[ $this->encoder_id . '_pending' ]) ) {
        $ids = $data[ $this->encoder_id .  '_pending' ];
        $response[ $this->encoder_id . '_still_pending'] = $this->jobs_check(true); // update pending job in js
        $rows_html = $this->get_updated_rows( $ids );
        $response[ $this->encoder_id ] = $rows_html; // html for jobs
      }
    }

    return $response;
  }

  function locate_script( $script ) {
    $file = false;
    if( file_exists( dirname( $this->getFILE() ) . '/../js/'.$this->encoder_id.'-'.$script ) ) {
      $file = '/../js/'.$this->encoder_id.'-'.$script;
    } else if( file_exists( dirname( $this->getFILE() ).'/js/'.$script ) ) {
      $file = '/js/'.$script;
    }
    return $file;
  }

  /**
   * Returns augmented arguments array for the category picker with the option for "checked_ontop" set to FALSE.
   *
   * @param $args The original arguments array for the category picker.
   *
   * @return array Returns augmented arguments array for the category picker with the option for "checked_ontop" set to FALSE.
   */
  function category_picker_args( $args ) {
    if( !empty($_POST['action']) && strcmp( sanitize_key( $_POST['action'] ), 'add-fv_player_encoding_category') == 0 ) {
      $args['checked_ontop'] = false;
    }
    return $args;
  }

  /**
   * Creates encoding categories taxonomy.
   */
  function create_encoding_categories() {
    register_taxonomy(
      'fv_player_encoding_category',
      'fv_player_encoding_job',
      array(
        'hierarchical' => true,
        'rewrite' => false // we only need the category names
      )
    );
  }

  /**
   * Adds FV Player admin menu item to show jobs for this Encoder.
   */
  function admin_menu(){
    if( current_user_can('edit_posts')  ) {

      $title = $this->encoder_name . ( $this->is_configured() ? ' Jobs' : '' );

      $this->admin_page = add_submenu_page(  'fv_player', $title, $title, 'edit_posts', $this->encoder_wp_url_slug, array( $this, 'tools_panel' ) );

      if( $this->is_configured() ) {
        add_action( 'load-'.$this->admin_page, array( $this, 'screen_options' ) );
        //add_filter( 'manage_toplevel_page_fv_player_columns', array( $this, 'screen_columns' ) );
        //add_filter( 'hidden_columns', array( $this, 'screen_columns_hidden' ), 10, 3 );
        add_filter( 'set-screen-option', array($this, 'set_screen_option'), 10, 3);
      }
    }
  }

  /**
   * Adds Settings or Finish Set-Up tab links on top of the Encoder's jobs listing page.
   *
   * @param $links array  An array of existing tab links.
   * @param $file  string Filename in which we're calling this action.
   *
   * @return array Returns an array with new tab links added to it.
   */
  function admin_plugin_action_links($links, $file) {
    if ( stripos( $file, 'fv-player-' . $this->encoder_id . '.php') !== false ) {
      if ( $this->is_configured() ) {
        $extra_link = '<a href="'.admin_url('admin.php?page=' . $this->encoder_wp_url_slug . '&panel=settings').'">Settings</a>';
      } else {
        $extra_link = '<a href="'.admin_url('admin.php?page=' . $this->encoder_wp_url_slug).'">Finish Set-Up</a>';
      }
      array_unshift($links, $extra_link);
    }
    return $links;
  }

  /**
   * Ajax handler for deleting completed and errorred-out jobs
   *
   * @param int $_POST['id_row']      ID of the job row
   *
   * @return JSON                  Status message
   */
  function ajax_fv_player_delete_job() {
    global $wpdb;

    $id = absint( $_POST['id_row'] );

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv-player-encoder-delete-job-' . $id ) ) {
      wp_send_json( array('error' => 'Bad nonce') );
    }

    if ( $wpdb->query( $wpdb->prepare("DELETE FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE id = %d  ", $id) ) ) {
      wp_send_json( array('success' => 'Job deleted successfully') );
    } else {
      wp_send_json( array('error' => 'Error deleting row') );
    }
  }

  /**
   * Ajax handler for creation of new job.
   *
   * @param string $_POST['source']      Source file URL
   * @param string $_POST['target']      Target folder on the target CDN
   * @param string $_POST['encryption']  Should it encrypt the video?
   *
   * @return JSON                        New job table row HTML in html property and also error property if there is any error
   */
  function ajax_fv_player_job_submit() {
    global $wpdb;

    // TODO: update JS to generate correct nonce ID (it was coconut_expert_nonce before)
    if(
        defined('DOING_AJAX') &&
        ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_player_' . $this->encoder_id ) )
    ) {
      wp_send_json( array('error' => 'Bad nonce') );
    }

    $source = sanitize_url( $_POST['source'] );
    $target = sanitize_text_field( $_POST['target'] );

    // if the extending Encoder supports encryption, add it here
    if ( isset($_POST['encryption']) ) {
      $encryption = sanitize_text_field( $_POST['encryption'] );
    }

    // if the extending Encoder supports a trailer, add it here
    if ( isset( $_POST['trailer'] ) ) {
      $trailer = sanitize_text_field( $_POST['trailer'] );
    }

    $target = $this->util__sanitize_target($target);

    // if we get a proper category link, we prepend its Name (and parent Names) to the target
    if ( !empty($_POST['category_id']) ) {
      $this->create_encoding_categories();

      if ( $folder = $this->util__category_id_to_folder( absint( $_POST['category_id'] ) ) ) {
        $target = $folder.'/'.$target;
      }
    }

    if( isset( $_POST['id_video'] ) ) {
      $id_video = intval( $_POST['id_video'] );
    }

    // check for a valid source URL
    if ( empty( $_POST['no_source_verify'] ) && !preg_match('~^(https?|s?ftp)://~', $source) ) {
      $error = 'Your source location is not a proper URL!';
      if ( defined('DOING_AJAX') ) {
        wp_send_json( array('error' => $error) );
      } else {
        return $error;
      }
    }

    // if the same target name already exists and we've not asked to rename it automatically,
    // return an error
    if ( empty( $_POST['rename_if_exists'] ) && empty( $_POST['ignore_duplicates'] ) ) {
      if ( $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE target = %s AND status != 'error' AND type = %s", $target, $this->encoder_id ) ) ) {
        $error = 'Target stream already exists, please try with different target name.';
        if ( defined( 'DOING_AJAX' ) ) {
          wp_send_json( array( 'error' => $error ) );
        } else {
          return $error;
        }
      }
    } else if ( empty( $_POST['ignore_duplicates'] ) ) {
      $original_target = $target;
      $rename_suffix_counter = 1;
      while ( $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE target = %s AND status != 'error' AND type = %s", $target, $this->encoder_id ) ) ) {
        $rename_suffix_counter++;
        $target = $original_target . '_' . $rename_suffix_counter;
      }
    }

    // verify the currently used endpoint supported by the extending Encoder,
    // such as (S)FTP or S3 credentials
    $endpoint_verify = $this->verify_active_endpoint( $target );
    if ( $endpoint_verify !== true ) {
      return $endpoint_verify;
    }

    // prepare an encoding job to submit to the extending Encoder
    $job =  array(
      'source' => $source,
      'target' => $target,
    );

    // encryption support
    if ( isset( $encryption ) ) {
        $job['encryption'] = $encryption;
    }

    // support for trailers
    if ( isset( $trailer ) ) {
      $job['trailer'] = $trailer;
    }

    if( isset( $id_video ) ) {
      $job['id_video'] = $id_video;
    }

    // create a new job
    $id = $this->job_create( $job );
    $show = array( $id );

    // submit the job to the Encoder service
    $result = $this->job_submit($id);

    do_action( 'fv_player_encoder_job_submit', $id, $job, $result );

    $response = array( 'id' => $id, 'result' => $result );

    if ( ! empty( $_POST['create_player'] ) ) {
      global $FV_Player_Db;
      $player_id = $FV_Player_Db->import_player_data( false, false, array(
        'videos' => array(
          array(
            'src' => 'coconut_processing_' . $id,
            'meta' => array(
              array(
                'meta_key'   => 'encoding_job_id',
                'meta_value' => $id,
              ),
            )
          )
        )
      ) );
      $response['player_id'] = $player_id;
    }

    if( defined('DOING_AJAX') ) {
      if  ( $this->use_wp_list_table && function_exists( 'convert_to_screen' ) ) {
        $this->include_listing_lib();

        ob_start();
        $jobs_table = new FV_Player_Encoder_List_Table( array( 'encoder_id' => $this->encoder_id, 'table_name' => $this->table_name ) );
        $jobs_table->prepare_items($show);
        $jobs_table->display();
        $html = ob_get_clean();

        $response['html'] = $html;
      }

      wp_send_json( $response );

    } else {
      return $id;
    }
  }

  /**
   * Includes the browser PHP backend file for the extending encoder class.
   */
  function init_browser() {
    // it should not show when picking the media file in dashboard
    //if( empty( $_GET['page'] ) || strcmp( $_GET['page'], $this->encoder_wp_url_slug ) != 0 ) {
    if( !empty( $this->browser_inc_file ) ) {
      include_once( $this->browser_inc_file );
    }
    //}
  }

  /**
   * Returns an array with all updated jobs' HTML that can be used on admin pages
   * to refresh jobs table data during the WP heartbeat.
   *
   * @param $ids array An array of all job IDs to get HTML output for.
   *
   * @return array Returns an array with all updated jobs' HTML that can be used on admin pages
   *               to refresh jobs table data during the WP heartbeat.
   */
  function get_updated_rows( $ids ) {
    $rows = array();

    if( count($ids) > 0 ) {
      $this->include_listing_lib();
      // get html for processed rows
      foreach($ids as $id ) {
        ob_start();
        $jobs_table = new FV_Player_Encoder_List_Table( array( 'encoder_id' => $this->encoder_id, 'table_name' => $this->table_name ) );
        $jobs_table->prepare_items( array($id) );
        $jobs_table->display();
        $html = ob_get_clean();
        preg_match( '/<tbody[\s\S]*?(<tr>[\s\S]*?<\/tr>)[\s\S]*?<\/tbody>/', $html, $matches ); // match row

        $rows[$id] = $matches[1];
      }
    }

    return( $rows );
  }

  /**
   * Checks pending encoder jobs for status change and update the src
   * of this file everywhere it's used in players.
   *
   * @param false $all If true, all records are retrieved, otherwise only records for the last 30 seconds are selected.
   *
   * @return array Returns an array of all IDs that were in processing status and checked for status change.
   */
  function jobs_check( $all = false ) {
    global $wpdb;

    $ids = array();
    if( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $this->table_name ) ) != $this->table_name ) {
      return $ids;
    }

    if ( $all ) {
      $pending_jobs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE type = %s AND status = 'processing'", $this->encoder_id ) );

    } else {
      $pending_jobs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE type = %s AND status = 'processing' AND date_checked < DATE_SUB( UTC_TIMESTAMP(), INTERVAL 30 SECOND )", $this->encoder_id ) );
    }

    foreach( $pending_jobs AS $pending_job ) {
      $ids[] = $pending_job->id;

      $check_result = $this->job_check( $pending_job );

      // if this job was completed, update SRC of all players where its temporary placeholder is used
      if ( $check_result['status'] == 'completed' ) {
        $this->update_temporary_job_src( $check_result, $pending_job->id );
      }
    }

    return $ids;
  }

  /**
   * Updates src of all videos where the temporary "encoder_processing_" placeholder was used
   * for the video given either by the $check_result parameter or the one currently displayed on page.
   *
   * @param array $check_result If set, this will be a previous job check result from this encoder.
   * @param int $job_id         If set, this will be a previous job ID for which the $check_result check was made.
   *
   * @return array|null Returns job check value which will be either the same as the given $check_result
   *                    or a new, real $check_result after a job check.
   */
  private function update_temporary_job_src( $check_result = null, $job_id = null ) {
    global $FV_Player_Db, $fv_fp;

    if ( $check_result ) {
      $check = $check_result;
    } else if ( $fv_fp->current_video() ) {
      if ( !$job_id ) {
        $check = $this->job_check( (int) substr( $fv_fp->current_video()->getSrc(), strlen( $this->encoder_id . '_processing_' ) ) );
      } else {
        $check = $this->job_check( (int) $job_id );
      }

    } else if ( $job_id ) {
      $check = $this->job_check( absint( $job_id ) );

    } else {
      user_error('Could not retrieve JOB check for encoder ' . $this->encoder_name . ', job ID: ' . $job_id . ', defaulted back to input value: ' . print_r( $check_result, true ), E_USER_WARNING );
      return $check_result;
    }

    $temporary_src = $this->encoder_id . '_processing_' . (int) $job_id;

    if ( strcmp( $check['status'], 'completed' ) == 0 && ! empty( $check['output'] ) ) {
      $job_output = $check['output'];

      // if we don't have current_video then we're on the players listing page, so we need to find and update
      // all players where our temporary "encoder_processing_" placeholder is used
      if ( !$fv_fp->current_video() ) {
        $videos = $FV_Player_Db->query_videos( array(
          'fields_to_search' =>  array('src'),
          'search_string' => $temporary_src,
          'like' => false,
          'and_or' => 'OR'
          )
        );

        if(!empty($videos)) {
          foreach ( $videos as $video ) {
            $res = $this->update_temporary_job_video( $video, $temporary_src, $job_output );

            if ( $res ) {
              // purge HTML caches for all posts where players containing this video are present
              $players = $fv_fp->get_players_by_video_ids( $video->getId() );
              foreach ( $players as $player ) {
                if ( $posts = $player->getMetaValue( 'post_id' ) ) {
                  foreach ( $posts as $post_id ) {
                    wp_update_post( array( 'ID' => $post_id ) );
                  }
                }
              }
            }
          }
        }

      // If not, update the video with the job output if $fv_fp->current_video()->getSrc() ends with $temporary_src
      } else {
        $res = $this->update_temporary_job_video( $fv_fp->current_video(), $temporary_src, $job_output );

        if ( $res ) {
          // purge HTML caches for all posts where this player is present 
          if ( $posts = $fv_fp->current_player()->getMetaValue( 'post_id' ) ) {
            foreach ( $posts as $post_id ) {
              wp_update_post( array( 'ID' => $post_id ) );
            }
          }
        }
      }
    }

    return $check;
  }

  function update_temporary_job_video( $video, $temporary_src, $job_output ) {

    /**
     * Ensure $video->getSrc() ends with $temporary_src
     * This ensures we match coconut_processing_1 in http://coconut_processing_1,
     * but not in http://coconut_processing_10
     */
    if ( substr( $video->getSrc(), -strlen( $temporary_src ) ) !== $temporary_src ) {
      return false;
    }

    // video processed, replace its SRC
    if ( ! empty( $job_output->src[0] ) ) {
      $video->set( 'src', $job_output->src[0] );
    }

    // also replace its thumbnail / splash
    if ( ! empty( $job_output->thumbnail_large ) ) {
      $video->set( 'splash', $job_output->thumbnail_large );

    } else if ( ! empty( $job_output->thumbnail ) ) {
      $video->set( 'splash', $job_output->thumbnail );
    } else if ( ! empty( $job_output->splash ) ) {
      $video->set( 'splash', $job_output->splash );
    }

    if ( ! empty( $job_output->hlskey ) ) {
      $video->updateMetaValue( 'hls_hlskey', $job_output->hlskey );
    }

    // also set its timeline preview, if received
    if ( ! empty( $job_output->timeline_previews ) ) {
      $video->updateMetaValue( 'timeline_previews', $job_output->timeline_previews );
    }

    // save changes for this video
    return $video->save();
  }

  /**
   * Create the job database entry.
   *
   * @param array $args Job configuration
   *   $args = array(
   *     'source'               (string) Source file URL
   *     'target'               (string) Target video folder
   *     'encryption'           (bool) (optional, encoder-features-dependent) Encrypt the HLS stream or not
   *     'trailer'              (bool) (optional, encoder-features-dependent) Should it be a small part of video only
   *
   * @global object $wpdb       WordPress database object
   *
   * @return ID                 Job ID
   */
  public function job_create( $args ) {
    global $wpdb, $fv_fp;

    $args = wp_parse_args( $args, array(
      'encryption' => false,
      'trailer' => false,
      'id_video' => false
    ) );

    $video_ids = explode( ',', strval($args['id_video']) );

    // first we instert the table row with basic data and remember the row ID
    $wpdb->insert(  $this->table_name, array(
      'date_created' => gmdate("Y-m-d H:i:s"),
      'id_video' => $args['id_video'],
      'source' => $args['source'],
      'target' => $args['target'],
      'type' => $this->encoder_id,
      'mime' => $fv_fp->get_mime_type( $args['source'] ),
      'status' => 'created',
      'output' => $this->prepare_job_output_column_value(),
      'args' => '',
      'author' => get_current_user_id(),
      'id_video' => $video_ids[0]
    ), array(
      '%s',
      '%d',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%d',
      '%d'
    ));

    $job_id = $wpdb->insert_id;
    if( !$job_id ) {
      wp_send_json( array('error' => 'Database error') );
      return;
    }

    // we apply extra sanitizaion as some encoders (such as Coconut) use bare text format for their configs
    $source = $this->util__escape_source($args['source']);

    // we apply the URL signatures/tokens
    add_filter( 'fv_player_secure_link_timeout', array( $this, 'job_create_expiration' ) );
    $source = apply_filters( 'fv_flowplayer_video_src', $source, array( 'dynamic' => true ) );

    // once we have the row ID, we generate the configuration
    $conf_array = array(
      'source' => $source,
      'target' => $args['target'],
      'job_id' => $job_id,
      'video_id' => $args['id_video'],
    );

    if ( isset( $args['encryption'] ) ) {
      $conf_array['encryption'] = $args['encryption'];
    }

    if ( isset( $args['trailer'] ) ) {
      $conf_array['trailer'] = $args['trailer'];
    }

    $conf = $this->get_conf( $conf_array );

    // store the final configuration
    $wpdb->update( $this->table_name, array(
      'args' => wp_json_encode( $conf )
    ), array(
      'id' => $job_id
    ), array(
      '%s'
    ), array(
      '%d'
    ) );

    return $job_id;
  }

  /**
   * Adds filtering options for the jobs listing page.
   */
  function screen_options() {
    $screen = get_current_screen();
    if ( !is_object($screen) || $screen->id != $this->admin_page ) return;

    $args = array(
      'label' => __('Jobs per page', 'pippin'),
      'default' => 25,
      'option' => 'fv_player_' . $this->encoder_id . '_per_page'
    );

    add_screen_option( 'per_page', $args );
  }

  /**
   * Sets the per-page option value for job listing page filter.
   *
   * @param $status string Unused.
   * @param $option string Name of the option we're checking for.
   * @param $value string  Value of the option we're checking for.
   *
   * @return string|void
   */
  function set_screen_option($status, $option, $value) {
    if ( 'fv_player_' . $this->encoder_id . '_per_page' == $option ) return $value;
  }

  /**
   * Adds the title and tabs for the jobs listing encoder page in Admin.
   */
  function tools_panel() {
    if ( !$this->is_configured() ) {
      $this->tools_panel_settings();
      return;
    }

    ?>
    <div class="wrap">
      <h1 class="wp-heading-inline">FV Player <?php echo esc_html( $this->encoder_name ); ?> Video Encoding Jobs</h1>
      <h2 class="nav-tab-wrapper">
        <a href="<?php echo add_query_arg( 'page', $this->encoder_wp_url_slug, admin_url('admin.php') ) ?>" class="nav-tab<?php if( $this->tools_panel_is('jobs') ) echo ' nav-tab-active'; ?>">Jobs</a>
        <a href="<?php echo add_query_arg( array('page' => $this->encoder_wp_url_slug ,'panel' => 'settings'), admin_url('admin.php') ) ?>" class="nav-tab<?php if( $this->tools_panel_is('settings') ) echo ' nav-tab-active'; ?>">Settings</a>
      </h2>
      <?php
      if( $this->tools_panel_is('settings') ) {
        $this->tools_panel_settings();
      } else {
        $this->tools_panel_jobs();
      }
      ?>
    </div>
    <?php
  }

  /**
   * Checks what kind of tab we have active in the jobs listing page in Admin.
   *
   * @param boolean $kind The kind of tab we're comparing currently displayed tab with.
   *
   * @return bool Returns true if the tab we're looking for is active, false otherwise.
   */
  function tools_panel_is( $kind = false ) {
    $panel = !empty( $_GET['panel'] ) ? sanitize_key( $_GET['panel'] ) : 'jobs';
    return strcmp( $panel, $kind ) == 0;
  }

  /**
   * Includes JS for the extending encoder class.
   *
   * @param $page Auto-filled by WP by the page slug at which we're looking.
   */
  public function admin_enqueue_scripts( $page ) {
    if( $page == 'post.php' || $page == 'post-new.php' || $page == 'toplevel_page_fv_player' || $page == 'settings_page_fvplayer' || $page == 'fv-player_page_' . $this->encoder_wp_url_slug ) {
      $file = $this->locate_script('admin.js');
      if( $file ) {
        $handle = 'fv_player_' . $this->encoder_id . '_admin';
        wp_enqueue_script( $handle, plugins_url( $file, $this->getFILE() ), array('jquery'), filemtime( dirname( $this->getFILE() ) . $file), true );
        wp_localize_script( $handle, $this->encoder_id . '_pending_jobs', $this->jobs_check(true) );
      }
    }
  }

  /**
   * Adds a hidden encoding job ID field into the editor.
   */
  function shortcode_editor_item() {
    // TODO: The field has to start with fv_wp_flowplayer_field_ which is not easy to keep in mind!
    ?>
    <input type="hidden" id="fv_wp_flowplayer_field_encoding_job_id" name="fv_wp_flowplayer_field_encoding_job_id" />
    <?php
  }

  /**
   * Converts 'Tom & Jerry - "The Best" show' to Tom-Jerry-The-Best-show to
   * ensure safe directory names
   *
   * @param string $filename        The filename of the source video
   *
   * @return string                 Sanitized file URL - name of the resulting folder for video
   */
  function util__escape_filename( $filename ) {
    // allow only safe characters
    $filename = preg_replace('/[^A-Za-z0-9\-]/m', '-', $filename);
    $filename = preg_replace('/-{2,}/m', '-', $filename);
    // remove - at start or beginning
    $filename = preg_replace('/^-|-$/m', '', $filename);
    return $filename;
  }

  /**
   * Without this Coconut wouldn't accept file URLs with symbols like ' ' or
   * & in it
   *
   * @param string $url             Source video file URL
   *
   * @return string                 Sanitized file URL
   */
  function util__escape_source( $url ) {
    $url_components = wp_parse_url($url);
    $old_path = $url_components['path'];

    $url_components['path'] = str_replace( array('%20','+'), ' ', $url_components['path']);

    $url_components['path'] = rawurlencode($url_components['path']);
    $url_components['path'] = str_replace('%2F', '/', $url_components['path']);
    $url_components['path'] = str_replace('%2B', '+', $url_components['path']);

    $url = str_replace($old_path, $url_components['path'], $url);
    return $url;
  }

  /**
   * Convert fv_player_encoding_category ID to a nice folder name.
   * If you have:
   * - Documentaries
   * -- Nature
   * --- Wildlife & Adventure
   *
   * you get: Documentaries/Nature/Wildlife-Adventure
   *
   * @param string $url             Source video file URL
   *
   * @return string                 Sanitized file URL
   */
  function util__category_id_to_folder( $category_id ) {
    $folder = false;

    $category = get_term($category_id);
    if( !is_wp_error($category) ) {
      $hierarchy = array( $this->util__escape_filename($category->name) );
      $ancestors = get_ancestors( $category->term_id, 'fv_player_encoding_category', 'taxonomy' );
      foreach( (array)$ancestors as $ancestor ) {
        $ancestor_term = get_term($ancestor, 'fv_player_encoding_category');
        $hierarchy[] = $this->util__escape_filename($ancestor_term->name);
      }
      $hierarchy = array_reverse($hierarchy);
      $folder = implode('/', $hierarchy);
    }

    return $folder;
  }

  /**
   * Get sanitized file path. For example  https://cdn.site.com/lessons/music/composing/lesson-1.mp4 gives you /lessons/music/composing/lesson-1
   *
   * @param $string Filename or URL
   * @return string
   */
  function util__sanitize_target( $target ) {

    $target = trim($target);

    // take path only if it's full URL
    $parsed = wp_parse_url($target);

    if( !empty($parsed['scheme']) ) $target = str_replace($parsed['scheme'].'://', '', $parsed);
    if( !empty($parsed['hostname']) ) $target = str_replace($parsed['hostname'], '', $parsed);

    $target = preg_replace( '~/$~', '', $target ); // remove trailing slash

    // sanitize filename
    $target = explode('/', $target);

    // deal with %20 encoding of spaces
    $target = array_map( 'urldecode', $target );

    $filename = $target[ count($target) - 1 ];

    // remove file extension
    if( strrpos( $filename, ".") ) {
      $filename = substr( $filename, 0, strrpos( $filename, "."));
    }

    $filename = $this->util__escape_filename($filename);

    // we're done
    $target[ count($target) - 1 ] = $filename;
    $target = join('/', $target);

    return $target;
  }

  /**
   * Sends an e-mail about changes in the encoding job.
   *
   * @param $id int    int    ID of the job to send this e-mail about.
   * @param $author_id int    ID of the author of the encoding job.
   * @param $status    string Status of the processed encoding job.
   * @param $target    string The actual target for the processed encoding job.
   * @param $result    string Text representation of the result, used to send any error messages along with the e-mail.
   */
  function send_email( $id, $author_id, $status, $target, $result ) {
    $user = get_userdata( $author_id );
    $to = $user->user_email;
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    $subject = "[". get_bloginfo( 'name' ) . "] FV Player {$this->encoder_name}: Job #" . $id . " " . $target . " " . $status ;

    $body = "Hello " . $user->display_name . ",\r\n";
    $body .= "Your encoding job #" . $id . " " . $target . " has ";

    if( $status == 'completed' ) {
      $body .= "successfully finished.\r\n";
    } else {
      $body .= "run into some problems.\r\n";
      $body .= $result."\r\n";
    }

    if ( user_can( $author_id, 'manage_options' ) ) {
      $body .= "\r\nManage video encoding jobs <a href='". admin_url( 'admin.php?page=' . $this->encoder_wp_url_slug ) ."'>here</a>";
    }

    wp_mail( $to, $subject, $body, $headers );
  }

  /**
   * Updates DB table definition for the extending plugin.
   * Used when a version change of the extending plugin is detected,
   * as well as displaying jobs listing page.
   */
  public function plugin_update_database() {
    global $wpdb;

    $sql = "CREATE TABLE ". $this->table_name ." (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      id_video bigint(20) unsigned NOT NULL,
      job_id varchar(45) NOT NULL,
      date_created datetime NOT NULL,
      date_checked datetime NOT NULL,
      source varchar(1024) NOT NULL,
      target varchar(1024) NOT NULL,
      type varchar(64) NOT NULL,
      status varchar(64) NOT NULL,
      progress varchar(64),
      error varchar(1024),
      mime varchar(64),
      args TEXT,
      result TEXT,
      output TEXT,
      video_data TEXT,
      author bigint(20) unsigned NOT NULL default '0',
      fv_player_encoding_category_id bigint(20) unsigned DEFAULT NULL,
      PRIMARY KEY  (id),
      KEY source (source(191)),
      KEY type (type),
      KEY status (status),
      KEY job_id (job_id(15))
    )" . $wpdb->get_charset_collate() . ";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

  /**
   * Displays general notices on top in Admin pages.
   */
  abstract function admin_notices();

  /**
   * Returns an updated $conf variable with settings for the extending Encoder class.
   *
   * @param $conf Pre-populated configuration array into which the extending Encoder's class configuration should go.
   *
   * @return array Returns an updated $conf variable with settings for the extending Encoder class.
   */
  abstract function default_settings( $conf );

  /**
   * Verifies the currently used endpoint supported by the extending Encoder, such as (S)FTP or S3 credentials
   * and either directly outputs a JSON-formatted error (for AJAX purposes) or returns the error to be processed further.
   *
   * @return mixed Returns TRUE if the current endpoint is set up properly, an error object/array otherwise.
   *               If we're running an AJAX request, this method must return a valid JSON-formatted error for that request
   *               by utilizing the wp_send_json() method in this format: wp_send_json( array('error' => $error) );
   */
  protected abstract function verify_active_endpoint( $target );

  /*
  * Creates default Encoder's configuration.
  */
  abstract function get_conf( $args );

  /**
   * Determines whether this Encoder has been properly configured.
   */
  abstract function is_configured();

  /**
   * Prepares and returns data to be inserted into the "output" column of this encoder's DB table.
   */
  abstract protected function prepare_job_output_column_value();

  /**
   * Retrieves new encoding job expiration time, used in URL signatures / tokens.
   *
   * @param $ttl An optional TTL parameter.
   *
   * @return int Returns the duration in seconds for which this job is valid.
   */
  abstract public function job_create_expiration( $ttl );

  /**
   * Update job status
   *
   * @param object|int $pending_job Table row from encoder's table or its job ID
   *
   * @global object $wpdb       WordPress database object
   * @global object $fv_fp      FV Player
   *
   * @return array
   * array(
   *  'result' object Job info from the Encoder
   *  'status' string Valid values are: "processing", "completed", "error"
   *  'output' object URLs for all processed resources (such as video qualities, thumbnails etc.)
   * )
   */
  abstract protected function job_check( $pending_job );

  /**
   * Submits the job to the Encoder service and stores the result in a table.
   *
   * @param int $job_id     Job ID

   * @global object $wpdb   WordPress database object
   * @global object $fv_fp  FV Player instance to load options with
   *
   * @return bool           Result
   */
  abstract function job_submit( $id );

  /**
   * Displays the jobs listing page contents.
   */
  abstract function tools_panel_jobs();

  /**
   * Displays the Encoder's settings page contents.
   */
  abstract function tools_panel_settings();

  /**
   * Must return __FILE__ from the extending class.
   * Used to determine plugin path for registering JS and CSS.
   */
  abstract function getFILE();

  /**
   * Send out an e-mail notification of an encoding job change.
   * To be used when a WebHook is fired from the Encoding service.
   */
  abstract function email_notification();

}
