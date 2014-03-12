/**
 * Namespace for moderation related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Moderation = { };

/**
 * Moderation queue management.
 * 
 * @param	integer		queueID
 * @param	string		redirectURL
 */
WCF.Moderation.Management = Class.extend({
	/**
	 * button selector
	 * @var	string
	 */
	_buttonSelector: '',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * list of templates for confirmation message by action name
	 * @var	object
	 */
	_confirmationTemplate: { },
	
	/**
	 * language item pattern
	 * @var	string
	 */
	_languageItem: '',
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * queue id
	 * @var	integer
	 */
	_queueID: 0,
	
	/**
	 * redirect URL
	 * @var	string
	 */
	_redirectURL: '',
	
	/**
	 * Initializes the moderation report management.
	 * 
	 * @param	integer		queueID
	 * @param	string		redirectURL
	 * @param	string		languageItem
	 */
	init: function(queueID, redirectURL, languageItem) {
		if (!this._buttonSelector) {
			console.debug("[WCF.Moderation.Management] Missing button selector, aborting.");
			return;
		}
		else if (!this._className) {
			console.debug("[WCF.Moderation.Management] Missing class name, aborting.");
			return;
		}
		
		this._queueID = queueID;
		this._redirectURL = redirectURL;
		this._languageItem = languageItem;
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$(this._buttonSelector).click($.proxy(this._click, this));
	},
	
	/**
	 * Handles clicks on the action buttons.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $actionName = $(event.currentTarget).wcfIdentify();
		var $innerTemplate = '';
		if (this._confirmationTemplate[$actionName]) {
			$innerTemplate = this._confirmationTemplate[$actionName];
		}
		
		WCF.System.Confirmation.show(WCF.Language.get(this._languageItem.replace(/{actionName}/, $actionName)), $.proxy(function(action) {
			if (action === 'confirm') {
				var $parameters = {
					actionName: $actionName,
					className: this._className,
					objectIDs: [ this._queueID ]
				};
				if (this._confirmationTemplate[$actionName]) {
					$parameters.parameters = { };
					$innerTemplate.find('input, textarea').each(function(index, element) {
						var $element = $(element);
						var $value = $element.val();
						if ($element.getTagName() === 'input' && $element.attr('type') === 'checkbox') {
							if (!$element.is(':checked')) {
								$value = null;
							}
						}
						
						if ($value !== null) {
							$parameters.parameters[$element.attr('name')] = $value;
						}
					});
				}
				
				this._proxy.setOption('data', $parameters);
				this._proxy.sendRequest();
				
				$(this._buttonSelector).disable();
			}
		}, this), { }, $innerTemplate);
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
		var self = this;
		$notification.show(function() {
			window.location = self._redirectURL;
		});
	}
});

/**
 * Namespace for activation related classes.
 */
WCF.Moderation.Activation = { };

/**
 * Manages disabled content within moderation.
 * 
 * @see	WCF.Moderation.Management
 */
WCF.Moderation.Activation.Management = WCF.Moderation.Management.extend({
	/**
	 * @see	WCF.Moderation.Management.init()
	 */
	init: function(queueID, redirectURL) {
		this._buttonSelector = '#enableContent, #removeContent';
		this._className = 'wcf\\data\\moderation\\queue\\ModerationQueueActivationAction';
		
		this._super(queueID, redirectURL, 'wcf.moderation.activation.{actionName}.confirmMessage');
	}
});

/**
 * Namespace for report related classes.
 */
WCF.Moderation.Report = { };

/**
 * Handles content report.
 * 
 * @param	string		objectType
 * @param	string		buttonSelector
 */
WCF.Moderation.Report.Content = Class.extend({
	/**
	 * list of buttons
	 * @var	object
	 */
	_buttons: { },
	
	/**
	 * button selector
	 * @var	string
	 */
	_buttonSelector: '',
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
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
	 * Creates a new WCF.Moderation.Report object.
	 * 
	 * @param	string		objectType
	 * @param	string		buttonSelector
	 */
	init: function(objectType, buttonSelector) {
		this._objectType = objectType;
		this._buttonSelector = buttonSelector;
		
		this._buttons = { };
		this._notification = null;
		this._objectID = 0;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initButtons();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Moderation.Report' + this._objectType.hashCode(), $.proxy(this._initButtons, this));
	},
	
	/**
	 * Initializes the report feature for all matching buttons.
	 */
	_initButtons: function() {
		var self = this;
		$(this._buttonSelector).each(function(index, button) {
			var $button = $(button);
			var $buttonID = $button.wcfIdentify();
			
			if (!self._buttons[$buttonID]) {
				self._buttons[$buttonID] = $button;
				$button.click($.proxy(self._click, self));
			}
		});
	},
	
	/**
	 * Handles clicks on a report button.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._objectID = $(event.currentTarget).data('objectID');
		
		this._proxy.setOption('data', {
			actionName: 'prepareReport',
			className: 'wcf\\data\\moderation\\queue\\ModerationQueueReportAction',
			parameters: {
				objectID: this._objectID,
				objectType: this._objectType
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
		// object has been successfully reported
		if (data.returnValues.reported) {
			if (this._notification === null) {
				this._notification = new WCF.System.Notification(WCF.Language.get('wcf.moderation.report.success'));
			}
			
			// show success and close dialog
			this._dialog.wcfDialog('close');
			this._notification.show();
		}
		else if (data.returnValues.template) {
			// display template
			this._showDialog(data.returnValues.template);
			
			if (!data.returnValues.alreadyReported) {
				// bind event listener for buttons
				this._dialog.find('.jsSubmitReport').click($.proxy(this._submit, this));
			}
		}
	},
	
	/**
	 * Displays the dialog overlay.
	 * 
	 * @param	string		template
	 */
	_showDialog: function(template) {
		if (this._dialog === null) {
			this._dialog = $('#moderationReport');
			if (!this._dialog.length) {
				this._dialog = $('<div id="moderationReport" />').hide().appendTo(document.body);
			}
		}
		
		this._dialog.html(template).wcfDialog({
			title: WCF.Language.get('wcf.moderation.report.reportContent')
		}).wcfDialog('render');
	},
	
	/**
	 * Submits a report unless the textarea is empty.
	 */
	_submit: function() {
		var $text = this._dialog.find('.jsReportMessage').val();
		if ($text == '') {
			this._dialog.find('fieldset > dl').addClass('formError');
			
			if (!this._dialog.find('.innerError').length) {
				this._dialog.find('.jsReportMessage').after($('<small class="innerError">' + WCF.Language.get('wcf.global.form.error.empty') + "</small>"));;
			}
			
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: 'report',
			className: 'wcf\\data\\moderation\\queue\\ModerationQueueReportAction',
			parameters: {
				message: $text,
				objectID: this._objectID,
				objectType: this._objectType
			}
		});
		this._proxy.sendRequest();
	}
});

/**
 * Manages reported content within moderation.
 * 
 * @see	WCF.Moderation.Management
 */
WCF.Moderation.Report.Management = WCF.Moderation.Management.extend({
	/**
	 * @see	WCF.Moderation.Management.init()
	 */
	init: function(queueID, redirectURL) {
		this._buttonSelector = '#removeContent, #removeReport';
		this._className = 'wcf\\data\\moderation\\queue\\ModerationQueueReportAction';
		
		this._super(queueID, redirectURL, 'wcf.moderation.report.{actionName}.confirmMessage');
		
		this._confirmationTemplate.removeContent = $('<fieldset><dl><dt><label for="message">' + WCF.Language.get('wcf.moderation.report.removeContent.reason') + '</label></dt><dd><textarea name="message" id="message" cols="40" rows="3" /></dd></dl></fieldset>');
	}
});

/**
 * Provides a dropdown for user panel.
 * 
 * @see	WCF.UserPanel
 */
WCF.Moderation.UserPanel = WCF.UserPanel.extend({
	/**
	 * link to show all outstanding queues
	 * @var	string
	 */
	_showAllLink: '',
	
	/**
	 * link to deleted content list
	 * @var	string
	 */
	_deletedContentLink: '',
	
	/**
	 * @see	WCF.UserPanel.init()
	 */
	init: function(showAllLink, deletedContentLink) {
		this._noItems = 'wcf.moderation.noMoreItems';
		this._showAllLink = showAllLink;
		this._deletedContentLink = deletedContentLink;
		
		this._super('outstandingModeration');
	},
	
	/**
	 * @see	WCF.UserPanel._addDefaultItems()
	 */
	_addDefaultItems: function(dropdownMenu) {
		this._addDivider(dropdownMenu);
		$('<li><a href="' + this._showAllLink + '">' + WCF.Language.get('wcf.moderation.showAll') + '</a></li>').appendTo(dropdownMenu);
		this._addDivider(dropdownMenu);
		$('<li><a href="' + this._deletedContentLink + '">' + WCF.Language.get('wcf.moderation.showDeletedContent') + '</a></li>').appendTo(dropdownMenu);
	},
	
	/**
	 * @see	WCF.UserPanel._getParameters()
	 */
	_getParameters: function() {
		return {
			actionName: 'getOutstandingQueues',
			className: 'wcf\\data\\moderation\\queue\\ModerationQueueAction'
		};
	}
});
