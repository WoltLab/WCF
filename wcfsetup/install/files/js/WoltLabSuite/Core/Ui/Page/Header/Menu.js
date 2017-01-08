/**
 * Handles main menu overflow.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Header/Menu
 */
define(['Ui/Screen'], function(UiScreen) {
	"use strict";
	
	var _enabled = false;
	
	// elements
	var _buttonShowNext, _buttonShowPrevious, _firstElement, _menu;
	
	// internal states
	var _marginLeft = 0, _invisibleLeft = [], _invisibleRight = [];
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Page/Header/Menu
	 */
	return {
		/**
		 * Initializes the main menu overflow handling.
		 */
		init: function () {
			_menu = elBySel('.mainMenu .boxMenu');
			_firstElement = (_menu && _menu.childElementCount) ? _menu.children[0] : null;
			if (_firstElement === null) {
				throw new Error("Unable to find the menu.");
			}
			
			UiScreen.on('screen-lg', {
				enable: this._enable.bind(this),
				disable: this._disable.bind(this),
				setup: this._setup.bind(this)
			});
		},
		
		/**
		 * Enables the overflow handler.
		 * 
		 * @protected
		 */
		_enable: function () {
			_enabled = true;
			
			this._rebuildVisibility();
		},
		
		/**
		 * Disables the overflow handler.
		 * 
		 * @protected
		 */
		_disable: function () {
			_enabled = false;
		},
		
		/**
		 * Displays the next three menu items.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_showNext: function(event) {
			event.preventDefault();
			
			if (_invisibleRight.length) {
				var showItem = _invisibleRight.slice(0, 3).pop();
				this._setMarginLeft(_menu.clientWidth - (showItem.offsetLeft + showItem.clientWidth));
				
				if (_menu.lastElementChild === showItem) {
					_buttonShowNext.classList.remove('active');
				}
				
				_buttonShowPrevious.classList.add('active');
			}
		},
		
		/**
		 * Displays the previous three menu items.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_showPrevious: function (event) {
			event.preventDefault();
			
			if (_invisibleLeft.length) {
				var showItem = _invisibleLeft.slice(-3)[0];
				this._setMarginLeft(showItem.offsetLeft * -1);
				
				if (_menu.firstElementChild === showItem) {
					_buttonShowPrevious.classList.remove('active');
				}
				
				_buttonShowNext.classList.add('active');
			}
		},
		
		/**
		 * Sets the first item's margin-left value that is
		 * used to move the menu contents around.
		 * 
		 * @param       {int}   offset  changes to the margin-left value in pixel
		 * @protected
		 */
		_setMarginLeft: function (offset) {
			_marginLeft = Math.min(_marginLeft + offset, 0);
			
			_firstElement.style.setProperty('margin-left', _marginLeft + 'px', '');
		},
		
		/**
		 * Toggles button overlays and rebuilds the list
		 * of invisible items from left to right.
		 * 
		 * @protected
		 */
		_rebuildVisibility: function () {
			if (!_enabled) return;
			
			_invisibleLeft = [];
			_invisibleRight = [];
			
			var menuWidth = _menu.clientWidth;
			if (_menu.scrollWidth > menuWidth || _marginLeft < 0) {
				var child;
				for (var i = 0, length = _menu.childElementCount; i < length; i++) {
					child = _menu.children[i];
					
					var offsetLeft = child.offsetLeft;
					if (offsetLeft < 0) {
						_invisibleLeft.push(child);
					}
					else if (offsetLeft + child.clientWidth > menuWidth) {
						_invisibleRight.push(child);
					}
				}
			}
			
			_buttonShowPrevious.classList[(_invisibleLeft.length ? 'add' : 'remove')]('active');
			_buttonShowNext.classList[(_invisibleRight.length ? 'add' : 'remove')]('active');
		},
		
		/**
		 * Builds the UI and binds the event listeners.
		 * 
		 * @protected
		 */
		_setup: function () {
			_buttonShowNext = elCreate('a');
			_buttonShowNext.className = 'mainMenuShowNext';
			_buttonShowNext.href = '#';
			_buttonShowNext.innerHTML = '<span class="icon icon32 fa-angle-right"></span>';
			_buttonShowNext.addEventListener(WCF_CLICK_EVENT, this._showNext.bind(this));
			
			_menu.parentNode.appendChild(_buttonShowNext);
			
			_buttonShowPrevious = elCreate('a');
			_buttonShowPrevious.className = 'mainMenuShowPrevious';
			_buttonShowPrevious.href = '#';
			_buttonShowPrevious.innerHTML = '<span class="icon icon32 fa-angle-left"></span>';
			_buttonShowPrevious.addEventListener(WCF_CLICK_EVENT, this._showPrevious.bind(this));
			
			_menu.parentNode.insertBefore(_buttonShowPrevious, _menu.parentNode.firstChild);
			
			var rebuildVisibility = this._rebuildVisibility.bind(this);
			_firstElement.addEventListener('transitionend', rebuildVisibility);
			
			window.addEventListener('resize', function () {
				_firstElement.style.setProperty('margin-left', '0px', '');
				_marginLeft = 0;
				
				rebuildVisibility();
			});
			
			this._enable();
		}
	};
});
