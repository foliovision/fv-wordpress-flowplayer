<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FV_Player_X_Cards {

	private $tags = array();

	public function __construct() {

		// Only do this if Open Graph/X Cards setting is enabled.
		global $fv_fp;
		if ( ! empty( $fv_fp ) && $fv_fp->_get_option( array( 'integrations', 'open_graph' ) ) ) {
		/**
		 * Preparing the tags at wp action with priority 9, to make sure it's before
		 * FV Simpler SEO Open Graph and RankMath\Frontend\Frontend::hooks().
		 */
		add_action( 'wp', array( $this, 'find_suitable_video' ), 9 );

		add_action( 'wp_head', array( $this, 'wp_head' ) );
		}

		/**
		 * Make sure we do not generate all the possible image sizes for sharing images.
		 *
		 * We need to always do this, even if Open Graph/X Cards setting is disabled.
		 * As some images might be already there before user disabled the setting.
		 */
		add_filter( 'intermediate_image_sizes_advanced', array( $this, 'limit_image_sizes_for_sharing_images' ), 999, 3 );
	}

	/**
	 * Process uploaded images: create a 1280px wide copy with play button overlay in fv-player-video-sharing folder
	 *
	 * @param int $attachment_id The attachment ID.
	 *
	 * @return int|false Attachment ID on success, false on failure.
	 */
	public static function add_play_icon_to_splash_image( $attachment_id_or_url ) {

		// Get upload directory
		$upload_dir = wp_upload_dir();
		if ( $upload_dir['error'] ) {
			return false;
		}

		$filename = false;

		// Download the image if it's an URL.
		if ( sanitize_url( $attachment_id_or_url ) && in_array( strtolower( pathinfo( wp_parse_url( $attachment_id_or_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ), array( 'jpg', 'jpeg', 'png', 'gif' ) ) ) {

			// Let FV Player add URL signature if needed.
			$download_url = apply_filters( 'fv_flowplayer_resource', $attachment_id_or_url );

			// Download the image to a temporary file
			if ( ! function_exists( 'download_url' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$temp_file = download_url( $download_url );
			if ( is_wp_error( $temp_file ) ) {
				return false;
			}

			$path_no_query_string = wp_parse_url( $attachment_id_or_url, PHP_URL_PATH );
			$folder               = dirname( $path_no_query_string );
			$filename             = basename( $path_no_query_string );
			$extension            = pathinfo( $filename, PATHINFO_EXTENSION );

			// Use the folder name if the filename starts with something too generic and we have a real folder.
			if ( stripos( $filename, 'thumbnail-' ) === 0 && "/" !== $folder ) {
				$filename = basename( dirname( $path_no_query_string ) ) . '.' . $extension;
			}

			// Prepare the splash image (copy/resize to 1280px width)
			$filename = self::copy_and_resize_splash_image( $temp_file, array( 'target_filename' => $filename ) );
			
			// Clean up temporary file
			@unlink( $temp_file );

			if ( ! $filename ) {
				return false;
			}

		// Copy existing image from Media Library if it's an attachment ID.
		} else if ( is_numeric( $attachment_id_or_url ) ) {

			// Check if this is an image
			$mime_type = get_post_mime_type( $attachment_id_or_url );
			if ( ! $mime_type || strpos( $mime_type, 'image/' ) !== 0 ) {
				return;
			}
		
			// Get the attachment file path
			$file_path = get_attached_file( $attachment_id_or_url );
			if ( ! $file_path ) {
				return;
			}

			// Prepare the splash image (copy/resize)
			$filename = self::copy_and_resize_splash_image( $file_path );
			if ( ! $filename ) {
				return;
			}
		}

		$target_path = trailingslashit( $upload_dir['basedir'] ) . 'fv-player-video-sharing/' . $filename;

		// Add play button overlay
		// Get play button image path
		$play_button_path = dirname( dirname( __FILE__ ) ) . '/images/playbutton-to-add-cropped-darkergrey-03.png';
		if ( ! file_exists( $play_button_path ) ) {
			return false;
		}

		// Try Imagick first, fall back to GD
		if ( class_exists( 'Imagick' ) ) {
			$result = self::add_play_icon_to_splash_image_imagick( $target_path, $play_button_path );
			if ( ! $result ) {
				return false;
			}
		}

		// Fall back to GD
		if ( ! self::add_play_icon_to_splash_image_gd( $target_path, $play_button_path ) ) {
			return false;
		}

		/**
		 * Insert the new image into the WP Media Library.
		 */
		$attachment_data = array(
			'post_mime_type' => 'image/jpeg', // TODO: Get the correct MIME type from the image.
			'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Create relative path from uploads directory
		$relative_path = 'fv-player-video-sharing/' . $filename;

		// Insert the attachment
		$new_attachment_id = wp_insert_attachment( $attachment_data, $relative_path );

		if ( is_wp_error( $new_attachment_id ) || ! $new_attachment_id ) {
			return false;
		}

		// Generate attachment metadata
		$attach_data = wp_generate_attachment_metadata( $new_attachment_id, $target_path );
		wp_update_attachment_metadata( $new_attachment_id, $attach_data );

		return $new_attachment_id;
	}

	/**
	 * Add play button overlay to an image using Imagick
	 *
	 * @param string $image_path Path to the image file.
	 * @param string $play_button_path Path to the play button image.
	 * @return bool True on success, false on failure.
	 */
	private static function add_play_icon_to_splash_image_imagick( $image_path, $play_button_path ) {
		if ( ! class_exists( 'Imagick' ) ) {
			return false;
		}

		try {
			// Load main image
			/** @var Imagick $main_image */
			$main_image = new Imagick( $image_path );
			/** @var ImagickPixel $transparent_pixel */
			$transparent_pixel = new ImagickPixel( 'transparent' );
			$main_image->setImageBackgroundColor( $transparent_pixel );

			// Load play button
			/** @var Imagick $play_button */
			$play_button = new Imagick( $play_button_path );
			$play_button->setImageBackgroundColor( $transparent_pixel );

			// Get dimensions
			$main_width = $main_image->getImageWidth();
			$main_height = $main_image->getImageHeight();
			$play_button_width = $play_button->getImageWidth();
			$play_button_height = $play_button->getImageHeight();

			// Determine if image is horizontal or vertical and calculate max play button size
			$is_horizontal = $main_width > $main_height;
			$max_size = $is_horizontal ? ( $main_height / 3 ) : ( $main_width / 4 );

			// Calculate if play button needs to be resized
			$play_button_max_dimension = max( $play_button_width, $play_button_height );
			$needs_resize = $play_button_max_dimension > $max_size;

			if ( $needs_resize ) {
				// Calculate new dimensions maintaining aspect ratio
				$scale = $max_size / $play_button_max_dimension;
				$final_play_button_width = round( $play_button_width * $scale );
				$final_play_button_height = round( $play_button_height * $scale );

				$play_button->resizeImage( $final_play_button_width, $final_play_button_height, Imagick::FILTER_LANCZOS, 1, true );
			} else {
				$final_play_button_width = $play_button_width;
				$final_play_button_height = $play_button_height;
			}

			// Calculate center position for play button
			$play_button_x = round( ( $main_width - $final_play_button_width ) / 2 );
			$play_button_y = round( ( $main_height - $final_play_button_height ) / 2 );

			// Composite play button over the main image
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$main_image->compositeImage( $play_button, Imagick::COMPOSITE_OVER, $play_button_x, $play_button_y );

			// Get image format
			$image_info = getimagesize( $image_path );

			// Set quality for JPEG
			if ( IMAGETYPE_JPEG === $image_info[2] ) {
				// Taken from wp-includes/class-wp-image-editor.php
				$default_quality = 82;
				$quality = apply_filters( 'wp_editor_set_quality', $default_quality, 'image/jpeg', array( 'width' => $main_width, 'height' => $main_height ) );
				$quality = apply_filters( 'jpeg_quality', $quality, 'image_resize' );
				$main_image->setImageCompressionQuality( $quality );
				$main_image->setCompressionQuality( $quality );
				$main_image->setImageCompression( imagick::COMPRESSION_JPEG );
			}

			// Save the image
			$main_image->writeImage( $image_path );

			// Free memory
			$main_image->clear();
			$main_image->destroy();
			$play_button->clear();
			$play_button->destroy();

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Add play button overlay to an image using GD
	 *
	 * @param string $image_path Path to the image file.
	 * @param string $play_button_path Path to the play button image.
	 * @return bool True on success, false on failure.
	 */
	private static function add_play_icon_to_splash_image_gd( $image_path, $play_button_path ) {
		// Get image info to determine type
		$image_info = getimagesize( $image_path );
		if ( ! $image_info ) {
			return false;
		}

		// Load the main image using GD
		switch ( $image_info[2] ) {
			case IMAGETYPE_JPEG:
				$main_image = imagecreatefromjpeg( $image_path );
				break;
			case IMAGETYPE_PNG:
				$main_image = imagecreatefrompng( $image_path );
				break;
			case IMAGETYPE_GIF:
				$main_image = imagecreatefromgif( $image_path );
				break;
			default:
				return false;
		}

		if ( ! $main_image ) {
			return false;
		}

		// Load play button overlay
		$play_button_image = imagecreatefrompng( $play_button_path );
		if ( ! $play_button_image ) {
			imagedestroy( $main_image );
			return false;
		}

		// Enable alpha blending for transparency
		imagealphablending( $main_image, true );
		imagesavealpha( $main_image, true );
		imagealphablending( $play_button_image, false );
		imagesavealpha( $play_button_image, true );

		// Get dimensions
		$main_width = imagesx( $main_image );
		$main_height = imagesy( $main_image );
		$play_button_width = imagesx( $play_button_image );
		$play_button_height = imagesy( $play_button_image );

		// Determine if image is horizontal or vertical and calculate max play button size
		$is_horizontal = $main_width > $main_height;
		$max_size = $is_horizontal ? ( $main_height / 3 ) : ( $main_width / 4 );

		// Calculate if play button needs to be resized
		$play_button_max_dimension = max( $play_button_width, $play_button_height );
		$needs_resize = $play_button_max_dimension > $max_size;

		$final_play_button_width = $play_button_width;
		$final_play_button_height = $play_button_height;
		$play_button_to_use = $play_button_image;

		if ( $needs_resize ) {
			// Calculate new dimensions maintaining aspect ratio
			$scale = $max_size / $play_button_max_dimension;
			$final_play_button_width = round( $play_button_width * $scale );
			$final_play_button_height = round( $play_button_height * $scale );

			// Create resized play button image
			$resized_play_button = imagecreatetruecolor( $final_play_button_width, $final_play_button_height );
			imagealphablending( $resized_play_button, false );
			imagesavealpha( $resized_play_button, true );
			$transparent = imagecolorallocatealpha( $resized_play_button, 0, 0, 0, 127 );
			imagefill( $resized_play_button, 0, 0, $transparent );
			imagealphablending( $resized_play_button, true );

			// Resize the play button
			imagecopyresampled(
				$resized_play_button,
				$play_button_image,
				0, 0, 0, 0,
				$final_play_button_width,
				$final_play_button_height,
				$play_button_width,
				$play_button_height
			);

			$play_button_to_use = $resized_play_button;
		}

		// Calculate center position for play button
		$play_button_x = round( ( $main_width - $final_play_button_width ) / 2 );
		$play_button_y = round( ( $main_height - $final_play_button_height ) / 2 );

		// Composite play button over the main image with full opacity
		imagecopy( $main_image, $play_button_to_use, $play_button_x, $play_button_y, 0, 0, $final_play_button_width, $final_play_button_height );

		// Free resized play button if it was created
		if ( $needs_resize ) {
			imagedestroy( $resized_play_button );
		}

		// Save the final image with play button overlay
		switch ( $image_info[2] ) {
			case IMAGETYPE_JPEG:
				// Taken from wp-includes/class-wp-image-editor.php
				$default_quality = 82;
				$quality = apply_filters( 'wp_editor_set_quality', $default_quality, 'image/jpeg', array( 'width' => $main_width, 'height' => $main_height ) );
				$quality = apply_filters( 'jpeg_quality', $quality, 'image_resize' );

				imagejpeg( $main_image, $image_path, $quality );
				break;
			case IMAGETYPE_PNG:
				imagepng( $main_image, $image_path );
				break;
			case IMAGETYPE_GIF:
				imagegif( $main_image, $image_path );
				break;
		}

		// Free memory
		imagedestroy( $main_image );
		imagedestroy( $play_button_image );

		return true;
	}

	/**
	 * Prepare splash image: copy to {wp_uploads dir}/fv-player-video-sharing and resize to fit into the given size.
	 *
	 * @param string $file_path Source image file path.
	 * @param array  $args      Arguments array.
	 *                          'target_filename' => 'custom-filename-here.jpg' (optional),
	 *                          'fit_to_size'   => 1280,
	 * @return string|false Target file path on success, false on failure.
	 */
	private static function copy_and_resize_splash_image( $file_path, $args = array() ) {
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return false;
		}

		$args = wp_parse_args( $args, array(
			'target_filename' => false,
			'fit_to_size'     => 1280,
		) );

		$target_filename = $args['target_filename'];
		$fit_to_size     = absint( $args['fit_to_size'] );

		// Get upload directory
		$upload_dir = wp_upload_dir();
		if ( $upload_dir['error'] ) {
			return false;
		}

		// Create the fv-player-video-sharing directory if it doesn't exist
		$target_dir = trailingslashit( $upload_dir['basedir'] ) . 'fv-player-video-sharing';
		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		// Get image editor
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$image_editor = wp_get_image_editor( $file_path );
		if ( is_wp_error( $image_editor ) ) {
			return false;
		}

		// Get original image dimensions
		$image_size = $image_editor->get_size();
		if ( ! $image_size || empty( $image_size['width'] ) ) {
			return false;
		}

		// Generate filename for the processed image
		if ( $target_filename ) {
			$file_info = pathinfo( $target_filename );

		} else {
			$file_info = pathinfo( $file_path );
		}

		// Generate unique filename if file exists
		$ext      = isset( $file_info['extension'] ) ? '.' . $file_info['extension'] : '';
		$counter  = 1;
		$filename = $file_info['basename'];

		while ( file_exists( trailingslashit( $target_dir ) . $filename ) ) {
			$counter++;
			$filename = $file_info['filename'] . '-' . $counter . $ext;
		}

		$target_path = trailingslashit( $target_dir ) . $filename;

		// Image is already smaller or equal, just copy it
		if ( $image_size['width'] <= $fit_to_size && $image_size['height'] <= $fit_to_size ) {
			if ( ! copy( $file_path, $target_path ) ) {
				return false;
			}

		} else {
			// Calculate proportional height or width, depending on the image aspect ratio.
			$ratio = $image_size['height'] / $image_size['width'];

			if ( $ratio > 1 ) {
				$target_height = $fit_to_size;
				$target_width = round( $target_height / $ratio );

			} else {
				$target_width = $fit_to_size;
				$target_height = round( $fit_to_size * $ratio );	
			}

			$target_height = round( $fit_to_size * $ratio );

			// Resize the image
			$resized = $image_editor->resize( $target_width, $target_height, false );
			if ( is_wp_error( $resized ) ) {
				return false;
			}

			// Save the resized image
			$saved = $image_editor->save( $target_path );
			if ( is_wp_error( $saved ) ) {
				return false;
			}
		}

		return $filename;
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

		foreach( $shortcodes as $shortcode ) {
			$atts = shortcode_parse_atts( trim( $shortcode, ']' ) );
			if ( ! empty( $atts['id'] ) ) {
				$player = new FV_Player_Db_Player( $atts['id'] );
				if ( $player->getIsValid() ) {
					$player_for_x_card = $player;

					$videos = $player->getVideos();
					foreach( $videos as $video ) {
						$video_for_x_card = $video;
						break;
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

			// Output the HTML.
			$this->tags   = array();
			$this->tags[] = '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />';
			$this->tags[] = '<meta name="twitter:card" content="summary_large_image" />';
			$this->tags[] = '<meta name="twitter:image" content="' . esc_url( $this->get_splash( $video_for_x_card ) ) . '" />';
			$this->tags[] = '<meta name="twitter:description" content="' . esc_attr( $this->get_description( $video_for_x_card ) ) . '" />';
			$this->tags[] = '<meta name="twitter:url" content="' . esc_url( get_permalink( $post->ID ) ) . '">';
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

		$sharing_image_id = $video->getSharingImageId();
		if ( $sharing_image_id ) {
			return wp_get_attachment_image_url( $sharing_image_id, 'full' );
		}

		$splash = $video->getSplash();

		$home_url_host = wp_parse_url( home_url(), PHP_URL_HOST );

		if (
			! $splash ||
			stripos( $splash, '//' . $home_url_host . '/' ) === false &&
			stripos( $splash, '.' . $home_url_host . '/' ) === false ||
			apply_filters( 'fv_flowplayer_resource', $splash ) !== $splash
		) {
			$splash = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
		}

		return $splash;
	}

	/**
	 * Only create the thumbnail size for FV Player X Card sharing images.
	 *
	 * Do not create all the possible image sizes for sharing images as they will never be used.
	 */
	public function limit_image_sizes_for_sharing_images( $sizes, $image_meta, $attachment_id ) {

		// Is it one of the FV Player X Card sharing images?
		if ( ! empty( $image_meta['file'] ) && stripos( $image_meta['file'], 'fv-player-video-sharing/' ) === 0 ) {
			return array(
				'thumbnail' => $sizes['thumbnail'],
			);
		}
	
		return $sizes;
	}

	public function wp_head() {
		if ( $this->tags ) {
			echo implode( "\n", $this->tags ) . "\n";
		}
	}
}

new FV_Player_X_Cards();