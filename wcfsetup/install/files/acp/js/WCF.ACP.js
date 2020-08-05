/**
 * Class and function collection for WCF ACP
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Initialize WCF.ACP namespace
 */
WCF.ACP = { };

/**
 * Namespace for ACP application management.
 */
WCF.ACP.Application = { };

/**
 * Namespace for ACP cronjob management.
 */
WCF.ACP.Cronjob = { };

/**
 * Handles the manual execution of cronjobs.
 */
WCF.ACP.Cronjob.ExecutionHandler = Class.extend({
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes WCF.ACP.Cronjob.ExecutionHandler object.
	 */
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('.jsCronjobRow .jsExecuteButton').click($.proxy(this._click, this));
		
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'), 'success');
	},
	
	/**
	 * Handles a click on an execute button.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._proxy.setOption('data', {
			actionName: 'execute',
			className: 'wcf\\data\\cronjob\\CronjobAction',
			objectIDs: [ $(event.target).data('objectID') ]
		});
		
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful cronjob execution.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		$('.jsCronjobRow').each($.proxy(function(index, row) {
			var $button = $(row).find('.jsExecuteButton');
			var $objectID = ($button).data('objectID');
			
			if (WCF.inArray($objectID, data.objectIDs)) {
				if (data.returnValues[$objectID]) {
					// insert feedback here
					$(row).find('td.columnNextExec').html(data.returnValues[$objectID].formatted);
					$(row).wcfHighlight();
				}
				
				this._notification.show();
				
				return false;
			}
		}, this));
	}
});

/**
 * Handles the cronjob log list.
 */
WCF.ACP.Cronjob.LogList = Class.extend({
	/**
	 * error message dialog
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Initializes WCF.ACP.Cronjob.LogList object.
	 */
	init: function() {
		// bind event listener to delete cronjob log button
		$('.jsCronjobLogDelete').click(function() {
			WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.cronjob.log.clear.confirm'), function(action) {
				if (action == 'confirm') {
					new WCF.Action.Proxy({
						autoSend: true,
						data: {
							actionName: 'clearAll',
							className: 'wcf\\data\\cronjob\\log\\CronjobLogAction'
						},
						success: function() {
							window.location.reload();
						}
					});
				}
			});
		});
		
		// bind event listeners to error badges
		$('.jsCronjobError').click($.proxy(this._showError, this));
	},
	
	/**
	 * Shows certain error message
	 * 
	 * @param	object		event
	 */
	_showError: function(event) {
		var $errorBadge = $(event.currentTarget);
		
		if (this._dialog === null) {
			this._dialog = $('<div style="overflow: auto"><pre>' + $errorBadge.next().html() + '</pre></div>').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.acp.cronjob.log.error.details')
			});
		}
		else {
			this._dialog.html('<pre>' + $errorBadge.next().html() + '</pre>');
			this._dialog.wcfDialog('open');
		}
	}
});

/**
 * Namespace for ACP package management.
 */
WCF.ACP.Package = { };

/**
 * Provides the package installation.
 * 
 * @param	integer		queueID
 * @param	string		actionName
 */
