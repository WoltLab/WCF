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
	function MediaSearch(initialFileType) {
		this._fileType = 'all';
		
		var dropdown = UiSimpleDropdown.getDropdownMenu(this._getIdPrefix() + 'Search');
		if (dropdown) {
			this._fileTypes = DomTraverse.childrenBySel(dropdown, 'li:not(.dropdownDivider)');
			
			var selectFileType = this._selectFileType.bind(this);
			for (var i = 0, length = this._fileTypes.length; i < length; i++) {
				var listItem = this._fileTypes[i];
				
				if (initialFileType && elData(listItem, 'file-type') == initialFileType) {
					this._fileType = initialFileType;
				}
				
				this._fileTypes[i].addEventListener(WCF_CLICK_EVENT, selectFileType);
			}
			
			if (initialFileType && initialFileType.length) {
				this._updateDropdownButtonLabel();
			}
			
			UiSimpleDropdown.registerCallback(this._getIdPrefix() + 'Search', this._updateFileTypeDropdown.bind(this));
			
			var form = DomTraverse.parentByTag(elById(this._getIdPrefix() + 'Search'), 'FORM');
			if (form) {
				form.addEventListener('submit', function() {
					var fileTypeInput = elCreate('input');
					elAttr(fileTypeInput, 'type', 'hidden');
					elAttr(fileTypeInput, 'name', 'fileType');
					elAttr(fileTypeInput, 'value', this._fileType);
					
					form.appendChild(fileTypeInput);
				}.bind(this));
			}
		}
		else {
			this._fileType = null;
		}
	};
	MediaSearch.prototype = {
		/**
		 * Returns the prefix to identify search-related elements.
		 * 
		 * @return	{string}
		 */
		_getIdPrefix: function() {
			return 'media';
		},
		
		/**
		 * Selects a certain file type after clicking on it in the dropdown menu.
		 *
		 * @param	{Event}		event
		 */
		_selectFileType: function(event) {
			this._fileType = elData(event.currentTarget, 'file-type');
			
			this._updateDropdownButtonLabel(event);
		},
		
		/**
		 * Updates the label of the dropdown button based on the currently selected file type.
		 */
		_updateDropdownButtonLabel: function(event) {
			var dropdown = UiSimpleDropdown.getDropdown(this._getIdPrefix() + 'Search');
			var buttonLabel = DomTraverse.childBySel(DomTraverse.childByClass(dropdown, 'dropdownToggle'), 'SPAN');
			
			if (this._fileType !== 'all') {
				var listItem;
				if (event) {
					listItem = event.currentTarget;
				}
				else {
					for (var i = 0, length = this._fileTypes.length; i < length; i++) {
						var _listItem = this._fileTypes[i];
						
						if (elData(_listItem, 'file-type') == this._fileType) {
							listItem = _listItem;
							break;
						}
					}
				}
				
				buttonLabel.textContent = DomTraverse.childBySel(listItem, 'SPAN').textContent;
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
		}
	};
	
	return MediaSearch;
});
