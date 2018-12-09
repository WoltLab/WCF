/**
 * Simple notification overlay.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/Editor
 */
define(['Ajax', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog', 'Ui/Notification'], function(Ajax, Language, StringUtil, DomUtil, UiDialog, UiNotification) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_click: function() {},
			_submit: function() {},
			_ajaxSuccess: function() {},
			_ajaxSetup: function() {},
			_dialogSetup: function() {}
		};
		return Fake;
	}
	
	var _actionName = '';
	var _userHeader = null;
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/User/Editor
	 */
	return {
		/**
		 * Initializes the user editor.
		 */
		init: function() {
			_userHeader = elBySel('.userProfileUser');
			
			// init buttons
			['ban', 'disableAvatar', 'disableCoverPhoto', 'disableSignature', 'enable'].forEach((function(action) {
				var button = elBySel('.userProfileButtonMenu .jsButtonUser' + StringUtil.ucfirst(action));
				
				// button is missing if users lacks the permission
				if (button) {
					elData(button, 'action', action);
					button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
				}
			}).bind(this));
		},
		
		/**
		 * Handles clicks on action buttons.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_click: function(event) {
			event.preventDefault();
			
			//noinspection JSCheckFunctionSignatures
			var action = elData(event.currentTarget, 'action');
			var actionName = '';
			switch (action) {
				case 'ban':
					if (elDataBool(_userHeader, 'banned')) {
						actionName = 'unban';
					}
					break;
				
				case 'disableAvatar':
					if (elDataBool(_userHeader, 'disable-avatar')) {
						actionName = 'enableAvatar';
					}
					break;
					
				case 'disableCoverPhoto':
					if (elDataBool(_userHeader, 'disable-cover-photo')) {
						actionName = 'enableCoverPhoto';
					}
					break;
				
				case 'disableSignature':
					if (elDataBool(_userHeader, 'disable-signature')) {
						actionName = 'enableSignature';
					}
					break;
				
				case 'enable':
					actionName = (elDataBool(_userHeader, 'is-disabled')) ? 'enable' : 'disable';
					break;
			}
			
			if (actionName === '') {
				_actionName = action;
				
				UiDialog.open(this);
			}
			else {
				Ajax.api(this, {
					actionName: actionName
				});
			}
		},
		
		/**
		 * Handles form submit and input validation.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_submit: function(event) {
			event.preventDefault();
			
			var label = elById('wcfUiUserEditorExpiresLabel');
			
			var expires = '';
			var errorMessage = '';
			if (!elById('wcfUiUserEditorNeverExpires').checked) {
				expires = elById('wcfUiUserEditorExpiresDatePicker').value;
				if (expires === '') {
					errorMessage = Language.get('wcf.global.form.error.empty');
				}
			}
			
			elInnerError(label, errorMessage);
			
			var parameters = {};
			parameters[_actionName + 'Expires'] = expires;
			parameters[_actionName + 'Reason'] = elById('wcfUiUserEditorReason').value.trim();
			
			Ajax.api(this, {
				actionName: _actionName,
				parameters: parameters
			});
		},
		
		_ajaxSuccess: function(data) {
			switch (data.actionName) {
				case 'ban':
				case 'unban':
					elData(_userHeader, 'banned', (data.actionName === 'ban'));
					elBySel('.userProfileButtonMenu .jsButtonUserBan').textContent = Language.get('wcf.user.' + (data.actionName === 'ban' ? 'unban' : 'ban'));
					
					var contentTitle = elBySel('.contentTitle', _userHeader);
					var banIcon = elBySel('.jsUserBanned', contentTitle);
					if (data.actionName === 'ban') {
						banIcon = elCreate('span');
						banIcon.className = 'icon icon24 fa-lock jsUserBanned jsTooltip';
						banIcon.title = data.returnValues;
						contentTitle.appendChild(banIcon);
					}
					else if (banIcon) {
						elRemove(banIcon);
					}
					
					break;
				
				case 'disableAvatar':
				case 'enableAvatar':
					elData(_userHeader, 'disable-avatar', (data.actionName === 'disableAvatar'));
					elBySel('.userProfileButtonMenu .jsButtonUserDisableAvatar').textContent = Language.get('wcf.user.' + (data.actionName === 'disableAvatar' ? 'enable' : 'disable') + 'Avatar');
					
					break;
					
				case 'disableCoverPhoto':
				case 'enableCoverPhoto':
					elData(_userHeader, 'disable-cover-photo', (data.actionName === 'disableCoverPhoto'));
					elBySel('.userProfileButtonMenu .jsButtonUserDisableCoverPhoto').textContent = Language.get('wcf.user.' + (data.actionName === 'disableCoverPhoto' ? 'enable' : 'disable') + 'CoverPhoto');
					
					break;
					
				case 'disableSignature':
				case 'enableSignature':
					elData(_userHeader, 'disable-signature', (data.actionName === 'disableSignature'));
					elBySel('.userProfileButtonMenu .jsButtonUserDisableSignature').textContent = Language.get('wcf.user.' + (data.actionName === 'disableSignature' ? 'enable' : 'disable') + 'Signature');
					
					break;
				
				case 'enable':
				case 'disable':
					elData(_userHeader, 'is-disabled', (data.actionName === 'disable'));
					elBySel('.userProfileButtonMenu .jsButtonUserEnable').textContent = Language.get('wcf.acp.user.' + (data.actionName === 'enable' ? 'disable' : 'enable'));
					
					break;
			}
			
			if (data.actionName === 'ban' || data.actionName === 'disableAvatar' || data.actionName === 'disableCoverPhoto' || data.actionName === 'disableSignature') {
				UiDialog.close(this);
			}
			
			UiNotification.show();
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					className: 'wcf\\data\\user\\UserAction',
					objectIDs: [ elData(_userHeader, 'object-id') ]
				}
			};
		},
		
		_dialogSetup: function() {
			return {
				id: 'wcfUiUserEditor',
				options: {
					onSetup: (function (content) {
						elById('wcfUiUserEditorNeverExpires').addEventListener('change', function () {
							window[(this.checked) ? 'elHide' : 'elShow'](elById('wcfUiUserEditorExpiresSettings'));
						});
						
						elBySel('button.buttonPrimary', content).addEventListener(WCF_CLICK_EVENT, this._submit.bind(this));
					}).bind(this),
					onShow: function(content) {
						UiDialog.setTitle('wcfUiUserEditor', Language.get('wcf.user.' + _actionName + '.confirmMessage'));
						
						var label = elById('wcfUiUserEditorReason').nextElementSibling;
						var phrase = 'wcf.user.' + _actionName + '.reason.description';
						label.textContent = Language.get(phrase);
						window[(label.textContent === phrase) ? 'elHide' : 'elShow'](label);
						
						label = elById('wcfUiUserEditorNeverExpires').nextElementSibling;
						label.textContent = Language.get('wcf.user.' + _actionName + '.neverExpires');
						
						label = elBySel('label[for="wcfUiUserEditorExpires"]', content);
						label.textContent = Language.get('wcf.user.' + _actionName + '.expires');
						
						label = elById('wcfUiUserEditorExpiresLabel');
						label.textContent = Language.get('wcf.user.' + _actionName + '.expires.description');
					}
				},
				source: '<div class="section">'
						+ '<dl>'
							+ '<dt><label for="wcfUiUserEditorReason">' + Language.get('wcf.global.reason') + '</label></dt>'
							+ '<dd><textarea id="wcfUiUserEditorReason" cols="40" rows="3"></textarea><small></small></dd>'
						+ '</dl>'
						+ '<dl>'
							+ '<dt></dt>'
							+ '<dd><label><input type="checkbox" id="wcfUiUserEditorNeverExpires" checked> <span></span></label></dd>'
						+ '</dl>'
						+ '<dl id="wcfUiUserEditorExpiresSettings" style="display: none">'
							+ '<dt><label for="wcfUiUserEditorExpires"></label></dt>'
							+ '<dd>'
								+ '<input type="date" name="wcfUiUserEditorExpires" id="wcfUiUserEditorExpires" class="medium" min="' + new Date(TIME_NOW * 1000).toISOString() + '" data-ignore-timezone="true">'
								+ '<small id="wcfUiUserEditorExpiresLabel"></small>'
							+ '</dd>'
						+'</dl>'
					+ '</div>'
					+ '<div class="formSubmit"><button class="buttonPrimary">' + Language.get('wcf.global.button.submit') + '</button></div>'
			};
		}
	};
});
