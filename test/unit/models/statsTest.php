<?php
use PHPUnit\Framework\TestCase;

final class statsTest extends TestCase {

  protected $raw_db_results_by_posts = array (
    array (
      'date' => '2024-09-02',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-03',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '1',
    ),
    array (
      'date' => '2024-09-04',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '3',
    ),
    array (
      'date' => '2024-09-05',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '7',
    ),
    array (
      'date' => '2024-09-06',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-07',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-08',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-09',
      'id_post' => '5753',
      'id_video' => '26',
      'post_title' => 'Foliovision',
      'play' => '3',
    ),
    array (
      'date' => '2024-09-03',
      'id_post' => '8299',
      'id_video' => '912',
      'post_title' => 'Custom Video Ads in FV Player (pre-roll and post-roll)',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-07',
      'id_post' => '8299',
      'id_video' => '912',
      'post_title' => 'Custom Video Ads in FV Player (pre-roll and post-roll)',
      'play' => '4',
    ),
    array (
      'date' => '2024-09-04',
      'id_post' => '8398',
      'id_video' => '7',
      'post_title' => 'VAST Ads',
      'play' => '3',
    ),
    array (
      'date' => '2024-09-06',
      'id_post' => '8398',
      'id_video' => '7',
      'post_title' => 'VAST Ads',
      'play' => '3',
    ),
    array (
      'date' => '2024-09-05',
      'id_post' => '8455',
      'id_video' => '912',
      'post_title' => 'Pre-roll Custom Video Ads',
      'play' => '7',
    ),
    array (
      'date' => '2024-09-09',
      'id_post' => '8455',
      'id_video' => '913',
      'post_title' => 'Pre-roll Custom Video Ads',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-03',
      'id_post' => '8467',
      'id_video' => '879',
      'post_title' => 'Using YouTube with FV Player',
      'play' => '1',
    ),
    array (
      'date' => '2024-09-04',
      'id_post' => '8467',
      'id_video' => '879',
      'post_title' => 'Using YouTube with FV Player',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-04',
      'id_post' => '23826',
      'id_video' => '26',
      'post_title' => 'FV Player Pro',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-08',
      'id_post' => '23826',
      'id_video' => '26',
      'post_title' => 'FV Player Pro',
      'play' => '1',
    ),
    array (
      'date' => '2024-09-04',
      'id_post' => '33834',
      'id_video' => '274',
      'post_title' => 'Encrypted HLS stream',
      'play' => '1',
    ),
    array (
      'date' => '2024-09-06',
      'id_post' => '33834',
      'id_video' => '274',
      'post_title' => 'Encrypted HLS stream',
      'play' => '3',
    ),
    array (
      'date' => '2024-09-05',
      'id_post' => '55205',
      'id_video' => '8',
      'post_title' => 'Playlist Styles',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-07',
      'id_post' => '55205',
      'id_video' => '8',
      'post_title' => 'Playlist Styles',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-03',
      'id_post' => '118484',
      'id_video' => '319',
      'post_title' => 'Video Stabilisation Test: iPhone 11 Pro vs Nikon Z6 vs Canon M6',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-05',
      'id_post' => '118484',
      'id_video' => '319',
      'post_title' => 'Video Stabilisation Test: iPhone 11 Pro vs Nikon Z6 vs Canon M6',
      'play' => '2',
    ),
    array (
      'date' => '2024-09-04',
      'id_post' => '142132',
      'id_video' => '516',
      'post_title' => 'Ok.ru Video',
      'play' => '3',
    ),
    array (
      'date' => '2024-09-07',
      'id_post' => '142132',
      'id_video' => '516',
      'post_title' => 'Ok.ru Video',
      'play' => '3',
    ),
    array (
      'date' => '2024-09-08',
      'id_post' => '142132',
      'id_video' => '516',
      'post_title' => 'Ok.ru Video',
      'play' => '2',
    ),
  );  

