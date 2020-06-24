/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/BootstrapFrontend
 */
define(
	[
	 	'WoltLabSuite/Core/BackgroundQueue', 'WoltLabSuite/Core/Bootstrap', 'WoltLabSuite/Core/Controller/Style/Changer',
	 	'WoltLabSuite/Core/Controller/Popover', 'WoltLabSuite/Core/Ui/User/Ignore', 'WoltLabSuite/Core/Ui/Page/Header/Menu',
		'WoltLabSuite/Core/Ui/Message/UserConsent'
	],
	function(
		BackgroundQueue, Bootstrap, ControllerStyleChanger,
		ControllerPopover, UiUserIgnore, UiPageHeaderMenu,
		UiMessageUserConsent
	)
{
	"use strict";
	
	/**
	 * @exports	WoltLabSuite/Core/BootstrapFrontend
	 */
	return {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{object<string, *>}	options		bootstrap options
		 */
		setup: function(options) {
			// fix the background queue URL to always run against the current domain (avoiding CORS)
			options.backgroundQueue.url = WSC_API_URL + options.backgroundQueue.url.substr(WCF_PATH.length);
			
			Bootstrap.setup();
			
			UiPageHeaderMenu.init();
			
			if (options.styleChanger) {
				ControllerStyleChanger.setup();
			}
			
			if (options.enableUserPopover) {
				this._initUserPopover();
			}
			
			BackgroundQueue.setUrl(options.backgroundQueue.url);
			if (Math.random() < 0.1 || options.backgroundQueue.force) {
				// invoke the queue roughly every 10th request or on demand
				BackgroundQueue.invoke();
			}
			
			if (COMPILER_TARGET_DEFAULT) {
				UiUserIgnore.init();
			}
			
			UiMessageUserConsent.init();
		},
		
		/**
		 * Initializes user profile popover.
		 */
		_initUserPopover: function() {
			ControllerPopover.init({
				className: 'userLink',
				dboAction: 'wcf\\data\\user\\UserProfileAction',
				identifier: 'com.woltlab.wcf.user'
			});
			
			// @deprecated since 5.3
			ControllerPopover.init({
				attributeName: 'data-user-id',
				className: 'userLink',
				dboAction: 'wcf\\data\\user\\UserProfileAction',
				identifier: 'com.woltlab.wcf.user.deprecated'
			});
		}
	};
});
