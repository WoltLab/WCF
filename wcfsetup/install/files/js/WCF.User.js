"use strict";

/**
 * User-related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * UserProfile namespace
 */
WCF.User.Profile = {};

/**
 * Shows the activity point list for users.
 */
WCF.User.Profile.ActivityPointList = {
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
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the WCF.User.Profile.ActivityPointList class.
	 */
	init: function() {
		if (this._didInit) {
			return;
		}
		
		this._cache = { };
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._init();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.User.Profile.ActivityPointList', $.proxy(this._init, this));
		
		this._didInit = true;
	},
	
	/**
	 * Initializes display for activity points.
	 */
	_init: function() {
		$('.activityPointsDisplay').removeClass('activityPointsDisplay').click($.proxy(this._click, this));
	},
	
	/**
	 * Shows or loads the activity point for selected user.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		event.preventDefault();
		var $userID = $(event.currentTarget).data('userID');
		
		if (this._cache[$userID] === undefined) {
			this._proxy.setOption('data', {
				actionName: 'getDetailedActivityPointList',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [ $userID ]
			});
			this._proxy.sendRequest();
		}
		else {
			this._show($userID);
		}
	},
	
	/**
	 * Displays activity points for given user.
	 * 
	 * @param	integer		userID
	 */
	_show: function(userID) {
		if (this._dialog === null) {
			this._dialog = $('<div>' + this._cache[userID] + '</div>').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.user.activityPoint')
			});
		}
		else {
			this._dialog.html(this._cache[userID]);
			this._dialog.wcfDialog('open');
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
		this._cache[data.returnValues.userID] = data.returnValues.template;
		this._show(data.returnValues.userID);
	}
};

/**
 * Provides methods to load tab menu content upon request.
 */
