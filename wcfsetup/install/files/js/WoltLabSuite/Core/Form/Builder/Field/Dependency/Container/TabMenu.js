/**
 * Container visibility handler implementation for a tab menu that checks visibility
 * based on the visibility of its tab menu list items.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/TabMenu
 * @see 	module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	3.2
 */
define(['./Abstract', 'Core', 'Dom/Util', '../Manager', 'Ui/TabMenu'], function(Abstract, Core, DomUtil, DependencyManager, UiTabMenu) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function TabMenu(containerId) {
		this.init(containerId);
	};
	Core.inherit(TabMenu, Abstract, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default#checkContainer
		 */
		checkContainer: function() {
			// only consider containers that have not been hidden by their own dependencies
			if (DependencyManager.isHiddenByDependencies(this._container)) {
				return;
			}
			
			var containerIsVisible = !elIsHidden(this._container);
			var containerShouldBeVisible = false;
			
			var tabMenuListItems = elBySelAll('#' + DomUtil.identify(this._container) + ' > nav > ul > li', this._container.parentNode);
			for (var i = 0, length = tabMenuListItems.length; i < length; i++) {
				if (!elIsHidden(tabMenuListItems[i])) {
					containerShouldBeVisible = true;
					break;
				}
			}
			
			if (containerIsVisible !== containerShouldBeVisible) {
				if (containerShouldBeVisible) {
					elShow(this._container);
					
					UiTabMenu.getTabMenu(DomUtil.identify(this._container)).selectFirstVisible();
				}
				else {
					elHide(this._container);
				}
				
				// check containers again to make sure parent containers can react to
				// changing the visibility of this container
				DependencyManager.checkContainers();
			}
		}
	});
	
	return TabMenu;
});
