/**
 * Provides page actions such as "jump to top" and clipboard actions.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Action
 */
define(['Dictionary', 'Language'], function (Dictionary, Language) {
	'use strict';
	
	var _buttons = new Dictionary();
	
	/** @var {Element} */
	var _container;
	
	var _didInit = false;
	
	var _lastPosition = -1;
	
	/** @var {Element} */
	var _toTopButton;
	
	/** @var {Element} */
	var _wrapper;
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Page/Action
	 */
	return {
		/**
		 * Initializes the page action container.
		 */
		setup: function () {
			_didInit = true;
			
			_wrapper = elCreate('div');
			_wrapper.className = 'pageAction';
			
			_container = elCreate('div');
			_container.className = 'pageActionButtons';
			_wrapper.appendChild(_container);
			
			_toTopButton = this._buildToTopButton();
			_wrapper.appendChild(_toTopButton);
			
			document.body.appendChild(_wrapper);
			
			window.addEventListener(
				'scroll',
				window.debounce(this._onScroll.bind(this), 100, false),
				{passive: true}
			);
			
			this._onScroll();
		},
		
		_buildToTopButton: function () {
			var button = elCreate('a');
			button.className = 'button buttonPrimary pageActionButtonToTop initiallyHidden jsTooltip';
			button.href = '';
			elAttr(button, 'title', Language.get('wcf.global.scrollUp'));
			elAttr(button, 'aria-hidden', 'true');
			button.innerHTML = '<span class="icon icon32 fa-angle-up"></span>';
			
			button.addEventListener(WCF_CLICK_EVENT, this._scrollTopTop.bind(this));
			
			return button;
		},
		
		/**
		 * @param {Event=} event
		 */
		_onScroll: function (event) {
			var offset = window.pageYOffset;
			
			if (offset >= 300) {
				if (_toTopButton.classList.contains('initiallyHidden')) {
					_toTopButton.classList.remove('initiallyHidden');
				}
				
				elAttr(_toTopButton, 'aria-hidden', 'false');
			}
			else {
				elAttr(_toTopButton, 'aria-hidden', 'true');
			}
			
			this._renderContainer();
			
			if (_lastPosition !== -1) {
				_wrapper.classList[offset < _lastPosition ? 'remove' : 'add']('scrolledDown');
			}
			
			_lastPosition = offset;
		},
		
		/**
		 * @param {Event} event
		 */
		_scrollTopTop: function (event) {
			event.preventDefault();
			
			elById('top').scrollIntoView({behavior: 'smooth'});
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
		add: function (buttonName, button, insertBeforeButton) {
			if (_didInit === false) this.setup();
			
			// The wrapper is required for backwards compatibility, because some implementations rely on a
			// dedicated parent element to insert elements, for example, for drop-down menus.
			var wrapper = elCreate('div');
			wrapper.className = 'pageActionButton';
			wrapper.name = buttonName;
			elAttr(wrapper, 'aria-hidden', 'true');
			
			button.classList.add('button');
			button.classList.add('buttonPrimary');
			wrapper.appendChild(button);
			
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
				insertBefore = _container.firstChild;
			}
			
			_container.insertBefore(wrapper, insertBefore);
			_wrapper.classList.remove('scrolledDown');
			
			_buttons.set(buttonName, button);
			
			// Query a layout related property to force a reflow, otherwise the transition is optimized away.
			// noinspection BadExpressionStatementJS
			wrapper.offsetParent;
			
			// Toggle the visibility to force the transition to be applied.
			elAttr(wrapper, 'aria-hidden', 'false');
			
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
		get: function (buttonName) {
			return _buttons.get(buttonName);
		},
		
		/**
		 * Removes a button by its button name.
		 *
		 * @param       {string}        buttonName      unique identifier
		 */
		remove: function (buttonName) {
			var button = _buttons.get(buttonName);
			if (button !== undefined) {
				var listItem = button.parentNode;
				var callback = function () {
					try {
						if (elAttrBool(listItem, 'aria-hidden')) {
							_container.removeChild(listItem);
							_buttons.delete(buttonName);
						}
						
						listItem.removeEventListener('transitionend', callback);
					}
					catch (e) {
						// ignore errors if the element has already been removed
					}
				};
				
				listItem.addEventListener('transitionend', callback);
				
				this.hide(buttonName);
			}
		},
		
		/**
		 * Hides a button by its button name.
		 *
		 * @param       {string}        buttonName      unique identifier
		 */
		hide: function (buttonName) {
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
		show: function (buttonName) {
			var button = _buttons.get(buttonName);
			if (button) {
				if (button.parentNode.classList.contains('initiallyHidden')) {
					button.parentNode.classList.remove('initiallyHidden');
				}
				
				elAttr(button.parentNode, 'aria-hidden', 'false');
				_wrapper.classList.remove('scrolledDown');
				this._renderContainer();
			}
		},
		
		/**
		 * Toggles the container's visibility.
		 *
		 * @protected
		 */
		_renderContainer: function () {
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