WCF.User.Profile.TabMenu = Class.extend({
	/**
	 * list of containers
	 * @var	object
	 */
	_hasContent: { },
	
	/**
	 * profile content
	 * @var	jQuery
	 */
	_profileContent: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * target user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes the tab menu loader.
	 * 
	 * @param	integer		userID
	 */
	init: function(userID) {
		this._profileContent = $('#profileContent');
		this._userID = userID;
		
		var $activeMenuItem = this._profileContent.data('active');
		var $enableProxy = false;
		
		// fetch content state
		this._profileContent.find('div.tabMenuContent').each($.proxy(function(index, container) {
			var $containerID = $(container).wcfIdentify();
			
			if ($activeMenuItem === $containerID) {
				this._hasContent[$containerID] = true;
			}
			else {
				this._hasContent[$containerID] = false;
				$enableProxy = true;
			}
		}, this));
		
		// enable loader if at least one container is empty
		if ($enableProxy) {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			this._profileContent.on('wcftabsbeforeactivate', $.proxy(this._loadContent, this));
			
			// check which tab is selected
			this._profileContent.find('> nav.tabMenu > ul > li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				if ($listItem.hasClass('ui-state-active')) {
					if (index) {
						this._loadContent(null, {
							newPanel: $('#' + $listItem.attr('aria-controls'))
						});
					}
					
					return false;
				}
			}, this));
		}
		
		$('.userProfileUser .contentDescription a[href$="#likes"]').click((function (event) {
			event.preventDefault();
			
			require(['Ui/TabMenu'], function (UiTabMenu) {
				UiTabMenu.getTabMenu('profileContent').select('likes');
			})
		}).bind(this))
	},
	
	/**
	 * Prepares to load content once tabs are being switched.
	 * 
	 * @param	object		event
	 * @param	object		ui
	 */
	_loadContent: function(event, ui) {
		var $panel = $(ui.newPanel);
		var $containerID = $panel.attr('id');
		
		if (!this._hasContent[$containerID]) {
			this._proxy.setOption('data', {
				actionName: 'getContent',
				className: 'wcf\\data\\user\\profile\\menu\\item\\UserProfileMenuItemAction',
				parameters: {
					data: {
						containerID: $containerID,
						menuItem: $panel.data('menuItem'),
						userID: this._userID
					}
				}
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Shows previously requested content.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		this._hasContent[$containerID] = true;
		
		// insert content, uses non jQuery because DomUtil.insertHtml() moves <script> elements
		// to the bottom of the element by default which is exactly what is required here
		require(['Dom/ChangeListener', 'Dom/Util'], function(DomChangeListener, DomUtil) {
			DomUtil.insertHtml(data.returnValues.template, elById($containerID), 'append');
			
			DomChangeListener.trigger();
		});
	}
});

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * User profile inline editor.
	 *
	 * @param        integer                userID
	 * @param        boolean                editOnInit
	 */
	WCF.User.Profile.Editor = Class.extend({
		/**
		 * current action
		 * @var        string
		 */
		_actionName: '',
		
		_active: false,
		
		/**
		 * list of interface buttons
		 * @var        object
		 */
		_buttons: {},
		
		/**
		 * cached tab content
		 * @var        string
		 */
		_cachedTemplate: '',
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * tab object
		 * @var        jQuery
		 */
		_tab: null,
		
		/**
		 * target user id
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes the WCF.User.Profile.Editor object.
		 *
		 * @param        integer                userID
		 * @param        boolean                editOnInit
		 */
		init: function (userID, editOnInit) {
			this._actionName = '';
			this._active = false;
			this._cachedTemplate = '';
			this._tab = $('#about');
			this._userID = userID;
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			this._initButtons();
			
			// begin editing on page load
			if (editOnInit) {
				this._beginEdit();
			}
		},
		
		/**
		 * Initializes interface buttons.
		 */
		_initButtons: function () {
			// create buttons
			this._buttons = {
				beginEdit: $('.jsButtonEditProfile:eq(0)').click(this._beginEdit.bind(this))
			};
		},
		
		/**
		 * Begins editing.
		 *
		 * @param       {Event?}         event   event object
		 */
		_beginEdit: function (event) {
			if (event) event.preventDefault();
			
			if (this._active) return;
			this._active = true;
			
			this._actionName = 'beginEdit';
			this._buttons.beginEdit.parent().addClass('active');
			$('#profileContent').wcfTabs('select', 'about');
			
			// load form
			this._proxy.setOption('data', {
				actionName: 'beginEdit',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [this._userID]
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Saves input values.
		 */
		_save: function () {
			require(["WoltLabSuite/Core/Component/Ckeditor"], ({ getCkeditor }) => {
				const textareas = Array.from(this._tab[0].querySelectorAll("textarea"));
				const scrollToTextarea = textareas.find((textarea) => {
					const editor = getCkeditor(textarea);
					if (editor === undefined) {
						return false;
					}

					const data = {
						api: {
							throwError: elInnerError
						},
						valid: true
					};
					WCF.System.Event.fireEvent('com.woltlab.wcf.ckeditor5', `validate_${textarea.id}`, data);

					return data.valid === false;
				});
				
				if (scrollToTextarea) {
					scrollToTextarea.parentElement.scrollIntoView({ behavior: 'smooth' });
					return;
				}
				
				this._actionName = 'save';
				
				// collect values
				var $regExp = /values\[([a-zA-Z0-9._-]+)\]/;
				var $values = {};
				this._tab.find('input, textarea, select').each(function (index, element) {
					var $element = $(element);
					var $value = null;
					
					switch ($element.getTagName()) {
						case 'input':
							var $type = $element.attr('type');
							
							if (($type === 'radio' || $type === 'checkbox') && !$element.prop('checked')) {
								return;
							}
							break;
						
						case 'textarea':
							let editor = getCkeditor(element);
							if (editor !== undefined) {
								$value = editor.getHtml();
							}
							break;
					}
					
					var $name = $element.attr('name');
					if ($regExp.test($name)) {
						var $fieldName = RegExp.$1;
						if ($value === null) $value = $element.val();
						
						// check for checkboxes
						if ($element.attr('type') === 'checkbox' && /\[\]$/.test($name)) {
							if (!Array.isArray($values[$fieldName])) {
								$values[$fieldName] = [];
							}
							
							$values[$fieldName].push($value);
						}
						else {
							$values[$fieldName] = $value;
						}
					}
				});
				
				this._proxy.setOption('data', {
					actionName: 'save',
					className: 'wcf\\data\\user\\UserProfileAction',
					objectIDs: [this._userID],
					parameters: {
						values: $values
					}
				});
				this._proxy.sendRequest();
			});
		},
		
		/**
		 * Restores back to default view.
		 */
		_restore: function () {
			this._actionName = 'restore';
			this._active = false;
			this._buttons.beginEdit.parent().removeClass('active');
			
			this._destroyEditor();
			
			this._tab.html(this._cachedTemplate).children().css({height: 'auto'});
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			switch (this._actionName) {
				case 'beginEdit':
					this._prepareEdit(data);
					break;
				
				case 'save':
					// save was successful, show parsed template
					if (data.returnValues.success) {
						this._cachedTemplate = data.returnValues.template;
						this._restore();
					}
					else {
						this._prepareEdit(data, true);
					}
					break;
			}
		},
		
		/**
		 * Prepares editing mode.
		 *
		 * @param        object                data
		 * @param        boolean                disableCache
		 */
		_prepareEdit: function (data, disableCache) {
			this._destroyEditor();
			
			// update template
			var self = this;
			this._tab.html(function (index, oldHTML) {
				if (disableCache !== true) {
					self._cachedTemplate = oldHTML;
				}
				
				return data.returnValues.template;
			});
			
			// block autocomplete
			this._tab.find('input[type=text]').attr('autocomplete', 'off');
			
			// bind event listener
			this._tab.find('.formSubmit > button[data-type=save]').click($.proxy(this._save, this));
			this._tab.find('.formSubmit > button[data-type=restore]').click($.proxy(this._restore, this));
			this._tab.find('input').keyup(function (event) {
				if (event.which === $.ui.keyCode.ENTER) {
					self._save();
					
					event.preventDefault();
					return false;
				}
			});
		},
		
		/**
		 * Destroys all editor instances within current tab.
		 */
		_destroyEditor: function () {
			require(["WoltLabSuite/Core/Component/Ckeditor"], ({ getCkeditor }) => {
				this._tab[0].querySelectorAll("textarea").forEach((textarea) => {
					const editor = getCkeditor(textarea);
					editor?.destroy();
				});
			});
		}
	});
}
else {
	WCF.User.Profile.Editor = Class.extend({
		_actionName: "",
		_active: false,
		_buttons: {},
		_cachedTemplate: "",
		_proxy: {},
		_tab: {},
		_userID: 0,
		init: function() {},
		_initButtons: function() {},
		_beginEdit: function() {},
		_save: function() {},
		_restore: function() {},
		_success: function() {},
		_prepareEdit: function() {},
		_destroyEditor: function() {}
	});
}

/**
 * Namespace for registration functions.
 */
WCF.User.Registration = {};

/**
 * Validates the password.
 * 
 * @param	jQuery		element
 * @param	jQuery		confirmElement
 * @param	object		options
 * @deprecated 6.1 use `WoltLabSuite/Core/Controller/User/Registration` instead
 */
WCF.User.Registration.Validation = Class.extend({
	/**
	 * action name
	 * @var	string
	 */
	_actionName: '',
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * confirmation input element
	 * @var	jQuery
	 */
	_confirmElement: null,
	
	/**
	 * input element
	 * @var	jQuery
	 */
	_element: null,
	
	/**
	 * list of error messages
	 * @var	object
	 */
	_errorMessages: { },
	
	/**
	 * list of additional options
	 * @var	object
	 */
	_options: { },
	
	/**
	 * AJAX proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the validation.
	 * 
	 * @param	jQuery		element
	 * @param	jQuery		confirmElement
	 * @param	object		options
	 */
	init: function(element, confirmElement, options) {
		this._element = element;
		this._element.blur($.proxy(this._blur, this));
		this._confirmElement = confirmElement || null;
		
		if (this._confirmElement !== null) {
			this._confirmElement.blur($.proxy(this._blurConfirm, this));
		}
		
		options = options || { };
		this._setOptions(options);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			showLoadingOverlay: false
		});
		
		this._setErrorMessages();
	},
	
	/**
	 * Sets additional options
	 */
	_setOptions: function(options) { },
	
	/**
	 * Sets error messages.
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: '',
			notEqual: ''
		};
	},
	
	/**
	 * Validates once focus on input is lost.
	 * 
	 * @param	object		event
	 */
	_blur: function(event) {
		var $value = this._element.val();
		if (!$value) {
			return this._showError(this._element, WCF.Language.get('wcf.global.form.error.empty'));
		}
		
		if (this._confirmElement !== null) {
			var $confirmValue = this._confirmElement.val();
			if ($confirmValue != '' && $value != $confirmValue) {
				return this._showError(this._confirmElement, this._errorMessages.notEqual);
			}
		}
		
		if (!this._validateOptions()) {
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: this._actionName,
			className: this._className,
			parameters: this._getParameters()
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Returns a list of parameters.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return { };
	},
	
	/**
	 * Validates input by options.
	 * 
	 * @return	boolean
	 */
	_validateOptions: function() {
		return true;
	},
	
	/**
	 * Validates value once confirmation input focus is lost.
	 * 
	 * @param	object		event
	 */
	_blurConfirm: function(event) {
		var $value = this._confirmElement.val();
		if (!$value) {
			return this._showError(this._confirmElement, WCF.Language.get('wcf.global.form.error.empty'));
		}
		
		this._blur(event);
	},
	
	/**
	 * Handles AJAX responses.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.isValid) {
			this._showSuccess(this._element);
			if (this._confirmElement !== null && this._confirmElement.val()) {
				this._showSuccess(this._confirmElement);
			}
		}
		else {
			this._showError(this._element, WCF.Language.get(this._errorMessages.ajaxError + data.returnValues.error));
		}
	},
	
	/**
	 * Shows an error message.
	 * 
	 * @param	jQuery		element
	 * @param	string		message
	 */
	_showError: function(element, message) {
		element.parent().parent().addClass('formError').removeClass('formSuccess');
		
		var $innerError = element.parent().find('small.innerError');
		if (!$innerError.length) {
			$innerError = $('<small />').addClass('innerError').insertAfter(element);
		}
		
		$innerError.text(message);
	},
	
	/**
	 * Displays a success message.
	 * 
	 * @param	jQuery		element
	 */
	_showSuccess: function(element) {
		element.parent().parent().addClass('formSuccess').removeClass('formError');
		element.next('small.innerError').remove();
	}
});

/**
 * Username validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 * @deprecated 6.1 use `WoltLabSuite/Core/Controller/User/Registration` instead
 */
WCF.User.Registration.Validation.Username = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validateUsername',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._setOptions()
	 */
	_setOptions: function(options) {
		this._options = $.extend(true, {
			minlength: 3,
			maxlength: 25
		}, options);
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.username.error.'
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._validateOptions()
	 */
	_validateOptions: function() {
		var $value = this._element.val();
		if ($value.length < this._options.minlength || $value.length > this._options.maxlength) {
			this._showError(this._element, WCF.Language.get('wcf.user.username.error.invalid'));
			return false;
		}
		
		return true;
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			username: this._element.val()
		};
	}
});

