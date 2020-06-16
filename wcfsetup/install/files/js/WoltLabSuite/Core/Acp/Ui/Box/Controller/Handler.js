/**
 * Provides the interface logic to add and edit boxes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Box/Controller/Handler
 */
define(['Ajax', 'Dom/Util', 'EventHandler'], function(Ajax, DomUtil, EventHandler) {
	"use strict";
	
	var _boxControllerContainer = elById('boxControllerContainer');
	var _boxController = elById('boxControllerID');
	var _boxConditions = elById('boxConditions');
	
	/**
	 * @exports	WoltLabSuite/Core/Acp/Ui/Box/Controller/Handler
	 */
	return {
		init: function(initialObjectTypeId) {
			_boxController.addEventListener('change', this._updateConditions.bind(this));
			
			elShow(_boxControllerContainer);
			
			if (initialObjectTypeId === undefined) {
				this._updateConditions();
			}
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
			DomUtil.setInnerHtml(_boxConditions, data.returnValues.template);
		},
		
		/**
		 * Updates the displayed box conditions based on the selected dynamic box controller.
		 * 
		 * @protected
		 */
		_updateConditions: function() {
			EventHandler.fire('com.woltlab.wcf.boxControllerHandler', 'updateConditions');
			
			Ajax.api(this, {
				parameters: {
					objectTypeID: ~~_boxController.value
				}
			});
		}
	};
});