WCF.ACP.Package.Installation = Class.extend({
	/**
	 * package installation type
	 * @var	string
	 */
	_actionName: 'InstallPackage',
	
	/**
	 * additional parameters send in all requests
	 * @var	object
	 */
	_additionalRequestParameters: {},
	
	/**
	 * true, if rollbacks are supported
	 * @var	boolean
	 */
	_allowRollback: false,
	
	/**
	 * dialog object
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * name of the language item with the title of the dialog
	 * @var	string
	 */
	_dialogTitle: '',
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * package installation queue id
	 * @var	integer
	 */
	_queueID: 0,
	
	/**
	 * true, if dialog should be rendered again
	 * @var	boolean
	 */
	_shouldRender: false,
	
	/**
	 * Initializes the WCF.ACP.Package.Installation class.
	 * 
	 * @param	integer		queueID
	 * @param	string		actionName
	 * @param	boolean		allowRollback
	 * @param	boolean		isUpdate
	 * @param	object		additionalRequestParameters
	 */
	init: function(queueID, actionName, allowRollback, isUpdate, additionalRequestParameters) {
		this._actionName = (actionName) ? actionName : 'InstallPackage';
		this._allowRollback = (allowRollback === true);
		this._queueID = queueID;
		this._additionalRequestParameters = additionalRequestParameters || {};
		
		this._dialogTitle = 'wcf.acp.package.' + (isUpdate ? 'update' : 'install') + '.title';
		if (this._actionName === 'UninstallPackage') {
			this._dialogTitle = 'wcf.acp.package.uninstallation.title';
		}
		
		this._initProxy();
		this._init();
	},
	
	/**
	 * Initializes the WCF.Action.Proxy object.
	 */
	_initProxy: function() {
		var $actionName = '';
		var $parts = this._actionName.split(/([A-Z][a-z0-9]+)/);
		for (var $i = 0, $length = $parts.length; $i < $length; $i++) {
			var $part = $parts[$i];
			if ($part.length) {
				if ($actionName.length) $actionName += '-';
				$actionName += $part.toLowerCase();
			}
		}
		
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php?' + $actionName + '/&t=' + SECURITY_TOKEN
		});
	},
	
	/**
	 * Initializes the package installation.
	 */
	_init: function() {
		$('#submitButton').click($.proxy(this.prepareInstallation, this));
	},
	
	/**
	 * Handles erroneous AJAX requests.
	 */
	_failure: function() {
		if (this._dialog !== null) {
			$('#packageInstallationProgress').removeAttr('value');
			this._setIcon('times');
		}
		
		if (!this._allowRollback) {
			return;
		}
		
		if (this._dialog !== null) {
			this._purgeTemplateContent($.proxy(function() {
				var $form = $('<div class="formSubmit" />').appendTo($('#packageInstallationInnerContent'));
				$('<button class="buttonPrimary">' + WCF.Language.get('wcf.acp.package.installation.rollback') + '</button>').appendTo($form).click($.proxy(this._rollback, this));
				
				$('#packageInstallationInnerContentContainer').show();
				
				this._dialog.wcfDialog('render');
			}, this));
		}
	},
	
	/**
	 * Performs a rollback.
	 * 
	 * @param	object		event
	 */
	_rollback: function(event) {
		this._setIcon('spinner');
		
		if (event) {
			$(event.currentTarget).disable();
		}
		
		this._executeStep('rollback');
	},
	
	/**
	 * Prepares installation dialog.
	 */
	prepareInstallation: function() {
		if (document.activeElement) {
			document.activeElement.blur();
		}
		
		this._proxy.setOption('data', this._getParameters());
		this._proxy.sendRequest();
	},
	
	/**
	 * Returns parameters to prepare installation.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return $.extend({}, this._additionalRequestParameters, {
			queueID: this._queueID,
			step: 'prepare'
		});
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._shouldRender = false;
		
		if (typeof window._trackPackageStep === 'function') window._trackPackageStep(this._actionName, data);
		
		if (this._dialog === null) {
			this._dialog = $('<div id="package' + (this._actionName === 'UninstallPackage' ? 'Uni' : 'I') + 'nstallationDialog" />').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				closable: false,
				title: WCF.Language.get(this._dialogTitle)
			});
		}
		
		this._setIcon('spinner');
		
		if (data.step == 'rollback') {
			this._dialog.wcfDialog('close');
			this._dialog.remove();
			
			new WCF.PeriodicalExecuter(function(pe) {
				pe.stop();
				
				var $uninstallation = new WCF.ACP.Package.Uninstallation();
				$uninstallation.start(data.packageID);
			}, 200);
			
			return;
		}
		
		// receive new queue id
		if (data.queueID) {
			this._queueID = data.queueID;
		}
		
		// update template
		if (data.template && !data.ignoreTemplate) {
			this._dialog.html(data.template);
			this._shouldRender = true;
		}
		
		// update progress
		if (data.progress) {
			$('#packageInstallationProgress').attr('value', data.progress).text(data.progress + '%');
			$('#packageInstallationProgressLabel').text(data.progress + '%');
		}
		
		// update action
		if (data.currentAction) {
			$('#packageInstallationAction').html(data.currentAction);
		}
		
		// handle success
		if (data.step === 'success') {
			this._setIcon('check');
			
			this._purgeTemplateContent($.proxy(function() {
				var $form = $('<div class="formSubmit" />').appendTo($('#packageInstallationInnerContent'));
				var $button = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.next') + '</button>').appendTo($form).click(function() {
					$(this).disable();
					window.location = data.redirectLocation;
				});
				
				$('#packageInstallationInnerContentContainer').show();
				
				$(document).keydown(function(event) {
					if (event.which === $.ui.keyCode.ENTER) {
						$button.trigger('click');
					}
				});
				
				this._dialog.wcfDialog('render');
			}, this));
			
			return;
		}
		
		// handle inner template
		if (data.innerTemplate) {
			var self = this;
			$('#packageInstallationInnerContent').html(data.innerTemplate).find('input').keyup(function(event) {
				if (event.keyCode === $.ui.keyCode.ENTER) {
					self._submit(data);
				}
			});
			
			// create button to handle next step
			if (data.step && data.node) {
				$('#packageInstallationProgress').removeAttr('value');
				this._setIcon('question');
				
				var $form = $('<div class="formSubmit" />').appendTo($('#packageInstallationInnerContent'));
				$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.next') + '</button>').appendTo($form).click($.proxy(function(event) {
					$(event.currentTarget).disable();
					
					this._submit(data);
				}, this));
			}
			
			$('#packageInstallationInnerContentContainer').show();
			
			this._dialog.wcfDialog('render');
			return;
		}
		
		// purge content
		this._purgeTemplateContent($.proxy(function() {
			// render container
			if (this._shouldRender) {
				this._dialog.wcfDialog('render');
			}
			
			// execute next step
			if (data.step && data.node) {
				this._executeStep(data.step, data.node);
			}
		}, this));
	},
	
	/**
	 * Submits the dialog content.
	 * 
	 * @param	object		data
	 */
	_submit: function(data) {
		this._setIcon('spinner');
		
		// collect form values
		var $additionalData = { };
		$('#packageInstallationInnerContent input').each(function(index, inputElement) {
			var $inputElement = $(inputElement);
			var $type = $inputElement.attr('type');
			
			if (($type != 'checkbox' && $type != 'radio') || $inputElement.prop('checked')) {
				var $name = $inputElement.attr('name');
				if ($name.match(/(.*)\[([^[]*)\]$/)) {
					$name = RegExp.$1;
					$key = RegExp.$2;
					
					if ($additionalData[$name] === undefined) {
						if ($key) {
							$additionalData[$name] = { };
						}
						else {
							$additionalData[$name] = [ ];
						}
					}
					
					if ($key) {
						$additionalData[$name][$key] = $inputElement.val();
					}
					else {
						$additionalData[$name].push($inputElement.val());
					}
				}
				else {
					$additionalData[$name] = $inputElement.val();
				}
			}
		});
		
		this._executeStep(data.step, data.node, $additionalData);
	},
	
	/**
	 * Purges template content.
	 * 
	 * @param	function	callback
	 */
	_purgeTemplateContent: function(callback) {
		if ($('#packageInstallationInnerContent').children().length) {
			$('#packageInstallationInnerContentContainer').hide();
			$('#packageInstallationInnerContent').empty();
			
			this._shouldRender = true;
		}
		
		callback();
	},
	
	/**
	 * Executes the next installation step.
	 * 
	 * @param	string		step
	 * @param	string		node
	 * @param	object		additionalData
	 */
	_executeStep: function(step, node, additionalData) {
		if (!additionalData) additionalData = { };
		
		var $data = $.extend({}, this._additionalRequestParameters, {
			node: node,
			queueID: this._queueID,
			step: step
		}, additionalData);
		
		this._proxy.setOption('data', $data);
		this._proxy.sendRequest();
	},
	
	/**
	 * Sets the icon with the given name as the current installation status icon.
	 * 
	 * @param	string		iconName
	 */
	_setIcon: function(iconName) {
		this._dialog.find('.jsPackageInstallationStatus').removeClass('fa-check fa-question fa-times fa-spinner').addClass('fa-' + iconName);
	}
});

/**
 * Handles canceling the package installation at the package installation
 * confirm page.
 */
WCF.ACP.Package.Installation.Cancel = Class.extend({
	/**
	 * Creates a new instance of WCF.ACP.Package.Installation.Cancel.
	 * 
	 * @param	integer		queueID
	 */
	init: function(queueID) {
		$('#backButton').click(function() {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'cancelInstallation',
					className: 'wcf\\data\\package\\installation\\queue\\PackageInstallationQueueAction',
					objectIDs: [ queueID ]
				},
				success: function(data) {
					window.location = data.returnValues.url;
				}
			});
		});
	}
});

/**
 * Provides the package uninstallation.
 * 
 * @param	jQuery		elements
 * @param	string		wcfPackageListURL
 */
WCF.ACP.Package.Uninstallation = WCF.ACP.Package.Installation.extend({
	/**
	 * list of uninstallation buttons
	 * @var	jQuery
	 */
	_elements: null,
	
	/**
	 * current package id
	 * @var	integer
	 */
	_packageID: 0,
	
	/**
	 * URL of WCF package list
	 * @var	string
	 */
	_wcfPackageListURL: '',
	
	/**
	 * Initializes the WCF.ACP.Package.Uninstallation class.
	 * 
	 * @param	jQuery		elements
	 * @param	string		wcfPackageListURL
	 */
	init: function(elements, wcfPackageListURL) {
		this._elements = elements;
		this._packageID = 0;
		this._wcfPackageListURL = wcfPackageListURL;
		
		if (this._elements !== undefined && this._elements.length) {
			this._super(0, 'UninstallPackage');
		}
	},
	
	/**
	 * Begins a package uninstallation without user action.
	 * 
	 * @param	integer		packageID
	 */
	start: function(packageID) {
		this._actionName = 'UninstallPackage';
		this._packageID = packageID;
		this._queueID = 0;
		this._dialogTitle = 'wcf.acp.package.uninstallation.title';
		
		this._initProxy();
		this.prepareInstallation();
	},
	
	/**
	 * @see	WCF.ACP.Package.Installation.init()
	 */
	_init: function() {
		this._elements.click($.proxy(this._showConfirmationDialog, this));
	},
	
	/**
	 * Displays a confirmation dialog prior to package uninstallation.
	 * 
	 * @param	object		event
	 */
	_showConfirmationDialog: function(event) {
		var $element = $(event.currentTarget);
		
		if ($element.data('isApplication') && this._wcfPackageListURL) {
			window.location = WCF.String.unescapeHTML(this._wcfPackageListURL.replace(/{packageID}/, $element.data('objectID')));
			return;
		}
		
		var self = this;
		WCF.System.Confirmation.show($element.data('confirmMessage'), function(action) {
			if (action === 'confirm') {
				self._packageID = $element.data('objectID');
				self.prepareInstallation();
			}
		}, undefined, undefined, true);
	},
	
	/**
	 * @see	WCF.ACP.Package.Installation._getParameters()
	 */
	_getParameters: function() {
		return {
			packageID: this._packageID,
			step: 'prepare'
		};
	}
});

