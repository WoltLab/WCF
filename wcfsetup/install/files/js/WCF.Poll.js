"use strict";

/**
 * Namespace for poll-related classes.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Poll = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles poll option management.
	 *
	 * @param        string                containerID
	 * @param        array<object>        optionList
	 * @deprecated	5.5 use `WoltLabSuite/Core/Ui/Poll/Editor` instead
	 */
	WCF.Poll.Management = Class.extend({
		/**
		 * container object
		 * @var        jQuery
		 */
		_container: null,

		/**
		 * number of options
		 * @var        int
		 */
		_count: 0,

		/**
		 * editor element id
		 * @var string
		 */
		_editorId: '',

		/**
		 * maximum allowed number of options
		 * @var        int
		 */
		_maxOptions: 0,

		/**
		 * Initializes the WCF.Poll.Management class.
		 *
		 * @param       {string}        containerID
		 * @param       {Object[]}      optionList
		 * @param       {int}           maxOptions
		 * @param       {string}        editorId
		 */
		init: function (containerID, optionList, maxOptions, editorId, fieldName) {
			this._count = 0;
			this._maxOptions = maxOptions || -1;
			this._container = $('#' + containerID).children('ol:eq(0)');
			this._fieldName = fieldName || 'pollOptions';

			if (!this._container.length) {
				console.debug("[WCF.Poll.Management] Invalid container id given, aborting.");
				return;
			}

			optionList = optionList || [];
			this._createOptionList(optionList);

			// bind event listener
			if (editorId) {
				this._editorId = editorId;

				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'reset_' + editorId, this._reset.bind(this));
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'submit_' + editorId, this._submit.bind(this));
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'validate_' + editorId, this._validate.bind(this));
				WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'handleError_' + editorId, this._handleError.bind(this));
			}
			else {
				this._container.closest('form').submit($.proxy(this._submit, this));
			}

			// init sorting
			require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
				new UiSortableList({
					containerId: containerID,
					options: {
						toleranceElement: '> div'
					}
				});
			});
		},

		/**
		 * Creates the option list on init.
		 *
		 * @param        array<object>                optionList
		 */
		_createOptionList: function (optionList) {
			for (var $i = 0, $length = optionList.length; $i < $length; $i++) {
				var $option = optionList[$i];
				this._createOption($option.optionValue, $option.optionID);
			}

			// add an empty option, unless it would exceed the limit
			if (optionList.length < this._maxOptions) {
				this._createOption();
			}
		},

		/**
		 * Creates a new option element.
		 *
		 * @param        string                optionValue
		 * @param        integer                optionID
		 * @param        jQuery                insertAfter
		 */
		_createOption: function (optionValue, optionID, insertAfter) {
			optionValue = optionValue || '';
			optionID = parseInt(optionID) || 0;
			insertAfter = insertAfter || null;

			var $listItem = $('<li class="sortableNode" />').data('optionID', optionID);
			if (insertAfter === null) {
				$listItem.appendTo(this._container);
			}
			else {
				$listItem.insertAfter(insertAfter);
			}

			// insert buttons
			var $container = $('<div class="pollOptionInput" />').appendTo($listItem);
			$('<span class="icon icon16 fa-arrows sortableNodeHandle" />').appendTo($container);
			$('<a role="button" href="#" class="icon icon16 fa-plus jsTooltip jsAddOption pointer" title="' + WCF.Language.get('wcf.poll.button.addOption') + '" />').click($.proxy(this._addOption, this)).appendTo($container);
			$('<a role="button" href="#" class="icon icon16 fa-times jsTooltip jsDeleteOption pointer" title="' + WCF.Language.get('wcf.poll.button.removeOption') + '" />').click($.proxy(this._removeOption, this)).appendTo($container);

			// insert input field
			var $input = $('<input type="text" value="' + optionValue + '" maxlength="255" />').keydown($.proxy(this._keyDown, this)).appendTo($container);
			$input.click(function () {
				// work-around for some weird focus issue on iOS/Android
				if (document.activeElement !== this) {
					this.focus();
				}
			});

			if (insertAfter !== null) {
				$input.focus();
			}

			WCF.DOMNodeInsertedHandler.execute();

			this._count++;
			if (this._count === this._maxOptions) {
				this._container.find('span.jsAddOption').removeClass('pointer').addClass('disabled');
			}
		},

		/**
		 * Handles key down events for option input field.
		 *
		 * @param        object                event
		 */
		_keyDown: function (event) {
			// ignore every key except for [Enter]
			if (event.which !== 13) {
				return;
			}

			$(event.currentTarget).parent().children('.jsAddOption').trigger('click');

			event.preventDefault();
		},

		/**
		 * Adds a new option after current one.
		 *
		 * @param        object                event
		 */
		_addOption: function (event) {
			event.preventDefault();

			if (this._count === this._maxOptions) {
				return false;
			}

			var $listItem = $(event.currentTarget).closest('li', this._container[0]);

			this._createOption(undefined, undefined, $listItem);
		},

		/**
		 * Removes an option.
		 *
		 * @param        object                event
		 */
		_removeOption: function (event) {
			event.preventDefault();

			$(event.currentTarget).closest('li', this._container[0]).remove();

			this._count--;
			this._container.find('span.jsAddOption').addClass('pointer').removeClass('disabled');

			if (this._container.children('li').length == 0) {
				this._createOption();
			}
		},

		/**
		 * Inserts hidden input elements storing the option values.
		 *
		 * @param       {(Event|Object)}        event
		 */
		_submit: function (event) {
			var $options = [];
			this._container.children('li').each(function (index, listItem) {
				var $listItem = $(listItem);
				var $optionValue = $.trim($listItem.find('input').val());

				// ignore empty values
				if ($optionValue != '') {
					$options.push($listItem.data('optionID') + '_' + $optionValue);
				}
			});

			if (typeof event.originalEvent === 'object' && event.originalEvent instanceof Event) {
				// create hidden input fields
				if ($options.length) {
					var $formSubmit = this._container.parents('form').find('.formSubmit');

					for (var $i = 0, $length = $options.length; $i < $length; $i++) {
						$('<input type="hidden" name="' + this._fieldName + '[' + $i + ']">').val($options[$i]).appendTo($formSubmit);
					}
				}
			}
			else {
				event.poll = {pollOptions: $options};

				// get form input fields
				var parentContainer = this._container.parents('.messageTabMenuContent:eq(0)');
				parentContainer.find('input').each(function (index, input) {
					if (input.name) {
						if (input.type !== 'checkbox' || input.checked) {
							event.poll[input.name] = input.value;
						}
					}
				});
			}
		},

		/**
		 * Resets the poll option form.
		 *
		 * @private
		 */
		_reset: function () {
			// reset options
			/** @type Element */
			var container = this._container[0];
			while (container.childElementCount > 1) {
				container.removeChild(container.children[1]);
			}

			elBySel('input', container.children[0]).value = '';

			// reset input fields and checkboxes
			var parentContainer = this._container.parents('.messageTabMenuContent:eq(0)');
			parentContainer.find('input').each(function (index, input) {
				if (input.name) {
					if (input.type === 'checkbox') {
						input.checked = false;
					}
					else if (input.type === 'text') {
						input.value = '';
					}
					else if (input.type === 'number') {
						input.value = input.min;
					}
				}
			});

			// reset date picker
			require(['WoltLabSuite/Core/Date/Picker'], (function (UiDatePicker) {
				UiDatePicker.clear('pollEndTime_' + this._editorId);
			}).bind(this));
		},

		_validate: function (data) {
			// get options
			var count = 0;
			elBySelAll('li input[type="text"]', this._container[0], function (input) {
				if (input.value.trim() !== '') {
					count++;
				}
			});

			var question = elById('pollQuestion_' + this._editorId);
			if (question.value.trim() === '') {
				if (count === 0) {
					// no question and no options provided, ignore
					return;
				}
				else {
					data.api.throwError(question, WCF.Language.get('wcf.global.form.error.empty'));
					data.valid = false;
				}
			}

			if (count === 0) {
				data.api.throwError(this._container[0], WCF.Language.get('wcf.global.form.error.empty'));
				data.valid = false;
			}
			else {
				var pollMaxVotes = elById('pollMaxVotes_' + this._editorId);
				var num = ~~pollMaxVotes.value;
				if (num && num > count) {
					data.api.throwError(pollMaxVotes, WCF.Language.get('wcf.poll.maxVotes.error.invalid'));
					data.valid = false;
				}
			}
		},

		_handleError: function (data) {
			switch (data.returnValues.fieldName) {
				case 'pollQuestion':
					var questionField = elById('pollQuestion_' + this._editorId);
					var errorMessage = WCF.Language.get('wcf.global.form.error.empty');
					if (data.returnValues.errorType !== 'empty') {
						errorMessage = WCF.Language.get('wcf.poll.pollQuestion.error.' + data.returnValues.errorType);
					}

					elInnerError(questionField, errorMessage);

					data.cancel = true;
					break;

				case 'pollEndTime':
				case 'pollMaxVotes':
					var fieldName = (data.returnValues.fieldName === 'pollEndTime') ? 'endTime' : 'maxVotes';

					var small = elCreate('small');
					small.className = 'innerError';
					small.innerHTML = WCF.Language.get('wcf.poll.' + fieldName + '.error.' + data.returnValues.errorType);

					var element = elById(data.returnValues.fieldName + '_' + this._editorId);
					var parent = element.parentElement;
					if (parent.classList.contains('inputAddon')) {
						element = parent;
						parent = parent.parentElement;
					}

					parent.insertBefore(small, element.nextSibling);

					data.cancel = true;
					break;
			}
		}
	});
}
else {
	WCF.Poll.Management = Class.extend({
		_container: {},
		_count: 0,
		_editorId: "",
		_maxOptions: 0,
		init: function() {},
		_createOptionList: function() {},
		_createOption: function() {},
		_keyDown: function() {},
		_addOption: function() {},
		_removeOption: function() {},
		_submit: function() {},
		_reset: function() {},
		_validate: function() {}
	});
}
