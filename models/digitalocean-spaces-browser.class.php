<?php

if( !class_exists('FV_Player_DigitalOcean_Spaces_Browser') && class_exists('FV_Player_Media_Browser') ) :

class FV_Player_DigitalOcean_Spaces_Browser extends FV_Player_Media_Browser {

  function init() {
    if( $this->isSetUpCorrectly() ) {
      global $fv_wp_flowplayer_ver;
      wp_enqueue_script( 'fv-player-dos-browser', flowplayer::get_plugin_url().'/js/digitalocean-spaces-browser.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );
    }
  }

  // Legacy
  function init_for_gutenberg() {}

  function fv_wp_flowplayer_include_aws_sdk() {
    if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
      require_once( dirname( __FILE__ ) . "/../vendor/autoload.php" );
    }
  }

  function get_s3_client() {
    global $fv_fp, $FV_Player_DigitalOcean_Spaces;

    // instantiate the S3 client with AWS credentials
    $endpoint = 'https://' . $FV_Player_DigitalOcean_Spaces->get_endpoint();

    $region = $FV_Player_DigitalOcean_Spaces->get_region();

    $secret = $fv_fp->_get_option(array('digitalocean_spaces','secret'));
    $key    = $fv_fp->_get_option(array('digitalocean_spaces','key'));

    $credentials = new Aws\Credentials\Credentials( $key, $secret );

    return Aws\S3\S3Client::factory( array(
      'credentials' => $credentials,
      'region'      => $region,
      'version'     => 'latest',
      'endpoint' => $endpoint
    ) );
  }

  function get_formatted_assets_data() {
    $this->fv_wp_flowplayer_include_aws_sdk();
    global $fv_fp, $s3Client;

    $bucket = $fv_fp->_get_option(array('digitalocean_spaces','space'));
    //$domain = $fv_fp->_get_option(array('digitalocean_spaces','space'));

    $output = array(
      'name' => 'Home',
      'type' => 'folder',
      'path' => !empty($_POST['path']) ? $_POST['path'] : 'Home/',
      'items' => array()
    );

    // instantiate the S3 client with AWS credentials
    $s3Client = $this->get_s3_client();

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

global $FV_Player_DigitalOcean_Spaces_Browser;
$FV_Player_DigitalOcean_Spaces_Browser = new FV_Player_DigitalOcean_Spaces_Browser( 'wp_ajax_load_dos_assets' );

endif;
