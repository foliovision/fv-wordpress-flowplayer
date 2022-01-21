(function( $ ){
  $.fn.fv_player_box = $.fv_player_box = function( args ) {
    args.onComplete();
    args.onOpen();

    $("#fv-player-editor-modal, #fv-player-editor-backdrop").show();

		$(document).on( 'click', '#fv-player-editor-modal-close', function() {
			args.onClosed();
			$("#fv-player-editor-modal, #fv-player-editor-backdrop").hide();
		});

    return this;
  };

})( jQuery );