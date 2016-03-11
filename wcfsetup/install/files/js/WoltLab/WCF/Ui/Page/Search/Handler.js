/**
 * Provides access to the lookup function of page handlers, allowing the user to search and
 * select page object ids.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Search/Handler
 */
define(['StringUtil', 'Dom/Util', 'Ui/Dialog', './Input'], function(StringUtil, DomUtil, UiDialog, UiPageSearchInput) {
	"use strict";
	
	var _callback = null;
	var _searchInput = null;
	var _searchInputHandler = null;
	var _resultList = null;
	var _resultListContainer = null;
	
	/**
	 * @exports     WoltLab/WCF/Ui/Page/Search/Handler
	 */
	return {
		/**
		 * Opens the lookup overlay for provided page id.
		 * 
		 * @param       {int}           pageId          page id
		 * @param       {string}        title           dialog title
		 * @param       {function}      callback        callback function provided with the user-selected object id
		 */
		open: function (pageId, title, callback) {
			_callback = callback;
			
			UiDialog.open(this);
			UiDialog.setTitle(this, title);
			
			this._getSearchInputHandler().setPageId(pageId);
		},
		
		/**
		 * Builds the result list.
		 * 
		 * @param       {Object}        data            AJAX response data
		 * @protected
		 */
		_buildList: function(data) {
			this._resetList();
			
			// no matches
			if (!Array.isArray(data.returnValues) || data.returnValues.length === 0) {
				var innerError = elCreate('small');
				innerError.className = 'innerError';
				innerError.textContent = 'TODO: no matches';
				DomUtil.insertAfter(innerError, _searchInput);
				
				return;
			}
			
			var image, item, listItem;
			for (var i = 0, length = data.returnValues.length; i < length; i++) {
				item = data.returnValues[i];
				image = item.image;
				if (/^fa-/.test(image)) {
					image = '<span class="icon icon48 ' + image + '"></span>';
				}
				
				listItem = elCreate('li');
				elData(listItem, 'object-id', item.objectID);
				
				listItem.innerHTML = '<div class="box48">'
					+ image
					+ '<div>'
						+ '<div class="containerHeadline">'
							+ '<h3><a href="' + StringUtil.escapeHTML(item.link) + '">' + StringUtil.escapeHTML(item.title) + '</a></h3>'
							+ (item.description ? '<p>' + item.description + '</p>' : '')
						+ '</div>'
					+ '</div>'
				+ '</div>';
				
				listItem.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
				
				_resultList.appendChild(listItem);
			}
			
			elShow(_resultListContainer);
		},
		
		/**
		 * Resets the list and removes any error elements.
		 * 
		 * @protected
		 */
		_resetList: function() {
			var innerError = _searchInput.nextElementSibling;
			if (innerError && innerError.classList.contains('innerError')) elRemove(innerError);
			
			_resultList.innerHTML = '';
			
			elHide(_resultListContainer);
		},
		
		/**
		 * Initializes the search input handler and returns the instance.
		 * 
		 * @returns     {UiPageSearchInput}     search input handler
		 * @protected
		 */
		_getSearchInputHandler: function() {
			if (_searchInputHandler === null) {
				var callback = this._buildList.bind(this);
				_searchInputHandler = new UiPageSearchInput(elById('wcfUiPageSearchInput'), {
					callbackSuccess: callback
				});
			}
			
			return _searchInputHandler;
		},
		
		/**
		 * Handles clicks on the item unless the click occured directly on a link.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_click: function(event) {
			if (event.target.nodeName === 'A') {
				return;
			}
			
			event.stopPropagation();
			
			_callback(elData(event.currentTarget, 'object-id'));
			UiDialog.close(this);
		},
		
		_dialogSetup: function() {
			return {
				id: 'wcfUiPageSearchHandler',
				options: {
					onShow: function() {
						if (_searchInput === null) {
							_searchInput = elById('wcfUiPageSearchInput');
							_resultList = elById('wcfUiPageSearchResultList');
							_resultListContainer = elById('wcfUiPageSearchResultListContainer');
						}
						
						// clear search input
						_searchInput.value = '';
						
						// reset results
						elHide(_resultListContainer);
						_resultList.innerHTML = '';
						
						_searchInput.focus();
					},
					title: ''
				},
				source: '<div class="section">'
						+ '<dl>'
							+ '<dt><label for="wcfUiPageSearchInput">TODO: Search terms</label></dt>'
							+ '<dd>'
								+ '<input type="text" id="wcfUiPageSearchInput" class="long">'
								+ '<small>TODO: Enter at least 3 characters to search</small>'
							+ '</dd>'
						+ '</dl>'
					+ '</div>'
					+ '<section id="wcfUiPageSearchResultListContainer" class="section sectionContainerList">'
						+ '<header class="sectionHeader">'
							+ '<h2 class="sectionTitle">TODO: results</h2>'
							+ '<small class="sectionDescription">TODO: click on a result to select it</small>'
						+ '</header>'
						+ '<ul id="wcfUiPageSearchResultList" class="containerList wcfUiPageSearchResultList"></ul>'
					+ '</section>'
			};
		}
	};
});
