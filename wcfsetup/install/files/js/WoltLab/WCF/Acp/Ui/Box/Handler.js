/**
 * Provides the interface logic to add and edit boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Ui/Box/Handler
 */
define(['Dictionary', 'WoltLab/Wcf/Ui/Page/Search/Handler'], function(Dictionary, UiPageSearchHandler) {
	"use strict";
	
	var _activePageId = 0;
	var _cache;
	var _containerExternalLink;
	var _containerPageID;
	var _containerPageObjectId = null;
	var _handlers;
	var _pageId;
	var _pageObjectId;
	
	/**
	 * @exports     WoltLab/WCF/Acp/Ui/Box/Handler
	 */
	return {
		/**
		 * Initializes the interface logic.
		 * 
		 * @param       {Dictionary}    handlers        list of handlers by page id supporting page object ids
		 */
		init: function(handlers) {
			_handlers = handlers;
			
			_containerPageID = elById('linkPageIDContainer');
			_containerExternalLink = elById('externalURLContainer');
			_containerPageObjectId = elById('linkPageObjectIDContainer');
			
			if (_handlers.size) {
				_pageId = elById('linkPageID');
				_pageId.addEventListener('change', this._togglePageId.bind(this));
				
				_pageObjectId = elById('linkPageObjectID');
				
				_cache = new Dictionary();
				_activePageId = ~~_pageId.value;
				if (_activePageId && _handlers.has(_activePageId)) {
					_cache.set(_activePageId, ~~_pageObjectId.value);
				}
				
				elById('searchLinkPageObjectID').addEventListener(WCF_CLICK_EVENT, this._openSearch.bind(this));
				
				// toggle page object id container on init
				if (_handlers.has(~~_pageId.value)) {
					elShow(_containerPageObjectId);
				}
			}
			
			elBySelAll('input[name="linkType"]', null, (function(input) {
				input.addEventListener('change', this._toggleLinkType.bind(this, input.value));
				
				if (input.checked) {
					this._toggleLinkType(input.value);
				}
			}).bind(this));
		},
		
		/**
		 * Toggles between the interface for internal and external links.
		 * 
		 * @param       {string}        value   selected option value
		 * @protected
		 */
		_toggleLinkType: function(value) {
			if (value == 'none') {
				elHide(_containerPageID);
				elHide(_containerPageObjectId);
				elHide(_containerExternalLink);
			}
			if (value == 'internal') {
				elShow(_containerPageID);
				elHide(_containerExternalLink);
				if (_handlers.size) this._togglePageId();
			}
			if (value == 'external') {
				elHide(_containerPageID);
				elHide(_containerPageObjectId);
				elShow(_containerExternalLink);
			}
		},
		
		/**
		 * Handles the changed page selection.
		 * 
		 * @protected
		 */
		_togglePageId: function() {
			if (_handlers.has(_activePageId)) {
				_cache.set(_activePageId, ~~_pageObjectId.value);
			}
			
			_activePageId = ~~_pageId.value;
			
			// page w/o pageObjectID support, discard value
			if (!_handlers.has(_activePageId)) {
				_pageObjectId.value = '';
				
				elHide(_containerPageObjectId);
				
				return;
			}
				
			var newValue = ~~_cache.get(_activePageId);
			_pageObjectId.value = (newValue) ? newValue : '';
			
			elShow(_containerPageObjectId);
		},
		
		/**
		 * Opens the handler lookup dialog.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_openSearch: function(event) {
			event.preventDefault();
			
			UiPageSearchHandler.open(_activePageId, _pageId.options[_pageId.selectedIndex].textContent, function(objectId) {
				_pageObjectId.value = objectId;
				_cache.set(_activePageId, objectId);
			});
		}
	};
});
