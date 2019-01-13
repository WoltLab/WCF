/**
 * Abstract implementation of a handler for the visibility of container due the dependencies
 * of its children.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/Abstract
 * @since	5.2
 */
define(['EventHandler', '../Manager'], function(EventHandler, DependencyManager) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Abstract(containerId) {
		this.init(containerId);
	};
	Abstract.prototype = {
		/**
		 * Checks if the container should be visible and shows or hides it accordingly.
		 * 
		 * @abstract
		 */
		checkContainer: function() {
			throw new Error("Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Dependency/Container.checkContainer!");
		},
		
		/**
		 * Initializes a new container dependency handler for the container with the given
		 * id.
		 * 
		 * @param	{string}	containerId	id of the handled container
		 * 
		 * @throws	{TypeError}			if container id is no string
		 * @throws	{Error}				if container id is invalid
		 */
		init: function(containerId) {
			if (typeof containerId !== 'string') {
				throw new TypeError("Container id has to be a string.");
			}
			
			this._container = elById(containerId);
			if (this._container === null) {
				throw new Error("Unknown container with id '" + containerId + "'.");
			}
			
			DependencyManager.addContainerCheckCallback(this.checkContainer.bind(this));
		}
	};
	
	return Abstract
});
