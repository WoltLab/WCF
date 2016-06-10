/**
 * Provides the media search for the media manager.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Media/Manager/Search
 */
define(['Ajax', 'Core', 'Dom/Traverse', 'Dom/Util', 'Language', 'WoltLab/WCF/Media/Search', 'Ui/SimpleDropdown'], function(Ajax, Core, DomTraverse, DomUtil, Language, MediaSearch, UiSimpleDropdown) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaManagerSearch(mediaManager) {
		MediaSearch.call(this);
		
		this._mediaManager = mediaManager;
		this._searchMode = false;
		
		this._input = elById(this._getIdPrefix() + 'SearchField');
		this._input.addEventListener('keypress', this._keyPress.bind(this));
		
		this._cancelButton = elById(this._getIdPrefix() + 'SearchCancelButton');
		this._cancelButton.addEventListener(WCF_CLICK_EVENT, this._cancelSearch.bind(this));
	};
	Core.inherit(MediaManagerSearch, MediaSearch, {
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
		 * @see	WoltLab/WCF/Media/Search#_getIdPrefix
		 */
		_getIdPrefix: function() {
			return 'mediaManager';
		},
		
		/**
		 * Handles the `[ENTER]` key to submit the form.
		 *
		 * @param	{Event}		event		event object
		 */
		_keyPress: function(event) {
			// 13 = [ENTER]
			if (event.charCode === 13) {
				event.preventDefault();
				
				var innerInfo = DomTraverse.childByClass(this._input.parentNode.parentNode, 'innerInfo');
				
				// TODO: treshold option?
				if (this._input.value.length >= 3) {
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
					fileType: this._fileType,
					fileTypeFilters: this._mediaManager.getOption('fileTypeFilters'),
					mode: this._mediaManager.getMode(),
					searchString: this._input.value
				}
			});
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Search#_selectFileType
		 */
		_selectFileType: function(event) {
			MediaManagerSearch._super.prototype._selectFileType.call(this, event);
			
			this._search();
		},
		
		/**
		 * Hides the media search.
		 */
		hideSearch: function() {
			elHide(elById(this._getIdPrefix() + 'Search'));
		},
		
		/**
		 * Resets the media search.
		 */
		resetSearch: function() {
			this._input.value = '';
			this._fileType = 'all';
			
			this._updateDropdownButtonLabel();
		},
		
		/**
		 * Shows the media search.
		 */
		showSearch: function() {
			elShow(elById(this._getIdPrefix() + 'Search'));
		}
	});
	
	return MediaManagerSearch;
});
