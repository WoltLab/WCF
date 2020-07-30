"use strict";

/**
 * Message related classes for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
	 * @deprecated
	 */
	init: function() {
		
	}
});

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Provides the dynamic parts of the edit history interface.
	 */
	WCF.Message.EditHistory = Class.extend({
		/**
		 * jQuery object containing the radio buttons for the oldID
		 * @var        object
		 */
		_oldIDInputs: null,
		
		/**
		 * jQuery object containing the radio buttons for the oldID
		 * @var        object
		 */
		_newIDInputs: null,
		
		/**
		 * selector for the version rows
		 * @var        string
		 */
		_containerSelector: '',
		
		/**
		 * selector for the revert button
		 * @var        string
		 */
		_buttonSelector: '.jsRevertButton',
		
		/**
		 * Initializes the edit history interface.
		 *
		 * @param        object        oldIDInputs
		 * @param        object        newIDInputs
		 * @param        string        containerSelector
		 * @param        string        buttonSelector
		 * @param       {Object}        options
		 */
		init: function (oldIDInputs, newIDInputs, containerSelector, buttonSelector, options) {
			this._oldIDInputs = oldIDInputs;
			this._newIDInputs = newIDInputs;
			this._containerSelector = containerSelector;
			this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsRevertButton';
			this._options = $.extend({
				isVersionTracker: false,
				versionTrackerObjectType: '',
				versionTrackerObjectId: 0,
				redirectUrl: ''
			}, options);
			
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
		_initInputs: function () {
			var self = this;
			this._newIDInputs.change(function (event) {
				var newID = parseInt($(this).val());
				if ($(this).val() === 'current') newID = Infinity;
				
				self._oldIDInputs.each(function (event) {
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
			
			this._oldIDInputs.change(function (event) {
				var oldID = parseInt($(this).val());
				if ($(this).val() === 'current') oldID = Infinity;
				
				self._newIDInputs.each(function (event) {
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
		_initElements: function () {
			var self = this;
			$(this._containerSelector).each(function (index, container) {
				var $container = $(container);
				$container.find(self._buttonSelector).click($.proxy(self._click, self));
			});
		},
		
		/**
		 * Sends AJAX request.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			var $target = $(event.currentTarget);
			event.preventDefault();
			
			if ($target.data('confirmMessage')) {
				var self = this;
				
				WCF.System.Confirmation.show($target.data('confirmMessage'), function (action) {
					if (action === 'cancel') return;
					
					self._sendRequest($target);
				}, undefined, undefined, true);
			}
			else {
				this._sendRequest($target);
			}
		},
		
		
		/**
		 * Sends the request
		 *
		 * @param        jQuery        object
		 */
		_sendRequest: function (object) {
			if (this._options.isVersionTracker) {
				//noinspection JSUnresolvedVariable
				this.proxy.setOption('url', window.WSC_API_URL + 'index.php?ajax-invoke/&t=' + window.SECURITY_TOKEN);
				this.proxy.setOption('data', {
					actionName: 'revert',
					className: 'wcf\\system\\version\\VersionTracker',
					parameters: {
						objectType: this._options.versionTrackerObjectType,
						objectID: this._options.versionTrackerObjectId,
						versionID: $(object).data('objectID')
					}
				});
			}
			else {
				this.proxy.setOption('data', {
					actionName: 'revert',
					className: 'wcf\\data\\edit\\history\\entry\\EditHistoryEntryAction',
					objectIDs: [$(object).data('objectID')]
				});
			}
			
			this.proxy.sendRequest();
		},
		
		/**
		 * Reloads the page to show the new versions.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        object                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			if (this._options.redirectUrl) {
				new WCF.System.Notification().show((function () {
					window.location = this._options.redirectUrl;
				}).bind(this));
			}
			else {
				window.location.reload(true);
			}
		}
	});
}
else {
	WCF.Message.EditHistory = Class.extend({
		_oldIDInputs: {},
		_newIDInputs: {},
		_containerSelector: "",
		_buttonSelector: "",
		init: function() {},
		_initInputs: function() {},
		_initElements: function() {},
		_click: function() {},
		_sendRequest: function() {},
		_success: function() {}
	});
}

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
		$(window).on('unload',function() {
			$forms.find('.formSubmit input[type=submit]').enable();
		});
	}
});

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Provides previews for Redactor message fields.
	 *
	 * @param        string                className
	 * @param        string                messageFieldID
	 * @param        string                previewButtonID
	 */
	WCF.Message.Preview = Class.extend({
		/**
		 * class name
		 * @var        string
		 */
		_className: '',
		
		/**
		 * message field id
		 * @var        string
		 */
		_messageFieldID: '',
		
		/**
		 * message field
		 * @var        jQuery
		 */
		_messageField: null,
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * preview button
		 * @var        jQuery
		 */
		_previewButton: null,
		
		/**
		 * previous button label
		 * @var        string
		 */
		_previewButtonLabel: '',
		
		/**
		 * Initializes a new WCF.Message.Preview object.
		 *
		 * @param        string                className
		 * @param        string                messageFieldID
		 * @param        string                previewButtonID
		 */
		init: function (className, messageFieldID, previewButtonID) {
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
		_click: function (event) {
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
		 * @param        string                message
		 * @return        object
		 */
		_getParameters: function (message) {
			// collect message form options
			var $options = {};
			$('#settings_' + this._messageFieldID).find('input[type=checkbox]').each(function (index, checkbox) {
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
		 * @return        string
		 */
		_getMessage: function () {
			return this._textarea.redactor('code.get');
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
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
		 * @param        object                data
		 */
		_handleResponse: function (data) {
		},
		
		/**
		 * Handles errors during preview requests.
		 *
		 * The return values indicates if the default error overlay is shown.
		 *
		 * @param        object                data
		 * @return        boolean
		 */
		_failure: function (data) {
			if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
				return true;
			}
			
			// restore preview button
			this._previewButton.html(this._previewButtonLabel).enable();
			
			var $innerError = this._textarea.parent().children('small.innerError').empty();
			if (!$innerError.length) {
				$innerError = $('<small class="innerError" />').appendTo(this._textarea.parent());
			}
			
			var message = (data.returnValues.errorType === 'empty' ? WCF.Language.get('wcf.global.form.error.empty') : data.returnValues.errorMessage);
			if (data.returnValues.realErrorMessage) message = data.returnValues.realErrorMessage;
			$innerError.html(message);
			
			return false;
		}
	});
	
	/**
	 * Default implementation for message previews.
	 *
	 * @see        WCF.Message.Preview
	 */
	WCF.Message.DefaultPreview = WCF.Message.Preview.extend({
		_dialog: null,
		_options: {},
		
		/**
		 * @see        WCF.Message.Preview.init()
		 */
		init: function (options) {
			if (arguments.length > 1 && typeof options === 'string') {
				throw new Error("Outdated API call, please update your implementation.");
			}
			
			this._options = $.extend({
				disallowedBBCodesPermission: 'user.message.disallowedBBCodes',
				messageFieldID: '',
				previewButtonID: '',
				messageObjectType: '',
				messageObjectID: 0
			}, options);
			
			if (!this._options.messageObjectType) {
				throw new Error("Field 'messageObjectType' cannot be empty.");
			}
			
			this._super('wcf\\data\\bbcode\\MessagePreviewAction', this._options.messageFieldID, this._options.previewButtonID);
		},
		
		/**
		 * @see        WCF.Message.Preview._handleResponse()
		 */
		_handleResponse: function (data) {
			require(['WoltLabSuite/Core/Ui/Dialog'], (function (UiDialog) {
				UiDialog.open(this, '<div class="htmlContent">' + data.returnValues.message + '</div>');
			}).bind(this));
		},
		
		/**
		 * @see        WCF.Message.Preview._getParameters()
		 */
		_getParameters: function (message) {
			var $parameters = this._super(message);
			
			for (var key in this._options) {
				if (this._options.hasOwnProperty(key) && key !== 'messageFieldID' && key !== 'previewButtonID') {
					$parameters[key] = this._options[key];
				}
			}
			
			return $parameters;
		},
		
		_dialogSetup: function () {
			return {
				id: 'messagePreview',
				options: {
					title: WCF.Language.get('wcf.global.preview')
				},
				source: null
			}
		}
	});
	
	/**
	 * Handles multilingualism for messages.
	 *
	 * @param        integer                languageID
	 * @param        object                availableLanguages
	 * @param        boolean                forceSelection
	 */
	WCF.Message.Multilingualism = Class.extend({
		/**
		 * list of available languages
		 * @var        object
		 */
		_availableLanguages: {},
		
		/**
		 * language id
		 * @var        integer
		 */
		_languageID: 0,
		
		/**
		 * language input element
		 * @var        jQuery
		 */
		_languageInput: null,
		
		/**
		 * Initializes WCF.Message.Multilingualism
		 *
		 * @param        integer                languageID
		 * @param        object                availableLanguages
		 * @param        boolean                forceSelection
		 */
		init: function (languageID, availableLanguages, forceSelection) {
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
		 * @param        object                event
		 */
		_click: function (event) {
			this._languageID = $(event.currentTarget).data('languageID');
			this._updateLabel();
		},
		
		/**
		 * Disables language selection.
		 */
		_disable: function () {
			this._languageID = 0;
			this._updateLabel();
		},
		
		/**
		 * Updates selected language.
		 */
		_updateLabel: function () {
			this._languageInput.find('.dropdownToggle > span').text(this._availableLanguages[this._languageID]);
		},
		
		/**
		 * Sets language id upon submit.
		 */
		_submit: function () {
			this._languageInput.next('input[name=languageID]').prop('value', this._languageID);
		}
	});
	
	/**
	 * Loads smiley categories upon user request.
	 */
	WCF.Message.SmileyCategories = Class.extend({
		/**
		 * list of already loaded category ids
		 * @var        array<integer>
		 */
		_cache: [],
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * wysiwyg editor selector
		 * @var        string
		 */
		_wysiwygSelector: '',
		
		/**
		 * Initializes the smiley loader.
		 *
		 * @param        string                wysiwygSelector
		 */
		init: function (wysiwygSelector, smiliesTabMenuId, formBuilderUsage) {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			this._wysiwygSelector = wysiwygSelector;
			this._smiliesTabMenuId = smiliesTabMenuId || 'smilies-' + this._wysiwygSelector;
			this._formBuilderUsage = formBuilderUsage || false;
			
			$('#' + this._smiliesTabMenuId).on('messagetabmenushow', $.proxy(this._click, this));
		},
		
		/**
		 * Handles tab menu clicks.
		 *
		 * @param        {Event}        event
		 * @param        {Object}        data
		 */
		_click: function (event, data) {
			event.preventDefault();
			
			if (this._formBuilderUsage) {
				var href = data.activeTab.tab.children('a').prop('href');
				if (href.match(/#([a-zA-Z0-9_-]+)$/)) {
					var anchor = RegExp.$1;
					
					if (anchor.match(this._smiliesTabMenuId.replace(/Container$/, '') + '_smileyCategoryTab(\\d+)Container')) {
						var categoryID = parseInt(RegExp.$1);
					}
					else {
						console.debug("[WCF.Message.SmileyCategories] Cannot extract category id for tab '" + data.activeTab.tab.wcfIdentify() + "'.");
						return;
					}
				}
				else {
					console.debug("[WCF.Message.SmileyCategories] Cannot extract category id for tab '" + data.activeTab.tab.wcfIdentify() + "'.");
					return;
				}
			}
			else {
				var categoryID = parseInt(data.activeTab.tab.data('smileyCategoryID'));
			}
			
			// ignore global category, will always be pre-loaded
			if (!categoryID) {
				return;
			}
			
			// smilies have already been loaded for this tab, ignore
			if (data.activeTab.container.children('ul.smileyList').length) {
				return;
			}
			
			// cache exists
			if (this._cache[categoryID] !== undefined) {
				data.activeTab.container.html(this._cache[categoryID]);
				return;
			}
			
			// load content
			this._proxy.setOption('data', {
				actionName: 'getSmilies',
				className: 'wcf\\data\\smiley\\category\\SmileyCategoryAction',
				objectIDs: [categoryID]
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			var $categoryID = parseInt(data.returnValues.smileyCategoryID);
			this._cache[$categoryID] = data.returnValues.template;
			
			if (this._formBuilderUsage) {
				$('#' + this._smiliesTabMenuId.replace(/Container$/, '') + '_smileyCategoryTab' + $categoryID + 'Container').html(data.returnValues.template);
			}
			else {
				$('#smilies-' + this._wysiwygSelector + '-' + $categoryID).html(data.returnValues.template);
			}
		}
	});
	
	/**
	 * @deprecated 5.2 Use `WoltLabSuite/Core/Ui/Smiley/Insert` instead.
	 */
	WCF.Message.Smilies = Class.extend({
		init: function (editorId) {
			require(['WoltLabSuite/Core/Ui/Smiley/Insert'], function(UiSmileyInsert) {
				new UiSmileyInsert(editorId);
			});
		}
	});
	
	/**
	 * Provides an inline message editor.
	 *
	 * @deprecated        3.0 - please use `WoltLabSuite/Core/Ui/Message/InlineEditor` instead
	 *
	 * @param        integer                containerID
	 */
	WCF.Message.InlineEditor = Class.extend({
		/**
		 * list of messages
		 * @var        object
		 */
		_container: {},
		
		/**
		 * container id
		 * @var        int
		 */
		_containerID: 0,
		
		/**
		 * list of dropdowns
		 * @var        object
		 */
		_dropdowns: {},
		
		/**
		 * CSS selector for the message container
		 * @var        string
		 */
		_messageContainerSelector: '.jsMessage',
		
		/**
		 * prefix of the message editor CSS id
		 * @var        string
		 */
		_messageEditorIDPrefix: 'messageEditor',
		
		/**
		 * Initializes a new WCF.Message.InlineEditor object.
		 *
		 * @param        integer                                containerID
		 * @param        boolean                                supportExtendedForm
		 * @param        WCF.Message.Quote.Manager        quoteManager
		 */
		init: function (containerID, supportExtendedForm, quoteManager) {
			require(['WoltLabSuite/Core/Ui/Message/InlineEditor'], (function (UiMessageInlineEditor) {
				new UiMessageInlineEditor({
					className: this._getClassName(),
					containerId: containerID,
					editorPrefix: this._messageEditorIDPrefix,
					
					messageSelector: this._messageContainerSelector,
					quoteManager: quoteManager || null,
					
					callbackDropdownInit: this._callbackDropdownInit.bind(this)
				});
			}).bind(this));
		},
		
		/**
		 * Loads WYSIWYG editor for selected message.
		 *
		 * @param        object                event
		 * @param        integer                containerID
		 * @return        boolean
		 */
		_click: function (event, containerID) {
			containerID = (event === null) ? ~~containerID : ~~elData(event.currentTarget, 'container-id');
			
			require(['WoltLabSuite/Core/Ui/Message/InlineEditor'], (function (UiMessageInlineEditor) {
				UiMessageInlineEditor.legacyEdit(containerID);
			}).bind(this));
			
			if (event) {
				event.preventDefault();
			}
		},
		
		/**
		 * Initializes the inline edit dropdown menu.
		 *
		 * @param        integer                containerID
		 * @param        jQuery                dropdownMenu
		 */
		_initDropdownMenu: function (containerID, dropdownMenu) {
		},
		
		_callbackDropdownInit: function (element, dropdownMenu) {
			this._initDropdownMenu($(element).wcfIdentify(), $(dropdownMenu));
			
			return null;
		},
		
		/**
		 * Returns message action class name.
		 *
		 * @return        string
		 */
		_getClassName: function () {
			return '';
		}
	});
	
	/**
	 * Handles submit buttons for forms with an embedded WYSIWYG editor.
	 */
	WCF.Message.Submit = {
		/**
		 * list of registered buttons
		 * @var        object
		 */
		_buttons: {},
		
		/**
		 * Registers submit button for specified wysiwyg container id.
		 *
		 * @param        string                wysiwygContainerID
		 * @param        string                selector
		 */
		registerButton: function (wysiwygContainerID, selector) {
			if (!WCF.Browser.isChrome()) {
				return;
			}
			
			this._buttons[wysiwygContainerID] = $(selector);
		},
		
		/**
		 * Triggers 'click' event for registered buttons.
		 */
		execute: function (wysiwygContainerID) {
			if (!this._buttons[wysiwygContainerID]) {
				return;
			}
			
			this._buttons[wysiwygContainerID].trigger('click');
		}
	};
}
else {
	WCF.Message.Preview = Class.extend({
		_className: "",
		_messageFieldID: "",
		_messageField: {},
		_proxy: {},
		_previewButton: {},
		_previewButtonLabel: "",
		init: function() {},
		_click: function() {},
		_getParameters: function() {},
		_getMessage: function() {},
		_success: function() {},
		_handleResponse: function() {},
		_failure: function() {}
	});
	
	WCF.Message.DefaultPreview = WCF.Message.Preview.extend({
		_dialog: {},
		_options: {},
		init: function() {},
		_handleResponse: function() {},
		_getParameters: function() {},
		_dialogSetup: function() {},
		_className: "",
		_messageFieldID: "",
		_messageField: {},
		_proxy: {},
		_previewButton: {},
		_previewButtonLabel: "",
		_click: function() {},
		_getMessage: function() {},
		_success: function() {},
		_failure: function() {}
	});
	
	WCF.Message.Multilingualism = Class.extend({
		_availableLanguages: {},
		_languageID: 0,
		_languageInput: {},
		init: function() {},
		_click: function() {},
		_disable: function() {},
		_updateLabel: function() {},
		_submit: function() {}
	});
	
	WCF.Message.SmileyCategories = Class.extend({
		_cache: {},
		_proxy: {},
		_wysiwygSelector: "",
		init: function() {},
		_click: function() {},
		_success: function() {}
	});
	
	WCF.Message.Smilies = Class.extend({
		_editorId: "",
		init: function() {},
		_smileyClick: function() {}
	});
	
	WCF.Message.InlineEditor = Class.extend({
		_container: {},
		_containerID: 0,
		_dropdowns: {},
		_messageContainerSelector: "",
		_messageEditorIDPrefix: "",
		init: function() {},
		_click: function() {},
		_initDropdownMenu: function() {},
		_callbackDropdownInit: function() {},
		_getClassName: function() {}
	});
	
	WCF.Message.Submit = {
		_buttons: {},
		registerButton: function() {},
		execute: function() {}
	};
}

/**
 * Namespace for message quotes.
 */
WCF.Message.Quote = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles message quotes.
	 */
	WCF.Message.Quote.Handler = Class.extend({
		/**
		 * active container id
		 * @var        string
		 */
		_activeContainerID: '',
		
		/**
		 * action class name
		 * @var        string
		 */
		_className: '',
		
		/**
		 * list of message containers
		 * @var        object
		 */
		_containers: {},
		
		/**
		 * container selector
		 * @var        string
		 */
		_containerSelector: '',
		
		/**
		 * 'copy quote' overlay
		 * @var        jQuery
		 */
		_copyQuote: null,
		
		/**
		 * marked message
		 * @var        string
		 */
		_message: '',
		
		/**
		 * message body selector
		 * @var        string
		 */
		_messageBodySelector: '',
		
		/**
		 * object id
		 * @var        {int}
		 */
		_objectID: 0,
		
		/**
		 * object type name
		 * @var        string
		 */
		_objectType: '',
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * quote manager
		 * @var        WCF.Message.Quote.Manager
		 */
		_quoteManager: null,
		
		/**
		 * @var {?int}
		 */
		_selectionChangeTimer: null,
		
		/**
		 * @var {boolean}
		 */
		_isMouseDown: false,
		
		/**
		 * Initializes the quote handler for given object type.
		 *
		 * @param        {WCF.Message.Quote.Manager}        quoteManager
		 * @param        {string}                        className
		 * @param        {string}                        objectType
		 * @param        {string}                        containerSelector
		 * @param        {string}                        messageBodySelector
		 * @param        {string}                        messageContentSelector
		 * @param        {boolean}                        supportDirectInsert
		 */
		init: function (quoteManager, className, objectType, containerSelector, messageBodySelector, messageContentSelector, supportDirectInsert) {
			this._className = className;
			if (this._className === '') {
				console.debug("[WCF.Message.QuoteManager] Empty class name given, aborting.");
				return;
			}
			
			this._objectType = objectType;
			if (this._objectType === '') {
				console.debug("[WCF.Message.QuoteManager] Empty object type name given, aborting.");
				return;
			}
			
			this._containerSelector = containerSelector;
			this._message = '';
			this._messageBodySelector = messageBodySelector;
			this._objectID = 0;
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			this._selectionChangeTimer = null;
			this._isMouseDown = false;
			
			this._initContainers();
			
			supportDirectInsert = (supportDirectInsert && quoteManager.supportPaste());
			this._initCopyQuote(supportDirectInsert);
			
			$(document).mouseup($.proxy(this._mouseUp, this));
			document.addEventListener('selectionchange', this._selectionchange.bind(this));
			
			// register with quote manager
			this._quoteManager = quoteManager;
			this._quoteManager.register(this._objectType, this);
			
			// register with DOMNodeInsertedHandler
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Message.Quote.Handler' + objectType.hashCode(), $.proxy(this._initContainers, this));
			
			// Prevent the tooltip from being selectable while the touch pointer is being moved.
			var timer = null;
			window.addEventListener('touchmove', (function() {
				if (!this._copyQuote[0].classList.contains('active')) {
					return;
				}
				
				this._copyQuote[0].classList.add('touchForceInaccessible');
				
				if (timer !== null) {
					window.clearTimeout(timer);
				}
				
				timer = window.setTimeout((function() {
					this._copyQuote[0].classList.remove('touchForceInaccessible');
					
					timer = null;
				}).bind(this), 50);
			}).bind(this));
		},
		
		/**
		 * Initializes message containers.
		 */
		_initContainers: function () {
			var self = this;
			$(this._containerSelector).each(function (index, container) {
				var $container = $(container);
				var $containerID = $container.wcfIdentify();
				
				if (!self._containers[$containerID]) {
					self._containers[$containerID] = $container;
					if ($container.hasClass('jsInvalidQuoteTarget')) {
						return true;
					}
					
					if (self._messageBodySelector) {
						$container.data('body', $container.find(self._messageBodySelector).data('containerID', $containerID));
					}
					
					$container.mousedown($.proxy(self._mouseDown, self));
					$container[0].classList.add('jsQuoteMessageContainer');
					
					// bind event to quote whole message
					self._containers[$containerID].find('.jsQuoteMessage').click($.proxy(self._saveFullQuote, self));
				}
			});
		},
		
		_selectionchange: function () {
			if (this._isMouseDown) {
				return;
			}
			
			if (this._activeContainerID === '') {
				// check if the selection is non-empty and is entirely contained
				// inside a single message container that is registered for quoting
				var selection = window.getSelection();
				if (selection.rangeCount !== 1 || selection.isCollapsed) {
					return;
				}
				
				var range = selection.getRangeAt(0);
				var startContainer = elClosest(range.startContainer, '.jsQuoteMessageContainer');
				var endContainer = elClosest(range.endContainer, '.jsQuoteMessageContainer');
				if (startContainer && startContainer === endContainer && !startContainer.classList.contains('jsInvalidQuoteTarget')) {
					// Check if the selection is visible, such as text marked inside containers with an
					// active overflow handling attached to it. This can be a side effect of the browser
					// search which modifies the text selection, but cannot be distinguished from manual
					// selections initiated by the user.
					var commonAncestor = range.commonAncestorContainer;
					if (commonAncestor.nodeType !== Node.ELEMENT_NODE) {
						commonAncestor = commonAncestor.parentNode;
					}
					
					var offsetParent = commonAncestor.offsetParent;
					if (startContainer.contains(offsetParent)) {
						if (offsetParent.scrollTop + offsetParent.clientHeight < commonAncestor.offsetTop) {
							// The selected text is not visible to the user.
							return;
						}
					}
					
					this._activeContainerID = startContainer.id;
				}
			}
			
			if (this._selectionChangeTimer !== null) {
				window.clearTimeout(this._selectionChangeTimer);
			}
			
			this._selectionChangeTimer = window.setTimeout(this._mouseUp.bind(this), 100);
		},
		
		/**
		 * Handles mouse down event.
		 *
		 * @param        {Event}                event
		 */
		_mouseDown: function (event) {
			// hide copy quote
			this._copyQuote.removeClass('active');
			
			this._activeContainerID = (event.currentTarget.classList.contains('jsInvalidQuoteTarget')) ? '' : event.currentTarget.id;
			
			if (this._selectionChangeTimer !== null) {
				window.clearTimeout(this._selectionChangeTimer);
				this._selectionChangeTimer = null;
			}
			
			this._isMouseDown = true;
		},
		
		/**
		 * Returns the text of a node and its children.
		 *
		 * @param        {Node}                node
		 * @return        {string}
		 */
		_getNodeText: function (node) {
			// work-around for IE, see http://stackoverflow.com/a/5983176
			var $nodeFilter = function (node) {
				switch (node.tagName) {
					case 'BLOCKQUOTE':
					case 'SCRIPT':
						return NodeFilter.FILTER_REJECT;
					
					case 'IMG':
						if (!node.classList.contains('smiley') || node.alt.length === 0) {
							return NodeFilter.FILTER_REJECT;
						}
						// fallthrough
					
					//noinspection FallthroughInSwitchStatementJS
					default:
						return NodeFilter.FILTER_ACCEPT;
				}
			};
			$nodeFilter.acceptNode = $nodeFilter;
			
			var $walker = document.createTreeWalker(
				node,
				NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT,
				$nodeFilter,
				true
			);
			
			var $text = '', ignoreLinks = [], value;
			while ($walker.nextNode()) {
				var $node = $walker.currentNode;
				
				if ($node.nodeType === Node.ELEMENT_NODE) {
					switch ($node.tagName) {
						case 'A':
							// \u2026 === &hellip;
							value = $node.textContent;
							if (value.indexOf('\u2026') > 0) {
								var tmp = value.split(/\u2026/);
								if (tmp.length === 2) {
									var href = $node.href;
									if (href.indexOf(tmp[0]) === 0 && href.substr(tmp[1].length * -1) === tmp[1]) {
										// truncated url, use original href to preserve link
										$text += href;
										ignoreLinks.push($node);
									}
								}
							}
							break;
						
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
							
						case 'P':
							$text += "\n\n";
							break;
							
						// smilies
						case 'IMG':
							$text += " " + $node.alt + " ";
							break;
							
						// Code listing
						case 'DIV':
							if ($node.classList.contains('codeBoxHeadline') || $node.classList.contains('codeBoxLine')) {
								$text += "\n";
							}
							break;
					}
				}
				else {
					if ($node.parentNode.nodeName === 'A' && ignoreLinks.indexOf($node.parentNode) !== -1) {
						// ignore text content of links that have already been captured
						continue;
					}
					
					// Firefox loves to arbitrarily wrap pasted text at weird line lengths, causing
					// pointless linebreaks to be inserted. Replacing them with a simple space will
					// preserve the spacing between words that would otherwise be lost.
					$text += $node.nodeValue.replace(/\n/g, ' ');
				}
				
			}
			
			return $text;
		},
		
		/**
		 * Handles the mouse up event.
		 * 
		 * @param       {?$.Event}      event
		 */
		_mouseUp: function (event) {
			if (event && event.originalEvent instanceof Event) {
				if (this._selectionChangeTimer !== null) {
					// prevent collisions of the `selectionchange` and the `mouseup` event
					window.clearTimeout(this._selectionChangeTimer);
					this._selectionChangeTimer = null;
				}
				
				this._isMouseDown = false;
			}
			
			// ignore event
			if (this._activeContainerID === '') {
				this._copyQuote.removeClass('active');
				return;
			}
			
			var selection = window.getSelection();
			if (selection.rangeCount !== 1 || selection.isCollapsed) {
				this._copyQuote.removeClass('active');
				return;
			}
			
			var $container = this._containers[this._activeContainerID];
			var $objectID = $container.data('objectID');
			$container = $container.data('body') || $container;
			
			var anchorNode = selection.anchorNode;
			while (anchorNode) {
				if (anchorNode === $container[0]) {
					break;
				}
				
				anchorNode = anchorNode.parentNode;
			}
			
			// selection spans unrelated nodes
			if (anchorNode !== $container[0]) {
				this._copyQuote.removeClass('active');
				return;
			}
			
			var $selection = this._getSelectedText();
			var $text = $.trim($selection);
			if ($text === '') {
				this._copyQuote.removeClass('active');
				
				return;
			}
			
			// check if mousedown/mouseup took place inside a blockquote
			var range = selection.getRangeAt(0);
			var startContainer = (range.startContainer.nodeType === Node.TEXT_NODE) ? range.startContainer.parentNode : range.startContainer;
			var endContainer = (range.endContainer.nodeType === Node.TEXT_NODE) ? range.endContainer.parentNode : range.endContainer;
			if (startContainer.closest('blockquote') || endContainer.closest('blockquote')) {
				this._copyQuote.removeClass('active');
				
				return;
			}
			
			// compare selection with message text of given container
			var $messageText = this._getNodeText($container[0]);
			
			// selected text is not part of $messageText or contains text from unrelated nodes
			if (this._normalize($messageText).indexOf(this._normalize($text)) === -1) {
				return;
			}
			this._copyQuote.addClass('active');
			
			var $coordinates = this._getBoundingRectangle($container, window.getSelection());
			var $dimensions = this._copyQuote.getDimensions('outer');
			var $left = ($coordinates.right - $coordinates.left) / 2 - ($dimensions.width / 2) + $coordinates.left;
			
			// Prevent the overlay from overflowing the left or right boundary of the container.
			var containerBoundaries = $container[0].getBoundingClientRect();
			if ($left < containerBoundaries.left) {
				$left = containerBoundaries.left;
			}
			else if ($left + $dimensions.width > containerBoundaries.right) {
				$left = containerBoundaries.right - $dimensions.width;
			}
			
			this._copyQuote.css({
				top: $coordinates.bottom + 7 + 'px',
				left: $left + 'px'
			});
			this._copyQuote.removeClass('active');
			
			if (this._selectionChangeTimer === null) {
				// reset containerID
				this._activeContainerID = '';
			}
			else {
				window.clearTimeout(this._selectionChangeTimer);
				this._selectionChangeTimer = null;
			}
			
			// show element after a delay, to prevent display if text was unmarked again (clicking into marked text)
			var self = this;
			window.setTimeout(function () {
				var $text = $.trim(self._getSelectedText());
				if ($text !== '') {
					self._copyQuote.addClass('active');
					self._message = $text;
					self._objectID = $objectID;
				}
			}, 10);
		},
		
		/**
		 * Normalizes a text for comparison.
		 *
		 * @param        {string}        text
		 * @return        {string}
		 */
		_normalize: function (text) {
			return text.replace(/\r?\n|\r/g, "\n").replace(/\s/g, ' ').replace(/\s{2,}/g, ' ');
		},
		
		/**
		 * Returns the offsets of the selection's bounding rectangle.
		 *
		 * @return        {Object}
		 */
		_getBoundingRectangle: function (container, selection) {
			var $coordinates = null;
			
			if (selection.rangeCount > 0) {
				// the coordinates returned by getBoundingClientRect() are relative to the viewport, not the document!
				var $rect = selection.getRangeAt(0).getBoundingClientRect();
				
				var scrollTop = $(document).scrollTop();
				$coordinates = {
					bottom: $rect.bottom + scrollTop,
					left: $rect.left,
					right: $rect.right,
					top: $rect.top + scrollTop
				};
			}
			
			return $coordinates;
		},
		
		/**
		 * Initializes the 'copy quote' element.
		 *
		 * @param        {boolean}        supportDirectInsert
		 */
		_initCopyQuote: function (supportDirectInsert) {
			this._copyQuote = $('#quoteManagerCopy');
			if (!this._copyQuote.length) {
				this._copyQuote = $('<div id="quoteManagerCopy" class="balloonTooltip interactive"><span class="jsQuoteManagerStore">' + WCF.Language.get('wcf.message.quote.quoteSelected') + '</span></div>').appendTo(document.body);
				var $storeQuote = this._copyQuote.children('span.jsQuoteManagerStore').click($.proxy(this._saveQuote, this));
				if (supportDirectInsert) {
					$('<span class="jsQuoteManagerQuoteAndInsert">' + WCF.Language.get('wcf.message.quote.quoteAndReply') + '</span>').click($.proxy(this._saveAndInsertQuote, this)).insertAfter($storeQuote);
				}
			}
		},
		
		/**
		 * Returns the text selection.
		 *
		 * @return        string
		 */
		_getSelectedText: function () {
			var $selection = window.getSelection();
			if ($selection.rangeCount) {
				return this._getNodeText($selection.getRangeAt(0).cloneContents());
			}
			
			return '';
		},
		
		/**
		 * Saves a full quote.
		 *
		 * @param        {Event}                event
		 */
		_saveFullQuote: function (event) {
			event.preventDefault();
			
			var $listItem = $(event.currentTarget);
			
			this._proxy.setOption('data', {
				actionName: 'saveFullQuote',
				className: this._className,
				interfaceName: 'wcf\\data\\IMessageQuoteAction',
				objectIDs: [$listItem.data('objectID')]
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
		},
		
		/**
		 * Saves a quote.
		 *
		 * @param        {boolean}        renderQuote
		 */
		_saveQuote: function (renderQuote) {
			this._proxy.setOption('data', {
				actionName: 'saveQuote',
				className: this._className,
				interfaceName: 'wcf\\data\\IMessageQuoteAction',
				objectIDs: [this._objectID],
				parameters: {
					message: this._message,
					renderQuote: (renderQuote === true)
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Saves a quote and directly inserts it.
		 */
		_saveAndInsertQuote: function () {
			this._saveQuote(true);
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        {Object}        data
		 */
		_success: function (data) {
			if (data.returnValues.count !== undefined) {
				if (data.returnValues.fullQuoteMessageIDs !== undefined) {
					data.returnValues.fullQuoteObjectIDs = data.returnValues.fullQuoteMessageIDs;
				}
				
				var $fullQuoteObjectIDs = (data.returnValues.fullQuoteObjectIDs !== undefined) ? data.returnValues.fullQuoteObjectIDs : {};
				this._quoteManager.updateCount(data.returnValues.count, $fullQuoteObjectIDs);
			}
			
			switch (data.actionName) {
				case 'saveQuote':
				case 'saveFullQuote':
					if (data.returnValues.renderedQuote) {
						WCF.System.Event.fireEvent('com.woltlab.wcf.message.quote', 'insert', {
							forceInsert: (data.actionName === 'saveQuote'),
							quote: data.returnValues.renderedQuote
						});
					}
					break;
			}
		},
		
		/**
		 * Updates the full quote data for all matching objects.
		 *
		 * @param        array<integer>                $objectIDs
		 */
		updateFullQuoteObjectIDs: function (objectIDs) {
			for (var $containerID in this._containers) {
				this._containers[$containerID].find('.jsQuoteMessage').each(function (index, button) {
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
	 * @param        integer                count
	 */
	WCF.Message.Quote.Manager = Class.extend({
		/**
		 * list of form buttons
		 * @var        {Object}
		 */
		_buttons: {},
		
		/**
		 * number of stored quotes
		 * @var        {int}
		 */
		_count: 0,
		
		/**
		 * dialog overlay
		 * @var        {jQuery}
		 */
		_dialog: null,
		
		/**
		 * editor element id
		 * @var        {string}
		 */
		_editorId: '',
		
		/**
		 * alternative editor element id
		 * @var        {string}
		 */
		_editorIdAlternative: '',
		
		/**
		 * form element
		 * @var        {jQuery}
		 */
		_form: null,
		
		/**
		 * list of quote handlers
		 * @var        {Object}
		 */
		_handlers: {},
		
		/**
		 * true, if an up-to-date template exists
		 * @var        {boolean}
		 */
		_hasTemplate: false,
		
		/**
		 * true, if related quotes should be inserted
		 * @var        {boolean}
		 */
		_insertQuotes: true,
		
		/**
		 * action proxy
		 * @var        {WCF.Action.Proxy}
		 */
		_proxy: null,
		
		/**
		 * list of quotes to remove upon submit
		 * @var        {Array}
		 */
		_removeOnSubmit: [],
		
		/**
		 * allow pasting
		 * @var        {boolean}
		 */
		_supportPaste: false,
		
		/**
		 * pasting was temporarily enabled due to an alternative editor being set
		 * @var boolean
		 */
		_supportPasteOverride: false,
		
		/**
		 * Initializes the quote manager.
		 *
		 * @param        {int}                count
		 * @param        {string}        elementID
		 * @param        {boolean}        supportPaste
		 * @param        {Array}        removeOnSubmit
		 */
		init: function (count, elementID, supportPaste, removeOnSubmit) {
			this._buttons = {
				insert: null,
				remove: null
			};
			this._count = parseInt(count) || 0;
			this._dialog = null;
			this._editorId = '';
			this._editorIdAlternative = '';
			this._form = null;
			this._handlers = {};
			this._hasTemplate = false;
			this._insertQuotes = true;
			this._removeOnSubmit = [];
			this._supportPaste = false;
			this._supportPasteOverride = false;
			
			if (elementID) {
				var element = $('#' + elementID);
				if (element.length) {
					this._editorId = elementID;
					this._supportPaste = true;
					
					// get surrounding form-tag
					this._form = element.parents('form:eq(0)');
					if (this._form.length) {
						this._form.submit(this._submit.bind(this));
						this._removeOnSubmit = removeOnSubmit || [];
					}
					else {
						this._form = null;
						
						// allow override
						this._supportPaste = (supportPaste === true);
					}
				}
			}
			
			this._proxy = new WCF.Action.Proxy({
				showLoadingOverlay: false,
				success: $.proxy(this._success, this),
				url: 'index.php?message-quote/&t=' + SECURITY_TOKEN
			});
			
			this._toggleShowQuotes();
			
			WCF.System.Event.addListener('com.woltlab.wcf.quote', 'reload', this.countQuotes.bind(this));
			
			// event forwarding
			WCF.System.Event.addListener('com.woltlab.wcf.message.quote', 'insert', (function (data) {
				//noinspection JSUnresolvedVariable
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'insertQuote_' + (this._editorIdAlternative ? this._editorIdAlternative : this._editorId), {
					author: data.quote.username,
					content: data.quote.text,
					isText: !data.quote.isFullQuote,
					link: data.quote.link
				});
			}).bind(this));
		},
		
		/**
		 * Sets an alternative editor element id on runtime.
		 *
		 * @param        {(string|jQuery)}       elementId       element id or jQuery element
		 */
		setAlternativeEditor: function (elementId) {
			if (!this._editorIdAlternative && !this._supportPaste) {
				this._hasTemplate = false;
				this._supportPaste = true;
				this._supportPasteOverride = true;
			}
			
			if (typeof elementId === 'object') elementId = elementId[0].id;
			this._editorIdAlternative = elementId;
		},
		
		/**
		 * Clears alternative editor element id.
		 */
		clearAlternativeEditor: function () {
			if (this._supportPasteOverride) {
				this._hasTemplate = false;
				this._supportPaste = false;
				this._supportPasteOverride = false;
			}
			
			this._editorIdAlternative = '';
		},
		
		/**
		 * Registers a quote handler.
		 *
		 * @param        {string}                        objectType
		 * @param        {WCF.Message.Quote.Handler}        handler
		 */
		register: function (objectType, handler) {
			this._handlers[objectType] = handler;
		},
		
		/**
		 * Updates number of stored quotes.
		 *
		 * @param        {int}                count
		 * @param        {Object}        fullQuoteObjectIDs
		 */
		updateCount: function (count, fullQuoteObjectIDs) {
			this._count = parseInt(count) || 0;
			
			this._toggleShowQuotes();
			
			// update full quote ids of handlers
			for (var $objectType in this._handlers) {
				if (this._handlers.hasOwnProperty($objectType)) {
					var $objectIDs = fullQuoteObjectIDs[$objectType] || [];
					this._handlers[$objectType].updateFullQuoteObjectIDs($objectIDs);
				}
			}
		},
		
		/**
		 * Inserts all associated quotes upon first time using quick reply.
		 *
		 * @param        {string}        className
		 * @param        {int}                parentObjectID
		 * @param        {Object}        callback
		 */
		insertQuotes: function (className, parentObjectID, callback) {
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
		_toggleShowQuotes: function () {
			require(['WoltLabSuite/Core/Ui/Page/Action'], (function (UiPageAction) {
				var buttonName = 'showQuotes';
				
				if (this._count) {
					var button = UiPageAction.get(buttonName);
					if (button === undefined) {
						button = elCreate('a');
						button.addEventListener('mousedown', this._click.bind(this));
						
						UiPageAction.add(buttonName, button);
					}
					
					button.textContent = WCF.Language.get('wcf.message.quote.showQuotes', {
						count: this._count
					});
					
					UiPageAction.show(buttonName);
				}
				else {
					UiPageAction.remove(buttonName);
				}
				
				this._hasTemplate = false;
			}).bind(this));
		},
		
		/**
		 * Handles clicks on 'Show quotes'.
		 */
		_click: function () {
			var editor = document.activeElement;
			if (editor.classList.contains('redactor-layer')) {
				$('#' + elData(editor, 'element-id')).redactor('selection.save');
			}
			
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
		 * @param        {string}        template
		 */
		renderDialog: function (template) {
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
				this._dialog.find('input.jsRemoveQuote').each(function (index, input) {
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
		_changeButtons: function () {
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
		 * @param        {Object}        event
		 */
		_change: function (event) {
			var $input = $(event.currentTarget);
			var $quoteID = $input.parent('li').attr('data-quote-id');
			
			if ($input.prop('checked')) {
				this._removeOnSubmit.push($quoteID);
			}
			else {
				var index = this._removeOnSubmit.indexOf($quoteID);
				if (index !== -1) {
					this._removeOnSubmit.splice(index, 1);
				}
			}
		},
		
		/**
		 * Inserts the selected quotes.
		 */
		_insertSelected: function () {
			if (!this._dialog.find('input.jsCheckbox:checked').length) {
				this._dialog.find('input.jsCheckbox').prop('checked', 'checked');
			}
			
			// insert all quotes
			this._dialog.find('input.jsCheckbox:checked').each($.proxy(function (index, input) {
				this._insertQuote(null, input);
			}, this));
			
			// close dialog
			this._dialog.wcfDialog('close');
		},
		
		/**
		 * Inserts a quote.
		 *
		 * @param        {Event}                event
		 * @param        {Object}        inputElement
		 */
		_insertQuote: function (event, inputElement) {
			var listItem = $(event ? event.currentTarget : inputElement).parents('li:eq(0)');
			var text = listItem.children('.jsFullQuote')[0].textContent.trim();
			
			var message = listItem.parents('.message:eq(0)');
			var author = message.data('username');
			var link = message.data('link');
			var isText = !elDataBool(listItem[0], 'is-full-quote');
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'insertQuote_' + (this._editorIdAlternative ? this._editorIdAlternative : this._editorId), {
				author: author,
				content: text,
				isText: isText,
				link: link
			});
			
			// remove quote upon submit or upon request
			this._removeOnSubmit.push(listItem.data('quote-id'));
			
			// close dialog
			if (event !== null) {
				this._dialog.wcfDialog('close');
			}
		},
		
		/**
		 * Removes selected quotes.
		 */
		_removeSelected: function () {
			if (!this._dialog.find('input.jsCheckbox:checked').length) {
				this._dialog.find('input.jsCheckbox').prop('checked', 'checked');
			}
			
			var $quoteIDs = [];
			this._dialog.find('input.jsCheckbox:checked').each(function (index, input) {
				$quoteIDs.push($(input).parents('li').attr('data-quote-id'));
			});
			
			if ($quoteIDs.length) {
				// get object types
				var $objectTypes = [];
				for (var $objectType in this._handlers) {
					if (this._handlers.hasOwnProperty($objectType)) {
						$objectTypes.push($objectType);
					}
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
		_submit: function () {
			if (this._supportPaste && this._removeOnSubmit.length > 0) {
				var $formSubmit = this._form.find('.formSubmit');
				for (var i = 0, length = this._removeOnSubmit.length; i < length; i++) {
					$('<input type="hidden" name="__removeQuoteIDs[]" value="' + this._removeOnSubmit[i] + '" />').appendTo($formSubmit);
				}
			}
		},
		
		/**
		 * Returns a list of quote ids marked for removal.
		 *
		 * @return        {Array}
		 */
		getQuotesMarkedForRemoval: function () {
			return this._removeOnSubmit;
		},
		
		/**
		 * Marks quote ids for removal.
		 */
		markQuotesForRemoval: function () {
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
		removeMarkedQuotes: function () {
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
		countQuotes: function () {
			var $objectTypes = [];
			for (var $objectType in this._handlers) {
				if (this._handlers.hasOwnProperty($objectType)) {
					$objectTypes.push($objectType);
				}
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
		 * @param        {Object}        data
		 */
		_success: function (data) {
			if (data === null) {
				return;
			}
			
			if (data.count !== undefined) {
				var $fullQuoteObjectIDs = (data.fullQuoteObjectIDs !== undefined) ? data.fullQuoteObjectIDs : {};
				this.updateCount(data.count, $fullQuoteObjectIDs);
			}
			
			if (data.template !== undefined) {
				if ($.trim(data.template) == '') {
					this.updateCount(0, {});
				}
				else {
					this.renderDialog(data.template);
				}
			}
		},
		
		/**
		 * Returns true if pasting is supported.
		 *
		 * @return        boolean
		 */
		supportPaste: function () {
			return this._supportPaste;
		}
	});
}
else {
	WCF.Message.Quote.Handler = Class.extend({
		_activeContainerID: "",
		_className: "",
		_containers: {},
		_containerSelector: "",
		_copyQuote: {},
		_message: "",
		_messageBodySelector: "",
		_objectID: 0,
		_objectType: "",
		_proxy: {},
		_quoteManager: {},
		init: function() {},
		_initContainers: function() {},
		_mouseDown: function() {},
		_getNodeText: function() {},
		_mouseUp: function() {},
		_normalize: function() {},
		_getBoundingRectangle: function() {},
		_initCopyQuote: function() {},
		_getSelectedText: function() {},
		_saveFullQuote: function() {},
		_saveQuote: function() {},
		_saveAndInsertQuote: function() {},
		_success: function() {},
		updateFullQuoteObjectIDs: function() {}
	});
	
	WCF.Message.Quote.Manager = Class.extend({
		_buttons: {},
		_count: 0,
		_dialog: {},
		_editorId: "",
		_editorIdAlternative: "",
		_form: {},
		_handlers: {},
		_hasTemplate: false,
		_insertQuotes: true,
		_proxy: {},
		_removeOnSubmit: {},
		_supportPaste: false,
		init: function() {},
		setAlternativeEditor: function() {},
		clearAlternativeEditor: function() {},
		register: function() {},
		updateCount: function() {},
		insertQuotes: function() {},
		_toggleShowQuotes: function() {},
		_click: function() {},
		renderDialog: function() {},
		_changeButtons: function() {},
		_change: function() {},
		_insertSelected: function() {},
		_insertQuote: function() {},
		_removeSelected: function() {},
		_submit: function() {},
		getQuotesMarkedForRemoval: function() {},
		markQuotesForRemoval: function() {},
		removeMarkedQuotes: function() {},
		countQuotes: function() {},
		_success: function() {},
		supportPaste: function() {}
	});
}

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
	 * template containing the social media share buttons
	 * @var	string
	 */
	_shareButtonsTemplate: '',
	
	/**
	 * Initializes the WCF.Message.Share.Content class.
	 * 
	 * @param	{string?}	shareButtonsTemplate
	 */
	init: function(shareButtonsTemplate) {
		this._shareButtonsTemplate = shareButtonsTemplate || '';
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
				this._dialog = $('<div id="shareContentDialog" />').hide().appendTo(document.body);
				$dialogInitialized = true;
			}
			else {
				this._dialog.empty();
			}
			
			// permalink (plain text)
			var $section = $('<section class="section"><h2 class="sectionTitle"><label for="__sharePermalink">' + WCF.Language.get('wcf.message.share.permalink') + '</label></h2></section>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalink" class="long" readonly />').attr('value', $link).appendTo($section);
			
			// permalink (BBCode)
			var $section = $('<section class="section"><h2 class="sectionTitle"><label for="__sharePermalinkBBCode">' + WCF.Language.get('wcf.message.share.permalink.bbcode') + '</label></h2></section>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalinkBBCode" class="long" readonly />').attr('value', '[url=\'' + $link + '\']' + $title + '[/url]').appendTo($section);
			
			// permalink (HTML)
			var $section = $('<section class="section"><h2 class="sectionTitle"><label for="__sharePermalinkHTML">' + WCF.Language.get('wcf.message.share.permalink.html') + '</label></h2></section>').appendTo(this._dialog);
			$('<input type="text" id="__sharePermalinkHTML" class="long" readonly />').attr('value', '<a href="' + $link + '">' + WCF.String.escapeHTML($title) + '</a>').appendTo($section);
			
			// share buttons
			if (this._shareButtonsTemplate !== '') {
				$section = $('<section class="section"><h2 class="sectionTitle">' + WCF.Language.get('wcf.message.share') + '</h2>'  + this._shareButtonsTemplate + '</section>').appendTo(this._dialog);
				elData($section.children('.jsMessageShareButtons')[0], 'url', WCF.String.escapeHTML($link));
			}
			
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
 * @deprecated  3.0 - please use `WoltLabSuite/Core/Ui/Message/Share` instead
 */
WCF.Message.Share.Page = Class.extend({
	init: function() {
		require(['WoltLabSuite/Core/Ui/Message/Share'], function(UiMessageShare) {
			UiMessageShare.init();
		});
	}
});

/**
 * @deprecated 3.0
 */
WCF.Message.UserMention = Class.extend({
	init: function() {
		throw new Error("Support for mentions in Redactor are now enabled by adding the attribute 'data-support-mention=\"true\"' to the textarea element.");
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
		var $nav = this.element.find('> nav');
		var $tabs = $nav.find('> ul > li:not(.jsFlexibleMenuDropdown)');
		var $tabContainers = this.element.find('> div, > fieldset');
		
		if ($tabs.length != $tabContainers.length) {
			console.debug("[wcf.messageTabMenu] Amount of tabs does not equal amount of tab containers, aborting.");
			return;
		}
		
		var $preselect = this.element.data('preselect');
		
		// check for tabs containing '.innerError' and select the first matching one instead
		$tabContainers.each(function(index, container) {
			if (elBySel('.innerError', container) !== null) {
				$preselect = $($tabs[index]).data('name');
				return false;
			}
		});
		if ($preselect === 'true') $preselect = true;
		
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
				}
			}
			
			this._tabs.push({
				container: $tabContainer,
				name: $name,
				tab: $tab
			});
			this._tabsByName[$name] = $i;
			
			var $anchor = $tab.children('a').data('index', $i).on('mousedown', this._showTab.bind(this));
			// handle a11y
			$anchor.attr('role', 'button').attr('tabindex', '0').attr('aria-haspopup', true).attr('aria-expanded', false).attr('aria-controls', $tabContainer[0].id);
			$anchor.on('keydown', (function(event) {
				if (event.which === 13 || event.which === 32) {
					event.preventDefault();
					this._showTab(event);
				}
			}).bind(this));
			if ($preselect === $name || ($preselect === true && $i === 0)) {
				$anchor.trigger('mousedown');
			}
		}
		
		if ($preselect === true && this._tabs.length && !window.matchMedia('(max-width: 544px)').matches) {
			// pick the first available tab
			this._tabs[0].tab.children('a').trigger('click');
		}
		
		var $collapsible = this.element.data('collapsible');
		if ($collapsible !== undefined) {
			this.options.collapsible = $collapsible;
		}
		
		var wysiwygContainerId = elData(this.element[0], 'wysiwyg-container-id');
		if (wysiwygContainerId) {
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'reset_' + wysiwygContainerId, (function () {
				for (var i = 0, length = this._tabs.length; i < length; i++) {
					this._tabs[i].container.removeClass('active');
					this._tabs[i].tab.removeClass('active');
				}
			}).bind(this));
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
		forceOpen = (!this.options.collapsible || forceOpen === true);
		
		var $target = null;
		for (var $i = 0; $i < this._tabs.length; $i++) {
			var $current = this._tabs[$i];
			
			if ($i == $index) {
				if (!$current.tab.hasClass('active')) {
					$current.tab.addClass('active');
					$current.container.addClass('active');
					$target = $current;
					$current.tab.children('a').attr('aria-expanded', true);
					
					// if the tab contains a tab menu itself, open the first tab too,
					// unless there is already at least one open tab
					var container = $current.container[0];
					if (elBySel('.messageTabMenuContent.active', container) === null && elBySel('.messageTabMenuContent', container) !== null) {
						var link = elBySel('nav > ul > li[data-name] > a', container);
						if (link !== null) $(link).trigger('mousedown');
					}
					
					continue;
				}
				else if (forceOpen === true) {
					continue;
				}
			}
			
			$current.tab.removeClass('active');
			$current.container.removeClass('active');
			$current.tab.children('a').attr('aria-expanded', false);
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
