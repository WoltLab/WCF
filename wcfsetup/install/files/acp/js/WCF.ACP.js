/**
 * Class and function collection for WCF ACP
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Initialize WCF.ACP namespace
 */
WCF.ACP = {};

/**
 * Handles ACPMenu.
 *
 * @param	array		activeMenuItems
 */
WCF.ACP.Menu = function(activeMenuItems) { this.init(activeMenuItems); };
WCF.ACP.Menu.prototype = {
	/**
	 * Initializes ACPMenu.
	 *
	 * @param	array		activeMenuItems
	 */
	init: function(activeMenuItems) {
		this._headerNavigation = $('nav#mainMenu');
		this._sidebarNavigation = $('nav#sidebarContent');
		
		this._prepareElements(activeMenuItems);
	},
	
	/**
	 * Resets all elements and binds event listeners.
	 */
	_prepareElements: function(activeMenuItems) {
		this._headerNavigation.find('li').removeClass('activeMenuItem');
		
		this._sidebarNavigation.find('div h1').each($.proxy(function(index, menuHeader) {
			$(menuHeader).click($.proxy(this._toggleItem, this));
		}, this));
		
		// close all navigation groups
		this._sidebarNavigation.find('div div').each(function() {
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
		var $menuItem = $(event.target);
		
		$menuItem.next().stop(true, true).toggle('blind', { }, 200).end().toggleClass('activeMenuItem');
	},
	
	/**
	 * Handles clicks on main menu.
	 *
	 * @param	object		event
	 */
	_toggleSidebar: function(event) {
		var $target = $(event.target).parent();
		
		if ($target.hasClass('activeMenuItem')) {
			return;
		}
		
		this._renderSidebar($target.data('menuItem'), []);
		
		// force sidebar to be displayed
		this._sidebarNavigation.wcfSidebar('show');
	},
	
	/**
	 * Renders sidebar including highlighting of currently active menu items.
	 *
	 * @param	string		menuItem
	 * @param	array		activeMenuItems
	 */
	_renderSidebar: function(menuItem, activeMenuItems) {
		// reset visible and active items
		this._headerNavigation.find('li').removeClass('activeMenuItem');
		this._sidebarNavigation.find('div.wcf-menuContainer').hide();
		
		if (activeMenuItems.length === 0) {
			// show active menu
			this._headerNavigation.find('li[data-menu-item="' + menuItem + '"]').addClass('activeMenuItem');
			this._sidebarNavigation.find('div[data-parent-menu-item="' + menuItem + '"]').show();
		}
		else {
			// open menu by active menu items, first element is always a head navigation item
			menuItem = activeMenuItems.shift();
			
			this._headerNavigation.find('li[data-menu-item="' + menuItem + '"]').addClass('activeMenuItem');
			this._sidebarNavigation.find('div[data-parent-menu-item="' + menuItem + '"]').show();
			
			for (var $i = 0, $size = activeMenuItems.length; $i < $size; $i++) {
				var $item = activeMenuItems[$i];
				
				if ($.wcfIsset($item)) {
					var $menuItem = $('#' + $.wcfEscapeID($item));
					
					if ($menuItem.getTagName() === 'ul') {
						$menuItem.parent('div').show().prev().addClass('activeMenuItem');
					}
					else {
						$menuItem.addClass('activeMenuItem');
					}
				}
			}
		}
	}
};

/**
 * Namespace for ACP package management.
 */
WCF.ACP.Package = {};

/**
 * Paginated package list.
 * 
 * @param	integer		pages
 */
WCF.ACP.Package.List = function(pages) { this.init(pages); };
WCF.ACP.Package.List.prototype = {
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
};

/**
 * Handles package installation dialog.
 * 
 * @param	string		actionName
 * @param	integer		queueID
 * @param	boolean		initialize
 */
WCF.ACP.Package.Installation = function(actionName, queueID, initialize) { this.init(actionName, queueID, initialize); };
WCF.ACP.Package.Installation.prototype = {
	/**
	 * package installation type
	 * 
	 * @var	string
	 */
	_actionName: '',

	/**
	 * dialog api
	 * @var	$.ui.wcfDialog
	 */
	_api: null,

	/**
	 * package installation dialog
	 *
	 * @var	object
	 */
	_dialog: null,
	
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
	 * render dialog
	 * @var	boolean
	 */
	_shouldRender: false,
	
	/**
	 * Initializes package installation.
	 * 
	 * @param	string		actionName
	 * @param	integer		queueID
	 * @param	boolean		initialize
	 */
	init: function(actionName, queueID, initialize) {
		this._actionName = WCF.String.ucfirst(actionName) + 'Package';
		this._queueID = queueID;
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._handleResponse, this),
			url: 'index.php/' + this._actionName + '/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});

		if (initialize) {
			$('#submitButton').click($.proxy(function(event) {
				this.prepareInstallation();
				return false;
			}, this));
		}
	},
	
	/**
	 * Prepares installation dialog.
	 */
	prepareInstallation: function() {
		var $dialog = WCF.showAJAXDialog('packageInstallationDialog', true, {
			ajax: true,
			closable: false,
			data: { queueID: this._queueID, step: 'prepare' },
			showLoadingOverlay: false,
			success: $.proxy(this._handleResponse, this),
			title: WCF.Language.get('wcf.acp.package.installation.title'),
			url: 'index.php/' + this._actionName + '/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		this._api = $dialog.data('wcfDialog');
	},
	
	/**
	 * Executes response instructions.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_handleResponse: function(data, textStatus, jqXHR) {
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
		if (data.step == 'success') {
			this._purgeTemplateContent($.proxy(function() {
				var $id = WCF.getRandomID();
				$('#packageInstallationInnerContent').append('<div class="wcf-formSubmit"><input type="button" id="' + $id + '" value="' + WCF.Language.get('wcf.global.button.next') + '" class="default" /></div>');
				
				$('#' + $id).click(function() {
					window.location.href = data.redirectLocation;
				});
				
				$('#packageInstallationInnerContentContainer').show();
				this._api.render();
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
			$('#packageInstallationInnerContent').html(data.innerTemplate);
			
			// create button to handle next step
			if (data.step && data.node) {
				var $id = WCF.getRandomID();
				$('#packageInstallationInnerContent').append('<div class="wcf-formSubmit"><input type="button" id="' + $id + '" value="' + WCF.Language.get('wcf.global.button.next') + '" class="default" /></div>');
				
				$('#' + $id).click($.proxy(function() {
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
				}, this));
			}
			
			$('#packageInstallationInnerContentContainer').show();
			
			this._api.render();
			return;
		}
		
		// purge content
		this._purgeTemplateContent($.proxy(function() {
			// render container
			if (this._shouldRender) {
				this._api.render();
			}
			
			// execute next step
			if (data.step && data.node) {
				this._executeStep(data.step, data.node);
			}
		}, this));
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
};

/**
 * Handles package uninstallation.
 * 
 * @param	jQuery		elements
 */
WCF.ACP.Package.Uninstallation = function(elements) { this.init(elements); };
WCF.ACP.Package.Uninstallation.prototype = {
	/**
	 * WCF.ACP.Package.Installation object
	 * 
	 * @var	WCF.ACP.Package.Installation
	 */
	_installation: null,
	
	/**
	 * Initializes package uninstallation.
	 * 
	 * @param	jQuery		elements
	 */
	init: function(elements) {
		if (elements.length == 0) return;
		
		// bind event listener
		elements.each($.proxy(function(index, element) {
			$(element).click($.proxy(this._createQueue, this));
		}, this));
	},
	
	/**
	 * Creates a new package uninstallation process.
	 * 
	 * @param	object		event
	 */
	_createQueue: function(event) {
		var $element = $(event.target);
		var packageID = $element.data('objectID');
		
		if (confirm(WCF.Language.get('wcf.acp.package.view.button.uninstall.sure'))) {
			this._installation = new WCF.ACP.Package.Installation('uninstall', 0, false);
			
			// initialize dialog
			WCF.showAJAXDialog('packageInstallationDialog', true, {
				ajax: true,
				closable: false,
				data: { packageID: packageID, step: 'prepare' },
				success: $.proxy(this._installation._handleResponse, this._installation),
				title: 'wcf.acp.package.uninstall.title',
				url: 'index.php/UninstallPackage/?t=' + SECURITY_TOKEN + SID_ARG_2ND
			});
		}
	}
};

/**
 * Handles option selection.
 */
WCF.ACP.Options = function() { this.init(); };
WCF.ACP.Options.prototype = {
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
					if (isActive) {
						$('#' + $.wcfEscapeID($target)).disable().closest('dl').addClass('disabled');
					}
					else {
						$('#' + $.wcfEscapeID($target)).enable().closest('dl').removeClass('disabled');
					}
				}
			}
		}
		
		if (enableOptions.length > 0) {
			for (var $i = 0, $size = enableOptions.length; $i < $size; $i++) {
				var $target = enableOptions[$i];
				if ($.wcfIsset($target)) {
					if (isActive) {
						$('#' + $.wcfEscapeID($target)).enable().closest('dl').removeClass('disabled');
					}
					else {
						$('#' + $.wcfEscapeID($target)).disable().closest('dl').addClass('disabled');
					}
				}
			}
		}
	}


};

