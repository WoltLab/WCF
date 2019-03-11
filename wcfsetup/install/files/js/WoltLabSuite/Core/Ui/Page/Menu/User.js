/**
 * Provides the touch-friendly fullscreen user menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Menu/User
 */
define(['Core', 'EventHandler', 'Language', './Abstract'], function(Core, EventHandler, Language, UiPageMenuAbstract) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiPageMenuUser() { this.init(); }
	Core.inherit(UiPageMenuUser, UiPageMenuAbstract, {
		/**
		 * Initializes the touch-friendly fullscreen user menu.
		 */
		init: function() {
			// check if user menu is actually empty
			var menu = elBySel('#pageUserMenuMobile > .menuOverlayItemList');
			if (menu.childElementCount === 1 && menu.children[0].classList.contains('menuOverlayTitle')) {
				elBySel('#pageHeader .userPanel').classList.add('hideUserPanel');
				return;
			}
			
			UiPageMenuUser._super.prototype.init.call(
				this,
				'com.woltlab.wcf.UserMenuMobile',
				'pageUserMenuMobile',
				'#pageHeader .userPanel'
			);
			
			EventHandler.add('com.woltlab.wcf.userMenu', 'updateBadge', (function (data) {
				elBySelAll('.menuOverlayItemBadge', this._menu, (function (item) {
					if (elData(item, 'badge-identifier') === data.identifier) {
						var badge = elBySel('.badge', item);
						if (data.count) {
							if (badge === null) {
								badge = elCreate('span');
								badge.className = 'badge badgeUpdate';
								item.appendChild(badge);
							}
							
							badge.textContent = data.count;
						}
						else if (badge !== null) {
							elRemove(badge);
						}
						
						this._updateButtonState();
					}
				}).bind(this));
			}).bind(this));
			
			elAttr(this._button, 'aria-label', Language.get('wcf.menu.user'));
			elAttr(this._button, 'role', 'button');
		},
		
		close: function (event) {
			var dropdown = WCF.Dropdown.Interactive.Handler.getOpenDropdown();
			if (dropdown) {
				event.preventDefault();
				event.stopPropagation();
				
				dropdown.close();
			}
			else {
				UiPageMenuUser._super.prototype.close.call(this, event);
			}
		}
	});
	
	return UiPageMenuUser;
});
