"use strict";

/* Warning: only use /* comments here! */
(function () {
  /* forEach for IE 11: https://rimdev.io/foreach-for-ie-11/ */
  if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
  }

  if (window.HTMLCollection && !HTMLCollection.prototype.forEach) {
    HTMLCollection.prototype.forEach = Array.prototype.forEach;
  }

  var filter = document.createElement('div');
  filter.innerHTML = '<svg class="fp-filters" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 0 0"><defs><filter id="f1" x="-20%" y="-20%" width="200%" height="200%"><feOffset result="offOut" in="SourceAlpha" dx="0" dy="0" /><feColorMatrix result="matrixOut" in="offOut" type="matrix" values="0.3 0 0 0 0 0 0.3 0 0 0 0 0 0.3 0 0 0 0 0 0.4 0" /><feGaussianBlur result="blurOut" in="matrixOut" stdDeviation="4" /><feBlend in="SourceGraphic" in2="blurOut" mode="normal" /></filter></defs></svg>';
  filter.style.width = 0;
  filter.style.height = 0;
  filter.style.overflow = 'hidden';
  filter.style.position = 'absolute', filter.style.margin = 0;
  filter.style.padding = 0;
  document.body.appendChild(filter);
  Array.prototype.filter.call(document.getElementsByClassName('flowplayer'), function (player) {
    player.className = player.className.replace(/\bno-svg\b/g, '');
    /* remove admin JavaScript warning */

    var admin_js_warning = player.querySelector('.fvfp_admin_error');

    if (admin_js_warning) {
      admin_js_warning.parentNode.removeChild(admin_js_warning);
    }

    /* remove preload animation if it's there - not there for audio player */
    var preload = player.querySelector('.fp-preload');
    if( preload ) {
      preload.style.display = 'none';
    }
  });
})();

var FV_Player_JS_Loader_Compatibility_Checker = /*#__PURE__*/function () {
  function FV_Player_JS_Loader_Compatibility_Checker(options) {
    this.passiveSupported = false;

    this._checkPassiveOption(this);

    this.options = this.passiveSupported ? options : false;
  }
  /**
   * Initializes browser check for addEventListener passive option.
   *
   * @link https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener#Safely_detecting_option_support
   * @private
   *
   * @param self Instance of this object.
   * @returns {boolean}
   */


  var _proto = FV_Player_JS_Loader_Compatibility_Checker.prototype;

  _proto._checkPassiveOption = function _checkPassiveOption(self) {
    try {
      var options = {
        /* This function will be called when the browser attempts to access the passive property. */
        get passive() {
          self.passiveSupported = true;
          return false;
        }

      };
      window.addEventListener('test', null, options);
      window.removeEventListener('test', null, options);
    } catch (err) {
      self.passiveSupported = false;
    }
  };

  return FV_Player_JS_Loader_Compatibility_Checker;
}();

