<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

require_once( dirname(__FILE__).'/class.fv-player-bunny_stream-wizard.php' );

class FV_Player_Bunny_Stream extends FV_Player_Video_Encoder {

  private static $instance = null;

  public $plugin_api;

  /**
   * gets the instance via lazy initialization (created on first usage)
   */
  public static function getInstance( $encoder_id, $encoder_name, $encoder_wp_url_slug, $browser_inc_file ) {
    if ( self::$instance === null ) {
      self::$instance = new static( $encoder_id, $encoder_name, $encoder_wp_url_slug, $browser_inc_file );
    }

    return self::$instance;
  }

  /**
   * prevent the instance from being cloned (which would create a second instance of it)
   */
  private function __clone() {}

  /**
   * prevent from being unserialized (which would create a second instance of it)
   */
  public function __wakeup() {
    throw new Exception("Cannot unserialize singleton");
  }

  protected function __construct( $encoder_id, $encoder_name, $encoder_wp_url_slug, $browser_inc_file ) {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    $this->version = '7.5.15.727';

    parent::__construct( $encoder_id, $encoder_name, $encoder_wp_url_slug, $browser_inc_file );
  }
  
  public function admin_notices() {}

  /**
   * Bunny Stream doesn't use configurations, so we'll just return the same config that we received.
   *
   * @param $conf Pre-populated configuration array into which the extending Encoder's class configuration should go.
   *
   * @return array Simply returns the same config that we received, since Bunny Stream doesn't use configurations.
   */
  public function default_settings( $conf ) {
    return $conf;
  }

  /**
   * Verifies the currently used endpoint supported by the extending Encoder, such as (S)FTP or S3 credentials
   * and either directly outputs a JSON-formatted error (for AJAX purposes) or returns the error to be processed further.
   *
   * @return mixed Returns TRUE if the current endpoint is set up properly, an error object/array otherwise.
   *               If we're running an AJAX request, this method must return a valid JSON-formatted error for that request
   *               by utilizing the wp_send_json() method in this format: wp_send_json( array('error' => $error) );
   */
  protected function verify_active_endpoint( $target ) {
    // if we have API key, library ID & CDN hostname, we're good and any errors (such as edited & invalid / expired API key) would show up
    // when we try to contact the API
    return $this->is_configured();
  }

  /*
   * Returns the CDN URL - same as get_cdn_url() for now, until we determine whether we need something else or if we can merge these 2.
   * TODO: determine whether we need 2 CDN functions or if we can merge this with get_cdn_url()
   */
  function get_cdn_conf( /*$no_credentials = false*/ ) {
    global $fv_fp;

    return trailingslashit( $fv_fp->_get_option( array('bunny_stream', 'cdn_hostname') ) );
  }

  /**
   * Gives you the HTTP URL of the target location
   * TODO: determine whether we need 2 CDN functions or if we can merge this with get_cdn_conf()
   *
   * @return string
   */
  function get_cdn_url() {
    global $fv_fp;

    return trailingslashit( $fv_fp->_get_option( array('bunny_stream', 'cdn_hostname') ) );
  }

  /**
   * Bunny Stream uses no configuration, it will encode videos according to each stream's settings.
   * This method simply therefore returns exactly what it was given.
   *
   * @param $args array Config array from the base encoder class.
   *
   * @return array      Returns the same $args array that it was given, since Bunny Streams use no configurations.
   */
  function get_conf( $args ) {
    return $args;
  }

  /**
   * Determines whether this Encoder has been properly configured.
   */
  function is_configured() {
    global $fv_fp;
    return !empty($fv_fp) && method_exists($fv_fp,'_get_option') && $fv_fp->_get_option( array('bunny_stream','api_key') ) && $fv_fp->_get_option( array('bunny_stream','lib_id') ) && $fv_fp->_get_option( array('bunny_stream','cdn_hostname') );
  }

