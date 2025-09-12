(function($) {

	$(window).on('elementor/frontend/init', function() {

		elementor.hooks.addAction( 'panel/open_editor/widget/fv_player', function( panel, model, view ) {

			let field_source_url,
				field_splash_url,
				field_splash_attachment_id,
				field_title,
				field_timeline_previews,
				field_show_timeline_previews,
				field_hls_hlskey,
				field_show_hls_hlskey,
				field_shortcode,
				// Which field is saved using what key in our Ajax?
				ajax_field_map = [],
				ajax_data = {},
				last_ajax_data = false,
				is_loading = false,
				debounce_timer = null,
				shortcode = model.get('settings').get('shortcode'),
				player_id = get_player_id( shortcode );

			function find_fields() {
				field_source_url = $( '.elementor-control-source_url [data-setting="url"]', panel.$el );
				field_splash_url = $( '.elementor-control-splash_url [data-setting="url"]', panel.$el );
				field_splash_attachment_id = $( '[data-setting="splash_attachment_id"]', panel.$el );
				field_title = $( '[data-setting="title"]', panel.$el );
				field_timeline_previews = $( '.elementor-control-timeline_previews [data-setting="url"]', panel.$el );
				field_show_timeline_previews = $( '[data-setting="_show_timeline_previews"]', panel.$el );
				field_hls_hlskey = $( '[data-setting="hls_key"]', panel.$el );
				field_show_hls_hlskey = $( '[data-setting="_show_hls_key"]', panel.$el );
				field_shortcode = $( '[data-setting="shortcode"]', panel.$el );

				ajax_field_map = [
					{ field: field_source_url,           dataKey: 'src' },
					{ field: field_splash_url,           dataKey: 'splash' },
					{ field: field_splash_attachment_id, dataKey: 'splash_attachment_id' },
					{ field: field_title,                dataKey: 'title' },
					{ field: field_timeline_previews,    dataKey: 'timeline_previews' },
					{ field: field_hls_hlskey,           dataKey: 'hls_hlskey' }
				];
			}

			find_fields();

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

						populate_fields( response );

						is_loading = false;
					},
					error: function(xhr, status, error) {
						console.error('Error loading player attributes:', error);

						is_loading = false;
					}
				});
			}

			// Map response to fields in DOM and to Elementor model
			function populate_fields( resp ) {
				const fields = [
					{ el: field_shortcode,              val: resp.shortcodeContent,               model: 'shortcode' },
					{ el: field_source_url,             val: resp.src,                            model: 'source_url', model_val: { 'url': resp.src } },
					{ el: field_splash_url,             val: resp.splash,                         model: 'splash_url', model_val: { 'url': resp.splash } },
					{ el: field_title,                  val: resp.title,                          model: 'title' },
					{ el: field_splash_attachment_id,   val: resp.splash_attachment_id,           model: 'splash_attachment_id' },
					{ el: field_timeline_previews,      val: resp.timeline_previews,              model: 'timeline_previews', model_val: { 'url': resp.timeline_previews } },
					{ el: field_show_timeline_previews, val: resp.timeline_previews ? 'yes' : '', model: '_show_timeline_previews' },
					{ el: field_hls_hlskey,             val: resp.hls_hlskey,                     model: 'hls_hlskey' },
					{ el: field_show_hls_hlskey,        val: resp.hls_hlskey ? 'yes' : '',        model: '_show_hls_key' },
				];

				fields.forEach( function( item ) {
					item.el.val( item.val );

					// Without this Elementor would remove the field values when switching panel tabs.
					model.get( 'settings' ).set( item.model, item.model_val || item.val );
				} );

				// Refresh the widget preview
				field_shortcode.trigger( 'input' );
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

							populate_fields( response );

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
					ajax_field_map.forEach( function( mapping ) {
						if ( mapping.field.val() ) {
							ajax_data[ mapping.dataKey ] = mapping.field.val().trim();
						} else {
							console.log( 'FV Player Elementor Widget: Ajax save: Field not found', mapping.dataKey );
						}
					} );

					save( ajax_data );
				}, 500 );
			}

			/**
			 * Events to trigger save when fields are changed.
			 * And to disable Elementor Ajax for the fields.
			 */
			function attach_listeners() {
				ajax_field_map.forEach( function( mapping ) {
					mapping.field.off( 'input change' ).on( 'input change', function() {
						debounced_save();

						// Avoid Elementor Ajax
						return false;
					});
				});
			}

			attach_listeners();

			// Re-attach when the FV Player Elementor Widget preview finishes loading.
			elementorFrontend.hooks.addAction( 'frontend/element_ready/fv_player.default', attach_listeners );

			// Re-attach when the FV Player Elementor Widget tab is switched.
			/**
			 * Note: We tried to use the panel.content.currentView.ui.tabs click event, but it kept getting removed,
			 * when changing tabs or even when changing field values in the tabs!
			 */
			$( panel.$el ).on( 'click', function() {
				find_fields();
				attach_listeners();
			} );
		} );

	});

})(jQuery);