var FV_Player_JS_Loader = /*#__PURE__*/function () {
  function FV_Player_JS_Loader(triggerEvents, browser) {
    this.attrName = 'data-fv-player-loader-src';
    this.browser = browser;
    this.options = this.browser.options;
    this.triggerEvents = triggerEvents;
    this.userEventListener = this.triggerListener.bind(this);
  }
  /**
   * Initializes the LazyLoad Scripts handler.
   */


  var _proto2 = FV_Player_JS_Loader.prototype;

  _proto2.init = function init() {
    this._addEventListener(this);
  }
  /**
   * Resets the handler.
   */
  ;

  _proto2.reset = function reset() {
    this._removeEventListener(this);
  }
  /**
   * Adds a listener for each of the configured user interactivity event type. When an even is triggered, it invokes
   * the triggerListener() method.
   *
   * @private
   *
   * @param self Instance of this object.
   */
  ;

  _proto2._addEventListener = function _addEventListener(self) {
    this.triggerEvents.forEach(function (eventName) {
      return window.addEventListener(eventName, self.userEventListener, self.options);
    });
  }
  /**
   * Removes the listener for each of the configured user interactivity event type.
   *
   * @private
   *
   * @param self Instance of this object.
   */
  ;

  _proto2._removeEventListener = function _removeEventListener(self) {
    this.triggerEvents.forEach(function (eventName) {
      return window.removeEventListener(eventName, self.userEventListener, self.options);
    });
  }
  /**
   * Loads the script's src from the data attribute, which will then trigger the browser to request and
   * load the script.
   */
  ;

  _proto2._loadScriptSrc = function _loadScriptSrc() {
    var _this = this;

    var scripts = document.querySelectorAll("script[" + this.attrName + "]");
    window.FV_Player_JS_Loader_scripts_total = 0;
    window.FV_Player_JS_Loader_scripts_loaded = 0;
    scripts.forEach(function (elem) {
      var scriptSrc = elem.getAttribute(_this.attrName);
      elem.setAttribute('src', scriptSrc);
      elem.removeAttribute(_this.attrName);
      window.FV_Player_JS_Loader_scripts_total++;

      elem.onload = function () {
        window.FV_Player_JS_Loader_scripts_loaded++;
      };
    });
    this.reset();
  }
  /**
   * Window event listener - when triggered, invokes the load script src handler and then resets.
   */
  ;

  _proto2.triggerListener = function triggerListener() {
    /* Show the preload indicator once again */
    Array.prototype.filter.call(document.getElementsByClassName('flowplayer'), function (player) {
      if( player.getAttribute('data-error') ) {
        return;
      }

      var preload = player.querySelector('.fp-preload');

      if (preload) {
        preload.style.display = 'block';
      }
    });
    /* Not sure when, but sometimes the flowplayer script is not ready */

    if (window.flowplayer) {
      this._loadScriptSrc();
    } else {
      var that = this,
          wait_for_flowplayer = setInterval(function () {
        if (window.flowplayer) {
          that._loadScriptSrc();

          clearInterval(wait_for_flowplayer);
        }
      }, 100);
    }

    this._removeEventListener(this);
  };

  FV_Player_JS_Loader.run = function run() {
    var browser = new FV_Player_JS_Loader_Compatibility_Checker({
      passive: true
    });
    var instance = new FV_Player_JS_Loader(['keydown', 'mouseover', 'touchmove', 'touchstart', 'wheel'], browser);
    instance.init();
    /* if using Video Link, load it all right away */

    if (location.hash.match(/fvp_/)) {
      instance.triggerListener();
      return;
    }
    /* iOS specific block https://stackoverflow.com/questions/9038625/detect-if-device-is-ios */


    function iOS() {
      return navigator.platform.match(/iPad|iPhone|iPod/)
      /* iPad on iOS 13 detection */
      || navigator.userAgent.indexOf("Mac") !== -1 && "ontouchend" in document;
    }

    if (iOS()) {
      var load_if_any_player_visible = function load_if_any_player_visible() {
        var is_any_player_visible = false;
        /* if part of any player visible? */

        /* TODO: What about playlist item thumbs? */

        document.querySelectorAll('.flowplayer').forEach(function (el) {
          var rect = el.getBoundingClientRect();

          if (rect.top >= -el.offsetHeight && rect.left >= -el.offsetWidth && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + el.offsetHeight && rect.right <= (window.innerWidth || document.documentElement.clientWidth) + el.offsetWidth) {
            is_any_player_visible = true;
          }
        });
        console.log('FV Player: Visible?', is_any_player_visible);

        if (is_any_player_visible) {
          instance.triggerListener();
        }

        return is_any_player_visible;
      };
      /* Load FV Player scripts instantly if any player is visible */


      var was_visible = load_if_any_player_visible();
      /* Try again once styles are loaded */

      if (!was_visible) {
        /* ...or when Safari restores the scroll position */
        var load_on_scroll = function load_on_scroll() {
          this.removeEventListener('scroll', load_on_scroll);
          load_if_any_player_visible();
        };

        /* once everything is loaded */
        window.addEventListener('load', load_if_any_player_visible);
        window.addEventListener('scroll', load_on_scroll);
      }

      return;
    }
    /* If the first click was on player, play it */


    var first_click_done = false;
    document.addEventListener('mousedown', function (e) {
      if (first_click_done) return;
      first_click_done = true;
      var playlist_item = false;
      var path = e.path || e.composedPath && e.composedPath();
      path.forEach(function (el) {
        /* store playlist item for later use */
        if (el.getAttribute && el.getAttribute('data-item')) {
          playlist_item = el;
        }

        if (el.className && el.className.match(/\b(flowplayer|fp-playlist-external)\b/)) {
          /* Players with autoplay should stop */
          document.querySelectorAll('[data-fvautoplay]').forEach(function (player) {
            player.removeAttribute('data-fvautoplay');
          });
          /* VAST should not autoplay */

          if (window.fv_vast_conf) {
            window.fv_vast_conf.autoplay = false;
          }
          /* TODO: Perhaps video link should not be parsed or it should be done here */

          /* was it lightbox? */


          if (el.className.match(/lightbox-starter/)) {
            /* was it playlist thumb? */
          } else if (el.className.match(/\bfp-playlist-external\b/)) {
            console.log('First click on playlist');
            var player = document.getElementById(el.getAttribute('rel'));
            player.setAttribute('data-fvautoplay', Array.prototype.indexOf.call(el.children, playlist_item));
          } else {
            console.log('First click on player');
            el.setAttribute('data-fvautoplay', 0);
          }
        }
      });
    }, false);
  };

  return FV_Player_JS_Loader;
}();

FV_Player_JS_Loader.run();