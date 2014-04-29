// WCF.Combined.js -- DO NOT EDIT

// included files:
//  - WCF.js
//  - WCF.Like.js
//  - WCF.ACL.js
//  - WCF.Attachment.js
//  - WCF.ColorPicker.js
//  - WCF.Comment.js
//  - WCF.ImageViewer.js
//  - WCF.Label.js
//  - WCF.Location.js
//  - WCF.Message.js
//  - WCF.Moderation.js
//  - WCF.Poll.js
//  - WCF.Search.Message.js
//  - WCF.Tagging.js
//  - WCF.User.js

// WCF.js
/**
 * Class and function collection for WCF.
 * 
 * Major Contributors: Markus Bartz, Tim Duesterhus, Matthias Schmidt and Marcel Werk
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

(function() {
	// store original implementation
	var $jQueryData = jQuery.fn.data;
	
	/**
	 * Override jQuery.fn.data() to support custom 'ID' suffix which will
	 * be translated to '-id' at runtime.
	 * 
	 * @see	jQuery.fn.data()
	 */
	jQuery.fn.data = function(key, value) {
		if (key) {
			switch (typeof key) {
				case 'object':
					for (var $key in key) {
						if ($key.match(/ID$/)) {
							var $value = key[$key];
							delete key[$key];
							
							$key = $key.replace(/ID$/, '-id');
							key[$key] = $value;
						}
					}
					
					arguments[0] = key;
				break;
				
				case 'string':
					if (key.match(/ID$/)) {
						arguments[0] = key.replace(/ID$/, '-id');
					}
				break;
			} 
		}
		
		// call jQuery's own data method
		var $data = $jQueryData.apply(this, arguments);
		
		// handle .data() call without arguments
		if (key === undefined) {
			for (var $key in $data) {
				if ($key.match(/Id$/)) {
					$data[$key.replace(/Id$/, 'ID')] = $data[$key];
					delete $data[$key];
				}
			}
		}
		
		return $data;
	};
	
	// provide a sane window.console implementation
	if (!window.console) window.console = { };
	var consoleProperties = [ "log",/* "debug",*/ "info", "warn", "exception", "assert", "dir", "dirxml", "trace", "group", "groupEnd", "groupCollapsed", "profile", "profileEnd", "count", "clear", "time", "timeEnd", "timeStamp", "table", "error" ];
	for (var i = 0; i < consoleProperties.length; i++) {
		if (typeof (console[consoleProperties[i]]) === 'undefined') {
			console[consoleProperties[i]] = function () { };
		}
	}
	
	if (typeof(console.debug) === 'undefined') {
		// forward console.debug to console.log (IE9)
		console.debug = function(string) { console.log(string); };
	}
})();

/**
 * Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
(function(){var a=false,b=/xyz/.test(function(){xyz})?/\b_super\b/:/.*/;this.Class=function(){};Class.extend=function(c){function g(){if(!a&&this.init)this.init.apply(this,arguments);}var d=this.prototype;a=true;var e=new this;a=false;for(var f in c){e[f]=typeof c[f]=="function"&&typeof d[f]=="function"&&b.test(c[f])?function(a,b){return function(){var c=this._super;this._super=d[a];var e=b.apply(this,arguments);this._super=c;return e;};}(f,c[f]):c[f]}g.prototype=e;g.prototype.constructor=g;g.extend=arguments.callee;return g;};})();

/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas, David Knight. Dual MIT/BSD license */
window.matchMedia||(window.matchMedia=function(){"use strict";var e=window.styleMedia||window.media;if(!e){var t=document.createElement("style"),n=document.getElementsByTagName("script")[0],r=null;t.type="text/css";t.id="matchmediajs-test";n.parentNode.insertBefore(t,n);r="getComputedStyle"in window&&window.getComputedStyle(t,null)||t.currentStyle;e={matchMedium:function(e){var n="@media "+e+"{ #matchmediajs-test { width: 1px; } }";if(t.styleSheet){t.styleSheet.cssText=n}else{t.textContent=n}return r.width==="1px"}}}return function(t){return{matches:e.matchMedium(t||"all"),media:t||"all"}}}());

/*! matchMedia() polyfill addListener/removeListener extension. Author & copyright (c) 2012: Scott Jehl. Dual MIT/BSD license */
(function(){if(window.matchMedia&&window.matchMedia("all").addListener){return false}var e=window.matchMedia,t=e("only all").matches,n=false,r=0,i=[],s=function(t){clearTimeout(r);r=setTimeout(function(){for(var t=0,n=i.length;t<n;t++){var r=i[t].mql,s=i[t].listeners||[],o=e(r.media).matches;if(o!==r.matches){r.matches=o;for(var u=0,a=s.length;u<a;u++){s[u].call(window,r)}}}},30)};window.matchMedia=function(r){var o=e(r),u=[],a=0;o.addListener=function(e){if(!t){return}if(!n){n=true;window.addEventListener("resize",s,true)}if(a===0){a=i.push({mql:o,listeners:u})}u.push(e)};o.removeListener=function(e){for(var t=0,n=u.length;t<n;t++){if(u[t]===e){u.splice(t,1)}}};return o}})();

/*!
 * enquire.js v2.1.0 - Awesome Media Queries in JavaScript
 * Copyright (c) 2013 Nick Williams - http://wicky.nillia.ms/enquire.js
 * License: MIT (http://www.opensource.org/licenses/mit-license.php)
 */
(function(t,i,n){var e=i.matchMedia;"undefined"!=typeof module&&module.exports?module.exports=n(e):"function"==typeof define&&define.amd?define(function(){return i[t]=n(e)}):i[t]=n(e)})("enquire",this,function(t){"use strict";function i(t,i){var n,e=0,s=t.length;for(e;s>e&&(n=i(t[e],e),n!==!1);e++);}function n(t){return"[object Array]"===Object.prototype.toString.apply(t)}function e(t){return"function"==typeof t}function s(t){this.options=t,!t.deferSetup&&this.setup()}function o(i,n){this.query=i,this.isUnconditional=n,this.handlers=[],this.mql=t(i);var e=this;this.listener=function(t){e.mql=t,e.assess()},this.mql.addListener(this.listener)}function r(){if(!t)throw Error("matchMedia not present, legacy browsers require a polyfill");this.queries={},this.browserIsIncapable=!t("only all").matches}return s.prototype={setup:function(){this.options.setup&&this.options.setup(),this.initialised=!0},on:function(){!this.initialised&&this.setup(),this.options.match&&this.options.match()},off:function(){this.options.unmatch&&this.options.unmatch()},destroy:function(){this.options.destroy?this.options.destroy():this.off()},equals:function(t){return this.options===t||this.options.match===t}},o.prototype={addHandler:function(t){var i=new s(t);this.handlers.push(i),this.matches()&&i.on()},removeHandler:function(t){var n=this.handlers;i(n,function(i,e){return i.equals(t)?(i.destroy(),!n.splice(e,1)):void 0})},matches:function(){return this.mql.matches||this.isUnconditional},clear:function(){i(this.handlers,function(t){t.destroy()}),this.mql.removeListener(this.listener),this.handlers.length=0},assess:function(){var t=this.matches()?"on":"off";i(this.handlers,function(i){i[t]()})}},r.prototype={register:function(t,s,r){var h=this.queries,u=r&&this.browserIsIncapable;return h[t]||(h[t]=new o(t,u)),e(s)&&(s={match:s}),n(s)||(s=[s]),i(s,function(i){h[t].addHandler(i)}),this},unregister:function(t,i){var n=this.queries[t];return n&&(i?n.removeHandler(i):(n.clear(),delete this.queries[t])),this}},new r});

/*! head.load - v1.0.3 */
(function(n,t){"use strict";function w(){}function u(n,t){if(n){typeof n=="object"&&(n=[].slice.call(n));for(var i=0,r=n.length;i<r;i++)t.call(n,n[i],i)}}function it(n,i){var r=Object.prototype.toString.call(i).slice(8,-1);return i!==t&&i!==null&&r===n}function s(n){return it("Function",n)}function a(n){return it("Array",n)}function et(n){var i=n.split("/"),t=i[i.length-1],r=t.indexOf("?");return r!==-1?t.substring(0,r):t}function f(n){(n=n||w,n._done)||(n(),n._done=1)}function ot(n,t,r,u){var f=typeof n=="object"?n:{test:n,success:!t?!1:a(t)?t:[t],failure:!r?!1:a(r)?r:[r],callback:u||w},e=!!f.test;return e&&!!f.success?(f.success.push(f.callback),i.load.apply(null,f.success)):e||!f.failure?u():(f.failure.push(f.callback),i.load.apply(null,f.failure)),i}function v(n){var t={},i,r;if(typeof n=="object")for(i in n)!n[i]||(t={name:i,url:n[i]});else t={name:et(n),url:n};return(r=c[t.name],r&&r.url===t.url)?r:(c[t.name]=t,t)}function y(n){n=n||c;for(var t in n)if(n.hasOwnProperty(t)&&n[t].state!==l)return!1;return!0}function st(n){n.state=ft;u(n.onpreload,function(n){n.call()})}function ht(n){n.state===t&&(n.state=nt,n.onpreload=[],rt({url:n.url,type:"cache"},function(){st(n)}))}function ct(){var n=arguments,t=n[n.length-1],r=[].slice.call(n,1),f=r[0];return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(f?(u(r,function(n){s(n)||!n||ht(v(n))}),b(v(n[0]),s(f)?f:function(){i.load.apply(null,r)})):b(v(n[0])),i)}function lt(){var n=arguments,t=n[n.length-1],r={};return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(u(n,function(n){n!==t&&(n=v(n),r[n.name]=n)}),u(n,function(n){n!==t&&(n=v(n),b(n,function(){y(r)&&f(t)}))}),i)}function b(n,t){if(t=t||w,n.state===l){t();return}if(n.state===tt){i.ready(n.name,t);return}if(n.state===nt){n.onpreload.push(function(){b(n,t)});return}n.state=tt;rt(n,function(){n.state=l;t();u(h[n.name],function(n){f(n)});o&&y()&&u(h.ALL,function(n){f(n)})})}function at(n){n=n||"";var t=n.split("?")[0].split(".");return t[t.length-1].toLowerCase()}function rt(t,i){function e(t){t=t||n.event;u.onload=u.onreadystatechange=u.onerror=null;i()}function o(f){f=f||n.event;(f.type==="load"||/loaded|complete/.test(u.readyState)&&(!r.documentMode||r.documentMode<9))&&(n.clearTimeout(t.errorTimeout),n.clearTimeout(t.cssTimeout),u.onload=u.onreadystatechange=u.onerror=null,i())}function s(){if(t.state!==l&&t.cssRetries<=20){for(var i=0,f=r.styleSheets.length;i<f;i++)if(r.styleSheets[i].href===u.href){o({type:"load"});return}t.cssRetries++;t.cssTimeout=n.setTimeout(s,250)}}var u,h,f;i=i||w;h=at(t.url);h==="css"?(u=r.createElement("link"),u.type="text/"+(t.type||"css"),u.rel="stylesheet",u.href=t.url,t.cssRetries=0,t.cssTimeout=n.setTimeout(s,500)):(u=r.createElement("script"),u.type="text/"+(t.type||"javascript"),u.src=t.url);u.onload=u.onreadystatechange=o;u.onerror=e;u.async=!1;u.defer=!1;t.errorTimeout=n.setTimeout(function(){e({type:"timeout"})},7e3);f=r.head||r.getElementsByTagName("head")[0];f.insertBefore(u,f.lastChild)}function vt(){for(var t,u=r.getElementsByTagName("script"),n=0,f=u.length;n<f;n++)if(t=u[n].getAttribute("data-headjs-load"),!!t){i.load(t);return}}function yt(n,t){var v,p,e;return n===r?(o?f(t):d.push(t),i):(s(n)&&(t=n,n="ALL"),a(n))?(v={},u(n,function(n){v[n]=c[n];i.ready(n,function(){y(v)&&f(t)})}),i):typeof n!="string"||!s(t)?i:(p=c[n],p&&p.state===l||n==="ALL"&&y()&&o)?(f(t),i):(e=h[n],e?e.push(t):e=h[n]=[t],i)}function e(){if(!r.body){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(e,50);return}o||(o=!0,vt(),u(d,function(n){f(n)}))}function k(){r.addEventListener?(r.removeEventListener("DOMContentLoaded",k,!1),e()):r.readyState==="complete"&&(r.detachEvent("onreadystatechange",k),e())}var r=n.document,d=[],h={},c={},ut="async"in r.createElement("script")||"MozAppearance"in r.documentElement.style||n.opera,o,g=n.head_conf&&n.head_conf.head||"head",i=n[g]=n[g]||function(){i.ready.apply(null,arguments)},nt=1,ft=2,tt=3,l=4,p;if(r.readyState==="complete")e();else if(r.addEventListener)r.addEventListener("DOMContentLoaded",k,!1),n.addEventListener("load",e,!1);else{r.attachEvent("onreadystatechange",k);n.attachEvent("onload",e);p=!1;try{p=!n.frameElement&&r.documentElement}catch(wt){}p&&p.doScroll&&function pt(){if(!o){try{p.doScroll("left")}catch(t){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(pt,50);return}e()}}()}i.load=i.js=ut?lt:ct;i.test=ot;i.ready=yt;i.ready(r,function(){y()&&u(h.ALL,function(n){f(n)});i.feature&&i.feature("domloaded",!0)})})(window);
/*
//# sourceMappingURL=head.load.min.js.map
*/

/**
 * Provides a hashCode() method for strings, similar to Java's String.hashCode().
 * 
 * @see	http://werxltd.com/wp/2010/05/13/javascript-implementation-of-javas-string-hashcode-method/
 */
String.prototype.hashCode = function() {
	var $char;
	var $hash = 0;
	
	if (this.length) {
		for (var $i = 0, $length = this.length; $i < $length; $i++) {
			$char = this.charCodeAt($i);
			$hash = (($hash << 5) - $hash) + $char;
			$hash = $hash & $hash; // convert to 32bit integer
		}
	}
	
	return $hash;
};

/**
 * Adds a Fisher-Yates shuffle algorithm for arrays.
 * 
 * @see	http://stackoverflow.com/a/2450976
 */
function shuffle(array) {
	var currentIndex = array.length, temporaryValue, randomIndex;
	
	// While there remain elements to shuffle...
	while (0 !== currentIndex) {
		// Pick a remaining element...
		randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;
		
		// And swap it with the current element.
		temporaryValue = array[currentIndex];
		array[currentIndex] = array[randomIndex];
		array[randomIndex] = temporaryValue;
	}
	
	return this;
};

/**
 * User-Agent based browser detection and touch detection.
 */
(function() {
	var ua = navigator.userAgent.toLowerCase();
	var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
		/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
		/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
		/(msie) ([\w.]+)/.exec( ua ) ||
		ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
		[];
	
	var matched = {
		browser: match[ 1 ] || "",
		version: match[ 2 ] || "0"
	};
	browser = {};
	
	if ( matched.browser ) {
		browser[ matched.browser ] = true;
		browser.version = matched.version;
	}
	
	// Chrome is Webkit, but Webkit is also Safari.
	if ( browser.chrome ) {
		browser.webkit = true;
	} else if ( browser.webkit ) {
		browser.safari = true;
	}
	
	jQuery.browser = browser;
	jQuery.browser.touch = (!!('ontouchstart' in window) || (!!('msMaxTouchPoints' in window.navigator) && window.navigator.msMaxTouchPoints > 0));
	
	// detect smartphones
	jQuery.browser.smartphone = ($('html').css('caption-side') == 'bottom');
	
	// allow plugins to detect the used editor, value should be the same as the $.browser.<editorName> key
	jQuery.browser.editor = 'redactor';
	
	// CKEditor support (removed in WCF 2.1), do NOT remove this variable for the sake for compatibility
	jQuery.browser.ckeditor = false;
	
	// Redactor support
	jQuery.browser.redactor = true;
	
	// properly detect IE11
	if (jQuery.browser.mozilla && ua.match(/trident/)) {
		jQuery.browser.mozilla = false;
		jQuery.browser.msie = true;
	}
})();

/**
 * jQuery.browser.mobile (http://detectmobilebrowser.com/)
 *
 * jQuery.browser.mobile will be true if the browser is a mobile device
 *
 **/
(function(a){(jQuery.browser=jQuery.browser||{}).mobile=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);

/**
 * Initialize WCF namespace
 */
var WCF = {};

/**
 * Extends jQuery with additional methods.
 */
$.extend(true, {
	/**
	 * Removes the given value from the given array and returns the array.
	 * 
	 * @param	array		array
	 * @param	mixed		element
	 * @return	array
	 */
	removeArrayValue: function(array, value) {
		return $.grep(array, function(element, index) {
			return value !== element;
		});
	},
	
	/**
	 * Escapes an ID to work with jQuery selectors.
	 * 
	 * @see		http://docs.jquery.com/Frequently_Asked_Questions#How_do_I_select_an_element_by_an_ID_that_has_characters_used_in_CSS_notation.3F
	 * @param	string		id
	 * @return	string
	 */
	wcfEscapeID: function(id) {
		return id.replace(/(:|\.)/g, '\\$1');
	},
	
	/**
	 * Returns true if given ID exists within DOM.
	 * 
	 * @param	string		id
	 * @return	boolean
	 */
	wcfIsset: function(id) {
		return !!$('#' + $.wcfEscapeID(id)).length;
	},
	
	/**
	 * Returns the length of an object.
	 * 
	 * @param	object		targetObject
	 * @return	integer
	 */
	getLength: function(targetObject) {
		var $length = 0;
		
		for (var $key in targetObject) {
			if (targetObject.hasOwnProperty($key)) {
				$length++;
			}
		}
		
		return $length;
	}
});

/**
 * Extends jQuery's chainable methods.
 */
$.fn.extend({
	/**
	 * Returns tag name of first jQuery element.
	 * 
	 * @returns	string
	 */
	getTagName: function() {
		return (this.length) ? this.get(0).tagName.toLowerCase() : '';
	},
	
	/**
	 * Returns the dimensions for current element.
	 * 
	 * @see		http://api.jquery.com/hidden-selector/
	 * @param	string		type
	 * @return	object
	 */
	getDimensions: function(type) {
		var dimensions = css = {};
		var wasHidden = false;
		
		// show element to retrieve dimensions and restore them later
		if (this.is(':hidden')) {
			css = WCF.getInlineCSS(this);
			
			wasHidden = true;
			
			this.css({
				display: 'block',
				visibility: 'hidden'
			});
		}
		
		switch (type) {
			case 'inner':
				dimensions = {
					height: this.innerHeight(),
					width: this.innerWidth()
				};
			break;
			
			case 'outer':
				dimensions = {
					height: this.outerHeight(),
					width: this.outerWidth()
				};
			break;
			
			default:
				dimensions = {
					height: this.height(),
					width: this.width()
				};
			break;
		}
		
		// restore previous settings
		if (wasHidden) {
			WCF.revertInlineCSS(this, css, [ 'display', 'visibility' ]);
		}
		
		return dimensions;
	},
	
	/**
	 * Returns the offsets for current element, defaults to position
	 * relative to document.
	 * 
	 * @see		http://api.jquery.com/hidden-selector/
	 * @param	string		type
	 * @return	object
	 */
	getOffsets: function(type) {
		var offsets = css = {};
		var wasHidden = false;
		
		// show element to retrieve dimensions and restore them later
		if (this.is(':hidden')) {
			css = WCF.getInlineCSS(this);
			wasHidden = true;
			
			this.css({
				display: 'block',
				visibility: 'hidden'
			});
		}
		
		switch (type) {
			case 'offset':
				offsets = this.offset();
			break;
			
			case 'position':
			default:
				offsets = this.position();
			break;
		}
		
		// restore previous settings
		if (wasHidden) {
			WCF.revertInlineCSS(this, css, [ 'display', 'visibility' ]);
		}
		
		return offsets;
	},
	
	/**
	 * Changes element's position to 'absolute' or 'fixed' while maintaining it's
	 * current position relative to viewport. Optionally removes element from
	 * current DOM-node and moving it into body-element (useful for drag & drop)
	 * 
	 * @param	boolean		rebase
	 * @return	object
	 */
	makePositioned: function(position, rebase) {
		if (position != 'absolute' && position != 'fixed') {
			position = 'absolute';
		}
		
		var $currentPosition = this.getOffsets('position');
		this.css({
			position: position,
			left: $currentPosition.left,
			margin: 0,
			top: $currentPosition.top
		});
		
		if (rebase) {
			this.remove().appentTo('body');
		}
		
		return this;
	},
	
	/**
	 * Disables a form element.
	 * 
	 * @return	jQuery
	 */
	disable: function() {
		return this.attr('disabled', 'disabled');
	},
	
	/**
	 * Enables a form element.
	 * 
	 * @return	jQuery
	 */
	enable: function() {
		return this.removeAttr('disabled');
	},
	
	/**
	 * Returns the element's id. If none is set, a random unique
	 * ID will be assigned.
	 * 
	 * @return	string
	 */
	wcfIdentify: function() {
		if (!this.attr('id')) {
			this.attr('id', WCF.getRandomID());
		}
		
		return this.attr('id');
	},
	
	/**
	 * Returns the caret position of current element. If the element
	 * does not equal input[type=text], input[type=password] or
	 * textarea, -1 is returned.
	 * 
	 * @return	integer
	 */
	getCaret: function() {
		if (this.is('input')) {
			if (this.attr('type') != 'text' && this.attr('type') != 'password') {
				return -1;
			}
		}
		else if (!this.is('textarea')) {
			return -1;
		}
		
		var $position = 0;
		var $element = this.get(0);
		if (document.selection) { // IE 8
			// set focus to enable caret on this element
			this.focus();
			
			var $selection = document.selection.createRange();
			$selection.moveStart('character', -this.val().length);
			$position = $selection.text.length;
		}
		else if ($element.selectionStart || $element.selectionStart == '0') { // Opera, Chrome, Firefox, Safari, IE 9+
			$position = parseInt($element.selectionStart);
		}
		
		return $position;
	},
	
	/**
	 * Sets the caret position of current element. If the element
	 * does not equal input[type=text], input[type=password] or
	 * textarea, false is returned.
	 * 
	 * @param	integer		position
	 * @return	boolean
	 */
	setCaret: function (position) {
		if (this.is('input')) {
			if (this.attr('type') != 'text' && this.attr('type') != 'password') {
				return false;
			}
		}
		else if (!this.is('textarea')) {
			return false;
		}
		
		var $element = this.get(0);
		
		// set focus to enable caret on this element
		this.focus();
		if (document.selection) { // IE 8
			var $selection = document.selection.createRange();
			$selection.moveStart('character', position);
			$selection.moveEnd('character', 0);
			$selection.select();
		}
		else if ($element.selectionStart || $element.selectionStart == '0') { // Opera, Chrome, Firefox, Safari, IE 9+
			$element.selectionStart = position;
			$element.selectionEnd = position;
		}
		
		return true;
	},
	
	/**
	 * Shows an element by sliding and fading it into viewport.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfDropIn: function(direction, callback, duration) {
		if (!direction) direction = 'up';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.show(WCF.getEffect(this, 'drop'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Hides an element by sliding and fading it out the viewport.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfDropOut: function(direction, callback, duration) {
		if (!direction) direction = 'down';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.hide(WCF.getEffect(this, 'drop'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Shows an element by blinding it up.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfBlindIn: function(direction, callback, duration) {
		if (!direction) direction = 'vertical';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.show(WCF.getEffect(this, 'blind'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Hides an element by blinding it down.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfBlindOut: function(direction, callback, duration) {
		if (!direction) direction = 'vertical';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.hide(WCF.getEffect(this, 'blind'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Highlights an element.
	 * 
	 * @param	object		options
	 * @param	object		callback
	 * @returns	jQuery
	 */
	wcfHighlight: function(options, callback) {
		return this.effect('highlight', options, 600, callback);
	},
	
	/**
	 * Shows an element by fading it in.
	 * 
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfFadeIn: function(callback, duration) {
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.show(WCF.getEffect(this, 'fade'), { }, duration, callback);
	},
	
	/**
	 * Hides an element by fading it out.
	 * 
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfFadeOut: function(callback, duration) {
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.hide(WCF.getEffect(this, 'fade'), { }, duration, callback);
	}
});

/**
 * WoltLab Community Framework core methods
 */
$.extend(WCF, {
	/**
	 * count of active dialogs
	 * @var	integer
	 */
	activeDialogs: 0,
	
	/**
	 * Counter for dynamic element ids
	 * 
	 * @var	integer
	 */
	_idCounter: 0,
	
	/**
	 * Returns a dynamically created id.
	 * 
	 * @see		https://github.com/sstephenson/prototype/blob/5e5cfff7c2c253eaf415c279f9083b4650cd4506/src/prototype/dom/dom.js#L1789
	 * @return	string
	 */
	getRandomID: function() {
		var $elementID = '';
		
		do {
			$elementID = 'wcf' + this._idCounter++;
		}
		while ($.wcfIsset($elementID));
		
		return $elementID;
	},
	
	/**
	 * Wrapper for $.inArray which returns boolean value instead of
	 * index value, similar to PHP's in_array().
	 * 
	 * @param	mixed		needle
	 * @param	array		haystack
	 * @return	boolean
	 */
	inArray: function(needle, haystack) {
		return ($.inArray(needle, haystack) != -1);
	},
	
	/**
	 * Adjusts effect for partially supported elements.
	 * 
	 * @param	jQuery		object
	 * @param	string		effect
	 * @return	string
	 */
	getEffect: function(object, effect) {
		// most effects are not properly supported on table rows, use highlight instead
		if (object.is('tr')) {
			return 'highlight';
		}
		
		return effect;
	},
	
	/**
	 * Returns inline CSS for given element.
	 * 
	 * @param	jQuery		element
	 * @return	object
	 */
	getInlineCSS: function(element) {
		var $inlineStyles = { };
		var $style = element.attr('style');
		
		// no style tag given or empty
		if (!$style) {
			return { };
		}
		
		$style = $style.split(';');
		for (var $i = 0, $length = $style.length; $i < $length; $i++) {
			var $fragment = $.trim($style[$i]);
			if ($fragment == '') {
				continue;
			}
			
			$fragment = $fragment.split(':');
			$inlineStyles[$.trim($fragment[0])] = $.trim($fragment[1]);
		}
		
		return $inlineStyles;
	},
	
	/**
	 * Reverts inline CSS or negates a previously set property.
	 * 
	 * @param	jQuery		element
	 * @param	object		inlineCSS
	 * @param	array<string>	targetProperties
	 */
	revertInlineCSS: function(element, inlineCSS, targetProperties) {
		for (var $i = 0, $length = targetProperties.length; $i < $length; $i++) {
			var $property = targetProperties[$i];
			
			// revert inline CSS
			if (inlineCSS[$property]) {
				element.css($property, inlineCSS[$property]);
			}
			else {
				// negate inline CSS
				element.css($property, '');
			}
		}
	}
});

/**
 * Browser related functions.
 */
WCF.Browser = {
	/**
	 * determines if browser is chrome
	 * @var	boolean
	 */
	_isChrome: null,
	
	/**
	 * Returns true, if browser is Chrome, Chromium or using GoogleFrame for Internet Explorer.
	 * 
	 * @return	boolean
	 */
	isChrome: function() {
		if (this._isChrome === null) {
			this._isChrome = false;
			if (/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())) {
				this._isChrome = true;
			}
		}
		
		return this._isChrome;
	}
};

/**
 * Dropdown API
 */
WCF.Dropdown = {
	/**
	 * list of callbacks
	 * @var	object
	 */
	_callbacks: { },
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * list of registered dropdowns
	 * @var	object
	 */
	_dropdowns: { },
	
	/**
	 * container for dropdown menus
	 * @var	object
	 */
	_menuContainer: null,
	
	/**
	 * list of registered dropdown menus
	 * @var	object
	 */
	_menus: { },
	
	/**
	 * Initializes dropdowns.
	 */
	init: function() {
		if (this._menuContainer === null) {
			this._menuContainer = $('<div id="dropdownMenuContainer" />').appendTo(document.body);
		}
		
		var self = this;
		$('.dropdownToggle:not(.jsDropdownEnabled)').each(function(index, button) {
			self.initDropdown($(button), false);
		});
		
		if (!this._didInit) {
			this._didInit = true;
			
			WCF.CloseOverlayHandler.addCallback('WCF.Dropdown', $.proxy(this._closeAll, this));
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Dropdown', $.proxy(this.init, this));
			$(document).on('scroll', $.proxy(this._scroll, this));
		}
	},
	
	/**
	 * Handles dropdown positions in overlays when scrolling in the overlay.
	 * 
	 * @param	object		event
	 */
	_dialogScroll: function(event) {
		var $dialogContent = $(event.currentTarget);
		$dialogContent.find('.dropdown.dropdownOpen').each(function(index, element) {
			var $dropdown = $(element);
			var $dropdownID = $dropdown.wcfIdentify();
			var $dropdownOffset = $dropdown.offset();
			var $dialogContentOffset = $dialogContent.offset();
			
			var $verticalScrollTolerance = $(element).height() / 2;
			
			// check if dropdown toggle is still (partially) visible
			if ($dropdownOffset.top + $verticalScrollTolerance <= $dialogContentOffset.top) {
				// top check
				WCF.Dropdown.toggleDropdown($dropdownID);
			}
			else if ($dropdownOffset.top >= $dialogContentOffset.top + $dialogContent.height()) {
				// bottom check
				WCF.Dropdown.toggleDropdown($dropdownID);
			}
			else if ($dropdownOffset.left <= $dialogContentOffset.left) {
				// left check
				WCF.Dropdown.toggleDropdown($dropdownID);
			}
			else if ($dropdownOffset.left >= $dialogContentOffset.left + $dialogContent.width()) {
				// right check
				WCF.Dropdown.toggleDropdown($dropdownID);
			}
			else {
				WCF.Dropdown.setAlignmentByID($dropdown.wcfIdentify());
			}
		});
	},
	
	/**
	 * Handles dropdown positions in overlays when scrolling in the document.
	 * 
	 * @param	object		event
	 */
	_scroll: function(event) {
		for (var $containerID in this._dropdowns) {
			var $dropdown = this._dropdowns[$containerID];
			if ($dropdown.data('isOverlayDropdownButton') && $dropdown.hasClass('dropdownOpen')) {
				this.setAlignmentByID($containerID);
			}
		}
	},
	
	/**
	 * Initializes a dropdown.
	 * 
	 * @param	jQuery		button
	 * @param	boolean		isLazyInitialization
	 */
	initDropdown: function(button, isLazyInitialization) {
		if (button.hasClass('jsDropdownEnabled') || button.data('target')) {
			return;
		}
		
		var $dropdown = button.parents('.dropdown');
		if (!$dropdown.length) {
			// broken dropdown, ignore
			console.debug("[WCF.Dropdown] Invalid dropdown passed, button '" + button.wcfIdentify() + "' does not have a parent with .dropdown, aborting.");
			return;
		}
		
		var $dropdownMenu = button.next('.dropdownMenu');
		if (!$dropdownMenu.length) {
			// broken dropdown, ignore
			console.debug("[WCF.Dropdown] Invalid dropdown passed, dropdown '" + $dropdown.wcfIdentify() + "' does not have a dropdown menu, aborting.");
			return;
		}
		
		$dropdownMenu.detach().appendTo(this._menuContainer);
		var $containerID = $dropdown.wcfIdentify();
		if (!this._dropdowns[$containerID]) {
			button.addClass('jsDropdownEnabled').click($.proxy(this._toggle, this));
			
			this._dropdowns[$containerID] = $dropdown;
			this._menus[$containerID] = $dropdownMenu;
		}
		
		button.data('target', $containerID);
		
		if (isLazyInitialization) {
			button.trigger('click');
		}
	},
	
	/**
	 * Removes the dropdown with the given container id.
	 * 
	 * @param	string		containerID
	 */
	removeDropdown: function(containerID) {
		if (this._menus[containerID]) {
			$(this._menus[containerID]).remove();
			delete this._menus[containerID];
			delete this._dropdowns[containerID];
		}
	},
	
	/**
	 * Initializes a dropdown fragment which behaves like a usual dropdown
	 * but is not controlled by a trigger element.
	 * 
	 * @param	jQuery		dropdown
	 * @param	jQuery		dropdownMenu
	 */
	initDropdownFragment: function(dropdown, dropdownMenu) {
		var $containerID = dropdown.wcfIdentify();
		if (this._dropdowns[$containerID]) {
			console.debug("[WCF.Dropdown] Cannot register dropdown identified by '" + $containerID + "' as a fragement.");
			return;
		}
		
		this._dropdowns[$containerID] = dropdown;
		this._menus[$containerID] = dropdownMenu.detach().appendTo(this._menuContainer);
	},
	
	/**
	 * Registers a callback notified upon dropdown state change.
	 * 
	 * @param	string		identifier
	 * @var		object		callback
	 */
	registerCallback: function(identifier, callback) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.Dropdown] Callback for '" + identifier + "' is invalid");
			return false;
		}
		
		if (!this._callbacks[identifier]) {
			this._callbacks[identifier] = [ ];
		}
		
		this._callbacks[identifier].push(callback);
	},
	
	/**
	 * Toggles a dropdown.
	 * 
	 * @param	object		event
	 * @param	string		targetID
	 */
	_toggle: function(event, targetID) {
		var $targetID = (event === null) ? targetID : $(event.currentTarget).data('target');
		
		// check if 'isOverlayDropdownButton' is set which indicates if
		// the dropdown toggle is in an overlay
		var $target = this._dropdowns[$targetID];
		if ($target && $target.data('isOverlayDropdownButton') === undefined) {
			var $dialogContent = $target.parents('.dialogContent');
			$target.data('isOverlayDropdownButton', $dialogContent.length > 0);
			
			if ($dialogContent.length) {
				$dialogContent.on('scroll', this._dialogScroll);
			}
		}
		
		// close all dropdowns
		for (var $containerID in this._dropdowns) {
			var $dropdown = this._dropdowns[$containerID];
			var $dropdownMenu = this._menus[$containerID];
			
			if ($dropdown.hasClass('dropdownOpen')) {
				$dropdown.removeClass('dropdownOpen');
				$dropdownMenu.removeClass('dropdownOpen');
				
				this._notifyCallbacks($containerID, 'close');
			}
			else if ($containerID === $targetID) {
				$dropdown.addClass('dropdownOpen');
				$dropdownMenu.addClass('dropdownOpen');
				
				this._notifyCallbacks($containerID, 'open');
				
				this.setAlignment($dropdown, $dropdownMenu);
			}
		}
		
		if (event !== null) {
			event.stopPropagation();
			return false;
		}
	},
	
	/**
	 * Toggles a dropdown.
	 * 
	 * @param	string		containerID
	 */
	toggleDropdown: function(containerID) {
		this._toggle(null, containerID);
	},
	
	/**
	 * Returns dropdown by container id.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	getDropdown: function(containerID) {
		if (this._dropdowns[containerID]) {
			return this._dropdowns[containerID];
		}
		
		return null;
	},
	
	/**
	 * Returns dropdown menu by container id.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	getDropdownMenu: function(containerID) {
		if (this._menus[containerID]) {
			return this._menus[containerID];
		}
		
		return null;
	},
	
	/**
	 * Sets alignment for given container id.
	 * 
	 * @param	string		containerID
	 */
	setAlignmentByID: function(containerID) {
		var $dropdown = this.getDropdown(containerID);
		if ($dropdown === null) {
			console.debug("[WCF.Dropdown] Unable to find dropdown identified by '" + containerID + "'");
		}
		
		var $dropdownMenu = this.getDropdownMenu(containerID);
		if ($dropdownMenu === null) {
			console.debug("[WCF.Dropdown] Unable to find dropdown menu identified by '" + containerID + "'");
		}
		
		this.setAlignment($dropdown, $dropdownMenu);
	},
	
	/**
	 * Sets alignment for dropdown.
	 * 
	 * @param	jQuery		dropdown
	 * @param	jQuery		dropdownMenu
	 */
	setAlignment: function(dropdown, dropdownMenu) {
		// force dropdown menu to be placed in the upper left corner, otherwise
		// it might cause the calculations to be a bit off if the page exceeds
		// the window boundaries during getDimensions() making it visible
		if (!dropdownMenu.data('isInitialized')) {
			dropdownMenu.data('isInitialized', true).css({ left: 0, top: 0 });
		}
		
		// get dropdown position
		var $dropdownDimensions = dropdown.getDimensions('outer');
		var $dropdownOffsets = dropdown.getOffsets('offset');
		var $menuDimensions = dropdownMenu.getDimensions('outer');
		var $windowWidth = $(window).width();
		
		// check if button belongs to an i18n textarea
		var $button = dropdown.find('.dropdownToggle');
		if ($button.hasClass('dropdownCaptionTextarea')) {
			// use button dimensions instead
			$dropdownDimensions = $button.getDimensions('outer');
		}
		
		// get alignment
		var $align = 'left';
		if (($dropdownOffsets.left + $menuDimensions.width) > $windowWidth) {
			$align = 'right';
		}
		
		// calculate offsets
		var $left = 'auto';
		var $right = 'auto';
		if ($align === 'left') {
			dropdownMenu.removeClass('dropdownArrowRight');
			
			$left = $dropdownOffsets.left;
		}
		else {
			dropdownMenu.addClass('dropdownArrowRight');
			
			$right = ($windowWidth - ($dropdownOffsets.left + $dropdownDimensions.width));
		}
		
		// rtl works the same with the exception that we need to offset it with the right boundary
		if (WCF.Language.get('wcf.global.pageDirection') == 'rtl') {
			var $oldLeft = $left;
			var $oldRight = $right;
			
			// use reverse positioning
			if ($left == 'auto') {
				dropdownMenu.removeClass('dropdownArrowRight');
			}
			else {
				$right = $windowWidth - ($dropdownOffsets.left + $dropdownDimensions.width);
				$left = 'auto';
				
				if ($right + $menuDimensions.width > $windowWidth) {
					// exceeded window width, restore ltr values
					$left = $oldLeft;
					$right = $oldRight;
					
					dropdownMenu.addClass('dropdownArrowRight');
				}
			}
		}
		
		if ($left == 'auto') $right += 'px';
		else $left += 'px';
		
		// calculate vertical offset
		var $wasHidden = true;
		if (dropdownMenu.hasClass('dropdownOpen')) {
			$wasHidden = false;
			dropdownMenu.removeClass('dropdownOpen');
		}
		
		var $bottom = 'auto';
		var $top = $dropdownOffsets.top + $dropdownDimensions.height + 7;
		if ($top + $menuDimensions.height > $(window).height() + $(document).scrollTop()) {
			$bottom = $(window).height() - $dropdownOffsets.top + 10;
			$top = 'auto';
			
			dropdownMenu.addClass('dropdownArrowBottom');
		}
		else {
			dropdownMenu.removeClass('dropdownArrowBottom');
		}
		
		if (!$wasHidden) {
			dropdownMenu.addClass('dropdownOpen');
		}
		
		dropdownMenu.css({
			bottom: $bottom,
			left: $left,
			right: $right,
			top: $top
		});
	},
	
	/**
	 * Closes all dropdowns.
	 */
	_closeAll: function() {
		for (var $containerID in this._dropdowns) {
			var $dropdown = this._dropdowns[$containerID];
			if ($dropdown.hasClass('dropdownOpen')) {
				$dropdown.removeClass('dropdownOpen');
				this._menus[$containerID].removeClass('dropdownOpen');
				
				this._notifyCallbacks($containerID, 'close');
			}
		}
	},
	
	/**
	 * Closes a dropdown without notifying callbacks.
	 * 
	 * @param	string		containerID
	 */
	close: function(containerID) {
		if (!this._dropdowns[containerID]) {
			return;
		}
		
		this._dropdowns[containerID].removeClass('dropdownMenu');
		this._menus[containerID].removeClass('dropdownMenu');
	},
	
	/**
	 * Notifies callbacks.
	 * 
	 * @param	string		containerID
	 * @param	string		action
	 */
	_notifyCallbacks: function(containerID, action) {
		if (!this._callbacks[containerID]) {
			return;
		}
		
		for (var $i = 0, $length = this._callbacks[containerID].length; $i < $length; $i++) {
			this._callbacks[containerID][$i](containerID, action);
		}
	}
};

/**
 * Clipboard API
 */
WCF.Clipboard = {
	/**
	 * action proxy object
	 * @var	WCF.Action.Proxy
	 */
	_actionProxy: null,
	
	/**
	 * action objects
	 * @var	object
	 */
	_actionObjects: {},
	
	/**
	 * list of clipboard containers
	 * @var	jQuery
	 */
	_containers: null,
	
	/**
	 * container meta data
	 * @var	object
	 */
	_containerData: { },
	
	/**
	 * user has marked items
	 * @var	boolean
	 */
	_hasMarkedItems: false,
	
	/**
	 * list of ids of marked objects grouped by object type
	 * @var	object
	 */
	_markedObjectIDs: { },
	
	/**
	 * current page
	 * @var	string
	 */
	_page: '',
	
	/**
	 * current page's object id
	 * @var	integer
	 */
	_pageObjectID: 0,
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of elements already tracked for clipboard actions
	 * @var	object
	 */
	_trackedElements: { },
	
	/**
	 * Initializes the clipboard API.
	 * 
	 * @param	string		page
	 * @param	integer		hasMarkedItems
	 * @param	object		actionObjects
	 * @param	integer		pageObjectID
	 */
	init: function(page, hasMarkedItems, actionObjects, pageObjectID) {
		this._page = page;
		this._actionObjects = actionObjects || { };
		this._hasMarkedItems = (hasMarkedItems > 0);
		this._pageObjectID = parseInt(pageObjectID) || 0;
		
		this._actionProxy = new WCF.Action.Proxy({
			success: $.proxy(this._actionSuccess, this),
			url: 'index.php/ClipboardProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			url: 'index.php/Clipboard/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		// init containers first
		this._containers = $('.jsClipboardContainer').each($.proxy(function(index, container) {
			this._initContainer(container);
		}, this));
		
		// loads marked items
		if (this._hasMarkedItems && this._containers.length) {
			this._loadMarkedItems();
		}
		
		var self = this;
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Clipboard', function() {
			self._containers = $('.jsClipboardContainer').each($.proxy(function(index, container) {
				self._initContainer(container);
			}, self));
		});
	},
	
	/**
	 * Loads marked items on init.
	 */
	_loadMarkedItems: function() {
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				containerData: this._containerData,
				pageClassName: this._page,
				pageObjectID: this._pageObjectID
			},
			success: $.proxy(this._loadMarkedItemsSuccess, this),
			url: 'index.php/ClipboardLoadMarkedItems/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
	},
	
	/**
	 * Reloads the list of marked items.
	 */
	reload: function() {
		if (this._containers === null) {
			return;
		}
		
		this._loadMarkedItems();
	},
	
	/**
	 * Marks all returned items as marked
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_loadMarkedItemsSuccess: function(data, textStatus, jqXHR) {
		this._resetMarkings();
		
		for (var $typeName in data.markedItems) {
			if (!this._markedObjectIDs[$typeName]) {
				this._markedObjectIDs[$typeName] = [ ];
			}
			
			var $objectData = data.markedItems[$typeName];
			for (var $i in $objectData) {
				this._markedObjectIDs[$typeName].push($objectData[$i]);
			}
			
			// loop through all containers
			this._containers.each($.proxy(function(index, container) {
				var $container = $(container);
				
				// typeName does not match, continue
				if ($container.data('type') != $typeName) {
					return true;
				}
				
				// mark items as marked
				$container.find('input.jsClipboardItem').each($.proxy(function(innerIndex, item) {
					var $item = $(item);
					if (WCF.inArray($item.data('objectID'), this._markedObjectIDs[$typeName])) {
						$item.prop('checked', true);
						
						// add marked class for element container
						$item.parents('.jsClipboardObject').addClass('jsMarked');
					}
				}, this));
				
				// check if there is a markAll-checkbox
				$container.find('input.jsClipboardMarkAll').each(function(innerIndex, markAll) {
					var $allItemsMarked = true;
					
					$container.find('input.jsClipboardItem').each(function(itemIndex, item) {
						var $item = $(item);
						if (!$item.prop('checked')) {
							$allItemsMarked = false;
						}
					});
					
					if ($allItemsMarked) {
						$(markAll).prop('checked', true);
					}
				});
			}, this));
		}
		
		// call success method to build item list editors
		this._success(data, textStatus, jqXHR);
	},
	
	/**
	 * Resets all checkboxes.
	 */
	_resetMarkings: function() {
		this._containers.each($.proxy(function(index, container) {
			var $container = $(container);
			
			this._markedObjectIDs[$container.data('type')] = [ ];
			$container.find('input.jsClipboardItem, input.jsClipboardMarkAll').prop('checked', false);
			$container.find('.jsClipboardObject').removeClass('jsMarked');
		}, this));
	},
	
	/**
	 * Initializes a clipboard container.
	 * 
	 * @param	object		container
	 */
	_initContainer: function(container) {
		var $container = $(container);
		var $containerID = $container.wcfIdentify();
		
		if (!this._trackedElements[$containerID]) {
			$container.find('.jsClipboardMarkAll').data('hasContainer', $containerID).click($.proxy(this._markAll, this));
			
			this._markedObjectIDs[$container.data('type')] = [ ];
			this._containerData[$container.data('type')] = {};
			$.each($container.data(), $.proxy(function(index, element) {
				if (index.match(/^type(.+)/)) {
					this._containerData[$container.data('type')][WCF.String.lcfirst(index.replace(/^type/, ''))] = element;
				}
			}, this));
			
			this._trackedElements[$containerID] = [ ];
		}
		
		// track individual checkboxes
		$container.find('input.jsClipboardItem').each($.proxy(function(index, input) {
			var $input = $(input);
			var $inputID = $input.wcfIdentify();
			
			if (!WCF.inArray($inputID, this._trackedElements[$containerID])) {
				this._trackedElements[$containerID].push($inputID);
				
				$input.data('hasContainer', $containerID).click($.proxy(this._click, this));
			}
		}, this));
	},
	
	/**
	 * Processes change checkbox state.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $item = $(event.target);
		var $objectID = $item.data('objectID');
		var $isMarked = ($item.prop('checked')) ? true : false;
		var $objectIDs = [ $objectID ];
		
		if ($item.data('hasContainer')) {
			var $container = $('#' + $item.data('hasContainer'));
			var $type = $container.data('type');
		}
		else {
			var $type = $item.data('type');
		}
		
		if ($isMarked) {
			this._markedObjectIDs[$type].push($objectID);
			$item.parents('.jsClipboardObject').addClass('jsMarked');
		}
		else {
			this._markedObjectIDs[$type] = $.removeArrayValue(this._markedObjectIDs[$type], $objectID);
			$item.parents('.jsClipboardObject').removeClass('jsMarked');
		}
		
		// item is part of a container
		if ($item.data('hasContainer')) {
			// check if all items are marked
			var $markedAll = true;
			$container.find('input.jsClipboardItem').each(function(index, containerItem) {
				var $containerItem = $(containerItem);
				if (!$containerItem.prop('checked')) {
					$markedAll = false;
				}
			});
			
			// simulate a ticked 'markAll' checkbox
			$container.find('.jsClipboardMarkAll').each(function(index, markAll) {
				if ($markedAll) {
					$(markAll).prop('checked', true);
				}
				else {
					$(markAll).prop('checked', false);
				}
			});
		}
		
		this._saveState($type, $objectIDs, $isMarked);
	},
	
	/**
	 * Marks all associated clipboard items as checked.
	 * 
	 * @param	object		event
	 */
	_markAll: function(event) {
		var $item = $(event.target);
		var $objectIDs = [ ];
		var $isMarked = true;
		
		// if markAll object is a checkbox, allow toggling
		if ($item.is('input')) {
			$isMarked = $item.prop('checked');
		}
		
		if ($item.data('hasContainer')) {
			var $container = $('#' + $item.data('hasContainer'));
			var $type = $container.data('type');
		}
		else {
			var $type = $item.data('type');
		}
		
		// handle item containers
		if ($item.data('hasContainer')) {
			// toggle state for all associated items
			$container.find('input.jsClipboardItem').each($.proxy(function(index, containerItem) {
				var $containerItem = $(containerItem);
				var $objectID = $containerItem.data('objectID');
				if ($isMarked) {
					if (!$containerItem.prop('checked')) {
						$containerItem.prop('checked', true);
						this._markedObjectIDs[$type].push($objectID);
						$objectIDs.push($objectID);
					}
				}
				else {
					if ($containerItem.prop('checked')) {
						$containerItem.prop('checked', false);
						this._markedObjectIDs[$type] = $.removeArrayValue(this._markedObjectIDs[$type], $objectID);
						$objectIDs.push($objectID);
					}
				}
			}, this));
			
			if ($isMarked) {
				$container.find('.jsClipboardObject').addClass('jsMarked');
			}
			else {
				$container.find('.jsClipboardObject').removeClass('jsMarked');
			}
		}
		
		// save new status
		this._saveState($type, $objectIDs, $isMarked);
	},
	
	/**
	 * Saves clipboard item state.
	 * 
	 * @param	string		type
	 * @param	array		objectIDs
	 * @param	boolean		isMarked
	 */
	_saveState: function(type, objectIDs, isMarked) {
		this._proxy.setOption('data', {
			action: (isMarked) ? 'mark' : 'unmark',
			containerData: this._containerData,
			objectIDs: objectIDs,
			pageClassName: this._page,
			pageObjectID: this._pageObjectID,
			type: type
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Updates editor options.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// clear all editors first
		var $containers = {};
		$('.jsClipboardEditor').each(function(index, container) {
			var $container = $(container);
			var $types = eval($container.data('types'));
			for (var $i = 0, $length = $types.length; $i < $length; $i++) {
				var $typeName = $types[$i];
				$containers[$typeName] = $container;
			}
			
			var $containerID = $container.wcfIdentify();
			WCF.CloseOverlayHandler.removeCallback($containerID);
			
			$container.empty();
		});
		
		// do not build new editors
		if (!data.items) return;
		
		// rebuild editors
		for (var $typeName in data.items) {
			if (!$containers[$typeName]) {
				continue;
			}
			
			// create container
			var $container = $containers[$typeName];
			var $list = $container.children('ul');
			if ($list.length == 0) {
				$list = $('<ul />').appendTo($container);
			}
			
			var $editor = data.items[$typeName];
			var $label = $('<li class="dropdown"><span class="dropdownToggle button">' + $editor.label + '</span></li>').appendTo($list);
			var $itemList = $('<ol class="dropdownMenu"></ol>').appendTo($label);
			
			// create editor items
			for (var $itemIndex in $editor.items) {
				var $item = $editor.items[$itemIndex];
				
				var $listItem = $('<li><span>' + $item.label + '</span></li>').appendTo($itemList);
				$listItem.data('container', $container);
				$listItem.data('objectType', $typeName);
				$listItem.data('actionName', $item.actionName).data('parameters', $item.parameters);
				$listItem.data('internalData', $item.internalData).data('url', $item.url).data('type', $typeName);
				
				// bind event
				$listItem.click($.proxy(this._executeAction, this));
			}
			
			// add 'unmark all'
			$('<li class="dropdownDivider" />').appendTo($itemList);
			$('<li><span>' + WCF.Language.get('wcf.clipboard.item.unmarkAll') + '</span></li>').appendTo($itemList).click($.proxy(function() {
				this._proxy.setOption('data', {
					action: 'unmarkAll',
					type: $typeName
				});
				this._proxy.setOption('success', $.proxy(function(data, textStatus, jqXHR) {
					this._containers.each($.proxy(function(index, container) {
						var $container = $(container);
						if ($container.data('type') == $typeName) {
							$container.find('.jsClipboardMarkAll, .jsClipboardItem').prop('checked', false);
							$container.find('.jsClipboardObject').removeClass('jsMarked');
							
							return false;
						}
					}, this));
					
					// call and restore success method
					this._success(data, textStatus, jqXHR);
					this._proxy.setOption('success', $.proxy(this._success, this));
				}, this));
				this._proxy.sendRequest();
			}, this));
			
			WCF.Dropdown.initDropdown($label.children('.dropdownToggle'), false);
		}
	},
	
	/**
	 * Closes the clipboard editor item list.
	 */
	_closeLists: function() {
		$('.jsClipboardEditor ul').removeClass('dropdownOpen');
	},
	
	/**
	 * Executes a clipboard editor item action.
	 * 
	 * @param	object		event
	 */
	_executeAction: function(event) {
		var $listItem = $(event.currentTarget);
		var $url = $listItem.data('url');
		if ($url) {
			window.location.href = $url;
		}
		
		if ($listItem.data('parameters').className && $listItem.data('parameters').actionName) {
			if ($listItem.data('parameters').actionName === 'unmarkAll' || $listItem.data('parameters').objectIDs) {
				var $confirmMessage = $listItem.data('internalData')['confirmMessage'];
				if ($confirmMessage) {
					var $template = $listItem.data('internalData')['template'];
					if ($template) $template = $($template);
					
					WCF.System.Confirmation.show($confirmMessage, $.proxy(function(action) {
						if (action === 'confirm') {
							var $data = { };
							
							if ($template && $template.length) {
								$('#wcfSystemConfirmationContent').find('input, select, textarea').each(function(index, item) {
									var $item = $(item);
									$data[$item.prop('name')] = $item.val();
								});
							}
							
							this._executeAJAXActions($listItem, $data);
						}
					}, this), '', $template);
				}
				else {
					this._executeAJAXActions($listItem, { });
				}
			}
		}
		
		// fire event
		$listItem.data('container').trigger('clipboardAction', [ $listItem.data('type'), $listItem.data('actionName'), $listItem.data('parameters') ]);
	},
	
	/**
	 * Executes the AJAX actions for the given editor list item.
	 * 
	 * @param	jQuery		listItem
	 * @param	object		data
	 */
	_executeAJAXActions: function(listItem, data) {
		data = data || { };
		var $objectIDs = [];
		if (listItem.data('parameters').actionName !== 'unmarkAll') {
			$.each(listItem.data('parameters').objectIDs, function(index, objectID) {
				$objectIDs.push(parseInt(objectID));
			});
		}
		
		var $parameters = {
			data: data,
			containerData: this._containerData[listItem.data('type')]
		};
		var $__parameters = listItem.data('internalData')['parameters'];
		if ($__parameters !== undefined) {
			for (var $key in $__parameters) {
				$parameters[$key] = $__parameters[$key];
			}
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: listItem.data('parameters').actionName,
				className: listItem.data('parameters').className,
				objectIDs: $objectIDs,
				parameters: $parameters
			},
			success: $.proxy(function(data) {
				if (listItem.data('parameters').actionName !== 'unmarkAll') {
					listItem.data('container').trigger('clipboardActionResponse', [ data, listItem.data('type'), listItem.data('actionName'), listItem.data('parameters') ]);
				}
				
				this._loadMarkedItems();
			}, this)
		});
		
		if (this._actionObjects[listItem.data('objectType')] && this._actionObjects[listItem.data('objectType')][listItem.data('parameters').actionName]) {
			this._actionObjects[listItem.data('objectType')][listItem.data('parameters').actionName].triggerEffect($objectIDs);
		}
	},
	
	/**
	 * Sends a clipboard proxy request.
	 * 
	 * @param	object		item
	 */
	sendRequest: function(item) {
		var $item = $(item);
		
		this._actionProxy.setOption('data', {
			parameters: $item.data('parameters'),
			typeName: $item.data('type')
		});
		this._actionProxy.sendRequest();
	}
};

/**
 * Provides a simple call for periodical executed functions. Based upon
 * ideas by Prototype's PeriodicalExecuter.
 * 
 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/periodical_executer.js
 * @param	function		callback
 * @param	integer			delay
 */
WCF.PeriodicalExecuter = Class.extend({
	/**
	 * callback for each execution cycle
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * interval
	 * @var	integer
	 */
	_delay: 0,
	
	/**
	 * interval id
	 * @var	integer
	 */
	_intervalID: null,
	
	/**
	 * execution state
	 * @var	boolean
	 */
	_isExecuting: false,
	
	/**
	 * Initializes a periodical executer.
	 * 
	 * @param	function		callback
	 * @param	integer			delay
	 */
	init: function(callback, delay) {
		if (!$.isFunction(callback)) {
			console.debug('[WCF.PeriodicalExecuter] Given callback is invalid, aborting.');
			return;
		}
		
		this._callback = callback;
		this._interval = delay;
		this.resume();
	},
	
	/**
	 * Executes callback.
	 */
	_execute: function() {
		if (!this._isExecuting) {
			try {
				this._isExecuting = true;
				this._callback(this);
				this._isExecuting = false;
			}
			catch (e) {
				this._isExecuting = false;
				throw e;
			}
		}
	},
	
	/**
	 * Terminates loop.
	 */
	stop: function() {
		if (!this._intervalID) {
			return;
		}
		
		clearInterval(this._intervalID);
	},
	
	/**
	 * Resumes the interval-based callback execution.
	 */
	resume: function() {
		if (this._intervalID) {
			this.stop();
		}
		
		this._intervalID = setInterval($.proxy(this._execute, this), this._interval);
	}
});

/**
 * Handler for loading overlays
 */
WCF.LoadingOverlayHandler = {
	/**
	 * count of active loading-requests
	 * @var	integer
	 */
	_activeRequests: 0,
	
	/**
	 * loading overlay
	 * @var	jQuery
	 */
	_loadingOverlay: null,
	
	/**
	 * WCF.PeriodicalExecuter instance
	 * @var	WCF.PeriodicalExecuter
	 */
	_pending: null,
	
	/**
	 * Adds one loading-request and shows the loading overlay if nessercery
	 */
	show: function() {
		if (this._loadingOverlay === null) { // create loading overlay on first run
			this._loadingOverlay = $('<div class="spinner"><span class="icon icon48 icon-spinner" /> <span>' + WCF.Language.get('wcf.global.loading') + '</span></div>').appendTo($('body'));
			
			// fix position
			var $width = this._loadingOverlay.outerWidth();
			if ($width < 70) $width = 70;
			this._loadingOverlay.css({
				marginLeft: Math.ceil(-1 * $width / 2), 
				width: $width
			}).hide();
		}
		
		this._activeRequests++;
		if (this._activeRequests == 1) {
			if (this._pending === null) {
				var self = this;
				this._pending = new WCF.PeriodicalExecuter(function(pe) {
					if (self._activeRequests) {
						self._loadingOverlay.stop(true, true).fadeIn(100);
					}
					
					pe.stop();
					self._pending = null;
				}, 250); 
			}
			
		}
	},
	
	/**
	 * Removes one loading-request and hides loading overlay if there're no more pending requests
	 */
	hide: function() {
		this._activeRequests--;
		if (this._activeRequests == 0) {
			if (this._pending !== null) {
				this._pending.stop();
				this._pending = null;
			}
			
			this._loadingOverlay.stop(true, true).fadeOut(100);
		}
	},
	
	/**
	 * Updates a icon to/from spinner
	 * 
	 * @param	jQuery	target
	 * @pram	boolean	loading
	 */
	updateIcon: function(target, loading) {
		var $method = (loading === undefined || loading ? 'addClass' : 'removeClass');
		
		target.find('.icon')[$method]('icon-spinner');
		if (target.hasClass('icon')) {
			target[$method]('icon-spinner');
		}
	}
};

/**
 * Namespace for AJAXProxies
 */
WCF.Action = {};

/**
 * Basic implementation for AJAX-based proxyies
 * 
 * @param	object		options
 */
WCF.Action.Proxy = Class.extend({
	/**
	 * shows loading overlay for a single request
	 * @var	boolean
	 */
	_showLoadingOverlayOnce: false,
	
	/**
	 * suppresses errors
	 * @var	boolean
	 */
	_suppressErrors: false,
	
	/**
	 * last request
	 * @var	jqXHR
	 */
	_lastRequest: null,
	
	/**
	 * Initializes AJAXProxy.
	 * 
	 * @param	object		options
	 */
	init: function(options) {
		// initialize default values
		this.options = $.extend(true, {
			autoSend: false,
			data: { },
			dataType: 'json',
			after: null,
			init: null,
			jsonp: 'callback',
			async: true,
			failure: null,
			showLoadingOverlay: true,
			success: null,
			suppressErrors: false,
			type: 'POST',
			url: 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND,
			aborted: null,
			autoAbortPrevious: false
		}, options);
		
		this.confirmationDialog = null;
		this.loading = null;
		this._showLoadingOverlayOnce = false;
		this._suppressErrors = (this.options.suppressErrors === true);
		
		// send request immediately after initialization
		if (this.options.autoSend) {
			this.sendRequest();
		}
		
		var self = this;
		$(window).on('beforeunload', function() { self._suppressErrors = true; });
	},
	
	/**
	 * Sends an AJAX request.
	 * 
	 * @param	abortPrevious	boolean
	 * @return	jqXHR
	 */
	sendRequest: function(abortPrevious) {
		this._init();
		
		if (abortPrevious || this.options.autoAbortPrevious) {
			this.abortPrevious();
		}
		
		this._lastRequest = $.ajax({
			data: this.options.data,
			dataType: this.options.dataType,
			jsonp: this.options.jsonp,
			async: this.options.async,
			type: this.options.type,
			url: this.options.url,
			success: $.proxy(this._success, this),
			error: $.proxy(this._failure, this)
		});
		return this._lastRequest;
	},
	
	/**
	 * Aborts the previous request
	 */
	abortPrevious: function() {
		if (this._lastRequest !== null) {
			this._lastRequest.abort();
			this._lastRequest = null;
		}
	},
	
	/**
	 * Shows loading overlay for a single request.
	 */
	showLoadingOverlayOnce: function() {
		this._showLoadingOverlayOnce = true;
	},
	
	/**
	 * Suppressed errors for this action proxy.
	 */
	suppressErrors: function() {
		this._suppressErrors = true;
	},
	
	/**
	 * Fires before request is send, displays global loading status.
	 */
	_init: function() {
		if ($.isFunction(this.options.init)) {
			this.options.init(this);
		}
		
		if (this.options.showLoadingOverlay || this._showLoadingOverlayOnce) {
			WCF.LoadingOverlayHandler.show();
		}
	},
	
	/**
	 * Handles AJAX errors.
	 * 
	 * @param	object		jqXHR
	 * @param	string		textStatus
	 * @param	string		errorThrown
	 */
	_failure: function(jqXHR, textStatus, errorThrown) {
		if (textStatus == 'abort') {
			// call child method if applicable
			if ($.isFunction(this.options.aborted)) {
				this.options.aborted(jqXHR);
			}
			
			return;
		}
		
		try {
			var $data = $.parseJSON(jqXHR.responseText);
			
			// call child method if applicable
			var $showError = true;
			if ($.isFunction(this.options.failure)) {
				$showError = this.options.failure($data, jqXHR, textStatus, errorThrown);
			}
			
			if (!this._suppressErrors && $showError !== false) {
				var $details = '';
				if ($data.stacktrace) $details = '<br /><p>Stacktrace:</p><p>' + $data.stacktrace + '</p>';
				else if ($data.exceptionID) $details = '<br /><p>Exception ID: <code>' + $data.exceptionID + '</code></p>';
				
				$('<div class="ajaxDebugMessage"><p>' + $data.message + '</p>' + $details + '</div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
			}
		}
		// failed to parse JSON
		catch (e) {
			// call child method if applicable
			var $showError = true;
			if ($.isFunction(this.options.failure)) {
				$showError = this.options.failure(null, jqXHR, textStatus, errorThrown);
			}
			
			if (!this._suppressErrors && $showError !== false) {
				var $message = (textStatus === 'timeout') ? WCF.Language.get('wcf.global.error.timeout') : jqXHR.responseText;
				
				// validate if $message is neither empty nor 'undefined'
				if ($message && $message != 'undefined') {
					$('<div class="ajaxDebugMessage"><p>' + $message + '</p></div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
				}
			}
		}
		
		this._after();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// call child method if applicable
		if ($.isFunction(this.options.success)) {
			// trim HTML before processing, see http://jquery.com/upgrade-guide/1.9/#jquery-htmlstring-versus-jquery-selectorstring
			if (data && data.returnValues && data.returnValues.template !== undefined) {
				data.returnValues.template = $.trim(data.returnValues.template);
			}
			
			this.options.success(data, textStatus, jqXHR);
		}
		
		this._after();
	},
	
	/**
	 * Fires after an AJAX request, hides global loading status.
	 */
	_after: function() {
		this._lastRequest = null;
		if ($.isFunction(this.options.after)) {
			this.options.after();
		}
		
		if (this.options.showLoadingOverlay || this._showLoadingOverlayOnce) {
			WCF.LoadingOverlayHandler.hide();
			
			if (this._showLoadingOverlayOnce) {
				this._showLoadingOverlayOnce = false;
			}
		}
		
		WCF.DOMNodeInsertedHandler.execute();
		
		// fix anchor tags generated through WCF::getAnchor()
		$('a[href*=#]').each(function(index, link) {
			var $link = $(link);
			if ($link.prop('href').indexOf('AJAXProxy') != -1) {
				var $anchor = $link.prop('href').substr($link.prop('href').indexOf('#'));
				var $pageLink = document.location.toString().replace(/#.*/, '');
				$link.prop('href', $pageLink + $anchor);
			}
		});
	},
	
	/**
	 * Sets options, MUST be used to set parameters before sending request
	 * if calling from child classes.
	 * 
	 * @param	string		optionName
	 * @param	mixed		optionData
	 */
	setOption: function(optionName, optionData) {
		this.options[optionName] = optionData;
	}
});

/**
 * Basic implementation for simple proxy access using bound elements.
 * 
 * @param	object		options
 * @param	object		callbacks
 */
WCF.Action.SimpleProxy = Class.extend({
	/**
	 * Initializes SimpleProxy.
	 * 
	 * @param	object		options
	 * @param	object		callbacks
	 */
	init: function(options, callbacks) {
		/**
		 * action-specific options
		 */
		this.options = $.extend(true, {
			action: '',
			className: '',
			elements: null,
			eventName: 'click'
		}, options);
		
		/**
		 * proxy-specific options
		 */
		this.callbacks = $.extend(true, {
			after: null,
			failure: null,
			init: null,
			success: null
		}, callbacks);
		
		if (!this.options.elements) return;
		
		// initialize proxy
		this.proxy = new WCF.Action.Proxy(this.callbacks);
		
		// bind event listener
		this.options.elements.each($.proxy(function(index, element) {
			$(element).bind(this.options.eventName, $.proxy(this._handleEvent, this));
		}, this));
	},
	
	/**
	 * Handles event actions.
	 * 
	 * @param	object		event
	 */
	_handleEvent: function(event) {
		this.proxy.setOption('data', {
			actionName: this.options.action,
			className: this.options.className,
			objectIDs: [ $(event.target).data('objectID') ]
		});
		
		this.proxy.sendRequest();
	}
});

/**
 * Basic implementation for AJAXProxy-based deletion.
 * 
 * @param	string		className
 * @param	string		containerSelector
 * @param	string		buttonSelector
 */
WCF.Action.Delete = Class.extend({
	/**
	 * delete button selector
	 * @var	string
	 */
	_buttonSelector: '',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * container selector
	 * @var	string
	 */
	_containerSelector: '',
	
	/**
	 * list of known container ids
	 * @var	array<string>
	 */
	_containers: [ ],
	
	/**
	 * Initializes 'delete'-Proxy.
	 * 
	 * @param	string		className
	 * @param	string		containerSelector
	 * @param	string		buttonSelector
	 */
	init: function(className, containerSelector, buttonSelector) {
		this._containerSelector = containerSelector;
		this._className = className;
		this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsDeleteButton';
		
		this.proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initElements();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Action.Delete' + this._className.hashCode(), $.proxy(this._initElements, this));
	},
	
	/**
	 * Initializes available element containers.
	 */
	_initElements: function() {
		var self = this;
		$(this._containerSelector).each(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!WCF.inArray($containerID, self._containers)) {
				self._containers.push($containerID);
				$container.find(self._buttonSelector).click($.proxy(self._click, self));
			}
		});
	},
	
	/**
	 * Sends AJAX request.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $target = $(event.currentTarget);
		event.preventDefault();
		
		if ($target.data('confirmMessage')) {
			WCF.System.Confirmation.show($target.data('confirmMessage'), $.proxy(this._execute, this), { target: $target });
		}
		else {
			WCF.LoadingOverlayHandler.updateIcon($target);
			this._sendRequest($target);
		}
	},
	
	/**
	 * Is called if the delete effect has been triggered on the given element.
	 * 
	 * @param	jQuery		element
	 */
	_didTriggerEffect: function(element) {
		// does nothing
	},
	
	/**
	 * Executes deletion.
	 * 
	 * @param	string		action
	 * @param	object		parameters
	 */
	_execute: function(action, parameters) {
		if (action === 'cancel') {
			return;
		}
		
		WCF.LoadingOverlayHandler.updateIcon(parameters.target);
		this._sendRequest(parameters.target);
	},
	
	/**
	 * Sends the request
	 * 
	 * @param	jQuery	object
	 */
	_sendRequest: function(object) {
		this.proxy.setOption('data', {
			actionName: 'delete',
			className: this._className,
			interfaceName: 'wcf\\data\\IDeleteAction',
			objectIDs: [ $(object).data('objectID') ]
		});
		
		this.proxy.sendRequest();
	},
	
	/**
	 * Deletes items from containers.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this.triggerEffect(data.objectIDs);
	},
	
	/**
	 * Triggers the delete effect for the objects with the given ids.
	 * 
	 * @param	array		objectIDs
	 */
	triggerEffect: function(objectIDs) {
		for (var $index in this._containers) {
			var $container = $('#' + this._containers[$index]);
			if (WCF.inArray($container.find(this._buttonSelector).data('objectID'), objectIDs)) {
				var self = this;
				$container.wcfBlindOut('up',function() {
					$(this).remove();
					self._containers.splice(self._containers.indexOf($(this).wcfIdentify()), 1);
					self._didTriggerEffect($(this));
				});
			}
		}
	}
});

/**
 * Basic implementation for deletion of nested elements.
 * 
 * The implementation requires the nested elements to be grouped as numbered lists
 * (ol lists). The child elements of the deleted elements are moved to the parent
 * element of the deleted element.
 * 
 * @see	WCF.Action.Delete
 */
WCF.Action.NestedDelete = WCF.Action.Delete.extend({
	/**
	 * @see	WCF.Action.Delete.triggerEffect()
	 */
	triggerEffect: function(objectIDs) {
		for (var $index in this._containers) {
			var $container = $('#' + this._containers[$index]);
			if (WCF.inArray($container.find(this._buttonSelector).data('objectID'), objectIDs)) {
				// move children up
				if ($container.has('ol').has('li').length) {
					if ($container.is(':only-child')) {
						$container.parent().replaceWith($container.find('> ol'));
					}
					else {
						$container.replaceWith($container.find('> ol > li'));
					}
					
					this._containers.splice(this._containers.indexOf($container.wcfIdentify()), 1);
					this._didTriggerEffect($container);
				}
				else {
					var self = this;
					$container.wcfBlindOut('up', function() {
						$(this).remove();
						self._containers.splice(self._containers.indexOf($(this).wcfIdentify()), 1);
						self._didTriggerEffect($(this));
					});
				}
			}
		}
	}
});

/**
 * Basic implementation for AJAXProxy-based toggle actions.
 * 
 * @param	string		className
 * @param	jQuery		containerList
 * @param	string		buttonSelector
 */
WCF.Action.Toggle = Class.extend({
	/**
	 * toogle button selector
	 * @var	string
	 */
	_buttonSelector: '.jsToggleButton',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * container selector
	 * @var	string
	 */
	_containerSelector: '',
	
	/**
	 * list of known container ids
	 * @var	array<string>
	 */
	_containers: [ ],
	
	/**
	 * Initializes 'toggle'-Proxy
	 * 
	 * @param	string		className
	 * @param	string		containerSelector
	 * @param	string		buttonSelector
	 */
	init: function(className, containerSelector, buttonSelector) {
		this._containerSelector = containerSelector;
		this._className = className;
		this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsToggleButton';
		this._containers = [ ];
		
		// initialize proxy
		var options = {
			success: $.proxy(this._success, this)
		};
		this.proxy = new WCF.Action.Proxy(options);
		
		// bind event listener
		this._initElements();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Action.Toggle' + this._className.hashCode(), $.proxy(this._initElements, this));	
	},
	
	/**
	 * Initializes available element containers.
	 */
	_initElements: function() {
		$(this._containerSelector).each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!WCF.inArray($containerID, this._containers)) {
				this._containers.push($containerID);
				$container.find(this._buttonSelector).click($.proxy(this._click, this));
			}
		}, this));
	},
	
	/**
	 * Sends AJAX request.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $target = $(event.currentTarget);
		event.preventDefault();
		
		if ($target.data('confirmMessage')) {
			WCF.System.Confirmation.show($target.data('confirmMessage'), $.proxy(this._execute, this), { target: $target });
		}
		else {
			WCF.LoadingOverlayHandler.updateIcon($target);
			this._sendRequest($target);
		}
	},
	
	/**
	 * Executes toggeling.
	 * 
	 * @param	string		action
	 * @param	object		parameters
	 */
	_execute: function(action, parameters) {
		if (action === 'cancel') {
			return;
		}
		
		WCF.LoadingOverlayHandler.updateIcon(parameters.target);
		this._sendRequest(parameters.target);
	},
	
	_sendRequest: function(object) {
		this.proxy.setOption('data', {
			actionName: 'toggle',
			className: this._className,
			interfaceName: 'wcf\\data\\IToggleAction',
			objectIDs: [ $(object).data('objectID') ]
		});
		
		this.proxy.sendRequest();
	},
	
	/**
	 * Toggles status icons.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this.triggerEffect(data.objectIDs);
	},
	
	/**
	 * Triggers the toggle effect for the objects with the given ids.
	 * 
	 * @param	array		objectIDs
	 */
	triggerEffect: function(objectIDs) {
		for (var $index in this._containers) {
			var $container = $('#' + this._containers[$index]);
			var $toggleButton = $container.find(this._buttonSelector);
			if (WCF.inArray($toggleButton.data('objectID'), objectIDs)) {
				$container.wcfHighlight();
				this._toggleButton($container, $toggleButton);
			}
		}
	},
	
	/**
	 * Tiggers the toggle effect on a button
	 * 
	 * @param	jQuery	$container
	 * @param	jQuery	$toggleButton
	 */
	_toggleButton: function($container, $toggleButton) {
		// toggle icon source
		WCF.LoadingOverlayHandler.updateIcon($toggleButton, false);
		if ($toggleButton.hasClass('icon-check-empty')) {
			$toggleButton.removeClass('icon-check-empty').addClass('icon-check');
			$newTitle = ($toggleButton.data('disableTitle') ? $toggleButton.data('disableTitle') : WCF.Language.get('wcf.global.button.disable'));
			$toggleButton.attr('title', $newTitle);
		}
		else {
			$toggleButton.removeClass('icon-check').addClass('icon-check-empty');
			$newTitle = ($toggleButton.data('enableTitle') ? $toggleButton.data('enableTitle') : WCF.Language.get('wcf.global.button.enable'));
			$toggleButton.attr('title', $newTitle);
		}
		
		// toggle css class
		$container.toggleClass('disabled');
	}
});

/**
 * Executes provided callback if scroll threshold is reached. Usuable to determine
 * if user reached the bottom of an element to load new elements on the fly.
 * 
 * If you do not provide a value for 'reference' and 'target' it will assume you're
 * monitoring page scrolls, otherwise a valid jQuery selector must be provided for both.
 * 
 * @param	integer		threshold
 * @param	object		callback
 * @param	string		reference
 * @param	string		target
 */
WCF.Action.Scroll = Class.extend({
	/**
	 * callback used once threshold is reached
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * reference object
	 * @var	jQuery
	 */
	_reference: null,
	
	/**
	 * target object
	 * @var	jQuery
	 */
	_target: null,
	
	/**
	 * threshold value
	 * @var	integer
	 */
	_threshold: 0,
	
	/**
	 * Initializes a new WCF.Action.Scroll object.
	 * 
	 * @param	integer		threshold
	 * @param	object		callback
	 * @param	string		reference
	 * @param	string		target
	 */
	init: function(threshold, callback, reference, target) {
		this._threshold = parseInt(threshold);
		if (this._threshold === 0) {
			console.debug("[WCF.Action.Scroll] Given threshold is invalid, aborting.");
			return;
		}
		
		if ($.isFunction(callback)) this._callback = callback;
		if (this._callback === null) {
			console.debug("[WCF.Action.Scroll] Given callback is invalid, aborting.");
			return;
		}
		
		// bind element references
		this._reference = $((reference) ? reference : window);
		this._target = $((target) ? target : document);
		
		// watch for scroll event
		this.start();
		
		// check if browser navigated back and jumped to offset before JavaScript was loaded
		this._scroll();
	},
	
	/**
	 * Calculates if threshold is reached and notifies callback.
	 */
	_scroll: function() {
		var $targetHeight = this._target.height();
		var $topOffset = this._reference.scrollTop();
		var $referenceHeight = this._reference.height();
		
		// calculate if defined threshold is visible
		if (($targetHeight - ($referenceHeight + $topOffset)) < this._threshold) {
			this._callback(this);
		}
	},
	
	/**
	 * Enables scroll monitoring, may be used to resume.
	 */
	start: function() {
		this._reference.on('scroll', $.proxy(this._scroll, this));
	},
	
	/**
	 * Disables scroll monitoring, e.g. no more elements loadable.
	 */
	stop: function() {
		this._reference.off('scroll');
	}
});

/**
 * Namespace for date-related functions.
 */
WCF.Date = {};

/**
 * Provides a date picker for date input fields.
 */
WCF.Date.Picker = {
	/**
	 * date format
	 * @var	string
	 */
	_dateFormat: 'yy-mm-dd',
	
	/**
	 * time format
	 * @var	string
	 */
	_timeFormat: 'g:ia',
	
	/**
	 * Initializes the jQuery UI based date picker.
	 */
	init: function() {
		// ignore error 'unexpected literal' error; this might be not the best approach
		// to fix this problem, but since the date is properly processed anyway, we can
		// simply continue :)	- Alex
		var $__log = $.timepicker.log;
		$.timepicker.log = function(error) {
			if (error.indexOf('Error parsing the date/time string: Unexpected literal at position') == -1 && error.indexOf('Error parsing the date/time string: Unknown name at position') == -1) {
				$__log(error);
			}
		};
		
		this._convertDateFormat();
		this._initDatePicker();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Date.Picker', $.proxy(this._initDatePicker, this));
	},
	
	/**
	 * Convert PHPs date() format to jQuery UIs date picker format.
	 */
	_convertDateFormat: function() {
		// replacement table
		// format of PHP date() => format of jQuery UI date picker
		//
		// No equivalence in PHP date():
		// oo	day of the year (three digit)
		// !	Windows ticks (100ns since 01/01/0001)
		//
		// No equivalence in jQuery UI date picker:
		// N	ISO-8601 numeric representation of the day of the week
		// w	Numeric representation of the day of the week
		// W	ISO-8601 week number of year, weeks starting on Monday
		// t	Number of days in the given month
		// L	Whether it's a leap year
		var $replacementTable = {
			// time
			'a': 'tt',
			'A': 'TT',
			'g': 'h',
			'G': 'H',
			'h': 'hh',
			'H': 'HH',
			'i': 'mm',
			's': 'ss',
			'u': 'l',
			
			// day
			'd': 'dd',
			'D': 'D',
			'j': 'd',
			'l': 'DD',
			'z': 'o',
			'S': '', // English ordinal suffix for the day of the month, 2 characters, will be discarded

			// month
			'F': 'MM',
			'm': 'mm',
			'M': 'M',
			'n': 'm',

			// year
			'o': 'yy',
			'Y': 'yy',
			'y': 'y',

			// timestamp
			'U': '@'
		};
		
		// do the actual replacement
		// this is not perfect, but a basic implementation and should work in 99% of the cases
		this._dateFormat = WCF.Language.get('wcf.date.dateFormat').replace(/([^dDjlzSFmMnoYyU\\]*(?:\\.[^dDjlzSFmMnoYyU\\]*)*)([dDjlzSFmMnoYyU])/g, function(match, part1, part2, offset, string) {
			for (var $key in $replacementTable) {
				if (part2 == $key) {
					part2 = $replacementTable[$key];
				}
			}
			
			return part1 + part2;
		});
		
		this._timeFormat = WCF.Language.get('wcf.date.timeFormat').replace(/([^aAgGhHisu\\]*(?:\\.[^aAgGhHisu\\]*)*)([aAgGhHisu])/g, function(match, part1, part2, offset, string) {
			for (var $key in $replacementTable) {
				if (part2 == $key) {
					part2 = $replacementTable[$key];
				}
			}
			
			return part1 + part2;
		});
	},
	
	/**
	 * Initializes the date picker for valid fields.
	 */
	_initDatePicker: function() {
		$('input[type=date]:not(.jsDatePicker), input[type=datetime]:not(.jsDatePicker)').each($.proxy(function(index, input) {
			var $input = $(input);
			var $inputName = $input.prop('name');
			var $inputValue = $input.val(); // should be Y-m-d (H:i:s), must be interpretable by Date
			
			var $hasTime = $input.attr('type') == 'datetime';
			
			// update $input
			$input.prop('type', 'text').addClass('jsDatePicker');
			
			// set placeholder
			if ($input.data('placeholder')) $input.attr('placeholder', $input.data('placeholder'));
			
			// insert a hidden element representing the actual date
			$input.removeAttr('name');
			$input.before('<input type="hidden" id="' + $input.wcfIdentify() + 'DatePicker" name="' + $inputName + '" value="' + $inputValue + '" />');
			
			// max- and mindate
			var $maxDate = $input.attr('max') ? new Date($input.attr('max').replace(' ', 'T')) : null;
			var $minDate = $input.attr('min') ? new Date($input.attr('min').replace(' ', 'T')) : null;
			
			// init date picker
			$options = {
				altField: '#' + $input.wcfIdentify() + 'DatePicker',
				altFormat: 'yy-mm-dd', // PHPs strtotime() understands this best
				beforeShow: function(input, instance) {
					// dirty hack to force opening below the input
					setTimeout(function() {
						instance.dpDiv.position({
							my: 'left top',
							at: 'left bottom',
							collision: 'none',
							of: input
						});
					}, 1);
				},
				changeMonth: true,
				changeYear: true,
				dateFormat: this._dateFormat,
				dayNames: WCF.Language.get('__days'),
				dayNamesMin: WCF.Language.get('__daysShort'),
				dayNamesShort: WCF.Language.get('__daysShort'),
				firstDay: parseInt(WCF.Language.get('wcf.date.firstDayOfTheWeek')) || 0,
				isRTL: WCF.Language.get('wcf.global.pageDirection') == 'rtl',
				maxDate: $maxDate,
				minDate: $minDate,
				monthNames: WCF.Language.get('__months'),
				monthNamesShort: WCF.Language.get('__monthsShort'),
				showButtonPanel: false,
				onClose: function(dateText, datePicker) {
					// clear altField when datepicker is cleared
					if (dateText == '') {
						$(datePicker.settings["altField"]).val(dateText);
					}
				},
				showOtherMonths: true,
				yearRange: ($input.hasClass('birthday') ? '-100:+0' : '1900:2038')
			};
			
			if ($hasTime) {
				// drop the seconds
				if (/[0-9]{2}:[0-9]{2}:[0-9]{2}$/.test($inputValue)) {
					$inputValue = $inputValue.replace(/:[0-9]{2}$/, '');
					$input.val($inputValue);
				}
				$inputValue = $inputValue.replace(' ', 'T');
				
				if ($input.data('ignoreTimezone')) {
					var $timezoneOffset = new Date().getTimezoneOffset();
					var $timezone = ($timezoneOffset > 0) ? '-' : '+'; // -120 equals GMT+0200
					$timezoneOffset = Math.abs($timezoneOffset);
					var $hours = (Math.floor($timezoneOffset / 60)).toString();
					var $minutes = ($timezoneOffset % 60).toString();
					$timezone += ($hours.length == 2) ? $hours : '0' + $hours;
					$timezone += ':';
					$timezone += ($minutes.length == 2) ? $minutes : '0' + $minutes;
					
					$inputValue = $inputValue.replace(/[+-][0-9]{2}:[0-9]{2}$/, $timezone);
				}
				
				$options = $.extend($options, {
					altFieldTimeOnly: false,
					altTimeFormat: 'HH:mm',
					controlType: 'select',
					hourText: WCF.Language.get('wcf.date.hour'),
					minuteText: WCF.Language.get('wcf.date.minute'),
					showTime: false,
					timeFormat: this._timeFormat,
					yearRange: ($input.hasClass('birthday') ? '-100:+0' : '1900:2038')
				});
			}
			
			if ($hasTime) {
				$input.datetimepicker($options);
			}
			else {
				$input.datepicker($options);
			}
			
			// format default date
			if ($inputValue) {
				if (!$hasTime) {
					// drop timezone for date-only input
					$inputValue = new Date($inputValue);
					$inputValue.setMinutes($inputValue.getMinutes() + $inputValue.getTimezoneOffset());
				}
				
				$input.datepicker('setDate', $inputValue);
			}
			
			// bug workaround: setDate creates the widget but unfortunately doesn't hide it...
			$input.datepicker('widget').hide();
		}, this));
	}
};

/**
 * Provides utility functions for date operations.
 */
WCF.Date.Util = {
	/**
	 * Returns UTC timestamp, if date is not given, current time will be used.
	 * 
	 * @param	Date		date
	 * @return	integer
	 */
	gmdate: function(date) {
		var $date = (date) ? date : new Date();
		
		return Math.round(Date.UTC(
			$date.getUTCFullYear(),
			$date.getUTCMonth(),
			$date.getUTCDay(),
			$date.getUTCHours(),
			$date.getUTCMinutes(),
			$date.getUTCSeconds()
		) / 1000);
	},
	
	/**
	 * Returns a Date object with precise offset (including timezone and local timezone).
	 * Parameters timestamp and offset must be in miliseconds!
	 * 
	 * @param	integer		timestamp
	 * @param	integer		offset
	 * @return	Date
	 */
	getTimezoneDate: function(timestamp, offset) {
		var $date = new Date(timestamp);
		var $localOffset = $date.getTimezoneOffset() * 60000;
		
		return new Date((timestamp + $localOffset + offset));
	}
};

/**
 * Handles relative time designations.
 */
WCF.Date.Time = Class.extend({
	/**
	 * Date of current timestamp
	 * @var	Date
	 */
	_date: 0,
	
	/**
	 * list of time elements
	 * @var	jQuery
	 */
	_elements: null,
	
	/**
	 * difference between server and local time
	 * @var	integer
	 */
	_offset: null,
	
	/**
	 * current timestamp
	 * @var	integer
	 */
	_timestamp: 0,
	
	/**
	 * Initializes relative datetimes.
	 */
	init: function() {
		this._elements = $('time.datetime');
		this._offset = null;
		this._timestamp = 0;
		
		// calculate relative datetime on init
		this._refresh();
		
		// re-calculate relative datetime every minute
		new WCF.PeriodicalExecuter($.proxy(this._refresh, this), 60000);
		
		// bind dom node inserted listener
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Date.Time', $.proxy(this._domNodeInserted, this));
	},
	
	/**
	 * Updates element collection once a DOM node was inserted.
	 */
	_domNodeInserted: function() {
		this._elements = $('time.datetime');
		this._refresh();
	},
	
	/**
	 * Refreshes relative datetime for each element.
	 */
	_refresh: function() {
		this._date = new Date();
		this._timestamp = (this._date.getTime() - this._date.getMilliseconds()) / 1000;
		if (this._offset === null) {
			this._offset = this._timestamp - TIME_NOW;
		}
		
		this._elements.each($.proxy(this._refreshElement, this));
	},
	
	/**
	 * Refreshes relative datetime for current element.
	 * 
	 * @param	integer		index
	 * @param	object		element
	 */
	_refreshElement: function(index, element) {
		var $element = $(element);
		
		if (!$element.attr('title')) {
			$element.attr('title', $element.text());
		}
		
		var $timestamp = $element.data('timestamp') + this._offset;
		var $date = $element.data('date');
		var $time = $element.data('time');
		var $offset = $element.data('offset');
		
		// skip for future dates
		if ($element.data('isFutureDate')) return;
		
		// timestamp is less than 60 seconds ago
		if ($timestamp >= this._timestamp || this._timestamp < ($timestamp + 60)) {
			$element.text(WCF.Language.get('wcf.date.relative.now'));
		}
		// timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
		else if (this._timestamp < ($timestamp + 3540)) {
			var $minutes = Math.max(Math.round((this._timestamp - $timestamp) / 60), 1);
			$element.text(WCF.Language.get('wcf.date.relative.minutes', { minutes: $minutes }));
		}
		// timestamp is less than 24 hours ago
		else if (this._timestamp < ($timestamp + 86400)) {
			var $hours = Math.round((this._timestamp - $timestamp) / 3600);
			$element.text(WCF.Language.get('wcf.date.relative.hours', { hours: $hours }));
		}
		// timestamp is less than 6 days ago
		else if (this._timestamp < ($timestamp + 518400)) {
			var $midnight = new Date(this._date.getFullYear(), this._date.getMonth(), this._date.getDate());
			var $days = Math.ceil(($midnight / 1000 - $timestamp) / 86400);
			
			// get day of week
			var $dateObj = WCF.Date.Util.getTimezoneDate(($timestamp * 1000), $offset * 1000);
			var $dow = $dateObj.getDay();
			var $day = WCF.Language.get('__days')[$dow];
			
			$element.text(WCF.Language.get('wcf.date.relative.pastDays', { days: $days, day: $day, time: $time }));
		}
		// timestamp is between ~700 million years BC and last week
		else {
			var $string = WCF.Language.get('wcf.date.shortDateTimeFormat');
			$element.text($string.replace(/\%date\%/, $date).replace(/\%time\%/, $time));
		}
	}
});

/**
 * Hash-like dictionary. Based upon idead from Prototype's hash
 * 
 * @see	https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/hash.js
 */
WCF.Dictionary = Class.extend({
	/**
	 * list of variables
	 * @var	object
	 */
	_variables: { },
	
	/**
	 * Initializes a new dictionary.
	 */
	init: function() {
		this._variables = { };
	},
	
	/**
	 * Adds an entry.
	 * 
	 * @param	string		key
	 * @param	mixed		value
	 */
	add: function(key, value) {
		this._variables[key] = value;
	},
	
	/**
	 * Adds a traditional object to current dataset.
	 * 
	 * @param	object		object
	 */
	addObject: function(object) {
		for (var $key in object) {
			this.add($key, object[$key]);
		}
	},
	
	/**
	 * Adds a dictionary to current dataset.
	 * 
	 * @param	object		dictionary
	 */
	addDictionary: function(dictionary) {
		dictionary.each($.proxy(function(pair) {
			this.add(pair.key, pair.value);
		}, this));
	},
	
	/**
	 * Retrieves the value of an entry or returns null if key is not found.
	 * 
	 * @param	string		key
	 * @returns	mixed
	 */
	get: function(key) {
		if (this.isset(key)) {
			return this._variables[key];
		}
		
		return null;
	},
	
	/**
	 * Returns true if given key is a valid entry.
	 * 
	 * @param	string		key
	 */
	isset: function(key) {
		return this._variables.hasOwnProperty(key);
	},
	
	/**
	 * Removes an entry.
	 * 
	 * @param	string		key
	 */
	remove: function(key) {
		delete this._variables[key];
	},
	
	/**
	 * Iterates through dictionary.
	 * 
	 * Usage:
	 * 	var $hash = new WCF.Dictionary();
	 * 	$hash.add('foo', 'bar');
	 * 	$hash.each(function(pair) {
	 * 		// alerts:	foo = bar
	 * 		alert(pair.key + ' = ' + pair.value);
	 * 	});
	 * 
	 * @param	function	callback
	 */
	each: function(callback) {
		if (!$.isFunction(callback)) {
			return;
		}
		
		for (var $key in this._variables) {
			var $value = this._variables[$key];
			var $pair = {
				key: $key,
				value: $value
			};
			
			callback($pair);
		}
	},
	
	/**
	 * Returns the amount of items.
	 * 
	 * @return	integer
	 */
	count: function() {
		return $.getLength(this._variables);
	},
	
	/**
	 * Returns true if dictionary is empty.
	 * 
	 * @return	integer
	 */
	isEmpty: function() {
		return !this.count();
	}
});

/**
 * Global language storage.
 * 
 * @see	WCF.Dictionary
 */
WCF.Language = {
	_variables: new WCF.Dictionary(),
	
	/**
	 * @see	WCF.Dictionary.add()
	 */
	add: function(key, value) {
		this._variables.add(key, value);
	},
	
	/**
	 * @see	WCF.Dictionary.addObject()
	 */
	addObject: function(object) {
		this._variables.addObject(object);
	},
	
	/**
	 * Retrieves a variable.
	 * 
	 * @param	string		key
	 * @return	mixed
	 */
	get: function(key, parameters) {
		// initialize parameters with an empty object
		if (parameters == null) var parameters = { };
		
		var value = this._variables.get(key);
		
		if (value === null) {
			// return key again
			return key;
		}
		else if (typeof value === 'string') {
			// transform strings into template and try to refetch
			this.add(key, new WCF.Template(value));
			return this.get(key, parameters);
		}
		else if (typeof value.fetch === 'function') {
			// evaluate templates
			value = value.fetch(parameters);
		}
		
		return value;
	}
};

/**
 * Handles multiple language input fields.
 * 
 * @param	string		elementID
 * @param	boolean		forceSelection
 * @param	object		values
 * @param	object		availableLanguages
 */
WCF.MultipleLanguageInput = Class.extend({
	/**
	 * list of available languages
	 * @var	object
	 */
	_availableLanguages: {},
	
	/**
	 * button element
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * target input element
	 * @var	jQuery
	 */
	_element: null,
	
	/**
	 * true, if data was entered after initialization
	 * @var	boolean
	 */
	_insertedDataAfterInit: false,
	
	/**
	 * enables multiple language ability
	 * @var	boolean
	 */
	_isEnabled: false,
	
	/**
	 * enforce multiple language ability
	 * @var	boolean
	 */
	_forceSelection: false,
	
	/**
	 * currently active language id
	 * @var	integer
	 */
	_languageID: 0,
	
	/**
	 * language selection list
	 * @var	jQuery
	 */
	_list: null,
	
	/**
	 * list of language values on init
	 * @var	object
	 */
	_values: null,
	
	/**
	 * Initializes multiple language ability for given element id.
	 * 
	 * @param	integer		elementID
	 * @param	boolean		forceSelection
	 * @param	boolean		isEnabled
	 * @param	object		values
	 * @param	object		availableLanguages
	 */
	init: function(elementID, forceSelection, values, availableLanguages) {
		this._button = null;
		this._element = $('#' + $.wcfEscapeID(elementID));
		this._forceSelection = forceSelection;
		this._values = values;
		this._availableLanguages = availableLanguages;
		
		// unescape values
		if ($.getLength(this._values)) {
			for (var $key in this._values) {
				this._values[$key] = WCF.String.unescapeHTML(this._values[$key]);
			}
		}
		
		// default to current user language
		this._languageID = LANGUAGE_ID;
		if (this._element.length == 0) {
			console.debug("[WCF.MultipleLanguageInput] element id '" + elementID + "' is unknown");
			return;
		}
		
		// build selection handler
		var $enableOnInit = ($.getLength(this._values) > 0) ? true : false;
		this._insertedDataAfterInit = $enableOnInit;
		this._prepareElement($enableOnInit);
		
		// listen for submit event
		this._element.parents('form').submit($.proxy(this._submit, this));
		
		this._didInit = true;
	},
	
	/**
	 * Builds language handler.
	 * 
	 * @param	boolean		enableOnInit
	 */
	_prepareElement: function(enableOnInit) {
		this._element.wrap('<div class="dropdown preInput" />');
		var $wrapper = this._element.parent();
		this._button = $('<p class="button dropdownToggle"><span>' + WCF.Language.get('wcf.global.button.disabledI18n') + '</span></p>').prependTo($wrapper);
		
		// insert list
		this._list = $('<ul class="dropdownMenu"></ul>').insertAfter(this._button);
		
		// add a special class if next item is a textarea
		if (this._button.nextAll('textarea').length) {
			this._button.addClass('dropdownCaptionTextarea');
		}
		else {
			this._button.addClass('dropdownCaption');
		}
		
		// insert available languages
		for (var $languageID in this._availableLanguages) {
			$('<li><span>' + this._availableLanguages[$languageID] + '</span></li>').data('languageID', $languageID).click($.proxy(this._changeLanguage, this)).appendTo(this._list);
		}
		
		// disable language input
		if (!this._forceSelection) {
			$('<li class="dropdownDivider" />').appendTo(this._list);
			$('<li><span>' + WCF.Language.get('wcf.global.button.disabledI18n') + '</span></li>').click($.proxy(this._disable, this)).appendTo(this._list);
		}
		
		WCF.Dropdown.initDropdown(this._button, enableOnInit);
		
		if (enableOnInit || this._forceSelection) {
			this._isEnabled = true;
			
			// pre-select current language
			this._list.children('li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				if ($listItem.data('languageID') == this._languageID) {
					$listItem.trigger('click');
				}
			}, this));
		}
		
		WCF.Dropdown.registerCallback($wrapper.wcfIdentify(), $.proxy(this._handleAction, this));
	},
	
	/**
	 * Handles dropdown actions.
	 * 
	 * @param	string		containerID
	 * @param	string		action
	 */
	_handleAction: function(containerID, action) {
		if (action === 'open') {
			this._enable();
		}
		else {
			this._closeSelection();
		}
	},
	
	/**
	 * Enables the language selection or shows the selection if already enabled.
	 * 
	 * @param	object		event
	 */
	_enable: function(event) {
		if (!this._isEnabled) {
			var $button = (this._button.is('p')) ? this._button.children('span:eq(0)') : this._button;
			$button.addClass('active');
			
			this._isEnabled = true;
		}
		
		// toggle list
		if (this._list.is(':visible')) {
			this._showSelection();
		}
	},
	
	/**
	 * Shows the language selection.
	 */
	_showSelection: function() {
		if (this._isEnabled) {
			// display status for each language
			this._list.children('li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				var $languageID = $listItem.data('languageID');
				
				if ($languageID) {
					if (this._values[$languageID] && this._values[$languageID] != '') {
						$listItem.removeClass('missingValue');
					}
					else {
						$listItem.addClass('missingValue');
					}
				}
			}, this));
		}
	},
	
	/**
	 * Closes the language selection.
	 */
	_closeSelection: function() {
		this._disable();
	},
	
	/**
	 * Changes the currently active language.
	 * 
	 * @param	object		event
	 */
	_changeLanguage: function(event) {
		var $button = $(event.currentTarget);
		this._insertedDataAfterInit = true;
		
		// save current value
		if (this._didInit) {
			this._values[this._languageID] = this._element.val();
		}
		
		// set new language
		this._languageID = $button.data('languageID');
		if (this._values[this._languageID]) {
			this._element.val(this._values[this._languageID]);
		}
		else {
			this._element.val('');
		}
		
		// update marking
		this._list.children('li').removeClass('active');
		$button.addClass('active');
		
		// update label
		this._button.children('span').addClass('active').text(this._availableLanguages[this._languageID]);
		
		// close selection and set focus on input element
		if (this._didInit) {
			this._element.blur().focus();
		}
	},
	
	/**
	 * Disables language selection for current element.
	 * 
	 * @param	object		event
	 */
	_disable: function(event) {
		if (event === undefined && this._insertedDataAfterInit) {
			event = null;
		}
		
		if (this._forceSelection || !this._list || event === null) {
			return;
		}
		
		// remove active marking
		this._button.children('span').removeClass('active').text(WCF.Language.get('wcf.global.button.disabledI18n'));
		
		// update element value
		if (this._values[LANGUAGE_ID]) {
			this._element.val(this._values[LANGUAGE_ID]);
		}
		else {
			// no value for current language found, proceed with empty input
			this._element.val();
		}
		
		if (event) {
			this._list.children('li').removeClass('active');
			$(event.currentTarget).addClass('active');
		}
		
		this._element.blur().focus();
		this._insertedDataAfterInit = false;
		this._isEnabled = false;
		this._values = { };
	},
	
	/**
	 * Prepares language variables on before submit.
	 */
	_submit: function() {
		// insert hidden form elements on before submit
		if (!this._isEnabled) {
			return 0xDEADBEEF;
		}
		
		// fetch active value
		if (this._languageID) {
			this._values[this._languageID] = this._element.val();
		}
		
		var $form = $(this._element.parents('form')[0]);
		var $elementID = this._element.wcfIdentify();
		
		for (var $languageID in this._availableLanguages) {
			if (this._values[$languageID] === undefined) {
				this._values[$languageID] = '';
			}
			
			$('<input type="hidden" name="' + $elementID + '_i18n[' + $languageID + ']" value="' + WCF.String.escapeHTML(this._values[$languageID]) + '" />').appendTo($form);
		}
		
		// remove name attribute to prevent conflict with i18n values
		this._element.removeAttr('name');
	}
});

/**
 * Number utilities.
 */
WCF.Number = {
	/**
	 * Rounds a number to a given number of decimal places. Defaults to 0.
	 * 
	 * @param	number		number
	 * @param	decimalPlaces	number of decimal places
	 * @return	number
	 */
	round: function (number, decimalPlaces) {
		decimalPlaces = Math.pow(10, (decimalPlaces || 0));
		
		return Math.round(number * decimalPlaces) / decimalPlaces;
	}
};

/**
 * String utilities.
 */
WCF.String = {
	/**
	 * Adds thousands separators to a given number.
	 * 
	 * @see		http://stackoverflow.com/a/6502556/782822
	 * @param	mixed		number
	 * @return	string
	 */
	addThousandsSeparator: function(number) {
		return String(number).replace(/(^-?\d{1,3}|\d{3})(?=(?:\d{3})+(?:$|\.))/g, '$1' + WCF.Language.get('wcf.global.thousandsSeparator'));
	},
	
	/**
	 * Escapes special HTML-characters within a string
	 * 
	 * @param	string	string
	 * @return	string
	 */
	escapeHTML: function (string) {
		return String(string).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	},
	
	/**
	 * Escapes a String to work with RegExp.
	 * 
	 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/regexp.js#L25
	 * @param	string	string
	 * @return	string
	 */
	escapeRegExp: function(string) {
		return String(string).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
	},
	
	/**
	 * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands-separators
	 * 
	 * @param	mixed	number
	 * @return	string
	 */
	formatNumeric: function(number, decimalPlaces) {
		number = String(WCF.Number.round(number, decimalPlaces || 2));
		numberParts = number.split('.');
		
		number = this.addThousandsSeparator(numberParts[0]);
		if (numberParts.length > 1) number += WCF.Language.get('wcf.global.decimalPoint') + numberParts[1];
		
		number = number.replace('-', '\u2212');
		
		return number;
	},
	
	/**
	 * Makes a string's first character lowercase
	 * 
	 * @param	string		string
	 * @return	string
	 */
	lcfirst: function(string) {
		return String(string).substring(0, 1).toLowerCase() + string.substring(1);
	},
	
	/**
	 * Makes a string's first character uppercase
	 * 
	 * @param	string		string
	 * @return	string
	 */
	ucfirst: function(string) {
		return String(string).substring(0, 1).toUpperCase() + string.substring(1);
	},
	
	/**
	 * Unescapes special HTML-characters within a string
	 * 
	 * @param	string		string
	 * @return	string
	 */
	unescapeHTML: function (string) {
		return String(string).replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
	}
};

/**
 * Basic implementation for WCF TabMenus. Use the data attributes 'active' to specify the
 * tab which should be shown on init. Furthermore you may specify a 'store' data-attribute
 * which will be filled with the currently selected tab.
 */
WCF.TabMenu = {
	/**
	 * list of tabmenu containers
	 * @var	object
	 */
	_containers: { },
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * Initializes all TabMenus
	 */
	init: function() {
		var $containers = $('.tabMenuContainer:not(.staticTabMenuContainer)');
		var self = this;
		$containers.each(function(index, tabMenu) {
			var $tabMenu = $(tabMenu);
			var $containerID = $tabMenu.wcfIdentify();
			if (self._containers[$containerID]) {
				// continue with next container
				return true;
			}
			
			if ($tabMenu.data('store') && !$('#' + $tabMenu.data('store')).length) {
				$('<input type="hidden" name="' + $tabMenu.data('store') + '" value="" id="' + $tabMenu.data('store') + '" />').appendTo($tabMenu.parents('form').find('.formSubmit'));
			}
			
			// init jQuery UI TabMenu
			self._containers[$containerID] = $tabMenu;
			$tabMenu.wcfTabs({
				active: false,
				activate: function(event, eventData) {
					var $panel = $(eventData.newPanel);
					var $container = $panel.closest('.tabMenuContainer');
					
					// store currently selected item
					var $tabMenu = $container;
					while (true) {
						// do not trigger on init
						if ($tabMenu.data('isParent') === undefined) {
							break;
						}
						
						if ($tabMenu.data('isParent')) {
							if ($tabMenu.data('store')) {
								$('#' + $tabMenu.data('store')).val($panel.attr('id'));
							}
							
							break;
						}
						else {
							$tabMenu = $tabMenu.data('parent');
						}
					}
					
					// set panel id as location hash
					if (WCF.TabMenu._didInit) {
						// do not update history if within an overlay
						if ($panel.data('inTabMenu') == undefined) {
							$panel.data('inTabMenu', ($panel.parents('.dialogContainer').length));
						}
						
						if (!$panel.data('inTabMenu')) {
							if (window.history) {
								window.history.pushState(null, document.title, window.location.toString().replace(/#.+$/, '') + '#' + $panel.attr('id'));
							}
							else {
								location.hash = '#' + $panel.attr('id');
							}
						}
					}
				}
			});
			
			$tabMenu.data('isParent', ($tabMenu.children('.tabMenuContainer, .tabMenuContent').length > 0)).data('parent', false);
			if (!$tabMenu.data('isParent')) {
				// check if we're a child element
				if ($tabMenu.parent().hasClass('tabMenuContainer')) {
					$tabMenu.data('parent', $tabMenu.parent());
				}
			}
		});
		
		// try to resolve location hash
		if (!this._didInit) {
			this._selectActiveTab();
			$(window).bind('hashchange', $.proxy(this.selectTabs, this));
			
			if (!this._selectErroneousTab()) {
				this.selectTabs();
			}
			
			if ($.browser.mozilla && location.hash) {
				var $target = $(location.hash);
				if ($target.length && $target.hasClass('tabMenuContent')) {
					var $offset = $target.offset();
					window.scrollTo($offset.left, $offset.top);
				}
			}
		}
		
		this._didInit = true;
	},
	
	/**
	 * Reloads the tab menus.
	 */
	reload: function() {
		this._containers = { };
		this.init();
	},
	
	/**
	 * Force display of first erroneous tab and returns true if at least one
	 * tab contains an error.
	 * 
	 * @return	boolean
	 */
	_selectErroneousTab: function() {
		var $foundErrors = false;
		for (var $containerID in this._containers) {
			var $tabMenu = this._containers[$containerID];
			
			if ($tabMenu.find('.formError').length) {
				$foundErrors = true;
				
				if (!$tabMenu.data('isParent')) {
					while (true) {
						if ($tabMenu.data('parent') === false) {
							break;
						}
						
						$tabMenu = $tabMenu.data('parent').wcfTabs('selectTab', $tabMenu.wcfIdentify());
					}
					
					return true;
				}
			}
		}
		
		// found an error in a non-nested tab menu
		if ($foundErrors) {
			for (var $containerID in this._containers) {
				var $tabMenu = this._containers[$containerID];
				var $formError = $tabMenu.find('.formError:eq(0)');
				
				if ($formError.length) {
					// find the tab container
					$tabMenu.wcfTabs('selectTab', $formError.parents('.tabMenuContent').wcfIdentify());
					
					while (true) {
						if ($tabMenu.data('parent') === false) {
							break;
						}
						
						$tabMenu = $tabMenu.data('parent').wcfTabs('selectTab', $tabMenu.wcfIdentify());
					}
					
					return true;
				}
			}
		}
		
		return false;
	},
	
	/**
	 * Selects the active tab menu item.
	 */
	_selectActiveTab: function() {
		for (var $containerID in this._containers) {
			var $tabMenu = this._containers[$containerID];
			if ($tabMenu.data('active')) {
				var $index = $tabMenu.data('active');
				var $subIndex = null;
				if (/-/.test($index)) {
					var $tmp = $index.split('-');
					$index = $tmp[0];
					$subIndex = $tmp[1];
				}
				
				$tabMenu.find('.tabMenuContent').each(function(innerIndex, tabMenuItem) {
					var $tabMenuItem = $(tabMenuItem);
					if ($tabMenuItem.wcfIdentify() == $index) {
						$tabMenu.wcfTabs('select', innerIndex);
						if ($subIndex !== null) {
							if ($tabMenuItem.hasClass('tabMenuContainer')) {
								$tabMenuItem.wcfTabs('selectTab', $tabMenu.data('active'));
							}
							else {
								$tabMenu.wcfTabs('selectTab', $tabMenu.data('active'));
							}
						}
						
						return false;
					}
				});
			}
		}
	},
	
	/**
	 * Resolves location hash to display tab menus.
	 * 
	 * @return	boolean
	 */
	selectTabs: function() {
		if (location.hash) {
			var $hash = location.hash.substr(1);
			
			// try to find matching tab menu container
			var $tabMenu = $('#' + $.wcfEscapeID($hash));
			if ($tabMenu.length === 1 && $tabMenu.hasClass('ui-tabs-panel')) {
				$tabMenu = $tabMenu.parent('.ui-tabs');
				if ($tabMenu.length) {
					$tabMenu.wcfTabs('selectTab', $hash);
					
					// check if this is a nested tab menu
					if ($tabMenu.hasClass('ui-tabs-panel')) {
						$hash = $tabMenu.wcfIdentify();
						$tabMenu = $tabMenu.parent('.ui-tabs');
						if ($tabMenu.length) {
							$tabMenu.wcfTabs('selectTab', $hash);
						}
					}
					
					return true;
				}
			}
		}
		
		return false;
	}
};

/**
 * Templates that may be fetched more than once with different variables.
 * Based upon ideas from Prototype's template.
 * 
 * Usage:
 * 	var myTemplate = new WCF.Template('{$hello} World');
 * 	myTemplate.fetch({ hello: 'Hi' }); // Hi World
 * 	myTemplate.fetch({ hello: 'Hello' }); // Hello World
 * 	
 * 	my2ndTemplate = new WCF.Template('{@$html}{$html}');
 * 	my2ndTemplate.fetch({ html: '<b>Test</b>' }); // <b>Test</b>&lt;b&gt;Test&lt;/b&gt;
 *	
 * 	var my3rdTemplate = new WCF.Template('You can use {literal}{$variable}{/literal}-Tags here');
 * 	my3rdTemplate.fetch({ variable: 'Not shown' }); // You can use {$variable}-Tags here
 * 
 * @param	template		template-content
 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/template.js
 */
WCF.Template = Class.extend({
	/**
	 * Prepares template
	 * 
	 * @param	$template		template-content
	 */
	init: function(template) {
		var $literals = new WCF.Dictionary();
		var $tagID = 0;
		
		// escape \ and ' and newlines
		template = template.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/(\r\n|\n|\r)/g, '\\n');
		
		// save literal-tags
		template = template.replace(/\{literal\}(.*?)\{\/literal\}/g, $.proxy(function(match) {
			// hopefully no one uses this string in one of his templates
			var id = '@@@@@@@@@@@'+Math.random()+'@@@@@@@@@@@';
			$literals.add(id, match.replace(/\{\/?literal\}/g, ''));
			
			return id;
		}, this));
		
		// remove comments
		template = template.replace(/\{\*.*?\*\}/g, '');
		
		var parseParameterList = function(parameterString) {
			var $chars = parameterString.split('');
			var $parameters = { };
			var $inName = true;
			var $name = '';
			var $value = '';
			var $doubleQuoted = false;
			var $singleQuoted = false;
			var $escaped = false;
			
			for (var $i = 0, $max = $chars.length; $i < $max; $i++) {
				var $char = $chars[$i];
				if ($inName && $char != '=' && $char != ' ') $name += $char;
				else if ($inName && $char == '=') {
					$inName = false;
					$singleQuoted = false;
					$doubleQuoted = false;
					$escaped = false;
				}
				else if (!$inName && !$singleQuoted && !$doubleQuoted && $char == ' ') {
					$inName = true;
					$parameters[$name] = $value;
					$value = $name = '';
				}
				else if (!$inName && $singleQuoted && !$escaped && $char == "'") {
					$singleQuoted = false;
					$value += $char;
				}
				else if (!$inName && !$singleQuoted && !$doubleQuoted && $char == "'") {
					$singleQuoted = true;
					$value += $char;
				}
				else if (!$inName && $doubleQuoted && !$escaped && $char == '"') {
					$doubleQuoted = false;
					$value += $char;
				}
				else if (!$inName && !$singleQuoted && !$doubleQuoted && $char == '"') {
					$doubleQuoted = true;
					$value += $char;
				}
				else if (!$inName && ($doubleQuoted || $singleQuoted) && !$escaped && $char == '\\') {
					$escaped = true;
					$value += $char;
				}
				else if (!$inName) {
					$escaped = false;
					$value += $char;
				}
			}
			$parameters[$name] = $value;
			
			if ($doubleQuoted || $singleQuoted || $escaped) throw new Error('Syntax error in parameterList: "' + parameterString + '"');
			
			return $parameters;
		};
		
		var unescape = function(string) {
			return string.replace(/\\n/g, "\n").replace(/\\\\/g, '\\').replace(/\\'/g, "'");
		};
		
		template = template.replace(/\{(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return "' + WCF.String.escapeHTML(" + content + ") + '";
		})
		// Numeric Variable
		.replace(/\{#(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return "' + WCF.String.formatNumeric(" + content + ") + '";
		})
		// Variable without escaping
		.replace(/\{@(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return "' + " + content + " + '";
		})
		// {lang}foo{/lang}
		.replace(/{lang}(.+?){\/lang}/g, function(_, content) {
			return "' + WCF.Language.get('" + unescape(content) + "') + '";
		})
		// {if}
		.replace(/\{if (.+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return	"';\n" +
				"if (" + content + ") {\n" +
				"	$output += '";
		})
		// {elseif}
		.replace(/\{else ?if (.+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return	"';\n" +
				"}\n" +
				"else if (" + content + ") {\n" +
				"	$output += '";
		})
		// {implode}
		.replace(/\{implode (.+?)\}/g, function(_, content) {
			$tagID++;
			
			content = content.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
			var $parameters = parseParameterList(content);
			
			if (typeof $parameters['from'] === 'undefined') throw new Error('Missing from attribute in implode-tag');
			if (typeof $parameters['item'] === 'undefined') throw new Error('Missing item attribute in implode-tag');
			if (typeof $parameters['glue'] === 'undefined') $parameters['glue'] = "', '";
			
			$parameters['from'] = $parameters['from'].replace(/\$([^.\[\s]+)/g, "(v.$1)");
			
			return 	"';\n"+
				"var $implode_" + $tagID + " = false;\n" +
				"for ($implodeKey_" + $tagID + " in " + $parameters['from'] + ") {\n" +
				"	v[" + $parameters['item'] + "] = " + $parameters['from'] + "[$implodeKey_" + $tagID + "];\n" +
				(typeof $parameters['key'] !== 'undefined' ? "		v[" + $parameters['key'] + "] = $implodeKey_" + $tagID + ";\n" : "") +
				"	if ($implode_" + $tagID + ") $output += " + $parameters['glue'] + ";\n" +
				"	$implode_" + $tagID + " = true;\n" +
				"	$output += '";
		})
		// {foreach}
		.replace(/\{foreach (.+?)\}/g, function(_, content) {
			$tagID++;
			
			content = content.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
			var $parameters = parseParameterList(content);
			
			if (typeof $parameters['from'] === 'undefined') throw new Error('Missing from attribute in foreach-tag');
			if (typeof $parameters['item'] === 'undefined') throw new Error('Missing item attribute in foreach-tag');
			$parameters['from'] = $parameters['from'].replace(/\$([^.\[\s]+)/g, "(v.$1)");
			
			return	"';\n" +
				"$foreach_"+$tagID+" = false;\n" +
				"for ($foreachKey_" + $tagID + " in " + $parameters['from'] + ") {\n" +
				"	$foreach_"+$tagID+" = true;\n" +
				"	break;\n" +
				"}\n" +
				"if ($foreach_"+$tagID+") {\n" +
				"	for ($foreachKey_" + $tagID + " in " + $parameters['from'] + ") {\n" +
				"		v[" + $parameters['item'] + "] = " + $parameters['from'] + "[$foreachKey_" + $tagID + "];\n" +
				(typeof $parameters['key'] !== 'undefined' ? "		v[" + $parameters['key'] + "] = $foreachKey_" + $tagID + ";\n" : "") +
				"		$output += '";
		})
		// {foreachelse}
		.replace(/\{foreachelse\}/g, 
			"';\n" +
			"	}\n" +
			"}\n" +
			"else {\n" +
			"	{\n" +
			"		$output += '"
		)
		// {/foreach}
		.replace(/\{\/foreach\}/g, 
			"';\n" +
			"	}\n" +
			"}\n" +
			"$output += '"
		)
		// {else}
		.replace(/\{else\}/g, 
			"';\n" +
			"}\n" +
			"else {\n" +
			"	$output += '"
		)
		// {/if} and {/implode}
		.replace(/\{\/(if|implode)\}/g, 
			"';\n" +
			"}\n" +
			"$output += '"
		);
		
		// call callback
		for (var key in WCF.Template.callbacks) {
			template = WCF.Template.callbacks[key](template);
		}
		
		// insert delimiter tags
		template = template.replace('{ldelim}', '{').replace('{rdelim}', '}');
		
		$literals.each(function(pair) {
			template = template.replace(pair.key, pair.value);
		});
		
		template = "$output += '" + template + "';";
		
		try {
			this.fetch = new Function("v", "if (typeof v != 'object') { v = {}; } v.__window = window; v.__wcf = window.WCF; var $output = ''; " + template + ' return $output;');
		}
		catch (e) {
			console.debug("var $output = ''; " + template + ' return $output;');
			throw e;
		}
	},
	
	/**
	 * Fetches the template with the given variables.
	 * 
	 * @param	v	variables to insert
	 * @return		parsed template
	 */
	fetch: function(v) {
		// this will be replaced in the init function
	}
});

/**
 * Array of callbacks that will be called after parsing the included tags. Only applies to Templates compiled after the callback was added.
 * 
 * @var	array<Function>
 */
WCF.Template.callbacks = [ ];

/**
 * Toggles options.
 * 
 * @param	string		element
 * @param	array		showItems
 * @param	array		hideItems
 * @param	function	callback
 */
WCF.ToggleOptions = Class.extend({
	/**
	 * target item
	 * 
	 * @var	jQuery
	 */
	_element: null,
	
	/**
	 * list of items to be shown
	 * 
	 * @var	array
	 */
	_showItems: [],
	
	/**
	 * list of items to be hidden
	 * 
	 * @var	array
	 */
	_hideItems: [],
		
	/**
	 * callback after options were toggled
	 * 
	 * @var	function
	 */
	 _callback: null,
	
	/**
	 * Initializes option toggle.
	 * 
	 * @param	string		element
	 * @param	array		showItems
	 * @param	array		hideItems
	 * @param	function	callback
	 */
	init: function(element, showItems, hideItems, callback) {
		this._element = $('#' + element);
		this._showItems = showItems;
		this._hideItems = hideItems;
		if (callback !== undefined) {
			this._callback = callback;
		}
		
		// bind event
		this._element.click($.proxy(this._toggle, this));
		
		// execute toggle on init
		this._toggle();
	},
	
	/**
	 * Toggles items.
	 */
	_toggle: function() {
		if (!this._element.prop('checked')) return;
		
		for (var $i = 0, $length = this._showItems.length; $i < $length; $i++) {
			var $item = this._showItems[$i];
			
			$('#' + $item).show();
		}
		
		for (var $i = 0, $length = this._hideItems.length; $i < $length; $i++) {
			var $item = this._hideItems[$i];
			
			$('#' + $item).hide();
		}
		
		if (this._callback !== null) {
			this._callback();
		}
	}
});

/**
 * Namespace for all kind of collapsible containers.
 */
WCF.Collapsible = {};

/**
 * Simple implementation for collapsible content, neither does it
 * store its state nor does it allow AJAX callbacks to fetch content.
 */
WCF.Collapsible.Simple = {
	/**
	 * Initializes collapsibles.
	 */
	init: function() {
		$('.jsCollapsible').each($.proxy(function(index, button) {
			this._initButton(button);
		}, this));
	},
	
	/**
	 * Binds an event listener on all buttons triggering the collapsible.
	 * 
	 * @param	object		button
	 */
	_initButton: function(button) {
		var $button = $(button);
		var $isOpen = $button.data('isOpen');
		
		if (!$isOpen) {
			// hide container on init
			$('#' + $button.data('collapsibleContainer')).hide();
		}
		
		$button.click($.proxy(this._toggle, this));
	},
	
	/**
	 * Toggles collapsible containers on click.
	 * 
	 * @param	object		event
	 */
	_toggle: function(event) {
		var $button = $(event.currentTarget);
		var $isOpen = $button.data('isOpen');
		var $target = $('#' + $.wcfEscapeID($button.data('collapsibleContainer')));
		
		if ($isOpen) {
			$target.stop().wcfBlindOut('vertical', $.proxy(function() {
				this._toggleImage($button);
			}, this));
			$isOpen = false;
		}
		else {
			$target.stop().wcfBlindIn('vertical', $.proxy(function() {
				this._toggleImage($button);
			}, this));
			$isOpen = true;
		}
		
		$button.data('isOpen', $isOpen);
		
		// suppress event
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Toggles image of target button.
	 * 
	 * @param	jQuery		button
	 */
	_toggleImage: function(button) {
		var $icon = button.find('span.icon');
		if (button.data('isOpen')) {
			$icon.removeClass('icon-chevron-right').addClass('icon-chevron-down');
		}
		else {
			$icon.removeClass('icon-chevron-down').addClass('icon-chevron-right');
		}
	}
};

/**
 * Basic implementation for collapsible containers with AJAX support. Results for open
 * and closed state will be cached.
 * 
 * @param	string		className
 */
WCF.Collapsible.Remote = Class.extend({
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * list of active containers
	 * @var	object
	 */
	_containers: {},
	
	/**
	 * container meta data
	 * @var	object
	 */
	_containerData: {},
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the controller for collapsible containers with AJAX support.
	 * 
	 * @param	string	className
	 */
	init: function(className) {
		this._className = className;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// initialize each container
		this._init();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Collapsible.Remote', $.proxy(this._init, this));
	},
	
	/**
	 * Initializes a collapsible container.
	 * 
	 * @param	string		containerID
	 */
	_init: function(containerID) {
		this._getContainers().each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (this._containers[$containerID] === undefined) {
				this._containers[$containerID] = $container;
				
				this._initContainer($containerID);
			}
		}, this));
	},
	
	/**
	 * Initializes a collapsible container.
	 * 
	 * @param	string		containerID
	 */
	_initContainer: function(containerID) {
		var $target = this._getTarget(containerID);
		var $buttonContainer = this._getButtonContainer(containerID);
		var $button = this._createButton(containerID, $buttonContainer);
		
		// store container meta data
		this._containerData[containerID] = {
			button: $button,
			buttonContainer: $buttonContainer,
			isOpen: this._containers[containerID].data('isOpen'),
			target: $target
		};
		
		// add 'jsCollapsed' CSS class
		if (!this._containers[containerID].data('isOpen')) {
			$('#' + containerID).addClass('jsCollapsed');
		}
	},
	
	/**
	 * Returns a collection of collapsible containers.
	 * 
	 * @return	jQuery
	 */
	_getContainers: function() { },
	
	/**
	 * Returns the target element for current collapsible container.
	 * 
	 * @param	integer		containerID
	 * @return	jQuery
	 */
	_getTarget: function(containerID) { },
	
	/**
	 * Returns the button container for current collapsible container.
	 * 
	 * @param	integer		containerID
	 * @return	jQuery
	 */
	_getButtonContainer: function(containerID) { },
	
	/**
	 * Creates the toggle button.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		buttonContainer
	 */
	_createButton: function(containerID, buttonContainer) {
		var $isOpen = this._containers[containerID].data('isOpen');
		var $button = $('<span class="collapsibleButton jsTooltip pointer icon icon16 icon-' + ($isOpen ? 'chevron-down' : 'chevron-right') + '" title="'+WCF.Language.get('wcf.global.button.collapsible')+'">').prependTo(buttonContainer);
		$button.data('containerID', containerID).click($.proxy(this._toggleContainer, this));
		
		return $button;
	},
	
	/**
	 * Toggles a container.
	 * 
	 * @param	object		event
	 */
	_toggleContainer: function(event) {
		var $button = $(event.currentTarget);
		var $containerID = $button.data('containerID');
		var $isOpen = this._containerData[$containerID].isOpen;
		var $state = ($isOpen) ? 'open' : 'close';
		var $newState = ($isOpen) ? 'close' : 'open';
		
		// fetch content state via AJAX
		this._proxy.setOption('data', {
			actionName: 'loadContainer',
			className: this._className,
			interfaceName: 'wcf\\data\\ILoadableContainerAction',
			objectIDs: [ this._getObjectID($containerID) ],
			parameters: $.extend(true, {
				containerID: $containerID,
				currentState: $state,
				newState: $newState
			}, this._getAdditionalParameters($containerID))
		});
		this._proxy.sendRequest();
		
		// toogle 'jsCollapsed' CSS class
		$('#' + $containerID).toggleClass('jsCollapsed');
		
		// set spinner for current button
		// this._exchangeIcon($button);
	},
	
	/**
	 * Exchanges button icon.
	 * 
	 * @param	jQuery		button
	 * @param	string		newIcon
	 */
	_exchangeIcon: function(button, newIcon) {
		newIcon = newIcon || 'spinner';
		button.removeClass('icon-chevron-down icon-chevron-right icon-spinner').addClass('icon-' + newIcon);
	},
	
	/**
	 * Returns the object id for current container.
	 * 
	 * @param	integer		containerID
	 * @return	integer
	 */
	_getObjectID: function(containerID) {
		return $('#' + containerID).data('objectID');
	},
	
	/**
	 * Returns additional parameters.
	 * 
	 * @param	integer		containerID
	 * @return	object
	 */
	_getAdditionalParameters: function(containerID) {
		return {};
	},
	
	/**
	 * Updates container content.
	 * 
	 * @param	integer		containerID
	 * @param	string		newContent
	 * @param	string		newState
	 */
	_updateContent: function(containerID, newContent, newState) {
		this._containerData[containerID].target.html(newContent);
	},
	
	/**
	 * Sets content upon successfull AJAX request.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// validate container id
		if (!data.returnValues.containerID) return;
		var $containerID = data.returnValues.containerID;
		
		// check if container id is known
		if (!this._containers[$containerID]) return;
		
		// update content storage
		this._containerData[$containerID].isOpen = (data.returnValues.isOpen) ? true : false;
		var $newState = (data.returnValues.isOpen) ? 'open' : 'close';
		
		// update container content
		this._updateContent($containerID, $.trim(data.returnValues.content), $newState);
		
		// update icon
		this._exchangeIcon(this._containerData[$containerID].button, (data.returnValues.isOpen ? 'chevron-down' : 'chevron-right'));
	}
});

/**
 * Basic implementation for collapsible containers with AJAX support. Requires collapsible
 * content to be available in DOM already, if you want to load content on the fly use
 * WCF.Collapsible.Remote instead.
 */
WCF.Collapsible.SimpleRemote = WCF.Collapsible.Remote.extend({
	/**
	 * Initializes an AJAX-based collapsible handler.
	 * 
	 * @param	string		className
	 */
	init: function(className) {
		this._super(className);
		
		// override settings for action proxy
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false
		});
	},
	
	/**
	 * @see	WCF.Collapsible.Remote._initContainer()
	 */
	_initContainer: function(containerID) {
		this._super(containerID);
		
		// hide container on init if applicable
		if (!this._containerData[containerID].isOpen) {
			this._containerData[containerID].target.hide();
			this._exchangeIcon(this._containerData[containerID].button, 'chevron-right');
		}
	},
	
	/**
	 * Toggles container visibility.
	 * 
	 * @param	object		event
	 */
	_toggleContainer: function(event) {
		var $button = $(event.currentTarget);
		var $containerID = $button.data('containerID');
		var $isOpen = this._containerData[$containerID].isOpen;
		var $currentState = ($isOpen) ? 'open' : 'close';
		var $newState = ($isOpen) ? 'close' : 'open';
		
		this._proxy.setOption('data', {
			actionName: 'toggleContainer',
			className: this._className,
			interfaceName: 'wcf\\data\\IToggleContainerAction',
			objectIDs: [ this._getObjectID($containerID) ],
			parameters: $.extend(true, {
				containerID: $containerID,
				currentState: $currentState,
				newState: $newState
			}, this._getAdditionalParameters($containerID))
		});
		this._proxy.sendRequest();
		
		// exchange icon
		this._exchangeIcon(this._containerData[$containerID].button, ($newState === 'open' ? 'chevron-down' : 'chevron-right'));
		
		// toggle container
		if ($newState === 'open') {
			this._containerData[$containerID].target.show();
		}
		else {
			this._containerData[$containerID].target.hide();
		}
		
		// toogle 'jsCollapsed' CSS class
		$('#' + $containerID).toggleClass('jsCollapsed');
		
		// update container data
		this._containerData[$containerID].isOpen = ($newState === 'open' ? true : false);
	}
});

/**
 * Provides collapsible sidebars with persistency support.
 */
WCF.Collapsible.Sidebar = Class.extend({
	/**
	 * trigger button object
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * trigger button height
	 * @var	integer
	 */
	_buttonHeight: 0,
	
	/**
	 * sidebar state
	 * @var	boolean
	 */
	_isOpen: false,
	
	/**
	 * main container object
	 * @var	jQuery
	 */
	_mainContainer: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * sidebar object
	 * @var	jQuery
	 */
	_sidebar: null,
	
	/**
	 * sidebar height
	 * @var	integer
	 */
	_sidebarHeight: 0,
	
	/**
	 * sidebar identifier
	 * @var	string
	 */
	_sidebarName: '',
	
	/**
	 * sidebar offset from document top
	 * @var	integer
	 */
	_sidebarOffset: 0,
	
	/**
	 * user panel height
	 * @var	integer
	 */
	_userPanelHeight: 0,
	
	/**
	 * Creates a new WCF.Collapsible.Sidebar object.
	 */
	init: function() {
		this._sidebar = $('.sidebar:eq(0)');
		if (!this._sidebar.length) {
			console.debug("[WCF.Collapsible.Sidebar] Could not find sidebar, aborting.");
			return;
		}
		
		this._isOpen = (this._sidebar.data('isOpen')) ? true : false;
		this._sidebarName = this._sidebar.data('sidebarName');
		this._mainContainer = $('#main');
		this._sidebarHeight = this._sidebar.height();
		this._sidebarOffset = this._sidebar.getOffsets('offset').top;
		this._userPanelHeight = $('#topMenu').outerHeight();
		
		// add toggle button
		this._button = $('<a class="collapsibleButton jsTooltip" title="' + WCF.Language.get('wcf.global.button.collapsible') + '" />').prependTo(this._sidebar);
		this._button.wrap('<span />');
		this._button.click($.proxy(this._click, this));
		this._buttonHeight = this._button.outerHeight();
		
		WCF.DOMNodeInsertedHandler.execute();
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			url: 'index.php/AJAXInvoke/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		$(document).scroll($.proxy(this._scroll, this)).resize($.proxy(this._scroll, this));
		
		this._renderSidebar();
		this._scroll();
		
		// fake resize event once transition has completed
		var $window = $(window);
		this._sidebar.on('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', function() { $window.trigger('resize'); });
	},
	
	/**
	 * Handles clicks on the trigger button.
	 */
	_click: function() {
		this._isOpen = (this._isOpen) ? false : true;
		
		this._proxy.setOption('data', {
			actionName: 'toggle',
			className: 'wcf\\system\\user\\collapsible\\content\\UserCollapsibleSidebarHandler',
			isOpen: (this._isOpen ? 1 : 0),
			sidebarName: this._sidebarName
		});
		this._proxy.sendRequest();
		
		this._renderSidebar();
	},
	
	/**
	 * Aligns the toggle button upon scroll or resize.
	 */
	_scroll: function() {
		var $window = $(window);
		var $scrollOffset = $window.scrollTop();
		
		// calculate top and bottom coordinates of visible sidebar
		var $topOffset = Math.max($scrollOffset - this._sidebarOffset, 0);
		var $bottomOffset = Math.min(this._mainContainer.height(), ($window.height() + $scrollOffset) - this._sidebarOffset);
		
		var $buttonTop = 0;
		if ($bottomOffset === $topOffset) {
			// sidebar not within visible area
			$buttonTop = this._sidebarOffset + this._sidebarHeight;
		}
		else {
			$buttonTop = $topOffset + (($bottomOffset - $topOffset) / 2);
			
			// if the user panel is above the sidebar, substract it's height
			var $overlap = Math.max(Math.min($topOffset - this._userPanelHeight, this._userPanelHeight), 0);
			if ($overlap > 0) {
				$buttonTop += ($overlap / 2);
			}
		}
		
		// ensure the button does not exceed bottom boundaries
		if (($bottomOffset - $topOffset - this._userPanelHeight) < this._buttonHeight) {
			$buttonTop = $buttonTop - this._buttonHeight;
		}
		else {
			// exclude half button height
			$buttonTop = Math.max($buttonTop - (this._buttonHeight / 2), 0);
		}
		
		this._button.css({ top: $buttonTop + 'px' });
		
	},
	
	/**
	 * Renders the sidebar state.
	 */
	_renderSidebar: function() {
		if (this._isOpen) {
			$('.sidebarOrientationLeft, .sidebarOrientationRight').removeClass('sidebarCollapsed');
		}
		else {
			$('.sidebarOrientationLeft, .sidebarOrientationRight').addClass('sidebarCollapsed');
		}
		
		// update button position
		this._scroll();
		
		// IE9 does not support transitions, fire resize event manually
		if ($.browser.msie && $.browser.version.indexOf('9') === 0) {
			$(window).trigger('resize');
		}
	}
});

/**
 * Holds userdata of the current user
 */
WCF.User = {
	/**
	 * id of the active user
	 * @var	integer
	 */
	userID: 0,
	
	/**
	 * name of the active user
	 * @var	string
	 */
	username: '',
	
	/**
	 * Initializes userdata
	 * 
	 * @param	integer	userID
	 * @param	string	username
	 */
	init: function(userID, username) {
		this.userID = userID;
		this.username = username;
	}
};

/**
 * Namespace for effect-related functions.
 */
WCF.Effect = {};

/**
 * Scrolls to a specific element offset, optionally handling menu height.
 */
WCF.Effect.Scroll = Class.extend({
	/**
	 * Scrolls to a specific element offset.
	 * 
	 * @param	jQuery		element
	 * @param	boolean		excludeMenuHeight
	 * @param	boolean		disableAnimation
	 * @return	boolean
	 */
	scrollTo: function(element, excludeMenuHeight, disableAnimation) {
		if (!element.length) {
			return true;
		}
		
		var $elementOffset = element.getOffsets('offset').top;
		var $documentHeight = $(document).height();
		var $windowHeight = $(window).height();
		
		// handles menu height
		/*if (excludeMenuHeight) {
			$elementOffset = Math.max($elementOffset - $('#topMenu').outerHeight(), 0);
		}*/
		
		if ($elementOffset > $documentHeight - $windowHeight) {
			$elementOffset = $documentHeight - $windowHeight;
			if ($elementOffset < 0) {
				$elementOffset = 0;
			}
		}
		
		if (disableAnimation === true) {
			$('html,body').scrollTop($elementOffset);
		}
		else {
			$('html,body').animate({ scrollTop: $elementOffset }, 400, function (x, t, b, c, d) {
				return -c * ( ( t = t / d - 1 ) * t * t * t - 1) + b;
			});
		}
		
		return false;
	}
});

/**
 * Creates a smooth scroll effect.
 */
WCF.Effect.SmoothScroll = WCF.Effect.Scroll.extend({
	/**
	 * Initializes effect.
	 */
	init: function() {
		var self = this;
		$(document).on('click', 'a[href$=#top],a[href$=#bottom]', function() {
			var $target = $(this.hash);
			self.scrollTo($target, true);
			
			return false;
		});
	}
});

/**
 * Creates the balloon tool-tip.
 */
WCF.Effect.BalloonTooltip = Class.extend({
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * tooltip element
	 * @var	jQuery
	 */
	_tooltip: null,
	
	/**
	 * cache viewport dimensions
	 * @var	object
	 */
	_viewportDimensions: { },
	
	/**
	 * Initializes tooltips.
	 */
	init: function() {
		if (jQuery.browser.mobile) return;
		
		if (!this._didInit) {
			// create empty div
			this._tooltip = $('<div id="balloonTooltip" class="balloonTooltip"><span id="balloonTooltipText"></span><span class="pointer"><span></span></span></div>').appendTo($('body')).hide();
			
			// get viewport dimensions
			this._updateViewportDimensions();
			
			// update viewport dimensions on resize
			$(window).resize($.proxy(this._updateViewportDimensions, this));
			
			// observe DOM changes
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Effect.BalloonTooltip', $.proxy(this.init, this));
			
			this._didInit = true;
		}
		
		// init elements
		$('.jsTooltip').each($.proxy(this._initTooltip, this));
	},
	
	/**
	 * Updates cached viewport dimensions.
	 */
	_updateViewportDimensions: function() {
		this._viewportDimensions = $(document).getDimensions();
	},
	
	/**
	 * Initializes a tooltip element.
	 * 
	 * @param	integer		index
	 * @param	object		element
	 */
	_initTooltip: function(index, element) {
		var $element = $(element);
		
		if ($element.hasClass('jsTooltip')) {
			$element.removeClass('jsTooltip');
			var $title = $element.attr('title');
			
			// ignore empty elements
			if ($title !== '') {
				$element.data('tooltip', $title);
				$element.removeAttr('title');
				
				$element.hover(
					$.proxy(this._mouseEnterHandler, this),
					$.proxy(this._mouseLeaveHandler, this)
				);
				$element.click($.proxy(this._mouseLeaveHandler, this));
			}
		}
	},
	
	/**
	 * Shows tooltip on hover.
	 * 
	 * @param	object		event
	 */
	_mouseEnterHandler: function(event) {
		var $element = $(event.currentTarget);
		
		var $title = $element.attr('title');
		if ($title && $title !== '') {
			$element.data('tooltip', $title);
			$element.removeAttr('title');
		}
		
		// reset tooltip position
		this._tooltip.css({
			top: "0px",
			left: "0px"
		});
		
		// empty tooltip, skip
		if (!$element.data('tooltip')) {
			this._tooltip.hide();
			return;
		}
		
		// update text
		this._tooltip.children('span:eq(0)').text($element.data('tooltip'));
		
		// get arrow
		var $arrow = this._tooltip.find('.pointer');
		
		// get arrow width
		this._tooltip.show();
		var $arrowWidth = $arrow.outerWidth();
		this._tooltip.hide();
		
		// calculate position
		var $elementOffsets = $element.getOffsets('offset');
		var $elementDimensions = $element.getDimensions('outer');
		var $tooltipDimensions = this._tooltip.getDimensions('outer');
		var $tooltipDimensionsInner = this._tooltip.getDimensions('inner');
		
		var $elementCenter = $elementOffsets.left + Math.ceil($elementDimensions.width / 2);
		var $tooltipHalfWidth = Math.ceil($tooltipDimensions.width / 2);
		
		// determine alignment
		var $alignment = 'center';
		if (($elementCenter - $tooltipHalfWidth) < 5) {
			$alignment = 'left';
		}
		else if ((this._viewportDimensions.width - 5) < ($elementCenter + $tooltipHalfWidth)) {
			$alignment = 'right';
		}
		
		// calculate top offset
		if ($elementOffsets.top + $elementDimensions.height + $tooltipDimensions.height - $(document).scrollTop() < $(window).height()) {
			var $top = $elementOffsets.top + $elementDimensions.height + 7;
			this._tooltip.removeClass('inverse');
			$arrow.css('top', -5);
		}
		else {
			var $top = $elementOffsets.top - $tooltipDimensions.height - 7;
			this._tooltip.addClass('inverse');
			$arrow.css('top', $tooltipDimensions.height);
		}
		
		// calculate left offset
		switch ($alignment) {
			case 'center':
				var $left = Math.round($elementOffsets.left - $tooltipHalfWidth + ($elementDimensions.width / 2));
				
				$arrow.css({
					left: ($tooltipDimensionsInner.width / 2 - $arrowWidth / 2) + "px"
				});
			break;
			
			case 'left':
				var $left = $elementOffsets.left;
				
				$arrow.css({
					left: "5px"
				});
			break;
			
			case 'right':
				var $left = $elementOffsets.left + $elementDimensions.width - $tooltipDimensions.width;
				
				$arrow.css({
					left: ($tooltipDimensionsInner.width - $arrowWidth - 5) + "px"
				});
			break;
		}
		
		// move tooltip
		this._tooltip.css({
			top: $top + "px",
			left: $left + "px"
		});
		
		// show tooltip
		this._tooltip.wcfFadeIn();
	},
	
	/**
	 * Hides tooltip once cursor left the element.
	 * 
	 * @param	object		event
	 */
	_mouseLeaveHandler: function(event) {
		this._tooltip.stop().hide().css({
			opacity: 1
		});
	}
});

/**
 * Handles clicks outside an overlay, hitting body-tag through bubbling.
 * 
 * You should always remove callbacks before disposing the attached element,
 * preventing errors from blocking the iteration. Furthermore you should
 * always handle clicks on your overlay's container and return 'false' to
 * prevent bubbling.
 */
WCF.CloseOverlayHandler = {
	/**
	 * list of callbacks
	 * @var	WCF.Dictionary
	 */
	_callbacks: new WCF.Dictionary(),
	
	/**
	 * indicates that overlay handler is listening to click events on body-tag
	 * @var	boolean
	 */
	_isListening: false,
	
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		this._bindListener();
		
		if (this._callbacks.isset(identifier)) {
			console.debug("[WCF.CloseOverlayHandler] identifier '" + identifier + "' is already bound to a callback");
			return false;
		}
		
		this._callbacks.add(identifier, callback);
	},
	
	/**
	 * Removes a callback from list.
	 * 
	 * @param	string		identifier
	 */
	removeCallback: function(identifier) {
		if (this._callbacks.isset(identifier)) {
			this._callbacks.remove(identifier);
		}
	},
	
	/**
	 * Binds click event handler.
	 */
	_bindListener: function() {
		if (this._isListening) return;
		
		$('body').click($.proxy(this._executeCallbacks, this));
		
		this._isListening = true;
	},
	
	/**
	 * Executes callbacks on click.
	 */
	_executeCallbacks: function(event) {
		this._callbacks.each(function(pair) {
			// execute callback
			pair.value();
		});
	}
};

/**
 * Notifies objects once a DOM node was inserted.
 */
WCF.DOMNodeInsertedHandler = {
	/**
	 * list of callbacks
	 * @var	array<object>
	 */
	_callbacks: [ ],
	
	/**
	 * prevent infinite loop if a callback manipulates DOM
	 * @var	boolean
	 */
	_isExecuting: false,
	
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		this._callbacks.push(callback);
	},
	
	/**
	 * Executes callbacks on click.
	 */
	_executeCallbacks: function() {
		if (this._isExecuting) return;
		
		// do not track events while executing callbacks
		this._isExecuting = true;
		
		for (var $i = 0, $length = this._callbacks.length; $i < $length; $i++) {
			this._callbacks[$i]();
		}
		
		// enable listener again
		this._isExecuting = false;
	},
	
	/**
	 * Executes all callbacks.
	 */
	execute: function() {
		this._executeCallbacks();
	}
};

/**
 * Notifies objects once a DOM node was removed.
 */
WCF.DOMNodeRemovedHandler = {
	/**
	 * list of callbacks
	 * @var	WCF.Dictionary
	 */
	_callbacks: new WCF.Dictionary(),
	
	/**
	 * prevent infinite loop if a callback manipulates DOM
	 * @var	boolean
	 */
	_isExecuting: false,
	
	/**
	 * indicates that overlay handler is listening to DOMNodeRemoved events on body-tag
	 * @var	boolean
	 */
	_isListening: false,
	
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		this._bindListener();
		
		if (this._callbacks.isset(identifier)) {
			console.debug("[WCF.DOMNodeRemovedHandler] identifier '" + identifier + "' is already bound to a callback");
			return false;
		}
		
		this._callbacks.add(identifier, callback);
	},
	
	/**
	 * Removes a callback from list.
	 * 
	 * @param	string		identifier
	 */
	removeCallback: function(identifier) {
		if (this._callbacks.isset(identifier)) {
			this._callbacks.remove(identifier);
		}
	},
	
	/**
	 * Binds click event handler.
	 */
	_bindListener: function() {
		if (this._isListening) return;
		
		$(document).bind('DOMNodeRemoved', $.proxy(this._executeCallbacks, this));
		
		this._isListening = true;
	},
	
	/**
	 * Executes callbacks if a DOM node is removed.
	 */
	_executeCallbacks: function(event) {
		if (this._isExecuting) return;
		
		// do not track events while executing callbacks
		this._isExecuting = true;
		
		this._callbacks.each(function(pair) {
			// execute callback
			pair.value(event);
		});
		
		// enable listener again
		this._isExecuting = false;
	}
};

WCF.PageVisibilityHandler = {
	/**
	 * list of callbacks
	 * @var	WCF.Dictionary
	 */
	_callbacks: new WCF.Dictionary(),
	
	/**
	 * indicates that event listeners are bound
	 * @var	boolean
	 */
	_isListening: false,
	
	/**
	 * name of window's hidden property
	 * @var	string
	 */
	_hiddenFieldName: '',
	
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		this._bindListener();
		
		if (this._callbacks.isset(identifier)) {
			console.debug("[WCF.PageVisibilityHandler] identifier '" + identifier + "' is already bound to a callback");
			return false;
		}
		
		this._callbacks.add(identifier, callback);
	},
	
	/**
	 * Removes a callback from list.
	 * 
	 * @param	string		identifier
	 */
	removeCallback: function(identifier) {
		if (this._callbacks.isset(identifier)) {
			this._callbacks.remove(identifier);
		}
	},
	
	/**
	 * Binds click event handler.
	 */
	_bindListener: function() {
		if (this._isListening) return;
		
		var $eventName = null;
		if (typeof document.hidden !== "undefined") {
			this._hiddenFieldName = "hidden";
			$eventName = "visibilitychange";
		}
		else if (typeof document.mozHidden !== "undefined") {
			this._hiddenFieldName = "mozHidden";
			$eventName = "mozvisibilitychange";
		}
		else if (typeof document.msHidden !== "undefined") {
			this._hiddenFieldName = "msHidden";
			$eventName = "msvisibilitychange";
		}
		else if (typeof document.webkitHidden !== "undefined") {
			this._hiddenFieldName = "webkitHidden";
			$eventName = "webkitvisibilitychange";
		}
		
		if ($eventName === null) {
			console.debug("[WCF.PageVisibilityHandler] This browser does not support the page visibility API.");
		}
		else {
			$(document).on($eventName, $.proxy(this._executeCallbacks, this));
		}
		
		this._isListening = true;
	},
	
	/**
	 * Executes callbacks if page is hidden/visible again.
	 */
	_executeCallbacks: function(event) {
		if (this._isExecuting) return;
		
		// do not track events while executing callbacks
		this._isExecuting = true;
		
		var $state = document[this._hiddenFieldName];
		this._callbacks.each(function(pair) {
			// execute callback
			pair.value($state);
		});
		
		// enable listener again
		this._isExecuting = false;
	}
};

/**
 * Namespace for table related classes.
 */
WCF.Table = {};

/**
 * Handles empty tables which can be used in combination with WCF.Action.Proxy.
 */
WCF.Table.EmptyTableHandler = Class.extend({
	/**
	 * handler options
	 * @var	object
	 */
	_options: {},
	
	/**
	 * class name of the relevant rows
	 * @var	string
	 */
	_rowClassName: '',
	
	/**
	 * Initalizes a new WCF.Table.EmptyTableHandler object.
	 * 
	 * @param	jQuery		tableContainer
	 * @param	string		rowClassName
	 * @param	object		options
	 */
	init: function(tableContainer, rowClassName, options) {
		this._rowClassName = rowClassName;
		this._tableContainer = tableContainer;
		
		this._options = $.extend(true, {
			emptyMessage: null,
			messageType: 'info',
			refreshPage: false,
			updatePageNumber: false
		}, options || { });
		
		WCF.DOMNodeRemovedHandler.addCallback('WCF.Table.EmptyTableHandler.' + rowClassName, $.proxy(this._remove, this));
	},
	
	/**
	 * Handles the removal of a DOM node.
	 */
	_remove: function(event) {
		var element = $(event.target);
		
		// check if DOM element is relevant
		if (element.hasClass(this._rowClassName)) {
			var tbody = element.parents('tbody:eq(0)');
			
			// check if table will be empty if DOM node is removed
			if (tbody.children('tr').length == 1) {
				if (this._options.emptyMessage) {
					// insert message
					this._tableContainer.replaceWith($('<p />').addClass(this._options.messageType).text(this._options.emptyMessage));
				}
				else if (this._options.refreshPage) {
					// refresh page
					if (this._options.updatePageNumber) {
						// calculate the new page number
						var pageNumberURLComponents = window.location.href.match(/(\?|&)pageNo=(\d+)/g);
						if (pageNumberURLComponents) {
							var currentPageNumber = pageNumberURLComponents[pageNumberURLComponents.length - 1].match(/\d+/g);
							if (this._options.updatePageNumber > 0) {
								currentPageNumber++;
							}
							else {
								currentPageNumber--;
							}
							
							window.location = window.location.href.replace(pageNumberURLComponents[pageNumberURLComponents.length - 1], pageNumberURLComponents[pageNumberURLComponents.length - 1][0] + 'pageNo=' + currentPageNumber);
						}
					}
					else {
						window.location.reload();
					}
				}
				else {
					// simply remove the table container
					this._tableContainer.remove();
				}
			}
		}
	}
});

/**
 * Namespace for search related classes.
 */
WCF.Search = {};

/**
 * Performs a quick search.
 */
WCF.Search.Base = Class.extend({
	/**
	 * notification callback
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * comma seperated list
	 * @var	boolean
	 */
	_commaSeperated: false,
	
	/**
	 * delay in miliseconds before a request is send to the server
	 * @var	integer
	 */
	_delay: 0,
	
	/**
	 * list with values that are excluded from seaching
	 * @var	array
	 */
	_excludedSearchValues: [],
	
	/**
	 * count of available results
	 * @var	integer
	 */
	_itemCount: 0,
	
	/**
	 * item index, -1 if none is selected
	 * @var	integer
	 */
	_itemIndex: -1,
	
	/**
	 * result list
	 * @var	jQuery
	 */
	_list: null,
	
	/**
	 * old search string, used for comparison
	 * @var	array<string>
	 */
	_oldSearchString: [ ],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * search input field
	 * @var	jQuery
	 */
	_searchInput: null,
	
	/**
	 * minimum search input length, MUST be 1 or higher
	 * @var	integer
	 */
	_triggerLength: 3,
	
	/**
	 * delay timer
	 * @var	WCF.PeriodicalExecuter
	 */
	_timer: null,
	
	/**
	 * Initializes a new search.
	 * 
	 * @param	jQuery		searchInput
	 * @param	object		callback
	 * @param	array		excludedSearchValues
	 * @param	boolean		commaSeperated
	 * @param	boolean		showLoadingOverlay
	 */
	init: function(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay) {
		if (callback !== null && callback !== undefined && !$.isFunction(callback)) {
			console.debug("[WCF.Search.Base] The given callback is invalid, aborting.");
			return;
		}
		
		this._callback = (callback) ? callback : null;
		this._delay = 0;
		this._excludedSearchValues = [];
		if (excludedSearchValues) {
			this._excludedSearchValues = excludedSearchValues;
		}
		
		this._searchInput = $(searchInput);
		if (!this._searchInput.length) {
			console.debug("[WCF.Search.Base] Selector '" + searchInput + "' for search input is invalid, aborting.");
			return;
		}
		
		this._searchInput.keydown($.proxy(this._keyDown, this)).keyup($.proxy(this._keyUp, this)).wrap('<span class="dropdown" />');
		
		if ($.browser.mozilla && $.browser.touch) {
			this._searchInput.on('input', $.proxy(this._keyUp, this));
		}
		
		this._list = $('<ul class="dropdownMenu" />').insertAfter(this._searchInput);
		this._commaSeperated = (commaSeperated) ? true : false;
		this._oldSearchString = [ ];
		
		this._itemCount = 0;
		this._itemIndex = -1;
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: (showLoadingOverlay !== true ? false : true),
			success: $.proxy(this._success, this),
			autoAbortPrevious: true
		});
		
		if (this._searchInput.is('input')) {
			this._searchInput.attr('autocomplete', 'off');
		}
		
		this._searchInput.blur($.proxy(this._blur, this));
		
		WCF.Dropdown.initDropdownFragment(this._searchInput.parent(), this._list);
	},
	
	/**
	 * Closes the dropdown after a short delay.
	 */
	_blur: function() {
		var self = this;
		new WCF.PeriodicalExecuter(function(pe) {
			if (self._list.is(':visible')) {
				self._clearList(false);
			}
			
			pe.stop();
		}, 250);
	},
	
	/**
	 * Blocks execution of 'Enter' event.
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		if (event.which === $.ui.keyCode.ENTER) {
			var $dropdown = this._searchInput.parents('.dropdown');
			
			if ($dropdown.data('disableAutoFocus')) {
				if (this._itemIndex !== -1) {
					event.preventDefault();
				}
			}
			else if ($dropdown.data('preventSubmit') || this._itemIndex !== -1) {
				event.preventDefault();
			}
		}
	},
	
	/**
	 * Performs a search upon key up.
	 * 
	 * @param	object		event
	 */
	_keyUp: function(event) {
		// handle arrow keys and return key
		switch (event.which) {
			case 37: // arrow-left
			case 39: // arrow-right
				return;
			break;
			
			case 38: // arrow up
				this._selectPreviousItem();
				return;
			break;
			
			case 40: // arrow down
				this._selectNextItem();
				return;
			break;
			
			case 13: // return key
				return this._selectElement(event);
			break;
		}
		
		var $content = this._getSearchString(event);
		if ($content === '') {
			this._clearList(true);
		}
		else if ($content.length >= this._triggerLength) {
			var $parameters = {
				data: {
					excludedSearchValues: this._excludedSearchValues,
					searchString: $content
				}
			};
			
			if (this._delay) {
				if (this._timer !== null) {
					this._timer.stop();
				}
				
				var self = this;
				this._timer = new WCF.PeriodicalExecuter(function() {
					self._queryServer($parameters);
					
					self._timer.stop();
					self._timer = null;
				}, this._delay);
			}
			else {
				this._queryServer($parameters);
			}
		}
		else {
			// input below trigger length
			this._clearList(false);
		}
	},
	
	/**
	 * Queries the server.
	 * 
	 * @param	object		parameters
	 */
	_queryServer: function(parameters) {
		this._searchInput.parents('.searchBar').addClass('loading');
		this._proxy.setOption('data', {
			actionName: 'getSearchResultList',
			className: this._className,
			interfaceName: 'wcf\\data\\ISearchAction',
			parameters: this._getParameters(parameters)
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Sets query delay in miliseconds.
	 * 
	 * @param	integer		delay
	 */
	setDelay: function(delay) {
		this._delay = delay;
	},
	
	/**
	 * Selects the next item in list.
	 */
	_selectNextItem: function() {
		if (this._itemCount === 0) {
			return;
		}
		
		// remove previous marking
		this._itemIndex++;
		if (this._itemIndex === this._itemCount) {
			this._itemIndex = 0;
		}
		
		this._highlightSelectedElement();
	},
	
	/**
	 * Selects the previous item in list.
	 */
	_selectPreviousItem: function() {
		if (this._itemCount === 0) {
			return;
		}
		
		this._itemIndex--;
		if (this._itemIndex === -1) {
			this._itemIndex = this._itemCount - 1;
		}
		
		this._highlightSelectedElement();
	},
	
	/**
	 * Highlights the active item.
	 */
	_highlightSelectedElement: function() {
		this._list.find('li').removeClass('dropdownNavigationItem');
		this._list.find('li:eq(' + this._itemIndex + ')').addClass('dropdownNavigationItem');
	},
	
	/**
	 * Selects the active item by pressing the return key.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_selectElement: function(event) {
		if (this._itemCount === 0) {
			return true;
		}
		
		this._list.find('li.dropdownNavigationItem').trigger('click');
		
		return false;
	},
	
	/**
	 * Returns search string.
	 * 
	 * @return	string
	 */
	_getSearchString: function(event) {
		var $searchString = $.trim(this._searchInput.val());
		if (this._commaSeperated) {
			var $keyCode = event.keyCode || event.which;
			if ($keyCode == $.ui.keyCode.COMMA) {
				// ignore event if char is ','
				return '';
			}
			
			var $current = $searchString.split(',');
			var $length = $current.length;
			for (var $i = 0; $i < $length; $i++) {
				// remove whitespaces at the beginning or end
				$current[$i] = $.trim($current[$i]);
			}
			
			for (var $i = 0; $i < $length; $i++) {
				var $part = $current[$i];
				
				if (this._oldSearchString[$i]) {
					// compare part
					if ($part != this._oldSearchString[$i]) {
						// current part was changed
						$searchString = $part;
						break;
					}
				}
				else {
					// new part was added
					$searchString = $part;
					break;
				}
			}
			
			this._oldSearchString = $current;
		}
		
		return $searchString;
	},
	
	/**
	 * Returns parameters for quick search.
	 * 
	 * @param	object		parameters
	 * @return	object
	 */
	_getParameters: function(parameters) {
		return parameters;
	},
	
	/**
	 * Evalutes search results.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._clearList(false);
		this._searchInput.parents('.searchBar').removeClass('loading');
		
		if ($.getLength(data.returnValues)) {
			for (var $i in data.returnValues) {
				var $item = data.returnValues[$i];
				
				this._createListItem($item);
			}
		}
		else if (!this._handleEmptyResult()) {
			return;
		}
		
		WCF.CloseOverlayHandler.addCallback('WCF.Search.Base', $.proxy(function() { this._clearList(); }, this));
		
		var $containerID = this._searchInput.parents('.dropdown').wcfIdentify();
		if (!WCF.Dropdown.getDropdownMenu($containerID).hasClass('dropdownOpen')) {
			WCF.Dropdown.toggleDropdown($containerID);
		}
		
		// pre-select first item
		this._itemIndex = -1;
		if (!WCF.Dropdown.getDropdown($containerID).data('disableAutoFocus')) {
			this._selectNextItem();
		}
	},
	
	/**
	 * Handles empty result lists, should return false if dropdown should be hidden.
	 * 
	 * @return	boolean
	 */
	_handleEmptyResult: function() {
		return false;
	},
	
	/**
	 * Creates a new list item.
	 * 
	 * @param	object		item
	 * @return	jQuery
	 */
	_createListItem: function(item) {
		var $listItem = $('<li><span>' + WCF.String.escapeHTML(item.label) + '</span></li>').appendTo(this._list);
		$listItem.data('objectID', item.objectID).data('label', item.label).click($.proxy(this._executeCallback, this));
		
		this._itemCount++;
		
		return $listItem;
	},
	
	/**
	 * Executes callback upon result click.
	 * 
	 * @param	object		event
	 */
	_executeCallback: function(event) {
		var $clearSearchInput = false;
		var $listItem = $(event.currentTarget);
		// notify callback
		if (this._commaSeperated) {
			// auto-complete current part
			var $result = $listItem.data('label');
			for (var $i = 0, $length = this._oldSearchString.length; $i < $length; $i++) {
				var $part = this._oldSearchString[$i];
				if ($result.toLowerCase().indexOf($part.toLowerCase()) === 0) {
					this._oldSearchString[$i] = $result;
					this._searchInput.val(this._oldSearchString.join(', '));
					
					if ($.browser.webkit) {
						// chrome won't display the new value until the textarea is rendered again
						// this quick fix forces chrome to render it again, even though it changes nothing
						this._searchInput.css({ display: 'block' });
					}
					
					// set focus on input field again
					var $position = this._searchInput.val().toLowerCase().indexOf($result.toLowerCase()) + $result.length;
					this._searchInput.focus().setCaret($position);
					
					break;
				}
			}
		}
		else {
			if (this._callback === null) {
				this._searchInput.val($listItem.data('label'));
			}
			else {
				$clearSearchInput = (this._callback($listItem.data()) === true) ? true : false;
			}
		}
		
		// close list and revert input
		this._clearList($clearSearchInput);
	},
	
	/**
	 * Closes the suggestion list and clears search input on demand.
	 * 
	 * @param	boolean		clearSearchInput
	 */
	_clearList: function(clearSearchInput) {
		if (clearSearchInput && !this._commaSeperated) {
			this._searchInput.val('');
		}
		
		// close dropdown
		WCF.Dropdown.getDropdown(this._searchInput.parents('.dropdown').wcfIdentify()).removeClass('dropdownOpen');
		WCF.Dropdown.getDropdownMenu(this._searchInput.parents('.dropdown').wcfIdentify()).removeClass('dropdownOpen');
		
		this._list.end().empty();
		
		WCF.CloseOverlayHandler.removeCallback('WCF.Search.Base');
		
		// reset item navigation
		this._itemCount = 0;
		this._itemIndex = -1;
	},
	
	/**
	 * Adds an excluded search value.
	 * 
	 * @param	string		value
	 */
	addExcludedSearchValue: function(value) {
		if (!WCF.inArray(value, this._excludedSearchValues)) {
			this._excludedSearchValues.push(value);
		}
	},
	
	/**
	 * Removes an excluded search value.
	 * 
	 * @param	string		value
	 */
	removeExcludedSearchValue: function(value) {
		var index = $.inArray(value, this._excludedSearchValues);
		if (index != -1) {
			this._excludedSearchValues.splice(index, 1);
		}
	}
});

/**
 * Provides quick search for users and user groups.
 * 
 * @see	WCF.Search.Base
 */
WCF.Search.User = WCF.Search.Base.extend({
	/**
	 * @see	WCF.Search.Base._className
	 */
	_className: 'wcf\\data\\user\\UserAction',
	
	/**
	 * include user groups in search
	 * @var	boolean
	 */
	_includeUserGroups: false,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, includeUserGroups, excludedSearchValues, commaSeperated) {
		this._includeUserGroups = includeUserGroups;
		
		this._super(searchInput, callback, excludedSearchValues, commaSeperated);
	},
	
	/**
	 * @see	WCF.Search.Base._getParameters()
	 */
	_getParameters: function(parameters) {
		parameters.data.includeUserGroups = this._includeUserGroups ? 1 : 0;
		
		return parameters;
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(item) {
		var $listItem = this._super(item);
		
		var $icon = null;
		if (item.icon) {
			$icon = $(item.icon);
		}
		else if (this._includeUserGroups && item.type === 'group') {
			$icon = $('<span class="icon icon16 icon-group" />');
		}
		
		if ($icon) {
			var $label = $listItem.find('span').detach();
			
			var $box16 = $('<div />').addClass('box16').appendTo($listItem);
			
			$box16.append($icon);
			$box16.append($('<div />').append($label));
		}
		
		// insert item type
		$listItem.data('type', item.type);
		
		return $listItem;
	}
});

/**
 * Namespace for system-related classes.
 */
WCF.System = { };

/**
 * Namespace for dependency-related classes.
 */
WCF.System.Dependency = { };

/**
 * JavaScript Dependency Manager.
 */
WCF.System.Dependency.Manager = {
	/**
	 * list of callbacks grouped by identifier
	 * @var	object
	 */
	_callbacks: { },
	
	/**
	 * list of loaded identifiers
	 * @var	array<string>
	 */
	_loaded: [ ],
	
	/**
	 * list of setup callbacks grouped by identifier
	 * @var	object
	 */
	_setupCallbacks: { },
	
	/**
	 * Registers a callback for given identifier, will be executed after all setup
	 * callbacks have been invoked.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	register: function(identifier, callback) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.System.Dependency.Manager] Callback for identifier '" + identifier + "' is invalid, aborting.");
			return;
		}
		
		// already loaded, invoke now
		if (WCF.inArray(identifier, this._loaded)) {
			setTimeout(function() {
				callback();
			}, 1);
		}
		else {
			if (!this._callbacks[identifier]) {
				this._callbacks[identifier] = [ ];
			}
			
			this._callbacks[identifier].push(callback);
		}
	},
	
	/**
	 * Registers a setup callback for given identifier, will be invoked
	 * prior to all other callbacks.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	setup: function(identifier, callback) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.System.Dependency.Manager] Setup callback for identifier '" + identifier + "' is invalid, aborting.");
			return;
		}
		
		if (!this._setupCallbacks[identifier]) {
			this._setupCallbacks[identifier] = [ ];
		}
		
		this._setupCallbacks[identifier].push(callback);
	},
	
	/**
	 * Invokes all callbacks for given identifier and marks it as loaded.
	 * 
	 * @param	string		identifier
	 */
	invoke: function(identifier) {
		if (this._setupCallbacks[identifier]) {
			for (var $i = 0, $length = this._setupCallbacks[identifier].length; $i < $length; $i++) {
				this._setupCallbacks[identifier][$i]();
			}
			
			delete this._setupCallbacks[identifier];
		}
		
		this._loaded.push(identifier);
		
		if (this._callbacks[identifier]) {
			for (var $i = 0, $length = this._callbacks[identifier].length; $i < $length; $i++) {
				this._callbacks[identifier][$i]();
			}
			
			delete this._callbacks[identifier];
		}
	}
};

/**
 * Provides flexible dropdowns for tab-based menus.
 */
WCF.System.FlexibleMenu = {
	/**
	 * list of containers
	 * @var	object<jQuery>
	 */
	_containers: { },
	
	/**
	 * list of registered container ids
	 * @var	array<string>
	 */
	_containerIDs: [ ],
	
	/**
	 * list of dropdowns
	 * @var	object<jQuery>
	 */
	_dropdowns: { },
	
	/**
	 * list of dropdown menus
	 * @var	object<jQuery>
	 */
	_dropdownMenus: { },
	
	/**
	 * list of hidden status for containers
	 * @var	object<boolean>
	 */
	_hasHiddenItems: { },
	
	/**
	 * true if menus are currently rebuilt
	 * @var	boolean
	 */
	_isWorking: false,
	
	/**
	 * list of tab menu items per container
	 * @var	object<jQuery>
	 */
	_menuItems: { },
	
	/**
	 * Initializes the WCF.System.FlexibleMenu class.
	 */
	init: function() {
		// register .mainMenu and .navigationHeader by default
		this.registerMenu('mainMenu');
		this.registerMenu($('.navigationHeader:eq(0)').wcfIdentify());
		
		this._registerTabMenus();
		
		$(window).resize($.proxy(this.rebuildAll, this));
		WCF.DOMNodeInsertedHandler.addCallback('WCF.System.FlexibleMenu', $.proxy(this._registerTabMenus, this));
	},
	
	/**
	 * Registers tab menus.
	 */
	_registerTabMenus: function() {
		// register tab menus
		$('.tabMenuContainer:not(.jsFlexibleMenuEnabled)').each(function(index, tabMenuContainer) {
			var $navigation = $(tabMenuContainer).children('nav');
			if ($navigation.length && $navigation.find('> ul:eq(0) > li').length) {
				WCF.System.FlexibleMenu.registerMenu($navigation.wcfIdentify());
			}
		});
	},
	
	/**
	 * Registers a tab-based menu by id.
	 * 
	 * Required DOM:
	 * <container>
	 * 	<ul style="white-space: nowrap">
	 * 		<li>tab 1</li>
	 * 		<li>tab 2</li>
	 * 		...
	 * 		<li>tab n</li>
	 * 	</ul>
	 * </container>
	 * 
	 * @param	string		containerID
	 */
	registerMenu: function(containerID) {
		var $container = $('#' + containerID);
		if (!$container.length) {
			console.debug("[WCF.System.FlexibleMenu] Unable to find container identified by '" + containerID + "', aborting.");
			return;
		}
		
		this._containerIDs.push(containerID);
		this._containers[containerID] = $container;
		this._menuItems[containerID] = $container.find('> ul:eq(0) > li');
		this._dropdowns[containerID] = $('<li class="dropdown"><a class="icon icon16 icon-list" /></li>').data('containerID', containerID).click($.proxy(this._click, this));
		this._dropdownMenus[containerID] = $('<ul class="dropdownMenu" />').appendTo(this._dropdowns[containerID]);
		this._hasHiddenItems[containerID] = false;
		
		this.rebuild(containerID);
		
		WCF.Dropdown.initDropdown(this._dropdowns[containerID].children('a'));
	},
	
	/**
	 * Rebuilds all registered containers.
	 */
	rebuildAll: function() {
		if (this._isWorking) {
			return;
		}
		
		this._isWorking = true;
		
		for (var $i = 0, $length = this._containerIDs.length; $i < $length; $i++) {
			this.rebuild(this._containerIDs[$i]);
		}
		
		this._isWorking = false;
	},
	
	/**
	 * Rebuilds a container, will be automatically invoked on window resize and registering.
	 * 
	 * @param	string		containerID
	 */
	rebuild: function(containerID) {
		if (!this._containers[containerID]) {
			console.debug("[WCF.System.FlexibleMenu] Cannot rebuild unknown container identified by '" + containerID + "'");
			return;
		}
		
		var $changedItems = false;
		var $container = this._containers[containerID];
		var $currentWidth = 0;
		
		// the current width is based upon all items without the dropdown
		var $menuItems = this._menuItems[containerID].filter(':visible');
		for (var $i = 0, $length = $menuItems.length; $i < $length; $i++) {
			$currentWidth += $($menuItems[$i]).outerWidth(true);
		}
		
		// insert dropdown for calculation purposes
		if (!this._hasHiddenItems[containerID]) {
			this._dropdowns[containerID].appendTo($container.children('ul:eq(0)'));
		}
		
		var $dropdownWidth = this._dropdowns[containerID].outerWidth(true);
		
		// remove dropdown previously inserted
		if (!this._hasHiddenItems[containerID]) {
			this._dropdowns[containerID].detach();
		}
		
		var $maximumWidth = $container.parent().innerWidth();
		
		// substract padding from the parent element
		$maximumWidth -= parseInt($container.parent().css('padding-left').replace(/px$/, '')) + parseInt($container.parent().css('padding-right').replace(/px$/, ''));
		
		// substract margins and paddings from the container itself
		$maximumWidth -= parseInt($container.css('margin-left').replace(/px$/, '')) + parseInt($container.css('margin-right').replace(/px$/, ''));
		$maximumWidth -= parseInt($container.css('padding-left').replace(/px$/, '')) + parseInt($container.css('padding-right').replace(/px$/, ''));
		
		// substract paddings from the actual list
		$maximumWidth -= parseInt($container.children('ul:eq(0)').css('padding-left').replace(/px$/, '')) + parseInt($container.children('ul:eq(0)').css('padding-right').replace(/px$/, '')); 
		if ($currentWidth > $maximumWidth || (this._hasHiddenItems[containerID] && ($currentWidth > $maximumWidth - $dropdownWidth))) {
			var $menuItems = $menuItems.filter(':not(.active):not(.ui-state-active):visible');
			
			// substract dropdown width from maximum width
			$maximumWidth -= $dropdownWidth;
			
			// hide items starting with the last in list (ignores active item)
			for (var $i = ($menuItems.length - 1); $i >= 0; $i--) {
				if ($currentWidth > $maximumWidth) {
					var $item = $($menuItems[$i]);
					$currentWidth -= $item.outerWidth(true);
					$item.hide();
					
					$changedItems = true;
					this._hasHiddenItems[containerID] = true;
				}
				else {
					break;
				}
			}
			
			if (this._hasHiddenItems[containerID]) {
				this._dropdowns[containerID].appendTo($container.children('ul:eq(0)'));
			}
		}
		else if (this._hasHiddenItems[containerID] && $currentWidth < $maximumWidth) {
			var $hiddenItems = this._menuItems[containerID].filter(':not(:visible)');
			
			// substract dropdown width from maximum width unless it is the last item
			$maximumWidth -= $dropdownWidth;
			
			// reverts items starting with the first hidden one
			for (var $i = 0, $length = $hiddenItems.length; $i < $length; $i++) {
				var $item = $($hiddenItems[$i]);
				$currentWidth += $item.outerWidth();
				
				if ($i + 1 == $length) {
					$maximumWidth += $dropdownWidth;
				}
				
				if ($currentWidth < $maximumWidth) {
					// enough space, show item
					$item.css('display', '');
					$changedItems = true;
				}
				else {
					break;
				}
			}
			
			if ($changedItems) {
				this._hasHiddenItems[containerID] = (this._menuItems[containerID].filter(':not(:visible)').length > 0);
				if (!this._hasHiddenItems[containerID]) {
					this._dropdowns[containerID].detach();
				}
			}
		}
		
		// build dropdown menu for hidden items
		if ($changedItems) {
			this._dropdownMenus[containerID].empty();
			this._menuItems[containerID].filter(':not(:visible)').each($.proxy(function(index, item) {
				$('<li>' + $(item).html() + '</li>').appendTo(this._dropdownMenus[containerID]);
			}, this));
		}
	}
};

/**
 * Namespace for mobile device-related classes.
 */
WCF.System.Mobile = { };

/**
 * Handles general navigation and UX on mobile devices.
 */
WCF.System.Mobile.UX = {
	/**
	 * true if mobile optimizations are enabled
	 * @var	boolean
	 */
	_enabled: false,
	
	/**
	 * main container
	 * @var	jQuery
	 */
	_main: null,
	
	/**
	 * sidebar container
	 * @var	jQuery
	 */
	_sidebar: null,
	
	/**
	 * Initializes the WCF.System.Mobile.UX class.
	 */
	init: function() {
		this._enabled = false;
		this._main = $('#main');
		this._sidebar = this._main.find('> div > div > .sidebar');
		
		if ($.browser.touch) {
			$('html').addClass('touch');
		}
		
		enquire.register('screen and (max-width: 800px)', {
			match: $.proxy(this._enable, this),
			unmatch: $.proxy(this._disable, this),
			setup: $.proxy(this._setup, this),
			deferSetup: true
		});
		
		if ($.browser.msie && this._sidebar.width() > 305) {
			// sidebar is rarely broken on IE9/IE10
			this._sidebar.css('display', 'none').css('display', '');
		}
	},
	
	/**
	 * Initializes the mobile optimization once the media query matches.
	 */
	_setup: function() {
		this._initSidebarToggleButtons();
		this._initSearchBar();
		this._initButtonGroupNavigation();
		
		WCF.CloseOverlayHandler.addCallback('WCF.System.Mobile.UX', $.proxy(this._closeMenus, this));
		WCF.DOMNodeInsertedHandler.addCallback('WCF.System.Mobile.UX', $.proxy(this._initButtonGroupNavigation, this));
	},
	
	/**
	 * Enables the mobile optimization.
	 */
	_enable: function() {
		this._enabled = true;
		
		if ($.browser.msie) {
			this._sidebar.css('display', 'none').css('display', '');
		}
	},
	
	/**
	 * Disables the mobile optimization.
	 */
	_disable: function() {
		this._enabled = false;
		
		if ($.browser.msie) {
			this._sidebar.css('display', 'none').css('display', '');
		}
	},
	
	/**
	 * Initializes the sidebar toggle buttons.
	 */
	_initSidebarToggleButtons: function() {
		var $sidebarLeft = this._main.hasClass('sidebarOrientationLeft');
		var $sidebarRight = this._main.hasClass('sidebarOrientationRight');
		if ($sidebarLeft || $sidebarRight) {
			// use icons if language item is empty/non-existant
			var $languageShowSidebar = 'wcf.global.sidebar.show' + ($sidebarLeft ? 'Left' : 'Right') + 'Sidebar';
			if ($languageShowSidebar === WCF.Language.get($languageShowSidebar) || WCF.Language.get($languageShowSidebar) === '') {
				$languageShowSidebar = '<span class="icon icon16 icon-double-angle-' + ($sidebarLeft ? 'left' : 'right') + '" />';
			}
			
			var $languageHideSidebar = 'wcf.global.sidebar.hide' + ($sidebarLeft ? 'Left' : 'Right') + 'Sidebar';
			if ($languageHideSidebar === WCF.Language.get($languageHideSidebar) || WCF.Language.get($languageHideSidebar) === '') {
				$languageHideSidebar = '<span class="icon icon16 icon-double-angle-' + ($sidebarLeft ? 'right' : 'left') + '" />';
			}
			
			// add toggle buttons
			var self = this;
			$('<span class="button small mobileSidebarToggleButton">' + $languageShowSidebar + '</span>').appendTo($('.content')).click(function() { self._main.addClass('mobileShowSidebar'); });
			$('<span class="button small mobileSidebarToggleButton">' + $languageHideSidebar + '</span>').appendTo($('.sidebar')).click(function() { self._main.removeClass('mobileShowSidebar'); });
		}
	},
	
	/**
	 * Initializes the search bar.
	 */
	_initSearchBar: function() {
		var $searchBar = $('.searchBar:eq(0)');
		
		var self = this;
		$searchBar.click(function() {
			if (self._enabled) {
				$searchBar.addClass('searchBarOpen');
			}
		});
		
		this._main.click(function() { $searchBar.removeClass('searchBarOpen'); });
	},
	
	/**
	 * Initializes the button group lists, converting them into native dropdowns.
	 */
	_initButtonGroupNavigation: function() {
		$('.buttonGroupNavigation:not(.jsMobileButtonGroupNavigation)').each(function(index, navigation) {
			var $navigation = $(navigation).addClass('jsMobileButtonGroupNavigation');
			var $button = $('<a class="dropdownLabel"><span class="icon icon24 icon-list" /></a>').prependTo($navigation);
			
			$button.click(function() { $button.next().toggleClass('open'); return false; });
		});
	},
	
	/**
	 * Closes menus.
	 */
	_closeMenus: function() {
		$('.jsMobileButtonGroupNavigation > ul.open').removeClass('open');
	}
};

WCF.System.Page = { };

WCF.System.Page.Multiple = Class.extend({
	_cache: { },
	_options: { },
	_pageNo: 1,
	_pages: 0,
	
	init: function(options) {
		this._options = $.extend({
			// elements
			container: null,
			pagination: null,
			
			// callbacks
			loadItems: null
		}, options);
	},
	
	/**
	 * Callback after page has changed.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_showPage: function(event, data) {
		if (data && data.activePage) {
			if (!data.template) {
				this._previousPageNo = this._pageNo;
			}
			
			this._pageNo = data.activePage;
		}
		
		if (this._cache[this._pageNo] || (data && data.template)) {
			this._cache[this._previousPageNo] = this._list.children().detach();
			
			if (data && data.template) {
				this._list.html(data.template);
			}
			else {
				this._list.append(this._cache[this._pageNo]);
			}
		}
		else {
			this._loadItems();
		}
	},
	
	showPage: function(pageNo, template) {
		this._showPage(null, {
			activePage: pageNo,
			template: template
		});
	}
});

/**
 * System notification overlays.
 * 
 * @param	string		message
 * @param	string		cssClassNames
 */
WCF.System.Notification = Class.extend({
	/**
	 * callback on notification close
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * CSS class names
	 * @var	string
	 */
	_cssClassNames: '',
	
	/**
	 * notification message
	 * @var	string
	 */
	_message: '',
	
	/**
	 * notification overlay
	 * @var	jQuery
	 */
	_overlay: null,
	
	/**
	 * Creates a new system notification overlay.
	 * 
	 * @param	string		message
	 * @param	string		cssClassNames
	 */
	init: function(message, cssClassNames) {
		this._cssClassNames = cssClassNames || 'success';
		this._message = message || WCF.Language.get('wcf.global.success');
		this._overlay = $('#systemNotification');
		
		if (!this._overlay.length) {
			this._overlay = $('<div id="systemNotification"><p></p></div>').hide().appendTo(document.body);
		}
	},
	
	/**
	 * Shows the notification overlay.
	 * 
	 * @param	object		callback
	 * @param	integer		duration
	 * @param	string		message
	 * @param	string		cssClassName
	 */
	show: function(callback, duration, message, cssClassNames) {
		duration = parseInt(duration);
		if (!duration) duration = 2000;
		
		if (callback && $.isFunction(callback)) {
			this._callback = callback;
		}
		
		this._overlay.children('p').html((message || this._message));
		this._overlay.children('p').removeClass().addClass((cssClassNames || this._cssClassNames));
		
		// hide overlay after specified duration
		new WCF.PeriodicalExecuter($.proxy(this._hide, this), duration);
		
		this._overlay.wcfFadeIn(undefined, 300);
	},
	
	/**
	 * Hides the notification overlay after executing the callback.
	 * 
	 * @param	WCF.PeriodicalExecuter		pe
	 */
	_hide: function(pe) {
		if (this._callback !== null) {
			this._callback();
		}
		
		this._overlay.wcfFadeOut(undefined, 300);
		
		pe.stop();
	}
});

/**
 * Provides dialog-based confirmations.
 */
WCF.System.Confirmation = {
	/**
	 * notification callback
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * confirmation dialog
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * callback parameters
	 * @var	object
	 */
	_parameters: null,
	
	/**
	 * dialog visibility
	 * @var	boolean
	 */
	_visible: false,
	
	/**
	 * confirmation button
	 * @var	jQuery
	 */
	_confirmationButton: null,
	
	/**
	 * Displays a confirmation dialog.
	 * 
	 * @param	string		message
	 * @param	object		callback
	 * @param	object		parameters
	 * @param	jQuery		template
	 */
	show: function(message, callback, parameters, template) {
		if (this._visible) {
			console.debug('[WCF.System.Confirmation] Confirmation dialog is already open, refusing action.');
			return;
		}
		
		if (!$.isFunction(callback)) {
			console.debug('[WCF.System.Confirmation] Given callback is invalid, aborting.');
			return;
		}
		
		this._callback = callback;
		this._parameters = parameters;
		
		var $render = true;
		if (this._dialog === null) {
			this._createDialog();
			$render = false;
		}
		
		this._dialog.find('#wcfSystemConfirmationContent').empty().hide();
		if (template && template.length) {
			template.appendTo(this._dialog.find('#wcfSystemConfirmationContent').show());
		}
		
		this._dialog.find('p').text(message);
		this._dialog.wcfDialog({
			onClose: $.proxy(this._close, this),
			onShow: $.proxy(this._show, this),
			title: WCF.Language.get('wcf.global.confirmation.title')
		});
		if ($render) {
			this._dialog.wcfDialog('render');
		}
		
		this._confirmationButton.focus();
		this._visible = true;
	},
	
	/**
	 * Creates the confirmation dialog on first use.
	 */
	_createDialog: function() {
		this._dialog = $('<div id="wcfSystemConfirmation" class="systemConfirmation"><p /><div id="wcfSystemConfirmationContent" /></div>').hide().appendTo(document.body);
		var $formButtons = $('<div class="formSubmit" />').appendTo(this._dialog);
		
		this._confirmationButton = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.confirmation.confirm') + '</button>').data('action', 'confirm').click($.proxy(this._click, this)).appendTo($formButtons);
		$('<button>' + WCF.Language.get('wcf.global.confirmation.cancel') + '</button>').data('action', 'cancel').click($.proxy(this._click, this)).appendTo($formButtons);
	},
	
	/**
	 * Handles button clicks.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._notify($(event.currentTarget).data('action'));
	},
	
	/**
	 * Handles dialog being closed.
	 */
	_close: function() {
		if (this._visible) {
			this._notify('cancel');
		}
	},
	
	/**
	 * Notifies callback upon user's decision.
	 * 
	 * @param	string		action
	 */
	_notify: function(action) {
		this._visible = false;
		this._dialog.wcfDialog('close');
		
		this._callback(action, this._parameters);
	},
	
	/**
	 * Tries to set focus on confirm button.
	 */
	_show: function() {
		this._dialog.find('button.buttonPrimary').blur().focus();
	}
};

/**
 * Disables the ability to scroll the page.
 */
WCF.System.DisableScrolling = {
	/**
	 * number of times scrolling was disabled (nested calls)
	 * @var	integer
	 */
	_depth: 0,
	
	/**
	 * old overflow-value of the body element
	 * @var	string
	 */
	_oldOverflow: null,
	
	/**
	 * Disables scrolling.
	 */
	disable: function () {
		// do not block scrolling on touch devices
		if ($.browser.touch) {
			return;
		}
		
		if (this._depth === 0) {
			this._oldOverflow = $(document.body).css('overflow');
			$(document.body).css('overflow', 'hidden');
		}
		
		this._depth++;
	},
	
	/**
	 * Enables scrolling again.
	 * Must be called the same number of times disable() was called to enable scrolling.
	 */
	enable: function () {
		if (this._depth === 0) return;
		
		this._depth--;
		
		if (this._depth === 0) {
			$(document.body).css('overflow', this._oldOverflow);
		}
	}
};

/**
 * Provides the 'jump to page' overlay.
 */
WCF.System.PageNavigation = {
	/**
	 * submit button
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * page No description
	 * @var	jQuery
	 */
	_description: null,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * active element id
	 * @var	string
	 */
	_elementID: '',
	
	/**
	 * list of tracked navigation bars
	 * @var	object
	 */
	_elements: { },
	
	/**
	 * page No input
	 * @var	jQuery
	 */
	_pageNo: null,
	
	/**
	 * Initializes the 'jump to page' overlay for given selector.
	 * 
	 * @param	string		selector
	 * @param	object		callback
	 */
	init: function(selector, callback) {
		var $elements = $(selector);
		if (!$elements.length) {
			return;
		}
		
		callback = callback || null;
		if (callback !== null && !$.isFunction(callback)) {
			console.debug("[WCF.System.PageNavigation] Callback for selector '" + selector + "' is invalid, aborting.");
			return;
		}
		
		this._initElements($elements, callback);
	},
	
	/**
	 * Initializes the 'jump to page' overlay for given elements.
	 * 
	 * @param	jQuery		elements
	 * @param	object		callback
	 */
	_initElements: function(elements, callback) {
		var self = this;
		elements.each(function(index, element) {
			var $element = $(element);
			var $elementID = $element.wcfIdentify();
			
			if (self._elements[$elementID] === undefined) {
				self._elements[$elementID] = $element;
				$element.find('li.jumpTo').data('elementID', $elementID).click($.proxy(self._click, self));
			}
		}).data('callback', callback);
	},
	
	/**
	 * Shows the 'jump to page' overlay.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._elementID = $(event.currentTarget).data('elementID');
		
		if (this._dialog === null) {
			this._dialog = $('<div id="pageNavigationOverlay" />').hide().appendTo(document.body);
			
			var $fieldset = $('<fieldset><legend>' + WCF.Language.get('wcf.global.page.jumpTo') + '</legend></fieldset>').appendTo(this._dialog);
			$('<dl><dt><label for="jsPageNavigationPageNo">' + WCF.Language.get('wcf.global.page.jumpTo') + '</label></dt><dd></dd></dl>').appendTo($fieldset);
			this._pageNo = $('<input type="number" id="jsPageNavigationPageNo" value="1" min="1" max="1" class="tiny" />').keyup($.proxy(this._keyUp, this)).appendTo($fieldset.find('dd'));
			this._description = $('<small></small>').insertAfter(this._pageNo);
			var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
			this._button = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.submit') + '</button>').click($.proxy(this._submit, this)).appendTo($formSubmit);
		}
		
		this._button.enable();
		this._description.html(WCF.Language.get('wcf.global.page.jumpTo.description').replace(/#pages#/, this._elements[this._elementID].data('pages')));
		this._pageNo.val(this._elements[this._elementID].data('pages')).attr('max', this._elements[this._elementID].data('pages'));
		
		this._dialog.wcfDialog({
			'title': WCF.Language.get('wcf.global.page.pageNavigation')
		});
	},
	
	/**
	 * Validates the page No input.
	 *
	 * @param	Event		event
	 */
	_keyUp: function(event) {
		if (event.which == $.ui.keyCode.ENTER && !this._button.prop('disabled')) {
			this._submit();
			return;
		}
		
		var $pageNo = parseInt(this._pageNo.val()) || 0;
		if ($pageNo < 1 || $pageNo > this._pageNo.attr('max')) {
			this._button.disable();
		}
		else {
			this._button.enable();
		}
	},
	
	/**
	 * Redirects to given page No.
	 */
	_submit: function() {
		var $pageNavigation = this._elements[this._elementID];
		if ($pageNavigation.data('callback') === null) {
			var $redirectURL = $pageNavigation.data('link').replace(/pageNo=%d/, 'pageNo=' + this._pageNo.val());
			window.location = $redirectURL;
		}
		else {
			$pageNavigation.data('callback')(this._pageNo.val());
			this._dialog.wcfDialog('close');
		}
	}
};

/**
 * Sends periodical requests to protect the session from expiring. By default
 * it will send a request 1 minute before it would expire.
 * 
 * @param	integer		seconds
 */
WCF.System.KeepAlive = Class.extend({
	/**
	 * Initializes the WCF.System.KeepAlive class.
	 * 
	 * @param	integer		seconds
	 */
	init: function(seconds) {
		new WCF.PeriodicalExecuter(function(pe) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'keepAlive',
					className: 'wcf\\data\\session\\SessionAction'
				},
				failure: function() { pe.stop(); },
				showLoadingOverlay: false,
				suppressErrors: true
			});
		}, (seconds * 1000));
	}
});

/**
 * Default implementation for inline editors.
 * 
 * @param	string		elementSelector
 */
WCF.InlineEditor = Class.extend({
	/**
	 * list of registered callbacks
	 * @var	array<object>
	 */
	_callbacks: [ ],
	
	/**
	 * list of dropdown selections
	 * @var	object
	 */
	_dropdowns: { },
	
	/**
	 * list of container elements
	 * @var	object
	 */
	_elements: { },
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * list of known options
	 * @var	array<object>
	 */
	_options: [ ],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of data to update upon success
	 * @var	array<object>
	 */
	_updateData: [ ],
	
	/**
	 * Initializes a new inline editor.
	 */
	init: function(elementSelector) {
		var $elements = $(elementSelector);
		if (!$elements.length) {
			return;
		}
		
		this._setOptions();
		var $quickOption = '';
		for (var $i = 0, $length = this._options.length; $i < $length; $i++) {
			if (this._options[$i].isQuickOption) {
				$quickOption = this._options[$i].optionName;
				break;
			}
		}
		
		var self = this;
		$elements.each(function(index, element) {
			var $element = $(element);
			var $elementID = $element.wcfIdentify();
			
			// find trigger element
			var $trigger = self._getTriggerElement($element);
			if ($trigger === null || $trigger.length !== 1) {
				return;
			}
			
			$trigger.click($.proxy(self._show, self)).data('elementID', $elementID);
			if ($quickOption) {
				// simulate click on target action
				$trigger.disableSelection().data('optionName', $quickOption).dblclick($.proxy(self._click, self));
			}
			
			// store reference
			self._elements[$elementID] = $element;
		});
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		WCF.CloseOverlayHandler.addCallback('WCF.InlineEditor', $.proxy(this._closeAll, this));
		
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'), 'success');
	},
	
	/**
	 * Closes all inline editors.
	 */
	_closeAll: function() {
		for (var $elementID in this._elements) {
			this._hide($elementID);
		}
	},
	
	/**
	 * Sets options for this inline editor.
	 */
	_setOptions: function() {
		this._options = [ ];
	},
	
	/**
	 * Register an option callback for validation and execution.
	 * 
	 * @param	object		callback
	 */
	registerCallback: function(callback) {
		if ($.isFunction(callback)) {
			this._callbacks.push(callback);
		}
	},
	
	/**
	 * Returns the triggering element.
	 * 
	 * @param	jQuery		element
	 * @return	jQuery
	 */
	_getTriggerElement: function(element) {
		return null;
	},
	
	/**
	 * Shows a dropdown menu if options are available.
	 * 
	 * @param	object		event
	 */
	_show: function(event) {
		var $elementID = $(event.currentTarget).data('elementID');
		
		// build dropdown
		var $trigger = null;
		if (!this._dropdowns[$elementID]) {
			$trigger = this._getTriggerElement(this._elements[$elementID]).addClass('dropdownToggle').wrap('<span class="dropdown" />');
			this._dropdowns[$elementID] = $('<ul class="dropdownMenu" />').insertAfter($trigger);
		}
		this._dropdowns[$elementID].empty();
		
		// validate options
		var $hasOptions = false;
		var $lastElementType = '';
		for (var $i = 0, $length = this._options.length; $i < $length; $i++) {
			var $option = this._options[$i];
			
			if ($option.optionName === 'divider') {
				if ($lastElementType !== '' && $lastElementType !== 'divider') {
					$('<li class="dropdownDivider" />').appendTo(this._dropdowns[$elementID]);
					$lastElementType = $option.optionName;
				}
			}
			else if (this._validate($elementID, $option.optionName) || this._validateCallbacks($elementID, $option.optionName)) {
				var $listItem = $('<li><span>' + $option.label + '</span></li>').appendTo(this._dropdowns[$elementID]);
				$listItem.data('elementID', $elementID).data('optionName', $option.optionName).data('isQuickOption', ($option.isQuickOption ? true : false)).click($.proxy(this._click, this));
				
				$hasOptions = true;
				$lastElementType = $option.optionName;
			}
		}
		
		if ($hasOptions) {
			// if last child is divider, remove it
			var $lastChild = this._dropdowns[$elementID].children().last();
			if ($lastChild.hasClass('dropdownDivider')) {
				$lastChild.remove();
			}
			
			// check if only element is a quick option
			var $quickOption = null;
			var $count = 0;
			this._dropdowns[$elementID].children().each(function(index, child) {
				var $child = $(child);
				if (!$child.hasClass('dropdownDivider')) {
					if ($child.data('isQuickOption')) {
						$quickOption = $child;
					}
					else {
						$count++;
					}
				}
			});
			
			if (!$count) {
				$quickOption.trigger('click');
				
				if ($trigger !== null) {
					WCF.Dropdown.close($trigger.parents('.dropdown').wcfIdentify());
				}
				
				return false;
			}
		}
		
		if ($trigger !== null) {
			WCF.Dropdown.initDropdown($trigger, true);
		}
		
		return false;
	},
	
	/**
	 * Validates an option.
	 * 
	 * @param	string		elementID
	 * @param	string		optionName
	 * @returns	boolean
	 */
	_validate: function(elementID, optionName) {
		return false;
	},
	
	/**
	 * Validates an option provided by callbacks.
	 * 
	 * @param	string		elementID
	 * @param	string		optionName
	 * @return	boolean
	 */
	_validateCallbacks: function(elementID, optionName) {
		var $length = this._callbacks.length;
		if ($length) {
			for (var $i = 0; $i < $length; $i++) {
				if (this._callbacks[$i].validate(this._elements[elementID], optionName)) {
					return true;
				}
			}
		}
		
		return false;
	},
	
	/**
	 * Handles AJAX responses.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $length = this._updateData.length;
		if (!$length) {
			return;
		}
		
		this._updateState(data);
		
		this._updateData = [ ];
	},
	
	/**
	 * Update element states based upon update data.
	 * 
	 * @param	object		data
	 */
	_updateState: function(data) { },
	
	/**
	 * Handles clicks within dropdown.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $listItem = $(event.currentTarget);
		var $elementID = $listItem.data('elementID');
		var $optionName = $listItem.data('optionName');
		
		if (!this._execute($elementID, $optionName)) {
			this._executeCallback($elementID, $optionName);
		}
		
		this._hide($elementID);
	},
	
	/**
	 * Executes actions associated with an option.
	 * 
	 * @param	string		elementID
	 * @param	string		optionName
	 * @return	boolean
	 */
	_execute: function(elementID, optionName) {
		return false;
	},
	
	/**
	 * Executes actions associated with an option provided by callbacks.
	 * 
	 * @param	string		elementID
	 * @param	string		optionName
	 * @return	boolean
	 */
	_executeCallback: function(elementID, optionName) {
		var $length = this._callbacks.length;
		if ($length) {
			for (var $i = 0; $i < $length; $i++) {
				if (this._callbacks[$i].execute(this._elements[elementID], optionName)) {
					return true;
				}
			}
		}
		
		return false;
	},
	
	/**
	 * Hides a dropdown menu.
	 * 
	 * @param	string		elementID
	 */
	_hide: function(elementID) {
		if (this._dropdowns[elementID]) {
			this._dropdowns[elementID].empty().removeClass('dropdownOpen');
		}
	}
});

/**
 * Default implementation for ajax file uploads
 * 
 * @param	jquery		buttonSelector
 * @param	jquery		fileListSelector
 * @param	string		className
 * @param	jquery		options
 */
WCF.Upload = Class.extend({
	/**
	 * name of the upload field
	 * @var	string
	 */
	_name: '__files[]',
	
	/**
	 * button selector
	 * @var	jQuery
	 */
	_buttonSelector: null,
	
	/**
	 * file list selector
	 * @var	jQuery
	 */
	_fileListSelector: null,
	
	/**
	 * upload file
	 * @var	jQuery
	 */
	_fileUpload: null,
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * iframe for IE<10 fallback
	 * @var	jQuery
	 */
	_iframe: null,
	
	/**
	 * internal file id
	 * @var	integer
	 */
	_internalFileID: 0,
	
	/**
	 * additional options
	 * @var	jQuery
	 */
	_options: {},
	
	/**
	 * upload matrix
	 * @var	array
	 */
	_uploadMatrix: [],
	
	/**
	 * true, if the active user's browser supports ajax file uploads
	 * @var	boolean
	 */
	_supportsAJAXUpload: true,
	
	/**
	 * fallback overlay for stupid browsers
	 * @var	jquery
	 */
	_overlay: null,
	
	/**
	 * Initializes a new upload handler.
	 * 
	 * @param	string		buttonSelector
	 * @param	string		fileListSelector
	 * @param	string		className
	 * @param	object		options
	 */
	init: function(buttonSelector, fileListSelector, className, options) {
		this._buttonSelector = buttonSelector;
		this._fileListSelector = fileListSelector;
		this._className = className;
		this._internalFileID = 0;
		this._options = $.extend(true, {
			action: 'upload',
			multiple: false,
			url: 'index.php/AJAXUpload/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		}, options || { });
		
		// check for ajax upload support
		var $xhr = new XMLHttpRequest();
		this._supportsAJAXUpload = ($xhr && ('upload' in $xhr) && ('onprogress' in $xhr.upload));
		
		// create upload button
		this._createButton();
	},
	
	/**
	 * Creates the upload button.
	 */
	_createButton: function() {
		if (this._supportsAJAXUpload) {
			this._fileUpload = $('<input type="file" name="' + this._name + '" ' + (this._options.multiple ? 'multiple="true" ' : '') + '/>');
			this._fileUpload.change($.proxy(this._upload, this));
			var $button = $('<p class="button uploadButton"><span>' + WCF.Language.get('wcf.global.button.upload') + '</span></p>');
			$button.prepend(this._fileUpload);
		}
		else {
			var $button = $('<p class="button uploadFallbackButton"><span>' + WCF.Language.get('wcf.global.button.upload') + '</span></p>');
			$button.click($.proxy(this._showOverlay, this));
		}
		
		this._insertButton($button);
	},
	
	/**
	 * Inserts the upload button.
	 * 
	 * @param	jQuery		button
	 */
	_insertButton: function(button) {
		this._buttonSelector.append(button);
	},
	
	/**
	 * Removes the upload button.
	 */
	_removeButton: function() {
		var $selector = '.uploadButton';
		if (!this._supportsAJAXUpload) {
			$selector = '.uploadFallbackButton';
		}
		
		this._buttonSelector.find($selector).remove();
	},
	
	/**
	 * Callback for file uploads.
	 */
	_upload: function() {
		var $files = this._fileUpload.prop('files');
		if ($files.length) {
			var $fd = new FormData();
			var $uploadID = this._createUploadMatrix($files);
			
			// no more files left, abort
			if (!this._uploadMatrix[$uploadID].length) {
				return;
			}
			
			for (var $i = 0, $length = $files.length; $i < $length; $i++) {
				if (this._uploadMatrix[$uploadID][$i]) {
					var $internalFileID = this._uploadMatrix[$uploadID][$i].data('internalFileID');
					$fd.append('__files[' + $internalFileID + ']', $files[$i]);
				}
			}
			
			$fd.append('actionName', this._options.action);
			$fd.append('className', this._className);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$fd.append('parameters[' + $name + ']', $additionalParameters[$name]);
			}
			
			var self = this;
			$.ajax({ 
				type: 'POST',
				url: this._options.url,
				enctype: 'multipart/form-data',
				data: $fd,
				contentType: false,
				processData: false,
				success: function(data, textStatus, jqXHR) {
					self._success($uploadID, data);
				},
				error: $.proxy(this._error, this),
				xhr: function() {
					var $xhr = $.ajaxSettings.xhr();
					if ($xhr) {
						$xhr.upload.addEventListener('progress', function(event) {
							self._progress($uploadID, event);
						}, false);
					}
					return $xhr;
				}
			});
		}
	},
	
	/**
	 * Creates upload matrix for provided files.
	 * 
	 * @param	array<object>		files
	 * @return	integer
	 */
	_createUploadMatrix: function(files) {
		if (files.length) {
			var $uploadID = this._uploadMatrix.length;
			this._uploadMatrix[$uploadID] = [ ];
			
			for (var $i = 0, $length = files.length; $i < $length; $i++) {
				var $file = files[$i];
				var $li = this._initFile($file);
				
				if (!$li.hasClass('uploadFailed')) {
					$li.data('filename', $file.name).data('internalFileID', this._internalFileID++);
					this._uploadMatrix[$uploadID][$i] = $li;
				}
			}
			
			return $uploadID;
		}
		
		return null;
	},
	
	/**
	 * Callback for success event.
	 * 
	 * @param	integer		uploadID
	 * @param	object		data
	 */
	_success: function(uploadID, data) { },
	
	/**
	 * Callback for error event.
	 * 
	 * @param	jQuery		jqXHR
	 * @param	string		textStatus
	 * @param	string		errorThrown
	 */
	_error: function(jqXHR, textStatus, errorThrown) { },
	
	/**
	 * Callback for progress event.
	 * 
	 * @param	integer		uploadID
	 * @param	object		event
	 */
	_progress: function(uploadID, event) {
		var $percentComplete = Math.round(event.loaded * 100 / event.total);
		
		for (var $i in this._uploadMatrix[uploadID]) {
			this._uploadMatrix[uploadID][$i].find('progress').attr('value', $percentComplete);
		}
	},
	
	/**
	 * Returns additional parameters.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return {};
	},
	
	/**
	 * Initializes list item for uploaded file.
	 * 
	 * @return	jQuery
	 */
	_initFile: function(file) {
		return $('<li>' + file.name + ' (' + file.size + ')<progress max="100" /></li>').appendTo(this._fileListSelector);
	},
	
	/**
	 * Shows the fallback overlay (work in progress)
	 */
	_showOverlay: function() {
		// create iframe
		if (this._iframe === null) {
			this._iframe = $('<iframe name="__fileUploadIFrame" />').hide().appendTo(document.body);
		}
		
		// create overlay
		if (!this._overlay) {
			this._overlay = $('<div><form enctype="multipart/form-data" method="post" action="' + this._options.url + '" target="__fileUploadIFrame" /></div>').hide().appendTo(document.body);
			
			var $form = this._overlay.find('form');
			$('<dl class="wide"><dd><input type="file" id="__fileUpload" name="' + this._name + '" ' + (this._options.multiple ? 'multiple="true" ' : '') + '/></dd></dl>').appendTo($form);
			$('<div class="formSubmit"><input type="submit" value="Upload" accesskey="s" /></div></form>').appendTo($form);
			
			$('<input type="hidden" name="isFallback" value="1" />').appendTo($form);
			$('<input type="hidden" name="actionName" value="' + this._options.action + '" />').appendTo($form);
			$('<input type="hidden" name="className" value="' + this._className + '" />').appendTo($form);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$('<input type="hidden" name="' + $name + '" value="' + $additionalParameters[$name] + '" />').appendTo($form);
			}
			
			$form.submit($.proxy(function() {
				var $file = {
					name: this._getFilename(),
					size: ''
				};
				
				var $uploadID = this._createUploadMatrix([ $file ]);
				var self = this;
				this._iframe.data('loading', true).off('load').load(function() { self._evaluateResponse($uploadID); });
				this._overlay.wcfDialog('close');
			}, this));
		}
		
		this._overlay.wcfDialog({
			title: WCF.Language.get('wcf.global.button.upload')
		});
	},
	
	/**
	 * Evaluates iframe response.
	 * 
	 * @param	integer		uploadID
	 */
	_evaluateResponse: function(uploadID) {
		var $returnValues = $.parseJSON(this._iframe.contents().find('pre').html());
		this._success(uploadID, $returnValues);
	},
	
	/**
	 * Returns name of selected file.
	 * 
	 * @return	string
	 */
	_getFilename: function() {
		return $('#__fileUpload').val().split('\\').pop();
	}
});

/**
 * Default implementation for parallel AJAX file uploads.
 */
WCF.Upload.Parallel = WCF.Upload.extend({
	/**
	 * @see	WCF.Upload.init()
	 */
	init: function(buttonSelector, fileListSelector, className, options) {
		// force multiple uploads
		options = $.extend(true, options || { }, {
			multiple: true
		});
		
		this._super(buttonSelector, fileListSelector, className, options);
	},
	
	/**
	 * @see	WCF.Upload._upload()
	 */
	_upload: function() {
		var $files = this._fileUpload.prop('files');
		for (var $i = 0, $length = $files.length; $i < $length; $i++) {
			var $file = $files[$i];
			var $formData = new FormData();
			var $internalFileID = this._createUploadMatrix($file);
			
			if (!this._uploadMatrix[$internalFileID].length) {
				continue;
			}
			
			$formData.append('__files[' + $internalFileID + ']', $file);
			$formData.append('actionName', this._options.action);
			$formData.append('className', this._className);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$formData.append('parameters[' + $name + ']', $additionalParameters[$name]);
			}
			
			this._sendRequest($internalFileID, $formData);
		}
	},
	
	/**
	 * Sends an AJAX request to upload a file.
	 * 
	 * @param	integer		internalFileID
	 * @param	FormData	formData
	 */
	_sendRequest: function(internalFileID, formData) {
		var self = this;
		$.ajax({ 
			type: 'POST',
			url: this._options.url,
			enctype: 'multipart/form-data',
			data: formData,
			contentType: false,
			processData: false,
			success: function(data, textStatus, jqXHR) {
				self._success(internalFileID, data);
			},
			error: $.proxy(this._error, this),
			xhr: function() {
				var $xhr = $.ajaxSettings.xhr();
				if ($xhr) {
					$xhr.upload.addEventListener('progress', function(event) {
						self._progress(internalFileID, event);
					}, false);
				}
				return $xhr;
			}
		});
	},
	
	/**
	 * Creates upload matrix for provided file and returns its internal file id.
	 * 
	 * @param	object		file
	 * @return	integer
	 */
	_createUploadMatrix: function(file) {
		var $li = this._initFile(file);
		if (!$li.hasClass('uploadFailed')) {
			$li.data('filename', file.name).data('internalFileID', this._internalFileID);
			this._uploadMatrix[this._internalFileID++] = $li;
			
			return this._internalFileID - 1;
		}
		
		return null;
	},
	
	/**
	 * Callback for success event.
	 * 
	 * @param	integer		internalFileID
	 * @param	object		data
	 */
	_success: function(internalFileID, data) { },
	
	/**
	 * Callback for progress event.
	 * 
	 * @param	integer		internalFileID
	 * @param	object		event
	 */
	_progress: function(internalFileID, event) {
		var $percentComplete = Math.round(event.loaded * 100 / event.total);
		
		this._uploadMatrix[internalFileID].find('progress').attr('value', $percentComplete);
	},
	
	/**
	 * @see	WCF.Upload._showOverlay()
	 */
	_showOverlay: function() {
		// create iframe
		if (this._iframe === null) {
			this._iframe = $('<iframe name="__fileUploadIFrame" />').hide().appendTo(document.body);
		}
		
		// create overlay
		if (!this._overlay) {
			this._overlay = $('<div><form enctype="multipart/form-data" method="post" action="' + this._options.url + '" target="__fileUploadIFrame" /></div>').hide().appendTo(document.body);
			
			var $form = this._overlay.find('form');
			$('<dl class="wide"><dd><input type="file" id="__fileUpload" name="' + this._name + '" ' + (this._options.multiple ? 'multiple="true" ' : '') + '/></dd></dl>').appendTo($form);
			$('<div class="formSubmit"><input type="submit" value="Upload" accesskey="s" /></div></form>').appendTo($form);
			
			$('<input type="hidden" name="isFallback" value="1" />').appendTo($form);
			$('<input type="hidden" name="actionName" value="' + this._options.action + '" />').appendTo($form);
			$('<input type="hidden" name="className" value="' + this._className + '" />').appendTo($form);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$('<input type="hidden" name="' + $name + '" value="' + $additionalParameters[$name] + '" />').appendTo($form);
			}
			
			$form.submit($.proxy(function() {
				var $file = {
					name: this._getFilename(),
					size: ''
				};
				
				var $internalFileID = this._createUploadMatrix($file);
				var self = this;
				this._iframe.data('loading', true).off('load').load(function() { self._evaluateResponse($internalFileID); });
				this._overlay.wcfDialog('close');
			}, this));
		}
		
		this._overlay.wcfDialog({
			title: WCF.Language.get('wcf.global.button.upload')
		});
	},
	
	/**
	 * Evaluates iframe response.
	 * 
	 * @param	integer		internalFileID
	 */
	_evaluateResponse: function(internalFileID) {
		var $returnValues = $.parseJSON(this._iframe.contents().find('pre').html());
		this._success(internalFileID, $returnValues);
	}
});

/**
 * Namespace for sortables.
 */
WCF.Sortable = { };

/**
 * Sortable implementation for lists.
 * 
 * @param	string		containerID
 * @param	string		className
 * @param	integer		offset
 * @param	object		options
 */
WCF.Sortable.List = Class.extend({
	/**
	 * additional parameters for AJAX request
	 * @var	object
	 */
	_additionalParameters: { },
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * container id
	 * @var	string
	 */
	_containerID: '',
	
	/**
	 * container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * show order offset
	 * @var	integer
	 */
	_offset: 0,
	
	/**
	 * list of options
	 * @var	object
	 */
	_options: { },
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * object structure
	 * @var	object
	 */
	_structure: { },
	
	/**
	 * Creates a new sortable list.
	 * 
	 * @param	string		containerID
	 * @param	string		className
	 * @param	integer		offset
	 * @param	object		options
	 * @param	boolean		isSimpleSorting
	 * @param	object		additionalParameters
	 */
	init: function(containerID, className, offset, options, isSimpleSorting, additionalParameters) {
		this._additionalParameters = additionalParameters || { };
		this._containerID = $.wcfEscapeID(containerID);
		this._container = $('#' + this._containerID);
		this._className = className;
		this._offset = (offset) ? offset : 0;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		this._structure = { };
		
		// init sortable
		this._options = $.extend(true, {
			axis: 'y',
			connectWith: '#' + this._containerID + ' .sortableList',
			disableNesting: 'sortableNoNesting',
			doNotClear: true,
			errorClass: 'sortableInvalidTarget',
			forcePlaceholderSize: true,
			helper: 'clone',
			items: 'li:not(.sortableNoSorting)',
			opacity: .6,
			placeholder: 'sortablePlaceholder',
			tolerance: 'pointer',
			toleranceElement: '> span'
		}, options || { });
		
		if (isSimpleSorting) {
			$('#' + this._containerID + ' .sortableList').sortable(this._options);
		}
		else {
			$('#' + this._containerID + ' > .sortableList').nestedSortable(this._options);
		}
		
		if (this._className) {
			var $formSubmit = this._container.find('.formSubmit');
			if (!$formSubmit.length) {
				$formSubmit = this._container.next('.formSubmit');
				if (!$formSubmit.length) {
					console.debug("[WCF.Sortable.Simple] Unable to find form submit for saving, aborting.");
					return;
				}
			}
			
			$formSubmit.children('button[data-type="submit"]').click($.proxy(this._submit, this));
		}
	},
	
	/**
	 * Saves object structure.
	 */
	_submit: function() {
		// reset structure
		this._structure = { };
		
		// build structure
		this._container.find('.sortableList').each($.proxy(function(index, list) {
			var $list = $(list);
			var $parentID = $list.data('objectID');
			
			if ($parentID !== undefined) {
				$list.children(this._options.items).each($.proxy(function(index, listItem) {
					var $objectID = $(listItem).data('objectID');
					
					if (!this._structure[$parentID]) {
						this._structure[$parentID] = [ ];
					}
					
					this._structure[$parentID].push($objectID);
				}, this));
			}
		}, this));
		
		// send request
		var $parameters = $.extend(true, {
			data: {
				offset: this._offset,
				structure: this._structure
			}
		}, this._additionalParameters);
		
		this._proxy.setOption('data', {
			actionName: 'updatePosition',
			className: this._className,
			interfaceName: 'wcf\\data\\ISortableAction',
			parameters: $parameters
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Shows notification upon success.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (this._notification === null) {
			this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
		}
		
		this._notification.show();
	}
});

WCF.Popover = Class.extend({
	/**
	 * currently active element id
	 * @var	string
	 */
	_activeElementID: '',
	
	/**
	 * cancels popover
	 * @var	boolean
	 */
	_cancelPopover: false,
	
	/**
	 * element data
	 * @var	object
	 */
	_data: { },
	
	/**
	 * default dimensions, should reflect the estimated size
	 * @var	object
	 */
	_defaultDimensions: {
		height: 150,
		width: 450
	},
	
	/**
	 * default orientation, may be a combintion of left/right and bottom/top
	 * @var	object
	 */
	_defaultOrientation: {
		x: 'right',
		y: 'top'
	},
	
	/**
	 * delay to show or hide popover, values in miliseconds
	 * @var	object
	 */
	_delay: {
		show: 800,
		hide: 500
	},
	
	/**
	 * true, if an element is being hovered
	 * @var	boolean
	 */
	_hoverElement: false,
	
	/**
	 * element id of element being hovered
	 * @var	string
	 */
	_hoverElementID: '',
	
	/**
	 * true, if popover is being hovered
	 * @var	boolean
	 */
	_hoverPopover: false,
	
	/**
	 * minimum margin (all directions) for popover
	 * @var	integer
	 */
	_margin: 20,
	
	/**
	 * periodical executer once element or popover is no longer being hovered
	 * @var	WCF.PeriodicalExecuter
	 */
	_peOut: null,
	
	/**
	 * periodical executer once an element is being hovered
	 * @var	WCF.PeriodicalExecuter
	 */
	_peOverElement: null,
	
	/**
	 * popover object
	 * @var	jQuery
	 */
	_popover: null,
	
	/**
	 * popover content
	 * @var	jQuery
	 */
	_popoverContent: null,
	
	/**
	 * popover horizontal offset
	 * @var	integer
	 */
	_popoverOffset: 10,
	
	/**
	 * element selector
	 * @var	string
	 */
	_selector: '',
	
	/**
	 * Initializes a new WCF.Popover object.
	 * 
	 * @param	string		selector
	 */
	init: function(selector) {
		if ($.browser.mobile) return;
		
		// assign default values
		this._activeElementID = '';
		this._cancelPopover = false;
		this._data = { };
		this._defaultDimensions = {
			height: 150,
			width: 450
		};
		this._defaultOrientation = {
			x: 'right',
			y: 'top'
		};
		this._delay = {
			show: 800,
			hide: 500
		};
		this._hoverElement = false;
		this._hoverElementID = '';
		this._hoverPopover = false;
		this._margin = 20;
		this._peOut = null;
		this._peOverElement = null;
		this._popoverOffset = 10;
		this._selector = selector;
		
		this._popover = $('<div class="popover"><span class="icon icon48 icon-spinner"></span><div class="popoverContent"></div></div>').hide().appendTo(document.body);
		this._popoverContent = this._popover.children('.popoverContent:eq(0)');
		this._popover.hover($.proxy(this._overPopover, this), $.proxy(this._out, this));
		
		this._initContainers();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Popover.'+selector, $.proxy(this._initContainers, this));
	},
	
	/**
	 * Initializes all element triggers.
	 */
	_initContainers: function() {
		if ($.browser.mobile) return;
		
		var $elements = $(this._selector);
		if (!$elements.length) {
			return;
		}
		
		$elements.each($.proxy(function(index, element) {
			var $element = $(element);
			var $elementID = $element.wcfIdentify();
			
			if (!this._data[$elementID]) {
				this._data[$elementID] = {
					'content': null,
					'isLoading': false
				};
				
				$element.hover($.proxy(this._overElement, this), $.proxy(this._out, this));
				
				if ($element.is('a') && $element.attr('href')) {
					$element.click($.proxy(this._cancel, this));
				}
			}
		}, this));
	},
	
	/**
	 * Cancels popovers if link is being clicked
	 */
	_cancel: function(event) {
		this._cancelPopover = true;
		this._hide(true);
	},
	
	/**
	 * Triggered once an element is being hovered.
	 * 
	 * @param	object		event
	 */
	_overElement: function(event) {
		if (this._cancelPopover) {
			return;
		}
		
		if (this._peOverElement !== null) {
			this._peOverElement.stop();
		}
		
		var $elementID = $(event.currentTarget).wcfIdentify();
		this._hoverElementID = $elementID;
		this._peOverElement = new WCF.PeriodicalExecuter($.proxy(function(pe) {
			pe.stop();
			
			// still above the same element
			if (this._hoverElementID === $elementID) {
				this._activeElementID = $elementID;
				this._prepare();
			}
		}, this), this._delay.show);
		
		this._hoverElement = true;
		this._hoverPopover = false;
	},
	
	/**
	 * Prepares popover to be displayed.
	 */
	_prepare: function() {
		if (this._cancelPopover) {
			return;
		}
		
		if (this._peOut !== null) {
			this._peOut.stop();
		}
		
		// hide and reset
		if (this._popover.is(':visible')) {
			this._hide(true);
		}
		
		// insert html
		if (!this._data[this._activeElementID].loading && this._data[this._activeElementID].content) {
			this._popoverContent.html(this._data[this._activeElementID].content);
			
			WCF.DOMNodeInsertedHandler.execute();
		}
		else {
			this._data[this._activeElementID].loading = true;
		}
		
		// get dimensions
		var $dimensions = this._popover.show().getDimensions();
		if (this._data[this._activeElementID].loading) {
			$dimensions = {
				height: Math.max($dimensions.height, this._defaultDimensions.height),
				width: Math.max($dimensions.width, this._defaultDimensions.width)
			};
		}
		else {
			$dimensions = this._fixElementDimensions(this._popover, $dimensions);
		}
		this._popover.hide();
		
		// get orientation
		var $orientation = this._getOrientation($dimensions.height, $dimensions.width);
		this._popover.css(this._getCSS($orientation.x, $orientation.y));
		
		// apply orientation to popover
		this._popover.removeClass('bottom left right top').addClass($orientation.x).addClass($orientation.y);
		
		this._show();
	},
	
	/**
	 * Displays the popover.
	 */
	_show: function() {
		if (this._cancelPopover) {
			return;
		}
		
		this._popover.stop().show().css({ opacity: 1 }).wcfFadeIn();
		
		if (this._data[this._activeElementID].loading) {
			this._popover.children('span').show();
			this._loadContent();
		}
		else {
			this._popover.children('span').hide();
			this._popoverContent.css({ opacity: 1 });
		}
	},
	
	/**
	 * Loads content, should be overwritten by child classes.
	 */
	_loadContent: function() { },
	
	/**
	 * Inserts content and animating transition.
	 * 
	 * @param	string		elementID
	 * @param	boolean		animate
	 */
	_insertContent: function(elementID, content, animate) {
		this._data[elementID] = {
			content: content,
			loading: false
		};
		
		// only update content if element id is active
		if (this._activeElementID === elementID) {
			if (animate) {
				// get current dimensions
				var $dimensions = this._popoverContent.getDimensions();
				
				// insert new content
				this._popoverContent.css({
					height: 'auto',
					width: 'auto'
				});
				this._popoverContent.html(this._data[elementID].content);
				var $newDimensions = this._popoverContent.getDimensions();
				
				// enforce current dimensions and remove HTML
				this._popoverContent.html('').css({
					height: $dimensions.height + 'px',
					width: $dimensions.width + 'px'
				});
				
				// animate to new dimensons
				var self = this;
				this._popoverContent.animate({
					height: $newDimensions.height + 'px',
					width: $newDimensions.width + 'px'
				}, 300, function() {
					self._popover.children('span').hide();
					self._popoverContent.html(self._data[elementID].content).css({ opacity: 0 }).animate({ opacity: 1 }, 200);
					
					WCF.DOMNodeInsertedHandler.execute();
				});
			}
			else {
				// insert new content
				this._popover.children('span').hide();
				this._popoverContent.html(this._data[elementID].content);
				
				WCF.DOMNodeInsertedHandler.execute();
			}
		}
	},
	
	/**
	 * Hides the popover.
	 */
	_hide: function(disableAnimation) {
		var self = this;
		this._popoverContent.stop();
		this._popover.stop();
		
		if (disableAnimation) {
			self._popover.css({ opacity: 0 }).hide();
			self._popoverContent.empty().css({ height: 'auto', opacity: 0, width: 'auto' });
		}
		else {
			this._popover.wcfFadeOut(function() {
				self._popoverContent.empty().css({ height: 'auto', opacity: 0, width: 'auto' });
				self._popover.hide();
			});
		}
	},
	
	/**
	 * Triggered once popover is being hovered.
	 */
	_overPopover: function() {
		if (this._peOut !== null) {
			this._peOut.stop();
		}
		
		this._hoverElement = false;
		this._hoverPopover = true;
	},
	
	/**
	 * Triggered once element *or* popover is now longer hovered.
	 */
	_out: function(event) {
		if (this._cancelPopover) {
			return;
		}
		
		this._hoverElementID = '';
		this._hoverElement = false;
		this._hoverPopover = false;
		
		this._peOut = new WCF.PeriodicalExecuter($.proxy(function(pe) {
			pe.stop();
			
			// hide popover is neither element nor popover was hovered given time
			if (!this._hoverElement && !this._hoverPopover) {
				this._hide(false);
			}
		}, this), this._delay.hide);
	},
	
	/**
	 * Resolves popover orientation, tries to use default orientation first.
	 * 
	 * @param	integer		height
	 * @param	integer		width
	 * @return	object
	 */
	_getOrientation: function(height, width) {
		// get offsets and dimensions
		var $element = $('#' + this._activeElementID);
		var $offsets = $element.getOffsets('offset');
		var $elementDimensions = $element.getDimensions();
		var $documentDimensions = $(document).getDimensions();
		
		// try default orientation first
		var $orientationX = (this._defaultOrientation.x === 'left') ? 'left' : 'right';
		var $orientationY = (this._defaultOrientation.y === 'bottom') ? 'bottom' : 'top';
		var $result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
		
		if ($result.flawed) {
			// try flipping orientationX
			$orientationX = ($orientationX === 'left') ? 'right' : 'left';
			$result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
			
			if ($result.flawed) {
				// try flipping orientationY while maintaing original orientationX
				$orientationX = ($orientationX === 'right') ? 'left' : 'right';
				$orientationY = ($orientationY === 'bottom') ? 'top' : 'bottom';
				$result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
				
				if ($result.flawed) {
					// try flipping both orientationX and orientationY compared to default values
					$orientationX = ($orientationX === 'left') ? 'right' : 'left';
					$result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
					
					if ($result.flawed) {
						// fuck this shit, we will use the default orientation
						$orientationX = (this._defaultOrientationX === 'left') ? 'left' : 'right';
						$orientationY = (this._defaultOrientationY === 'bottom') ? 'bottom' : 'top';
					}
				}
			}
		}
		
		return {
			x: $orientationX,
			y: $orientationY
		};
	},
	
	/**
	 * Evaluates if popover fits into given orientation.
	 * 
	 * @param	string		orientationX
	 * @param	string		orientationY
	 * @param	object		offsets
	 * @param	object		elementDimensions
	 * @param	object		documentDimensions
	 * @param	integer		height
	 * @param	integer		width
	 * @return	object
	 */
	_evaluateOrientation: function(orientationX, orientationY, offsets, elementDimensions, documentDimensions, height, width) {
		var $heightDifference = 0, $widthDifference = 0;
		switch (orientationX) {
			case 'left':
				$widthDifference = offsets.left - width;
			break;
			
			case 'right':
				$widthDifference = documentDimensions.width - (offsets.left + width);
			break;
		}
		
		switch (orientationY) {
			case 'bottom':
				$heightDifference = documentDimensions.height - (offsets.top + elementDimensions.height + this._popoverOffset + height);
			break;
			
			case 'top':
				$heightDifference = offsets.top - (height - this._popoverOffset);
			break;
		}
		
		// check if both difference are above margin
		var $flawed = false;
		if ($heightDifference < this._margin || $widthDifference < this._margin) {
			$flawed = true;
		}
		
		return {
			flawed: $flawed,
			x: $widthDifference,
			y: $heightDifference
		};
	},
	
	/**
	 * Computes CSS for popover.
	 * 
	 * @param	string		orientationX
	 * @param	string		orientationY
	 * @return	object
	 */
	_getCSS: function(orientationX, orientationY) {
		var $css = {
			bottom: 'auto',
			left: 'auto',
			right: 'auto',
			top: 'auto'
		};
		
		var $element = $('#' + this._activeElementID);
		var $offsets = $element.getOffsets('offset');
		var $elementDimensions = this._fixElementDimensions($element, $element.getDimensions());
		var $windowDimensions = $(window).getDimensions();
		
		switch (orientationX) {
			case 'left':
				$css.right = $windowDimensions.width - ($offsets.left + $elementDimensions.width);
			break;
			
			case 'right':
				$css.left = $offsets.left;
			break;
		}
		
		switch (orientationY) {
			case 'bottom':
				$css.top = $offsets.top + ($elementDimensions.height + this._popoverOffset);
			break;
			
			case 'top':
				$css.bottom = $windowDimensions.height - ($offsets.top - this._popoverOffset);
			break;
		}
		
		return $css;
	},
	
	/**
	 * Tries to fix dimensions if element is partially hidden (overflow: hidden).
	 * 
	 * @param	jQuery		element
	 * @param	object		dimensions
	 * @return	dimensions
	 */
	_fixElementDimensions: function(element, dimensions) {
		var $parentDimensions = element.parent().getDimensions();
		
		if ($parentDimensions.height < dimensions.height) {
			dimensions.height = $parentDimensions.height;
		}
		
		if ($parentDimensions.width < dimensions.width) {
			dimensions.width = $parentDimensions.width;
		}
		
		return dimensions;
	}
});

/**
 * Provides an extensible item list with built-in search.
 * 
 * @param	string		itemListSelector
 * @param	string		searchInputSelector
 */
WCF.EditableItemList = Class.extend({
	/**
	 * allows custom input not recognized by search to be added
	 * @var	boolean
	 */
	_allowCustomInput: false,
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * internal data storage
	 * @var	mixed
	 */
	_data: { },
	
	/**
	 * form container
	 * @var	jQuery
	 */
	_form: null,
	
	/**
	 * item list container
	 * @var	jQuery
	 */
	_itemList: null,
	
	/**
	 * current object id
	 * @var	integer
	 */
	_objectID: 0,
	
	/**
	 * object type id
	 * @var	integer
	 */
	_objectTypeID: 0,
	
	/**
	 * search controller
	 * @var	WCF.Search.Base
	 */
	_search: null,
	
	/**
	 * search input element
	 * @var	jQuery
	 */
	_searchInput: null,
	
	/**
	 * Creates a new WCF.EditableItemList object.
	 * 
	 * @param	string		itemListSelector
	 * @param	string		searchInputSelector
	 */
	init: function(itemListSelector, searchInputSelector) {
		this._itemList = $(itemListSelector);
		this._searchInput = $(searchInputSelector);
		this._data = { };
		
		if (!this._itemList.length || !this._searchInput.length) {
			console.debug("[WCF.EditableItemList] Item list and/or search input do not exist, aborting.");
			return;
		}
		
		this._objectID = this._getObjectID();
		this._objectTypeID = this._getObjectTypeID();
		
		// bind item listener
		this._itemList.find('.jsEditableItem').click($.proxy(this._click, this));
		
		// create item list
		if (!this._itemList.children('ul').length) {
			$('<ul />').appendTo(this._itemList);
		}
		this._itemList = this._itemList.children('ul');
		
		// bind form submit
		this._form = this._itemList.parents('form').submit($.proxy(this._submit, this));
		
		if (this._allowCustomInput) {
			var self = this;
			this._searchInput.keydown($.proxy(this._keyDown, this)).on('paste', function() {
				setTimeout(function() { self._onPaste(); }, 100);
			});
		}
		
		// block form submit through [ENTER]
		this._searchInput.parents('.dropdown').data('preventSubmit', true);
	},
	
	/**
	 * Handles the key down event.
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		// 188 = [,]
		if (event === null || event.which === 188 || event.which === $.ui.keyCode.ENTER) {
			if (event !== null && event.which === $.ui.keyCode.ENTER && this._search) {
				if (this._search._itemIndex !== -1) {
					return false;
				}
			}
			
			var $value = $.trim(this._searchInput.val());
			
			// read everything left from caret position
			if (event && event.which === 188) {
				$value = $value.substring(0, this._searchInput.getCaret());
			}
			
			if ($value === '') {
				return true;
			}
			
			this.addItem({
				objectID: 0,
				label: $value
			});
			
			// reset input
			if (event && event.which === 188) {
				this._searchInput.val($.trim(this._searchInput.val().substr(this._searchInput.getCaret())));
			}
			else {
				this._searchInput.val('');
			}
			
			if (event !== null) {
				event.stopPropagation();
			}
			
			return false;
		}
		
		return true;
	},
	
	/**
	 * Handle paste event.
	 */
	_onPaste: function() {
		// split content by comma
		var $value = $.trim(this._searchInput.val());
		$value = $value.split(',');
		
		for (var $i = 0, $length = $value.length; $i < $length; $i++) {
			var $label = $.trim($value[$i]);
			if ($label === '') {
				continue;
			}
			
			this.addItem({
				objectID: 0,
				label: $label
			});
		}
		
		this._searchInput.val('');
	},
	
	/**
	 * Loads raw data and converts it into internal structure. Override this methods
	 * in your derived classes.
	 * 
	 * @param	object		data
	 */
	load: function(data) { },
	
	/**
	 * Removes an item on click.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_click: function(event) {
		var $element = $(event.currentTarget);
		var $objectID = $element.data('objectID');
		var $label = $element.data('label');
		
		if (this._search) {
			this._search.removeExcludedSearchValue($label);
		}
		this._removeItem($objectID, $label);
		
		$element.remove();
		
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Returns current object id.
	 * 
	 * @return	integer
	 */
	_getObjectID: function() {
		return 0;
	},
	
	/**
	 * Returns current object type id.
	 * 
	 * @return	integer
	 */
	_getObjectTypeID: function() {
		return 0;
	},
	
	/**
	 * Adds a new item to the list.
	 * 
	 * @param	object		data
	 * @return	boolean
	 */
	addItem: function(data) {
		if (this._data[data.objectID]) {
			if (!(data.objectID === 0 && this._allowCustomInput)) {
				return true;
			}
		}
		
		var $listItem = $('<li class="badge">' + WCF.String.escapeHTML(data.label) + '</li>').data('objectID', data.objectID).data('label', data.label).appendTo(this._itemList);
		$listItem.click($.proxy(this._click, this));
		
		if (this._search) {
			this._search.addExcludedSearchValue(data.label);
		}
		this._addItem(data.objectID, data.label);
		
		return true;
	},
	
	/**
	 * Clears the list of items.
	 */
	clearList: function() {
		this._itemList.children('li').each($.proxy(function(index, element) {
			var $element = $(element);
			
			if (this._search) {
				this._search.removeExcludedSearchValue($element.data('label'));
			}
			
			$element.remove();
			this._removeItem($element.data('objectID'), $element.data('label'));
		}, this));
	},
	
	/**
	 * Handles form submit, override in your class.
	 */
	_submit: function() {
		this._keyDown(null);
	},
	
	/**
	 * Adds an item to internal storage.
	 * 
	 * @param	integer		objectID
	 * @param	string		label
	 */
	_addItem: function(objectID, label) {
		this._data[objectID] = label;
	},
	
	/**
	 * Removes an item from internal storage.
	 * 
	 * @param	integer		objectID
	 * @param	string		label
	 */
	_removeItem: function(objectID, label) {
		delete this._data[objectID];
	},
	
	/**
	 * Returns the search input field.
	 * 
	 * @return	jQuery
	 */
	getSearchInput: function() {
		return this._searchInput;
	}
});

/**
 * Provides a generic sitemap.
 */
WCF.Sitemap = Class.extend({
	/**
	 * sitemap name cache
	 * @var	array
	 */
	_cache: [ ],
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the generic sitemap.
	 */
	init: function() {
		$('#sitemap').click($.proxy(this._click, this));
		
		this._cache = [ ];
		this._dialog = null;
		this._didInit = false;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Handles clicks on the sitemap icon.
	 */
	_click: function() {
		if (this._dialog === null) {
			this._dialog = $('<div id="sitemapDialog" />').appendTo(document.body);
			
			this._proxy.setOption('data', {
				actionName: 'getSitemap',
				className: 'wcf\\data\\sitemap\\SitemapAction'
			});
			this._proxy.sendRequest();
		}
		else {
			this._dialog.wcfDialog('open');
		}
	},
	
	/**
	 * Handles successful AJAX responses.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (this._didInit) {
			this._cache.push(data.returnValues.sitemapName);
			
			this._dialog.find('#sitemap_' + data.returnValues.sitemapName).html(data.returnValues.template);
			
			// redraw dialog
			this._dialog.wcfDialog('render');
		}
		else {
			// mark sitemap name as loaded
			this._cache.push(data.returnValues.sitemapName);
			
			// insert sitemap template
			this._dialog.html(data.returnValues.template);
			
			// bind event listener
			this._dialog.find('.sitemapNavigation').click($.proxy(this._navigate, this));
			
			// select active item
			this._dialog.find('.tabMenuContainer').wcfTabs('select', 'sitemap_' + data.returnValues.sitemapName);
			
			// show dialog
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.page.sitemap')
			});
			
			this._didInit = true;
		}
	},
	
	/**
	 * Navigates between different sitemaps.
	 * 
	 * @param	object		event
	 */
	_navigate: function(event) {
		var $sitemapName = $(event.currentTarget).data('sitemapName');
		if (WCF.inArray($sitemapName, this._cache)) {
			this._dialog.find('.tabMenuContainer').wcfTabs('select', 'sitemap_' + $sitemapName);
			
			// redraw dialog
			this._dialog.wcfDialog('render');
		}
		else {
			this._proxy.setOption('data', {
				actionName: 'getSitemap',
				className: 'wcf\\data\\sitemap\\SitemapAction',
				parameters: {
					sitemapName: $sitemapName
				}
			});
			this._proxy.sendRequest();
		}
	}
});

/**
 * Provides a language chooser.
 * 
 * @param	string		containerID
 * @param	string		inputFieldID
 * @param	integer		languageID
 * @param	object		languages
 * @param	object		callback
 */
WCF.Language.Chooser = Class.extend({
	/**
	 * callback object
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * dropdown object
	 * @var	jQuery
	 */
	_dropdown: null,
	
	/**
	 * input field
	 * @var	jQuery
	 */
	_input: null,
	
	/**
	 * Initializes the language chooser.
	 * 
	 * @param	string		containerID
	 * @param	string		inputFieldID
	 * @param	integer		languageID
	 * @param	object		languages
	 * @param	object		callback
	 * @param	boolean		allowEmptyValue
	 */
	init: function(containerID, inputFieldID, languageID, languages, callback, allowEmptyValue) {
		var $container = $('#' + containerID);
		if ($container.length != 1) {
			console.debug("[WCF.Language.Chooser] Invalid container id '" + containerID + "' given");
			return;
		}
		
		// bind language id input
		this._input = $('#' + inputFieldID);
		if (!this._input.length) {
			this._input = $('<input type="hidden" name="' + inputFieldID + '" value="' + languageID + '" />').appendTo($container);
		}
		
		// handle callback
		if (callback !== undefined) {
			if (!$.isFunction(callback)) {
				console.debug("[WCF.Language.Chooser] Given callback is invalid");
				return;
			}
			
			this._callback = callback;
		}
		
		// create language dropdown
		this._dropdown = $('<div class="dropdown" id="' + containerID + '-languageChooser" />').appendTo($container);
		$('<div class="dropdownToggle boxFlag box24" data-toggle="' + containerID + '-languageChooser"></div>').appendTo(this._dropdown);
		var $dropdownMenu = $('<ul class="dropdownMenu" />').appendTo(this._dropdown);
		
		for (var $languageID in languages) {
			var $language = languages[$languageID];
			var $item = $('<li class="boxFlag"><a class="box24"><div class="framed"><img src="' + $language.iconPath + '" alt="" class="iconFlag" /></div> <div><h3>' + $language.languageName + '</h3></div></a></li>').appendTo($dropdownMenu);
			$item.data('languageID', $languageID).click($.proxy(this._click, this));
			
			// update dropdown label
			if ($languageID == languageID) {
				var $html = $('' + $item.html());
				var $innerContent = $html.children().detach();
				this._dropdown.children('.dropdownToggle').empty().append($innerContent);
			}
		}
		
		// allow an empty selection (e.g. using as language filter)
		if (allowEmptyValue) {
			$('<li class="dropdownDivider" />').appendTo($dropdownMenu);
			var $item = $('<li><a>' + WCF.Language.get('wcf.global.language.noSelection') + '</a></li>').data('languageID', 0).click($.proxy(this._click, this)).appendTo($dropdownMenu);
			
			if (languageID === 0) {
				this._dropdown.children('.dropdownToggle').empty().append($item.html());
			}
		}
		
		WCF.Dropdown.init();
	},
	
	/**
	 * Handles click events.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $item = $(event.currentTarget);
		var $languageID = $item.data('languageID');
		
		// update input field
		this._input.val($languageID);
		
		// update dropdown label
		var $html = $('' + $item.html());
		var $innerContent = ($languageID === 0) ? $html : $html.children().detach();
		this._dropdown.children('.dropdownToggle').empty().append($innerContent);
		
		// execute callback
		if (this._callback !== null) {
			this._callback($item);
		}
	}
});

/**
 * Namespace for style related classes.
 */
WCF.Style = { };

/**
 * Provides a visual style chooser.
 */
WCF.Style.Chooser = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the style chooser class.
	 */
	init: function() {
		$('<li class="styleChooser"><a>' + WCF.Language.get('wcf.style.changeStyle') + '</a></li>').appendTo($('#footerNavigation > ul.navigationItems')).click($.proxy(this._showDialog, this));
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Displays the style chooser dialog.
	 */
	_showDialog: function() {
		if (this._dialog === null) {
			this._dialog = $('<div id="styleChooser" />').hide().appendTo(document.body);
			this._loadDialog();
		}
		else {
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.style.changeStyle')
			});
		}
	},
	
	/**
	 * Loads the style chooser dialog.
	 */
	_loadDialog: function() {
		this._proxy.setOption('data', {
			actionName: 'getStyleChooser',
			className: 'wcf\\data\\style\\StyleAction'
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.actionName === 'changeStyle') {
			window.location.reload();
			return;
		}
		
		this._dialog.html(data.returnValues.template);
		this._dialog.find('li').addClass('pointer').click($.proxy(this._click, this));
		
		this._showDialog();
	},
	
	/**
	 * Changes user style.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._proxy.setOption('data', {
			actionName: 'changeStyle',
			className: 'wcf\\data\\style\\StyleAction',
			objectIDs: [ $(event.currentTarget).data('styleID') ]
		});
		this._proxy.sendRequest();
	}
});

/**
 * Converts static user panel items into interactive dropdowns.
 * 
 * @param	string		containerID
 */
WCF.UserPanel = Class.extend({
	/**
	 * target container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didLoad: false,
	
	/**
	 * original link element
	 * @var	jQuery
	 */
	_link: null,
	
	/**
	 * language variable name for 'no items'
	 * @var	string
	 */
	_noItems: '',
	
	/**
	 * reverts to original link if return values are empty
	 * @var	boolean
	 */
	_revertOnEmpty: true,
	
	/**
	 * Initialites the WCF.UserPanel class.
	 * 
	 * @param	string		containerID
	 */
	init: function(containerID) {
		this._container = $('#' + containerID);
		this._didLoad = false;
		this._revertOnEmpty = true;
		
		if (this._container.length != 1) {
			console.debug("[WCF.UserPanel] Unable to find container identfied by '" + containerID + "', aborting.");
			return;
		}
		
		this._convert();
	},
	
	/**
	 * Converts link into an interactive dropdown menu.
	 */
	_convert: function() {
		this._container.addClass('dropdown');
		this._link = this._container.children('a').remove();
		
		var $button = $('<a class="dropdownToggle">' + this._link.html() + '</a>').appendTo(this._container).click($.proxy(this._click, this));
		var $dropdownMenu = $('<ul class="dropdownMenu" />').appendTo(this._container);
		$('<li class="jsDropdownPlaceholder"><span>' + WCF.Language.get('wcf.global.loading') + '</span></li>').appendTo($dropdownMenu);
		
		this._addDefaultItems($dropdownMenu);
		
		this._container.dblclick($.proxy(function() {
			window.location = this._link.attr('href');
			return false;
		}, this));
		
		WCF.Dropdown.initDropdown($button, false);
	},
	
	/**
	 * Adds default items to dropdown menu.
	 * 
	 * @param	jQuery		dropdownMenu
	 */
	_addDefaultItems: function(dropdownMenu) { },
	
	/**
	 * Adds a dropdown divider.
	 * 
	 * @param	jQuery		dropdownMenu
	 */
	_addDivider: function(dropdownMenu) {
		$('<li class="dropdownDivider" />').appendTo(dropdownMenu);
	},
	
	/**
	 * Handles clicks on the dropdown item.
	 */
	_click: function() {
		if (this._didLoad) {
			return;
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: this._getParameters(),
			success: $.proxy(this._success, this)
		});
		
		this._didLoad = true;
	},
	
	/**
	 * Returns a list of parameters for AJAX request.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return { };
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $dropdownMenu = WCF.Dropdown.getDropdownMenu(this._container.wcfIdentify());
		$dropdownMenu.children('.jsDropdownPlaceholder').remove();
		
		if (data.returnValues && data.returnValues.template) {
			$('' + data.returnValues.template).prependTo($dropdownMenu);
			
			// update badge
			var $badge = this._container.find('.badge');
			if (!$badge.length) {
				$badge = $('<span class="badge badgeInverse" />').appendTo(this._container.children('.dropdownToggle'));
				$badge.before(' ');
			}
			$badge.html(data.returnValues.totalCount);
			
			this._after($dropdownMenu);
		}
		else {
			$('<li><span>' + WCF.Language.get(this._noItems) + '</span></li>').prependTo($dropdownMenu);
			
			// remove badge
			this._container.find('.badge').remove();
		}
	},
	
	/**
	 * Execute actions after the dropdown menu has been populated.
	 * 
	 * @param	object		dropdownMenu
	 */
	_after: function(dropdownMenu) { }
});

/**
 * WCF implementation for dialogs, based upon ideas by jQuery UI.
 */
$.widget('ui.wcfDialog', {
	/**
	 * close button
	 * @var	jQuery
	 */
	_closeButton: null,
	
	/**
	 * dialog container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * dialog content
	 * @var	jQuery
	 */
	_content: null,
	
	/**
	 * modal overlay
	 * @var	jQuery
	 */
	_overlay: null,
	
	/**
	 * plain html for title
	 * @var	string
	 */
	_title: null,
	
	/**
	 * title bar
	 * @var	jQuery
	 */
	_titlebar: null,
	
	/**
	 * dialog visibility state
	 * @var	boolean
	 */
	_isOpen: false,
	
	/**
	 * option list
	 * @var	object
	 */
	options: {
		// dialog
		autoOpen: true,
		closable: true,
		closeButtonLabel: null,
		closeConfirmMessage: null,
		closeViaModal: true,
		hideTitle: false,
		modal: true,
		title: '',
		zIndex: 400,
		
		// event callbacks
		onClose: null,
		onShow: null
	},
	
	/**
	 * @see	$.widget._createWidget()
	 */
	_createWidget: function(options, element) {
		// ignore script tags
		if ($(element).getTagName() === 'script') {
			console.debug("[ui.wcfDialog] Ignored script tag");
			this.element = false;
			return null;
		}
		
		$.Widget.prototype._createWidget.apply(this, arguments);
	},
	
	/**
	 * Initializes a new dialog.
	 */
	_init: function() {
		if (this.options.autoOpen) {
			this.open();
		}
		
		// act on resize
		$(window).resize($.proxy(this._resize, this));
	},
	
	/**
	 * Creates a new dialog instance.
	 */
	_create: function() {
		if (this.options.closeButtonLabel === null) {
			this.options.closeButtonLabel = WCF.Language.get('wcf.global.button.close');
		}
		
		// create dialog container
		this._container = $('<div class="dialogContainer" />').hide().css({ zIndex: this.options.zIndex }).appendTo(document.body);
		this._titlebar = $('<header class="dialogTitlebar" />').hide().appendTo(this._container);
		this._title = $('<span class="dialogTitle" />').hide().appendTo(this._titlebar);
		this._closeButton = $('<a class="dialogCloseButton jsTooltip" title="' + this.options.closeButtonLabel + '"><span /></a>').click($.proxy(this.close, this)).hide().appendTo(this._titlebar);
		this._content = $('<div class="dialogContent" />').appendTo(this._container);
		
		this._setOption('title', this.options.title);
		this._setOption('closable', this.options.closable);
		
		// move target element into content
		var $content = this.element.detach();
		this._content.html($content);
		
		// create modal view
		if (this.options.modal) {
			this._overlay = $('#jsWcfDialogOverlay');
			if (!this._overlay.length) {
				this._overlay = $('<div id="jsWcfDialogOverlay" class="dialogOverlay" />').css({ height: '100%', zIndex: 399 }).hide().appendTo(document.body);
			}
			
			if (this.options.closable && this.options.closeViaModal) {
				this._overlay.click($.proxy(this.close, this));
				
				$(document).keyup($.proxy(function(event) {
					if (event.keyCode && event.keyCode === $.ui.keyCode.ESCAPE) {
						this.close();
						event.preventDefault();
					}
				}, this));
			}
		}
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Sets the given option to the given value.
	 * See the jQuery UI widget documentation for more.
	 */
	_setOption: function(key, value) {
		this.options[key] = value;
		
		if (key == 'hideTitle' || key == 'title') {
			if (!this.options.hideTitle && this.options.title != '') {
				this._title.html(this.options.title).show();
			} else {
				this._title.html('');
			}
		} else if (key == 'closable' || key == 'closeButtonLabel') {
			if (this.options.closable) {
				this._closeButton.attr('title', this.options.closeButtonLabel).show().find('span').html(this.options.closeButtonLabel);
				
				WCF.DOMNodeInsertedHandler.execute();
			} else {
				this._closeButton.hide();
			}
		}
		
		if ((!this.options.hideTitle && this.options.title != '') || this.options.closable) {
			this._titlebar.show();
		} else {
			this._titlebar.hide();
		}
		
		return this;
	},
	
	/**
	 * Opens this dialog.
	 */
	open: function() {
		// ignore script tags
		if (this.element === false) {
			return;
		}
		
		if (this.isOpen()) {
			return;
		}
		
		if (this._overlay !== null) {
			WCF.activeDialogs++;
			
			if (WCF.activeDialogs === 1) {
				this._overlay.show();
			}
		}
		
		this.render();
		this._isOpen = true;
	},
	
	/**
	 * Returns true if dialog is visible.
	 * 
	 * @return	boolean
	 */
	isOpen: function() {
		return this._isOpen;
	},
	
	/**
	 * Closes this dialog.
	 * 
	 * This function can be manually called, even if the dialog is set as not
	 * closable by the user.
	 * 
	 * @param	object		event
	 */
	close: function(event) {
		if (!this.isOpen()) {
			return;
		}
		
		if (this.options.closeConfirmMessage) {
			WCF.System.Confirmation.show(this.options.closeConfirmMessage, $.proxy(function(action) {
				if (action === 'confirm') {
					this._close();
				}
			}, this));
		}
		else {
			this._close();
		}
		
		if (event !== undefined) {
			event.preventDefault();
		}
	},
	
	/**
	 * Handles dialog closing, should never be called directly.
	 * 
	 * @see	$.ui.wcfDialog.close()
	 */
	_close: function() {
		this._isOpen = false;
		this._container.wcfFadeOut();
		
		if (this._overlay !== null) {
			WCF.activeDialogs--;
			
			if (WCF.activeDialogs === 0) {
				this._overlay.hide();
			}
		}
		
		if (this.options.onClose !== null) {
			this.options.onClose();
		}
	},
	
	/**
	 * Renders dialog on resize if visible.
	 */
	_resize: function() {
		if (this.isOpen()) {
			this.render();
		}
	},
	
	/**
	 * Renders this dialog, should be called whenever content is updated.
	 */
	render: function() {
		// check if this if dialog was previously hidden and container is fixed
		// at 0px (mobile optimization), in this case scroll to top
		if (!this._container.is(':visible') && this._container.css('top') === '0px') {
			window.scrollTo(0, 0);
		}
		
		// force dialog and it's contents to be visible
		this._container.show();
		this._content.children().show();
		
		// remove fixed content dimensions for calculation
		this._content.css({
			height: 'auto',
			width: 'auto'
		});
		
		// terminate concurrent rendering processes
		this._container.stop();
		this._content.stop();
		
		// set dialog to be fully opaque, prevents weird bugs in WebKit
		this._container.show().css('opacity', 1.0);
		
		// handle positioning of form submit controls
		var $heightDifference = 0;
		if (this._content.find('.formSubmit').length) {
			$heightDifference = this._content.find('.formSubmit').outerHeight();
			
			this._content.addClass('dialogForm').css({ marginBottom: $heightDifference + 'px' });
		}
		else {
			this._content.removeClass('dialogForm').css({ marginBottom: '0px' });
		}
		
		// force 800px or 90% width
		var $windowDimensions = $(window).getDimensions();
		if ($windowDimensions.width * 0.9 > 800) {
			this._container.css('maxWidth', '800px');
		}
		
		// calculate dimensions
		var $containerDimensions = this._container.getDimensions('outer');
		var $contentDimensions = this._content.getDimensions();
		
		// calculate maximum content height
		var $heightDifference = $containerDimensions.height - $contentDimensions.height;
		var $maximumHeight = $windowDimensions.height - $heightDifference - 120;
		this._content.css({ maxHeight: $maximumHeight + 'px' });
		
		this._determineOverflow();
		
		// calculate new dimensions
		$containerDimensions = this._container.getDimensions('outer');
		
		// move container
		var $leftOffset = Math.round(($windowDimensions.width - $containerDimensions.width) / 2);
		var $topOffset = Math.round(($windowDimensions.height - $containerDimensions.height) / 2);
		
		// place container at 20% height if possible
		var $desiredTopOffset = Math.round(($windowDimensions.height / 100) * 20);
		if ($desiredTopOffset < $topOffset) {
			$topOffset = $desiredTopOffset;
		}
		
		// apply offset
		this._container.css({
			left: $leftOffset + 'px',
			top: $topOffset + 'px'
		});
		
		// remove static dimensions
		this._content.css({
			height: 'auto',
			width: 'auto'
		});
		
		if (!this.isOpen()) {
			// hide container again
			this._container.hide();
			
			// fade in container
			this._container.wcfFadeIn($.proxy(function() {
				if (this.options.onShow !== null) {
					this.options.onShow();
				}
			}, this));
		}
	},
	
	/**
	 * Determines content overflow based upon static dimensions.
	 */
	_determineOverflow: function() {
		var $max = $(window).getDimensions();
		var $maxHeight = this._content.css('maxHeight');
		this._content.css('maxHeight', 'none');
		var $dialog = this._container.getDimensions('outer');
		
		var $overflow = 'visible';
		if (($max.height * 0.8 < $dialog.height) || ($max.width * 0.8 < $dialog.width)) {
			$overflow = 'auto';
		}
		
		this._content.css('overflow', $overflow);
		this._content.css('maxHeight', $maxHeight);
		
		if ($overflow === 'visible') {
			// content may already overflow, even though the overall height is still below the threshold
			var $contentHeight = 0;
			this._content.children().each(function(index, child) {
				$contentHeight += $(child).outerHeight();
			});
			
			if (this._content.height() < $contentHeight) {
				this._content.css('overflow', 'auto');
			}
		}
	},
	
	/**
	 * Returns calculated content dimensions.
	 * 
	 * @param	integer		maximumHeight
	 * @return	object
	 */
	_getContentDimensions: function(maximumHeight) {
		var $contentDimensions = this._content.getDimensions();
		
		// set height to maximum height if exceeded
		if (maximumHeight && $contentDimensions.height > maximumHeight) {
			$contentDimensions.height = maximumHeight;
		}
		
		return $contentDimensions;
	}
});

/**
 * Provides a slideshow for lists.
 */
$.widget('ui.wcfSlideshow', {
	/**
	 * button list object
	 * @var	jQuery
	 */
	_buttonList: null,
	
	/**
	 * number of items
	 * @var	integer
	 */
	_count: 0,
	
	/**
	 * item index
	 * @var	integer
	 */
	_index: 0,
	
	/**
	 * item list object
	 * @var	jQuery
	 */
	_itemList: null,
	
	/**
	 * list of items
	 * @var	jQuery
	 */
	_items: null,
	
	/**
	 * timer object
	 * @var	WCF.PeriodicalExecuter
	 */
	_timer: null,
	
	/**
	 * list item width
	 * @var	integer
	 */
	_width: 0,
	
	/**
	 * list of options
	 * @var	object
	 */
	options: {
		/* enables automatic cycling of items */
		cycle: true,
		/* cycle interval in seconds */
		cycleInterval: 5,
		/* gap between items in pixels */
		itemGap: 50,
	},
	
	/**
	 * Creates a new instance of ui.wcfSlideshow.
	 */
	_create: function() {
		this._itemList = this.element.children('ul');
		this._items = this._itemList.children('li');
		this._count = this._items.length;
		this._index = 0;
		
		if (this._count > 1) {
			this._initSlideshow();
		}
	},
	
	/**
	 * Initializes the slideshow.
	 */
	_initSlideshow: function() {
		// calculate item dimensions
		var $itemHeight = $(this._items.get(0)).outerHeight();
		this._items.addClass('slideshowItem');
		this._width = this.element.css('height', $itemHeight).innerWidth();
		this._itemList.addClass('slideshowItemList').css('left', 0);
		
		this._items.each($.proxy(function(index, item) {
			$(item).show().css({
				height: $itemHeight,
				left: ((this._width + this.options.itemGap) * index),
				width: this._width
			});
		}, this));
		
		this.element.css({
			height: $itemHeight,
			width: this._width
		}).hover($.proxy(this._hoverIn, this), $.proxy(this._hoverOut, this));
		
		// create toggle buttons
		this._buttonList = $('<ul class="slideshowButtonList" />').appendTo(this.element);
		for (var $i = 0; $i < this._count; $i++) {
			var $link = $('<li><a><span class="icon icon16 icon-circle" /></a></li>').data('index', $i).click($.proxy(this._click, this)).appendTo(this._buttonList);
			if ($i == 0) {
				$link.find('.icon').addClass('active');
			}
		}
		
		this._resetTimer();
		
		$(window).resize($.proxy(this._resize, this));
	},
	
	/**
	 * Handles browser resizing
	 */
	_resize: function() {
		this._width = this.element.css('width', 'auto').innerWidth();
		this._items.each($.proxy(function(index, item) {
			$(item).css({
				left: ((this._width + this.options.itemGap) * index),
				width: this._width
			});
		}, this));
		
		this._index--;
		this.moveTo(null);
	},
	
	/**
	 * Disables cycling while hovering.
	 */
	_hoverIn: function() {
		if (this._timer !== null) {
			this._timer.stop();
		}
	},
	
	/**
	 * Enables cycling after mouse out.
	 */
	_hoverOut: function() {
		this._resetTimer();
	},
	
	/**
	 * Resets cycle timer.
	 */
	_resetTimer: function() {
		if (!this.options.cycle) {
			return;
		}
		
		if (this._timer !== null) {
			this._timer.stop();
		}
		
		var self = this;
		this._timer = new WCF.PeriodicalExecuter(function() {
			self.moveTo(null);
		}, this.options.cycleInterval * 1000);
	},
	
	/**
	 * Handles clicks on the select buttons.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this.moveTo($(event.currentTarget).data('index'));
		
		this._resetTimer();
	},
	
	/**
	 * Moves to a specified item index, NULL will move to the next item in list.
	 * 
	 * @param	integer		index
	 */
	moveTo: function(index) {
		this._index = (index === null) ? this._index + 1 : index;
		if (this._index == this._count) {
			this._index = 0;
		}
		
		$(this._buttonList.find('.icon').removeClass('active').get(this._index)).addClass('active');
		this._itemList.css('left', this._index * (this._width + this.options.itemGap) * -1);
		
		this._trigger('moveTo', null, { index: this._index });
	},
	
	/**
	 * Returns item by index or null if index is invalid.
	 * 
	 * @return	jQuery
	 */
	getItem: function(index) {
		if (this._items[index]) {
			return this._items[index];
		}
		
		return null;
	}
});

/**
 * Custom tab menu implementation for WCF.
 */
$.widget('ui.wcfTabs', $.ui.tabs, {
	/**
	 * Workaround for ids containing a dot ".", until jQuery UI devs learn
	 * to properly escape ids ... (it took 18 months until they finally
	 * fixed it!)
	 * 
	 * @see	http://bugs.jqueryui.com/ticket/4681
	 * @see	$.ui.tabs.prototype._sanitizeSelector()
	 */
	_sanitizeSelector: function(hash) {
		return hash.replace(/([:\.])/g, '\\$1');
	},
	
	/**
	 * @see	$.ui.tabs.prototype.select()
	 */
	select: function(index) {
		if (!$.isNumeric(index)) {
			// panel identifier given
			this.panels.each(function(i, panel) {
				if ($(panel).wcfIdentify() === index) {
					index = i;
					return false;
				}
			});
			
			// unable to identify panel
			if (!$.isNumeric(index)) {
				console.debug("[ui.wcfTabs] Unable to find panel identified by '" + index + "', aborting.");
				return;
			}
		}
		
		this._setOption('active', index);
	},
	
	/**
	 * Selects a specific tab by triggering the 'click' event.
	 * 
	 * @param	string		tabIdentifier
	 */
	selectTab: function(tabIdentifier) {
		tabIdentifier = '#' + tabIdentifier;
		
		this.anchors.each(function(index, anchor) {
			var $anchor = $(anchor);
			if ($anchor.prop('hash') === tabIdentifier) {
				$anchor.trigger('click');
				return false;
			}
		});
	},
	
	/**
	 * Returns the currently selected tab index.
	 * 
	 * @return	integer
	 */
	getCurrentIndex: function() {
		return this.lis.index(this.lis.filter('.ui-tabs-selected'));
	},
	
	/**
	 * Returns true if identifier is used by an anchor.
	 * 
	 * @param	string		identifier
	 * @param	boolean		isChildren
	 * @return	boolean
	 */
	hasAnchor: function(identifier, isChildren) {
		var $matches = false;
		
		this.anchors.each(function(index, anchor) {
			var $href = $(anchor).attr('href');
			if (/#.+/.test($href)) {
				// split by anchor
				var $parts = $href.split('#', 2);
				if (isChildren) {
					$parts = $parts[1].split('-', 2);
				}
				
				if ($parts[1] === identifier) {
					$matches = true;
					
					// terminate loop
					return false;
				}
			}
		});
		
		return $matches;
	},
	
	/**
	 * Shows default tab.
	 */
	revertToDefault: function() {
		var $active = this.element.data('active');
		if (!$active || $active === '') $active = 0;
		
		this.select($active);
	},
	
	/**
	 * @see	$.ui.tabs.prototype._processTabs()
	 */
	_processTabs: function() {
		var that = this;
		
		this.tablist = this._getList()
			.addClass( "ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" )
			.attr( "role", "tablist" );
		
		this.tabs = this.tablist.find( "> li:has(a[href])" )
			.addClass( "ui-state-default ui-corner-top" )
			.attr({
				role: "tab",
				tabIndex: -1
			});
		
		this.anchors = this.tabs.map(function() {
				return $( "a", this )[ 0 ];
			})
			.addClass( "ui-tabs-anchor" )
			.attr({
				role: "presentation",
				tabIndex: -1
			});
		
		this.panels = $();
		
		this.anchors.each(function( i, anchor ) {
			var selector, panel,
				anchorId = $( anchor ).uniqueId().attr( "id" ),
				tab = $( anchor ).closest( "li" ),
				originalAriaControls = tab.attr( "aria-controls" );
			
			// inline tab
			selector = anchor.hash;
			panel = that.element.find( that._sanitizeSelector( selector ) );
			
			if ( panel.length) {
				that.panels = that.panels.add( panel );
			}
			if ( originalAriaControls ) {
				tab.data( "ui-tabs-aria-controls", originalAriaControls );
			}
			tab.attr({
				"aria-controls": selector.substring( 1 ),
				"aria-labelledby": anchorId
			});
			panel.attr( "aria-labelledby", anchorId );
		});
		
		this.panels
			.addClass( "ui-tabs-panel ui-widget-content ui-corner-bottom" )
			.attr( "role", "tabpanel" );
	},
	
	/**
	 * @see	$.ui.tabs.prototype.load()
	 */
	load: function( index, event ) {
		return;
	}
});

/**
 * jQuery widget implementation of the wcf pagination.
 */
$.widget('ui.wcfPages', {
	SHOW_LINKS: 11,
	SHOW_SUB_LINKS: 20,
	
	options: {
		// vars
		activePage: 1,
		maxPage: 1,
		
		// language
		// we use options here instead of language variables, because the paginator is not only usable with pages
		nextPage: null,
		previousPage: null
	},
	
	/**
	 * Creates the pages widget.
	 */
	_create: function() {
		if (this.options.nextPage === null) this.options.nextPage = WCF.Language.get('wcf.global.page.next');
		if (this.options.previousPage === null) this.options.previousPage = WCF.Language.get('wcf.global.page.previous');
		
		this.element.addClass('pageNavigation');
		
		this._render();
	},
	
	/**
	 * Destroys the pages widget.
	 */
	destroy: function() {
		$.Widget.prototype.destroy.apply(this, arguments);
		
		this.element.children().remove();
	},
	
	/**
	 * Renders the pages widget.
	 */
	_render: function() {
		// only render if we have more than 1 page
		if (!this.options.disabled && this.options.maxPage > 1) {
			var $hasHiddenPages = false;
			
			// make sure pagination is visible
			if (this.element.hasClass('hidden')) {
				this.element.removeClass('hidden');
			}
			this.element.show();
			
			this.element.children().remove();
			
			var $pageList = $('<ul />');
			this.element.append($pageList);
			
			var $previousElement = $('<li class="button skip" />');
			$pageList.append($previousElement);
			
			if (this.options.activePage > 1) {
				var $previousLink = $('<a' + ((this.options.previousPage != null) ? (' title="' + this.options.previousPage + '"') : ('')) + '></a>');
				$previousElement.append($previousLink);
				this._bindSwitchPage($previousLink, this.options.activePage - 1);
				
				var $previousImage = $('<span class="icon icon16 icon-double-angle-left" />');
				$previousLink.append($previousImage);
			}
			else {
				var $previousImage = $('<span class="icon icon16 icon-double-angle-left" />');
				$previousElement.append($previousImage);
				$previousElement.addClass('disabled').removeClass('button');
				$previousImage.addClass('disabled');
			}
			
			// add first page
			$pageList.append(this._renderLink(1));
			
			// calculate page links
			var $maxLinks = this.SHOW_LINKS - 4;
			var $linksBefore = this.options.activePage - 2;
			if ($linksBefore < 0) $linksBefore = 0;
			var $linksAfter = this.options.maxPage - (this.options.activePage + 1);
			if ($linksAfter < 0) $linksAfter = 0;
			if (this.options.activePage > 1 && this.options.activePage < this.options.maxPage) $maxLinks--;
			
			var $half = $maxLinks / 2;
			var $left = this.options.activePage;
			var $right = this.options.activePage;
			if ($left < 1) $left = 1;
			if ($right < 1) $right = 1;
			if ($right > this.options.maxPage - 1) $right = this.options.maxPage - 1;
			
			if ($linksBefore >= $half) {
				$left -= $half;
			}
			else {
				$left -= $linksBefore;
				$right += $half - $linksBefore;
			}
			
			if ($linksAfter >= $half) {
				$right += $half;
			}
			else {
				$right += $linksAfter;
				$left -= $half - $linksAfter;
			}
			
			$right = Math.ceil($right);
			$left = Math.ceil($left);
			if ($left < 1) $left = 1;
			if ($right > this.options.maxPage) $right = this.options.maxPage;
			
			// left ... links
			if ($left > 1) {
				if ($left - 1 < 2) {
					$pageList.append(this._renderLink(2));
				}
				else {
					$('<li class="button jumpTo"><a title="' + WCF.Language.get('wcf.global.page.jumpTo') + '" class="jsTooltip">...</a></li>').appendTo($pageList);
					$hasHiddenPages = true;
				}
			}
			
			// visible links
			for (var $i = $left + 1; $i < $right; $i++) {
				$pageList.append(this._renderLink($i));
			}
			
			// right ... links
			if ($right < this.options.maxPage) {
				if (this.options.maxPage - $right < 2) {
					$pageList.append(this._renderLink(this.options.maxPage - 1));
				}
				else {
					$('<li class="button jumpTo"><a title="' + WCF.Language.get('wcf.global.page.jumpTo') + '" class="jsTooltip">...</a></li>').appendTo($pageList);
					$hasHiddenPages = true;
				}
			}
			
			// add last page
			$pageList.append(this._renderLink(this.options.maxPage));
			
			// add next button
			var $nextElement = $('<li class="button skip" />');
			$pageList.append($nextElement);
			
			if (this.options.activePage < this.options.maxPage) {
				var $nextLink = $('<a' + ((this.options.nextPage != null) ? (' title="' + this.options.nextPage + '"') : ('')) + '></a>');
				$nextElement.append($nextLink);
				this._bindSwitchPage($nextLink, this.options.activePage + 1);
				
				var $nextImage = $('<span class="icon icon16 icon-double-angle-right" />');
				$nextLink.append($nextImage);
			}
			else {
				var $nextImage = $('<span class="icon icon16 icon-double-angle-right" />');
				$nextElement.append($nextImage);
				$nextElement.addClass('disabled').removeClass('button');
				$nextImage.addClass('disabled');
			}
			
			if ($hasHiddenPages) {
				$pageList.data('pages', this.options.maxPage);
				WCF.System.PageNavigation.init('#' + $pageList.wcfIdentify(), $.proxy(function(pageNo) {
					this.switchPage(pageNo);
				}, this));
			}
		}
		else {
			// otherwise hide the paginator if not already hidden
			this.element.hide();
		}
	},
	
	/**
	 * Renders a page link.
	 * 
	 * @parameter	integer		page
	 * @return	jQuery
	 */
	_renderLink: function(page, lineBreak) {
		var $pageElement = $('<li class="button"></li>');
		if (lineBreak != undefined && lineBreak) {
			$pageElement.addClass('break');
		}
		if (page != this.options.activePage) {
			var $pageLink = $('<a>' + WCF.String.addThousandsSeparator(page) + '</a>'); 
			$pageElement.append($pageLink);
			this._bindSwitchPage($pageLink, page);
		}
		else {
			$pageElement.addClass('active');
			var $pageSubElement = $('<span>' + WCF.String.addThousandsSeparator(page) + '</span>');
			$pageElement.append($pageSubElement);
		}
		
		return $pageElement;
	},
	
	/**
	 * Binds the 'click'-event for the page switching to the given element.
	 * 
	 * @parameter	$(element)	element
	 * @paremeter	integer		page
	 */
	_bindSwitchPage: function(element, page) {
		var $self = this;
		element.click(function() {
			$self.switchPage(page);
		});
	},
	
	/**
	 * Switches to the given page
	 * 
	 * @parameter	Event		event
	 * @parameter	integer		page
	 */
	switchPage: function(page) {
		this._setOption('activePage', page);
	},
	
	/**
	 * Sets the given option to the given value.
	 * See the jQuery UI widget documentation for more.
	 */
	_setOption: function(key, value) {
		if (key == 'activePage') {
			if (value != this.options[key] && value > 0 && value <= this.options.maxPage) {
				// you can prevent the page switching by returning false or by event.preventDefault()
				// in a shouldSwitch-callback. e.g. if an AJAX request is already running.
				var $result = this._trigger('shouldSwitch', undefined, {
					nextPage: value
				});
				
				if ($result || $result !== undefined) {
					this.options[key] = value;
					this._render();
					this._trigger('switched', undefined, {
						activePage: value
					});
				}
				else {
					this._trigger('notSwitched', undefined, {
						activePage: value
					});
				}
			}
		}
		else {
			this.options[key] = value;
			
			if (key == 'disabled') {
				if (value) {
					this.element.children().remove();
				}
				else {
					this._render();
				}
			}
			else if (key == 'maxPage') {
				this._render();
			}
		}
		
		return this;
	},
	
	/**
	 * Start input of pagenumber
	 * 
	 * @parameter	Event		event
	 */
	_startInput: function(event) {
		// hide a-tag
		var $childLink = $(event.currentTarget);
		if (!$childLink.is('a')) $childLink = $childLink.parent('a');
		
		$childLink.hide();
		
		// show input-tag
		var $childInput = $childLink.parent('li').children('input')
			.css('display', 'block')
			.val('');
		
		$childInput.focus();
	},
	
	/**
	 * Stops input of pagenumber
	 * 
	 * @parameter	Event		event
	 */
	_stopInput: function(event) {
		// hide input-tag
		var $childInput = $(event.currentTarget);
		$childInput.css('display', 'none');
		
		// show a-tag
		var $childContainer = $childInput.parent('li');
		if ($childContainer != undefined && $childContainer != null) {
			$childContainer.children('a').show();
		}
	},
	
	/**
	 * Handles input of pagenumber
	 * 
	 * @parameter	Event		event
	 */
	_handleInput: function(event) {
		var $ie7 = ($.browser.msie && $.browser.version == '7.0');
		if (event.type != 'keyup' || $ie7) {
			if (!$ie7 || ((event.which == 13 || event.which == 27) && event.type == 'keyup')) {
				if (event.which == 13) {
					this.switchPage(parseInt($(event.currentTarget).val()));
				}
				
				if (event.which == 13 || event.which == 27) {
					this._stopInput(event);
					event.stopPropagation();
				}
			}
		}
	}
});

/**
 * Namespace for category related classes.
 */
WCF.Category = { };

/**
 * Handles selection of categories.
 */
WCF.Category.NestedList = Class.extend({
	/**
	 * list of categories
	 * @var	object
	 */
	_categories: { },
	
	/**
	 * Initializes the WCF.Category.NestedList object.
	 */
	init: function() {
		var self = this;
		$('.jsCategory').each(function(index, category) {
			var $category = $(category).data('parentCategoryID', null).change($.proxy(self._updateSelection, self));
			self._categories[$category.val()] = $category;
			
			// find child categories
			var $childCategoryIDs = [ ];
			$category.parents('li').find('.jsChildCategory').each(function(innerIndex, childCategory) {
				var $childCategory = $(childCategory).data('parentCategoryID', $category.val()).change($.proxy(self._updateSelection, self));
				self._categories[$childCategory.val()] = $childCategory;
				$childCategoryIDs.push($childCategory.val());
				
				if ($childCategory.is(':checked')) {
					$category.prop('checked', 'checked');
				}
			});
			
			$category.data('childCategoryIDs', $childCategoryIDs);
		});
	},
	
	/**
	 * Updates selection of categories.
	 * 
	 * @param	object		event
	 */
	_updateSelection: function(event) {
		var $category = $(event.currentTarget);
		var $parentCategoryID = $category.data('parentCategoryID');
		
		if ($category.is(':checked')) {
			// child category
			if ($parentCategoryID !== null) {
				// mark parent category as checked
				this._categories[$parentCategoryID].prop('checked', 'checked');
			}
		}
		else {
			// top-level category
			if ($parentCategoryID === null) {
				// unmark all child categories
				var $childCategoryIDs = $category.data('childCategoryIDs');
				for (var $i = 0, $length = $childCategoryIDs.length; $i < $length; $i++) {
					this._categories[$childCategoryIDs[$i]].prop('checked', false);
				}
			}
		}
	}
});

/**
 * Encapsulate eval() within an own function to prevent problems
 * with optimizing and minifiny JS.
 * 
 * @param	mixed		expression
 * @returns	mixed
 */
function wcfEval(expression) {
	return eval(expression);
}


// WCF.Like.js
/**
 * Like support for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Like = Class.extend({
	/**
	 * true, if users can like their own content
	 * @var	boolean
	 */
	_allowForOwnContent: false,
	
	/**
	 * user can like
	 * @var	boolean
	 */
	_canLike: false,
	
	/**
	 * list of containers
	 * @var	object
	 */
	_containers: { },
	
	/**
	 * container meta data
	 * @var	object
	 */
	_containerData: { },
	
	/**
	 * enables the dislike option
	 */
	_enableDislikes: true,
	
	/**
	 * prevents like/dislike until the server responded
	 * @var	boolean
	 */
	_isBusy: false,
	
	/**
	 * cached grouped user lists for like details
	 * @var	object
	 */
	_likeDetails: { },
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * shows the detailed summary of users who liked the object
	 * @var	boolean
	 */
	_showSummary: true,
	
	/**
	 * Initializes like support.
	 * 
	 * @param	boolean		canLike
	 * @param	boolean		enableDislikes
	 * @param	boolean		showSummary
	 * @param	boolean		allowForOwnContent
	 */
	init: function(canLike, enableDislikes, showSummary, allowForOwnContent) {
		this._canLike = canLike;
		this._enableDislikes = enableDislikes;
		this._isBusy = false;
		this._likeDetails = { };
		this._showSummary = showSummary;
		this._allowForOwnContent = allowForOwnContent;
		
		var $containers = this._getContainers();
		this._initContainers($containers);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// bind dom node inserted listener
		var $date = new Date();
		var $identifier = $date.toString().hashCode + $date.getUTCMilliseconds();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Like' + $identifier, $.proxy(this._domNodeInserted, this));
	},
	
	/**
	 * Initialize containers once new nodes are inserted.
	 */
	_domNodeInserted: function() {
		var $containers = this._getContainers();
		this._initContainers($containers);
		
	},
	
	/**
	 * Initializes like containers.
	 * 
	 * @param	object		containers
	 */
	_initContainers: function(containers) {
		var $createdWidgets = false;
		containers.each($.proxy(function(index, container) {
			// set container
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!this._containers[$containerID]) {
				this._containers[$containerID] = $container;
				
				// set container data
				this._containerData[$containerID] = {
					'likeButton': null,
					'badge': null,
					'dislikeButton': null,
					'likes': $container.data('like-likes'),
					'dislikes': $container.data('like-dislikes'),
					'objectType': $container.data('objectType'),
					'objectID': this._getObjectID($containerID),
					'users': eval($container.data('like-users')),
					'liked': $container.data('like-liked')
				};
				
				// create UI
				this._createWidget($containerID);
				
				$createdWidgets = true;
			}
		}, this));
		
		if ($createdWidgets) {
			new WCF.PeriodicalExecuter(function(pe) {
				pe.stop();
				
				WCF.DOMNodeInsertedHandler.execute();
			}, 250);
		}
	},
	
	/**
	 * Returns a list of available object containers.
	 * 
	 * @return	jQuery
	 */
	_getContainers: function() { },
	
	/**
	 * Returns widget container for target object container.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	_getWidgetContainer: function(containerID) { },
	
	/**
	 * Returns object id for targer object container.
	 * 
	 * @param	string		containerID
	 * @return	integer
	 */
	_getObjectID: function(containerID) { },
	
	/**
	 * Adds the like widget.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		widget
	 */
	_addWidget: function(containerID, widget) {
		var $widgetContainer = this._getWidgetContainer(containerID);
		
		widget.appendTo($widgetContainer);
	},
	
	/**
	 * Builds the like widget.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		likeButton
	 * @param	jQuery		dislikeButton
	 * @param	jQuery		badge
	 * @param	jQuery		summary
	 */
	_buildWidget: function(containerID, likeButton, dislikeButton, badge, summary) {
		var $widget = $('<aside class="likesWidget"><ul></ul></aside>');
		if (this._canLike) {
			likeButton.appendTo($widget.find('ul'));
			dislikeButton.appendTo($widget.find('ul'));
		}
		badge.appendTo($widget);
		
		this._addWidget(containerID, $widget); 
	},
	
	/**
	 * Creates the like widget.
	 * 
	 * @param	integer		containerID
	 */
	_createWidget: function(containerID) {
		var $likeButton = $('<li class="likeButton"><a title="'+WCF.Language.get('wcf.like.button.like')+'" class="jsTooltip"><span class="icon icon16 icon-thumbs-up-alt" /> <span class="invisible">'+WCF.Language.get('wcf.like.button.like')+'</span></a></li>');
		var $dislikeButton = $('<li class="dislikeButton"><a title="'+WCF.Language.get('wcf.like.button.dislike')+'" class="jsTooltip"><span class="icon icon16 icon-thumbs-down-alt" /> <span class="invisible">'+WCF.Language.get('wcf.like.button.dislike')+'</span></a></li>');
		if (!this._enableDislikes) $dislikeButton.hide();
		
		if (!this._allowForOwnContent && (WCF.User.userID == this._containers[containerID].data('userID'))) {
			$likeButton = $('');
			$dislikeButton = $('');
		}
		
		var $badge = $('<a class="badge jsTooltip likesBadge" />').data('containerID', containerID).click($.proxy(this._showLikeDetails, this));
		
		var $summary = null;
		if (this._showSummary) {
			$summary = $('<p class="likesSummary"><span class="pointer" /></p>');
			$summary.children('span').data('containerID', containerID).click($.proxy(this._showLikeDetails, this));
		}
		this._buildWidget(containerID, $likeButton, $dislikeButton, $badge, $summary);
		
		this._containerData[containerID].likeButton = $likeButton;
		this._containerData[containerID].dislikeButton = $dislikeButton;
		this._containerData[containerID].badge = $badge;
		this._containerData[containerID].summary = $summary;
		
		$likeButton.data('containerID', containerID).data('type', 'like').click($.proxy(this._click, this));
		$dislikeButton.data('containerID', containerID).data('type', 'dislike').click($.proxy(this._click, this));
		this._setActiveState($likeButton, $dislikeButton, this._containerData[containerID].liked);
		this._updateBadge(containerID);
		if (this._showSummary) this._updateSummary(containerID);
	},
	
	/**
	 * Displays like details for an object.
	 * 
	 * @param	object		event
	 * @param	string		containerID
	 */
	_showLikeDetails: function(event, containerID) {
		var $containerID = (event === null) ? containerID : $(event.currentTarget).data('containerID');
		
		if (this._likeDetails[$containerID] === undefined) {
			this._likeDetails[$containerID] = new WCF.User.List('wcf\\data\\like\\LikeAction', WCF.Language.get('wcf.like.details'), {
				data: {
					containerID: $containerID,
					objectID: this._containerData[$containerID].objectID,
					objectType: this._containerData[$containerID].objectType
				}
			});
		}
		
		this._likeDetails[$containerID].open();
	},
	
	/**
	 * Handles likes and dislikes.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $button = $(event.currentTarget);
		if ($button === null) {
			console.debug("[WCF.Like] Unable to find target button, aborting.");
			return;
		}
		
		this._sendRequest($button.data('containerID'), $button.data('type'));
	},
	
	/**
	 * Sends request through proxy.
	 * 
	 * @param	integer		containerID
	 * @param	string		type
	 */
	_sendRequest: function(containerID, type) {
		// ignore retards spamming clicks on the buttons
		if (this._isBusy) {
			return;
		}
		
		this._isBusy = true;
		
		this._proxy.setOption('data', {
			actionName: type,
			className: 'wcf\\data\\like\\LikeAction',
			parameters: {
				data: {
					containerID: containerID,
					objectID: this._containerData[containerID].objectID,
					objectType: this._containerData[containerID].objectType
				}
			}
		});
		
		this._proxy.sendRequest();
	},
	
	/**
	 * Updates likeable object.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		
		if (!this._containers[$containerID]) {
			return;
		}
		
		switch (data.actionName) {
			case 'dislike':
			case 'like':
				// update container data
				this._containerData[$containerID].likes = parseInt(data.returnValues.likes);
				this._containerData[$containerID].dislikes = parseInt(data.returnValues.dislikes);
				this._containerData[$containerID].users = data.returnValues.users;
				
				// update label
				this._updateBadge($containerID);
				// update summary
				if (this._showSummary) this._updateSummary($containerID);
				
				// mark button as active
				var $likeButton = this._containerData[$containerID].likeButton;
				var $dislikeButton = this._containerData[$containerID].dislikeButton;
				var $likeStatus = 0;
				if (data.returnValues.isLiked) $likeStatus = 1;
				else if (data.returnValues.isDisliked) $likeStatus = -1;
				this._setActiveState($likeButton, $dislikeButton, $likeStatus);
				
				// invalidate cache for like details
				if (this._likeDetails[$containerID] !== undefined) {
					delete this._likeDetails[$containerID];
				}
				
				this._isBusy = false;
			break;
		}
	},
	
	_updateBadge: function(containerID) {
		if (!this._containerData[containerID].likes && !this._containerData[containerID].dislikes) {
			this._containerData[containerID].badge.hide();
		}
		else {
			this._containerData[containerID].badge.show();
			
			// update like counter
			var $cumulativeLikes = this._containerData[containerID].likes - this._containerData[containerID].dislikes;
			var $badge = this._containerData[containerID].badge;
			$badge.removeClass('green red');
			if ($cumulativeLikes > 0) {
				$badge.text('+' + WCF.String.formatNumeric($cumulativeLikes));
				$badge.addClass('green');
			}
			else if ($cumulativeLikes < 0) {
				$badge.text(WCF.String.formatNumeric($cumulativeLikes));
				$badge.addClass('red');
			}
			else {
				$badge.text('\u00B10');
			}
			
			// update tooltip
			var $likes = this._containerData[containerID].likes;
			var $dislikes = this._containerData[containerID].dislikes;
			$badge.data('tooltip', WCF.Language.get('wcf.like.tooltip', { likes: $likes, dislikes: $dislikes }));
		}
	},
	
	_updateSummary: function(containerID) {
		if (!this._containerData[containerID].likes) {
			this._containerData[containerID].summary.hide();
		}
		else {
			this._containerData[containerID].summary.show();
			
			var $users = this._containerData[containerID].users;
			var $userArray = [];
			for (var $userID in $users) $userArray.push($users[$userID].username);
			var $others = this._containerData[containerID].likes - $userArray.length;
			
			this._containerData[containerID].summary.children('span').html(WCF.Language.get('wcf.like.summary', { users: $userArray, others: $others }));
		}
	},
	
	/**
	 * Sets button active state.
	 * 
	 * @param	jquery		likeButton
	 * @param	jquery		dislikeButton
	 * @param	integer		likeStatus
	 */
	_setActiveState: function(likeButton, dislikeButton, likeStatus) {
		likeButton.removeClass('active');
		dislikeButton.removeClass('active');
		
		if (likeStatus == 1) {
			likeButton.addClass('active');
		}
		else if (likeStatus == -1) {
			dislikeButton.addClass('active');
		}
	}
});


// WCF.ACL.js
/**
 * Namespace for ACL
 */
WCF.ACL = { };

/**
 * ACL support for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ACL.List = Class.extend({
	/**
	 * name of the category the acl options belong to
	 * @var	string
	 */
	_categoryName: '',
	
	/**
	 * ACL container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * list of ACL container elements
	 * @var	object
	 */
	_containerElements: { },
	
	/**
	 * object id
	 * @var	integer
	 */
	_objectID: 0,
	
	/**
	 * object type id
	 * @var	integer
	 */
	_objectTypeID: null,
	
	/**
	 * list of available ACL options
	 * @var	object
	 */
	_options: { },
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * user search handler
	 * @var	WCF.Search.User
	 */
	_search: null,
	
	/**
	 * list of ACL settings
	 * @var	object
	 */
	_values: {
		group: { },
		user: { }
	},
	
	/**
	 * Initializes the ACL configuration.
	 * 
	 * @param	string		containerSelector
	 * @param	integer		objectTypeID
	 * @param	string		categoryName
	 * @param	integer		objectID
	 * @param	boolean		includeUserGroups
	 */
	init: function(containerSelector, objectTypeID, categoryName, objectID, includeUserGroups, initialPermissions) {
		this._objectID = objectID || 0;
		this._objectTypeID = objectTypeID;
		this._categoryName = categoryName;
		if (includeUserGroups === undefined) {
			includeUserGroups = true;
		}
		this._values = {
			group: { },
			user: { }
		};
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
		
		// bind hidden container
		this._container = $(containerSelector).hide().addClass('aclContainer');
		
		// insert container elements
		var $elementContainer = this._container.children('dd');
		var $aclList = $('<ul class="aclList container" />').appendTo($elementContainer);
		var $searchInput = $('<input type="text" class="long" placeholder="' + WCF.Language.get('wcf.acl.search.' + (!includeUserGroups ? 'user.' : '') + 'description') + '" />').appendTo($elementContainer);
		var $permissionList = $('<ul class="aclPermissionList container" />').hide().appendTo($elementContainer);
		
		// set elements
		this._containerElements = {
			aclList: $aclList,
			denyAll: null,
			grantAll: null,
			permissionList: $permissionList,
			searchInput: $searchInput
		};
		
		// prepare search input
		this._search = new WCF.Search.User($searchInput, $.proxy(this.addObject, this), includeUserGroups);
		
		// bind event listener for submit
		var $form = this._container.parents('form:eq(0)');
		$form.submit($.proxy(this.submit, this));
		
		// reset ACL on click
		var $resetButton = $form.find('input[type=reset]:eq(0)');
		if ($resetButton.length) {
			$resetButton.click($.proxy(this._reset, this));
		}
		
		if (initialPermissions) {
			this._success(initialPermissions);
		}
		else {
			this._loadACL();
		}
	},
	
	/**
	 * Restores the original ACL state.
	 */
	_reset: function() {
		// reset stored values
		this._values = {
			group: { },
			user: { }
		};
		
		// remove entries
		this._containerElements.aclList.empty();
		this._containerElements.searchInput.val('');
		
		// deselect all input elements
		this._containerElements.permissionList.hide().find('input[type=checkbox]').prop('checked', false);
	},
	
	/**
	 * Loads current ACL configuration.
	 */
	_loadACL: function() {
		this._proxy.setOption('data', {
			actionName: 'loadAll',
			className: 'wcf\\data\\acl\\option\\ACLOptionAction',
			parameters: {
				categoryName: this._categoryName,
				objectID: this._objectID,
				objectTypeID: this._objectTypeID
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Adds a new object to acl list.
	 * 
	 * @param	object		data
	 */
	addObject: function(data) {
		var $listItem = this._createListItem(data.objectID, data.label, data.type);
		
		// toggle element
		this._savePermissions();
		this._containerElements.aclList.children('li').removeClass('active');
		$listItem.addClass('active');
		
		this._search.addExcludedSearchValue(data.label);
		
		// uncheck all option values
		this._containerElements.permissionList.find('input[type=checkbox]').prop('checked', false);
		
		// clear search input
		this._containerElements.searchInput.val('');
		
		// show permissions
		this._containerElements.permissionList.show();
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Creates a list item with the given data and returns it.
	 * 
	 * @param	integer		objectID
	 * @param	string		label
	 * @param	string		type
	 * @return	jQuery
	 */
	_createListItem: function(objectID, label, type) {
		var $listItem = $('<li><span class="icon icon16 icon-' + (type === 'group' ? 'group' : 'user') + '" /> <span>' + label + '</span></li>').appendTo(this._containerElements.aclList);
		$listItem.data('objectID', objectID).data('type', type).data('label', label).click($.proxy(this._click, this));
		$('<span class="icon icon16 icon-remove jsTooltip pointer" title="' + WCF.Language.get('wcf.global.button.delete') + '" />').click($.proxy(this._removeItem, this)).appendTo($listItem);
		
		return $listItem;
	},
	
	/**
	 * Removes an item from list.
	 * 
	 * @param	object		event
	 */
	_removeItem: function(event) {
		var $listItem = $(event.currentTarget).parent();
		var $type = $listItem.data('type');
		var $objectID = $listItem.data('objectID');
		
		this._search.removeExcludedSearchValue($listItem.data('label'));
		$listItem.remove();
		
		// remove stored data
		if (this._values[$type][$objectID]) {
			delete this._values[$type][$objectID];
		}
		
		// try to select something else
		this._selectFirstEntry();
	},
	
	/**
	 * Selects the first available entry.
	 */
	_selectFirstEntry: function() {
		var $listItem = this._containerElements.aclList.children('li:eq(0)');
		if ($listItem.length) {
			this._select($listItem, false);
		}
		else {
			this._reset();
		}
	},
	
	/**
	 * Parses current ACL configuration.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (!$.getLength(data.returnValues.options)) {
			return;
		}
		
		// prepare options
		var $count = 0;
		var $structure = { };
		for (var $optionID in data.returnValues.options) {
			var $option = data.returnValues.options[$optionID];
			
			var $listItem = $('<li><span>' + $option.label + '</span></li>').data('optionID', $optionID).data('optionName', $option.optionName);
			var $grantPermission = $('<input type="checkbox" id="grant' + $optionID + '" />').appendTo($listItem).wrap('<label for="grant' + $optionID + '" class="jsTooltip" title="' + WCF.Language.get('wcf.acl.option.grant') + '" />');
			var $denyPermission = $('<input type="checkbox" id="deny' + $optionID + '" />').appendTo($listItem).wrap('<label for="deny' + $optionID + '" class="jsTooltip" title="' + WCF.Language.get('wcf.acl.option.deny') + '" />');
			
			$grantPermission.data('type', 'grant').data('optionID', $optionID).change($.proxy(this._change, this));
			$denyPermission.data('type', 'deny').data('optionID', $optionID).change($.proxy(this._change, this));
			
			if (!$structure[$option.categoryName]) {
				$structure[$option.categoryName] = [ ];
			}
			
			if ($option.categoryName === '') {
				$listItem.appendTo(this._containerElements.permissionList);
			}
			else {
				$structure[$option.categoryName].push($listItem);
			}
			
			$count++;
		}
		
		// add a "full access" permission if there are more than one option
		if ($count > 1) {
			var $listItem = $('<li class="aclFullAccess"><span>' + WCF.Language.get('wcf.acl.option.fullAccess') + '</span></li>').prependTo(this._containerElements.permissionList);
			this._containerElements.grantAll = $('<input type="checkbox" id="grantAll" />').appendTo($listItem).wrap('<label for="grantAll" class="jsTooltip" title="' + WCF.Language.get('wcf.acl.option.grant') + '" />');
			this._containerElements.denyAll = $('<input type="checkbox" id="denyAll" />').appendTo($listItem).wrap('<label for="denyAll" class="jsTooltip" title="' + WCF.Language.get('wcf.acl.option.deny') + '" />');
			
			// bind events
			this._containerElements.grantAll.data('type', 'grant').change($.proxy(this._changeAll, this));
			this._containerElements.denyAll.data('type', 'deny').change($.proxy(this._changeAll, this));
		}
		
		if ($.getLength($structure)) {
			for (var $categoryName in $structure) {
				var $listItems = $structure[$categoryName];
				
				if (data.returnValues.categories[$categoryName]) {
					$('<li class="aclCategory">' + data.returnValues.categories[$categoryName] + '</li>').appendTo(this._containerElements.permissionList);
				}
				
				for (var $i = 0, $length = $listItems.length; $i < $length; $i++) {
					$listItems[$i].appendTo(this._containerElements.permissionList);
				}
			}
		}
		
		// set data
		this._parseData(data, 'group');
		this._parseData(data, 'user');
		
		// show container
		this._container.show();
		
		// pre-select an entry
		this._selectFirstEntry();
	},
	
	/**
	 * Parses user and group data.
	 * 
	 * @param	object		data
	 * @param	string		type
	 */
	_parseData: function(data, type) {
		if (!$.getLength(data.returnValues[type].option)) {
			return;
		}
		
		// add list items
		for (var $typeID in data.returnValues[type].label) {
			this._createListItem($typeID, data.returnValues[type].label[$typeID], type);
			
			this._search.addExcludedSearchValue(data.returnValues[type].label[$typeID]);
		}
		
		// add options
		this._values[type] = data.returnValues[type].option;
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Prepares permission list for a specific object.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $listItem = $(event.currentTarget);
		if ($listItem.hasClass('active')) {
			return;
		}
		
		this._select($listItem, true);
	},
	
	/**
	 * Selects the given item and marks it as active.
	 * 
	 * @param	jQuery		listItem
	 * @param	boolean		savePermissions
	 */
	_select: function(listItem, savePermissions) {
		// save previous permissions
		if (savePermissions) {
			this._savePermissions();
		}
		
		// switch active item
		this._containerElements.aclList.children('li').removeClass('active');
		listItem.addClass('active');
		
		// apply permissions for current item
		this._setupPermissions(listItem.data('type'), listItem.data('objectID'));
	},
	
	/**
	 * Toggles between deny and grant.
	 * 
	 * @param	object		event
	 */
	_change: function(event) {
		var $checkbox = $(event.currentTarget);
		var $optionID = $checkbox.data('optionID');
		var $type = $checkbox.data('type');
		
		if ($checkbox.is(':checked')) {
			if ($type === 'deny') {
				$('#grant' + $optionID).prop('checked', false);
				
				if (this._containerElements.grantAll !== null) {
					this._containerElements.grantAll.prop('checked', false);
				}
			}
			else {
				$('#deny' + $optionID).prop('checked', false);
				
				if (this._containerElements.denyAll !== null) {
					this._containerElements.denyAll.prop('checked', false);
				}
			}
		}
		else {
			if ($type === 'deny' && this._containerElements.denyAll !== null) {
				this._containerElements.denyAll.prop('checked', false);
			}
			else if ($type === 'grant' && this._containerElements.grantAll !== null) {
				this._containerElements.grantAll.prop('checked', false);
			}
		}
		
		var $allChecked = true;
		this._containerElements.permissionList.find('input[type=checkbox]').each(function(index, item) {
			var $item = $(item);
			
			if ($item.data('type') === $type && $item.attr('id') !== $type + 'All') {
				if (!$item.is(':checked')) {
					$allChecked = false;
					return false;
				}
			}
		});
		if ($type == 'deny') {
			if (this._containerElements.denyAll !== null) {
				if ($allChecked) this._containerElements.denyAll.prop('checked', true);
				else this._containerElements.denyAll.prop('checked', false);
			}
		}
		else {
			if (this._containerElements.grantAll !== null) {
				if ($allChecked) this._containerElements.grantAll.prop('checked', true);
				else this._containerElements.grantAll.prop('checked', false);
			}
		}
	},
	
	/**
	 * Toggles all options between deny and grant.
	 * 
	 * @param	object		event
	 */
	_changeAll: function(event) {
		var $checkbox = $(event.currentTarget);
		var $type = $checkbox.data('type');
		
		if ($checkbox.is(':checked')) {
			if ($type === 'deny') {
				this._containerElements.grantAll.prop('checked', false);
				
				this._containerElements.permissionList.find('input[type=checkbox]').each(function(index, item) {
					var $item = $(item);
					
					if ($item.data('type') === 'deny' && $item.attr('id') !== 'denyAll') {
						$item.prop('checked', true).trigger('change');
					}
				});
			}
			else {
				this._containerElements.denyAll.prop('checked', false);
				
				this._containerElements.permissionList.find('input[type=checkbox]').each(function(index, item) {
					var $item = $(item);
					
					if ($item.data('type') === 'grant' && $item.attr('id') !== 'grantAll') {
						$item.prop('checked', true).trigger('change');
					}
				});
			}
		}
		else {
			if ($type === 'deny') {
				this._containerElements.grantAll.prop('checked', false);
				
				this._containerElements.permissionList.find('input[type=checkbox]').each(function(index, item) {
					var $item = $(item);
					
					if ($item.data('type') === 'deny' && $item.attr('id') !== 'denyAll') {
						$item.prop('checked', false).trigger('change');
					}
				});
			}
			else {
				this._containerElements.denyAll.prop('checked', false);
				
				this._containerElements.permissionList.find('input[type=checkbox]').each(function(index, item) {
					var $item = $(item);
					
					if ($item.data('type') === 'grant' && $item.attr('id') !== 'grantAll') {
						$item.prop('checked', false).trigger('change');
					}
				});
			}
		}
	},
	
	/**
	 * Setups permission input for given object.
	 * 
	 * @param	string		type
	 * @param	integer		objectID
	 */
	_setupPermissions: function(type, objectID) {
		// reset all checkboxes to unchecked
		this._containerElements.permissionList.find("input[type='checkbox']").prop('checked', false);
		
		// use stored permissions if applicable
		if (this._values[type] && this._values[type][objectID]) {
			for (var $optionID in this._values[type][objectID]) {
				if (this._values[type][objectID][$optionID] == 1) {
					$('#grant' + $optionID).prop('checked', true).trigger('change');
				}
				else {
					$('#deny' + $optionID).prop('checked', true).trigger('change');
				}
			}
		}
		
		// show permissions
		this._containerElements.permissionList.show();
	},
	
	/**
	 * Saves currently set permissions.
	 */
	_savePermissions: function() {
		// get active object
		var $activeObject = this._containerElements.aclList.find('li.active');
		if (!$activeObject.length) {
			return;
		}
		
		var $objectID = $activeObject.data('objectID');
		var $type = $activeObject.data('type');
		
		// clear old values
		this._values[$type][$objectID] = { };
		
		var self = this;
		this._containerElements.permissionList.find("input[type='checkbox']").each(function(index, checkbox) {
			var $checkbox = $(checkbox);
			if ($checkbox.attr('id') != 'grantAll' && $checkbox.attr('id') != 'denyAll') {
				var $optionValue = ($checkbox.data('type') === 'deny') ? 0 : 1;
				var $optionID = $checkbox.data('optionID');
				
				if ($checkbox.is(':checked')) {
					// store value
					self._values[$type][$objectID][$optionID] = $optionValue;
					
					// reset value afterwards
					$checkbox.prop('checked', false);
				}
				else if (self._values[$type] && self._values[$type][$objectID] && self._values[$type][$objectID][$optionID] && self._values[$type][$objectID][$optionID] == $optionValue) {
					delete self._values[$type][$objectID][$optionID];
				}
			}
		});
	},
	
	/**
	 * Prepares ACL values on submit.
	 * 
	 * @param	object		event
	 */
	submit: function(event) {
		this._savePermissions();
		
		this._save('group');
		this._save('user');
	},
	
	/**
	 * Inserts hidden form elements for each value.
	 * 
	 * @param	string		$type
	 */
	_save: function($type) {
		if ($.getLength(this._values[$type])) {
			var $form = this._container.parents('form:eq(0)');
			
			for (var $objectID in this._values[$type]) {
				var $object = this._values[$type][$objectID];
				
				for (var $optionID in $object) {
					$('<input type="hidden" name="aclValues[' + $type + '][' + $objectID + '][' + $optionID + ']" value="' + $object[$optionID] + '" />').appendTo($form);
				}
			}
		}
	}
});


// WCF.Attachment.js
/**
 * Namespace for attachments
 */
WCF.Attachment = {};

/**
 * Attachment upload function
 * 
 * @see	WCF.Upload
 */
WCF.Attachment.Upload = WCF.Upload.extend({
	/**
	 * object type of the object the uploaded attachments belong to
	 * @var	string
	 */
	_objectType: '',
	
	/**
	 * id of the object the uploaded attachments belong to
	 * @var	string
	 */
	_objectID: 0,
	
	/**
	 * temporary hash to identify uploaded attachments
	 * @var	string
	 */
	_tmpHash: '',
	
	/**
	 * id of the parent object of the object the uploaded attachments belongs to
	 * @var	string
	 */
	_parentObjectID: 0,
	
	/**
	 * container if of WYSIWYG editor
	 * @var	string
	 */
	_wysiwygContainerID: '',
	
	/**
	 * @see	WCF.Upload.init()
	 */
	init: function(buttonSelector, fileListSelector, objectType, objectID, tmpHash, parentObjectID, maxUploads, wysiwygContainerID) {
		this._super(buttonSelector, fileListSelector, 'wcf\\data\\attachment\\AttachmentAction', { multiple: true, maxUploads: maxUploads });
		
		this._objectType = objectType;
		this._objectID = objectID;
		this._tmpHash = tmpHash;
		this._parentObjectID = parentObjectID;
		this._wysiwygContainerID = wysiwygContainerID;
		
		this._buttonSelector.children('p.button').click($.proxy(this._validateLimit, this));
		this._fileListSelector.find('.jsButtonInsertAttachment').click($.proxy(this._insert, this));
		
		WCF.DOMNodeRemovedHandler.addCallback('WCF.Attachment.Upload', $.proxy(this._removeLimitError, this));
	},
	
	/**
	 * Validates upload limits.
	 * 
	 * @return	boolean
	 */
	_validateLimit: function() {
		var $innerError = this._buttonSelector.next('small.innerError');
		
		// check maximum uploads
		var $max = this._options.maxUploads - this._fileListSelector.children('li:not(.uploadFailed)').length;
		var $filesLength = (this._fileUpload) ? this._fileUpload.prop('files').length : 0;
		if ($max <= 0 || $max < $filesLength) {
			// reached limit
			var $errorMessage = ($max <= 0) ? WCF.Language.get('wcf.attachment.upload.error.reachedLimit') : WCF.Language.get('wcf.attachment.upload.error.reachedRemainingLimit').replace(/#remaining#/, $max);
			if (!$innerError.length) {
				$innerError = $('<small class="innerError" />').insertAfter(this._buttonSelector);
			}
			
			$innerError.html($errorMessage);
			
			return false;
		}
		
		// remove previous errors
		$innerError.remove();
		
		return true;
	},
	
	/**
	 * Removes the limit error message.
	 * 
	 * @param	object		event
	 */
	_removeLimitError: function(event) {
		var $target = $(event.target);
		if ($target.is('li.box48') && $target.parent().wcfIdentify() === this._fileListSelector.wcfIdentify()) {
			this._buttonSelector.next('small.innerError').remove();
		}
	},
	
	/**
	 * @see	WCF.Upload._upload()
	 */
	_upload: function() {
		if (this._validateLimit()) {
			this._super();
		}
		
		if (this._fileUpload) {
			// remove and re-create the upload button since the 'files' property
			// of the input field is readonly thus it can't be reset
			this._removeButton();
			this._createButton();
		}
	},
	
	/**
	 * @see	WCF.Upload._createUploadMatrix()
	 */
	_createUploadMatrix: function(files) {
		// remove failed uploads
		this._fileListSelector.children('li.uploadFailed').remove();
		
		return this._super(files);
	},
	
	/**
	 * @see	WCF.Upload._getParameters()
	 */
	_getParameters: function() {
		return {
			objectType: this._objectType,
			objectID: this._objectID,
			tmpHash: this._tmpHash,
			parentObjectID: this._parentObjectID
		};
	},
	
	/**
	 * @see	WCF.Upload._initFile()
	 */
	_initFile: function(file) {
		var $li = $('<li class="box48"><span class="icon icon48 icon-spinner" /><div><div><p>'+file.name+'</p><small><progress max="100"></progress></small></div><ul></ul></div></li>').data('filename', file.name);
		this._fileListSelector.append($li);
		this._fileListSelector.show();
		
		// validate file size
		if (this._buttonSelector.data('maxSize') < file.size) {
			// remove progress bar
			$li.find('progress').remove();
			
			// upload icon
			$li.children('.icon-spinner').removeClass('icon-spinner').addClass('icon-ban-circle');
			
			// error message
			$li.find('div > div').append($('<small class="innerError">' + WCF.Language.get('wcf.attachment.upload.error.tooLarge') + '</small>'));
			$li.addClass('uploadFailed');
		}
		
		return $li;
	},
	
	/**
	 * @see	WCF.Upload._success()
	 */
	_success: function(uploadID, data) {
		for (var $i in this._uploadMatrix[uploadID]) {
			// get li
			var $li = this._uploadMatrix[uploadID][$i];
			
			// remove progress bar
			$li.find('progress').remove();
			
			// get filename and check result
			var $filename = $li.data('filename');
			var $internalFileID = $li.data('internalFileID');
			if (data.returnValues && data.returnValues.attachments[$internalFileID]) {
				// show thumbnail
				if (data.returnValues.attachments[$internalFileID]['tinyURL']) {
					$li.children('.icon-spinner').replaceWith($('<img src="' + data.returnValues.attachments[$internalFileID]['tinyURL'] + '" alt="" class="attachmentTinyThumbnail" />'));
				}
				// show file icon
				else {
					$li.children('.icon-spinner').removeClass('icon-spinner').addClass('icon-paper-clip');
				}
				
				// update attachment link
				var $link = $('<a href=""></a>');
				$link.text($filename).attr('href', data.returnValues.attachments[$internalFileID]['url']);
				
				if (data.returnValues.attachments[$internalFileID]['isImage'] != 0) {
					$link.addClass('jsImageViewer').attr('title', $filename);
				}
				$li.find('p').empty().append($link);
				
				// update file size
				$li.find('small').append(data.returnValues.attachments[$internalFileID]['formattedFilesize']);
				
				// init buttons
				var $deleteButton = $('<li><span class="icon icon16 icon-remove pointer jsTooltip jsDeleteButton" title="'+WCF.Language.get('wcf.global.button.delete')+'" data-object-id="'+data.returnValues.attachments[$internalFileID]['attachmentID']+'" data-confirm-message="'+WCF.Language.get('wcf.attachment.delete.sure')+'" /></li>');
				$li.find('ul').append($deleteButton);
				
				if (this._wysiwygContainerID) {
					var $insertButton = $('<li><span class="icon icon16 icon-paste pointer jsTooltip jsButtonInsertAttachment" title="' + WCF.Language.get('wcf.attachment.insert') + '" data-object-id="' + data.returnValues.attachments[$internalFileID]['attachmentID'] + '" /></li>');
					$insertButton.children('.jsButtonInsertAttachment').click($.proxy(this._insert, this));
					$li.find('ul').append($insertButton);
				}
			}
			else {
				// upload icon
				$li.children('.icon-spinner').removeClass('icon-spinner').addClass('icon-ban-circle');
				var $errorMessage = '';
				
				// error handling
				if (data.returnValues && data.returnValues.errors[$internalFileID]) {
					$errorMessage = data.returnValues.errors[$internalFileID]['errorType'];
				}
				else {
					// unknown error
					$errorMessage = 'uploadFailed';
				}
				
				$li.find('div > div').append($('<small class="innerError">'+WCF.Language.get('wcf.attachment.upload.error.'+$errorMessage)+'</small>'));
				$li.addClass('uploadFailed');
			}
			
			// fix webkit rendering bug
			$li.css('display', 'block');
		}
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Inserts an attachment into WYSIWYG editor contents.
	 * 
	 * @param	object		event
	 */
	_insert: function(event) {
		var $attachmentID = $(event.currentTarget).data('objectID');
		var $bbcode = '[attach=' + $attachmentID + '][/attach]';
		
		var $ckEditor = ($.browser.mobile) ? null : $('#' + this._wysiwygContainerID).ckeditorGet();
		if ($ckEditor !== null && $ckEditor.mode === 'wysiwyg') {
			// in design mode
			$ckEditor.insertText($bbcode);
		}
		else {
			// in source mode
			var $textarea = ($.browser.mobile) ? $('#' + this._wysiwygContainerID) : $('#' + this._wysiwygContainerID).next('.cke_editor_text').find('textarea');
			var $value = $textarea.val();
			if ($value.length == 0) {
				$textarea.val($bbcode);
			}
			else {
				var $position = $textarea.getCaret();
				$textarea.val( $value.substr(0, $position) + $bbcode + $value.substr($position) );
			}
		}
	},
	
	/**
	 * @see	WCF.Upload._error()
	 */
	_error: function(data) {
		// mark uploads as failed
		this._fileListSelector.find('li').each(function(index, listItem) {
			var $listItem = $(listItem);
			if ($listItem.children('.icon-spinner').length) {
				// upload icon
				$listItem.addClass('uploadFailed').children('.icon-spinner').removeClass('icon-spinner').addClass('icon-ban-circle');
				$listItem.find('div > div').append($('<small class="innerError">' + (data.responseJSON && data.responseJSON.message ? data.responseJSON.message : WCF.Language.get('wcf.attachment.upload.error.uploadFailed')) + '</small>'));
			}
		});
	}
});


// WCF.ColorPicker.js
/**
 * Color picker for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ColorPicker = Class.extend({
	/**
	 * hue bar element
	 * @var	jQuery
	 */
	_bar: null,
	
	/**
	 * bar selector is being moved
	 * @var	boolean
	 */
	_barActive: false,
	
	/**
	 * bar selector element
	 * @var	jQuery
	 */
	_barSelector: null,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * active element id
	 * @var	string
	 */
	_elementID: '',
	
	/**
	 * saturation and value gradient element
	 * @var	jQuery
	 */
	_gradient: null,
	
	/**
	 * gradient selector is being moved
	 * @var	boolean
	 */
	_gradientActive: false,
	
	/**
	 * gradient selector element
	 * @var	jQuery
	 */
	_gradientSelector: null,
	
	/**
	 * HEX input element
	 * @var	jQuery
	 */
	_hex: null,
	
	/**
	 * HSV representation
	 * @var	object
	 */
	_hsv: { },
	
	/**
	 * visual new color element
	 * @var	jQuery
	 */
	_newColor: null,
	
	/**
	 * visual previous color element
	 * @var	jQuery
	 */
	_oldColor: null,
	
	/**
	 * list of RGBa input elements
	 * @var	object
	 */
	_rgba: { },
	
	/**
	 * RegExp to parse rgba()
	 * @var	RegExp
	 */
	_rgbaRegExp: null,
	
	/**
	 * Initializes the WCF.ColorPicker class.
	 * 
	 * @param	string		selector
	 */
	init: function(selector) {
		this._elementID = '';
		this._hsv = { h: 0, s: 100, v: 100 };
		this._position = { };
		
		var $elements = $(selector);
		if (!$elements.length) {
			console.debug("[WCF.ColorPicker] Selector does not match any element, aborting.");
			return;
		}
		
		$elements.click($.proxy(this._open, this));
	},
	
	/**
	 * Opens the color picker overlay.
	 * 
	 * @param	object		event
	 */
	_open: function(event) {
		if (!this._didInit) {
			// init color picker on first usage
			this._initColorPicker();
			this._didInit = true;
		}
		
		// load values from element
		var $element = $(event.currentTarget);
		this._elementID = $element.wcfIdentify();
		this._parseColor($element);
		
		// set 'current' color
		var $rgb = this.hsvToRgb(this._hsv.h, this._hsv.s, this._hsv.v);
		this._oldColor.css({ backgroundColor: 'rgb(' + $rgb.r + ', ' + $rgb.g + ', ' + $rgb.b + ')' });
		
		this._dialog.wcfDialog({
			'title': WCF.Language.get('wcf.style.colorPicker')
		});
	},
	
	/**
	 * Parses the color of an element.
	 * 
	 * @param	jQuery		element
	 */
	_parseColor: function(element) {
		if (element.data('hsv') && element.data('rgb')) {
			// create an explicit copy here, otherwise it would be only a reference
			var $hsv = element.data('hsv');
			for (var $type in $hsv) {
				this._hsv[$type] = $hsv[$type];
			}
			this._updateValues(element.data('rgb'), true, true);
			this._rgba.a.val(parseInt(element.data('alpha')));
		}
		else {
			if (this._rgbaRegExp === null) {
				this._rgbaRegExp = new RegExp("^rgba\\((\\d{1,3}), ?(\\d{1,3}), ?(\\d{1,3}), ?(1|1\\.00?|0|0?\\.[0-9]{1,2})\\)$");
			}
			
			// parse value
			this._rgbaRegExp.exec(element.data('color'));
			var $alpha = RegExp.$4;
			// convert into x.yz
			if ($alpha.indexOf('.') === 0) {
				$alpha = "0" + $alpha;
			}
			$alpha *= 100;
			
			this._updateValues({
				r: RegExp.$1,
				g: RegExp.$2,
				b: RegExp.$3,
				a: Math.round($alpha)
			}, true, true);
		}
	},
	
	/**
	 * Initializes the color picker upon first usage.
	 */
	_initColorPicker: function() {
		this._dialog = $('<div id="colorPickerContainer" />').hide().appendTo(document.body);
		
		// create gradient
		this._gradient = $('<div id="colorPickerGradient" />').appendTo(this._dialog);
		this._gradientSelector = $('<span id="colorPickerGradientSelector"><span></span></span>').appendTo(this._gradient);
		
		// create bar
		this._bar = $('<div id="colorPickerBar" />').appendTo(this._dialog);
		this._barSelector = $('<span id="colorPickerBarSelector" />').appendTo(this._bar);
		
		// bind event listener
		this._gradient.mousedown($.proxy(this._mouseDownGradient, this));
		this._bar.mousedown($.proxy(this._mouseDownBar, this));
		
		var self = this;
		$(document).mouseup(function(event) {
			if (self._barActive) {
				self._barActive = false;
				self._mouseBar(event);
			}
			else if (self._gradientActive) {
				self._gradientActive = false;
				self._mouseGradient(event);
			}
		}).mousemove(function(event) {
			if (self._barActive) {
				self._mouseBar(event);
			}
			else if (self._gradientActive) {
				self._mouseGradient(event);
			}
		});
		
		this._initColorPickerForm();
	},
	
	/**
	 * Initializes the color picker input elements upon first usage.
	 */
	_initColorPickerForm: function() {
		var $form = $('<div id="colorPickerForm" />').appendTo(this._dialog);
		
		// new and current color
		$('<small>' + WCF.Language.get('wcf.style.colorPicker.new') + '</small>').appendTo($form);
		var $colors = $('<ul class="colors" />').appendTo($form);
		this._newColor = $('<li class="new" />').appendTo($colors);
		this._oldColor = $('<li class="old" />').appendTo($colors);
		$('<small>' + WCF.Language.get('wcf.style.colorPicker.current') + '</small>').appendTo($form);
		
		// RGBa input
		var $rgba = $('<ul class="rgba" />').appendTo($form);
		this._createInputElement('r', 'R', 0, 255).appendTo($rgba);
		this._createInputElement('g', 'G', 0, 255).appendTo($rgba);
		this._createInputElement('b', 'B', 0, 255).appendTo($rgba);
		this._createInputElement('a', 'a', 0, 100).appendTo($rgba);
		
		// HEX input
		var $hex = $('<ul class="hex"><li><label><span>#</span></label></li></ul>').appendTo($form);
		this._hex = $('<input type="text" maxlength="6" />').appendTo($hex.find('label'));
		
		// bind event listener
		this._rgba.r.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._rgba.g.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._rgba.b.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._rgba.a.blur($.proxy(this._blurRgba, this)).keyup($.proxy(this._keyUpRGBA, this));
		this._hex.blur($.proxy(this._blurHex, this)).keyup($.proxy(this._keyUpHex, this));
		
		// submit button
		var $submitForm = $('<div class="formSubmit" />').appendTo(this._dialog);
		$('<button class="buttonPrimary">' + WCF.Language.get('wcf.style.colorPicker.button.apply') + '</button>').appendTo($submitForm).click($.proxy(this._submit, this));
		
		// allow pasting of colors like '#888888'
		var self = this;
		this._hex.on('paste', function() {
			self._hex.attr('maxlength', '7');
			
			setTimeout(function() {
				var $value = self._hex.val();
				if ($value.substring(0, 1) == '#') {
					$value = $value.substr(1);
				}
				
				if ($value.length > 6) {
					$value = $value.substring(0, 6);
				}
				
				self._hex.attr('maxlength', '6').val($value);
			}, 50);
		});
	},
	
	/**
	 * Submits form on enter.
	 */
	_keyUpRGBA: function(event) {
		if (event.which == 13) {
			this._blurRgba();
			this._submit();
		}
	},
	
	/**
	 * Submits form on enter.
	 */
	_keyUpHex: function(event) {
		if (event.which == 13) {
			this._blurHex();
			this._submit();
		}
	},
	
	/**
	 * Assigns the new color for active element.
	 */
	_submit: function() {
		var $rgb = this.hsvToRgb(this._hsv.h, this._hsv.s, this._hsv.v);
		
		// create an explicit copy here, otherwise it would be only a reference
		var $hsv = { };
		for (var $type in this._hsv) {
			$hsv[$type] = this._hsv[$type];
		}
		
		var $element = $('#' + this._elementID);
		$element.data('hsv', $hsv).css({ backgroundColor: 'rgb(' + $rgb.r + ', ' + $rgb.g + ', ' + $rgb.b + ')' }).data('alpha', parseInt(this._rgba.a.val()));
		$element.data('rgb', {
			r: this._rgba.r.val(),
			g: this._rgba.g.val(),
			b: this._rgba.b.val()
		});
		$('#' + $element.data('store')).val('rgba(' + this._rgba.r.val() + ', ' + this._rgba.g.val() + ', ' + this._rgba.b.val() + ', ' + (this._rgba.a.val() / 100) + ')').trigger('change');
		
		this._dialog.wcfDialog('close');
	},
	
	/**
	 * Creates an input element.
	 * 
	 * @param	string		type
	 * @param	string		label
	 * @param	integer		min
	 * @param	integer		max
	 * @return	jQuery
	 */
	_createInputElement: function(type, label, min, max) {
		// create elements
		var $listItem = $('<li class="' + type + '" />');
		var $label = $('<label />').appendTo($listItem);
		$('<span>' + label + '</span>').appendTo($label);
		this._rgba[type] = $('<input type="number" value="0" min="' + min + '" max="' + max + '" step="1" />').appendTo($label);
		
		return $listItem;
	},
	
	/**
	 * Handles the mouse down event on the gradient.
	 * 
	 * @param	object		event
	 */
	_mouseDownGradient: function(event) {
		this._gradientActive = true;
		this._mouseGradient(event);
	},
	
	/**
	 * Handles updates of gradient selector position.
	 * 
	 * @param	object		event
	 */
	_mouseGradient: function(event) {
		var $position = this._gradient.getOffsets('offset');
		
		var $left = Math.max(Math.min(event.pageX - $position.left, 255), 0);
		var $top = Math.max(Math.min(event.pageY - $position.top, 255), 0);
		
		// calculate saturation and value
		this._hsv.s = Math.max(0, Math.min(1, $left / 255)) * 100;
		this._hsv.v = Math.max(0, Math.min(1, (255 - $top) / 255)) * 100;
		
		// update color
		this._updateValues(null);
	},
	
	/**
	 * Handles the mouse down event on the bar.
	 * 
	 * @param	object		event
	 */
	_mouseDownBar: function(event) {
		this._barActive = true;
		this._mouseBar(event);
	},
	
	/**
	 * Handles updates of the bar selector position.
	 * 
	 * @param	object		event
	 */
	_mouseBar: function(event) {
		var $position = this._bar.getOffsets('offset');
		var $top = Math.max(Math.min(event.pageY - $position.top, 255), 0);
		this._barSelector.css({ top: $top + 'px' });
		
		// calculate hue
		this._hsv.h = Math.max(0, Math.min(359, Math.round((255 - $top) / 255 * 360)));
		
		// update color
		this._updateValues(null);
	},
	
	/**
	 * Handles changes of RGBa input fields.
	 */
	_blurRgba: function() {
		for (var $type in this._rgba) {
			var $value = parseInt(this._rgba[$type].val()) || 0;
			
			// alpha
			if ($type === 'a') {
				this._rgba[$type].val(Math.max(0, Math.min(100, $value)));
			}
			else {
				// rgb
				this._rgba[$type].val(Math.max(0, Math.min(255, $value)));
			}
		}
		
		this._updateValues({
			r: this._rgba.r.val(),
			g: this._rgba.g.val(),
			b: this._rgba.b.val()
		}, true, true);
	},
	
	/**
	 * Handles change of HEX value.
	 */
	_blurHex: function() {
		var $value = this.hexToRgb(this._hex.val());
		if ($value !== Number.NaN) {
			this._updateValues($value, true, true);
		}
	},
	
	/**
	 * Updates the values of all elements, including color picker and
	 * input elements. Argument 'rgb' may be null.
	 * 
	 * @param	object		rgb
	 * @param	boolean		changeH
	 * @param	boolean		changeSV
	 */
	_updateValues: function(rgb, changeH, changeSV) {
		changeH = (changeH === true) ? true : false;
		changeSV = (changeSV === true) ? true : false;
		
		// calculate RGB values from HSV
		if (rgb === null) {
			rgb = this.hsvToRgb(this._hsv.h, this._hsv.s, this._hsv.v);
		}
		
		// add alpha channel
		if (rgb.a === undefined) {
			rgb.a = this._rgba.a.val();
		}
		
		// adjust RGBa input
		for (var $type in rgb) {
			this._rgba[$type].val(rgb[$type]);
		}
		
		// set hex input
		this._hex.val(this.rgbToHex(rgb.r, rgb.g, rgb.b));
		
		// calculate HSV to adjust selectors
		if (changeH || changeSV) {
			var $hsv = this.rgbToHsv(rgb.r, rgb.g, rgb.b);
			
			// adjust hue
			if (changeH) {
				this._hsv.h = $hsv.h;
			}
			
			// adjust saturation and value
			if (changeSV) {
				this._hsv.s = $hsv.s;
				this._hsv.v = $hsv.v;
			}
		}
		
		// adjust bar selector
		var $top = Math.max(0, Math.min(255, 255 - (this._hsv.h / 360) * 255));
		this._barSelector.css({ top: $top + 'px' });
		
		// adjust gradient selector
		var $left = Math.max(0, Math.min(255, (this._hsv.s / 100) * 255));
		var $top = Math.max(0, Math.min(255, 255 - ((this._hsv.v / 100) * 255)));
		this._gradientSelector.css({
			left: ($left - 6) + 'px',
			top: ($top - 6) + 'px'
		});
				
		// update 'new' color
		this._newColor.css({ backgroundColor: 'rgb(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ')' });
		
		// adjust gradient color
		var $rgb = this.hsvToRgb(this._hsv.h, 100, 100);
		this._gradient.css({ backgroundColor: 'rgb(' + $rgb.r + ', ' + $rgb.g + ', ' + $rgb.b + ')' });
	},
	
	/**
	 * Converts a HSV color into RGB.
	 * 
	 * @see	https://secure.wikimedia.org/wikipedia/de/wiki/HSV-Farbraum#Transformation_von_RGB_und_HSV
	 * 
	 * @param	integer		h
	 * @param	integer		s
	 * @param	integer		v
	 * @return	object
	 */
	hsvToRgb: function(h, s, v) {
		var $rgb = { r: 0, g: 0, b: 0 };
		var $h, $f, $p, $q, $t;
		
		$h = Math.floor(h / 60);
		$f = h / 60 - $h;
		
		s /= 100;
		v /= 100;
		
		$p = v * (1 - s);
		$q = v * (1 - s * $f);
		$t = v * (1 - s * (1 - $f));
		
		if (s == 0) {
			$rgb.r = $rgb.g = $rgb.b = v;
		}
		else {
			switch ($h) {
				case 1:
					$rgb.r = $q;
					$rgb.g = v;
					$rgb.b = $p;
				break;
				
				case 2:
					$rgb.r = $p;
					$rgb.g = v;
					$rgb.b = $t;
				break;
				
				case 3:
					$rgb.r = $p;
					$rgb.g = $q;
					$rgb.b = v;
				break;
				
				case 4:
					$rgb.r = $t;
					$rgb.g = $p;
					$rgb.b = v;
				break;
				
				case 5:
					$rgb.r = v;
					$rgb.g = $p;
					$rgb.b = $q;
				break;
				
				case 0:
				case 6:
					$rgb.r = v;
					$rgb.g = $t;
					$rgb.b = $p;
				break;
			}
		}
		
		return {
			r: Math.round($rgb.r * 255),
			g: Math.round($rgb.g * 255),
			b: Math.round($rgb.b * 255)
		};
	},
	
	/**
	 * Converts a RGB color into HSV.
	 * 
	 * @see	https://secure.wikimedia.org/wikipedia/de/wiki/HSV-Farbraum#Transformation_von_RGB_und_HSV
	 * 
	 * @param	integer		r
	 * @param	integer		g
	 * @param	integer		b
	 * @return	object
	 */
	rgbToHsv: function(r, g, b) {
		var $h, $s, $v;
		var $max, $min, $diff;
		
		r /= 255;
		g /= 255;
		b /= 255;
		
		$max = Math.max(Math.max(r, g), b);
		$min = Math.min(Math.min(r, g), b);
		$diff = $max - $min;
		
		$h = 0;
		if ($max !== $min) {
			switch ($max) {
				case r:
					$h = 60 * (0 + (g - b) / $diff);
				break;
				
				case g:
					$h = 60 * (2 + (b - r) / $diff);
				break;
				
				case b:
					$h = 60 * (4 + (r - g) / $diff);
				break;
			}
			
			if ($h < 0) {
				$h += 360;
			}
		}
		
		if ($max === 0) {
			$s = 0;
		}
		else {
			$s = $diff / $max;
		}
		
		$v = $max;
		
		return {
			h: Math.round($h),
			s: Math.round($s * 100),
			v: Math.round($v * 100)
		};
	},
	
	/**
	 * Converts HEX into RGB.
	 * 
	 * @param	string		hex
	 * @return	object
	 */
	hexToRgb: function(hex) {
		if (/^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(hex)) {
			// only convert #abc and #abcdef
			hex = hex.split('');
			
			// drop the hashtag
			if (hex[0] === '#') {
				hex.shift();
			}
			
			// parse shorthand #xyz
			if (hex.length === 3) {
				return {
					r: parseInt(hex[0] + '' + hex[0], 16),
					g: parseInt(hex[1] + '' + hex[1], 16),
					b: parseInt(hex[2] + '' + hex[2], 16)
				};
			}
			else {
				return {
					r: parseInt(hex[0] + '' + hex[1], 16),
					g: parseInt(hex[2] + '' + hex[3], 16),
					b: parseInt(hex[4] + '' + hex[5], 16)
				};
			}
		}
		
		return Number.NaN;
	},
	
	/**
	 * Converts a RGB into HEX.
	 * 
	 * @see	http://www.linuxtopia.org/online_books/javascript_guides/javascript_faq/rgbtohex.htm
	 * 
	 * @param	integer		r
	 * @param	integer		g
	 * @param	integer		b
	 * @return	string
	 */
	rgbToHex: function(r, g, b) {
		return ("0123456789ABCDEF".charAt((r - r % 16) / 16) + '' + "0123456789ABCDEF".charAt(r % 16)) + '' + ("0123456789ABCDEF".charAt((g - g % 16) / 16) + '' + "0123456789ABCDEF".charAt(g % 16)) + '' + ("0123456789ABCDEF".charAt((b - b % 16) / 16) + '' + "0123456789ABCDEF".charAt(b % 16));
	}
});

// WCF.Comment.js
/**
 * Namespace for comments
 */
WCF.Comment = { };

/**
 * Comment support for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Comment.Handler = Class.extend({
	/**
	 * input element to add a comment
	 * @var	jQuery
	 */
	_commentAdd: null,
	
	/**
	 * list of comment buttons per comment
	 * @var	object
	 */
	_commentButtonList: { },
	
	/**
	 * list of comment objects
	 * @var	object
	 */
	_comments: { },
	
	/**
	 * comment container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * container id
	 * @var	string
	 */
	_containerID: '',
	
	/**
	 * number of currently displayed comments
	 * @var	integer
	 */
	_displayedComments: 0,
	
	/**
	 * button to load next comments
	 * @var	jQuery
	 */
	_loadNextComments: null,
	
	/**
	 * buttons to load next responses per comment
	 * @var	object
	 */
	_loadNextResponses: { },
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of response objects
	 * @var	object
	 */
	_responses: { },
	
	/**
	 * user's avatar
	 * @var	string
	 */
	_userAvatar: '',
	
	/**
	 * data of the comment the active guest user is about to create
	 * @var	object
	 */
	_commentData: { },
	
	/**
	 * guest dialog with username input field and recaptcha
	 * @var	jQuery
	 */
	_guestDialog: null,

	/**
	 * true if the guest has to solve a recaptcha challenge to save the comment
	 * @var	boolean
	 */
	_useRecaptcha: true,
	
	/**
	 * Initializes the WCF.Comment.Handler class.
	 * 
	 * @param	string		containerID
	 * @param	string		userAvatar
	 */
	init: function(containerID, userAvatar) {
		this._commentAdd = null;
		this._commentButtonList = { };
		this._comments = { };
		this._containerID = containerID;
		this._displayedComments = 0;
		this._loadNextComments = null;
		this._loadNextResponses = { };
		this._responses = { };
		this._userAvatar = userAvatar;
		
		this._container = $('#' + $.wcfEscapeID(this._containerID));
		if (!this._container.length) {
			console.debug("[WCF.Comment.Handler] Unable to find container identified by '" + this._containerID + "'");
		}
		
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			success: $.proxy(this._success, this)
		});
		
		this._initComments();
		this._initResponses();
		
		// add new comment
		if (this._container.data('canAdd')) {
			this._initAddComment();
		}
		
		WCF.DOMNodeInsertedHandler.execute();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Comment.Handler', $.proxy(this._domNodeInserted, this));
	},
	
	/**
	 * Shows a button to load next comments.
	 */
	_handleLoadNextComments: function() {
		if (this._displayedComments < this._container.data('comments')) {
			if (this._loadNextComments === null) {
				this._loadNextComments = $('<li class="commentLoadNext"><button class="small">' + WCF.Language.get('wcf.comment.more') + '</button></li>').appendTo(this._container);
				this._loadNextComments.children('button').click($.proxy(this._loadComments, this));
			}
			
			this._loadNextComments.children('button').enable();
		}
		else if (this._loadNextComments !== null) {
			this._loadNextComments.hide();
		}
	},
	
	/**
	 * Shows a button to load next responses per comment.
	 * 
	 * @param	integer		commentID
	 */
	_handleLoadNextResponses: function(commentID) {
		var $comment = this._comments[commentID];
		$comment.data('displayedResponses', $comment.find('ul.commentResponseList > li').length);
		
		if ($comment.data('displayedResponses') < $comment.data('responses')) {
			if (this._loadNextResponses[commentID] === undefined) {
				var $difference = $comment.data('responses') - $comment.data('displayedResponses');
				this._loadNextResponses[commentID] = $('<li class="jsCommentLoadNextResponses"><a>' + WCF.Language.get('wcf.comment.response.more', { count: $difference }) + '</a></li>').appendTo(this._commentButtonList[commentID]);
				this._loadNextResponses[commentID].children('a').data('commentID', commentID).click($.proxy(this._loadResponses, this));
				this._commentButtonList[commentID].parent().show();
			}
		}
		else if (this._loadNextResponses[commentID] !== undefined) {
			var $showAddResponse = this._loadNextResponses[commentID].next();
			this._loadNextResponses[commentID].remove();
			if ($showAddResponse.length) {
				$showAddResponse.trigger('click');
			}
		}
	},
	
	/**
	 * Loads next comments.
	 */
	_loadComments: function() {
		this._loadNextComments.children('button').disable();
		
		this._proxy.setOption('data', {
			actionName: 'loadComments',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: {
					objectID: this._container.data('objectID'),
					objectTypeID: this._container.data('objectTypeID'),
					lastCommentTime: this._container.data('lastCommentTime')
				}
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Loads next responses for given comment.
	 * 
	 * @param	object		event
	 */
	_loadResponses: function(event) {
		this._loadResponsesExecute($(event.currentTarget).disable().data('commentID'), false);
		
	},
	
	/**
	 * Executes loading of comments, optionally fetching all at once.
	 * 
	 * @param	integer		commentID
	 * @param	boolean		loadAllResponses
	 */
	_loadResponsesExecute: function(commentID, loadAllResponses) {
		this._proxy.setOption('data', {
			actionName: 'loadResponses',
			className: 'wcf\\data\\comment\\response\\CommentResponseAction',
			parameters: {
				data: {
					commentID: commentID,
					lastResponseTime: this._comments[commentID].data('lastResponseTime'),
					loadAllResponses: (loadAllResponses ? 1 : 0)
				}
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles DOMNodeInserted events.
	 */
	_domNodeInserted: function() {
		this._initComments();
		this._initResponses();
	},
	
	/**
	 * Initializes available comments.
	 */
	_initComments: function() {
		var self = this;
		var $loadedComments = false;
		this._container.find('.jsComment').each(function(index, comment) {
			var $comment = $(comment).removeClass('jsComment');
			var $commentID = $comment.data('commentID');
			self._comments[$commentID] = $comment;
			
			var $insertAfter = $comment.find('ul.commentResponseList');
			if (!$insertAfter.length) $insertAfter = $comment.find('.commentContent');
			
			$container = $('<div class="commentOptionContainer" />').hide().insertAfter($insertAfter);
			self._commentButtonList[$commentID] = $('<ul />').appendTo($container);
			
			self._handleLoadNextResponses($commentID);
			self._initComment($commentID, $comment);
			self._displayedComments++;
			
			$loadedComments = true;
		});
		
		if ($loadedComments) {
			this._handleLoadNextComments();
		}
	},
	
	/**
	 * Initializes a specific comment.
	 * 
	 * @param	integer		commentID
	 * @param	jQuery		comment
	 */
	_initComment: function(commentID, comment) {
		if (this._container.data('canAdd')) {
			this._initAddResponse(commentID, comment);
		}
		
		if (comment.data('canEdit')) {
			var $editButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.edit') + '"><span class="icon icon16 icon-pencil" /> <span class="invisible">' + WCF.Language.get('wcf.global.button.edit') + '</span></a></li>');
			$editButton.data('commentID', commentID).appendTo(comment.find('ul.commentOptions:eq(0)')).click($.proxy(this._prepareEdit, this));
		}
		
		if (comment.data('canDelete')) {
			var $deleteButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.delete') + '"><span class="icon icon16 icon-remove" /> <span class="invisible">' + WCF.Language.get('wcf.global.button.delete') + '</span></a></li>');
			$deleteButton.data('commentID', commentID).appendTo(comment.find('ul.commentOptions:eq(0)')).click($.proxy(this._delete, this));
		}
	},
	
	/**
	 * Initializes available responses.
	 */
	_initResponses: function() {
		var self = this;
		this._container.find('.jsCommentResponse').each(function(index, response) {
			var $response = $(response).removeClass('jsCommentResponse');
			var $responseID = $response.data('responseID');
			self._responses[$responseID] = $response;
			
			self._initResponse($responseID, $response);
		});
	},
	
	/**
	 * Initializes a specific response.
	 * 
	 * @param	integer		responseID
	 * @param	jQuery		response
	 */
	_initResponse: function(responseID, response) {
		if (response.data('canEdit')) {
			var $editButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.edit') + '"><span class="icon icon16 icon-pencil" /> <span class="invisible">' + WCF.Language.get('wcf.global.button.edit') + '</span></a></li>');
			
			var self = this;
			$editButton.data('responseID', responseID).appendTo(response.find('ul.commentOptions:eq(0)')).click(function(event) { self._prepareEdit(event, true); });
		}
		
		if (response.data('canDelete')) {
			var $deleteButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.delete') + '"><span class="icon icon16 icon-remove" /> <span class="invisible">' + WCF.Language.get('wcf.global.button.delete') + '</span></a></li>');
			
			var self = this;
			$deleteButton.data('responseID', responseID).appendTo(response.find('ul.commentOptions:eq(0)')).click(function(event) { self._delete(event, true); });
		}
	},
	
	/**
	 * Initializes the UI components to add a comment.
	 */
	_initAddComment: function() {
		// create UI
		this._commentAdd = $('<li class="box32 jsCommentAdd"><span class="framed">' + this._userAvatar + '</span><div /></li>').prependTo(this._container);
		var $inputContainer = this._commentAdd.children('div');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.add') + '" maxlength="65535" class="long" />').appendTo($inputContainer);
		$('<small>' + WCF.Language.get('wcf.comment.description') + '</small>').appendTo($inputContainer);
		
		$input.keyup($.proxy(this._keyUp, this));
	},
	
	/**
	 * Initializes the UI elements to add a response.
	 * 
	 * @param	integer		commentID
	 * @param	jQuery		comment
	 */
	_initAddResponse: function(commentID, comment) {
		var $placeholder = null;
		if (!comment.data('responses') || this._loadNextResponses[commentID]) {
			$placeholder = $('<li class="jsCommentShowAddResponse"><a>' + WCF.Language.get('wcf.comment.button.response.add') + '</a></li>').data('commentID', commentID).click($.proxy(this._showAddResponse, this)).appendTo(this._commentButtonList[commentID]);
		}
		
		var $listItem = $('<div class="box32 commentResponseAdd jsCommentResponseAdd"><span class="framed">' + this._userAvatar + '</span><div /></div>');
		if ($placeholder !== null) {
			$listItem.hide();
		}
		else {
			this._commentButtonList[commentID].parent().addClass('jsAddResponseActive');
		}
		$listItem.appendTo(this._commentButtonList[commentID].parent().show());
		
		var $inputContainer = $listItem.children('div');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.response.add') + '" maxlength="65535" class="long" />').data('commentID', commentID).appendTo($inputContainer);
		$('<small>' + WCF.Language.get('wcf.comment.description') + '</small>').appendTo($inputContainer);
		
		var self = this;
		$input.keyup(function(event) { self._keyUp(event, true); });
		
		comment.data('responsePlaceholder', $placeholder).data('responseInput', $listItem);
	},
	
	/**
	 * Prepares editing of a comment or response.
	 * 
	 * @param	object		event
	 * @param	boolean		isResponse
	 */
	_prepareEdit: function(event, isResponse) {
		var $button = $(event.currentTarget);
		var $data = {
			objectID: this._container.data('objectID'),
			objectTypeID: this._container.data('objectTypeID')
		};
		
		if (isResponse === true) {
			$data.responseID = $button.data('responseID');
		}
		else {
			$data.commentID = $button.data('commentID');
		}
		
		this._proxy.setOption('data', {
			actionName: 'prepareEdit',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: $data
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Displays the UI elements to create a response.
	 * 
	 * @param	object		event
	 */
	_showAddResponse: function(event) {
		var $placeholder = $(event.currentTarget);
		var $commentID = $placeholder.data('commentID');
		if ($placeholder.prev().hasClass('jsCommentLoadNextResponses')) {
			this._loadResponsesExecute($commentID, true);
			$placeholder.parent().children('.button').disable();
		}
		
		$placeholder.remove();
		
		var $responseInput = this._comments[$commentID].data('responseInput').show();
		$responseInput.find('input').focus();
		
		$responseInput.parents('.commentOptionContainer').addClass('jsAddResponseActive');
	},
	
	/**
	 * Handles the keyup event for comments and responses.
	 * 
	 * @param	object		event
	 * @param	boolean		isResponse
	 */
	_keyUp: function(event, isResponse) {
		// ignore every key except for [Enter] and [Esc]
		if (event.which !== 13 && event.which !== 27) {
			return;
		}
		
		var $input = $(event.currentTarget);
		
		// cancel input
		if (event.which === 27) {
			$input.val('').trigger('blur', event);
			return;
		}
		
		var $value = $.trim($input.val());
		
		// ignore empty comments
		if ($value == '') {
			return;
		}
		
		var $actionName = 'addComment';
		var $data = {
			message: $value,
			objectID: this._container.data('objectID'),
			objectTypeID: this._container.data('objectTypeID')
		};
		if (isResponse === true) {
			$actionName = 'addResponse';
			$data.commentID = $input.data('commentID');
		}
		
		if (!WCF.User.userID) {
			this._commentData = $data;
			
			// check if guest dialog has already been loaded
			if (this._guestDialog === null) {
				this._proxy.setOption('data', {
					actionName: 'getGuestDialog',
					className: 'wcf\\data\\comment\\CommentAction',
					parameters: {
						data: {
							message: $value,
							objectID: this._container.data('objectID'),
							objectTypeID: this._container.data('objectTypeID')
						}
					}
				});
				this._proxy.sendRequest();
			}
			else {
				// request a new recaptcha
				if (this._useRecaptcha) {
					Recaptcha.reload();
				}
				
				this._guestDialog.find('input[type="submit"]').enable();
				
				this._guestDialog.wcfDialog('open');
			}
		}
		else {
			this._proxy.setOption('data', {
				actionName: $actionName,
				className: 'wcf\\data\\comment\\CommentAction',
				parameters: {
					data: $data
				}
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Shows a confirmation message prior to comment or response deletion.
	 * 
	 * @param	object		event
	 * @param	boolean		isResponse
	 */
	_delete: function(event, isResponse) {
		WCF.System.Confirmation.show(WCF.Language.get('wcf.comment.delete.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				var $data = {
					objectID: this._container.data('objectID'),
					objectTypeID: this._container.data('objectTypeID')
				};
				if (isResponse !== true) {
					$data.commentID = $(event.currentTarget).data('commentID');
				}
				else {
					$data.responseID = $(event.currentTarget).data('responseID');
				}
				
				this._proxy.setOption('data', {
					actionName: 'remove',
					className: 'wcf\\data\\comment\\CommentAction',
					parameters: {
						data: $data
					}
				});
				this._proxy.sendRequest();
			}
		}, this));
	},
	
	/**
	 * Handles a failed AJAX request.
	 * 
	 * @param	object		data
	 * @param	object		jqXHR
	 * @param	string		textStatus
	 * @param	string		errorThrown
	 * @return	boolean
	 */
	_failure: function(data, jqXHR, textStatus, errorThrown) {
		if (!WCF.User.userID && this._guestDialog) {
			// enable submit button again
			this._guestDialog.find('input[type="submit"]').enable();
		}
		
		return true;
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.actionName) {
			case 'addComment':
				if (data.returnValues.errors) {
					this._handleGuestDialogErrors(data.returnValues.errors);
				}
				else {
					this._commentAdd.find('input').val('').blur();
					$(data.returnValues.template).insertAfter(this._commentAdd).wcfFadeIn();
					
					if (!WCF.User.userID) {
						this._guestDialog.wcfDialog('close');
					}
				}
			break;
			
			case 'addResponse':
				if (data.returnValues.errors) {
					this._handleGuestDialogErrors(data.returnValues.errors);
				}
				else {
					var $comment = this._comments[data.returnValues.commentID];
					$comment.find('.jsCommentResponseAdd input').val('').blur();
					
					var $responseList = $comment.find('ul.commentResponseList');
					if (!$responseList.length) $responseList = $('<ul class="commentResponseList" />').insertBefore($comment.find('.commentOptionContainer'));
					$(data.returnValues.template).appendTo($responseList).wcfFadeIn();
				}
				
				if (!WCF.User.userID) {
					this._guestDialog.wcfDialog('close');
				}
			break;
			
			case 'edit':
				this._update(data);
			break;
			
			case 'loadComments':
				this._insertComments(data);
			break;
			
			case 'loadResponses':
				this._insertResponses(data);
			break;
			
			case 'prepareEdit':
				this._edit(data);
			break;
			
			case 'remove':
				this._remove(data);
			break;
			
			case 'getGuestDialog':
				this._createGuestDialog(data);
			break;
		}
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Inserts previously loaded comments.
	 * 
	 * @param	object		data
	 */
	_insertComments: function(data) {
		// insert comments
		$(data.returnValues.template).insertBefore(this._loadNextComments);
		
		// update time of last comment
		this._container.data('lastCommentTime', data.returnValues.lastCommentTime);
	},
	
	/**
	 * Inserts previously loaded responses.
	 * 
	 * @param	object		data
	 */
	_insertResponses: function(data) {
		var $comment = this._comments[data.returnValues.commentID];
		
		// insert responses
		$(data.returnValues.template).appendTo($comment.find('ul.commentResponseList'));
		
		// update time of last response
		$comment.data('lastResponseTime', data.returnValues.lastResponseTime);
		
		// update button state to load next responses
		this._handleLoadNextResponses(data.returnValues.commentID);
	},
	
	/**
	 * Removes a comment or response from list.
	 * 
	 * @param	object		data
	 */
	_remove: function(data) {
		if (data.returnValues.commentID) {
			this._comments[data.returnValues.commentID].remove();
			delete this._comments[data.returnValues.commentID];
		}
		else {
			this._responses[data.returnValues.responseID].remove();
			delete this._responses[data.returnValues.responseID];
		}
	},
	
	/**
	 * Prepares editing of a comment or response.
	 * 
	 * @param	object		data
	 */
	_edit: function(data) {
		if (data.returnValues.commentID) {
			var $content = this._comments[data.returnValues.commentID].find('.commentContent:eq(0) .userMessage:eq(0)');
		}
		else {
			var $content = this._responses[data.returnValues.responseID].find('.commentContent:eq(0) .userMessage:eq(0)');
		}
		
		// replace content with input field
		$content.html($.proxy(function(index, oldHTML) {
			var $input = $('<input type="text" class="long" maxlength="65535" /><small>' + WCF.Language.get('wcf.comment.description') + '</small>').val(data.returnValues.message);
			$input.data('__html', oldHTML).keyup($.proxy(this._saveEdit, this));
			
			if (data.returnValues.commentID) {
				$input.data('commentID', data.returnValues.commentID);
			}
			else {
				$input.data('responseID', data.returnValues.responseID);
			}
			
			return $input;
		}, this));
		$content.children('input').focus();
		
		// hide elements
		$content.parent().find('.containerHeadline:eq(0)').hide();
		$content.parent().find('.buttonGroupNavigation:eq(0)').hide();
	},
	
	/**
	 * Updates a comment or response.
	 * 
	 * @param	object		data
	 */
	_update: function(data) {
		if (data.returnValues.commentID) {
			var $input = this._comments[data.returnValues.commentID].find('.commentContent:eq(0) .userMessage:eq(0) > input');
		}
		else {
			var $input = this._responses[data.returnValues.responseID].find('.commentContent:eq(0) .userMessage:eq(0) > input');
		}
		
		$input.data('__html', data.returnValues.message);
		
		this._cancelEdit($input);
	},
	
	/**
	 * Creates the guest dialog based on the given return data from the AJAX
	 * request.
	 * 
	 * @param	object		data
	 */
	_createGuestDialog: function(data) {
		this._guestDialog = $('<div id="commentAddGuestDialog" />').append(data.returnValues.template).hide().appendTo(document.body);
		
		// bind submit event listeners
		this._guestDialog.find('input[type="submit"]').click($.proxy(this._submit, this));

		this._guestDialog.find('input[type="text"]').keydown($.proxy(this._keyDown, this));

		// check if recaptcha is used
		this._useRecaptcha = this._guestDialog.find('dl.reCaptcha').length > 0;
		
		this._guestDialog.wcfDialog({
			'title': WCF.Language.get('wcf.comment.guestDialog.title')
		});
	},

	/**
	 * Handles clicking enter in the input fields of the guest dialog by
	 * submitting it.
	 * 
	 * @param	Event		event
	 */
	_keyDown: function(event) {
		if (event.which === $.ui.keyCode.ENTER) {
			this._submit();
		}
	},

	/**
	 * Handles errors during creation of a comment or response due to the input
	 * in the guest dialog.
	 * 
	 * @param	object		errors
	 */
	_handleGuestDialogErrors: function(errors) {
		if (errors.username) {
			var $usernameInput = this._guestDialog.find('input[name="username"]');
			var $errorMessage = $usernameInput.next('.innerError');
			if (!$errorMessage.length) {
				$errorMessage = $('<small class="innerError" />').text(errors.username).insertAfter($usernameInput);
			}
			else {
				$errorMessage.text(errors.username).show();
			}
		}
		
		if (errors.recaptcha) {
			Recaptcha.reload();

			var $recaptchaInput = this._guestDialog.find('input[name="recaptcha_response_field"]');
			var $errorMessage = $recaptchaInput.next('.innerError');
			if (!$errorMessage.length) {
				$errorMessage = $('<small class="innerError" />').text(errors.recaptcha).insertAfter($recaptchaInput);
			}
			else {
				$errorMessage.text(errors.recaptcha).show();
			}
		}

		this._guestDialog.find('input[type="submit"]').enable();
	},
	
	/**
	 * Handles submitting the guest dialog.
	 * 
	 * @param	Event		event
	 */
	_submit: function(event) {
		var $submit = true;

		this._guestDialog.find('input[type="submit"]').enable();

		// validate username
		var $usernameInput = this._guestDialog.find('input[name="username"]');
		var $username = $usernameInput.val();
		var $usernameErrorMessage = $usernameInput.next('.innerError');
		if (!$username) {
			$submit = false;
			if (!$usernameErrorMessage.length) {
				$usernameErrorMessage = $('<small class="innerError" />').text(WCF.Language.get('wcf.global.form.error.empty')).insertAfter($usernameInput);
			}
			else {
				$usernameErrorMessage.text(WCF.Language.get('wcf.global.form.error.empty')).show();
			}
		}

		// validate recaptcha
		if (this._useRecaptcha) {
			var $recaptchaInput = this._guestDialog.find('input[name="recaptcha_response_field"]');
			var $recaptchaResponse = $recaptchaInput.val();
			var $recaptchaErrorMessage = $recaptchaInput.next('.innerError');
			if (!$recaptchaResponse) {
				$submit = false;
				if (!$recaptchaErrorMessage.length) {
					$recaptchaErrorMessage = $('<small class="innerError" />').text(WCF.Language.get('wcf.global.form.error.empty')).insertAfter($recaptchaInput);
				}
				else {
					$recaptchaErrorMessage.text(WCF.Language.get('wcf.global.form.error.empty')).show();
				}
			}
		}

		if ($submit) {
			if ($usernameErrorMessage.length) {
				$usernameErrorMessage.hide();
			}

			if (this._useRecaptcha && $recaptchaErrorMessage.length) {
				$recaptchaErrorMessage.hide();
			}

			var $data = this._commentData;
			$data.username = $username;

			var $parameters = {
				data: $data
			};

			if (this._useRecaptcha) {
				$parameters.recaptchaChallenge = Recaptcha.get_challenge();
				$parameters.recaptchaResponse = Recaptcha.get_response();
			}
			
			this._proxy.setOption('data', {
				actionName: this._commentData.commentID ? 'addResponse' : 'addComment',
				className: 'wcf\\data\\comment\\CommentAction',
				parameters: $parameters
			});
			this._proxy.sendRequest();

			this._guestDialog.find('input[type="submit"]').disable();
		}
	},
	
	/**
	 * Saves editing of a comment or response.
	 * 
	 * @param	object		event
	 */
	_saveEdit: function(event) {
		var $input = $(event.currentTarget);
		
		// abort with [Esc]
		if (event.which === 27) {
			this._cancelEdit($input);
			return;
		}
		else if (event.which !== 13) {
			// ignore everything except for [Enter]
			return;
		}
		
		var $message = $.trim($input.val());
		
		// ignore empty message
		if ($message === '') {
			return;
		}
		
		var $data = {
			message: $message,
			objectID: this._container.data('objectID'),
			objectTypeID: this._container.data('objectTypeID')
		};
		if ($input.data('commentID')) {
			$data.commentID = $input.data('commentID');
		}
		else {
			$data.responseID = $input.data('responseID');
		}
		
		this._proxy.setOption('data', {
			actionName: 'edit',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: $data
			}
		});
		this._proxy.sendRequest()
	},
	
	/**
	 * Cancels editing of a comment or response.
	 * 
	 * @param	jQuery		input
	 */
	_cancelEdit: function(input) {
		// restore elements
		input.parent().prev('.containerHeadline:eq(0)').show();
		input.parent().next('.buttonGroupNavigation:eq(0)').show();
		
		// restore HTML
		input.parent().html(input.data('__html'));
	}
});

/**
 * Like support for comments
 * 
 * @see	WCF.Like
 */
WCF.Comment.Like = WCF.Like.extend({
	/**
	 * @see	WCF.Like._getContainers()
	 */
	_getContainers: function() {
		return $('.commentList > li.comment');
	},
	
	/**
	 * @see	WCF.Like._getObjectID()
	 */
	_getObjectID: function(containerID) {
		return this._containers[containerID].data('commentID');
	},
	
	/**
	 * @see	WCF.Like._buildWidget()
	 */
	_buildWidget: function(containerID, likeButton, dislikeButton, badge, summary) {
		this._containers[containerID].find('.containerHeadline:eq(0) > h3').append(badge);
		
		if (this._canLike) {
			likeButton.appendTo(this._containers[containerID].find('.commentOptions:eq(0)'));
			dislikeButton.appendTo(this._containers[containerID].find('.commentOptions:eq(0)'));
		}
	},
	
	/**
	 * @see	WCF.Like._getWidgetContainer()
	 */
	_getWidgetContainer: function(containerID) {},
	
	/**
	 * @see	WCF.Like._addWidget()
	 */
	_addWidget: function(containerID, widget) {}
});

/**
 * Namespace for comment responses
 */
WCF.Comment.Response = { };

/**
 * Like support for comments responses.
 * 
 * @see	WCF.Like
 */
WCF.Comment.Response.Like = WCF.Like.extend({
	/**
	 * @see	WCF.Like._addWidget()
	 */
	_addWidget: function(containerID, widget) { },
	
	/**
	 * @see	WCF.Like._buildWidget()
	 */
	_buildWidget: function(containerID, likeButton, dislikeButton, badge, summary) {
		this._containers[containerID].find('.containerHeadline:eq(0) > h3').append(badge);
		
		if (this._canLike) {
			likeButton.appendTo(this._containers[containerID].find('.commentOptions:eq(0)'));
			dislikeButton.appendTo(this._containers[containerID].find('.commentOptions:eq(0)'));
		}
	},
	
	/**
	 * @see	WCF.Like._getContainers()
	 */
	_getContainers: function() {
		return $('.commentResponseList > li.commentResponse');
	},
	
	/**
	 * @see	WCF.Like._getObjectID()
	 */
	_getObjectID: function(containerID) {
		return this._containers[containerID].data('responseID');
	},
	
	/**
	 * @see	WCF.Like._getWidgetContainer()
	 */
	_getWidgetContainer: function(containerID) { }
});


// WCF.ImageViewer.js
/**
 * ImageViewer for WCF.
 * Based upon "Slimbox 2" by Christophe Beyls 2007-2012, http://www.digitalia.be/software/slimbox2, MIT-style license.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ImageViewer = Class.extend({
	/**
	 * Initializes the ImageViewer for every a-tag with the attribute rel = imageviewer.
	 */
	init: function() {
		// navigation buttons
		$('<span class="icon icon16 icon-chevron-left jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.previous') + '" />').appendTo($('#lbPrevLink'));
		$('<span class="icon icon16 icon-chevron-right jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.next') + '" />').appendTo($('#lbNextLink'));
		
		// close and enlarge icons
		$('<span class="icon icon32 icon-remove jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.close') + '" />').appendTo($('#lbCloseLink'));
		var $buttonEnlarge = $('<span class="icon icon32 icon-resize-full jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.enlarge') + '" id="lbEnlarge" />').insertAfter($('#lbCloseLink'));
		
		// handle enlarge button
		$buttonEnlarge.click($.proxy(this._enlarge, this));
		
		this._initImageViewer();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.ImageViewer', $.proxy(this._domNodeInserted, this));
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Executes actions upon DOMNodeInserted events.
	 */
	_domNodeInserted: function() {
		this._initImageSizeCheck();
		this._initImageViewer();
	},
	
	/**
	 * Initializes the image viewer for all links with class ".jsImageViewer"
	 */
	_initImageViewer: function() {
		// disable ImageViewer on touch devices identifying themselves as 'mobile'
		if ($.browser.touch && /[Mm]obile/.test(navigator.userAgent)) {
			// Apple always appends mobile regardless if it is an iPad or iP(hone|od)
			if (!/iPad/.test(navigator.userAgent)) {
				return;
			}
		}
		
		var $links = $('a.jsImageViewer');
		if ($links.length) {
			$links.removeClass('jsImageViewer').slimbox({
				counterText: WCF.Language.get('wcf.imageViewer.counter'),
				loop: true
			});
		}
	},
	
	/**
	 * Redirects to image for full view.
	 */
	_enlarge: function() {
		var $url = $('#lbImage').css('backgroundImage');
		if ($url) {
			$url = $url.replace(/^url\((["']?)(.*)\1\)$/, '$2');
			window.location = $url;
		}
	},
	
	/**
	 * Initializes the image size check.
	 */
	_initImageSizeCheck: function() {
		$('.jsResizeImage').each($.proxy(function(index, image) {
			if (image.complete) this._checkImageSize({ currentTarget: image });
		}, this));
		
		$('.jsResizeImage').on('load', $.proxy(this._checkImageSize, this));
	},
	
	/**
	 * Checks the image size.
	 */
	_checkImageSize: function(event) {
		var $image = $(event.currentTarget);
		if (!$image.is(':visible')) {
			$image.off('load');
			
			return;
		}
		
		$image.removeClass('jsResizeImage');
		var $dimensions = $image.getDimensions();
		var $maxWidth = $image.parents('div').innerWidth();
		
		if ($dimensions.width > $maxWidth) {
			$image.css({
				height: Math.round($dimensions.height * ($maxWidth / $dimensions.width)) + 'px',
				width: $maxWidth + 'px'
			});
			
			if (!$image.parents('a').length) {
				$image.wrap('<a href="' + $image.attr('src') + '" />');
				$image.parent().slimbox();
			}
		}
	}
});

/**
 * Provides a focused image viewer for WCF.
 * 
 * Usage:
 * $('.triggerElement').wcfImageViewer({
 * 	shiftBy: 5,
 * 	
 * 	enableSlideshow: 1,
 * 	speed: 5,
 * 	
 * 	className: 'wcf\\data\\foo\\FooAction'
 * });
 */
$.widget('ui.wcfImageViewer', {
	/**
	 * active image index
	 * @var	integer
	 */
	_active: -1,
	
	/**
	 * active image object id
	 * @var	integer
	 */
	_activeImage: null,
	
	/**
	 * image viewer container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * overrides slideshow settings unless explicitly enabled by user
	 * @var	boolean
	 */
	_disableSlideshow: false,
	
	/**
	 * event namespace used to distinguish event handlers using $.proxy
	 * @var	string
	 */
	_eventNamespace: '',
	
	/**
	 * list of available images
	 * @var	array<object>
	 */
	_images: [ ],
	
	/**
	 * true if image viewer is open
	 * @var	boolean
	 */
	_isOpen: false,
	
	/**
	 * number of total images
	 * @var	integer
	 */
	_items: -1,
	
	/**
	 * maximum dimensions for enlarged view
	 * @var	object<integer>
	 */
	_maxDimensions: {
		height: 0,
		width: 0
	},
	
	/**
	 * action proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * true if slideshow is currently running
	 * @var	boolean
	 */
	_slideshowEnabled: false,
	
	/**
	 * visible width of thumbnail container
	 * @var	integer
	 */
	_thumbnailContainerWidth: 0,
	
	/**
	 * right margin of a thumbnail
	 * @var	integer
	 */
	_thumbnailMarginRight: 0,
	
	/**
	 * left offset of thumbnail list
	 * @var	integer
	 */
	_thumbnailOffset: 0,
	
	/**
	 * outer width of a thumbnail (includes margin)
	 * @var	integer
	 */
	_thumbnailWidth: 0,
	
	/**
	 * slideshow timer object
	 * @var	WCF.PeriodicalExecuter
	 */
	_timer: null,
	
	/**
	 * list of interface elements
	 * @var	object<jQuery>
	 */
	_ui: {
		buttonNext: null,
		buttonPrevious: null,
		header: null,
		image: null,
		imageContainer: null,
		imageList: null,
		slideshow: {
			container: null,
			enlarge: null,
			next: null,
			previous: null,
			toggle: null
		}
	},
	
	/**
	 * list of options parsed during init
	 * @var	object<mixed>
	 */
	options: {
		// navigation
		shiftBy: 5, // thumbnail slider control
		
		// slideshow
		enableSlideshow: 1,
		speed: 5, // time in seconds
		
		// ajax
		className: '' // must be an instance of \wcf\data\IImageViewerAction
	},
	
	/**
	 * Creates a new wcfImageViewer instance.
	 */
	_create: function() {
		this._active = -1;
		this._activeImage = null;
		this._container = null;
		this._didInit = false;
		this._disableSlideshow = (this.element.data('disableSlideshow'));
		this._eventNamespace = this.element.wcfIdentify();
		this._images = [ ];
		this._isOpen = false;
		this._items = -1;
		this._maxDimensions = {
			height: document.documentElement.clientHeight,
			width: document.documentElement.clientWidth
		};
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		this._slideshowEnabled = false;
		this._thumbnailContainerWidth = 0;
		this._thumbnailMarginRight = 0;
		this._thumbnailOffset = 0;
		this._thumbnaiLWidth = 0;
		this._timer = null;
		this._ui = { };
		
		this.element.click($.proxy(this.open, this));
	},
	
	/**
	 * Opens the image viewer.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	open: function(event) {
		if (event) event.preventDefault();
		
		if (this._isOpen) {
			return false;
		}
		
		if (this._images.length === 0) {
			this._loadNextImages(true);
		}
		else {
			this._render(false, this.element.data('targetImageID'));
			
			if (this._items > 1 && this._slideshowEnabled) {
				this.startSlideshow();
			}
		}
		
		this._bindListener();
		
		this._isOpen = true;
		
		WCF.System.DisableScrolling.disable();
		
		return true;
	},
	
	/**
	 * Closes the image viewer.
	 * 
	 * @return	boolean
	 */
	close: function(event) {
		if (event) event.preventDefault();
		
		if (!this._isOpen) {
			return false;
		}
		
		this._container.removeClass('open');
		if (this._timer !== null) {
			this._timer.stop();
		}
		
		this._unbindListener();
		
		this._isOpen = false;
		
		WCF.System.DisableScrolling.enable();
		
		return true;
	},
	
	/**
	 * Enables the slideshow.
	 * 
	 * @return	boolean
	 */
	startSlideshow: function() {
		if (this._disableSlideshow || this._slideshowEnabled) {
			return false;
		}
		
		if (this._timer === null) {
			this._timer = new WCF.PeriodicalExecuter($.proxy(function() {
				var $index = this._active + 1;
				if ($index == this._items) {
					$index = 0;
				}
				
				this.showImage($index);
			}, this), this.options.speed * 1000);
		}
		else {
			this._timer.resume();
		}
		
		this._slideshowEnabled = true;
		
		this._ui.slideshow.toggle.children('span').removeClass('icon-play').addClass('icon-pause');
		
		return true;
	},
	
	/**
	 * Disables the slideshow.
	 * 
	 * @param	boolean		disableSlideshow
	 * @return	boolean
	 */
	stopSlideshow: function(disableSlideshow) {
		if (!this._slideshowEnabled) {
			return false;
		}
		
		this._timer.stop();
		if (disableSlideshow) {
			this._ui.slideshow.toggle.children('span').removeClass('icon-pause').addClass('icon-play');
		}
		
		this._slideshowEnabled = false;
		
		return true;
	},
	
	/**
	 * Binds event listeners.
	 */
	_bindListener: function() {
		$(document).on('keydown.' + this._eventNamespace, $.proxy(this._keyDown, this));
		$(window).on('resize.' + this._eventNamespace, $.proxy(this._renderImage, this));
	},
	
	/**
	 * Unbinds event listeners.
	 */
	_unbindListener: function() {
		$(document).off('keydown.' + this._eventNamespace);
		$(window).off('resize.' + this._eventNamespace);
	},
	
	/**
	 * Closes the slideshow on escape.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_keyDown: function(event) {
		switch (event.which) {
			// close slideshow
			case $.ui.keyCode.ESCAPE:
				this.close();
			break;
			
			// show previous image
			case $.ui.keyCode.LEFT:
				this._previousImage();
			break;
			
			// show next image
			case $.ui.keyCode.RIGHT:
				this._nextImage();
			break;
			
			// enable fullscreen mode
			case $.ui.keyCode.UP:
				if (!this._container.hasClass('maximized')) {
					this._toggleView();
				}
			break;
			
			// disable fullscreen mode
			case $.ui.keyCode.DOWN:
				if (this._container.hasClass('maximized')) {
					this._toggleView();
				}
			break;
			
			// jump to image page or full version
			case $.ui.keyCode.ENTER:
				var $link = this._ui.header.find('> div > h1 > a');
				if ($link.length == 1) {
					// forward to image page
					window.location = $link.prop('href');
				}
				else {
					// forward to full version
					this._ui.slideshow.full.trigger('click');
				}
			break;
			
			// toggle play/pause (80 = [p])
			case 80:
				this._ui.slideshow.toggle.trigger('click');
			break;
			
			default:
				return true;
			break;
		}
		
		return false;
	},
	
	/**
	 * Renders the image viewer UI.
	 * 
	 * @param	boolean		initialized
	 * @param	integer		targetImageID
	 */
	_render: function(initialized, targetImageID) {
		this._container.addClass('open');
		
		var $thumbnail = null;
		if (initialized) {
			$thumbnail = this._ui.imageList.children('li:eq(0)');
			this._thumbnailMarginRight = parseInt($thumbnail.css('marginRight').replace(/px$/, '')) || 0;
			this._thumbnailWidth = $thumbnail.outerWidth(true);
			this._thumbnailContainerWidth = this._ui.imageList.parent().innerWidth();
			
			if (this._items > 1 && this.options.enableSlideshow && !targetImageID) {
				this.startSlideshow();
			}
		}
		
		if (targetImageID) {
			this._ui.imageList.children('li').each($.proxy(function(index, item) {
				var $item = $(item);
				if ($item.data('objectID') == targetImageID) {
					$item.trigger('click');
					this.moveToImage($item.data('index'));
					
					return false;
				}
			}, this));
		}
		else if ($thumbnail !== null) {
			$thumbnail.trigger('click');
		}
			
		this._toggleButtons();
		
		// check if there is enough space to load more thumbnails
		this._preload();
	},
	
	/**
	 * Attempts to load the next images.
	 */
	_preload: function() {
		if (this._images.length < this._items) {
			var $thumbnailsWidth = this._images.length * this._thumbnailWidth;
			if ($thumbnailsWidth - this._thumbnailOffset < this._thumbnailContainerWidth) {
				this._loadNextImages(false);
			}
		}
	},
	
	/**
	 * Displays image on thumbnail click.
	 * 
	 * @param	object		event
	 */
	_showImage: function(event) {
		this.showImage($(event.currentTarget).data('index'), true);
	},
	
	/**
	 * Displays an image by index.
	 * 
	 * @param	integer		index
	 * @param	boolean		disableSlideshow
	 * @return	boolean
	 */
	showImage: function(index, disableSlideshow) {
		if (this._active == index) {
			return false;
		}
		
		this.stopSlideshow(disableSlideshow || false);
		
		// reset active marking
		if (this._active != -1) {
			this._images[this._active].listItem.removeClass('active');
		}
		
		this._active = index;
		var $image = this._images[index];
		
		this._ui.imageList.children('li').removeClass('active');
		$image.listItem.addClass('active');
		
		var $dimensions = this._ui.imageContainer.getDimensions('inner');
		var $newImageIndex = (this._activeImage ? 0 : 1);
		
		if (this._activeImage !== null) {
			this._ui.images[this._activeImage].removeClass('active');
		}
		
		this._activeImage = $newImageIndex;
		
		var $currentActiveImage = this._active;
		this._ui.imageContainer.addClass('loading');
		this._ui.images[$newImageIndex].off('load').prop('src', false).on('load', $.proxy(function() {
			this._imageOnLoad($currentActiveImage, $newImageIndex);
		}, this));
		
		this._renderImage($newImageIndex, $image, $dimensions);
		
		// user
		var $link = this._ui.header.find('> div > a').prop('href', $image.user.link).prop('title', $image.user.username);
		$link.children('img').prop('src', $image.user.avatarURL);
		
		// meta data
		var $title = WCF.String.escapeHTML($image.image.title);
		if ($image.image.link) $title = '<a href="' + $image.image.link + '">' + $image.image.title + '</a>';
		this._ui.header.find('> div > h1').html($title);
		
		var $seriesTitle = ($image.series && $image.series.title ? WCF.String.escapeHTML($image.series.title) : '');
		if ($image.series.link) $seriesTitle = '<a href="' + $image.series.link + '">' + $seriesTitle + '</a>';
		this._ui.header.find('> div > h2').html($seriesTitle);
		
		this._ui.header.find('> div > h3').text(WCF.Language.get('wcf.imageViewer.seriesIndex').replace(/{x}/, $image.listItem.data('index') + 1).replace(/{y}/, this._items));
		
		this._ui.slideshow.full.data('link', ($image.image.fullURL ? $image.image.fullURL : $image.image.url));
		
		this.moveToImage($image.listItem.data('index'));
		
		this._toggleButtons();
		
		return true;
	},
	
	/**
	 * Callback function for the image 'load' event.
	 * 
	 * @param	integer		currentActiveImage
	 * @param	integer		activeImageIndex
	 */
	_imageOnLoad: function(currentActiveImage, activeImageIndex) {
		// image did not load in time, ignore
		if (currentActiveImage != this._active) {
			return;
		}
		
		this._ui.imageContainer.removeClass('loading');
		this._ui.images[activeImageIndex].addClass('active');
		
		this.startSlideshow();
	},
	
	/**
	 * Renders target image, leaving 'imageData' undefined will invoke the rendering process for the currently active image.
	 * 
	 * @param	integer		targetIndex
	 * @param	object		imageData
	 * @param	object		containerDimensions
	 */
	_renderImage: function(targetIndex, imageData, containerDimensions) {
		if (!imageData) {
			targetIndex = this._activeImage;
			imageData = this._images[this._active];
			
			containerDimensions = {
				height: $(window).height() - (this._container.hasClass('maximized') ? 0 : 200),
				width: this._ui.imageContainer.innerWidth()
			};
		}
		
		// simulate padding
		containerDimensions.height -= 22;
		containerDimensions.width -= 20;
		
		this._ui.images[targetIndex].prop('src', imageData.image.url);
		
		var $height = imageData.image.height;
		var $width = imageData.image.width;
		var $ratio = 0.0;
		
		// check if image exceeds dimensions on the Y axis
		if ($height > containerDimensions.height) {
			$ratio = containerDimensions.height / $height;
			$height = containerDimensions.height;
			$width = Math.floor($width * $ratio);
		}
		
		// check if image exceeds dimensions on the X axis
		if ($width > containerDimensions.width) {
			$ratio = containerDimensions.width / $width;
			$width = containerDimensions.width;
			$height = Math.floor($height * $ratio);
		}
		
		var $left = Math.floor((containerDimensions.width - $width) / 2);
		
		this._ui.images[targetIndex].css({
			height: $height + 'px',
			left: ($left + 10) + 'px',
			marginTop: (Math.round($height / 2) * -1) + 'px',
			width: $width + 'px'
		});
	},
	
	/**
	 * Initialites the user interface.
	 * 
	 * @return	boolean
	 */
	_initUI: function() {
		if (this._didInit) {
			return false;
		}
		
		this._didInit = true;
		
		this._container = $('<div class="wcfImageViewer" />').appendTo(document.body);
		var $imageContainer = $('<div><img class="active" /><img /></div>').appendTo(this._container);
		var $imageList = $('<footer><span class="wcfImageViewerButtonPrevious icon icon-double-angle-left" /><div><ul /></div><span class="wcfImageViewerButtonNext icon icon-double-angle-right" /></footer>').appendTo(this._container);
		var $slideshowContainer = $('<ul />').appendTo($imageContainer);
		var $slideshowButtonPrevious = $('<li class="wcfImageViewerSlideshowButtonPrevious"><span class="icon icon48 icon-angle-left" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonToggle = $('<li class="wcfImageViewerSlideshowButtonToggle pointer"><span class="icon icon48 icon-play" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonNext = $('<li class="wcfImageViewerSlideshowButtonNext"><span class="icon icon48 icon-angle-right" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonEnlarge = $('<li class="wcfImageViewerSlideshowButtonEnlarge pointer jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.button.enlarge') + '"><span class="icon icon48 icon-resize-full" /></li>').appendTo($slideshowContainer);
		var $slideshowButtonFull = $('<li class="wcfImageViewerSlideshowButtonFull pointer jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.button.full') + '"><span class="icon icon48 icon-external-link" /></li>').appendTo($slideshowContainer);
		
		this._ui = {
			buttonNext: $imageList.children('span.wcfImageViewerButtonNext'),
			buttonPrevious: $imageList.children('span.wcfImageViewerButtonPrevious'),
			header: $('<header><div class="box64"><a class="framed jsTooltip"><img /></a><h1 /><h2 /><h3 /></div></header>').appendTo(this._container),
			imageContainer: $imageContainer,
			images: [
				$imageContainer.children('img:eq(0)').on('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', function() { $(this).removeClass('animateTransformation'); }),
				$imageContainer.children('img:eq(1)').on('webkitTransitionEnd transitionend msTransitionEnd oTransitionEnd', function() { $(this).removeClass('animateTransformation'); })
			],
			imageList: $imageList.find('> div > ul'),
			slideshow: {
				container: $slideshowContainer,
				enlarge: $slideshowButtonEnlarge,
				full: $slideshowButtonFull,
				next: $slideshowButtonNext,
				previous: $slideshowButtonPrevious,
				toggle: $slideshowButtonToggle
			}
		};
		
		this._ui.buttonNext.click($.proxy(this._next, this));
		this._ui.buttonPrevious.click($.proxy(this._previous, this));
		
		$slideshowButtonNext.click($.proxy(this._nextImage, this));
		$slideshowButtonPrevious.click($.proxy(this._previousImage, this));
		$slideshowButtonEnlarge.click($.proxy(this._toggleView, this));
		$slideshowButtonToggle.click($.proxy(function() {
			if (this._slideshowEnabled) {
				this.stopSlideshow(true);
			}
			else {
				this._disableSlideshow = false;
				this.startSlideshow();
			}
		}, this));
		$slideshowButtonFull.click(function(event) { window.location = $(event.currentTarget).data('link'); });
		
		// close button
		$('<span class="wcfImageViewerButtonClose icon icon48 icon-remove pointer jsTooltip" title="' + WCF.Language.get('wcf.global.button.close') + '" />').appendTo(this._ui.header).click($.proxy(this.close, this));
		
		return true;
	},
	
	/**
	 * Toggles between normal and fullscreen view.
	 */
	_toggleView: function() {
		this._ui.images[this._activeImage].addClass('animateTransformation');
		this._container.toggleClass('maximized');
		this._ui.slideshow.enlarge.toggleClass('active').children('span').toggleClass('icon-resize-full').toggleClass('icon-resize-small');
		
		this._renderImage(null, undefined, null);
	},
	
	/**
	 * Shifts the thumbnail list.
	 * 
	 * @param	object		event
	 * @param	integer		shiftBy
	 */
	_next: function(event, shiftBy) {
		if (this._ui.buttonNext.hasClass('pointer')) {
			if (shiftBy == undefined) {
				this.stopSlideshow(true);
			}
			
			var $maximumOffset = Math.max((this._items * this._thumbnailWidth) - this._thumbnailContainerWidth - this._thumbnailMarginRight, 0);
			this._thumbnailOffset = Math.min(this._thumbnailOffset + (this._thumbnailWidth * (shiftBy ? shiftBy : this.options.shiftBy)), $maximumOffset);
			this._ui.imageList.css('marginLeft', (this._thumbnailOffset * -1));
		}
		
		this._preload();
		
		this._toggleButtons();
	},
	
	/**
	 * Unshifts the thumbnail list.
	 * 
	 * @param	object		event
	 * @param	integer		shiftBy
	 */
	_previous: function(event, unshiftBy) {
		if (this._ui.buttonPrevious.hasClass('pointer')) {
			if (unshiftBy == undefined) {
				this.stopSlideshow(true);
			}
			
			this._thumbnailOffset = Math.max(this._thumbnailOffset - (this._thumbnailWidth * (unshiftBy ? unshiftBy : this.options.shiftBy)), 0);
			this._ui.imageList.css('marginLeft', (this._thumbnailOffset * -1));
		}
		
		this._toggleButtons();
	},
	
	/**
	 * Displays the next image.
	 * 
	 * @param	object		event
	 */
	_nextImage: function(event) {
		if (this._ui.slideshow.next.hasClass('pointer')) {
			this._disableSlideshow = true;
			
			this.stopSlideshow(true);
			this.showImage(this._active + 1);
		}
	},
	
	/**
	 * Displays the previous image.
	 * 
	 * @param	object		event
	 */
	_previousImage: function(event) {
		if (this._ui.slideshow.previous.hasClass('pointer')) {
			this._disableSlideshow = true;
			
			this.stopSlideshow(true);
			this.showImage(this._active - 1);
		}
	},
	
	/**
	 * Moves thumbnail list to target thumbnail.
	 * 
	 * @param	integer		seriesIndex
	 */
	moveToImage: function(seriesIndex) {
		// calculate start and end of thumbnail
		var $start = (seriesIndex - 3) * this._thumbnailWidth;
		var $end = $start + (this._thumbnailWidth * 5);
		
		// calculate visible offsets
		var $left = this._thumbnailOffset;
		var $right = this._thumbnailOffset + this._thumbnailContainerWidth;
		
		// check if thumbnail is within boundaries
		var $shouldMove = false;
		if ($start < $left || $end > $right) {
			$shouldMove = true;
		}
		
		// try to shift until the thumbnail itself and the next/previous 2 thumbnails are visible
		if ($shouldMove) {
			var $shiftBy = 0;
			
			// unshift
			if ($start < $left) {
				while ($start < $left) {
					$shiftBy++;
					$left -= this._thumbnailWidth;
				}
				
				this._previous(null, $shiftBy);
			}
			else {
				// shift
				while ($end > $right) {
					$shiftBy++;
					$right += this._thumbnailWidth;
				}
				
				this._next(null, $shiftBy);
			}
		}
	},
	
	/**
	 * Toggles control buttons.
	 */
	_toggleButtons: function() {
		// button 'previous'
		if (this._thumbnailOffset > 0) {
			this._ui.buttonPrevious.addClass('pointer');
		}
		else {
			this._ui.buttonPrevious.removeClass('pointer');
		}
		
		// button 'next'
		var $maximumOffset = (this._images.length * this._thumbnailWidth) - this._thumbnailContainerWidth - this._thumbnailMarginRight;
		if (this._thumbnailOffset >= $maximumOffset) {
			this._ui.buttonNext.removeClass('pointer');
		}
		else {
			this._ui.buttonNext.addClass('pointer');
		}
		
		// slideshow controls
		if (this._active > 0) {
			this._ui.slideshow.previous.addClass('pointer');
		}
		else {
			this._ui.slideshow.previous.removeClass('pointer');
		}
		
		if (this._active + 1 < this._images.length) {
			this._ui.slideshow.next.addClass('pointer');
		}
		else {
			this._ui.slideshow.next.removeClass('pointer');
		}
	},
	
	/**
	 * Inserts thumbnails.
	 * 
	 * @param	array<object>	images
	 */
	_createThumbnails: function(images) {
		for (var $i = 0, $length = images.length; $i < $length; $i++) {
			var $image = images[$i];
			
			var $listItem = $('<li class="loading pointer"><img src="' + $image.thumbnail.url + '" /></li>').appendTo(this._ui.imageList);
			$listItem.data('index', this._images.length).data('objectID', $image.objectID).click($.proxy(this._showImage, this));
			var $img = $listItem.children('img');
			if ($img.get(0).complete) {
				// thumbnail is read from cache
				$listItem.removeClass('loading');
			}
			else {
				$img.on('load', function() { $(this).parent().removeClass('loading'); });
			}
			
			$image.listItem = $listItem;
			this._images.push($image);
		}
	},
	
	/**
	 * Loads the next images via AJAX.
	 * 
	 * @param	boolean		init
	 */
	_loadNextImages: function(init) {
		this._proxy.setOption('data', {
			actionName: 'loadNextImages',
			className: this.options.className,
			interfaceName: 'wcf\\data\\IImageViewerAction',
			objectIDs: [ this.element.data('objectID') ],
			parameters: {
				maximumHeight: this._maxDimensions.height,
				maximumWidth: this._maxDimensions.width,
				offset: this._images.length,
				targetImageID: (init && this.element.data('targetImageID') ? this.element.data('targetImageID') : 0)
			}
		});
		this._proxy.setOption('showLoadingOverlay', false);
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.items) {
			this._items = data.returnValues.items;
		}
		
		var $initialized = this._initUI();
		
		this._createThumbnails(data.returnValues.images);
		
		var $targetImageID = (data.returnValues.targetImageID ? data.returnValues.targetImageID : 0);
		this._render($initialized, $targetImageID);
	}
});


// WCF.Label.js
/**
 * Namespace for labels.
 */
WCF.Label = {};

/**
 * Provides enhancements for ACP label management.
 */
WCF.Label.ACPList = Class.extend({
	/**
	 * input element
	 * @var	jQuery
	 */
	_labelInput: null,
	
	/**
	 * list of pre-defined label items
	 * @var	array<jQuery>
	 */
	_labelList: [ ],
	
	/**
	 * Initializes the ACP label list.
	 */
	init: function() {
		this._labelInput = $('#label').keydown($.proxy(this._keyPressed, this)).keyup($.proxy(this._keyPressed, this)).blur($.proxy(this._keyPressed, this));
		
		if ($.browser.mozilla && $.browser.touch) {
			this._labelInput.on('input', $.proxy(this._keyPressed, this));
		}
		
		$('#labelList').find('input[type="radio"]').each($.proxy(function(index, input) {
			var $input = $(input);
			
			// ignore custom values
			if ($input.prop('value') !== 'custom') {
				this._labelList.push($($input.next('span')));
			}
		}, this));
	},
	
	/**
	 * Renders label name as label or falls back to a default value if label is empty.
	 */
	_keyPressed: function() {
		var $text = this._labelInput.prop('value');
		if ($text === '') $text = WCF.Language.get('wcf.acp.label.defaultValue');
		
		for (var $i = 0, $length = this._labelList.length; $i < $length; $i++) {
			this._labelList[$i].text($text);
		}
	}
});

/**
 * Provides simple logic to inherit associations within structured lists.
 */
WCF.Label.ACPList.Connect = Class.extend({
	/**
	 * Initializes inheritation for structured lists.
	 */
	init: function() {
		var $listItems = $('#connect .structuredList li');
		if (!$listItems.length) return;
		
		$listItems.each($.proxy(function(index, item) {
			$(item).find('input[type="checkbox"]').click($.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Marks items as checked if they're logically below current item.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $listItem = $(event.currentTarget);
		if ($listItem.is(':checked')) {
			$listItem = $listItem.parents('li');
			var $depth = $listItem.data('depth');
			
			while (true) {
				$listItem = $listItem.next();
				if (!$listItem.length) {
					// no more siblings
					return true;
				}
				
				// element is on the same or higher level (= lower depth)
				if ($listItem.data('depth') <= $depth) {
					return true;
				}
				
				$listItem.find('input[type="checkbox"]').prop('checked', 'checked');
			}
		}
	}
});

/**
 * Provides a flexible label chooser.
 * 
 * @param	object		selectedLabelIDs
 * @param	string		containerSelector
 * @param	string		submitButtonSelector
 * @param	boolean		showWithoutSelection
 */
WCF.Label.Chooser = Class.extend({
	/**
	 * label container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * list of label groups
	 * @var	object
	 */
	_groups: { },
	
	/**
	 * show the 'without selection' option
	 * @var	boolean
	 */
	_showWithoutSelection: false,
	
	/**
	 * Initializes a new label chooser.
	 * 
	 * @param	object		selectedLabelIDs
	 * @param	string		containerSelector
	 * @param	string		submitButtonSelector
	 * @param	boolean		showWithoutSelection
	 */
	init: function(selectedLabelIDs, containerSelector, submitButtonSelector, showWithoutSelection) {
		this._container = null;
		this._groups = { };
		this._showWithoutSelection = (showWithoutSelection === true);
		
		// init containers
		this._initContainers(containerSelector);
		
		// pre-select labels
		if ($.getLength(selectedLabelIDs)) {
			for (var $groupID in selectedLabelIDs) {
				var $group = this._groups[$groupID];
				if ($group) {
					WCF.Dropdown.getDropdownMenu($group.wcfIdentify()).find('> ul > li:not(.dropdownDivider)').each($.proxy(function(index, label) {
						var $label = $(label);
						var $labelID = $label.data('labelID') || 0;
						if ($labelID && selectedLabelIDs[$groupID] == $labelID) {
							this._selectLabel($label, true);
						}
					}, this));
				}
			}
		}
		
		// mark all containers as initialized
		for (var $containerID in this._containers) {
			var $dropdown = this._containers[$containerID];
			if ($dropdown.data('labelID') === undefined) {
				$dropdown.data('labelID', 0);
			}
		}
		
		this._container = $(containerSelector);
		if (submitButtonSelector) {
			$(submitButtonSelector).click($.proxy(this._submit, this));
		}
		else if (this._container.is('form')) {
			this._container.submit($.proxy(this._submit, this));
		}
	},
	
	/**
	 * Initializes label groups.
	 * 
	 * @param	string		containerSelector
	 */
	_initContainers: function(containerSelector) {
		$(containerSelector).find('.labelChooser').each($.proxy(function(index, group) {
			var $group = $(group);
			var $groupID = $group.data('groupID');
			
			if (!this._groups[$groupID]) {
				var $containerID = $group.wcfIdentify();
				var $dropdownMenu = WCF.Dropdown.getDropdownMenu($containerID);
				if ($dropdownMenu === null) {
					WCF.Dropdown.initDropdown($group.find('.dropdownToggle'));
					$dropdownMenu = WCF.Dropdown.getDropdownMenu($containerID);
				}
				
				var $additionalList = $dropdownMenu;
				if ($dropdownMenu.getTagName() == 'div' && $dropdownMenu.children('.scrollableDropdownMenu').length) {
					$additionalList = $('<ul />').appendTo($dropdownMenu);
					$dropdownMenu = $dropdownMenu.children('.scrollableDropdownMenu');
				}
				
				this._groups[$groupID] = $group;
				
				$dropdownMenu.children('li').data('groupID', $groupID).click($.proxy(this._click, this));
				
				if (!$group.data('forceSelection') || this._showWithoutSelection) {
					$('<li class="dropdownDivider" />').appendTo($additionalList);
				}
				
				if (this._showWithoutSelection) {
					$('<li data-label-id="-1"><span><span class="badge label">' + WCF.Language.get('wcf.label.withoutSelection') + '</span></span></li>').data('groupID', $groupID).appendTo($additionalList).click($.proxy(this._click, this));
				}
				
				if (!$group.data('forceSelection')) {
					var $buttonEmpty = $('<li data-label-id="0"><span><span class="badge label">' + WCF.Language.get('wcf.label.none') + '</span></span></li>').data('groupID', $groupID).appendTo($additionalList);
					$buttonEmpty.click($.proxy(this._click, this));
				}
			}
		}, this));
	},
	
	/**
	 * Handles label selections.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._selectLabel($(event.currentTarget), false);
	},
	
	/**
	 * Selects a label.
	 * 
	 * @param	jQuery		label
	 * @param	boolean		onInit
	 */
	_selectLabel: function(label, onInit) {
		var $group = this._groups[label.data('groupID')];
		
		// already initialized, ignore
		if (onInit && $group.data('labelID') !== undefined) {
			return;
		}
		
		// save label id
		if (label.data('labelID')) {
			$group.data('labelID', label.data('labelID'));
		}
		else {
			$group.data('labelID', 0);
		}
		
		// replace button
		label = label.find('span > span');
		$group.find('.dropdownToggle > span').removeClass().addClass(label.attr('class')).text(label.text());
	},
	
	/**
	 * Creates hidden input elements on submit.
	 */
	_submit: function() {
		// get form submit area
		var $formSubmit = this._container.find('.formSubmit');
		
		// remove old, hidden values
		$formSubmit.find('input[type="hidden"]').each(function(index, input) {
			var $input = $(input);
			if ($input.attr('name').indexOf('labelIDs[') === 0) {
				$input.remove();
			}
		});
		
		// insert label ids
		for (var $groupID in this._groups) {
			var $group = this._groups[$groupID];
			if ($group.data('labelID')) {
				$('<input type="hidden" name="labelIDs[' + $groupID + ']" value="' + $group.data('labelID') + '" />').appendTo($formSubmit);
			}
		}
	}
});


// WCF.Location.js
/**
 * Location-related classes for WCF
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Location = { };

/**
 * Provides location-related utility functions.
 */
WCF.Location.Util = {
	/**
	 * Passes the user's current latitude and longitude to the given function
	 * as parameters. If the user's current position cannot be determined,
	 * undefined will be passed as both parameters.
	 * 
	 * @param	function	callback
	 * @param	integer		timeout
	 */
	getLocation: function(callback, timeout) {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				callback(position.coords.latitude, position.coords.longitude);
			}, function() {
				callback(undefined, undefined);
			}, {
				timeout: timeout || 5000
			});
		}
		else {
			callback(undefined, undefined);
		}
	}
};

/**
 * Namespace for Google Maps-related classes.
 */
WCF.Location.GoogleMaps = { };

/**
 * Handles the global Google Maps settings.
 */
WCF.Location.GoogleMaps.Settings = {
	/**
	 * Google Maps settings
	 * @var	object
	 */
	_settings: { },
	
	/**
	 * Returns the value of a certain setting or null if it doesn't exist.
	 * 
	 * If no parameter is given, all settings are returned.
	 * 
	 * @param	string		setting
	 * @return	mixed
	 */
	get: function(setting) {
		if (setting === undefined) {
			return this._settings;
		}
		
		if (this._settings[setting] !== undefined) {
			return this._settings[setting];
		}
		
		return null;
	},
	
	/**
	 * Sets the value of a certain setting.
	 * 
	 * @param	mixed		setting
	 * @param	mixed		value
	 */
	set: function(setting, value) {
		if ($.isPlainObject(setting)) {
			for (var index in setting) {
				this._settings[index] = setting[index];
			}
		}
		else {
			this._settings[setting] = value;
		}
	}
};

/**
 * Handles a Google Maps map.
 */
WCF.Location.GoogleMaps.Map = Class.extend({
	/**
	 * map object for the displayed map
	 * @var	google.maps.Map
	 */
	_map: null,
	
	/**
	 * list of markers on the map
	 * @var	array<google.maps.Marker>
	 */
	_markers: [ ],
	
	/**
	 * Initalizes a new WCF.Location.Map object.
	 * 
	 * @param	string		mapContainerID
	 * @param	object		mapOptions
	 */
	init: function(mapContainerID, mapOptions) {
		this._mapContainer = $('#' + mapContainerID);
		this._mapOptions = $.extend(true, this._getDefaultMapOptions(), mapOptions);
		
		this._map = new google.maps.Map(this._mapContainer[0], this._mapOptions);
		this._markers = [ ];
		
		// fix maps in mobile sidebars by refreshing the map when displaying
		// the map
		if (this._mapContainer.parents('.sidebar').length) {
			enquire.register('screen and (max-width: 800px)', {
				setup: $.proxy(this._addSidebarMapListener, this),
				deferSetup: true
			});
		}
		
		this.refresh();
	},
	
	/**
	 * Adds click listener to mobile sidebar toggle button to refresh map.
	 */
	_addSidebarMapListener: function() {
		$('.content > .mobileSidebarToggleButton').click($.proxy(this.refresh, this));
	},
	
	/**
	 * Returns the default map options.
	 * 
	 * @return	object
	 */
	_getDefaultMapOptions: function() {
		var $defaultMapOptions = { };
		
		// dummy center value
		$defaultMapOptions.center = new google.maps.LatLng(WCF.Location.GoogleMaps.Settings.get('defaultLatitude'), WCF.Location.GoogleMaps.Settings.get('defaultLongitude'));
		
		// double click to zoom
		$defaultMapOptions.disableDoubleClickZoom = WCF.Location.GoogleMaps.Settings.get('disableDoubleClickZoom');
		
		// draggable
		$defaultMapOptions.draggable = WCF.Location.GoogleMaps.Settings.get('draggable');
		
		// map type
		switch (WCF.Location.GoogleMaps.Settings.get('mapType')) {
			case 'map':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
			break;
			
			case 'satellite':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
			break;
			
			case 'physical':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
			break;
			
			case 'hybrid':
			default:
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
			break;
		}
		
		/// map type controls
		$defaultMapOptions.mapTypeControl = WCF.Location.GoogleMaps.Settings.get('mapTypeControl') != 'off';
		if ($defaultMapOptions.mapTypeControl) {
			switch (WCF.Location.GoogleMaps.Settings.get('mapTypeControl')) {
				case 'dropdown':
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
					};
				break;
				
				case 'horizontalBar':
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR
					};
				break;
				
				default:
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.DEFAULT
					};
				break;
			}
		}
		
		// scale control
		$defaultMapOptions.scaleControl = WCF.Location.GoogleMaps.Settings.get('scaleControl');
		$defaultMapOptions.scrollwheel = WCF.Location.GoogleMaps.Settings.get('scrollwheel');
		
		// zoom
		$defaultMapOptions.zoom = WCF.Location.GoogleMaps.Settings.get('zoom');
		
		return $defaultMapOptions;
	},
	
	/**
	 * Adds a draggable marker at the given position to the map and returns
	 * the created marker object.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 * @return	google.maps.Marker
	 */
	addDraggableMarker: function(latitude, longitude) {
		var $marker = new google.maps.Marker({
			clickable: false,
			draggable: true,
			map: this._map,
			position: new google.maps.LatLng(latitude, longitude),
			zIndex: 1
		});
		
		this._markers.push($marker);
		
		return $marker;
	},
	
	/**
	 * Adds a marker with the given data to the map and returns the created
	 * marker object.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 * @param	string		title
	 * @param	mixed		icon
	 * @param	string		information
	 * @return	google.maps.Marker
	 */
	addMarker: function(latitude, longitude, title, icon, information) {
		var $marker = new google.maps.Marker({
			map: this._map,
			position: new google.maps.LatLng(latitude, longitude),
			title: title
		});
		
		// add icon
		if (icon) {
			$marker.setIcon(icon);
		}
		
		// add info window for marker information
		if (information) {
			var $infoWindow = new google.maps.InfoWindow({
				content: information
			});
			google.maps.event.addListener($marker, 'click', $.proxy(function() {
				$infoWindow.open(this._map, $marker);
			}, this));
			
			// add info window object to marker object
			$marker.infoWindow = $infoWindow;
		}
		
		this._markers.push($marker);
		
		return $marker;
	},
	
	/**
	 * Returns all markers on the map.
	 * 
	 * @return	array<google.maps.Marker>
	 */
	getMarkers: function() {
		return this._markers;
	},
	
	/**
	 * Returns the Google Maps map object.
	 * 
	 * @return	google.maps.Map
	 */
	getMap: function() {
		return this._map;
	},
	
	/**
	 * Refreshes the map.
	 */
	refresh: function() {
		// save current center since resize does not preserve it
		var $center = this._map.getCenter();
		
		google.maps.event.trigger(this._map, 'resize');
		
		// set center to old value again
		this._map.setCenter($center);
	},
	
	/**
	 * Refreshes the boundaries of the map to show all markers.
	 */
	refreshBounds: function() {
		var $minLatitude = null;
		var $maxLatitude = null;
		var $minLongitude = null;
		var $maxLongitude = null;
		
		for (var $index in this._markers) {
			var $marker = this._markers[$index];
			var $latitude = $marker.getPosition().lat();
			var $longitude = $marker.getPosition().lng();
			
			if ($minLatitude === null) {
				$minLatitude = $maxLatitude = $latitude;
				$minLongitude = $maxLongitude = $longitude;
			}
			else {
				if ($minLatitude > $latitude) {
					$minLatitude = $latitude;
				}
				else if ($maxLatitude < $latitude) {
					$maxLatitude = $latitude;
				}
				
				if ($minLongitude > $latitude) {
					$minLongitude = $latitude;
				}
				else if ($maxLongitude < $longitude) {
					$maxLongitude = $longitude;
				}
			}
		}
		
		this._map.fitBounds(new google.maps.LatLngBounds(
			new google.maps.LatLng($minLatitude, $minLongitude),
			new google.maps.LatLng($maxLatitude, $maxLongitude)
		));
	},
	
	/**
	 * Removes all markers from the map.
	 */
	removeMarkers: function() {
		for (var $index in this._markers) {
			this._markers[$index].setMap(null);
		}
		
		this._markers = [ ];
	},
	
	/**
	 * Sets the center of the map to the given position.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 */
	setCenter: function(latitude, longitude) {
		this._map.setCenter(new google.maps.LatLng(latitude, longitude));
	}
});

/**
 * Handles a large map with many markers where (new) markers are loaded via AJAX.
 */
WCF.Location.GoogleMaps.LargeMap = WCF.Location.GoogleMaps.Map.extend({
	/**
	 * name of the PHP class executing the 'getMapMarkers' action
	 * @var	string
	 */
	_actionClassName: null,
	
	/**
	 * indicates if the maps center can be set by location search
	 * @var	WCF.Location.GoogleMaps.LocationSearch
	 */
	_locationSearch: null,
	
	/**
	 * selector for the location search input
	 * @var	string
	 */
	_locationSearchInputSelector: null,
	
	/**
	 * cluster handling the markers on the map
	 * @var	MarkerClusterer
	 */
	_markerClusterer: null,
	
	/**
	 * ids of the objects which are already displayed
	 * @var	array<integer>
	 */
	_objectIDs: [ ],
	
	/**
	 * previous coordinates of the north east map boundary
	 * @var	google.maps.LatLng
	 */
	_previousNorthEast: null,
	
	/**
	 * previous coordinates of the south west map boundary
	 * @var	google.maps.LatLng
	 */
	_previousSouthWest: null,
	
	/**
	 * @see	WCF.Location.GoogleMaps.Map.init()
	 */
	init: function(mapContainerID, mapOptions, actionClassName, locationSearchInputSelector) {
		this._super(mapContainerID, mapOptions);
		
		this._actionClassName = actionClassName;
		this._locationSearchInputSelector = locationSearchInputSelector || '';
		this._objectIDs = [ ];
		
		if (this._locationSearchInputSelector) {
			this._locationSearch = new WCF.Location.GoogleMaps.LocationSearch(locationSearchInputSelector, $.proxy(this._centerMap, this));
		}
		
		this._markerClusterer = new MarkerClusterer(this._map, this._markers, {
			maxZoom: 17
		});
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
		
		this._previousNorthEast = null;
		this._previousSouthWest = null;
		google.maps.event.addListener(this._map, 'idle', $.proxy(this._loadMarkers, this));
	},
	
	/**
	 * Centers the map based on a location search result.
	 * 
	 * @param	object		data
	 */
	_centerMap: function(data) {
		this.setCenter(data.location.lat(), data.location.lng());
		
		$(this._locationSearchInputSelector).val(data.label);
	},
	
	/**
	 * Loads markers if the map is reloaded.
	 */
	_loadMarkers: function() {
		var $northEast = this._map.getBounds().getNorthEast();
		var $southWest = this._map.getBounds().getSouthWest();
		
		// check if the user has zoomed in, then all markers are already
		// displayed
		if (this._previousNorthEast && this._previousNorthEast.lat() >= $northEast.lat() && this._previousNorthEast.lng() >= $northEast.lng() && this._previousSouthWest.lat() <= $southWest.lat() && this._previousSouthWest.lng() <= $southWest.lng()) {
			return;
		}
		
		this._previousNorthEast = $northEast;
		this._previousSouthWest = $southWest;
		
		this._proxy.setOption('data', {
			actionName: 'getMapMarkers',
			className: this._actionClassName,
			parameters: {
				excludedObjectIDs: this._objectIDs,
				eastLongitude: $northEast.lng(),
				northLatitude: $northEast.lat(),
				southLatitude: $southWest.lat(),
				westLongitude: $southWest.lng()
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles a successful AJAX request.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues && data.returnValues.markers) {
			for (var $index in data.returnValues.markers) {
				var $markerInfo = data.returnValues.markers[$index];
				
				this.addMarker($markerInfo.latitude, $markerInfo.longitude, $markerInfo.title, null, $markerInfo.infoWindow);
				
				if ($markerInfo.objectID) {
					this._objectIDs.push($markerInfo.objectID);
				}
				else if ($markerInfo.objectIDs) {
					this._objectIDs = this._objectIDs.concat($markerInfo.objectIDs);
				}
			}
		}
	},
	
	/**
	 * @see	WCF.Location.GoogleMaps.Map.addMarker()
	 */
	addMarker: function(latitude, longitude, title, icon, information) {
		var $marker = this._super(latitude, longitude, title, icon, information);
		this._markerClusterer.addMarker($marker);
		
		return $marker;
	}
});

/**
 * Provides location searches based on google.maps.Geocoder.
 */
WCF.Location.GoogleMaps.LocationSearch = WCF.Search.Base.extend({
	/**
	 * Google Maps geocoder object
	 * @var	google.maps.Geocoder
	 */
	_geocoder: null,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay) {
		this._super(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay);
		
		this._geocoder = new google.maps.Geocoder();
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(geocoderResult) {
		var $listItem = $('<li><span>' + WCF.String.escapeHTML(geocoderResult.formatted_address) + '</span></li>').appendTo(this._list);
		$listItem.data('location', geocoderResult.geometry.location).data('label', geocoderResult.formatted_address).click($.proxy(this._executeCallback, this));
		
		this._itemCount++;
		
		return $listItem;
	},
	
	/**
	 * @see	WCF.Search.Base._keyUp()
	 */
	_keyUp: function(event) {
		// handle arrow keys and return key
		switch (event.which) {
			case $.ui.keyCode.LEFT:
			case $.ui.keyCode.RIGHT:
				return;
			break;
			
			case $.ui.keyCode.UP:
				this._selectPreviousItem();
				return;
			break;
			
			case $.ui.keyCode.DOWN:
				this._selectNextItem();
				return;
			break;
			
			case $.ui.keyCode.ENTER:
				return this._selectElement(event);
			break;
		}
		
		var $content = this._getSearchString(event);
		if ($content === '') {
			this._clearList(true);
		}
		else if ($content.length >= this._triggerLength) {
			this._clearList(false);
			
			this._geocoder.geocode({
				address: $content
			}, $.proxy(this._success, this));
		}
		else {
			// input below trigger length
			this._clearList(false);
		}
	},
	
	/**
	 * Handles a successfull geocoder request.
	 * 
	 * @param	array		results
	 * @param	integer		status
	 */
	_success: function(results, status) {
		if (status != google.maps.GeocoderStatus.OK) {
			return;
		}
		
		if ($.getLength(results)) {
			var $count = 0;
			for (var $index in results) {
				this._createListItem(results[$index]);
				
				if (++$count == 10) {
					break;
				}
			}
		}
		else if (!this._handleEmptyResult()) {
			return;
		}
		
		WCF.CloseOverlayHandler.addCallback('WCF.Search.Base', $.proxy(function() { this._clearList(); }, this));
		
		var $containerID = this._searchInput.parents('.dropdown').wcfIdentify();
		if (!WCF.Dropdown.getDropdownMenu($containerID).hasClass('dropdownOpen')) {
			WCF.Dropdown.toggleDropdown($containerID);
		}
		
		// pre-select first item
		this._itemIndex = -1;
		if (!WCF.Dropdown.getDropdown($containerID).data('disableAutoFocus')) {
			this._selectNextItem();
		}
	}
});

/**
 * Handles setting a single location on a Google Map.
 */
WCF.Location.GoogleMaps.LocationInput = Class.extend({
	/**
	 * location search object
	 * @var	WCF.Location.GoogleMaps.LocationSearch
	 */
	_locationSearch: null,
	
	/**
	 * related map object
	 * @var	WCF.Location.GoogleMaps.Map
	 */
	_map: null,
	
	/**
	 * draggable marker to set the location
	 * @var	google.maps.Marker
	 */
	_marker: null,
	
	/**
	 * Initializes a new WCF.Location.GoogleMaps.LocationInput object.
	 * 
	 * @param	string		mapContainerID
	 * @param	object		mapOptions
	 * @param	string		searchInput
	 * @param	function	callback
	 */
	init: function(mapContainerID, mapOptions, searchInput, latitude, longitude) {
		this._searchInput = searchInput;
		this._map = new WCF.Location.GoogleMaps.Map(mapContainerID, mapOptions);
		this._locationSearch = new WCF.Location.GoogleMaps.LocationSearch(searchInput, $.proxy(this._setMarkerByLocation, this));
		
		if (latitude && longitude) {
			this._marker = this._map.addDraggableMarker(latitude, longitude);
		}
		else {
			this._marker = this._map.addDraggableMarker(0, 0);
			
			WCF.Location.Util.getLocation($.proxy(function(latitude, longitude) {
				if (latitude !== undefined && longitude !== undefined) {
					WCF.Location.GoogleMaps.Util.moveMarker(this._marker, latitude, longitude);
					WCF.Location.GoogleMaps.Util.focusMarker(this._marker);
				}
			}, this));
		}
		
		this._marker.addListener('dragend', $.proxy(this._updateLocation, this));
	},
	
	/**
	 * Returns the related map.
	 * 
	 * @return	WCF.Location.GoogleMaps.Map
	 */
	getMap: function() {
		return this._map;
	},
	
	/**
	 * Returns the draggable marker used to set the location.
	 * 
	 * @return	google.maps.Marker
	 */
	getMarker: function() {
		return this._marker;
	},
	
	/**
	 * Updates location on marker position change.
	 */
	_updateLocation: function() {
		WCF.Location.GoogleMaps.Util.reverseGeocoding($.proxy(function(result) {
			if (result !== null) {
				$(this._searchInput).val(result);
			}
		}, this), this._marker);
	},
	
	/**
	 * Sets the marker based on an entered location.
	 * 
	 * @param	object		data
	 */
	_setMarkerByLocation: function(data) {
		this._marker.setPosition(data.location);
		WCF.Location.GoogleMaps.Util.focusMarker(this._marker);
		
		$(this._searchInput).val(data.label);
	}
});

/**
 * Provides utility functions for Google Maps maps.
 */
WCF.Location.GoogleMaps.Util = {
	/**
	 * geocoder instance
	 * @var	google.maps.Geocoder
	 */
	_geocoder: null,
	
	/**
	 * Focuses the given marker's map on the marker.
	 * 
	 * @param	google.maps.Marker	marker
	 */
	focusMarker: function(marker) {
		marker.getMap().setCenter(marker.getPosition());
	},
	
	/**
	 * Returns the latitude and longitude of the given marker.
	 * 
	 * @return	object
	 */
	getMarkerPosition: function(marker) {
		return {
			latitude: marker.getPosition().lat(),
			longitude: marker.getPosition().lng()
		};
	},
	
	/**
	 * Moves the given marker to the given position.
	 * 
	 * @param	google.maps.Marker		marker
	 * @param	float				latitude
	 * @param	float				longitude
	 * @param	boolean				dragend		indicates if "dragend" event is fired
	 */
	moveMarker: function(marker, latitude, longitude, triggerDragend) {
		marker.setPosition(new google.maps.LatLng(latitude, longitude));
		
		if (triggerDragend) {
			google.maps.event.trigger(marker, 'dragend');
		}
	},
	
	/**
	 * Performs a reverse geocoding request.
	 * 
	 * @param	object			callback
	 * @param	google.maps.Marker	marker
	 * @param	string			latitude
	 * @param	string			longitude
	 * @param	boolean			fullResult
	 */
	reverseGeocoding: function(callback, marker, latitude, longitude, fullResult) {
		if (marker) {
			latitude = marker.getPosition().lat();
			longitude = marker.getPosition().lng();
		}
		
		if (this._geocoder === null) {
			this._geocoder = new google.maps.Geocoder();
		}
		
		var $latLng = new google.maps.LatLng(latitude, longitude);
		this._geocoder.geocode({ latLng: $latLng }, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				callback((fullResult ? results : results[0].formatted_address));
			}
			else {
				callback(null);
			}
		});
	}
};


// WCF.Message.js
/**
 * Message related classes for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Message = { };

/**
 * Namespace for BBCode related classes.
 */
WCF.Message.BBCode = { };

/**
 * BBCode Viewer for WCF.
 */
WCF.Message.BBCode.CodeViewer = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Initializes the WCF.Message.BBCode.CodeViewer class.
	 */
	init: function() {
		this._dialog = null;
		
		this._initCodeBoxes();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.BBCode.CodeViewer', $.proxy(this._initCodeBoxes, this));
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Initializes available code boxes.
	 */
	_initCodeBoxes: function() {
		$('.codeBox:not(.jsCodeViewer)').each($.proxy(function(index, codeBox) {
			var $codeBox = $(codeBox).addClass('jsCodeViewer');
			
			$('<span class="icon icon16 icon-copy pointer jsTooltip" title="' + WCF.Language.get('wcf.message.bbcode.code.copy') + '" />').appendTo($codeBox.find('div > h3')).click($.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Shows a code viewer for a specific code box.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $content = '';
		$(event.currentTarget).parents('div').next('ol').children('li').each(function(index, listItem) {
			if ($content) {
				$content += "\n";
			}
			
			// do *not* use $.trim here, as we want to preserve whitespaces
			$content += $(listItem).text().replace(/\n+$/, '');
		});
		
		if (this._dialog === null) {
			this._dialog = $('<div><textarea cols="60" rows="12" readonly="readonly" /></div>').hide().appendTo(document.body);
			this._dialog.children('textarea').val($content);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.message.bbcode.code.copy')
			});
		}
		else {
			this._dialog.children('textarea').val($content);
			this._dialog.wcfDialog('open');
		}
		
		this._dialog.children('textarea').select();
	}
});

/**
 * Prevents multiple submits of the same form by disabling the submit button.
 */
WCF.Message.FormGuard = Class.extend({
	/**
	 * Initializes the WCF.Message.FormGuard class.
	 */
	init: function() {
		var $forms = $('form.jsFormGuard').removeClass('jsFormGuard').submit(function() {
			$(this).find('.formSubmit input[type=submit]').disable();
		});
		
		// restore buttons, prevents disabled buttons on back navigation in Opera
		$(window).unload(function() {
			$forms.find('.formSubmit input[type=submit]').enable();
		});
	}
});

/**
 * Provides previews for Redactor message fields.
 * 
 * @param	string		className
 * @param	string		messageFieldID
 * @param	string		previewButtonID
 */
WCF.Message.Preview = Class.extend({
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * message field id
	 * @var	string
	 */
	_messageFieldID: '',
	
	/**
	 * message field
	 * @var	jQuery
	 */
	_messageField: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * preview button
	 * @var	jQuery
	 */
	_previewButton: null,
	
	/**
	 * previous button label
	 * @var	string
	 */
	_previewButtonLabel: '',
	
	/**
	 * Initializes a new WCF.Message.Preview object.
	 * 
	 * @param	string		className
	 * @param	string		messageFieldID
	 * @param	string		previewButtonID
	 */
	init: function(className, messageFieldID, previewButtonID) {
		this._className = className;
		
		// validate message field
		this._messageFieldID = $.wcfEscapeID(messageFieldID);
		this._messageField = $('#' + this._messageFieldID);
		if (!this._messageField.length) {
			console.debug("[WCF.Message.Preview] Unable to find message field identified by '" + this._messageFieldID + "'");
			return;
		}
		
		// validate preview button
		previewButtonID = $.wcfEscapeID(previewButtonID);
		this._previewButton = $('#' + previewButtonID);
		if (!this._previewButton.length) {
			console.debug("[WCF.Message.Preview] Unable to find preview button identified by '" + previewButtonID + "'");
			return;
		}
		
		this._previewButton.click($.proxy(this._click, this));
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Reads message field input and triggers an AJAX request.
	 */
	_click: function(event) {
		var $message = this._getMessage();
		if ($message === null) {
			console.debug("[WCF.Message.Preview] Unable to access Redactor instance of '" + this._messageFieldID + "'");
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: 'getMessagePreview',
			className: this._className,
			parameters: this._getParameters($message)
		});
		this._proxy.sendRequest();
		
		// update button label
		this._previewButtonLabel = this._previewButton.html();
		this._previewButton.html(WCF.Language.get('wcf.global.loading')).disable();
		
		// poke event
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Returns request parameters.
	 * 
	 * @param	string		message
	 * @return	object
	 */
	_getParameters: function(message) {
		// collect message form options
		var $options = { };
		$('#settings').find('input[type=checkbox]').each(function(index, checkbox) {
			var $checkbox = $(checkbox);
			if ($checkbox.is(':checked')) {
				$options[$checkbox.prop('name')] = $checkbox.prop('value');
			}
		});
		
		// build parameters
		return {
			data: {
				message: message
			},
			options: $options
		};
	},
	
	/**
	 * Returns parsed message from Redactor or null if editor was not accessible.
	 * 
	 * @return	string
	 */
	_getMessage: function() {
		if (!$.browser.redactor) {
			return this._messageField.val();
		}
		else if (this._messageField.data('redactor')) {
			return this._messageField.redactor('getText');
		}
		
		return null;
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// restore preview button
		this._previewButton.html(this._previewButtonLabel).enable();
		
		// remove error message
		this._messageField.parent().children('small.innerError').remove();
		
		// evaluate message
		this._handleResponse(data);
	},
	
	/**
	 * Evaluates response data.
	 * 
	 * @param	object		data
	 */
	_handleResponse: function(data) { },
	
	/**
	 * Handles errors during preview requests.
	 * 
	 * The return values indicates if the default error overlay is shown.
	 * 
	 * @param	object		data
	 * @return	boolean
	 */
	_failure: function(data) {
		if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
			return true;
		}
		
		// restore preview button
		this._previewButton.html(this._previewButtonLabel).enable();
		
		var $innerError = this._messageField.next('small.innerError').empty();
		if (!$innerError.length) {
			$innerError = $('<small class="innerError" />').appendTo(this._messageField.parent());
		}
		
		$innerError.html(data.returnValues.errorType);
		
		return false;
	}
});

/**
 * Default implementation for message previews.
 * 
 * @see	WCF.Message.Preview
 */
WCF.Message.DefaultPreview = WCF.Message.Preview.extend({
	_attachmentObjectType: null,
	_attachmentObjectID: null,
	_tmpHash: null,
	
	/**
	 * @see	WCF.Message.Preview.init()
	 */
	init: function(attachmentObjectType, attachmentObjectID, tmpHash) {
		this._super('wcf\\data\\bbcode\\MessagePreviewAction', 'text', 'previewButton');
		
		this._attachmentObjectType = attachmentObjectType || null;
		this._attachmentObjectID = attachmentObjectID || null;
		this._tmpHash = tmpHash || null;
	},
	
	/**
	 * @see	WCF.Message.Preview._handleResponse()
	 */
	_handleResponse: function(data) {
		var $preview = $('#previewContainer');
		if (!$preview.length) {
			$preview = $('<div class="container containerPadding marginTop" id="previewContainer"><fieldset><legend>' + WCF.Language.get('wcf.global.preview') + '</legend><div></div></fieldset>').prependTo($('#messageContainer')).wcfFadeIn();
		}
		
		$preview.find('div:eq(0)').html(data.returnValues.message);
		
		new WCF.Effect.Scroll().scrollTo($preview);
	},
	
	/**
	 * @see	WCF.Message.Preview._getParameters()
	 */
	_getParameters: function(message) {
		var $parameters = this._super(message);
		
		if (this._attachmentObjectType != null) {
			$parameters.attachmentObjectType = this._attachmentObjectType;
			$parameters.attachmentObjectID = this._attachmentObjectID;
			$parameters.tmpHash = this._tmpHash;
		}
		
		return $parameters;
	}
});

/**
 * Handles multilingualism for messages.
 * 
 * @param	integer		languageID
 * @param	object		availableLanguages
 * @param	boolean		forceSelection
 */
WCF.Message.Multilingualism = Class.extend({
	/**
	 * list of available languages
	 * @var	object
	 */
	_availableLanguages: { },
	
	/**
	 * language id
	 * @var	integer
	 */
	_languageID: 0,
	
	/**
	 * language input element
	 * @var	jQuery
	 */
	_languageInput: null,
	
	/**
	 * Initializes WCF.Message.Multilingualism
	 * 
	 * @param	integer		languageID
	 * @param	object		availableLanguages
	 * @param	boolean		forceSelection
	 */
	init: function(languageID, availableLanguages, forceSelection) {
		this._availableLanguages = availableLanguages;
		this._languageID = languageID || 0;
		
		this._languageInput = $('#languageID');
		
		// preselect current language id
		this._updateLabel();
		
		// register event listener
		this._languageInput.find('.dropdownMenu > li').click($.proxy(this._click, this));
		
		// add element to disable multilingualism
		if (!forceSelection) {
			var $dropdownMenu = this._languageInput.find('.dropdownMenu');
			$('<li class="dropdownDivider" />').appendTo($dropdownMenu);
			$('<li><span><span class="badge">' + this._availableLanguages[0] + '</span></span></li>').click($.proxy(this._disable, this)).appendTo($dropdownMenu);
		}
		
		// bind submit event
		this._languageInput.parents('form').submit($.proxy(this._submit, this));
	},
	
	/**
	 * Handles language selections.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._languageID = $(event.currentTarget).data('languageID');
		this._updateLabel();
	},
	
	/**
	 * Disables language selection.
	 */
	_disable: function() {
		this._languageID = 0;
		this._updateLabel();
	},
	
	/**
	 * Updates selected language.
	 */
	_updateLabel: function() {
		this._languageInput.find('.dropdownToggle > span').text(this._availableLanguages[this._languageID]);
	},
	
	/**
	 * Sets language id upon submit.
	 */
	_submit: function() {
		this._languageInput.next('input[name=languageID]').prop('value', this._languageID);
	}
});

/**
 * Loads smiley categories upon user request.
 */
WCF.Message.SmileyCategories = Class.extend({
	/**
	 * list of already loaded category ids
	 * @var	array<integer>
	 */
	_cache: [ ],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the smiley loader.
	 */
	init: function() {
		this._cache = [ ];
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('#smilies').on('wcftabsbeforeactivate', $.proxy(this._click, this));
		
		// handle onload
		var self = this;
		new WCF.PeriodicalExecuter(function(pe) {
			pe.stop();
			
			self._click({ }, { newTab: $('#smilies > .menu li.ui-state-active') });
		}, 100);
	},
	
	/**
	 * Handles tab menu clicks.
	 * 
	 * @param	object		event
	 * @param	object		ui
	 */
	_click: function(event, ui) {
		var $categoryID = parseInt($(ui.newTab).children('a').data('smileyCategoryID'));
		
		if ($categoryID && !WCF.inArray($categoryID, this._cache)) {
			this._proxy.setOption('data', {
				actionName: 'getSmilies',
				className: 'wcf\\data\\smiley\\category\\SmileyCategoryAction',
				objectIDs: [ $categoryID ]
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $categoryID = parseInt(data.returnValues.smileyCategoryID);
		this._cache.push($categoryID);
		
		$('#smilies-' + $categoryID).html(data.returnValues.template);
	}
});

/**
 * Handles smiley clicks.
 */
WCF.Message.Smilies = Class.extend({
	/**
	 * redactor element
	 * @var	$.Redactor
	 */
	_redactor: null,
	
	_wysiwygSelector: '',
	
	/**
	 * Initializes the smiley handler.
	 * 
	 * @param	string		wysiwygSelector
	 */
	init: function(wysiwygSelector) {
		this._wysiwygSelector = wysiwygSelector;
		
		WCF.System.Dependency.Manager.register('Redactor_' + this._wysiwygSelector, $.proxy(function() {
			this._redactor = $('#' + this._wysiwygSelector).redactor('getObject');
			
			// add smiley click handler
			$(document).on('click', '.jsSmiley', $.proxy(this._smileyClick, this));
		}, this));
	},
	
	/**
	 * Handles tab smiley clicks.
	 * 
	 * @param	object		event
	 */
	_smileyClick: function(event) {
		var $target = $(event.currentTarget);
		var $smileyCode = $target.data('smileyCode');
		var $smileyPath = $target.data('smileyPath');
		
		// register smiley
		this._redactor.insertSmiley($smileyCode, $smileyPath, true);
	}
});

/**
 * Provides an AJAX-based quick reply for messages.
 */
WCF.Message.QuickReply = Class.extend({
	/**
	 * quick reply container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * message field
	 * @var	jQuery
	 */
	_messageField: null,
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * true, if a request to save the message is pending
	 * @var	boolean
	 */
	_pendingSave: false,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * quote manager object
	 * @var	WCF.Message.Quote.Manager
	 */
	_quoteManager: null,
	
	/**
	 * scroll handler
	 * @var	WCF.Effect.Scroll
	 */
	_scrollHandler: null,
	
	/**
	 * success message for created but invisible messages
	 * @var	string
	 */
	_successMessageNonVisible: '',
	
	/**
	 * Initializes a new WCF.Message.QuickReply object.
	 * 
	 * @param	boolean				supportExtendedForm
	 * @param	WCF.Message.Quote.Manager	quoteManager
	 */
	init: function(supportExtendedForm, quoteManager) {
		this._container = $('#messageQuickReply');
		this._container.children('.message').addClass('jsInvalidQuoteTarget');
		this._messageField = $('#text');
		this._pendingSave = false;
		if (!this._container || !this._messageField) {
			return;
		}
		
		// button actions
		var $formSubmit = this._container.find('.formSubmit');
		$formSubmit.find('button[data-type=save]').click($.proxy(this._save, this));
		if (supportExtendedForm) $formSubmit.find('button[data-type=extended]').click($.proxy(this._prepareExtended, this));
		$formSubmit.find('button[data-type=cancel]').click($.proxy(this._cancel, this));
		
		if (quoteManager) this._quoteManager = quoteManager;
		
		$('.jsQuickReply').data('__api', this).click($.proxy(this.click, this));
		
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
		this._scroll = new WCF.Effect.Scroll();
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.add'));
		this._successMessageNonVisible = '';
	},
	
	/**
	 * Handles clicks on reply button.
	 * 
	 * @param	object		event
	 */
	click: function(event) {
		this._container.toggle();
		
		if (this._container.is(':visible')) {
			// TODO: Scrolling is anything but smooth, better use the init callback
			this._scroll.scrollTo(this._container, true);
			
			WCF.Message.Submit.registerButton('text', this._container.find('.formSubmit button[data-type=save]'));
			
			if (this._quoteManager) {
				// check if message field is empty
				var $empty = true;
				if ($.browser.redactor) {
					if (this._messageField.data('redactor')) {
						$empty = (!$.trim(this._messageField.redactor('getText')));
						this._editorCallback($empty);
					}
				}
				else {
					$empty = (!this._messageField.val().length);
					this._editorCallback($empty);
				}
			}
		}
		
		// discard event
		if (event !== null) {
			event.stopPropagation();
			return false;
		}
	},
	
	/**
	 * Inserts quotes and focuses the editor.
	 */
	_editorCallback: function(isEmpty) {
		if (isEmpty) {
			this._quoteManager.insertQuotes(this._getClassName(), this._getObjectID(), $.proxy(this._insertQuotes, this));
		}
		
		if ($.browser.redactor) {
			this._messageField.redactor('focus');
		}
		else {
			this._messageField.focus();
		}
	},
	
	/**
	 * Returns container element.
	 * 
	 * @return	jQuery
	 */
	getContainer: function() {
		return this._container;
	},
	
	/**
	 * Insertes quotes into the quick reply editor.
	 * 
	 * @param	object		data
	 */
	_insertQuotes: function(data) {
		if (!data.returnValues.template) {
			return;
		}
		
		if ($.browser.redactor) {
			this._messageField.redactor('insertDynamic', data.returnValues.template);
		}
		else {
			this._messageField.val(data.returnValues.template);
		}
	},
	
	/**
	 * Saves message.
	 */
	_save: function() {
		if (this._pendingSave) {
			return;
		}
		
		var $message = '';
		if ($.browser.redactor) {
			$message = this._messageField.redactor('getText');
		}
		else {
			$message = $.trim(this._messageField.val());
		}
		
		// check if message is empty
		var $innerError = this._messageField.parent().find('small.innerError');
		if ($message === '' || $message === '0') {
			if (!$innerError.length) {
				$innerError = $('<small class="innerError" />').appendTo(this._messageField.parent());
			}
			
			$innerError.html(WCF.Language.get('wcf.global.form.error.empty'));
			return;
		}
		else {
			$innerError.remove();
		}
		
		this._pendingSave = true;
		
		this._proxy.setOption('data', {
			actionName: 'quickReply',
			className: this._getClassName(),
			interfaceName: 'wcf\\data\\IMessageQuickReplyAction',
			parameters: this._getParameters($message)
		});
		this._proxy.sendRequest();
		
		// show spinner and hide Redactor
		var $messageBody = this._container.find('.messageQuickReplyContent .messageBody');
		$('<span class="icon icon48 icon-spinner" />').appendTo($messageBody);
		$messageBody.children('.redactor_box').hide().end().next().hide();
	},
	
	/**
	 * Returns the parameters for the save request.
	 * 
	 * @param	string		message
	 * @return	object
	 */
	_getParameters: function(message) {
		var $parameters = {
			objectID: this._getObjectID(),
			data: {
				message: message
			},
			lastPostTime: this._container.data('lastPostTime'),
			pageNo: this._container.data('pageNo'),
			removeQuoteIDs: (this._quoteManager === null ? [ ] : this._quoteManager.getQuotesMarkedForRemoval()),
			tmpHash: this._container.data('tmpHash') || ''
		};
		if (this._container.data('anchor')) {
			$parameters.anchor = this._container.data('anchor');
		}
		
		return $parameters;
	},
	
	/**
	 * Cancels quick reply.
	 */
	_cancel: function() {
		this._revertQuickReply(true);
		
		if ($.browser.redactor) {
			this._messageField.redactor('reset');
		}
		else {
			this._messageField.val('');
		}
	},
	
	/**
	 * Reverts quick reply to original state and optionally hiding it.
	 * 
	 * @param	boolean		hide
	 */
	_revertQuickReply: function(hide) {
		var $messageBody = this._container.find('.messageQuickReplyContent .messageBody');
		
		if (hide) {
			this._container.hide();
			
			// remove previous error messages
			$messageBody.children('small.innerError').remove();
		}
		
		// display Redactor
		$messageBody.children('.icon-spinner').remove();
		$messageBody.children('.redactor_box').show();
		
		// display form submit
		$messageBody.next().show();
	},
	
	/**
	 * Prepares jump to extended message add form.
	 */
	_prepareExtended: function() {
		this._pendingSave = true;
		
		// mark quotes for removal
		if (this._quoteManager !== null) {
			this._quoteManager.markQuotesForRemoval();
		}
		
		var $message = '';
		if ($.browser.redactor) {
			$message = this._messageField.redactor('getText');
		}
		else {
			$message = this._messageField.val();
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'jumpToExtended',
				className: this._getClassName(),
				interfaceName: 'wcf\\data\\IExtendedMessageQuickReplyAction',
				parameters: {
					containerID: this._getObjectID(),
					message: $message
				}
			},
			success: function(data, textStatus, jqXHR) {
				window.location = data.returnValues.url;
			}
		});
	},
	
	/**
	 * Handles successful AJAX calls.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if ($.browser.redactor) {
			this._messageField.redactor('autosavePurge');
		}
		
		// redirect to new page
		if (data.returnValues.url) {
			window.location = data.returnValues.url;
		}
		else {
			if (data.returnValues.template) {
				// insert HTML
				var $message = $('' + data.returnValues.template);
				if (this._container.data('sortOrder') == 'DESC') {
					$message.insertAfter(this._container);
				}
				else {
					$message.insertBefore(this._container);
				}
				
				// update last post time
				this._container.data('lastPostTime', data.returnValues.lastPostTime);
				
				// show notification
				this._notification.show(undefined, undefined, WCF.Language.get('wcf.global.success.add'));
				
				this._updateHistory($message.wcfIdentify());
			}
			else {
				// show notification
				var $message = (this._successMessageNonVisible) ? this._successMessageNonVisible : 'wcf.global.success.add';
				this._notification.show(undefined, 5000, WCF.Language.get($message));
			}
			
			if ($.browser.redactor) {
				this._messageField.redactor('reset');
			}
			else {
				this._messageField.val('');
			}
			
			// hide quick reply and revert it
			this._revertQuickReply(true);
			
			// count stored quotes
			if (this._quoteManager !== null) {
				this._quoteManager.countQuotes();
			}
			
			this._pendingSave = false;
		}
	},
	
	/**
	 * Reverts quick reply on failure to preserve entered message.
	 */
	_failure: function(data) {
		this._pendingSave = false;
		this._revertQuickReply(false);
		
		if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
			return true;
		}
		
		var $messageBody = this._container.find('.messageQuickReplyContent .messageBody');
		var $innerError = $messageBody.children('small.innerError').empty();
		if (!$innerError.length) {
			$innerError = $('<small class="innerError" />').appendTo($messageBody);
		}
		
		$innerError.html(data.returnValues.errorType);
		
		return false;
	},
	
	/**
	 * Returns action class name.
	 * 
	 * @return	string
	 */
	_getClassName: function() {
		return '';
	},
	
	/**
	 * Returns object id.
	 * 
	 * @return	integer
	 */
	_getObjectID: function() {
		return 0;
	},
	
	/**
	 * Updates the history to avoid old content when going back in the browser
	 * history.
	 * 
	 * @param	hash
	 */
	_updateHistory: function(hash) {
		window.location.hash = hash;
	}
});

/**
 * Provides an inline message editor.
 * 
 * @param	integer		containerID
 */
WCF.Message.InlineEditor = Class.extend({
	/**
	 * currently active message
	 * @var	string
	 */
	_activeElementID: '',
	
	/**
	 * message cache
	 * @var	string
	 */
	_cache: '',
	
	/**
	 * list of messages
	 * @var	object
	 */
	_container: { },
	
	/**
	 * container id
	 * @var	integer
	 */
	_containerID: 0,
	
	/**
	 * list of dropdowns
	 * @var	object
	 */
	_dropdowns: { },
	
	/**
	 * CSS selector for the message container
	 * @var	string
	 */
	_messageContainerSelector: '.jsMessage',
	
	/**
	 * prefix of the message editor CSS id
	 * @var	string
	 */
	_messageEditorIDPrefix: 'messageEditor',
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * quote manager object
	 * @var	WCF.Message.Quote.Manager
	 */
	_quoteManager: null,
	
	/**
	 * support for extended editing form
	 * @var	boolean
	 */
	_supportExtendedForm: false,
	
	/**
	 * Initializes a new WCF.Message.InlineEditor object.
	 * 
	 * @param	integer				containerID
	 * @param	boolean				supportExtendedForm
	 * @param	WCF.Message.Quote.Manager	quoteManager
	 */
	init: function(containerID, supportExtendedForm, quoteManager) {
		this._activeElementID = '';
		this._cache = '';
		this._container = { };
		this._containerID = parseInt(containerID);
		this._dropdowns = { };
		this._quoteManager = quoteManager || null;
		this._supportExtendedForm = (supportExtendedForm) ? true : false;
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
		
		this.initContainers();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.InlineEditor', $.proxy(this.initContainers, this));
	},
	
	/**
	 * Initializes editing capability for all messages.
	 */
	initContainers: function() {
		$(this._messageContainerSelector).each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!this._container[$containerID]) {
				this._container[$containerID] = $container;
				
				if ($container.data('canEditInline')) {
					var $button = $container.find('.jsMessageEditButton:eq(0)').data('containerID', $containerID).click($.proxy(this._clickInline, this));
					if ($container.data('canEdit')) $button.dblclick($.proxy(this._click, this));
				}
				else if ($container.data('canEdit')) {
					$container.find('.jsMessageEditButton:eq(0)').data('containerID', $containerID).click($.proxy(this._click, this));
				}
			}
		}, this));
	},
	
	/**
	 * Loads WYSIWYG editor for selected message.
	 * 
	 * @param	object		event
	 * @param	integer		containerID
	 * @return	boolean
	 */
	_click: function(event, containerID) {
		var $containerID = (event === null) ? containerID : $(event.currentTarget).data('containerID');
		if (this._activeElementID === '') {
			this._activeElementID = $containerID;
			this._prepare();
			
			this._proxy.setOption('data', {
				actionName: 'beginEdit',
				className: this._getClassName(),
				interfaceName: 'wcf\\data\\IMessageInlineEditorAction',
				parameters: {
					containerID: this._containerID,
					objectID: this._container[$containerID].data('objectID')
				}
			});
			this._proxy.setOption('failure', $.proxy(function() { this._cancel(); }, this));
			this._proxy.sendRequest();
		}
		else {
			var $notification = new WCF.System.Notification(WCF.Language.get('wcf.message.error.editorAlreadyInUse'), 'warning');
			$notification.show();
		}
		
		// force closing dropdown to avoid displaying the dropdown after
		// triple clicks
		if (this._dropdowns[this._container[$containerID].data('objectID')]) {
			this._dropdowns[this._container[$containerID].data('objectID')].removeClass('dropdownOpen');
		}
		
		if (event !== null) {
			event.stopPropagation();
			return false;
		}
	},
	
	/**
	 * Provides an inline dropdown menu instead of directly loading the WYSIWYG editor.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_clickInline: function(event) {
		var $button = $(event.currentTarget);
		
		if (!$button.hasClass('dropdownToggle')) {
			var $containerID = $button.data('containerID');
			
			$button.addClass('dropdownToggle').parent().addClass('dropdown');
			
			var $dropdownMenu = $('<ul class="dropdownMenu" />').insertAfter($button);
			this._initDropdownMenu($containerID, $dropdownMenu);
			
			WCF.DOMNodeInsertedHandler.execute();
			
			this._dropdowns[this._container[$containerID].data('objectID')] = $dropdownMenu;
			
			WCF.Dropdown.registerCallback($button.parent().wcfIdentify(), $.proxy(this._toggleDropdown, this));
			
			// trigger click event
			$button.trigger('click');
		}
		
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Handles errorneus editing requests.
	 * 
	 * @param	object		data
	 */
	_failure: function(data) {
		this._revertEditor();
		
		if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
			return true;
		}
		
		var $messageBody = this._container[this._activeElementID].find('.messageBody .messageInlineEditor');
		var $innerError = $messageBody.children('small.innerError').empty();
		if (!$innerError.length) {
			$innerError = $('<small class="innerError" />').insertBefore($messageBody.children('.formSubmit'));
		}
		
		$innerError.html(data.returnValues.errorType);
		
		return false;
	},
	
	/**
	 * Forces message options to stay visible if toggling dropdown menu.
	 * 
	 * @param	string		containerID
	 * @param	string		action
	 */
	_toggleDropdown: function(containerID, action) {
		WCF.Dropdown.getDropdown(containerID).parents('.messageOptions').toggleClass('forceOpen');
	},
	
	/**
	 * Initializes the inline edit dropdown menu.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		dropdownMenu
	 */
	_initDropdownMenu: function(containerID, dropdownMenu) { },
	
	/**
	 * Prepares message for WYSIWYG display.
	 */
	_prepare: function() {
		var $messageBody = this._container[this._activeElementID].find('.messageBody');
		$('<span class="icon icon48 icon-spinner" />').appendTo($messageBody);
		
		var $content = $messageBody.find('.messageText');
		
		// hide unrelated content
		$content.parent().children('.jsInlineEditorHideContent').hide();
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').hide();
		
		this._cache = $content.detach();
	},
	
	/**
	 * Cancels editing and reverts to original message.
	 */
	_cancel: function() {
		var $container = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget');
		
		// remove editor
		var $target = $('#' + this._messageEditorIDPrefix + $container.data('objectID'));
		$target.redactor('autosavePurge');
		$target.redactor('destroy');
		
		// restore message
		var $messageBody = $container.find('.messageBody');
		$messageBody.children('.icon-spinner').remove();
		$messageBody.children('div:eq(0)').html(this._cache);
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		
		// show unrelated content
		$messageBody.find('.jsInlineEditorHideContent').show();
		
		// revert message options
		this._container[this._activeElementID].find('.messageOptions').removeClass('forceHidden');
		
		this._activeElementID = '';
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Handles successful AJAX calls.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.returnValues.actionName) {
			case 'beginEdit':
				this._showEditor(data);
			break;
			
			case 'save':
				this._showMessage(data);
			break;
		}
	},
	
	/**
	 * Shows WYSIWYG editor for active message.
	 * 
	 * @param	object		data
	 */
	_showEditor: function(data) {
		// revert failure function
		this._proxy.setOption('failure', $.proxy(this._failure, this));
		
		var $messageBody = this._container[this._activeElementID].addClass('jsInvalidQuoteTarget').find('.messageBody');
		$messageBody.children('.icon-spinner').remove();
		var $content = $messageBody.children('div:eq(0)');
		
		// insert wysiwyg
		$('' + data.returnValues.template).appendTo($content);
		
		// bind buttons
		var $formSubmit = $content.find('.formSubmit');
		var $saveButton = $formSubmit.find('button[data-type=save]').click($.proxy(this._save, this));
		if (this._supportExtendedForm) $formSubmit.find('button[data-type=extended]').click($.proxy(this._prepareExtended, this));
		$formSubmit.find('button[data-type=cancel]').click($.proxy(this._cancel, this));
		
		WCF.Message.Submit.registerButton(
			this._messageEditorIDPrefix + this._container[this._activeElementID].data('objectID'),
			$saveButton
		);
		
		// hide message options
		this._container[this._activeElementID].find('.messageOptions').addClass('forceHidden');
		
		var $element = $('#' + this._messageEditorIDPrefix + this._container[this._activeElementID].data('objectID'));
		if ($.browser.redactor) {
			new WCF.PeriodicalExecuter($.proxy(function(pe) {
				pe.stop();
				
				if (this._quoteManager) {
					this._quoteManager.setAlternativeEditor($element);
				}
			}, this), 250);
		}
		else {
			$element.focus();
		}
	},
	
	/**
	 * Reverts editor.
	 */
	_revertEditor: function() {
		var $messageBody = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget').find('.messageBody');
		$messageBody.children('span.icon-spinner').remove();
		$messageBody.children('div:eq(0)').children().show();
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		
		// show unrelated content
		$messageBody.find('.jsInlineEditorHideContent').show();
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Saves editor contents.
	 */
	_save: function() {
		var $container = this._container[this._activeElementID];
		var $objectID = $container.data('objectID');
		var $message = '';
		
		if ($.browser.redactor) {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).redactor('getText');
		}
		else {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).val();
		}
		
		this._proxy.setOption('data', {
			actionName: 'save',
			className: this._getClassName(),
			interfaceName: 'wcf\\data\\IMessageInlineEditorAction',
			parameters: {
				containerID: this._containerID,
				data: {
					message: $message
				},
				objectID: $objectID
			}
		});
		this._proxy.sendRequest();
		
		this._hideEditor();
	},
	
	/**
	 * Prepares jumping to extended editing mode.
	 */
	_prepareExtended: function() {
		var $container = this._container[this._activeElementID];
		var $objectID = $container.data('objectID');
		var $message = '';
		
		if ($.browser.redactor) {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).redactor('getText');
		}
		else {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).val();
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'jumpToExtended',
				className: this._getClassName(),
				parameters: {
					containerID: this._containerID,
					message: $message,
					messageID: $objectID
				}
			},
			success: function(data, textStatus, jqXHR) {
				window.location = data.returnValues.url;
			}
		});
	},
	
	/**
	 * Hides WYSIWYG editor.
	 */
	_hideEditor: function() {
		var $messageBody = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget').find('.messageBody');
		$('<span class="icon icon48 icon-spinner" />').appendTo($messageBody);
		$messageBody.children('div:eq(0)').children().hide();
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		
		// show unrelated content
		$messageBody.find('.jsInlineEditorHideContent').show();
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Shows rendered message.
	 * 
	 * @param	object		data
	 */
	_showMessage: function(data) {
		var $container = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget');
		var $messageBody = $container.find('.messageBody');
		$messageBody.children('.icon-spinner').remove();
		var $content = $messageBody.children('div:eq(0)');
		
		// show unrelated content
		$content.parent().children('.jsInlineEditorHideContent').show();
		
		// revert message options
		this._container[this._activeElementID].find('.messageOptions').removeClass('forceHidden');
		
		// remove editor
		if ($.browser.redactor) {
			$('#' + this._messageEditorIDPrefix + $container.data('objectID')).redactor('destroy');
		}
		
		$content.empty();
		
		// insert new message
		$content.html('<div class="messageText">' + data.returnValues.message + '</div>');
		
		if (data.returnValues.attachmentList == undefined) {
			$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		}
		else {
			$messageBody.children('.attachmentThumbnailList, .attachmentFileList').remove();
			
			if (data.returnValues.attachmentList) {
				$(data.returnValues.attachmentList).insertAfter($messageBody.children('div:eq(0)'));
			}
		}
		
		this._activeElementID = '';
		
		this._updateHistory(this._getHash($container.data('objectID')));
		
		this._notification.show();
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Returns message action class name.
	 * 
	 * @return	string
	 */
	_getClassName: function() {
		return '';
	},
	
	/**
	 * Returns the hash added to the url after successfully editing a message.
	 * 
	 * @return	string
	 */
	_getHash: function(objectID) {
		return '#message' + objectID;
	},
	
	/**
	 * Updates the history to avoid old content when going back in the browser
	 * history.
	 * 
	 * @param	hash
	 */
	_updateHistory: function(hash) {
		window.location.hash = hash;
	}
});

/**
 * Handles submit buttons for forms with an embedded WYSIWYG editor.
 */
WCF.Message.Submit = {
	/**
	 * list of registered buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * Registers submit button for specified wysiwyg container id.
	 * 
	 * @param	string		wysiwygContainerID
	 * @param	string		selector
	 */
	registerButton: function(wysiwygContainerID, selector) {
		if (!WCF.Browser.isChrome()) {
			return;
		}
		
		this._buttons[wysiwygContainerID] = $(selector);
	},
	
	/**
	 * Triggers 'click' event for registered buttons.
	 */
	execute: function(wysiwygContainerID) {
		if (!this._buttons[wysiwygContainerID]) {
			return;
		}
		
		this._buttons[wysiwygContainerID].trigger('click');
	}
};

/**
 * Namespace for message quotes.
 */
WCF.Message.Quote = { };

/**
 * Handles message quotes.
 * 
 * @param	string		className
 * @param	string		objectType
 * @param	string		containerSelector
 * @param	string		messageBodySelector
 */
WCF.Message.Quote.Handler = Class.extend({
	/**
	 * active container id
	 * @var	string
	 */
	_activeContainerID: '',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * list of message containers
	 * @var	object
	 */
	_containers: { },
	
	/**
	 * container selector
	 * @var	string
	 */
	_containerSelector: '',
	
	/**
	 * 'copy quote' overlay
	 * @var	jQuery
	 */
	_copyQuote: null,
	
	/**
	 * marked message
	 * @var	string
	 */
	_message: '',
	
	/**
	 * message body selector
	 * @var	string
	 */
	_messageBodySelector: '',
	
	/**
	 * object id
	 * @var	integer
	 */
	_objectID: 0,
	
	/**
	 * object type name
	 * @var	string
	 */
	_objectType: '',
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * quote manager
	 * @var	WCF.Message.Quote.Manager
	 */
	_quoteManager: null,
	
	/**
	 * Initializes the quote handler for given object type.
	 * 
	 * @param	WCF.Message.Quote.Manager	quoteManager
	 * @param	string				className
	 * @param	string				objectType
	 * @param	string				containerSelector
	 * @param	string				messageBodySelector
	 * @param	string				messageContentSelector
	 */
	init: function(quoteManager, className, objectType, containerSelector, messageBodySelector, messageContentSelector) {
		this._className = className;
		if (this._className == '') {
			console.debug("[WCF.Message.QuoteManager] Empty class name given, aborting.");
			return;
		}
		
		this._objectType = objectType;
		if (this._objectType == '') {
			console.debug("[WCF.Message.QuoteManager] Empty object type name given, aborting.");
			return;
		}
		
		this._containerSelector = containerSelector;
		this._message = '';
		this._messageBodySelector = messageBodySelector;
		this._messageContentSelector = messageContentSelector;
		this._objectID = 0;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initContainers();
		this._initCopyQuote();
		
		$(document).mouseup($.proxy(this._mouseUp, this));
		
		// register with quote manager
		this._quoteManager = quoteManager;
		this._quoteManager.register(this._objectType, this);
		
		// register with DOMNodeInsertedHandler
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.Quote.Handler' + objectType.hashCode(), $.proxy(this._initContainers, this));
	},
	
	/**
	 * Initializes message containers.
	 */
	_initContainers: function() {
		var self = this;
		$(this._containerSelector).each(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!self._containers[$containerID]) {
				self._containers[$containerID] = $container;
				if ($container.hasClass('jsInvalidQuoteTarget')) {
					return true;
				}
				
				if (self._messageBodySelector !== null) {
					$container = $container.find(self._messageBodySelector).data('containerID', $containerID);
				}
				
				$container.mousedown($.proxy(self._mouseDown, self));
				
				// bind event to quote whole message
				self._containers[$containerID].find('.jsQuoteMessage').click($.proxy(self._saveFullQuote, self));
			}
		});
	},
	
	/**
	 * Handles mouse down event.
	 * 
	 * @param	object		event
	 */
	_mouseDown: function(event) {
		// hide copy quote
		this._copyQuote.hide();
		
		// store container ID
		var $container = $(event.currentTarget);
		
		if (this._messageBodySelector) {
			$container = this._containers[$container.data('containerID')];
		}
		
		if ($container.hasClass('jsInvalidQuoteTarget')) {
			this._activeContainerID = '';
			
			return;
		}
		
		this._activeContainerID = $container.wcfIdentify();
		
		// remove alt-tag from all images, fixes quoting in Firefox
		if ($.browser.mozilla) {
			$container.find('img').each(function() {
				var $image = $(this);
				$image.data('__alt', $image.attr('alt')).removeAttr('alt');
			});
		}
	},
	
	/**
	 * Returns the text of a node and its children.
	 * 
	 * @param	object		node
	 * @return	string
	 */
	_getNodeText: function(node) {
		var nodeText = '';
		
		for (var i = 0; i < node.childNodes.length; i++) {
			if (node.childNodes[i].nodeType == 3) {
				// text node
				nodeText += node.childNodes[i].nodeValue;
			}
			else {
				if (!node.childNodes[i].tagName) {
					continue;
				}
				
				var $tagName = node.childNodes[i].tagName.toLowerCase();
				if ($tagName === 'li') {
					nodeText += "\r\n";
				}
				else if ($tagName === 'td' && !$.browser.msie) {
					nodeText += "\r\n";
				}
				
				nodeText += this._getNodeText(node.childNodes[i]);
				
				if ($tagName === 'ul') {
					nodeText += "\n";
				}
			}
		}
		
		return nodeText;
	},
	
	/**
	 * Handles the mouse up event.
	 * 
	 * @param	object		event
	 */
	_mouseUp: function(event) {
		// ignore event
		if (this._activeContainerID == '') {
			this._copyQuote.hide();
			
			return;
		}
		
		var $container = this._containers[this._activeContainerID];
		var $selection = this._getSelectedText();
		var $text = $.trim($selection);
		if ($text == '') {
			this._copyQuote.hide();
			
			return;
		}
		
		// compare selection with message text of given container
		var $messageText = null;
		if (this._messageBodySelector) {
			$messageText = this._getNodeText($container.find(this._messageContentSelector).get(0));
		}
		else {
			$messageText = this._getNodeText($container.get(0));
		}
		
		// selected text is not part of $messageText or contains text from unrelated nodes
		if (this._normalize($messageText).indexOf(this._normalize($text)) === -1) {
			return;
		}
		this._copyQuote.show();
		
		var $coordinates = this._getBoundingRectangle($container, $selection);
		var $dimensions = this._copyQuote.getDimensions('outer');
		var $left = ($coordinates.right - $coordinates.left) / 2 - ($dimensions.width / 2) + $coordinates.left;
		
		this._copyQuote.css({
			top: $coordinates.top - $dimensions.height - 7 + 'px',
			left: $left + 'px'
		});
		this._copyQuote.hide();
		
		// reset containerID
		this._activeContainerID = '';
		
		// show element after a delay, to prevent display if text was unmarked again (clicking into marked text)
		var self = this;
		new WCF.PeriodicalExecuter(function(pe) {
			pe.stop();
			
			var $text = $.trim(self._getSelectedText());
			if ($text != '') {
				self._copyQuote.show();
				self._message = $text;
				self._objectID = $container.data('objectID');
				
				// revert alt tags, fixes quoting in Firefox
				if ($.browser.mozilla) {
					$container.find('img').each(function() {
						var $image = $(this);
						$image.attr('alt', $image.data('__alt'));
					});
				}
			}
		}, 10);
	},
	
	/**
	 * Normalizes a text for comparison.
	 * 
	 * @param	string		text
	 * @return	string
	 */
	_normalize: function(text) {
		return text.replace(/\r?\n|\r/g, "\n").replace(/\s/g, ' ').replace(/\s{2,}/g, ' ');
	},
	
	/**
	 * Returns the left or right offset of the current text selection.
	 * 
	 * @param	object		range
	 * @param	boolean		before
	 * @return	object
	 */
	_getOffset: function(range, before) {
		range.collapse(before);
		
		var $elementID = WCF.getRandomID();
		var $element = document.createElement('span');
		$element.innerHTML = '<span id="' + $elementID + '"></span>';
		var $fragment = document.createDocumentFragment(), $node;
		while ($node = $element.firstChild) {
			$fragment.appendChild($node);
		}
		range.insertNode($fragment);
		
		$element = $('#' + $elementID);
		var $position = $element.offset();
		$position.top = $position.top - $(window).scrollTop();
		$element.remove();
		
		return $position;
	},
	
	/**
	 * Returns the offsets of the selection's bounding rectangle.
	 * 
	 * @return	object
	 */
	_getBoundingRectangle: function(container, selection) {
		var $coordinates = null;
		
		if (document.createRange && typeof document.createRange().getBoundingClientRect != "undefined") { // Opera, Firefox, Safari, Chrome
			if (selection.rangeCount > 0) {
				// the coordinates returned by getBoundingClientRect() is relative to the window, not the document!
				//var $rect = selection.getRangeAt(0).getBoundingClientRect();
				var $rects = selection.getRangeAt(0).getClientRects();
				var $rect = selection.getRangeAt(0).getBoundingClientRect();
				
				/*
				var $rect = { };
				if (!$.browser.mozilla && $rects.length > 1) {
					// save current selection to restore it later
					var $range = selection.getRangeAt(0);
					var $bckp = this._saveSelection(container.get(0));
					var $position1 = this._getOffset($range, true);
					
					var $range = selection.getRangeAt(0);
					var $position2 = this._getOffset($range, false);
					
					$rect = {
						left: Math.min($position1.left, $position2.left),
						right: Math.max($position1.left, $position2.left),
						top: Math.max($position1.top, $position2.top)
					};
					
					// restore selection
					this._restoreSelection(container.get(0), $bckp);
				}
				else {
					$rect = selection.getRangeAt(0).getBoundingClientRect();
				}
				*/
				
				var $document = $(document);
				var $offsetTop = $document.scrollTop();
				
				$coordinates = {
					left: $rect.left,
					right: $rect.right,
					top: $rect.top + $offsetTop
				};
			}
		}
		else if (document.selection && document.selection.type != "Control") { // IE
			var $range = document.selection.createRange();
			
			$coordinates = {
				left: $range.boundingLeft,
				right: $range.boundingRight,
				top: $range.boundingTop
			};
		}
		
		return $coordinates;
	},
	
	/**
	 * Saves current selection.
	 * 
	 * @see		http://stackoverflow.com/a/13950376
	 * 
	 * @param	object		containerEl
	 * @return	object
	 */
	_saveSelection: function(containerEl) {
		if (window.getSelection && document.createRange) {
			var range = window.getSelection().getRangeAt(0);
			var preSelectionRange = range.cloneRange();
			preSelectionRange.selectNodeContents(containerEl);
			preSelectionRange.setEnd(range.startContainer, range.startOffset);
			var start = preSelectionRange.toString().length;
			
			return {
				start: start,
				end: start + range.toString().length
			};
		}
		else {
			var selectedTextRange = document.selection.createRange();
			var preSelectionTextRange = document.body.createTextRange();
			preSelectionTextRange.moveToElementText(containerEl);
			preSelectionTextRange.setEndPoint("EndToStart", selectedTextRange);
			var start = preSelectionTextRange.text.length;
			
			return {
				start: start,
				end: start + selectedTextRange.text.length
			};
		}
	},
	
	/**
	 * Restores a selection.
	 * 
	 * @see		http://stackoverflow.com/a/13950376
	 * 
	 * @param	object		containerEl
	 * @param	object		savedSel
	 */
	_restoreSelection: function(containerEl, savedSel) {
		if (window.getSelection && document.createRange) {
			var charIndex = 0, range = document.createRange();
			range.setStart(containerEl, 0);
			range.collapse(true);
			var nodeStack = [containerEl], node, foundStart = false, stop = false;
			
			while (!stop && (node = nodeStack.pop())) {
				if (node.nodeType == 3) {
					var nextCharIndex = charIndex + node.length;
					if (!foundStart && savedSel.start >= charIndex && savedSel.start <= nextCharIndex) {
						range.setStart(node, savedSel.start - charIndex);
						foundStart = true;
					}
					if (foundStart && savedSel.end >= charIndex && savedSel.end <= nextCharIndex) {
						range.setEnd(node, savedSel.end - charIndex);
						stop = true;
					}
					charIndex = nextCharIndex;
				} else {
					var i = node.childNodes.length;
					while (i--) {
						nodeStack.push(node.childNodes[i]);
					};
				};
			}
			
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		}
		else {
			var textRange = document.body.createTextRange();
			textRange.moveToElementText(containerEl);
			textRange.collapse(true);
			textRange.moveEnd("character", savedSel.end);
			textRange.moveStart("character", savedSel.start);
			textRange.select();
		}
	},
	
	/**
	 * Initializes the 'copy quote' element.
	 */
	_initCopyQuote: function() {
		this._copyQuote = $('#quoteManagerCopy');
		if (!this._copyQuote.length) {
			this._copyQuote = $('<div id="quoteManagerCopy" class="balloonTooltip"><span>' + WCF.Language.get('wcf.message.quote.quoteSelected') + '</span><span class="pointer"><span></span></span></div>').hide().appendTo(document.body);
			this._copyQuote.click($.proxy(this._saveQuote, this));
		}
	},
	
	/**
	 * Returns the text selection.
	 * 
	 * @return	object
	 */
	_getSelectedText: function() {
		if (window.getSelection) { // Opera, Firefox, Safari, Chrome, IE 9+
			return window.getSelection();
		}
		else if (document.getSelection) { // Opera, Firefox, Safari, Chrome, IE 9+
			return document.getSelection();
		}
		else if (document.selection) { // IE 8
			return document.selection.createRange().text;
		}
		
		return '';
	},
	
	/**
	 * Saves a full quote.
	 * 
	 * @param	object		event
	 */
	_saveFullQuote: function(event) {
		var $listItem = $(event.currentTarget);
		
		this._proxy.setOption('data', {
			actionName: 'saveFullQuote',
			className: this._className,
			interfaceName: 'wcf\\data\\IMessageQuoteAction',
			objectIDs: [ $listItem.data('objectID') ]
		});
		this._proxy.sendRequest();
		
		// mark element as quoted
		if ($listItem.data('isQuoted')) {
			$listItem.data('isQuoted', false).children('a').removeClass('active');
		}
		else {
			$listItem.data('isQuoted', true).children('a').addClass('active');
		}
		
		// discard event
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Saves a quote.
	 */
	_saveQuote: function() {
		this._proxy.setOption('data', {
			actionName: 'saveQuote',
			className: this._className,
			interfaceName: 'wcf\\data\\IMessageQuoteAction',
			objectIDs: [ this._objectID ],
			parameters: {
				message: this._message
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.count !== undefined) {
			var $fullQuoteObjectIDs = (data.fullQuoteObjectIDs !== undefined) ? data.fullQuoteObjectIDs : { };
			this._quoteManager.updateCount(data.returnValues.count, $fullQuoteObjectIDs);
		}
	},
	
	/**
	 * Updates the full quote data for all matching objects.
	 * 
	 * @param	array<integer>		$objectIDs
	 */
	updateFullQuoteObjectIDs: function(objectIDs) {
		for (var $containerID in this._containers) {
			this._containers[$containerID].find('.jsQuoteMessage').each(function(index, button) {
				// reset all markings
				var $button = $(button).data('isQuoted', 0);
				$button.children('a').removeClass('active');
				
				// mark as active
				if (WCF.inArray($button.data('objectID'), objectIDs)) {
					$button.data('isQuoted', 1).children('a').addClass('active');
				}
			});
		}
	}
});

/**
 * Manages stored quotes.
 * 
 * @param	integer		count
 */
WCF.Message.Quote.Manager = Class.extend({
	/**
	 * list of form buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * number of stored quotes
	 * @var	integer
	 */
	_count: 0,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Redactor element
	 * @var	jQuery
	 */
	_editorElement: null,
	
	/**
	 * alternative Redactor element
	 * @var	jQuery
	 */
	_editorElementAlternative: null,
	
	/**
	 * form element
	 * @var	jQuery
	 */
	_form: null,
	
	/**
	 * list of quote handlers
	 * @var	object
	 */
	_handlers: { },
	
	/**
	 * true, if an up-to-date template exists
	 * @var	boolean
	 */
	_hasTemplate: false,
	
	/**
	 * true, if related quotes should be inserted
	 * @var	boolean
	 */
	_insertQuotes: true,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of quotes to remove upon submit
	 * @var	array<string>
	 */
	_removeOnSubmit: [ ],
	
	/**
	 * show quotes element
	 * @var	jQuery
	 */
	_showQuotes: null,
	
	/**
	 * allow pasting
	 * @var	boolean
	 */
	_supportPaste: false,
	
	/**
	 * Initializes the quote manager.
	 * 
	 * @param	integer		count
	 * @param	string		elementID
	 * @param	boolean		supportPaste
	 * @param	array<string>	removeOnSubmit
	 */
	init: function(count, elementID, supportPaste, removeOnSubmit) {
		this._buttons = {
			insert: null,
			remove: null
		};
		this._count = parseInt(count) || 0;
		this._dialog = null;
		this._editorElement = null;
		this._editorElementAlternative = null;
		this._form = null;
		this._handlers = { };
		this._hasTemplate = false;
		this._insertQuotes = true;
		this._removeOnSubmit = [ ];
		this._showQuotes = null;
		this._supportPaste = false;
		
		if (elementID) {
			this._editorElement = $('#' + elementID);
			if (this._editorElement.length) {
				this._supportPaste = true;
				
				// get surrounding form-tag
				this._form = this._editorElement.parents('form:eq(0)');
				if (this._form.length) {
					this._form.submit($.proxy(this._submit, this));
					this._removeOnSubmit = removeOnSubmit || [ ];
				}
				else {
					this._form = null;
					
					// allow override
					this._supportPaste = (supportPaste === true) ? true : false;
				}
			}
		}
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php/MessageQuote/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		this._toggleShowQuotes();
	},
	
	/**
	 * Sets an alternative editor element on runtime.
	 * 
	 * @param	jQuery		element
	 */
	setAlternativeEditor: function(element) {
		this._editorElementAlternative = element;
	},
	
	/**
	 * Clears alternative editor element.
	 */
	clearAlternativeEditor: function() {
		this._editorElementAlternative = null;
	},
	
	/**
	 * Registers a quote handler.
	 * 
	 * @param	string				objectType
	 * @param	WCF.Message.Quote.Handler	handler
	 */
	register: function(objectType, handler) {
		this._handlers[objectType] = handler;
	},
	
	/**
	 * Updates number of stored quotes.
	 * 
	 * @param	integer		count
	 * @param	object		fullQuoteObjectIDs
	 */
	updateCount: function(count, fullQuoteObjectIDs) {
		this._count = parseInt(count) || 0;
		
		this._toggleShowQuotes();
		
		// update full quote ids of handlers
		for (var $objectType in this._handlers) {
			if (fullQuoteObjectIDs[$objectType]) {
				this._handlers[$objectType].updateFullQuoteObjectIDs(fullQuoteObjectIDs[$objectType]);
			}
		}
	},
	
	/**
	 * Inserts all associated quotes upon first time using quick reply.
	 * 
	 * @param	string		className
	 * @param	integer		parentObjectID
	 * @param	object		callback
	 */
	insertQuotes: function(className, parentObjectID, callback) {
		if (!this._insertQuotes) {
			this._insertQuotes = true;
			
			return;
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'getRenderedQuotes',
				className: className,
				interfaceName: 'wcf\\data\\IMessageQuoteAction',
				parameters: {
					parentObjectID: parentObjectID
				}
			},
			success: callback
		});
	},
	
	/**
	 * Toggles the display of the 'Show quotes' button
	 */
	_toggleShowQuotes: function() {
		if (!this._count) {
			if (this._showQuotes !== null) {
				this._showQuotes.hide();
			}
		}
		else {
			if (this._showQuotes === null) {
				this._showQuotes = $('#showQuotes');
				if (!this._showQuotes.length) {
					this._showQuotes = $('<div id="showQuotes" class="balloonTooltip" />').click($.proxy(this._click, this)).appendTo(document.body);
				}
			}
			
			var $text = WCF.Language.get('wcf.message.quote.showQuotes').replace(/#count#/, this._count);
			this._showQuotes.text($text).show();
		}
		
		this._hasTemplate = false;
	},
	
	/**
	 * Handles clicks on 'Show quotes'.
	 */
	_click: function() {
		if (this._hasTemplate) {
			this._dialog.wcfDialog('open');
		}
		else {
			this._proxy.showLoadingOverlayOnce();
			
			this._proxy.setOption('data', {
				actionName: 'getQuotes',
				supportPaste: this._supportPaste
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Renders the dialog.
	 * 
	 * @param	string		template
	 */
	renderDialog: function(template) {
		// create dialog if not exists
		if (this._dialog === null) {
			this._dialog = $('#messageQuoteList');
			if (!this._dialog.length) {
				this._dialog = $('<div id="messageQuoteList" />').hide().appendTo(document.body);
			}
		}
		
		// add template
		this._dialog.html(template);
		
		// add 'insert' and 'delete' buttons
		var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
		if (this._supportPaste) this._buttons.insert = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.message.quote.insertAllQuotes') + '</button>').click($.proxy(this._insertSelected, this)).appendTo($formSubmit);
		this._buttons.remove = $('<button>' + WCF.Language.get('wcf.message.quote.removeAllQuotes') + '</button>').click($.proxy(this._removeSelected, this)).appendTo($formSubmit);
		
		// show dialog
		this._dialog.wcfDialog({
			title: WCF.Language.get('wcf.message.quote.manageQuotes')
		});
		this._dialog.wcfDialog('render');
		this._hasTemplate = true;
		
		// bind event listener
		var $insertQuoteButtons = this._dialog.find('.jsInsertQuote');
		if (this._supportPaste) {
			$insertQuoteButtons.click($.proxy(this._insertQuote, this));
		}
		else {
			$insertQuoteButtons.hide();
		}
		
		this._dialog.find('input.jsCheckbox').change($.proxy(this._changeButtons, this));
		
		// mark quotes for removal
		if (this._removeOnSubmit.length) {
			var self = this;
			this._dialog.find('input.jsRemoveQuote').each(function(index, input) {
				var $input = $(input).change($.proxy(this._change, this));
				
				// mark for deletion
				if (WCF.inArray($input.parent('li').attr('data-quote-id'), self._removeOnSubmit)) {
					$input.attr('checked', 'checked');
				}
			});
		}
	},
	
	/**
	 * Updates button labels if a checkbox is checked or unchecked.
	 */
	_changeButtons: function() {
		// selection
		if (this._dialog.find('input.jsCheckbox:checked').length) {
			if (this._supportPaste) this._buttons.insert.html(WCF.Language.get('wcf.message.quote.insertSelectedQuotes'));
			this._buttons.remove.html(WCF.Language.get('wcf.message.quote.removeSelectedQuotes'));
		}
		else {
			// no selection, pick all
			if (this._supportPaste) this._buttons.insert.html(WCF.Language.get('wcf.message.quote.insertAllQuotes'));
			this._buttons.remove.html(WCF.Language.get('wcf.message.quote.removeAllQuotes'));
		}
	},
	
	/**
	 * Checks for change event on delete-checkboxes.
	 * 
	 * @param	object		event
	 */
	_change: function(event) {
		var $input = $(event.currentTarget);
		var $quoteID = $input.parent('li').attr('data-quote-id');
		
		if ($input.prop('checked')) {
			this._removeOnSubmit.push($quoteID);
		}
		else {
			for (var $index in this._removeOnSubmit) {
				if (this._removeOnSubmit[$index] == $quoteID) {
					delete this._removeOnSubmit[$index];
					break;
				}
			}
		}
	},
	
	/**
	 * Inserts the selected quotes.
	 */
	_insertSelected: function() {
		if (this._editorElementAlternative === null) {
			var $api = $('.jsQuickReply:eq(0)').data('__api');
			if ($api && !$api.getContainer().is(':visible')) {
				this._insertQuotes = false;
				$api.click(null);
			}
		}
		
		if (!this._dialog.find('input.jsCheckbox:checked').length) {
			this._dialog.find('input.jsCheckbox').prop('checked', 'checked');
		}
		
		// insert all quotes
		this._dialog.find('input.jsCheckbox:checked').each($.proxy(function(index, input) {
			this._insertQuote(null, input);
		}, this));
		
		// close dialog
		this._dialog.wcfDialog('close');
	},
	
	/**
	 * Inserts a quote.
	 * 
	 * @param	object		event
	 * @param	object		inputElement
	 */
	_insertQuote: function(event, inputElement) {
		if (event !== null && this._editorElementAlternative === null) {
			var $api = $('.jsQuickReply:eq(0)').data('__api');
			if ($api && !$api.getContainer().is(':visible')) {
				this._insertQuotes = false;
				$api.click(null);
			}
		}
		
		var $listItem = (event === null) ? $(inputElement).parents('li') : $(event.currentTarget).parents('li');
		var $quote = $.trim($listItem.children('div.jsFullQuote').text());
		var $message = $listItem.parents('article.message');
		
		// build quote tag
		$quote = "[quote='" + $message.attr('data-username') + "','" + $message.data('link') + "']" + $quote + "[/quote]";
		
		// insert into editor
		if ($.browser.redactor) {
			if (this._editorElementAlternative === null) {
				this._editorElement.redactor('insertDynamic', $quote);
			}
			else {
				this._editorElementAlternative.redactor('insertDynamic', $quote);
			}
		}
		else {
			// plain textarea
			var $textarea = (this._editorElementAlternative === null) ? this._editorElement : this._editorElementAlternative;
			var $value = $textarea.val();
			$quote += "\n\n";
			if ($value.length == 0) {
				$textarea.val($quote);
			}
			else {
				var $position = $textarea.getCaret();
				$textarea.val( $value.substr(0, $position) + $quote + $value.substr($position) );
			}
		}
		
		// remove quote upon submit or upon request
		this._removeOnSubmit.push($listItem.attr('data-quote-id'));
		
		// close dialog
		if (event !== null) {
			this._dialog.wcfDialog('close');
		}
	},
	
	/**
	 * Removes selected quotes.
	 */
	_removeSelected: function() {
		if (!this._dialog.find('input.jsCheckbox:checked').length) {
			this._dialog.find('input.jsCheckbox').prop('checked', 'checked');
		}
		
		var $quoteIDs = [ ];
		this._dialog.find('input.jsCheckbox:checked').each(function(index, input) {
			$quoteIDs.push($(input).parents('li').attr('data-quote-id'));
		});
		
		if ($quoteIDs.length) {
			// get object types
			var $objectTypes = [ ];
			for (var $objectType in this._handlers) {
				$objectTypes.push($objectType);
			}
			
			this._proxy.setOption('data', {
				actionName: 'remove',
				getFullQuoteObjectIDs: this._handlers.length > 0,
				objectTypes: $objectTypes,
				quoteIDs: $quoteIDs
			});
			this._proxy.sendRequest();
			
			this._dialog.wcfDialog('close');
		}
	},
	
	/**
	 * Appends list of quote ids to remove after successful submit.
	 */
	_submit: function() {
		if (this._supportPaste && this._removeOnSubmit.length > 0) {
			var $formSubmit = this._form.find('.formSubmit');
			for (var $i in this._removeOnSubmit) {
				$('<input type="hidden" name="__removeQuoteIDs[]" value="' + this._removeOnSubmit[$i] + '" />').appendTo($formSubmit);
			}
		}
	},
	
	/**
	 * Returns a list of quote ids marked for removal.
	 * 
	 * @return	array<integer>
	 */
	getQuotesMarkedForRemoval: function() {
		return this._removeOnSubmit;
	},
	
	/**
	 * Marks quote ids for removal.
	 */
	markQuotesForRemoval: function() {
		if (this._removeOnSubmit.length) {
			this._proxy.setOption('data', {
				actionName: 'markForRemoval',
				quoteIDs: this._removeOnSubmit
			});
			this._proxy.suppressErrors();
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Removes all marked quote ids.
	 */
	removeMarkedQuotes: function() {
		if (this._removeOnSubmit.length) {
			this._proxy.setOption('data', {
				actionName: 'removeMarkedQuotes',
				getFullQuoteObjectIDs: this._handlers.length > 0
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Counts stored quotes.
	 */
	countQuotes: function() {
		var $objectTypes = [ ];
		for (var $objectType in this._handlers) {
			$objectTypes.push($objectType);
		}
		
		this._proxy.setOption('data', {
			actionName: 'count',
			getFullQuoteObjectIDs: this._handlers.length > 0,
			objectTypes: $objectTypes
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data === null) {
			return;
		}
		
		if (data.count !== undefined) {
			var $fullQuoteObjectIDs = (data.fullQuoteObjectIDs !== undefined) ? data.fullQuoteObjectIDs : { };
			this.updateCount(data.count, $fullQuoteObjectIDs);
		}
		
		if (data.template !== undefined) {
			if ($.trim(data.template) == '') {
				this.updateCount(0, { });
			}
			else {
				this.renderDialog(data.template);
			}
		}
	}
});

/**
 * Namespace for message sharing related classes.
 */
WCF.Message.Share = { };

/**
 * Displays a dialog overlay for permalinks.
 */
WCF.Message.Share.Content = Class.extend({
	/**
	 * list of cached templates
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Initializes the WCF.Message.Share.Content class.
	 */
	init: function() {
		this._cache = { };
		this._dialog = null;
		
		this._initLinks();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.Share.Content', $.proxy(this._initLinks, this));
	},
	
	/**
	 * Initializes share links.
	 */
	_initLinks: function() {
		$('a.jsButtonShare').removeClass('jsButtonShare').click($.proxy(this._click, this));
	},
	
	/**
	 * Displays links to share this content.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		event.preventDefault();
		
		var $target = $(event.currentTarget);
		var $link = $target.prop('href');
		var $title = ($target.data('linkTitle') ? $target.data('linkTitle') : $link);
		var $key = $link.hashCode();
		if (this._cache[$key] === undefined) {
			// remove dialog contents
			var $dialogInitialized = false;
			if (this._dialog === null) {
				this._dialog = $('<div />').hide().appendTo(document.body);
				$dialogInitialized = true;
			}
			else {
				this._dialog.empty();
			}
			
			// permalink (plain text)
			var $fieldset = $('<fieldset><legend><label for="__sharePermalink">' + WCF.Language.get('wcf.message.share.permalink') + '</label></legend></fieldset>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalink" class="long" readonly="readonly" />').attr('value', $link).appendTo($fieldset);
			
			// permalink (BBCode)
			var $fieldset = $('<fieldset><legend><label for="__sharePermalinkBBCode">' + WCF.Language.get('wcf.message.share.permalink.bbcode') + '</label></legend></fieldset>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalinkBBCode" class="long" readonly="readonly" />').attr('value', '[url=\'' + $link + '\']' + $title + '[/url]').appendTo($fieldset);
			
			// permalink (HTML)
			var $fieldset = $('<fieldset><legend><label for="__sharePermalinkHTML">' + WCF.Language.get('wcf.message.share.permalink.html') + '</label></legend></fieldset>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalinkHTML" class="long" readonly="readonly" />').attr('value', '<a href="' + $link + '">' + WCF.String.escapeHTML($title) + '</a>').appendTo($fieldset);
			
			this._cache[$key] = this._dialog.html();
			
			if ($dialogInitialized) {
				this._dialog.wcfDialog({
					title: WCF.Language.get('wcf.message.share')
				});
			}
			else {
				this._dialog.wcfDialog('open');
			}
		}
		else {
			this._dialog.html(this._cache[$key]).wcfDialog('open');
		}
		
		this._enableSelection();
	},
	
	/**
	 * Enables text selection.
	 */
	_enableSelection: function() {
		var $inputElements = this._dialog.find('input').click(function() { $(this).select(); });
		
		// Safari on iOS can only select the text if it is not readonly and setSelectionRange() is used
		if (navigator.userAgent.match(/iP(ad|hone|od)/)) {
			$inputElements.keydown(function() { return false; }).removeAttr('readonly').click(function() { this.setSelectionRange(0, 9999); });
		}
	}
});

/**
 * Provides buttons to share a page through multiple social community sites.
 * 
 * @param	boolean		fetchObjectCount
 */
WCF.Message.Share.Page = Class.extend({
	/**
	 * list of share buttons
	 * @var	object
	 */
	_ui: { },
	
	/**
	 * page description
	 * @var	string
	 */
	_pageDescription: '',
	
	/**
	 * canonical page URL
	 * @var	string
	 */
	_pageURL: '',
	
	/**
	 * Initializes the WCF.Message.Share.Page class.
	 * 
	 * @param	boolean		fetchObjectCount
	 */
	init: function(fetchObjectCount) {
		this._pageDescription = encodeURIComponent($('meta[property="og:title"]').prop('content'));
		this._pageURL = encodeURIComponent($('meta[property="og:url"]').prop('content'));
		
		var $container = $('.messageShareButtons');
		this._ui = {
			facebook: $container.find('.jsShareFacebook'),
			google: $container.find('.jsShareGoogle'),
			reddit: $container.find('.jsShareReddit'),
			twitter: $container.find('.jsShareTwitter')
		};
		
		this._ui.facebook.children('a').click($.proxy(this._shareFacebook, this));
		this._ui.google.children('a').click($.proxy(this._shareGoogle, this));
		this._ui.reddit.children('a').click($.proxy(this._shareReddit, this));
		this._ui.twitter.children('a').click($.proxy(this._shareTwitter, this));
		
		if (fetchObjectCount === true) {
			this._fetchFacebook();
			this._fetchTwitter();
			this._fetchReddit();
		}
	},
	
	/**
	 * Shares current page to selected social community site.
	 * 
	 * @param	string		objectName
	 * @param	string		url
	 * @param	boolean		appendURL
	 */
	_share: function(objectName, url, appendURL) {
		window.open(url.replace(/{pageURL}/, this._pageURL).replace(/{text}/, this._pageDescription + (appendURL ? " " + this._pageURL : "")), 'height=600,width=600');
	},
	
	/**
	 * Shares current page with Facebook.
	 */
	_shareFacebook: function() {
		this._share('facebook', 'https://www.facebook.com/sharer.php?u={pageURL}&t={text}', true);
	},
	
	/**
	 * Shares current page with Google Plus.
	 */
	_shareGoogle: function() {
		this._share('google', 'https://plus.google.com/share?url={pageURL}', true);
	},
	
	/**
	 * Shares current page with Reddit.
	 */
	_shareReddit: function() {
		this._share('reddit', 'https://ssl.reddit.com/submit?url={pageURL}', true);
	},
	
	/**
	 * Shares current page with Twitter.
	 */
	_shareTwitter: function() {
		this._share('twitter', 'https://twitter.com/share?url={pageURL}&text={text}', false);
	},
	
	/**
	 * Fetches share count from a social community site.
	 * 
	 * @param	string		url
	 * @param	object		callback
	 * @param	string		callbackName
	 */
	_fetchCount: function(url, callback, callbackName) {
		var $options = {
			autoSend: true,
			dataType: 'jsonp',
			showLoadingOverlay: false,
			success: callback,
			suppressErrors: true,
			type: 'GET',
			url: url.replace(/{pageURL}/, this._pageURL)
		};
		if (callbackName) {
			$options.jsonp = callbackName;
		}
		
		new WCF.Action.Proxy($options);
	},
	
	/**
	 * Fetches number of Facebook likes.
	 */
	_fetchFacebook: function() {
		this._fetchCount('https://graph.facebook.com/?id={pageURL}', $.proxy(function(data) {
			if (data.shares) {
				this._ui.facebook.children('span.badge').show().text(data.shares);
			}
		}, this));
	},
	
	/**
	 * Fetches tweet count from Twitter.
	 */
	_fetchTwitter: function() {
		if (window.location.protocol.match(/^https/)) return;
		
		this._fetchCount('http://urls.api.twitter.com/1/urls/count.json?url={pageURL}', $.proxy(function(data) {
			if (data.count) {
				this._ui.twitter.children('span.badge').show().text(data.count);
			}
		}, this));
	},
	
	/**
	 * Fetches cumulative vote sum from Reddit.
	 */
	_fetchReddit: function() {
		if (window.location.protocol.match(/^https/)) return;
		
		this._fetchCount('http://www.reddit.com/api/info.json?url={pageURL}', $.proxy(function(data) {
			if (data.data.children.length) {
				this._ui.reddit.children('span.badge').show().text(data.data.children[0].data.score);
			}
		}, this), 'jsonp');
	}
});

/**
 * Handles user mention suggestions in Redactor instances.
 * 
 * Important: Objects of this class have to be created before the CKEditor
 * is initialized!
 */
WCF.Message.UserMention = Class.extend({
	/**
	 * current caret position
	 * @var	DOMRange
	 */
	_caretPosition: null,
	
	/**
	 * name of the class used to get the user suggestions
	 * @var	string
	 */
	_className: 'wcf\\data\\user\\UserAction',
	
	/**
	 * dropdown object
	 * @var	jQuery
	 */
	_dropdown: null,
	
	/**
	 * dropdown menu object
	 * @var	jQuery
	 */
	_dropdownMenu: null,
	
	/**
	 * suggestion item index, -1 if none is selected
	 * @var	integer
	 */
	_itemIndex: -1,
	
	/**
	 * line height
	 * @var	integer
	 */
	_lineHeight: null,
	
	/**
	 * current beginning of the mentioning
	 * @var	string
	 */
	_mentionStart: '',
	
	/**
	 * redactor instance object
	 * @var	$.Redactor
	 */
	_redactor: null,
	
	/**
	 * Initalizes user suggestions for the CKEditor with the given textarea id.
	 * 
	 * @param	string		wysiwygSelector
	 */
	init: function(wysiwygSelector) {
		this._textarea = $('#' + wysiwygSelector);
		this._redactor = this._textarea.redactor('getObject');
		
		this._redactor.setOption('keyupCallback', $.proxy(this._keyup, this));
		this._redactor.setOption('wkeydownCallback', $.proxy(this._keydown, this));
		
		this._dropdown = this._textarea.redactor('getEditor');
		this._dropdownMenu = $('<ul class="dropdownMenu userSuggestionList" />').appendTo(this._textarea.parent());
		WCF.Dropdown.initDropdownFragment(this._dropdown, this._dropdownMenu);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Clears the suggestion list.
	 */
	_clearList: function() {
		this._hideList();
		
		this._dropdownMenu.empty();
	},
	
	/**
	 * Handles a click on a list item suggesting a username.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		// restore caret position
		this._redactor.replaceRangesWith(this._caretPosition);
		
		this._setUsername($(event.currentTarget).data('username'));
	},
	
	/**
	 * Creates an item in the suggestion list with the given data.
	 * 
	 * @return	object
	 */
	_createListItem: function(listItemData) {
		var $listItem = $('<li />').data('username', listItemData.label).click($.proxy(this._click, this)).appendTo(this._dropdownMenu);
		
		var $box16 = $('<div />').addClass('box16').appendTo($listItem);
		$box16.append($(listItemData.icon).addClass('framed'));
		$box16.append($('<div />').append($('<span />').text(listItemData.label)));
	},
	
	/**
	 * Returns the offsets used to set the position of the user suggestion
	 * dropdown.
	 * 
	 * @return	object
	 */
	_getDropdownMenuPosition: function() {
		var $orgRange = getSelection().getRangeAt(0).cloneRange();
		
		// mark the entire text, starting from the '@' to the current cursor position
		var $newRange = document.createRange();
		$newRange.setStart($orgRange.startContainer, $orgRange.startOffset - (this._mentionStart.length + 1));
		$newRange.setEnd($orgRange.startContainer, $orgRange.startOffset);
		
		this._redactor.replaceRangesWith($newRange);
		
		// get the offsets of the bounding box of current text selection
		var $range = getSelection().getRangeAt(0);
		var $rect = $range.getBoundingClientRect();
		var $window = $(window);
		var $offsets = {
			top: Math.round($rect.bottom) + $window.scrollTop(),
			left: Math.round($rect.left) + $window.scrollLeft()
		};
		
		if (this._lineHeight === null) {
			this._lineHeight = Math.round($rect.bottom - $rect.top);
		}
		
		// restore caret position
		this._redactor.replaceRangesWith($orgRange);
		this._caretPosition = $orgRange;
		
		return $offsets;
	},
	
	/**
	 * Replaces the started mentioning with a chosen username.
	 */
	_setUsername: function(username) {
		var $orgRange = getSelection().getRangeAt(0).cloneRange();
		
		// allow redactor to undo this
		this._redactor.bufferSet();
		
		var $newRange = document.createRange();
		$newRange.setStart($orgRange.startContainer, $orgRange.startOffset - (this._mentionStart.length + 1));
		$newRange.setEnd($orgRange.startContainer, $orgRange.startOffset);
		
		this._redactor.replaceRangesWith($newRange);
		
		var $range = getSelection().getRangeAt(0);
		$range.deleteContents();
		$range.collapse(true);
		
		// insert username
		if (username.indexOf("'") !== -1) {
			username = username.replace(/'/g, "''");
			username = "'" + username + "'";
		}
		else if (username.indexOf(' ') !== -1) {
			username = "'" + username + "'";
		}
		
		// use native API to prevent issues in Internet Explorer
		var $text = document.createTextNode('@' + username);
		$range.insertNode($text);
		
		var $newRange = document.createRange();
		$newRange.setStart($text, username.length + 1);
		$newRange.setEnd($text, username.length + 1);
		
		this._redactor.replaceRangesWith($newRange);
		
		this._hideList();
	},
	
	/**
	 * Returns the parameters for the AJAX request.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return {
			data: {
				includeUserGroups: false,
				searchString: this._mentionStart
			}
		};
	},
	
	/**
	 * Returns the relevant text in front of the caret in the current line.
	 * 
	 * @return	string
	 */
	_getTextLineInFrontOfCaret: function() {
		// if text is marked, user suggestions are disabled
		if (this._redactor.getSelectionHtml().length) {
			return '';
		}
		
		var $range = this._redactor.getSelection().getRangeAt(0);
		var $text = $range.startContainer.textContent.substr(0, $range.startOffset);
		
		// remove unicode zero width space and non-breaking space
		var $textBackup = $text;
		$text = '';
		for (var $i = 0; $i < $textBackup.length; $i++) {
			var $byte = $textBackup.charCodeAt($i).toString(16);
			if ($byte != '200b' && !/\s/.test($textBackup[$i])) {
				if ($textBackup[$i] === '@' && $i && /\s/.test($textBackup[$i - 1])) {
					$text = '';
				}
				
				$text += $textBackup[$i];
			}
			else {
				$text = '';
			}
		}
		
		return $text;
	},
	
	/**
	 * Hides the suggestion list.
	 */
	_hideList: function() {
		this._dropdown.removeClass('dropdownOpen');
		this._dropdownMenu.removeClass('dropdownOpen');
		
		this._itemIndex = -1;
	},
	
	/**
	 * Handles the keydown event to check if the user starts mentioning someone.
	 * 
	 * @param	object		event
	 */
	_keydown: function(event) {
		if (this._redactor.inPlainMode()) {
			return true;
		}
		
		if (this._dropdownMenu.is(':visible')) {
			switch (event.which) {
				case $.ui.keyCode.ENTER:
					event.preventDefault();
					
					this._dropdownMenu.children('li').eq(this._itemIndex).trigger('click');
					
					return false;
				break;
				
				case $.ui.keyCode.UP:
					event.preventDefault();
					
					this._selectItem(this._itemIndex - 1);
					
					return false;
				break;
				
				case $.ui.keyCode.DOWN:
					event.preventDefault();
					
					this._selectItem(this._itemIndex + 1);
					
					return false;
				break;
			}
		}
		
		return true;
	},
	
	/**
	 * Handles the keyup event to check if the user starts mentioning someone.
	 * 
	 * @param	object		event
	 */
	_keyup: function(event) {
		if (this._redactor.inPlainMode()) {
			return true;
		}
		
		// ignore enter key up event
		if (event.which === $.ui.keyCode.ENTER) {
			return;
		}
		
		// ignore event if suggestion list and user pressed enter, arrow up or arrow down
		if (this._dropdownMenu.is(':visible') && event.which in { 13:1, 38:1, 40:1 }) {
			return;
		}
		
		var $currentText = this._getTextLineInFrontOfCaret();
		if ($currentText) {
			var $match = $currentText.match(/@([^,]{3,})$/);
			if ($match) {
				// if mentioning is at text begin or there's a whitespace character
				// before the '@', everything is fine
				if (!$match.index || $currentText[$match.index - 1].match(/\s/)) {
					this._mentionStart = $match[1];
					
					this._proxy.setOption('data', {
						actionName: 'getSearchResultList',
						className: this._className,
						interfaceName: 'wcf\\data\\ISearchAction',
						parameters: this._getParameters()
					});
					this._proxy.sendRequest();
				}
			}
			else {
				this._hideList();
			}
		}
		else {
			this._hideList();
		}
	},
	
	/**
	 * Selects the suggestion with the given item index.
	 * 
	 * @param	integer		itemIndex
	 */
	_selectItem: function(itemIndex) {
		var $li = this._dropdownMenu.children('li');
		
		if (itemIndex < 0) {
			itemIndex = $li.length - 1;
		}
		else if (itemIndex + 1 > $li.length) {
			itemIndex = 0;
		}
		
		$li.removeClass('dropdownNavigationItem');
		$li.eq(itemIndex).addClass('dropdownNavigationItem');
		
		this._itemIndex = itemIndex;
	},
	
	/**
	 * Shows the suggestion list.
	 */
	_showList: function() {
		this._dropdown.addClass('dropdownOpen');
		this._dropdownMenu.addClass('dropdownOpen');
	},
	
	/**
	 * Evalutes user suggestion-AJAX request results.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._clearList(false);
		
		if ($.getLength(data.returnValues)) {
			for (var $i in data.returnValues) {
				var $item = data.returnValues[$i];
				this._createListItem($item);
			}
			
			this._updateSuggestionListPosition();
			this._showList();
		}
	},
	
	/**
	 * Updates the position of the suggestion list.
	 */
	_updateSuggestionListPosition: function() {
		try {
			var $dropdownMenuPosition = this._getDropdownMenuPosition();
			$dropdownMenuPosition.top += 5; // add a little vertical gap
			
			this._dropdownMenu.css($dropdownMenuPosition);
			this._selectItem(0);
			
			if ($dropdownMenuPosition.top + this._dropdownMenu.outerHeight() + 10 > $(window).height() + $(document).scrollTop()) {
				this._dropdownMenu.addClass('dropdownArrowBottom');
				
				this._dropdownMenu.css({
					top: $dropdownMenuPosition.top - this._dropdownMenu.outerHeight() - 2 * this._lineHeight + 5
				});
			}
			else {
				this._dropdownMenu.removeClass('dropdownArrowBottom');
			}
		}
		catch (e) {
			// ignore errors that are caused by pressing enter to
			// often in a short period of time
		}
	}
});


// WCF.Moderation.js
/**
 * Namespace for moderation related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Moderation = { };

/**
 * Moderation queue management.
 * 
 * @param	integer		queueID
 * @param	string		redirectURL
 */
WCF.Moderation.Management = Class.extend({
	/**
	 * button selector
	 * @var	string
	 */
	_buttonSelector: '',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * list of templates for confirmation message by action name
	 * @var	object
	 */
	_confirmationTemplate: { },
	
	/**
	 * language item pattern
	 * @var	string
	 */
	_languageItem: '',
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * queue id
	 * @var	integer
	 */
	_queueID: 0,
	
	/**
	 * redirect URL
	 * @var	string
	 */
	_redirectURL: '',
	
	/**
	 * Initializes the moderation report management.
	 * 
	 * @param	integer		queueID
	 * @param	string		redirectURL
	 * @param	string		languageItem
	 */
	init: function(queueID, redirectURL, languageItem) {
		if (!this._buttonSelector) {
			console.debug("[WCF.Moderation.Management] Missing button selector, aborting.");
			return;
		}
		else if (!this._className) {
			console.debug("[WCF.Moderation.Management] Missing class name, aborting.");
			return;
		}
		
		this._queueID = queueID;
		this._redirectURL = redirectURL;
		this._languageItem = languageItem;
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$(this._buttonSelector).click($.proxy(this._click, this));
	},
	
	/**
	 * Handles clicks on the action buttons.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $actionName = $(event.currentTarget).wcfIdentify();
		var $innerTemplate = '';
		if (this._confirmationTemplate[$actionName]) {
			$innerTemplate = this._confirmationTemplate[$actionName];
		}
		
		WCF.System.Confirmation.show(WCF.Language.get(this._languageItem.replace(/{actionName}/, $actionName)), $.proxy(function(action) {
			if (action === 'confirm') {
				var $parameters = {
					actionName: $actionName,
					className: this._className,
					objectIDs: [ this._queueID ]
				};
				if (this._confirmationTemplate[$actionName]) {
					$parameters.parameters = { };
					$innerTemplate.find('input, textarea').each(function(index, element) {
						var $element = $(element);
						var $value = $element.val();
						if ($element.getTagName() === 'input' && $element.attr('type') === 'checkbox') {
							if (!$element.is(':checked')) {
								$value = null;
							}
						}
						
						if ($value !== null) {
							$parameters.parameters[$element.attr('name')] = $value;
						}
					});
				}
				
				this._proxy.setOption('data', $parameters);
				this._proxy.sendRequest();
				
				$(this._buttonSelector).disable();
			}
		}, this), { }, $innerTemplate);
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
		var self = this;
		$notification.show(function() {
			window.location = self._redirectURL;
		});
	}
});

/**
 * Namespace for activation related classes.
 */
WCF.Moderation.Activation = { };

/**
 * Manages disabled content within moderation.
 * 
 * @see	WCF.Moderation.Management
 */
WCF.Moderation.Activation.Management = WCF.Moderation.Management.extend({
	/**
	 * @see	WCF.Moderation.Management.init()
	 */
	init: function(queueID, redirectURL) {
		this._buttonSelector = '#enableContent, #removeContent';
		this._className = 'wcf\\data\\moderation\\queue\\ModerationQueueActivationAction';
		
		this._super(queueID, redirectURL, 'wcf.moderation.activation.{actionName}.confirmMessage');
	}
});

/**
 * Namespace for report related classes.
 */
WCF.Moderation.Report = { };

/**
 * Handles content report.
 * 
 * @param	string		objectType
 * @param	string		buttonSelector
 */
WCF.Moderation.Report.Content = Class.extend({
	/**
	 * list of buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * button selector
	 * @var	string
	 */
	_buttonSelector: '',
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * object id
	 * @var	integer
	 */
	_objectID: 0,
	
	/**
	 * object type name
	 * @var	string
	 */
	_objectType: '',
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Creates a new WCF.Moderation.Report object.
	 * 
	 * @param	string		objectType
	 * @param	string		buttonSelector
	 */
	init: function(objectType, buttonSelector) {
		this._objectType = objectType;
		this._buttonSelector = buttonSelector;
		
		this._buttons = { };
		this._notification = null;
		this._objectID = 0;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initButtons();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Moderation.Report' + this._objectType.hashCode(), $.proxy(this._initButtons, this));
	},
	
	/**
	 * Initializes the report feature for all matching buttons.
	 */
	_initButtons: function() {
		var self = this;
		$(this._buttonSelector).each(function(index, button) {
			var $button = $(button);
			var $buttonID = $button.wcfIdentify();
			
			if (!self._buttons[$buttonID]) {
				self._buttons[$buttonID] = $button;
				$button.click($.proxy(self._click, self));
			}
		});
	},
	
	/**
	 * Handles clicks on a report button.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._objectID = $(event.currentTarget).data('objectID');
		
		this._proxy.setOption('data', {
			actionName: 'prepareReport',
			className: 'wcf\\data\\moderation\\queue\\ModerationQueueReportAction',
			parameters: {
				objectID: this._objectID,
				objectType: this._objectType
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// object has been successfully reported
		if (data.returnValues.reported) {
			if (this._notification === null) {
				this._notification = new WCF.System.Notification(WCF.Language.get('wcf.moderation.report.success'));
			}
			
			// show success and close dialog
			this._dialog.wcfDialog('close');
			this._notification.show();
		}
		else if (data.returnValues.template) {
			// display template
			this._showDialog(data.returnValues.template);
			
			if (!data.returnValues.alreadyReported) {
				// bind event listener for buttons
				this._dialog.find('.jsSubmitReport').click($.proxy(this._submit, this));
			}
		}
	},
	
	/**
	 * Displays the dialog overlay.
	 * 
	 * @param	string		template
	 */
	_showDialog: function(template) {
		if (this._dialog === null) {
			this._dialog = $('#moderationReport');
			if (!this._dialog.length) {
				this._dialog = $('<div id="moderationReport" />').hide().appendTo(document.body);
			}
		}
		
		this._dialog.html(template).wcfDialog({
			title: WCF.Language.get('wcf.moderation.report.reportContent')
		}).wcfDialog('render');
	},
	
	/**
	 * Submits a report unless the textarea is empty.
	 */
	_submit: function() {
		var $text = this._dialog.find('.jsReportMessage').val();
		if ($text == '') {
			this._dialog.find('fieldset > dl').addClass('formError');
			
			if (!this._dialog.find('.innerError').length) {
				this._dialog.find('.jsReportMessage').after($('<small class="innerError">' + WCF.Language.get('wcf.global.form.error.empty') + "</small>"));;
			}
			
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: 'report',
			className: 'wcf\\data\\moderation\\queue\\ModerationQueueReportAction',
			parameters: {
				message: $text,
				objectID: this._objectID,
				objectType: this._objectType
			}
		});
		this._proxy.sendRequest();
	}
});

/**
 * Manages reported content within moderation.
 * 
 * @see	WCF.Moderation.Management
 */
WCF.Moderation.Report.Management = WCF.Moderation.Management.extend({
	/**
	 * @see	WCF.Moderation.Management.init()
	 */
	init: function(queueID, redirectURL) {
		this._buttonSelector = '#removeContent, #removeReport';
		this._className = 'wcf\\data\\moderation\\queue\\ModerationQueueReportAction';
		
		this._super(queueID, redirectURL, 'wcf.moderation.report.{actionName}.confirmMessage');
		
		this._confirmationTemplate.removeContent = $('<fieldset><dl><dt><label for="message">' + WCF.Language.get('wcf.moderation.report.removeContent.reason') + '</label></dt><dd><textarea name="message" id="message" cols="40" rows="3" /></dd></dl></fieldset>');
	}
});

/**
 * Provides a dropdown for user panel.
 * 
 * @see	WCF.UserPanel
 */
WCF.Moderation.UserPanel = WCF.UserPanel.extend({
	/**
	 * link to show all outstanding queues
	 * @var	string
	 */
	_showAllLink: '',
	
	/**
	 * link to deleted content list
	 * @var	string
	 */
	_deletedContentLink: '',
	
	/**
	 * @see	WCF.UserPanel.init()
	 */
	init: function(showAllLink, deletedContentLink) {
		this._noItems = 'wcf.moderation.noMoreItems';
		this._showAllLink = showAllLink;
		this._deletedContentLink = deletedContentLink;
		
		this._super('outstandingModeration');
	},
	
	/**
	 * @see	WCF.UserPanel._addDefaultItems()
	 */
	_addDefaultItems: function(dropdownMenu) {
		this._addDivider(dropdownMenu);
		$('<li><a href="' + this._showAllLink + '">' + WCF.Language.get('wcf.moderation.showAll') + '</a></li>').appendTo(dropdownMenu);
		this._addDivider(dropdownMenu);
		$('<li><a href="' + this._deletedContentLink + '">' + WCF.Language.get('wcf.moderation.showDeletedContent') + '</a></li>').appendTo(dropdownMenu);
	},
	
	/**
	 * @see	WCF.UserPanel._getParameters()
	 */
	_getParameters: function() {
		return {
			actionName: 'getOutstandingQueues',
			className: 'wcf\\data\\moderation\\queue\\ModerationQueueAction'
		};
	}
});


// WCF.Poll.js
/**
 * Namespace for poll-related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Poll = { };

/**
 * Handles poll option management.
 * 
 * @param	string		containerID
 * @param	array<object>	optionList
 */
WCF.Poll.Management = Class.extend({
	/**
	 * container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * number of options
	 * @var	integer
	 */
	_count: 0,
	
	/**
	 * width for input-elements
	 * @var	integer
	 */
	_inputSize: 0,
	
	/**
	 * maximum allowed number of options
	 * @var	integer
	 */
	_maxOptions: 0,
	
	/**
	 * Initializes the WCF.Poll.Management class.
	 * 
	 * @param	string		containerID
	 * @param	array<object>	optionList
	 * @param	integer		maxOptions
	 */
	init: function(containerID, optionList, maxOptions) {
		this._count = 0;
		this._maxOptions = maxOptions || -1;
		this._container = $('#' + containerID).children('ol:eq(0)');
		if (!this._container.length) {
			console.debug("[WCF.Poll.Management] Invalid container id given, aborting.");
			return;
		}
		
		optionList = optionList || [ ];
		this._createOptionList(optionList);
		
		// bind event listener
		$(window).resize($.proxy(this._resize, this));
		this._container.parents('form').submit($.proxy(this._submit, this));
		
		// init sorting
		new WCF.Sortable.List(containerID, '', undefined, undefined, true);
		
		// trigger resize event for field length calculation
		this._resize();
		
		// update size on tab select
		var $tabMenuContent = this._container.parents('.tabMenuContent:eq(0)');
		var $tabMenuContentID = $tabMenuContent.wcfIdentify();
		var self = this;
		$tabMenuContent.parents('.tabMenuContainer:eq(0)').on('wcftabsactivate', function(event, ui) {
			if (ui.newPanel.wcfIdentify() == $tabMenuContentID) {
				self._resize();
			}
		});
	},
	
	/**
	 * Creates the option list on init.
	 * 
	 * @param	array<object>		optionList
	 */
	_createOptionList: function(optionList) {
		for (var $i = 0, $length = optionList.length; $i < $length; $i++) {
			var $option = optionList[$i];
			this._createOption($option.optionValue, $option.optionID);
		}
		
		// add empty option
		this._createOption();
	},
	
	/**
	 * Creates a new option element.
	 * 
	 * @param	string		optionValue
	 * @param	integer		optionID
	 * @param	jQuery		insertAfter
	 */
	_createOption: function(optionValue, optionID, insertAfter) {
		optionValue = optionValue || '';
		optionID = parseInt(optionID) || 0;
		insertAfter = insertAfter || null;
		
		var $listItem = $('<li class="sortableNode" />').data('optionID', optionID);
		if (insertAfter === null) {
			$listItem.appendTo(this._container);
		}
		else {
			$listItem.insertAfter(insertAfter);
		}
		
		// insert buttons
		var $buttonContainer = $('<span class="sortableButtonContainer" />').appendTo($listItem);
		$('<span class="icon icon16 icon-plus jsTooltip jsAddOption pointer" title="' + WCF.Language.get('wcf.poll.button.addOption') + '" />').click($.proxy(this._addOption, this)).appendTo($buttonContainer);
		$('<span class="icon icon16 icon-remove jsTooltip jsDeleteOption pointer" title="' + WCF.Language.get('wcf.poll.button.removeOption') + '" />').click($.proxy(this._removeOption, this)).appendTo($buttonContainer);
		
		// insert input field
		var $input = $('<input type="text" value="' + optionValue + '" maxlength="255" />').css({ width: this._inputSize + "px" }).keydown($.proxy(this._keyDown, this)).appendTo($listItem);
		
		if (insertAfter !== null) {
			$input.focus();
		}
		
		WCF.DOMNodeInsertedHandler.execute();
		
		this._count++;
		if (this._count === this._maxOptions) {
			this._container.find('span.jsAddOption').removeClass('pointer').addClass('disabled');
		}
	},
	
	/**
	 * Handles key down events for option input field.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_keyDown: function(event) {
		// ignore every key except for [Enter]
		if (event.which !== 13) {
			return true;
		}
		
		$(event.currentTarget).prev('.sortableButtonContainer').children('.jsAddOption').trigger('click');
		
		event.preventDefault();
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Adds a new option after current one.
	 * 
	 * @param	object		event
	 */
	_addOption: function(event) {
		if (this._count === this._maxOptions) {
			return false;
		}
		
		var $listItem = $(event.currentTarget).parents('li');
		
		this._createOption(undefined, undefined, $listItem);
	},
	
	/**
	 * Removes an option.
	 * 
	 * @param	object		event
	 */
	_removeOption: function(event) {
		$(event.currentTarget).parents('li').remove();
		
		this._count--;
		this._container.find('span.jsAddOption').addClass('pointer').removeClass('disabled');
		
		if (this._container.children('li').length == 0) {
			this._createOption();
		}
	},
	
	/**
	 * Handles the 'resize'-event to adjust input-width.
	 */
	_resize: function() {
		var $containerWidth = this._container.innerWidth();
		
		// select first option to determine dimensions
		var $listItem = this._container.children('li:eq(0)');
		var $buttonWidth = $listItem.children('.sortableButtonContainer').outerWidth();
		var $inputSize = $containerWidth - $buttonWidth;
		
		if ($inputSize != this._inputSize) {
			this._inputSize = $inputSize;
			
			// update width of <input /> elements
			this._container.find('li > input').css({ width: this._inputSize + 'px' });
		}
	},
	
	/**
	 * Inserts hidden input elements storing the option values.
	 */
	_submit: function() {
		var $options = [ ];
		this._container.children('li').each(function(index, listItem) {
			var $listItem = $(listItem);
			var $optionValue = $.trim($listItem.children('input').val());
			
			// ignore empty values
			if ($optionValue != '') {
				$options.push({
					optionID: $listItem.data('optionID'),
					optionValue: $optionValue
				});
			}
		});
		
		// create hidden input fields
		if ($options.length) {
			var $formSubmit = this._container.parents('form').find('.formSubmit');
			
			for (var $i = 0, $length = $options.length; $i < $length; $i++) {
				var $option = $options[$i];
				$('<input type="hidden" name="pollOptions[' + $i + ']" />').val($option.optionID + '_' + $option.optionValue).appendTo($formSubmit);
			}
		}
	}
});

/**
 * Manages poll voting and result display.
 * 
 * @param	string		containerSelector
 */
WCF.Poll.Manager = Class.extend({
	/**
	 * template cache
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * list of permissions to view participants
	 * @var	object
	 */
	_canViewParticipants: { },
	
	/**
	 * list of permissions to view result
	 * @var	object
	 */
	_canViewResult: { },
	
	/**
	 * list of permissions
	 * @var	object
	 */
	_canVote: { },
	
	/**
	 * list of input elements per poll
	 * @var	object
	 */
	_inputElements: { },
	
	/**
	 * list of participant lists
	 * @var	object
	 */
	_participants: { },
	
	/**
	 * list of poll objects
	 * @var	object
	 */
	_polls: { },
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Intiailizes the poll manager.
	 * 
	 * @param	string		containerSelector
	 */
	init: function(containerSelector) {
		var $polls = $(containerSelector);
		if (!$polls.length) {
			console.debug("[WCF.Poll.Manager] Given selector '" + containerSelector + "' does not match, aborting.");
			return;
		}
		
		this._cache = { };
		this._canViewParticipants = { };
		this._canViewResult = { };
		this._inputElements = { };
		this._participants = { };
		this._polls = { };
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			url: 'index.php/Poll/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		// init polls
		var self = this;
		$polls.each(function(index, poll) {
			var $poll = $(poll);
			var $pollID = $poll.data('pollID');
			
			if (self._polls[$pollID] === undefined) {
				self._cache[$pollID] = {
					result: '',
					vote: ''
				};
				self._polls[$pollID] = $poll;
				
				self._canViewParticipants[$pollID] = ($poll.data('canViewParticipants')) ? true : false;
				self._canViewResult[$pollID] = ($poll.data('canViewResult')) ? true : false;
				self._canVote[$pollID] = ($poll.data('canVote')) ? true : false;
				
				self._bindListeners($pollID);
				
				if ($poll.data('inVote')) {
					self._prepareVote($pollID);
				}
				
				self._toggleButtons($pollID);
			}
		});
	},
	
	/**
	 * Bind event listeners for current poll id.
	 * 
	 * @param	integer		pollID
	 */
	_bindListeners: function(pollID) {
		this._polls[pollID].find('.jsButtonPollShowParticipants').data('pollID', pollID).click($.proxy(this._showParticipants, this));
		this._polls[pollID].find('.jsButtonPollShowResult').data('pollID', pollID).click($.proxy(this._showResult, this));
		this._polls[pollID].find('.jsButtonPollShowVote').data('pollID', pollID).click($.proxy(this._showVote, this));
		this._polls[pollID].find('.jsButtonPollVote').data('pollID', pollID).click($.proxy(this._vote, this));
	},
	
	/**
	 * Displays poll result template.
	 * 
	 * @param	object		event
	 * @param	integer		pollID
	 */
	_showResult: function(event, pollID) {
		var $pollID = (event === null) ? pollID : $(event.currentTarget).data('pollID');
		
		// user cannot see the results yet
		if (!this._canViewResult[$pollID]) {
			return;
		}
		
		// ignore request, we're within results already
		if (!this._polls[$pollID].data('inVote')) {
			return;
		}
		
		if (!this._cache[$pollID].result) {
			this._proxy.setOption('data', {
				actionName: 'getResult',
				pollID: $pollID
			});
			this._proxy.sendRequest();
		}
		else {
			// show results from cache
			this._polls[$pollID].find('.pollInnerContainer').html(this._cache[$pollID].result);
			
			// set vote state
			this._polls[$pollID].data('inVote', false);
			
			// toggle buttons
			this._toggleButtons($pollID);
		}
	},
	
	/**
	 * Displays a list of participants.
	 * 
	 * @param	object		event
	 */
	_showParticipants: function(event) {
		var $pollID = $(event.currentTarget).data('pollID');
		if (!this._participants[$pollID]) {
			this._participants[$pollID] = new WCF.User.List('wcf\\data\\poll\\PollAction', this._polls[$pollID].data('question'), { pollID: $pollID });
		}
		
		this._participants[$pollID].open();
	},
	
	/**
	 * Displays the vote template.
	 * 
	 * @param	object		event
	 * @param	integer		pollID
	 */
	_showVote: function(event, pollID) {
		var $pollID = (event === null) ? pollID : $(event.currentTarget).data('pollID');
		
		// user cannot vote (e.g. already voted or guest)
		if (!this._canVote[$pollID]) {
			return;
		}
		
		// ignore request, we're within vote already
		if (this._polls[$pollID].data('inVote')) {
			return;
		}
		
		if (!this._cache[$pollID].vote) {
			this._proxy.setOption('data', {
				actionName: 'getVote',
				pollID: $pollID
			});
			this._proxy.sendRequest();
		}
		else {
			// show vote from cache
			this._polls[$pollID].find('.pollInnerContainer').html(this._cache[$pollID].vote);
			
			// set vote state
			this._polls[$pollID].data('inVote', true);
			
			// bind event listener and toggle buttons
			this._prepareVote($pollID);
			this._toggleButtons($pollID);
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (!data || !data.actionName) {
			return;
		}
		
		var $pollID = data.pollID;
		
		// updating result template
		if (data.resultTemplate) {
			this._cache[$pollID].result = data.resultTemplate;
		}
		
		// updating vote template
		if (data.voteTemplate) {
			this._cache[$pollID].vote = data.voteTemplate;
		}
		
		switch (data.actionName) {
			case 'getResult':
				this._showResult(null, $pollID);
			break;
			
			case 'getVote':
				this._showVote(null, $pollID);
			break;
			
			case 'vote':
				// display results
				this._canViewResult[$pollID] = true;
				this._canVote[$pollID] = (data.canVote) ? true : false;
				this._showResult(null, $pollID);
			break;
		}
	},
	
	/**
	 * Binds event listener for vote template.
	 * 
	 * @param	integer		pollID
	 */
	_prepareVote: function(pollID) {
		this._polls[pollID].find('.jsButtonPollVote').disable();
		
		var $voteContainer = this._polls[pollID].find('.pollInnerContainer > .jsPollVote');
		var self = this;
		this._inputElements[pollID] = $voteContainer.find('input').change(function() { self._handleVoteButton(pollID); });
		this._handleVoteButton(pollID);
		
		var $maxVotes = $voteContainer.data('maxVotes');
		if (this._inputElements[pollID].filter('[type=checkbox]').length) {
			this._inputElements[pollID].change(function() { self._enforceMaxVotes(pollID, $maxVotes); });
			this._enforceMaxVotes(pollID, $maxVotes);
		}
	},
	
	/**
	 * Enforces max votes for input fields.
	 * 
	 * @param	integer		pollID
	 * @param	integer		maxVotes
	 */
	_enforceMaxVotes: function(pollID, maxVotes) {
		var $elements = this._inputElements[pollID];
		
		if ($elements.filter(':checked').length == maxVotes) {
			$elements.filter(':not(:checked)').disable();
		}
		else {
			$elements.enable();
		}
	},
	
	/**
	 * Enables or disable vote button.
	 * 
	 * @param	integer		pollID
	 */
	_handleVoteButton: function(pollID) {
		var $elements = this._inputElements[pollID];
		var $voteButton = this._polls[pollID].find('.jsButtonPollVote');
		
		if ($elements.filter(':checked').length) {
			$voteButton.enable();
		}
		else {
			$voteButton.disable();
		}
	},
	
	/**
	 * Toggles buttons for given poll id.
	 * 
	 * @param	integer		pollID
	 */
	_toggleButtons: function(pollID) {
		var $formSubmit = this._polls[pollID].children('.formSubmit');
		$formSubmit.find('.jsButtonPollShowParticipants, .jsButtonPollShowResult, .jsButtonPollShowVote, .jsButtonPollVote').hide();
		
		var $hideFormSubmit = true;
		if (this._polls[pollID].data('inVote')) {
			$hideFormSubmit = false;
			$formSubmit.find('.jsButtonPollVote').show();
			
			if (this._canViewResult[pollID]) {
				$formSubmit.find('.jsButtonPollShowResult').show();
			}
		}
		else {
			if (this._canVote[pollID]) {
				$hideFormSubmit = false;
				$formSubmit.find('.jsButtonPollShowVote').show();
			}
			
			if (this._canViewParticipants[pollID]) {
				$hideFormSubmit = false;
				$formSubmit.find('.jsButtonPollShowParticipants').show();
			}
		}
		
		if ($hideFormSubmit) {
			$formSubmit.hide();
		}
	},
	
	/**
	 * Executes a user's vote.
	 * 
	 * @param	object		event
	 */
	_vote: function(event) {
		var $pollID = $(event.currentTarget).data('pollID');
		
		// user cannot vote
		if (!this._canVote[$pollID]) {
			return;
		}
		
		// collect values
		var $optionIDs = [ ];
		this._inputElements[$pollID].each(function(index, input) {
			var $input = $(input);
			if ($input.is(':checked')) {
				$optionIDs.push($input.data('optionID'));
			}
		});
		
		if ($optionIDs.length) {
			this._proxy.setOption('data', {
				actionName: 'vote',
				optionIDs: $optionIDs,
				pollID: $pollID
			});
			this._proxy.sendRequest();
		}
	}
});


// WCF.Search.Message.js
/**
 * Namespace
 */
WCF.Search.Message = {};

/**
 * Provides quick search for search keywords.
 * 
 * @see	WCF.Search.Base
 */
WCF.Search.Message.KeywordList = WCF.Search.Base.extend({
	/**
	 * @see	WCF.Search.Base._className
	 */
	_className: 'wcf\\data\\search\\keyword\\SearchKeywordAction',
	
	/**
	 * dropdown divider
	 * @var	jQuery
	 */
	_divider: null,
	
	/**
	 * true, if submit should be forced
	 * @var	boolean
	 */
	_forceSubmit: false,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.Search.Message.KeywordList] The given callback is invalid, aborting.");
			return;
		}
		
		this._callback = callback;
		this._excludedSearchValues = [];
		if (excludedSearchValues) {
			this._excludedSearchValues = excludedSearchValues;
		}
		this._searchInput = $(searchInput).keyup($.proxy(this._keyUp, this)).keydown($.proxy(function(event) {
			// block form submit
			if (event.which === 13) {
				// ... unless there are no suggestions or suggestions are optional and none is selected
				if (this._itemCount && this._itemIndex !== -1) {
					event.preventDefault();
				}
			}
		}, this));
		
		var $dropdownMenu = WCF.Dropdown.getDropdownMenu(this._searchInput.parents('.dropdown').wcfIdentify());
		var $lastDivider = $dropdownMenu.find('li.dropdownDivider').last();
		this._divider = $('<li class="dropdownDivider" />').hide().insertBefore($lastDivider);
		this._list = $('<li class="dropdownList"><ul /></li>').hide().insertBefore($lastDivider).children('ul');
		
		// supress clicks on checkboxes
		$dropdownMenu.find('input, label').on('click', function(event) { event.stopPropagation(); });
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(item) {
		this._divider.show();
		this._list.parent().show();
		
		this._super(item);
	},
	
	/**
	 * @see	WCF.Search.Base._clearList()
	 */
	_clearList: function(clearSearchInput) {
		if (clearSearchInput) {
			this._searchInput.val('');
		}
		
		this._divider.hide();
		this._list.empty().parent().hide();
		
		WCF.CloseOverlayHandler.removeCallback('WCF.Search.Base');
		
		// reset item navigation
		this._itemCount = 0;
		this._itemIndex = -1;
	}
});

/**
 * Handles the search area box.
 * 
 * @param	jQuery		searchArea
 */
WCF.Search.Message.SearchArea = Class.extend({
	/**
	 * search area object
	 * @var	jQuery
	 */
	_searchArea: null,
	
	/**
	 * Initializes the WCF.Search.Message.SearchArea class.
	 * 
	 * @param	jQuery		searchArea
	 */
	init: function(searchArea) {
		this._searchArea = searchArea;
		
		var $keywordList = new WCF.Search.Message.KeywordList(this._searchArea.find('input[type=search]'), $.proxy(this._callback, this));
		$keywordList.setDelay(500);
		
		// forward clicks on the search icon to input field
		var self = this;
		var $input = this._searchArea.find('input[type=search]');
		this._searchArea.click(function(event) {
			// only forward clicks if the search element itself is the target
			if (event.target == self._searchArea[0]) {
				$input.focus().trigger('click');
				return false;
			}
		});
		
		if (this._searchArea.hasClass('dropdown')) {
			var $containerID = this._searchArea.wcfIdentify();
			var $form = this._searchArea.find('form');
			$form.submit(function() {
				// copy checkboxes and hidden fields into form
				var $dropdownMenu = WCF.Dropdown.getDropdownMenu($containerID);
				
				$dropdownMenu.find('input[type=hidden]').appendTo($form);
				$dropdownMenu.find('input[type=checkbox]:checked').each(function(index, input) {
					var $input = $(input);
					
					$('<input type="hidden" name="' + $input.attr('name') + '" value="' + $input.attr('value') + '" />').appendTo($form);
				});
			});
		}
	},
	
	/**
	 * Callback for WCF.Search.Message.KeywordList.
	 * 
	 * @param	object		data
	 * @return	boolean
	 */
	_callback: function(data) {
		this._searchArea.find('input[type=search]').val(data.label);
		this._searchArea.find('input[type=search]').focus();
		return false;
	}
});

// WCF.Tagging.js
/**
 * Tagging System for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Namespace for tagging related functions.
 */
WCF.Tagging = {};

/**
 * Editable tag list.
 * 
 * @see	WCF.EditableItemList
 */
WCF.Tagging.TagList = WCF.EditableItemList.extend({
	/**
	 * @see	WCF.EditableItemList._className
	 */
	_className: 'wcf\\data\\tag\\TagAction',
	
	/**
	 * maximum tag length
	 * @var	integer
	 */
	_maxLength: 0,
	
	/**
	 * @see	WCF.EditableItemList.init()
	 */
	init: function(itemListSelector, searchInputSelector, maxLength) {
		this._allowCustomInput = true;
		this._maxLength = maxLength;
		
		this._super(itemListSelector, searchInputSelector);
		
		this._data = [ ];
		this._search = new WCF.Tagging.TagSearch(this._searchInput, $.proxy(this.addItem, this));
		this._itemList.addClass('tagList');
		$(itemListSelector).data('__api', this);
	},
	
	/**
	 * @see	WCF.EditableItemList._keyDown()
	 */
	_keyDown: function(event) {
		if (this._super(event)) {
			// ignore submit event
			if (event === null) {
				return true;
			}
			
			var $keyCode = event.which;
			// allow [backspace], [escape], [enter] and [delete]
			if ($keyCode === 8 || $keyCode === 27 || $keyCode === 13 || $keyCode === 46) {
				return true;
			}
			else if ($keyCode > 36 && $keyCode < 41) {
				// allow arrow keys (37-40)
				return true;
			}
			
			if (this._searchInput.val().length >= this._maxLength) {
				return false;
			}
			
			return true;
		}
		
		return false;
	},
	
	/**
	 * @see	WCF.EditableItemList._submit()
	 */
	_submit: function() {
		this._super();
		
		for (var $i = 0, $length = this._data.length; $i < $length; $i++) {
			// deleting items leaves crappy indices
			if (this._data[$i]) {
				$('<input type="hidden" name="tags[]" />').val(this._data[$i]).appendTo(this._form);
			}
		};
	},
	
	/**
	 * @see	WCF.EditableItemList.addItem()
	 */
	addItem: function(data) {
		// enforce max length by trimming values
		if (!data.objectID && data.label.length > this._maxLength) {
			data.label = data.label.substr(0, this._maxLength);
		}
		
		var result = this._super(data);
		$(this._itemList).find('.badge:not(tag)').addClass('tag');
		
		return result;
	},
	
	/**
	 * @see	WCF.EditableItemList._addItem()
	 */
	_addItem: function(objectID, label) {
		this._data.push(label);
	},
	
	/**
	 * @see	WCF.EditableItemList.clearList()
	 */
	clearList: function() {
		this._super();
		
		this._data = [ ];
	},
	
	/**
	 * Returns the current tags.
	 * 
	 * @return	array<string>
	 */
	getTags: function() {
		return this._data;
	},
	
	/**
	 * @see	WCF.EditableItemList._removeItem()
	 */
	_removeItem: function(objectID, label) {
		for (var $i = 0, $length = this._data.length; $i < $length; $i++) {
			if (this._data[$i] === label) {
				// don't use "delete" here since it doesn't reindex
				// the array
				this._data.splice($i, 1);
				return;
			}
		}
	},
	
	/**
	 * @see	WCF.EditableItemList.load()
	 */
	load: function(data) {
		if (data && data.length) {
			for (var $i = 0, $length = data.length; $i < $length; $i++) {
				this.addItem({ objectID: 0, label: WCF.String.unescapeHTML(data[$i]) });
			}
		}
	}
});

/**
 * Search handler for tags.
 * 
 * @see	WCF.Search.Base
 */
WCF.Tagging.TagSearch = WCF.Search.Base.extend({
	/**
	 * @see	WCF.Search.Base._className
	 */
	_className: 'wcf\\data\\tag\\TagAction',
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues, commaSeperated) {
		this._super(searchInput, callback, excludedSearchValues, commaSeperated, false);
	}
});


// WCF.User.js
/**
 * User-related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * User login
 * 
 * @param	boolean		isQuickLogin
 */
WCF.User.Login = Class.extend({
	/**
	 * login button
	 * @var	jQuery
	 */
	_loginSubmitButton: null,
	
	/**
	 * password input
	 * @var	jQuery
	 */
	_password: null,
	
	/**
	 * password input container
	 * @var	jQuery
	 */
	_passwordContainer: null,
	
	/**
	 * cookie input
	 * @var	jQuery
	 */
	_useCookies: null,
	
	/**
	 * cookie input container
	 * @var	jQuery
	 */
	_useCookiesContainer: null,
	
	/**
	 * Initializes the user login
	 * 
	 * @param	boolean		isQuickLogin
	 */
	init: function(isQuickLogin) {
		this._loginSubmitButton = $('#loginSubmitButton');
		this._password = $('#password'),
		this._passwordContainer = this._password.parents('dl');
		this._useCookies = $('#useCookies');
		this._useCookiesContainer = this._useCookies.parents('dl');
		
		var $loginForm = $('#loginForm');
		$loginForm.find('input[name=action]').change($.proxy(this._change, this));
		
		if (isQuickLogin) {
			WCF.User.QuickLogin.init();
		}
	},
	
	/**
	 * Handle toggle between login and register.
	 * 
	 * @param	object		event
	 */
	_change: function(event) {
		if ($(event.currentTarget).val() === 'register') {
			this._setState(false, WCF.Language.get('wcf.user.button.register'));
		}
		else {
			this._setState(true, WCF.Language.get('wcf.user.button.login'));
		}
	},
	
	/**
	 * Sets form states.
	 * 
	 * @param	boolean		enable
	 * @param	string		buttonTitle
	 */
	_setState: function(enable, buttonTitle) {
		if (enable) {
			this._password.enable();
			this._passwordContainer.removeClass('disabled');
			this._useCookies.enable();
			this._useCookiesContainer.removeClass('disabled');
		}
		else {
			this._password.disable();
			this._passwordContainer.addClass('disabled');
			this._useCookies.disable();
			this._useCookiesContainer.addClass('disabled');
		}
		
		this._loginSubmitButton.val(buttonTitle);
	}
});

/**
 * Quick login box
 */
WCF.User.QuickLogin = {
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * login message container
	 * @var	jQuery
	 */
	_loginMessage: null,
	
	/**
	 * Initializes the quick login box
	 */
	init: function() {
		$('.loginLink').click($.proxy(this._render, this));
		
		// prepend protocol and hostname
		$('#loginForm input[name=url]').val(function(index, value) {
			return window.location.protocol + '//' + window.location.host + value;
		});
	},
	
	/**
	 * Displays the quick login box with a info message
	 * 
	 * @param	string	message
	 */
	show: function(message) {
		if (message) {
			if (this._loginMessage === null) {
				this._loginMessage = $('<p class="info" />').hide().prependTo($('#loginForm > form'));
			}
			
			this._loginMessage.show().text(message);
		}
		else if (this._loginMessage !== null) {
			this._loginMessage.hide();
		}
		
		this._render();
	},
	
	/**
	 * Renders the dialog
	 * 
	 * @param	jQuery.Event	event
	 */
	_render: function(event) {
		if (event !== undefined) {
			event.preventDefault();
		}
		
		if (this._dialog === null) {
			this._dialog = $('#loginForm').wcfDialog({
				title: WCF.Language.get('wcf.user.login')
			});
			this._dialog.find('#username').focus();
		}
		else {
			this._dialog.wcfDialog('open');
		}
	}
};

/**
 * UserProfile namespace
 */
WCF.User.Profile = {};

/**
 * Shows the activity point list for users.
 */
WCF.User.Profile.ActivityPointList = {
	/**
	 * list of cached templates
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the WCF.User.Profile.ActivityPointList class.
	 */
	init: function() {
		if (this._didInit) {
			return;
		}
		
		this._cache = { };
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._init();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.User.Profile.ActivityPointList', $.proxy(this._init, this));
		
		this._didInit = true;
	},
	
	/**
	 * Initializes display for activity points.
	 */
	_init: function() {
		$('.activityPointsDisplay').removeClass('activityPointsDisplay').click($.proxy(this._click, this));
	},
	
	/**
	 * Shows or loads the activity point for selected user.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $userID = $(event.currentTarget).data('userID');
		
		if (this._cache[$userID] === undefined) {
			this._proxy.setOption('data', {
				actionName: 'getDetailedActivityPointList',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [ $userID ]
			});
			this._proxy.sendRequest();
		}
		else {
			this._show($userID);
		}
	},
	
	/**
	 * Displays activity points for given user.
	 * 
	 * @param	integer		userID
	 */
	_show: function(userID) {
		if (this._dialog === null) {
			this._dialog = $('<div>' + this._cache[userID] + '</div>').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.user.activityPoint')
			});
		}
		else {
			this._dialog.html(this._cache[userID]);
			this._dialog.wcfDialog('open');
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._cache[data.returnValues.userID] = data.returnValues.template;
		this._show(data.returnValues.userID);
	}
};

/**
 * Provides methods to follow an user.
 * 
 * @param	integer		userID
 * @param	boolean		following
 */
WCF.User.Profile.Follow = Class.extend({
	/**
	 * follow button
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * true if following current user
	 * @var	boolean
	 */
	_following: false,
	
	/**
	 * action proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Creates a new follow object.
	 * 
	 * @param	integer		userID
	 * @param	boolean		following
	 */
	init: function (userID, following) {
		this._following = following;
		this._userID = userID;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._createButton();
		this._showButton();
	},
	
	/**
	 * Creates the (un-)follow button
	 */
	_createButton: function () {
		this._button = $('<li id="followUser"><a class="button jsTooltip" title="'+WCF.Language.get('wcf.user.button.'+(this._following ? 'un' : '')+'follow')+'"><span class="icon icon16 icon-plus"></span> <span class="invisible">'+WCF.Language.get('wcf.user.button.'+(this._following ? 'un' : '')+'follow')+'</span></a></li>').prependTo($('#profileButtonContainer'));
		this._button.click($.proxy(this._execute, this));
	},
	
	/**
	 * Follows or unfollows an user.
	 */
	_execute: function () {
		var $actionName = (this._following) ? 'unfollow' : 'follow';
		this._proxy.setOption('data', {
			'actionName': $actionName,
			'className': 'wcf\\data\\user\\follow\\UserFollowAction',
			'parameters': {
				data: {
					userID: this._userID
				}
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Displays current follow state.
	 */
	_showButton: function () {
		if (this._following) {
			this._button.find('.button').data('tooltip', WCF.Language.get('wcf.user.button.unfollow')).addClass('active').children('.icon').removeClass('icon-plus').addClass('icon-minus');
		}
		else {
			this._button.find('.button').data('tooltip', WCF.Language.get('wcf.user.button.follow')).removeClass('active').children('.icon').removeClass('icon-minus').addClass('icon-plus');
		}
	},
	
	/**
	 * Update object state on success.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function (data, textStatus, jqXHR) {
		this._following = data.returnValues.following;
		this._showButton();
		
		var $notification = new WCF.System.Notification();
		$notification.show();
	}
});

/**
 * Provides methods to manage ignored users.
 * 
 * @param	integer		userID
 * @param	boolean		isIgnoredUser
 */
WCF.User.Profile.IgnoreUser = Class.extend({
	/**
	 * ignore button
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * ignore state
	 * @var	boolean
	 */
	_isIgnoredUser: false,
	
	/**
	 * ajax proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * target user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes methods to manage an ignored user.
	 * 
	 * @param	integer		userID
	 * @param	boolean		isIgnoredUser
	 */
	init: function(userID, isIgnoredUser) {
		this._userID = userID;
		this._isIgnoredUser = isIgnoredUser;
		
		// initialize proxy
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// handle button
		this._updateButton();
		this._button.click($.proxy(this._click, this));
	},
	
	/**
	 * Handle clicks, might cause 'ignore' or 'unignore' to be triggered.
	 */
	_click: function() {
		var $action = (this._isIgnoredUser) ? 'unignore' : 'ignore';
		
		this._proxy.setOption('data', {
			actionName: $action,
			className: 'wcf\\data\\user\\ignore\\UserIgnoreAction',
			parameters: {
				data: {
					ignoreUserID: this._userID
				}
			}
		});
		
		this._proxy.sendRequest();
	},
	
	/**
	 * Updates button label and function upon successful request.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._isIgnoredUser = data.returnValues.isIgnoredUser;
		this._updateButton();
		
		var $notification = new WCF.System.Notification();
		$notification.show();
	},
	
	/**
	 * Updates button label and inserts it if not exists.
	 */
	_updateButton: function() {
		if (this._button === null) {
			this._button = $('<li id="ignoreUser"><a class="button jsTooltip" title="'+WCF.Language.get('wcf.user.button.'+(this._isIgnoredUser ? 'un' : '')+'ignore')+'"><span class="icon icon16 icon-ban-circle"></span> <span class="invisible">'+WCF.Language.get('wcf.user.button.'+(this._isIgnoredUser ? 'un' : '')+'ignore')+'</span></a></li>').prependTo($('#profileButtonContainer'));
		}
		
		this._button.find('.button').data('tooltip', WCF.Language.get('wcf.user.button.' + (this._isIgnoredUser ? 'un' : '') + 'ignore'));
		if (this._isIgnoredUser) this._button.find('.button').addClass('active').children('.icon').removeClass('icon-ban-circle').addClass('icon-circle-blank');
		else this._button.find('.button').removeClass('active').children('.icon').removeClass('icon-circle-blank').addClass('icon-ban-circle');
	}
});

/**
 * Provides methods to load tab menu content upon request.
 */
WCF.User.Profile.TabMenu = Class.extend({
	/**
	 * list of containers
	 * @var	object
	 */
	_hasContent: { },
	
	/**
	 * profile content
	 * @var	jQuery
	 */
	_profileContent: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * target user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes the tab menu loader.
	 * 
	 * @param	integer		userID
	 */
	init: function(userID) {
		this._profileContent = $('#profileContent');
		this._userID = userID;
		
		var $activeMenuItem = this._profileContent.data('active');
		var $enableProxy = false;
		
		// fetch content state
		this._profileContent.find('div.tabMenuContent').each($.proxy(function(index, container) {
			var $containerID = $(container).wcfIdentify();
			
			if ($activeMenuItem === $containerID) {
				this._hasContent[$containerID] = true;
			}
			else {
				this._hasContent[$containerID] = false;
				$enableProxy = true;
			}
		}, this));
		
		// enable loader if at least one container is empty
		if ($enableProxy) {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			this._profileContent.bind('wcftabsbeforeactivate', $.proxy(this._loadContent, this));
		}
	},
	
	/**
	 * Prepares to load content once tabs are being switched.
	 * 
	 * @param	object		event
	 * @param	object		ui
	 */
	_loadContent: function(event, ui) {
		var $panel = $(ui.newPanel);
		var $containerID = $panel.attr('id');
		
		if (!this._hasContent[$containerID]) {
			this._proxy.setOption('data', {
				actionName: 'getContent',
				className: 'wcf\\data\\user\\profile\\menu\\item\\UserProfileMenuItemAction',
				parameters: {
					data: {
						containerID: $containerID,
						menuItem: $panel.data('menuItem'),
						userID: this._userID
					}
				}
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Shows previously requested content.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		this._hasContent[$containerID] = true;
		
		// insert content
		var $content = this._profileContent.find('#' + $containerID);
		$('<div>' + data.returnValues.template + '</div>').hide().appendTo($content);
		
		// slide in content
		$content.children('div').wcfBlindIn();
	}
});

/**
 * User profile inline editor.
 * 
 * @param	integer		userID
 * @param	boolean		editOnInit
 */
WCF.User.Profile.Editor = Class.extend({
	/**
	 * current action
	 * @var	string
	 */
	_actionName: '',
	
	/**
	 * list of interface buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * cached tab content
	 * @var	string
	 */
	_cachedTemplate: '',
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * tab object
	 * @var	jQuery
	 */
	_tab: null,
	
	/**
	 * target user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes the WCF.User.Profile.Editor object.
	 * 
	 * @param	integer		userID
	 * @param	boolean		editOnInit
	 */
	init: function(userID, editOnInit) {
		this._actionName = '';
		this._cachedTemplate = '';
		this._tab = $('#about');
		this._userID = userID;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initButtons();
		
		// begin editing on page load
		if (editOnInit) {
			this._beginEdit();
		}
	},
	
	/**
	 * Initializes interface buttons.
	 */
	_initButtons: function() {
		var $buttonContainer = $('#profileButtonContainer');
		
		// create buttons
		this._buttons = {
			beginEdit: $('<li><a class="button"><span class="icon icon16 icon-pencil" /> <span>' + WCF.Language.get('wcf.user.editProfile') + '</span></a></li>').click($.proxy(this._beginEdit, this)).appendTo($buttonContainer)
		};
	},
	
	/**
	 * Begins editing.
	 */
	_beginEdit: function() {
		this._actionName = 'beginEdit';
		this._buttons.beginEdit.hide();
		$('#profileContent').wcfTabs('select', 'about');
		
		// load form
		this._proxy.setOption('data', {
			actionName: 'beginEdit',
			className: 'wcf\\data\\user\\UserProfileAction',
			objectIDs: [ this._userID ]
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Saves input values.
	 */
	_save: function() {
		this._actionName = 'save';
		
		// collect values
		var $regExp = /values\[([a-zA-Z0-9._-]+)\]/;
		var $values = { };
		this._tab.find('input, textarea, select').each(function(index, element) {
			var $element = $(element);
			
			if ($element.getTagName() === 'input') {
				var $type = $element.attr('type');
				
				if (($type === 'radio' || $type === 'checkbox') && !$element.prop('checked')) {
					return;
				}
			}
			
			var $name = $element.attr('name');
			if ($regExp.test($name)) {
				$values[RegExp.$1] = $element.val();
			}
		});
		
		this._proxy.setOption('data', {
			actionName: 'save',
			className: 'wcf\\data\\user\\UserProfileAction',
			objectIDs: [ this._userID ],
			parameters: {
				values: $values
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Restores back to default view.
	 */
	_restore: function() {
		this._actionName = 'restore';
		this._buttons.beginEdit.show();
		
		this._destroyCKEditor();
		
		this._tab.html(this._cachedTemplate).children().css({ height: 'auto' });
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (this._actionName) {
			case 'beginEdit':
				this._prepareEdit(data);
			break;
			
			case 'save':
				// save was successful, show parsed template
				if (data.returnValues.success) {
					this._cachedTemplate = data.returnValues.template;
					this._restore();
				}
				else {
					this._prepareEdit(data, true);
				}
			break;
		}
	},
	
	/**
	 * Prepares editing mode.
	 * 
	 * @param	object		data
	 * @param	boolean		disableCache
	 */
	_prepareEdit: function(data, disableCache) {
		this._destroyCKEditor();
		
		// update template
		var self = this;
		this._tab.html(function(index, oldHTML) {
			if (disableCache !== true) {
				self._cachedTemplate = oldHTML;
			}
			
			return data.returnValues.template;
		});
		
		// block autocomplete
		this._tab.find('input[type=text]').attr('autocomplete', 'off');
		
		// bind event listener
		this._tab.find('.formSubmit > button[data-type=save]').click($.proxy(this._save, this));
		this._tab.find('.formSubmit > button[data-type=restore]').click($.proxy(this._restore, this));
		this._tab.find('input').keyup(function(event) {
			if (event.which === 13) { // Enter
				self._save();
				
				event.preventDefault();
				return false;
			}
		});
	},
	
	/**
	 * Destroys all CKEditor instances within current tab.
	 */
	_destroyCKEditor: function() {
		// destroy all CKEditor instances
		this._tab.find('textarea + .cke').each(function(index, container) {
			var $instanceName = $(container).attr('id').replace(/cke_/, '');
			if (CKEDITOR.instances[$instanceName]) {
				CKEDITOR.instances[$instanceName].destroy();
			}
		});
	}
});

/**
 * Namespace for registration functions.
 */
WCF.User.Registration = {};

/**
 * Validates the password.
 * 
 * @param	jQuery		element
 * @param	jQuery		confirmElement
 * @param	object		options
 */
WCF.User.Registration.Validation = Class.extend({
	/**
	 * action name
	 * @var	string
	 */
	_actionName: '',
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * confirmation input element
	 * @var	jQuery
	 */
	_confirmElement: null,
	
	/**
	 * input element
	 * @var	jQuery
	 */
	_element: null,
	
	/**
	 * list of error messages
	 * @var	object
	 */
	_errorMessages: { },
	
	/**
	 * list of additional options
	 * @var	object
	 */
	_options: { },
	
	/**
	 * AJAX proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the validation.
	 * 
	 * @param	jQuery		element
	 * @param	jQuery		confirmElement
	 * @param	object		options
	 */
	init: function(element, confirmElement, options) {
		this._element = element;
		this._element.blur($.proxy(this._blur, this));
		this._confirmElement = confirmElement || null;
		
		if (this._confirmElement !== null) {
			this._confirmElement.blur($.proxy(this._blurConfirm, this));
		}
		
		options = options || { };
		this._setOptions(options);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			showLoadingOverlay: false
		});
		
		this._setErrorMessages();
	},
	
	/**
	 * Sets additional options
	 */
	_setOptions: function(options) { },
	
	/**
	 * Sets error messages.
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: '',
			notEqual: ''
		};
	},
	
	/**
	 * Validates once focus on input is lost.
	 * 
	 * @param	object		event
	 */
	_blur: function(event) {
		var $value = this._element.val();
		if (!$value) {
			return this._showError(this._element, WCF.Language.get('wcf.global.form.error.empty'));
		}
		
		if (this._confirmElement !== null) {
			var $confirmValue = this._confirmElement.val();
			if ($confirmValue != '' && $value != $confirmValue) {
				return this._showError(this._confirmElement, this._errorMessages.notEqual);
			}
		}
		
		if (!this._validateOptions()) {
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: this._actionName,
			className: this._className,
			parameters: this._getParameters()
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Returns a list of parameters.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return { };
	},
	
	/**
	 * Validates input by options.
	 * 
	 * @return	boolean
	 */
	_validateOptions: function() {
		return true;
	},
	
	/**
	 * Validates value once confirmation input focus is lost.
	 * 
	 * @param	object		event
	 */
	_blurConfirm: function(event) {
		var $value = this._confirmElement.val();
		if (!$value) {
			return this._showError(this._confirmElement, WCF.Language.get('wcf.global.form.error.empty'));
		}
		
		this._blur(event);
	},
	
	/**
	 * Handles AJAX responses.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.isValid) {
			this._showSuccess(this._element);
			if (this._confirmElement !== null && this._confirmElement.val()) {
				this._showSuccess(this._confirmElement);
			}
		}
		else {
			this._showError(this._element, WCF.Language.get(this._errorMessages.ajaxError + data.returnValues.error));
		}
	},
	
	/**
	 * Shows an error message.
	 * 
	 * @param	jQuery		element
	 * @param	string		message
	 */
	_showError: function(element, message) {
		element.parent().parent().addClass('formError').removeClass('formSuccess');
		
		var $innerError = element.parent().find('small.innerError');
		if (!$innerError.length) {
			$innerError = $('<small />').addClass('innerError').insertAfter(element);
		}
		
		$innerError.text(message);
	},
	
	/**
	 * Displays a success message.
	 * 
	 * @param	jQuery		element
	 */
	_showSuccess: function(element) {
		element.parent().parent().addClass('formSuccess').removeClass('formError');
		element.next('small.innerError').remove();
	}
});

/**
 * Username validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 */
WCF.User.Registration.Validation.Username = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validateUsername',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._setOptions()
	 */
	_setOptions: function(options) {
		this._options = $.extend(true, {
			minlength: 3,
			maxlength: 25
		}, options);
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.username.error.'
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._validateOptions()
	 */
	_validateOptions: function() {
		var $value = this._element.val();
		if ($value.length < this._options.minlength || $value.length > this._options.maxlength) {
			this._showError(this._element, WCF.Language.get('wcf.user.username.error.notValid'));
			return false;
		}
		
		return true;
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			username: this._element.val()
		};
	}
});

/**
 * Email validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 */
WCF.User.Registration.Validation.EmailAddress = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validateEmailAddress',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			email: this._element.val()
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.email.error.',
			notEqual: WCF.Language.get('wcf.user.confirmEmail.error.notEqual')
		};
	}
});

/**
 * Password validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 */
WCF.User.Registration.Validation.Password = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validatePassword',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			password: this._element.val()
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.password.error.',
			notEqual: WCF.Language.get('wcf.user.confirmPassword.error.notEqual')
		};
	}
});

/**
 * Toggles input fields for lost password form.
 */
WCF.User.Registration.LostPassword = Class.extend({
	/**
	 * email input
	 * @var	jQuery
	 */
	_email: null,
	
	/**
	 * username input
	 * @var	jQuery
	 */
	_username: null,
	
	/**
	 * Initializes LostPassword-form class.
	 */
	init: function() {
		// bind input fields
		this._email = $('#emailInput');
		this._username = $('#usernameInput');
		
		// bind event listener
		this._email.keyup($.proxy(this._checkEmail, this));
		this._username.keyup($.proxy(this._checkUsername, this));
		
		if ($.browser.mozilla && $.browser.touch) {
			this._email.on('input', $.proxy(this._checkEmail, this));
			this._username.on('input', $.proxy(this._checkUsername, this));
		}
		
		// toggle fields on init
		this._checkEmail();
		this._checkUsername();
	},
	
	/**
	 * Checks for content in email field and toggles username.
	 */
	_checkEmail: function() {
		if (this._email.val() == '') {
			this._username.enable();
			this._username.parents('dl:eq(0)').removeClass('disabled');
		}
		else {
			this._username.disable();
			this._username.parents('dl:eq(0)').addClass('disabled');
		}
	},
	
	/**
	 * Checks for content in username field and toggles email.
	 */
	_checkUsername: function() {
		if (this._username.val() == '') {
			this._email.enable();
			this._email.parents('dl:eq(0)').removeClass('disabled');
		}
		else {
			this._email.disable();
			this._email.parents('dl:eq(0)').addClass('disabled');
		}
	}
});

/**
 * Notification system for WCF.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Notification = {};

/**
 * Loads notification for the user panel.
 * 
 * @see	WCF.UserPanel
 */
WCF.Notification.UserPanel = WCF.UserPanel.extend({
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * link to show all notifications
	 * @var	string
	 */
	_showAllLink: '',
	
	/**
	 * @see	WCF.UserPanel.init()
	 */
	init: function(showAllLink) {
		this._noItems = 'wcf.user.notification.noMoreNotifications';
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		this._showAllLink = showAllLink;
		
		this._super('userNotifications');
		
		// update page title
		if (this._container.data('count')) {
			document.title = '(' + this._container.data('count') + ') ' + document.title;
		}
	},
	
	/**
	 * @see	WCF.UserPanel._addDefaultItems()
	 */
	_addDefaultItems: function(dropdownMenu) {
		this._addDivider(dropdownMenu);
		if (this._container.data('count')) {
			$('<li><a href="' + this._showAllLink + '">' + WCF.Language.get('wcf.user.notification.showAll') + '</a></li>').appendTo(dropdownMenu);
			this._addDivider(dropdownMenu);
		}
		$('<li id="userNotificationsMarkAllAsConfirmed"><a>' + WCF.Language.get('wcf.user.notification.markAllAsConfirmed') + '</a></li>').click($.proxy(this._markAllAsConfirmed, this)).appendTo(dropdownMenu);
	},
	
	/**
	 * @see	WCF.UserPanel._getParameters()
	 */
	_getParameters: function() {
		return {
			actionName: 'getOutstandingNotifications',
			className: 'wcf\\data\\user\\notification\\UserNotificationAction'
		};
	},
	
	/**
	 * @see	WCF.UserPanel._after()
	 */
	_after: function(dropdownMenu) {
		WCF.Dropdown.getDropdownMenu(this._container.wcfIdentify()).children('li.jsNotificationItem').click($.proxy(this._markAsConfirmed, this));
	},
	
	/**
	 * Marks a notification as confirmed.
	 * 
	 * @param	object		event
	 */
	_markAsConfirmed: function(event) {
		this._proxy.setOption('data', {
			actionName: 'markAsConfirmed',
			className: 'wcf\\data\\user\\notification\\UserNotificationAction',
			parameters: {
				notificationID: $(event.currentTarget).data('notificationID')
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Marks all notifications as confirmed.
	 */
	_markAllAsConfirmed: function() {
		WCF.System.Confirmation.show(WCF.Language.get('wcf.user.notification.markAllAsConfirmed.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._proxy.setOption('data', {
					actionName: 'markAllAsConfirmed',
					className: 'wcf\\data\\user\\notification\\UserNotificationAction'
				});
				this._proxy.sendRequest();
			}
		}, this));
	},
	
	/**
	 * @see	WCF.UserPanel._success()
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.actionName) {
			case 'markAllAsConfirmed':
				$('.jsNotificationItem').remove();
				// remove notification count
				document.title = document.title.replace(/^\(([0-9]+)\) /, '');
			// fall through
			case 'getOutstandingNotifications':
				if (!data.returnValues || !data.returnValues.template) {
					$('#userNotificationsMarkAllAsConfirmed').prev('.dropdownDivider').remove();
					$('#userNotificationsMarkAllAsConfirmed').remove();
				}
				
				this._super(data, textStatus, jqXHR);
			break;
			
			case 'markAsConfirmed':
				WCF.Dropdown.getDropdownMenu(this._container.wcfIdentify()).children('li.jsNotificationItem').each(function(index, item) {
					var $item = $(item);
					if (data.returnValues.notificationID == $item.data('notificationID')) {
						window.location = $item.data('link');
						return false;
					}
				});
			break;
		}
	}
});

/**
 * Handles notification list actions.
 */
WCF.Notification.List = Class.extend({
	/**
	 * notification count
	 * @var	jQuery
	 */
	_badge: null,
	
	/**
	 * list of notification items
	 * @var	object
	 */
	_items: { },
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the notification list.
	 */
	init: function() {
		var $containers = $('li.jsNotificationItem');
		if (!$containers.length) {
			return;
		}
		
		$containers.each($.proxy(function(index, container) {
			var $container = $(container);
			this._items[$container.data('notificationID')] = $container;
			
			$container.find('.jsMarkAsConfirmed').data('notificationID', $container.data('notificationID')).click($.proxy(this._click, this));
			$container.find('p').html(function(index, oldHTML) {
				return '<a>' + oldHTML + '</a>';
			}).children('a').data('notificationID', $container.data('notificationID')).click($.proxy(this._clickLink, this));
		}, this));
		
		this._badge = $('.jsNotificationsBadge:eq(0)');
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// mark all as confirmed button
		$('.contentNavigation .jsMarkAllAsConfirmed').click($.proxy(this._markAllAsConfirmed, this));
	},
	
	/**
	 * Handles clicks on the text link.
	 * 
	 * @param	object		event
	 */
	_clickLink: function(event) {
		this._items[$(event.currentTarget).data('notificationID')].data('redirect', true);
		this._click(event);
	},
	
	/**
	 * Handles button actions.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._proxy.setOption('data', {
			actionName: 'markAsConfirmed',
			className: 'wcf\\data\\user\\notification\\UserNotificationAction',
			parameters: {
				notificationID: $(event.currentTarget).data('notificationID')
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Marks all notifications as confirmed.
	 */
	_markAllAsConfirmed: function() {
		WCF.System.Confirmation.show(WCF.Language.get('wcf.user.notification.markAllAsConfirmed.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._proxy.setOption('data', {
					actionName: 'markAllAsConfirmed',
					className: 'wcf\\data\\user\\notification\\UserNotificationAction'
				});
				this._proxy.sendRequest();
			}
		}, this));
	},
	
	/**
	 * Handles successful button actions.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.actionName) {
			case 'markAllAsConfirmed':
				window.location.reload();
			break;
			
			case 'markAsConfirmed':
				var $item = this._items[data.returnValues.notificationID];
				if ($item.data('redirect')) {
					window.location = $item.data('link');
					return;
				}
				
				this._items[data.returnValues.notificationID].remove();
				delete this._items[data.returnValues.notificationID];
				
				// reduce badge count
				this._badge.html(data.returnValues.totalCount);
				
				// remove previous notification count
				document.title = document.title.replace(/^\(([0-9]+)\) /, '');
				
				// update page title
				if (data.returnValues.totalCount > 0) {
					document.title = '(' + data.returnValues.totalCount + ') ' + document.title;
				}
			break;
		}
	}
});

/**
 * Signature preview.
 * 
 * @see	WCF.Message.Preview
 */
WCF.User.SignaturePreview = WCF.Message.Preview.extend({
	/**
	 * @see	WCF.Message.Preview._handleResponse()
	 */
	_handleResponse: function(data) {
		// get preview container
		var $preview = $('#previewContainer');
		if (!$preview.length) {
			$preview = $('<fieldset id="previewContainer"><legend>' + WCF.Language.get('wcf.global.preview') + '</legend><div></div></fieldset>').insertBefore($('#signatureContainer')).wcfFadeIn();
		}
		
		$preview.children('div').first().html(data.returnValues.message);
	}
});

/**
 * Loads recent activity events once the user scrolls to the very bottom.
 * 
 * @param	integer		userID
 */
WCF.User.RecentActivityLoader = Class.extend({
	/**
	 * container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * true if list should be filtered by followed users
	 * @var	boolean
	 */
	_filteredByFollowedUsers: false,
	
	/**
	 * button to load next events
	 * @var	jQuery
	 */
	_loadButton: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes a new RecentActivityLoader object.
	 * 
	 * @param	integer		userID
	 * @param	boolean		filteredByFollowedUsers
	 */
	init: function(userID, filteredByFollowedUsers) {
		this._container = $('#recentActivities');
		this._filteredByFollowedUsers = (filteredByFollowedUsers === true);
		this._userID = userID;
		
		if (this._userID !== null && !this._userID) {
			console.debug("[WCF.User.RecentActivityLoader] Invalid parameter 'userID' given.");
			return;
		}
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._loadButton = $('<li class="recentActivitiesMore"><button class="small">' + WCF.Language.get('wcf.user.recentActivity.more') + '</button></li>').appendTo(this._container);
		this._loadButton = this._loadButton.children('button').click($.proxy(this._click, this));
	},
	
	/**
	 * Loads next activity events.
	 */
	_click: function() {
		this._loadButton.enable();
		
		var $parameters = {
			lastEventTime: this._container.data('lastEventTime')
		};
		if (this._userID) {
			$parameters.userID = this._userID;
		}
		else if (this._filteredByFollowedUsers) {
			$parameters.filteredByFollowedUsers = 1;
		}
		
		this._proxy.setOption('data', {
			actionName: 'load',
			className: 'wcf\\data\\user\\activity\\event\\UserActivityEventAction',
			parameters: $parameters
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.template) {
			$(data.returnValues.template).insertBefore(this._loadButton.parent());
			
			this._container.data('lastEventTime', data.returnValues.lastEventTime);
			this._loadButton.enable();
		}
		else {
			$('<small>' + WCF.Language.get('wcf.user.recentActivity.noMoreEntries') + '</small>').appendTo(this._loadButton.parent());
			this._loadButton.remove();
		}
	}
});

/**
 * Loads user profile previews.
 * 
 * @see	WCF.Popover
 */
WCF.User.ProfilePreview = WCF.Popover.extend({
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of user profiles
	 * @var	object
	 */
	_userProfiles: { },
	
	/**
	 * @see	WCF.Popover.init()
	 */
	init: function() {
		this._super('.userLink');
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false
		});
	},
	
	/**
	 * @see	WCF.Popover._loadContent()
	 */
	_loadContent: function() {
		var $element = $('#' + this._activeElementID);
		var $userID = $element.data('userID');
		
		if (this._userProfiles[$userID]) {
			// use cached user profile
			this._insertContent(this._activeElementID, this._userProfiles[$userID], true);
		}
		else {
			this._proxy.setOption('data', {
				actionName: 'getUserProfile',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [ $userID ]
			});
			
			var $elementID = this._activeElementID;
			var self = this;
			this._proxy.setOption('success', function(data, textStatus, jqXHR) {
				// cache user profile
				self._userProfiles[$userID] = data.returnValues.template;
				
				// show user profile
				self._insertContent($elementID, data.returnValues.template, true);
			});
			this._proxy.setOption('failure', function(data, jqXHR, textStatus, errorThrown) {
				// cache user profile
				self._userProfiles[$userID] = data.message;
				
				// show user profile
				self._insertContent($elementID, data.message, true);
				
				return false;
			});
			this._proxy.sendRequest();
		}
	}
});

/**
 * Initalizes WCF.User.Action namespace.
 */
WCF.User.Action = {};

/**
 * Handles user follow and unfollow links.
 */
WCF.User.Action.Follow = Class.extend({
	/**
	 * list with elements containing follow and unfollow buttons
	 * @var	array
	 */
	_containerList: null,
	
	/**
	 * CSS selector for follow buttons
	 * @var	string
	 */
	_followButtonSelector: '.jsFollowButton',
	
	/**
	 * id of the user that is currently being followed/unfollowed
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes new WCF.User.Action.Follow object.
	 * 
	 * @param	array		containerList
	 * @param	string		followButtonSelector
	 */
	init: function(containerList, followButtonSelector) {
		if (!containerList.length) {
			return;
		}
		this._containerList = containerList;
		
		if (followButtonSelector) {
			this._followButtonSelector = followButtonSelector;
		}
		
		// initialize proxy
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// bind event listeners
		this._containerList.each($.proxy(function(index, container) {
			$(container).find(this._followButtonSelector).click($.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Handles a click on a follow or unfollow button.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var link = $(event.target);
		if (!link.is('a')) {
			link = link.closest('a');
		}
		this._userID = link.data('objectID');
		
		this._proxy.setOption('data', {
			'actionName': link.data('following') ? 'unfollow' : 'follow',
			'className': 'wcf\\data\\user\\follow\\UserFollowAction',
			'parameters': {
				data: {
					userID: this._userID
				}
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles the successful (un)following of a user.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._containerList.each($.proxy(function(index, container) {
			var button = $(container).find(this._followButtonSelector).get(0);
			
			if (button && $(button).data('objectID') == this._userID) {
				button = $(button);
				
				// toogle icon title
				if (data.returnValues.following) {
					button.data('tooltip', WCF.Language.get('wcf.user.button.unfollow')).children('.icon').removeClass('icon-plus').addClass('icon-minus');
				}
				else {
					button.data('tooltip', WCF.Language.get('wcf.user.button.follow')).children('.icon').removeClass('icon-minus').addClass('icon-plus');
				}
				
				button.data('following', data.returnValues.following);
				
				return false;
			}
		}, this));
		
		var $notification = new WCF.System.Notification();
		$notification.show();
	}
});

/**
 * Handles user ignore and unignore links.
 */
WCF.User.Action.Ignore = Class.extend({
	/**
	 * list with elements containing ignore and unignore buttons
	 * @var	array
	 */
	_containerList: null,
	
	/**
	 * CSS selector for ignore buttons
	 * @var	string
	 */
	_ignoreButtonSelector: '.jsIgnoreButton',
	
	/**
	 * id of the user that is currently being ignored/unignored
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes new WCF.User.Action.Ignore object.
	 * 
	 * @param	array		containerList
	 * @param	string		ignoreButtonSelector
	 */
	init: function(containerList, ignoreButtonSelector) {
		if (!containerList.length) {
			return;
		}
		this._containerList = containerList;
		
		if (ignoreButtonSelector) {
			this._ignoreButtonSelector = ignoreButtonSelector;
		}
		
		// initialize proxy
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// bind event listeners
		this._containerList.each($.proxy(function(index, container) {
			$(container).find(this._ignoreButtonSelector).click($.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Handles a click on a ignore or unignore button.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var link = $(event.target);
		if (!link.is('a')) {
			link = link.closest('a');
		}
		this._userID = link.data('objectID');
		
		this._proxy.setOption('data', {
			'actionName': link.data('ignored') ? 'unignore' : 'ignore',
			'className': 'wcf\\data\\user\\ignore\\UserIgnoreAction',
			'parameters': {
				data: {
					ignoreUserID: this._userID
				}
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles the successful (un)ignoring of a user.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._containerList.each($.proxy(function(index, container) {
			var button = $(container).find(this._ignoreButtonSelector).get(0);
			
			if (button && $(button).data('objectID') == this._userID) {
				button = $(button);
				
				// toogle icon title
				if (data.returnValues.isIgnoredUser) {
					button.data('tooltip', WCF.Language.get('wcf.user.button.unignore')).children('.icon').removeClass('icon-ban-circle').addClass('icon-circle-blank');
				}
				else {
					button.data('tooltip', WCF.Language.get('wcf.user.button.ignore')).children('.icon').removeClass('icon-circle-blank').addClass('icon-ban-circle');
				}
				
				button.data('ignored', data.returnValues.isIgnoredUser);
				
				return false;
			}
		}, this));
		
		var $notification = new WCF.System.Notification();
		$notification.show();
	}
});

/**
 * Namespace for avatar functions.
 */
WCF.User.Avatar = {};

/**
 * Handles cropping an avatar.
 */
WCF.User.Avatar.Crop = Class.extend({
	/**
	 * current crop setting in x-direction
	 * @var	integer
	 */
	_cropX: 0,
	
	/**
	 * current crop setting in y-direction
	 * @var	integer
	 */
	_cropY: 0,
	
	/**
	 * avatar crop dialog
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy to send the crop AJAX requests
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * maximum size of thumbnails
	 * @var	integer
	 */
	MAX_THUMBNAIL_SIZE: 128,
	
	/**
	 * Creates a new instance of WCF.User.Avatar.Crop.
	 * 
	 * @param	integer		avatarID
	 */
	init: function(avatarID) {
		this._avatarID = avatarID;
		
		if (this._dialog) {
			this.destroy();
		}
		this._dialog = null;
		
		// check if object already had been initialized
		if (!this._proxy) {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
		}
		
		$('.userAvatarCrop').click($.proxy(this._showCropDialog, this));
	},
	
	/**
	 * Destroys the avatar crop interface.
	 */
	destroy: function() {
		this._dialog.remove();
	},
	
	/**
	 * Sends AJAX request to crop avatar.
	 * 
	 * @param	object		event
	 */
	_crop: function(event) {
		this._proxy.setOption('data', {
			actionName: 'cropAvatar',
			className: 'wcf\\data\\user\\avatar\\UserAvatarAction',
			objectIDs: [ this._avatarID ],
			parameters: {
				cropX: this._cropX,
				cropY: this._cropY
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Initializes the dialog after a successful 'getCropDialog' request.
	 * 
	 * @param	object		data
	 */
	_getCropDialog: function(data) {
		if (!this._dialog) {
			this._dialog = $('<div />').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.user.avatar.type.custom.crop')
			});
		}
		
		this._dialog.html(data.returnValues.template);
		this._dialog.find('button[data-type="save"]').click($.proxy(this._crop, this));
		
		this._cropX = data.returnValues.cropX;
		this._cropY = data.returnValues.cropY;
		
		var $image = $('#userAvatarCropSelection > img');
		$('#userAvatarCropSelection').css({
			height: $image.height() + 'px',
			width: $image.width() + 'px'
		});
		$('#userAvatarCropOverlaySelection').css({
			'background-image': 'url(' + $image.attr('src') + ')',
			'background-position': -this._cropX + 'px ' + -this._cropY + 'px',
			'left': this._cropX + 'px',
			'top': this._cropY + 'px'
		}).draggable({
			containment: 'parent',
			drag : $.proxy(this._updateSelection, this),
			stop : $.proxy(this._updateSelection, this)
		});
		
		this._dialog.find('button[data-type="save"]').click($.proxy(this._save, this));
		
		this._dialog.wcfDialog('render');
	},
	
	/**
	 * Shows the cropping dialog.
	 */
	_showCropDialog: function() {
		if (!this._dialog) {
			this._proxy.setOption('data', {
				actionName: 'getCropDialog',
				className: 'wcf\\data\\user\\avatar\\UserAvatarAction',
				objectIDs: [ this._avatarID ]
			});
			this._proxy.sendRequest();
		}
		else {
			this._dialog.wcfDialog('open');
		}
	},
	
	/**
	 * Handles successful AJAX request.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.actionName) {
			case 'getCropDialog':
				this._getCropDialog(data);
			break;
			
			case 'cropAvatar':
				$('#avatarUpload > dt > img').replaceWith($('<img src="' + data.returnValues.url + '" alt="" class="userAvatarCrop jsTooltip" title="' + WCF.Language.get('wcf.user.avatar.type.custom.crop') + '" />').css({
					width: '96px',
					height: '96px'
				}).click($.proxy(this._showCropDialog, this)));
				
				WCF.DOMNodeInsertedHandler.execute();
				
				this._dialog.wcfDialog('close');
				
				var $notification = new WCF.System.Notification();
				$notification.show();
			break;
		}
	},
	
	/**
	 * Updates the current crop selection if the selection overlay is dragged.
	 * 
	 * @param	object		event
	 * @param	object		ui
	 */
	_updateSelection: function(event, ui) {
		this._cropX = ui.position.left;
		this._cropY = ui.position.top;
		
		$('#userAvatarCropOverlaySelection').css({
			'background-position': -ui.position.left + 'px ' + -ui.position.top + 'px'
		});
	}
});

/**
 * Avatar upload function
 * 
 * @see	WCF.Upload
 */
WCF.User.Avatar.Upload = WCF.Upload.extend({
	/**
	 * handles cropping the avatar
	 * @var	WCF.User.Avatar.Crop
	 */
	_avatarCrop: null,
	
	/**
	 * user id of avatar owner
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initalizes a new WCF.User.Avatar.Upload object.
	 * 
	 * @param	integer			userID
	 * @param	WCF.User.Avatar.Crop	avatarCrop
	 */
	init: function(userID, avatarCrop) {
		this._super($('#avatarUpload > dd > div'), undefined, 'wcf\\data\\user\\avatar\\UserAvatarAction');
		this._userID = userID || 0;
		this._avatarCrop = avatarCrop;
		
		$('#avatarForm input[type=radio]').change(function() {
			if ($(this).val() == 'custom') {
				$('#avatarUpload > dd > div').show();
			}
			else {
				$('#avatarUpload > dd > div').hide();
			}
		});
		if (!$('#avatarForm input[type=radio][value=custom]:checked').length) {
			$('#avatarUpload > dd > div').hide();
		}
	},
	
	/**
	 * @see	WCF.Upload._initFile()
	 */
	_initFile: function(file) {
		return $('#avatarUpload > dt > img');
	},
	
	/**
	 * @see	WCF.Upload._success()
	 */
	_success: function(uploadID, data) {
		if (data.returnValues.url) {
			this._updateImage(data.returnValues.url, data.returnValues.canCrop);
			
			if (data.returnValues.canCrop) {
				if (!this._avatarCrop) {
					this._avatarCrop = new WCF.User.Avatar.Crop(data.returnValues.avatarID);
				}
				else {
					this._avatarCrop.init(data.returnValues.avatarID);
				}
			}
			else if (this._avatarCrop) {
				this._avatarCrop.destroy();
				this._avatarCrop = null;
			}
			
			// hide error
			$('#avatarUpload > dd > .innerError').remove();
			
			// show success message
			var $notification = new WCF.System.Notification(WCF.Language.get('wcf.user.avatar.upload.success'));
			$notification.show();
		}
		else if (data.returnValues.errorType) {
			// show error
			this._getInnerErrorElement().text(WCF.Language.get('wcf.user.avatar.upload.error.' + data.returnValues.errorType));
		}
	},
	
	/**
	 * Updates the displayed avatar image.
	 * 
	 * @param	string		url
	 * @param	boolean		canCrop
	 */
	_updateImage: function(url, canCrop) {
		$('#avatarUpload > dt > img').remove();
		var $image = $('<img src="' + url + '" alt="" />').css({
			'height': 'auto',
			'max-height': '96px',
			'max-width': '96px',
			'width': 'auto'
		});
		if (canCrop) {
			$image.addClass('userAvatarCrop').addClass('jsTooltip');
			$image.attr('title', WCF.Language.get('wcf.user.avatar.type.custom.crop'));
		}
		
		$('#avatarUpload > dt').prepend($image);
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Returns the inner error element.
	 * 
	 * @return	jQuery
	 */
	_getInnerErrorElement: function() {
		var $span = $('#avatarUpload > dd > .innerError');
		if (!$span.length) {
			$span = $('<small class="innerError"></span>');
			$('#avatarUpload > dd').append($span);
		}
		
		return $span;
	},
	
	/**
	 * @see	WCF.Upload._getParameters()
	 */
	_getParameters: function() {
		return {
			userID: this._userID
		};
	},
});

/**
 * Generic implementation for grouped user lists.
 * 
 * @param	string		className
 * @param	string		dialogTitle
 * @param	object		additionalParameters
 */
WCF.User.List = Class.extend({
	/**
	 * list of additional parameters
	 * @var	object
	 */
	_additionalParameters: { },
	
	/**
	 * list of cached pages
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * dialog title
	 * @var	string
	 */
	_dialogTitle: '',
	
	/**
	 * page count
	 * @var	integer
	 */
	_pageCount: 0,
	
	/**
	 * current page no
	 * @var	integer
	 */
	_pageNo: 1,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes a new grouped user list.
	 * 
	 * @param	string		className
	 * @param	string		dialogTitle
	 * @param	object		additionalParameters
	 */
	init: function(className, dialogTitle, additionalParameters) {
		this._additionalParameters = additionalParameters || { };
		this._cache = { };
		this._className = className;
		this._dialog = null;
		this._dialogTitle = dialogTitle;
		this._pageCount = 0;
		this._pageNo = 1;
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Opens the dialog overlay.
	 */
	open: function() {
		this._pageNo = 1;
		this._showPage();
	},
	
	/**
	 * Displays the specified page.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_showPage: function(event, data) {
		if (data && data.activePage) {
			this._pageNo = data.activePage;
		}
		
		if (this._pageCount != 0 && (this._pageNo < 1 || this._pageNo > this._pageCount)) {
			console.debug("[WCF.User.List] Cannot access page " + this._pageNo + " of " + this._pageCount);
			return;
		}
		
		if (this._cache[this._pageNo]) {
			var $dialogCreated = false;
			if (this._dialog === null) {
				//this._dialog = $('<div id="userList' + this._className.hashCode() + '" style="min-width: 600px;" />').hide().appendTo(document.body);
				this._dialog = $('<div id="userList' + this._className.hashCode() + '" />').hide().appendTo(document.body);
				$dialogCreated = true;
			}
			
			// remove current view
			this._dialog.empty();
			
			// insert HTML
			this._dialog.html(this._cache[this._pageNo]);
			
			// add pagination
			if (this._pageCount > 1) {
				this._dialog.find('.jsPagination').wcfPages({
					activePage: this._pageNo,
					maxPage: this._pageCount
				}).bind('wcfpagesswitched', $.proxy(this._showPage, this));
			}
			
			// show dialog
			if ($dialogCreated) {
				this._dialog.wcfDialog({
					title: this._dialogTitle
				});
			}
			else {
				this._dialog.wcfDialog('open').wcfDialog('render');
			}
		}
		else {
			this._additionalParameters.pageNo = this._pageNo;
			
			// load template via AJAX
			this._proxy.setOption('data', {
				actionName: 'getGroupedUserList',
				className: this._className,
				interfaceName: 'wcf\\data\\IGroupedUserListAction',
				parameters: this._additionalParameters
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.pageCount) {
			this._pageCount = data.returnValues.pageCount;
		}
		
		this._cache[this._pageNo] = data.returnValues.template;
		this._showPage();
	}
});

/**
 * Namespace for object watch functions.
 */
WCF.User.ObjectWatch = {};

/**
 * Handles subscribe/unsubscribe links.
 */
WCF.User.ObjectWatch.Subscribe = Class.extend({
	/**
	 * CSS selector for subscribe buttons
	 * @var	string
	 */
	_buttonSelector: '.jsSubscribeButton',
	
	/**
	 * list of buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * dialog overlay
	 * @var	object
	 */
	_dialog: null,
	
	/**
	 * system notification
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * WCF.User.ObjectWatch.Subscribe object.
	 */
	init: function() {
		this._buttons = { };
		this._notification = null;
		
		// initialize proxy
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// bind event listeners
		$(this._buttonSelector).each($.proxy(function(index, button) {
			var $button = $(button);
			var $objectID = $button.data('objectID');
			this._buttons[$objectID] = $button.click($.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Handles a click on a subscribe button.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $button = $(event.currentTarget);
		
		this._proxy.setOption('data', {
			actionName: 'manageSubscription',
			className: 'wcf\\data\\user\\object\\watch\\UserObjectWatchAction',
			parameters: {
				objectID: $button.data('objectID'),
				objectType: $button.data('objectType')
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.actionName === 'manageSubscription') {
			if (this._dialog === null) {
				this._dialog = $('<div>' + data.returnValues.template + '</div>').hide().appendTo(document.body);
				this._dialog.wcfDialog({
					title: WCF.Language.get('wcf.user.objectWatch.manageSubscription')
				});
			}
			else {
				this._dialog.html(data.returnValues.template);
				this._dialog.wcfDialog('open');
			}
			
			// bind event listener
			this._dialog.find('.formSubmit > .jsButtonSave').data('objectID', data.returnValues.objectID).click($.proxy(this._save, this));
			var $enableNotification = this._dialog.find('input[name=enableNotification]').disable();
			
			// toggle subscription
			this._dialog.find('input[name=subscribe]').change(function(event) {
				var $input = $(event.currentTarget);
				if ($input.val() == 1) {
					$enableNotification.enable();
				}
				else {
					$enableNotification.disable();
				}
			});
			
			// setup
			var $selectedOption = this._dialog.find('input[name=subscribe]:checked');
			if ($selectedOption.length && $selectedOption.val() == 1) {
				$enableNotification.enable();
			}
		}
		else if (data.actionName === 'saveSubscription' && this._dialog.is(':visible')) {
			this._dialog.wcfDialog('close');
			
			if (this._notification === null) {
				this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
			}
			
			this._notification.show();
		}
	},
	
	/**
	 * Saves the subscription.
	 * 
	 * @param	object		event
	 */
	_save: function(event) {
		var $button = this._buttons[$(event.currentTarget).data('objectID')];
		var $subscribe = this._dialog.find('input[name=subscribe]:checked').val();
		var $enableNotification = (this._dialog.find('input[name=enableNotification]').is(':checked')) ? 1 : 0;
		
		this._proxy.setOption('data', {
			actionName: 'saveSubscription',
			className: 'wcf\\data\\user\\object\\watch\\UserObjectWatchAction',
			parameters: {
				enableNotification: $enableNotification,
				objectID: $button.data('objectID'),
				objectType: $button.data('objectType'),
				subscribe: $subscribe
			}
		});
		this._proxy.sendRequest();
	}
});

/**
 * Handles inline editing of users.
 */
WCF.User.InlineEditor = WCF.InlineEditor.extend({
	/**
	 * list of permissions
	 * @var	object
	 */
	_permissions: { },
	
	/**
	 * @see	WCF.InlineEditor._execute()
	 */
	_execute: function(elementID, optionName) {
		if (!this._validate(elementID, optionName)) {
			return false;
		}
		
		var $data = { };
		var $element = $('#' + elementID);
		switch (optionName) {
			case 'unban':
			case 'enableAvatar':
			case 'enableSignature':
				switch (optionName) {
					case 'unban':
						$data.banned = 0;
					break;
					
					case 'enableAvatar':
						$data.disableAvatar = 0;
					break;
					
					case 'enableSignature':
						$data.disableSignature = 0;
					break;
				}
				
				this._proxy.setOption('data', {
					actionName: optionName,
					className: 'wcf\\data\\user\\UserAction',
					objectIDs: [ $element.data('objectID') ]
				});
				this._proxy.sendRequest();
			break;
			
			case 'ban':
			case 'disableAvatar':
			case 'disableSignature':
				if (optionName == 'ban') {
					$data.banned = 1;
				}
				else {
					$data[optionName] = 1;
				}
				
				this._showReasonDialog($element.data('objectID'), optionName);
			break;
			
			case 'advanced':
				window.location = this._getTriggerElement($element).attr('href');
			break;
		}
		
		if ($.getLength($data)) {
			this._updateData.push({
				data: $data,
				elementID: elementID,
			});
		}
	},
	
	/**
	 * Executes an action with a reason.
	 * 
	 * @param	integer		userID
	 * @param	string		optionName
	 * @param	string		reason
	 */
	_executeReasonAction: function(userID, optionName, reason) {
		var $parameters = { };
		$parameters[optionName + WCF.String.ucfirst('reason')] = reason;
		
		this._proxy.setOption('data', {
			actionName: optionName,
			className: 'wcf\\data\\user\\UserAction',
			objectIDs: [ userID ],
			parameters: $parameters
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Returns a specific permission.
	 * 
	 * @param	string		permission
	 * @return	integer
	 */
	_getPermission: function(permission) {
		if (this._permissions[permission]) {
			return this._permissions[permission];
		}
		
		return 0;
	},
	
	/**
	 * @see	WCF.InlineEditor._getTriggerElement()
	 */
	_getTriggerElement: function(element) {
		return element.find('.jsUserInlineEditor');
	},
	
	/**
	 * @see	WCF.InlineEditor._setOptions()
	 */
	_setOptions: function() {
		this._options = [
			// banning
			{ label: WCF.Language.get('wcf.user.ban'), optionName: 'ban' },
			{ label: WCF.Language.get('wcf.user.unban'), optionName: 'unban' },
			
			// disabling avatar
			{ label: WCF.Language.get('wcf.user.disableAvatar'), optionName: 'disableAvatar' },
			{ label: WCF.Language.get('wcf.user.enableAvatar'), optionName: 'enableAvatar' },
			
			// disabling signature
			{ label: WCF.Language.get('wcf.user.disableSignature'), optionName: 'disableSignature' },
			{ label: WCF.Language.get('wcf.user.enableSignature'), optionName: 'enableSignature' },
			
			// divider
			{ optionName: 'divider' },
			
			// overlay
			{ label: WCF.Language.get('wcf.user.edit'), optionName: 'advanced' }
		];
	},
	
	/**
	 * @see	WCF.InlineEditor._show()
	 */
	_show: function(event) {
		var $element = $(event.currentTarget);
		var $elementID = $element.data('elementID');
		
		if (!this._dropdowns[$elementID]) {
			var $dropdownMenu = $element.next('.dropdownMenu');
			
			if ($dropdownMenu) {
				this._dropdowns[$elementID] = $dropdownMenu;
				WCF.Dropdown.initDropdown(this._getTriggerElement(this._elements[$elementID]), true);
			}
		}
		
		return this._super(event);
	},
	
	/**
	 * Shows the dialog to enter a reason for executing the option with the
	 * given name.
	 * 
	 * @param	string		optionName
	 */
	_showReasonDialog: function(userID, optionName) {
		var $languageItem = 'wcf.user.' + optionName + '.reason.description';
		var $reasonDescription = WCF.Language.get($languageItem);
		
		WCF.System.Confirmation.show(WCF.Language.get('wcf.user.' + optionName + '.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._executeReasonAction(userID, optionName, $('#wcfSystemConfirmationContent').find('textarea').val());
			}
		}, this), { }, $('<fieldset><dl><dt>' + WCF.Language.get('wcf.global.reason') + '</dt><dd><textarea cols="40" rows="4" />' + ($reasonDescription != $languageItem ? '<small>' + $reasonDescription + '</small>' : '') + '</dd></dl></fieldset>'));
	},
	
	/**
	 * @see	WCF.InlineEditor._updateState()
	 */
	_updateState: function(data) {
		this._notification.show();
		
		for (var $i = 0, $length = this._updateData.length; $i < $length; $i++) {
			var $data = this._updateData[$i];
			var $element = $('#' + $data.elementID);
			
			for (var $property in $data.data) {
				$element.data($property, $data.data[$property]);
			}
		}
	},
	
	/**
	 * @see	WCF.InlineEditor._validate()
	 */
	_validate: function(elementID, optionName) {
		var $user = $('#' + elementID);
		
		switch (optionName) {
			case 'ban':
			case 'unban':
				if (!this._getPermission('canBanUser')) {
					return false;
				}
				
				if (optionName == 'ban') {
					return !$user.data('banned');
				}
				else {
					return $user.data('banned');
				}
			break;
			
			case 'disableAvatar':
			case 'enableAvatar':
				if (!this._getPermission('canDisableAvatar')) {
					return false;
				}
				
				if (optionName == 'disableAvatar') {
					return !$user.data('disableAvatar');
				}
				else {
					return $user.data('disableAvatar');
				}
			break;
			
			case 'disableSignature':
			case 'enableSignature':
				if (!this._getPermission('canDisableSignature')) {
					return false;
				}
				
				if (optionName == 'disableSignature') {
					return !$user.data('disableSignature');
				}
				else {
					return $user.data('disableSignature');
				}
			break;
			
			case 'advanced':
				return this._getPermission('canEditUser');
			break;
		}
		
		return false;
	},
	
	/**
	 * Sets a permission.
	 * 
	 * @param	string		permission
	 * @param	integer		value
	 */
	setPermission: function(permission, value) {
		this._permissions[permission] = value;
	},
	
	/**
	 * Sets permissions.
	 * 
	 * @param	object		permissions
	 */
	setPermissions: function(permissions) {
		for (var $permission in permissions) {
			this.setPermission($permission, permissions[$permission]);
		}
	}
});


