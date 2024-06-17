(function( $ ){
  $.fn.fv_player_box = $.fv_player_box = function( args ) {
    args.onComplete();
    args.onOpen();

    $("#fv-player-editor-modal, #fv-player-editor-backdrop").show();

    $('body').addClass('is-fv-player-editor-modal-open');

		$( '#fv-player-editor-modal-close, #fv-player-editor-backdrop' ).on( 'click', close );

    function close() {
      if ( fv_player_editor.is_busy_saving() ) {
        alert( 'FV Player Edtior is still saving, please wait.' );
        return false;
      }

      args.onClosed();
      $("#fv-player-editor-modal, #fv-player-editor-backdrop").hide();

      $('body').removeClass('is-fv-player-editor-modal-open');

      $( '#fv-player-editor-modal-close, #fv-player-editor-backdrop' ).off( 'click', close );
    }

    $.fv_player_box.close = close;

    return this;
  };

})( jQuery );
