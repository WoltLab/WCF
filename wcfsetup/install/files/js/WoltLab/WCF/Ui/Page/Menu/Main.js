/**
 * Provides the touch-friendly fullscreen main menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Menu/Main
 */
define(['Core', './Abstract'], function(Core, UiPageMenuAbstract) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiPageMenuMain() { this.init(); }
	Core.inherit(UiPageMenuMain, UiPageMenuAbstract, {
		/**
		 * Initializes the touch-friendly fullscreen main menu.
		 */
		init: function() {
			UiPageMenuMain._super.prototype.init.call(
				this,
				'com.woltlab.wcf.MainMenuMobile',
				'pageMainMenuMobile',
				'#pageHeader .mainMenu'
			);
		}
	});
	
	return UiPageMenuMain;
});
