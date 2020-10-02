
( function() {
	var filter = document.createElement('div');
	filter.innerHTML = '<svg class="fp-filters" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0"><defs><filter id="f1" x="-20%" y="-20%" width="200%" height="200%"><feOffset result="offOut" in="SourceAlpha" dx="0" dy="0" /><feColorMatrix result="matrixOut" in="offOut" type="matrix" values="0.3 0 0 0 0 0 0.3 0 0 0 0 0 0.3 0 0 0 0 0 0.4 0" /><feGaussianBlur result="blurOut" in="matrixOut" stdDeviation="4" /><feBlend in="SourceGraphic" in2="blurOut" mode="normal" /></filter></defs></svg>';
	filter.style.width = 0;
	filter.style.height = 0;
	filter.style.overflow = 'hidden';
	filter.style.position = 'absolute',
	filter.style.margin = 0;
	filter.style.padding = 0;
	document.body.appendChild(filter);

	Array.prototype.filter.call(document.getElementsByClassName('flowplayer'), function(player){
		player.className = player.className.replace(/\bno-svg\b/g,'');

		var preload = player.querySelector('.fp-preload'),
			parent = preload.parentNode,
			play_icon = document.createElement('div');
			play_icon.className = 'fp-play fp-visible';

		play_icon.innerHTML = '<a class="fp-icon fp-playbtn"></a>\
	<svg class="fp-play-rounded-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.a{fill:#000;opacity:0.65;}.b{fill:#fff;opacity:1.0;}</style></defs><title>play-rounded-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><path class="b" d="M35.942,35.2323c0-4.7289,3.3506-6.6637,7.446-4.2971L68.83,45.6235c4.0956,2.364,4.0956,6.2319,0,8.5977L43.388,68.91c-4.0954,2.364-7.446.43-7.446-4.2979Z" filter="url(#f1)"/></svg>\
	<svg class="fp-play-rounded-outline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 99.844 99.8434"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-rounded-outline</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><path class="controlbutton" d="M41.0359,71.19a5.0492,5.0492,0,0,1-2.5575-.6673c-1.8031-1.041-2.7958-3.1248-2.7958-5.8664V35.1887c0-2.7429.9933-4.8272,2.797-5.8676,1.8025-1.0422,4.1034-.86,6.48.5143L70.4782,44.5672c2.3751,1.3711,3.6826,3.2725,3.6832,5.3545s-1.3076,3.9845-3.6832,5.3562L44.9592,70.0114A7.9384,7.9384,0,0,1,41.0359,71.19Zm.0065-40.123a2.6794,2.6794,0,0,0-1.3582.3413c-1.0263.5926-1.5912,1.9349-1.5912,3.78V64.6563c0,1.8449.5649,3.1866,1.5906,3.7791,1.0281.5932,2.4733.4108,4.07-.512L69.273,53.1906c1.5983-.9227,2.478-2.0838,2.478-3.2689s-.88-2.3445-2.478-3.2666L43.754,31.9227A5.5685,5.5685,0,0,0,41.0423,31.0671Z" filter="url(#f1)"/></svg>\
	<svg class="fp-play-sharp-fill" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><style>.fp-color-play{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-fill</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><polygon class="controlbutton" points="73.601 50 37.968 70.573 37.968 29.427 73.601 50" filter="url(#f1)"/></svg>\
	<svg class="fp-play-sharp-outline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 99.844 99.8434"><defs><style>.controlbuttonbg{opacity:0.65;}.controlbutton{fill:#fff;}</style></defs><title>play-sharp-outline</title><path class="fp-color-play" d="M49.9217-.078a50,50,0,1,0,50,50A50.0564,50.0564,0,0,0,49.9217-.078Z"/><path class="controlbutton" d="M36.9443,72.2473V27.2916L75.8776,49.77Zm2.2-41.1455V68.4371L71.4776,49.77Z" filter="url(#f1)"/></svg>'.replace(/url\(#/g, 'url(' + window.location.href.replace(window.location.hash, "").replace(/\#$/g, '') + '#');
		
		parent.replaceChild(play_icon,preload);
		
		var admin_js_warning = player.querySelector('.fvfp_admin_error');
		admin_js_warning.parentNode.removeChild( admin_js_warning );
	});

})();


class FV_Player_JS_Loader_Compatibility_Checker {

	constructor( options ) {
		this.passiveSupported = false;

		this._checkPassiveOption( this );
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
	_checkPassiveOption( self ) {
		try {
			const options = {
				// This function will be called when the browser attempts to access the passive property.
				get passive() {
					self.passiveSupported = true;
					return false;
				}
			};

			window.addEventListener( 'test', null, options );
			window.removeEventListener( 'test', null, options );
		} catch ( err ) {
			self.passiveSupported = false;
		}
	}

}

class FV_Player_JS_Loader {

	constructor( triggerEvents, browser ) {
		this.attrName = 'data-fv-player-loader-src';
		this.browser = browser;
		this.options = this.browser.options;
		this.triggerEvents = triggerEvents;
		this.userEventListener = this.triggerListener.bind( this );
	}

	/**
	 * Initializes the LazyLoad Scripts handler.
	 */
	init() {
		this._addEventListener( this );
	}

	/**
	 * Resets the handler.
	 */
	reset() {
		this._removeEventListener( this );
	}

	/**
	 * Adds a listener for each of the configured user interactivity event type. When an even is triggered, it invokes
	 * the triggerListener() method.
	 *
	 * @private
	 *
	 * @param self Instance of this object.
	 */
	_addEventListener( self ) {
		this.triggerEvents.forEach(
			eventName => window.addEventListener( eventName, self.userEventListener, self.options )
		);
	}

	/**
	 * Removes the listener for each of the configured user interactivity event type.
	 *
	 * @private
	 *
	 * @param self Instance of this object.
	 */
	_removeEventListener( self ) {
		this.triggerEvents.forEach(
			eventName => window.removeEventListener( eventName, self.userEventListener, self.options )
		);
	}

	/**
	 * Loads the script's src from the data attribute, which will then trigger the browser to request and
	 * load the script.
	 */
	_loadScriptSrc() {
		const scripts = document.querySelectorAll( `script[${ this.attrName }]` );
		scripts.forEach( elem => {
			const scriptSrc = elem.getAttribute( this.attrName );

			elem.setAttribute( 'src', scriptSrc );
			elem.removeAttribute( this.attrName );
		} );

		this.reset();
	}

	/**
	 * Window event listener - when triggered, invokes the load script src handler and then resets.
	 */
	triggerListener() {
		this._loadScriptSrc();
		this._removeEventListener( this );
	}

	static run() {
		const browser = new FV_Player_JS_Loader_Compatibility_Checker( { passive: true } );
		const instance = new FV_Player_JS_Loader( ['keydown','mouseover','touchmove','touchstart' ], browser );
		instance.init();
	}
}

FV_Player_JS_Loader.run();