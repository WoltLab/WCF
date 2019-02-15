/**
 * Executes user notification tests.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Devtools/Project/QuickSetup
 */
define(['Ajax', 'Dictionary', 'Language', 'Ui/Dialog'], function(Ajax, Dictionary, Language, UiDialog) {
	var _buttons = elByClass('jsTestEventButton');
	var _titles = new Dictionary();
	
	return {
		/**
		 * Initializes the user notification test handler.
		 */
		init: function() {
			Array.prototype.forEach.call(_buttons, function(button) {
				button.addEventListener('click', this._test.bind(this));
				
				_titles.set(~~elData(button, 'event-id'), elData(button, 'title'));
			}.bind(this));
		},
		
		/**
		 * Returns the data used to setup the AJAX request object.
		 *
		 * @return	{object}	setup data
		 */
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'testEvent',
					className: 'wcf\\data\\user\\notification\\event\\UserNotificationEventAction'
				}
			}
		},
		
		/**
		 * Handles successful AJAX request.
		 *
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			UiDialog.open(this, data.returnValues.template);
			UiDialog.setTitle(this, _titles.get(~~data.returnValues.eventID));
			
			var dialog = UiDialog.getDialog(this).dialog;
			
			elBySelAll('.formSubmit button', dialog, function(button) {
				button.addEventListener('click', this._changeView.bind(this));
			}.bind(this));
			
			// fix some margin issues
			var errors = elByClass('error', dialog);
			if (errors.length === 1) {
				errors.item(0).style.setProperty('margin-top', '0px');
				errors.item(0).style.setProperty('margin-bottom', '20px');
			}
			
			elBySelAll('.notificationTestSection', dialog, function(section) {
				section.style.setProperty('margin-top', '0px');
			});
			
			elById('notificationTestDialog').parentNode.scrollTop = 0;
			
			// restore buttons
			Array.prototype.forEach.call(_buttons, function(button) {
				button.innerHTML = Language.get('wcf.acp.devtools.notificationTest.button.test');
				button.disabled = false;
			});
		},
		
		/**
		 * Changes the view after clicking on one of the buttons.
		 * 
		 * @param	{Event}		event		button click event
		 */
		_changeView: function(event) {
			var button = event.currentTarget;
			
			var dialog = UiDialog.getDialog(this).dialog;
			
			elBySelAll('.notificationTestSection', dialog, elHide);
			elShow(elById(button.id.replace('Button', '')));
			
			var primaryButton = elBySel('.formSubmit .buttonPrimary', dialog);
			primaryButton.classList.remove('buttonPrimary');
			primaryButton.classList.add('button');
			
			button.classList.remove('button');
			button.classList.add('buttonPrimary');
			
			elById('notificationTestDialog').parentNode.scrollTop = 0;
		},
		
		/**
		 * Returns the data used to setup the dialog.
		 *
		 * @return	{object}	setup data
		 */
		_dialogSetup: function() {
			return {
				id: 'notificationTestDialog',
				source: null
			}
		},
		
		/**
		 * Executes a test after clicking on a test button.
		 * 
		 * @param	{Event}		event
		 */
		_test: function(event) {
			var button = event.currentTarget;
			
			button.innerHTML = '<span class="icon icon16 fa-spinner"></span>';
			
			Array.prototype.forEach.call(_buttons, function(button) {
				button.disabled = true;
			});
			
			Ajax.api(this, {
				parameters: {
					eventID: elData(button, 'event-id')
				}
			});
		}
	};
});