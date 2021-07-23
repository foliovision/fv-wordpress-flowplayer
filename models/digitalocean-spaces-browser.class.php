<?php

if( !class_exists('FV_Player_DigitalOcean_Spaces_Browser') && class_exists('FV_Player_Media_Browser') ) :

class FV_Player_DigitalOcean_Spaces_Browser extends FV_Player_Media_Browser {

  function init() {
    global $fv_fp;

    if( $this->isSetUpCorrectly() ) {
      global $fv_wp_flowplayer_ver;

      $endpoint = $fv_fp->_get_option(array('digitalocean_spaces','endpoint'));
      if ( strpos( $endpoint, 'http://') === false && strpos( $endpoint, 'https://') === false ) {
        $endpoint = 'https://' . $endpoint;
      }

      wp_enqueue_script( 'fv-player-dos-browser', flowplayer::get_plugin_url().'/js/digitalocean-spaces-browser.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );
      wp_localize_script( 'fv-player-dos-browser', 'fv_player_dos_browser', array(
        'dos_browser_endpoint' => $endpoint,
      ) );
    }
  }

  function register() {
    add_action( $this->ajax_action_name, array($this, 'load_assets') );

    // register extra AJAX functions for file uploads to Vzaar
    add_action( 'wp_ajax_dos_get_signature', array($this, 'get_signature') );
    /*add_action( 'wp_ajax_dos_upload_complete', array($this, 'upload_complete') );
    add_action( 'wp_ajax_dos_check_progress', array($this, 'progress_check') );*/
  }

  // Legacy
  function init_for_gutenberg() {}

  function fv_wp_flowplayer_include_aws_sdk() {
    if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
      require_once( dirname( __FILE__ ) . "/../vendor/autoload.php" );
    }
  }

  function get_signature() {
    global $fv_fp;

    $bucket = $fv_fp->_get_option(array('digitalocean_spaces','space'));
    $secret = $fv_fp->_get_option(array('digitalocean_spaces','secret'));

    $policy = base64_encode(json_encode(array(
      // ISO 8601 - date('c'); generates incompatible date, so better do it manually
      'expiration' => date('Y-m-d\TH:i:s.000\Z', strtotime('+1 day')),
      'conditions' => array(
        array('bucket' => $bucket),
        array('acl' => 'public-read'),
        array('starts-with', '$key', ''),
        array('starts-with', '$Content-Type', ''), // accept all files
        // Plupload internally adds name field, so we need to mention it here
        array('starts-with', '$name', ''),
        // One more field to take into account: Filename - gets silently sent by FileReference.upload() in Flash
        // http://docs.amazonwebservices.com/AmazonS3/latest/dev/HTTPPOSTFlash.html
        array('starts-with', '$Filename', ''),
      )
    )));

    $ret = array(
      'signature' => base64_encode(hash_hmac('sha1', $policy, $secret, true)),
      'access_key_id' => $fv_fp->_get_option(array('digitalocean_spaces','key')),
      'policy' => $policy,
    );

    wp_send_json( $ret );
    wp_die();
  }

  function upload_complete() {
    $this->initVzaarSDK();

    $ret = array();

    if (isset($_POST["guid"])) {
      $multi["guid"] = $_POST["guid"];
      $multi["ingest_recipe_id"] = $_POST["ingest_recipe_id"];
      $multi["title"] = $_POST['original_filename'];

      $video = VzaarApi\Video::create($multi);
      $ret['id'] = $video->id;

      // store uploaded but unprocessed files in the DB,
      // so we can still show them in the browser
      $uploading = get_option('fv_player_vzaar_queue', array() );
      $uploading[] = $video->id;
      update_option( 'fv_player_vzaar_queue', $uploading );
    }

    wp_send_json( $ret );
    wp_die();
  }

