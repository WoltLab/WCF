/**
 * Provides the interface logic to add and edit boxes.
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
	
	/**
	 * @exports	WoltLabSuite/Core/Acp/Ui/Box/Controller/Handler
	 */
	return {
		init: function() {
			_boxController.addEventListener('change', this._updateConditions.bind(this));
			
			elShow(_boxControllerContainer);
			
			_boxController.closest('form').addEventListener('submit', this._submit.bind(this));
			
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
			var boxConditions = elCreate('div');
			boxConditions.id = 'boxConditions' + data.returnValues.objectTypeID;
			boxConditions.className = 'boxConditionsContainer';
			DomUtil.setInnerHtml(boxConditions, data.returnValues.template);
			
			_boxConditions.appendChild(boxConditions);
		},
		
		/**
		 * Removes obsolete box conditions containers before submitting the form.
		 * 
		 * @param	{Event}		event
		 */
		_submit: function(event) {
			var boxConditionsContainers = elBySelAll('.boxConditionsContainer');
			var targetId = 'boxConditions' + ~~_boxController.value, boxConditionsContainer;
			
			for (var i = 0, length = boxConditionsContainers.length; i < length; i++) {
				boxConditionsContainer = boxConditionsContainers[i];
				if (boxConditionsContainer.id !== targetId) {
					elRemove(boxConditionsContainer);
				}
			}
		},
		
		/**
		 * Updates the displayed box conditions based on the selected dynamic box controller.
		 * 
		 * @protected
		 */
		_updateConditions: function() {
			var objectTypeId = ~~_boxController.value;
			
			elBySelAll('.boxConditionsContainer', undefined, elHide);
			
			var boxConditions = elById('boxConditions' + objectTypeId);
			if (boxConditions) {
				elShow(boxConditions);
			}
			else {
				Ajax.api(this, {
					parameters: {
						objectTypeID: objectTypeId
					}
				});
			}
		}
	};
});
