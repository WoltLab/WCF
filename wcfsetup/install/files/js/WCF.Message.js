/**
 * Message related classes for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Message = { };

/**
 * Namespace for BBCode related classes.
 */
WCF.Message.BBCode = { };

/**
 * BBCode Viewer for WCF.
 */
WCF.Message.BBCode.CodeViewer = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Initializes the WCF.Message.BBCode.CodeViewer class.
	 */
	init: function() {
		this._dialog = null;
		
		this._initCodeBoxes();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.BBCode.CodeViewer', $.proxy(this._initCodeBoxes, this));
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Initializes available code boxes.
	 */
	_initCodeBoxes: function() {
		$('.codeBox:not(.jsCodeViewer)').each($.proxy(function(index, codeBox) {
			var $codeBox = $(codeBox).addClass('jsCodeViewer');
			
			$('<span class="icon icon16 icon-copy pointer jsTooltip" title="' + WCF.Language.get('wcf.message.bbcode.code.copy') + '" />').appendTo($codeBox.find('div > h3')).click($.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Shows a code viewer for a specific code box.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $content = '';
		$(event.currentTarget).parents('div').next('ol').children('li').each(function(index, listItem) {
			if ($content) {
				$content += "\n";
			}
			
			// do *not* use $.trim here, as we want to preserve whitespaces
			$content += $(listItem).text().replace(/\n+$/, '');
		});
		
		if (this._dialog === null) {
			this._dialog = $('<div><textarea cols="60" rows="12" readonly="readonly" /></div>').hide().appendTo(document.body);
			this._dialog.children('textarea').val($content);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.message.bbcode.code.copy')
			});
		}
		else {
			this._dialog.children('textarea').val($content);
			this._dialog.wcfDialog('open');
		}
		
		this._dialog.children('textarea').select();
	}
});

/**
 * Provides the dynamic parts of the edit history interface.
 */
WCF.Message.EditHistory = Class.extend({
	/**
	 * jQuery object containing the radio buttons for the oldID
	 * @var	object
	 */
	_oldIDInputs: null,
	
	/**
	 * jQuery object containing the radio buttons for the oldID
	 * @var	object
	 */
	_newIDInputs: null,
	
	/**
	 * selector for the version rows
	 * @var	string
	 */
	_containerSelector: '',
	
	/**
	 * selector for the revert button
	 * @var	string
	 */
	_buttonSelector: '.jsRevertButton',
	
	/**
	 * Initializes the edit history interface.
	 * 
	 * @param	object	oldIDInputs
	 * @param	object	newIDInputs
	 * @param	string	containerSelector
	 * @param	string	buttonSelector
	 */
	init: function(oldIDInputs, newIDInputs, containerSelector, buttonSelector) {
		this._oldIDInputs = oldIDInputs;
		this._newIDInputs = newIDInputs;
		this._containerSelector = containerSelector;
		this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsRevertButton';
		
		this.proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initInputs();
		this._initElements();
	},
	
	/**
	 * Initializes the radio buttons.
	 * Force the "oldID" to be lower than the "newID"
	 * 'current' is interpreted as Infinity.
	 */
	_initInputs: function() {
		var self = this;
		this._newIDInputs.change(function(event) {
			var newID = parseInt($(this).val())
			if ($(this).val() === 'current') newID = Infinity;
			
			self._oldIDInputs.each(function(event) {
				var oldID = parseInt($(this).val())
				if ($(this).val() === 'current') oldID = Infinity;
				
				if (oldID >= newID) {
					$(this).disable();
				}
				else {
					$(this).enable();
				}
			});
		});
		
		this._oldIDInputs.change(function(event) {
			var oldID = parseInt($(this).val());
			if ($(this).val() === 'current') oldID = Infinity;
			
			self._newIDInputs.each(function(event) {
				var newID = parseInt($(this).val())
				if ($(this).val() === 'current') newID = Infinity;
				
				if (newID <= oldID) {
					$(this).disable();
				}
				else {
					$(this).enable();
				}
			});
		});
		this._oldIDInputs.filter(':checked').change();
		this._newIDInputs.filter(':checked').change();
	},
	
	/**
	 * Initializes available element containers.
	 */
	_initElements: function() {
		var self = this;
		$(this._containerSelector).each(function(index, container) {
			var $container = $(container);
			$container.find(self._buttonSelector).click($.proxy(self._click, self));
		});
	},
	
	/**
	 * Sends AJAX request.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $target = $(event.currentTarget);
		event.preventDefault();
		
		if ($target.data('confirmMessage')) {
			var self = this;
			
			WCF.System.Confirmation.show($target.data('confirmMessage'), function(action) {
				if (action === 'cancel') return;
				
				self._sendRequest($target);
			});
		}
		else {
			this._sendRequest($target);
		}
	},
	
	
	/**
	 * Sends the request
	 * 
	 * @param	jQuery	object
	 */
	_sendRequest: function(object) {
		this.proxy.setOption('data', {
			actionName: 'revert',
			className: 'wcf\\data\\edit\\history\\entry\\EditHistoryEntryAction',
			objectIDs: [ $(object).data('objectID') ]
		});
		
		this.proxy.sendRequest();
	},
	
	/**
	 * Reloads the page to show the new versions.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		window.location.reload(true);
	}
});

/**
 * Prevents multiple submits of the same form by disabling the submit button.
 */
WCF.Message.FormGuard = Class.extend({
	/**
	 * Initializes the WCF.Message.FormGuard class.
	 */
	init: function() {
		var $forms = $('form.jsFormGuard').removeClass('jsFormGuard').submit(function() {
			$(this).find('.formSubmit input[type=submit]').disable();
		});
		
		// restore buttons, prevents disabled buttons on back navigation in Opera
		$(window).unload(function() {
			$forms.find('.formSubmit input[type=submit]').enable();
		});
	}
});

/**
 * Provides previews for Redactor message fields.
 * 
 * @param	string		className
 * @param	string		messageFieldID
 * @param	string		previewButtonID
 */
WCF.Message.Preview = Class.extend({
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * message field id
	 * @var	string
	 */
	_messageFieldID: '',
	
	/**
	 * message field
	 * @var	jQuery
	 */
	_messageField: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * preview button
	 * @var	jQuery
	 */
	_previewButton: null,
	
	/**
	 * previous button label
	 * @var	string
	 */
	_previewButtonLabel: '',
	
	/**
	 * Initializes a new WCF.Message.Preview object.
	 * 
	 * @param	string		className
	 * @param	string		messageFieldID
	 * @param	string		previewButtonID
	 */
	init: function(className, messageFieldID, previewButtonID) {
		this._className = className;
		
		// validate message field
		this._messageFieldID = $.wcfEscapeID(messageFieldID);
		this._messageField = $('#' + this._messageFieldID);
		if (!this._messageField.length) {
			console.debug("[WCF.Message.Preview] Unable to find message field identified by '" + this._messageFieldID + "'");
			return;
		}
		
		// validate preview button
		previewButtonID = $.wcfEscapeID(previewButtonID);
		this._previewButton = $('#' + previewButtonID);
		if (!this._previewButton.length) {
			console.debug("[WCF.Message.Preview] Unable to find preview button identified by '" + previewButtonID + "'");
			return;
		}
		
		this._previewButton.click($.proxy(this._click, this));
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Reads message field input and triggers an AJAX request.
	 */
	_click: function(event) {
		var $message = this._getMessage();
		if ($message === null) {
			console.debug("[WCF.Message.Preview] Unable to access Redactor instance of '" + this._messageFieldID + "'");
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: 'getMessagePreview',
			className: this._className,
			parameters: this._getParameters($message)
		});
		this._proxy.sendRequest();
		
		// update button label
		this._previewButtonLabel = this._previewButton.html();
		this._previewButton.html(WCF.Language.get('wcf.global.loading')).disable();
		
		// poke event
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Returns request parameters.
	 * 
	 * @param	string		message
	 * @return	object
	 */
	_getParameters: function(message) {
		// collect message form options
		var $options = { };
		$('#settings').find('input[type=checkbox]').each(function(index, checkbox) {
			var $checkbox = $(checkbox);
			if ($checkbox.is(':checked')) {
				$options[$checkbox.prop('name')] = $checkbox.prop('value');
			}
		});
		
		// build parameters
		return {
			data: {
				message: message
			},
			options: $options
		};
	},
	
	/**
	 * Returns parsed message from Redactor or null if editor was not accessible.
	 * 
	 * @return	string
	 */
	_getMessage: function() {
		if (!$.browser.redactor) {
			return this._messageField.val();
		}
		else if (this._messageField.data('redactor')) {
			return this._messageField.redactor('getText');
		}
		
		return null;
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// restore preview button
		this._previewButton.html(this._previewButtonLabel).enable();
		
		// remove error message
		this._messageField.parent().children('small.innerError').remove();
		
		// evaluate message
		this._handleResponse(data);
	},
	
	/**
	 * Evaluates response data.
	 * 
	 * @param	object		data
	 */
	_handleResponse: function(data) { },
	
	/**
	 * Handles errors during preview requests.
	 * 
	 * The return values indicates if the default error overlay is shown.
	 * 
	 * @param	object		data
	 * @return	boolean
	 */
	_failure: function(data) {
		if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
			return true;
		}
		
		// restore preview button
		this._previewButton.html(this._previewButtonLabel).enable();
		
		var $innerError = this._messageField.next('small.innerError').empty();
		if (!$innerError.length) {
			$innerError = $('<small class="innerError" />').appendTo(this._messageField.parent());
		}
		
		$innerError.html(data.returnValues.errorType);
		
		return false;
	}
});

/**
 * Default implementation for message previews.
 * 
 * @see	WCF.Message.Preview
 */
WCF.Message.DefaultPreview = WCF.Message.Preview.extend({
	_attachmentObjectType: null,
	_attachmentObjectID: null,
	_tmpHash: null,
	
	/**
	 * @see	WCF.Message.Preview.init()
	 */
	init: function(attachmentObjectType, attachmentObjectID, tmpHash) {
		this._super('wcf\\data\\bbcode\\MessagePreviewAction', 'text', 'previewButton');
		
		this._attachmentObjectType = attachmentObjectType || null;
		this._attachmentObjectID = attachmentObjectID || null;
		this._tmpHash = tmpHash || null;
	},
	
	/**
	 * @see	WCF.Message.Preview._handleResponse()
	 */
	_handleResponse: function(data) {
		var $preview = $('#previewContainer');
		if (!$preview.length) {
			$preview = $('<div class="container containerPadding marginTop" id="previewContainer"><fieldset><legend>' + WCF.Language.get('wcf.global.preview') + '</legend><div></div></fieldset>').prependTo($('#messageContainer')).wcfFadeIn();
		}
		
		$preview.find('div:eq(0)').html(data.returnValues.message);
		
		new WCF.Effect.Scroll().scrollTo($preview);
	},
	
	/**
	 * @see	WCF.Message.Preview._getParameters()
	 */
	_getParameters: function(message) {
		var $parameters = this._super(message);
		
		if (this._attachmentObjectType != null) {
			$parameters.attachmentObjectType = this._attachmentObjectType;
			$parameters.attachmentObjectID = this._attachmentObjectID;
			$parameters.tmpHash = this._tmpHash;
		}
		
		return $parameters;
	}
});

/**
 * Handles multilingualism for messages.
 * 
 * @param	integer		languageID
 * @param	object		availableLanguages
 * @param	boolean		forceSelection
 */
WCF.Message.Multilingualism = Class.extend({
	/**
	 * list of available languages
	 * @var	object
	 */
	_availableLanguages: { },
	
	/**
	 * language id
	 * @var	integer
	 */
	_languageID: 0,
	
	/**
	 * language input element
	 * @var	jQuery
	 */
	_languageInput: null,
	
	/**
	 * Initializes WCF.Message.Multilingualism
	 * 
	 * @param	integer		languageID
	 * @param	object		availableLanguages
	 * @param	boolean		forceSelection
	 */
	init: function(languageID, availableLanguages, forceSelection) {
		this._availableLanguages = availableLanguages;
		this._languageID = languageID || 0;
		
		this._languageInput = $('#languageID');
		
		// preselect current language id
		this._updateLabel();
		
		// register event listener
		this._languageInput.find('.dropdownMenu > li').click($.proxy(this._click, this));
		
		// add element to disable multilingualism
		if (!forceSelection) {
			var $dropdownMenu = this._languageInput.find('.dropdownMenu');
			$('<li class="dropdownDivider" />').appendTo($dropdownMenu);
			$('<li><span><span class="badge">' + this._availableLanguages[0] + '</span></span></li>').click($.proxy(this._disable, this)).appendTo($dropdownMenu);
		}
		
		// bind submit event
		this._languageInput.parents('form').submit($.proxy(this._submit, this));
	},
	
	/**
	 * Handles language selections.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._languageID = $(event.currentTarget).data('languageID');
		this._updateLabel();
	},
	
	/**
	 * Disables language selection.
	 */
	_disable: function() {
		this._languageID = 0;
		this._updateLabel();
	},
	
	/**
	 * Updates selected language.
	 */
	_updateLabel: function() {
		this._languageInput.find('.dropdownToggle > span').text(this._availableLanguages[this._languageID]);
	},
	
	/**
	 * Sets language id upon submit.
	 */
	_submit: function() {
		this._languageInput.next('input[name=languageID]').prop('value', this._languageID);
	}
});

/**
 * Loads smiley categories upon user request.
 */
WCF.Message.SmileyCategories = Class.extend({
	/**
	 * list of already loaded category ids
	 * @var	array<integer>
	 */
	_cache: [ ],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * wysiwyg editor selector
	 * @var	string
	 */
	_wysiwygSelector: '',
	
	/**
	 * Initializes the smiley loader.
	 * 
	 * @param	string		wysiwygSelector
	 */
	init: function(wysiwygSelector) {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		this._wysiwygSelector = wysiwygSelector;
		
		$('#smilies-' + this._wysiwygSelector).on('messagetabmenushow', $.proxy(this._click, this));
		
		// handle onload
		/*var self = this;
		new WCF.PeriodicalExecuter(function(pe) {
			pe.stop();
			
			self._click({ }, { newTab: $('#smilies > .menu li.ui-state-active') });
		}, 100);*/
	},
	
	/**
	 * Handles tab menu clicks.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_click: function(event, data) {
		var $categoryID = parseInt(data.activeTab.tab.data('smileyCategoryID'));
		
		// ignore global category, will always be pre-loaded
		if (!$categoryID) {
			return;
		}
		
		// smilies have already been loaded for this tab, ignore
		if (data.activeTab.container.children('ul.smileyList').length) {
			return;
		}
		
		// cache exists
		if (this._cache[$categoryID] !== undefined) {
			data.activeTab.container.html(this._cache[$categoryID]);
		}
		
		// load content
		this._proxy.setOption('data', {
			actionName: 'getSmilies',
			className: 'wcf\\data\\smiley\\category\\SmileyCategoryAction',
			objectIDs: [ $categoryID ]
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $categoryID = parseInt(data.returnValues.smileyCategoryID);
		this._cache[$categoryID] = data.returnValues.template;
		
		$('#smilies-' + this._wysiwygSelector + '-' + $categoryID).html(data.returnValues.template);
	}
});

/**
 * Handles smiley clicks.
 */
WCF.Message.Smilies = Class.extend({
	/**
	 * redactor element
	 * @var	$.Redactor
	 */
	_redactor: null,
	
	_wysiwygSelector: '',
	
	/**
	 * Initializes the smiley handler.
	 * 
	 * @param	string		wysiwygSelector
	 */
	init: function(wysiwygSelector) {
		this._wysiwygSelector = wysiwygSelector;
		
		WCF.System.Dependency.Manager.register('Redactor_' + this._wysiwygSelector, $.proxy(function() {
			this._redactor = $('#' + this._wysiwygSelector).redactor('getObject');
			
			// add smiley click handler
			$(document).on('click', '.jsSmiley', $.proxy(this._smileyClick, this));
		}, this));
	},
	
	/**
	 * Handles tab smiley clicks.
	 * 
	 * @param	object		event
	 */
	_smileyClick: function(event) {
		var $target = $(event.currentTarget);
		var $smileyCode = $target.data('smileyCode');
		var $smileyPath = $target.data('smileyPath');
		
		// register smiley
		this._redactor.insertSmiley($smileyCode, $smileyPath, true);
	}
});

/**
 * Provides an AJAX-based quick reply for messages.
 */
WCF.Message.QuickReply = Class.extend({
	/**
	 * quick reply container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * message field
	 * @var	jQuery
	 */
	_messageField: null,
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * true, if a request to save the message is pending
	 * @var	boolean
	 */
	_pendingSave: false,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * collection of quick reply buttons
	 * @var	jQuery
	 */
	_quickReplyButtons: null,
	
	/**
	 * quote manager object
	 * @var	WCF.Message.Quote.Manager
	 */
	_quoteManager: null,
	
	/**
	 * scroll handler
	 * @var	WCF.Effect.Scroll
	 */
	_scrollHandler: null,
	
	/**
	 * success message for created but invisible messages
	 * @var	string
	 */
	_successMessageNonVisible: '',
	
	/**
	 * Initializes a new WCF.Message.QuickReply object.
	 * 
	 * @param	boolean				supportExtendedForm
	 * @param	WCF.Message.Quote.Manager	quoteManager
	 */
	init: function(supportExtendedForm, quoteManager) {
		this._container = $('#messageQuickReply');
		this._container.children('.message').addClass('jsInvalidQuoteTarget');
		this._messageField = $('#text');
		this._pendingSave = false;
		if (!this._container || !this._messageField) {
			return;
		}
		
		// button actions
		var $formSubmit = this._container.find('.formSubmit');
		$formSubmit.find('button[data-type=save]').click($.proxy(this._save, this));
		if (supportExtendedForm) $formSubmit.find('button[data-type=extended]').click($.proxy(this._prepareExtended, this));
		$formSubmit.find('button[data-type=cancel]').click($.proxy(this._cancel, this));
		
		if (quoteManager) this._quoteManager = quoteManager;
		
		this._quickReplyButtons = $('.jsQuickReply').data('__api', this).click($.proxy(this.click, this));
		
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
		this._scroll = new WCF.Effect.Scroll();
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.add'));
		this._successMessageNonVisible = '';
	},
	
	/**
	 * Handles clicks on reply button.
	 * 
	 * @param	object		event
	 */
	click: function(event) {
		this._container.toggle();
		
		if (this._container.is(':visible')) {
			this._quickReplyButtons.hide();
			
			// TODO: Scrolling is anything but smooth, better use the init callback
			this._scroll.scrollTo(this._container, true);
			
			WCF.Message.Submit.registerButton('text', this._container.find('.formSubmit button[data-type=save]'));
			
			if (this._quoteManager) {
				// check if message field is empty
				var $empty = true;
				if ($.browser.redactor) {
					if (this._messageField.data('redactor')) {
						$empty = (!$.trim(this._messageField.redactor('getText')));
						this._editorCallback($empty);
					}
				}
				else {
					$empty = (!this._messageField.val().length);
					this._editorCallback($empty);
				}
			}
		}
		
		// discard event
		if (event !== null) {
			event.stopPropagation();
			return false;
		}
	},
	
	/**
	 * Inserts quotes and focuses the editor.
	 */
	_editorCallback: function(isEmpty) {
		if (isEmpty) {
			this._quoteManager.insertQuotes(this._getClassName(), this._getObjectID(), $.proxy(this._insertQuotes, this));
		}
		
		if ($.browser.redactor) {
			this._messageField.redactor('focus');
		}
		else {
			this._messageField.focus();
		}
	},
	
	/**
	 * Returns container element.
	 * 
	 * @return	jQuery
	 */
	getContainer: function() {
		return this._container;
	},
	
	/**
	 * Insertes quotes into the quick reply editor.
	 * 
	 * @param	object		data
	 */
	_insertQuotes: function(data) {
		if (!data.returnValues.template) {
			return;
		}
		
		if ($.browser.redactor) {
			this._messageField.redactor('insertDynamic', data.returnValues.template);
		}
		else {
			this._messageField.val(data.returnValues.template);
		}
	},
	
	/**
	 * Saves message.
	 */
	_save: function() {
		if (this._pendingSave) {
			return;
		}
		
		var $message = '';
		if ($.browser.redactor) {
			$message = this._messageField.redactor('getText');
		}
		else {
			$message = $.trim(this._messageField.val());
		}
		
		// check if message is empty
		var $innerError = this._messageField.parent().find('small.innerError');
		if ($message === '' || $message === '0') {
			if (!$innerError.length) {
				$innerError = $('<small class="innerError" />').appendTo(this._messageField.parent());
			}
			
			$innerError.html(WCF.Language.get('wcf.global.form.error.empty'));
			return;
		}
		else {
			$innerError.remove();
		}
		
		this._pendingSave = true;
		
		this._proxy.setOption('data', {
			actionName: 'quickReply',
			className: this._getClassName(),
			interfaceName: 'wcf\\data\\IMessageQuickReplyAction',
			parameters: this._getParameters($message)
		});
		this._proxy.sendRequest();
		
		// show spinner and hide Redactor
		var $messageBody = this._container.find('.messageQuickReplyContent .messageBody');
		$('<span class="icon icon48 icon-spinner" />').appendTo($messageBody);
		var $redactorBox = $messageBody.children('.redactor_box').hide();
		
		// hide message tabs
		$redactorBox.next().hide();
		
		// hide form submit
		$messageBody.next().hide();
	},
	
	/**
	 * Returns the parameters for the save request.
	 * 
	 * @param	string		message
	 * @return	object
	 */
	_getParameters: function(message) {
		var $parameters = {
			objectID: this._getObjectID(),
			data: {
				message: message
			},
			lastPostTime: this._container.data('lastPostTime'),
			pageNo: this._container.data('pageNo'),
			removeQuoteIDs: (this._quoteManager === null ? [ ] : this._quoteManager.getQuotesMarkedForRemoval()),
			tmpHash: this._container.data('tmpHash') || ''
		};
		if (this._container.data('anchor')) {
			$parameters.anchor = this._container.data('anchor');
		}
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.messageOptionsInline', 'submit_' + this._messageField.wcfIdentify(), $parameters.data);
		
		return $parameters;
	},
	
	/**
	 * Cancels quick reply.
	 */
	_cancel: function() {
		this._revertQuickReply(true);
		
		if ($.browser.redactor) {
			this._messageField.redactor('reset');
		}
		else {
			this._messageField.val('');
		}
	},
	
	/**
	 * Reverts quick reply to original state and optionally hiding it.
	 * 
	 * @param	boolean		hide
	 */
	_revertQuickReply: function(hide) {
		var $messageBody = this._container.find('.messageQuickReplyContent .messageBody');
		
		if (hide) {
			this._container.hide();
			
			// remove previous error messages
			$messageBody.children('small.innerError').remove();
		}
		
		// display Redactor
		$messageBody.children('.icon-spinner').remove();
		$messageBody.children('.redactor_box').show().next().show();
		
		// display form submit
		$messageBody.next().show();
		
		this._quickReplyButtons.show();
	},
	
	/**
	 * Prepares jump to extended message add form.
	 */
	_prepareExtended: function() {
		this._pendingSave = true;
		
		// mark quotes for removal
		if (this._quoteManager !== null) {
			this._quoteManager.markQuotesForRemoval();
		}
		
		var $message = '';
		if ($.browser.redactor) {
			$message = this._messageField.redactor('getText');
		}
		else {
			$message = this._messageField.val();
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'jumpToExtended',
				className: this._getClassName(),
				interfaceName: 'wcf\\data\\IExtendedMessageQuickReplyAction',
				parameters: {
					containerID: this._getObjectID(),
					message: $message
				}
			},
			success: function(data, textStatus, jqXHR) {
				window.location = data.returnValues.url;
			}
		});
	},
	
	/**
	 * Handles successful AJAX calls.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if ($.browser.redactor) {
			this._messageField.redactor('autosavePurge');
		}
		
		// redirect to new page
		if (data.returnValues.url) {
			window.location = data.returnValues.url;
		}
		else {
			if (data.returnValues.template) {
				// insert HTML
				var $message = $('' + data.returnValues.template);
				if (this._container.data('sortOrder') == 'DESC') {
					$message.insertAfter(this._container);
				}
				else {
					$message.insertBefore(this._container);
				}
				
				// update last post time
				this._container.data('lastPostTime', data.returnValues.lastPostTime);
				
				// show notification
				this._notification.show(undefined, undefined, WCF.Language.get('wcf.global.success.add'));
				
				this._updateHistory($message.wcfIdentify());
			}
			else {
				// show notification
				var $message = (this._successMessageNonVisible) ? this._successMessageNonVisible : 'wcf.global.success.add';
				this._notification.show(undefined, 5000, WCF.Language.get($message));
			}
			
			if ($.browser.redactor) {
				this._messageField.redactor('reset');
			}
			else {
				this._messageField.val('');
			}
			
			// hide quick reply and revert it
			this._revertQuickReply(true);
			
			// count stored quotes
			if (this._quoteManager !== null) {
				this._quoteManager.countQuotes();
			}
			
			this._pendingSave = false;
		}
	},
	
	/**
	 * Reverts quick reply on failure to preserve entered message.
	 */
	_failure: function(data) {
		this._pendingSave = false;
		this._revertQuickReply(false);
		
		if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
			return true;
		}
		
		var $messageBody = this._container.find('.messageQuickReplyContent .messageBody');
		var $innerError = $messageBody.children('small.innerError').empty();
		if (!$innerError.length) {
			$innerError = $('<small class="innerError" />').appendTo($messageBody);
		}
		
		$innerError.html(data.returnValues.errorType);
		
		return false;
	},
	
	/**
	 * Returns action class name.
	 * 
	 * @return	string
	 */
	_getClassName: function() {
		return '';
	},
	
	/**
	 * Returns object id.
	 * 
	 * @return	integer
	 */
	_getObjectID: function() {
		return 0;
	},
	
	/**
	 * Updates the history to avoid old content when going back in the browser
	 * history.
	 * 
	 * @param	hash
	 */
	_updateHistory: function(hash) {
		window.location.hash = hash;
	}
});

/**
 * Provides an inline message editor.
 * 
 * @param	integer		containerID
 */
WCF.Message.InlineEditor = Class.extend({
	/**
	 * currently active message
	 * @var	string
	 */
	_activeElementID: '',
	
	/**
	 * list of messages
	 * @var	object
	 */
	_container: { },
	
	/**
	 * container id
	 * @var	integer
	 */
	_containerID: 0,
	
	/**
	 * list of dropdowns
	 * @var	object
	 */
	_dropdowns: { },
	
	/**
	 * CSS selector for the message container
	 * @var	string
	 */
	_messageContainerSelector: '.jsMessage',
	
	/**
	 * prefix of the message editor CSS id
	 * @var	string
	 */
	_messageEditorIDPrefix: 'messageEditor',
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * quote manager object
	 * @var	WCF.Message.Quote.Manager
	 */
	_quoteManager: null,
	
	/**
	 * support for extended editing form
	 * @var	boolean
	 */
	_supportExtendedForm: false,
	
	/**
	 * Initializes a new WCF.Message.InlineEditor object.
	 * 
	 * @param	integer				containerID
	 * @param	boolean				supportExtendedForm
	 * @param	WCF.Message.Quote.Manager	quoteManager
	 */
	init: function(containerID, supportExtendedForm, quoteManager) {
		this._activeElementID = '';
		this._container = { };
		this._containerID = parseInt(containerID);
		this._dropdowns = { };
		this._quoteManager = quoteManager || null;
		this._supportExtendedForm = (supportExtendedForm) ? true : false;
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
		
		this.initContainers();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.InlineEditor', $.proxy(this.initContainers, this));
	},
	
	/**
	 * Initializes editing capability for all messages.
	 */
	initContainers: function() {
		$(this._messageContainerSelector).each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!this._container[$containerID]) {
				this._container[$containerID] = $container;
				
				if ($container.data('canEditInline')) {
					var $button = $container.find('.jsMessageEditButton:eq(0)').data('containerID', $containerID).click($.proxy(this._clickInline, this));
					if ($container.data('canEdit')) $button.dblclick($.proxy(this._click, this));
				}
				else if ($container.data('canEdit')) {
					$container.find('.jsMessageEditButton:eq(0)').data('containerID', $containerID).click($.proxy(this._click, this));
				}
			}
		}, this));
	},
	
	/**
	 * Loads WYSIWYG editor for selected message.
	 * 
	 * @param	object		event
	 * @param	integer		containerID
	 * @return	boolean
	 */
	_click: function(event, containerID) {
		var $containerID = (event === null) ? containerID : $(event.currentTarget).data('containerID');
		if (this._activeElementID === '') {
			this._activeElementID = $containerID;
			this._prepare();
			
			this._proxy.setOption('data', {
				actionName: 'beginEdit',
				className: this._getClassName(),
				interfaceName: 'wcf\\data\\IMessageInlineEditorAction',
				parameters: {
					containerID: this._containerID,
					objectID: this._container[$containerID].data('objectID')
				}
			});
			this._proxy.setOption('failure', $.proxy(function() { this._cancel(); }, this));
			this._proxy.sendRequest();
		}
		else {
			var $notification = new WCF.System.Notification(WCF.Language.get('wcf.message.error.editorAlreadyInUse'), 'warning');
			$notification.show();
		}
		
		// force closing dropdown to avoid displaying the dropdown after
		// triple clicks
		if (this._dropdowns[this._container[$containerID].data('objectID')]) {
			this._dropdowns[this._container[$containerID].data('objectID')].removeClass('dropdownOpen');
		}
		
		if (event !== null) {
			event.stopPropagation();
			return false;
		}
	},
	
	/**
	 * Provides an inline dropdown menu instead of directly loading the WYSIWYG editor.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_clickInline: function(event) {
		var $button = $(event.currentTarget);
		
		if (!$button.hasClass('dropdownToggle')) {
			var $containerID = $button.data('containerID');
			
			$button.addClass('dropdownToggle').parent().addClass('dropdown');
			
			var $dropdownMenu = $('<ul class="dropdownMenu" />').insertAfter($button);
			this._initDropdownMenu($containerID, $dropdownMenu);
			
			WCF.DOMNodeInsertedHandler.execute();
			
			this._dropdowns[this._container[$containerID].data('objectID')] = $dropdownMenu;
			
			WCF.Dropdown.registerCallback($button.parent().wcfIdentify(), $.proxy(this._toggleDropdown, this));
			
			// trigger click event
			$button.trigger('click');
		}
		
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Handles errorneus editing requests.
	 * 
	 * @param	object		data
	 */
	_failure: function(data) {
		this._revertEditor();
		
		if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
			return true;
		}
		
		var $messageBody = this._container[this._activeElementID].find('.messageBody .messageInlineEditor');
		var $innerError = $messageBody.children('small.innerError').empty();
		if (!$innerError.length) {
			$innerError = $('<small class="innerError" />').insertBefore($messageBody.children('.formSubmit'));
		}
		
		$innerError.html(data.returnValues.errorType);
		
		return false;
	},
	
	/**
	 * Forces message options to stay visible if toggling dropdown menu.
	 * 
	 * @param	string		containerID
	 * @param	string		action
	 */
	_toggleDropdown: function(containerID, action) {
		WCF.Dropdown.getDropdown(containerID).parents('.messageOptions').toggleClass('forceOpen');
	},
	
	/**
	 * Initializes the inline edit dropdown menu.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		dropdownMenu
	 */
	_initDropdownMenu: function(containerID, dropdownMenu) { },
	
	/**
	 * Prepares message for WYSIWYG display.
	 */
	_prepare: function() {
		var $messageBody = this._container[this._activeElementID].find('.messageBody');
		$('<span class="icon icon48 icon-spinner" />').appendTo($messageBody);
		
		var $content = $messageBody.find('.messageText').hide();
		
		// hide unrelated content
		$content.parent().children('.jsInlineEditorHideContent').hide();
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').hide();
	},
	
	/**
	 * Cancels editing and reverts to original message.
	 */
	_cancel: function() {
		var $container = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget');
		
		// remove editor
		this._destroyEditor();
		
		// restore message
		var $messageBody = $container.find('.messageBody');
		$messageBody.children('.icon-spinner').remove();
		$messageBody.find('.messageText').show();
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		
		// show unrelated content
		$messageBody.find('.jsInlineEditorHideContent').show();
		
		// revert message options
		this._container[this._activeElementID].find('.messageOptions').removeClass('forceHidden');
		
		this._activeElementID = '';
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Handles successful AJAX calls.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.returnValues.actionName) {
			case 'beginEdit':
				this._showEditor(data);
			break;
			
			case 'save':
				this._showMessage(data);
			break;
		}
	},
	
	/**
	 * Shows WYSIWYG editor for active message.
	 * 
	 * @param	object		data
	 */
	_showEditor: function(data) {
		// revert failure function
		this._proxy.setOption('failure', $.proxy(this._failure, this));
		
		var $messageBody = this._container[this._activeElementID].addClass('jsInvalidQuoteTarget').find('.messageBody');
		$messageBody.children('.icon-spinner').remove();
		var $content = $messageBody.children('div:eq(0)');
		
		// insert wysiwyg
		$('' + data.returnValues.template).appendTo($content);
		
		// bind buttons
		var $formSubmit = $content.find('.formSubmit');
		var $saveButton = $formSubmit.find('button[data-type=save]').click($.proxy(this._save, this));
		if (this._supportExtendedForm) $formSubmit.find('button[data-type=extended]').click($.proxy(this._prepareExtended, this));
		$formSubmit.find('button[data-type=cancel]').click($.proxy(this._cancel, this));
		
		WCF.Message.Submit.registerButton(
			this._messageEditorIDPrefix + this._container[this._activeElementID].data('objectID'),
			$saveButton
		);
		
		// hide message options
		this._container[this._activeElementID].find('.messageOptions').addClass('forceHidden');
		
		var $element = $('#' + this._messageEditorIDPrefix + this._container[this._activeElementID].data('objectID'));
		if ($.browser.redactor) {
			new WCF.PeriodicalExecuter($.proxy(function(pe) {
				pe.stop();
				
				if (this._quoteManager) {
					this._quoteManager.setAlternativeEditor($element);
				}
			}, this), 250);
		}
		else {
			$element.focus();
		}
	},
	
	/**
	 * Reverts editor.
	 */
	_revertEditor: function() {
		var $messageBody = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget').find('.messageBody');
		$messageBody.children('span.icon-spinner').remove();
		$messageBody.children('div:eq(0)').children(':not(.messageText)').show();
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		
		// show unrelated content
		$messageBody.find('.jsInlineEditorHideContent').show();
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Saves editor contents.
	 */
	_save: function() {
		var $container = this._container[this._activeElementID];
		var $objectID = $container.data('objectID');
		var $message = '';
		
		if ($.browser.redactor) {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).redactor('getText');
		}
		else {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).val();
		}
		
		var $parameters = {
			containerID: this._containerID,
			data: {
				message: $message
			},
			objectID: $objectID
		};
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.messageOptionsInline', 'submit_' + this._messageEditorIDPrefix + $objectID, $parameters);
		
		this._proxy.setOption('data', {
			actionName: 'save',
			className: this._getClassName(),
			interfaceName: 'wcf\\data\\IMessageInlineEditorAction',
			parameters: $parameters
		});
		this._proxy.sendRequest();
		
		this._hideEditor();
	},
	
	/**
	 * Prepares jumping to extended editing mode.
	 */
	_prepareExtended: function() {
		var $container = this._container[this._activeElementID];
		var $objectID = $container.data('objectID');
		var $message = '';
		
		if ($.browser.redactor) {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).redactor('getText');
		}
		else {
			$message = $('#' + this._messageEditorIDPrefix + $objectID).val();
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'jumpToExtended',
				className: this._getClassName(),
				parameters: {
					containerID: this._containerID,
					message: $message,
					messageID: $objectID
				}
			},
			success: function(data, textStatus, jqXHR) {
				window.location = data.returnValues.url;
			}
		});
	},
	
	/**
	 * Hides WYSIWYG editor.
	 */
	_hideEditor: function() {
		var $messageBody = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget').find('.messageBody');
		$('<span class="icon icon48 icon-spinner" />').appendTo($messageBody);
		$messageBody.children('div:eq(0)').children().hide();
		$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		
		// show unrelated content
		$messageBody.find('.jsInlineEditorHideContent').show();
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Shows rendered message.
	 * 
	 * @param	object		data
	 */
	_showMessage: function(data) {
		var $container = this._container[this._activeElementID].removeClass('jsInvalidQuoteTarget');
		var $messageBody = $container.find('.messageBody');
		$messageBody.children('.icon-spinner').remove();
		var $content = $messageBody.children('div:eq(0)');
		
		// show unrelated content
		$content.parent().children('.jsInlineEditorHideContent').show();
		
		// revert message options
		this._container[this._activeElementID].find('.messageOptions').removeClass('forceHidden');
		
		// remove editor
		this._destroyEditor();
		
		$content.children('.messageText').html(data.returnValues.message).show();
		
		if (data.returnValues.attachmentList == undefined) {
			$messageBody.children('.attachmentThumbnailList, .attachmentFileList').show();
		}
		else {
			$messageBody.children('.attachmentThumbnailList, .attachmentFileList').remove();
			
			if (data.returnValues.attachmentList) {
				$(data.returnValues.attachmentList).insertAfter($messageBody.children('div:eq(0)'));
			}
		}
		
		this._activeElementID = '';
		
		this._updateHistory(this._getHash($container.data('objectID')));
		
		this._notification.show();
		
		if (this._quoteManager) {
			this._quoteManager.clearAlternativeEditor();
		}
	},
	
	/**
	 * Destroies editor instance and removes it's DOM elements.
	 */
	_destroyEditor: function() {
		var $container = this._container[this._activeElementID];
		
		// destroy editor
		if ($.browser.redactor) {
			var $target = $('#' + this._messageEditorIDPrefix + $container.data('objectID'));
			$target.redactor('autosavePurge');
			$target.redactor('destroy');
		}
		
		// purge DOM elements
		$container.find('.messageBody > div > .messageInlineEditor').remove();
		
		// remove event listeners
		WCF.System.Event.removeAllListeners('com.woltlab.wcf.messageOptionsInline', 'submit_' + this._messageEditorIDPrefix + $container.data('objectID'));
	},
	
	/**
	 * Returns message action class name.
	 * 
	 * @return	string
	 */
	_getClassName: function() {
		return '';
	},
	
	/**
	 * Returns the hash added to the url after successfully editing a message.
	 * 
	 * @return	string
	 */
	_getHash: function(objectID) {
		return '#message' + objectID;
	},
	
	/**
	 * Updates the history to avoid old content when going back in the browser
	 * history.
	 * 
	 * @param	hash
	 */
	_updateHistory: function(hash) {
		window.location.hash = hash;
	}
});

