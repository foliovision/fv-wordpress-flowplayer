(function( $ ){
  $.fn.fv_player_box = $.fv_player_box = function( args ) {
    console.log('fv_player_box',args);

    args.onComplete();
    args.onOpen();

    $(".fv-player-modal, .fv-player-modal-backdrop").show();

    console.log('fv_player_box',this);

		$(document).on( 'click', '#fv_player_boxClose', function() {
			args.onClosed();
			$(".fv-player-modal, .fv-player-modal-backdrop").hide();
		});

    return this;

  };

})( jQuery );