/**
 * Class and function collection for WCF ACP
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Initialize WCF.ACP namespace
 */
WCF.ACP = {};

/**
 * Namespace for ACP application management.
 */
WCF.ACP.Application = { };

/**
 * Provides the ability to set an application as primary.
 * 
 * @param	integer		packageID
 */
WCF.ACP.Application.SetAsPrimary = Class.extend({
	/**
	 * application package id
	 * @var	integer
	 */
	_packageID: 0,
	
	/**
	 * Initializes the WCF.ACP.Application.SetAsPrimary class.
	 * 
	 * @param	integer		packageID
	 */
	init: function(packageID) {
		this._packageID = packageID;
		
		$('#setAsPrimary').click($.proxy(this._click, this));
	},
	
	/**
	 * Shows a confirmation dialog to set current application as primary.
	 */
	_click: function() {
		WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.application.setAsPrimary.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._setAsPrimary();
			}
		}, this));
	},
	
	/**
	 * Sets an application as primary.
	 */
	_setAsPrimary: function() {
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'setAsPrimary',
				className: 'wcf\\data\\application\\ApplicationAction',
				objectIDs: [ this._packageID ]
			},
			success: $.proxy(function(data, textStatus, jqXHR) {
				var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
				$notification.show();
				
				// remove button
				$('#setAsPrimary').parent().remove();
				
				// insert icon
				$headline = $('.boxHeadline > h1');
				$headline.html($headline.html() + ' ');
				$('<span class="icon icon16 icon-ok-sign jsTooltip" title="' + WCF.Language.get('wcf.acp.application.primaryApplication') + '" />').appendTo($headline);
				
				WCF.DOMNodeInsertedHandler.execute();
			}, this)
		});
	}
});

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
 * Handles ACPMenu.
 * 
 * @param	array<string>		activeMenuItems
 */
