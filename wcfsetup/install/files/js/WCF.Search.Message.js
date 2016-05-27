"use strict";

/**
 * Namespace
 */
WCF.Search.Message = {};

/**
 * Provides quick search for search keywords.
 * 
 * @see	WCF.Search.Base
 */
WCF.Search.Message.KeywordList = WCF.Search.Base.extend({
	/**
	 * @see	WCF.Search.Base._className
	 */
	_className: 'wcf\\data\\search\\keyword\\SearchKeywordAction',
	
	/**
	 * dropdown divider
	 * @var	jQuery
	 */
	_divider: null,
	
	/**
	 * true, if submit should be forced
	 * @var	boolean
	 */
	_forceSubmit: false,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.Search.Message.KeywordList] The given callback is invalid, aborting.");
			return;
		}
		
		this._callback = callback;
		this._excludedSearchValues = [];
		if (excludedSearchValues) {
			this._excludedSearchValues = excludedSearchValues;
		}
		this._searchInput = $(searchInput).keyup($.proxy(this._keyUp, this)).keydown($.proxy(function(event) {
			// block form submit
			if (event.which === 13) {
				// ... unless there are no suggestions or suggestions are optional and none is selected
				if (this._itemCount && this._itemIndex !== -1) {
					event.preventDefault();
				}
			}
		}, this));
		
		var $dropdownMenu = WCF.Dropdown.getDropdownMenu(this._searchInput.parents('.dropdown').wcfIdentify());
		var $lastDivider = $dropdownMenu.find('li.dropdownDivider').last();
		this._divider = $('<li class="dropdownDivider" />').hide().insertBefore($lastDivider);
		this._list = $('<li class="dropdownList"><ul /></li>').hide().insertBefore($lastDivider).children('ul');
		
		// supress clicks on checkboxes
		$dropdownMenu.find('input, label').on('click', function(event) { event.stopPropagation(); });
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(item) {
		this._divider.show();
		this._list.parent().show();
		
		this._super(item);
	},
	
	/**
	 * @see	WCF.Search.Base._clearList()
	 */
	_clearList: function(clearSearchInput) {
		if (clearSearchInput) {
			this._searchInput.val('');
		}
		
		this._divider.hide();
		this._list.empty().parent().hide();
		
		WCF.CloseOverlayHandler.removeCallback('WCF.Search.Base');
		
		// reset item navigation
		this._itemCount = 0;
		this._itemIndex = -1;
	}
});
