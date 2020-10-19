/**
 * Simple interface to work with reusable dropdowns that are not bound to a specific item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	Ui/ReusableDropdown (alias)
 * @module	WoltLabSuite/Core/Ui/Dropdown/Reusable
 */
define(['Dictionary', 'Ui/SimpleDropdown'], function(Dictionary, UiSimpleDropdown) {
	"use strict";
	
	var _dropdowns = new Dictionary();
	var _ghostElementId = 0;
	
	/**
	 * Returns dropdown name by internal identifier.
	 *
	 * @param       {string}        identifier      internal identifier
	 * @returns     {string}        dropdown name
	 */
	function _getDropdownName(identifier) {
		if (!_dropdowns.has(identifier)) {
			throw new Error("Unknown dropdown identifier '" + identifier + "'");
		}
		
		return _dropdowns.get(identifier);
	}
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Dropdown/Reusable
	 */
	return {
		/**
		 * Initializes a new reusable dropdown.
		 * 
		 * @param       {string}        identifier      internal identifier
		 * @param       {Element}       menu            dropdown menu element
		 */
		init: function(identifier, menu) {
			if (_dropdowns.has(identifier)) {
				return;
			}
			
			var ghostElement = elCreate('div');
			ghostElement.id = 'reusableDropdownGhost' + _ghostElementId++;
			
			UiSimpleDropdown.initFragment(ghostElement, menu);
			
			_dropdowns.set(identifier, ghostElement.id);
		},
		
		/**
		 * Returns the dropdown menu element.
		 * 
		 * @param       {string}        identifier      internal identifier
		 * @returns     {Element}       dropdown menu element
		 */
		getDropdownMenu: function(identifier) {
			return UiSimpleDropdown.getDropdownMenu(_getDropdownName(identifier));
		},
		
		/**
		 * Registers a callback invoked upon open and close.
		 * 
		 * @param       {string}        identifier      internal identifier
		 * @param       {function}      callback        callback function
		 */
		registerCallback: function(identifier, callback) {
			UiSimpleDropdown.registerCallback(_getDropdownName(identifier), callback);
		},
		
		/**
		 * Toggles a dropdown.
		 * 
		 * @param       {string}        identifier              internal identifier
		 * @param       {Element}       referenceElement        reference element used for alignment
		 */
		toggleDropdown: function(identifier, referenceElement) {
			UiSimpleDropdown.toggleDropdown(_getDropdownName(identifier), referenceElement);
		}
	};
});
