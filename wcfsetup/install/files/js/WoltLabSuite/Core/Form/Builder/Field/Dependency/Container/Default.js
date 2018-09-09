/**
 * Default implementation for a container visibility handler due to the dependencies of its
 * children that only considers the visibility of all of its children.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default
 * @see 	module:WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract
 * @since	3.2
 */
define(['./Abstract', 'Core', '../Manager'], function(Abstract, Core, DependencyManager) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Default(containerId) {
		this.init(containerId);
	};
	Core.inherit(Default, Abstract, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Default#checkContainer
		 */
		checkContainer: function() {
			if (elDataBool(this._container, 'ignore-dependencies')) {
				return;
			}
			
			// only consider containers that have not been hidden by their own dependencies
			if (DependencyManager.isHiddenByDependencies(this._container)) {
				return;
			}
			
			var containerIsVisible = !elIsHidden(this._container);
			var containerShouldBeVisible = false;
			
			var children = this._container.children;
			var start = 0;
			// ignore container header for visibility considerations
			if (this._container.children.item(0).tagName === 'H2' || this._container.children.item(0).tagName === 'HEADER') {
				var start = 1;
			}
			
			for (var i = start, length = children.length; i < length; i++) {
				if (!elIsHidden(children.item(i))) {
					containerShouldBeVisible = true;
					break;
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
	
	return Default;
});
