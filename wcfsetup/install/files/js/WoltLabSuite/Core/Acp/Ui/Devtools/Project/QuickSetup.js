/**
 * Handles quick setup of all projects within a path.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
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
				this._showPathError(data.returnValues.errorMessage);
				
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
		 * Returns the error element for the path input in the dialog or
		 * `null` if no exists yet.
		 *
		 * @param	{boolean}	createPathError	if `true` and inner error element does not exist, it will be created
		 * @return	{HTMLElement?}	path error element
		 */
		_getPathError: function(createPathError) {
			var innerError = DomTraverse.nextByClass(_pathInput, 'innerError');
			
			if (createPathError && innerError === null) {
				innerError = elCreate('small');
				innerError.classList = 'innerError';
				
				DomUtil.insertAfter(innerError, _pathInput);
			}
			
			return innerError;
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
			var innerError = this._getPathError();
			if (innerError) {
				elHide(innerError);
			}
		},
		
		/**
		 * Shows the dialog after clicking on the related button.
		 */
		_showDialog: function() {
			UiDialog.open(this);
		},
		
		/**
		 * Shows the path error message.
		 * 
		 * @param	{string}	errorMessage	path error emssage
		 */
		_showPathError: function(errorMessage) {
			var innerError = this._getPathError(true);
			innerError.textContent = errorMessage;
			
			elShow(innerError);
		},
		
		/**
		 * Is called if the dialog form is submitted.
		 */
		_submit: function() {
			// check if path is empty
			if (_pathInput.value === '') {
				this._showPathError(Language.get('wcf.global.form.error.empty'));
				
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