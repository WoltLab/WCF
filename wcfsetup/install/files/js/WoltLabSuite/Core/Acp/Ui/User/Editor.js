/**
 * User editing capabilities for the user list.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/User/Editor
 * @since       3.1
 */
define(['Ajax', 'Core', 'EventHandler', 'Language', 'Ui/SimpleDropdown', 'WoltLabSuite/Core/Acp/Ui/User/Content/Remove/Handler'], function(Ajax, Core, EventHandler, Language, UiSimpleDropdown, RemoveContentHandler) {
	"use strict";
	
	/**
	 * @exports     WoltLabSuite/Core/Acp/Ui/User/Editor
	 */
	return {
		/**
		 * Initializes the edit dropdown for each user.
		 */
		init: function () {
			elBySelAll('.jsUserRow', undefined, this._initUser.bind(this));
		},
		
		/**
		 * Initializes the edit dropdown for a user.
		 * 
		 * @param       {Element}       userRow
		 * @protected
		 */
		_initUser: function (userRow) {
			var userId = ~~elData(userRow, 'object-id');
			var dropdownMenu = UiSimpleDropdown.getDropdownMenu('userListDropdown' + userId);
			var legacyButtonContainer = elBySel('.jsLegacyButtons', userRow);
			
			UiSimpleDropdown.registerCallback('userListDropdown' + userId, (function (identifier, action) {
				if (action === 'open') {
					this._rebuild(userId, dropdownMenu, legacyButtonContainer);
				}
			}).bind(this));
			
			var editLink = elBySel('.jsEditLink', dropdownMenu);
			if (editLink !== null) {
				elBySel('.dropdownToggle', userRow).addEventListener('dblclick', function (event) {
					event.preventDefault();
					
					editLink.click();
				});
			}
			
			var sendNewPassword = elBySel('.jsSendNewPassword', dropdownMenu);
			if (sendNewPassword !== null) {
				sendNewPassword.addEventListener(WCF_CLICK_EVENT, function (event) {
					event.preventDefault();
					
					// emulate clipboard selection
					EventHandler.fire('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.user', {
						data: {
							actionName: 'com.woltlab.wcf.user.sendNewPassword',
							parameters: {
								confirmMessage: Language.get('wcf.acp.user.action.sendNewPassword.confirmMessage'),
								objectIDs: [userId]
							}
						},
						responseData: {
							actionName: 'com.woltlab.wcf.user.sendNewPassword',
							objectIDs: [userId]
						}
					});
				});
			}
			
			var deleteContent = elBySel('.jsDeleteContent', dropdownMenu);
			if (deleteContent !== null) {
				new RemoveContentHandler(deleteContent, userId);
			}
		},
		
		/**
		 * Rebuilds the dropdown by adding wrapper links for legacy buttons,
		 * that will eventually receive the click event.
		 * 
		 * @param       {int}           userId
		 * @param       {Element}       dropdownMenu
		 * @param       {Element}       legacyButtonContainer
		 * @protected
		 */
		_rebuild: function (userId, dropdownMenu, legacyButtonContainer) {
			elBySelAll('.jsLegacyItem', dropdownMenu, elRemove);
			
			// inject buttons
			var button, item, link;
			var items = [];
			var deleteButton = null;
			for (var i = 0, length = legacyButtonContainer.childElementCount; i < length; i++) {
				button = legacyButtonContainer.children[i];
				if (button.classList.contains('jsDeleteButton')) {
					deleteButton = button;
					continue;
				}
				
				item = elCreate('li');
				item.className = 'jsLegacyItem';
				item.innerHTML = '<a href="#"></a>';
				
				link = item.children[0];
				link.textContent = elData(button, 'tooltip') || button.title;
				(function(button) {
					link.addEventListener(WCF_CLICK_EVENT, function (event) {
						event.preventDefault();
						
						// forward click onto original button
						if (button.nodeName === 'A') button.click();
						else Core.triggerEvent(button, WCF_CLICK_EVENT);
					});
				})(button);
				
				items.push(item);
			}
			
			while (items.length) {
				dropdownMenu.insertBefore(items.pop(), dropdownMenu.firstElementChild);
			}
			
			if (deleteButton !== null) {
				elBySel('.jsDispatchDelete', dropdownMenu).addEventListener(WCF_CLICK_EVENT, function (event) {
					event.preventDefault();
					
					Core.triggerEvent(deleteButton, WCF_CLICK_EVENT);
				});
			}
			
			// check if there are visible items before each divider
			for (i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
				elShow(dropdownMenu.children[i]);
			}
			
			var hasItem = false;
			for (i = 0, length = dropdownMenu.childElementCount; i < length; i++) {
				item = dropdownMenu.children[i];
				if (item.classList.contains('dropdownDivider')) {
					if (!hasItem) elHide(item);
				}
				else {
					hasItem = true;
				}
			}
		}
	};
});
