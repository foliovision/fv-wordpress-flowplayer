(function($){
$.fn.Progressor = function(args) {
  args = args || {};

  return this.each(function() {
    function timer() {
      if (running) {
        $('.conversion-done').hide();

        $.ajax({
          url: opts.url,
          cache: false,
          data: ({
            action: opts.action,
            offset: offset,
            limit: opts.limit,
            _ajax_nonce: opts.nonce,
            offset2: $('[name=offset]').val(),
            verbose: $('[name=verbose]').is(':checked'),
            'make-changes': jQuery('#make-changes').prop('checked')
          }),
          type: 'POST',
          error: showAlert ,
          success: function(data) {
            try {
              var response = JSON.parse(data);
            } catch(e) {
              alert('Error in conversion Ajax, please check the PHP error log');
              $(button).val('Start');
              $('#loading').hide();
              running = false;
              return;
            }

            var percent = response.percent_done,
              table_rows = response.table_rows,
              left = response.left,
              convert_error = false;

            if( response.convert_error ) {
              convert_error = true;
            }

            $('#progress').css('width', percent+'%');

            if( table_rows) {
              $("#output").append(table_rows);
            } else {
              console.log('No table rows');
            }

            if (left > 0) {
              // More to come
              offset = parseInt(offset) + parseInt(opts.limit);
              setTimeout(timer, 0);
            } else {
              // Finished
              $('.conversion-done').show();

              $('#progress').css('width', '100%');

              $('#loading').hide();
              running = false;
            }

            if( !running ) {
              $(opts.start).val(original);

              if( convert_error ) {
                $('#export').show();
              }
            }
          }
        });
      }
    }

    // Load values from args and merge with defaults
    var opts = $.extend({
      action: 'Action',
      cancel: 'Cancel',
      limit: 1, // limit jobs count, TODO: increase to 10
      nonce: '',
    }, args);

    var offset  = 0;
    var running = false;
    var convert_error = false; // track if some job failed
    var wrapper = this;
    var messages = $('#messages');
    var original = $(opts.start).val();
    var button;

    $(opts.start).click(function() {
      button = this;

      if (running) {
        // Cancel
        running = false;
        $(button).val('Stopping');
        $('#loading').hide();
        if( convert_error ) {
          $('#export').show();
        }
      }
      else {
        $("#output").html('');

        offset = 0;
        running = true;

        $(button).val(opts.cancel);

        $('#export').hide();
        $('#loading').show();
        $(wrapper).fadeIn();
        $('#progress').css('width', '0px');

        // Now kick-start a timer to perform the progressor
        setTimeout(timer, 0);
      }

      return false;
    });
    });
  };
})(jQuery);

function showAlert() {
  alert('There is an error')
}
