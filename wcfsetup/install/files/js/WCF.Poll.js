"use strict";

/**
 * Namespace for poll-related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Poll = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles poll option management.
	 *
	 * @param        string                containerID
	 * @param        array<object>        optionList
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
		init: function (containerID, optionList, maxOptions, editorId) {
			this._count = 0;
			this._maxOptions = maxOptions || -1;
			this._container = $('#' + containerID).children('ol:eq(0)');
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
			
			// add empty option
			this._createOption();
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
			$('<span class="icon icon16 fa-plus jsTooltip jsAddOption pointer" title="' + WCF.Language.get('wcf.poll.button.addOption') + '" />').click($.proxy(this._addOption, this)).appendTo($container);
			$('<span class="icon icon16 fa-times jsTooltip jsDeleteOption pointer" title="' + WCF.Language.get('wcf.poll.button.removeOption') + '" />').click($.proxy(this._removeOption, this)).appendTo($container);
			
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
						$('<input type="hidden" name="pollOptions[' + $i + ']">').val($options[$i]).appendTo($formSubmit);
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
			var question = elById('pollQuestion_' + this._editorId);
			if (question.value.trim() === '') {
				// no question provided, ignore
				return;
			}
			
			// get options
			var count = 0;
			elBySelAll('li input[type="text"]', this._container[0], function (input) {
				if (input.value.trim() !== '') {
					count++;
				}
			});
			
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

/**
 * Manages poll voting and result display.
 * 
 * @param	string		containerSelector
 */
