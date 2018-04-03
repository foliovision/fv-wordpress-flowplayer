<?php

class FV_Player_Media_Browser {

  public $ajax_action_name = 'wp_ajax_load_assets';

  public function __construct($ajax_action_name) {
    $this->ajax_action_name = $ajax_action_name;
    $this->register();
  }

  function register() {
    add_action( $this->ajax_action_name, array($this, 'load_assets') );
  }

  function get_formatted_assets_data() {
    return json_decode('{"buckets":[{"id":0,"name":"flowplayer500600 (eu-west-1) - http:\/\/sjdua7x04ygyx.cloudfront.net"},{"id":-1,"name":"fv-flowplayer-frankfurt (no region)"}],"region_names":{"us-east-1":"US East (N. Virginia)","us-east-2":"US East (Ohio)","us-west-1":"US West (N. California)","us-west-2":"US West (Oregon)","ca-central-1":"Canada (Central)","ap-south-1":"Asia Pacific (Mumbai)","ap-northeast-2":"Asia Pacific (Seoul)","ap-southeast-1":"Asia Pacific (Singapore)","ap-southeast-2":"Asia Pacific (Sydney)","ap-northeast-1":"Asia Pacific (Tokyo)","eu-central-1":"EU (Frankfurt)","eu-west-1":"EU (Ireland)","eu-west-2":"EU (London)","sa-east-1":"South America (S&atilde;o Paulo)"},"active_bucket_id":0,"items":{"name":"Home","type":"folder","path":"Home\/","items":[{"name":"01 The Beginning.mp3","size":2117536,"type":"file","path":"Home\/01 The Beginning.mp3","link":"http:\/\/sjdua7x04ygyx.cloudfront.net\/01%20The%20Beginning.mp3"},{"name":"Fender_Bass_Guitar_Patent.jpg","size":495756,"type":"file","path":"Home\/Fender_Bass_Guitar_Patent.jpg","link":"http:\/\/sjdua7x04ygyx.cloudfront.net\/Fender_Bass_Guitar_Patent.jpg"}]}}', true);
  }

  function load_assets() {
    $json_final = $this->get_formatted_assets_data();

    wp_send_json( $json_final );
    wp_die();
  }

}