/**
 * Email validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 * @deprecated 6.1 use `WoltLabSuite/Core/Controller/User/Registration` instead
 */
WCF.User.Registration.Validation.EmailAddress = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validateEmailAddress',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			email: this._element.val()
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.email.error.',
			notEqual: WCF.Language.get('wcf.user.confirmEmail.error.notEqual')
		};
	}
});

/**
 * Notification system for WCF.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Notification = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles the notification list.
	 */
	WCF.Notification.List = Class.extend({
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * Initializes the WCF.Notification.List object.
		 */
		init: function () {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// handle 'mark all as confirmed' buttons
			$('.jsMarkAllAsConfirmed').click(function () {
				WCF.System.Confirmation.show(WCF.Language.get('wcf.user.notification.markAllAsConfirmed.confirmMessage'), function (action) {
					if (action === 'confirm') {
						new WCF.Action.Proxy({
							autoSend: true,
							data: {
								actionName: 'markAllAsConfirmed',
								className: 'wcf\\data\\user\\notification\\UserNotificationAction'
							},
							success: function () {
								window.location.reload();
							}
						});
					}
				});
			});
			
			// handle regular items
			this._convertList();
		},
		
		/**
		 * Converts the notification item list to be in sync with the notification dropdown.
		 */
		_convertList: function () {
			$('.userNotificationItemList > .notificationItem').each((function (index, item) {
				var $item = $(item);
				
				if (!$item.data('isRead')) {
					$item.find('a:not(.userLink)').prop('href', $item.data('link'));
					
					var $markAsConfirmed = $(`<button type="button" class="notificationItemMarkAsConfirmed jsTooltip" title="${WCF.Language.get('wcf.global.button.markAsRead')}">
						<fa-icon size="24" name="check"></fa-icon>
					</button>`).appendTo($item);
					$markAsConfirmed.click($.proxy(this._markAsConfirmed, this));
				}
			}).bind(this));
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		/**
		 * Marks a single notification as confirmed.
		 *
		 * @param        object                event
		 */
		_markAsConfirmed: function (event) {
			event.preventDefault();
			
			var $notificationID = $(event.currentTarget).parents('.notificationItem:eq(0)').data('objectID');
			
			this._proxy.setOption('data', {
				actionName: 'markAsConfirmed',
				className: 'wcf\\data\\user\\notification\\UserNotificationAction',
				objectIDs: [$notificationID]
			});
			this._proxy.sendRequest();
			
			return false;
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			var $item = $('.userNotificationItemList > .notificationItem[data-object-id=' + data.returnValues.markAsRead + ']');
			
			$item.data('isRead', true);
			$item.find('.newContentBadge').remove();
			$item.find('.notificationItemMarkAsConfirmed').remove();
			$item.removeClass('notificationUnconfirmed');
		}
	});
	
	/**
	 * Signature preview.
	 *
	 * @see        WCF.Message.Preview
	 */
	WCF.User.SignaturePreview = WCF.Message.Preview.extend({
		/**
		 * @see        WCF.Message.Preview._handleResponse()
		 */
		_handleResponse: function (data) {
			// get preview container
			var $preview = $('#previewContainer');
			if (!$preview.length) {
				$preview = $('<section class="section" id="previewContainer"><h2 class="sectionTitle">' + WCF.Language.get('wcf.global.preview') + '</h2><div class="htmlContent messageSignatureConstraints"></div></section>').insertBefore($('#signatureContainer')).wcfFadeIn();
			}
			
			$preview.children('div').first().html(data.returnValues.message);
		}
	});
}
else {
	WCF.Notification.List = Class.extend({
		_proxy: {},
		init: function() {},
		_convertList: function() {},
		_markAsConfirmed: function() {},
		_success: function() {}
	});
	
	WCF.User.SignaturePreview = WCF.Message.Preview.extend({
		_handleResponse: function() {},
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
		_failure: function() {}
	});
}

