document.addEventListener( 'DOMContentLoaded', function () {
	if ( typeof Tutor === 'undefined' || ! Tutor.CourseBuilder ) {
		return;
	}

	fv_player_editor_conf.on_insert = function ( shortcode ) {

		// Set the value in a way which works with React:
		var el = document.querySelector( '.tutor-portal-popover [name=videoUrl]' );
		var setter = Object.getOwnPropertyDescriptor(
			window.HTMLTextAreaElement.prototype,
			'value'
		).set;
		setter.call( el, shortcode );
		el.dispatchEvent(new Event('input', { bubbles: true }));
	};

	fv_player_editor_conf.on_close = function () {
		// Just click to accept the shortcode.
		jQuery('[data-cy="submit-url"]').trigger( 'click' );
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

	function fv_player_tutor_lms_hooks() {
		console.log( 'FV_Player_TutorLMS: fv_player_tutor_lms_hooks...' );

		/**
		 * Find the "Add from URL" button of Tutor LMS and change it to "Add FV Player".
		 */
		waitForElement(
			'#tutor-course-builder [data-cy="add-from-url"]',
			function ( $button ) {
				$button.html( $button.html().replace( /Add from URL/, 'Add FV Player' ) );

				if ( ! jQuery.fv_player_box ) {
					console.error( 'FV_Player_TutorLMS: jQuery.fv_player_box not found' );
				}

				// Add the FV Player Editor trigger
				$button.addClass( 'fv-player-editor-button' );

				/**
				 * Once clicked we set the video type to "Shortcode", find the textarea and open the FV Player modal.
				 */
				$button.on( 'click', function () {
					waitForElement(
						'.tutor-portal-popover [name=videoSource]',
						function ( $source ) {
							$source.val( 'shortcode' );
							$source.attr( 'title', 'Shortcode' );
						}
					);

					waitForElement(
						'.tutor-portal-popover [name=videoUrl]',
						function ( $url ) {
							$url.val( 'FV Player!' );
						}
					);

					// The FV Player Editor opens thanks to the fv-player-editor-button class added above.
					// It even sets the value properly with fv_player_editor_conf.lazy_field_selector set above.
					// TODO: Submit with jQuery('[data-cy="submit-url"]').trigger( 'click' );
				} );
			}
		);
	}

	fv_player_tutor_lms_hooks();

	// We check if the lesson editing was open and if so, we add the hooks.
	// We could check for clicks on [data-cy="add-lesson"] and [data-cy="edit-lesson"], but you can also just click the lesson title which has no class.
	setInterval( function () {
		if ( jQuery( '[data-cy="tutor-modal"]').length ) {
			fv_player_tutor_lms_hooks();
		}

	}, 1000 );
} );
