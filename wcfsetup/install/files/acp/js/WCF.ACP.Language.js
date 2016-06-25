/**
 * ACP Language related classes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ACP.Language = { };

/**
 * Handles language item list management.
 * 
 * @param	integer		count
 * @param	integer		pageNo
 */
WCF.ACP.Language.ItemList = Class.extend({
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
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
	 * Initializes the WCF.ACP.Style.List class.
	 */
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('.jsLanguageItem').each($.proxy(function(index, button) {
			var $button = $(button);
			var $languageItemID = $button.data('languageItemID');
			
			var self = this;
			$button.click(function() { self._click($languageItemID); });
		}, this));
	},
	
	/**
	 * Executes actions.
	 * 
	 * @param	integer		languageItemID
	 */
	_click: function(languageItemID) {
		this._proxy.setOption('data', {
			actionName: 'prepareEdit',
			className: 'wcf\\data\\language\\item\\LanguageItemAction',
			objectIDs: [ languageItemID ]
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
		if (data.returnValues) {
			// display template
			this._showDialog(data.returnValues.template, data.returnValues.languageItem);
			
			// bind event listener
			this._dialog.find('.jsSubmitLanguageItem').click($.proxy(this._submit, this));
		}
		else {
			if (this._notification === null) {
				this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
			}
			
			// show success and close dialog
			this._dialog.wcfDialog('close');
			this._notification.show();
		}
	},
	
	/**
	 * Displays the dialog overlay.
	 * 
	 * @param	string		template
	 * @param	string		itemName
	 */
	_showDialog: function(template, itemName) {
		if (this._dialog === null) {
			this._dialog = $('#languageItemEdit');
			if (!this._dialog.length) {
				this._dialog = $('<div id="languageItemEdit" />').hide().appendTo(document.body);
			}
		}
		
		this._dialog.html(template).wcfDialog({
			title: itemName
		}).wcfDialog('render');
	},
	
	/**
	 * Submits the form.
	 */
	_submit: function() {
		var $languageItemValue = $('#overlayLanguageItemValue').val();
		var $languageCustomItemValue = $('#overlayLanguageCustomItemValue').val();
		var $languageUseCustomValue = ($('#overlayLanguageUseCustomValue').is(':checked') ? 1 : 0);
		var $languageItemID = $('#overlayLanguageItemID').val();
		
		this._proxy.setOption('data', {
			actionName: 'edit',
			className: 'wcf\\data\\language\\item\\LanguageItemAction',
			objectIDs: [ $languageItemID ],
			parameters: {
				languageItemValue: $languageItemValue,
				languageCustomItemValue: $languageCustomItemValue,
				languageUseCustomValue: $languageUseCustomValue
			}
		});
		this._proxy.sendRequest();
	}
});
