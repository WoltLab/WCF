"use strict";

define(['Dictionary', 'DOM/Util', './TabMenu/Simple'], function(Dictionary, DOMUtil, SimpleTabMenu) {
	var _tabMenus = new Dictionary();
	
	/**
	 * @constructor
	 */
	var UiTabMenu = function() {};
	UiTabMenu.prototype = {
		init: function() {
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
		
		getTabMenu: function(containerId) {
			return _tabMenus.get(containerId);
		}
	};
	
	return new UiTabMenu();
});
