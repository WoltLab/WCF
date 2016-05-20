/**
 * Dynamically transforms menu-like structures to handle items exceeding the available width
 * by moving them into a separate dropdown.  
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/FlexibleMenu
 */
define(['Core', 'Dictionary', 'Dom/ChangeListener', 'Dom/Traverse', 'Dom/Util', 'Ui/SimpleDropdown'], function(Core, Dictionary, DomChangeListener, DomTraverse, DomUtil, SimpleDropdown) {
	"use strict";
	
	var _containers = new Dictionary();
	var _dropdowns = new Dictionary();
	var _dropdownMenus = new Dictionary();
	var _itemLists = new Dictionary();
	
	/**
	 * @exports	WoltLab/WCF/Ui/FlexibleMenu
	 */
	var UiFlexibleMenu = {
		/**
		 * Register default menus and set up event listeners.
		 */
		setup: function() {
			if (elById('mainMenu') !== null) this.register('mainMenu');
			var navigationHeader = elBySel('.navigationHeader');
			if (navigationHeader !== null) this.register(DomUtil.identify(navigationHeader));
			
			window.addEventListener('resize', this.rebuildAll.bind(this));
			DomChangeListener.add('WoltLab/WCF/Ui/FlexibleMenu', this.registerTabMenus.bind(this));
		},
		
		/**
		 * Registers a menu by element id.
		 * 
		 * @param	{string}	containerId	element id
		 */
		register: function(containerId) {
			var container = elById(containerId);
			if (container === null) {
				throw "Expected a valid element id, '" + containerId + "' does not exist.";
			}
			
			if (_containers.has(containerId)) {
				return;
			}
			
			var list = DomTraverse.childByTag(container, 'UL');
			if (list === null) {
				throw "Expected an <ul> element as child of container '" + containerId + "'.";
			}
			
			_containers.set(containerId, container);
			_itemLists.set(containerId, list);
			
			this.rebuild(containerId);
		},
		
		/**
		 * Registers tab menus.
		 */
		registerTabMenus: function() {
			var tabMenus = elBySelAll('.tabMenuContainer:not(.jsFlexibleMenuEnabled), .messageTabMenu:not(.jsFlexibleMenuEnabled)');
			for (var i = 0, length = tabMenus.length; i < length; i++) {
				var tabMenu = tabMenus[i];
				var nav = DomTraverse.childByTag(tabMenu, 'NAV');
				if (nav !== null) {
					tabMenu.classList.add('jsFlexibleMenuEnabled');
					this.register(DomUtil.identify(nav));
				}
			}
		},
		
		/**
		 * Rebuilds all menus, e.g. on window resize.
		 */
		rebuildAll: function() {
			_containers.forEach((function(container, containerId) {
				this.rebuild(containerId);
			}).bind(this));
		},
		
		/**
		 * Rebuild the menu identified by given element id.
		 * 
		 * @param	{string}	containerId	element id
		 */
		rebuild: function(containerId) {
			var container = _containers.get(containerId);
			if (container === undefined) {
				throw "Expected a valid element id, '" + containerId + "' is unknown.";
			}
			
			var styles = window.getComputedStyle(container);
			
			var availableWidth = container.parentNode.clientWidth;
			availableWidth -= DomUtil.styleAsInt(styles, 'margin-left');
			availableWidth -= DomUtil.styleAsInt(styles, 'margin-right');
			
			var list = _itemLists.get(containerId);
			var items = DomTraverse.childrenByTag(list, 'LI');
			var dropdown = _dropdowns.get(containerId);
			var dropdownWidth = 0;
			if (dropdown !== undefined) {
				// show all items for calculation
				for (var i = 0, length = items.length; i < length; i++) {
					var item = items[i];
					if (item.classList.contains('dropdown')) {
						continue;
					}
					
					elShow(item);
				}
				
				if (dropdown.parentNode !== null) {
					dropdownWidth = DomUtil.outerWidth(dropdown);
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
					elHide(item);
					
					if (list.scrollWidth < availableWidth) {
						break;
					}
				}
			}
			
			if (hiddenItems.length) {
				var dropdownMenu;
				if (dropdown === undefined) {
					dropdown = elCreate('li');
					dropdown.className = 'dropdown jsFlexibleMenuDropdown';
					var icon = elCreate('a');
					icon.className = 'icon icon16 fa-list';
					dropdown.appendChild(icon);
					
					dropdownMenu = elCreate('ul');
					dropdownMenu.classList.add('dropdownMenu');
					dropdown.appendChild(dropdownMenu);
					
					_dropdowns.set(containerId, dropdown);
					_dropdownMenus.set(containerId, dropdownMenu);
					
					SimpleDropdown.init(icon);
				}
				else {
					dropdownMenu = _dropdownMenus.get(containerId);
				}
				
				if (dropdown.parentNode === null) {
					list.appendChild(dropdown);
				}
				
				// build dropdown menu
				var fragment = document.createDocumentFragment();
				
				var self = this;
				hiddenItems.forEach(function(hiddenItem) {
					var item = elCreate('li');
					item.innerHTML = hiddenItem.innerHTML;
					
					item.addEventListener(WCF_CLICK_EVENT, (function(event) {
						event.preventDefault();
						
						Core.triggerEvent(elBySel('a', hiddenItem), WCF_CLICK_EVENT);
						
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
				elRemove(dropdown);
			}
		}
	};
	
	return UiFlexibleMenu;
});
