/**
 * Dynamically transforms menu-like structures to handle items exceeding the available width
 * by moving them into a separate dropdown.  
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/FlexibleMenu
 */
define(['Core', 'Dictionary', 'DOM/Traverse', 'DOM/Util', 'UI/SimpleDropdown'], function(Core, Dictionary, DOMTraverse, DOMUtil, SimpleDropdown) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UIFlexibleMenu() {
		this._containers = new Dictionary();
		this._dropdowns = new Dictionary();
		this._dropdownMenus = new Dictionary();
		this._itemLists = new Dictionary();
	};
	UIFlexibleMenu.prototype = {
		/**
		 * Register default menus and set up event listeners.
		 */
		setup: function() {
			if (document.getElementById('mainMenu') !== null) this.register('mainMenu');
			var navigationHeader = document.querySelector('.navigationHeader');
			if (navigationHeader !== null) this.register(DOMUtil.identify(navigationHeader));
			
			
			window.addEventListener('resize', this.rebuildAll.bind(this));
			WCF.DOMNodeInsertedHandler.addCallback('WoltLab/WCF/UI/FlexibleMenu', this.registerTabMenus.bind(this));
		},
		
		/**
		 * Registers a menu by element id.
		 * 
		 * @param	{string}	containerId	element id
		 */
		register: function(containerId) {
			var container = document.getElementById(containerId);
			if (container === null) {
				throw "Expected a valid element id, '" + containerId + "' does not exist.";
			}
			
			if (this._containers.has(containerId)) {
				return;
			}
			
			var lists = DOMTraverse.childrenByTag(container, 'UL');
			if (!lists.length) {
				throw "Expected an <ul> element as child of container '" + containerId + "'.";
			}
			
			this._containers.set(containerId, container);
			this._itemLists.set(containerId, lists[0]);
			
			this.rebuild(containerId);
		},
		
		/**
		 * Registers tab menus.
		 */
		registerTabMenus: function() {
			var tabMenus = document.querySelectorAll('.tabMenuContainer:not(.jsFlexibleMenuEnabled), .messageTabMenu:not(.jsFlexibleMenuEnabled)');
			for (var i = 0, length = tabMenus.length; i < length; i++) {
				var tabMenu = tabMenus[i];
				var navs = DOMTraverse.childrenByTag(tabMenu, 'NAV');
				if (navs.length !== 0) {
					tabMenu.classList.add('jsFlexibleMenuEnabled');
					this.register(DOMUtil.identify(navs[0]));
				}
			}
		},
		
		/**
		 * Rebuilds all menus, e.g. on window resize.
		 */
		rebuildAll: function() {
			this._containers.forEach((function(container, containerId) {
				this.rebuild(containerId);
			}).bind(this));
		},
		
		/**
		 * Rebuild the menu identified by given element id.
		 * 
		 * @param	{string}	containerId	element id
		 */
		rebuild: function(containerId) {
			var container = this._containers.get(containerId);
			if (container === undefined) {
				throw "Expected a valid element id, '" + containerId + "' is unknown.";
			}
			
			var styles = window.getComputedStyle(container);
			
			var availableWidth = container.parentNode.clientWidth;
			availableWidth -= DOMUtil.styleAsInt(styles, 'margin-left');
			availableWidth -= DOMUtil.styleAsInt(styles, 'margin-right');
			
			var list = this._itemLists.get(containerId);
			var items = DOMTraverse.childrenByTag(list, 'LI');
			var dropdown = this._dropdowns.get(containerId);
			var dropdownWidth = 0;
			if (dropdown !== undefined) {
				// show all items for calculation
				for (var i = 0, length = items.length; i < length; i++) {
					var item = items[i];
					if (item.classList.contains('dropdown')) {
						continue;
					}
					
					item.style.removeProperty('display'); 
				}
				
				if (dropdown.parentNode !== null) {
					dropdownWidth = DOMUtil.outerWidth(dropdown);
				}
			}
			
			var currentWidth = list.scrollWidth - dropdownWidth;
			var hiddenItems = [];
			if (currentWidth > availableWidth) {
				// hide items starting with the last one
				for (var i = items.length - 1; i >= 0; i--) {
					var item = items[i];
					
					// ignore dropdown and active item
					if (item.classList.contains('dropdown') || item.classList.contains('active') || item.classList.contains('ui-state-active')) {
						continue;
					}
					
					hiddenItems.push(item);
					item.style.setProperty('display', 'none');
					
					if (list.scrollWidth < availableWidth) {
						break;
					}
				}
			}
			
			if (hiddenItems.length) {
				var dropdownMenu;
				if (dropdown === undefined) {
					dropdown = document.createElement('li');
					dropdown.className = 'dropdown jsFlexibleMenuDropdown';
					var icon = document.createElement('a');
					icon.className = 'icon icon16 fa-list';
					dropdown.appendChild(icon);
					
					dropdownMenu = document.createElement('ul');
					dropdownMenu.classList.add('dropdownMenu');
					dropdown.appendChild(dropdownMenu);
					
					this._dropdowns.set(containerId, dropdown);
					this._dropdownMenus.set(containerId, dropdownMenu);
					
					SimpleDropdown.init(icon);
				}
				else {
					dropdownMenu = this._dropdownMenus.get(containerId);
				}
				
				if (dropdown.parentNode === null) {
					list.appendChild(dropdown);
				}
				
				// build dropdown menu
				var fragment = document.createDocumentFragment();
				
				var self = this;
				hiddenItems.forEach(function(hiddenItem) {
					var item = document.createElement('li');
					item.innerHTML = hiddenItem.innerHTML;
					
					item.addEventListener('click', (function(event) {
						event.preventDefault();
						
						Core.triggerEvent(hiddenItem.querySelector('a'), 'click');
						
						// force a rebuild to guarantee the active item being visible
						setTimeout(function() {
							self.rebuild(containerId);
						}, 59);
					}).bind(this));
					
					fragment.appendChild(item);
				});
				
				dropdownMenu.innerHTML = '';
				dropdownMenu.appendChild(fragment);
			}
			else if (dropdown !== undefined && dropdown.parentNode !== null) {
				dropdown.parentNode.removeChild(dropdown);
			}
		}
	};
	
	return new UIFlexibleMenu();
});