  function progress_check() {
    global $FV_Player_Db;

    $this->initVzaarSDK();

    try {
      $lookupFile = VzaarApi\Video::find( $_GET['id'] );
      header( 'Content-Type: application/json' );
      $ret = array(
        'state'      => $lookupFile->state,
        'renditions' => $lookupFile->renditions
      );
    } catch ( VzaarApi\Exceptions\VzaarException $ve ) {
      $ret = array( 'error' => $ve->getMessage() );
    } catch ( VzaarApi\Exceptions\VzaarError $verr ) {
      $ret = array( 'error' => $verr->getMessage() );
    }

    // check the status and create a new player for a finished upload
    if ( empty( $ret['error'] ) ) {
      $generate_player = false;

      if ( $ret['state'] == 'ready' ) {
        $generate_player = true;
      } else if ( $ret['state'] == 'failed' ) {
        $generate_player = false;
      } else {
        $renditionsReady   = 0;
        $somethingFinished = false;

        foreach ( $ret['renditions'] as $rendition ) {
          if ( $rendition->status == 'finished' || $rendition->status == 'skipped' || $rendition->status == 'failed' ) {
            $renditionsReady ++;
          }

          if ( $rendition->status == 'finished' ) {
            $somethingFinished = true;
          }
        }

        // sometimes, all renditions may fail but the file will be stuck in processing,
        // so we'll mark it as failed
        $rCount = count( $ret['renditions'] );
        if ( $rCount && $renditionsReady == $rCount ) {
          if ( $somethingFinished ) {
            $generate_player = true;
          }
        }
      }

      if ($generate_player ) {
        $meta_key = 'vzaar-upload-player';
        $meta = get_post_meta( $_GET['postID'], $meta_key, true );
        if( !$meta ) {
          $meta = array();
          // only generate new player if we don't already have one
          $player = array( 'videos' => array(), 'name' => $lookupFile->title );
          $video = array(
            'src' => str_replace('/video', '/adaptive.m3u8', $lookupFile->asset_url),
            'splash' => $lookupFile->poster_url,
            'caption' => $lookupFile->title,
            //'meta' => array( array( 'meta_key' => 'synopsis', 'meta_value' => $synopsis[0] ) )
          );

          $player['videos'][] = $video;
          $player_id = $FV_Player_Db->import_player_data(false, false, $player );

          $meta['player_id'] = $player_id;
          update_post_meta( $_GET['postID'], $meta_key, $meta );
          $ret['player_id'] = $player_id;
        } else {
          $ret['player_id'] = $meta['player_id'] . '...';
        }
      }
    }

    wp_send_json( $ret );
    wp_die();
  }

  function get_s3_client() {
    global $fv_fp, $s3Client, $FV_Player_DigitalOcean_Spaces;

    $endpoint = 'https://' . $FV_Player_DigitalOcean_Spaces->get_endpoint();

    $region = $FV_Player_DigitalOcean_Spaces->get_region();

    $secret = $fv_fp->_get_option(array('digitalocean_spaces','secret'));
    $key    = $fv_fp->_get_option(array('digitalocean_spaces','key'));

    $credentials = new Aws\Credentials\Credentials( $key, $secret );

    // instantiate the S3 client with AWS credentials
    $s3Client = Aws\S3\S3Client::factory( array(
      'credentials' => $credentials,
      'region'      => $region,
      'version'     => 'latest',
      'endpoint' => $endpoint
    ) );

    return $s3Client;
  }