WCF.Poll.Manager = Class.extend({
	/**
	 * template cache
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * list of permissions to view participants
	 * @var	object
	 */
	_canViewParticipants: { },
	
	/**
	 * list of permissions to view result
	 * @var	object
	 */
	_canViewResult: { },
	
	/**
	 * list of permissions
	 * @var	object
	 */
	_canVote: { },
	
	/**
	 * list of input elements per poll
	 * @var	object
	 */
	_inputElements: { },
	
	/**
	 * list of participant lists
	 * @var	object
	 */
	_participants: { },
	
	/**
	 * list of poll objects
	 * @var	object
	 */
	_polls: { },
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the poll manager.
	 * 
	 * @param	string		containerSelector
	 */
	init: function(containerSelector) {
		var $polls = $(containerSelector);
		if (!$polls.length) {
			console.debug("[WCF.Poll.Manager] Given selector '" + containerSelector + "' does not match, aborting.");
			return;
		}
		
		this._cache = { };
		this._canViewParticipants = { };
		this._canViewResult = { };
		this._inputElements = { };
		this._participants = { };
		this._polls = { };
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			url: 'index.php?poll/&t=' + SECURITY_TOKEN
		});
		
		// init polls
		var self = this;
		$polls.each(function(index, poll) {
			var $poll = $(poll);
			var $pollID = $poll.data('pollID');
			
			if (self._polls[$pollID] === undefined) {
				self._cache[$pollID] = {
					result: '',
					vote: ''
				};
				self._polls[$pollID] = $poll;
				
				self._canViewParticipants[$pollID] = ($poll.data('canViewParticipants')) ? true : false;
				self._canViewResult[$pollID] = ($poll.data('canViewResult')) ? true : false;
				self._canVote[$pollID] = ($poll.data('canVote')) ? true : false;
				
				self._bindListeners($pollID);
				
				if ($poll.data('inVote')) {
					self._prepareVote($pollID);
				}
				
				self._toggleButtons($pollID);
			}
		});
	},
	
	/**
	 * Bind event listeners for current poll id.
	 * 
	 * @param	integer		pollID
	 */
	_bindListeners: function(pollID) {
		this._polls[pollID].find('.jsButtonPollShowParticipants').data('pollID', pollID).click($.proxy(this._showParticipants, this));
		this._polls[pollID].find('.jsButtonPollShowResult').data('pollID', pollID).click($.proxy(this._showResult, this));
		this._polls[pollID].find('.jsButtonPollShowVote').data('pollID', pollID).click($.proxy(this._showVote, this));
		this._polls[pollID].find('.jsButtonPollVote').data('pollID', pollID).click($.proxy(this._vote, this));
	},
	
	/**
	 * Displays poll result template.
	 * 
	 * @param	object		event
	 * @param	integer		pollID
	 */
	_showResult: function(event, pollID) {
		var $pollID = (event === null) ? pollID : $(event.currentTarget).data('pollID');
		
		// user cannot see the results yet
		if (!this._canViewResult[$pollID]) {
			return;
		}
		
		// ignore request, we're within results already
		if (!this._polls[$pollID].data('inVote')) {
			return;
		}
		
		if (!this._cache[$pollID].result) {
			this._proxy.setOption('data', {
				actionName: 'getResult',
				pollID: $pollID
			});
			this._proxy.sendRequest();
		}
		else {
			// show results from cache
			this._polls[$pollID].find('.pollInnerContainer').html(this._cache[$pollID].result);
			
			// set vote state
			this._polls[$pollID].data('inVote', false);
			
			// toggle buttons
			this._toggleButtons($pollID);
		}
	},
	
	/**
	 * Displays a list of participants.
	 * 
	 * @param	object		event
	 */
	_showParticipants: function(event) {
		var $pollID = $(event.currentTarget).data('pollID');
		if (!this._participants[$pollID]) {
			this._participants[$pollID] = new WCF.User.List('wcf\\data\\poll\\PollAction', this._polls[$pollID].data('question'), { pollID: $pollID });
		}
		
		this._participants[$pollID].open();
	},
	
	/**
	 * Displays the vote template.
	 * 
	 * @param	object		event
	 * @param	integer		pollID
	 */
	_showVote: function(event, pollID) {
		var $pollID = (event === null) ? pollID : $(event.currentTarget).data('pollID');
		
		// user cannot vote (e.g. already voted or guest)
		if (!this._canVote[$pollID]) {
			return;
		}
		
		// ignore request, we're within vote already
		if (this._polls[$pollID].data('inVote')) {
			return;
		}
		
		if (!this._cache[$pollID].vote) {
			this._proxy.setOption('data', {
				actionName: 'getVote',
				pollID: $pollID
			});
			this._proxy.sendRequest();
		}
		else {
			// show vote from cache
			this._polls[$pollID].find('.pollInnerContainer').html(this._cache[$pollID].vote);
			
			// set vote state
			this._polls[$pollID].data('inVote', true);
			
			// bind event listener and toggle buttons
			this._prepareVote($pollID);
			this._toggleButtons($pollID);
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (!data || !data.actionName) {
			return;
		}
		
		var $pollID = data.pollID;
		
		// updating result template
		if (data.resultTemplate) {
			this._cache[$pollID].result = data.resultTemplate;
		}
		
		// updating vote template
		if (data.voteTemplate) {
			this._cache[$pollID].vote = data.voteTemplate;
		}
		
		switch (data.actionName) {
			case 'getResult':
				this._showResult(null, $pollID);
			break;
			
			case 'getVote':
				this._showVote(null, $pollID);
			break;
			
			case 'vote':
				// display results
				this._canViewResult[$pollID] = true;
				this._canVote[$pollID] = (data.canVote) ? true : false;
				
				if (this._polls[$pollID].data('isPublic')) {
					this._canViewParticipants[$pollID] = true;
				}
				
				this._showResult(null, $pollID);
			break;
		}
	},
	
	/**
	 * Binds event listener for vote template.
	 * 
	 * @param	integer		pollID
	 */
	_prepareVote: function(pollID) {
		this._polls[pollID].find('.jsButtonPollVote').disable();
		
		var $voteContainer = this._polls[pollID].find('.pollInnerContainer > .jsPollVote');
		var self = this;
		this._inputElements[pollID] = $voteContainer.find('input').change(function() { self._handleVoteButton(pollID); });
		this._handleVoteButton(pollID);
		
		var $maxVotes = $voteContainer.data('maxVotes');
		if (this._inputElements[pollID].filter('[type=checkbox]').length) {
			this._inputElements[pollID].change(function() { self._enforceMaxVotes(pollID, $maxVotes); });
			this._enforceMaxVotes(pollID, $maxVotes);
		}
	},
	
	/**
	 * Enforces max votes for input fields.
	 * 
	 * @param	integer		pollID
	 * @param	integer		maxVotes
	 */
	_enforceMaxVotes: function(pollID, maxVotes) {
		var $elements = this._inputElements[pollID];
		
		if ($elements.filter(':checked').length == maxVotes) {
			$elements.filter(':not(:checked)').disable();
		}
		else {
			$elements.enable();
		}
	},
	
	/**
	 * Enables or disable vote button.
	 * 
	 * @param	integer		pollID
	 */
	_handleVoteButton: function(pollID) {
		var $elements = this._inputElements[pollID];
		var $voteButton = this._polls[pollID].find('.jsButtonPollVote');
		
		if ($elements.filter(':checked').length) {
			$voteButton.enable();
		}
		else {
			$voteButton.disable();
		}
	},
	
	/**
	 * Toggles buttons for given poll id.
	 * 
	 * @param	integer		pollID
	 */
	_toggleButtons: function(pollID) {
		var $formSubmit = this._polls[pollID].children('.formSubmit');
		$formSubmit.find('.jsButtonPollShowParticipants, .jsButtonPollShowResult, .jsButtonPollShowVote, .jsButtonPollVote').hide();
		
		var $hideFormSubmit = true;
		if (this._polls[pollID].data('inVote')) {
			$hideFormSubmit = false;
			$formSubmit.find('.jsButtonPollVote').show();
			
			if (this._canViewResult[pollID]) {
				$formSubmit.find('.jsButtonPollShowResult').show();
			}
		}
		else {
			if (this._canVote[pollID]) {
				$hideFormSubmit = false;
				$formSubmit.find('.jsButtonPollShowVote').show();
			}
			
			if (this._canViewParticipants[pollID]) {
				$hideFormSubmit = false;
				$formSubmit.find('.jsButtonPollShowParticipants').show();
			}
		}
		
		if ($hideFormSubmit) {
			$formSubmit.hide();
		}
	},
	
	/**
	 * Executes a user's vote.
	 * 
	 * @param	object		event
	 */
	_vote: function(event) {
		var $pollID = $(event.currentTarget).data('pollID');
		
		// user cannot vote
		if (!this._canVote[$pollID]) {
			return;
		}
		
		// collect values
		var $optionIDs = [ ];
		this._inputElements[$pollID].each(function(index, input) {
			var $input = $(input);
			if ($input.is(':checked')) {
				$optionIDs.push($input.data('optionID'));
			}
		});
		
		if ($optionIDs.length) {
			this._proxy.setOption('data', {
				actionName: 'vote',
				optionIDs: $optionIDs,
				pollID: $pollID
			});
			this._proxy.sendRequest();
		}
	}
});
