<?php

class FV_Player_Media_Browser_S3 extends FV_Player_Media_Browser {

  function __construct( $ajax_action_name ) {
    add_action( 'edit_form_after_editor', array($this, 'init'), 1 );
    parent::__construct( $ajax_action_name );
  }

  function init() {
    global $fv_fp, $fv_wp_flowplayer_ver;
    if ($fv_fp->_get_option('s3_browser')) {
      wp_enqueue_script( 'flowplayer-aws-s3', flowplayer::get_plugin_url().'/js/s3-browser.js', array(), $fv_wp_flowplayer_ver, true );
    }
  }

  function fv_wp_flowplayer_include_aws_sdk() {
    if ( ! class_exists( 'Aws\S3\S3Client' ) ) {
      require_once( dirname( __FILE__ ) . "/../includes/aws/aws-autoloader.php" );
    }
  }

  function get_formatted_assets_data() {
    $this->fv_wp_flowplayer_include_aws_sdk();
    global $fv_fp, $s3Client;

    $error = false;

    $regions = $fv_fp->_get_option('amazon_region');
    $secrets = $fv_fp->_get_option('amazon_secret');
    $keys    = $fv_fp->_get_option('amazon_key');
    $buckets = $fv_fp->_get_option('amazon_bucket');
    $region_names = fv_player_get_aws_regions();
    $domains = array();

    if (isset($_POST['bucket']) && isset($buckets[$_POST['bucket']])) {
      $array_id = $_POST['bucket'];
    } else {
      $array_id = 0;
    }

    // remove all buckets with missing name
    $keep_checking = true;
    while ($keep_checking) {
      // break here if there are no buckets left
      if (!count($buckets)) {
        break;
      } else {
        // remove any bucket without a name
        $all_ok = true;
        foreach ($buckets as $bucket_id => $bucket_name) {
          if (!$bucket_name) {
            unset($buckets[$bucket_id], $regions[$bucket_id], $secrets[$bucket_id], $keys[$bucket_id]);

            // adjust the selected bucket to the first array ID if we just
            // removed the one we chose to display
            if ($array_id == $bucket_id) {
              reset($buckets);
              $array_id = key($buckets);
            }

            $all_ok = false;
            break;
          }
        }

        // all buckets have names, we can stop the check now
        if ($all_ok) {
          $keep_checking = false;
        }
      }
    }

    // if the selected bucket is a region-less one, change the $array_id variable
    // to one that has a region
    $regioned_bucket_found = (count($buckets) ? true : false);
    if (!$regions[$array_id]) {
      $regioned_bucket_found = false;
      foreach ($buckets as $bucket_id => $unused) {
        if ($regions[$bucket_id]) {
          $array_id = $bucket_id;
          $regioned_bucket_found = true;
        }
      }
    }

    if ($regioned_bucket_found) {
      
      $output = array();
      
      $region = $regions[ $array_id ];
      $secret = $secrets[ $array_id ];
      $key    = $keys[ $array_id ];
      $bucket = $buckets[ $array_id ];

      $credentials = new Aws\Credentials\Credentials( $key, $secret );
      
      try {

        $cfClient = Aws\CloudFront\CloudFrontClient::factory( array(
        'credentials' => $credentials,
        'region' => 'us-east-1',
        'version' => 'latest'
        ) );

        $cloudfronts = $cfClient->listDistributions();        
        foreach( $cloudfronts['DistributionList']['Items'] AS $item ) {
          if( !$item['Enabled'] ) continue;
          
          $cf_domain = $item['DomainName'];
          if( !empty($item['Aliases']) && !empty($item['Aliases']['Items']) && !empty($item['Aliases']['Items'][0]) ) {
            $cf_domain = $item['Aliases']['Items'][0];
          }
          $origin = false;
          if( !empty($item['Origins']) && !empty($item['Origins']['Items']) && !empty($item['Origins']['Items'][0]) && !empty($item['Origins']['Items'][0]['DomainName']) ) {
            $origin = $item['Origins']['Items'][0]['DomainName'];
          }
          
          foreach( $buckets as $bucket_id => $bucket_name ) {
            if( $bucket_name.'.s3.amazonaws.com' == $origin ) {
              $domains[$bucket_id] = 'https://'.$cf_domain; // todo: check if SSL is enabled for custom domains!
            }            
          }
          
        }

      } catch ( Aws\CloudFront\Exception\CloudFrontException $e ) {
        $error = 'It appears that the policy of AWS IAM user identified by '.$key.' doesn\'t permit List and Read operations for the CloudFront service. Please add these access levels if you are using CloudFront for your S3 buckets in order to obtain CloudFront links for your videos.';
      }
      
      // instantiate the S3 client with AWS credentials
      $s3Client = Aws\S3\S3Client::factory( array(
        'credentials' => $credentials,
        'region'      => $region,
        'version'     => 'latest'
      ) );

      try {
        $objects = $s3Client->getIterator( 'ListObjects', array( 'Bucket' => $bucket ) );

        $path_array = array();
        $size_array = array();
        $link_array = array();

        foreach ( $objects as $object ) {
          if ( ! isset( $objectarray ) ) {
            $objectarray = array();
          }
          //print_r($object);
          $name = $object['Key'];
          $size = $object['Size'];

          if (strtolower(substr($name, strrpos($name, '.') + 1)) === 'ts') {
            continue;
          }

          if ( $object['Size'] != '0' ) {

            $link = (string) $s3Client->getObjectUrl( $bucket, $name );
            $link = str_replace( '%20', '+', $link );
            
            // replace link with CloudFront URL, if we have one
            if( !empty($domains[$array_id]) ) {
              // replace S3 URLs with buckets in the S3 subdomain
              $link = preg_replace('/https?:\/\/' . $bucket . '\.s3[^.]*\.amazonaws\.com\/(.*)/i', rtrim($domains[$array_id], '/').'/$1', $link);

              // replace S3 URLs with bucket name as a subfolder
              $link = preg_replace('/https?:\/\/[^\/]+\/' . $bucket . '\/(.*)/i', rtrim($domains[$array_id], '/').'/$1', $link);
            }

            $path = 'Home/' . $name;

            $path_array[] = $path;
            $size_array[] = $size;
            $link_array[] = $link;

          }

        }

        function &placeInArray( array &$dest, array $path_array, $size, $pathorig, $link ) {
          // If we're at the leaf of the tree, just push it to the array and return
          //echo $pathorig;
          //echo $size."<br>";

          global $folders_added;
          if ( count( $path_array ) === 1 ) {
            if ( $path_array[0] !== '' ) {
              $file_array         = array();
              $file_array['name'] = $path_array[0];
              $file_array['size'] = $size;
              $file_array['type'] = 'file';
              $file_array['path'] = $pathorig;
              $file_array['link'] = $link;
              array_push( $dest['items'], $file_array );
            }

            return $dest;
          }

          // If not (if multiple elements exist in path_array) then shift off the next path-part...
          // (this removes $path_array's first element off, too)
          $path = array_shift( $path_array );

          if ( $path ) {

            $newpath_temp = explode( $path, $pathorig );
            $newpath      = $newpath_temp[0] . $path . '/';
            // ...make a new sub-array for it...


            //if (!isset($dest['items'][$path])) {
            if ( ! in_array( $newpath, $folders_added, true ) ) {
              $dest['items'][] = array(

                'name'  => $path,
                'type'  => 'folder',
                'path'  => $newpath,
                'items' => array()

              );
              $folders_added[] = $newpath;
              //print_r($folders_added);
            }
            $count = count( $dest['items'] );
            $count --;
            //echo $count.'<br>';
            //print_r($dest['items'][$path]);

            // ...and continue the process on the sub-array
            return placeInArray( $dest['items'][ $count ], $path_array, $size, $pathorig, $link );
          }

          // This is just here to blow past multiple slashes (an empty path-part), like
          // /path///to///thing
          return placeInArray( $dest, $path_array, $size, $pathorig, $link );
        }

        $folders_added = array();
        $i             = 0;
        foreach ( $path_array as $path ) {
          $size = $size_array[ $i ];
          $link = $link_array[ $i ];
          placeInArray( $output, explode( '/', $path ), $size, $path, $link );
          $i ++;
        }
      } catch ( Aws\S3\Exception\S3Exception $e ) {
        //echo $e->getMessage() . "\n";
        $err = $e->getMessage();
        $output['items'] = array(
          'items' => array(),
          'name' => '/',
          'path' => '/',
          'type' => 'folder'
        );
      }
    }

    // prepare list of buckets for the selection dropdown
    $buckets_output = array();
    $negative_ids = -1;
    foreach ( $buckets as $bucket_index => $bucket_name ) {
      $has_all_data = ($regions[ $bucket_index ] && $keys[ $bucket_index ] && $secrets[ $bucket_index ]);
      $buckets_output[] = array(
        'id'   => ($has_all_data ? $bucket_index : $negative_ids--),
        'name' => $bucket_name . ' (' . ($regions[ $bucket_index ] ? $regions[ $bucket_index ] : translate('no region', 'fv-wordpress-flowplayer')) . ')' . (!empty($domains[$bucket_index]) ? ' - '. $domains[$bucket_index] : '')
      );
    }

    $json_final = array(
      'buckets'          => $buckets_output,
      'region_names'     => $region_names,
      'active_bucket_id' => $array_id,
      'items'            => (
      $regioned_bucket_found ?
        $output['items'][0] :
        array(
          'items' => array(),
          'name' => '/',
          'path' => '/',
          'type' => 'folder'
        )
      )
    );
    
    if( $error ) {
      $json_final['error'] = $error;
    }

    if (isset($err) && $err) {
      $json_final['err'] = $err;
    }

    return $json_final;
  }

}

new FV_Player_Media_Browser_S3( 'wp_ajax_load_s3_assets' );