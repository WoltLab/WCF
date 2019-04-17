/**
 * Attempts to download the requested package from the file and prompts for the
 * authentication credentials on rejection.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Package/PrepareInstallation
 */
define(['Ajax', 'Core', 'Language', 'Ui/Dialog'], function(Ajax, Core, Language, UiDialog) {
	'use strict';
	
	function AcpUiPackagePrepareInstallation() { }
	AcpUiPackagePrepareInstallation.prototype = {
		/**
		 * @param {string} identifier
		 * @param {string} version
		 */
		start: function (identifier, version) {
			this._identifier = identifier;
			this._version = version;
			
			this._prepare({});
		},
		
		/**
		 * @param {Object} authData
		 */
		_prepare: function(authData) {
			var packages = {};
			packages[this._identifier] = this._version;
			
			Ajax.api(this, {
				parameters: {
					authData: authData,
					packages: packages
				}
			});
		},
		
		/**
		 * @param {number} packageUpdateServerId
		 * @param {Event} event
		 */
		_submit: function(packageUpdateServerId, event) {
			event.preventDefault();
			
			var usernameInput = elById('packageUpdateServerUsername');
			var passwordInput = elById('packageUpdateServerPassword');
			
			elInnerError(usernameInput, false);
			elInnerError(passwordInput, false);
			
			var username = usernameInput.value.trim();
			if (username === '') {
				elInnerError(usernameInput, Language.get('wcf.global.form.error.empty'));
			}
			else {
				var password = passwordInput.value.trim();
				if (password === '') {
					elInnerError(passwordInput, Language.get('wcf.global.form.error.empty'));
				}
				else {
					this._prepare({
						packageUpdateServerID: packageUpdateServerId,
						password: password,
						saveCredentials: elById('packageUpdateServerSaveCredentials').checked,
						username: username
					});
				}
			}
		},
		
		_ajaxSuccess: function(data) {
			if (data.returnValues.queueID) {
				UiDialog.close(this);
				
				var installation = new window.WCF.ACP.Package.Installation(data.returnValues.queueID, undefined, false);
				installation.prepareInstallation();
			}
			else if (data.returnValues.template) {
				UiDialog.open(this, data.returnValues.template);
			}
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'prepareInstallation',
					className: 'wcf\\data\\package\\update\\PackageUpdateAction'
				}
			}
		},
		
		_dialogSetup: function() {
			return {
				id: 'packageDownloadAuthorization',
				options: {
					onSetup: (function(content) {
						var button = elBySel('.formSubmit > button', content);
						button.addEventListener(WCF_CLICK_EVENT, this._submit.bind(this, elData(button, 'package-update-server-id')));
					}).bind(this),
					title: Language.get('wcf.acp.package.update.unauthorized')
				},
				source: null
			}
		}
	};
	
	return AcpUiPackagePrepareInstallation;
});
