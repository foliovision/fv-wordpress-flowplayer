<?php
  require_once 'bootstrap.php';

  if ( empty($config['existing_video_id']) ) {
    throw new Exception('No existing video ID found in configuration. Please update your bootstrap.php file in test/singlepurpose foloder with a valid config!');
  }

  // setup
  $existing_video_id = $config['existing_video_id'];
  $video_meta_key_name = 'meta_test';
  $initial_video_meta_value = 'it works';
  $new_video_meta_value = 'it really works';

  // execution
  global $FV_Player_Db;
  $objVideo = new FV_Player_Db_Video( $config['existing_video_id'], array(), $FV_Player_Db );

  if ( !$objVideo->getIsValid() ) {
    throw new Exception('Invalid existing video ID found in configuration. Please update your bootstrap.php file in test/singlepurpose foloder with a valid config!');
  }

  // create a new meta value first
  $objVideo->updateMetaValue( $video_meta_key_name, $initial_video_meta_value );

  // update the test key with a different value
  $objVideo->updateMetaValue( $video_meta_key_name, $new_video_meta_value );

  global $wpdb;
  $video_meta_count = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM {$wpdb->prefix}fv_player_videometa WHERE id_video = %d AND meta_key = '%s'", $config['existing_video_id'], $video_meta_key_name ) );

  // check that we only have a single meta key
  if ( $video_meta_count != 1 ) {
    echo 'Video meta key duplication failed with a duplicate "meta_test" key for video ID ' . $existing_video_id;
    return;
  }

  // check that this meta key has indeed the correct value
  $video_meta_value = $wpdb->get_row( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}fv_player_videometa WHERE id_video = %d AND meta_key = '%s'", $config['existing_video_id'], $video_meta_key_name ) );
  if ( $video_meta_value->meta_value != $new_video_meta_value ) {
    echo 'Video meta key duplication failed with invalid value for "meta_test" key (' .  $video_meta_value->meta_value . ') after update for video ID ' . $existing_video_id;
    return;
  }

  echo "Video meta key duplication test passed.\n";