<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( !class_exists('FV_Player_DigitalOcean_Spaces_Browser') && class_exists('FV_Player_Media_Browser') ) :

class FV_Player_DigitalOcean_Spaces_Browser extends FV_Player_Media_Browser {

  function init() {
    if( $this->isSetUpCorrectly() ) {
      global $fv_wp_flowplayer_ver;
      wp_enqueue_script( 'fv-player-dos-browser', flowplayer::get_plugin_url().'/js/digitalocean-spaces-browser.js', array( 'flowplayer-browser-base' ), $fv_wp_flowplayer_ver );
      wp_localize_script( 'fv-player-dos-browser', 'fv_player_dos_browser', array(
          'nonce' => wp_create_nonce( $this->ajax_action_name ),
        )
      );
    }
  }

  function decode_link_components( $link ) {
    $url_components = wp_parse_url($link);
    $link = str_replace( $url_components['path'], urldecode($url_components['path']), $link );

    return $link;
  }

  function get_custom_domain_url( $link, $bucket, $custom_domain ) {
    // TODO: check and implement custom domain URLs - replace link with Custom Domain URL, if we have one
    return $link;
  }

  // Legacy
  function init_for_gutenberg() {}

  function get_s3_client() {
    global $fv_fp, $FV_Player_DigitalOcean_Spaces;

    // instantiate the S3 client with AWS credentials
    $endpoint = 'https://' . $FV_Player_DigitalOcean_Spaces->get_endpoint();

    $region = $FV_Player_DigitalOcean_Spaces->get_region();

    $secret = $fv_fp->_get_option(array('digitalocean_spaces','secret'));
    $key    = $fv_fp->_get_option(array('digitalocean_spaces','key'));

    $credentials = new Aws\Credentials\Credentials( $key, $secret );

    return Aws\S3\S3Client::factory( array(
      'credentials'                 => $credentials,
      'region'                      => $region,
      'version'                     => 'latest',
      'endpoint'                    => $endpoint,
      'use_aws_shared_config_files' => false
    ) );
  }

  function get_formatted_assets_data() {
    if( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->ajax_action_name ) ) {
      return array(
        'items' => array(),
        'name' => '/',
        'path' => '/',
        'type' => 'folder',
        'err' => 'Invalid nonce'
      );
    }

    $this->include_aws_sdk();
    global $fv_fp, $s3Client;

    $bucket = $fv_fp->_get_option(array('digitalocean_spaces','space'));

    $bucket = explode( ',', $bucket );
    $bucket = $bucket[0];

    //$domain = $fv_fp->_get_option(array('digitalocean_spaces','space'));

    $output = $this->get_output();

    // instantiate the S3 client with AWS credentials
    $s3Client = $this->get_s3_client();

    try {

      list( $request_path, $paged, $date_format ) = $this->get_metadata( $s3Client, $bucket );

      list($output, $sum_up ) = $this->get_output_items( $output, $s3Client, $request_path, $paged, $date_format, $bucket );

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
    function FV_Player_DigitalOcean_Spaces_Browser_date_compare($a, $b) {
      $t1 = ! empty( $a['LastModified'] ) ? strtotime($a['LastModified']) : 0;
      $t2 = ! empty( $b['LastModified'] ) ? strtotime($b['LastModified']) : 0;
      return $t1 - $t2;
    }
    usort($output['items'], 'FV_Player_DigitalOcean_Spaces_Browser_date_compare');

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
