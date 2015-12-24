/**
 * Provides the media search for the media manager.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Media/Search
 */
define(['Ajax', 'Dom/Traverse', 'Dom/Util', 'Language', 'Ui/SimpleDropdown'], function(Ajax, DomTraverse, DomUtil, Language, UiSimpleDropdown) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaSearch(mediaManager) {
		this._mediaManager = mediaManager;
		this._searchMode = false;
		this._fileType = 'all';
		
		this._input = elById('mediaManagerSearchField');
		this._input.addEventListener('keypress', this._keyPress.bind(this));
		
		this._cancelButton = elById('mediaManagerSearchCancelButton');
		this._cancelButton.addEventListener('click', this._cancelSearch.bind(this));
		
		var dropdown = UiSimpleDropdown.getDropdownMenu('mediaManagerSearch');
		if (dropdown) {
			this._fileTypes = DomTraverse.childrenBySel(dropdown, 'li:not(.dropdownDivider)');
			var selectFileType = this._selectFileType.bind(this);
			for (var i = 0, length = this._fileTypes.length; i < length; i++) {
				this._fileTypes[i].addEventListener('click', selectFileType);
			}
			
			UiSimpleDropdown.registerCallback('mediaManagerSearch', this._updateFileTypeDropdown.bind(this));
		}
		else {
			this._fileType = null;
		}
	};
	MediaSearch.prototype = {
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
						innerInfo.textContent = Language.get('wcf.media.search.info.searchStringTreshold');
						
						DomUtil.insertAfter(innerInfo, this._input.parentNode);
					}
				}
			}
		},
		
		/**
		 * Sends an AJAX request to fetch seach results.
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
		 * Selects a certain file type after clicking on it in the dropdown menu.
		 *
		 * @param	{Event}		event
		 */
		_selectFileType: function(event) {
			this._fileType = elData(event.currentTarget, 'file-type');
			
			this._updateDropdownButtonLabel();
			
			this._search();
		},
		
		/**
		 * Updates the label of the dropdown button based on the currently selected file type.
		 */
		_updateDropdownButtonLabel: function() {
			var dropdown = UiSimpleDropdown.getDropdown('mediaManagerSearch');
			var buttonLabel = DomTraverse.childBySel(DomTraverse.childByClass(dropdown, 'dropdownToggle'), 'SPAN');
			
			if (this._fileType !== 'all') {
				buttonLabel.textContent = DomTraverse.childBySel(event.currentTarget, 'SPAN').textContent;
			}
			else {
				buttonLabel.textContent = Language.get('wcf.media.search.filetype');
			}
		},
		
		/**
		 * Updates the file type dropdown by correctly marking the currently selected file type.
		 */
		_updateFileTypeDropdown: function() {
			for (var i = 0, length = this._fileTypes.length; i < length; i++) {
				var listItem = this._fileTypes[i];
				
				listItem.classList[elData(listItem, 'file-type') === this._fileType ? 'add' : 'remove']('active');
			}
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
			this._fileType = 'all';
			
			this._updateDropdownButtonLabel();
		},
		
		/**
		 * Shows the media search.
		 */
		showSearch: function() {
			elShow(elById('mediaManagerSearch'));
		}
	};
	
	return MediaSearch;
});
