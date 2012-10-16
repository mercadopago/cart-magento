/*!
 * Chico UI v0.11
 * http://chico-ui.com.ar/
 *
 * Copyright (c) 2012, MercadoLibre.com
 * Released under the MIT license.
 * http://chico-ui.com.ar/license
 *
 * Team: Hernan Mammana, Leandro Linares, Guillermo Paz.
 */

;(function ($) {

/**
* ch is the namespace for Chico-UI.
* @namespace ch
* @name ch
* @static
*/

var ch = window.ch = {

	/**
	* Current version
	* @name version
	* @type number
	* @memberOf ch
	*/
	version: "0.11",
	/**
	* Here you will find a map of all component's instances created by Chico-UI.
	* @name instances
	* @type object
	* @memberOf ch
	*/
	instances: {},
	/**
	* Available device's features.
	* @name features
	* @type object
	* @see ch.Support
	* @memberOf ch
	*/
	features: {},
	/**
	* Core constructor function.
	* @name init
	* @function
	* @memberOf ch
	*/
	init: function() { 
		// unmark the no-js flag on html tag
		$("html").removeClass("no-js");
		// check for browser support
		ch.features = ch.support();
		// TODO: This should be on keyboard controller.
		ch.utils.document.bind("keydown", function(event){ ch.keyboard(event); });
	},
	/**
	* References and commons functions.
	* @name Utils
	* @class Utils
	* @type object
	* @memberOf ch
	*/
	utils: {
		body: $("body"),
		html: $("html"),
		window: $(window),
		document: $(document),
		zIndex: 1000,
		index: 0, // global instantiation index
		isTag: function(string){
			return (/<([\w:]+)/).test(string);
		},
		isSelector: function (selector) {
			if (typeof selector !== "string") return false;
			for (var regex in $.expr.match){
				if ($.expr.match[ regex ].test(selector) && !ch.utils.isTag(selector)) {
					return true;
				};
			};
			return false;
		},
		inDom: function (selector, context) {
			if (typeof selector !== "string") return false;
			// jQuery: If you wish to use any of the meta-characters ( such as !"#$%&'()*+,./:;<=>?@[\]^`{|}~ ) as a literal part of a name, you must escape the character with two backslashes: \\.
			var selector = selector.replace(/(\!|\"|\$|\%|\&|\'|\(|\)|\*|\+|\,|\/|\;|\<|\=|\>|\?|\@|\[|\\|\]|\^|\`|\{|\||\}|\~)/gi, function (str, $1) {
				return "\\\\" + $1;
			});
			return $(selector, context).length > 0;
		},
		/**
		* Checks if the parameter given is an Array.
		* @name isArray
		* @public
		* @param o The member to be checked
		* @function
		* @memberOf ch.Utils
		* @returns boolean
		*/
		isArray: (function () {

			if (Array.hasOwnProperty("isArray")) {
				return Array.isArray;
			}

			return function (o) {
				return Object.prototype.toString.apply(o) === "[object Array]";
			};
		}()),
		/**
		* Checks if the url given is right to load content.
		* @name isUrl
		* @public
		* @function
		* @memberOf ch.Utils
		* @returns boolean
		*/
		isUrl: function (url) {
		/* 
		# RegExp

		https://github.com/mercadolibre/chico/issues/579#issuecomment-5206670

		```javascript
		1	1.1						   1.2	 1.3  1.4		1.5		  1.6					2					   3 			   4					5
		/^(((https|http|ftp|file):\/\/)|www\.|\.\/|(\.\.\/)+|(\/{1,2})|(\d{1,3}\.){3}\d{1,3})(((\w+|-)(\.?)(\/?))+)(\:\d{1,5}){0,1}(((\w+|-)(\.?)(\/?))+)((\?)(\w+=(\w?)+(&?))+)?$/
		```

		## Description
		1. Checks for the start of the URL
			1. if starts with a protocols followed by :// Example: file://chico
			2. if start with www followed by . (dot) Example: www.chico
			3. if starts with ./ 
			4. if starts with ../ and can repeat one or more times
			5. if start with double slash // Example: //chico.server
			6. if start with an ip address
		2. Checks the domain
		  letters, dash followed by a dot or by a slash. All this group can repeat one or more times
		3. Ports
		 Zero or one time
		4. Idem to point two
		5. QueryString pairs

		## Allowed URLs
		1. http://www.mercadolibre.com
		2. http://mercadolibre.com/
		3. http://mercadolibre.com:8080?hola=
		4. http://mercadolibre.com/pepe
		5. http://localhost:2020
		6. http://192.168.1.1
		7. http://192.168.1.1:9090
		8. www.mercadolibre.com
		9. /mercadolibre
		10. /mercadolibre/mercado
		11. /tooltip?siteId=MLA&categId=1744&buyingMode=buy_it_now&listingTypeId=bronze
		12. ./pepe
		13. ../../mercado/
		14. www.mercadolibre.com?siteId=MLA&categId=1744&buyingMode=buy_it_now&listingTypeId=bronze
		15. www.mercado-libre.com
		16. http://ui.ml.com:8080/ajax.html

		## Forbiden URLs
		1. http://
		2. http://www&
		3. http://hola=
		4. /../../mercado/
		5. /mercado/../pepe
		6. mercadolibre.com
		7. mercado/mercado
		8. localhost:8080/mercadolibre
		9. pepe/../pepe.html
		10. /pepe/../pepe.html
		11. 192.168.1.1
		12. localhost:8080/pepe
		13. localhost:80-80
		14. www.mercadolibre.com?siteId=MLA&categId=1744&buyi ngMode=buy_it_now&listingTypeId=bronze
		15. `<asd src="www.mercadolibre.com">`
		16. Mercadolibre.................
		17. /laksjdlkasjd../
		18. /..pepe..
		19. /pepe..
		20. pepe:/
		21. /:pepe
		22. dadadas.pepe
		23. qdasdasda
		24. http://ui.ml.com:8080:8080/ajax.html
		*/
			return ((/^(((https|http|ftp|file):\/\/)|www\.|\.\/|(\.\.\/)+|(\/{1,2})|(\d{1,3}\.){3}\d{1,3})(((\w+|-)(\.?)(\/?))+)(\:\d{1,5}){0,1}(((\w+|-)(\.?)(\/?)(#?))+)((\?)(\w+=(\w?)+(&?))+)?(\w+#\w+)?$/).test(url));
		},
		avoidTextSelection: function () {
			$.each(arguments, function(i, e){
				if ( $.browser.msie ) {
					$(e).attr('unselectable', 'on');
				} else if ($.browser.opera) {
					$(e).bind("mousedown", function(){ return false; });
				} else { 
					$(e).addClass("ch-user-no-select");
				};
			});
			return;
		},
		hasOwn: (function () {
			var hOP = Object.prototype.hasOwnProperty;

			return function (o, property) {
				return hOP.call(o, property);
			};
		}()),
		// Based on: http://www.quirksmode.org/dom/getstyles.html
		getStyles: function (element, style) {
			// Main browsers
			if (window.getComputedStyle) {

				return getComputedStyle(element, "").getPropertyValue(style);

			// IE
			} else {

				// Turn style name into camel notation
				style = style.replace(/\-(\w)/g, function (str, $1) { return $1.toUpperCase(); });

				return element.currentStyle[style];

			}
		},
		// Grab the vendor prefix of the current browser
		// Based on: http://lea.verou.me/2009/02/find-the-vendor-prefix-of-the-current-browser/
		"VENDOR_PREFIX": (function () {
			
			var regex = /^(Webkit|Khtml|Moz|ms|O)(?=[A-Z])/,
			
				styleDeclaration = document.getElementsByTagName("script")[0].style;

			for (var prop in styleDeclaration) {
				if (regex.test(prop)) {
					return prop.match(regex)[0].toLowerCase();
				}
			}
			
			// Nothing found so far? Webkit does not enumerate over the CSS properties of the style object.
			// However (prop in style) returns the correct value, so we'll have to test for
			// the precence of a specific property
			if ("WebkitOpacity" in styleDeclaration) { return "webkit"; }
			if ("KhtmlOpacity" in styleDeclaration) { return "khtml"; }

			return "";
		}())
	}
};
/**
* Chico UI global events reference.
* @name Events
* @class Events
* @memberOf ch
* @static
*/	
ch.events = {
	/**
	* Layout event collection.
	* @name LAYOUT
	* @public
	* @static
	* @constant
	* @type object
	* @memberOf ch.Events
	*/
	LAYOUT: {
		/**
		* Every time Chico-UI needs to inform al visual components that layout has been changed, he triggers this event.
		* @name CHANGE
		* @memberOf ch.Events.LAYOUT
		* @public
		* @type string
		* @constant
		* @see ch.Form
		* @see ch.Layer
		* @see ch.Tooltip
		* @see ch.Helper 
		*/
		CHANGE: "change"
	},
	/**
	* Viewport event collection.
	* @name VIEWPORT
	* @public
	* @static
	* @constant
	* @type object
	* @memberOf ch.Events
	*/
	VIEWPORT: {
		/**
		* Every time Chico-UI needs to inform all visual components that window has been scrolled or resized, he triggers this event.
		* @name CHANGE
		* @constant
		* @memberOf ch.Events.VIEWPORT
		* @see ch.Positioner
		*/
		CHANGE: "change"
	},
	/**
	* Keryboard event collection.
	* @name KEY
	* @public
	* @static
	* @constant
	* @type object
	* @memberOf ch.Events
	*/
	KEY: {
		/**
		* Enter key event.
		* @name ENTER
		* @constant
		* @memberOf ch.Events.KEY
		*/
		ENTER: "enter",
		/**
		* Esc key event.
		* @name ESC
		* @constant
		* @memberOf ch.Events.KEY
		*/
		ESC: "esc",
		/**
		* Left arrow key event.
		* @name LEFT_ARROW
		* @constant
		* @memberOf ch.Events.KEY
		*/
		LEFT_ARROW: "left_arrow",
		/**
		* Up arrow key event.
		* @name UP_ARROW
		* @constant
		* @memberOf ch.Events.KEY
		*/
		UP_ARROW: "up_arrow",
		/**
		* Rigth arrow key event.
		* @name RIGHT_ARROW
		* @constant
		* @memberOf ch.Events.KEY
		*/
		RIGHT_ARROW: "right_arrow",
		/**
		* Down arrow key event.
		* @name DOWN_ARROW
		* @constant
		* @memberOf ch.Events.KEY
		*/
		DOWN_ARROW: "down_arrow",
		/**
		* Backspace key event.
		* @name BACKSPACE
		* @constant
		* @memberOf ch.Events.KEY
		*/
		BACKSPACE: "backspace"
	}
};

/**
* Keyboard event controller utility to know wich keys are begin.
* @name Keyboard
* @class Keyboard
* @memberOf ch
* @param event
*/
ch.keyboard = (function () {

	/**
	* Map with references to key codes.
	* @private
	* @name ch.Keyboard#codeMap
	* @type object
	*/ 
	var codeMap = {
		"13": "ENTER",
		"27": "ESC",
		"37": "LEFT_ARROW",
		"38": "UP_ARROW",
		"39": "RIGHT_ARROW",
		"40": "DOWN_ARROW",
		 "8": "BACKSPACE"
	};

	return function (event) {

		// Check for event existency on the map
		if(!ch.utils.hasOwn(codeMap, event.keyCode)) { return; }

		// Trigger custom event with original event as second parameter
		ch.utils.document.trigger(ch.events.KEY[codeMap[event.keyCode]], event);
	};
}());

/** 
* Utility to clone objects
* @function
* @name clon
* @param o Object to clone
* @returns object
* @memberOf ch
*/
ch.clon = function(o) {

	var copy = {},
		x;

	for (x in o) {
		if (ch.utils.hasOwn(o, x)) {
			copy[x] = o[x];
		}
	};

	return copy;
};


/** 
* Class to create UI Components
* @name Factory
* @class Factory
* @param o Configuration Object
* @example
*	o {
*		component: "chat",
*		callback: function(){},
*		[script]: "http://..",
*		[style]: "http://..",
*		[callback]: function(){}	
*	}
* @returns collection
* @memberOf ch
*/
// TODO: Always it should receive a conf object as parameter (see Multiple component)
// TODO: Try to deprecate .and() method on Validator
ch.factory = function(o) {

	var x = o.component || o;
	
	var create = function(x) { 

		// Send configuration to a component trough options object
		$.fn[x] = function( options ) {

			var results = [];
			var that = this;

			// Could be more than one argument
			var _arguments = arguments;
			
			that.each( function(i, e) {
				
				var conf = options || {};

				var context = {};
					context.type = x;
					context.element = e;
					context.$element = $(e);
					context.uid = ch.utils.index += 1; // Global instantiation index
			
				switch(typeof conf) {
					// If argument is a number, join with the conf
					case "number":
						var num = conf;
						conf = {};
						conf.value = num;
						
						// Could come a messages as a second argument
						if (_arguments[1]) {
							conf.msg = _arguments[1];
						};
					break;
					
					// This could be a message
					case "string":
						var msg = conf;
						conf = {};
						conf.msg = msg;
					break;
					
					// This is a condition for custom validation
					case "function":
						var func = conf;
						conf = {};
						conf.lambda = func;
						
						// Could come a messages as a second argument
						if (_arguments[1]) {
							conf.msg = _arguments[1];
						};
					break;
				};

				// Create a component from his constructor
				var created = ch[x].call( context, conf );

				/*
					MAPPING INSTANCES
					Internal interface for avoid mapping objects
					{
						exists:true,
						object: {}
					}
				*/

				created = ( ch.utils.hasOwn(created, "public") ) ? created["public"] : created;

				if (created.type) {
					var type = created.type;
					// If component don't exists in the instances map create an empty array
					if (!ch.instances[type]) { ch.instances[type] = []; }
						ch.instances[type].push( created );
				}

				// Avoid mapping objects that already exists
				if (created.exists) {
					// Return the inner object
					created = created.object;
				}

				results.push( created );

			});

			// return the created components collection or single component
			return ( results.length > 1 ) ? results : results[0];
		};

		// if a callback is defined 
		if ( o.callback ) { o.callback(); }

	} // end create function

	if ( ch[x] ) {
		// script already here, just create it
		create(x);

	} else {
		// get resurces and call create later
		ch.get({
			"method":"component",
			"component": x,
			"script": (o.script)? o.script : "src/js/"+x+".js",
			"styles": (o.style)? o.style : "src/css/"+x+".css",
			"callback":create
		});
	}
}

/**
* Load components or content
* @name Get
* @class Get
* @param {object} o Configuration object 
* @example
*	o {
*		component: "chat",
*		[script]: "http://..",
*		[style]: "http://..",
*		[callback]: function(){}
*	}
* @memberOf ch
*/
ch.get = function(o) {
	
	// ch.get: "Should I get a style?"
	if ( o.style ) {
		var style = document.createElement('link');
			style.href = o.style;
			style.rel = 'stylesheet';
			style.type = 'text/css';
	}
	// ch.get: "Should I get a script?"		
	if ( o.script ) {
		var script = document.createElement("script");
			script.src = o.script;
	}

	var head = document.getElementsByTagName("head")[0] || document.documentElement;
	// Handle Script loading
	var done = false;

	// Attach handlers for all browsers
	script.onload = script.onreadystatechange = function() {

		if ( !done && (!this.readyState || 
			this.readyState === "loaded" || this.readyState === "complete") ) {
			done = true;
			// if callback is defined call it
			if ( o.callback ) { o.callback( o.component ); }
			// Handle memory leak in IE
			script.onload = script.onreadystatechange = null;
			if ( head && script.parentNode ) { head.removeChild( script ); }
		}
	};

	// Use insertBefore instead of appendChild to circumvent an IE6 bug.
	// This arises when a base node is used.
	if ( o.script ) { head.insertBefore( script, head.firstChild ); }
	if ( o.style ) { head.appendChild( style ); }

}


/**
* Returns a data object with features supported by the device
* @name Support
* @class Support
* @returns {object}
* @memberOf ch 
*/
ch.support = function () {

	/**
	* Private reference to the <body> element
	* @private
	* @name body
	* @type HTMLBodyElement
	* @memberOf ch.Support
	*/
	var body = document.body || document.documentElement,

	/**
	* Public reference to features support
	* @public
	* @name self
	* @type Object
	* @memberOf ch.Support
	*/
		self = {};

	/**
	* Verify that CSS Transitions are supported (or any of its browser-specific implementations).
	* @basedOn http://gist.github.com/373874
	* @public
	* @name transition
	* @type Boolean
	* @memberOf ch.Support#self
	*/
	self.transition = (function () {

		// Get reference to CSS Style Decalration
		var style = body.style,
		// Grab "undefined" into a privated scope
			u = undefined;

		// Analize availability of transition on all browsers
		return style.WebkitTransition !== u || style.MozTransition !== u || style.MSTransition !== u || style.OTransition !== u || style.transition !== u;
	}());

	/**
	* Boolean property that indicates if CSS "Fixed" positioning are supported by the device.
	* @basedOn http://kangax.github.com/cft/#IS_POSITION_FIXED_SUPPORTED
	* @public
	* @name fixed
	* @type Boolean
	* @memberOf ch.Support#self
	*/
	self.fixed = (function () {

		// Flag to know if position is supported
		var isSupported = false,
		// Create an element to test
			el = document.createElement("div");

		// Set the position fixed
		el.style.position = "fixed";
		// Set a top
		el.style.top = "10px";

		// Add element to DOM
		body.appendChild(el);

		// Compare setted offset with "in DOM" offset of element
		if (el.offsetTop === 10) {
			isSupported = true;
		}

		// Delete element from DOM
		body.removeChild(el);

		// Results
		return isSupported;
	}());

	// Return public object
	return self;
};


/**
* Extend is a utility that resolve creating interfaces problem for all UI-Objects.
* @name Extend
* @class Extend
* @memberOf ch
* @param {string} name Interface's name.
* @param {function} klass Class to inherit from.
* @param {function} [process] Optional function to pre-process configuration, recieves a 'conf' param and must return the configration object.
* @returns class
* @exampleDescription Create an URL interface type based on String component.
* @example
* ch.extend("string").as("url");
* @exampleDescription Create an Accordion interface type based on Menu component.
* @example
* ch.extend("menu").as("accordion"); 
* @exampleDescription And the coolest one... Create an Transition interface type based on his Modal component, with some conf manipulations:
* @example
* ch.extend("modal").as("transition", function(conf) {
*	conf.closeButton = false;
*	conf.msg = conf.msg || conf.content || "Please wait...";
*	conf.content = $("&lt;div&gt;").addClass("loading").after( $("&lt;p&gt;").html(conf.msg) );
*	return conf;
* });
*/

ch.extend = function (klass) {

	"use strict";

	return {
		as: function (name, process) {
			// Create the component in Chico-UI namespace
			ch[name] = function (conf) {
				// Some interfaces need a data value,
				// others simply need to be 'true'.
				conf[name] = conf.value || true;
	
				// Invoke pre-proccess if is defined,
				// or grab the raw conf argument,
				// or just create an empty object.
				conf = (process) ? process(conf) : conf || {};
	
				// Here we recieve messages,
				// or create an empty object.
				conf.messages = conf.messages || {};
	
				// If the interface recieve a 'msg' argument,
				// store it in the message map.
				if (ch.utils.hasOwn(conf, "msg")) {
					conf.messages[name] = conf.msg;
					conf.msg = null;
					delete conf.msg;
				}
				// Here is where the magic happen,
				// invoke the class with the new conf,
				// and return the instance to the namespace.
				return ch[klass].call(this, conf);
			};
			// Almost done, now we need expose the new component,
			// let's ask the factory to do it for us.
			ch.factory(name);
		} // end as method
	} // end return
};


/**
* Cache control utility.
* @name Cache
* @class Cache
* @memberOf ch
*/

ch.cache = {

	/**
	* Map of cached resources
	* @public
	* @name ch.Cache#map 
	* @type object
	*/
	map: {},
	
	/**
	* Set a resource to the cache control
	* @public
	* @function 
	* @name ch.Cache#set
	* @param {string} url Resource location
	* @param {string} data Resource information
	*/
	set: function(url, data) {
		ch.cache.map[url] = data;
	},
	
	/**
	* Get a resource from the cache
	* @public
	* @function
	* @name ch.Cache#get
	* @param {string} url Resource location
	* @returns data Resource information
	*/
	get: function(url) {
		return ch.cache.map[url];
	},
	
	/**
	* Remove a resource from the cache
	* @public
	* @function
	* @name ch.Cache#rem
	* @param {string} url Resource location
	*/
	rem: function(url) {
		ch.cache.map[url] = null;
		delete ch.cache.map[url];
	},
	
	/**
	* Clears the cache map
	* @public
	* @function
	* @name ch.Cache#flush
	*/
	flush: function() {
		delete ch.cache.map;
		ch.cache.map = {};
	}
};

/**
* Object represents the abstract class of all Objects.
* @abstract
* @name Object
* @class Object
* @memberOf ch
* @see ch.Controllers
* @see ch.Floats
* @see ch.Navs
* @see ch.Validator
* @see ch.Controls
*/

ch.object = function(){

	/**
	* Reference to a internal component instance, saves all the information and configuration properties.
	* @private
	* @name ch.Object#that
	* @type object
	*/
	var that = this;

	var conf = that.conf;

/**
*	Public Members
*/

	/**
	* This method will be deprecated soon. Triggers a specific callback inside component's context.
	* @name ch.Object#callbacks
	* @function
	* @protected
	*/
	// TODO: Add examples!!!
	that.callbacks = function (when, data) {
		if( ch.utils.hasOwn(conf, when) ) {
			var context = ( that.controller ) ? that.controller["public"] : that["public"];
			return conf[when].call( context, data );
		};
	};


	// Triggers a specific event within the component public context.
	that.trigger = function (event, data) {
		$(that["public"]).trigger("ch-"+event, data);
	};
	
	// Add a callback function from specific event.
	that.on = function (event, handler) {
		if (event && handler) {
			$(that["public"]).bind("ch-"+event, handler);
		}
		return that["public"];
	};

	// Add a callback function from specific event that it will execute once.
	that.once = function (event, handler) {

		if (event && handler) {
			$(that["public"]).one("ch-"+event, handler);
		}

		return that["public"];
	};

	
	// Removes a callback function from specific event.
	that.off = function (event, handler) {
		if (event && handler) {
			$(that["public"]).unbind("ch-"+event, handler);
		} else if (event) {
			$(that["public"]).unbind("ch-"+event);
		}
		return that["public"];
	};

	/**
	* Component's public scope. In this scope you will find all public members.
	*/
	that["public"] = {};

	/**
	* The 'uid' is the Chico's unique instance identifier. Every instance has a different 'uid' property. You can see its value by reading the 'uid' property on any public instance.
	* @public
	* @name ch.Object#uid
	* @type number
	*/
	that["public"].uid = that.uid;

	/**
	* Reference to a DOM Element. This binding between the component and the HTMLElement, defines context where the component will be executed. Also is usual that this element triggers the component default behavior.
	* @public
	* @name ch.Object#element
	* @type HTMLElement
	*/
	that["public"].element = that.element;

	/**
	* This public property defines the component type. All instances are saved into a 'map', grouped by its type. You can reach for any or all of the components from a specific type with 'ch.instances'.
	* @public
	* @name ch.Object#type
	* @type string
	*/
	that["public"].type = that.type;
	
	/**
	* Triggers a specific event within the component public context.
	* @name trigger
	* @name ch.Object 
	* @public
	* @param {string} event The event name you want to trigger.
	* @since 0.7.1
	*/
	that["public"].trigger = that.trigger;

	/**
	* Add a callback function from specific event.
	* @public
	* @name ch.Object#on
	* @function
	* @param {string} event Event nawidget.
	* @param {function} handler Handler function.
	* @returns itself
	* @since version 0.7.1
	* @exampleDescription Will add a event handler to the "ready" event
	* @example
	* widget.on("ready", startDoingStuff);
	*/
	that["public"].on = that.on;
	
	/**
	* Add a callback function from specific event that it will execute once.
	* @public
	* @name ch.Object#once
	* @function
	* @param {string} event Event nawidget.
	* @param {function} handler Handler function.
	* @returns itself
	* @since version 0.8.0
	* @exampleDescription Will add a event handler to the "contentLoad" event once
	* @example
	* widget.once("contentLoad", startDoingStuff);
	*/
	that["public"].once = that.once;

	/**
	* Removes a callback function from specific event.
	* @public
	* @function
	* @name ch.Object#off
	* @param {string} event Event nawidget.
	* @param {function} handler Handler function.
	* @returns itself
	* @since version 0.7.1
	* @exampleDescription Will remove event handler to the "ready" event
	* @example
	* var startDoingStuff = function () {
	*     // Some code here!
	* };
	*
	* widget.off("ready", startDoingStuff);
	*/
	that["public"].off = that.off;

	return that;
};

/**
* Positioner lets you centralize and manage changes related to positioned elements. Positioner returns an utility that resolves positioning for all widget.
* @name Positioner
* @class Positioner
* @memberOf ch
* @param {Object} conf Configuration object with positioning properties.
* @param {String} conf.element Reference to the DOM Element to be positioned.
* @param {String} [conf.context] It's a reference to position and size of element that will be considered to carry out the position. If it isn't defined through configuration, it will be the viewport.
* @param {String} [conf.points] Points where element will be positioned, specified by configuration or centered by default.
* @param {String} [conf.offset] Offset in pixels that element will be displaced from original position determined by points. It's specified by configuration or zero by default.
* @param {Boolean} [conf.reposition] Parameter that enables or disables reposition intelligence. It's disabled by default.
* @requires ch.Viewport
* @see ch.Viewport
* @returns {Function} The Positioner returns a Function that it works in 3 ways: as a setter, as a getter and with the "refresh" parameter refreshes the position.
* @exampleDescription
* Instance the Positioner It requires a little configuration. 
* The default behavior place an element centered into the Viewport. 
*  
* @example
* var positioned = ch.positioner({
*     element: "#element1",
* });
* @exampleDescription 1. Getting the current configuration properties.
* @example
* var configuration = positioned()
* @exampleDescription 2. Updates the current position with <code>refresh</code> as a parameter. 
* @example
* positioned("refresh");
* @exampleDescription 3. Define a new position
* @example
* positioned({
*     element: "#element2",
*     context: "#context2",
*     points: "lt rt"
* });
* @exampleDescription <strong>Offset</strong>: The positioner could be configurated with an offset. 
* This example show an element displaced horizontally by 10px of defined position.
* @example
* var positioned = ch.positioner({
*     element: "#element3",
*     context: "#context3",
*     points: "lt rt",
*     offset: "10 0"
* });
* @exampleDescription <strong>Reposition</strong>: Repositionable feature moves the postioned element if it can be shown into the viewport.
* @example
* var positioned = ch.positioner({
*     element: "#element4",
*     context: "#context4",
*     points: "lt rt",
*     reposition: true
* });
*/

ch.positioner = (function () {

	/**
	* Converts points in className.
	* @private
	* @name ch.Positioner#classNamePoints
	* @function
	* @returns String
	*/
	var classNamePoints = function (points) {
			return "ch-points-" + points.replace(" ", "");
		},

	/**
	* Reference that allows to know when window is being scrolled or resized.
	* @private
	* @name ch.Positioner#changing
	* @type Boolean
	*/
		changing = false,

	/**
	* Checks if window is being scrolled or resized, updates viewport position and triggers internal Change event.
	* @private
	* @name ch.Positioner#triggerScroll
	* @function
	*/
		triggerChange = function () {
			// No changing, no execution
			if (!changing) { return; }

			// Updates viewport position
			ch.viewport.getOffset();

			/**
			* Triggers when window is being scrolled or resized.
			* @private
			* @name ch.Positioner#change
			* @event
			*/
			ch.utils.window.trigger(ch.events.VIEWPORT.CHANGE);

			// Change scrolling status
			changing = false;
		};

	// Resize and Scroll events binding. These updates respectives boolean variables
	ch.utils.window.bind("resize scroll", function () { changing = true; });

	// Interval that checks for resizing status and triggers specific events
	setInterval(triggerChange, 350);

	// Returns Positioner Abstract Component
	return function (conf) {

		// Validation for required "element" parameter
		if (!ch.utils.hasOwn(conf, "element")) {
			alert("Chico UI error: Expected to find \"element\" as required configuration parameter of ch.Positioner");

			return;
		}

		/**
		* Configuration parameter that enables or disables reposition intelligence. It's disabled by default.
		* @private
		* @name ch.Positioner#reposition
		* @type Boolean
		* @default false
		* @exampleDescription Repositionable Element if it can't be shown into viewport area
		* @example
		* ch.positioner({
		*     element: "#element1",
		*     reposition: true
		* });
		*/
		conf.reposition = conf.reposition || false;

		/**
		* Reference that saves all members to be published.
		* @private
		* @name ch.Positioner#that
		* @type Object
		*/
		var that = {},

		/**
		* Reference to the DOM Element to be positioned.
		* @private
		* @name ch.Positioner#$element
		* @type jQuery Object
		*/
			$element = $(conf.element),

		/**
		* Points where element will be positioned, specified by configuration or centered by default.
		* @private
		* @name ch.Positioner#points
		* @type String
		* @default "cm cm"
		* @exampleDescription Element left-top point = Context right-bottom point
		* @example
		* ch.positioner({
		*     element: "#element1",
		*     points: "lt rt"
		* });
		* @exampleDescription Element center-middle point = Context center-middle point
		* @example
		* ch.positioner({
		*     element: "#element2",
		*     points: "cm cm"
		* });
		*/
			points = conf.points || "cm cm",

		/**
		* Offset in pixels that element will be displaced from original position determined by points. It's specified by configuration or zero by default.
		* @private
		* @name ch.Positioner#offset
		* @type {Array} X and Y references determined by "offset" configuration parameter.
		* @default "0 0"
		* @exampleDescription Moves 5px to right and 5px to bottom
		* @example
		* ch.positioner({
		*     element: "#element1",
		*     offset: "5 5"
		* });
		* @exampleDescription It will be worth:
		* @example
		* offset[0] = 5;
		* offset[1] = 5;
		* @exampleDescription Moves 10px to right and 5px to top
		* @example
		* ch.positioner({
		*     element: "#element1",
		*     offset: "10 -5"
		* });
		* @exampleDescription It will be worth:
		* @example It will be worth:
		* offset[0] = 10;
		* offset[1] = -5;
		*/
			offset = (conf.offset || "0 0").split(" "),

		/**
		* Defines context element, its size, position, and methods to recalculate all.
		* @function
		* @name ch.Positioner#getContext
		* @returns Context Object
		*/
			getContext = function () {

				// Parse as Integer offset values
				offset[0] = parseInt(offset[0], 10);
				offset[1] = parseInt(offset[1], 10);

				// Context by default is viewport
				if (!ch.utils.hasOwn(conf, "context") || !conf.context || conf.context === "viewport") {
					contextIsNotViewport = false;
					return ch.viewport;
				}

				// Context from configuration
				// Object to be returned.
				var self = {};

				/**
				* Width of context.
				* @private
				* @name width
				* @type Number
				* @memberOf ch.Positioner#context
				*/
				self.width =

				/**
				* Height of context.
				* @private
				* @name height
				* @type Number
				* @memberOf ch.Positioner#context
				*/
					self.height =

				/**
				* Left offset of context.
				* @private
				* @name left
				* @type Number
				* @memberOf ch.Positioner#context
				*/
					self.left =

				/**
				* Top offset of context.
				* @private
				* @name top
				* @type Number
				* @memberOf ch.Positioner#context
				*/
					self.top =

				/**
				* Right offset of context.
				* @private
				* @name right
				* @type Number
				* @memberOf ch.Positioner#context
				*/
					self.right =

				/**
				* Bottom offset of context.
				* @private
				* @name bottom
				* @type Number
				* @memberOf ch.Positioner#context
				*/
					self.bottom = 0;

				/**
				* Context HTML Element.
				* @private
				* @name element
				* @type HTMLElement
				* @memberOf ch.Positioner#context
				*/
				self.element = $(conf.context);

				/**
				* Recalculates width and height of context and updates size on context object.
				* @private
				* @function
				* @name getSize
				* @returns Object
				* @memberOf ch.Positioner#context
				*/
				self.getSize = function () {

					return {
						"width": context.width = self.element.outerWidth(),
						"height": context.height = self.element.outerHeight()
					};

				};
				
				/**
				* Recalculates left and top of context and updates offset on context object.
				* @private
				* @function
				* @name getOffset
				* @returns Object
				* @memberOf ch.Positioner#context
				*/
				self.getOffset = function () {

					// Gets offset of context element
					var contextOffset = self.element.offset(),
						size = self.getSize(),
						scrollLeft = contextOffset.left, // + offset[0], // - relativeParent.left,
						scrollTop = contextOffset.top; // + offset[1]; // - relativeParent.top;

					if (!parentIsBody) {
						scrollLeft -= relativeParent.left,
						scrollTop -= relativeParent.top;
					}
					
					// Calculated including offset and relative parent positions
					return {
						"left": context.left = scrollLeft,
						"top": context.top = scrollTop,
						"right": context.right = scrollLeft + size.width,
						"bottom": context.bottom = scrollTop + size.height
					};
				};

				contextIsNotViewport = true;

				return self;
			},

		/**
		* Reference that allows to know if context is different to viewport.
		* @private
		* @name ch.Positioner#contextIsNotViewport
		* @type Boolean
		*/
			contextIsNotViewport,

		/**
		* It's a reference to position and size of element that will be considered to carry out the position. If it isn't defined through configuration, it will be the viewport.
		* @private
		* @name ch.Positioner#context
		* @type Object
		* @default ch.Viewport
		*/
			context = getContext(),
		
		/**
		* Reference to know if direct parent is the body HTML element.
		* @private
		* @name ch.Positioner#parentIsBody
		* @type Boolean
		*/
			parentIsBody,

		/**
		* It's the first of context's parents that is styled positioned. If it isn't defined through configuration, it will be the HTML Body Element.
		* @private
		* @name ch.Positioner#relativeParent
		* @type Object
		* @default HTMLBodyElement
		*/
			relativeParent = (function () {

				// Context's parent that's positioned.
				var element = (contextIsNotViewport) ? context.element.offsetParent()[0] : ch.utils.body[0],

				// Object to be returned.
					self = {};

				/**
				* Left offset of relative parent.
				* @private
				* @name left
				* @type Number
				* @memberOf ch.Positioner#relativeParent
				*/
				self.left =

				/**
				* Top offset of relative parent.
				* @private
				* @name top
				* @type Number
				* @memberOf ch.Positioner#relativeParent
				*/
					self.top = 0;

				/**
				* Recalculates left and top of relative parent of context and updates offset on relativeParent object.
				* @private
				* @name getOffset
				* @function
				* @memberOf ch.Positioner#relativeParent
				* @returns Offset Object
				*/
				// TODO: on ie6 the relativeParent border push too (also on old positioner)
				self.getOffset = function () {
					// If first parent relative is Body, don't recalculate position
					if (element.tagName === "BODY") { return; }

					// Offset of first parent relative
					var parentOffset = $(element).offset(),

					// Left border width of context's parent.
						borderLeft = parseInt(ch.utils.getStyles(element, "border-left-width"), 10),

					// Top border width of context's parent.
						borderTop = parseInt(ch.utils.getStyles(element, "border-top-width"), 10);

					// Returns left and top position of relative parent and updates offset on relativeParent object.
					return {
						"left": relativeParent.left = parentOffset.left + borderLeft,
						"top": relativeParent.top = parentOffset.top + borderTop
					};
				};
				
				return self;
			}()),

		/**
		* Calculates left and top position from specific points.
		* @private
		* @name ch.Positioner#getCoordinates
		* @function
		* @param {String} points String with points to be calculated.
		* @returns Offset measures
		* @exampleDescription
		* @example
		* var foo = getCoordinates("lt rt");
		* 
		* foo = {
		*     left: Number,
		*     top: Number
		* };
		*/
			getCoordinates = function (pts) {

				// Calculates left or top position from points related to specific axis (X or Y).
				// TODO: Complete cases: X -> lc, cl, rc, cr. Y -> tm, mt, bm, mb.
				var calculate = function (reference) {

					// Use Position or Offset of Viewport if position is fixed or absolute respectively
					var ctx = (!contextIsNotViewport && ch.features.fixed) ? ch.viewport.getPosition() : context,
					
					// Returnable value
						r;

					switch (reference) {
					// X references
					case "ll": r = ctx.left + offset[0]; break;
					case "lr": r = ctx.right + offset[0]; break;
					case "rl": r = ctx.left - $element.outerWidth() + offset[0]; break;
					case "rr": r = ctx.right - $element.outerWidth() + offset[0]; break;
					case "cc": r = ctx.left + (ctx.width / 2) - ($element.outerWidth() / 2) + offset[0]; break;
					// Y references
					case "tt": r = ctx.top + offset[1]; break;
					case "tb": r = ctx.bottom + offset[1]; break;
					case "bt": r = ctx.top - $element.outerHeight() + offset[1]; break;
					case "bb": r = ctx.bottom - $element.outerHeight() + offset[1]; break;
					case "mm": r = ctx.top + (ctx.height / 2) - ($element.outerHeight() / 2) + offset[1]; break;
					}

					return r;
				},

				// Splitted points
					splittedPoints = pts.split(" ");

				// Calculates left and top with references to X and Y axis points (crossed points)
				return {
					"left": calculate(splittedPoints[0].charAt(0) + splittedPoints[1].charAt(0)),
					"top": calculate(splittedPoints[0].charAt(1) + splittedPoints[1].charAt(1))
				};
			},

		/**
		* Gets new coordinates and checks its space into viewport.
		* @private
		* @name ch.Positioner#getPosition
		* @function
		* @returns Offset measures
		*/
			getPosition = function () {

				// Gets coordinates from main points
				var coordinates = getCoordinates(points);

				// Update classPoints
				// TODO: Is this ok in this place?
				classPoints = classNamePoints(points);

				// Default behavior: returns left and top offset related to main points
				if (!conf.reposition) { return coordinates; }

				if (points !== "lt lb" && points !== "rt rb" && points !== "lt rt") { return coordinates; }

				// Intelligence
				// TODO: Improve and unify intelligence code
				var newData,
					newPoints = points,
					offsetX = /*relativeParent.left + */offset[0],
					offsetY = /*relativeParent.top + */offset[1];
				
				if (!parentIsBody) {
					offsetX += relativeParent.left;
					offsetY += relativeParent.top;
				}

				// Viewport limits (From bottom to top)
				if (coordinates.top + offsetY + $element.outerHeight() > ch.viewport.bottom && points !== "lt rt") {
					newPoints = newPoints.charAt(0) + "b " + newPoints.charAt(3) + "t";
					newData = getCoordinates(newPoints);

					newData.classPoints = classNamePoints(newPoints);

					if (newData.top + offsetY > ch.viewport.top) {
						coordinates.top = newData.top - (2 * offset[1]);
						coordinates.left = newData.left;
						classPoints = newData.classPoints;
					}
				}

				// Viewport limits (From right to left)
				if (coordinates.left + offsetX + $element.outerWidth() > ch.viewport.right) {
					// TODO: Improve this
					var orientation = (newPoints.charAt(0) === "r") ? "l" : "r";
					// TODO: Use splice or slice
					newPoints = orientation + newPoints.charAt(1) + " " + orientation + newPoints.charAt(4);

					newData = getCoordinates(newPoints);

					newData.classPoints = classNamePoints(newPoints);

					if (newData.left + offsetX > ch.viewport.left) {
						coordinates.top = newData.top;
						coordinates.left = newData.left - (2 * offset[0]);
						classPoints = newData.classPoints;
					}
				}

				// Returns left and top offset related to modified points
				return coordinates;
			},

		/**
		* Reference that stores last changes on coordinates for evaluate necesaries redraws.
		* @private
		* @name ch.Positioner#lastCoordinates
		* @type Object
		*/
			lastCoordinates = {},

		/**
		* Checks if there are changes on coordinates to reposition the element.
		* @private
		* @name ch.Positioner#draw
		* @function
		*/
			draw = function () {

				// New element position
				var coordinates,
					
					// Update classname related to position
					updateClassName = function ($element) {
						$element.removeClass(lastClassPoints).addClass(classPoints);
					};

				// Save the last className before calculate new points
				lastClassPoints = classPoints;

				// Gets definitive coordinates for element repositioning
				coordinates = getPosition();

				// Coordinates equal to last coordinates means that there aren't changes on position
				if (coordinates.left === lastCoordinates.left && coordinates.top === lastCoordinates.top) {
					return;
				}

				// If there are changes, it stores new coordinates on lastCoordinates
				lastCoordinates = coordinates;
				
				// Element reposition (Updates element position based on new coordinates)
				updateClassName($element.css({ "left": coordinates.left, "top": coordinates.top }));

				// Context class-names
				if (contextIsNotViewport) { updateClassName(context.element); }
			},

		/**
		* Constructs a new position, gets viewport size, checks for relative parent's offset,
		* finds the context and sets the position to a given element.
		* @private
		* @function
		* @constructs
		* @name ch.Positioner#init
		*/
			init = function () {
				// Calculates viewport position for prevent auto-scrolling
				//ch.viewport.getOffset();
				
				// Refresh parent parameter
				// TODO: Put this code in some better place, where it's been calculated few times
				parentIsBody = $element.parent().length > 0 && $element.parent().prop("tagName") === "BODY";
				
				// Calculates relative parent position
				relativeParent.getOffset();

				// If context isn't the viewport, calculates its position and size
				if (contextIsNotViewport) { context.getOffset(); }

				// Calculates coordinates and redraws if it's necessary	
				draw();
			},

		/**
		* Listen to LAYOUT.CHANGE and VIEWPORT.CHANGE events and recalculate data as needed.
		* @private
		* @function
		* @name ch.Positioner#changesListener
		*/
			changesListener = function (event) {
				// Only recalculates if element is visible
				if (!$element.is(":visible")) { return; }
	
				// If context isn't the viewport...
				if (contextIsNotViewport) {
					// On resize and layout change, recalculates relative parent position
					relativeParent.getOffset();
	
					// Recalculates its position and size
					context.getOffset();
				}
	
				draw();
			},

		/**
		* Position "element" as fixed or absolute as needed.
		* @private
		* @function
		* @name ch.Positioner#addCSSproperties
		*/
			addCSSproperties = function () {

				// Fixed position behavior
				if (!contextIsNotViewport && ch.features.fixed) {

					// Sets position of element as fixed to avoid recalculations
					$element.css("position", "fixed");

					// Bind reposition only on resize
					ch.utils.window.bind("resize", changesListener);

				// Absolute position behavior
				} else {

					// Sets position of element as absolute to allow continuous positioning
					$element.css("position", "absolute");

					// Bind reposition recalculations (scroll, resize and changeLayout)
					ch.utils.window.bind(ch.events.VIEWPORT.CHANGE + " " + ch.events.LAYOUT.CHANGE, changesListener);
				}

			},

		/**
		* Classname relative to position points.
		* @private
		* @name ch.Positioner#classPoints
		* @type String
		* @default "ch-points-cmcm"
		*/
			classPoints = classNamePoints(points),

		/**
		* The last className before calculate new points.
		* @private
		* @name ch.Positioner#lastClassPoints
		* @type string
		*/
			lastClassPoints = classPoints;

		/**
		* Control object that allows to change configuration properties, refresh current position or get current configuration.
		* @ignore
		* @protected
		* @name ch.Positioner#position
		* @function
		* @param {Object} [o] Configuration object.
		* @param {String} ["refresh"] Refresh current position.
		* @returns Control Object
		* @exampleDescription Sets a new configuration
		* @example
		* var foo = ch.positioner({ ... });
		* foo.position({ ... });
		* @exampleDescription Refresh current position
		* @example
		* foo.position("refresh");
		* @exampleDescription Gets current configuration properties
		* @example
		* foo.position();
		*/
		that.position = function (o) {

			var r = that;

			switch (typeof o) {
			
			// Changes configuration properties and repositions the element
			case "object":
				// New points
				if (ch.utils.hasOwn(o, "points")) { points = o.points; }

				// New reposition
				if (ch.utils.hasOwn(o, "reposition")) { conf.reposition = o.reposition; }

				// New offset (splitted)
				if (ch.utils.hasOwn(o, "offset")) { offset = o.offset.split(" "); }

				// New context
				if (ch.utils.hasOwn(o, "context")) {
					// Sets conf value
					conf.context = o.context;

					// Clear the conf.context variable
					if (o.context === "viewport") { conf.context = undefined; }

					// Regenerate the context object
					context = getContext();
					
					// Update CSS properties to element (position fixed or absolute)
					addCSSproperties();
				}

				// Reset
				init();

				break;

			// Refresh current position
			case "string":
				if (o !== "refresh") {
					alert("Chico UI error: expected to find \"refresh\" parameter on position() method of Positioner component.");
				}

				// Reset
				init();

				break;

			// Gets current configuration
			case "undefined":
			default:
				r = {
					"context": context.element,
					"element": $element,
					"points": points,
					"offset": offset.join(" "),
					"reposition": conf.reposition
				};

				break;
			}

			return r;
		};

		// Apply CSS properties to element (position fixed or absolute)
		addCSSproperties();

		// Inits positioning
		init();

		return that.position;
	};

}());

/**
* Viewport is a reference to position and size of the visible area of browser.
* @name Viewport
* @class Viewport
* @standalone
* @memberOf ch
*/
ch.viewport = {

	/**
	* Width of the visible area.
	* @public
	* @name ch.Viewport#width
	* @type Number
	*/
	"width": ch.utils.window.width(),

	/**
	* Height of the visible area.
	* @public
	* @name ch.Viewport#height
	* @type Number
	*/
	"height": ch.utils.window.height(),

	/**
	* Left offset of the visible area.
	* @public
	* @name ch.Viewport#left
	* @type Number
	*/
	"left": ch.utils.window.scrollLeft(),

	/**
	* Top offset of the visible area.
	* @public
	* @name ch.Viewport#top
	* @type Number
	*/
	"top": ch.utils.window.scrollTop(),

	/**
	* Right offset of the visible area.
	* @public
	* @name ch.Viewport#right
	* @type Number
	*/
	"right": ch.utils.window.scrollLeft() + ch.utils.window.width(),

	/**
	* Bottom offset of the visible area.
	* @public
	* @name ch.Viewport#bottom
	* @type Number
	*/
	"bottom": ch.utils.window.scrollTop() + ch.utils.window.height(),

	/**
	* Element representing the visible area.
	* @public
	* @name ch.Viewport#element
	* @type Object
	*/
	"element": ch.utils.window,

	/**
	* Updates width and height of the visible area and updates ch.viewport.width and ch.viewport.height
	* @public
	* @function
	* @name ch.Viewport#getSize
	* @returns Object
	*/
	"getSize": function () {

		return {
			"width": this.width = ch.utils.window.width(),
			"height": this.height = ch.utils.window.height()
		};

	},

	/**
	* Updates left, top, right and bottom coordinates of the visible area, relative to the window.
	* @public
	* @function
	* @name ch.Viewport#getPosition
	* @returns Object
	*/
	"getPosition": function () {

		var size = this.getSize();

		return {
			"left": 0,
			"top": 0,
			"right": size.width,
			"bottom": size.height,
			// Size is for use as context on Positioner
			// (see getCoordinates method on Positioner)
			"width": size.width,
			"height": size.height
		};
		
	},
	
	/**
	* Updates left, top, right and bottom coordinates of the visible area, relative to the document.
	* @public
	* @function
	* @name ch.Viewport#getOffset
	* @returns Object
	*/
	"getOffset": function () {

		var position = this.getPosition(),
			scrollLeft = ch.utils.window.scrollLeft(),
			scrollTop = ch.utils.window.scrollTop();

		return {
			"left": this.left = scrollLeft,
			"top": this.top = scrollTop,
			"right": this.right = scrollLeft + position.right,
			"bottom": this.bottom = scrollTop + position.bottom
		};
		
	}
};

/**
* Object represents the abstract class of all widgets.
* @abstract
* @name Uiobject
* @class Uiobject
* @augments ch.Object
* @requires ch.Cache
* @memberOf ch
* @exampleDescription 
* @example
* ch.uiobject.call();
* @see ch.Object
* @see ch.Cache
* @see ch.Controllers
* @see ch.Floats
* @see ch.Navs
* @see ch.Watcher
*/

ch.uiobject = function(){

	/**
	* Reference to a internal component instance, saves all the information and configuration properties.
	* @private
	* @name ch.Uiobject#that
	* @type object
	*/
	var that = this;

	var conf = that.conf;
	

/**
*	Inheritance
*/

	that = ch.object.call(that);
	that.parent = ch.clon(that);



/**
*	Protected Members
*/

	/**
	* Component static content.
	* @protected
	* @name ch.Uiobject#staticContent
	* @type string
	*/
	that.staticContent;

	/**
	* DOM Parent of content, this is useful to attach DOM Content when float is hidding.
	* @protected
	* @name ch.Uiobject#DOMParent
	* @type HTMLElement
	*/
	that.DOMParent;

	/**
	* Component original content.
	* @protected
	* @name ch.Uiobject#originalContent
	* @type HTMLElement
	*/
	that.originalContent;

	/**
	* Set and get the content of a component. With no arguments will behave as a getter function. Send any kind of content and will be a setter function. Use a valid URL for AJAX content, use a CSS selector for a DOM content or just send a static content like HTML or Text.
	* @ignore
	* @name ch.Uiobject#content
	* @protected
	* @function
	* @param {string} [content] Could be a simple text, html or a url to get the content with ajax.
	* @returns {string} content
	* @requires ch.Cache
	* @exampleDescription Simple static content
	* @example
	* $(element).layer().content("Some static content");
	* @exampleDescription Get DOM content
	* @example
	* $(element).layer().content("#hiddenContent");
	* @exampleDescription Get AJAX content
	* @example
	* $(element).layer().content("http://chico.com/content/layer.html");
	*/
	that.content = function(content) {

		var _get = (content) ? false : true,

			// Local argument
			content = content,
			// Local isURL
			sourceIsUrl = ch.utils.isUrl(that.source),
			// Local isSelector
			sourceIsSelector = ch.utils.isSelector(that.source),
			// Local inDom
			sourceInDom = (!sourceIsUrl) ? ch.utils.inDom(that.source) : false,
			// Get context, could be a single component or a controller
			context = ( ch.utils.hasOwn(that, "controller") ) ? that.controller : that["public"],
			// undefined, for comparison.
			undefined,
			// Save cache configuration
			cache = ( ch.utils.hasOwn(conf, "cache") ) ? conf.cache : true;

		/**
		* Get content
		*/

		// return defined content
		if (_get) {

			// no source, no content
			if (that.source === undefined) {
				that.staticContent = "<p>No content defined for this component</p>";
				that.trigger("contentLoad");

				return;
			}

			// First time we need to get the content.
			// Is cache is off, go and get content again.
			// Yeap, recursive.
			if (!cache || that.staticContent === undefined) {
				that.content(that.source);
				return;
			}

			// Get data from cache if the staticContent was defined
			if (cache && that.staticContent) {
				var fromCache = ch.cache.get(that.source);

				// Load content from cache
				if (fromCache && that.staticContent != fromCache) {
					that.staticContent = fromCache;

					that.trigger("contentLoad");

					// Return and load content from cache
					return;
				}

				// Return and show the latest content that was loaded
				return;
			}
		}

		/**
		* Set content
		*/

		// Reset cache to overwrite content
		conf.cache = false;

		// Local isURL
		var isUrl = ch.utils.isUrl(content),
			// Local isSelector
			isSelector = ch.utils.isSelector(content),
			// Local inDom
			inDom = (!isUrl) ? ch.utils.inDom(content) : false;

		/* Evaluate static content*/

		// Set 'that.staticContent' and overwrite 'that.source'
		// just in case you want to update DOM or AJAX Content.

		that.staticContent = that.source = content;

		/* Evaluate AJAX content*/

		if (isUrl) {

			// First check Cache
			// Check if this source is in our cache
			if (cache) {
				var fromCache = ch.cache.get(that.source);
				if (fromCache) {
					conf.cache = cache;
					that.staticContent = fromCache;
					that.trigger("contentLoad", context);
					return;
				}
			}

			var _method, _serialized, _params = "x=x";

			// If the trigger is a form button, serialize its parent to send data to the server.
			if (that.$element.attr('type') == 'submit') {
				_method = that.$element.parents('form').attr('method') || 'GET';
				_serialized = that.$element.parents('form').serialize();
				_params = _params + ((_serialized != '') ? '&' + _serialized : '');
			};

			// Set ajax config
			// On IE (6-7) "that" reference losts for second time
			// Why?? I don't know... but with a setTimeOut() works fine!
			setTimeout(function(){

				$.ajax({
					url: that.source,
					type: _method || 'GET',
					data: _params,
					// each component could have a different cache configuration
					cache: cache,
					async: true,
					beforeSend: function(jqXHR){
						// Ajax default HTTP headers
						jqXHR.setRequestHeader("X-Requested-With", "XMLHttpRequest");
					},
					success: function(data, textStatus, jqXHR){
						// Save data as staticContent
						that.staticContent = data;

						// Use the contentLoad callback.
						that.trigger("contentLoad", context);

						// Save new staticContent to the cache
						if (cache) {
							ch.cache.set(that.source, that.staticContent);
						}

					},
					error: function(jqXHR, textStatus, errorThrown){
						that.staticContent = "<p>Error on ajax call.</p>";

						var data = {
							"context": context,
							"jqXHR": jqXHR,
							"textStatus": textStatus,
							"errorThrown": errorThrown
						};

						// Use the contentError callback.
						that.trigger("contentError", data);
					}
				});

			}, 0);

			// Return Spinner and wait for async callback
			that.$content.html("<div class=\"ch-loading\"></div>");
			that.staticContent = undefined;

		} else {

			/* Evaluate DOM content*/

			if (isSelector && inDom) {

				// Save original DOMFragment.
				that.originalContent = $(content);

				// Save DOMParent, so we know where to re-insert the content.
				that.DOMParent = that.originalContent.parent();

				// Save a clone to original DOM content
				that.staticContent = that.originalContent.clone().removeClass("ch-hide");

			}

			// Save new data to the cache
			if (cache) {
				ch.cache.set(that.source, that.staticContent);
			}

			// First time we need to set the callbacks that append and remove the original content.
			if (that.originalContent && that.originalContent.selector == that.source) {

				// Remove DOM content from DOM to avoid ID duplications
				that["public"].on("show", function() {
					that.originalContent.detach();
				});

				// Returns DOMelement to DOM
				that["public"].on("hide", function(){
					that.originalContent.appendTo(that.DOMParent||"body");
				});
			}
		}

		/* Set previous cache configuration*/
		conf.cache = cache;

		// trigger contentLoad callback for DOM and Static content.
		if (that.staticContent !== undefined) {
			that.trigger("contentLoad", context);
		}

	};

	/**
	* Prevent propagation and default actions.
	* @name ch.Uiobject#prevent
	* @function
	* @protected
	* @param {event} event Recieves a event object
	*/
	that.prevent = function(event) {

		if (event && typeof event == "object") {
			event.preventDefault();
			event.stopPropagation();
		};

		return that;
	};

/**
*	Public Members
*/
	
	/**
	* Component's public scope. In this scope you will find all public members.
	*/

	/**
	* Sets and gets component content. To get the defined content just use the method without arguments, like 'widget.content()'. To define a new content pass an argument to it, like 'widget.content("new content")'. Use a valid URL to get content using AJAX. Use a CSS selector to get content from a DOM Element. Or just use a String with HTML code.
	* @ignore
	* @public
	* @name ch.Uiobject#content
	* @function
	* @param {string} content Static content, DOM selector or URL. If argument is empty then will return the content.
	* @exampleDescription Get the defined content
	* @example
	* widget.content();
	* @exampleDescription Set static content
	* @example
	* widget.content("Some static content");
	* @exampleDescription Set DOM content
	* @example
	* widget.content("#hiddenContent");
	* @exampleDescription Set AJAX content
	* @example
	* widget.content("http://chico.com/some/content.html");
	*/
	that["public"].content = function(content){
		if (content) { // sets
			// Reset content data
			that.source = content;
			that.staticContent = undefined;

			if (that.active) {
				that.content(content);
			}

			return that["public"];

		} else { // gets
			return that.staticContent;
		}
	};
	
	/**
	* @borrows ch.Object#trigger as ch.Uiobject#trigger
	*/

	/**
	* @borrows ch.Object#on as ch.Uiobject#on
	*/

	/**
	* @borrows ch.Object#once as ch.Uiobject#once
	*/

	/**
	* @borrows ch.Object#off as ch.Uiobject#off
	*/
	

	return that;
};

/**
* Floats brings the functionality of all Floats elements.
* @abstract
* @name ch.Floats
* @class Floats
* @augments ch.Uiobject
* @requires ch.Positioner
* @returns itself
* @see ch.Tooltip
* @see ch.Layer
* @see ch.Modal
* @see ch.Controls
* @see ch.Transition
* @see ch.Zoom
* @see ch.Uiobject
* @see ch.Positioner
*/

ch.floats = function () {

	/**
	* Reference to a internal component instance, saves all the information and configuration properties.
	* @protected
	* @name ch.Floats#that
	* @type object
	*/
	var that = this,
		conf = that.conf;
/**
* Inheritance
*/

	that = ch.uiobject.call(that);
	that.parent = ch.clon(that);

/**
* Private Members
*/

	/**
	* Creates a 'cone', is a visual asset for floats.
	* @private
	* @function
	* @deprecated
	* @name ch.Floats#createCone
	*/

	/**
	* Creates close button.
	* @private
	* @function
	* @deprecated
	* @name ch.Floats#createClose
	*/

	/**
	* Closable behavior.
	* @private
	* @function
	* @name ch.Floats-closable
	*/
	// TODO: Create "closable" interface
	var closable = (function () {
		/**
		* Returns any if the component closes automatic. 
		* @public
		* @function
		* @methodOf ch.Floats#closabe
		* @exampleDescription to get the height
		* @example
		* widget.closable() // true | false | "button"
		* @returns boolean | string
		*/
		that["public"].closable = function () {
			return that.closable;
		};

		
		return function () {
			
			// Closable Off: don't anything
			if (!that.closable) { return; }

			// Closable On

			if (ch.utils.hasOwn(conf, "closeButton") && conf.closeButton || ch.utils.hasOwn(conf, "event") && conf.event === "click") {
				// Append close buttons	
				// It will close with close button
				that.$container
					.prepend("<a class=\"ch-close\" style=\"z-index:" + (ch.utils.zIndex += 1) + "\">×</a>")
					.bind("click", function (event) {
						if ($(event.target || event.srcElement).hasClass("ch-close")) { 
							that.innerHide(event);
						}
					});
			}

			// It will close only with close button
			if (that.closable === "button") {
				return;
			}

			// Default Closable behavior
			// It will close with click on document, too
			that.on("show", function () {
				ch.utils.document.one("click", that.innerHide);
			});

			// Stop event propatation, if click container.
			that.$container.bind("click", function (event) {
				event.stopPropagation();
			});

			// and ESC key support
			ch.utils.document.bind(ch.events.KEY.ESC, function () {
				that.innerHide();
			});
		};

	})();

/**
* Protected Members
*/
	/**
	* Flag that indicates if the float is active and rendered on the DOM tree.
	* @protected
	* @name ch.Floats#active
	* @type boolean
	*/
	that.active = false;

	/**
	* It sets the hablity of auto close the component or indicate who closes the component.
	* @protected
	* @function
	* @name ch.Floats#closable
	* @type boolean | string
	*/
	that.closable = ch.utils.hasOwn(conf, "closable") ? conf.closable: true;

	/**
	* Content configuration property.
	* @protected
	* @name ch.Floats#source
	* @type string
	*/
	that.source = conf.content || conf.msg || conf.ajax || that.element.href || that.$element.parents("form").attr("action");

	/**
	* Inner function that resolves the component's layout and returns a static reference.
	* @protected
	* @name ch.Floats#$container
	* @type jQuery
	*/
	that.$container = (function () { // Create Layout
		
		// Final jQuery Object
		var $container,
		
		// Component with close button and keyboard binding for close
		//	closable = ch.utils.hasOwn(conf, "closeButton") && conf.closeButton,
		
		// HTML Div Element with role for WAI-ARIA
			container = ["<div role=\"" + conf.aria.role + "\""];
			
		// ID for WAI-ARIA
		if (ch.utils.hasOwn(conf.aria, "identifier")) {
			
			// Generated ID using component name and its instance order
			var id = "ch-" + that.type + "-" + (ch.utils.hasOwn(ch.instances, that.type) ? ch.instances[that.type].length + 1 : "1");
			
			// Add ID to container element
			container.push(" id=\"" + id + "\"");
			
			// Add aria attribute to trigger element
			that.$element.attr(conf.aria.identifier, id);
		}
		
		// Classname with component type and extra classes from conf.classes
		container.push(" class=\"ch-hide ch-" + that.type + (ch.utils.hasOwn(conf, "classes") ? " " + conf.classes : "") + "\"");
		
		// Z-index
		container.push(" style=\"z-index:" + (ch.utils.zIndex += 1) + ";");
		
		// Width
		if (ch.utils.hasOwn(conf, "width")) {
			container.push("width:" + conf.width + ((typeof conf.width === "number") ? "px;" : ";"));
		}
		
		// Height
		if (ch.utils.hasOwn(conf, "height")) {
			container.push("height:" + conf.height + ((typeof conf.height === "number") ? "px;" : ";"));
		}
		
		// Style and tag close
		container.push("\">");
		
		// Create cone
		if (ch.utils.html.hasClass("lt-ie8") && ch.utils.hasOwn(conf, "cone")) {
			container.push("<div class=\"ch-" + that.type + "-cone\"></div>");
		}

		// Create close button
		//if (closable) { container.push("<div class=\"btn close\" style=\"z-index:" + (ch.utils.zIndex += 1) + "\"></div>"); }
		
		// Tag close
		container.push("</div>");
		
		// jQuery Object generated from string
		$container = $(container.join(""));

		// Create cone
		if (ch.utils.hasOwn(conf, "cone")) { $container.addClass("ch-cone"); }
		
		// Close behavior bindings
		/*if (closable) {
			// Close button event delegation
			$container.bind("click", function (event) {
				if ($(event.target || event.srcElement).hasClass("close")) { that.innerHide(event); }
			});
			
			// ESC key support
			ch.utils.document.bind(ch.events.KEY.ESC, function (event) { that.innerHide(event); });
		}*/
		
		// Efects configuration
		conf.fx = ch.utils.hasOwn(conf, "fx") ? conf.fx : true;

		// Position component configuration
		conf.position = conf.position || {};
		conf.position.element = $container;
		conf.position.reposition = ch.utils.hasOwn(conf, "reposition") ? conf.reposition : true;

		// Initialize positioner component
		that.position = ch.positioner(conf.position);

		// Return the entire Layout
		return $container;
	})();

	/**
	* Inner reference to content container. Here is where the content will be added.
	* @protected
	* @name ch.Floats#$content
	* @type jQuery
	* @see ch.Object#content
	*/
	that.$content = $("<div class=\"ch-" + that.type + "-content\">").appendTo(that.$container);

	/**
	* This callback is triggered when async data is loaded into component's content, when ajax content comes back.
	* @protected
	* @function
	* @name ch.Floats#contentCallback
	* @returns itself
	*/
	that["public"].on("contentLoad", function (event, context) {
		that.$content.html(that.staticContent);

		if (ch.utils.hasOwn(conf, "onContentLoad")) {
			conf.onContentLoad.call(context, that.staticContent);
		}

		that.position("refresh");
	});

	/**
	* This callback is triggered when async request fails.
	* @protected
	* @name ch.Floats#contentError
	* @function
	* @returns {this}
	*/
	that["public"].on("contentError", function (event, data) {

		that.$content.html(that.staticContent);

		// Get the original that.source
		var originalSource = that.source;

		if (ch.utils.hasOwn(conf, "onContentError")) {
			conf.onContentError.call(data.context, data.jqXHR, data.textStatus, data.errorThrown);
		}

		// Reset content configuration
		that.source = originalSource;
		that.staticContent = undefined;

		if (ch.utils.hasOwn(conf, "position")) {
		   ch.positioner(conf.position);
		}

	});

	/**
	* Inner show method. Attach the component layout to the DOM tree.
	* @protected
	* @function
	* @name ch.Floats#innerShow
	* @returns itself
	*/
	that.innerShow = function (event) {
		if (event) {
			that.prevent(event);
		}

		// Avoid showing things that are already shown
		if (that.active) return;

		// Add layout to DOM tree
		// Increment zIndex
		that.$container
			.appendTo("body")
			.css("z-index", ch.utils.zIndex++);

		// This make a reflow, but we need that the static content appends to DOM
		// Get content
		that.content();

		/**
		* Triggers when component is visible.
		* @name ch.Floats#show
		* @event
		* @public
		* @exampleDescription It change the content when the component was shown.
		* @example
		* widget.on("show",function () {
		*	this.content("Some new content");
		* });
		* @see ch.Floats#show
		*/
		// Show component with effects
		if (conf.fx) {
			that.$container.fadeIn("fast", function () {
				
				that.$container.removeClass("ch-hide");
				// new callbacks
				that.trigger("show");
				// Old callback system
				that.callbacks('onShow');

			});
		} else {
		// Show component without effects
			that.$container.removeClass("ch-hide");
			// new callbacks
			that.trigger("show");
			// Old callback system
			that.callbacks('onShow');
		}

		that.position("refresh");
		
		that.active = true;

		return that;
	};

	/**
	* Inner hide method. Hides the component and detach it from DOM tree.
	* @protected
	* @function
	* @name ch.Floats#innerHide
	* @returns itself
	*/
	that.innerHide = function (event) {
		
		if (event) {
			event.stopPropagation();
		}

		if (!that.active) {
			return;
		}

		var afterHide = function () {

			that.active = false;

		/**
		* Triggers when component is not longer visible.
		* @name ch.Floats#hide
		* @event
		* @public
		* @exampleDescription When the component hides show other component.
		* @example
		* widget.on("hide",function () {
		*	otherComponent.show();
		* });
		*/
			// new callbacks
			that.trigger("hide");
			// Old callback system
			that.callbacks('onHide');

			that.$container.detach();

		};

		// Show component with effects
		if (conf.fx) {
			that.$container.fadeOut("fast", afterHide);

		// Show component without effects
		} else {
			that.$container.addClass("ch-hide");
			afterHide();
		}

		return that;

	};

	/**
	* Getter and setter for size attributes on any float component.
	* @protected
	* @function
	* @name ch.Floats#size
	* @param {String} prop Property that will be setted or getted, like "width" or "height".
	* @param {String} [data] Only for setter. It's the new value of defined property.
	* @returns itself
	*/
	that.size = function (prop, data) {
		// Getter
		if (!data) { return that.conf[prop]; }

		// Setter
		that.conf[prop] = data;

		// Container size
		that.$container[prop](data);

		// Refresh position
		that.position("refresh");

		return that["public"];
	};


/**
* Public Members
*/

	/**
	* @borrows ch.Object#on as ch.Floats#on
	*/

	/**
	* @borrows ch.Object#once as ch.Floats#once
	*/

	/**
	* @borrows ch.Object#off as ch.Floats#off
	*/

	//Documented again because the method works in this class
	/**
	* Sets and gets component content. To get the defined content just use the method without arguments, like 'widget.content()'. To define a new content pass an argument to it, like 'widget.content("new content")'. Use a valid URL to get content using AJAX. Use a CSS selector to get content from a DOM Element. Or just use a String with HTML code.
	* @public
	* @name ch.Uiobject#content
	* @function
	* @param {string} content Static content, DOM selector or URL. If argument is empty then will return the content.
	* @exampleDescription Get the defined content
	* @example
	* widget.content();
	* @exampleDescription Set static content
	* @example
	* widget.content("Some static content");
	* @exampleDescription Set DOM content
	* @example
	* widget.content("#hiddenContent");
	* @exampleDescription Set AJAX content
	* @example
	* widget.content("http://chico.com/some/content.html");
	*/

	/**
	* Triggers the innerShow method, returns the public scope to keep method chaining and sets new content if receive a parameter.
	* @public
	* @function
	* @name ch.Floats#show
	* @returns itself
	* @see ch.Floats#content
	*/
	that["public"].show = function (content) {
		if (content !== undefined) { that["public"].content(content); }
		that.innerShow();
		return that["public"];
	};

	/**
	* Triggers the innerHide method and returns the public scope to keep method chaining.
	* @public
	* @function
	* @name ch.Floats#hide
	* @returns itself
	*/
	that["public"].hide = function () {
		that.innerHide();
		return that["public"];
	};
	
	/**
	* Sets or gets positioning configuration. Use it without arguments to get actual configuration. Pass an argument to define a new positioning configuration.
	* @public
	* @function
	* @name ch.Floats#position
	* @exampleDescription Change component's position.
	* @example
	* widget.position({ 
	*	  offset: "0 10",
	*	  points: "lt lb"
	* });
	* @exampleDescription Refresh position.
	* @example
	* widget.position("refresh");
	* @see ch.Floats#position
	*/
	// Create a custom Positioner object to update conf.position data of Float family
	that["public"].position = function (o) {

		var r = that["public"];

		switch (typeof o) {
		// Custom Setter: It updates conf.position data
		case "object":
			// New points
			if (ch.utils.hasOwn(o, "points")) { conf.position.points = o.points; }

			// New reposition
			if (ch.utils.hasOwn(o, "reposition")) { conf.position.reposition = o.reposition; }

			// New offset (splitted)
			if (ch.utils.hasOwn(o, "offset")) { conf.position.offset = o.offset; }

			// New context
			if (ch.utils.hasOwn(o, "context")) { conf.position.context = o.context; }

			// Original Positioner
			that.position(conf.position);

			break;

		// Refresh
		case "string":
			that.position("refresh");
			
			break;

		// Getter
		case "undefined":
		default:
			r = that.position();
			
			break;
		}

		return r;
	};

	/**
	* Sets or gets the width property of the component's layout. Use it without arguments to get the value. To set a new value pass an argument, could be a Number or CSS value like '300' or '300px'.
	* @public
	* @function
	* @name ch.Floats#width
	* @param {Number|String} [width]
	* @returns itself
	* @see ch.Zarasa#size
	* @see ch.Floats#size
	* @exampleDescription to set the width
	* @example
	* widget.width(700);
	* @exampleDescription to get the width
	* @example
	* widget.width() // 700
	*/
	that["public"].width = function (data) {
		return that.size("width", data) || that["public"];
	};

	/**
	* Sets or gets the height of the Float element.
	* @public
	* @function
	* @name ch.Floats#height
	* @returns itself
	* @see ch.Floats#size
	* @exampleDescription to set the height
	* @example
	* widget.height(300);
	* @exampleDescription to get the height
	* @example
	* widget.height // 300
	*/
	that["public"].height = function (data) {
		return that.size("height", data) || that["public"];
	};

	/**
	* Returns a Boolean if the component's core behavior is active. That means it will return 'true' if the component is on and it will return false otherwise.
	* @public
	* @function
	* @name ch.Floats#isActive
	* @returns boolean
	*/
	that["public"].isActive = function () {
		return that.active;
	};
	
	/**
	* Triggers when the component is ready to use.
	* @name ch.Floats#ready
	* @event
	* @public
	* @exampleDescription Following the first example, using <code>widget</code> as modal's instance controller:
	* @example
	* widget.on("ready",function () {
	*	this.show();
	* });
	*/
	that.trigger("ready");

	/**
	*	Default behavior
	*/

	// Add Closable behavior
	closable();


	return that;

};

/**
* Modal is a centered floated window with a dark gray dimmer background. Modal lets you handle its size, positioning and content.
* @name Modal
* @class Modal
* @augments ch.Floats
* @memberOf ch
* @param {Object} [conf] Object with configuration properties.
* @param {String} [conf.content] Sets content by: static content, DOM selector or URL. By default, the content is the href attribute value  or form's action attribute.
* @param {Number || String} [conf.width] Sets width property of the component's layout. By default, the width is "500px".
* @param {Number || String} [conf.height] Sets height property of the component's layout. By default, the height is elastic.
* @param {Boolean} [conf.fx] Enable or disable UI effects. By default, the effects are enable.
* @param {Boolean} [conf.cache] Enable or disable the content cache. By default, the cache is enable.
* @param {String} [conf.closable] Sets the way (true, "button" or false) the Modal close. By default, the modal close true.
* @returns itself
* @factorized
* @see ch.Floats
* @see ch.Tooltip
* @see ch.Layer
* @see ch.Zoom
* @exampleDescription Create a new modal window triggered by an anchor with a class name 'example'.
* @example
* var widget = $("a.example").modal();
* @exampleDescription Create a new modal window triggered by form.
* @example
* var widget = $("form").modal();
* @exampleDescription Create a new modal window with configuration.
* @example
* var widget = $("a.example").modal({
*     "content": "Some content here!",
*     "width": "500px",
*     "height": 350,
*     "cache": false,
*     "fx": false
* });
* @exampleDescription Now <code>widget</code> is a reference to the modal instance controller. You can set a new content by using <code>widget</code> like this:
* @example
* widget.content("http://content.com/new/content");
*/

ch.modal = function (conf) {

	/**
	* Reference to a internal component instance, saves all the information and configuration properties.
	* @private
	* @name ch.Modal#that
	* @type object
	*/
	var that = this;
	conf = ch.clon(conf);

	conf.classes = conf.classes || "ch-box";
	conf.reposition = false;

	// Closable configuration
	conf.closeButton = ch.utils.hasOwn(conf, "closeButton") ? conf.closeButton : true;
	conf.closable = ch.utils.hasOwn(conf, "closable") ? conf.closable : true;
	
	conf.aria = {};
	
	if (conf.closeButton) {
		conf.aria.role = "dialog";
		conf.aria.identifier = "aria-label";
	} else {
		conf.aria.role = "alert";
	}
	
	that.conf = conf;

/**
*	Inheritance
*/

	that = ch.floats.call(that);
	that.parent = ch.clon(that);

/**
*	Private Members
*/

	/**
	* Reference to the dimmer object, the gray background element.
	* @private
	* @name ch.Modal#$dimmer
	* @type jQuery
	*/
	var $dimmer = $("<div class=\"ch-dimmer\">");

	// Set dimmer height for IE6
	if (ch.utils.html.hasClass("ie6")) { $dimmer.height(parseInt(document.documentElement.clientHeight, 10) * 3); }

	/**
	* Reference to dimmer control, turn on/off the dimmer object.
	* @private
	* @name ch.Modal#dimmer
	* @type object
	*/
	var dimmer = {
		on: function () {

			if (that.active) { return; }

			$dimmer
				.css("z-index", ch.utils.zIndex += 1)
				.appendTo(ch.utils.body)
				.fadeIn();

			/*if (that.type === "modal") {
				$dimmer.one("click", function (event) { that.innerHide(event) });
			}*/
			
			// TODO: position dimmer with Positioner
			if (!ch.features.fixed) {
			 	ch.positioner({ element: $dimmer });
			}

			if (ch.utils.html.hasClass("ie6")) {
				$("select, object").css("visibility", "hidden");
			}
		},
		off: function () {
			$dimmer.fadeOut("normal", function () {
				$dimmer.detach();
				if (ch.utils.html.hasClass("ie6")) {
					$("select, object").css("visibility", "visible");
				}
			});
		}
	};

/**
*	Protected Members
*/

	/**
	* Inner show method. Attach the component's layout to the DOM tree and load defined content.
	* @protected
	* @name ch.Modal#innerShow
	* @function
	* @returns itself
	*/
	that.innerShow = function (event) {
		dimmer.on();
		that.parent.innerShow(event);		
		that.$element.blur();
		return that;
	};

	/**
	* Inner hide method. Hides the component's layout and detach it from DOM tree.
	* @protected
	* @name ch.Modal#innerHide
	* @function
	* @returns itself
	*/
	that.innerHide = function (event) {
		dimmer.off();
		that.parent.innerHide(event);
		return that;
	};

	/**
	* Returns any if the component closes automatic. 
	* @protected
	* @name ch.Modal#closable
	* @function
	* @returns boolean
	*/

/**
*	Public Members
*/

	/**
	* @borrows ch.Object#uid as ch.Modal#uid
	*/	
	
	/**
	* @borrows ch.Object#element as ch.Modal#element
	*/

	/**
	* @borrows ch.Object#type as ch.Modal#type
	*/

	/**
	* @borrows ch.Uiobject#content as ch.Modal#content
	*/

	/**
	* @borrows ch.Floats#isActive as ch.Modal#isActive
	*/

	/**
	* @borrows ch.Floats#show as ch.Modal#show
	*/

	/**
	* @borrows ch.Floats#hide as ch.Modal#hide
	*/

	/**
	* @borrows ch.Floats#width as ch.Modal#width
	*/

	/**
	* @borrows ch.Floats#height as ch.Modal#height
	*/

	/**
	* @borrows ch.Floats#position as ch.Modal#position
	*/

	/**
	* @borrows ch.Floats#closable as ch.Modal#closable
	*/

/**
*	Default event delegation
*/

	if (that.element.tagName === "INPUT" && that.element.type === "submit") {
		that.$element.parents("form").bind("submit", function (event) { that.innerShow(event); });
	} else {
		that.$element.bind("click", function (event) { that.innerShow(event); });
	}

	/**
	* Triggers when the component is ready to use.
	* @name ch.Modal#ready
	* @event
	* @public
	* @example
	* // Following the first example, using <code>widget</code> as modal's instance controller:
	* widget.on("ready",function () {
	*	this.show();
	* });
	*/
	setTimeout(function(){ that.trigger("ready")}, 50);

	return that;
};

ch.factory("modal");


/**
* Transition lets you give feedback to the users when their have to wait for an action. 
* @name Transition
* @class Transition
* @interface
* @augments ch.Floats
* @requires ch.Modal
* @memberOf ch
* @param {Object} [conf] Object with configuration properties.
* @param {String} [conf.content] Sets content by: static content, DOM selector or URL. By default, the content is the href attribute value  or form's action attribute.
* @param {Number || String} [conf.width] Sets width property of the component's layout. By default, the width is "500px".
* @param {Number || String} [conf.height] Sets height property of the component's layout. By default, the height is elastic.
* @param {Boolean} [conf.fx] Enable or disable UI effects. By default, the effects are enable.
* @param {Boolean} [conf.cache] Enable or disable the content cache. By default, the cache is enable.
* @param {String} [conf.closable] Sets the way (true, "button" or false) the Transition close. By default, the transition close true.
* @returns itself
* @factorized
* @see ch.Tooltip
* @see ch.Layer
* @see ch.Zoom
* @see ch.Modal
* @see ch.Floats
* @exampleDescription Create a transition.
* @example
* var widget = $("a.example").transition();
* @exampleDescription Create a transition with configuration.
* @example
* var widget = $("a.example").transition({
*     "content": "Some content here!",
*     "width": "500px",
*     "height": 350,
*     "cache": false,
*     "fx": false
* });
*/

ch.extend("modal").as("transition", function (conf) {

	conf.closable = false;
	
	conf.msg = conf.msg || conf.content || "Please wait...";
	
	conf.content = $("<div class=\"ch-loading\"></div><p>" + conf.msg + "</p>");
	
	return conf;
});

/**
* Tooltip improves the native tooltips. Tooltip uses the 'alt' and 'title' attributes to grab its content.
* @name Tooltip
* @class Tooltip
* @augments ch.Floats
* @memberOf ch
* @param {Object} [conf] Object with configuration properties.
* @param {Boolean} [conf.fx] Enable or disable UI effects. By default, the effects are enable.
* @param {String} [conf.points] Sets the points where component will be positioned, specified by configuration or centered by default: "cm cm".
* @param {String} [conf.offset] Sets the offset in pixels that component will be displaced from original position determined by points. It's specified by configuration or zero by default: "0 0".
* @returns itself
* @factorized
* @see ch.Modal
* @see ch.Layer
* @see ch.Zoom
* @see ch.Flaots
* @exampleDescription Create a tooltip.
* @example
* var widget = $(".some-element").tooltip();
* @exampleDescription Create a new tooltip with configuration.
* @example
* var widget = $("a.example").tooltip({
*     "fx": false,
*     "offset": "10 -10",
*     "points": "lt rt"
* });
* @exampleDescription
* Now <code>widget</code> is a reference to the tooltip instance controller.
* You can set a new content by using <code>widget</code> like this: 
* @example
* widget.width(300);
*/

ch.tooltip = function (conf) {

	/**
	* Reference to a internal component instance, saves all the information and configuration properties.
	* @private
	* @name ch.Tooltip#that
	* @type object
	*/
	var that = this;

	conf = ch.clon(conf);

	conf.cone = true;
	conf.content = that.element.title || that.element.alt;
	
	// Closable configuration
	conf.closable = false;

	conf.aria = {};
	conf.aria.role = "tooltip";
	conf.aria.identifier = "aria-describedby";

	conf.position = {};
	conf.position.context = that.$element;
	conf.position.offset = conf.offset || "0 10";
	conf.position.points = conf.points || "lt lb";

	that.conf = conf;

/**
*	Inheritance
*/

	that = ch.floats.call(that);
	that.parent = ch.clon(that);

/**
*	Private Members
*/
	/**
	* The attribute that will provide the content. It can be "title" or "alt" attributes.
	* @protected
	* @name ch.Tooltip#attrReference
	* @type string
	*/
	var attrReference = (that.element.title) ? "title" : "alt",

	/**
	* The original attribute content.
	* @private
	* @name ch.Tooltip#attrContent
	* @type string
	*/
		attrContent = that.element.title || that.element.alt;

/**
*	Protected Members
*/

	/**
	* Inner show method. Attach the component layout to the DOM tree.
	* @protected
	* @name ch.Tooltip#innerShow
	* @function
	* @returns itself
	*/
	that.innerShow = function (event) {
	
		// Reset all tooltip, except me
		$.each(ch.instances.tooltip, function (i, e) {
			if (e !== that["public"]) {
				e.hide();
			}
		});
		
		// IE8 remembers the attribute even when is removed, so ... empty the attribute to fix the bug.
		that.element[attrReference] = "";

		that.parent.innerShow(event);

		return that;
	};

	/**
	* Inner hide method. Hides the component and detach it from DOM tree.
	* @protected
	* @name ch.Tooltip#innerHide
	* @function
	* @returns itself
	*/
	that.innerHide = function (event) {
		that.element[attrReference] = attrContent;

		that.parent.innerHide(event);

		return that;
	};

/**
*	Public Members
*/

	/**
	* @borrows ch.Object#uid as ch.Tooltip#uid
	*/	
	
	/**
	* @borrows ch.Object#element as ch.Tooltip#element
	*/

	/**
	* @borrows ch.Object#type as ch.Tooltip#type
	*/

	/**
	* @borrows ch.Object#content as ch.Tooltip#content
	*/

	/**
	* @borrows ch.Floats#isActive as ch.Tooltip#isActive
	*/

	/**
	* @borrows ch.Floats#show as ch.Tooltip#show
	*/

	/**
	* @borrows ch.Floats#hide as ch.Tooltip#hide
	*/

	/**
	* @borrows ch.Floats#width as ch.Tooltip#width
	*/

	/**
	* @borrows ch.Floats#height as ch.Tooltip#height
	*/

	/**
	* @borrows ch.Floats#position as ch.Tooltip#position
	*/

	/**
	* @borrows ch.Floats#closable as ch.Tooltip#closable
	*/

/**
*	Default event delegation
*/

	that.$element
		.bind("mouseenter", that.innerShow)
		.bind("mouseleave", that.innerHide);
	
	/**
	* Triggers when component is ready to use.
	* @name ch.Tooltip#ready
	* @event
	* @public
	* @example
	* // Following the first example, using <code>widget</code> as tooltip's instance controller:
	* widget.on("ready",function () {
	*	this.show();
	* });
	*/
	setTimeout(function(){ that.trigger("ready")}, 50);

	return that;
};

ch.factory("tooltip");

;ch.init();

})(jQuery);