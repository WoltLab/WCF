/**
 * Handles email notification type for user notification settings.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Controller/User/Notification/Settings
 */
define(['Language', 'Ui/ReusableDropdown'], function (Language, UiReusableDropdown) {
	'use strict';
	
	if (!COMPILER_TARGET_DEFAULT) {
		return function () {};
	}
	
	var _dropDownMenu = null;
	var _objectId = null;
	
	/**
	 * @exports	WoltLabSuite/Core/Controller/User/Notification/Settings
	 */
	return {
		/**
		 * Binds event listeners for all notifications supporting emails.
		 */
		init: function () {
			elBySelAll('.notificationSettingsEmailType', undefined, (function (button) {
				button.addEventListener('click', this._click.bind(this));
			}).bind(this));
		},
		
		/**
		 * @param	{Event}	event		event object
		 */
		_click: function (event) {
			event.preventDefault();
			event.stopPropagation();
			
			var button = event.currentTarget;
			_objectId = ~~elData(button, 'object-id');
			
			this._createDropDown();
			
			this._setCurrentEmailType(this._getEmailTypeInputElement().value);
			
			this._showDropDown(button);
		},
		
		_createDropDown: function () {
			if (_dropDownMenu !== null) {
				return;
			}
			
			_dropDownMenu = elCreate('ul');
			_dropDownMenu.className = 'dropdownMenu';
			
			['instant', 'daily', 'divider', 'none'].forEach((function (value) {
				var listItem = elCreate('li');
				if (value === 'divider') {
					listItem.className = 'dropdownDivider';
				}
				else {
					var link = elCreate('a');
					link.href = '#';
					link.textContent = Language.get('wcf.user.notification.mailNotificationType.' + value);
					listItem.appendChild(link);
					elData(listItem, 'value', value);
					listItem.addEventListener(WCF_CLICK_EVENT, this._setEmailType.bind(this));
				}
				
				_dropDownMenu.appendChild(listItem);
			}).bind(this));
			
			UiReusableDropdown.init('UiNotificationSettingsEmailType', _dropDownMenu);
		},
		
		_setCurrentEmailType: function (currentValue) {
			elBySelAll('li', _dropDownMenu, function (button) {
				var value = elData(button, 'value');
				button.classList[(value === currentValue) ? 'add' : 'remove']('active');
			});
		},
		
		_showDropDown: function (referenceElement) {
			UiReusableDropdown.toggleDropdown('UiNotificationSettingsEmailType', referenceElement);
		},
		
		/**
		 * @param	{Event}	event		event object
		 */
		_setEmailType: function (event) {
			event.preventDefault();
			
			var value = elData(event.currentTarget, 'value');
			
			this._getEmailTypeInputElement().value = value;
			
			var button = elBySel('.notificationSettingsEmailType[data-object-id="' + _objectId + '"]');
			elAttr(
				button,
				'aria-label',
				Language.get('wcf.user.notification.mailNotificationType.' + value)
			);
			
			var icon = elBySel('.jsIconNotificationSettingsEmailType', button);
			icon.classList.remove('fa-clock-o');
			icon.classList.remove('fa-flash');
			icon.classList.remove('fa-times');
			icon.classList.remove('green');
			icon.classList.remove('red');
			
			switch (value) {
				case 'daily':
					icon.classList.add('fa-clock-o');
					icon.classList.add('green');
					break;
				
				case 'instant':
					icon.classList.add('fa-flash');
					icon.classList.add('green');
					break;
				
				case 'none':
					icon.classList.add('fa-times');
					icon.classList.add('red');
					break;
			}
			
			_objectId = null;
		},
		
		/**
		 * @return {HTMLInputElement}
		 */
		_getEmailTypeInputElement: function () {
			return elById('settings_' + _objectId + '_mailNotificationType');
		}
	};
});