  protected $raw_db_results_by_videos = array(
    array(
      'date' => '2024-09-04',
      'id_player' => '4',
      'id_video' => '7',
      'title' => 'Swan-Lake-Reloaded',
      'play' => '3',
    ),
    array(
      'date' => '2024-09-06',
      'id_player' => '4',
      'id_video' => '7',
      'title' => 'Swan-Lake-Reloaded',
      'play' => '2',
    ),
    array(
      'date' => '2024-09-02',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '6',
    ),
    array(
      'date' => '2024-09-03',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '2',
    ),
    array(
      'date' => '2024-09-04',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '6',
    ),
    array(
      'date' => '2024-09-05',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '7',
    ),
    array(
      'date' => '2024-09-06',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '3',
    ),
    array(
      'date' => '2024-09-07',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '4',
    ),
    array(
      'date' => '2024-09-08',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '5',
    ),
    array(
      'date' => '2024-09-09',
      'id_player' => '14',
      'id_video' => '26',
      'title' => 'Foliovision Promo Video',
      'play' => '3',
    ),
    array(
      'date' => '2024-09-04',
      'id_player' => '225',
      'id_video' => '274',
      'title' => 'Foliovision-Promo-Video-original-maxrate',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-06',
      'id_player' => '225',
      'id_video' => '274',
      'title' => 'Foliovision-Promo-Video-original-maxrate',
      'play' => '3',
    ),
    array(
      'date' => '2024-09-03',
      'id_player' => '253',
      'id_video' => '319',
      'title' => 'Video Stabilisation Test: Nikon Z6 vs iPhone 11 Pro vs Canon M6 I',
      'play' => '2',
    ),
    array(
      'date' => '2024-09-05',
      'id_player' => '253',
      'id_video' => '319',
      'title' => 'Video Stabilisation Test: Nikon Z6 vs iPhone 11 Pro vs Canon M6 I',
      'play' => '2',
    ),
    array(
      'date' => '2024-09-05',
      'id_player' => '286',
      'id_video' => '356',
      'title' => 'Big Buck Bunny (HD) | FULL MOVIE Short film (2008)',
      'play' => '3',
    ),
    array(
      'date' => '2024-09-09',
      'id_player' => '286',
      'id_video' => '356',
      'title' => 'Big Buck Bunny (HD) | FULL MOVIE Short film (2008)',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-04',
      'id_player' => '423',
      'id_video' => '516',
      'title' => 'Аж мурашки побежали от такой обстановки!😳',
      'play' => '4',
    ),
    array(
      'date' => '2024-09-07',
      'id_player' => '423',
      'id_video' => '516',
      'title' => 'Аж мурашки побежали от такой обстановки!😳',
      'play' => '3',
    ),
    array(
      'date' => '2024-09-08',
      'id_player' => '423',
      'id_video' => '516',
      'title' => 'Аж мурашки побежали от такой обстановки!😳',
      'play' => '2',
    ),
    array(
      'date' => '2024-09-02',
      'id_player' => '732',
      'id_video' => '864',
      'title' => 'Cloudflare Demo Video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-03',
      'id_player' => '732',
      'id_video' => '864',
      'title' => 'Cloudflare Demo Video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-07',
      'id_player' => '732',
      'id_video' => '864',
      'title' => 'Cloudflare Demo Video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-09',
      'id_player' => '732',
      'id_video' => '864',
      'title' => 'Cloudflare Demo Video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-03',
      'id_player' => '14',
      'id_video' => '912',
      'title' => 'Video Ad: Lights out of focus (update test)',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-05',
      'id_player' => '171',
      'id_video' => '912',
      'title' => 'Video Ad: Lights out of focus (update test)',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-07',
      'id_player' => '14',
      'id_video' => '912',
      'title' => 'Video Ad: Lights out of focus (update test)',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-05',
      'id_player' => '286',
      'id_video' => '913',
      'title' => 'Video Ad: A short ocean video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-07',
      'id_player' => '14',
      'id_video' => '913',
      'title' => 'Video Ad: A short ocean video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-09',
      'id_player' => '286',
      'id_video' => '913',
      'title' => 'Video Ad: A short ocean video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-08',
      'id_player' => '812',
      'id_video' => '978',
      'title' => 'Foliovision Promo Video',
      'play' => '1',
    ),
    array(
      'date' => '2024-09-09',
      'id_player' => '812',
      'id_video' => '978',
      'title' => 'Foliovision Promo Video',
      'play' => '2',
    ),
  );

