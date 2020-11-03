/**
 * Handles the data to create and edit a poll in a form created via form builder.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Poll/Editor
 * @since	5.2
 */
define([
	'Core',
	'Dom/Util',
	'EventHandler',
	'EventKey',
	'Language',
	'WoltLabSuite/Core/Date/Picker',
	'WoltLabSuite/Core/Ui/Sortable/List'
], function(
	Core,
	DomUtil,
	EventHandler,
	EventKey,
	Language,
	DatePicker,
	UiSortableList
) {
	"use strict";
	
	function UiPollEditor(containerId, pollOptions, wysiwygId, options) {
		this.init(containerId, pollOptions, wysiwygId, options);
	}
	UiPollEditor.prototype = {
		/**
		 * Initializes the poll editor.
		 * 
		 * @param	{string}	containerId	id of the poll options container
		 * @param	{object[]}	pollOptions	existing poll options
		 * @param	{string}	wysiwygId	id of the related wysiwyg editor
		 * @param	{object}	options		additional poll options
		 */
		init: function(containerId, pollOptions, wysiwygId, options) {
			this._container = elById(containerId);
			if (this._container === null) {
				throw new Error("Unknown poll editor container with id '" + containerId + "'.");
			}
			
			this._wysiwygId = wysiwygId;
			if (wysiwygId !== '' && elById(wysiwygId) === null) {
				throw new Error("Unknown wysiwyg field with id '" + wysiwygId + "'.");
			}
			
			this.questionField = elById(this._wysiwygId + 'Poll_question');
			
			var optionLists = elByClass('sortableList', this._container);
			if (optionLists.length === 0) {
				throw new Error("Cannot find poll options list for container with id '" + containerId + "'.");
			}
			this.optionList = optionLists[0];
			
			this.endTimeField = elById(this._wysiwygId + 'Poll_endTime');
			this.maxVotesField = elById(this._wysiwygId + 'Poll_maxVotes');
			this.isChangeableYesField = elById(this._wysiwygId + 'Poll_isChangeable');
			this.isChangeableNoField = elById(this._wysiwygId + 'Poll_isChangeable_no');
			this.isPublicYesField = elById(this._wysiwygId + 'Poll_isPublic');
			this.isPublicNoField = elById(this._wysiwygId + 'Poll_isPublic_no');
			this.resultsRequireVoteYesField = elById(this._wysiwygId + 'Poll_resultsRequireVote');
			this.resultsRequireVoteNoField = elById(this._wysiwygId + 'Poll_resultsRequireVote_no');
			this.sortByVotesYesField = elById(this._wysiwygId + 'Poll_sortByVotes');
			this.sortByVotesNoField = elById(this._wysiwygId + 'Poll_sortByVotes_no');
			
			this._optionCount = 0;
			this._options = Core.extend({
				isAjax: false,
				maxOptions: 20
			}, options);
			
			this._createOptionList(pollOptions || []);
			
			new UiSortableList({
				containerId: containerId,
				options: {
					toleranceElement: '> div'
				}
			});
			
			if (this._options.isAjax) {
				var events = ['handleError', 'reset', 'submit', 'validate'];
				for (var i = 0, length = events.length; i < length; i++) {
					var event = events[i];
					
					EventHandler.add(
						'com.woltlab.wcf.redactor2',
						event + '_' + this._wysiwygId,
						this['_' + event].bind(this)
					);
				}
			}
			else {
				var form = this._container.closest('form');
				if (form === null) {
					throw new Error("Cannot find form for container with id '" + containerId + "'.");
				}
				
				form.addEventListener('submit', this._submit.bind(this));
			}
		},
		
		/**
		 * Adds an option based on below the option for which the `Add Option` button has
		 * been clicked.
		 * 
		 * @param	{Event}		event		icon click event
		 */
		_addOption: function(event) {
			event.preventDefault();
			
			if (this._optionCount === this._options.maxOptions) {
				return false;
			}
			
			this._createOption(
				undefined,
				undefined,
				event.currentTarget.closest('li')
			);
		},
		
		/**
		 * Creates a new option based on the given data or an empty option if no option data
		 * is given.
		 * 
		 * @param	{string}	optionValue	value of the option
		 * @param	{integer}	optionId	id of the option
		 * @param	{Element?}	insertAfter	optional element after which the new option is added
		 * @private
		 */
		_createOption: function(optionValue, optionId, insertAfter) {
			optionValue = optionValue || '';
			optionId = ~~optionId || 0;
			
			var listItem = elCreate('LI');
			listItem.className = 'sortableNode';
			elData(listItem, 'option-id', optionId);
			
			if (insertAfter) {
				DomUtil.insertAfter(listItem, insertAfter);
			}
			else {
				this.optionList.appendChild(listItem);
			}
			
			var pollOptionInput = elCreate('div');
			pollOptionInput.className = 'pollOptionInput';
			listItem.appendChild(pollOptionInput);
			
			var sortHandle = elCreate('span');
			sortHandle.className = 'icon icon16 fa-arrows sortableNodeHandle';
			pollOptionInput.appendChild(sortHandle);
			
			// buttons
			var addButton = elCreate('a');
			elAttr(addButton, 'role', 'button');
			elAttr(addButton, 'href', '#');
			addButton.className = 'icon icon16 fa-plus jsTooltip jsAddOption pointer';
			elAttr(addButton, 'title', Language.get('wcf.poll.button.addOption'));
			addButton.addEventListener('click', this._addOption.bind(this));
			pollOptionInput.appendChild(addButton);
			
			var deleteButton = elCreate('a');
			elAttr(deleteButton, 'role', 'button');
			elAttr(deleteButton, 'href', '#');
			deleteButton.className = 'icon icon16 fa-times jsTooltip jsDeleteOption pointer';
			elAttr(deleteButton, 'title', Language.get('wcf.poll.button.removeOption'));
			deleteButton.addEventListener('click', this._removeOption.bind(this));
			pollOptionInput.appendChild(deleteButton);
			
			// input field
			var optionInput = elCreate('input');
			elAttr(optionInput, 'type', 'text');
			optionInput.value = optionValue;
			elAttr(optionInput, 'maxlength', 255);
			optionInput.addEventListener('keydown', this._optionInputKeyDown.bind(this));
			optionInput.addEventListener('click', function() {
				// work-around for some weird focus issue on iOS/Android
				if (document.activeElement !== this) {
					this.focus();
				}
			});
			pollOptionInput.appendChild(optionInput);
			
			if (insertAfter !== null) {
				optionInput.focus();
			}
			
			this._optionCount++;
			if (this._optionCount === this._options.maxOptions) {
				elBySelAll('span.jsAddOption', this.optionList, function(icon) {
					icon.classList.remove('pointer');
					icon.classList.add('disabled');
				});
			}
		},
		
		/**
		 * Adds the given poll option to the option list.
		 * 
		 * @param	{object[]}	pollOptions	data of the added options
		 */
		_createOptionList: function(pollOptions) {
			for (var i = 0, length = pollOptions.length; i < length; i++) {
				var option = pollOptions[i];
				this._createOption(option.optionValue, option.optionID);
			}
			
			// add empty option field to add new options
			if (this._optionCount < this._options.maxOptions) {
				this._createOption();
			}
		},
		
		/**
		 * Handles errors when the data is saved via AJAX.
		 * 
		 * @param	{object}	data	request response data
		 */
		_handleError: function (data) {
			switch (data.returnValues.fieldName) {
				case this._wysiwygId + 'Poll_endTime':
				case this._wysiwygId + 'Poll_maxVotes':
					var fieldName = data.returnValues.fieldName.replace(this._wysiwygId + 'Poll_', '');
					
					var small = elCreate('small');
					small.className = 'innerError';
					small.innerHTML = Language.get('wcf.poll.' + fieldName + '.error.' + data.returnValues.errorType);
					
					var element = elById(data.returnValues.fieldName);
					var errorParent = element.closest('dd');
					
					DomUtil.prepend(small, element.nextSibling);
					
					data.cancel = true;
					break;
			}
		},
		
		/**
		 * Adds an empty poll option after the current option when clicking enter.
		 * 
		 * @param	{Event}		event	key event
		 */
		_optionInputKeyDown: function(event) {
			// ignore every key except for [Enter]
			if (!EventKey.Enter(event)) {
				return;
			}
			
			Core.triggerEvent(elByClass('jsAddOption', event.currentTarget.parentNode)[0], 'click');
			
			event.preventDefault();
		},
		
		/**
		 * Removes a poll option after clicking on the `Remove Option` button.
		 * 
		 * @param	{Event}		event	click event
		 */
		_removeOption: function (event) {
			event.preventDefault();
			
			elRemove(event.currentTarget.closest('li'));
			
			this._optionCount--;
			
			elBySelAll('span.jsAddOption', this.optionList, function(icon) {
				icon.classList.add('pointer');
				icon.classList.remove('disabled');
			});
			
			if (this.optionList.length === 0) {
				this._createOption();
			}
		},
		
		/**
		 * Resets all poll-related form fields.
		 */
		_reset: function() {
			this.questionField.value = '';
			
			this._optionCount = 0;
			this.optionList.innerHtml = '';
			this._createOption();
			
			DatePicker.clear(this.endTimeField);
			
			this.maxVotesField.value = 1;
			this.isChangeableYesField.checked = false;
			this.isChangeableNoField.checked = true;
			this.isPublicYesField.checked = false;
			this.isPublicNoField.checked = true;
			this.resultsRequireVoteYesField.checked = false;
			this.resultsRequireVoteNoField.checked = true;
			this.sortByVotesYesField.checked = false;
			this.sortByVotesNoField.checked = true;
			
			EventHandler.fire(
				'com.woltlab.wcf.poll.editor',
				'reset',
				{
					pollEditor: this
				}
			);
		},
		
		/**
		 * Is called if the form is submitted or before the AJAX request is sent.
		 * 
		 * @param	{Event?}	event	form submit event
		 */
		_submit: function(event) {
			if (this._options.isAjax) {
				event.poll = this.getData();
				
				EventHandler.fire(
					'com.woltlab.wcf.poll.editor',
					'submit',
					{
						event: event,
						pollEditor: this
					}
				);
			}
			else {
				var form = this._container.closest('form');
				
				var options = this.getOptions();
				for (var i = 0, length = options.length; i < length; i++) {
					var input = elCreate('input');
					elAttr(input, 'type', 'hidden');
					elAttr(input, 'name', this._wysiwygId + 'Poll_options[' + i + ']');
					input.value = options[i];
					form.appendChild(input);
				}
			}
		},
		
		/**
		 * Is called to validate the poll data.
		 * 
		 * @param	{object}	data	event data
		 */
		_validate: function(data) {
			if (this.questionField.value.trim() === '') {
				return;
			}
			
			var nonEmptyOptionCount = 0;
			for (var i = 0, length = this.optionList.children.length; i < length; i++) {
				var optionInput = elBySel('input[type=text]', this.optionList.children[i]);
				if (optionInput.value.trim() !== '') {
					nonEmptyOptionCount++;
				}
			}
			
			if (nonEmptyOptionCount === 0) {
				data.api.throwError(this._container, Language.get('wcf.global.form.error.empty'));
				data.valid = false;
			}
			else {
				var maxVotes = ~~this.maxVotesField.value;
				
				if (maxVotes && maxVotes > nonEmptyOptionCount) {
					data.api.throwError(this.maxVotesField.parentNode, Language.get('wcf.poll.maxVotes.error.invalid'));
					data.valid = false;
				}
				else {
					EventHandler.fire(
						'com.woltlab.wcf.poll.editor',
						'validate',
						{
							data: data,
							pollEditor: this
						}
					);
				}
			}
		},
		
		/**
		 * Returns all poll data.
		 * 
		 * @return      {object}
		 */
		getData: function() {
			var data = {};
			
			data[this.questionField.id] = this.questionField.value;
			data[this._wysiwygId + 'Poll_options'] = this.getOptions();
			data[this.endTimeField.id] = this.endTimeField.value;
			data[this.maxVotesField.id] = this.maxVotesField.value;
			data[this.isChangeableYesField.id] = !!this.isChangeableYesField.checked;
			data[this.isPublicYesField.id] = !!this.isPublicYesField.checked;
			data[this.resultsRequireVoteYesField.id] = !!this.resultsRequireVoteYesField.checked;
			data[this.sortByVotesYesField.id] = !!this.sortByVotesYesField.checked;
			
			return data;
		},
		
		/**
		 * Returns all entered poll options.
		 * 
		 * @return      {string[]}
		 */
		getOptions: function() {
			var options = [];
			for (var i = 0, length = this.optionList.children.length; i < length; i++) {
				var listItem = this.optionList.children[i];
				var optionValue = elBySel('input[type=text]', listItem).value.trim();
				
				if (optionValue !== '') {
					options.push(elData(listItem, 'option-id') + '_' + optionValue);
				}
			}
			
			return options;
		}
	};
	
	return UiPollEditor;
});
