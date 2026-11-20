/*
 * "Pick existing player" modal using the shared Backbone wp.media browser wrapper
 * in media-library-browser-base.js (same wrapper as the additional Media Library tabs).
 */
jQuery( function( $ ) {
  var TAB_ID = 'fv_player_existing_players_tab',
    current_xhr = null;

  function abort_request() {
    if ( current_xhr && current_xhr.readyState !== 4 ) {
      current_xhr.abort();
    }
    current_xhr = null;
  }

  function escape_html( text ) {
    return $( '<div></div>' ).text( String( text || '' ) ).html();
  }

  function get_player_display_name( player ) {
    if ( player.player_name ) {
      return player.player_name;
    }

    if ( Array.isArray( player.video_titles ) && player.video_titles.length ) {
      return player.video_titles.join( ', ' );
    }

    return '#' + player.id;
  }

  function get_player_splash( player ) {
    if ( ! Array.isArray( player.thumbs ) || ! player.thumbs.length || ! player.thumbs[0] ) {
      return '';
    }

    return $( player.thumbs[0] ).find( 'img' ).first().attr( 'src' ) || '';
  }

  function render_player_sidebar( $item, $filenameDiv, extra ) {
    var player = extra && extra.player;
    if ( ! player ) {
      return '';
    }

    var $details = $( '<div class="attachment-details save-ready"></div>' ),
      id = player.id,
      player_name = player.player_name || '',
      date_created = player.date_created || '',
      video_titles = player.video_titles || [],
      embeds = player.embeds || {};

    $details.append( $( '<h2></h2>' ).text( 'Player Details' ) );

    var $info = $( '<div class="attachment-info"></div>' ),
      $infoDetails = $( '<div class="details"></div>' );
    $infoDetails.append( $( '<div class="filename"></div>' ).text( '#' + id + '. ' + player_name ) );
    $infoDetails.append( $( '<div class="uploaded"></div>' ).text( date_created ) );
    $info.append( $infoDetails );
    $details.append( $info );

    $details.append( $( '<h2></h2>' ).text( 'Videos' ) );
    var $videosUl = $( '<ul></ul>' );
    $.each( video_titles, function( k, video_title ) {
      $videosUl.append( $( '<li></li>' ).text( video_title ) );
    } );
    $details.append( $( '<div class="videos-list"></div>' ).append( $videosUl ) );

    $details.append( $( '<h2></h2>' ).text( 'Embedded on' ) );
    var $postsUl = $( '<ul></ul>' );
    $.each( embeds, function( k, v ) {
      var $li = $( '<li></li>' ),
        type = v.post_type && v.post_type !== 'post' ? ' (' + v.post_type + ')' : '',
        status = v.post_status && v.post_status !== 'publish' ? ' (' + v.post_status + ')' : '';

      $li.append( $( '<strong></strong>' ).text( v.post_title || '' ) );
      if ( type || status ) {
        $li.append( document.createTextNode( type + status ) );
      }

      if ( v.taxonomies ) {
        var taxonomies_and_terms = [];
        $.each( v.taxonomies, function( taxonomy, taxonomy_details ) {
          var taxonomy_terms = [];
          $.each( taxonomy_details.terms || [], function( idx, term ) {
            taxonomy_terms.push( term.name );
          } );
          if ( taxonomy_terms.length ) {
            taxonomies_and_terms.push( taxonomy_details.label + ': ' + taxonomy_terms.join( ', ' ) );
          }
        } );

        if ( taxonomies_and_terms.length ) {
          var $taxUl = $( '<ul></ul>' );
          $.each( taxonomies_and_terms, function( idx, text ) {
            $taxUl.append( $( '<li></li>' ).text( text ) );
          } );
          $li.append( $taxUl );
        }
      }

      $postsUl.append( $li );
    } );
    $details.append( $( '<div class="posts-list"></div>' ).append( $postsUl ) );

    return $details;
  }

  function on_player_select( $selectedItems ) {
    var $selected = $selectedItems.first(),
      extra = $selected.find( '.filename div' ).data( 'extra' ),
      player = extra && extra.player;

    if ( player && player.id && window.fv_player_editor ) {
      $( '#fv-player-shortcode-editor .fv_player_field_insert-button' ).text( 'Insert' );
      fv_player_editor.editor_open( player.id );
    }
  }

  function load_players( bucket, path, search ) {
    var $frame = fv_player_media_browser.get_frame_el(),
      $media_frame_content = $frame.find( '.media-frame-content' ),
      $overlay_div = $( '#fv-player-shortcode-editor-preview-spinner' ).clone().css( {
        height: '100%'
      } ),
      ajax_data = {
        action: 'fv_player_db_retrieve_all_players_for_dropdown',
        nonce: fv_player_editor_conf.search_nonce
      };

    abort_request();
    fv_player_media_browser.set_choose_button_disabled( true );
    $media_frame_content.html( $overlay_div );

    if ( typeof search === 'string' && search ) {
      ajax_data.search = search;
    }

    current_xhr = $.post( ajaxurl, ajax_data )
      .done( function( json_data ) {
        current_xhr = null;

        var renderOptions = {
            searchMsg: 'Search videos or playlists',
            noBreadcrumbs: true,
            nothingFoundMsg: 'No players found.'
          },
          items = [];

        if ( ! json_data || ! json_data.success ) {
          renderOptions.errorMsg = escape_html(
            ( json_data && json_data.data )
              ? json_data.data
              : 'An unexpected error has occurred while loading players. Please try again.'
          );
        } else {
          $.each( json_data.players || [], function( i, player ) {
            items.push( {
              name: get_player_display_name( player ),
              splash: get_player_splash( player ),
              link: String( player.id ),
              size: '',
              modified: player.date_created || '',
              type: 'file',
              extra: {
                player: player
              }
            } );
          } );
        }

        $media_frame_content.html( renderBrowserPlaceholderHTML( renderOptions ) );

        fv_flowplayer_browser_browse(
          {
            path: '',
            items: items
          },
          {
            extraAttachmentClass: 'fullsize',
            searchDelay: 400,
            ajaxSearchCallback: function( searchVal ) {
              load_players( null, null, searchVal );
            }
          }
        );
      } )
      .fail( function( jqXHR, textStatus, errorThrown ) {
        if ( textStatus === 'abort' ) {
          return;
        }
        current_xhr = null;

        var prefix = search
            ? 'An unexpected error has occurred while searching in player data: '
            : 'An unexpected error has occurred while loading players: ',
          renderOptions = {
            searchMsg: 'Search videos or playlists',
            noBreadcrumbs: true,
            nothingFoundMsg: 'No players found.',
            errorMsg: prefix + '<code>' + escape_html( errorThrown || textStatus || 'Unknown error' ) + '</code><br /><br />Please try again.'
          };

        $media_frame_content.html( renderBrowserPlaceholderHTML( renderOptions ) );
      } );

    return false;
  }

  $( document ).on( 'click', '.copy_player', function() {
    var title = $( this ).text().trim() || 'Pick existing player';

    if ( ! window.fv_player_media_browser || ! fv_player_media_browser.open_standalone ) {
      return false;
    }

    fv_player_media_browser.open_standalone( {
      tabId: TAB_ID,
      title: title,
      buttonText: 'Choose',
      frameClass: 'fv-player-picker-frame',
      multiple: false,
      loader: load_players,
      renderSidebar: render_player_sidebar,
      onSelect: on_player_select,
      onClose: abort_request
    } );

    return false;
  } );
} );
