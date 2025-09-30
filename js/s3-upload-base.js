/*global S3MultiUpload, fv_player_coconut_dos_upload_settings*/

function fv_flowplayer_init_s3_uploader( options ) {
  var
    $ = jQuery,
    $uploadDiv,
    $uploadButton,
    $uploadInput,
    $cancelButton,
    $progressDiv,
    $progressBar,
    $progressBarNumber,
    $progressBarDiv,
    upload_button_class = options.upload_button_class, //'fv-player-coconut-browser-upload',
    wizard_callback = typeof options.wizard_callback == 'function' ? options.wizard_callback : false,
    s3upload = null,
    file_select_input_name = options.file_select_input_name,
    file_select_input_class = options.file_select_input_class,
    upload_start_callback = ( typeof( options.upload_start_callback ) == 'function' ? options.upload_start_callback : function() {} ),
    upload_success_message = options.upload_success_message,
    upload_success_callback = options.upload_success_callback,
    upload_error_callback = ( typeof( options.upload_error_callback ) == 'function' ? options.upload_error_callback : function() {} );
    min_duration = options.min_duration,
    max_duration = options.max_duration,
    vertical_only = options.vertical_only;

  function debug_log( ...args ) {
    console.log( 'FV Player S3 Uploader', ...args );
  }

  function recreate_file_input( input_name, input_class_name ) {
    if ( $uploadInput.length ) {
      $uploadInput.remove();
    }

    $uploadButton.after('<input type="file" accept=".mp4,.mov,.web,.flv,.avi,.vmw,.avchd,.swf,.mkv,.webm.,mpeg,.mpg" class="fv-player-s3-upload-file-input ' + input_class_name + '" name="' + input_name + '" />');

    $uploadInput = $uploadDiv.find( '.' + input_class_name);
    $uploadInput.on( 'change', function() {
      if( wizard_callback ) {
        wizard_callback($uploadInput[0].files[0]);
      } else {
        upload( $uploadInput[0].files[0] );
      }
    });
  }

  function set_upload_status( status ) {
    if ( window.fv_player_media_browser ) {
      window.fv_player_media_browser.set_upload_status( status );
    }
  }

  function upload( file ) {
    if (!(window.File && window.FileReader && window.FileList && window.Blob && window.Blob.prototype.slice)) {
      alert("You are using an unsupported browser. Please update your browser.");
      return;
    }

    if ( !file || typeof( file ) == 'undefined' ) {
      return;
    }

    if ( file.size < 5242880 ) {
      alert('Only files upwards 5MB can be uploaded using this uploader.');
      return;
    }

    set_upload_status(true);

    window.addEventListener('beforeunload', closeWarning);

    $uploadButton.add( $cancelButton ).toggle();
    $progressDiv.text('');

    /**
     * Check video properties prior to video upload.
     * This is not 100% reliable, but if the browser supports the video type,
     * it will get proper duration, width and height. It even supports the video
     * rotation using "displaymatrix: rotation of -90.00 degrees" as reported by
     * ffprobe. 
     */

    // Create a temporary URL for the file
    const videoUrl = URL.createObjectURL(file);

    // Create a video element
    const video = document.createElement('video');
    video.src = videoUrl;

    // Wait for metadata to load
    video.addEventListener('loadedmetadata', () => {
      URL.revokeObjectURL(videoUrl);

      if ( video.duration ) {
        if ( min_duration && min_duration.value && video.duration < min_duration.value ) {
          s3upload.onValidationError( min_duration.msg );
          return;
        } else if ( max_duration && max_duration.value && video.duration > max_duration.value ) {
          s3upload.onValidationError( max_duration.msg );
          return;
        }
      }

      if ( video.videoWidth && video.videoHeight ) {
        if ( vertical_only && vertical_only.value && video.videoWidth > video.videoHeight ) {
          s3upload.onValidationError( vertical_only.msg );
          return;
        }
      }

      upload_start_callback();
      s3upload.start();
    });

    video.addEventListener('error', () => {
      URL.revokeObjectURL(videoUrl);

      upload_start_callback();
      s3upload.start();
    });

    s3upload = new S3MultiUpload( file, options );
    s3upload.onServerError = function(command, jqXHR, textStatus, errorThrown) {
      set_upload_status(false);
      window.removeEventListener('beforeunload', closeWarning);
      $progressDiv.text("Upload failed. " + textStatus );
      $progressBarDiv.hide();
      upload_error_callback();
      console.log( command, jqXHR, textStatus, errorThrown );
    };

    s3upload.onS3UploadError = function(xhr) {
      $progressDiv.text("Upload failed.");
      window.removeEventListener('beforeunload', closeWarning);
      set_upload_status(false);
      $progressBarDiv.hide();
      upload_error_callback();
      console.log( xhr );
    };

    s3upload.onProgressChanged = function(uploadedSize, totalSize, speed) {
      var progress = parseInt(uploadedSize / totalSize * 100, 10);
      $progressBar.css(
        'width',
        progress + '%'
      );

      $progressDiv.text('Uploading...');
      $progressBarNumber.html( getReadableFileSizeString(uploadedSize) + " / "+getReadableFileSizeString(totalSize)
        + " <span style='font-size:smaller'>(at "
        + getReadableFileSizeString(speed)+"ps"
        + ")</span>").css({'margin-left' : -$progressBarNumber.width()/2});

    };

    s3upload.onPrepareCompleted = function() {
      $progressDiv.text("Uploading...");
      $progressBarDiv.show();
    }

    s3upload.onUploadCompleted = function( data ) {
      window.removeEventListener('beforeunload', closeWarning);
      set_upload_status(false);
      $progressDiv.text(upload_success_message);
      $uploadButton.add( $cancelButton ).toggle();
      recreate_file_input( file_select_input_name, file_select_input_class );

      if ( typeof( upload_success_callback ) == 'function' ) {
        upload_success_callback( data );
      }
    };

    s3upload.onValidationError = function( error ) {
      $uploadButton.add( $cancelButton ).toggle();
      recreate_file_input( file_select_input_name, file_select_input_class );

      if ( options.error_container && options.error_container.length ) {
        options.error_container.html( '<p>' + error + '</p>' ).show();
        options.error_container.one( 'click', function() {
          options.error_container.hide();
        });

        $progressDiv.text( '' );

      } else {
        $progressDiv.text( error );
      }

      upload_error_callback();
      $progressBarDiv.hide();
    };

    $progressDiv.text("Preparing upload...");
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

  function closeWarning(e) {
    (e || window.event).returnValue = true; //Gecko + IE
    return true; //Gecko + Webkit, Safari, Chrome etc.
  }

  // Are we loading into a custom element?
  if ( options.container ) {
    debug_log( 'Loading into custom element', options.container );

    // Add wrapper on the button for styling
    if ( ! $uploadDiv || ! $uploadDiv.length ) {
      $uploadDiv = $('<div class="fv-player-upload_buttons"></div>');
      options.container.append( $uploadDiv );
    }

    initUploaderElements( $uploadDiv );

  // Otherwise load for WordPress Media Library modal.
  } else {
    debug_log( 'Loading into WordPress Media Library modal' );

    $(document).on("mediaBrowserOpen", function (event) {
      debug_log( 'Media Browser Open' );

      var container = $('.media-modal .media-frame-toolbar .media-toolbar-secondary');

      // add Upload to Coconut button to the media library modal, use style and data-tab-id for show/hide based on active tab in media-library-browser-base.js
      if ( ! $uploadDiv || ! $uploadDiv.length ) {
        $uploadDiv = $('<div class="fv-player-upload_buttons" style="display: none" data-tab-id="'+options.tab_id+'"></div>');
        container.append( $uploadDiv );
      }

      initUploaderElements( $uploadDiv );
    });
  }

  function initUploaderElements( $uploadDiv ) {

    var
      upload_button_text = options.upload_button_text, //'Upload to Coconut',
      cancel_button_class = options.cancel_button_class, //'fv-player-coconut-browser-upload-cancel',
      upload_progress_class = options.upload_progress_class, //'fv-player-coconut-browser-upload-progress',
      upload_progress_bar_enclosure_class = options.upload_progress_bar_enclosure_class, //'fv-player-coconut-progress',
      upload_progress_bar_class = options.upload_progress_bar_class, //'fv-player-coconut-progress-bar',
      upload_progress_bar_number_class = options.upload_progress_bar_number_class; //'fv-player-coconut-progress-number';

    // add Upload to Coconut button to the media library modal
    if ( ! $uploadButton || ! $uploadButton.length ) {
      $uploadButton = $( '<button type="button" class="button media-button button-primary button-large ' + upload_button_class + '">' + upload_button_text + '</button>' );
      $cancelButton = $( '<button type="button" class="button media-button button-primary button-large fv-player-s3-upload-cancel-btn ' + cancel_button_class + '">Cancel Upload</button>' );

      var buttons = $( '<div class="fv-player-s3-upload-buttons"></div>' );

      buttons.append( $uploadButton );
      buttons.append( $cancelButton );
      
      if( options.upload_button_extra_html ) {
        buttons.append( options.upload_button_extra_html );
      }

      $uploadDiv.append( buttons );

      var upload_interface = '<div class="fv-player-s3-upload-wrap">';
      upload_interface += '<div class="fv-player-s3-upload-progress ' + upload_progress_class +'"></div>';
      upload_interface += '<div class="fv-player-s3-upload-progress-enclosure ' + upload_progress_bar_enclosure_class + '"><div class="fv-player-s3-upload-progress-bar ' + upload_progress_bar_class + '"></div><div class="fv-player-s3-upload-progress-number ' + upload_progress_bar_number_class + '"></div></div>';
      upload_interface += '</div>';

      $uploadDiv.append( upload_interface);

      $uploadInput = $uploadDiv.find('.' + file_select_input_class);
      $progressDiv = $uploadDiv.find('.' + upload_progress_class);
      $progressBarDiv = $uploadDiv.find('.' + upload_progress_bar_enclosure_class);
      $progressBar = $uploadDiv.find('.' + upload_progress_bar_class);
      $progressBarNumber = $uploadDiv.find('.' + upload_progress_bar_number_class);
      s3upload = null;

      $progressBar.css('width',"0px");
      $progressBarNumber.text("");

      recreate_file_input( file_select_input_name, file_select_input_class );

      $uploadButton.on( 'click', function() {
        $uploadInput.trigger( 'click' );
      });

      $cancelButton.on( 'click', function() {
        s3upload.cancel();
        $uploadButton.add( $cancelButton ).toggle();
        recreate_file_input( file_select_input_name, file_select_input_class );
        $progressDiv.html('Upload cancelled.');
        upload_error_callback();
        $progressBarDiv.hide();
      });
    }
  }

  return {
    update_progress_bar_text: function( txt ) {
      $progressDiv.html( txt );
    },
    hide_progress_bar: function() {
      $progressBarDiv.hide();
    },
    upload: upload,
    recreate_file_input: recreate_file_input
  }
}
