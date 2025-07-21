<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Tutor_LMS {

  function __construct() {
    add_action( 'plugins_loaded', array( $this, 'loader' ), 11 );
  }

  function loader() {
    // Capture the shortcode output and setup capture for the end of the shortcode
    add_action( 'tutor_lesson/single/before/video/shortcode', array( $this, 'shortcode_before' ), 0 );
  }

  public function shortcode_before() {
    ob_start();

    // Capture the end of the shortcode output and remove the classes
    add_action( 'tutor_lesson/single/after/video/shortcode', array( $this, 'shortcode_after' ), 1000 );
  }

  public function shortcode_after() {

    $html = ob_get_clean();

    /**
     * We remove the classes tutor-ratio tutor-ratio-16x9 as they force their children elements to be 16:9.
     * That way FV Player with playlist items does not appear properly.
     */
    if ( stripos( $html, 'freedomplayer' ) !== false ) {
      $html = str_replace( '<div class="tutor-ratio tutor-ratio-16x9">', '<div class="fv-player-tutor-lms" data-original-class="tutor-ratio tutor-ratio-16x9">', $html );
    }

    // Output the modified HTML
    echo $html;
  }

}

new FV_Player_Tutor_LMS;
