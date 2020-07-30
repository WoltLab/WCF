/**
 * Provides page actions such as "jump to top" and clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Action
 */
define(['Dictionary', 'Dom/Util'], function(Dictionary, DomUtil) {
	"use strict";
	
	var _buttons = new Dictionary();
	var _container = null;
	var _didInit = false;
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Page/Action
	 */
	return {
		/**
		 * Initializes the page action container.
		 */
		setup: function() {
			_didInit = true;
			
			_container = elCreate('ul');
			_container.className = 'pageAction';
			document.body.appendChild(_container);
		},
		
		/**
		 * Adds a button to the page action list. You can optionally provide a button name to
		 * insert the button right before it. Unmatched button names or empty value will cause
		 * the button to be prepended to the list.
		 * 
		 * @param       {string}        buttonName              unique identifier
		 * @param       {Element}       button                  button element, must not be wrapped in a <li>
		 * @param       {string=}       insertBeforeButton      insert button before element identified by provided button name
		 */
		add: function(buttonName, button, insertBeforeButton) {
			if (_didInit === false) this.setup();
			
			var listItem = elCreate('li');
			button.classList.add('button');
			button.classList.add('buttonPrimary');
			listItem.appendChild(button);
			elAttr(listItem, 'aria-hidden', (buttonName === 'toTop' ? 'true' : 'false'));
			elData(listItem, 'name', buttonName);
			
			// force 'to top' button to be always at the most outer position
			if (buttonName === 'toTop') {
				listItem.className = 'toTop initiallyHidden';
				_container.appendChild(listItem);
			}
			else {
				var insertBefore = null;
				if (insertBeforeButton) {
					insertBefore = _buttons.get(insertBeforeButton);
					if (insertBefore !== undefined) {
						insertBefore = insertBefore.parentNode;
					}
				}
				
				if (insertBefore === null && _container.childElementCount) {
					insertBefore = _container.children[0];
				}
				
				if (insertBefore === null) {
					DomUtil.prepend(listItem, _container);
				}
				else {
					_container.insertBefore(listItem, insertBefore);
				}
			}
			
			_buttons.set(buttonName, button);
			this._renderContainer();
		},
		
		/**
		 * Returns true if there is a registered button with the provided name.
		 * 
		 * @param       {string}        buttonName      unique identifier
		 * @return      {boolean}       true if there is a registered button with this name
		 */
		has: function (buttonName) {
			return _buttons.has(buttonName);
		},
		
		/**
		 * Returns the stored button by name or undefined.
		 * 
		 * @param       {string}        buttonName      unique identifier
		 * @return      {Element}       button element or undefined
		 */
		get: function(buttonName) {
			return _buttons.get(buttonName);
		},
		
		/**
		 * Removes a button by its button name.
		 * 
		 * @param       {string}        buttonName      unique identifier
		 */
		remove: function(buttonName) {
			var button = _buttons.get(buttonName);
			if (button !== undefined) {
				var listItem = button.parentNode;
				var callback = function () {
					try {
						if (elAttrBool(listItem, 'aria-hidden')) {
							_container.removeChild(listItem);
							_buttons.delete(buttonName);
						}
						
						listItem.removeEventListener('animationend', callback);
					}
					catch (e) {
						// ignore errors if the element has already been removed
					}
				};
				
				listItem.addEventListener('animationend', callback);
				
				this.hide(buttonName);
			}
		},
		
		/**
		 * Hides a button by its button name.
		 * 
		 * @param       {string}        buttonName      unique identifier
		 */
		hide: function(buttonName) {
			var button = _buttons.get(buttonName);
			if (button) {
				elAttr(button.parentNode, 'aria-hidden', 'true');
				this._renderContainer();
			}
		},
		
		/**
		 * Shows a button by its button name.
		 * 
		 * @param       {string}        buttonName      unique identifier
		 */
		show: function(buttonName) {
			var button = _buttons.get(buttonName);
			if (button) {
				if (button.parentNode.classList.contains('initiallyHidden')) {
					button.parentNode.classList.remove('initiallyHidden');
				}
				
				elAttr(button.parentNode, 'aria-hidden', 'false');
				this._renderContainer();
			}
		},
		
		/**
		 * Toggles the container's visibility.
		 * 
		 * @protected
		 */
		_renderContainer: function() {
			var hasVisibleItems = false;
			if (_container.childElementCount) {
				for (var i = 0, length = _container.childElementCount; i < length; i++) {
					if (elAttr(_container.children[i], 'aria-hidden') === 'false') {
						hasVisibleItems = true;
						break;
					}
				}
			}
			
			_container.classList[(hasVisibleItems ? 'add' : 'remove')]('active');
		}
	};
});
