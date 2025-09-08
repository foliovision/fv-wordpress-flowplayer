/*global coconut_pending_jobs:writable */

jQuery( function($) {
  var $expert_ui = $('#fv-player-coconut-expert-ui'),
    $expert_submit = $expert_ui.find('[type=submit]'),
    $expert_source = $expert_ui.find('#fv_player_coconut_expert_source'),
    $expert_target = $expert_ui.find('#fv_player_coconut_expert_target'),
    $expert_encryption = $expert_ui.find('[name=fv_player_coconut_expert_encryption]'),
    $expert_trailer = $expert_ui.find('[name=fv_player_coconut_expert_trailer]'),
    $expert_nonce = $expert_ui.find('[name=fv_player_coconut_nonce]');

  jQuery( document ).on( 'heartbeat-send', function ( event, data ) {
    if ( typeof(coconut_pending_jobs) != 'undefined' && coconut_pending_jobs.length > 0 ) {
      data.coconut_pending = coconut_pending_jobs;
    }
  });

  // HeartBeat update row
  $(document).on( 'heartbeat-tick', function ( event, data ) {
    var rows = data.coconut;

    if(typeof rows === 'object' && rows !== null) {
      for (var key in rows) {
        if (rows.hasOwnProperty(key)) {
          var table_row = $('a[data-id="'+key+'"]').closest('tr'); // find row
          if (table_row.length == 0 ) {
            $( rows[key] ).prependTo( $( "#the-list" ) ); // if no row then add new
          } else{
            table_row.replaceWith(rows[key]); // replace with new data
          }
        }
      }
    }

    // update pending jobs
    if( typeof data.coconut_still_pending == 'undefined' ) {
      coconut_pending_jobs = [];
    } else {
      coconut_pending_jobs = data.coconut_still_pending;
    }
  });

  // Delete completed or failed job
  $(document).on('click', '.job-delete', function(e) {
    var
      $e = $(this),
      args = {
      action: 'fv_player_coconut_delete_job',
      nonce: $e.data('nonce'),
      id_row: $e.data('id')
    };

    // console.log(args);

    if ( confirm('Do you want to delete this job? Files will have to be removed manually from:' + $(this).data('message')) ) {
      $.post(ajaxurl, args, function(data) {
        if( data.error ) {
          alert(data.error);
        } else {
          $e.parents('tr:first').remove();
          alert(data.success);
        }
      });
    }

    return false;
  
  });

  function sanitize_target_path(path) {
    path = path.split('/');
    var filename = path[path.length - 1];

    // remove file extension
    if( filename.match(/\./) ) {
      filename = filename.split('.').slice(0, -1).join('.')
    }

    // allow only safe characters
    filename = filename.replace(/[^A-Za-z0-9\-]/gm, '-');
    filename = filename.replace(/-{2,}/gm, '-');
    filename = filename.replace(/^-|-$/gm, '');

    // we're done
    path[path.length - 1] = filename;
    return path.join('/');
  }

  $expert_target.on('blur', function() {
    this.value = sanitize_target_path( this.value );
  });
  
  $expert_target.on('keyup mouseup', function() {
    this.value = this.value.replace(/[^A-Za-z0-9\-]/gm, '-');
  });

  $expert_ui.on( 'submit', function() {
    $expert_submit.prop('disabled',true);
    
    var args = {
      action: 'fv_player_coconut_submit',
      nonce: $expert_nonce.val(),
      source: $expert_source.val(),
      encryption: $expert_encryption.prop('checked') ? 1 : 0,
      trailer: $expert_trailer.prop('checked') ? 1 : 0,
      category_id: $('#fv_player_encoding_categorychecklist input:checked').val(),
      target: $expert_target.val()
    };
    
    $.post(ajaxurl, args, function(data){
      $expert_submit.prop('disabled',false);
      
      if( data.error ) {
        alert(data.error);
      } else {
        var html = $('#the-list', data.html).html();
        if( !coconut_pending_jobs.includes(String(data.id)) ) { // add new job if not in array
          coconut_pending_jobs.push(String(data.id));
          console.log('Pending jobs', coconut_pending_jobs);
        }
        $('#the-list').prepend(html);
      }
    });
    
    return false;
    
  });

  // TODO: This should be done properly, not hardcoded for DigitalOcean Spaces
  var fv_player_uploader;

  $('#wpbody-content').on('click', '.fv-player-bunny_stream-add', function(e) {

    $( document ).one( "click", ".media-button-select", function(event) {
      var
        $e = jQuery('#__assets_browser .selected'),
        filenameDiv = ($e.get(0).tagName == 'TR' ? $e.find('td:first') : $e.find('.filename div'));

      if (filenameDiv.length && filenameDiv.data('link')) {
        $expert_source.val( filenameDiv.data('link') );

        var path = filenameDiv.data('link').split('/');
        $expert_target.val( sanitize_target_path( path[path.length - 1] ) );
        $expert_ui.show();
      }
    });

    //If the uploader object has already been created, reopen the dialog
    if (fv_player_uploader) {
        fv_player_uploader.open();
        return;
    }

    //Extend the wp.media object
    fv_player_uploader = wp.media.frames.file_frame = wp.media({
        title: 'Add Video',
        button: {
            text: 'Choose'
        },
        multiple: false
    });
    
    fv_player_uploader.on('open', function() {
      $( document ).trigger( "mediaBrowserOpen" );
      jQuery('.media-router .media-menu-item').eq(0).click();
      //jQuery('.media-frame-title h1').text(fv_flowplayer_uploader_button.text());
    });

    //When a file is selected, grab the URL and set it as the text field's value
    /*fv_flowplayer_uploader.on('select', function() {
      attachment = fv_flowplayer_uploader.state().get('selection').first().toJSON();
      console.log(attachment);
      $('#fv_player_coconut_expert_source').val(attachment.url);
      $expert_ui.show();
    });*/

    fv_player_uploader.open();

    return false;
  });

});

