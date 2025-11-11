<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FV_Player_Open_Graph {

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

		if ( !is_singular() || empty( $post ) ) {
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

		// Find video video that is a MP4 without signed URL requirement.
		$video_for_open_graph = false;
		$splash               = false;

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
							! $video_for_open_graph &&
							stripos( $video->getSrc(), '.mp4' ) !== false &&
							apply_filters( 'fv_flowplayer_video_src', $video->getSrc(), array( 'dynamic' => true ) ) === $video->getSrc()
						) {
							$video_for_open_graph = $video;
						}
					}
				}
			}
		}

		if ( $video_for_open_graph ) {

			// Stop FV Simpler SEO X Cards from appearing.
			global $fvseo;
			remove_action('wp_head', array( $fvseo, 'social_meta_tags' ) );

			// Disable Rank Math Open Graph.
			add_filter( 'rank_math/frontend/disable_integration', '__return_true' );

			// Disable SEOPress Open Graph.
			remove_action( 'wp_head', 'seopress_load_social_options', 0 );

			$title       = get_the_title( $post->ID );
			$site_name   = get_bloginfo( 'name' );

			$description = $video_for_open_graph->getMetaValue( 'synopsis', true );

			if ( ! $description ) {
				$description = get_post_meta( $post->ID, '_aioseo_description', true );
			}

			if ( ! $description ) {
				$description = get_post_meta( $post->ID, '_aioseop_description', true );
			}

			if ( ! $description ) {
				$description = wp_strip_all_tags( wp_trim_words( strip_shortcodes( wp_strip_all_tags( $post->post_content ) ), 20, '...') ); 
			}

			$video_src      = $video_for_open_graph->getSrc();
			$video_width    = $video_for_open_graph->getWidth();
			$video_height   = $video_for_open_graph->getHeight();
			$video_duration = $video_for_open_graph->getDuration();

			// Prepare the HTML.
			$this->tags   = array();
			$this->tags[] = '<meta property="og:title" content="' . esc_attr( $title ) . '" />';
			$this->tags[] = '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />';
			$this->tags[] = '<meta property="og:url" content="' . esc_url( get_permalink( $post->ID ) ) . '" />';
			$this->tags[] = '<meta property="og:type" content="video.other" />';
			if ( $description ) {
				$this->tags[] = '<meta property="og:description" content="' . esc_attr( $description ) . '" />';
			}

			if ( ! $splash ) {
				$splash = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
			}

			if ( $splash ) {
				$this->tags[] = '<meta property="og:image" content="' . esc_url( $splash ) . '" />';
			}

			$this->tags[] = '<meta property="og:video" content="' . esc_url( $video_src ) . '">';
			$this->tags[] = '<meta property="og:video:url" content="' . esc_url( $video_src ) . '">';
			$this->tags[] = '<meta property="og:video:secure_url" content="' . esc_url( $video_src ) . '">';

			$this->tags[] = '<meta property="og:video:type" content="video/mp4">';
			if ( $video_width ) {
				$this->tags[] = '<meta property="og:video:width" content="' . esc_attr( $video_width ) . '">';
			}
			if ( $video_height ) {
				$this->tags[] = '<meta property="og:video:height" content="' . esc_attr( $video_height) . '">';
			}
			if ( $video_duration ) {
				$this->tags[] = '<meta property="og:video:duration" content="' . esc_attr( $video_duration ) . '">';
			}
		}
	}

	public function wp_head() {
		if ( $this->tags ) {
			echo implode( "\n", $this->tags ) . "\n";
		}
	}
}

new FV_Player_Open_Graph();