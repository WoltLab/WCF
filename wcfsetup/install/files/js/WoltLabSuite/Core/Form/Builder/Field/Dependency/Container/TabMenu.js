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
define(['./Abstract', 'Core', 'Dom/Util', '../Manager'], function(Abstract, Core, DomUtil, DependencyManager) {
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
			var containerIsVisible = !elIsHidden(this._container);
			var containerShouldBeVisible = false;
			
			var tabMenuListItems = elBySelAll('#' + DomUtil.identify(this._container) + ' > nav > ul > li', this._container.parentNode);
			for (var i = 0, length = tabMenuListItems.length; i < length; i++) {
				if (!elIsHidden(tabMenuListItems[i])) {
					containerShouldBeVisible = true;
				}
			}
			
			if (containerIsVisible !== containerShouldBeVisible) {
				if (containerShouldBeVisible) {
					elShow(this._container);
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
	
	return Tab;
});