  /**
   * Normalizes Bunny Stream statuses for use in our DB.
   *
   * @param $status string The original status from Bunny Stream.
   *
   * @return string Returns the normalized status for use in our DB.
   */
  function get_bunny_stream_status( $status ) {
    if( $status == 0 || $status == 1 || $status == 2 ) {
      // 0 = queued, 1 = processing, 2 = encoding ... but these 3 mean the same thing for our purposes
      $status = 'processing';
    } else if( $status == 3 || $status == 4 ) {
      // 4 = resolution finished (which was previously changed to 3 afterwards but is no longer the case, so 4 is as good as done)
      $status = 'completed';
    } else {
      $status = 'error';
    }

    return $status;
  }

  /**
   * Prepares and returns data to be inserted into the "output" column of this encoder's DB table.
   */
  protected function prepare_job_output_column_value() {
      return wp_json_encode( array(
        'base_url' => trailingslashit( $this->get_cdn_conf() ),
        'replacing_url' => trailingslashit( $this->get_cdn_url() )
      ) );
  }

  public function job_create_expiration( $ttl ) {
    return 4 * 3600;
  }

  /**
   * Update job status
   *
   * @param object|int $pending_job Table row from wp_fv_player_encoding_jobs or its job ID
   *
   * @global object $wpdb       WordPress database object
   * @global object $fv_fp      FV Player
   *
   * @return array
   * array(
   *  'result' object Job info from Bunny.net
   *  'status' string You get either "processing", "completed" or "error"
   *  'output' object URLs for resources - for Bunny Streams, we don't change CDN URLs, so both URLs here will be the same
   * )
   */
  protected function job_check( $pending_job ) {
    global $wpdb, $fv_fp;

    if( is_numeric($pending_job) ) {
      $pending_job = $wpdb->get_row( $wpdb->prepare( "SELECT id, progress, result, output FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE id = %d", $pending_job ) );
    }

    if( !$pending_job ) {
      return;
    }

    $output = json_decode( $pending_job->output );
    $job_details = json_decode( $pending_job->result );

    if ( ! isset( $job_details->guid ) ) {
      $job_id = $pending_job->job_id;
    } else {
      $job_id = $job_details->guid;
    }

    require_once( dirname( __FILE__ ) . '/class.fv-player-bunny_stream-api.php' );
    $api = new FV_Player_Bunny_Stream_API();
    $job = $api->api_call( 'https://video.bunnycdn.com/library/' . $fv_fp->_get_option( array(
        'bunny_stream',
        'lib_id'
      ) ) . '/videos/' . $job_id );

    if ( ! is_wp_error( $job ) ) {
      $status   = $this->get_bunny_stream_status( $job->status );
      $progress = $job->encodeProgress;

      // check whether this job hasn't failed upload
      if ( $job->status == 6 ) {
        // job failed upload
        $wpdb->update( $this->table_name, array(
          'date_checked' => gmdate( "Y-m-d H:i:s" ),
          'status'       => 'error',
          'error'        => 'Upload failed',
        ), array(
          'id' => $pending_job->id
        ), array(
          '%s'
        ), array(
            '%d'
          )
        );
      } else {
        $wpdb->update( $this->table_name, array(
          'result'       => wp_json_encode( $job ),
          'status'       => $status,
          'date_checked' => gmdate( "Y-m-d H:i:s" ),
          'progress'     => $progress . '%',
        ), array(
          'id' => $pending_job->id
        ), array(
          '%s',
          '%s',
          '%s',
          '%s'
        ), array(
          '%d'
        ) );
      }
    } else {
      $wpdb->update( $this->table_name, array(
        'date_checked' => gmdate( "Y-m-d H:i:s" ),
        'error'        => $job->get_error_message(),
      ), array(
        'id' => $pending_job->id
      ), array(
        '%s'
      ), array(
          '%d'
        )
      );

      $status = 0;
    }

    $ret = array(
      'result' => $job,
      'status' => $status,
      'output' => $output,
    );

    if ( isset($progress) ) {
        $ret['progress'] = $progress;
    } else if ( isset( $pending_job ) && !empty( $pending_job->progress ) ) {
        $ret['progress'] = $pending_job->progress;
    }

    return $ret;
  }

