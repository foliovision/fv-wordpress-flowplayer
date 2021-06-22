/*
 * MAILCHIMP FORM
 */
(function($){
  flowplayer(function(api, root) {
    if( jQuery(root).hasClass('is-cva') ) return;

    $(document).on('submit','#' + jQuery(root).attr('id') + ' .mailchimp-form' ,function(e){
      e.preventDefault();
      
      $('.mailchimp-response',root).remove();
      $('input[type=submit]',root).attr('disabled','disabled').addClass('fv-form-loading');

      var data = {action:"fv_wp_flowplayer_email_signup"};
      $('[name]',this).each(function(){
        data[this.name] = $(this).val();
      });
      $.post(fv_player.ajaxurl,data,function( response ) {
        response = JSON.parse(response);
        $('<div class="mailchimp-response"></div>').insertAfter('.mailchimp-form',root);

        if( response.text.match(/already subscribed/) ) {
          response.status = 'ERROR';
        }

        if(response.status === 'OK'){
          $('.mailchimp-form input[type=text],.mailchimp-form input[type=email]',root).val('');
          $('.mailchimp-response',root).removeClass('is-fv-error').html(response.text);

          setTimeout( function() {
            $('.wpfp_custom_popup',root).fadeOut();
          }, 2000 );

        }else{
          $('.mailchimp-response',root).addClass('is-fv-error').html(response.text);
        }
        $('input[type=submit]',root).removeAttr('disabled').removeClass('fv-form-loading');
      });
    });
  });
}(jQuery));