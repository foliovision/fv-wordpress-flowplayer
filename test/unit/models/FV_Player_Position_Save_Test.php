<?php
use PHPUnit\Framework\TestCase;

final class FV_Player_Position_Save_Test extends TestCase {

	protected function setUp(): void {
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', '' );
		}
	
		include_once "../../models/player-position-save.php";
	}

	public function test_get_extensionless_file_name() {
		$obj = new FV_Player_Position_Save();
		$is = $obj->get_extensionless_file_name( 'https://example.com/video.mp4' );
		$this->assertEquals( 'video', $is );

		$is = $obj->get_extensionless_file_name( 'https://example.com/video/index.m3u8' );
		$this->assertEquals( 'video', $is );
	}
}