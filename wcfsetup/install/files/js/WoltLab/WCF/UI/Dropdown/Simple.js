/**
 * Simple Dropdown
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/Dropdown/Simple
 */
define(
	[       'CallbackList', 'Core', 'Dictionary', 'UI/Alignment', 'DOM/ChangeListener', 'DOM/Traverse', 'DOM/Util', 'UI/CloseOverlay'],
	function(CallbackList,   Core,   Dictionary,   UIAlignment,    DOMChangeListener,    DOMTraverse,    DOMUtil,    UICloseOverlay)
{
	"use strict";
	
	var _availableDropdowns = null;
	var _callbacks = new CallbackList();
	var _dropdowns = new Dictionary();
	var _menus = new Dictionary();
	var _menuContainer = null;
	
	/**
	 * @exports	WoltLab/WCF/UI/Dropdown/Simple
	 */
	var SimpleDropdown = {
		/**
		 * Performs initial setup such as setting up dropdowns and binding listeners.
		 */
		setup: function() {
			_menuContainer = document.createElement('div');
			_menuContainer.setAttribute('id', 'dropdownMenuContainer');
			document.body.appendChild(_menuContainer);
			
			_availableDropdowns = document.getElementsByClassName('dropdownToggle');
			
			this.initAll();
			
			UICloseOverlay.add('WoltLab/WCF/UI/Dropdown/Simple', this.closeAll.bind(this));
			DOMChangeListener.add('WoltLab/WCF/UI/Dropdown/Simple', this.initAll.bind(this));
			
			document.addEventListener('scroll', this._onScroll.bind(this));
			
			// expose on window object for backward compatibility
			window.bc_wcfSimpleDropdown = this;
		},
		
		/**
		 * Loops through all possible dropdowns and registers new ones.
		 */
		initAll: function() {
			for (var i = 0, length = _availableDropdowns.length; i < length; i++) {
				this.init(_availableDropdowns[i], false);
			}
		},
		
		/**
		 * Initializes a dropdown.
		 * 
		 * @param	{Element}	button
		 * @param	{boolean}	isLazyInitialization
		 */
		init: function(button, isLazyInitialization) {
			if (button.classList.contains('jsDropdownEnabled') || button.getAttribute('data-target')) {
				return false;
			}
			
			var dropdown = DOMTraverse.parentByClass(button, 'dropdown');
			if (dropdown === null) {
				throw new Error("Invalid dropdown passed, button '" + DOMUtil.identify(button) + "' does not have a parent with .dropdown.");
			}
			
			var menu = DOMTraverse.nextByClass(button, 'dropdownMenu');
			if (menu === null) {
				throw new Error("Invalid dropdown passed, button '" + DOMUtil.identify(button) + "' does not have a menu as next sibling.");
			}
			
			// move menu into global container
			_menuContainer.appendChild(menu);
			
			var containerId = DOMUtil.identify(dropdown);
			if (!_dropdowns.has(containerId)) {
				button.classList.add('jsDropdownEnabled');
				button.addEventListener('click', this._toggle.bind(this));
				
				_dropdowns.set(containerId, dropdown);
				_menus.set(containerId, menu);
				
				if (!containerId.match(/^wcf\d+$/)) {
					menu.setAttribute('data-source', containerId);
				}
			}
			
			button.setAttribute('data-target', containerId);
			
			if (isLazyInitialization) {
				Core.triggerEvent(button, 'click');
			}
		},
		
		/**
		 * Initializes a remote-controlled dropdown.
		 * 
		 * @param	{Element}	dropdown	dropdown wrapper element
		 * @param	{Element}	menu		menu list element
		 */
		initFragment: function(dropdown, menu) {
			var containerId = DOMUtil.identify(dropdown);
			if (_dropdowns.has(dropdown)) {
				throw new Error("Dropdown identified by '" + DOMUtil.identify(dropdown) + "' has already been registered.");
			}
			
			_dropdowns.set(containerId, dropdown);
			_menuContainer.appendChild(menu);
			
			_menus.set(containerId, menu);
		},
		
		/**
		 * Registers a callback for open/close events.
		 * 
		 * @param	{string}			containerId	dropdown wrapper id
		 * @param	{function(string, string)}	callback
		 */
		registerCallback: function(containerId, callback) {
			_callbacks.add(containerId, callback);
		},
		
		/**
		 * Returns the requested dropdown wrapper element.
		 * 
		 * @return	{Element}	dropdown wrapper element
		 */
		getDropdown: function(containerId) {
			return _dropdowns.get(containerId);
		},
		
		/**
		 * Returns the requested dropdown menu list element.
		 * 
		 * @return	{Element}	menu list element
		 */
		getDropdownMenu: function(containerId) {
			return _menus.get(containerId);
		},
		
		/**
		 * Toggles the requested dropdown between opened and closed.
		 * 
		 * @param	{string}	containerId	dropdown wrapper id
		 */
		toggleDropdown: function(containerId) {
			this._toggle(null, containerId);
		},
		
		/**
		 * Calculates and sets the alignment of given dropdown.
		 * 
		 * @param	{Element}	dropdown	dropdown wrapper element
		 * @param	{Element}	dropdownMenu	menu list element
		 */
		setAlignment: function(dropdown, dropdownMenu) {
			// check if button belongs to an i18n textarea
			var button = dropdown.querySelector('.dropdownToggle');
			var refDimensionsElement = null;
			if (button !== null && button.classList.contains('dropdownCaptionTextarea')) {
				refDimensionsElement = button;
			}
			
			UIAlignment.set(dropdownMenu, dropdown, {
				pointerClassNames: ['dropdownArrowBottom', 'dropdownArrowRight'],
				refDimensionsElement: refDimensionsElement
			});
		},
		
		/**
		 * Calculats and sets the alignment of the dropdown identified by given id.
		 * 
		 * @param	{string}	containerId	dropdown wrapper id
		 */
		setAlignmentById: function(containerId) {
			var dropdown = _dropdowns.get(containerId);
			if (dropdown === undefined) {
				throw new Error("Unknown dropdown identifier '" + containerId + "'.");
			}
			
			var menu = _menus.get(containerId);
			
			this.setAlignment(dropdown, menu);
		},
		
		/**
		 * Closes the dropdown identified by given id without notifying callbacks.
		 * 
		 * @param	{string}	containerId	dropdown wrapper id
		 */
		close: function(containerId) {
			var dropdown = _dropdowns.get(containerId);
			if (dropdown !== undefined) {
				dropdown.classList.remove('dropdownOpen');
				_menus.get(containerId).classList.remove('dropdownOpen');
			}
		},
		
		/**
		 * Closes all dropdowns.
		 */
		closeAll: function() {
			_dropdowns.forEach((function(dropdown, containerId) {
				if (dropdown.classList.contains('dropdownOpen')) {
					dropdown.classList.remove('dropdownOpen');
					_menus.get(containerId).classList.remove('dropdownOpen');
					
					this._notifyCallbacks(containerId, 'close');
				}
			}).bind(this));
		},
		
		/**
		 * Destroys a dropdown identified by given id.
		 * 
		 * @param	{string}	containerId	dropdown wrapper id
		 * @return	{boolean}	false for unknown dropdowns
		 */
		destroy: function(containerId) {
			if (!_dropdowns.has(containerId)) {
				return false;
			}
			
			this.close(containerId);
			
			var menu = _menus.get(containerId);
			_menus.parentNode.removeChild(menu);
			
			_menus['delete'](containerId);
			_dropdowns['delete'](containerId);
			
			return true;
		},
		
		/**
		 * Handles dropdown positions in overlays when scrolling in the overlay.
		 * 
		 * @param	{Event}		event	event object
		 */
		_onDialogScroll: function(event) {
			var dialogContent = event.currentTarget;
			var dropdowns = dialogContent.querySelectorAll('.dropdown.dropdownOpen');
			
			for (var i = 0, length = dropdowns.length; i < length; i++) {
				var dropdown = dropdowns[i];
				var containerId = DOMUtil.identify(dropdown);
				var offset = DOMUtil.offset(dropdown);
				var dialogOffset = DOMUtil.offset(dialogContent);
				
				// check if dropdown toggle is still (partially) visible
				if (offset.top + dropdown.clientHeight <= dialogOffset.top) {
					// top check
					this.toggleDropdown(containerId);
				}
				else if (offset.top >= dialogOffset.top + dialogContent.offsetHeight) {
					// bottom check
					this.toggleDropdown(containerId);
				}
				else if (offset.left <= dialogOffset.left) {
					// left check
					this.toggleDropdown(containerId);
				}
				else if (offset.left >= dialogOffset.left + dialogContent.offsetWidth) {
					// right check
					this.toggleDropdown(containerId);
				}
				else {
					this.setAlignment(containerId, _menus.get(containerId));
				}
			}
		},
		
		/**
		 * Recalculates dropdown positions on page scroll.
		 */
		_onScroll: function() {
			_dropdowns.forEach((function(dropdown, containerId) {
				if (dropdown.getAttribute('data-is-overlay-dropdown-button') === true && dropdown.classList.contains('dropdownOpen')) {
					this.setAlignment(dropdown, _menus.get(containerId));
				}
			}).bind(this));
		},
		
		/**
		 * Notifies callbacks on status change.
		 * 
		 * @param	{string}	containerId	dropdown wrapper id
		 * @param	{string}	action		can be either 'open' or 'close'
		 */
		_notifyCallbacks: function(containerId, action) {
			_callbacks.forEach(containerId, function(callback) {
				callback(containerId, action);
			});
		},
		
		/**
		 * Toggles the dropdown's state between open and close.
		 * 
		 * @param	{?Event}	event		event object, should be 'null' if targetId is given
		 * @param	{string=}	targetId	dropdown wrapper id
		 * @return	{boolean}	'false' if event is not null
		 */
		_toggle: function(event, targetId) {
			targetId = (event === null) ? targetId : event.currentTarget.getAttribute('data-target');
			
			// check if 'isOverlayDropdownButton' is set which indicates if
			// the dropdown toggle is in an overlay
			var dropdown = _dropdowns.get(targetId);
			if (dropdown !== undefined && dropdown.getAttribute('data-is-overlay-dropdown-button') === null) {
				var dialogContent = DOMTraverse.parentByClass(dropdown, 'dialogContent');
				dropdown.setAttribute('data-is-overlay-dropdown-button', (dialogContent !== null));
				
				if (dialogContent !== null) {
					dialogContent.addEventListener('scroll', this._onDialogScroll.bind(this));
				}
			}
			
			// close all dropdowns
			_dropdowns.forEach((function(dropdown, containerId) {
				var menu = _menus.get(containerId);
				
				if (dropdown.classList.contains('dropdownOpen')) {
					dropdown.classList.remove('dropdownOpen');
					menu.classList.remove('dropdownOpen');
					
					this._notifyCallbacks(containerId, 'close');
				}
				else if (containerId === targetId && menu.childElementCount > 0) {
					dropdown.classList.add('dropdownOpen');
					menu.classList.add('dropdownOpen');
					
					this._notifyCallbacks(containerId, 'open');
					
					this.setAlignment(dropdown, menu);
				}
			}).bind(this));
			
			// TODO
			WCF.Dropdown.Interactive.Handler.closeAll();
			
			if (event !== null) {
				event.stopPropagation();
				return false;
			}
			
			return true;
		}
	};
	
	return SimpleDropdown;
});
