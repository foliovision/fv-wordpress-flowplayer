<?php
  require_once 'bootstrap.php';

  // setup
  $invalid_player_id_value = '1 2'; // this should in reality be 12, but with an errorneous space, it becomes 1 2

  // execution
  $new_player = $fv_fp->build_min_player( false, array( 'id' => $invalid_player_id_value ) );
  if ( $new_player != null ) {
    echo "Invalid player ID test failed on the 'id' parameter, not returning null value on ID: $invalid_player_id_value";
    return;
  }

  echo "Invalid player ID test passed.\n";