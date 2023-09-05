import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { createElement, RawHTML, useEffect, useState } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck, URLPopover } from '@wordpress/block-editor';
import { SVG, Path, Panel, PanelBody, TextControl, Button, PanelRow } from '@wordpress/components';

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
    cover: {
      type: 'string',
      default: ''
    },
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
    },
    forceUpdate: {
      type: 'string',
      default: '0',
    }
  },
  example: {
    attributes: {
      cover: 'https://cdn.foliovision.com/images/graphics/led-monitor-small.optim.jpg',
    }
  },
  edit: ({ isSelected ,attributes, setAttributes, context, clientId}) => {
    const { src, splash, title, shortcodeContent, player_id, splash_attachment_id } = attributes;

    // interval
    const [count, setCount] = useState(0);

    // debounce
    const [debouncedSrc, setDebouncedSrc] = useState(src);
    const [debouncedTitle, setDebouncedTitle] = useState(title);
    const [debouncedSplash, setDebouncedSplash] = useState(splash);

    // popover
    const [URLPopoverIsOpen, setURLPopoverIsOpen] = useState(false);

    // we need to handle first load
    let firstLoad = true;

    // debounce block ajax
    useEffect(() => {
      const timeoutId = setTimeout(() => {
        if (debouncedSrc !== src || debouncedTitle !== title || debouncedSplash !== splash) {
          setDebouncedSrc(src);
          setDebouncedTitle(title);
          setDebouncedSplash(splash);
          ajaxUpdateAttributes({ ...attributes });
        }
      }, 500);

      return () => {
        clearTimeout(timeoutId);
      };
    }, [src, title, splash]);

    // block interval to load player and resize
    useEffect(() => {
      const intervalId = setInterval(() => {
        fv_player_load();
        fv_flowplayer_safety_resize();
        setCount(count + 1);
      }, 1000);

      return () => {
        clearInterval(intervalId);
      };
    }, [count]);

    // block is being loaded
    useEffect(() => {
      if (firstLoad && player_id > 0) {
        firstLoad = false;
        ajaxUpdateFromDB();
      }
    }, []);

    // used shorctcode editor or media library
    useEffect(() => {
      if( isSelected &&  player_id > 0 && player_id != 'undefined'  ) { // run only when block is selected
        ajaxUpdateAttributes({ ...attributes });
      }
    }, [shortcodeContent, player_id, splash_attachment_id]);

    const ajaxUpdateFromDB = () => {
      const data = new FormData();
      data.append('action', 'fv_player_guttenberg_attributes_load');
      data.append('player_id', player_id);

      // nonce is required for security
      data.append('security', fv_player_gutenberg.nonce);

      fetch(ajaxurl, {
        method: 'POST',
        body: data,
        credentials: 'same-origin',
      })
      .then((response) => response.json())
      .then((data) => {
        if( data.src != 'undefined' && data.splash != 'undefined' && data.title != 'undefined' ) {
          setAttributes({ splash: String(data.splash) });
          setAttributes({ title: String(data.title) });
          setAttributes({ src: String(data.src) });
          setAttributes({ splash_attachment_id: String(data.splash_attachment_id) });
          setAttributes({ forceUpdate: String(Math.random()) });
        }
      })
      .catch((error) => {
        console.error('Error:', error);
      });
    };

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
      })
      .then((response) => response.json())
      .then((data) => {
        if( data.shortcodeContent != 'undefined' && data.player_id != 'undefined' ) {
          //  update the shortcode content and player id
          setAttributes({ shortcodeContent: String(data.shortcodeContent) });
          setAttributes({ player_id: String(data.player_id) });
          setAttributes({ forceUpdate: String(Math.random()) });
        }
      })
      .catch((error) => {
        console.error('Error:', error);
      });
    }

    // show preview for block
    if ( attributes.cover ) {
      return <img src={ attributes.cover } />;
    }

    // show initial state when no player
    if( player_id == 'undefined' || player_id == 0 ) {
      return(
        <div className="components-placeholder block-editor-media-placeholder is-large">
          <div class="components-placeholder__label">
            FV Player
          </div>
          <fieldset class="components-placeholder__fieldset">
            <div className='fv-player-editor-wrapper fv-player-gutenberg'>
              <legend className='components-placeholder__instructions'>{__(' Create a FV new player or select media from your library.', 'fv-player-gutenberg')}</legend>
              <input className='fv-player-gutenberg-client-id' type="hidden" value={clientId} />
              <input
                className="attachement-shortcode fv-player-editor-field"
                type="hidden"
                value=""
              />
              <Button
                className='is-primary fv-player-gutenberg-media'
                onClick={() => {
                  setURLPopoverIsOpen(false);
                }}
                >Select Media</Button>
              <Button
                className="fv-wordpress-flowplayer-button is-secondary"
                onClick={() => {
                  setURLPopoverIsOpen(false);
                }}
                >
                  FV player Editor</Button>
              <Button
                className="is-secondary"
                onClick={() => setURLPopoverIsOpen(!URLPopoverIsOpen)}
                >Video URL</Button>
              {URLPopoverIsOpen && (
                <URLPopover>
                  <form
                    className="block-editor-media-placeholder__url-input-form"
                    onSubmit={(event) => {
                      event.preventDefault();
                      // get input value
                      const input = event.target.querySelector(
                        ".block-editor-media-placeholder__url-input-field, .fv-player-gutenberg-url-input-field"
                      );
                      setAttributes({ src: input.value });
                      setURLPopoverIsOpen(false);
                    }}
                  >
                  <input
                    data-cy="url-input"
                    className="block-editor-media-placeholder__url-input-field fv-player-gutenberg-url-input"
                    type="url"
                    aria-label={__("URL", "fv-player-gutenberg/basic")}
                    placeholder={__(
                      "Add video URL",
                      "fv-player-gutenberg/basic"
                    )}
                  />
                  <Button
                    data-cy="url-submit"
                    className="block-editor-media-placeholder__url-input-submit-button"
                    icon={"editor-break"}
                    label={__("Submit", "fv-player-gutenberg/basic")}
                    type="submit"
                  />
                  </form>
                </URLPopover>
              )}
            </div>
          </fieldset>
        </div>
      )
    }

    return (
      <>
        <InspectorControls>
          <Panel>
            <PanelBody title="Player Settings" initialOpen={true}>
              <PanelRow>
              <TextControl
                label="Source URL"
                className='fv-player-gutenberg-src'
                value={src}
                onChange={(newSrc) => {
                  setAttributes({ src: newSrc });
                }}
              />
              </PanelRow>

              <PanelRow>
              <Button className={ ( src ? 'is-secondary' : 'is-primary' ) + ' fv-player-gutenberg-media' }>Select Media</Button>
              </PanelRow>

              <PanelRow>
              <TextControl
                label="Splash URL"
                className='fv-player-gutenberg-splash'
                value={splash}
                onChange={(newSplash) => {
                  setAttributes({ splash: newSplash });
                }}
              />
              </PanelRow>

              <PanelRow>
              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(attachment) => {
                      setAttributes({ splash: attachment.url });
                      setAttributes({ splash_attachment_id: String(attachment.id) });

                      let newAttributes = { ...attributes, splash: attachment.url };
                      newAttributes.splash_attachment_id = attachment.id;

                      ajaxUpdateAttributes(newAttributes);
                    }
                  }
                  allowedTypes={['image']}
                  render={({ open }) => (
                    <Button onClick={open} className={ splash ? 'is-secondary' : 'is-primary' }>Select Image</Button>
                  )}
                />
              </MediaUploadCheck>
              </PanelRow>

              <PanelRow>
              <TextControl
                label="Title"
                className='fv-player-gutenberg-title'
                value={title}
                onChange={(newTitle) => {
                  setAttributes({ title: newTitle });
                }}
              />
              </PanelRow>

              <PanelRow>
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
                    setAttributes({ shortcodeContent: shortcodeContent });
                  }}
                />
              </div>
              </PanelRow>

            </PanelBody>
          </Panel>
        </InspectorControls>

        <ServerSideRender
          block="fv-player-gutenberg/basic"
          attributes={ attributes }
        />

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