<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * FV Player Elementor video widget.
 *
 * Elementor widget that displays FV Player.
 *
 * @since 1.0.0
 * @disregard P1009 Undefined type
 */
class FV_Player_Elementor_Widget extends Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve video widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'fv_player';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve video widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'FV Player', 'elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve video widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-youtube';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the video widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'basic' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'fv', 'video', 'player', 'embed', 'youtube', 'vimeo' ];
	}

	/**
	 * Special Elementor feature to reduce the amount of HTML elements.
	 * Docs: https://developers.elementor.com/docs/widgets/widget-inner-wrapper/
	 */
	public function has_widget_inner_wrapper(): bool {
		/** @disregard P1009 Undefined type */
		/** @disregard P1014 Undefined property */
		return ! Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	/**
	 * Register video widget controls.
	 *
	 * Note that the follwoing fields are not saved in the Elementor DB but in the FV Player DB:
	 * - source_url
	 * - splash_url
	 * - title
	 * - timeline_previews
	 * - hls_hlskey
	 *
	 * The field values are populated using JavaScript with Ajax with action=fv_player_guttenberg_attributes_load
	 * The field values are removed from Elementor data using PHP in fv_player_editor_elementor_widget_remove_settings().
	 *
	 * @access protected
	 */
	protected function register_controls() {
		/** @disregard P1013 Undefined method */
		$this->start_controls_section(
			'section_video',
			[
				'label' => esc_html__( 'Video', 'elementor' ),
			]
		);

		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'source_url',
			array(
				'label' => esc_html__( 'Source URL', 'fv-player' ),
				'type' => Elementor\Controls_Manager::URL,
				'options' => false,
				'ai' => array(
					'active' => false,
				),
			)
		);

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'_media_library_source_url',
			array(
				'type'        => Elementor\Controls_Manager::BUTTON,
				'text'        => esc_html__( 'Select Media', 'fv-player' ),
				'event'       => 'fv-player-elementor-pick-source_url',
				'button_type' => 'default',
				'show_label'  => false,
			)
		);

		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'splash_url',
			array(
				'label' => esc_html__( 'Splash URL', 'fv-player' ),
				'type' => Elementor\Controls_Manager::URL,
				'options' => false,
				'ai' => array(
					'active' => false,
				),
			)
		);

		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'splash_attachment_id',
			array(
				'type' => Elementor\Controls_Manager::HIDDEN,
				'default' => '',
			)
		);

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'_media_library_splash_url',
			array(
				'type'        => Elementor\Controls_Manager::BUTTON,
				'text'        => esc_html__( 'Select Media', 'fv-player' ),
				'event'       => 'fv-player-elementor-pick-splash_url',
				'button_type' => 'default',
				'show_label'  => false,
			)
		);

		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'title',
			array(
				'label' => esc_html__( 'Video Title', 'fv-player' ),
				'label_block' => true,
				'type' => Elementor\Controls_Manager::TEXT,
				'ai' => array(
					'active' => false,
				),
			)
		);

		// Deal with "Timeline Previews" field visibility.
		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'_show_timeline_previews',
			array(
				'type' => Elementor\Controls_Manager::HIDDEN,
				'default' => '',
			)
		);

		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'timeline_previews',
			array(
				'label' => esc_html__( 'Timeline Previews', 'fv-player' ),
				'type' => Elementor\Controls_Manager::URL,
				'options' => false,
				'ai' => array(
					'active' => false,
				),
				'condition' => array(
					'_show_timeline_previews' => 'yes',
				),
			)
		);

		// Deal with "HLS Key" field visibility.
		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'_show_hls_key',
			array(
				'type' => Elementor\Controls_Manager::HIDDEN,
				'default' => '',
			)
		);

		// Note: This field is populated in JS and removed from Elementor data, see function comment.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'hls_key',
			array(
				'label' => esc_html__( 'HLS Key', 'fv-player' ),
				'label_block' => true,
				'type' => Elementor\Controls_Manager::TEXT,
				'ai' => array(
					'active' => false,
				),
				'condition' => array(
					'_show_hls_key' => 'yes',
				),
			)
		);

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'shortcode',
			array(
				'type' => Elementor\Controls_Manager::HIDDEN,
				'default' => '',
			)
		);

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'fvplayer_subtitles_settings_note',
			array(
				'type' => Elementor\Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'Looking for subtitles or player settings?', 'fv-player' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'fvplayer_editor_button',
			array(
				'type'        => Elementor\Controls_Manager::BUTTON,
				'text'        => esc_html__( 'Configure Player', 'fv-player' ),
				'event'       => 'fv-player-elementor-editor-open',
				'button_type' => 'default',
				'show_label'  => false,
			)
		);

		// TODO: Add these controls back in.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		// $this->add_control(
		// 	'autoplay',
		// 	array(
		// 		'label' => esc_html__( 'Autoplay', 'elementor' ),
		// 		'description' => sprintf(
		// 			/* translators: 1: `<a>` opening tag, 2: `</a>` closing tag. */
		// 			esc_html__( 'Note: Autoplay is affected by %1$s Google’s Autoplay policy %2$s on Chrome browsers.', 'elementor' ),
		// 			'<a href="https://developers.google.com/web/updates/2017/09/autoplay-policy-changes" target="_blank">',
		// 			'</a>'
		// 		),
		// 		'type' => Elementor\Controls_Manager::SWITCHER,
		// 		'frontend_available' => true,
		// 	)
		// );

		// TODO: Add these controls back in.
		// /** @disregard P1013 Undefined method */
		// /** @disregard P1009 Undefined type */
		// $this->add_control(
		// 	'loop',
		// 	array(
		// 		'label' => esc_html__( 'Loop', 'elementor' ),
		// 		'type' => Elementor\Controls_Manager::SWITCHER,
		// 		'frontend_available' => true,
		// 	)
		// );

		// TODO: Add these controls back in.
		// /** @disregard P1013 Undefined method */
		// /** @disregard P1009 Undefined type */
		// $this->add_control(
		// 	'controls',
		// 	array(
		// 		'label' => esc_html__( 'Player Controls', 'elementor' ),
		// 		'type' => Elementor\Controls_Manager::SWITCHER,
		// 		'label_off' => esc_html__( 'Hide', 'elementor' ),
		// 		'label_on' => esc_html__( 'Show', 'elementor' ),
		// 		'default' => 'yes',
		// 		'frontend_available' => true,
		// 	)
		// );

		// TODO: Add these controls back in.
		// /** @disregard P1013 Undefined method */
		// /** @disregard P1009 Undefined type */
		// $this->add_control(
		// 	'lightbox',
		// 	array(
		// 		'label' => esc_html__( 'Lightbox', 'elementor' ),
		// 		'type' => Elementor\Controls_Manager::SWITCHER,
		// 		'frontend_available' => true,
		// 		'label_off' => esc_html__( 'Off', 'elementor' ),
		// 		'label_on' => esc_html__( 'On', 'elementor' ),
		// 		'separator' => 'before',
		// 	)
		// );

		/** @disregard P1013 Undefined method */
		$this->end_controls_section();

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->start_controls_section(
			'section_video_style',
			array(
				'label' => esc_html__( 'Video', 'elementor' ),
				'tab' => Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		// TODO: This does not work.
		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_control(
			'aspect_ratio',
			array(
				'label' => esc_html__( 'Aspect Ratio', 'elementor' ),
				'type' => Elementor\Controls_Manager::SELECT,
				'options' => array(
					'169' => '16:9',
					'219' => '21:9',
					'43' => '4:3',
					'32' => '3:2',
					'11' => '1:1',
					'916' => '9:16',
				),
				'selectors_dictionary' => array(
					'169' => '1.77777', // 16 / 9
					'219' => '2.33333', // 21 / 9
					'43' => '1.33333', // 4 / 3
					'32' => '1.5', // 3 / 2
					'11' => '1', // 1 / 1
					'916' => '0.5625', // 9 / 16
				),
				'default' => '169',
				'selectors' => array(
					'{{WRAPPER}} .elementor-wrapper' => '--video-aspect-ratio: {{VALUE}}',
				),
			)
		);

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->add_group_control(
			Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name' => 'css_filters',
				'selector' => '{{WRAPPER}} .elementor-wrapper',
			)
		);

		/** @disregard P1013 Undefined method */
		/** @disregard P1009 Undefined type */
		$this->end_controls_section();
	}

	public function print_a11y_text( $image_overlay ) {
		if ( empty( $image_overlay['alt'] ) ) {
			echo esc_html__( 'Play Video', 'elementor' );
		} else {
			echo esc_html__( 'Play Video about', 'elementor' ) . ' ' . esc_attr( $image_overlay['alt'] );
		}
	}

	/**
	 * Render video widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		/** @disregard P1013 Undefined method */
		$settings = $this->get_settings_for_display();

		$video_html = do_shortcode( $settings['shortcode'] );

		if ( empty( $video_html ) ) {
			return;
		}

		/** @disregard P1013 Undefined method */
		$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-wrapper' );

		/** @disregard P1013 Undefined method */
		$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-open-inline' );
		?>
		<div <?php /** @disregard P1013 Undefined method */ $this->print_render_attribute_string( 'video-wrapper' ); ?>>
			<?php
			/** @disregard P1009 Undefined type */
			Elementor\Utils::print_unescaped_internal_string( $video_html ); // XSS ok.
			?>
		</div>
		<?php
	}

	/**
	 * Set script dependencies for the widget editing.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget script dependencies.
	 */
	public function get_script_depends() {
		/** @disregard P1009 Undefined type */
		/** @disregard P1014 Undefined property */
		return Elementor\Plugin::$instance->preview->is_preview_mode() ? array( 'elementor-fv-player-widget' ) : array();
	}
}
