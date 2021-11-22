(function($){
$.fn.Progressor = function(args) {
  args = args || {};

  return this.each(function() {
    function timer() {
      if (running) {
        $.ajax({
          url: opts.url,
          cache: false,
          data: ({ action: opts.action, offset: offset, limit: opts.limit, _ajax_nonce: opts.nonce, offset2: $('[name=offset]').val(), verbose: $('[name=verbose]').is(':checked')
          }),
          type: 'POST',
          error: showAlert ,
          success: function(data) {
            var response = JSON.parse(data),
              percent = response.percent_done,
              table_rows = response.table_rows,
              left = response.left;

            if( response.convert_error ) {
              convert_error = true;
            }

            $('#progress').css('width', percent+'%');
            
            $("#output").append(table_rows);

            if (left > 0) {
              // More to come
              offset += opts.limit;
              setTimeout(timer, 0);
            }
            else {
              // Finished
              if( convert_error ) {
                $('#export').show();
              }

              $('#progress').css('width', '100%');

              $(opts.start).val(original);
              $('#loading').hide();
              running = false;
            }
          }
        });
      }
    }

    // Load values from args and merge with defaults
    var opts = $.extend({
      action: 'Action',
      cancel: 'Cancel',
      limit: 20, // limit jobs count
      nonce: '',
    }, args);

    var offset  = 0;
    var running = false;
    var convert_error = false; // track if some job failed
    var wrapper = this;
    var messages = $('#messages');
    var original = $(opts.start).val();
    
    $(opts.start).click(function() {
      var button = this;

      if (running) {
        // Cancel
        running = false;
        $(button).val(original);
        $('#loading').hide();
      }
      else {
        offset = 0;
        running = true;

        $(button).val(opts.cancel);

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