WCF.ACP.Menu = Class.extend({
	/**
	 * Initializes ACPMenu.
	 * 
	 * @param	array		activeMenuItems
	 */
	init: function(activeMenuItems) {
		this._headerNavigation = $('nav#mainMenu');
		this._sidebarNavigation = $('aside.collapsibleMenu > div');
		
		this._prepareElements(activeMenuItems);
	},
	
	/**
	 * Resets all elements and binds event listeners.
	 */
	_prepareElements: function(activeMenuItems) {
		this._headerNavigation.find('li').removeClass('active');
		
		this._sidebarNavigation.find('legend').each($.proxy(function(index, menuHeader) {
			$(menuHeader).click($.proxy(this._toggleItem, this));
		}, this));
		
		// close all navigation groups
		this._sidebarNavigation.find('nav ul').each(function() {
			$(this).hide();
		});
		
		this._headerNavigation.find('li').click($.proxy(this._toggleSidebar, this));
		
		if (activeMenuItems.length === 0) {
			this._renderSidebar(this._headerNavigation.find('li:first').data('menuItem'), []);
		}
		else {
			this._renderSidebar('', activeMenuItems);
		}
	},
	
	/**
	 * Toggles a navigation group entry.
	 */
	_toggleItem: function(event) {
		var $menuItem = $(event.currentTarget);
		
		$menuItem.parent().find('nav ul').stop(true, true).toggle('blind', { }, 200).end();
		$menuItem.toggleClass('active');
	},
	
	/**
	 * Handles clicks on main menu.
	 * 
	 * @param	object		event
	 */
	_toggleSidebar: function(event) {
		var $target = $(event.currentTarget);
		
		if ($target.hasClass('active')) {
			return;
		}
		
		this._renderSidebar($target.data('menuItem'), []);
	},
	
	/**
	 * Renders sidebar including highlighting of currently active menu items.
	 * 
	 * @param	string		menuItem
	 * @param	array		activeMenuItems
	 */
	_renderSidebar: function(menuItem, activeMenuItems) {
		// reset visible and active items
		this._headerNavigation.find('li').removeClass('active');
		this._sidebarNavigation.find('> div').hide();
		
		if (activeMenuItems.length === 0) {
			// show active menu
			this._headerNavigation.find('li[data-menu-item="' + menuItem + '"]').addClass('active');
			this._sidebarNavigation.find('div[data-parent-menu-item="' + menuItem + '"]').show();
		}
		else {
			// open menu by active menu items, first element is always a head navigation item
			menuItem = activeMenuItems.shift();
			
			this._headerNavigation.find('li[data-menu-item="' + menuItem + '"]').addClass('active');
			this._sidebarNavigation.find('div[data-parent-menu-item="' + menuItem + '"]').show();
			
			for (var $i = 0, $size = activeMenuItems.length; $i < $size; $i++) {
				var $item = activeMenuItems[$i];
				
				if ($.wcfIsset($item)) {
					var $menuItem = $('#' + $.wcfEscapeID($item));
					
					if ($menuItem.getTagName() === 'ul') {
						$menuItem.show().parents('fieldset').children('legend').addClass('active');
					}
					else {
						$menuItem.addClass('active');
					}
				}
			}
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
	 */
	init: function(queueID, actionName, allowRollback, isUpdate) {
		this._actionName = (actionName) ? actionName : 'InstallPackage';
		this._allowRollback = (allowRollback === true) ? true : false;
		this._queueID = queueID;
		
		switch (this._actionName) {
			case 'InstallPackage':
				this._dialogTitle = 'wcf.acp.package.' + (isUpdate ? 'update' : 'install') + '.title';
			break;
			
			case 'UninstallPackage':
				this._dialogTitle = 'wcf.acp.package.uninstallation.title';
			break;
		}
		
		this._initProxy();
		this._init();
	},
	
	/**
	 * Initializes the WCF.Action.Proxy object.
	 */
	_initProxy: function() {
		this._proxy = new WCF.Action.Proxy({
			failure: $.proxy(this._failure, this),
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php/' + this._actionName + '/?t=' + SECURITY_TOKEN + SID_ARG_2ND
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
			this._setIcon('remove');
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
		this._proxy.setOption('data', this._getParameters());
		this._proxy.sendRequest();
	},
	
	/**
	 * Returns parameters to prepare installation.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return {
			queueID: this._queueID,
			step: 'prepare'
		};
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
		
		if (this._dialog === null) {
			this._dialog = $('<div id="packageInstallationDialog" />').hide().appendTo(document.body);
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
			this._setIcon('ok');
			
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
		var $additionalData = {};
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
		if ($('#packageInstallationInnerContent').children().length > 1) {
			$('#packageInstallationInnerContentContainer').wcfBlindOut('vertical', $.proxy(function() {
				$('#packageInstallationInnerContent').empty();
				this._shouldRender = true;
				
				// execute callback
				callback();
			}, this));
		}
		else {
			callback();
		}
	},
	
	/**
	 * Executes the next installation step.
	 * 
	 * @param	string		step
	 * @param	string		node
	 * @param	object		additionalData
	 */
	_executeStep: function(step, node, additionalData) {
		if (!additionalData) additionalData = {};
		
		var $data = $.extend({
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
		this._dialog.find('.jsPackageInstallationStatus').removeClass('icon-ok icon-question icon-remove icon-spinner').addClass('icon-' + iconName);
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
		this._elements.click($.proxy(this._prepareQueue, this));
	},
	
	/**
	 * Prepares a new package uninstallation queue.
	 * 
	 * @param	object		event
	 */
	_prepareQueue: function(event) {
		var $element = $(event.currentTarget);
		
		if ($element.data('isRequired')) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'getConfirmMessage',
					className: 'wcf\\data\\package\\PackageAction',
					objectIDs: [ $element.data('objectID') ]
				},
				success: $.proxy(function(data, textStatus, jqXHR) {
					// remove isRequired flag to prevent loading the same content again
					$element.data('isRequired', false);
					
					// update confirmation message
					$element.data('confirmMessage', data.returnValues.confirmMessage);
					
					// display confirmation dialog
					this._showConfirmationDialog($element);
				}, this)
			});
		}
		else {
			this._showConfirmationDialog($element);
		}
	},
	
	/**
	 * Displays a confirmation dialog prior to package uninstallation.
	 * 
	 * @param	jQuery		element
	 */
	_showConfirmationDialog: function(element) {
		if (element.data('isApplication') && this._wcfPackageListURL) {
			window.location = WCF.String.unescapeHTML(this._wcfPackageListURL.replace(/{packageID}/, element.data('objectID')));
			return;
		}
		
		var self = this;
		WCF.System.Confirmation.show(element.data('confirmMessage'), function(action) {
			if (action === 'confirm') {
				self._packageID = element.data('objectID');
				self.prepareInstallation();
			}
		});
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

/**
 * Manages package search.
 */
WCF.ACP.Package.Search = Class.extend({
	/**
	 * search button
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * list of cached pages
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * search container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * package input field
	 * @var	jQuery
	 */
	_package: null,
	
	/**
	 * package description input field
	 * @var	jQuery
	 */
	_packageDescription: null,
	
	/**
	 * package name input field
	 * @var	jQuery
	 */
	_packageName: null,
	
	/**
	 * package search result container
	 * @var	jQuery
	 */
	_packageSearchResultContainer: null,
	
	/**
	 * package search result list
	 * @var	jQuery
	 */
	_packageSearchResultList: null,
	
	/**
	 * number of pages
	 * @var	integer
	 */
	_pageCount: 0,
	
	/**
	 * current page
	 * @var	integer
	 */
	_pageNo: 1,
	
	/**
	 * action proxy
	 * @var	WCF.Action:proxy
	 */
	_proxy: null,
	
	/**
	 * search id
	 * @var	integer
	 */
	_searchID: 0,
	
	/**
	 * currently selected package
	 * @var	string
	 */
	_selectedPackage: '',
	
	/**
	 * currently selected package's version
	 */
	_selectedPackageVersion: '',
	
	/**
	 * Initializes the WCF.ACP.Package.Seach class.
	 */
	init: function() {
		this._button = null;
		this._cache = { };
		this._container = $('#packageSearch');
		this._dialog = null;
		this._package = null;
		this._packageName = null;
		this._packageSearchResultContainer = $('#packageSearchResultContainer');
		this._packageSearchResultList = $('#packageSearchResultList');
		this._pageCount = 0;
		this._pageNo = 1;
		this._searchDescription = null;
		this._searchID = 0;
		this._selectedPackage = '';
		this._selectedPackageVersion = '';
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initElements();
	},
	
	/**
	 * Initializes search elements.
	 */
	_initElements: function() {
		this._button = this._container.find('.formSubmit > button.jsButtonPackageSearch').disable().click($.proxy(this._search, this));
		
		this._package = $('#package').keyup($.proxy(this._keyUp, this));
		this._packageDescription = $('#packageDescription').keyup($.proxy(this._keyUp, this));
		this._packageName = $('#packageName').keyup($.proxy(this._keyUp, this));
	},
	
	/**
	 * Handles the 'keyup' event.
	 */
	_keyUp: function(event) {
		if (this._package.val() === '' && this._packageDescription.val() === '' && this._packageName.val() === '') {
			this._button.disable();
		}
		else {
			this._button.enable();
			
			// submit on [Enter]
			if (event.which === 13) {
				this._button.trigger('click');
			}
		}
	},
	
	/**
	 * Performs a new search.
	 */
	_search: function() {
		var $values = this._getSearchValues();
		if (!this._validate($values)) {
			return false;
		}
		
		$values.pageNo = this._pageNo;
		this._proxy.setOption('data', {
			actionName: 'search',
			className: 'wcf\\data\\package\\update\\PackageUpdateAction',
			parameters: $values
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Returns search values.
	 * 
	 * @return	object
	 */
	_getSearchValues: function() {
		return {
			'package': $.trim(this._package.val()),
			packageDescription: $.trim(this._packageDescription.val()),
			packageName: $.trim(this._packageName.val())
		};
	},
	
	/**
	 * Validates search values.
	 * 
	 * @param	object		values
	 * @return	boolean
	 */
	_validate: function(values) {
		if (values['package'] === '' && values['packageDescription'] === '' && values['packageName'] === '') {
			return false;
		}
		
		return true;
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
			case 'getResultList':
				this._insertTemplate(data.returnValues.template);
			break;
			
			case 'prepareInstallation':
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
			break;
			
			case 'search':
				this._pageCount = data.returnValues.pageCount;
				this._searchID = data.returnValues.searchID;
				
				this._insertTemplate(data.returnValues.template, (data.returnValues.count === undefined ? undefined : data.returnValues.count));
				this._setupPagination();
			break;
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
	 * Inserts search result list template.
	 * 
	 * @param	string		template
	 * @param	integer		count
	 */
	_insertTemplate: function(template, count) {
		this._packageSearchResultContainer.show();
		
		this._packageSearchResultList.html(template);
		if (count === undefined) {
			this._content[this._pageNo] = template;
		}
		
		// update badge count
		if (count !== undefined) {
			this._content = { 1: template };
			this._packageSearchResultContainer.find('> header > h2 > .badge').html(count);
		}
		
		// bind listener
		this._packageSearchResultList.find('.jsInstallPackage').click($.proxy(this._click, this));
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
		}, this));
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
	
	/**
	 * Setups pagination for current search.
	 */
	_setupPagination: function() {
		// remove previous instances
		this._content = { 1: this._packageSearchResultList.html() };
		this._packageSearchResultContainer.find('.pageNavigation').wcfPages('destroy').remove();
		
		if (this._pageCount > 1) {
			$('<div class="contentNavigation" />').insertBefore(this._packageSearchResultList).wcfPages({
				activePage: this._pageNo,
				maxPage: this._pageCount
			}).bind('wcfpagesswitched', $.proxy(this._showPage, this));
			
			$('<div class="contentNavigation" />').insertAfter(this._packageSearchResultList).wcfPages({
				activePage: this._pageNo,
				maxPage: this._pageCount
			}).bind('wcfpagesswitched', $.proxy(this._showPage, this));
		}
	},
	
	/**
	 * Displays requested pages or loads it.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_showPage: function(event, data) {
		if (data && data.activePage) {
			this._pageNo = data.activePage;
		}
		
		// validate page no
		if (this._pageNo < 1 || this._pageNo > this._pageCount) {
			console.debug("[WCF.ACP.Package.Search] Cannot access page " + this._pageNo + " of " + this._pageCount);
			return;
		}
		
		// load content
		if (this._content[this._pageNo] === undefined) {
			this._proxy.setOption('data', {
				actionName: 'getResultList',
				className: 'wcf\\data\\package\\update\\PackageUpdateAction',
				parameters: {
					pageNo: this._pageNo,
					searchID: this._searchID
				}
			});
			this._proxy.sendRequest();
		}
		else {
			// show cached content
			this._packageSearchResultList.html(this._content[this._pageNo]);
			
			WCF.DOMNodeInsertedHandler.execute();
		}
	}
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
			this._click(undefined, $(event.currentTarget).data('packageUpdateServerID'))
		}
	}
});

/**
 * Searches for available updates.
 */
WCF.ACP.Package.Update.Search = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * initializes the WCF.ACP.Package.SearchForUpdates class.
	 */
	init: function() {
		this._dialog = null;
		
		var $button = $('<li><a class="button"><span class="icon icon16 icon-refresh"></span> <span>' + WCF.Language.get('wcf.acp.package.searchForUpdates') + '</span></a></li>');
		$button.click($.proxy(this._click, this)).prependTo($('.contentNavigation:eq(0) > nav > ul'));
	},
	
	/**
	 * Handles clicks on the search button.
	 */
	_click: function() {
		if (this._dialog === null) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'searchForUpdates',
					className: 'wcf\\data\\package\\update\\PackageUpdateAction'
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
 * Handles option selection.
 */
WCF.ACP.Options = Class.extend({
	/**
	 * Initializes options.
	 */
	init: function() {
		$('.jsEnablesOptions').each($.proxy(this._initOption, this));
	},
	
	/**
	 * Initializes an option.
	 * 
	 * @param	integer		index
	 * @param	object		option
	 */
	_initOption: function(index, option) {
		// execute action on init
		this._change(option);
		
		// bind event listener
		$(option).change($.proxy(this._handleChange, this));
	},
	
	/**
	 * Applies whenever an option is changed.
	 * 
	 * @param	object		event
	 */
	_handleChange: function(event) {
		this._change($(event.target));
	},
	
	/**
	 * Enables or disables options on option value change.
	 * 
	 * @param	object		option
	 */
	_change: function(option) {
		option = $(option);
		
		var $disableOptions = eval(option.data('disableOptions'));
		var $enableOptions = eval(option.data('enableOptions'));
		
		// determine action by type
		switch(option.getTagName()) {
			case 'input':
				switch(option.attr('type')) {
					case 'checkbox':
						this._execute(option.prop('checked'), $disableOptions, $enableOptions);
					break;
					
					case 'radio':
						if (option.prop('checked')) {
							this._execute(true, $disableOptions, $enableOptions);
						}
					break;
				}
			break;
			
			case 'select':
				var $value = option.val();
				var $disableOptions = $enableOptions = [];
				
				if (option.data('disableOptions').length > 0) {
					for (var $index in option.data('disableOptions')) {
						var $item = option.data('disableOptions')[$index];
						
						if ($item.value == $value) {
							$disableOptions.push($item.option);
						}
					}
				}
				
				if (option.data('enableOptions').length > 0) {
					for (var $index in option.data('enableOptions')) {
						var $item = option.data('enableOptions')[$index];
						
						if ($item.value == $value) {
							$enableOptions.push($item.option);
						}
					}
				}
				
				this._execute(true, $disableOptions, $enableOptions);
			break;
		}
	},
	
	/**
	 * Enables or disables options.
	 * 
	 * @param	boolean		isActive
	 * @param	array		disableOptions
	 * @param	array		enableOptions
	 */
	_execute: function(isActive, disableOptions, enableOptions) {
		if (disableOptions.length > 0) {
			for (var $i = 0, $size = disableOptions.length; $i < $size; $i++) {
				var $target = disableOptions[$i];
				if ($.wcfIsset($target)) {
					this._enableOption($target, !isActive);
				}
			}
		}
		
		if (enableOptions.length > 0) {
			for (var $i = 0, $size = enableOptions.length; $i < $size; $i++) {
				var $target = enableOptions[$i];
				if ($.wcfIsset($target)) {
					this._enableOption($target, isActive);
				}
			}
		}
	},
	
	/**
	 * Enables an option.
	 *
	 * @param	string		target
	 * @param	boolean		enable
	 */
	_enableOption: function(target, enable) {
		var $targetElement = $('#' + $.wcfEscapeID(target));
		var $tagName = $targetElement.getTagName();
		
		if ($tagName == 'select' || ($tagName == 'input' && ($targetElement.attr('type') == 'checkbox' || $targetElement.attr('type') == 'radio'))) {
			if (enable) $targetElement.enable();
			else $targetElement.disable();
		}
		else {
			if (enable) $targetElement.removeAttr('readonly');
			else $targetElement.attr('readonly', true);
		}
		
		if (enable) {
			$targetElement.closest('dl').removeClass('disabled');
		}
		else {
			$targetElement.closest('dl').addClass('disabled');
		}
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
 */
WCF.ACP.Worker = Class.extend({
	/**
	 * callback invoked after worker completed
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * dialog id
	 * @var	string
	 */
	_dialogID: null,
	
	/**
	 * dialog object
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * dialog title
	 * @var	string
	 */
	_title: '',
	
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
		this._callback = callback || null;
		this._dialogID = dialogID + 'Worker';
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			autoSend: true,
			data: {
				className: className,
				parameters: parameters || { }
			},
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php/WorkerProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		this._title = title;
	},
	
	/**
	 * Handles response from server.
	 * 
	 * @param	object		data
	 */
	_success: function(data) {
		// init binding
		if (this._dialog === null) {
			this._dialog = $('<div id="' + this._dialogID + '" />').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				onClose:  $.proxy(function() {
					this._proxy.abortPrevious();
				}, this),
				title: this._title
			});
		}
		
		if (data.template) {
			this._dialog.html(data.template);
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
		else if (this._callback !== null) {
			this._callback(this, data);
		}
		else {
			// display continue button
			var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
			$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.next') + '</button>').appendTo($formSubmit).click(function() { window.location = data.proceedURL; });
			
			this._dialog.wcfDialog('render');
		}
	}
});

/**
 * Namespace for category-related functions.
 */
WCF.ACP.Category = {};

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
		this._containers = {};
		this._containerData = {};
		
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
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function() {
		this._className = 'wcf\\data\\acp\\search\\provider\\ACPSearchProviderAction';
		this._super('#search input[name=q]');
		
		// disable form submitting
		$('#search > form').on('submit', function(event) {
			event.preventDefault();
		});
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
			
			$('<li><a href="' + $item.link + '">' + WCF.String.escapeHTML($item.title) + '</a></li>').appendTo(this._list);
			
			this._itemCount++;
		}
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
		this._dialog = $('<div />').hide().appendTo(document.body);
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
		
		// bind listener
		$('.jsClipboardEditor').each($.proxy(function(index, container) {
			var $container = $(container);
			var $types = eval($container.data('types'));
			if (WCF.inArray('com.woltlab.wcf.user', $types)) {
				$container.on('clipboardAction', $.proxy(this._execute, this));
				return false;
			}
		}, this));
	},
	
	/**
	 * Handles clipboard actions.
	 * 
	 * @param	object		event
	 * @param	string		type
	 * @param	string		actionName
	 * @param	object		parameters
	 */
	_execute: function(event, type, actionName, parameters) {
		if (actionName == 'com.woltlab.wcf.user.ban') {
			this.ban(parameters.objectIDs);
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
		WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.user.ban.sure'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._proxy.setOption('data', {
					actionName: 'ban',
					className: 'wcf\\data\\user\\UserAction',
					objectIDs: userIDs,
					parameters: {
						banReason: $('#userBanReason').val()
					}
				});
				this._proxy.sendRequest();
			}
		}, this), '', $('<fieldset><dl><dt><label for="userBanReason">' + WCF.Language.get('wcf.acp.user.banReason') + '</label></dt><dd><textarea id="userBanReason" cols="40" rows="3" /><small>' + WCF.Language.get('wcf.acp.user.banReason.description') + '</small></dd></dl></fieldset>'));
	},
	
	/**
	 * Handles successful AJAX calls.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		$('.jsBanButton').each(function(index, button) {
			var $button = $(button);
			if (WCF.inArray($button.data('objectID'), data.objectIDs)) {
				if (data.actionName == 'unban') {
					$button.data('banned', false).data('tooltip', $button.data('banMessage')).removeClass('icon-lock').addClass('icon-unlock');
				}
				else {
					$button.data('banned', true).data('tooltip', $button.data('unbanMessage')).removeClass('icon-unlock').addClass('icon-lock');
				}
			}
		});
		
		var $notification = new WCF.System.Notification();
		$notification.show();
		
		WCF.Clipboard.reload();
	}
};

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
		
		// bind listener
		$('.jsClipboardEditor').each($.proxy(function(index, container) {
			var $container = $(container);
			var $types = eval($container.data('types'));
			if (WCF.inArray('com.woltlab.wcf.user', $types)) {
				$container.on('clipboardAction', $.proxy(this._execute, this));
				return false;
			}
		}, this));
	},
	
	/**
	 * Handles clipboard actions.
	 * 
	 * @param	object		event
	 * @param	string		type
	 * @param	string		actionName
	 * @param	object		parameters
	 */
	_execute: function(event, type, actionName, parameters) {
		if (actionName == 'com.woltlab.wcf.user.enable') {
			this.enable(parameters.objectIDs);
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
		$('.jsEnableButton').each(function(index, button) {
			var $button = $(button);
			if (WCF.inArray($button.data('objectID'), data.objectIDs)) {
				if (data.actionName == 'disable') {
					$button.data('enabled', false).data('tooltip', $button.data('enableMessage')).removeClass('icon-check').addClass('icon-check-empty');
				}
				else {
					$button.data('enabled', true).data('tooltip', $button.data('disableMessage')).removeClass('icon-check-empty').addClass('icon-check');
				}
			}
		});
		
		var $notification = new WCF.System.Notification();
		$notification.show();
		
		WCF.Clipboard.reload();
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
	 * @var	integer
	 */
	_index: -1,
	
	/**
	 * list of object types
	 * @var	array<string>
	 */
	_objectTypes: [ ],
	
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
	 * @param	array<string>	objectTypes
	 * @param	string		redirectURL
	 */
	init: function(objectTypes, redirectURL) {
		this._currentAction = '';
		this._index = -1;
		this._objectTypes = objectTypes;
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php/WorkerProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
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
			this._dialog.find('.icon-spinner').removeClass('icon-spinner').addClass('icon-ok');
			this._dialog.find('h1').text(WCF.Language.get('wcf.acp.dataImport.completed'));
			
			var $form = $('<div class="formSubmit" />').appendTo(this._dialog.find('#workerContainer'));
			$('<button>' + WCF.Language.get('wcf.global.button.next') + '</button>').click($.proxy(function() {
				window.location = this._redirectURL;
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
