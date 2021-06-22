/**
 * ACP Style related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ACP.Style = { };

/**
 * Handles style duplicating.
 * 
 * @param	integer		styleID
 */
WCF.ACP.Style.CopyStyle = Class.extend({
	/**
	 * style id
	 * @var	integer
	 */
	_styleID: 0,
	
	/**
	 * Initializes the WCF.ACP.Style.CopyStyle class.
	 * 
	 * @param	integer		styleID
	 */
	init: function(styleID) {
		this._styleID = styleID;
		
		var self = this;
		$('.jsCopyStyle').click(function() {
			WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.style.copyStyle.confirmMessage'), $.proxy(self._copy, self), undefined, undefined, true);
		});
	},
	
	/**
	 * Invokes the style duplicating process.
	 * 
	 * @param	string		action
	 */
	_copy: function(action) {
		if (action === 'confirm') {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'copy',
					className: 'wcf\\data\\style\\StyleAction',
					objectIDs: [ this._styleID ]
				},
				success: $.proxy(this._success, this)
			});
		}
	},
	
	/**
	 * Redirects to newly created style.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		window.location = data.returnValues.redirectURL;
	}
});

/**
 * Handles style list management buttons.
 */
WCF.ACP.Style.List = Class.extend({
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the WCF.ACP.Style.List class.
	 */
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('.styleList .buttonList').each($.proxy(function(index, list) {
			var $list = $(list);
			var $styleID = $list.data('styleID');
			
			var self = this;
			$list.find('.jsSetAsDefault').click(function(event) {
				event.preventDefault();
				self._click('setAsDefault', $styleID);
			});
			$list.find('.jsDelete').click(function(event) { self._delete(event, $styleID); });
		}, this));
	},
	
	/**
	 * Executes actions.
	 * 
	 * @param	string		actionName
	 * @param	integer		styleID
	 */
	_click: function(actionName, styleID) {
		this._proxy.setOption('data', {
			actionName: actionName,
			className: 'wcf\\data\\style\\StyleAction',
			objectIDs: [ styleID ]
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Prepares to delete a style.
	 * 
	 * @param	object		event
	 * @param	integer		styleID
	 */
	_delete: function(event, styleID) {
		event.preventDefault();
		
		var $confirmMessage = $(event.currentTarget).data('confirmMessageHtml');
		if ($confirmMessage) {
			var self = this;
			WCF.System.Confirmation.show($confirmMessage, function(action) {
				if (action === 'confirm') {
					self._click('delete', styleID);
				}
			}, undefined, undefined, true);
		}
		else {
			// invoke action directly
			this._click('delete', styleID);
		}
	},
	
	/**
	 * Reloads the page after an action was executed successfully.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function (data, textStatus, jqXHR) {
		// reload page
		window.location.reload();
	}
});
