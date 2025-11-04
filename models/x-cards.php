<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FV_Player_X_Cards {

	private $tags = array();

	public function __construct() {
		/**
		 * Preparing the tags at wp action with priority 9, to make sure it's before
		 * FV Simpler SEO Open Graph and RankMath\Frontend\Frontend::hooks().
		 */
		add_action( 'wp', array( $this, 'find_suitable_video' ), 9 );

		add_action( 'wp_head', array( $this, 'wp_head' ) );
	}

	public function find_suitable_video() {
		global $post;

		if ( !is_singular() ) {
			return;
		}

		$shortcodes = array();

		// Get FV Player from post content.
		if( preg_match_all('~\[fvplayer.*?\]~', $post->post_content, $shortcodes_post_content ) ) {
			$shortcodes = array_merge( $shortcodes, $shortcodes_post_content[0] );
		}

		// Get FV Player from post meta.
		if( preg_match_all('~\[fvplayer.*?\]~', implode( array_map( 'implode', get_post_custom( $post->ID)  ) ), $shortcodes_post_meta ) ) {
			$shortcodes = array_merge( $shortcodes, $shortcodes_post_meta[0] );
		}

		// Find first player and its video.
		$player_for_x_card = false;
		$video_for_x_card  = false;
		$splash            = false;

		foreach( $shortcodes as $shortcode ) {
			$atts = shortcode_parse_atts( trim( $shortcode, ']' ) );
			if ( ! empty( $atts['id'] ) ) {
				$player = new FV_Player_Db_Player( $atts['id'] );
				if ( $player->getIsValid() ) {
					$videos = $player->getVideos();

					// Try the trailer video first.
					$trailer_video_id = $player->getTrailerVideoId();
					if ( $trailer_video_id ) {
						$trailer_video = new FV_Player_Db_Video( $trailer_video_id );
						if ( $trailer_video->getIsValid() ) {
							if ( ! $videos ) { 
								$videos = array();
							}
							$videos = array_merge( array( $trailer_video ), $videos );
						}
					}

					foreach( $videos as $video ) {

						// Take first splash image with URL signature.
						if ( ! $splash && $video->getSplash() && apply_filters( 'fv_flowplayer_resource', $video->getSplash() ) === $video->getSplash() ) {
							$splash = $video->getSplash();
						}

						// Take first MP4 video without URL signature.
						if (
							! $video_for_x_card &&
							stripos( $video->getSrc(), '.mp4' ) !== false &&
							apply_filters( 'fv_flowplayer_video_src', $video->getSrc(), array( 'dynamic' => true ) ) === $video->getSrc()
						) {
							$player_for_x_card = $player;
							$video_for_x_card  = $video;
						}
					}
				}
			}
		}

		if ( $player_for_x_card && $video_for_x_card ) {

			// Stop FV Simpler SEO X Cards from appearing.
			global $fvseo;
			remove_action('wp_head', array( $fvseo, 'social_meta_tags' ) );

			// Disable Rank Math X Cards.
			add_filter( 'rank_math/frontend/disable_integration', '__return_true' );

			// Disable SEOPress Open Graph.
			remove_action( 'wp_head', 'seopress_load_social_options', 0 );

			$title  = get_the_title( $post->ID );

			$player_url = user_trailingslashit( trailingslashit( get_permalink( $post->ID ) ) . 'fvp-' . $player_for_x_card->getId() );

			$video_width = 640;

			$aspect_ratio = $video_for_x_card->getAspectRatio();
			if ( ! $aspect_ratio ) {
				$aspect_ratio = 9/16;
			}

			$video_height = round( $video_width * $aspect_ratio );

			// Output the HTML.
			$this->tags   = array();
			$this->tags[] = '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />';
			$this->tags[] = '<meta name="twitter:card" content="player" />';
			$this->tags[] = '<meta name="twitter:image" content="' . esc_url( $this->get_splash( $video_for_x_card ) ) . '" />';
			$this->tags[] = '<meta name="twitter:description" content="' . esc_attr( $this->get_description( $video_for_x_card ) ) . '" />';
			$this->tags[] = '<meta name="twitter:url" content="' . esc_url( get_permalink( $post->ID ) ) . '">';
			$this->tags[] = '<meta name="twitter:player" content="' . esc_url( $player_url ) . '">';
			$this->tags[] = '<meta name="twitter:player:stream" content="' . esc_url( $video_for_x_card->getSrc() ) . '">';

			if ( $video_width ) {
				$this->tags[] = '<meta name="twitter:player:width" content="' . esc_attr( $video_width ) . '">';
			}

			if ( $video_height ) {
				$this->tags[] = '<meta name="twitter:player:height" content="' . esc_attr( $video_height) . '">';
			}
		}
	}

	/**
	 * Get description for the video with fall back to post meta and post content part.
	 *
	 * @param FV_Player_Db_Video $video
	 * @return string
	 */
	private function get_description( $video ) {
		global $post;
	
		$description = $video->getMetaValue( 'synopsis', true );

		if ( ! $description ) {
			$description = get_post_meta( $post->ID, '_aioseo_description', true );
		}

		if ( ! $description ) {
			$description = get_post_meta( $post->ID, '_aioseop_description', true );
		}

		if ( ! $description ) {
			$description = wp_strip_all_tags( wp_trim_words( strip_shortcodes( wp_strip_all_tags( $post->post_content ) ), 20, '...') ); 
		}

		return $description;
	}

	/**
	 * Get splash for the video with fall back to post thumbnail.
	 * Splash image has to come from the website domain and must not require URL signature.
	 * 
	 * @param FV_Player_Db_Video $video_for_x_card
	 * @return string
	 */
	private function get_splash( $video ) {
		global $post;

		$splash = $video->getSplash();

		$home_url_host = wp_parse_url( home_url(), PHP_URL_HOST );

		if (
			! $splash ||
			stripos( $splash, '//' . $home_url_host . '/' ) === 0 &&
			stripos( $splash, '.' . $home_url_host . '/' ) === 0 ||
			apply_filters( 'fv_flowplayer_resource', $splash ) !== $splash
		) {
			$splash = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
		}

		return $splash;
	}

	public function wp_head() {
		if ( $this->tags ) {
			echo implode( "\n", $this->tags ) . "\n";
		}
	}
}

new FV_Player_X_Cards();