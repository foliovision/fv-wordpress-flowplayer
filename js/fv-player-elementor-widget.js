(function($) {

	$(window).on('elementor/frontend/init', function() {

		elementor.hooks.addAction( 'panel/open_editor/widget/fv_player', function( panel, model, view ) {

			let field_source_url = $( '.elementor-control-source_url [data-setting="url"]', panel.$el ),
				field_splash_url = $( '.elementor-control-splash_url [data-setting="url"]', panel.$el ),
				field_splash_attachment_id = $( '[data-setting="splash_attachment_id"]', panel.$el ),
				field_title = $( '[data-setting="title"]', panel.$el ),
				field_timeline_previews = $( '.elementor-control-timeline_previews [data-setting="url"]', panel.$el ),
				field_hls_hlskey = $( '[data-setting="hls_key"]', panel.$el ),
				field_shortcode = $( '[data-setting="shortcode"]', panel.$el ),
				ajax_data = {},
				last_ajax_data = false,
				is_loading = false,
				debounce_timer = null,
				shortcode = model.get('settings').get('shortcode'),
				player_id = get_player_id( shortcode );

			if ( player_id > 0 ) {
				console.log('FV Player Elementor Widget data loading for player #' + player_id );

				is_loading = true;

				// Make AJAX call to get player attributes
				$.ajax({
					url: elementor_fv_player_widget.ajaxurl,
					type: 'POST',
					data: {
						action: 'fv_player_guttenberg_attributes_load',
						player_id: player_id,
						security: elementor_fv_player_widget.nonce
					},
					success: function(response) {
						console.log('FV Player Elementor Widget data loaded', response);

						field_source_url.val( response.src || '' ).trigger( 'input' );
						field_splash_url.val( response.splash || '' ).trigger( 'input' );
						field_title.val( response.title || '' ).trigger( 'input' );

						$( '[data-setting="_show_timeline_previews"]', panel.$el ).val( response.timeline_previews !== '' ? 'yes' : '' ).trigger( 'input' );
						field_timeline_previews.val( response.timeline_previews || '' ).trigger( 'input' );

						$( '[data-setting="_show_hls_key"]', panel.$el ).val( response.hls_hlskey !== '' ? 'yes' : '' ).trigger( 'input' );
						field_hls_hlskey.val( response.hls_hlskey || '' ).trigger( 'input' );

						is_loading = false;
					},
					error: function(xhr, status, error) {
						console.error('Error loading player attributes:', error);

						is_loading = false;
					}
				});
			}

			function get_player_id( shortcode) {
				var match = shortcode.match(/\[fvplayer id="(\d+)"\]/);
				if (match && match[1]) {
					return parseInt( match[1] );
				}
				return 0;
			}

			function save( ajax_data ) {

				// No changes made
				if ( JSON.stringify( ajax_data ) === last_ajax_data ) {
					return;
				}
				last_ajax_data = JSON.stringify( ajax_data );

				console.log('FV Player Elementor Widget data saving for widget ' + model.id );
		
				$.ajax({
					url: elementor_fv_player_widget.ajaxurl,
					type: 'POST',
					data: {
						action:               'fv_player_guttenberg_attributes_save',
						player_id:            player_id,
						src:                  ajax_data.src,
						splash:               ajax_data.splash,
						title:                ajax_data.title,
						splash_attachment_id: ajax_data.splash_attachment_id,
						hls_hlskey:           ajax_data.hls_hlskey,
						timeline_previews:    ajax_data.timeline_previews,
						security:             elementor_fv_player_widget.nonce
					},
					success: function(response) {
						if ( response.player_id ) {
							console.log('FV Player Elementor Widget data saved', response);

							player_id = response.player_id;

							const fields = [
								{ field: field_shortcode,           value: response.shortcodeContent },
								{ field: field_source_url,          value: response.src },
								{ field: field_splash_url,          value: response.splash },
								{ field: field_title,               value: response.title },
								{ field: field_splash_attachment_id,value: response.splash_attachment_id },
								{ field: field_timeline_previews,   value: response.timeline_previews },
								{ field: field_hls_hlskey,          value: response.hls_hlskey }
							];

							fields.forEach( function( item ) {
								item.field.val( item.value ).trigger( 'input' );
							} );

						} else {
							console.error('Error saving player attributes:', response);
						}
					},
					error: function(xhr, status, error) {
						console.error('Error saving player attributes:', error);
					}
				});
			}

			function debounced_save() {
				if ( is_loading ) {
					return;
				}

				clearTimeout(debounce_timer);
				debounce_timer = setTimeout(function() {
					save( ajax_data );
				}, 500 );
			}

			field_source_url.on( 'input change', function() {

				console.log('src input');

				ajax_data.src = field_source_url.val().trim();
				debounced_save();
			});

			field_splash_url.on( 'input', function() {
				ajax_data.splash = field_splash_url.val().trim();
				debounced_save();
			});

			field_splash_attachment_id.on( 'input', function() {
				ajax_data.splash_attachment_id = field_splash_attachment_id.val().trim();
				debounced_save();
			});

			field_title.on( 'input', function() {
				ajax_data.title = field_title.val().trim();
				debounced_save();
			});

			field_timeline_previews.on( 'input', function() {
				ajax_data.timeline_previews = field_timeline_previews.val().trim();
				debounced_save();
			});

			field_hls_hlskey.on( 'input', function() {
				ajax_data.hls_hlskey = field_hls_hlskey.val().trim();
				debounced_save();
			});

		} );

	});



})(jQuery);