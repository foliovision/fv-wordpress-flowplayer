<?php
  require_once 'bootstrap.php';

  if ( empty($config['existing_player_id']) ) {
    throw new Exception('No existing player ID found in configuration. Please update your bootstrap.php file in test/singlepurpose foloder with a valid config!');
  }

  // setup
  $existing_player_id = $config['existing_player_id'];

  // execution
  global $FV_Player_Db;
  $player = new FV_Player_Db_Player( $existing_player_id, array(), $FV_Player_Db );
  $player2 = new FV_Player_Db_Player( $existing_player_id, array(), $FV_Player_Db );

  if ( $player->getId() !== $player2->getId() ) {
    echo "Same cached player ID test failed on the 'id' parameter, not returning same value for second player. ID used: $existing_player_id";
    return;
  }

  echo "Same cached player ID test passed.\n";