/**
 * Loads recent activity events once the user scrolls to the very bottom.
 * 
 * @param	integer		userID
 */
WCF.User.RecentActivityLoader = Class.extend({
	/**
	 * container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * true if list should be filtered by followed users
	 * @var	boolean
	 */
	_filteredByFollowedUsers: false,
	
	/**
	 * button to load next events
	 * @var	jQuery
	 */
	_loadButton: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes a new RecentActivityLoader object.
	 * 
	 * @param	integer		userID
	 * @param	boolean		filteredByFollowedUsers
	 */
	init: function(userID, filteredByFollowedUsers) {
		this._container = $('#recentActivities');
		this._filteredByFollowedUsers = (filteredByFollowedUsers === true);
		this._userID = userID;
		
		if (this._userID !== null && !this._userID) {
			console.debug("[WCF.User.RecentActivityLoader] Invalid parameter 'userID' given.");
			return;
		}
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		if (this._container.children('li').length) {
			this._loadButton = $('<li class="showMore"><button type="button" class="button small">' + WCF.Language.get('wcf.user.recentActivity.more') + '</button></li>').appendTo(this._container);
			this._loadButton = this._loadButton.children('button').click($.proxy(this._click, this));
		}
		else {
			$('<li class="showMore"><small>' + WCF.Language.get('wcf.user.recentActivity.noMoreEntries') + '</small></li>').appendTo(this._container);
		}
		
		if (WCF.User.userID) {
			$('.jsRecentActivitySwitchContext .button').click($.proxy(this._switchContext, this));
		}
	},
	
	/**
	 * Loads next activity events.
	 */
	_click: function() {
		this._loadButton.enable();
		
		var $parameters = {
			lastEventID: this._container.data('lastEventID'),
			lastEventTime: this._container.data('lastEventTime')
		};
		if (this._userID) {
			$parameters.userID = this._userID;
		}
		else if (this._filteredByFollowedUsers) {
			$parameters.filteredByFollowedUsers = 1;
		}
		
		this._proxy.setOption('data', {
			actionName: 'load',
			className: 'wcf\\data\\user\\activity\\event\\UserActivityEventAction',
			parameters: $parameters
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Switches recent activity context.
	 */
	_switchContext: function(event) {
		event.preventDefault();
		
		if (!$(event.currentTarget).hasClass('active')) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'switchContext',
					className: 'wcf\\data\\user\\activity\\event\\UserActivityEventAction'
				},
				success: function() {
					window.location.hash = '#dashboardBoxRecentActivity';
					window.location.reload();
				}
			});
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
		if (data.returnValues.template) {
			$(data.returnValues.template).insertBefore(this._loadButton.parent());
			
			this._container.data('lastEventTime', data.returnValues.lastEventTime);
			this._container.data('lastEventID', data.returnValues.lastEventID);
			this._loadButton.enable();
		}
		else {
			$('<small>' + WCF.Language.get('wcf.user.recentActivity.noMoreEntries') + '</small>').appendTo(this._loadButton.parent());
			this._loadButton.remove();
		}
	}
});

