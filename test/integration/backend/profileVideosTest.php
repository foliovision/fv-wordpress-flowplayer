<?php

require_once( dirname(__FILE__).'/../fv-player-unittest-case.php');

/**
 * Tests WordPress integration of playlists without any advertisements present
 * in the HTML markup.
 */
final class FV_Player_ProfileVideosTestCase extends FV_Player_UnitTestCase {

  private $userID;

  public function testProfileScreen() {
    global $fv_fp;
    $fv_fp->conf['profile_videos_enable_bio'] = true;

    // add new user and create last saved position metadata for this new user
    $this->userID = $this->factory->user->create(array(
      'role' => 'admin'
    ));

    add_user_meta($this->userID, '_fv_player_user_video', '[fvplayer src="https://vimeo.com/255317467" playlist="https://vimeo.com/192934117" caption=";Talking about FV Player"]');
    add_user_meta($this->userID, '_fv_player_user_video', '[fvplayer src="https://vimeo.com/255370388"]');
    add_user_meta($this->userID, '_fv_player_user_video', '[fvplayer src="https://www.youtube.com/watch?v=6ZfuNTqbHE8"]');

    $profileuser = get_user_to_edit($this->userID);

    ob_start();
    apply_filters( 'show_password_fields', true, $profileuser );
    $output = ob_get_clean();

    $this->assertTrue(
      strpos( $output, 'https://vimeo.com/255317467') !== false,
      'Profile screen should contain the first video'
    );

    $this->assertTrue(
      strpos( $output, 'https://vimeo.com/255370388') !== false,
      'Profile screen should contain the second video'
    );

    $this->assertTrue(
      strpos( $output, '6ZfuNTqbHE8') !== false,
      'Profile screen should contain the third video'
    );

    $this->assertTrue(
      substr_count( $output, "<button class='button fv-player-editor-button'>Edit Video</button>" ) === 3,
      'Profile screen should contain 3 "Edit Video" buttons'
    );

    $this->assertTrue(
      substr_count( $output, "<button class='button fv-player-editor-more' style='display:none'>Add Another Video</button>" ) === 3,
      'Profile screen should contain 3 hidden "Add Video" buttons'
    );

    preg_match_all( "~<input.*?name='fv_player_videos\[_fv_player_user_video\]\[\]' type='hidden' value='\[fvplayer src=~", $output, $matches );

    $this->assertTrue(
      count( $matches[0] ) === 3,
      'Profile screen should contain 3 [fvplayer] shortcodes with src as hidden inputs'
    );
  }

}
