/**
 * Handles quick setup of all projects within a path.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Devtools/Project/QuickSetup
 */
define([
	'Ajax',
	'Dom/Traverse',
	'Dom/Util',
	'EventKey',
	'Language',
	'Ui/Dialog',
	'Ui/Notification'
], function (
	Ajax,
	DomTraverse,
	DomUtil,
	EventKey,
	Language,
	UiDialog,
	UiNotification
) {
	"use strict";
	
	var _setupButtons = elByClass('jsDevtoolsProjectQuickSetupButton');
	var _submitButton = elById('projectQuickSetupSubmit');
	var _pathInput = elById('projectQuickSetupPath');
	
	return {
		/**
		 * Initializes the project quick setup handler.
		 */
		init: function() {
			// add event listeners to open dialog
			Array.prototype.forEach.call(_setupButtons, function(button) {
				button.addEventListener('click', this._showDialog.bind(this));
			}.bind(this));
			
			// add event listener to submit dialog
			_submitButton.addEventListener('click', this._submit.bind(this));
			
			// add event listener to input field to submit dialog by pressing enter
			_pathInput.addEventListener('keypress', this._keyPress.bind(this));
		},
		
		/**
		 * Returns the data used to setup the AJAX request object.
		 * 
		 * @return	{object}	setup data
		 */
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'quickSetup',
					className: 'wcf\\data\\devtools\\project\\DevtoolsProjectAction'
				}
			};
		},
		
		/**
		 * Handles successful AJAX request.
		 * 
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			if (data.returnValues.errorMessage) {
				elInnerError(_pathInput, data.returnValues.errorMessage);
				
				_submitButton.disabled = false;
				
				return;
			}
			
			UiDialog.close(this);
			
			UiNotification.show(data.returnValues.successMessage, function() {
				window.location.reload();
			});
		},
		
		/**
		 * Returns the data used to setup the dialog.
		 *
		 * @return	{object}	setup data
		 */
		_dialogSetup: function() {
			return {
				id: 'projectQuickSetup',
				options: {
					onShow: this._onDialogShow.bind(this),
					title: Language.get('wcf.acp.devtools.project.quickSetup')
				}
			};
		},
		
		/**
		 * Handles the `[ENTER]` key to submit the form.
		 *
		 * @param	{object}	event		event object
		 */
		_keyPress: function(event) {
			if (EventKey.Enter(event)) {
				this._submit();
			}
		},
		
		/**
		 * Is called every time the dialog is shown.
		 */
		_onDialogShow: function() {
			// reset path input
			_pathInput.value = '';
			_pathInput.focus();
			
			// hide error
			elInnerError(_pathInput, false);
		},
		
		/**
		 * Shows the dialog after clicking on the related button.
		 */
		_showDialog: function() {
			UiDialog.open(this);
		},
		
		/**
		 * Is called if the dialog form is submitted.
		 */
		_submit: function() {
			// check if path is empty
			if (_pathInput.value === '') {
				elInnerError(_pathInput, Language.get('wcf.global.form.error.empty'));
				
				return;
			}
			
			Ajax.api(this, {
				parameters: {
					path: _pathInput.value
				}
			});
			
			_submitButton.disabled = true;
		}
	};
});