WCF.ACP.Package.Server = { };

WCF.ACP.Package.Server.Installation = Class.extend({
	_proxy: null,
	_selectedPackage: '',
	
	init: function() {
		this._dialog = null;
		this._selectedPackage = null;
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	bind: function() {
		$('.jsButtonPackageInstall').removeClass('jsButtonPackageInstall').click($.proxy(this._click, this));
	},
	
	/**
	 * Prepares a package installation.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $button = $(event.currentTarget);
		WCF.System.Confirmation.show($button.data('confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._selectedPackage = $button.data('package');
				this._selectedPackageVersion = $button.data('packageVersion');
				this._prepareInstallation();
			}
		}, this), undefined, undefined, true);
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 */
	_success: function(data) {
		if (data.returnValues.queueID) {
			if (this._dialog !== null) {
				this._dialog.wcfDialog('close');
			}
			
			var $installation = new WCF.ACP.Package.Installation(data.returnValues.queueID, undefined, false);
			$installation.prepareInstallation();
		}
		else if (data.returnValues.template) {
			if (this._dialog === null) {
				this._dialog = $('<div>' + data.returnValues.template + '</div>').hide().appendTo(document.body);
				this._dialog.wcfDialog({
					title: WCF.Language.get('wcf.acp.package.update.unauthorized')
				});
			}
			else {
				this._dialog.html(data.returnValues.template).wcfDialog('open');
			}
			
			this._dialog.find('.formSubmit > button').click($.proxy(this._submitAuthentication, this));
		}
	},
	
	/**
	 * Submits authentication data for current update server.
	 * 
	 * @param	object		event
	 */
	_submitAuthentication: function(event) {
		var $usernameField = $('#packageUpdateServerUsername');
		var $passwordField = $('#packageUpdateServerPassword');
		
		// remove error messages if any
		$usernameField.next('small.innerError').remove();
		$passwordField.next('small.innerError').remove();
		
		var $continue = true;
		if ($.trim($usernameField.val()) === '') {
			$('<small class="innerError">' + WCF.Language.get('wcf.global.form.error.empty') + '</small>').insertAfter($usernameField);
			$continue = false;
		}
		
		if ($.trim($passwordField.val()) === '') {
			$('<small class="innerError">' + WCF.Language.get('wcf.global.form.error.empty') + '</small>').insertAfter($passwordField);
			$continue = false;
		}
		
		if ($continue) {
			this._prepareInstallation($(event.currentTarget).data('packageUpdateServerID'));
		}
	},
	
	/**
	 * Prepares package installation.
	 * 
	 * @param	integer		packageUpdateServerID
	 */
	_prepareInstallation: function(packageUpdateServerID) {
		var $parameters = {
			'packages': { }
		};
		$parameters['packages'][this._selectedPackage] = this._selectedPackageVersion;
		
		if (packageUpdateServerID) {
			$parameters.authData = {
				packageUpdateServerID: packageUpdateServerID,
				password: $.trim($('#packageUpdateServerPassword').val()),
				saveCredentials: ($('#packageUpdateServerSaveCredentials:checked').length ? true : false),
				username: $.trim($('#packageUpdateServerUsername').val())
			};
		}
		
		this._proxy.setOption('data', {
			actionName: 'prepareInstallation',
			className: 'wcf\\data\\package\\update\\PackageUpdateAction',
			parameters: $parameters
		});
		this._proxy.sendRequest();
	},
});

/**
 * Namespace for package update related classes.
 */
WCF.ACP.Package.Update = { };

/**
 * Handles the package update process.
 */
WCF.ACP.Package.Update.Manager = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * submit button
	 * @var	jQuery
	 */
	_submitButton: null,
	
	/**
	 * Initializes the WCF.ACP.Package.Update.Manager class.
	 */
	init: function() {
		this._dialog = null;
		this._submitButton = $('.formSubmit > button').click($.proxy(this._click, this));
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('.jsPackageUpdate').each($.proxy(function(index, packageUpdate) {
			var $packageUpdate = $(packageUpdate);
			$packageUpdate.find('input[type=checkbox]').data('packageUpdate', $packageUpdate).change($.proxy(this._change, this));
		}, this));
	},
	
	/**
	 * Handles toggles for a specific update.
	 */
	_change: function(event) {
		var $checkbox = $(event.currentTarget);
		
		if ($checkbox.is(':checked')) {
			$checkbox.data('packageUpdate').find('select').enable();
			$checkbox.data('packageUpdate').find('dl').removeClass('disabled');
			
			this._submitButton.enable();
		}
		else {
			$checkbox.data('packageUpdate').find('select').disable();
			$checkbox.data('packageUpdate').find('dl').addClass('disabled');
			
			// disable submit button
			if (!$('input[type=checkbox]:checked').length) {
				this._submitButton.disable();
			}
			else {
				this._submitButton.enable();
			}
		}
	},
	
	/**
	 * Handles clicks on the submit button.
	 * 
	 * @param	object		event
	 * @param	integer		packageUpdateServerID
	 */
	_click: function(event, packageUpdateServerID) {
		var $packages = { };
		$('.jsPackageUpdate').each($.proxy(function(index, packageUpdate) {
			var $packageUpdate = $(packageUpdate);
			if ($packageUpdate.find('input[type=checkbox]:checked').length) {
				$packages[$packageUpdate.data('package')] = $packageUpdate.find('select').val();
			}
		}, this));
		
		if ($.getLength($packages)) {
			this._submitButton.disable();
			
			var $parameters = {
				packages: $packages
			};
			if (packageUpdateServerID) {
				$parameters.authData = {
					packageUpdateServerID: packageUpdateServerID,
					password: $.trim($('#packageUpdateServerPassword').val()),
					saveCredentials: ($('#packageUpdateServerSaveCredentials:checked').length ? true : false),
					username: $.trim($('#packageUpdateServerUsername').val())
				};
			}
			
			this._proxy.setOption('data', {
				actionName: 'prepareUpdate',
				className: 'wcf\\data\\package\\update\\PackageUpdateAction',
				parameters: $parameters,
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
		if (data.returnValues.queueID) {
			if (this._dialog !== null) {
				this._dialog.wcfDialog('close');
			}
			
			var $installation = new WCF.ACP.Package.Installation(data.returnValues.queueID, undefined, false, true);
			$installation.prepareInstallation();
		}
		else if (data.returnValues.excludedPackages) {
			if (this._dialog === null) {
				this._dialog = $('<div>' + data.returnValues.template + '</div>').hide().appendTo(document.body);
				this._dialog.wcfDialog({
					title: WCF.Language.get('wcf.acp.package.update.excludedPackages')
				});
			}
			else {
				this._dialog.wcfDialog('option', 'title', WCF.Language.get('wcf.acp.package.update.excludedPackages'));
				this._dialog.html(data.returnValues.template).wcfDialog('open');
			}
			
			this._submitButton.enable();
		}
		else if (data.returnValues.template) {
			if (this._dialog === null) {
				this._dialog = $('<div>' + data.returnValues.template + '</div>').hide().appendTo(document.body);
				this._dialog.wcfDialog({
					title: WCF.Language.get('wcf.acp.package.update.unauthorized')
				});
			}
			else {
				this._dialog.wcfDialog('option', 'title', WCF.Language.get('wcf.acp.package.update.unauthorized'));
				this._dialog.html(data.returnValues.template).wcfDialog('open');
			}
			
			this._dialog.find('.formSubmit > button').click($.proxy(this._submitAuthentication, this));
		}
	},
	
	/**
	 * Submits authentication data for current update server.
	 * 
	 * @param	object		event
	 */
	_submitAuthentication: function(event) {
		var $usernameField = $('#packageUpdateServerUsername');
		var $passwordField = $('#packageUpdateServerPassword');
		
		// remove error messages if any
		$usernameField.next('small.innerError').remove();
		$passwordField.next('small.innerError').remove();
		
		var $continue = true;
		if ($.trim($usernameField.val()) === '') {
			$('<small class="innerError">' + WCF.Language.get('wcf.global.form.error.empty') + '</small>').insertAfter($usernameField);
			$continue = false;
		}
		
		if ($.trim($passwordField.val()) === '') {
			$('<small class="innerError">' + WCF.Language.get('wcf.global.form.error.empty') + '</small>').insertAfter($passwordField);
			$continue = false;
		}
		
		if ($continue) {
			this._click(undefined, $(event.currentTarget).data('packageUpdateServerID'));
		}
	}
});

/**
 * Searches for available updates.
 * 
 * @param	boolean		bindOnExistingButtons
 */
WCF.ACP.Package.Update.Search = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * Initializes the WCF.ACP.Package.SearchForUpdates class.
	 * 
	 * @param	boolean		bindOnExistingButtons
	 */
	init: function(bindOnExistingButtons) {
		this._dialog = null;
		
		if (bindOnExistingButtons === true) {
			$('.jsButtonSearchForUpdates').click($.proxy(this._click, this));
		}
		else {
			var $button = $('<li><a class="button jsButtonSearchForUpdates"><span class="icon icon16 fa-refresh"></span> <span>' + WCF.Language.get('wcf.acp.package.searchForUpdates') + '</span></a></li>');
			$button.click(this._click.bind(this)).prependTo($('.contentHeaderNavigation > ul'));
		}
	},
	
	/**
	 * Handles clicks on the search button.
	 * 
	 * @param {Event} event
	 */
	_click: function(event) {
		event.preventDefault();
		
		if (this._dialog === null) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'searchForUpdates',
					className: 'wcf\\data\\package\\update\\PackageUpdateAction',
					parameters: {
						ignoreCache: 1
					}
				},
				success: $.proxy(this._success, this)
			});
		}
		else {
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
		if (typeof window._trackSearchForUpdates === 'function') {
			window._trackSearchForUpdates(data);
			return;
		}
		
		if (data.returnValues.url) {
			window.location = data.returnValues.url;
		}
		else {
			this._dialog = $('<div>' + WCF.Language.get('wcf.acp.package.searchForUpdates.noResults') + '</div>').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.acp.package.searchForUpdates')
			});
		}
	}
});

