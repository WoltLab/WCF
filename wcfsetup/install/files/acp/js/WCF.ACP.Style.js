/**
 * ACP Style related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
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
 * Handles the preview image upload.
 * 
 * @param	integer		styleID
 * @param	string		tmpHash
 * @deprecated	use WoltLab/WCF/Acp/Ui/Style/Image/Upload
 */
WCF.ACP.Style.ImageUpload = WCF.Upload.extend({
	/**
	 * upload button
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * preview image
	 * @var	jQuery
	 */
	_image: null,
	
	/**
	 * style id
	 * @var	integer
	 */
	_styleID: 0,
	
	/**
	 * tmp hash
	 * @var	string
	 */
	_tmpHash: '',
	
	/**
	 * @see	WCF.Upload.init()
	 */
	init: function(styleID, tmpHash) {
		this._styleID = parseInt(styleID) || 0;
		this._tmpHash = tmpHash;
		
		this._button = $('#uploadImage');
		this._image = $('#styleImage');
		
		this._super(this._button, undefined, 'wcf\\data\\style\\StyleAction');
	},
	
	/**
	 * @see	WCF.Upload._initFile()
	 */
	_initFile: function(file) {
		return this._image;
	},
	
	/**
	 * @see	WCF.Upload._getParameters()
	 */
	_getParameters: function() {
		return {
			styleID: this._styleID,
			tmpHash: this._tmpHash
		};
	},
	
	/**
	 * @see	WCF.Upload._success()
	 */
	_success: function(uploadID, data) {
		if (data.returnValues.url) {
			// show image
			this._image.attr('src', data.returnValues.url + '?timestamp=' + Date.now());
			
			// hide error
			this._button.next('.innerError').remove();
			
			// show success message
			var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
			$notification.show();
		}
		else if (data.returnValues.errorType) {
			// show error
			this._getInnerErrorElement().text(WCF.Language.get('wcf.acp.style.image.error.' + data.returnValues.errorType));
		}
	},
	
	/**
	 * Returns error display element.
	 * 
	 * @return	jQuery
	 */
	_getInnerErrorElement: function() {
		var $span = this._button.next('.innerError');
		if (!$span.length) {
			$span = $('<small class="innerError" />').insertAfter(this._button);
		}
		
		return $span;
	}
});

/**
 * Handles the logo upload.
 * 
 * @param	string		tmpHash
 */
WCF.ACP.Style.LogoUpload = WCF.Upload.extend({
	/**
	 * upload button
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * image path
	 * @var	jQuery
	 */
	_imagePath: null,
	
	/**
	 * logo
	 * @var	jQuery
	 */
	_logo: null,
	
	/**
	 * page logo input field
	 * @var	jQuery
	 */
	_pageLogo: null,
	
	/**
	 * tmp hash
	 * @var	string
	 */
	_tmpHash: '',
	
	/**
	 * absolute path to WCF directory
	 * @var	string
	 */
	_wcfPath: '',
	
	/**
	 * @see	WCF.Upload.init()
	 */
	init: function(tmpHash, wcfPath) {
		this._tmpHash = tmpHash;
		this._wcfPath = wcfPath;
		
		this._button = $('#uploadLogo');
		this._image = $('#styleLogo');
		this._imagePath = $('#imagePath');
		this._pageLogo = $('#pageLogo');
		
		this._super(this._button, undefined, 'wcf\\data\\style\\StyleAction', { action: 'uploadLogo' });
		
		if (!this._image.attr('src').length) {
			this._updateLogo();
		}
		
		this._pageLogo.blur($.proxy(this._updateLogo, this));
	},
	
	/**
	 * Updates the logo preview.
	 */
	_updateLogo: function() {
		var $src = this._pageLogo.val();
		if ($src.length) {
			if (!$src.match(/^https?:\/\//)) {
				var $path = this._imagePath.val();
				if (!$path) {
					$path = 'images/';
				}
				
				$path = this._wcfPath + $path.replace(/^\/?images\/?/, '');
				if ($path.substr(-1) !== '/') {
					$path += '/';
				}
				
				$src = $path + $src;
			}
		}
		else {
			// no logo defined, fallback to application logo
			$src = $('#logo > a > img').prop('src');
		}
		
		this._image.attr('src', $src + '?timestamp=' + Date.now());
	},
	
	/**
	 * @see	WCF.Upload._initFile()
	 */
	_initFile: function(file) {
		return this._image;
	},
	
	/**
	 * @see	WCF.Upload._getParameters()
	 */
	_getParameters: function() {
		return {
			tmpHash: this._tmpHash
		};
	},
	
	/**
	 * @see	WCF.Upload._success()
	 */
	_success: function(uploadID, data) {
		if (data.returnValues.url) {
			// show image
			this._image.attr('src', data.returnValues.url + '?timestamp=' + Date.now());
			this._pageLogo.val(data.returnValues.url);
			
			// hide error
			this._button.next('.innerError').remove();
			
			// show success message
			var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
			$notification.show();
		}
		else if (data.returnValues.errorType) {
			// show error
			this._getInnerErrorElement().text(WCF.Language.get('wcf.acp.style.image.error.' + data.returnValues.errorType));
		}
	},
	
	/**
	 * Returns error display element.
	 * 
	 * @return	jQuery
	 */
	_getInnerErrorElement: function() {
		var $span = this._button.next('.innerError');
		if (!$span.length) {
			$span = $('<small class="innerError" />').insertAfter(this._button);
		}
		
		return $span;
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
			$list.find('.jsSetAsDefault').click(function() { self._click('setAsDefault', $styleID); });
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
		var $confirmMessage = $(event.currentTarget).data('confirmMessage');
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
