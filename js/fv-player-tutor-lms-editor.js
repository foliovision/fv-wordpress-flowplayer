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
		el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	};

	fv_player_editor_conf.on_close = function () {
		// Just click to accept the shortcode.
		jQuery( '[data-cy="submit-url"]' ).trigger( 'click' );
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
			} );
		} );
	}

	initAddFromUrlButtons();
	setInterval( initAddFromUrlButtons, 500 );
} );
