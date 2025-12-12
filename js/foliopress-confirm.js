function foliopress_confirm( message, yes_callback, no_callback ) {

	/**
	 * HTML code copied from a standard WordPress Gutenberg confirmation popup when trashing a post on the block editor screen.
	 *
	 * Changes:
	 * - span gets style="margin-top: 11px; display: inline-block"
	 * - div with button gets .alignright
	 * - data-wp-component and data-wp-c16t attributes removed
	 * - dynamic React classes removed
	 * - .foliopress-confirm-modal added
	 */
	var modalHTML = '\
		<div class="components-modal__screen-overlay foliopress-confirm-modal">\
			<div class="components-modal__frame has-size-small components-confirm-dialog" role="dialog" tabindex="-1" style="--modal-frame-animation-duration: 200ms;">\
				<div class="components-modal__content hide-header" role="document">\
					<div class="components-flex components-h-stack components-v-stack">\
						<span class="components-truncate components-text" style="margin-top: 11px; display: inline-block"></span>\
						<div class="components-flex alignright">\
							<button type="button" class="components-button is-next-40px-default-size is-tertiary">No</button>\
							<button type="button" class="components-button is-next-40px-default-size is-primary">Yes</button>\
						</div>\
					</div>\
				</div>\
			</div>\
		</div>';

	var tempDiv = document.createElement('div');
	tempDiv.innerHTML = modalHTML;
	var modal = tempDiv.firstElementChild;

	modal.querySelector('.components-text').textContent = message;

	modal.querySelector('.is-primary').addEventListener('click', function() {
		yes_callback();
		modal.remove();
	});

	modal.querySelector('.is-tertiary').addEventListener('click', function() {
		if ( no_callback ) {
			no_callback();
		}
		modal.remove();
	});

	// Handle keyboard events
	var handleKeyDown = function( event ) {
		// Check if modal is still in the DOM
		if ( ! document.body.contains( modal ) ) {
			return;
		}

		if ( event.key === 'Enter' ) {
			event.preventDefault();
			yes_callback();
			modal.remove();
		} else if ( event.key === 'Escape' ) {
			event.preventDefault();
			if ( no_callback ) {
				no_callback();
			}
			modal.remove();
		}
	};

	document.addEventListener('keydown', handleKeyDown);

	// Clean up event listener when modal is removed
	var originalRemove = modal.remove.bind(modal);
	modal.remove = function() {
		document.removeEventListener('keydown', handleKeyDown);
		originalRemove();
	};

	// Add the modal to the page
	document.body.appendChild(modal);

	// Focus the modal dialog for keyboard accessibility
	modal.querySelector('[role="dialog"]').focus();
}
