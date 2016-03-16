/**
 * Provides the ACP menu navigation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Ui/Page/Menu
 */
define(['Dictionary'], function(Dictionary) {
	"use strict";
	
	var _activeMenuItem = '';
	var _menuItems = new Dictionary();
	var _menuItemContainers = new Dictionary();
	
	/**
	 * @exports     WoltLab/WCF/Acp/Ui/Page/Menu
	 */
	return {
		/**
		 * Initializes the ACP menu navigation.
		 */
		init: function() {
			elBySelAll('.acpPageMenuLink', null, (function(link) {
				var menuItem = elData(link, 'menu-item');
				if (link.classList.contains('active')) {
					_activeMenuItem = menuItem;
				}
				
				link.addEventListener(WCF_CLICK_EVENT, this._toggle.bind(this));
				
				_menuItems.set(menuItem, link);
			}).bind(this));
			
			elBySelAll('.acpPageSubMenuCategoryList', null, function(container) {
				_menuItemContainers.set(elData(container, 'menu-item'), container);
			});
		},
		
		/**
		 * Toggles a menu item.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_toggle: function(event) {
			event.preventDefault();
			event.stopPropagation();
			
			var link = event.currentTarget;
			var menuItem = elData(link, 'menu-item');
			
			// remove active marking from currently active menu
			if (_activeMenuItem) {
				_menuItems.get(_activeMenuItem).classList.remove('active');
				_menuItemContainers.get(_activeMenuItem).classList.remove('active');
			}
			
			if (_activeMenuItem === menuItem) {
				// current item was active before
				_activeMenuItem = '';
			}
			else {
				link.classList.add('active');
				_menuItemContainers.get(menuItem).classList.add('active');
				
				_activeMenuItem = menuItem;
			}
		}
	};
});