/**
 * Initializes WCF.User.Action namespace.
 */
WCF.User.Action = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles user follow and unfollow links.
	 * 
	 * @deprecated 6.1 use `WoltLabSuite/Core/Component/User/Follow` instead
	 */
	WCF.User.Action.Follow = Class.extend({
		/**
		 * list with elements containing follow and unfollow buttons
		 * @var        array
		 */
		_containerList: null,
		
		/**
		 * CSS selector for follow buttons
		 * @var        string
		 */
		_followButtonSelector: '.jsFollowButton',
		
		/**
		 * id of the user that is currently being followed/unfollowed
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes new WCF.User.Action.Follow object.
		 *
		 * @param        array                containerList
		 * @param        string                followButtonSelector
		 */
		init: function (containerList, followButtonSelector) {
			if (!containerList.length) {
				return;
			}
			this._containerList = containerList;
			
			if (followButtonSelector) {
				this._followButtonSelector = followButtonSelector;
			}
			
			// initialize proxy
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// bind event listeners
			this._containerList.each($.proxy(function (index, container) {
				$(container).find(this._followButtonSelector).click($.proxy(this._click, this));
			}, this));
		},
		
		/**
		 * Handles a click on a follow or unfollow button.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			event.preventDefault();
			var link = $(event.target);
			if (!link.is('a')) {
				link = link.closest('a');
			}
			this._userID = link.data('objectID');
			
			this._proxy.setOption('data', {
				'actionName': link.data('following') ? 'unfollow' : 'follow',
				'className': 'wcf\\data\\user\\follow\\UserFollowAction',
				'parameters': {
					data: {
						userID: this._userID
					}
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Handles the successful (un)following of a user.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			this._containerList.each($.proxy(function (index, container) {
				var button = $(container).find(this._followButtonSelector).get(0);
				
				if (button && $(button).data('objectID') == this._userID) {
					button = $(button);
					
					// toogle icon title
					if (data.returnValues.following) {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.unfollow'));
						button[0].querySelector("fa-icon").setIcon("circle-minus");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.unfollow'));
					}
					else {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.follow'));
						button[0].querySelector("fa-icon").setIcon("circle-plus");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.follow'));
					}
					
					button.data('following', data.returnValues.following);
					
					return false;
				}
			}, this));
			
			var $notification = new WCF.System.Notification();
			$notification.show();
		}
	});
	
	/**
	 * Handles user ignore and unignore links.
	 * 
	 * @deprecated 5.4 Use a FormBuilderDialog for wcf\data\user\ignore\UserIgnoreAction::getDialog()
	 */
	WCF.User.Action.Ignore = Class.extend({
		/**
		 * list with elements containing ignore and unignore buttons
		 * @var        array
		 */
		_containerList: null,
		
		/**
		 * CSS selector for ignore buttons
		 * @var        string
		 */
		_ignoreButtonSelector: '.jsIgnoreButton',
		
		/**
		 * id of the user that is currently being ignored/unignored
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes new WCF.User.Action.Ignore object.
		 *
		 * @param        array                containerList
		 * @param        string                ignoreButtonSelector
		 */
		init: function (containerList, ignoreButtonSelector) {
			if (!containerList.length) {
				return;
			}
			this._containerList = containerList;
			
			if (ignoreButtonSelector) {
				this._ignoreButtonSelector = ignoreButtonSelector;
			}
			
			// initialize proxy
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// bind event listeners
			this._containerList.each($.proxy(function (index, container) {
				$(container).find(this._ignoreButtonSelector).click($.proxy(this._click, this));
			}, this));
		},
		
		/**
		 * Handles a click on a ignore or unignore button.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			event.preventDefault();
			var link = $(event.target);
			if (!link.is('a')) {
				link = link.closest('a');
			}
			this._userID = link.data('objectID');
			
			this._proxy.setOption('data', {
				'actionName': link.data('ignored') ? 'unignore' : 'ignore',
				'className': 'wcf\\data\\user\\ignore\\UserIgnoreAction',
				'parameters': {
					data: {
						userID: this._userID
					}
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Handles the successful (un)ignoring of a user.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			this._containerList.each($.proxy(function (index, container) {
				var button = $(container).find(this._ignoreButtonSelector).get(0);
				
				if (button && $(button).data('objectID') == this._userID) {
					button = $(button);
					
					// toogle icon title
					if (data.returnValues.isIgnoredUser) {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.unignore'));
						button[0].querySelector("fa-icon").setIcon("circle");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.unignore'));
					}
					else {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.ignore'));
						button[0].querySelector("fa-icon").setIcon("ban");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.ignore'));
					}
					
					button.data('ignored', data.returnValues.isIgnoredUser);
					
					return false;
				}
			}, this));
			
			var $notification = new WCF.System.Notification();
			$notification.show();
		}
	});
}
else {
	WCF.User.Action.Follow = Class.extend({
		_containerList: {},
		_followButtonSelector: "",
		_userID: 0,
		init: function() {},
		_click: function() {},
		_success: function() {}
	});
	
	/**
	 * @deprecated
	 */
	WCF.User.Action.Ignore = Class.extend({
		_containerList: {},
		_ignoreButtonSelector: "",
		_userID: 0,
		init: function() {},
		_click: function() {},
		_success: function() {}
	});
}

/**
 * Namespace for avatar functions.
 */
WCF.User.Avatar = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Avatar upload function
	 *
	 * @see        WCF.Upload
	 */
	WCF.User.Avatar.Upload = WCF.Upload.extend({
		/**
		 * user id of avatar owner
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes a new WCF.User.Avatar.Upload object.
		 *
		 * @param        integer                        userID
		 */
		init: function (userID) {
			this._super($('#avatarUpload > dd > div'), undefined, 'wcf\\data\\user\\avatar\\UserAvatarAction');
			this._userID = userID || 0;
			
			$('#avatarForm input[type=radio]').change(function () {
				if ($(this).val() == 'custom') {
					$('#avatarUpload > dd > div').show();
				}
				else {
					$('#avatarUpload > dd > div').hide();
				}
			});
			if (!$('#avatarForm input[type=radio][value=custom]:checked').length) {
				$('#avatarUpload > dd > div').hide();
			}
		},
		
		/**
		 * @see        WCF.Upload._initFile()
		 */
		_initFile: function (file) {
			return $('#avatarUpload > dt > img');
		},
		
		/**
		 * @see        WCF.Upload._success()
		 */
		_success: function (uploadID, data) {
			if (data.returnValues.url) {
				this._updateImage(data.returnValues.url);
				
				// hide error
				$('#avatarUpload > dd > .innerError').remove();
				
				// show success message
				var $notification = new WCF.System.Notification(WCF.Language.get('wcf.user.avatar.upload.success'));
				$notification.show();
			}
			else if (data.returnValues.errorType) {
				// show error
				this._getInnerErrorElement().text(WCF.Language.get('wcf.user.avatar.upload.error.' + data.returnValues.errorType));
			}
		},
		
		/**
		 * Updates the displayed avatar image.
		 *
		 * @param        string                url
		 */
		_updateImage: function (url) {
			$('#avatarUpload > dt > img').remove();
			var $image = $('<img src="' + url + '" class="userAvatarImage" alt="" />').css({
				'height': 'auto',
				'max-height': '96px',
				'max-width': '96px',
				'width': 'auto'
			});
			
			$('#avatarUpload > dt').prepend($image);
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		/**
		 * Returns the inner error element.
		 *
		 * @return        jQuery
		 */
		_getInnerErrorElement: function () {
			var $span = $('#avatarUpload > dd > .innerError');
			if (!$span.length) {
				$span = $('<small class="innerError"></span>');
				$('#avatarUpload > dd').append($span);
			}
			
			return $span;
		},
		
		/**
		 * @see        WCF.Upload._getParameters()
		 */
		_getParameters: function () {
			return {
				userID: this._userID
			};
		}
	});
}
else {
	WCF.User.Avatar.Upload = WCF.Upload.extend({
		_userID: 0,
		init: function() {},
		_initFile: function() {},
		_success: function() {},
		_updateImage: function() {},
		_getInnerErrorElement: function() {},
		_getParameters: function() {},
		_name: "",
		_buttonSelector: {},
		_fileListSelector: {},
		_fileUpload: {},
		_className: "",
		_iframe: {},
		_internalFileID: 0,
		_options: {},
		_uploadMatrix: {},
		_supportsAJAXUpload: true,
		_overlay: {},
		_createButton: function() {},
		_insertButton: function() {},
		_removeButton: function() {},
		_upload: function() {},
		_createUploadMatrix: function() {},
		_error: function() {},
		_progress: function() {},
		_showOverlay: function() {},
		_evaluateResponse: function() {},
		_getFilename: function() {}
	});
}

/**
 * Generic implementation for grouped user lists.
 * 
 * @param	string		className
 * @param	string		dialogTitle
 * @param	object		additionalParameters
 * @deprecated 6.0 use `WoltLabSuite/Core/Component/User/List` instead
 */
WCF.User.List = Class.extend({
	/**
	 * list of additional parameters
	 * @var	object
	 */
	_additionalParameters: { },
	
	/**
	 * list of cached pages
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * dialog title
	 * @var	string
	 */
	_dialogTitle: '',
	
	/**
	 * page count
	 * @var	integer
	 */
	_pageCount: 0,
	
	/**
	 * current page no
	 * @var	integer
	 */
	_pageNo: 1,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes a new grouped user list.
	 * 
	 * @param	string		className
	 * @param	string		dialogTitle
	 * @param	object		additionalParameters
	 */
	init: function(className, dialogTitle, additionalParameters) {
		this._additionalParameters = additionalParameters || { };
		this._cache = { };
		this._className = className;
		this._dialog = null;
		this._dialogTitle = dialogTitle;
		this._pageCount = 0;
		this._pageNo = 1;
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Opens the dialog overlay.
	 */
	open: function() {
		this._pageNo = 1;
		this._showPage();
	},
	
	/**
	 * Displays the specified page.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_showPage: function(event, data) {
		if (data && data.activePage) {
			this._pageNo = data.activePage;
		}
		
		if (this._pageCount != 0 && (this._pageNo < 1 || this._pageNo > this._pageCount)) {
			console.debug("[WCF.User.List] Cannot access page " + this._pageNo + " of " + this._pageCount);
			return;
		}
		
		if (this._cache[this._pageNo]) {
			var $dialogCreated = false;
			if (this._dialog === null) {
				this._dialog = $('#userList' + this._className.hashCode());
				if (this._dialog.length === 0) {
					this._dialog = $('<div id="userList' + this._className.hashCode() + '" />').hide().appendTo(document.body);
					$dialogCreated = true;
				}
			}
			
			// remove current view
			this._dialog.empty();
			
			// insert HTML
			this._dialog.html(this._cache[this._pageNo]);
			
			// add pagination
			if (this._pageCount > 1) {
				this._dialog.find('.jsPagination').wcfPages({
					activePage: this._pageNo,
					maxPage: this._pageCount
				}).on('wcfpagesswitched', $.proxy(this._showPage, this));
			}
			else {
				this._dialog.find('.jsPagination').hide();
			}
			
			// show dialog
			if ($dialogCreated) {
				this._dialog.wcfDialog({
					title: this._dialogTitle
				});
			}
			else {
				this._dialog.wcfDialog('option', 'title', this._dialogTitle);
				this._dialog.wcfDialog('open').wcfDialog('render');
			}
			
			WCF.DOMNodeInsertedHandler.execute();
		}
		else {
			this._additionalParameters.pageNo = this._pageNo;
			
			// load template via AJAX
			this._proxy.setOption('data', {
				actionName: 'getGroupedUserList',
				className: this._className,
				interfaceName: 'wcf\\data\\IGroupedUserListAction',
				parameters: this._additionalParameters
			});
			this._proxy.sendRequest();
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
		if (data.returnValues.pageCount) {
			this._pageCount = data.returnValues.pageCount;
		}
		
		this._cache[this._pageNo] = data.returnValues.template;
		this._showPage();
	}
});

/**
 * Namespace for object watch functions.
 */
WCF.User.ObjectWatch = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles subscribe/unsubscribe links.
	 * 
	 * @deprecated	since 6.0, use `WoltLabSuite/Core/Ui/User/ObjectWatch` instead.
	 */
	WCF.User.ObjectWatch.Subscribe = Class.extend({
		/**
		 * CSS selector for subscribe buttons
		 * @var        string
		 */
		_buttonSelector: '.jsSubscribeButton',
		
		/**
		 * list of buttons
		 * @var        object
		 */
		_buttons: {},
		
		/**
		 * dialog overlay
		 * @var        object
		 */
		_dialog: null,
		
		/**
		 * system notification
		 * @var        WCF.System.Notification
		 */
		_notification: null,
		
		/**
		 * reload page on unsubscribe
		 * @var        boolean
		 */
		_reloadOnUnsubscribe: false,
		
		/**
		 * WCF.User.ObjectWatch.Subscribe object.
		 *
		 * @param        boolean                reloadOnUnsubscribe
		 */
		init: function (reloadOnUnsubscribe) {
			this._buttons = {};
			this._notification = null;
			this._reloadOnUnsubscribe = (reloadOnUnsubscribe === true);
			
			// initialize proxy
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// bind event listeners
			$(this._buttonSelector).each($.proxy(function (index, button) {
				var $button = $(button);
				$button.addClass('pointer');
				var $objectType = $button.data('objectType');
				var $objectID = $button.data('objectID');
				
				if (this._buttons[$objectType] === undefined) {
					this._buttons[$objectType] = {};
				}
				
				this._buttons[$objectType][$objectID] = $button.click($.proxy(this._click, this));
			}, this));
			
			WCF.System.Event.addListener('com.woltlab.wcf.objectWatch', 'update', $.proxy(this._updateSubscriptionStatus, this));
		},
		
		/**
		 * Handles a click on a subscribe button.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			event.preventDefault();
			var $button = $(event.currentTarget);
			
			this._proxy.setOption('data', {
				actionName: 'manageSubscription',
				className: 'wcf\\data\\user\\object\\watch\\UserObjectWatchAction',
				parameters: {
					objectID: $button.data('objectID'),
					objectType: $button.data('objectType')
				}
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
			if (data.actionName === 'manageSubscription') {
				if (this._dialog === null) {
					this._dialog = $('<div>' + data.returnValues.template + '</div>').hide().appendTo(document.body);
					this._dialog.wcfDialog({
						title: WCF.Language.get('wcf.user.objectWatch.manageSubscription')
					});
				}
				else {
					this._dialog.html(data.returnValues.template);
					this._dialog.wcfDialog('open');
				}
				
				// bind event listener
				this._dialog.find('.formSubmit > .jsButtonSave').data('objectID', data.returnValues.objectID).data('objectType', data.returnValues.objectType).click($.proxy(this._save, this));
				var $enableNotification = this._dialog.find('input[name=enableNotification]').disable();
				
				// toggle subscription
				this._dialog.find('input[name=subscribe]').change(function (event) {
					var $input = $(event.currentTarget);
					if ($input.val() == 1) {
						$enableNotification.enable();
					}
					else {
						$enableNotification.disable();
					}
				});
				
				// setup
				var $selectedOption = this._dialog.find('input[name=subscribe]:checked');
				if ($selectedOption.length && $selectedOption.val() == 1) {
					$enableNotification.enable();
				}
			}
			else if (data.actionName === 'saveSubscription' && this._dialog.is(':visible')) {
				this._dialog.wcfDialog('close');
				
				this._updateSubscriptionStatus({
					isSubscribed: data.returnValues.subscribe,
					objectID: data.returnValues.objectID
				});
				
				
				// show notification
				if (this._notification === null) {
					this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
				}
				
				this._notification.show();
			}
		},
		
		/**
		 * Saves the subscription.
		 *
		 * @param        object                event
		 */
		_save: function (event) {
			var $button = this._buttons[$(event.currentTarget).data('objectType')][$(event.currentTarget).data('objectID')];
			var $subscribe = this._dialog.find('input[name=subscribe]:checked').val();
			var $enableNotification = (this._dialog.find('input[name=enableNotification]').is(':checked')) ? 1 : 0;
			
			this._proxy.setOption('data', {
				actionName: 'saveSubscription',
				className: 'wcf\\data\\user\\object\\watch\\UserObjectWatchAction',
				parameters: {
					enableNotification: $enableNotification,
					objectID: $button.data('objectID'),
					objectType: $button.data('objectType'),
					subscribe: $subscribe
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Updates subscription status and icon.
		 *
		 * @param        object                data
		 */
		_updateSubscriptionStatus: function (data) {
			var $button = $(this._buttonSelector + '[data-object-id=' + data.objectID + ']');
			var $icon = $button.children('.icon');
			if (data.isSubscribed) {
				$icon[0].querySelector("fa-icon").setIcon("bookmark", true);
				$button.data('isSubscribed', true);
				$button.addClass('active');
			}
			else {
				if ($button.data('removeOnUnsubscribe')) {
					$button.parent().remove();
				}
				else {
					$icon[0].querySelector("fa-icon").setIcon("bookmark");
					$button.data('isSubscribed', false);
					$button.removeClass('active');
				}
				
				if (this._reloadOnUnsubscribe) {
					window.location.reload();
					return;
				}
			}
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.objectWatch', 'updatedSubscription', data);
		}
	});
}
else {
	WCF.User.ObjectWatch.Subscribe = Class.extend({
		_buttonSelector: "",
		_buttons: {},
		_dialog: {},
		_notification: {},
		_reloadOnUnsubscribe: false,
		init: function() {},
		_click: function() {},
		_success: function() {},
		_save: function() {},
		_updateSubscriptionStatus: function() {}
	});
}
