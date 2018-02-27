/**
 * Wrapper class to provide color picker support. Constructing a new object does not
 * guarantee the picker to be ready at the time of call.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Color/Picker
 */
define(['Core'], function (Core) {
	"use strict";
	
	var _marshal = function (element, options) {
		if (typeof window.WCF === 'object' && typeof window.WCF.ColorPicker === 'function') {
			_marshal = function (element, options) {
				var picker = new window.WCF.ColorPicker(element);
				
				if (typeof options.callbackSubmit === 'function') {
					picker.setCallbackSubmit(options.callbackSubmit);
				}
				
				return picker;
			};
			
			return _marshal(element, options);
		}
		else {
			if (_queue.length === 0) {
				window.__wcf_bc_colorPickerInit = function () {
					_queue.forEach(function (data) {
						_marshal(data[0], data[1]);
					});
					
					window.__wcf_bc_colorPickerInit = undefined;
					_queue = [];
				};
			}
			
			_queue.push([element, options]);
		}
	};
	var _queue = [];
	
	/**
	 * @constructor
	 */
	function UiColorPicker(element, options) { this.init(element, options); }
	UiColorPicker.prototype = {
		/**
		 * Initializes a new color picker instance. This is actually just a wrapper that does
		 * not guarantee the picker to be ready at the time of call.
		 * 
		 * @param       {Element}       element         input element
		 * @param       {Object}        options         list of initialization options
		 */
		init: function (element, options) {
			if (!(element instanceof Element)) {
				throw new TypeError("Expected a valid DOM element, use `UiColorPicker.fromSelector()` if you want to use a CSS selector.");
			}
			
			this._options = Core.extend({
				callbackSubmit: null
			}, options);
			
			_marshal(element, options);
		}
	};
	
	/**
	 * Initializes a color picker for all input elements matching the given selector.
	 * 
	 * @param       {string}        selector        CSS selector
	 */
	UiColorPicker.fromSelector = function (selector) {
		elBySelAll(selector, undefined, function (element) {
			new UiColorPicker(element);
		});
	};
	
	return UiColorPicker;
});
