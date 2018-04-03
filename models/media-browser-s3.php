<?php

class FV_Player_Media_Browser_S3 extends FV_Player_Media_Browser {

  function get_formatted_assets_data() {
    fv_wp_flowplayer_include_aws_sdk();
    global $fv_fp, $s3Client;

    // load CloudFront setttings
    $cfDomains = $fv_fp->_get_option( array('pro','cf_domains_list') );
    $cfBuckets = $fv_fp->_get_option( array('pro','cf_buckets_list') );

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
          break;
        }
      }
    }

    if ($regioned_bucket_found) {
      $region = $regions[ $array_id ];
      $secret = $secrets[ $array_id ];
      $key    = $keys[ $array_id ];
      $bucket = $buckets[ $array_id ];
      $cf_domain_to_use = null;

      // check if we have current bucket assigned to an URL
      if (is_array($cfBuckets) && count($cfBuckets)) {
        foreach ($cfBuckets as $cf_bucket_index => $cf_bucket_id) {
          if ($cf_bucket_id == $array_id) {
            $cf_domain_to_use = strtolower($cfDomains[$cf_bucket_index]);

            // add HTTP, if not set
            if (substr($cf_domain_to_use, 0, 4) !== 'http') {
              $cf_domain_to_use = 'http://' . $cf_domain_to_use;
            }

            $domains[ $array_id ] = $cf_domain_to_use;

            break;
          }
        }
      }

      $credentials = new Aws\Credentials\Credentials( $key, $secret );

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

          if ( $object['Size'] != '0' ) {

            $link = (string) $s3Client->getObjectUrl( $bucket, $name );

            // replace link with CloudFront URL, if we have one
            if ($cf_domain_to_use !== null) {
              // replace S3 URLs with buckets in the S3 subdomain
              $link = preg_replace('/https?:\/\/' . $bucket . '\.s3[^.]*\.amazonaws\.com\/(.*)/i', rtrim($cf_domain_to_use, '/').'/$1', $link);

              // replace S3 URLs with bucket name as a subfolder
              $link = preg_replace('/https?:\/\/[^\/]+\/' . $bucket . '\/(.*)/i', rtrim($cf_domain_to_use, '/').'/$1', $link);
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

        $output        = array();
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

    if (isset($err) && $err) {
      $json_final['err'] = $err;
    }

    return $json_final;
  }

}