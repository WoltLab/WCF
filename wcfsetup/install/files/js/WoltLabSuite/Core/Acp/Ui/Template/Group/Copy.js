/**
 * Provides a dialog to copy an existing template group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Template/Group/Copy
 */
define(['Ajax', 'EventKey', 'Language', 'Ui/Dialog', 'Ui/Notification'], function(Ajax, EventKey, Language, UiDialog, UiNotification) {
	"use strict";
	
	var _name = null;
	var _folderName = null;
	var _templateGroupId = 0;
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/Template/Group/Copy
	 */
	return {
		/**
		 * Initializes the dialog handler.
		 * 
		 * @param       {int}           templateGroupId
		 */
		init: function (templateGroupId) {
			_templateGroupId = templateGroupId;
			
			elBySel('.jsButtonCopy').addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
		},
		
		/**
		 * Handles clicks on the 'Copy Template Group' button.
		 * 
		 * @param       {Event}         event
		 * @protected
		 */
		_click: function (event) {
			event.preventDefault();
			
			UiDialog.open(this);
		},
		
		/**
		 * Submits the values for the new template group.
		 * 
		 * @param       {Event}         event
		 * @protected
		 */
		_submit: function (event) {
			event.preventDefault();
			
			var valid = true;
			[_name, _folderName].forEach(function (input) {
				if (input.value.trim() === '') {
					elInnerError(input, Language.get('wcf.global.form.error.empty'));
					valid = false;
				}
				else {
					elInnerError(input, false);
				}
			});
			
			if (valid) {
				Ajax.api(this, {
					parameters: {
						templateGroupName: _name.value,
						templateGroupFolderName: _folderName.value
					}
				});
			}
		},
		
		_ajaxSuccess: function (data) {
			UiDialog.close(this);
			
			UiNotification.show(undefined, function () {
				//noinspection JSUnresolvedVariable
				window.location = data.returnValues.redirectURL;
			});
		},
		
		_dialogSetup: function () {
			return {
				id: 'templateGroupCopy',
				options: {
					onSetup: (function (content) {
						['Name', 'FolderName'].forEach((function(type) {
							var input = elById('copyTemplateGroup' + type);
							input.value = elById('templateGroup' + type).value;
							
							if (type === 'Name') _name = input;
							else _folderName = input;
							
							input.addEventListener('keydown', (function (event) {
								if (EventKey.Enter(event)) {
									this._submit(event);
								}
							}).bind(this));
						}).bind(this));
						
						elBySel('.formSubmit > button[data-type="submit"]', content).addEventListener(WCF_CLICK_EVENT, this._submit.bind(this));
					}).bind(this),
					title: Language.get('wcf.acp.template.group.copy')
				},
				source: '<dl>' +
					'<dt><label for="copyTemplateGroupName">' + Language.get('wcf.global.name') + '</label></dt>' +
					'<dd><input type="text" id="copyTemplateGroupName" class="long"></dd>' +
				'</dl>' +
				'<dl>' +
					'<dt><label for="copyTemplateGroupFolderName">' + Language.get('wcf.acp.template.group.folderName') + '</label></dt>' +
					'<dd><input type="text" id="copyTemplateGroupFolderName" class="long"></dd>' +
				'</dl>' +
				'<div class="formSubmit">' +
					'<button class="buttonPrimary" data-type="submit">' + Language.get('wcf.global.button.submit') + '</button>' +
				'</div>'
			}
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'copy',
					className: 'wcf\\data\\template\\group\\TemplateGroupAction',
					objectIDs: [_templateGroupId]
				},
				/** @var {{returnValues:{fieldName: string, errorType: string}}} data */
				failure: function (data) {
					if (data && data.returnValues && data.returnValues.fieldName && data.returnValues.errorType) {
						if (data.returnValues.fieldName === 'templateGroupName') {
							elInnerError(_name, Language.get('wcf.acp.template.group.name.error.' + data.returnValues.errorType));
						}
						else {
							elInnerError(_folderName, Language.get('wcf.acp.template.group.folderName.error.' + data.returnValues.errorType));
						}
						
						return false;
					}
				}
			}
		}
	};
});
