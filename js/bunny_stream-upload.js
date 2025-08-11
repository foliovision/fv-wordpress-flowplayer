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

  function ajax_file_upload( file, video_guid ) {
    $progressDiv.text("Uploading...");
    fv_player_media_browser.set_upload_status(true);

    window.addEventListener('beforeunload', closeWarning);

    $progressBarDiv.show();

    file_upload_xhr = $.ajax({
      "async": true,
      "crossDomain": true,
      "url": "https://video.bunnycdn.com/library/" + fv_player_bunny_stream_upload_settings.lib_id + "/videos/" + video_guid,
      "method": "PUT",
      "headers": {
        "AccessKey": fv_player_bunny_stream_upload_settings.api_key,
      },
      data: file,
      cache: false,
      contentType: false,
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
    .fail(function( jqXHR, textStatus, errorThrown ) {
      fv_player_media_browser.set_upload_status(false);
      window.removeEventListener('beforeunload', closeWarning);
      $progressDiv.text("Upload failed with server error.");
      $progressBarDiv.hide();
      console.log( jqXHR, textStatus, errorThrown );
    })
    .done(  function( response ) {
      if ( response && typeof( response ) == 'object' && response.success ) {
        // everything worked out fine, file is uploaded
        // reload Bunny Stream tab
        fv_flowplayer_browser_assets_loaders[ fv_player_media_browser.get_active_tab().attr('id') ]( fv_player_media_browser.get_current_bucket() ,fv_player_media_browser.get_current_folder() );
        $progressDiv.text( upload_success_message );
      } else {
        $progressDiv.text( "Upload failed with server error: " + ( response && typeof( response ) == 'object' && response.message ? response.message : response ) );
        $progressBarDiv.hide();
        console.log( response );
      }
      window.removeEventListener('beforeunload', closeWarning);
      fv_player_media_browser.set_upload_status(false);
      file_upload_xhr = null;
      $uploadButton.add( $cancelButton ).toggle();
      recreate_file_input( file_select_input_name, file_select_input_class );
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
      if ( data.result && !data.error && data.result.status != 'error' ) {
        // start the upload process
        ajax_file_upload( file, data.result.result.guid );
      } else {
        $progressDiv.text( "Upload failed with server error: " + (data.error ? data.error : data.result.result.exception ) );
        $progressBarDiv.hide();
        console.log( data.error ? data.error : data.result.result.exception );
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