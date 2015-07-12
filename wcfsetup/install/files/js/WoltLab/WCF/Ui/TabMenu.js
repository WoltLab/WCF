/**
 * Common interface for tab menu access.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/TabMenu
 */
define(['Dictionary', 'Dom/ChangeListener', 'Dom/Util', './TabMenu/Simple'], function(Dictionary, DomChangeListener, DomUtil, SimpleTabMenu) {
	"use strict";
	
	var _tabMenus = new Dictionary();
	
	/**
	 * @exports	WoltLab/WCF/Ui/TabMenu
	 */
	var UiTabMenu = {
		/**
		 * Sets up tab menus and binds listeners.
		 */
		setup: function() {
			this._init();
			this._selectErroneousTabs();
			
			DomChangeListener.add('WoltLab/WCF/Ui/TabMenu', this._init.bind(this));
		},
		
		/**
		 * Initializes available tab menus.
		 */
		_init: function() {
			var container, containerId, returnValue, tabMenu, tabMenus = document.querySelectorAll('.tabMenuContainer:not(.staticTabMenuContainer)');
			for (var i = 0, length = tabMenus.length; i < length; i++) {
				container = tabMenus[i];
				containerId = DomUtil.identify(container);
				
				if (_tabMenus.has(containerId)) {
					continue;
				}
				
				tabMenu = new SimpleTabMenu(container);
				if (tabMenu.validate()) {
					returnValue = tabMenu.init();
					
					_tabMenus.set(containerId, tabMenu);
					
					if (returnValue instanceof Element) {
						tabMenu = this.getTabMenu(returnValue.parentNode.id);
						tabMenu.select(returnValue.id, null, true);
					}
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
	
	return UiTabMenu;
});
