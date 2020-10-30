/**
 * Provides the ACP menu navigation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Page/Menu
 */
define(['Dictionary', 'EventHandler', 'perfect-scrollbar', 'Ui/Screen'], function(Dictionary, EventHandler, perfectScrollbar, UiScreen) {
	"use strict";
	
	var _acpPageMenu = elById('acpPageMenu');
	var _acpPageSubMenu = elById('acpPageSubMenu');
	var _activeMenuItem = '';
	var _menuItems = new Dictionary();
	var _menuItemContainers = new Dictionary();
	var _pageContainer = elById('pageContainer');
	var _perfectScrollbarActive = false;
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/Page/Menu
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
				
				link.addEventListener('click', this._toggle.bind(this));
				
				_menuItems.set(menuItem, link);
			}).bind(this));
			
			elBySelAll('.acpPageSubMenuCategoryList', null, function(container) {
				_menuItemContainers.set(elData(container, 'menu-item'), container);
			});
			
			// menu is missing on the login page or during WCFSetup
			if (_acpPageMenu === null) {
				return;
			}
			
			var enablePerfectScrollbar = function () {
				var options = {
					wheelPropagation: false,
					swipePropagation: false,
					suppressScrollX: true
				};
				
				perfectScrollbar.initialize(_acpPageMenu, options);
				perfectScrollbar.initialize(_acpPageSubMenu, options);
				
				_perfectScrollbarActive = true;
			};
			
			UiScreen.on('screen-lg', {
				match: enablePerfectScrollbar,
				unmatch: function () {
					perfectScrollbar.destroy(_acpPageMenu);
					perfectScrollbar.destroy(_acpPageSubMenu);
					
					_perfectScrollbarActive = false;
				},
				setup: enablePerfectScrollbar
			});
			
			window.addEventListener('resize', function () {
				if (_perfectScrollbarActive) {
					perfectScrollbar.update(_acpPageMenu);
					perfectScrollbar.update(_acpPageSubMenu);
				}
			})
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
			var acpPageSubMenuActive = false;
			
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
				acpPageSubMenuActive = true;
			}
			
			_pageContainer.classList[(acpPageSubMenuActive ? 'add' : 'remove')]('acpPageSubMenuActive');
			if (_perfectScrollbarActive) {
				_acpPageSubMenu.scrollTop = 0;
				perfectScrollbar.update(_acpPageSubMenu);
			}
			
			EventHandler.fire('com.woltlab.wcf.AcpMenu', 'resize');
		}
	};
});
