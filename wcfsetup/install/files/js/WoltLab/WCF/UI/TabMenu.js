/**
 * Common interface for tab menu access.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/TabMenu
 */
define(['Dictionary', 'DOM/ChangeListener', 'DOM/Util', './TabMenu/Simple'], function(Dictionary, DOMChangeListener, DOMUtil, SimpleTabMenu) {
	"use strict";
	
	var _tabMenus = new Dictionary();
	
	/**
	 * @exports	WoltLab/WCF/UI/TabMenu
	 */
	var UITabMenu = {
		/**
		 * Sets up tab menus and binds listeners.
		 */
		setup: function() {
			this._init();
			this._selectErroneousTabs();
			
			DOMChangeListener.add('WoltLab/WCF/UI/TabMenu', this._init.bind(this));
		},
		
		/**
		 * Initializes available tab menus.
		 */
		_init: function() {
			var tabMenus = document.querySelectorAll('.tabMenuContainer:not(.staticTabMenuContainer)');
			for (var i = 0, length = tabMenus.length; i < length; i++) {
				var container = tabMenus[i];
				var containerId = DOMUtil.identify(container);
				
				if (_tabMenus.has(containerId)) {
					continue;
				}
				
				var tabMenu = new SimpleTabMenu(containerId, container);
				if (tabMenu.validate()) {
					tabMenu.init();
					
					_tabMenus.set(containerId, tabMenu);
				}
			}
		},
		
		/**
		 * Selects the first tab containing an element with class `formError`.
		 */
		_selectErroneousTabs: function() {
			_tabMenus.forEach(function(tabMenu) {
				var foundError = false;
				tabMenu.getContainers().forEach(function(container) {
					if (!foundError && container.getElementsByClassName('formError').length) {
						foundError = true;
						
						tabMenu.select(container.id);
					}
				});
			});
		},
		
		/**
		 * Returns a SimpleTabMenu instance for given container id.
		 * 
		 * @param	{string}	containerId	tab menu container id
		 * @return	{(SimpleTabMenu|undefined)}	tab menu object
		 */
		getTabMenu: function(containerId) {
			return _tabMenus.get(containerId);
		}
	};
	
	return UITabMenu;
});
