/**
 * Provides the media search for the media manager.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/Manager/Search
 */
define(['Ajax', 'Core', 'Dom/Traverse', 'Dom/Util', 'EventKey', 'Language', 'Ui/SimpleDropdown'], function(Ajax, Core, DomTraverse, DomUtil, EventKey, Language, UiSimpleDropdown) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaManagerSearch(mediaManager) {
		this._mediaManager = mediaManager;
		this._searchMode = false;
		
		this._input = elById('mediaManagerSearchField');
		this._input.addEventListener('keypress', this._keyPress.bind(this));
		
		this._cancelButton = elById('mediaManagerSearchCancelButton');
		this._cancelButton.addEventListener(WCF_CLICK_EVENT, this._cancelSearch.bind(this));
	}
	MediaManagerSearch.prototype = {
		/**
		 * Returns the data for Ajax to setup the Ajax/Request object.
		 *
		 * @return	{object}	setup data for Ajax/Request object
		 */
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'getSearchResultList',
					className: 'wcf\\data\\media\\MediaAction',
					interfaceName: 'wcf\\data\\ISearchAction'
				}
			};
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			this._mediaManager.setMedia(data.returnValues.media || { }, data.returnValues.template || '');
		},
		
		/**
		 * Cancels the search after clicking on the cancel search button.
		 */
		_cancelSearch: function() {
			if (this._searchMode) {
				this._searchMode = false;
				
				this._mediaManager.resetMedia();
				this.resetSearch();
			}
		},
		
		/**
		 * Handles the `[ENTER]` key to submit the form.
		 *
		 * @param	{Event}		event		event object
		 */
		_keyPress: function(event) {
			if (EventKey.Enter(event)) {
				event.preventDefault();
				
				var innerInfo = DomTraverse.childByClass(this._input.parentNode.parentNode, 'innerInfo');
				
				if (this._input.value.length >= this._mediaManager.getOption('minSearchLength')) {
					if (innerInfo) {
						elHide(innerInfo);
					}
					
					this._search();
				}
				else {
					if (innerInfo) {
						elShow(innerInfo);
					}
					else {
						innerInfo = elCreate('p');
						innerInfo.className = 'innerInfo';
						innerInfo.textContent = Language.get('wcf.media.search.info.searchStringThreshold');
						
						DomUtil.insertAfter(innerInfo, this._input.parentNode);
					}
				}
			}
		},
		
		/**
		 * Sends an AJAX request to fetch search results.
		 */
		_search: function() {
			this._searchMode = true;
			
			Ajax.api(this, {
				parameters: {
					imagesOnly: this._mediaManager.getOption('imagesOnly'),
					mode: this._mediaManager.getMode(),
					searchString: this._input.value
				}
			});
		},
		
		/**
		 * Hides the media search.
		 */
		hideSearch: function() {
			elHide(elById('mediaManagerSearch'));
		},
		
		/**
		 * Resets the media search.
		 */
		resetSearch: function() {
			this._input.value = '';
		},
		
		/**
		 * Shows the media search.
		 */
		showSearch: function() {
			elShow(elById('mediaManagerSearch'));
		}
	};
	
	return MediaManagerSearch;
});
