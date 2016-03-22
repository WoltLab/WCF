/**
 * Common interface for tab menu access.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/TabMenu
 */
define(['Dictionary', 'Dom/ChangeListener', 'Dom/Util', 'Ui/CloseOverlay', './TabMenu/Simple'], function(Dictionary, DomChangeListener, DomUtil, UiCloseOverlay, SimpleTabMenu) {
	"use strict";
	
	var _activeList = null;
	var _tabMenus = new Dictionary();
	
	/**
	 * @exports	WoltLab/WCF/Ui/TabMenu
	 */
	return {
		/**
		 * Sets up tab menus and binds listeners.
		 */
		setup: function() {
			this._init();
			this._selectErroneousTabs();
			
			DomChangeListener.add('WoltLab/WCF/Ui/TabMenu', this._init.bind(this));
			UiCloseOverlay.add('WoltLab/WCF/Ui/TabMenu', function() {
				if (_activeList) {
					_activeList.classList.remove('active');
					
					_activeList = null;
				}
			});
		},
		
		/**
		 * Initializes available tab menus.
		 */
		_init: function() {
			var container, containerId, list, returnValue, tabMenu, tabMenus = elBySelAll('.tabMenuContainer:not(.staticTabMenuContainer)');
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
					
					list = elBySel('#' + containerId + ' > nav > ul');
					(function(list) {
						list.addEventListener(WCF_CLICK_EVENT, function(event) {
							event.preventDefault();
							event.stopPropagation();
							
							if (event.target === list) {
								list.classList.add('active');
								
								_activeList = list;
							}
							else {
								list.classList.remove('active');
								
								_activeList = null;
							}
						});
					})(list);
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
					if (!foundError && elByClass('formError', container).length) {
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
});
