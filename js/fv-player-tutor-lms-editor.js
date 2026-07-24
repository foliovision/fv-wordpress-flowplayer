document.addEventListener( 'DOMContentLoaded', function () {
	if ( typeof Tutor === 'undefined' || ! Tutor.CourseBuilder ) {
		return;
	}

	var video_url_selector = '.tutor-portal-popover [name=videoUrl]';

	function isFvPlayerShortcode( value ) {
		return ( value || '' ).indexOf( '[fvplayer ' ) !== -1;
	}

	/**
	 * Pull the first [fvplayer ...] shortcode out of mixed preview text.
	 *
	 * @param {string} value
	 * @return {string}
	 */
	function extractFvPlayerShortcode( value ) {
		var match = ( value || '' ).match( /\[fvplayer [^\]]*\]/i );
		return match ? match[0] : '';
	}

	fv_player_editor_conf.on_insert = function ( shortcode ) {

		// Set the value in a way which works with React:
		var el = document.querySelector( video_url_selector );
		if ( ! el ) {
			return;
		}

		var setter = Object.getOwnPropertyDescriptor(
			window.HTMLTextAreaElement.prototype,
			'value'
		).set;
		setter.call( el, shortcode );
		el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	};

	fv_player_editor_conf.on_close = function () {
		// Just click to accept the shortcode.
		jQuery( '[data-cy="submit-url"]' ).trigger( 'click' );

		// Do not keep field_selector around for the next "Add FV Player" open.
		fv_player_editor_conf.field_selector = false;
	};

	/**
	 * Poll until a jQuery selector matches, then run the callback.
	 *
	 * @param {string}   selector  jQuery selector to wait for.
	 * @param {Function} callback  Called with the matched jQuery object.
	 * @param {Object}   [options]
	 * @param {number}   [options.interval=100]  Poll interval in ms.
	 * @param {number}   [options.maxAttempts=100]
	 * @return {number} Interval ID (can be cleared with clearInterval).
	 */
	function waitForElement( selector, callback, options ) {
		options = options || {};
		var intervalMs = options.interval || 200;
		var maxAttempts = options.maxAttempts || 25;
		var attempts = 0;

		var intervalId = setInterval( function () {
			var $el = jQuery( selector );

			if ( $el.length > 0 ) {
				clearInterval( intervalId );
				callback( $el );
				return;
			}

			attempts++;
			if ( attempts > maxAttempts ) {
				clearInterval( intervalId );
				console.error( 'FV_Player_TutorLMS: Element not found: ' + selector );
			}
		}, intervalMs );

		return intervalId;
	}

	/**
	 * Open the FV Player editor via a hidden trigger button so the normal
	 * shortcode-editor click path (editor_open / editor_close) runs.
	 */
	function openFvPlayerEditor() {
		if ( ! jQuery.fv_player_box ) {
			console.error( 'FV_Player_TutorLMS: jQuery.fv_player_box not found' );
			return;
		}

		var $trigger = jQuery( '#fv-player-tutor-lms-editor-trigger' );
		if ( ! $trigger.length ) {
			$trigger = jQuery(
				'<button type="button" id="fv-player-tutor-lms-editor-trigger" class="fv-player-editor-button" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0" tabindex="-1" aria-hidden="true"></button>'
			);
			jQuery( 'body' ).append( $trigger );
		}

		$trigger.trigger( 'click' );
	}

	/**
	 * Find uninitialized "Add from URL" buttons and wire them up as FV Player triggers.
	 */
	function initAddFromUrlButtons() {
		jQuery( '#tutor-course-builder [data-cy="add-from-url"]:not([data-fv-player-ready])' ).each( function () {
			var $button = jQuery( this );

			$button.attr( 'data-fv-player-ready', '1' );
			$button.html( $button.html().replace( /Add from URL/, 'Add FV Player' ) );

			if ( ! jQuery.fv_player_box ) {
				console.error( 'FV_Player_TutorLMS: jQuery.fv_player_box not found' );
			}

			// Add the FV Player Editor trigger
			$button.addClass( 'fv-player-editor-button' );

			/**
			 * Once clicked we set the video type to "Shortcode", find the textarea and open the FV Player modal.
			 */
			$button.on( 'click.fvPlayerTutor', function () {
				// New player insert — do not try to load from a field.
				fv_player_editor_conf.field_selector = false;

				waitForElement(
					'.tutor-portal-popover [name=videoSource]',
					function ( $source ) {
						$source.val( 'shortcode' );
						$source.attr( 'title', 'Shortcode' );
					}
				);

				waitForElement(
					video_url_selector,
					function ( $url ) {
						$url.val( 'FV Player!' );
					}
				);

				// The FV Player Editor opens thanks to the fv-player-editor-button class added above.
			} );
		} );
	}

	/**
	 * Wire the edit (pencil) button on an existing shortcode video preview.
	 * Tutor opens the URL popover; we then load that shortcode into the FV Player editor.
	 */
	function initMediaPreviewEditButtons() {
		jQuery( '#tutor-course-builder [data-cy="media-preview"]' ).each( function () {
			var $preview = jQuery( this );

			// Only handle previews that already show an FV Player shortcode.
			if ( ! isFvPlayerShortcode( $preview.text() ) ) {
				return;
			}

			$preview.find( 'button:not([data-cy="remove-video"]):not([data-fv-player-ready])' ).each( function () {
				var $button = jQuery( this );

				// Skip Tutor media buttons that have their own data-cy (replace/clear thumbnail).
				if ( $button.attr( 'data-cy' ) ) {
					return;
				}

				$button.attr( 'data-fv-player-ready', '1' );

				$button.on( 'click.fvPlayerTutor', function () {
					waitForElement(
						video_url_selector,
						function ( $url ) {
							if ( ! isFvPlayerShortcode( $url.val() ) ) {
								return;
							}

							// Let editor_open() load the existing shortcode from the Tutor field.
							fv_player_editor_conf.field_selector = video_url_selector;
							openFvPlayerEditor();
						}
					);
				} );
			} );
		} );
	}

	/**
	 * Load an FV Player preview after each Tutor media preview that holds a shortcode.
	 * Uses the same fv_player_preview GET endpoint as the Custom Videos editor.
	 */
	function initFvPlayerPreviews() {
		jQuery( '#tutor-course-builder [data-cy="media-preview"]' ).each( function () {
			var $preview = jQuery( this );
			var $field_wrapper = $preview.closest( '[data-cy="form-field-wrapper"]' );
			var shortcode = extractFvPlayerShortcode( $preview.text() );

			if ( ! $field_wrapper.length ) {
				return;
			}

			var $container = $field_wrapper.next( '.fv-player-tutor-lms-preview' );

			if ( ! shortcode ) {
				$container.remove();
				return;
			}

			// Already showing this shortcode — skip.
			if ( $container.length && $container.data( 'shortcode' ) === shortcode ) {
				return;
			}

			if ( ! $container.length ) {
				$container = jQuery( '<div class="fv-player-tutor-lms-preview"></div>' ).insertAfter( $field_wrapper );
			}

			$container.data( 'shortcode', shortcode );
			$container.html( '<span class="fv-player-tutor-lms-preview-loading">Loading preview…</span>' );

			var preview_shortcode = shortcode
				.replace( /(width=[\'"])\d*([\'"])/, '$1320$2' )
				.replace( /(height=[\'"])\d*([\'"])/, '$1240$2' );

			var url = fv_player_editor_conf.home_url
				+ '?fv_player_preview_nonce=' + fv_player_editor_conf.preview_nonce
				+ '&fv_player_preview=' + fv_player_editor.b64EncodeUnicode( preview_shortcode );

			jQuery.get( url, function ( response ) {
				if ( $container.data( 'shortcode' ) !== shortcode ) {
					return;
				}

				var $wrapper = jQuery( '#wrapper', response );
				if ( $wrapper.length ) {
					$container.html( $wrapper );
					jQuery( document ).trigger( 'fvp-preview-complete', [ shortcode, null, $container ] );
				} else {
					$container.html( '<span class="fv-player-tutor-lms-preview-error">Preview unavailable</span>' );
				}
			} ).fail( function () {
				if ( $container.data( 'shortcode' ) !== shortcode ) {
					return;
				}

				$container.html( '<span class="fv-player-tutor-lms-preview-error">Preview failed</span>' );
			} );
		} );
	}

	// Remove the FV Player preview when Tutor's remove-video control is used, click event would not work.
	jQuery( document ).on( 'mouseup', '#tutor-course-builder [data-cy="remove-video"]', function () {
		var $field_wrapper = jQuery( this ).closest( '[data-cy="form-field-wrapper"]' );
		$field_wrapper.next( '.fv-player-tutor-lms-preview' ).remove();
	} );

	function initTutorLmsButtons() {
		initAddFromUrlButtons();
		initMediaPreviewEditButtons();
		initFvPlayerPreviews();
	}

	initTutorLmsButtons();
	setInterval( initTutorLmsButtons, 500 );
} );