  protected static function getMethod($name) {
    $class = new ReflectionClass('FV_Player_Stats');
    $method = $class->getMethod($name);
    $method->setAccessible(true);
    return $method;
  }
 
  protected function setUp(): void {
    if ( ! defined( 'WP_CONTENT_DIR' ) ) {
      define( 'WP_CONTENT_DIR', '' );
    }

    include_once "../../models/stats.php";
  }

  public function test_get_dates_in_range() {

    // Check is setting the date range works
    $foo = self::getMethod('get_dates_in_range');
    $obj = new FV_Player_Stats();
    $is = $foo->invokeArgs(
      $obj, array(
        'this_week',
        '2024-09-09'
      )
    );

    $should_be = array(
      0 => '2024-09-02',
      1 => '2024-09-03',
      2 => '2024-09-04',
      3 => '2024-09-05',
      4 => '2024-09-06',
      5 => '2024-09-07',
      6 => '2024-09-08',
      7 => '2024-09-09',
    );

    $this->assertEquals( json_encode( $should_be ), json_encode( $is ) );
  }

  public function test_process_graph_data() {

    $foo = self::getMethod('process_graph_data');
    $obj = new FV_Player_Stats();
    $is = $foo->invokeArgs(
      $obj, array(
        $this->raw_db_results_by_videos,
        array(
          0 => 26,
          1 => 516,
          2 => 7,
          3 => 274,
          4 => 864,
          5 => 356,
          6 => 319,
          7 => 912,
          8 => 978,
          9 => 913,
          10 => 0,
        ),
        'this_week',
        'video',
        'play',
        '2024-09-09'
      )
    );

    $should_be = array(
      26 => array(
        '2024-09-02' => array( 'play' => '6' ),
        'name' => 'Foliovision Promo Video',
        '2024-09-03' => array( 'play' => 2 ),
        '2024-09-04' => array( 'play' => 6 ),
        '2024-09-05' => array( 'play' => 7 ),
        '2024-09-06' => array( 'play' => 3 ),
        '2024-09-07' => array( 'play' => 4 ),
        '2024-09-08' => array( 'play' => 5 ),
        '2024-09-09' => array( 'play' => 3 ),
      ),
      516 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Аж мурашки побежали от такой обстановки!😳',
        '2024-09-03' => array( 'play' => 0 ),
        '2024-09-04' => array( 'play' => '4' ),
        '2024-09-05' => array( 'play' => 0 ),
        '2024-09-06' => array( 'play' => 0 ),
        '2024-09-07' => array( 'play' => 3 ),
        '2024-09-08' => array( 'play' => 2 ),
        '2024-09-09' => array( 'play' => 0 ),
      ),
      7 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Swan-Lake-Reloaded',
        '2024-09-03' => array( 'play' => 0 ),
        '2024-09-04' => array( 'play' => '3' ),
        '2024-09-05' => array( 'play' => 0 ),
        '2024-09-06' => array( 'play' => 2 ),
        '2024-09-07' => array( 'play' => 0 ),
        '2024-09-08' => array( 'play' => 0 ),
        '2024-09-09' => array( 'play' => 0 ),
      ),
      274 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Foliovision-Promo-Video-original-maxrate',
        '2024-09-03' => array( 'play' => 0 ),
        '2024-09-04' => array( 'play' => '1' ),
        '2024-09-05' => array( 'play' => 0 ),
        '2024-09-06' => array( 'play' => 3 ),
        '2024-09-07' => array( 'play' => 0 ),
        '2024-09-08' => array( 'play' => 0 ),
        '2024-09-09' => array( 'play' => 0 ),
      ),
      864 => array(
        '2024-09-02' => array( 'play' => '1' ),
        'name' => 'Cloudflare Demo Video',
        '2024-09-03' => array( 'play' => 1 ),
        '2024-09-04' => array( 'play' => 0 ),
        '2024-09-05' => array( 'play' => 0 ),
        '2024-09-06' => array( 'play' => 0 ),
        '2024-09-07' => array( 'play' => 1 ),
        '2024-09-08' => array( 'play' => 0 ),
        '2024-09-09' => array( 'play' => 1 ),
      ),
      356 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Big Buck Bunny (HD) | FULL MOVIE Short film (2008)',
        '2024-09-03' => array( 'play' => 0 ),
        '2024-09-04' => array( 'play' => 0 ),
        '2024-09-05' => array( 'play' => '3' ),
        '2024-09-06' => array( 'play' => 0 ),
        '2024-09-07' => array( 'play' => 0 ),
        '2024-09-08' => array( 'play' => 0 ),
        '2024-09-09' => array( 'play' => 1 ),
      ),
      319 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Video Stabilisation Test: Nikon Z6 vs iPhone 11 Pro vs Canon M6 I',
        '2024-09-03' => array( 'play' => '2' ),
        '2024-09-04' => array( 'play' => 0 ),
        '2024-09-05' => array( 'play' => 2 ),
        '2024-09-06' => array( 'play' => 0 ),
        '2024-09-07' => array( 'play' => 0 ),
        '2024-09-08' => array( 'play' => 0 ),
        '2024-09-09' => array( 'play' => 0 ),
      ),
      912 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Video Ad: Lights out of focus (update test)',
        '2024-09-03' => array( 'play' => '1' ),
        '2024-09-04' => array( 'play' => 0 ),
        '2024-09-05' => array( 'play' => 1 ),
        '2024-09-06' => array( 'play' => 0 ),
        '2024-09-07' => array( 'play' => 1 ),
        '2024-09-08' => array( 'play' => 0 ),
        '2024-09-09' => array( 'play' => 0 ),
      ),
      978 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Foliovision Promo Video',
        '2024-09-03' => array( 'play' => 0 ),
        '2024-09-04' => array( 'play' => 0 ),
        '2024-09-05' => array( 'play' => 0 ),
        '2024-09-06' => array( 'play' => 0 ),
        '2024-09-07' => array( 'play' => 0 ),
        '2024-09-08' => array( 'play' => '1' ),
        '2024-09-09' => array( 'play' => 2 ),
      ),
      913 => array(
        '2024-09-02' => array( 'play' => 0 ),
        'name' => 'Video Ad: A short ocean video',
        '2024-09-03' => array( 'play' => 0 ),
        '2024-09-04' => array( 'play' => 0 ),
        '2024-09-05' => array( 'play' => '1' ),
        '2024-09-06' => array( 'play' => 0 ),
        '2024-09-07' => array( 'play' => 1 ),
        '2024-09-08' => array( 'play' => 0 ),
        '2024-09-09' => array( 'play' => 1 ),
      ),
      'date-labels' => array(
        0 => '2024-09-02',
        1 => '2024-09-03',
        2 => '2024-09-04',
        3 => '2024-09-05',
        4 => '2024-09-06',
        5 => '2024-09-07',
        6 => '2024-09-08',
        7 => '2024-09-09',
      ),
    );

    $this->assertEquals( json_encode( $should_be ), json_encode( $is ) );

    $foo = self::getMethod('process_graph_data');
    $obj = new FV_Player_Stats();
    $is = $foo->invokeArgs(
      $obj, array(
        $this->raw_db_results_by_posts,
        array(
          0 => '5753',
          1 => '8455',
          2 => '142132',
          3 => '8398',
          4 => '8299',
          5 => '33834',
          6 => '55205',
          7 => '118484',
          8 => '23826',
          9 => '8467',
        ),
        'this_week',
        'post',
        'play',
        '2024-09-09'
      )
    );

    $should_be = array(
      5753 => array (
        '2024-09-02' => array (
          'play' => '2',
        ),
        'name' => 'Foliovision',
        '2024-09-03' => array (
          'play' => 1,
        ),
        '2024-09-04' => array (
          'play' => 3,
        ),
        '2024-09-05' => array (
          'play' => 7,
        ),
        '2024-09-06' => array (
          'play' => 2,
        ),
        '2024-09-07' => array (
          'play' => 2,
        ),
        '2024-09-08' => array (
          'play' => 2,
        ),
        '2024-09-09' => array (
          'play' => 3,
        ),
      ),
      8455 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'Pre-roll Custom Video Ads',
        '2024-09-03' => array (
          'play' => 0,
        ),
        '2024-09-04' => array (
          'play' => 0,
        ),
        '2024-09-05' => array (
          'play' => '7',
        ),
        '2024-09-06' => array (
          'play' => 0,
        ),
        '2024-09-07' => array (
          'play' => 0,
        ),
        '2024-09-08' => array (
          'play' => 0,
        ),
        '2024-09-09' => array (
          'play' => 2,
        ),
      ),
      142132 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'Ok.ru Video',
        '2024-09-03' => array (
          'play' => 0,
        ),
        '2024-09-04' => array (
          'play' => '3',
        ),
        '2024-09-05' => array (
          'play' => 0,
        ),
        '2024-09-06' => array (
          'play' => 0,
        ),
        '2024-09-07' => array (
          'play' => 3,
        ),
        '2024-09-08' => array (
          'play' => 2,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      8398 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'VAST Ads',
        '2024-09-03' => array (
          'play' => 0,
        ),
        '2024-09-04' => array (
          'play' => '3',
        ),
        '2024-09-05' => array (
          'play' => 0,
        ),
        '2024-09-06' => array (
          'play' => 3,
        ),
        '2024-09-07' => array (
          'play' => 0,
        ),
        '2024-09-08' => array (
          'play' => 0,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      8299 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'Custom Video Ads in FV Player (pre-roll and post-roll)',
        '2024-09-03' => array (
          'play' => '2',
        ),
        '2024-09-04' => array (
          'play' => 0,
        ),
        '2024-09-05' => array (
          'play' => 0,
        ),
        '2024-09-06' => array (
          'play' => 0,
        ),
        '2024-09-07' => array (
          'play' => 4,
        ),
        '2024-09-08' => array (
          'play' => 0,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      33834 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'Encrypted HLS stream',
        '2024-09-03' => array (
          'play' => 0,
        ),
        '2024-09-04' => array (
          'play' => '1',
        ),
        '2024-09-05' => array (
          'play' => 0,
        ),
        '2024-09-06' => array (
          'play' => 3,
        ),
        '2024-09-07' => array (
          'play' => 0,
        ),
        '2024-09-08' => array (
          'play' => 0,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      55205 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'Playlist Styles',
        '2024-09-03' => array (
          'play' => 0,
        ),
        '2024-09-04' => array (
          'play' => 0,
        ),
        '2024-09-05' => array (
          'play' => '2',
        ),
        '2024-09-06' => array (
          'play' => 0,
        ),
        '2024-09-07' => array (
          'play' => 2,
        ),
        '2024-09-08' => array (
          'play' => 0,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      118484 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'Video Stabilisation Test: iPhone 11 Pro vs Nikon Z6 vs Canon M6',
        '2024-09-03' => array (
          'play' => '2',
        ),
        '2024-09-04' => array (
          'play' => 0,
        ),
        '2024-09-05' => array (
          'play' => 2,
        ),
        '2024-09-06' => array (
          'play' => 0,
        ),
        '2024-09-07' => array (
          'play' => 0,
        ),
        '2024-09-08' => array (
          'play' => 0,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      23826 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'FV Player Pro',
        '2024-09-03' => array (
          'play' => 0,
        ),
        '2024-09-04' => array (
          'play' => '2',
        ),
        '2024-09-05' => array (
          'play' => 0,
        ),
        '2024-09-06' => array (
          'play' => 0,
        ),
        '2024-09-07' => array (
          'play' => 0,
        ),
        '2024-09-08' => array (
          'play' => 1,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      8467 => array (
        '2024-09-02' => array (
          'play' => 0,
        ),
        'name' => 'Using YouTube with FV Player',
        '2024-09-03' => array (
          'play' => '1',
        ),
        '2024-09-04' => array (
          'play' => 2,
        ),
        '2024-09-05' => array (
          'play' => 0,
        ),
        '2024-09-06' => array (
          'play' => 0,
        ),
        '2024-09-07' => array (
          'play' => 0,
        ),
        '2024-09-08' => array (
          'play' => 0,
        ),
        '2024-09-09' => array (
          'play' => 0,
        ),
      ),
      'date-labels' => array (
        0 => '2024-09-02',
        1 => '2024-09-03',
        2 => '2024-09-04',
        3 => '2024-09-05',
        4 => '2024-09-06',
        5 => '2024-09-07',
        6 => '2024-09-08',
        7 => '2024-09-09',
      ),
    );

    $this->assertEquals( json_encode( $should_be ), json_encode( $is ) );
  }
}