/**
 * Namespace for classes related to the WoltLab Plugin-Store.
 */
WCF.ACP.PluginStore = { };

/**
 * Namespace for classes handling items purchased in the WoltLab Plugin-Store.
 */
WCF.ACP.PluginStore.PurchasedItems = { };

/**
 * Searches for purchased items available for install but not yet installed.
 */
WCF.ACP.PluginStore.PurchasedItems.Search = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the WCF.ACP.PluginStore.PurchasedItems.Search class.
	 */
	init: function() {
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		var $button = $('<li><a class="button"><span class="icon icon16 fa-shopping-cart" /> <span>' + WCF.Language.get('wcf.acp.pluginStore.purchasedItems.button.search') + '</span></a></li>');
		$button.prependTo($('.contentHeaderNavigation > ul')).click($.proxy(this._click, this));
	},
	
	/**
	 * Handles clicks on the search button.
	 */
	_click: function() {
		this._proxy.setOption('data', {
			actionName: 'searchForPurchasedItems',
			className: 'wcf\\data\\package\\PackageAction'
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
		// prompt for credentials
		if (data.returnValues.template) {
			if (this._dialog === null) {
				this._dialog = $('<div />').hide().appendTo(document.body);
				this._dialog.html(data.returnValues.template).wcfDialog({
					title: WCF.Language.get('wcf.acp.pluginStore.authorization')
				});
			}
			else {
				this._dialog.html(data.returnValues.template);
				this._dialog.wcfDialog('open');
			}
			
			var $button = this._dialog.find('button').click($.proxy(this._submit, this));
			this._dialog.find('input').keyup(function(event) {
				if (event.which == $.ui.keyCode.ENTER) {
					$button.trigger('click');
					return false;
				}
			});
		}
		else if (data.returnValues.noResults) {
			// there are no purchased products yet
			if (this._dialog === null) {
				this._dialog = $('<div />').hide().appendTo(document.body);
				this._dialog.html(data.returnValues.noResults).wcfDialog({
					title: WCF.Language.get('wcf.acp.pluginStore.purchasedItems')
				});
			} else {
				this._dialog.wcfDialog('option', 'title', WCF.Language.get('wcf.acp.pluginStore.purchasedItems'));
				this._dialog.html(data.returnValues.noResults);
				this._dialog.wcfDialog('open');
			}
		}
		else if (data.returnValues.noSSL) {
			// PHP was compiled w/o OpenSSL support
			if (this._dialog === null) {
				this._dialog = $('<div />').hide().appendTo(document.body);
				this._dialog.html(data.returnValues.noSSL).wcfDialog({
					title: WCF.Language.get('wcf.global.error.title')
				});
			}
			else {
				this._dialog.wcfDialog('option', 'title', WCF.Language.get('wcf.global.error.title'));
				this._dialog.html(data.returnValues.noSSL);
				this._dialog.wcfDialog('open');
			}
		}
		else if (data.returnValues.redirectURL) {
			// redirect to list of purchased products
			window.location = data.returnValues.redirectURL;
		}
	},
	
	/**
	 * Submits the user credentials.
	 */
	_submit: function() {
		this._dialog.wcfDialog('close');
		
		this._proxy.setOption('data', {
			actionName: 'searchForPurchasedItems',
			className: 'wcf\\data\\package\\PackageAction',
			parameters: {
				password: $('#pluginStorePassword').val(),
				username: $('#pluginStoreUsername').val()
			}
		});
		this._proxy.sendRequest();
	}
});

/**
 * Worker support for ACP.
 * 
 * @param	string		dialogID
 * @param	string		className
 * @param	string		title
 * @param	object		parameters
 * @param	object		callback
 * 
 * @deprecated  3.1 - please use `WoltLabSuite/Core/Acp/Ui/Worker` instead
 */
WCF.ACP.Worker = Class.extend({
	/**
	 * Initializes a new worker instance.
	 * 
	 * @param	string		dialogID
	 * @param	string		className
	 * @param	string		title
	 * @param	object		parameters
	 * @param	object		callback
	 */
	init: function(dialogID, className, title, parameters, callback) {
		if (typeof callback === 'function') {
			throw new Error("The callback parameter is no longer supported, please migrate to 'WoltLabSuite/Core/Acp/Ui/Worker'.");
		}
		
		require(['WoltLabSuite/Core/Acp/Ui/Worker'], function(AcpUiWorker) {
			new AcpUiWorker({
				// dialog
				dialogId: dialogID,
				dialogTitle: title,
				
				// ajax
				className: className,
				parameters: parameters
			});
		});
	}
});

/**
 * Namespace for category-related functions.
 */
WCF.ACP.Category = { };

