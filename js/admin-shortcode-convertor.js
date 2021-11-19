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
              msgs = response.status,
              left = response.left;

            console.log('response', response);

            $(wrapper).find('p').html(message);
            $('#' + opts.inside).css('width', percent);
            
            $("#messages").append('<p>'+msgs+'<p>');

            if (left > 0) {
              // More to come
              offset += opts.limit;
              setTimeout(timer, 0);
            }
            else {
              // Finished
              $(opts.start).val(original);
              $(opts.loading).hide();
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
      inside: 'inner',
      limit: 1,
      nonce: '',
      loading: '#loading'
    }, args);

    console.log('ops', opts);

    var offset  = 0;
    var running = false;
    var wrapper = this;
    var messages = $('#messages');
    var original = $(opts.start).val();
    
    $(opts.start).click(function() {
      var button = this;

      if (running) {
        // Cancel
        running = false;
        $(button).val(original);
        $(opts.loading).hide();
      }
      else {
        offset = 0;
        running = true;

        // Hide the button
        $(button).val(opts.cancel);
        $(opts.loading).show();

        // Setup the progress bar
        $(wrapper).empty();
        $(wrapper).append('<div id="' + opts.inside + '"></div><p></p>');
        $(wrapper).fadeIn();
        $('#' + opts.inside).css('width', '0px');
      
        /*$(messages).append('<p>asdf<p>');
        $(messages).fadeIn();*/

        // Now kick-start a timer to perform the progressor
        setTimeout(timer, 0);
      }

      return false;
    });
    });
  };
})(jQuery);

function clearmessages() {
  jQuery("#messages").empty();
}

function showAlert() {
  alert('There is an error')
}