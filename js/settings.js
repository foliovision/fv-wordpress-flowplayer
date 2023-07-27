jQuery(function() {
  jQuery('.fv-wordpress-flowplayer-save').on('click', function() {
    var $this = jQuery(this),
      $postbox = $this.closest('.postbox'),
      $inputs = $postbox.find('input, select, textarea'),
      formData  = {}; // settings object that we need to assemble

      // Iterate inputs
      $inputs.each(function(i, input) {

        var $input = jQuery(input),
          name = $input.attr('name'),
          value = $input.val(),
          type = $input.attr('type');

        if ( name && value !== undefined ) {
          // handle checkboxes and radio buttons
          if ( type === 'checkbox' || type === 'radio' && ! $input.is(':checked') ) {
            // skip unchecked checkboxes and radio buttons
            return;
          }

          var keys = name.match(/[^\[\]]+/g);
          var currentObj = formData;

          // we need to create same structure as in $_POST
          for (var i = 0; i < keys.length; i++) {
            // debugger; // eslint-disable-line
            var key = keys[i];

            if (i === keys.length - 1) {
              // last key, handle arrays or regular key-value pairs
              if (/\[\]$/.test(key)) {
                // handle arrays (e.g., popups[])
                key = key.replace(/\[\]$/, '');
                currentObj[key] = currentObj[key] || [];
                currentObj[key].push(value);
              } else {
                // Handle regular key-value pairs
                currentObj[key] = value;
              }
            } else {
              // create nested objects if they don't exist yet
              if (!currentObj[key]) {
                // check if the next key is an array index (e.g., popups[1][html])
                currentObj[key] = /^\d+$/.test(keys[i + 1]) ? [] : {};
              } else if (currentObj[key] && !Array.isArray(currentObj[key])) {
                // convert to array if the key already exists
                currentObj[key] = [currentObj[key]];
              }

            }
          }
        }
      });

      // Send to server
      jQuery.post( window.location.href, formData , function( data ) {
        console.log( data );

        // replace current psotbox with new one

        var $new_postbox = jQuery(data).find('#' + $postbox.attr('id'));

        $postbox.replaceWith($new_postbox);

      }).fail(function() {
        alert("Error: Failed to save settings");
      }).always(function() {
        console.log( "complete" );
      });

  });
});