/**
 * Handles collapsing categories.
 * 
 * @param	string		className
 * @param	integer		objectTypeID
 */
WCF.ACP.Category.Collapsible = WCF.Collapsible.SimpleRemote.extend({
	/**
	 * @see	WCF.Collapsible.Remote.init()
	 */
	init: function(className) {
		var sortButton = $('.formSubmit > button[data-type="submit"]');
		if (sortButton) {
			sortButton.click($.proxy(this._sort, this));
		}
		
		this._super(className);
	},
	
	/**
	 * @see	WCF.Collapsible.Remote._getButtonContainer()
	 */
	_getButtonContainer: function(containerID) {
		return $('#' + containerID + ' > .buttons');
	},
	
	/**
	 * @see	WCF.Collapsible.Remote._getContainers()
	 */
	_getContainers: function() {
		return $('.jsCategory').has('ol').has('li');
	},
	
	/**
	 * @see	WCF.Collapsible.Remote._getTarget()
	 */
	_getTarget: function(containerID) {
		return $('#' + containerID + ' > ol');
	},
	
	/**
	 * Handles a click on the sort button.
	 */
	_sort: function() {
		// remove existing collapsible buttons
		$('.collapsibleButton').remove();
		
		// reinit containers
		this._containers = { };
		this._containerData = { };
		
		var $containers = this._getContainers();
		if ($containers.length == 0) {
			console.debug('[WCF.ACP.Category.Collapsible] Empty container set given, aborting.');
		}
		$containers.each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			this._containers[$containerID] = $container;
			
			this._initContainer($containerID);
		}, this));
	}
});

/**
 * Provides the search dropdown for ACP
 * 
 * @see	WCF.Search.Base
 */