/**
 * Namespace for WCF.ACP.User
 */
WCF.ACP.User = {};

/**
 * UserList clipboard API
 */
WCF.ACP.User.List = function() { this.init(); };
WCF.ACP.User.List.prototype = {
	/**
	 * Initializes the UserList clipboard API.
	 */
	init: function() {
		$('body').bind('clipboardAction', $.proxy(this.handleClipboardEvent, this));
	},
	
	/**
	 * Event handler for clipboard editor item actions.
	 */
	handleClipboardEvent: function(event, type, actionName) {
		// ignore unrelated events
		if ((type != 'com.woltlab.wcf.user') || (actionName != 'user.delete')) return;
		
		var $item = $(event.target);
		this._delete($item);
	},
	
	/**
	 * Handle delete action.
	 * 
	 * @param	jQuery		item
	 */
	_delete: function(item) {
		var $confirmMessage = item.data('internalData')['confirmMessage'];
		WCF.System.Confirmation.show($confirmMessage, function() {
			WCF.Clipboard.sendRequest(item);
		});
	}
};

/**
 * Worker support for ACP.
 * 
 * @param	string		dialogID
 * @param	string		className
 * @param	object		options
 */
WCF.ACP.Worker = function(dialogID, className, options) { this.init(dialogID, className, options); };
WCF.ACP.Worker.prototype = {
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
	 * Initializes a new worker instance.
	 * 
	 * @param	string		dialogID
	 * @param	string		className
	 * @param	object		options
	 */
	init: function(dialogID, className, options) {
		this._dialogID = dialogID + 'Worker';
		options = options || { };
		
		// initialize AJAX-based dialog
		WCF.showAJAXDialog(this._dialogID, true, {
			ajax: {
				url: 'index.php/WorkerProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND,
				type: 'POST',
				data: {
					className: className,
					parameters: options
				},
				success: $.proxy(this._handleResponse, this)
			},
			preventClose: true,
			hideTitle: true
		});
	},
	
	/**
	 * Handles response from server.
	 */
	_handleResponse: function() {
		// init binding
		if (this._dialog === null) {
			this._dialog = $('#' + $.wcfEscapeID(this._dialogID));
		}
		
		// fetch data returned by server response
		var $data = this._dialog.data('responseData');
		
		// update progress
		this._dialog.find('#workerProgress').attr('value', $data.progress).text($data.progress + '%');
		
		// worker is still busy with it's business, carry on
		if ($data.progress < 100) {
			// send request for next loop
			$.ajax({
				url: 'index.php/WorkerProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND,
				type: 'POST',
				data: {
					className: $data.className,
					loopCount: $data.loopCount,
					parameters: $data.parameters
				},
				success: $.proxy(function(data) {
					this._dialog.data('responseData', data);
					this._handleResponse();
				}, this),
				error: function(transport) {
					alert(transport.responseText);
				}
			});
		}
		else {
			// display proceed button
			var $proceedButton = $('<input type="submit" value="Proceed" />').appendTo('#workerInnerContent');
			$proceedButton.click(function() {
				window.location = $data.proceedURL;
			});
			
			$('#workerInnerContentContainer').wcfBlindIn();
			
			this._dialog.wcfDialog('render');
		}
	}
};