  /**
    * Submit the job to Bunny Stream and store the result in table
    *
    * @param int $job_id     Job ID

    * @global object $wpdb   WordPress database object
    * @global object $fv_fp  FV Player instance to load options with
    *
    * @return bool           Result
    */
  function job_submit( $id ) {
    global $fv_fp, $wpdb;

    if(
        defined('DOING_AJAX') &&
        ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fv_player_bunny_stream' ) )
    ) {
      wp_send_json( array('error' => 'Bad nonce, please reload the page and try again.' ) );
    }

    $target_name = $wpdb->get_var( $wpdb->prepare( "SELECT target FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE id = %d", $id ) );

    $body = array(
      'title' => $target_name,
    );

    require_once( dirname( __FILE__ ) . '/class.fv-player-bunny_stream-api.php');

    $api = new FV_Player_Bunny_Stream_API();

    // check if we have collection
    if( isset( $_POST['collection_name'] ) ) {
      $collection_name = wp_strip_all_tags( sanitize_text_field( $_POST['collection_name'] ) );
      $collection_name = str_replace('Home/', '', $collection_name);
      $collection_name = rtrim($collection_name, '/');

      $guid = $api->get_collection_guid_by_name($collection_name);

      if($guid) $body['collectionId'] = $guid;
    }

    $job = $api->api_call(
            'https://video.bunnycdn.com/library/' . $fv_fp->_get_option( array('bunny_stream','lib_id') ) . '/videos',
            $body,
            'POST'
    );

    $job_id = 0;
    $progress = '0%';

    if ( !is_wp_error( $job ) ) {
      $job_id = $job->guid;
      $status = 'processing';
      $result = $job;
    } else {
      $result = array( 'exception' => $job->get_error_message() );
      $status = 'error';
      $progress = 'failed';
    }

    $wpdb->update( $this->table_name, array(
      'job_id' => $job_id,
      'result' => $result,
      'status' => $status,
      'progress' => $progress,
    ), array(
      'id' => $id // where id
    ), array(
      '%s', // job_id
      '%s', // result
      '%s', // status
      '%s'  // progress
    ), array(
      '%d'  // id
    ) );

    return array(
      'status' => $status,
      'result' => $result,
    );
  }

  /**
   * Displays the jobs listing page contents.
   */
  function tools_panel_jobs() {
    do_action( 'fv_player_video_encoder_include_listing_lib' );

    $this->plugin_update_database();

    $this->jobs_check();

    $user = get_current_user_id();
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($user, $screen_option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
      $per_page = $screen->get_option( 'per_page', 'default' );
    }

    do_action( 'fv_player_video_encoder_include_listing_lib' );

    $table = new FV_Player_Encoder_List_Table( array( 'encoder_id' => 'bunny_stream', 'table_name' => $this->table_name, 'per_page' => $per_page ) );
    $table->prepare_items();
    ?>
      <style>
          .wrap .fv-player-bunny_stream-add, .wrap .fv-player-bunny_stream-add:active {
              top: 24px;
          }
      </style>
      <a href="#" class="page-title-action fv-player-bunny_stream-add" data-add_new="1">Add New</a>

      <div id="fv_player_players_table">
          <form id="fv-player-filter" method="get" action="<?php echo admin_url( 'admin.php?page=fv_player' ); ?>">
              <input type="hidden" name="page" value="fv_player" />

              <?php $table->views() ?>

              <?php $table->advanced_filters(); ?>

              <?php $table->display() ?>
          </form>
      </div>

  <?php
  }

