/**
 * Provides the touch-friendly fullscreen user menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Menu/User
 */
define(['Core', 'EventHandler', './Abstract'], function(Core, EventHandler, UiPageMenuAbstract) {
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
		}
	});
	
	return UiPageMenuUser;
});
