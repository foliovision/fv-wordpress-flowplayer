( function() {
  var el = wp.element.createElement;
  
  wp.blocks.registerBlockType( 'fv-player-gutenberg/basic', {
      title: 'FV Player',
  
      icon: {
        foreground: '#C20B33',
        src: wp.element.createElement(
          wp.components.SVG, {
            viewBox: "0 0 24 24"
          }, wp.element.createElement(wp.components.Path, {
            d: "M21.8 8s-.195-1.377-.795-1.984c-.76-.797-1.613-.8-2.004-.847-2.798-.203-6.996-.203-6.996-.203h-.01s-4.197 0-6.996.202c-.39.046-1.242.05-2.003.846C2.395 6.623 2.2 8 2.2 8S2 9.62 2 11.24v1.517c0 1.618.2 3.237.2 3.237s.195 1.378.795 1.985c.76.797 1.76.77 2.205.855 1.6.153 6.8.2 6.8.2s4.203-.005 7-.208c.392-.047 1.244-.05 2.005-.847.6-.607.795-1.985.795-1.985s.2-1.618.2-3.237v-1.517C22 9.62 21.8 8 21.8 8zM9.935 14.595v-5.62l5.403 2.82-5.403 2.8z"
          })
        )
      },
  
      category: 'layout',
      
      attributes: {
        content: {
          type: 'string',
          source: 'text'
        }
      },
  
      edit: function( props ) {
        var content = props.attributes.content;

        setTimeout( function() {
          console.log('block?',props.clientId,jQuery('[data-block='+props.clientId+']').length);
          fv_player_editor.gutenberg_preview( jQuery('[data-block='+props.clientId+']'),content);
        }, 100 );

        function onChangeContent( newContent ) {
          fv_player_editor.gutenberg_preview(newContent);
          props.setAttributes( { content: newContent } );
        }
        
        return el('div', {
          className: 'fv-player-gutenberg'
        }, el(
            'a',{
              'className': 'button fv-wordpress-flowplayer-button'
            },
            el(
              'span'  
            ),
            'FV Player'
          ),
          el(
            wp.components.TextareaControl,
            {
              'className': 'components-textarea-control__input fv-player-editor-field',
              onChange: onChangeContent,
              value: content,
            }
          ),
          el(
            'div', {
              'className': 'fv-player-gutenberg-preview'
            }
          )
        );
      },
  
      save: function( props ) {
        return el( wp.element.RawHTML,
                  null,
                  props.attributes.content
        );
      },

  } );

}() );