WCF.ACP.Search = WCF.Search.Base.extend({
	_delay: 250,
	
	/**
	 * name of the selected search provider
	 * @var	string
	 */
	_providerName: '',
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function() {
		this._className = 'wcf\\data\\acp\\search\\provider\\ACPSearchProviderAction';
		this._super('#pageHeaderSearch input[name=q]');
		
		// disable form submitting
		$('#pageHeaderSearch > form').on('submit', function(event) {
			event.preventDefault();
		});
		
		var $dropdown = WCF.Dropdown.getDropdownMenu('pageHeaderSearchType');
		$dropdown.find('a[data-provider-name]').on('click', $.proxy(function(event) {
			event.preventDefault();
			var $button = $(event.target);
			$('.pageHeaderSearchType > .button > .pageHeaderSearchTypeLabel').text($button.text());
			
			var $oldProviderName = this._providerName;
			this._providerName = ($button.data('providerName') != 'everywhere' ? $button.data('providerName') : '');
			
			if ($oldProviderName != this._providerName) {
				var $searchString = $.trim(this._searchInput.val());
				if ($searchString) {
					var $parameters = {
						data: {
							excludedSearchValues: this._excludedSearchValues,
							searchString: $searchString
						}
					};
					this._queryServer($parameters);
				}
			}
		}, this));
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(resultList) {
		// add a divider between result lists
		if (this._list.children('li').length > 0) {
			$('<li class="dropdownDivider" />').appendTo(this._list);
		}
		
		// add caption
		$('<li class="dropdownText">' + resultList.title + '</li>').appendTo(this._list);
		
		// add menu items
		for (var $i in resultList.items) {
			var $item = resultList.items[$i];
			
			$('<li><a href="' + $item.link + '"><span>' + WCF.String.escapeHTML($item.title) + '</span>' + ($item.subtitle ? '<small>' + WCF.String.escapeHTML($item.subtitle) + '</small>' : '') + '</a></li>').appendTo(this._list);
			
			this._itemCount++;
		}
	},
	
	/**
	 * @see	WCF.Search.Base._openDropdown()
	 */
	_openDropdown: function() {
		this._list.find('small').each(function(index, element) {
			while (element.scrollWidth > element.clientWidth) {
				element.innerText = '\u2026 ' + element.innerText.substr(3);
			}
		});
	},
	
	/**
	 * @see	WCF.Search.Base._handleEmptyResult()
	 */
	_handleEmptyResult: function() {
		$('<li class="dropdownText">' + WCF.Language.get('wcf.acp.search.noResults') + '</li>').appendTo(this._list);
		
		return true;
	},
	
	/**
	 * @see	WCF.Search.Base._highlightSelectedElement()
	 */
	_highlightSelectedElement: function() {
		this._list.find('li').removeClass('dropdownNavigationItem');
		this._list.find('li:not(.dropdownDivider):not(.dropdownText)').eq(this._itemIndex).addClass('dropdownNavigationItem');
	},
	
	/**
	 * @see	WCF.Search.Base._selectElement()
	 */
	_selectElement: function(event) {
		if (this._itemIndex === -1) {
			return false;
		}
		
		window.location = this._list.find('li.dropdownNavigationItem > a').attr('href');
	},
	
	_success: function(data) {
		this._super(data);
		
		var search = elById('pageHeaderSearch');
		this._list[0].style.setProperty('top', search.offsetTop + search.clientHeight + 'px', 'important');
		this._list.addClass('acpSearchDropdown');
	},
	
	/**
	 * @see	WCF.Search.Base._getParameters()
	 */
	_getParameters: function(parameters) {
		parameters.data.providerName = this._providerName;
		
		return parameters;
	}
});

/**
 * Namespace for user management.
 */
WCF.ACP.User = { };

/**
 * Generic implementation to ban users.
 */
WCF.ACP.User.BanHandler = {
	/**
	 * callback object
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes WCF.ACP.User.BanHandler on first use.
	 */
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('.jsBanButton').click($.proxy(function(event) {
			var $button = $(event.currentTarget);
			if ($button.data('banned')) {
				this.unban([ $button.data('objectID') ]);
			}
			else {
				this.ban([ $button.data('objectID') ]);
			}
		}, this));
		
		require(['EventHandler'], function(EventHandler) {
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.user', this._clipboardAction.bind(this));
		}.bind(this));
	},
	
	/**
	 * Reacts to executed clipboard actions.
	 *
	 * @param	{object<string, *>}	actionData	data of the executed clipboard action
	 */
	_clipboardAction: function(actionData) {
		if (actionData.data.actionName === 'com.woltlab.wcf.user.ban') {
			this.ban(actionData.data.parameters.objectIDs);
		}
	},
	
	/**
	 * Unbans users.
	 * 
	 * @param	array<integer>	userIDs
	 */
	unban: function(userIDs) {
		this._proxy.setOption('data', {
			actionName: 'unban',
			className: 'wcf\\data\\user\\UserAction',
			objectIDs: userIDs
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Bans users.
	 * 
	 * @param	array<integer>	userIDs
	 */
	ban: function(userIDs) {
		if (this._dialog === null) {
			// create dialog
			this._dialog = $('<div />').hide().appendTo(document.body);
			this._dialog.append($('<div class="section"><dl><dt><label for="userBanReason">' + WCF.Language.get('wcf.acp.user.banReason') + '</label></dt><dd><textarea id="userBanReason" cols="40" rows="3" /><small>' + WCF.Language.get('wcf.acp.user.banReason.description') + '</small></dd></dl><dl><dt></dt><dd><label for="userBanNeverExpires"><input type="checkbox" name="userBanNeverExpires" id="userBanNeverExpires" checked> ' + WCF.Language.get('wcf.acp.user.ban.neverExpires') + '</label></dd></dl><dl id="userBanExpiresSettings" style="display: none;"><dt><label for="userBanExpires">' + WCF.Language.get('wcf.acp.user.ban.expires') + '</label></dt><dd><input type="date" name="userBanExpires" id="userBanExpires" class="medium" min="' + new Date(TIME_NOW * 1000).toISOString() + '" data-ignore-timezone="true" /><small>' + WCF.Language.get('wcf.acp.user.ban.expires.description') + '</small></dd></dl></div>'));
			this._dialog.append($('<div class="formSubmit"><button class="buttonPrimary" accesskey="s">' + WCF.Language.get('wcf.global.button.submit') + '</button></div>'));
			
			this._dialog.find('#userBanNeverExpires').change(function() {
				$('#userBanExpiresSettings').toggle();
			});
			
			this._dialog.find('button').click($.proxy(this._submit, this));
		}
		else {
			// reset dialog
			$('#userBanReason').val('');
			$('#userBanNeverExpires').prop('checked', true);
			$('#userBanExpiresSettings').hide();
			$('#userBanExpiresDatePicker, #userBanExpires').val('');
		}
		
		this._dialog.data('userIDs', userIDs);
		this._dialog.wcfDialog({
			title: WCF.Language.get('wcf.acp.user.ban.sure')
		});
	},
	
	/**
	 * Handles submitting the ban dialog.
	 */
	_submit: function() {
		this._dialog.find('.innerError').remove();
		
		var $banExpires = '';
		if (!$('#userBanNeverExpires').is(':checked')) {
			var $banExpires = $('#userBanExpiresDatePicker').val();
			if (!$banExpires) {
				this._dialog.find('#userBanExpiresSettings > dd > small').prepend($('<small class="innerError" />').text(WCF.Language.get('wcf.global.form.error.empty')));
				return
			}
		}
		
		this._proxy.setOption('data', {
			actionName: 'ban',
			className: 'wcf\\data\\user\\UserAction',
			objectIDs: this._dialog.data('userIDs'),
			parameters: {
				banReason: $('#userBanReason').val(),
				banExpires: $banExpires
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX calls.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		elBySelAll('.jsUserRow', undefined, function(userRow) {
			var userId = parseInt(elData(userRow, 'object-id'), 10);
			if (data.objectIDs.indexOf(userId) !== -1) {
				elData(userRow, 'banned', data.actionName === 'ban');
			}
		});
		
		$('.jsBanButton').each(function(index, button) {
			var $button = $(button);
			if (WCF.inArray($button.data('objectID'), data.objectIDs)) {
				if (data.actionName == 'unban') {
					$button.data('banned', false).attr('data-tooltip', $button.data('banMessage')).removeClass('fa-lock').addClass('fa-unlock');
				}
				else {
					$button.data('banned', true).attr('data-tooltip', $button.data('unbanMessage')).removeClass('fa-unlock').addClass('fa-lock');
				}
			}
		});
		
		var $notification = new WCF.System.Notification();
		$notification.show();
		
		WCF.Clipboard.reload();
		
		if (data.actionName == 'ban') {
			this._dialog.wcfDialog('close');
		}
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.acp.user', 'refresh', {userIds: data.objectIDs});
	}
};

/**
 * Namespace for user group management.
 */
WCF.ACP.User.Group = { };

/**
 * Handles copying user groups.
 */
WCF.ACP.User.Group.Copy = Class.extend({
	/**
	 * id of the copied group
	 * @var	integer
	 */
	_groupID: 0,
	
	/**
	 * Initializes a new instance of WCF.ACP.User.Group.Copy.
	 * 
	 * @param	integer		groupID
	 */
	init: function(groupID) {
		this._groupID = groupID;
		
		$('.jsButtonUserGroupCopy').click($.proxy(this._click, this));
	},
	
	/**
	 * Handles clicking on a 'copy user group' button.
	 */
	_click: function() {
		var $template = $('<div class="section" />');
		$template.append($('<dl class="wide"><dt /><dd><label><input type="checkbox" id="copyMembers" value="1" /> ' + WCF.Language.get('wcf.acp.group.copy.copyMembers') + '</label><small>' + WCF.Language.get('wcf.acp.group.copy.copyMembers.description') + '</small></dd></dl>'));
		$template.append($('<dl class="wide"><dt /><dd><label><input type="checkbox" id="copyUserGroupOptions" value="1" /> ' + WCF.Language.get('wcf.acp.group.copy.copyUserGroupOptions') + '</label><small>' + WCF.Language.get('wcf.acp.group.copy.copyUserGroupOptions.description') + '</small></dd></dl>'));
		$template.append($('<dl class="wide"><dt /><dd><label><input type="checkbox" id="copyACLOptions" value="1" /> ' + WCF.Language.get('wcf.acp.group.copy.copyACLOptions') + '</label><small>' + WCF.Language.get('wcf.acp.group.copy.copyACLOptions.description') + '</small></dd></dl>'));
		
		WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.group.copy.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				new WCF.Action.Proxy({
					autoSend: true,
					data: {
						actionName: 'copy',
						className: 'wcf\\data\\user\\group\\UserGroupAction',
						objectIDs: [ this._groupID ],
						parameters: {
							copyACLOptions: $('#copyACLOptions').is(':checked'),
							copyMembers: $('#copyMembers').is(':checked'),
							copyUserGroupOptions: $('#copyUserGroupOptions').is(':checked')
						}
					},
					success: function(data) {
						window.location = data.returnValues.redirectURL;
					}
				});
			}
		}, this), '', $template, true);
	}
});

/**
 * Generic implementation to enable users.
 */
WCF.ACP.User.EnableHandler = {
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes WCF.ACP.User.EnableHandler on first use.
	 */
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('.jsEnableButton').click($.proxy(function(event) {
			var $button = $(event.currentTarget);
			if ($button.data('enabled')) {
				this.disable([ $button.data('objectID') ]);
			}
			else {
				this.enable([ $button.data('objectID') ]);
			}
		}, this));
		
		require(['EventHandler'], function(EventHandler) {
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.user', this._clipboardAction.bind(this));
		}.bind(this));
	},
	
	/**
	 * Reacts to executed clipboard actions.
	 *
	 * @param	{object<string, *>}	actionData	data of the executed clipboard action
	 */
	_clipboardAction: function(actionData) {
		if (actionData.data.actionName === 'com.woltlab.wcf.user.enable') {
			this.enable(actionData.data.parameters.objectIDs);
		}
	},
	
	/**
	 * Disables users.
	 * 
	 * @param	array<integer>	userIDs
	 */
	disable: function(userIDs) {
		this._proxy.setOption('data', {
			actionName: 'disable',
			className: 'wcf\\data\\user\\UserAction',
			objectIDs: userIDs
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Enables users.
	 * 
	 * @param	array<integer>	userIDs
	 */
	enable: function(userIDs) {
		this._proxy.setOption('data', {
			actionName: 'enable',
			className: 'wcf\\data\\user\\UserAction',
			objectIDs: userIDs
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX calls.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		elBySelAll('.jsUserRow', undefined, function(userRow) {
			var userId = parseInt(elData(userRow, 'object-id'), 10);
			if (data.objectIDs.indexOf(userId) !== -1) {
				elData(userRow, 'enabled', data.actionName === 'enable');
			}
		});
		
		$('.jsEnableButton').each(function(index, button) {
			var $button = $(button);
			if (WCF.inArray($button.data('objectID'), data.objectIDs)) {
				if (data.actionName == 'disable') {
					$button.data('enabled', false).attr('data-tooltip', $button.data('enableMessage')).removeClass('fa-check-square-o').addClass('fa-square-o');
				}
				else {
					$button.data('enabled', true).attr('data-tooltip', $button.data('disableMessage')).removeClass('fa-square-o').addClass('fa-check-square-o');
				}
			}
		});
		
		var $notification = new WCF.System.Notification();
		$notification.show(function() { window.location.reload(); });
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.acp.user', 'refresh', {userIds: data.objectIDs});
	}
};

/**
 * Handles the send new password clipboard action.
 */
WCF.ACP.User.SendNewPasswordHandler = {
	/**
	 * Initializes WCF.ACP.User.SendNewPasswordHandler on first use.
	 */
	init: function() {
		require(['EventHandler'], function(EventHandler) {
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.user', this._clipboardAction.bind(this));
		}.bind(this));
	},
	
	/**
	 * Reacts to executed clipboard actions.
	 *
	 * @param	{object<string, *>}	actionData	data of the executed clipboard action
	 */
	_clipboardAction: function(actionData) {
		if (actionData.data.actionName === 'com.woltlab.wcf.user.sendNewPassword') {
			WCF.System.Confirmation.show(actionData.data.parameters.confirmMessage, function(action) {
				if (action === 'confirm') {
					new WCF.ACP.Worker('sendingNewPasswords', 'wcf\\system\\worker\\SendNewPasswordWorker', WCF.Language.get('wcf.acp.user.sendNewPassword.workerTitle'), {
						userIDs: actionData.data.parameters.objectIDs
					});
				}
			});
		}
	}
};

/**
 * Namespace for import-related classes.
 */
WCF.ACP.Import = { };

/**
 * Importer for ACP.
 * 
 * @param	array<string>	objectTypes
 */
WCF.ACP.Import.Manager = Class.extend({
	/**
	 * current action
	 * @var	string
	 */
	_currentAction: '',
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * current object type index
	 * @var	int
	 */
	_index: -1,
	
	/**
	 * list of object types
	 * @var	string[]
	 */
	_objectTypes: [],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * redirect URL
	 * @var	string
	 */
	_redirectURL: '',
	
	/**
	 * Initializes the WCF.ACP.Importer object.
	 * 
	 * @param	{string[]}	objectTypes
	 * @param	{string}	redirectURL
	 */
	init: function(objectTypes, redirectURL) {
		this._currentAction = '';
		this._index = -1;
		this._objectTypes = objectTypes;
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php?worker-proxy/&t=' + SECURITY_TOKEN
		});
		this._redirectURL = redirectURL;
		
		this._invoke();
	},
	
	/**
	 * Invokes importing of an object type.
	 */
	_invoke: function() {
		this._index++;
		if (this._index >= this._objectTypes.length) {
			this._dialog.find('.fa-spinner').removeClass('fa-spinner').addClass('fa-check');
			this._dialog.find('h1').text(WCF.Language.get('wcf.acp.dataImport.completed'));
			
			var $form = $('<div class="formSubmit" />').appendTo(this._dialog.find('#workerContainer'));
			$('<button>' + WCF.Language.get('wcf.global.button.next') + '</button>').click($.proxy(function() {
				new WCF.Action.Proxy({
					autoSend: true,
					data: {
						noRedirect: 1
					},
					dataType: 'html',
					success: $.proxy(function() {
						window.location = this._redirectURL;
					}, this),
					url: 'index.php?cache-clear/&t=' + SECURITY_TOKEN
				});
			}, this)).appendTo($form);
			
			this._dialog.wcfDialog('render');
		}
		else {
			this._run(
				WCF.Language.get('wcf.acp.dataImport.data.' + this._objectTypes[this._index]),
				this._objectTypes[this._index]
			);
		}
	},
	
	/**
	 * Executes import of given object type.
	 * 
	 * @param	string		currentAction
	 * @param	string		objectType
	 */
	_run: function(currentAction, objectType) {
		this._currentAction = currentAction;
		this._proxy.setOption('data', {
			className: 'wcf\\system\\worker\\ImportWorker',
			parameters: {
				objectType: objectType
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles response from server.
	 * 
	 * @param	object		data
	 */
	_success: function(data) {
		// init binding
		if (this._dialog === null) {
			this._dialog = $('<div />').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				closable: false,
				title: WCF.Language.get('wcf.acp.dataImport')
			});
		}
		
		if (data.template) {
			this._dialog.html(data.template);
		}
		
		if (this._currentAction) {
			this._dialog.find('h1').text(this._currentAction);
		}
		
		// update progress
		this._dialog.find('progress').attr('value', data.progress).text(data.progress + '%').next('span').text(data.progress + '%');
		
		// worker is still busy with it's business, carry on
		if (data.progress < 100) {
			// send request for next loop
			this._proxy.setOption('data', {
				className: data.className,
				loopCount: data.loopCount,
				parameters: data.parameters
			});
			this._proxy.sendRequest();
		}
		else {
			this._invoke();
		}
	}
});

/**
 * Namespace for stat-related classes.
 */
WCF.ACP.Stat = { };

/**
 * Shows the daily stat chart.
 */
WCF.ACP.Stat.Chart = Class.extend({
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('#statRefreshButton').click($.proxy(this._refresh, this));
		
		this._refresh();
	},
	
	_refresh: function() {
		var $objectTypeIDs = [ ];
		$('input[name=objectTypeID]:checked').each(function() {
			$objectTypeIDs.push($(this).val());
		});
		
		if (!$objectTypeIDs.length) return;
		
		this._proxy.setOption('data', {
			className: 'wcf\\data\\stat\\daily\\StatDailyAction',
			actionName: 'getData',
			parameters: {
				startDate: $('#startDateDatePicker').val(),
				endDate: $('#endDateDatePicker').val(),
				value: $('input[name=value]:checked').val(),
				dateGrouping: $('input[name=dateGrouping]:checked').val(),
				objectTypeIDs: $objectTypeIDs
			}
		});
		this._proxy.sendRequest();
	},
	
	_success: function(data) {
		switch ($('input[name=dateGrouping]:checked').val()) {
			case 'yearly':
				var $minTickSize = [1, "year"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.yearly');
				break;
			case 'monthly':
				var $minTickSize = [1, "month"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.monthly');
				break;
			case 'weekly':
				var $minTickSize = [7, "day"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.weekly');
				break;
			default:
				var $minTickSize = [1, "day"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.daily');
		}
		
		var options = {
			series: {
				lines: {
					show: true
				},
				points: {
					show: true
				}
			},
			grid: {
				hoverable: true
			},
			xaxis: {
				mode: "time",
				minTickSize: $minTickSize,
				timeformat: $timeFormat,
				monthNames: WCF.Language.get('__monthsShort')
			},
			yaxis: {
				min: 0,
				tickDecimals: 0,
				tickFormatter: function(val) {
					return WCF.String.addThousandsSeparator(val);
				}
			}
		};
		
		var $data = [ ];
		for (var $key in data.returnValues) {
			var $row = data.returnValues[$key];
			for (var $i = 0; $i < $row.data.length; $i++) {
				$row.data[$i][0] *= 1000;
			}
			
			$data.push($row);
		}
		
		$.plot("#chart", $data, options);
		
		require(['Ui/Alignment'], function (UiAlignment) {
			var span = elCreate('span');
			span.style.setProperty('position', 'absolute', '');
			document.body.appendChild(span);
			$("#chart").on("plothover", function(event, pos, item) {
				if (item) {
					span.style.setProperty('top', item.pageY + 'px', '');
					span.style.setProperty('left', item.pageX + 'px', '');
					$("#chartTooltip").html(item.series.xaxis.tickFormatter(item.datapoint[0], item.series.xaxis) + ', ' + WCF.String.formatNumeric(item.datapoint[1]) + ' ' + item.series.label).show();
					UiAlignment.set($("#chartTooltip")[0], span, {
						verticalOffset: 5,
						horizontal: 'center',
						vertical: 'top'
					});
				}
				else {
					$("#chartTooltip").hide();
				}
			});
		});
		
		if (!$data.length) {
			$('#chart').append('<p style="position: absolute; font-size: 1.2rem; text-align: center; top: 50%; margin-top: -20px; width: 100%">' + WCF.Language.get('wcf.acp.stat.noData') + '</p>');
		}
		
		elBySel('.contentHeader > .contentTitle').scrollIntoView({ behavior: 'smooth' });
	}
});

/**
 * Namespace for ACP ad management.
 */
WCF.ACP.Ad = { };

/**
 * Handles the location of an ad during ad creation/editing.
 */
WCF.ACP.Ad.LocationHandler = Class.extend({
	/**
	 * fieldset of the page conditions
	 * @var	jQuery
	 */
	_pageConditions: null,
	
	/**
	 * select elements for the page controller condition
	 * @var	jQuery[]
	 */
	_pageInputs: [],
	
	/**
	 * page controller condition container
	 * @var	jQuery[]
	 */
	_pageSelectionContainer: null,
	
	/**
	 * Initializes a new WCF.ACP.Ad.LocationHandler object.
	 * 
	 * @param	{object}	variablesDescriptions
	 */
	init: function(variablesDescriptions) {
		this._variablesDescriptions = variablesDescriptions;
		
		this._pageConditions = $('#pageConditions');
		this._pageInputs = $('input[name="pageIDs[]"]');
		
		this._variablesDescriptionsList = $('#ad').next('small').children('ul');
		
		this._pageSelectionContainer = $(this._pageInputs[0]).parents('dl:eq(0)');
		
		// hide the page controller elements
		this._hidePageSelection(true);
		
		$('#objectTypeID').on('change', $.proxy(this._setPageController, this));
		
		this._setPageController();
		
		$('#adForm').submit($.proxy(this._submit, this));
	},
	
	/**
	 * Hides the page selection form field.
	 * 
	 * @since	5.2
	 */
	_hidePageSelection: function(addEventListeners) {
		this._pageSelectionContainer.prev('dl').hide();
		this._pageSelectionContainer.hide();
		
		// fix the margin of a potentially next page condition element
		this._pageSelectionContainer.next('dl').css('margin-top', 0);
		
		var section = this._pageSelectionContainer.parent('section');
		if (!section.children('dl:visible').length) {
			section.hide();
			
			var nextSection = section.next('section');
			if (nextSection) {
				nextSection.css('margin-top', 0);
				
				if (addEventListeners) {
					require(['EventHandler'], function(EventHandler) {
						EventHandler.add('com.woltlab.wcf.pageConditionDependence', 'checkVisivility', function() {
							if (section.is(':visible')) {
								nextSection.css('margin-top', '40px');
							}
							else {
								nextSection.css('margin-top', 0);
							}
						});
					});
				}
			}
		}
	},
	
	/**
	 * Shows the page selection form field.
	 * 
	 * @since	5.2
	 */
	_showPageSelection: function() {
		this._pageSelectionContainer.prev('dl').show();
		this._pageSelectionContainer.show();
		this._pageSelectionContainer.next('dl').css('margin-top', '40px');
		
		var section = this._pageSelectionContainer.parent('section');
		section.show();
		
		var nextSection = section.next('section');
		if (nextSection) {
			nextSection.css('margin-top', '40px');
		}
	},
	
	/**
	 * Sets the page controller based on the selected ad location.
	 */
	_setPageController: function() {
		var option = $('#objectTypeID').find('option:checked');
		var parent = option.parent();
		
		// the page controller can be explicitly set for global positions
		if (parent.is('optgroup') && parent.data('categoryName') === 'com.woltlab.wcf.global') {
			this._showPageSelection();
		}
		else {
			this._hidePageSelection();
			
			require(['Core'], function(Core) {
				var input, triggerEvent;
				
				// select the related page
				for (var i = 0, length = this._pageInputs.length; i < length; i++) {
					input = this._pageInputs[i];
					triggerEvent = false;
					
					if (option.data('page') && elData(input, 'identifier') === option.data('page')) {
						if (!input.checked) triggerEvent = true;
						
						input.checked = true;
					}
					else {
						if (input.checked) triggerEvent = true;
						
						input.checked = false;
					}
					
					if (triggerEvent) Core.triggerEvent(this._pageInputs[i], 'change');
				}
			}.bind(this));
		}
		
		this._variablesDescriptionsList.children(':not(.jsDefaultItem)').remove();
		
		var objectTypeId = $('#objectTypeID').val();
		if (objectTypeId in this._variablesDescriptions) {
			this._variablesDescriptionsList[0].innerHTML += this._variablesDescriptions[objectTypeId];
		}
	},
	
	/**
	 * Handles submitting the ad form.
	 */
	_submit: function() {
		if (this._pageConditions.is(':hidden')) {
			// remove hidden page condition form elements to avoid creation
			// of these conditions
			this._pageConditions.find('select, input').remove();
		}
		else if (this._pageSelectionContainer.is(':hidden')) {
			// reset page controller conditions to avoid creation of
			// unnecessary conditions
			for (var i = 0, length = this._pageInputs.length; i < length; i++) {
				this._pageInputs[i].checked = false;
			}
		}
	}
});

/**
 * Initialize WCF.ACP.Tag namespace.
 */
WCF.ACP.Tag = { };

/**
 * Handles setting tags as synonyms of another tag by clipboard.
 */
WCF.ACP.Tag.SetAsSynonymsHandler = Class.extend({
	/**
	 * dialog to select the "main" tag
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * ids of the selected tags
	 * @var	array<integer>
	 */
	_objectIDs: [ ],
	
	/**
	 * Initializes the SetAsSynonymsHandler object.
	 */
	init: function() {
		require(['EventHandler'], function(EventHandler) {
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.tag', this._clipboardAction.bind(this));
		}.bind(this));
	},
	
	/**
	 * Reacts to executed clipboard actions.
	 *
	 * @param	{object<string, *>}	actionData	data of the executed clipboard action
	 */
	_clipboardAction: function(actionData) {
		if (actionData.data.actionName === 'com.woltlab.wcf.tag.setAsSynonyms') {
			this._objectIDs = actionData.data.parameters.objectIDs;
			if (this._dialog === null) {
				this._dialog = $('<div id="setAsSynonymsDialog" />').hide().appendTo(document.body);
				this._dialog.wcfDialog({
					closable: false,
					title: WCF.Language.get('wcf.acp.tag.setAsSynonyms')
				});
			}
			
			this._dialog.html(actionData.data.parameters.template);
			$button = this._dialog.find('button[data-type="submit"]').disable().click($.proxy(this._submit, this));
			this._dialog.find('input[type=radio]').change(function() { $button.enable(); });
		}
	},
	
	/**
	 * Saves the tags as synonyms.
	 */
	_submit: function() {
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'setAsSynonyms',
				className: 'wcf\\data\\tag\\TagAction',
				objectIDs: this._objectIDs,
				parameters: {
					tagID: this._dialog.find('input[name="tagID"]:checked').val()
				}
			},
			success: $.proxy(function() {
				this._dialog.wcfDialog('close');
				
				new WCF.System.Notification().show(function() {
					window.location.reload();
				});
			}, this)
		});
	}
});
