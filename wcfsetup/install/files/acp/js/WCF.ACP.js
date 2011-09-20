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
		this._sidebarNavigation = $('nav#sidebarMenu');
		
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
		if ($(event.target).hasClass('activeMenuItem')) {
			return;
		}
		
		this._renderSidebar($(event.target).data('menuItem'), []);
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
		this._sidebarNavigation.find('div.menuContainer').hide();
		
		if (activeMenuItems.length === 0) {
			// show active menu
			this._headerNavigation.find('li[data-menuItem="' + menuItem + '"]').addClass('activeMenuItem');
			this._sidebarNavigation.find('div[data-parentMenuItem="' + menuItem + '"]').show();
		}
		else {
			// open menu by active menu items, first element is always a head navigation item
			menuItem = activeMenuItems.shift();
			
			this._headerNavigation.find('li[data-menuItem="' + menuItem + '"]').addClass('activeMenuItem');
			this._sidebarNavigation.find('div[data-parentMenuItem="' + menuItem + '"]').show();
			
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
 * Handles package installation dialog.
 * 
 * @param	string		actionName
 * @param	integer		queueID
 * @param	boolean		initialize
 */
WCF.ACP.PackageInstallation = function(actionName, queueID, initialize) { this.init(actionName, queueID, initialize); };
WCF.ACP.PackageInstallation.prototype = {
	/**
	 * package installation type
	 * 
	 * @var	string
	 */
	_actionName: '',
	
	/**
	 * package installation dialog
	 *
	 * @var	object
	 */
	_dialog: null,
	
	/**
	 * queue id
	 *
	 * @var	integer
	 */
	_queueID: 0,
	
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
		WCF.showAJAXDialog('packageInstallationDialog', true, {
			ajax: {
				url: 'index.php?action=' + this._actionName + '&t=' + SECURITY_TOKEN + SID_ARG_2ND,
				type: 'POST',
				data: { queueID: this._queueID, step: 'prepare' },
				success: $.proxy(this._handleResponse, this)
			},
			preventClose: true,
			hideTitle: true
		});
	},
	
	/**
	 * Executes response instructions.
	 */
	_handleResponse: function() {
		if (this._dialog == null) {
			this._dialog = $('#packageInstallationDialog');
		}
		
		var $data = this._dialog.data('responseData');
		
		// receive new queue id
		if ($data.queueID) {
			this._queueID = $data.queueID;
		}
		
		// update progress
		if ($data.progress) {
			$('#packageInstallationProgress').attr('value', $data.progress).text($data.progress + '%');
		}
		
		// handle success
		if ($data.step == 'success') {
			var $id = WCF.getRandomID();
			$('#packageInstallationInnerContent').append('<div class="formSubmit"><input type="button" id="' + $id + '" value="' + WCF.Language.get('wcf.global.button.next') + '" class="default" /></div>');
			
			$('#' + $id).click($.proxy(function() {
				window.location.href = "index.php?page=PackageList" + SID_ARG_2ND;
			}, this));
			
			$('#packageInstallationInnerContentContainer').wcfBlindIn();
			
			return;
		}
		
		// update template
		if ($data.template && !$data.ignoreTemplate) {
			this._dialog.html($data.template);
		}
		
		// update action
		if ($data.currentAction) {
			$('#packageInstallationAction').text($data.currentAction);
		}
		
		// handle inner template
		if ($data.innerTemplate) {
			$('#packageInstallationInnerContent').html($data.innerTemplate);
			
			// create button to handle next step
			if ($data.step && $data.node) {
				var $id = WCF.getRandomID();
				$('#packageInstallationInnerContent').append('<div class="formSubmit"><input type="button" id="' + $id + '" value="' + WCF.Language.get('wcf.global.button.next') + '" class="default" /></div>');
				
				$('#' + $id).click($.proxy(function() {
					// collect form values
					var $additionalData = {};
					$('#packageInstallationInnerContent').find('input').each(function(index, inputElement) {
						$additionalData[$(inputElement).attr('name')] = $(inputElement).attr('value');
					});
					
					this._executeStep($data.step, $data.node, $additionalData);
				}, this));
			}
			
			$('#packageInstallationInnerContentContainer').wcfBlindIn();
			
			this._dialog.wcfDialog('redraw');
			return;
		}
		
		// purge content
		if ($('#packageInstallationInnerContent').children().length > 1) {
			$('#packageInstallationInnerContentContainer').wcfBlindOut('down', $.proxy(function() {
				$('#packageInstallationInnerContent').empty();
				this._dialog.wcfDialog('redraw');
				
				// execute next step
				if ($data.step && $data.node) {
					this._executeStep($data.step, $data.node);
				}
			}, this));
		}
		else {
			// execute next step
			if ($data.step && $data.node) {
				this._executeStep($data.step, $data.node);
			}
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
		
		$.ajax({
			url: 'index.php?action=' + this._actionName + '&t=' + SECURITY_TOKEN + SID_ARG_2ND,
			dataType: 'json',
			type: 'POST',
			data: $data,
			success: $.proxy(function(data) {
				this._dialog.data('responseData', data);
				this._handleResponse();
			}, this),
			error: function(transport) {
				alert(transport.responseText);
			}
		});
	}
};

/**
 * Handles package uninstallation.
 * 
 * @param	jQuery		elements
 */
WCF.ACP.PackageUninstallation = function(elements) { this.init(elements); };
WCF.ACP.PackageUninstallation.prototype = {
	/**
	 * WCF.ACP.PackageInstallation object
	 * 
	 * @var	WCF.ACP.PackageInstallation
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
			this._installation = new WCF.ACP.PackageInstallation('uninstall', 0, false);
			
			// initialize dialog
			WCF.showAJAXDialog('packageInstallationDialog', true, {
				ajax: {
					url: 'index.php?action=UninstallPackage&t=' + SECURITY_TOKEN + SID_ARG_2ND,
					type: 'POST',
					data: { packageID: packageID, step: 'prepare' },
					success: $.proxy(this._installation._handleResponse, this._installation)
				},
				preventClose: true,
				hideTitle: true
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
		$('.enablesOptions').each($.proxy(this._initOption, this));
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
		if (confirm($confirmMessage)) {
			WCF.Clipboard.sendRequest(item);
		}
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
				url: 'index.php?action=WorkerProxy&t=' + SECURITY_TOKEN + SID_ARG_2ND,
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
				url: 'index.php?action=WorkerProxy&t=' + SECURITY_TOKEN + SID_ARG_2ND,
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
			
			this._dialog.wcfDialog('redraw');
		}
	}
};
