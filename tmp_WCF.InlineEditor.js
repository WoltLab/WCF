/**
 * Default implementation for inline editors.
 *
 * @param        string                elementSelector
 */
WCF.InlineEditor = Class.extend({
	/**
	 * list of registered callbacks
	 * @var        array<object>
	 */
	_callbacks: [],
	
	/**
	 * list of dropdown selections
	 * @var        object
	 */
	_dropdowns: {},
	
	/**
	 * list of container elements
	 * @var        object
	 */
	_elements: {},
	
	/**
	 * notification object
	 * @var        WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * list of known options
	 * @var        array<object>
	 */
	_options: [],
	
	/**
	 * action proxy
	 * @var        WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of trigger elements by element id
	 * @var        object<object>
	 */
	_triggerElements: {},
	
	/**
	 * list of data to update upon success
	 * @var        array<object>
	 */
	_updateData: [],
	
	/**
	 * Initializes a new inline editor.
	 */
	init: function (elementSelector) {
		var $elements = $(elementSelector);
		if (!$elements.length) {
			return;
		}
		
		this._setOptions();
		var $quickOption = '';
		for (var $i = 0, $length = this._options.length; $i < $length; $i++) {
			if (this._options[$i].isQuickOption) {
				$quickOption = this._options[$i].optionName;
				break;
			}
		}
		
		var self = this;
		$elements.each(function (index, element) {
			var $element = $(element);
			var $elementID = $element.wcfIdentify();
			
			// find trigger element
			var $trigger = self._getTriggerElement($element);
			if ($trigger === null || $trigger.length !== 1) {
				return;
			}
			
			$trigger.on(WCF_CLICK_EVENT, $.proxy(self._show, self)).data('elementID', $elementID);
			if ($quickOption) {
				// simulate click on target action
				$trigger.disableSelection().data('optionName', $quickOption).dblclick($.proxy(self._click, self));
			}
			
			// store reference
			self._elements[$elementID] = $element;
		});
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		WCF.CloseOverlayHandler.addCallback('WCF.InlineEditor', $.proxy(this._closeAll, this));
		
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'), 'success');
	},
	
	/**
	 * Closes all inline editors.
	 */
	_closeAll: function () {
		for (var $elementID in this._elements) {
			this._hide($elementID);
		}
	},
	
	/**
	 * Sets options for this inline editor.
	 */
	_setOptions: function () {
		this._options = [];
	},
	
	/**
	 * Register an option callback for validation and execution.
	 *
	 * @param        object                callback
	 */
	registerCallback: function (callback) {
		if ($.isFunction(callback)) {
			this._callbacks.push(callback);
		}
	},
	
	/**
	 * Returns the triggering element.
	 *
	 * @param        jQuery                element
	 * @return        jQuery
	 */
	_getTriggerElement: function (element) {
		return null;
	},
	
	/**
	 * Shows a dropdown menu if options are available.
	 *
	 * @param        object                event
	 */
	_show: function (event) {
		event.preventDefault();
		var $elementID = $(event.currentTarget).data('elementID');
		
		// build dropdown
		var $trigger = null;
		if (!this._dropdowns[$elementID]) {
			this._triggerElements[$elementID] = $trigger = this._getTriggerElement(this._elements[$elementID]).addClass('dropdownToggle');
			var parent = $trigger[0].parentNode;
			if (parent && parent.nodeName === 'LI' && parent.childElementCount === 1) {
				// do not add a wrapper element if the trigger is the only child
				parent.classList.add('dropdown');
			}
			else {
				$trigger.wrap('<span class="dropdown" />');
			}
			
			this._dropdowns[$elementID] = $('<ul class="dropdownMenu" />').insertAfter($trigger);
		}
		this._dropdowns[$elementID].empty();
		
		// validate options
		var $hasOptions = false;
		var $lastElementType = '';
		for (var $i = 0, $length = this._options.length; $i < $length; $i++) {
			var $option = this._options[$i];
			
			if ($option.optionName === 'divider') {
				if ($lastElementType !== '' && $lastElementType !== 'divider') {
					$('<li class="dropdownDivider" />').appendTo(this._dropdowns[$elementID]);
					$lastElementType = $option.optionName;
				}
			}
			else if (this._validate($elementID, $option.optionName) || this._validateCallbacks($elementID, $option.optionName)) {
				var $listItem = $('<li><span>' + $option.label + '</span></li>').appendTo(this._dropdowns[$elementID]);
				$listItem.data('elementID', $elementID).data('optionName', $option.optionName).data('isQuickOption', ($option.isQuickOption ? true : false)).click($.proxy(this._click, this));
				
				$hasOptions = true;
				$lastElementType = $option.optionName;
			}
		}
		
		if ($hasOptions) {
			// if last child is divider, remove it
			var $lastChild = this._dropdowns[$elementID].children().last();
			if ($lastChild.hasClass('dropdownDivider')) {
				$lastChild.remove();
			}
			
			// check if only element is a quick option
			var $quickOption = null;
			var $count = 0;
			this._dropdowns[$elementID].children().each(function (index, child) {
				var $child = $(child);
				if (!$child.hasClass('dropdownDivider')) {
					if ($child.data('isQuickOption')) {
						$quickOption = $child;
					}
					else {
						$count++;
					}
				}
			});
			
			if (!$count) {
				$quickOption.trigger('click');
				
				if (this._triggerElements[$elementID]) {
					WCF.Dropdown.close(this._triggerElements[$elementID].parents('.dropdown').wcfIdentify());
				}
				
				return false;
			}
		}
		
		if ($trigger !== null) {
			WCF.Dropdown.initDropdown($trigger, true);
		}
		
		return false;
	},
	
	/**
	 * Validates an option.
	 *
	 * @param        string                elementID
	 * @param        string                optionName
	 * @returns        boolean
	 */
	_validate: function (elementID, optionName) {
		return false;
	},
	
	/**
	 * Validates an option provided by callbacks.
	 *
	 * @param        string                elementID
	 * @param        string                optionName
	 * @return        boolean
	 */
	_validateCallbacks: function (elementID, optionName) {
		var $length = this._callbacks.length;
		if ($length) {
			for (var $i = 0; $i < $length; $i++) {
				if (this._callbacks[$i].validate(this._elements[elementID], optionName)) {
					return true;
				}
			}
		}
		
		return false;
	},
	
	/**
	 * Handles AJAX responses.
	 *
	 * @param        object                data
	 * @param        string                textStatus
	 * @param        jQuery                jqXHR
	 */
	_success: function (data, textStatus, jqXHR) {
		var $length = this._updateData.length;
		if (!$length) {
			return;
		}
		
		this._updateState(data);
		
		this._updateData = [];
	},
	
	/**
	 * Update element states based upon update data.
	 *
	 * @param        object                data
	 */
	_updateState: function (data) {
	},
	
	/**
	 * Handles clicks within dropdown.
	 *
	 * @param        object                event
	 */
	_click: function (event) {
		var $listItem = $(event.currentTarget);
		var $elementID = $listItem.data('elementID');
		var $optionName = $listItem.data('optionName');
		
		if (!this._execute($elementID, $optionName)) {
			this._executeCallback($elementID, $optionName);
		}
		
		this._hide($elementID);
	},
	
	/**
	 * Executes actions associated with an option.
	 *
	 * @param        string                elementID
	 * @param        string                optionName
	 * @return        boolean
	 */
	_execute: function (elementID, optionName) {
		return false;
	},
	
	/**
	 * Executes actions associated with an option provided by callbacks.
	 *
	 * @param        string                elementID
	 * @param        string                optionName
	 * @return        boolean
	 */
	_executeCallback: function (elementID, optionName) {
		var $length = this._callbacks.length;
		if ($length) {
			for (var $i = 0; $i < $length; $i++) {
				if (this._callbacks[$i].execute(this._elements[elementID], optionName)) {
					return true;
				}
			}
		}
		
		return false;
	},
	
	/**
	 * Hides a dropdown menu.
	 *
	 * @param        string                elementID
	 */
	_hide: function (elementID) {
		if (this._dropdowns[elementID]) {
			this._dropdowns[elementID].empty().removeClass('dropdownOpen');
		}
	}
});