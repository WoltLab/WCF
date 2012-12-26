/**
 * Class and function collection for WCF ACP
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
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
				var $notification = new WCF.System.Notification(WCF.Language.get('wcf.acp.application.setAsPrimary.success'));
				$notification.show();
				
				// remove button
				$('#setAsPrimary').parent().remove();
				
				// insert icon
				WCF.DOMNodeInsertedHandler.enable();
				$('<img src="' + WCF.Icon.get('wcf.icon.home') + '" alt="" class="icon16 jsTooltip" title="' + WCF.Language.get('wcf.acp.application.primaryApplication') + '" />').appendTo($('.boxHeadline > hgroup > h1'));
				WCF.DOMNodeInsertedHandler.disable();
			}, this)
		});
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
WCF.ACP.Package = {};

/**
 * Paginated package list.
 * 
 * @param	integer		pages
 */
WCF.ACP.Package.List = Class.extend({
	/**
	 * page cache
	 * @var	object
	 */
	_pages: {},
	
	/**
	 * plugin list references
	 * @var	object
	 */
	_pluginLists: [],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * target container
	 * @var	jQuery
	 */
	_template: null,
	
	/**
	 * Initializes the package list.
	 * 
	 * @param	integer		pages
	 */
	init: function(pages) {
		// handle pagination
		$('.jsPluginListPagination').each($.proxy(function(index, pluginList) {
			var $wcfPages = $(pluginList).wcfPages({
				activePage: 1,
				maxPage: pages
			}).bind('wcfpagesshouldswitch', $.proxy(this._cachePage, this)).bind('wcfpagesswitched', $.proxy(this._loadPage, this));
			
			this._pluginLists.push($wcfPages);
		}, this));
		
		// initialize
		if (this._pluginLists.length > 0) {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			this._template = $('#plugins ol');
		}
	},
	
	/**
	 * Caches currently active page.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_cachePage: function(event, data) {
		if (!this._pages[data.currentPage]) {
			this._pages[data.currentPage] = $('#plugins ol').html();
		}
	},
	
	/**
	 * Loads the request page using AJAX.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_loadPage: function(event, data) {
		// update active page
		for (var $i = 0, $size = this._pluginLists.length; $i < $size; $i++) {
			this._pluginLists[$i].wcfPages('switchPage', data.activePage);
		}
		
		// load page from cache if applicable
		if (this._pages[data.activePage]) {
			this._template.html(this._pages[data.activePage]);
			return;
		}
		
		// load content using AJAX
		this._proxy.setOption('data', {
			actionName: 'getPluginList',
			className: 'wcf\\data\\package\\PackageAction',
			parameters: {
				activePage: data.activePage
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Displays the fetched page.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._pages[data.returnValues.activePage] = data.returnValues.template;
		this._loadPage(null, { activePage: data.returnValues.activePage });
	}
});

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
	 */
	init: function(queueID, actionName, allowRollback) {
		this._actionName = (actionName) ? actionName : 'InstallPackage';
		this._allowRollback = (allowRollback === true) ? true : false;
		this._queueID = queueID;
		
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
	 * 
	 * @param	jQuery		jqXHR
	 * @param	string		textStatus
	 * @param	string		errorThrown
	 * @param	string		responseText
	 */
	_failure: function(jqXHR, textStatus, errorThrown, responseText) {
		if (!this._allowRollback) {
			return;
		}
		
		this._purgeTemplateContent($.proxy(function() {
			var $form = $('<div class="formSubmit" />').appendTo($('#packageInstallationInnerContent'));
			$('<button class="buttonPrimary">' + WCF.Language.get('wcf.acp.package.installation.rollback') + '</button>').appendTo($form).click($.proxy(this._rollback, this));
			
			$('#packageInstallationInnerContentContainer').show();
			
			this._dialog.wcfDialog('render');
		}, this));
	},
	
	/**
	 * Performs a rollback.
	 */
	_rollback: function() {
		this._executeStep('rollback');
	},
	
	/**
	 * Prepares installation dialog.
	 */
	prepareInstallation: function() {
		WCF.showAJAXDialog('packageInstallationDialog', true, {
			ajax: true,
			closable: false,
			data: this._getParameters(),
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			title: WCF.Language.get('wcf.acp.package.installation.title'),
			url: 'index.php/' + this._actionName + '/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
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
		
		if (data.step == 'rollback') {
			this._dialog.wcfDialog('close');
			
			new WCF.PeriodicalExecuter(function(pe) {
				pe.stop();
				
				var $uninstallation = new WCF.ACP.Package.Uninstallation();
				$uninstallation.start(data.packageID);
			}, 200);
			
			return;
		}
		
		if (this._dialog == null) {
			this._dialog = $('#packageInstallationDialog');
		}
		
		// receive new queue id
		if (data.queueID) {
			this._queueID = data.queueID;
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
			this._purgeTemplateContent($.proxy(function() {
				var $form = $('<div class="formSubmit" />').appendTo($('#packageInstallationInnerContent'));
				$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.next') + '</button>').appendTo($form).click(function() { window.location = data.redirectLocation; });
				
				$('#packageInstallationInnerContentContainer').show();
				
				this._dialog.wcfDialog('render');
			}, this));
			
			return;
		}
		
		// update template
		if (data.template && !data.ignoreTemplate) {
			this._dialog.html(data.template);
			this._shouldRender = true;
		}
		
		// handle inner template
		if (data.innerTemplate) {
			var self = this;
			$('#packageInstallationInnerContent').html(data.innerTemplate).find('input').keyup(function(event) {
				if (event.keyCode === 13) { // Enter
					self._submit(data);
				}
			});
			
			// create button to handle next step
			if (data.step && data.node) {
				var $form = $('<div class="formSubmit" />').appendTo($('#packageInstallationInnerContent'));
				$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.next') + '</button>').appendTo($form).click($.proxy(function() { this._submit(data); }, this)); 
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
		// collect form values
		var $additionalData = {};
		$('#packageInstallationInnerContent input').each(function(index, inputElement) {
			var $inputElement = $(inputElement);
			var $type = $inputElement.attr('type');
			
			if (($type == 'checkbox' || $type == 'radio') && !$inputElement.attr('checked')) {
				return false;
			}
			
			$additionalData[$inputElement.attr('name')] = $inputElement.val();
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
	}
});

/**
 * Provides the package uninstallation.
 * 
 * @param	jQuery		elements
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
	 * Initializes the WCF.ACP.Package.Uninstallation class.
	 * 
	 * @param	jQuery		elements
	 */
	init: function(elements) {
		this._elements = elements;
		this._packageID = 0;
		
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
		var $element = $(event.target);
		
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
 * Namespace for page menu.
 */
WCF.ACP.PageMenu = { };

/**
 * Allows menu items to be set as landing page.
 * 
 * @param	integer		menuItemID
 */
WCF.ACP.PageMenu.SetAsLandingPage = Class.extend({
	/**
	 * menu item id
	 * @var	integer
	 */
	_menuItemID: 0,
	
	/**
	 * Initializes the WCF.ACP.PageMenu.SetAsLandingPage class.
	 * 
	 * @param	integer		menuItemID
	 */
	init: function(menuItemID) {
		this._menuItemID = menuItemID;
		
		$('#setAsLandingPage').click($.proxy(this._click, this));
	},
	
	/**
	 * Handles button clicks.
	 */
	_click: function() {
		var self = this;
		WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.pageMenu.isLandingPage.confirmMessage'), function(action) {
			if (action === 'confirm') {
				new WCF.Action.Proxy({
					autoSend: true,
					data: {
						actionName: 'setAsLandingPage',
						className: 'wcf\\data\\page\\menu\\item\\PageMenuItemAction',
						objectIDs: [ self._menuItemID ]
					},
					success: $.proxy(self._success, self)
				});
			}
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
		var $notification = new WCF.System.Notification(WCF.Language.get('wcf.acp.pageMenu.isLandingPage.success'));
		$notification.show(function() { window.location.reload(); });
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
						this._execute(option.attr('checked'), $disableOptions, $enableOptions);
					break;
					
					case 'radio':
						if (option.attr('checked')) {
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
 * Single-option handling for user group options.
 * 
 * @param	boolean		canEditEveryone
 */
WCF.ACP.Options.Group = Class.extend({
	/**
	 * true, if user can edit the 'Everyone' group
	 * @var	boolean
	 */
	_canEditEveryone: false,
	
	/**
	 * Initializes the WCF.ACP.Options.Group class.
	 * 
	 * @param	boolean		canEditEveryone
	 */
	init: function(canEditEveryone) {
		// disable 'Everyone' input
		this._canEditEveryone = (canEditEveryone === true) ? true : false;
		var $defaultContainer = $('#defaultValueContainer');
		var $defaultValue = $defaultContainer.find('input, textarea').attr('id', 'optionValue' + $defaultContainer.children('dl').data('groupID')).removeAttr('name');
		if (!this._canEditEveryone) {
			$defaultValue.attr('disabled', 'disabled');
		}
		
		// fix id and remove name-attribute from input elements
		$('#otherValueContainer > dl').each(function(index, container) {
			var $container = $(container);
			$container.find('input, textarea').removeAttr('name').attr('id', 'optionValue' + $container.data('groupID'));
		});
		
		// bind event listener
		$('#submitButton').click($.proxy(this._click, this));
	},
	
	/**
	 * Handles clicks on the submit button.
	 */
	_click: function() {
		var $values = { };
		
		// collect default value
		if (this._canEditEveryone) {
			var $container = $('#defaultValueContainer > dl');
			
			var $value = this._getValue($container);
			if ($value !== null) {
				$values[$container.data('groupID')] = $value;
			}
		}
		
		// collect values from other groups
		var self = this;
		$('#otherValueContainer > dl').each(function(index, container) {
			var $container = $(container);
			
			var $value = self._getValue($container);
			if ($value !== null) {
				$values[$container.data('groupID')] = $value;
			}
		});
		
		var $form = $('#defaultValueContainer').parent('form');
		var $formSubmit = $form.children('.formSubmit');
		for (var $groupID in $values) {
			$('<input type="hidden" name="values[' + $groupID + ']" value="' + $values[$groupID] + '" />').appendTo($formSubmit);
		}
		
		// disable submit button
		$('#submitButton').attr('disable', 'disable');
		
		$form.submit();
	},
	
	/**
	 * Returns the value of an input or textarea.
	 * 
	 * @param	jQuery		container
	 * @return	string
	 */
	_getValue: function(container) {
		var $textarea = container.find('textarea');
		if ($textarea.length) {
			return $textarea.val();
		}
		else {
			var $input = container.find('input');
			if (!$input.length) {
				return null;
			}
			
			if ($input.attr('type') == 'checkbox') {
				if ($input.is(':checked')) {
					return $input.val();
				}
				
				return null;
			}
			
			return $input.val();
		}
	}
});

/**
 * Worker support for ACP.
 * 
 * @param	string		dialogID
 * @param	string		className
 * @param	object		options
 */
WCF.ACP.Worker = Class.extend({
	/**
	 * true, if worker was aborted
	 * @var	boolean
	 */
	_aborted: false,
	
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
	 * Initializes a new worker instance.
	 * 
	 * @param	string		dialogID
	 * @param	string		className
	 * @param	string		title
	 * @param	object		parameters
	 */
	init: function(dialogID, className, title, parameters) {
		this._aborted = false;
		this._dialogID = dialogID + 'Worker';
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this),
			url: 'index.php/WorkerProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		// initialize AJAX-based dialog
		WCF.showAJAXDialog(this._dialogID, true, {
			url: 'index.php/WorkerProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND,
			type: 'POST',
			data: {
				className: className,
				parameters: parameters || { }
			},
			success: $.proxy(this._success, this),
			onClose: $.proxy(function() { this._aborted = true; }, this),
			title: title
		});
	},
	
	/**
	 * Handles response from server.
	 * 
	 * @param	object		data
	 */
	_success: function(data) {
		if (this._aborted) {
			return;
		}
		
		// init binding
		if (this._dialog === null) {
			this._dialog = $('#' + $.wcfEscapeID(this._dialogID));
		}
		
		// update progress
		this._dialog.find('progress').attr('value', data.progress).text(data.progress + '%').next('span').text(data.progress + '%');
		
		// worker is still busy with it's business, carry on
		if (data.progress < 100) {
			// send request for next loop
			this._proxy.setOption('data', {
				className: data.className,
				loopCount: data.loopCount,
				parameter: data.parameters
			});
			this._proxy.sendRequest();
		}
		else {
			// display continue button
			var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
			$('<button>' + WCF.Language.get('wcf.global.button.next') + '</button>').appendTo($formSubmit).click(function() { window.location = data.proceedURL; });
			
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
 * @see	WCF.Action.Delete
 */
WCF.ACP.Category.Delete = WCF.Action.Delete.extend({
	/**
	 * @see	WCF.Action.Delete.triggerEffect()
	 */
	triggerEffect: function(objectIDs) {
		for (var $index in this._containers) {
			var $container = $('#' + this._containers[$index]);
			if (WCF.inArray($container.find('.jsDeleteButton').data('objectID'), objectIDs)) {
				// move child categories up
				if ($container.has('ol').has('li')) {
					if ($container.is(':only-child')) {
						$container.parent().replaceWith($container.find('> ol'));
					}
					else {
						$container.replaceWith($container.find('> ol > li'));
					}
				}
				else {
					$container.wcfBlindOut('up', function() { $container.remove(); });
				}
			}
		}
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
			
			$('<li><a href="' + $item.link + '">' + $item.title + '</a></li>').appendTo(this._list);
			
			this._itemCount++;
		}
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
		window.location = this._list.find('li.dropdownNavigationItem > a').attr('href');
	}
});
