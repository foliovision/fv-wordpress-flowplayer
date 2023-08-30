!function(){"use strict";var e={};function t(e){return t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},t(e)}function r(e,r,n){return(r=function(e){var r=function(e,r){if("object"!==t(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var a=n.call(e,"string");if("object"!==t(a))return a;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(e)}(e);return"symbol"===t(r)?r:String(r)}(r))in e?Object.defineProperty(e,r,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[r]=n,e}function n(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function a(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var r=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=r){var n,a,_x,l,o=[],_n=!0,i=!1;try{if(_x=(r=r.call(e)).next,0===t){if(Object(r)!==r)return;_n=!1}else for(;!(_n=(n=_x.call(r)).done)&&(o.push(n.value),o.length!==t);_n=!0);}catch(e){i=!0,a=e}finally{try{if(!_n&&null!=r.return&&(l=r.return(),Object(l)!==l))return}finally{if(i)throw a}}return o}}(e,t)||function(e,t){if(e){if("string"==typeof e)return n(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);return"Object"===r&&e.constructor&&(r=e.constructor.name),"Map"===r||"Set"===r?Array.from(e):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?n(e,t):void 0}}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}e.n=function(t){var r=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(r,{a:r}),r},e.d=function(t,r){for(var n in r)e.o(r,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:r[n]})},e.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)};var l=window.wp.element,o=window.wp.i18n,i=window.wp.blockEditor,c=window.wp.serverSideRender,u=e.n(c),s=window.wp.blocks,p=window.wp.components;function d(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function f(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?d(Object(n),!0).forEach((function(t){r(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):d(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}(0,s.registerBlockType)("fv-player-gutenberg/basic",{icon:{foreground:"#C20B33",src:(0,l.createElement)(p.SVG,{viewBox:"0 0 24 24"},(0,l.createElement)(p.Path,{d:"M21.8 8s-.195-1.377-.795-1.984c-.76-.797-1.613-.8-2.004-.847-2.798-.203-6.996-.203-6.996-.203h-.01s-4.197 0-6.996.202c-.39.046-1.242.05-2.003.846C2.395 6.623 2.2 8 2.2 8S2 9.62 2 11.24v1.517c0 1.618.2 3.237.2 3.237s.195 1.378.795 1.985c.76.797 1.76.77 2.205.855 1.6.153 6.8.2 6.8.2s4.203-.005 7-.208c.392-.047 1.244-.05 2.005-.847.6-.607.795-1.985.795-1.985s.2-1.618.2-3.237v-1.517C22 9.62 21.8 8 21.8 8zM9.935 14.595v-5.62l5.403 2.82-5.403 2.8z"}))},title:(0,o.__)("FV Player","fv-player-gutenberg"),description:(0,o.__)("Embed a video from your Media Library or upload a new one.","fv-player-gutenberg"),category:"media",keywords:["fv player","player","fv","flowplayer","freedomplayer","video","embed","media","stream"],supports:{align:!0},attributes:{cover:{type:"string",default:""},src:{type:"string",default:""},splash:{type:"string",default:""},title:{type:"string",default:""},shortcodeContent:{type:"string",default:"",source:"text"},player_id:{type:"string",default:"0"},splash_attachment_id:{type:"string",default:"0"},forceUpdate:{type:"string",default:"0"}},example:{attributes:{cover:"https://cdn.foliovision.com/images/graphics/led-monitor-small.optim.jpg"}},edit:function(e){var t=e.isSelected,r=e.attributes,n=e.setAttributes,c=(e.context,e.clientId),s=r.src,d=r.splash,m=r.title,y=r.shortcodeContent,b=r.player_id,g=r.splash_attachment_id,v=(0,i.useBlockProps)(),h=a((0,l.useState)(0),2),_=h[0],E=h[1],w=a((0,l.useState)(s),2),S=w[0],O=w[1],j=a((0,l.useState)(m),2),C=j[0],P=j[1],N=a((0,l.useState)(d),2),k=N[0],M=N[1],T=a((0,l.useState)(!1),2),U=T[0],B=T[1],x=!0;(0,l.useEffect)((function(){var e=setTimeout((function(){S===s&&C===m&&k===d||(O(s),P(m),M(d),A(f({},r)))}),500);return function(){clearTimeout(e)}}),[s,m,d]),(0,l.useEffect)((function(){var e=setInterval((function(){fv_player_load(),fv_flowplayer_safety_resize(),E(_+1)}),1e3);return function(){clearInterval(e)}}),[_]),(0,l.useEffect)((function(){x&&b>0&&(x=!1,L())}),[]),(0,l.useEffect)((function(){t&&b>0&&"undefined"!=b&&A(f({},r))}),[y,b,g]);var L=function(){var e=new FormData;e.append("action","fv_player_guttenberg_attributes_load"),e.append("player_id",b),e.append("security",fv_player_gutenberg.nonce),fetch(ajaxurl,{method:"POST",body:e,credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){"undefined"!=e.src&&"undefined"!=e.splash&&"undefined"!=e.title&&(n({splash:String(e.splash)}),n({title:String(e.title)}),n({src:String(e.src)}),n({splash_attachment_id:String(e.splash_attachment_id)}),n({forceUpdate:String(Math.random())}))})).catch((function(e){console.error("Error:",e)}))},A=function(e){var t=new FormData;t.append("action","fv_player_guttenberg_attributes_save"),t.append("player_id",e.player_id),t.append("src",e.src),t.append("splash",e.splash),t.append("title",e.title),t.append("splash_attachment_id",e.splash_attachment_id),t.append("security",fv_player_gutenberg.nonce),fetch(ajaxurl,{method:"POST",body:t,credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){"undefined"!=e.shortcodeContent&&"undefined"!=e.player_id&&(n({shortcodeContent:String(e.shortcodeContent)}),n({player_id:String(e.player_id)}),n({forceUpdate:String(Math.random())}))})).catch((function(e){console.error("Error:",e)}))};return r.cover?(0,l.createElement)("img",{src:r.cover}):"undefined"==b||0==b?(0,l.createElement)("fieldset",{class:"components-placeholder__fieldset"},(0,l.createElement)("div",{className:"fv-player-editor-wrapper fv-player-gutenberg"},(0,l.createElement)("legend",{className:"components-placeholder__instructions"},(0,o.__)(" Create a FV new player or select media from your library.","fv-player-gutenberg")),(0,l.createElement)("input",{className:"fv-player-gutenberg-client-id",type:"hidden",value:c}),(0,l.createElement)("input",{className:"attachement-shortcode fv-player-editor-field",type:"hidden",value:""}),(0,l.createElement)(i.MediaUploadCheck,null,(0,l.createElement)(i.MediaUpload,{onSelect:function(e){n({src:e.url}),A(f(f({},r),{},{src:e.url}))},allowedTypes:["video","audio"],render:function(e){var t=e.open;return(0,l.createElement)(p.Button,{onClick:function(){t(),B(!1)},className:"is-primary"},"Select Media")}})),(0,l.createElement)(p.Button,{className:"fv-wordpress-flowplayer-button is-secondary",onClick:function(){B(!1)}},"FV player Editor"),(0,l.createElement)(p.Button,{className:"is-secondary",onClick:function(){return B(!U)}},"Video URL"),U&&(0,l.createElement)(i.URLPopover,null,(0,l.createElement)("form",{className:"block-editor-media-placeholder__url-input-form",onSubmit:function(e){e.preventDefault();var t=e.target.querySelector(".block-editor-media-placeholder__url-input-field, .fv-player-gutenberg-url-input-field");n({src:t.value}),B(!1)}},(0,l.createElement)("input",{"data-cy":"url-input",className:"block-editor-media-placeholder__url-input-field fv-player-gutenberg-url-input",type:"url","aria-label":(0,o.__)("URL","fv-player-gutenberg/basic"),placeholder:(0,o.__)("Add video URL","fv-player-gutenberg/basic")}),(0,l.createElement)(p.Button,{"data-cy":"url-submit",className:"block-editor-media-placeholder__url-input-submit-button",icon:"editor-break",label:(0,o.__)("Submit","fv-player-gutenberg/basic"),type:"submit"}))))):(0,l.createElement)(l.Fragment,null,(0,l.createElement)(i.InspectorControls,null,(0,l.createElement)(p.Panel,null,(0,l.createElement)(p.PanelBody,{title:"Player Settings",initialOpen:!0},(0,l.createElement)(p.TextControl,{label:"Source URL",className:"fv-player-gutenberg-src",value:s,onChange:function(e){n({src:e})}}),(0,l.createElement)(p.Button,{className:(s?"is-secondary":"is-primary")+" fv-player-gutenberg-media"},"Select Media"),(0,l.createElement)(p.TextControl,{label:"Splash URL",className:"fv-player-gutenberg-splash",value:d,onChange:function(e){n({splash:e})}}),(0,l.createElement)(i.MediaUploadCheck,null,(0,l.createElement)(i.MediaUpload,{onSelect:function(e){n({splash:e.url}),n({splash_attachment_id:String(e.id)});var t=f(f({},r),{},{splash:e.url});t.splash_attachment_id=e.id,A(t)},allowedTypes:["image"],render:function(e){var t=e.open;return(0,l.createElement)(p.Button,{onClick:t,className:d?"is-secondary":"is-primary"},"Select Image")}})),(0,l.createElement)(p.TextControl,{label:"Title",className:"fv-player-gutenberg-title",value:m,onChange:function(e){n({title:e})}}),(0,l.createElement)("div",{className:"fv-player-gutenberg"},(0,l.createElement)("p",null,(0,o.__)("Looking for advanced properties?","fv-player-gutenberg")),(0,l.createElement)(p.Button,{className:"fv-wordpress-flowplayer-button is-primary"},"Open in Editor"),(0,l.createElement)("input",{className:"fv-player-gutenberg-splash-attachement-id",type:"hidden",value:g}),(0,l.createElement)("input",{className:"fv-player-gutenberg-client-id",type:"hidden",value:c}),(0,l.createElement)("input",{className:"fv-player-gutenberg-player-id",type:"hidden",value:b}),(0,l.createElement)("input",{className:"attachement-shortcode fv-player-editor-field",type:"hidden",value:y,onChange:function(){A(r)}}))))),(0,l.createElement)("div",f({},v),(0,l.createElement)(u(),{block:"fv-player-gutenberg/basic",attributes:r})))},save:function(e){return(0,l.createElement)(l.Fragment,null,(0,l.createElement)(l.RawHTML,null,e.attributes.shortcodeContent))}})}();