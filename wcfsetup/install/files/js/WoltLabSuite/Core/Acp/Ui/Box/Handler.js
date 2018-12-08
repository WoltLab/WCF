/**
 * Provides the interface logic to add and edit boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Box/Handler
 */
define(['Dictionary', 'Language', 'WoltLabSuite/Core/Ui/Page/Search/Handler'], function(Dictionary, Language, UiPageSearchHandler) {
	"use strict";
	
	var _activePageId = 0;
	var _boxController;
	var _cache;
	var _containerExternalLink;
	var _containerPageID;
	var _containerPageObjectId = null;
	var _handlers;
	var _pageId;
	var _pageObjectId;
	var _position;
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/Box/Handler
	 */
	return {
		/**
		 * Initializes the interface logic.
		 * 
		 * @param       {Dictionary}    handlers        list of handlers by page id supporting page object ids
		 * @param       {string}        boxType         box type
		 */
		init: function(handlers, boxType) {
			_handlers = handlers;
			
			_boxController = elById('boxControllerID');
			
			if (boxType !== 'system') {
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
				
				elBySelAll('input[name="linkType"]', null, (function (input) {
					input.addEventListener('change', this._toggleLinkType.bind(this, input.value));
					
					if (input.checked) {
						this._toggleLinkType(input.value);
					}
				}).bind(this));
			}
			
			if (_boxController !== null) {
				_position = elById('position');
				_boxController.addEventListener('change', this._setAvailableBoxPositions.bind(this));
				
				// update positions on init
				this._setAvailableBoxPositions();
			}
		},
		
		/**
		 * Toggles between the interface for internal and external links.
		 * 
		 * @param       {string}        value   selected option value
		 * @protected
		 */
		_toggleLinkType: function(value) {
			switch (value) {
				case 'none':
					elHide(_containerPageID);
					elHide(_containerPageObjectId);
					elHide(_containerExternalLink);
					break;
					
				case 'internal':
					elShow(_containerPageID);
					elHide(_containerExternalLink);
					if (_handlers.size) this._togglePageId();
					break;
					
				case 'external':
					elHide(_containerPageID);
					elHide(_containerPageObjectId);
					elShow(_containerExternalLink);
					break;
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
			
			var pageIdentifier = elData(_pageId.options[_pageId.selectedIndex], 'identifier');
			var languageItem = 'wcf.page.pageObjectID.' + pageIdentifier;
			if (Language.get(languageItem) === languageItem) {
				languageItem = 'wcf.page.pageObjectID';
			}
			
			elByTag('label', _containerPageObjectId)[0].textContent = Language.get(languageItem);
			
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
			
			var labelLanguageItem;
			var pageIdentifier = elData(_pageId.options[_pageId.selectedIndex], 'identifier');
			var languageItem = 'wcf.page.pageObjectID.search.' + pageIdentifier;
			if (Language.get(languageItem) !== languageItem) {
				labelLanguageItem = languageItem;
			}
			
			UiPageSearchHandler.open(_activePageId, _pageId.options[_pageId.selectedIndex].textContent.trim(), function(objectId) {
				_pageObjectId.value = objectId;
				_cache.set(_activePageId, objectId);
			}, labelLanguageItem);
		},
		
		/**
		 * Updates the available box positions per box controller.
		 * 
		 * @protected
		 */
		_setAvailableBoxPositions: function() {
			var supportedPositions = JSON.parse(elData(_boxController.options[_boxController.selectedIndex], 'supported-positions'));
			
			var option;
			for (var i = 0, length = _position.childElementCount; i < length; i++) {
				option = _position.children[i];
				
				option.disabled = (supportedPositions.indexOf(option.value) === -1);
			}
		}
	};
});
