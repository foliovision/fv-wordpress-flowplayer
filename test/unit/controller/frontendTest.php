<?php
use PHPUnit\Framework\TestCase;

final class FV_Player_Controller extends PHPUnit_Framework_TestCase {

  private $fvPlayerProInstance;

  // playlist items
  private $playlist_items = array(
    0 =>
      array (
        'sources' =>
          array (
            0 =>
              array (
                'src' => 'https://youtu.be/7uY0Ab5HlZ0',
                'type' => 'video/youtube'
              )
          )
      ),
    1 =>
      array (
        'sources' =>
          array (
            0 =>
              array (
                'src' => 'https://www.youtube.com/watch?v=1XiHhpGUmQg',
                'type' => 'video/youtube'
              )
          )
      ),
    2 =>
      array (
        'sources' =>
          array (
            0 =>
              array (
                'src' => 'https://www.youtube.com/watch?v=Q1eR8pUM5iY',
                'type' => 'video/youtube'
              )
          )
      )
  );

  // ads
  private $adsMock = array(
    array(
      'videos' => array(
        'mp4' => 'https://www.youtube.com/watch?v=tPEE9ZwTmy0'
      ),
      'disabled' => '0',
      'name' => 'cat preroll',
      'click' => 'http://www.pobox.sk'
    ),
    array(
      'videos' => array(
        'mp4' => 'https://www.youtube.com/watch?v=bCGmUCDj4Nc'
      ),
      'disabled' => '1',
      'name' => 'kids postroll',
      'click' => 'http://www.foliovision.com'
    ),
    array(
      'videos' => array(
        'mp4' => 'https://www.youtube.com/watch?v=OsAVRDo9znQ'
      ),
      'disabled' => '0',
      'name' => 'funny whatroll',
      'click' => 'http://www.google.com'
    )
  );

  public function setUp() {
    // set an empty global return value to be used
    // in all the mocked global WordPress functions
    // like add_action() and the such
    global $testReturnValue;
    $testReturnValue = '';

    include_once "../../controller/frontend.php";
    //$this->fvPlayerProInstance = new FV_Player_Pro();
  }

  public function tearDown() {
    Mockery::close();
  }
  
  public function test_flowplayer_prepare_scripts() {
    $this->assertTrue( true );
  }

}
