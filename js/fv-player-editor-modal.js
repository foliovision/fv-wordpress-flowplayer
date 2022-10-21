(function( $ ){
  $.fn.fv_player_box = $.fv_player_box = function( args ) {
    args.onComplete();
    args.onOpen();

    $("#fv-player-editor-modal, #fv-player-editor-backdrop").show();

    $('body').addClass('is-fv-player-editor-modal-open');

		$(document).on( 'click', '#fv-player-editor-modal-close, #fv-player-editor-backdrop', close );

    function close() {
      args.onClosed();
      $("#fv-player-editor-modal, #fv-player-editor-backdrop").hide();

      $('body').removeClass('is-fv-player-editor-modal-open');
    }

    $.fv_player_box.close = close;

    return this;
  };

})( jQuery );