function fv_flowplayer_init_s3_uploader( options ) {
  var
    $ = jQuery,
    $uploadButton,
    $uploadInput,
    $cancelButton,
    $progressDiv,
    $progressBar,
    $progressBarNumber,
    $progressBarDiv,
    s3upload = null,
    file_select_input_name = options.file_select_input_name,
    file_select_input_class = options.file_select_input_class,
    upload_success_message = options.upload_success_message,
    upload_success_callback = options.upload_success_callback;

  function recreate_file_input( input_name, input_class_name ) {
    if ( $uploadInput.length ) {
      $uploadInput.remove();
    }

    $uploadButton.after('<input type="file" accept=".mp4,.mov,.web,.flv,.avi,.vmw,.avchd,.swf,.mkv,.webm.,mpeg,.mpg" class="fv-player-s3-upload-file-input ' + input_class_name + '" name="' + input_name + '" />');

    $uploadInput = $('.media-frame-toolbar .media-toolbar-secondary > .upload_buttons .' + input_class_name);
    $uploadInput.change(function() {
      upload( $uploadInput[0].files[0] );
    });
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

    $uploadButton.add( $cancelButton ).toggle();
    $progressDiv.text('');

    s3upload = new S3MultiUpload( file );
    s3upload.onServerError = function(command, jqXHR, textStatus, errorThrown) {
      $progressDiv.text("Upload failed with server error.");
      $progressBarDiv.hide();
      console.log( command, jqXHR, textStatus, errorThrown );
    };

    s3upload.onS3UploadError = function(xhr) {
      $progressDiv.text("Upload failed.");
      $progressBarDiv.hide();
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
      $progressDiv.text(upload_success_message);
      $uploadButton.add( $cancelButton ).toggle();
      recreate_file_input( file_select_input_name, file_select_input_class );

      if ( typeof( upload_success_callback ) == 'function' ) {
        upload_success_callback( data );
      }
    };

    $progressDiv.text("Preparing upload...");
    s3upload.start();
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

  $(document).on("mediaBrowserOpen", function (event) {
    var
      upload_button_class = options.upload_button_class, //'fv-player-coconut-browser-upload',
      upload_button_text = options.upload_button_text, //'Upload to Coconut',
      cancel_button_class = options.cancel_button_class, //'fv-player-coconut-browser-upload-cancel',
      upload_progress_class = options.upload_progress_class, //'fv-player-coconut-browser-upload-progress',
      upload_progress_bar_enclosure_class = options.upload_progress_bar_enclosure_class, //'fv-player-coconut-progress',
      upload_progress_bar_class = options.upload_progress_bar_class, //'fv-player-coconut-progress-bar',
      upload_progress_bar_number_class = options.upload_progress_bar_number_class; //'fv-player-coconut-progress-number';

    // add Upload to Coconut button to the media library modal
    if ( !$('.' + upload_button_class).length ) {
      if ( !$('.media-frame-toolbar .media-toolbar-secondary > .upload_buttons').length ) {
        $('.media-frame-toolbar .media-toolbar-secondary').append('<div id="'+upload_button_text+'-wrap" class="upload_buttons" style="display: none" data-tab-id="'+options.tab_id+'"></div>');
      }

      // check if we have the correct player version
      if ( !fv_player_coconut_dos_upload_settings.can_use_get_space ) {
        $('.media-frame-toolbar .media-toolbar-secondary > .upload_buttons').append('<button type="button" class="button media-button button-primary button-large ' + upload_button_class + '">' + upload_button_text + '</button>');

        $('.' + upload_button_class).click(function() {
          alert('This functionality requires the latest version of FV Flowplayer. Please update your WordPress plugins.');
        });
        return;
      }

      var $uploadDiv = $('.media-frame-toolbar .media-toolbar-secondary > .upload_buttons');

      var upload_interface = '<div class="fv-player-s3-upload-buttons">'
      upload_interface += '<button type="button" class="button media-button button-primary button-large ' + upload_button_class + '">' + upload_button_text + '</button>';
      upload_interface += '<button type="button" class="button media-button button-primary button-large fv-player-s3-upload-cancel-btn ' + cancel_button_class + '">Cancel Upload</button>';
      if( options.upload_button_extra_html ) {
        upload_interface += options.upload_button_extra_html;
      }
      upload_interface += '</div>';

      upload_interface += '<div class="fv-player-s3-upload-wrap">';
      upload_interface += '<div class="fv-player-s3-upload-progress ' + upload_progress_class +'"></div>';
      upload_interface += '<div class="fv-player-s3-upload-progress-enclosure ' + upload_progress_bar_enclosure_class + '"><div class="fv-player-s3-upload-progress-bar ' + upload_progress_bar_class + '"></div><div class="fv-player-s3-upload-progress-number ' + upload_progress_bar_number_class + '"></div></div>';
      upload_interface += '</div>';

      $('.media-frame-toolbar .media-toolbar-secondary > .upload_buttons').append( upload_interface);

      $uploadButton = $uploadDiv.find('.' + upload_button_class);
      $uploadInput = $uploadDiv.find('.' + file_select_input_class);
      $cancelButton = $uploadDiv.find('.' + cancel_button_class);
      $progressDiv = $uploadDiv.find('.' + upload_progress_class);
      $progressBarDiv = $uploadDiv.find('.' + upload_progress_bar_enclosure_class);
      $progressBar = $uploadDiv.find('.' + upload_progress_bar_class);
      $progressBarNumber = $uploadDiv.find('.' + upload_progress_bar_number_class);
      s3upload = null;

      $progressBar.css('width',"0px");
      $progressBarNumber.text("");

      recreate_file_input( file_select_input_name, file_select_input_class );

      $uploadButton.click(function() {
        $uploadInput.click();
      });

      $cancelButton.click(function() {
        s3upload.cancel();
        $uploadButton.add( $cancelButton ).toggle();
        recreate_file_input( file_select_input_name, file_select_input_class );
        $progressDiv.html('Upload cancelled.');
        $progressBarDiv.hide();
      });
    }
  });

  return {
    update_progress_bar_text: function( txt ) {
      $progressDiv.text( txt );
    },
    hide_progress_bar: function() {
      $progressBarDiv.hide();
    }
  }
}