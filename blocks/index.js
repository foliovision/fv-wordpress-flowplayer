import { __ } from '@wordpress/i18n';
import { createElement, RawHTML } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { SVG, Path, PanelBody, TextControl, TextareaControl, Button } from '@wordpress/components';

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
    },
  },
  edit: ({ attributes, setAttributes, clientId }) => {
    const { src, splash, title, shortcodeContent } = attributes;

    return (
      <>
        <InspectorControls>
          <PanelBody title="Player Settings" initialOpen={true}>
            <TextControl
              label="Source URL"
              value={src}
              onChange={(value) => setAttributes({ src: value })}
            />
            <MediaUploadCheck>
              <MediaUpload
                onSelect={(src) => setAttributes({ src: src.url })}
                allowedTypes={['video', 'audio']}
                render={({ open }) => (
                  <Button onClick={open} className='is-primary'>Select Media</Button>
                )}
              />
            </MediaUploadCheck>
            <TextControl
              label="Splash URL"
              value={splash}
              onChange={(value) => setAttributes({ splash: value })}
            />
            <TextControl
              label="Title"
              value={title}
              onChange={(value) => setAttributes({ title: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div className="fv-player-gutenberg">
          <h4>{title}</h4>
          <Button className="fv-wordpress-flowplayer-button is-primary">Open in Editor</Button>
        </div>

        <TextareaControl
            value={shortcodeContent}
            onChange={(value) => setAttributes({ shortcodeContent: value })}
            className="fv-player-editor-field" // Add custom class to the textarea
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
