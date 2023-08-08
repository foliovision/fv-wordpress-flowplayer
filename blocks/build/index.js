!function(){"use strict";var e={};function t(e){return t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},t(e)}function r(e,r,n){return(r=function(e){var r=function(e,r){if("object"!==t(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var a=n.call(e,"string");if("object"!==t(a))return a;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(e)}(e);return"symbol"===t(r)?r:String(r)}(r))in e?Object.defineProperty(e,r,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[r]=n,e}function n(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function a(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var r=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=r){var n,a,_x,o,l=[],_n=!0,i=!1;try{if(_x=(r=r.call(e)).next,0===t){if(Object(r)!==r)return;_n=!1}else for(;!(_n=(n=_x.call(r)).done)&&(l.push(n.value),l.length!==t);_n=!0);}catch(e){i=!0,a=e}finally{try{if(!_n&&null!=r.return&&(o=r.return(),Object(o)!==o))return}finally{if(i)throw a}}return l}}(e,t)||function(e,t){if(e){if("string"==typeof e)return n(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);return"Object"===r&&e.constructor&&(r=e.constructor.name),"Map"===r||"Set"===r?Array.from(e):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?n(e,t):void 0}}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}e.n=function(t){var r=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(r,{a:r}),r},e.d=function(t,r){for(var n in r)e.o(r,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:r[n]})},e.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)};var o=window.wp.element,l=window.wp.i18n,i=window.wp.blockEditor,c=window.wp.serverSideRender,u=e.n(c),s=window.wp.blocks,p=window.wp.components;function f(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function d(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?f(Object(n),!0).forEach((function(t){r(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):f(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}(0,s.registerBlockType)("fv-player-gutenberg/basic",{icon:{foreground:"#C20B33",src:(0,o.createElement)(p.SVG,{viewBox:"0 0 24 24"},(0,o.createElement)(p.Path,{d:"M21.8 8s-.195-1.377-.795-1.984c-.76-.797-1.613-.8-2.004-.847-2.798-.203-6.996-.203-6.996-.203h-.01s-4.197 0-6.996.202c-.39.046-1.242.05-2.003.846C2.395 6.623 2.2 8 2.2 8S2 9.62 2 11.24v1.517c0 1.618.2 3.237.2 3.237s.195 1.378.795 1.985c.76.797 1.76.77 2.205.855 1.6.153 6.8.2 6.8.2s4.203-.005 7-.208c.392-.047 1.244-.05 2.005-.847.6-.607.795-1.985.795-1.985s.2-1.618.2-3.237v-1.517C22 9.62 21.8 8 21.8 8zM9.935 14.595v-5.62l5.403 2.82-5.403 2.8z"}))},title:(0,l.__)("FV Player","fv-player-gutenberg"),description:(0,l.__)("Embed a video from your Media Library or upload a new one.","fv-player-gutenberg"),category:"media",keywords:["fv player","player","fv","flowplayer","freedomplayer","video","embed","media","stream"],supports:{align:!0},attributes:{src:{type:"string",default:""},splash:{type:"string",default:""},title:{type:"string",default:""},shortcodeContent:{type:"string",default:"",source:"text"},player_id:{type:"string",default:"0"},splash_attachment_id:{type:"string",default:"0"},forceUpdate:{type:"integer",default:0}},edit:function(e){var t=e.attributes,r=e.setAttributes,n=(e.context,e.clientId),c=t.src,s=t.splash,f=t.title,y=t.shortcodeContent,m=t.player_id,b=t.splash_attachment_id,v=(0,i.useBlockProps)(),g=a((0,o.useState)(0),2),h=g[0],w=g[1],E=a((0,o.useState)(""),2),_=E[0],S=E[1],O=a((0,o.useState)(""),2),j=O[0],C=O[1],P=a((0,o.useState)(""),2),T=P[0],k=P[1];(0,o.useEffect)((function(){var e=setTimeout((function(){_===c&&j===f&&T===s||(S(c),C(f),k(s),M(d({},t)))}),500);return function(){clearTimeout(e)}}),[c,f,s]),(0,o.useEffect)((function(){var e=setInterval((function(){fv_player_load(),fv_flowplayer_safety_resize(),w(h+1)}),1e3);return function(){clearInterval(e)}}),[h]),(0,o.useEffect)((function(){M(d({},t))}),[]),(0,o.useEffect)((function(){M(d({},t))}),[y,m,b]);var M=function(e){var t=new FormData;t.append("action","fv_player_guttenberg_attributes_save"),t.append("player_id",e.player_id),t.append("src",e.src),t.append("splash",e.splash),t.append("title",e.title),t.append("splash_attachment_id",e.splash_attachment_id),t.append("security",fv_player_gutenberg.nonce),fetch(ajaxurl,{method:"POST",body:t,credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){e.shortcodeContent&&e.player_id&&(r({shortcodeContent:e.shortcodeContent}),r({player_id:e.player_id}),r({forceUpdate:Math.random()}))})).catch((function(e){console.error("Error:",e)}))};return(0,o.createElement)(o.Fragment,null,(0,o.createElement)(i.InspectorControls,null,(0,o.createElement)(p.Panel,null,(0,o.createElement)(p.PanelBody,{title:"Player Settings",initialOpen:!0},(0,o.createElement)(p.TextControl,{label:"Source URL",className:"fv-player-gutenberg-src",value:c,onChange:function(e){r({src:e})}}),(0,o.createElement)(i.MediaUploadCheck,null,(0,o.createElement)(i.MediaUpload,{onSelect:function(e){r({src:e.url}),M(d(d({},t),{},{src:e.url}))},allowedTypes:["video","audio"],render:function(e){var t=e.open;return(0,o.createElement)(p.Button,{onClick:t,className:"is-primary"},"Select Media")}})),(0,o.createElement)(p.TextControl,{label:"Splash URL",className:"fv-player-gutenberg-splash",value:s,onChange:function(e){r({splash:e})}}),(0,o.createElement)(i.MediaUploadCheck,null,(0,o.createElement)(i.MediaUpload,{onSelect:function(e){r({splash:e.url}),r({splash_attachment_id:e.id});var n=d(d({},t),{},{splash:e.url});n.splash_attachment_id=e.id,M(n)},allowedTypes:["image"],render:function(e){var t=e.open;return(0,o.createElement)(p.Button,{onClick:t,className:"is-primary"},"Select Image")}})),(0,o.createElement)(p.TextControl,{label:"Title",className:"fv-player-gutenberg-title",value:f,onChange:function(e){r({title:e})}}),(0,o.createElement)("div",{className:"fv-player-gutenberg"},(0,o.createElement)("p",null,(0,l.__)("Looking for advanced properties?","fv-player-gutenberg")),(0,o.createElement)(p.Button,{className:"fv-wordpress-flowplayer-button is-primary"},"Open in Editor"),(0,o.createElement)("input",{className:"fv-player-gutenberg-splash-attachement-id",type:"hidden",value:b}),(0,o.createElement)("input",{className:"fv-player-gutenberg-client-id",type:"hidden",value:n}),(0,o.createElement)("input",{className:"fv-player-gutenberg-player-id",type:"hidden",value:m}),(0,o.createElement)("input",{className:"attachement-shortcode fv-player-editor-field",type:"hidden",value:y,onChange:function(){M(t)}}))))),(0,o.createElement)("div",d({},v),(0,o.createElement)(u(),{block:"fv-player-gutenberg/basic",attributes:t})))},save:function(e){return(0,o.createElement)(o.Fragment,null,(0,o.createElement)(o.RawHTML,null,e.attributes.shortcodeContent))}})}();