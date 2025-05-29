(function( $ ){
  $.fn.fv_player_box = $.fv_player_box = function( args ) {
    args.onComplete();
    args.onOpen();

    $("#fv-player-editor-modal, #fv-player-editor-backdrop").show();

    $('body').addClass('is-fv-player-editor-modal-open');

		$( '#fv-player-editor-modal-close, #fv-player-editor-backdrop' ).on( 'click', close );

    function close() {
      // Wait for the keyup throttle to finish as otherwise there might still be some data just starting to save
      setTimeout( function() {
        if ( fv_player_editor.is_busy_saving() ) {
          alert( 'FV Player Edtior is still saving, please wait.' );
          return false;
        }

        args.onClosed();
        $("#fv-player-editor-modal, #fv-player-editor-backdrop").hide();

        $('body').removeClass('is-fv-player-editor-modal-open');

        $( '#fv-player-editor-modal-close, #fv-player-editor-backdrop' ).off( 'click', close );

      }, 1.2 * parseInt( fv_player_editor_conf.keyup_throttle ) );
    }

    $.fv_player_box.close = close;

    return this;
  };

})( jQuery );
