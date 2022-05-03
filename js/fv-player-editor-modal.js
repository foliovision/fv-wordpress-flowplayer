(function( $ ){
  $.fn.fv_player_box = $.fv_player_box = function( args ) {
    args.onComplete();
    args.onOpen();

    $("#fv-player-editor-modal, #fv-player-editor-backdrop").show();

    $('body').addClass('is-fv-player-editor-modal-open');

		$(document).on( 'click', '#fv-player-editor-modal-close, #fv-player-editor-backdrop', function() {
			args.onClosed();
			$("#fv-player-editor-modal, #fv-player-editor-backdrop").hide();

      $('body').removeClass('is-fv-player-editor-modal-open');
		});

    return this;
  };

})( jQuery );