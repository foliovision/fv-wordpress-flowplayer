jQuery( function($) {
  var form = $('[data-fv-player-wizard]'),
    current_step_number = fv_player_wizard_base.first_step_number,
    current_step_wrap = false;
    current_extra_fields = false;
    
    set_current_step_wrap();

    $(document).on( 'click', '[data-fv-player-wizard-next]', function(e) {
      var button_next = $(this),
        indicator = current_step_wrap.find('[data-fv-player-wizard-indicator]');

      clear_field_errors(current_step_wrap);

      var has_required_fields = true
      get_step_fields().each( function() {
        var input = $(this);
        if( !input.val() ) {
          add_field_error('This field is required',input);
          has_required_fields = false;
        }
      });

      if( !has_required_fields ) {
        return;
      }

      button_next.prop('disabled',true);
      indicator.show();

      $.post( ajaxurl, get_step_args(), function(response) {
        button_next.prop('disabled',false);
        indicator.hide();

        if( response.error ) {
          add_field_error( response.error, current_step_wrap.find('[data-fv-player-wizard-next]') );
          return;
        }

        if( response.next_step ) {
          var next_step = get_next_step(current_step_wrap);
          next_step.html(response.next_step);
        }

        if( response.ok ) {
          go_to_next_step();
        } else {
          add_field_error( 'Ajax error', current_step_wrap.find('[data-fv-player-wizard-next]') );
        }

      });

    });
    
    $(document).on( 'click', '[data-fv-player-wizard-prev]', function(e) {
      var button_prev = $(this),
        indicator = current_step_wrap.find('[data-fv-player-wizard-indicator]');

      clear_field_errors(current_step_wrap);

      button_prev.prop('disabled',true);
      indicator.show();

      go_to_prev_step();
    });

    // add error notice for any element, like an input fielld
    function add_field_error(message,field) {
      $('<div class="field-error"><p>'+message+'</p></div>').insertAfter(field);
    }

    // clear all error notices in given wrap, like current_step_wrap
    function clear_field_errors(wrap) {
      wrap.find('.field-error').remove();
      wrap.find('.field-error-row').remove();
    }

    // get field value
    function get_field_val(field) {
      var field = $(field),
        type = field.prop("type");
      
      // if it's checkbox or radio button, it only should give value if checked
      if( type == "checkbox" || type == "radio" ) {
        if( field.prop('checked') ) {
          return field.val();  
        } else {
          return false;
        }
      } else {        
        return field.val();
      }
    }

    function get_next_step( step ) {
      // we use nextAll() and eq() as there might be some additional tags
      // such as scripts in between the step table elements
      return step.nextAll('[data-step]').eq(0);
    }
    
    function get_prev_step( step ) {
      // we use prevAll() and eq() as there might be some additional tags
      // such as scripts in between the step table elements
      return step.prevAll('[data-step]').eq(0);
    }

    function get_step_args() {
      var args = {
        'action': fv_player_wizard_base.id+'_step',
        'nonce': fv_player_wizard_base.nonce,
        'step': current_step_number,
        'step_name': current_step_wrap.data('step_name')
      }

      get_step_fields().each( function(k,v) {
        if( get_field_val(v) ) {
          args[$(v).attr('name')] = get_field_val(v);
        }
      });

      // append fields from some other step if specified
      console.log('current_extra_fields',current_extra_fields);
      $.each(current_extra_fields, function(k,v) {
        var selector = v.replace(/\[/,'\\[').replace(/\]/,'\\]');
        var field = $('[name='+selector+']');

        if( get_field_val(field) ) {
          args[field.attr('name')] = get_field_val(field);
        }
      });

      return args;
    }

    function get_step_fields() {
      return current_step_wrap.find('select, input')
    }

    function go_to_next_step() {
      current_step_wrap.hide();
      var next_step = get_next_step(current_step_wrap);
      current_step_number = next_step.data('step');
      set_current_step_wrap();
      current_step_wrap.show();
    }
    
    function go_to_prev_step() {
      current_step_wrap.hide();
      var prev_step = get_prev_step(current_step_wrap);
      current_step_number = prev_step.data('step');
      set_current_step_wrap();
      current_step_wrap.show();
    }

    function set_current_step_wrap() {
      current_step_wrap = form.find('[data-step='+current_step_number+']');
      current_extra_fields = current_step_wrap.data('extra-fields');
    }

});