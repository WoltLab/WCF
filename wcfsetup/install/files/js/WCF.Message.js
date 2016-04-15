"use strict";

/**
 * Message related classes for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
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
			
			$('<span class="icon icon16 fa-files-o pointer jsTooltip" title="' + WCF.Language.get('wcf.message.bbcode.code.copy') + '" />').appendTo($codeBox.find('div > h3')).click($.proxy(this._click, this));
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
			var newID = parseInt($(this).val());
			if ($(this).val() === 'current') newID = Infinity;
			
			self._oldIDInputs.each(function(event) {
				var oldID = parseInt($(this).val());
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
				var newID = parseInt($(this).val());
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
		this._textarea = $('#' + this._messageFieldID);
		if (!this._textarea.length) {
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
		$('#settings_' + this._messageFieldID).find('input[type=checkbox]').each(function(index, checkbox) {
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
			return $.trim(this._textarea.val());
		}
		else if (this._textarea.data('redactor')) {
			return this._textarea.redactor('wutil.getText');
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
		this._textarea.parent().children('small.innerError').remove();
		
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
		
		var $innerError = this._textarea.next('small.innerError').empty();
		if (!$innerError.length) {
			$innerError = $('<small class="innerError" />').appendTo(this._textarea.parent());
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
			$preview = $('<section class="section" id="previewContainer"><h2 class="sectionTitle">' + WCF.Language.get('wcf.global.preview') + '</h2><div class="messageTextPreview"></div></section>').prependTo($('#messageContainer')).wcfFadeIn();
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
	},
	
	/**
	 * Handles tab menu clicks.
	 * 
	 * @param	{Event} 	event
	 * @param	{Object}	data
	 */
	_click: function(event, data) {
		event.preventDefault();
		
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
			return;
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
 * 
 * @param	string		wysiwygSelector
 */
WCF.Message.Smilies = Class.extend({
	/**
	 * redactor element
	 * @var	$.Redactor
	 */
	_redactor: null,
	
	/**
	 * wysiwyg container id
	 * @var	string
	 */
	_wysiwygSelector: '',
	
	/**
	 * Initializes the smiley handler.
	 * 
	 * @param	{string}	wysiwygSelector
	 */
	init: function(wysiwygSelector) {
		this._wysiwygSelector = wysiwygSelector;
		
		WCF.System.Dependency.Manager.register('Redactor_' + this._wysiwygSelector, $.proxy(function() {
			this._redactor = $('#' + this._wysiwygSelector).redactor('core.getObject');
			
			$('.messageTabMenu[data-wysiwyg-container-id=' + this._wysiwygSelector + ']').on('click', '.jsSmiley', $.proxy(this._smileyClick, this));
		}, this));
	},
	
	/**
	 * Handles tab smiley clicks.
	 * 
	 * @param	{Event}		event
	 */
	_smileyClick: function(event) {
		event.preventDefault();
		
		var $target = $(event.currentTarget);
		var $smileyCode = $target.data('smileyCode');
		var $smileyPath = $target.data('smileyPath');
		
		// register smiley
		this._redactor.wbbcode.insertSmiley($smileyCode, $smileyPath, true);
	}
});

/**
 * Provides an inline message editor.
 * 
 * @deprecated	2.2 - please use `WoltLab/WCF/Ui/Message/InlineEditor` instead
 * 
 * @param	integer		containerID
 */
WCF.Message.InlineEditor = Class.extend({
	/**
	 * list of messages
	 * @var	object
	 */
	_container: { },
	
	/**
	 * container id
	 * @var	int
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
	 * Initializes a new WCF.Message.InlineEditor object.
	 * 
	 * @param	integer				containerID
	 * @param	boolean				supportExtendedForm
	 * @param	WCF.Message.Quote.Manager	quoteManager
	 */
	init: function(containerID, supportExtendedForm, quoteManager) {
		require(['WoltLab/WCF/Ui/Message/InlineEditor'], (function(UiMessageInlineEditor) {
			UiMessageInlineEditor.init({
				extendedForm: (supportExtendedForm === true),
				
				className: this._getClassName(),
				containerId: containerID,
				editorPrefix: this._messageEditorIDPrefix,
				
				messageSelector: this._messageContainerSelector,
				
				callbackDropdownInit: this._callbackDropdownInit.bind(this)
			});
		}).bind(this));
	},
	
	/**
	 * Loads WYSIWYG editor for selected message.
	 * 
	 * @param	object		event
	 * @param	integer		containerID
	 * @return	boolean
	 */
	_click: function(event, containerID) {
		containerID = (event === null) ? ~~containerID : ~~elData(event.currentTarget, 'container-id');
		
		require(['WoltLab/WCF/Ui/Message/InlineEditor'], (function(UiMessageInlineEditor) {
			UiMessageInlineEditor.legacyEdit(containerID);
		}).bind(this));
		
		if (event) {
			event.preventDefault();
		}
	},
	
	/**
	 * Initializes the inline edit dropdown menu.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		dropdownMenu
	 */
	_initDropdownMenu: function(containerID, dropdownMenu) { },
	
	_callbackDropdownInit: function(element, dropdownMenu) {
		this._initDropdownMenu($(element).wcfIdentify(), $(dropdownMenu));
		
		return null;
	},
	
	/**
	 * Returns message action class name.
	 * 
	 * @return	string
	 */
	_getClassName: function() {
		return '';
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
	 * @param	boolean				supportDirectInsert
	 */
	init: function(quoteManager, className, objectType, containerSelector, messageBodySelector, messageContentSelector, supportDirectInsert) {
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
		
		supportDirectInsert = (supportDirectInsert && quoteManager.supportPaste()) ? true : false;
		this._initCopyQuote(supportDirectInsert);
		
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
		else {
			// check if mousedown occured inside a <blockquote>
			var $element = event.target;
			while ($element !== $container[0]) {
				if ($element.tagName === 'BLOCKQUOTE') {
					this._activeContainerID = '';
					
					return;
				}
				
				$element = $element.parentElement;
			}
		}
		
		this._activeContainerID = $container.wcfIdentify();
		
		// remove alt-tag from all images, fixes quoting in Firefox
		if ($.browser.mozilla) {
			// TODO: is this still required?
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
		// work-around for IE, see http://stackoverflow.com/a/5983176
		var $nodeFilter = function(node) {
			switch (node.tagName) {
				case 'BLOCKQUOTE':
				case 'H3':
				case 'SCRIPT':
					return NodeFilter.FILTER_REJECT;
				break;
				
				default:
					return NodeFilter.FILTER_ACCEPT;
				break;
			}
		};
		$nodeFilter.acceptNode = $nodeFilter;
		
		var $walker = document.createTreeWalker(
			node,
			NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT,
			$nodeFilter,
			true
		);
		
		var $text = '';
		while ($walker.nextNode()) {
			var $node = $walker.currentNode;
			
			if ($node.nodeType === Node.ELEMENT_NODE) {
				switch ($node.tagName) {
					case 'BR':
					case 'LI':
					case 'UL':
						$text += "\n";
					break;
					
					case 'TD':
						if (!$.browser.msie) {
							$text += "\n";
						}
					break;
				}
			}
			else {
				$text += $node.nodeValue.replace(/\n/g, '');
			}
			
		}
		
		return $text;
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
		
		var $messageBody = (this._messageBodySelector) ? $container.find(this._messageContentSelector)[0] : $container[0];
		
		// check if mouseup occured within a <blockquote>
		var $element = event.target;
		while ($element !== $container[0]) {
			if ($element === null || $element.tagName === 'BLOCKQUOTE') {
				this._copyQuote.hide();
				
				return;
			}
			
			$element = $element.parentElement;
		}
		
		// check if selection starts and ends within the $messageBody element
		var $range = window.getSelection().getRangeAt(0);
		if (!this._elementInsideContainer($range.startContainer, $messageBody) || !this._elementInsideContainer($range.endContainer, $messageBody)) {
			this._copyQuote.hide();
			
			return;
		}
		
		// compare selection with message text of given container
		var $messageText = this._getNodeText($messageBody);
		
		// selected text is not part of $messageText or contains text from unrelated nodes
		if (this._normalize($messageText).indexOf(this._normalize($text)) === -1) {
			return;
		}
		this._copyQuote.show();
		
		var $coordinates = this._getBoundingRectangle($container, window.getSelection());
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
					// TODO: is this still required?
					$container.find('img').each(function() {
						var $image = $(this);
						$image.attr('alt', $image.data('__alt'));
					});
				}
			}
		}, 10);
	},
	
	/**
	 * Returns true if given element is a child element of given container element.
	 * 
	 * @param	Node		element
	 * @param	Element		container
	 * @return	boolean
	 */
	_elementInsideContainer: function(element, container) {
		if (element.nodeType === Node.TEXT_NODE) element = element.parentNode;
		
		while (element) {
			if (element === container) {
				return true;
			}
			
			element = element.parentNode;
		}
		
		return false;
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
				var $rect = selection.getRangeAt(0).getBoundingClientRect();
				
				$coordinates = {
					left: $rect.left,
					right: $rect.right,
					top: $rect.top + $(document).scrollTop()
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
				if (node.nodeType == Node.TEXT_NODE) {
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
	 * 
	 * @param	boolean		supportDirectInsert
	 */
	_initCopyQuote: function(supportDirectInsert) {
		this._copyQuote = $('#quoteManagerCopy');
		if (!this._copyQuote.length) {
			this._copyQuote = $('<div id="quoteManagerCopy" class="balloonTooltip"><span class="jsQuoteManagerStore">' + WCF.Language.get('wcf.message.quote.quoteSelected') + '</span><span class="pointer"><span></span></span></div>').hide().appendTo(document.body);
			var $storeQuote = this._copyQuote.children('span.jsQuoteManagerStore').click($.proxy(this._saveQuote, this));
			if (supportDirectInsert) {
				$('<span class="jsQuoteManagerQuoteAndInsert">' + WCF.Language.get('wcf.message.quote.quoteAndReply') + '</span>').click($.proxy(this._saveAndInsertQuote, this)).insertAfter($storeQuote);
			}
		}
	},
	
	/**
	 * Returns the text selection.
	 * 
	 * @return	string
	 */
	_getSelectedText: function() {
		var $selection = window.getSelection();
		if ($selection.rangeCount) {
			return this._getNodeText($selection.getRangeAt(0).cloneContents());
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
		
		// close navigation on mobile
		var $navigationList = $listItem.parents('.buttonGroupNavigation');
		if ($navigationList.hasClass('jsMobileButtonGroupNavigation')) {
			$navigationList.children('.dropdownLabel').trigger('click');
		}
		
		// discard event
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Saves a quote.
	 * 
	 * @param	boolean		renderQuote
	 */
	_saveQuote: function(renderQuote) {
		renderQuote = (renderQuote === true) ? true : false;
		
		this._proxy.setOption('data', {
			actionName: 'saveQuote',
			className: this._className,
			interfaceName: 'wcf\\data\\IMessageQuoteAction',
			objectIDs: [ this._objectID ],
			parameters: {
				message: this._message,
				renderQuote: renderQuote
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Saves a quote and directly inserts it.
	 */
	_saveAndInsertQuote: function() {
		this._saveQuote(true);
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
			if (data.returnValues.fullQuoteMessageIDs !== undefined) {
				data.returnValues.fullQuoteObjectIDs = data.returnValues.fullQuoteMessageIDs;
			}
			
			var $fullQuoteObjectIDs = (data.returnValues.fullQuoteObjectIDs !== undefined) ? data.returnValues.fullQuoteObjectIDs : { };
			this._quoteManager.updateCount(data.returnValues.count, $fullQuoteObjectIDs);
		}
		
		switch (data.actionName) {
			case 'saveQuote':
			case 'saveFullQuote':
				if (data.returnValues.renderedQuote) {
					WCF.System.Event.fireEvent('com.woltlab.wcf.message.quote', 'insert', {
						forceInsert: (data.actionName === 'saveQuote' ? true : false),
						quote: data.returnValues.renderedQuote
					});
				}
			break;
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
			url: 'index.php/MessageQuote/?t=' + SECURITY_TOKEN
		});
		
		this._toggleShowQuotes();
		
		WCF.System.Event.addListener('com.woltlab.wcf.message.quote', 'insert', (function(data) {
			this._insertQuote(null, undefined, data);
		}).bind(this));
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
			var $objectIDs = fullQuoteObjectIDs[$objectType] || [ ];
			this._handlers[$objectType].updateFullQuoteObjectIDs($objectIDs);
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
	 * @param	object		data
	 */
	_insertQuote: function(event, inputElement, data) {
		var $listItem = null, $quote, $username, $link;
		if (data === undefined) {
			$listItem = (event === null) ? $(inputElement).parents('li') : $(event.currentTarget).parents('li');
			$quote = $.trim($listItem.children('div.jsFullQuote').text());
			var $message = $listItem.parents('article.message');
			$username = $message.attr('data-username');
			$link = $message.data('link');
			
			data = { forceInsert: true };
		}
		else {
			$quote = data.quote.text;
			$username = data.quote.username;
			$link = data.quote.link;
		}
		
		// insert into editor
		if ($.browser.redactor) {
			if (this._editorElementAlternative === null) {
				var insert = true;
				if (event !== null || data !== null) {
					var $api = $('.jsQuickReply:eq(0)').data('__api');
					if ($api && !$api.getContainer().is(':visible')) {
						if (data.forceInsert) {
							this._insertQuotes = false;
							$api.click(null);
						}
						else {
							insert = false;
						}
					}
				}
				
				if (insert && this._editorElement.length) this._editorElement.redactor('wbbcode.insertQuoteBBCode', $username, $link, $quote, $quote);
			}
			else {
				this._editorElementAlternative.redactor('wbbcode.insertQuoteBBCode', $username, $link, $quote, $quote);
			}
		}
		else {
			// build quote tag
			$quote = "[quote='" + $username + "','" + $link + "']" + $quote + "[/quote]";
			
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
		if ($listItem !== null) this._removeOnSubmit.push($listItem.attr('data-quote-id'));
		
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
			getFullQuoteObjectIDs: ($objectTypes.length > 0),
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
	},
	
	/**
	 * Returns true if pasting is supported.
	 * 
	 * @return	boolean
	 */
	supportPaste: function() {
		return this._supportPaste;
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
			var $fieldset = $('<section class="section"><h2 class="sectionTitle"><label for="__sharePermalink">' + WCF.Language.get('wcf.message.share.permalink') + '</label></h2></section>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalink" class="long" readonly="readonly" />').attr('value', $link).appendTo($fieldset);
			
			// permalink (BBCode)
			var $fieldset = $('<section class="section"><h2 class="sectionTitle"><label for="__sharePermalinkBBCode">' + WCF.Language.get('wcf.message.share.permalink.bbcode') + '</label></h2></section>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalinkBBCode" class="long" readonly="readonly" />').attr('value', '[url=\'' + $link + '\']' + $title + '[/url]').appendTo($fieldset);
			
			// permalink (HTML)
			var $fieldset = $('<section class="section"><h2 class="sectionTitle"><label for="__sharePermalinkHTML">' + WCF.Language.get('wcf.message.share.permalink.html') + '</label></h2></section>').appendTo(this._dialog);
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
 * @param	object		privacySettings
 */
WCF.Message.Share.Page = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * true if share count should be fetched
	 * @var	boolean
	 */
	_fetchObjectCount: false,
	
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
	 * list of privacy settings per social media site
	 */
	_privacySettings: { },
	
	/**
	 * list of provider links and share callback
	 * @var	object<object>
	 */
	_provider: { },
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the WCF.Message.Share.Page class.
	 * 
	 * @param	boolean		fetchObjectCount
	 * @param	object		privacySettings
	 */
	init: function(fetchObjectCount, privacySettings) {
		this._dialog = null;
		this._fetchObjectCount = (fetchObjectCount === true) ? true : false;
		this._pageDescription = encodeURIComponent($('meta[property="og:title"]').prop('content'));
		this._pageURL = encodeURIComponent($('meta[property="og:url"]').prop('content'));
		this._privacySettings = $.extend({
			facebook: false,
			google: false,
			twitter: false,
			reddit: false
		}, privacySettings || { });
		this._proxy = null;
		
		this._initProvider();
	},
	
	/**
	 * Initializes all social media providers.
	 */
	_initProvider: function() {
		var $container = $('.messageShareButtons');
		var self = this;
		this._provider = {
			facebook: {
				fetch: function() { self._fetchFacebook(); },
				link: $container.find('.jsShareFacebook'),
				share: function() { self._share('facebook', 'https://www.facebook.com/sharer.php?u={pageURL}&t={text}', true); }
			},
			google: {
				fetch: undefined,
				link: $container.find('.jsShareGoogle'),
				share: function() { self._share('google', 'https://plus.google.com/share?url={pageURL}', true); }
			},
			reddit: {
				fetch: function() { self._fetchReddit(); },
				link:$container.find('.jsShareReddit'),
				share: function() { self._share('reddit', 'https://ssl.reddit.com/submit?url={pageURL}', true); }
			},
			twitter: {
				fetch: undefined,
				link: $container.find('.jsShareTwitter'),
				share: function() { self._share('twitter', 'https://twitter.com/share?url={pageURL}&text={text}', false); }
			}
		};
		
		$.each(this._provider, function(provider, data) {
			if (self._privacySettings[provider]) {
				if (self._fetchObjectCount && data.fetch) {
					data.fetch();
				}
			}
			else {
				data.link.addClass('disabled');
			}
			
			data.link.data('provider', provider).click($.proxy(self._click, self));
		});
		
		if (WCF.User.userID && !$container.find('.jsShowPrivacySettings').length) {
			var $openSettings = $('<li class="jsShowPrivacySettings"><a><span class="icon icon32 fa-gear jsTooltip" title="' + WCF.Language.get('wcf.message.share.privacy') + '" /></a></li>');
			$openSettings.appendTo($container.children('ul')).children('a').click($.proxy(this._openPrivacySettings, this));
		}
	},
	
	/**
	 * Handles clicks on a social media provider link.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $link = $(event.currentTarget);
		var $provider = $link.data('provider');
		
		if ($link.hasClass('disabled')) {
			if (WCF.User.userID) {
				this._openPrivacySettings();
			}
			else {
				// guest => enable button
				$link.removeClass('disabled');
			}
		}
		else {
			this._provider[$provider].share();
		}
	},
	
	/**
	 * Opens the privacy settings dialog.
	 */
	_openPrivacySettings: function() {
		if (this._proxy === null) {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
		}
		
		this._proxy.setOption('data', {
			actionName: 'getSocialNetworkPrivacySettings',
			className: 'wcf\\data\\user\\UserAction'
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
		switch (data.actionName) {
			case 'getSocialNetworkPrivacySettings':
				this._renderDialog(data);
			break;
			
			case 'saveSocialNetworkPrivacySettings':
				this._updatePrivacySettings(data);
			break;
		}
	},
	
	/**
	 * Renders the settings dialog.
	 * 
	 * @param	object		data
	 */
	_renderDialog: function(data) {
		if (this._dialog === null) {
			this._dialog = $('<div />').hide().appendTo(document.body);
			this._dialog.html(data.returnValues.template);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.message.share.privacy')
			});
		}
		else {
			this._dialog.html(data.returnValues.template);
			this._dialog.wcfDialog('open');
		}
		
		this._dialog.find('input[type=submit]').click($.proxy(this._save, this));
	},
	
	/**
	 * Saves settings.
	 */
	_save: function() {
		this._proxy.setOption('data', {
			actionName: 'saveSocialNetworkPrivacySettings',
			className: 'wcf\\data\\user\\UserAction',
			parameters: {
				facebook: (this._dialog.find('input[name=facebook]').is(':checked')),
				google: (this._dialog.find('input[name=google]').is(':checked')),
				reddit: (this._dialog.find('input[name=reddit]').is(':checked')),
				twitter: (this._dialog.find('input[name=twitter]').is(':checked'))
			}
		});
		this._proxy.sendRequest();
		
		this._dialog.wcfDialog('close');
	},
	
	/**
	 * Updates the internal privacy settings.
	 * 
	 * @param	object		data
	 */
	_updatePrivacySettings: function(data) {
		this._privacySettings = $.extend(this._privacySettings, data.returnValues.settings);
		
		var self = this;
		$.each(data.returnValues.settings, function(provider, status) {
			self._privacySettings[provider] = (status) ? true : false;
			
			if (status) {
				self._provider[provider].link.removeClass('disabled');
				
				if (self._fetchObjectCount && self._provider[provider].fetch) {
					self._provider[provider].fetch();
				}
			}
			else {
				self._provider[provider].link.addClass('disabled');
			}
		});
		
		new WCF.System.Notification().show();
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
	 * Fetches number of Facebook likes.
	 */
	_fetchFacebook: function() {
		this._fetchCount('https://graph.facebook.com/?id={pageURL}', $.proxy(function(data) {
			if (data.shares) {
				this._provider.facebook.link.children('span.badge').show().text(data.shares);
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
				this._provider.reddit.link.children('span.badge').show().text(data.data.children[0].data.score);
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
	 * delay timer to only send requests after user paused typing
	 * @var	WCF.PeriodicalExecuter
	 */
	_timer: null,
	
	/**
	 * Initalizes user suggestions for Redactor with the given textarea id.
	 * 
	 * @param	string		wysiwygSelector
	 */
	init: function(wysiwygSelector) {
		if ($.browser.mobile && $.browser.mozilla) {
			// the desktop Firefox work-arounds do not work on Firefox for Android, in fact they crash it
			return;
		}
		
		this._textarea = $('#' + wysiwygSelector);
		this._redactor = this._textarea.redactor('core.getObject');
		
		this._dropdown = this._textarea.redactor('core.getEditor');
		this._dropdownMenu = $('<ul class="dropdownMenu userSuggestionList" />').appendTo(this._textarea.parent());
		WCF.Dropdown.initDropdownFragment(this._dropdown, this._dropdownMenu);
		
		this._proxy = new WCF.Action.Proxy({
			autoAbortPrevious: true,
			success: $.proxy(this._success, this)
		});
		
		WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keydown_' + wysiwygSelector, $.proxy(this._keydown, this));
		WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keyup_' + wysiwygSelector, $.proxy(this._keyup, this));
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
	 * This function is also called when seleting a suggested username by clicking
	 * enter.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		// in Firefox, this._caretPosition does not have the text node as
		// startContainer anymore when confirming a username suggestion by
		// clicking enter, thus we need to manually adjust it
		if ($.browser.mozilla && this._caretPosition.startContainer.nodeName == 'P') {
			var $textNode = this._caretPosition.startContainer.childNodes[this._caretPosition.startOffset - 1];
			
			this._caretPosition = document.createRange();
			this._caretPosition.selectNodeContents($textNode);
			this._caretPosition.collapse();
		}
		
		// restore caret position
		this._redactor.wutil.replaceRangesWith(this._caretPosition);
		
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
		$box16.append($(listItemData.icon));
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
		
		this._redactor.wutil.replaceRangesWith($newRange);
		
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
		this._redactor.wutil.replaceRangesWith($orgRange);
		this._caretPosition = $orgRange;
		
		return $offsets;
	},
	
	/**
	 * Replaces the started mentioning with a chosen username.
	 */
	_setUsername: function(username) {
		if (this._timer !== null) {
			this._timer.stop();
			this._timer = null;
		}
		this._proxy.abortPrevious();
		
		var $orgRange = getSelection().getRangeAt(0).cloneRange();
		
		// allow redactor to undo this
		this._redactor.buffer.set();
		
		var $startContainer = $orgRange.startContainer;
		var $startOffset = $orgRange.startOffset - (this._mentionStart.length + 1);
		
		// navigating with the keyboard before hitting enter will cause the text node to be split
		if ($startOffset < 0) {
			$startContainer = $startContainer.previousSibling;
			$startOffset = $startContainer.length - (this._mentionStart.length + 1) - ($orgRange.startOffset - 1);
		}
		
		var $newRange = document.createRange();
		$newRange.setStart($startContainer, $startOffset);
		$newRange.setEnd($orgRange.startContainer, $orgRange.startOffset);
		
		this._redactor.wutil.replaceRangesWith($newRange);
		
		var $range = getSelection().getRangeAt(0);
		$range.deleteContents();
		$range.collapse(true);
		
		// insert username
		if (username.indexOf("'") !== -1) {
			username = username.replace(/'/g, "''");
		}
		username = "'" + username + "'";
		
		// use native API to prevent issues in Internet Explorer
		var $text = document.createTextNode('@' + username);
		$range.insertNode($text);
		
		var $newRange = document.createRange();
		$newRange.setStart($text, username.length + 1);
		$newRange.setEnd($text, username.length + 1);
		
		this._redactor.wutil.replaceRangesWith($newRange);
		
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
		if (this._redactor.selection.getHtml().length) {
			return '';
		}
		
		var $range = getSelection().getRangeAt(0);
		
		// in Firefox, blurring and refocusing the browser creates separate
		// text nodes
		if ($.browser.mozilla && $range.startContainer.nodeType == 3) {
			$range.startContainer.parentNode.normalize();
		}
		
		var $text = $range.startContainer.textContent.substr(0, $range.startOffset);
		
		// remove unicode zero width space and non-breaking space
		var $textBackup = $text;
		$text = '';
		var $hadSpace = false;
		for (var $i = 0; $i < $textBackup.length; $i++) {
			var $byte = $textBackup.charCodeAt($i).toString(16);
			if ($byte != '200b' && (!/\s/.test($textBackup[$i]) || (($byte == 'a0' || $byte == '20') && !$hadSpace))) {
				if ($byte == 'a0' || $byte == '20') {
					$hadSpace = true;
				}
				
				if ($textBackup[$i] === '@' && $i && /\s/.test($textBackup[$i - 1])) {
					$hadSpace = false;
					$text = '';
				}
				
				$text += $textBackup[$i];
			}
			else {
				$hadSpace = false;
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
	 * @param	object		data
	 */
	_keydown: function(data) {
		if (this._redactor.wutil.inPlainMode()) {
			return;
		}
		
		if (this._dropdownMenu.is(':visible')) {
			switch (data.event.which) {
				case $.ui.keyCode.ENTER:
					data.event.preventDefault();
					data.cancel = true;
					
					this._dropdownMenu.children('li').eq(this._itemIndex).trigger('click');
				break;
				
				case $.ui.keyCode.UP:
					data.cancel = true;
					data.event.preventDefault();
					
					this._selectItem(this._itemIndex - 1);
				break;
				
				case $.ui.keyCode.DOWN:
					data.cancel = true;
					data.event.preventDefault();
					
					this._selectItem(this._itemIndex + 1);
				break;
			}
		}
	},
	
	/**
	 * Handles the keyup event to check if the user starts mentioning someone.
	 * 
	 * @param	object		data
	 */
	_keyup: function(data) {
		if (this._redactor.wutil.inPlainMode()) {
			return true;
		}
		
		// abort previous search requests
		if (this._timer !== null) {
			this._timer.stop();
			this._timer = null;
		}
		this._proxy.abortPrevious();
		
		// ignore enter key up event
		if (data.event.which === $.ui.keyCode.ENTER) {
			return;
		}
		
		// ignore event if suggestion list and user pressed enter, arrow up or arrow down
		if (this._dropdownMenu.is(':visible') && data.event.which in { 13:1, 38:1, 40:1 }) {
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
					
					if (this._timer !== null) {
						this._timer.stop();
					}
					
					this._timer = new WCF.PeriodicalExecuter($.proxy(function() {
						this._proxy.setOption('data', {
							actionName: 'getSearchResultList',
							className: this._className,
							interfaceName: 'wcf\\data\\ISearchAction',
							parameters: this._getParameters()
						});
						this._proxy.sendRequest();
						
						this._timer.stop();
						this._timer = null;
					}, this), 500);
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
	 * pointer span
	 * @var jQuery
	 */
	_span: null,
	
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
		var $nav = this.element.find('> nav');
		var $tabs = $nav.find('> ul > li:not(.jsFlexibleMenuDropdown)');
		var $tabContainers = this.element.find('> div, > fieldset');
		
		if ($tabs.length != $tabContainers.length) {
			console.debug("[wcf.messageTabMenu] Amount of tabs does not equal amount of tab containers, aborting.");
			return;
		}
		
		// pointer span
		this._span = $('<span />').appendTo($nav);
		
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
		
		this._span.css({
			transform: 'translateX(' + $target.tab[0].offsetLeft + 'px)', 
			width: $target.tab[0].clientWidth + 'px'
		});
		
		$(window).trigger('resize');
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
