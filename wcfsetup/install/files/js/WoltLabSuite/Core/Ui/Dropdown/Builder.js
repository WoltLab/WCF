/**
 * Simplified and consistent dropdown creation.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Dropdown/Builder
 */
define(['Core', 'Ui/SimpleDropdown'], function (Core, UiSimpleDropdown) {
	"use strict";
	
	var _validIconSizes = [16, 24, 32, 48, 64, 96, 144];
	
	function _validateList(list) {
		if (!(list instanceof HTMLUListElement)) {
			throw new TypeError('Expected a reference to an <ul> element.');
		}
		
		if (!list.classList.contains('dropdownMenu')) {
			throw new Error('List does not appear to be a dropdown menu.');
		}
	}
	
	function _buildItem(data) {
		var item = elCreate('li');
		
		// handle special `divider` type
		if (data === 'divider') {
			item.className = 'dropdownDivider';
			return item;
		}
		
		if (typeof data.identifier === 'string') {
			elData(item, 'identifier', data.identifier);
		}
		
		var link = elCreate('a');
		link.href = (typeof data.href === 'string') ? data.href : '#';
		if (typeof data.callback === 'function') {
			link.addEventListener(WCF_CLICK_EVENT, function (event) {
				event.preventDefault();
				
				data.callback(link);
			});
		}
		else if (link.getAttribute('href') === '#') {
			throw new Error('Expected either a `href` value or a `callback`.');
		}
		
		if (data.hasOwnProperty('attributes') && Core.isPlainObject(data.attributes)) {
			for (var key in data.attributes) {
				if (data.attributes.hasOwnProperty(key)) {
					elData(link, key, data.attributes[key]);
				}
			}
		}
		
		item.appendChild(link);
		
		if (typeof data.icon !== 'undefined' && Core.isPlainObject(data.icon)) {
			if (typeof data.icon.name !== 'string') {
				throw new TypeError('Expected a valid icon name.');
			}
			
			var size = 16;
			if (typeof data.icon.size === 'number' && _validIconSizes.indexOf(~~data.icon.size) !== -1) {
				size = ~~data.icon.size;
			}
			
			var icon = elCreate('span');
			icon.className = 'icon icon' + size + ' fa-' + data.icon.name;
			
			link.appendChild(icon);
		}
		
		var label = (typeof data.label === 'string') ? data.label.trim() : '';
		var labelHtml = (typeof data.labelHtml === 'string') ? data.labelHtml.trim() : '';
		if (label === '' && labelHtml === '') {
			throw new TypeError('Expected either a label or a `labelHtml`.');
		}
		
		var span = elCreate('span');
		span[label ? 'textContent' : 'innerHTML'] = (label) ? label : labelHtml;
		link.appendChild(document.createTextNode(' '));
		link.appendChild(span);
		
		return item;
	}
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Dropdown/Builder
	 */
	return {
		/**
		 * Creates a new dropdown menu, optionally pre-populated with the supplied list of
		 * dropdown items. The list element will be returned and must be manually injected
		 * into the DOM by the callee.
		 * 
		 * @param       {(Object|string)[]}     items
		 * @param       {string?}               identifier
		 * @return      {Element}
		 */
		create: function (items, identifier) {
			var list = elCreate('ul');
			list.className = 'dropdownMenu';
			if (typeof identifier === 'string') {
				elData(list, 'identifier', identifier);
			}
			
			if (Array.isArray(items) && items.length > 0) {
				this.appendItems(list, items);
			}
			
			return list;
		},
		
		/**
		 * Creates a new dropdown item that can be inserted into lists using regular DOM operations.
		 * 
		 * @param       {(Object|string)}        item
		 * @return      {Element}
		 */
		buildItem: function (item) {
			return _buildItem(item);
		},
		
		/**
		 * Appends a single item to the target list.
		 * 
		 * @param       {Element}               list
		 * @param       {(Object|string)}       item
		 */
		appendItem: function (list, item) {
			_validateList(list);
			
			list.appendChild(_buildItem(item));
		},
		
		/**
		 * Appends a list of items to the target list.
		 * 
		 * @param       {Element}               list
		 * @param       {(Object|string)[]}     items
		 */
		appendItems: function (list, items) {
			_validateList(list);
			
			if (!Array.isArray(items)) {
				throw new TypeError('Expected an array of items.');
			}
			
			var length = items.length;
			if (length === 0) {
				throw new Error('Expected a non-empty list of items.');
			}
			
			if (length === 1) {
				this.appendItem(list, items[0]);
			}
			else {
				var fragment = document.createDocumentFragment();
				for (var i = 0; i < length; i++) {
					fragment.appendChild(_buildItem(items[i]));
				}
				list.appendChild(fragment);
			}
		},
		
		/**
		 * Replaces the existing list items with the provided list of new items.
		 * 
		 * @param       {Element}               list
		 * @param       {(Object|string)[]}     items
		 */
		setItems: function (list, items) {
			_validateList(list);
			
			list.innerHTML = '';
			
			this.appendItems(list, items);
		},
		
		/**
		 * Attaches the list to a button, visibility is from then on controlled through clicks
		 * on the provided button element. Internally calls `Ui/SimpleDropdown.initFragment()`
		 * to delegate the DOM management.
		 * 
		 * @param       {Element}               list
		 * @param       {Element}               button
		 */
		attach: function (list, button) {
			_validateList(list);
			
			UiSimpleDropdown.initFragment(button, list);
			
			button.addEventListener(WCF_CLICK_EVENT, function (event) {
				event.preventDefault();
				event.stopPropagation();
				
				UiSimpleDropdown.toggleDropdown(button.id);
			});
		},
		
		/**
		 * Helper method that returns the special string `"divider"` that causes a divider to
		 * be created.
		 * 
		 * @return      {string}
		 */
		divider: function () {
			return 'divider';
		}
	};
});
