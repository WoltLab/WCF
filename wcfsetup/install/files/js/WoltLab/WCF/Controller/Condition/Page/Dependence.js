/**
 * Shows and hides an element that depends on certain selected pages when setting up conditions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Condition/Page/Dependence
 */
define(['Dom/ChangeListener', 'Dom/Traverse', 'EventHandler', 'ObjectMap'], function(DomChangeListener, DomTraverse, EventHandler, ObjectMap) {
	"use strict";
	
	var _pages = elBySelAll('input[name="pageIDs[]"]');
	var _dependentElements = [];
	var _pageIds = new ObjectMap();
	var _hiddenElements = new ObjectMap();
	
	var _didInit = false;
	
	return {
		register: function(dependentElement, pageIds) {
			_dependentElements.push(dependentElement);
			_pageIds.set(dependentElement, pageIds);
			_hiddenElements.set(dependentElement, []);
			
			if (!_didInit) {
				for (var i = 0, length = _pages.length; i < length; i++) {
					_pages[i].addEventListener('change', this._checkVisibility.bind(this));
				}
				
				_didInit = true;
			}
			
			// remove the dependent element before submit if it is hidden
			DomTraverse.parentByTag(dependentElement, 'FORM').addEventListener('submit', function() {
				if (dependentElement.style.getPropertyValue('display') === 'none') {
					dependentElement.remove();
				}
			});
			
			this._checkVisibility();
		},
		
		/**
		 * Checks if any of the relevant pages is selected. If that is the case, the dependent
		 * element is shown, otherwise it is hidden.
		 * 
		 * @private
		 */
		_checkVisibility: function() {
			var dependentElement, page, pageIds;
			
			depenentElementLoop: for (var i = 0, length = _dependentElements.length; i < length; i++) {
				dependentElement = _dependentElements[i];
				pageIds = _pageIds.get(dependentElement);
				
				for (var j = 0, length2 = _pages.length; j < length2; j++) {
					page = _pages[j];
					
					if (page.checked && pageIds.indexOf(~~page.value) !== -1) {
						this._showDependentElement(dependentElement);
						
						continue depenentElementLoop;
					}
				}
				
				this._hideDependentElement(dependentElement);
			}
			
			EventHandler.fire('com.woltlab.wcf.pageConditionDependence', 'checkVisivility');
		},
		
		_hideDependentElement: function(dependentElement) {
			elHide(dependentElement);
			
			var hiddenElements = _hiddenElements.get(dependentElement);
			for (var i = 0, length = hiddenElements.length; i < length; i++) {
				elHide(hiddenElements[i]);
			}
			
			_hiddenElements.set(dependentElement, [])
		},
		
		_showDependentElement: function(dependentElement) {
			elShow(dependentElement);
			
			// make sure that all parent elements are also visible
			var parentNode = dependentElement;
			while ((parentNode = parentNode.parentNode) && parentNode instanceof Element) {
				if (parentNode.style.getPropertyValue('display') === 'none') {
					_hiddenElements.get(dependentElement).push(parentNode);
				}
				
				elShow(parentNode);
			}
		}
	};
	
	return ControllerConditionPageDependence;
});
