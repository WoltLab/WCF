/**
 * Shows and hides an element that dependes on certain selected pages when setting up conditions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Condition/Page/Dependence
 */
define(['Dom/Traverse'], function(DomTraverse) {
	"use strict";
	
	var _pages = elBySelAll('input[name="pageIDs[]"]');
	
	/**
	 * @constructor
	 */
	function ControllerConditionPageDependence(dependentElement, pageIds) {
		this._dependentElement = dependentElement;
		this._pageIds = pageIds;
		
		for (var i = 0, length = _pages.length; i < length; i++) {
			_pages[i].addEventListener('change', this._checkVisibility.bind(this));
		}
		
		// remove the dependent element before submit if it is hidden
		DomTraverse.parentByTag(this._dependentElement, 'FORM').addEventListener('submit', function() {
			if (this._dependentElement.style.getPropertyValue('display') === 'none') {
				this._dependentElement.remove();
			}
		}.bind(this));
		
		this._checkVisibility();
	};
	ControllerConditionPageDependence.prototype = {
		/**
		 * Checks if any of the relevant pages is selected. If that is the case, the dependent
		 * element is shown, otherwise it is hidden.
		 * 
		 * @private
		 */
		_checkVisibility: function() {
			var page;
			for (var i = 0, length = _pages.length; i < length; i++) {
				page = _pages[i];
				
				if (page.checked && this._pageIds.indexOf(~~page.value) !== -1) {
					elShow(this._dependentElement);
					return;
				}
			}
			
			elHide(this._dependentElement);
		}
	};
	
	return ControllerConditionPageDependence;
});
