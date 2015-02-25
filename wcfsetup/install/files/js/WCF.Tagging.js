"use strict";

/**
 * Tagging System for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Namespace for tagging related functions.
 */
WCF.Tagging = {};

/**
 * Editable tag list.
 * 
 * @see	WCF.EditableItemList
 */
WCF.Tagging.TagList = WCF.EditableItemList.extend({
	/**
	 * @see	WCF.EditableItemList._className
	 */
	_className: 'wcf\\data\\tag\\TagAction',
	
	/**
	 * maximum tag length
	 * @var	integer
	 */
	_maxLength: 0,
	
	/**
	 * @see	WCF.EditableItemList.init()
	 */
	init: function(itemListSelector, searchInputSelector, maxLength) {
		this._allowCustomInput = true;
		this._maxLength = maxLength;
		
		this._super(itemListSelector, searchInputSelector);
		
		this._data = [ ];
		this._search = new WCF.Tagging.TagSearch(this._searchInput, $.proxy(this.addItem, this));
		this._itemList.addClass('tagList');
		$(itemListSelector).data('__api', this);
	},
	
	/**
	 * @see	WCF.EditableItemList._keyDown()
	 */
	_keyDown: function(event) {
		if (this._super(event)) {
			// ignore submit event
			if (event === null) {
				return true;
			}
			
			var $keyCode = event.which;
			// allow [backspace], [escape], [enter] and [delete]
			if ($keyCode === 8 || $keyCode === 27 || $keyCode === 13 || $keyCode === 46) {
				return true;
			}
			else if ($keyCode > 36 && $keyCode < 41) {
				// allow arrow keys (37-40)
				return true;
			}
			
			if (this._searchInput.val().length >= this._maxLength) {
				return false;
			}
			
			return true;
		}
		
		return false;
	},
	
	/**
	 * @see	WCF.EditableItemList._submit()
	 */
	_submit: function() {
		this._super();
		
		for (var $i = 0, $length = this._data.length; $i < $length; $i++) {
			// deleting items leaves crappy indices
			if (this._data[$i]) {
				$('<input type="hidden" name="tags[]" />').val(this._data[$i]).appendTo(this._form);
			}
		};
	},
	
	/**
	 * @see	WCF.EditableItemList.addItem()
	 */
	addItem: function(data) {
		// enforce max length by trimming values
		if (!data.objectID && data.label.length > this._maxLength) {
			data.label = data.label.substr(0, this._maxLength);
		}
		
		if (WCF.inArray(data.label, this._data)) {
			return true;
		}
		
		var $listItem = $('<li class="badge tag">' + WCF.String.escapeHTML(data.label) + '</li>').data('objectID', data.objectID).data('label', data.label).appendTo(this._itemList);
		$listItem.click($.proxy(this._click, this));
		
		if (this._search) {
			this._search.addExcludedSearchValue(data.label);
		}
		
		this._addItem(data.objectID, data.label);
		
		return true;
	},
	
	/**
	 * @see	WCF.EditableItemList._addItem()
	 */
	_addItem: function(objectID, label) {
		this._data.push(label);
	},
	
	/**
	 * @see	WCF.EditableItemList.clearList()
	 */
	clearList: function() {
		this._super();
		
		this._data = [ ];
	},
	
	/**
	 * Returns the current tags.
	 * 
	 * @return	array<string>
	 */
	getTags: function() {
		return this._data;
	},
	
	/**
	 * @see	WCF.EditableItemList._removeItem()
	 */
	_removeItem: function(objectID, label) {
		for (var $i = 0, $length = this._data.length; $i < $length; $i++) {
			if (this._data[$i] === label) {
				// don't use "delete" here since it doesn't reindex
				// the array
				this._data.splice($i, 1);
				return;
			}
		}
	},
	
	/**
	 * @see	WCF.EditableItemList.load()
	 */
	load: function(data) {
		if (data && data.length) {
			for (var $i = 0, $length = data.length; $i < $length; $i++) {
				this.addItem({ objectID: 0, label: WCF.String.unescapeHTML(data[$i]) });
			}
		}
	}
});

/**
 * Search handler for tags.
 * 
 * @see	WCF.Search.Base
 */
WCF.Tagging.TagSearch = WCF.Search.Base.extend({
	/**
	 * @see	WCF.Search.Base._className
	 */
	_className: 'wcf\\data\\tag\\TagAction',
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues, commaSeperated) {
		this._super(searchInput, callback, excludedSearchValues, commaSeperated, false);
	}
});
