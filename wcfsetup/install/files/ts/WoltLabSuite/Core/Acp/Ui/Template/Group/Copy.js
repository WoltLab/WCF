/**
 * Provides a dialog to copy an existing template group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Template/Group/Copy
 */
define(['Ajax', 'Language', 'Ui/Dialog', 'Ui/Notification'], function(Ajax, Language, UiDialog, UiNotification) {
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
			
			elBySel('.jsButtonCopy').addEventListener('click', this._click.bind(this));
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
		
		_dialogSubmit: function () {
			Ajax.api(this, {
				parameters: {
					templateGroupName: _name.value,
					templateGroupFolderName: _folderName.value
				}
			});
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
					onSetup: (function () {
						['Name', 'FolderName'].forEach((function(type) {
							var input = elById('copyTemplateGroup' + type);
							input.value = elById('templateGroup' + type).value;
							
							if (type === 'Name') _name = input;
							else _folderName = input;
						}).bind(this));
					}).bind(this),
					title: Language.get('wcf.acp.template.group.copy')
				},
				source: '<dl>' +
					'<dt><label for="copyTemplateGroupName">' + Language.get('wcf.global.name') + '</label></dt>' +
					'<dd><input type="text" id="copyTemplateGroupName" class="long" data-dialog-submit-on-enter="true" required></dd>' +
				'</dl>' +
				'<dl>' +
					'<dt><label for="copyTemplateGroupFolderName">' + Language.get('wcf.acp.template.group.folderName') + '</label></dt>' +
					'<dd><input type="text" id="copyTemplateGroupFolderName" class="long" data-dialog-submit-on-enter="true" required></dd>' +
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