( function($) {
  var open = false;
  $(document).on('click', '.hover-wrap > a', function() {
    if( open ) open.hide();
    
    open = $(this).next();
    open.show();
    return false;
  });
  
  $(document).on('click', function(e) {
    if( open.length ) {
      if( $(e.target).closest('.hover-details').length ) {
        return;
      }
      open.hide();
    }
  });  
} )(jQuery);

( function($) {
  /*
  Encoding category adding
  */

  // when adding new categories...
  $(document).on('click', '#fv_player_encoding_category-add-submit', function(e) {
    // uncheck anything previously selected
    $('#fv_player_encoding_categorychecklist input[type=checkbox]').prop('checked',false);
    // remove the message to add some category
    $('#fv-player-coconut-category-nag').remove();
  });
  
  // make sure only one category stays selected
  $(document).on('click', '#fv_player_encoding_categorychecklist input[type=checkbox]', function(e) {
    $('#fv_player_encoding_categorychecklist input[type=checkbox]').prop('checked',false);
    $(this).prop('checked','checked');
  });

  // When the media Library is open go to Bunny Stream browser directly
  // But make sure you are on the right screen - FV Player -> Bunny Stream Jobs
  if( location.href.match(/page=fv_player_bunny_stream/) ) {
    $(document).on("mediaBrowserOpen", function () {
      var bunny_stream_browser_link = false;

      // let FV Player's fv_flowplayer_media_browser_add_tab do its job
      // TODO: Should use a proper event
      var bunny_stream_browser_finder = setInterval( function() {
        bunny_stream_browser_link = $('#fv_player_bunny_stream_browser_media_tab');
        if( bunny_stream_browser_link.length ) {
          clearInterval(bunny_stream_browser_finder);

          var height = $('.media-frame-router').height();
          $('.media-frame-router').hide();

          // the list of files uses absolute positioning
          // so when we remove the tabs we move it up a bit
          var original_height = parseInt( $('.media-frame-content').css('top') );
          if( original_height > 80 ) {
            $('.media-frame-content').css('top', parseInt($('.media-frame-content').css('top')) - height );
          }

          // Open DigitalOcean Spaces browser
          setTimeout(function() {
            if( !bunny_stream_browser_link.hasClass('active') ) {
              bunny_stream_browser_link.trigger( 'click' );
            }
          }, 500);      
        }
        
      }, 5 );

      // Give up after a while
      setTimeout( function() {
        clearInterval(bunny_stream_browser_finder);
      }, 2000 );

      // Hide the Choose button because the videos are uploaded with drag&drop
      $('.media-toolbar-primary').html('<button type="button" class="button media-button button-primary button-large" disabled="">Pick your video in FV Player directly</button>');

    });

  }
} )(jQuery);