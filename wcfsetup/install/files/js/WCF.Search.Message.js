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

/**
 * Handles the search area box.
 * 
 * @param	jQuery		searchArea
 */
WCF.Search.Message.SearchArea = Class.extend({
	/**
	 * search area object
	 * @var	jQuery
	 */
	_searchArea: null,
	
	/**
	 * Initializes the WCF.Search.Message.SearchArea class.
	 * 
	 * @param	jQuery		searchArea
	 */
	init: function(searchArea) {
		this._searchArea = searchArea;
		
		var $keywordList = new WCF.Search.Message.KeywordList(this._searchArea.find('input[type=search]'), $.proxy(this._callback, this));
		$keywordList.setDelay(500);
		
		// forward clicks on the search icon to input field
		var self = this;
		var $input = this._searchArea.find('input[type=search]');
		this._searchArea.click(function(event) {
			// only forward clicks if the search element itself is the target
			if (event.target == self._searchArea[0]) {
				$input.focus().trigger('click');
				return false;
			}
		});
		
		if (this._searchArea.hasClass('dropdown')) {
			var $containerID = this._searchArea.wcfIdentify();
			var $dropdownMenu = WCF.Dropdown.getDropdownMenu($containerID);
			var $form = this._searchArea.find('form');
			if ($form.length === 0) $form = this._searchArea.parent();
			
			$form.submit(function() {
				// copy checkboxes and hidden fields into form
				$dropdownMenu.find('input[type=hidden]').appendTo($form);
				$dropdownMenu.find('input[type=checkbox]:checked').each(function(index, input) {
					var $input = $(input);
					
					$('<input type="hidden" name="' + $input.attr('name') + '" value="' + $input.attr('value') + '" />').appendTo($form);
				});
			});
			
			var $enableFlexWidth = true;
			require(['Ui/Screen'], function(UiScreen) {
				UiScreen.on('screen-md-down', {
					match: function() { $enableFlexWidth = false; },
					unmatch: function() { $enableFlexWidth = true; },
					setup: function() { $enableFlexWidth = false; }
				})
			});
			
			$dropdownMenu.addClass('pageHeaderSearchDropdown');
			WCF.Dropdown.registerCallback($containerID, (function(containerID, action) {
				if ($enableFlexWidth && action === 'open' && elById('pageHeader').classList.contains('sticky')) {
					var width = elById('pageHeaderSearch').clientWidth + elById('topMenu').clientWidth;
					if (width < 200) width = 200;
					
					this._searchArea.css('width', width + 'px');
					$dropdownMenu.css('width', width + 'px');
				}
				else {
					this._searchArea.css('width', '');
					$dropdownMenu.css('width', '');
				}
			}).bind(this));
			
			$(window).scroll((function () {
				this._searchArea.css('width', '');
			}).bind(this));
		}
	},
	
	/**
	 * Callback for WCF.Search.Message.KeywordList.
	 * 
	 * @param	object		data
	 * @return	boolean
	 */
	_callback: function(data) {
		this._searchArea.find('input[type=search]').val(data.label);
		this._searchArea.find('form').submit();
		return false;
	}
});