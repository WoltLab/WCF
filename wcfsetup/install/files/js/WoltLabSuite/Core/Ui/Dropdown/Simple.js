/**
 * Simple dropdown implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Dropdown/Simple
 */
define(
	[       'CallbackList', 'Core', 'Dictionary', 'Ui/Alignment', 'Dom/ChangeListener', 'Dom/Traverse', 'Dom/Util', 'Ui/CloseOverlay'],
	function(CallbackList,   Core,   Dictionary,   UiAlignment,    DomChangeListener,    DomTraverse,    DomUtil,    UiCloseOverlay)
{
	"use strict";
	
	var _availableDropdowns = null;
	var _callbacks = new CallbackList();
	var _didInit = false;
	var _dropdowns = new Dictionary();
	var _menus = new Dictionary();
	var _menuContainer = null;
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/Dropdown/Simple
	 */
	return {
		/**
		 * Performs initial setup such as setting up dropdowns and binding listeners.
		 */
		setup: function() {
			if (_didInit) return;
			_didInit = true;
			
			_menuContainer = elCreate('div');
			_menuContainer.className = 'dropdownMenuContainer';
			document.body.appendChild(_menuContainer);
			
			_availableDropdowns = elByClass('dropdownToggle');
			
			this.initAll();
			
			UiCloseOverlay.add('WoltLabSuite/Core/Ui/Dropdown/Simple', this.closeAll.bind(this));
			DomChangeListener.add('WoltLabSuite/Core/Ui/Dropdown/Simple', this.initAll.bind(this));
			
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
			this.setup();
			
			if (button.classList.contains('jsDropdownEnabled') || elData(button, 'target')) {
				return false;
			}
			
			var dropdown = DomTraverse.parentByClass(button, 'dropdown');
			if (dropdown === null) {
				throw new Error("Invalid dropdown passed, button '" + DomUtil.identify(button) + "' does not have a parent with .dropdown.");
			}
			
			var menu = DomTraverse.nextByClass(button, 'dropdownMenu');
			if (menu === null) {
				throw new Error("Invalid dropdown passed, button '" + DomUtil.identify(button) + "' does not have a menu as next sibling.");
			}
			
			// move menu into global container
			_menuContainer.appendChild(menu);
			
			var containerId = DomUtil.identify(dropdown);
			if (!_dropdowns.has(containerId)) {
				button.classList.add('jsDropdownEnabled');
				button.addEventListener(WCF_CLICK_EVENT, this._toggle.bind(this));
				
				_dropdowns.set(containerId, dropdown);
				_menus.set(containerId, menu);
				
				if (!containerId.match(/^wcf\d+$/)) {
					elData(menu, 'source', containerId);
				}
				
				// prevent page scrolling
				if (menu.childElementCount && menu.children[0].classList.contains('scrollableDropdownMenu')) {
					menu = menu.children[0];
					elData(menu, 'scroll-to-active', true);
					
					var menuHeight = null, menuRealHeight = null;
					menu.addEventListener('wheel', function (event) {
						if (menuHeight === null) menuHeight = menu.clientHeight;
						if (menuRealHeight === null) menuRealHeight = menu.scrollHeight;
						
						// negative value: scrolling up
						if (event.deltaY < 0 && menu.scrollTop === 0) {
							event.preventDefault();
						}
						else if (event.deltaY > 0 && (menu.scrollTop + menuHeight === menuRealHeight)) {
							event.preventDefault();
						}
					}, { passive: false });
				}
			}
			
			elData(button, 'target', containerId);
			
			if (isLazyInitialization) {
				setTimeout(function() { Core.triggerEvent(button, WCF_CLICK_EVENT); }, 10);
			}
		},
		
		/**
		 * Initializes a remote-controlled dropdown.
		 * 
		 * @param	{Element}	dropdown	dropdown wrapper element
		 * @param	{Element}	menu		menu list element
		 */
		initFragment: function(dropdown, menu) {
			this.setup();
			
			var containerId = DomUtil.identify(dropdown);
			if (_dropdowns.has(containerId)) {
				return;
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
		 * @param	{string}	containerId	        dropdown wrapper id
		 * @param       {Element=}      referenceElement        alternative reference element, used for reusable dropdown menus
		 */
		toggleDropdown: function(containerId, referenceElement) {
			this._toggle(null, containerId, referenceElement);
		},
		
		/**
		 * Calculates and sets the alignment of given dropdown.
		 * 
		 * @param	{Element}	dropdown	        dropdown wrapper element
		 * @param	{Element}	dropdownMenu	        menu list element
		 * @param       {Element=}      alternateElement        alternative reference element for alignment
		 */
		setAlignment: function(dropdown, dropdownMenu, alternateElement) {
			// check if button belongs to an i18n textarea
			var button = elBySel('.dropdownToggle', dropdown), refDimensionsElement;
			if (button !== null && button.parentNode.classList.contains('inputAddonTextarea')) {
				refDimensionsElement = button;
			}
			
			UiAlignment.set(dropdownMenu, alternateElement || dropdown, {
				pointerClassNames: ['dropdownArrowBottom', 'dropdownArrowRight'],
				refDimensionsElement: refDimensionsElement || null,
				
				// alignment
				horizontal: (elData(dropdownMenu, 'dropdown-alignment-horizontal') === 'right') ? 'right' : 'left',
				vertical: (elData(dropdownMenu, 'dropdown-alignment-vertical') === 'top') ? 'top' : 'bottom'
			});
		},
		
		/**
		 * Calculates and sets the alignment of the dropdown identified by given id.
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
		 * Returns true if target dropdown exists and is open.
		 * 
		 * @param	{string}	containerId	dropdown wrapper id
		 * @return	{boolean}	true if dropdown exists and is open
		 */
		isOpen: function(containerId) {
			var menu = _menus.get(containerId);
			return (menu !== undefined && menu.classList.contains('dropdownOpen'));
		},
		
		/**
		 * Opens the dropdown unless it is already open.
		 * 
		 * @param	{string}	containerId	dropdown wrapper id
		 */
		open: function(containerId) {
			var menu = _menus.get(containerId);
			if (menu !== undefined && !menu.classList.contains('dropdownOpen')) {
				this.toggleDropdown(containerId);
			}
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
			
			try {
				this.close(containerId);
				
				elRemove(_menus.get(containerId));
			}
			catch (e) {
				// the elements might not exist anymore thus ignore all errors while cleaning up
			}
			
			_menus.delete(containerId);
			_dropdowns.delete(containerId);
			
			return true;
		},
		
		/**
		 * Handles dropdown positions in overlays when scrolling in the overlay.
		 * 
		 * @param	{Event}		event	event object
		 */
		_onDialogScroll: function(event) {
			var dialogContent = event.currentTarget;
			//noinspection JSCheckFunctionSignatures
			var dropdowns = elBySelAll('.dropdown.dropdownOpen', dialogContent);
			
			for (var i = 0, length = dropdowns.length; i < length; i++) {
				var dropdown = dropdowns[i];
				var containerId = DomUtil.identify(dropdown);
				var offset = DomUtil.offset(dropdown);
				var dialogOffset = DomUtil.offset(dialogContent);
				
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
				if (dropdown.classList.contains('dropdownOpen')) {
					if (elDataBool(dropdown, 'is-overlay-dropdown-button')) {
						this.setAlignment(dropdown, _menus.get(containerId));
					}
					else {
						this.close(containerId);
					}
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
		 * @param	{?Event}	event		        event object, should be 'null' if targetId is given
		 * @param	{string?}	targetId	        dropdown wrapper id
		 * @param       {Element=}      alternateElement        alternative reference element for alignment
		 * @return	{boolean}	'false' if event is not null
		 */
		_toggle: function(event, targetId, alternateElement) {
			if (event !== null) {
				event.preventDefault();
				event.stopPropagation();
				
				//noinspection JSCheckFunctionSignatures
				targetId = elData(event.currentTarget, 'target');
			}
			
			var dropdown = _dropdowns.get(targetId), preventToggle = false;
			if (dropdown !== undefined) {
				// check if the dropdown is still the same, as some components (e.g. page actions)
				// re-create the parent of a button
				if (event) {
					var button = event.currentTarget, parent = button.parentNode;
					if (parent !== dropdown) {
						parent.classList.add('dropdown');
						parent.id = dropdown.id;
						
						// remove dropdown class and id from old parent
						dropdown.classList.remove('dropdown');
						dropdown.id = '';
						
						dropdown = parent;
						_dropdowns.set(targetId, parent);
					}
				}
				
				// Repeated clicks on the dropdown button will not cause it to close, the only way
				// to close it is by clicking somewhere else in the document or on another dropdown
				// toggle. This is used with the search bar to prevent the dropdown from closing by
				// setting the caret position in the search input field.
				if (elDataBool(dropdown, 'dropdown-prevent-toggle') && dropdown.classList.contains('dropdownOpen')) {
					preventToggle = true;
				}
				
				// check if 'isOverlayDropdownButton' is set which indicates that the dropdown toggle is within an overlay
				if (elData(dropdown, 'is-overlay-dropdown-button') === null) {
					var dialogContent = DomTraverse.parentByClass(dropdown, 'dialogContent');
					elData(dropdown, 'is-overlay-dropdown-button', (dialogContent !== null));
					
					if (dialogContent !== null) {
						dialogContent.addEventListener('scroll', this._onDialogScroll.bind(this));
					}
				}
			}
			
			// close all dropdowns
			_dropdowns.forEach((function(dropdown, containerId) {
				var menu = _menus.get(containerId);
				
				if (dropdown.classList.contains('dropdownOpen')) {
					if (preventToggle === false) {
						dropdown.classList.remove('dropdownOpen');
						menu.classList.remove('dropdownOpen');
						
						this._notifyCallbacks(containerId, 'close');
					}
				}
				else if (containerId === targetId && menu.childElementCount > 0) {
					dropdown.classList.add('dropdownOpen');
					menu.classList.add('dropdownOpen');
					
					if (menu.childElementCount && elDataBool(menu.children[0], 'scroll-to-active')) {
						var list = menu.children[0];
						list.removeAttribute('data-scroll-to-active');
						
						var active = null;
						for (var i = 0, length = list.childElementCount; i < length; i++) {
							if (list.children[i].classList.contains('active')) {
								active = list.children[i];
								break;
							}
						}
						
						if (active) {
							list.scrollTop = Math.max((active.offsetTop + active.clientHeight) - menu.clientHeight, 0);
						}
					}
					
					var itemList = elBySel('.scrollableDropdownMenu', menu);
					if (itemList !== null) {
						itemList.classList[(itemList.scrollHeight > itemList.clientHeight ? 'add' : 'remove')]('forceScrollbar');
					}
					
					this._notifyCallbacks(containerId, 'open');
					
					this.setAlignment(dropdown, menu, alternateElement);
				}
			}).bind(this));
			
			//noinspection JSDeprecatedSymbols
			window.WCF.Dropdown.Interactive.Handler.closeAll();
			
			return (event === null);
		}
	};
});