  /**
   * Displays the Encoder's settings page contents.
   */
  function tools_panel_settings() {

    if( isset($_POST['fv_player_bunny_stream_settings_nonce']) && !empty($_POST['bunny_stream']) ) {

      if( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fv_player_bunny_stream_settings_nonce'] ) ),'fv_player_bunny_stream_settings_nonce') ) {
        global $fv_fp;
        if( method_exists($fv_fp,'_set_conf') ) {
          if( empty($fv_fp->conf['bunny_stream']) ) {
            $fv_fp->conf['bunny_stream'] = array();
          }

          foreach( $_POST['bunny_stream'] AS $k => $v ) {
            if(strcmp($k,'video_token') === 0 && empty($_POST['bunny_stream']['api_access_key'])) { // video token enabled but no api_access_key provided
              if(isset($fv_fp->conf['bunny_stream'][$k]) && $v != $fv_fp->conf['bunny_stream'][$k] ) { // and setting changed
                // then unset option
                unset($_POST['bunny_stream'][$k]);
                unset($fv_fp->conf['bunny_stream'][$k]);

                echo "<div class='error'><p>No API Access Key provided to enable or disable Token Authentication.</p></div>";

                continue;
              }
            }

            if(in_array($k, array('api_access_key'))) continue; // do not store

            $fv_fp->conf['bunny_stream'][$k] = trim($v);
          }

          $fv_fp->_set_conf( $fv_fp->conf );

          do_action( 'fv_player_bunny_stream_settings_saved', $fv_fp->conf['bunny_stream'] );

          echo "<div class='updated'><p>Settings saved</p></div>";

        }
      } else {
        echo "<div class='error'><p>Nonce error.</p></div>";
      }
    }

    if( $this->is_configured() ) {
      include( dirname(__FILE__).'/../view/bunny-stream-settings.php' );
    }

    if( $this->is_configured() ) {
      FV_Player_Bunny_Stream_Wizard()->log_show();

    } else {
      FV_Player_Bunny_Stream_Wizard()->view();

    }
  }

  function email_notification() {
    if( isset( $_GET['fv_player_bunny_stream_job_webhook'] ) && !empty($_GET['fv_player_bunny_stream_job_webhook']) ) {
      global $wpdb, $fv_fp;

      // https://docs.bunny.net/docs/stream-webhook
      $body = file_get_contents( "php://input" );
      $webhook = json_decode( $body, true );

      if ( ! $webhook ) {
        return;
      }

      $job_id = $webhook['VideoGuid'];
      $status = $webhook['Status'];
      $status = $this->get_bunny_stream_status( $status );

      if ( strcmp( $status, 'processing' ) == 0 ) {
        // check if we have this job in the DB and if not, add it there
        if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE job_id = %s", $job_id ) ) ) {
          // get info about this job first, so we can set up DB data correctly
          require_once( dirname( __FILE__ ) . '/class.fv-player-bunny_stream-api.php' );
          $api = new FV_Player_Bunny_Stream_API();
          $job = $api->api_call( 'https://video.bunnycdn.com/library/' . $fv_fp->_get_option( array(
              'bunny_stream',
              'lib_id'
            ) ) . '/videos/' . $job_id );

          if ( ! is_wp_error( $job ) ) {
            $wpdb->insert( $this->table_name, array(
              'date_created' => gmdate( "Y-m-d H:i:s" ),
              'source'       => $job->title,
              'target'       => $job->title,
              'type'         => 'bunny_stream',
              'mime'         => $fv_fp->get_mime_type( $job->title ),
              'status'       => $status,
              'output'       => $this->prepare_job_output_column_value(),
              'args'         => '',
              'author'       => get_current_user_id(),
              'job_id'       => $job_id,
              'result'       => $job,
              'progress'     => $job->encodeProgress,
            ), array(
              '%s',
              '%s',
              '%s',
              '%s',
              '%s',
              '%s',
              '%s',
              '%s',
              '%d',
              '%s',
              '%s',
              '%s',
            ) );
          }
        }

        exit;
      }

      $row = $wpdb->get_row(
        $wpdb->prepare( "SELECT `job_id`, `author` , `target`, `result` FROM `{$wpdb->prefix}fv_player_encoding_jobs` WHERE job_id = %s", $job_id )
      );

      $this->send_email( $job_id, $row->author, $status, $row->target, $row->result );

      die();
    }
  }

  /**
   * Must return __FILE__ from the extending class.
   * Used to determine plugin path for registering JS and CSS.
   */
  function getFILE() {
    return __FILE__;
  }

  function fv_player_pro_compatible() {
    if( function_exists('FV_Player_Pro') && version_compare( str_replace( '.beta','', FV_Player_Pro()->version ) , '7.5.19.727', '>=' ) ) {
      return true;
    }

    return false;
  }

}

function FV_Player_Bunny_Stream() {
  return FV_Player_Bunny_Stream::getInstance( 'bunny_stream', 'Bunny Stream', 'fv_player_bunny_stream', dirname(__FILE__) . '/class.fv-player-bunny_stream-browser.php' );
}

// create the instance right away, so the browser and other assets are loaded correctly where they should be
FV_Player_Bunny_Stream();