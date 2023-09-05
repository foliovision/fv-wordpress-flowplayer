<?php

if( !class_exists('FV_Player_DigitalOcean_Spaces_Browser') && class_exists('FV_Player_Media_Browser') ) :

class FV_Player_DigitalOcean_Spaces_Browser extends FV_Player_Media_Browser {

  function init() {
    if( $this->isSetUpCorrectly() ) {
      global $fv_wp_flowplayer_ver;
      wp_enqueue_script( 'fv-player-dos-browser', flowplayer::get_plugin_url().'/js/digitalocean-spaces-browser.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );
    }
  }

  function decode_link_components( $link ) {
    $url_components = parse_url($link);
    $link = str_replace( $url_components['path'], urldecode($url_components['path']), $link );

    return $link;
  }

  function get_custom_domain_url( $link, $bucket, $custom_domain ) {
    // TODO: check and implement custom domain URLs - replace link with Custom Domain URL, if we have one
    return $link;
  }

  // Legacy
  function init_for_gutenberg() {}

  function get_endpoint() {
    global $FV_Player_DigitalOcean_Spaces;

    return $FV_Player_DigitalOcean_Spaces->get_endpoint();
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

  function get_s3_async_aws_client() {
    global $fv_fp, $FV_Player_DigitalOcean_Spaces;

    // instantiate the S3 client with AWS credentials
    $endpoint = 'https://' . $this->get_endpoint();

    $region = $FV_Player_DigitalOcean_Spaces->get_region();

    $secret = $fv_fp->_get_option(array('digitalocean_spaces','secret'));
    $key    = $fv_fp->_get_option(array('digitalocean_spaces','key'));

    return new AsyncAws\S3\S3Client( array(
      'accessKeyId' => $key,
      'accessKeySecret' => $secret,
      'region'      => $region,
      'endpoint' => $endpoint
    ) );

  }

  function get_formatted_assets_data() {
    // $this->include_aws_sdk();
    $this->include_async_aws_sdk();

    global $fv_fp;

    $bucket = $fv_fp->_get_option(array('digitalocean_spaces','space'));

    $bucket = explode( ',', $bucket );
    $bucket = $bucket[0];

    //$domain = $fv_fp->_get_option(array('digitalocean_spaces','space'));

    $output = $this->get_output();

    // instantiate the S3 client with AWS credentials
    $s3Client = $this->get_s3_async_aws_client();

    try {
      $args = array(
        'Bucket' => $bucket,
        'Delimiter' => '/',
        'MaxKeys' => 1000,
      );

      $date_format = get_option( 'date_format' );

      $request_path = !empty($_POST['path']) ? str_replace( 'Home/', '', stripslashes($_POST['path']) ) : false;

      if ( $request_path ) {
        $args['Prefix'] = $request_path;
      }

      $paged = $s3Client->listObjectsV2($args);

      foreach($paged->getIterator() as $object) {
        if( $object instanceof \AsyncAws\S3\ValueObject\AwsObject ) {
          $path = $object->getKey();
        } else {
          $path = $object->getPrefix();
        }

        $item = array();

        $item['path'] = 'Home/' . $path;

        if( $request_path ) {
          $item['name'] = str_replace( $request_path, '', $path );
        } else {
          $item['name'] = $path;
        }

        if( $object instanceof \AsyncAws\S3\ValueObject\AwsObject ) {
          $dateString = $object->getLastModified()->format('Y-m-d H:i:s');
          $timetamp = strtotime($dateString);
          $item['modified'] = date($date_format, $timetamp);
          $item['LastModified'] = $timetamp;
          $item['size'] = $object->getSize();
          $item['type'] = 'file';
  
          $endpoint = $this->get_endpoint();
  
          $link = 'https://' . $bucket . '.' . $endpoint . '/' . $path;
  
          $item['link'] = $link;
        } else {
          $item['LastModified'] = 0;
          $item['type'] = 'folder';
          $item['items'] = array();
        }

        $output['items'][] = $item;
      }

    } catch ( Exception $e ) {
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
      $t1 = $a['LastModified'];
      $t2 = $b['LastModified'];
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
