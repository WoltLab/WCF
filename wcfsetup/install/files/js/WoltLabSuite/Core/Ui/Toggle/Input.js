/**
 * Provides a simple toggle to show or hide certain elements when the
 * target element is checked.
 * 
 * Be aware that the list of elements to show or hide accepts selectors
 * which will be passed to `elBySel()`, causing only the first matched
 * element to be used. If you require a whole list of elements identified
 * by a single selector to be handled, please provide the actual list of
 * elements instead.
 * 
 * Usage:
 * 
 * new UiToggleInput('input[name="foo"][value="bar"]', {
 *      show: ['#showThisContainer', '.makeThisVisibleToo'],
 *      hide: ['.notRelevantStuff', elById('fooBar')]
 * });
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Toggle/Input
 */
define(['Core'], function(Core) {
	"use strict";
	
	/**
	 * @param       {string}        elementSelector         element selector used with `elBySel()`
	 * @param       {Object}        options                 toggle options
	 * @constructor
	 */
	function UiToggleInput(elementSelector, options) { this.init(elementSelector, options); }
	UiToggleInput.prototype = {
		/**
		 * Initializes a new input toggle.
		 * 
		 * @param       {string}        elementSelector         element selector used with `elBySel()`
		 * @param       {Object}        options                 toggle options
		 */
		init: function(elementSelector, options) {
			this._element = elBySel(elementSelector);
			if (this._element === null) {
				throw new Error("Unable to find element by selector '" + elementSelector + "'.");
			}
			
			var type = (this._element.nodeName === 'INPUT') ? elAttr(this._element, 'type') : '';
			if (type !== 'checkbox' && type !== 'radio') {
				throw new Error("Illegal element, expected input[type='checkbox'] or input[type='radio'].");
			}
			
			this._options = Core.extend({
				hide: [],
				show: []
			}, options);
			
			['hide', 'show'].forEach((function(type) {
				var element, i, length;
				for (i = 0, length = this._options[type].length; i < length; i++) {
					element = this._options[type][i];
					
					if (typeof element !== 'string' && !(element instanceof Element)) {
						throw new TypeError("The array '" + type + "' may only contain string selectors or DOM elements.");
					}
				}
			}).bind(this));
			
			this._element.addEventListener('change', this._change.bind(this));
			
			this._handleElements(this._options.show, this._element.checked);
			this._handleElements(this._options.hide, !this._element.checked);
		},
		
		/**
		 * Triggered when element is checked / unchecked.
		 * 
		 * @param       {Event}         event   event object
		 * @protected
		 */
		_change: function(event) {
			var showElements = event.currentTarget.checked;
			
			this._handleElements(this._options.show, showElements);
			this._handleElements(this._options.hide, !showElements);
		},
		
		/**
		 * Loops through the target elements and shows / hides them.
		 * 
		 * @param       {Array}         elements        list of elements or selectors
		 * @param       {boolean}       showElement     true if elements should be shown
		 * @protected
		 */
		_handleElements: function(elements, showElement) {
			var element, tmp;
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				if (typeof element === 'string') {
					tmp = elBySel(element);
					if (tmp === null) {
						throw new Error("Unable to find element by selector '" + element + "'.");
					}
					
					elements[i] = element = tmp;
				}
				
				window[(showElement ? 'elShow' : 'elHide')](element);
			}
		}
	};
	
	return UiToggleInput;
});
