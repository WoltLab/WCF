/**
 * Provides the interface logic to add and edit menu items.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Box/Controller/Handler
 */
define(['Ajax', 'Dictionary', 'Dom/Util'], function(Ajax, Dictionary, DomUtil) {
	"use strict";
	
	var _boxControllerContainer = elById('boxControllerContainer');
	var _boxController = elById('boxControllerID');
	var _boxConditions = elById('boxConditions');
	var _templates = new Dictionary();
	
	/**
	 * @exports	WoltLabSuite/Core/Acp/Ui/Box/Controller/Handler
	 */
	return {
		init: function(initialObjectTypeId) {
			_boxController.addEventListener('change', this._updateConditions.bind(this));
			
			if (initialObjectTypeId) {
				_templates.set(~~initialObjectTypeId, _boxConditions.innerHTML);
			}
			
			elShow(_boxControllerContainer);
			
			this._updateConditions();
		},
		
		/**
		 * Sets up ajax request object.
		 *
		 * @return	{object}	request options
		 */
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'getBoxConditionsTemplate',
					className: 'wcf\\data\\box\\BoxAction'
				}
			};
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			_templates.set(~~data.returnValues.objectTypeID, data.returnValues.template);
			
			DomUtil.setInnerHtml(_boxConditions, data.returnValues.template);
		},
		
		/**
		 * Updates the displayed box conditions based on the selected dynamic box controller.
		 * 
		 * @protected
		 */
		_updateConditions: function() {
			var objectTypeId = ~~_boxController.value;
			
			if (_templates.has(objectTypeId)) {
				if (_templates.get(objectTypeId) !== null) {
					_boxConditions.innerHTML = _templates.get(objectTypeId);
				}
			}
			else {
				_templates.set(objectTypeId, null);
				
				Ajax.api(this, {
					parameters: {
						objectTypeID: objectTypeId
					}
				});
			}
		}
	};
});