/**
 * Handles submit buttons for forms with an embedded WYSIWYG editor.
 */
WCF.Message.Submit = {
	/**
	 * list of registered buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * Registers submit button for specified wysiwyg container id.
	 * 
	 * @param	string		wysiwygContainerID
	 * @param	string		selector
	 */
	registerButton: function(wysiwygContainerID, selector) {
		if (!WCF.Browser.isChrome()) {
			return;
		}
		
		this._buttons[wysiwygContainerID] = $(selector);
	},
	
	/**
	 * Triggers 'click' event for registered buttons.
	 */
	execute: function(wysiwygContainerID) {
		if (!this._buttons[wysiwygContainerID]) {
			return;
		}
		
		this._buttons[wysiwygContainerID].trigger('click');
	}
};

/**
 * Namespace for message quotes.
 */
WCF.Message.Quote = { };

/**
 * Handles message quotes.
 * 
 * @param	string		className
 * @param	string		objectType
 * @param	string		containerSelector
 * @param	string		messageBodySelector
 */
WCF.Message.Quote.Handler = Class.extend({
	/**
	 * active container id
	 * @var	string
	 */
	_activeContainerID: '',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * list of message containers
	 * @var	object
	 */
	_containers: { },
	
	/**
	 * container selector
	 * @var	string
	 */
	_containerSelector: '',
	
	/**
	 * 'copy quote' overlay
	 * @var	jQuery
	 */
	_copyQuote: null,
	
	/**
	 * marked message
	 * @var	string
	 */
	_message: '',
	
	/**
	 * message body selector
	 * @var	string
	 */
	_messageBodySelector: '',
	
	/**
	 * object id
	 * @var	integer
	 */
	_objectID: 0,
	
	/**
	 * object type name
	 * @var	string
	 */
	_objectType: '',
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * quote manager
	 * @var	WCF.Message.Quote.Manager
	 */
	_quoteManager: null,
	
	/**
	 * Initializes the quote handler for given object type.
	 * 
	 * @param	WCF.Message.Quote.Manager	quoteManager
	 * @param	string				className
	 * @param	string				objectType
	 * @param	string				containerSelector
	 * @param	string				messageBodySelector
	 * @param	string				messageContentSelector
	 */
	init: function(quoteManager, className, objectType, containerSelector, messageBodySelector, messageContentSelector) {
		this._className = className;
		if (this._className == '') {
			console.debug("[WCF.Message.QuoteManager] Empty class name given, aborting.");
			return;
		}
		
		this._objectType = objectType;
		if (this._objectType == '') {
			console.debug("[WCF.Message.QuoteManager] Empty object type name given, aborting.");
			return;
		}
		
		this._containerSelector = containerSelector;
		this._message = '';
		this._messageBodySelector = messageBodySelector;
		this._messageContentSelector = messageContentSelector;
		this._objectID = 0;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initContainers();
		this._initCopyQuote();
		
		$(document).mouseup($.proxy(this._mouseUp, this));
		
		// register with quote manager
		this._quoteManager = quoteManager;
		this._quoteManager.register(this._objectType, this);
		
		// register with DOMNodeInsertedHandler
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.Quote.Handler' + objectType.hashCode(), $.proxy(this._initContainers, this));
	},
	
	/**
	 * Initializes message containers.
	 */
	_initContainers: function() {
		var self = this;
		$(this._containerSelector).each(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!self._containers[$containerID]) {
				self._containers[$containerID] = $container;
				if ($container.hasClass('jsInvalidQuoteTarget')) {
					return true;
				}
				
				if (self._messageBodySelector !== null) {
					$container = $container.find(self._messageBodySelector).data('containerID', $containerID);
				}
				
				$container.mousedown($.proxy(self._mouseDown, self));
				
				// bind event to quote whole message
				self._containers[$containerID].find('.jsQuoteMessage').click($.proxy(self._saveFullQuote, self));
			}
		});
	},
	
	/**
	 * Handles mouse down event.
	 * 
	 * @param	object		event
	 */
	_mouseDown: function(event) {
		// hide copy quote
		this._copyQuote.hide();
		
		// store container ID
		var $container = $(event.currentTarget);
		
		if (this._messageBodySelector) {
			$container = this._containers[$container.data('containerID')];
		}
		
		if ($container.hasClass('jsInvalidQuoteTarget')) {
			this._activeContainerID = '';
			
			return;
		}
		
		this._activeContainerID = $container.wcfIdentify();
		
		// remove alt-tag from all images, fixes quoting in Firefox
		if ($.browser.mozilla) {
			$container.find('img').each(function() {
				var $image = $(this);
				$image.data('__alt', $image.attr('alt')).removeAttr('alt');
			});
		}
	},
	
	/**
	 * Returns the text of a node and its children.
	 * 
	 * @param	object		node
	 * @return	string
	 */
	_getNodeText: function(node) {
		var nodeText = '';
		
		for (var i = 0; i < node.childNodes.length; i++) {
			if (node.childNodes[i].nodeType == 3) {
				// text node
				nodeText += node.childNodes[i].nodeValue;
			}
			else {
				if (!node.childNodes[i].tagName) {
					continue;
				}
				
				var $tagName = node.childNodes[i].tagName.toLowerCase();
				if ($tagName === 'li') {
					nodeText += "\r\n";
				}
				else if ($tagName === 'td' && !$.browser.msie) {
					nodeText += "\r\n";
				}
				
				nodeText += this._getNodeText(node.childNodes[i]);
				
				if ($tagName === 'ul') {
					nodeText += "\n";
				}
			}
		}
		
		return nodeText;
	},
	
	/**
	 * Handles the mouse up event.
	 * 
	 * @param	object		event
	 */
	_mouseUp: function(event) {
		// ignore event
		if (this._activeContainerID == '') {
			this._copyQuote.hide();
			
			return;
		}
		
		var $container = this._containers[this._activeContainerID];
		var $selection = this._getSelectedText();
		var $text = $.trim($selection);
		if ($text == '') {
			this._copyQuote.hide();
			
			return;
		}
		
		// compare selection with message text of given container
		var $messageText = null;
		if (this._messageBodySelector) {
			$messageText = this._getNodeText($container.find(this._messageContentSelector).get(0));
		}
		else {
			$messageText = this._getNodeText($container.get(0));
		}
		
		// selected text is not part of $messageText or contains text from unrelated nodes
		if (this._normalize($messageText).indexOf(this._normalize($text)) === -1) {
			return;
		}
		this._copyQuote.show();
		
		var $coordinates = this._getBoundingRectangle($container, $selection);
		var $dimensions = this._copyQuote.getDimensions('outer');
		var $left = ($coordinates.right - $coordinates.left) / 2 - ($dimensions.width / 2) + $coordinates.left;
		
		this._copyQuote.css({
			top: $coordinates.top - $dimensions.height - 7 + 'px',
			left: $left + 'px'
		});
		this._copyQuote.hide();
		
		// reset containerID
		this._activeContainerID = '';
		
		// show element after a delay, to prevent display if text was unmarked again (clicking into marked text)
		var self = this;
		new WCF.PeriodicalExecuter(function(pe) {
			pe.stop();
			
			var $text = $.trim(self._getSelectedText());
			if ($text != '') {
				self._copyQuote.show();
				self._message = $text;
				self._objectID = $container.data('objectID');
				
				// revert alt tags, fixes quoting in Firefox
				if ($.browser.mozilla) {
					$container.find('img').each(function() {
						var $image = $(this);
						$image.attr('alt', $image.data('__alt'));
					});
				}
			}
		}, 10);
	},
	
	/**
	 * Normalizes a text for comparison.
	 * 
	 * @param	string		text
	 * @return	string
	 */
	_normalize: function(text) {
		return text.replace(/\r?\n|\r/g, "\n").replace(/\s/g, ' ').replace(/\s{2,}/g, ' ');
	},
	
	/**
	 * Returns the left or right offset of the current text selection.
	 * 
	 * @param	object		range
	 * @param	boolean		before
	 * @return	object
	 */
	_getOffset: function(range, before) {
		range.collapse(before);
		
		var $elementID = WCF.getRandomID();
		var $element = document.createElement('span');
		$element.innerHTML = '<span id="' + $elementID + '"></span>';
		var $fragment = document.createDocumentFragment(), $node;
		while ($node = $element.firstChild) {
			$fragment.appendChild($node);
		}
		range.insertNode($fragment);
		
		$element = $('#' + $elementID);
		var $position = $element.offset();
		$position.top = $position.top - $(window).scrollTop();
		$element.remove();
		
		return $position;
	},
	
	/**
	 * Returns the offsets of the selection's bounding rectangle.
	 * 
	 * @return	object
	 */
	_getBoundingRectangle: function(container, selection) {
		var $coordinates = null;
		
		if (document.createRange && typeof document.createRange().getBoundingClientRect != "undefined") { // Opera, Firefox, Safari, Chrome
			if (selection.rangeCount > 0) {
				// the coordinates returned by getBoundingClientRect() is relative to the window, not the document!
				//var $rect = selection.getRangeAt(0).getBoundingClientRect();
				var $rects = selection.getRangeAt(0).getClientRects();
				var $rect = selection.getRangeAt(0).getBoundingClientRect();
				
				/*
				var $rect = { };
				if (!$.browser.mozilla && $rects.length > 1) {
					// save current selection to restore it later
					var $range = selection.getRangeAt(0);
					var $bckp = this._saveSelection(container.get(0));
					var $position1 = this._getOffset($range, true);
					
					var $range = selection.getRangeAt(0);
					var $position2 = this._getOffset($range, false);
					
					$rect = {
						left: Math.min($position1.left, $position2.left),
						right: Math.max($position1.left, $position2.left),
						top: Math.max($position1.top, $position2.top)
					};
					
					// restore selection
					this._restoreSelection(container.get(0), $bckp);
				}
				else {
					$rect = selection.getRangeAt(0).getBoundingClientRect();
				}
				*/
				
				var $document = $(document);
				var $offsetTop = $document.scrollTop();
				
				$coordinates = {
					left: $rect.left,
					right: $rect.right,
					top: $rect.top + $offsetTop
				};
			}
		}
		else if (document.selection && document.selection.type != "Control") { // IE
			var $range = document.selection.createRange();
			
			$coordinates = {
				left: $range.boundingLeft,
				right: $range.boundingRight,
				top: $range.boundingTop
			};
		}
		
		return $coordinates;
	},
	
	/**
	 * Saves current selection.
	 * 
	 * @see		http://stackoverflow.com/a/13950376
	 * 
	 * @param	object		containerEl
	 * @return	object
	 */
	_saveSelection: function(containerEl) {
		if (window.getSelection && document.createRange) {
			var range = window.getSelection().getRangeAt(0);
			var preSelectionRange = range.cloneRange();
			preSelectionRange.selectNodeContents(containerEl);
			preSelectionRange.setEnd(range.startContainer, range.startOffset);
			var start = preSelectionRange.toString().length;
			
			return {
				start: start,
				end: start + range.toString().length
			};
		}
		else {
			var selectedTextRange = document.selection.createRange();
			var preSelectionTextRange = document.body.createTextRange();
			preSelectionTextRange.moveToElementText(containerEl);
			preSelectionTextRange.setEndPoint("EndToStart", selectedTextRange);
			var start = preSelectionTextRange.text.length;
			
			return {
				start: start,
				end: start + selectedTextRange.text.length
			};
		}
	},
	
	/**
	 * Restores a selection.
	 * 
	 * @see		http://stackoverflow.com/a/13950376
	 * 
	 * @param	object		containerEl
	 * @param	object		savedSel
	 */
	_restoreSelection: function(containerEl, savedSel) {
		if (window.getSelection && document.createRange) {
			var charIndex = 0, range = document.createRange();
			range.setStart(containerEl, 0);
			range.collapse(true);
			var nodeStack = [containerEl], node, foundStart = false, stop = false;
			
			while (!stop && (node = nodeStack.pop())) {
				if (node.nodeType == 3) {
					var nextCharIndex = charIndex + node.length;
					if (!foundStart && savedSel.start >= charIndex && savedSel.start <= nextCharIndex) {
						range.setStart(node, savedSel.start - charIndex);
						foundStart = true;
					}
					if (foundStart && savedSel.end >= charIndex && savedSel.end <= nextCharIndex) {
						range.setEnd(node, savedSel.end - charIndex);
						stop = true;
					}
					charIndex = nextCharIndex;
				} else {
					var i = node.childNodes.length;
					while (i--) {
						nodeStack.push(node.childNodes[i]);
					};
				};
			}
			
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		}
		else {
			var textRange = document.body.createTextRange();
			textRange.moveToElementText(containerEl);
			textRange.collapse(true);
			textRange.moveEnd("character", savedSel.end);
			textRange.moveStart("character", savedSel.start);
			textRange.select();
		}
	},
	
	/**
	 * Initializes the 'copy quote' element.
	 */
	_initCopyQuote: function() {
		this._copyQuote = $('#quoteManagerCopy');
		if (!this._copyQuote.length) {
			this._copyQuote = $('<div id="quoteManagerCopy" class="balloonTooltip"><span>' + WCF.Language.get('wcf.message.quote.quoteSelected') + '</span><span class="pointer"><span></span></span></div>').hide().appendTo(document.body);
			this._copyQuote.click($.proxy(this._saveQuote, this));
		}
	},
	
	/**
	 * Returns the text selection.
	 * 
	 * @return	object
	 */
	_getSelectedText: function() {
		if (window.getSelection) { // Opera, Firefox, Safari, Chrome, IE 9+
			return window.getSelection();
		}
		else if (document.getSelection) { // Opera, Firefox, Safari, Chrome, IE 9+
			return document.getSelection();
		}
		else if (document.selection) { // IE 8
			return document.selection.createRange().text;
		}
		
		return '';
	},
	
	/**
	 * Saves a full quote.
	 * 
	 * @param	object		event
	 */
	_saveFullQuote: function(event) {
		var $listItem = $(event.currentTarget);
		
		this._proxy.setOption('data', {
			actionName: 'saveFullQuote',
			className: this._className,
			interfaceName: 'wcf\\data\\IMessageQuoteAction',
			objectIDs: [ $listItem.data('objectID') ]
		});
		this._proxy.sendRequest();
		
		// mark element as quoted
		if ($listItem.data('isQuoted')) {
			$listItem.data('isQuoted', false).children('a').removeClass('active');
		}
		else {
			$listItem.data('isQuoted', true).children('a').addClass('active');
		}
		
		// discard event
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Saves a quote.
	 */
	_saveQuote: function() {
		this._proxy.setOption('data', {
			actionName: 'saveQuote',
			className: this._className,
			interfaceName: 'wcf\\data\\IMessageQuoteAction',
			objectIDs: [ this._objectID ],
			parameters: {
				message: this._message
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.count !== undefined) {
			var $fullQuoteObjectIDs = (data.fullQuoteObjectIDs !== undefined) ? data.fullQuoteObjectIDs : { };
			this._quoteManager.updateCount(data.returnValues.count, $fullQuoteObjectIDs);
		}
	},
	
	/**
	 * Updates the full quote data for all matching objects.
	 * 
	 * @param	array<integer>		$objectIDs
	 */
	updateFullQuoteObjectIDs: function(objectIDs) {
		for (var $containerID in this._containers) {
			this._containers[$containerID].find('.jsQuoteMessage').each(function(index, button) {
				// reset all markings
				var $button = $(button).data('isQuoted', 0);
				$button.children('a').removeClass('active');
				
				// mark as active
				if (WCF.inArray($button.data('objectID'), objectIDs)) {
					$button.data('isQuoted', 1).children('a').addClass('active');
				}
			});
		}
	}
});

/**
 * Manages stored quotes.
 * 
 * @param	integer		count
 */
WCF.Message.Quote.Manager = Class.extend({
	/**
	 * list of form buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * number of stored quotes
	 * @var	integer
	 */
	_count: 0,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Redactor element
	 * @var	jQuery
	 */
	_editorElement: null,
	
	/**
	 * alternative Redactor element
	 * @var	jQuery
	 */
	_editorElementAlternative: null,
	
	/**
	 * form element
	 * @var	jQuery
	 */
	_form: null,
	
	/**
	 * list of quote handlers
	 * @var	object
	 */
	_handlers: { },
	
	/**
	 * true, if an up-to-date template exists
	 * @var	boolean
	 */
	_hasTemplate: false,
	
	/**
	 * true, if related quotes should be inserted
	 * @var	boolean
	 */
	_insertQuotes: true,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of quotes to remove upon submit
	 * @var	array<string>
	 */
	_removeOnSubmit: [ ],
	
	/**
	 * show quotes element
	 * @var	jQuery
	 */
	_showQuotes: null,
	
	/**
	 * allow pasting
	 * @var	boolean
	 */
	_supportPaste: false,
	
	/**
	 * Initializes the quote manager.
	 * 
	 * @param	integer		count
	 * @param	string		elementID
	 * @param	boolean		supportPaste
	 * @param	array<string>	removeOnSubmit
	 */
	init: function(count, elementID, supportPaste, removeOnSubmit) {
		this._buttons = {
			insert: null,
			remove: null
		};
		this._count = parseInt(count) || 0;
		this._dialog = null;
		this._editorElement = null;
		this._editorElementAlternative = null;
		this._form = null;
		this._handlers = { };
		this._hasTemplate = false;
		this._insertQuotes = true;
		this._removeOnSubmit = [ ];
		this._showQuotes = null;
		this._supportPaste = false;
		
		if (elementID) {
			this._editorElement = $('#' + elementID);
			if (this._editorElement.length) {
				this._supportPaste = true;
				
				// get surrounding form-tag
				this._form = this._editorElement.parents('form:eq(0)');
				if (this._form.length) {
					this._form.submit($.proxy(this._submit, this));
					this._removeOnSubmit = removeOnSubmit || [ ];
				}
				else {
					this._form = null;
					
					// allow override
					this._supportPaste = (supportPaste === true) ? true : false;
				}
			}
		}
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php/MessageQuote/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		this._toggleShowQuotes();
	},
	
	/**
	 * Sets an alternative editor element on runtime.
	 * 
	 * @param	jQuery		element
	 */
	setAlternativeEditor: function(element) {
		this._editorElementAlternative = element;
	},
	
	/**
	 * Clears alternative editor element.
	 */
	clearAlternativeEditor: function() {
		this._editorElementAlternative = null;
	},
	
	/**
	 * Registers a quote handler.
	 * 
	 * @param	string				objectType
	 * @param	WCF.Message.Quote.Handler	handler
	 */
	register: function(objectType, handler) {
		this._handlers[objectType] = handler;
	},
	
	/**
	 * Updates number of stored quotes.
	 * 
	 * @param	integer		count
	 * @param	object		fullQuoteObjectIDs
	 */
	updateCount: function(count, fullQuoteObjectIDs) {
		this._count = parseInt(count) || 0;
		
		this._toggleShowQuotes();
		
		// update full quote ids of handlers
		for (var $objectType in this._handlers) {
			if (fullQuoteObjectIDs[$objectType]) {
				this._handlers[$objectType].updateFullQuoteObjectIDs(fullQuoteObjectIDs[$objectType]);
			}
		}
	},
	
	/**
	 * Inserts all associated quotes upon first time using quick reply.
	 * 
	 * @param	string		className
	 * @param	integer		parentObjectID
	 * @param	object		callback
	 */
	insertQuotes: function(className, parentObjectID, callback) {
		if (!this._insertQuotes) {
			this._insertQuotes = true;
			
			return;
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'getRenderedQuotes',
				className: className,
				interfaceName: 'wcf\\data\\IMessageQuoteAction',
				parameters: {
					parentObjectID: parentObjectID
				}
			},
			success: callback
		});
	},
	
	/**
	 * Toggles the display of the 'Show quotes' button
	 */
	_toggleShowQuotes: function() {
		if (!this._count) {
			if (this._showQuotes !== null) {
				this._showQuotes.hide();
			}
		}
		else {
			if (this._showQuotes === null) {
				this._showQuotes = $('#showQuotes');
				if (!this._showQuotes.length) {
					this._showQuotes = $('<div id="showQuotes" class="balloonTooltip" />').click($.proxy(this._click, this)).appendTo(document.body);
				}
			}
			
			var $text = WCF.Language.get('wcf.message.quote.showQuotes').replace(/#count#/, this._count);
			this._showQuotes.text($text).show();
		}
		
		this._hasTemplate = false;
	},
	
	/**
	 * Handles clicks on 'Show quotes'.
	 */
	_click: function() {
		if (this._hasTemplate) {
			this._dialog.wcfDialog('open');
		}
		else {
			this._proxy.showLoadingOverlayOnce();
			
			this._proxy.setOption('data', {
				actionName: 'getQuotes',
				supportPaste: this._supportPaste
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Renders the dialog.
	 * 
	 * @param	string		template
	 */
	renderDialog: function(template) {
		// create dialog if not exists
		if (this._dialog === null) {
			this._dialog = $('#messageQuoteList');
			if (!this._dialog.length) {
				this._dialog = $('<div id="messageQuoteList" />').hide().appendTo(document.body);
			}
		}
		
		// add template
		this._dialog.html(template);
		
		// add 'insert' and 'delete' buttons
		var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
		if (this._supportPaste) this._buttons.insert = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.message.quote.insertAllQuotes') + '</button>').click($.proxy(this._insertSelected, this)).appendTo($formSubmit);
		this._buttons.remove = $('<button>' + WCF.Language.get('wcf.message.quote.removeAllQuotes') + '</button>').click($.proxy(this._removeSelected, this)).appendTo($formSubmit);
		
		// show dialog
		this._dialog.wcfDialog({
			title: WCF.Language.get('wcf.message.quote.manageQuotes')
		});
		this._dialog.wcfDialog('render');
		this._hasTemplate = true;
		
		// bind event listener
		var $insertQuoteButtons = this._dialog.find('.jsInsertQuote');
		if (this._supportPaste) {
			$insertQuoteButtons.click($.proxy(this._insertQuote, this));
		}
		else {
			$insertQuoteButtons.hide();
		}
		
		this._dialog.find('input.jsCheckbox').change($.proxy(this._changeButtons, this));
		
		// mark quotes for removal
		if (this._removeOnSubmit.length) {
			var self = this;
			this._dialog.find('input.jsRemoveQuote').each(function(index, input) {
				var $input = $(input).change($.proxy(this._change, this));
				
				// mark for deletion
				if (WCF.inArray($input.parent('li').attr('data-quote-id'), self._removeOnSubmit)) {
					$input.attr('checked', 'checked');
				}
			});
		}
	},
	
	/**
	 * Updates button labels if a checkbox is checked or unchecked.
	 */
	_changeButtons: function() {
		// selection
		if (this._dialog.find('input.jsCheckbox:checked').length) {
			if (this._supportPaste) this._buttons.insert.html(WCF.Language.get('wcf.message.quote.insertSelectedQuotes'));
			this._buttons.remove.html(WCF.Language.get('wcf.message.quote.removeSelectedQuotes'));
		}
		else {
			// no selection, pick all
			if (this._supportPaste) this._buttons.insert.html(WCF.Language.get('wcf.message.quote.insertAllQuotes'));
			this._buttons.remove.html(WCF.Language.get('wcf.message.quote.removeAllQuotes'));
		}
	},
	
	/**
	 * Checks for change event on delete-checkboxes.
	 * 
	 * @param	object		event
	 */
	_change: function(event) {
		var $input = $(event.currentTarget);
		var $quoteID = $input.parent('li').attr('data-quote-id');
		
		if ($input.prop('checked')) {
			this._removeOnSubmit.push($quoteID);
		}
		else {
			for (var $index in this._removeOnSubmit) {
				if (this._removeOnSubmit[$index] == $quoteID) {
					delete this._removeOnSubmit[$index];
					break;
				}
			}
		}
	},
	
	/**
	 * Inserts the selected quotes.
	 */
	_insertSelected: function() {
		if (this._editorElementAlternative === null) {
			var $api = $('.jsQuickReply:eq(0)').data('__api');
			if ($api && !$api.getContainer().is(':visible')) {
				this._insertQuotes = false;
				$api.click(null);
			}
		}
		
		if (!this._dialog.find('input.jsCheckbox:checked').length) {
			this._dialog.find('input.jsCheckbox').prop('checked', 'checked');
		}
		
		// insert all quotes
		this._dialog.find('input.jsCheckbox:checked').each($.proxy(function(index, input) {
			this._insertQuote(null, input);
		}, this));
		
		// close dialog
		this._dialog.wcfDialog('close');
	},
	
	/**
	 * Inserts a quote.
	 * 
	 * @param	object		event
	 * @param	object		inputElement
	 */
	_insertQuote: function(event, inputElement) {
		if (event !== null && this._editorElementAlternative === null) {
			var $api = $('.jsQuickReply:eq(0)').data('__api');
			if ($api && !$api.getContainer().is(':visible')) {
				this._insertQuotes = false;
				$api.click(null);
			}
		}
		
		var $listItem = (event === null) ? $(inputElement).parents('li') : $(event.currentTarget).parents('li');
		var $quote = $.trim($listItem.children('div.jsFullQuote').text());
		var $message = $listItem.parents('article.message');
		
		// build quote tag
		$quote = "[quote='" + $message.attr('data-username') + "','" + $message.data('link') + "']" + $quote + "[/quote]";
		
		// insert into editor
		if ($.browser.redactor) {
			if (this._editorElementAlternative === null) {
				this._editorElement.redactor('insertDynamic', $quote);
			}
			else {
				this._editorElementAlternative.redactor('insertDynamic', $quote);
			}
		}
		else {
			// plain textarea
			var $textarea = (this._editorElementAlternative === null) ? this._editorElement : this._editorElementAlternative;
			var $value = $textarea.val();
			$quote += "\n\n";
			if ($value.length == 0) {
				$textarea.val($quote);
			}
			else {
				var $position = $textarea.getCaret();
				$textarea.val( $value.substr(0, $position) + $quote + $value.substr($position) );
			}
		}
		
		// remove quote upon submit or upon request
		this._removeOnSubmit.push($listItem.attr('data-quote-id'));
		
		// close dialog
		if (event !== null) {
			this._dialog.wcfDialog('close');
		}
	},
	
	/**
	 * Removes selected quotes.
	 */
	_removeSelected: function() {
		if (!this._dialog.find('input.jsCheckbox:checked').length) {
			this._dialog.find('input.jsCheckbox').prop('checked', 'checked');
		}
		
		var $quoteIDs = [ ];
		this._dialog.find('input.jsCheckbox:checked').each(function(index, input) {
			$quoteIDs.push($(input).parents('li').attr('data-quote-id'));
		});
		
		if ($quoteIDs.length) {
			// get object types
			var $objectTypes = [ ];
			for (var $objectType in this._handlers) {
				$objectTypes.push($objectType);
			}
			
			this._proxy.setOption('data', {
				actionName: 'remove',
				getFullQuoteObjectIDs: this._handlers.length > 0,
				objectTypes: $objectTypes,
				quoteIDs: $quoteIDs
			});
			this._proxy.sendRequest();
			
			this._dialog.wcfDialog('close');
		}
	},
	
	/**
	 * Appends list of quote ids to remove after successful submit.
	 */
	_submit: function() {
		if (this._supportPaste && this._removeOnSubmit.length > 0) {
			var $formSubmit = this._form.find('.formSubmit');
			for (var $i in this._removeOnSubmit) {
				$('<input type="hidden" name="__removeQuoteIDs[]" value="' + this._removeOnSubmit[$i] + '" />').appendTo($formSubmit);
			}
		}
	},
	
	/**
	 * Returns a list of quote ids marked for removal.
	 * 
	 * @return	array<integer>
	 */
	getQuotesMarkedForRemoval: function() {
		return this._removeOnSubmit;
	},
	
	/**
	 * Marks quote ids for removal.
	 */
	markQuotesForRemoval: function() {
		if (this._removeOnSubmit.length) {
			this._proxy.setOption('data', {
				actionName: 'markForRemoval',
				quoteIDs: this._removeOnSubmit
			});
			this._proxy.suppressErrors();
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Removes all marked quote ids.
	 */
	removeMarkedQuotes: function() {
		if (this._removeOnSubmit.length) {
			this._proxy.setOption('data', {
				actionName: 'removeMarkedQuotes',
				getFullQuoteObjectIDs: this._handlers.length > 0
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Counts stored quotes.
	 */
	countQuotes: function() {
		var $objectTypes = [ ];
		for (var $objectType in this._handlers) {
			$objectTypes.push($objectType);
		}
		
		this._proxy.setOption('data', {
			actionName: 'count',
			getFullQuoteObjectIDs: this._handlers.length > 0,
			objectTypes: $objectTypes
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data === null) {
			return;
		}
		
		if (data.count !== undefined) {
			var $fullQuoteObjectIDs = (data.fullQuoteObjectIDs !== undefined) ? data.fullQuoteObjectIDs : { };
			this.updateCount(data.count, $fullQuoteObjectIDs);
		}
		
		if (data.template !== undefined) {
			if ($.trim(data.template) == '') {
				this.updateCount(0, { });
			}
			else {
				this.renderDialog(data.template);
			}
		}
	}
});

/**
 * Namespace for message sharing related classes.
 */
WCF.Message.Share = { };

/**
 * Displays a dialog overlay for permalinks.
 */
WCF.Message.Share.Content = Class.extend({
	/**
	 * list of cached templates
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Initializes the WCF.Message.Share.Content class.
	 */
	init: function() {
		this._cache = { };
		this._dialog = null;
		
		this._initLinks();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.Share.Content', $.proxy(this._initLinks, this));
	},
	
	/**
	 * Initializes share links.
	 */
	_initLinks: function() {
		$('a.jsButtonShare').removeClass('jsButtonShare').click($.proxy(this._click, this));
	},
	
	/**
	 * Displays links to share this content.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		event.preventDefault();
		
		var $target = $(event.currentTarget);
		var $link = $target.prop('href');
		var $title = ($target.data('linkTitle') ? $target.data('linkTitle') : $link);
		var $key = $link.hashCode();
		if (this._cache[$key] === undefined) {
			// remove dialog contents
			var $dialogInitialized = false;
			if (this._dialog === null) {
				this._dialog = $('<div />').hide().appendTo(document.body);
				$dialogInitialized = true;
			}
			else {
				this._dialog.empty();
			}
			
			// permalink (plain text)
			var $fieldset = $('<fieldset><legend><label for="__sharePermalink">' + WCF.Language.get('wcf.message.share.permalink') + '</label></legend></fieldset>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalink" class="long" readonly="readonly" />').attr('value', $link).appendTo($fieldset);
			
			// permalink (BBCode)
			var $fieldset = $('<fieldset><legend><label for="__sharePermalinkBBCode">' + WCF.Language.get('wcf.message.share.permalink.bbcode') + '</label></legend></fieldset>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalinkBBCode" class="long" readonly="readonly" />').attr('value', '[url=\'' + $link + '\']' + $title + '[/url]').appendTo($fieldset);
			
			// permalink (HTML)
			var $fieldset = $('<fieldset><legend><label for="__sharePermalinkHTML">' + WCF.Language.get('wcf.message.share.permalink.html') + '</label></legend></fieldset>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalinkHTML" class="long" readonly="readonly" />').attr('value', '<a href="' + $link + '">' + WCF.String.escapeHTML($title) + '</a>').appendTo($fieldset);
			
			this._cache[$key] = this._dialog.html();
			
			if ($dialogInitialized) {
				this._dialog.wcfDialog({
					title: WCF.Language.get('wcf.message.share')
				});
			}
			else {
				this._dialog.wcfDialog('open');
			}
		}
		else {
			this._dialog.html(this._cache[$key]).wcfDialog('open');
		}
		
		this._enableSelection();
	},
	
	/**
	 * Enables text selection.
	 */
	_enableSelection: function() {
		var $inputElements = this._dialog.find('input').click(function() { $(this).select(); });
		
		// Safari on iOS can only select the text if it is not readonly and setSelectionRange() is used
		if (navigator.userAgent.match(/iP(ad|hone|od)/)) {
			$inputElements.keydown(function() { return false; }).removeAttr('readonly').click(function() { this.setSelectionRange(0, 9999); });
		}
	}
});

/**
 * Provides buttons to share a page through multiple social community sites.
 * 
 * @param	boolean		fetchObjectCount
 */
WCF.Message.Share.Page = Class.extend({
	/**
	 * list of share buttons
	 * @var	object
	 */
	_ui: { },
	
	/**
	 * page description
	 * @var	string
	 */
	_pageDescription: '',
	
	/**
	 * canonical page URL
	 * @var	string
	 */
	_pageURL: '',
	
	/**
	 * Initializes the WCF.Message.Share.Page class.
	 * 
	 * @param	boolean		fetchObjectCount
	 */
	init: function(fetchObjectCount) {
		this._pageDescription = encodeURIComponent($('meta[property="og:title"]').prop('content'));
		this._pageURL = encodeURIComponent($('meta[property="og:url"]').prop('content'));
		
		var $container = $('.messageShareButtons');
		this._ui = {
			facebook: $container.find('.jsShareFacebook'),
			google: $container.find('.jsShareGoogle'),
			reddit: $container.find('.jsShareReddit'),
			twitter: $container.find('.jsShareTwitter')
		};
		
		this._ui.facebook.children('a').click($.proxy(this._shareFacebook, this));
		this._ui.google.children('a').click($.proxy(this._shareGoogle, this));
		this._ui.reddit.children('a').click($.proxy(this._shareReddit, this));
		this._ui.twitter.children('a').click($.proxy(this._shareTwitter, this));
		
		if (fetchObjectCount === true) {
			this._fetchFacebook();
			this._fetchTwitter();
			this._fetchReddit();
		}
	},
	
	/**
	 * Shares current page to selected social community site.
	 * 
	 * @param	string		objectName
	 * @param	string		url
	 * @param	boolean		appendURL
	 */
	_share: function(objectName, url, appendURL) {
		window.open(url.replace(/{pageURL}/, this._pageURL).replace(/{text}/, this._pageDescription + (appendURL ? " " + this._pageURL : "")), objectName, 'height=600,width=600');
	},
	
	/**
	 * Shares current page with Facebook.
	 */
	_shareFacebook: function() {
		this._share('facebook', 'https://www.facebook.com/sharer.php?u={pageURL}&t={text}', true);
	},
	
	/**
	 * Shares current page with Google Plus.
	 */
	_shareGoogle: function() {
		this._share('google', 'https://plus.google.com/share?url={pageURL}', true);
	},
	
	/**
	 * Shares current page with Reddit.
	 */
	_shareReddit: function() {
		this._share('reddit', 'https://ssl.reddit.com/submit?url={pageURL}', true);
	},
	
	/**
	 * Shares current page with Twitter.
	 */
	_shareTwitter: function() {
		this._share('twitter', 'https://twitter.com/share?url={pageURL}&text={text}', false);
	},
	
	/**
	 * Fetches share count from a social community site.
	 * 
	 * @param	string		url
	 * @param	object		callback
	 * @param	string		callbackName
	 */
	_fetchCount: function(url, callback, callbackName) {
		var $options = {
			autoSend: true,
			dataType: 'jsonp',
			showLoadingOverlay: false,
			success: callback,
			suppressErrors: true,
			type: 'GET',
			url: url.replace(/{pageURL}/, this._pageURL)
		};
		if (callbackName) {
			$options.jsonp = callbackName;
		}
		
		new WCF.Action.Proxy($options);
	},
	
	/**
	 * Fetches number of Facebook shares.
	 */
	_fetchFacebook: function() {
		this._fetchCount('https://graph.facebook.com/?id={pageURL}', $.proxy(function(data) {
			if (data.shares) {
				this._ui.facebook.children('span.badge').show().text(data.shares);
			}
		}, this));
	},
	
	/**
	 * Fetches tweet count from Twitter.
	 */
	_fetchTwitter: function() {
		if (window.location.protocol.match(/^https/)) return;
		
		this._fetchCount('http://urls.api.twitter.com/1/urls/count.json?url={pageURL}', $.proxy(function(data) {
			if (data.count) {
				this._ui.twitter.children('span.badge').show().text(data.count);
			}
		}, this));
	},
	
	/**
	 * Fetches cumulative vote sum from Reddit.
	 */
	_fetchReddit: function() {
		if (window.location.protocol.match(/^https/)) return;
		
		this._fetchCount('http://www.reddit.com/api/info.json?url={pageURL}', $.proxy(function(data) {
			if (data.data.children.length) {
				this._ui.reddit.children('span.badge').show().text(data.data.children[0].data.score);
			}
		}, this), 'jsonp');
	}
});

/**
 * Handles user mention suggestions in Redactor instances.
 * 
 * Important: Objects of this class have to be created before Redactor
 * is initialized!
 */
WCF.Message.UserMention = Class.extend({
	/**
	 * current caret position
	 * @var	DOMRange
	 */
	_caretPosition: null,
	
	/**
	 * name of the class used to get the user suggestions
	 * @var	string
	 */
	_className: 'wcf\\data\\user\\UserAction',
	
	/**
	 * dropdown object
	 * @var	jQuery
	 */
	_dropdown: null,
	
	/**
	 * dropdown menu object
	 * @var	jQuery
	 */
	_dropdownMenu: null,
	
	/**
	 * suggestion item index, -1 if none is selected
	 * @var	integer
	 */
	_itemIndex: -1,
	
	/**
	 * line height
	 * @var	integer
	 */
	_lineHeight: null,
	
	/**
	 * current beginning of the mentioning
	 * @var	string
	 */
	_mentionStart: '',
	
	/**
	 * redactor instance object
	 * @var	$.Redactor
	 */
	_redactor: null,
	
	/**
	 * Initalizes user suggestions for Redactor with the given textarea id.
	 * 
	 * @param	string		wysiwygSelector
	 */
	init: function(wysiwygSelector) {
		this._textarea = $('#' + wysiwygSelector);
		this._redactor = this._textarea.redactor('getObject');
		
		this._redactor.setOption('keyupCallback', $.proxy(this._keyup, this));
		this._redactor.setOption('wkeydownCallback', $.proxy(this._keydown, this));
		
		this._dropdown = this._textarea.redactor('getEditor');
		this._dropdownMenu = $('<ul class="dropdownMenu userSuggestionList" />').appendTo(this._textarea.parent());
		WCF.Dropdown.initDropdownFragment(this._dropdown, this._dropdownMenu);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Clears the suggestion list.
	 */
	_clearList: function() {
		this._hideList();
		
		this._dropdownMenu.empty();
	},
	
	/**
	 * Handles a click on a list item suggesting a username.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		// restore caret position
		this._redactor.replaceRangesWith(this._caretPosition);
		
		this._setUsername($(event.currentTarget).data('username'));
	},
	
	/**
	 * Creates an item in the suggestion list with the given data.
	 * 
	 * @return	object
	 */
	_createListItem: function(listItemData) {
		var $listItem = $('<li />').data('username', listItemData.label).click($.proxy(this._click, this)).appendTo(this._dropdownMenu);
		
		var $box16 = $('<div />').addClass('box16').appendTo($listItem);
		$box16.append($(listItemData.icon).addClass('framed'));
		$box16.append($('<div />').append($('<span />').text(listItemData.label)));
	},
	
	/**
	 * Returns the offsets used to set the position of the user suggestion
	 * dropdown.
	 * 
	 * @return	object
	 */
	_getDropdownMenuPosition: function() {
		var $orgRange = getSelection().getRangeAt(0).cloneRange();
		
		// mark the entire text, starting from the '@' to the current cursor position
		var $newRange = document.createRange();
		$newRange.setStart($orgRange.startContainer, $orgRange.startOffset - (this._mentionStart.length + 1));
		$newRange.setEnd($orgRange.startContainer, $orgRange.startOffset);
		
		this._redactor.replaceRangesWith($newRange);
		
		// get the offsets of the bounding box of current text selection
		var $range = getSelection().getRangeAt(0);
		var $rect = $range.getBoundingClientRect();
		var $window = $(window);
		var $offsets = {
			top: Math.round($rect.bottom) + $window.scrollTop(),
			left: Math.round($rect.left) + $window.scrollLeft()
		};
		
		if (this._lineHeight === null) {
			this._lineHeight = Math.round($rect.bottom - $rect.top);
		}
		
		// restore caret position
		this._redactor.replaceRangesWith($orgRange);
		this._caretPosition = $orgRange;
		
		return $offsets;
	},
	
	/**
	 * Replaces the started mentioning with a chosen username.
	 */
	_setUsername: function(username) {
		var $orgRange = getSelection().getRangeAt(0).cloneRange();
		
		// allow redactor to undo this
		this._redactor.bufferSet();
		
		var $newRange = document.createRange();
		$newRange.setStart($orgRange.startContainer, $orgRange.startOffset - (this._mentionStart.length + 1));
		$newRange.setEnd($orgRange.startContainer, $orgRange.startOffset);
		
		this._redactor.replaceRangesWith($newRange);
		
		var $range = getSelection().getRangeAt(0);
		$range.deleteContents();
		$range.collapse(true);
		
		// insert username
		if (username.indexOf("'") !== -1) {
			username = username.replace(/'/g, "''");
			username = "'" + username + "'";
		}
		else if (username.indexOf(' ') !== -1) {
			username = "'" + username + "'";
		}
		
		// use native API to prevent issues in Internet Explorer
		var $text = document.createTextNode('@' + username);
		$range.insertNode($text);
		
		var $newRange = document.createRange();
		$newRange.setStart($text, username.length + 1);
		$newRange.setEnd($text, username.length + 1);
		
		this._redactor.replaceRangesWith($newRange);
		
		this._hideList();
	},
	
	/**
	 * Returns the parameters for the AJAX request.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return {
			data: {
				includeUserGroups: false,
				searchString: this._mentionStart
			}
		};
	},
	
	/**
	 * Returns the relevant text in front of the caret in the current line.
	 * 
	 * @return	string
	 */
	_getTextLineInFrontOfCaret: function() {
		// if text is marked, user suggestions are disabled
		if (this._redactor.getSelectionHtml().length) {
			return '';
		}
		
		var $range = this._redactor.getSelection().getRangeAt(0);
		var $text = $range.startContainer.textContent.substr(0, $range.startOffset);
		
		// remove unicode zero width space and non-breaking space
		var $textBackup = $text;
		$text = '';
		for (var $i = 0; $i < $textBackup.length; $i++) {
			var $byte = $textBackup.charCodeAt($i).toString(16);
			if ($byte != '200b' && !/\s/.test($textBackup[$i])) {
				if ($textBackup[$i] === '@' && $i && /\s/.test($textBackup[$i - 1])) {
					$text = '';
				}
				
				$text += $textBackup[$i];
			}
			else {
				$text = '';
			}
		}
		
		return $text;
	},
	
	/**
	 * Hides the suggestion list.
	 */
	_hideList: function() {
		this._dropdown.removeClass('dropdownOpen');
		this._dropdownMenu.removeClass('dropdownOpen');
		
		this._itemIndex = -1;
	},
	
	/**
	 * Handles the keydown event to check if the user starts mentioning someone.
	 * 
	 * @param	object		event
	 */
	_keydown: function(event) {
		if (this._redactor.inPlainMode()) {
			return true;
		}
		
		if (this._dropdownMenu.is(':visible')) {
			switch (event.which) {
				case $.ui.keyCode.ENTER:
					event.preventDefault();
					
					this._dropdownMenu.children('li').eq(this._itemIndex).trigger('click');
					
					return false;
				break;
				
				case $.ui.keyCode.UP:
					event.preventDefault();
					
					this._selectItem(this._itemIndex - 1);
					
					return false;
				break;
				
				case $.ui.keyCode.DOWN:
					event.preventDefault();
					
					this._selectItem(this._itemIndex + 1);
					
					return false;
				break;
			}
		}
		
		return true;
	},
	
	/**
	 * Handles the keyup event to check if the user starts mentioning someone.
	 * 
	 * @param	object		event
	 */
	_keyup: function(event) {
		if (this._redactor.inPlainMode()) {
			return true;
		}
		
		// ignore enter key up event
		if (event.which === $.ui.keyCode.ENTER) {
			return;
		}
		
		// ignore event if suggestion list and user pressed enter, arrow up or arrow down
		if (this._dropdownMenu.is(':visible') && event.which in { 13:1, 38:1, 40:1 }) {
			return;
		}
		
		var $currentText = this._getTextLineInFrontOfCaret();
		if ($currentText) {
			var $match = $currentText.match(/@([^,]{3,})$/);
			if ($match) {
				// if mentioning is at text begin or there's a whitespace character
				// before the '@', everything is fine
				if (!$match.index || $currentText[$match.index - 1].match(/\s/)) {
					this._mentionStart = $match[1];
					
					this._proxy.setOption('data', {
						actionName: 'getSearchResultList',
						className: this._className,
						interfaceName: 'wcf\\data\\ISearchAction',
						parameters: this._getParameters()
					});
					this._proxy.sendRequest();
				}
			}
			else {
				this._hideList();
			}
		}
		else {
			this._hideList();
		}
	},
	
	/**
	 * Selects the suggestion with the given item index.
	 * 
	 * @param	integer		itemIndex
	 */
	_selectItem: function(itemIndex) {
		var $li = this._dropdownMenu.children('li');
		
		if (itemIndex < 0) {
			itemIndex = $li.length - 1;
		}
		else if (itemIndex + 1 > $li.length) {
			itemIndex = 0;
		}
		
		$li.removeClass('dropdownNavigationItem');
		$li.eq(itemIndex).addClass('dropdownNavigationItem');
		
		this._itemIndex = itemIndex;
	},
	
	/**
	 * Shows the suggestion list.
	 */
	_showList: function() {
		this._dropdown.addClass('dropdownOpen');
		this._dropdownMenu.addClass('dropdownOpen');
	},
	
	/**
	 * Evalutes user suggestion-AJAX request results.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._clearList(false);
		
		if ($.getLength(data.returnValues)) {
			for (var $i in data.returnValues) {
				var $item = data.returnValues[$i];
				this._createListItem($item);
			}
			
			this._updateSuggestionListPosition();
			this._showList();
		}
	},
	
	/**
	 * Updates the position of the suggestion list.
	 */
	_updateSuggestionListPosition: function() {
		try {
			var $dropdownMenuPosition = this._getDropdownMenuPosition();
			$dropdownMenuPosition.top += 5; // add a little vertical gap
			
			this._dropdownMenu.css($dropdownMenuPosition);
			this._selectItem(0);
			
			if ($dropdownMenuPosition.top + this._dropdownMenu.outerHeight() + 10 > $(window).height() + $(document).scrollTop()) {
				this._dropdownMenu.addClass('dropdownArrowBottom');
				
				this._dropdownMenu.css({
					top: $dropdownMenuPosition.top - this._dropdownMenu.outerHeight() - 2 * this._lineHeight + 5
				});
			}
			else {
				this._dropdownMenu.removeClass('dropdownArrowBottom');
			}
		}
		catch (e) {
			// ignore errors that are caused by pressing enter to
			// often in a short period of time
		}
	}
});

/**
 * Provides a specialized tab menu used for message options, integrates better into the editor.
 */
$.widget('wcf.messageTabMenu', {
	/**
	 * list of existing tabs and their containers
	 * @var	array<object>
	 */
	_tabs: [ ],
	
	/**
	 * list of tab names and their corresponding index
	 * @var	object<string>
	 */
	_tabsByName: { },
	
	/**
	 * widget options
	 * @var	object<mixed>
	 */
	options: {
		collapsible: true
	},
	
	/**
	 * Creates the message tab menu.
	 */
	_create: function() {
		var $tabs = this.element.find('> nav > ul > li');
		var $tabContainers = this.element.find('> div, > fieldset');
		
		if ($tabs.length != $tabContainers.length) {
			console.debug("[wcf.messageTabMenu] Amount of tabs does not equal amount of tab containers, aborting.");
			return;
		}
		
		var $preselect = this.element.data('preselect');
		this._tabs = [ ];
		this._tabsByName = { };
		for (var $i = 0; $i < $tabs.length; $i++) {
			var $tab = $($tabs[$i]);
			var $tabContainer = $($tabContainers[$i]);
			
			var $name = $tab.data('name');
			if ($name === undefined) {
				var $href = $tab.children('a').prop('href');
				if ($href !== undefined) {
					if ($href.match(/#([a-zA-Z_-]+)$/)) {
						$name = RegExp.$1;
					}
				}
				
				if ($name === undefined) {
					$name = $tab.wcfIdentify();
					console.debug("[wcf.messageTabMenu] Missing name attribute, assuming generic ID '" + $name + "'");
				}
			}
			
			this._tabs.push({
				container: $tabContainer,
				name: $name,
				tab: $tab
			});
			this._tabsByName[$name] = $i;
			
			var $anchor = $tab.children('a').data('index', $i).click($.proxy(this._showTab, this));
			if ($preselect == $name) {
				$anchor.trigger('click');
			}
		}
		
		if ($preselect === true && this._tabs.length) {
			// pick the first available tab
			this._tabs[0].tab.children('a').trigger('click');
		}
		
		var $collapsible = this.element.data('collapsible');
		if ($collapsible !== undefined) {
			this.options.collapsible = $collapsible;
		}
	},
	
	/**
	 * Destroys the message tab menu.
	 */
	destroy: function() {
		$.Widget.prototype.destroy.apply(this, arguments);
		
		this.element.remove();
	},
	
	/**
	 * Shows a tab or collapses it if already open.
	 * 
	 * @param	object		event
	 * @param	integer		index
	 * @param	boolean		forceOpen
	 */
	_showTab: function(event, index, forceOpen) {
		var $index = (event === null) ? index : $(event.currentTarget).data('index');
		forceOpen = (!this.options.collapsible || forceOpen === true) ? true : false;
		
		var $target = null;
		for (var $i = 0; $i < this._tabs.length; $i++) {
			var $current = this._tabs[$i];
			
			if ($i == $index) {
				if (!$current.tab.hasClass('active')) {
					$current.tab.addClass('active');
					$current.container.addClass('active');
					$target = $current;
					
					continue;
				}
				else if (forceOpen === true) {
					continue;
				}
			}
			
			$current.tab.removeClass('active');
			$current.container.removeClass('active');
		}
		
		if (event !== null) {
			event.preventDefault();
			event.stopPropagation();
		}
		
		if ($target !== null) {
			this._trigger('show', { }, {
				activeTab: $target
			});
		}
	},
	
	/**
	 * Toggle a specific tab by either index or name property.
	 * 
	 * @param	mixed		index
	 * @param	boolean		forceOpen
	 */
	showTab: function(index, forceOpen) {
		if (!$.isNumeric(index)) {
			if (this._tabsByName[index] !== undefined) {
				index = this._tabsByName[index];
			}
		}
		
		if (this._tabs[index] === undefined) {
			console.debug("[wcf.messageTabMenu] Cannot locate tab identified by '" + index + "'");
			return;
		}
		
		this._showTab(null, index, forceOpen);
	},
	
	/**
	 * Returns a tab by it's unique name.
	 * 
	 * @param	string		name
	 * @return	jQuery
	 */
	getTab: function(name) {
		if (this._tabsByName[name] !== undefined) {
			return this._tabs[this._tabsByName[name]].tab;
		}
		
		return null;
	},
	
	/**
	 * Returns a tab container by it's tab's unique name.
	 * 
	 * @param	string		name
	 * @return	jQuery
	 */
	getContainer: function(name) {
		if (this._tabsByName[name] !== undefined) {
			return this._tabs[this._tabsByName[name]].container;
		}
		
		return null;
	}
});
