/*!

   Flowplayer Unlimited v7.2.7 (2018-08-13) | flowplayer.com/license

*/
!function(e){function t(e,t,n,r){for(var o,a=n.slice(),s=i(t,e),l=0,u=a.length;u>l&&(handler=a[l],"object"==typeof handler&&"function"==typeof handler.handleEvent?handler.handleEvent(s):handler.call(e,s),!s.stoppedImmediatePropagation);l++);return o=!s.stoppedPropagation,r&&o&&e.parentNode?e.parentNode.dispatchEvent(s):!s.defaultPrevented}function n(e,t){return{configurable:!0,get:e,set:t}}function r(e,t,r){var i=y(t||e,r);m(e,"textContent",n(function(){return i.get.call(this)},function(e){i.set.call(this,e)}))}function i(e,t){return e.currentTarget=t,e.eventPhase=e.target===e.currentTarget?2:3,e}function o(e,t){for(var n=e.length;n--&&e[n]!==t;);return n}function a(){if("BR"===this.tagName)return"\n";for(var e=this.firstChild,t=[];e;)8!==e.nodeType&&7!==e.nodeType&&t.push(e.textContent),e=e.nextSibling;return t.join("")}function s(e){!f&&C.test(document.readyState)&&(f=!f,document.detachEvent(d,s),e=document.createEvent("Event"),e.initEvent(p,!0,!0),document.dispatchEvent(e))}function l(e){for(var t;t=this.lastChild;)this.removeChild(t);null!=e&&this.appendChild(document.createTextNode(e))}function u(t,n){return n||(n=e.event),n.target||(n.target=n.srcElement||n.fromElement||document),n.timeStamp||(n.timeStamp=(new Date).getTime()),n}if(!document.createEvent){var c=!0,f=!1,d="onreadystatechange",p="DOMContentLoaded",h="__IE8__"+Math.random(),g=e.Object,m=g.defineProperty||function(e,t,n){e[t]=n.value},v=g.defineProperties||function(t,n){for(var r in n)if(w.call(n,r))try{m(t,r,n[r])}catch(i){e.console&&console.log(r+" failed on object:",t,i.message)}},y=g.getOwnPropertyDescriptor,w=g.prototype.hasOwnProperty,b=e.Element.prototype,I=e.Text.prototype,M=/^[a-z]+$/,C=/loaded|complete/,A={},S=document.createElement("div");r(e.HTMLCommentElement.prototype,b,"nodeValue"),r(e.HTMLScriptElement.prototype,null,"text"),r(I,null,"nodeValue"),r(e.HTMLTitleElement.prototype,null,"text"),m(e.HTMLStyleElement.prototype,"textContent",function(e){return n(function(){return e.get.call(this.styleSheet)},function(t){e.set.call(this.styleSheet,t)})}(y(e.CSSStyleSheet.prototype,"cssText"))),v(b,{textContent:{get:a,set:l},firstElementChild:{get:function(){for(var e=this.childNodes||[],t=0,n=e.length;n>t;t++)if(1==e[t].nodeType)return e[t]}},lastElementChild:{get:function(){for(var e=this.childNodes||[],t=e.length;t--;)if(1==e[t].nodeType)return e[t]}},previousElementSibling:{get:function(){for(var e=this.previousSibling;e&&1!=e.nodeType;)e=e.previousSibling;return e}},nextElementSibling:{get:function(){for(var e=this.nextSibling;e&&1!=e.nodeType;)e=e.nextSibling;return e}},childElementCount:{get:function(){for(var e=0,t=this.childNodes||[],n=t.length;n--;e+=1==t[n].nodeType);return e}},addEventListener:{value:function(e,n,r){var i,a=this,s="on"+e,l=a[h]||m(a,h,{value:{}})[h],c=l[s]||(l[s]={}),f=c.h||(c.h=[]);if(!w.call(c,"w")){if(c.w=function(e){return e[h]||t(a,u(a,e),f,!1)},!w.call(A,s))if(M.test(e))try{i=document.createEventObject(),i[h]=!0,9!=a.nodeType&&null==a.parentNode&&S.appendChild(a),a.fireEvent(s,i),A[s]=!0}catch(i){for(A[s]=!1;S.hasChildNodes();)S.removeChild(S.firstChild)}else A[s]=!1;(c.n=A[s])&&a.attachEvent(s,c.w)}o(f,n)<0&&f[r?"unshift":"push"](n)}},dispatchEvent:{value:function(e){var n,r=this,i="on"+e.type,o=r[h],a=o&&o[i],s=!!a;return e.target||(e.target=r),s?a.n?r.fireEvent(i,e):t(r,e,a.h,!0):(n=r.parentNode)?n.dispatchEvent(e):!0,!e.defaultPrevented}},removeEventListener:{value:function(e,t,n){var r=this,i="on"+e,a=r[h],s=a&&a[i],l=s&&s.h,u=l?o(l,t):-1;u>-1&&l.splice(u,1)}}}),v(I,{addEventListener:{value:b.addEventListener},dispatchEvent:{value:b.dispatchEvent},removeEventListener:{value:b.removeEventListener}}),v(e.XMLHttpRequest.prototype,{addEventListener:{value:function(e,t,n){var r=this,i="on"+e,a=r[h]||m(r,h,{value:{}})[h],s=a[i]||(a[i]={}),l=s.h||(s.h=[]);o(l,t)<0&&(r[i]||(r[i]=function(){var t=document.createEvent("Event");t.initEvent(e,!0,!0),r.dispatchEvent(t)}),l[n?"unshift":"push"](t))}},dispatchEvent:{value:function(e){var n=this,r="on"+e.type,i=n[h],o=i&&i[r],a=!!o;return a&&(o.n?n.fireEvent(r,e):t(n,e,o.h,!0))}},removeEventListener:{value:b.removeEventListener}}),v(e.Event.prototype,{bubbles:{value:!0,writable:!0},cancelable:{value:!0,writable:!0},preventDefault:{value:function(){this.cancelable&&(this.defaultPrevented=!0,this.returnValue=!1)}},stopPropagation:{value:function(){this.stoppedPropagation=!0,this.cancelBubble=!0}},stopImmediatePropagation:{value:function(){this.stoppedImmediatePropagation=!0,this.stopPropagation()}},initEvent:{value:function(e,t,n){this.type=e,this.bubbles=!!t,this.cancelable=!!n,this.bubbles||this.stopPropagation()}}}),v(e.HTMLDocument.prototype,{textContent:{get:function(){return 11===this.nodeType?a.call(this):null},set:function(e){11===this.nodeType&&l.call(this,e)}},addEventListener:{value:function(t,n,r){var i=this;b.addEventListener.call(i,t,n,r),c&&t===p&&!C.test(i.readyState)&&(c=!1,i.attachEvent(d,s),e==top&&function o(e){try{i.documentElement.doScroll("left"),s()}catch(t){setTimeout(o,50)}}())}},dispatchEvent:{value:b.dispatchEvent},removeEventListener:{value:b.removeEventListener},createEvent:{value:function(e){var t;if("Event"!==e)throw new Error("unsupported "+e);return t=document.createEventObject(),t.timeStamp=(new Date).getTime(),t}}}),v(e.Window.prototype,{getComputedStyle:{value:function(){function e(e){this._=e}function t(){}var n=/^(?:[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|))(?!px)[a-z%]+$/,r=/^(top|right|bottom|left)$/,i=/\-([a-z])/g,o=function(e,t){return t.toUpperCase()};return e.prototype.getPropertyValue=function(e){var t,a,s,l=this._,u=l.style,c=l.currentStyle,f=l.runtimeStyle;return e=("float"===e?"style-float":e).replace(i,o),t=c?c[e]:u[e],n.test(t)&&!r.test(e)&&(a=u.left,s=f&&f.left,s&&(f.left=c.left),u.left="fontSize"===e?"1em":t,t=u.pixelLeft+"px",u.left=a,s&&(f.left=s)),null==t?t:t+""||"auto"},t.prototype.getPropertyValue=function(){return null},function(n,r){return r?new t(n):new e(n)}}()},addEventListener:{value:function(n,r,i){var a,s=e,l="on"+n;s[l]||(s[l]=function(e){return t(s,u(s,e),a,!1)}),a=s[l][h]||(s[l][h]=[]),o(a,r)<0&&a[i?"unshift":"push"](r)}},dispatchEvent:{value:function(t){var n=e["on"+t.type];return n?n.call(e,t)!==!1&&!t.defaultPrevented:!0}},removeEventListener:{value:function(t,n,r){var i="on"+t,a=(e[i]||g)[h],s=a?o(a,n):-1;s>-1&&a.splice(s,1)}}})}}(this),function(e){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=e();else if("function"==typeof define&&define.amd)define([],e);else{var t;t="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,t.flowplayer=e()}}(function(){var e;return function(){function e(t,n,r){function i(a,s){if(!n[a]){if(!t[a]){var l="function"==typeof require&&require;if(!s&&l)return l(a,!0);if(o)return o(a,!0);var u=new Error("Cannot find module '"+a+"'");throw u.code="MODULE_NOT_FOUND",u}var c=n[a]={exports:{}};t[a][0].call(c.exports,function(e){var n=t[a][1][e];return i(n||e)},c,c.exports,e,t,n,r)}return n[a].exports}for(var o="function"==typeof require&&require,a=0;a<r.length;a++)i(r[a]);return i}return e}()({1:[function(e,t,n){"use strict";var r=t.exports={},i=e("class-list"),o=window.jQuery,a=e("punycode"),s=e("computed-style");r.noop=function(){},r.identity=function(e){return e},r.removeNode=function(e){e&&e.parentNode&&e.parentNode.removeChild(e)},r.find=function(e,t){return o?o(e,t).toArray():(t=t||document,Array.prototype.map.call(t.querySelectorAll(e),function(e){return e}))},r.text=function(e,t){e["innerText"in e?"innerText":"textContent"]=t},r.findDirect=function(e,t){return r.find(e,t).filter(function(e){return e.parentNode===t})},r.hasClass=function(e,t){return"string"!=typeof e.className?!1:i(e).contains(t)},r.isSameDomain=function(e){var t=window.location,n=r.createElement("a",{href:e});return t.hostname===n.hostname&&t.protocol===n.protocol&&t.port===n.port},r.css=function(e,t,n){return"object"==typeof t?Object.keys(t).forEach(function(n){r.css(e,n,t[n])}):"undefined"!=typeof n?""===n?e?e.style.removeProperty(t):void 0:e?e.style.setProperty(t,n):void 0:e?s(e,t):void 0},r.createElement=function(e,t,n){try{var i=document.createElement(e);for(var a in t)t.hasOwnProperty(a)&&("css"===a?r.css(i,t[a]):r.attr(i,a,t[a]));return n&&(i.innerHTML=n),i}catch(s){if(!o)throw s;return o("<"+e+">"+n+"</"+e+">").attr(t)[0]}},r.toggleClass=function(e,t,n){if(e){var r=i(e);"undefined"==typeof n?r.toggle(t):n?r.add(t):n||r.remove(t)}},r.addClass=function(e,t){return r.toggleClass(e,t,!0)},r.removeClass=function(e,t){return r.toggleClass(e,t,!1)},r.append=function(e,t){return e.appendChild(t),e},r.appendTo=function(e,t){return r.append(t,e),e},r.prepend=function(e,t){e.insertBefore(t,e.firstChild)},r.insertAfter=function(e,t,n){t==r.lastChild(e)&&e.appendChild(n);var i=Array.prototype.indexOf.call(e.children,t);e.insertBefore(n,e.children[i+1])},r.html=function(e,t){e=e.length?e:[e],e.forEach(function(e){e.innerHTML=t})},r.attr=function(e,t,n){if("class"===t&&(t="className"),r.hasOwnOrPrototypeProperty(e,t))try{e[t]=n}catch(i){if(!o)throw i;o(e).attr(t,n)}else n===!1?e.removeAttribute(t):e.setAttribute(t,n);return e},r.prop=function(e,t,n){return"undefined"==typeof n?e&&e[t]:void(e[t]=n)},r.offset=function(e){var t=e.getBoundingClientRect();return e.offsetWidth/e.offsetHeight>e.clientWidth/e.clientHeight&&(t={left:100*t.left,right:100*t.right,top:100*t.top,bottom:100*t.bottom,width:100*t.width,height:100*t.height}),t},r.width=function(e,t){if(t)return e.style.width=(""+t).replace(/px$/,"")+"px";var n=r.offset(e).width;return"undefined"==typeof n?e.offsetWidth:n},r.height=function(e,t){if(t)return e.style.height=(""+t).replace(/px$/,"")+"px";var n=r.offset(e).height;return"undefined"==typeof n?e.offsetHeight:n},r.lastChild=function(e){return e.children[e.children.length-1]},r.hasParent=function(e,t){for(var n=e.parentElement;n;){if("string"!=typeof t){if(n===t)return!0}else if(r.matches(n,t))return!0;n=n.parentElement}return!1},r.createAbsoluteUrl=function(e){return r.createElement("a",{href:e}).href},r.xhrGet=function(e,t,n){var r=new XMLHttpRequest;r.onreadystatechange=function(){return 4===this.readyState?this.status>=400?n():void t(this.responseText):void 0},r.open("get",e,!0),r.send()},r.pick=function(e,t){var n={};return t.forEach(function(t){e.hasOwnProperty(t)&&(n[t]=e[t])}),n},r.hostname=function(e){return a.toUnicode(e||window.location.hostname)},r.browser={webkit:"WebkitAppearance"in document.documentElement.style},r.getPrototype=function(e){return Object.getPrototypeOf?Object.getPrototypeOf(e):e.__proto__},r.hasOwnOrPrototypeProperty=function(e,t){for(var n=e;n;){if(Object.prototype.hasOwnProperty.call(n,t))return!0;n=r.getPrototype(n)}return!1},r.matches=function(e,t){var n=Element.prototype,r=n.matches||n.matchesSelector||n.mozMatchesSelector||n.msMatchesSelector||n.oMatchesSelector||n.webkitMatchesSelector||function(e){for(var t=this,n=(t.document||t.ownerDocument).querySelectorAll(e),r=0;n[r]&&n[r]!==t;)r++;return n[r]?!0:!1};return r.call(e,t)},function(e){function t(e){return e.replace(/-[a-z]/g,function(e){return e[1].toUpperCase()})}"undefined"!=typeof e.setAttribute&&(e.setProperty=function(e,n){return this.setAttribute(t(e),String(n))},e.getPropertyValue=function(e){return this.getAttribute(t(e))||null},e.removeProperty=function(e){var n=this.getPropertyValue(e);return this.removeAttribute(t(e)),n})}(window.CSSStyleDeclaration.prototype)},{"class-list":36,"computed-style":37,punycode:44}],2:[function(e,t,n){"use strict";var r=e("../common");t.exports=function(e,t,n,i){n=n||"opaque";var o="obj"+(""+Math.random()).slice(2,15),a='<object class="fp-engine" id="'+o+'" name="'+o+'" ',s=navigator.userAgent.indexOf("MSIE")>-1;a+=s?'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">':' data="'+e+'" type="application/x-shockwave-flash">';var l={width:"100%",height:"100%",allowscriptaccess:"always",wmode:n,quality:"high",flashvars:"",movie:e+(s?"?"+o:""),name:o};"transparent"!==n&&(l.bgcolor=i||"#333333"),Object.keys(t).forEach(function(e){l.flashvars+=e+"="+t[e]+"&"}),Object.keys(l).forEach(function(e){a+='<param name="'+e+'" value="'+l[e]+'"/>'}),a+="</object>";var u=r.createElement("div",{},a);return r.find("object",u)},window.attachEvent&&window.attachEvent("onbeforeunload",function(){window.__flash_savedUnloadHandler=window.__flash_unloadHandler=function(){}})},{"../common":1}],3:[function(e,t,n){"use strict";function r(e){return/^https?:/.test(e)}var i,o=e("../flowplayer"),a=e("../common"),s=e("./embed"),l=e("extend-object"),u=e("bean");i=function(e,t){function n(e){function t(e){return("0"+parseInt(e).toString(16)).slice(-2)}return(e=e.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/))?"#"+t(e[1])+t(e[2])+t(e[3]):void 0}function c(e){if(7===e.length)return e;var t=e.split("").slice(1);return"#"+t.map(function(e){return e+e}).join("")}function f(e){return/application\/x-mpegurl/i.test(e.type)}var d,p,h,g,m=e.conf,v=[],y={engineName:i.engineName,pick:function(t){var n=l({},function(){if(o.support.flashVideo){for(var n,r,i=0;i<t.length;i++)if(r=t[i],/mp4|flv|flash/i.test(r.type)&&(n=r),e.conf.swfHls&&/mpegurl/i.test(r.type)&&(n=r),n&&!/mp4/i.test(n.type))return n;return n}}());if(n)return!n.src||r(n.src)||e.conf.rtmp||n.rtmp||(n.src=a.createAbsoluteUrl(n.src)),n},suspendEngine:function(){g=!0},resumeEngine:function(){g=!1},load:function(i){function w(e){return e.replace(/&amp;/g,"%26").replace(/&/g,"%26").replace(/=/g,"%3D")}d=i,v.forEach(function(e){clearTimeout(e)});var b=a.findDirect("video",t)[0]||a.find(".fp-player > video",t)[0],I=i.src,M=r(I),C=function(){a.removeNode(b)},A=function(e){return e.some(function(e){return!!b.canPlayType(e.type)})};o.support.video&&a.prop(b,"autoplay")&&A(i.sources)?u.one(b,"timeupdate",C):C();var S=i.rtmp||m.rtmp;if(M||S||(I=a.createAbsoluteUrl(I)),h&&f(i)&&h.data!==a.createAbsoluteUrl(m.swfHls)&&y.unload(),h){["live","preload","loop"].forEach(function(e){i.hasOwnProperty(e)&&h.__set(e,i[e])}),Object.keys(i.flashls||{}).forEach(function(e){h.__set("hls_"+e,i.flashls[e])});var E=!1;if(!M&&S)h.__set("rtmp",S.url||S);else{var j=h.__get("rtmp");E=!!j,h.__set("rtmp",null)}h.__play(I,E||i.rtmp&&i.rtmp!==m.rtmp)}else{p="fpCallback"+(""+Math.random()).slice(3,15),I=w(I);var D={hostname:m.embedded?a.hostname(m.hostname):a.hostname(location.hostname),url:I,callback:p};t.getAttribute("data-origin")&&(D.origin=t.getAttribute("data-origin")),["proxy","key","autoplay","preload","subscribe","live","loop","debug","splash","poster","rtmpt"].forEach(function(e){m.hasOwnProperty(e)&&(D[e]=m[e]),i.hasOwnProperty(e)&&(D[e]=i[e]),(m.rtmp||{}).hasOwnProperty(e)&&(D[e]=(m.rtmp||{})[e]),(i.rtmp||{}).hasOwnProperty(e)&&(D[e]=(i.rtmp||{})[e])}),m.splash&&(D.autoplay=!0),m.rtmp&&(D.rtmp=m.rtmp.url||m.rtmp),i.rtmp&&(D.rtmp=i.rtmp.url||i.rtmp),Object.keys(i.flashls||{}).forEach(function(e){var t=i.flashls[e];D["hls_"+e]=t});var N="undefined"!=typeof i.hlsQualities?i.hlsQualities:m.hlsQualities;"undefined"!=typeof N&&(D.hlsQualities=N?encodeURIComponent(JSON.stringify(N)):N),void 0!==m.bufferTime&&(D.bufferTime=m.bufferTime),void 0!==m.bufferTimeMax&&(D.bufferTimeMax=m.bufferTimeMax),M&&delete D.rtmp,D.rtmp&&(D.rtmp=w(D.rtmp));var x,L=m.bgcolor||a.css(t,"background-color")||"";0===L.indexOf("rgb")?x=n(L):0===L.indexOf("#")&&(x=c(L)),D.initialVolume=e.volumeLevel;var T=f(i)?m.swfHls:m.swf;h=s(T,D,m.wmode,x)[0];var Z=a.find(".fp-player",t)[0];a.prepend(Z,h),e.off("quality.flashengine").on("quality.flashengine",function(t,n,r){var i="undefined"!=typeof e.video.hlsQualities?e.video.hlsQualities:e.conf.hlsQualities;if(i)try{h.__quality(r)}catch(o){e.debug("Error changing quality in flash engine",o)}}),setTimeout(function(){try{if(!h.PercentLoaded())return e.trigger("error",[e,{code:7,url:m.swf}])}catch(t){}},5e3),v.push(setTimeout(function(){"undefined"==typeof h.PercentLoaded&&e.trigger("flashdisabled",[e])},15e3)),v.push(setTimeout(function(){"undefined"==typeof h.PercentLoaded&&e.trigger("flashdisabled",[e,!1])},500)),e.off("resume.flashhack").on("resume.flashhack",function(){var t=setTimeout(function(){var t=h.__status().time,n=setTimeout(function(){e.playing&&!e.loading&&h.__status().time===t&&e.trigger("flashdisabled",[e])},400);v.push(n),e.one("seek.flashhack pause.flashhack load.flashack",function(){clearTimeout(n)})},800);v.push(t),e.one("progress",function(){clearTimeout(t)})}),h.pollInterval=setInterval(function(){if(h&&!g){var t=h.__status?h.__status():null;t&&((e.conf.live||e.live||i.live)&&(i.seekOffset=t.seekOffset,i.duration=t.duration+t.seekOffset),e.playing&&t.time&&t.time!==e.video.time&&e.trigger("progress",[e,t.time]),i.buffer=t.buffer/i.bytes*i.duration,e.trigger("buffer",[e,i.buffer]),!i.buffered&&t.time>0&&(i.buffered=!0,e.trigger("buffered",[e])))}},250),window[p]=function(n,r){var i=d;m.debug&&(0===n.indexOf("debug")&&r&&r.length?console.log.apply(console,["-- "+n].concat(r)):console.log("--",n,r));var o={type:n};switch(n){case"ready":r=l(i,r);break;case"click":o.flash=!0;break;case"keydown":o.which=r;break;case"seek":i.time=r;break;case"status":e.trigger("progress",[e,r.time]),r.buffer<i.bytes&&!i.buffered?(i.buffer=r.buffer/i.bytes*i.duration,e.trigger("buffer",i.buffer)):i.buffered||(i.buffered=!0,e.trigger("buffered"));break;case"metadata":var a=atob(r);r={key:a.substr(10,4),data:a.substr(21)}}"click"===n||"keydown"===n?(o.target=t,u.fire(t,n,[o])):"buffered"!=n&&"unload"!==n?setTimeout(function(){e.trigger(o,[e,r])},1):"unload"===n&&e.trigger(o,[e,r])}}},speed:a.noop,unload:function(){h&&h.__unload&&h.__unload();try{p&&window[p]&&delete window[p]}catch(n){}a.find("object",t).forEach(a.removeNode),h=0,e.off(".flashengine"),e.off(".flashhack"),clearInterval(h.pollInterval),v.forEach(function(e){clearTimeout(e)})}};return["pause","resume","seek","volume"].forEach(function(t){y[t]=function(n){try{e.ready&&(void 0===n?h["__"+t]():h["__"+t](n))}catch(r){if("undefined"==typeof h["__"+t])return e.trigger("flashdisabled",[e]);throw r}}}),y},i.engineName="flash",i.canPlay=function(e,t){return o.support.flashVideo&&/video\/(mp4|flash|flv)/i.test(e)||o.support.flashVideo&&t.swfHls&&/mpegurl/i.test(e)},o.engines.push(i)},{"../common":1,"../flowplayer":31,"./embed":2,bean:34,"extend-object":39}],4:[function(e,t,n){"use strict";function r(e){return"undefined"==typeof window.Hls?!1:/mpegurl/.test(e)&&window.Hls.isSupported()}var i,o=e("../flowplayer"),a=o.support,s=o.common,l=o.bean,u=e("./html5-factory");i=function(e,t){function n(n,r,u){var d=o.extend({recoverMediaError:!0},e.conf.hlsjs,n.hlsjs);e.engine.hls&&e.engine.hls.destroy();var p=e.engine.hls=new f(d);i.extensions.forEach(function(n){n({hls:p,player:e,root:t,videoTag:r})}),p.loadSource(n.src),u.resume=function(){e.live&&!e.dvr&&(r.currentTime=p.liveSyncPosition||0),r.play()},u.seek=function(t){try{e.live||e.dvr?r.currentTime=Math.min(t,p.liveSyncPosition||r.duration-d.livePositionOffset):r.currentTime=t}catch(n){e.debug("Failed to seek to ",t,n)}},d.bufferWhilePaused===!1&&e.on("pause",function(){p.stopLoad(),e.one("resume",function(){p.startLoad()})}),e.on("quality",function(e,t,n){p.nextLevel=a=n});var h,g,m=function(n){if(e.debug("hlsjs - recovery"),s.removeClass(t,"is-paused"),s.addClass(t,"is-seeking"),l.one(r,"seeked",function(){r.paused&&(s.removeClass(t,"is-poster"),e.poster=!1,r.play()),s.removeClass(t,"is-seeking")}),n)return p.startLoad();var i=performance.now();!h||i-h>3e3?(h=performance.now(),p.recoverMediaError()):(!g||i-g>3e3)&&(g=performance.now(),p.swapAudioCodec(),p.recoverMediaError())};return p.on(f.Events.MANIFEST_PARSED,function(t,i){var o,l=n.hlsQualities||e.conf.hlsQualities,u={},f=i.levels;if(l===!1)return p.attachMedia(r);if("drive"===l)switch(f.length){case 4:o=[1,2,3];break;case 5:o=[1,2,3,4];break;case 6:o=[1,3,4,5];break;case 7:o=[1,3,5,6];break;case 8:o=[1,3,6,7];break;default:o=f.length<3||f[0].height&&f[2].height&&f[0].height===f[2].height?[]:[1,2]}if(n.qualities=[{value:-1,label:"Auto"}],Array.isArray(l)){var d=l.find(function(e){return-1===e||e.level&&-1===e.level});d?n.qualities[0].label="number"!=typeof d?d.label:n.qualities[0].label:n.qualities=[],o=l.map(function(e){return"undefined"!=typeof e.level&&(u[e.level]=e.label),"undefined"!=typeof e.level?e.level:e})}var h=-2;n.qualities=n.qualities.concat(f.map(function(e,t){if(o&&-1===o.indexOf(t))return!1;var n=u[t]||Math.min(e.width,e.height)+"p";return u[t]||"drive"===l||(n+=" ("+Math.round(e.bitrate/1e3)+"k)"),t===a&&(h=t),{value:t,label:n}})).filter(s.identity);var g=n.quality=-2===h?n.qualities[0].value||-1:h;g!==p.currentLevel&&(p.currentLevel=g),p.attachMedia(r),c&&n.src!==c&&r.play(),c=n.src}),p.on(f.Events.ERROR,function(t,n){if(n.fatal)if(d.recoverNetworkError&&n.type===f.ErrorTypes.NETWORK_ERROR)m(!0);else if(d.recoverMediaError&&n.type===f.ErrorTypes.MEDIA_ERROR)m(!1);else{var r=5;n.type===f.ErrorTypes.NETWORK_ERROR&&(r=2),n.type===f.ErrorTypes.MEDIA_ERROR&&(r=3),p.destroy(),e.trigger("error",[e,{code:r}])}}),e.one("unload",function(){p.destroy()}),{handlers:{error:function(e,t){var n=t.error&&t.error.code;return d.recoverMediaError&&3===n||!n?(e.preventDefault(),m(!1),!0):d.recoverNetworkError&&2===n?(e.preventDefault(),m(!0),!0):void 0}}}}var a,c,f=window.Hls;return u("hlsjs-lite",e,t,r,n)},i.canPlay=function(e,t){return t.hlsjs===!1||t.clip&&t.clip.hlsjs===!1?!1:a.browser.safari&&!(t.clip&&t.clip.hlsjs||t.hlsjs||{}).safari?!1:o.support.video&&r(e)},i.engineName="hlsjs-lite",i.plugin=function(e){i.extensions.push(e)},i.extensions=[],o.engines.push(i)},{"../flowplayer":31,"./html5-factory":5}],5:[function(e,t,n){function r(e,t,n,r,a){function f(e,o,a,f){var p=n.getAttribute("data-flowplayer-instance-id");if(e.listeners&&e.listeners.hasOwnProperty(p))return void(e.listeners[p]=a);(e.listeners||(e.listeners={}))[p]=a,u.on(o,"error",function(n){try{r(n.target.getAttribute("type"))&&t.trigger("error",[t,{code:4,video:c(a,{src:e.src,url:e.src})}])}catch(i){}}),t.on("shutdown",function(){u.off(o),u.off(e,".dvrhack"),t.off(".loophack")});var h={},g=function(e){"metadata"===e.kind&&(e.mode="hidden",e.addEventListener("cuechange",function(){e.activeCues.length&&t.trigger("metadata",[t,e.activeCues[0].value])},!1))};return e&&e.textTracks&&e.textTracks.length&&Array.prototype.forEach.call(e.textTracks,g),e&&e.textTracks&&"function"==typeof e.textTracks.addEventListener&&e.textTracks.addEventListener("addtrack",function(e){g(e.track)},!1),(t.conf.dvr||t.dvr||a.dvr)&&u.on(e,"progress.dvrhack",function(){e.seekable.length&&(t.video.duration=e.seekable.end(null),t.video.seekOffset=e.seekable.start(null),t.trigger("dvrwindow",[t,{start:e.seekable.start(null),end:e.seekable.end(null)}]),e.currentTime>=e.seekable.start(null)||(e.currentTime=e.seekable.start(null)))}),Object.keys(d).forEach(function(r){var o=d[r];if("webkitendfullscreen"===r&&t.conf.disableInline&&(o="unload"),o){var u=function(u){if(a=e.listeners[p],u.target&&s.hasClass(u.target,"fp-engine")){/progress/.test(o)||t.debug(r,"->",o,u);var d=function(e){t.trigger(e||o,[t,h])};if(!t.ready&&!/ready|error/.test(o)||!o||!s.find("video",n).length)return void("resume"===o&&t.one("ready",function(){setTimeout(function(){d()})}));var h;if("unload"===o)return void t.unload();switch(o){case"ready":if(t.ready)return t.debug("Player already ready, not sending duplicate ready event");if(!(e.duration&&e.duration!==1/0||t.live))return t.debug("No duration and VOD setup, not sending ready event");if(h=c(a,{duration:e.duration<Number.MAX_VALUE?e.duration:0,width:e.videoWidth,height:e.videoHeight,url:e.currentSrc}),h.seekable=h.duration,t.debug("Ready: ",h),!t.live&&!h.duration&&!l.hlsDuration&&"loadeddata"===r){var g=function(){h.duration=e.duration;try{h.seekable=e.seekable&&e.seekable.end(null)}catch(t){}d(),e.removeEventListener("durationchange",g),s.toggleClass(n,"is-live",!1)};e.addEventListener("durationchange",g);var m=function(){t.ready||e.duration||(h.duration=0,s.addClass(n,"is-live"),d()),e.removeEventListener("timeupdate",m)};return void e.addEventListener("timeupdate",m)}break;case"progress":case"seek":if(e.currentTime>0||t.live)h=Math.max(e.currentTime,0);else if("seek"===o&&0===e.currentTime)h=0;else if("progress"==o)return;break;case"buffer":h=[];for(var v=0;v<e.buffered.length;v++)h.push({start:e.buffered.start(v),end:e.buffered.end(v)});e.buffered.length&&e.buffered.end(null)===e.duration&&d("buffered");break;case"speed":h=i(e.playbackRate);break;case"volume":h=i(e.muted?0:e.volume);break;case"error":try{if(f&&f.handlers&&f.handlers.error){var y=f.handlers.error(u,e);if(y)return}h=(u.srcElement||u.originalTarget).error,h.video=c(a,{src:e.src,url:e.src})}catch(w){return}}d()}};n.addEventListener(r,u,!0),h[r]||(h[r]=[]),h[r].push(u)}}),h}var p,h,g,m=s.findDirect("video",n)[0]||s.find(".fp-player > video",n)[0],v=t.conf;return g={engineName:e,pick:function(e){var t=l.video&&e.filter(function(e){return r(e.type)})[0];if(t)return"string"==typeof t.src&&(t.src=s.createAbsoluteUrl(t.src)),t},load:function(e){var r=s.find(".fp-player",n)[0],i=!1;if(m||(m=document.createElement("video"),s.prepend(r,m),m.autoplay=!!v.splash,i=!0),s.addClass(m,"fp-engine"),s.find("track",m).forEach(s.removeNode),m.preload="none",v.nativesubtitles||s.attr(m,"crossorigin",!1),v.disableInline||(m.setAttribute("webkit-playsinline","true"),m.setAttribute("playsinline","true")),l.inlineVideo||s.css(m,{position:"absolute",top:"-9999em"}),l.subtitles&&v.nativesubtitles&&e.subtitles&&e.subtitles.length){s.addClass(m,"native-subtitles");var c=e.subtitles,d=function(e){var t=m.textTracks;t.length&&(t[0].mode=e)};c.some(function(e){return!s.isSameDomain(e.src)})&&s.attr(m,"crossorigin","anonymous"),"function"==typeof m.textTracks.addEventListener&&m.textTracks.addEventListener("addtrack",function(){d("disabled"),d("showing")}),c.forEach(function(e){m.appendChild(s.createElement("track",{kind:"subtitles",srclang:e.srclang||"en",label:e.label||"en",src:e.src,"default":e["default"]}))})}u.off(m,"timeupdate",s.noop),u.on(m,"timeupdate",s.noop),s.prop(m,"loop",!1),t.off(".loophack"),(e.loop||v.loop)&&t.on("finish.loophack",function(){t.resume()}),"undefined"!=typeof h&&(m.volume=h);var p=a(e,m,g);if(v.autoplay||v.splash||e.autoplay){t.debug("Autoplay / Splash setup, try to start video"),m.load();var y=function(){try{var e=m.play();if(e&&e["catch"]){var n=function(e){if("AbortError"===e.name&&20===e.code)return i?void 0:m.play()["catch"](n);if(!v.mutedAutoplay)throw new Error("Unable to autoplay");return t.debug("Play errored, trying muted",e),t.mute(!0,!0),m.play()};e["catch"](n)["catch"](function(){v.autoplay=!1,t.mute(!1,!0),t.trigger("stop",[t])})}}catch(r){t.debug("play() error thrown",r)}};m.readyState>0?y():u.one(m,"canplay",y)}if(g._listeners=f(m,s.find("source",m).concat(m),e,p)||g._listeners,!(v.autoplay||v.splash||e.autoplay)){var w=function(){o(n)&&(t.debug("player is in viewport, preload"),l.preloadMetadata?m.preload="metadata":m.load(),u.off(document,"scroll.preloadviewport"))};u.off(document,"scroll.preloadviewport"),u.on(document,"scroll.preloadviewport",function(){window.requestAnimationFrame(w)}),w()}},mute:function(e){m.muted=!!e,t.trigger("mute",[t,e]),t.trigger("volume",[t,e?0:m.volume])},pause:function(){m.pause()},resume:function(){m.play()},speed:function(e){m.playbackRate=e},seek:function(e){var n=m.paused||t.finished;try{m.currentTime=e,n&&u.one(m,"seeked",function(){m.pause()})}catch(r){}},volume:function(e){h=e,m&&(m.volume=e,e&&g.mute(!1))},unload:function(){u.off(document,"scroll.preloadviewport"),s.find("video.fp-engine",n).forEach(function(e){"MediaSource"in window?e.src=URL.createObjectURL(new MediaSource):e.src="",s.removeNode(e)}),p=clearInterval(p);var e=n.getAttribute("data-flowplayer-instance-id");delete m.listeners[e],m=0,g._listeners&&Object.keys(g._listeners).forEach(function(e){g._listeners[e].forEach(function(t){n.removeEventListener(e,t,!0)})})}}}function i(e,t){return t=t||100,Math.round(e*t)/t}function o(e){var t=e.getBoundingClientRect();return t.top>=0&&t.left>=0&&t.bottom<=(window.innerHeight||document.documentElement.clientHeight)+t.height&&t.right<=(window.innerWidth||document.documentElement.clientWidth)+t.width}var a=e("../flowplayer"),s=a.common,l=a.support,u=a.bean,c=a.extend,f=l.browser.safari&&!l.iOS,d={ended:"finish",pause:"pause",play:"resume",timeupdate:"progress",volumechange:"volume",ratechange:"speed",seeked:"seek",loadedmetadata:f?0:"ready",canplaythrough:f?"ready":0,durationchange:"ready",error:"error",dataunavailable:"error",webkitendfullscreen:!a.support.inlineVideo&&"unload",progress:"buffer"};t.exports=r},{"../flowplayer":31}],6:[function(e,t,n){"use strict";function r(e){return/mpegurl/i.test(e)?"application/x-mpegurl":e}function i(e){return/^(video|application)/i.test(e)||(e=r(e)),!!u.canPlayType(e).replace("no","")}var o,a=e("../flowplayer"),s=a.common,l=e("./html5-factory"),u=document.createElement("video");o=function(e,t){return l("html5",e,t,i,function(e,t){t.currentSrc!==e.src?(s.find("source",t).forEach(s.removeNode),t.src=e.src,t.type=e.type):e.autoplay&&t.load()})},o.canPlay=function(e){return a.support.video&&i(e)},o.engineName="html5",a.engines.push(o)},{"../flowplayer":31,"./html5-factory":5}],7:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("../common"),o=e("bean");r(function(e,t){e.on("ready",function(){var e=i.find("video.fp-engine",t)[0];e&&(e.setAttribute("x-webkit-airplay","allow"),window.WebKitPlaybackTargetAvailabilityEvent&&(e.addEventListener("webkitplaybacktargetavailabilitychanged",function(e){if("available"===e.availability){var n=i.find(".fp-header",t)[0];i.find(".fp-airplay",n).forEach(i.removeNode);var r=i.createElement("a",{"class":"fp-airplay fp-icon",title:"Play on AirPlay device"});n.insertBefore(r,i.find(".fp-fullscreen",n)[0])}}),e.addEventListener("webkitcurrentplaybacktargetiswirelesschanged",function(){var n=i.find(".fp-airplay",t)[0];n&&i.toggleClass(n,"fp-active",e.webkitCurrentPlaybackTargetIsWireless)})))}),o.on(t,"click",".fp-airplay",function(e){e.preventDefault();var n=i.find("video.fp-engine",t)[0];n.webkitShowPlaybackTargetPicker()})})},{"../common":1,"../flowplayer":31,bean:34}],8:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("./resolve").TYPE_RE,o=e("scriptjs"),a=e("bean");r(function(e,t){var n,r=e.conf.analytics,s=0,l=0;if(r){"undefined"==typeof _gat&&o("//google-analytics.com/ga.js");var u=function(){var e=_gat._getTracker(r);return e._setAllowLinker(!0),e},c=function(r,o,a){if(a=a||e.video,s&&"undefined"!=typeof _gat){var l=u();l._trackEvent("Video / Seconds played",e.engine.engineName+"/"+a.type,a.title||t.getAttribute("title")||a.src.split("/").slice(-1)[0].replace(i,""),Math.round(s/1e3)),s=0,n&&(clearTimeout(n),n=null)}};e.bind("load unload",c).bind("progress",function(){e.seeking||(s+=l?+new Date-l:0,l=+new Date),n||(n=setTimeout(function(){n=null;var e=u();e._trackEvent("Flowplayer heartbeat","Heartbeat","",0,!0)},6e5))}).bind("pause",function(){l=0}),e.bind("shutdown",function(){a.off(window,"unload",c)}),a.on(window,"unload",c)}})},{"../flowplayer":31,"./resolve":21,bean:34,scriptjs:45}],9:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("../common"),o=e("bean"),a=e("scriptjs");r(function(e,t){function n(){var e,t,n;e=g.applicationId||chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID,t=new chrome.cast.SessionRequest(e),n=new chrome.cast.ApiConfig(t,r,s),chrome.cast.initialize(n,l,u)}function r(){console.log("sessionListener")}function s(e){e===chrome.cast.ReceiverAvailability.AVAILABLE&&c()}function l(){}function u(){console.log("onError")}function c(){var e=i.find(".fp-header",t)[0];if(e){i.find(".fp-chromecast",e).forEach(i.removeNode),i.find(".fp-chromecast-engine",t).forEach(i.removeNode),h=i.createElement("a",{"class":"fp-chromecast fp-icon",title:"Play on Cast device"}),e.insertBefore(h,i.find(".fp-fullscreen",e)[0]);var n=i.createElement("div",{"class":"fp-chromecast-engine"}),r=i.createElement("p",{"class":"fp-chromecast-engine-status"}),o=i.createElement("p",{
"class":"fp-chromecast-engine-icon"});n.appendChild(o),n.appendChild(r);var a=i.find(".fp-engine",t)[0];a?a.parentNode.insertBefore(n,a):i.prepend(i.find(".fp-player",t)[0]||t,n)}}function f(){clearInterval(p),p=null,e.release(),i.toggleClass(t,"is-chromecast",!1),i.toggleClass(h,"fp-active",!1)}if(e.conf.chromecast!==!1){a("https://www.gstatic.com/cv/js/sender/v1/cast_sender.js"),window.__onGCastApiAvailable=function(e){e&&n()};var d,p,h,g=e.conf.chromecast||{};o.on(t,"click",".fp-chromecast",function(n){return n.preventDefault(),d?(e.trigger("pause",[e]),d.stop(),d=null,void f()):(e.playing&&e.pause(),void chrome.cast.requestSession(function(n){function r(n){n.addUpdateListener(function(r){if(d){p=p||setInterval(function(){e.trigger("progress",[e,n.getEstimatedTime()])},500),r?(i.toggleClass(t,"is-chromecast",!0),i.toggleClass(h,"fp-active",!0),e.hijack({pause:function(){n.pause()},resume:function(){n.play()},seek:function(e){var t=new chrome.cast.media.SeekRequest;t.currentTime=e,n.seek(t)}})):(f(),e.trigger("finish",[e]));var o=n.playerState;e.paused&&o===chrome.cast.media.PlayerState.PLAYING&&e.trigger("resume",[e]),e.playing&&o===chrome.cast.media.PlayerState.PAUSED&&e.trigger("pause",[e]),i.toggleClass(t,"is-loading",o===chrome.cast.media.PlayerState.BUFFERING)}})}d=n;var o=d.receiver.friendlyName;i.html(i.find(".fp-chromecast-engine-status")[0],"Playing on device "+o);var a=new chrome.cast.media.MediaInfo(e.video.src),s=new chrome.cast.media.LoadRequest(a);d.loadMedia(s,r,function(){})},function(e){console.error("requestSession error",e)}))})}})},{"../common":1,"../flowplayer":31,bean:34,scriptjs:45}],10:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("../common"),o=e("bean");r(function(e,t){function n(e){t.className=t.className.replace(a," "),e>=0&&i.addClass(t,"cue"+e)}function r(t){var n=t&&!isNaN(t.time)?t.time:t;return 0>n&&(n=e.video.duration+n),.125*Math.round(n/.125)}var a=/ ?cue\d+ ?/,s=!1,l={},u=-.125,c=function(t){n(t.index),e.trigger("cuepoint",[e,t])};e.on("progress",function(e,t,n){if(!s)for(var i=r(n);i>u;)u+=.125,l[u]&&l[u].forEach(c)}).on("unload",n).on("beforeseek",function(e){setTimeout(function(){e.defaultPrevented||(s=!0)})}).on("seek",function(e,t,i){n(),u=r(i||0)-.125,s=!1,!i&&l[0]&&l[0].forEach(c)}).on("ready",function(t,n,r){u=-.125;var i=r.cuepoints||e.conf.cuepoints||[];e.setCuepoints(i)}).on("finish",function(){for(var t=r(e.video.duration);t>u;)u+=.125,l[u]&&l[u].forEach(c);u=-.125}),e.conf.generate_cuepoints&&e.bind("load",function(){i.find(".fp-cuepoint",t).forEach(i.removeNode)}),e.setCuepoints=function(t){return e.cuepoints=[],l={},t.forEach(e.addCuepoint),e},e.addCuepoint=function(n){e.cuepoints||(e.cuepoints=[]),"number"==typeof n&&(n={time:n}),n.index=0;var a=r(n);if(l[a]||(l[a]=[]),l[a].push(n),e.cuepoints.length&&(n.index=Math.max.apply(null,e.cuepoints.map(function(e){return e.index}))+1),e.cuepoints.push(n),e.conf.generate_cuepoints&&n.visible!==!1){var s=e.video.duration,u=i.find(".fp-timeline",t)[0];i.css(u,"overflow","visible");var c=n.time||n;0>c&&(c=s+c);var f=i.createElement("a",{className:"fp-cuepoint fp-cuepoint"+n.index});i.css(f,"left",c/s*100+"%"),u.appendChild(f),o.on(f,"mousedown",function(t){t.preventDefault(),t.stopPropagation(),e.seek(c)})}return e},e.removeCuepoint=function(n){"number"==typeof n&&(n=e.cuepoints.filter(function(e){return e.index===n})[0]);var o=e.cuepoints.indexOf(n),a=r(n);if(-1!==o){e.cuepoints=e.cuepoints.slice(0,o).concat(e.cuepoints.slice(o+1));var s=i.find(".fp-timeline",t)[0];i.find(".fp-cuepoint"+n.index,s).forEach(i.removeNode);var u=l[a].indexOf(n);if(-1!==u)return l[a]=l[a].slice(0,u).concat(l[a].slice(u+1)),e}}})},{"../common":1,"../flowplayer":31,bean:34}],11:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("bean"),o=e("../common"),a=e("./util/clipboard");r(function(e,t){if(e.conf.embed!==!1&&e.conf.share!==!1){var n=o.find(".fp-share-menu",t)[0],r=o.createElement("a",{"class":"fp-icon fp-embed",title:"Copy to your site"},"Embed");o.append(n,r),e.embedCode=function(){var n=e.conf.embed||{},r=e.video,i=n.width||r.width||o.width(t),a=n.height||r.height||o.height(t),s=e.conf.ratio,l='<iframe src="'+e.shareUrl(!0)+'" allowfullscreen style="border:none;';return n.width||n.height?(isNaN(i)||(i+="px"),isNaN(a)||(a+="px"),l+"width:"+i+";height:"+a+';"></iframe>'):((!s||e.conf.adaptiveRatio)&&(s=a/i),'<div style="position:relative;width:100%;display:inline-block;">'+l+'position:absolute;top:0;left:0;width:100%;height:100%;"></iframe><div style="padding-top:'+100*s+'%;"></div></div>')},i.on(t,"click",".fp-embed",function(){a(e.embedCode(),function(){e.message("The embed code is now on your clipboard",2e3)},function(){e.textarea(e.embedCode(),"Copy the code below to embed your video")})})}})},{"../common":1,"../flowplayer":31,"./util/clipboard":30,bean:34}],12:[function(e,t,n){"use strict";t.exports=function(e,t){t||(t=document.createElement("div"));var n={},r={},i=function(e,i,o){var a=e.split(".")[0],s=function(l){o&&(t.removeEventListener(a,s),n[e].splice(n[e].indexOf(s),1));var u=[l].concat(r[l.timeStamp+l.type]||[]);i&&i.apply(void 0,u)};t.addEventListener(a,s),n[e]||(n[e]=[]),n[e].push(s)};e.on=e.bind=function(t,n){var r=t.split(" ");return r.forEach(function(e){i(e,n)}),e},e.one=function(t,n){var r=t.split(" ");return r.forEach(function(e){i(e,n,!0)}),e};var o=function(e,t){return 0===t.filter(function(t){return-1===e.indexOf(t)}).length};e.off=e.unbind=function(r){var i=r.split(" ");return i.forEach(function(e){var r=e.split(".").slice(1),i=e.split(".")[0];Object.keys(n).filter(function(e){var t=e.split(".").slice(1);return(!i||0===e.indexOf(i))&&o(t,r)}).forEach(function(e){var r=n[e],i=e.split(".")[0];n[e]=r.filter(function(e){return t.removeEventListener(i,e),!1})})}),e},e.trigger=function(n,i,o){if(n){i=(i||[]).length?i||[]:[i];var a,s=document.createEvent("Event");return a=n.type||n,s.initEvent(a,!1,!0),Object.defineProperty&&(s.preventDefault=function(){Object.defineProperty(this,"defaultPrevented",{get:function(){return!0}})}),r[s.timeStamp+s.type]=i,t.dispatchEvent(s),o?s:e}}},t.exports.EVENTS=["beforeseek","disable","error","finish","fullscreen","fullscreen-exit","load","mute","pause","progress","ready","resume","seek","speed","stop","unload","volume","boot","shutdown"]},{}],13:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("../common"),o=e("bean");r(function(e,t){var n=e.conf;if(n.share!==!1&&n.facebook){e.facebook=function(){var e,t,r=550,i=420,o=screen.height,a=screen.width,s="scrollbars=yes,resizable=yes,toolbar=no,location=yes",l="string"==typeof n.facebook?n.facebook:window.location.toString();e=Math.round(a/2-r/2),t=0,o>i&&(t=Math.round(o/2-i/2)),window.open("https://www.facebook.com/sharer.php?s=100&p[url]="+encodeURIComponent(l),"sharer",s+",width="+r+",height="+i+",left="+e+",top="+t)};var r=i.find(".fp-share-menu",t)[0],a=i.createElement("a",{"class":"fp-icon fp-facebook"},"Facebook");i.append(r,a),o.on(t,"click",".fp-facebook",function(){e.facebook()})}})},{"../common":1,"../flowplayer":31,bean:34}],14:[function(e,t,n){"use strict";var r,i=e("../flowplayer"),o=e("bean"),a=e("../common"),s="fullscreen",l="fullscreen-exit",u=i.support.fullscreen;o.on(document,"fullscreenchange.ffscr webkitfullscreenchange.ffscr mozfullscreenchange.ffscr MSFullscreenChange.ffscr",function(e){var t=document.webkitCurrentFullScreenElement||document.mozFullScreenElement||document.fullscreenElement||document.msFullscreenElement||e.target;if(r||t.parentNode&&t.parentNode.getAttribute("data-flowplayer-instance-id")){var n=r||i(t.parentNode);t&&!r?r=n.trigger(s,[n]):(r.trigger(l,[r]),r=null)}}),i(function(e,t){var n=a.createElement("div",{className:"fp-player"});if(Array.prototype.map.call(t.children,a.identity).forEach(function(e){a.matches(e,".fp-ratio,script")||n.appendChild(e)}),t.appendChild(n),e.conf.fullscreen){var i,o,c=window;e.isFullscreen=!1,e.fullscreen=function(t){return e.disabled?void 0:(void 0===t&&(t=!e.isFullscreen),t&&(i=c.scrollY,o=c.scrollX),u?t?["requestFullScreen","webkitRequestFullScreen","mozRequestFullScreen","msRequestFullscreen"].forEach(function(e){"function"==typeof n[e]&&(n[e](Element.ALLOW_KEYBOARD_INPUT),"webkitRequestFullScreen"!==e||document.webkitFullscreenElement||n[e]())}):["exitFullscreen","webkitCancelFullScreen","mozCancelFullScreen","msExitFullscreen"].forEach(function(e){"function"==typeof document[e]&&document[e]()}):e.trigger(t?s:l,[e]),e)};var f;e.on("mousedown.fs",function(){+new Date-f<150&&e.ready&&e.fullscreen(),f=+new Date}),e.on(s,function(){a.addClass(t,"is-fullscreen"),a.toggleClass(t,"fp-minimal-fullscreen",a.hasClass(t,"fp-minimal")),a.removeClass(t,"fp-minimal"),u||a.css(t,"position","fixed"),e.isFullscreen=!0}).on(l,function(){var n;a.toggleClass(t,"fp-minimal",a.hasClass(t,"fp-minimal-fullscreen")),a.removeClass(t,"fp-minimal-fullscreen"),u||"html5"!==e.engine||(n=t.css("opacity")||"",a.css(t,"opacity",0)),u||a.css(t,"position",""),a.removeClass(t,"is-fullscreen"),u||"html5"!==e.engine||setTimeout(function(){t.css("opacity",n)}),e.isFullscreen=!1,c.scrollTo(o,i)}).on("unload",function(){e.isFullscreen&&e.fullscreen()}),e.on("shutdown",function(){r=null,a.removeNode(n)})}})},{"../common":1,"../flowplayer":31,bean:34}],15:[function(e,t,n){"use strict";var r,i,o=e("../flowplayer"),a=e("bean"),s=e("../common");a.on(document,"keydown.fp",function(e){var t=r,n=e.ctrlKey||e.metaKey||e.altKey,i=e.which,o=t&&t.conf;if(t&&o.keyboard&&!t.disabled&&!n&&t.ready){if(e.shiftKey)return 39==i?t.speed(!0):37==i&&t.speed(!1),e.preventDefault();if(58>i&&i>47)return e.preventDefault(),t.seekTo(i-48);var a=function(){switch(i){case 38:case 75:return t.volume(t.volumeLevel+.15),!0;case 40:case 74:return t.volume(t.volumeLevel-.15),!0;case 39:case 76:return t.seeking=!0,t.seek(!0),!0;case 37:case 72:return t.seeking=!0,t.seek(!1),!0;case 190:return t.seekTo(),!0;case 32:return t.toggle(),!0;case 70:return o.fullscreen&&t.fullscreen(),!0;case 77:return t.mute(),!0;case 81:return t.unload(),!0}}();a&&e.preventDefault()}}),o(function(e,t){e.conf.keyboard&&(a.on(document,"click",function(n){if(s.hasParent(n.target,t))r=e.disabled?0:e;else{if(r!==e)return;r=0}r&&(i=t)}),e.bind("shutdown",function(){i==t&&(i=null)}))})},{"../common":1,"../flowplayer":31,bean:34}],16:[function(e,t,n){var r=e("../flowplayer"),i=e("../common"),o=e("bean");r(function(e,t){e.showMenu=function(n,r){var a=i.find(".fp-ui",t)[0];i.toggleClass(n,"fp-active",!0),setTimeout(function(){o.one(document,"click",function(){e.hideMenu(n)})});var s=r;if(r&&r.tagName&&(s={left:i.offset(r).left,rightFallbackOffset:i.width(r),top:i.offset(r).top+i.height(r)}),!s)return i.css(n,"top","auto");s.rightFallbackOffset=s.rightFallbackOffset||0;var l=s.top-i.offset(a).top,u=s.left-i.offset(a).left;i.width(n)+u>i.width(a)&&(u=u-i.width(n)+s.rightFallbackOffset),i.height(n)+l>i.height(a)&&(l-=i.height(n)),i.css(n,{top:l+"px",left:u+"px",right:"auto"})},e.hideMenu=function(e){i.toggleClass(e,"fp-active",!1),i.css(e,{top:"-9999em"})}})},{"../common":1,"../flowplayer":31,bean:34}],17:[function(e,t,n){var r=e("../flowplayer"),i=e("../common"),o=e("bean");r(function(e,t){function n(e){var t=i.createElement("div",{className:"fp-message"},e);return s.insertBefore(t,a),setTimeout(function(){i.toggleClass(t,"fp-shown")}),t}function r(e){i.removeNode(e)}var a=i.find(".fp-header",t)[0],s=i.find(".fp-ui",t)[0];e.message=function(e,t){var o=n(e),a=function(){i.toggleClass(o,"fp-shown"),setTimeout(function(){r(o)},500)};return t&&setTimeout(a,t),a},e.textarea=function(e){var t=document.createElement("textarea");t.value=e,t.className="fp-textarea",s.appendChild(t),o.on(document,"click.fptextarea",function(e){return e.target===t?t.select():(e.stopPropagation(),e.preventDefault(),i.removeNode(t),void o.off(document,"click.fptextarea"))})}})},{"../common":1,"../flowplayer":31,bean:34}],18:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=/IEMobile/.test(window.navigator.userAgent),o=e("../common"),a=e("bean"),s=e("./ui").format,l=r.support,u=window.navigator.userAgent;(l.touch||i)&&r(function(e,t){var n=l.android,r=n&&!n.firefox,c=/Silk/.test(u),f=n.version||0;if(r&&!i){if(!/Chrome/.test(u)&&4>f||n.samsung&&5>f){var d=e.load;e.load=function(){var n=d.apply(e,arguments);return o.find("video.fp-engine",t)[0].load(),e.trigger("ready",[e,e.video]),n}}var p,h=0,g=function(e){p=setInterval(function(){e.video.time=++h,e.trigger("progress",[e,h])},1e3)};e.on("ready pause unload",function(){p&&(clearInterval(p),p=null)}),e.on("ready",function(){h=0}),e.on("resume",function(t,n){return n.live?h?g(n):void e.one("progress",function(e,t,n){0===n&&g(t)}):void 0})}l.volume||(o.removeClass(t,"fp-mute"),o.addClass(t,"no-volume")),l.iOS&&o.addClass(t,"fp-mute"),o.addClass(t,"is-touch"),e.sliders&&e.sliders.timeline&&e.sliders.timeline.disableAnimation();var m=!1;a.on(t,"touchmove",function(){m=!0});var v=!0;if(a.on(t,"touchend click",function(n){if(m)return void(m=!1);var r=o.find("video.fp-engine",t)[0];return v&&e.conf.clickToUnMute&&r&&r.muted&&e.conf.autoplay&&(r.muted=!1),v=!1,e.playing&&!o.hasClass(t,"is-mouseover")?(o.addClass(t,"is-mouseover"),o.removeClass(t,"is-mouseout"),n.preventDefault(),void n.stopPropagation()):void(e.playing||e.splash||!o.hasClass(t,"is-mouseout")||o.hasClass(t,"is-mouseover")||setTimeout(function(){e.disabled||e.playing||e.splash||o.find("video.fp-engine",t)[0].play()},400))}),!l.fullscreen&&e.conf.native_fullscreen&&"function"==typeof o.createElement("video").webkitEnterFullScreen){var y=e.fullscreen;e.fullscreen=function(){var n=o.find("video.fp-engine",t)[0];return n?(e.trigger("fullscreen",[e]),a.on(document,"webkitfullscreenchange.nativefullscreen",function(){document.webkitFullscreenElement===n&&(a.off(document,".nativefullscreen"),a.on(document,"webkitfullscreenchange.nativefullscreen",function(){document.webkitFullscreenElement||(a.off(document,".nativefullscreen"),e.trigger("fullscreen-exit",[e]))}))}),n.webkitEnterFullScreen(),void a.one(n,"webkitendfullscreen",function(){a.off(document,"fullscreenchange.nativefullscreen"),e.trigger("fullscreen-exit",[e]),o.prop(n,"controls",!0),o.prop(n,"controls",!1)})):y.apply(e)}}(r||c)&&e.bind("ready",function(){var n=o.find("video.fp-engine",t)[0];e.conf.splash&& n && n.paused&&"hlsjs-lite"!==e.engine.engineName&&(a.one(n,"canplay",function(){n.play()}),n.load()),e.bind("progress.dur",function(){if(!e.live&&!e.conf.live && n ){var r=n.duration;1!==r&&(e.video.duration=r,o.find(".fp-duration",t)[0].innerHTML=s(r),e.unbind("progress.dur"))}})})})},{"../common":1,"../flowplayer":31,"./ui":27,bean:34}],19:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("extend-object"),o=e("bean"),a=e("../common"),s=e("./resolve"),l=new s,u=window.jQuery,c=/^#/;r(function(e,t){function n(){return a.find(h.query,r())}function r(){return c.test(h.query)?void 0:t}function f(){return a.find(h.query+"."+g,r())}function d(){var n=a.find(".fp-playlist",t)[0];if(!n){n=a.createElement("div",{className:"fp-playlist"});var r=a.find(".fp-next,.fp-prev",t);r.length?r[0].parentElement.insertBefore(n,r[0]):a.insertAfter(t,a.find("video",t)[0],n)}n.innerHTML="",e.conf.playlist[0].length&&(e.conf.playlist=e.conf.playlist.map(function(e){if("string"==typeof e){var t=e.split(s.TYPE_RE)[1];return{sources:[{type:"m3u8"===t.toLowerCase()?"application/x-mpegurl":"video/"+t,src:e}]}}return{sources:e.map(function(e){var t={};return Object.keys(e).forEach(function(n){t.type=/mpegurl/i.test(n)?"application/x-mpegurl":"video/"+n,t.src=e[n]}),t})}})),e.conf.playlist.forEach(function(t,r){var i=t.sources[0].src;n.appendChild(a.createElement("a",{href:i,className:e.video.index===r?g:void 0,"data-index":r}))})}function p(t){return"undefined"!=typeof t.index?t.index:"undefined"!=typeof e.video.index?e.video.index:e.conf.startIndex||0}var h=i({active:"is-active",advance:!0,query:".fp-playlist a"},e.conf),g=h.active,m=a.find(".fp-ui",t)[0],v=a.hasClass(t,"fp-custom-playlist")||!!h.customPlaylist;a.toggleClass(t,"fp-custom-playlist",v),a.toggleClass(t,"fp-default-playlist",!v),e.play=function(t){if(void 0===t)return e.resume();if("number"==typeof t&&!e.conf.playlist[t])return e;if("number"!=typeof t)return e.load.apply(null,arguments);var n=i({index:t},e.conf.playlist[t]);return e.off("beforeresume.fromfirst"),"number"==typeof t&&t===e.video.index?e.seek(0,function(){e.resume()}):(e.load(n,function(){e.video.index=t}),e)},e.next=function(t){t&&t.preventDefault();var n=e.video.index;return-1!=n&&(n=n===e.conf.playlist.length-1?0:n+1,e.play(n)),e},e.prev=function(t){t&&t.preventDefault();var n=e.video.index;return-1!=n&&(n=0===n?e.conf.playlist.length-1:n-1,e.play(n)),e},e.setPlaylist=function(t,n){return e.conf.playlist=t,n||delete e.video.index,d(),e},e.addPlaylistItem=function(t){return delete e.video.is_last,e.setPlaylist(e.conf.playlist.concat([t]),!0)},e.removePlaylistItem=function(t){var n=e.conf.playlist;return e.setPlaylist(n.slice(0,t).concat(n.slice(t+1)))},o.on(t,"click",".fp-next",e.next),o.on(t,"click",".fp-prev",e.prev),e.off("finish.pl").on("finish.pl",function(e,n){var r="undefined"==typeof n.conf.advance?!0:n.conf.advance;if(r){if(n.video.loop)return n.seek(0,function(){n.resume()});var i=n.video.index>=0?n.video.index+1:void 0;i<n.conf.playlist.length||h.loop?(i=i===n.conf.playlist.length?0:i,a.removeClass(t,"is-finished"),setTimeout(function(){n.play(i)})):n.conf.playlist.length>1&&(n.one("beforeresume.fromfirst",function(e){e.preventDefault(),n.play(0)}),n.one("seek",function(){n.off("beforeresume.fromfirst")}))}});var y=!1;e.conf.playlist.length&&(y=!0,d(),e.conf.clip&&e.conf.clip.sources.length||(e.conf.clip=e.conf.playlist[e.conf.startIndex||0])),n().length&&!y&&(e.conf.playlist=[],delete e.conf.startIndex,n().forEach(function(t){var n=t.href;t.setAttribute("data-index",e.conf.playlist.length);var r=l.resolve(n,e.conf.clip.sources);u&&i(r,u(t).data()),e.conf.playlist.push(r)})),a.find(".fp-prev,.fp-next,.fp-playlist",t).forEach(function(e){m.appendChild(e)}),o.on(c.test(h.query)?document:t,"click",h.query,function(t){t.preventDefault();var n=t.currentTarget,r=Number(n.getAttribute("data-index"));-1!=r&&e.play(r)}),e.on("load",function(n,i,o){if(e.conf.playlist.length){var s=f()[0],l=s&&s.getAttribute("data-index"),u=o.index=p(o),c=a.find(h.query+'[data-index="'+u+'"]',r())[0],d=u==e.conf.playlist.length-1;s&&a.removeClass(s,g),c&&a.addClass(c,g),a.removeClass(t,"video"+l),a.addClass(t,"video"+u),a.toggleClass(t,"last-video",d),o.index=i.video.index=u,o.is_last=i.video.is_last=d}}).on("unload.pl",function(){e.conf.playlist.length&&(f().forEach(function(e){a.toggleClass(e,g)}),e.conf.playlist.forEach(function(e,n){a.removeClass(t,"video"+n)}),delete e.video.index)}),e.conf.playlist.length&&(e.conf.loop=!1)})},{"../common":1,"../flowplayer":31,"./resolve":21,bean:34,"extend-object":39}],20:[function(e,t,n){var r=e("../flowplayer"),i=e("../common"),o=e("bean");r(function(e,t){function n(){i.find(".fp-qsel-menu",t).forEach(i.removeNode),i.find(".fp-qsel",t).forEach(i.removeNode)}function r(e){l.appendChild(i.createElement("strong",{className:"fp-qsel"},"HD"));var t=i.createElement("div",{className:"fp-menu fp-qsel-menu"},"<strong>Quality</strong>");e.forEach(function(e){var n=document.createElement("a"),r="undefined"!=typeof e.value?e.value:e;n.setAttribute("data-quality",r),n.innerHTML=e.label||e,t.appendChild(n)}),s.appendChild(t)}function a(e){i.find(".fp-qsel-menu a",t).forEach(function(t){i.toggleClass(t,"fp-selected",t.getAttribute("data-quality")==e),i.toggleClass(t,"fp-color",t.getAttribute("data-quality")==e)})}var s=i.find(".fp-ui",t)[0],l=i.find(".fp-controls",s)[0];o.on(t,"click",".fp-qsel",function(){var n=i.find(".fp-qsel-menu",t)[0];i.hasClass(n,"fp-active")?e.hideMenu():e.showMenu(n)}),o.on(t,"click",".fp-qsel-menu a",function(t){var n=t.target.getAttribute("data-quality");e.quality(n)}),e.quality=function(t){t=isNaN(Number(t))?t:Number(t),e.trigger("quality",[e,t])},e.on("quality",function(e,t,n){a(n,t.video.qualities)}),e.on("ready",function(e,t,i){n(),!i.qualities||i.qualities.filter(function(e){return"undefined"!=typeof e.value?e.value>-1:!0}).length<2||(r(i.qualities,i.quality),a(i.quality,i.qualities))})})},{"../common":1,"../flowplayer":31,bean:34}],21:[function(e,t,n){"use strict";function r(e){var t=e.attr("src"),n=e.attr("type")||"",r=t.split(o)[1];return n=n.toLowerCase(),a(e.data(),{src:t,suffix:r||n,type:n||r})}function i(e){return/mpegurl/i.test(e)?"application/x-mpegurl":"video/"+e}var o=/\.(\w{3,4})(\?.*)?$/i,a=e("extend-object");t.exports=function(){var e=this;e.sourcesFromVideoTag=function(e,t){var n=[];return t("source",e).each(function(){n.push(r(t(this)))}),!n.length&&e.length&&n.push(r(e)),n},e.resolve=function(e,t){return e?("string"==typeof e&&(e={src:e,sources:[]},e.sources=(t||[]).map(function(t){var n=t.src.split(o)[1];return{type:t.type,src:e.src.replace(o,"."+n+"$2")}})),e instanceof Array&&(e={sources:e.map(function(e){return e.type&&e.src?e:Object.keys(e).reduce(function(t,n){return a(t,{type:i(n),src:e[n]})},{})})}),e):{sources:t}}},t.exports.TYPE_RE=o},{"extend-object":39}],22:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("../common"),o=e("extend-object"),a=e("bean");r(function(e,t){var n=e.conf;if(n.share===!1)return void i.find(".fp-share",t).forEach(i.removeNode);e.shareUrl=function(t){if(t&&n.embed&&n.embed.iframe)return n.embed.iframe;if("string"==typeof e.conf.share)return e.conf.share;var r=encodeURIComponent(e.video.title||(i.find("title")[0]||{}).innerHTML||"Flowplayer Unlimited video"),a=encodeURIComponent(btoa(JSON.stringify(o({},e.conf,e.extensions)).replace(/[\u007F-\uFFFF]/g,function(e){return"\\u"+("0000"+e.charCodeAt(0).toString(16)).substr(-4)}))),s=encodeURIComponent(window.location.toString()),l=t?"https://flowplayer.com/e/":"https://flowplayer.com/s/";return l+"?t="+r+"&c="+a+"&r="+s};var r=i.createElement("div",{className:"fp-menu fp-share-menu"},"<strong>Share</strong>"),s=i.find(".fp-ui",t)[0];s.appendChild(r);var l=i.find(".fp-share",t)[0];a.on(t,"click",".fp-share",function(t){t.preventDefault(),i.hasClass(r,"fp-active")?e.hideMenu():e.showMenu(r,l)})})},{"../common":1,"../flowplayer":31,bean:34,"extend-object":39}],23:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("../common"),o=e("bean"),a=e("./subtitles/parser");r.defaults.subtitleParser=a,r(function(e,t){var n,a,s,l,u;(!r.support.inlineVideo||!r.support.fullscreen&&e.conf.native_fullscreen)&&(e.conf.nativesubtitles=!0),e.ui||(e.ui={}),e.ui.createSubtitleControl=function(e,n){return u=n,s=s||i.createElement("strong",{className:"fp-cc"},"CC"),l=l||i.createElement("div",{className:"fp-menu fp-subtitle-menu"},"<strong>Closed Captions</strong>"),i.find("a",l).forEach(i.removeNode),l.appendChild(i.createElement("a",{"data-subtitle-index":-1},"No subtitles")),(e||[]).forEach(function(e,t){var n=e.srclang||"en",r=e.label||"Default ("+n+")",o=i.createElement("a",{"data-subtitle-index":t},r);l.appendChild(o)}),i.find(".fp-ui",t)[0].appendChild(l),i.find(".fp-controls",t)[0].appendChild(s),i.toggleClass(s,"fp-hidden",!e||!e.length),s},e.ui.setActiveSubtitleItem=function(e){f(e)},o.on(t,"click",".fp-cc",function(){i.hasClass(l,"fp-active")?e.hideMenu():e.showMenu(l)}),o.on(t,"click",".fp-subtitle-menu [data-subtitle-index]",function(t){t.preventDefault();var n=t.target.getAttribute("data-subtitle-index");return u?u(n):"-1"===n?e.disableSubtitles():void e.loadSubtitles(n)});var c=function(){a=i.find(".fp-captions",t)[0],a=a||i.appendTo(i.createElement("div",{"class":"fp-captions"}),i.find(".fp-player",t)[0]),Array.prototype.forEach.call(a.children,i.removeNode),e.ui.createSubtitleControl(e.video.subtitles)};e.on("ready",function(n,r,o){if(r.subtitles=[],c(),i.removeClass(t,"has-menu"),e.disableSubtitles(),o.subtitles&&o.subtitles.length){var a=o.subtitles.filter(function(e){return e["default"]})[0];a&&r.loadSubtitles(o.subtitles.indexOf(a))}}),e.showSubtitle=function(e){i.html(a,e),i.addClass(a,"fp-shown")},e.hideSubtitle=function(){i.removeClass(a,"fp-shown")},e.bind("cuepoint",function(t,r,i){i.subtitle?(n=i.index,e.showSubtitle(i.subtitle.text)):i.subtitleEnd&&(e.hideSubtitle(),n=i.index)}),e.bind("seek",function(t,r,o){n&&e.cuepoints[n]&&e.cuepoints[n].time>o&&(i.removeClass(a,"fp-shown"),n=null),(e.cuepoints||[]).forEach(function(t,r){var i=t.subtitle;i&&n!=r?o>=t.time&&(!i.endTime||o<=i.endTime)&&e.trigger("cuepoint",[e,t]):t.subtitleEnd&&o>=t.time&&r==n+1&&e.trigger("cuepoint",[e,t])})}),e.on("unload",function(){i.find(".fp-captions",t).forEach(i.removeNode)});var f=function(e){i.toggleClass(i.find("a.fp-selected",l)[0],"fp-selected"),i.toggleClass(i.find('a[data-subtitle-index="'+e+'"]',l)[0],"fp-selected")},d=function(e,n){var r=i.find("video.fp-engine",t)[0].textTracks;r.length&&(null===e?[].forEach.call(r,function(e){e.mode=n}):r[e].mode=n)};e.disableSubtitles=function(){return e.subtitles=[],(e.cuepoints||[]).forEach(function(t){(t.subtitle||t.subtitleEnd)&&e.removeCuepoint(t)}),a&&Array.prototype.forEach.call(a.children,i.removeNode),f(-1),r.support.subtitles&&e.conf.nativesubtitles&&"html5"==e.engine.engineName&&d(null,"disabled"),e},e.loadSubtitles=function(t){e.disableSubtitles();var n=e.video.subtitles[t],o=n.src;return o?(f(t),r.support.subtitles&&e.conf.nativesubtitles&&"html5"==e.engine.engineName?void d(t,"showing"):(i.xhrGet(o,function(t){var n=e.conf.subtitleParser(t);n.forEach(function(t,n){t.title||(t.title="subtitle"+n);var i={time:t.startTime,subtitle:t,visible:!1};e.subtitles.push(t),e.addCuepoint(i),e.addCuepoint({time:t.endTime,subtitleEnd:t.title,visible:!1}),0!==t.startTime||e.video.time||e.splash||e.trigger("cuepoint",[e,r.extend({},i,{index:0})]),e.splash&&e.one("ready",function(){e.trigger("cuepoint",[e,i])})})},function(){return e.trigger("error",{code:8,url:o}),!1}),e)):void 0}})},{"../common":1,"../flowplayer":31,"./subtitles/parser":24,bean:34}],24:[function(e,t,n){t.exports=function(e){function t(e){var t=e.split(":");return 2==t.length&&t.unshift(0),60*t[0]*60+60*t[1]+parseFloat(t[2].replace(",","."))}for(var n,r,i,o=/^(([0-9]+:){1,2}[0-9]{2}[,.][0-9]{3}) --\> (([0-9]+:){1,2}[0-9]{2}[,.][0-9]{3})(.*)/,a=[],s=0,l=e.split("\n"),u=l.length,c={};u>s;s++)if(r=o.exec(l[s])){for(n=l[s-1],i="<p>"+l[++s]+"</p><br/>";"string"==typeof l[++s]&&l[s].trim()&&s<l.length;)i+="<p>"+l[s]+"</p><br/>";c={title:n,startTime:t(r[1]),endTime:t(r[3]),text:i},a.push(c)}return a}},{}],25:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("extend-object");!function(){var e=function(e){var t=/iP(ad|hone)(; CPU)? OS (\d+_\d)/.exec(e);return t&&t.length>1?parseFloat(t[t.length-1].replace("_","."),10):0},t=function(){var e=document.createElement("video");return e.loop=!0,e.autoplay=!0,e.preload=!0,e},n={},o=document.documentElement.style,a=navigator.userAgent.toLowerCase(),s=/(chrome)[ \/]([\w.]+)/.exec(a)||/(safari)[ \/]([\w.]+)/.exec(a)||/(webkit)[ \/]([\w.]+)/.exec(a)||/(opera)(?:.*version|)[ \/]([\w.]+)/.exec(a)||/(msie) ([\w.]+)/.exec(a)||a.indexOf("compatible")<0&&/(mozilla)(?:.*? rv:([\w.]+)|)/.exec(a)||[];s[1]&&(n[s[1]]=!0,n.version=s[2]||"0"),n.safari&&(n.version=(/version\/([\w.]+)/.exec(a)||[])[1]);var l=t(),u=navigator.userAgent,c=n.msie||/Trident\/7/.test(u),f=/iPad|MeeGo/.test(u)&&!/CriOS/.test(u),d=/iPad/.test(u)&&/CriOS/.test(u),p=/iP(hone|od)/i.test(u)&&!/iPad/.test(u)&&!/IEMobile/i.test(u),h=/Android/.test(u),g=h&&/Firefox/.test(u),m=h&&/SAMSUNG/.test(u),v=/Silk/.test(u),y=/IEMobile/.test(u),w=y?parseFloat(/Windows\ Phone\ (\d+\.\d+)/.exec(u)[1],10):0,b=y?parseFloat(/IEMobile\/(\d+\.\d+)/.exec(u)[1],10):0,I=f||p?e(u):0,M=h?parseFloat(/Android\ (\d+(\.\d+)?)/.exec(u)[1],10):0,C=(p||f||d)&&{iPhone:p,iPad:f||d,version:I,chrome:d},A=i(r.support,{browser:n,iOS:C,android:h?{firefox:g,opera:/Opera/.test(u),samsung:m,version:M}:!1,subtitles:!!l.addTextTrack,fullscreen:"boolean"==typeof document.webkitFullscreenEnabled?document.webkitFullscreenEnabled:"function"==typeof document.webkitCancelFullScreen&&!/Mac OS X 10_5.+Version\/5\.0\.\d Safari/.test(u)||document.mozFullScreenEnabled||"function"==typeof document.exitFullscreen||"function"==typeof document.msExitFullscreen,inlineBlock:!(c&&n.version<8),touch:"ontouchstart"in window,dataload:!f&&!p&&!y,flex:"flexWrap"in o||"WebkitFlexWrap"in o||"msFlexWrap"in o,svg:!!document.createElementNS&&!!document.createElementNS("http://www.w3.org/2000/svg","svg").createSVGRect,zeropreload:!c&&!h,volume:!(f||p||v||d),cachedVideoTag:!(f||p||d||y),firstframe:!(v||y||g||m||I&&10>I||h&&4.4>M),inlineVideo:(!p||I>=10)&&(!y||w>=8.1&&b>=11)&&(!h||M>=3),hlsDuration:!h&&(!n.safari||f||p||d),seekable:!f&&!d,preloadMetadata:!C&&!n.safari});A.autoplay=A.firstframe,y&&(A.browser.safari=!1);try{var S=navigator.plugins["Shockwave Flash"],E=c?new ActiveXObject("ShockwaveFlash.ShockwaveFlash").GetVariable("$version"):S.description;c||S[0].enabledPlugin?(E=E.split(/\D+/),E.length&&!E[0]&&(E=E.slice(1)),A.flashVideo=E[0]>9||9==E[0]&&E[3]>=115):A.flashVideo=!1}catch(j){}try{A.video=!!l.canPlayType,A.video&&l.canPlayType("video/mp4")}catch(D){A.video=!1}A.animation=function(){for(var e=["","Webkit","Moz","O","ms","Khtml"],t=document.createElement("p"),n=0;n<e.length;n++)if("undefined"!=typeof t.style[e[n]+"AnimationName"])return!0}()}()},{"../flowplayer":31,"extend-object":39}],26:[function(e,t,n){"use strict";var r=e("../flowplayer"),i=e("../common"),o=e("bean");r(function(e,t){var n=e.conf;if(n.share!==!1&&n.twitter!==!1){e.tweet=function(){var t,r,i=550,o=420,a=screen.height,s=screen.width,l="scrollbars=yes,resizable=yes,toolbar=no,location=yes",u="string"==typeof n.twitter?n.twitter:e.shareUrl();t=Math.round(s/2-i/2),r=0,a>o&&(r=Math.round(a/2-o/2)),window.open("https://twitter.com/intent/tweet?url="+encodeURIComponent(u),"intent",l+",width="+i+",height="+o+",left="+t+",top="+r)};var r=i.find(".fp-share-menu",t)[0],a=i.createElement("a",{"class":"fp-icon fp-twitter"},"Twitter");i.append(r,a),o.on(t,"click",".fp-twitter",function(){e.tweet()})}})},{"../common":1,"../flowplayer":31,bean:34}],27:[function(e,t,n){(function(n){"use strict";function r(e){return e=parseInt(e,10),e>=10?e:"0"+e}function i(e,t){e=Math.max(e||0,0),e=t?Math.ceil(e):Math.floor(e);var n=Math.floor(e/3600),i=Math.floor(e/60);return e-=60*i,n>=1?(i-=60*n,n+":"+r(i)+":"+r(e)):r(i)+":"+r(e)}var o=e("../flowplayer"),a=e("../common"),s=e("bean"),l=e("./ui/slider"),u=e("./ui/bar-slider"),c=n("PHN2ZyBjbGFzcz0iZnAtcGxheS1yb3VuZGVkLW91dGxpbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDk5Ljg0NCA5OS44NDM0Ij48ZGVmcz48c3R5bGU+LmZwLWNvbG9yLXBsYXl7b3BhY2l0eTowLjY1O30uY29udHJvbGJ1dHRvbntmaWxsOiNmZmY7fTwvc3R5bGU+CjwvZGVmcz4KPHRpdGxlPnBsYXktcm91bmRlZC1vdXRsaW5lPC90aXRsZT48cGF0aCBjbGFzcz0iZnAtY29sb3ItcGxheSIgZD0iTTQ5LjkyMTctLjA3OGE1MCw1MCwwLDEsMCw1MCw1MEE1MC4wNTY0LDUwLjA1NjQsMCwwLDAsNDkuOTIxNy0uMDc4WiIvPjxwYXRoIGNsYXNzPSJjb250cm9sYnV0dG9uIiBkPSJNNDEuMDM1OSw3MS4xOWE1LjA0OTIsNS4wNDkyLDAsMCwxLTIuNTU3NS0uNjY3M2MtMS44MDMxLTEuMDQxLTIuNzk1OC0zLjEyNDgtMi43OTU4LTUuODY2NFYzNS4xODg3YzAtMi43NDI5Ljk5MzMtNC44MjcyLDIuNzk3LTUuODY3NiwxLjgwMjUtMS4wNDIyLDQuMTAzNC0uODYsNi40OC41MTQzTDcwLjQ3ODIsNDQuNTY3MmMyLjM3NTEsMS4zNzExLDMuNjgyNiwzLjI3MjUsMy42ODMyLDUuMzU0NXMtMS4zMDc2LDMuOTg0NS0zLjY4MzIsNS4zNTYyTDQ0Ljk1OTIsNzAuMDExNEE3LjkzODQsNy45Mzg0LDAsMCwxLDQxLjAzNTksNzEuMTlabS4wMDY1LTQwLjEyM2EyLjY3OTQsMi42Nzk0LDAsMCwwLTEuMzU4Mi4zNDEzYy0xLjAyNjMuNTkyNi0xLjU5MTIsMS45MzQ5LTEuNTkxMiwzLjc4VjY0LjY1NjNjMCwxLjg0NDkuNTY0OSwzLjE4NjYsMS41OTA2LDMuNzc5MSwxLjAyODEuNTkzMiwyLjQ3MzMuNDEwOCw0LjA3LS41MTJMNjkuMjczLDUzLjE5MDZjMS41OTgzLS45MjI3LDIuNDc4LTIuMDgzOCwyLjQ3OC0zLjI2ODlzLS44OC0yLjM0NDUtMi40NzgtMy4yNjY2TDQzLjc1NCwzMS45MjI3QTUuNTY4NSw1LjU2ODUsMCwwLDAsNDEuMDQyMywzMS4wNjcxWiIgZmlsdGVyPSJ1cmwoI2YxKSIvPjwvc3ZnPgo=","base64"),f=n("PHN2ZyBjbGFzcz0iZnAtcGxheS1yb3VuZGVkLWZpbGwiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDEwMCAxMDAiPgogIDxkZWZzPjxzdHlsZT4uYXtmaWxsOiMwMDA7b3BhY2l0eTowLjY1O30uYntmaWxsOiNmZmY7b3BhY2l0eToxLjA7fTwvc3R5bGU+CiAgPC9kZWZzPjx0aXRsZT5wbGF5LXJvdW5kZWQtZmlsbDwvdGl0bGU+CiAgPHBhdGggY2xhc3M9ImZwLWNvbG9yLXBsYXkiIGQ9Ik00OS45MjE3LS4wNzhhNTAsNTAsMCwxLDAsNTAsNTBBNTAuMDU2NCw1MC4wNTY0LDAsMCwwLDQ5LjkyMTctLjA3OFoiLz4KICA8cGF0aCBjbGFzcz0iYiIgZD0iTTM1Ljk0MiwzNS4yMzIzYzAtNC43Mjg5LDMuMzUwNi02LjY2MzcsNy40NDYtNC4yOTcxTDY4LjgzLDQ1LjYyMzVjNC4wOTU2LDIuMzY0LDQuMDk1Niw2LjIzMTksMCw4LjU5NzdMNDMuMzg4LDY4LjkxYy00LjA5NTQsMi4zNjQtNy40NDYuNDMtNy40NDYtNC4yOTc5WiIgZmlsdGVyPSJ1cmwoI2YxKSIvPgogIDwvc3ZnPgogIAo=","base64"),d=n("PHN2ZyBjbGFzcz0iZnAtcGxheS1zaGFycC1maWxsIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj4KICA8ZGVmcz4KICAgIDxzdHlsZT4uZnAtY29sb3ItcGxheXtvcGFjaXR5OjAuNjU7fS5jb250cm9sYnV0dG9ue2ZpbGw6I2ZmZjt9PC9zdHlsZT4KICA8L2RlZnM+CiAgPHRpdGxlPnBsYXktc2hhcnAtZmlsbDwvdGl0bGU+CiAgPHBhdGggY2xhc3M9ImZwLWNvbG9yLXBsYXkiIGQ9Ik00OS45MjE3LS4wNzhhNTAsNTAsMCwxLDAsNTAsNTBBNTAuMDU2NCw1MC4wNTY0LDAsMCwwLDQ5LjkyMTctLjA3OFoiLz4KICA8cG9seWdvbiBjbGFzcz0iY29udHJvbGJ1dHRvbiIgcG9pbnRzPSI3My42MDEgNTAgMzcuOTY4IDcwLjU3MyAzNy45NjggMjkuNDI3IDczLjYwMSA1MCIgZmlsdGVyPSJ1cmwoI2YxKSIvPgo8L3N2Zz4K","base64"),p=n("PHN2ZyBjbGFzcz0iZnAtcGxheS1zaGFycC1vdXRsaW5lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA5OS44NDQgOTkuODQzNCI+PGRlZnM+PHN0eWxlPi5jb250cm9sYnV0dG9uYmd7b3BhY2l0eTowLjY1O30uY29udHJvbGJ1dHRvbntmaWxsOiNmZmY7fTwvc3R5bGU+CjwvZGVmcz48dGl0bGU+cGxheS1zaGFycC1vdXRsaW5lPC90aXRsZT48cGF0aCBjbGFzcz0iZnAtY29sb3ItcGxheSIgZD0iTTQ5LjkyMTctLjA3OGE1MCw1MCwwLDEsMCw1MCw1MEE1MC4wNTY0LDUwLjA1NjQsMCwwLDAsNDkuOTIxNy0uMDc4WiIvPjxwYXRoIGNsYXNzPSJjb250cm9sYnV0dG9uIiBkPSJNMzYuOTQ0Myw3Mi4yNDczVjI3LjI5MTZMNzUuODc3Niw0OS43N1ptMi4yLTQxLjE0NTVWNjguNDM3MUw3MS40Nzc2LDQ5Ljc3WiIgZmlsdGVyPSJ1cmwoI2YxKSIvPjwvc3ZnPgo=","base64"),h=n("PHN2ZyBjbGFzcz0iZnAtcGF1c2Utcm91bmRlZC1vdXRsaW5lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA5OS44NDM0IDk5Ljg0MzQiPjxkZWZzPjxzdHlsZT4uZnAtY29sb3ItcGxheXtvcGFjaXR5OjAuNjU7fS5yZWN0e2ZpbGw6I2ZmZjt9PC9zdHlsZT4KPC9kZWZzPjx0aXRsZT5wYXVzZS1yb3VuZGVkLW91dGxpbmU8L3RpdGxlPjxwYXRoIGNsYXNzPSJmcC1jb2xvci1wbGF5IiBkPSJNNDkuOTIxMi0uMDc4M2E1MCw1MCwwLDEsMCw1MC4wMDA2LDUwQTUwLjA1NjIsNTAuMDU2MiwwLDAsMCw0OS45MjEyLS4wNzgzWiIvPjxnIGNsYXNzPSJjb250cm9sYnV0dG9uIj48cGF0aCBjbGFzcz0icmVjdCIgZD0iTTM5LjAwMzYsNzEuOTcyNmE3LjU2NSw3LjU2NSwwLDAsMS03LjU1Ny03LjU1NnYtMjguOTlhNy41NTY1LDcuNTU2NSwwLDAsMSwxNS4xMTMsMHYyOC45OUE3LjU2NDgsNy41NjQ4LDAsMCwxLDM5LjAwMzYsNzEuOTcyNlptMC00MS45MDRhNS4zNjQ3LDUuMzY0NywwLDAsMC01LjM1OTMsNS4zNTgydjI4Ljk5YTUuMzU4Nyw1LjM1ODcsMCwwLDAsMTAuNzE3NCwwdi0yOC45OUE1LjM2NDUsNS4zNjQ1LDAsMCwwLDM5LjAwMzYsMzAuMDY4NloiIGZpbHRlcj0idXJsKCNmMSkiLz48cGF0aCBjbGFzcz0icmVjdCIgZD0iTTYwLjg0LDcxLjk3MjZhNy41NjQ4LDcuNTY0OCwwLDAsMS03LjU1Ni03LjU1NnYtMjguOTlhNy41NTY1LDcuNTU2NSwwLDAsMSwxNS4xMTMsMHYyOC45OUE3LjU2NSw3LjU2NSwwLDAsMSw2MC44NCw3MS45NzI2Wm0wLTQxLjkwNGE1LjM2NDUsNS4zNjQ1LDAsMCwwLTUuMzU4Miw1LjM1ODJ2MjguOTlhNS4zNTg3LDUuMzU4NywwLDAsMCwxMC43MTc0LDB2LTI4Ljk5QTUuMzY0Nyw1LjM2NDcsMCwwLDAsNjAuODQsMzAuMDY4NloiIGZpbHRlcj0idXJsKCNmMSkiLz48L2c+PC9zdmc+Cg==","base64"),g=n("PHN2ZyBjbGFzcz0iZnAtcGF1c2Utcm91bmRlZC1maWxsIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48ZGVmcz48c3R5bGU+LmZwLWNvbG9yLXBsYXl7b3BhY2l0eTowLjY1O30ucmVjdHtmaWxsOiNmZmY7fTwvc3R5bGU+CjwvZGVmcz48dGl0bGU+cGF1c2Utcm91bmRlZC1maWxsPC90aXRsZT48cGF0aCBjbGFzcz0iZnAtY29sb3ItcGxheSIgZD0iTTQ5LjkyMTctLjA3OGE1MCw1MCwwLDEsMCw1MCw1MEE1MC4wNTY0LDUwLjA1NjQsMCwwLDAsNDkuOTIxNy0uMDc4WiIvPjxnIGNsYXNzPSJjb250cm9sYnV0dG9uIiBmaWx0ZXI9InVybCgjZjEpIj48cmVjdCBjbGFzcz0icmVjdCIgeD0iMzEuODQ0IiB5PSIyOC4xMjMxIiB3aWR0aD0iMTMuNDM2MiIgaGVpZ2h0PSI0My41OTczIiByeD0iNi43MTgxIiByeT0iNi43MTgxIi8+PHJlY3QgY2xhc3M9InJlY3QiIHg9IjU0LjU2MzgiIHk9IjI4LjEyMzEiIHdpZHRoPSIxMy40MzYyIiBoZWlnaHQ9IjQzLjU5NzMiIHJ4PSI2LjcxODEiIHJ5PSI2LjcxODEiLz48L2c+PC9zdmc+Cg==","base64"),m=n("PHN2ZyBjbGFzcz0iZnAtcGF1c2Utc2hhcnAtZmlsbCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PGRlZnM+PHN0eWxlPi5mcC1jb2xvci1wbGF5e29wYWNpdHk6MC42NTt9LnJlY3R7ZmlsbDojZmZmO308L3N0eWxlPgo8L2RlZnM+PHRpdGxlPnBhdXNlLXNoYXJwLWZpbGw8L3RpdGxlPjxwYXRoIGNsYXNzPSJmcC1jb2xvci1wbGF5IiBkPSJNNDkuOTIxNy0uMDc4YTUwLDUwLDAsMSwwLDUwLDUwQTUwLjA1NjQsNTAuMDU2NCwwLDAsMCw0OS45MjE3LS4wNzhaIi8+PGcgY2xhc3M9ImNvbnRyb2xidXR0b24iIGZpbHRlcj0idXJsKCNmMSkiPjxyZWN0IGNsYXNzPSJyZWN0IiB4PSIzMy41IiB5PSIzMC4xMDQyIiB3aWR0aD0iMTIuMjYzNCIgaGVpZ2h0PSIzOS43OTE3Ii8+PHJlY3QgY2xhc3M9InJlY3QiIHg9IjU0LjIzNjYiIHk9IjMwLjEwNDIiIHdpZHRoPSIxMi4yNjM0IiBoZWlnaHQ9IjM5Ljc5MTciLz48L2c+PC9zdmc+Cg==","base64"),v=n("PHN2ZyBjbGFzcz0iZnAtcGF1c2Utc2hhcnAtb3V0bGluZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgOTkuODQzNCA5OS44NDM0Ij48ZGVmcz48c3R5bGU+LmZwLWNvbG9yLXBsYXl7b3BhY2l0eTowLjY1O30ucmVjdHtmaWxsOiNmZmY7fTwvc3R5bGU+CjwvZGVmcz48dGl0bGU+cGF1c2Utc2hhcnAtb3V0bGluZTwvdGl0bGU+PHBhdGggY2xhc3M9ImZwLWNvbG9yLXBsYXkiIGQ9Ik00OS45MjEyLS4wNzgzYTUwLDUwLDAsMSwwLDUwLjAwMDYsNTBBNTAuMDU2Miw1MC4wNTYyLDAsMCwwLDQ5LjkyMTItLjA3ODNaIi8+PGcgY2xhc3M9ImNvbnRyb2xidXR0b24iIGZpbHRlcj0idXJsKCNmMSkiPjxwYXRoIGNsYXNzPSJyZWN0IiBkPSJNNDYuODcwOSw2OS45NTMxSDMzLjEzODVWMjkuODlINDYuODcwOVpNMzUuMTQxNiw2Ny45NWg5LjcyNjJWMzEuODkzNUgzNS4xNDE2WiIvPjxwYXRoIGNsYXNzPSJyZWN0IiBkPSJNNjYuNzA0Nyw2OS45NTMxSDUyLjk3MjJWMjkuODlINjYuNzA0N1pNNTQuOTc1NCw2Ny45NWg5LjcyNjJWMzEuODkzNUg1NC45NzU0WiIvPjwvZz48L3N2Zz4K","base64"),y=n("PHN2ZyBjbGFzcz0iZnAtbG9hZGluZy1yb3VuZGVkLW91dGxpbmUiIHdpZHRoPScxMTJweCcgaGVpZ2h0PScxMTJweCcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pZFlNaWQiPgogICAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9Ijc2IiBoZWlnaHQ9Ijc2IiBmaWxsPSJyZ2JhKDAsMCwwLDApIiBjbGFzcz0iYmsiPjwvcmVjdD4KICAgIDxjaXJjbGUgY3g9IjAiIGN5PSIwIiByPSIxMCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjUgMjUpIiBmaWxsPSJub25lIiBzdHJva2U9InJnYmEoMCwwLDAsLjUpIiBzdHJva2Utd2lkdGg9IjMlIiBjbGFzcz0ic3EiPgogICAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJzdHJva2UiIGZyb209InJnYmEoMCwwLDAsMCkiIHRvPSJyZ2JhKDAsMCwwLC41KSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGR1cj0iMS42cyIgYmVnaW49IjAuMHMiIHZhbHVlcz0icmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsLjUpIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGU+CiAgICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0ic2NhbGUiIGFkZGl0aXZlPSJzdW0iIGZyb209IjAuOCIgdG89IjEiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBiZWdpbj0iMC4wcyIgZHVyPSIxLjZzIiB2YWx1ZXM9IjE7MC44OzAuODsxOzEiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZVRyYW5zZm9ybT4KICAgIDwvY2lyY2xlPgogICAgPGNpcmNsZSBjeD0iMCIgY3k9IjAiIHI9IjEwIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCAyNSkiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgwLDAsMCwuNSkiIHN0cm9rZS13aWR0aD0iMyUiIGNsYXNzPSJzcSI+CiAgICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9InN0cm9rZSIgZnJvbT0icmdiYSgwLDAsMCwwKSIgdG89InJnYmEoMCwwLDAsLjUpIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgZHVyPSIxLjZzIiBiZWdpbj0iMC40cyIgdmFsdWVzPSJyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwuNSkiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZT4KICAgICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJzY2FsZSIgYWRkaXRpdmU9InN1bSIgZnJvbT0iMC44IiB0bz0iMSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGJlZ2luPSIwLjRzIiBkdXI9IjEuNnMiIHZhbHVlcz0iMTswLjg7MC44OzE7MSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlVHJhbnNmb3JtPgogICAgPC9jaXJjbGU+CiAgICA8Y2lyY2xlIGN4PSIwIiBjeT0iMCIgcj0iMTAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDUwIDUwKSIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJyZ2JhKDAsMCwwLC41KSIgc3Ryb2tlLXdpZHRoPSIzJSIgY2xhc3M9InNxIj4KICAgICAgPGFuaW1hdGUgYXR0cmlidXRlTmFtZT0ic3Ryb2tlIiBmcm9tPSJyZ2JhKDAsMCwwLDApIiB0bz0icmdiYSgwLDAsMCwuNSkiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBkdXI9IjEuNnMiIGJlZ2luPSIwLjhzIiB2YWx1ZXM9InJnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLC41KSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlPgogICAgICA8YW5pbWF0ZVRyYW5zZm9ybSBhdHRyaWJ1dGVOYW1lPSJ0cmFuc2Zvcm0iIHR5cGU9InNjYWxlIiBhZGRpdGl2ZT0ic3VtIiBmcm9tPSIwLjgiIHRvPSIxIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgYmVnaW49IjAuOHMiIGR1cj0iMS42cyIgdmFsdWVzPSIxOzAuODswLjg7MTsxIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGVUcmFuc2Zvcm0+CiAgICA8L2NpcmNsZT4KICAgIDxjaXJjbGUgY3g9IjAiIGN5PSIwIiByPSIxMCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjUgNTApIiBmaWxsPSJub25lIiBzdHJva2U9InJnYmEoMCwwLDAsLjUpIiBzdHJva2Utd2lkdGg9IjMlIiBjbGFzcz0ic3EiPgogICAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJzdHJva2UiIGZyb209InJnYmEoMCwwLDAsMCkiIHRvPSJyZ2JhKDAsMCwwLC41KSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGR1cj0iMS42cyIgYmVnaW49IjEuMnMiIHZhbHVlcz0icmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsLjUpIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGU+CiAgICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0ic2NhbGUiIGFkZGl0aXZlPSJzdW0iIGZyb209IjAuOCIgdG89IjEiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBiZWdpbj0iMS4ycyIgZHVyPSIxLjZzIiB2YWx1ZXM9IjE7MC44OzAuODsxOzEiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZVRyYW5zZm9ybT4KICAgIDwvY2lyY2xlPgo8L3N2Zz4K","base64"),w=n("PHN2ZyBjbGFzcz0iZnAtbG9hZGluZy1yb3VuZGVkLWZpbGwiIHdpZHRoPScxMTJweCcgaGVpZ2h0PScxMTJweCcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pZFlNaWQiPgogICAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9Ijc2IiBoZWlnaHQ9Ijc2IiBmaWxsPSJyZ2JhKDAsMCwwLDApIiBjbGFzcz0iYmsiPjwvcmVjdD4KICAgIDxjaXJjbGUgY3g9IjAiIGN5PSIwIiByPSIxMCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjUgMjUpIiBmaWxsPSJyZ2JhKDAsMCwwLC41KSIgY2xhc3M9InNxIj4KICAgICAgPGFuaW1hdGUgYXR0cmlidXRlTmFtZT0iZmlsbCIgZnJvbT0icmdiYSgwLDAsMCwwKSIgdG89InJnYmEoMCwwLDAsLjUpIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgZHVyPSIxLjZzIiBiZWdpbj0iMC4wcyIgdmFsdWVzPSJyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwuNSkiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZT4KICAgICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJzY2FsZSIgYWRkaXRpdmU9InN1bSIgZnJvbT0iMC44IiB0bz0iMSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGJlZ2luPSIwLjBzIiBkdXI9IjEuNnMiIHZhbHVlcz0iMTswLjg7MC44OzE7MSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlVHJhbnNmb3JtPgogICAgPC9jaXJjbGU+CiAgICA8Y2lyY2xlIGN4PSIwIiBjeT0iMCIgcj0iMTAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDUwIDI1KSIgZmlsbD0icmdiYSgwLDAsMCwuNSkiIGNsYXNzPSJzcSI+CiAgICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9ImZpbGwiIGZyb209InJnYmEoMCwwLDAsMCkiIHRvPSJyZ2JhKDAsMCwwLC41KSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGR1cj0iMS42cyIgYmVnaW49IjAuNHMiIHZhbHVlcz0icmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsLjUpIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGU+CiAgICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0ic2NhbGUiIGFkZGl0aXZlPSJzdW0iIGZyb209IjAuOCIgdG89IjEiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBiZWdpbj0iMC40cyIgZHVyPSIxLjZzIiB2YWx1ZXM9IjE7MC44OzAuODsxOzEiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZVRyYW5zZm9ybT4KICAgIDwvY2lyY2xlPgogICAgPGNpcmNsZSBjeD0iMCIgY3k9IjAiIHI9IjEwIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCA1MCkiIGZpbGw9InJnYmEoMCwwLDAsLjUpIiBjbGFzcz0ic3EiPgogICAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJmaWxsIiBmcm9tPSJyZ2JhKDAsMCwwLDApIiB0bz0icmdiYSgwLDAsMCwuNSkiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBkdXI9IjEuNnMiIGJlZ2luPSIwLjhzIiB2YWx1ZXM9InJnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLC41KSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlPgogICAgICA8YW5pbWF0ZVRyYW5zZm9ybSBhdHRyaWJ1dGVOYW1lPSJ0cmFuc2Zvcm0iIHR5cGU9InNjYWxlIiBhZGRpdGl2ZT0ic3VtIiBmcm9tPSIwLjgiIHRvPSIxIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgYmVnaW49IjAuOHMiIGR1cj0iMS42cyIgdmFsdWVzPSIxOzAuODswLjg7MTsxIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGVUcmFuc2Zvcm0+CiAgICA8L2NpcmNsZT4KICAgIDxjaXJjbGUgY3g9IjAiIGN5PSIwIiByPSIxMCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjUgNTApIiBmaWxsPSJyZ2JhKDAsMCwwLC41KSIgY2xhc3M9InNxIj4KICAgICAgPGFuaW1hdGUgYXR0cmlidXRlTmFtZT0iZmlsbCIgZnJvbT0icmdiYSgwLDAsMCwwKSIgdG89InJnYmEoMCwwLDAsLjUpIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgZHVyPSIxLjZzIiBiZWdpbj0iMS4ycyIgdmFsdWVzPSJyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwuNSkiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZT4KICAgICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJzY2FsZSIgYWRkaXRpdmU9InN1bSIgZnJvbT0iMC44IiB0bz0iMSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGJlZ2luPSIxLjJzIiBkdXI9IjEuNnMiIHZhbHVlcz0iMTswLjg7MC44OzE7MSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlVHJhbnNmb3JtPgogICAgPC9jaXJjbGU+Cjwvc3ZnPgo=","base64"),b=n("PHN2ZyBjbGFzcz0iZnAtbG9hZGluZy1zaGFycC1maWxsIiB3aWR0aD0nMTEycHgnIGhlaWdodD0nMTEycHgnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDEwMCAxMDAiIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIj4KICA8cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iNzYiIGhlaWdodD0iNzYiIGZpbGw9InJnYmEoMCwwLDAsMCkiIGNsYXNzPSJiayI+PC9yZWN0PgogIDxyZWN0IHg9Ii0xMCIgeT0iLTEwIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDI1IDI1KSIgZmlsbD0icmdiYSgwLDAsMCwuNSkiIGNsYXNzPSJzcSI+CiAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJmaWxsIiBmcm9tPSJyZ2JhKDAsMCwwLDApIiB0bz0icmdiYSgwLDAsMCwuNSkiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBkdXI9IjEuNnMiIGJlZ2luPSIwLjBzIiB2YWx1ZXM9InJnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLC41KSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlPgogICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJzY2FsZSIgYWRkaXRpdmU9InN1bSIgZnJvbT0iMC44IiB0bz0iMSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGJlZ2luPSIwLjBzIiBkdXI9IjEuNnMiIHZhbHVlcz0iMTswLjg7MC44OzE7MSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlVHJhbnNmb3JtPgogIDwvcmVjdD4KICA8cmVjdCB4PSItMTAiIHk9Ii0xMCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg1MCAyNSkiIGZpbGw9InJnYmEoMCwwLDAsLjUpIiBjbGFzcz0ic3EiPgogICAgPGFuaW1hdGUgYXR0cmlidXRlTmFtZT0iZmlsbCIgZnJvbT0icmdiYSgwLDAsMCwwKSIgdG89InJnYmEoMCwwLDAsLjUpIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgZHVyPSIxLjZzIiBiZWdpbj0iMC40cyIgdmFsdWVzPSJyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwuNSkiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZT4KICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0ic2NhbGUiIGFkZGl0aXZlPSJzdW0iIGZyb209IjAuOCIgdG89IjEiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBiZWdpbj0iMC40cyIgZHVyPSIxLjZzIiB2YWx1ZXM9IjE7MC44OzAuODsxOzEiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZVRyYW5zZm9ybT4KICA8L3JlY3Q+CiAgPHJlY3QgeD0iLTEwIiB5PSItMTAiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNTAgNTApIiBmaWxsPSJyZ2JhKDAsMCwwLC41KSIgY2xhc3M9InNxIj4KICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9ImZpbGwiIGZyb209InJnYmEoMCwwLDAsMCkiIHRvPSJyZ2JhKDAsMCwwLC41KSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGR1cj0iMS42cyIgYmVnaW49IjAuOHMiIHZhbHVlcz0icmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsLjUpIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGU+CiAgICA8YW5pbWF0ZVRyYW5zZm9ybSBhdHRyaWJ1dGVOYW1lPSJ0cmFuc2Zvcm0iIHR5cGU9InNjYWxlIiBhZGRpdGl2ZT0ic3VtIiBmcm9tPSIwLjgiIHRvPSIxIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgYmVnaW49IjAuOHMiIGR1cj0iMS42cyIgdmFsdWVzPSIxOzAuODswLjg7MTsxIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGVUcmFuc2Zvcm0+CiAgPC9yZWN0PgogIDxyZWN0IHg9Ii0xMCIgeT0iLTEwIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDI1IDUwKSIgZmlsbD0icmdiYSgwLDAsMCwuNSkiIGNsYXNzPSJzcSI+CiAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJmaWxsIiBmcm9tPSJyZ2JhKDAsMCwwLDApIiB0bz0icmdiYSgwLDAsMCwuNSkiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBkdXI9IjEuNnMiIGJlZ2luPSIxLjJzIiB2YWx1ZXM9InJnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLC41KSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlPgogICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJzY2FsZSIgYWRkaXRpdmU9InN1bSIgZnJvbT0iMC44IiB0bz0iMSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGJlZ2luPSIxLjJzIiBkdXI9IjEuNnMiIHZhbHVlcz0iMTswLjg7MC44OzE7MSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlVHJhbnNmb3JtPgogIDwvcmVjdD4KPC9zdmc+Cg==","base64"),I=n("PHN2ZyBjbGFzcz0iZnAtbG9hZGluZy1zaGFycC1vdXRsaW5lIiB3aWR0aD0nMTEycHgnIGhlaWdodD0nMTEycHgnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDEwMCAxMDAiIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIj4KICA8cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iNzYiIGhlaWdodD0iNzYiIGZpbGw9InJnYmEoMCwwLDAsMCkiIGNsYXNzPSJiayI+PC9yZWN0PgogIDxyZWN0IHg9Ii05IiB5PSItOSIgd2lkdGg9IjE4IiBoZWlnaHQ9IjE4IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNSAyNSkiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgwLDAsMCwuNSkiIHN0cm9rZS13aWR0aD0iMyUiIGNsYXNzPSJzcSI+CiAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJzdHJva2UiIGZyb209InJnYmEoMCwwLDAsMCkiIHRvPSJyZ2JhKDAsMCwwLC41KSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGR1cj0iMS42cyIgYmVnaW49IjAuMHMiIHZhbHVlcz0icmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsLjUpIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGU+CiAgICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0ic2NhbGUiIGFkZGl0aXZlPSJzdW0iIGZyb209IjAuOCIgdG89IjEiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBiZWdpbj0iMC4wcyIgZHVyPSIxLjZzIiB2YWx1ZXM9IjE7MC44OzAuODsxOzEiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZVRyYW5zZm9ybT4KICA8L3JlY3Q+CiAgPHJlY3QgeD0iLTkiIHk9Ii05IiB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDUwIDI1KSIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJyZ2JhKDAsMCwwLC41KSIgc3Ryb2tlLXdpZHRoPSIzJSIgY2xhc3M9InNxIj4KICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9InN0cm9rZSIgZnJvbT0icmdiYSgwLDAsMCwwKSIgdG89InJnYmEoMCwwLDAsLjUpIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgZHVyPSIxLjZzIiBiZWdpbj0iMC40cyIgdmFsdWVzPSJyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwuNSkiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZT4KICAgICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJzY2FsZSIgYWRkaXRpdmU9InN1bSIgZnJvbT0iMC44IiB0bz0iMSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGJlZ2luPSIwLjRzIiBkdXI9IjEuNnMiIHZhbHVlcz0iMTswLjg7MC44OzE7MSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlVHJhbnNmb3JtPgogIDwvcmVjdD4KICA8cmVjdCB4PSItOSIgeT0iLTkiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNTAgNTApIiBmaWxsPSJub25lIiBzdHJva2U9InJnYmEoMCwwLDAsLjUpIiBzdHJva2Utd2lkdGg9IjMlIiBjbGFzcz0ic3EiPgogICAgPGFuaW1hdGUgYXR0cmlidXRlTmFtZT0ic3Ryb2tlIiBmcm9tPSJyZ2JhKDAsMCwwLDApIiB0bz0icmdiYSgwLDAsMCwuNSkiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBkdXI9IjEuNnMiIGJlZ2luPSIwLjhzIiB2YWx1ZXM9InJnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsMCk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLC41KTtyZ2JhKDAsMCwwLC41KSIga2V5VGltZXM9IjA7MC4xOzAuMjswLjQ7MSI+PC9hbmltYXRlPgogICAgICA8YW5pbWF0ZVRyYW5zZm9ybSBhdHRyaWJ1dGVOYW1lPSJ0cmFuc2Zvcm0iIHR5cGU9InNjYWxlIiBhZGRpdGl2ZT0ic3VtIiBmcm9tPSIwLjgiIHRvPSIxIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgYmVnaW49IjAuOHMiIGR1cj0iMS42cyIgdmFsdWVzPSIxOzAuODswLjg7MTsxIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGVUcmFuc2Zvcm0+CiAgPC9yZWN0PgogIDxyZWN0IHg9Ii05IiB5PSItOSIgd2lkdGg9IjE4IiBoZWlnaHQ9IjE4IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNSA1MCkiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgwLDAsMCwuNSkiIHN0cm9rZS13aWR0aD0iMyUiIGNsYXNzPSJzcSI+CiAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJzdHJva2UiIGZyb209InJnYmEoMCwwLDAsMCkiIHRvPSJyZ2JhKDAsMCwwLC41KSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGR1cj0iMS42cyIgYmVnaW49IjEuMnMiIHZhbHVlcz0icmdiYSgwLDAsMCwuNSk7cmdiYSgwLDAsMCwwKTtyZ2JhKDAsMCwwLDApO3JnYmEoMCwwLDAsLjUpO3JnYmEoMCwwLDAsLjUpIiBrZXlUaW1lcz0iMDswLjE7MC4yOzAuNDsxIj48L2FuaW1hdGU+CiAgICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0ic2NhbGUiIGFkZGl0aXZlPSJzdW0iIGZyb209IjAuOCIgdG89IjEiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBiZWdpbj0iMS4ycyIgZHVyPSIxLjZzIiB2YWx1ZXM9IjE7MC44OzAuODsxOzEiIGtleVRpbWVzPSIwOzAuMTswLjI7MC40OzEiPjwvYW5pbWF0ZVRyYW5zZm9ybT4KICA8L3JlY3Q+Cjwvc3ZnPgo=","base64");
o(function(e,t){function r(e){return a.find(".fp-"+e,t)[0]}function M(e){a.css(T,"padding-top",100*e+"%"),D.inlineBlock||a.height(a.find("object",t)[0],a.height(t))}function C(e){e?(a.addClass(t,"is-mouseover"),a.removeClass(t,"is-mouseout")):(a.addClass(t,"is-mouseout"),a.removeClass(t,"is-mouseover"))}a.find(".fp-filters").forEach(a.removeNode);try{var A;document.body.appendChild(A=a.createElement("div",{},n("PHN2ZyBjbGFzcz0iZnAtZmlsdGVycyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMCAwIj4KICA8ZGVmcz4KICAgIDxmaWx0ZXIgaWQ9ImYxIiB4PSItMjAlIiB5PSItMjAlIiB3aWR0aD0iMjAwJSIgaGVpZ2h0PSIyMDAlIj4KICAgICAgPGZlT2Zmc2V0IHJlc3VsdD0ib2ZmT3V0IiBpbj0iU291cmNlQWxwaGEiIGR4PSIwIiBkeT0iMCIgLz4KICAgICAgPGZlQ29sb3JNYXRyaXggcmVzdWx0PSJtYXRyaXhPdXQiIGluPSJvZmZPdXQiIHR5cGU9Im1hdHJpeCIKICAgICAgdmFsdWVzPSIwLjMgMCAwIDAgMCAwIDAuMyAwIDAgMCAwIDAgMC4zIDAgMCAwIDAgMCAwLjQgMCIgLz4KICAgICAgPGZlR2F1c3NpYW5CbHVyIHJlc3VsdD0iYmx1ck91dCIgaW49Im1hdHJpeE91dCIgc3RkRGV2aWF0aW9uPSI0IiAvPgogICAgICA8ZmVCbGVuZCBpbj0iU291cmNlR3JhcGhpYyIgaW4yPSJibHVyT3V0IiBtb2RlPSJub3JtYWwiIC8+CiAgICA8L2ZpbHRlcj4KICA8L2RlZnM+Cjwvc3ZnPgo=","base64"))),a.css(A,{width:0,height:0,overflow:"hidden",position:"absolute",margin:0,padding:0})}catch(S){}var E,j=e.conf,D=o.support;a.find(".fp-ratio,.fp-ui",t).forEach(a.removeNode),a.addClass(t,"flowplayer"),t.appendChild(a.createElement("div",{className:"fp-ratio"}));var N=a.createElement("div",{className:"fp-ui"},'         <div class="fp-waiting">           {{ LOADING_SHARP_OUTLINE }}           {{ LOADING_SHARP_FILL }}           {{ LOADING_ROUNDED_FILL }}           {{ LOADING_ROUNDED_OUTLINE }}         </div>         <div class="fp-header">           <a class="fp-share fp-icon"></a>           <a class="fp-fullscreen fp-icon"></a>           <a class="fp-unload fp-icon"></a>         </div>         <p class="fp-speed-flash"></p>         <div class="fp-play fp-visible">           <a class="fp-icon fp-playbtn"></a>           {{ PLAY_ROUNDED_FILL }}           {{ PLAY_ROUNDED_OUTLINE }}           {{ PLAY_SHARP_FILL }}           {{ PLAY_SHARP_OUTLINE }}         </div>         <div class="fp-pause">           <a class="fp-icon fp-playbtn"></a>           {{ PAUSE_SHARP_OUTLINE }}           {{ PAUSE_SHARP_FILL }}           {{ PAUSE_ROUNDED_OUTLINE }}           {{ PAUSE_ROUNDED_FILL }}         </div>         <div class="fp-controls">            <a class="fp-icon fp-playbtn"></a>            <span class="fp-elapsed">00:00</span>            <div class="fp-timeline fp-bar">               <span class="fp-timestamp"></span>               <div class="fp-progress fp-color"></div>            </div>            <span class="fp-duration"></span>            <span class="fp-remaining"></span>            <div class="fp-volume">               <a class="fp-icon fp-volumebtn"></a>               <div class="fp-volumebar fp-bar-slider">                 <em></em><em></em><em></em><em></em><em></em><em></em><em></em>               </div>            </div>            <strong class="fp-speed fp-hidden"></strong>         </div>'.replace("{{ PAUSE_ROUNDED_FILL }}",g).replace("{{ PAUSE_ROUNDED_OUTLINE }}",h).replace("{{ PAUSE_SHARP_FILL }}",m).replace("{{ PAUSE_SHARP_OUTLINE }}",v).replace("{{ PLAY_SHARP_OUTLINE }}",p).replace("{{ PLAY_SHARP_FILL }}",d).replace("{{ PLAY_ROUNDED_OUTLINE }}",c).replace("{{ PLAY_ROUNDED_FILL }}",f).replace("{{ LOADING_ROUNDED_OUTLINE }}",y).replace("{{ LOADING_ROUNDED_FILL }}",w).replace("{{ LOADING_SHARP_FILL }}",b).replace("{{ LOADING_SHARP_OUTLINE }}",I).replace(/url\(#/g,"url("+window.location.href.replace(window.location.hash,"").replace(/\#$/g,"")+"#"));t.appendChild(N);var x=r("waiting"),L=r("elapsed"),T=r("ratio"),Z=r("speed-flash"),P=r("duration"),k=r("remaining"),Y=r("timestamp"),z=a.css(T,"padding-top"),O=r("play"),G=r("pause"),R=r("timeline"),W=l(R,e.rtl),U=r("fullscreen"),J=r("volumebar"),B=u(J,{rtl:e.rtl}),F=a.hasClass(t,"no-toggle");W.disableAnimation(a.hasClass(t,"is-touch")),e.sliders=e.sliders||{},e.sliders.timeline=W,e.sliders.volume=B;var H=[];D.svg||a.html(x,"<p>loading &hellip;</p>"),j.ratio&&M(j.ratio);try{j.fullscreen||a.removeNode(U)}catch(S){a.removeNode(U)}e.on("dvrwindow",function(){W.disable(!1)}),e.on("ready",function(e,n,r){var o=n.video.duration;W.disable(n.disabled||!o),j.adaptiveRatio&&!isNaN(r.height/r.width)&&M(r.height/r.width,!0),a.html([P,k],n.live?"Live":i(o)),a.toggleClass(t,"is-long",o>=3600),B.slide(n.volumeLevel),"flash"===n.engine.engineName?W.disableAnimation(!0,!0):W.disableAnimation(!1),a.find(".fp-title",N).forEach(a.removeNode),r.title&&a.prepend(N,a.createElement("div",{className:"fp-message fp-title"},r.title)),a.toggleClass(t,"has-title",!!r.title)}).on("unload",function(){z||j.splash||a.css(T,"paddingTop",""),W.slide(0),a.addClass(O,"fp-visible")}).on("buffer",function(e,t,n){var r=t.video,i=r.buffer/r.duration;!r.seekable&&D.seekable&&W.max(t.conf.live?1/0:i),n&&"number"!=typeof n||(n=[{start:0,end:r.buffer}]);var o=a.find(".fp-buffer",R);o.length!==n.length&&(o.forEach(a.removeNode),o=[]),n.forEach(function(e,t){var n=o[t]||a.createElement("div",{className:"fp-buffer"});a.css(n,{left:100*e.start/r.duration+"%",width:100*(e.end-e.start)/r.duration+"%"}),a.prepend(R,n)})}).on("speed",function(e,t,n){t.video.time&&(a.text(Z,n+"x"),a.addClass(Z,"fp-shown"),H=H.filter(function(e){return clearTimeout(e),!1}),H.push(setTimeout(function(){a.addClass(Z,"fp-hilite"),H.push(setTimeout(function(){a.removeClass(Z,"fp-hilite"),H.push(setTimeout(function(){a.removeClass(Z,"fp-shown")},300))},1e3))})))}).on("buffered",function(){W.max(1)}).on("progress seek",function(n,r,o){var s=e.video.duration,l=e.video.seekOffset||0;o=o||e.video.time;var u=(o-l)/(s-l);W.dragging||W.slide(u,e.seeking?0:250),a.toggleClass(t,"is-live-position",s-o<j.livePositionOffset),a.html(L,i(o)),a.html(k,i(s-o,!0))}).on("finish resume seek",function(e){a.toggleClass(t,"is-finished","finish"==e.type)}).on("resume",function(){a.addClass(O,"fp-visible"),setTimeout(function(){a.removeClass(O,"fp-visible")},300)}).on("pause",function(){a.addClass(G,"fp-visible"),setTimeout(function(){a.removeClass(G,"fp-visible")},300)}).on("stop",function(){a.html(L,i(0)),W.slide(0,100)}).on("finish",function(){a.html(L,i(e.video.duration)),W.slide(1,100),a.removeClass(t,"is-seeking")}).on("beforeseek",function(){}).on("volume",function(){B.slide(e.volumeLevel)}).on("disable",function(){var n=e.disabled;W.disable(n),B.disable(n),a.toggleClass(t,"is-disabled",e.disabled)}).on("mute",function(e,n,r){a.toggleClass(t,"is-muted",r)}).on("error",function(e,n,r){if(a.removeClass(t,"is-loading"),a.removeClass(t,"is-seeking"),a.addClass(t,"is-error"),r){n.error=!0;var i=r.code;(r.message||"").match(/DECODER_ERROR_NOT_SUPPORTED/)&&(i=3);var o=n.message((n.engine&&n.engine.engineName||"html5")+": "+j.errors[i]);a.removeClass(t,"is-mouseover"),n.one("load progress",function(){o()})}}).one("resume ready",function(){var e=a.find("video.fp-engine",t)[0];if(e&&(!a.width(e)||!a.height(e))){var n=t.style.overflow;t.style.overflow="visible",setTimeout(function(){n?t.style.overflow=n:t.style.removeProperty("overflow")})}}),s.on(t,"mouseenter mouseleave",function(n){if(!F){var r,i="mouseover"==n.type;if(C(i),i){var o=function(){C(!0),r=new Date};e.on("pause.x volume.x",o),s.on(t,"mousemove.x",o),E=setInterval(function(){new Date-r>j.mouseoutTimeout&&(C(!1),r=new Date)},100)}else s.off(t,"mousemove.x"),e.off("pause.x volume.x"),clearInterval(E)}}),s.on(t,"mouseleave",function(){(W.dragging||B.dragging)&&(a.addClass(t,"is-mouseover"),a.removeClass(t,"is-mouseout"))}),s.on(t,"click.player",function(t){return e.disabled?void 0:a.hasClass(t.target,"fp-ui")||a.hasClass(t.target,"fp-engine")||t.flash||a.hasParent(t.target,".fp-play,.fp-pause")?(t.preventDefault&&t.preventDefault(),e.toggle()):void 0}),s.on(t,"mousemove",".fp-timeline",function(t){var n=t.pageX||t.clientX,r=n-a.offset(R).left,o=r/a.width(R),s=e.video,l=s.duration-(void 0===s.seekOffset?0:s.seekOffset),u=(e.rtl?1-o:o)*l;if(!(0>o)){a.html(Y,i(u));var c=r-a.width(Y)/2;0>c&&(c=0),c>a.width(R)-a.width(Y)&&(c=!1),c!==!1?a.css(Y,{left:c+"px",right:"auto"}):a.css(Y,{left:"auto",right:"0px"})}}),s.on(t,"contextmenu",function(n){var r=window;if(!a.hasClass(t,"is-flash-disabled")){var i=a.find(".fp-context-menu",t)[0];i&&(n.preventDefault(),e.showMenu(i,{left:n.clientX-r.scrollX,top:n.clientY-r.scrollY}),s.on(t,"click",".fp-context-menu",function(e){e.stopPropagation()}))}}),e.on("flashdisabled",function(n,r,i){a.addClass(t,"is-flash-disabled");var o;i!==!1&&(o=e.message("Seems something is blocking Adobe Flash from running")),e.one("ready progress",function(){a.removeClass(t,"is-flash-disabled"),o&&o()})}),j.poster&&a.css(t,"background-image","url("+j.poster+")");var V=a.css(t,"background-color"),X="none"!=a.css(t,"background-image")||V&&"rgba(0, 0, 0, 0)"!=V&&"transparent"!=V;if(X&&!j.splash){j.poster||(j.poster=!0);var _=function(){a.addClass(t,"is-poster"),a.addClass(O,"fp-visible"),e.poster=!0,e.on("resume.poster progress.poster beforeseek.poster",function(n){("beforeseek"===n.type||e.playing)&&(a.removeClass(t,"is-poster"),a.removeClass(O,"fp-visible"),e.poster=!1,e.off(".poster"))})};e.on("stop",function(){_()}),e.on("ready",function(e,t,n){n.index||n.autoplay||_()})}"string"==typeof j.splash&&a.css(t,"background-image","url('"+j.splash+"')"),!X&&e.forcedSplash&&a.css(t,"background-color","#555"),s.on(t,"click",".fp-toggle, .fp-play, .fp-playbtn",function(){e.disabled||e.toggle()}),s.on(t,"click",".fp-volumebtn",function(){e.mute()}),s.on(t,"click",".fp-fullscreen",function(){e.fullscreen()}),s.on(t,"click",".fp-unload",function(){e.unload()}),s.on(R,"slide",function(t){e.seeking=!0,e.seekTo(10*t)}),s.on(J,"slide",function(t){e.volume(t)}),s.on(t,"click",".fp-duration,.fp-remaining",function(){return e.dvr?e.seekTo(10):void a.toggleClass(t,"is-inverted")}),C(F);var K;if(e.on("shutdown",function(){s.off(R),s.off(J),K&&window.cancelAnimationFrame(K),a.removeNode(N),a.find(".fp-ratio",t).forEach(a.removeNode)}),"functionDISABLED-FOR-PERFORMANCE-REASONS"==typeof window.requestAnimationFrame){var Q=a.find(".fp-player",t)[0]||t,q=function(){a.toggleClass(t,"is-tiny",Q.clientWidth<400),a.toggleClass(t,"is-small",Q.clientWidth<600&&Q.clientWidth>=400),K=window.requestAnimationFrame(q)};K=window.requestAnimationFrame(q)}}),t.exports.format=i}).call(this,e("buffer").Buffer)},{"../common":1,"../flowplayer":31,"./ui/bar-slider":28,"./ui/slider":29,bean:34,buffer:35}],28:[function(e,t,n){function r(e,t){function n(t){var n=t.pageX||t.clientX,r=o.offset(e),i=o.width(e);!n&&t.originalEvent&&t.originalEvent.touches&&t.originalEvent.touches.length&&(n=t.originalEvent.touches[0].pageX);var a=n-r.left;a=Math.max(0,Math.min(i,a));var s=a/i;return l&&(s=1-s),s}t=t||{};var r=t.activeClass||"fp-color",a=t.inactiveClass||"fp-grey",s=t.childSelector||"em",l=!!t.rtl,u=!1,c=o.find(s,e).length,f={unload:function(){i.off(e,".barslider")},slide:function(t,n){o.find(s,e).forEach(function(e,n){var i=t>n/c;o.toggleClass(e,r,i),o.toggleClass(e,a,!i)}),n&&i.fire(e,"slide",[t])},disable:function(e){u=e}};return i.on(e,"mousedown.sld touchstart.sld",function(t){t.preventDefault(),u||(f.slide(n(t),!0),i.on(document,"mousemove.sld touchmove.sld",function(e){e.preventDefault(),f.slide(n(e),!0)}),i.one(document,"mouseup.sld touchup.sld",function(){i.off(document,"mousemove.sld touchmove.sld")}))}),f}var i=e("bean"),o=e("../../common");t.exports=r},{"../../common":1,bean:34}],29:[function(e,t,n){"use strict";var r=e("bean"),i=e("../../common"),o=function(e,t){var n;return function(){n||(e.apply(this,arguments),n=1,setTimeout(function(){n=0},t))}},a=function(e,t){var n,a,s,l,u,c,f,d,p=i.lastChild(e),h=!1,g=function(){a=i.offset(e),s=i.width(e),l=i.height(e),c=u?l:s,d=w(f)},m=function(t){n||t==b.value||f&&!(f>t)||(r.fire(e,"slide",[t]),b.value=t)},v=function(e){var n=e.pageX||e.clientX;!n&&e.originalEvent&&e.originalEvent.touches&&e.originalEvent.touches.length&&(n=e.originalEvent.touches[0].pageX);var r=u?e.pageY-a.top:n-a.left;r=Math.max(0,Math.min(d||c,r));var i=r/c;return u&&(i=1-i),t&&(i=1-i),y(i,0,!0)},y=function(e,t){void 0===t&&(t=0),e>1&&(e=1);var n=Math.round(1e3*e)/10+"%";return(!f||f>=e)&&(h?i.removeClass(p,"animated"):(i.addClass(p,"animated"),i.css(p,"transition-duration",(t||0)+"ms")),i.css(p,"width",n)),e},w=function(e){return Math.max(0,Math.min(c,u?(1-e)*l:e*s))},b={max:function(e){f=e},disable:function(e){n=e},slide:function(e,t,n){g(),n&&m(e),y(e,t)},disableAnimation:function(t,n){h=t!==!1,i.toggleClass(e,"no-animation",!!n)}};return g(),r.on(e,"mousedown.sld touchstart",function(t){if(t.preventDefault(),!n){var a=o(m,100);g(),b.dragging=!0,i.addClass(e,"is-dragging"),m(v(t)),r.on(document,"mousemove.sld touchmove.sld",function(e){e.preventDefault(),a(v(e))}),r.one(document,"mouseup touchend",function(){b.dragging=!1,i.removeClass(e,"is-dragging"),r.off(document,"mousemove.sld touchmove.sld")})}}),b};t.exports=a},{"../../common":1,bean:34}],30:[function(e,t,n){function r(e){var t=document.createElement("textarea");t.value=e,t.style.opacity=0,t.style.position="absolute",document.body.appendChild(t),t.select();var n=document.execCommand("copy");if(document.body.removeChild(t),!n)throw new Error("Unsuccessfull")}t.exports=function(e,t,n){try{r(e),t()}catch(i){n(i)}}},{}],31:[function(e,t,n){"use strict";function r(e,t,n){t&&t.embed&&(t.embed=i({},v.defaults.embed,t.embed));var r=!1;try{"undefined"==typeof v.conf.storage&&"object"==typeof window.localStorage&&(window.localStorage.flowplayerTestStorage="test",r=!0)}catch(s){}var l,p,h=e,g=i({},v.defaults,v.conf,t),m={},b=h.className,I=new w;u.addClass(h,"is-loading"),u.toggleClass(h,"no-flex",!v.support.flex),u.toggleClass(h,"no-svg",!v.support.svg);try{m=v.conf.storage||(r?window.localStorage:m)}catch(M){}if(g.volume="true"===m.muted?0:g.volume!==v.defaults.volume?g.volume:isNaN(m.volume)?g.volume:m.volume,g.debug=!!m.flowplayerDebug||g.debug,g.aspectRatio&&"string"==typeof g.aspectRatio){var C=g.aspectRatio.split(/[:\/]/);g.ratio=C[1]/C[0]}var A=h.currentStyle&&"rtl"===h.currentStyle.direction||window.getComputedStyle&&null!==window.getComputedStyle(h,null)&&"rtl"===window.getComputedStyle(h,null).getPropertyValue("direction");A&&u.addClass(h,"is-rtl");var S={conf:g,currentSpeed:1,volumeLevel:g.muted?0:"undefined"==typeof g.volume?1*m.volume:g.volume,video:{},disabled:!1,finished:!1,loading:!1,muted:"true"==m.muted||g.muted,paused:!1,playing:!1,ready:!1,splash:!1,rtl:A,hijack:function(e){try{S.engine.suspendEngine()}catch(t){}S.hijacked=e},release:function(){try{S.engine.resumeEngine()}catch(e){}S.hijacked=!1},debug:function(){g.debug&&console.log.apply(console,["DEBUG"].concat([].slice.call(arguments)))},load:function(e,t){if(!S.error&&!S.loading){S.video={},S.finished=!1,e=e||g.clip,e=i({},I.resolve(e,g.clip.sources)),(S.playing||S.engine)&&(e.autoplay=!0);var n=E(e);if(!n)return setTimeout(function(){S.trigger("error",[S,{code:v.support.flashVideo?5:10}])})&&S;if(!n.engineName)throw new Error("engineName property of factory should be exposed");if(S.engine&&n.engineName===S.engine.engineName||(S.ready=!1,S.engine&&(S.engine.unload(),S.conf.autoplay=!0),p=S.engine=n(S,h),S.one("ready",function(){setTimeout(function(){S.muted?S.mute(!0,!0):p.volume(S.volumeLevel)})})),i(e,p.pick(e.sources.filter(function(e){return e.engine?e.engine===p.engineName:!0}))),e.src){var r=S.trigger("load",[S,e,p],!0);r.defaultPrevented?S.loading=!1:(S.ready=!1,p.load(e),o(e)&&(t=e),t&&S.one("ready",t))}return S}},pause:function(e){return S.hijacked?S.hijacked.pause(e)|S:(!S.ready||S.seeking||S.loading||(p.pause(),S.one("pause",e)),S)},resume:function(){var e=S.trigger("beforeresume",[S],!0);if(!e.defaultPrevented)return S.hijacked?S.hijacked.resume()|S:(S.ready&&S.paused&&(p.resume(),S.finished&&(S.trigger("resume",[S]),S.finished=!1)),S)},toggle:function(){return S.ready?S.paused?S.resume():S.pause():S.load()},seek:function(e,t){if("boolean"==typeof e){var n=S.conf.seekStep||.1*S.video.duration;e=S.video.time+(e?n:-n),e=Math.min(Math.max(e,0),S.video.duration-.1)}if("undefined"==typeof e)return S;if(S.hijacked)return S.hijacked.seek(e,t)|S;if(S.ready){l=e;var r=S.trigger("beforeseek",[S,e],!0);r.defaultPrevented?(S.seeking=!1,u.toggleClass(h,"is-seeking",S.seeking)):(p.seek(e),o(t)&&S.one("seek",t))}return S},seekTo:function(e,t){return void 0===e?S.seek(l,t):void 0!==S.video.seekOffset?S.seek(S.video.seekOffset+.1*(S.video.duration-S.video.seekOffset)*e,t):S.seek(.1*S.video.duration*e,t)},mute:function(e,t){return void 0===e&&(e=!S.muted),S.muted=e,t||(m.muted=e,m.volume=isNaN(m.volume)?g.volume:m.volume),"undefined"!=typeof p.mute?p.mute(e):(S.volume(e?0:m.volume,!0),S.trigger("mute",[S,e])),S},volume:function(e,t){return S.ready&&(e=Math.min(Math.max(e,0),1),t||(m.volume=e),p.volume(e)),S},speed:function(e,t){return S.ready&&("boolean"==typeof e&&(e=g.speeds[g.speeds.indexOf(S.currentSpeed)+(e?1:-1)]||S.currentSpeed),p.speed(e),t&&h.one("speed",t)),S},stop:function(){return S.ready&&(S.pause(),!S.live||S.dvr?S.seek(0,function(){S.trigger("stop",[S])}):S.trigger("stop",[S])),S},unload:function(){return g.splash?(S.trigger("unload",[S]),p&&(p.unload(),S.engine=p=0)):S.stop(),S},shutdown:function(){S.unload(),S.trigger("shutdown",[S]),a.off(h),delete f[h.getAttribute("data-flowplayer-instance-id")],h.removeAttribute("data-flowplayer-instance-id")},disable:function(e){return void 0===e&&(e=!S.disabled),e!=S.disabled&&(S.disabled=e,S.trigger("disable",e)),S},registerExtension:function(e,t){e=e||[],t=t||[],"string"==typeof e&&(e=[e]),"string"==typeof t&&(t=[t]),e.forEach(function(e){S.extensions.js.push(e)}),t.forEach(function(e){S.extensions.css.push(e)})}};S.conf=i(S.conf,g),S.extensions={js:[],css:[]},v.extensions.forEach(function(e){S.registerExtension(e[0],e[1])}),c(S);var E=function(e){var t,n=v.engines;if(g.engine){var r=n.filter(function(e){return e.engineName===g.engine})[0];if(r&&e.sources.some(function(e){return e.engine&&e.engine!==r.engineName?!1:r.canPlay(e.type,S.conf)}))return r}return g.enginePreference&&(n=v.engines.filter(function(e){return g.enginePreference.indexOf(e.engineName)>-1}).sort(function(e,t){return g.enginePreference.indexOf(e.engineName)-g.enginePreference.indexOf(t.engineName)})),e.sources.some(function(e){var r=n.filter(function(t){return e.engine&&e.engine!==t.engineName?!1:t.canPlay(e.type,S.conf)}).shift();return r&&(t=r),!!r}),t};return h.getAttribute("data-flowplayer-instance-id")||(h.setAttribute("data-flowplayer-instance-id",y++),S.on("boot",function(){var e=v.support;(g.splash||u.hasClass(h,"is-splash")||!e.firstframe)&&(S.forcedSplash=!g.splash&&!u.hasClass(h,"is-splash"),S.splash=!0,g.splash||(g.splash=!0),u.addClass(h,"is-splash")),g.splash&&u.find("video",h).forEach(u.removeNode),(g.dvr||g.live||u.hasClass(h,"is-live"))&&(S.live=g.live=!0,S.dvr=g.dvr=!!g.dvr||u.hasClass(h,"is-dvr"),u.addClass(h,"is-live"),u.toggleClass(h,"is-dvr",S.dvr)),d.forEach(function(e){e(S,h)}),f.push(S),g.splash?S.unload():S.load(),g.disabled&&S.disable(),S.one("ready",n),S.one("shutdown",function(){h.className=b})}).on("load",function(e,t,n){g.splash&&u.find(".flowplayer.is-ready,.flowplayer.is-loading").forEach(function(e){var t=e.getAttribute("data-flowplayer-instance-id");if(t!==h.getAttribute("data-flowplayer-instance-id")){var n=f[Number(t)];n&&n.conf.splash&&n.unload()}}),u.addClass(h,"is-loading"),t.loading=!0,("undefined"!=typeof n.live||"undefined"!=typeof n.dvr)&&(u.toggleClass(h,"is-live",n.dvr||n.live),u.toggleClass(h,"is-dvr",!!n.dvr),t.live=n.dvr||n.live,t.dvr=!!n.dvr)}).on("ready",function(e,t,n){n.time=0,t.video=n,u.removeClass(h,"is-loading"),t.loading=!1,t.muted?t.mute(!0,!0):t.volume(t.volumeLevel);var r=t.conf.hlsFix&&/mpegurl/i.exec(n.type);u.toggleClass(h,"hls-fix",!!r)}).on("unload",function(){u.removeClass(h,"is-loading"),S.loading=!1}).on("ready unload",function(e){var t="ready"==e.type;u.toggleClass(h,"is-splash",!t),u.toggleClass(h,"is-ready",t),S.ready=t,S.splash=!t}).on("progress",function(e,t,n){t.video.time=n}).on("buffer",function(e,t,n){t.video.buffer="number"==typeof n?n:n.length?n[n.length-1].end:0}).on("speed",function(e,t,n){t.currentSpeed=n}).on("volume",function(e,t,n){t.volumeLevel=Math.round(100*n)/100,t.muted&&n&&t.mute(!1)}).on("beforeseek seek",function(e){S.seeking="beforeseek"==e.type,u.toggleClass(h,"is-seeking",S.seeking)}).on("ready pause resume unload finish stop",function(e){S.paused=/pause|finish|unload|stop/.test(e.type),S.paused=S.paused||"ready"===e.type&&!g.autoplay&&!S.playing,S.playing=!S.paused,u.toggleClass(h,"is-paused",S.paused),u.toggleClass(h,"is-playing",S.playing),S.load.ed||S.pause()}).on("finish",function(){S.finished=!0}).on("error",function(){})),S.trigger("boot",[S,h]),S}var i=e("extend-object"),o=e("is-function"),a=e("bean"),s=e("./ext/ui/slider"),l=e("./ext/ui/bar-slider"),u=e("./common"),c=e("./ext/events"),f=[],d=[],p=window.onbeforeunload;window.onbeforeunload=function(e){return f.forEach(function(e){e.conf.splash?e.unload():e.bind("error",function(){u.find(".flowplayer.is-error .fp-message").forEach(u.removeNode)})}),p?p(e):void 0};var h=/Safari/.exec(navigator.userAgent)&&!/Chrome/.exec(navigator.userAgent),g=/(\d+\.\d+) Safari/.exec(navigator.userAgent),m=g?Number(g[1]):100,v=t.exports=function(e,t,n){if(o(e))return d.push(e);if("number"==typeof e||"undefined"==typeof e)return f[e||0];if(e.nodeType){if(null!==e.getAttribute("data-flowplayer-instance-id"))return f[e.getAttribute("data-flowplayer-instance-id")];if(!t)return;return r(e,t,n)}if(e.jquery)return v(e[0],t,n);if("string"==typeof e){var i=u.find(e)[0];return i&&v(i,t,n)}};i(v,{version:"7.2.7",engines:[],engine:function(e){return v.engines.filter(function(t){return t.engineName===e})[0]},extensions:[],conf:{},set:function(e,t){"string"==typeof e?v.conf[e]=t:i(v.conf,e)},registerExtension:function(e,t){v.extensions.push([e,t])},support:{},defaults:{debug:!1,disabled:!1,fullscreen:window==window.top,keyboard:!0,ratio:9/16,adaptiveRatio:!1,rtmp:0,proxy:"best",hlsQualities:!0,seekStep:!1,splash:!1,live:!1,livePositionOffset:120,swf:"//releases.flowplayer.org/7.2.7/commercial/flowplayer.swf",swfHls:"//releases.flowplayer.org/7.2.7/commercial/flowplayerhls.swf",speeds:[.25,.5,1,1.5,2],tooltip:!0,mouseoutTimeout:5e3,mutedAutoplay:!0,clickToUnMute:!0,volume:1,errors:["","Video loading aborted","Network error","Video not properly encoded","Video file not found","Unsupported video","Skin not found","SWF file not found","Subtitles not found","Invalid RTMP URL","Unsupported video format. Try installing Adobe Flash."],errorUrls:["","","","","","","","","","","http://get.adobe.com/flashplayer/"],playlist:[],hlsFix:h&&8>m,disableInline:!1},bean:a,common:u,slider:s,barSlider:l,extend:i});var y=0,w=e("./ext/resolve");if("undefined"!=typeof window.jQuery){var b=window.jQuery;b(function(){"function"==typeof b.fn.flowplayer&&b('.flowplayer:has(video:not(.fp-engine),script[type="application/json"])').flowplayer()});var I=function(e){if(!e.length)return{};var t=e.data()||{},n={};return b.each(["autoplay","loop","preload","poster"],function(r,i){var o=e.attr(i);void 0!==o&&-1!==["autoplay","poster"].indexOf(i)?n[i]=o?o:!0:void 0!==o&&(t[i]=o?o:!0)}),e[0].autoplay=e[0].preload=!1,t.subtitles=e.find("track").map(function(){var e=b(this);return{src:e.attr("src"),kind:e.attr("kind"),label:e.attr("label"),srclang:e.attr("srclang"),"default":e.prop("default")}}).get(),t.sources=(new w).sourcesFromVideoTag(e,b),i(n,{clip:t})};b.fn.flowplayer=function(e,t){return this.each(function(){"string"==typeof e&&(e={swf:e}),o(e)&&(t=e,e={});var n=b(this),i=n.find('script[type="application/json"]'),a=i.length?JSON.parse(i.text()):I(n.find("video")),s=b.extend({},e||{},a,n.data()),l=r(this,s,t);c.EVENTS.forEach(function(e){l.on(e+".jquery",function(e){n.trigger.call(n,e.type,e.detail&&e.detail.args)})}),n.data("flowplayer",l)})}}},{"./common":1,"./ext/events":12,"./ext/resolve":21,"./ext/ui/bar-slider":28,"./ext/ui/slider":29,bean:34,"extend-object":39,"is-function":42}],32:[function(e,t,n){e("es5-shim");var r=t.exports=e("./flowplayer");e("./ext/support"),e("./engine/embed"),e("./engine/hlsjs"),e("./engine/html5"),e("./engine/flash"),e("./ext/ui"),e("./ext/message"),e("./ext/keyboard"),e("./ext/playlist"),e("./ext/cuepoint"),e("./ext/subtitle"),e("./ext/analytics"),e("./ext/share"),e("./ext/facebook"),e("./ext/twitter"),e("./ext/embed"),e("./ext/airplay"),e("./ext/chromecast"),e("./ext/qsel"),e("./ext/menu"),e("./ext/fullscreen"),e("./ext/mobile"),r(function(e,t){function n(e){var t=document.createElement("a");return t.href=e,u.hostname(t.hostname)}function i(e){var t="ab.ca,ac.ac,ac.ae,ac.at,ac.be,ac.cn,ac.il,ac.in,ac.jp,ac.kr,ac.sg,ac.th,ac.uk,ad.jp,adm.br,adv.br,ah.cn,am.br,arq.br,art.br,arts.ro,asn.au,asso.fr,asso.mc,bc.ca,bel.tr,bio.br,biz.pl,biz.tr,bj.cn,br.com,cn.com,cng.br,cnt.br,co.ac,co.at,co.de,co.gl,co.hk,co.id,co.il,co.in,co.jp,co.kr,co.mg,co.ms,co.nz,co.th,co.uk,co.ve,co.vi,co.za,com.ag,com.ai,com.ar,com.au,com.br,com.cn,com.co,com.cy,com.de,com.do,com.ec,com.es,com.fj,com.fr,com.gl,com.gt,com.hk,com.hr,com.hu,com.kg,com.ki,com.lc,com.mg,com.mm,com.ms,com.mt,com.mu,com.mx,com.my,com.na,com.nf,com.ng,com.ni,com.pa,com.ph,com.pl,com.pt,com.qa,com.ro,com.ru,com.sb,com.sc,com.sg,com.sv,com.tr,com.tw,com.ua,com.uy,com.ve,com.vn,cp.tz,cq.cn,de.com,de.org,ecn.br,ed.jp,edu.au,edu.cn,edu.hk,edu.mm,edu.my,edu.pl,edu.pt,edu.qa,edu.sg,edu.tr,edu.tw,eng.br,ernet.in,esp.br,etc.br,eti.br,eu.com,eu.int,eu.lv,firm.in,firm.ro,fm.br,fot.br,fst.br,g12.br,gb.com,gb.net,gd.cn,gen.in,go.jp,go.kr,go.th,gov.au,gov.az,gov.br,gov.cn,gov.il,gov.in,gov.mm,gov.my,gov.qa,gov.sg,gov.tr,gov.tw,gov.uk,govt.nz,gr.jp,gs.cn,gv.ac,gv.at,gx.cn,gz.cn,he.cn,hi.cn,hk.cn,hl.cn,hu.com,id.au,idv.tw,in.th,in.ua,ind.br,ind.in,inf.br,info.pl,info.ro,info.tr,info.ve,iwi.nz,jl.cn,jor.br,js.cn,jus.br,k12.il,k12.tr,kr.com,lel.br,lg.jp,ln.cn,ltd.uk,maori.nz,mb.ca,me.uk,med.br,mi.th,mil.br,mil.uk,mo.cn,mod.uk,muni.il,nb.ca,ne.jp,ne.kr,net.ag,net.ai,net.au,net.br,net.cn,net.do,net.gl,net.hk,net.il,net.in,net.kg,net.ki,net.lc,net.mg,net.mm,net.mu,net.ni,net.nz,net.pl,net.ru,net.sb,net.sc,net.sg,net.th,net.tr,net.tw,net.uk,net.ve,nf.ca,nhs.uk,nm.cn,nm.kr,no.com,nom.br,nom.ni,nom.ro,ns.ca,nt.ca,nt.ro,ntr.br,nx.cn,odo.br,off.ai,on.ca,or.ac,or.at,or.jp,or.kr,or.th,org.ag,org.ai,org.au,org.br,org.cn,org.do,org.es,org.gl,org.hk,org.in,org.kg,org.ki,org.lc,org.mg,org.mm,org.ms,org.nf,org.ng,org.ni,org.nz,org.pl,org.ro,org.ru,org.sb,org.sc,org.sg,org.tr,org.tw,org.uk,org.ve,pe.ca,plc.uk,police.uk,ppg.br,presse.fr,pro.br,psc.br,psi.br,qc.ca,qc.com,qh.cn,rec.br,rec.ro,res.in,sa.com,sc.cn,sch.uk,se.com,se.net,sh.cn,sk.ca,slg.br,sn.cn,store.ro,tj.cn,tm.fr,tm.mc,tm.ro,tmp.br,tur.br,tv.br,tv.tr,tw.cn,uk.com,uk.net,us.com,uy.com,vet.br,waw.pl,web.ve,www.ro,xj.cn,xz.cn,yk.ca,yn.cn,zj.cn,zlg.br".split(",");e=e.toLowerCase();var n=e.split("."),r=n.length;if(2>r||/^\d+$/.test(n[r-1]))return e;var i=n.slice(-2).join(".");return r>=3&&t.indexOf(i)>=0?n.slice(-3).join("."):i}function o(e,t){t=i(t);for(var n=0,r=t.length-1;r>=0;r--)n+=0x65cb98ae4ad1ec*t.charCodeAt(r);for(n=(""+n).substring(0,7),r=0;r<e.length;r++)if(n===e[r].substring(1,8))return 1}var a=function(e,t){var n=e.className.split(" ");-1===n.indexOf(t)&&(e.className+=" "+t)},s=function(e){return"none"!==window.getComputedStyle(e).display},l=e.conf,u=r.common,c=u.createElement,f=l.swf.indexOf("flowplayer.org")&&l.e&&t.getAttribute("data-origin"),d=f?n(f):u.hostname(),p=(document,l.key);if("file:"==location.protocol&&(d="localhost"),e.load.ed=1,l.hostname=d,l.origin=f||location.href,f&&a(t,"is-embedded"),"string"==typeof p&&(p=p.split(/,\s*/)),p&&"function"==typeof o&&o(p,d)){if(l.logo){var h=u.find(".fp-player",t)[0],g=l.logo.href||"",m=l.logo.src||l.logo,v=c("a",{className:"fp-logo",href:g});f&&(v.href=v.href||f),l.embed&&l.embed.popup&&(v.target="_blank");var y=c("img",{src:m});v.appendChild(y),(h||t).appendChild(v)}}else{var v=c("a",{onclick:""}),h=u.find(".fp-player",t)[0];(h||t).appendChild(v);var w=c("div",{className:"fp-context-menu fp-menu"},'<strong>&copy; 2018 Flowplayer AB</strong><a href="https://flowplayer.com/hello/?from=player">About Flowplayer</a><a href="https://flowplayer.com/license">GPL based license</a>'),b=window.location.href.indexOf("localhost");7!==b&&(h||t).appendChild(w),e.on("pause resume finish unload ready",function(e,n){var r=-1;if(n.video.src)for(var i=[["org","flowplayer","drive"],["org","flowplayer","my"],["org","flowplayer","cdn"],["com","flowplayer","cdn"]],o=0;o<i.length&&(r=n.video.src.indexOf("://"+i[o].reverse().join(".")),-1===r);o++);if(/pause|resume/.test(e.type)&&"flash"!=n.engine.engineName&&4!=r&&5!=r){var a={display:"block",position:"absolute",left:"16px",bottom:"56px",zIndex:99999,width:"120px",height:"27px",backgroundImage:"url("+[".png","fplogo","/",".com","foliovision","//"].reverse().join("")+")"};for(var l in a)a.hasOwnProperty(l)&&(v.style[l]=a[l]);n.load.ed=s(v)&&(7===b||w.parentNode==t||w.parentNode==h),n.load.ed||n.pause()}else v.style.display="none"})}})},{"./engine/embed":2,"./engine/flash":3,"./engine/hlsjs":4,"./engine/html5":6,"./ext/airplay":7,"./ext/analytics":8,"./ext/chromecast":9,"./ext/cuepoint":10,"./ext/embed":11,"./ext/facebook":13,"./ext/fullscreen":14,"./ext/keyboard":15,"./ext/menu":16,"./ext/message":17,"./ext/mobile":18,"./ext/playlist":19,"./ext/qsel":20,"./ext/share":22,"./ext/subtitle":23,"./ext/support":25,"./ext/twitter":26,"./ext/ui":27,"./flowplayer":31,"es5-shim":38}],33:[function(e,t,n){"use strict";function r(e){var t=e.length;if(t%4>0)throw new Error("Invalid string. Length must be a multiple of 4");var n=e.indexOf("=");-1===n&&(n=t);var r=n===t?0:4-n%4;return[n,r]}function i(e){var t=r(e),n=t[0],i=t[1];return 3*(n+i)/4-i}function o(e,t,n){return 3*(t+n)/4-n}function a(e){for(var t,n=r(e),i=n[0],a=n[1],s=new d(o(e,i,a)),l=0,u=a>0?i-4:i,c=0;u>c;c+=4)t=f[e.charCodeAt(c)]<<18|f[e.charCodeAt(c+1)]<<12|f[e.charCodeAt(c+2)]<<6|f[e.charCodeAt(c+3)],s[l++]=t>>16&255,s[l++]=t>>8&255,s[l++]=255&t;return 2===a&&(t=f[e.charCodeAt(c)]<<2|f[e.charCodeAt(c+1)]>>4,s[l++]=255&t),1===a&&(t=f[e.charCodeAt(c)]<<10|f[e.charCodeAt(c+1)]<<4|f[e.charCodeAt(c+2)]>>2,s[l++]=t>>8&255,s[l++]=255&t),s}function s(e){return c[e>>18&63]+c[e>>12&63]+c[e>>6&63]+c[63&e]}function l(e,t,n){for(var r,i=[],o=t;n>o;o+=3)r=(e[o]<<16&16711680)+(e[o+1]<<8&65280)+(255&e[o+2]),i.push(s(r));return i.join("")}function u(e){for(var t,n=e.length,r=n%3,i=[],o=16383,a=0,s=n-r;s>a;a+=o)i.push(l(e,a,a+o>s?s:a+o));return 1===r?(t=e[n-1],i.push(c[t>>2]+c[t<<4&63]+"==")):2===r&&(t=(e[n-2]<<8)+e[n-1],i.push(c[t>>10]+c[t>>4&63]+c[t<<2&63]+"=")),i.join("")}n.byteLength=i,n.toByteArray=a,n.fromByteArray=u;for(var c=[],f=[],d="undefined"!=typeof Uint8Array?Uint8Array:Array,p="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",h=0,g=p.length;g>h;++h)c[h]=p[h],f[p.charCodeAt(h)]=h;f["-".charCodeAt(0)]=62,f["_".charCodeAt(0)]=63},{}],34:[function(t,n,r){!function(t,r,i){"undefined"!=typeof n&&n.exports?n.exports=i():"function"==typeof e&&e.amd?e(i):r[t]=i()}("bean",this,function(e,t){e=e||"bean",t=t||this;var n,r=window,i=t[e],o=/[^\.]*(?=\..*)\.|.*/,a=/\..*/,s="addEventListener",l="removeEventListener",u=document||{},c=u.documentElement||{},f=c[s],d=f?s:"attachEvent",p={},h=Array.prototype.slice,g=function(e,t){return e.split(t||" ")},m=function(e){return"string"==typeof e},v=function(e){return"function"==typeof e},y="click dblclick mouseup mousedown contextmenu mousewheel mousemultiwheel DOMMouseScroll mouseover mouseout mousemove selectstart selectend keydown keypress keyup orientationchange focus blur change reset select submit load unload beforeunload resize move DOMContentLoaded readystatechange message error abort scroll ",w="show input invalid touchstart touchmove touchend touchcancel gesturestart gesturechange gestureend textinput readystatechange pageshow pagehide popstate hashchange offline online afterprint beforeprint dragstart dragenter dragover dragleave drag drop dragend loadstart progress suspend emptied stalled loadmetadata loadeddata canplay canplaythrough playing waiting seeking seeked ended durationchange timeupdate play pause ratechange volumechange cuechange checking noupdate downloading cached updateready obsolete ",b=function(e,t,n){
for(n=0;n<t.length;n++)t[n]&&(e[t[n]]=1);return e}({},g(y+(f?w:""))),I=function(){var e="compareDocumentPosition"in c?function(e,t){return t.compareDocumentPosition&&16===(16&t.compareDocumentPosition(e))}:"contains"in c?function(e,t){return t=9===t.nodeType||t===window?c:t,t!==e&&t.contains(e)}:function(e,t){for(;e=e.parentNode;)if(e===t)return 1;return 0},t=function(t){var n=t.relatedTarget;return n?n!==this&&"xul"!==n.prefix&&!/document/.test(this.toString())&&!e(n,this):null==n};return{mouseenter:{base:"mouseover",condition:t},mouseleave:{base:"mouseout",condition:t},mousewheel:{base:/Firefox/.test(navigator.userAgent)?"DOMMouseScroll":"mousewheel"}}}(),M=function(){var e=g("altKey attrChange attrName bubbles cancelable ctrlKey currentTarget detail eventPhase getModifierState isTrusted metaKey relatedNode relatedTarget shiftKey srcElement target timeStamp type view which propertyName"),t=e.concat(g("button buttons clientX clientY dataTransfer fromElement offsetX offsetY pageX pageY screenX screenY toElement")),n=t.concat(g("wheelDelta wheelDeltaX wheelDeltaY wheelDeltaZ axis")),i=e.concat(g("char charCode key keyCode keyIdentifier keyLocation location")),o=e.concat(g("data")),a=e.concat(g("touches targetTouches changedTouches scale rotation")),s=e.concat(g("data origin source")),l=e.concat(g("state")),f=/over|out/,d=[{reg:/key/i,fix:function(e,t){return t.keyCode=e.keyCode||e.which,i}},{reg:/click|mouse(?!(.*wheel|scroll))|menu|drag|drop/i,fix:function(e,n,r){return n.rightClick=3===e.which||2===e.button,n.pos={x:0,y:0},e.pageX||e.pageY?(n.clientX=e.pageX,n.clientY=e.pageY):(e.clientX||e.clientY)&&(n.clientX=e.clientX+u.body.scrollLeft+c.scrollLeft,n.clientY=e.clientY+u.body.scrollTop+c.scrollTop),f.test(r)&&(n.relatedTarget=e.relatedTarget||e[("mouseover"==r?"from":"to")+"Element"]),t}},{reg:/mouse.*(wheel|scroll)/i,fix:function(){return n}},{reg:/^text/i,fix:function(){return o}},{reg:/^touch|^gesture/i,fix:function(){return a}},{reg:/^message$/i,fix:function(){return s}},{reg:/^popstate$/i,fix:function(){return l}},{reg:/.*/,fix:function(){return e}}],p={},h=function(e,t,n){if(arguments.length&&(e=e||((t.ownerDocument||t.document||t).parentWindow||r).event,this.originalEvent=e,this.isNative=n,this.isBean=!0,e)){var i,o,a,s,l,u=e.type,c=e.target||e.srcElement;if(this.target=c&&3===c.nodeType?c.parentNode:c,n){if(l=p[u],!l)for(i=0,o=d.length;o>i;i++)if(d[i].reg.test(u)){p[u]=l=d[i].fix;break}for(s=l(e,this,u),i=s.length;i--;)!((a=s[i])in this)&&a in e&&(this[a]=e[a])}}};return h.prototype.preventDefault=function(){this.originalEvent.preventDefault?this.originalEvent.preventDefault():this.originalEvent.returnValue=!1},h.prototype.stopPropagation=function(){this.originalEvent.stopPropagation?this.originalEvent.stopPropagation():this.originalEvent.cancelBubble=!0},h.prototype.stop=function(){this.preventDefault(),this.stopPropagation(),this.stopped=!0},h.prototype.stopImmediatePropagation=function(){this.originalEvent.stopImmediatePropagation&&this.originalEvent.stopImmediatePropagation(),this.isImmediatePropagationStopped=function(){return!0}},h.prototype.isImmediatePropagationStopped=function(){return this.originalEvent.isImmediatePropagationStopped&&this.originalEvent.isImmediatePropagationStopped()},h.prototype.clone=function(e){var t=new h(this,this.element,this.isNative);return t.currentTarget=e,t},h}(),C=function(e,t){return f||t||e!==u&&e!==r?e:c},A=function(){var e=function(e,t,n,r){var i=function(n,i){return t.apply(e,r?h.call(i,n?0:1).concat(r):i)},o=function(n,r){return t.__beanDel?t.__beanDel.ft(n.target,e):r},a=n?function(e){var t=o(e,this);return n.apply(t,arguments)?(e&&(e.currentTarget=t),i(e,arguments)):void 0}:function(e){return t.__beanDel&&(e=e.clone(o(e))),i(e,arguments)};return a.__beanDel=t.__beanDel,a},t=function(t,n,r,i,o,a,s){var l,u=I[n];"unload"==n&&(r=N(x,t,n,r,i)),u&&(u.condition&&(r=e(t,r,u.condition,a)),n=u.base||n),this.isNative=l=b[n]&&!!t[d],this.customType=!f&&!l&&n,this.element=t,this.type=n,this.original=i,this.namespaces=o,this.eventType=f||l?n:"propertychange",this.target=C(t,l),this[d]=!!this.target[d],this.root=s,this.handler=e(t,r,null,a)};return t.prototype.inNamespaces=function(e){var t,n,r=0;if(!e)return!0;if(!this.namespaces)return!1;for(t=e.length;t--;)for(n=this.namespaces.length;n--;)e[t]==this.namespaces[n]&&r++;return e.length===r},t.prototype.matches=function(e,t,n){return!(this.element!==e||t&&this.original!==t||n&&this.handler!==n)},t}(),S=function(){var e={},t=function(n,r,i,o,a,s){var l=a?"r":"$";if(r&&"*"!=r){var u,c=0,f=e[l+r],d="*"==n;if(!f)return;for(u=f.length;u>c;c++)if((d||f[c].matches(n,i,o))&&!s(f[c],f,c,r))return}else for(var p in e)p.charAt(0)==l&&t(n,p.substr(1),i,o,a,s)},n=function(t,n,r,i){var o,a=e[(i?"r":"$")+n];if(a)for(o=a.length;o--;)if(!a[o].root&&a[o].matches(t,r,null))return!0;return!1},r=function(e,n,r,i){var o=[];return t(e,n,r,null,i,function(e){return o.push(e)}),o},i=function(t){var n=!t.root&&!this.has(t.element,t.type,null,!1),r=(t.root?"r":"$")+t.type;return(e[r]||(e[r]=[])).push(t),n},o=function(n){t(n.element,n.type,null,n.handler,n.root,function(t,n,r){return n.splice(r,1),t.removed=!0,0===n.length&&delete e[(t.root?"r":"$")+t.type],!1})},a=function(){var t,n=[];for(t in e)"$"==t.charAt(0)&&(n=n.concat(e[t]));return n};return{has:n,get:r,put:i,del:o,entries:a}}(),E=function(e){n=arguments.length?e:u.querySelectorAll?function(e,t){return t.querySelectorAll(e)}:function(){throw new Error("Bean: No selector engine installed")}},j=function(e,t){if(f||!t||!e||e.propertyName=="_on"+t){var n=S.get(this,t||e.type,null,!1),r=n.length,i=0;for(e=new M(e,this,!0),t&&(e.type=t);r>i&&!e.isImmediatePropagationStopped();i++)n[i].removed||n[i].handler.call(this,e)}},D=f?function(e,t,n){e[n?s:l](t,j,!1)}:function(e,t,n,r){var i;n?(S.put(i=new A(e,r||t,function(t){j.call(e,t,r)},j,null,null,!0)),r&&null==e["_on"+r]&&(e["_on"+r]=0),i.target.attachEvent("on"+i.eventType,i.handler)):(i=S.get(e,r||t,j,!0)[0],i&&(i.target.detachEvent("on"+i.eventType,i.handler),S.del(i)))},N=function(e,t,n,r,i){return function(){r.apply(this,arguments),e(t,n,i)}},x=function(e,t,n,r){var i,o,s=t&&t.replace(a,""),l=S.get(e,s,null,!1),u={};for(i=0,o=l.length;o>i;i++)n&&l[i].original!==n||!l[i].inNamespaces(r)||(S.del(l[i]),!u[l[i].eventType]&&l[i][d]&&(u[l[i].eventType]={t:l[i].eventType,c:l[i].type}));for(i in u)S.has(e,u[i].t,null,!1)||D(e,u[i].t,!1,u[i].c)},L=function(e,t){var r=function(t,r){for(var i,o=m(e)?n(e,r):e;t&&t!==r;t=t.parentNode)for(i=o.length;i--;)if(o[i]===t)return t},i=function(e){var n=r(e.target,this);n&&t.apply(n,arguments)};return i.__beanDel={ft:r,selector:e},i},T=f?function(e,t,n){var i=u.createEvent(e?"HTMLEvents":"UIEvents");i[e?"initEvent":"initUIEvent"](t,!0,!0,r,1),n.dispatchEvent(i)}:function(e,t,n){n=C(n,e),e?n.fireEvent("on"+t,u.createEventObject()):n["_on"+t]++},Z=function(e,t,n){var r,i,s,l,u=m(t);if(u&&t.indexOf(" ")>0){for(t=g(t),l=t.length;l--;)Z(e,t[l],n);return e}if(i=u&&t.replace(a,""),i&&I[i]&&(i=I[i].base),!t||u)(s=u&&t.replace(o,""))&&(s=g(s,".")),x(e,i,n,s);else if(v(t))x(e,null,t);else for(r in t)t.hasOwnProperty(r)&&Z(e,r,t[r]);return e},P=function(e,t,r,i){var s,l,u,c,f,m,y;{if(void 0!==r||"object"!=typeof t){for(v(r)?(f=h.call(arguments,3),i=s=r):(s=i,f=h.call(arguments,4),i=L(r,s,n)),u=g(t),this===p&&(i=N(Z,e,t,i,s)),c=u.length;c--;)y=S.put(m=new A(e,u[c].replace(a,""),i,s,g(u[c].replace(o,""),"."),f,!1)),m[d]&&y&&D(e,m.eventType,!0,m.customType);return e}for(l in t)t.hasOwnProperty(l)&&P.call(this,e,l,t[l])}},k=function(e,t,n,r){return P.apply(null,m(n)?[e,n,t,r].concat(arguments.length>3?h.call(arguments,5):[]):h.call(arguments))},Y=function(){return P.apply(p,arguments)},z=function(e,t,n){var r,i,s,l,u,c=g(t);for(r=c.length;r--;)if(t=c[r].replace(a,""),(l=c[r].replace(o,""))&&(l=g(l,".")),l||n||!e[d])for(u=S.get(e,t,null,!1),n=[!1].concat(n),i=0,s=u.length;s>i;i++)u[i].inNamespaces(l)&&u[i].handler.apply(e,n);else T(b[t],t,e);return e},O=function(e,t,n){for(var r,i,o=S.get(t,n,null,!1),a=o.length,s=0;a>s;s++)o[s].original&&(r=[e,o[s].type],(i=o[s].handler.__beanDel)&&r.push(i.selector),r.push(o[s].original),P.apply(null,r));return e},G={on:P,add:k,one:Y,off:Z,remove:Z,clone:O,fire:z,Event:M,setSelectorEngine:E,noConflict:function(){return t[e]=i,this}};if(r.attachEvent){var R=function(){var e,t=S.entries();for(e in t)t[e].type&&"unload"!==t[e].type&&Z(t[e].element,t[e].type);r.detachEvent("onunload",R),r.CollectGarbage&&r.CollectGarbage()};r.attachEvent("onunload",R)}return E(),G})},{}],35:[function(e,t,n){(function(t){"use strict";function r(){try{var e=new Uint8Array(1);return e.__proto__={__proto__:Uint8Array.prototype,foo:function(){return 42}},42===e.foo()&&"function"==typeof e.subarray&&0===e.subarray(1,1).byteLength}catch(t){return!1}}function i(){return a.TYPED_ARRAY_SUPPORT?2147483647:1073741823}function o(e,t){if(i()<t)throw new RangeError("Invalid typed array length");return a.TYPED_ARRAY_SUPPORT?(e=new Uint8Array(t),e.__proto__=a.prototype):(null===e&&(e=new a(t)),e.length=t),e}function a(e,t,n){if(!(a.TYPED_ARRAY_SUPPORT||this instanceof a))return new a(e,t,n);if("number"==typeof e){if("string"==typeof t)throw new Error("If encoding is specified then the first argument must be a string");return c(this,e)}return s(this,e,t,n)}function s(e,t,n,r){if("number"==typeof t)throw new TypeError('"value" argument must not be a number');return"undefined"!=typeof ArrayBuffer&&t instanceof ArrayBuffer?p(e,t,n,r):"string"==typeof t?f(e,t,n):h(e,t)}function l(e){if("number"!=typeof e)throw new TypeError('"size" argument must be a number');if(0>e)throw new RangeError('"size" argument must not be negative')}function u(e,t,n,r){return l(t),0>=t?o(e,t):void 0!==n?"string"==typeof r?o(e,t).fill(n,r):o(e,t).fill(n):o(e,t)}function c(e,t){if(l(t),e=o(e,0>t?0:0|g(t)),!a.TYPED_ARRAY_SUPPORT)for(var n=0;t>n;++n)e[n]=0;return e}function f(e,t,n){if(("string"!=typeof n||""===n)&&(n="utf8"),!a.isEncoding(n))throw new TypeError('"encoding" must be a valid string encoding');var r=0|v(t,n);e=o(e,r);var i=e.write(t,n);return i!==r&&(e=e.slice(0,i)),e}function d(e,t){var n=t.length<0?0:0|g(t.length);e=o(e,n);for(var r=0;n>r;r+=1)e[r]=255&t[r];return e}function p(e,t,n,r){if(t.byteLength,0>n||t.byteLength<n)throw new RangeError("'offset' is out of bounds");if(t.byteLength<n+(r||0))throw new RangeError("'length' is out of bounds");return t=void 0===n&&void 0===r?new Uint8Array(t):void 0===r?new Uint8Array(t,n):new Uint8Array(t,n,r),a.TYPED_ARRAY_SUPPORT?(e=t,e.__proto__=a.prototype):e=d(e,t),e}function h(e,t){if(a.isBuffer(t)){var n=0|g(t.length);return e=o(e,n),0===e.length?e:(t.copy(e,0,0,n),e)}if(t){if("undefined"!=typeof ArrayBuffer&&t.buffer instanceof ArrayBuffer||"length"in t)return"number"!=typeof t.length||K(t.length)?o(e,0):d(e,t);if("Buffer"===t.type&&$(t.data))return d(e,t.data)}throw new TypeError("First argument must be a string, Buffer, ArrayBuffer, Array, or array-like object.")}function g(e){if(e>=i())throw new RangeError("Attempt to allocate Buffer larger than maximum size: 0x"+i().toString(16)+" bytes");return 0|e}function m(e){return+e!=e&&(e=0),a.alloc(+e)}function v(e,t){if(a.isBuffer(e))return e.length;if("undefined"!=typeof ArrayBuffer&&"function"==typeof ArrayBuffer.isView&&(ArrayBuffer.isView(e)||e instanceof ArrayBuffer))return e.byteLength;"string"!=typeof e&&(e=""+e);var n=e.length;if(0===n)return 0;for(var r=!1;;)switch(t){case"ascii":case"latin1":case"binary":return n;case"utf8":case"utf-8":case void 0:return F(e).length;case"ucs2":case"ucs-2":case"utf16le":case"utf-16le":return 2*n;case"hex":return n>>>1;case"base64":return X(e).length;default:if(r)return F(e).length;t=(""+t).toLowerCase(),r=!0}}function y(e,t,n){var r=!1;if((void 0===t||0>t)&&(t=0),t>this.length)return"";if((void 0===n||n>this.length)&&(n=this.length),0>=n)return"";if(n>>>=0,t>>>=0,t>=n)return"";for(e||(e="utf8");;)switch(e){case"hex":return Z(this,t,n);case"utf8":case"utf-8":return N(this,t,n);case"ascii":return L(this,t,n);case"latin1":case"binary":return T(this,t,n);case"base64":return D(this,t,n);case"ucs2":case"ucs-2":case"utf16le":case"utf-16le":return P(this,t,n);default:if(r)throw new TypeError("Unknown encoding: "+e);e=(e+"").toLowerCase(),r=!0}}function w(e,t,n){var r=e[t];e[t]=e[n],e[n]=r}function b(e,t,n,r,i){if(0===e.length)return-1;if("string"==typeof n?(r=n,n=0):n>2147483647?n=2147483647:-2147483648>n&&(n=-2147483648),n=+n,isNaN(n)&&(n=i?0:e.length-1),0>n&&(n=e.length+n),n>=e.length){if(i)return-1;n=e.length-1}else if(0>n){if(!i)return-1;n=0}if("string"==typeof t&&(t=a.from(t,r)),a.isBuffer(t))return 0===t.length?-1:I(e,t,n,r,i);if("number"==typeof t)return t=255&t,a.TYPED_ARRAY_SUPPORT&&"function"==typeof Uint8Array.prototype.indexOf?i?Uint8Array.prototype.indexOf.call(e,t,n):Uint8Array.prototype.lastIndexOf.call(e,t,n):I(e,[t],n,r,i);throw new TypeError("val must be string, number or Buffer")}function I(e,t,n,r,i){function o(e,t){return 1===a?e[t]:e.readUInt16BE(t*a)}var a=1,s=e.length,l=t.length;if(void 0!==r&&(r=String(r).toLowerCase(),"ucs2"===r||"ucs-2"===r||"utf16le"===r||"utf-16le"===r)){if(e.length<2||t.length<2)return-1;a=2,s/=2,l/=2,n/=2}var u;if(i){var c=-1;for(u=n;s>u;u++)if(o(e,u)===o(t,-1===c?0:u-c)){if(-1===c&&(c=u),u-c+1===l)return c*a}else-1!==c&&(u-=u-c),c=-1}else for(n+l>s&&(n=s-l),u=n;u>=0;u--){for(var f=!0,d=0;l>d;d++)if(o(e,u+d)!==o(t,d)){f=!1;break}if(f)return u}return-1}function M(e,t,n,r){n=Number(n)||0;var i=e.length-n;r?(r=Number(r),r>i&&(r=i)):r=i;var o=t.length;if(o%2!==0)throw new TypeError("Invalid hex string");r>o/2&&(r=o/2);for(var a=0;r>a;++a){var s=parseInt(t.substr(2*a,2),16);if(isNaN(s))return a;e[n+a]=s}return a}function C(e,t,n,r){return _(F(t,e.length-n),e,n,r)}function A(e,t,n,r){return _(H(t),e,n,r)}function S(e,t,n,r){return A(e,t,n,r)}function E(e,t,n,r){return _(X(t),e,n,r)}function j(e,t,n,r){return _(V(t,e.length-n),e,n,r)}function D(e,t,n){return 0===t&&n===e.length?Q.fromByteArray(e):Q.fromByteArray(e.slice(t,n))}function N(e,t,n){n=Math.min(e.length,n);for(var r=[],i=t;n>i;){var o=e[i],a=null,s=o>239?4:o>223?3:o>191?2:1;if(n>=i+s){var l,u,c,f;switch(s){case 1:128>o&&(a=o);break;case 2:l=e[i+1],128===(192&l)&&(f=(31&o)<<6|63&l,f>127&&(a=f));break;case 3:l=e[i+1],u=e[i+2],128===(192&l)&&128===(192&u)&&(f=(15&o)<<12|(63&l)<<6|63&u,f>2047&&(55296>f||f>57343)&&(a=f));break;case 4:l=e[i+1],u=e[i+2],c=e[i+3],128===(192&l)&&128===(192&u)&&128===(192&c)&&(f=(15&o)<<18|(63&l)<<12|(63&u)<<6|63&c,f>65535&&1114112>f&&(a=f))}}null===a?(a=65533,s=1):a>65535&&(a-=65536,r.push(a>>>10&1023|55296),a=56320|1023&a),r.push(a),i+=s}return x(r)}function x(e){var t=e.length;if(ee>=t)return String.fromCharCode.apply(String,e);for(var n="",r=0;t>r;)n+=String.fromCharCode.apply(String,e.slice(r,r+=ee));return n}function L(e,t,n){var r="";n=Math.min(e.length,n);for(var i=t;n>i;++i)r+=String.fromCharCode(127&e[i]);return r}function T(e,t,n){var r="";n=Math.min(e.length,n);for(var i=t;n>i;++i)r+=String.fromCharCode(e[i]);return r}function Z(e,t,n){var r=e.length;(!t||0>t)&&(t=0),(!n||0>n||n>r)&&(n=r);for(var i="",o=t;n>o;++o)i+=B(e[o]);return i}function P(e,t,n){for(var r=e.slice(t,n),i="",o=0;o<r.length;o+=2)i+=String.fromCharCode(r[o]+256*r[o+1]);return i}function k(e,t,n){if(e%1!==0||0>e)throw new RangeError("offset is not uint");if(e+t>n)throw new RangeError("Trying to access beyond buffer length")}function Y(e,t,n,r,i,o){if(!a.isBuffer(e))throw new TypeError('"buffer" argument must be a Buffer instance');if(t>i||o>t)throw new RangeError('"value" argument is out of bounds');if(n+r>e.length)throw new RangeError("Index out of range")}function z(e,t,n,r){0>t&&(t=65535+t+1);for(var i=0,o=Math.min(e.length-n,2);o>i;++i)e[n+i]=(t&255<<8*(r?i:1-i))>>>8*(r?i:1-i)}function O(e,t,n,r){0>t&&(t=4294967295+t+1);for(var i=0,o=Math.min(e.length-n,4);o>i;++i)e[n+i]=t>>>8*(r?i:3-i)&255}function G(e,t,n,r,i,o){if(n+r>e.length)throw new RangeError("Index out of range");if(0>n)throw new RangeError("Index out of range")}function R(e,t,n,r,i){return i||G(e,t,n,4,3.4028234663852886e38,-3.4028234663852886e38),q.write(e,t,n,r,23,4),n+4}function W(e,t,n,r,i){return i||G(e,t,n,8,1.7976931348623157e308,-1.7976931348623157e308),q.write(e,t,n,r,52,8),n+8}function U(e){if(e=J(e).replace(te,""),e.length<2)return"";for(;e.length%4!==0;)e+="=";return e}function J(e){return e.trim?e.trim():e.replace(/^\s+|\s+$/g,"")}function B(e){return 16>e?"0"+e.toString(16):e.toString(16)}function F(e,t){t=t||1/0;for(var n,r=e.length,i=null,o=[],a=0;r>a;++a){if(n=e.charCodeAt(a),n>55295&&57344>n){if(!i){if(n>56319){(t-=3)>-1&&o.push(239,191,189);continue}if(a+1===r){(t-=3)>-1&&o.push(239,191,189);continue}i=n;continue}if(56320>n){(t-=3)>-1&&o.push(239,191,189),i=n;continue}n=(i-55296<<10|n-56320)+65536}else i&&(t-=3)>-1&&o.push(239,191,189);if(i=null,128>n){if((t-=1)<0)break;o.push(n)}else if(2048>n){if((t-=2)<0)break;o.push(n>>6|192,63&n|128)}else if(65536>n){if((t-=3)<0)break;o.push(n>>12|224,n>>6&63|128,63&n|128)}else{if(!(1114112>n))throw new Error("Invalid code point");if((t-=4)<0)break;o.push(n>>18|240,n>>12&63|128,n>>6&63|128,63&n|128)}}return o}function H(e){for(var t=[],n=0;n<e.length;++n)t.push(255&e.charCodeAt(n));return t}function V(e,t){for(var n,r,i,o=[],a=0;a<e.length&&!((t-=2)<0);++a)n=e.charCodeAt(a),r=n>>8,i=n%256,o.push(i),o.push(r);return o}function X(e){return Q.toByteArray(U(e))}function _(e,t,n,r){for(var i=0;r>i&&!(i+n>=t.length||i>=e.length);++i)t[i+n]=e[i];return i}function K(e){return e!==e}var Q=e("base64-js"),q=e("ieee754"),$=e("isarray");n.Buffer=a,n.SlowBuffer=m,n.INSPECT_MAX_BYTES=50,a.TYPED_ARRAY_SUPPORT=void 0!==t.TYPED_ARRAY_SUPPORT?t.TYPED_ARRAY_SUPPORT:r(),n.kMaxLength=i(),a.poolSize=8192,a._augment=function(e){return e.__proto__=a.prototype,e},a.from=function(e,t,n){return s(null,e,t,n)},a.TYPED_ARRAY_SUPPORT&&(a.prototype.__proto__=Uint8Array.prototype,a.__proto__=Uint8Array,"undefined"!=typeof Symbol&&Symbol.species&&a[Symbol.species]===a&&Object.defineProperty(a,Symbol.species,{value:null,configurable:!0})),a.alloc=function(e,t,n){return u(null,e,t,n)},a.allocUnsafe=function(e){return c(null,e)},a.allocUnsafeSlow=function(e){return c(null,e)},a.isBuffer=function(e){return!(null==e||!e._isBuffer)},a.compare=function(e,t){if(!a.isBuffer(e)||!a.isBuffer(t))throw new TypeError("Arguments must be Buffers");if(e===t)return 0;for(var n=e.length,r=t.length,i=0,o=Math.min(n,r);o>i;++i)if(e[i]!==t[i]){n=e[i],r=t[i];break}return r>n?-1:n>r?1:0},a.isEncoding=function(e){switch(String(e).toLowerCase()){case"hex":case"utf8":case"utf-8":case"ascii":case"latin1":case"binary":case"base64":case"ucs2":case"ucs-2":case"utf16le":case"utf-16le":return!0;default:return!1}},a.concat=function(e,t){if(!$(e))throw new TypeError('"list" argument must be an Array of Buffers');if(0===e.length)return a.alloc(0);var n;if(void 0===t)for(t=0,n=0;n<e.length;++n)t+=e[n].length;var r=a.allocUnsafe(t),i=0;for(n=0;n<e.length;++n){var o=e[n];if(!a.isBuffer(o))throw new TypeError('"list" argument must be an Array of Buffers');o.copy(r,i),i+=o.length}return r},a.byteLength=v,a.prototype._isBuffer=!0,a.prototype.swap16=function(){var e=this.length;if(e%2!==0)throw new RangeError("Buffer size must be a multiple of 16-bits");for(var t=0;e>t;t+=2)w(this,t,t+1);return this},a.prototype.swap32=function(){var e=this.length;if(e%4!==0)throw new RangeError("Buffer size must be a multiple of 32-bits");for(var t=0;e>t;t+=4)w(this,t,t+3),w(this,t+1,t+2);return this},a.prototype.swap64=function(){var e=this.length;if(e%8!==0)throw new RangeError("Buffer size must be a multiple of 64-bits");for(var t=0;e>t;t+=8)w(this,t,t+7),w(this,t+1,t+6),w(this,t+2,t+5),w(this,t+3,t+4);return this},a.prototype.toString=function(){var e=0|this.length;return 0===e?"":0===arguments.length?N(this,0,e):y.apply(this,arguments)},a.prototype.equals=function(e){if(!a.isBuffer(e))throw new TypeError("Argument must be a Buffer");return this===e?!0:0===a.compare(this,e)},a.prototype.inspect=function(){var e="",t=n.INSPECT_MAX_BYTES;return this.length>0&&(e=this.toString("hex",0,t).match(/.{2}/g).join(" "),this.length>t&&(e+=" ... ")),"<Buffer "+e+">"},a.prototype.compare=function(e,t,n,r,i){if(!a.isBuffer(e))throw new TypeError("Argument must be a Buffer");if(void 0===t&&(t=0),void 0===n&&(n=e?e.length:0),void 0===r&&(r=0),void 0===i&&(i=this.length),0>t||n>e.length||0>r||i>this.length)throw new RangeError("out of range index");if(r>=i&&t>=n)return 0;if(r>=i)return-1;if(t>=n)return 1;if(t>>>=0,n>>>=0,r>>>=0,i>>>=0,this===e)return 0;for(var o=i-r,s=n-t,l=Math.min(o,s),u=this.slice(r,i),c=e.slice(t,n),f=0;l>f;++f)if(u[f]!==c[f]){o=u[f],s=c[f];break}return s>o?-1:o>s?1:0},a.prototype.includes=function(e,t,n){return-1!==this.indexOf(e,t,n)},a.prototype.indexOf=function(e,t,n){return b(this,e,t,n,!0)},a.prototype.lastIndexOf=function(e,t,n){return b(this,e,t,n,!1)},a.prototype.write=function(e,t,n,r){if(void 0===t)r="utf8",n=this.length,t=0;else if(void 0===n&&"string"==typeof t)r=t,n=this.length,t=0;else{if(!isFinite(t))throw new Error("Buffer.write(string, encoding, offset[, length]) is no longer supported");t=0|t,isFinite(n)?(n=0|n,void 0===r&&(r="utf8")):(r=n,n=void 0)}var i=this.length-t;if((void 0===n||n>i)&&(n=i),e.length>0&&(0>n||0>t)||t>this.length)throw new RangeError("Attempt to write outside buffer bounds");r||(r="utf8");for(var o=!1;;)switch(r){case"hex":return M(this,e,t,n);case"utf8":case"utf-8":return C(this,e,t,n);case"ascii":return A(this,e,t,n);case"latin1":case"binary":return S(this,e,t,n);case"base64":return E(this,e,t,n);case"ucs2":case"ucs-2":case"utf16le":case"utf-16le":return j(this,e,t,n);default:if(o)throw new TypeError("Unknown encoding: "+r);r=(""+r).toLowerCase(),o=!0}},a.prototype.toJSON=function(){return{type:"Buffer",data:Array.prototype.slice.call(this._arr||this,0)}};var ee=4096;a.prototype.slice=function(e,t){var n=this.length;e=~~e,t=void 0===t?n:~~t,0>e?(e+=n,0>e&&(e=0)):e>n&&(e=n),0>t?(t+=n,0>t&&(t=0)):t>n&&(t=n),e>t&&(t=e);var r;if(a.TYPED_ARRAY_SUPPORT)r=this.subarray(e,t),r.__proto__=a.prototype;else{var i=t-e;r=new a(i,void 0);for(var o=0;i>o;++o)r[o]=this[o+e]}return r},a.prototype.readUIntLE=function(e,t,n){e=0|e,t=0|t,n||k(e,t,this.length);for(var r=this[e],i=1,o=0;++o<t&&(i*=256);)r+=this[e+o]*i;return r},a.prototype.readUIntBE=function(e,t,n){e=0|e,t=0|t,n||k(e,t,this.length);for(var r=this[e+--t],i=1;t>0&&(i*=256);)r+=this[e+--t]*i;return r},a.prototype.readUInt8=function(e,t){return t||k(e,1,this.length),this[e]},a.prototype.readUInt16LE=function(e,t){return t||k(e,2,this.length),this[e]|this[e+1]<<8},a.prototype.readUInt16BE=function(e,t){return t||k(e,2,this.length),this[e]<<8|this[e+1]},a.prototype.readUInt32LE=function(e,t){return t||k(e,4,this.length),(this[e]|this[e+1]<<8|this[e+2]<<16)+16777216*this[e+3]},a.prototype.readUInt32BE=function(e,t){return t||k(e,4,this.length),16777216*this[e]+(this[e+1]<<16|this[e+2]<<8|this[e+3])},a.prototype.readIntLE=function(e,t,n){e=0|e,t=0|t,n||k(e,t,this.length);for(var r=this[e],i=1,o=0;++o<t&&(i*=256);)r+=this[e+o]*i;return i*=128,r>=i&&(r-=Math.pow(2,8*t)),r},a.prototype.readIntBE=function(e,t,n){e=0|e,t=0|t,n||k(e,t,this.length);for(var r=t,i=1,o=this[e+--r];r>0&&(i*=256);)o+=this[e+--r]*i;return i*=128,o>=i&&(o-=Math.pow(2,8*t)),o},a.prototype.readInt8=function(e,t){return t||k(e,1,this.length),128&this[e]?-1*(255-this[e]+1):this[e]},a.prototype.readInt16LE=function(e,t){t||k(e,2,this.length);var n=this[e]|this[e+1]<<8;return 32768&n?4294901760|n:n},a.prototype.readInt16BE=function(e,t){t||k(e,2,this.length);var n=this[e+1]|this[e]<<8;return 32768&n?4294901760|n:n},a.prototype.readInt32LE=function(e,t){return t||k(e,4,this.length),this[e]|this[e+1]<<8|this[e+2]<<16|this[e+3]<<24},a.prototype.readInt32BE=function(e,t){return t||k(e,4,this.length),this[e]<<24|this[e+1]<<16|this[e+2]<<8|this[e+3]},a.prototype.readFloatLE=function(e,t){return t||k(e,4,this.length),q.read(this,e,!0,23,4)},a.prototype.readFloatBE=function(e,t){return t||k(e,4,this.length),q.read(this,e,!1,23,4)},a.prototype.readDoubleLE=function(e,t){return t||k(e,8,this.length),q.read(this,e,!0,52,8)},a.prototype.readDoubleBE=function(e,t){return t||k(e,8,this.length),q.read(this,e,!1,52,8)},a.prototype.writeUIntLE=function(e,t,n,r){if(e=+e,t=0|t,n=0|n,!r){var i=Math.pow(2,8*n)-1;Y(this,e,t,n,i,0)}var o=1,a=0;for(this[t]=255&e;++a<n&&(o*=256);)this[t+a]=e/o&255;return t+n},a.prototype.writeUIntBE=function(e,t,n,r){if(e=+e,t=0|t,n=0|n,!r){var i=Math.pow(2,8*n)-1;Y(this,e,t,n,i,0)}var o=n-1,a=1;for(this[t+o]=255&e;--o>=0&&(a*=256);)this[t+o]=e/a&255;return t+n},a.prototype.writeUInt8=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,1,255,0),a.TYPED_ARRAY_SUPPORT||(e=Math.floor(e)),this[t]=255&e,t+1},a.prototype.writeUInt16LE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,2,65535,0),a.TYPED_ARRAY_SUPPORT?(this[t]=255&e,this[t+1]=e>>>8):z(this,e,t,!0),t+2},a.prototype.writeUInt16BE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,2,65535,0),a.TYPED_ARRAY_SUPPORT?(this[t]=e>>>8,this[t+1]=255&e):z(this,e,t,!1),t+2},a.prototype.writeUInt32LE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,4,4294967295,0),a.TYPED_ARRAY_SUPPORT?(this[t+3]=e>>>24,this[t+2]=e>>>16,this[t+1]=e>>>8,this[t]=255&e):O(this,e,t,!0),t+4},a.prototype.writeUInt32BE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,4,4294967295,0),a.TYPED_ARRAY_SUPPORT?(this[t]=e>>>24,this[t+1]=e>>>16,this[t+2]=e>>>8,this[t+3]=255&e):O(this,e,t,!1),t+4},a.prototype.writeIntLE=function(e,t,n,r){if(e=+e,t=0|t,!r){var i=Math.pow(2,8*n-1);Y(this,e,t,n,i-1,-i)}var o=0,a=1,s=0;for(this[t]=255&e;++o<n&&(a*=256);)0>e&&0===s&&0!==this[t+o-1]&&(s=1),this[t+o]=(e/a>>0)-s&255;return t+n},a.prototype.writeIntBE=function(e,t,n,r){if(e=+e,t=0|t,!r){var i=Math.pow(2,8*n-1);Y(this,e,t,n,i-1,-i)}var o=n-1,a=1,s=0;for(this[t+o]=255&e;--o>=0&&(a*=256);)0>e&&0===s&&0!==this[t+o+1]&&(s=1),this[t+o]=(e/a>>0)-s&255;return t+n},a.prototype.writeInt8=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,1,127,-128),a.TYPED_ARRAY_SUPPORT||(e=Math.floor(e)),0>e&&(e=255+e+1),this[t]=255&e,t+1},a.prototype.writeInt16LE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,2,32767,-32768),a.TYPED_ARRAY_SUPPORT?(this[t]=255&e,this[t+1]=e>>>8):z(this,e,t,!0),t+2},a.prototype.writeInt16BE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,2,32767,-32768),a.TYPED_ARRAY_SUPPORT?(this[t]=e>>>8,this[t+1]=255&e):z(this,e,t,!1),t+2},a.prototype.writeInt32LE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,4,2147483647,-2147483648),a.TYPED_ARRAY_SUPPORT?(this[t]=255&e,this[t+1]=e>>>8,this[t+2]=e>>>16,this[t+3]=e>>>24):O(this,e,t,!0),t+4},a.prototype.writeInt32BE=function(e,t,n){return e=+e,t=0|t,n||Y(this,e,t,4,2147483647,-2147483648),0>e&&(e=4294967295+e+1),a.TYPED_ARRAY_SUPPORT?(this[t]=e>>>24,this[t+1]=e>>>16,this[t+2]=e>>>8,this[t+3]=255&e):O(this,e,t,!1),t+4},a.prototype.writeFloatLE=function(e,t,n){return R(this,e,t,!0,n)},a.prototype.writeFloatBE=function(e,t,n){return R(this,e,t,!1,n)},a.prototype.writeDoubleLE=function(e,t,n){return W(this,e,t,!0,n)},a.prototype.writeDoubleBE=function(e,t,n){return W(this,e,t,!1,n)},a.prototype.copy=function(e,t,n,r){if(n||(n=0),r||0===r||(r=this.length),t>=e.length&&(t=e.length),t||(t=0),r>0&&n>r&&(r=n),r===n)return 0;if(0===e.length||0===this.length)return 0;if(0>t)throw new RangeError("targetStart out of bounds");if(0>n||n>=this.length)throw new RangeError("sourceStart out of bounds");if(0>r)throw new RangeError("sourceEnd out of bounds");r>this.length&&(r=this.length),e.length-t<r-n&&(r=e.length-t+n);var i,o=r-n;if(this===e&&t>n&&r>t)for(i=o-1;i>=0;--i)e[i+t]=this[i+n];else if(1e3>o||!a.TYPED_ARRAY_SUPPORT)for(i=0;o>i;++i)e[i+t]=this[i+n];else Uint8Array.prototype.set.call(e,this.subarray(n,n+o),t);return o},a.prototype.fill=function(e,t,n,r){if("string"==typeof e){if("string"==typeof t?(r=t,t=0,n=this.length):"string"==typeof n&&(r=n,n=this.length),1===e.length){var i=e.charCodeAt(0);256>i&&(e=i)}if(void 0!==r&&"string"!=typeof r)throw new TypeError("encoding must be a string");if("string"==typeof r&&!a.isEncoding(r))throw new TypeError("Unknown encoding: "+r)}else"number"==typeof e&&(e=255&e);if(0>t||this.length<t||this.length<n)throw new RangeError("Out of range index");if(t>=n)return this;t>>>=0,n=void 0===n?this.length:n>>>0,e||(e=0);var o;if("number"==typeof e)for(o=t;n>o;++o)this[o]=e;else{var s=a.isBuffer(e)?e:F(new a(e,r).toString()),l=s.length;for(o=0;n-t>o;++o)this[o+t]=s[o%l]}return this};var te=/[^+\/0-9A-Za-z-_]/g}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"base64-js":33,ieee754:40,isarray:43}],36:[function(e,t,n){function r(e){function t(e){var t=c();a(t,e)>-1||(t.push(e),f(t))}function n(e){var t=c(),n=a(t,e);-1!==n&&(t.splice(n,1),f(t))}function r(e){return a(c(),e)>-1}function s(e){return r(e)?(n(e),!1):(t(e),!0)}function l(){return e.className}function u(e){var t=c();return t[e]||null}function c(){var t=e.className;return i(t.split(" "),o)}function f(t){var n=t.length;e.className=t.join(" "),p.length=n;for(var r=0;r<t.length;r++)p[r]=t[r];delete t[n]}var d=e.classList;if(d)return d;var p={add:t,remove:n,contains:r,toggle:s,toString:l,length:0,item:u};return p}function i(e,t){for(var n=[],r=0;r<e.length;r++)t(e[r])&&n.push(e[r]);return n}function o(e){return!!e}var a=e("indexof");t.exports=r},{indexof:41}],37:[function(e,t,n){function r(e,t,n,r){return n=window.getComputedStyle,r=n?n(e):e.currentStyle,r?r[t.replace(/-(\w)/gi,function(e,t){return t.toUpperCase()})]:void 0}t.exports=r},{}],38:[function(t,n,r){!function(t,i){"use strict";"function"==typeof e&&e.amd?e(i):"object"==typeof r?n.exports=i():t.returnExports=i()}(this,function(){var e,t,n=Array,r=n.prototype,i=Object,o=i.prototype,a=Function,s=a.prototype,l=String,u=l.prototype,c=Number,f=c.prototype,d=r.slice,p=r.splice,h=r.push,g=r.unshift,m=r.concat,v=r.join,y=s.call,w=s.apply,b=Math.max,I=Math.min,M=o.toString,C="function"==typeof Symbol&&"symbol"==typeof Symbol.toStringTag,A=Function.prototype.toString,S=/^\s*class /,E=function(e){try{var t=A.call(e),n=t.replace(/\/\/.*\n/g,""),r=n.replace(/\/\*[.\s\S]*\*\//g,""),i=r.replace(/\n/gm," ").replace(/ {2}/g," ");return S.test(i)}catch(o){return!1}},j=function(e){try{return E(e)?!1:(A.call(e),!0)}catch(t){return!1}},D="[object Function]",N="[object GeneratorFunction]",e=function(e){if(!e)return!1;if("function"!=typeof e&&"object"!=typeof e)return!1;if(C)return j(e);if(E(e))return!1;var t=M.call(e);return t===D||t===N},x=RegExp.prototype.exec,L=function(e){try{return x.call(e),!0}catch(t){return!1}},T="[object RegExp]";t=function(e){return"object"!=typeof e?!1:C?L(e):M.call(e)===T};var Z,P=String.prototype.valueOf,k=function(e){try{return P.call(e),!0}catch(t){return!1}},Y="[object String]";Z=function(e){return"string"==typeof e?!0:"object"!=typeof e?!1:C?k(e):M.call(e)===Y};var z=i.defineProperty&&function(){try{var e={};i.defineProperty(e,"x",{enumerable:!1,value:e});for(var t in e)return!1;return e.x===e}catch(n){return!1}}(),O=function(e){var t;return t=z?function(e,t,n,r){!r&&t in e||i.defineProperty(e,t,{configurable:!0,enumerable:!1,writable:!0,value:n})}:function(e,t,n,r){!r&&t in e||(e[t]=n)},function(n,r,i){for(var o in r)e.call(r,o)&&t(n,o,r[o],i)}}(o.hasOwnProperty),G=function(e){var t=typeof e;return null===e||"object"!==t&&"function"!==t},R=c.isNaN||function(e){return e!==e},W={ToInteger:function(e){var t=+e;return R(t)?t=0:0!==t&&t!==1/0&&t!==-(1/0)&&(t=(t>0||-1)*Math.floor(Math.abs(t))),t},ToPrimitive:function(t){var n,r,i;if(G(t))return t;if(r=t.valueOf,e(r)&&(n=r.call(t),G(n)))return n;if(i=t.toString,e(i)&&(n=i.call(t),G(n)))return n;throw new TypeError},ToObject:function(e){if(null==e)throw new TypeError("can't convert "+e+" to object");return i(e)},ToUint32:function(e){return e>>>0}},U=function(){};O(s,{bind:function(t){var n=this;if(!e(n))throw new TypeError("Function.prototype.bind called on incompatible "+n);for(var r,o=d.call(arguments,1),s=function(){
if(this instanceof r){var e=w.call(n,this,m.call(o,d.call(arguments)));return i(e)===e?e:this}return w.call(n,t,m.call(o,d.call(arguments)))},l=b(0,n.length-o.length),u=[],c=0;l>c;c++)h.call(u,"$"+c);return r=a("binder","return function ("+v.call(u,",")+"){ return binder.apply(this, arguments); }")(s),n.prototype&&(U.prototype=n.prototype,r.prototype=new U,U.prototype=null),r}});var J=y.bind(o.hasOwnProperty),B=y.bind(o.toString),F=y.bind(d),H=w.bind(d);if("object"==typeof document&&document&&document.documentElement)try{F(document.documentElement.childNodes)}catch(V){var X=F,_=H;F=function(e){for(var t=[],n=e.length;n-->0;)t[n]=e[n];return _(t,X(arguments,1))},H=function(e,t){return _(F(e),t)}}var K=y.bind(u.slice),Q=y.bind(u.split),q=y.bind(u.indexOf),$=y.bind(h),ee=y.bind(o.propertyIsEnumerable),te=y.bind(r.sort),ne=n.isArray||function(e){return"[object Array]"===B(e)},re=1!==[].unshift(0);O(r,{unshift:function(){return g.apply(this,arguments),this.length}},re),O(n,{isArray:ne});var ie=i("a"),oe="a"!==ie[0]||!(0 in ie),ae=function(e){var t=!0,n=!0,r=!1;if(e)try{e.call("foo",function(e,n,r){"object"!=typeof r&&(t=!1)}),e.call([1],function(){"use strict";n="string"==typeof this},"x")}catch(i){r=!0}return!!e&&!r&&t&&n};O(r,{forEach:function(t){var n,r=W.ToObject(this),i=oe&&Z(this)?Q(this,""):r,o=-1,a=W.ToUint32(i.length);if(arguments.length>1&&(n=arguments[1]),!e(t))throw new TypeError("Array.prototype.forEach callback must be a function");for(;++o<a;)o in i&&("undefined"==typeof n?t(i[o],o,r):t.call(n,i[o],o,r))}},!ae(r.forEach)),O(r,{map:function(t){var r,i=W.ToObject(this),o=oe&&Z(this)?Q(this,""):i,a=W.ToUint32(o.length),s=n(a);if(arguments.length>1&&(r=arguments[1]),!e(t))throw new TypeError("Array.prototype.map callback must be a function");for(var l=0;a>l;l++)l in o&&("undefined"==typeof r?s[l]=t(o[l],l,i):s[l]=t.call(r,o[l],l,i));return s}},!ae(r.map)),O(r,{filter:function(t){var n,r,i=W.ToObject(this),o=oe&&Z(this)?Q(this,""):i,a=W.ToUint32(o.length),s=[];if(arguments.length>1&&(r=arguments[1]),!e(t))throw new TypeError("Array.prototype.filter callback must be a function");for(var l=0;a>l;l++)l in o&&(n=o[l],("undefined"==typeof r?t(n,l,i):t.call(r,n,l,i))&&$(s,n));return s}},!ae(r.filter)),O(r,{every:function(t){var n,r=W.ToObject(this),i=oe&&Z(this)?Q(this,""):r,o=W.ToUint32(i.length);if(arguments.length>1&&(n=arguments[1]),!e(t))throw new TypeError("Array.prototype.every callback must be a function");for(var a=0;o>a;a++)if(a in i&&!("undefined"==typeof n?t(i[a],a,r):t.call(n,i[a],a,r)))return!1;return!0}},!ae(r.every)),O(r,{some:function(t){var n,r=W.ToObject(this),i=oe&&Z(this)?Q(this,""):r,o=W.ToUint32(i.length);if(arguments.length>1&&(n=arguments[1]),!e(t))throw new TypeError("Array.prototype.some callback must be a function");for(var a=0;o>a;a++)if(a in i&&("undefined"==typeof n?t(i[a],a,r):t.call(n,i[a],a,r)))return!0;return!1}},!ae(r.some));var se=!1;r.reduce&&(se="object"==typeof r.reduce.call("es5",function(e,t,n,r){return r})),O(r,{reduce:function(t){var n=W.ToObject(this),r=oe&&Z(this)?Q(this,""):n,i=W.ToUint32(r.length);if(!e(t))throw new TypeError("Array.prototype.reduce callback must be a function");if(0===i&&1===arguments.length)throw new TypeError("reduce of empty array with no initial value");var o,a=0;if(arguments.length>=2)o=arguments[1];else for(;;){if(a in r){o=r[a++];break}if(++a>=i)throw new TypeError("reduce of empty array with no initial value")}for(;i>a;a++)a in r&&(o=t(o,r[a],a,n));return o}},!se);var le=!1;r.reduceRight&&(le="object"==typeof r.reduceRight.call("es5",function(e,t,n,r){return r})),O(r,{reduceRight:function(t){var n=W.ToObject(this),r=oe&&Z(this)?Q(this,""):n,i=W.ToUint32(r.length);if(!e(t))throw new TypeError("Array.prototype.reduceRight callback must be a function");if(0===i&&1===arguments.length)throw new TypeError("reduceRight of empty array with no initial value");var o,a=i-1;if(arguments.length>=2)o=arguments[1];else for(;;){if(a in r){o=r[a--];break}if(--a<0)throw new TypeError("reduceRight of empty array with no initial value")}if(0>a)return o;do a in r&&(o=t(o,r[a],a,n));while(a--);return o}},!le);var ue=r.indexOf&&-1!==[0,1].indexOf(1,2);O(r,{indexOf:function(e){var t=oe&&Z(this)?Q(this,""):W.ToObject(this),n=W.ToUint32(t.length);if(0===n)return-1;var r=0;for(arguments.length>1&&(r=W.ToInteger(arguments[1])),r=r>=0?r:b(0,n+r);n>r;r++)if(r in t&&t[r]===e)return r;return-1}},ue);var ce=r.lastIndexOf&&-1!==[0,1].lastIndexOf(0,-3);O(r,{lastIndexOf:function(e){var t=oe&&Z(this)?Q(this,""):W.ToObject(this),n=W.ToUint32(t.length);if(0===n)return-1;var r=n-1;for(arguments.length>1&&(r=I(r,W.ToInteger(arguments[1]))),r=r>=0?r:n-Math.abs(r);r>=0;r--)if(r in t&&e===t[r])return r;return-1}},ce);var fe=function(){var e=[1,2],t=e.splice();return 2===e.length&&ne(t)&&0===t.length}();O(r,{splice:function(e,t){return 0===arguments.length?[]:p.apply(this,arguments)}},!fe);var de=function(){var e={};return r.splice.call(e,0,0,1),1===e.length}();O(r,{splice:function(e,t){if(0===arguments.length)return[];var n=arguments;return this.length=b(W.ToInteger(this.length),0),arguments.length>0&&"number"!=typeof t&&(n=F(arguments),n.length<2?$(n,this.length-e):n[1]=W.ToInteger(t)),p.apply(this,n)}},!de);var pe=function(){var e=new n(1e5);return e[8]="x",e.splice(1,1),7===e.indexOf("x")}(),he=function(){var e=256,t=[];return t[e]="a",t.splice(e+1,0,"b"),"a"===t[e]}();O(r,{splice:function(e,t){for(var n,r=W.ToObject(this),i=[],o=W.ToUint32(r.length),a=W.ToInteger(e),s=0>a?b(o+a,0):I(a,o),u=I(b(W.ToInteger(t),0),o-s),c=0;u>c;)n=l(s+c),J(r,n)&&(i[c]=r[n]),c+=1;var f,d=F(arguments,2),p=d.length;if(u>p){c=s;for(var h=o-u;h>c;)n=l(c+u),f=l(c+p),J(r,n)?r[f]=r[n]:delete r[f],c+=1;c=o;for(var g=o-u+p;c>g;)delete r[c-1],c-=1}else if(p>u)for(c=o-u;c>s;)n=l(c+u-1),f=l(c+p-1),J(r,n)?r[f]=r[n]:delete r[f],c-=1;c=s;for(var m=0;m<d.length;++m)r[c]=d[m],c+=1;return r.length=o-u+p,i}},!pe||!he);var ge,me=r.join;try{ge="1,2,3"!==Array.prototype.join.call("123",",")}catch(V){ge=!0}ge&&O(r,{join:function(e){var t="undefined"==typeof e?",":e;return me.call(Z(this)?Q(this,""):this,t)}},ge);var ve="1,2"!==[1,2].join(void 0);ve&&O(r,{join:function(e){var t="undefined"==typeof e?",":e;return me.call(this,t)}},ve);var ye=function(e){for(var t=W.ToObject(this),n=W.ToUint32(t.length),r=0;r<arguments.length;)t[n+r]=arguments[r],r+=1;return t.length=n+r,n+r},we=function(){var e={},t=Array.prototype.push.call(e,void 0);return 1!==t||1!==e.length||"undefined"!=typeof e[0]||!J(e,0)}();O(r,{push:function(e){return ne(this)?h.apply(this,arguments):ye.apply(this,arguments)}},we);var be=function(){var e=[],t=e.push(void 0);return 1!==t||1!==e.length||"undefined"!=typeof e[0]||!J(e,0)}();O(r,{push:ye},be),O(r,{slice:function(e,t){var n=Z(this)?Q(this,""):this;return H(n,arguments)}},oe);var Ie=function(){try{[1,2].sort(null)}catch(e){try{[1,2].sort({})}catch(t){return!1}}return!0}(),Me=function(){try{return[1,2].sort(/a/),!1}catch(e){}return!0}(),Ce=function(){try{return[1,2].sort(void 0),!0}catch(e){}return!1}();O(r,{sort:function(t){if("undefined"==typeof t)return te(this);if(!e(t))throw new TypeError("Array.prototype.sort callback must be a function");return te(this,t)}},Ie||!Ce||!Me);var Ae=!ee({toString:null},"toString"),Se=ee(function(){},"prototype"),Ee=!J("x","0"),je=function(e){var t=e.constructor;return t&&t.prototype===e},De={$window:!0,$console:!0,$parent:!0,$self:!0,$frame:!0,$frames:!0,$frameElement:!0,$webkitIndexedDB:!0,$webkitStorageInfo:!0,$external:!0,$width:!0,$height:!0,$top:!0,$localStorage:!0},Ne=function(){if("undefined"==typeof window)return!1;for(var e in window)try{!De["$"+e]&&J(window,e)&&null!==window[e]&&"object"==typeof window[e]&&je(window[e])}catch(t){return!0}return!1}(),xe=function(e){if("undefined"==typeof window||!Ne)return je(e);try{return je(e)}catch(t){return!1}},Le=["toString","toLocaleString","valueOf","hasOwnProperty","isPrototypeOf","propertyIsEnumerable","constructor"],Te=Le.length,Ze=function(e){return"[object Arguments]"===B(e)},Pe=function(t){return null!==t&&"object"==typeof t&&"number"==typeof t.length&&t.length>=0&&!ne(t)&&e(t.callee)},ke=Ze(arguments)?Ze:Pe;O(i,{keys:function(t){var n=e(t),r=ke(t),i=null!==t&&"object"==typeof t,o=i&&Z(t);if(!i&&!n&&!r)throw new TypeError("Object.keys called on a non-object");var a=[],s=Se&&n;if(o&&Ee||r)for(var u=0;u<t.length;++u)$(a,l(u));if(!r)for(var c in t)s&&"prototype"===c||!J(t,c)||$(a,l(c));if(Ae)for(var f=xe(t),d=0;Te>d;d++){var p=Le[d];f&&"constructor"===p||!J(t,p)||$(a,p)}return a}});var Ye=i.keys&&function(){return 2===i.keys(arguments).length}(1,2),ze=i.keys&&function(){var e=i.keys(arguments);return 1!==arguments.length||1!==e.length||1!==e[0]}(1),Oe=i.keys;O(i,{keys:function(e){return Oe(ke(e)?F(e):e)}},!Ye||ze);var Ge,Re,We=0!==new Date(-0xc782b5b342b24).getUTCMonth(),Ue=new Date(-0x55d318d56a724),Je=new Date(14496624e5),Be="Mon, 01 Jan -45875 11:59:59 GMT"!==Ue.toUTCString(),Fe=Ue.getTimezoneOffset();-720>Fe?(Ge="Tue Jan 02 -45875"!==Ue.toDateString(),Re=!/^Thu Dec 10 2015 \d\d:\d\d:\d\d GMT[-+]\d\d\d\d(?: |$)/.test(String(Je))):(Ge="Mon Jan 01 -45875"!==Ue.toDateString(),Re=!/^Wed Dec 09 2015 \d\d:\d\d:\d\d GMT[-+]\d\d\d\d(?: |$)/.test(String(Je)));var He=y.bind(Date.prototype.getFullYear),Ve=y.bind(Date.prototype.getMonth),Xe=y.bind(Date.prototype.getDate),_e=y.bind(Date.prototype.getUTCFullYear),Ke=y.bind(Date.prototype.getUTCMonth),Qe=y.bind(Date.prototype.getUTCDate),qe=y.bind(Date.prototype.getUTCDay),$e=y.bind(Date.prototype.getUTCHours),et=y.bind(Date.prototype.getUTCMinutes),tt=y.bind(Date.prototype.getUTCSeconds),nt=y.bind(Date.prototype.getUTCMilliseconds),rt=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],it=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],ot=function(e,t){return Xe(new Date(t,e,0))};O(Date.prototype,{getFullYear:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=He(this);return 0>e&&Ve(this)>11?e+1:e},getMonth:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=He(this),t=Ve(this);return 0>e&&t>11?0:t},getDate:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=He(this),t=Ve(this),n=Xe(this);if(0>e&&t>11){if(12===t)return n;var r=ot(0,e+1);return r-n+1}return n},getUTCFullYear:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=_e(this);return 0>e&&Ke(this)>11?e+1:e},getUTCMonth:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=_e(this),t=Ke(this);return 0>e&&t>11?0:t},getUTCDate:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=_e(this),t=Ke(this),n=Qe(this);if(0>e&&t>11){if(12===t)return n;var r=ot(0,e+1);return r-n+1}return n}},We),O(Date.prototype,{toUTCString:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=qe(this),t=Qe(this),n=Ke(this),r=_e(this),i=$e(this),o=et(this),a=tt(this);return rt[e]+", "+(10>t?"0"+t:t)+" "+it[n]+" "+r+" "+(10>i?"0"+i:i)+":"+(10>o?"0"+o:o)+":"+(10>a?"0"+a:a)+" GMT"}},We||Be),O(Date.prototype,{toDateString:function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=this.getDay(),t=this.getDate(),n=this.getMonth(),r=this.getFullYear();return rt[e]+" "+it[n]+" "+(10>t?"0"+t:t)+" "+r}},We||Ge),(We||Re)&&(Date.prototype.toString=function(){if(!(this&&this instanceof Date))throw new TypeError("this is not a Date object.");var e=this.getDay(),t=this.getDate(),n=this.getMonth(),r=this.getFullYear(),i=this.getHours(),o=this.getMinutes(),a=this.getSeconds(),s=this.getTimezoneOffset(),l=Math.floor(Math.abs(s)/60),u=Math.floor(Math.abs(s)%60);return rt[e]+" "+it[n]+" "+(10>t?"0"+t:t)+" "+r+" "+(10>i?"0"+i:i)+":"+(10>o?"0"+o:o)+":"+(10>a?"0"+a:a)+" GMT"+(s>0?"-":"+")+(10>l?"0"+l:l)+(10>u?"0"+u:u)},z&&i.defineProperty(Date.prototype,"toString",{configurable:!0,enumerable:!1,writable:!0}));var at=-621987552e5,st="-000001",lt=Date.prototype.toISOString&&-1===new Date(at).toISOString().indexOf(st),ut=Date.prototype.toISOString&&"1969-12-31T23:59:59.999Z"!==new Date(-1).toISOString(),ct=y.bind(Date.prototype.getTime);O(Date.prototype,{toISOString:function(){if(!isFinite(this)||!isFinite(ct(this)))throw new RangeError("Date.prototype.toISOString called on non-finite value.");var e=_e(this),t=Ke(this);e+=Math.floor(t/12),t=(t%12+12)%12;var n=[t+1,Qe(this),$e(this),et(this),tt(this)];e=(0>e?"-":e>9999?"+":"")+K("00000"+Math.abs(e),e>=0&&9999>=e?-4:-6);for(var r=0;r<n.length;++r)n[r]=K("00"+n[r],-2);return e+"-"+F(n,0,2).join("-")+"T"+F(n,2).join(":")+"."+K("000"+nt(this),-3)+"Z"}},lt||ut);var ft=function(){try{return Date.prototype.toJSON&&null===new Date(NaN).toJSON()&&-1!==new Date(at).toJSON().indexOf(st)&&Date.prototype.toJSON.call({toISOString:function(){return!0}})}catch(e){return!1}}();ft||(Date.prototype.toJSON=function(t){var n=i(this),r=W.ToPrimitive(n);if("number"==typeof r&&!isFinite(r))return null;var o=n.toISOString;if(!e(o))throw new TypeError("toISOString property is not callable");return o.call(n)});var dt=1e15===Date.parse("+033658-09-27T01:46:40.000Z"),pt=!isNaN(Date.parse("2012-04-04T24:00:00.500Z"))||!isNaN(Date.parse("2012-11-31T23:59:59.000Z"))||!isNaN(Date.parse("2012-12-31T23:59:60.000Z")),ht=isNaN(Date.parse("2000-01-01T00:00:00.000Z"));if(ht||pt||!dt){var gt=Math.pow(2,31)-1,mt=R(new Date(1970,0,1,0,0,0,gt+1).getTime());Date=function(e){var t=function(n,r,i,o,a,s,u){var c,f=arguments.length;if(this instanceof e){var d=s,p=u;if(mt&&f>=7&&u>gt){var h=Math.floor(u/gt)*gt,g=Math.floor(h/1e3);d+=g,p-=1e3*g}c=1===f&&l(n)===n?new e(t.parse(n)):f>=7?new e(n,r,i,o,a,d,p):f>=6?new e(n,r,i,o,a,d):f>=5?new e(n,r,i,o,a):f>=4?new e(n,r,i,o):f>=3?new e(n,r,i):f>=2?new e(n,r):f>=1?new e(n instanceof e?+n:n):new e}else c=e.apply(this,arguments);return G(c)||O(c,{constructor:t},!0),c},n=new RegExp("^(\\d{4}|[+-]\\d{6})(?:-(\\d{2})(?:-(\\d{2})(?:T(\\d{2}):(\\d{2})(?::(\\d{2})(?:(\\.\\d{1,}))?)?(Z|(?:([-+])(\\d{2}):(\\d{2})))?)?)?)?$"),r=[0,31,59,90,120,151,181,212,243,273,304,334,365],i=function(e,t){var n=t>1?1:0;return r[t]+Math.floor((e-1969+n)/4)-Math.floor((e-1901+n)/100)+Math.floor((e-1601+n)/400)+365*(e-1970)},o=function(t){var n=0,r=t;if(mt&&r>gt){var i=Math.floor(r/gt)*gt,o=Math.floor(i/1e3);n+=o,r-=1e3*o}return c(new e(1970,0,1,0,0,n,r))};for(var a in e)J(e,a)&&(t[a]=e[a]);O(t,{now:e.now,UTC:e.UTC},!0),t.prototype=e.prototype,O(t.prototype,{constructor:t},!0);var s=function(t){var r=n.exec(t);if(r){var a,s=c(r[1]),l=c(r[2]||1)-1,u=c(r[3]||1)-1,f=c(r[4]||0),d=c(r[5]||0),p=c(r[6]||0),h=Math.floor(1e3*c(r[7]||0)),g=Boolean(r[4]&&!r[8]),m="-"===r[9]?1:-1,v=c(r[10]||0),y=c(r[11]||0),w=d>0||p>0||h>0;return(w?24:25)>f&&60>d&&60>p&&1e3>h&&l>-1&&12>l&&24>v&&60>y&&u>-1&&u<i(s,l+1)-i(s,l)&&(a=60*(24*(i(s,l)+u)+f+v*m),a=1e3*(60*(a+d+y*m)+p)+h,g&&(a=o(a)),a>=-864e13&&864e13>=a)?a:NaN}return e.parse.apply(this,arguments)};return O(t,{parse:s}),t}(Date)}Date.now||(Date.now=function(){return(new Date).getTime()});var vt=f.toFixed&&("0.000"!==8e-5.toFixed(3)||"1"!==.9.toFixed(0)||"1.25"!==1.255.toFixed(2)||"1000000000000000128"!==0xde0b6b3a7640080.toFixed(0)),yt={base:1e7,size:6,data:[0,0,0,0,0,0],multiply:function(e,t){for(var n=-1,r=t;++n<yt.size;)r+=e*yt.data[n],yt.data[n]=r%yt.base,r=Math.floor(r/yt.base)},divide:function(e){for(var t=yt.size,n=0;--t>=0;)n+=yt.data[t],yt.data[t]=Math.floor(n/e),n=n%e*yt.base},numToString:function(){for(var e=yt.size,t="";--e>=0;)if(""!==t||0===e||0!==yt.data[e]){var n=l(yt.data[e]);""===t?t=n:t+=K("0000000",0,7-n.length)+n}return t},pow:function Ot(e,t,n){return 0===t?n:t%2===1?Ot(e,t-1,n*e):Ot(e*e,t/2,n)},log:function(e){for(var t=0,n=e;n>=4096;)t+=12,n/=4096;for(;n>=2;)t+=1,n/=2;return t}},wt=function(e){var t,n,r,i,o,a,s,u;if(t=c(e),t=R(t)?0:Math.floor(t),0>t||t>20)throw new RangeError("Number.toFixed called with invalid number of decimals");if(n=c(this),R(n))return"NaN";if(-1e21>=n||n>=1e21)return l(n);if(r="",0>n&&(r="-",n=-n),i="0",n>1e-21)if(o=yt.log(n*yt.pow(2,69,1))-69,a=0>o?n*yt.pow(2,-o,1):n/yt.pow(2,o,1),a*=4503599627370496,o=52-o,o>0){for(yt.multiply(0,a),s=t;s>=7;)yt.multiply(1e7,0),s-=7;for(yt.multiply(yt.pow(10,s,1),0),s=o-1;s>=23;)yt.divide(1<<23),s-=23;yt.divide(1<<s),yt.multiply(1,1),yt.divide(2),i=yt.numToString()}else yt.multiply(0,a),yt.multiply(1<<-o,0),i=yt.numToString()+K("0.00000000000000000000",2,2+t);return t>0?(u=i.length,i=t>=u?r+K("0.0000000000000000000",0,t-u+2)+i:r+K(i,0,u-t)+"."+K(i,u-t)):i=r+i,i};O(f,{toFixed:wt},vt);var bt=function(){try{return"1"===1..toPrecision(void 0)}catch(e){return!0}}(),It=f.toPrecision;O(f,{toPrecision:function(e){return"undefined"==typeof e?It.call(this):It.call(this,e)}},bt),2!=="ab".split(/(?:ab)*/).length||4!==".".split(/(.?)(.?)/).length||"t"==="tesst".split(/(s)*/)[1]||4!=="test".split(/(?:)/,-1).length||"".split(/.?/).length||".".split(/()()/).length>1?!function(){var e="undefined"==typeof/()??/.exec("")[1],n=Math.pow(2,32)-1;u.split=function(r,i){var o=String(this);if("undefined"==typeof r&&0===i)return[];if(!t(r))return Q(this,r,i);var a,s,l,u,c=[],f=(r.ignoreCase?"i":"")+(r.multiline?"m":"")+(r.unicode?"u":"")+(r.sticky?"y":""),d=0,p=new RegExp(r.source,f+"g");e||(a=new RegExp("^"+p.source+"$(?!\\s)",f));var g="undefined"==typeof i?n:W.ToUint32(i);for(s=p.exec(o);s&&(l=s.index+s[0].length,!(l>d&&($(c,K(o,d,s.index)),!e&&s.length>1&&s[0].replace(a,function(){for(var e=1;e<arguments.length-2;e++)"undefined"==typeof arguments[e]&&(s[e]=void 0)}),s.length>1&&s.index<o.length&&h.apply(c,F(s,1)),u=s[0].length,d=l,c.length>=g)));)p.lastIndex===s.index&&p.lastIndex++,s=p.exec(o);return d===o.length?(u||!p.test(""))&&$(c,""):$(c,K(o,d)),c.length>g?F(c,0,g):c}}():"0".split(void 0,0).length&&(u.split=function(e,t){return"undefined"==typeof e&&0===t?[]:Q(this,e,t)});var Mt=u.replace,Ct=function(){var e=[];return"x".replace(/x(.)?/g,function(t,n){$(e,n)}),1===e.length&&"undefined"==typeof e[0]}();Ct||(u.replace=function(n,r){var i=e(r),o=t(n)&&/\)[*?]/.test(n.source);if(i&&o){var a=function(e){var t=arguments.length,i=n.lastIndex;n.lastIndex=0;var o=n.exec(e)||[];return n.lastIndex=i,$(o,arguments[t-2],arguments[t-1]),r.apply(this,o)};return Mt.call(this,n,a)}return Mt.call(this,n,r)});var At=u.substr,St="".substr&&"b"!=="0b".substr(-1);O(u,{substr:function(e,t){var n=e;return 0>e&&(n=b(this.length+e,0)),At.call(this,n,t)}},St);var Et="	\n\f\r   ᠎             　\u2028\u2029\ufeff",jt="​",Dt="["+Et+"]",Nt=new RegExp("^"+Dt+Dt+"*"),xt=new RegExp(Dt+Dt+"*$"),Lt=u.trim&&(Et.trim()||!jt.trim());O(u,{trim:function(){if("undefined"==typeof this||null===this)throw new TypeError("can't convert "+this+" to object");return l(this).replace(Nt,"").replace(xt,"")}},Lt);var Tt=y.bind(String.prototype.trim),Zt=u.lastIndexOf&&-1!=="abcあい".lastIndexOf("あい",2);O(u,{lastIndexOf:function(e){if("undefined"==typeof this||null===this)throw new TypeError("can't convert "+this+" to object");for(var t=l(this),n=l(e),r=arguments.length>1?c(arguments[1]):NaN,i=R(r)?1/0:W.ToInteger(r),o=I(b(i,0),t.length),a=n.length,s=o+a;s>0;){s=b(0,s-a);var u=q(K(t,s,o+a),n);if(-1!==u)return s+u}return-1}},Zt);var Pt=u.lastIndexOf;if(O(u,{lastIndexOf:function(e){return Pt.apply(this,arguments)}},1!==u.lastIndexOf.length),(8!==parseInt(Et+"08")||22!==parseInt(Et+"0x16"))&&(parseInt=function(e){var t=/^[-+]?0[xX]/;return function(n,r){var i=Tt(String(n)),o=c(r)||(t.test(i)?16:10);return e(i,o)}}(parseInt)),1/parseFloat("-0")!==-(1/0)&&(parseFloat=function(e){return function(t){var n=Tt(String(t)),r=e(n);return 0===r&&"-"===K(n,0,1)?-0:r}}(parseFloat)),"RangeError: test"!==String(new RangeError("test"))){var kt=function(){if("undefined"==typeof this||null===this)throw new TypeError("can't convert "+this+" to object");var e=this.name;"undefined"==typeof e?e="Error":"string"!=typeof e&&(e=l(e));var t=this.message;return"undefined"==typeof t?t="":"string"!=typeof t&&(t=l(t)),e?t?e+": "+t:e:t};Error.prototype.toString=kt}if(z){var Yt=function(e,t){if(ee(e,t)){var n=Object.getOwnPropertyDescriptor(e,t);n.configurable&&(n.enumerable=!1,Object.defineProperty(e,t,n))}};Yt(Error.prototype,"message"),""!==Error.prototype.message&&(Error.prototype.message=""),Yt(Error.prototype,"name")}if("/a/gim"!==String(/a/gim)){var zt=function(){var e="/"+this.source+"/";return this.global&&(e+="g"),this.ignoreCase&&(e+="i"),this.multiline&&(e+="m"),e};RegExp.prototype.toString=zt}})},{}],39:[function(e,t,n){var r=[],i=r.forEach,o=r.slice;t.exports=function(e){return i.call(o.call(arguments,1),function(t){if(t)for(var n in t)e[n]=t[n]}),e}},{}],40:[function(e,t,n){n.read=function(e,t,n,r,i){var o,a,s=8*i-r-1,l=(1<<s)-1,u=l>>1,c=-7,f=n?i-1:0,d=n?-1:1,p=e[t+f];for(f+=d,o=p&(1<<-c)-1,p>>=-c,c+=s;c>0;o=256*o+e[t+f],f+=d,c-=8);for(a=o&(1<<-c)-1,o>>=-c,c+=r;c>0;a=256*a+e[t+f],f+=d,c-=8);if(0===o)o=1-u;else{if(o===l)return a?NaN:(p?-1:1)*(1/0);a+=Math.pow(2,r),o-=u}return(p?-1:1)*a*Math.pow(2,o-r)},n.write=function(e,t,n,r,i,o){var a,s,l,u=8*o-i-1,c=(1<<u)-1,f=c>>1,d=23===i?Math.pow(2,-24)-Math.pow(2,-77):0,p=r?0:o-1,h=r?1:-1,g=0>t||0===t&&0>1/t?1:0;for(t=Math.abs(t),isNaN(t)||t===1/0?(s=isNaN(t)?1:0,a=c):(a=Math.floor(Math.log(t)/Math.LN2),t*(l=Math.pow(2,-a))<1&&(a--,l*=2),t+=a+f>=1?d/l:d*Math.pow(2,1-f),t*l>=2&&(a++,l/=2),a+f>=c?(s=0,a=c):a+f>=1?(s=(t*l-1)*Math.pow(2,i),a+=f):(s=t*Math.pow(2,f-1)*Math.pow(2,i),a=0));i>=8;e[n+p]=255&s,p+=h,s/=256,i-=8);for(a=a<<i|s,u+=i;u>0;e[n+p]=255&a,p+=h,a/=256,u-=8);e[n+p-h]|=128*g}},{}],41:[function(e,t,n){var r=[].indexOf;t.exports=function(e,t){if(r)return e.indexOf(t);for(var n=0;n<e.length;++n)if(e[n]===t)return n;return-1}},{}],42:[function(e,t,n){function r(e){var t=i.call(e);return"[object Function]"===t||"function"==typeof e&&"[object RegExp]"!==t||"undefined"!=typeof window&&(e===window.setTimeout||e===window.alert||e===window.confirm||e===window.prompt)}t.exports=r;var i=Object.prototype.toString},{}],43:[function(e,t,n){var r={}.toString;t.exports=Array.isArray||function(e){return"[object Array]"==r.call(e)}},{}],44:[function(t,n,r){(function(t){!function(i){function o(e){throw new RangeError(P[e])}function a(e,t){for(var n=e.length,r=[];n--;)r[n]=t(e[n]);return r}function s(e,t){var n=e.split("@"),r="";n.length>1&&(r=n[0]+"@",e=n[1]),e=e.replace(Z,".");var i=e.split("."),o=a(i,t).join(".");return r+o}function l(e){for(var t,n,r=[],i=0,o=e.length;o>i;)t=e.charCodeAt(i++),t>=55296&&56319>=t&&o>i?(n=e.charCodeAt(i++),56320==(64512&n)?r.push(((1023&t)<<10)+(1023&n)+65536):(r.push(t),i--)):r.push(t);return r}function u(e){return a(e,function(e){var t="";return e>65535&&(e-=65536,t+=z(e>>>10&1023|55296),e=56320|1023&e),t+=z(e)}).join("")}function c(e){return 10>e-48?e-22:26>e-65?e-65:26>e-97?e-97:C}function f(e,t){return e+22+75*(26>e)-((0!=t)<<5)}function d(e,t,n){var r=0;for(e=n?Y(e/j):e>>1,e+=Y(e/t);e>k*S>>1;r+=C)e=Y(e/k);return Y(r+(k+1)*e/(e+E))}function p(e){var t,n,r,i,a,s,l,f,p,h,g=[],m=e.length,v=0,y=N,w=D;for(n=e.lastIndexOf(x),0>n&&(n=0),r=0;n>r;++r)e.charCodeAt(r)>=128&&o("not-basic"),g.push(e.charCodeAt(r));for(i=n>0?n+1:0;m>i;){for(a=v,s=1,l=C;i>=m&&o("invalid-input"),f=c(e.charCodeAt(i++)),(f>=C||f>Y((M-v)/s))&&o("overflow"),v+=f*s,p=w>=l?A:l>=w+S?S:l-w,!(p>f);l+=C)h=C-p,s>Y(M/h)&&o("overflow"),s*=h;t=g.length+1,w=d(v-a,t,0==a),Y(v/t)>M-y&&o("overflow"),y+=Y(v/t),v%=t,g.splice(v++,0,y)}return u(g)}function h(e){var t,n,r,i,a,s,u,c,p,h,g,m,v,y,w,b=[];for(e=l(e),m=e.length,t=N,n=0,a=D,s=0;m>s;++s)g=e[s],128>g&&b.push(z(g));for(r=i=b.length,i&&b.push(x);m>r;){for(u=M,s=0;m>s;++s)g=e[s],g>=t&&u>g&&(u=g);for(v=r+1,u-t>Y((M-n)/v)&&o("overflow"),n+=(u-t)*v,t=u,s=0;m>s;++s)if(g=e[s],t>g&&++n>M&&o("overflow"),g==t){for(c=n,p=C;h=a>=p?A:p>=a+S?S:p-a,!(h>c);p+=C)w=c-h,y=C-h,b.push(z(f(h+w%y,0))),c=Y(w/y);b.push(z(f(c,0))),a=d(n,v,r==i),n=0,++r}++n,++t}return b.join("")}function g(e){return s(e,function(e){return L.test(e)?p(e.slice(4).toLowerCase()):e})}function m(e){return s(e,function(e){return T.test(e)?"xn--"+h(e):e})}var v="object"==typeof r&&r&&!r.nodeType&&r,y="object"==typeof n&&n&&!n.nodeType&&n,w="object"==typeof t&&t;(w.global===w||w.window===w||w.self===w)&&(i=w);var b,I,M=2147483647,C=36,A=1,S=26,E=38,j=700,D=72,N=128,x="-",L=/^xn--/,T=/[^\x20-\x7E]/,Z=/[\x2E\u3002\uFF0E\uFF61]/g,P={overflow:"Overflow: input needs wider integers to process","not-basic":"Illegal input >= 0x80 (not a basic code point)","invalid-input":"Invalid input"},k=C-A,Y=Math.floor,z=String.fromCharCode;if(b={version:"1.4.1",ucs2:{decode:l,encode:u},decode:p,encode:h,toASCII:m,toUnicode:g},"function"==typeof e&&"object"==typeof e.amd&&e.amd)e("punycode",function(){return b});else if(v&&y)if(n.exports==v)y.exports=b;else for(I in b)b.hasOwnProperty(I)&&(v[I]=b[I]);else i.punycode=b}(this)}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],45:[function(t,n,r){!function(t,r){"undefined"!=typeof n&&n.exports?n.exports=r():"function"==typeof e&&e.amd?e(r):this[t]=r()}("$script",function(){function e(e,t){for(var n=0,r=e.length;r>n;++n)if(!t(e[n]))return l;return 1}function t(t,n){e(t,function(e){return!n(e)})}function n(o,a,s){function l(e){return e.call?e():d[e]}function c(){if(!--y){d[v]=1,m&&m();for(var n in h)e(n.split("|"),l)&&!t(h[n],l)&&(h[n]=[])}}o=o[u]?o:[o];var f=a&&a.call,m=f?a:s,v=f?o.join(""):a,y=o.length;return setTimeout(function(){t(o,function e(t,n){return null===t?c():(n||/^https?:\/\//.test(t)||!i||(t=-1===t.indexOf(".js")?i+t+".js":i+t),g[t]?(v&&(p[v]=1),2==g[t]?c():setTimeout(function(){e(t,!0)},0)):(g[t]=1,v&&(p[v]=1),void r(t,c)))})},0),n}function r(e,t){var n,r=a.createElement("script");r.onload=r.onerror=r[f]=function(){r[c]&&!/^c|loade/.test(r[c])||n||(r.onload=r[f]=null,n=1,g[e]=2,t())},r.async=1,r.src=o?e+(-1===e.indexOf("?")?"?":"&")+o:e,s.insertBefore(r,s.lastChild)}var i,o,a=document,s=a.getElementsByTagName("head")[0],l=!1,u="push",c="readyState",f="onreadystatechange",d={},p={},h={},g={};return n.get=r,n.order=function(e,t,r){!function i(o){o=e.shift(),e.length?n(o,i):n(o,t,r)}()},n.path=function(e){i=e},n.urlArgs=function(e){o=e},n.ready=function(r,i,o){r=r[u]?r:[r];var a=[];return!t(r,function(e){d[e]||a[u](e)})&&e(r,function(e){return d[e]})?i():!function(e){h[e]=h[e]||[],h[e][u](i),o&&o(a)}(r.join("|")),n},n.done=function(e){n([null],e)},n})},{}]},{},[32])(32)});



/*! js-cookie v2.2.0 | MIT */
!function(e){var n=!1;if("function"==typeof define&&define.amd&&(define(e),n=!0),"object"==typeof exports&&(module.exports=e(),n=!0),!n){var o=window.Cookies,t=window.Cookies=e();t.noConflict=function(){return window.Cookies=o,t}}}(function(){function e(){for(var e=0,n={};e<arguments.length;e++){var o=arguments[e];for(var t in o)n[t]=o[t]}return n}function n(o){function t(n,r,i){var c;if("undefined"!=typeof document){if(arguments.length>1){if("number"==typeof(i=e({path:"/"},t.defaults,i)).expires){var a=new Date;a.setMilliseconds(a.getMilliseconds()+864e5*i.expires),i.expires=a}i.expires=i.expires?i.expires.toUTCString():"";try{c=JSON.stringify(r),/^[\{\[]/.test(c)&&(r=c)}catch(e){}r=o.write?o.write(r,n):encodeURIComponent(r+"").replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),n=(n=(n=encodeURIComponent(n+"")).replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent)).replace(/[\(\)]/g,escape);var s="";for(var f in i)i[f]&&(s+="; "+f,!0!==i[f]&&(s+="="+i[f]));return document.cookie=n+"="+r+s}n||(c={});for(var p=document.cookie?document.cookie.split("; "):[],d=/(%[0-9A-Z]{2})+/g,u=0;u<p.length;u++){var l=p[u].split("="),C=l.slice(1).join("=");this.json||'"'!==C.charAt(0)||(C=C.slice(1,-1));try{var m=l[0].replace(d,decodeURIComponent);if(C=o.read?o.read(C,m):o(C,m)||C.replace(d,decodeURIComponent),this.json)try{C=JSON.parse(C)}catch(e){}if(n===m){c=C;break}n||(c[m]=C)}catch(e){}}return c}}return t.set=t,t.get=function(e){return t.call(t,e)},t.getJSON=function(){return t.apply({json:!0},[].slice.call(arguments))},t.defaults={},t.remove=function(n,o){t(n,"",e(o,{expires:-1}))},t.withConverter=n,t}return n(function(){})});


/*
FV Flowplayer additions!
*/
if( typeof(fv_flowplayer_conf) != "undefined" ) {
  try {
    if(typeof(window.localStorage) == 'object' && typeof(window.localStorage.volume) != 'undefined'){
      delete fv_flowplayer_conf.volume;
    }
  } catch(e) {}
  
  flowplayer.conf = fv_flowplayer_conf;
  flowplayer.conf.share = false;
  
  if( !flowplayer.support.android && flowplayer.conf.dacast_hlsjs ) {
    function FVAbrController(hls) {      
      this.hls = hls;
      this.nextAutoLevel = 3;
    }
    
    FVAbrController.prototype.nextAutoLevel = function(nextLevel) {
      this.nextAutoLevel = nextLevel;
    }
    
    FVAbrController.prototype.destroy = function() {
      EventHandler.prototype.destroy.call(this);
    }
    
    flowplayer.conf.hlsjs = {      
      startLevel: -1, // todo: doesn't seem to work, fix it to pick quality matching the player size
      abrController: FVAbrController
    }
  }
  
  flowplayer.support.fvmobile = !!( !flowplayer.support.firstframe || flowplayer.support.iOS || flowplayer.support.android );
  
  var fls = flowplayer.support;
  if( flowplayer.conf.mobile_native_fullscreen && ( 'ontouchstart' in window ) && fls.fvmobile ) {
    flowplayer.conf.native_fullscreen = true;
  }
  
  if( 'ontouchstart' in window ) {    
    if( fls.android && fls.android.version < 4.4 && ! ( fls.browser.chrome && fls.browser.version > 54 ) ) {
      flowplayer.conf.native_fullscreen = true;
    }
    
    function inIframe() {
      try {
          return window.self !== window.top;
      } catch (e) {
          return true;
      }
    }
    
    if( fls.iOS && ( inIframe() || fls.iOS.version < 7 ) ) {
      flowplayer.conf.native_fullscreen = true;
    }
  }
}
if( typeof(fv_flowplayer_translations) != "undefined" ) {
  flowplayer.defaults.errors = fv_flowplayer_translations;
}

if( typeof(fv_flowplayer_admin_input) != "undefined" && fv_flowplayer_admin_input ) {
	jQuery(document).keyup(function(e) { 
		if (e.keyCode == 27) { fv_wp_flowplayer_admin_show_notice(); 	}   // esc
	});
	
	jQuery(document).click( function(event) {
		if( jQuery(event.target).parents('.fv-wp-flowplayer-notice').length == 0 &&
			jQuery(event.target).parents('.fv-wp-flowplayer-notice-small').length == 0 ) {
			if( jQuery('.fv-wp-flowplayer-notice:visible').length ) fv_wp_flowplayer_admin_show_notice();
		}
	}	);
}

function fv_wp_flowplayer_admin_show_notice( id, link ) {
	if( id == null && link == null ) {
		var api = flowplayer(), currentPos;
		if( typeof api != "undefined" ) {
			api.disable(false);
		}
		jQuery('.fv-wp-flowplayer-notice .fv_wp_fp_notice_content').toggle();
		jQuery('.fv-wp-flowplayer-notice').toggleClass("fv-wp-flowplayer-notice");					
	} else {			
		jQuery('#fv_wp_fp_notice_'+id).toggle();

		var api = flowplayer(), currentPos;
		if( typeof(api) != "undefined" && jQuery('#fv_wp_fp_notice_'+id).parent().hasClass("fv-wp-flowplayer-notice") ) {
			api.disable(false);
		} else if( typeof(api) != "undefined" ) {
			api.disable(true);
		}
		
		jQuery('#fv_wp_fp_notice_'+id).parent().toggleClass("fv-wp-flowplayer-notice");
	}
  
  jQuery('.fv-wp-flowplayer-notice').each( function() {
    if( jQuery(this).is(':visible') )  {
      jQuery(this).parents('.flowplayer').addClass('has-video-checker');
    } else {
      jQuery(this).parents('.flowplayer').removeClass('has-video-checker');
    }
  });
}				

function fv_wp_flowplayer_admin_support_mail( hash, button ) {
	jQuery('.fv_flowplayer_submit_error').remove();
	
	var comment_text = jQuery('#wpfp_support_'+hash).val();
	var comment_words = comment_text.split(/\s/);
	if( comment_words.length == 0 || comment_text.match(/Enter your comment/) ) {
		jQuery('#wpfp_support_'+hash).before('<p class="fv_flowplayer_submit_error" style="display:none; "><strong>'+fv_flowplayer_translations.what_is_wrong+'</strong></p>');
		jQuery('.fv_flowplayer_submit_error').fadeIn();
		return false;
	}

	if( comment_words.length < 7 ) {
		jQuery('#wpfp_support_'+hash).before('<p class="fv_flowplayer_submit_error" style="display:none; "><strong>'+fv_flowplayer_translations.full_sentence+'</strong>:</p>');
		jQuery('.fv_flowplayer_submit_error').fadeIn();					
		return false;
	}
	
	jQuery('#wpfp_spin_'+hash).show();
	jQuery(button).attr("disabled", "disabled");
				
	jQuery.post(
		fv_fp_ajaxurl,
		{
			action: 'fv_wp_flowplayer_support_mail',
			comment: comment_text,
			notice: jQuery('#wpfp_notice_'+hash+' .mail-content-notice').html(),
			details: jQuery('#wpfp_notice_'+hash+' .mail-content-details').html()						
		},
		function( response ) {
			jQuery('#wpfp_spin_'+hash).hide();					
			jQuery(button).removeAttr("disabled");
			jQuery(button).after(' Message sent');
			setTimeout( function() { fv_wp_flowplayer_admin_show_notice(hash) }, 1500 );
		}	
	);
}

function fv_flowplayer_admin_message_parse_group(aInfo) {
  var sOutput = '';
  if( typeof(aInfo) != "undefined" && Object.keys(aInfo).length > 0 ) {
    for( var j in aInfo ) {
      if( j == parseInt(j) ){
        sOutput += aInfo[j]+'<br />';
      } else if( typeof(aInfo[j]) == "function" ) {
        continue;
      } else {
        sOutput += j+': <tt>'+aInfo[j]+'</tt><br />';
      }
    }
  }
  if( sOutput.length > 0 ){
    sOutput = '<p>'+sOutput+'</p>';
  }
  return sOutput;
}



if( typeof(fv_flowplayer_admin_test_media_array) != "undefined" ) {
	var fv_flowplayer_scroll_video_checker = false;
  var fv_flowplayer_scroll_video_checker_status = [];
	jQuery(document).ready( function() { fv_flowplayer_scroll_video_checker = true; } );	
	jQuery(document).scroll( function() { fv_flowplayer_scroll_video_checker = true; } );
	
	setInterval( function() {
		if( !fv_flowplayer_scroll_video_checker ) return;

		var iMin = jQuery(window).scrollTop();
		var iMax = iMin + jQuery(window).height();
		jQuery('.flowplayer').each( function() {
			var iPlayer = jQuery(this).offset().top;
			if( iPlayer > iMin && iPlayer < iMax ) {
				if( typeof(fv_flowplayer_scroll_video_checker_status[jQuery(this).attr('id')]) == "undefined" ) {				//  todo: store this somewhere else!
					fv_flowplayer_scroll_video_checker_status[jQuery(this).attr('id')] = true;
					var sID = jQuery(this).attr('id').replace(/wpfp_/,'');
					if( typeof(fv_flowplayer_admin_test_media_array[sID]) != "undefined" ) {
						fv_flowplayer_admin_test_media( sID, fv_flowplayer_admin_test_media_array[sID] );	
					}					
				}
			}
		} );
		fv_flowplayer_scroll_video_checker = false;
	}, 500 );
}


function fv_flowplayer_admin_test_media( hash, media ) {
    var hVideoChecker = jQuery('#wpfp_notice_'+hash);
    jQuery('#wpfp_notice_'+hash).parent().append(jQuery('#wpfp_notice_'+hash));
    jQuery('#wpfp_notice_'+hash).show();
    
		jQuery.post( 'https://video-checker.foliovision.com/', { action: 'vid_check', media: media, hash: hash, site: flowplayer.conf.video_checker_site }, function( response ) {
			var obj;
			try {
        response = response.replace( /[\s\S]*<FVFLOWPLAYER>/, '' );
        response = response.replace( /<\/FVFLOWPLAYER>[\s\S]*/, '' );
				obj = jQuery.parseJSON( response );
        
        var sCheckerInfo = '';
        var sCheckerDetails = '';
        var sResponseClass = 'vid-ok';
        var sResponseMsg = 'Video OK';
        
        for( var i in obj ) {
          if( !obj.hasOwnProperty(i) ) continue;
          if( i != "global" ) {
            sCheckerInfo += '<p>Analysis of <a href="'+i+'">'+i+'</a></p>';
          }
          sCheckerInfo += fv_flowplayer_admin_message_parse_group(obj[i].info);
                    
          var sWarnings = (typeof(obj[i].warnings) != "undefined" ) ? fv_flowplayer_admin_message_parse_group(obj[i].warnings) : false;
          if( typeof(obj[i].warnings) != "undefined" && sWarnings ) {
            if( sResponseClass != 'vid-issues' ) {
              sResponseMsg = 'Video Warnings';
              sResponseClass = 'vid-warning';
            }
            sCheckerInfo += sWarnings;         
          }          
          
          var sErrors = ( typeof(obj[i].errors) != "undefined" ) ? fv_flowplayer_admin_message_parse_group(obj[i].errors) : false;
          if( typeof(obj[i].errors) != "undefined" && sErrors ) {
            sResponseMsg = fv_flowplayer_translations.video_issues;
            sResponseClass = 'vid-issues';
            sCheckerInfo += sErrors;   
          }

          jQuery('#wpfp_notice_'+hash).find('.video-checker-result').addClass(sResponseClass).html(sResponseMsg);
          
          sCheckerDetails += fv_flowplayer_admin_message_parse_group(obj[i].details);

        }
        jQuery('#wpfp_notice_'+hash).find('.video-checker-result').wrap('<a class="fv_wp_flowplayer_dialog_link"></a>');
        jQuery('#wpfp_notice_'+hash).find('.fv_wp_flowplayer_dialog_link').click( function() { fv_wp_flowplayer_admin_show_notice( hash, this) } );
        jQuery('#wpfp_notice_'+hash).find('.mail-content-notice').html('<p>'+sCheckerInfo+'</p>');
        jQuery('#wpfp_notice_'+hash).find('.mail-content-details .fv-wp-flowplayer-notice-parsed').html(sCheckerDetails)
                    
			} catch(e) {
        console.log(e);
				jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.error_JSON+'</p>');
				return;
			}

		} ).error(function() { 
      if( /MSIE 9/i.test(navigator.userAgent) ){
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.no_support_IE9+'</p>');
      } else {
        jQuery('#wpfp_notice_'+hash).html('<p>'+fv_flowplayer_translations.check_failed+'</p>');
      }
    });
}


function fv_flowplayer_amazon_s3( hash, time ) {  //  v6
	jQuery('#wpfp_'+hash).bind('error', function (e,api, error) {
			var fv_fp_date = new Date();
			if( error.code == 4 && fv_fp_date.getTime() > (fv_fp_utime + parseInt(time)) ) {
				jQuery(e.target).find('.fp-message').delay(500).queue( function(n) {			
					jQuery(this).html(fv_flowplayer_translations.video_expired); n();
				} );
			}
	} );
}

function fv_flowplayer_browser_chrome_fail( hash, sAttributes, sVideo, bAutobuffer ) {
	jQuery('#wpfp_'+hash).bind('error', function (e,api, error) {
		if( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && error != null && ( error.code == 3 || error.code == 4 || error.code == 5 ) ) {							
			api.unload();
			
			jQuery('#wpfp_'+hash).attr('id','bad_wpfp_'+hash);					
			jQuery('#bad_wpfp_'+hash).after( '<div id="wpfp_'+hash+'" '+sAttributes+' data-engine="flash"></div>' );
			jQuery('#wpfp_'+hash).flowplayer({ playlist: [ [ {mp4: sVideo} ] ] });
      //  what about scripts?
			if( bAutobuffer ) {
				jQuery('#wpfp_'+hash).bind('ready', function(e, api) { api.play(); } );
			} else {
				jQuery('#wpfp_'+hash).flowplayer().play(0);
			}
			jQuery('#bad_wpfp_'+hash).remove();						
		}
	});				
}

function fv_flowplayer_browser_chrome_mp4( hash ) {
	var match = window.navigator.appVersion.match(/Chrome\/(\d+)\./);
	if( match != null ) {
		var chrome_ver = parseInt(match[1], 10);
		if(
			( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && chrome_ver < 28 && navigator.appVersion.indexOf("Win")!=-1 ) || 
			( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) && chrome_ver < 27 && navigator.appVersion.indexOf("Linux")!=-1 && navigator.userAgent.toLowerCase().indexOf("android")==-1 )							
		) {
			jQuery('#wpfp_'+hash).attr('data-engine','flash');
		}
	}
}

function fv_flowplayer_browser_ff_m4v( hash ) {
	if( jQuery.browser && jQuery.browser.mozilla && navigator.appVersion.indexOf("Win")!=-1 ) {
		jQuery('#wpfp_'+hash).attr('data-engine','flash');
	}
}

function fv_flowplayer_browser_ie( hash ) {
	if( ( jQuery.browser && jQuery.browser.msie && parseInt(jQuery.browser.version, 10) >= 9) /*|| !!navigator.userAgent.match(/Trident.*rv[ :]*11\./)*/ ) {
		jQuery('#wpfp_'+hash).attr('data-engine','flash');
	}
}

if( (navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) || (navigator.userAgent.toLowerCase().indexOf("android") != -1) ) {  	
  flowplayer(function (api, root) { 
    api.bind("error", function (e,api, error) {
      if( error.code == 10 ) {
        jQuery(e.target).find('.fp-message').html(fv_flowplayer_translations.unsupported_format);
      }
    });
  });
}  	

jQuery(document).ready( function() {
  if( (navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) ) {
    jQuery(window).trigger('load');
  }
  jQuery('.flowplayer').mouseleave( function() {
    jQuery(this).find('.fvp-share-bar').removeClass('visible');
    jQuery(this).find('.embed-code').hide();
  } ); 
} );

jQuery(document).on('click', '.flowplayer .embed-code-toggle', function() {
  var button = jQuery(this);
  var player = button.parents('.flowplayer');
  var api = player.data('flowplayer');
  if( typeof(api.embedCode) == 'function' && player.find('.embed-code textarea').val() == '' ) {
    player.find('.embed-code textarea').val(api.embedCode());  
  }
  
  fv_player_clipboard( player.find('.embed-code textarea').val(), function() {
      fv_player_notice(player,fv_flowplayer_translations.embed_copied,2000);          
    }, function() {
      button.parents('.fvp-share-bar').find('.embed-code').toggle();
      button.parents('.fvp-share-bar').toggleClass('visible');
    });
  
  return false;
} ); 


function fv_flowplayer_mobile_switch(id) {
	var regex = new RegExp("[\\?&]fv_flowplayer_mobile=([^&#]*)");
	var results = regex.exec(location.search);	
	if(
		(
			(results != null && results[1] == 'yes') ||
			(jQuery(window).width() <= 480 || jQuery(window).height() <= 480) //  todo: improve for Android with 1.5 pixel ratio 
		)
		&&
		(results == null || results[1] != 'no')
	) {
		var fv_fp_mobile = false;
		jQuery('#wpfp_'+id+' video source').each( function() {
			if( jQuery(this).attr('id') != 'wpfp_'+id+'_mobile' ) {
				fv_fp_mobile = true
				jQuery(this).remove();
			}
		} );
		if( fv_fp_mobile ) {
			jQuery('#wpfp_'+id).after('<p class="fv-flowplayer-mobile-switch">'+fv_flowplayer_translations.mobile_browser_detected_1+' <a href="'+document.URL+'?fv_flowplayer_mobile=no">'+fv_flowplayer_translations.mobile_browser_detected_2+'</a> '+fv_flowplayer_translations.mobile_browser_detected_3+'</p>');
		}
	}
}


var fv_flowplayer_safety_resize_arr = Array();

function fv_flowplayer_safety_resize() {
	var fv_flowplayer_safety_resize_init = false;

	jQuery('.flowplayer').each( function() {
    if( !jQuery(this).is(":visible") || jQuery(this).hasClass('lightboxed') || jQuery(this).hasClass('lightbox-starter') || jQuery(this).hasClass('is-audio') ) return;
    
		if( jQuery(this).width() < 30 || jQuery(this).height() < 20 ) {
			fv_flowplayer_safety_resize_init = true
			var el = jQuery(this);
			while( jQuery(el).width() < 30 || jQuery(el).width() == jQuery(this).width() ) {
        if( jQuery(el).parent().length == 0 ) break; 
				el = jQuery(el).parent();
			}
			
			jQuery(this).width( jQuery(el).width() );
			jQuery(this).height( parseInt(jQuery(this).width() * jQuery(this).attr('data-ratio')) );					
			fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')] = el;                  
		}
	} );
	
	if( fv_flowplayer_safety_resize_init ) {
		jQuery(window).resize(function() {
			jQuery('.flowplayer').each( function() {
        if( jQuery(this).hasClass('lightboxed') || jQuery(this).hasClass('lightbox-starter') ) return;
        
				if( fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')] ) {
					jQuery(this).width( fv_flowplayer_safety_resize_arr[jQuery(this).attr('id')].width() );
					jQuery(this).height( parseInt(jQuery(this).width() * jQuery(this).attr('data-ratio')) );	
				}
			} );  
		} );    
	}
}

if( typeof(flowplayer.conf.safety_resize) != "undefined" && flowplayer.conf.safety_resize ) {
  jQuery(document).ready(function() { setTimeout( function() { fv_flowplayer_safety_resize(); }, 10 ); } );	
}




//  did autoplay?
var fv_player_did_autoplay = false;




function fv_player_videos_parse(args, root) {
  var videos = JSON.parse(args);
  
  var regex = new RegExp("[\\?&]fv_flowplayer_mobile=([^&#]*)");
	var results = regex.exec(location.search);	
	if(
		(
			(results != null && results[1] == 'yes') ||
			(jQuery(window).width() <= 480 || jQuery(window).height() <= 480) //  todo: improve for Android with 1.5 pixel ratio 
		)
		&&
		(results == null || results[1] != 'no')
	) {
    var fv_fp_mobile = false;
    jQuery(videos.sources).each( function(k,v) {
      if(v.mobile) {
        videos.sources[k] = videos.sources[0];
        videos.sources[0] = v;
        fv_fp_mobile = true;
      }
      if( fv_fp_mobile ) {
        jQuery(root).after('<p class="fv-flowplayer-mobile-switch">'+fv_flowplayer_translations.mobile_browser_detected_1+' <a href="'+document.URL+'?fv_flowplayer_mobile=no">'+fv_flowplayer_translations.mobile_browser_detected_2+'</a> '+fv_flowplayer_translations.mobile_browser_detected_3+'</p>');
      }
    });
  }
  return videos;
}




jQuery(document).ready( function() {
  var loading_count = 0;
  var loading = setInterval( function() {
    loading_count++;
    if( loading_count < 1000 && (
      window.fv_video_intelligence_conf && !window.FV_Player_IMA ||
      window.fv_vast_conf && !window.FV_Player_IMA ||
      window.fv_player_pro && !window.FV_Flowplayer_Pro && document.getElementById('fv_player_pro') != fv_player_pro
    ) ) {      
      return;
    }
    clearInterval(loading);
    fv_player_preload();
  }, 10 );
});

function fv_player_preload() {
 
  if( flowplayer.support.touch ) {
    jQuery('.fp-playlist-external.fv-playlist-design-2017').addClass('visible-captions');
  }

  flowplayer( function(api,root) {
    root = jQuery(root);
    
    if( root.hasClass('fixed-controls') ) {
      root.find('.fp-controls').click( function(e) {
        if( !api.loading && !api.ready ) {
          e.preventDefault();
          e.stopPropagation(); 
          api.load();
        }
      });
    }
    
    if( !flowplayer.support.volume && !flowplayer.support.autoplay ) { // iPhone iOS 11 doesn't support setting of volume, but the button it important to allow unmuting of autoplay videos
      root.find('.fp-volume').hide();
    }
    
    // failsafe is Flowplayer is loaded outside of fv_player_load()
    var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
    if( ( !api.conf.playlist || api.conf.playlist.length == 0 ) && playlist.length && playlist.find('a[data-item]').length > 0 ) {  // api.conf.playlist.length necessary for iOS 9 in some setups
      var items = [];      
      playlist.find('a[data-item]').each( function() {
        items.push( fv_player_videos_parse(jQuery(this).attr('data-item'), root) );
      });
      api.conf.playlist = items;
      api.conf.clip = items[0];
    } else if( !api.conf.clip ){
      api.conf.clip = fv_player_videos_parse(jQuery(root).attr('data-item'), root);
    }
    
    if( playlist.parents('.fv-playlist-slider-wrapper').length > 0 ) {
      var items = playlist.find('a');
      playlist.css( 'width', items.outerWidth() * items.length );
    }
    
    //  playlist item click action
    jQuery('a',playlist).click( function(e) {
      e.preventDefault();

      var
        $this = jQuery(this),
        $prev = $this.prev('a');

      if ($prev.length && $this.is(':visible') && !$prev.is(':visible')) {
        $prev.click();
        return false;
      }

      if( jQuery( '#' + $this.parent().attr('rel') ).hasClass('dynamic-playlist') ) return;
      
      var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
      
      fv_player_playlist_active(playlist,this);
      
      if( api ){
        var index = jQuery('a',playlist).index(this);
        if( !api.video || api.video.index == index ) return;
        api.play( index );
      }

      var rect = root[0].getBoundingClientRect();
      if((rect.bottom - 100) < 0){
        jQuery('html, body').animate({
          scrollTop: jQuery(root).offset().top - 100
        }, 300);
      }
    } );
    
    var playlist_external = jQuery('[rel='+root.attr('id')+']');
    var playlist_progress = false;
    
    api.bind('ready', function(e,api,video) {
      //console.log('playlist mark',video.index);
      setTimeout( function() {
        if( video.index > -1 ) {          
          if( playlist_external.length > 0 ) {
            var playlist_item = jQuery('a',playlist_external).eq(video.index);
            fv_player_playlist_active(playlist_external,playlist_item);
            playlist_progress = playlist_item.find('.fvp-progress');
          }
        }        
      }, 250 );
      
      root.find('.fv-fp-splash-text').hide();
    } );
    
    api.bind( 'unload', function() {
      jQuery('.fp-playlist-external .now-playing').remove();
      jQuery('.fp-playlist-external a').removeClass('is-active');
      
      root.find('.fv-fp-splash-text').show();
      playlist_progress = false;
    });
    
    api.bind( 'progress', function() {
      if( playlist_progress && api.video.duration ) {
        var progress = 100*api.video.time/api.video.duration;
        playlist_progress.css('width',progress+'%');
      }
    });
      

    //is this needed?  
    var playlist = jQuery(root).parent().find('div.fp-playlist-vertical[rel='+jQuery(root).attr('id')+']');  
    if( playlist.length ){
      function check_size_and_all(args) {
        var property = playlist.hasClass('fp-playlist-only-captions') ? 'height' : 'max-height';
        if( playlist.parents('.fp-playlist-text-wrapper').hasClass('is-fv-narrow') ){
          property = 'max-height';
        }
        playlist.css(property,vertical_playlist_height());
        if( property == 'max-height' ) playlist.css('height','auto');
      }
      check_size_and_all();
      jQuery(window).on('resize tabsactivate', function() {
        setTimeout( check_size_and_all, 0 );
      } );
    }
    
    function vertical_playlist_height(args) {
      var height = root.height();
      if( height == 0 ) height = root.css('max-height');
      return height;
    }
  });
  
  //sets height for embedded players 
  if( window.self != window.top && !location.href.match(/fv_player_preview/) ){
    embed_size();
    jQuery(window.self).resize(embed_size);
  }
  
  function embed_size() {
    jQuery('.flowplayer.fp-is-embed').each( function() {
      var root = jQuery(this);
      if( !root.hasClass('has-chapters') && !root.hasClass('has-transcript') && jQuery('.fp-playlist-external[rel='+root.attr('id')+']').length == 0 ) {
        root.height(jQuery(window).height());
      }
    });
  }
  
  //  Playlist - old style
  if( typeof(fv_flowplayer_playlists) != "undefined" ) {
    for( var i in fv_flowplayer_playlists ) {
      if( !fv_flowplayer_playlists.hasOwnProperty(i) ) continue;
      jQuery('#'+i).flowplayer( { playlist: fv_flowplayer_playlists[i] });
    }
  }
  
  fv_player_load();
  fv_autoplay_exec();
  
  jQuery(document).ajaxComplete( function() {  
    fv_player_load();
  });
  
  jQuery(window).on('hashchange',fv_autoplay_exec);
}


function fv_player_load() {
  
  jQuery('.flowplayer' ).each( function(i,el) {
    var root = jQuery(el);
    var api = root.data('flowplayer');
    if( api ) return;
    
    if( root.attr('data-item') ) {
      root.flowplayer( { clip: fv_player_videos_parse(root.attr('data-item'), root) });
    } else if( playlist = jQuery( '[rel='+root.attr('id')+']' ) ) {
      if ( playlist.find('a[data-item]').length == 0 ) return;  //  respect old playlist script setup
      
      var items = [];
      playlist.find('a[data-item]').each( function() {
        items.push( fv_player_videos_parse(jQuery(this).attr('data-item'), root) );
      });

      root.flowplayer( { playlist: items } );
    }
  } );
  
  if( typeof(jQuery().tabs) != "undefined" ) {
    jQuery('body').removeClass('fv_flowplayer_tabs_hide');
    jQuery('.fv_flowplayer_tabs_content').tabs();
  }

}


function fv_player_playlist_active(playlist,item) {
  if(playlist) {
    jQuery('a',playlist).removeClass('is-active');
    jQuery('.now-playing').remove();
  }
  
  $playlist = jQuery(playlist);
  $item = jQuery(item);

  var scroll_parent = false;
  
  $item.addClass('is-active');
  var is_design_2014 = $playlist.hasClass('fv-playlist-design-2014');
  if( ( is_design_2014 && $item.find('h4').length == 0 || !is_design_2014 ) && $item.find('.now-playing').length == 0 ) $item.prepend('<strong class="now-playing"><span>'+fv_flowplayer_translations.playlist_current+'</span></strong>');
  
  // adjust playlist to the encompassing DIV, if the actual playlist element itself is wrapped inside
  // another element to enable CSS scrolling
  if (!$playlist.parent().find('.flowplayer').length) {
    scroll_parent = true;
  }
  
  // scroll to the currently playing video if playlist type is vertical or horizontal
  if ( (
        $playlist.hasClass('fp-playlist-vertical') ||
        $playlist.hasClass('fp-playlist-horizontal') && $playlist.hasClass('is-audio') // this combination is also a vertical playlist basically
        ) && !fullyVisibleY($item.get(0)) ) {
    var $el = (scroll_parent ? $playlist.parent() : $playlist);
    $el.animate({
      scrollTop: $el.scrollTop() + ($item.position().top - $el.position().top)
    }, 750);
  
    //$playlist.scrollTop($playlist.scrollTop() + ($item.position().top - $playlist.position().top));
  } else if ($playlist.hasClass('fp-playlist-horizontal') && !fullyVisibleX($item.get(0))) {
    var $el = (scroll_parent ? $playlist.parent() : $playlist);
    $el.animate({
      scrollLeft: $el.scrollLeft() + ($item.position().left - $el.position().left)
    }, 750);
  }
  
  function fullyVisibleY(el) {
    var rect = el.getBoundingClientRect(), top = rect.top, height = rect.height,
      bottom = (top + height), el = el.parentNode;
    do {
      rect = el.getBoundingClientRect();
      if (bottom <= rect.bottom === false) return false;
      if (top <= rect.top) return false;
      el = el.parentNode;
    } while (el != document.body);
    // Check its within the document viewport
    return bottom <= document.documentElement.clientHeight;
  }
  
  function fullyVisibleX(el) {
    var rect = el.getBoundingClientRect(), left = rect.left, width = rect.width,
      right = (left + width), el = el.parentNode;
    do {
      rect = el.getBoundingClientRect();
      if (right <= rect.right === false) return false;
      if (left <= rect.left) return false;
      el = el.parentNode;
    } while (el != document.body);
    // Check its within the document viewport
    return right <= document.documentElement.clientWidth;
  }  
}


jQuery( function() {
  jQuery('.flowplayer').each( function() {
    flowplayer.bean.off(jQuery(this)[0],'contextmenu');
  });
} );

var fv_fp_date = new Date();
var fv_fp_utime = fv_fp_date.getTime();


if( typeof(fv_flowplayer_browser_ff_m4v_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ff_m4v_array ) {
    fv_flowplayer_browser_ff_m4v( i );
  }
}
if( typeof(fv_flowplayer_browser_chrome_fail_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_chrome_fail_array ) {
    fv_flowplayer_browser_chrome_fail( i, fv_flowplayer_browser_chrome_fail_array[i]['attrs'], fv_flowplayer_browser_chrome_fail_array[i]['mp4'], fv_flowplayer_browser_chrome_fail_array[i]['auto_buffer'] );
  }
}

if( typeof(fv_flowplayer_browser_ie_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ie_array ) {
    fv_flowplayer_browser_ie( i );
  }
}
if( typeof(fv_flowplayer_mobile_switch_array) != "undefined" ) {
  for( var i in fv_flowplayer_mobile_switch_array ) {
    fv_flowplayer_mobile_switch( i );
  }
}




/*
 *  Sharing bar, redirect feature, loop, disabling rightclick and obscuring the video URL in errors
 */
flowplayer( function(api,root) {
  root = jQuery(root);
  
  root.find('.fp-logo').removeAttr('href');
  
  if( root.hasClass('no-controlbar') ) {    
    var timelineApi = api.sliders.timeline;
    timelineApi.disable(true);
    api.bind('ready',function() {
      timelineApi.disable(true);
    });
  }
  
  if( root.data('fv_loop') ) {
    api.conf.loop = true;
  }
  
  jQuery('.fvfp_admin_error', root).remove();
  
  root.find('.fp-logo, .fp-header').click( function(e) {
    if (e.target !== this) return;
    root.find('.fp-ui').click();
  });
    
  jQuery('.fvp-share-bar .sharing-facebook',root).append('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="#fff"><title>Facebook</title><path d="M11.9 5.2l-2.6 0 0-1.6c0-0.7 0.3-0.7 0.7-0.7 0.3 0 1.6 0 1.6 0l0-2.9 -2.3 0c-2.6 0-3.3 2-3.3 3.3l0 2 -1.6 0 0 2.9 1.6 0c0 3.6 0 7.8 0 7.8l3.3 0c0 0 0-4.2 0-7.8l2.3 0 0.3-2.9Z"/></svg>');
  jQuery('.fvp-share-bar .sharing-twitter',root).append('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="#fff"><title>Twitter</title><path d="M16 3.1c-0.6 0.3-1.2 0.4-1.9 0.5 0.7-0.4 1.2-1 1.4-1.8 -0.6 0.4-1.3 0.6-2.1 0.8 -0.6-0.6-1.4-1-2.4-1 -2 0.1-3.2 1.6-3.2 4 -2.7-0.1-5.1-1.4-6.7-3.4 -0.9 1.4 0.2 3.8 1 4.4 -0.5 0-1-0.1-1.5-0.4l0 0.1c0 1.6 1.1 2.9 2.6 3.2 -0.7 0.2-1.3 0.1-1.5 0.1 0.4 1.3 1.6 2.2 3 2.3 -1.6 1.7-4.6 1.4-4.8 1.3 1.4 0.9 3.2 1.4 5 1.4 6 0 9.3-5 9.3-9.3 0-0.1 0-0.3 0-0.4 0.6-0.4 1.2-1 1.6-1.7Z"/></svg>');
  jQuery('.fvp-share-bar .sharing-email',root).append('<svg xmlns="http://www.w3.org/2000/svg" height="16" viewBox="0 0 16 16" width="16" fill="#fff"><title>Email</title><path d="M8 10c0 0 0 0-1 0L0 6v7c0 1 0 1 1 1h14c1 0 1 0 1-1V6L9 10C9 10 8 10 8 10zM15 2H1C0 2 0 2 0 3v1l8 4 8-4V3C16 2 16 2 15 2z"/></svg>');
    
  jQuery('.fp-header',root).prepend( jQuery('.fvp-share-bar',root) );
  
  if( api.conf.playlist.length ) {
    var prev = jQuery('<a class="fp-icon fv-fp-prevbtn"></a>');
    var next = jQuery('<a class="fp-icon fv-fp-nextbtn"></a>');
    root.find('.fp-controls .fp-playbtn').before(prev).after(next);
    prev.click( function() {
      api.prev();
    });
    next.click( function() {
      api.next();
    });
  }
  
  api.bind("pause resume finish unload ready", function(e,api) {
    root.addClass('no-brand');
  });
  
  api.one('ready', function() {
    root.find('.fp-fullscreen').clone().appendTo( root.find('.fp-controls') );
  });
  
  api.bind("ready", function (e, api, video) {
    setTimeout( function () {      
      jQuery('.fvp-share-bar',root).show();
      
      jQuery('.fv-player-buttons-wrap',root).appendTo(jQuery('.fv-player-buttons-wrap',root).parent().find('.fp-ui'));
    }, 100 );
  });

  api.bind('finish', function() {
    var url = root.data('fv_redirect');
    if( url && ( typeof(api.video.is_last) == "undefined" || api.video.is_last ) ) {
      location.href = url;
    }
  });
  
  if( flowplayer.support.iOS && flowplayer.support.iOS.version == 11 ) {
    api.bind('error',function(e,api,error){
      if( error.code == 4 ) root.find('.fp-engine').hide();
    });
  }
  
  jQuery(document).on('contextmenu', '.flowplayer', function(e) {
    e.preventDefault();
  });
  
  api.one("ready", function (e, api, video) {
    root.find('.fp-chromecast').insertAfter( root.find('.fp-header .fp-fullscreen') );
  });
  
  // svg color fix
  root.find('.fp-waiting .sq animate').each( function() {
    jQuery(this).attr('values',jQuery(this).attr('values').replace(/0,0,0,.5/g,'255,255,255,0.8'));
    jQuery(this).attr('to',jQuery(this).attr('to').replace(/0,0,0,.5/g,'255,255,255,0.8'))
    jQuery(this).attr('values',jQuery(this).attr('values').replace(/0,0,0,0/g,'0,0,0,0.8'))
    jQuery(this).attr('to',jQuery(this).attr('to').replace(/0,0,0,0/g,'0,0,0,0.8'))
  });
  
  // performance fix as showing too many SVGs at once increases CPU load
  var waiting = root.find('.fp-waiting'),
    svgs = waiting.children();
    
  svgs.remove();
  
  api.bind('load', function() {
    svgs.appendTo(waiting);
  }).bind('unload', function() {
    svgs.remove();
  });
  
  if( !flowplayer.support.fullscreen ) {
    var id = root.attr('id'),
      alternative = !flowplayer.conf.native_fullscreen && flowplayer.conf.mobile_alternative_fullscreen;
    
  	api.bind('fullscreen', function(e,api) {
      jQuery('#wpadminbar, .nc_wrapper').hide();
	  if( alternative ) {
        if( api.video.type == 'video/youtube' ) return;		
        root.before('<span data-fv-placeholder="'+id+'"></span>');
  	    root.appendTo('body');
      }
  	});
    api.bind('fullscreen-exit', function(e,api,video) {
      jQuery('#wpadminbar, .nc_wrapper').show();
      if( alternative ) jQuery('span[data-fv-placeholder='+id+']').replaceWith(root);		
  	});
  }
    
});




/*
 *  IE < 9 - disabling responsiveness
 */
if( jQuery.browser && jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9 ) {
  jQuery('.flowplayer').each( function() {
    jQuery(this).css('width', jQuery(this).css('max-width'));
    jQuery(this).css('height', jQuery(this).css('max-height'));
  } );
}




/*
 *  IE11 - hiding animations
 */
var isIE11 = !!navigator.userAgent.match(/Trident.*rv[ :]*11\./);
if( isIE11 ) {
  jQuery(document).ready( function() {
    jQuery('.fp-waiting').hide();
  } );
  
  flowplayer( function(api,root) {
    api.bind("load", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').show();
    } ).bind("beforeseek", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').show();
    } ).bind("progress", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').hide();
    } ).bind("seek", function (e) {
      jQuery(e.currentTarget).find('.fp-waiting').hide();
    } ).bind("fullscreen", function (e) {
      jQuery('#wpadminbar').hide();
    } ).bind("fullscreen-exit", function (e) {
      jQuery('#wpadminbar').show();
    } );       
  } );
}




/*
 *  Ads
 */
flowplayer(function (api,root) {
  root = jQuery(root);
  var player_id = root.attr('id');
  
  if( root.data('end_popup_preview') ){
    jQuery(document).ready( function() {      
      api.trigger('finish', [ api] );
    });
  }
  
  function show_popup( event ) {
    var popup = root.find('.wpfp_custom_popup');
    if( typeof(fv_flowplayer_popup) != "undefined" && typeof(fv_flowplayer_popup[player_id]) != "undefined" && ( event == 'finish' || fv_flowplayer_popup[player_id].pause || fv_flowplayer_popup[player_id].html.match(/fv-player-ppv-purchase-btn-wrapper/) ) ) {
      root.addClass('is-popup-showing');
      root.find('.fp-player').append( '<div id="'+player_id+'_custom_popup" class="wpfp_custom_popup">'+fv_flowplayer_popup[player_id].html+'</div>' );
    }
  }
  
  api.bind("ready", function (e, api) {  
    if( !root.hasClass('is-cva') && root.find('#'+player_id+'_ad').length == 0 && typeof(fv_flowplayer_ad) != "undefined" && typeof(fv_flowplayer_ad[player_id]) != "undefined" && root.width() >= parseInt(fv_flowplayer_ad[player_id].width) ) {
      var html = fv_flowplayer_ad[player_id].html;
      html = html.replace( '%random%', Math.random() );
      root.find('.fp-player').append( '<div id="'+player_id+'_ad" class="wpfp_custom_ad">'+html+'</div>' );
    }
  }).bind("finish", function (e, api) {
    if( typeof(api.video.index) == "undefined" || api.video.index+1 == api.conf.playlist.length ) {
      show_popup(e.type);
    }
  }).bind("pause", function (e, api) {
    show_popup(e.type); // todo: only if showing on pause is enabled or FV Player PPV
  }).bind("resume unload seek", function (e, api) {
    if( root.hasClass('is-popup-showing') ) {
      root.find('.wpfp_custom_popup').remove();
      root.removeClass('is-popup-showing');
    }
  });
});

/*
 *  Popups form
 */
jQuery(document).on('focus','.fv_player_popup input[type=text], .fv_player_popup input[type=email], .fv_player_popup textarea', function() {
  var api = jQuery(this).parents('.flowplayer').data('flowplayer');
  if( api ) api.disable(true);
});
jQuery(document).on('blur','.fv_player_popup input[type=text], .fv_player_popup input[type=email], .fv_player_popup textarea', function() {
  var api = jQuery(this).parents('.flowplayer').data('flowplayer');
  if( api ) api.disable(false);
});




/*
 *  Live stream errors
 */
flowplayer(function (api, root) {
  api.bind("load", function (e,api,data) {
    var player = jQuery(e.currentTarget);
    if( player.data('live') ){
      var live_check = setTimeout( function() {
        player.find('.fp-ui').append('<div class="fp-message">'+fv_flowplayer_translations.live_stream_failed+'</div>');
        player.addClass('is-error');
      }, 10000 );
      jQuery(e.currentTarget).data('live_check', live_check);
    }
  }).bind("ready", function (e,api,data) {
    clearInterval( jQuery(e.currentTarget).data('live_check') );
  }).bind("error", function (e,api,data) {
    var player = jQuery(e.currentTarget);
    if( player.data('live') ){
      player.find('.fp-message').html(fv_flowplayer_translations.live_stream_failed_2);
    }
  });
});

/*if( /ipad/.test(navigator.userAgent.toLowerCase()) && /os 8/.test(navigator.userAgent.toLowerCase()) ){
  flowplayer(function (api, root) {
    api.bind("resume", function (e,api,data) {
      setTimeout( function() {      
        if( api.loading ) jQuery(e.currentTarget).children('video')[0].play();
      }, 1000 );
    });  
  });
}*/ //  v6




/*
 *  BlackBerry 10 hotfix
 */
jQuery('.flowplayer').on('ready', function(e,api) { //  v6
  if( /BB10/.test(navigator.userAgent) ){
    api.fullscreen();
  }
});




/*
 *  Google Analytics improvements
 */
flowplayer( function(api,root) {
  var root = jQuery(root);
  api.bind('progress', function(e,api,current) {
    fv_track(e,api,current);
    if (!jQuery(root).hasClass("is-cva")){
      if (current > (jQuery(root).data('ad_show_after') )){
        jQuery(root).find(".wpfp_custom_ad_content").fadeIn();
      }
    }
  }).bind('finish ready ', function(e,api) {				
    //if( typeof(aFVPlayersSwitching[root.attr('id')]) != "undefined" ) { //  todo: problem that it won't work on video replay or playlist
      //return;
    //}
    for( var j in fv_ga_events ) {
      root.removeData('fv_track_'+fv_ga_events[j]);
    }
  }).bind('error', function(e,api,error) {
    setTimeout( function() {
      if( !api.error ) return;

      var video = typeof(api.video) != "undefined" && typeof(api.video.src) != "undefined" ? api.video : false;
      if( !video && typeof(api.conf.clip) != "undefined" && typeof(api.conf.clip.sources) != "undefined" && typeof(api.conf.clip.sources[0]) != "undefined" && typeof(api.conf.clip.sources[0].src) != "undefined" ) video = api.conf.clip.sources[0];

      var name = fv_player_track_name(root,video);
      if( name && !name.match(/\/\/vimeo.com\/\d/) ) {
        fv_player_track(api.conf.analytics, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", error.message, name );
      }
    }, 100 );
  });
  
  var fv_ga_events = [ 'start', 'first quartile', 'second quartile', 'third quartile', 'complete' ];
        
  function fv_track(e,api,data) {
      var video = api.video,
        dur = video.duration,
        i = 0;
      
      var name = fv_player_track_name(root,video);
      
      if( dur ) {
        if( data > 19 * dur/20 ) i = 4;
        else if( data > 3 * dur/4 ) i = 3;
        else if( data > dur/2 ) i = 2;
        else if( data > dur/4 ) i = 1;      
      }
            
      if( root.data('fv_track_'+fv_ga_events[i]) ) return;			
          
      for( var j in fv_ga_events ) {  //  make sure user triggered the previous quartiles before tracking
        if(j == i) break;    
        if( !root.data('fv_track_'+fv_ga_events[j]) ) return;      
      }
      
      root.trigger('fv_track_'+fv_ga_events[i]);
      root.data('fv_track_'+fv_ga_events[i], true);
      
      fv_player_track( api.conf.analytics, "Video " + (root.hasClass('is-cva')?'Ad ':'') +  fv_ga_events[i] , api.engine.engineName + "/" + video.type, name );
    }
  
});

//Sends event statistics to Google analytics
function fv_player_track( ga_id, event, engineType, name){
 
  if( !ga_id || typeof( _gat) == 'undefined' ) return;    
  
  var tracker = _gat._getTracker(ga_id);
  if( typeof(tracker._setAllowLinker) == "undefined" ) {
    return;
  }
  
  if( typeof(engineType) == "undefined" ) engineType = 'Unknown engine';
  
  if( /fv_ga_debug/.test(window.location.href) ) console.log('FV GA: ' + event + ' - '   + engineType + " '" + name + "'")
  tracker._setAllowLinker(true);    
  tracker._trackEvent( event, engineType, name, 1 );
  //tracker._trackEvent( "Video "+fv_ga_events[i], api.engine + "/" + video.type, name, 1 );
  
}

function fv_player_track_name(root,video) {
  var name = root.attr("title");
  if( !name && typeof(video.fv_title) != "undefined" ) name = video.fv_title;
  if( !name && typeof(video.src) != "undefined" ) {
    name = video.src.split("/").slice(-1)[0].replace(/\.(\w{3,4})(\?.*)?$/i, '');
    if( video.type.match(/mpegurl/) ) name = video.src.split("/").slice(-2)[0].replace(/\.(\w{3,4})(\?.*)?$/i, '') + '/' + name;
  }
  return name;
}




/*
 *  Speed control
 */
/*!

   Speed menu plugin for Flowplayer HTML5

   Copyright (c) 2017, Flowplayer Drive Oy

   Released under the MIT License:
   http://www.opensource.org/licenses/mit-license.php

   Requires Flowplayer HTML5 version 7.x or greater
   $GIT_DESC$

*/
(function() {
  var extension = function(flowplayer) {
    flowplayer(function(api, root) {
      if( !jQuery(root).data('speedb') ) return;
    
      var support = flowplayer.support;
      if (!support.video || !support.inlineVideo) return;
      var common = flowplayer.common
        , bean = flowplayer.bean
        , ui = common.find('.fp-ui', root)[0]
        , controlbar = common.find('.fp-controls', ui)[0]
        , speeds = api.conf.speeds;

      bean.on(root, 'click', '.fp-speed', function() {
        var menu = common.find('.fp-speed-menu', root)[0];
        if (common.hasClass(menu, 'fp-active')) api.hideMenu();
        else api.showMenu(menu);
      });

      bean.on(root, 'click', '.fp-speed-menu a', function(ev) {
        var s = ev.target.getAttribute('data-speed');
        api.speed(s);
      });

      api.on('speed', function(ev, _a, rate) {
        if (speeds.length > 1) {
          selectSpeed(rate);
        }
      })
      .on('ready', function(ev, api) {
        removeMenu();
        
        if( flowplayer.support.android && ( api.video.type = 'application/x-mpegurl' || api.video.src.match(/m3u8/) ) ) return; 
        
        speeds = api.conf.speeds;
        if (speeds.length > 1) {
          createMenu();
        }
      });

      function removeMenu() {
        common.find('.fp-speed-menu', root).forEach(common.removeNode);
        common.find('.fp-speed', root).forEach(common.removeNode);
      }

      function round(val) {
        return Math.round(val * 100) / 100;
      }

      function createMenu() {
        controlbar.appendChild(common.createElement('strong', { className: 'fp-speed' }, api.currentSpeed + 'x'));
        var menu = common.createElement('div', { className: 'fp-menu fp-speed-menu', css: { width: 'auto' } }, '<strong>Speed</strong>');
        speeds.forEach(function(s) {
          var a = common.createElement('a', { 'data-speed': round(s) }, round(s) + 'x');
          menu.appendChild(a);
        });
        ui.appendChild(menu);
        selectSpeed(api.currentSpeed);
      }

      function selectSpeed(rate) {
        common.find('.fp-speed', root)[0].innerHTML = rate + 'x';
        common.find('.fp-speed-menu a', root).forEach(function(el) {
          common.toggleClass(el, 'fp-selected', el.getAttribute('data-speed') == rate);
          common.toggleClass(el, 'fp-color', el.getAttribute('data-speed') == rate);
        });
      }
    });
  };

  if (typeof module === 'object' && module.exports) module.exports = extension;
  else if (typeof window.flowplayer === 'function') extension(window.flowplayer);
})();




/*
 *  Tabbed playlist
 */
jQuery(document).on("tabsactivate", '.fv_flowplayer_tabs_content', function(event, ui){
  var oldPlayer = jQuery('.flowplayer.is-playing').data('flowplayer');
  if( typeof(oldPlayer) != "undefined" ) {
    oldPlayer.pause();
  }
  
  var objPlayer = jQuery('.flowplayer',ui.newPanel);
  var api = objPlayer.data('flowplayer');
  api.load();  
}); 




/*
 *  Iframe embedding embed code
 */
flowplayer(function(player, root) {
  root = jQuery(root);
  if( typeof(root.data('fv-embed')) == 'undefined' || !root.data('fv-embed') || root.data('fv-embed') == 'false' ) return;

  player.embedCode = function() {    
    var video = player.video;
    var width = root.width();
    var height = root.height();
    height += 2;
    
    // adjust height to show at least some of chapters and transcripts
    if (root.hasClass('has-chapters') || root.hasClass('has-transcript') ) {
      height += 300;
    }
    
    if( jQuery('.fp-playlist-external[rel='+root.attr('id')+']').length > 0 ) {
      height += 150 + 20; // estimate of playlist height + scrollbar height
    }

    return '<iframe src="' + root.data('fv-embed') + '" allowfullscreen  width="' + width + '" height="' + height + '" frameborder="0" style="max-width:100%"></iframe>';
  };
  
});




/*
 *  Visual Composer tabs support
 */
jQuery(document).on('click','.vc_tta-tab a', function() {
  var api = jQuery('.flowplayer.is-playing').data('flowplayer');
  if( api ) api.pause();
});


/* *
 * Anchor Sharing + Playlist Autoplay
 */

//Makes sharable slug
function fv_parse_sharelink(src){
  src = src.replace('https?://[^./].','')
  var prefix = 'fvp_';
  if(src.match(/(youtube.com)/)){
    return prefix + src.match(/(?:v=)([A-Za-z0-9_-]*)/)[1]; 
  }else if(src.match(/(vimeo.com)|(youtu.be)/)){
    return prefix + src.match(/(?:\/)([^/]*$)/)[1];
  }else{
    var match = src.match(/(?:\/)([^/]*$)/);
    if(match){
      return prefix + match[1].match(/^[^.]*/)[0];
    }
  }
  return prefix + src;
}

function fv_player_time_hms(seconds) {

  if(isNaN(seconds)){
    return NaN;
  }

  var date = new Date(null);
  date.setSeconds(seconds); // specify value for SECONDS here
  var timeSrting = date.toISOString().substr(11, 8);
  timeSrting = timeSrting.replace(/([0-9]{2}):([0-9]{2}):([0-9]{2}\.?[0-9]*)/,'$1h$2m$3s').replace(/^00h(00m)?/,'').replace(/^0/,'');
  return timeSrting;
}

function fv_player_time_seconds(time, duration) {

  if(!time)
    return false;

  var seconds = 0;
  var aTime = time.replace(/[hm]/g,':').replace(/s/,'').split(':').reverse();

  if( typeof(aTime[0]) != "undefined" ) seconds += parseFloat(aTime[0]);
  if( typeof(aTime[1]) != "undefined" ) seconds += parseInt(60*aTime[1]);
  if( typeof(aTime[2]) != "undefined" ) seconds += parseInt(60*60*aTime[2]);

  return duration ? Math.min(seconds, duration) : seconds;
}

//Autoplays the video, queues the right video on mobile
function fv_autoplay_init(root, index ,time){
  if( fv_autoplay_exec_in_progress ) return;

  fv_autoplay_exec_in_progress = true;  

  var api = root.data('flowplayer');
  if(!api) return;

  var fTime = fv_player_time_seconds(time);

  if(root.parent().hasClass('ui-tabs-panel')){
    var tabId = root.parent().attr('id');
    jQuery('[aria-controls=' + tabId + '] a').click();
  }

  if( !root.find('.fp-player').attr('class').match(/\bis-sticky/) ){    
    var offset = jQuery(root).offset().top - (jQuery(window).height() - jQuery(root).height()) / 2;    
    window.scrollTo(0,offset);
    api.one('ready',function(){
      window.scrollTo(0,offset);
    });
  }
  if(root.hasClass('lightboxed')){
    setTimeout(function(){
      jQuery('[href=#' + root.attr('id')+ ']').click();
    },0);
  }

  if(index){
    if( fv_autoplay_can(api,parseInt(index)) ) {
      api.play(parseInt(index));
      api.one('ready', function() {
        fv_autoplay_exec_in_progress = false;
        if( fTime ) api.seek(fTime)
      } );    
    } else if( flowplayer.support.inlineVideo ) {
      api.one( api.playing ? 'progress' : 'ready', function (e,api) {
        api.play(parseInt(item));
        api.one('ready', function() {
          fv_autoplay_exec_in_progress = false;
          if( fTime ) api.seek(fTime)
        } );              
      });
      
      fv_player_playlist_active( false, jQuery('[rel='+root.attr('id')+'] a').eq(index) );
      
      root.css('background-image', jQuery('[rel='+root.attr('id')+'] a').eq(index).find('span').css('background-image') );
      
      fv_player_notice( root, fv_flowplayer_translations[11], 'progress' );
    }
  }else{
    if( fv_autoplay_can(api) ) {
      api.load();
    } else {
      fv_player_notice( root, fv_flowplayer_translations[11], 'progress' );
    }
    api.one('ready', function() {
      fv_autoplay_exec_in_progress = false;
      if( fTime ) {
        var do_seek = setInterval( function() {
          if( api.loading ) return;
          api.seek(fTime)
          clearInterval(do_seek);
        }, 10 );
      }
    } );    
  }
  
}

/*
* ANCHORS
* keywords: hashmark hashtag anchor links
* */
if (typeof (flowplayer) !== "undefined" && typeof(fv_flowplayer_conf) != "undefined"  && fv_flowplayer_conf.video_hash_links ) {
  flowplayer(function (api, root) {
    if( jQuery(root).find('.sharing-link').length > 0 ) {
      api.on('progress',function(e,api){
        if( !api.video.sources || !api.video.sources[0] ) {
          return;
        }
        
        var hash = fv_parse_sharelink( typeof(api.video.sources_original) != "undefined" && typeof(api.video.sources_original[0]) != "undefined" ? api.video.sources_original[0].src : api.video.sources[0].src);
        var sTime = ',' + fv_player_time_hms(api.video.time);
        //console.log(sTime);
        jQuery('.fvp-sharing>li>a',root).each(function(){
          jQuery(this).attr('href',jQuery(this).attr('href').replace(/%23.*/,'') + '%23' + hash /*+ sTime*/);
        });
  
        jQuery('.sharing-link',root).attr('href',jQuery('.sharing-link',root).attr('href').replace(/#.*/,'') + '#' + hash + sTime);
      });
      
      jQuery('.sharing-link',root).click( function(e) {

        fv_player_clipboard( jQuery(this).attr('href'), function() {
          e.preventDefault();
          fv_player_notice(root,fv_flowplayer_translations.link_copied,2000);          
        });
      })
    }
  })
  
  jQuery(document).on('click','a[href*="fvp_"]', function() {
    var link = jQuery(this)
    setTimeout( function() {
      if( link.parents('.fvp-share-bar').length == 0 ) fv_autoplay_exec();
    } );
  });

}

var fv_autoplay_exec_in_progress = false;

function fv_autoplay_exec(){
  var autoplay = true;
  //anchor sharing
  if( typeof (flowplayer) !== "undefined" && typeof(fv_flowplayer_conf) != "undefined"  && fv_flowplayer_conf.video_hash_links && window.location.hash.substring(1).length ) {
    var aHash = window.location.hash.match(/\?t=/) ? window.location.hash.substring(1).split('?t=') : window.location.hash.substring(1).split(',');
    var hash = aHash[0];
    var time = aHash[1] === undefined ? false : aHash[1];

    jQuery('.flowplayer').each(function(){
      var root = jQuery(this);
      if(root.hasClass('lightbox-starter')){
        root = jQuery(root.attr('href'));
      }
      var api = root.data('flowplayer');
      if(!api) return;
      
      var playlist = typeof(api.conf.playlist) !== 'undefined' && api.conf.playlist.length > 1 ? api.conf.playlist : [ api.conf.clip ];          
      for( var item in playlist ) {
        var src = fv_parse_sharelink(playlist[item].sources[0].src);
        if(hash === src && autoplay ){
          console.log('fv_autoplay_exec for '+src,item);
          fv_autoplay_init(root, parseInt(item),time);
          autoplay = false;
          return false;
        }
      }

    });
  }

  // If no video is matched by URL hash string, process autoplay
  if( autoplay && flowplayer.support.firstframe ) {
    jQuery('.flowplayer[data-fvautoplay]').each( function() {
      var root = jQuery(this);
      var api = root.data('flowplayer');
      if( !fv_player_did_autoplay && root.data('fvautoplay') ) {
        if( !( ( flowplayer.support.android || flowplayer.support.iOS ) && api && api.conf.clip.sources[0].type == 'video/youtube' ) ) { // don't let these mobile devices autoplay YouTube
          fv_player_did_autoplay = true;
          api.load();
        }
      }
    });
  }
}

function fv_autoplay_can( api, item ) {  
  var video = item ? api.conf.playlist[item] : api.conf.clip;
  
  if( video.sources[0].type == 'video/youtube' && ( flowplayer.support.iOS || flowplayer.support.android ) ) return false;
  
  return flowplayer.support.firstframe;
}




/*
 *  Basic Iframe YouTube and Vimeo responsiveness
 */
(function($) {
  $(window).on('resize',function(){
    var iframe = $('iframe[id][src][height][width]'); 
    iframe.each(function(){
      if( $(this).attr('id').match(/(fv_vimeo_)|(fv_ytplayer_)/) && $(this).width() <= $(this).attr('width') )
        $(this).height( $(this).width() * $(this).attr('height') / $(this).attr('width') );
    })
    
    var wistia = jQuery('.wistia_embed'); 
    wistia.each(function(){      
      $(this).height( $(this).width() * $(this).data('ratio') );
    })
  }).trigger('resize');
})(jQuery);




/*
 *  HLS.js fallback to Flowplayer Flash HLS
 */
flowplayer(function(api, root) {
  var store_engine_pos = -1;
  var store_engine = false;
    
  api.on("error", function (e, api, err) {
    if( err.code != 4 || api.engine.engineName != 'hlsjs' ) return;    
    
    console.log('FV Player: HLSJS failed to play the video, switching to Flash HLS');
    api.error = api.loading = false;
    
    jQuery(root).removeClass('is-error');
    jQuery(flowplayer.engines).each( function(k,v) {
      if( flowplayer.engines[k].engineName == 'hlsjs' ){
        store_engine_pos = k;
        store_engine = flowplayer.engines[k];        
        delete(flowplayer.engines[k]);        
      }
    });
    
    var index = typeof(api.video.index) != "undefined" ? api.video.index : 0;
    var video = index > 0 ? api.conf.playlist[index].sources : api.conf.clip.sources;
    video.index = index;
    
    api.load({ sources: video });
    
    //  without this any further HLS playback won't use HLS.js
    api.bind('unload error', function() {
      flowplayer.engines[store_engine_pos] = store_engine;
    });
  });
});




/*
 *  Gravity Forms Partial Entries fix - the whole player is cloned if it's placed in the form, causing it to play again in the background
 */
flowplayer(function(api, root) {

  api.bind('ready',function() {
    setTimeout( function() {
      var video = jQuery('video',root);
      if( video.length > 0 ) {
        video.removeAttr('autoplay'); //  removing autoplay attribute fixes the issue
      }
    }, 100 ); //  by default the heartbeat JS event triggering this happens every 30 seconds, we just add a bit of delay to be sure
  });

});





flowplayer(function(api, root) {
  /*
   *  Chrome 55>= video download button fix 
   */  
  api.bind('ready', function() {
    if( /Chrome/.test(navigator.userAgent) && parseFloat(/Chrome\/(\d\d)/.exec(navigator.userAgent)[1], 10) > 54 ) {
      if( api.video.subtitles ) {
        jQuery(root).addClass('chrome55fix-subtitles');
      } else {
        jQuery(root).addClass('chrome55fix');
      }
    }
  });
  
  /*
   *  Splash dimension bugfix
   */
  root = jQuery(root);
  var image_src = root.css('background-image')
  if( image_src ) {
    image_src = image_src.replace(/url\((['"])?(.*?)\1\)/gi, '$2').split(',');
    if( !image_src || !image_src[0].match(/^(https?:)?\/\//) ) return;      
    var image = new Image();
    image.src = image_src[0];
    
    var image_ratio = image.height/image.width;
    var player_ratio = root.height()/root.width();
    
    var ratio_diff = Math.abs(player_ratio - image_ratio);
    if( ratio_diff < 0.05 ) {
      root.css('background-size','cover');
    }
    
  }
});

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
      $.post(fv_fp_ajaxurl,data,function( response ) {
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

/* *
 * WARNINGS
 */
if( typeof(flowplayer) != "undefined" ) { //  stop lightbox from playing if it's closed
  flowplayer(function (api,root) {    
    root = jQuery(root);
    
    //  Subtitles which iPhone can't show
    if( navigator.userAgent.match(/iPhone.* OS [0-6]_/i)){
      api.one('progress', function(e) {
        if( typeof(api.video.subtitles) !== 'undefined' && api.video.subtitles.length ){
          fv_player_warning(root,fv_flowplayer_translations.warning_iphone_subs);
        }        
      });         
    }
    
    //  unstable Android
    if( flowplayer.support.android && flowplayer.support.android.version < 5 && ( flowplayer.support.android.samsung || flowplayer.support.browser.safari ) ){
      fv_player_warning(root,fv_flowplayer_translations.warning_unstable_android,'firefox');
    }
    
    //  Vimeo misbehaving on Android 4.4
    if( /Android 4/.test(navigator.userAgent) && !/Firefox/.test(navigator.userAgent) ) {
      api.on('ready', function(e,api,video) { //  works for my Samsung Android 4.4.4, both built-in browser and Chrome
        setTimeout( function() {          
          if( video.src && video.src.match(/fpdl.vimeocdn.com/) && ( video.time == 0 || video.time == 1 ) ) {          
            fv_player_warning(root,fv_flowplayer_translations.warning_unstable_android,'firefox');
            
            api.on('progress', function(e,api) {
              root.prev().find('.fv-player-warning-firefox').remove();
            });          
          }
        }, 1500 );
      });
      
      api.on('error', function(e,api,error) { //  works for Huawei Android 4.3
        if( error.MEDIA_ERR_NETWORK == 2 && error.video.src.match(/fpdl.vimeocdn.com/) ) {          
          fv_player_warning(root,fv_flowplayer_translations.warning_unstable_android,'firefox');
        }        
      });
    }
    
    //  Vimeo misbehaving on old Safari
    if( /Safari/.test(navigator.userAgent) && /Version\/5/.test(navigator.userAgent) ) {
      api.on('error', function(e,api,error) {
        if( error.video.src.match(/fpdl.vimeocdn.com/) ) {          
          fv_player_warning(root,fv_flowplayer_translations.warning_old_safari);
        }        
      });
    }
    
    var sup = flowplayer.support;
    if( sup.android && (      
      sup.android.samsung && parseInt(sup.browser.version) < 66 || // Samsung Browser is just old version of Google Chrome!
      sup.browser.safari // and in some cases it's detected as Safari
      )
    ) {
      api.on('error', function(e,api,error) {     
        fv_player_warning(root,fv_flowplayer_translations.warning_samsungbrowser,'warning_samsungbrowser');      
      });
    }        
    
    
  });
  
  
  function fv_player_warning(root,warning,classname) {
    var wrapper = jQuery(root).prev('.fv-player-warning-wrapper');
    if( wrapper.length == 0 ) {
      jQuery(root).before('<div class="fv-player-warning-wrapper">');
      wrapper = jQuery(root).prev('.fv-player-warning-wrapper');
    }
    
    if( wrapper.find('.fv-player-warning-'+classname).length == 0 ) {
      var latest = jQuery("<p style='display: none' "+(classname?" class='fv-player-warning-"+classname+"'" : "")+">"+warning+"</p>");
      wrapper.append(latest);
      latest.slideDown();
    }
  }
}


/*
 * Player size dependent classes
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var player = root.find('.fp-player'),
    had_no_volume = root.hasClass('no-volume'),
    had_fp_mute = root.hasClass('fp-mute');

  function check_size() {
    var width = player.width() || root.width();
    if(width > 900) {
      jQuery('.fp-subtitle',root).addClass('is-wide');
    } else {
      jQuery('.fp-subtitle',root).removeClass('is-wide');
    }
    
    // core Flowplayer classes which are normally added in requestAnimationFrame, which increases CPU load too much
    root.toggleClass('is-tiny', width < 400);
    root.toggleClass('is-small', width < 600 && width >= 400 );

    var el = player;
    if( root.parent().hasClass('fp-playlist-vertical-wrapper') || root.parent().hasClass('fp-playlist-text-wrapper') ) el = root.parent(); // in some cases we use the wrapper

    if(el.width() <= 560) {
      el.addClass('is-fv-narrow');
    } else {
      el.removeClass('is-fv-narrow');
    }
    
    if(width <= 320) { // remove volue bar on narrow players
      root.addClass('no-volume fp-mute');
    } else {
      if( !had_no_volume ) root.removeClass('no-volume');
      if( !had_fp_mute ) root.removeClass('fp-mute');
    }
    
    if( !root.hasClass('is-audio') ) {
      var speed = root.find('.fp-speed-menu'); // speed menu should get scrollbar when needed    
      speed.toggleClass('wont-fit', ++speed.children().length * 25 > player.height() );
    }
  }
  
  check_size();
  
  jQuery(window).on('resize',check_size);

  api.on('ready fullscreen fullscreen-exit',check_size);

  api.on('unload pause finish error',function(){
    if(typeof(checker) !== 'undefined')
      clearInterval(checker);
  })
})


jQuery(window).on('resize tabsactivate',function(){
  jQuery('.fp-playlist-external').each(function(){
    var playlist = jQuery(this);
    if( playlist.parent().width() >= 900 ) playlist.addClass('is-wide');
    else playlist.removeClass('is-wide');
  })
}).trigger('resize');



/*
 *  Audio support
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var bean = flowplayer.bean;
  
  if( root.hasClass('is-audio') ) {
    bean.off(root[0], "mouseenter");
    bean.off(root[0], "mouseleave");
    root.removeClass('is-mouseout');
    root.addClass('fixed-controls').addClass('is-mouseover');
    
    api.on('error', function (e,api, error) {    
      jQuery('.fp-message',root).html( jQuery('.fp-message',root).html().replace(/video/,'audio') );
    });
    
    root.click( function(e) {
      if( !api.ready) {
        e.preventDefault();
        e.stopPropagation();
        api.load();
      }
    })
  }
  
})




/*
 *  Player notices
 */
function fv_player_notice(root, message, timeout) {
  var notices = jQuery('.fvfp-notices',root);
  if( !notices.length ) {
    notices = jQuery('<div class="fvfp-notices">');    
    jQuery('.fp-player',root).append(notices);
  }
  
  var notice = jQuery('<div class="fvfp-notice-content">'+message+'</div></div>');  
  notices.append(notice);
  if ( typeof(timeout) == 'string' ) {
    var player = jQuery(root).data('flowplayer');
    player.on(timeout, function() {
      notice.fadeOut(100,function() { jQuery(this).remove(); });
    } );
  }
  if ( timeout > 0 ) {
    setTimeout( function() {
      notice.fadeOut(2000,function() { jQuery(this).remove(); });
    }, timeout );
  }
}




var fv_player_clipboard = function(text, successCallback, errorCallback) {
  try {
    fv_player_doCopy(text);
    successCallback();
  } catch (e) {
    if( typeof(errorCallback) != "undefined" ) errorCallback(e);
  }
};

function fv_player_doCopy(text) {
  var textarea = document.createElement('textarea');
  textarea.value = text;
  textarea.style.opacity = 0;
  textarea.style.position = 'absolute';
  textarea.setAttribute('readonly', true);
  document.body.appendChild(textarea);

  // Check if there is any content selected previously.
  var selected = document.getSelection().rangeCount > 0 ?
    document.getSelection().getRangeAt(0) : false;

  // iOS Safari blocks programmtic execCommand copying normally, without this hack.
  // https://stackoverflow.com/questions/34045777/copy-to-clipboard-using-javascript-in-ios
  if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
    var editable = textarea.contentEditable;
    textarea.contentEditable = true;
    var range = document.createRange();
    range.selectNodeContents(textarea);
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    textarea.setSelectionRange(0, 999999);
    textarea.contentEditable = editable;
  } else {
    textarea.select();
  }

  try {
    var result = document.execCommand('copy');

    // Restore previous selection.
    if (selected) {
      document.getSelection().removeAllRanges();
      document.getSelection().addRange(selected);
    }

    document.body.removeChild(textarea);

    return result;
  } catch (err) {
    throw new Error('Unsuccessfull');
  }
}




/*
 *  Custom keyboard controls, todo: fp7 check!
 */
flowplayer.bean.off(document,'keydown.fp');

flowplayer(function(api, root) {
  var bean = flowplayer.bean;  

  // no keyboard configured
  if (!api.conf.keyboard) return;
  
  //  todo: is help really gone?
  /*var help = jQuery(root).find('.fp-help').html();
  var playlist_help = api.conf.playlist.length > 0 ? '<p><em>shift</em> + <em>n</em><em>p</em>next / prev video</p>' : '';
  help = help.replace(/<p><em>1.*?60% <\/p>/,playlist_help);
  jQuery(root).find('.fp-help').html(help);*/
  
  // hover
  bean.on(root, "mouseenter mouseleave", function(e) {
    fv_player_focused = !api.disabled && e.type == 'mouseover' ? api : 0;
    if (fv_player_focused) fv_player_focusedRoot = root;
  });
  
  api.bind('ready', function(e,api,video) {
    if( video.subtitles && video.subtitles.length > 0 ) {
      var help = jQuery(root).find('.fp-help').html();
      help += '<div class="fp-help-section fp-help-subtitles"><p><em>c</em>cycle through subtitles</p></div>';
      jQuery(root).find('.fp-help').html(help);
    } else {
      jQuery(root).find('.fp-help-subtitles').remove();
    }
  });
});

flowplayer.bean.on(document, "keydown.fp", function(e) {
  if( typeof(fv_player_focused) == "undefined" ) return

  var api = fv_player_focused,
    focusedRoot = api ? fv_player_focusedRoot : false,
    common = flowplayer.common;
  
  var el = api && !api.disabled ? api : 0,
    metaKeyPressed = e.ctrlKey || e.metaKey || e.altKey,
    key = e.which,
    conf = el && el.conf;
    
  
  
  if (!el || !conf.keyboard || el.disabled) return;
  
  // help dialog (shift key not truly required)
  if ([63, 187, 191].indexOf(key) != -1) {
    common.toggleClass(focusedRoot, "is-help");
    return false;
  }
  
  // close help / unload
  if (key == 27 && common.hasClass(focusedRoot, "is-help")) {
    common.toggleClass(focusedRoot, "is-help");
    return false;
  }
  
  if (!metaKeyPressed && el.ready) {
  
  e.preventDefault();
  
  // slow motion / fast forward
  if (e.shiftKey) {
    if (key == 39) el.speed(true);
    else if (key == 37) el.speed(false);
    else if (key == 78) el.next();  //  N
    else if (key == 80) el.prev();  //  P    
    return;
  }
  
  // 1, 2, 3, 4 ..
  if (key < 58 && key > 47) return el.seekTo(key - 48);
  
  
  
  switch (key) {
    case 38: case 75: el.volume(el.volumeLevel + 0.15); break;
    case 40: case 74: el.volume(el.volumeLevel - 0.15); break;
    case 39: case 76: el.seeking = true; el.seek(api.video.time+5); break;
    case 37: case 72: el.seeking = true; el.seek(api.video.time-5); break;
    case 190: el.seekTo(); break;
    case 32: el.toggle(); break;
    case 70: if(conf.fullscreen) el.fullscreen(); break;
    case 77: el.mute(); break;
    case 81: el.unload(); break;
    case 67:  //  circle through subtitles
      if( !api.video.subtitles || api.video.subtitles.length == 0 ) break;
      
      var current_subtitles = jQuery(focusedRoot).find('.fp-dropdown li.active[data-subtitle-index]').data('subtitle-index');
      if( typeof(current_subtitles) == "undefined" ) current_subtitles = -1;
      
      current_subtitles++;
      if( current_subtitles > (api.video.subtitles.length - 1) ) {
        current_subtitles = -1;
      }
      
      api.trigger('fv-subtitles-switched');
      
      if( current_subtitles > -1 ) {
        el.loadSubtitles(current_subtitles);
        fv_player_notice(focusedRoot,fv_flowplayer_translations.subtitles_switched+' '+api.video.subtitles[current_subtitles].label,'fv-subtitles-switched');          
      } else {
        el.disableSubtitles();
        fv_player_notice(focusedRoot,fv_flowplayer_translations.subtitles_disabled,'fv-subtitles-switched');          
      }
      
      break;
  }
  
  }

});




flowplayer(function(api, root) {
  root = jQuery(root);
  
  if( root.hasClass('is-audio') ) return;
  
  if( root.data('fsforce') == false ) return;
  
  var force = 1 || flowplayer.conf.mobile_force_fullscreen && ( 'ontouchstart' in window ) && flowplayer.support.fvmobile; // todo: setting
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
  if( !force && !playlist.hasClass('fp-playlist-season') ) return;
  
  if( flowplayer.support.iOS.iPad || flowplayer.support.iOS.iPhone && flowplayer.support.iOS.version >= 10 ) {
    api.bind('ready', function() {
      api.fullscreen();
    });
    
  } else {
    root.on('click', function() {
      if( !api.isFullscreen ) api.fullscreen();
    });
    
    jQuery('[rel='+root.attr('id')+'] a').on('click', function(e) {        
      if( !api.isFullscreen ) {
        api.fullscreen();
        api.resume();
      }
    });
  }
  
  api.on('fullscreen-exit', function(a,api) {
    api.pause();      
  });
});




/*
 *  MPEG-DASH and HLS.js ABR changes
 */
if( localStorage.FVPlayerHLSQuality && typeof(flowplayer.conf.hlsjs.autoLevelEnabled) == "undefined" ) {
  flowplayer.conf.hlsjs.startLevel = localStorage.FVPlayerHLSQuality;
}

flowplayer( function(api,root) {
  var hlsjs;
  flowplayer.engine('hlsjs-lite').plugin(function(params) {
    hlsjs = params.hls;
  });
  
  root = jQuery(root);
  var search = document.location.search;

  if( localStorage.FVPlayerDashQuality ) {
    if( !api.conf.dash ) api.conf.dash = {};
    api.conf.dash.initialVideoQuality = 'restore'; // special flag for Dash.js
  }
  
  if( localStorage.FVPlayerHLSQuality && typeof(flowplayer.conf.hlsjs.autoLevelEnabled) == "undefined" ) {
    flowplayer.conf.hlsjs.startLevel = localStorage.FVPlayerHLSQuality;
  }
  
  api.bind('quality', function(e,api,quality) {
    if(api.engine.engineName == 'dash' ) {      
      if( quality == -1 ) {
        localStorage.removeItem('FVPlayerDashQuality');
      } else if( bitrates[quality] ) {
        localStorage.FVPlayerDashQuality = bitrates[quality].height;
      }
    } else if(api.engine.engineName == 'hlsjs-lite' ) {      
      if( quality == -1 ) {
        localStorage.removeItem('FVPlayerHLSQuality');
      } else {
        localStorage.FVPlayerHLSQuality = quality;
      }
    }
  });  

  var bitrates = [];
  var last_quality = -1;
  api.bind('ready', function(e,api) {
    if(api.engine.engineName == 'dash' ) {      
      bitrates = api.engine.dash.getBitrateInfoListFor('video');      
      if( localStorage.FVPlayerDashQuality && api.conf.dash.initialVideoQuality ) { // Dash.js gives us initialVideoQuality 
        api.quality(api.conf.dash.initialVideoQuality);
        root.one('progress', function() { // we need to make sure Flowplayer Dash.js setInitialVideoQuality won't enable the ABR again
          setTimeout( function() {
            api.quality(api.conf.dash.initialVideoQuality);
          });
        });
      }
      quality_sort();
    } else if(api.engine.engineName == 'hlsjs-lite' ) {
      if( localStorage.FVPlayerHLSQuality && api.video.qualities > 2 ) {
        api.quality(localStorage.FVPlayerHLSQuality);
        root.one('progress', function() {
          setTimeout( function() {
            api.quality(localStorage.FVPlayerHLSQuality);
          });
        });
      }
      quality_sort();
    } else if( api.video.sources_fvqs && api.video.sources_fvqs.length > 0 && api.video.src.match(/vimeo.*?\.mp4/) ) {
      setTimeout( quality_sort, 0 );      
    }    
    root.find('a[data-quality]').removeClass('is-current');
  });

  if( search.match(/dash_debug/) || search.match(/hls_debug/) ) var debug_log = jQuery('<div class="fv-debug" style="background: gray; color: white; top: 10%; position: absolute; z-index: 1000">').appendTo(root.find('.fp-player'));
  
  api.bind('ready progress', quality_process);
  
  api.bind('quality', function() {
    setTimeout( quality_process, 0 );
  });
  
  function quality_process() {
    if( api.engine.engineName == 'dash' ) {
      var stream_info = bitrates[api.engine.dash.getQualityFor('video')];
      if( stream_info.qualityIndex != last_quality ) {
        last_quality = stream_info.qualityIndex;
        var low = 100000;
        for( var i in bitrates ) {
          if( bitrates[i].height < low ) low = bitrates[i].height;
        }
        quality_label(stream_info.qualityIndex,stream_info.height,low);
      }
      if( search.match(/dash_debug/) ) quality_debug(stream_info.width,stream_info.height,stream_info.bitrate);      
      
    } else if( api.engine.engineName == 'hlsjs-lite' ) {
      if( hlsjs.currentLevel != last_quality ) {
        last_quality = hlsjs.currentLevel;
        var low = 100000;
        for( var i in hlsjs.levels ) {
          if( hlsjs.levels[i].height < low ) low = hlsjs.levels[i].height;
        }
        quality_label( hlsjs.currentLevel, hlsjs.levels[hlsjs.currentLevel].height, low );
      }
      
      if( search.match(/hls_debug/) ) {
        var level = hlsjs.levels[hlsjs.currentLevel];
        quality_debug(level.width,level.height,level.bitrate);
      }      
      
    }
  }
  
  function quality_label(index,height,low) {
    root.find('a[data-quality]').removeClass('is-current');
    root.find('a[data-quality='+index+']').addClass('is-current');    
    var label = 'M';
    if( height >= 360 && low < height ) label = 'SD';
    if( height > 540 ) label = 'HD';
    if( height >= 1400 ) label = '4K';        
    root.find('.fp-qsel').html(label);    
  }
  
  function quality_debug(w,h,br) {
    debug_log.html( "Using "+w+"x"+h+" at "+Math.round(br/1024)+" kbps" );
  }
  
  function quality_sort() {
    var menu = root.find('.fp-qsel-menu');
    menu.children().each(function(i,li){menu.prepend(li)})
    menu.children().each(function(i,li){ jQuery(li).html(jQuery(li).html().replace(/\(.*?\)/,'')) })
    menu.prepend(menu.find('a[data-quality=-1]'));
    menu.prepend(menu.find('strong'));
  }

});


flowplayer( function(api,root) {
  root = jQuery(root);
  
  if( !root.data('button-no-picture') && !root.data('button-repeat') ) return;
  
  api.bind('ready', function(e,api) {
    if( !api.video.type.match(/^audio/) && root.data('button-no-picture') && root.find('.fv-fp-no-picture').length == 0 ) {
      var button_no_picture = jQuery('<span class="fv-fp-no-picture"><svg viewBox="0 0 90 80" width="20px" height="20px" class="fvp-icon fvp-nopicture"><use xlink:href="#fvp-nopicture"></use></svg></strspanong>');
      
      button_no_picture.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        jQuery('.fp-engine',root).slideToggle(20);
        jQuery(this).toggleClass('is-active fp-color-fill');
      });    
    }
    
    if( root.data('button-repeat') ) {
      if( api.conf.playlist.length > 0 && root.find('.fv-fp-playlist').length == 0 ) {
        var playlist_button = jQuery('<strong class="fv-fp-playlist mode-normal"><svg viewBox="0 0 80.333 80" width="20px" height="20px" class="fvp-icon fvp-replay-list"><title>Replay Playlist</title><use xlink:href="#fvp-replay-list"></use></svg><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-shuffle"><use xlink:href="#fvp-shuffle"></use></svg><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-replay-track"><title>Replay Track</title><use xlink:href="#fvp-replay-track"></use></svg><span id="fvp-playlist-play" title="Play All">All</span></strong>'),
            playlist_menu = jQuery('<div class="fp-menu fv-fp-playlist-menu"><a data-action="repeat_playlist"><svg viewBox="0 0 80.333 80" width="20px" height="20px" class="fvp-icon fvp-replay-list"><title>Replay Playlist</title><use xlink:href="#fvp-replay-list"></use></svg> <span class="screen-reader-text">Repeat Playlist</span></a><a data-action="shuffle_playlist"><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-shuffle"><title>Shuffle Playlist</title><use xlink:href="#fvp-shuffle"></use></svg> <span class="screen-reader-text">Shuffle Playlist</span></a><a data-action="repeat_track"><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-replay-track"><title>Repeat Track</title><use xlink:href="#fvp-replay-track"></use></svg> <span class="screen-reader-text">Repeat Track</span></a><a class="fp-selected" data-action="normal"><span id="fvp-playlist-play" title="Play All">All</span></a></div>').insertAfter( root.find('.fp-controls') );
            
        api.conf.playlist_shuffle = api.conf.track_repeat = false;
          
        var random_seed = randomize();
        
        var should_advance = api.conf.advance;

        playlist_button.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
          e.preventDefault();
          e.stopPropagation();

          // reposition the repeat menu to be aligned with the repeat button
          if (playlist_menu.css('right') !== 'auto') {
            playlist_menu.css({
              "right": "auto",
              "left": playlist_button.position().left + 'px'
            });
          }

          if( playlist_menu.hasClass('fp-active') ) {
            api.hideMenu(playlist_menu[0]);
          }
          else {
            // workaround for flowplayer 7 not picking up our menu as one of its own,
            // thus not closing it
            root.click();
            api.showMenu(playlist_menu[0]);
          }
        });
        
        jQuery('a',playlist_menu).click( function() {
          jQuery(this).siblings('a').removeClass('fp-selected');
          jQuery(this).addClass('fp-selected');
          playlist_button.removeClass('mode-normal mode-repeat-track mode-repeat-playlist mode-shuffle-playlist');
          
          var action = jQuery(this).data('action');
          if( action == 'repeat_playlist' ) {
            playlist_button.addClass('mode-repeat-playlist');
            api.conf.loop = true;
            api.conf.advance = true;
            api.video.loop = api.conf.track_repeat = false;
            api.conf.playlist_shuffle = false;
          
          } else if( action == 'shuffle_playlist' ) {
            playlist_button.addClass('mode-shuffle-playlist');
            api.conf.loop = true;
            api.conf.advance = true;
            api.conf.playlist_shuffle = true;          
          
          } else if( action == 'repeat_track' ) {
            playlist_button.addClass('mode-repeat-track');
            api.conf.track_repeat = api.video.loop = true;
            api.conf.loop = api.conf.playlist_shuffle = false;
            //api.conf.advance = !track_repeat && should_advance;
          
          } else if( action == 'normal' ) {
            playlist_button.addClass('mode-normal');
            api.conf.track_repeat = api.video.loop = false;
            api.conf.loop = api.conf.playlist_shuffle = false;
          
          }
          
        });
        
        if( api.conf.loop ) {
          jQuery('a[data-action=repeat_playlist]', playlist_menu ).click();
        }
        
        api.on('progress', function() {
          api.video.loop = api.conf.track_repeat;        
        });
        
        api.on("finish.pl", function(e,api) {console.log('playlist_repeat',api.conf.loop,'advance',api.conf.advance,'video.loop',api.video.loop);
          if( api.conf.playlist_shuffle ) {
            api.play( random_seed.pop() );
            if( random_seed.length == 0 ) random_seed = randomize();
          }
        });      
        
      } else if( root.find('.fv-fp-track-repeat').length == 0 && api.conf.playlist.length == 0 ) {
        var button_track_repeat = jQuery('<strong class="fv-fp-track-repeat"><svg viewBox="0 0 80.333 71" width="20px" height="20px" class="fvp-icon fvp-replay-track"><use xlink:href="#fvp-replay-track"></use></svg></strong>');
        button_track_repeat.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          if( api.video.loop ) {
            api.video.loop = false;
            jQuery(this).removeClass('is-active fp-color-fill');
          } else {
            api.video.loop = true;
            jQuery(this).addClass('is-active fp-color-fill');
          }
        });
        
        if( api.conf.loop ) {
          button_track_repeat.click();
        }
        
      }
    }
  }).bind('unload', function() {
    root.find('.fv-fp-no-picture').remove();
    root.find('.fv-fp-playlist').remove();
    root.find('.fv-fp-track-repeat').remove();
  });
  
  function array_shuffle(a) {
    var j, x, i;
    for (i = a.length; i; i--) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
    }
    return a;
  }
  
  function randomize(random_seed) {
    random_seed = [];
    jQuery(api.conf.playlist).each( function(k,v) {
      random_seed.push(k);
    });      

    random_seed = array_shuffle(random_seed);
    console.log('FV Player Randomizer random seed:',random_seed);
    return random_seed;
  }  
});


 // sticky video
flowplayer(function(api, root) {
  var $root = jQuery(root);
  var $playerDiv = $root.find('.fp-player');
  var sticky = $root.data("fvsticky");
  var globalSticky = false;
  var videoRatio = $root.data("ratio");
  if (typeof(videoRatio) == "undefined") {
    videoRatio = 0.5625;
  }
  if (flowplayer.conf.sticky_video == 1 && typeof(sticky) == "undefined") {
    globalSticky = true;
  }
  if (globalSticky || sticky) {
    if (flowplayer.support.firstframe) {
      var stickyPlace = flowplayer.conf.sticky_place;
      var stickyWidth = flowplayer.conf.sticky_width;
      if (stickyWidth == "") {
        stickyWidth = 380;
      }
      var stickyHeight = stickyWidth * videoRatio;
      fv_player_sticky_video();
    } else {
      return;
    }
  }

  function fv_player_sticky_video() {
    var change = false;
    var $window = jQuery(window),
      $flowplayerDiv = $root,
      top = $flowplayerDiv.offset().top,
      offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2));
    api.on('ready', function() {
      change = true;
    });
    api.on('progress', function() {
         change = true;
     });
    api.on('unload', function() {
      change = false;
      fv_player_sticky_class_remove();
      $root.removeClass("is-unSticky");
    });

     $window
      .on("resize", function() {
        top = $flowplayerDiv.offset().top;
        offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2));
      })
      .on("scroll", function() {
        top = $flowplayerDiv.offset().top;
        offset = Math.floor(top + ($flowplayerDiv.outerHeight() / 2)); 
        if ($window.scrollTop() > offset && change) {
          if (jQuery("div.flowplayer.is-unSticky").length > 0) {
            console.log('unSticky', jQuery("div.flowplayer.is-unSticky").length);
            return false;
          } else {
            fv_player_sticky_class_add();
          }
        } else {
          fv_player_sticky_class_remove();
          change = false;
        }
      });
  }

  function fv_player_sticky_class_add() {
    if ($playerDiv.hasClass("is-sticky-" + stickyPlace)) {
      return;
    } else {
      $playerDiv.addClass("is-sticky-" + stickyPlace);
      if ($root.find("a.fp-sticky").length == 0){
        $root.find('div.fp-header').prepend('<a class="fp-sticky fp-icon"></a>');
      }
      $playerDiv.css("width", stickyWidth);
      $playerDiv.css("height", stickyHeight);
      $playerDiv.css("max-height", stickyHeight);
    }
    $playerDiv.parent(".flowplayer").addClass("is-stickable");
  }

  function fv_player_sticky_class_remove() {
    $playerDiv.removeClass("is-sticky-" + stickyPlace);
    $playerDiv.css("width", "");
    $playerDiv.css("height", "");
    $playerDiv.css("max-height", "");
    $playerDiv.parent(".flowplayer").removeClass("is-stickable");
  }
});

jQuery(function($) {
  $(document).on('click', "a.fp-sticky", function() {
    $("div.flowplayer.is-stickable").addClass("is-unSticky");
    var $playerDiv = $("div.flowplayer.is-stickable").find('.fp-player');
    $playerDiv.removeClass("is-sticky-right-bottom");
    $playerDiv.removeClass("is-sticky-left-bottom");
    $playerDiv.removeClass("is-sticky-right-top");
    $playerDiv.removeClass("is-sticky-left-top");
    $playerDiv.css("width", "");
    $playerDiv.css("height", "");
    $playerDiv.css("max-height", "");
  });
  $(document).on('click', "div.flowplayer.is-unSticky", function() {
    $("div.flowplayer").removeClass("is-unSticky");
  });
});


//  Magnific Popup suppport
jQuery(document).on('mfpClose', function() {
  if( typeof(jQuery('.flowplayer').data('flowplayer')) != "undefined" ) jQuery('.flowplayer').data('flowplayer').unload();
} );


/*
 *  Video Position Store functionality
 */
flowplayer( function(api,root) {
  var
    $root = jQuery(root),    
    progressEventsCount = 0,
    // number of events to pass before we auto-send current video positions
    sendPositionsEvery = 60,
    // the actual AJAX object we use to send progress data, so we can cancel it in case it's still running    
    ajaxCall = null,
    // maximum cookie size with saved video positions we should store
    maxCookieSize = 2500,    
    localStorageEnabled = null,    
    cookieKeyName = 'video_positions',
        
    // retrieves the original source of a video
    getOriginalSource = function(video) {
      // logged-in users will have position stored within that video
      return (
        (typeof(video.sources_original) != "undefined" && typeof(video.sources_original[0]) != "undefined") ?
          video.sources_original[0] :
          video.sources[0]
      );
    },

    // calculates a cookie byte size
    getTextByteSize = function(txt) {
      return encodeURIComponent(txt).length;
    },

    getCookieKey = function(key) {
      return (localStorageEnabled ? localStorage.getItem(key) : Cookies.get(key));
    },

    setCookieKey = function(key, value) {
      return (localStorageEnabled ? localStorage.setItem(key, value) : Cookies.set(key, value));
    },

    // stores currently played/paused/stopped video position
    storeVideoPosition = function (e, api) {
      if (api.video.sources) {
        if (typeof(flowplayer['playPositions']) == 'undefined') {
          flowplayer['playPositions'] = [];
        }
        if (typeof(flowplayer['sawVideo']) == 'undefined') {
          flowplayer['sawVideo'] = [];
        }

        var
          originalVideoApiPath = getOriginalSource(api.video),
          position = Math.round(api.video.time);

        flowplayer['playPositions'][originalVideoApiPath.src] = position;

        // store the new position in the instance itself as well
        if (originalVideoApiPath.position) {
          originalVideoApiPath.position = position;
        }

        // make a call home every +-30 seconds to make sure a browser crash doesn't affect the position save too much
        if (progressEventsCount++ >= sendPositionsEvery) {
          if (ajaxCall) {
            ajaxCall.abort();
          }
          
          ajaxCall = sendVideoPositions(true, function () {
            ajaxCall = null;
          });
          
          progressEventsCount = 0;
        }
      }
    },

    // used when video unloads and another video starts playing
    forceSavePosition = function (e, api) {
      var inPlaylist = false;

      for (var i in api.conf.playlist) {
        inPlaylist = true;
        break;
      }

      if (inPlaylist && !flowplayer.conf.closingPage) {
        progressEventsCount = sendPositionsEvery + 1;
        storeVideoPosition(e, api);
        sendVideoPositions();
      }
    },

    // called when the video finishes playing - removes that video position from cache, as it's no longer needed
    removeVideoPosition = function (e, api) {
      if (api.video.sources) {
        if (typeof(flowplayer['playPositions']) == 'undefined') {
          flowplayer['playPositions'] = [];
        }
        if (typeof(flowplayer['sawVideo']) == 'undefined') {
          flowplayer['sawVideo'] = [];
        }

        var originalVideoApiPath = getOriginalSource(api.video);
        flowplayer['playPositions'][originalVideoApiPath.src] = 0;
        flowplayer['sawVideo'][originalVideoApiPath.src] = 1;
      }
    },

    // used to seek into the desired last stored position when he video has started
    seekIntoPosition = function (e, api) {
      var
        originalVideoApiPath = getOriginalSource(api.video),
        position = originalVideoApiPath.position;

      api.bind('progress', storeVideoPosition);
      
      if (position) {
        seek(position);
      } else {
        // try to lookup position of a guest visitor
        if (flowplayer.conf.is_logged_in != '1') {
          var data = getCookieKey(cookieKeyName);
          if (data && typeof(data) !== 'undefined') {
            try {
              data = JSON.parse(data);
              if (data[originalVideoApiPath.src]) {
                seek(data[originalVideoApiPath.src]);
              }
            } catch (e) {
              // something went wrong...
              // TODO: shall we try to reset guest data here?
              return;
            }
          }
        }
      }
    },

    sendVideoPositions = function(async, callback) {
      if (async !== true) {
        async = false;
      }

      if (!callback || typeof(callback) == 'undefined') {
        callback = function() {};
      }

      postData = [];

      for (var video_name in flowplayer['playPositions']) {
        postData.push({
          name: video_name,
          position: flowplayer['playPositions'][video_name],
          saw: typeof(flowplayer['sawVideo'][video_name]) != "undefined" ? flowplayer['sawVideo'][video_name] : false,
        });
      }

      if (!postData.length) {
        return;
      }
      
      if ( flowplayer.conf.is_logged_in == '1') {
        // logged-in user, store position in their metadata on server
        return jQuery.ajax({
          type: 'POST',
          async: async,
          url: fv_fp_ajaxurl,
          complete: callback,
          data: {
            action: 'fv_wp_flowplayer_video_position_save',
            videoTimes: postData
          }
        });
      } else {
        // guest visitor, store position in a cookie / localStorage
        try {
          var data = getCookieKey(cookieKeyName);
          if (data && typeof(data) !== 'undefined') {
            data = JSON.parse(data);
          } else {
            data = {};
          }

          // add / edit our video positions
          for (var i in postData) {
            data[postData[i].name] = postData[i].position;
          }
          
          var
            serialized = JSON.stringify(data),
            dataSize = getTextByteSize(serialized);

          // check if we're not going over maximum cache size
          if (dataSize > maxCookieSize) {
            // we're over max cache size, let's delete some older videos
            while (dataSize > maxCookieSize) {
              // remove the first entry only
              for (var i in data) {
                delete data[i];

                // re-serialize with the value removed
                serialized = JSON.stringify(data);
                // calculate new data size, so we can exit the while loop
                dataSize = getTextByteSize(serialized);

                break;
              }
            }
          }
          
          setCookieKey(cookieKeyName, serialized);
        } catch (e) {
          // JSON JS support missing
          return;
        }
      }
      
      return false;
    },
    
  seek = function(position) {
    var seek_count = 0;
    var do_seek = setInterval( function() {
      if( ++seek_count > 20 ) clearInterval(do_seek);
      if( api.loading ) return;            
      api.seek(parseInt(position)); // int for Dash.js!
      clearInterval(do_seek);
    }, 10 );
  };
  
  var enabled = flowplayer.conf.video_position_save_enable || $root.data('save-position');
  if( $root.data('save-position') == false ) enabled = false;
    
  if( !enabled ) return;

  // stop events  
  api.bind('finish', removeVideoPosition);

  // seek into the last saved position, it also hooks the progress event
  if( flowplayer.support.fvmobile ) {
    api.one( 'progress', seekIntoPosition);
  } else {
    api.bind( 'ready', seekIntoPosition);
  }

  // TODO: find out what event can be used to force saving of playlist video positions on video change
  //api.bind('finish', forceSavePosition);
  api.one('progress', function() {
    jQuery(window).on('beforeunload', function () {
      flowplayer.conf.closingPage = true;
      sendVideoPositions();
    });
  });

  // check whether local storage is enabled
  if (localStorageEnabled !== null) {
    return localStorageEnabled;
  }

  localStorageEnabled = true;
  try {
    localStorage.setItem('t', 't');
    if (localStorage.getItem('t') !== 't') {
      localStorageEnabled = false;
    }
    localStorage.removeItem('t');
  } catch (e) {
    localStorageEnabled = false;
  }
  
});

// HSL engine on iOS and on Safari doesn't report error for HTTP 403. If there is no progress event for 5 second and it's not loading or anything, we can assume that the HLS segment has failed to load
flowplayer( function(api,root) {  
  if( !flowplayer.support.browser.safari && !flowplayer.support.iOS ) return;
  
  root = jQuery(root);
  
  var no_progress = false,
    time_start = 0,
    time_delay = 0;
  
  api.on('load', function(e,api,video) {
    time_start = new Date().getTime();
  });
  
  api.on('ready', function() {
    root.find('video').on( "stalled", function(e) {} ); // could be helpful, but just using this event alone is not enough: https://github.com/flowplayer/flowplayer/issues/1403
    
    if( api.engine.engineName == 'html5' ) {
      
      time_delay = new Date().getTime() - time_start;
      
      console.log('Video took '+time_delay+' ms to start');
      
      if( time_delay < 500 ) time_delay = 500;      
      time_delay = 10 * time_delay;
      if( time_delay > 15000 ) time_delay = 15000;
      
      no_progress = setTimeout( hls_check, time_delay );
      
      api.on('progress', function(e,api,time) {
        clearInterval(no_progress);
        no_progress = setTimeout( hls_check, time_delay );
      });
    }
  });
  
  function hls_check() {    
    if( api.ready && api.playing && !api.loading && !api.finished ) {
      clearInterval(no_progress);
      console.log('Video stale for '+time_delay+' ms, triggering error!');      
      fv_player_notice(root,fv_flowplayer_translations.video_reload+' <a class="fv-player-reload" href="#">&#x21bb;</a>','progress error unload');
      jQuery('.fv-player-reload').click( function() {
        api.trigger('error', [api, { code: 4, video: api.video }]);
        return false;
      });
    }
  }
});

flowplayer( function(api,root) {

  var
    $root = jQuery(root),
    start_index = $root.data('playlist_start');

  if( typeof(start_index) == 'undefined' ) return; 

  function start_position_changer() {  
    if ($root.data('position_changed') !== 1 && api.conf.playlist.length) {      
      start_index--; // the index should start from 0
      api.play(start_index);
      $root.data('position_changed', 1);
    }
  }

  api.bind('unload', function() {
    start_index = $root.data('playlist_start');
    $root.removeData('position_changed');
    api.one('ready', start_position_changer);
    api.video.index = 0;
  });

  api.one('ready', start_position_changer);

  jQuery(".fp-ui", root).on('click', function() {
    start_position_changer();
    $root.data('position_changed', 1);
  });

});

// Playlist in controlbar for the "Season" playlist style
flowplayer( function(api,root) {
  root = jQuery(root);
  
  if( api.conf.playlist.length == 0 ) return;
  
  var playlist = jQuery('.fp-playlist-external[rel='+root.attr('id')+']');
  //if( !playlist.hasClass('fp-playlist-season') ) return; // todo: what about mobile? Should we always allow this?
  
  var playlist_button = jQuery('<strong class="fv-fp-list">Item 1.</strong>'),
    playlist_menu = jQuery('<div class="fp-menu fv-fp-list-menu"></div>').insertAfter( root.find('.fp-controls') );
  
  jQuery(api.conf.playlist).each( function(k,v) {
    
    playlist_menu.append('<a data-index="'+k+'">'+(k+1)+'. '+playlist.find('h4').eq(k).text()+'</a>');
  });
  
  playlist_button.insertAfter( root.find('.fp-controls .fp-volume') ).click( function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if( playlist_menu.hasClass('fp-active') ) {
      api.hideMenu(playlist_menu[0]);
    }
    else {
      // workaround for flowplayer 7 not picking up our menu as one of its own,
      // thus not closing it
      root.click();
      api.showMenu(playlist_menu[0]);
    }
  });
  
  jQuery('a',playlist_menu).click( function() {
    api.play(jQuery(this).data('index'));
  });
  
  api.on('ready', function(e,api,video) {
    playlist_menu.find('a').removeClass('fp-selected');
    playlist_menu.find('a[data-index='+video.index+']').addClass('fp-selected');
    playlist_button.html('Item '+(video.index+1)+'.');
  });
  
});
