/**
 * Container visibility handler implementation for a tab menu tab that, in addition to the
 * tab itself, also handles the visibility of the tab menu list item.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Tab
 * @see 	module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	3.2
 */
define(['./Abstract', 'Core', 'Dom/Util', '../Manager', 'Ui/TabMenu'], function(Abstract, Core, DomUtil, DependencyManager, UiTabMenu) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Tab(containerId) {
		this.init(containerId);
	};
	Core.inherit(Tab, Abstract, {
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
			
			var children = this._container.children;
			for (var i = 0, length = children.length; i < length; i++) {
				if (!elIsHidden(children.item(i))) {
					containerShouldBeVisible = true;
					break;
				}
			}
			
			if (containerIsVisible !== containerShouldBeVisible) {
				var tabMenuListItem = elBySel('#' + DomUtil.identify(this._container.parentNode) + ' > nav > ul > li[data-name=' + this._container.id + ']', this._container.parentNode.parentNode);
				if (tabMenuListItem === null) {
					throw new Error("Cannot find tab menu entry for tab '" + this._container.id + "'.");
				}
				
				if (containerShouldBeVisible) {
					elShow(this._container);
					elShow(tabMenuListItem);
				}
				else {
					elHide(this._container);
					elHide(tabMenuListItem);
					
					var tabMenu = UiTabMenu.getTabMenu(DomUtil.identify(tabMenuListItem.closest('.tabMenuContainer')));
					
					// check if currently active tab will be hidden
					if (tabMenu.getActiveTab() === tabMenuListItem) {
						tabMenu.selectFirstVisible();
					}
				}
				
				// check containers again to make sure parent containers can react to
				// changing the visibility of this container
				DependencyManager.checkContainers();
			}
		}
	});
	
	return Tab;
});
