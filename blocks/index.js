import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { createElement, RawHTML, useEffect, useState } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { SVG, Path, Panel, PanelBody, TextControl, TextareaControl, Popover, Button } from '@wordpress/components';

registerBlockType( 'fv-player-gutenberg/basic', {
  icon: {
    foreground: '#C20B33',
    src: createElement(
      SVG, {
        viewBox: "0 0 24 24"
      }, createElement(Path, {
        d: "M21.8 8s-.195-1.377-.795-1.984c-.76-.797-1.613-.8-2.004-.847-2.798-.203-6.996-.203-6.996-.203h-.01s-4.197 0-6.996.202c-.39.046-1.242.05-2.003.846C2.395 6.623 2.2 8 2.2 8S2 9.62 2 11.24v1.517c0 1.618.2 3.237.2 3.237s.195 1.378.795 1.985c.76.797 1.76.77 2.205.855 1.6.153 6.8.2 6.8.2s4.203-.005 7-.208c.392-.047 1.244-.05 2.005-.847.6-.607.795-1.985.795-1.985s.2-1.618.2-3.237v-1.517C22 9.62 21.8 8 21.8 8zM9.935 14.595v-5.62l5.403 2.82-5.403 2.8z"
      })
    )
  },
  title: __( 'FV Player', 'fv-player-gutenberg' ),
  description: __( 'Embed a video from your Media Library or upload a new one.', 'fv-player-gutenberg' ),
  category: 'media',
  keywords: ['fv player', 'player', 'fv', 'flowplayer', 'freedomplayer', 'video', 'embed', 'media', 'stream'],
  supports: {
    align: true,
  },
  attributes: {
    src: {
      type: 'string',
      default: ''
    },
    splash: {
      type: 'string',
      default: ''
    },
    title: {
      type: 'string',
      default: ''
    },
    shortcodeContent: {
      type: 'string',
      default: '',
      source: 'text'
    },
    player_id: {
      type: 'string',
      default: '0',
    },
    splash_attachment_id: {
      type: 'string',
      default: '0',
    }
  },
  edit: ({ attributes, setAttributes, context, clientId}) => {
    const { src, splash, title, shortcodeContent, player_id, splash_attachment_id } = attributes;
    const blockProps = useBlockProps();

    // block is being loaded
    useEffect(() => {
      // TODO: fetch data from server

      ajaxUpdateAttributes({ ...attributes });
    }, []);

    // block is being updated
    useEffect(() => {
      ajaxUpdateAttributes({ ...attributes });

      setTimeout( function() {
        fv_player_load();
      }, 1000);

      // just in case if the player is not loaded yet
      setTimeout( function() {
        fv_player_load();
      }, 8000);

    }, [src, splash, title, shortcodeContent, player_id, splash_attachment_id]);

    // handle ajax update of attributes
    const ajaxUpdateAttributes = (newAttributes) => {
      const data = new FormData();
      data.append('action', 'fv_player_guttenberg_attributes_save');
      data.append('player_id', newAttributes.player_id);
      data.append('src', newAttributes.src);
      data.append('splash', newAttributes.splash);
      data.append('title', newAttributes.title);
      data.append('splash_attachment_id', newAttributes.splash_attachment_id);

      // nonce is required for security
      data.append('security', fv_player_gutenberg.nonce);

      fetch(ajaxurl, {
        method: 'POST',
        body: data,
        credentials: 'same-origin',
      }).
      then((response) => response.json())
      .then((data) => {
        if (data.error) {
          console.error('Error:', data.error);
          return;
        }

        if(data.info) {
          console.info('Info:', data.info);
          return;
        }

        //  update the shortcode content and player id
        setAttributes({ shortcodeContent: data.shortcodeContent });
        setAttributes({ player_id: data.player_id });
      })
      .catch((error) => {
        console.error('Error:', error);
      });

    }

    return (
      <>
        <InspectorControls>
          <Panel>
            <PanelBody title="Player Settings" initialOpen={true}>
              <TextControl
                label="Source URL"
                className='fv-player-gutenberg-src'
                value={src}
                onChange={(newSrc) => {
                  setAttributes({ src: newSrc });
                  ajaxUpdateAttributes({ ...attributes, src: newSrc });
                }}
              />
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(attachment) => {
                      setAttributes({ src: attachment.url })
                      ajaxUpdateAttributes({ ...attributes, src: attachment.url });
                    }
                  }
                  allowedTypes={['video', 'audio']}
                  render={({ open }) => (
                    <Button onClick={open} className='is-primary'>Select Media</Button>
                  )}
                />
              </MediaUploadCheck>
              <TextControl
                label="Splash URL"
                className='fv-player-gutenberg-splash'
                value={splash}
                onChange={(newSplash) => {
                  setAttributes({ splash: newSplash });
                  ajaxUpdateAttributes({ ...attributes, splash: newSplash });
                }}
              />
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(attachment) => {
                      setAttributes({ splash: attachment.url });
                      setAttributes({ splash_attachment_id: attachment.id });

                      let newAttributes = { ...attributes, splash: attachment.url };
                      newAttributes.splash_attachment_id = attachment.id;

                      ajaxUpdateAttributes(newAttributes);
                    }
                  }
                  allowedTypes={['image']}
                  render={({ open }) => (
                    <Button onClick={open} className='is-primary'>Select Image</Button>
                  )}
                />
              </MediaUploadCheck>
              <TextControl
                label="Title"
                className='fv-player-gutenberg-title'
                value={title}
                onChange={(newTitle) => {
                  setAttributes({ title: newTitle });
                  ajaxUpdateAttributes({ ...attributes, title: newTitle });
                }}
              />
              <div className="fv-player-gutenberg">
                <p>{__('Looking for advanced properties?', 'fv-player-gutenberg')}</p>
                <Button className="fv-wordpress-flowplayer-button is-primary">Open in Editor</Button>
                <input className='fv-player-gutenberg-splash-attachement-id' type="hidden" value={splash_attachment_id} />
                <input className='fv-player-gutenberg-client-id' type="hidden" value={clientId} />
                <input className='fv-player-gutenberg-player-id' type="hidden" value={player_id} />
                <input
                  className="attachement-shortcode fv-player-editor-field"
                  type="hidden"
                  value={shortcodeContent}
                  onChange={() =>{
                    ajaxUpdateAttributes( attributes );
                  }}
                />
              </div>
            </PanelBody>
          </Panel>
        </InspectorControls>

        <div { ...blockProps }>
            <ServerSideRender
              block="fv-player-gutenberg/basic"
              attributes={ attributes }
            />
        </div>

      </>
    );
  },
  save: (props) => {
    return (
      <>
        <RawHTML>{props.attributes.shortcodeContent}</RawHTML>
      </>
    );
  }
})
