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
			className: 'wcf\\data\\user\\ExtendedUserAction',
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
			className: 'wcf\\data\\user\\ExtendedUserAction',
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
					$button.data('enabled', false).data('tooltip', $button.data('enableMessage')).removeClass('icon-circle-blank').addClass('icon-off');
				}
				else {
					$button.data('enabled', true).data('tooltip', $button.data('disableMessage')).removeClass('icon-off').addClass('icon-circle-blank');
				}
			}
		});
		
		var $notification = new WCF.System.Notification();
		$notification.show();
		
		WCF.Clipboard.reload();
	}
};
