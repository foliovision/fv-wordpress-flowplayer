<?php

class FV_Player_Media_Browser_S3 extends FV_Player_Media_Browser {

  function init() {
    global $fv_fp, $fv_wp_flowplayer_ver;
    if ($fv_fp->_get_option('s3_browser')) {
      wp_enqueue_script( 'flowplayer-aws-s3', flowplayer::get_plugin_url().'/js/s3-browser.js', array('flowplayer-browser-base'), $fv_wp_flowplayer_ver, true );
    }
  }

  function decode_link_components( $link ) {
    $link = str_replace( '%20', '+', $link );

    return $link;
  }

  function get_custom_domain_url( $link, $bucket, $custom_domain ) {
    // replace S3 URLs with buckets in the S3 subdomain, like https://fv-flowplayer-cloudfront.s3-us-west-2.amazonaws.com/video.mp4
    $link = preg_replace('/https?:\/\/' . $bucket . '\.s3[^.]*\.amazonaws\.com\/(.*)/i', rtrim($custom_domain, '/').'/$1', $link);

    // replace S3 URLs with buckets in the S3 subdomain, like https://fv-flowplayer-cloudfront.s3.us-west-2.amazonaws.com/video.mp4
    $link = preg_replace('/https?:\/\/' . $bucket . '\.s3\.[^.]*\.amazonaws\.com\/(.*)/i', rtrim($custom_domain, '/').'/$1', $link);

    // replace S3 URLs with bucket name as a subfolder
    $link = preg_replace('/https?:\/\/[^\/]+\/' . $bucket . '\/(.*)/i', rtrim($custom_domain, '/').'/$1', $link);

    return $link;
  }

  function get_formatted_assets_data() {
    $this->include_aws_sdk();
    global $fv_fp, $s3Client;

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
      
      $output = $this->get_output();
      
      $region = $regions[ $array_id ];
      $secret = $secrets[ $array_id ];
      $key    = $keys[ $array_id ];
      $bucket = $buckets[ $array_id ];

      $credentials = new Aws\Credentials\Credentials( $key, $secret );
      
      try {
        $cloudfronts = get_transient('fv_player_s3_browser_cf');
        if( !$cloudfronts ) {
          $cfClient = Aws\CloudFront\CloudFrontClient::factory( array(
          'credentials' => $credentials,
          'region' => 'us-east-1',
          'version' => 'latest'
          ) );
  
          $cloudfronts = $cfClient->listDistributions();
          if( !empty($cloudfronts['DistributionList']['Items']) ) {
            set_transient('fv_player_s3_browser_cf',$cloudfronts,60);
          }
        }
        
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
            if( !empty($regions) && !empty($regions[$bucket_id]) && $bucket_name.'.s3.'.$regions[$bucket_id].'.amazonaws.com' == $origin ) {
              $domains[$bucket_id] = 'https://'.$cf_domain; // todo: check if SSL is enabled for custom domains!
            } else if( $bucket_name.'.s3.amazonaws.com' == $origin ) {
              $domains[$bucket_id] = 'https://'.$cf_domain; // todo: check if SSL is enabled for custom domains!
            }            
          }
          
        }

      } catch ( Aws\CloudFront\Exception\CloudFrontException $e ) {
        $err = 'It appears that the policy of AWS IAM user identified by '.$key.' doesn\'t permit List and Read operations for the CloudFront service. Please add CloudFrontReadOnlyAccess policy for the user if you are using CloudFront for your S3 buckets. Otherwise you won\'t be getting the proper CloudFront links for your videos.';
      }
      
      // instantiate the S3 client with AWS credentials
      $s3Client = Aws\S3\S3Client::factory( array(
        'credentials' => $credentials,
        'region'      => $region,
        'version'     => 'latest'
      ) );

      try {

        list( $request_path, $paged, $date_format ) = $this->get_metadata( $s3Client, $bucket );

        list( $output, $sum_up ) = $this->get_output_items( $output, $s3Client, $request_path, $paged, $date_format, $bucket, array(), $domains[$array_id] );

        foreach( $sum_up AS $ext => $count ) {
          $output['items'][] = array(
            'name' => '*.ts',
            'link' => '',
            'size' => $count.' .'.$ext.' files hidden',
            'type' => 'placeholder'
          );
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
        $output :
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

new FV_Player_Media_Browser_S3( 'wp_ajax_load_s3_assets' );