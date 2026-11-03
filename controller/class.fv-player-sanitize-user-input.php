<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FV_Player_Sanitize_User_Input {

	/**
	 * Singleton instance.
	 *
	 * @var FV_Player_Sanitize_User_Input
	 */
	private static $instance;

	private $untrusted_content_counter = 0;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new FV_Player_Sanitize_User_Input();
		}
		return self::$instance;
	}

	private function __construct() {
		/*
		 * Comments, bbPress topics and replies and BuddyPress activity are untrusted
		 * input. Mark the whole filter run so that flowplayer_content_handle() can
		 * restrict the shortcode attributes, no matter who runs do_shortcode() on it.
		 */
		add_filter( 'comment_text', array( $this, 'untrusted_content_start' ), PHP_INT_MIN );
		add_filter( 'bp_get_activity_content_body', array( $this, 'untrusted_content_start' ), PHP_INT_MIN );
		add_filter( 'bbp_get_topic_content', array( $this, 'untrusted_content_start' ), PHP_INT_MIN );
		add_filter( 'bbp_get_reply_content', array( $this, 'untrusted_content_start' ), PHP_INT_MIN );

		add_filter( 'comment_text', array( $this, 'untrusted_content_end' ), PHP_INT_MAX );
		add_filter( 'bp_get_activity_content_body', array( $this, 'untrusted_content_end' ), PHP_INT_MAX );
		add_filter( 'bbp_get_topic_content', array( $this, 'untrusted_content_end' ), PHP_INT_MAX );
		add_filter( 'bbp_get_reply_content', array( $this, 'untrusted_content_end' ), PHP_INT_MAX );
	}

	/**
	 * Untrusted content processing is starting. FV Player shortcodes in it can
	 * only use the attributes given by get_untrusted_shortcode_attributes(),
	 * see flowplayer_content_handle().
	 *
	 * Uses a counter as these filters can nest, like comment_text being used in a
	 * shortcode in post content.
	 *
	 * @param string $content The content, returned unchanged.
	 * @return string
	 */
	public function untrusted_content_start( $content ) {
		$this->untrusted_content_counter++;
		return $content;
	}

	/**
	 * Untrusted content processing is over.
	 *
	 * @param string $content The content, returned unchanged.
	 * @return string
	 */
	public function untrusted_content_end( $content ) {
		$this->untrusted_content_counter = max( 0, intval( $this->untrusted_content_counter ) - 1 );
		return $content;
	}


	/**
 * Shortcode attributes which can be used by untrusted users, like in comments
 * or in the post and profile videos.
 *
 * These are escaped on output.
 *
 * @param array $extra_attributes Attributes to allow on top of the basic ones.
 * @return array The attribute names.
 */
public function get_untrusted_shortcode_attributes( $extra_attributes = array() ) {
  return array_merge( array( 'src', 'splash', 'caption', 'title' ), $extra_attributes );
}

/**
 * Are we processing untrusted content, like comments?
 *
 * @return bool
 */
public function is_untrusted_content() {
  return intval( $this->untrusted_content_counter ) > 0;
}

/**
 * Restrict the attributes of FV Player shortcodes in untrusted text, like the
 * post and profile videos. Their shortcodes can be shown with just
 * do_shortcode(), so the text itself must be safe.
 *
 * The attributes are parsed with shortcode_parse_atts(), just like
 * do_shortcode() does, only the allowed ones are kept and the shortcode is
 * written again. So no matter how an attribute is written, it cannot get
 * through. This also removes the positional (unnamed) attributes and the
 * shortcode content, which FV Player does not use.
 *
 * @param string $text             Text with FV Player shortcodes.
 * @param array  $extra_attributes Attributes to allow on top of the basic ones,
 *                                 see get_untrusted_shortcode_attributes().
 * @return string The text with the FV Player shortcodes restricted.
 */
public function sanitize_untrusted_shortcodes( $text, $extra_attributes = array() ) {
  if ( ! is_string( $text ) || ( false === stripos( $text, '[fvplayer' ) && false === stripos( $text, '[flowplayer' ) ) ) {
    return $text;
  }

  if ( ! preg_match_all( '/' . get_shortcode_regex( array( 'fvplayer', 'flowplayer' ) ) . '/', $text, $matches, PREG_SET_ORDER ) ) {
    return $text;
  }

  $replacements = array();
  foreach ( $matches as $match ) {
    $replacements[ $match[0] ] = $this->sanitize_untrusted_shortcodes_worker( $match, $extra_attributes );
  }

  // strtr() replaces the longest match first and never replaces the replaced
  // text again, so [fvplayer] cannot change the inside of an escaped
  // [[fvplayer]] which is the same otherwise
  return strtr( $text, $replacements );
}

/**
 * Restrict the attributes of a single FV Player shortcode, see sanitize_untrusted_shortcodes().
 *
 * @param array $match            Shortcode match of get_shortcode_regex().
 * @param array $extra_attributes Attributes to allow on top of the basic ones.
 * @return string The shortcode with only the allowed attributes.
 */
private function sanitize_untrusted_shortcodes_worker( $match, $extra_attributes = array() ) {
  // Escaped shortcode like [[fvplayer]] is not run by do_shortcode(), keep it
  if ( '[' === $match[1] && ']' === $match[6] ) {
    return $match[0];
  }

  $atts = array_intersect_key(
    (array) shortcode_parse_atts( $match[3] ),
    array_flip( $this->get_untrusted_shortcode_attributes( $extra_attributes ) )
  );

  $shortcode = '[' . $match[2];
  foreach ( $atts as $name => $value ) {
    $shortcode .= ' ' . $name . '="' . $this->shortcode_attribute_encode( $value ) . '"';
  }

  return $shortcode . ']';
}

/**
 * Encode shortcode attribute value to be put in double quotes.
 *
 * shortcode_parse_atts() decodes the value with stripcslashes(), so the
 * shortcode gives back exactly the same value. Quotes and square brackets
 * would break the shortcode and < and > are encoded to not put HTML tags into
 * the stored text.
 *
 * @param string $value The attribute value.
 * @return string The encoded value.
 */
public function shortcode_attribute_encode( $value ) {
  return str_replace(
    array( '\\', '"', '[', ']', '<', '>' ),
    array( '\\\\', '\x22', '\x5b', '\x5d', '\x3c', '\x3e' ),
    $value
  );
}

}

function FV_Player_Sanitize_User_Input() {
	return FV_Player_Sanitize_User_Input::get_instance();
}

FV_Player_Sanitize_User_Input();