/**
 * Handles email notification type for user notification settings.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/User/Notification/Settings
 */
define(['Dictionary', 'Language', 'Dom/Traverse', 'Ui/SimpleDropdown'], function(Dictionary, Language, DomTraverse, UiSimpleDropdown) {
	"use strict";
	
	var _data = new Dictionary();
	
	var _callbackClick = null;
	var _callbackSelectType = null;
	
	/**
	 * @exports	WoltLab/WCF/Controller/User/Notification/Settings
	 */
	var ControllerUserNotificationSettings = {
		/**
		 * Binds event listeners for all notifications supporting emails.
		 */
		setup: function() {
			_callbackClick = this._click.bind(this);
			_callbackSelectType = this._selectType.bind(this);
			
			var group, mailSetting, groups = elBySelAll('#notificationSettings .flexibleButtonGroup');
			for (var i = 0, length = groups.length; i < length; i++) {
				group = groups[i];
				
				mailSetting = elBySel('.notificationSettingsEmail', group);
				if (mailSetting === null) {
					continue;
				}
				
				this._initGroup(group, mailSetting);
			}
		},
		
		/**
		 * Initializes a setting.
		 * 
		 * @param	{Element}	group		button group element
		 * @param	{Element}	mailSetting	mail settings element
		 */
		_initGroup: function(group, mailSetting) {
			var groupId = ~~group.getAttribute('data-object-id');
			
			var disabledNotification = elById('settings_' + groupId + '_disabled');
			disabledNotification.addEventListener('click', function() { mailSetting.classList.remove('active'); });
			var enabledNotification = elById('settings_' + groupId + '_enabled');
			enabledNotification.addEventListener('click', function() { mailSetting.classList.add('active'); });
			
			var mailValue = DomTraverse.childByTag(mailSetting, 'INPUT');
			
			var button = DomTraverse.childByTag(mailSetting, 'A');
			elAttr(button, 'data-object-id', groupId);
			button.addEventListener('click', _callbackClick);
			
			_data.set(groupId, {
				button: button,
				dropdownMenu: null,
				mailSetting: mailSetting,
				mailValue: mailValue
			});
		},
		
		/**
		 * Creates and displays the email type dropdown.
		 * 
		 * @param	{Object}	event		event object
		 */
		_click: function(event) {
			event.preventDefault();
			
			var button = event.currentTarget;
			var objectId = ~~button.getAttribute('data-object-id');
			var data = _data.get(objectId);
			if (data.dropdownMenu === null) {
				data.dropdownMenu = this._createDropdown(objectId, data.mailValue.value);
				
				button.parentNode.classList.add('dropdown');
				button.parentNode.appendChild(data.dropdownMenu);
				
				UiSimpleDropdown.init(button, true);
			}
			else {
				var items = DomTraverse.childrenByTag(data.dropdownMenu, 'LI'), value = data.mailValue.value;
				for (var i = 0; i < 4; i++) {
					items[i].classList[(items[i].getAttribute('data-value') === value) ? 'add' : 'remove']('active');
				}
			}
		},
		
		/**
		 * Creates the email type dropdown.
		 * 
		 * @param	{integer}	objectId	notification event id
		 * @param	{string}	initialValue	initial email type
		 * @returns	{Element}	dropdown menu object
		 */
		_createDropdown: function(objectId, initialValue) {
			var dropdownMenu = elCreate('ul');
			dropdownMenu.className = 'dropdownMenu';
			elAttr(dropdownMenu, 'data-object-id', objectId);
			
			var link, listItem, value, items = ['instant', 'daily', 'divider', 'none'];
			for (var i = 0; i < 4; i++) {
				value = items[i];
				
				listItem = elCreate('li');
				if (value === 'divider') {
					listItem.className = 'dropdownDivider';
				}
				else {
					link = elCreate('a');
					link.textContent = Language.get('wcf.user.notification.mailNotificationType.' + value);
					listItem.appendChild(link);
					elAttr(listItem, 'data-value', value);
					listItem.addEventListener('click', _callbackSelectType);
					
					if (initialValue === value) {
						listItem.className = 'active';
					}
				}
				
				dropdownMenu.appendChild(listItem);
			}
			
			return dropdownMenu;
		},
		
		/**
		 * Sets the selected email notification type.
		 * 
		 * @param	{Object}	event		event object
		 */
		_selectType: function(event) {
			var value = elAttr(event.currentTarget, 'data-value');
			var groupId = ~~event.currentTarget.parentNode.getAttribute('data-object-id');
			
			var data = _data.get(groupId);
			data.mailValue.value = value;
			elBySel('span.title', data.mailSetting).textContent = Language.get('wcf.user.notification.mailNotificationType.' + value);
			
			data.button.classList[(value === 'none') ? 'remove' : 'add']('yellow');
			data.button.classList[(value === 'none') ? 'remove' : 'add']('active');
		}
	};
	
	return ControllerUserNotificationSettings;
});
