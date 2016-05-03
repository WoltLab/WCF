/**
 * Provides the interface logic to add and edit menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Acp/Ui/Menu/Item/Handler
 */
define(['Dictionary', 'WoltLab/Wcf/Ui/Page/Search/Handler'], function(Dictionary, UiPageSearchHandler) {
	"use strict";
	
	var _activePageId = 0;
	var _cache;
	var _containerExternalLink;
	var _containerInternalLink;
	var _containerPageObjectId = null;
	var _handlers;
	var _pageId;
	var _pageObjectId;
	
	/**
	 * @exports     WoltLab/WCF/Acp/Ui/Menu/Item/Handler
	 */
	return {
		/**
		 * Initializes the interface logic.
		 * 
		 * @param       {Dictionary}    handlers        list of handlers by page id supporting page object ids
		 */
		init: function(handlers) {
			_handlers = handlers;
			
			_containerInternalLink = elById('pageIDContainer');
			_containerExternalLink = elById('externalURLContainer');
			_containerPageObjectId = elById('pageObjectIDContainer');
			
			if (_handlers.size) {
				_pageId = elById('pageID');
				_pageId.addEventListener('change', this._togglePageId.bind(this));
				
				_pageObjectId = elById('pageObjectID');
				
				_cache = new Dictionary();
				_activePageId = ~~_pageId.value;
				if (_activePageId && _handlers.has(_activePageId)) {
					_cache.set(_activePageId, ~~_pageObjectId.value);
				}
				
				elById('searchPageObjectID').addEventListener(WCF_CLICK_EVENT, this._openSearch.bind(this));
				
				// toggle page object id container on init
				if (_handlers.has(~~_pageId.value)) {
					elShow(_containerPageObjectId);
				}
			}
			
			elBySelAll('input[name="isInternalLink"]', null, (function(input) {
				input.addEventListener('change', this._toggleIsInternalLink.bind(this, input.value));
				
				if (input.checked) {
					this._toggleIsInternalLink(input.value);
				}
			}).bind(this));
		},
		
		/**
		 * Toggles between the interface for internal and external links.
		 * 
		 * @param       {string}        value   selected option value
		 * @protected
		 */
		_toggleIsInternalLink: function(value) {
			if (~~value) {
				elShow(_containerInternalLink);
				elHide(_containerExternalLink);
				this._togglePageId();
			}
			else {
				elHide(_containerInternalLink);
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
