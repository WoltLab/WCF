"use strict";

/**
 * Namespace for labels.
 */
WCF.Label = {};

/**
 * Provides a flexible label chooser.
 * 
 * @param	object		selectedLabelIDs
 * @param	string		containerSelector
 * @param	string		submitButtonSelector
 * @param	boolean		showWithoutSelection
 * @deprecated 6.1 Use the new `LabelPicker` PHP API
 */
WCF.Label.Chooser = Class.extend({
	/**
	 * label container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * list of label groups
	 * @var	object
	 */
	_groups: { },
	
	/**
	 * show the 'without selection' option
	 * @var	boolean
	 */
	_showWithoutSelection: false,
	
	/**
	 * Initializes a new label chooser.
	 * 
	 * @param	object		selectedLabelIDs
	 * @param	string		containerSelector
	 * @param	string		submitButtonSelector
	 * @param	boolean		showWithoutSelection
	 */
	init: function(selectedLabelIDs, containerSelector, submitButtonSelector, showWithoutSelection) {
		this._container = null;
		this._groups = { };
		this._showWithoutSelection = (showWithoutSelection === true);
		
		// init containers
		this._initContainers(containerSelector);
		
		// pre-select labels
		if ($.getLength(selectedLabelIDs)) {
			for (var $groupID in selectedLabelIDs) {
				var $group = this._groups[$groupID];
				if ($group) {
					WCF.Dropdown.getDropdownMenu($group.wcfIdentify()).find('> ul > li:not(.dropdownDivider)').each($.proxy(function(index, label) {
						var $label = $(label);
						var $labelID = $label.data('labelID') || 0;
						if ($labelID && selectedLabelIDs[$groupID] == $labelID) {
							this._selectLabel($label, true);
						}
					}, this));
				}
			}
		}
		
		// mark all containers as initialized
		for (var $containerID in this._containers) {
			var $dropdown = this._containers[$containerID];
			if ($dropdown.data('labelID') === undefined) {
				$dropdown.data('labelID', 0);
			}
		}
		
		this._container = $(containerSelector);
		if (submitButtonSelector) {
			$(submitButtonSelector).click($.proxy(this._submit, this));
		}
		else if (this._container.is('form')) {
			this._container.submit($.proxy(this._submit, this));
		}
	},
	
	/**
	 * Initializes label groups.
	 * 
	 * @param	string		containerSelector
	 */
	_initContainers: function(containerSelector) {
		function blockPageScroll(element) {
			element.addEventListener('wheel', function(event) {
				event.preventDefault();
			}, { passive: false });
		}
		
		$(containerSelector).find('.labelChooser').each($.proxy(function(index, group) {
			var $group = $(group);
			var $groupID = $group.data('groupID');
			
			if (!this._groups[$groupID]) {
				var $containerID = $group.wcfIdentify();
				var $dropdownMenu = WCF.Dropdown.getDropdownMenu($containerID);
				if ($dropdownMenu === null) {
					WCF.Dropdown.initDropdown($group.find('.dropdownToggle'));
					$dropdownMenu = WCF.Dropdown.getDropdownMenu($containerID);
				}
				
				var $additionalList = $dropdownMenu;
				if ($dropdownMenu.getTagName() == 'div' && $dropdownMenu.children('.scrollableDropdownMenu').length) {
					$additionalList = $('<ul />').appendTo($dropdownMenu);
					$dropdownMenu = $dropdownMenu.children('.scrollableDropdownMenu');
				}
				
				this._groups[$groupID] = $group;
				
				$dropdownMenu.children('li').data('groupID', $groupID).click($.proxy(this._click, this));
				
				if (!$group.data('forceSelection') || this._showWithoutSelection) {
					$('<li class="dropdownDivider" />').appendTo($additionalList);
				}
				
				if (this._showWithoutSelection) {
					var buttonWithoutSelection = $('<li data-label-id="-1"><span><span class="badge label">' + WCF.Language.get('wcf.label.withoutSelection') + '</span></span></li>').data('groupID', $groupID).appendTo($additionalList).click($.proxy(this._click, this));
					blockPageScroll(buttonWithoutSelection[0]);
				}
				
				if (!$group.data('forceSelection')) {
					var $buttonEmpty = $('<li data-label-id="0"><span><span class="badge label">' + WCF.Language.get('wcf.label.none') + '</span></span></li>').data('groupID', $groupID).appendTo($additionalList);
					$buttonEmpty.click($.proxy(this._click, this));
					blockPageScroll($buttonEmpty[0]);
				}
			}
		}, this));
	},
	
	/**
	 * Handles label selections.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._selectLabel($(event.currentTarget), false);
	},
	
	/**
	 * Selects a label.
	 * 
	 * @param	jQuery		label
	 * @param	boolean		onInit
	 */
	_selectLabel: function(label, onInit) {
		var $group = this._groups[label.data('groupID')];
		
		// already initialized, ignore
		if (onInit && $group.data('labelID') !== undefined) {
			return;
		}
		
		// save label id
		if (label.data('labelID')) {
			$group.data('labelID', label.data('labelID'));
		}
		else {
			$group.data('labelID', 0);
		}
		
		// replace button
		label = label.find('span > span');
		$group.find('.dropdownToggle > span').removeClass().addClass(label.attr('class')).text(label.text());
		
		// Submit the form if the label chooser is the only form element.
		if (!onInit && this._container[0] && this._container[0].nodeName === 'FORM') {
			if (elBySel('input:not([type="hidden"]):not([type="submit"]):not([type="reset"]), select, textarea', this._container[0]) === null) {
				setTimeout((function () {
					this._container.trigger('submit');
					//this._container[0].submit();
				}).bind(this), 100);
			}
		}
	},
	
	/**
	 * Creates hidden input elements on submit.
	 */
	_submit: function() {
		// get form submit area
		var $formSubmit = this._container.find('.formSubmit');
		
		// remove old, hidden values
		$formSubmit.find('input[type="hidden"]').each(function(index, input) {
			var $input = $(input);
			if ($input.attr('name').indexOf('labelIDs[') === 0) {
				$input.remove();
			}
		});
		
		// insert label ids
		for (var $groupID in this._groups) {
			var $group = this._groups[$groupID];
			if ($group.data('labelID')) {
				$('<input type="hidden" name="labelIDs[' + $groupID + ']" value="' + $group.data('labelID') + '" />').appendTo($formSubmit);
			}
		}
	},
	
	/**
	 * Destroys the label chooser by destroying the used dropdowns.
	 */
	destroy: function() {
		for (var groupID in this._groups) {
			WCF.Dropdown.destroy(this._groups[groupID].wcfIdentify());
		}
	}
});

