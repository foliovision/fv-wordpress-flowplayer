<?php
  // configuration
  $config = array(
    'existing_video_id' => 1
  );

  // initialization
  $loop_breaker = 0;
  $path_prefix = '';

  // search for wp-load.php until we've gone too far,
  // in which case break the loop and raise error
  while ( !file_exists( $path_prefix . 'wp-load.php' ) && $loop_breaker++ < 25 ) {
    $loop_breaker++;
    $path_prefix .= '../';
  }

  if ( !file_exists( $path_prefix . 'wp-load.php' ) ) {
    throw new Exception('wp-load.php file not found. Could not run single purpose tests.');
  }

  include($path_prefix . 'wp-load.php');