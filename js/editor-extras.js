/* eslint-disable no-unused-vars */
/*global fv_player_editor_matcher */

// What's here needs to stay global

// used in FV Player Pro to add more matchers
if (typeof(window.fv_player_editor_matcher) == 'undefined') {
  window.fv_player_editor_matcher = {};
}

fv_player_editor_matcher.default = {
  // matches URL of the video
  matcher: /\.(mp4|webm|m3u8)(?:(?:\?.*?|\#.*?)?)?$/i,
  // AJAX will return these fields which can be auto-updated via JS
  update_fields: ['duration', 'last_video_meta_check'],
  support_thumb_generate: true
}

function fv_wp_flowplayer_dialog_resize() {
  fv_player_editor.fv_wp_flowplayer_dialog_resize();
}

function fv_flowplayer_insertUpdateOrDeletePlayerMeta( options ) {
  fv_player_editor.insertUpdateOrDeletePlayerMeta( options );
}

function fv_flowplayer_insertUpdateOrDeleteVideoMeta( options ) {
  fv_player_editor.insertUpdateOrDeleteVideoMeta( options );
}

function fv_wp_flowplayer_shortcode_parse_arg( sShortcode, sArg, bHTML, sCallback ) {
  return fv_player_editor.shortcode_parse_arg( sShortcode, sArg, bHTML, sCallback );
}

// TODO: This is used in editor-screenshots.js and FV Player Pay Per View!
function fv_wp_flowplayer_submit( preview ) {

}

jQuery(document).on('fv_flowplayer_shortcode_insert', function(e) {
  jQuery(e.target).siblings('.button.fv-wordpress-flowplayer-button').val('Edit');
});

/*
 * Player edit lock
 */
if( typeof(fv_player_editor_conf) != "undefined" ) {
  // extending DB player edit lock's timer
  jQuery( document ).on( 'heartbeat-send', function ( event, data ) {
    // FV Player might not be loaded, like in case of Elementor
    if( !window.fv_player_editor ) return;

    if( fv_player_editor.get_current_player_db_id() > -1 ) {
      data.fv_flowplayer_edit_lock_id = fv_player_editor.get_current_player_db_id();
    }

    var
      removals = fv_player_editor.get_edit_lock_removal(),
      removalsEmpty = true;

    for (var i in removals) {
      removalsEmpty = false;
      break;
    }

    if( !removalsEmpty ) {
      data.fv_flowplayer_edit_lock_removal = fv_player_editor.get_edit_lock_removal();
    } else {
      delete(data.fv_flowplayer_edit_lock_removal);
    }
  });
  
  // remove edit locks in the config if it was removed on the server
  jQuery( document ).on( 'heartbeat-tick', function ( event, data ) {
    if ( data.fv_flowplayer_edit_locks_removed ) {
      var
        edit_lock_removal = fv_player_editor.get_edit_lock_removal(),
        new_edit_lock_removal = {};

      // remove only edit locks that were removed server-side
      for (var i in edit_lock_removal) {
        if (!data.fv_flowplayer_edit_locks_removed[i]) {
          new_edit_lock_removal[i] = edit_lock_removal[i];
        }
      }

      fv_player_editor.set_edit_lock_removal(new_edit_lock_removal);
    }
  });
}

/**
 * LMS | Teaching saving into Player meta
 */
(function ($) {
  $( document ).on( 'fv_flowplayer_player_meta_save', function( event, data ) {
    fv_flowplayer_insertUpdateOrDeletePlayerMeta( {
      data:          data,
      meta_key:      'lms_teaching',
      element:       $( '[name=fv_wp_flowplayer_field_lms_teaching]' )[0],
    } );

  });

  $( document ).on( 'fv_flowplayer_player_meta_load', function( event, data ) {
    if ( data.meta ) {
      for ( let i in data.meta ) {
        if ( 'lms_teaching' == data.meta[i].meta_key ) {
          $( '[name=fv_wp_flowplayer_field_lms_teaching]' )
            .prop( 'checked', data.meta[i].meta_value )
            .attr( 'data-id', data.meta[i].id )
            .trigger( 'change' );
        }
      }
    }
  } );

}(jQuery));