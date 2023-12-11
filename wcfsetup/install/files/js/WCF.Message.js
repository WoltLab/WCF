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
	 * Provides previews for CKEditor 5 message fields.
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

			this._ckeditorApi = undefined;
			require(["WoltLabSuite/Core/Component/Ckeditor"], (Ckeditor) => {
				this._ckeditorApi = Ckeditor;
			})
		},
		
		/**
		 * Reads message field input and triggers an AJAX request.
		 */
		_click: function (event) {
			event.preventDefault();
			
			var $message = this._getMessage();
			if ($message === null) {
				console.debug("[WCF.Message.Preview] Unable to access Redactor instance of '" + this._messageFieldID + "'");
				return;
			}

			if ($message.trim().length === 0) {
				const innerError = this._textarea[0].parentElement.querySelector(".innerError");
				if (innerError) {
					innerError.remove();
				}

				elInnerError(this._getCkeditor().element, WCF.Language.get("wcf.global.form.error.empty"));
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
		 * Returns parsed message from CKEditor 5 or null if the editor was not accessible.
		 *
		 * @return        string
		 */
		_getMessage: function () {
			const editor = this._getCkeditor();
			if (editor === undefined) {
				return null;
			}

			return editor.getHtml();
		},

		_getCkeditor(messageFieldId){
			return this._ckeditorApi.getCkeditorById(messageFieldId ? messageFieldId : this._messageFieldID);
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
	
	WCF.Message.I18nPreview = WCF.Message.Preview.extend({
		_activeMessageField: '',
		_dialog: null,
		_options: {},
		
		init: function (options) {
			this._activeMessageField = '';
			this._options = $.extend({
				disallowedBBCodesPermission: 'user.message.disallowedBBCodes',
				messageFields: [],
				messageObjectType: '',
				messageObjectID: 0
			}, options);
			
			if (!this._options.messageObjectType) {
				throw new Error("Field 'messageObjectType' cannot be empty.");
			}
			if (this._options.messageFields.length < 1) {
				throw new TypeError('Expected a non empty list of message field ids');
			}
			
			this._super('wcf\\data\\bbcode\\MessagePreviewAction', this._options.messageFields[0], 'buttonMessagePreview');
		},
		
		_click: function (event) {
			this._messageFieldID = '';
			this._textarea = null;
			
			// Pick the first message field that is currently visible.
			var messageFieldId = '', messageField = null;
			for (var i = 0, length = this._options.messageFields.length; i < length; i++) {
				messageFieldId = this._options.messageFields[i];
				messageField = elById(messageFieldId);
				
				// Check if the editor instance has an offset parent. If it is null, the editor is invisible.
				if (this._getCkeditor(messageFieldId).isVisible()) {
					this._messageFieldID = messageFieldId;
					this._textarea = $(messageField);
					break;
				}
			}
			
			if (this._messageFieldID === '') {
				throw new Error('Unable to identify the active message field.');
			}
			
			this._super(event);
		},
		
		_getParameters: function (message) {
			var $parameters = this._super(message);
			
			for (var key in this._options) {
				if (this._options.hasOwnProperty(key) && ['messageFields', 'messageFieldID', 'previewButtonID'].indexOf(key) === -1) {
					$parameters[key] = this._options[key];
				}
			}
			return $parameters;
		},
		
		_handleResponse: function (data) {
			require(['WoltLabSuite/Core/Ui/Dialog'], (function (UiDialog) {
				UiDialog.open(this, '<div class="htmlContent">' + data.returnValues.message + '</div>');
			}).bind(this));
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
	 * 
	 * @deprecated 5.4 Use `WoltLabSuite/Core/Ui/Message/Quote` instead
	 */
	WCF.Message.Quote.Handler = Class.extend({
		init: function (quoteManager, className, objectType, containerSelector, messageBodySelector, messageContentSelector, supportDirectInsert) {
			require(["WoltLabSuite/Core/Ui/Message/Quote"], (UiMessageQuote) => {
				new UiMessageQuote.default(
					quoteManager,
					className,
					objectType,
					containerSelector,
					messageBodySelector,
					messageContentSelector,
					supportDirectInsert,
				);
			});
		},
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
				const element = document.getElementById(
					this._editorIdAlternative ? this._editorIdAlternative : this._editorId
				);

				require(["WoltLabSuite/Core/Component/Ckeditor/Event"], ({ dispatchToCkeditor }) => {
					dispatchToCkeditor(element).insertQuote({
						author: data.quote.username,
						content: data.quote.text,
						isText: !data.quote.isFullQuote,
						link: data.quote.link,
					});
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
			if (this._supportPaste) this._buttons.insert = $('<button type="button" class="button buttonPrimary">' + WCF.Language.get('wcf.message.quote.insertAllQuotes') + '</button>').click($.proxy(this._insertSelected, this)).appendTo($formSubmit);
			this._buttons.remove = $('<button type="button" class="button">' + WCF.Language.get('wcf.message.quote.removeAllQuotes') + '</button>').click($.proxy(this._removeSelected, this)).appendTo($formSubmit);
			
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

			// close dialog
			this._dialog.wcfDialog('close');
			
			// insert all quotes
			window.setTimeout(() => {
				this._dialog.find('input.jsCheckbox:checked').each($.proxy(function (index, input) {
					this._insertQuote(null, input);
				}, this));
			}, 0);
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

			const element = document.getElementById(
				this._editorIdAlternative ? this._editorIdAlternative : this._editorId
			);

			require(["WoltLabSuite/Core/Component/Ckeditor/Event"], ({ dispatchToCkeditor }) => {
				dispatchToCkeditor(element).insertQuote({
					author,
					content: text,
					isText,
					link,
				});
			});
			
			// remove quote upon submit or upon request
			this._removeOnSubmit.push(listItem.data('quote-id'));
			
			// close dialog
			if (event !== null) {
				require(["WoltLabSuite/Core/Environment"], (function (Environment) {
					var callback = (function () {
						this._dialog.wcfDialog("close");
					}).bind(this);

					// Slightly delay the closing of the overlay, preventing some unexpected
					// changes to the scroll position on iOS.
					if (Environment.platform() === "ios") {
						window.setTimeout(callback, 100);
					} else {
						callback();
					}
				}.bind(this)));
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
		 * @deprecated 5.5 This method is no longer used since 3.0.
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
 * 
 * @deprecated	6.0 Use `WoltLabSuite/Core/Ui/Message/Share/Dialog` instead.
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
			
			var $anchor = $tab.children('a')
				.data('index', $i)
				.on('mousedown', this._showTab.bind(this))
				.on('touchstart', this._showTab.bind(this));
			// handle a11y
			$anchor.attr('role', 'button').attr('tabindex', '0').attr('aria-haspopup', true).attr('aria-expanded', false).attr('aria-controls', $tabContainer[0].id);

			const span = $tabs[$i].querySelector("span:not(.icon)");
			if (span) {
				$anchor[0].setAttribute("aria-labelledby", $(span).wcfIdentify());
			}

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
			const element = document.getElementById(wysiwygContainerId);

			require(["WoltLabSuite/Core/Component/Ckeditor/Event"], ({ listenToCkeditor }) => {
				listenToCkeditor(element).reset(() => {
					for (var i = 0, length = this._tabs.length; i < length; i++) {
						this._tabs[i].container.removeClass('active');
						this._tabs[i].tab.removeClass('active');
					}
				});
			});
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