  function get_formatted_assets_data() {
    global $fv_fp;

    $this->fv_wp_flowplayer_include_aws_sdk();
    $s3Client = $this->get_s3_client();

    $bucket = $fv_fp->_get_option(array('digitalocean_spaces','space'));
    //$domain = $fv_fp->_get_option(array('digitalocean_spaces','space'));

    $output = array(
      'name' => 'Home',
      'type' => 'folder',
      'path' => !empty($_POST['path']) ? $_POST['path'] : 'Home/',
      'items' => array()
    );

    try {
      $args = array(
        'Bucket' => $bucket,
        'Delimiter' => '/',
      );

      $request_path = !empty($_POST['path']) ? str_replace( 'Home/', '', stripslashes($_POST['path']) ) : false;

      if( $request_path ) {
        $args['Prefix'] = $request_path;
      }

      $paged = $s3Client->getPaginator('ListObjects',$args);

      $date_format = get_option( 'date_format' );
      foreach( $paged AS $res ) {

        $folders = !empty($res['CommonPrefixes']) ? $res['CommonPrefixes'] : array();
        $files = $res->get('Contents');
        if( !$files ) $files = array();

        $objects = array_merge( $folders, $files );

        foreach ( $objects as $object ) {
          if ( ! isset( $objectarray ) ) {
            $objectarray = array();
          }

          $item = array();

          $path = $object['Prefix'] ? $object['Prefix'] : $object['Key'];

          $item['path'] = 'Home/' . $path;

          if( $request_path ) {
            if( $request_path == $path ) continue; // sometimes the current folder is present in the response, weird

            $item['name'] = str_replace( $request_path, '', $path );
          } else {
            $item['name'] = $path;
          }

          if( !empty($object['Size']) ) {
            $item['type'] = 'file';
            $item['size'] = $object['Size'];
            $item['LastModified'] = $object['LastModified'];
            $item['modified'] = date($date_format, strtotime($object['LastModified']));

            $link = (string) $s3Client->getObjectUrl( $bucket, $path );
            $url_components = parse_url($link);
            $link = str_replace( $url_components['path'], urldecode($url_components['path']), $link );

            // TODO: check and implement custom domain URLs
            /*// replace link with Custom Domain URL, if we have one
            if( !empty($domains[$array_id]) ) {
              // replace S3 URLs with buckets in the S3 subdomain
              $link = preg_replace('/https?:\/\/' . $bucket . '\.s3[^.]*\.amazonaws\.com\/(.*)/i', rtrim($domains[$array_id], '/').'/$1', $link);

              // replace S3 URLs with bucket name as a subfolder
              $link = preg_replace('/https?:\/\/[^\/]+\/' . $bucket . '\/(.*)/i', rtrim($domains[$array_id], '/').'/$1', $link);
            }*/

            $item['link'] = $link;

            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $item['name'])) {
              $item['splash'] = apply_filters('fv_flowplayer_splash', $link );
            }
          } else {
            $item['type'] = 'folder';
            $item['items'] = array();
          }

          $output['items'][] = $item;

          if (strtolower(substr($item['name'], strrpos($item['name'], '.') + 1)) === 'ts') {
            continue;
          }

        }
      }

    } catch ( Aws\S3\Exception\S3Exception $e ) {
      //echo $e->getMessage() . "\n";
      $err = $e->getMessage();
      $output = array(
        'items' => array(),
        'name' => '/',
        'path' => '/',
        'type' => 'folder'
      );
    }
    
    // sorting by date, descending
    // TODO: Make this an interface option? How to handle it for paged listings, like on Vimeo?
    function date_compare($a, $b) {
      $t1 = strtotime($a['LastModified']);
      $t2 = strtotime($b['LastModified']);
      return $t1 - $t2;
    }    
    usort($output['items'], 'date_compare');
    
    $output['items'] = array_reverse($output['items']);
    
    $json_final = array(
      'items' => $output
    );

    if (isset($err) && $err) {
      $json_final['err'] = $err;
    }

    return $json_final;
  }

  function load_assets() {
    $json_final = $this->get_formatted_assets_data();

    wp_send_json( $json_final );
    wp_die();
  }

  // checks whether options for DOS are set
  // ... used to determine whether to actually include the DOS JS or not
  public function isSetUpCorrectly() {
    global $fv_fp;

    return (
      $fv_fp->_get_option(array('digitalocean_spaces','endpoint'))
      && $fv_fp->_get_option(array('digitalocean_spaces','secret'))
      && $fv_fp->_get_option(array('digitalocean_spaces','key'))
      && $fv_fp->_get_option(array('digitalocean_spaces','space'))
    );
  }

}

new FV_Player_DigitalOcean_Spaces_Browser( 'wp_ajax_load_dos_assets' );

endif;
