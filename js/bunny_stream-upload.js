jQuery( function($) {
  var
    $doc = $( document ),
    $uploadButton,
    $uploadInput,
    $cancelButton,
    $progressDiv,
    $progressBar,
    $progressBarNumber,
    $progressBarDiv,
    file_select_input_name = 'fv-player-bunny_stream-upload-file-select',
    file_select_input_class = 'fv-player-bunny_stream-upload-file-select',
    upload_success_message = 'Upload successful',
    upload_button_class = 'fv-player-bunny_stream-browser-upload',
    file_upload_xhr = null;

  function recreate_file_input( input_name, input_class_name ) {
    if ( $uploadInput.length ) {
      $uploadInput.remove();
    }

    $uploadButton.after('<input type="file" accept=".mp4,.mov,.web,.flv,.avi,.vmw,.avchd,.swf,.mkv,.webm.,mpeg,.mpg" class="fv-player-bunny_stream-upload-file-input ' + input_class_name + '" name="' + input_name + '" />');

    $uploadInput = $('.media-toolbar-secondary > #' + upload_button_class + '-wrap .' + input_class_name);
        $uploadInput.on('change', function() {
            upload( $uploadInput[0].files[0] );
    });
  }

  function getReadableFileSizeString(fileSizeInBytes) {
    var i = -1;
    var byteUnits = [ ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB' ];
    do {
      fileSizeInBytes = fileSizeInBytes / 1024;
      i++;
    } while (fileSizeInBytes > 1024);

    return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
  }

  function calculate_progress( totalSize, uploadedSize ) {
    var progress = parseInt(uploadedSize / totalSize * 100, 10);
    $progressBar.css(
      'width',
      progress + '%'
    );

    $progressDiv.text('Uploading...');
    $progressBarNumber.html( getReadableFileSizeString(uploadedSize) + " / "+getReadableFileSizeString(totalSize) ).css({'margin-left' : -$progressBarNumber.width()/2});
  }

  /**
   * Base64 encoding for the TUS Upload-Metadata header values, they can have UTF-8 characters.
   */
  function tus_metadata_value( value ) {
    var bytes = new TextEncoder().encode( String( value ) ),
      binary = '';

    for ( var i = 0; i < bytes.length; i++ ) {
      binary += String.fromCharCode( bytes[i] );
    }

    return btoa( binary );
  }

  /**
   * Upload the file to Bunny Stream using the TUS resumable upload endpoint.
   *
   * The server creates the video and gives us a signature which only allows uploading into that video
   * and only for a limited time, so the Bunny Stream API key never gets to the browser.
   *
   * @see https://bunny.net/docs/stream/tus-resumable-uploads
   *
   * @param {File}   file  The file to upload
   * @param {object} tus   Upload authorization from the fv_player_bunny_stream_submit AJAX call:
   *                       endpoint, library_id, video_id, expire, signature
   * @param {string} title Video title
   */
  function ajax_file_upload( file, tus, title ) {
    $progressDiv.text("Uploading...");
    fv_player_media_browser.set_upload_status(true);

    window.addEventListener('beforeunload', closeWarning);

    $progressBarDiv.show();

    var auth_headers = {
      'AuthorizationSignature': tus.signature,
      'AuthorizationExpire': tus.expire,
      'VideoId': tus.video_id,
      'LibraryId': tus.library_id,
      'Tus-Resumable': '1.0.0'
    };

    function upload_done() {
      window.removeEventListener('beforeunload', closeWarning);
      fv_player_media_browser.set_upload_status(false);
      file_upload_xhr = null;
      $uploadButton.add( $cancelButton ).toggle();
      recreate_file_input( file_select_input_name, file_select_input_class );
    }

    function upload_failed( jqXHR, textStatus, errorThrown ) {
      window.removeEventListener('beforeunload', closeWarning);
      fv_player_media_browser.set_upload_status(false);
      $progressDiv.text( "Upload failed with server error" + ( jqXHR && jqXHR.status ? ": " + jqXHR.status : "." ) );
      $progressBarDiv.hide();
      console.log( jqXHR, textStatus, errorThrown );
    }

    // 1. Create the upload, Bunny Stream gives us its URL in the Location header
    file_upload_xhr = $.ajax({
      "url": tus.endpoint,
      "method": "POST",
      "headers": $.extend( {}, auth_headers, {
        'Upload-Length': file.size,
        'Upload-Metadata': 'filetype ' + tus_metadata_value( file.type || 'application/octet-stream' ) + ',title ' + tus_metadata_value( title || file.name )
      })
    })
    .fail( upload_failed )
    .done( function( response, textStatus, jqXHR ) {
      var location = jqXHR.getResponseHeader( 'Location' );
      if ( !location ) {
        upload_failed( jqXHR, 'error', 'Missing Location header in the TUS upload creation response' );
        return;
      }

      // 2. Upload the file, the Location is relative, like /tusupload/{id}
      file_upload_xhr = $.ajax({
        "url": new URL( location, tus.endpoint ).href,
        "method": "PATCH",
        "headers": $.extend( {}, auth_headers, {
          'Upload-Offset': 0
        }),
        data: file,
        contentType: 'application/offset+octet-stream',
        processData: false,
        // Custom XMLHttpRequest
        xhr: function () {
          var myXhr = $.ajaxSettings.xhr();
          if ( myXhr.upload ) {
            // For handling the progress of the upload
            myXhr.upload.addEventListener('progress', function (e) {
              if ( e.lengthComputable ) {
                calculate_progress( e.total, e.loaded );
              }
            }, false);
          }

          return myXhr;
        },
      })
      .fail( upload_failed )
      .done( function( response, textStatus, jqXHR ) {
        if ( parseInt( jqXHR.getResponseHeader( 'Upload-Offset' ), 10 ) === file.size ) {
          // everything worked out fine, file is uploaded
          // reload Bunny Stream tab
          fv_flowplayer_browser_assets_loaders[ fv_player_media_browser.get_active_tab().attr('id') ]( fv_player_media_browser.get_current_bucket() ,fv_player_media_browser.get_current_folder() );
          $progressDiv.text( upload_success_message );
        } else {
          $progressDiv.text( "Upload failed with server error: incomplete upload." );
          $progressBarDiv.hide();
          console.log( jqXHR );
        }

        upload_done();
      });
    });
  }

  function upload( file ) {
    if ( !file || typeof( file ) == 'undefined' ) {
      return;
    }

    $uploadButton.add( $cancelButton ).toggle();
    $progressDiv.text('');

    // create the file info on server and ready it for upload
    $.post( ajaxurl, {
      action: 'fv_player_bunny_stream_submit',
      nonce: fv_player_bunny_stream_upload_settings.job_submit_nonce,
      source: file.name,
      target: file.name,
      collection_name: fv_player_media_browser.get_current_folder(),
      no_source_verify: 1,
      ignore_duplicates: 1,
    }).done( function( data ) {
      // check that we have the correct output
      if ( data.result && !data.error && data.result.status != 'error' && data.result.tus ) {
        // start the upload process
        ajax_file_upload( file, data.result.tus, data.result.result.title );
      } else {
        $progressDiv.text( "Upload failed with server error: " + (data.error ? data.error : ( data.result && data.result.result && data.result.result.exception ? data.result.result.exception : 'missing upload authorization' ) ) );
        $progressBarDiv.hide();
        console.log( data.error ? data.error : ( data.result && data.result.result && data.result.result.exception ? data.result.result.exception : 'missing upload authorization' ) );
      }
    }).fail( function( jqXHR, textStatus, errorThrown ) {
      $progressDiv.text( "Upload failed with server error: " + textStatus );
      $progressBarDiv.hide();
      console.log( jqXHR, textStatus, errorThrown );
    });

    $progressDiv.text("Preparing upload...");
  }

  function closeWarning(e) {
    (e || window.event).returnValue = true; //Gecko + IE
    return true; //Gecko + Webkit, Safari, Chrome etc.
  }

  $doc.on("mediaBrowserOpen", function (event) {
    var
      upload_button_text = 'Upload to Bunny Stream',
      cancel_button_class = 'fv-player-bunny_stream-browser-upload-cancel',
      upload_progress_class = 'fv-player-bunny_stream-browser-upload-progress',
      upload_progress_bar_enclosure_class = 'fv-player-bunny_stream-progress',
      upload_progress_bar_class = 'fv-player-bunny_stream-progress-bar',
      upload_progress_bar_number_class = 'fv-player-bunny_stream-progress-number';

    // add the upload button to the media library modal
    if ( !$('.' + upload_button_class).length ) {
      if ( !$('.media-toolbar-secondary > #' + upload_button_class + '-wrap').length ) {
        $('.media-toolbar-secondary').append('<div id="' + upload_button_class + '-wrap" class="fv-player-upload_buttons" style="display: none" data-tab-id="fv_player_bunny_stream_browser_media_tab"></div>');
      }

      var $uploadDiv = $('.media-toolbar-secondary > #' + upload_button_class + '-wrap');

      var upload_interface = '<div class="fv-player-bunny_stream-upload-buttons fv-player-upload-buttons">'
      upload_interface += '<button type="button" class="button media-button button-primary button-large ' + upload_button_class + '">' + upload_button_text + '</button>';
      upload_interface += '<button type="button" class="button media-button button-primary button-large fv-player-bunny_stream-upload-cancel-btn ' + cancel_button_class + '" style="display: none">Cancel Upload</button>';
      upload_interface += '</div>';

      upload_interface += '<div class="fv-player-bunny_stream-upload-wrap fv-player-upload-wrap">';
      upload_interface += '<div class="fv-player-bunny_stream-upload-progress ' + upload_progress_class +' fv-player-upload-progress"></div>';
      upload_interface += '<div class="fv-player-bunny_stream-upload-progress-enclosure ' + upload_progress_bar_enclosure_class + ' fv-player-upload-progress-enclosure"><div class="fv-player-bunny_stream-upload-progress-bar ' + upload_progress_bar_class + ' fv-player-upload-progress-bar"></div><div class="fv-player-bunny_stream-upload-progress-number ' + upload_progress_bar_number_class + ' fv-player-upload-progress-number"></div></div>';
      upload_interface += '</div>';

      $('.media-toolbar-secondary > #' + upload_button_class + '-wrap').append( upload_interface);

      $uploadButton = $uploadDiv.find('.' + upload_button_class);
      $uploadInput = $uploadDiv.find('.' + file_select_input_class);
      $cancelButton = $uploadDiv.find('.' + cancel_button_class);
      $progressDiv = $uploadDiv.find('.' + upload_progress_class);
      $progressBarDiv = $uploadDiv.find('.' + upload_progress_bar_enclosure_class);
      $progressBar = $uploadDiv.find('.' + upload_progress_bar_class);
      $progressBarNumber = $uploadDiv.find('.' + upload_progress_bar_number_class);

      $progressBar.css('width',"0px");
      $progressBarNumber.text("");

      recreate_file_input( file_select_input_name, file_select_input_class );

      $uploadButton.on( 'click', function() {
        $uploadInput.click();
      });

      $cancelButton.on( 'click', function() {
        if ( file_upload_xhr ) {
          file_upload_xhr.abort();
        }
        $uploadButton.add( $cancelButton ).toggle();
        recreate_file_input( file_select_input_name, file_select_input_class );
        $progressDiv.html('Upload cancelled.');
        $progressBarDiv.hide();
      });

      // listen to the drop event on this browser's files area and the media toolbar
      $doc.on('media_browser_drop_event', function( e, tab_id, files ) {
        // check that we've dropped onto our own browser tab
        if ( tab_id == 'fv_player_bunny_stream_browser_media_tab' ) {
          upload( files[0] );
        }
      });
    }
  });
});