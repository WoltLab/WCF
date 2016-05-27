/**
 * Provides the touch-friendly fullscreen user menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Menu/User
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
		}
	});
	
	return UiPageMenuUser;
});
